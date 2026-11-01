<?php
/**
 * Plugin Name: ALMORA Mobile Menu Scroll
 * Description: The phone off-canvas menu scrolls on its own and has room at the bottom, so the last links (מחשבון זהב וכסף, צור קשר) can be lifted above the floating chat and WhatsApp buttons.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	echo '<style id="almora-mobile-menu-css">'
		. '.e-off-canvas__content{overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}'
		. '.e-off-canvas__content .elementor-element-mobile-menu-content{padding-bottom:160px}'
		. '</style>';
}, 99 );
