/**
 * Aurix International — product.js v8.0
 * Author: ZaheerAbbas
 * Fixes: category/tags display, thumbnail carousel L/R, 
 *        mobile related products horizontal scroll, improved lightbox
 */
(function ($) {
  'use strict';

  /* ════════════════════════════════════════════════════
     GALLERY — Build image data from thumbnails
  ════════════════════════════════════════════════════ */
  var mainImg     = document.getElementById('aprodMainImage');
  var thumbsEl    = document.getElementById('aprodThumbs');
  var thumbsTrack = document.getElementById('aprodThumbsTrack');
  var thumbBtns   = [];
  var imgData     = [];
  var currentIdx  = 0;

  if (thumbsEl) {
    thumbBtns = Array.from(thumbsEl.querySelectorAll('.aprod-thumb'));
    thumbBtns.forEach(function(btn) {
      imgData.push({
        src:  btn.getAttribute('data-src')  || '',
        full: btn.getAttribute('data-full') || btn.getAttribute('data-src') || '',
        alt:  btn.getAttribute('data-alt')  || '',
      });
    });
  }

  // Fallback: collect from main image only
  if (imgData.length === 0 && mainImg) {
    imgData.push({
      src:  mainImg.src,
      full: mainImg.getAttribute('data-full') || mainImg.src,
      alt:  mainImg.alt || '',
    });
  }

  function setActiveThumb(idx) {
    thumbBtns.forEach(function(btn, i) {
      btn.classList.toggle('active', i === idx);
    });
    scrollThumbIntoView(idx);
  }

  // Smooth fade+scale swap of main image
  function switchImage(idx, fromLightbox) {
    if (idx < 0 || idx >= imgData.length) return;
    currentIdx = idx;
    // Update nav arrow states
    if (thumbPrev) thumbPrev.disabled = idx <= 0;
    if (thumbNext) thumbNext.disabled = idx >= imgData.length - 1;
    var d = imgData[idx];
    if (!mainImg || !d.src) return;

    mainImg.style.opacity = '0';
    mainImg.style.transform = 'scale(0.96)';
    setTimeout(function() {
      mainImg.src = d.src;
      mainImg.setAttribute('data-full', d.full || d.src);
      mainImg.alt = d.alt || '';
      mainImg.style.opacity = '1';
      mainImg.style.transform = 'scale(1)';
    }, 140);

    if (!fromLightbox) setActiveThumb(idx);
  }

  // Apply CSS transition to main image
  if (mainImg) {
    mainImg.style.transition = 'opacity .14s ease, transform .14s ease';
  }

  // Wire thumb clicks
  thumbBtns.forEach(function(btn, i) {
    btn.addEventListener('click', function() { switchImage(i); });
    btn.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); switchImage(i); }
    });
  });

  /* ════════════════════════════════════════════════════
     THUMBNAIL CAROUSEL — prev/next arrows
  ════════════════════════════════════════════════════ */
  var thumbPrev       = document.getElementById('aprodThumbPrev');
  var thumbNext       = document.getElementById('aprodThumbNext');
  var thumbOffset     = 0;      // current scroll offset in px
  var THUMB_W         = 64 + 8; // thumb width + gap

  function getVisibleCount() {
    if (!thumbsTrack) return 4;
    return Math.floor(thumbsTrack.offsetWidth / THUMB_W);
  }

  function getMaxOffset() {
    var visible = getVisibleCount();
    var max = (imgData.length - visible) * THUMB_W;
    return Math.max(0, max);
  }

  function setThumbOffset(offset) {
    thumbOffset = Math.max(0, Math.min(offset, getMaxOffset()));
    if (thumbsEl) {
      thumbsEl.style.transform = 'translateX(-' + thumbOffset + 'px)';
    }
    updateNavBtns();
  }

  function updateNavBtns() {
    if (thumbPrev) thumbPrev.disabled = thumbOffset <= 0;
    if (thumbNext) thumbNext.disabled = thumbOffset >= getMaxOffset();
  }

  function scrollThumbIntoView(idx) {
    var visible = getVisibleCount();
    var itemLeft  = idx * THUMB_W;
    var itemRight = itemLeft + THUMB_W;
    if (itemLeft < thumbOffset) {
      setThumbOffset(itemLeft);
    } else if (itemRight > thumbOffset + visible * THUMB_W) {
      setThumbOffset(itemRight - visible * THUMB_W);
    }
  }

  // ← → arrows SWITCH the active/displayed image (not just scroll the strip)
  if (thumbPrev) {
    thumbPrev.addEventListener('click', function() {
      var newIdx = Math.max(0, currentIdx - 1);
      switchImage(newIdx);
      thumbPrev.disabled = newIdx <= 0;
      if (thumbNext) thumbNext.disabled = newIdx >= imgData.length - 1;
    });
  }
  if (thumbNext) {
    thumbNext.addEventListener('click', function() {
      var newIdx = Math.min(imgData.length - 1, currentIdx + 1);
      switchImage(newIdx);
      if (thumbPrev) thumbPrev.disabled = newIdx <= 0;
      thumbNext.disabled = newIdx >= imgData.length - 1;
    });
  }

  // Keep the strip scrolled to show the active thumb
  function syncStripToActive(idx) {
    setThumbOffset(0); // reset first
    scrollThumbIntoView(idx);
  }

  // Init nav btn states
  updateNavBtns();

  // Hide nav buttons if all thumbs fit
  window.addEventListener('resize', function() {
    updateNavBtns();
    setThumbOffset(thumbOffset); // re-clamp
  });

  // Touch swipe on thumbnail strip
  var thumbTouchX = 0;
  if (thumbsEl) {
    thumbsEl.addEventListener('touchstart', function(e) { thumbTouchX = e.touches[0].clientX; }, {passive:true});
    thumbsEl.addEventListener('touchend', function(e) {
      var dx = e.changedTouches[0].clientX - thumbTouchX;
      if (Math.abs(dx) > 30) setThumbOffset(thumbOffset - dx);
    });
  }

  /* ════════════════════════════════════════════════════
     LIGHTBOX — improved with spinner + smooth transitions
  ════════════════════════════════════════════════════ */
  var lightbox   = document.getElementById('aprodLightbox');
  var lbImage    = document.getElementById('aprodLbImage');
  var lbCounter  = document.getElementById('aprodLbCounter');
  var lbBackdrop = document.getElementById('aprodLbBackdrop');
  var lbClose    = document.getElementById('aprodLbClose');
  var lbPrev     = document.getElementById('aprodLbPrev');
  var lbNext     = document.getElementById('aprodLbNext');
  var lbSpinner  = document.getElementById('aprodLbSpinner');
  var zoomBtn    = document.getElementById('aprodZoomBtn');
  var imgWrap    = document.getElementById('aprodImgWrap');

  function openLightbox(idx) {
    if (!lightbox || !imgData.length) return;
    showLbImage(idx);
    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function showLbImage(idx) {
    if (idx !== undefined) currentIdx = idx;
    var d = imgData[currentIdx];
    if (!d || !lbImage) return;

    // Show spinner, fade out old image
    if (lbSpinner) lbSpinner.classList.add('active');
    lbImage.classList.add('loading');

    var url = d.full || d.src;
    var preload = new Image();
    preload.onload = function() {
      lbImage.src = url;
      lbImage.alt = d.alt || '';
      lbImage.classList.remove('loading');
      if (lbSpinner) lbSpinner.classList.remove('active');
    };
    preload.onerror = function() {
      lbImage.src = url;
      lbImage.classList.remove('loading');
      if (lbSpinner) lbSpinner.classList.remove('active');
    };
    preload.src = url;

    // Update counter
    if (lbCounter && imgData.length > 1) {
      lbCounter.textContent = (currentIdx + 1) + ' / ' + imgData.length;
      lbCounter.style.display = '';
    } else if (lbCounter) {
      lbCounter.style.display = 'none';
    }

    // Sync thumbnail
    setActiveThumb(currentIdx);
  }

  function lbNavigate(dir) {
    var next = (currentIdx + dir + imgData.length) % imgData.length;
    showLbImage(next);
  }

  // Trigger lightbox
  if (zoomBtn) zoomBtn.addEventListener('click', function() { openLightbox(currentIdx); });
  if (imgWrap) {
    imgWrap.addEventListener('click', function() { openLightbox(currentIdx); });
    imgWrap.style.cursor = 'zoom-in';
  }

  if (lbClose)    lbClose.addEventListener('click', closeLightbox);
  if (lbBackdrop) lbBackdrop.addEventListener('click', closeLightbox);
  if (lbPrev)     lbPrev.addEventListener('click', function() { lbNavigate(-1); });
  if (lbNext)     lbNext.addEventListener('click', function() { lbNavigate(+1); });

  // Touch swipe in lightbox
  var lbTouchX = 0;
  if (lightbox) {
    lightbox.addEventListener('touchstart', function(e) { lbTouchX = e.touches[0].clientX; }, {passive:true});
    lightbox.addEventListener('touchend', function(e) {
      var dx = e.changedTouches[0].clientX - lbTouchX;
      if (Math.abs(dx) > 50) lbNavigate(dx < 0 ? 1 : -1);
    });
  }

  // Keyboard
  document.addEventListener('keydown', function(e) {
    if (!lightbox || !lightbox.classList.contains('open')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  lbNavigate(-1);
    if (e.key === 'ArrowRight') lbNavigate(+1);
  });

  // Hide nav if 0 or 1 image
  if (imgData.length <= 1) {
    if (lbPrev) lbPrev.style.display = 'none';
    if (lbNext) lbNext.style.display = 'none';
  }

  /* ════════════════════════════════════════════════════
     PRODUCT TABS
  ════════════════════════════════════════════════════ */
  window.switchProdTab = function(id) {
    document.querySelectorAll('.aprod-tab').forEach(function(t) {
      var active = t.getAttribute('onclick') && t.getAttribute('onclick').indexOf("'"+id+"'") > -1;
      t.classList.toggle('active', active);
      t.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.querySelectorAll('.aprod-tab-panel').forEach(function(p) {
      p.classList.toggle('active', p.id === 'aprod-tab-' + id);
    });
    var wrap = document.getElementById('aprod-tabs');
    if (wrap && window.innerWidth < 860)
      setTimeout(function() { wrap.scrollIntoView({behavior:'smooth',block:'start'}); }, 50);
  };

  /* ════════════════════════════════════════════════════
     QUANTITY CONTROLS
  ════════════════════════════════════════════════════ */
  var qtyInput = document.querySelector('.aprod-qty-ctrl input.qty');
  var qMinus   = document.querySelector('.aprod-qty-minus');
  var qPlus    = document.querySelector('.aprod-qty-plus');

  if (qMinus && qtyInput) {
    qMinus.addEventListener('click', function() {
      var v   = parseInt(qtyInput.value,10) || 1;
      var min = Math.max(1, parseInt(qtyInput.getAttribute('min'),10) || 1);
      if (v > min) { qtyInput.value = v - 1; qtyInput.dispatchEvent(new Event('change')); }
    });
  }
  if (qPlus && qtyInput) {
    qPlus.addEventListener('click', function() {
      var v = parseInt(qtyInput.value,10)||1;
      var max = parseInt(qtyInput.getAttribute('max'),10);
      // Only enforce max if it's a valid positive number (ignore -1 from WC unmanaged stock)
      if (isNaN(max) || max < 1 || v < max) {
        qtyInput.value = v + 1;
        qtyInput.dispatchEvent(new Event('change'));
      }
    });
  }

  /* ════════════════════════════════════════════════════
     RFQ MODAL
  ════════════════════════════════════════════════════ */
  var rfqOverlay = document.getElementById('aprodRfqOverlay');
  var rfqBtn     = document.getElementById('aprodRfqBtn');
  var rfqClose   = document.getElementById('aprodRfqClose');
  var rfqForm    = document.getElementById('aprodRfqForm');

  function openRfq()  { if(rfqOverlay){rfqOverlay.classList.add('open');rfqOverlay.setAttribute('aria-hidden','false');document.body.style.overflow='hidden';} }
  function closeRfq() { if(rfqOverlay){rfqOverlay.classList.remove('open');rfqOverlay.setAttribute('aria-hidden','true');document.body.style.overflow='';} }
  if (rfqBtn)   rfqBtn.addEventListener('click', openRfq);
  if (rfqClose) rfqClose.addEventListener('click', closeRfq);
  if (rfqOverlay) rfqOverlay.addEventListener('click', function(e){ if(e.target===rfqOverlay)closeRfq(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape'&&rfqOverlay&&rfqOverlay.classList.contains('open'))closeRfq(); });

  if (rfqForm && typeof $ !== 'undefined') {
    rfqForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var btn = rfqForm.querySelector('.aprod-rfq-submit');
      if (btn) { btn.disabled=true; btn.innerHTML='<i class="fas fa-circle-notch fa-spin"></i> Sending…'; }
      var data = new FormData(rfqForm);
      data.append('action','aurix_rfq_submit');
      if (typeof aurixData!=='undefined') data.append('nonce',aurixData.nonce);
      $.ajax({ url:typeof aurixData!=='undefined'?aurixData.ajaxUrl:'/wp-admin/admin-ajax.php',
        type:'POST',data:data,processData:false,contentType:false,
        success:function(){ rfqForm.innerHTML='<div style="text-align:center;padding:32px"><i class="fas fa-check-circle" style="font-size:2.8rem;color:#22c55e;display:block;margin-bottom:12px"></i><h4 style="font-family:Cormorant Garamond,serif;font-size:1.4rem;color:var(--navy);margin-bottom:8px">Quote Request Sent!</h4><p style="color:var(--muted);font-size:.9rem;line-height:1.6">We\'ll respond within 24 business hours.</p></div>'; setTimeout(closeRfq,3500); },
        error:function(){ if(btn){btn.disabled=false;btn.innerHTML='<i class="fas fa-paper-plane"></i> Send Quote Request';} }
      });
    });
  }

  /* ════════════════════════════════════════════════════
     COPY LINK
  ════════════════════════════════════════════════════ */
  var copyBtn = document.getElementById('aprodCopyLink');
  if (copyBtn) {
    copyBtn.addEventListener('click', function() {
      if (!navigator.clipboard) return;
      navigator.clipboard.writeText(window.location.href).then(function() {
        copyBtn.innerHTML='<i class="fas fa-check"></i>';
        copyBtn.style.cssText='background:#22c55e;color:#fff;border-color:#22c55e';
        setTimeout(function(){ copyBtn.innerHTML='<i class="fas fa-link"></i>'; copyBtn.style.cssText=''; },2200);
      });
    });
  }

  /* ════════════════════════════════════════════════════
     ADD TO CART feedback
  ════════════════════════════════════════════════════ */
  if (typeof $ !== 'undefined') {
    $(document.body).on('added_to_cart', function() {
      var atcBtn = document.querySelector('.aprod-atc-btn.single_add_to_cart_button');
      if (atcBtn) {
        var orig = atcBtn.innerHTML;
        atcBtn.classList.add('success');
        atcBtn.innerHTML = '<i class="fas fa-check"></i> <span class="aprod-atc-text">Added!</span>';
        setTimeout(function() { atcBtn.classList.remove('success'); atcBtn.innerHTML=orig; }, 2500);
      }
    });

    // Variable product variation — update gallery image
    $(document.body).on('found_variation', function(e, variation) {
      if (variation.image && variation.image.src) {
        imgData[0] = {
          src:  variation.image.src,
          full: variation.image.full_src || variation.image.src,
          alt:  variation.image.alt || '',
        };
        switchImage(0);
      }
    });
  }

})(typeof jQuery !== 'undefined' ? jQuery : null);

/* ════════════════════════════════════════════════════
   REVIEWS — Style WooCommerce comment list
   Injects Aurix-branded review cards over WC defaults
════════════════════════════════════════════════════ */
function styleReviews() {
  var commentList = document.querySelector('#reviews #comments .commentlist');
  if (!commentList) return;

  commentList.querySelectorAll('li.comment').forEach(function(li) {
    if (li.classList.contains('aprod-styled')) return;
    li.classList.add('aprod-styled');

    var container = li.querySelector('.comment_container');
    if (!container) return;

    // Extract data
    var avatar    = container.querySelector('.avatar');
    var authorEl  = container.querySelector('.woocommerce-review__author');
    var dateEl    = container.querySelector('.woocommerce-review__published-date');
    var verified  = container.querySelector('.woocommerce-review__verified');
    var starEl    = container.querySelector('.star-rating');
    var textEl    = container.querySelector('.description p, .description');

    var authorName = authorEl ? authorEl.textContent.trim() : 'Anonymous';
    var dateStr    = dateEl   ? dateEl.textContent.trim()   : '';
    var isVerified = !!verified;
    var starWidth  = starEl ? (starEl.querySelector('span') ? starEl.querySelector('span').style.width : '100%') : '100%';
    var starPct    = parseFloat(starWidth) || 100;
    var starCount  = Math.round((starPct / 100) * 5);
    var reviewText = textEl ? textEl.textContent.trim() : '';

    // Build star string
    var stars = '';
    for (var s = 1; s <= 5; s++) stars += s <= starCount ? '★' : '☆';

    // Rebuild container HTML
    container.innerHTML =
      '<div class="aprod-review-meta">' +
        '<span class="aprod-review-author">' + escHtml(authorName) + '</span>' +
        (isVerified ? '<span class="aprod-review-verified"><i class="fas fa-check-circle"></i> Verified</span>' : '') +
        (dateStr ? '<span class="aprod-review-date">' + escHtml(dateStr) + '</span>' : '') +
      '</div>' +
      '<div class="aprod-review-stars">' + stars + '</div>' +
      (reviewText ? '<div class="aprod-review-text">' + escHtml(reviewText) + '</div>' : '');
  });
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Run on tab switch
var origSwitch = window.switchProdTab;
window.switchProdTab = function(id) {
  origSwitch(id);
  if (id === 'reviews') setTimeout(styleReviews, 80);
};

// Also run on load if reviews tab is visible
document.addEventListener('DOMContentLoaded', function() {
  var activePanel = document.querySelector('#aprod-tab-reviews.active');
  if (activePanel) styleReviews();
});

/* ════════════════════════════════════════════════════
   SHORT DESCRIPTION — truncate + "view more" / "view less"
════════════════════════════════════════════════════ */
(function() {
  var descEl  = document.getElementById('aprodShortDesc');
  var moreBtn = document.getElementById('aprodViewMore');
  if (!descEl || !moreBtn) return;

  // Check if content actually overflows (is longer than 3 lines)
  function checkOverflow() {
    // Temporarily expand to measure full height
    descEl.style.webkitLineClamp = 'unset';
    descEl.style.maxHeight = 'none';
    descEl.style.display = 'block';
    var fullH = descEl.scrollHeight;
    // Restore clamp
    descEl.style.webkitLineClamp = '';
    descEl.style.maxHeight = '';
    descEl.style.display = '';

    var lineH = parseFloat(getComputedStyle(descEl).lineHeight) || 25;
    var clampedH = lineH * 3;

    if (fullH <= clampedH + 4) {
      moreBtn.classList.add('hidden');
    } else {
      moreBtn.classList.remove('hidden');
    }
  }

  checkOverflow();
  window.addEventListener('resize', checkOverflow);

  moreBtn.addEventListener('click', function() {
    var isOpen = descEl.classList.contains('expanded');
    if (isOpen) {
      descEl.classList.remove('expanded');
      moreBtn.innerHTML = 'View more <i class="fas fa-chevron-down"></i>';
      moreBtn.classList.remove('open');
      moreBtn.setAttribute('aria-expanded','false');
    } else {
      descEl.classList.add('expanded');
      moreBtn.innerHTML = 'View less <i class="fas fa-chevron-up"></i>';
      moreBtn.classList.add('open');
      moreBtn.setAttribute('aria-expanded','true');
    }
  });
})();

/* ════════════════════════════════════════════════════
   RELATED PRODUCTS — Mobile 1-per-view carousel
════════════════════════════════════════════════════ */
(function() {
  var relGrid = document.getElementById('aprodRelatedGrid');
  var relPrev = document.getElementById('aprodRelPrev');
  var relNext = document.getElementById('aprodRelNext');
  if (!relGrid || !relPrev || !relNext) return;

  var cards   = Array.from(relGrid.querySelectorAll('.aprod-rel-card'));
  var total   = cards.length;
  var current = 0;

  // Mobile = any screen up to 768px (covers all phones in portrait and landscape)
  function isMobile() { return window.innerWidth <= 768; }

  function updateArrows() {
    relPrev.disabled = current === 0;
    relNext.disabled = current === total - 1;
    relPrev.style.opacity = current === 0 ? '0.3' : '1';
    relNext.style.opacity = current === total - 1 ? '0.3' : '1';
  }

  function goTo(idx) {
    if (!isMobile()) return;
    current = Math.max(0, Math.min(idx, total - 1));
    // Each card is 100% of container width
    relGrid.style.transform = 'translateX(-' + (current * 100) + '%)';
    updateArrows();
  }

  relPrev.addEventListener('click', function(e) {
    e.preventDefault();
    if (isMobile()) goTo(current - 1);
  });
  relNext.addEventListener('click', function(e) {
    e.preventDefault();
    if (isMobile()) goTo(current + 1);
  });

  // Touch swipe on related grid
  var touchStart = 0;
  relGrid.addEventListener('touchstart', function(e) {
    touchStart = e.touches[0].clientX;
  }, {passive:true});
  relGrid.addEventListener('touchend', function(e) {
    if (!isMobile()) return;
    var dx = e.changedTouches[0].clientX - touchStart;
    if (Math.abs(dx) > 40) goTo(dx < 0 ? current + 1 : current - 1);
  });

  // Reset on resize
  window.addEventListener('resize', function() {
    if (!isMobile()) {
      relGrid.style.transform = '';
      current = 0;
    } else {
      goTo(current);
    }
  });

  // Init
  if (isMobile()) {
    goTo(0);
  }
})();

/* ════════════════════════════════════════════════════
   SIDEBAR TOOLTIP — populate from data-tooltip attr
════════════════════════════════════════════════════ */
document.querySelectorAll('.aprod-sb-info-btn').forEach(function(btn) {
  var tipEl = btn.nextElementSibling;
  if (tipEl && tipEl.classList.contains('aprod-sb-tooltip')) {
    tipEl.textContent = btn.getAttribute('data-tooltip') || '';
  }
  // Also handle keyboard focus show/hide
  btn.addEventListener('focus', function() {
    if (tipEl) { tipEl.style.opacity='1'; tipEl.style.visibility='visible'; tipEl.style.transform='translateY(0)'; }
  });
  btn.addEventListener('blur', function() {
    if (tipEl) { tipEl.style.opacity=''; tipEl.style.visibility=''; tipEl.style.transform=''; }
  });
});

/* ════════════════════════════════════════════════════
   v11: Long description tab truncation + "Read more"
════════════════════════════════════════════════════ */
(function() {
  var descEl  = document.getElementById('aprodLongDesc');
  var moreBtn = document.getElementById('aprodDescViewMore');
  if (!descEl || !moreBtn) return;

  function checkDescOverflow() {
    // Temporarily measure full height
    var prev = descEl.style.maxHeight;
    descEl.style.maxHeight = 'none';
    descEl.classList.add('expanded');
    var full = descEl.scrollHeight;
    descEl.classList.remove('expanded');
    descEl.style.maxHeight = prev;

    if (full <= 190) {
      moreBtn.classList.add('hidden');
      descEl.classList.add('expanded'); // show all if short
    } else {
      moreBtn.classList.remove('hidden');
    }
  }

  checkDescOverflow();
  window.addEventListener('resize', checkDescOverflow);

  moreBtn.addEventListener('click', function() {
    var open = descEl.classList.contains('expanded');
    if (open) {
      descEl.classList.remove('expanded');
      moreBtn.innerHTML = 'Read full description <i class="fas fa-chevron-down"></i>';
      moreBtn.classList.remove('open');
    } else {
      descEl.classList.add('expanded');
      moreBtn.innerHTML = 'Show less <i class="fas fa-chevron-up"></i>';
      moreBtn.classList.add('open');
    }
  });
})();

/* ════════════════════════════════════════════════════
   v11: Improved lightbox with thumbnail strip
   Overwrites the old openLightbox / closeLightbox
════════════════════════════════════════════════════ */
(function($) {
  // Elements
  var lb          = document.getElementById('aprodLightbox');
  var lbImage     = document.getElementById('aprodLbImage');
  var lbCounter   = document.getElementById('aprodLbCounter');
  var lbBackdrop  = document.getElementById('aprodLbBackdrop');
  var lbClose     = document.getElementById('aprodLbClose');
  var lbPrev      = document.getElementById('aprodLbPrev');
  var lbNext      = document.getElementById('aprodLbNext');
  var lbSpinner   = document.getElementById('aprodLbSpinner');
  var lbThumbsEl  = document.getElementById('aprodLbThumbs');
  var lbThPrev    = document.getElementById('aprodLbThPrev');
  var lbThNext    = document.getElementById('aprodLbThNext');

  if (!lb) return;

  // Get image data from main gallery thumbnails
  var allThumbs = Array.from(document.querySelectorAll('#aprodThumbs .aprod-thumb'));
  var lbImgData = allThumbs.map(function(t) {
    return {
      full: t.getAttribute('data-full') || t.getAttribute('data-src') || '',
      src:  t.getAttribute('data-src')  || '',
      alt:  t.getAttribute('data-alt')  || '',
      thumb: t.querySelector('img') ? t.querySelector('img').src : '',
    };
  });

  // Fallback: single image from main img
  if (!lbImgData.length) {
    var mi = document.getElementById('aprodMainImage');
    if (mi) lbImgData.push({ full: mi.getAttribute('data-full')||mi.src, src:mi.src, alt:mi.alt, thumb:mi.src });
  }

  var lbCurrent = 0;

  // Build thumbnail strip inside lightbox
  function buildLbThumbs() {
    if (!lbThumbsEl || lbImgData.length <= 1) {
      if (lbThumbsEl) lbThumbsEl.closest('.aprod-lb-footer').style.display = 'none';
      return;
    }
    lbThumbsEl.innerHTML = '';
    lbImgData.forEach(function(d, i) {
      var btn = document.createElement('button');
      btn.className = 'aprod-lb-thumb-btn';
      btn.setAttribute('aria-label', 'View image ' + (i+1));
      var img = document.createElement('img');
      img.src = d.thumb || d.src;
      img.alt = d.alt || '';
      img.loading = 'lazy';
      btn.appendChild(img);
      btn.addEventListener('click', function() { lbShowImage(i); });
      lbThumbsEl.appendChild(btn);
    });
  }
  buildLbThumbs();

  function setLbThumbActive(idx) {
    if (!lbThumbsEl) return;
    lbThumbsEl.querySelectorAll('.aprod-lb-thumb-btn').forEach(function(b,i) {
      b.classList.toggle('active', i === idx);
    });
    // Scroll active thumb into view
    var active = lbThumbsEl.children[idx];
    if (active) active.scrollIntoView({inline:'center', behavior:'smooth', block:'nearest'});
  }

  function lbShowImage(idx) {
    lbCurrent = idx;
    var d = lbImgData[idx];
    if (!d || !lbImage) return;

    // Show spinner, fade out
    if (lbSpinner) lbSpinner.classList.add('active');
    lbImage.classList.add('loading');

    var pre = new Image();
    pre.onload = pre.onerror = function() {
      lbImage.src = d.full || d.src;
      lbImage.alt = d.alt || '';
      lbImage.classList.remove('loading');
      if (lbSpinner) lbSpinner.classList.remove('active');
    };
    pre.src = d.full || d.src;

    // Counter
    if (lbCounter) {
      lbCounter.textContent = lbImgData.length > 1 ? (idx+1) + ' / ' + lbImgData.length : '';
    }

    // Sync nav arrows
    if (lbPrev) lbPrev.style.display = lbImgData.length > 1 ? '' : 'none';
    if (lbNext) lbNext.style.display = lbImgData.length > 1 ? '' : 'none';

    setLbThumbActive(idx);
  }

  function openLightbox(idx) {
    lbShowImage(idx || 0);
    lb.classList.add('open');
    lb.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lb.classList.remove('open');
    lb.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }

  function lbNavigate(dir) {
    var next = (lbCurrent + dir + lbImgData.length) % lbImgData.length;
    lbShowImage(next);
  }

  // Wire controls
  if (lbClose)   lbClose.addEventListener('click', closeLightbox);
  if (lbBackdrop) lbBackdrop.addEventListener('click', closeLightbox);
  if (lbPrev)    lbPrev.addEventListener('click', function() { lbNavigate(-1); });
  if (lbNext)    lbNext.addEventListener('click', function() { lbNavigate(+1); });

  // Keyboard
  document.addEventListener('keydown', function(e) {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  lbNavigate(-1);
    if (e.key === 'ArrowRight') lbNavigate(+1);
  });

  // Touch swipe in lightbox
  var lbTX = 0;
  lb.addEventListener('touchstart', function(e) { lbTX = e.touches[0].clientX; }, {passive:true});
  lb.addEventListener('touchend',   function(e) {
    var dx = e.changedTouches[0].clientX - lbTX;
    if (Math.abs(dx) > 50) lbNavigate(dx < 0 ? 1 : -1);
  });

  // Override: zoom button and main image click
  var zoomBtn = document.getElementById('aprodZoomBtn');
  var imgWrap = document.getElementById('aprodImgWrap');
  var imgHint = document.querySelector('.aprod-img-hint');

  if (zoomBtn) { zoomBtn.onclick = function(e) { e.stopPropagation(); openLightbox(lbCurrent); }; }
  if (imgWrap) { imgWrap.onclick = function() { openLightbox(lbCurrent); }; }
  if (imgHint) { imgHint.onclick = function() { openLightbox(lbCurrent); }; }

  // Sync when main gallery thumb is clicked
  var mainThumbs = document.querySelectorAll('#aprodThumbs .aprod-thumb');
  mainThumbs.forEach(function(btn, i) {
    btn.addEventListener('click', function() { lbCurrent = i; });
  });

  // Expose for external use
  window.aurixOpenLightbox  = openLightbox;
  window.aurixCloseLightbox = closeLightbox;

})(typeof jQuery !== 'undefined' ? jQuery : null);

/* ════════════════════════════════════════════════════
   v12: FAQ Accordion + Ask a Question form
════════════════════════════════════════════════════ */
(function($) {
  // FAQ accordion
  document.querySelectorAll('.aprod-faq-q').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var item   = btn.closest('.aprod-faq-item');
      var answer = item.querySelector('.aprod-faq-a');
      var isOpen = btn.getAttribute('aria-expanded') === 'true';

      // Close all
      document.querySelectorAll('.aprod-faq-q').forEach(function(b) {
        b.setAttribute('aria-expanded','false');
        var a = b.closest('.aprod-faq-item').querySelector('.aprod-faq-a');
        if (a) a.classList.remove('open');
      });

      // Open clicked if it was closed
      if (!isOpen) {
        btn.setAttribute('aria-expanded','true');
        if (answer) answer.classList.add('open');
      }
    });
  });

  // FAQ ask form submit
  var faqForm = document.getElementById('aprodFaqForm');
  if (faqForm && typeof $ !== 'undefined') {
    faqForm.addEventListener('submit', function(e) {
      e.preventDefault();
      var submitBtn = faqForm.querySelector('.aprod-faq-submit');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending…'; }

      var data = new FormData(faqForm);
      data.append('action', 'aurix_faq_submit');
      if (typeof aurixData !== 'undefined') data.append('nonce', aurixData.nonce);

      $.ajax({
        url: typeof aurixData !== 'undefined' ? aurixData.ajaxUrl : '/wp-admin/admin-ajax.php',
        type:'POST', data:data, processData:false, contentType:false,
        success: function() {
          faqForm.innerHTML = '<div style="text-align:center;padding:28px 20px">'
            + '<i class="fas fa-check-circle" style="font-size:2.5rem;color:#22c55e;display:block;margin-bottom:10px"></i>'
            + '<h4 style="font-family:Cormorant Garamond,serif;font-size:1.25rem;color:var(--navy);margin-bottom:6px">Question Sent!</h4>'
            + '<p style="color:var(--muted);font-size:.88rem;">We\'ll get back to you within 24 hours.</p>'
            + '</div>';
        },
        error: function() {
          if (submitBtn) { submitBtn.disabled=false; submitBtn.innerHTML='<i class="fas fa-paper-plane"></i> Send Question'; }
          alert('Failed to send. Please try again or contact us directly.');
        }
      });
    });
  }
})(typeof jQuery !== 'undefined' ? jQuery : null);

/* ════════════════════════════════════════════════════
   v14: QR Code Modal — generate, download, share, copy
════════════════════════════════════════════════════ */
(function() {
  var qrBtn     = document.getElementById('aprodQrBtn');
  var qrOverlay = document.getElementById('aprodQrOverlay');
  var qrClose   = document.getElementById('aprodQrClose');
  var qrCanvas  = document.getElementById('aprodQrCanvas');
  var qrLoading = document.getElementById('aprodQrLoading');
  var qrUrlEl   = document.getElementById('aprodQrUrl');
  var qrDl      = document.getElementById('aprodQrDownload');
  var qrShare   = document.getElementById('aprodQrShare');
  var qrCopyUrl = document.getElementById('aprodQrCopyUrl');

  if (!qrBtn || !qrOverlay || !qrCanvas) return;

  var productUrl = qrUrlEl ? qrUrlEl.textContent.trim() : window.location.href;
  var productTitle = document.querySelector('.aprod-title') ? document.querySelector('.aprod-title').textContent.trim() : document.title;
  var qrGenerated = false;
  var qrInstance  = null;

  function openQr() {
    qrOverlay.classList.add('open');
    qrOverlay.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
    generateQr();
  }

  function closeQr() {
    qrOverlay.classList.remove('open');
    qrOverlay.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }

  function generateQr() {
    if (qrGenerated) return;
    qrGenerated = true;

    // Clear canvas
    var ctx = qrCanvas.getContext('2d');
    ctx.clearRect(0, 0, 240, 240);

    if (typeof QRCode !== 'undefined') {
      // Use qrcodejs library
      qrCanvas.style.display = 'none';
      var tempDiv = document.createElement('div');
      tempDiv.style.cssText = 'position:absolute;left:-9999px;width:240px;height:240px;';
      document.body.appendChild(tempDiv);

      try {
        new QRCode(tempDiv, {
          text:          productUrl,
          width:         240,
          height:        240,
          colorDark:     '#0d1b2a',
          colorLight:    '#ffffff',
          correctLevel:  QRCode.CorrectLevel.M
        });

        // QRCode creates a canvas or img inside tempDiv
        setTimeout(function() {
          var qrImg  = tempDiv.querySelector('img');
          var qrCvs  = tempDiv.querySelector('canvas');
          var srcUrl = '';

          if (qrCvs) {
            srcUrl = qrCvs.toDataURL('image/png');
          } else if (qrImg) {
            srcUrl = qrImg.src;
          }

          if (srcUrl) {
            var finalImg = new Image();
            finalImg.onload = function() {
              ctx.fillStyle = '#fff';
              ctx.fillRect(0, 0, 240, 240);
              ctx.drawImage(finalImg, 0, 0, 240, 240);
              qrCanvas.style.display = 'block';
              if (qrLoading) qrLoading.classList.add('hidden');
            };
            finalImg.src = srcUrl;
          }
          document.body.removeChild(tempDiv);
        }, 120);

      } catch(e) {
        document.body.removeChild(tempDiv);
        drawFallbackQr(ctx);
      }

    } else {
      // Fallback: draw a simple placeholder pattern
      drawFallbackQr(ctx);
    }
  }

  function drawFallbackQr(ctx) {
    // Draw a simple message if QRCode library fails
    ctx.fillStyle = '#f5f1eb';
    ctx.fillRect(0, 0, 240, 240);
    ctx.fillStyle = '#b8925a';
    ctx.font = 'bold 13px Outfit,sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('QR code for:', 120, 100);
    ctx.fillStyle = '#0d1b2a';
    ctx.font = '11px Outfit,sans-serif';
    var words = productUrl.split('/');
    ctx.fillText(words.slice(-2).join('/'), 120, 120);
    qrCanvas.style.display = 'block';
    if (qrLoading) qrLoading.classList.add('hidden');
  }

  // Download PNG
  if (qrDl) {
    qrDl.addEventListener('click', function() {
      var dataUrl = qrCanvas.toDataURL('image/png');
      var a = document.createElement('a');
      a.href = dataUrl;
      a.download = 'aurix-product-qr.png';
      a.click();
    });
  }

  // Web Share API
  if (qrShare) {
    qrShare.addEventListener('click', function() {
      if (navigator.share) {
        navigator.share({
          title: productTitle,
          text:  'Check out ' + productTitle + ' on Aurix International',
          url:   productUrl
        }).catch(function() {});
      } else {
        // Fallback: copy link
        if (navigator.clipboard) {
          navigator.clipboard.writeText(productUrl).then(function() {
            qrShare.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(function() { qrShare.innerHTML = '<i class="fas fa-share-alt"></i> Share'; }, 2000);
          });
        }
      }
    });
  }

  // Copy URL — uses CSS class for reliable color feedback
  if (qrCopyUrl) {
    qrCopyUrl.addEventListener('click', function() {
      var url = productUrl || window.location.href;
      var doCopy = function() {
        qrCopyUrl.innerHTML = '<i class="fas fa-check"></i> Copied!';
        qrCopyUrl.classList.add('copied');
        setTimeout(function() {
          qrCopyUrl.innerHTML = '<i class="fas fa-link"></i> Copy Link';
          qrCopyUrl.classList.remove('copied');
        }, 2200);
      };
      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(doCopy).catch(function() {
          // Fallback for clipboard permission denied
          var ta = document.createElement('textarea');
          ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
          document.body.appendChild(ta); ta.select();
          try { document.execCommand('copy'); doCopy(); } catch(e) {}
          document.body.removeChild(ta);
        });
      } else {
        var ta = document.createElement('textarea');
        ta.value = url; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); doCopy(); } catch(e) {}
        document.body.removeChild(ta);
      }
    });
  }

  // Open/close
  qrBtn.addEventListener('click', openQr);
  if (qrClose) qrClose.addEventListener('click', closeQr);
  qrOverlay.addEventListener('click', function(e) { if (e.target === qrOverlay) closeQr(); });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && qrOverlay.classList.contains('open')) closeQr();
  });

})();
