@php
$sidebar = Helper::getSiderbar();
$getPermisn = Helper::getPermisn();
$getPermisnByBranch = Helper::getPermisnByBranch();
$getSetting = Helper::getSetting();
$allPermisn = !empty($getPermisn['sidebar_id']) ? explode(',', $getPermisn['sidebar_id']) : [];
@endphp

<style>
/* ========================================================
   EXECUTIVE SIDEBAR - MODERN EXCEL GREEN THEME (#217346)
   ======================================================== */
:root {
    --side-bg-dark: #0f291e;
    --side-bg-header: #0a1f16;
    --side-active-green: #217346;
    --side-active-green-hover: #165b35;
    --side-text: #cbd5e1;
    --side-text-muted: #84a395;
    --side-accent: #86efac;
    --side-border: rgba(255, 255, 255, 0.08);
}

/* Sidebar Container */
.main-sidebar.executive-sidebar {
    background: linear-gradient(180deg, #0f291e 0%, #0b1f17 100%) !important;
    border-right: 1px solid var(--side-border) !important;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.18) !important;
    overflow-x: hidden !important;
}

/* Brand Section (Top Logo & Title) */
.sidebar-brand-wrapper {
    height: 54px;
    padding: 8px 12px;
    background: var(--side-bg-header);
    border-bottom: 1px solid var(--side-border);
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none !important;
    transition: background 0.2s ease;
}
.sidebar-brand-wrapper:hover {
    background: #0d261b;
}

.sidebar-brand-logo-frame {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 8px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.25);
    border: 1.5px solid var(--side-accent);
}
.sidebar-brand-logo-frame img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 6px;
}

.sidebar-brand-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    line-height: 1.2;
}
.sidebar-brand-name {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    letter-spacing: 0.3px;
    margin: 0;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.sidebar-brand-sub {
    color: var(--side-accent);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

/* Sidebar Scroll Area */
.executive-sidebar .sidebar,
body.layout-navbar-fixed .executive-sidebar .sidebar,
.layout-navbar-fixed.layout-fixed .wrapper .executive-sidebar .sidebar,
.layout-navbar-fixed .wrapper .executive-sidebar .sidebar {
    margin-top: 0 !important;
    padding: 6px 0 30px 0 !important;
    overflow-y: auto;
    overflow-x: hidden;
}
.executive-sidebar .sidebar::-webkit-scrollbar {
    width: 4px;
}
.executive-sidebar .sidebar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
}
.executive-sidebar .sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}
.executive-sidebar .sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--side-accent);
}

/* Navigation List */
.executive-nav {
    list-style: none;
    padding: 0 8px !important;
    margin: 0 !important;
}

.executive-nav .nav-item {
    margin-bottom: 3px;
    list-style: none;
}

/* ========================================================
   EXPANDED MODE LINK STYLES
   ======================================================== */
.executive-nav .nav-link {
    display: flex !important;
    align-items: center !important;
    padding: 7px 10px !important;
    border-radius: 7px !important;
    color: var(--side-text) !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
    border: 1px solid transparent !important;
    background: transparent;
}

.executive-nav .nav-link .nav-icon {
    width: 28px !important;
    height: 28px !important;
    min-width: 28px !important;
    max-width: 28px !important;
    border-radius: 6px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-right: 10px !important;
    margin-left: 0 !important;
    color: var(--side-accent) !important;
    font-size: 13px !important;
    line-height: 1 !important;
    text-align: center !important;
    float: none !important;
    transition: all 0.15s ease !important;
    flex-shrink: 0;
}

.executive-nav .nav-link p {
    margin: 0 !important;
    font-size: 12px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    line-height: 1.3 !important;
    font-weight: 500 !important;
    color: inherit !important;
}

.executive-nav .nav-link:hover {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #ffffff !important;
}
.executive-nav .nav-link:hover .nav-icon {
    background: rgba(33, 115, 70, 0.4) !important;
    color: #ffffff !important;
    border-color: var(--side-accent) !important;
    box-shadow: 0 0 8px rgba(134, 239, 172, 0.3) !important;
}

.executive-nav .nav-link.active {
    background: linear-gradient(135deg, #217346 0%, #166534 100%) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    border-color: rgba(134, 239, 172, 0.3) !important;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25) !important;
}
.executive-nav .nav-link.active .nav-icon {
    background: #ffffff !important;
    color: #166534 !important;
    border-color: #ffffff !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
}

/* Special Items */
.sidebar-menu-divider {
    height: 1px;
    background: var(--side-border);
    margin: 8px 6px;
    list-style: none;
}

.nav-link.link-help .nav-icon {
    color: #facc15 !important;
    background: rgba(250, 204, 21, 0.1) !important;
    border-color: rgba(250, 204, 21, 0.2) !important;
}
.nav-link.link-help:hover {
    background: rgba(250, 204, 21, 0.15) !important;
    color: #ffffff !important;
}

.nav-link.link-logout .nav-icon {
    color: #f87171 !important;
    background: rgba(248, 113, 113, 0.1) !important;
    border-color: rgba(248, 113, 113, 0.2) !important;
}
.nav-link.link-logout:hover {
    background: rgba(248, 113, 113, 0.18) !important;
    color: #ffffff !important;
}

/* ========================================================
   MINI-SIDEBAR COLLAPSED MODE (SINGLE UNIFIED SQUARE BUTTONS)
   ======================================================== */
body.sidebar-collapse .main-sidebar,
body.sidebar-collapse .main-sidebar:hover,
body.sidebar-collapse .main-sidebar.sidebar-focused,
body.sidebar-collapse .main-sidebar::before,
body.sidebar-collapse .main-sidebar:hover::before,
.sidebar-collapse.sidebar-mini .main-sidebar,
.sidebar-collapse.sidebar-mini .main-sidebar:hover {
    width: 4.6rem !important;
    min-width: 4.6rem !important;
    max-width: 4.6rem !important;
    transition: none !important;
}

/* Hide all text completely in collapsed mode */
body.sidebar-collapse .sidebar-brand-text,
body.sidebar-collapse .main-sidebar:hover .sidebar-brand-text,
body.sidebar-collapse .brand-text,
body.sidebar-collapse .executive-nav .nav-link p,
body.sidebar-collapse .main-sidebar:hover .executive-nav .nav-link p,
body.sidebar-collapse .nav-sidebar .nav-link p,
body.sidebar-collapse .main-sidebar:hover .nav-sidebar .nav-link p {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
    overflow: hidden !important;
}

/* Center brand logo in mini mode */
body.sidebar-collapse .sidebar-brand-wrapper,
body.sidebar-collapse .main-sidebar:hover .sidebar-brand-wrapper {
    justify-content: center !important;
    padding: 8px 0 !important;
    gap: 0 !important;
}

/* Unified square button in mini mode */
body.sidebar-collapse .executive-nav {
    padding: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
}

body.sidebar-collapse .executive-nav .nav-item {
    width: 100% !important;
    display: flex !important;
    justify-content: center !important;
    margin-bottom: 5px !important;
}

body.sidebar-collapse .executive-nav .nav-link,
body.sidebar-collapse .executive-nav .nav-link:hover,
body.sidebar-collapse .main-sidebar:hover .executive-nav .nav-link {
    width: 36px !important;
    height: 36px !important;
    min-width: 36px !important;
    max-width: 36px !important;
    min-height: 36px !important;
    max-height: 36px !important;
    padding: 0 !important;
    margin: 0 auto !important;
    border-radius: 7px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    color: var(--side-accent) !important;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
}

body.sidebar-collapse .executive-nav .nav-link:hover,
body.sidebar-collapse .main-sidebar:hover .executive-nav .nav-link:hover {
    background: rgba(33, 115, 70, 0.4) !important;
    border-color: var(--side-accent) !important;
    color: #ffffff !important;
    box-shadow: 0 0 8px rgba(134, 239, 172, 0.3) !important;
}

body.sidebar-collapse .executive-nav .nav-link.active,
body.sidebar-collapse .main-sidebar:hover .executive-nav .nav-link.active {
    background: linear-gradient(135deg, #217346 0%, #166534 100%) !important;
    border-color: var(--side-accent) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

/* In mini-mode, set icon font-size to 13px (exact same compact size as extended sidebar) */
body.sidebar-collapse .executive-nav .nav-link .nav-icon,
body.sidebar-collapse .executive-nav .nav-link:hover .nav-icon,
body.sidebar-collapse .executive-nav .nav-link.active .nav-icon {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 13px !important;
    line-height: 1 !important;
    width: auto !important;
    height: auto !important;
    color: inherit !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

body.sidebar-collapse .sidebar-menu-divider {
    width: 32px;
    margin: 6px auto;
}
</style>

<!-- Main Sidebar Container -->
<aside class="main-sidebar executive-sidebar sidebar-no-expand">
    <!-- Brand Logo Section -->
    <a href="{{ url('/') }}" class="sidebar-brand-wrapper" title="{{ $getSetting->name ?? 'Campus ERP' }}">
        <div class="sidebar-brand-logo-frame">
            <img src="{{ env('IMAGE_SHOW_PATH').'/setting/left_logo/'.($getSetting['left_logo'] ?? '') }}" alt="Logo" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/no_image.png' }}'">
        </div>
        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">{{ $getSetting->name ?? 'Campus ERP' }}</span>
            <span class="sidebar-brand-sub">Management Portal</span>
        </div>
    </a>

    <!-- Sidebar Menu -->
    <div class="sidebar">
        <nav class="mt-1">
            <ul class="executive-nav nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                @if(!empty($sidebar))
                    @foreach($sidebar as $data)
                        @foreach($allPermisn as $permisnData)
                            @if($data['id'] == $permisnData)
                            <li class="nav-item">
                                <a href="{{ url($data['url']) }}" class="nav-link {{ (url($data['url']) == URL::current() || request()->is(trim($data['url'], '/').'*') || ($data['url'] == 'college/fees/dashboard' && request()->is('college/fees*'))) ? 'active' : '' }}" title="{{ $data['name'] ?? '' }}">
                                    <i class="nav-icon {{ !empty($data['ican']) ? $data['ican'] : 'fa fa-circle-o' }}"></i>
                                    <p>
                                        @if(Session::get('locale') == 'hi')
                                            {{ $data['hindi_name'] ?? $data['name'] ?? '' }}
                                        @else
                                            {{ $data['name'] ?? '' }}
                                        @endif
                                    </p>
                                </a>
                            </li>
                            @endif
                        @endforeach
                    @endforeach
                @endif

                <li class="sidebar-menu-divider"></li>

                @if(Session::get('role_id') == 1)
                <li class="nav-item">
                    <a href="{{ url('helpAndUpdate') }}" class="nav-link link-help {{ url('helpAndUpdate') == URL::current() ? 'active' : '' }}" title="Help & Updates">
                        <i class="nav-icon fa fa-question-circle-o"></i>
                        <p>Help & Updates</p>
                    </a>
                </li>
                @endif

                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link link-logout" onclick="confirmLogout(event)" title="Log Out">
                        <i class="nav-icon fa fa-sign-out"></i>
                        <p>Log Out</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
