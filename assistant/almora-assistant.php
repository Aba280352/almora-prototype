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
{"chat":"<path transform=\"translate(2.5 2.5)\" d=\"M9.501 0C4.264 0 0.003 4.261 0.003 9.499C0.003 10.863 0.306 12.236 0.881 13.474C1.025 13.774 1.036 14.148 0.919 14.519L0.078 17.02C-0.103 17.562 0.038 18.151 0.444 18.557C0.732 18.846 1.114 19 1.505 19C1.664 19 1.824 18.974 1.982 18.922L4.475 18.085C4.852 17.964 5.226 17.977 5.532 18.122C6.765 18.695 8.138 18.998 9.502 18.998C14.739 18.998 19.001 14.737 19.001 9.499C19.001 4.261 14.738 0 9.501 0ZM9.501 17.998C8.282 17.998 7.056 17.727 5.958 17.217C5.427 16.963 4.792 16.933 4.163 17.134L1.664 17.973C1.481 18.033 1.29 17.987 1.151 17.849C1.012 17.711 0.965 17.518 1.026 17.337L1.869 14.83C2.066 14.208 2.036 13.574 1.785 13.048C1.273 11.945 1.003 10.718 1.003 9.499C1.003 4.813 4.815 1 9.501 1C14.188 1 18 4.812 18 9.499C18 14.186 14.188 17.998 9.501 17.998ZM10.25 9.5C10.25 9.914 9.916 10.25 9.504 10.25C9.093 10.25 8.75 9.914 8.75 9.5C8.75 9.086 9.077 8.75 9.489 8.75L9.504 8.75C9.916 8.75 10.25 9.086 10.25 9.5ZM14.25 9.5C14.25 9.914 13.916 10.25 13.504 10.25C13.093 10.25 12.75 9.914 12.75 9.5C12.75 9.086 13.077 8.75 13.489 8.75L13.504 8.75C13.916 8.75 14.25 9.086 14.25 9.5ZM6.25 9.5C6.25 9.914 5.916 10.25 5.504 10.25C5.093 10.25 4.75 9.914 4.75 9.5C4.75 9.086 5.077 8.75 5.489 8.75L5.504 8.75C5.916 8.75 6.25 9.086 6.25 9.5Z\"/>","times":"<path transform=\"translate(5.5 5.501)\" d=\"M12.854 12.145C13.049 12.34 13.049 12.657 12.854 12.852C12.756 12.95 12.628 12.998 12.5 12.998C12.372 12.998 12.244 12.949 12.146 12.852L6.5 7.206L0.854 12.852C0.756 12.95 0.628 12.998 0.5 12.998C0.372 12.998 0.244 12.949 0.146 12.852C-0.049 12.657 -0.049 12.34 0.146 12.145L5.792 6.499L0.146 0.853C-0.049 0.658 -0.049 0.341 0.146 0.146C0.341 -0.049 0.658 -0.049 0.853 0.146L6.499 5.792L12.145 0.146C12.34 -0.049 12.657 -0.049 12.852 0.146C13.047 0.341 13.047 0.658 12.852 0.853L7.207 6.499L12.854 12.145Z\"/>","restart":"<path transform=\"translate(2.5 2.5)\" d=\"M19 9.5C19 14.738 14.738 19 9.5 19C5.869 19 2.609 16.978 0.992 13.722C0.87 13.475 0.971 13.175 1.218 13.052C1.464 12.93 1.765 13.029 1.889 13.278C3.335 16.191 6.251 18 9.5 18C14.187 18 18 14.187 18 9.5C18 4.813 14.187 1 9.5 1C6.148 1 3.147 2.99 1.786 6L5.5 6C5.776 6 6 6.224 6 6.5C6 6.776 5.776 7 5.5 7L0.5 7C0.224 7 0 6.776 0 6.5L0 1.5C0 1.224 0.224 1 0.5 1C0.776 1 1 1.224 1 1.5L1 5.28C2.583 2.083 5.865 0 9.5 0C14.738 0 19 4.262 19 9.5Z\"/>","send":"<path transform=\"translate(2.5 2.5)\" d=\"M18.25 0.75C17.61 0.11 16.704 -0.141 15.825 0.078L2.712 3.356C0.825 3.828 -0.01 5.453 0 6.855C0.01 8.256 0.867 9.87 2.761 10.315L7.557 11.443L8.686 16.239C9.13 18.132 10.744 18.989 12.145 18.999C12.154 18.999 12.162 18.999 12.169 18.999C13.565 18.999 15.175 18.163 15.644 16.287L18.922 3.174C19.142 2.296 18.89 1.39 18.25 0.75ZM17.951 2.932L14.673 16.045C14.311 17.498 13.064 18.013 12.152 18C11.215 17.993 10.001 17.469 9.658 16.011L8.523 11.185L12.854 6.854C13.049 6.659 13.049 6.342 12.854 6.147C12.659 5.952 12.342 5.952 12.147 6.147L7.817 10.478L2.99 9.342C1.532 8.999 1.008 7.784 1.001 6.848C0.994 5.912 1.502 4.69 2.955 4.327L16.068 1.049C16.196 1.017 16.324 1.002 16.451 1.002C16.856 1.002 17.247 1.161 17.544 1.458C17.933 1.846 18.085 2.398 17.951 2.932Z\"/>","bag":"<path transform=\"translate(3.5 2.5)\" d=\"M15 5L12.5 5L12.5 4C12.5 1.794 10.706 0 8.5 0C6.294 0 4.5 1.794 4.5 4L4.5 5L2 5C0.897 5 0 5.897 0 7L0 15.5C0 17.79 1.21 19 3.5 19L13.5 19C15.79 19 17 17.79 17 15.5L17 7C17 5.897 16.103 5 15 5ZM5.5 4C5.5 2.346 6.846 1 8.5 1C10.154 1 11.5 2.346 11.5 4L11.5 5L5.5 5L5.5 4ZM16 15.5C16 17.229 15.229 18 13.5 18L3.5 18C1.771 18 1 17.229 1 15.5L1 7C1 6.448 1.448 6 2 6L4.5 6L4.5 8.5C4.5 8.776 4.724 9 5 9C5.276 9 5.5 8.776 5.5 8.5L5.5 6L11.5 6L11.5 8.5C11.5 8.776 11.724 9 12 9C12.276 9 12.5 8.776 12.5 8.5L12.5 6L15 6C15.552 6 16 6.448 16 7L16 15.5Z\"/>","gift":"<path transform=\"translate(2.5 2.5)\" d=\"M17 4L15.487 4C15.804 3.581 16 3.065 16 2.5C16 1.122 14.879 0 13.5 0C12.753 0 12.051 0.333 11.569 0.918C11.539 0.955 10.19 2.75 9.5 3.668C8.809 2.749 7.459 0.953 7.426 0.912C6.949 0.332 6.247 0 5.5 0C4.121 0 3 1.122 3 2.5C3 3.065 3.195 3.581 3.513 4L2 4C0.897 4 0 4.897 0 6L0 9.5C0 9.776 0.224 10 0.5 10L1 10L1 15.5C1 17.79 2.21 19 4.5 19L14.5 19C16.79 19 18 17.79 18 15.5L18 10L18.5 10C18.776 10 19 9.776 19 9.5L19 6C19 4.897 18.103 4 17 4ZM12.346 1.548C12.633 1.2 13.053 1 13.5 1C14.327 1 15 1.673 15 2.5C15 3.327 14.327 4 13.5 4L10.501 4C11.209 3.058 12.296 1.613 12.346 1.548ZM4 2.5C4 1.673 4.673 1 5.5 1C5.947 1 6.367 1.2 6.646 1.538C6.702 1.61 7.79 3.058 8.499 4L5.5 4C4.673 4 4 3.327 4 2.5ZM1 6C1 5.448 1.448 5 2 5L5.5 5L9 5L9 9L1 9L1 6ZM2 15.5L2 10L9 10L9 18L4.5 18C2.771 18 2 17.229 2 15.5ZM17 15.5C17 17.229 16.229 18 14.5 18L10 18L10 10L17 10L17 15.5ZM18 9L10 9L10 5L13.5 5L17 5C17.552 5 18 5.448 18 6L18 9Z\"/>","sparkle":"<path transform=\"translate(2.498 2.54)\" d=\"M18.639 8.979L15.912 8.2C13.404 7.483 11.478 5.558 10.761 3.049L9.982 0.322C9.859 -0.107 9.143 -0.107 9.02 0.322L8.241 3.049C7.524 5.558 5.598 7.484 3.09 8.2L0.363 8.979C0.148 9.04 0 9.237 0 9.46C0 9.683 0.148 9.879 0.363 9.941L3.09 10.72C5.598 11.437 7.524 13.362 8.241 15.871L9.02 18.598C9.082 18.813 9.278 18.961 9.501 18.961C9.724 18.961 9.92 18.813 9.982 18.598L10.761 15.871C11.478 13.362 13.404 11.436 15.912 10.72L18.639 9.941C18.854 9.88 19.002 9.683 19.002 9.46C19.002 9.237 18.854 9.041 18.639 8.979ZM15.638 9.758C12.795 10.57 10.613 12.752 9.8 15.596L9.502 16.639L9.204 15.596C8.391 12.753 6.208 10.57 3.366 9.758L2.322 9.46L3.366 9.162C6.209 8.35 8.391 6.168 9.204 3.324L9.502 2.281L9.8 3.324C10.613 6.167 12.796 8.35 15.638 9.162L16.682 9.46L15.638 9.758Z\"/>","back":"<path transform=\"translate(2.5 4.502)\" d=\"M18.853 7.852L11.853 14.852C11.755 14.95 11.627 14.998 11.499 14.998C11.371 14.998 11.243 14.949 11.145 14.852C10.95 14.657 10.95 14.34 11.145 14.145L17.291 7.999L0.5 7.999C0.224 7.999 0 7.775 0 7.499C0 7.223 0.224 6.999 0.5 6.999L17.293 6.999L11.147 0.853C10.952 0.658 10.952 0.341 11.147 0.146C11.342 -0.049 11.659 -0.049 11.854 0.146L18.854 7.146C18.9 7.192 18.937 7.248 18.962 7.309C19.013 7.431 19.013 7.569 18.962 7.691C18.936 7.75 18.899 7.806 18.853 7.852Z\"/>","check":"<path transform=\"translate(4.501 6.5)\" d=\"M14.853 0.854L4.853 10.854C4.759 10.948 4.632 11 4.499 11C4.386 10.988 4.237 10.946 4.143 10.851L0.143 6.789C-0.05 6.592 -0.048 6.276 0.149 6.082C0.345 5.888 0.662 5.891 0.856 6.087L4.502 9.79L14.146 0.146C14.341 -0.049 14.658 -0.049 14.853 0.146C15.048 0.341 15.048 0.658 14.853 0.854Z\"/>"}
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
#almora-bot{--alb-ink:#202020;--alb-sur:#F5F5F5;--alb-line:#E4E4E4;--alb-mute:#666;--alb-sale:#E24A49;--alb-bottom:84px;font-family:inherit;color:var(--alb-ink);direction:rtl;text-align:right;font-size:15px;line-height:1.5;letter-spacing:0}
#almora-bot[hidden]{display:none!important}
#almora-bot *,#almora-bot *::before,#almora-bot *::after{box-sizing:border-box}
#almora-bot [hidden]{display:none!important}
#almora-bot button,#almora-bot input,#almora-bot a{font-family:inherit;letter-spacing:0;text-transform:none}
#almora-bot svg{display:block;fill:currentColor}
html.alb-lock{overflow:hidden}

#almora-bot .alb-fab{position:fixed;right:15px;bottom:var(--alb-bottom);z-index:99999990;width:54px;height:54px;margin:0;padding:0;border:0!important;border-radius:12px!important;background:var(--alb-ink)!important;color:#fff!important;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 6px 22px rgba(0,0,0,.2);transition:transform .2s ease,box-shadow .2s ease}
#almora-bot .alb-fab:hover,#almora-bot .alb-fab:focus-visible{transform:translateY(-2px);box-shadow:0 10px 26px rgba(0,0,0,.26);background:var(--alb-ink)!important;color:#fff!important}
#almora-bot .alb-fab:focus-visible{outline:2px solid #fff;outline-offset:-5px}
#almora-bot .alb-fab__a,#almora-bot .alb-fab__b{display:flex}
#almora-bot .alb-fab__b{display:none}
#almora-bot.is-open .alb-fab__a{display:none}
#almora-bot.is-open .alb-fab__b{display:flex}

#almora-bot .alb-panel{position:fixed;right:15px;bottom:calc(var(--alb-bottom) + 66px);z-index:99999991;width:392px;max-width:calc(100vw - 30px);height:min(640px,calc(100vh - var(--alb-bottom) - 82px));height:min(640px,calc(100dvh - var(--alb-bottom) - 82px));display:flex;flex-direction:column;background:#fff;border-radius:12px;box-shadow:0 18px 60px rgba(0,0,0,.24),0 0 0 1px rgba(0,0,0,.06);overflow:hidden;outline:0}
#almora-bot .alb-head{display:flex;align-items:center;gap:8px;padding:14px 16px 12px;border-bottom:1px solid var(--alb-line);flex:none}
#almora-bot .alb-head__t{flex:1;min-width:0}
#almora-bot .alb-head__n{font-size:16px;font-weight:600;line-height:1.3;margin:0}
#almora-bot .alb-head__s{font-size:12.5px;color:var(--alb-mute);line-height:1.4;margin:2px 0 0}
#almora-bot .alb-ib{width:40px;height:40px;margin:0;padding:0;border:0!important;border-radius:12px!important;background:transparent!important;color:var(--alb-ink)!important;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:none}
#almora-bot .alb-ib:hover,#almora-bot .alb-ib:focus-visible{background:var(--alb-sur)!important;color:var(--alb-ink)!important}

#almora-bot .alb-body{position:relative;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding:16px;display:flex;flex-direction:column;gap:10px}
#almora-bot .alb-m{max-width:88%;padding:11px 14px;border-radius:12px;font-size:15px;line-height:1.55;overflow-wrap:anywhere;white-space:pre-line;margin:0}
#almora-bot .alb-m--b{align-self:flex-start;background:var(--alb-sur);color:var(--alb-ink)}
#almora-bot .alb-m--u{align-self:flex-end;background:var(--alb-ink);color:#fff}
#almora-bot .alb-c{display:flex;flex-wrap:wrap;gap:8px;align-self:stretch}
#almora-bot .alb-chip{display:inline-flex;align-items:center;gap:6px;min-height:40px;margin:0;padding:8px 14px;border:1px solid var(--alb-ink)!important;border-radius:12px!important;background:#fff!important;color:var(--alb-ink)!important;font-size:14px;font-weight:600;line-height:1.3;text-decoration:none!important;cursor:pointer;text-align:right}
#almora-bot .alb-chip:hover,#almora-bot .alb-chip:focus-visible{background:var(--alb-sur)!important;color:var(--alb-ink)!important}
#almora-bot .alb-chip--pri{background:var(--alb-ink)!important;color:#fff!important}
#almora-bot .alb-chip--pri:hover,#almora-bot .alb-chip--pri:focus-visible{background:#000!important;color:#fff!important}
#almora-bot .alb-chip:focus-visible,#almora-bot .alb-btn:focus-visible,#almora-bot .alb-ib:focus-visible,#almora-bot .alb-opt:focus-visible,#almora-bot .alb-link:focus-visible,#almora-bot .alb-p__name:focus-visible{outline:2px solid var(--alb-ink);outline-offset:2px}

#almora-bot .alb-ps{display:flex;flex-direction:column;gap:8px;align-self:stretch}
#almora-bot .alb-p{display:flex;flex-wrap:wrap;gap:10px 12px;background:var(--alb-sur);border-radius:12px;padding:10px}
#almora-bot .alb-p__img{width:88px;height:88px;border-radius:10px;object-fit:cover;background:#fff;flex:none;display:block}
#almora-bot .alb-p__main{flex:1;min-width:0;display:flex;flex-direction:column;gap:4px}
#almora-bot .alb-p__name{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:14px;font-weight:600;line-height:1.4;color:var(--alb-ink)!important;text-decoration:none!important}
#almora-bot .alb-p__price{font-size:15px;font-weight:600;line-height:1.3}
#almora-bot .alb-p__price s{margin-inline-start:6px;font-size:13px;font-weight:400;color:var(--alb-mute)}
#almora-bot .alb-p__price.is-sale{color:var(--alb-sale)}
#almora-bot .alb-p__act{display:flex;flex-wrap:wrap;gap:8px;margin-top:auto}
#almora-bot .alb-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:36px;margin:0;padding:0 12px;border:1px solid var(--alb-ink)!important;border-radius:12px!important;background:#fff!important;color:var(--alb-ink)!important;font-size:13.5px;font-weight:600;line-height:1;text-decoration:none!important;cursor:pointer;white-space:nowrap}
#almora-bot .alb-btn:hover,#almora-bot .alb-btn:focus-visible{background:var(--alb-sur)!important;color:var(--alb-ink)!important}
#almora-bot .alb-btn--pri{background:var(--alb-ink)!important;color:#fff!important}
#almora-bot .alb-btn--pri:hover,#almora-bot .alb-btn--pri:focus-visible{background:#000!important;color:#fff!important}
#almora-bot .alb-btn[disabled]{opacity:.45;cursor:default}
#almora-bot .alb-o{flex:1 1 100%;display:flex;flex-direction:column;gap:10px;padding-top:10px;border-top:1px solid var(--alb-line)}
#almora-bot .alb-o__g{display:flex;flex-direction:column;gap:6px}
#almora-bot .alb-o__l{font-size:13px;color:var(--alb-mute)}
#almora-bot .alb-o__r{display:flex;flex-wrap:wrap;gap:6px}
#almora-bot .alb-opt{min-height:34px;margin:0;padding:5px 12px;border:1px solid var(--alb-ink)!important;border-radius:12px!important;background:#fff!important;color:var(--alb-ink)!important;font-size:13.5px;font-weight:600;line-height:1.2;cursor:pointer}
#almora-bot .alb-opt:hover{background:var(--alb-sur)!important}
#almora-bot .alb-opt[aria-pressed="true"]{background:var(--alb-ink)!important;color:#fff!important}
#almora-bot .alb-opt[disabled]{opacity:.38;text-decoration:line-through;cursor:default}
#almora-bot .alb-err{flex:1 1 100%;margin:0;font-size:13px;color:#B3261E}

#almora-bot .alb-typing{align-self:flex-start;display:flex;gap:4px;padding:15px 16px;border-radius:12px;background:var(--alb-sur)}
#almora-bot .alb-typing i{width:6px;height:6px;border-radius:50%;background:#8a8a8a;animation:alb-b 1s infinite ease-in-out}
#almora-bot .alb-typing i:nth-child(2){animation-delay:.15s}
#almora-bot .alb-typing i:nth-child(3){animation-delay:.3s}
@keyframes alb-b{0%,60%,100%{opacity:.35;transform:translateY(0)}30%{opacity:1;transform:translateY(-3px)}}

#almora-bot .alb-foot{flex:none;padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));border-top:1px solid var(--alb-line);display:flex;flex-direction:column;gap:8px}
#almora-bot .alb-form{display:flex;gap:8px;margin:0}
#almora-bot .alb-in{flex:1;min-width:0;height:44px;margin:0;padding:0 14px;border:1px solid var(--alb-line);border-radius:12px;background:#fff;color:var(--alb-ink);font-size:16px;box-shadow:none;outline:0}
#almora-bot .alb-in:focus{border-color:var(--alb-ink)}
#almora-bot .alb-in::placeholder{color:#767676}
#almora-bot .alb-send{width:44px;height:44px;margin:0;padding:0;border:0!important;border-radius:12px!important;background:var(--alb-ink)!important;color:#fff!important;display:flex;align-items:center;justify-content:center;cursor:pointer;flex:none}
#almora-bot .alb-send svg{transform:scaleX(-1)}
#almora-bot .alb-send:focus-visible{outline:2px solid var(--alb-ink);outline-offset:2px}
#almora-bot .alb-link{align-self:center;font-size:13px;font-weight:600;color:var(--alb-ink)!important;text-decoration:underline!important;text-underline-offset:3px;background:none!important;border:0!important;padding:2px 4px;cursor:pointer}

@media (max-width:600px){
	#almora-bot .alb-panel{right:0;left:0;top:0;bottom:0;width:auto;max-width:none;height:100vh;height:100dvh;border-radius:0;box-shadow:none}
	#almora-bot.is-open .alb-fab{display:none}
	#almora-bot .alb-m{max-width:92%}
}
@media (prefers-reduced-motion:reduce){
	#almora-bot .alb-fab{transition:none}
	#almora-bot .alb-typing i{animation:none;opacity:.7}
}
</style>
<script id="almora-bot-js">
window.ALMORA_BOT=<?php echo wp_json_encode( $cfg ); ?>;
/* almora-bot-js */
(function () {
	'use strict';
	if (window.__almoraBot) { return; }
	window.__almoraBot = 1;
	var C = window.ALMORA_BOT || {};
	var root = document.getElementById('almora-bot');
	if (!root) { return; }

	/* Shown to visitors only when the option almora_bot_live is on; before that only with ?almora-bot=1 (remembered in this tab). */
	var preview = false;
	try {
		if (/[?&]almora-bot=1/.test(location.search)) { sessionStorage.setItem('almoraBotPreview', '1'); }
		preview = sessionStorage.getItem('almoraBotPreview') === '1';
	} catch (e) {}
	if (!C.live && !preview) { return; }
	root.hidden = false;

	var KEY = 'almoraBot2';
	var T = {
		name: 'העוזר של ALMORA',
		sub: 'בחירת תכשיט, משלוחים ושאלות',
		add: 'הוספה לסל',
		view: 'לצפייה',
		pickOpts: 'בחרו אפשרויות',
		soldOut: 'השילוב הזה לא זמין',
		wa: 'שיחה עם נציג בוואטסאפ'
	};
	var fab = root.querySelector('.alb-fab');
	var S = load();
	var panel, body, input, typingEl, busy = false, built = false, isOpen = false;
	var varsCache = {};

	function load() {
		try {
			var j = JSON.parse(sessionStorage.getItem(KEY) || 'null');
			if (j && j.log) { return j; }
		} catch (e) {}
		return { log: [], ans: {}, off: 0, seen: [], last: '' };
	}
	function save() {
		try {
			if (S.log.length > 60) { S.log = S.log.slice(-60); }
			sessionStorage.setItem(KEY, JSON.stringify(S));
		} catch (e) {}
	}
	function el(tag, cls, txt) {
		var e = document.createElement(tag);
		if (cls) { e.className = cls; }
		if (txt != null) { e.textContent = txt; }
		return e;
	}
	function icon(name, size) {
		var w = el('span');
		w.style.display = 'flex';
		w.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + ((C.icons || {})[name] || '') + '</svg>';
		return w;
	}
	function ils(n) {
		n = Number(n) || 0;
		var p = (Math.round(n * 100) / 100).toString().split('.');
		p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		return '₪' + p.join('.');
	}

	/* ---------------------------------------------------------------- tracking */
	function track(name, p) {
		p = p || {};
		try { if (window.gtag) { window.gtag('event', 'almora_bot_' + name, p); } } catch (e) {}
		try { (window.dataLayer = window.dataLayer || []).push(Object.assign({ event: 'almora_bot_' + name }, p)); } catch (e) {}
		try {
			if (window.fbq) {
				window.fbq('trackCustom', 'AlmoraBot' + name.replace(/(^|_)(\w)/g, function (m, a, b) { return b.toUpperCase(); }), p);
			}
		} catch (e) {}
	}

	/* ---------------------------------------------------------------- whatsapp */
	var LBL = {
		who: { self: 'לעצמי', gift: 'מתנה' },
		type: { ring: 'טבעת', earrings: 'עגילים', necklace: 'שרשרת', bracelet: 'צמיד', men: 'תכשיט לגברים', any: 'כל סוג' },
		bud: { '300': 'עד 300 ₪', '600': '300–600 ₪', '1000': '600–1,000 ₪', '1000+': 'מעל 1,000 ₪', any: 'כל תקציב' }
	};
	function waUrl() {
		var a = S.ans || {}, L = ['שלום, פונה מהאתר דרך העוזר של ALMORA.'], k = [];
		if (a.who) { k.push(LBL.who[a.who]); }
		if (a.type) { k.push(LBL.type[a.type]); }
		if (a.bud) { k.push(LBL.bud[a.bud]); }
		if (k.length) { L.push('ביקשתי המלצה על: ' + k.join(', ') + '.'); }
		if (S.seen && S.seen.length) {
			L.push('דגמים שראיתי:');
			S.seen.slice(-3).forEach(function (s) { L.push('• ' + s.n + ' ' + s.u); });
		}
		if (S.last) { L.push('מה שרציתי לשאול: ' + S.last); }
		return 'https://wa.me/' + C.wa + '?text=' + encodeURIComponent(L.join('\n'));
	}
	function seen(item) {
		S.seen = (S.seen || []).filter(function (s) { return s.id !== item.id; });
		S.seen.push({ id: item.id, n: item.n, u: item.u });
		save();
	}

	/* ---------------------------------------------------------------- panel */
	function build() {
		if (built) { return; }
		built = true;
		panel = el('section', 'alb-panel');
		panel.id = 'alb-panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-label', T.name);
		panel.tabIndex = -1;
		panel.hidden = true;

		var head = el('div', 'alb-head');
		var ht = el('div', 'alb-head__t');
		ht.appendChild(el('p', 'alb-head__n', T.name));
		ht.appendChild(el('p', 'alb-head__s', T.sub));
		var re = el('button', 'alb-ib');
		re.type = 'button';
		re.setAttribute('aria-label', 'התחלה מחדש');
		re.title = 'התחלה מחדש';
		re.appendChild(icon('restart', 22));
		re.addEventListener('click', function () { track('restart'); start(); });
		var cl = el('button', 'alb-ib');
		cl.type = 'button';
		cl.setAttribute('aria-label', 'סגירה');
		cl.title = 'סגירה';
		cl.appendChild(icon('times', 24));
		cl.addEventListener('click', close);
		head.appendChild(ht);
		head.appendChild(re);
		head.appendChild(cl);

		body = el('div', 'alb-body');
		body.setAttribute('role', 'log');
		body.setAttribute('aria-live', 'polite');

		var foot = el('div', 'alb-foot');
		var form = el('form', 'alb-form');
		input = el('input', 'alb-in');
		input.type = 'text';
		input.maxLength = 200;
		input.placeholder = 'אפשר גם לכתוב כאן…';
		input.setAttribute('aria-label', 'כתבו הודעה');
		input.autocomplete = 'off';
		var send = el('button', 'alb-send');
		send.type = 'submit';
		send.setAttribute('aria-label', 'שליחה');
		send.appendChild(icon('send', 22));
		form.appendChild(input);
		form.appendChild(send);
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			var v = input.value.trim();
			if (!v || busy) { return; }
			input.value = '';
			freeText(v);
		});
		var wa = el('a', 'alb-link', T.wa);
		wa.href = waUrl();
		wa.target = '_blank';
		wa.rel = 'noopener';
		['click', 'touchstart', 'focus', 'mouseenter'].forEach(function (n) {
			wa.addEventListener(n, function () { wa.href = waUrl(); });
		});
		wa.addEventListener('click', function () { track('whatsapp', { from: 'footer' }); });
		foot.appendChild(form);
		foot.appendChild(wa);

		panel.appendChild(head);
		panel.appendChild(body);
		panel.appendChild(foot);
		root.appendChild(panel);
	}

	function open() {
		build();
		isOpen = true;
		root.classList.add('is-open');
		panel.hidden = false;
		fab.setAttribute('aria-expanded', 'true');
		fab.setAttribute('aria-label', 'סגירת העוזר');
		if (window.matchMedia && window.matchMedia('(max-width:600px)').matches) {
			document.documentElement.classList.add('alb-lock');
		}
		track('open', { returning: S.log.length ? 1 : 0 });
		if (!S.log.length) {
			start();
		} else {
			body.textContent = '';
			S.log.forEach(function (e) { render(e); });
			body.scrollTop = body.scrollHeight;
		}
		panel.focus({ preventScroll: true });
	}
	function close() {
		if (!isOpen) { return; }
		isOpen = false;
		root.classList.remove('is-open');
		panel.hidden = true;
		fab.setAttribute('aria-expanded', 'false');
		fab.setAttribute('aria-label', T.name);
		document.documentElement.classList.remove('alb-lock');
		fab.focus({ preventScroll: true });
	}
	fab.addEventListener('click', function () { isOpen ? close() : open(); });
	fab.querySelector('.alb-fab__a').appendChild(icon('chat', 26));
	fab.querySelector('.alb-fab__b').appendChild(icon('times', 26));
	document.addEventListener('keydown', function (ev) {
		if (ev.key === 'Escape' && isOpen) { close(); }
	});

	/* ---------------------------------------------------------------- log */
	function clearChips() {
		S.log = S.log.filter(function (e) { return e.t !== 'chips'; });
		if (body) {
			Array.prototype.slice.call(body.querySelectorAll('.alb-c')).forEach(function (n) { n.remove(); });
		}
	}
	function push(e) {
		if (e.t === 'chips' || e.r === 'u') { clearChips(); }
		S.log.push(e);
		save();
		if (built) { render(e); }
	}
	function render(e) {
		if (!body) { return; }
		var n;
		if (e.t === 'text') {
			n = el('p', 'alb-m ' + (e.r === 'u' ? 'alb-m--u' : 'alb-m--b'), e.x);
		} else if (e.t === 'chips') {
			n = el('div', 'alb-c');
			e.o.forEach(function (o) { n.appendChild(chip(e, o)); });
		} else if (e.t === 'prods') {
			n = el('div', 'alb-ps');
			e.items.forEach(function (it) { n.appendChild(card(it)); });
		}
		if (n) { body.appendChild(n); }
	}
	function chip(e, o) {
		var c;
		if (o.h === 'wa' || o.h) {
			c = el('a', 'alb-chip' + (o.pri ? ' alb-chip--pri' : ''));
			c.href = o.h === 'wa' ? waUrl() : o.h;
			if (o.h === 'wa') {
				c.target = '_blank';
				c.rel = 'noopener';
				['click', 'touchstart', 'focus', 'mouseenter'].forEach(function (n) {
					c.addEventListener(n, function () { c.href = waUrl(); });
				});
				c.addEventListener('click', function () { track('whatsapp', { from: 'chip' }); });
			} else {
				c.addEventListener('click', function () { track('link', { to: o.k || '' }); });
			}
		} else {
			c = el('button', 'alb-chip' + (o.pri ? ' alb-chip--pri' : ''));
			c.type = 'button';
			c.addEventListener('click', function () {
				if (busy) { return; }
				push({ r: 'u', t: 'text', x: o.l });
				scrollEnd();
				act(o.k);
			});
		}
		if (o.i) { c.appendChild(icon(o.i, 18)); }
		c.appendChild(document.createTextNode(o.l));
		return c;
	}
	function showTyping() {
		if (!built) { return; }
		hideTyping();
		typingEl = el('div', 'alb-typing');
		typingEl.setAttribute('aria-label', 'מקלידים…');
		typingEl.innerHTML = '<i></i><i></i><i></i>';
		body.appendChild(typingEl);
		body.scrollTop = body.scrollHeight;
	}
	function hideTyping() {
		if (typingEl) { typingEl.remove(); typingEl = null; }
	}
	function scrollEnd() { if (body) { body.scrollTop = body.scrollHeight; } }
	/* a tall answer is read from its first line, a short one from the bottom */
	function say(list, delay) {
		busy = true;
		showTyping();
		setTimeout(function () {
			hideTyping();
			var before = body ? body.lastElementChild : null;
			list.forEach(push);
			busy = false;
			if (!body) { return; }
			var first = before ? before.nextElementSibling : body.firstElementChild;
			if (first && body.scrollHeight - first.offsetTop > body.clientHeight) {
				body.scrollTop = Math.max(0, first.offsetTop - 12);
			} else {
				scrollEnd();
			}
		}, delay == null ? 420 : delay);
	}
	function tx(x) { return { r: 'b', t: 'text', x: x }; }
	function chips(o) { return { r: 'b', t: 'chips', o: o }; }

	/* ---------------------------------------------------------------- conversation */
	function mainChips() {
		return chips([
			{ k: 'm:pick', l: 'עוזרים לי לבחור תכשיט', i: 'sparkle', pri: 1 },
			{ k: 'm:faq', l: 'שאלות נפוצות' },
			{ h: 'wa', l: 'שיחה עם נציג' }
		]);
	}
	function start() {
		S.log = [];
		S.ans = {};
		S.off = 0;
		S.last = '';
		save();
		if (body) { body.textContent = ''; }
		say([
			tx('שלום, כאן העוזר של ALMORA.'),
			tx('אפשר לבחור יחד תכשיט שמתאים בדיוק, או לקבל תשובות על משלוח, תשלום והחזרות.'),
			mainChips()
		]);
	}
	function askWho() {
		say([tx('בשביל מי התכשיט?'), chips([
			{ k: 'who:self', l: 'לעצמי' },
			{ k: 'who:gift', l: 'מתנה', i: 'gift' }
		])]);
	}
	function askType() {
		say([tx('איזה סוג תכשיט מעניין?'), chips([
			{ k: 'type:ring', l: 'טבעת' },
			{ k: 'type:earrings', l: 'עגילים' },
			{ k: 'type:necklace', l: 'שרשרת' },
			{ k: 'type:bracelet', l: 'צמיד' },
			{ k: 'type:men', l: 'תכשיט לגברים' },
			{ k: 'type:any', l: 'הציעו לי' }
		])]);
	}
	function askBudget() {
		say([tx('ומה התקציב בערך?'), chips([
			{ k: 'bud:300', l: 'עד 300 ₪' },
			{ k: 'bud:600', l: '300–600 ₪' },
			{ k: 'bud:1000', l: '600–1,000 ₪' },
			{ k: 'bud:1000+', l: 'מעל 1,000 ₪' },
			{ k: 'bud:any', l: 'לא משנה' }
		])]);
	}
	function faqMenu() {
		say([tx('על מה רוצים לדעת?'), chips([
			{ k: 'faq:ship', l: 'משלוחים' },
			{ k: 'faq:ret', l: 'החלפה והחזרה' },
			{ k: 'faq:pay', l: 'תשלום' },
			{ k: 'faq:mat', l: 'חומרים ואבן מוסונייט' },
			{ k: 'faq:care', l: 'שמירה וניקוי' },
			{ k: 'faq:size', l: 'מידות' },
			{ k: 'faq:contact', l: 'שעות ויצירת קשר' }
		])]);
	}
	var FAQ = {
		ship: 'משלוח עד הבית עולה 35 ₪ ומגיע תוך 3–5 ימי עסקים. ליישובים מרוחקים 5–10 ימי עסקים, באותו מחיר.\nבקנייה מ-1,000 ₪ המשלוח חינם.',
		ret: 'אפשר להחליף או להחזיר עד 14 יום מקבלת המוצר, כשהוא באריזה המקורית ולא היה בשימוש.\nלפני שליחה יוצרים איתנו קשר לאישור.',
		pay: 'משלמים בכרטיס אשראי, בביט, ב-Google Pay או ב-Apple Pay (במכשירי אפל). התשלום מאובטח ומתבצע באתר.',
		mat: 'התכשיטים עשויים מכסף 925, חלקם בציפוי זהב, ומשובצים באבן מוסונייט.\nמוסונייט היא אבן שנוצרת במעבדה, קשיחה במיוחד ובעלת ברק ופיזור אור חזקים, בלי מחיר של יהלום. בעמוד של כל מוצר מצוינת רמת הניקיון של האבן, למשל VVS1.',
		care: 'כדאי להסיר את התכשיט לפני מקלחת, שחייה וספורט, ולנגב בעדינות במטלית ניקוי. בכל הזמנה מצורפת מטלית כזו במתנה.',
		size: 'המידה נבחרת בעמוד המוצר לפני ההוספה לסל. לא בטוחים איזו מידה מתאימה? נציג יעזור בוואטסאפ.',
		contact: 'אפשר לכתוב בוואטסאפ או להתקשר: ' + (C.phone || '052-3550851') + '.\nמענה בין השעות 09:00 ל-20:00.',
		color: 'כל התמונות באתר מקוריות ומשקפות את מה שמתקבל. ייתכנו הבדלים קלים בגוון בגלל הגדרות המסך.'
	};
	function faq(key) {
		track('faq', { topic: key });
		say([tx(FAQ[key]), chips([
			{ k: 'm:faq', l: 'שאלה נוספת' },
			{ k: 'm:pick', l: 'עוזרים לי לבחור תכשיט', i: 'sparkle' },
			{ h: 'wa', l: 'שיחה עם נציג' }
		])]);
	}

	function act(k) {
		var p = k.split(':'), a = S.ans;
		track('step', { step: k });
		switch (p[0]) {
			case 'm':
				if (p[1] === 'pick') { a.who ? askType() : askWho(); }
				else if (p[1] === 'faq') { faqMenu(); }
				break;
			case 'who': a.who = p[1]; save(); askType(); break;
			case 'type': a.type = p[1]; save(); askBudget(); break;
			case 'bud': a.bud = p[1]; a.s = ''; S.off = 0; save(); recommend(); break;
			case 'more': S.off += 3; save(); recommend(); break;
			case 'chbud': askBudget(); break;
			case 'chtype': askType(); break;
			case 'faq': faq(p[1]); break;
		}
	}

	var RANGE = { '300': [0, 300], '600': [300, 600], '1000': [600, 1000], '1000+': [1000, 0], any: [0, 0] };
	function recommend(extra) {
		var a = S.ans, q = [];
		if (a.s) {
			q.push('s=' + encodeURIComponent(a.s));
			if (a.smax) { q.push('max=' + a.smax); }
		} else {
			var r = RANGE[a.bud] || [0, 0];
			if (a.type && a.type !== 'any') { q.push('cat=' + a.type); }
			q.push('min=' + r[0], 'max=' + r[1]);
		}
		q.push('off=' + S.off);
		busy = true;
		showTyping();
		fetch(C.rest + 'products?' + q.join('&'), { credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) { throw 0; } return r.json(); })
			.then(function (d) {
				hideTyping();
				busy = false;
				var items = d.items || [];
				var out = [];
				if (!items.length) {
					out.push(tx(S.off ? 'אלה היו כל הדגמים שמתאימים כרגע.' : (a.s ? 'לא מצאתי דגם לפי החיפוש הזה.' : 'לא מצאתי דגם שמתאים בדיוק לבחירה הזו.')));
					out.push(chips([
						{ k: 'chbud', l: 'לשנות תקציב' },
						{ k: 'chtype', l: 'סוג תכשיט אחר' },
						{ h: 'wa', l: 'שיחה עם נציג' }
					]));
				} else {
					if (!S.off) {
						out.push(tx(a.s ? 'אלה התוצאות שמצאתי:' : (a.who === 'gift' ? 'הנה כמה רעיונות למתנה:' : 'הנה כמה דגמים שמתאימים:')));
					}
					out.push({ r: 'b', t: 'prods', items: items });
					if (a.who === 'gift' && !S.off) { out.push(tx('בכל הזמנה מצורפת מתנה: מטלית לניקוי תכשיטים.')); }
					var c = [];
					if (d.more) { c.push({ k: 'more', l: 'עוד המלצות', pri: 1 }); }
					c.push({ k: 'chbud', l: 'לשנות תקציב' }, { k: 'chtype', l: 'סוג תכשיט אחר' }, { h: 'wa', l: 'שיחה עם נציג' });
					out.push(chips(c));
				}
				track('recommend', { type: a.type || '', budget: a.bud || '', who: a.who || '', results: items.length, search: a.s || '' });
				pushAll(out);
			})
			.catch(function () {
				hideTyping();
				busy = false;
				pushAll([tx('משהו השתבש בטעינת הדגמים. אפשר לנסות שוב, או לשוחח עם נציג.'), chips([
					{ k: 'm:pick', l: 'לנסות שוב' },
					{ h: 'wa', l: 'שיחה עם נציג' }
				])]);
			});
	}
	function pushAll(list) {
		var before = body ? body.lastElementChild : null;
		list.forEach(push);
		if (!body) { return; }
		var first = before ? before.nextElementSibling : body.firstElementChild;
		if (first && body.scrollHeight - first.offsetTop > body.clientHeight) {
			body.scrollTop = Math.max(0, first.offsetTop - 12);
		} else {
			scrollEnd();
		}
	}

	/* ---------------------------------------------------------------- free text */
	var KW = [
		['human', /נציג|בן אדם|אדם אמיתי|וואטסאפ|וואצאפ|ווטסאפ|whatsapp/i],
		['contact', /טלפון|שעות|פתוח|כתובת|ליצור קשר|יצירת קשר|להתקשר/],
		['ret', /החזר|להחליף|החלפ|ביטול|לבטל/],
		['ship', /משלוח|שליח|הגעה|מגיע|אספקה|דמי משלוח/],
		['pay', /תשלום|לשלם|אשראי|ביט\b|apple|google|פיי|תשלומים|חשבונית/i],
		['moi', /מוסונייט|מויסנייט|יהלום|זרקון/],
		['mat', /925|ציפוי|זהב|חומר|אלרגי|מתכת|ניקל/],
		['care', /ניקוי|לנקות|טיפול|להשחיר|משחיר|מקלחת|שמירה/],
		['size', /מידה|מידות|גודל/],
		['color', /גוון|תמונות|תמונה/]
	];
	function freeText(v) {
		S.last = v;
		save();
		push({ r: 'u', t: 'text', x: v });
		scrollEnd();
		track('text', { len: v.length });
		var key = '';
		for (var i = 0; i < KW.length; i++) { if (KW[i][1].test(v)) { key = KW[i][0]; break; } }
		if (key === 'human') {
			say([tx('בשמחה. כאן אפשר להמשיך בוואטסאפ, ההודעה כבר מוכנה עם סיכום השיחה.'), chips([{ h: 'wa', l: 'המשך בוואטסאפ', pri: 1 }, mainChips().o[0]])]);
			return;
		}
		if (key) {
			if (key === 'moi') { key = 'mat'; }
			faq(key);
			return;
		}
		if (/^(שלום|היי|הי|הלו|בוקר טוב|ערב טוב|צהריים טובים)\b/.test(v) && v.length < 25) {
			say([tx('שלום, שמחים לעזור.'), mainChips()]);
			return;
		}
		var m = v.match(/(?:עד|מתחת ל|לא יותר מ)\s*₪?\s*(\d{2,5})/);
		S.ans.s = v.replace(/(?:עד|מתחת ל|לא יותר מ)\s*₪?\s*\d{2,5}\s*(?:₪|שקל|שקלים|ש"ח)?/, '').replace(/\s+/g, ' ').trim() || v;
		S.ans.smax = m ? m[1] : '';
		S.off = 0;
		save();
		recommend();
	}

	/* ---------------------------------------------------------------- product card */
	function setPrice(node, price, reg, from) {
		node.textContent = '';
		node.classList.toggle('is-sale', !!reg);
		node.appendChild(document.createTextNode((from ? 'החל מ-' : '') + ils(price)));
		if (reg) { node.appendChild(el('s', '', ils(reg))); }
	}
	function card(item) {
		var c = el('div', 'alb-p');
		var im = el('img', 'alb-p__img');
		im.src = item.i;
		im.alt = '';
		im.width = 88;
		im.height = 88;
		im.loading = 'lazy';
		var main = el('div', 'alb-p__main');
		var nm = el('a', 'alb-p__name', item.n);
		nm.href = item.u;
		var pr = el('div', 'alb-p__price');
		setPrice(pr, item.p, item.rp, item.f);
		var act = el('div', 'alb-p__act');
		var vw = el('a', 'alb-btn', T.view);
		vw.href = item.u;
		[nm, vw].forEach(function (a) {
			a.addEventListener('click', function () {
				seen(item);
				track('product_click', { item_id: item.id, item_name: item.n });
			});
		});
		var ad = el('button', 'alb-btn alb-btn--pri');
		ad.type = 'button';
		ad.appendChild(icon('bag', 18));
		var adl = document.createTextNode(T.add);
		ad.appendChild(adl);
		act.appendChild(ad);
		act.appendChild(vw);
		main.appendChild(nm);
		main.appendChild(pr);
		main.appendChild(act);
		c.appendChild(im);
		c.appendChild(main);
		var err = el('p', 'alb-err');
		err.hidden = true;
		err.setAttribute('role', 'alert');
		c.appendChild(err);

		var ctx = { item: item, card: c, pr: pr, ad: ad, adl: adl, err: err, vid: 0, sel: {}, V: null, box: null };
		ad.addEventListener('click', function () {
			err.hidden = true;
			if (item.v && !ctx.vid) {
				toggleOptions(ctx);
				return;
			}
			addToCart(ctx);
		});
		return c;
	}

	/* variable products: the options are chosen inside the card */
	function getVars(id) {
		if (!varsCache[id]) {
			varsCache[id] = fetch(C.rest + 'variations?id=' + id, { credentials: 'same-origin' }).then(function (r) {
				if (!r.ok) { throw 0; }
				return r.json();
			});
		}
		return varsCache[id];
	}
	function fits(v, sel) {
		for (var k in sel) {
			if (sel[k] && v.a[k] !== undefined && v.a[k] !== '' && v.a[k] !== sel[k]) { return false; }
		}
		return true;
	}
	function toggleOptions(ctx) {
		if (ctx.box) {
			ctx.box.hidden = !ctx.box.hidden;
			return;
		}
		ctx.box = el('div', 'alb-o');
		ctx.box.textContent = 'טוענים אפשרויות…';
		ctx.card.appendChild(ctx.box);
		ctx.adl.nodeValue = T.pickOpts;
		ctx.ad.disabled = true;
		getVars(ctx.item.id).then(function (V) {
			ctx.V = V;
			ctx.box.textContent = '';
			ctx.groups = {};
			(V.attrs || []).forEach(function (at) {
				var g = el('div', 'alb-o__g');
				g.appendChild(el('div', 'alb-o__l', at.l));
				var row = el('div', 'alb-o__r');
				row.setAttribute('role', 'group');
				row.setAttribute('aria-label', at.l);
				at.o.forEach(function (o) {
					var b = el('button', 'alb-opt', o.l);
					b.type = 'button';
					b.setAttribute('aria-pressed', 'false');
					b.addEventListener('click', function () {
						ctx.sel[at.k] = ctx.sel[at.k] === o.v ? '' : o.v;
						refresh(ctx);
					});
					b._v = o.v;
					row.appendChild(b);
				});
				g.appendChild(row);
				ctx.box.appendChild(g);
				ctx.groups[at.k] = row;
			});
			refresh(ctx);
		}).catch(function () {
			ctx.box.textContent = '';
			showErr(ctx, 'לא הצלחנו לטעון את האפשרויות. אפשר לפתוח את עמוד המוצר.');
		});
	}
	function refresh(ctx) {
		var V = ctx.V, list = V.list || [], changed;
		// options that lead nowhere are disabled; a lone remaining choice is picked for the visitor
		do {
			changed = false;
			V.attrs.forEach(function (at) {
				Array.prototype.forEach.call(ctx.groups[at.k].children, function (b) {
					var t = {};
					for (var k in ctx.sel) { t[k] = ctx.sel[k]; }
					t[at.k] = b._v;
					var ok = list.some(function (v) { return v.in && fits(v, t); });
					b.disabled = !ok;
					if (!ok && ctx.sel[at.k] === b._v) { ctx.sel[at.k] = ''; changed = true; }
				});
			});
			V.attrs.forEach(function (at) {
				if (ctx.sel[at.k]) { return; }
				var free = Array.prototype.filter.call(ctx.groups[at.k].children, function (b) { return !b.disabled; });
				if (free.length === 1) { ctx.sel[at.k] = free[0]._v; changed = true; }
			});
		} while (changed);
		V.attrs.forEach(function (at) {
			Array.prototype.forEach.call(ctx.groups[at.k].children, function (b) {
				b.setAttribute('aria-pressed', ctx.sel[at.k] === b._v ? 'true' : 'false');
			});
		});
		var done = V.attrs.every(function (at) { return ctx.sel[at.k]; });
		var cur = null;
		if (done) {
			cur = list.filter(function (v) { return v.in && fits(v, ctx.sel); })[0] || null;
		}
		ctx.vid = cur ? cur.id : 0;
		ctx.ad.disabled = !cur;
		ctx.adl.nodeValue = cur ? T.add : (done ? T.soldOut : T.pickOpts);
		if (cur) { setPrice(ctx.pr, cur.p, cur.rp, false); }
		else { setPrice(ctx.pr, ctx.item.p, ctx.item.rp, ctx.item.f); }
	}
	function showErr(ctx, m) {
		ctx.err.textContent = m;
		ctx.err.hidden = false;
	}

	function addToCart(ctx) {
		var item = ctx.item;
		ctx.ad.disabled = true;
		var prev = ctx.adl.nodeValue;
		ctx.adl.nodeValue = 'מוסיפים…';
		var form = new URLSearchParams();
		form.append('product_id', item.id);
		form.append('variation_id', ctx.vid || 0);
		form.append('sel', JSON.stringify(ctx.sel || {}));
		fetch(C.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: form.toString() })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				ctx.ad.disabled = false;
				ctx.adl.nodeValue = prev;
				if (!d || !d.ok) {
					showErr(ctx, (d && d.m) || 'לא הצלחנו להוסיף לסל.');
					return;
				}
				try {
					if (window.jQuery) {
						var $b = window.jQuery('<a class="ajax_add_to_cart add_to_cart_button"></a>').attr({ 'data-product_id': item.id, 'data-quantity': 1 });
						window.jQuery(document.body).trigger('added_to_cart', [d.frag, d.hash, $b]);
					}
				} catch (e) {}
				track('add_to_cart', { item_id: item.id, item_name: item.n, value: ctx.vid ? 0 : item.p });
				var left = (C.free || 1000) - (d.sub || 0);
				pushAll([
					tx('נוסף לסל: ' + item.n + '.\n' + (left > 0 ? 'עוד ' + ils(Math.ceil(left)) + ' למשלוח חינם.' : 'המשלוח בהזמנה הזו חינם.')),
					chips([
						{ h: C.checkout, k: 'checkout', l: 'לתשלום', pri: 1 },
						{ h: C.cart, k: 'cart', l: 'לצפייה בעגלה' },
						{ k: 'chtype', l: 'להמשיך לחפש' }
					])
				]);
			})
			.catch(function () {
				ctx.ad.disabled = false;
				ctx.adl.nodeValue = prev;
				showErr(ctx, 'לא הצלחנו להוסיף לסל. אפשר לנסות שוב או לפתוח את עמוד המוצר.');
			});
	}
})();
</script>
	<?php
}, 99 );
