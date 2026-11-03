<?php
/**
 * Plugin Name: ALMORA gift wrap
 * Description: Checkout: "gift wrap" checkbox (+9 ILS fee, not taxed) and a free dedication text (up to 200 characters). Both are saved on the order and shown on the admin order screen and in the order emails.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_GIFT_WRAP_PRICE = 9;

add_action( 'woocommerce_after_order_notes', function () {
	$on = WC()->session && WC()->session->get( 'almora_gift_wrap' );
	?>
<div class="almora-gift">
	<h3 class="almora-gift__title">מתנה?</h3>
	<label class="almora-gift__check"><input type="checkbox" name="almora_gift_wrap" value="1" <?php checked( $on ); ?>> <span>אריזת מתנה (+<?php echo (int) ALMORA_GIFT_WRAP_PRICE; ?> ₪)</span></label>
	<label class="almora-gift__label" for="almora_gift_note">הקדשה (לא חובה, ללא עלות)</label>
	<textarea id="almora_gift_note" name="almora_gift_note" maxlength="200" rows="3" placeholder="למשל: מזל טוב! באהבה, נועה"></textarea>
	<p class="almora-gift__hint">עד 200 תווים. ההקדשה תצורף לחבילה.</p>
</div>
<style>
.almora-gift{margin:24px 0 0;padding:20px;border:1px solid #e6e6e6;border-radius:12px;background:#fff}
.almora-gift__title{margin:0 0 12px;font-size:18px}
.almora-gift__check{display:flex;align-items:center;gap:10px;margin:0 0 16px;cursor:pointer;font-weight:600}
.almora-gift__check input{width:18px;height:18px;margin:0;accent-color:#202020}
.almora-gift__label{display:block;margin:0 0 6px}
.almora-gift textarea{width:100%;border-radius:12px;padding:12px;resize:vertical}
.almora-gift__hint{margin:6px 0 0;font-size:13px;color:#6b6b6b}
</style>
<script>
jQuery(function ($) {
	$(document.body).on('change', 'input[name="almora_gift_wrap"]', function () { $(document.body).trigger('update_checkout'); });
});
</script>
	<?php
} );

/* the checkbox state reaches the cart through the session: on every checkout refresh, and on the final submit */
add_action( 'woocommerce_checkout_update_order_review', function ( $post_data ) {
	parse_str( (string) $post_data, $d );
	WC()->session->set( 'almora_gift_wrap', ! empty( $d['almora_gift_wrap'] ) );
} );
add_action( 'woocommerce_checkout_process', function () {
	WC()->session->set( 'almora_gift_wrap', ! empty( $_POST['almora_gift_wrap'] ) );
} );

add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( WC()->session && WC()->session->get( 'almora_gift_wrap' ) ) {
		$cart->add_fee( 'אריזת מתנה', ALMORA_GIFT_WRAP_PRICE, false );
	}
} );

add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	$note = isset( $_POST['almora_gift_note'] ) ? mb_substr( sanitize_textarea_field( wp_unslash( $_POST['almora_gift_note'] ) ), 0, 200 ) : '';
	if ( '' !== $note ) {
		$order->update_meta_data( '_almora_gift_note', $note );
	}
	if ( ! empty( $_POST['almora_gift_wrap'] ) ) {
		$order->update_meta_data( '_almora_gift_wrap', 'yes' );
	}
} );

add_action( 'woocommerce_checkout_order_processed', function () {
	WC()->session->set( 'almora_gift_wrap', false );
} );

function almora_gift_summary_html( $order ) {
	$wrap = 'yes' === $order->get_meta( '_almora_gift_wrap' );
	$note = (string) $order->get_meta( '_almora_gift_note' );
	if ( ! $wrap && '' === $note ) {
		return '';
	}
	$html = '<div style="margin:16px 0;padding:12px 16px;border:1px solid #e6e6e6;border-radius:12px;">';
	if ( $wrap ) {
		$html .= '<p style="margin:0 0 6px;"><strong>אריזת מתנה:</strong> כן</p>';
	}
	if ( '' !== $note ) {
		$html .= '<p style="margin:0;"><strong>הקדשה:</strong><br>' . nl2br( esc_html( $note ) ) . '</p>';
	}
	return $html . '</div>';
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( $order ) {
	echo almora_gift_summary_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput
} );

add_action( 'woocommerce_email_order_meta', function ( $order, $sent_to_admin, $plain_text ) {
	if ( $plain_text ) {
		if ( 'yes' === $order->get_meta( '_almora_gift_wrap' ) ) {
			echo "\nאריזת מתנה: כן\n";
		}
		if ( $order->get_meta( '_almora_gift_note' ) ) {
			echo "הקדשה: " . $order->get_meta( '_almora_gift_note' ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		return;
	}
	echo almora_gift_summary_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput
}, 20, 3 );

add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
	echo almora_gift_summary_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput
} );