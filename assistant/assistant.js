(function () {
	'use strict';
	if (window.__almoraBot) { return; }
	window.__almoraBot = 1;
	var C = window.ALMORA_BOT || {};
	var root = document.getElementById('almora-bot');
	if (!root) { return; }

	/* Shown to visitors only when the option almora_bot_live is on; before that only with ?almora-bot=1 (remembered in this tab). */
	var preview = false;
	try {
		if (/[?&]almora-bot=1/.test(location.search)) { sessionStorage.setItem('almoraBotPreview', '1'); }
		preview = sessionStorage.getItem('almoraBotPreview') === '1';
	} catch (e) {}
	if (!C.live && !preview) { return; }
	root.hidden = false;

	var KEY = 'almoraBot2';
	var T = {
		name: 'העוזר של ALMORA',
		sub: 'בחירת תכשיט, משלוחים ושאלות',
		add: 'הוספה לסל',
		view: 'לצפייה',
		pickOpts: 'בחרו אפשרויות',
		soldOut: 'השילוב הזה לא זמין',
		wa: 'שיחה עם נציג בוואטסאפ'
	};
	var fab = root.querySelector('.alb-fab');
	var S = load();
	var panel, body, input, typingEl, busy = false, built = false, isOpen = false;
	var varsCache = {};

	function load() {
		try {
			var j = JSON.parse(sessionStorage.getItem(KEY) || 'null');
			if (j && j.log) { return j; }
		} catch (e) {}
		return { log: [], ans: {}, off: 0, seen: [], last: '' };
	}
	function save() {
		try {
			if (S.log.length > 60) { S.log = S.log.slice(-60); }
			sessionStorage.setItem(KEY, JSON.stringify(S));
		} catch (e) {}
	}
	function el(tag, cls, txt) {
		var e = document.createElement(tag);
		if (cls) { e.className = cls; }
		if (txt != null) { e.textContent = txt; }
		return e;
	}
	function icon(name, size) {
		var w = el('span');
		w.style.display = 'flex';
		w.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + ((C.icons || {})[name] || '') + '</svg>';
		return w;
	}
	function ils(n) {
		n = Number(n) || 0;
		var p = (Math.round(n * 100) / 100).toString().split('.');
		p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		return '₪' + p.join('.');
	}

	/* ---------------------------------------------------------------- tracking */
	function track(name, p) {
		p = p || {};
		try { if (window.gtag) { window.gtag('event', 'almora_bot_' + name, p); } } catch (e) {}
		try { (window.dataLayer = window.dataLayer || []).push(Object.assign({ event: 'almora_bot_' + name }, p)); } catch (e) {}
		try {
			if (window.fbq) {
				window.fbq('trackCustom', 'AlmoraBot' + name.replace(/(^|_)(\w)/g, function (m, a, b) { return b.toUpperCase(); }), p);
			}
		} catch (e) {}
	}

	/* ---------------------------------------------------------------- whatsapp */
	var LBL = {
		who: { self: 'לעצמי', gift: 'מתנה' },
		type: { ring: 'טבעת', earrings: 'עגילים', necklace: 'שרשרת', bracelet: 'צמיד', men: 'תכשיט לגברים', any: 'כל סוג' },
		bud: { '300': 'עד 300 ₪', '600': '300–600 ₪', '1000': '600–1,000 ₪', '1000+': 'מעל 1,000 ₪', any: 'כל תקציב' }
	};
	function waUrl() {
		var a = S.ans || {}, L = ['שלום, פונה מהאתר דרך העוזר של ALMORA.'], k = [];
		if (a.who) { k.push(LBL.who[a.who]); }
		if (a.type) { k.push(LBL.type[a.type]); }
		if (a.bud) { k.push(LBL.bud[a.bud]); }
		if (k.length) { L.push('ביקשתי המלצה על: ' + k.join(', ') + '.'); }
		if (S.seen && S.seen.length) {
			L.push('דגמים שראיתי:');
			S.seen.slice(-3).forEach(function (s) { L.push('• ' + s.n + ' ' + s.u); });
		}
		if (S.last) { L.push('מה שרציתי לשאול: ' + S.last); }
		return 'https://wa.me/' + C.wa + '?text=' + encodeURIComponent(L.join('\n'));
	}
	function seen(item) {
		S.seen = (S.seen || []).filter(function (s) { return s.id !== item.id; });
		S.seen.push({ id: item.id, n: item.n, u: item.u });
		save();
	}

	/* ---------------------------------------------------------------- panel */
	function build() {
		if (built) { return; }
		built = true;
		panel = el('section', 'alb-panel');
		panel.id = 'alb-panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-label', T.name);
		panel.tabIndex = -1;
		panel.hidden = true;

		var head = el('div', 'alb-head');
		var ht = el('div', 'alb-head__t');
		ht.appendChild(el('p', 'alb-head__n', T.name));
		ht.appendChild(el('p', 'alb-head__s', T.sub));
		var re = el('button', 'alb-ib');
		re.type = 'button';
		re.setAttribute('aria-label', 'התחלה מחדש');
		re.title = 'התחלה מחדש';
		re.appendChild(icon('restart', 22));
		re.addEventListener('click', function () { track('restart'); start(); });
		var cl = el('button', 'alb-ib');
		cl.type = 'button';
		cl.setAttribute('aria-label', 'סגירה');
		cl.title = 'סגירה';
		cl.appendChild(icon('times', 24));
		cl.addEventListener('click', close);
		head.appendChild(ht);
		head.appendChild(re);
		head.appendChild(cl);

		body = el('div', 'alb-body');
		body.setAttribute('role', 'log');
		body.setAttribute('aria-live', 'polite');

		var foot = el('div', 'alb-foot');
		var form = el('form', 'alb-form');
		input = el('input', 'alb-in');
		input.type = 'text';
		input.maxLength = 200;
		input.placeholder = 'אפשר גם לכתוב כאן…';
		input.setAttribute('aria-label', 'כתבו הודעה');
		input.autocomplete = 'off';
		var send = el('button', 'alb-send');
		send.type = 'submit';
		send.setAttribute('aria-label', 'שליחה');
		send.appendChild(icon('send', 22));
		form.appendChild(input);
		form.appendChild(send);
		form.addEventListener('submit', function (ev) {
			ev.preventDefault();
			var v = input.value.trim();
			if (!v || busy) { return; }
			input.value = '';
			freeText(v);
		});
		var wa = el('a', 'alb-link', T.wa);
		wa.href = waUrl();
		wa.target = '_blank';
		wa.rel = 'noopener';
		['click', 'touchstart', 'focus', 'mouseenter'].forEach(function (n) {
			wa.addEventListener(n, function () { wa.href = waUrl(); });
		});
		wa.addEventListener('click', function () { track('whatsapp', { from: 'footer' }); });
		foot.appendChild(form);
		foot.appendChild(wa);

		panel.appendChild(head);
		panel.appendChild(body);
		panel.appendChild(foot);
		root.appendChild(panel);
	}

	function open() {
		build();
		isOpen = true;
		root.classList.add('is-open');
		panel.hidden = false;
		fab.setAttribute('aria-expanded', 'true');
		fab.setAttribute('aria-label', 'סגירת העוזר');
		if (window.matchMedia && window.matchMedia('(max-width:600px)').matches) {
			document.documentElement.classList.add('alb-lock');
		}
		track('open', { returning: S.log.length ? 1 : 0 });
		if (!S.log.length) {
			start();
		} else {
			body.textContent = '';
			S.log.forEach(function (e) { render(e); });
			body.scrollTop = body.scrollHeight;
		}
		panel.focus({ preventScroll: true });
	}
	function close() {
		if (!isOpen) { return; }
		isOpen = false;
		root.classList.remove('is-open');
		panel.hidden = true;
		fab.setAttribute('aria-expanded', 'false');
		fab.setAttribute('aria-label', T.name);
		document.documentElement.classList.remove('alb-lock');
		fab.focus({ preventScroll: true });
	}
	fab.addEventListener('click', function () { isOpen ? close() : open(); });
	fab.querySelector('.alb-fab__a').appendChild(icon('chat', 26));
	fab.querySelector('.alb-fab__b').appendChild(icon('times', 26));
	document.addEventListener('keydown', function (ev) {
		if (ev.key === 'Escape' && isOpen) { close(); }
	});

	/* ---------------------------------------------------------------- log */
	function clearChips() {
		S.log = S.log.filter(function (e) { return e.t !== 'chips'; });
		if (body) {
			Array.prototype.slice.call(body.querySelectorAll('.alb-c')).forEach(function (n) { n.remove(); });
		}
	}
	function push(e) {
		if (e.t === 'chips' || e.r === 'u') { clearChips(); }
		S.log.push(e);
		save();
		if (built) { render(e); }
	}
	function render(e) {
		if (!body) { return; }
		var n;
		if (e.t === 'text') {
			n = el('p', 'alb-m ' + (e.r === 'u' ? 'alb-m--u' : 'alb-m--b'), e.x);
		} else if (e.t === 'chips') {
			n = el('div', 'alb-c');
			e.o.forEach(function (o) { n.appendChild(chip(e, o)); });
		} else if (e.t === 'prods') {
			n = el('div', 'alb-ps');
			e.items.forEach(function (it) { n.appendChild(card(it)); });
		}
		if (n) { body.appendChild(n); }
	}
	function chip(e, o) {
		var c;
		if (o.h === 'wa' || o.h) {
			c = el('a', 'alb-chip' + (o.pri ? ' alb-chip--pri' : ''));
			c.href = o.h === 'wa' ? waUrl() : o.h;
			if (o.h === 'wa') {
				c.target = '_blank';
				c.rel = 'noopener';
				['click', 'touchstart', 'focus', 'mouseenter'].forEach(function (n) {
					c.addEventListener(n, function () { c.href = waUrl(); });
				});
				c.addEventListener('click', function () { track('whatsapp', { from: 'chip' }); });
			} else {
				c.addEventListener('click', function () { track('link', { to: o.k || '' }); });
			}
		} else {
			c = el('button', 'alb-chip' + (o.pri ? ' alb-chip--pri' : ''));
			c.type = 'button';
			c.addEventListener('click', function () {
				if (busy) { return; }
				push({ r: 'u', t: 'text', x: o.l });
				scrollEnd();
				act(o.k);
			});
		}
		if (o.i) { c.appendChild(icon(o.i, 18)); }
		c.appendChild(document.createTextNode(o.l));
		return c;
	}
	function showTyping() {
		if (!built) { return; }
		hideTyping();
		typingEl = el('div', 'alb-typing');
		typingEl.setAttribute('aria-label', 'מקלידים…');
		typingEl.innerHTML = '<i></i><i></i><i></i>';
		body.appendChild(typingEl);
		body.scrollTop = body.scrollHeight;
	}
	function hideTyping() {
		if (typingEl) { typingEl.remove(); typingEl = null; }
	}
	function scrollEnd() { if (body) { body.scrollTop = body.scrollHeight; } }
		function say(list, delay) {
		busy = true;
		showTyping();
		setTimeout(function () {
			hideTyping();
			list.forEach(push);
			busy = false;
			scrollEnd();
		}, delay == null ? 420 : delay);
	}
	function tx(x) { return { r: 'b', t: 'text', x: x }; }
	function chips(o) { return { r: 'b', t: 'chips', o: o }; }

	/* ---------------------------------------------------------------- conversation */
	function mainChips() {
		return chips([
			{ k: 'm:pick', l: 'עוזרים לי לבחור תכשיט', i: 'sparkle', pri: 1 },
			{ k: 'm:faq', l: 'שאלות נפוצות' },
			{ h: 'wa', l: 'שיחה עם נציג' }
		]);
	}
	function start() {
		S.log = [];
		S.ans = {};
		S.off = 0;
		S.last = '';
		save();
		if (body) { body.textContent = ''; }
		say([
			tx('שלום, כאן העוזר של ALMORA.'),
			tx('אפשר לבחור יחד תכשיט שמתאים בדיוק, או לקבל תשובות על משלוח, תשלום והחזרות.'),
			mainChips()
		]);
	}
	function askWho() {
		say([tx('בשביל מי התכשיט?'), chips([
			{ k: 'who:self', l: 'לעצמי' },
			{ k: 'who:gift', l: 'מתנה', i: 'gift' }
		])]);
	}
	function askType() {
		say([tx('איזה סוג תכשיט מעניין?'), chips([
			{ k: 'type:ring', l: 'טבעת' },
			{ k: 'type:earrings', l: 'עגילים' },
			{ k: 'type:necklace', l: 'שרשרת' },
			{ k: 'type:bracelet', l: 'צמיד' },
			{ k: 'type:men', l: 'תכשיט לגברים' },
			{ k: 'type:any', l: 'הציעו לי' }
		])]);
	}
	function askBudget() {
		say([tx('ומה התקציב בערך?'), chips([
			{ k: 'bud:300', l: 'עד 300 ₪' },
			{ k: 'bud:600', l: '300–600 ₪' },
			{ k: 'bud:1000', l: '600–1,000 ₪' },
			{ k: 'bud:1000+', l: 'מעל 1,000 ₪' },
			{ k: 'bud:any', l: 'לא משנה' }
		])]);
	}
	function faqMenu() {
		say([tx('על מה רוצים לדעת?'), chips([
			{ k: 'faq:ship', l: 'משלוחים' },
			{ k: 'faq:ret', l: 'החלפה והחזרה' },
			{ k: 'faq:pay', l: 'תשלום' },
			{ k: 'faq:mat', l: 'חומרים ואבן מוסונייט' },
			{ k: 'faq:care', l: 'שמירה וניקוי' },
			{ k: 'faq:size', l: 'מידות' },
			{ k: 'faq:contact', l: 'שעות ויצירת קשר' }
		])]);
	}
	var FAQ = {
		ship: 'משלוח עד הבית עולה 35 ₪ ומגיע תוך 3–5 ימי עסקים. ליישובים מרוחקים 5–10 ימי עסקים, באותו מחיר.\nבקנייה מ-1,000 ₪ המשלוח חינם.',
		ret: 'אפשר להחליף או להחזיר עד 14 יום מקבלת המוצר, כשהוא באריזה המקורית ולא היה בשימוש.\nלפני שליחה יוצרים איתנו קשר לאישור.',
		pay: 'משלמים בכרטיס אשראי, בביט, ב-Google Pay או ב-Apple Pay (במכשירי אפל). התשלום מאובטח ומתבצע באתר.',
		mat: 'התכשיטים עשויים מכסף 925, חלקם בציפוי זהב, ומשובצים באבן מוסונייט.\nמוסונייט היא אבן שנוצרת במעבדה, קשיחה במיוחד ובעלת ברק ופיזור אור חזקים, בלי מחיר של יהלום. בעמוד של כל מוצר מצוינת רמת הניקיון של האבן, למשל VVS1.',
		care: 'כדאי להסיר את התכשיט לפני מקלחת, שחייה וספורט, ולנגב בעדינות במטלית ניקוי. בכל הזמנה מצורפת מטלית כזו במתנה.',
		size: 'המידה נבחרת בעמוד המוצר לפני ההוספה לסל. לא בטוחים איזו מידה מתאימה? נציג יעזור בוואטסאפ.',
		contact: 'אפשר לכתוב בוואטסאפ או להתקשר: ' + (C.phone || '052-3550851') + '.\nמענה בין השעות 09:00 ל-20:00.',
		color: 'כל התמונות באתר מקוריות ומשקפות את מה שמתקבל. ייתכנו הבדלים קלים בגוון בגלל הגדרות המסך.'
	};
	function faq(key) {
		track('faq', { topic: key });
		say([tx(FAQ[key]), chips([
			{ k: 'm:faq', l: 'שאלה נוספת' },
			{ k: 'm:pick', l: 'עוזרים לי לבחור תכשיט', i: 'sparkle' },
			{ h: 'wa', l: 'שיחה עם נציג' }
		])]);
	}

	function act(k) {
		var p = k.split(':'), a = S.ans;
		track('step', { step: k });
		switch (p[0]) {
			case 'm':
				if (p[1] === 'pick') { a.who ? askType() : askWho(); }
				else if (p[1] === 'faq') { faqMenu(); }
				break;
			case 'who': a.who = p[1]; save(); askType(); break;
			case 'type': a.type = p[1]; save(); askBudget(); break;
			case 'bud': a.bud = p[1]; a.s = ''; S.off = 0; save(); recommend(); break;
			case 'more': S.off += 3; save(); recommend(); break;
			case 'chbud': askBudget(); break;
			case 'chtype': askType(); break;
			case 'faq': faq(p[1]); break;
		}
	}

	var RANGE = { '300': [0, 300], '600': [300, 600], '1000': [600, 1000], '1000+': [1000, 0], any: [0, 0] };
	function recommend(extra) {
		var a = S.ans, q = [];
		if (a.s) {
			q.push('s=' + encodeURIComponent(a.s));
			if (a.smax) { q.push('max=' + a.smax); }
		} else {
			var r = RANGE[a.bud] || [0, 0];
			if (a.type && a.type !== 'any') { q.push('cat=' + a.type); }
			q.push('min=' + r[0], 'max=' + r[1]);
		}
		q.push('off=' + S.off);
		busy = true;
		showTyping();
		fetch(C.rest + 'products?' + q.join('&'), { credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) { throw 0; } return r.json(); })
			.then(function (d) {
				hideTyping();
				busy = false;
				var items = d.items || [];
				var out = [];
				if (!items.length) {
					out.push(tx(S.off ? 'אלה היו כל הדגמים שמתאימים כרגע.' : (a.s ? 'לא מצאתי דגם לפי החיפוש הזה.' : 'לא מצאתי דגם שמתאים בדיוק לבחירה הזו.')));
					out.push(chips([
						{ k: 'chbud', l: 'לשנות תקציב' },
						{ k: 'chtype', l: 'סוג תכשיט אחר' },
						{ h: 'wa', l: 'שיחה עם נציג' }
					]));
				} else {
					if (!S.off) {
						out.push(tx(a.s ? 'אלה התוצאות שמצאתי:' : (a.who === 'gift' ? 'הנה כמה רעיונות למתנה. בכל הזמנה מצורפת מטלית לניקוי תכשיטים במתנה.' : 'הנה כמה דגמים שמתאימים:')));
					}
					out.push({ r: 'b', t: 'prods', items: items });
					var c = [];
					if (d.more) { c.push({ k: 'more', l: 'עוד המלצות', pri: 1 }); }
					c.push({ k: 'chbud', l: 'לשנות תקציב' }, { k: 'chtype', l: 'סוג תכשיט אחר' }, { h: 'wa', l: 'שיחה עם נציג' });
					out.push(chips(c));
				}
				track('recommend', { type: a.type || '', budget: a.bud || '', who: a.who || '', results: items.length, search: a.s || '' });
				pushAll(out);
			})
			.catch(function () {
				hideTyping();
				busy = false;
				pushAll([tx('משהו השתבש בטעינת הדגמים. אפשר לנסות שוב, או לשוחח עם נציג.'), chips([
					{ k: 'm:pick', l: 'לנסות שוב' },
					{ h: 'wa', l: 'שיחה עם נציג' }
				])]);
			});
	}
	function pushAll(list) {
		list.forEach(push);
		scrollEnd();
	}

	/* ---------------------------------------------------------------- free text */
	/* JS  does not work with Hebrew letters, so word ends are checked with a lookahead */
	var END = "(?![א-ת])";
	var STRONG = [
		["human", new RegExp("נציג|בן אדם|אדם אמיתי|וואטסאפ|וואצאפ|ווטסאפ|whatsapp", "i")],
		["contact", new RegExp("טלפון|שעות|פתוח|כתובת|ליצור קשר|יצירת קשר|להתקשר")],
		["ret", new RegExp("החזר|להחליף|החלפ|ביטול|לבטל")],
		["ship", new RegExp("משלוח|שליח|הגעה|מגיע|אספקה|דמי משלוח")],
		["pay", new RegExp("תשלום|לשלם|אשראי|ביט" + END + "|apple|google|פיי|תשלומים|חשבונית", "i")]
	];
	var SOFT = [
		["mat", new RegExp("מוסונייט|מויסנייט|יהלום|זרקון|925|ציפוי|זהב|חומר|אלרגי|מתכת|ניקל")],
		["care", new RegExp("ניקוי|לנקות|טיפול|להשחיר|משחיר|מקלחת|שמירה")],
		["size", new RegExp("מידה|מידות|גודל")],
		["color", new RegExp("גוון|תמונות|תמונה")]
	];
	var NOUN = new RegExp("(?:^|[^א-ת])[הולבכמש]?(?:טבעת|טבעות|עגילים|עגיל|שרשרת|שרשראות|צמיד|צמידים|תליון|תליונים|סט|סטים|תכשיט|תכשיטים)" + END);
	var GIFT = new RegExp("מתנה|אירוסין|חתונה|יום הולדת|יום נישואין|יום נישואים");
	var HELLO = new RegExp("^(?:שלום|היי|הי|הלו|בוקר טוב|ערב טוב|צהריים טובים|אהלן)" + END);
	var THANKS = new RegExp("^(?:תודה|תודה רבה|מעולה|אחלה|סבבה|יופי|מושלם|ביי|להתראות)" + END);
	function firstKey(list, v) {
		for (var i = 0; i < list.length; i++) { if (list[i][1].test(v)) { return list[i][0]; } }
		return "";
	}
	function notUnderstood() {
		track("unknown", { len: S.last.length });
		say([tx("לא הצלחתי להבין מה ביקשתם. למעבר לנציג אנושי אפשר להמשיך בוואטסאפ, מתאים?"), chips([
			{ h: "wa", l: "המשך בוואטסאפ", pri: 1 },
			{ k: "m:pick", l: "עוזרים לי לבחור תכשיט", i: "sparkle" },
			{ k: "m:faq", l: "שאלות נפוצות" }
		])]);
	}
	function freeText(v) {
		S.last = v;
		save();
		push({ r: "u", t: "text", x: v });
		scrollEnd();
		track("text", { len: v.length });
		var key = firstKey(STRONG, v);
		if (key === "human") {
			say([tx("בשמחה. כאן אפשר להמשיך בוואטסאפ, ההודעה כבר מוכנה עם סיכום השיחה."), chips([{ h: "wa", l: "המשך בוואטסאפ", pri: 1 }, mainChips().o[0]])]);
			return;
		}
		if (key) { faq(key); return; }
		if (NOUN.test(v)) {
			var m = v.match(/(?:עד|מתחת ל|לא יותר מ)s*₪?s*(d{2,5})/);
			S.ans.s = v.replace(/(?:עד|מתחת ל|לא יותר מ)s*₪?s*d{2,5}s*(?:₪|שקל|שקלים|ש"ח)?/, "").replace(/s+/g, " ").trim() || v;
			S.ans.smax = m ? m[1] : "";
			S.off = 0;
			save();
			recommend();
			return;
		}
		key = firstKey(SOFT, v);
		if (key) { faq(key); return; }
		if (GIFT.test(v)) {
			S.ans.who = "gift";
			save();
			say([tx("איזה כיף, נבחר יחד מתנה."), tx("איזה סוג תכשיט מעניין?"), chips([
				{ k: "type:ring", l: "טבעת" },
				{ k: "type:earrings", l: "עגילים" },
				{ k: "type:necklace", l: "שרשרת" },
				{ k: "type:bracelet", l: "צמיד" },
				{ k: "type:men", l: "תכשיט לגברים" },
				{ k: "type:any", l: "הציעו לי" }
			])]);
			return;
		}
		if (HELLO.test(v) && v.length < 25) {
			say([tx("שלום, שמחים לעזור."), mainChips()]);
			return;
		}
		if (THANKS.test(v) && v.length < 30) {
			say([tx("בשמחה. אם צריך עוד משהו, אני כאן."), mainChips()]);
			return;
		}
		notUnderstood();
	}

	/* ---------------------------------------------------------------- product card */
	function setPrice(node, price, reg, from) {
		node.textContent = '';
		node.classList.toggle('is-sale', !!reg);
		node.appendChild(document.createTextNode((from ? 'החל מ-' : '') + ils(price)));
		if (reg) { node.appendChild(el('s', '', ils(reg))); }
	}
	function card(item) {
		var c = el('div', 'alb-p');
		var im = el('img', 'alb-p__img');
		im.src = item.i;
		im.alt = '';
		im.width = 88;
		im.height = 88;
		im.loading = 'lazy';
		var main = el('div', 'alb-p__main');
		var nm = el('a', 'alb-p__name', item.n);
		nm.href = item.u;
		var pr = el('div', 'alb-p__price');
		setPrice(pr, item.p, item.rp, item.f);
		var act = el('div', 'alb-p__act');
		var vw = el('a', 'alb-btn', T.view);
		vw.href = item.u;
		[nm, vw].forEach(function (a) {
			a.addEventListener('click', function () {
				seen(item);
				track('product_click', { item_id: item.id, item_name: item.n });
			});
		});
		var ad = el('button', 'alb-btn alb-btn--pri');
		ad.type = 'button';
		ad.appendChild(icon('bag', 18));
		var adl = document.createTextNode(T.add);
		ad.appendChild(adl);
		act.appendChild(ad);
		act.appendChild(vw);
		main.appendChild(nm);
		main.appendChild(pr);
		main.appendChild(act);
		c.appendChild(im);
		c.appendChild(main);
		var err = el('p', 'alb-err');
		err.hidden = true;
		err.setAttribute('role', 'alert');
		c.appendChild(err);

		var ctx = { item: item, card: c, pr: pr, ad: ad, adl: adl, err: err, vid: 0, sel: {}, V: null, box: null };
		ad.addEventListener('click', function () {
			err.hidden = true;
			if (item.v && !ctx.vid) {
				toggleOptions(ctx);
				return;
			}
			addToCart(ctx);
		});
		return c;
	}

	/* variable products: the options are chosen inside the card */
	function getVars(id) {
		if (!varsCache[id]) {
			varsCache[id] = fetch(C.rest + 'variations?id=' + id, { credentials: 'same-origin' }).then(function (r) {
				if (!r.ok) { throw 0; }
				return r.json();
			});
		}
		return varsCache[id];
	}
	function fits(v, sel) {
		for (var k in sel) {
			if (sel[k] && v.a[k] !== undefined && v.a[k] !== '' && v.a[k] !== sel[k]) { return false; }
		}
		return true;
	}
	function toggleOptions(ctx) {
		if (ctx.box) {
			ctx.box.hidden = !ctx.box.hidden;
			return;
		}
		ctx.box = el('div', 'alb-o');
		ctx.box.textContent = 'טוענים אפשרויות…';
		ctx.card.appendChild(ctx.box);
		ctx.adl.nodeValue = T.pickOpts;
		ctx.ad.disabled = true;
		getVars(ctx.item.id).then(function (V) {
			ctx.V = V;
			ctx.box.textContent = '';
			ctx.groups = {};
			(V.attrs || []).forEach(function (at) {
				var g = el('div', 'alb-o__g');
				g.appendChild(el('div', 'alb-o__l', at.l));
				var row = el('div', 'alb-o__r');
				row.setAttribute('role', 'group');
				row.setAttribute('aria-label', at.l);
				at.o.forEach(function (o) {
					var b = el('button', 'alb-opt', o.l);
					b.type = 'button';
					b.setAttribute('aria-pressed', 'false');
					b.addEventListener('click', function () {
						ctx.sel[at.k] = ctx.sel[at.k] === o.v ? '' : o.v;
						refresh(ctx);
					});
					b._v = o.v;
					row.appendChild(b);
				});
				g.appendChild(row);
				ctx.box.appendChild(g);
				ctx.groups[at.k] = row;
			});
			refresh(ctx);
		}).catch(function () {
			ctx.box.textContent = '';
			showErr(ctx, 'לא הצלחנו לטעון את האפשרויות. אפשר לפתוח את עמוד המוצר.');
		});
	}
	function refresh(ctx) {
		var V = ctx.V, list = V.list || [], changed;
		// options that lead nowhere are disabled; a lone remaining choice is picked for the visitor
		do {
			changed = false;
			V.attrs.forEach(function (at) {
				Array.prototype.forEach.call(ctx.groups[at.k].children, function (b) {
					var t = {};
					for (var k in ctx.sel) { t[k] = ctx.sel[k]; }
					t[at.k] = b._v;
					var ok = list.some(function (v) { return v.in && fits(v, t); });
					b.disabled = !ok;
					if (!ok && ctx.sel[at.k] === b._v) { ctx.sel[at.k] = ''; changed = true; }
				});
			});
			V.attrs.forEach(function (at) {
				if (ctx.sel[at.k]) { return; }
				var free = Array.prototype.filter.call(ctx.groups[at.k].children, function (b) { return !b.disabled; });
				if (free.length === 1) { ctx.sel[at.k] = free[0]._v; changed = true; }
			});
		} while (changed);
		V.attrs.forEach(function (at) {
			Array.prototype.forEach.call(ctx.groups[at.k].children, function (b) {
				b.setAttribute('aria-pressed', ctx.sel[at.k] === b._v ? 'true' : 'false');
			});
		});
		var done = V.attrs.every(function (at) { return ctx.sel[at.k]; });
		var cur = null;
		if (done) {
			cur = list.filter(function (v) { return v.in && fits(v, ctx.sel); })[0] || null;
		}
		ctx.vid = cur ? cur.id : 0;
		ctx.ad.disabled = !cur;
		ctx.adl.nodeValue = cur ? T.add : (done ? T.soldOut : T.pickOpts);
		if (cur) { setPrice(ctx.pr, cur.p, cur.rp, false); }
		else { setPrice(ctx.pr, ctx.item.p, ctx.item.rp, ctx.item.f); }
	}
	function showErr(ctx, m) {
		ctx.err.textContent = m;
		ctx.err.hidden = false;
	}

	function addToCart(ctx) {
		var item = ctx.item;
		ctx.ad.disabled = true;
		var prev = ctx.adl.nodeValue;
		ctx.adl.nodeValue = 'מוסיפים…';
		var form = new URLSearchParams();
		form.append('product_id', item.id);
		form.append('variation_id', ctx.vid || 0);
		form.append('sel', JSON.stringify(ctx.sel || {}));
		fetch(C.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: form.toString() })
			.then(function (r) { return r.json(); })
			.then(function (d) {
				ctx.ad.disabled = false;
				ctx.adl.nodeValue = prev;
				if (!d || !d.ok) {
					showErr(ctx, (d && d.m) || 'לא הצלחנו להוסיף לסל.');
					return;
				}
				try {
					if (window.jQuery) {
						var $b = window.jQuery('<a class="ajax_add_to_cart add_to_cart_button"></a>').attr({ 'data-product_id': item.id, 'data-quantity': 1 });
						window.jQuery(document.body).trigger('added_to_cart', [d.frag, d.hash, $b]);
					}
				} catch (e) {}
				track('add_to_cart', { item_id: item.id, item_name: item.n, value: ctx.vid ? 0 : item.p });
				var left = (C.free || 1000) - (d.sub || 0);
				pushAll([
					tx('נוסף לסל: ' + item.n + '.\n' + (left > 0 ? 'עוד ' + ils(Math.ceil(left)) + ' למשלוח חינם.' : 'המשלוח בהזמנה הזו חינם.')),
					chips([
						{ h: C.checkout, k: 'checkout', l: 'לתשלום', pri: 1 },
						{ h: C.cart, k: 'cart', l: 'לצפייה בעגלה' },
						{ k: 'chtype', l: 'להמשיך לחפש' }
					])
				]);
			})
			.catch(function () {
				ctx.ad.disabled = false;
				ctx.adl.nodeValue = prev;
				showErr(ctx, 'לא הצלחנו להוסיף לסל. אפשר לנסות שוב או לפתוח את עמוד המוצר.');
			});
	}
})();
