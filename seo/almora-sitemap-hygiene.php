// ALMORA - sitemap hygiene (Code Snippets, global)
// Keeps URLs that are noindex / not for visitors out of the Yoast XML sitemap.
// It does NOT change any robots / noindex / canonical setting.
add_filter('wpseo_exclude_from_sitemap_by_post_ids', function ($ids) {
	$ids = is_array($ids) ? $ids : array();
	if (function_exists('wc_get_page_id')) {
		foreach (array('cart', 'checkout', 'myaccount') as $page) {
			$id = (int) wc_get_page_id($page);
			if ($id > 0) {
				$ids[] = $id;
			}
		}
	}
	return $ids;
});
// Theme-builder footer and newsletter popup are not pages for Google.
add_filter('wpseo_sitemap_exclude_post_type', function ($exclude, $post_type) {
	if (in_array($post_type, array('ecomus_popup', 'ecomus_builder'), true)) {
		return true;
	}
	return $exclude;
}, 10, 2);
