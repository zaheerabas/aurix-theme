<?php
/**
 * Aurix International — My Account page template v5.0
 * Author: ZaheerAbbas
 * Replaces woocommerce/myaccount/my-account.php
 */
defined('ABSPATH') || exit;

do_action('woocommerce_before_my_account');
?>

<?php if ( is_user_logged_in() ) :
    $user         = wp_get_current_user();
    $initial      = strtoupper(substr($user->display_name, 0, 1));
    $orders       = wc_get_orders(['customer'=>get_current_user_id(),'limit'=>-1,'return'=>'ids','status'=>['processing','completed','on-hold','pending']]);
    $order_count  = count($orders);
    $wl_count     = function_exists('aurix_get_wishlist') ? count(aurix_get_wishlist()) : 0;
?>

<!-- ═══ HERO BANNER ═══ -->
<div class="aurix-account-hero">
  <div class="aurix-account-hero__avatar"><?php echo esc_html($initial); ?></div>
  <div class="aurix-account-hero__info">
    <h3><?php echo esc_html($user->display_name); ?></h3>
    <p><i class="fas fa-envelope" style="margin-right:5px;font-size:.72rem;"></i><?php echo esc_html($user->user_email); ?></p>
  </div>
  <div class="aurix-account-hero__stats">
    <div class="ahero-stat">
      <span class="ahero-stat__num"><?php echo esc_html($order_count); ?></span>
      <span class="ahero-stat__lbl">Orders</span>
    </div>
    <div class="ahero-stat">
      <span class="ahero-stat__num"><?php echo esc_html($wl_count); ?></span>
      <span class="ahero-stat__lbl">Wishlist</span>
    </div>
  </div>
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="aurix-account-hero__badge">
    <i class="fas fa-pencil-alt"></i> Edit Profile
  </a>
</div>

<!-- ═══ MAIN GRID: sidebar nav + content ═══ -->
<div class="aurix-account-inner">

  <!-- Sidebar nav -->
  <?php do_action('woocommerce_account_navigation'); ?>

  <!-- Content -->
  <div class="woocommerce-MyAccount-content">
    <?php do_action('woocommerce_account_content'); ?>
  </div>

</div>

<?php else : ?>
<!-- Not logged in — WC handles login/register form via form-login.php -->
<?php do_action('woocommerce_account_navigation'); ?>
<div class="woocommerce-MyAccount-content">
  <?php do_action('woocommerce_account_content'); ?>
</div>
<?php endif; ?>

<?php do_action('woocommerce_after_my_account'); ?>
