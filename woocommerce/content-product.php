<?php
/**
 * Aurix International — content-product.php v1.0
 * Custom product card: image → title → SKU → price
 * Hover reveals: wishlist, quick-view, add-to-cart bar
 * Author: ZaheerAbbas
 */
defined( 'ABSPATH' ) || exit;

global $product;
if ( ! is_a( $product, 'WC_Product' ) ) return;

$sku        = $product->get_sku();
$rating     = $product->get_average_rating();
$rcount     = $product->get_review_count();
$in_stock   = $product->is_in_stock();
$is_sale    = $product->is_on_sale();
$wishlist   = function_exists( 'aurix_get_wishlist' ) ? aurix_get_wishlist() : [];
$in_wl      = in_array( $product->get_id(), $wishlist, true );
$thumb_id   = $product->get_image_id();
$cats       = wp_strip_all_tags( wc_get_product_category_list( $product->get_id(), ', ' ) );
$short_desc = $product->get_short_description();
?>

<li <?php wc_product_class( 'acard', $product ); ?>>
  <div class="acard-inner">

    <!-- ══ IMAGE AREA ══ -->
    <div class="acard-img-wrap">

      <!-- Image -->
      <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="acard-img-link" tabindex="-1" aria-hidden="true">
        <?php if ( $thumb_id ) :
          echo wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail', false, [
            'class'   => 'acard-img',
            'loading' => 'lazy',
            'alt'     => esc_attr( $product->get_name() ),
          ] );
        else : ?>
          <div class="acard-no-img"><i class="fas fa-image"></i></div>
        <?php endif; ?>
      </a>

      <!-- Badges: top-left -->
      <div class="acard-badges">
        <?php if ( $is_sale ) :
          $regular = (float) $product->get_regular_price();
          $sale    = (float) $product->get_sale_price();
          $pct     = ( $regular && $sale ) ? round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0;
        ?>
          <span class="acard-badge acard-badge--sale">
            <?php echo $pct ? '−' . esc_html( $pct ) . '%' : 'Sale'; ?>
          </span>
        <?php endif; ?>
        <?php if ( ! $in_stock ) : ?>
          <span class="acard-badge acard-badge--oos">Out of Stock</span>
        <?php endif; ?>
      </div>

      <!-- Hover overlay: wishlist + quick-view -->
      <div class="acard-overlay" aria-hidden="true">
        <!-- Wishlist button (top-right) -->
        <button class="acard-wl-btn aurix-wl-btn <?php echo $in_wl ? 'in-wishlist' : ''; ?>"
                data-id="<?php echo esc_attr( $product->get_id() ); ?>"
                aria-label="<?php echo $in_wl ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
          <i class="fas fa-heart"></i>
        </button>
        <!-- Quick view button (center) -->
        <button class="acard-qv-btn"
                data-id="<?php echo esc_attr( $product->get_id() ); ?>"
                data-url="<?php echo esc_attr( $product->get_permalink() ); ?>"
                data-title="<?php echo esc_attr( $product->get_name() ); ?>"
                aria-label="Quick view <?php echo esc_attr( $product->get_name() ); ?>">
          <i class="fas fa-eye"></i> Quick View
        </button>
      </div>

      <!-- Hover ATC bar (slides up from bottom of image) -->
      <?php if ( $in_stock ) : ?>
      <div class="acard-atc-bar" aria-hidden="true">
        <form method="post" action="<?php echo esc_url( $product->get_permalink() ); ?>" class="acard-atc-form cart">
          <div class="acard-atc-qty">
            <button type="button" class="acard-qty-btn acard-qty-minus" aria-label="Decrease">−</button>
            <input type="number" name="quantity" value="1" min="1"
                   <?php if ( $product->managing_stock() && $product->get_max_purchase_quantity() > 0 ) echo 'max="' . esc_attr( $product->get_max_purchase_quantity() ) . '"'; ?>
                   class="acard-qty-input" step="1" />
            <button type="button" class="acard-qty-btn acard-qty-plus" aria-label="Increase">+</button>
          </div>
          <button type="submit"
                  name="add-to-cart"
                  value="<?php echo esc_attr( $product->get_id() ); ?>"
                  class="acard-atc-submit single_add_to_cart_button button alt">
            <i class="fas fa-shopping-cart"></i>
            <span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
          </button>
        </form>
      </div>
      <?php else : ?>
      <div class="acard-atc-bar acard-atc-bar--oos" aria-hidden="true">
        <span class="acard-oos-label"><i class="fas fa-clock"></i> Out of Stock — <a href="<?php echo esc_url( $product->get_permalink() ); ?>">Request Quote</a></span>
      </div>
      <?php endif; ?>

    </div><!-- .acard-img-wrap -->

    <!-- ══ CARD BODY ══ -->
    <div class="acard-body">

      <!-- Category -->
      <?php if ( $cats ) : ?>
      <div class="acard-cat"><?php echo esc_html( $cats ); ?></div>
      <?php endif; ?>

      <!-- Title -->
      <h3 class="acard-title">
        <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
          <?php echo esc_html( $product->get_name() ); ?>
        </a>
      </h3>

      <!-- SKU -->
      <?php if ( $sku ) : ?>
      <div class="acard-sku">
        <i class="fas fa-barcode"></i> <?php echo esc_html( $sku ); ?>
      </div>
      <?php endif; ?>

      <!-- Star rating -->
      <?php if ( $rcount > 0 ) : ?>
      <div class="acard-rating">
        <?php echo wc_get_rating_html( $rating ); ?>
        <span class="acard-rating-count">(<?php echo esc_html( $rcount ); ?>)</span>
      </div>
      <?php endif; ?>

      <!-- Price -->
      <div class="acard-price">
        <?php echo $product->get_price_html(); ?>
      </div>

    </div><!-- .acard-body -->

  </div><!-- .acard-inner -->
</li>
