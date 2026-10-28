@php
$getSetting = Helper::getSetting();
$sidebarData = DB::table('students_sidebar')->whereNull('deleted_at')->orderBy('id', 'ASC')->get();
$admission = DB::table('admissions')->whereNull('deleted_at')->find(Session::get('id'));
@endphp

<style>
/* ========================================================
   STUDENT SIDEBAR - MODERN EXCEL GREEN THEME (#217346)
   ======================================================== */
.main-sidebar.student-executive-sidebar {
    background: linear-gradient(180deg, #0f291e 0%, #0b1f17 100%) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.18) !important;
    overflow-x: hidden !important;
}

.student-sidebar-brand {
    height: 54px;
    padding: 8px 12px;
    background: #0a1f16;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none !important;
}

.student-brand-logo-frame {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 8px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    border: 1.5px solid #86efac;
    box-shadow: 0 2px 5px rgba(0,0,0,0.25);
}
.student-brand-logo-frame img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 6px;
}

.student-brand-text {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    line-height: 1.2;
}
.student-brand-name {
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    margin: 0;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.student-brand-sub {
    color: #86efac;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

.student-nav-list {
    list-style: none;
    padding: 0 8px !important;
    margin: 0 !important;
}

.student-executive-sidebar .sidebar,
body.layout-navbar-fixed .student-executive-sidebar .sidebar,
.layout-navbar-fixed.layout-fixed .wrapper .student-executive-sidebar .sidebar {
    margin-top: 0 !important;
    padding: 6px 0 30px 0 !important;
    overflow-y: auto;
    overflow-x: hidden;
}

.student-nav-list .nav-item {
    margin-bottom: 3px;
    list-style: none;
}

/* Expanded Mode */
.student-nav-list .nav-link {
    display: flex !important;
    align-items: center !important;
    padding: 7px 10px !important;
    border-radius: 7px !important;
    color: #cbd5e1 !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
    border: 1px solid transparent !important;
    background: transparent;
}

.student-nav-list .nav-link .nav-icon {
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
    color: #86efac !important;
    font-size: 13px !important;
    line-height: 1 !important;
    text-align: center !important;
    float: none !important;
    transition: all 0.15s ease !important;
    flex-shrink: 0;
}

.student-nav-list .nav-link p {
    margin: 0 !important;
    font-size: 12px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    color: inherit !important;
}

.student-nav-list .nav-link:hover {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #ffffff !important;
}
.student-nav-list .nav-link:hover .nav-icon {
    background: rgba(33, 115, 70, 0.4) !important;
    color: #ffffff !important;
    border-color: #86efac !important;
}

.student-nav-list .nav-link.active {
    background: linear-gradient(135deg, #217346 0%, #166534 100%) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25) !important;
    border-color: rgba(134, 239, 172, 0.3) !important;
}
.student-nav-list .nav-link.active .nav-icon {
    background: #ffffff !important;
    color: #166534 !important;
    border-color: #ffffff !important;
}

.student-nav-list .nav-link.link-logout .nav-icon {
    color: #f87171 !important;
    background: rgba(248, 113, 113, 0.1) !important;
    border-color: rgba(248, 113, 113, 0.2) !important;
}
.student-nav-list .nav-link.link-logout:hover {
    background: rgba(248, 113, 113, 0.18) !important;
    color: #ffffff !important;
}

/* ========================================================
   MINI-SIDEBAR COLLAPSED MODE (SINGLE UNIFIED SQUARE BUTTONS)
   ======================================================== */
body.sidebar-collapse .student-executive-sidebar,
body.sidebar-collapse .student-executive-sidebar:hover,
body.sidebar-collapse .student-executive-sidebar.sidebar-focused,
body.sidebar-collapse .student-executive-sidebar::before,
body.sidebar-collapse .student-executive-sidebar:hover::before {
    width: 4.6rem !important;
    min-width: 4.6rem !important;
    max-width: 4.6rem !important;
    transition: none !important;
}

body.sidebar-collapse .student-brand-text,
body.sidebar-collapse .student-executive-sidebar:hover .student-brand-text,
body.sidebar-collapse .student-nav-list .nav-link p,
body.sidebar-collapse .student-executive-sidebar:hover .student-nav-list .nav-link p {
    display: none !important;
    opacity: 0 !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
}

body.sidebar-collapse .student-sidebar-brand,
body.sidebar-collapse .student-executive-sidebar:hover .student-sidebar-brand {
    justify-content: center !important;
    padding: 8px 0 !important;
    gap: 0 !important;
}

body.sidebar-collapse .student-nav-list {
    padding: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
}

body.sidebar-collapse .student-nav-list .nav-item {
    width: 100% !important;
    display: flex !important;
    justify-content: center !important;
    margin-bottom: 5px !important;
}

body.sidebar-collapse .student-nav-list .nav-link,
body.sidebar-collapse .student-nav-list .nav-link:hover,
body.sidebar-collapse .student-executive-sidebar:hover .student-nav-list .nav-link {
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
    color: #86efac !important;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
}

body.sidebar-collapse .student-nav-list .nav-link:hover,
body.sidebar-collapse .student-executive-sidebar:hover .student-nav-list .nav-link:hover {
    background: rgba(33, 115, 70, 0.4) !important;
    border-color: #86efac !important;
    color: #ffffff !important;
    box-shadow: 0 0 8px rgba(134, 239, 172, 0.3) !important;
}

body.sidebar-collapse .student-nav-list .nav-link.active,
body.sidebar-collapse .student-executive-sidebar:hover .student-nav-list .nav-link.active {
    background: linear-gradient(135deg, #217346 0%, #166534 100%) !important;
    border-color: #86efac !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

body.sidebar-collapse .student-nav-list .nav-link .nav-icon,
body.sidebar-collapse .student-nav-list .nav-link:hover .nav-icon,
body.sidebar-collapse .student-nav-list .nav-link.active .nav-icon {
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
</style>

<aside class="main-sidebar student-executive-sidebar sidebar-no-expand">
    <!-- Brand Logo -->
    <a href="{{ url('/') }}" class="student-sidebar-brand" title="{{ $getSetting->name ?? 'Student Portal' }}">
        <div class="student-brand-logo-frame">
            <img src="{{ env('IMAGE_SHOW_PATH').'/setting/left_logo/'.($getSetting['left_logo'] ?? '') }}" alt="Logo" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/no_image.png' }}'">
        </div>
        <div class="student-brand-text">
            <span class="student-brand-name">{{ $getSetting->name ?? 'Student Portal' }}</span>
            <span class="student-brand-sub">Student ERP</span>
        </div>
    </a>

    <!-- Navigation Menu -->
    <div class="sidebar">
        <nav class="mt-1">
            <ul class="student-nav-list nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                    @if(!empty($sidebarData))
                        @foreach($sidebarData as $item)
                        <li class="nav-item">
                            <a href="{{ url($item->url) }}{{ $item->url == 'student_fees_details' ? '/'.Session::get('id') : '' }}" class="nav-link {{ url($item->url) == URL::current() ? 'active' : '' }}" title="{{ $item->name ?? '' }}">
                                <i class="nav-icon {{ !empty($item->ican) ? $item->ican : 'fa fa-circle-o' }}"></i>
                                <p>{{ $item->name ?? '' }}</p>
                            </a>
                        </li>
                        @endforeach
                    @endif
                
                <!--@if(!empty($admission->student_profile_update) && $admission->student_profile_update !== 'no')-->
                <!--    @if(!empty($sidebarData))-->
                <!--        @foreach($sidebarData as $item)-->
                <!--        <li class="nav-item">-->
                <!--            <a href="{{ url($item->url) }}{{ $item->url == 'student_fees_details' ? '/'.Session::get('id') : '' }}" class="nav-link {{ url($item->url) == URL::current() ? 'active' : '' }}" title="{{ $item->name ?? '' }}">-->
                <!--                <i class="nav-icon {{ !empty($item->ican) ? $item->ican : 'fa fa-circle-o' }}"></i>-->
                <!--                <p>{{ $item->name ?? '' }}</p>-->
                <!--            </a>-->
                <!--        </li>-->
                <!--        @endforeach-->
                <!--    @endif-->
                <!--@endif-->

                <li style="height: 1px; background: rgba(255,255,255,0.07); width: 32px; margin: 6px auto;"></li>

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