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
__EMAIL_HTML__
ALMORA_EMAIL;
	$first     = trim( preg_split( '/\s+/u', trim( (string) $name ) )[0] ?? '' );
	$name_line = $first !== '' ? esc_html( $first ) . ', ' : '';
	return strtr(
		$tpl,
		array(
			'{{CODE}}'      => esc_html( $code ),
			'{{PERCENT}}'   => (int) ALMORA_FP_PERCENT,
			'{{EXPIRY}}'    => esc_html( wp_date( 'd.m.Y', $expiry_ts ) ),
			'{{NAME_LINE}}' => $name_line,
			'{{LOGO}}'      => esc_url( home_url( '/wp-content/uploads/2026/09/ALMORA-header-compact.png' ) ),
			'{{SHOP_URL}}'  => esc_url( home_url( '/' ) ),
		)
	);
}

/**
 * One personal coupon per email address. A repeat sign-up gets the same code again.
 */
function almora_fp_get_or_create_coupon( $email ) {
	$email = strtolower( trim( $email ) );
	$found = get_posts(
		array(
			'post_type'      => 'shop_coupon',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'meta_key'       => '_almora_fp_email',
			'meta_value'     => $email,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $found ) {
		$c = new WC_Coupon( $found[0] );
		return array( $c->get_code(), $c->get_date_expires() ? $c->get_date_expires()->getTimestamp() : time() + ALMORA_FP_DAYS * DAY_IN_SECONDS, false );
	}

	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I
	do {
		$suffix = '';
		for ( $i = 0; $i < 5; $i++ ) {
			$suffix .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		$code = 'ALMORA-' . $suffix;
		$dupe = wc_get_coupon_id_by_code( $code );
	} while ( $dupe );

	$expires = time() + ALMORA_FP_DAYS * DAY_IN_SECONDS;
	$coupon  = new WC_Coupon();
	$coupon->set_code( $code );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( ALMORA_FP_PERCENT );
	$coupon->set_usage_limit( 1 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->set_email_restrictions( array( $email ) );
	$coupon->set_date_expires( $expires );
	$coupon->set_description( 'Popup: first purchase ' . ALMORA_FP_PERCENT . '% (auto)' );
	$coupon->update_meta_data( '_almora_fp_email', $email );
	$coupon->update_meta_data( '_almora_first_purchase', '1' );
	$coupon->save();

	return array( $code, $expires, true );
}

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

		$subject = 'הקופון שלכם מ-ALMORA: ' . ALMORA_FP_PERCENT . '% הנחה לרכישה הראשונה';
		$sent    = wp_mail(
			$email,
			$subject,
			almora_fp_email_html( $code, $expires, $name ),
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
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

/**
 * First order only: the coupon is refused when the customer already has an order.
 */
add_filter(
	'woocommerce_coupon_is_valid',
	function ( $valid, $coupon ) {
		if ( ! $valid || $coupon->get_meta( '_almora_first_purchase' ) !== '1' ) {
			return $valid;
		}
		$emails = array();
		$restr  = $coupon->get_email_restrictions();
		if ( $restr ) {
			$emails = array_map( 'strtolower', $restr );
		}
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
						'typography_font_size'   => array( 'unit' => 'px', 'size' => 34 ),
						'typography_font_size_mobile' => array( 'unit' => 'px', 'size' => 27 ),
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
						'editor'     => '<p>השאירו פרטים ונשלח אליכם במייל קוד קופון אישי, תקף ' . ALMORA_FP_DAYS . ' ימים.</p>',
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
					'widgetType' => 'form',
					'settings'   => array(
						'form_name'      => ALMORA_FP_FORM,
						'form_fields'    => array(
							array( 'custom_id' => 'name', 'field_type' => 'text', 'field_label' => 'שם מלא', 'placeholder' => 'שם מלא', 'required' => 'true', 'width' => '100', '_id' => 'fldname1' ),
							array( 'custom_id' => 'email', 'field_type' => 'email', 'field_label' => 'אימייל', 'placeholder' => 'אימייל', 'required' => 'true', 'width' => '100', '_id' => 'fldmail1' ),
							array( 'custom_id' => 'phone', 'field_type' => 'tel', 'field_label' => 'טלפון', 'placeholder' => 'טלפון (לא חובה)', 'width' => '100', '_id' => 'fldtel01' ),
							array( 'custom_id' => 'consent', 'field_type' => 'acceptance', 'field_label' => 'אישור', 'acceptance_text' => 'אני מאשר/ת קבלת הקוד ועדכונים מ-ALMORA במייל, אפשר להסיר בכל עת', 'required' => 'true', 'width' => '100', '_id' => 'fldcons1' ),
						),
						'show_labels'      => '',
						'button_text'      => 'שלחו לי את הקוד',
						'button_size'      => 'md',
						'button_width'     => '100',
						'submit_actions'   => array( 'save-to-database' ),
						'success_message'  => 'הקוד נשלח למייל שלכם. אם לא רואים אותו, כדאי להציץ גם בספאם.',
						'error_message'    => 'משהו השתבש, נסו שוב.',
						'required_field_message' => 'שדה חובה',
						'field_text_color' => $ink,
						'field_background_color' => '#FFFFFF',
						'field_border_color'     => '#C9C9C9',
						'field_border_radius'    => array( 'unit' => 'px', 'top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12', 'isLinked' => true ),
						'button_background_color'=> $ink,
						'button_text_color'      => '#FFFFFF',
						'button_border_radius'   => array( 'unit' => 'px', 'top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12', 'isLinked' => true ),
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
.elementor-{$id} .elementor-field-type-acceptance label{font-family:Polin,sans-serif;font-size:14px;font-weight:300;line-height:1.4}
.elementor-{$id} .elementor-button{height:52px;font-family:Polin,sans-serif;font-size:18px;font-weight:600;letter-spacing:.9px;background:#202020;color:#fff}
.elementor-{$id} .elementor-button:hover{background:#202020;color:#fff;opacity:.9}
.elementor-{$id} .elementor-message-success{font-family:Polin,sans-serif;font-size:17px;font-weight:600;text-align:center;color:#202020;padding:6px 0}
.elementor-{$id} .elementor-message-danger{font-family:Polin,sans-serif;text-align:center}
#elementor-popup-modal-{$id} .dialog-message{border-radius:16px;overflow:hidden;max-width:92vw}
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
			'post_status' => 'draft',
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
	update_post_meta(
		$id,
		'_elementor_popup_display_settings',
		array(
			'triggers' => array(
				'page_load'       => 'yes',
				'page_load_delay' => 8,
			),
			'timing'   => array(
				'times'       => 'yes',
				'times_times' => 1,
				'times_count' => 1,
				'times_period' => 'week',
			),
		)
	);
	update_option( 'almora_fp_popup_id', $id, false );
}
add_action( 'admin_init', 'almora_fp_ensure_popup' );
