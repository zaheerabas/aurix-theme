<?php
/**
 * Aurix — woocommerce.php
 * Routes shop/archive pages to our custom layout.
 * Single products and other WC pages use the standard wrapper.
 */
defined('ABSPATH') || exit;

get_header();

if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
    // Shop/category pages — our full-width layout with sidebar
    wc_get_template('archive-product.php');
} else {
    // All other WC pages: cart, checkout, account, single product
    ?>
    <div class="aurix-woo-wrap">
      <div class="aurix-woo-inner">
        <?php woocommerce_content(); ?>
      </div>
    </div>
    <?php
}

get_footer();
