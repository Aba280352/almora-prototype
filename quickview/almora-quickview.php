<?php
/**
 * Plugin Name: ALMORA Quick View
 * Description: Quick view card (the eye on product cards): two actions only, "הוספה לסל" (solid) and "צפייה במוצר" (outline) like the product page; the quick-buy button and the "הצג פרטים מלאים" link are gone; shorter card on phones.
 */

defined( 'ABSPATH' ) || exit;

// The theme prints "הצג פרטים מלאים" under the buttons; it becomes the second button, "צפייה במוצר".
add_filter( 'gettext', function ( $translated ) {
	return 'הצג פרטים מלאים' === $translated ? 'צפייה במוצר' : $translated;
}, 20 );

add_action( 'wp_head', function () {
	$q = '.quick-view-modal ';
	echo '<style id="almora-quickview-css">'
		// one call to action next to "add to cart": no quick buy (sizes may still need picking)
		. $q . '.em-buy-now-button{display:none!important}'
		// "צפייה במוצר": outline button, same look as the product page's second button
		. $q . '.view-full-details-button{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:46px;margin-top:10px;padding:0 20px;box-sizing:border-box;border:1px solid #202020;border-radius:12px;background:#fff;color:#202020;font-size:15px;font-weight:600;text-decoration:none}'
		. $q . '.view-full-details-button .ecomus-button-text{border:0;padding:0;box-shadow:none}'
		. $q . '.view-full-details-button:hover{background:#fff;color:#202020}'
		// shorter card on phones: smaller photo, tighter spacing
		. '@media(max-width:767px){'
		. $q . '.woocommerce-product-gallery .woocommerce-product-gallery__image img,' . $q . '.woocommerce-product-gallery img.wp-post-image{height:230px!important;object-fit:contain!important}'
		. $q . '.woocommerce-product-gallery{height:230px!important;max-height:230px!important}'
		. $q . '.summary{padding-top:4px!important}'
		. $q . '.product_title{margin-bottom:6px}'
		. '}'
		. '</style>';
}, 99 );
