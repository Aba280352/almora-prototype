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

/**
 * Under the circles: tells the visitor that on screen the circles are small and where to get the true-size PDF.
 * The PDF is a media-library attachment whose ID is in option almora_size_guide_pdf (source: size-guide/pdf-src/ring-sizes.html).
 */
function almora_sg_pdf_box() {
	$id  = (int) get_option( 'almora_size_guide_pdf' );
	$url = $id ? wp_get_attachment_url( $id ) : '';
	if ( ! $url ) {
		return '<p class="alm-sz__note">העיגולים בגודל אמיתי רק כשמדפיסים את הדף על A4 ב-100% (בלי "התאמה לעמוד"). על המסך הם מוקטנים ולכן אי אפשר למדוד עליהם.</p>';
	}
	$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path transform="translate(4.5 2.5)" d="M15.854 6.146L9.854 0.146C9.76 0.052 9.633 0 9.5 0L3.5 0C1.21 0 0 1.21 0 3.5L0 15.5C0 17.79 1.21 19 3.5 19L12.5 19C14.79 19 16 17.79 16 15.5L16 6.5C16 6.367 15.947 6.24 15.854 6.146ZM10 1.707L14.293 6L12.5 6C10.771 6 10 5.229 10 3.5L10 1.707ZM12.5 18L3.5 18C1.771 18 1 17.229 1 15.5L1 3.5C1 1.771 1.771 1 3.5 1L9 1L9 3.5C9 5.79 10.21 7 12.5 7L15 7L15 15.5C15 17.229 14.229 18 12.5 18ZM8.854 12.146C9.049 12.341 9.049 12.658 8.854 12.853L6.854 14.853C6.808 14.899 6.752 14.936 6.691 14.961C6.63 14.986 6.565 15 6.5 15C6.435 15 6.37 14.987 6.309 14.961C6.247 14.936 6.192 14.899 6.146 14.853L4.146 12.853C3.951 12.658 3.951 12.341 4.146 12.146C4.341 11.951 4.658 11.951 4.853 12.146L5.999 13.292L5.999 9.5C5.999 9.224 6.223 9 6.499 9C6.775 9 6.999 9.224 6.999 9.5L6.999 13.293L8.145 12.147C8.342 11.951 8.658 11.951 8.854 12.146Z"/></svg>';
	return '<div class="alm-sz__pdf">'
		. '<p class="alm-sz__pdf-t"><b>העיגולים על המסך מוקטנים.</b> כדי לקבל אותם בגודל האמיתי, מורידים את הקובץ ומדפיסים אותו על דף A4 ב-100% (בלי "התאמה לעמוד").</p>'
		. '<a class="alm-sz__pdf-btn" href="' . esc_url( $url ) . '" download target="_blank" rel="noopener">' . $icon . '<span>הורדת PDF להדפסה בגודל אמיתי</span></a>'
		. '</div>';
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
		. '.alm-sz__pdf{text-align:center;background:#f5f5f5;border-radius:12px;padding:16px 18px;margin:14px 0 4px}'
		. '.alm-sz__pdf-t{margin:0 0 12px;font-size:15px}'
		. '.alm-sz a.alm-sz__pdf-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:10px 26px;border-radius:12px;background:#202020;color:#fff!important;font-size:16px;font-weight:600;text-decoration:none!important;box-shadow:none}'
		. '.alm-sz a.alm-sz__pdf-btn:hover{background:#202020;color:#fff!important;opacity:.9}'
		. '.alm-sz__pdf-btn svg{flex:none;width:22px;height:22px}'
		. '.alm-sg-link{display:inline-flex;align-items:center;gap:6px;margin:4px 0 14px;padding:0;background:none;border:0;color:#202020;font-size:14px;text-decoration:underline;text-underline-offset:3px;cursor:pointer}'
		. '@media(max-width:600px){.alm-sz__sheet{padding:18px 14px}.alm-sz h2{font-size:22px}.alm-sz__pdf{padding:14px 12px}.alm-sz__pdf-t{font-size:14px}.alm-sz a.alm-sz__pdf-btn{display:flex;width:100%;min-height:44px;padding:8px 10px;gap:6px;font-size:14px;white-space:nowrap}.alm-sz__pdf-btn svg{width:20px;height:20px}}'
		/* print: only the sheets, one per A4 page, true size */
		. '@media print{'
		. '@page{size:A4;margin:0}'
		. 'html,body{background:#fff!important;margin:0!important;padding:0!important}'
		. '.alm-sz-noprint{display:none!important}'
		. '.alm-sz .alm-sz__pdf{display:none!important}'
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
		. almora_sg_pdf_box()
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
