<?php
/**
 * Plugin Name: ALMORA size guide
 * Description: Ring size guide: true-size circles to lay a ring on (best printed at 100%), a size table and a bracelet part. Shortcode [almora_size_guide] on the size guide page (9477). Printing the page prints only the sheets, one per A4 page. On ring and bracelet product pages a "size guide" link opens the page in a new tab.
 * US size to inner diameter: d = 11.63 + 0.8128 x US (mm). EU size is about the inner circumference in mm.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_SG_RING_CATS     = array( 66, 120, 127 );
const ALMORA_SG_BRACELET_CATS = array( 69, 121, 125 );

function almora_sg_d( $us ) {
	return 11.63 + 0.8128 * $us;
}

function almora_sg_c( $us ) {
	return M_PI * almora_sg_d( $us );
}

function almora_sg_n( $n, $dec = 1 ) {
	return number_format( $n, $dec, '.', '' );
}

/** US sizes 5 to 14 in half steps. */
function almora_sg_sizes() {
	$a = array();
	for ( $i = 10; $i <= 28; $i++ ) {
		$a[] = $i / 2;
	}
	return $a;
}

function almora_sg_us_label( $us ) {
	return ( floor( $us ) == $us ) ? (string) (int) $us : almora_sg_n( $us, 1 );
}

/**
 * Grey panel with true-size white circles (inner diameters of every half size) to lay a ring on.
 * Two layouts of the same circles: 5 columns (print + wide screens) and 3 columns with bigger labels (phones).
 */
function almora_sg_circles_svg( $variant = 'd' ) {
	$sizes = almora_sg_sizes();
	$mob   = ( 'm' === $variant );
	$cols  = $mob ? 3 : 5;
	$cw    = $mob ? 30 : 34;
	$ch    = $mob ? 30 : 33;
	$f_us  = $mob ? 5.2 : 4.2;
	$f_eu  = $mob ? 3.1 : 2.6;
	$w     = $cols * $cw;
	$rows  = (int) ceil( count( $sizes ) / $cols );
	$h     = $rows * $ch + 10;
	$o     = '<svg class="alm-sz__svg alm-sz__svg--circles alm-sz__svg--circles-' . $variant . '" viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="עיגולים בגודל אמיתי לכל מידה" font-family="inherit">';
	$o    .= '<rect width="' . $w . '" height="' . $h . '" rx="8" fill="#dedede"/>';
	foreach ( $sizes as $i => $us ) {
		$row = intdiv( $i, $cols );
		$col = $i % $cols;
		$cx  = $w - $cw / 2 - $col * $cw;
		$cy  = 5 + $ch / 2 + $row * $ch;
		$d   = almora_sg_d( $us );
		$o  .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . almora_sg_n( $d / 2, 3 ) . '" fill="#fff"/>';
		$o  .= '<text x="' . $cx . '" y="' . ( $cy - 0.3 ) . '" font-size="' . $f_us . '" font-weight="700" text-anchor="middle" fill="#202020">' . almora_sg_us_label( $us ) . '</text>';
		$o  .= '<text x="' . $cx . '" y="' . ( $cy + $f_us * 0.85 ) . '" font-size="' . $f_eu . '" text-anchor="middle" fill="#202020">EU ' . round( almora_sg_c( $us ) ) . '</text>';
	}
	$o .= '</svg>';
	return $o;
}

function almora_sg_table_html() {
	$rows = '';
	foreach ( almora_sg_sizes() as $us ) {
		$rows .= '<tr><td>' . almora_sg_us_label( $us ) . '</td><td>' . round( almora_sg_c( $us ) ) . '</td><td>' . almora_sg_n( almora_sg_d( $us ) ) . '</td><td>' . almora_sg_n( almora_sg_c( $us ) ) . '</td></tr>';
	}
	return '<table class="alm-sz__table"><thead><tr><th>מידה (ארה״ב)</th><th>אירופה (בערך)</th><th>קוטר פנימי (מ״מ)</th><th>היקף פנימי (מ״מ)</th></tr></thead><tbody>' . $rows . '</tbody></table>';
}

function almora_sg_bracelet_table_html() {
	$rows = '';
	foreach ( range( 14, 18 ) as $w ) {
		$rows .= '<tr><td>' . $w . '</td><td>' . ( $w + 1 ) . '–' . ( $w + 2 ) . '</td></tr>';
	}
	return '<table class="alm-sz__table alm-sz__table--narrow"><thead><tr><th>היקף פרק היד (ס״מ)</th><th>אורך צמיד מתאים (ס״מ)</th></tr></thead><tbody>' . $rows . '</tbody></table>';
}

function almora_sg_css() {
	return '<style id="almora-size-guide">'
		/* screen */
		. '.alm-sz{max-width:860px;margin:0 auto;color:#202020;line-height:1.7}'
		. '.alm-sz__sheet{background:#fff;border:1px solid #e6e6e6;border-radius:12px;padding:26px 28px;margin:0 0 24px;box-shadow:0 2px 14px rgba(0,0,0,.05)}'
		. '.alm-sz h2{font-size:26px;text-align:center;margin:6px 0 14px}'
		. '.alm-sz h3{font-size:19px;margin:22px 0 8px}'
		. '.alm-sz p{margin:0 0 10px}'
		. '.alm-sz__svg{display:block;margin:10px auto;height:auto}'
		. '.alm-sz__svg--circles{width:100%}'
		. '.alm-sz__svg--circles-m{display:none}'
		. '@media screen and (max-width:760px){.alm-sz__svg--circles-d{display:none}.alm-sz__svg--circles-m{display:block}}'
		. '.alm-sz__steps{list-style:none;counter-reset:s;margin:8px 0 12px;padding:0}'
		. '.alm-sz__steps li{counter-increment:s;display:flex;gap:10px;align-items:flex-start;margin:0 0 7px}'
		. '.alm-sz__steps li::before{content:counter(s);flex:none;width:24px;height:24px;border-radius:50%;background:#202020;color:#fff;font-size:13px;line-height:24px;text-align:center;margin-top:2px}'
		. '.alm-sz__tips{background:#f5f5f5;border-radius:12px;padding:12px 18px 12px 12px;margin:10px 0;font-size:14.5px}'
		. '.alm-sz__tips ul{margin:0;padding-inline-start:18px}'
		. '.alm-sz__tips li{margin:0 0 3px}'
		. '.alm-sz__table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #cfcfcf;border-radius:12px;overflow:hidden;text-align:center;margin:10px 0}'
		. '.alm-sz__table--narrow{max-width:520px;margin-left:auto;margin-right:auto}'
		. '.alm-sz__table th{background:#202020;color:#fff;font-weight:600;padding:9px 6px;font-size:14px}'
		. '.alm-sz__table td{padding:5px 6px;border-top:1px solid #e6e6e6;text-align:center;line-height:1.5;font-size:15px}'
		. '.alm-sz__table tbody tr:nth-child(even) td{background:#fafafa}'
		. '.alm-sz__note{font-size:13px;color:#6b6b6b}'
		. '.alm-sg-link{display:inline-flex;align-items:center;gap:6px;margin:4px 0 14px;padding:0;background:none;border:0;color:#202020;font-size:14px;text-decoration:underline;text-underline-offset:3px;cursor:pointer}'
		. '@media(max-width:600px){.alm-sz__sheet{padding:18px 14px}.alm-sz h2{font-size:22px}}'
		/* print: only the sheets, one per A4 page, true size */
		. '@media print{'
		. '@page{size:A4;margin:0}'
		. 'html,body{background:#fff!important;margin:0!important;padding:0!important}'
		. '.alm-sz-noprint{display:none!important}'
		. '.alm-sz-keep{margin:0!important;padding:0!important;border:0!important;width:auto!important;max-width:none!important;min-width:0!important;min-height:0!important;height:auto!important;display:block!important;position:static!important;overflow:visible!important;transform:none!important;background:none!important;float:none!important;box-shadow:none!important}'
		. '.alm-sz{line-height:1.55;font-size:10.5pt;max-width:none!important;margin:0!important;width:210mm}'
		. '.alm-sz .alm-sz__sheet{box-sizing:border-box;width:210mm;height:296mm;margin:0!important;border:0;border-radius:0;box-shadow:none;padding:12mm 16mm;overflow:hidden;break-after:page;page-break-after:always;display:flex;flex-direction:column}'
		. '.alm-sz .alm-sz__sheet:last-child{break-after:auto;page-break-after:auto}'
		. '.alm-sz h2{font-size:18pt;margin:3mm 0 5mm}'
		. '.alm-sz h3{font-size:13pt;margin:6mm 0 2mm}'
		. '.alm-sz .alm-sz__svg--circles{width:170mm!important;max-width:none!important;margin-left:auto;margin-right:auto}'
		. '.alm-sz .alm-sz__svg--circles-d{display:block!important}'
		. '.alm-sz .alm-sz__svg--circles-m{display:none!important}'
		. '.alm-sz .alm-sz__table td{padding:1.4mm 2mm}'
		. '.alm-sz .alm-sz__table th{padding:2.4mm 2mm}'
		. '.alm-sz *{-webkit-print-color-adjust:exact;print-color-adjust:exact}'
		. '}'
		. '</style>';
}

/** Marks the page around the sheets so that printing shows only them. */
function almora_sg_print_js() {
	return '<script>(function(){function mark(){var el=document.querySelector(".alm-sz");if(!el){return;}var n=el;while(n&&n!==document.body){var p=n.parentElement;if(!p){break;}Array.prototype.forEach.call(p.children,function(s){if(s!==n){s.classList.add("alm-sz-noprint");}});if(n!==el){n.classList.add("alm-sz-keep");}n=p;}}'
		. 'mark();window.addEventListener("beforeprint",mark);})();</script>';
}

add_shortcode( 'almora_size_guide', function () {
	$GLOBALS['almora_sg_need_js'] = true;
	/* sheet 1: the circles (option 2 only) */
	$s1 = '<section class="alm-sz__sheet" id="rings">'
		. '<h2>המדריך למציאת מידת טבעת</h2>'
		. '<p>בוחרים טבעת שיש ברשותכם ומתאימה לכם בגודלה. מניחים אותה על אחד העיגולים, ומוודאים שהעיגול ממלא את החלק הפנימי של הטבעת בדיוק: בתוך החור של הטבעת רואים רק צבע לבן, בלי אפור. המספר שבתוך העיגול הוא המידה. אם יצא בין שתי מידות, בוחרים את הגדולה.</p>'
		. almora_sg_circles_svg( 'd' ) . almora_sg_circles_svg( 'm' )
		. '<p class="alm-sz__note">העיגולים בגודל אמיתי רק כשמדפיסים את הדף על A4 ב-100% (בלי "התאמה לעמוד"). על המסך הם מוקטנים ולכן אי אפשר למדוד עליהם.</p>'
		. '</section>';

	/* sheet 2: table */
	$s2 = '<section class="alm-sz__sheet">'
		. '<h2>טבלה למציאת מידת טבעת</h2>'
		. almora_sg_table_html()
		. '<p class="alm-sz__note">המידה האירופית היא בערך ההיקף הפנימי במ״מ. המידות הזמינות בכל טבעת מופיעות בעמוד שלה.</p>'
		. '</section>';

	/* sheet 3: bracelets */
	$s3 = '<section class="alm-sz__sheet" id="bracelets">'
		. '<h2>המדריך למציאת מידת צמיד</h2>'
		. '<ol class="alm-sz__steps">'
		. '<li>כורכים סרט מדידה רך או חוט סביב פרק היד, במקום שבו הצמיד ישב.</li>'
		. '<li>מודדים בסנטימטרים. זה היקף פרק היד. בחוט מסמנים איפה הקצוות נפגשים ומודדים אותו בסרגל.</li>'
		. '<li>מוסיפים 1 עד 2 ס״מ: 1 למראה צמוד, 2 למראה משוחרר.</li>'
		. '<li>מחפשים את האורך בטבלה.</li>'
		. '</ol>'
		. almora_sg_bracelet_table_html()
		. '<div class="alm-sz__tips"><ul>'
		. '<li>יצא בין שני אורכים? בוחרים את הארוך.</li>'
		. '<li>כשאורך הצמיד ידוע, הוא מופיע בעמוד המוצר או כאפשרות לבחירה.</li>'
		. '<li>בצמידים קשיחים ההתאמה שונה. יש שאלה על צמיד מסוים? כתבו לנו ונבדוק.</li>'
		. '</ul></div>'
		. '</section>';

	return almora_sg_css() . '<div class="alm-sz" dir="rtl">'
		. $s1 . $s2 . $s3 . '</div>';
} );

add_action( 'wp_footer', function () {
	if ( ! empty( $GLOBALS['almora_sg_need_js'] ) ) {
		echo almora_sg_print_js(); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}, 99 );

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

/* product page: a link under the size choice that opens the guide page in a new tab */
add_action( 'woocommerce_after_variations_table', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	$kind = almora_sg_kind( $product->get_id() );
	if ( ! $kind ) {
		return;
	}
	$page = (int) get_option( 'almora_size_guide_page' );
	$url  = $page ? get_permalink( $page ) : home_url( '/size-guide/' );
	echo '<style>.alm-sg-link{display:inline-block;margin:4px 0 14px;color:#202020;font-size:14px;text-decoration:underline;text-underline-offset:3px}</style>';
	echo '<a class="alm-sg-link" href="' . esc_url( $url . '#' . $kind ) . '" target="_blank" rel="noopener">מדריך מידות</a>';
} );
