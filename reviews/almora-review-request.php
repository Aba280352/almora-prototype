<?php
/**
 * Plugin Name: ALMORA review request
 * Description: 5 days after an order is marked completed, one email (no incentive) asks the customer to review what they bought. Each product link carries a signed token: the review form opens with the first name filled in, and the review is saved with the order email so WooCommerce marks it a verified purchase. Runs only when option almora_review_request_live is 1; orders completed before that are not emailed.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_RR_DELAY = 5 * DAY_IN_SECONDS;

function almora_rr_token( $order_id, $product_id ) {
	return substr( hash_hmac( 'sha256', $order_id . '|' . $product_id, wp_salt( 'auth' ) ), 0, 20 );
}

/** Products of the order (variations folded into their parent), only ones still published. */
function almora_rr_products( $order ) {
	$ids = array();
	foreach ( $order->get_items() as $item ) {
		$pid = $item->get_product_id();
		if ( $pid && 'publish' === get_post_status( $pid ) ) {
			$ids[ $pid ] = $pid;
		}
	}
	return array_values( $ids );
}

/** The order behind a review link, or null when the link is not valid for this product. */
function almora_rr_parse( $rv, $product_id ) {
	if ( ! preg_match( '/^(\d+)-([a-f0-9]{20})$/', (string) $rv, $m ) ) {
		return null;
	}
	if ( ! hash_equals( almora_rr_token( (int) $m[1], (int) $product_id ), $m[2] ) ) {
		return null;
	}
	$order = wc_get_order( (int) $m[1] );
	return ( $order && in_array( (int) $product_id, almora_rr_products( $order ), true ) ) ? $order : null;
}

/* schedule: once per order, when it is marked completed */
add_action( 'woocommerce_order_status_completed', function ( $order_id ) {
	if ( '1' !== get_option( 'almora_review_request_live' ) || ! function_exists( 'as_schedule_single_action' ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_almora_rr_scheduled' ) ) {
		return;
	}
	$order->update_meta_data( '_almora_rr_scheduled', time() );
	$order->save();
	as_schedule_single_action( time() + ALMORA_RR_DELAY, 'almora_review_request_send', array( (int) $order_id ), 'almora' );
} );

add_action( 'almora_review_request_send', 'almora_rr_send' );

/** Sends the email. With $to it is a test: the order is not marked as sent. */
function almora_rr_send( $order_id, $to = '' ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return false;
	}
	$test = (bool) $to;
	if ( ! $test ) {
		if ( $order->get_meta( '_almora_rr_sent' ) || in_array( $order->get_status(), array( 'refunded', 'cancelled', 'failed' ), true ) ) {
			return false;
		}
		$to = $order->get_billing_email();
	}
	$products = almora_rr_products( $order );
	if ( ! is_email( $to ) || ! $products ) {
		return false;
	}

	$name  = trim( $order->get_billing_first_name() );
	$cards = '';
	foreach ( $products as $pid ) {
		$product = wc_get_product( $pid );
		$url     = add_query_arg( 'almora_rv', $order->get_id() . '-' . almora_rr_token( $order->get_id(), $pid ), get_permalink( $pid ) ) . '#reviews';
		$img     = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
		$cards  .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;border:1px solid #e6e6e6;border-radius:12px;"><tr>'
			. '<td width="96" style="padding:12px;"><img src="' . esc_url( $img ) . '" width="80" height="80" alt="" style="display:block;border-radius:12px;object-fit:cover;"></td>'
			. '<td style="padding:12px 4px 12px 12px;text-align:right;"><div style="font-size:15px;color:#202020;margin:0 0 10px;">' . esc_html( $product->get_name() ) . '</div>'
			. '<a href="' . esc_url( $url ) . '" style="display:inline-block;background:#202020;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:10px 22px;border-radius:12px;">לכתיבת ביקורת</a></td>'
			. '</tr></table>';
	}

	$body = '<div dir="rtl" style="text-align:right;font-size:15px;line-height:1.7;color:#202020;">'
		. '<p>' . ( $name ? 'היי ' . esc_html( $name ) . ',' : 'היי,' ) . '</p>'
		. '<p>ההזמנה שלך מ-ALMORA הגיעה לפני כמה ימים, ונשמח לדעת מה דעתך על התכשיט.<br>ביקורת קצרה, אפילו שורה אחת, עוזרת ללקוחות אחרות ואחרים לבחור, ועוזרת לנו להשתפר.</p>'
		. $cards
		. '<p style="font-size:13px;color:#6b6b6b;">זה מייל חד־פעמי על ההזמנה שלך. אם משהו לא תקין עם התכשיט, אפשר להשיב למייל הזה או לכתוב לנו בוואטסאפ 052-3550851.</p>'
		. '<p>תודה,<br>צוות ALMORA</p>'
		. '</div>';

	$mailer  = WC()->mailer();
	$email   = new WC_Email();
	$html    = $email->style_inline( $mailer->wrap_message( 'נשמח לשמוע מה דעתך', $body ) );
	$subject = ( $test ? '[בדיקה] ' : '' ) . 'איך התכשיט? נשמח לשמוע ממך';
	$sent    = $mailer->send( $to, $subject, $html, "Content-Type: text/html\r\n" );

	if ( $sent && ! $test ) {
		$order->update_meta_data( '_almora_rr_sent', time() );
		$order->add_order_note( 'נשלח ללקוח מייל בקשת ביקורת.' );
		$order->save();
	}
	return $sent;
}

/* product page opened from the email: open the review form, fill the first name, carry the token */
add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() || empty( $_GET['almora_rv'] ) ) {
		return;
	}
	$rv    = sanitize_text_field( wp_unslash( $_GET['almora_rv'] ) );
	$order = almora_rr_parse( $rv, get_the_ID() );
	if ( ! $order ) {
		return;
	}
	?>
<script>
(function () {
	var rv = <?php echo wp_json_encode( $rv ); ?>, nm = <?php echo wp_json_encode( trim( $order->get_billing_first_name() ) ); ?>;
	function go() {
		var f = document.getElementById('commentform');
		if (!f) { return; }
		var h = document.createElement('input');
		h.type = 'hidden'; h.name = 'almora_rv'; h.value = rv;
		f.appendChild(h);
		var a = f.querySelector('#author');
		if (a && !a.value) { a.value = nm; }
		var b = document.querySelector('.ecomus-form-review');
		if (b) { b.click(); } else { var r = document.getElementById('reviews'); if (r) { r.scrollIntoView(); } }
	}
	if (document.readyState === 'complete') { setTimeout(go, 400); } else { window.addEventListener('load', function () { setTimeout(go, 400); }); }
})();
</script>
	<?php
}, 99 );

/* the review from the email link gets the order email, so WooCommerce marks it a verified purchase */
add_filter( 'preprocess_comment', function ( $data ) {
	if ( empty( $_POST['almora_rv'] ) || 'product' !== get_post_type( (int) $data['comment_post_ID'] ) ) {
		return $data;
	}
	$order = almora_rr_parse( sanitize_text_field( wp_unslash( $_POST['almora_rv'] ) ), (int) $data['comment_post_ID'] );
	if ( $order && is_email( $order->get_billing_email() ) ) {
		$data['comment_author_email'] = $order->get_billing_email();
	}
	return $data;
}, 6 );
