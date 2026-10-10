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

    <!-- Breadcrumb -->
    <nav class="asph-breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>"><i class="fas fa-home"></i> Home</a>
      <span class="asph-bc-sep"><i class="fas fa-chevron-right"></i></span>
      <?php if ( is_product_category() ) :
        $current_cat = get_queried_object(); ?>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">Products</a>
        <span class="asph-bc-sep"><i class="fas fa-chevron-right"></i></span>
        <span class="asph-bc-current"><?php echo esc_html($current_cat->name); ?></span>
      <?php else : ?>
        <span class="asph-bc-current">All Products</span>
      <?php endif; ?>
    </nav>

    <!-- Title row -->
    <div class="asph-title-row">
      <div class="asph-title-left">
        <?php if ( apply_filters('woocommerce_show_page_title', true) ) : ?>
        <h1 class="aurix-shop-title">
          <?php if ( is_product_category() ) :
            $cat = get_queried_object();
            echo '<span class="asph-title-accent">' . esc_html($cat->name) . '</span>';
            echo ' <em>Collection</em>';
          else : ?>
            All <em>Products</em>
          <?php endif; ?>
        </h1>
        <?php endif; ?>
        <?php if ( is_product_category() ) :
          $cat   = get_queried_object();
          $desc  = $cat->description;
          if ( $desc ) : ?>
        <div class="asph-desc-wrap">
          <div class="asph-desc asph-desc--collapsed" id="asphDesc"><?php echo wp_kses_post( wpautop( $desc ) ); ?></div>
          <button class="asph-read-more" type="button" aria-expanded="false" aria-controls="asphDesc" hidden>
            <span>Read more</span> <i class="fas fa-chevron-down" aria-hidden="true"></i>
          </button>
        </div>
        <?php endif; endif; ?>
      </div>
      <div class="asph-title-right">
        <?php
        $total = wc_get_loop_prop('total') ?: (is_product_category() ? get_queried_object()->count : wp_count_posts('product')->publish);
        $total = (int) $total;
        ?>
        <div class="asph-count-badge">
          <span class="asph-count-num"><?php echo $total; ?></span>
          <span class="asph-count-label"><?php echo esc_html( _n( 'Product', 'Products', $total, 'aurix' ) ); ?></span>
        </div>
        <?php if ( is_product_category() ) :
          $cat = get_queried_object();
          $parent = $cat->parent ? get_term($cat->parent, 'product_cat') : null;
          if ($parent && !is_wp_error($parent)) : ?>
          <a href="<?php echo esc_url(get_term_link($parent)); ?>" class="asph-parent-link">
            <i class="fas fa-layer-group"></i> <?php echo esc_html($parent->name); ?>
          </a>
          <?php endif;
        endif; ?>
      </div>
    </div>

    <!-- Decorative rule -->
    <div class="asph-rule"><span></span></div>

  </div>
  <script>
  (function(){
    var desc = document.getElementById('asphDesc');
    var btn  = document.querySelector('.asph-read-more');
    if (!desc || !btn) return;
    var label = btn.querySelector('span');
    function check(){
      if (desc.classList.contains('asph-desc--expanded')) return;
      var overflow = desc.scrollHeight > desc.clientHeight + 4;
      btn.hidden = !overflow;
      desc.classList.toggle('asph-desc--fits', !overflow);
    }
    check();
    window.addEventListener('load', check);
    window.addEventListener('resize', check);
    btn.addEventListener('click', function(){
      var open = btn.getAttribute('aria-expanded') === 'true';
      if (open) {
        desc.style.maxHeight = desc.scrollHeight + 'px';
        requestAnimationFrame(function(){
          desc.classList.remove('asph-desc--expanded');
          desc.classList.add('asph-desc--collapsed');
          desc.style.maxHeight = '';
        });
        btn.setAttribute('aria-expanded','false');
        label.textContent = 'Read more';
        var head = desc.closest('.aurix-shop-page-head');
        if (head && head.getBoundingClientRect().top < 0) head.scrollIntoView({behavior:'smooth', block:'start'});
      } else {
        desc.style.maxHeight = desc.scrollHeight + 'px';
        desc.classList.remove('asph-desc--collapsed');
        desc.classList.add('asph-desc--expanded');
        desc.addEventListener('transitionend', function t(){ desc.style.maxHeight = 'none'; desc.removeEventListener('transitionend', t); });
        btn.setAttribute('aria-expanded','true');
        label.textContent = 'Read less';
      }
    });
  })();
  </script>

  <div class="aurix-shop-layout">

    <!-- ══ SIDEBAR: Categories only ══ -->
    <aside class="aurix-shop-sidebar" id="aurixShopSidebar">
      <div class="asb-header">
        <h3 class="asb-title"><i class="fas fa-th-list"></i> Categories</h3>
        <button class="asb-close-btn" id="aurixSbClose" aria-label="Close"><i class="fas fa-times"></i></button>
      </div>
      <div class="asb-body">
        <ul class="asb-cat-list">

          <?php
          $top_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name']);
          // Filter out page-type categories that aren't instrument categories
          $excluded_slugs = ['oem-private-label','wholesale-export','wholesale','surgical-instruments','dental-instruments','sets-packs','sets-and-packs','uncategorized'];
          if ( $top_cats && ! is_wp_error($top_cats) ) :
            foreach ( $top_cats as $cat ) :
              if ( in_array($cat->slug, $excluded_slugs, true) ) continue;
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
      <!-- View All fixed footer -->
      <div class="asb-footer">
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id('shop') ) ); ?>" class="asb-view-all">
          <i class="fas fa-th-large"></i>
          <span>View All Products</span>
          <span class="asb-va-count"><?php echo (int) wp_count_posts('product')->publish; ?></span>
        </a>
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
              $current_pp = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12;
              foreach ( [6, 12, 18, 24] as $n ) :
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
          <!-- Columns (grid view only) -->
          <div class="ast-cols" id="astCols">
            <span class="ast-label">Cols:</span>
            <div class="ast-cols-btns">
              <button class="ast-cols-btn ast-cols-btn--mobile-only" data-cols="1" aria-label="1 column">1</button>
              <button class="ast-cols-btn ast-cols-btn--both" data-cols="2" aria-label="2 columns">2</button>
              <button class="ast-cols-btn ast-cols-btn--desktop-only active" data-cols="3" aria-label="3 columns">3</button>
              <button class="ast-cols-btn ast-cols-btn--desktop-only" data-cols="4" aria-label="4 columns">4</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Product grid -->
      <?php if ( woocommerce_product_loop() ) : ?>
        <ul class="products columns-3 aurix-cols-3" id="aurixProductGrid">
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
