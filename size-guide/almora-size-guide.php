<?php
/**
 * Plugin Name: ALMORA size guide
 * Description: Size guide for rings (US sizes with inner diameter and finger circumference in mm) and bracelets (how to measure the wrist, length to choose). Shortcode [almora_size_guide] for the size guide page, and a "size guide" link under the size choice on ring and bracelet product pages that opens the relevant part in a popup.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_SG_RING_CATS     = array( 66, 120, 127 );
const ALMORA_SG_BRACELET_CATS = array( 69, 121, 125 );

function almora_sg_rings_html() {
	$rows = '';
	foreach ( range( 5, 14 ) as $us ) {
		$d     = 11.63 + 0.8128 * $us;
		$rows .= '<tr><td>' . $us . '</td><td>' . number_format( $d, 1 ) . '</td><td>' . number_format( M_PI * $d, 1 ) . '</td></tr>';
	}
	return '<section class="alm-sg__part" id="rings">'
		. '<h2>מידות טבעות</h2>'
		. '<p>המידות באתר הן מידות אמריקאיות (US), השיטה המקובלת ברוב אתרי התכשיטים. הטבלה מציגה לכל מידה את הקוטר הפנימי של הטבעת ואת היקף האצבע במילימטרים.</p>'
		. '<div class="alm-sg__ways">'
		. '<div class="alm-sg__way"><h3>לפי טבעת שכבר מתאימה לך</h3><p>מניחים את הטבעת על סרגל ומודדים את הקוטר הפנימי, מקצה לקצה מבפנים. מוצאים בטבלה את הקוטר הקרוב.</p></div>'
		. '<div class="alm-sg__way"><h3>לפי היקף האצבע</h3><p>כורכים פס נייר דק או חוט סביב בסיס האצבע, מסמנים איפה הוא נפגש ומודדים בסרגל במילימטרים. מוצאים בטבלה את ההיקף הקרוב.</p></div>'
		. '</div>'
		. '<div class="alm-sg__table"><table><thead><tr><th>מידה (US)</th><th>קוטר פנימי (מ"מ)</th><th>היקף אצבע (מ"מ)</th></tr></thead><tbody>' . $rows . '</tbody></table></div>'
		. '<ul class="alm-sg__tips">'
		. '<li>מודדים בסוף היום, כשהידיים חמות. בבוקר ובקור האצבעות קצת קטנות יותר.</li>'
		. '<li>אם יצא בין שתי מידות, כדאי לבחור את הגדולה.</li>'
		. '<li>הטבעת צריכה לעבור את המפרק. אם המפרק רחב, מודדים גם אותו ובוחרים לפי הגדול מבין השניים.</li>'
		. '<li>טבעת רחבה יושבת צמוד יותר, אז אם יצא קרוב לגבול בין מידות, עדיף את הגדולה.</li>'
		. '<li>המידות שאפשר לבחור בכל טבעת מופיעות בעמוד שלה.</li>'
		. '</ul></section>';
}

function almora_sg_bracelets_html() {
	$rows = '';
	foreach ( range( 14, 18 ) as $w ) {
		$rows .= '<tr><td>' . $w . '</td><td>' . ( $w + 1 ) . '–' . ( $w + 2 ) . '</td></tr>';
	}
	return '<section class="alm-sg__part" id="bracelets">'
		. '<h2>מידות צמידים</h2>'
		. '<div class="alm-sg__ways">'
		. '<div class="alm-sg__way"><h3>איך מודדים</h3><p>כורכים סרט מדידה רך או חוט סביב פרק היד, במקום שבו הצמיד ישב, ומודדים בסנטימטרים. זה היקף פרק היד.</p></div>'
		. '<div class="alm-sg__way"><h3>איזה אורך לבחור</h3><p>לצמיד שרשרת או צמיד טניס מוסיפים לרוב 1 עד 2 ס"מ להיקף פרק היד: 1 ס"מ למראה צמוד, 2 ס"מ למראה משוחרר.</p></div>'
		. '</div>'
		. '<div class="alm-sg__table"><table><thead><tr><th>היקף פרק היד (ס"מ)</th><th>אורך צמיד מתאים (ס"מ)</th></tr></thead><tbody>' . $rows . '</tbody></table></div>'
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
		. '.alm-sg__part{margin:0 0 40px}'
		. '.alm-sg h2{font-size:26px;margin:0 0 12px}'
		. '.alm-sg h3{font-size:17px;margin:0 0 6px}'
		. '.alm-sg__ways{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:16px 0}'
		. '.alm-sg__way{background:#f5f5f5;border-radius:12px;padding:16px 18px}'
		. '.alm-sg__way p{margin:0}'
		. '.alm-sg__table{overflow-x:auto;margin:16px 0}'
		. '.alm-sg table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e6e6e6;border-radius:12px;overflow:hidden;text-align:center}'
		. '.alm-sg th{background:#202020;color:#fff;font-weight:600;padding:10px 8px}'
		. '.alm-sg td{padding:9px 8px;border-top:1px solid #eee}'
		. '.alm-sg tbody tr:nth-child(even) td{background:#fafafa}'
		. '.alm-sg__tips{margin:12px 0 0;padding-inline-start:20px}'
		. '.alm-sg__tips li{margin:0 0 6px}'
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
		. '<p>איך בוחרים מידה של טבעת או צמיד בלי לנחש. שתי דרכים פשוטות למדוד בבית, וטבלה שמתרגמת את המדידה למידה.</p>'
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
	document.addEventListener('click', function (e) {
		if (e.target.closest('[data-alm-sg-open]')) { e.preventDefault(); if (d.showModal) { d.showModal(); } else { d.setAttribute('open', ''); } }
		if (e.target.closest('[data-alm-sg-close]') || e.target === d) { d.close ? d.close() : d.removeAttribute('open'); }
	});
})();
</script>
	<?php
}, 98 );
