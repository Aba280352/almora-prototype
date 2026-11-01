<?php
/**
 * Plugin Name: ALMORA upsell and cross-sell
 * Description: On the product page "מומלץ יחד עם" (3 matching pieces, same stone and style) and "אפשר גם יותר" (2 pricier pieces of the same kind, up to +30% or at least +50 ₪), a 5% cart discount on the cheapest non-sale item when the cart holds 2 or more items, and a REST endpoint the chat assistant uses after an add to cart. Pairings come from rules (name, categories, price, stock); per product they can be overridden with the post meta _almora_xs_cross / _almora_xs_up (comma separated product ids) or switched off with _almora_xs_off = 1.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_XS_PCT     = 5;     // cart discount, percent of the cheapest non-sale unit
const ALMORA_XS_UP_PCT  = 0.30;  // upsell: at most this much above the viewed price ...
const ALMORA_XS_UP_MIN  = 50;    // ... or at least this many shekels above, whichever allows more
const ALMORA_XS_CROSS_N = 3;
const ALMORA_XS_UP_N    = 2;
const ALMORA_XS_CACHE   = 'almora_xs_universe_v1';

/* ------------------------------------------------------------------ product profile */

function almora_xs_note() {
	return 'בהזמנה של 2 פריטים ומעלה יש ' . ALMORA_XS_PCT . '% הנחה על הפריט הזול ביותר. ההנחה לא חלה על מוצרי מבצע.';
}

/* JS \b does not work with Hebrew letters: whole words are matched with lookarounds */
function almora_xs_profile( $p ) {
	$id   = $p->get_id();
	$name = html_entity_decode( $p->get_name(), ENT_QUOTES, 'UTF-8' );
	$cats = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'ids' ) );
	$cats = is_array( $cats ) ? array_map( 'intval', $cats ) : array();
	$has  = function ( $re ) use ( $name ) {
		return (bool) preg_match( $re, $name );
	};

	if ( $has( '/סטנד/u' ) ) {
		$type = 'stand';
	} elseif ( $has( '/(?<![א-ת])סט(?![א-ת])/u' ) || in_array( 70, $cats, true ) ) {
		$type = 'set';
	} elseif ( $has( '/תליון/u' ) ) {
		$type = 'pendant';
	} elseif ( $has( '/צמיד/u' ) ) {
		$type = 'bracelet';
	} elseif ( $has( '/עגיל/u' ) ) {
		$type = 'earrings';
	} elseif ( $has( "/שרשר|צ׳וקר|צ'וקר/u" ) ) {
		$type = 'necklace';
	} elseif ( $has( '/טבע(ת|ות)/u' ) ) {
		$type = 'ring';
	} elseif ( array_intersect( $cats, array( 66, 120, 127 ) ) ) {
		$type = 'ring';
	} elseif ( array_intersect( $cats, array( 67, 119 ) ) ) {
		$type = 'earrings';
	} elseif ( array_intersect( $cats, array( 68, 122 ) ) ) {
		$type = 'necklace';
	} elseif ( array_intersect( $cats, array( 69, 121, 125 ) ) ) {
		$type = 'bracelet';
	} elseif ( in_array( 71, $cats, true ) ) {
		$type = 'pendant';
	} else {
		$type = '';
	}

	$gender = ( array_intersect( $cats, array( 124, 125, 127 ) ) || $has( '/גבר/u' ) ) ? 'm' : 'w';

	if ( in_array( 117, $cats, true ) || $has( '/מוסונ|מוסנ|מואסנ/u' ) ) {
		$stone = 'moissanite';
	} elseif ( $has( '/זרקון|זירקון/u' ) ) {
		$stone = 'zircon';
	} elseif ( $has( '/אוניקס/u' ) ) {
		$stone = 'onyx';
	} elseif ( $has( '/פנינ/u' ) ) {
		$stone = 'pearl';
	} else {
		$stone = '';
	}

	$map    = array(
		'lotus'      => '/לוטוס/u',
		'ice'        => '/אייס/u',
		'tennis'     => '/טניס/u',
		'oldfashion' => '/אולדפשן/u',
		'israel'     => '/ישראל/u',
		'david'      => '/מג[ןד] דוד/u',
		'heart'      => '/(?<![א-ת])(לב|לבבות)(?![א-ת])/u',
		'drop'       => '/טיפ(ה|ות)/u',
		'hoop'       => '/חישוק/u',
		'solitaire'  => '/סוליטר/u',
		'wings'      => '/כנפיים/u',
		'links'      => '/חוליות|קובנית/u',
		'oval'       => '/אליפס|אובל/u',
		'gadi'       => '/גאדי/u',
	);
	$styles = array();
	foreach ( $map as $k => $re ) {
		if ( $has( $re ) ) {
			$styles[] = $k;
		}
	}

	$price = (float) $p->get_price();
	$test  = (int) get_option( 'almora_test_product_id', 0 );
	$ok    = 'publish' === get_post_status( $id )
		&& $price > 0
		&& $p->is_in_stock()
		&& 'hidden' !== $p->get_catalog_visibility()
		&& $id !== $test
		&& 'stand' !== $type
		&& '' !== $type
		&& ! get_post_meta( $id, '_almora_xs_off', true );

	return array(
		'id'     => $id,
		'type'   => $type,
		'gender' => $gender,
		'stone'  => $stone,
		'styles' => $styles,
		'price'  => $price,
		'ok'     => $ok,
	);
}

function almora_xs_universe() {
	$u = get_transient( ALMORA_XS_CACHE );
	if ( is_array( $u ) && $u ) {
		return $u;
	}
	$ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	$u   = array();
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( $p ) {
			$u[ $id ] = almora_xs_profile( $p );
		}
	}
	set_transient( ALMORA_XS_CACHE, $u, 12 * HOUR_IN_SECONDS );
	return $u;
}

function almora_xs_flush() {
	delete_transient( ALMORA_XS_CACHE );
}
add_action( 'save_post_product', 'almora_xs_flush' );
add_action( 'woocommerce_update_product', 'almora_xs_flush' );
add_action( 'woocommerce_product_set_stock_status', 'almora_xs_flush' );
add_action( 'woocommerce_variation_set_stock_status', 'almora_xs_flush' );
add_action( 'woocommerce_product_set_stock', 'almora_xs_flush' );

/* ------------------------------------------------------------------ rules */

function almora_xs_jitter( $a, $b ) {
	// small stable spread so that one popular piece is not suggested everywhere
	return ( crc32( $a . '-' . $b ) % 100 ) / 100 * 0.6;
}

function almora_xs_shared( $a, $b ) {
	return count( array_intersect( $a['styles'], $b['styles'] ) );
}

function almora_xs_stone_score( $a, $b, $same, $none, $diff ) {
	if ( '' === $a['stone'] && '' === $b['stone'] ) {
		return $none;
	}
	if ( '' === $a['stone'] || '' === $b['stone'] ) {
		return 0;
	}
	return $a['stone'] === $b['stone'] ? $same : $diff;
}

function almora_xs_manual( $id, $key ) {
	$raw = (string) get_post_meta( $id, $key, true );
	if ( '' === trim( $raw ) ) {
		return array();
	}
	$u   = almora_xs_universe();
	$ids = array();
	foreach ( array_map( 'absint', explode( ',', $raw ) ) as $x ) {
		if ( $x && ! empty( $u[ $x ]['ok'] ) && $x !== (int) $id ) {
			$ids[] = $x;
		}
	}
	return $ids;
}

/** Pieces that go with the viewed one: another kind of jewellery, same gender group, same stone and style. */
function almora_xs_cross( $id, $limit = ALMORA_XS_CROSS_N ) {
	$id = (int) $id;
	if ( get_post_meta( $id, '_almora_xs_off', true ) ) {
		return array();
	}
	$manual = almora_xs_manual( $id, '_almora_xs_cross' );
	if ( $manual ) {
		return array_slice( $manual, 0, $limit );
	}
	$u = almora_xs_universe();
	if ( empty( $u[ $id ] ) ) {
		$p = wc_get_product( $id );
		if ( ! $p ) {
			return array();
		}
		$u[ $id ] = almora_xs_profile( $p );
	}
	$s = $u[ $id ];
	if ( $s['price'] <= 0 || '' === $s['type'] || 'stand' === $s['type'] ) {
		return array();
	}
	$pair = array(
		'ring'     => array( 'earrings' => 2, 'necklace' => 1.5, 'bracelet' => 1, 'pendant' => 0.5, 'set' => 0.5 ),
		'earrings' => array( 'necklace' => 2, 'ring' => 2, 'bracelet' => 1, 'pendant' => 1, 'set' => 0.5 ),
		'necklace' => array( 'earrings' => 2, 'ring' => 1.5, 'bracelet' => 1, 'pendant' => 0.5, 'set' => 0.5 ),
		'pendant'  => array( 'necklace' => 3, 'earrings' => 1.5, 'ring' => 0.5, 'bracelet' => 0.5, 'set' => 0.5 ),
		'bracelet' => array( 'earrings' => 1.5, 'necklace' => 1.5, 'ring' => 1, 'pendant' => 0.5, 'set' => 0.5 ),
		'set'      => array(),
	);
	$cap  = max( $s['price'] * 2.2, $s['price'] + 250 );
	$list = array();
	foreach ( $u as $c ) {
		if ( ! $c['ok'] || $c['id'] === $id || $c['gender'] !== $s['gender'] || $c['type'] === $s['type'] || $c['price'] > $cap ) {
			continue;
		}
		$score  = isset( $pair[ $s['type'] ][ $c['type'] ] ) ? $pair[ $s['type'] ][ $c['type'] ] : 1;
		$score += almora_xs_stone_score( $s, $c, 4, 0.5, -3 );
		$score += 3 * min( 2, almora_xs_shared( $s, $c ) );
		$score -= 1.5 * abs( log( $c['price'] / $s['price'] ) );
		$score += almora_xs_jitter( $id, $c['id'] );
		$list[] = array( $score, $c );
	}
	usort( $list, function ( $a, $b ) {
		return $b[0] <=> $a[0];
	} );
	// one of each kind first, then fill with the next best
	$out   = array();
	$kinds = array();
	foreach ( $list as $row ) {
		if ( count( $out ) >= $limit ) {
			break;
		}
		if ( ! isset( $kinds[ $row[1]['type'] ] ) ) {
			$kinds[ $row[1]['type'] ] = 1;
			$out[ $row[1]['id'] ]     = 1;
		}
	}
	foreach ( $list as $row ) {
		if ( count( $out ) >= $limit ) {
			break;
		}
		$out[ $row[1]['id'] ] = 1;
	}
	return array_keys( $out );
}

/** A pricier piece of the same kind: up to +30% (or at least +50 ₪) above the viewed price. */
function almora_xs_upsell( $id, $limit = ALMORA_XS_UP_N ) {
	$id = (int) $id;
	if ( get_post_meta( $id, '_almora_xs_off', true ) ) {
		return array();
	}
	$manual = almora_xs_manual( $id, '_almora_xs_up' );
	if ( $manual ) {
		return array_slice( $manual, 0, $limit );
	}
	$u = almora_xs_universe();
	if ( empty( $u[ $id ] ) ) {
		$p = wc_get_product( $id );
		if ( ! $p ) {
			return array();
		}
		$u[ $id ] = almora_xs_profile( $p );
	}
	$s = $u[ $id ];
	if ( $s['price'] <= 0 || '' === $s['type'] || 'stand' === $s['type'] ) {
		return array();
	}
	$top  = max( $s['price'] * ( 1 + ALMORA_XS_UP_PCT ), $s['price'] + ALMORA_XS_UP_MIN );
	$low  = $s['price'] + max( 20, $s['price'] * 0.08 );
	$list = array();
	foreach ( $u as $c ) {
		if ( ! $c['ok'] || $c['id'] === $id || $c['gender'] !== $s['gender'] || $c['type'] !== $s['type'] ) {
			continue;
		}
		if ( $c['price'] < $low || $c['price'] > $top ) {
			continue;
		}
		$score  = almora_xs_stone_score( $s, $c, 3, 1, -2 );
		$score += 2 * min( 2, almora_xs_shared( $s, $c ) );
		$score += 1.5 * ( $c['price'] - $s['price'] ) / $s['price'];
		$score += almora_xs_jitter( $id, $c['id'] );
		$list[] = array( $score, $c['id'] );
	}
	usort( $list, function ( $a, $b ) {
		return $b[0] <=> $a[0];
	} );
	return array_slice( wp_list_pluck( $list, 1 ), 0, $limit );
}

/* ------------------------------------------------------------------ product page */

function almora_xs_block( $ids, $class, $title, $note = '' ) {
	if ( ! $ids ) {
		return;
	}
	$q = new WP_Query( array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'post__in'            => $ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $ids ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );
	if ( ! $q->have_posts() ) {
		return;
	}
	global $product;
	$keep = $product;
	wc_setup_loop( array( 'columns' => max( 2, count( $ids ) ), 'name' => 'almora_xs' ) );
	echo '<section class="' . esc_attr( $class ) . ' products alm-xs">';
	echo '<h2>' . esc_html( $title ) . '</h2>';
	if ( $note ) {
		echo '<p class="alm-xs__note">' . esc_html( $note ) . '</p>';
	}
	woocommerce_product_loop_start();
	while ( $q->have_posts() ) {
		$q->the_post();
		wc_get_template_part( 'content', 'product' );
	}
	woocommerce_product_loop_end();
	echo '</section>';
	wp_reset_postdata();
	wc_reset_loop();
	$product = $keep; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
}

add_action( 'woocommerce_after_single_product_summary', function () {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	$id = get_the_ID();
	static $css = false;
	if ( ! $css ) {
		$css = true;
		echo '<style id="almora-xs-css">.alm-xs{margin:48px 0}.alm-xs__note{margin:-4px 0 18px;font-size:14px;color:#6b6b6b}</style>';
	}
	almora_xs_block( almora_xs_cross( $id ), 'related', 'מומלץ יחד עם', almora_xs_note() );
	almora_xs_block( almora_xs_upsell( $id ), 'up-sells upsells', 'אפשר גם יותר' );
}, 12 );

/* ------------------------------------------------------------------ cart discount */

add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$count = 0;
	$units = array();
	foreach ( $cart->get_cart() as $item ) {
		$qty    = max( 1, (int) $item['quantity'] );
		$count += $qty;
		if ( $item['data']->is_on_sale() ) {
			continue;
		}
		$unit = (float) $item['line_subtotal'] / $qty;
		if ( $unit > 0 ) {
			$units[] = $unit;
		}
	}
	if ( $count < 2 || ! $units ) {
		return;
	}
	$off = round( min( $units ) * ALMORA_XS_PCT / 100, 2 );
	if ( $off > 0 ) {
		$cart->fees_api()->add_fee( array(
			'name'      => 'הנחה של ' . ALMORA_XS_PCT . '% על הפריט הזול ביותר',
			'amount'    => -$off,
			'taxable'   => true,
			'tax_class' => '',
		) );
	}
}, 20 );

/* ------------------------------------------------------------------ chat assistant */

add_action( 'rest_api_init', function () {
	register_rest_route( 'almora/v1', '/xsell', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $r ) {
			$id = absint( $r->get_param( 'id' ) );
			if ( ! $id ) {
				return array( 'items' => array() );
			}
			if ( function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
			}
			$in = array();
			if ( WC()->cart ) {
				foreach ( WC()->cart->get_cart() as $line ) {
					$in[] = (int) $line['product_id'];
				}
			}
			$ids   = array_values( array_diff( almora_xs_cross( $id, 6 ), $in ) );
			$items = array();
			foreach ( array_slice( $ids, 0, 2 ) as $x ) {
				$p = wc_get_product( $x );
				if ( $p && function_exists( 'almora_bot_item' ) ) {
					$items[] = almora_bot_item( $p );
				}
			}
			return array( 'items' => $items, 'note' => almora_xs_note() );
		},
	) );
} );
