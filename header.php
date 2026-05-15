<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$page_tpl    = get_page_template_slug();
if ( $page_tpl === 'elementor_canvas' ) return;

$phone        = get_theme_mod('aurix_phone','+1 (234) 567-8900');
$email        = get_theme_mod('aurix_email','info@aurixinternational.com');
$topbar_text  = get_theme_mod('aurix_topbar_text','Pre-assembled Surgical &amp; Dental Kits now available — <a href="#">Learn more</a>');
$cart_count   = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
$account_url  = function_exists('WC') ? get_permalink(get_option('woocommerce_myaccount_page_id')) : home_url('/my-account');
$logo_color   = get_template_directory_uri() . '/images/logo-color.jpg';
$logo_white   = get_template_directory_uri() . '/images/logo-white.png';
$wishlist     = function_exists('aurix_get_wishlist') ? count(aurix_get_wishlist()) : 0;

// Logo helper — uses real uploaded logo, falls back to custom_logo, then SVG
function aurix_logo_html( $variant = 'color', $classes = '' ) {
    global $logo_color, $logo_white;
    // If WordPress custom logo is set, use it
    if ( has_custom_logo() ) {
        return get_custom_logo();
    }
    $src = ($variant === 'white') ? $logo_white : $logo_color;
    return '<img src="' . esc_url($src) . '" alt="Aurix International" class="aurix-logo-img ' . esc_attr($classes) . '" width="160" height="52" loading="eager">';
}
?>

<!-- ═══════════ TOP BAR ═══════════ -->
<div id="aurix-topbar">
  <div class="atb-left">
    <span class="atb-pill">NEW</span>
    <span><?php echo wp_kses_post($topbar_text); ?></span>
  </div>
  <div class="atb-right">
    <a href="mailto:<?php echo esc_attr($email); ?>"><i class="fas fa-envelope"></i> <?php echo esc_html($email); ?></a>
    <div class="atb-sep"></div>
    <span><i class="fas fa-globe"></i> Worldwide Distribution &amp; Export</span>
    <div class="atb-sep"></div>
    <a href="<?php echo esc_url(home_url('/tracking')); ?>"><i class="fas fa-box-open"></i> Track Order</a>
  </div>
</div>

<!-- ═══════════ DESKTOP HEADER ═══════════ -->
<header id="aurix-header">
  <div class="ahdr-row">

    <!-- LOGO -->
    <a href="<?php echo esc_url(home_url('/')); ?>" class="ahdr-logo" aria-label="Aurix International Homepage">
      <?php echo aurix_logo_html('color','ahdr-logo-img'); ?>
    </a>

    <!-- LIVE SEARCH -->
    <div class="ahdr-search" id="ahdrSearch">
      <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" autocomplete="off">
        <i class="fas fa-search ahdr-search__icon"></i>
        <input class="ahdr-search__input" id="ahdrSearchInput" type="search" name="s"
               placeholder="Search surgical &amp; dental instruments…"
               value="<?php echo esc_attr(get_search_query()); ?>"
               aria-label="Search" aria-autocomplete="list" />
        <input type="hidden" name="post_type" value="product" />
        <button class="ahdr-search__btn" type="submit" aria-label="Search">
          <i class="fas fa-search"></i>
        </button>
      </form>
      <!-- Live search dropdown -->
      <div class="asrch-dropdown" id="ahdrSearchDrop" role="listbox" aria-hidden="true">
        <div class="asrch-loading"><i class="fas fa-circle-notch fa-spin"></i> Searching…</div>
        <div class="asrch-results"></div>
        <div class="asrch-footer">
          <span class="asrch-footer__hint">Press <kbd>Enter</kbd> for all results</span>
          <span class="asrch-result-count"></span>
        </div>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="ahdr-actions">
      <a href="<?php echo esc_url(home_url('/deals')); ?>" class="ahdr-pill">
        <i class="fas fa-tag"></i> Today's Deals
      </a>
      <a href="<?php echo esc_url(home_url('/offers')); ?>" class="ahdr-pill">
        <i class="fas fa-star"></i> Special Offer
      </a>

      <!-- Phone -->
      <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','', $phone)); ?>" class="ahdr-call">
        <div class="ahdr-call__ico"><i class="fas fa-phone"></i></div>
        <div class="ahdr-call__info">
          <span class="ahdr-call__lbl">CONTACT NOW</span>
          <span class="ahdr-call__num"><?php echo esc_html($phone); ?></span>
        </div>
      </a>

      <!-- Account -->
      <?php if ( function_exists('WC') ) : ?>
      <a href="<?php echo esc_url($account_url); ?>" class="ahdr-ibtn ahdr-ibtn--account"
         aria-label="<?php echo is_user_logged_in() ? 'My Account' : 'Login or Register'; ?>">
        <i class="fas <?php echo is_user_logged_in() ? 'fa-user-check' : 'fa-user'; ?>"></i>
        <?php if (is_user_logged_in()) : ?><span class="ahdr-ibtn__dot"></span><?php endif; ?>
      </a>
      <?php endif; ?>

      <!-- Wishlist -->
      <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="ahdr-ibtn ahdr-ibtn--wishlist" aria-label="Wishlist">
        <i class="fas fa-heart"></i>
        <span class="ahdr-ibtn__badge aurix-wishlist-count"
              style="<?php echo $wishlist > 0 ? '' : 'display:none'; ?>">
          <?php echo esc_html($wishlist); ?>
        </span>
      </a>

      <!-- Cart -->
      <?php if ( function_exists('WC') ) : ?>
      <button class="ahdr-ibtn ahdr-ibtn--cart" id="desktopCartBtn" type="button"
              data-panel="aurix-cart-panel" aria-label="Shopping Cart">
        <i class="fas fa-shopping-cart"></i>
        <span class="ahdr-ibtn__badge aurix-cart-count"
              data-count="<?php echo esc_attr($cart_count); ?>"
              style="<?php echo $cart_count ? '' : 'display:none'; ?>">
          <?php echo esc_html($cart_count); ?>
        </span>
      </button>
      <?php endif; ?>
    </div>

  </div>
</header>

<!-- ═══════════ NAV BAR ═══════════ -->
<nav id="aurix-nav" aria-label="Main navigation">
  <div class="anav-inner">
    <button class="anav-cat-btn" id="desktopCatBtn" aria-expanded="false">
      <span class="bars"><span></span><span></span><span></span></span>
      ALL CATEGORIES
    </button>

    <?php
    wp_nav_menu([
        'theme_location' => 'primary',
        'menu_class'     => 'anav-links',
        'container'      => false,
        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'fallback_cb'    => function() {
            echo '<ul class="anav-links">';
            $pages = [
                [home_url('/'),'Home'],
                [home_url('/products'),'Products'],
                [home_url('/custom-kits'),'Custom Kits'],
                [home_url('/oem-private-label'),'OEM / Private Label'],
                [home_url('/about'),'About Us'],
                [home_url('/blog'),'Blog'],
                [home_url('/contact'),'Contact'],
            ];
            foreach ($pages as $p)
                printf('<li><a href="%s">%s</a></li>', esc_url($p[0]), esc_html($p[1]));
            echo '</ul>';
        },
    ]);
    ?>
    <div class="anav-pills">
      <a href="<?php echo esc_url(home_url('/deals')); ?>" class="anav-pill anav-pill--hot">
        <i class="fas fa-fire"></i> Today's Deals
      </a>
      <a href="<?php echo esc_url(home_url('/offers')); ?>" class="anav-pill">
        <i class="fas fa-star"></i> Special Offer
      </a>
      <a href="<?php echo esc_url(home_url('/tracking')); ?>" class="anav-track">
        <i class="fas fa-box"></i> Track Order
      </a>
    </div>
  </div>
</nav>

<!-- ═══════════ CATEGORY PANEL ═══════════ -->
<aside class="apanel" id="aurix-cat-panel" aria-hidden="true" role="dialog">
  <div class="apanel__head">
    <span class="apanel__title">Browse <em>Categories</em></span>
    <button class="apanel__close" id="catPanelClose" aria-label="Close"><i class="fas fa-times"></i></button>
  </div>
  <div class="apanel__body">
    <?php
    $icons = ['surgical-instruments'=>'fa-scalpel-line-dashed','dental-instruments'=>'fa-tooth','sets-packs'=>'fa-box-open','diagnostic'=>'fa-stethoscope','periodontal'=>'fa-teeth-open','implant-instruments'=>'fa-syringe','operative'=>'fa-tools','endodontic'=>'fa-microscope','general'=>'fa-layer-group'];
    if (function_exists('WC')) {
        $cats = get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0,'number'=>20]);
        if (!empty($cats) && !is_wp_error($cats)) {
            foreach ($cats as $cat) {
                if ($cat->slug === 'uncategorized') continue;
                $icon = $icons[$cat->slug] ?? 'fa-circle-dot';
                $thumb = get_term_meta($cat->term_id, 'thumbnail_id', true);
                printf('<a href="%s"><span class="albl"><i class="fas %s"></i> %s</span><span class="albl-count">%d</span><i class="fas fa-chevron-right albl-arrow"></i></a>',
                    esc_url(get_term_link($cat)), esc_attr($icon), esc_html($cat->name), $cat->count);
            }
        }
    }
    // Fallback static
    $defaults = [
        ['/product-category/surgical-instruments','fa-scalpel','Surgical Instruments',0],
        ['/product-category/dental-instruments','fa-tooth','Dental Instruments',0],
        ['/product-category/sets-packs','fa-box-open','Sets & Packs',0],
        ['/product-category/diagnostic','fa-stethoscope','Diagnostic Instruments',0],
        ['/product-category/implant-instruments','fa-syringe','Implant Instruments',0],
        ['/product-category/operative','fa-tools','Operative Instruments',0],
        ['/oem-private-label','fa-industry','OEM / Private Label',0],
        ['/wholesale','fa-globe','Wholesale / Export',0],
    ];
    foreach ($defaults as $d) :
        printf('<a href="%s"><span class="albl"><i class="fas %s"></i> %s</span><i class="fas fa-chevron-right albl-arrow"></i></a>',
            esc_url(home_url($d[0])), esc_attr($d[1]), esc_html($d[2]));
    endforeach;
    ?>
  </div>
</aside>

<!-- ═══════════ MOBILE MENU PANEL ═══════════ -->
<aside class="apanel apanel--mob-left" id="aurix-pages-panel" aria-hidden="true" role="dialog">
  <div class="apanel__head">
    <div class="apanel__logo">
      <?php echo aurix_logo_html('color'); ?>
    </div>
    <button class="apanel__close" id="pagesPanelClose" aria-label="Close"><i class="fas fa-times"></i></button>
  </div>
  <div class="apanel__body">
    <?php if (is_user_logged_in()) : ?>
    <div class="apanel__user">
      <div class="apanel__user-ava"><i class="fas fa-user-circle"></i></div>
      <div>
        <span class="apanel__user-name"><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
        <a href="<?php echo esc_url($account_url); ?>" class="apanel__user-link">View Account</a>
      </div>
    </div>
    <?php endif; ?>
    <a href="<?php echo esc_url(home_url('/')); ?>"><span class="albl"><i class="fas fa-home"></i> Home</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/products')); ?>"><span class="albl"><i class="fas fa-microscope"></i> Products</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/custom-kits')); ?>"><span class="albl"><i class="fas fa-box-open"></i> Custom Kits</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/oem-private-label')); ?>"><span class="albl"><i class="fas fa-industry"></i> OEM &amp; Private Label</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/about')); ?>"><span class="albl"><i class="fas fa-building"></i> About Us</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/blog')); ?>"><span class="albl"><i class="fas fa-newspaper"></i> Blog</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/contact')); ?>"><span class="albl"><i class="fas fa-envelope"></i> Contact</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <div class="apanel__divider"></div>
    <a href="<?php echo esc_url(home_url('/deals')); ?>"><span class="albl"><i class="fas fa-fire"></i> Today's Deals</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/wishlist')); ?>"><span class="albl"><i class="fas fa-heart"></i> Wishlist</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <a href="<?php echo esc_url(home_url('/tracking')); ?>"><span class="albl"><i class="fas fa-box"></i> Track Order</span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <?php if (function_exists('WC')) : ?>
    <a href="<?php echo esc_url($account_url); ?>"><span class="albl"><i class="fas fa-user-circle"></i> <?php echo is_user_logged_in() ? 'My Account' : 'Login / Register'; ?></span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <?php endif; ?>
  </div>
</aside>

<!-- ═══════════ MOBILE TOP BAR ═══════════ -->
<div id="aurix-mob-top">
  <button class="amob-btn" id="mobMenuBtn" aria-label="Menu"><i class="fas fa-bars"></i></button>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="amob-logo-link" aria-label="Home">
    <?php echo aurix_logo_html('color','amob-logo-img'); ?>
  </a>
  <div class="amob-right">
    <button class="amob-ilink" id="mobSearchBtn" aria-label="Search"><i class="fas fa-search"></i></button>
    <?php if (function_exists('WC')) : ?>
    <button class="amob-ilink" type="button" data-panel="aurix-cart-panel" aria-label="Cart">
      <i class="fas fa-shopping-cart"></i>
      <span class="amob-badge aurix-cart-count" data-count="<?php echo esc_attr($cart_count); ?>"
            style="<?php echo $cart_count ? '' : 'display:none'; ?>"><?php echo esc_html($cart_count); ?></span>
    </button>
    <?php endif; ?>
  </div>
</div>

<!-- ═══════════ MOBILE SEARCH OVERLAY ═══════════ -->
<div id="aurix-search-overlay" role="dialog" aria-modal="true">
  <div class="asrch-box">
    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" autocomplete="off">
      <div class="asrch-row">
        <input class="asrch-input" id="mobSearchInput" type="search" name="s"
               placeholder="Search instruments, kits, brands…"
               value="<?php echo esc_attr(get_search_query()); ?>" />
        <input type="hidden" name="post_type" value="product" />
        <button class="asrch-submit" type="submit"><i class="fas fa-search"></i></button>
      </div>
    </form>
    <!-- Mobile live results -->
    <div class="asrch-dropdown asrch-dropdown--mob" id="mobSearchDrop" aria-hidden="true">
      <div class="asrch-loading"><i class="fas fa-circle-notch fa-spin"></i> Searching…</div>
      <div class="asrch-results"></div>
    </div>
    <div class="asrch-hints">
      <span class="asrch-hints-label">Popular:</span>
      <button class="asrch-hint" type="button">Surgical Scissors</button>
      <button class="asrch-hint" type="button">Dental Forceps</button>
      <button class="asrch-hint" type="button">Implant Kits</button>
      <button class="asrch-hint" type="button">Retractors</button>
    </div>
  </div>
</div>

<!-- ═══════════ MOBILE BOTTOM NAV ═══════════ -->
<nav id="aurix-mob-bot" aria-label="Mobile navigation">
  <button class="amob-nav" id="mobPagesBtn"><i class="fas fa-bars"></i><span>Menu</span></button>
  <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="amob-nav">
    <i class="fas fa-heart"></i><span>Wishlist</span>
    <span class="amob-nav__badge aurix-wishlist-count" style="<?php echo $wishlist > 0 ? '' : 'display:none'; ?>"><?php echo esc_html($wishlist); ?></span>
  </a>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="amob-nav amob-nav--home"><i class="fas fa-home"></i></a>
  <?php if (function_exists('WC')) : ?>
  <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="amob-nav amob-nav--cart-link">
    <i class="fas fa-shopping-cart"></i><span>Cart</span>
    <span class="amob-nav__badge aurix-cart-count" data-count="<?php echo esc_attr($cart_count); ?>"
          style="<?php echo $cart_count ? '' : 'display:none'; ?>"><?php echo esc_html($cart_count); ?></span>
  </a>
  <a href="<?php echo esc_url($account_url); ?>" class="amob-nav">
    <i class="fas <?php echo is_user_logged_in() ? 'fa-user-check' : 'fa-user'; ?>"></i>
    <span><?php echo is_user_logged_in() ? 'Account' : 'Login'; ?></span>
  </a>
  <?php endif; ?>
</nav>

<!-- ═══════════ PAGE WRAPPER ═══════════ -->
<div id="page" class="site">
  <main id="main" class="site-main" role="main">
