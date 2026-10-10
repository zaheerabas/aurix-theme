<?php
/**
 * Aurix International — functions.php v3.0
 * Author: ZaheerAbbas
 */
if ( ! defined('ABSPATH') ) exit;

require_once get_template_directory() . '/inc/ajax-handlers.php';
require_once get_template_directory() . '/inc/nav-walker.php';
require_once get_template_directory() . '/inc/blog.php';

/* ═══ 1. THEME SETUP ═══ */
function aurix_setup() {
    load_theme_textdomain('aurix', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('custom-logo', ['height'=>80,'width'=>280,'flex-height'=>true,'flex-width'=>true]);
    register_nav_menus([
        'primary'            => __('Primary Nav (Desktop)',     'aurix'),
        'mobile_menu'        => __('Mobile Menu Panel',         'aurix'),
        'header_pills'       => __('Header Quick Links — Top Bar (White)', 'aurix'),
        'nav_pills'          => __('Header Quick Links — Nav Bar (Dark)', 'aurix'),
        'footer'             => __('Footer Menu',               'aurix'),
        'footer_about'       => __('Footer — About Us',         'aurix'),
        'footer_products'    => __('Footer — Our Products',     'aurix'),
        'footer_care'        => __('Footer — Customer Care',    'aurix'),
        'footer_mobile_contact' => __('Footer — Mobile: Contact', 'aurix'),
        'footer_mobile_info' => __('Footer — Mobile: Information', 'aurix'),
        'footer_mobile_care' => __('Footer — Mobile: Customer Care', 'aurix'),
    ]);
}
add_action('after_setup_theme', 'aurix_setup');

/* ═══ 2. FAVICON ═══ */
add_action('wp_head', function() {
    $favicon = get_template_directory_uri() . '/favicon.ico';
    echo '<link rel="icon" href="' . esc_url($favicon) . '" type="image/x-icon">' . "\n";
    echo '<link rel="shortcut icon" href="' . esc_url($favicon) . '" type="image/x-icon">' . "\n";
    echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

// FontAwesome via a proper enqueue (standard 'font-awesome' handle) instead of
// a raw <link> echo -- WordPress dedupes styles by handle, so if any plugin
// also loads FontAwesome under this same common handle, it'll only actually
// output once. A raw echo bypassed that entirely and was loading it twice.
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [], '6.5.1'
    );
}, 1);

// Preserve the SRI integrity hash that was on the original hardcoded <link>
// tag -- wp_enqueue_style() has no built-in way to add integrity/crossorigin,
// and dropping it would silently lose a real security protection during the
// conversion above. Also load the Google Fonts stylesheet without blocking
// initial render, via the standard preload-then-swap technique.
add_filter('style_loader_tag', function ($html, $handle) {
    if ($handle === 'font-awesome') {
        $html = str_replace(' />', ' integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />', $html);
        return $html;
    }
    if ($handle !== 'aurix-fonts') return $html;
    $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
    $html = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $html);
    $html .= '<noscript>' . str_replace(["rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"'], ["rel='stylesheet'", 'rel="stylesheet"'], $html) . '</noscript>';
    return $html;
}, 10, 2);

/* ═══ 3. ENQUEUE ═══ */
function aurix_assets() {
    wp_enqueue_style('aurix-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Outfit:wght@300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap',
        [], null);
    wp_enqueue_style('aurix-style', get_stylesheet_uri(), ['aurix-fonts'], '36.93.0');
    wp_enqueue_script('aurix-main', get_template_directory_uri().'/js/aurix-main.js', ['jquery'], '36.93.0', true);

    wp_localize_script('aurix-main', 'aurixData', [
        'ajaxUrl'       => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('aurix_nonce'),
        'searchNonce'   => wp_create_nonce('aurix_search_nonce'),
        'cartUrl'       => function_exists('WC') ? wc_get_cart_url() : home_url('/cart'),
        'checkoutUrl'   => function_exists('WC') ? wc_get_checkout_url() : home_url('/checkout'),
        'accountUrl'    => function_exists('WC') ? get_permalink(get_option('woocommerce_myaccount_page_id')) : home_url('/my-account'),
        'wishlistUrl'   => home_url('/wishlist'),
        'isLoggedIn'    => is_user_logged_in() ? '1' : '0',
        'userName'      => is_user_logged_in() ? wp_get_current_user()->display_name : '',
        'logoUrl'       => get_template_directory_uri() . '/images/logo-color.jpg',
        'logoWhiteUrl'  => get_template_directory_uri() . '/images/logo-white.png',
    ]);

    if ( is_singular() && comments_open() && get_option('thread_comments') )
        wp_enqueue_script('comment-reply');
}
add_action('wp_enqueue_scripts', 'aurix_assets');

/* ── Cart & Checkout dedicated CSS (loads after WooCommerce, max priority) ── */
add_action('wp_enqueue_scripts', function() {
    if ( function_exists('is_cart') && (is_cart() || is_checkout()) ) {
        wp_enqueue_style(
            'aurix-cart',
            get_template_directory_uri() . '/css/cart.css',
            ['woocommerce-general','woocommerce-layout','woocommerce-smallscreen','aurix-style'],
            '36.93.0'
        );
    }
}, 99);

/* ═══ 4. ELEMENTOR ═══ */
add_action('after_setup_theme', function() { add_theme_support('header-footer-elementor'); });

/* ═══ 5. WOO CART FRAGMENTS ═══ */
function aurix_cart_fragments( $fragments ) {
    if ( !function_exists('WC') || !WC()->cart ) return $fragments;
    $count = WC()->cart->get_cart_contents_count();
    ob_start();
    ?><span class="aurix-cart-count" data-count="<?php echo esc_attr($count); ?>"><?php echo esc_html($count); ?></span><?php
    $fragments['.aurix-cart-count'] = ob_get_clean();
    ob_start(); woocommerce_mini_cart();
    $fragments['div.aurix-minicart-items'] = '<div class="aurix-minicart-items">'.ob_get_clean().'</div>';
    return $fragments;
}
if ( function_exists('WC') ) add_filter('woocommerce_add_to_cart_fragments','aurix_cart_fragments');

/* ═══ 6. WOO WRAPPERS ═══ */
// Content wrappers only for non-shop/archive pages
// (shop/archive use archive-product.php which has its own layout)
remove_action('woocommerce_before_main_content','woocommerce_output_content_wrapper',10);
remove_action('woocommerce_after_main_content','woocommerce_output_content_wrapper_end',10);
add_action('woocommerce_before_main_content', function(){
    if ( is_shop() || is_product_category() || is_product_tag() ) return;
    echo '<div class="aurix-woo-wrap"><div class="aurix-woo-inner">';
}, 10);
add_action('woocommerce_after_main_content', function(){
    if ( is_shop() || is_product_category() || is_product_tag() ) return;
    echo '</div></div>';
}, 10);

/* ═══ 7. CART SIDEBAR ═══ */
add_action('wp_footer', function() {
    if ( !function_exists('WC') ) return; ?>
    <aside id="aurix-cart-panel" class="aurix-cart-panel" aria-hidden="true" inert role="dialog" aria-label="Shopping Cart">

      <!-- Header -->
      <div class="acp-header">
        <div class="acp-title">
          <div class="acp-title-icon"><i class="fas fa-shopping-bag"></i></div>
          <div>
            <span class="acp-title-text"><?php esc_html_e('Your Cart','aurix'); ?></span>
            <span class="acp-item-count">
              <?php $cnt = function_exists('WC')&&WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
              echo $cnt === 1 ? '1 item' : esc_html($cnt).' items'; ?>
            </span>
          </div>
        </div>
        <button class="acp-close" id="cartPanelClose" aria-label="Close cart">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Trust bar (replaces free shipping bar) -->
      <?php $cart_total = function_exists('WC')&&WC()->cart ? (float)WC()->cart->get_cart_contents_total() : 0; ?>
      <div class="acp-trust-bar">
        <div class="acp-trust-bar-item">
          <i class="fas fa-shield-alt"></i>
          <span>Secure Payment</span>
        </div>
        <div class="acp-trust-bar-divider"></div>
        <div class="acp-trust-bar-item">
          <i class="fas fa-medal"></i>
          <span>ISO 9001 Certified</span>
        </div>
        <div class="acp-trust-bar-divider"></div>
        <div class="acp-trust-bar-item">
          <i class="fas fa-undo-alt"></i>
          <span>30-Day Returns</span>
        </div>
      </div>

      <!-- Cart items -->
      <div class="acp-body">
        <div class="aurix-minicart-items"><?php woocommerce_mini_cart(); ?></div>
      </div>

      <!-- Footer -->
      <div class="acp-footer">

        <!-- Order summary -->
        <div class="acp-summary">
          <div class="acp-summary-row">
            <span class="acp-sum-label"><?php esc_html_e('Subtotal','aurix'); ?></span>
            <span class="acp-total-amt"><?php if(function_exists('WC')&&WC()->cart) echo WC()->cart->get_cart_subtotal(); ?></span>
          </div>
          <div class="acp-summary-row">
            <span class="acp-sum-label"><i class="fas fa-truck"></i> Shipping</span>
            <span class="acp-sum-note">Calculated at checkout</span>
          </div>
          <div class="acp-summary-divider"></div>
          <div class="acp-summary-row acp-summary-total">
            <span>Total</span>
            <span class="acp-grand-total"><?php if(function_exists('WC')&&WC()->cart) echo WC()->cart->get_cart_subtotal(); ?></span>
          </div>
        </div>

        <!-- Checkout button (full width, prominent) -->
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="acp-checkout-btn">
          <i class="fas fa-lock"></i> Secure Checkout
          <span class="acp-checkout-amt"><?php if(function_exists('WC')&&WC()->cart) echo WC()->cart->get_cart_subtotal(); ?></span>
        </a>

        <!-- Secondary: view cart -->
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="acp-viewcart-btn">
          View Full Cart <i class="fas fa-arrow-right"></i>
        </a>

      </div>
    </aside>
    <div id="aurix-overlay"></div>
    <?php
}, 5);

/* ═══ 8. WISHLIST SYSTEM ═══ */
function aurix_get_wishlist() {
    if ( is_user_logged_in() ) {
        return (array) get_user_meta(get_current_user_id(), '_aurix_wishlist', true);
    }
    return isset($_COOKIE['aurix_wishlist']) ? (array) json_decode(stripslashes($_COOKIE['aurix_wishlist']), true) : [];
}

function aurix_save_wishlist( $ids ) {
    if ( is_user_logged_in() ) {
        update_user_meta(get_current_user_id(), '_aurix_wishlist', array_values(array_unique(array_map('intval', $ids))));
    }
}

// AJAX: Toggle wishlist
function aurix_ajax_wishlist_toggle() {
    check_ajax_referer('aurix_nonce','nonce');
    $product_id = intval($_POST['product_id'] ?? 0);
    if ( !$product_id ) { wp_send_json_error('Invalid ID'); return; }

    $wishlist = aurix_get_wishlist();
    if ( in_array($product_id, $wishlist, true) ) {
        $wishlist = array_values(array_diff($wishlist, [$product_id]));
        $action = 'removed';
    } else {
        $wishlist[] = $product_id;
        $action = 'added';
    }
    aurix_save_wishlist($wishlist);
    wp_send_json_success(['action'=>$action,'count'=>count($wishlist),'wishlist'=>$wishlist]);
}
add_action('wp_ajax_aurix_wishlist_toggle',        'aurix_ajax_wishlist_toggle');
add_action('wp_ajax_nopriv_aurix_wishlist_toggle', 'aurix_ajax_wishlist_toggle');

// AJAX: Get wishlist count
function aurix_ajax_wishlist_count() {
    $wishlist = aurix_get_wishlist();
    wp_send_json_success(['count'=>count($wishlist)]);
}
add_action('wp_ajax_aurix_wishlist_count',        'aurix_ajax_wishlist_count');
add_action('wp_ajax_nopriv_aurix_wishlist_count', 'aurix_ajax_wishlist_count');

// Wishlist shortcode: [aurix_wishlist]
add_shortcode('aurix_wishlist', function() {
    $wishlist = aurix_get_wishlist();
    if ( empty($wishlist) ) {
        return '<div class="aurix-wishlist-empty"><i class="fas fa-heart"></i><h3>Your wishlist is empty</h3><p>Save items you love to find them later.</p><a href="'.esc_url(home_url('/products')).'">Browse Products</a></div>';
    }
    ob_start();
    echo '<div class="aurix-wishlist-grid">';
    foreach ($wishlist as $pid) {
        $product = wc_get_product($pid);
        if (!$product || !$product->is_visible()) continue;
        echo '<div class="aurix-wl-item" data-id="'.esc_attr($pid).'">';
        echo '<a href="'.esc_url($product->get_permalink()).'">';
        echo $product->get_image('woocommerce_thumbnail');
        echo '</a>';
        echo '<div class="aurix-wl-body">';
        echo '<h4><a href="'.esc_url($product->get_permalink()).'">'.esc_html($product->get_name()).'</a></h4>';
        echo '<span class="aurix-wl-price">'.wp_kses_post($product->get_price_html()).'</span>';
        echo '</div>';
        echo '<div class="aurix-wl-actions">';
        if ($product->is_in_stock()) {
            echo '<a href="?add-to-cart='.$pid.'" class="aurix-wl-add-btn add_to_cart_button ajax_add_to_cart" data-product_id="'.$pid.'"><i class="fas fa-cart-plus"></i> Add to Cart</a>';
        }
        echo '<button class="aurix-wl-remove" data-id="'.$pid.'" aria-label="Remove"><i class="fas fa-trash"></i></button>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    return ob_get_clean();
});

// Add wishlist page on theme activation
add_action('after_switch_theme', function() {
    if (!get_page_by_path('wishlist')) {
        wp_insert_post(['post_title'=>'Wishlist','post_name'=>'wishlist','post_status'=>'publish','post_type'=>'page','post_content'=>'[aurix_wishlist]']);
    }
});

/* ═══ 9. LIVE SEARCH AJAX ═══ */
function aurix_ajax_live_search() {
    check_ajax_referer('aurix_search_nonce','nonce');
    $q = sanitize_text_field($_POST['query'] ?? '');
    if ( strlen($q) < 2 ) { wp_send_json_success(['results'=> []]); return; }

    $results = [];

    // Products
    if ( function_exists('WC') ) {
        $products = wc_get_products(['s'=>$q,'limit'=>5,'status'=>'publish']);
        foreach ($products as $product) {
            $results[] = [
                'type'  => 'product',
                'label' => 'Product',
                'title' => $product->get_name(),
                'url'   => $product->get_permalink(),
                'img'   => wp_get_attachment_image_url($product->get_image_id(),'thumbnail') ?: '',
                'price' => strip_tags($product->get_price_html()),
                'sku'   => $product->get_sku(),
            ];
        }

        // Categories
        $cats = get_terms(['taxonomy'=>'product_cat','search'=>$q,'hide_empty'=>true,'number'=>4]);
        if (!is_wp_error($cats)) {
            foreach ($cats as $cat) {
                $results[] = [
                    'type'  => 'category',
                    'label' => 'Category',
                    'title' => $cat->name,
                    'url'   => get_term_link($cat),
                    'img'   => '',
                    'count' => $cat->count,
                ];
            }
        }
    }

    // Blog posts
    $posts = get_posts(['s'=>$q,'post_type'=>'post','posts_per_page'=>3,'post_status'=>'publish']);
    foreach ($posts as $post) {
        $results[] = [
            'type'  => 'blog',
            'label' => 'Blog',
            'title' => $post->post_title,
            'url'   => get_permalink($post),
            'img'   => get_the_post_thumbnail_url($post,'thumbnail') ?: '',
            'date'  => get_the_date('M j, Y', $post),
        ];
    }

    // Pages
    $pages = get_posts(['s'=>$q,'post_type'=>'page','posts_per_page'=>2,'post_status'=>'publish']);
    foreach ($pages as $page) {
        $results[] = [
            'type'  => 'page',
            'label' => 'Page',
            'title' => $page->post_title,
            'url'   => get_permalink($page),
            'img'   => '',
        ];
    }

    wp_send_json_success(['results'=>$results, 'query'=>$q]);
}
add_action('wp_ajax_aurix_live_search',        'aurix_ajax_live_search');
add_action('wp_ajax_nopriv_aurix_live_search', 'aurix_ajax_live_search');

/* ═══ 10. MY ACCOUNT MENU ═══ */
add_filter('woocommerce_account_menu_items', function($items) {
    return [
        'dashboard'       => __('Dashboard','aurix'),
        'orders'          => __('My Orders','aurix'),
        'downloads'       => __('Downloads','aurix'),
        'edit-address'    => __('Addresses','aurix'),
        'edit-account'    => __('Account Details','aurix'),
        'customer-logout' => __('Sign Out','aurix'),
    ];
});

/* ═══ 11. CHECKOUT ═══ */
add_filter('woocommerce_checkout_registration_required','__return_false');
add_filter('woocommerce_enable_signup_and_login_from_checkout','__return_true');

/* ═══ 12. REDIRECTS ═══ */
add_filter('woocommerce_login_redirect', function($r,$u){ return get_permalink(get_option('woocommerce_myaccount_page_id')); }, 10, 2);
add_action('wp_logout', function(){ wp_redirect(home_url('/')); exit; });

/* ═══ 13. WIDGETS ═══ */
function aurix_widgets() {
    register_sidebar(['name'=>__('Sidebar','aurix'),'id'=>'sidebar-1','before_widget'=>'<section id="%1$s" class="widget %2$s">','after_widget'=>'</section>','before_title'=>'<h2 class="widget-title">','after_title'=>'</h2>']);
    register_sidebar(['name'=>__('Footer Widgets','aurix'),'id'=>'footer-widgets','before_widget'=>'<div class="footer-widget">','after_widget'=>'</div>','before_title'=>'<h4 class="footer-widget-title">','after_title'=>'</h4>']);
}
add_action('widgets_init','aurix_widgets');

/* ═══ 14. BODY CLASSES ═══ */
add_filter('body_class', function($c) {
    if (is_singular()) $c[]='singular';
    if (function_exists('WC')) $c[]='woo-enabled';
    if (is_user_logged_in()) $c[]='user-logged-in';
    if (function_exists('is_account_page') && is_account_page()) $c[]='aurix-account-page';
    if (function_exists('is_cart') && is_cart()) $c[]='aurix-cart-page';
    if (function_exists('is_checkout') && is_checkout()) $c[]='aurix-checkout-page';
    return $c;
});

/* ═══ 15. CUSTOMIZER ═══ */
add_action('customize_register', function($wp_customize) {

    /* ── Aurix: Contact Info ── */
    $wp_customize->add_section('aurix_contact', [
        'title'       => __('Aurix — Contact Info', 'aurix'),
        'description' => __('All fields on the Contact Us page. Edit here and Save & Publish.', 'aurix'),
        'priority'    => 30,
    ]);

    // ── Core ──
    $wp_customize->add_setting('aurix_phone', ['default'=>'+1 (347) 727-3384','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_phone', ['label'=>__('Phone Number','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_email', ['default'=>'info@aurixinternational.com','sanitize_callback'=>'sanitize_email','transport'=>'refresh']);
    $wp_customize->add_control('aurix_email', ['label'=>__('Email Address','aurix'),'section'=>'aurix_contact','type'=>'email']);

    // ── Top Bar ──
    $wp_customize->add_setting('aurix_topbar_enabled', ['default'=>true,'sanitize_callback'=>'wp_validate_boolean','transport'=>'refresh']);
    $wp_customize->add_control('aurix_topbar_enabled', ['label'=>__('Show Top Announcement Bar','aurix'),'description'=>__('Turns off the entire top bar (announcement, email, worldwide text, Track Order) sitewide.','aurix'),'section'=>'aurix_contact','type'=>'checkbox']);
    $wp_customize->add_setting('aurix_topbar_badge', ['default'=>'NEW','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_topbar_badge', ['label'=>__('Top Bar Badge Text','aurix'),'description'=>__('The small pill before the announcement text (e.g. "NEW"). Leave blank to hide just the badge.','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_topbar_text', ['default'=>'Pre-assembled Surgical & Dental Kits now available','sanitize_callback'=>'wp_kses_post','transport'=>'refresh']);
    $wp_customize->add_control('aurix_topbar_text', ['label'=>__('Top Bar Announcement Text','aurix'),'description'=>__('Text shown in the top announcement bar','aurix'),'section'=>'aurix_contact','type'=>'textarea']);
    $wp_customize->add_setting('aurix_topbar_link', ['default'=>'#','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_topbar_link', ['label'=>__('Top Bar Link URL','aurix'),'description'=>__('e.g. /custom-kits','aurix'),'section'=>'aurix_contact','type'=>'url']);
    $wp_customize->add_setting('aurix_topbar_track_url', ['default'=>'/tracking','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_topbar_track_url', ['label'=>__('Top Bar — Track Order Link','aurix'),'description'=>__('A relative path (e.g. /tracking) or a full URL (https://...)','aurix'),'section'=>'aurix_contact','type'=>'text']);

    // ── Contact Page: Hero ──
    $wp_customize->add_setting('aurix_contact_hero_tag',   ['default'=>'Get in Touch','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hero_tag',   ['label'=>__('Contact Page — Hero Tag','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hero_h1',    ['default'=>"We'd Love to<br>Hear from <em>You</em>",'sanitize_callback'=>'wp_kses_post','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hero_h1',    ['label'=>__('Contact Page — Hero Heading (HTML allowed)','aurix'),'section'=>'aurix_contact','type'=>'textarea']);
    $wp_customize->add_setting('aurix_contact_hero_sub',   ['default'=>'Reach out for business inquiries, partnerships, or any questions. We respond within one business day.','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hero_sub',   ['label'=>__('Contact Page — Hero Subtext','aurix'),'section'=>'aurix_contact','type'=>'textarea']);

    // ── Contact Page: Stats ──
    $wp_customize->add_setting('aurix_contact_stat1_num',   ['default'=>'24h','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat1_num',   ['label'=>__('Stat 1 — Number','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat1_label', ['default'=>'Response Time','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat1_label', ['label'=>__('Stat 1 — Label','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat2_num',   ['default'=>'50+','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat2_num',   ['label'=>__('Stat 2 — Number','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat2_label', ['default'=>'Countries Served','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat2_label', ['label'=>__('Stat 2 — Label','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat3_num',   ['default'=>'15+','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat3_num',   ['label'=>__('Stat 3 — Number','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat3_label', ['default'=>'Years Experience','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat3_label', ['label'=>__('Stat 3 — Label','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat4_num',   ['default'=>'98%','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat4_num',   ['label'=>__('Stat 4 — Number','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_stat4_label', ['default'=>'Client Satisfaction','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_stat4_label', ['label'=>__('Stat 4 — Label','aurix'),'section'=>'aurix_contact','type'=>'text']);

    // ── Contact Page: Address & Details ──
    $wp_customize->add_setting('aurix_contact_address',    ['default'=>'USA-based<br>4024 Avenue S, Brooklyn New York. 11234','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_address',    ['label'=>__('Contact Page — Address Line','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_phone2',     ['default'=>'+1 (347) 325-5355','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_phone2',     ['label'=>__('Contact Page — Second Phone (optional)','aurix'),'section'=>'aurix_contact','type'=>'text']);

    // ── Contact Page: Hours ──
    $wp_customize->add_setting('aurix_contact_hours_1_day',   ['default'=>'Mon – Fri','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_1_day',   ['label'=>__('Hours Row 1 — Day','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_1_time',  ['default'=>'9AM – 6PM EST','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_1_time',  ['label'=>__('Hours Row 1 — Time','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_1_badge', ['default'=>'open','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_1_badge', ['label'=>__('Hours Row 1 — Badge (open/limited/closed)','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_2_day',   ['default'=>'Saturday','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_2_day',   ['label'=>__('Hours Row 2 — Day','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_2_time',  ['default'=>'10AM – 2PM','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_2_time',  ['label'=>__('Hours Row 2 — Time','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_2_badge', ['default'=>'limited','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_2_badge', ['label'=>__('Hours Row 2 — Badge (open/limited/closed)','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_3_day',   ['default'=>'Sunday','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_3_day',   ['label'=>__('Hours Row 3 — Day','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_3_time',  ['default'=>'—','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_3_time',  ['label'=>__('Hours Row 3 — Time','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_hours_3_badge', ['default'=>'closed','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_hours_3_badge', ['label'=>__('Hours Row 3 — Badge (open/limited/closed)','aurix'),'section'=>'aurix_contact','type'=>'text']);

    // ── Contact Page: Map ──
    $wp_customize->add_setting('aurix_contact_map_embed',  ['default'=>'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14483.482!2d-74.006!3d40.7128!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY!5e0!3m2!1sen!2sus!4v1700000000','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_map_embed',  ['label'=>__('Contact Page — Google Maps Embed URL','aurix'),'description'=>__('Paste the src URL from Google Maps → Share → Embed a map','aurix'),'section'=>'aurix_contact','type'=>'url']);
    $wp_customize->add_setting('aurix_contact_map_label',  ['default'=>'USA-Based Operations','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_map_label',  ['label'=>__('Contact Page — Map Footer Label','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_map_link',   ['default'=>'https://maps.google.com','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_map_link',   ['label'=>__('Contact Page — Directions Link','aurix'),'section'=>'aurix_contact','type'=>'url']);

    // ── Contact Page: Social ──
    $wp_customize->add_setting('aurix_contact_social_li',  ['default'=>'https://www.linkedin.com/company/aurix-international/','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_social_li',  ['label'=>__('Contact Page — LinkedIn URL','aurix'),'section'=>'aurix_contact','type'=>'url']);
    $wp_customize->add_setting('aurix_contact_social_fb',  ['default'=>'https://www.facebook.com/profile.php?id=61590405910410','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_social_fb',  ['label'=>__('Contact Page — Facebook URL','aurix'),'section'=>'aurix_contact','type'=>'url']);
    $wp_customize->add_setting('aurix_contact_social_ig',  ['default'=>'https://www.instagram.com/aurix.international/','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_social_ig',  ['label'=>__('Contact Page — Instagram URL','aurix'),'section'=>'aurix_contact','type'=>'url']);

    // ── Contact Page: FAQ ──
    $wp_customize->add_setting('aurix_contact_faq_title',  ['default'=>'Frequently Asked Questions','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_faq_title',  ['label'=>__('FAQ — Section Title','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_contact_faq_sub',    ['default'=>'Quick answers to common inquiries about working with us.','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_contact_faq_sub',    ['label'=>__('FAQ — Section Subtitle','aurix'),'section'=>'aurix_contact','type'=>'text']);
    for ($i = 1; $i <= 3; $i++) {
        $wp_customize->add_setting("aurix_contact_faq_{$i}_q", ['default'=>'','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
        $wp_customize->add_control("aurix_contact_faq_{$i}_q", ['label'=>__("FAQ {$i} — Question",'aurix'),'section'=>'aurix_contact','type'=>'text']);
        $wp_customize->add_setting("aurix_contact_faq_{$i}_a", ['default'=>'','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
        $wp_customize->add_control("aurix_contact_faq_{$i}_a", ['label'=>__("FAQ {$i} — Answer",'aurix'),'section'=>'aurix_contact','type'=>'textarea']);
    }


    // ── Contact Form — Email Settings ──
    $wp_customize->add_setting('aurix_cf_admin_email',   ['default'=>get_option('admin_email'),'sanitize_callback'=>'sanitize_email','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_admin_email',   ['label'=>__('Email Notifications — Admin Recipient','aurix'),'description'=>__('Where contact form submissions are sent.','aurix'),'section'=>'aurix_contact','type'=>'email']);

    $wp_customize->add_setting('aurix_cf_admin_cc',      ['default'=>'','sanitize_callback'=>'sanitize_email','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_admin_cc',      ['label'=>__('Email Notifications — CC (optional)','aurix'),'description'=>__('Add a second email to CC on every submission.','aurix'),'section'=>'aurix_contact','type'=>'email']);

    $wp_customize->add_setting('aurix_cf_from_name',     ['default'=>'Aurix International','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_from_name',     ['label'=>__('Email Notifications — From Name','aurix'),'description'=>__('Sender name shown in the recipient\'s inbox.','aurix'),'section'=>'aurix_contact','type'=>'text']);

    $wp_customize->add_setting('aurix_cf_from_email',    ['default'=>'noreply@aurixinternational.com','sanitize_callback'=>'sanitize_email','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_from_email',    ['label'=>__('Email Notifications — From Email','aurix'),'description'=>__('Must be a valid address. Use an address on your domain for best deliverability.','aurix'),'section'=>'aurix_contact','type'=>'email']);

    $wp_customize->add_setting('aurix_cf_admin_subject', ['default'=>'New Contact Inquiry: {subject}','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_admin_subject', ['label'=>__('Admin Email — Subject Line','aurix'),'description'=>__('Placeholders: {name} {subject} {type} {company}','aurix'),'section'=>'aurix_contact','type'=>'text']);

    $wp_customize->add_setting('aurix_cf_user_subject',  ['default'=>'We received your message — Aurix International','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_user_subject',  ['label'=>__('User Confirmation Email — Subject Line','aurix'),'description'=>__('Placeholders: {name} {subject} {type} {company}','aurix'),'section'=>'aurix_contact','type'=>'text']);

    $wp_customize->add_setting('aurix_cf_user_greeting', ['default'=>'Thank you for reaching out, {name}. We have received your inquiry and our team will review it shortly.','sanitize_callback'=>'sanitize_textarea_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_cf_user_greeting', ['label'=>__('User Confirmation Email — Greeting Paragraph','aurix'),'description'=>__('Placeholders: {name} {subject} {type} {company}','aurix'),'section'=>'aurix_contact','type'=>'textarea']);

        /* ── Aurix: Kit Builder Section ── */
    $wp_customize->add_section('aurix_kit_builder', [
        'title'       => __('Aurix — Kit Builder Section', 'aurix'),
        'description' => __('Edit the Kit Builder block that appears on the front page or via shortcode [aurix_kit_builder]', 'aurix'),
        'priority'    => 35,
    ]);

    // Left card
    $wp_customize->add_setting('aurix_kb_eyebrow',  ['default'=>'Custom instrument trays','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_eyebrow',  ['label'=>__('Eyebrow Text (small label above "Build")','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_big_word', ['default'=>'Build','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_big_word', ['label'=>__('Big Word (gold, large font)','aurix'),'description'=>__('Default: Build','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_subtitle', ['default'=>'Your kit. Your way.','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_subtitle', ['label'=>__('Subtitle','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_desc', ['default'=>'Configure your signature instrument tray from scratch. Search any tool, drag & drop your layout, and submit for a custom quote — all in one place.','sanitize_callback'=>'sanitize_textarea_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_desc', ['label'=>__('Description Text','aurix'),'section'=>'aurix_kit_builder','type'=>'textarea']);

    $wp_customize->add_setting('aurix_kb_btn_text', ['default'=>'Open Kit Builder','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_btn_text', ['label'=>__('Button Text','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_btn_url',  ['default'=>'/custom-kits','sanitize_callback'=>'esc_url_raw','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_btn_url',  ['label'=>__('Button URL','aurix'),'description'=>__('Page the button links to, e.g. /custom-kits','aurix'),'section'=>'aurix_kit_builder','type'=>'url']);

    // Stats
    $wp_customize->add_setting('aurix_kb_stat1_num',   ['default'=>'500+','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_stat1_num',   ['label'=>__('Stat 1 — Number','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);
    $wp_customize->add_setting('aurix_kb_stat1_label', ['default'=>'Instruments','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_stat1_label', ['label'=>__('Stat 1 — Label','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_stat2_num',   ['default'=>'48h','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_stat2_num',   ['label'=>__('Stat 2 — Number','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);
    $wp_customize->add_setting('aurix_kb_stat2_label', ['default'=>'Quote turnaround','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_stat2_label', ['label'=>__('Stat 2 — Label','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_stat3_num',   ['default'=>'Global','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_stat3_num',   ['label'=>__('Stat 3 — Number','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);
    $wp_customize->add_setting('aurix_kb_stat3_label', ['default'=>'Suppliers','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_stat3_label', ['label'=>__('Stat 3 — Label','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    // Note card
    $wp_customize->add_setting('aurix_kb_note_season', ['default'=>'Fall 2026 Collection','sanitize_callback'=>'sanitize_text_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_note_season', ['label'=>__('Note Card — Season Label','aurix'),'section'=>'aurix_kit_builder','type'=>'text']);

    $wp_customize->add_setting('aurix_kb_note_text', ['default'=>'Currently manufacturing our Fall 2026 USA Collection. Secure early-access pricing by joining the VIP Waitlist today.','sanitize_callback'=>'sanitize_textarea_field','transport'=>'refresh']);
    $wp_customize->add_control('aurix_kb_note_text', ['label'=>__('Note Card — Message','aurix'),'section'=>'aurix_kit_builder','type'=>'textarea']);

    /* ── Aurix: Out of Stock Text ── */
    $wp_customize->add_section('aurix_oos_text', [
        'title'       => __('Aurix — Out of Stock Text', 'aurix'),
        'description' => __('Edit the badge and message shown on product pages when an item is out of stock. No coding needed — just type and save.', 'aurix'),
        'priority'    => 36,
    ]);

    // Badge label (replaces "Out of Stock")
    $wp_customize->add_setting('aurix_oos_badge_text', [
        'default'           => 'Out of Stock',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_oos_badge_text', [
        'label'       => __('Badge Text', 'aurix'),
        'description' => __('Shown as the small badge on the product image. e.g. "Coming Soon", "Next Batch Available Soon"', 'aurix'),
        'section'     => 'aurix_oos_text',
        'type'        => 'text',
    ]);

    // Badge tooltip (hover text)
    $wp_customize->add_setting('aurix_oos_badge_tip', [
        'default'           => 'This item is currently out of stock. Contact us to be notified when it is available, or request a wholesale quote.',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_oos_badge_tip', [
        'label'       => __('Badge Tooltip (hover text)', 'aurix'),
        'description' => __('Text shown when hovering over the badge.', 'aurix'),
        'section'     => 'aurix_oos_text',
        'type'        => 'textarea',
    ]);

    // Waitlist section heading
    $wp_customize->add_setting('aurix_oos_waitlist_heading', [
        'default'           => 'Add to Our Waitlist',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_oos_waitlist_heading', [
        'label'       => __('Waitlist Section — Heading', 'aurix'),
        'description' => __('The heading next to the bell icon. e.g. "Join the Next Batch"', 'aurix'),
        'section'     => 'aurix_oos_text',
        'type'        => 'text',
    ]);

    // Waitlist message
    $wp_customize->add_setting('aurix_oos_waitlist_msg', [
        'default'           => 'This item is currently out of stock. Join the waitlist and we\'ll notify you the moment it\'s back.',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_oos_waitlist_msg', [
        'label'       => __('Waitlist Section — Message', 'aurix'),
        'description' => __('The paragraph shown below the heading. e.g. "We work in batches. Enter your email and we\'ll notify you when the next batch is ready."', 'aurix'),
        'section'     => 'aurix_oos_text',
        'type'        => 'textarea',
    ]);

    // Notify Me button text
    $wp_customize->add_setting('aurix_oos_notify_btn', [
        'default'           => 'Notify Me',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_oos_notify_btn', [
        'label'       => __('Waitlist Section — Button Text', 'aurix'),
        'description' => __('Text on the submit button. e.g. "Notify Me", "Join Batch List"', 'aurix'),
        'section'     => 'aurix_oos_text',
        'type'        => 'text',
    ]);

    /* ── Aurix: Header Text ── */
    $wp_customize->add_section('aurix_header_text', [
        'title'       => __('Aurix — Header Text', 'aurix'),
        'description' => __('Edit text shown in the top bar and header. No coding needed.', 'aurix'),
        'priority'    => 25,
    ]);

    // Top bar right side text
    $wp_customize->add_setting('aurix_topbar_worldwide', [
        'default'           => 'Worldwide Distribution & Export',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_topbar_worldwide', [
        'label'       => __('Top Bar — Worldwide Label', 'aurix'),
        'description' => __('Text next to the globe icon in the top bar.', 'aurix'),
        'section'     => 'aurix_header_text',
        'type'        => 'text',
    ]);

    // Top bar "Track Order" label
    $wp_customize->add_setting('aurix_topbar_track_label', [
        'default'           => 'Track Order',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_topbar_track_label', [
        'label'       => __('Top Bar — Track Order Label', 'aurix'),
        'section'     => 'aurix_header_text',
        'type'        => 'text',
    ]);

    // "Today's Deals" / "Special Offer" pills are now managed via
    // Appearance -> Menus -> "Header Quick Links (Pills)" -- add, remove,
    // reorder, or rename them there instead of fixed text/URL fields here.

    // "ALL CATEGORIES" dropdown button label
    $wp_customize->add_setting('aurix_all_categories_label', [
        'default'           => 'All Categories',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_all_categories_label', [
        'label'       => __('Nav — "All Categories" Button Label', 'aurix'),
        'description' => __('The button itself still opens the category dropdown — only the text is editable here.', 'aurix'),
        'section'     => 'aurix_header_text',
        'type'        => 'text',
    ]);

    // Header call button label
    $wp_customize->add_setting('aurix_header_call_label', [
        'default'           => 'CONTACT NOW',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_header_call_label', [
        'label'       => __('Header — Call Button Label (above phone number)', 'aurix'),
        'section'     => 'aurix_header_text',
        'type'        => 'text',
    ]);

    // Search placeholder
    $wp_customize->add_setting('aurix_search_placeholder', [
        'default'           => 'Search surgical & dental instruments…',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_search_placeholder', [
        'label'       => __('Search Box Placeholder Text', 'aurix'),
        'section'     => 'aurix_header_text',
        'type'        => 'text',
    ]);

    /* ── Aurix: Footer Text ── */
    $wp_customize->add_section('aurix_footer_text', [
        'title'       => __('Aurix — Footer Text', 'aurix'),
        'description' => __('Edit the footer brand description, address, and bottom bar text.', 'aurix'),
        'priority'    => 26,
    ]);

    // Footer brand description
    $wp_customize->add_setting('aurix_footer_brand_desc', [
        'default'           => 'Aurix International is a USA-based supplier and distributor of precision surgical & dental instruments — combining four generations of manufacturing expertise with modern, efficient sourcing solutions for healthcare professionals worldwide.',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_footer_brand_desc', [
        'label'       => __('Footer — Brand Description', 'aurix'),
        'description' => __('The paragraph under the logo in the footer.', 'aurix'),
        'section'     => 'aurix_footer_text',
        'type'        => 'textarea',
    ]);

    // Footer column headers
    $footer_col_headers = [
        'aurix_footer_col_about'    => 'About Us',
        'aurix_footer_col_products' => 'Our Products',
        'aurix_footer_col_care'     => 'Customer Care',
        'aurix_footer_col_address'  => 'Our Address',
    ];
    foreach ( $footer_col_headers as $setting_id => $default_label ) {
        $wp_customize->add_setting( $setting_id, [
            'default'           => $default_label,
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ] );
        $wp_customize->add_control( $setting_id, [
            'label'   => sprintf( __( 'Footer — Column Header: "%s"', 'aurix' ), $default_label ),
            'section' => 'aurix_footer_text',
            'type'    => 'text',
        ] );
    }

    // Footer address lines (up to 5)
    for ( $i = 1; $i <= 5; $i++ ) {
        $defaults = [ 1 => 'USA-based', 2 => 'Worldwide Distribution & Export', 3 => '', 4 => '', 5 => '' ];
        $wp_customize->add_setting("aurix_footer_address_{$i}", [
            'default'           => $defaults[$i],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_control("aurix_footer_address_{$i}", [
            'label'       => sprintf(__('Footer — Address Line %d', 'aurix'), $i),
            'description' => $i === 1 ? __('Leave a line blank to hide it.', 'aurix') : '',
            'section'     => 'aurix_footer_text',
            'type'        => 'text',
        ]);
    }

    // Footer extra phone numbers (up to 3; phone 1 comes from aurix_phone)
    for ( $i = 2; $i <= 3; $i++ ) {
        $wp_customize->add_setting("aurix_footer_phone_{$i}", [
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_control("aurix_footer_phone_{$i}", [
            'label'       => sprintf(__('Footer — Extra Phone %d', 'aurix'), $i),
            'description' => $i === 2 ? __('Leave blank to hide. Main phone comes from "Aurix — Contact Info".', 'aurix') : '',
            'section'     => 'aurix_footer_text',
            'type'        => 'text',
        ]);
    }

    // Footer extra emails (up to 3; email 1 comes from aurix_email)
    for ( $i = 2; $i <= 3; $i++ ) {
        $wp_customize->add_setting("aurix_footer_email_{$i}", [
            'default'           => '',
            'sanitize_callback' => 'sanitize_email',
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_control("aurix_footer_email_{$i}", [
            'label'       => sprintf(__('Footer — Extra Email %d', 'aurix'), $i),
            'description' => $i === 2 ? __('Leave blank to hide. Main email comes from "Aurix — Contact Info".', 'aurix') : '',
            'section'     => 'aurix_footer_text',
            'type'        => 'email',
        ]);
    }

    // Footer mobile brand tagline
    $wp_customize->add_setting('aurix_footer_mob_tagline', [
        'default'           => 'USA-based supplier of precision surgical and dental instruments — four generations of expertise delivered worldwide.',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_footer_mob_tagline', [
        'label'       => __('Footer — Mobile Tagline', 'aurix'),
        'description' => __('Short tagline shown in the mobile footer.', 'aurix'),
        'section'     => 'aurix_footer_text',
        'type'        => 'textarea',
    ]);

    // Footer copyright company name
    $wp_customize->add_setting('aurix_footer_copyright_name', [
        'default'           => 'Aurix International',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_footer_copyright_name', [
        'label'       => __('Footer — Copyright Company Name', 'aurix'),
        'description' => __('The bold name in "© 2025 Aurix International. All Rights Reserved."', 'aurix'),
        'section'     => 'aurix_footer_text',
        'type'        => 'text',
    ]);

    // Footer developer credit ("Designed & Developed By ...")
    $wp_customize->add_setting('aurix_footer_dev_credit', [
        'default'           => 'Designed &amp; Developed By <span class="dev-mark"><span style="color:#4DA6FF">TecSol</span><span style="color:#fff">Pr</span><span style="color:#FF5252">o</span></span>',
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control('aurix_footer_dev_credit', [
        'label'       => __('Footer — "Designed & Developed By" Credit', 'aurix'),
        'description' => __('HTML allowed, so you can keep/change the colored styling or switch to a different developer/agency name. Leave blank to hide this line entirely.', 'aurix'),
        'section'     => 'aurix_footer_text',
        'type'        => 'textarea',
    ]);

    /* ── Aurix: Social Media Links ── */
    $wp_customize->add_section('aurix_social_links', [
        'title'       => __('Aurix — Social Media Links', 'aurix'),
        'description' => __('Enter full profile URLs for built-in platforms. Use the Custom Links slots to add any other network — paste the URL and type the platform name (e.g. TikTok, WhatsApp, Threads). Leave blank to hide.', 'aurix'),
        'priority'    => 27,
    ]);

    // ── Built-in platforms ──
    $socials = [
        'facebook'  => [ 'label' => 'Facebook URL',    'placeholder' => 'https://facebook.com/yourpage' ],
        'twitter'   => [ 'label' => 'X / Twitter URL', 'placeholder' => 'https://x.com/yourhandle' ],
        'linkedin'  => [ 'label' => 'LinkedIn URL',    'placeholder' => 'https://linkedin.com/company/aurix' ],
        'instagram' => [ 'label' => 'Instagram URL',   'placeholder' => 'https://instagram.com/yourhandle' ],
        'youtube'   => [ 'label' => 'YouTube URL',     'placeholder' => 'https://youtube.com/@yourchannel' ],
        'tiktok'    => [ 'label' => 'TikTok URL',      'placeholder' => 'https://tiktok.com/@yourhandle' ],
        'pinterest' => [ 'label' => 'Pinterest URL',   'placeholder' => 'https://pinterest.com/yourprofile' ],
        'whatsapp'  => [ 'label' => 'WhatsApp URL',    'placeholder' => 'https://wa.me/1234567890' ],
        'threads'   => [ 'label' => 'Threads URL',     'placeholder' => 'https://threads.net/@yourhandle' ],
    ];

    foreach ( $socials as $key => $meta ) {
        $wp_customize->add_setting("aurix_social_{$key}", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_control("aurix_social_{$key}", [
            'label'       => __($meta['label'], 'aurix'),
            'description' => $meta['placeholder'],
            'section'     => 'aurix_social_links',
            'type'        => 'url',
        ]);
    }

    // ── Custom Link slots (any platform) ──
    for ( $i = 1; $i <= 5; $i++ ) {
        $wp_customize->add_setting("aurix_social_custom_{$i}_url", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_control("aurix_social_custom_{$i}_url", [
            'label'       => sprintf( __('Custom Link %d — URL', 'aurix'), $i ),
            'description' => __('Paste the full profile / page URL', 'aurix'),
            'section'     => 'aurix_social_links',
            'type'        => 'url',
        ]);
        $wp_customize->add_setting("aurix_social_custom_{$i}_label", [
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ]);
        $wp_customize->add_control("aurix_social_custom_{$i}_label", [
            'label'       => sprintf( __('Custom Link %d — Platform Name', 'aurix'), $i ),
            'description' => __('e.g. TikTok, Snapchat, Telegram, Behance …', 'aurix'),
            'section'     => 'aurix_social_links',
            'type'        => 'text',
        ]);
    }

});

add_filter('excerpt_length', function(){ return 25; });

/* ═══ SOCIAL ICONS HELPER ═══ */
/**
 * Renders social icon links from Customizer settings.
 * Icons with no URL set are hidden automatically.
 *
 * @param string $extra_classes  Extra CSS classes on the wrapper div
 */
function aurix_social_icons( $extra_classes = '' ) {
    // SVG paths for built-in platforms
    $icons = [
        'facebook'  => [ 'class' => 'fsoc--fb',  'label' => 'Facebook',    'path' => 'M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z' ],
        'twitter'   => [ 'class' => 'fsoc--x',   'label' => 'X / Twitter', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63 5.905-5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z' ],
        'linkedin'  => [ 'class' => 'fsoc--li',  'label' => 'LinkedIn',    'path' => 'M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 011.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 001.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 00-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z' ],
        'instagram' => [ 'class' => 'fsoc--ig',  'label' => 'Instagram',   'path' => 'M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2m-.2 2A3.6 3.6 0 004 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 003.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 011.25 1.25A1.25 1.25 0 0117.25 8 1.25 1.25 0 0116 6.75a1.25 1.25 0 011.25-1.25M12 7a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5m0 2a3 3 0 00-3 3 3 3 0 003 3 3 3 0 003-3 3 3 0 00-3-3z' ],
        'youtube'   => [ 'class' => 'fsoc--yt',  'label' => 'YouTube',     'path' => 'M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z' ],
        'tiktok'    => [ 'class' => 'fsoc--tt',  'label' => 'TikTok',      'path' => 'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.78a4.85 4.85 0 01-1.01-.09z' ],
        'pinterest' => [ 'class' => 'fsoc--pin', 'label' => 'Pinterest',   'path' => 'M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.181-.78 1.172-4.97 1.172-4.97s-.299-.598-.299-1.482c0-1.388.806-2.428 1.808-2.428.852 0 1.266.64 1.266 1.408 0 .858-.546 2.14-.828 3.33-.236.995.499 1.806 1.476 1.806 1.772 0 3.137-1.868 3.137-4.564 0-2.387-1.715-4.057-4.163-4.057-2.836 0-4.5 2.126-4.5 4.322 0 .856.33 1.772.741 2.273a.3.3 0 01.069.286c-.076.314-.243.995-.276 1.134-.044.183-.145.222-.335.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.966-.527-2.292-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.938.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S17.523 2 12 2z' ],
        'whatsapp'  => [ 'class' => 'fsoc--wa',  'label' => 'WhatsApp',    'path' => 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.985-1.306A9.953 9.953 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z' ],
        'threads'   => [ 'class' => 'fsoc--th',  'label' => 'Threads',     'path' => 'M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.5 12.068c0-3.819 1.045-6.845 3.107-8.994C6.704 1.01 9.531 0 13.0 0c3.055 0 5.65.875 7.49 2.53 1.748 1.578 2.724 3.8 2.724 6.268 0 3.896-1.577 6.22-4.323 6.22-1.018 0-1.937-.408-2.536-1.12-.626.707-1.535 1.12-2.59 1.12-2.145 0-3.657-1.722-3.657-4.082 0-2.31 1.488-3.97 3.6-3.97.808 0 1.543.297 2.074.836l.178-.7h1.844l-1.2 5.375c-.156.693-.107 1.07.155 1.28.217.174.555.213.908.106.93-.283 2.04-1.54 2.04-4.073 0-3.904-2.668-6.42-6.716-6.42-2.595 0-4.77.87-6.3 2.517-1.477 1.591-2.256 3.877-2.256 6.623 0 3.014.707 5.45 2.043 7.055 1.303 1.563 3.345 2.378 5.904 2.394h.006c.643 0 1.284-.06 1.904-.18l.33 1.79c-.737.136-1.49.205-2.239.205zm.857-10.513c.903 0 1.603-.763 1.603-1.73 0-.968-.7-1.73-1.603-1.73-.904 0-1.604.762-1.604 1.73 0 .967.7 1.73 1.604 1.73z' ],
    ];

    // Generic link icon SVG path (used for custom slots)
    $generic_path = 'M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z';

    $wrapper_class = trim('footer-socials ' . $extra_classes);
    echo '<div class="' . esc_attr($wrapper_class) . '">';

    // Render built-in platform icons
    foreach ( $icons as $key => $icon ) {
        $url = get_theme_mod("aurix_social_{$key}", '');
        if ( ! $url ) continue;
        printf(
            '<a class="fsoc %s" href="%s" aria-label="%s" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24"><path d="%s"/></svg></a>',
            esc_attr($icon['class']),
            esc_url($url),
            esc_attr($icon['label']),
            esc_attr($icon['path'])
        );
    }

    // Render custom link slots
    for ( $i = 1; $i <= 5; $i++ ) {
        $url   = get_theme_mod("aurix_social_custom_{$i}_url", '');
        $label = get_theme_mod("aurix_social_custom_{$i}_label", '');
        if ( ! $url ) continue;
        $label = $label ? $label : "Social Link {$i}";
        printf(
            '<a class="fsoc fsoc--custom" href="%s" aria-label="%s" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24"><path d="%s"/></svg></a>',
            esc_url($url),
            esc_attr($label),
            esc_attr($generic_path)
        );
    }

    echo '</div>';
}

/* ═══ 16. PRODUCT WISHLIST BUTTON on product loop ═══ */
add_action('woocommerce_after_shop_loop_item', function() {
    global $product;
    if (!$product) return;
    $wishlist = aurix_get_wishlist();
    $in_wl = in_array($product->get_id(), $wishlist, true);
    printf(
        '<button class="aurix-wl-btn%s" data-id="%d" aria-label="Wishlist" title="%s"><i class="fas fa-heart"></i></button>',
        $in_wl ? ' in-wishlist' : '',
        $product->get_id(),
        $in_wl ? 'Remove from Wishlist' : 'Add to Wishlist'
    );
}, 15);

/* ═══ 17. Add wishlist count to header (output to JS) ═══ */
add_action('wp_footer', function() {
    if (!is_admin()) {
        $count = count(aurix_get_wishlist());
        echo '<script>window.aurixWishlistCount=' . intval($count) . ';</script>';
    }
}, 1);

/* ═══ PRODUCT PAGE ASSETS ═══ */
add_action('wp_enqueue_scripts', function() {
    if ( ! is_product() ) return;
    wp_enqueue_style('aurix-product', get_template_directory_uri().'/css/product.css', ['aurix-style'], '36.93.0');
    // QR code library
    wp_enqueue_script('qrcode-js', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', [], '1.0.0', true);
    wp_enqueue_script('aurix-product', get_template_directory_uri().'/js/product.js', ['jquery','aurix-main','qrcode-js'], '36.93.0', true);
});

/* ═══ RFQ SUBMIT HANDLER ═══ */
function aurix_rfq_submit() {
    check_ajax_referer('aurix_nonce','nonce');
    $name    = sanitize_text_field($_POST['rfq_name']    ?? '');
    $email   = sanitize_email($_POST['rfq_email']        ?? '');
    $company = sanitize_text_field($_POST['rfq_company'] ?? '');
    $country = sanitize_text_field($_POST['rfq_country'] ?? '');
    $product = sanitize_text_field($_POST['rfq_product'] ?? '');
    $sku     = sanitize_text_field($_POST['rfq_sku']     ?? '');
    $qty     = sanitize_text_field($_POST['rfq_qty']     ?? '');
    $price   = sanitize_text_field($_POST['rfq_price']   ?? '');
    $message = sanitize_textarea_field($_POST['rfq_message'] ?? '');
    $url     = esc_url_raw($_POST['rfq_url'] ?? '');
    if ( ! $name || ! is_email($email) ) { wp_send_json_error('Missing required fields'); return; }
    $admin = get_option('admin_email');
    $subj  = 'Wholesale Quote: ' . $product . ' (SKU: ' . $sku . ')';
    $body  = "Product: $product\nSKU: $sku\nURL: $url\n\nName: $name\nEmail: $email\nCompany: $company\nCountry: $country\nQty: $qty\nTarget $: $price\n\nMessage:\n$message";
    $hdrs  = ['Content-Type: text/plain; charset=UTF-8','From: '.get_bloginfo('name').' <'.$admin.'>','Reply-To: '.$name.' <'.$email.'>'];
    $sent  = wp_mail($admin, $subj, $body, $hdrs);
    wp_mail($email, 'Your Quote Request — Aurix International', "Dear $name,\n\nThank you! We received your quote request for $product (SKU: $sku, Qty: $qty).\nWe'll respond within 24 hours.\n\nBest,\nAurix International", ['From: Aurix International <'.$admin.'>']);
    $sent ? wp_send_json_success(['message'=>'Sent.']) : wp_send_json_error('Mail failed.');
}
add_action('wp_ajax_aurix_rfq_submit',        'aurix_rfq_submit');
add_action('wp_ajax_nopriv_aurix_rfq_submit', 'aurix_rfq_submit');

/* ═══ FAQ SUBMIT ═══ */
function aurix_faq_submit() {
    check_ajax_referer('aurix_nonce','nonce');
    $name    = sanitize_text_field($_POST['faq_name']     ?? '');
    $email   = sanitize_email($_POST['faq_email']          ?? '');
    $product = sanitize_text_field($_POST['faq_product']   ?? '');
    $url     = esc_url_raw($_POST['faq_product_url']       ?? '');
    $q       = sanitize_textarea_field($_POST['faq_question'] ?? '');
    if ( ! $name || ! is_email($email) || ! $q ) { wp_send_json_error('Missing fields'); return; }
    $admin = get_option('admin_email');
    $subj  = 'Product FAQ: ' . $product;
    $body  = "Product: $product\nURL: $url\n\nFrom: $name ($email)\n\nQuestion:\n$q";
    $hdrs  = ['Content-Type: text/plain; charset=UTF-8','Reply-To: '.$name.' <'.$email.'>'];
    wp_mail($admin, $subj, $body, $hdrs);
    wp_mail($email, 'We received your question — Aurix International', "Dear $name,\n\nThank you for your question about $product.\nOur team will respond within 24 hours.\n\nBest regards,\nAurix International", ['From: Aurix International <'.$admin.'>']);
    wp_send_json_success(['message'=>'Question sent.']);
}
add_action('wp_ajax_aurix_faq_submit',        'aurix_faq_submit');
add_action('wp_ajax_nopriv_aurix_faq_submit', 'aurix_faq_submit');

/* ═══════════════════════════════════════════════════
   DISTRIBUTOR COMMUNITY — Signup AJAX (page-distributors.php)
═══════════════════════════════════════════════════ */
function aurix_distributor_signup() {
    check_ajax_referer('adc_distributor_signup', 'nonce');

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if ( ! is_email($email) ) {
        wp_send_json_error(['message' => 'Please enter a valid email address.']);
    }

    $admin = get_option('admin_email');
    $date  = current_time('mysql');

    // Notify the admin
    wp_mail(
        $admin,
        'New Distributor Community Signup',
        "A new distributor joined the Aurix Distributor Community.\n\nEmail: $email\nDate: $date",
        ['Content-Type: text/plain; charset=UTF-8']
    );

    // Confirm to the subscriber
    wp_mail(
        $email,
        'Welcome to the Aurix Distributor Community',
        "Thank you for joining the Aurix International Distributor Community.\n\nOur team will be in touch shortly with exclusive distributor resources, early access to new product lines, and priority support.\n\nBest regards,\nAurix International",
        ['From: Aurix International <' . $admin . '>']
    );

    wp_send_json_success(['message' => 'Welcome to the network!']);
}
add_action('wp_ajax_adc_distributor_signup',        'aurix_distributor_signup');
add_action('wp_ajax_nopriv_adc_distributor_signup', 'aurix_distributor_signup');

/* ═══════════════════════════════════════════════════
   SHOP PAGE — Assets + Quick View AJAX
═══════════════════════════════════════════════════ */

/* Enqueue shop CSS/JS on archive/shop pages */
add_action('wp_enqueue_scripts', function() {
    // Product archives only -- blog category/tag/author archives use css/blog.css.
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_post_type_archive('product') && ! is_tax( get_object_taxonomies('product') ) ) return;
    wp_enqueue_style(
        'aurix-shop',
        get_template_directory_uri() . '/css/shop.css',
        ['aurix-style'],
        '36.93.0'
    );
    wp_enqueue_script(
        'aurix-shop',
        get_template_directory_uri() . '/js/shop.js',
        ['jquery', 'aurix-main'],
        '36.93.0',
        true
    );
    // Pass wishlist data to JS
    wp_localize_script('aurix-shop', 'aurixData', [
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('aurix_nonce'),
        'wishlist' => array_map('intval', aurix_get_wishlist()),
        'cartUrl'  => wc_get_cart_url(),
    ]);
});

/* Remove default WC loop hooks we replace with our template */
remove_action('woocommerce_after_shop_loop_item',       'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating',      5);
remove_action('woocommerce_before_shop_loop_item',      'woocommerce_template_loop_product_link_open', 10);
remove_action('woocommerce_after_shop_loop_item',       'woocommerce_template_loop_product_link_close', 5);
remove_action('woocommerce_shop_loop_item_title',       'woocommerce_template_loop_product_title', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
remove_action('woocommerce_before_shop_loop_item_title','woocommerce_template_loop_product_thumbnail', 10);

/* Quick View AJAX handler */
function aurix_quick_view() {
    check_ajax_referer('aurix_nonce', 'nonce');
    $product_id = intval($_POST['product_id'] ?? 0);
    if ( ! $product_id ) { wp_send_json_error('No product ID'); return; }

    $product = wc_get_product($product_id);
    if ( ! $product ) { wp_send_json_error('Product not found'); return; }

    $cats = [];
    $terms = get_the_terms($product_id, 'product_cat');
    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $t) $cats[] = $t->name;
    }

    // Build gallery images array
    $thumb_id    = $product->get_image_id();
    $gallery_ids = $product->get_gallery_image_ids();
    $all_img_ids = $thumb_id ? array_merge([$thumb_id], (array)$gallery_ids) : (array)$gallery_ids;
    $gallery_imgs = [];
    foreach ( $all_img_ids as $img_id ) {
        $large = wp_get_attachment_image_url($img_id, 'woocommerce_large');
        $full  = wp_get_attachment_image_url($img_id, 'full');
        $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
        if ($large) {
            $gallery_imgs[] = ['large' => $large, 'full' => $full, 'thumb' => $thumb];
        }
    }

    wp_send_json_success([
        'id'         => $product->get_id(),
        'title'      => $product->get_name(),
        'sku'        => $product->get_sku(),
        'price_html' => $product->get_price_html(),
        'short_desc' => wp_kses_post($product->get_short_description()),
        'category'   => implode(', ', $cats),
        'url'        => get_permalink($product_id),
        'in_stock'   => $product->is_in_stock(),
        'image'      => $gallery_imgs ? $gallery_imgs[0]['large'] : '',
        'gallery'    => $gallery_imgs,
    ]);
}
add_action('wp_ajax_aurix_quick_view',        'aurix_quick_view');
add_action('wp_ajax_nopriv_aurix_quick_view', 'aurix_quick_view');

/* ── Per-page selector via URL param ── */
add_filter('loop_shop_per_page', function($n) {
    if ( isset($_GET['per_page']) && in_array((int)$_GET['per_page'], [6,12,18,24]) ) {
        return (int)$_GET['per_page'];
    }
    return 12; // default 12
}, 20);

/* ── Remove WC default result-count and ordering on shop pages ── */
add_action('init', function() {
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
});

/* ── Shop page: remove default main padding ── */
add_action('wp_head', function() {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        echo '<style>#main.site-main{padding:0!important;margin:0!important;}
        .aurix-woo-wrap,.aurix-woo-inner{padding:0!important;margin:0!important;}
        </style>'; /* aurix-shop-page-full */
    }
}, 20);

/* ═══════════════════════════════════════════════════════════════
   AJAX ADD TO CART — Single product page
   Action: aurix_atc
   Returns: minicart HTML, count, subtotal, fragments
════════════════════════════════════════════════════════════════ */
function aurix_atc_handler() {
    // Verify nonce
    if ( ! check_ajax_referer( 'aurix_nonce', 'nonce', false ) ) {
        wp_send_json_error( ['msg' => 'Security check failed'] );
    }

    if ( ! function_exists('WC') || ! WC()->cart ) {
        wp_send_json_error( ['msg' => 'WooCommerce not available'] );
    }

    $product_id   = absint( wp_unslash( $_POST['add-to-cart'] ?? 0 ) );
    $quantity     = wc_stock_amount( wp_unslash( $_POST['quantity'] ?? 1 ) );
    $variation_id = absint( wp_unslash( $_POST['variation_id'] ?? 0 ) );
    $variation    = [];

    if ( $variation_id ) {
        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, 'attribute_' ) === 0 ) {
                $variation[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
            }
        }
    }

    // Add to cart
    $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

    if ( ! $added ) {
        wp_send_json_error( ['msg' => 'Could not add to cart'] );
    }

    // Recalculate and save session immediately
    WC()->cart->calculate_totals();
    WC()->cart->maybe_set_cart_cookies();

    // Force session save so minicart reads fresh data
    if ( WC()->session ) {
        WC()->session->save_data();
    }

    // Build minicart HTML — reads from the now-saved cart session
    ob_start();
    woocommerce_mini_cart();
    $minicart_html = ob_get_clean();

    // Fragments for WC native listeners (shop page)
    $count     = WC()->cart->get_cart_contents_count();
    $subtotal  = WC()->cart->get_cart_subtotal();
    $cart_hash = WC()->cart->get_cart_hash();

    ob_start();
    ?><span class="aurix-cart-count" data-count="<?php echo esc_attr($count); ?>"><?php echo esc_html($count); ?></span><?php
    $count_html = ob_get_clean();

    $fragments = apply_filters( 'woocommerce_add_to_cart_fragments', [
        'div.aurix-minicart-items' => '<div class="aurix-minicart-items">' . $minicart_html . '</div>',
        '.aurix-cart-count'        => $count_html,
    ] );

    wp_send_json_success( [
        'minicart'  => $minicart_html,
        'count'     => $count,
        'subtotal'  => $subtotal,
        'cart_hash' => $cart_hash,
        'fragments' => $fragments,
    ] );
}
add_action( 'wp_ajax_aurix_atc',        'aurix_atc_handler' );
add_action( 'wp_ajax_nopriv_aurix_atc', 'aurix_atc_handler' );

/* ═══════════════════════════════════════════════════
   KIT BUILDER SECTION — Shortcode + Waitlist AJAX
═══════════════════════════════════════════════════ */

// Shortcode: [aurix_kit_builder] — use anywhere on front page or any page
add_shortcode('aurix_kit_builder', function() {
    ob_start();
    get_template_part('template-parts/kit-builder');
    return ob_get_clean();
});

/* ═══ WAITLIST — AJAX Handler ═══ */
function aurix_waitlist_signup() {
    check_ajax_referer('aurix_waitlist', 'nonce');
    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email($email) ) {
        wp_send_json_error(['msg' => 'Invalid email']);
    }

    $site_name   = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    $list        = get_option('aurix_waitlist_emails', []);
    $already     = in_array($email, array_column($list, 'email'));

    if ( ! $already ) {
        $list[] = [
            'email'  => $email,
            'date'   => current_time('Y-m-d H:i:s'),
            'status' => 'active',
        ];
        update_option('aurix_waitlist_emails', $list);

        /* ── 1. Confirmation email to USER ── */
        $user_subject = "You're on the VIP Waitlist — " . $site_name;
        ob_start(); ?>
<!DOCTYPE html><html><body style="margin:0;padding:0;background:#f9f8f5;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f8f5;padding:40px 20px;">
<tr><td align="center">
  <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
    <tr><td style="background:#0d1b2a;padding:32px 40px;text-align:center;">
      <p style="font-family:Georgia,serif;font-size:26px;font-weight:700;color:#c9a84c;margin:0;"><?php echo esc_html($site_name); ?></p>
      <p style="font-size:11px;color:rgba(255,255,255,0.45);margin:6px 0 0;letter-spacing:1px;text-transform:uppercase;">VIP Waitlist Confirmation</p>
    </td></tr>
    <tr><td style="padding:40px 40px 32px;">
      <p style="font-size:22px;font-weight:700;color:#0d1b2a;margin:0 0 14px;">You're on the list! 🎉</p>
      <p style="font-size:15px;color:#64748b;line-height:1.8;margin:0 0 24px;">Thank you for joining the <strong style="color:#0d1b2a;">Aurix International VIP Waitlist</strong>. You'll be among the first to know when our Fall 2026 USA Collection is available — with exclusive early-access pricing.</p>
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#fdf8ed;border-radius:12px;border:1px solid #e8d5a3;margin-bottom:28px;">
        <tr><td style="padding:24px 28px;">
          <p style="font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#c9a84c;margin:0 0 14px;">What happens next</p>
          <p style="font-size:14px;color:#5a4a2a;margin:0 0 8px;">✦ &nbsp;Early-access pricing notification</p>
          <p style="font-size:14px;color:#5a4a2a;margin:0 0 8px;">✦ &nbsp;Priority order processing</p>
          <p style="font-size:14px;color:#5a4a2a;margin:0;">✦ &nbsp;Exclusive kit builder preview access</p>
        </td></tr>
      </table>
      <p style="font-size:13px;color:#94a3b8;margin:0;">Questions? Contact us at <a href="mailto:<?php echo esc_attr($admin_email); ?>" style="color:#c9a84c;text-decoration:none;"><?php echo esc_html($admin_email); ?></a></p>
    </td></tr>
    <tr><td style="background:#f8fafc;border-top:1px solid #e8edf5;padding:18px 40px;text-align:center;">
      <p style="font-size:12px;color:#94a3b8;margin:0;">&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?> &middot; USA-Based &middot; Worldwide Distribution</p>
    </td></tr>
  </table>
</td></tr>
</table>
</body></html>
<?php
        $user_html = ob_get_clean();
        wp_mail($email, $user_subject, $user_html, [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>',
            'Reply-To: ' . $admin_email,
        ]);

        /* ── 2. Notification email to ADMIN ── */
        $admin_subject = 'New VIP Waitlist Signup — ' . $email;
        ob_start(); ?>
<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f1f5f9;padding:30px;">
<table width="520" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;margin:0 auto;">
  <tr><td style="background:#0d1b2a;padding:22px 30px;">
    <p style="font-size:16px;font-weight:700;color:#c9a84c;margin:0;"><?php echo esc_html($site_name); ?> &mdash; New Waitlist Signup</p>
  </td></tr>
  <tr><td style="padding:28px 30px;">
    <p style="font-size:14px;color:#334155;margin:0 0 16px;">A new user joined the VIP Waitlist:</p>
    <table cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;width:100%;">
      <tr><td style="padding:16px 20px;">
        <p style="font-size:13px;color:#64748b;margin:0 0 4px;">Email Address</p>
        <p style="font-size:16px;font-weight:700;color:#0d1b2a;margin:0;"><?php echo esc_html($email); ?></p>
      </td></tr>
      <tr><td style="padding:0 20px 16px;">
        <p style="font-size:13px;color:#64748b;margin:0 0 4px;">Date &amp; Time</p>
        <p style="font-size:14px;color:#0d1b2a;margin:0;"><?php echo current_time('d M Y, H:i'); ?></p>
      </td></tr>
    </table>
    <p style="margin:18px 0 6px;font-size:13px;color:#94a3b8;">Total signups: <strong><?php echo count($list); ?></strong></p>
    <a href="<?php echo esc_url(admin_url('admin.php?page=aurix-waitlist')); ?>" style="display:inline-block;margin-top:10px;background:#0d1b2a;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">View All Emails &rarr;</a>
  </td></tr>
</table>
</body></html>
<?php
        $admin_html = ob_get_clean();
        wp_mail($admin_email, $admin_subject, $admin_html, [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>',
        ]);
    }

    wp_send_json_success(['msg' => $already ? 'Already registered' : 'Added to waitlist']);
}
add_action('wp_ajax_aurix_waitlist_signup',        'aurix_waitlist_signup');
add_action('wp_ajax_nopriv_aurix_waitlist_signup', 'aurix_waitlist_signup');

/* ═══ WAITLIST — Admin Menu Page ═══ */
add_action('admin_menu', function() {
    add_menu_page(
        'VIP Waitlist',
        'VIP Waitlist',
        'manage_options',
        'aurix-waitlist',
        'aurix_waitlist_admin_page',
        'dashicons-email-alt2',
        56
    );
});

function aurix_waitlist_admin_page() {

    /* Handle CSV export */
    if ( isset($_GET['export']) && $_GET['export'] === 'csv' && current_user_can('manage_options') ) {
        check_admin_referer('aurix_waitlist_export');
        $list = get_option('aurix_waitlist_emails', []);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="aurix-waitlist-' . date('Y-m-d') . '.csv"');
        echo "Email,Date,Status\n";
        foreach ($list as $entry) {
            if ( is_array($entry) ) {
                printf("%s,%s,%s\n", esc_html($entry['email']), esc_html($entry['date']), esc_html($entry['status']));
            } else {
                printf("%s,—,active\n", esc_html($entry));
            }
        }
        exit;
    }

    /* Handle delete */
    if ( isset($_POST['aurix_delete_email']) && current_user_can('manage_options') ) {
        check_admin_referer('aurix_delete_waitlist');
        $del  = sanitize_email($_POST['aurix_delete_email']);
        $list = get_option('aurix_waitlist_emails', []);
        $list = array_values(array_filter($list, function($e) use ($del) {
            return is_array($e) ? $e['email'] !== $del : $e !== $del;
        }));
        update_option('aurix_waitlist_emails', $list);
        echo '<div class="notice notice-success is-dismissible"><p>Email removed from waitlist.</p></div>';
    }

    $list        = get_option('aurix_waitlist_emails', []);
    $total       = count($list);
    $month       = date('Y-m');
    $this_month  = count(array_filter($list, function($e) use ($month) {
        return is_array($e) && strpos($e['date'], $month) === 0;
    }));
    $export_url  = wp_nonce_url(admin_url('admin.php?page=aurix-waitlist&export=csv'), 'aurix_waitlist_export');
    ?>
    <div class="wrap">
      <h1 style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <span style="font-size:26px;">📧</span>
        VIP Waitlist
        <span style="background:#0d1b2a;color:#c9a84c;font-size:13px;font-weight:700;padding:4px 14px;border-radius:20px;"><?php echo $total; ?> subscribers</span>
      </h1>

      <!-- Stats -->
      <div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px 28px;min-width:160px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
          <p style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:0 0 6px;">Total Signups</p>
          <p style="font-size:32px;font-weight:800;color:#0d1b2a;margin:0;font-family:Georgia,serif;"><?php echo $total; ?></p>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px 28px;min-width:160px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
          <p style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin:0 0 6px;">This Month</p>
          <p style="font-size:32px;font-weight:800;color:#c9a84c;margin:0;font-family:Georgia,serif;"><?php echo $this_month; ?></p>
        </div>
      </div>

      <!-- Action buttons -->
      <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <a href="<?php echo esc_url($export_url); ?>" class="button button-primary" style="background:#0d1b2a;border-color:#0d1b2a;color:#fff;font-weight:600;">
          ⬇ Export CSV
        </a>
      </div>

      <!-- Table or empty state -->
      <?php if ( empty($list) ) : ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:60px 30px;text-align:center;">
          <p style="font-size:40px;margin:0 0 12px;">📭</p>
          <p style="font-size:16px;font-weight:600;color:#334155;margin:0 0 8px;">No signups yet</p>
          <p style="font-size:13px;color:#94a3b8;margin:0;">Emails will appear here when users sign up via the Kit Builder section on your website.</p>
        </div>
      <?php else : ?>
        <table class="wp-list-table widefat fixed striped" style="border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
          <thead>
            <tr style="background:#0d1b2a;">
              <th style="color:#c9a84c;font-size:11px;letter-spacing:1px;text-transform:uppercase;padding:14px 16px;width:50px;">#</th>
              <th style="color:#c9a84c;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Email Address</th>
              <th style="color:#c9a84c;font-size:11px;letter-spacing:1px;text-transform:uppercase;width:180px;">Date Signed Up</th>
              <th style="color:#c9a84c;font-size:11px;letter-spacing:1px;text-transform:uppercase;width:100px;">Status</th>
              <th style="color:#c9a84c;font-size:11px;letter-spacing:1px;text-transform:uppercase;width:160px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( array_reverse($list, true) as $i => $entry ) :
                $em   = is_array($entry) ? $entry['email']  : $entry;
                $dt   = is_array($entry) ? $entry['date']   : '—';
                $stat = is_array($entry) ? $entry['status'] : 'active';
                $num  = $total - ( count($list) - 1 - $i );
            ?>
            <tr>
              <td style="color:#94a3b8;font-size:13px;padding:14px 16px;"><?php echo $num; ?></td>
              <td style="font-weight:600;font-size:14px;color:#0d1b2a;">
                <a href="mailto:<?php echo esc_attr($em); ?>" style="color:#0d1b2a;text-decoration:none;"><?php echo esc_html($em); ?></a>
              </td>
              <td style="color:#64748b;font-size:13px;"><?php echo esc_html($dt ?: '—'); ?></td>
              <td>
                <span style="background:#f0fff4;color:#276749;border:1px solid #c6f6d5;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;"><?php echo esc_html(ucfirst($stat)); ?></span>
              </td>
              <td>
                <form method="post" style="display:inline;" onsubmit="return confirm('Remove <?php echo esc_js($em); ?>?');">
                  <?php wp_nonce_field('aurix_delete_waitlist'); ?>
                  <input type="hidden" name="aurix_delete_email" value="<?php echo esc_attr($em); ?>">
                  <button type="submit" class="button button-small" style="color:#dc2626;border-color:#fecaca;background:#fff5f5;">✕ Remove</button>
                </form>
                <a href="mailto:<?php echo esc_attr($em); ?>" class="button button-small" style="margin-left:4px;">✉ Email</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p style="margin-top:10px;font-size:12px;color:#94a3b8;">Showing all <?php echo $total; ?> subscribers · Newest first</p>
      <?php endif; ?>
    </div>
    <?php
}


/* ═══════════════════════════════════════════════════════════
   OUT OF STOCK WAITLIST — AJAX + emails
═══════════════════════════════════════════════════════════ */
function aurix_oos_waitlist_signup() {
    check_ajax_referer('aurix_oos_waitlist', 'nonce');

    $email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $product_name = sanitize_text_field( wp_unslash( $_POST['product_name'] ?? '' ) );
    $product_id   = absint( $_POST['product_id'] ?? 0 );

    if ( ! is_email($email) ) {
        wp_send_json_error(['msg' => 'Invalid email']);
    }

    $site_name   = get_bloginfo('name');
    $admin_email = get_option('admin_email');
    $option_key  = 'aurix_oos_waitlist_' . $product_id;
    $list        = get_option($option_key, []);
    $already     = in_array($email, array_column($list, 'email'));

    if ( ! $already ) {
        $list[] = ['email' => $email, 'date' => current_time('Y-m-d H:i:s')];
        update_option($option_key, $list);

        /* ── Email to USER ── */
        ob_start(); ?>
<!DOCTYPE html><html><body style="margin:0;padding:0;background:#f9f8f5;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f9f8f5;padding:40px 20px;">
<tr><td align="center">
  <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;">
    <tr><td style="background:#0d1b2a;padding:28px 36px;text-align:center;">
      <p style="font-family:Georgia,serif;font-size:22px;font-weight:700;color:#c9a84c;margin:0;"><?php echo esc_html($site_name); ?></p>
      <p style="font-size:11px;color:rgba(255,255,255,.45);margin:5px 0 0;letter-spacing:1px;text-transform:uppercase;">Back-in-Stock Notification</p>
    </td></tr>
    <tr><td style="padding:36px 36px 28px;">
      <p style="font-size:20px;font-weight:700;color:#0d1b2a;margin:0 0 12px;">You're on the waitlist! 🔔</p>
      <p style="font-size:14px;color:#64748b;line-height:1.8;margin:0 0 20px;">We've added you to the back-in-stock notification list for:</p>
      <div style="background:#fdf8ed;border:1px solid #e8d5a3;border-radius:10px;padding:16px 20px;margin-bottom:22px;">
        <p style="font-size:15px;font-weight:700;color:#0d1b2a;margin:0;"><?php echo esc_html($product_name); ?></p>
        <p style="font-size:12px;color:#c9a84c;margin:4px 0 0;font-weight:600;">Aurix International</p>
      </div>
      <p style="font-size:14px;color:#64748b;line-height:1.8;margin:0 0 8px;">As soon as this product is back in stock, you'll be the first to know with a direct purchase link.</p>
      <p style="font-size:13px;color:#94a3b8;margin:0;">Questions? Contact us at <a href="mailto:<?php echo esc_attr($admin_email); ?>" style="color:#c9a84c;text-decoration:none;"><?php echo esc_html($admin_email); ?></a></p>
    </td></tr>
    <tr><td style="background:#f8fafc;border-top:1px solid #e8edf5;padding:16px 36px;text-align:center;">
      <p style="font-size:11px;color:#94a3b8;margin:0;">&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?> &middot; USA-Based &middot; Worldwide Distribution</p>
    </td></tr>
  </table>
</td></tr>
</table>
</body></html>
<?php
        $user_html = ob_get_clean();
        wp_mail($email, 'Back-in-Stock Waitlist — ' . $product_name . ' | ' . $site_name, $user_html, [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>',
        ]);

        /* ── Email to ADMIN ── */
        $product_url = get_permalink($product_id);
        ob_start(); ?>
<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f1f5f9;padding:30px;">
<table width="520" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;margin:0 auto;">
  <tr><td style="background:#0d1b2a;padding:20px 28px;">
    <p style="font-size:15px;font-weight:700;color:#c9a84c;margin:0;"><?php echo esc_html($site_name); ?> — OOS Waitlist Signup</p>
  </td></tr>
  <tr><td style="padding:24px 28px;">
    <p style="font-size:14px;color:#334155;margin:0 0 14px;">New back-in-stock waitlist signup:</p>
    <table cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;width:100%;">
      <tr><td style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
        <p style="font-size:12px;color:#64748b;margin:0 0 3px;">Product</p>
        <p style="font-size:15px;font-weight:700;color:#0d1b2a;margin:0;"><?php echo esc_html($product_name); ?></p>
      </td></tr>
      <tr><td style="padding:14px 18px;border-bottom:1px solid #e2e8f0;">
        <p style="font-size:12px;color:#64748b;margin:0 0 3px;">Customer Email</p>
        <p style="font-size:14px;font-weight:600;color:#0d1b2a;margin:0;"><?php echo esc_html($email); ?></p>
      </td></tr>
      <tr><td style="padding:14px 18px;">
        <p style="font-size:12px;color:#64748b;margin:0 0 3px;">Date</p>
        <p style="font-size:13px;color:#0d1b2a;margin:0;"><?php echo current_time('d M Y, H:i'); ?></p>
      </td></tr>
    </table>
    <p style="margin-top:16px;font-size:13px;color:#94a3b8;">Total waitlist for this product: <strong><?php echo count($list); ?></strong></p>
    <?php if ($product_url) : ?>
    <a href="<?php echo esc_url($product_url); ?>" style="display:inline-block;margin-top:12px;background:#0d1b2a;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;">View Product &rarr;</a>
    <?php endif; ?>
  </td></tr>
</table>
</body></html>
<?php
        $admin_html = ob_get_clean();
        wp_mail($admin_email, 'OOS Waitlist: ' . $product_name . ' — ' . $email, $admin_html, [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>',
        ]);
    }

    wp_send_json_success(['msg' => $already ? 'Already on waitlist' : 'Added to waitlist']);
}
add_action('wp_ajax_aurix_oos_waitlist',        'aurix_oos_waitlist_signup');
add_action('wp_ajax_nopriv_aurix_oos_waitlist', 'aurix_oos_waitlist_signup');

/* ═══════════════════════════════════════════════════════════
   PER-PRODUCT FAQs — WooCommerce metabox
═══════════════════════════════════════════════════════════ */
add_action('add_meta_boxes', function() {
    add_meta_box(
        'aurix_product_faqs',
        '❓ Product FAQs (shown in FAQs tab)',
        'aurix_product_faqs_metabox',
        'product',
        'normal',
        'high'
    );
});

function aurix_product_faqs_metabox($post) {
    $faqs = get_post_meta($post->ID, '_aurix_faqs', true);
    $faqs = $faqs ? json_decode($faqs, true) : [];
    wp_nonce_field('aurix_save_faqs', 'aurix_faqs_nonce');
    ?>
    <style>
    .aurix-faq-row{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;margin-bottom:10px;position:relative}
    .aurix-faq-row label{display:block;font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px}
    .aurix-faq-row input,.aurix-faq-row textarea{width:100%;border:1px solid #dde4ef;border-radius:6px;padding:8px 11px;font-size:13px;font-family:inherit;color:#0d1b2a;margin-bottom:8px}
    .aurix-faq-row textarea{min-height:70px;resize:vertical;margin-bottom:0}
    .aurix-faq-row input:focus,.aurix-faq-row textarea:focus{border-color:#c9a84c;outline:none;box-shadow:0 0 0 3px rgba(201,168,76,.12)}
    .aurix-faq-remove{position:absolute;top:10px;right:12px;background:#fff5f5;border:1px solid #fecaca;color:#dc2626;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:12px;font-weight:600}
    .aurix-faq-remove:hover{background:#dc2626;color:#fff;border-color:#dc2626}
    #aurix-add-faq{background:#0d1b2a;color:#c9a84c;border:none;border-radius:8px;padding:10px 18px;cursor:pointer;font-size:13px;font-weight:600;margin-top:6px;display:flex;align-items:center;gap:6px}
    #aurix-add-faq:hover{background:#c9a84c;color:#fff}
    .aurix-faq-hint{font-size:12px;color:#94a3b8;margin:6px 0 14px;line-height:1.5}
    </style>
    <p class="aurix-faq-hint">Add FAQs specific to <strong><?php echo esc_html(get_the_title($post->ID)); ?></strong>. These appear at the top of the FAQs tab. General FAQs are always shown below.</p>
    <div id="aurix-faqs-container">
    <?php foreach ($faqs as $i => $faq) : ?>
      <div class="aurix-faq-row">
        <button type="button" class="aurix-faq-remove" onclick="this.closest('.aurix-faq-row').remove()">✕ Remove</button>
        <label>Question</label>
        <input type="text" name="aurix_faq_q[]" value="<?php echo esc_attr($faq['q'] ?? ''); ?>" placeholder="e.g. What size cassette is included?"/>
        <label>Answer</label>
        <textarea name="aurix_faq_a[]" placeholder="Write the answer here. HTML allowed for links and bold text."><?php echo esc_textarea($faq['a'] ?? ''); ?></textarea>
      </div>
    <?php endforeach; ?>
    </div>
    <button type="button" id="aurix-add-faq" onclick="aurixAddFaq()">
      <span style="font-size:16px;">+</span> Add FAQ
    </button>
    <script>
    function aurixAddFaq() {
      var c = document.getElementById('aurix-faqs-container');
      var div = document.createElement('div');
      div.className = 'aurix-faq-row';
      div.innerHTML = '<button type="button" class="aurix-faq-remove" onclick="this.closest(\'.aurix-faq-row\').remove()">✕ Remove</button>'
        + '<label>Question</label>'
        + '<input type="text" name="aurix_faq_q[]" placeholder="e.g. What size cassette is included?"/>'
        + '<label>Answer</label>'
        + '<textarea name="aurix_faq_a[]" placeholder="Write the answer here. HTML allowed."></textarea>';
      c.appendChild(div);
      div.querySelector('input').focus();
    }
    </script>
    <?php
}

add_action('save_post_product', function($post_id) {
    if ( ! isset($_POST['aurix_faqs_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['aurix_faqs_nonce'], 'aurix_save_faqs') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;

    $questions = array_map('sanitize_text_field', wp_unslash($_POST['aurix_faq_q'] ?? []));
    $answers   = array_map('wp_kses_post',        wp_unslash($_POST['aurix_faq_a'] ?? []));
    $faqs      = [];

    foreach ($questions as $i => $q) {
        if ( ! empty(trim($q)) && ! empty(trim($answers[$i] ?? '')) ) {
            $faqs[] = ['q' => $q, 'a' => $answers[$i]];
        }
    }

    if ( empty($faqs) ) {
        delete_post_meta($post_id, '_aurix_faqs');
    } else {
        update_post_meta($post_id, '_aurix_faqs', wp_json_encode($faqs));
    }
});

/* ═══════════════════════════════════════════════════
   PROFILE PICTURE UPLOAD — My Account -> Account Details
   Lets a logged-in customer upload their own photo instead of relying on
   Gravatar. Stored as a real Media Library attachment (not just a raw file
   path), with the attachment ID saved to user meta. get_avatar() is
   filtered to prefer this custom photo everywhere avatars are shown
   sitewide (account hero, mobile sidebar, blog author boxes, etc.) --
   falls back to Gravatar/default automatically if none was uploaded.
═══════════════════════════════════════════════════ */

add_action('woocommerce_edit_account_form_start', function () {
    $user_id = get_current_user_id();
    $current = get_user_meta($user_id, '_aurix_avatar_id', true);
    $preview_url = $current ? wp_get_attachment_image_url($current, 'thumbnail') : '';
    ?>
    <div class="aurix-avatar-upload">
        <div class="aurix-avatar-upload__preview">
            <?php if ($preview_url) : ?>
                <img src="<?php echo esc_url($preview_url); ?>" alt="" id="aurixAvatarPreviewImg">
            <?php else : ?>
                <?php echo get_avatar($user_id, 88, '', '', array('class' => 'aurix-avatar-upload__gravatar')); ?>
            <?php endif; ?>
        </div>
        <div class="aurix-avatar-upload__controls">
            <label for="aurix_avatar_file" class="aurix-avatar-upload__btn">
                <i class="fas fa-camera"></i> <?php echo $current ? esc_html__('Change Photo', 'aurix') : esc_html__('Upload Photo', 'aurix'); ?>
            </label>
            <input type="file" name="aurix_avatar_file" id="aurix_avatar_file" accept="image/png,image/jpeg,image/webp" style="display:none;">
            <?php if ($current) : ?>
                <label class="aurix-avatar-upload__remove">
                    <input type="checkbox" name="aurix_avatar_remove" value="1"> <?php esc_html_e('Remove photo (use Gravatar instead)', 'aurix'); ?>
                </label>
            <?php endif; ?>
            <p class="aurix-avatar-upload__hint"><?php esc_html_e('JPG, PNG, or WEBP. Square images look best.', 'aurix'); ?></p>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('aurix_avatar_file');
        if (!input) return;
        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById('aurixAvatarPreviewImg');
                var wrap = document.querySelector('.aurix-avatar-upload__preview');
                if (img) {
                    img.src = e.target.result;
                } else if (wrap) {
                    wrap.innerHTML = '<img src="' + e.target.result + '" alt="" id="aurixAvatarPreviewImg">';
                }
            };
            reader.readAsDataURL(input.files[0]);
        });
    });
    </script>
    <?php
}, 5);

// The account edit form already renders with enctype="multipart/form-data"
// (WooCommerce core sets this by default specifically to support file
// uploads like this one), so no template override is needed for that part.

add_action('woocommerce_save_account_details', function ($user_id) {
    // Remove takes priority if both are somehow submitted at once.
    if ( ! empty($_POST['aurix_avatar_remove']) ) {
        $old = get_user_meta($user_id, '_aurix_avatar_id', true);
        if ($old) wp_delete_attachment($old, false); // false = send to Media Trash, not permanent delete
        delete_user_meta($user_id, '_aurix_avatar_id');
        return;
    }

    if ( empty($_FILES['aurix_avatar_file']['name']) ) return; // nothing uploaded

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    if ( ! in_array($_FILES['aurix_avatar_file']['type'], $allowed_types, true) ) {
        wc_add_notice(__('Profile photo must be a JPG, PNG, or WEBP file.', 'aurix'), 'error');
        return;
    }
    if ( $_FILES['aurix_avatar_file']['size'] > 3 * 1024 * 1024 ) { // 3MB
        wc_add_notice(__('Profile photo must be smaller than 3MB.', 'aurix'), 'error');
        return;
    }

    $attachment_id = media_handle_upload('aurix_avatar_file', 0);
    if ( is_wp_error($attachment_id) ) {
        wc_add_notice(__('Sorry, that photo could not be uploaded. Please try a different file.', 'aurix'), 'error');
        return;
    }

    // Clean up the previous photo (if any) so uploads don't pile up unused in the Media Library.
    $old = get_user_meta($user_id, '_aurix_avatar_id', true);
    if ($old && $old != $attachment_id) wp_delete_attachment($old, false);

    update_user_meta($user_id, '_aurix_avatar_id', $attachment_id);
});

// Use the custom uploaded photo wherever get_avatar() is called sitewide
// (account hero, mobile sidebar, blog author boxes, etc.) -- falls back to
// Gravatar/default automatically if the user never uploaded one.
add_filter('get_avatar_url', function ($url, $id_or_email, $args) {
    $user_id = false;
    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
        $user_id = (int) $id_or_email->user_id;
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
        if ($user) $user_id = $user->ID;
    }
    if (!$user_id) return $url;

    $attachment_id = get_user_meta($user_id, '_aurix_avatar_id', true);
    if (!$attachment_id) return $url;

    $size = isset($args['size']) ? (int) $args['size'] : 96;
    $img  = wp_get_attachment_image_src($attachment_id, array($size, $size));
    return $img ? $img[0] : $url;
}, 10, 3);

/* ================================================================
   AURIX AUTO-UPDATER
   Checks the latest GitHub release for a newer theme version and
   shows the native "Update now" button in Appearance → Themes and
   Dashboard → Updates.

   Each release (built by .github/workflows/release.yml) carries
   two assets: aurix-theme.zip and version.json.
================================================================ */

define('AURIX_VERSION_URL',  'https://github.com/zaheerabas/aurix-theme/releases/latest/download/version.json');
define('AURIX_DOWNLOAD_URL', 'https://github.com/zaheerabas/aurix-theme/releases/latest/download/aurix-theme.zip');
define('AURIX_DETAILS_URL',  'https://github.com/zaheerabas/aurix-theme');

/**
 * Folder name this theme is installed under (e.g. "aurix-theme" or "aurix-v36").
 * WordPress keys theme updates by folder name, so never hardcode it.
 */
function aurix_theme_slug() {
    return get_template();
}

/**
 * Fetch version.json from the latest GitHub release.
 * Cached for 12 hours; failures are cached for 1 hour.
 */
function aurix_get_remote_version() {
    $cache_key = 'aurix_remote_version';
    $cached    = get_transient($cache_key);
    if ($cached !== false) return $cached ?: false;

    $response = wp_remote_get(AURIX_VERSION_URL, [
        'timeout'    => 10,
        'user-agent' => 'AurixThemeUpdater/2.0 WordPress/' . get_bloginfo('version'),
    ]);

    $data = null;
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
    }

    if (empty($data['version'])) {
        set_transient($cache_key, 0, HOUR_IN_SECONDS);
        return false;
    }

    set_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
    return $data;
}

/**
 * Inject update info into the WordPress theme update transient.
 * This makes the native WP "Update" button appear on the Themes page.
 */
add_filter('pre_set_site_transient_update_themes', function($transient) {
    if (empty($transient->checked)) return $transient;

    $slug   = aurix_theme_slug();
    $remote = aurix_get_remote_version();
    if (!$remote) return $transient;

    $current_version = $transient->checked[$slug] ?? wp_get_theme($slug)->get('Version');

    $item = [
        'theme'        => $slug,
        'new_version'  => $remote['version'],
        'url'          => AURIX_DETAILS_URL,
        'package'      => $remote['download_url'] ?? AURIX_DOWNLOAD_URL,
        'requires'     => $remote['requires']     ?? '5.8',
        'requires_php' => $remote['requires_php'] ?? '7.4',
    ];

    if (version_compare($remote['version'], $current_version, '>')) {
        $transient->response[$slug] = $item;
    } else {
        unset($transient->response[$slug]);
        $transient->no_update[$slug] = $item;
    }

    return $transient;
});

/**
 * Show theme info (changelog, description) when user clicks "View version details".
 */
add_filter('themes_api', function($result, $action, $args) {
    if ($action !== 'theme_information') return $result;
    if (($args->slug ?? '') !== aurix_theme_slug()) return $result;

    $remote = aurix_get_remote_version();
    if (!$remote) return $result;

    return (object) [
        'name'          => $remote['name']         ?? 'Aurix International',
        'slug'          => aurix_theme_slug(),
        'version'       => $remote['version'],
        'author'        => $remote['author']       ?? 'ZaheerAbbas',
        'homepage'      => AURIX_DETAILS_URL,
        'download_link' => $remote['download_url'] ?? AURIX_DOWNLOAD_URL,
        'last_updated'  => $remote['last_updated'] ?? '',
        'requires'      => $remote['requires']     ?? '5.8',
        'requires_php'  => $remote['requires_php'] ?? '7.4',
        'sections'      => [
            'description' => $remote['description'] ?? 'Aurix International custom WooCommerce theme.',
            'changelog'   => nl2br(esc_html($remote['changelog'] ?? 'See GitHub for changelog.')),
        ],
    ];
}, 20, 3);

/**
 * The release zip contains an "aurix-theme/" folder. If this site has the
 * theme installed under another folder name (e.g. "aurix-v36"), rename the
 * extracted folder so the update replaces the installed theme in place.
 */
add_filter('upgrader_source_selection', function($source, $remote_source, $upgrader, $hook_extra = []) {
    global $wp_filesystem;

    $slug = aurix_theme_slug();
    if (($hook_extra['theme'] ?? '') !== $slug) return $source;

    $desired = trailingslashit($remote_source) . $slug;
    if (untrailingslashit($source) === $desired) return $source;

    if ($wp_filesystem && $wp_filesystem->move(untrailingslashit($source), $desired, true)) {
        return trailingslashit($desired);
    }

    return new WP_Error('aurix_rename_failed', __('Could not prepare the Aurix theme update folder.', 'aurix'));
}, 10, 4);

/**
 * Clear the cached release info after an update, and when the admin
 * clicks "Check again" on Dashboard → Updates.
 */
add_action('upgrader_process_complete', function($upgrader, $options) {
    if (($options['type'] ?? '') === 'theme') {
        delete_transient('aurix_remote_version');
    }
}, 10, 2);

add_action('load-update-core.php', function() {
    if (!empty($_GET['force-check'])) {
        delete_transient('aurix_remote_version');
    }
});

/**
 * Admin notice on Themes page showing current version + GitHub link.
 */
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'themes') return;
    $v = wp_get_theme(aurix_theme_slug())->get('Version');
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong>Aurix International Theme</strong> — version <?php echo esc_html($v); ?> &nbsp;|&nbsp;
            <a href="<?php echo esc_url(AURIX_DETAILS_URL . '/releases'); ?>" target="_blank" rel="noopener">View releases on GitHub</a> &nbsp;|&nbsp;
            <a href="<?php echo esc_url(admin_url('update-core.php?force-check=1')); ?>">Check for updates</a>
        </p>
    </div>
    <?php
});
