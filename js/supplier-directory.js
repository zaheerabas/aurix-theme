/* Dental supplier directory: search, filters, sorting, "show more" and the
   "Add your company" form (page-supplier-directory.php). */
(function () {
  'use strict';

  var PAGE = 24;
  var grid = document.getElementById('axd-grid');
  if (!grid) return;

  var cards = Array.prototype.slice.call(grid.querySelectorAll('.axd-card'));
  var q = document.getElementById('axd-q');
  var country = document.getElementById('axd-country');
  var state = document.getElementById('axd-state');
  var stateWrap = document.getElementById('axd-state-wrap');
  var type = document.getElementById('axd-type');
  var sort = document.getElementById('axd-sort');
  var chips = Array.prototype.slice.call(document.querySelectorAll('#axd-focus .axd-chip'));
  var count = document.getElementById('axd-count');
  var more = document.getElementById('axd-more');
  var empty = document.getElementById('axd-empty');
  var shown = PAGE;

  function activeFocus() {
    return chips.filter(function (c) { return c.getAttribute('aria-pressed') === 'true'; })
      .map(function (c) { return c.getAttribute('data-focus'); });
  }

  function matches(card, terms, focus) {
    var d = card.dataset;
    if (country.value && d.country !== country.value) return false;
    if (country.value === 'United States' && state.value && d.state !== state.value) return false;
    if (type.value && d.type !== type.value) return false;
    if (focus.length) {
      var has = d.focus.split('|');
      for (var i = 0; i < focus.length; i++) if (has.indexOf(focus[i]) === -1) return false;
    }
    for (var j = 0; j < terms.length; j++) if (d.search.indexOf(terms[j]) === -1) return false;
    return true;
  }

  function order() {
    var mode = sort.value;
    var sorted = cards.slice().sort(function (a, b) {
      if (mode === 'featured') return a.dataset.order - b.dataset.order;
      var r = a.dataset.name.localeCompare(b.dataset.name);
      return mode === 'za' ? -r : r;
    });
    sorted.forEach(function (c) { grid.appendChild(c); });
    cards = sorted;
  }

  function render() {
    var terms = q.value.toLowerCase().trim().split(/\s+/).filter(Boolean);
    var focus = activeFocus();
    var total = 0;
    stateWrap.hidden = country.value !== 'United States';
    cards.forEach(function (card) {
      var ok = matches(card, terms, focus);
      if (ok) total++;
      card.hidden = !ok || total > shown;
    });
    count.innerHTML = 'Showing <strong>' + Math.min(total, shown) + '</strong> of <strong>' + total + '</strong> suppliers';
    more.hidden = total <= shown;
    empty.hidden = total > 0;
    syncUrl();
  }

  function reset() { shown = PAGE; render(); }

  // Keep filters in the address bar so a filtered view can be shared.
  function syncUrl() {
    if (!window.history.replaceState) return;
    var p = new URLSearchParams();
    if (q.value.trim()) p.set('q', q.value.trim());
    if (country.value) p.set('country', country.value);
    if (!stateWrap.hidden && state.value) p.set('state', state.value);
    if (type.value) p.set('type', type.value);
    var f = activeFocus();
    if (f.length) p.set('focus', f.join(','));
    var s = p.toString();
    history.replaceState(null, '', location.pathname + (s ? '?' + s : '') + location.hash);
  }

  function readUrl() {
    var p = new URLSearchParams(location.search);
    if (p.get('q')) q.value = p.get('q');
    if (p.get('country')) country.value = p.get('country');
    if (p.get('state')) state.value = p.get('state');
    if (p.get('type')) type.value = p.get('type');
    (p.get('focus') || '').split(',').forEach(function (f) {
      chips.forEach(function (c) { if (c.getAttribute('data-focus') === f) c.setAttribute('aria-pressed', 'true'); });
    });
  }

  var timer;
  q.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(reset, 120); });
  [country, state, type].forEach(function (el) { el.addEventListener('change', reset); });
  sort.addEventListener('change', function () { order(); render(); });
  chips.forEach(function (c) {
    c.addEventListener('click', function () {
      c.setAttribute('aria-pressed', c.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
      reset();
    });
  });
  more.addEventListener('click', function () { shown += PAGE; render(); });
  document.getElementById('axd-reset').addEventListener('click', function () {
    q.value = ''; country.value = ''; state.value = ''; type.value = '';
    chips.forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
    reset();
  });

  readUrl();
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
