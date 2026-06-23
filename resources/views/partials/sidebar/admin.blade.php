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
    /* =========================================
       SIDEBAR SCROLLBAR HIDING
       ========================================= */
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

    /* =========================================
       MAIN SHELL & LAYOUT
       ========================================= */
    .side-shell {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        padding: 20px 16px;
        background: #ffffff; /* Latar belakang bersih */
    }

    /* =========================================
       LOGO
       ========================================= */
    .side-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-bottom: 24px;
        margin-bottom: 24px;
        border-bottom: 1px dashed #e2e8f0; /* Pemisah yang rapi */
    }
    .side-logo img {
        width: 140px;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.2s ease;
    }
    .side-logo:hover img {
        transform: scale(1.02);
    }

    /* =========================================
       USER PROFILE CARD
       ========================================= */
    .side-user {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        margin-bottom: 30px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }
    .side-user:hover {
        background: #f0fdf4;
        border-color: #d1fae5;
    }
    .side-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 18px;
        font-weight: 800;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }
    .side-user-meta {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .side-user-name {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .side-user-role {
        color: #64748b;
        font-size: 12px;
        font-weight: 500;
        margin-top: 2px;
    }
    .side-status {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        color: #059669;
        font-size: 11px;
        font-weight: 600;
    }
    .side-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    /* =========================================
       NAVIGATION MENUS
       ========================================= */
    .side-group {
        margin-bottom: 24px;
    }
    .side-title {
        margin: 0 0 8px 12px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .side-menu {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .side-link,
    .side-logout {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border: 0;
        border-radius: 12px;
        background: transparent;
        color: #475569;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    
    .side-icon {
        width: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: #94a3b8;
        transition: color 0.15s ease;
    }

    /* Hover State */
    .side-link:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: translateX(4px);
    }
    .side-link:hover .side-icon {
        color: #10b981;
    }

    /* Active State */
    .side-link.active {
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
    }
    .side-link.active .side-icon {
        color: #059669;
    }
    
    /* Tombol Logout */
    .side-logout {
        width: 100%;
        color: #ef4444;
        margin-top: auto;
    }
    .side-logout .side-icon {
        color: #f87171;
    }
    .side-logout:hover {
        background: #fef2f2;
        color: #dc2626;
        transform: translateX(4px);
    }
    .side-logout:hover .side-icon {
        color: #dc2626;
    }

    /* Spacer untuk mendorong logout ke bawah jika ruang tersisa */
    .flex-grow-spacer {
        flex-grow: 1;
    }

    /* =========================================
       RESPONSIVE ADJUSTMENTS
       ========================================= */
    @media (max-height: 720px) {
        .side-shell { padding: 16px 12px; }
        .side-logo { margin-bottom: 16px; padding-bottom: 16px; }
        .side-user { margin-bottom: 20px; padding: 10px; }
        .side-avatar { width: 40px; height: 40px; font-size: 16px; }
        .side-group { margin-bottom: 16px; }
        .side-link, .side-logout { padding: 8px 12px; font-size: 13px; }
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
            <div class="side-user-role">Administrator</div>
            <div class="side-status">
                <span class="side-status-dot"></span>
                <span>Aktif</span>
            </div>
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Menu Utama</div>
        <div class="side-menu">
            <a href="{{ route('admin.dashboard') }}" 
               class="side-link {{ $isDashboard ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-house"></i></span>
                <span class="side-text">Dashboard</span>
            </a>
        </div>
    </div>

    <div class="side-group">
        <div class="side-title">Manajemen Akun</div>
        <div class="side-menu">
            <a href="{{ route('admin.users.index') }}" 
               class="side-link {{ $isUsers ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-users"></i></span>
                <span class="side-text">Kelola Warga</span>
            </a>
            <a href="{{ route('admin.bidans.index') }}" 
               class="side-link {{ $isBidans ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-user-doctor"></i></span>
                <span class="side-text">Kelola Bidan</span>
            </a>
            <a href="{{ route('admin.kaders.index') }}" 
               class="side-link {{ $isKaders ? 'active' : '' }}">
                <span class="side-icon"><i class="fa-solid fa-user-nurse"></i></span>
                <span class="side-text">Kelola Kader</span>
            </a>
        </div>
    </div>

    <div class="flex-grow-spacer"></div>

    <div class="side-group" style="margin-bottom: 0;">
        <div class="side-title">Sesi Akun</div>
        <div class="side-menu">
            <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0">
                @csrf
                <button type="submit" class="side-logout">
                    <span class="side-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                    <span class="side-text">Keluar</span>
                </button>
            </form>
        </div>
    </div>

</div>