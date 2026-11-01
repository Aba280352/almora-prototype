<?php
/**
 * Plugin Name: ALMORA Google Tag Manager
 * Description: Loads the GTM container of almora.jewelry@gmail.com (GTM-T3ZGCVPQ). Skips admin, feeds and Elementor preview.
 */
if (!defined('ABSPATH')) exit;
const ALMORA_GTM_ID = 'GTM-T3ZGCVPQ';

function almora_gtm_enabled() {
	if (is_admin() || is_feed() || is_embed() || is_customize_preview() || wp_doing_ajax()) return false;
	if (isset($_GET['elementor-preview'])) return false;
	return true;
}
add_action('wp_head', function () {
	if (!almora_gtm_enabled()) return;
	echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . ALMORA_GTM_ID . "');</script>\n";
}, 1);
add_action('wp_body_open', function () {
	if (!almora_gtm_enabled()) return;
	echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . ALMORA_GTM_ID . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
}, 1);
// WP Rocket must not delay the GTM loader
add_filter('rocket_delay_js_exclusions', function ($e) { $e[] = 'googletagmanager.com/gtm.js'; $e[] = 'gtm.start'; return $e; });
add_filter('rocket_excluded_inline_js_content', function ($e) { $e[] = 'gtm.start'; return $e; });
