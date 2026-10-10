  </main>
</div>

<?php
$phone               = get_theme_mod('aurix_phone','+1 (234) 567-8900');
$email               = get_theme_mod('aurix_email','info@aurixinternational.com');
$topbar_worldwide    = get_theme_mod('aurix_topbar_worldwide', 'Worldwide Distribution & Export');
$year                = date('Y');
$pt                  = get_page_template_slug();
$footer_brand_desc   = get_theme_mod('aurix_footer_brand_desc', 'Aurix International is a USA-based supplier and distributor of precision surgical & dental instruments — combining four generations of manufacturing expertise with modern, efficient sourcing solutions for healthcare professionals worldwide.');
$footer_address_1    = get_theme_mod('aurix_footer_address_1', 'USA-based');
$footer_address_2    = get_theme_mod('aurix_footer_address_2', 'Worldwide Distribution & Export');
$footer_mob_tagline  = get_theme_mod('aurix_footer_mob_tagline', 'USA-based supplier of precision surgical and dental instruments — four generations of expertise delivered worldwide.');
$footer_copy_name    = get_theme_mod('aurix_footer_copyright_name', 'Aurix International');
$footer_col_about    = get_theme_mod('aurix_footer_col_about', 'About Us');
$footer_col_products = get_theme_mod('aurix_footer_col_products', 'Our Products');
$footer_col_care     = get_theme_mod('aurix_footer_col_care', 'Customer Care');
$footer_col_address  = get_theme_mod('aurix_footer_col_address', 'Our Address');
$footer_dev_credit   = get_theme_mod('aurix_footer_dev_credit', 'Designed &amp; Developed By <span class="dev-mark"><span style="color:#4DA6FF">TecSol</span><span style="color:#fff">Pr</span><span style="color:#FF5252">o</span></span>');
if ($pt === 'elementor_canvas') { wp_footer(); echo '</body></html>'; return; }
?>

<footer class="aurix-footer">
  <div class="footer-bar-top"></div>

  <!-- DESKTOP FOOTER -->
  <div class="footer-desk">
    <div class="footer-container">
      <div class="footer-grid">

        <!-- Brand column -->
        <div class="footer-brand">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> — Home">
            <?php echo aurix_logo_img('color', 'footer-logo-img'); ?>
          </a>
          <div class="footer-logo__line"></div>
          <p class="footer-brand__desc"><?php echo esc_html($footer_brand_desc); ?></p>
          <!-- Social icons — managed via Appearance → Customize → Social Media Links -->
          <?php aurix_social_icons(); ?>
        </div>

        <!-- About Us -->
        <div class="footer-col">
          <h3><?php echo esc_html($footer_col_about); ?></h3>
          <?php
          if ( has_nav_menu('footer_about') ) {
              wp_nav_menu([
                  'theme_location' => 'footer_about',
                  'container'      => false,
                  'menu_class'     => '',
                  'items_wrap'     => '<ul>%3$s</ul>',
                  'depth'          => 1,
                  'fallback_cb'    => false,
              ]);
          } else { ?>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/about')); ?>">About Us</a></li>
            <li><a href="<?php echo esc_url(home_url('/about#story')); ?>">Our Story</a></li>
            <li><a href="<?php echo esc_url(home_url('/about#mission')); ?>">Our Mission</a></li>
            <li><a href="<?php echo esc_url(home_url('/about#vision')); ?>">Our Vision</a></li>
            <li><a href="<?php echo esc_url(home_url('/about#why')); ?>">Why Choose Us</a></li>
            <li><a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a></li>
            <li><a href="<?php echo esc_url(home_url('/terms-conditions')); ?>">Terms &amp; Conditions</a></li>
            <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a></li>
          </ul>
          <?php } ?>
        </div>

        <!-- Products -->
        <div class="footer-col">
          <h3><?php echo esc_html($footer_col_products); ?></h3>
          <?php
          if ( has_nav_menu('footer_products') ) {
              wp_nav_menu([
                  'theme_location' => 'footer_products',
                  'container'      => false,
                  'menu_class'     => '',
                  'items_wrap'     => '<ul>%3$s</ul>',
                  'depth'          => 1,
                  'fallback_cb'    => false,
              ]);
          } else { ?>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/product-category/surgical')); ?>">Surgical Instruments</a></li>
            <li><a href="<?php echo esc_url(home_url('/product-category/dental')); ?>">Dental Instruments</a></li>
            <li><a href="<?php echo esc_url(home_url('/custom-kits')); ?>">Custom Kit Builder</a></li>
            <li><a href="<?php echo esc_url(home_url('/product-category/kits')); ?>">Pre-Assembled Kits</a></li>
            <li><a href="<?php echo esc_url(home_url('/oem-private-label')); ?>">OEM Manufacturing</a></li>
            <li><a href="<?php echo esc_url(home_url('/oem-private-label#private-label')); ?>">Private Labeling</a></li>
            <li><a href="<?php echo esc_url(home_url('/oem-private-label#branding')); ?>">Custom Branding</a></li>
          </ul>
          <?php } ?>
        </div>

        <!-- Customer Care -->
        <div class="footer-col">
          <h3><?php echo esc_html($footer_col_care); ?></h3>
          <?php
          if ( has_nav_menu('footer_care') ) {
              wp_nav_menu([
                  'theme_location' => 'footer_care',
                  'container'      => false,
                  'menu_class'     => '',
                  'items_wrap'     => '<ul>%3$s</ul>',
                  'depth'          => 1,
                  'fallback_cb'    => false,
              ]);
          } else { ?>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/get-a-quote')); ?>">Get a Quote</a></li>
            <li><a href="<?php echo esc_url(home_url('/wholesale')); ?>">Wholesale Inquiry</a></li>
            <li><a href="<?php echo esc_url(home_url('/shipping')); ?>">Shipping &amp; Export</a></li>
            <li><a href="<?php echo esc_url(home_url('/returns')); ?>">Returns &amp; Warranty</a></li>
            <li><a href="<?php echo esc_url(home_url('/payment-info')); ?>">Payment Info</a></li>
            <li><a href="<?php echo esc_url(home_url('/faq')); ?>">FAQs</a></li>
            <li><a href="<?php echo esc_url(home_url('/tracking')); ?>">Track My Order</a></li>
          </ul>
          <?php } ?>
        </div>

        <!-- Contact Info -->
        <div class="footer-col footer-col--contact">
          <h3><?php echo esc_html($footer_col_address); ?></h3>

          <?php
          // Address lines (1–5)
          $addr_defaults = [ 1 => 'USA-based', 2 => 'Worldwide Distribution & Export', 3 => '', 4 => '', 5 => '' ];
          $addr_lines = [];
          for ( $i = 1; $i <= 5; $i++ ) {
              $line = get_theme_mod("aurix_footer_address_{$i}", $addr_defaults[$i]);
              if ( trim($line) ) $addr_lines[] = esc_html($line);
          }
          if ( $addr_lines ) : ?>
          <div class="footer-contact-block">
            <i class="fas fa-map-marker-alt"></i>
            <div><?php foreach ($addr_lines as $line) echo '<p>' . $line . '</p>'; ?></div>
          </div>
          <?php endif;

          // All phones: main + extras 2 & 3
          $all_phones = array_filter([ get_theme_mod('aurix_phone', '+1 (234) 567-8900') ]);
          for ( $i = 2; $i <= 3; $i++ ) {
              $extra = trim( get_theme_mod("aurix_footer_phone_{$i}", '') );
              if ( $extra ) $all_phones[] = $extra;
          }
          foreach ( $all_phones as $ph ) : ?>
          <div class="footer-contact-block">
            <i class="fas fa-phone"></i>
            <div><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','', $ph)); ?>"><?php echo esc_html($ph); ?></a></div>
          </div>
          <?php endforeach;

          // All emails: main + extras 2 & 3
          $all_emails = array_filter([ get_theme_mod('aurix_email', 'info@aurixinternational.com') ]);
          for ( $i = 2; $i <= 3; $i++ ) {
              $extra = trim( get_theme_mod("aurix_footer_email_{$i}", '') );
              if ( $extra ) $all_emails[] = $extra;
          }
          foreach ( $all_emails as $em ) : ?>
          <div class="footer-contact-block">
            <i class="fas fa-envelope"></i>
            <div><a href="mailto:<?php echo esc_attr($em); ?>"><?php echo esc_html($em); ?></a></div>
          </div>
          <?php endforeach; ?>

        </div>

      </div>
    </div>
  </div>

  <!-- MOBILE FOOTER (accordion) -->
  <div class="footer-mob">
    <?php
    /* ── Mobile Contact accordion ── */
    $mob_contact_fallback = [
        [$email, 'mailto:'.$email],
        [$phone, 'tel:'.preg_replace('/[^+0-9]/','', $phone)],
        ['Get a Quote',  home_url('/get-a-quote')],
        ['Contact Us',   home_url('/contact')],
    ];
    ?>
    <div class="mob-accord">
      <button class="mob-accord__btn" aria-expanded="false">
        Contact
        <span class="mob-accord__arrow"><svg viewBox="0 0 12 12"><polyline points="2,4 6,8 10,4"/></svg></span>
      </button>
      <div class="mob-accord__body">
        <ul>
          <li class="mob-accord__static"><?php echo esc_html( $topbar_worldwide ); ?></li>
          <?php if ( has_nav_menu('footer_mobile_contact') ) :
              wp_nav_menu([
                  'theme_location' => 'footer_mobile_contact',
                  'container'      => false,
                  'menu_class'     => '',
                  'items_wrap'     => '%3$s',
                  'depth'          => 1,
                  'fallback_cb'    => false,
              ]);
          else :
              foreach ($mob_contact_fallback as $l) : ?>
                <li><a href="<?php echo esc_url($l[1]); ?>"><?php echo wp_kses_post($l[0]); ?></a></li>
              <?php endforeach;
          endif; ?>
        </ul>
      </div>
    </div>

    <?php
    /* ── Information accordion ── */
    $info_fallback = [
        ['About Us',               home_url('/about')],
        ['Our Story',              home_url('/about#story')],
        ['Our Mission &amp; Vision', home_url('/about#mission')],
        ['Why Choose Us',          home_url('/about#why')],
        ['FAQs',                   home_url('/faq')],
        ['Blog',                   home_url('/blog')],
    ];
    ?>
    <div class="mob-accord">
      <button class="mob-accord__btn" aria-expanded="false">
        Information
        <span class="mob-accord__arrow"><svg viewBox="0 0 12 12"><polyline points="2,4 6,8 10,4"/></svg></span>
      </button>
      <div class="mob-accord__body">
        <?php if ( has_nav_menu('footer_mobile_info') ) :
            wp_nav_menu([
                'theme_location' => 'footer_mobile_info',
                'container'      => false,
                'menu_class'     => '',
                'items_wrap'     => '<ul>%3$s</ul>',
                'depth'          => 1,
                'fallback_cb'    => false,
            ]);
        else : ?>
        <ul><?php foreach ($info_fallback as $l) : ?>
          <li><a href="<?php echo esc_url($l[1]); ?>"><?php echo wp_kses_post($l[0]); ?></a></li>
        <?php endforeach; ?></ul>
        <?php endif; ?>
      </div>
    </div>

    <?php
    /* ── Our Products accordion (reuses desktop footer_products menu) ── */
    $prod_fallback = [
        ['Surgical Instruments', home_url('/product-category/surgical')],
        ['Dental Instruments',   home_url('/product-category/dental')],
        ['Custom Kit Builder',   home_url('/custom-kits')],
        ['Pre-Assembled Kits',   home_url('/product-category/kits')],
        ['OEM Manufacturing',    home_url('/oem-private-label')],
        ['Private Labeling',     home_url('/oem-private-label#private-label')],
    ];
    ?>
    <div class="mob-accord">
      <button class="mob-accord__btn" aria-expanded="false">
        <?php echo esc_html($footer_col_products); ?>
        <span class="mob-accord__arrow"><svg viewBox="0 0 12 12"><polyline points="2,4 6,8 10,4"/></svg></span>
      </button>
      <div class="mob-accord__body">
        <?php if ( has_nav_menu('footer_products') ) :
            wp_nav_menu([
                'theme_location' => 'footer_products',
                'container'      => false,
                'menu_class'     => '',
                'items_wrap'     => '<ul>%3$s</ul>',
                'depth'          => 1,
                'fallback_cb'    => false,
            ]);
        else : ?>
        <ul><?php foreach ($prod_fallback as $l) : ?>
          <li><a href="<?php echo esc_url($l[1]); ?>"><?php echo wp_kses_post($l[0]); ?></a></li>
        <?php endforeach; ?></ul>
        <?php endif; ?>
      </div>
    </div>

    <?php
    /* ── Customer Care accordion ── */
    $care_fallback = [
        ['Shipping &amp; Export',      home_url('/shipping')],
        ['Returns &amp; Warranty',     home_url('/returns')],
        ['Payment Info',               home_url('/payment-info')],
        ['Track My Order',             home_url('/tracking')],
        ['Privacy Policy',             home_url('/privacy-policy')],
        ['Terms &amp; Conditions',     home_url('/terms-conditions')],
    ];
    ?>
    <div class="mob-accord">
      <button class="mob-accord__btn" aria-expanded="false">
        <?php echo esc_html($footer_col_care); ?>
        <span class="mob-accord__arrow"><svg viewBox="0 0 12 12"><polyline points="2,4 6,8 10,4"/></svg></span>
      </button>
      <div class="mob-accord__body">
        <?php if ( has_nav_menu('footer_mobile_care') ) :
            wp_nav_menu([
                'theme_location' => 'footer_mobile_care',
                'container'      => false,
                'menu_class'     => '',
                'items_wrap'     => '<ul>%3$s</ul>',
                'depth'          => 1,
                'fallback_cb'    => false,
            ]);
        else : ?>
        <ul><?php foreach ($care_fallback as $l) : ?>
          <li><a href="<?php echo esc_url($l[1]); ?>"><?php echo wp_kses_post($l[0]); ?></a></li>
        <?php endforeach; ?></ul>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mobile brand block -->
    <div class="mob-brand">
      <div class="mob-brand__logo">
        <?php echo aurix_logo_img('color', 'mob-brand-logo-img'); ?>
      </div>
      <p><?php echo esc_html($footer_mob_tagline); ?></p>
      <?php aurix_social_icons('footer-socials--center'); ?>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="footer-bottom">
    <div class="footer-bottom__inner">
      <p class="footer-copy">&copy; <?php echo esc_html($year); ?> <strong><?php echo esc_html($footer_copy_name); ?></strong>. All Rights Reserved.
        &nbsp;|&nbsp;<a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a>
        &nbsp;|&nbsp;<a href="<?php echo esc_url(home_url('/terms-conditions')); ?>">Terms &amp; Conditions</a>
      </p>
      <?php if ( trim( $footer_dev_credit ) !== '' ) : ?>
        <p class="footer-dev"><?php echo wp_kses_post( $footer_dev_credit ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</footer>


<!-- Scroll to top -->
<button id="aurix-scroll-top" aria-label="Scroll to top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <i class="fas fa-chevron-up"></i>
</button>
<script>
(function() {
  var btn = document.getElementById('aurix-scroll-top');
  if (!btn) return;
  window.addEventListener('scroll', function() {
    btn.classList.toggle('show', window.scrollY > 400);
  }, {passive: true});
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
