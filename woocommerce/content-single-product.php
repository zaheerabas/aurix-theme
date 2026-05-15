<?php
/**
 * Aurix International — content-single-product.php v7.0
 * Layout: 3-column (gallery | product info | trust sidebar)
 * Author: ZaheerAbbas
 */
defined('ABSPATH') || exit;

global $product;
if ( ! is_a($product,'WC_Product') ) $product = wc_get_product(get_the_ID());
if ( ! $product ) return;

do_action('woocommerce_before_single_product');
if ( post_password_required() ) { echo get_the_password_form(); return; }

/* ── Data ── */
$sku         = $product->get_sku();
$price_html  = $product->get_price_html();
$regular     = (float) $product->get_regular_price();
$sale        = (float) $product->get_sale_price();
$in_stock    = $product->is_in_stock();
$stock_qty   = $product->get_stock_quantity();
$gallery_ids = $product->get_gallery_image_ids();
$thumb_id    = $product->get_image_id();
$all_imgs    = $thumb_id ? array_merge([$thumb_id], (array)$gallery_ids) : (array)$gallery_ids;
$wishlist    = function_exists('aurix_get_wishlist') ? aurix_get_wishlist() : [];
$in_wl       = in_array($product->get_id(), $wishlist, true);
$attributes  = $product->get_attributes();
$cats        = wc_get_product_category_list($product->get_id(), ', ');
$tags        = wc_get_product_tag_list($product->get_id(), ', ');
$weight      = $product->get_weight();
$dims        = $product->get_dimensions(false);
$sale_pct    = ($product->is_on_sale() && $regular && $sale) ? round((($regular - $sale) / $regular) * 100) : 0;

/* ── Breadcrumb terms ── */
$terms = get_the_terms(get_the_ID(), 'product_cat');
$term_links = [];
if ($terms && !is_wp_error($terms)) {
    foreach ($terms as $term)
        $term_links[] = '<a href="'.esc_url(get_term_link($term)).'">'.esc_html($term->name).'</a>';
}
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class('aprod-article', $product); ?>>

<!-- ── BREADCRUMB ── -->
<nav class="aprod-breadcrumb" aria-label="Breadcrumb">
  <a href="<?php echo esc_url(home_url('/')); ?>"><i class="fas fa-home"></i></a>
  <i class="fas fa-chevron-right aprod-bc-sep"></i>
  <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Products</a>
  <?php foreach ($term_links as $tl) : ?>
    <i class="fas fa-chevron-right aprod-bc-sep"></i><?php echo $tl; ?>
  <?php endforeach; ?>
  <i class="fas fa-chevron-right aprod-bc-sep"></i>
  <span class="aprod-bc-current"><?php the_title(); ?></span>
</nav>

<!-- ── 3-COLUMN MAIN LAYOUT ── -->
<div class="aprod-main">

  <!-- ══ COL 1: GALLERY ══ -->
  <div class="aprod-gallery" id="aprodGallery">

    <!-- Main image -->
    <div class="aprod-gallery-main" id="aprodMainImg">
      <?php if ($sale_pct) : ?>
      <div class="aprod-sale-badge">−<?php echo esc_html($sale_pct); ?>%</div>
      <?php endif; ?>
      <button class="aprod-zoom-btn" id="aprodZoomBtn" aria-label="View full size" title="Click to zoom">
        <i class="fas fa-expand-alt"></i>
      </button>
      <div class="aprod-img-wrap" id="aprodImgWrap">
        <?php if ($thumb_id) :
          $full_url = wp_get_attachment_image_url($thumb_id, 'full');
          $large_url = wp_get_attachment_image_url($thumb_id, 'woocommerce_large');
          ?>
          <img
            src="<?php echo esc_url($large_url); ?>"
            id="aprodMainImage"
            class="aprod-main-img"
            alt="<?php echo esc_attr(get_the_title()); ?>"
            data-full="<?php echo esc_attr($full_url); ?>"
            loading="eager"
          />
        <?php else : ?>
          <div class="aprod-no-img"><i class="fas fa-image"></i><span>No image</span></div>
        <?php endif; ?>
      </div>
      <div class="aprod-img-hint"><i class="fas fa-search-plus"></i> Click to zoom</div>
    </div>

    <!-- Thumbnail strip — vertical on desktop -->
    <?php if (count($all_imgs) > 1) : ?>
    <div class="aprod-thumb-wrap">
      <button class="aprod-thumb-nav aprod-thumb-prev" id="aprodThumbPrev" aria-label="Previous image">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div class="aprod-thumbs-track" id="aprodThumbsTrack">
        <div class="aprod-thumbs" id="aprodThumbs" role="list">
          <?php foreach ($all_imgs as $i => $img_id) :
            $tsrc  = wp_get_attachment_image_url($img_id, 'thumbnail');
            $lsrc  = wp_get_attachment_image_url($img_id, 'woocommerce_large');
            $fsrc  = wp_get_attachment_image_url($img_id, 'full');
            $alt   = get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: get_the_title();
            ?>
          <button class="aprod-thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                  role="listitem"
                  data-idx="<?php echo esc_attr($i); ?>"
                  data-src="<?php echo esc_attr($lsrc); ?>"
                  data-full="<?php echo esc_attr($fsrc); ?>"
                  data-alt="<?php echo esc_attr($alt); ?>"
                  aria-label="View image <?php echo esc_attr($i + 1); ?>"
                  tabindex="0">
            <img src="<?php echo esc_attr($tsrc); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="aprod-thumb-nav aprod-thumb-next" id="aprodThumbNext" aria-label="Next image">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
    <?php endif; ?>

  </div>

  <!-- ══ COL 2: PRODUCT INFO ══ -->
  <div class="aprod-info">

    <!-- Status badges -->
    <div class="aprod-badges">
      <?php if ($in_stock) : ?>
        <span class="aprod-badge aprod-badge--stock aprod-badge-tip"
              data-tip="This product is <?php echo $stock_qty ? 'available with '.esc_attr($stock_qty).' units' : 'available'; ?> and ready to dispatch. Orders placed before 3pm ship same business day.">
          <i class="fas fa-check-circle"></i>
          <?php echo $stock_qty ? esc_html($stock_qty).' in stock' : 'In Stock'; ?>
        </span>
      <?php else : ?>
        <span class="aprod-badge aprod-badge--oos aprod-badge-tip"
              data-tip="This item is currently out of stock. Contact us to be notified when it is available, or request a wholesale quote.">
          <i class="fas fa-times-circle"></i> Out of Stock
        </span>
      <?php endif; ?>
      <span class="aprod-badge aprod-badge--cert aprod-badge-tip"
            data-tip="ISO 9001 is the international standard for quality management. All Aurix instruments are CE marked and manufactured under ISO 9001:2015 certified conditions in Sialkot, Pakistan.">
        <i class="fas fa-award"></i> ISO 9001
      </span>
      <span class="aprod-badge aprod-badge--ship aprod-badge-tip"
            data-tip="Orders are processed and dispatched within 2 business days. Worldwide delivery via DHL or FedEx with full tracking. Standard transit: 7–14 days. Express: 3–5 days.">
        <i class="fas fa-shipping-fast"></i> Ships in 2 days
      </span>
    </div>

    <h1 class="aprod-title"><?php the_title(); ?></h1>

    <!-- Meta row: SKU only -->
    <?php if ($sku) : ?>
    <div class="aprod-meta-row">
      <span class="aprod-sku-pill"><i class="fas fa-barcode"></i> <?php echo esc_html($sku); ?></span>
    </div>
    <?php endif; ?>

    <!-- Rating -->
    <?php if ($product->get_review_count() > 0) : ?>
    <div class="aprod-rating">
      <?php echo wc_get_rating_html($product->get_average_rating()); ?>
      <a href="#aprod-tab-reviews" class="aprod-rating-link" onclick="switchProdTab('reviews');return false;">
        <?php echo esc_html($product->get_review_count()); ?> reviews
      </a>
    </div>
    <?php endif; ?>

    <!-- Price -->
    <div class="aprod-price-block">
      <?php echo $price_html; ?>
      <?php if ($sale_pct) :
        $savings = number_format($regular - $sale, 2);
      ?>
        <span class="aprod-save-chip">Save $<?php echo esc_html($savings); ?></span>
      <?php endif; ?>
    </div>

    <!-- Category only -->
    <?php if ($cats) : ?>
    <div class="aprod-cat-tags-block">
      <div class="aprod-ct-row">
        <span class="aprod-ct-label"><i class="fas fa-folder-open"></i> Category</span>
        <span class="aprod-ct-value"><?php echo wp_kses_post($cats); ?></span>
      </div>
    </div>
    <?php endif; ?>

    <!-- Short description with truncation + view more -->
    <?php if ($product->get_short_description()) : ?>
    <div class="aprod-short-desc-wrap">
      <div class="aprod-short-desc" id="aprodShortDesc">
        <?php echo wp_kses_post($product->get_short_description()); ?>
      </div>
      <button class="aprod-view-more" id="aprodViewMore" aria-expanded="false">
        View more <i class="fas fa-chevron-down"></i>
      </button>
    </div>
    <?php endif; ?>

    <div class="aprod-sep"></div>

    <!-- ADD TO CART FORM -->
    <?php do_action('woocommerce_before_add_to_cart_form'); ?>
    <form class="aprod-cart-form cart"
          action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
          method="post" enctype="multipart/form-data">

      <?php do_action('woocommerce_before_add_to_cart_button'); ?>

      <!-- Variable product attributes -->
      <?php if ($product->is_type('variable')) :
        $available_variations = $product->get_available_variations();
        $attributes_data = $product->get_variation_attributes();
        foreach ($attributes_data as $attr_name => $attr_options) :
          $attr_label = wc_attribute_label($attr_name);
          ?>
          <div class="aprod-variation-row">
            <label class="aprod-var-label"><?php echo esc_html($attr_label); ?>:</label>
            <div class="aprod-var-options">
              <?php wc_dropdown_variation_attribute_options(['options'=>$attr_options,'attribute'=>$attr_name,'product'=>$product]); ?>
            </div>
          </div>
        <?php endforeach;
        do_action('woocommerce_before_variations_form');
        do_action('woocommerce_after_variations_form');
      endif; ?>

      <!-- Qty + ATC row -->
      <div class="aprod-buy-row" id="aprodBuyRow">
        <div class="aprod-qty-block">
          <div class="aprod-qty-ctrl">
            <button type="button" class="aprod-qty-btn aprod-qty-minus" aria-label="Decrease">
              <i class="fas fa-minus"></i>
            </button>
            <?php
            $qty_min = apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product);
            $qty_max = apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product);
            // Only set max if stock is actually managed and max is valid positive number
            $qty_max_attr = ($product->managing_stock() && $qty_max > 0) ? 'max="'.esc_attr($qty_max).'"' : '';
            ?>
            <input type="number" id="aprod-qty" class="qty" name="quantity"
                   value="<?php echo esc_attr(apply_filters('woocommerce_quantity_input_value',1,$product)); ?>"
                   min="1"
                   <?php echo $qty_max_attr; ?>
                   step="1" aria-label="Quantity" />
            <button type="button" class="aprod-qty-btn aprod-qty-plus" aria-label="Increase">
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </div>

        <div class="aprod-atc-group">
          <?php if ($in_stock) : ?>
            <button type="submit"
                    name="add-to-cart"
                    value="<?php echo esc_attr($product->get_id()); ?>"
                    class="aprod-atc-btn single_add_to_cart_button button alt">
              <i class="fas fa-shopping-cart"></i>
              <span class="aprod-atc-text"><?php echo esc_html($product->single_add_to_cart_text()); ?></span>
            </button>
          <?php else : ?>
            <button type="button" class="aprod-atc-btn aprod-atc-btn--oos" disabled>
              <i class="fas fa-clock"></i> Out of Stock
            </button>
          <?php endif; ?>

          <button type="button"
                  class="aprod-wl-btn aurix-wl-btn <?php echo $in_wl ? 'in-wishlist' : ''; ?>"
                  data-id="<?php echo esc_attr($product->get_id()); ?>"
                  aria-label="<?php echo $in_wl ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
            <i class="fas fa-heart"></i>
          </button>
        </div>
      </div>

      <?php do_action('woocommerce_after_add_to_cart_button'); ?>
      <?php wp_nonce_field('woocommerce-cart','woocommerce-add-to-cart-nonce'); ?>
    </form>
    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

    <!-- RFQ Button -->
    <button type="button" class="aprod-rfq-btn" id="aprodRfqBtn">
      <i class="fas fa-file-invoice-dollar"></i> Request Wholesale / Bulk Quote
    </button>

    <!-- Share -->
    <div class="aprod-share">
      <span class="aprod-share-label">Share:</span>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode(get_permalink()); ?>"
         target="_blank" rel="noopener" class="aprod-share-btn sh-fb" aria-label="Facebook">
        <i class="fab fa-facebook-f"></i>
      </a>
      <a href="https://wa.me/?text=<?php echo rawurlencode(get_the_title().' — '.get_permalink()); ?>"
         target="_blank" rel="noopener" class="aprod-share-btn sh-wa" aria-label="WhatsApp">
        <i class="fab fa-whatsapp"></i>
      </a>
      <a href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode(get_permalink()); ?>&text=<?php echo rawurlencode(get_the_title()); ?>"
         target="_blank" rel="noopener" class="aprod-share-btn sh-x" aria-label="X">
        <i class="fab fa-x-twitter"></i>
      </a>
      <button class="aprod-share-btn sh-copy" id="aprodCopyLink" aria-label="Copy link">
        <i class="fas fa-link"></i>
      </button>
    </div>

    <!-- Policies note -->
    <div class="aprod-policies-note">
      <i class="fas fa-info-circle"></i>
      <span>Review our <a href="<?php echo esc_url(home_url('/shipping')); ?>">Shipping</a>, <a href="<?php echo esc_url(home_url('/returns')); ?>">Returns</a> &amp; <a href="<?php echo esc_url(home_url('/payment-info')); ?>">Payment</a> policies before purchasing.</span>
    </div>

    <!-- QR Code trigger button -->
    <button class="aprod-qr-btn" id="aprodQrBtn" type="button">
      <i class="fas fa-qrcode"></i> Product QR Code
    </button>
  </div>


  <!-- QR Code Modal -->
  <div class="aprod-qr-overlay" id="aprodQrOverlay" aria-hidden="true">
    <div class="aprod-qr-modal" role="dialog" aria-modal="true">
      <div class="aprod-qr-header">
        <h3><i class="fas fa-qrcode"></i> Product QR Code</h3>
        <button class="aprod-qr-close" id="aprodQrClose" aria-label="Close"><i class="fas fa-times"></i></button>
      </div>
      <div class="aprod-qr-body">
        <div class="aprod-qr-canvas-wrap">
          <canvas id="aprodQrCanvas" width="240" height="240"></canvas>
          <div class="aprod-qr-loading" id="aprodQrLoading"><i class="fas fa-circle-notch fa-spin"></i></div>
        </div>
        <p class="aprod-qr-url" id="aprodQrUrl"><?php the_permalink(); ?></p>
        <p class="aprod-qr-hint">Scan with any camera app to view this product</p>
        <div class="aprod-qr-actions">
          <button class="aprod-qr-action-btn" id="aprodQrDownload"><i class="fas fa-download"></i> Download PNG</button>
          <button class="aprod-qr-action-btn aprod-qr-action-btn--share" id="aprodQrShare"><i class="fas fa-share-alt"></i> Share</button>
          <button class="aprod-qr-action-btn aprod-qr-action-btn--copy" id="aprodQrCopyUrl"><i class="fas fa-link"></i> Copy Link</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ COL 3: TRUST SIDEBAR ══ -->
  <aside class="aprod-sidebar">

    <!-- Contact CTA card — blue phone + red chat like reference -->
    <div class="aprod-sb-card aprod-sb-contact">
      <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',get_theme_mod('aurix_phone','+1 (234) 567-8900'))); ?>"
         class="aprod-sb-call-btn">
        <i class="fas fa-phone-alt"></i>
        <?php echo esc_html(get_theme_mod('aurix_phone','+1 (234) 567-8900')); ?>
      </a>
      <a href="<?php echo esc_url(home_url('/contact')); ?>" class="aprod-sb-chat-btn">
        <i class="fas fa-comment-dots"></i> Chat Now
      </a>
    </div>

    <!-- Returns & Warranty with ⓘ tooltip -->
    <div class="aprod-sb-card">
      <div class="aprod-sb-card-head">
        <span class="aprod-sb-head-text"><b>Returns &amp; Warranty</b></span>
        <span class="aprod-sb-info-wrap">
          <button class="aprod-sb-info-btn" aria-label="About returns and warranty" data-tooltip="We offer a 5-year quality guarantee on all instruments, free repair service, and a 30-day hassle-free return policy. ISO 9001 &amp; CE certified.">
            <i class="fas fa-info-circle"></i>
          </button>
          <span class="aprod-sb-tooltip" role="tooltip"></span>
        </span>
      </div>
      <ul class="aprod-sb-list">
        <li><i class="fas fa-check-circle"></i> German Made Quality</li>
        <li><i class="fas fa-check-circle"></i> Free Repair Service</li>
        <li><i class="fas fa-check-circle"></i> 5 Years Warranty</li>
      </ul>
    </div>

    <!-- Delivery with ⓘ tooltip -->
    <div class="aprod-sb-card">
      <div class="aprod-sb-card-head">
        <span class="aprod-sb-head-text"><b>Delivery</b></span>
        <span class="aprod-sb-info-wrap">
          <button class="aprod-sb-info-btn" aria-label="About delivery" data-tooltip="We ship worldwide via DHL and FedEx. Orders over $500 qualify for free shipping. Standard delivery 7–14 business days. Express 3–5 days available on request.">
            <i class="fas fa-info-circle"></i>
          </button>
          <span class="aprod-sb-tooltip" role="tooltip"></span>
        </span>
      </div>
      <ul class="aprod-sb-list">
        <li><i class="fas fa-check-circle"></i> Free Delivery on orders $500+</li>
        <li><i class="fas fa-check-circle"></i> Worldwide — DHL / FedEx</li>
        <li><i class="fas fa-check-circle"></i> 7–14 business days</li>
      </ul>
      <!-- Shipping logos -->
      <div class="aprod-sb-ship-logos">
        <div class="aprod-ship-logo aprod-ship-ups">UPS</div>
        <div class="aprod-ship-logo aprod-ship-usps">USPS</div>
      </div>
    </div>

    <!-- Payments with ⓘ tooltip -->
    <div class="aprod-sb-card">
      <div class="aprod-sb-card-head">
        <span class="aprod-sb-head-text"><b>Payments</b></span>
        <span class="aprod-sb-info-wrap">
          <button class="aprod-sb-info-btn" aria-label="About payments" data-tooltip="We accept all major credit cards, PayPal, and bank transfers. All transactions are secured with 256-bit SSL encryption.">
            <i class="fas fa-info-circle"></i>
          </button>
          <span class="aprod-sb-tooltip" role="tooltip"></span>
        </span>
      </div>
      <div class="aprod-sb-payments">
        <div class="aprod-pay-chip p-visa">VISA</div>
        <div class="aprod-pay-chip p-mc"><div class="mc-w"><div class="mc-c mc-l"></div><div class="mc-c mc-r"></div></div></div>
        <div class="aprod-pay-chip p-pp"><span style="color:#003087;font-weight:700;font-size:.75rem">Pay</span><span style="color:#009CDE;font-weight:700;font-size:.75rem">Pal</span></div>
        <div class="aprod-pay-chip p-disc"><span style="color:#231F20;font-size:.7rem;font-weight:700;letter-spacing:-.02em">DIS</span><span style="color:#FF6600;font-size:.7rem;font-weight:700">COVER</span></div>
        <div class="aprod-pay-chip p-amex"><span style="color:#2E77BC;font-size:.68rem;font-weight:700;letter-spacing:.06em">AMEX</span></div>
      </div>
    </div>

  </aside>

</div><!-- .aprod-main -->

<!-- ═══ LIGHTBOX (isolated, outside product layout) ═══ -->
<div class="aprod-lightbox" id="aprodLightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Product image viewer">
  <div class="aprod-lb-backdrop" id="aprodLbBackdrop"></div>
  <!-- Image stage -->
  <div class="aprod-lb-stage">
    <div class="aprod-lb-spinner" id="aprodLbSpinner"></div>
    <img id="aprodLbImage" src="" alt="" loading="lazy">
  </div>
  <!-- Controls -->
  <button class="aprod-lb-close" id="aprodLbClose" aria-label="Close"><i class="fas fa-times"></i></button>
  <button class="aprod-lb-prev" id="aprodLbPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
  <button class="aprod-lb-next" id="aprodLbNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
  <!-- Counter + thumbnail strip inside lightbox -->
  <div class="aprod-lb-footer" id="aprodLbFooter">
    <button class="aprod-lb-thumb-prev" id="aprodLbThPrev"><i class="fas fa-chevron-left"></i></button>
    <div class="aprod-lb-thumbs" id="aprodLbThumbs"></div>
    <button class="aprod-lb-thumb-next" id="aprodLbThNext"><i class="fas fa-chevron-right"></i></button>
    <span class="aprod-lb-counter" id="aprodLbCounter"></span>
  </div>
</div>

<!-- ═══ TABS ═══ -->
<div class="aprod-tabs-wrap" id="aprod-tabs">
  <div class="aprod-tabs-nav" role="tablist">
    <button class="aprod-tab active" onclick="switchProdTab('desc')" role="tab" aria-selected="true" aria-controls="aprod-tab-desc">
      <i class="fas fa-align-left"></i> Description
    </button>
    <button class="aprod-tab" onclick="switchProdTab('specs')" role="tab" aria-selected="false" aria-controls="aprod-tab-specs">
      <i class="fas fa-list-ul"></i> Specifications
    </button>
    <button class="aprod-tab" onclick="switchProdTab('reviews')" role="tab" aria-selected="false" aria-controls="aprod-tab-reviews">
      <i class="fas fa-star"></i> Reviews
      <?php if ($product->get_review_count()) : ?>
        <span class="aprod-tab-count"><?php echo esc_html($product->get_review_count()); ?></span>
      <?php endif; ?>
    </button>
    <button class="aprod-tab" onclick="switchProdTab('shipping')" role="tab" aria-selected="false" aria-controls="aprod-tab-shipping">
      <i class="fas fa-truck"></i> Shipping &amp; Returns
    </button>
    <button class="aprod-tab" onclick="switchProdTab('guides')" role="tab" aria-selected="false" aria-controls="aprod-tab-guides">
      <i class="fas fa-book-open"></i> Guides
    </button>
    <button class="aprod-tab" onclick="switchProdTab('faqs')" role="tab" aria-selected="false" aria-controls="aprod-tab-faqs">
      <i class="fas fa-question-circle"></i> FAQs
    </button>
  </div>

  <div class="aprod-tab-panel active" id="aprod-tab-desc" role="tabpanel">
    <div class="aprod-desc-content-wrap">
      <div class="aprod-desc-content entry-content" id="aprodLongDesc">
        <?php $desc = $product->get_description();
          echo $desc ? wp_kses_post(apply_filters('the_content',$desc)) : '<p class="aprod-no-desc"><i class="fas fa-info-circle"></i> Contact us for full product details and specifications.</p>'; ?>
      </div>
      <button class="aprod-desc-view-more" id="aprodDescViewMore" aria-expanded="false">
        Read full description <i class="fas fa-chevron-down"></i>
      </button>
    </div>
  </div>

  <div class="aprod-tab-panel" id="aprod-tab-specs" role="tabpanel">
    <?php
    /* Build specs rows — ONLY from WooCommerce data, nothing hardcoded */
    $spec_rows = [];

    // Core WooCommerce fields
    if ( $sku )    $spec_rows[] = ['SKU',         esc_html($sku)];
    if ( $cats )   $spec_rows[] = ['Category',    wp_kses_post($cats)];
    if ( $tags )   $spec_rows[] = ['Tags',         wp_kses_post($tags)];
    if ( $weight ) $spec_rows[] = ['Weight',       esc_html($weight) . ' ' . esc_html(get_option('woocommerce_weight_unit'))];
    if ( !empty($dims['length']) || !empty($dims['width']) || !empty($dims['height']) )
        $spec_rows[] = ['Dimensions', esc_html($product->get_dimensions())];

    // ALL custom product attributes added by user in WooCommerce
    foreach ( $attributes as $attr ) {
        if ( $attr->get_variation() ) continue; // skip variation attrs
        $attr_name = wc_attribute_label( $attr->get_name() );
        if ( $attr->is_taxonomy() ) {
            $terms_list = wc_get_product_terms( $product->get_id(), $attr->get_name(), ['fields'=>'names'] );
            $attr_val   = implode(', ', $terms_list);
        } else {
            $attr_val = implode(', ', $attr->get_options());
        }
        if ( $attr_val ) {
            $spec_rows[] = [ esc_html($attr_name), esc_html($attr_val) ];
        }
    }

    if ( $spec_rows ) :
    ?>
    <table class="aprod-specs-table">
      <thead><tr><th colspan="2">Product Specifications</th></tr></thead>
      <tbody>
        <?php foreach ( $spec_rows as $row ) : ?>
        <tr><td><?php echo $row[0]; ?></td><td><?php echo $row[1]; ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else : ?>
    <div class="aprod-no-specs">
      <i class="fas fa-info-circle"></i>
      <p>No specifications added yet. Add attributes to this product in WooCommerce to display them here.</p>
    </div>
    <?php endif; ?>
  </div>

  <div class="aprod-tab-panel" id="aprod-tab-reviews" role="tabpanel">
    <?php if ( $product->get_reviews_allowed() ) :
      $avg    = round( $product->get_average_rating(), 1 );
      $rcount = $product->get_review_count();
      $rating_counts = [];
      for ( $s = 5; $s >= 1; $s-- ) {
          $rating_counts[$s] = get_comments(['post_id'=>$product->get_id(),'count'=>true,'meta_key'=>'rating','meta_value'=>$s,'status'=>'approve','type'=>'review']);
      }
    ?>
    <?php if ( $rcount > 0 ) : ?>
    <!-- Dark rating summary bar -->
    <div class="aprod-review-summary">
      <div class="aprod-rs-score">
        <div class="aprod-rs-num"><?php echo esc_html( $avg ); ?></div>
        <div class="aprod-rs-stars"><?php
          $full = floor($avg); $half = ($avg - $full) >= 0.5;
          for ($i=1;$i<=5;$i++) echo ($i<=$full) ? '★' : ($half&&$i==$full+1 ? '½' : '☆');
        ?></div>
        <div class="aprod-rs-count"><?php echo esc_html($rcount); ?> reviews</div>
      </div>
      <div class="aprod-rs-bars">
        <?php for ( $s = 5; $s >= 1; $s-- ) :
          $cnt = (int)($rating_counts[$s] ?? 0);
          $pct = $rcount > 0 ? round(($cnt/$rcount)*100) : 0;
        ?>
        <div class="aprod-rs-bar-row">
          <span class="aprod-rs-lbl"><?php echo $s; ?>★</span>
          <div class="aprod-rs-bar-track">
            <div class="aprod-rs-bar-fill" style="width:<?php echo esc_attr($pct); ?>%"></div>
          </div>
          <span class="aprod-rs-pct"><?php echo esc_html($pct); ?>%</span>
        </div>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php comments_template(); ?>
    <?php else : ?>
    <p class="aprod-no-reviews"><i class="fas fa-comment-slash"></i> Reviews are disabled for this product.</p>
    <?php endif; ?>
  </div>

  <div class="aprod-tab-panel" id="aprod-tab-shipping" role="tabpanel">
    <div class="aprod-shipping-grid">
      <div class="aprod-ship-card"><div class="aprod-ship-icon"><i class="fas fa-truck"></i></div><div><h4>Standard Shipping</h4><p>7–14 business days via DHL or FedEx. Full tracking included.</p><span class="aprod-ship-tag">Free on orders over $500</span></div></div>
      <div class="aprod-ship-card"><div class="aprod-ship-icon aprod-ship-icon--express"><i class="fas fa-bolt"></i></div><div><h4>Express Shipping</h4><p>3–5 business days. Contact us for a quote.</p><span class="aprod-ship-tag aprod-ship-tag--info">Contact for pricing</span></div></div>
      <div class="aprod-ship-card"><div class="aprod-ship-icon aprod-ship-icon--return"><i class="fas fa-undo-alt"></i></div><div><h4>Returns &amp; Warranty</h4><p>30-day returns. 5-year quality guarantee on all instruments.</p><span class="aprod-ship-tag aprod-ship-tag--success">5-year guarantee</span></div></div>
      <div class="aprod-ship-card"><div class="aprod-ship-icon aprod-ship-icon--wholesale"><i class="fas fa-building"></i></div><div><h4>Wholesale &amp; Bulk</h4><p>Special pricing for distributors ordering 10+ units.</p><a href="<?php echo esc_url(home_url('/get-a-quote')); ?>" class="aprod-ship-tag aprod-ship-tag--gold">Request a quote</a></div></div>
    </div>
  </div>

  <!-- ══ GUIDES TAB ══ -->
  <div class="aprod-tab-panel" id="aprod-tab-guides" role="tabpanel">
    <div class="aprod-guides-wrap">
      <div class="aprod-guides-intro">
        <div class="aprod-guides-icon"><i class="fas fa-book-open"></i></div>
        <div>
          <h3>Product Guides &amp; Catalogue</h3>
          <p>Download our complete surgical and dental instrument catalogue or view instrument usage guides below.</p>
        </div>
      </div>

      <!-- Catalogue download card -->
      <div class="aprod-guide-cards">
        <div class="aprod-guide-card aprod-guide-card--pdf">
          <div class="aprod-guide-card-icon"><i class="fas fa-file-pdf"></i></div>
          <div class="aprod-guide-card-body">
            <h4>Aurix International Catalogue</h4>
            <p>Complete product range including surgical instruments, dental instruments, OEM options, and custom kit configurations.</p>
            <div class="aprod-guide-card-meta">
              <span><i class="fas fa-file-alt"></i> PDF Document</span>
              <span><i class="fas fa-download"></i> Available for download</span>
            </div>
          </div>
          <a href="<?php echo esc_url(get_template_directory_uri().'/images/aurix-catalogue.pdf'); ?>" 
             class="aprod-guide-dl-btn" target="_blank" rel="noopener"
             download>
            <i class="fas fa-download"></i> Download PDF
          </a>
        </div>

        <div class="aprod-guide-card">
          <div class="aprod-guide-card-icon" style="background:rgba(49,130,206,.1);color:#1d4ed8;"><i class="fas fa-play-circle"></i></div>
          <div class="aprod-guide-card-body">
            <h4>Instrument Usage Guide</h4>
            <p>Proper handling, sterilization procedures, and maintenance guidelines for all Aurix surgical and dental instruments.</p>
            <div class="aprod-guide-card-meta">
              <span><i class="fas fa-shield-alt"></i> ISO 9001 standards</span>
              <span><i class="fas fa-sync-alt"></i> Autoclave to 134°C</span>
            </div>
          </div>
          <a href="<?php echo esc_url(home_url('/instrument-guide')); ?>" class="aprod-guide-dl-btn aprod-guide-dl-btn--outline">
            <i class="fas fa-external-link-alt"></i> View Guide
          </a>
        </div>

        <div class="aprod-guide-card">
          <div class="aprod-guide-card-icon" style="background:rgba(139,92,246,.1);color:#7c3aed;"><i class="fas fa-boxes"></i></div>
          <div class="aprod-guide-card-body">
            <h4>OEM &amp; Private Label Guide</h4>
            <p>Information on custom branding, minimum order quantities, lead times, and the private labeling process.</p>
            <div class="aprod-guide-card-meta">
              <span><i class="fas fa-tag"></i> Custom branding</span>
              <span><i class="fas fa-industry"></i> OEM manufacturing</span>
            </div>
          </div>
          <a href="<?php echo esc_url(home_url('/oem-private-label')); ?>" class="aprod-guide-dl-btn aprod-guide-dl-btn--outline">
            <i class="fas fa-external-link-alt"></i> Learn More
          </a>
        </div>
      </div>

      <div class="aprod-guides-contact">
        <i class="fas fa-envelope"></i>
        <span>Need a specific document or custom catalogue? <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact us</a> and we'll send it directly.</span>
      </div>
    </div>
  </div>

  <!-- ══ FAQS TAB ══ -->
  <div class="aprod-tab-panel" id="aprod-tab-faqs" role="tabpanel">
    <div class="aprod-faqs-wrap">

      <!-- Static FAQs -->
      <div class="aprod-faqs-list" id="aprodFaqsList">

        <div class="aprod-faq-item">
          <button class="aprod-faq-q" aria-expanded="false">
            <span>Are all instruments ISO 9001 and CE certified?</span>
            <i class="fas fa-chevron-down aprod-faq-icon"></i>
          </button>
          <div class="aprod-faq-a">
            <p>Yes. All Aurix International instruments are manufactured under ISO 9001:2015 certified quality management systems and carry CE marking, confirming compliance with European health, safety, and environmental protection standards. Our manufacturing facility in Sialkot, Pakistan operates under strict quality control with German-grade 4Cr13 stainless steel.</p>
          </div>
        </div>

        <div class="aprod-faq-item">
          <button class="aprod-faq-q" aria-expanded="false">
            <span>What is your minimum order quantity for wholesale?</span>
            <i class="fas fa-chevron-down aprod-faq-icon"></i>
          </button>
          <div class="aprod-faq-a">
            <p>For wholesale and bulk orders, the minimum order quantity (MOQ) is typically 10 units per product. However, for OEM and private label orders, MOQ may vary depending on the customization required. Please use the <strong>Request Wholesale Quote</strong> button on the product page or <a href="<?php echo esc_url(home_url('/contact')); ?>">contact us</a> directly for a custom quote.</p>
          </div>
        </div>

        <div class="aprod-faq-item">
          <button class="aprod-faq-q" aria-expanded="false">
            <span>How long does international shipping take?</span>
            <i class="fas fa-chevron-down aprod-faq-icon"></i>
          </button>
          <div class="aprod-faq-a">
            <p>Standard international shipping via DHL or FedEx takes <strong>7–14 business days</strong> from dispatch. Express shipping (3–5 business days) is available on request. Orders over $500 qualify for free standard shipping. All shipments include full tracking. Contact us for express shipping rates to your country.</p>
          </div>
        </div>

      </div>

      <!-- Ask a question form -->
      <div class="aprod-faq-ask">
        <div class="aprod-faq-ask-header">
          <i class="fas fa-comment-dots"></i>
          <div>
            <h4>Have a different question?</h4>
            <p>Ask us anything about this product and we'll respond within 24 hours.</p>
          </div>
        </div>
        <form class="aprod-faq-form" id="aprodFaqForm">
          <?php wp_nonce_field('aurix_faq','aurix_faq_nonce'); ?>
          <input type="hidden" name="faq_product" value="<?php the_title_attribute(); ?>">
          <input type="hidden" name="faq_product_url" value="<?php the_permalink(); ?>">
          <div class="aprod-faq-form-row">
            <div class="aprod-faq-field">
              <label>Your Name <span>*</span></label>
              <input type="text" name="faq_name" required placeholder="Dr. John Smith">
            </div>
            <div class="aprod-faq-field">
              <label>Email Address <span>*</span></label>
              <input type="email" name="faq_email" required placeholder="john@clinic.com">
            </div>
          </div>
          <div class="aprod-faq-field">
            <label>Your Question <span>*</span></label>
            <textarea name="faq_question" rows="3" required placeholder="e.g. Is this instrument suitable for pediatric use?"></textarea>
          </div>
          <button type="submit" class="aprod-faq-submit">
            <i class="fas fa-paper-plane"></i> Send Question
          </button>
        </form>
      </div>

    </div>
  </div>

</div>

<!-- ═══ RFQ MODAL ═══ -->
<div class="aprod-rfq-overlay" id="aprodRfqOverlay" aria-hidden="true">
  <div class="aprod-rfq-modal" role="dialog" aria-modal="true">
    <div class="aprod-rfq-header">
      <h3><i class="fas fa-file-invoice-dollar"></i> Request Wholesale Quote</h3>
      <button class="aprod-rfq-close" id="aprodRfqClose" aria-label="Close"><i class="fas fa-times"></i></button>
    </div>
    <div class="aprod-rfq-body">
      <div class="aprod-rfq-product-info">
        <strong><?php the_title(); ?></strong>
        <?php if ($sku) : ?><span>SKU: <?php echo esc_html($sku); ?></span><?php endif; ?>
      </div>
      <form class="aprod-rfq-form" id="aprodRfqForm" method="post">
        <?php wp_nonce_field('aurix_rfq','aurix_rfq_nonce'); ?>
        <input type="hidden" name="rfq_product" value="<?php the_title_attribute(); ?>">
        <input type="hidden" name="rfq_sku"     value="<?php echo esc_attr($sku); ?>">
        <input type="hidden" name="rfq_url"     value="<?php the_permalink(); ?>">
        <div class="aprod-rfq-row">
          <div class="aprod-rfq-field"><label>Full Name <span>*</span></label><input type="text"  name="rfq_name"    required placeholder="Dr. John Smith"></div>
          <div class="aprod-rfq-field"><label>Email <span>*</span></label><input type="email" name="rfq_email"   required placeholder="john@clinic.com"></div>
        </div>
        <div class="aprod-rfq-row">
          <div class="aprod-rfq-field"><label>Company / Clinic</label><input type="text" name="rfq_company" placeholder="Your organization"></div>
          <div class="aprod-rfq-field"><label>Country <span>*</span></label><input type="text" name="rfq_country" required placeholder="United States"></div>
        </div>
        <div class="aprod-rfq-row">
          <div class="aprod-rfq-field"><label>Quantity <span>*</span></label><input type="number" name="rfq_qty" min="1" required placeholder="e.g. 50" value="10"></div>
          <div class="aprod-rfq-field"><label>Target Price (USD)</label><input type="text" name="rfq_price" placeholder="e.g. $120 per unit"></div>
        </div>
        <div class="aprod-rfq-field"><label>Additional Requirements</label><textarea name="rfq_message" rows="3" placeholder="Custom branding, packaging, delivery timeline…"></textarea></div>
        <div class="aprod-rfq-privacy"><i class="fas fa-lock"></i> Your information is secure and used only to respond to your inquiry.</div>
        <button type="submit" class="aprod-rfq-submit"><i class="fas fa-paper-plane"></i> Send Quote Request</button>
      </form>
    </div>
  </div>
</div>

<!-- ═══ RELATED PRODUCTS ═══ -->
<?php
$related_ids = wc_get_related_products($product->get_id(), 4);
if ($related_ids) :
  $related = array_filter(array_map('wc_get_product', $related_ids));
?>
<section class="aprod-related">
  <div class="aprod-related-header">
    <h2>You May Also Need</h2>
    <div class="aprod-related-header-right">
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="aprod-related-link">
        View all <i class="fas fa-arrow-right"></i>
      </a>
      <!-- Mobile carousel arrows -->
      <div class="aprod-rel-nav" id="aprodRelNav">
        <button class="aprod-rel-arrow" id="aprodRelPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
        <button class="aprod-rel-arrow" id="aprodRelNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
  <!-- Related products: desktop 4-col grid, mobile 1-per-view carousel -->
  <div class="aprod-related-scroll" id="aprodRelatedScroll">
    <div class="aprod-related-grid" id="aprodRelatedGrid">
    <?php foreach ($related as $rel) : ?>
    <div class="aprod-rel-card">
      <div class="aprod-rel-img">
        <a href="<?php echo esc_url($rel->get_permalink()); ?>"><?php echo $rel->get_image('woocommerce_thumbnail',['loading'=>'lazy']); ?></a>
        <?php if ($rel->is_on_sale()) : ?><span class="aprod-rel-sale">Sale</span><?php endif; ?>
        <button class="aurix-wl-btn aprod-rel-wl <?php echo in_array($rel->get_id(),$wishlist,true)?'in-wishlist':''; ?>" data-id="<?php echo esc_attr($rel->get_id()); ?>" aria-label="Wishlist"><i class="fas fa-heart"></i></button>
      </div>
      <div class="aprod-rel-body">
        <a href="<?php echo esc_url($rel->get_permalink()); ?>" class="aprod-rel-name"><?php echo esc_html($rel->get_name()); ?></a>
        <?php if ($rel->get_sku()) : ?><span class="aprod-rel-sku"><?php echo esc_html($rel->get_sku()); ?></span><?php endif; ?>
        <div class="aprod-rel-price"><?php echo wp_kses_post($rel->get_price_html()); ?></div>
        <a href="?add-to-cart=<?php echo esc_attr($rel->get_id()); ?>" class="aprod-rel-atc add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo esc_attr($rel->get_id()); ?>">
          <i class="fas fa-cart-plus"></i> Add to Cart
        </a>
      </div>
    </div>
    <?php endforeach; ?>
    </div><!-- .aprod-related-grid -->
  </div><!-- .aprod-related-scroll -->
</section>
<?php endif; ?>

<?php do_action('woocommerce_after_single_product'); ?>
</article>
