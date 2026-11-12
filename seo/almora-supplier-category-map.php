<?php
/**
 * ALMORA supplier category map (Code Snippets, run everywhere).
 *
 * LitCommerce / Silver Israel creates its own duplicate categories ("כל העגילים", "שרשראות משובצות", "תכשיטי נשים" ...)
 * every time it pushes a product. After a product is saved through the REST API, map those categories onto ALMORA's own
 * (rings 66, earrings 67, necklaces 68, bracelets 69), add "תכשיטי ציפוי זהב" (153) when the product is brass (פליז),
 * and delete the supplier category if it is now empty, so no duplicate categories pile up.
 * Owner rule 2026-11-12: only our own categories on the site, no duplicates.
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

function almora_cm_fix( $pid ) {
	$map       = array(
		'כל הטבעות' => 66, 'טבעות משובצות' => 66,
		'כל העגילים' => 67, 'עגילי חישוק' => 67, 'עגילים נופלים' => 67, 'עגילים צמודים' => 67,
		'כל השרשראות' => 68, 'שרשראות משובצות' => 68,
		'כל הצמידים' => 69,
		'תכשיטי נשים' => 0,
	);
	$protected = array( 15, 66, 67, 68, 69, 70, 71, 111, 112, 117, 119, 120, 121, 122, 123, 124, 125, 126, 127, 153 );
	$terms     = wp_get_object_terms( $pid, 'product_cat' );
	if ( is_wp_error( $terms ) ) {
		return;
	}
	$new     = array();
	$junk    = array();
	$changed = false;
	foreach ( $terms as $t ) {
		$n = trim( $t->name );
		if ( array_key_exists( $n, $map ) && ! in_array( (int) $t->term_id, $protected, true ) ) {
			$changed = true;
			$junk[]  = (int) $t->term_id;
			if ( $map[ $n ] ) {
				$new[] = $map[ $n ];
			}
		} else {
			$new[] = (int) $t->term_id;
		}
	}
	$post = get_post( $pid );
	if ( ! $post ) {
		return;
	}
	if ( ! array_intersect( $new, array( 66, 67, 68, 69, 71, 70 ) ) && ! array_intersect( $new, array( 117, 124 ) ) ) {
		$title = $post->post_title;
		foreach ( array( 'טבעת' => 66, 'עגיל' => 67, 'שרשרת' => 68, 'צמיד' => 69, 'תליון' => 71 ) as $w => $id ) {
			if ( false !== mb_strpos( $title, $w ) ) {
				$new[]   = $id;
				$changed = true;
				break;
			}
		}
	}
	if ( false !== mb_strpos( $post->post_title . ' ' . $post->post_content, 'פליז' ) && ! in_array( 153, $new, true ) && term_exists( 153, 'product_cat' ) ) {
		$new[]   = 153;
		$changed = true;
	}
	if ( $changed ) {
		wp_set_object_terms( $pid, array_values( array_unique( array_map( 'intval', $new ) ) ), 'product_cat' );
	}
	foreach ( $junk as $j ) {
		$t = get_term( $j, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) && 0 === (int) $t->count ) {
			wp_delete_term( $j, 'product_cat' );
		}
	}
}

add_action(
	'woocommerce_rest_insert_product_object',
	function ( $product ) {
		if ( $product && method_exists( $product, 'get_id' ) ) {
			almora_cm_fix( (int) $product->get_id() );
		}
	},
	99,
	1
);
