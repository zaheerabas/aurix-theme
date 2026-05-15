/**
 * Aurix International — aurix-main.js v4.0
 * Author: ZaheerAbbas
 * Fixes: icon alignment, mobile menu right, mobile cart→page, improved live search, account hero
 */
(function ($) {
  'use strict';

  var body          = document.body;
  var overlay       = document.getElementById('aurix-overlay');
  var catPanel      = document.getElementById('aurix-cat-panel');
  var cartPanel     = document.getElementById('aurix-cart-panel');
  var pagesPanel    = document.getElementById('aurix-pages-panel');
  var searchOverlay = document.getElementById('aurix-search-overlay');
  var activePanel   = null;

  /* ════════ SCROLL ════════ */
  var ticking = false;
  function onScroll() {
    if (!ticking) {
      requestAnimationFrame(function () {
        body.classList.toggle('scrolled', window.scrollY > 80);
        ticking = false;
      });
      ticking = true;
    }
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ════════ PANELS ════════ */
  function openPanel(panel) {
    if (!panel) return;
    if (activePanel && activePanel !== panel) closeAllPanels();
    panel.classList.add('open');
    panel.setAttribute('aria-hidden','false');
    if (overlay) overlay.classList.add('open');
    body.style.overflow = 'hidden';
    activePanel = panel;
    var catBtn = document.getElementById('desktopCatBtn');
    if (panel === catPanel && catBtn) { catBtn.classList.add('open'); catBtn.setAttribute('aria-expanded','true'); }
  }

  function closeAllPanels() {
    [catPanel, cartPanel, pagesPanel].forEach(function(p) {
      if (p) { p.classList.remove('open'); p.setAttribute('aria-hidden','true'); }
    });
    if (overlay) overlay.classList.remove('open');
    body.style.overflow = '';
    activePanel = null;
    var catBtn = document.getElementById('desktopCatBtn');
    if (catBtn) { catBtn.classList.remove('open'); catBtn.setAttribute('aria-expanded','false'); }
  }

  if (overlay) overlay.addEventListener('click', closeAllPanels);
  ['catPanelClose','pagesPanelClose','cartPanelClose'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', closeAllPanels);
  });

  // [data-panel] triggers — desktop cart button only (mobile cart goes to page)
  document.addEventListener('click', function(e) {
    var trigger = e.target.closest('[data-panel]');
    if (trigger) {
      e.preventDefault();
      var target = document.getElementById(trigger.getAttribute('data-panel'));
      if (target) { activePanel === target ? closeAllPanels() : openPanel(target); }
    }
  });

  // Desktop category button
  var desktopCatBtn = document.getElementById('desktopCatBtn');
  if (desktopCatBtn) {
    desktopCatBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      activePanel === catPanel ? closeAllPanels() : openPanel(catPanel);
    });
  }

  // Mobile TOP bar hamburger → opens CATEGORIES panel (LEFT slide)
  var mobMenuBtn = document.getElementById('mobMenuBtn');
  var mobCatBtn  = document.getElementById('mobCatBtn');
  if (mobMenuBtn) mobMenuBtn.addEventListener('click', function() { openPanel(catPanel); });
  if (mobCatBtn)  mobCatBtn.addEventListener('click',  function() { openPanel(catPanel); });

  // Mobile BOTTOM nav "Menu" button → opens PAGES panel (nav links, LEFT slide)
  var mobPagesBtn = document.getElementById('mobPagesBtn');
  if (mobPagesBtn) mobPagesBtn.addEventListener('click', function() { openPanel(pagesPanel); });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeAllPanels(); closeSearch(); }
  });

  /* ════════ MOBILE SEARCH ════════ */
  var mobSearchBtn   = document.getElementById('mobSearchBtn');
  var mobSearchInput = document.getElementById('mobSearchInput');

  function closeSearch() {
    if (searchOverlay) searchOverlay.classList.remove('open');
    body.style.overflow = '';
  }

  if (mobSearchBtn) {
    mobSearchBtn.addEventListener('click', function() {
      if (searchOverlay) {
        searchOverlay.classList.add('open');
        body.style.overflow = 'hidden';
        setTimeout(function() { if (mobSearchInput) mobSearchInput.focus(); }, 180);
      }
    });
  }
  if (searchOverlay) {
    searchOverlay.addEventListener('click', function(e) {
      if (e.target === searchOverlay) closeSearch();
    });
  }

  // Search hint chips
  document.querySelectorAll('.asrch-hint').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var box  = btn.closest('.asrch-box');
      var inp  = box && box.querySelector('input[type="search"]');
      var drop = box && box.querySelector('.asrch-dropdown');
      if (inp) { inp.value = btn.textContent.trim(); inp.focus(); triggerSearch(inp, drop); }
    });
  });

  /* ════════ LIVE SEARCH ════════ */
  var searchTimer = null;

  function triggerSearch(input, dropdown) {
    var q = input.value.trim();
    if (q.length < 2) { hideDrop(dropdown); return; }
    clearTimeout(searchTimer);
    showDropLoading(dropdown);
    searchTimer = setTimeout(function() { doLiveSearch(q, dropdown); }, 280);
  }

  function showDropLoading(drop) {
    if (!drop) return;
    drop.classList.add('open');
    drop.setAttribute('aria-hidden','false');
    var l = drop.querySelector('.asrch-loading'), r = drop.querySelector('.asrch-results');
    if (l) l.style.display = 'flex';
    if (r) r.innerHTML = '';
    var counter = drop.querySelector('.asrch-result-count');
    if (counter) counter.textContent = '';
  }

  function hideDrop(drop) {
    if (!drop) return;
    drop.classList.remove('open');
    drop.setAttribute('aria-hidden','true');
  }

  function doLiveSearch(query, dropdown) {
    if (typeof $ === 'undefined' || typeof aurixData === 'undefined') return;
    $.post(aurixData.ajaxUrl, {
      action:'aurix_live_search',
      nonce: aurixData.searchNonce,
      query: query
    }, function(res) {
      if (!res.success || !dropdown) return;
      var loading = dropdown.querySelector('.asrch-loading');
      var results = dropdown.querySelector('.asrch-results');
      var counter = dropdown.querySelector('.asrch-result-count');
      if (loading) loading.style.display = 'none';
      var data = res.data.results || [];
      // Filter: NO pages — only products, categories, blogs
      data = data.filter(function(item) { return item.type !== 'page'; });
      if (counter) counter.textContent = data.length > 0 ? data.length + ' result' + (data.length !== 1 ? 's' : '') : '';

      if (!data.length) {
        results.innerHTML = buildEmptyState(query);
        dropdown.classList.add('open');
        return;
      }

      // Group
      var groups = {};
      data.forEach(function(item) {
        if (!groups[item.type]) groups[item.type] = [];
        groups[item.type].push(item);
      });

      var cfg = {
        product:  { label:'Products',       icon:'fa-box',       color:'#b8925a' },
        category: { label:'Categories',     icon:'fa-folder-open', color:'#3182ce' },
        blog:     { label:'Blog Articles',  icon:'fa-newspaper', color:'#38a169' }
      };

      var html = '';
      ['product','category','blog'].forEach(function(type) {
        if (!groups[type] || !groups[type].length) return;
        var c = cfg[type];
        html += '<div class="asrch-group asrch-group--' + type + '">';
        html += '<div class="asrch-group-label"><i class="fas ' + c.icon + '" style="color:' + c.color + '"></i>' + c.label + '</div>';
        groups[type].forEach(function(item) {
          html += buildResultItem(item, query, c.icon);
        });
        html += '</div>';
      });

      results.innerHTML = html;
      dropdown.classList.add('open');
    });
  }

  function buildResultItem(item, query, fallbackIcon) {
    var imgHtml = item.img
      ? '<img class="asrch-item__img" src="' + escHtml(item.img) + '" alt="" loading="lazy">'
      : '<div class="asrch-item__icon-wrap"><i class="fas ' + fallbackIcon + '"></i></div>';

    var meta = '';
    if (item.price)                  meta += '<span class="asrch-item__price">' + escHtml(item.price) + '</span>';
    if (item.sku)                    meta += '<span class="asrch-item__sku"><i class="fas fa-barcode"></i> ' + escHtml(item.sku) + '</span>';
    if (item.date)                   meta += '<span class="asrch-item__date"><i class="fas fa-calendar-alt"></i> ' + escHtml(item.date) + '</span>';
    if (item.count !== undefined)    meta += '<span class="asrch-item__count">' + item.count + ' products</span>';

    return '<a class="asrch-item asrch-item--' + item.type + '" href="' + escHtml(item.url) + '">'
      + imgHtml
      + '<div class="asrch-item__body">'
      + '<span class="asrch-item__title">' + highlightMatch(escHtml(item.title), escHtml(query)) + '</span>'
      + (meta ? '<div class="asrch-item__meta">' + meta + '</div>' : '')
      + '</div>'
      + '<i class="fas fa-arrow-right asrch-item__arrow"></i>'
      + '</a>';
  }

  function buildEmptyState(query) {
    return '<div class="asrch-empty">'
      + '<i class="fas fa-search-minus"></i>'
      + '<div>'
      + '<p class="asrch-empty__title">No results for "<strong>' + escHtml(query) + '</strong>"</p>'
      + '<p class="asrch-empty__sub">Try different keywords or <a href="' + (aurixData.cartUrl ? aurixData.cartUrl.replace('cart','?s='+encodeURIComponent(query)) : '#') + '">browse all products</a></p>'
      + '</div>'
      + '</div>';
  }

  function highlightMatch(text, query) {
    var re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');
    return text.replace(re, '<mark>$1</mark>');
  }
  function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // Desktop search wiring
  var ahdrInput = document.getElementById('ahdrSearchInput');
  var ahdrDrop  = document.getElementById('ahdrSearchDrop');
  if (ahdrInput && ahdrDrop) {
    ahdrInput.addEventListener('input', function() { triggerSearch(ahdrInput, ahdrDrop); });
    ahdrInput.addEventListener('focus', function() {
      if (ahdrInput.value.trim().length >= 2) ahdrDrop.classList.add('open');
    });
    document.addEventListener('click', function(e) {
      if (!e.target.closest('#ahdrSearch')) hideDrop(ahdrDrop);
    });
    // Keyboard nav in dropdown
    ahdrInput.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { hideDrop(ahdrDrop); ahdrInput.blur(); }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        var first = ahdrDrop.querySelector('.asrch-item');
        if (first) first.focus();
      }
    });
  }

  // Mobile search wiring
  if (mobSearchInput) {
    var mobDrop = document.getElementById('mobSearchDrop');
    mobSearchInput.addEventListener('input', function() { triggerSearch(mobSearchInput, mobDrop); });
  }

  /* ════════ WISHLIST ════════ */
  function updateWishlistCount(count) {
    document.querySelectorAll('.aurix-wishlist-count').forEach(function(el) {
      el.textContent = count;
      el.style.display = count > 0 ? '' : 'none';
    });
  }

  if (typeof window.aurixWishlistCount !== 'undefined') {
    updateWishlistCount(window.aurixWishlistCount);
  }

  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.aurix-wl-btn, .aurix-wl-remove');
    if (!btn) return;
    e.preventDefault(); e.stopPropagation();
    var pid = parseInt(btn.getAttribute('data-id'), 10);
    if (!pid || typeof $ === 'undefined') return;
    btn.classList.add('loading');

    $.post(aurixData.ajaxUrl, {
      action:'aurix_wishlist_toggle', nonce:aurixData.nonce, product_id:pid
    }, function(res) {
      btn.classList.remove('loading');
      if (!res.success) return;
      updateWishlistCount(res.data.count);
      document.querySelectorAll('.aurix-wl-btn[data-id="'+pid+'"]').forEach(function(b) {
        b.classList.toggle('in-wishlist', res.data.action === 'added');
        b.title = res.data.action === 'added' ? 'Remove from Wishlist' : 'Add to Wishlist';
      });
      showToast(res.data.action === 'added' ? '❤️ Added to Wishlist' : '🗑 Removed from Wishlist',
                res.data.action === 'added' ? 'success' : 'info');
      if (res.data.action === 'removed') {
        var item = document.querySelector('.aurix-wl-item[data-id="'+pid+'"]');
        if (item) { item.style.opacity='0'; item.style.transform='scale(.95)'; setTimeout(function(){ item.remove(); }, 300); }
      }
    });
  });

  /* ════════ TOAST ════════ */
  function showToast(msg, type) {
    var t = document.getElementById('aurix-toast');
    if (t) t.remove();
    t = document.createElement('div');
    t.id = 'aurix-toast';
    t.className = 'aurix-toast aurix-toast--'+(type||'info');
    t.innerHTML = msg;
    document.body.appendChild(t);
    setTimeout(function(){ t.classList.add('show'); }, 10);
    setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ t.remove(); }, 300); }, 3000);
  }

  /* ════════ CART COUNT + FRAGMENTS ════════ */
  function updateCartCount(count) {
    document.querySelectorAll('.aurix-cart-count').forEach(function(el) {
      el.textContent = count;
      el.setAttribute('data-count', count);
      el.style.display = count > 0 ? '' : 'none';
    });
  }

  if (typeof $ !== 'undefined') {
    $(document.body).on('added_to_cart', function(e, fragments) {
      if (fragments) {
        if (fragments['div.aurix-minicart-items']) {
          var $c = $('#aurix-cart-panel .aurix-minicart-items');
          if ($c.length) $c.replaceWith(fragments['div.aurix-minicart-items']);
        }
        if (fragments['.aurix-cart-count']) {
          var $b = $(fragments['.aurix-cart-count']);
          updateCartCount($b.data('count') || parseInt($b.text(),10) || 0);
        }
      }
      // On desktop open cart sidebar; on mobile show toast only (cart btn = link to page)
      if (window.innerWidth > 768 && cartPanel) openPanel(cartPanel);
      showToast('<i class="fas fa-cart-plus"></i> Added to Cart', 'success');
    });

    $(document.body).on('removed_from_cart updated_wc_div wc_fragment_refresh', function() {
      $.post(aurixData.ajaxUrl, { action:'aurix_get_cart_count', nonce:aurixData.nonce }, function(r) {
        if (r.success) updateCartCount(r.data.count);
      });
    });

    $(document.body).on('wc_fragments_refreshed', function() {
      if (typeof wc_cart_fragments_params === 'undefined') return;
      try {
        var stored = sessionStorage.getItem(wc_cart_fragments_params.fragment_name);
        if (!stored) return;
        var parsed = JSON.parse(stored);
        if (parsed['div.aurix-minicart-items']) {
          var $c = $('#aurix-cart-panel .aurix-minicart-items');
          if ($c.length) $c.replaceWith(parsed['div.aurix-minicart-items']);
        }
        if (parsed['.aurix-cart-count']) {
          var $b = $(parsed['.aurix-cart-count']);
          updateCartCount($b.data('count') || parseInt($b.text(),10) || 0);
        }
      } catch(err) {}
    });

    $(document.body).on('wc_fragments_refreshed added_to_cart', function() {
      var $t = $('#aurix-cart-panel .acp-total-amt');
      if (!$t.length) return;
      $.post(aurixData.ajaxUrl, { action:'aurix_get_cart_total', nonce:aurixData.nonce }, function(r) {
        if (r.success && r.data.total) $t.html(r.data.total);
      });
    });
  }

  /* ════════ FOOTER ACCORDION ════════ */
  document.querySelectorAll('.mob-accord__btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var item = btn.closest('.mob-accord');
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.mob-accord').forEach(function(el) { el.classList.remove('open'); el.querySelector('.mob-accord__btn').setAttribute('aria-expanded','false'); });
      if (!isOpen) { item.classList.add('open'); btn.setAttribute('aria-expanded','true'); }
    });
  });

  /* ════════ ACTIVE NAV LINK ════════ */
  var path = window.location.pathname;
  document.querySelectorAll('.anav-links a').forEach(function(a) {
    try { if (new URL(a.href).pathname === path) a.classList.add('active'); } catch(e) {}
  });

  /* ════════ ACCOUNT HERO INJECT ════════ */
  // Inject avatar initial into dashboard hero if present
  var hero = document.querySelector('.aurix-account-hero__avatar');
  if (hero && typeof aurixData !== 'undefined' && aurixData.userName) {
    hero.textContent = aurixData.userName.charAt(0).toUpperCase();
  }

})(typeof jQuery !== 'undefined' ? jQuery : {});
