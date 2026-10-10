<?php
/**
 * Template Name: About Us
 * Template Post Type: page
 */
defined('ABSPATH') || exit;
get_header();
?>

<style>
/* Reset conflicts with Aurix theme */
.aurix-about-page *,
.aurix-about-page *::before,
.aurix-about-page *::after { box-sizing:border-box; }
.aurix-about-page { margin:0; padding:0; overflow-x:hidden; }

:root{
  --navy:#111827; --navy-2:#1e2b42; --navy-3:#263452;
  --gold:#c9921a; --gold-2:#e0a820; --gold-lt:#faf0db; --gold-mid:#ddb96a;
  --white:#ffffff; --off:#f9f8f5; --surface:#ffffff;
  --text:#475569; --text-2:#1e293b; --border:#e5e7eb; --border-2:#d1d5db;
  --radius-sm:8px; --radius:14px; --radius-lg:22px; --radius-xl:32px;
  --shadow-sm:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
  --shadow:0 4px 20px rgba(0,0,0,.07),0 1px 4px rgba(0,0,0,.04);
  --shadow-lg:0 16px 48px rgba(0,0,0,.10),0 4px 12px rgba(0,0,0,.05);
  --shadow-xl:0 32px 80px rgba(0,0,0,.14),0 8px 24px rgba(0,0,0,.07);
}
.aurix-about-page { font-family:'Outfit','Plus Jakarta Sans',system-ui,sans-serif; background:var(--off); color:var(--text); line-height:1.7; }
.aurix-about-page a { text-decoration:none; }
.aurix-about-page .wrap { width:100%; max-width:1200px; margin:0 auto; padding:0 clamp(18px,5vw,52px); }
.aurix-about-page .sr { opacity:0; transform:translateY(32px); transition:opacity .75s cubic-bezier(.22,1,.36,1),transform .75s cubic-bezier(.22,1,.36,1); }
.aurix-about-page .sr.show { opacity:1; transform:none; }
.aurix-about-page .dl1{transition-delay:.06s} .aurix-about-page .dl2{transition-delay:.12s} .aurix-about-page .dl3{transition-delay:.18s} .aurix-about-page .dl4{transition-delay:.24s}
.aurix-about-page .eyebrow { display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:var(--gold);margin-bottom:16px; }
.aurix-about-page .eyebrow-line { width:28px;height:1.5px;background:var(--gold);border-radius:2px; }
.aurix-about-page .eyebrow-inv { color:rgba(255,255,255,.55); }
.aurix-about-page .eyebrow-inv .eyebrow-line { background:rgba(255,255,255,.35); }
.aurix-about-page .display { font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(36px,5.5vw,68px);font-weight:600;line-height:1.03;letter-spacing:-.5px;color:var(--navy); }
.aurix-about-page .display em { font-style:italic;color:var(--gold); }
.aurix-about-page .display-inv { color:#fff; }
.aurix-about-page .display-inv em { color:var(--gold-2); }
.aurix-about-page .h2 { font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(28px,4vw,46px);font-weight:600;line-height:1.08;letter-spacing:-.3px;color:var(--navy); }
.aurix-about-page .h2 em { font-style:italic;color:var(--gold); }
.aurix-about-page .h3 { font-family:'Cormorant Garamond',Georgia,serif;font-size:clamp(20px,2.5vw,26px);font-weight:600;line-height:1.15;letter-spacing:-.2px;color:var(--navy); }
.aurix-about-page .body-lg { font-size:clamp(14.5px,1.8vw,16.5px);line-height:1.85;color:var(--text); }
.aurix-about-page .btn { display:inline-flex;align-items:center;gap:10px;font-family:'Outfit',sans-serif;font-size:14px;font-weight:700;letter-spacing:.1px;padding:14px 26px;border-radius:var(--radius);text-decoration:none;cursor:pointer;border:none;transition:all .25s cubic-bezier(.22,1,.36,1);white-space:nowrap; }
.aurix-about-page .btn svg { flex-shrink:0;transition:transform .25s; }
.aurix-about-page .btn:hover svg { transform:translateX(3px); }
.aurix-about-page .btn-gold { background:var(--gold);color:#fff;box-shadow:0 4px 16px rgba(201,146,26,.35); }
.aurix-about-page .btn-gold:hover { background:#b07e14;box-shadow:0 6px 24px rgba(201,146,26,.45);transform:translateY(-2px); }
.aurix-about-page .btn-navy { background:var(--navy);color:#fff;box-shadow:var(--shadow); }
.aurix-about-page .btn-navy:hover { background:var(--navy-2);transform:translateY(-2px);box-shadow:var(--shadow-lg); }
.aurix-about-page .btn-outline-w { background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.28); }
.aurix-about-page .btn-outline-w:hover { border-color:rgba(255,255,255,.7);background:rgba(255,255,255,.08); }
.aurix-about-page .btn-sm { padding:10px 18px;font-size:13px;border-radius:var(--radius-sm); }
.aurix-about-page .section { padding:clamp(60px,9vw,100px) 0; }
.aurix-about-page .section-w { background:var(--white); }
.aurix-about-page .section-off { background:var(--off); }
.aurix-about-page .divider { height:1px;background:var(--border);margin:0; }

/* ACCESSIBILITY — visible keyboard focus (brand-colored, since default browser
   outline is removed/inconsistent elsewhere on this site) */
.aurix-about-page .btn:focus-visible,
.aurix-about-page .contact-card:focus-visible,
.aurix-about-page .hstat:focus-visible,
.aurix-about-page a:focus-visible {
  outline:2px solid var(--gold);
  outline-offset:3px;
  border-radius:4px;
}
.aurix-about-page .btn-outline-w:focus-visible { outline-color:#fff; }

/* Respect reduced-motion preference: skip scroll-reveal + pulse/hover animations */
@media (prefers-reduced-motion: reduce) {
  .aurix-about-page .sr { opacity:1; transform:none; transition:none; }
  .aurix-about-page .hero-badge-dot span { animation:none; }
  .aurix-about-page *, .aurix-about-page *::before, .aurix-about-page *::after {
    transition-duration:.01ms !important;
    animation-duration:.01ms !important;
  }
}

/* HERO */
.aurix-about-page .hero { background:var(--navy);position:relative;overflow:hidden;padding:clamp(72px,11vw,120px) 0 0; }
.aurix-about-page .hero::before { content:'';position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 100% 40%,rgba(201,146,26,.13) 0%,transparent 60%),radial-gradient(ellipse 50% 50% at 0% 100%,rgba(201,146,26,.08) 0%,transparent 60%); }
.aurix-about-page .hero::after { content:'';position:absolute;inset:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse 70% 70% at 50% 50%,black 20%,transparent 100%); }
.aurix-about-page .hero-inner { display:grid;grid-template-columns:1fr 1fr;gap:clamp(32px,5vw,80px);align-items:end;position:relative;z-index:1; }
.aurix-about-page .hero-left { padding-bottom:clamp(48px,8vw,80px); }
.aurix-about-page .hero-badge { display:inline-flex;align-items:center;gap:8px;background:rgba(201,146,26,.12);border:1px solid rgba(201,146,26,.3);border-radius:100px;padding:6px 14px 6px 8px;margin-bottom:24px; }
.aurix-about-page .hero-badge-dot { width:26px;height:26px;border-radius:100px;background:rgba(201,146,26,.2);display:flex;align-items:center;justify-content:center; }
.aurix-about-page .hero-badge-dot span { width:8px;height:8px;border-radius:50%;background:var(--gold);animation:hpulse 2.2s ease-in-out infinite;display:block; }
@keyframes hpulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.6);opacity:.5}}
.aurix-about-page .hero-badge-text { font-size:11.5px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:rgba(255,255,255,.75); }
.aurix-about-page .hero-title { margin-bottom:22px; }
.aurix-about-page .hero-desc { font-size:clamp(14.5px,1.8vw,16.5px);line-height:1.85;color:rgba(255,255,255,.55);max-width:500px;margin-bottom:36px; }
.aurix-about-page .hero-desc strong { color:rgba(255,255,255,.9);font-weight:600; }
.aurix-about-page .hero-ctas { display:flex;gap:12px;flex-wrap:wrap; }
.aurix-about-page .hero-right { display:flex;flex-direction:column;gap:0;align-self:end; }
.aurix-about-page .hstat { padding:clamp(18px,3vw,28px) clamp(20px,3vw,32px);border-top:1px solid rgba(255,255,255,.08);position:relative;transition:background .25s; }
.aurix-about-page .hstat:hover { background:rgba(255,255,255,.04); }
.aurix-about-page .hstat::before { content:'';position:absolute;top:0;left:0;width:2px;height:0;background:var(--gold);transition:height .4s cubic-bezier(.22,1,.36,1); }
.aurix-about-page .hstat:hover::before { height:100%; }
.aurix-about-page .hstat-num { font-family:'Cormorant Garamond',serif;font-size:clamp(32px,4vw,48px);font-weight:600;color:var(--gold);line-height:1;margin-bottom:4px;letter-spacing:-1px; }
.aurix-about-page .hstat-label { font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.4); }

/* TRUST BAR */
.aurix-about-page .trust-bar { background:rgba(255,255,255,.03);border-top:1px solid rgba(255,255,255,.07);padding:clamp(16px,3vw,22px) 0;position:relative;z-index:1; }
.aurix-about-page .trust-inner { display:flex;align-items:center;gap:clamp(20px,4vw,48px);flex-wrap:wrap; }
.aurix-about-page .trust-item { display:flex;align-items:center;gap:10px;font-size:12.5px;font-weight:600;color:rgba(255,255,255,.45);letter-spacing:.2px;white-space:nowrap; }
.aurix-about-page .trust-item svg { width:15px;height:15px;stroke:var(--gold);opacity:.8;flex-shrink:0; }
.aurix-about-page .trust-sep { width:1px;height:16px;background:rgba(255,255,255,.1);flex-shrink:0; }

/* ABOUT GRID */
.aurix-about-page .about-grid { display:grid;grid-template-columns:1fr 1.05fr;gap:clamp(32px,7vw,88px);align-items:start; }
.aurix-about-page .about-text p { font-size:clamp(14.5px,1.8vw,16px);line-height:1.9;color:var(--text);margin-bottom:18px; }
.aurix-about-page .about-text p strong { color:var(--text-2);font-weight:700; }
.aurix-about-page .feature-panel { background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow); }
.aurix-about-page .feature-panel-header { background:var(--navy);padding:22px 26px;display:flex;align-items:center;gap:14px; }
.aurix-about-page .fp-icon { width:38px;height:38px;border-radius:10px;background:rgba(201,146,26,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.aurix-about-page .fp-icon svg { width:18px;height:18px;stroke:var(--gold); }
.aurix-about-page .fp-heading { font-size:14px;font-weight:700;color:#fff; }
.aurix-about-page .fp-sub { font-size:11.5px;color:rgba(255,255,255,.45);margin-top:1px; }
.aurix-about-page .fp-list { padding:8px 0; }
.aurix-about-page .fp-item { display:flex;align-items:center;gap:14px;padding:15px 26px;border-bottom:1px solid var(--border);transition:background .2s; }
.aurix-about-page .fp-item:last-child { border-bottom:none; }
.aurix-about-page .fp-item:hover { background:var(--off); }
.aurix-about-page .fp-dot { width:7px;height:7px;border-radius:50%;background:var(--gold);flex-shrink:0;box-shadow:0 0 0 3px var(--gold-lt); }
.aurix-about-page .fp-item-text { font-size:13.5px;font-weight:600;color:var(--text-2);line-height:1.4; }
.aurix-about-page .fp-item-sub { font-size:12px;color:var(--text);margin-top:2px; }

/* STORY / TIMELINE */
.aurix-about-page .story-head { display:grid;grid-template-columns:1fr 1fr;gap:clamp(24px,5vw,64px);align-items:end;margin-bottom:clamp(44px,7vw,72px); }
.aurix-about-page .timeline { display:grid;grid-template-columns:1fr 80px 1fr;position:relative; }
.aurix-about-page .tl-center { display:flex;flex-direction:column;align-items:center;padding-top:6px;gap:0; }
.aurix-about-page .tl-line { width:2px;flex:1;background:linear-gradient(180deg,var(--gold) 0%,rgba(201,146,26,.12) 100%); }
.aurix-about-page .tl-node { width:14px;height:14px;border-radius:50%;background:var(--gold);border:3px solid var(--off);box-shadow:0 0 0 3px var(--gold),0 0 16px rgba(201,146,26,.3);flex-shrink:0;z-index:1;transition:transform .3s; }
.aurix-about-page .tl-col { display:flex;flex-direction:column;gap:0; }
.aurix-about-page .tl-left { padding-right:clamp(16px,3vw,32px);text-align:right; }
.aurix-about-page .tl-right { padding-left:clamp(16px,3vw,32px);padding-top:clamp(60px,8vw,100px); }
.aurix-about-page .tl-item { padding-bottom:clamp(32px,5vw,52px);position:relative; }
.aurix-about-page .tl-item-inner { background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:clamp(20px,3vw,28px);box-shadow:var(--shadow-sm);transition:box-shadow .3s,transform .3s,border-color .3s;cursor:default;text-align:left; }
.aurix-about-page .tl-item-inner:hover { box-shadow:var(--shadow-lg);transform:translateY(-4px);border-color:var(--gold-mid); }
.aurix-about-page .tl-era { display:inline-flex;align-items:center;gap:6px;background:var(--navy);color:var(--gold);font-size:10.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:4px 10px;border-radius:6px;margin-bottom:10px; }
.aurix-about-page .tl-era-dot { width:5px;height:5px;border-radius:50%;background:var(--gold); }
.aurix-about-page .tl-title { font-size:clamp(15px,2vw,17px);font-weight:800;color:var(--navy);margin-bottom:8px;line-height:1.25; }
.aurix-about-page .tl-text { font-size:13.5px;line-height:1.8;color:var(--text); }
.aurix-about-page .tl-left .tl-item-inner::after { content:'';position:absolute;top:28px;right:calc(-1 * clamp(16px,3vw,32px) - 1px);width:clamp(16px,3vw,32px);height:2px;background:linear-gradient(90deg,transparent,var(--gold-mid)); }
.aurix-about-page .tl-right .tl-item-inner::before { content:'';position:absolute;top:28px;left:calc(-1 * clamp(16px,3vw,32px) - 1px);width:clamp(16px,3vw,32px);height:2px;background:linear-gradient(90deg,var(--gold-mid),transparent); }

/* OFFERINGS */
.aurix-about-page .off-head { display:grid;grid-template-columns:1fr 1fr;gap:clamp(24px,5vw,64px);align-items:end;margin-bottom:clamp(36px,6vw,56px); }
.aurix-about-page .offer-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:clamp(28px,5vw,48px); }
.aurix-about-page .offer-card { background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:clamp(18px,3vw,26px) clamp(16px,2.5vw,22px);display:flex;flex-direction:column;gap:14px;position:relative;overflow:hidden;transition:all .3s cubic-bezier(.22,1,.36,1);cursor:default; }
.aurix-about-page .offer-card::after { content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--gold-2));transform:scaleX(0);transform-origin:left;transition:transform .35s; }
.aurix-about-page .offer-card:hover { transform:translateY(-6px);box-shadow:var(--shadow-xl);border-color:rgba(201,146,26,.3); }
.aurix-about-page .offer-card:hover::after { transform:scaleX(1); }
.aurix-about-page .offer-num { font-family:'Cormorant Garamond',serif;font-size:38px;font-weight:600;color:var(--border);line-height:1;position:absolute;top:16px;right:18px;transition:color .3s;pointer-events:none; }
.aurix-about-page .offer-card:hover .offer-num { color:var(--gold-lt); }
.aurix-about-page .offer-icon { width:48px;height:48px;border-radius:12px;background:var(--off);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;transition:all .3s; }
.aurix-about-page .offer-card:hover .offer-icon { background:var(--navy);border-color:var(--navy); }
.aurix-about-page .offer-icon svg { width:22px;height:22px;stroke:var(--text);transition:stroke .3s; }
.aurix-about-page .offer-card:hover .offer-icon svg { stroke:var(--gold); }
.aurix-about-page .offer-title { font-size:14.5px;font-weight:800;color:var(--navy);line-height:1.3;margin-top:4px; }
.aurix-about-page .offer-desc { font-size:12.5px;line-height:1.7;color:var(--text);flex:1; }

/* KIT BANNER */
.aurix-about-page .kit-banner { position:relative;overflow:hidden;border-radius:var(--radius-xl);background:var(--navy);padding:clamp(32px,5vw,56px) clamp(24px,5vw,60px);display:grid;grid-template-columns:1fr auto;gap:clamp(24px,4vw,48px);align-items:center; }
.aurix-about-page .kit-banner::before { content:'';position:absolute;top:-100px;right:-100px;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(201,146,26,.18) 0%,transparent 65%);pointer-events:none; }
.aurix-about-page .kit-eyebrow { display:inline-flex;align-items:center;gap:7px;font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--gold);background:rgba(201,146,26,.15);border:1px solid rgba(201,146,26,.3);border-radius:100px;padding:5px 12px 5px 8px;margin-bottom:14px; }
.aurix-about-page .kit-banner h3 { font-family:'Cormorant Garamond',serif;font-size:clamp(22px,3.5vw,36px);font-weight:600;color:#fff;line-height:1.1;margin-bottom:14px; }
.aurix-about-page .kit-banner h3 em { font-style:italic;color:var(--gold-2); }
.aurix-about-page .kit-banner p { font-size:clamp(13px,1.8vw,15px);line-height:1.8;color:rgba(255,255,255,.55);max-width:520px; }
.aurix-about-page .kit-chips { display:flex;flex-wrap:wrap;gap:8px;margin-top:18px; }
.aurix-about-page .kit-chip { display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:100px;padding:5px 13px;font-size:12px;font-weight:600;color:rgba(255,255,255,.7);transition:background .2s; }
.aurix-about-page .kit-chip svg { width:11px;height:11px;stroke:var(--gold);flex-shrink:0; }
.aurix-about-page .kit-right { display:flex;flex-direction:column;gap:12px;align-items:flex-end;flex-shrink:0; }

/* MVV */
.aurix-about-page .mvv-head { margin-bottom:clamp(36px,6vw,56px);text-align:center; }
.aurix-about-page .mvv-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:clamp(14px,2vw,22px); }
.aurix-about-page .mvv-card { background:var(--white);border:1px solid var(--border);border-radius:var(--radius-xl);overflow:hidden;display:flex;flex-direction:column;box-shadow:var(--shadow-sm);transition:all .35s cubic-bezier(.22,1,.36,1);cursor:default; }
.aurix-about-page .mvv-card:hover { transform:translateY(-10px);box-shadow:var(--shadow-xl);border-color:rgba(201,146,26,.25); }
.aurix-about-page .mvv-top { background:var(--navy);padding:clamp(24px,3.5vw,34px) clamp(22px,3vw,30px);position:relative;overflow:hidden; }
.aurix-about-page .mvv-top::after { content:'';position:absolute;right:-30px;bottom:-30px;width:120px;height:120px;border-radius:50%;background:rgba(201,146,26,.08);pointer-events:none; }
.aurix-about-page .mvv-icon { width:54px;height:54px;border-radius:15px;background:rgba(201,146,26,.15);border:1px solid rgba(201,146,26,.3);display:flex;align-items:center;justify-content:center;margin-bottom:18px;transition:transform .35s cubic-bezier(.22,1,.36,1); }
.aurix-about-page .mvv-card:hover .mvv-icon { transform:scale(1.08) rotate(-6deg); }
.aurix-about-page .mvv-icon svg { width:24px;height:24px;stroke:var(--gold);fill:none; }
.aurix-about-page .mvv-tag { font-size:10px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:6px; }
.aurix-about-page .mvv-title { font-family:'Cormorant Garamond',serif;font-size:clamp(22px,3vw,28px);font-weight:600;color:#fff;letter-spacing:-.2px; }
.aurix-about-page .mvv-body { padding:clamp(20px,3vw,28px);flex:1;display:flex;flex-direction:column;gap:16px; }
.aurix-about-page .mvv-body p { font-size:13.5px;line-height:1.8;color:var(--text); }
.aurix-about-page .mvv-sep { height:1px;background:var(--border); }
.aurix-about-page .mvv-list { list-style:none;display:flex;flex-direction:column;gap:10px; }
.aurix-about-page .mvv-li { display:flex;align-items:flex-start;gap:11px;font-size:13px;font-weight:600;color:var(--text-2);line-height:1.5;padding:10px 12px;border-radius:var(--radius-sm);transition:background .2s; }
.aurix-about-page .mvv-li:hover { background:var(--off); }
.aurix-about-page .mvv-li-dot { width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0;margin-top:5px;box-shadow:0 0 0 2px var(--gold-lt); }

/* CTA */
.aurix-about-page .cta-section { background:var(--navy);padding:clamp(64px,9vw,100px) 0;text-align:center;position:relative;overflow:hidden; }
.aurix-about-page .cta-section::before { content:'';position:absolute;inset:0;pointer-events:none;background:radial-gradient(ellipse 60% 50% at 50% 100%,rgba(201,146,26,.12) 0%,transparent 65%); }
.aurix-about-page .cta-inner { position:relative;z-index:1;max-width:580px;margin:0 auto; }
.aurix-about-page .cta-section p { font-size:clamp(14px,1.8vw,16px);color:rgba(255,255,255,.5);line-height:1.8;margin:16px auto 36px; }
.aurix-about-page .cta-btns { display:flex;justify-content:center;gap:12px;flex-wrap:wrap; }
.aurix-about-page .contact-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:clamp(36px,5vw,56px); }
.aurix-about-page .contact-card { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-lg);padding:24px 22px;display:flex;align-items:center;gap:14px;transition:background .25s,border-color .25s;text-decoration:none; }
.aurix-about-page .contact-card:hover { background:rgba(255,255,255,.09);border-color:rgba(201,146,26,.35); }
.aurix-about-page .contact-card-icon { width:42px;height:42px;border-radius:11px;background:rgba(201,146,26,.15);border:1px solid rgba(201,146,26,.25);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.aurix-about-page .contact-card-icon svg { width:18px;height:18px;stroke:var(--gold); }
.aurix-about-page .contact-card-label { font-size:11px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:3px; }
.aurix-about-page .contact-card-value { font-size:14px;font-weight:700;color:#fff;line-height:1.3; }

/* RESPONSIVE */
@media(max-width:860px){
  .aurix-about-page .hero-inner { grid-template-columns:1fr; }
  .aurix-about-page .hero-right { display:grid;grid-template-columns:repeat(2,1fr);gap:12px;padding-bottom:clamp(40px,6vw,64px); }
  .aurix-about-page .hstat { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:var(--radius);padding:20px 18px; }
  .aurix-about-page .hstat::before { display:none; }
  .aurix-about-page .about-grid,.aurix-about-page .story-head,.aurix-about-page .off-head { grid-template-columns:1fr; }
  .aurix-about-page .mvv-grid { grid-template-columns:1fr;max-width:600px;margin:0 auto; }
  .aurix-about-page .contact-grid { grid-template-columns:1fr; }
  .aurix-about-page .timeline { grid-template-columns:28px 1fr; }
  .aurix-about-page .tl-center { grid-column:1;grid-row:1 / 999; }
  .aurix-about-page .tl-left,.aurix-about-page .tl-right { grid-column:2;text-align:left;padding-left:clamp(16px,4vw,28px);padding-right:0; }
  .aurix-about-page .tl-right { padding-top:0; }
  .aurix-about-page .tl-left .tl-item-inner::after,.aurix-about-page .tl-right .tl-item-inner::before { display:none; }
  .aurix-about-page .kit-banner { grid-template-columns:1fr; }
  .aurix-about-page .kit-right { align-items:flex-start; }
}
@media(max-width:960px){ .aurix-about-page .offer-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px){ .aurix-about-page .offer-grid { grid-template-columns:1fr; } .aurix-about-page .hero-right { grid-template-columns:1fr 1fr; } }
</style>

<div class="aurix-about-page">

<!-- HERO -->
<section class="hero">
  <div class="wrap">
    <div class="hero-inner">
      <div class="hero-left">
        <div class="hero-badge sr">
          <div class="hero-badge-dot"><span></span></div>
          <span class="hero-badge-text">USA-Based · Est. by Abdul Raffay</span>
        </div>
        <div class="display display-inv hero-title sr dl1">About<br><em>Aurix</em><br>International</div>
        <p class="hero-desc sr dl2">Specializing in the supply and distribution of <strong>high-quality surgical and dental instruments</strong> for healthcare professionals, distributors, clinics, and private-label brands worldwide.</p>
        <div class="hero-ctas sr dl3">
          <a href="#story" class="btn btn-gold">Discover Our Story <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 16 16"><path d="M3 8h10M9.5 4.5L13 8l-3.5 3.5"/></svg></a>
          <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn-outline-w">Get in Touch</a>
        </div>
      </div>
      <div class="hero-right sr dl2">
        <div class="hstat"><div class="hstat-num">4+</div><div class="hstat-label">Generations of Heritage</div></div>
        <div class="hstat"><div class="hstat-num">USA</div><div class="hstat-label">Based &amp; Operated</div></div>
        <div class="hstat"><div class="hstat-num">Global</div><div class="hstat-label">Distribution Network</div></div>
        <div class="hstat"><div class="hstat-num">OEM</div><div class="hstat-label">Private Label Ready</div></div>
      </div>
    </div>
  </div>
  <div class="trust-bar">
    <div class="wrap">
      <div class="trust-inner">
        <div class="trust-item"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> ISO-Grade Quality Standards</div>
        <div class="trust-sep"></div>
        <div class="trust-item"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg> Worldwide Export Support</div>
        <div class="trust-sep"></div>
        <div class="trust-item"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg> Custom Branding &amp; OEM</div>
        <div class="trust-sep"></div>
        <div class="trust-item"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> Competitive Value Pricing</div>
      </div>
    </div>
  </div>
</section>

<!-- WHO WE ARE -->
<section class="section section-w">
  <div class="wrap">
    <div class="about-grid">
      <div class="about-text">
        <div class="eyebrow sr"><span class="eyebrow-line"></span>Who We Are</div>
        <h2 class="h2 sr dl1" style="margin-bottom:22px">A Modern Distributor<br>With Deep <em>Manufacturing</em><br>Roots</h2>
        <p class="sr dl2">At <strong>Aurix International</strong>, we specialize in the supply and distribution of high-quality surgical and dental instruments for healthcare professionals, distributors, clinics, and private-label brands worldwide. As a USA-based company, our focus is centered on reliability, professional service, and building long-term partnerships based on trust, consistency, and quality.</p>
        <p class="sr dl3">With strong roots in manufacturing and a modern distribution-focused approach, Aurix International combines <strong>generations of industry expertise</strong> with efficient sourcing solutions to provide dependable instruments at competitive value.</p>
        <div style="margin-top:28px" class="sr dl4"><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-navy btn-sm">Explore Our Products <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 16 16"><path d="M3 8h10M9.5 4.5L13 8l-3.5 3.5"/></svg></a></div>
      </div>
      <div class="feature-panel sr dl2">
        <div class="feature-panel-header">
          <div class="fp-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
          <div><div class="fp-heading">Why Partners Choose Aurix</div><div class="fp-sub">Our commitment to every client</div></div>
        </div>
        <div class="fp-list">
          <div class="fp-item"><span class="fp-dot"></span><div><div class="fp-item-text">Precision-Crafted Instruments</div><div class="fp-item-sub">Surgical &amp; dental grade quality assured</div></div></div>
          <div class="fp-item"><span class="fp-dot"></span><div><div class="fp-item-text">Reliable Supply Chain</div><div class="fp-item-sub">On-time delivery, every time</div></div></div>
          <div class="fp-item"><span class="fp-dot"></span><div><div class="fp-item-text">OEM &amp; Private Label Solutions</div><div class="fp-item-sub">Build your brand with our infrastructure</div></div></div>
          <div class="fp-item"><span class="fp-dot"></span><div><div class="fp-item-text">Competitive Value Pricing</div><div class="fp-item-sub">No compromise on quality</div></div></div>
          <div class="fp-item"><span class="fp-dot"></span><div><div class="fp-item-text">Worldwide Export Support</div><div class="fp-item-sub">Serving distributors globally</div></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

<!-- STORY -->
<section class="section section-off" id="story">
  <div class="wrap">
    <div class="story-head">
      <div><div class="eyebrow sr"><span class="eyebrow-line"></span>Our Story</div><h2 class="h2 sr dl1">From Workshop Floor<br>to <em>Global Distribution</em></h2></div>
      <p class="body-lg sr dl2">Founded by Abdul Raffay, Aurix International carries forward a legacy built over four generations of manufacturing excellence — now reimagined for the modern distribution landscape.</p>
    </div>
    <div class="timeline">
      <div class="tl-col tl-left">
        <div class="tl-item sr"><div class="tl-item-inner"><div class="tl-era"><span class="tl-era-dot"></span>The Beginning</div><div class="tl-title">A Family Legacy in Surgical Instruments</div><p class="tl-text">Aurix International was founded by Abdul Raffay, whose vision has always been to build a modern surgical and dental instrument distribution company in the United States while continuing his family's long-standing legacy in manufacturing. Growing up around the manufacturing industry, he was inspired watching his father and uncles dedicate their lives to producing surgical instruments.</p></div></div>
        <div class="tl-item sr dl2"><div class="tl-item-inner"><div class="tl-era"><span class="tl-era-dot"></span>Age 16</div><div class="tl-title">Hands-On Manufacturing Experience</div><p class="tl-text">From the age of 16, Abdul Raffay began working directly in manufacturing — gaining hands-on experience in production, finishing, quality control, and understanding customer requirements. Over the years, this gave him a deep understanding of both the technical and business sides of the industry.</p></div></div>
      </div>
      <div class="tl-center"><div class="tl-node"></div><div class="tl-line"></div><div class="tl-node"></div><div class="tl-line"></div><div class="tl-node"></div><div class="tl-line"></div><div class="tl-node"></div></div>
      <div class="tl-col tl-right">
        <div class="tl-item sr dl1"><div class="tl-item-inner"><div class="tl-era"><span class="tl-era-dot"></span>4+ Generations</div><div class="tl-title">Decades of Craftsmanship &amp; Expertise</div><p class="tl-text">His family has been involved in the manufacturing business for over four generations, building decades of craftsmanship, technical expertise, and industry experience. This deep heritage forms the foundation of everything Aurix International stands for — quality that is earned, not claimed.</p></div></div>
        <div class="tl-item sr dl3"><div class="tl-item-inner"><div class="tl-era"><span class="tl-era-dot"></span>Age 22 · Present</div><div class="tl-title">Launching Aurix International</div><p class="tl-text">By the age of 22, Abdul Raffay entered the distribution side through Aurix International — bridging the gap between manufacturing and distribution by bringing customers dependable quality, competitive pricing, and a more modern purchasing experience.</p></div></div>
      </div>
    </div>
  </div>
</section>

<div class="divider"></div>

<!-- OFFERINGS -->
<section class="section section-w" id="offerings">
  <div class="wrap">
    <div class="off-head">
      <div><div class="eyebrow sr"><span class="eyebrow-line"></span>What We Offer</div><h2 class="h2 sr dl1">More Than<br>a <em>Supplier</em></h2></div>
      <p class="body-lg sr dl2">Aurix International is focused on <strong style="color:var(--text-2)">improving the buying experience</strong> for healthcare businesses through modern solutions, flexible services, and a customer-first approach.</p>
    </div>
    <div class="offer-grid">
      <div class="offer-card sr"><div class="offer-num">01</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg></div><div class="offer-title">Surgical Instruments</div><div class="offer-desc">Precision-crafted surgical instruments for hospitals, clinics, and healthcare professionals worldwide.</div></div>
      <div class="offer-card sr dl1"><div class="offer-num">02</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div class="offer-title">Dental Instruments</div><div class="offer-desc">Full range of dental instruments for offices, distributors, and private-label brands globally.</div></div>
      <div class="offer-card sr dl2"><div class="offer-num">03</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg></div><div class="offer-title">OEM Manufacturing</div><div class="offer-desc">Full OEM manufacturing solutions for brands that need custom production runs at scale.</div></div>
      <div class="offer-card sr dl3"><div class="offer-num">04</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></div><div class="offer-title">Private Labeling</div><div class="offer-desc">Build your own instrument brand with our flexible private labeling and customization services.</div></div>
      <div class="offer-card sr"><div class="offer-num">05</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></div><div class="offer-title">Custom Branding USA</div><div class="offer-desc">Custom branding services provided from within the USA for faster turnaround and full compliance.</div></div>
      <div class="offer-card sr dl1"><div class="offer-num">06</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg></div><div class="offer-title">Pre-Assembled Kits</div><div class="offer-desc">Pre-assembled surgical and dental procedure kits designed to simplify purchasing for clinics.</div></div>
      <div class="offer-card sr dl2"><div class="offer-num">07</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div><div class="offer-title">Custom Kits</div><div class="offer-desc">Fully customized kit solutions tailored to specific surgical and dental procedure requirements.</div></div>
      <div class="offer-card sr dl3"><div class="offer-num">08</div><div class="offer-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg></div><div class="offer-title">Worldwide Export</div><div class="offer-desc">International distribution and export support to serve healthcare distributors and partners globally.</div></div>
    </div>
    <div class="kit-banner sr">
      <div>
        <div class="kit-eyebrow"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-linecap="round"/></svg> Innovation · Coming Soon</div>
        <h3>Build Your Own Kit — <em>Online</em></h3>
        <p>Select any instruments, add to cart, and build fully customized procedure kits — eliminating hundreds of emails and back-and-forth with suppliers.</p>
        <div class="kit-chips">
          <span class="kit-chip"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l2 2 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>Select online</span>
          <span class="kit-chip"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l2 2 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>Custom kits</span>
          <span class="kit-chip"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l2 2 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>Order in cart</span>
          <span class="kit-chip"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l2 2 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>No back-and-forth</span>
        </div>
      </div>
      <div class="kit-right"><a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn-gold">Learn More <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 16 16"><path d="M3 8h10M9.5 4.5L13 8l-3.5 3.5"/></svg></a><a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn-outline-w btn-sm">Get Notified</a></div>
    </div>
  </div>
</section>

<div class="divider"></div>

<!-- MVV -->
<section class="section section-off">
  <div class="wrap">
    <div class="mvv-head"><div class="eyebrow sr" style="justify-content:center"><span class="eyebrow-line"></span>Our Foundation<span class="eyebrow-line"></span></div><h2 class="h2 sr dl1" style="margin:0 auto">Mission, Vision &amp; <em>Values</em></h2></div>
    <div class="mvv-grid">
      <div class="mvv-card sr"><div class="mvv-top"><div class="mvv-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2" fill="#c9921a" stroke="none"/></svg></div><div class="mvv-tag">Mission</div><div class="mvv-title">Our Mission</div></div><div class="mvv-body"><p>To provide healthcare professionals and distributors with dependable, precision-crafted instruments while delivering a simpler, more efficient sourcing experience through quality, innovation, and professional service.</p><div class="mvv-sep"></div><ul class="mvv-list"><li class="mvv-li"><span class="mvv-li-dot"></span>Precision-crafted instruments</li><li class="mvv-li"><span class="mvv-li-dot"></span>Reliable supply &amp; on-time delivery</li><li class="mvv-li"><span class="mvv-li-dot"></span>Responsive, professional service</li><li class="mvv-li"><span class="mvv-li-dot"></span>Competitive pricing, zero compromise</li></ul></div></div>
      <div class="mvv-card sr dl2"><div class="mvv-top"><div class="mvv-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></div><div class="mvv-tag">Vision</div><div class="mvv-title">Our Vision</div></div><div class="mvv-body"><p>To become a globally trusted name in surgical and dental instrument distribution by combining generations of manufacturing expertise with modern technology and innovative purchasing systems.</p><div class="mvv-sep"></div><ul class="mvv-list"><li class="mvv-li"><span class="mvv-li-dot"></span>Globally trusted distribution brand</li><li class="mvv-li"><span class="mvv-li-dot"></span>Online customizable kit-builder</li><li class="mvv-li"><span class="mvv-li-dot"></span>Bridging manufacturing &amp; commerce</li><li class="mvv-li"><span class="mvv-li-dot"></span>OEM &amp; private label services</li></ul></div></div>
      <div class="mvv-card sr dl4"><div class="mvv-top"><div class="mvv-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L20 5.5V12C20 17 16.5 21 12 23C7.5 21 4 17 4 12V5.5Z"/><path d="M9 12L11.5 14.5L15.5 10"/></svg></div><div class="mvv-tag">Values</div><div class="mvv-title">Our Values</div></div><div class="mvv-body"><p>Built on four generations of craftsmanship, our values guide everything we do — from how we source instruments to how we serve our partners, every single day.</p><div class="mvv-sep"></div><ul class="mvv-list"><li class="mvv-li"><span class="mvv-li-dot"></span>Quality &amp; precision in every instrument</li><li class="mvv-li"><span class="mvv-li-dot"></span>Trust &amp; long-term partnerships</li><li class="mvv-li"><span class="mvv-li-dot"></span>Innovation in sourcing &amp; UX</li><li class="mvv-li"><span class="mvv-li-dot"></span>Integrity in pricing &amp; communication</li></ul></div></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section" id="contact">
  <div class="wrap">
    <div class="cta-inner">
      <div class="eyebrow eyebrow-inv sr" style="justify-content:center"><span class="eyebrow-line"></span>Work With Us</div>
      <h2 class="display display-inv sr dl1" style="font-size:clamp(32px,5vw,56px);margin-bottom:0">Ready to Source<br><em>Smarter?</em></h2>
      <p class="sr dl2">Whether you're a distributor, clinic, or private-label brand — let's talk about how Aurix International can simplify your instrument sourcing.</p>
      <div class="cta-btns sr dl3">
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="btn btn-gold">Contact Us Today <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 16 16"><path d="M3 8h10M9.5 4.5L13 8l-3.5 3.5"/></svg></a>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn-outline-w">Browse Products</a>
      </div>
    </div>
    <div class="contact-grid sr dl3">
      <a href="mailto:<?php echo esc_attr(get_theme_mod('aurix_email','info@aurixinternational.com')); ?>" class="contact-card"><div class="contact-card-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div><div><div class="contact-card-label">Email Us</div><div class="contact-card-value"><?php echo esc_html(get_theme_mod('aurix_email','info@aurixinternational.com')); ?></div></div></a>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="contact-card"><div class="contact-card-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg></div><div><div class="contact-card-label">Our Website</div><div class="contact-card-value">aurixinternational.com</div></div></a>
      <div class="contact-card" style="cursor:default"><div class="contact-card-icon"><svg fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div><div><div class="contact-card-label">Based In</div><div class="contact-card-value">United States of America</div></div></div>
    </div>
  </div>
</section>

</div>

<script>
(function(){
  var io=new IntersectionObserver(function(e){e.forEach(function(n){if(!n.isIntersecting)return;n.target.classList.add('show');io.unobserve(n.target)});},{threshold:0.08,rootMargin:'0px 0px -40px 0px'});
  document.querySelectorAll('.aurix-about-page .sr').forEach(function(el){io.observe(el)});
})();
</script>

<?php get_footer(); ?>
