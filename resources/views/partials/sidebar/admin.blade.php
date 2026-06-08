@php
    $admin = auth()->user();
    $adminName = $admin->name ?? 'Administrator';
    $initial = strtoupper(substr($adminName, 0, 1));

    $isDashboard = request()->routeIs('admin.dashboard');
    $isUsers = request()->routeIs('admin.users.*');
    $isBidans = request()->routeIs('admin.bidans.*');
    $isKaders = request()->routeIs('admin.kaders.*');
@endphp

<style>
    .pc-sidebar {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .pc-sidebar::-webkit-scrollbar,
    .pc-sidebar *::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .side-shell {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .side-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 18px 16px 20px;
        margin-bottom: 16px;
        border-radius: 26px;
        background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,255,252,.88));
        border: 1px solid rgba(226,232,240,.78);
        box-shadow: 0 16px 34px rgba(15,23,42,.055);
    }

    .side-logo img {
        width: 152px;
        max-width: 86%;
        max-height: 82px;
        object-fit: contain;
        display: block;
        filter: drop-shadow(0 8px 15px rgba(15,23,42,.08));
    }

    .side-user {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        padding: 16px;
        margin: 0 2px 24px;
        border-radius: 24px;
        background:
            radial-gradient(circle at 18% 22%, rgba(16,185,129,.14), transparent 34%),
            linear-gradient(135deg, rgba(255,255,255,.94), rgba(240,253,250,.82));
        border: 1px solid rgba(16,185,129,.20);
        box-shadow:
            0 16px 34px rgba(15,23,42,.055),
            inset 0 1px 0 rgba(255,255,255,.96);
    }

    .side-avatar {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 21px;
        font-weight: 900;
        background: linear-gradient(135deg, #22c55e 0%, #10b981 50%, #f59e0b 100%);
        box-shadow:
            0 14px 26px rgba(16,185,129,.22),
            inset 0 1px 0 rgba(255,255,255,.25);
    }

    .side-user-meta {
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .side-user-name {
        width: 100%;
        max-width: 148px;
        margin: 0;
        color: #064e3b;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.15;
        letter-spacing: -.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .side-user-role {
        margin: 4px 0 8px;
        color: #64748b;
        font-size: 11.5px;
        font-weight: 800;
        line-height: 1;
    }

    .side-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 28px;
        padding: 6px 11px;
        border-radius: 999px;
        background: rgba(236,253,245,.96);
        border: 1px solid rgba(16,185,129,.13);
        color: #047857;
        font-size: 10.5px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .side-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #10b981;
        box-shadow: 0 0 0 4px rgba(16,185,129,.12);
        flex-shrink: 0;
    }

    .side-group {
        margin-bottom: 22px;
        position: relative;
        z-index: 2;
    }

    .side-title {
        margin: 0 0 10px 6px;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .10em;
    }

    .side-menu {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .side-link,
    .side-logout {
        position: relative;
        width: 100%;
        min-height: 46px;
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 11px 14px;
        border: 0;
        border-radius: 15px;
        background: transparent;
        color: #334155;
        text-decoration: none;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: background .10s ease, color .10s ease, transform .10s ease;
    }

    .side-link:hover,
    .side-link.active {
        background: linear-gradient(90deg, rgba(236,253,245,.98), rgba(255,255,255,.86));
        color: #047857;
    }

    .side-link:hover {
        transform: translateX(2px);
    }

    .side-link.active {
        box-shadow:
            0 10px 22px rgba(16,185,129,.07),
            inset 0 1px 0 rgba(255,255,255,.90);
    }

    .side-link.active::before {
        content: "";
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 4px;
        border-radius: 999px;
        background: linear-gradient(180deg, #10b981, #059669);
    }

    .side-icon {
        width: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 14px;
        flex-shrink: 0;
        transition: color .10s ease;
    }

    .side-link:hover .side-icon,
    .side-link.active .side-icon {
        color: #059669;
    }

    .side-text {
        min-width: 0;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
    }

    .side-logout {
        color: #ef4444;
    }

    .side-logout .side-icon {
        color: #ef4444;
    }

    .side-logout:hover {
        background: #fff1f2;
        color: #dc2626;
    }

    .side-logout:hover .side-icon {
        color: #dc2626;
    }

    .side-bottom {
        margin-top: auto;
        height: 96px;
        position: relative;
        pointer-events: none;
        overflow: hidden;
        opacity: .82;
    }

    .side-bottom::before {
        content: "";
        position: absolute;
        left: -30px;
        right: -30px;
        bottom: -56px;
        height: 108px;
        border-radius: 50% 50% 0 0;
        background:
            radial-gradient(circle at 72% 18%, rgba(16,185,129,.26), transparent 18%),
            linear-gradient(160deg, rgba(16,185,129,.14), rgba(14,165,233,.08));
    }

    .side-bottom::after {
        content: "";
        position: absolute;
        right: 38px;
        bottom: 18px;
        width: 72px;
        height: 52px;
        border-radius: 80% 0 80% 0;
        background: rgba(16,185,129,.32);
        transform: rotate(-18deg);
    }

    @media (max-height: 720px) {
        .side-logo {
            padding: 14px 14px 16px;
            margin-bottom: 12px;
        }

        .side-logo img {
            width: 132px;
        }

        .side-user {
            padding: 13px;
            margin-bottom: 18px;
        }

        .side-avatar {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            font-size: 19px;
        }

        .side-group {
            margin-bottom: 16px;
        }

        .side-link,
        .side-logout {
            min-height: 42px;
            padding: 9px 13px;
        }

        .side-bottom {
            height: 58px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .side-link,
        .side-logout,
        .side-icon {
            transition: none !important;
        }
    }
</style>

<div class="side-shell">

    <div class="side-logo">
        <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard Admin">
            <img
                src="{{ asset('img/logo.webp') }}"
                alt="Logo PosyanduCare"
                onerror="this.src='{{ asset('img/logo.png') }}'"
            >
        </a>
    </div>

    <div class="side-user">
        <div class="side-avatar">
            {{ $initial }}
        </div>

        <div class="side-user-meta">
            <h4 class="side-user-name" title="{{ $adminName }}">
                {{ $adminName }}
            </h4>

            <div class="side-user-role">
                Administrator
            </div>

            <div class="side-status">
                <span class="side-status-dot"></span>
                <span>Akses Admin Aktif</span>
            </div>
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Menu Utama</div>

        <div class="side-menu">
            <a href="{{ route('admin.dashboard') }}"
               class="side-link {{ $isDashboard ? 'active' : '' }}">
                <span class="side-icon">
                    <i class="fa-solid fa-house"></i>
                </span>
                <span class="side-text">Dashboard</span>
            </a>
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Manajemen Akun</div>

        <div class="side-menu">
            <a href="{{ route('admin.users.index') }}"
               class="side-link {{ $isUsers ? 'active' : '' }}">
                <span class="side-icon">
                    <i class="fa-solid fa-users"></i>
                </span>
                <span class="side-text">Kelola Warga</span>
            </a>

            <a href="{{ route('admin.bidans.index') }}"
               class="side-link {{ $isBidans ? 'active' : '' }}">
                <span class="side-icon">
                    <i class="fa-solid fa-user-doctor"></i>
                </span>
                <span class="side-text">Kelola Bidan</span>
            </a>

            <a href="{{ route('admin.kaders.index') }}"
               class="side-link {{ $isKaders ? 'active' : '' }}">
                <span class="side-icon">
                    <i class="fa-solid fa-user-nurse"></i>
                </span>
                <span class="side-text">Kelola Kader</span>
            </a>
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Sesi Akun</div>

        <div class="side-menu">
            <form method="POST" action="{{ route('logout') }}" class="js-logout-form">
                @csrf

                <button type="submit" class="side-logout">
                    <span class="side-icon">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </span>
                    <span class="side-text">Keluar</span>
                </button>
            </form>
        </div>
    </div>

    <div class="side-bottom"></div>
</div>