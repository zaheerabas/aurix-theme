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
    $orders       = wc_get_orders(['customer'=>get_current_user_id(),'limit'=>-1,'return'=>'ids','status'=>['processing','completed','on-hold','pending']]);
    $order_count  = count($orders);
    $wl_count     = function_exists('aurix_get_wishlist') ? count(aurix_get_wishlist()) : 0;
    $member_since = date_i18n('Y', strtotime($user->user_registered));
?>

<!-- ═══ HERO BANNER — Instagram-style profile header ═══ -->
<div class="aurix-account-hero aurix-account-hero--ig">
  <div class="aig-top">
    <div class="aig-avatar"><?php echo get_avatar($user->ID, 96); ?></div>
    <div class="aig-top-right">
      <div class="aig-name-row">
        <h3><?php echo esc_html($user->display_name); ?></h3>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="aig-edit-btn">
          <?php esc_html_e('Edit Profile', 'aurix'); ?>
        </a>
      </div>
      <div class="aig-stats">
        <div class="aig-stat"><span class="aig-stat__num"><?php echo esc_html($order_count); ?></span> <span class="aig-stat__lbl">Orders</span></div>
        <div class="aig-stat"><span class="aig-stat__num"><?php echo esc_html($wl_count); ?></span> <span class="aig-stat__lbl">Wishlist</span></div>
        <div class="aig-stat"><span class="aig-stat__num"><?php echo esc_html($member_since); ?></span> <span class="aig-stat__lbl">Member Since</span></div>
      </div>
      <p class="aig-bio"><i class="fas fa-envelope"></i> <?php echo esc_html($user->user_email); ?></p>
    </div>
  </div>
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
