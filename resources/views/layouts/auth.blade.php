<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Masuk') — PosyanduCare</title>

{{-- Favicon: clean green rounded square + white cross-pulse mark --}}
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='bg' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%23047857'/%3E%3Cstop offset='1' stop-color='%2310b981'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='64' height='64' rx='16' fill='url(%23bg)'/%3E%3C!-- heart-pulse path in white --%3E%3Cpath d='M10 34h8l4-10 6 18 5-14 3 6h18' stroke='%23ffffff' stroke-width='4.2' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

<style>
/* ════════ TOKENS ════════ */
:root {
  --g900:#064e3b; --g700:#047857; --g600:#059669; --g500:#10b981; --g400:#34d399; --g300:#6ee7b7;
  --amber:#f59e0b; --amber2:#d97706;
  --s900:#0f172a; --s800:#1e293b; --s700:#334155; --s600:#475569; --s500:#64748b;
  --s400:#94a3b8; --s300:#cbd5e1; --s200:#e2e8f0; --s100:#f1f5f9; --s50:#f8fafc;
  --red:#ef4444;
  --E: cubic-bezier(.22,1,.36,1);
  --sans: 'Plus Jakarta Sans', sans-serif;
  --display: 'Poppins', sans-serif;
  --r-card: 24px;
  --r-sm: 14px;
}

/* ════════ RESET ════════ */
*, *::before, *::after { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
html, body { margin: 0; padding: 0; width: 100%; min-height: 100%; overflow-x: hidden; }
body {
  font-family: var(--sans);
  color: var(--s800);
  background: linear-gradient(150deg, #e4f5ee 0%, #f2f9f5 40%, #f7fafc 100%);
  -webkit-font-smoothing: antialiased;
}
body.pc-lock { overflow: hidden !important; height: 100dvh !important; touch-action: none !important; }
h1,h2,h3,h4,h5,h6 { font-family: var(--display); margin: 0; }
button, input, select, textarea { font-family: inherit; }
::selection { background: rgba(16,185,129,.18); color: var(--g900); }

/* ════════ BACKGROUND ════════ */
.bg-blob {
  position: fixed; border-radius: 50%;
  filter: blur(80px); pointer-events: none; z-index: 0; will-change: transform;
}
.bg-blob:nth-child(1) {
  width: 460px; height: 460px; top: -130px; left: -120px;
  background: rgba(16,185,129,.14);
  animation: blobA 22s ease-in-out infinite alternate;
}
.bg-blob:nth-child(2) {
  width: 340px; height: 340px; bottom: -80px; right: -80px;
  background: rgba(20,184,166,.10);
  animation: blobA 28s ease-in-out infinite alternate-reverse;
}
.bg-blob:nth-child(3) {
  width: 220px; height: 220px; top: 45%; left: 20%;
  background: rgba(245,158,11,.07);
  animation: blobA 19s ease-in-out 3s infinite alternate;
}
@keyframes blobA {
  0%   { transform: translate(0,0) scale(1); }
  50%  { transform: translate(18px,-24px) scale(1.05); }
  100% { transform: translate(-16px,14px) scale(.96); }
}

/* ════════ DECORATIVE ELEMENTS ════════ */
.deco-dots {
  position: fixed; top: 24px; left: 24px;
  width: 72px; height: 72px; opacity: .15;
  pointer-events: none; z-index: 1;
}
.deco-leaves {
  position: fixed; bottom: -16px; left: -24px;
  width: min(360px, 46vw);
  pointer-events: none; z-index: 1;
  transform: rotate(-6deg); transform-origin: bottom left;
  opacity: .9;
}
.deco-wave {
  position: fixed; bottom: 0; left: 0; right: 0;
  pointer-events: none; z-index: 1; line-height: 0;
}

/* ════════ LAYOUT — DESKTOP ════════ */
.auth-wrap {
  position: relative; z-index: 10;
  min-height: 100dvh;
  display: grid;
  grid-template-columns: 1fr 480px;
  align-items: center;
  gap: 64px;
  padding: 48px 60px;
  max-width: 1340px;
  margin: 0 auto;
}

/* ════════ BRAND SIDE ════════ */
.brand-side {
  display: flex; flex-direction: column; align-items: center; text-align: center;
  animation: fadeLeft .75s var(--E) both;
}
@keyframes fadeLeft {
  from { opacity: 0; transform: translateX(-24px); }
  to   { opacity: 1; transform: none; }
}

.brand-logo {
  width: min(280px, 82%); height: auto; display: block;
  margin-bottom: 14px;
  filter: drop-shadow(0 10px 22px rgba(4,120,87,.13));
  user-select: none; pointer-events: none;
}
.brand-tagline {
  font-family: var(--display);
  font-size: clamp(13px, 1.3vw, 15px);
  font-weight: 700;
  color: var(--s700);
  margin: 0 0 6px;
}
.brand-sep {
  display: flex; align-items: center; gap: 8px;
  margin: 8px 0 12px;
}
.sep-line { width: 36px; height: 2px; border-radius: 99px; }
.sep-line.l { background: linear-gradient(to right, transparent, var(--amber)); }
.sep-line.r { background: linear-gradient(to left, transparent, var(--amber)); }
.sep-dot {
  width: 6px; height: 6px; border-radius: 2px;
  background: var(--amber); transform: rotate(45deg);
  box-shadow: 0 0 8px rgba(245,158,11,.4);
}
.brand-desc {
  margin: 0 0 24px; color: var(--s600);
  font-size: clamp(12.5px, 1.2vw, 14px);
  font-weight: 600; line-height: 1.7; max-width: 340px;
}

/* Feature cards */
.feat-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 10px; width: 100%; max-width: 380px;
}
.feat-card {
  height: 84px; border-radius: 16px;
  background: rgba(255,255,255,.80);
  border: 1.5px solid rgba(255,255,255,.94);
  box-shadow: 0 4px 14px rgba(15,23,42,.06), inset 0 1px 0 rgba(255,255,255,.9);
  backdrop-filter: blur(12px);
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
  cursor: default;
  transition: transform .28s var(--E), box-shadow .28s ease;
  animation: fadeUp .65s var(--E) both;
}
.feat-card:nth-child(1) { animation-delay: .12s; }
.feat-card:nth-child(2) { animation-delay: .20s; }
.feat-card:nth-child(3) { animation-delay: .28s; }
.feat-card:nth-child(4) { animation-delay: .36s; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: none; }
}
.feat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 26px rgba(16,185,129,.13); }
.feat-icon {
  width: 34px; height: 34px; border-radius: 10px;
  background: rgba(5,150,105,.10); color: var(--g600);
  display: flex; align-items: center; justify-content: center; font-size: 14px;
}
.feat-label { font-size: 10px; font-weight: 800; color: var(--s800); letter-spacing: .3px; }

/* ════════ FORM SIDE ════════ */
.form-side {
  position: relative; z-index: 10;
  animation: fadeUp2 .8s var(--E) .05s both;
}
@keyframes fadeUp2 {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: none; }
}

.login-card {
  background: rgba(255,255,255,.97);
  border: 1px solid rgba(16,185,129,.08);
  border-radius: var(--r-card);
  padding: 40px 40px 34px;
  box-shadow:
    0 20px 56px rgba(15,23,42,.09),
    0 4px 12px rgba(15,23,42,.04),
    inset 0 1px 0 rgba(255,255,255,1);
  backdrop-filter: blur(14px);
}

/* ── Mobile brand header (hidden on desktop) ── */
.card-mobile-brand {
  display: none; /* shown only on mobile */
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding-bottom: 22px;
  margin-bottom: 22px;
  border-bottom: 1px solid rgba(16,185,129,.09);
}
.card-mobile-brand .m-logo {
  width: min(180px, 60vw);
  height: auto;
  display: block;
  margin: 0 auto 10px;
  filter: drop-shadow(0 6px 14px rgba(4,120,87,.12));
  user-select: none; pointer-events: none;
}
.card-mobile-brand .m-tagline {
  font-family: var(--display);
  font-size: 12px;
  font-weight: 700;
  color: var(--s600);
  margin: 0;
  letter-spacing: .2px;
}

/* ── Card text ── */
.card-title {
  font-size: clamp(19px, 2vw, 23px);
  font-weight: 900;
  color: var(--g900);
  text-align: center;
  margin-bottom: 5px;
}
.card-sub {
  font-size: 13px;
  color: var(--s500);
  font-weight: 500;
  text-align: center;
  margin-bottom: 26px;
  line-height: 1.5;
}

/* ── Fields ── */
.field { margin-bottom: 14px; }
.field-label {
  display: block;
  font-size: 11.5px; font-weight: 700;
  color: var(--s700);
  margin-bottom: 6px;
  letter-spacing: .35px; text-transform: uppercase;
}
.field-wrap { position: relative; }
.field-input {
  width: 100%; height: 50px;
  padding: 0 44px 0 42px;
  border-radius: var(--r-sm);
  border: 1.5px solid var(--s200);
  background: #fff;
  font-size: 14px; color: var(--s900);
  outline: none;
  transition: border-color .18s, box-shadow .18s;
}
.field-input::placeholder { color: var(--s400); font-weight: 500; }
.field-input:focus {
  border-color: var(--g500);
  box-shadow: 0 0 0 3px rgba(16,185,129,.12);
}
.field-input.is-err { border-color: var(--red); box-shadow: 0 0 0 3px rgba(239,68,68,.09); }
.fi-icon-l {
  position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
  color: var(--s400); font-size: 13.5px; pointer-events: none;
  transition: color .18s;
}
.field-input:focus ~ .fi-icon-l { color: var(--g600); }
.fi-eye {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: var(--s400); font-size: 14px; padding: 7px;
  transition: color .18s;
}
.fi-eye:hover { color: var(--g600); }

.field-msg {
  display: none; margin-top: 4px;
  font-size: 11px; font-weight: 600; padding-left: 2px;
}
.field-msg.show { display: block; }
.field-msg.err  { color: var(--red); }
.field-msg.ok   { color: var(--g600); }

/* Forgot */
.row-forgot { text-align: right; margin: 0 0 16px; }
.link-forgot {
  font-size: 12.5px; font-weight: 700;
  color: var(--g600); text-decoration: none;
  transition: color .15s;
}
.link-forgot:hover { color: var(--g700); text-decoration: underline; }

/* Submit */
.btn-submit {
  width: 100%; height: 52px; border-radius: var(--r-sm); border: none; cursor: pointer;
  background: linear-gradient(135deg, var(--g700) 0%, var(--g500) 100%);
  color: #fff; font-size: 14.5px; font-weight: 700; letter-spacing: .3px;
  display: flex; align-items: center; justify-content: center; gap: 9px;
  box-shadow: 0 6px 20px rgba(16,185,129,.28);
  transition: transform .22s var(--E), box-shadow .22s ease;
  position: relative; overflow: hidden;
}
.btn-submit::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,.16), transparent);
  opacity: 0; transition: opacity .18s;
}
.btn-submit:hover:not(:disabled)::after { opacity: 1; }
.btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(16,185,129,.36); }
.btn-submit:active:not(:disabled) { transform: translateY(0); }
.btn-submit:disabled { opacity: .7; cursor: not-allowed; }
.btn-submit .ic-spin {
  display: none; width: 15px; height: 15px;
  border: 2px solid rgba(255,255,255,.35); border-top-color: #fff;
  border-radius: 50%; animation: spin .65s linear infinite;
}
.btn-submit.loading .ic-spin  { display: block; }
.btn-submit.loading .ic-arrow { display: none; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Admin note */
.admin-note {
  margin-top: 18px;
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(16,185,129,.05);
  border: 1px solid rgba(16,185,129,.11);
  display: flex; align-items: flex-start; gap: 9px;
}
.admin-note-icon {
  flex-shrink: 0; width: 26px; height: 26px; border-radius: 7px;
  background: rgba(5,150,105,.10); color: var(--g600);
  display: flex; align-items: center; justify-content: center; font-size: 11.5px;
  margin-top: 1px;
}
.admin-note-text { font-size: 11.5px; color: var(--s500); font-weight: 600; line-height: 1.6; }
.admin-note-text a { color: var(--g600); font-weight: 700; text-decoration: none; }
.admin-note-text a:hover { text-decoration: underline; }

/* ════════ LOADING OVERLAY — FAST + LIGHT ════════ */
#pcLoader {
  position: fixed; inset: 0; z-index: 99999;
  display: flex; align-items: center; justify-content: center;
  visibility: hidden; pointer-events: none;
}
#pcLoader.show { visibility: visible; pointer-events: auto; }

.ld-veil {
  position: absolute; inset: 0;
  background: rgba(240,255,248,.76);
  backdrop-filter: blur(8px) saturate(1.12);
  -webkit-backdrop-filter: blur(8px) saturate(1.12);
  opacity: 0;
  transition: opacity .18s ease;
}
#pcLoader.show .ld-veil { opacity: 1; }

.ld-panel {
  position: relative; z-index: 2;
  background: rgba(255,255,255,.96);
  border: 1px solid rgba(16,185,129,.13);
  border-radius: 24px;
  padding: 30px 40px 28px;
  box-shadow: 0 22px 54px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.92);
  display: flex; flex-direction: column; align-items: center; text-align: center;
  min-width: 236px;
  opacity: 0;
  transform: translateY(12px) scale(.96);
  transition: opacity .24s var(--E) .04s, transform .24s var(--E) .04s;
  will-change: opacity, transform;
}
#pcLoader.show .ld-panel { opacity: 1; transform: none; }

.ld-orbit {
  position: relative;
  width: 62px; height: 62px;
  margin: 0 auto 17px;
  display: flex; align-items: center; justify-content: center;
}
.ld-ring {
  position: absolute; inset: 0; border-radius: 50%;
  border: 2.25px solid transparent;
  will-change: transform;
}
.ld-ring:nth-child(1) {
  border-top-color: var(--g500); border-right-color: rgba(16,185,129,.25);
  animation: spinR .78s linear infinite;
}
.ld-ring:nth-child(2) {
  inset: 8px;
  border-bottom-color: var(--g400); border-left-color: rgba(52,211,153,.25);
  animation: spinR 1.15s linear infinite reverse;
}
.ld-ring:nth-child(3) {
  inset: 17px;
  border-top-color: var(--amber); border-right-color: rgba(245,158,11,.22);
  animation: spinR 1.65s linear infinite;
}
@keyframes spinR { to { transform: rotate(360deg); } }

.ld-heart {
  position: relative; z-index: 2;
  font-size: 17px; color: var(--g600);
  animation: heartBeat 1.08s ease-in-out infinite;
  will-change: transform;
}
@keyframes heartBeat {
  0%,100% { transform: scale(1); opacity: .9; }
  18%     { transform: scale(1.16); }
  36%     { transform: scale(1); }
  52%     { transform: scale(1.07); }
}
.ld-name  {
  font-family: var(--display);
  font-size: 15px; font-weight: 800;
  color: var(--s900); margin-bottom: 2px;
}
.ld-label {
  font-size: 10.5px; font-weight: 700;
  color: var(--s500); text-transform: uppercase;
  letter-spacing: .6px; margin-bottom: 14px;
}
.ld-dots  { display: flex; gap: 5px; align-items: center; justify-content: center; }
.ld-dot   {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--g400);
  animation: dotPop .72s ease-in-out infinite both;
  will-change: transform, opacity;
}
.ld-dot:nth-child(1) { animation-delay: 0s; }
.ld-dot:nth-child(2) { animation-delay: .12s; background: var(--g500); }
.ld-dot:nth-child(3) { animation-delay: .24s; background: var(--g600); }
.ld-dot:nth-child(4) { animation-delay: .36s; background: var(--amber); }
@keyframes dotPop {
  0%,80%,100% { transform: scale(.55); opacity: .35; }
  40%         { transform: scale(1.12); opacity: 1; }
}

@media (max-width: 390px) {
  .ld-panel { padding: 26px 22px 24px; min-width: unset; width: 86vw; }
  .ld-orbit { width: 58px; height: 58px; margin-bottom: 15px; }
}

/* ════════ NEXUS ALERT — Premium Redesign ════════ */
.nxa-veil {
  position: fixed; inset: 0; z-index: 99998;
  display: flex; align-items: flex-end; justify-content: center;
  padding: 0 0 env(safe-area-inset-bottom,0);
  visibility: hidden; opacity: 0;
  background: rgba(10,18,35,.32);
  backdrop-filter: blur(10px) saturate(1.2);
  transition: opacity .28s ease, visibility .28s;
}
/* center on wider screens */
@media (min-width: 540px) {
  .nxa-veil { align-items: center; padding: 20px; }
}
.nxa-veil.open { visibility: visible; opacity: 1; }

.nxa-panel {
  background: #fff;
  border-radius: 28px 28px 0 0;
  width: 100%; max-width: 100%;
  box-shadow: 0 -4px 40px rgba(15,23,42,.12), 0 -1px 0 rgba(15,23,42,.04);
  overflow: hidden;
  transform: translateY(100%);
  opacity: 1;
  transition: transform .38s var(--E);
}
@media (min-width: 540px) {
  .nxa-panel {
    border-radius: 24px;
    max-width: 400px;
    box-shadow: 0 32px 80px rgba(15,23,42,.18), 0 0 0 1px rgba(226,232,240,.5);
    transform: scale(.9) translateY(20px);
    opacity: 0;
    transition: transform .34s var(--E) .02s, opacity .26s ease .02s;
  }
}
.nxa-veil.open .nxa-panel {
  transform: translateY(0);
  opacity: 1;
}

/* Pull handle — mobile only */
.nxa-handle {
  width: 36px; height: 4px; border-radius: 99px;
  background: var(--s200);
  margin: 10px auto 0;
}
@media (min-width: 540px) { .nxa-handle { display: none; } }

/* Hidden strip — kept for JS compatibility but invisible */
.nxa-strip { display: none; }

.nxa-body {
  padding: 24px 28px 28px;
  display: flex; flex-direction: column; align-items: center; text-align: center;
}
@media (min-width: 540px) {
  .nxa-body { padding: 32px 36px 28px; }
}

/* Solid icon circle — premium feel */
.nxa-badge {
  width: 64px; height: 64px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 18px;
  /* no ::before pulse — cleaner */
}
.nxa-badge.success { background: linear-gradient(135deg, var(--g700), var(--g500)); }
.nxa-badge.error   { background: linear-gradient(135deg, #b91c1c, #ef4444); }
.nxa-badge.warning { background: linear-gradient(135deg, var(--amber2), var(--amber)); }
.nxa-badge.info    { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
/* Badge icon: white */
.nxa-ico { font-size: 26px; color: #fff; display: flex; align-items: center; justify-content: center; }

/* Icon entrance */
.nxa-veil.open .nxa-badge {
  animation: badgeIn .4s var(--E) .15s both;
}
@keyframes badgeIn {
  from { transform: scale(.5); opacity: 0; }
  to   { transform: scale(1); opacity: 1; }
}

.nxa-title {
  font-family: var(--display);
  font-size: 18px; font-weight: 900;
  color: var(--s900); margin-bottom: 8px;
  line-height: 1.25;
}
.nxa-msg {
  font-size: 13.5px; color: var(--s600);
  font-weight: 500; line-height: 1.7;
  margin-bottom: 26px;
}

/* Divider before buttons */
.nxa-actions {
  display: flex; flex-direction: column; gap: 9px; width: 100%;
  border-top: 1px solid var(--s100);
  padding-top: 20px;
}
.nxa-btn {
  width: 100%; height: 50px; border-radius: 14px; border: none; cursor: pointer;
  font-size: 14px; font-weight: 700; letter-spacing: .2px;
  transition: transform .18s var(--E), box-shadow .18s, filter .18s;
}
.nxa-btn:active { transform: scale(.98); }
.nxa-btn.primary {
  background: linear-gradient(135deg, var(--g700), var(--g500));
  color: #fff;
  box-shadow: 0 6px 18px rgba(16,185,129,.28);
}
.nxa-btn.primary:hover { filter: brightness(1.06); box-shadow: 0 10px 24px rgba(16,185,129,.38); }
.nxa-btn.secondary {
  background: var(--s100); color: var(--s600);
  font-weight: 600;
}
.nxa-btn.secondary:hover { background: var(--s200); }

/* Timer bar */
.nxa-timer {
  width: 100%; height: 2.5px;
  background: var(--s100); border-radius: 99px;
  overflow: hidden; margin-top: 18px; display: none;
}
.nxa-timer.show { display: block; }
.nxa-timer-fill {
  height: 100%; border-radius: 99px;
  background: linear-gradient(90deg, var(--g400), var(--g600));
  width: 100%; transform-origin: left;
}
@keyframes timerShrink { to { transform: scaleX(0); } }

/* ════════ RESPONSIVE ════════ */
@media (max-width: 1060px) {
  .auth-wrap { padding: 32px 36px; gap: 44px; }
}

/* ── TABLET/MOBILE breakpoint ── */
@media (max-width: 860px) {
  .auth-wrap {
    grid-template-columns: 1fr;
    padding: 24px 16px 40px;
    gap: 0;
    max-width: 480px;
    align-items: start;
  }

  /* Hide desktop brand side */
  .brand-side { display: none; }

  /* Form takes full column */
  .form-side {
    animation: fadeUp2 .65s var(--E) both;
  }

  /* Show mobile brand header inside card */
  .card-mobile-brand {
    display: flex;
    animation: fadeDown .55s var(--E) both;
  }
  @keyframes fadeDown {
    from { opacity: 0; transform: translateY(-12px); }
    to   { opacity: 1; transform: none; }
  }

  .login-card {
    padding: 28px 22px 24px;
    border-radius: 20px;
    /* Subtle lift on mobile */
    box-shadow: 0 12px 40px rgba(15,23,42,.10), 0 2px 8px rgba(15,23,42,.04);
  }

  /* Slightly tighten card title spacing on mobile */
  .card-title { font-size: 19px; margin-bottom: 4px; }
  .card-sub   { font-size: 12.5px; margin-bottom: 20px; }
  .field      { margin-bottom: 12px; }
  .field-input { height: 48px; font-size: 15px; } /* bigger touch target */
  .btn-submit  { height: 50px; font-size: 14px; }
  .admin-note  { margin-top: 14px; }

  /* Decorative elements scale down */
  .deco-leaves { width: min(220px, 48vw); }
  .deco-dots   { opacity: .10; }

  /* NxAlert: handled via its own media queries above */
}

/* ── Small phones ── */
@media (max-width: 390px) {
  .auth-wrap { padding: 16px 12px 32px; }
  .login-card { padding: 22px 16px 20px; border-radius: 18px; }
  .card-mobile-brand .m-logo { width: 140px; }
}

/* ── Reduced motion ── */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: .01ms !important;
    transition-duration: .01ms !important;
  }
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
          <circle cx="{{ 8+$c*16 }}" cy="{{ 8+$r*16 }}" r="2.2"/>
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
  <svg viewBox="0 0 1440 140" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:auto">
    <path fill="#10b981" fill-opacity=".055" d="M0,110C280,75 560,104 840,110C1110,116 1310,88 1440,72L1440,140L0,140Z"/>
    <path fill="#0f766e" fill-opacity=".06"  d="M0,126C320,100 620,120 900,128C1150,136 1330,108 1440,94L1440,140L0,140Z"/>
  </svg>
</div>

{{-- ════════ MAIN LAYOUT ════════ --}}
<main aria-label="Halaman masuk PosyanduCare">
<div class="auth-wrap">

  {{-- ── BRAND SIDE (desktop only) ── --}}
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

      {{-- ── Mobile-only brand header (logo at top of card) ── --}}
      <div class="card-mobile-brand" aria-hidden="true">
        <img src="{{ asset('img/logo.png') }}"
             alt="Logo PosyanduCare"
             class="m-logo">
        <p class="m-tagline">Sehat Bersama, Tumbuh Setiap Generasi</p>
      </div>

      @yield('content')
    </div>
  </section>

</div>
</main>

{{-- ════════ LOADING OVERLAY ════════ --}}
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

{{-- ════════ NEXUS ALERT ════════ --}}
<div class="nxa-veil" id="nxaVeil" role="dialog" aria-modal="true" aria-live="assertive">
  <div class="nxa-panel" role="document">
    <div class="nxa-handle"></div>
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
    success:{ico:'fa-circle-check',  strip:'success',badge:'success'},
    error:  {ico:'fa-circle-xmark',  strip:'error',  badge:'error'},
    warning:{ico:'fa-triangle-exclamation',strip:'warning',badge:'warning'},
    info:   {ico:'fa-circle-info',   strip:'info',   badge:'info'},
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
    /* Primary button first (top) */
    var b=document.createElement('button');
    b.className='nxa-btn primary';b.textContent=o.confirmText||'Tutup';
    b.onclick=function(){close();if(o.onConfirm)o.onConfirm();};
    acts.appendChild(b);
    /* Secondary button below */
    if(o.cancelText){
      var c=document.createElement('button');
      c.className='nxa-btn secondary';c.textContent=o.cancelText;
      c.onclick=function(){close();if(o.onCancel)o.onCancel();};
      acts.appendChild(c);
    }
    if(o.timer){
      timer.classList.add('show');
      fill.style.animation='none';fill.offsetHeight;
      fill.style.animation='timerShrink '+o.timer+'ms linear forwards';
      _t=setTimeout(close,o.timer);
    }else{timer.classList.remove('show');}
    veil.classList.add('open');
    setTimeout(function(){b.focus();},60);
  }

  veil.addEventListener('click',function(e){if(e.target===veil)close();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&veil.classList.contains('open'))close();});
  return{fire:fire,close:close};
})();

/* ── LOADER ENGINE — FAST + SAFE ── */
window.PcLoader=(function(){
  var el=document.getElementById('pcLoader');
  var label=el?el.querySelector('.ld-label'):null;
  var fallbackTimer;

  return{
    show:function(text){
      if(!el)return;
      if(text&&label)label.textContent=text;
      clearTimeout(fallbackTimer);
      document.body.classList.add('pc-lock');
      el.classList.add('show');

      /* Pengaman biar loader tidak nyangkut kalau browser/validasi manusiawi mulai bertingkah. */
      fallbackTimer=setTimeout(function(){
        document.body.classList.remove('pc-lock');
        el.classList.remove('show');
      },5200);
    },
    hide:function(){
      if(!el)return;
      clearTimeout(fallbackTimer);
      document.body.classList.remove('pc-lock');
      el.classList.remove('show');
    }
  };
})();

/* ── AUTO BIND LOGIN FORM ── */
document.addEventListener('DOMContentLoaded',function(){
  var form=document.getElementById('loginFormEngine');
  if(!form||form.dataset.bound)return;
  form.dataset.bound='1';

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

    /*
      Delay lama 1600ms dipangkas.
      Loader tetap terasa premium, tapi tidak bikin user menunggu seperti antre fotokopi KTP.
    */
    setTimeout(function(){
      PcLoader.show('Membuka Portal');
      setTimeout(function(){form.submit();},420);
    },80);
  });
});
</script>

@stack('scripts')
</body>
</html>