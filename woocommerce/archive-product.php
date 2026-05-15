<?php
/**
 * Aurix International — archive-product.php v3.0
 * Author: ZaheerAbbas
 * Fixes: double sort removed, title aligned, sidebar cats only
 */
defined('ABSPATH') || exit;
?>

<div class="aurix-shop-page">

  <!-- Page title + breadcrumb above layout -->
  <div class="aurix-shop-page-head">
    <?php if ( apply_filters('woocommerce_show_page_title', true) ) : ?>
    <h1 class="aurix-shop-title"><?php woocommerce_page_title(); ?></h1>
    <?php endif; ?>
    <?php do_action('woocommerce_archive_description'); ?>
  </div>

  <div class="aurix-shop-layout">

    <!-- ══ SIDEBAR: Categories only ══ -->
    <aside class="aurix-shop-sidebar" id="aurixShopSidebar">
      <div class="asb-header">
        <h3 class="asb-title"><i class="fas fa-th-list"></i> Categories</h3>
        <button class="asb-close-btn" id="aurixSbClose" aria-label="Close"><i class="fas fa-times"></i></button>
      </div>
      <div class="asb-body">
        <ul class="asb-cat-list">
          <!-- All Products -->
          <li class="asb-cat-item <?php echo ( ! is_product_category() ) ? 'active' : ''; ?>">
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id('shop') ) ); ?>">
              <span class="asb-cat-name">All Products</span>
              <span class="asb-cat-count"><?php echo (int) wp_count_posts('product')->publish; ?></span>
            </a>
          </li>
          <?php
          $top_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'orderby' => 'name']);
          if ( $top_cats && ! is_wp_error($top_cats) ) :
            foreach ( $top_cats as $cat ) :
              $active   = is_product_category( $cat->slug );
              $children = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => $cat->term_id]);
          ?>
          <li class="asb-cat-item <?php echo $active ? 'active' : ''; ?> <?php echo $children ? 'has-children' : ''; ?>">
            <a href="<?php echo esc_url( get_term_link($cat) ); ?>">
              <span class="asb-cat-name"><?php echo esc_html( $cat->name ); ?></span>
              <span class="asb-cat-count"><?php echo esc_html( $cat->count ); ?></span>
            </a>
            <?php if ( $children ) : ?>
            <ul class="asb-subcat-list">
              <?php foreach ( $children as $child ) : ?>
              <li class="asb-subcat-item <?php echo is_product_category( $child->slug ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( get_term_link($child) ); ?>">
                  <span><?php echo esc_html( $child->name ); ?></span>
                  <span class="asb-cat-count"><?php echo esc_html( $child->count ); ?></span>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
    </aside>

    <!-- ══ MAIN ══ -->
    <div class="aurix-shop-main">

      <!-- Toolbar -->
      <div class="aurix-shop-toolbar">
        <div class="ast-left">
          <button class="ast-filter-toggle" id="aurixFilterToggle">
            <i class="fas fa-sliders-h"></i> <span>Filters</span>
          </button>
          <span class="ast-count">
            <?php
            global $wp_query;
            $found = (int) $wp_query->found_posts;
            echo esc_html( $found . ' product' . ( $found !== 1 ? 's' : '' ) );
            ?>
          </span>
        </div>
        <div class="ast-right">
          <!-- Per-page buttons -->
          <div class="ast-perpage">
            <span class="ast-label">Show:</span>
            <div class="ast-perpage-btns" id="astPerpage">
              <?php
              $current_pp = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 8;
              foreach ( [4, 8, 12, 16] as $n ) :
              ?>
              <button class="ast-pp-btn <?php echo $current_pp === $n ? 'active' : ''; ?>"
                      data-perpage="<?php echo esc_attr($n); ?>"><?php echo esc_html($n); ?></button>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- Sort -->
          <div class="ast-sort">
            <?php
            // Output sort without the wrapper form WC adds (strip it)
            woocommerce_catalog_ordering();
            ?>
          </div>
          <!-- View toggle -->
          <div class="ast-view-toggle" id="astViewToggle">
            <button class="ast-view-btn active" data-view="grid" aria-label="Grid view"><i class="fas fa-th"></i></button>
            <button class="ast-view-btn" data-view="list" aria-label="List view"><i class="fas fa-list"></i></button>
          </div>
        </div>
      </div>

      <!-- Product grid -->
      <?php if ( woocommerce_product_loop() ) : ?>
        <ul class="products columns-3" id="aurixProductGrid">
          <?php while ( have_posts() ) : the_post(); wc_get_template_part('content', 'product'); endwhile; ?>
        </ul>
        <!-- Pagination -->
        <div class="aurix-shop-pagination">
          <?php
          woocommerce_pagination();
          ?>
        </div>
      <?php else : ?>
        <div class="aurix-shop-empty">
          <i class="fas fa-search"></i>
          <h3>No products found</h3>
          <p>Try a different category or <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">view all products</a>.</p>
        </div>
      <?php endif; ?>

    </div><!-- .aurix-shop-main -->
  </div><!-- .aurix-shop-layout -->
</div><!-- .aurix-shop-page -->

<!-- Mobile sidebar overlay -->
<div class="aurix-sb-overlay" id="aurixSbOverlay"></div>
