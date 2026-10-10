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
	// Carousel sections: arrow buttons scroll one card at a time.
	document.querySelectorAll('.axs--carousel').forEach(function (sec) {
		var track = sec.querySelector('.axs-track');
		var btns  = sec.querySelectorAll('.axs-arrow');
		if (!track || !btns.length) { return; }
		var sync = function () {
			var max = track.scrollWidth - track.clientWidth - 2;
			btns[0].disabled = track.scrollLeft <= 2;
			btns[1].disabled = track.scrollLeft >= max;
		};
		btns.forEach(function (b) {
			b.addEventListener('click', function () {
				var card = track.querySelector('.axb-card');
				var step = card ? card.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth * 0.8;
				track.scrollBy({ left: step * parseInt(b.getAttribute('data-dir'), 10), behavior: 'smooth' });
			});
		});
		track.addEventListener('scroll', sync, { passive: true });
		window.addEventListener('resize', sync);
		sync();
	});

	// Article images: click to view full size.
	if (content) {
		var box, boxImg, boxCap, lastFocus;
		var close = function () {
			if (!box) { return; }
			box.classList.remove('is-open');
			document.removeEventListener('keydown', onKey);
			if (lastFocus) { lastFocus.focus(); }
		};
		var onKey = function (e) { if (e.key === 'Escape') { close(); } };
		var open = function (src, alt, cap) {
			if (!box) {
				box = document.createElement('div');
				box.className = 'axa-lightbox';
				box.setAttribute('role', 'dialog');
				box.setAttribute('aria-modal', 'true');
				box.setAttribute('aria-label', 'Image viewer');
				box.innerHTML = '<button type="button" class="axa-lightbox__close" aria-label="Close"><i class="fas fa-times" aria-hidden="true"></i></button><img alt=""><p class="axa-lightbox__cap"></p>';
				document.body.appendChild(box);
				boxImg = box.querySelector('img');
				boxCap = box.querySelector('.axa-lightbox__cap');
				box.addEventListener('click', close);
			}
			lastFocus = document.activeElement;
			boxImg.src = src;
			boxImg.alt = alt || '';
			boxCap.textContent = cap || '';
			box.classList.add('is-open');
			document.addEventListener('keydown', onKey);
			box.querySelector('.axa-lightbox__close').focus();
		};
		var isImageUrl = function (href) { return /\.(jpe?g|png|gif|webp|avif)(\?.*)?$/i.test(href || ''); };

		content.querySelectorAll('img').forEach(function (img) {
			var link = img.closest('a');
			if (link && !isImageUrl(link.getAttribute('href'))) { return; } // links to a page: leave alone
			if (img.closest('.axa-table')) { return; }
			var figure = img.closest('figure');
			var cap    = figure && figure.querySelector('figcaption') ? figure.querySelector('figcaption').textContent.trim() : '';
			var full   = link ? link.getAttribute('href') : (img.currentSrc || img.src);
			var target = link || img;
			if (link) { link.classList.add('axa-zoom'); } else {
				img.classList.add('is-zoomable');
				img.setAttribute('tabindex', '0');
				img.setAttribute('role', 'button');
				img.setAttribute('aria-label', (img.alt ? img.alt + ' — ' : '') + 'view full size');
			}
			target.addEventListener('click', function (e) {
				e.preventDefault();
				// Prefer the largest srcset candidate for a sharp enlargement.
				var src = full;
				if (!link && img.srcset) {
					var best = img.srcset.split(',').map(function (c) { var p = c.trim().split(/\s+/); return { u: p[0], w: parseInt(p[1], 10) || 0 }; })
						.sort(function (a, b) { return b.w - a.w; })[0];
					if (best) { src = best.u; }
				}
				open(src, img.alt, cap);
			});
			if (!link) {
				img.addEventListener('keydown', function (e) {
					if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); img.click(); }
				});
			}
		});
	}
})();
