<?php
/**
 * Aurix International — functions.php v3.0
 * Author: ZaheerAbbas
 */
if ( ! defined('ABSPATH') ) exit;

require_once get_template_directory() . '/inc/ajax-handlers.php';

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
    register_nav_menus(['primary'=>__('Primary Nav','aurix'),'footer'=>__('Footer Menu','aurix')]);
}
add_action('after_setup_theme', 'aurix_setup');

/* ═══ 2. FAVICON ═══ */
add_action('wp_head', function() {
    $favicon = get_template_directory_uri() . '/favicon.ico';
    echo '<link rel="icon" href="' . esc_url($favicon) . '" type="image/x-icon">' . "\n";
    echo '<link rel="shortcut icon" href="' . esc_url($favicon) . '" type="image/x-icon">' . "\n";
    echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">' . "\n";
}, 1);

/* ═══ 3. ENQUEUE ═══ */
function aurix_assets() {
    wp_enqueue_style('aurix-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Outfit:wght@300;400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap',
        [], null);
    wp_enqueue_style('aurix-style', get_stylesheet_uri(), ['aurix-fonts'], '15.0.0');
    wp_enqueue_script('aurix-main', get_template_directory_uri().'/js/aurix-main.js', ['jquery'], '15.0.0', true);

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
    <aside id="aurix-cart-panel" class="aurix-cart-panel" aria-hidden="true" role="dialog">
      <div class="acp-header">
        <div class="acp-title"><i class="fas fa-shopping-cart"></i><span><?php esc_html_e('Shopping Cart','aurix'); ?></span></div>
        <button class="acp-close" id="cartPanelClose"><i class="fas fa-times"></i></button>
      </div>
      <div class="acp-body">
        <div class="aurix-minicart-items"><?php woocommerce_mini_cart(); ?></div>
      </div>
      <div class="acp-footer">
        <div class="acp-subtotal">
          <span><?php esc_html_e('Subtotal','aurix'); ?></span>
          <span class="acp-total-amt"><?php if(function_exists('WC')&&WC()->cart) echo WC()->cart->get_cart_subtotal(); ?></span>
        </div>
        <p class="acp-note"><i class="fas fa-info-circle"></i> <?php esc_html_e('Final price and discounts will be determined at time of payment processing.','aurix'); ?></p>
        <div class="acp-actions">
          <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="acp-btn acp-btn--outline"><i class="fas fa-arrow-left"></i> <?php esc_html_e('Continue','aurix'); ?></a>
          <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="acp-btn acp-btn--solid"><?php esc_html_e('Checkout','aurix'); ?> <i class="fas fa-arrow-right"></i></a>
        </div>
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
    $wp_customize->add_section('aurix_contact',['title'=>__('Aurix — Contact','aurix'),'priority'=>30]);
    $wp_customize->add_setting('aurix_phone',['default'=>'+1 (234) 567-8900','sanitize_callback'=>'sanitize_text_field']);
    $wp_customize->add_control('aurix_phone',['label'=>__('Phone','aurix'),'section'=>'aurix_contact','type'=>'text']);
    $wp_customize->add_setting('aurix_email',['default'=>'info@aurixinternational.com','sanitize_callback'=>'sanitize_email']);
    $wp_customize->add_control('aurix_email',['label'=>__('Email','aurix'),'section'=>'aurix_contact','type'=>'email']);
    $wp_customize->add_setting('aurix_topbar_text',['default'=>'Pre-assembled Surgical &amp; Dental Kits now available','sanitize_callback'=>'wp_kses_post']);
    $wp_customize->add_control('aurix_topbar_text',['label'=>__('Top Bar Text','aurix'),'section'=>'aurix_contact','type'=>'textarea']);
});

add_filter('excerpt_length', function(){ return 25; });

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
    wp_enqueue_style('aurix-product', get_template_directory_uri().'/css/product.css', ['aurix-style'], '15.0.0');
    // QR code library
    wp_enqueue_script('qrcode-js', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', [], '1.0.0', true);
    wp_enqueue_script('aurix-product', get_template_directory_uri().'/js/product.js', ['jquery','aurix-main','qrcode-js'], '15.0.0', true);
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
   SHOP PAGE — Assets + Quick View AJAX
═══════════════════════════════════════════════════ */

/* Enqueue shop CSS/JS on archive/shop pages */
add_action('wp_enqueue_scripts', function() {
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_archive() ) return;
    wp_enqueue_style(
        'aurix-shop',
        get_template_directory_uri() . '/css/shop.css',
        ['aurix-style'],
        '1.0.0'
    );
    wp_enqueue_script(
        'aurix-shop',
        get_template_directory_uri() . '/js/shop.js',
        ['jquery', 'aurix-main'],
        '1.0.0',
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
    if ( isset($_GET['per_page']) && in_array((int)$_GET['per_page'], [4,8,12,16]) ) {
        return (int)$_GET['per_page'];
    }
    return 8; // default 8
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

/* ================================================================
   AURIX AUTO-UPDATER
   Checks GitHub for new theme versions and shows update button
   in WP Admin → Appearance → Themes.
   
   GitHub repo: https://github.com/zaheerabas/aurix-theme
================================================================ */

define('AURIX_THEME_SLUG',    'aurix-v17');
define('AURIX_VERSION_URL',   'https://raw.githubusercontent.com/zaheerabas/aurix-theme/main/version.json');
define('AURIX_DOWNLOAD_URL',  'https://github.com/zaheerabas/aurix-theme/releases/latest/download/aurix-theme.zip');
define('AURIX_DETAILS_URL',   'https://github.com/zaheerabas/aurix-theme');

/**
 * Check GitHub for a newer version.
 * Cached for 12 hours to avoid hammering the API.
 */
function aurix_get_remote_version() {
    $cache_key = 'aurix_remote_version';
    $cached    = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $response = wp_remote_get(AURIX_VERSION_URL, [
        'timeout'    => 10,
        'user-agent' => 'AurixThemeUpdater/1.0 WordPress/' . get_bloginfo('version'),
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_transient($cache_key, false, HOUR_IN_SECONDS); // cache failure for 1h
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($data['version'])) {
        set_transient($cache_key, false, HOUR_IN_SECONDS);
        return false;
    }

    set_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
    return $data;
}

/**
 * Inject update info into WordPress theme update transient.
 * This makes the native WP "Update" button appear on the Themes page.
 */
add_filter('pre_set_site_transient_update_themes', function($transient) {
    if (empty($transient->checked)) return $transient;

    $remote = aurix_get_remote_version();
    if (!$remote) return $transient;

    $current_version = wp_get_theme()->get('Version');

    if (version_compare($remote['version'], $current_version, '>')) {
        $transient->response[AURIX_THEME_SLUG] = [
            'theme'       => AURIX_THEME_SLUG,
            'new_version' => $remote['version'],
            'url'         => AURIX_DETAILS_URL,
            'package'     => isset($remote['download_url']) ? $remote['download_url'] : AURIX_DOWNLOAD_URL,
            'requires'    => $remote['requires']     ?? '5.8',
            'requires_php'=> $remote['requires_php'] ?? '7.4',
        ];
    }

    return $transient;
});

/**
 * Show theme info (changelog, description) when user clicks "View version details".
 */
add_filter('themes_api', function($result, $action, $args) {
    if ($action !== 'theme_information') return $result;
    if (($args->slug ?? '') !== AURIX_THEME_SLUG) return $result;

    $remote = aurix_get_remote_version();
    if (!$remote) return $result;

    return (object) [
        'name'          => $remote['name']        ?? 'Aurix International',
        'slug'          => AURIX_THEME_SLUG,
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
 * Clear update cache when WordPress checks for updates manually.
 */
add_action('upgrader_process_complete', function($upgrader, $options) {
    if (($options['type'] ?? '') === 'theme') {
        delete_transient('aurix_remote_version');
    }
}, 10, 2);

/**
 * Admin notice on Themes page showing current version + GitHub link.
 */
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'themes') return;
    $v = wp_get_theme()->get('Version');
    ?>
    <div class="notice notice-info is-dismissible" style="display:flex;align-items:center;gap:16px;padding:12px 16px;">
        <span style="font-size:1.2rem;">🎨</span>
        <div>
            <strong>Aurix International Theme</strong> — version <?php echo esc_html($v); ?> &nbsp;|&nbsp;
            <a href="<?php echo esc_url(AURIX_DETAILS_URL); ?>" target="_blank" rel="noopener">
                View on GitHub
            </a> &nbsp;|&nbsp;
            <a href="<?php echo esc_url(admin_url('update-core.php')); ?>">
                Check for updates
            </a>
        </div>
    </div>
    <?php
});
