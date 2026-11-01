<?php
/**
 * Plugin Name: ALMORA Budget Shopping
 * Description: Shortcode [almora_budget]: price range cards, results below on the same page, filters by gender, type and sort. No JS, plain links (?range=&g=&t=&sort=).
 *
 * Reuses almora_xs_universe() from almora-upsell.php for type, gender and the "can be bought" check
 * (published, priced, in stock, not hidden, not a stand, not the test product, not switched off).
 * Prices are matched on the price the customer sees (sale price when there is one).
 */

defined( 'ABSPATH' ) || exit;

const ALMORA_BUD_CACHE = 'almora_bud_v1';

function almora_bud_ranges() {
	// key => [ min exclusive, max inclusive (0 = open), label ]
	return array(
		'0-200'    => array( 0, 200, 'עד 200 ₪' ),
		'200-400'  => array( 200, 400, '200 עד 400 ₪' ),
		'400-700'  => array( 400, 700, '400 עד 700 ₪' ),
		'700-1000' => array( 700, 1000, '700 עד 1,000 ₪' ),
		'1000-up'  => array( 1000, 0, 'מעל 1,000 ₪' ),
	);
}

function almora_bud_types() {
	return array(
		'earrings' => 'עגילים',
		'necklace' => 'שרשראות',
		'bracelet' => 'צמידים',
		'ring'     => 'טבעות',
		'pendant'  => 'תליונים',
		'set'      => 'סטים',
	);
}

/* id => display price, time; only products that can be bought */
function almora_bud_items() {
	$c = get_transient( ALMORA_BUD_CACHE );
	if ( is_array( $c ) ) {
		return $c;
	}
	$out = array();
	if ( function_exists( 'almora_xs_universe' ) ) {
		foreach ( almora_xs_universe() as $id => $pr ) {
			if ( empty( $pr['ok'] ) ) {
				continue;
			}
			$p = wc_get_product( $id );
			if ( ! $p ) {
				continue;
			}
			$price = (float) wc_get_price_to_display( $p );
			if ( $price <= 0 ) {
				continue;
			}
			$out[ $id ] = array(
				'price'  => $price,
				'ts'     => (int) get_post_timestamp( $id ),
				'type'   => $pr['type'],
				'gender' => $pr['gender'],
			);
		}
	}
	set_transient( ALMORA_BUD_CACHE, $out, 12 * HOUR_IN_SECONDS );
	return $out;
}

function almora_bud_flush() {
	delete_transient( ALMORA_BUD_CACHE );
}
add_action( 'save_post_product', 'almora_bud_flush' );
add_action( 'woocommerce_update_product', 'almora_bud_flush' );
add_action( 'woocommerce_product_set_stock_status', 'almora_bud_flush' );
add_action( 'woocommerce_variation_set_stock_status', 'almora_bud_flush' );
add_action( 'woocommerce_product_set_stock', 'almora_bud_flush' );
add_action( 'woocommerce_scheduled_sales', 'almora_bud_flush', 20 );

function almora_bud_in_range( $price, $r ) {
	return $price > $r[0] && ( 0 === $r[1] || $price <= $r[1] );
}

function almora_bud_url( $base, $args ) {
	$args = array_filter( $args, function ( $v ) {
		return '' !== $v && null !== $v;
	} );
	return esc_url( add_query_arg( $args, $base ) . '#budget-results' );
}

function almora_bud_css() {
	return '<style id="almora-budget-css">'
		. '.alm-bud{margin:0 0 40px}'
		. '.alm-bud__cards{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin:0 0 40px;padding:0;list-style:none}'
		. '.alm-bud__card{display:flex;align-items:center;justify-content:center;min-height:120px;padding:16px;border:1px solid #d9d9d9;border-radius:12px;background:#fff;color:#202020;font-size:22px;line-height:1.2;text-align:center;white-space:nowrap;text-decoration:none;transition:background .2s,color .2s,border-color .2s}'
		. '.alm-bud__card:hover,.alm-bud__card:focus-visible{border-color:#202020}'
		. '.alm-bud__card[aria-current=true]{background:#202020;border-color:#202020;color:#fff}'
		. '.alm-bud__bar{display:flex;flex-wrap:wrap;gap:12px 32px;margin:0 0 24px}'
		. '.alm-bud__group{display:flex;flex-wrap:wrap;align-items:center;gap:8px}'
		. '.alm-bud__label{font-size:14px;color:#6b6b6b}'
		. '.alm-bud__chip{padding:8px 16px;border:1px solid #d9d9d9;border-radius:12px;color:#202020;font-size:14px;line-height:1.2;text-decoration:none}'
		. '.alm-bud__chip:hover,.alm-bud__chip:focus-visible{border-color:#202020}'
		. '.alm-bud__chip[aria-current=true]{background:#202020;border-color:#202020;color:#fff}'
		. '.alm-bud h2{margin:0 0 20px;font-size:32px;font-weight:400;line-height:1.2}'
		. '.alm-bud__empty,.alm-bud__hint{margin:0;font-size:16px;color:#6b6b6b}'
		. '@media(max-width:991px){.alm-bud__cards{grid-template-columns:repeat(3,1fr)}}'
		. '@media(max-width:767px){.alm-bud__cards{grid-template-columns:repeat(2,1fr);gap:12px}.alm-bud__card{min-height:96px;font-size:20px}.alm-bud__card:last-child{grid-column:1/-1}.alm-bud h2{font-size:24px}}'
		. '</style>';
}

function almora_bud_render() {
	if ( ! function_exists( 'almora_xs_universe' ) || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$ranges = almora_bud_ranges();
	$types  = almora_bud_types();
	$base   = get_permalink();
	if ( ! $base ) {
		$base = home_url( '/budget/' );
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$rk   = isset( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '';
	$rk   = isset( $ranges[ $rk ] ) ? $rk : '';
	$g    = isset( $_GET['g'] ) ? sanitize_text_field( wp_unslash( $_GET['g'] ) ) : '';
	$g    = in_array( $g, array( 'w', 'm' ), true ) ? $g : '';
	$t    = isset( $_GET['t'] ) ? sanitize_text_field( wp_unslash( $_GET['t'] ) ) : '';
	$t    = isset( $types[ $t ] ) ? $t : '';
	$sort = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'new';
	$sort = in_array( $sort, array( 'new', 'asc', 'desc' ), true ) ? $sort : 'new';
	// phpcs:enable

	$items = almora_bud_items();

	$html  = almora_bud_css();
	$html .= '<div class="alm-bud">';

	$html .= '<ul class="alm-bud__cards" aria-label="טווחי תקציב">';
	foreach ( $ranges as $key => $r ) {
		$cur   = $key === $rk ? ' aria-current="true"' : '';
		$html .= '<li><a class="alm-bud__card" href="' . almora_bud_url( $base, array( 'range' => $key ) ) . '"' . $cur . '>' . esc_html( $r[2] ) . '</a></li>';
	}
	$html .= '</ul>';

	$html .= '<div id="budget-results">';

	if ( '' === $rk ) {
		$html .= '<p class="alm-bud__hint">בחרו טווח מחיר כדי לראות את התכשיטים הזמינים במלאי.</p></div></div>';
		return $html;
	}

	$r     = $ranges[ $rk ];
	$inrng = array_filter( $items, function ( $it ) use ( $r ) {
		return almora_bud_in_range( $it['price'], $r );
	} );

	// filter chips are built from the range only, so no chip leads to an empty page
	$genders = array();
	foreach ( $inrng as $it ) {
		$genders[ $it['gender'] ] = true;
	}
	if ( $g && empty( $genders[ $g ] ) ) {
		$g = '';
	}
	$set = $g ? array_filter( $inrng, function ( $it ) use ( $g ) {
		return $it['gender'] === $g;
	} ) : $inrng;
	$have = array();
	foreach ( $set as $it ) {
		$have[ $it['type'] ] = true;
	}
	if ( $t && empty( $have[ $t ] ) ) {
		$t = '';
	}
	$res = $t ? array_filter( $set, function ( $it ) use ( $t ) {
		return $it['type'] === $t;
	} ) : $set;

	$keep = array( 'range' => $rk );

	$bar = '<div class="alm-bud__bar">';
	if ( count( $genders ) > 1 ) {
		$bar .= '<div class="alm-bud__group"><span class="alm-bud__label">למי:</span>';
		foreach ( array( '' => 'הכל', 'w' => 'נשים', 'm' => 'גברים' ) as $k => $label ) {
			if ( '' !== $k && empty( $genders[ $k ] ) ) {
				continue;
			}
			$cur  = $g === $k ? ' aria-current="true"' : '';
			$bar .= '<a class="alm-bud__chip" href="' . almora_bud_url( $base, $keep + array( 'g' => $k, 'sort' => 'new' === $sort ? '' : $sort ) ) . '"' . $cur . '>' . esc_html( $label ) . '</a>';
		}
		$bar .= '</div>';
	}
	if ( count( $have ) > 1 ) {
		$bar .= '<div class="alm-bud__group"><span class="alm-bud__label">סוג:</span>';
		$bar .= '<a class="alm-bud__chip" href="' . almora_bud_url( $base, $keep + array( 'g' => $g, 'sort' => 'new' === $sort ? '' : $sort ) ) . '"' . ( '' === $t ? ' aria-current="true"' : '' ) . '>הכל</a>';
		foreach ( $types as $k => $label ) {
			if ( empty( $have[ $k ] ) ) {
				continue;
			}
			$cur  = $t === $k ? ' aria-current="true"' : '';
			$bar .= '<a class="alm-bud__chip" href="' . almora_bud_url( $base, $keep + array( 'g' => $g, 't' => $k, 'sort' => 'new' === $sort ? '' : $sort ) ) . '"' . $cur . '>' . esc_html( $label ) . '</a>';
		}
		$bar .= '</div>';
	}
	$bar .= '<div class="alm-bud__group"><span class="alm-bud__label">מיון:</span>';
	foreach ( array( 'new' => 'הכי חדש', 'asc' => 'מחיר עולה', 'desc' => 'מחיר יורד' ) as $k => $label ) {
		$cur  = $sort === $k ? ' aria-current="true"' : '';
		$bar .= '<a class="alm-bud__chip" href="' . almora_bud_url( $base, $keep + array( 'g' => $g, 't' => $t, 'sort' => 'new' === $k ? '' : $k ) ) . '"' . $cur . '>' . esc_html( $label ) . '</a>';
	}
	$bar .= '</div></div>';

	$html .= '<h2>' . esc_html( 'תכשיטים ' . ( 0 === $r[0] ? $r[2] : ( 0 === $r[1] ? $r[2] : 'בטווח ' . $r[2] ) ) ) . '</h2>';
	$html .= $bar;

	if ( ! $res ) {
		$html .= '<p class="alm-bud__empty">אין כרגע מוצרים זמינים בבחירה הזו.</p></div></div>';
		return $html;
	}

	uasort( $res, function ( $a, $b ) use ( $sort ) {
		if ( 'asc' === $sort ) {
			return $a['price'] <=> $b['price'] ?: $b['ts'] <=> $a['ts'];
		}
		if ( 'desc' === $sort ) {
			return $b['price'] <=> $a['price'] ?: $b['ts'] <=> $a['ts'];
		}
		return $b['ts'] <=> $a['ts'];
	} );
	$ids = array_keys( $res );

	$q = new WP_Query( array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'post__in'            => $ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $ids ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	) );

	global $product;
	$keep_product = $product;
	wc_setup_loop( array( 'columns' => 4, 'name' => 'almora_budget' ) );
	ob_start();
	woocommerce_product_loop_start();
	while ( $q->have_posts() ) {
		$q->the_post();
		wc_get_template_part( 'content', 'product' );
	}
	woocommerce_product_loop_end();
	$html .= ob_get_clean();
	wp_reset_postdata();
	wc_reset_loop();
	$product = $keep_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride

	return $html . '</div></div>';
}
add_shortcode( 'almora_budget', 'almora_bud_render' );
