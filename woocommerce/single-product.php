<?php
/**
 * Aurix International — woocommerce/single-product.php
 * WooCommerce single product page wrapper.
 * This file is the correct override point — it wraps woocommerce_content().
 * Author: ZaheerAbbas
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<div class="aurix-woo-wrap">
  <div class="aurix-woo-inner">
    <?php woocommerce_content(); ?>
  </div>
</div>

<?php get_footer();
