@php
$getSetting = Helper::getSetting();
$getstudentbirthday = Helper::getstudentbirthday();
$getUsersBirthday = Helper::getUsersBirthday();
$getUser = Helper::getUser();
$getSession = Helper::getSession();
$getNewChat = Helper::getNewChat();
$getAllBranch = Helper::getAllBranch();
$roleName = DB::table('role')->whereNull('deleted_at')->find(Session::get('role_id'));
$currentBranchName = '';
if(!empty($getAllBranch)) {
    foreach($getAllBranch as $b) {
        if(($b->id == Session::get('admin_branch_id')) || ($b->id == Session::get('branch_id'))) {
            $currentBranchName = $b->branch_name ?? '';
            break;
        }
    }
}
@endphp

<style>
/* ========================================================
   EXECUTIVE TOP NAVIGATION BAR - MODERN THEME (#217346)
   ======================================================== */
:root {
    --hdr-green: #217346;
    --hdr-green-dark: #165b35;
    --hdr-green-light: #f0fdf4;
    --hdr-green-border: #86efac;
    --hdr-text: #1e293b;
    --hdr-muted: #64748b;
    --hdr-border: #e2e8f0;
    --hdr-bg: #ffffff;
}

.main-header.navbar {
    min-height: 52px;
    height: 52px;
    background: var(--hdr-bg) !important;
    border-bottom: 1px solid var(--hdr-border) !important;
    border-top: 2px solid var(--hdr-green) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04) !important;
    padding: 0 14px !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 1034 !important;
}

/* Left Group */
.header-left-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-pushmenu-toggle {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid var(--hdr-border);
    color: #334155 !important;
    font-size: 14px;
    transition: all 0.2s ease;
    padding: 0 !important;
    flex-shrink: 0;
}
.btn-pushmenu-toggle:hover {
    background: var(--hdr-green-light);
    border-color: var(--hdr-green-border);
    color: var(--hdr-green) !important;
    transform: scale(1.04);
}

.header-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    background: #f8fafc;
    border: 1px solid var(--hdr-border);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
}
.header-brand-badge .badge-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    display: inline-block;
    flex-shrink: 0;
}

/* Right Controls Group */
.header-right-group {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

/* Control Pills (Branch, Session, Language) */
.header-pill-item {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.header-select-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.header-select-icon {
    position: absolute;
    left: 9px;
    pointer-events: none;
    font-size: 12px;
    z-index: 2;
}

.header-select-pill {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    font-size: 11.5px;
    font-weight: 600;
    height: 30px;
    padding: 2px 24px 2px 27px;
    border-radius: 6px;
    border: 1px solid var(--hdr-border);
    background-color: #f8fafc;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%2364748b' d='M0 0l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 8px 5px;
    max-width: 170px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.header-select-pill:hover {
    border-color: #cbd5e1;
    background-color: #ffffff;
}
.header-select-pill:focus {
    outline: none;
    border-color: var(--hdr-green);
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15);
    background-color: #ffffff;
}

/* Branch Specific Pill */
.pill-branch .header-select-pill {
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}
.pill-branch .header-select-pill:hover {
    border-color: #86efac;
    background-color: #dcfce7;
}
.pill-branch .header-select-icon {
    color: #16a34a;
}

/* Session / Financial Year Pill */
.header-fy-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
    padding: 0 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    color: #92400e;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    cursor: default;
    user-select: none;
    transition: all 0.2s ease;
}
.header-fy-badge:hover {
    background: #fef3c7;
    border-color: #fcd34d;
}
.header-fy-badge i {
    color: #d97706;
    font-size: 13px;
}
.pill-session .header-select-pill {
    background-color: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}
.pill-session .header-select-pill:hover {
    border-color: #fcd34d;
    background-color: #fef3c7;
}
.pill-session .header-select-icon {
    color: #d97706;
}

/* Language Specific Pill */
.pill-lang .header-select-pill {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    color: #475569;
}
.pill-lang .header-select-icon {
    color: #64748b;
}

/* Action Icon Buttons */
.btn-header-circle {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid var(--hdr-border);
    color: #475569 !important;
    font-size: 13px;
    transition: all 0.2s ease;
    text-decoration: none !important;
    position: relative;
    padding: 0 !important;
}
.btn-header-circle:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    color: var(--hdr-green) !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}
.btn-header-circle .badge-pulse {
    position: absolute;
    top: -4px;
    right: -4px;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 4px;
    border-radius: 10px;
    background: #ef4444;
    color: #ffffff;
    border: 1.5px solid #ffffff;
    box-shadow: 0 0 4px rgba(239, 68, 68, 0.4);
}

.btn-header-circle.btn-refresh:hover i {
    transform: rotate(180deg);
    transition: transform 0.4s ease;
}

/* Birthday Pulse Icon */
.btn-birthday-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 20px;
    color: #b91c1c !important;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none !important;
    animation: pulseCelebration 2s infinite ease-in-out;
}
@keyframes pulseCelebration {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* WhatsApp Queue Dropdown */
.whatsapp-menu-dropdown {
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    padding: 0 !important;
    overflow: hidden;
    min-width: 290px;
}
.whatsapp-menu-header {
    background: #f0fdf4;
    border-bottom: 1px solid #bbf7d0;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 700;
    color: #166534;
}
.whatsapp-item {
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
    text-decoration: none !important;
    display: block;
}
.whatsapp-item:hover {
    background: #f8fafc;
}
.whatsapp-item .msg-phone {
    font-weight: 600;
    font-size: 11.5px;
    color: #1e293b;
    margin-bottom: 2px;
}
.whatsapp-item .msg-snippet {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 2px;
}
.whatsapp-item .msg-time {
    font-size: 10px;
    color: #94a3b8;
}
.whatsapp-footer-link {
    background: #f8fafc;
    padding: 8px;
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: var(--hdr-green) !important;
    display: block;
    text-decoration: none !important;
}
.whatsapp-footer-link:hover {
    background: #f0fdf4;
    text-decoration: underline !important;
}

/* User Profile Trigger & Dropdown */
.header-user-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 3px 8px 3px 4px;
    border-radius: 20px;
    background: #f8fafc;
    border: 1px solid var(--hdr-border);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none !important;
}
.header-user-trigger:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}

.header-user-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid var(--hdr-green);
    background: #ffffff;
}

.header-user-info {
    display: flex;
    flex-direction: column;
    text-align: left;
    line-height: 1.15;
}
.header-user-name {
    font-size: 11.5px;
    font-weight: 700;
    color: #0f172a;
    max-width: 110px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.header-user-role {
    font-size: 9.5px;
    font-weight: 600;
    color: var(--hdr-green);
}
.header-user-caret {
    font-size: 10px;
    color: #64748b;
    margin-left: 2px;
}

/* User Dropdown Menu */
.user-menu-dropdown {
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: 0 12px 30px rgba(0,0,0,0.12) !important;
    padding: 0 !important;
    min-width: 250px;
    overflow: hidden;
    margin-top: 6px !important;
}

.user-menu-header {
    background: linear-gradient(135deg, var(--hdr-green) 0%, #15803d 100%);
    padding: 14px 14px 12px 14px;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-menu-header-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.85);
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.user-menu-header-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 3px 0;
    line-height: 1.2;
}
.user-menu-header-role {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: #f0fdf4;
    font-size: 10px;
    font-weight: 600;
    padding: 1px 8px;
    border-radius: 10px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.user-menu-body {
    padding: 6px 0;
    background: #ffffff;
}

.user-menu-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 500;
    color: #334155 !important;
    transition: all 0.15s ease;
    text-decoration: none !important;
}
.user-menu-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
}
.user-menu-link.active-header-link {
    background: var(--hdr-green-light);
    color: var(--hdr-green) !important;
    font-weight: 600;
}
.user-menu-link i {
    font-size: 14px;
    width: 18px;
    text-align: center;
}
.user-menu-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 4px 0;
}
.user-menu-link.link-logout {
    color: #dc2626 !important;
    font-weight: 600;
}
.user-menu-link.link-logout:hover {
    background: #fef2f2;
    color: #b91c1c !important;
}

/* Modern Logout Modal Confirmation */
.logout-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(2px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    animation: fadeInBackdrop 0.2s ease;
}
@keyframes fadeInBackdrop {
    from { opacity: 0; }
    to { opacity: 1; }
}
.logout-modal-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    width: 100%;
    max-width: 380px;
    padding: 24px 20px 20px 20px;
    text-align: center;
    border: 1px solid #e2e8f0;
    animation: scaleModal 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes scaleModal {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.logout-icon-ring {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #fee2e2;
    color: #ef4444;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 14px;
}
.logout-modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
}
.logout-modal-desc {
    font-size: 12.5px;
    color: #64748b;
    margin-bottom: 20px;
    line-height: 1.4;
}
.logout-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}
.btn-logout-confirm {
    background: #dc2626;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 7px 18px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
}
.btn-logout-confirm:hover {
    background: #b91c1c;
    color: #ffffff;
}
.btn-logout-cancel {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 7px 18px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-logout-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* Mobile Adjustments */
@media (max-width: 768px) {
    .header-brand-badge {
        display: none !important;
    }
    .header-user-info {
        display: none !important;
    }
    .header-user-trigger {
        padding: 2px !important;
    }
    .header-select-pill {
        max-width: 110px;
        font-size: 10.5px;
        padding-left: 22px;
    }
    .header-select-icon {
        font-size: 10px;
        left: 6px;
    }
}
@media (max-width: 576px) {
    .pill-lang {
        display: none !important;
    }
    .pill-branch {
        display: none !important;
    }
    .main-header.navbar {
        padding: 0 8px !important;
    }
}

/* ========================================================
   REAL-TIME GLOBAL STUDENT SEARCH COMPONENT
   ======================================================== */
.header-student-search-box {
    position: relative;
    flex: 1;
    max-width: 440px;
    margin: 0 12px;
}

.global-search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.global-search-input {
    width: 100%;
    height: 32px;
    background: #f8fafc;
    border: 1px solid var(--hdr-border);
    border-radius: 6px;
    padding: 0 28px 0 30px;
    font-size: 11.5px;
    color: #1e293b;
    transition: all 0.2s ease;
    outline: none;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
}

.global-search-input::placeholder {
    color: #94a3b8;
    font-size: 11px;
}

.global-search-input:focus {
    background: #ffffff;
    border-color: var(--hdr-green);
    box-shadow: 0 0 0 3px rgba(33, 115, 70, 0.12);
}

.global-search-icon {
    position: absolute;
    left: 10px;
    color: #94a3b8;
    font-size: 12px;
    pointer-events: none;
    transition: color 0.15s;
}

.global-search-wrapper:focus-within .global-search-icon {
    color: var(--hdr-green);
}

.global-search-clear {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 11px;
    cursor: pointer;
    padding: 2px 5px;
    border-radius: 4px;
    display: none;
}

.global-search-clear:hover {
    color: #ef4444;
    background: #fee2e2;
}

.global-search-spinner {
    position: absolute;
    right: 9px;
    color: var(--hdr-green);
    font-size: 11px;
    display: none;
}

/* Suggestion Dropdown */
.global-search-dropdown {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    width: 100%;
    min-width: 480px;
    max-width: 600px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-top: 3px solid var(--hdr-green);
    border-radius: 6px;
    box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.2), 0 4px 10px -2px rgba(0, 0, 0, 0.08);
    z-index: 1060;
    overflow: hidden;
}

.global-search-dropdown-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.global-search-list {
    max-height: 380px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.global-search-item {
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #cbd5e1;
    text-decoration: none !important;
    color: #1e293b;
    transition: background 0.15s ease;
}

.global-search-item:last-child {
    border-bottom: none;
}

.global-search-item:hover,
.global-search-item.highlighted {
    background: #f0fdf4;
}

.global-stu-left {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
    flex: 1;
}

.global-stu-avatar {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 50%;
    background: #e2f0d9;
    border: 1.5px solid #86efac;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    color: #166534;
    overflow: hidden;
    margin-top: 2px;
}

.global-stu-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.global-stu-details {
    min-width: 0;
    flex: 1;
}

.global-stu-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 3px;
}

.global-stu-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.meta-badge {
    background: #f1f5f9;
    color: #334155;
    padding: 1.5px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1.2;
    display: inline-flex;
    align-items: center;
}

.meta-badge-sr {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.meta-badge-batch {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #dbeafe;
}

.global-stu-meta {
    font-size: 10.5px;
    color: #64748b;
    line-height: 1.35;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    align-items: center;
}

.meta-pill-item {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    color: #475569;
}

.global-stu-meta .meta-badge {
    background: #f1f5f9;
    color: #334155;
    padding: 1px 5px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 600;
}

.global-stu-meta .meta-badge-green {
    background: #dcfce7;
    color: #166534;
}

.global-stu-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
    flex-shrink: 0;
}

.btn-stu-action {
    font-size: 10px;
    font-weight: 600;
    padding: 3px 7px;
    border-radius: 3px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #1e293b;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: all 0.15s;
}

.btn-stu-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.btn-stu-action.btn-fee {
    background: #f0fdf4;
    border-color: #86efac;
    color: #166534;
    font-weight: 700;
}
.btn-stu-action.btn-fee:hover {
    background: #dcfce7;
    color: #14532d;
}

.global-search-dropdown-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 6px 12px;
    font-size: 10px;
    color: #64748b;
    text-align: center;
}

@media (max-width: 991px) {
    .header-student-search-box {
        max-width: 240px;
    }
    .global-search-dropdown {
        min-width: 320px;
        left: -40px;
    }
}

@media (max-width: 767px) {
    .header-student-search-box {
        max-width: 170px;
        margin: 0 6px;
    }
    .global-search-dropdown {
        min-width: 280px;
        left: -60px;
    }
}
</style>

<!-- ========================================================
     NAVBAR TEMPLATE
     ======================================================== -->
<nav class="main-header navbar navbar-expand">
    <!-- Left Navbar Section -->
    <div class="header-left-group">
        <a class="btn-pushmenu-toggle" data-widget="pushmenu" href="#" role="button" title="Toggle Navigation Menu">
            <i class="fa fa-bars"></i>
        </a>
        
        @if(!empty($currentBranchName) || !empty($getSetting->name))
        <div class="header-brand-badge d-none d-md-inline-flex" title="Active Branch / Institute">
            <span class="badge-dot"></span>
            <span>{{ $currentBranchName ?: ($getSetting->name ?? '') }}</span>
        </div>
        @endif
    </div>

    <!-- Center / Global Real-Time Student Search Bar -->
    @if(Session::get('role_id') != 3)
    <div class="header-student-search-box">
        <div class="global-search-wrapper">
            <i class="fa fa-search global-search-icon"></i>
            <input type="search" id="globalStudentSearchInput" class="global-search-input" 
                   placeholder="Search student (Name, Mobile, SR No, Father, Aadhaar...)" 
                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" 
                   data-lpignore="true" data-1p-ignore>
            <span id="globalSearchSpinner" class="global-search-spinner">
                <i class="fa fa-spinner fa-spin"></i>
            </span>
            <button type="button" id="globalSearchClearBtn" class="global-search-clear" title="Clear search">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <!-- Real-Time Suggestion Results Dropdown -->
        <div id="globalStudentSearchResults" class="global-search-dropdown" style="display: none;">
            <div class="global-search-dropdown-header">
                <span><i class="fa fa-graduation-cap mr-1 text-success"></i> Student Suggestions</span>
                <span id="globalSearchCountBadge" class="badge badge-success">0 Found</span>
            </div>
            <div id="globalSearchListContainer" class="global-search-list">
                <!-- Dynamic Items Rendered via AJAX -->
            </div>
            <div class="global-search-dropdown-footer">
                <span class="text-muted"><i class="fa fa-keyboard-o mr-1"></i> Press <kbd>ESC</kbd> to close &bull; Click to open fee desk</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Right Navbar Controls Section -->
    <ul class="header-right-group">

        {{-- 1. Birthday Celebration Notification --}}
        @if(Session::get('role_id') == 1)
            @if(count($getstudentbirthday) > 0 || count($getUsersBirthday) > 0)
            <li class="header-pill-item">
                <a class="btn-birthday-badge" href="{{ url('happy_birthday') }}" title="Today's Birthdays">
                    <i class="fa fa-birthday-cake text-danger"></i>
                    <span>Birthday ({{ count($getstudentbirthday) + count($getUsersBirthday) }})</span>
                </a>
            </li>
            @endif
        @endif

        {{-- 2. Pending WhatsApp Messages Queue Dropdown --}}
        @if(Session::get('role_id') == 1)
            @php
                $queMessage = DB::table('message_queue')->whereNull('deleted_at')->where('message_status', 0)->orderBy('id','DESC')->take(3)->get();
                $queMessageCount = DB::table('message_queue')->whereNull('deleted_at')->where('message_status', 0)->count();
            @endphp
            @if($queMessageCount > 0)
            <li class="header-pill-item dropdown">
                <a class="btn-header-circle" data-toggle="dropdown" href="#" aria-expanded="false" title="WhatsApp Pending Messages">
                    <i class="fa fa-whatsapp text-success" style="font-size: 15px;"></i>
                    <span class="badge-pulse">{{ $queMessageCount > 99 ? '99+' : $queMessageCount }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right whatsapp-menu-dropdown">
                    <div class="whatsapp-menu-header">
                        <span><i class="fa fa-whatsapp mr-1"></i> WhatsApp Message Queue</span>
                        <span class="badge badge-success">{{ $queMessageCount }} Pending</span>
                    </div>
                    @foreach($queMessage as $quemsg)
                    <a href="{{ url('WhatsAppMessageHistory') }}" class="whatsapp-item">
                        <div class="msg-phone"><i class="fa fa-phone-square mr-1 text-success"></i> {{ $quemsg->receiver_number ?? '' }}</div>
                        <div class="msg-snippet">{{ Str::limit($quemsg->content ?? '', 40, '...') }}</div>
                        <div class="msg-time">
                            <i class="fa fa-clock-o mr-1"></i>
                            {{ (time() - strtotime($quemsg->created_at) < 60) ? 'Just now' : 
                                ((time() - strtotime($quemsg->created_at) < 3600) ? floor((time() - strtotime($quemsg->created_at)) / 60) . 'm ago' : 
                                ((time() - strtotime($quemsg->created_at) < 86400) ? floor((time() - strtotime($quemsg->created_at)) / 3600) . 'h ago' : 
                                date("d M Y", strtotime($quemsg->created_at)))) }}
                        </div>
                    </a>
                    @endforeach
                    <a href="{{ url('WhatsAppMessageHistory') }}" class="whatsapp-footer-link">
                        View All Pending WhatsApp Messages <i class="fa fa-angle-right ml-1"></i>
                    </a>
                </div>
            </li>
            @endif
        @endif

        {{-- 3. Language Selector Pill --}}
        <li class="header-pill-item pill-lang d-none d-sm-inline-flex">
            <form action="{{ url('changeLang') }}" method="POST" id="formChangeLang" class="m-0">
                @csrf
                <div class="header-select-wrapper" title="Change Language">
                    <i class="fa fa-globe header-select-icon"></i>
                    <select class="header-select-pill" id="lang" name="lang" onchange="this.form.submit()">
                        @php
                            $languages = DB::table('languages')->whereNull('deleted_at')->get();
                        @endphp
                        @if(!empty($languages))
                            @foreach($languages as $type)
                            <option value="{{ $type->value ?? '' }}" {{ session()->get('locale') == $type->value ? 'selected' : '' }}>
                                {{ $type->name ?? '' }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </form>
        </li>

        {{-- 4. Branch Selector Pill (Not for Students) --}}
        @if(Session::get('role_id') !== 3 && !empty($getAllBranch) && count($getAllBranch) > 1)
        <li class="header-pill-item pill-branch d-none d-sm-inline-flex">
            <form action="{{ url('changeBranch') }}" method="POST" id="formChangeBranch" class="m-0">
                @csrf
                <div class="header-select-wrapper" title="Switch Branch">
                    <i class="fa fa-university header-select-icon"></i>
                    <select class="header-select-pill" id="branch_id" name="branch_id" onchange="this.form.submit()">
                        @foreach($getAllBranch as $branch)
                        <option value="{{ $branch->id ?? '' }}" {{ ($branch->id == Session::get('admin_branch_id') || $branch->id == Session::get('branch_id')) ? 'selected' : '' }}>
                            {{ $branch->branch_name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </li>
        @endif

        {{-- 5. Dynamic Financial Year Badge (Replaces Academic Session Selector) --}}
        <li class="header-pill-item pill-session">
            @php
                $curM = (int)date('n');
                $curY = (int)date('Y');
                $fyStart = ($curM >= 4) ? $curY : ($curY - 1);
                $fyEnd = $fyStart + 1;
                $currentFY = $fyStart . '-' . substr((string)$fyEnd, -2);
            @endphp

            <div class="header-fy-badge" title="Financial Year: 1 Apr {{ $fyStart }} - 31 Mar {{ $fyEnd }}">
                <i class="fa fa-calendar-check-o"></i>
                <span>FY {{ $currentFY }}</span>
            </div>

            {{-- Hidden session selector kept to maintain background script compatibility --}}
            <form action="{{ url('sectionDataId') }}" method="POST" id="formChangeSession" class="m-0 d-none" style="display: none !important;">
                @csrf
                <select id="sessionData" name="sessionData">
                    @if(!empty($getSession))
                        @foreach($getSession as $type)
                        <option value="{{ $type->id ?? '' }}" {{ ($type->id == Session::get('session_id')) ? 'selected' : '' }}>
                            {{ $type->from_year ?? '' }} - {{ $type->to_year ?? '' }}
                        </option>
                        @endforeach
                    @endif
                </select>
            </form>
        </li>

        {{-- 6. Page Refresh Button --}}
        <li class="header-pill-item">
            <a href="{{ URL::current() }}" class="btn-header-circle btn-refresh" title="Refresh Page">
                <i class="fa fa-refresh"></i>
            </a>
        </li>

        {{-- 7. User Profile Trigger & Menu --}}
        @if(!empty(Session::get('id')))
        <li class="header-pill-item dropdown">
            <a class="header-user-trigger" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
                <img src="{{ env('IMAGE_SHOW_PATH').'/profile/'.$getUser['image'] }}" class="header-user-avatar" alt="Avatar" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'">
                <div class="header-user-info">
                    <span class="header-user-name">{{ Session::get('first_name') ?? 'User' }}</span>
                    <span class="header-user-role">{{ $roleName->name ?? 'Staff' }}</span>
                </div>
                <i class="fa fa-angle-down header-user-caret"></i>
            </a>
            
            <div class="dropdown-menu dropdown-menu-right user-menu-dropdown">
                <!-- User Header Card -->
                <div class="user-menu-header">
                    <img src="{{ env('IMAGE_SHOW_PATH').'/profile/'.$getUser['image'] }}" class="user-menu-header-avatar" alt="User Image" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'">
                    <div style="overflow: hidden;">
                        <h4 class="user-menu-header-name">{{ Session::get('first_name') ?? '' }}</h4>
                        <span class="user-menu-header-role">{{ $roleName->name ?? '' }}</span>
                    </div>
                </div>

                <!-- Menu Links -->
                <div class="user-menu-body">
                    <a href="{{ url('profile/edit/'.(Session::get('id') ?? '')) }}" class="user-menu-link {{ url('profile/edit/'.Session::get('id')) == URL::current() ? 'active-header-link' : '' }}">
                        <i class="fa fa-user-circle text-primary"></i>
                        <span>Profile Settings</span>
                    </a>

                    <a href="{{ url('change_password') }}" class="user-menu-link {{ url('change_password') == URL::current() ? 'active-header-link' : '' }}">
                        <i class="fa fa-key text-warning"></i>
                        <span>Change Password</span>
                    </a>

                    @if(Session::get('role_id') == 1)
                    <a href="{{ url('helpAndUpdate') }}" class="user-menu-link {{ url('helpAndUpdate') == URL::current() ? 'active-header-link' : '' }}">
                        <i class="fa fa-question-circle text-info"></i>
                        <span>Help & Updates</span>
                    </a>
                    @endif

                    <div class="user-menu-divider"></div>

                    <a href="javascript:void(0);" class="user-menu-link link-logout" onclick="confirmLogout(event)">
                        <i class="fa fa-sign-out"></i>
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
        </li>
        @endif

    </ul>
</nav>

<!-- ========================================================
     MODERN LOGOUT CONFIRMATION MODAL
     ======================================================== -->
<div id="logout-confirmation" class="logout-modal-backdrop" style="display: none;">
    <div class="logout-modal-card">
        <div class="logout-icon-ring">
            <i class="fa fa-sign-out"></i>
        </div>
        <h3 class="logout-modal-title">Sign Out</h3>
        <p class="logout-modal-desc">Are you sure you want to end your session? Any unsaved changes may be lost.</p>
        <div class="logout-modal-actions">
            <button type="button" class="btn-logout-cancel" onclick="closePopup()">Cancel</button>
            <button type="button" class="btn-logout-confirm" onclick="logout()">Yes, Log Out</button>
        </div>
    </div>
</div>

<!-- ========================================================
     GLOBAL HEADER JAVASCRIPT
     ======================================================== -->
<script>
function confirmLogout(event) {
    if(event) event.preventDefault();
    const modal = document.getElementById('logout-confirmation');
    if(modal) modal.style.display = 'flex';
}

function closePopup() {
    const modal = document.getElementById('logout-confirmation');
    if(modal) modal.style.display = 'none';
}

function logout() {
    window.location.href = "{{ url('logout') }}";
}

// Close logout modal if clicking outside card
document.addEventListener('DOMContentLoaded', function() {
    const logoutModal = document.getElementById('logout-confirmation');
    if(logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if(e.target === logoutModal) {
                closePopup();
            }
        });
    }
});

// ========================================================
// REAL-TIME STUDENT SEARCH JAVASCRIPT
// ========================================================
(function($) {
    $(document).ready(function() {
        var searchTimeout = null;
        var $input = $('#globalStudentSearchInput');
        var $resultsDropdown = $('#globalStudentSearchResults');
        var $listContainer = $('#globalSearchListContainer');
        var $countBadge = $('#globalSearchCountBadge');
        var $spinner = $('#globalSearchSpinner');
        var $clearBtn = $('#globalSearchClearBtn');

        function doSearch(keyword) {
            keyword = $.trim(keyword);
            if (!keyword || keyword.length < 1) {
                $resultsDropdown.hide();
                $clearBtn.hide();
                $spinner.hide();
                return;
            }

            $clearBtn.show();
            $spinner.show();

            $.ajax({
                url: "{{ url('header/student/search') }}",
                type: "GET",
                data: { keyword: keyword },
                dataType: "json",
                success: function(res) {
                    $spinner.hide();
                    if (res && res.status && res.data && res.data.length > 0) {
                        $countBadge.text(res.data.length + ' Found');
                        var html = '';
                        
                        $.each(res.data, function(idx, item) {
                            var initial = item.name ? item.name.charAt(0).toUpperCase() : 'S';
                            var avatarHtml = item.photo ? 
                                '<img src="' + item.photo + '" onerror="this.onerror=null;this.parentNode.innerHTML=\'' + initial + '\';">' : initial;

                            var courseBatchDisplay = '';
                            if (item.course && item.course !== 'N/A') {
                                courseBatchDisplay = item.course + ((item.batch && item.batch !== 'N/A') ? ('_' + item.batch) : '');
                            } else if (item.batch && item.batch !== 'N/A') {
                                courseBatchDisplay = item.batch;
                            }

                            var courseBadge = courseBatchDisplay ? 
                                '<span class="meta-badge meta-badge-sr"><i class="fa fa-book mr-1"></i>' + $('<div>').text(courseBatchDisplay).html() + '</span>' : '';
                            
                            var admNoVal = item.admission_no || item.unique_id;

                            html += '<div class="global-search-item">';
                            html += '  <div class="global-stu-left">';
                            html += '    <div class="global-stu-avatar">' + avatarHtml + '</div>';
                            html += '    <div class="global-stu-details">';
                            html += '      <div class="global-stu-header">';
                            html += '        <span class="global-stu-name">' + $('<div>').text(item.name).html() + '</span>';
                            html +=          courseBadge;
                            html += '      </div>';
                            html += '      <div class="global-stu-meta">';
                            if (admNoVal) {
                                html += '        <span class="meta-pill-item"><i class="fa fa-id-badge text-success"></i> <strong>Adm No:</strong> ' + $('<div>').text(admNoVal).html() + '</span>';
                            }
                            if (item.semester && item.semester !== 'N/A') {
                                html += '        <span class="meta-pill-item"><i class="fa fa-graduation-cap text-primary"></i> <strong>Sem:</strong> ' + $('<div>').text(item.semester).html() + '</span>';
                            }
                            if (item.dob && item.dob !== 'N/A') {
                                html += '        <span class="meta-pill-item"><i class="fa fa-birthday-cake text-warning"></i> <strong>DOB:</strong> ' + $('<div>').text(item.dob).html() + '</span>';
                            }
                            if (item.father_name && item.father_name !== 'N/A') {
                                html += '        <span class="meta-pill-item"><i class="fa fa-user text-secondary"></i> <strong>Father:</strong> ' + $('<div>').text(item.father_name).html() + '</span>';
                            }
                            if (item.mobile && item.mobile !== 'N/A') {
                                html += '        <span class="meta-pill-item"><i class="fa fa-phone text-info"></i> ' + $('<div>').text(item.mobile).html() + '</span>';
                            }
                            html += '      </div>';
                            html += '    </div>';
                            html += '  </div>';
                            html += '  <div class="global-stu-actions">';
                            html += '    <a href="' + item.fee_url + '" class="btn-stu-action btn-fee" title="Collect Fee"><i class="fa fa-money"></i> Collect Fee</a>';
                            html += '    <a href="' + item.profile_url + '" class="btn-stu-action" title="View Profile"><i class="fa fa-user-circle"></i> Profile</a>';
                            html += '  </div>';
                            html += '</div>';
                        });

                        $listContainer.html(html);
                        $resultsDropdown.show();
                    } else {
                        $countBadge.text('0 Found');
                        $listContainer.html(
                            '<div class="p-3 text-center text-muted" style="font-size: 11.5px;">' +
                            '  <i class="fa fa-info-circle mr-1 text-secondary"></i> No matching student found for "<strong>' + $('<div>').text(keyword).html() + '</strong>"' +
                            '</div>'
                        );
                        $resultsDropdown.show();
                    }
                },
                error: function() {
                    $spinner.hide();
                }
            });
        }

        // Real-time input typing handler with debounce
        $input.on('input', function() {
            var val = $(this).val();
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                doSearch(val);
            }, 250);
        });

        // Focus event
        $input.on('focus', function() {
            var val = $(this).val();
            if ($.trim(val).length > 0) {
                $resultsDropdown.show();
            }
        });

        // Clear button
        $clearBtn.on('click', function() {
            $input.val('').focus();
            $resultsDropdown.hide();
            $clearBtn.hide();
        });

        // Hide dropdown on Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $resultsDropdown.hide();
            }
        });

        // Hide dropdown on click outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.header-student-search-box').length) {
                $resultsDropdown.hide();
            }
        });
    });
})(jQuery);
</script>