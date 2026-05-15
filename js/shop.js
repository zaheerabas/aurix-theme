/**
 * Aurix International — shop.js v2.0
 * Author: ZaheerAbbas
 * Handles: sidebar toggle, per-page selector, view toggle,
 *          card qty buttons, ATC feedback, quick view modal
 */
(function ($) {
  'use strict';

  /* ── SIDEBAR TOGGLE (mobile) ── */
  var $sidebar  = $('#aurixShopSidebar');
  var $overlay  = $('#aurixSbOverlay');
  var $toggle   = $('#aurixFilterToggle');
  var $sbClose  = $('#aurixSbClose');

  function openSidebar() {
    $sidebar.addClass('open');
    $overlay.addClass('open');
    $('body').css('overflow','hidden');
  }
  function closeSidebar() {
    $sidebar.removeClass('open');
    $overlay.removeClass('open');
    $('body').css('overflow','');
  }
  $toggle.on('click', openSidebar);
  $sbClose.on('click', closeSidebar);
  $overlay.on('click', closeSidebar);

  /* ── PER-PAGE SELECTOR ── */
  $('#astPerpage').on('click', '.ast-pp-btn', function() {
    var n = $(this).data('perpage');
    // Set cookie and reload with per_page param
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', n);
    url.searchParams.delete('paged'); // reset to page 1
    window.location.href = url.toString();
  });

  /* ── VIEW TOGGLE (grid / list) ── */
  var $grid     = $('#aurixProductGrid');
  var savedView = localStorage.getItem('aurix_shop_view') || 'grid';

  function setView(v) {
    if (v === 'list') {
      $grid.addClass('aurix-list-view');
    } else {
      $grid.removeClass('aurix-list-view');
    }
    $('#astViewToggle .ast-view-btn').each(function() {
      $(this).toggleClass('active', $(this).data('view') === v);
    });
    localStorage.setItem('aurix_shop_view', v);
    // Mobile: always show ATC bar
    if (window.innerWidth <= 600) {
      $('.acard-atc-bar').css('transform','translateY(0)');
      $('.acard-overlay').hide();
    }
  }
  // Apply saved view on load
  if ($grid.length) setView(savedView);

  $('#astViewToggle').on('click', '.ast-view-btn', function() {
    setView($(this).data('view'));
  });

  /* ── CARD QTY BUTTONS ── */
  $(document).on('click', '.acard-qty-minus', function() {
    var inp = $(this).siblings('.acard-qty-input')[0];
    if (!inp) return;
    var v = parseInt(inp.value,10)||1, min = parseInt(inp.min,10)||1;
    if (v > min) inp.value = v - 1;
  });
  $(document).on('click', '.acard-qty-plus', function() {
    var inp = $(this).siblings('.acard-qty-input')[0];
    if (!inp) return;
    var v = parseInt(inp.value,10)||1;
    var max = parseInt(inp.getAttribute('max'),10);
    if (isNaN(max)||max<1||v<max) inp.value = v + 1;
  });

  /* ── ADD TO CART FEEDBACK ── */
  $(document.body).on('added_to_cart', function(e, fragments, hash, btn) {
    var $btn = $(btn);
    if (!$btn.hasClass('acard-atc-submit')) return;
    var origHtml = $btn.html();
    $btn.addClass('atc-success').html('<i class="fas fa-check"></i> <span>Added!</span>');
    setTimeout(function() { $btn.removeClass('atc-success').html(origHtml); }, 2200);
  });

  /* ── QUICK VIEW MODAL ── */
  var $qvOverlay = null;

  function buildQvOverlay() {
    if ($qvOverlay && $qvOverlay.length) return;
    $qvOverlay = $('<div class="aurix-qv-overlay" id="aurixQvOverlay" aria-hidden="true" role="dialog" aria-modal="true"></div>');
    $('body').append($qvOverlay);
    $qvOverlay.on('click', function(e){ if(e.target===this) closeQv(); });
    $(document).on('keydown.aurixqv', function(e){ if(e.key==='Escape') closeQv(); });
  }

  function openQv(pid, purl, ptitle) {
    buildQvOverlay();
    $qvOverlay.html('<div class="aurix-qv-modal"><div class="aurix-qv-loader"><i class="fas fa-circle-notch fa-spin"></i></div></div>');
    $qvOverlay.addClass('open').attr('aria-hidden','false');
    $('body').css('overflow','hidden');

    $.ajax({
      url: (typeof aurixData!=='undefined'?aurixData.ajaxUrl:'/wp-admin/admin-ajax.php'),
      type:'POST',
      data:{ action:'aurix_quick_view', product_id:pid, nonce:(typeof aurixData!=='undefined'?aurixData.nonce:'') },
      success: function(res){ res.success&&res.data ? renderQv(res.data) : (closeQv(), window.location.href=purl); },
      error: function(){ closeQv(); window.location.href=purl; }
    });
  }

  function renderQv(d) {
    var wl = (typeof aurixData!=='undefined'&&aurixData.wishlist)?aurixData.wishlist:[];
    var inWl = wl.indexOf(parseInt(d.id))>-1;
    var gallery = d.gallery || (d.image ? [{large:d.image,full:d.image,thumb:d.image}] : []);

    // Build gallery HTML
    var mainImgHtml = gallery.length
      ? '<img id="qvMainImg" src="'+gallery[0].large+'" alt="'+esc(d.title)+'" loading="lazy" data-current="0">'
      : '<i class="fas fa-image" style="font-size:4rem;color:#dde4ef"></i>';

    var thumbsHtml = '';
    if (gallery.length > 1) {
      thumbsHtml = '<div class="aurix-qv-thumbs">';
      gallery.forEach(function(img, i) {
        thumbsHtml += '<button class="aurix-qv-thumb'+(i===0?' active':'')+'" data-idx="'+i+'" data-src="'+img.large+'" style="border:none;padding:0;cursor:pointer;border-radius:6px;overflow:hidden;width:48px;height:48px;background:#f7f8fb;flex-shrink:0;'+(i===0?'outline:2px solid #b8925a;outline-offset:1px;':'outline:1px solid rgba(13,27,42,.1);')+'">'
          +'<img src="'+img.thumb+'" alt="" loading="lazy" style="width:100%;height:100%;object-fit:contain;padding:3px;display:block;">'
          +'</button>';
      });
      thumbsHtml += '</div>';
    }

    var html = '<div class="aurix-qv-modal">'
      +'<div class="aurix-qv-img-col">'
        +'<div id="qvImgWrap" style="width:100%;display:flex;align-items:center;justify-content:center;min-height:240px;">'+mainImgHtml+'</div>'
        +thumbsHtml
      +'</div>'
      +'<div class="aurix-qv-info-col">'
      +'<button class="aurix-qv-close" id="aurixQvClose" aria-label="Close"><i class="fas fa-times"></i></button>'
      +(d.category?'<div class="aurix-qv-cat">'+esc(d.category)+'</div>':'')
      +'<h2 class="aurix-qv-title">'+esc(d.title)+'</h2>'
      +(d.sku?'<div class="aurix-qv-sku"><i class="fas fa-barcode"></i> '+esc(d.sku)+'</div>':'')
      +'<div class="aurix-qv-price">'+d.price_html+'</div>'
      +(d.short_desc?'<div class="aurix-qv-desc">'+d.short_desc+'</div>':'')
      +'<div class="aurix-qv-actions">'
      +(d.in_stock
        ?'<button class="aurix-qv-atc single_add_to_cart_button" data-product-id="'+d.id+'"><i class="fas fa-shopping-cart"></i> Add to Cart</button>'
        :'<button class="aurix-qv-atc" disabled style="background:#a0aec0;cursor:not-allowed"><i class="fas fa-clock"></i> Out of Stock</button>')
      +'<button class="aurix-qv-wl aurix-wl-btn'+(inWl?' in-wishlist':'')+'" data-id="'+d.id+'" aria-label="Wishlist"><i class="fas fa-heart"></i></button>'
      +'</div>'
      +'<a href="'+d.url+'" class="aurix-qv-view-btn"><i class="fas fa-external-link-alt"></i> View full details</a>'
      +'</div></div>';
    $qvOverlay.html(html);

    // Wire thumbnail clicks
    $qvOverlay.find('.aurix-qv-thumb').on('click', function() {
      var src = $(this).data('src');
      var mainImg = $qvOverlay.find('#qvMainImg');
      mainImg.css('opacity','0.3');
      setTimeout(function() { mainImg.attr('src', src).css('opacity','1'); }, 120);
      $qvOverlay.find('.aurix-qv-thumb').css('outline','1px solid rgba(13,27,42,.1)');
      $(this).css('outline','2px solid #b8925a');
    });

    $('#aurixQvClose').on('click', closeQv);
    $qvOverlay.find('.aurix-qv-atc').on('click', function(){
      var $btn=$(this), pid=$btn.data('product-id'); if(!pid) return;
      $btn.html('<i class="fas fa-circle-notch fa-spin"></i> Adding…').prop('disabled',true);
      $.ajax({ url:aurixData?aurixData.ajaxUrl:'/wp-admin/admin-ajax.php', type:'POST',
        data:{action:'woocommerce_add_to_cart',product_id:pid,quantity:1},
        success:function(){ $btn.html('<i class="fas fa-check"></i> Added!').css('background','#22c55e'); $(document.body).trigger('wc_fragment_refresh'); setTimeout(closeQv,1200); },
        error:function(){ $btn.html('<i class="fas fa-shopping-cart"></i> Add to Cart').prop('disabled',false).css('background',''); }
      });
    });
  }

  function closeQv() {
    if(!$qvOverlay) return;
    $qvOverlay.removeClass('open').attr('aria-hidden','true');
    $('body').css('overflow','');
  }
  function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  $(document).on('click','.acard-qv-btn',function(e){
    e.preventDefault(); e.stopPropagation();
    openQv($(this).data('id'),$(this).data('url'),$(this).data('title'));
  });

  /* ── MOBILE: always show ATC bar ── */
  function mobileCheck(){
    if(window.innerWidth<=600){
      $('.acard-overlay').hide();
      $('.acard-atc-bar').css('transform','translateY(0)');
    } else {
      $('.acard-overlay').css('display','');
      $('.acard-atc-bar').css('transform','');
    }
  }
  mobileCheck();
  $(window).on('resize', mobileCheck);

  /* ── PER PAGE: apply WC posts_per_page via cookie ── */
  // Read per_page from URL and set WC loop option
  (function(){
    var pp = new URLSearchParams(window.location.search).get('per_page');
    if(pp) {
      // Mark active button
      $('.ast-pp-btn').each(function(){
        $(this).toggleClass('active', String($(this).data('perpage'))===String(pp));
      });
    }
  })();

})(jQuery);
