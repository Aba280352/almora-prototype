<?php
/**
 * ALMORA category normalizer (Code Snippets, run everywhere).
 *
 * LitCommerce / Silver Israel pushes products with the supplier's own category tree ("קולקציית טניס", "טבעות לזרת",
 * "צמידי ג'אדי", "SHOP THE LOOK" ...). Owner rule 2026-11-12: only ALMORA's own categories on the site, no duplicates.
 * After every product save (REST push, LitCommerce, cron) this rebuilds the category list from our own set:
 *   type        rings 66, earrings 67, necklaces 68, bracelets 69, sets 70, pendants 71
 *   men         124 + sub (rings 127, necklaces 126, bracelets 125)
 *   moissanite  117 + sub (rings 120, earrings 119, bracelets 121, necklaces 122, sets 123)
 *   plated      153 (brass / gold plated)
 *   accessories "אקססוריז" (slug accessories) + child "ציוד לפירסינג" or "סטנדים לתכשיטים"
 * Supplier categories are dropped from the product (the empty category terms are left for the owner to delete).
 * A product it cannot classify is left untouched and its ID is written to the option almora_cn_unclassified.
 * Saves made from the admin product edit screen are not touched, so a manual choice by the owner stays.
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

function almora_cn_accessory_ids() {
	$parent = get_term_by( 'slug', 'accessories', 'product_cat' );
	$pierc  = get_term_by( 'slug', 'piercing-equipment', 'product_cat' );
	$stands = get_term_by( 'slug', 'jewelry-stands', 'product_cat' );
	return array(
		'parent' => $parent ? (int) $parent->term_id : 0,
		'pierc'  => $pierc ? (int) $pierc->term_id : 0,
		'stands' => $stands ? (int) $stands->term_id : 0,
	);
}

/**
 * Sub-categories under rings / earrings / necklaces / bracelets (owner OK 2026-11-13).
 * key => array( parent type id, name, slug, regex run on supplier categories + product title )
 */
function almora_cn_subs_map() {
	return array(
		'pinky'    => array( 66, 'טבעות לזרת', 'pinky-rings', '/לזרת/u' ),
		'e-stud'   => array( 67, 'עגילים צמודים', 'stud-earrings', '/עגילים צמודים|עגילי צמוד/u' ),
		'e-drop'   => array( 67, 'עגילים נופלים', 'drop-earrings', '/נופלים|עגילי נפילה/u' ),
		'e-hoop'   => array( 67, 'עגילי חישוק', 'hoop-earrings', '/חישוק/u' ),
		'e-two'    => array( 67, 'עגילי שני חורים', 'two-hole-earrings', '/שני חורים/u' ),
		'e-helix'  => array( 67, 'עגילי הליקס', 'helix-earrings', '/הליקס/u' ),
		'e-eve'    => array( 67, 'עגילי ערב', 'evening-earrings', '/עגילי ערב/u' ),
		'n-tennis' => array( 68, 'שרשראות טניס', 'tennis-necklaces', '/טניס/u' ),
		'n-choker' => array( 68, 'שרשראות צ\'וקר', 'choker-necklaces', '/צ.?וקר|קולר/u' ),
		'n-tie'    => array( 68, 'שרשראות עניבה', 'tie-necklaces', '/עניבה/u' ),
		'n-ice'    => array( 68, 'שרשראות אייס', 'ice-necklaces', '/אייס/u' ),
		'n-coll'   => array( 68, 'קוליירים', 'colliers', '/קולייר/u' ),
		'b-tennis' => array( 69, 'צמידי טניס', 'tennis-bracelets', '/טניס/u' ),
		'b-bangle' => array( 69, 'צמידים קשיחים', 'bangle-bracelets', '/קשיח|בנגל/u' ),
		'b-jadi'   => array( 69, 'צמידי ג\'אדי', 'jadi-bracelets', '/ג.?אדי/u' ),
		'b-chain'  => array( 69, 'צמידי שרשרת', 'chain-bracelets', '/צמידי שרשרת/u' ),
		'b-ice'    => array( 69, 'צמידי אייס', 'ice-bracelets', '/אייס/u' ),
	);
}

function almora_cn_sub_ids( $types, $text ) {
	$ids = array();
	foreach ( almora_cn_subs_map() as $s ) {
		if ( ! in_array( $s[0], $types, true ) || ! preg_match( $s[3], $text ) ) {
			continue;
		}
		$t = get_term_by( 'slug', $s[2], 'product_cat' );
		if ( ! $t ) {
			$r = wp_insert_term( $s[1], 'product_cat', array( 'slug' => $s[2], 'parent' => $s[0] ) );
			if ( is_wp_error( $r ) ) {
				continue;
			}
			$ids[] = (int) $r['term_id'];
		} else {
			$ids[] = (int) $t->term_id;
		}
	}
	return $ids;
}

function almora_cn_sub_slugs_ids() {
	$ids = array();
	foreach ( almora_cn_subs_map() as $s ) {
		$t = get_term_by( 'slug', $s[2], 'product_cat' );
		if ( $t ) {
			$ids[] = (int) $t->term_id;
		}
	}
	return $ids;
}

function almora_cn_fix( $pid ) {
	static $busy = false;
	if ( $busy ) {
		return;
	}
	$busy = true;
	try {
		almora_cn_fix_inner( (int) $pid );
	} finally {
		$busy = false;
	}
}

function almora_cn_fix_inner( $pid ) {
	global $pagenow;
	if ( is_admin() && in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
		return; // a manual save on the edit screen keeps the owner's choice
	}
	$post = get_post( $pid );
	if ( ! $post || 'product' !== $post->post_type ) {
		return;
	}
	$acc  = almora_cn_accessory_ids();
	$ours = array( 66, 67, 68, 69, 70, 71, 111, 112, 117, 119, 120, 121, 122, 123, 124, 125, 126, 127, 153 );
	foreach ( $acc as $a ) {
		if ( $a ) {
			$ours[] = $a;
		}
	}
	$pc = get_term_by( 'slug', 'piercing', 'product_cat' );
	if ( $pc ) {
		$ours[] = (int) $pc->term_id;
	}
	$sub_ids = almora_cn_sub_slugs_ids();
	$ours    = array_merge( $ours, $sub_ids );
	$terms = wp_get_object_terms( $pid, 'product_cat' );
	if ( is_wp_error( $terms ) ) {
		return;
	}
	$old   = array();
	$names = array();
	$keep  = array();
	foreach ( $terms as $t ) {
		$old[]   = (int) $t->term_id;
		$names[] = trim( html_entity_decode( $t->name ) );
		if ( in_array( (int) $t->term_id, $ours, true ) ) {
			$keep[] = (int) $t->term_id;
		}
	}
	$sup   = implode( ' | ', $names );
	$title = html_entity_decode( $post->post_title );
	$new   = array();

	/* accessories (piercing equipment, display stands) */
	$is_stand = (bool) preg_match( '/מתקני תצוגה|סטנד עומד|סטנד שוכב|משטח/u', $sup . ' ' . $title ) && ! preg_match( '/עגיל|טבעת|שרשרת|צמיד/u', $title );
	$is_equip = (bool) preg_match( '/מחטים|מחטי|מלקחיים|פלייר|כפפות|דרמל|אביזרים לפירסינג|ציוד עזר|ציוד נלווה/u', $sup . ' ' . $title );
	/* piercing jewelry (titanium labrets, barbells, belly rings ...) is a main category of its own, not earrings */
	$pierc_cat = get_term_by( 'slug', 'piercing', 'product_cat' );
	if ( $pierc_cat && ! $is_equip && preg_match( '/Lumipierce|Belly Ring|Labret|Barbell|לברט|קליקר|פירסינג/ui', $sup . ' ' . $title ) ) {
		almora_cn_apply( $pid, $old, array( (int) $pierc_cat->term_id ) );
		return;
	}
	if ( $pierc_cat && in_array( (int) $pierc_cat->term_id, $keep, true ) ) {
		return;
	}
	if ( $acc['parent'] && ( $is_stand || $is_equip ) ) {
		$new = array( $acc['parent'] );
		if ( $is_stand && $acc['stands'] ) {
			$new[] = $acc['stands'];
		} elseif ( $is_equip && $acc['pierc'] ) {
			$new[] = $acc['pierc'];
		}
		almora_cn_apply( $pid, $old, $new );
		return;
	}
	if ( in_array( $acc['parent'], $keep, true ) ) {
		return; // already an accessory, leave it
	}

	/* type: first jewelry word in the title, else the supplier categories */
	$type  = 0;
	$words = array( 66 => array( 'טבעת', 'טבעות' ), 67 => array( 'עגיל' ), 68 => array( 'שרשרת', 'קולר', 'קולייר' ), 69 => array( 'צמיד' ), 71 => array( 'תליון' ) );
	$best  = PHP_INT_MAX;
	foreach ( $words as $id => $ws ) {
		foreach ( $ws as $w ) {
			$pos = mb_strpos( $title, $w );
			if ( false !== $pos && $pos < $best ) {
				$best = $pos;
				$type = $id;
			}
		}
	}
	if ( false !== mb_strpos( $title, 'לעגיל' ) ) {
		$type = 67; // a chain made for earrings stays under earrings
	}
	if ( preg_match( '/(^|\s)סט(\s|$)/u', $title ) || false !== mb_strpos( $sup, 'SHOP THE LOOK' ) ) {
		$type = 70;
	}
	if ( ! $type ) {
		foreach ( array( 67 => 'עגיל', 66 => 'טבעות', 69 => 'צמיד', 68 => 'שרשראות', 71 => 'תליונים' ) as $id => $w ) {
			if ( false !== mb_strpos( $sup, $w ) ) {
				$type = $id;
				break;
			}
		}
	}
	if ( ! $type ) {
		$type = (int) current( array_intersect( array( 66, 67, 68, 69, 70, 71 ), $keep ) );
	}
	if ( ! $type ) {
		$list   = (array) get_option( 'almora_cn_unclassified', array() );
		$list[] = $pid;
		update_option( 'almora_cn_unclassified', array_values( array_unique( array_map( 'intval', $list ) ) ), false );
		return;
	}
	$new[] = $type;

	/* men */
	if ( preg_match( '/גברים|לגבר|גבר/u', $sup . ' ' . $title ) || in_array( 124, $keep, true ) ) {
		$new[] = 124;
		$sub   = array( 66 => 127, 68 => 126, 69 => 125 );
		if ( isset( $sub[ $type ] ) ) {
			$new[] = $sub[ $type ];
		}
	}
	/* moissanite */
	if ( preg_match( '/מוסונייט|מוסנייט|moissanite/ui', $sup . ' ' . $title ) || in_array( 117, $keep, true ) ) {
		$new[] = 117;
		$sub   = array( 66 => 120, 67 => 119, 69 => 121, 68 => 122, 70 => 123 );
		if ( isset( $sub[ $type ] ) ) {
			$new[] = $sub[ $type ];
		}
	}
	/* plated */
	if ( false !== mb_strpos( $title . ' ' . $post->post_content, 'פליז' ) || preg_match( '/ציפוי/u', $sup ) || in_array( 153, $keep, true ) ) {
		$new[] = 153;
	}
	/* sub-categories (hoops, tennis, bangles ...) from the supplier names + title */
	$new = array_merge( $new, almora_cn_sub_ids( array( $type ), $sup . ' ' . $title ), array_intersect( $keep, $sub_ids ) );
	/* flags we already had on the product (new in stock, hand-picked) */
	foreach ( array( 111, 112 ) as $f ) {
		if ( in_array( $f, $keep, true ) ) {
			$new[] = $f;
		}
	}
	almora_cn_apply( $pid, $old, $new );
}

function almora_cn_apply( $pid, $old, $new ) {
	$new = array_values( array_unique( array_map( 'intval', array_filter( $new ) ) ) );
	$a   = $old;
	$b   = $new;
	sort( $a );
	sort( $b );
	if ( $a !== $b && $new ) {
		if ( ! empty( $GLOBALS['almora_cn_dry'] ) ) {
			$GLOBALS['almora_cn_log'][ $pid ] = array( $a, $b );
			return;
		}
		wp_set_object_terms( $pid, $new, 'product_cat' );
	}
}

add_action(
	'woocommerce_new_product',
	function ( $id ) {
		almora_cn_fix( $id );
	},
	99
);
add_action(
	'woocommerce_update_product',
	function ( $id ) {
		almora_cn_fix( $id );
	},
	99
);
add_action(
	'woocommerce_rest_insert_product_object',
	function ( $product ) {
		if ( $product && method_exists( $product, 'get_id' ) ) {
			almora_cn_fix( (int) $product->get_id() );
		}
	},
	100
);
