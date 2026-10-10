<?php
/**
 * Template Name: FAQ
 * Template Post Type: page
 */
defined('ABSPATH') || exit;
get_header();
?>
<style>
.aurix-faq-page *,.aurix-faq-page *::before,.aurix-faq-page *::after{box-sizing:border-box}
.aurix-faq-page{margin:0;padding:0;overflow-x:hidden}
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
.aurix-faq-page{font-family:'Outfit','Inter',system-ui,sans-serif;background:#f8f7f3;-webkit-font-smoothing:antialiased}
:root{--faq-navy:#0d1b2a;--faq-navy2:#162436;--faq-navy3:#1e3a52;--faq-gold:#c9a84c;--faq-gold-l:#f0d080;--faq-gold-d:#a07830;--faq-white:#fff;--faq-off:#f8f7f3;--faq-border:#e8e3d8;--faq-text:#1a1708;--faq-muted:#6b6450;--faq-light:#9baab8;--faq-r:10px;--faq-r2:16px}

.aurix-faq-page .afx-hero{background:var(--faq-navy);padding:4rem 2.5rem 3rem;text-align:center;position:relative;overflow:hidden}
.aurix-faq-page .afx-hero-deco{position:absolute;top:-60px;left:50%;transform:translateX(-50%);width:500px;height:220px;border-radius:50%;border:1px solid rgba(201,168,76,0.08);pointer-events:none}
.aurix-faq-page .afx-eyebrow{font-size:10.5px;letter-spacing:0.16em;text-transform:uppercase;color:var(--faq-gold);font-weight:700;margin-bottom:0.75rem;display:flex;align-items:center;justify-content:center;gap:10px}
.aurix-faq-page .afx-eyebrow::before,.aurix-faq-page .afx-eyebrow::after{content:'';width:28px;height:1px;background:var(--faq-gold-d)}
.aurix-faq-page .afx-hero h1{font-family:'Cormorant Garamond',serif;font-size:2.6rem;color:#fff;margin-bottom:0.6rem;line-height:1.15}
.aurix-faq-page .afx-hero h1 span{color:var(--faq-gold)}
.aurix-faq-page .afx-hero-sub{font-size:0.95rem;color:var(--faq-light);margin-bottom:2.2rem;line-height:1.6}
.aurix-faq-page .afx-search-wrap{max-width:520px;margin:0 auto;position:relative}
.aurix-faq-page .afx-search-wrap svg{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#4a6275;pointer-events:none}
.aurix-faq-page .afx-search-wrap input{width:100%;padding:13px 16px 13px 46px;background:#162436;border:1.5px solid #2a3f56;color:#fff;border-radius:100px;font-size:14px;font-family:'Outfit',sans-serif;outline:none;transition:border-color 0.2s}
.aurix-faq-page .afx-search-wrap input::placeholder{color:#4a6275}
.aurix-faq-page .afx-search-wrap input:focus{border-color:var(--faq-gold)}
.aurix-faq-page .afx-stats{display:grid;grid-template-columns:repeat(3,1fr);background:var(--faq-navy2);border-top:1px solid rgba(201,168,76,0.2)}
.aurix-faq-page .afx-stat{padding:1.1rem 1.5rem;text-align:center;border-right:1px solid rgba(201,168,76,0.1)}
.aurix-faq-page .afx-stat:last-child{border-right:none}
.aurix-faq-page .afx-stat-n{font-size:1.4rem;font-weight:700;color:var(--faq-gold);line-height:1}
.aurix-faq-page .afx-stat-l{font-size:11px;color:var(--faq-light);margin-top:3px;letter-spacing:0.04em}
.aurix-faq-page .afx-tabs{padding:1.4rem 2.5rem;background:var(--faq-off);border-bottom:1.5px solid var(--faq-border);display:flex;flex-wrap:wrap;gap:8px;align-items:center;position:sticky;top:0;z-index:10;backdrop-filter:blur(8px)}
.aurix-faq-page .afx-tab{font-size:12.5px;padding:7px 16px;border:1.5px solid var(--faq-border);background:#fff;color:var(--faq-muted);border-radius:100px;cursor:pointer;transition:all 0.2s;font-weight:500;font-family:'Outfit',sans-serif;white-space:nowrap}
.aurix-faq-page .afx-tab:hover{border-color:#c9a84c55;color:var(--faq-gold-d)}
.aurix-faq-page .afx-tab.active{background:var(--faq-navy);color:var(--faq-gold);border-color:var(--faq-navy);font-weight:600}
.aurix-faq-page .afx-count{font-size:10px;background:rgba(201,168,76,0.18);color:var(--faq-gold-d);border-radius:100px;padding:1px 6px;margin-left:4px;font-weight:700}
.aurix-faq-page .afx-body{padding:2rem 2.5rem 3rem;background:#fff}
.aurix-faq-page .afx-sec{margin-bottom:0.5rem}
.aurix-faq-page .afx-sec-head{display:flex;align-items:center;gap:12px;margin-bottom:1rem;margin-top:2rem}
.aurix-faq-page .afx-sec-icon{width:34px;height:34px;border-radius:var(--faq-r);background:rgba(201,168,76,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.aurix-faq-page .afx-sec-icon svg{color:var(--faq-gold);width:17px;height:17px}
.aurix-faq-page .afx-sec-title{font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--faq-gold-d)}
.aurix-faq-page .afx-sec-line{flex:1;height:1.5px;background:linear-gradient(90deg,var(--faq-gold) 0%,rgba(201,168,76,0.08) 100%)}
.aurix-faq-page .afx-sec.hidden{display:none}
.aurix-faq-page .afx-item{border:1.5px solid var(--faq-border);border-radius:var(--faq-r);margin-bottom:8px;overflow:hidden;transition:border-color 0.2s,box-shadow 0.2s;background:#fff}
.aurix-faq-page .afx-item:hover{border-color:#c9a84c55}
.aurix-faq-page .afx-item.open{border-color:rgba(201,168,76,0.5);box-shadow:0 2px 16px rgba(201,168,76,0.08)}
.aurix-faq-page .afx-q{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:1rem 1.2rem;cursor:pointer;user-select:none;transition:background 0.15s}
.aurix-faq-page .afx-q:hover{background:var(--faq-off)}
.aurix-faq-page .afx-item.open .afx-q{background:#fffdf5}
.aurix-faq-page .afx-q-text{font-size:14.5px;font-weight:500;color:var(--faq-text);line-height:1.45;flex:1}
.aurix-faq-page .afx-icon-wrap{width:26px;height:26px;border-radius:50%;border:1.5px solid var(--faq-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.25s;background:#fff}
.aurix-faq-page .afx-item.open .afx-icon-wrap{background:var(--faq-gold);border-color:var(--faq-gold)}
.aurix-faq-page .afx-icon-wrap svg{transition:transform 0.3s cubic-bezier(.34,1.56,.64,1);color:var(--faq-muted);width:13px;height:13px}
.aurix-faq-page .afx-item.open .afx-icon-wrap svg{transform:rotate(45deg);color:var(--faq-navy)}
.aurix-faq-page .afx-a-wrap{max-height:0;overflow:hidden;transition:max-height 0.38s cubic-bezier(.4,0,.2,1)}
.aurix-faq-page .afx-item.open .afx-a-wrap{max-height:400px}
.aurix-faq-page .afx-a{padding:0 1.2rem 1.1rem;font-size:14px;color:var(--faq-muted);line-height:1.8;border-top:1px solid var(--faq-border)}
.aurix-faq-page .afx-a p{margin-top:0.8rem}
.aurix-faq-page .afx-noresult{text-align:center;padding:2.5rem 1rem;display:none}
.aurix-faq-page .afx-noresult p{font-size:14px;color:var(--faq-muted)}

/* AI ASSISTANT */
.aurix-faq-page .afx-ai{background:var(--faq-navy);border-radius:var(--faq-r2);margin-top:2.5rem;overflow:hidden}
.aurix-faq-page .afx-ai-head{padding:1.6rem 2rem 0;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}
.aurix-faq-page .afx-ai-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);border-radius:100px;padding:4px 12px 4px 8px;margin-bottom:0.5rem}
.aurix-faq-page .afx-ai-dot{width:7px;height:7px;border-radius:50%;background:var(--faq-gold);animation:afx-pulse 2s infinite}
@keyframes afx-pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.6;transform:scale(0.85)}}
.aurix-faq-page .afx-ai-badge span{font-size:11px;font-weight:600;color:var(--faq-gold);letter-spacing:0.06em}
.aurix-faq-page .afx-ai-title{font-family:'Cormorant Garamond',serif;font-size:1.35rem;color:#fff;margin-bottom:0.3rem}
.aurix-faq-page .afx-ai-sub{font-size:12.5px;color:var(--faq-light);line-height:1.5}
.aurix-faq-page .afx-chat-area{margin:1.2rem;background:var(--faq-navy2);border-radius:var(--faq-r);padding:1rem;max-height:280px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;scrollbar-width:thin;scrollbar-color:#2a3f56 transparent}
.aurix-faq-page .afx-msg{display:flex;gap:9px;align-items:flex-start;animation:afx-msgIn 0.35s cubic-bezier(.22,1,.36,1)}
@keyframes afx-msgIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.aurix-faq-page .afx-av{width:30px;height:30px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;letter-spacing:0.02em}
.aurix-faq-page .afx-msg.user .afx-av{background:var(--faq-gold);color:var(--faq-navy)}
.aurix-faq-page .afx-msg.bot .afx-av{background:var(--faq-navy3);color:var(--faq-gold);font-size:10px}
.aurix-faq-page .afx-bubble{font-size:13.5px;line-height:1.65;padding:0.65rem 0.9rem;border-radius:var(--faq-r);max-width:88%}
.aurix-faq-page .afx-msg.user .afx-bubble{background:var(--faq-navy3);color:#c8d8e8;border-radius:var(--faq-r) var(--faq-r) 4px var(--faq-r)}
.aurix-faq-page .afx-msg.bot .afx-bubble{background:#0d2235;color:#9baab8;border-radius:var(--faq-r) var(--faq-r) var(--faq-r) 4px}
.aurix-faq-page .afx-bubble.thinking{color:#4a6275;font-style:italic;display:flex;align-items:center;gap:6px}
.aurix-faq-page .dots span{display:inline-block;width:5px;height:5px;border-radius:50%;background:#4a6275;animation:afx-dot 1.4s infinite}
.aurix-faq-page .dots span:nth-child(2){animation-delay:.2s}
.aurix-faq-page .dots span:nth-child(3){animation-delay:.4s}
@keyframes afx-dot{0%,80%,100%{transform:scale(0.6);opacity:0.4}40%{transform:scale(1);opacity:1}}
.aurix-faq-page .afx-sugs{padding:0 1.2rem 0.8rem;display:flex;flex-wrap:wrap;gap:6px}
.aurix-faq-page .afx-sug{font-size:12px;padding:6px 13px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:var(--faq-light);border-radius:100px;cursor:pointer;transition:all 0.18s;font-family:'Outfit',sans-serif}
.aurix-faq-page .afx-sug:hover{background:rgba(201,168,76,0.12);border-color:rgba(201,168,76,0.35);color:var(--faq-gold)}
.aurix-faq-page .afx-input-row{display:flex;gap:8px;padding:0.8rem 1.2rem 1.4rem;align-items:center}
.aurix-faq-page .afx-input-wrap{flex:1;position:relative}
.aurix-faq-page .afx-input-wrap input{width:100%;padding:11px 44px 11px 16px;background:#162436;border:1.5px solid #2a3f56;color:#fff;border-radius:100px;font-size:13.5px;font-family:'Outfit',sans-serif;outline:none;transition:border-color 0.2s}
.aurix-faq-page .afx-input-wrap input:focus{border-color:var(--faq-gold)}
.aurix-faq-page .afx-input-wrap input::placeholder{color:#4a6275}
.aurix-faq-page .afx-send{width:40px;height:40px;background:var(--faq-gold);border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background 0.15s,transform 0.15s}
.aurix-faq-page .afx-send:hover{background:var(--faq-gold-l);transform:scale(1.06)}
.aurix-faq-page .afx-send:disabled{opacity:0.45;cursor:default;transform:none}
.aurix-faq-page .afx-send svg{color:var(--faq-navy);width:16px;height:16px}

@media(max-width:600px){
  .aurix-faq-page .afx-hero{padding:3rem 1.2rem 2.2rem}
  .aurix-faq-page .afx-hero h1{font-size:1.9rem}
  .aurix-faq-page .afx-tabs,.aurix-faq-page .afx-body{padding-left:1rem;padding-right:1rem}
}
</style>

<div class="aurix-faq-page">

  <div class="afx-hero">
    <div class="afx-hero-deco"></div>
    <div class="afx-eyebrow">Support Center</div>
    <h1>Frequently Asked <span>Questions</span></h1>
    <p class="afx-hero-sub">Find answers about ordering, instruments, shipping, custom kits and more.</p>
    <div class="afx-search-wrap">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" id="afx-search" placeholder="Search all questions…" oninput="afxSearch(this.value)" autocomplete="off"/>
    </div>
  </div>

  <div class="afx-stats">
    <div class="afx-stat"><div class="afx-stat-n">17</div><div class="afx-stat-l">Questions answered</div></div>
    <div class="afx-stat"><div class="afx-stat-n">5</div><div class="afx-stat-l">Topic categories</div></div>
    <div class="afx-stat"><div class="afx-stat-n">AI</div><div class="afx-stat-l">Instant answers</div></div>
  </div>

  <div class="afx-tabs">
    <button class="afx-tab active" onclick="afxCat('all',this)">All <span class="afx-count">17</span></button>
    <button class="afx-tab" onclick="afxCat('ordering',this)">Ordering &amp; Payment <span class="afx-count">4</span></button>
    <button class="afx-tab" onclick="afxCat('instruments',this)">Instruments <span class="afx-count">5</span></button>
    <button class="afx-tab" onclick="afxCat('custom',this)">Custom Sets <span class="afx-count">2</span></button>
    <button class="afx-tab" onclick="afxCat('shipping',this)">Shipping <span class="afx-count">3</span></button>
    <button class="afx-tab" onclick="afxCat('account',this)">Account &amp; Returns <span class="afx-count">3</span></button>
  </div>

  <div class="afx-body">

    <div class="afx-sec" data-cat="ordering">
      <div class="afx-sec-head"><div class="afx-sec-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 7h13"/><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/></svg></div><span class="afx-sec-title">Ordering &amp; Payment</span><div class="afx-sec-line"></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Who do you serve?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">We primarily serve dental clinics, private practices, dental students, and distributors. Our instruments are designed for everyday clinical use, education, and professional surgical procedures.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Do I need to pay before my order ships?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. All standard orders are processed on a prepaid basis — full payment is required at checkout. Once payment is received, your order is confirmed and sent for processing within 24–72 hours.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Do you offer bulk or wholesale pricing?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. We offer custom pricing for bulk and distributor orders. These require an 80% advance payment to begin processing, with the remaining 20% balance due before shipment. Contact us for a formal quotation.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Is my payment information safe?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. All transactions are securely processed and payment data is not stored after completion. No information is shared with third parties.</div></div></div>
    </div>

    <div class="afx-sec" data-cat="instruments">
      <div class="afx-sec-head"><div class="afx-sec-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14.5 2.5c0 1.5-1.5 4-2.5 5.5-1-1.5-2.5-4-2.5-5.5a2.5 2.5 0 0 1 5 0z"/><path d="M12 8v13M8 21h8"/></svg></div><span class="afx-sec-title">Instruments</span><div class="afx-sec-line"></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">What material are your instruments made from?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">All instruments are manufactured from high-quality surgical-grade stainless steel, engineered for durability, corrosion resistance, and long-term clinical performance.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Are your instruments autoclave safe?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. All Aurix stainless steel instruments are fully autoclavable and engineered to withstand repeated sterilization cycles under standard dental and surgical autoclave conditions.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Are instruments sterilized on delivery?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Instruments are delivered non-sterile and must be properly sterilized before clinical use, following standard dental or surgical protocols at your facility.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Do you sell instruments individually or only in sets?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Both. We offer individual instruments, pre-designed clinical kits, and fully custom instrument sets — so you can purchase exactly what you need.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">What if an item is out of stock?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">We will notify you before processing your order and offer alternative options or updated delivery timelines. You will never be charged for an unavailable item without prior notice.</div></div></div>
    </div>

    <div class="afx-sec" data-cat="custom">
      <div class="afx-sec-head"><div class="afx-sec-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></div><span class="afx-sec-title">Custom Sets</span><div class="afx-sec-line"></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Can I build my own custom instrument set?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. Select your required instruments and submit a request via our website. We review your selection, send a formal quotation by email, and begin production after confirmation and payment.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Why do custom sets take longer to deliver?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Custom orders may need extra time due to instrument availability, production scheduling, and assembly requirements. We work as efficiently as possible without compromising quality.</div></div></div>
    </div>

    <div class="afx-sec" data-cat="shipping">
      <div class="afx-sec-head"><div class="afx-sec-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div><span class="afx-sec-title">Shipping &amp; Delivery</span><div class="afx-sec-line"></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">How fast will I receive my order?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Standard orders are processed and shipped within 24–72 hours after payment confirmation. Orders are not processed on weekends or public holidays. Delivery times vary by location.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Do you ship internationally?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. We ship worldwide via UPS, FedEx, and USPS. International shipping costs are confirmed before dispatch, and delivery timelines may vary due to customs or logistics factors.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Can I track my order?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Yes. Once your order ships you will receive a tracking number to monitor your shipment in real time via the Track Order page on our website.</div></div></div>
    </div>

    <div class="afx-sec" data-cat="account">
      <div class="afx-sec-head"><div class="afx-sec-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div><span class="afx-sec-title">Account &amp; Returns</span><div class="afx-sec-line"></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Do I need an account to place an order?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">An account may be required to place orders and track purchases. It enables faster checkout, better order management, and access to your full order history.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">Do you accept returns or offer replacements?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">We offer replacements or repairs for confirmed manufacturing defects only. Each claim is individually reviewed by our quality control team. Warranty coverage varies by product type and usage conditions.</div></div></div>
      <div class="afx-item"><div class="afx-q" onclick="afxOpen(this)"><span class="afx-q-text">What if I have a complaint or unresolved issue?</span><div class="afx-icon-wrap"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div></div><div class="afx-a-wrap"><div class="afx-a">Please reach out to <?php echo esc_html(get_theme_mod('aurix_email','info@aurixinternational.com')); ?> with your order details and our team will respond promptly.</div></div></div>
    </div>

    <div id="afx-noresult" class="afx-noresult"><p><strong>No results found.</strong><br>Try a different keyword or ask our AI assistant below.</p></div>

    <!-- AI ASSISTANT -->
    <div class="afx-ai">
      <div class="afx-ai-head">
        <div>
          <div class="afx-ai-badge"><div class="afx-ai-dot"></div><span>Live AI Assistant</span></div>
          <div class="afx-ai-title">Ask us anything</div>
          <div class="afx-ai-sub">Powered by AI — answers about products, orders, OEM, shipping &amp; more.</div>
        </div>
      </div>
      <div class="afx-chat-area" id="afx-chat">
        <div class="afx-msg bot"><div class="afx-av">AI</div><div class="afx-bubble">Hi there! I'm the Aurix assistant. Ask me anything about our surgical &amp; dental instruments, ordering process, custom kits, shipping, or OEM services.</div></div>
      </div>
      <div class="afx-sugs">
        <button class="afx-sug" onclick="afxPreset('Do you offer OEM or private label services?')">OEM &amp; Private Label</button>
        <button class="afx-sug" onclick="afxPreset('What is the minimum order for bulk pricing?')">Bulk order minimum</button>
        <button class="afx-sug" onclick="afxPreset('How do I request a quote for a custom kit?')">Custom kit quote</button>
        <button class="afx-sug" onclick="afxPreset('What carriers do you use for international shipping?')">International shipping</button>
        <button class="afx-sug" onclick="afxPreset('How long does a custom set take to build?')">Custom set timeline</button>
      </div>
      <div class="afx-input-row">
        <div class="afx-input-wrap"><input type="text" id="afx-input" placeholder="Type your question here…" onkeydown="if(event.key==='Enter')afxSend()" autocomplete="off"/></div>
        <button class="afx-send" id="afx-btn" onclick="afxSend()" aria-label="Send"><svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 2 11 13M22 2 15 22l-4-9-9-4 20-7z"/></svg></button>
      </div>
    </div>

  </div>
</div>

<script>
var afxHist=[];
var AFX_SYS="You are the friendly, professional customer support assistant for Aurix International — a USA-based supplier of high-quality surgical and dental instruments. Keep answers concise (2-4 sentences). Only assist with Aurix-related questions. For anything outside scope, politely redirect to <?php echo esc_js(get_theme_mod('aurix_email','info@aurixinternational.com')); ?>.\n\nKey facts:\n- Serves dental clinics, private practices, students, distributors\n- Standard orders: prepaid, 24-72hr processing, worldwide via UPS/FedEx/USPS\n- Bulk orders: 80% advance, 20% before shipment\n- Custom sets: submit request online, quotation by email, production after payment\n- Instruments: surgical-grade stainless steel, autoclavable, delivered non-sterile\n- OEM and private label available\n- Returns for manufacturing defects only\n- Contact: <?php echo esc_js(get_theme_mod('aurix_email','info@aurixinternational.com')); ?> | <?php echo esc_js(get_theme_mod('aurix_phone','+1 (234) 567-8900')); ?>";

function afxOpen(el){var item=el.closest('.afx-item');var was=item.classList.contains('open');document.querySelectorAll('.afx-item.open').forEach(function(i){i.classList.remove('open')});if(!was)item.classList.add('open')}
function afxCat(cat,btn){document.querySelectorAll('.afx-tab').forEach(function(b){b.classList.remove('active')});btn.classList.add('active');document.getElementById('afx-search').value='';document.querySelectorAll('.afx-sec[data-cat]').forEach(function(s){s.classList.toggle('hidden',cat!=='all'&&s.dataset.cat!==cat);s.querySelectorAll('.afx-item').forEach(function(i){i.style.display=''})});document.getElementById('afx-noresult').style.display='none'}
function afxSearch(v){var q=v.trim().toLowerCase();document.querySelectorAll('.afx-tab').forEach(function(b){b.classList.remove('active')});document.querySelectorAll('.afx-tab')[0].classList.add('active');if(!q){document.querySelectorAll('.afx-sec').forEach(function(s){s.classList.remove('hidden');s.querySelectorAll('.afx-item').forEach(function(i){i.style.display=''})});document.getElementById('afx-noresult').style.display='none';return}var found=0;document.querySelectorAll('.afx-sec').forEach(function(s){s.classList.remove('hidden');var vis=0;s.querySelectorAll('.afx-item').forEach(function(i){if(i.textContent.toLowerCase().includes(q)){i.style.display='';vis++}else{i.style.display='none'}});if(vis===0)s.classList.add('hidden');else found+=vis});document.getElementById('afx-noresult').style.display=found===0?'flex':'none'}
function afxPreset(t){document.getElementById('afx-input').value=t;afxSend()}

async function afxSend(){
  var inp=document.getElementById('afx-input');var q=inp.value.trim();if(!q)return;
  inp.value='';var btn=document.getElementById('afx-btn');btn.disabled=true;
  var chat=document.getElementById('afx-chat');
  var u=document.createElement('div');u.className='afx-msg user';u.innerHTML='<div class="afx-av">U</div><div class="afx-bubble">'+q.replace(/</g,'&lt;')+'</div>';chat.appendChild(u);
  var b=document.createElement('div');b.className='afx-msg bot';b.innerHTML='<div class="afx-av">AI</div><div class="afx-bubble thinking"><span class="dots"><span></span><span></span><span></span></span></div>';chat.appendChild(b);
  chat.scrollTop=chat.scrollHeight;
  afxHist.push({role:'user',content:q});
  try{
    var r=await fetch('https://api.anthropic.com/v1/messages',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({model:'claude-sonnet-4-20250514',max_tokens:1000,system:AFX_SYS,messages:afxHist})});
    var d=await r.json();
    var ans=(d.content&&d.content[0]&&d.content[0].text)||'Sorry, please email <?php echo esc_js(get_theme_mod("aurix_email","info@aurixinternational.com")); ?> for help.';
    afxHist.push({role:'assistant',content:ans});
    b.querySelector('.afx-bubble').className='afx-bubble';b.querySelector('.afx-bubble').textContent=ans;
  }catch(e){b.querySelector('.afx-bubble').className='afx-bubble';b.querySelector('.afx-bubble').textContent='Something went wrong. Please email <?php echo esc_js(get_theme_mod("aurix_email","info@aurixinternational.com")); ?>.'}
  btn.disabled=false;chat.scrollTop=chat.scrollHeight;
}
</script>

<?php get_footer(); ?>
