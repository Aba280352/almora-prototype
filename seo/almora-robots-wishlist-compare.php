// ALMORA - robots.txt: keep crawlers off wishlist / compare action URLs (Code Snippets, global)
// robots.txt on this site is virtual (no file on disk), built by WordPress + WooCommerce + Yoast through the robots_txt filter.
// This adds four Disallow lines to the existing first group, right before "Disallow: /wp-admin/".
// Nothing is removed: products, categories, pages, /wp-content/uploads and the sitemap line stay as they were.
add_filter('robots_txt', function ($output, $public) {
	if (! is_string($output) || false !== strpos($output, 'add-to-wishlist')) {
		return $output;
	}
	$lines = "Disallow: /*?add-to-wishlist=\n"
		. "Disallow: /*?*add-to-wishlist=\n"
		. "Disallow: /*?add_to_compare=\n"
		. "Disallow: /*?*add_to_compare=\n";
	$anchor = "Disallow: /wp-admin/\n";
	$pos = strpos($output, $anchor);
	if (false !== $pos) {
		return substr($output, 0, $pos) . $lines . substr($output, $pos);
	}
	// No anchor found (unexpected): put the lines after the first "User-agent: *" line.
	$ua = "User-agent: *\n";
	$pos = strpos($output, $ua);
	if (false !== $pos) {
		$pos += strlen($ua);
		return substr($output, 0, $pos) . $lines . substr($output, $pos);
	}
	return $output;
}, 100, 2);
