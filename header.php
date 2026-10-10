<?php
/**
 * Aurix International — header.php
 * Author: ZaheerAbbas
 * Clean logo: reads WordPress custom logo first, falls back to theme image file
 */
defined('ABSPATH') || exit;

// Computed early (before <head>) so the --bar-h override below can account
// for it -- everything else in the CSS (body padding, sticky header/nav
// offsets) is built on that variable assuming the topbar is always present.
$aurix_topbar_enabled = get_theme_mod('aurix_topbar_enabled', true);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if ( ! $aurix_topbar_enabled ) : ?>
    <style>:root{--bar-h:0px;}</style>
  <?php endif; ?>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
if ( get_page_template_slug() === 'elementor_canvas' ) return;

$phone                 = get_theme_mod('aurix_phone', '+1 (234) 567-8900');
$email                 = get_theme_mod('aurix_email', 'info@aurixinternational.com');
$topbar_enabled        = $aurix_topbar_enabled;
$topbar_badge          = get_theme_mod('aurix_topbar_badge', 'NEW');
$topbar_text           = get_theme_mod('aurix_topbar_text', 'Pre-assembled Surgical &amp; Dental Kits now available — <a href="#">Learn more</a>');
$topbar_worldwide      = get_theme_mod('aurix_topbar_worldwide', 'Worldwide Distribution & Export');
$topbar_track_label    = get_theme_mod('aurix_topbar_track_label', 'Track Order');
$topbar_track_url_raw  = get_theme_mod('aurix_topbar_track_url', '/tracking');
$topbar_track_url      = preg_match('#^https?://#i', $topbar_track_url_raw) ? $topbar_track_url_raw : home_url($topbar_track_url_raw);
$all_categories_label  = get_theme_mod('aurix_all_categories_label', 'All Categories');
$header_call_label     = get_theme_mod('aurix_header_call_label', 'CONTACT NOW');
$search_placeholder    = get_theme_mod('aurix_search_placeholder', 'Search surgical & dental instruments…');
$cart_count  = ( function_exists('WC') && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
$account_url = function_exists('WC') ? get_permalink( get_option('woocommerce_myaccount_page_id') ) : home_url('/my-account');
$wishlist    = function_exists('aurix_get_wishlist') ? count( aurix_get_wishlist() ) : 0;

/**
 * aurix_logo_img()
 * Returns a clean <img> tag.
 * Priority: 1. WordPress custom logo (Customize→Site Identity→Logo)
 *           2. Theme /images/ fallback file
 *
 * @param string $variant    'color' | 'white'
 * @param string $extra_class  Extra CSS classes on the img
 */
function aurix_logo_img( $variant = 'color', $extra_class = '' ) {
    $logo_id = get_theme_mod('custom_logo');
    if ( $logo_id ) {
        $img = wp_get_attachment_image( $logo_id, 'full', false, [
            'class'   => trim('aurix-logo-img ' . $extra_class),
            'loading' => 'eager',
            'alt'     => get_bloginfo('name'),
            'style'   => 'max-height:44px;width:auto;',
        ]);
        if ( $img ) return $img;
    }
    $src = get_template_directory_uri() . '/images/' . ( $variant === 'white' ? 'logo-white.png' : 'logo-color.jpg' );
    return '<img src="' . esc_url($src) . '" class="' . esc_attr(trim('aurix-logo-img ' . $extra_class)) . '"
                 alt="' . esc_attr(get_bloginfo('name')) . '" loading="eager" style="max-height:44px;width:auto;">';
}
?>

<!-- ═══ TOP BAR ═══ -->
<?php if ( $topbar_enabled ) : ?>
<div id="aurix-topbar">
  <div class="atb-left">
    <?php if ( trim( $topbar_badge ) !== '' ) : ?>
      <span class="atb-pill"><?php echo esc_html( $topbar_badge ); ?></span>
    <?php endif; ?>
    <span><?php echo wp_kses_post($topbar_text); ?></span>
  </div>
  <div class="atb-right">
    <a href="mailto:<?php echo esc_attr($email); ?>"><i class="fas fa-envelope"></i> <?php echo esc_html($email); ?></a>
    <div class="atb-sep"></div>
    <span><i class="fas fa-globe"></i> <?php echo esc_html($topbar_worldwide); ?></span>
    <div class="atb-sep"></div>
    <a href="<?php echo esc_url($topbar_track_url); ?>"><i class="fas fa-box-open"></i> <?php echo esc_html($topbar_track_label); ?></a>
  </div>
</div>
<?php endif; ?>

<!-- ═══ DESKTOP HEADER ═══ -->
<header id="aurix-header">
  <div class="ahdr-row">

    <a href="<?php echo esc_url(home_url('/')); ?>" class="ahdr-logo" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> — Home">
      <?php echo aurix_logo_img('color', 'ahdr-logo-img'); ?>
    </a>

    <div class="ahdr-search" id="ahdrSearch">
      <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" autocomplete="off">
        <i class="fas fa-search ahdr-search__icon"></i>
        <input class="ahdr-search__input" id="ahdrSearchInput" type="search" name="s"
               placeholder="<?php echo esc_attr($search_placeholder); ?>"
               value="<?php echo esc_attr(get_search_query()); ?>"
               aria-label="Search products" aria-autocomplete="list" />
        <input type="hidden" name="post_type" value="product" />
        <button class="ahdr-search__btn" type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
      </form>
      <div class="asrch-dropdown" id="ahdrSearchDrop" role="listbox" aria-hidden="true" inert>
        <div class="asrch-loading"><i class="fas fa-circle-notch fa-spin"></i> Searching…</div>
        <div class="asrch-results"></div>
        <div class="asrch-footer">
          <span class="asrch-footer__hint">Press <kbd>Enter</kbd> for all results</span>
          <span class="asrch-result-count"></span>
        </div>
      </div>
    </div>

    <div class="ahdr-actions">
      <div class="ahdr-pills">
        <?php
        wp_nav_menu([
          'theme_location' => 'header_pills',
          'container'      => false,
          'items_wrap'     => '%3$s',
          'walker'         => new Aurix_Icon_Menu_Walker('pill'),
          'fallback_cb'    => function() {
            echo '<a href="' . esc_url(home_url('/deals')) . '" class="anav-pill"><i class="fas fa-tag"></i> Today\'s Deals</a>';
            echo '<a href="' . esc_url(home_url('/offers')) . '" class="anav-pill"><i class="fas fa-star"></i> Special Offer</a>';
          },
        ]);
        ?>
      </div>
      <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$phone)); ?>" class="ahdr-call">
        <div class="ahdr-call__ico"><i class="fas fa-phone"></i></div>
        <div class="ahdr-call__info">
          <span class="ahdr-call__lbl"><?php echo esc_html($header_call_label); ?></span>
          <span class="ahdr-call__num"><?php echo esc_html($phone); ?></span>
        </div>
      </a>
      <?php if ( function_exists('WC') ) : ?>
      <a href="<?php echo esc_url($account_url); ?>" class="ahdr-ibtn ahdr-ibtn--account"
         aria-label="<?php echo is_user_logged_in() ? 'My Account' : 'Login or Register'; ?>">
        <i class="fas <?php echo is_user_logged_in() ? 'fa-user-check' : 'fa-user'; ?>"></i>
        <?php if (is_user_logged_in()) : ?><span class="ahdr-ibtn__dot"></span><?php endif; ?>
      </a>
      <?php endif; ?>
      <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="ahdr-ibtn ahdr-ibtn--wishlist" aria-label="Wishlist">
        <i class="fas fa-heart"></i>
        <span class="ahdr-ibtn__badge aurix-wishlist-count" style="<?php echo $wishlist > 0 ? '' : 'display:none'; ?>"><?php echo esc_html($wishlist); ?></span>
      </a>
      <?php if ( function_exists('WC') ) : ?>
      <button class="ahdr-ibtn ahdr-ibtn--cart" id="desktopCartBtn" type="button" data-panel="aurix-cart-panel" aria-label="Shopping Cart">
        <i class="fas fa-shopping-cart"></i>
        <span class="ahdr-ibtn__badge aurix-cart-count" data-count="<?php echo esc_attr($cart_count); ?>"
              style="<?php echo $cart_count ? '' : 'display:none'; ?>"><?php echo esc_html($cart_count); ?></span>
      </button>
      <?php endif; ?>
    </div>

  </div>
</header>

<!-- ═══ NAV BAR ═══ -->
<nav id="aurix-nav" aria-label="Main navigation">
  <div class="anav-inner">
    <button class="anav-cat-btn" id="desktopCatBtn" aria-expanded="false">
      <span class="bars"><span></span><span></span><span></span></span>
      <?php echo esc_html( $all_categories_label ); ?>
    </button>
    <?php
    wp_nav_menu([
      'theme_location' => 'primary',
      'menu_class'     => 'anav-links',
      'container'      => false,
      'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
      'fallback_cb'    => function() {
        $links = [
          [home_url('/'),'Home'],[home_url('/products'),'Products'],
          [home_url('/custom-kits'),'Custom Kits'],[home_url('/oem-private-label'),'OEM / Private Label'],
          [home_url('/about'),'About Us'],[home_url('/blog'),'Blog'],[home_url('/contact'),'Contact'],
        ];
        echo '<ul class="anav-links">';
        foreach ($links as $l) printf('<li><a href="%s">%s</a></li>',esc_url($l[0]),esc_html($l[1]));
        echo '</ul>';
      },
    ]);
    ?>
    <div class="anav-pills">
      <?php
      wp_nav_menu([
        'theme_location' => 'nav_pills',
        'container'      => false,
        'items_wrap'     => '%3$s',
        'walker'         => new Aurix_Icon_Menu_Walker('pill'),
        'fallback_cb'    => function() {
          echo '<a href="' . esc_url(home_url('/deals')) . '" class="anav-pill anav-pill--hot"><i class="fas fa-fire"></i> Today\'s Deals</a>';
          echo '<a href="' . esc_url(home_url('/offers')) . '" class="anav-pill"><i class="fas fa-star"></i> Special Offer</a>';
          echo '<a href="' . esc_url(home_url('/tracking')) . '" class="anav-track"><i class="fas fa-box"></i> Track Order</a>';
        },
      ]);
      ?>
    </div>
  </div>
</nav>

<!-- ═══ CATEGORY PANEL ═══ -->
<aside class="apanel" id="aurix-cat-panel" aria-hidden="true" inert role="dialog" aria-label="Browse Categories">
  <div class="apanel__head">
    <span class="apanel__title">Browse <em>Categories</em></span>
    <button class="apanel__close" id="catPanelClose" aria-label="Close"><i class="fas fa-times"></i></button>
  </div>
  <div class="apanel__body">
    <?php
    $cat_icons = ['bone-curette'=>'fa-circle-dot','composite-filling-set'=>'fa-circle-dot','diagnostic'=>'fa-stethoscope','explorer'=>'fa-circle-dot','extracting-forceps'=>'fa-circle-dot','gracey-curettes'=>'fa-tooth','matrix-band-retainer'=>'fa-tools','ortho'=>'fa-circle-dot','rubber-dam-kit'=>'fa-box','scaler'=>'fa-tooth','sinus-lift-kit'=>'fa-syringe','surgical-instruments'=>'fa-scalpel','dental-instruments'=>'fa-tooth','sets-packs'=>'fa-box-open','implant-instruments'=>'fa-syringe','operative'=>'fa-tools','endodontic'=>'fa-microscope'];
    $excluded = ['uncategorized','oem-private-label','wholesale-export','wholesale','surgical-instruments','dental-instruments','sets-packs','sets-and-packs'];
    if (function_exists('WC')) {
      $cats = get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0,'number'=>30,'orderby'=>'name']);
      if (!empty($cats) && !is_wp_error($cats)) {
        foreach ($cats as $cat) {
          if (in_array($cat->slug, $excluded, true)) continue;
          $icon = $cat_icons[$cat->slug] ?? 'fa-circle-dot';
          printf('<a href="%s"><span class="albl"><i class="fas %s"></i> %s</span><span class="albl-count">%d</span><i class="fas fa-chevron-right albl-arrow"></i></a>',
            esc_url(get_term_link($cat)),esc_attr($icon),esc_html($cat->name),(int)$cat->count);
        }
      }
    }
    ?>
  </div>
  <div class="apanel__foot">
    <?php if (function_exists('wc_get_page_id')) : ?>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="apanel__view-all">
      <i class="fas fa-th-large"></i>
      <span>View All Products</span>
      <span class="apanel__va-count"><?php echo (int) wp_count_posts('product')->publish; ?></span>
    </a>
    <?php endif; ?>
  </div>
</aside>

<!-- ═══ MOBILE MENU PANEL ═══ -->
<aside class="apanel apanel--mob-left" id="aurix-pages-panel" aria-hidden="true" inert role="dialog" aria-label="Navigation menu">
  <div class="apanel__head">
    <div class="apanel__logo">
      <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo aurix_logo_img('color','apanel-logo-img'); ?></a>
    </div>
    <button class="apanel__close" id="pagesPanelClose" aria-label="Close"><i class="fas fa-times"></i></button>
  </div>
  <div class="apanel__body">
    <?php if (is_user_logged_in()) : $u=wp_get_current_user(); ?>
    <div class="apanel__user">
      <div class="apanel__user-ava"><?php echo get_avatar( $u->ID, 44 ); ?></div>
      <div>
        <span class="apanel__user-name"><?php echo esc_html($u->display_name); ?></span>
        <a href="<?php echo esc_url($account_url); ?>" class="apanel__user-link">View Account</a>
      </div>
    </div>
    <?php endif; ?>
    <?php
    wp_nav_menu([
      'theme_location' => 'mobile_menu',
      'container'      => false,
      'items_wrap'     => '%3$s',
      'walker'         => new Aurix_Icon_Menu_Walker('mobile'),
      'fallback_cb'    => function() {
        $shop_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/products');
        $links = [
          [home_url('/'), 'fa-home', 'Home'],
          [$shop_url, 'fa-microscope', 'Products'],
          [home_url('/custom-kits'), 'fa-box-open', 'Custom Kits'],
          [home_url('/oem-private-label'), 'fa-industry', 'OEM &amp; Private Label'],
          [home_url('/about'), 'fa-building', 'About Us'],
          [home_url('/blog'), 'fa-newspaper', 'Blog'],
          [home_url('/contact'), 'fa-envelope', 'Contact'],
        ];
        foreach ($links as $l) {
          printf('<a href="%s"><span class="albl"><i class="fas %s"></i> %s</span><i class="fas fa-chevron-right albl-arrow"></i></a>', esc_url($l[0]), esc_attr($l[1]), $l[2]);
        }
        echo '<div class="apanel__divider"></div>';
        $links2 = [
          [home_url('/deals'), 'fa-fire', "Today's Deals"],
          [home_url('/offers'), 'fa-star', 'Special Offer'],
          [home_url('/wishlist'), 'fa-heart', 'Wishlist'],
          [home_url('/tracking'), 'fa-box', 'Track Order'],
        ];
        foreach ($links2 as $l) {
          printf('<a href="%s"><span class="albl"><i class="fas %s"></i> %s</span><i class="fas fa-chevron-right albl-arrow"></i></a>', esc_url($l[0]), esc_attr($l[1]), $l[2]);
        }
      },
    ]);
    ?>
    <?php if (function_exists('WC')) : ?>
    <a href="<?php echo esc_url($account_url); ?>"><span class="albl"><i class="fas fa-user-circle"></i> <?php echo is_user_logged_in() ? 'My Account' : 'Login / Register'; ?></span><i class="fas fa-chevron-right albl-arrow"></i></a>
    <?php endif; ?>
  </div>
</aside>

<!-- ═══ MOBILE TOP BAR ═══ -->
<div id="aurix-mob-top">
  <button class="amob-btn" id="mobMenuBtn" aria-label="Open categories"><i class="fas fa-bars"></i></button>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="amob-logo-link" aria-label="Home">
    <?php echo aurix_logo_img('color','amob-logo-img'); ?>
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

<!-- ═══ MOBILE SEARCH OVERLAY ═══ -->
<div id="aurix-search-overlay" role="dialog" aria-modal="true" aria-label="Search">
  <div class="asrch-box">
    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" autocomplete="off">
      <div class="asrch-row">
        <input class="asrch-input" id="mobSearchInput" type="search" name="s"
               placeholder="Search instruments, kits, brands…" value="<?php echo esc_attr(get_search_query()); ?>" aria-label="Search" />
        <input type="hidden" name="post_type" value="product" />
        <button class="asrch-submit" type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
      </div>
    </form>
    <div class="asrch-dropdown asrch-dropdown--mob" id="mobSearchDrop" aria-hidden="true" inert>
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

<!-- ═══ MOBILE BOTTOM NAV ═══ -->
<nav id="aurix-mob-bot" aria-label="Mobile navigation">
  <button class="amob-nav" id="mobPagesBtn" aria-label="Menu"><i class="fas fa-bars"></i><span>Menu</span></button>
  <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="amob-nav" aria-label="Wishlist">
    <i class="fas fa-heart"></i><span>Wishlist</span>
    <span class="amob-nav__badge aurix-wishlist-count" style="<?php echo $wishlist > 0 ? '' : 'display:none'; ?>"><?php echo esc_html($wishlist); ?></span>
  </a>
  <a href="<?php echo esc_url(home_url('/')); ?>" class="amob-nav amob-nav--home" aria-label="Home"><i class="fas fa-home"></i></a>
  <?php if (function_exists('WC')) : ?>
  <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="amob-nav amob-nav--cart-link" aria-label="Cart">
    <i class="fas fa-shopping-cart"></i><span>Cart</span>
    <span class="amob-nav__badge aurix-cart-count" data-count="<?php echo esc_attr($cart_count); ?>"
          style="<?php echo $cart_count ? '' : 'display:none'; ?>"><?php echo esc_html($cart_count); ?></span>
  </a>
  <a href="<?php echo esc_url($account_url); ?>" class="amob-nav" aria-label="<?php echo is_user_logged_in() ? 'My Account' : 'Login'; ?>">
    <i class="fas <?php echo is_user_logged_in() ? 'fa-user-check' : 'fa-user'; ?>"></i>
    <span><?php echo is_user_logged_in() ? 'Account' : 'Login'; ?></span>
  </a>
  <?php endif; ?>
</nav>

<!-- ═══ PAGE WRAPPER (closed in footer.php) ═══ -->
<div id="page" class="site">
  <main id="main" class="site-main" role="main">
