/* Aurix blog — reading progress, active table-of-contents link, copy link. */
(function () {
	'use strict';

	var content  = document.querySelector('.axa-content');
	var bar      = document.getElementById('axaProgress');
	var pct      = document.getElementById('axaPct');
	var tocLinks = Array.prototype.slice.call(document.querySelectorAll('.axa-toc--side a'));
	var headings = tocLinks.map(function (a) {
		return document.getElementById(decodeURIComponent(a.hash.slice(1)));
	});

	// Reading progress + active TOC entry, updated once per frame on scroll.
	if (content && (bar || tocLinks.length)) {
		var ticking = false;
		var update = function () {
			ticking = false;
			var rect  = content.getBoundingClientRect();
			var total = rect.height - window.innerHeight * 0.6;
			var done  = Math.min(1, Math.max(0, -rect.top / (total > 0 ? total : 1)));
			if (bar) { bar.style.transform = 'scaleX(' + done + ')'; }
			if (pct) { pct.textContent = Math.round(done * 100) + '%'; }

			var active = -1;
			var line = window.innerHeight * 0.3;
			headings.forEach(function (h, i) {
				if (h && h.getBoundingClientRect().top <= line) { active = i; }
			});
			tocLinks.forEach(function (a, i) {
				a.classList.toggle('is-active', i === active);
				if (i === active) { a.setAttribute('aria-current', 'location'); } else { a.removeAttribute('aria-current'); }
			});
		};
		var onScroll = function () {
			if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll);
		update();
	}

	// Close the inline (mobile) TOC after a jump so the heading is visible.
	document.querySelectorAll('.axa-toc--inline a').forEach(function (a) {
		a.addEventListener('click', function () {
			var d = a.closest('details');
			if (d) { d.removeAttribute('open'); }
		});
	});

	// Copy link buttons.
	document.querySelectorAll('.axa-copy').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var url  = btn.getAttribute('data-url');
			var tip  = btn.querySelector('.axa-copy__tip');
			var icon = btn.querySelector('i');
			var done = function () {
				btn.classList.add('is-copied');
				if (icon) { icon.className = 'fas fa-check'; }
				if (tip) { tip.textContent = 'Link copied'; }
				setTimeout(function () {
					btn.classList.remove('is-copied');
					if (icon) { icon.className = 'fas fa-link'; }
					if (tip) { tip.textContent = ''; }
				}, 1800);
			};
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link:', url); });
			} else {
				window.prompt('Copy this link:', url);
			}
		});
	});
})();
