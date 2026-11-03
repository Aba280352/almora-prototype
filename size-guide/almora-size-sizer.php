<?php
/**
 * Plugin Name: ALMORA ring sizer (on screen)
 * Description: A live ring measure inside the size guide: calibrate the screen with a bank card, put a ring on the screen and match the circle to its inner edge, get the US size. Second tab: type a measurement in mm. Printed at the top of the rings part of the size guide (page and product popup) through almora_sg_tool_html().
 */
defined( 'ABSPATH' ) || exit;

function almora_sg_tool_html() {
	$GLOBALS['almora_sg_need_js'] = true;
	return almora_sg_tool_css()
		. '<details class="alm-sizer-fold"><summary>אין סרגל? מדידה על המסך</summary>'
		. '<div class="alm-sizer" data-alm-sizer>'
		. '<p class="alm-sizer__lead">שמים טבעת שמתאימה לכם על המסך, ורואים מיד איזו מידה היא. בלי חישובים.</p>'
		. '<div class="alm-sizer__tabs" role="group" aria-label="שיטת מדידה">'
		. '<button type="button" class="is-on" data-sz-tab="screen" aria-pressed="true">מודדים על המסך</button>'
		. '<button type="button" data-sz-tab="mm" aria-pressed="false">יש לכם מידה במ"מ</button>'
		. '</div>'

		. '<div data-sz-pane="screen">'
		. '<div data-sz-step="1">'
		. '<p class="alm-sizer__step"><b>שלב 1, כיול המסך.</b> מניחים כרטיס אשראי זקוף על המסך, כשהקצה העליון שלו על הקו העליון. מזיזים את המחוון עד שהקצה התחתון של הכרטיס נוגע בקו התחתון.</p>'
		. '<input type="range" class="alm-sizer__range" min="170" max="620" step="1" value="470" data-sz-card-range aria-label="המרחק בין שני הקווים">'
		. '<div class="alm-sizer__stage alm-sizer__stage--lines" data-sz-stage1><div class="alm-sizer__line alm-sizer__line--top"></div><div class="alm-sizer__line alm-sizer__line--bottom" data-sz-line></div></div>'
		. '<button type="button" class="alm-sizer__btn" data-sz-next>הכרטיס מתאים, ממשיכים</button>'
		. '</div>'
		. '<div data-sz-step="2" hidden>'
		. '<p class="alm-sizer__step"><b>שלב 2, מודדים את הטבעת.</b> מניחים טבעת שמתאימה לכם על העיגול, ומזיזים את המחוון עד שהעיגול נוגע בקצה הפנימי של הטבעת מכל הצדדים.</p>'
		. '<div class="alm-sizer__stage alm-sizer__stage--ring" data-sz-stage2><div class="alm-sizer__circle" data-sz-circle></div></div>'
		. '<input type="range" class="alm-sizer__range" min="14" max="25" step="0.1" value="17.3" data-sz-ring-range aria-label="קוטר העיגול">'
		. '<p class="alm-sizer__res" data-sz-res aria-live="polite"></p>'
		. '<button type="button" class="alm-sizer__link" data-sz-recal>לכייל מחדש</button>'
		. '</div>'
		. '</div>'

		. '<div data-sz-pane="mm" hidden>'
		. '<p class="alm-sizer__step">מדדתם בסרגל או בפס נייר? בוחרים מה מדדתם, מקלידים את המספר, והמידה מופיעה.</p>'
		. '<div class="alm-sizer__row">'
		. '<select class="alm-sizer__field" data-sz-kind aria-label="מה מדדתם"><option value="c">היקף האצבע (מ"מ)</option><option value="d">קוטר פנימי של טבעת (מ"מ)</option></select>'
		. '<input type="text" class="alm-sizer__field" inputmode="decimal" placeholder="לדוגמה 54" data-sz-in aria-label="המספר במילימטרים">'
		. '</div>'
		. '<p class="alm-sizer__res" data-sz-mm-res aria-live="polite"></p>'
		. '</div>'

		. '<p class="alm-sizer__note">המדידה על המסך מקורבת. אם יצא קרוב לגבול בין שתי מידות, בוחרים את הגדולה, ואפשר לאמת מול הטבלה למעלה.</p>'
		. '</div></details>';
}

function almora_sg_tool_css() {
	static $done = false;
	if ( $done ) {
		return '';
	}
	$done = true;
	return '<style id="almora-sizer">'
		. '.alm-sizer-fold{margin:18px 0 0;background:#f5f5f5;border-radius:12px;color:#202020}'
		. '.alm-sizer-fold>summary{list-style:none;cursor:pointer;padding:14px 16px;font-weight:600;display:flex;justify-content:space-between;align-items:center}'
		. '.alm-sizer-fold>summary::-webkit-details-marker{display:none}'
		. '.alm-sizer-fold>summary::after{content:"+";font-size:22px;line-height:1}'
		. '.alm-sizer-fold[open]>summary::after{content:"−"}'
		. '.alm-sizer{padding:0 16px 16px;color:#202020}'
		. '.alm-sizer .alm-sizer__title{font-size:20px;margin:0 0 4px}'
		. '.alm-sizer p{margin:0 0 12px}'
		. '.alm-sizer .alm-sizer__tabs{display:flex;gap:8px;margin:0 0 14px}'
		. '.alm-sizer .alm-sizer__tabs button{flex:1;min-height:44px;padding:8px 10px;border:1px solid #e6e6e6;border-radius:12px;background:#fff;color:#202020;font-size:14px;font-weight:600;cursor:pointer;box-shadow:none;text-transform:none}'
		. '.alm-sizer .alm-sizer__tabs button.is-on,.alm-sizer .alm-sizer__tabs button.is-on:hover{background:#202020;border-color:#202020;color:#fff}'
		. '.alm-sizer .alm-sizer__tabs button:hover{background:#fff;border-color:#202020;color:#202020}'
		. '.alm-sizer .alm-sizer__stage{display:flex;justify-content:center;align-items:flex-start;padding:12px;background:#fff;border-radius:12px;overflow:hidden;margin:0 0 12px}'
		. '.alm-sizer .alm-sizer__stage--ring{align-items:center}'
		. '.alm-sizer .alm-sizer__stage--lines{display:block;position:relative;padding:0}'
		. '.alm-sizer .alm-sizer__line{position:absolute;left:0;right:0;height:3px;background:#202020}'
		. '.alm-sizer .alm-sizer__line--top{top:8px}'
		. '.alm-sizer .alm-sizer__circle{box-sizing:border-box;border:3px solid #202020;border-radius:50%;background:rgba(32,32,32,.05);flex:none}'
		. '.alm-sizer .alm-sizer__range{width:100%;margin:0 0 12px;accent-color:#202020;height:32px}'
		. '.alm-sizer .alm-sizer__btn{display:block;width:100%;min-height:46px;padding:10px 16px;border:0;border-radius:12px;background:#202020;color:#fff;font-size:15px;font-weight:600;cursor:pointer;box-shadow:none;text-transform:none}'
		. '.alm-sizer .alm-sizer__btn:hover{background:#202020;color:#fff;opacity:.9}'
		. '.alm-sizer .alm-sizer__link{background:none;border:0;padding:4px 0;color:#202020;font-size:14px;text-decoration:underline;text-underline-offset:3px;cursor:pointer;box-shadow:none;text-transform:none}'
		. '.alm-sizer .alm-sizer__link:hover{background:none;color:#202020}'
		. '.alm-sizer .alm-sizer__res{background:#fff;border-radius:12px;padding:12px 14px;font-size:16px;min-height:48px;margin:0 0 8px}'
		. '.alm-sizer .alm-sizer__res b{font-size:20px}'
		. '.alm-sizer .alm-sizer__row{display:flex;gap:8px;margin:0 0 12px}'
		. '.alm-sizer .alm-sizer__field{flex:1;min-width:0;min-height:46px;padding:8px 12px;border:1px solid #e6e6e6;border-radius:12px;background:#fff;color:#202020;font-size:16px}'
				. '.alm-sizer .alm-sizer__note{font-size:13px;color:#6b6b6b;margin:8px 0 0}'
		. '@media(max-width:600px){.alm-sizer{padding:14px}.alm-sg-dialog{padding:28px 12px 16px!important}.alm-sizer .alm-sizer__row{flex-direction:column}}'
		. '</style>';
}

/* the script, printed once in the footer when a sizer was rendered */
add_action( 'wp_footer', function () {
	if ( empty( $GLOBALS['almora_sg_need_js'] ) ) {
		return;
	}
	?>
<script>
(function () {
	var KEY = 'almora_sizer_ppm', CARD_H = 85.6;
	function usOf(d) { return (d - 11.63) / 0.8128; }
	function fmt(n) { return (Math.round(n * 10) / 10).toFixed(1); }
	function loadPpm() {
		try {
			var v = JSON.parse(localStorage.getItem(KEY) || 'null');
			if (v && v.ppm > 1.5 && v.ppm < 9 && Math.abs((v.dpr || 1) - (window.devicePixelRatio || 1)) < 0.01) { return v.ppm; }
		} catch (e) {}
		return 0;
	}
	function savePpm(p) {
		try { localStorage.setItem(KEY, JSON.stringify({ ppm: p, dpr: window.devicePixelRatio || 1 })); } catch (e) {}
	}
	/* from an exact US size (can be fractional) to the sentence shown to the visitor */
	function advice(us, dmm) {
		var f = Math.floor(us + 1e-6), frac = us - f, n, txt;
		if (frac < 0.2) { n = f; } else { n = f + 1; }
		var head = 'קוטר פנימי ' + fmt(dmm) + ' מ"מ. ';
		if (n < 5 || n > 14) {
			return head + 'זה מחוץ לטווח המידות באתר (5–14). כתבו לנו בוואטסאפ ונבדוק מה אפשר לעשות.';
		}
		if (frac >= 0.2 && frac <= 0.8) {
			txt = 'יצא בין ' + f + ' ל-' + (f + 1) + '. כדאי לבחור את <b>' + n + '</b>.';
		} else {
			txt = 'המידה המתאימה: <b>US ' + n + '</b>.';
		}
		return head + txt;
	}
	function init(root) {
		var $ = function (s) { return root.querySelector(s); };
		var $$ = function (s) { return root.querySelectorAll(s); };
		var tabs = $$('[data-sz-tab]'), panes = $$('[data-sz-pane]');
		Array.prototype.forEach.call(tabs, function (b) {
			b.addEventListener('click', function () {
				Array.prototype.forEach.call(tabs, function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
				Array.prototype.forEach.call(panes, function (p) { p.hidden = p.getAttribute('data-sz-pane') !== b.getAttribute('data-sz-tab'); });
			});
		});

		var step1 = $('[data-sz-step="1"]'), step2 = $('[data-sz-step="2"]');
		var line = $('[data-sz-line]'), cr = $('[data-sz-card-range]'), stage1 = $('[data-sz-stage1]');
		var circle = $('[data-sz-circle]'), rr = $('[data-sz-ring-range]'), stage2 = $('[data-sz-stage2]'), res = $('[data-sz-res]');
		var ppm = loadPpm();

		cr.value = ppm ? Math.round(ppm * CARD_H) : (window.innerWidth < 700 ? 470 : 324);
		/* the card stands upright: its long side (85.6 mm) runs between the two lines */
		function drawCard() {
			var h = +cr.value;
			line.style.top = (8 + h) + 'px';
			stage1.style.height = (h + 19) + 'px';
		}
		function drawRing() {
			var d = +rr.value;
			circle.style.width = circle.style.height = (d * ppm) + 'px';
			stage2.style.minHeight = (25 * ppm + 24) + 'px';
			res.innerHTML = advice(usOf(d), d);
		}
		cr.addEventListener('input', drawCard);
		$('[data-sz-next]').addEventListener('click', function () {
			ppm = (+cr.value) / CARD_H;
			savePpm(ppm);
			step1.hidden = true; step2.hidden = false;
			drawRing();
		});
		rr.addEventListener('input', drawRing);
		$('[data-sz-recal]').addEventListener('click', function () {
			cr.value = Math.round(ppm * CARD_H);
			step2.hidden = true; step1.hidden = false;
			drawCard();
		});
		if (ppm) { step1.hidden = true; step2.hidden = false; drawRing(); } else { drawCard(); }
		
		/* the mm tab */
		var inp = $('[data-sz-in]'), kind = $('[data-sz-kind]'), out = $('[data-sz-mm-res]');
		function calc() {
			var v = parseFloat(String(inp.value).replace(',', '.'));
			if (!(v > 0)) { out.innerHTML = ''; return; }
			var d = kind.value === 'c' ? v / Math.PI : v;
			if (d < 10 || d > 35) { out.textContent = 'המספר נראה לא סביר. מודדים במילימטרים, לדוגמה היקף אצבע בערך 54.'; return; }
			out.innerHTML = advice(usOf(d), d);
		}
		inp.addEventListener('input', calc);
		kind.addEventListener('change', calc);
	}
	function run() { Array.prototype.forEach.call(document.querySelectorAll('[data-alm-sizer]'), init); }
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', run); } else { run(); }
})();
</script>
	<?php
}, 99 );
