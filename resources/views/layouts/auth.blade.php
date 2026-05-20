<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Masuk') — PosyanduCare</title>

{{-- ▸ Favicon: infinity-loop + amber dot, clean SVG --}}
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%23f8fffe'/%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%23047857'/%3E%3Cstop offset='1' stop-color='%2310b981'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath d='M10 33 C10 26 15 21 21.5 21 C26 21 29.5 23.5 32 27 C34.5 23.5 38 21 42.5 21 C49 21 54 26 54 33 C54 40 49 45 42.5 45 C38 45 34.5 42.5 32 39 C29.5 42.5 26 45 21.5 45 C15 45 10 40 10 33Z' fill='url(%23g)'/%3E%3Ccircle cx='49' cy='16' r='7' fill='%23f59e0b'/%3E%3C/svg%3E">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

<style>
/* ════════ TOKENS ════════ */
:root{
  --g900:#064e3b;--g700:#047857;--g600:#059669;--g500:#10b981;--g400:#34d399;--g300:#6ee7b7;
  --amber:#f59e0b;--amber2:#d97706;
  --s900:#0f172a;--s800:#1e293b;--s700:#334155;--s600:#475569;--s500:#64748b;
  --s400:#94a3b8;--s300:#cbd5e1;--s200:#e2e8f0;--s100:#f1f5f9;--s50:#f8fafc;
  --red:#ef4444;--red-s:#fca5a5;
  --E:cubic-bezier(.22,1,.36,1);
  --sans:'Plus Jakarta Sans',sans-serif;
  --display:'Poppins',sans-serif;
  --r-card:26px;
}

/* ════════ RESET ════════ */
*,*::before,*::after{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0;padding:0;width:100%;min-height:100%;overflow-x:hidden}
body{
  font-family:var(--sans);color:var(--s800);
  background:linear-gradient(155deg,#e6f7f0 0%,#f3faf6 45%,#f8fafc 100%);
  -webkit-font-smoothing:antialiased;
}
body.pc-lock{overflow:hidden!important;height:100dvh!important;touch-action:none!important}
h1,h2,h3,h4,h5,h6{font-family:var(--display);margin:0}
button,input,select,textarea{font-family:inherit}
::selection{background:rgba(16,185,129,.18);color:var(--g900)}

/* ════════ BACKGROUND BLOBS ════════ */
.bg-blob{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;will-change:transform}
.bg-blob:nth-child(1){width:500px;height:500px;top:-160px;left:-140px;background:rgba(16,185,129,.16);animation:blobDrift 20s ease-in-out infinite alternate}
.bg-blob:nth-child(2){width:380px;height:380px;bottom:-100px;right:-100px;background:rgba(20,184,166,.12);animation:blobDrift 26s ease-in-out infinite alternate-reverse}
.bg-blob:nth-child(3){width:260px;height:260px;top:42%;left:18%;background:rgba(245,158,11,.08);animation:blobDrift 18s ease-in-out 4s infinite alternate}
@keyframes blobDrift{
  0%{transform:translate(0,0) scale(1)}
  50%{transform:translate(24px,-32px) scale(1.06)}
  100%{transform:translate(-20px,18px) scale(.95)}
}

/* ════════ DECORATIVE DOTS ════════ */
.deco-dots{
  position:fixed;top:28px;left:28px;width:80px;height:80px;
  opacity:.18;pointer-events:none;z-index:1
}

/* ════════ LEAF DECORATION ════════ */
.deco-leaves{
  position:fixed;bottom:-20px;left:-30px;
  width:min(400px,52vw);pointer-events:none;z-index:1;
  transform:rotate(-6deg);transform-origin:bottom left;
  opacity:.95
}

/* ════════ WAVE ════════ */
.deco-wave{
  position:fixed;bottom:0;left:0;right:0;
  pointer-events:none;z-index:1;line-height:0
}

/* ════════ LAYOUT ════════ */
.auth-wrap{
  position:relative;z-index:10;
  min-height:100dvh;
  display:grid;
  grid-template-columns:1fr 510px;
  align-items:center;
  gap:72px;
  padding:48px 64px;
  max-width:1380px;
  margin:0 auto;
}

/* ════════ BRAND SIDE ════════ */
.brand-side{
  display:flex;flex-direction:column;align-items:center;text-align:center;
  animation:fadeLeft .8s var(--E) both
}
@keyframes fadeLeft{from{opacity:0;transform:translateX(-28px);filter:blur(6px)}to{opacity:1;transform:none;filter:none}}

.brand-logo{
  width:min(300px,85%);height:auto;display:block;
  margin-bottom:16px;
  filter:drop-shadow(0 12px 24px rgba(4,120,87,.14));
  user-select:none;pointer-events:none
}

/* tagline "Sehat Bersama…" */
.brand-tagline{
  font-family:var(--display);
  font-size:clamp(14px,1.5vw,16px);
  font-weight:700;
  color:var(--s700);
  margin:0 0 6px;
}

/* amber divider */
.brand-sep{display:flex;align-items:center;gap:8px;margin:10px 0 14px}
.sep-line{width:40px;height:2px;border-radius:99px}
.sep-line.l{background:linear-gradient(to right,transparent,var(--amber))}
.sep-line.r{background:linear-gradient(to left,transparent,var(--amber))}
.sep-dot{
  width:7px;height:7px;border-radius:2px;
  background:var(--amber);transform:rotate(45deg);
  box-shadow:0 0 10px rgba(245,158,11,.4)
}

.brand-desc{
  margin:0 0 28px;color:var(--s600);
  font-size:clamp(13px,1.3vw,14.5px);
  font-weight:600;line-height:1.7;max-width:360px
}

/* Feature cards */
.feat-grid{
  display:grid;grid-template-columns:repeat(4,1fr);gap:12px;
  width:100%;max-width:400px
}
.feat-card{
  height:88px;border-radius:18px;
  background:rgba(255,255,255,.78);
  border:1.5px solid rgba(255,255,255,.92);
  box-shadow:0 6px 18px rgba(15,23,42,.06),inset 0 1px 0 rgba(255,255,255,.9);
  backdrop-filter:blur(14px);
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;
  cursor:default;
  transition:transform .3s var(--E),box-shadow .3s ease;
  animation:fadeUp .7s var(--E) both
}
.feat-card:nth-child(1){animation-delay:.15s}
.feat-card:nth-child(2){animation-delay:.22s}
.feat-card:nth-child(3){animation-delay:.29s}
.feat-card:nth-child(4){animation-delay:.36s}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.feat-card:hover{transform:translateY(-5px);box-shadow:0 14px 28px rgba(16,185,129,.14)}
.feat-icon{
  width:38px;height:38px;border-radius:11px;
  background:rgba(5,150,105,.10);color:var(--g600);
  display:flex;align-items:center;justify-content:center;font-size:15px
}
.feat-label{font-size:10.5px;font-weight:800;color:var(--s800);letter-spacing:.3px}

/* ════════ FORM SIDE ════════ */
.form-side{
  position:relative;z-index:10;
  animation:fadeUp2 .85s var(--E) .06s both
}
@keyframes fadeUp2{from{opacity:0;transform:translateY(24px);filter:blur(4px)}to{opacity:1;transform:none;filter:none}}

.login-card{
  background:rgba(255,255,255,.97);
  border:1px solid rgba(16,185,129,.08);
  border-radius:var(--r-card);
  padding:44px 44px 38px;
  box-shadow:0 24px 64px rgba(15,23,42,.10),0 4px 14px rgba(15,23,42,.04),inset 0 1px 0 rgba(255,255,255,.99);
  backdrop-filter:blur(16px)
}

.card-title{
  font-size:clamp(20px,2.2vw,24px);font-weight:900;
  color:var(--g900);text-align:center;margin-bottom:6px
}
.card-sub{
  font-size:13.5px;color:var(--s500);font-weight:500;
  text-align:center;margin-bottom:28px;line-height:1.5
}

/* ── FIELDS ── */
.field{margin-bottom:16px}
.field-label{
  display:block;font-size:12.5px;font-weight:700;
  color:var(--s700);margin-bottom:7px;letter-spacing:.3px;text-transform:uppercase
}
.field-wrap{position:relative}
.field-input{
  width:100%;height:52px;
  padding:0 46px 0 44px;
  border-radius:13px;border:1.5px solid var(--s200);
  background:#fff;font-size:14px;color:var(--s900);
  outline:none;
  transition:border-color .2s,box-shadow .2s
}
.field-input::placeholder{color:var(--s400);font-weight:500}
.field-input:focus{
  border-color:var(--g500);
  box-shadow:0 0 0 3.5px rgba(16,185,129,.13)
}
.field-input.is-err{border-color:var(--red);box-shadow:0 0 0 3px rgba(239,68,68,.1)}
.fi-icon-l{
  position:absolute;left:14px;top:50%;transform:translateY(-50%);
  color:var(--s400);font-size:14px;pointer-events:none;
  transition:color .2s
}
.field-input:focus~.fi-icon-l{color:var(--g600)}
.fi-eye{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;
  color:var(--s400);font-size:15px;padding:6px;
  transition:color .2s
}
.fi-eye:hover{color:var(--g600)}

/* inline field error */
.field-msg{
  display:none;margin-top:5px;
  font-size:11.5px;font-weight:600;
  padding-left:2px
}
.field-msg.show{display:block}
.field-msg.err{color:var(--red)}
.field-msg.ok{color:var(--g600)}

/* Forgot */
.row-forgot{text-align:right;margin:0 0 18px}
.link-forgot{
  font-size:13px;font-weight:700;
  color:var(--g600);text-decoration:none;
  transition:color .15s
}
.link-forgot:hover{color:var(--g700);text-decoration:underline}

/* Submit */
.btn-submit{
  width:100%;height:54px;border-radius:13px;border:none;cursor:pointer;
  background:linear-gradient(135deg,var(--g700) 0%,var(--g500) 100%);
  color:#fff;font-size:15px;font-weight:700;letter-spacing:.3px;
  display:flex;align-items:center;justify-content:center;gap:10px;
  box-shadow:0 8px 22px rgba(16,185,129,.30);
  transition:transform .25s var(--E),box-shadow .25s ease;
  position:relative;overflow:hidden
}
.btn-submit::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,.18),transparent);
  opacity:0;transition:opacity .2s
}
.btn-submit:hover:not(:disabled)::after{opacity:1}
.btn-submit:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 14px 32px rgba(16,185,129,.38)}
.btn-submit:active:not(:disabled){transform:translateY(0)}
.btn-submit:disabled{opacity:.7;cursor:not-allowed}
.btn-submit .ic-spin{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
.btn-submit.loading .ic-spin{display:block}
.btn-submit.loading .ic-arrow{display:none}
@keyframes spin{to{transform:rotate(360deg)}}

/* Admin note (replaces social/register) */
.admin-note{
  margin-top:22px;
  padding:14px 16px;
  border-radius:12px;
  background:rgba(16,185,129,.05);
  border:1px solid rgba(16,185,129,.12);
  display:flex;align-items:flex-start;gap:10px
}
.admin-note-icon{
  flex-shrink:0;width:28px;height:28px;border-radius:8px;
  background:rgba(5,150,105,.1);color:var(--g600);
  display:flex;align-items:center;justify-content:center;font-size:12px;
  margin-top:1px
}
.admin-note-text{font-size:12px;color:var(--s500);font-weight:600;line-height:1.6}
.admin-note-text a{color:var(--g600);font-weight:700;text-decoration:none}
.admin-note-text a:hover{text-decoration:underline}

/* ════════ LOADING OVERLAY ════════ */
#pcLoader{
  position:fixed;inset:0;z-index:99999;
  display:flex;align-items:center;justify-content:center;
  visibility:hidden;pointer-events:none
}
#pcLoader.show{visibility:visible;pointer-events:auto}

.ld-veil{
  position:absolute;inset:0;
  background:rgba(240,255,248,.88);
  backdrop-filter:blur(18px) saturate(1.4);
  opacity:0;transition:opacity .35s ease
}
#pcLoader.show .ld-veil{opacity:1}

.ld-panel{
  position:relative;z-index:2;
  background:rgba(255,255,255,.96);
  border:1px solid rgba(16,185,129,.14);
  border-radius:28px;
  padding:40px 52px 36px;
  box-shadow:0 32px 80px rgba(15,23,42,.14),inset 0 1px 0 rgba(255,255,255,.9);
  display:flex;flex-direction:column;align-items:center;text-align:center;
  min-width:272px;
  opacity:0;transform:translateY(22px) scale(.94);
  transition:opacity .4s var(--E) .1s,transform .4s var(--E) .1s
}
#pcLoader.show .ld-panel{opacity:1;transform:none}

/* Orbit loader */
.ld-orbit{
  position:relative;width:76px;height:76px;
  margin:0 auto 24px;
  display:flex;align-items:center;justify-content:center
}
.ld-ring{
  position:absolute;inset:0;border-radius:50%;
  border:2.5px solid transparent
}
.ld-ring:nth-child(1){
  border-top-color:var(--g500);
  border-right-color:rgba(16,185,129,.3);
  animation:spinR 1.1s linear infinite
}
.ld-ring:nth-child(2){
  inset:8px;
  border-bottom-color:var(--g400);
  border-left-color:rgba(52,211,153,.3);
  animation:spinR 1.7s linear infinite reverse
}
.ld-ring:nth-child(3){
  inset:18px;
  border-top-color:var(--amber);
  border-right-color:rgba(245,158,11,.25);
  animation:spinR 2.3s linear infinite
}
@keyframes spinR{to{transform:rotate(360deg)}}
.ld-heart{
  position:relative;z-index:2;
  font-size:20px;color:var(--g600);
  animation:heartBeat 1.4s ease-in-out infinite
}
@keyframes heartBeat{
  0%,100%{transform:scale(1);opacity:.9}
  14%{transform:scale(1.22)}
  28%{transform:scale(1)}
  42%{transform:scale(1.12)}
  70%{transform:scale(1);opacity:1}
}

.ld-name{
  font-family:var(--display);font-size:16px;font-weight:800;
  color:var(--s900);margin-bottom:3px;letter-spacing:-.3px
}
.ld-label{
  font-size:11px;font-weight:700;color:var(--s500);
  text-transform:uppercase;letter-spacing:.6px;margin-bottom:18px
}

/* Dot-pulse progress */
.ld-dots{display:flex;gap:6px;align-items:center;justify-content:center}
.ld-dot{
  width:7px;height:7px;border-radius:50%;
  background:var(--g400);
  animation:dotPop .9s ease-in-out infinite both
}
.ld-dot:nth-child(1){animation-delay:0s}
.ld-dot:nth-child(2){animation-delay:.18s;background:var(--g500)}
.ld-dot:nth-child(3){animation-delay:.36s;background:var(--g600)}
.ld-dot:nth-child(4){animation-delay:.54s;background:var(--amber)}
@keyframes dotPop{
  0%,80%,100%{transform:scale(.6);opacity:.4}
  40%{transform:scale(1.15);opacity:1}
}

/* ════════ NEXUS ALERT ════════ */
.nxa-veil{
  position:fixed;inset:0;z-index:99998;
  display:flex;align-items:center;justify-content:center;padding:20px;
  visibility:hidden;opacity:0;
  background:rgba(15,23,42,.25);
  backdrop-filter:blur(8px);
  transition:opacity .28s ease,visibility .28s
}
.nxa-veil.open{visibility:visible;opacity:1}

.nxa-panel{
  background:#fff;
  border-radius:22px;
  padding:0;
  width:100%;max-width:408px;
  box-shadow:0 32px 80px rgba(15,23,42,.18),0 0 0 1px rgba(226,232,240,.6);
  overflow:hidden;
  transform:scale(.88) translateY(28px);
  opacity:0;
  transition:transform .36s var(--E) .04s,opacity .28s ease .04s
}
.nxa-veil.open .nxa-panel{transform:none;opacity:1}

/* colored top strip */
.nxa-strip{height:4px;width:100%;transition:background .2s}
.nxa-strip.success{background:linear-gradient(90deg,var(--g700),var(--g400))}
.nxa-strip.error{background:linear-gradient(90deg,#dc2626,#f87171)}
.nxa-strip.warning{background:linear-gradient(90deg,var(--amber2),var(--amber))}
.nxa-strip.info{background:linear-gradient(90deg,#2563eb,#60a5fa)}

.nxa-body{padding:32px 36px 28px;display:flex;flex-direction:column;align-items:center;text-align:center}

.nxa-badge{
  width:62px;height:62px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:16px;position:relative
}
.nxa-badge::before{
  content:'';position:absolute;inset:-7px;border-radius:50%;
  animation:badgePulse 2.2s ease-in-out infinite;opacity:.14
}
@keyframes badgePulse{0%,100%{transform:scale(.88);opacity:.12}50%{transform:scale(1.08);opacity:.22}}
.nxa-badge.success{background:rgba(16,185,129,.12)}
.nxa-badge.success::before{background:var(--g500)}
.nxa-badge.success .nxa-ico{color:var(--g600)}
.nxa-badge.error{background:rgba(239,68,68,.1)}
.nxa-badge.error::before{background:var(--red)}
.nxa-badge.error .nxa-ico{color:var(--red)}
.nxa-badge.warning{background:rgba(245,158,11,.1)}
.nxa-badge.warning::before{background:var(--amber)}
.nxa-badge.warning .nxa-ico{color:var(--amber2)}
.nxa-badge.info{background:rgba(37,99,235,.08)}
.nxa-badge.info::before{background:#2563eb}
.nxa-badge.info .nxa-ico{color:#2563eb}
.nxa-ico{font-size:26px;display:flex;align-items:center;justify-content:center}

.nxa-title{font-family:var(--display);font-size:18px;font-weight:900;color:var(--s900);margin-bottom:8px}
.nxa-msg{font-size:13.5px;color:var(--s600);font-weight:600;line-height:1.65;margin-bottom:24px}

.nxa-actions{display:flex;gap:9px;width:100%}
.nxa-btn{
  flex:1;height:44px;border-radius:11px;border:none;cursor:pointer;
  font-size:13.5px;font-weight:700;letter-spacing:.2px;
  transition:transform .18s var(--E),box-shadow .18s,opacity .15s
}
.nxa-btn:hover{transform:translateY(-1px)}
.nxa-btn:active{transform:translateY(0);opacity:.9}
.nxa-btn.primary{
  background:linear-gradient(135deg,var(--g700),var(--g500));
  color:#fff;box-shadow:0 6px 18px rgba(16,185,129,.26)
}
.nxa-btn.primary:hover{box-shadow:0 10px 24px rgba(16,185,129,.36)}
.nxa-btn.secondary{background:var(--s100);color:var(--s700)}
.nxa-btn.secondary:hover{background:var(--s200)}

/* timer bar */
.nxa-timer{width:100%;height:2px;background:rgba(16,185,129,.1);border-radius:99px;overflow:hidden;margin-top:16px;display:none}
.nxa-timer.show{display:block}
.nxa-timer-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--g400),var(--g600));width:100%;transform-origin:left}
@keyframes timerShrink{to{transform:scaleX(0)}}

/* ════════ RESPONSIVE ════════ */
@media(max-width:1100px){
  .auth-wrap{padding:36px 40px;gap:48px}
}
@media(max-width:900px){
  .auth-wrap{
    grid-template-columns:1fr;
    padding:32px 20px 56px;
    gap:28px;max-width:520px
  }
  .form-side{order:-1}
  .brand-side{order:1}
  .deco-leaves{width:min(280px,50vw)}
}
@media(max-width:540px){
  .login-card{padding:28px 20px 24px;border-radius:20px}
  .feat-grid{grid-template-columns:repeat(4,1fr);gap:8px}
  .feat-card{height:78px}
  .feat-label{font-size:9.5px}
  .nxa-panel{max-width:96vw}
  .nxa-body{padding:26px 22px 22px}
  .ld-panel{padding:36px 32px 32px;min-width:240px}
}
@media(prefers-reduced-motion:reduce){
  *,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}
}
</style>
@stack('styles')
</head>
<body>

{{-- ▸ Background blobs --}}
<div class="bg-blob" aria-hidden="true"></div>
<div class="bg-blob" aria-hidden="true"></div>
<div class="bg-blob" aria-hidden="true"></div>

{{-- ▸ Dot grid --}}
<div class="deco-dots" aria-hidden="true">
  <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g fill="#10b981" opacity=".6">
      @for($r=0;$r<5;$r++)
        @for($c=0;$c<5;$c++)
          <circle cx="{{ 8+$c*16 }}" cy="{{ 8+$r*16 }}" r="2.4"/>
        @endfor
      @endfor
    </g>
  </svg>
</div>

{{-- ▸ Leaf decoration bottom-left --}}
<div class="deco-leaves" aria-hidden="true">
  <svg viewBox="0 0 400 340" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="lf1" x1="30" y1="330" x2="360" y2="80" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#059669" stop-opacity=".92"/>
        <stop offset="100%" stop-color="#34d399" stop-opacity=".45"/>
      </linearGradient>
      <linearGradient id="lf2" x1="0" y1="310" x2="290" y2="100" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#047857" stop-opacity=".86"/>
        <stop offset="100%" stop-color="#6ee7b7" stop-opacity=".40"/>
      </linearGradient>
      <linearGradient id="lf3" x1="150" y1="320" x2="320" y2="210" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#10b981" stop-opacity=".78"/>
        <stop offset="100%" stop-color="#a7f3d0" stop-opacity=".35"/>
      </linearGradient>
    </defs>
    <path d="M30 325C42 252 106 168 208 116C290 74 350 84 366 105C372 114 356 136 320 162C268 198 196 228 144 278C102 312 62 330 30 325Z" fill="url(#lf1)" opacity=".84"/>
    <path d="M0 305C22 242 84 166 166 124C228 92 280 98 292 118C298 129 280 150 248 172C202 204 140 234 88 276C50 306 12 318 0 305Z" fill="url(#lf2)" opacity=".74"/>
    <path d="M162 315C175 280 215 246 262 224C294 208 316 213 318 226C320 234 306 248 282 260C252 274 216 286 190 302C174 312 160 318 162 315Z" fill="url(#lf3)" opacity=".62"/>
    <path d="M185 295C206 255 258 212 308 180" stroke="#047857" stroke-width="1.4" stroke-linecap="round" opacity=".2"/>
    <path d="M65 298C96 262 148 220 208 188" stroke="#047857" stroke-width="1.4" stroke-linecap="round" opacity=".16"/>
  </svg>
</div>

{{-- ▸ Bottom wave --}}
<div class="deco-wave" aria-hidden="true">
  <svg viewBox="0 0 1440 160" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:auto">
    <path fill="#10b981" fill-opacity=".065" d="M0,125C280,85 560,118 840,124C1110,130 1310,100 1440,84L1440,160L0,160Z"/>
    <path fill="#0f766e" fill-opacity=".07"  d="M0,142C320,114 620,138 900,146C1150,154 1330,124 1440,108L1440,160L0,160Z"/>
  </svg>
</div>

{{-- ════════════════ MAIN LAYOUT ════════════════ --}}
<main aria-label="Halaman masuk PosyanduCare">
<div class="auth-wrap">

  {{-- ── BRAND SIDE ── --}}
  <section class="brand-side" aria-label="Brand PosyanduCare">

    <img src="{{ asset('img/logo.png') }}"
         alt="Logo PosyanduCare — Infinity Care"
         class="brand-logo">

    <p class="brand-tagline">Sehat Bersama, Tumbuh Setiap Generasi</p>

    <div class="brand-sep" aria-hidden="true">
      <span class="sep-line l"></span>
      <span class="sep-dot"></span>
      <span class="sep-line r"></span>
    </div>

    <p class="brand-desc">Platform layanan kesehatan terpadu<br>untuk masyarakat modern.</p>

    <div class="feat-grid" role="list">
      @foreach([
        ['fa-people-group','Terintegrasi'],
        ['fa-shield-halved','Aman'],
        ['fa-chart-simple','Efisien'],
        ['fa-heart-pulse','Peduli'],
      ] as $f)
      <div class="feat-card" role="listitem">
        <div class="feat-icon" aria-hidden="true"><i class="fa-solid {{ $f[0] }}"></i></div>
        <span class="feat-label">{{ $f[1] }}</span>
      </div>
      @endforeach
    </div>

  </section>

  {{-- ── FORM SIDE ── --}}
  <section class="form-side" aria-label="Form masuk">
    <div class="login-card">
      @yield('content')
    </div>
  </section>

</div>
</main>

{{-- ════════════════ LOADING OVERLAY ════════════════ --}}
<div id="pcLoader" role="status" aria-label="Memuat, harap tunggu…" aria-live="polite">
  <div class="ld-veil"></div>
  <div class="ld-panel">
    <div class="ld-orbit">
      <div class="ld-ring"></div>
      <div class="ld-ring"></div>
      <div class="ld-ring"></div>
      <i class="fa-solid fa-heart-pulse ld-heart"></i>
    </div>
    <div class="ld-name">PosyanduCare</div>
    <div class="ld-label">Membuka Portal</div>
    <div class="ld-dots">
      <span class="ld-dot"></span>
      <span class="ld-dot"></span>
      <span class="ld-dot"></span>
      <span class="ld-dot"></span>
    </div>
  </div>
</div>

{{-- ════════════════ NEXUS ALERT ════════════════ --}}
<div class="nxa-veil" id="nxaVeil" role="dialog" aria-modal="true" aria-live="assertive">
  <div class="nxa-panel" role="document">
    <div class="nxa-strip" id="nxaStrip"></div>
    <div class="nxa-body">
      <div class="nxa-badge" id="nxaBadge">
        <i class="nxa-ico" id="nxaIco"></i>
      </div>
      <div class="nxa-title" id="nxaTitle"></div>
      <div class="nxa-msg"   id="nxaMsg"></div>
      <div class="nxa-actions" id="nxaActions"></div>
      <div class="nxa-timer" id="nxaTimer">
        <div class="nxa-timer-fill" id="nxaTimerFill"></div>
      </div>
    </div>
  </div>
</div>

<script>
/* ── NEXUS ALERT ENGINE ── */
window.NxAlert=(function(){
  var veil  =document.getElementById('nxaVeil');
  var strip =document.getElementById('nxaStrip');
  var badge =document.getElementById('nxaBadge');
  var ico   =document.getElementById('nxaIco');
  var title =document.getElementById('nxaTitle');
  var msg   =document.getElementById('nxaMsg');
  var acts  =document.getElementById('nxaActions');
  var timer =document.getElementById('nxaTimer');
  var fill  =document.getElementById('nxaTimerFill');
  var _t;

  var MAP={
    success:{ico:'fa-circle-check',strip:'success',badge:'success'},
    error:  {ico:'fa-circle-xmark',strip:'error',  badge:'error'},
    warning:{ico:'fa-triangle-exclamation',strip:'warning',badge:'warning'},
    info:   {ico:'fa-circle-info',strip:'info',   badge:'info'},
  };

  function close(){veil.classList.remove('open');clearTimeout(_t);}

  function fire(o){
    var t=o.type||'success';var m=MAP[t]||MAP.success;
    strip.className='nxa-strip '+m.strip;
    badge.className='nxa-badge '+m.badge;
    ico.className  ='nxa-ico fa-solid '+m.ico;
    title.textContent=o.title||'';
    msg.innerHTML   =o.message||'';
    acts.innerHTML  ='';
    if(o.cancelText){
      var c=document.createElement('button');
      c.className='nxa-btn secondary';c.textContent=o.cancelText;
      c.onclick=function(){close();if(o.onCancel)o.onCancel();};
      acts.appendChild(c);
    }
    var b=document.createElement('button');
    b.className='nxa-btn primary';b.textContent=o.confirmText||'Tutup';
    b.onclick=function(){close();if(o.onConfirm)o.onConfirm();};
    acts.appendChild(b);
    if(o.timer){
      timer.classList.add('show');
      fill.style.animation='none';fill.offsetHeight;
      fill.style.animation='timerShrink '+o.timer+'ms linear forwards';
      _t=setTimeout(close,o.timer);
    }else{timer.classList.remove('show');}
    veil.classList.add('open');
    /* focus trap */
    setTimeout(function(){b.focus();},60);
  }

  veil.addEventListener('click',function(e){if(e.target===veil)close();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&veil.classList.contains('open'))close();});
  return{fire:fire,close:close};
})();

/* ── LOADER ENGINE ── */
window.PcLoader=(function(){
  var el=document.getElementById('pcLoader');
  return{
    show:function(){document.body.classList.add('pc-lock');el.classList.add('show');},
    hide:function(){document.body.classList.remove('pc-lock');el.classList.remove('show');}
  };
})();

/* ── AUTO BIND LOGIN FORM ── */
document.addEventListener('DOMContentLoaded',function(){
  var form=document.getElementById('loginFormEngine');
  if(!form||form.dataset.bound)return;
  form.dataset.bound='1';

  /* password toggle */
  var passInput=form.querySelector('#passInput');
  var eyeBtn   =form.querySelector('#eyeBtn');
  if(eyeBtn&&passInput){
    eyeBtn.addEventListener('click',function(){
      var show=passInput.type==='password';
      passInput.type=show?'text':'password';
      eyeBtn.querySelector('i').className='fa-solid '+(show?'fa-eye-slash':'fa-eye');
    });
  }

  form.addEventListener('submit',function(e){
    e.preventDefault();
    var btn=document.getElementById('submitActionBtn');
    var txt=document.getElementById('submitTxt');
    if(btn){btn.disabled=true;btn.classList.add('loading');}
    if(txt){txt.textContent='Memeriksa data…';}
    setTimeout(function(){
      PcLoader.show();
      setTimeout(function(){form.submit();},1600);
    },160);
  });
});
</script>

@stack('scripts')
</body>
</html>