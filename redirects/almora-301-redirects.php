// ALMORA - 301 redirects (Code Snippets, global, run everywhere)
// Exact path matching only, and only when WordPress is already about to show a 404.
// Add a line to $map as 'old-path' => 'new-path' (both without domain). Never redirect to the home page.
add_action('template_redirect', function () {
	if (!is_404()) {
		return;
	}
	$map = array(
		// WooCommerce default slugs -> the renamed ALMORA cart / checkout pages
		'/cart'     => '/shop-cart',
		'/checkout' => '/shop-checkout',
	);
	$path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
	$path = rtrim(rawurldecode((string) $path), '/');
	if ($path !== '' && isset($map[$path])) {
		wp_safe_redirect(home_url($map[$path]), 301);
		exit;
	}
}, 1);
