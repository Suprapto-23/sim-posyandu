{{--
    resources/views/layouts/partials/fast-nav.blade.php

    NAVIGASI CEPAT TANPA ROUTER JAVASCRIPT
    ---------------------------------------------------------------
    Setiap pindah halaman = pemuatan halaman normal (sama seperti
    "setelah reload", kondisi yang sudah terbukti benar), tetapi:

      1. Speculation Rules  -> halaman tujuan disiapkan (prerender) saat
                               kursor mendekat / jari menyentuh link sidebar,
                               sehingga saat diklik langsung tampil.
      2. View Transitions   -> layar lama tetap tampil sampai layar baru siap,
                               lalu crossfade singkat. Tidak ada kedipan putih.
      3. Bar tipis di atas  -> hanya muncul jika navigasi lebih lama dari 160 ms.

    Browser tanpa dukungan (Firefox, Safari lama) otomatis memakai navigasi
    biasa. Tidak ada yang rusak.

    Dipakai di: layouts/admin, bidan, kader, user (di dalam <head>).
--}}
<style>
    /* ── 1. CROSSFADE ANTAR HALAMAN (cross-document view transition) ── */
    @view-transition { navigation: auto; }

    /* Durasi pendek; old & new memakai durasi yang sama agar tidak "berkedip" */
    ::view-transition-group(root) {
        animation-duration: .16s;
        animation-timing-function: ease-out;
    }

    /* ── 2. BAR PROGRES (hanya jika navigasi > 160 ms) ── */
    #pc-nav-bar {
        position: fixed; top: 0; left: 0; width: 100%; height: 3px;
        z-index: 2147483000; pointer-events: none;
        background: linear-gradient(90deg, #10b981, #34d399, #059669);
        transform-origin: left; transform: scaleX(0); opacity: 0;
        transition: opacity .15s ease;
    }
    #pc-nav-bar.is-on {
        opacity: 1; transform: scaleX(.85);
        transition: transform 6s cubic-bezier(.1, .8, .2, 1), opacity .15s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        ::view-transition-group(*), ::view-transition-old(*), ::view-transition-new(*) { animation: none !important; }
        #pc-nav-bar.is-on { transition: opacity .15s ease; transform: scaleX(1); }
    }
</style>

{{-- 3. PRERENDER link sidebar saat hover (200 ms) / sentuh. Chrome & Edge; browser lain mengabaikan.
        Hanya link di <aside> dan bottom-nav (menu utama, halaman index yang aman di-GET).
        Link lain bisa ikut dengan menambah atribut data-prerender="1". --}}
<script type="speculationrules">
{
  "prerender": [{
    "where": {
      "and": [
        { "href_matches": "/*" },
        { "or": [
            { "selector_matches": "aside a[href]" },
            { "selector_matches": "nav.bottom-nav a[href]" },
            { "selector_matches": "a[data-prerender]" }
        ]},
        { "not": { "selector_matches": "[target=_blank], [download], [data-no-prerender]" } }
      ]
    },
    "eagerness": "moderate"
  }]
}
</script>

<script>
(function () {
    'use strict';

    var PC = window.PC = window.PC || {};

    /* ── Polling aman: jeda saat tab tersembunyi, tidak mulai saat halaman masih di-prerender ── */
    PC.poll = function (fn, ms) {
        var id = null;
        function tick() { if (!document.hidden) fn(); }
        function begin() { fn(); id = setInterval(tick, ms); }
        if (document.prerendering) document.addEventListener('prerenderingchange', begin, { once: true });
        else begin();
        return function stop() { if (id) clearInterval(id); id = null; };
    };

    /* ── Posisi scroll sidebar dipertahankan antar halaman ── */
    var SB_KEY = 'pc_sidebar_scroll';
    function sbEls() {
        return [document.querySelector('.pc-sidebar'), document.querySelector('[id$="SidebarScrollArea"]')];
    }
    PC.saveSb = function () {
        try {
            var e = sbEls();
            sessionStorage.setItem(SB_KEY, JSON.stringify([e[0] ? e[0].scrollTop : 0, e[1] ? e[1].scrollTop : 0]));
        } catch (_) {}
    };
    PC.restoreSb = function () {
        try {
            var v = JSON.parse(sessionStorage.getItem(SB_KEY) || 'null');
            if (!v) return;
            var e = sbEls();
            if (e[0]) e[0].scrollTop = v[0];
            if (e[1]) e[1].scrollTop = v[1];
        } catch (_) {}
    };
    document.addEventListener('DOMContentLoaded', PC.restoreSb);

    /* ── Bar progres ── */
    var barTimer = null, bar = null;
    function getBar() {
        if (!bar) {
            bar = document.getElementById('pc-nav-bar');
            if (!bar && document.body) {
                bar = document.createElement('div');
                bar.id = 'pc-nav-bar';
                bar.setAttribute('aria-hidden', 'true');
                document.body.appendChild(bar);
            }
        }
        return bar;
    }
    function startBar(evt) {
        clearTimeout(barTimer);
        barTimer = setTimeout(function () {
            if (evt.defaultPrevented) return;          // link/form ditangani AJAX oleh halaman → tidak ada navigasi
            var b = getBar(); if (b) b.classList.add('is-on');
        }, 160);
    }
    function stopBar() {
        clearTimeout(barTimer);
        var b = getBar(); if (b) b.classList.remove('is-on');
    }
    window.addEventListener('pageshow', stopBar);

    document.addEventListener('click', function (e) {
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || a.target === '_blank' || a.hasAttribute('download')) return;

        var u;
        try { u = new URL(a.href, location.href); } catch (_) { return; }
        if (u.origin !== location.origin) return;
        if (u.pathname === location.pathname && u.search === location.search) return;   // sama / hanya #hash

        // Link di sidebar: simpan scroll sidebar; di mobile tutup sidebar agar tidak "menggantung"
        if (a.closest('aside')) {
            PC.saveSb();
            var ov = document.querySelector('.mobile-overlay');
            if (ov && getComputedStyle(ov).display !== 'none' && typeof window.setSidebar === 'function') {
                window.setSidebar(false, false);
            }
        }
        startBar(e);
    });

    document.addEventListener('submit', function (e) { startBar(e); });

    /* ── Kompatibilitas: sisa pemanggilan navigateTo() dari view lama tetap berfungsi ── */
    window.navigateTo = window.navigateTo || function (url) { window.location.href = url; };
})();
</script>
