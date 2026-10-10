<?php
/**
 * Kit Builder Section — template part
 * All text/links editable via Appearance → Customize → "Aurix — Kit Builder Section"
 */
defined('ABSPATH') || exit;

// Read from Customizer (with fallback defaults)
$kb_eyebrow    = get_theme_mod('aurix_kb_eyebrow',  'Custom instrument trays');
$kb_big_word   = get_theme_mod('aurix_kb_big_word',  'Build');
$kb_subtitle   = get_theme_mod('aurix_kb_subtitle',  'Your kit. Your way.');
$kb_desc       = get_theme_mod('aurix_kb_desc',      'Configure your signature instrument tray from scratch. Search any tool, drag & drop your layout, and submit for a custom quote — all in one place.');
$kb_btn_text   = get_theme_mod('aurix_kb_btn_text',  'Open Kit Builder');
$kb_btn_url    = get_theme_mod('aurix_kb_btn_url',   home_url('/custom-kits'));
$kb_s1_num     = get_theme_mod('aurix_kb_stat1_num',   '500+');
$kb_s1_lbl     = get_theme_mod('aurix_kb_stat1_label', 'Instruments');
$kb_s2_num     = get_theme_mod('aurix_kb_stat2_num',   '48h');
$kb_s2_lbl     = get_theme_mod('aurix_kb_stat2_label', 'Quote turnaround');
$kb_s3_num     = get_theme_mod('aurix_kb_stat3_num',   'Global');
$kb_s3_lbl     = get_theme_mod('aurix_kb_stat3_label', 'Suppliers');
$kb_note_season = get_theme_mod('aurix_kb_note_season', 'Fall 2026 Collection');
$kb_note_text   = get_theme_mod('aurix_kb_note_text',  'Currently manufacturing our Fall 2026 USA Collection. Secure early-access pricing by joining the VIP Waitlist today.');
$uid = 'kb-' . uniqid();
?>

<style>
.aurix-kb-wrap{padding:40px clamp(16px,4vw,40px);background:transparent}
.kb *,.kb *::before,.kb *::after{box-sizing:border-box;margin:0;padding:0}
.kb{font-family:'Outfit','DM Sans',sans-serif;padding:40px 0;width:100%}
.kb-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.35fr);gap:24px;max-width:1400px;margin:0 auto;align-items:stretch}
.kb-left{border:1.5px solid #b8925a;border-radius:20px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 48px;text-align:center;background:#ffffff;position:relative;overflow:hidden;min-height:480px}
.kb-left-corner{position:absolute;top:0;right:0;width:100px;height:100px;background:#faf5e8;border-bottom-left-radius:100%}
.kb-left-corner2{position:absolute;bottom:0;left:0;width:60px;height:60px;background:#faf5e8;border-top-right-radius:100%}
.kb-eyebrow{font-size:10px;font-weight:500;letter-spacing:0.16em;text-transform:uppercase;color:#734608;margin-bottom:14px}
.kb-title{font-family:'Cormorant Garamond',serif;font-size:80px;font-weight:700;color:#734608;line-height:1;margin-bottom:6px}
.kb-subtitle{font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:#64748b;margin-bottom:24px}
.kb-divider{width:48px;height:1.5px;background:#e8d9a0;margin:0 auto 24px}
.kb-desc{font-size:15px;color:#2d3748;line-height:1.8;max-width:300px;margin-bottom:36px}
.kb-btn{display:inline-flex;align-items:center;gap:10px;background:#1a1a1a;color:#ffffff !important;border:none;border-radius:10px;padding:15px 28px;font-size:14px;font-weight:500;font-family:'Outfit',sans-serif;cursor:pointer;text-decoration:none !important;transition:background .2s,transform .15s;letter-spacing:.02em}
.kb-btn:hover{background:#734608;color:#ffffff !important;transform:translateY(-2px)}
.kb-btn svg{width:16px;height:16px;flex-shrink:0}
.kb-stats{display:flex;gap:32px;margin-top:36px;padding-top:28px;border-top:1px solid #f0ead8;width:100%;justify-content:center}
.kb-stat{display:flex;flex-direction:column;align-items:center;gap:3px}
.kb-stat-num{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:#734608;line-height:1}
.kb-stat-label{font-size:11px;color:#64748b;letter-spacing:.04em}
.kb-right{display:flex;flex-direction:column;gap:14px}
.kb-note{background:#fdf8ed;border-radius:16px;padding:22px 24px;border:.5px solid #e8d5a3}
.kb-note-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.kb-note-tag{display:inline-flex;align-items:center;gap:5px;background:#734608;color:#fff;font-size:10px;font-weight:500;letter-spacing:.1em;text-transform:uppercase;padding:4px 12px;border-radius:20px}
.kb-note-season{font-size:11px;color:#734608;font-weight:500;letter-spacing:.06em}
.kb-note-text{font-size:14px;color:#5a4a2a;line-height:1.75;text-align:center;font-style:italic}
.kb-email-card{background:#fff;border:.5px solid #e3e0d8;border-radius:16px;padding:22px 24px}
.kb-email-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.kb-email-label{font-size:11px;font-weight:500;letter-spacing:.1em;text-transform:uppercase;color:#64748b}
.kb-vip-badge{display:inline-flex;align-items:center;gap:4px;background:#faf5e8;border:.5px solid #e8d5a3;color:#734608;font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px}
.kb-email-row{display:flex;gap:8px;align-items:stretch}
.kb-email-input{flex:1;min-width:0;background:#f7f6f3;border:1px solid #e3e0d8;border-radius:10px;padding:13px 16px;font-size:14px;color:#222;font-family:'Outfit',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s,background .2s;-webkit-appearance:none;appearance:none}
.kb-email-input::placeholder{color:#c0bdb6}
.kb-email-input:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.15);background:#fff}
.kb-email-submit{background:#734608;border:none;border-radius:10px;width:48px;height:48px;min-width:48px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .2s,transform .15s}
.kb-email-submit:hover{background:#b5923e;transform:translateY(-1px)}
.kb-email-submit svg{width:17px;height:17px;stroke:#fff;flex-shrink:0}
.kb-privacy{font-size:11px;color:#64748b;margin-top:10px;display:flex;align-items:center;gap:5px}
.kb-privacy svg{flex-shrink:0}
.kb-how{background:#fff;border:.5px solid #e3e0d8;border-radius:16px;padding:22px 24px;flex:1}
.kb-how-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.kb-how-title{font-size:11px;font-weight:500;letter-spacing:.1em;text-transform:uppercase;color:#64748b}
.kb-how-badge{background:#f7f6f3;border-radius:20px;padding:3px 10px;font-size:11px;color:#64748b;border:.5px solid #e3e0d8}
.kb-steps-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.kb-step{display:flex;flex-direction:column;align-items:flex-start;gap:10px;background:#f9f8f5;border-radius:12px;padding:16px 14px}
.kb-step-num{width:28px;height:28px;border-radius:50%;border:1.5px solid #b8925a;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:500;color:#734608;flex-shrink:0}
.kb-step-title{font-size:13.5px;font-weight:500;color:#222;line-height:1.3}
.kb-step-desc{font-size:12px;color:#64748b;line-height:1.55;flex:1}
.kb-step-tag{display:inline-block;background:#fff;border-radius:4px;font-size:11px;color:#64748b;padding:2px 8px;border:.5px solid #e3e0d8;margin-top:auto}
@media(max-width:860px){.kb-grid{grid-template-columns:1fr}.kb-left{min-height:auto;padding:44px 32px}.kb-title{font-size:64px}}
@media(max-width:580px){.kb-grid{gap:14px}.kb-left{padding:36px 20px}.kb-title{font-size:52px}.kb-stats{gap:20px}.kb-steps-grid{grid-template-columns:1fr}.kb-note-top{flex-wrap:wrap;gap:8px}}
</style>

<div class="aurix-kb-wrap">
<div class="kb">
  <div class="kb-grid">

    <!-- LEFT -->
    <div class="kb-left">
      <div class="kb-left-corner"></div>
      <div class="kb-left-corner2"></div>
      <div class="kb-eyebrow"><?php echo esc_html($kb_eyebrow); ?></div>
      <h1 class="kb-title"><?php echo esc_html($kb_big_word); ?></h1>
      <div class="kb-subtitle"><?php echo esc_html($kb_subtitle); ?></div>
      <div class="kb-divider"></div>
      <p class="kb-desc"><?php echo esc_html($kb_desc); ?></p>
      <a href="<?php echo esc_url($kb_btn_url ?: home_url('/custom-kits')); ?>" class="kb-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        <?php echo esc_html($kb_btn_text); ?>
      </a>
      <div class="kb-stats">
        <div class="kb-stat"><span class="kb-stat-num"><?php echo esc_html($kb_s1_num); ?></span><span class="kb-stat-label"><?php echo esc_html($kb_s1_lbl); ?></span></div>
        <div class="kb-stat"><span class="kb-stat-num"><?php echo esc_html($kb_s2_num); ?></span><span class="kb-stat-label"><?php echo esc_html($kb_s2_lbl); ?></span></div>
        <div class="kb-stat"><span class="kb-stat-num"><?php echo esc_html($kb_s3_num); ?></span><span class="kb-stat-label"><?php echo esc_html($kb_s3_lbl); ?></span></div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="kb-right">

      <!-- Note card -->
      <div class="kb-note">
        <div class="kb-note-top">
          <div class="kb-note-tag">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            Note
          </div>
          <span class="kb-note-season"><?php echo esc_html($kb_note_season); ?></span>
        </div>
        <p class="kb-note-text">"<?php echo esc_html($kb_note_text); ?>"</p>
      </div>

      <!-- Email / Waitlist -->
      <div class="kb-email-card">
        <div class="kb-email-top">
          <div class="kb-email-label">Join the VIP Waitlist</div>
          <div class="kb-vip-badge">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            Early access
          </div>
        </div>
        <div class="kb-email-row">
          <input type="email" class="kb-email-input" placeholder="Enter your email address..." aria-label="Email address" id="<?php echo esc_attr($uid); ?>"/>
          <button class="kb-email-submit" aria-label="Join waitlist"
            onclick="kbSubmit('<?php echo esc_js($uid); ?>','<?php echo esc_js(wp_create_nonce('aurix_waitlist')); ?>','<?php echo esc_js(admin_url('admin-ajax.php')); ?>')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </div>
        <div class="kb-privacy">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#c0bdb6" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          No spam. Unsubscribe anytime.
        </div>
      </div>

      <!-- How it works -->
      <div class="kb-how">
        <div class="kb-how-header">
          <div class="kb-how-title">How the kit builder works</div>
          <div class="kb-how-badge">3 steps</div>
        </div>
        <div class="kb-steps-grid">
          <div class="kb-step"><div class="kb-step-num">1</div><div class="kb-step-title">Search any instrument</div><div class="kb-step-desc">Find tools instantly across global supplier catalogs.</div><span class="kb-step-tag"><?php echo esc_html($kb_s1_num); ?> instruments</span></div>
          <div class="kb-step"><div class="kb-step-num">2</div><div class="kb-step-title">Drag &amp; drop your tray</div><div class="kb-step-desc">Assemble your custom kit — your layout, your way.</div><span class="kb-step-tag">Visual editor</span></div>
          <div class="kb-step"><div class="kb-step-num">3</div><div class="kb-step-title">Get a custom quote</div><div class="kb-step-desc">Submit your layout and receive pricing fast.</div><span class="kb-step-tag"><?php echo esc_html($kb_s2_num); ?> turnaround</span></div>
        </div>
      </div>

    </div>
  </div>
</div>
</div>

<script>
if(typeof kbSubmit==='undefined'){
  window.kbSubmit=function(id,nonce,ajaxUrl){
    var el=document.getElementById(id);if(!el)return;
    var v=el.value.trim();
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)){
      el.style.borderColor='#e24b4a';el.style.boxShadow='0 0 0 3px rgba(226,75,74,.15)';
      el.focus();setTimeout(function(){el.style.borderColor='';el.style.boxShadow='';},2000);return;
    }
    var fd=new FormData();fd.append('action','aurix_waitlist_signup');fd.append('email',v);fd.append('nonce',nonce);
    fetch(ajaxUrl,{method:'POST',body:fd}).catch(function(){});
    el.style.borderColor='#1d9e75';el.style.boxShadow='0 0 0 3px rgba(29,158,117,.15)';
    el.value='';el.placeholder="You're on the list! ✓";
    setTimeout(function(){el.style.borderColor='';el.style.boxShadow='';el.placeholder='Enter your email address...';},3500);
  }
}
</script>
