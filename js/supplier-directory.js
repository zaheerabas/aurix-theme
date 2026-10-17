/* Dental supplier directory: search, filters, sorting, "show more" and the
   "Add your company" form (page-supplier-directory.php). */
(function () {
  'use strict';

  var PAGE = 24;
  var grid = document.getElementById('axd-grid');
  if (!grid) return;

  var $ = function (id) { return document.getElementById(id); };
  var all = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  var cards = all('.axd-card', grid);
  var q = $('axd-q');
  var country = $('axd-country');
  var state = $('axd-state');
  var stateWrap = $('axd-state-wrap');
  var typeInputs = all('#axd-type input');
  var verified = $('axd-verified');
  var sort = $('axd-sort');
  var chips = all('#axd-focus .axd-chip');
  var regionBtns = all('.axd-region');
  var letterBtns = all('.axd-letter');
  var viewBtns = all('.axd-view');
  var count = $('axd-count');
  var active = $('axd-active');
  var badge = $('axd-filters-badge');
  var more = $('axd-more');
  var moreWrap = $('axd-more-wrap');
  var empty = $('axd-empty');
  var panel = $('axd-filters');
  var backdrop = $('axd-backdrop');
  var shown = PAGE;
  var region = '';
  var letter = '';

  function store(key, val) {
    try { if (val === undefined) return localStorage.getItem(key); localStorage.setItem(key, val); } catch (e) { return null; }
  }

  function typeValue() {
    var c = typeInputs.filter(function (i) { return i.checked; })[0];
    return c ? c.value : '';
  }
  function setType(v) { typeInputs.forEach(function (i) { i.checked = i.value === v; }); }
  function focusList() {
    return chips.filter(function (c) { return c.getAttribute('aria-pressed') === 'true'; })
      .map(function (c) { return c.getAttribute('data-focus'); });
  }
  function press(btns, attr, value) {
    btns.forEach(function (b) {
      var on = b.getAttribute(attr) === value;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }

  function matches(card, terms, focus, type) {
    var d = card.dataset;
    if (region && d.region !== region) return false;
    if (country.value && d.country !== country.value) return false;
    if (country.value === 'United States' && state.value && d.state !== state.value) return false;
    if (type && d.type !== type) return false;
    if (verified.checked && !d.trust) return false;
    if (letter && d.letter !== letter) return false;
    if (focus.length) {
      var has = d.focus.split('|');
      for (var i = 0; i < focus.length; i++) if (has.indexOf(focus[i]) === -1) return false;
    }
    for (var j = 0; j < terms.length; j++) if (d.search.indexOf(terms[j]) === -1) return false;
    return true;
  }

  function order() {
    var mode = sort.value;
    cards = cards.slice().sort(function (a, b) {
      if (mode === 'featured') return a.dataset.order - b.dataset.order;
      if (mode === 'country') return (a.dataset.country.localeCompare(b.dataset.country)) || a.dataset.name.localeCompare(b.dataset.name);
      var r = a.dataset.name.localeCompare(b.dataset.name);
      return mode === 'za' ? -r : r;
    });
    cards.forEach(function (c) { grid.appendChild(c); });
  }

  // Removable chips for every active filter.
  function renderActive(focus, type) {
    var items = [];
    if (q.value.trim()) items.push(['q', '“' + q.value.trim() + '”']);
    if (region) items.push(['region', region]);
    if (country.value) items.push(['country', country.value]);
    if (!stateWrap.hidden && state.value) items.push(['state', state.options[state.selectedIndex].text]);
    if (type) items.push(['type', type]);
    focus.forEach(function (f) { items.push(['focus:' + f, f]); });
    if (verified.checked) items.push(['verified', 'Featured & verified']);
    if (letter) items.push(['letter', 'Starts with ' + letter]);
    active.hidden = !items.length;
    active.innerHTML = '';
    items.forEach(function (it) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'axd-active-chip';
      b.setAttribute('data-clear', it[0]);
      b.textContent = it[1];
      b.insertAdjacentHTML('beforeend', ' <i class="fa-solid fa-xmark" aria-hidden="true"></i>');
      b.setAttribute('aria-label', 'Remove filter ' + it[1]);
      active.appendChild(b);
    });
    if (items.length > 1) {
      var c = document.createElement('button');
      c.type = 'button';
      c.className = 'axd-active-clear';
      c.setAttribute('data-axd-reset', '');
      c.textContent = 'Clear all';
      active.appendChild(c);
    }
    var n = items.filter(function (it) { return it[0] !== 'q'; }).length;
    badge.hidden = !n;
    badge.textContent = n;
  }

  function render() {
    var terms = q.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
    var focus = focusList();
    var type = typeValue();
    var total = 0;
    stateWrap.hidden = country.value !== 'United States';
    cards.forEach(function (card) {
      var ok = matches(card, terms, focus, type);
      if (ok) total++;
      card.hidden = !ok || total > shown;
    });
    var visible = Math.min(total, shown);
    count.innerHTML = total
      ? 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> supplier' + (total === 1 ? '' : 's')
      : 'No suppliers found';
    moreWrap.hidden = total <= shown;
    $('axd-progress-bar').style.width = (total ? Math.round(visible / total * 100) : 0) + '%';
    $('axd-progress-text').textContent = 'You have seen ' + visible + ' of ' + total + ' suppliers';
    empty.hidden = total > 0;
    $('axd-filters-apply').textContent = 'Show ' + total + ' result' + (total === 1 ? '' : 's');
    renderActive(focus, type);
    syncUrl(focus, type);
  }

  function reset() { shown = PAGE; render(); }

  function clearAll() {
    q.value = ''; country.value = ''; state.value = ''; setType(''); verified.checked = false;
    region = ''; letter = '';
    press(regionBtns, 'data-region', ''); press(letterBtns, 'data-letter', '');
    chips.forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
    reset();
  }

  function clearOne(key) {
    if (key === 'q') q.value = '';
    else if (key === 'region') { region = ''; press(regionBtns, 'data-region', ''); }
    else if (key === 'country') { country.value = ''; state.value = ''; }
    else if (key === 'state') state.value = '';
    else if (key === 'type') setType('');
    else if (key === 'verified') verified.checked = false;
    else if (key === 'letter') { letter = ''; press(letterBtns, 'data-letter', ''); }
    else if (key.indexOf('focus:') === 0) {
      var f = key.slice(6);
      chips.forEach(function (c) { if (c.getAttribute('data-focus') === f) c.setAttribute('aria-pressed', 'false'); });
    }
    reset();
  }

  // Keep filters and view in the address bar so a view can be shared.
  function syncUrl(focus, type) {
    if (!window.history.replaceState || !window.URLSearchParams) return;
    var p = new URLSearchParams();
    if (q.value.trim()) p.set('q', q.value.trim());
    if (region) p.set('region', region);
    if (country.value) p.set('country', country.value);
    if (!stateWrap.hidden && state.value) p.set('state', state.value);
    if (type) p.set('type', type);
    if (focus.length) p.set('focus', focus.join(','));
    if (verified.checked) p.set('verified', '1');
    if (letter) p.set('letter', letter);
    if (grid.classList.contains('is-list')) p.set('view', 'list');
    var s = p.toString();
    history.replaceState(null, '', location.pathname + (s ? '?' + s : '') + location.hash);
  }

  function readUrl() {
    if (!window.URLSearchParams) return;
    var p = new URLSearchParams(location.search);
    if (p.get('q')) q.value = p.get('q');
    if (p.get('region')) { region = p.get('region'); press(regionBtns, 'data-region', region); }
    if (p.get('country')) country.value = p.get('country');
    if (p.get('state')) state.value = p.get('state');
    if (p.get('type')) setType(p.get('type'));
    if (p.get('verified')) verified.checked = true;
    if (p.get('letter')) { letter = p.get('letter'); press(letterBtns, 'data-letter', letter); }
    (p.get('focus') || '').split(',').forEach(function (f) {
      chips.forEach(function (c) { if (c.getAttribute('data-focus') === f) c.setAttribute('aria-pressed', 'true'); });
    });
    return p.get('view');
  }

  function setView(v) {
    var list = v === 'list';
    grid.classList.toggle('is-list', list);
    press(viewBtns, 'data-view', list ? 'list' : 'grid');
    store('axdView', list ? 'list' : 'grid');
  }

  // Mobile filter drawer.
  function openPanel(open) {
    panel.classList.toggle('is-open', open);
    backdrop.hidden = !open;
    document.documentElement.classList.toggle('axd-lock', open);
    if (open) $('axd-filters-close').focus();
  }

  var timer;
  q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(reset, 120); });
  country.addEventListener('change', function () {
    if (country.value) { region = ''; press(regionBtns, 'data-region', ''); }
    reset();
  });
  [state, verified, sort].forEach(function (el) { el.addEventListener('change', el === sort ? function () { order(); render(); } : reset); });
  typeInputs.forEach(function (i) { i.addEventListener('change', reset); });
  chips.forEach(function (c) {
    c.addEventListener('click', function () {
      c.setAttribute('aria-pressed', c.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
      reset();
    });
  });
  regionBtns.forEach(function (b) {
    b.addEventListener('click', function () {
      region = b.getAttribute('data-region');
      press(regionBtns, 'data-region', region);
      country.value = ''; state.value = '';
      reset();
    });
  });
  letterBtns.forEach(function (b) {
    b.addEventListener('click', function () {
      letter = b.getAttribute('data-letter') === letter ? '' : b.getAttribute('data-letter');
      press(letterBtns, 'data-letter', letter);
      reset();
    });
  });
  viewBtns.forEach(function (b) {
    b.addEventListener('click', function () { setView(b.getAttribute('data-view')); syncUrl(focusList(), typeValue()); });
  });
  more.addEventListener('click', function () { shown += PAGE; render(); });
  active.addEventListener('click', function (e) {
    var b = e.target.closest('[data-clear]');
    if (b) clearOne(b.getAttribute('data-clear'));
  });
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-axd-reset]')) clearAll();
  });
  $('axd-reset').addEventListener('click', clearAll);
  $('axd-filters-open').addEventListener('click', function () { openPanel(true); });
  $('axd-filters-close').addEventListener('click', function () { openPanel(false); });
  $('axd-filters-apply').addEventListener('click', function () {
    openPanel(false);
    $('axd-directory').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  backdrop.addEventListener('click', function () { openPanel(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel.classList.contains('is-open')) openPanel(false); });

  // "Popular" shortcuts under the hero search.
  all('.axd-popular button').forEach(function (b) {
    b.addEventListener('click', function () {
      clearAll();
      var d = b.dataset;
      if (d.popFocus) chips.forEach(function (c) { if (c.getAttribute('data-focus') === d.popFocus) c.setAttribute('aria-pressed', 'true'); });
      if (d.popCountry) country.value = d.popCountry;
      if (d.popType) setType(d.popType);
      reset();
      $('axd-directory').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  var urlView = readUrl();
  setView(urlView || store('axdView') || 'grid');
  render();

  /* ── Add your company form ─────────────────────────────────────────── */
  var form = document.getElementById('axd-form');
  if (!form || !window.aurixDir) return;
  var msg = document.getElementById('axd-form-msg');
  var fCountry = document.getElementById('axd-f-country');
  var stateUs = document.getElementById('axd-f-state-us');
  var stateOther = document.getElementById('axd-f-state-other');

  function toggleState() {
    var v = fCountry.value.trim().toLowerCase();
    var isUs = !v || v === 'united states' || v === 'usa' || v === 'us' || v === 'united states of america';
    stateUs.hidden = !isUs; stateUs.querySelector('select').disabled = !isUs;
    stateOther.hidden = isUs; stateOther.querySelector('input').disabled = isUs;
  }
  fCountry.addEventListener('input', toggleState);
  toggleState();

  function show(text, ok) {
    msg.hidden = false;
    msg.textContent = text;
    msg.className = 'axd-form-msg ' + (ok ? 'is-ok' : 'is-error');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var bad = false;
    form.querySelectorAll('[required]').forEach(function (el) {
      var wrap = el.closest('.axd-field, .axd-check');
      var invalid = el.type === 'checkbox' ? !el.checked : !el.value.trim() || (el.type === 'email' && !/^\S+@\S+\.\S+$/.test(el.value));
      if (wrap) wrap.classList.toggle('is-invalid', invalid);
      if (invalid) bad = true;
    });
    if (bad) { show('Please fill in the required fields marked *.', false); return; }

    var data = new FormData(form);
    if (/^(usa|us|united states of america)$/i.test(fCountry.value.trim())) data.set('country', 'United States');
    form.classList.add('is-sending');
    fetch(window.aurixDir.ajax, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        var text = res && res.data && res.data.message ? res.data.message : 'Something went wrong. Please try again.';
        show(text, !!(res && res.success));
        if (res && res.success) { form.reset(); toggleState(); }
      })
      .catch(function () { show('Network error. Please try again.', false); })
      .then(function () { form.classList.remove('is-sending'); });
  });
})();
