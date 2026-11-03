<?php
/**
 * Plugin Name: ALMORA size guide
 * Description: Printable ring size guide in the shape of three A4 sheets (sheet 1: print check with a bank card + a cut-out paper ruler, sheet 2: true-size circles to lay a ring on, sheet 3: size table) plus a fourth sheet for bracelets. Shortcode [almora_size_guide] on the size guide page (9477). Printing the page prints only the sheets, one per A4 page, at 100%. On ring and bracelet product pages a "size guide" link opens the page in a new tab.
 * US size to inner diameter: d = 11.63 + 0.8128 x US (mm). EU size is about the inner circumference in mm.
 */
defined( 'ABSPATH' ) || exit;

const ALMORA_SG_RING_CATS     = array( 66, 120, 127 );
const ALMORA_SG_BRACELET_CATS = array( 69, 121, 125 );
const ALMORA_SG_LOGO_ID       = 7740;

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

function almora_sg_logo() {
	$url = wp_get_attachment_image_url( ALMORA_SG_LOGO_ID, 'medium' );
	return $url ? '<img class="alm-sz__logo" src="' . esc_url( $url ) . '" alt="ALMORA">' : '<span class="alm-sz__logo alm-sz__logo--text">ALMORA</span>';
}

function almora_sg_foot() {
	return '<div class="alm-sz__foot"><div><b>מסתבכים? יש לכם שאלה?</b><br>נשמח לעזור בוואטסאפ: <a href="https://wa.me/972523550851" target="_blank" rel="noopener">052-3550851</a></div>' . almora_sg_logo() . '</div>';
}

/** A line exactly as long as the long side of a bank card (85.6 mm), with end marks. */
function almora_sg_card_svg() {
	return '<svg class="alm-sz__svg alm-sz__svg--card" viewBox="0 0 95 9" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="קו באורך כרטיס אשראי">'
		. '<path d="M4.7 4.5H90.3M4.7 1.5V7.5M90.3 1.5V7.5" stroke="#202020" stroke-width=".5" fill="none" stroke-linecap="round"/></svg>';
}

/** The cut-out paper ruler: a slit in the head, scale 0 at the slit, so the loop circumference is read straight off. */
function almora_sg_strip_svg() {
	$x0 = 10; // the slit's centre line is the zero of the scale
	$o  = '<svg class="alm-sz__svg alm-sz__svg--strip" viewBox="0 0 106 22" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="סרגל נייר גזיר למדידת היקף האצבע" font-family="inherit">';
	$o .= '<path d="M.2 1H20V5H90L104 11L90 17H20V21H.2Z" fill="#fff" stroke="#202020" stroke-width=".35" stroke-linejoin="round"/>';
	$o .= '<rect x="' . ( $x0 - 0.8 ) . '" y="4" width="1.6" height="14" fill="#202020"/>';
	$o .= '<text x="' . $x0 . '" y="2.8" font-size="1.9" text-anchor="middle" fill="#202020" direction="rtl">חריץ</text>';
	// mm scale along the top edge
	for ( $l = 44; $l <= 76; $l++ ) {
		$x     = $x0 + $l;
		$major = ( 0 === $l % 4 );
		$o    .= '<path d="M' . $x . ' 5V' . ( $major ? 7.2 : 6.2 ) . '" stroke="#202020" stroke-width="' . ( $major ? '.3' : '.2' ) . '"/>';
		if ( $major ) {
			$o .= '<text x="' . $x . '" y="9.5" font-size="2.3" text-anchor="middle" fill="#202020">' . $l . '</text>';
		}
	}
	// US sizes along the bottom edge
	foreach ( almora_sg_sizes() as $us ) {
		$x     = $x0 + almora_sg_c( $us );
		$whole = ( floor( $us ) == $us );
		$o    .= '<path d="M' . almora_sg_n( $x, 2 ) . ' 17V' . ( $whole ? 14.8 : 15.8 ) . '" stroke="#202020" stroke-width="' . ( $whole ? '.3' : '.2' ) . '"/>';
		if ( $whole ) {
			$o .= '<text x="' . almora_sg_n( $x, 2 ) . '" y="14.1" font-size="2.2" text-anchor="middle" font-weight="700" fill="#202020">' . (int) $us . '</text>';
		}
	}
	$o .= '<text x="52" y="9.5" font-size="2.4" text-anchor="end" fill="#202020" direction="rtl">היקף במ״מ</text>';
	$o .= '<text x="52" y="14.1" font-size="2.4" text-anchor="end" fill="#202020" direction="rtl" font-weight="700">מידת US</text>';
	$o .= '</svg>';
	return $o;
}

/** Grey panel with true-size white circles (inner diameters of every half size) to lay a ring on. */
function almora_sg_circles_svg() {
	$sizes = almora_sg_sizes();
	$cols  = 5;
	$cw    = 34;
	$ch    = 33;
	$w     = $cols * $cw;
	$rows  = (int) ceil( count( $sizes ) / $cols );
	$h     = $rows * $ch + 10;
	$o     = '<svg class="alm-sz__svg alm-sz__svg--circles" viewBox="0 0 ' . $w . ' ' . $h . '" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="עיגולים בגודל אמיתי לכל מידה" font-family="inherit">';
	$o    .= '<rect width="' . $w . '" height="' . $h . '" rx="8" fill="#dedede"/>';
	foreach ( $sizes as $i => $us ) {
		$row = intdiv( $i, $cols );
		$col = $i % $cols;
		$cx  = $w - $cw / 2 - $col * $cw;
		$cy  = 5 + $ch / 2 + $row * $ch;
		$d   = almora_sg_d( $us );
		$o  .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . almora_sg_n( $d / 2, 3 ) . '" fill="#fff"/>';
		$o  .= '<text x="' . $cx . '" y="' . ( $cy - 0.4 ) . '" font-size="4.2" font-weight="700" text-anchor="middle" fill="#202020">' . almora_sg_us_label( $us ) . '</text>';
		$o  .= '<text x="' . $cx . '" y="' . ( $cy + 3.4 ) . '" font-size="2.2" text-anchor="middle" fill="#202020">EU ' . round( almora_sg_c( $us ) ) . '</text>';
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
		. '.alm-sz__bar{display:flex;gap:14px;align-items:center;justify-content:space-between;background:#f5f5f5;border-radius:12px;padding:14px 18px;margin:0 0 20px}'
		. '.alm-sz__bar p{margin:0;font-size:15px}'
		. '.alm-sz .alm-sz__btn{flex:none;min-height:44px;padding:8px 22px;border:0;border-radius:12px;background:#202020;color:#fff;font-size:15px;font-weight:600;cursor:pointer;box-shadow:none;text-transform:none}'
		. '.alm-sz .alm-sz__btn:hover{background:#202020;color:#fff;opacity:.9}'
		. '.alm-sz__sheet{background:#fff;border:1px solid #e6e6e6;border-radius:12px;padding:26px 28px;margin:0 0 24px;box-shadow:0 2px 14px rgba(0,0,0,.05)}'
		. '.alm-sz__top{display:flex;justify-content:flex-end;margin:0 0 6px}'
		. '.alm-sz .alm-sz__logo{height:44px;width:auto;display:block}'
		. '.alm-sz .alm-sz__logo--text{font-size:20px;letter-spacing:.2em;font-weight:600}'
		. '.alm-sz h2{font-size:26px;text-align:center;margin:6px 0 14px}'
		. '.alm-sz h3{font-size:19px;margin:22px 0 8px}'
		. '.alm-sz p{margin:0 0 10px}'
		. '.alm-sz__svg{display:block;margin:10px auto;height:auto}'
		. '.alm-sz__svg--card{width:100%;max-width:380px}'
		. '.alm-sz__svg--strip{width:100%;max-width:520px}'
		. '.alm-sz__svg--circles{width:100%}'
		. '.alm-sz__cap{text-align:center;font-size:14px;margin:0 0 6px}'
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
		. '.alm-sz__foot{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin:22px 0 0;padding-top:12px;border-top:1px solid #e6e6e6;font-size:14px}'
		. '.alm-sz__foot a{color:#202020;font-weight:600;text-decoration:underline}'
		. '.alm-sz__note{font-size:13px;color:#6b6b6b}'
		. '.alm-sg-link{display:inline-flex;align-items:center;gap:6px;margin:4px 0 14px;padding:0;background:none;border:0;color:#202020;font-size:14px;text-decoration:underline;text-underline-offset:3px;cursor:pointer}'
		. '@media(max-width:600px){.alm-sz__bar{flex-direction:column;align-items:stretch;text-align:center}.alm-sz__sheet{padding:18px 14px}.alm-sz h2{font-size:22px}}'
		/* print: only the sheets, one per A4 page, true size */
		. '@media print{'
		. '@page{size:A4;margin:0}'
		. 'html,body{background:#fff!important;margin:0!important;padding:0!important}'
		. '.alm-sz-noprint{display:none!important}'
		. '.alm-sz-keep{margin:0!important;padding:0!important;border:0!important;width:auto!important;max-width:none!important;min-width:0!important;min-height:0!important;height:auto!important;display:block!important;position:static!important;overflow:visible!important;transform:none!important;background:none!important;float:none!important;box-shadow:none!important}'
		. '.alm-sz .alm-sz__bar{display:none!important}'
		. '.alm-sz{line-height:1.55;font-size:10.5pt;max-width:none!important;margin:0!important;width:210mm}'
		. '.alm-sz .alm-sz__sheet{box-sizing:border-box;width:210mm;height:296mm;margin:0!important;border:0;border-radius:0;box-shadow:none;padding:12mm 16mm;overflow:hidden;break-after:page;page-break-after:always;display:flex;flex-direction:column}'
		. '.alm-sz .alm-sz__sheet:last-child{break-after:auto;page-break-after:auto}'
		. '.alm-sz .alm-sz__foot{margin-top:auto}'
		. '.alm-sz .alm-sz__logo{height:15mm}'
		. '.alm-sz h2{font-size:18pt;margin:3mm 0 5mm}'
		. '.alm-sz h3{font-size:13pt;margin:6mm 0 2mm}'
		. '.alm-sz .alm-sz__svg--card{width:95mm!important;max-width:none!important}'
		. '.alm-sz .alm-sz__svg--strip{width:106mm!important;max-width:none!important}'
		. '.alm-sz .alm-sz__svg--circles{width:170mm!important;max-width:none!important;margin-left:auto;margin-right:auto}'
		. '.alm-sz .alm-sz__table td{padding:1.4mm 2mm}'
		. '.alm-sz .alm-sz__table th{padding:2.4mm 2mm}'
		. '.alm-sz *{-webkit-print-color-adjust:exact;print-color-adjust:exact}'
		. '}'
		. '</style>';
}

/** Marks the page around the sheets so that printing shows only them. */
function almora_sg_print_js() {
	return '<script>(function(){function mark(){var el=document.querySelector(".alm-sz");if(!el){return;}var n=el;while(n&&n!==document.body){var p=n.parentElement;if(!p){break;}Array.prototype.forEach.call(p.children,function(s){if(s!==n){s.classList.add("alm-sz-noprint");}});if(n!==el){n.classList.add("alm-sz-keep");}n=p;}}'
		. 'mark();window.addEventListener("beforeprint",mark);var b=document.querySelector("[data-alm-sz-print]");if(b){b.addEventListener("click",function(){mark();window.print();});}})();</script>';
}

add_shortcode( 'almora_size_guide', function () {
	$GLOBALS['almora_sg_need_js'] = true;
	$logo = almora_sg_logo();

	/* sheet 1: print check + option 1 (cut-out ruler) */
	$s1 = '<section class="alm-sz__sheet" id="rings">'
		. '<div class="alm-sz__top">' . $logo . '</div>'
		. '<h2>המדריך למציאת מידת טבעת</h2>'
		. '<p>כדי למצוא את גודל הטבעת המתאים, מדפיסים את המדריך על דף A4 <b>בגודל 100%</b>, בלי "התאמה לעמוד". אפשר לוודא שההדפסה מדויקת: מניחים כרטיס אשראי על הקו למטה. אם אורך הכרטיס שווה לאורך הקו, ההדפסה בגודל הנכון.</p>'
		. almora_sg_card_svg()
		. '<p class="alm-sz__cap">מקמו כאן את כרטיס האשראי, לאורכו</p>'
		. '<h3>אופציה 1: מדידת היקף האצבע עם הסרגל הגזיר</h3>'
		. '<ol class="alm-sz__steps">'
		. '<li>גוזרים את הסרגל לאורך הקו, וגוזרים גם את החריץ השחור.</li>'
		. '<li>מעבירים את הקצה המחודד דרך החריץ, כך שנוצרת לולאה והמספרים כלפי חוץ.</li>'
		. '<li>מושכים את הלולאה סביב בסיס האצבע, עד שהיא צמודה ונוחה, ועוברת את המפרק.</li>'
		. '<li>קוראים את המספר שנמצא בדיוק בחריץ: למעלה היקף האצבע במ״מ, ולמטה מידת ה-US המתאימה.</li>'
		. '</ol>'
		. almora_sg_strip_svg()
		. '<div class="alm-sz__tips"><ul>'
		. '<li>מודדים בסוף היום, כשהידיים חמות. בקור האצבעות קטנות יותר.</li>'
		. '<li>מודדים את האצבע שעליה תענדו את הטבעת, ואפשר למדוד גם את המפרק ולבחור לפי הגדול.</li>'
		. '<li>יצא בין שתי מידות? בוחרים את הגדולה. בטבעת רחבה כדאי מידה אחת גדולה יותר.</li>'
		. '</ul></div>'
		. almora_sg_foot()
		. '</section>';

	/* sheet 2: option 2 (circles) + help */
	$s2 = '<section class="alm-sz__sheet">'
		. '<div class="alm-sz__top">' . $logo . '</div>'
		. '<h2>אופציה 2: מדידת גודל טבעת</h2>'
		. '<p>בוחרים טבעת שיש ברשותכם ומתאימה לכם בגודלה. מניחים אותה על אחד העיגולים, ומוודאים שהעיגול ממלא את החלק הפנימי של הטבעת בדיוק: בתוך החור של הטבעת רואים רק צבע לבן, בלי אפור. המספר שבתוך העיגול הוא המידה. אם יצא בין שתי מידות, בוחרים את הגדולה.</p>'
		. almora_sg_circles_svg()
		. '<p class="alm-sz__note">העיגולים הם בגודל אמיתי רק בדף מודפס ב-100%. על המסך הם מוקטנים ולכן אי אפשר למדוד עליהם.</p>'
		. '<h3>אופציה 3: עזרה אישית</h3>'
		. '<p>לא בטוחים? כתבו לנו בוואטסאפ, אפשר לשלוח צילום של הטבעת על הסרגל, ונעזור לבחור מידה.</p>'
		. almora_sg_foot()
		. '</section>';

	/* sheet 3: table */
	$s3 = '<section class="alm-sz__sheet">'
		. '<div class="alm-sz__top">' . $logo . '</div>'
		. '<h2>טבלה למציאת מידת טבעת</h2>'
		. almora_sg_table_html()
		. '<p class="alm-sz__note">המידה האירופית היא בערך ההיקף הפנימי במ״מ. המידות הזמינות בכל טבעת מופיעות בעמוד שלה.</p>'
		. almora_sg_foot()
		. '</section>';

	/* sheet 4: bracelets */
	$s4 = '<section class="alm-sz__sheet" id="bracelets">'
		. '<div class="alm-sz__top">' . $logo . '</div>'
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
		. almora_sg_foot()
		. '</section>';

	return almora_sg_css() . '<div class="alm-sz" dir="rtl">'
		. '<div class="alm-sz__bar"><p>המדריך בנוי להדפסה על דפי A4. בחלון ההדפסה בוחרים גודל 100% (בלי "התאמה לעמוד").</p><button type="button" class="alm-sz__btn" data-alm-sz-print>הדפסת המדריך</button></div>'
		. $s1 . $s2 . $s3 . $s4 . '</div>';
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
