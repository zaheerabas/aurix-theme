<?php
/**
 * Template Name: Contact Us
 * Template Post Type: page
 */
defined('ABSPATH') || exit;
get_header();

// ── Pull all fields from Customizer ──
$phone   = get_theme_mod('aurix_phone',  '+1 (347) 727-3384');
$phone2  = get_theme_mod('aurix_contact_phone2', '+1 (347) 325-5355');
$email   = get_theme_mod('aurix_email',  'info@aurixinternational.com');
$address = get_theme_mod('aurix_contact_address', 'USA-based<br>4024 Avenue S, Brooklyn New York. 11234');

$hero_tag = get_theme_mod('aurix_contact_hero_tag', 'Get in Touch');
$hero_h1  = get_theme_mod('aurix_contact_hero_h1',  "We'd Love to<br>Hear from <em>You</em>");
$hero_sub = get_theme_mod('aurix_contact_hero_sub',  'Reach out for business inquiries, partnerships, or any questions. We respond within one business day.');

$stats = [
    [ get_theme_mod('aurix_contact_stat1_num','24h'),  get_theme_mod('aurix_contact_stat1_label','Response Time') ],
    [ get_theme_mod('aurix_contact_stat2_num','50+'),  get_theme_mod('aurix_contact_stat2_label','Countries Served') ],
    [ get_theme_mod('aurix_contact_stat3_num','15+'),  get_theme_mod('aurix_contact_stat3_label','Years Experience') ],
    [ get_theme_mod('aurix_contact_stat4_num','98%'),  get_theme_mod('aurix_contact_stat4_label','Client Satisfaction') ],
];

$hours = [
    [ get_theme_mod('aurix_contact_hours_1_day','Mon – Fri'),  get_theme_mod('aurix_contact_hours_1_badge','open'),    get_theme_mod('aurix_contact_hours_1_time','9AM – 6PM EST') ],
    [ get_theme_mod('aurix_contact_hours_2_day','Saturday'),   get_theme_mod('aurix_contact_hours_2_badge','limited'), get_theme_mod('aurix_contact_hours_2_time','10AM – 2PM') ],
    [ get_theme_mod('aurix_contact_hours_3_day','Sunday'),     get_theme_mod('aurix_contact_hours_3_badge','closed'),  get_theme_mod('aurix_contact_hours_3_time','—') ],
];

$map_embed = get_theme_mod('aurix_contact_map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3028.818086011154!2d-73.92768110000002!3d40.6118371!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c2435c5082cfe9%3A0x8aa333466277a77b!2s4024%20Ave.%20S%2C%20Brooklyn%2C%20NY%2011234%2C%20USA!5e0!3m2!1sen!2s!4v1781240753093!5m2!1sen!2s');
$map_label = get_theme_mod('aurix_contact_map_label', 'Fortune Tower, Karachi');
$map_link  = get_theme_mod('aurix_contact_map_link',  'https://maps.app.goo.gl/sPZnTs529qyaZVMp9');

$social_li = get_theme_mod('aurix_contact_social_li', 'https://www.linkedin.com/company/aurix-international/');
$social_fb = get_theme_mod('aurix_contact_social_fb', 'https://www.facebook.com/profile.php?id=61590405910410');
$social_ig = get_theme_mod('aurix_contact_social_ig', 'https://www.instagram.com/aurix.international/');

$faq_title = get_theme_mod('aurix_contact_faq_title', 'Frequently Asked Questions');
$faq_sub   = get_theme_mod('aurix_contact_faq_sub',   'Quick answers to common inquiries about working with us.');
$faqs = [
    [ get_theme_mod('aurix_contact_faq_1_q','How quickly will I receive a response?'),    get_theme_mod('aurix_contact_faq_1_a','We aim to respond to all inquiries within one business day. For urgent matters, please call us directly.') ],
    [ get_theme_mod('aurix_contact_faq_2_q','What countries do you operate in?'),          get_theme_mod('aurix_contact_faq_2_a','Aurix International serves 50+ countries, with worldwide distribution via UPS, FedEx, and USPS.') ],
    [ get_theme_mod('aurix_contact_faq_3_q','Do you offer free consultations?'),           get_theme_mod('aurix_contact_faq_3_a','Yes — our initial consultation is completely free. We\'ll assess your needs and propose a tailored solution at no cost.') ],
];

// Badge label map
$badge_labels = ['open'=>'Open','limited'=>'Limited','closed'=>'Closed'];
$badge_classes = ['open'=>'badge-open','limited'=>'badge-limited','closed'=>'badge-closed'];
?>

<style>
.aurix-contact-page *,.aurix-contact-page *::before,.aurix-contact-page *::after{box-sizing:border-box}
.aurix-contact-page{margin:0;padding:0;overflow-x:hidden}
:root{--sand:#F7F4EF;--sand-2:#EDE9E2;--sand-3:#D5CFC5;--cream:#FDFCFA;--ink:#1A1714;--ink-2:#3D3A35;--muted:#8C8880;--accent:#B5814A;--accent-2:#D4A46A;--accent-bg:#FBF5EC;--green:#3A6B4E;--red:#C0392B;--border:#E4DFD6;--radius:8px;--radius-lg:14px;--shadow:0 2px 8px rgba(26,23,20,0.07),0 8px 28px rgba(26,23,20,0.06);--shadow-sm:0 1px 3px rgba(26,23,20,0.07);--transition:0.22s cubic-bezier(0.4,0,0.2,1)}
.aurix-contact-page{background:var(--cream);color:var(--ink);font-family:'Outfit','DM Sans',sans-serif;-webkit-font-smoothing:antialiased}
.aurix-contact-page a{text-decoration:none}

/* HERO */
.aurix-contact-page .hero{position:relative;overflow:hidden;background:var(--sand);padding:clamp(56px,9vw,96px) clamp(20px,5vw,60px) clamp(48px,7vw,80px);text-align:center;border-bottom:1px solid var(--border)}
.aurix-contact-page .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 50% at 20% 40%,rgba(181,129,74,0.08) 0%,transparent 70%),radial-gradient(ellipse 50% 60% at 80% 60%,rgba(181,129,74,0.05) 0%,transparent 70%);pointer-events:none}
.aurix-contact-page .hero-content{position:relative;z-index:1}
.aurix-contact-page .hero-tag{display:inline-flex;align-items:center;gap:7px;font-size:10.5px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent);background:var(--accent-bg);border:1px solid rgba(181,129,74,0.22);padding:7px 16px;border-radius:24px;margin-bottom:26px}
.aurix-contact-page .hero h1{font-family:'Cormorant Garamond',serif;font-size:clamp(38px,7vw,76px);font-weight:500;line-height:1.05;color:var(--ink);margin-bottom:18px;letter-spacing:-0.01em}
.aurix-contact-page .hero h1 em{font-style:italic;color:var(--accent)}
.aurix-contact-page .hero-sub{font-size:clamp(15px,2vw,17px);color:var(--muted);max-width:460px;margin:0 auto;line-height:1.75;font-weight:300}

/* STATS */
.aurix-contact-page .stats-bar{background:#fff;border-bottom:1px solid var(--border);display:grid;grid-template-columns:repeat(4,1fr)}
.aurix-contact-page .stat-item{text-align:center;padding:clamp(18px,3.5vw,28px) 12px;border-right:1px solid var(--border);transition:background var(--transition)}
.aurix-contact-page .stat-item:last-child{border-right:none}
.aurix-contact-page .stat-item:hover{background:var(--sand)}
.aurix-contact-page .stat-num{font-family:'Cormorant Garamond',serif;font-size:clamp(24px,4vw,32px);font-weight:600;color:var(--accent);line-height:1;margin-bottom:5px}
.aurix-contact-page .stat-label{font-size:11px;color:var(--muted);letter-spacing:0.07em;text-transform:uppercase}

/* LAYOUT */
.aurix-contact-page .contact-wrap{max-width:1160px;margin:0 auto;padding:clamp(44px,6vw,76px) clamp(16px,4vw,40px) clamp(64px,8vw,108px);display:grid;grid-template-columns:330px 1fr;gap:clamp(24px,4vw,48px);align-items:start}

/* SIDEBAR */
.aurix-contact-page .sidebar-section{background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:14px;box-shadow:var(--shadow-sm);transition:box-shadow var(--transition)}
.aurix-contact-page .sidebar-section:hover{box-shadow:var(--shadow)}
.aurix-contact-page .sidebar-header{background:var(--sand);padding:13px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:9px}
.aurix-contact-page .sidebar-header svg{width:14px;height:14px;stroke:var(--accent);fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.aurix-contact-page .sidebar-header span{font-size:10.5px;font-weight:500;letter-spacing:0.11em;text-transform:uppercase;color:var(--ink-2)}
.aurix-contact-page .contact-item{padding:13px 20px;display:flex;gap:12px;align-items:flex-start;border-bottom:1px solid var(--border);transition:background var(--transition)}
.aurix-contact-page .contact-item:last-child{border-bottom:none}
.aurix-contact-page .contact-item:hover{background:var(--sand)}
.aurix-contact-page .c-icon{width:34px;height:34px;flex-shrink:0;background:var(--accent-bg);border:1px solid rgba(181,129,74,0.18);border-radius:8px;display:flex;align-items:center;justify-content:center}
.aurix-contact-page .c-icon svg{width:14px;height:14px;stroke:var(--accent);fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round}
.aurix-contact-page .c-label{font-size:10px;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;color:var(--muted);margin-bottom:3px}
.aurix-contact-page .c-value{font-size:13px;color:var(--ink);line-height:1.6}
.aurix-contact-page .c-value a{color:var(--accent)}
.aurix-contact-page .c-value a:hover{color:var(--ink);text-decoration:underline}
.aurix-contact-page .hours-table{padding:14px 20px}
.aurix-contact-page .hours-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:13px;gap:8px}
.aurix-contact-page .hours-row:not(:last-child){border-bottom:1px solid var(--border)}
.aurix-contact-page .hours-day{color:var(--ink-2);flex-shrink:0}
.aurix-contact-page .hours-time{color:var(--muted);font-weight:300;flex-shrink:0}
.aurix-contact-page .badge{font-size:10px;font-weight:500;letter-spacing:0.07em;text-transform:uppercase;padding:3px 8px;border-radius:20px;white-space:nowrap}
.aurix-contact-page .badge-open{background:#EDF7F2;color:var(--green);border:1px solid rgba(58,107,78,0.2)}
.aurix-contact-page .badge-limited{background:var(--accent-bg);color:var(--accent);border:1px solid rgba(181,129,74,0.2)}
.aurix-contact-page .badge-closed{background:#FEF2F2;color:#A32D2D;border:1px solid rgba(163,45,45,0.2)}
.aurix-contact-page .map-mini{border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow-sm);margin-bottom:14px}
.aurix-contact-page .map-mini iframe{display:block;width:100%;height:190px;border:none;filter:saturate(0.55) brightness(1.02)}
.aurix-contact-page .map-footer{background:#fff;border-top:1px solid var(--border);padding:10px 16px;display:flex;align-items:center;justify-content:space-between;gap:8px}
.aurix-contact-page .map-footer span{font-size:12px;color:var(--muted)}
.aurix-contact-page .map-footer a{font-size:12px;font-weight:500;color:var(--accent);display:flex;align-items:center;gap:4px;white-space:nowrap}
.aurix-contact-page .map-footer a svg{width:11px;height:11px;stroke:var(--accent);fill:none;stroke-width:1.5;stroke-linecap:round}
.aurix-contact-page .social-bar{display:flex;gap:8px;padding:12px 20px}
.aurix-contact-page .social-btn{flex:1;height:36px;display:flex;align-items:center;justify-content:center;gap:5px;border:1px solid var(--border);border-radius:var(--radius);text-decoration:none;color:var(--muted);font-size:12px;transition:all 0.2s;background:#fff;position:relative;overflow:hidden}
.aurix-contact-page .social-btn::before{content:'';position:absolute;inset:0;background:var(--accent);transform:translateY(100%);transition:transform 0.2s}
.aurix-contact-page .social-btn svg,.aurix-contact-page .social-btn span{position:relative;z-index:1}
.aurix-contact-page .social-btn svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round}
.aurix-contact-page .social-btn:hover{color:#fff;border-color:var(--accent)}
.aurix-contact-page .social-btn:hover::before{transform:translateY(0)}

/* FORM CARD */
.aurix-contact-page .form-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow);overflow:hidden}
.aurix-contact-page .form-card-header{padding:clamp(24px,4vw,34px) clamp(22px,4vw,40px) clamp(22px,3vw,30px);background:linear-gradient(160deg,var(--sand) 0%,rgba(247,244,239,0.3) 60%,#fff 100%);border-bottom:1px solid var(--border)}
.aurix-contact-page .form-eyebrow{display:inline-flex;align-items:center;gap:10px;font-size:10.5px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:var(--accent);margin-bottom:10px}
.aurix-contact-page .form-eyebrow span{width:20px;height:1px;background:var(--accent-2);display:inline-block}
.aurix-contact-page .form-card-header h2{font-family:'Cormorant Garamond',serif;font-size:clamp(24px,3.5vw,30px);font-weight:500;color:var(--ink);margin-bottom:6px;letter-spacing:-0.01em}
.aurix-contact-page .form-card-header p{font-size:14px;color:var(--muted);font-weight:300;line-height:1.65}
.aurix-contact-page .form-steps{display:flex;align-items:center;margin-top:22px}
.aurix-contact-page .f-step{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--muted);flex:1}
.aurix-contact-page .f-step:last-child{flex:none}
.aurix-contact-page .f-dot{width:26px;height:26px;flex-shrink:0;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:500;color:var(--muted);transition:all 0.35s;background:#fff}
.aurix-contact-page .f-step.active .f-dot{background:var(--accent);border-color:var(--accent);color:#fff;transform:scale(1.1);box-shadow:0 0 0 4px rgba(181,129,74,0.15)}
.aurix-contact-page .f-step.done .f-dot{background:var(--green);border-color:var(--green);color:#fff}
.aurix-contact-page .f-step.active > span,.aurix-contact-page .f-step.done > span{color:var(--ink-2);font-weight:500}
.aurix-contact-page .f-line{flex:1;height:1.5px;background:var(--border);margin:0 4px;transition:background 0.4s}
.aurix-contact-page .f-line.done{background:var(--green)}

/* FIELDS */
.aurix-contact-page .field{margin-bottom:16px;position:relative}
.aurix-contact-page .field label{display:flex;align-items:center;gap:5px;font-size:12px;font-weight:500;color:var(--ink-2);margin-bottom:7px}
.aurix-contact-page .req{width:4px;height:4px;border-radius:50%;background:var(--accent);display:inline-block;flex-shrink:0}
.aurix-contact-page .field input,.aurix-contact-page .field select,.aurix-contact-page .field textarea{width:100%;background:var(--sand);border:1.5px solid var(--border);border-radius:var(--radius);color:var(--ink);font-family:'Outfit',sans-serif;font-size:14px;padding:11px 14px;outline:none;transition:border-color var(--transition),box-shadow var(--transition),background var(--transition);-webkit-appearance:none;appearance:none}
.aurix-contact-page .field input:focus,.aurix-contact-page .field select:focus,.aurix-contact-page .field textarea:focus{border-color:var(--accent);background:#fff;box-shadow:0 0 0 3.5px rgba(181,129,74,0.12)}
.aurix-contact-page .field input.valid{border-color:var(--green);background:#fff}
.aurix-contact-page .field input.invalid{border-color:var(--red);background:#fff8f8}
.aurix-contact-page .field select{cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2 4l4 4 4-4' stroke='%238C8880' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:36px}
.aurix-contact-page .field textarea{resize:vertical;min-height:115px;line-height:1.65}
.aurix-contact-page .field-hint{font-size:11px;color:var(--muted);margin-top:5px;line-height:1.5}
.aurix-contact-page .field-msg{font-size:11.5px;margin-top:5px;line-height:1.4;display:flex;align-items:center;gap:4px;font-weight:400}
.aurix-contact-page .field-msg.error{color:var(--red)}
.aurix-contact-page .field-msg.success{color:var(--green)}
.aurix-contact-page .field-msg svg{width:11px;height:11px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.aurix-contact-page .char-counter{font-size:11px;color:var(--muted);text-align:right;margin-top:4px}
.aurix-contact-page .char-counter.warn{color:var(--accent)}
.aurix-contact-page .char-counter.over{color:var(--red)}
.aurix-contact-page .budget-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}
.aurix-contact-page .budget-option input[type=radio]{display:none}
.aurix-contact-page .budget-label{display:block;text-align:center;padding:10px 6px;border:1.5px solid var(--border);border-radius:var(--radius);font-size:13px;color:var(--muted);cursor:pointer;transition:all 0.2s;background:var(--sand);position:relative;overflow:hidden}
.aurix-contact-page .budget-label::before{content:'';position:absolute;inset:0;background:var(--accent-bg);opacity:0;transition:opacity 0.2s}
.aurix-contact-page .budget-option input:checked + .budget-label{border-color:var(--accent);color:var(--accent);font-weight:500}
.aurix-contact-page .budget-option input:checked + .budget-label::before{opacity:1}
.aurix-contact-page .review-box{background:var(--sand);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;margin-bottom:20px}
.aurix-contact-page .review-row{display:flex;gap:14px;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)}
.aurix-contact-page .review-row:last-child{border-bottom:none}
.aurix-contact-page .review-key{font-weight:500;color:var(--ink);min-width:90px;flex-shrink:0}
.aurix-contact-page .review-val{color:var(--muted);flex:1;word-break:break-word}
.aurix-contact-page .check-field{display:flex;align-items:flex-start;gap:11px;margin-bottom:14px}
.aurix-contact-page .check-field input[type=checkbox]{width:16px;height:16px;flex-shrink:0;margin-top:2px;accent-color:var(--accent);cursor:pointer}
.aurix-contact-page .check-field span{font-size:13px;color:var(--muted);line-height:1.55}
.aurix-contact-page .check-field a{color:var(--accent)}
.aurix-contact-page .btn-row{display:flex;gap:10px;margin-top:10px}
.aurix-contact-page .btn-primary{flex:1;padding:14px 20px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius);font-family:'Outfit',sans-serif;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.25s;display:flex;align-items:center;justify-content:center;gap:8px;position:relative;overflow:hidden}
.aurix-contact-page .btn-primary:hover{background:#9e6e3a;transform:translateY(-1px);box-shadow:0 4px 14px rgba(181,129,74,0.35)}
.aurix-contact-page .btn-primary svg{width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;transition:transform 0.2s}
.aurix-contact-page .btn-primary:hover svg{transform:translateX(3px)}
.aurix-contact-page .btn-primary:disabled{opacity:0.55;cursor:not-allowed;transform:none;box-shadow:none}
.aurix-contact-page .btn-ghost{padding:14px 18px;background:transparent;color:var(--muted);border:1.5px solid var(--border);border-radius:var(--radius);font-family:'Outfit',sans-serif;font-size:14px;cursor:pointer;transition:all 0.2s;white-space:nowrap}
.aurix-contact-page .btn-ghost:hover{background:var(--sand);color:var(--ink);border-color:var(--sand-3)}
.aurix-contact-page .form-body{padding:clamp(22px,4vw,34px) clamp(22px,4vw,40px)}
.aurix-contact-page .section-title{font-size:10.5px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);margin-bottom:18px;display:flex;align-items:center;gap:12px}
.aurix-contact-page .section-title::after{content:'';flex:1;height:1px;background:var(--border)}
.aurix-contact-page .field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:0}
.aurix-contact-page .field-row .field{margin-bottom:0}
.aurix-contact-page .trust-bar{padding:14px clamp(22px,4vw,40px);background:var(--sand);border-top:1px solid var(--border);display:flex;align-items:center;gap:22px;flex-wrap:wrap}
.aurix-contact-page .trust-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)}
.aurix-contact-page .trust-item svg{width:13px;height:13px;stroke:var(--green);fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round}
.aurix-contact-page .step-panel{animation:stepIn 0.3s cubic-bezier(0.4,0,0.2,1) both}
@keyframes stepIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
.aurix-contact-page .step-panel.back{animation:stepInBack 0.3s both}
@keyframes stepInBack{from{opacity:0;transform:translateX(-16px)}to{opacity:1;transform:translateX(0)}}
.aurix-contact-page .success-state{display:none;padding:clamp(44px,6vw,64px) clamp(22px,4vw,40px);text-align:center;flex-direction:column;align-items:center}
.aurix-contact-page .success-icon{width:68px;height:68px;background:#EDF7F2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;animation:successPop 0.5s cubic-bezier(0.34,1.56,0.64,1) both}
@keyframes successPop{from{opacity:0;transform:scale(0.5)}to{opacity:1;transform:scale(1)}}
.aurix-contact-page .success-icon svg{width:28px;height:28px;stroke:var(--green);fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round}
.aurix-contact-page .success-state h3{font-family:'Cormorant Garamond',serif;font-size:28px;color:var(--ink);margin-bottom:10px}
.aurix-contact-page .success-state p{font-size:15px;color:var(--muted);max-width:320px;margin:0 auto 24px;line-height:1.7;font-weight:300}

/* FAQ */
.aurix-contact-page .faq-strip{background:var(--sand);border-top:1px solid var(--border);padding:clamp(44px,6vw,68px) clamp(16px,4vw,40px)}
.aurix-contact-page .faq-inner{max-width:1160px;margin:0 auto}
.aurix-contact-page .faq-header{text-align:center;margin-bottom:38px}
.aurix-contact-page .faq-header h2{font-family:'Cormorant Garamond',serif;font-size:clamp(26px,4vw,34px);font-weight:500;color:var(--ink);margin-bottom:8px;letter-spacing:-0.01em}
.aurix-contact-page .faq-header p{font-size:15px;color:var(--muted);font-weight:300}
.aurix-contact-page .faq-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.aurix-contact-page .faq-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;box-shadow:var(--shadow-sm);transition:box-shadow var(--transition),transform var(--transition)}
.aurix-contact-page .faq-card:hover{box-shadow:var(--shadow);transform:translateY(-2px)}
.aurix-contact-page .faq-q{font-size:14px;font-weight:500;color:var(--ink);margin-bottom:9px;line-height:1.45}
.aurix-contact-page .faq-a{font-size:13px;color:var(--muted);line-height:1.75;font-weight:300}

/* RESPONSIVE */
@media(max-width:860px){
  .aurix-contact-page .contact-wrap{grid-template-columns:1fr;padding-top:36px}
  .aurix-contact-page .faq-grid{grid-template-columns:1fr}
}
@media(max-width:600px){
  .aurix-contact-page .stats-bar{grid-template-columns:repeat(2,1fr)}
  .aurix-contact-page .field-row{grid-template-columns:1fr}
  .aurix-contact-page .budget-grid{grid-template-columns:repeat(2,1fr)}
}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>

<div class="aurix-contact-page">

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-tag">
      <svg viewBox="0 0 12 12" style="width:10px;height:10px;stroke:var(--accent);fill:none;stroke-width:2;stroke-linecap:round"><circle cx="6" cy="6" r="4"/><path d="M6 3v3l2 1.5"/></svg>
      <?php echo esc_html($hero_tag); ?>
    </div>
    <h1><?php echo wp_kses_post($hero_h1); ?></h1>
    <p class="hero-sub"><?php echo esc_html($hero_sub); ?></p>
  </div>
</section>

<!-- STATS -->
<div class="stats-bar">
  <?php foreach($stats as $s): ?>
  <div class="stat-item"><div class="stat-num"><?php echo esc_html($s[0]); ?></div><div class="stat-label"><?php echo esc_html($s[1]); ?></div></div>
  <?php endforeach; ?>
</div>

<!-- CONTACT SECTION -->
<div class="contact-wrap">

  <!-- SIDEBAR -->
  <aside>
    <div class="sidebar-section">
      <div class="sidebar-header">
        <svg viewBox="0 0 16 16"><path d="M8 1C5.2 1 3 3.2 3 6c0 4 5 9 5 9s5-5 5-9c0-2.8-2.2-5-5-5z"/><circle cx="8" cy="6" r="1.5"/></svg>
        <span>Contact Details</span>
      </div>
      <div class="contact-item">
        <div class="c-icon"><svg viewBox="0 0 16 16"><path d="M8 1C5.2 1 3 3.2 3 6c0 4 5 9 5 9s5-5 5-9c0-2.8-2.2-5-5-5z"/><circle cx="8" cy="6" r="1.5"/></svg></div>
        <div><div class="c-label">Address</div><div class="c-value"><?php echo esc_html($address); ?></div></div>
      </div>
      <div class="contact-item">
        <div class="c-icon"><svg viewBox="0 0 16 16"><path d="M14 10.7v2a1.3 1.3 0 01-1.4 1.3 12.9 12.9 0 01-5.6-2A12.7 12.7 0 013 8a12.9 12.9 0 01-2-5.6A1.3 1.3 0 012.3 1h2a1.3 1.3 0 011.3 1.1c.1.8.3 1.6.6 2.3a1.3 1.3 0 01-.3 1.4L5.1 6.6A10.4 10.4 0 009 10.5l.8-.9a1.3 1.3 0 011.4-.3c.7.3 1.4.5 2.2.6A1.3 1.3 0 0114 10.7z"/></svg></div>
        <div>
          <div class="c-label">Phone</div>
          <div class="c-value">
            <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',get_theme_mod('aurix_phone','+13477273384'))); ?>"><?php echo esc_html($phone); ?></a>
            <?php if($phone2): ?><br><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',get_theme_mod('aurix_contact_phone2',''))); ?>"><?php echo esc_html($phone2); ?></a><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="contact-item">
        <div class="c-icon"><svg viewBox="0 0 16 16"><rect x="1" y="3" width="14" height="10" rx="2"/><polyline points="1,3 8,9 15,3"/></svg></div>
        <div><div class="c-label">Email</div><div class="c-value"><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div></div>
      </div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-header">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 4.5V8l2 2"/></svg>
        <span>Business Hours</span>
      </div>
      <div class="hours-table">
        <?php foreach($hours as $h):
          $badge_key = strtolower(trim($h[1]));
          $badge_cls = isset($badge_classes[$badge_key]) ? $badge_classes[$badge_key] : 'badge-open';
          $badge_lbl = isset($badge_labels[$badge_key])  ? $badge_labels[$badge_key]  : ucfirst($badge_key);
        ?>
        <div class="hours-row">
          <span class="hours-day"><?php echo esc_html($h[0]); ?></span>
          <span class="badge <?php echo esc_attr($badge_cls); ?>"><?php echo esc_html($badge_lbl); ?></span>
          <span class="hours-time"><?php echo esc_html($h[2]); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="map-mini">
      <iframe src="<?php echo esc_url($map_embed); ?>" loading="lazy" title="Aurix International" referrerpolicy="no-referrer-when-downgrade"></iframe>
      <div class="map-footer">
        <span><?php echo esc_html($map_label); ?></span>
        <a href="<?php echo esc_url($map_link); ?>" target="_blank" rel="noopener">Directions <svg viewBox="0 0 12 12"><path d="M7 2l3 3-3 3M10 5H2"/></svg></a>
      </div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-header">
        <svg viewBox="0 0 16 16"><path d="M12 2a2 2 0 100 4 2 2 0 000-4zM4 7a2 2 0 100 4A2 2 0 004 7zm8 5a2 2 0 100 4 2 2 0 000-4z"/><path d="M5.8 8.7l4.4 2.1M10.2 5.2L5.8 7.3"/></svg>
        <span>Follow Us</span>
      </div>
      <div class="social-bar">
        <a href="<?php echo esc_url($social_li); ?>" class="social-btn" aria-label="LinkedIn" target="_blank" rel="noopener"><svg viewBox="0 0 16 16"><path d="M11 5a4 4 0 014 4v5h-3V9a1 1 0 00-2 0v5H7V9a4 4 0 014-4z"/><rect x="1" y="6" width="3" height="8"/><circle cx="2.5" cy="3" r="1.5"/></svg><span>LinkedIn</span></a>
        <a href="<?php echo esc_url($social_fb); ?>" class="social-btn" aria-label="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M22 12.07C22 6.48 17.52 2 12 2S2 6.48 2 12.07c0 5.05 3.66 9.24 8.44 10v-7.07H7.9v-2.93h2.54V9.84c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.19 2.23.19v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.93h-2.34V22.07C18.34 21.31 22 17.12 22 12.07z"/></svg><span>Facebook</span></a>
        <a href="<?php echo esc_url($social_ig); ?>" class="social-btn" aria-label="Instagram" target="_blank" rel="noopener"><svg viewBox="0 0 16 16"><rect x="2" y="2" width="12" height="12" rx="3"/><circle cx="8" cy="8" r="2.5"/><circle cx="11.5" cy="4.5" r="0.5" fill="currentColor" stroke="none"/></svg><span>Instagram</span></a>
      </div>
    </div>
  </aside>


  <!-- FORM CARD -->
  <div class="form-card">
    <div class="form-card-header">
      <div class="form-eyebrow"><span></span>New Inquiry<span></span></div>
      <h2>Send Us a Message</h2>
      <p>A specialist will get back to you within 24 business hours.</p>
      <div class="form-steps">
        <div class="f-step active" id="c-sn1"><div class="f-dot">1</div><span>Your Details</span></div>
        <div class="f-line" id="c-fl1"></div>
        <div class="f-step" id="c-sn2"><div class="f-dot">2</div><span>Your Inquiry</span></div>
        <div class="f-line" id="c-fl2"></div>
        <div class="f-step" id="c-sn3"><div class="f-dot">3</div><span>Confirm</span></div>
      </div>
    </div>

    <form id="c-contactForm" novalidate>
      <!-- STEP 1 -->
      <div class="form-body step-panel" id="c-s1">
        <div class="section-title">Personal Information</div>
        <div class="field-row" style="margin-bottom:16px">
          <div class="field"><label>First Name <span class="req"></span></label><input type="text" name="firstName" id="c-firstName" placeholder="John" autocomplete="given-name"/></div>
          <div class="field"><label>Last Name</label><input type="text" name="lastName" id="c-lastName" placeholder="Smith" autocomplete="family-name"/></div>
        </div>
        <div class="field-row" style="margin-bottom:16px">
          <div class="field"><label>Email Address <span class="req"></span></label><input type="email" name="email" id="c-email" placeholder="john@company.com" autocomplete="email"/><div class="field-hint">We'll use this to follow up with you.</div></div>
          <div class="field"><label>Phone Number</label><input type="tel" name="phone" id="c-phone" placeholder="<?php echo esc_attr($phone); ?>" autocomplete="tel"/></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Company / Organization</label><input type="text" name="company" placeholder="Acme Corp Ltd." autocomplete="organization"/></div>
          <div class="field"><label>Your Role</label><input type="text" name="role" placeholder="Director, Manager…" autocomplete="organization-title"/></div>
        </div>
        <div class="btn-row" style="justify-content:flex-end;margin-top:20px">
          <button type="button" class="btn-primary" style="flex:none;padding:13px 28px" onclick="cGoStep(2)">Continue <svg viewBox="0 0 16 16"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button>
        </div>
      </div>

      <!-- STEP 2 -->
      <div class="form-body" id="c-s2" style="display:none">
        <div class="section-title">Inquiry Details</div>
        <div class="field"><label>Type of Inquiry <span class="req"></span></label><select name="type" id="c-inquiryType"><option value="" disabled selected>Select a category…</option><option>Business Inquiry</option><option>Partnership Proposal</option><option>Export / Import Services</option><option>OEM &amp; Private Label</option><option>Custom Kits</option><option>General Question</option><option>Other</option></select></div>
        <div class="field" style="margin-bottom:16px"><label>Estimated Budget</label><div class="budget-grid"><label class="budget-option"><input type="radio" name="budget" value="small"/><span class="budget-label">Under $10K</span></label><label class="budget-option"><input type="radio" name="budget" value="mid"/><span class="budget-label">$10K – $50K</span></label><label class="budget-option"><input type="radio" name="budget" value="large"/><span class="budget-label">$50K+</span></label></div></div>
        <div class="field" style="margin-bottom:16px"><label>Subject <span class="req"></span></label><input type="text" name="subject" id="c-subject" placeholder="Brief subject of your message"/></div>
        <div class="field"><label>Your Message <span class="req"></span></label><textarea name="message" id="c-message" placeholder="Describe your inquiry in detail…" maxlength="1000"></textarea><div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px"><div class="field-hint" style="margin-top:0">Min. 20 characters</div><div class="char-counter" id="c-charCount">0 / 1000</div></div></div>
        <div class="btn-row"><button type="button" class="btn-ghost" onclick="cGoStep(1,'back')">← Back</button><button type="button" class="btn-primary" onclick="cGoStep(3)">Review <svg viewBox="0 0 16 16"><path d="M3 8h10M9 4l4 4-4 4"/></svg></button></div>
      </div>

      <!-- STEP 3 -->
      <div class="form-body" id="c-s3" style="display:none">
        <div class="section-title">Review &amp; Submit</div>
        <div class="review-box" id="c-reviewBox"></div>
        <div class="check-field"><input type="checkbox" id="c-agree"/><span>I agree to Aurix International's <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a> and consent to being contacted.</span></div>
        <div class="check-field"><input type="checkbox" id="c-newsletter"/><span>Subscribe to our newsletter for industry insights.</span></div>
        <div id="c-agreeError" style="display:none;color:#C0392B;font-size:11.5px;margin-bottom:10px">Please agree to the Privacy Policy to submit.</div>
        <div class="btn-row"><button type="button" class="btn-ghost" onclick="cGoStep(2,'back')">← Edit</button><button type="submit" class="btn-primary" id="c-submitBtn">Send Message <svg viewBox="0 0 16 16"><path d="M2 8l12-6-6 12-2-4-4-2z"/></svg></button></div>
      </div>
    </form>

    <div class="success-state" id="c-successState">
      <div class="success-icon"><svg viewBox="0 0 28 28"><path d="M5 14l6 6L23 8"/></svg></div>
      <h3>Message Sent!</h3>
      <p>Thank you for reaching out. Our team will be in touch within 24 business hours.</p>
      <button class="btn-primary" onclick="cResetForm()" style="max-width:188px;margin:0 auto">New Message <svg viewBox="0 0 16 16"><path d="M2 8l12-6-6 12-2-4-4-2z"/></svg></button>
    </div>

    <div class="trust-bar" id="c-trustBar">
      <div class="trust-item"><svg viewBox="0 0 14 14"><path d="M7 1l1.8 3.5L13 5.3l-3 2.9.7 4.1L7 10.2l-3.7 2L4 8.2 1 5.3l4.2-.8z"/></svg>Trusted by 500+ clients</div>
      <div class="trust-item"><svg viewBox="0 0 14 14"><rect x="2" y="5" width="10" height="7" rx="1"/><path d="M5 5V4a2 2 0 014 0v1"/></svg>Secure &amp; confidential</div>
      <div class="trust-item"><svg viewBox="0 0 14 14"><circle cx="7" cy="7" r="5"/><path d="M7 4v3l2 2"/></svg>Replies within 24 hours</div>
    </div>
  </div>

</div>


<!-- FAQ -->
<div class="faq-strip">
  <div class="faq-inner">
    <div class="faq-header">
      <h2><?php echo esc_html($faq_title); ?></h2>
      <p><?php echo esc_html($faq_sub); ?></p>
    </div>
    <div class="faq-grid">
      <?php foreach($faqs as $faq): if(!$faq[0]) continue; ?>
      <div class="faq-card">
        <p class="faq-q"><?php echo esc_html($faq[0]); ?></p>
        <p class="faq-a"><?php echo esc_html($faq[1]); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

</div>

<script>
(function(){
  var gv=function(n){var e=document.querySelector('[name='+n+']');return e?e.value.trim():''};
  var $c=function(id){return document.getElementById(id)};
  var cStep=1;

  function cValidEmail(v){return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)}
  function cSetField(el,state,msg){
    el.classList.remove('valid','invalid');
    var prev=el.parentElement.querySelector('.field-msg');if(prev)prev.remove();
    if(state==='valid'){el.classList.add('valid')}
    else if(state==='invalid'){el.classList.add('invalid');var d=document.createElement('div');d.className='field-msg error';d.innerHTML='<svg viewBox="0 0 12 12"><circle cx="6" cy="6" r="5"/></svg> '+msg;el.parentElement.insertBefore(d,el.nextSibling)}
  }

  var msgArea=$c('c-message'),charCount=$c('c-charCount');
  if(msgArea&&charCount){msgArea.addEventListener('input',function(){var l=msgArea.value.length;charCount.textContent=l+' / 1000';charCount.className='char-counter'+(l>900?' over':l>700?' warn':'');})}

  window.cGoStep=function(n,dir){
    if(n===2){var fn=$c('c-firstName'),em=$c('c-email');var ok=true;if(!fn.value.trim()){cSetField(fn,'invalid','First name is required.');ok=false}if(!em.value.trim()){cSetField(em,'invalid','Email is required.');ok=false}else if(!cValidEmail(em.value.trim())){cSetField(em,'invalid','Please enter a valid email.');ok=false}if(!ok){fn.focus();return}}
    if(n===3){var t=document.querySelector('[name=type]'),s=$c('c-subject'),m=$c('c-message');var ok=true;if(!t.value){cSetField(t,'invalid','Please select an inquiry type.');ok=false}if(!s.value.trim()){cSetField(s,'invalid','Subject is required.');ok=false}if(m.value.trim().length<20){cSetField(m,'invalid','Please write at least 20 characters.');ok=false}if(!ok)return;cBuildReview();}
    $c('c-s'+cStep).style.display='none';
    var arr=$c('c-s'+n);arr.style.display='block';arr.classList.remove('step-panel','back');void arr.offsetWidth;arr.classList.add('step-panel');if(dir==='back')arr.classList.add('back');
    [1,2,3].forEach(function(i){var nav=$c('c-sn'+i);nav.classList.remove('active','done');if(i===n)nav.classList.add('active');if(i<n)nav.classList.add('done');});
    ['1','2'].forEach(function(i){var l=$c('c-fl'+i);if(l)l.classList.toggle('done',parseInt(i)<n);});
    cStep=n;
  };

  function cBuildReview(){
    var bv=document.querySelector('[name=budget]:checked');var bmap={small:'Under $10K',mid:'$10K – $50K',large:'$50K+'};
    var rows=[['Name',gv('firstName')+' '+gv('lastName')],['Email',gv('email')],['Phone',gv('phone')||'—'],['Company',gv('company')||'—'],['Inquiry',gv('type')],['Budget',bv?bmap[bv.value]:'Not specified'],['Subject',gv('subject')],['Message',gv('message').substring(0,100)+(gv('message').length>100?'…':'')]];
    $c('c-reviewBox').innerHTML=rows.map(function(r){return'<div class="review-row"><span class="review-key">'+r[0]+'</span><span class="review-val">'+r[1]+'</span></div>'}).join('');
  }

  $c('c-contactForm').addEventListener('submit',function(e){
    e.preventDefault();
    if(!$c('c-agree').checked){$c('c-agreeError').style.display='block';return;}
    $c('c-agreeError').style.display='none';
    var btn=$c('c-submitBtn');btn.disabled=true;
    btn.innerHTML='Sending… <svg style="width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;animation:spin 0.8s linear infinite;vertical-align:middle" viewBox="0 0 16 16"><circle cx="8" cy="8" r="5" stroke-dasharray="20 12"/></svg>';

    // Submit to WordPress via AJAX or WP mail
    var formData=new FormData(this);
    formData.append('action','aurix_contact_submit');
    formData.append('nonce','<?php echo wp_create_nonce("aurix_contact"); ?>');

    fetch('<?php echo admin_url("admin-ajax.php"); ?>',{method:'POST',body:formData})
      .then(function(r){return r.json();})
      .then(function(res){
        btn.disabled=false;
        btn.innerHTML='Send Message <svg style="width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 16 16"><path d="M2 8l12-6-6 12-2-4-4-2z"/></svg>';
        if(res.success){
          document.querySelector('.aurix-contact-page #c-contactForm').style.display='none';
          $c('c-successState').style.display='flex';
          $c('c-trustBar').style.display='none';
        } else {
          var errMsg=res.data&&res.data.msg?res.data.msg:'Something went wrong. Please try again or email us directly.';
          var errDiv=$c('c-submitError');
          if(!errDiv){errDiv=document.createElement('div');errDiv.id='c-submitError';errDiv.style.cssText='color:#C0392B;font-size:12px;margin-top:10px;padding:10px 14px;background:#FEF2F2;border:1px solid rgba(192,57,43,0.2);border-radius:6px;';$c('c-submitBtn').parentElement.parentElement.appendChild(errDiv);}
          errDiv.textContent=errMsg;
          errDiv.style.display='block';
        }
      })
      .catch(function(){
        btn.disabled=false;
        btn.innerHTML='Send Message <svg style="width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 16 16"><path d="M2 8l12-6-6 12-2-4-4-2z"/></svg>';
        var errDiv=$c('c-submitError');
        if(!errDiv){errDiv=document.createElement('div');errDiv.id='c-submitError';errDiv.style.cssText='color:#C0392B;font-size:12px;margin-top:10px;padding:10px 14px;background:#FEF2F2;border:1px solid rgba(192,57,43,0.2);border-radius:6px;';$c('c-submitBtn').parentElement.parentElement.appendChild(errDiv);}
        errDiv.textContent='Network error. Please try again or contact us directly.';
        errDiv.style.display='block';
      });
  });

  $c('c-agree').addEventListener('change',function(){if($c('c-agree').checked)$c('c-agreeError').style.display='none';});

  window.cResetForm=function(){
    $c('c-contactForm').reset();$c('c-contactForm').style.display='block';
    $c('c-successState').style.display='none';$c('c-trustBar').style.display='flex';
    document.querySelectorAll('.aurix-contact-page .valid,.aurix-contact-page .invalid').forEach(function(el){el.classList.remove('valid','invalid')});
    document.querySelectorAll('.aurix-contact-page .field-msg').forEach(function(el){el.remove()});
    if(charCount){charCount.textContent='0 / 1000';charCount.className='char-counter';}
    var btn=$c('c-submitBtn');btn.disabled=false;btn.innerHTML='Send Message <svg style="width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 16 16"><path d="M2 8l12-6-6 12-2-4-4-2z"/></svg>';
    cStep=1;cGoStep(1);
  };
})();
</script>

<?php get_footer(); ?>
