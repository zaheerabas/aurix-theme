<?php
/**
 * Template Name: Distributor Community
 * Template Post Type: page
 */
defined('ABSPATH') || exit;
get_header();
?>

<!-- ===================================================
     AURIX INTERNATIONAL — DISTRIBUTOR COMMUNITY SECTION
     Palette: Navy/Black, Gold #D4A338, Cream
     Fonts: Cormorant Garamond (headings) + DM Sans (body)
     =================================================== -->

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<section class="adc-section">
  <div class="adc-bg-glow"></div>
  <div class="adc-bg-pattern"></div>

  <div class="adc-container">
    <div class="adc-content">
      <span class="adc-eyebrow">Join Our Network</span>
      <h2 class="adc-heading">Become Part of the<br><span class="adc-heading-accent">Aurix Distributor Community</span></h2>
      <p class="adc-subtext">
        Connect with a global network of trusted distributors. Get early access to new product lines,
        exclusive pricing, and priority support &mdash; straight to your inbox.
      </p>

      <form class="adc-form" id="adcForm" novalidate>
        <?php wp_nonce_field('adc_distributor_signup', 'adc_nonce'); ?>
        <div class="adc-input-wrap">
          <input
            type="email"
            id="adcEmail"
            class="adc-input"
            placeholder="Enter your email address"
            required
            autocomplete="email"
          >
          <button type="submit" class="adc-submit" id="adcSubmitBtn">
            <span class="adc-submit-text">Join Now</span>
            <svg class="adc-submit-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
        <p class="adc-error" id="adcError">Please enter a valid email address.</p>
        <p class="adc-note">No spam. Unsubscribe anytime. By joining, you agree to receive distributor updates from Aurix International.</p>
      </form>

      <div class="adc-success" id="adcSuccess">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="12" cy="12" r="11" stroke="#D4A338" stroke-width="1.5"/>
          <path d="M7 12.5L10.5 16L17 8.5" stroke="#D4A338" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h3>Welcome to the Network</h3>
        <p>Thank you for joining. Our team will be in touch with exclusive distributor resources shortly.</p>
      </div>

      <div class="adc-stats">
        <div class="adc-stat">
          <span class="adc-stat-num">40+</span>
          <span class="adc-stat-label">Countries Served</span>
        </div>
        <div class="adc-stat-divider"></div>
        <div class="adc-stat">
          <span class="adc-stat-num">500+</span>
          <span class="adc-stat-label">Active Distributors</span>
        </div>
        <div class="adc-stat-divider"></div>
        <div class="adc-stat">
          <span class="adc-stat-num">25+</span>
          <span class="adc-stat-label">Years of Trust</span>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  .adc-section {
    position: relative;
    background: linear-gradient(160deg, #0B0E14 0%, #10141F 45%, #0B0E14 100%);
    padding: 100px 24px;
    overflow: hidden;
    font-family: 'DM Sans', sans-serif;
    box-sizing: border-box;
  }
  .adc-section *, .adc-section *::before, .adc-section *::after {
    box-sizing: border-box;
  }

  .adc-bg-glow {
    position: absolute;
    top: -20%;
    left: 50%;
    transform: translateX(-50%);
    width: 900px;
    height: 900px;
    background: radial-gradient(circle, rgba(212,163,56,0.14) 0%, rgba(212,163,56,0) 65%);
    pointer-events: none;
    z-index: 0;
  }

  .adc-bg-pattern {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(212,163,56,0.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(212,163,56,0.05) 1px, transparent 1px);
    background-size: 48px 48px;
    -webkit-mask-image: radial-gradient(ellipse 60% 60% at 50% 40%, #000 0%, transparent 75%);
    mask-image: radial-gradient(ellipse 60% 60% at 50% 40%, #000 0%, transparent 75%);
    pointer-events: none;
    z-index: 0;
  }

  .adc-container {
    position: relative;
    z-index: 1;
    max-width: 780px;
    margin: 0 auto;
  }

  .adc-content {
    text-align: center;
  }

  .adc-eyebrow {
    display: inline-block;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #D4A338;
    padding: 8px 20px;
    border: 1px solid rgba(212,163,56,0.35);
    border-radius: 30px;
    background: rgba(212,163,56,0.06);
    margin-bottom: 28px;
    opacity: 0;
    animation: adcFadeUp 0.7s ease forwards;
  }

  .adc-heading {
    font-family: 'Cormorant Garamond', serif;
    font-weight: 600;
    font-size: clamp(32px, 4.5vw, 52px);
    line-height: 1.2;
    color: #F5F0E6;
    margin: 0 0 22px;
    opacity: 0;
    animation: adcFadeUp 0.7s ease 0.1s forwards;
  }

  .adc-heading-accent {
    color: #D4A338;
    font-style: italic;
  }

  .adc-subtext {
    font-size: 16px;
    line-height: 1.7;
    color: rgba(245,240,230,0.65);
    max-width: 560px;
    margin: 0 auto 44px;
    opacity: 0;
    animation: adcFadeUp 0.7s ease 0.2s forwards;
  }

  .adc-form {
    opacity: 0;
    animation: adcFadeUp 0.7s ease 0.3s forwards;
  }

  .adc-input-wrap {
    display: flex;
    gap: 10px;
    max-width: 480px;
    margin: 0 auto;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(212,163,56,0.3);
    border-radius: 50px;
    padding: 6px 6px 6px 24px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  .adc-input-wrap:focus-within {
    border-color: #D4A338;
    box-shadow: 0 0 0 4px rgba(212,163,56,0.12);
  }

  .adc-input {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #F5F0E6;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    padding: 12px 0;
  }

  .adc-input::placeholder {
    color: rgba(245,240,230,0.4);
  }

  .adc-submit {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #D4A338, #B8872A);
    color: #0B0E14;
    border: none;
    border-radius: 50px;
    padding: 13px 26px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.3px;
    cursor: pointer;
    white-space: nowrap;
    transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
  }

  .adc-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(212,163,56,0.35);
  }

  .adc-submit:active {
    transform: translateY(0);
  }

  .adc-submit:disabled {
    opacity: 0.7;
    cursor: default;
    transform: none;
  }

  .adc-submit-icon {
    transition: transform 0.25s ease;
  }

  .adc-submit:hover .adc-submit-icon {
    transform: translateX(3px);
  }

  .adc-error {
    color: #E8896B;
    font-size: 13px;
    margin: 14px 0 0;
    display: none;
  }

  .adc-error.adc-show {
    display: block;
  }

  .adc-note {
    font-size: 12.5px;
    color: rgba(245,240,230,0.4);
    margin: 18px 0 0;
  }

  .adc-success {
    display: none;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 20px;
    animation: adcFadeUp 0.5s ease forwards;
  }

  .adc-success.adc-show {
    display: flex;
  }

  .adc-success h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px;
    font-weight: 600;
    color: #F5F0E6;
    margin: 4px 0 0;
  }

  .adc-success p {
    font-size: 14.5px;
    color: rgba(245,240,230,0.6);
    max-width: 400px;
    margin: 0;
    line-height: 1.6;
  }

  .adc-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
    margin-top: 64px;
    padding-top: 44px;
    border-top: 1px solid rgba(212,163,56,0.15);
    opacity: 0;
    animation: adcFadeUp 0.7s ease 0.4s forwards;
  }

  .adc-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
  }

  .adc-stat-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 34px;
    font-weight: 700;
    color: #D4A338;
    line-height: 1;
  }

  .adc-stat-label {
    font-size: 12px;
    letter-spacing: 0.5px;
    color: rgba(245,240,230,0.5);
    text-transform: uppercase;
  }

  .adc-stat-divider {
    width: 1px;
    height: 36px;
    background: rgba(212,163,56,0.2);
  }

  @keyframes adcFadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Tablet */
  @media (max-width: 900px) {
    .adc-section { padding: 80px 20px; }
    .adc-stats { gap: 20px; }
  }

  /* Mobile */
  @media (max-width: 480px) {
    .adc-section { padding: 64px 16px; }
    .adc-input-wrap {
      flex-direction: column;
      border-radius: 24px;
      padding: 16px;
      gap: 12px;
    }
    .adc-input { padding: 8px 4px; text-align: center; }
    .adc-submit { width: 100%; justify-content: center; }
    .adc-stats { gap: 16px; }
    .adc-stat-num { font-size: 26px; }
    .adc-stat-label { font-size: 10px; }
    .adc-stat-divider { height: 28px; }
  }
</style>

<script>
(function() {
  var form = document.getElementById('adcForm');
  var emailInput = document.getElementById('adcEmail');
  var errorMsg = document.getElementById('adcError');
  var successBox = document.getElementById('adcSuccess');
  var submitBtn = document.getElementById('adcSubmitBtn');

  if (!form) return;

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    var email = emailInput.value.trim();

    if (!isValidEmail(email)) {
      errorMsg.classList.add('adc-show');
      errorMsg.textContent = 'Please enter a valid email address.';
      emailInput.style.borderColor = '#E8896B';
      return;
    }

    errorMsg.classList.remove('adc-show');
    submitBtn.disabled = true;
    submitBtn.querySelector('.adc-submit-text').textContent = 'Joining...';

    var nonceField = form.querySelector('#adc_nonce');

    fetch(<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'adc_distributor_signup',
        nonce: nonceField ? nonceField.value : '',
        email: email
      })
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          form.style.display = 'none';
          successBox.classList.add('adc-show');
        } else {
          errorMsg.textContent = (data && data.data && data.data.message) || 'Something went wrong. Please try again.';
          errorMsg.classList.add('adc-show');
          submitBtn.disabled = false;
          submitBtn.querySelector('.adc-submit-text').textContent = 'Join Now';
        }
      })
      .catch(function () {
        errorMsg.textContent = 'Something went wrong. Please try again.';
        errorMsg.classList.add('adc-show');
        submitBtn.disabled = false;
        submitBtn.querySelector('.adc-submit-text').textContent = 'Join Now';
      });
  });

  emailInput.addEventListener('input', function() {
    errorMsg.classList.remove('adc-show');
    emailInput.style.borderColor = '';
  });
})();
</script>

<!-- ===================================================
     ADDITIONAL SECTIONS — Distribution Process, Partner
     Benefits, Why Choose Aurix. Genuine Aurix content
     (USA-based, manufacturing heritage), theme's real
     brand colors (navy #0d1b2a, gold #734608/#b8925a).
     =================================================== -->
<style>
  .adp-section { max-width: 1200px; margin: 0 auto; padding: 80px 24px; font-family: 'DM Sans', sans-serif; }
  .adp-eyebrow { display:block; text-align:center; font-size:12px; font-weight:600; letter-spacing:.15em; text-transform:uppercase; color:#734608; margin-bottom:12px; }
  .adp-h2 { font-family:'Cormorant Garamond',serif; font-size:clamp(28px,4vw,40px); color:#0d1b2a; text-align:center; margin:0 0 50px; }

  /* Process steps */
  .adp-steps { display:grid; grid-template-columns:repeat(5,1fr); gap:0; position:relative; }
  .adp-steps::before { content:''; position:absolute; top:28px; left:10%; right:10%; height:2px; background:#e2e8f0; z-index:0; }
  .adp-step { position:relative; z-index:1; text-align:center; padding:0 12px; }
  .adp-step-num {
    width:56px; height:56px; border-radius:50%; background:#0d1b2a; color:#fff;
    display:flex; align-items:center; justify-content:center; margin:0 auto 18px;
    font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:700;
  }
  .adp-step h3 { font-size:15px; color:#0d1b2a; margin:0 0 8px; }
  .adp-step p { font-size:13px; color:#64748b; line-height:1.6; margin:0; }

  /* Two-panel partner section */
  .adp-partner { display:grid; grid-template-columns:1fr 1fr; gap:0; border-radius:16px; overflow:hidden; box-shadow:0 8px 40px rgba(13,27,42,.1); }
  .adp-partner-dark { background:linear-gradient(135deg,#0d1b2a,#152338); color:#fff; padding:48px 44px; }
  .adp-partner-light { background:#fff; padding:48px 44px; border:1px solid #e2e8f0; }
  .adp-partner h3 { font-family:'Cormorant Garamond',serif; font-size:26px; margin:0 0 16px; line-height:1.3; }
  .adp-partner-dark h3 { color:#fff; }
  .adp-partner-light h3 { color:#0d1b2a; }
  .adp-partner h3 em { font-style:italic; color:#d4ae7a; }
  .adp-partner-light h3 em { color:#734608; }
  .adp-partner p { font-size:14px; line-height:1.7; margin:0 0 24px; }
  .adp-partner-dark p { color:rgba(255,255,255,.75); }
  .adp-partner-light p { color:#2d3748; }
  .adp-partner ul { list-style:none; margin:0; padding:0; }
  .adp-partner li { display:flex; align-items:flex-start; gap:10px; font-size:14px; font-weight:600; margin-bottom:14px; }
  .adp-partner li i { flex-shrink:0; margin-top:3px; font-size:11px; }
  .adp-partner-dark li i { color:#d4ae7a; }
  .adp-partner-light li i { color:#734608; }

  /* Why choose us */
  .adp-why { background:#f5f1eb; border-radius:16px; padding:48px 44px; }
  .adp-why-label { font-size:12px; font-weight:600; letter-spacing:.12em; text-transform:uppercase; color:#734608; margin-bottom:10px; }
  .adp-why h3 { font-family:'Cormorant Garamond',serif; font-size:28px; color:#0d1b2a; margin:0 0 16px; }
  .adp-why h3 em { font-style:italic; color:#734608; }
  .adp-why > p { font-size:14px; color:#2d3748; line-height:1.7; max-width:760px; margin:0 0 32px; }
  .adp-why-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px 40px; }
  .adp-why-item { display:flex; align-items:flex-start; gap:12px; }
  .adp-why-item i { flex-shrink:0; margin-top:2px; color:#734608; font-size:14px; }
  .adp-why-item span { font-size:14px; font-weight:600; color:#0d1b2a; line-height:1.5; }

  @media (max-width:900px) {
    .adp-steps { grid-template-columns:repeat(3,1fr); gap:32px 0; }
    .adp-steps::before { display:none; }
    .adp-partner, .adp-why-grid { grid-template-columns:1fr; }
  }
  @media (max-width:520px) {
    .adp-steps { grid-template-columns:1fr 1fr; }
    .adp-section { padding:56px 20px; }
  }
</style>

<!-- Our Distribution Process -->
<section class="adp-section">
  <span class="adp-eyebrow">How It Works</span>
  <h2 class="adp-h2">Our Distribution Process</h2>
  <div class="adp-steps">
    <div class="adp-step">
      <div class="adp-step-num">1</div>
      <h3>Understand Your Needs</h3>
      <p>We learn about your practice, distribution business, or private-label requirements.</p>
    </div>
    <div class="adp-step">
      <div class="adp-step-num">2</div>
      <h3>Product Selection</h3>
      <p>Our team helps you choose the right instruments, kits, or OEM solutions.</p>
    </div>
    <div class="adp-step">
      <div class="adp-step-num">3</div>
      <h3>Order & Quality Check</h3>
      <p>Every order is confirmed, inspected, and prepared to precision standards.</p>
    </div>
    <div class="adp-step">
      <div class="adp-step-num">4</div>
      <h3>Worldwide Shipping</h3>
      <p>We ship reliably and on time, with export support for international partners.</p>
    </div>
    <div class="adp-step">
      <div class="adp-step-num">5</div>
      <h3>Ongoing Partnership</h3>
      <p>Dedicated support to keep your supply consistent as your business grows.</p>
    </div>
  </div>
</section>

<!-- More Than a Supplier -->
<section class="adp-section" style="padding-top:0;">
  <div class="adp-partner">
    <div class="adp-partner-dark">
      <h3>More Than a <em>Supplier</em> — Your Long-Term <em>Manufacturing Partner</em></h3>
      <p>Backed by four generations of manufacturing heritage, Aurix combines hands-on craftsmanship with a modern distribution model — built to support partners who need consistency, quality, and flexibility.</p>
      <ul>
        <li><i class="fas fa-check-circle"></i> Volume-based & distributor pricing</li>
        <li><i class="fas fa-check-circle"></i> OEM manufacturing & private labeling</li>
        <li><i class="fas fa-check-circle"></i> Custom branding, made in the USA</li>
        <li><i class="fas fa-check-circle"></i> Pre-assembled & custom procedure kits</li>
        <li><i class="fas fa-check-circle"></i> Long-term supply agreements</li>
      </ul>
    </div>
    <div class="adp-partner-light">
      <div class="adp-eyebrow" style="text-align:left;">Pioneer Program</div>
      <h3>Flexible Programs for <em>Distribution Partners</em></h3>
      <p>We offer <strong>wholesale pricing, standing orders, and custom solutions</strong> for distributors and healthcare organizations with ongoing instrument needs.</p>
      <ul>
        <li><i class="fas fa-check-circle"></i> Standing orders and recurring supply</li>
        <li><i class="fas fa-check-circle"></i> Custom kits and procedural trays</li>
        <li><i class="fas fa-check-circle"></i> Priority access to new product lines</li>
        <li><i class="fas fa-check-circle"></i> Dedicated account support</li>
        <li><i class="fas fa-check-circle"></i> Worldwide distribution & export</li>
      </ul>
    </div>
  </div>
</section>

<!-- Why Choose Aurix -->
<section class="adp-section" style="padding-top:0;">
  <div class="adp-why">
    <div class="adp-why-label">Why Partners Choose Aurix International</div>
    <h3>Built on <em>four generations</em> of manufacturing trust</h3>
    <p>From the factory floor to a modern distribution network, Aurix bridges manufacturing expertise with the reliability, speed, and flexibility today's healthcare partners expect.</p>
    <div class="adp-why-grid">
      <div class="adp-why-item"><i class="fas fa-check-circle"></i><span>Extensive selection of precision surgical & dental instruments</span></div>
      <div class="adp-why-item"><i class="fas fa-check-circle"></i><span>Dedicated account support and responsive communication</span></div>
      <div class="adp-why-item"><i class="fas fa-check-circle"></i><span>Competitive wholesale pricing and flexible purchasing</span></div>
      <div class="adp-why-item"><i class="fas fa-check-circle"></i><span>Reliable inventory and consistent product availability</span></div>
      <div class="adp-why-item"><i class="fas fa-check-circle"></i><span>OEM manufacturing, private labeling & custom branding</span></div>
      <div class="adp-why-item"><i class="fas fa-check-circle"></i><span>Four generations of manufacturing craftsmanship & trust</span></div>
    </div>
  </div>
</section>

<?php get_footer(); ?>
