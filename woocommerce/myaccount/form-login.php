<?php
/**
 * Aurix — Custom My Account Login/Register page
 * Replaces woocommerce/myaccount/form-login.php
 */
defined('ABSPATH') || exit;

// If already logged in, redirect
if ( is_user_logged_in() ) {
    wc_get_template('myaccount/dashboard.php');
    return;
}

do_action('woocommerce_before_customer_login_form');
?>

<div class="aurix-auth-wrap">
  <!-- Tabs -->
  <div class="aurix-auth-tabs">
    <button class="aurix-auth-tab active" data-target="aurix-login-panel">
      <i class="fas fa-sign-in-alt"></i> <?php esc_html_e('Sign In','aurix'); ?>
    </button>
    <button class="aurix-auth-tab" data-target="aurix-register-panel">
      <i class="fas fa-user-plus"></i> <?php esc_html_e('Create Account','aurix'); ?>
    </button>
  </div>

  <!-- Login Panel -->
  <div id="aurix-login-panel" class="aurix-auth-panel active">
    <div class="aurix-auth-card">
      <div class="aurix-auth-card__header">
        <div class="aurix-auth-card__icon"><i class="fas fa-lock"></i></div>
        <div>
          <h2><?php esc_html_e('Welcome Back','aurix'); ?></h2>
          <p><?php esc_html_e('Sign in to your Aurix account','aurix'); ?></p>
        </div>
      </div>

      <form class="woocommerce-form woocommerce-form-login" method="post">
        <?php do_action('woocommerce_login_form_start'); ?>

        <div class="aurix-form-field">
          <label for="username"><?php esc_html_e('Username or email address','aurix'); ?> <span class="req">*</span></label>
          <div class="aurix-input-wrap">
            <i class="fas fa-user"></i>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="username" id="username" autocomplete="username"
                   value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
                   placeholder="<?php esc_attr_e('Enter your email or username','aurix'); ?>" required />
          </div>
        </div>

        <div class="aurix-form-field">
          <label for="password"><?php esc_html_e('Password','aurix'); ?> <span class="req">*</span></label>
          <div class="aurix-input-wrap">
            <i class="fas fa-lock"></i>
            <input class="woocommerce-Input woocommerce-Input--text input-text"
                   type="password" name="password" id="password" autocomplete="current-password"
                   placeholder="<?php esc_attr_e('Enter your password','aurix'); ?>" required />
            <button type="button" class="aurix-pw-toggle" aria-label="Toggle password">
              <i class="fas fa-eye-slash"></i>
            </button>
          </div>
        </div>

        <div class="aurix-form-row">
          <label class="aurix-checkbox-label">
            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
            <?php esc_html_e('Remember me','aurix'); ?>
          </label>
          <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="aurix-lost-pw">
            <i class="fas fa-key"></i> <?php esc_html_e('Lost password?','aurix'); ?>
          </a>
        </div>

        <?php do_action('woocommerce_login_form'); ?>
        <?php wp_nonce_field('woocommerce-login','woocommerce-login-nonce'); ?>
        <input type="hidden" name="redirect" value="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" />

        <button type="submit" class="aurix-btn aurix-btn--primary" name="login" value="<?php esc_attr_e('Log in','aurix'); ?>">
          <i class="fas fa-sign-in-alt"></i> <?php esc_html_e('Sign In','aurix'); ?>
        </button>

        <p class="aurix-auth-switch">
          <?php esc_html_e("Don't have an account?",'aurix'); ?>
          <button type="button" class="aurix-link-btn" data-switch="aurix-register-panel">
            <?php esc_html_e('Create one now','aurix'); ?> <i class="fas fa-arrow-right"></i>
          </button>
        </p>

        <?php do_action('woocommerce_login_form_end'); ?>
      </form>
    </div>
  </div>

  <!-- Register Panel -->
  <div id="aurix-register-panel" class="aurix-auth-panel">
    <div class="aurix-auth-card">
      <div class="aurix-auth-card__header">
        <div class="aurix-auth-card__icon aurix-auth-card__icon--reg"><i class="fas fa-user-plus"></i></div>
        <div>
          <h2><?php esc_html_e('Create Account','aurix'); ?></h2>
          <p><?php esc_html_e('Join thousands of healthcare professionals','aurix'); ?></p>
        </div>
      </div>

      <form method="post" class="woocommerce-form woocommerce-form-register">
        <?php do_action('woocommerce_register_form_start'); ?>

        <?php if ( 'no' === get_option('woocommerce_registration_generate_username') ) : ?>
        <div class="aurix-form-field">
          <label for="reg_username"><?php esc_html_e('Username','aurix'); ?> <span class="req">*</span></label>
          <div class="aurix-input-wrap">
            <i class="fas fa-user"></i>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="username" id="reg_username" autocomplete="username"
                   value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
                   placeholder="<?php esc_attr_e('Choose a username','aurix'); ?>" />
          </div>
        </div>
        <?php endif; ?>

        <div class="aurix-form-field">
          <label for="reg_email"><?php esc_html_e('Email address','aurix'); ?> <span class="req">*</span></label>
          <div class="aurix-input-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="email" id="reg_email" autocomplete="email"
                   value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>"
                   placeholder="<?php esc_attr_e('your@email.com','aurix'); ?>" required />
          </div>
        </div>

        <?php if ( 'no' === get_option('woocommerce_registration_generate_password') ) : ?>
        <div class="aurix-form-field">
          <label for="reg_password"><?php esc_html_e('Password','aurix'); ?> <span class="req">*</span></label>
          <div class="aurix-input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="password" id="reg_password" autocomplete="new-password"
                   placeholder="<?php esc_attr_e('Create a strong password','aurix'); ?>" required />
            <button type="button" class="aurix-pw-toggle" aria-label="Toggle password">
              <i class="fas fa-eye-slash"></i>
            </button>
          </div>
        </div>
        <?php else : ?>
        <div class="aurix-info-box">
          <i class="fas fa-info-circle"></i>
          <?php esc_html_e('A password will be sent to your email address.','aurix'); ?>
        </div>
        <?php endif; ?>

        <!-- Benefits list -->
        <div class="aurix-benefits">
          <p><?php esc_html_e('As a member you get:','aurix'); ?></p>
          <ul>
            <li><i class="fas fa-check"></i> <?php esc_html_e('Full order history & tracking','aurix'); ?></li>
            <li><i class="fas fa-check"></i> <?php esc_html_e('Save addresses for faster checkout','aurix'); ?></li>
            <li><i class="fas fa-check"></i> <?php esc_html_e('Exclusive wholesale pricing','aurix'); ?></li>
            <li><i class="fas fa-check"></i> <?php esc_html_e('5-year quality guarantee on orders','aurix'); ?></li>
          </ul>
        </div>

        <?php do_action('woocommerce_register_form'); ?>
        <?php wp_nonce_field('woocommerce-register','woocommerce-register-nonce'); ?>

        <div class="aurix-privacy-note">
          <?php wc_get_template_part('global/form-login','privacy-policy-notice'); ?>
        </div>

        <button type="submit" class="aurix-btn aurix-btn--gold" name="register" value="<?php esc_attr_e('Register','aurix'); ?>">
          <i class="fas fa-user-plus"></i> <?php esc_html_e('Create My Account','aurix'); ?>
        </button>

        <p class="aurix-auth-switch">
          <?php esc_html_e('Already have an account?','aurix'); ?>
          <button type="button" class="aurix-link-btn" data-switch="aurix-login-panel">
            <?php esc_html_e('Sign in','aurix'); ?> <i class="fas fa-arrow-right"></i>
          </button>
        </p>

        <?php do_action('woocommerce_register_form_end'); ?>
      </form>
    </div>
  </div>
</div>

<style>
/* Auth page styles */
.aurix-auth-wrap { max-width:560px; margin:0 auto; padding:0 20px 40px; }

.aurix-auth-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:28px; }
.aurix-auth-tab { flex:1; padding:14px 20px; background:none; border:none; border-bottom:2.5px solid transparent;
  margin-bottom:-2px; font-family:'Outfit',sans-serif; font-size:.88rem; font-weight:600;
  color:var(--muted); letter-spacing:.05em; text-transform:uppercase; cursor:pointer;
  display:flex; align-items:center; justify-content:center; gap:8px;
  transition:color .2s, border-color .2s; }
.aurix-auth-tab i { font-size:.82rem; }
.aurix-auth-tab.active { color:var(--navy); border-bottom-color:var(--gold); }
.aurix-auth-tab:hover:not(.active) { color:var(--navy); }

.aurix-auth-panel { display:none; }
.aurix-auth-panel.active { display:block; }

.aurix-auth-card { background:#fff; border:1px solid var(--border); border-radius:var(--r-lg); padding:32px; box-shadow:var(--shadow-sm); }
.aurix-auth-card__header { display:flex; align-items:center; gap:16px; margin-bottom:28px; padding-bottom:20px; border-bottom:1px solid var(--border); }
.aurix-auth-card__icon { width:50px; height:50px; background:var(--navy); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gold); font-size:1.15rem; flex-shrink:0; }
.aurix-auth-card__icon--reg { background:var(--gold); color:#fff; }
.aurix-auth-card__header h2 { font-family:'Cormorant Garamond',serif; font-size:1.4rem; color:var(--navy); margin:0 0 3px; }
.aurix-auth-card__header p { font-size:.82rem; color:var(--muted); margin:0; }

.aurix-form-field { margin-bottom:18px; }
.aurix-form-field label { display:block; font-size:.76rem; font-weight:700; color:var(--navy); letter-spacing:.06em; text-transform:uppercase; margin-bottom:7px; }
.req { color:#e55; }
.aurix-input-wrap { position:relative; display:flex; align-items:center; }
.aurix-input-wrap > i:first-child { position:absolute; left:14px; color:var(--muted); font-size:.8rem; pointer-events:none; z-index:1; }
.aurix-input-wrap input { width:100%; height:48px; padding:0 46px 0 40px; border:1.5px solid #dde4ef; border-radius:var(--r); font-family:'Outfit',sans-serif; font-size:.9rem; color:var(--navy); background:#fff; outline:none; transition:border-color .2s, box-shadow .2s; }
.aurix-input-wrap input:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(184,146,90,.1); }
.aurix-pw-toggle { position:absolute; right:12px; background:none; border:none; color:var(--muted); font-size:.9rem; padding:4px; cursor:pointer; transition:color .2s; }
.aurix-pw-toggle:hover { color:var(--gold); }

.aurix-form-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
.aurix-checkbox-label { display:flex; align-items:center; gap:8px; font-size:.84rem; color:var(--muted); cursor:pointer; }
.aurix-lost-pw { font-size:.82rem; color:var(--gold); transition:color .15s; display:flex; align-items:center; gap:5px; }
.aurix-lost-pw:hover { color:var(--navy); }

.aurix-btn { width:100%; height:50px; border:none; border-radius:25px; font-family:'Outfit',sans-serif; font-size:.95rem; font-weight:600; letter-spacing:.05em; display:flex; align-items:center; justify-content:center; gap:9px; cursor:pointer; margin-bottom:18px; transition:background .2s, transform .15s; }
.aurix-btn:hover { transform:translateY(-1px); }
.aurix-btn--primary { background:var(--navy); color:#fff; }
.aurix-btn--primary:hover { background:var(--gold); color:#fff; }
.aurix-btn--gold { background:var(--gold); color:#fff; }
.aurix-btn--gold:hover { background:var(--navy); color:#fff; }

.aurix-auth-switch { text-align:center; font-size:.84rem; color:var(--muted); margin:0; }
.aurix-link-btn { background:none; border:none; color:var(--gold); font-family:'Outfit',sans-serif; font-size:.84rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:color .15s; }
.aurix-link-btn:hover { color:var(--navy); }

.aurix-benefits { background:var(--gold-pale); border-radius:var(--r); border-left:3px solid var(--gold); padding:14px 16px; margin:18px 0; }
.aurix-benefits p { font-size:.78rem; font-weight:700; color:var(--navy); letter-spacing:.05em; text-transform:uppercase; margin-bottom:10px; }
.aurix-benefits ul { list-style:none; display:flex; flex-direction:column; gap:7px; }
.aurix-benefits ul li { display:flex; align-items:center; gap:10px; font-size:.84rem; color:var(--navy); }
.aurix-benefits ul li i { color:var(--gold); font-size:.76rem; }

.aurix-info-box { background:#f0f8ff; border-left:3px solid #3182ce; border-radius:0 var(--r) var(--r) 0; padding:12px 16px; font-size:.84rem; color:#2b6cb0; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
.aurix-privacy-note { font-size:.76rem; color:var(--muted); margin-bottom:16px; line-height:1.55; }

@media (max-width:480px) {
  .aurix-auth-card { padding:20px 16px; }
  .aurix-auth-tabs .aurix-auth-tab { font-size:.76rem; padding:12px 10px; }
}
</style>

<script>
(function() {
  // Tab switching
  document.querySelectorAll('.aurix-auth-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
      var target = tab.getAttribute('data-target');
      document.querySelectorAll('.aurix-auth-tab').forEach(function(t) { t.classList.remove('active'); });
      document.querySelectorAll('.aurix-auth-panel').forEach(function(p) { p.classList.remove('active'); });
      tab.classList.add('active');
      var panel = document.getElementById(target);
      if (panel) panel.classList.add('active');
    });
  });

  // Switch links inside panels
  document.querySelectorAll('[data-switch]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var targetId = btn.getAttribute('data-switch');
      document.querySelectorAll('.aurix-auth-tab').forEach(function(t) {
        t.classList.toggle('active', t.getAttribute('data-target') === targetId);
      });
      document.querySelectorAll('.aurix-auth-panel').forEach(function(p) {
        p.classList.toggle('active', p.id === targetId);
      });
    });
  });

  // Password toggle
  document.querySelectorAll('.aurix-pw-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var input = btn.closest('.aurix-input-wrap').querySelector('input');
      var icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye';
      } else {
        input.type = 'password';
        icon.className = 'fas fa-eye-slash';
      }
    });
  });
})();
</script>

<?php do_action('woocommerce_after_customer_login_form'); ?>
