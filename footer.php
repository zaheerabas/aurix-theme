  </main>
</div>

<?php
$phone = get_theme_mod('aurix_phone','+1 (234) 567-8900');
$email = get_theme_mod('aurix_email','info@aurixinternational.com');
$year  = date('Y');
$pt    = get_page_template_slug();
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
          <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo">
            <div class="footer-logo__mark">
              <svg viewBox="0 0 38 38" fill="none"><polygon points="19,4 34,32 26,32 19,17 12,32 4,32" fill="none" stroke="#0E1B2E" stroke-width="2.2" stroke-linejoin="round"/><line x1="10" y1="24" x2="28" y2="24" stroke="#C9A84C" stroke-width="2"/></svg>
            </div>
            <div class="footer-logo__text">
              <span class="footer-logo__name">AURIX</span>
              <span class="footer-logo__sub">International</span>
            </div>
          </a>
          <div class="footer-logo__line"></div>
          <p class="footer-brand__desc">Aurix International is a USA-based supplier and distributor of precision surgical &amp; dental instruments — combining four generations of manufacturing expertise with modern, efficient sourcing solutions for healthcare professionals worldwide.</p>
          <!-- Social icons — brand colors via inline style -->
          <div class="footer-socials">
            <a class="fsoc fsoc--fb" href="#" aria-label="Facebook">
              <svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
            </a>
            <a class="fsoc fsoc--x" href="#" aria-label="X / Twitter">
              <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63 5.905-5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a class="fsoc fsoc--li" href="#" aria-label="LinkedIn">
              <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 011.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 001.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 00-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
            </a>
            <a class="fsoc fsoc--ig" href="#" aria-label="Instagram">
              <svg viewBox="0 0 24 24"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2m-.2 2A3.6 3.6 0 004 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 003.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 011.25 1.25A1.25 1.25 0 0117.25 8 1.25 1.25 0 0116 6.75a1.25 1.25 0 011.25-1.25M12 7a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5m0 2a3 3 0 00-3 3 3 3 0 003 3 3 3 0 003-3 3 3 0 00-3-3z"/></svg>
            </a>
            <a class="fsoc fsoc--yt" href="#" aria-label="YouTube">
              <svg viewBox="0 0 24 24"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/></svg>
            </a>
            <a class="fsoc fsoc--pin" href="#" aria-label="Pinterest">
              <svg viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.181-.78 1.172-4.97 1.172-4.97s-.299-.598-.299-1.482c0-1.388.806-2.428 1.808-2.428.852 0 1.266.64 1.266 1.408 0 .858-.546 2.14-.828 3.33-.236.995.499 1.806 1.476 1.806 1.772 0 3.137-1.868 3.137-4.564 0-2.387-1.715-4.057-4.163-4.057-2.836 0-4.5 2.126-4.5 4.322 0 .856.33 1.772.741 2.273a.3.3 0 01.069.286c-.076.314-.243.995-.276 1.134-.044.183-.145.222-.335.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.966-.527-2.292-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.938.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
            </a>
          </div>
        </div>

        <!-- About Us -->
        <div class="footer-col">
          <h4>About Us</h4>
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
        </div>

        <!-- Products -->
        <div class="footer-col">
          <h4>Our Products</h4>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/product-category/surgical')); ?>">Surgical Instruments</a></li>
            <li><a href="<?php echo esc_url(home_url('/product-category/dental')); ?>">Dental Instruments</a></li>
            <li><a href="<?php echo esc_url(home_url('/custom-kits')); ?>">Custom Kit Builder</a></li>
            <li><a href="<?php echo esc_url(home_url('/product-category/kits')); ?>">Pre-Assembled Kits</a></li>
            <li><a href="<?php echo esc_url(home_url('/oem-private-label')); ?>">OEM Manufacturing</a></li>
            <li><a href="<?php echo esc_url(home_url('/oem-private-label#private-label')); ?>">Private Labeling</a></li>
            <li><a href="<?php echo esc_url(home_url('/oem-private-label#branding')); ?>">Custom Branding</a></li>
          </ul>
        </div>

        <!-- Customer Care -->
        <div class="footer-col">
          <h4>Customer Care</h4>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/get-a-quote')); ?>">Get a Quote</a></li>
            <li><a href="<?php echo esc_url(home_url('/wholesale')); ?>">Wholesale Inquiry</a></li>
            <li><a href="<?php echo esc_url(home_url('/shipping')); ?>">Shipping &amp; Export</a></li>
            <li><a href="<?php echo esc_url(home_url('/returns')); ?>">Returns &amp; Warranty</a></li>
            <li><a href="<?php echo esc_url(home_url('/payment-info')); ?>">Payment Info</a></li>
            <li><a href="<?php echo esc_url(home_url('/faq')); ?>">FAQs</a></li>
            <li><a href="<?php echo esc_url(home_url('/tracking')); ?>">Track My Order</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="footer-col footer-col--contact">
          <h4>Our Address</h4>
          <div class="footer-contact-block">
            <i class="fas fa-map-marker-alt"></i>
            <div><p>USA-based</p><p>Worldwide Distribution &amp; Export</p></div>
          </div>
          <div class="footer-contact-block">
            <i class="fas fa-phone"></i>
            <div><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','', $phone)); ?>"><?php echo esc_html($phone); ?></a></div>
          </div>
          <div class="footer-contact-block">
            <i class="fas fa-envelope"></i>
            <div><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- MOBILE FOOTER (accordion) -->
  <div class="footer-mob">
    <?php
    $mob_sections = [
        'Contact' => [
            [$email, 'mailto:'.$email],
            [$phone, 'tel:'.preg_replace('/[^+0-9]/','', $phone)],
            ['USA — Worldwide Distribution &amp; Export', '#'],
            ['Get a Quote', home_url('/get-a-quote')],
            ['Contact Us', home_url('/contact')],
        ],
        'Information' => [
            ['About Us', home_url('/about')],
            ['Our Story', home_url('/about#story')],
            ['Our Mission &amp; Vision', home_url('/about#mission')],
            ['Why Choose Us', home_url('/about#why')],
            ['FAQs', home_url('/faq')],
            ['Blog', home_url('/blog')],
        ],
        'Our Products' => [
            ['Surgical Instruments', home_url('/product-category/surgical')],
            ['Dental Instruments', home_url('/product-category/dental')],
            ['Custom Kit Builder', home_url('/custom-kits')],
            ['Pre-Assembled Kits', home_url('/product-category/kits')],
            ['OEM Manufacturing', home_url('/oem-private-label')],
            ['Private Labeling', home_url('/oem-private-label#private-label')],
        ],
        'Customer Care' => [
            ['Shipping &amp; Export', home_url('/shipping')],
            ['Returns &amp; Warranty', home_url('/returns')],
            ['Payment Info', home_url('/payment-info')],
            ['Track My Order', home_url('/tracking')],
            ['Privacy Policy', home_url('/privacy-policy')],
            ['Terms &amp; Conditions', home_url('/terms-conditions')],
        ],
    ];
    foreach ($mob_sections as $title => $links) :
    ?>
    <div class="mob-accord">
      <button class="mob-accord__btn" aria-expanded="false">
        <?php echo esc_html($title); ?>
        <span class="mob-accord__arrow"><svg viewBox="0 0 12 12"><polyline points="2,4 6,8 10,4"/></svg></span>
      </button>
      <div class="mob-accord__body">
        <ul><?php foreach ($links as $l) : ?>
          <li><a href="<?php echo esc_url($l[1]); ?>"><?php echo wp_kses_post($l[0]); ?></a></li>
        <?php endforeach; ?></ul>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Mobile brand block -->
    <div class="mob-brand">
      <div class="mob-brand__logo">
        <span class="mob-brand__name">AURIX</span>
        <span class="mob-brand__sub">International</span>
        <div class="mob-brand__line"></div>
      </div>
      <p>USA-based supplier of precision surgical and dental instruments — four generations of expertise delivered worldwide.</p>
      <div class="footer-socials footer-socials--center">
        <a class="fsoc fsoc--fb" href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg></a>
        <a class="fsoc fsoc--x"  href="#" aria-label="X"><svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63 5.905-5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
        <a class="fsoc fsoc--li" href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14m-.5 15.5v-5.3a3.26 3.26 0 00-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 011.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 001.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 00-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg></a>
        <a class="fsoc fsoc--ig" href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2m-.2 2A3.6 3.6 0 004 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 003.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 011.25 1.25A1.25 1.25 0 0117.25 8 1.25 1.25 0 0116 6.75a1.25 1.25 0 011.25-1.25M12 7a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5m0 2a3 3 0 00-3 3 3 3 0 003 3 3 3 0 003-3 3 3 0 00-3-3z"/></svg></a>
        <a class="fsoc fsoc--yt"  href="#" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/></svg></a>
        <a class="fsoc fsoc--pin" href="#" aria-label="Pinterest"><svg viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.181-.78 1.172-4.97 1.172-4.97s-.299-.598-.299-1.482c0-1.388.806-2.428 1.808-2.428.852 0 1.266.64 1.266 1.408 0 .858-.546 2.14-.828 3.33-.236.995.499 1.806 1.476 1.806 1.772 0 3.137-1.868 3.137-4.564 0-2.387-1.715-4.057-4.163-4.057-2.836 0-4.5 2.126-4.5 4.322 0 .856.33 1.772.741 2.273a.3.3 0 01.069.286c-.076.314-.243.995-.276 1.134-.044.183-.145.222-.335.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.966-.527-2.292-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.938.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg></a>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="footer-bottom">
    <div class="footer-bottom__inner">
      <p class="footer-copy">&copy; <?php echo esc_html($year); ?> <strong>Aurix International</strong>. All Rights Reserved.
        &nbsp;|&nbsp;<a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a>
        &nbsp;|&nbsp;<a href="<?php echo esc_url(home_url('/terms-conditions')); ?>">Terms &amp; Conditions</a>
      </p>
      <p class="footer-dev">Designed &amp; Developed By <span class="dev-mark"><span style="color:#4DA6FF">TecSol</span><span style="color:#fff">Pr</span><span style="color:#FF5252">o</span></span></p>
      <div class="footer-pay">
        <div class="pay-chip p-visa">VISA</div>
        <div class="pay-chip p-mc"><div class="mc-w"><div class="mc-c mc-l"></div><div class="mc-c mc-r"></div></div></div>
        <div class="pay-chip p-pp"><span style="color:#003087">Pay</span><span style="color:#009CDE">Pal</span></div>
        <div class="pay-chip p-disc">DIS<span style="color:#FF6600">COVER</span></div>
        <div class="pay-chip p-amex" style="color:#2E77BC">AMEX</div>
      </div>
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
