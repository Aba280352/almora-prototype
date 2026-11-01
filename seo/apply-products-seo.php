<?php
/**
 * Applies seo/products-seo.json to the WooCommerce products (Yoast title / meta description / focus keyphrase,
 * and the short + full description for products that had almost none).
 *
 * Run through Novamira execute-php (or wp eval-file). Set $ids to limit to a batch, or leave empty for all.
 * Before the first change of a product, the old values are saved in the post meta _almora_seo_backup.
 */
$json_url = 'https://raw.githubusercontent.com/Aba280352/almora-prototype/__SHA__/seo/products-seo.json';
$ids = [];            // e.g. [8373, 8371]; empty = every product in the JSON
$dry = false;         // true = report only

$r = wp_remote_get($json_url, ['timeout' => 30]);
if (is_wp_error($r) || wp_remote_retrieve_response_code($r) !== 200) { return 'fetch failed'; }
$data = json_decode(wp_remote_retrieve_body($r), true);
if (!$data) { return 'bad json'; }

$generic_excerpt = ['שרשרת כסף 925 בעיצוב עדין ונקי', 'טבעת כסף 925 בעיצוב'];
$log = [];
foreach ($data as $id => $o) {
	$id = (int) $id;
	if ($ids && !in_array($id, $ids, true)) { continue; }
	$p = wc_get_product($id);
	if (!$p || get_post_status($id) !== 'publish') { $log[] = "$id skip (not published product)"; continue; }
	if ($dry) { $log[] = "$id dry"; continue; }

	if (!get_post_meta($id, '_almora_seo_backup', true)) {
		update_post_meta($id, '_almora_seo_backup', wp_json_encode([
			'title'   => get_post_meta($id, '_yoast_wpseo_title', true),
			'desc'    => get_post_meta($id, '_yoast_wpseo_metadesc', true),
			'kw'      => get_post_meta($id, '_yoast_wpseo_focuskw', true),
			'content' => get_post_field('post_content', $id),
			'excerpt' => get_post_field('post_excerpt', $id),
			'date'    => gmdate('c'),
		], JSON_UNESCAPED_UNICODE));
	}

	update_post_meta($id, '_yoast_wpseo_title', $o['t']);
	update_post_meta($id, '_yoast_wpseo_metadesc', $o['d']);
	update_post_meta($id, '_yoast_wpseo_focuskw', $o['k']);
	delete_post_meta($id, '_yoast_wpseo_linkdex');
	delete_post_meta($id, '_yoast_wpseo_content_score');

	$upd = ['ID' => $id];
	if (!empty($o['long'])) {
		$cur = trim(wp_strip_all_tags(get_post_field('post_content', $id)));
		if (mb_strlen($cur) < 150) {
			$opt = '';
			$parts = [];
			foreach ($p->get_attributes() as $a) {
				if (!is_object($a)) { continue; }
				$vals = $a->is_taxonomy() ? wc_get_product_terms($id, $a->get_name(), ['fields' => 'names']) : $a->get_options();
				if ($vals) { $parts[] = wc_attribute_label($a->get_name()) . ': ' . implode(', ', $vals); }
			}
			if ($parts) { $opt = '<p><strong>אפשרויות לבחירה:</strong> ' . esc_html(implode(' | ', $parts)) . '</p>'; }
			$upd['post_content'] = str_replace('{{OPTIONS}}', $opt, $o['long']);
		}
	}
	if (!empty($o['short'])) {
		$ex = trim(get_post_field('post_excerpt', $id));
		$is_generic = false;
		foreach ($generic_excerpt as $g) { if ($ex !== '' && mb_strpos($ex, $g) === 0) { $is_generic = true; } }
		if ($ex === '' || $is_generic) { $upd['post_excerpt'] = $o['short']; }
	}
	wp_update_post($upd);   // also fires save_post so Yoast rebuilds its indexable
	$log[] = "$id ok" . (isset($upd['post_content']) ? ' +desc' : '') . (isset($upd['post_excerpt']) ? ' +short' : '');
}
if (function_exists('rocket_clean_domain')) { ob_start(); rocket_clean_domain(); ob_end_clean(); }
return implode("\n", $log);
