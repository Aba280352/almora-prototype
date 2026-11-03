<?php
/**
 * Plugin Name: ALMORA size guide
 * Description: Simple size guide for rings (two ways to measure, one table in US sizes with inner diameter and finger circumference in mm) and bracelets (wrist + 1-2 cm, one table). Shortcode [almora_size_guide] for the size guide page, and a "size guide" link under the size choice on ring and bracelet product pages that opens the relevant part in a popup. The on-screen sizer (almora-size-sizer.php) sits folded at the end of the rings part.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_SG_RING_CATS     = array( 66, 120, 127 );
const ALMORA_SG_BRACELET_CATS = array( 69, 121, 125 );

/** Small line drawing: inner diameter of a ring. */
function almora_sg_svg_ring() {
	return '<svg class="alm-sg__art" viewBox="0 0 120 80" role="img" aria-label="מודדים את הרוחב הפנימי של הטבעת" xmlns="http://www.w3.org/2000/svg">'
		. '<circle cx="60" cy="40" r="32" fill="none" stroke="#202020" stroke-width="3"/>'
		. '<circle cx="60" cy="40" r="22" fill="none" stroke="#202020" stroke-width="3"/>'
		. '<path d="M43 40H77M43 40l5-4M43 40l5 4M77 40l-5-4M77 40l-5 4" fill="none" stroke="#202020" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';
}

/** Small line drawing: a paper strip with the mark where the ends meet. */
function almora_sg_svg_strip() {
	return '<svg class="alm-sg__art" viewBox="0 0 120 80" role="img" aria-label="פס נייר עם סימון איפה הקצוות נפגשים" xmlns="http://www.w3.org/2000/svg">'
		. '<rect x="8" y="24" width="104" height="18" rx="3" fill="#fff" stroke="#202020" stroke-width="3"/>'
		. '<path d="M88 18V48" stroke="#202020" stroke-width="3" stroke-linecap="round" stroke-dasharray="1 6"/>'
		. '<path d="M8 62H88M8 62l5-4M8 62l5 4M88 62l-5-4M88 62l-5 4" fill="none" stroke="#202020" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
		. '</svg>';
}

function almora_sg_rings_html() {
	$rows = '';
	foreach ( range( 5, 14 ) as $us ) {
		$d     = 11.63 + 0.8128 * $us;
		$rows .= '<tr><td>' . $us . '</td><td>' . number_format( $d, 1 ) . '</td><td>' . number_format( M_PI * $d, 1 ) . '</td></tr>';
	}
	return '<section class="alm-sg__part" id="rings">'
		. '<h2>מידות טבעות</h2>'
		. '<p class="alm-sg__lead">שתי דרכים פשוטות למדוד, ואז מוצאים את המידה בטבלה.</p>'
		. '<div class="alm-sg__ways">'
		. '<div class="alm-sg__way"><span class="alm-sg__tag">דרך 1</span><h3>טבעת שמתאימה לכם</h3>' . almora_sg_svg_ring()
		. '<ol class="alm-sg__steps"><li>בוחרים טבעת שיושבת טוב על האצבע.</li><li>מודדים בסרגל את הרוחב הפנימי שלה, במילימטרים.</li><li>מחפשים את המספר בעמודה "קוטר פנימי" בטבלה.</li></ol></div>'
		. '<div class="alm-sg__way"><span class="alm-sg__tag">דרך 2</span><h3>פס נייר</h3>' . almora_sg_svg_strip()
		. '<ol class="alm-sg__steps"><li>כורכים פס נייר דק סביב בסיס האצבע.</li><li>מסמנים איפה הקצוות נפגשים.</li><li>מודדים בסרגל את האורך עד הסימון, במילימטרים.</li><li>מחפשים את המספר בעמודה "היקף אצבע" בטבלה.</li></ol></div>'
		. '</div>'
		. '<div class="alm-sg__table"><table><thead><tr><th>מידה (US)</th><th>קוטר פנימי (מ"מ)</th><th>היקף אצבע (מ"מ)</th></tr></thead><tbody>' . $rows . '</tbody></table></div>'
		. '<p class="alm-sg__between"><b>יצא בין שתי מידות? בוחרים את הגדולה.</b></p>'
		. '<ul class="alm-sg__tips">'
		. '<li>מודדים בסוף היום, כשהידיים חמות. בקור האצבעות קטנות יותר.</li>'
		. '<li>טבעת רחבה יושבת צמוד יותר, ולכן כדאי לבחור בה מידה אחת גדולה.</li>'
		. '<li>רגילים למידות אירופיות, כמו 54? זה בערך ההיקף במ"מ בעמודה האחרונה.</li>'
		. '<li>המידות הזמינות בכל טבעת מופיעות בעמוד שלה.</li>'
		. '</ul>'
		. ( function_exists( 'almora_sg_tool_html' ) ? almora_sg_tool_html() : '' )
		. '</section>';
}

function almora_sg_bracelets_html() {
	$rows = '';
	foreach ( range( 14, 18 ) as $w ) {
		$rows .= '<tr><td>' . $w . '</td><td>' . ( $w + 1 ) . '–' . ( $w + 2 ) . '</td></tr>';
	}
	return '<section class="alm-sg__part" id="bracelets">'
		. '<h2>מידות צמידים</h2>'
		. '<p class="alm-sg__lead">מודדים את פרק היד, מוסיפים קצת, ובוחרים אורך.</p>'
		. '<div class="alm-sg__ways alm-sg__ways--one"><div class="alm-sg__way"><span class="alm-sg__tag">3 שלבים</span>'
		. '<ol class="alm-sg__steps"><li>כורכים סרט מדידה רך או חוט סביב פרק היד, במקום שבו הצמיד ישב.</li><li>מודדים בסנטימטרים. זה היקף פרק היד.</li><li>מוסיפים 1 עד 2 ס"מ: 1 למראה צמוד, 2 למראה משוחרר. מחפשים את האורך בטבלה.</li></ol></div></div>'
		. '<div class="alm-sg__table"><table><thead><tr><th>היקף פרק היד (ס"מ)</th><th>אורך צמיד מתאים (ס"מ)</th></tr></thead><tbody>' . $rows . '</tbody></table></div>'
		. '<p class="alm-sg__between"><b>יצא בין שני אורכים? בוחרים את הארוך.</b></p>'
		. '<ul class="alm-sg__tips">'
		. '<li>כשאורך הצמיד ידוע, הוא מופיע בעמוד המוצר או כאפשרות לבחירה.</li>'
		. '<li>בצמידים קשיחים ההתאמה שונה. יש שאלה על צמיד מסוים? כתבו לנו ונבדוק.</li>'
		. '</ul></section>';
}

function almora_sg_help_html() {
	return '<p class="alm-sg__help">צריך עזרה במידה? כתבו לנו בוואטסאפ <a href="https://wa.me/972523550851" target="_blank" rel="noopener">052-3550851</a> ונעזור לבחור.</p>';
}

function almora_sg_css() {
	return '<style id="almora-size-guide">'
		. '.alm-sg{max-width:860px;margin:0 auto;color:#202020;line-height:1.7}'
		. '.alm-sg__jump{display:flex;gap:10px;margin:0 0 28px}'
		. '.alm-sg__jump a{flex:1;text-align:center;padding:12px 10px;border-radius:12px;background:#202020;color:#fff;font-size:16px;font-weight:600;text-decoration:none}'
		. '.alm-sg__jump a:hover{background:#202020;color:#fff;opacity:.9}'
		. '.alm-sg__part{margin:0 0 40px}'
		. '.alm-sg h2{font-size:26px;margin:0 0 6px}'
		. '.alm-sg h3{font-size:17px;margin:6px 0 8px}'
		. '.alm-sg__lead{margin:0 0 14px}'
		. '.alm-sg__ways{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:16px 0}'
		. '.alm-sg__ways--one{grid-template-columns:1fr}'
		. '.alm-sg__way{background:#f5f5f5;border-radius:12px;padding:16px 18px}'
		. '.alm-sg__tag{display:inline-block;font-size:13px;font-weight:600;background:#fff;border-radius:12px;padding:2px 10px}'
		. '.alm-sg .alm-sg__art{display:block;width:120px;height:80px;margin:4px auto 10px}'
		. '.alm-sg__steps{list-style:none;counter-reset:alm;margin:8px 0 0;padding:0}'
		. '.alm-sg__steps li{counter-increment:alm;display:flex;gap:10px;align-items:flex-start;margin:0 0 8px}'
		. '.alm-sg__steps li::before{content:counter(alm);flex:none;width:24px;height:24px;border-radius:50%;background:#202020;color:#fff;font-size:13px;line-height:24px;text-align:center;margin-top:2px}'
		. '.alm-sg__table{overflow-x:auto;margin:16px 0 10px}'
		. '.alm-sg table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e6e6e6;border-radius:12px;overflow:hidden;text-align:center}'
		. '.alm-sg th{background:#202020;color:#fff;font-weight:600;padding:10px 8px}'
		. '.alm-sg th,.alm-sg td{text-align:center}'
		. '.alm-sg td{padding:6px 8px;border-top:1px solid #eee;line-height:1.5}'
		. '.alm-sg tbody tr:nth-child(even) td{background:#fafafa}'
		. '.alm-sg__between{margin:0 0 8px}'
		. '.alm-sg__tips{margin:0;padding-inline-start:20px;font-size:14px;color:#444}'
		. '.alm-sg__tips li{margin:0 0 4px}'
		. '.alm-sg__help{background:#f5f5f5;border-radius:12px;padding:14px 18px}'
		. '.alm-sg__help a{color:#202020;font-weight:600;text-decoration:underline}'
		. '@media(max-width:600px){.alm-sg__ways{grid-template-columns:1fr}.alm-sg h2{font-size:22px}}'
		. '.alm-sg-link{display:inline-flex;align-items:center;gap:6px;margin:4px 0 14px;padding:0;background:none;border:0;color:#202020;font-size:14px;text-decoration:underline;text-underline-offset:3px;cursor:pointer}'
		. '.alm-sg-dialog{width:min(720px,calc(100vw - 32px));max-height:calc(100vh - 48px);border:0;border-radius:12px;padding:28px 24px 20px;box-shadow:0 10px 40px rgba(0,0,0,.2)}'
		. '.alm-sg-dialog::backdrop{background:rgba(0,0,0,.45)}'
		. '.alm-sg-dialog__close{position:absolute;top:10px;left:12px;background:none;border:0;font-size:26px;line-height:1;cursor:pointer;color:#202020}'
		. '.alm-sg-dialog__more{display:inline-block;margin-top:8px;color:#202020;font-weight:600}'
		. '</style>';
}

add_shortcode( 'almora_size_guide', function () {
	return almora_sg_css() . '<div class="alm-sg" dir="rtl">'
		. '<div class="alm-sg__jump"><a href="#rings">טבעות</a><a href="#bracelets">צמידים</a></div>'
		. almora_sg_rings_html() . almora_sg_bracelets_html() . almora_sg_help_html() . '</div>';
} );

/** Which part of the guide fits this product: rings, bracelets, or none. */
function almora_sg_kind( $product_id ) {
	if ( has_term( ALMORA_SG_RING_CATS, 'product_cat', $product_id ) ) {
		return 'rings';
	}
	if ( has_term( ALMORA_SG_BRACELET_CATS, 'product_cat', $product_id ) ) {
		return 'bracelets';
	}
	return '';
}

/* product page: a link under the size choice, the guide in a popup */
add_action( 'woocommerce_after_variations_table', function () {
	global $product;
	if ( ! $product || ! almora_sg_kind( $product->get_id() ) ) {
		return;
	}
	echo '<button type="button" class="alm-sg-link" data-alm-sg-open>מדריך מידות</button>';
} );

add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$kind = almora_sg_kind( get_the_ID() );
	if ( ! $kind ) {
		return;
	}
	$page = get_option( 'almora_size_guide_page' );
	echo almora_sg_css(); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<dialog class="alm-sg-dialog" id="alm-sg-dialog" dir="rtl" aria-label="מדריך מידות"><button type="button" class="alm-sg-dialog__close" aria-label="סגירה" data-alm-sg-close>&times;</button><div class="alm-sg">'
		. ( 'rings' === $kind ? almora_sg_rings_html() : almora_sg_bracelets_html() )
		. almora_sg_help_html()
		. ( $page ? '<a class="alm-sg-dialog__more" href="' . esc_url( get_permalink( $page ) ) . '">למדריך המלא</a>' : '' )
		. '</div></dialog>'; // phpcs:ignore WordPress.Security.EscapeOutput
	?>
<script>
(function () {
	var d = document.getElementById('alm-sg-dialog');
	if (!d) { return; }
	var downOnBackdrop = false;
	d.addEventListener('pointerdown', function (e) { downOnBackdrop = e.target === d; });
	document.addEventListener('click', function (e) {
		if (e.target.closest('[data-alm-sg-open]')) { e.preventDefault(); if (d.showModal) { d.showModal(); } else { d.setAttribute('open', ''); } }
		/* close on the X, or on the backdrop only when the press also started there (dragging a slider and letting go outside must not close it) */
		if (e.target.closest('[data-alm-sg-close]') || (e.target === d && downOnBackdrop)) { d.close ? d.close() : d.removeAttribute('open'); }
	});
})();
</script>
	<?php
}, 98 );
