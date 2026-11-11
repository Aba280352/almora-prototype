<?php
/**
 * ALMORA - first purchase discount popup (Code Snippets, run everywhere).
 *
 * Elementor Pro popup (draft until the owner says go) + form hook:
 * the form creates a personal single-use WooCommerce coupon and emails it
 * in a designed HTML email. Valid only for a first order.
 * Source: repo folder popup/ (node popup/build.js).
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// ---- Settings (change here) -------------------------------------------------
if ( ! defined( 'ALMORA_FP_PERCENT' ) ) {
	define( 'ALMORA_FP_PERCENT', 10 );
}
if ( ! defined( 'ALMORA_FP_DAYS' ) ) {
	define( 'ALMORA_FP_DAYS', 30 );
}
if ( ! defined( 'ALMORA_FP_FORM' ) ) {
	define( 'ALMORA_FP_FORM', 'ALMORA first purchase' );
}

/**
 * Designed email (HTML). Placeholders: {{CODE}} {{PERCENT}} {{EXPIRY}} {{NAME_LINE}} {{LOGO}} {{SHOP_URL}}
 */
function almora_fp_email_html( $code, $expiry_ts, $name ) {
	$tpl = <<<'ALMORA_EMAIL'
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>הקופון שלכם מ-ALMORA</title>
</head>
<body style="margin:0;padding:0;background:#F5F5F5;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">הקוד האישי שלכם ל-{{PERCENT}}% הנחה על הרכישה הראשונה</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" dir="rtl" style="background:#F5F5F5;">
<tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" dir="rtl" style="width:100%;max-width:560px;background:#FFFFFF;border-radius:16px;">
<tr><td align="center" style="padding:40px 32px 8px 32px;">
<img src="{{LOGO}}" width="150" alt="ALMORA" style="display:block;border:0;width:150px;height:auto;">
</td></tr>
<tr><td align="center" style="padding:24px 32px 0 32px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:14px;letter-spacing:2px;color:#B8860B;">מתנה לרכישה הראשונה</td></tr>
<tr><td align="center" style="padding:8px 32px 0 32px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:30px;line-height:38px;font-weight:600;color:#202020;">{{NAME_LINE}}הקופון שלכם כאן</td></tr>
<tr><td align="center" style="padding:16px 40px 0 40px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:18px;line-height:28px;color:#202020;">{{PERCENT}}% הנחה על הרכישה הראשונה שלכם בתכשיטי הכסף של ALMORA. הקוד מחכה לכם בקופה.</td></tr>
<tr><td align="center" style="padding:28px 32px 0 32px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="background:#F5F5F5;border:2px dashed #202020;border-radius:12px;">
<tr><td align="center" style="padding:18px 40px;font-family:'Courier New',Courier,monospace;font-size:32px;line-height:36px;font-weight:bold;letter-spacing:4px;color:#202020;" dir="ltr">{{CODE}}</td></tr>
</table>
</td></tr>
<tr><td align="center" style="padding:12px 32px 0 32px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:15px;line-height:22px;color:#202020;">מזינים את הקוד בעגלה או בתשלום.</td></tr>
<tr><td align="center" style="padding:28px 32px 0 32px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td align="center" bgcolor="#202020" style="border-radius:12px;"><a href="{{SHOP_URL}}" style="display:inline-block;padding:16px 40px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:18px;font-weight:600;color:#FFFFFF;text-decoration:none;">לקולקציה</a></td>
</tr></table>
</td></tr>
<tr><td style="padding:36px 32px 0 32px;"><div style="border-top:1px solid #E5E5E5;font-size:0;line-height:0;">&nbsp;</div></td></tr>
<tr><td align="center" style="padding:20px 40px 0 40px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:15px;line-height:24px;color:#202020;">כסף 925 &middot; משלוח חינם מעל 1,000 &#8362; &middot; 14 יום להחזרה<br>שאלה? אנחנו בוואטסאפ <a href="https://wa.me/972523550851" style="color:#202020;font-weight:600;text-decoration:underline;">052-3550851</a></td></tr>
<tr><td align="center" style="padding:20px 40px 36px 40px;font-family:Polin,Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#202020;">הקוד תקף לרכישה הראשונה באתר, פעם אחת ללקוח. לא חל על פריטים במבצע ולא מצטרף להנחות או קופונים אחרים.<br>קיבלתם הודעה זו כי השארתם פרטים באתר almora.co.il. להסרה מהרשימה, השיבו למייל זה "הסר".</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
ALMORA_EMAIL;
	$first     = trim( preg_split( '/\s+/u', trim( (string) $name ) )[0] ?? '' );
	$name_line = $first !== '' ? esc_html( $first ) . ', ' : '';
	return strtr(
		$tpl,
		array(
			'{{CODE}}'      => esc_html( $code ),
			'{{PERCENT}}'   => (int) ALMORA_FP_PERCENT,
			'{{NAME_LINE}}' => $name_line,
			'{{LOGO}}'      => esc_url( home_url( '/wp-content/uploads/2026/09/ALMORA-header-compact.png' ) ),
			'{{SHOP_URL}}'  => esc_url( home_url( '/' ) ),
		)
	);
}

/**
 * One fixed code for everyone (owner, 2026-11-11): ALMORA10.
 * First purchase only, once per customer, no expiry. Created if missing.
 */
define( 'ALMORA_FP_CODE', 'ALMORA10' );
function almora_fp_get_or_create_coupon( $email = '' ) {
	$cid    = wc_get_coupon_id_by_code( ALMORA_FP_CODE );
	$coupon = $cid ? new WC_Coupon( $cid ) : new WC_Coupon();
	if ( ! $cid || $coupon->get_meta( '_almora_fp_v' ) !== '2' ) {
		$coupon->set_code( ALMORA_FP_CODE );
		$coupon->set_discount_type( 'percent' );
		$coupon->set_amount( ALMORA_FP_PERCENT );
		$coupon->set_usage_limit_per_user( 1 );
		$coupon->set_individual_use( true );      // no other coupon with it
		$coupon->set_exclude_sale_items( true );  // not on items already on sale
		$coupon->set_description( 'Popup: ' . ALMORA_FP_PERCENT . '% first purchase, once per customer, no stacking (no sale items, no other coupon, no 2+ items 5% fee)' );
		$coupon->update_meta_data( '_almora_first_purchase', '1' );
		$coupon->update_meta_data( '_almora_fp_v', '2' );
		$coupon->save();
	}
	return array( ALMORA_FP_CODE, 0, true );
}

// No stacking with the automatic cart discounts (negative fees, e.g. the 5% for 2+ items).
add_action(
	'woocommerce_cart_calculate_fees',
	function ( $cart ) {
		if ( ! $cart->has_discount( ALMORA_FP_CODE ) ) {
			return;
		}
		$fees = $cart->fees_api()->get_fees();
		$keep = array_filter(
			$fees,
			function ( $fee ) {
				return (float) $fee->amount >= 0;
			}
		);
		if ( count( $keep ) !== count( $fees ) ) {
			$cart->fees_api()->set_fees( $keep );
		}
	},
	9999
);

/**
 * Form submit: build the coupon and send the mail.
 */
add_action(
	'elementor_pro/forms/new_record',
	function ( $record, $handler ) {
		if ( $record->get_form_settings( 'form_name' ) !== ALMORA_FP_FORM || ! function_exists( 'WC' ) ) {
			return;
		}
		$fields = $record->get( 'fields' );
		$email  = isset( $fields['email']['value'] ) ? sanitize_email( $fields['email']['value'] ) : '';
		$name   = isset( $fields['name']['value'] ) ? sanitize_text_field( $fields['name']['value'] ) : '';
		if ( ! is_email( $email ) ) {
			$handler->add_error_message( 'כתובת המייל לא תקינה' );
			return;
		}

		list( $code, $expires, $is_new ) = almora_fp_get_or_create_coupon( $email );

		// Spam hygiene: calm subject (no % / "הנחה"), plain-text twin, unsubscribe header.
		$subject = 'הקוד שלכם מ-ALMORA';
		$first   = trim( preg_split( '/\s+/u', trim( $name ) )[0] ?? '' );
		$GLOBALS['almora_fp_alt'] = ( $first !== '' ? $first . ",\n\n" : '' )
			. "הקוד שלכם לרכישה הראשונה באתר ALMORA: {$code}\n"
			. ALMORA_FP_PERCENT . "% הנחה. מזינים את הקוד בעגלה או בתשלום.\n"
			. "הקוד תקף לרכישה הראשונה באתר, פעם אחת ללקוח, ולא חל על פריטים במבצע.\n\n"
			. home_url( '/' ) . "\n\n"
			. "שאלה? וואטסאפ 052-3550851\n"
			. 'להסרה מהרשימה, השיבו למייל זה "הסר".';
		$sent = wp_mail(
			$email,
			$subject,
			almora_fp_email_html( $code, $expires, $name ),
			array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ALMORA <' . get_option( 'admin_email' ) . '>' )
		);
		unset( $GLOBALS['almora_fp_alt'] );
		if ( ! $sent ) {
			$handler->add_error_message( 'לא הצלחנו לשלוח את המייל. נסו שוב, או כתבו לנו בוואטסאפ.' );
			return;
		}

		// Heads-up to the owner (lead + code).
		if ( $is_new ) {
			wp_mail(
				get_option( 'admin_email' ),
				'ליד חדש מהפופאפ: ' . $name,
				"שם: {$name}\nמייל: {$email}\nטלפון: " . ( $fields['phone']['value'] ?? '' ) . "\nקוד: {$code}"
			);
		}
	},
	10,
	2
);

// Plain-text alternative + List-Unsubscribe for the popup mail only.
add_action(
	'phpmailer_init',
	function ( $mailer ) {
		if ( empty( $GLOBALS['almora_fp_alt'] ) ) {
			return;
		}
		$mailer->AltBody = $GLOBALS['almora_fp_alt'];
		$mailer->addCustomHeader( 'List-Unsubscribe', '<mailto:' . get_option( 'admin_email' ) . '?subject=unsubscribe>' );
	}
);

/**
 * First order only: the coupon is refused when the customer already has an order.
 */
add_filter(
	'woocommerce_coupon_is_valid',
	function ( $valid, $coupon ) {
		if ( ! $valid || $coupon->get_meta( '_almora_first_purchase' ) !== '1' ) {
			return $valid;
		}
		$emails = array_map( 'strtolower', (array) $coupon->get_email_restrictions() );
		if ( function_exists( 'WC' ) && WC()->customer && WC()->customer->get_billing_email() ) {
			$emails[] = strtolower( WC()->customer->get_billing_email() );
		}
		if ( ! empty( $_POST['billing_email'] ) ) { // checkout submit
			$emails[] = strtolower( sanitize_email( wp_unslash( $_POST['billing_email'] ) ) );
		}
		$emails = array_unique( array_filter( $emails ) );
		$args = array(
			'status' => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
			'limit'  => 1,
			'return' => 'ids',
		);
		foreach ( $emails as $em ) {
			if ( wc_get_orders( array_merge( $args, array( 'billing_email' => $em ) ) ) ) {
				throw new Exception( 'הקוד תקף לרכישה ראשונה בלבד.' );
			}
		}
		if ( is_user_logged_in() && wc_get_orders( array_merge( $args, array( 'customer_id' => get_current_user_id() ) ) ) ) {
			throw new Exception( 'הקוד תקף לרכישה ראשונה בלבד.' );
		}
		return $valid;
	},
	10,
	2
);

/**
 * Create the Elementor popup once, as a DRAFT (nothing shows until it is published).
 */
function almora_fp_popup_data() {
	$gold  = '#B8860B';
	$ink   = '#202020';
	$muted = '#F5F5F5';
	return array(
		array(
			'id'       => 'alfp0001',
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array(
				'content_width'      => 'full',
				'flex_direction'     => 'column',
				'flex_align_items'   => 'center',
				'flex_gap'           => array( 'column' => '0', 'row' => '0', 'isLinked' => true, 'unit' => 'px' ),
				'padding'            => array( 'unit' => 'px', 'top' => '44', 'right' => '36', 'bottom' => '36', 'left' => '36', 'isLinked' => false ),
				'padding_mobile'     => array( 'unit' => 'px', 'top' => '36', 'right' => '22', 'bottom' => '28', 'left' => '22', 'isLinked' => false ),
				'background_background' => 'classic',
				'background_color'   => '#FFFFFF',
				'css_classes'        => 'alm-fp',
			),
			'elements' => array(
				array(
					'id'         => 'alfp0002',
					'elType'     => 'widget',
					'widgetType' => 'image',
					'settings'   => array(
						'image'      => array( 'url' => home_url( '/wp-content/uploads/2026/09/ALMORA-header-compact.webp' ), 'id' => '' ),
						'image_size' => 'full',
						'width'      => array( 'unit' => 'px', 'size' => 130 ),
						'align'      => 'center',
						'_margin'    => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '22', 'left' => '0', 'isLinked' => false ),
					),
				),
				array(
					'id'         => 'alfp0003',
					'elType'     => 'widget',
					'widgetType' => 'heading',
					'settings'   => array(
						'title'       => ALMORA_FP_PERCENT . '% הנחה על הרכישה הראשונה',
						'header_size' => 'h2',
						'align'       => 'center',
						'title_color' => $ink,
						'typography_typography' => 'custom',
						'typography_font_family' => 'Polin',
						'typography_font_size'   => array( 'unit' => 'px', 'size' => 30 ),
						'typography_font_size_mobile' => array( 'unit' => 'px', 'size' => 25 ),
						'typography_font_weight' => '600',
						'typography_line_height' => array( 'unit' => 'em', 'size' => 1.15 ),
						'_margin'     => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '14', 'left' => '0', 'isLinked' => false ),
					),
				),
				array(
					'id'         => 'alfp0004',
					'elType'     => 'widget',
					'widgetType' => 'text-editor',
					'settings'   => array(
						'editor'     => '<p>הקוד שלכם כאן. מעתיקים ומזינים בקופה.</p>',
						'align'      => 'center',
						'text_color' => $ink,
						'typography_typography' => 'custom',
						'typography_font_family' => 'Polin',
						'typography_font_size'   => array( 'unit' => 'px', 'size' => 18 ),
						'typography_font_weight' => '300',
						'typography_line_height' => array( 'unit' => 'em', 'size' => 1.4 ),
						'_margin'    => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '22', 'left' => '0', 'isLinked' => false ),
					),
				),
				array(
					'id'         => 'alfp0005',
					'elType'     => 'widget',
					'widgetType' => 'html',
					'settings'   => array(
						'html' => '<div class="alm-fp-code" dir="ltr" role="button" tabindex="0" data-code="' . ALMORA_FP_CODE . '">' . ALMORA_FP_CODE . '</div><button type="button" class="alm-fp-copy" data-code="' . ALMORA_FP_CODE . '">העתקת הקוד</button><p class="alm-fp-note">מזינים בעגלה או בתשלום. תקף לרכישה הראשונה באתר, פעם אחת ללקוח, ולא חל על פריטים במבצע או יחד עם הנחות וקופונים אחרים.</p>',
					),
				),
			),
		),
	);
}

function almora_fp_popup_css( $id ) {
	return <<<CSS
.elementor-{$id} .alm-fp{font-family:Polin,sans-serif;color:#202020}
.elementor-{$id} .elementor-field-textual{height:50px;padding:0 16px;font-family:Polin,sans-serif;font-size:16px;font-weight:300;text-align:right}
.elementor-{$id} .elementor-field-textual::placeholder{color:#6b6b6b}
.elementor-{$id} .elementor-field-type-acceptance .elementor-field-subgroup,.elementor-{$id} .elementor-field-type-acceptance .elementor-field-option{display:flex;align-items:center;gap:10px}
.elementor-{$id} .elementor-field-type-acceptance input[type=checkbox]{width:18px;height:18px;margin:0;accent-color:#202020;cursor:pointer}
.elementor-{$id} .elementor-field-type-acceptance label{font-family:Polin,sans-serif;font-size:15px;font-weight:300;line-height:1.4;color:#202020}
.elementor-{$id} .elementor-field-type-acceptance label a{color:#202020;text-decoration:underline;text-underline-offset:3px}
.elementor-{$id} .elementor-field-type-acceptance label a:hover{color:#202020}
.elementor-{$id} .elementor-button{height:52px;font-family:Polin,sans-serif;font-size:18px;font-weight:600;letter-spacing:.9px;background:#202020;color:#fff}
.elementor-{$id} .elementor-button:hover{background:#202020;color:#fff;opacity:.9}
.elementor-{$id} .elementor-message-success{font-family:Polin,sans-serif;font-size:17px;font-weight:600;text-align:center;color:#202020;padding:6px 0}
.elementor-{$id} .elementor-message-danger{font-family:Polin,sans-serif;text-align:center}
.elementor-{$id} .alm-fp-code{display:block;margin:0 auto 14px;padding:16px 20px;background:#F5F5F5;border:2px dashed #202020;border-radius:12px;font-family:'Courier New',Courier,monospace;font-size:34px;line-height:1.1;font-weight:700;letter-spacing:5px;text-align:center;color:#202020;cursor:pointer;user-select:all}
.elementor-{$id} .alm-fp-copy{display:block;width:100%;height:52px;border:0;border-radius:12px;background:#202020;color:#fff;font-family:Polin,sans-serif;font-size:18px;font-weight:600;letter-spacing:.9px;cursor:pointer}
.elementor-{$id} .alm-fp-copy:hover,.elementor-{$id} .alm-fp-copy:focus{background:#202020;color:#fff;opacity:.9}
.elementor-{$id} .alm-fp-note{margin:14px 0 0;font-family:Polin,sans-serif;font-size:13px;font-weight:300;line-height:1.45;text-align:center;color:#202020}
#elementor-popup-modal-{$id}{z-index:2147483600 !important}
#elementor-popup-modal-{$id} .dialog-message{border-radius:16px;overflow:hidden;max-width:92vw;max-height:92vh;overflow-y:auto}
@media (max-width:767px){
.elementor-{$id} .alm-fp{padding:30px 20px 22px !important}
.elementor-{$id} .elementor-widget-image img{width:96px !important}
.elementor-{$id} .elementor-widget-image{margin-bottom:14px !important}
.elementor-{$id} .elementor-widget-heading .elementor-heading-title{font-size:24px !important}
.elementor-{$id} .elementor-widget-text-editor{margin-bottom:16px !important}
.elementor-{$id} .elementor-widget-text-editor p{font-size:16px;margin:0}
.elementor-{$id} .elementor-field-textual{height:46px}
.elementor-{$id} .elementor-button{height:50px}
}
#elementor-popup-modal-{$id} .dialog-close-button{inset-inline-start:14px;inset-inline-end:auto;top:14px;color:#202020;font-size:18px}
CSS;
}

function almora_fp_ensure_popup() {
	if ( get_option( 'almora_fp_popup_id' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'   => 'elementor_library',
			'post_title'  => 'ALMORA - ' . ALMORA_FP_PERCENT . '% לרכישה ראשונה (פופאפ)',
			'post_status' => 'publish',
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	wp_set_object_terms( $id, 'popup', 'elementor_library_type' );
	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_template_type', 'popup' );
	update_post_meta( $id, '_elementor_version', ELEMENTOR_VERSION );
	update_post_meta( $id, '_wp_page_template', 'default' );
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( almora_fp_popup_data() ) ) );
	update_post_meta(
		$id,
		'_elementor_page_settings',
		array(
			'width'                => array( 'unit' => 'px', 'size' => 460 ),
			'width_mobile'         => array( 'unit' => 'vw', 'size' => 92 ),
			'height_type'          => 'auto',
			'content_position'     => 'center',
			'horizontal_position'  => 'center',
			'vertical_position'    => 'center',
			'overlay'              => 'yes',
			'overlay_opacity'      => array( 'unit' => 'px', 'size' => 0.5 ),
			'entrance_animation'   => 'fadeIn',
			'exit_animation'       => 'fadeIn',
			'close_button'         => 'yes',
			'custom_css'           => almora_fp_popup_css( $id ),
		)
	);
	// No automatic trigger: the popup opens only from the gift button.
	update_post_meta( $id, '_elementor_popup_display_settings', array( 'triggers' => array(), 'timing' => array() ) );
	// Load it on every page (Pro conditions manager keeps its own cache).
	if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->save_conditions(
			$id,
			array( array( 'type' => 'include', 'name' => 'general', 'sub_name' => '', 'sub_id' => '' ) )
		);
	}
	update_option( 'almora_fp_popup_id', $id, false );
}
add_action( 'admin_init', 'almora_fp_ensure_popup' );

// Versioned refresh of the popup content (bump ALMORA_FP_VER to re-apply almora_fp_popup_data()).
define( 'ALMORA_FP_VER', 6 );
add_action(
	'admin_init',
	function () {
		$id = (int) get_option( 'almora_fp_popup_id' );
		if ( ! $id || (int) get_option( 'almora_fp_ver' ) >= ALMORA_FP_VER ) {
			return;
		}
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( almora_fp_popup_data() ) ) );
		$ps = get_post_meta( $id, '_elementor_page_settings', true );
		$ps = is_array( $ps ) ? $ps : array();
		$ps['width']      = array( 'unit' => 'px', 'size' => 480 );
		$ps['custom_css'] = almora_fp_popup_css( $id );
		update_post_meta( $id, '_elementor_page_settings', $ps );
		delete_post_meta( $id, '_elementor_css' );
		delete_post_meta( $id, '_elementor_element_cache' );
		almora_fp_get_or_create_coupon();
		update_option( 'almora_fp_live', 1, true ); // owner asked to put the button on the site (2026-11-11)
		update_option( 'almora_fp_ver', ALMORA_FP_VER, false );
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
	},
	20
);

function almora_fp_visible() {
	if ( is_admin() || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
		return false;
	}
	return (bool) get_option( 'almora_fp_live' ) || ! empty( $_GET['almora-fp'] );
}

// Print the popup on the page (independent of the theme builder conditions cache).
add_action(
	'wp',
	function () {
		$id = (int) get_option( 'almora_fp_popup_id' );
		if ( $id && almora_fp_visible() && class_exists( '\ElementorPro\Modules\Popup\Module' ) ) {
			\ElementorPro\Modules\Popup\Module::add_popup_to_location( $id );
		}
	}
);

/**
 * Floating gift button "10%", just above the accessibility button (bottom left).
 * Visible when option almora_fp_live is on, or with ?almora-fp=1 for a preview.
 */
add_action(
	'wp_footer',
	function () {
		$id = (int) get_option( 'almora_fp_popup_id' );
		if ( ! $id || ! almora_fp_visible() ) {
			return;
		}
		$pct = (int) ALMORA_FP_PERCENT;
		?>
<button type="button" class="alm-gift" aria-label="<?php echo esc_attr( $pct ); ?>% הנחה לרכישה הראשונה" data-popup="<?php echo esc_attr( $id ); ?>">
<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" transform="translate(2.5 2.5)" d="M17 4L15.487 4C15.804 3.581 16 3.065 16 2.5C16 1.122 14.879 0 13.5 0C12.753 0 12.051 0.333 11.569 0.918C11.539 0.955 10.19 2.75 9.5 3.668C8.809 2.749 7.459 0.953 7.426 0.912C6.949 0.332 6.247 0 5.5 0C4.121 0 3 1.122 3 2.5C3 3.065 3.195 3.581 3.513 4L2 4C0.897 4 0 4.897 0 6L0 9.5C0 9.776 0.224 10 0.5 10L1 10L1 15.5C1 17.79 2.21 19 4.5 19L14.5 19C16.79 19 18 17.79 18 15.5L18 10L18.5 10C18.776 10 19 9.776 19 9.5L19 6C19 4.897 18.103 4 17 4ZM12.346 1.548C12.633 1.2 13.053 1 13.5 1C14.327 1 15 1.673 15 2.5C15 3.327 14.327 4 13.5 4L10.501 4C11.209 3.058 12.296 1.613 12.346 1.548ZM4 2.5C4 1.673 4.673 1 5.5 1C5.947 1 6.367 1.2 6.646 1.538C6.702 1.61 7.79 3.058 8.499 4L5.5 4C4.673 4 4 3.327 4 2.5ZM1 6C1 5.448 1.448 5 2 5L5.5 5L9 5L9 9L1 9L1 6ZM2 15.5L2 10L9 10L9 18L4.5 18C2.771 18 2 17.229 2 15.5ZM17 15.5C17 17.229 16.229 18 14.5 18L10 18L10 10L17 10L17 15.5ZM18 9L10 9L10 5L13.5 5L17 5C17.552 5 18 5.448 18 6L18 9Z"/></svg>
<span><?php echo esc_html( $pct ); ?>%</span>
</button>
<style>
.alm-gift{position:fixed;left:8px;bottom:69px;z-index:99999990;width:54px;height:54px;padding:0;border:0;border-radius:12px;background:#202020;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.18);font-family:Polin,sans-serif;line-height:1}
.alm-gift span{font-size:13px;font-weight:600;letter-spacing:.3px}
.alm-gift:hover,.alm-gift:focus{background:#202020;color:#fff;opacity:.9}
.alm-gift:focus-visible{outline:2px solid #B8860B;outline-offset:2px}
</style>
<script>
(function(){
var b=document.querySelector('.alm-gift'); if(!b) return;
function place(){var a=document.querySelector('.userway_buttons_wrapper'); if(!a) return; var r=a.getBoundingClientRect(); if(!r.width) return;
b.style.left=Math.max(4,Math.round(r.left+r.width/2-27))+'px'; b.style.bottom=Math.round(window.innerHeight-r.top+12)+'px';}
place(); setTimeout(place,1500); setTimeout(place,4000); window.addEventListener('resize',place);
document.addEventListener('click',function(e){
 var t=e.target.closest&&e.target.closest('.alm-fp-code,.alm-fp-copy'); if(!t) return;
 var code=t.getAttribute('data-code'); var btn=document.querySelector('.alm-fp-copy');
 function done(){ if(btn){ btn.textContent='הקוד הועתק'; setTimeout(function(){btn.textContent='העתקת הקוד';},2200);} if(window.gtag){gtag('event','almora_code_copy');} }
 if(navigator.clipboard&&navigator.clipboard.writeText){ navigator.clipboard.writeText(code).then(done,done); }
 else { var i=document.createElement('input'); i.value=code; document.body.appendChild(i); i.select(); try{document.execCommand('copy');}catch(x){} i.remove(); done(); }
});
b.addEventListener('click',function(){
 var id=parseInt(b.getAttribute('data-popup'),10);
 if(window.elementorProFrontend && elementorProFrontend.modules && elementorProFrontend.modules.popup){ elementorProFrontend.modules.popup.showPopup({id:id}); }
 if(window.gtag){ gtag('event','almora_gift_click'); }
});
})();
</script>
		<?php
	}
);
