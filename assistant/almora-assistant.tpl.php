<?php
/**
 * Plugin Name: ALMORA shopping assistant
 * Description: A floating guided assistant on every page (no external AI): asks who the jewellery is for, which type and the budget, shows real products from the catalogue, adds to the cart from the chat, answers the common questions and hands over to WhatsApp with a summary of the conversation. Shown to visitors when the option almora_bot_live is on; before that only with ?almora-bot=1.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_BOT_WA    = '972523550851';
const ALMORA_BOT_PHONE = '052-3550851';
const ALMORA_BOT_FREE  = 1000;
const ALMORA_BOT_CATS  = array(
	'ring'     => 66,
	'earrings' => 67,
	'necklace' => 68,
	'bracelet' => 69,
	'men'      => 124,
);

/* ------------------------------------------------------------------ catalogue */

function almora_bot_item( $p ) {
	$img = $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' ) : '';
	if ( $p->is_type( 'variable' ) ) {
		$min   = (float) $p->get_variation_price( 'min', true );
		$max   = (float) $p->get_variation_price( 'max', true );
		$reg   = (float) $p->get_variation_regular_price( 'min', true );
		$price = $min;
		$from  = $max > $min;
	} else {
		$price = (float) wc_get_price_to_display( $p );
		$reg   = (float) wc_get_price_to_display( $p, array( 'price' => $p->get_regular_price() ) );
		$from  = false;
	}
	return array(
		'id' => $p->get_id(),
		'n'  => html_entity_decode( $p->get_name(), ENT_QUOTES, 'UTF-8' ),
		'u'  => get_permalink( $p->get_id() ),
		'i'  => $img ? $img : wc_placeholder_img_src(),
		'p'  => $price,
		'rp' => $reg > $price ? $reg : 0,
		'v'  => $p->is_type( 'variable' ),
		'f'  => $from,
	);
}

function almora_bot_ids( $cat, $min, $max, $s ) {
	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'fields'              => 'ids',
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'post__not_in'        => array( (int) get_option( 'almora_test_product_id', 0 ) ),
		'meta_query'          => array(
			'relation' => 'AND',
			array( 'key' => '_stock_status', 'value' => array( 'instock', 'onbackorder' ), 'compare' => 'IN' ),
			array( 'key' => '_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ),
		),
		'tax_query'           => array(
			'relation' => 'AND',
			array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => array( 'exclude-from-catalog' ), 'operator' => 'NOT IN' ),
		),
	);
	if ( $max > 0 ) {
		$args['meta_query'][] = array( 'key' => '_price', 'value' => $max * 1.25, 'compare' => '<=', 'type' => 'NUMERIC' );
	}
	if ( $min > 0 ) {
		$args['meta_query'][] = array( 'key' => '_price', 'value' => $min / 1.25, 'compare' => '>=', 'type' => 'NUMERIC' );
	}
	if ( $cat && isset( ALMORA_BOT_CATS[ $cat ] ) ) {
		$args['tax_query'][] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array( ALMORA_BOT_CATS[ $cat ] ) );
	}
	if ( '' !== $s ) {
		$args['s'] = $s;
	}
	$ids = get_posts( $args );
	if ( '' === $s && $ids ) {
		update_meta_cache( 'post', $ids );
		usort( $ids, function ( $a, $b ) {
			$d = (int) get_post_meta( $b, 'total_sales', true ) - (int) get_post_meta( $a, 'total_sales', true );
			return $d ? $d : $b - $a;
		} );
	}
	return $ids;
}

function almora_bot_rest_products( WP_REST_Request $r ) {
	$cat = sanitize_key( (string) $r->get_param( 'cat' ) );
	$min = max( 0, (float) $r->get_param( 'min' ) );
	$max = max( 0, (float) $r->get_param( 'max' ) );
	$off = max( 0, (int) $r->get_param( 'off' ) );
	$s   = trim( sanitize_text_field( (string) $r->get_param( 's' ) ) );
	$per = 3;

	$ids = almora_bot_ids( $cat, $min, $max, $s );
	if ( '' !== $s && ! $ids ) {
		$hits = array();
		foreach ( preg_split( '/\s+/u', $s ) as $w ) {
			if ( mb_strlen( $w ) < 3 ) {
				continue;
			}
			foreach ( almora_bot_ids( $cat, $min, $max, $w ) as $id ) {
				$hits[ $id ] = ( $hits[ $id ] ?? 0 ) + 1;
			}
		}
		arsort( $hits );
		$ids = array_keys( $hits );
	}

	$items = array();
	$more  = false;
	$seen  = 0;
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p || ! $p->is_visible() ) {
			continue;
		}
		$it = almora_bot_item( $p );
		if ( ( $min > 0 && $it['p'] < $min ) || ( $max > 0 && $it['p'] > $max ) ) {
			continue;
		}
		if ( $seen++ < $off ) {
			continue;
		}
		if ( count( $items ) >= $per ) {
			$more = true;
			break;
		}
		$items[] = $it;
	}
	return rest_ensure_response( array( 'items' => $items, 'more' => $more ) );
}

function almora_bot_rest_variations( WP_REST_Request $r ) {
	$p = wc_get_product( (int) $r->get_param( 'id' ) );
	if ( ! $p || ! $p->is_type( 'variable' ) ) {
		return new WP_Error( 'almora_bot_nf', 'not found', array( 'status' => 404 ) );
	}
	$list = array();
	$used = array();
	foreach ( $p->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( ! $v || 'publish' !== $v->get_status() || ! $v->variation_is_visible() ) {
			continue;
		}
		$a = array();
		foreach ( $v->get_attributes() as $k => $val ) {
			$a[ $k ]          = (string) $val;
			$used[ $k ][ (string) $val ] = true;
		}
		$price = (float) wc_get_price_to_display( $v );
		$reg   = (float) wc_get_price_to_display( $v, array( 'price' => $v->get_regular_price() ) );
		$list[] = array(
			'id' => $vid,
			'a'  => (object) $a,
			'p'  => $price,
			'rp' => $reg > $price ? $reg : 0,
			'in' => $v->is_in_stock() && $v->is_purchasable(),
		);
	}
	$attrs = array();
	foreach ( $p->get_attributes() as $key => $a ) {
		if ( ! $a->get_variation() ) {
			continue;
		}
		$opts = array();
		$all  = isset( $used[ $key ][''] );
		$raw  = $a->is_taxonomy() ? $a->get_slugs() : $a->get_options();
		foreach ( $raw as $o ) {
			$o = (string) $o;
			if ( ! $all && ! isset( $used[ $key ][ $o ] ) ) {
				continue;
			}
			$label = $o;
			if ( $a->is_taxonomy() ) {
				$t     = get_term_by( 'slug', $o, $a->get_name() );
				$label = $t ? $t->name : $o;
			}
			$opts[] = array( 'v' => $o, 'l' => $label );
		}
		$attrs[] = array( 'k' => (string) $key, 'l' => wc_attribute_label( $a->get_name(), $p ), 'o' => $opts );
	}
	return rest_ensure_response( array( 'attrs' => $attrs, 'list' => $list ) );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'almora/v1', '/bot/products', array(
		'methods'             => 'GET',
		'callback'            => 'almora_bot_rest_products',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'almora/v1', '/bot/variations', array(
		'methods'             => 'GET',
		'callback'            => 'almora_bot_rest_variations',
		'permission_callback' => '__return_true',
	) );
} );

/* ------------------------------------------------------------------ add to cart */

add_action( 'wc_ajax_almora_bot_add', function () {
	$fail = function ( $m ) {
		wp_send_json( array( 'ok' => false, 'm' => $m ) );
	};
	// phpcs:disable WordPress.Security.NonceVerification
	$pid = absint( $_POST['product_id'] ?? 0 );
	$vid = absint( $_POST['variation_id'] ?? 0 );
	$sel = json_decode( wp_unslash( $_POST['sel'] ?? '{}' ), true );
	// phpcs:enable
	$sel = is_array( $sel ) ? $sel : array();
	$p   = $pid ? wc_get_product( $pid ) : false;
	if ( ! $p || 'publish' !== get_post_status( $pid ) ) {
		$fail( 'המוצר לא נמצא.' );
	}
	$variation = array();
	if ( $p->is_type( 'variable' ) ) {
		$v = $vid ? wc_get_product( $vid ) : false;
		if ( ! $v || ! $v->is_type( 'variation' ) || (int) $v->get_parent_id() !== $pid ) {
			$fail( 'האפשרות שנבחרה לא זמינה.' );
		}
		$variation = $v->get_variation_attributes();
		foreach ( $variation as $k => $val ) {
			if ( '' === $val ) {
				$key           = substr( $k, 10 );
				$variation[ $k ] = isset( $sel[ $key ] ) ? wc_clean( (string) $sel[ $key ] ) : '';
			}
		}
	} else {
		$vid = 0;
	}
	wc_clear_notices();
	$added = WC()->cart->add_to_cart( $pid, 1, $vid, $variation );
	if ( ! $added ) {
		$msg = 'לא הצלחנו להוסיף לסל.';
		$err = wc_get_notices( 'error' );
		if ( $err ) {
			$first = reset( $err );
			$msg   = wp_strip_all_tags( is_array( $first ) ? ( $first['notice'] ?? $msg ) : $first );
		}
		wc_clear_notices();
		$fail( $msg );
	}
	wc_clear_notices();
	ob_start();
	woocommerce_mini_cart();
	$mini = ob_get_clean();
	$frag = apply_filters( 'woocommerce_add_to_cart_fragments', array(
		'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini . '</div>',
	) );
	wp_send_json( array(
		'ok'    => true,
		'frag'  => $frag,
		'hash'  => WC()->cart->get_cart_hash(),
		'sub'   => (float) WC()->cart->get_displayed_subtotal(),
		'count' => (int) WC()->cart->get_cart_contents_count(),
	) );
} );

/* ------------------------------------------------------------------ front end */

function almora_bot_enabled() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() || is_customize_preview() ) {
		return false;
	}
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return false;
	}
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
		return false;
	}
	return (bool) apply_filters( 'almora_bot_enabled', true );
}

add_filter( 'rocket_delay_js_exclusions', function ( $e ) {
	$e[] = 'almora-bot-js';
	$e[] = 'ALMORA_BOT';
	return $e;
} );
add_filter( 'rocket_exclude_defer_js', function ( $e ) {
	$e[] = 'almora-bot-js';
	return $e;
} );

add_action( 'wp_footer', function () {
	if ( ! almora_bot_enabled() ) {
		return;
	}
	$icons = json_decode( <<<'ICONS'
__ICONS__
ICONS
	, true );
	$cfg = array(
		'live'     => (bool) get_option( 'almora_bot_live' ),
		'rest'     => esc_url_raw( rest_url( 'almora/v1/bot/' ) ),
		'ajax'     => esc_url_raw( WC_AJAX::get_endpoint( 'almora_bot_add' ) ),
		'wa'       => ALMORA_BOT_WA,
		'phone'    => ALMORA_BOT_PHONE,
		'free'     => ALMORA_BOT_FREE,
		'cart'     => wc_get_cart_url(),
		'checkout' => wc_get_checkout_url(),
		'icons'    => $icons,
	);
	?>
<div id="almora-bot" class="alb" dir="rtl" hidden>
	<button type="button" class="alb-fab" aria-label="העוזר של ALMORA" title="העוזר של ALMORA" aria-expanded="false" aria-controls="alb-panel"><span class="alb-fab__a"></span><span class="alb-fab__b"></span></button>
</div>
<style id="almora-bot-css">
__CSS__
</style>
<script id="almora-bot-js">
window.ALMORA_BOT=<?php echo wp_json_encode( $cfg ); ?>;
/* almora-bot-js */
__JS__
</script>
	<?php
}, 99 );
