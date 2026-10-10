<?php
/**
 * Aurix — Mini-cart sidebar (v36.4)
 */
defined('ABSPATH') || exit;
do_action('woocommerce_before_mini_cart');
?>

<?php if ( ! WC()->cart->is_empty() ) : ?>

<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr($args['list_classes']); ?>">
  <?php
  do_action('woocommerce_before_mini_cart_contents');
  foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
    $_product        = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
    $product_id      = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
    if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;
    if ( ! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key) ) continue;

    $product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
    $thumbnail         = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key);
    $product_price     = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
    $qty               = (int) $cart_item['quantity'];
    $max_qty           = $_product->get_max_purchase_quantity();
  ?>
  <li class="woocommerce-mini-cart-item mini_cart_item acp-cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">

    <!-- Remove -->
    <button type="button"
            class="acp-remove-btn"
            data-key="<?php echo esc_attr($cart_item_key); ?>"
            aria-label="Remove <?php echo esc_attr($product_name); ?>">
      <i class="fas fa-times"></i>
    </button>

    <!-- Image -->
    <div class="acp-item-img">
      <?php if ($product_permalink) : ?>
        <a href="<?php echo esc_url($product_permalink); ?>" tabindex="-1"><?php echo $thumbnail; ?></a>
      <?php else : echo $thumbnail; endif; ?>
    </div>

    <!-- Details -->
    <div class="acp-item-details">
      <div class="acp-item-name">
        <?php if ($product_permalink) : ?>
          <a href="<?php echo esc_url($product_permalink); ?>"><?php echo wp_kses_post($product_name); ?></a>
        <?php else : echo wp_kses_post($product_name); endif; ?>
      </div>

      <?php if ($_product->get_sku()) : ?>
        <span class="acp-item-sku"><?php echo esc_html($_product->get_sku()); ?></span>
      <?php endif; ?>

      <!-- Qty controls + line price -->
      <div class="acp-item-bottom">
        <div class="acp-qty-ctrl" data-key="<?php echo esc_attr($cart_item_key); ?>"
             data-max="<?php echo esc_attr($max_qty > 0 ? $max_qty : 99); ?>">
          <button type="button" class="acp-qty-btn acp-qty-minus" aria-label="Decrease">−</button>
          <span class="acp-qty-num"><?php echo esc_html($qty); ?></span>
          <button type="button" class="acp-qty-btn acp-qty-plus" aria-label="Increase">+</button>
        </div>
        <span class="acp-item-price"><?php echo $product_price; ?></span>
      </div>
    </div>

  </li>
  <?php endforeach; ?>
  <?php do_action('woocommerce_mini_cart_contents'); ?>
</ul>

<?php else : ?>

<div class="acp-empty-cart">
  <div class="acp-empty-icon"><i class="fas fa-shopping-bag"></i></div>
  <p class="acp-empty-title">Your cart is empty</p>
  <p class="acp-empty-sub">Add some products to get started!</p>
  <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="acp-empty-btn">
    <i class="fas fa-store"></i> Browse Products
  </a>
</div>

<?php endif; ?>
<?php do_action('woocommerce_after_mini_cart'); ?>
