@php
$attendanceType = Helper::attendanceType();
$getMonth = Helper::getMonth();
$currentRole = Session::get('role_id');
$currentStaffId = Session::get('id');

$monthNames = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];
$selectedMonthName = $monthNames[$curr_mnt] ?? date('F');
$todayDayNumber = (int)date('d');
$isCurrentMonthYear = ($curr_mnt == date('m') && $curr_yrs == date('Y'));

// Precalculate Grand Totals for KPI Ribbon
$grandTotalPresent = 0;
$grandTotalAbsent = 0;
$grandTotalLeave = 0;
$grandTotalHoliday = 0;
$grandTotalSundays = 0;
$totalStaffCount = !empty($all_teachers) ? count($all_teachers) : 0;

if(!empty($all_teachers)) {
    foreach($all_teachers as $t) {
        $staffId = $t->id;
        for($d = 1; $d <= $totel_month_day; $d++) {
            $dayStr = sprintf("%02d", $d);
            $fullDate = $curr_yrs . '-' . $curr_mnt . '-' . $dayStr;
            $dayOfWeek = date('w', strtotime($fullDate));

            if(isset($data[$staffId][$fullDate])) {
                $stId = $data[$staffId][$fullDate]['attendance_status_id'] ?? null;
                if($stId == 1) $grandTotalPresent++;
                elseif($stId == 3) $grandTotalAbsent++;
                elseif($stId == 9) $grandTotalLeave++;
                elseif($stId == 5) $grandTotalHoliday++;
            }
        }
    }
}

$possibleAttendances = $totalStaffCount * $totel_month_day;
$overallRate = ($possibleAttendances > 0 && ($grandTotalPresent + $grandTotalAbsent) > 0) 
    ? round(($grandTotalPresent / ($grandTotalPresent + $grandTotalAbsent)) * 100, 1) 
    : 0;
@endphp
@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   STAFF ATTENDANCE REGISTER - EXECUTIVE EXCEL GREEN THEME
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #eef6f0;
    --green-excel-border: #8eb99b;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --brand-navy: #002c54;
}

.content-wrapper {
    background-color: #f4f6f9;
}

.attendance-card {
    border: 1px solid #9aa7b1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 0;
    overflow: hidden;
    height: calc(100vh - 120px);
    min-height: 520px;
    display: flex;
    flex-direction: column;
}

/* Titlebar */
.attendance-titlebar {
    min-height: 38px;
    padding: 6px 12px;
    background: var(--green-excel) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-shrink: 0;
}

.attendance-title {
    margin: 0;
    font-size: 13.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #ffffff !important;
}

.attendance-title small {
    font-size: 11px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 4px;
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 3px 9px !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.4;
    cursor: pointer;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-add:hover {
    background: #166534 !important;
    color: #ffffff !important;
}

/* Filter Toolbar */
.attendance-filter-bar {
    background: #f8fafc;
    border-bottom: 1px solid var(--line);
    padding: 6px 12px;
    flex-shrink: 0;
}
.attendance-filter-bar label {
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.3px;
    color: #475569;
    margin-bottom: 2px;
    display: block;
}
.attendance-filter-bar .form-control {
    height: 28px !important;
    min-height: 28px !important;
    font-size: 11px !important;
    padding: 2px 6px !important;
    border-radius: 2px !important;
    border: 1px solid #9fb3c4 !important;
    color: #0f172a !important;
    background: #ffffff;
}
.attendance-filter-bar .form-control:focus {
    border-color: var(--green-excel) !important;
    outline: none !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15) !important;
}

/* KPI Summary Ribbon */
.attendance-ribbon {
    background: var(--green-excel-light);
    border-bottom: 1px solid var(--green-excel-border);
    padding: 5px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
}

.kpi-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    gap: 4px;
    line-height: 1.3;
}
.kpi-badge .count-val {
    font-weight: 700;
    padding: 0 6px;
    border-radius: 2px;
    background: #f1f5f9;
}
.kpi-badge-total .count-val { background: #334155; color: #ffffff; }
.kpi-badge-rate .count-val { background: #166534; color: #ffffff; }
.kpi-badge-present { background: #f0fdf4; border-color: #86efac; color: #166534; }
.kpi-badge-present .count-val { background: #166534; color: #ffffff; }
.kpi-badge-absent { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.kpi-badge-absent .count-val { background: #dc2626; color: #ffffff; }
.kpi-badge-leave { background: #f0f9ff; border-color: #7dd3fc; color: #075985; }
.kpi-badge-leave .count-val { background: #0284c7; color: #ffffff; }
.kpi-badge-holiday { background: #faf5ff; border-color: #d8b4fe; color: #6b21a8; }
.kpi-badge-holiday .count-val { background: #7e22ce; color: #ffffff; }

/* Legend Pills */
.legend-group {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}
.legend-pill {
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    border: 1px solid transparent;
}
.legend-pill-p { background: #dcfce7; color: #166534; border-color: #86efac; }
.legend-pill-a { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
.legend-pill-l { background: #e0f2fe; color: #0284c7; border-color: #7dd3fc; }
.legend-pill-h { background: #f3e8ff; color: #7e22ce; border-color: #d8b4fe; }
.legend-pill-sun { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }

/* ========================================================
   HORIZONTAL SCROLL NAVIGATION CONTROLLER
   ======================================================== */
.scroll-navigation-bar {
    background: #ffffff;
    border-bottom: 1px solid var(--line);
    padding: 4px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
}
.nav-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-scroll-jump {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 10.5px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 3px;
    cursor: pointer;
    line-height: 1.3;
    transition: all 0.12s ease;
}
.btn-scroll-jump:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
    color: #0f172a;
}
.btn-scroll-today {
    background: #fffbeb !important;
    border-color: #fde68a !important;
    color: #b45309 !important;
    font-weight: 700 !important;
}
.btn-scroll-today:hover {
    background: #b45309 !important;
    color: #ffffff !important;
}
.btn-scroll-nav {
    background: #ffffff;
    border: 1px solid #94a3b8;
    color: #1e293b;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 3px;
    cursor: pointer;
    line-height: 1.3;
    transition: all 0.12s ease;
}
.btn-scroll-nav:hover {
    background: var(--green-excel);
    border-color: var(--green-excel-dark);
    color: #ffffff;
}

/* Register Matrix Table Container */
.register-scroll-container {
    flex: 1;
    min-height: 0;
    height: 100%;
    overflow: auto;
    background: #ffffff;
    border-bottom: 1px solid var(--line);
    position: relative;
    cursor: grab;
}
.register-scroll-container.active-dragging {
    cursor: grabbing;
    user-select: none;
}

.register-table {
    margin: 0 !important;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11px;
    border-top: 1px solid var(--line) !important;
    border-left: 1px solid var(--line) !important;
}

.register-table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 10.5px;
    white-space: nowrap;
    text-align: center;
    height: 32px !important;
    line-height: 1.2;
    border-top: 0 !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    padding: 3px 4px !important;
}

.register-table thead th.th-sunday {
    background: #185c37 !important;
    color: #fde047 !important;
}

.register-table thead th.th-today {
    background: #0f3d23 !important;
    box-shadow: inset 0 -3px 0 #fde047;
}

.register-table td {
    padding: 3px 4px !important;
    border-right: 1px solid var(--line) !important;
    border-bottom: 1px solid var(--line) !important;
    vertical-align: middle !important;
    font-size: 11px;
    height: 34px;
    box-sizing: border-box;
    text-align: center;
}

.register-table tbody tr:nth-child(even) td { background-color: #fafafa; }
.register-table tbody tr:hover td { background-color: #fffde7 !important; }

/* ========================================================
   STICKY LEFT COLUMNS (EMPLOYEE DETAILS)
   ======================================================== */
.sticky-col {
    position: sticky !important;
    z-index: 3;
    background-color: #ffffff;
}
.register-table tbody tr:nth-child(even) .sticky-col { background-color: #fafafa !important; }
.register-table tbody tr:hover .sticky-col { background-color: #fffde7 !important; }

.col-sticky-sr {
    left: 0;
    width: 35px;
    min-width: 35px;
    max-width: 35px;
}
.col-sticky-photo {
    left: 35px;
    width: 36px;
    min-width: 36px;
    max-width: 36px;
}
.col-sticky-id {
    left: 71px;
    width: 65px;
    min-width: 65px;
    max-width: 65px;
}
.col-sticky-name {
    left: 136px;
    width: 175px;
    min-width: 175px;
    max-width: 175px;
    text-align: left !important;
    border-right: 2px solid #8eb99b !important;
    box-shadow: 3px 0 6px rgba(0,0,0,0.06);
}

.register-table thead th.col-sticky-sr { left: 0; z-index: 10; }
.register-table thead th.col-sticky-photo { left: 35px; z-index: 10; }
.register-table thead th.col-sticky-id { left: 71px; z-index: 10; }
.register-table thead th.col-sticky-name { left: 136px; z-index: 10; border-right: 2px solid #8eb99b !important; box-shadow: 3px 0 6px rgba(0,0,0,0.1); }

/* ========================================================
   STICKY RIGHT SUMMARY STATS SECTION (PERMANENTLY FIXED)
   ======================================================== */
.sticky-right-col {
    position: sticky !important;
}

/* Th Sticky Right Headers */
.register-table thead th.th-sum-p {
    right: 184px;
    z-index: 10;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background: #166534 !important;
    color: #ffffff !important;
    border-left: 2px solid #8eb99b !important;
    box-shadow: -3px 0 6px rgba(0,0,0,0.08);
}
.register-table thead th.th-sum-a {
    right: 146px;
    z-index: 10;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background: #991b1b !important;
    color: #ffffff !important;
}
.register-table thead th.th-sum-l {
    right: 108px;
    z-index: 10;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background: #0369a1 !important;
    color: #ffffff !important;
}
.register-table thead th.th-sum-h {
    right: 70px;
    z-index: 10;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background: #6b21a8 !important;
    color: #ffffff !important;
}
.register-table thead th.th-sum-rate {
    right: 0px;
    z-index: 10;
    width: 70px;
    min-width: 70px;
    max-width: 70px;
    background: #002c54 !important;
    color: #fde047 !important;
}

/* Td Sticky Right Cells with Solid Backgrounds */
.register-table td.td-sum-p {
    right: 184px;
    z-index: 3;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background-color: #f0fdf4 !important;
    border-left: 2px solid #8eb99b !important;
    color: #166534 !important;
    font-weight: 700;
    font-size: 11.5px;
    box-shadow: -3px 0 6px rgba(0,0,0,0.08);
}
.register-table td.td-sum-a {
    right: 146px;
    z-index: 3;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background-color: #fef2f2 !important;
    color: #dc2626 !important;
    font-weight: 700;
    font-size: 11.5px;
}
.register-table td.td-sum-l {
    right: 108px;
    z-index: 3;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background-color: #f0f9ff !important;
    color: #0284c7 !important;
    font-weight: 700;
    font-size: 11.5px;
}
.register-table td.td-sum-h {
    right: 70px;
    z-index: 3;
    width: 38px;
    min-width: 38px;
    max-width: 38px;
    background-color: #faf5ff !important;
    color: #7e22ce !important;
    font-weight: 700;
    font-size: 11.5px;
}
.register-table td.td-sum-rate {
    right: 0px;
    z-index: 3;
    width: 70px;
    min-width: 70px;
    max-width: 70px;
    background-color: #f8fafc !important;
    font-weight: 700;
}

.register-table tbody tr:hover td.td-sum-p { background-color: #dcfce7 !important; }
.register-table tbody tr:hover td.td-sum-a { background-color: #fee2e2 !important; }
.register-table tbody tr:hover td.td-sum-l { background-color: #e0f2fe !important; }
.register-table tbody tr:hover td.td-sum-h { background-color: #f3e8ff !important; }
.register-table tbody tr:hover td.td-sum-rate { background-color: #f1f5f9 !important; }

/* Day Cell Badges */
.att-cell {
    width: 32px;
    min-width: 32px;
    padding: 2px !important;
}

.att-badge {
    width: 24px;
    height: 24px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1;
    margin: 0 auto;
    cursor: default;
    user-select: none;
    transition: transform 0.1s ease;
}
.att-badge:hover {
    transform: scale(1.18);
    z-index: 2;
    position: relative;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.att-badge-p { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.att-badge-a { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
.att-badge-l { background: #e0f2fe; color: #0284c7; border: 1px solid #7dd3fc; }
.att-badge-h { background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; }
.att-badge-sun { background: #f1f5f9; color: #94a3b8; font-weight: 500; }
.att-badge-empty { color: #cbd5e1; font-weight: 400; }

.td-sunday {
    background-color: #f8fafc !important;
}

/* Percentage Pill */
.rate-pill {
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 10.5px;
    font-weight: 700;
    display: inline-block;
}
.rate-pill-high { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.rate-pill-mid { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.rate-pill-low { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }

/* Avatar with First Letter Fallback */
.staff-avatar-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    position: relative;
    vertical-align: middle;
}

.staff-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    display: block;
}

.staff-letter-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    color: #ffffff;
    font-weight: 700;
    font-size: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    line-height: 1;
    user-select: none;
    border: 1px solid rgba(255,255,255,0.4);
}

.staff-badge-id {
    font-size: 10px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 2px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    display: inline-block;
}

.staff-role-tag {
    font-size: 9.5px;
    font-weight: 600;
    padding: 0 4px;
    border-radius: 2px;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    display: inline-block;
}

/* Pulse animation for jump to today */
@keyframes pulseGlow {
    0% { background-color: #fde047; color: #000; }
    50% { background-color: #ca8a04; color: #fff; }
    100% { background-color: #0f3d23; color: #fde047; }
}
.pulse-today {
    animation: pulseGlow 1s ease 2;
}

/* Empty State Centered */
.register-table tbody tr.staff-empty-row {
    height: calc(100vh - 270px) !important;
    min-height: 380px !important;
    background: #ffffff !important;
}
.register-table tbody tr.staff-empty-row:hover td {
    background-color: #ffffff !important;
}
.register-table tbody tr.staff-empty-row td.empty-state-cell {
    height: calc(100vh - 270px) !important;
    min-height: 380px !important;
    border-right: none !important;
    border-bottom: none !important;
    padding: 20px !important;
    vertical-align: middle !important;
    background: #ffffff !important;
    text-align: center !important;
}

.empty-state-wrap {
    position: sticky;
    left: 0;
    right: 0;
    margin: 0 auto;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    max-width: 460px;
    background: transparent;
}

.empty-state-icon-wrap {
    width: 58px;
    height: 58px;
    background: #eef6f0;
    color: var(--green-excel);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 12px;
    border: 1px solid var(--green-excel-border);
}

.empty-state-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}

.empty-state-subtitle {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 15px;
}

.btn-empty-reset {
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    padding: 5px 16px !important;
    border-radius: 3px !important;
    border: 1px solid var(--green-excel-dark) !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.15s ease-in-out;
}
.btn-empty-reset:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}
</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-2">
        <div class="container-fluid">
            <div class="attendance-card">
                <!-- 1. Header / Titlebar -->
                <div class="attendance-titlebar">
                    <div class="attendance-title">
                        <i class="fa fa-calendar-check-o"></i>
                        <span>Staff Attendance Register</span>
                        <small>| {{ $selectedMonthName }} {{ $curr_yrs }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($currentRole != 2)
                            <a href="{{ url('staff_attendance_add') }}" class="btn-header-action btn-header-add" title="Mark Attendance">
                                <i class="fa fa-plus"></i> Mark Attendance
                            </a>
                        @endif
                        <button type="button" class="btn-header-action" onclick="downloadCSV()" title="Export Matrix to CSV">
                            <i class="fa fa-file-excel-o text-success"></i> Export CSV
                        </button>
                        <a href="{{ url('user_dashboard') }}" class="btn-header-action" title="Back to Dashboard">
                            <i class="fa fa-arrow-left text-secondary"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Parameter & Filter Toolbar -->
                <div class="attendance-filter-bar">
                    <form id="filterForm" action="{{ url('staff/Attendance/view') }}" method="POST">
                        @csrf
                        <div class="row align-items-end g-2">
                            <!-- Year -->
                            <div class="col-6 col-md-2 col-lg-1">
                                <label><i class="fa fa-calendar text-muted"></i> Year</label>
                                <select class="form-control" id="year" name="year" onchange="$('#filterForm').submit();">
                                    @for($y = date('Y') - 3; $y <= date('Y') + 3; $y++)
                                        <option value="{{ $y }}" {{ $y == $curr_yrs ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Month -->
                            <div class="col-6 col-md-3 col-lg-2">
                                <label><i class="fa fa-calendar-o text-muted"></i> Month</label>
                                <select class="form-control" id="date" name="date" onchange="$('#filterForm').submit();">
                                    @foreach($monthNames as $num => $name)
                                        <option value="{{ $num }}" {{ $num == $curr_mnt ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Live Keyword Search -->
                            <div class="col-12 col-md-3 col-lg-3">
                                <label><i class="fa fa-search text-muted"></i> Search Staff</label>
                                <input type="text" class="form-control" id="staffLiveSearch" placeholder="Filter by Name, ID, Mobile, Role..." autocomplete="off">
                            </div>

                            <!-- Role Filter -->
                            <div class="col-6 col-md-2 col-lg-2">
                                <label><i class="fa fa-user-circle text-muted"></i> Role</label>
                                <select class="form-control" id="filterRole" name="role_id" onchange="applyLiveFilter();">
                                    <option value="">All Roles</option>
                                    @if(!empty($roles))
                                        @foreach($roles as $r)
                                            <option value="{{ $r->name ?? '' }}" {{ ($search['role_id'] == $r->id) ? 'selected' : '' }}>{{ $r->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Department Filter -->
                            <div class="col-6 col-md-2 col-lg-2">
                                <label><i class="fa fa-building text-muted"></i> Department</label>
                                <select class="form-control" id="filterDept" name="department" onchange="applyLiveFilter();">
                                    <option value="">All Departments</option>
                                    @if(!empty($departments))
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ ($search['department'] == $dept) ? 'selected' : '' }}>{{ $dept }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="col-12 col-md-12 col-lg-2 text-lg-right">
                                <label class="d-none d-lg-block">&nbsp;</label>
                                <button type="button" class="btn-header-action" onclick="resetRegisterFilters();" title="Reset Search">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 3. Dynamic Stats Ribbon & Legend -->
                <div class="attendance-ribbon">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="kpi-badge kpi-badge-total">
                            <i class="fa fa-users text-muted"></i> Total Staff: <span class="count-val" id="kpi_total_staff">{{ $totalStaffCount }}</span>
                        </span>
                        <span class="kpi-badge kpi-badge-rate">
                            <i class="fa fa-line-chart text-success"></i> Attendance Rate: <span class="count-val" id="kpi_rate">{{ $overallRate }}%</span>
                        </span>
                        <span class="kpi-badge kpi-badge-present">
                            <i class="fa fa-check-circle text-success"></i> Total Present: <span class="count-val" id="kpi_present">{{ $grandTotalPresent }}</span>
                        </span>
                        <span class="kpi-badge kpi-badge-absent">
                            <i class="fa fa-times-circle text-danger"></i> Total Absent: <span class="count-val" id="kpi_absent">{{ $grandTotalAbsent }}</span>
                        </span>
                        <span class="kpi-badge kpi-badge-leave">
                            <i class="fa fa-plane text-info"></i> Total Leave: <span class="count-val" id="kpi_leave">{{ $grandTotalLeave }}</span>
                        </span>
                        <span class="kpi-badge kpi-badge-holiday">
                            <i class="fa fa-star text-purple"></i> Total Holiday: <span class="count-val" id="kpi_holiday">{{ $grandTotalHoliday }}</span>
                        </span>
                    </div>

                    <!-- Legend -->
                    <div class="legend-group">
                        <span class="legend-pill legend-pill-p">P Present</span>
                        <span class="legend-pill legend-pill-a">A Absent</span>
                        <span class="legend-pill legend-pill-l">L Leave</span>
                        <span class="legend-pill legend-pill-h">H Holiday</span>
                        <span class="legend-pill legend-pill-sun">Sun Sunday</span>
                    </div>
                </div>

                <!-- 4. Quick Horizontal Scroll Navigation Controller -->
                <div class="scroll-navigation-bar">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="nav-label"><i class="fa fa-arrows-h text-success mr-1"></i> Scroll Dates:</span>
                        <button type="button" class="btn-scroll-jump" onclick="scrollToPosition(0)" title="Scroll to Beginning (Day 1)">
                            <i class="fa fa-angle-double-left"></i> Day 1 - 10
                        </button>
                        <button type="button" class="btn-scroll-jump" onclick="scrollToPosition(450)" title="Scroll to Middle of Month">
                            Day 11 - 20
                        </button>
                        <button type="button" class="btn-scroll-jump" onclick="scrollToPosition(850)" title="Scroll to End of Month">
                            Day 21 - 31 <i class="fa fa-angle-double-right"></i>
                        </button>
                        @if($isCurrentMonthYear)
                            <button type="button" class="btn-scroll-jump btn-scroll-today" onclick="scrollToToday()" title="Jump directly to Today's Column">
                                <i class="fa fa-crosshairs text-warning"></i> Today (Day {{ $todayDayNumber }})
                            </button>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn-scroll-nav" onclick="scrollRelative(-250)" title="Scroll Left">
                            <i class="fa fa-chevron-left"></i> Left
                        </button>
                        <button type="button" class="btn-scroll-nav" onclick="scrollRelative(250)" title="Scroll Right">
                            Right <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- 5. Main Spreadsheet Register Matrix -->
                <div class="register-scroll-container" id="matrixScrollContainer">
                    <table class="register-table" id="staffRegisterTable">
                        <thead>
                            <tr>
                                <th class="col-sticky-sr">#</th>
                                <th class="col-sticky-photo">Photo</th>
                                <th class="col-sticky-id">Staff ID</th>
                                <th class="col-sticky-name">{{ __('common.Name') }}</th>

                                <!-- Month Days Headers (1..31) - Horizontally Scrollable -->
                                @for($day = 1; $day <= $totel_month_day; $day++)
                                    @php
                                        $loopDate = $curr_yrs . '-' . $curr_mnt . '-' . sprintf("%02d", $day);
                                        $dayName = date('D', strtotime($loopDate));
                                        $isSunday = ($dayName === 'Sun');
                                        $isTodayCol = ($isCurrentMonthYear && $day == $todayDayNumber);
                                    @endphp
                                    <th class="att-cell th-day-{{ $day }} {{ $isSunday ? 'th-sunday' : '' }} {{ $isTodayCol ? 'th-today' : '' }}" title="{{ date('d M Y (l)', strtotime($loopDate)) }}{{ $isTodayCol ? ' - Today' : '' }}">
                                        <div>{{ sprintf("%02d", $day) }}</div>
                                        <div style="font-size: 9px; opacity: 0.85;">{{ $dayName }}</div>
                                    </th>
                                @endfor

                                <!-- Fixed Sticky Right Summary Stats Columns -->
                                <th class="th-sum-p sticky-right-col" title="Total Present Days in {{ $selectedMonthName }}">P</th>
                                <th class="th-sum-a sticky-right-col" title="Total Absent Days in {{ $selectedMonthName }}">A</th>
                                <th class="th-sum-l sticky-right-col" title="Total Leave Days in {{ $selectedMonthName }}">L</th>
                                <th class="th-sum-h sticky-right-col" title="Total Holidays in {{ $selectedMonthName }}">H</th>
                                <th class="th-sum-rate sticky-right-col" title="Attendance Percentage">Att. %</th>
                            </tr>
                        </thead>
                        <tbody id="staffRegisterBody">
                            @php
                                $visibleRowCount = 0;
                            @endphp

                            @if(!empty($all_teachers) && count($all_teachers) > 0)
                                @foreach($all_teachers as $index => $teacher)
                                    @php
                                        $visibleRowCount++;
                                        $staffId = $teacher->id;
                                        $rawName = trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''));
                                        $nameForAvatar = !empty($rawName) ? $rawName : ($teacher->userName ?? 'Staff');
                                        $firstLetter = strtoupper(mb_substr($nameForAvatar, 0, 1, 'UTF-8'));
                                        $palette = ['#166534', '#0284c7', '#7e22ce', '#d97706', '#dc2626', '#4f46e5', '#0d9488', '#b45309', '#0369a1'];
                                        $bgColor = $palette[abs(crc32($nameForAvatar . $staffId)) % count($palette)];
                                        $imgSrc = !empty($teacher->image) ? env('IMAGE_SHOW_PATH').'profile/'.$teacher->image : '';

                                        $staffPresentCount = 0;
                                        $staffAbsentCount = 0;
                                        $staffLeaveCount = 0;
                                        $staffHolidayCount = 0;
                                        $staffUnmarkedCount = 0;
                                        $staffWorkingDays = 0;

                                        $staffCode = !empty($teacher->UniqueId) ? $teacher->UniqueId : ('EMP-' . sprintf('%04d', $staffId));
                                    @endphp
                                    <tr class="staff-register-row" 
                                        data-staff-id="{{ $staffId }}"
                                        data-name="{{ strtolower($rawName) }}"
                                        data-father="{{ strtolower($teacher->father_name ?? '') }}"
                                        data-mobile="{{ $teacher->mobile ?? '' }}"
                                        data-email="{{ strtolower($teacher->email ?? '') }}"
                                        data-role="{{ strtolower($teacher->roleName->name ?? '') }}"
                                        data-dept="{{ strtolower($teacher->department ?? '') }}">
                                        
                                        <!-- 1. Sticky SR. NO -->
                                        <td class="sticky-col col-sticky-sr font-weight-bold text-muted row-sr-no">
                                            {{ $visibleRowCount }}
                                        </td>

                                        <!-- 2. Sticky Photo (with First Letter Fallback) -->
                                        <td class="sticky-col col-sticky-photo">
                                            <div class="staff-avatar-wrapper">
                                                @if(!empty($imgSrc))
                                                    <img src="{{ $imgSrc }}" class="staff-avatar" alt="{{ $nameForAvatar }}"
                                                         onerror="this.style.display='none'; if(this.nextElementSibling){ this.nextElementSibling.style.display='inline-flex'; }">
                                                    <span class="staff-letter-avatar" style="display: none; background-color: {{ $bgColor }};" title="{{ $nameForAvatar }}">
                                                        {{ $firstLetter }}
                                                    </span>
                                                @else
                                                    <span class="staff-letter-avatar" style="background-color: {{ $bgColor }};" title="{{ $nameForAvatar }}">
                                                        {{ $firstLetter }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- 3. Sticky Staff ID -->
                                        <td class="sticky-col col-sticky-id">
                                            <span class="staff-badge-id">#{{ $staffId }}</span>
                                        </td>

                                        <!-- 4. Sticky Name & Role -->
                                        <td class="sticky-col col-sticky-name">
                                            <div class="font-weight-bold text-dark text-truncate" title="{{ $nameForAvatar }}" style="max-width: 155px; font-size: 11.5px;">
                                                {{ $nameForAvatar }}
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="staff-role-tag">{{ $teacher->roleName->name ?? 'Staff' }}</span>
                                                @if(!empty($teacher->department))
                                                    <small class="text-muted text-truncate" style="max-width: 75px;">{{ $teacher->department }}</small>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Days Matrix Cells (1..31) - Scrollable Underneath Sticky Ends -->
                                        @for($day = 1; $day <= $totel_month_day; $day++)
                                            @php
                                                $dayStr = sprintf("%02d", $day);
                                                $fullDate = $curr_yrs . '-' . $curr_mnt . '-' . $dayStr;
                                                $dayOfWeek = date('w', strtotime($fullDate));
                                                $isSunday = ($dayOfWeek == 0);

                                                $attRecord = $data[$staffId][$fullDate] ?? null;
                                                $statusId = $attRecord['attendance_status_id'] ?? null;
                                                $checkInTime = (!empty($attRecord['time'])) ? date('h:i A', strtotime($attRecord['time'])) : '';

                                                if($statusId == 1) {
                                                    $staffPresentCount++;
                                                    $staffWorkingDays++;
                                                } elseif($statusId == 3) {
                                                    $staffAbsentCount++;
                                                    $staffWorkingDays++;
                                                } elseif($statusId == 9) {
                                                    $staffLeaveCount++;
                                                    $staffWorkingDays++;
                                                } elseif($statusId == 5) {
                                                    $staffHolidayCount++;
                                                } else {
                                                    if(!$isSunday) $staffUnmarkedCount++;
                                                }
                                            @endphp
                                            <td class="att-cell {{ $isSunday ? 'td-sunday' : '' }}">
                                                @if($statusId == 1)
                                                    <span class="att-badge att-badge-p" title="Present (In) | Date: {{ $fullDate }} {{ $checkInTime ? '| Time: '.$checkInTime : '' }}">P</span>
                                                @elseif($statusId == 3)
                                                    <span class="att-badge att-badge-a" title="Absent | Date: {{ $fullDate }}">A</span>
                                                @elseif($statusId == 9)
                                                    <span class="att-badge att-badge-l" title="Leave | Date: {{ $fullDate }}">L</span>
                                                @elseif($statusId == 5)
                                                    <span class="att-badge att-badge-h" title="Holiday | Date: {{ $fullDate }}">H</span>
                                                @elseif($isSunday)
                                                    <span class="att-badge att-badge-sun" title="Sunday | Date: {{ $fullDate }}">Sun</span>
                                                @else
                                                    <span class="att-badge att-badge-empty" title="Unmarked | Date: {{ $fullDate }}">-</span>
                                                @endif
                                            </td>
                                        @endfor

                                        <!-- Totals Calculation -->
                                        @php
                                            $staffRate = ($staffWorkingDays > 0) ? round(($staffPresentCount / $staffWorkingDays) * 100, 1) : 0;
                                            $rateClass = 'rate-pill-high';
                                            if($staffRate < 70) $rateClass = 'rate-pill-low';
                                            elseif($staffRate < 85) $rateClass = 'rate-pill-mid';
                                        @endphp

                                        <!-- Fixed Sticky Right Summary Stats Cells -->
                                        <td class="td-sum-p sticky-right-col font-weight-bold">{{ $staffPresentCount }}</td>
                                        <td class="td-sum-a sticky-right-col font-weight-bold">{{ $staffAbsentCount }}</td>
                                        <td class="td-sum-l sticky-right-col font-weight-bold">{{ $staffLeaveCount }}</td>
                                        <td class="td-sum-h sticky-right-col font-weight-bold">{{ $staffHolidayCount }}</td>
                                        <td class="td-sum-rate sticky-right-col">
                                            <span class="rate-pill {{ $rateClass }}">{{ $staffRate }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            <!-- Full Table Centered Empty State -->
                            <tr id="noRegisterRecordRow" class="staff-empty-row" style="{{ $visibleRowCount == 0 ? '' : 'display: none;' }}">
                                <td colspan="{{ $totel_month_day + 9 }}" class="empty-state-cell">
                                    <div class="empty-state-wrap">
                                        <div class="empty-state-icon-wrap">
                                            <i class="fa fa-calendar-times-o"></i>
                                        </div>
                                        <div class="empty-state-title">No Staff Attendance Records Found</div>
                                        <p class="empty-state-subtitle">No active staff records match your current month or search parameters.</p>
                                        <button type="button" class="btn-empty-reset" onclick="resetRegisterFilters();">
                                            <i class="fa fa-refresh"></i> Reset All Filters
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 6. Bottom Summary & Quick Help Bar -->
                <div class="attendance-ribbon" style="background: #ffffff; border-top: 1px solid var(--line); border-bottom: none;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted font-weight-bold" style="font-size: 11px;">
                            <i class="fa fa-info-circle text-primary"></i> Fixed Stats Sheet: Summary metrics (P/A/L/H/Att.%) remain pinned on the right while dates scroll smoothly.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-header-action" onclick="downloadCSV()">
                            <i class="fa fa-download text-success"></i> Export Full Sheet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // 1. Live Instant Search
    $('#staffLiveSearch').on('input', applyLiveFilter);
    $('#filterRole').on('change', applyLiveFilter);
    $('#filterDept').on('change', applyLiveFilter);

    // 2. Initialize Click & Drag to Scroll
    initDragToScroll();
});

/* Drag to Scroll Implementation */
function initDragToScroll() {
    const slider = document.getElementById('matrixScrollContainer');
    if (!slider) return;

    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', (e) => {
        // Prevent drag when selecting text or clicking buttons
        if (e.target.closest('.att-badge, a, button, input, select')) return;
        isDown = true;
        slider.classList.add('active-dragging');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.classList.remove('active-dragging');
    });

    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.classList.remove('active-dragging');
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 1.5;
        slider.scrollLeft = scrollLeft - walk;
    });
}

/* Horizontal Scroll Jump Functions */
function scrollToPosition(pos) {
    $('#matrixScrollContainer').stop().animate({ scrollLeft: pos }, 250);
}

function scrollRelative(amount) {
    var current = $('#matrixScrollContainer').scrollLeft();
    $('#matrixScrollContainer').stop().animate({ scrollLeft: current + amount }, 200);
}

function scrollToToday() {
    var todayDay = {{ (int)date('d') }};
    var $todayCell = $('.th-day-' + todayDay);
    if ($todayCell.length) {
        var offset = $todayCell.position().left + $('#matrixScrollContainer').scrollLeft() - 320;
        $('#matrixScrollContainer').stop().animate({ scrollLeft: offset }, 300);
        $todayCell.addClass('pulse-today');
        setTimeout(function() { $todayCell.removeClass('pulse-today'); }, 2000);
    }
}

/* Live DOM Filtering for instant search */
function applyLiveFilter() {
    var keyword = $('#staffLiveSearch').val().toLowerCase().trim();
    var roleFilter = $('#filterRole').val().toLowerCase().trim();
    var deptFilter = $('#filterDept').val().toLowerCase().trim();

    var visibleIndex = 0;
    var totalPresent = 0;
    var totalAbsent = 0;
    var totalLeave = 0;
    var totalHoliday = 0;

    $('.staff-register-row').each(function() {
        var $row = $(this);
        var name = $row.data('name') || '';
        var father = $row.data('father') || '';
        var mobile = String($row.data('mobile') || '');
        var email = $row.data('email') || '';
        var staffId = String($row.data('staff-id') || '');
        var role = $row.data('role') || '';
        var dept = $row.data('dept') || '';

        var matchesKeyword = (!keyword || 
            name.indexOf(keyword) !== -1 || 
            father.indexOf(keyword) !== -1 || 
            mobile.indexOf(keyword) !== -1 || 
            email.indexOf(keyword) !== -1 || 
            staffId.indexOf(keyword) !== -1 || 
            role.indexOf(keyword) !== -1 || 
            dept.indexOf(keyword) !== -1
        );

        var matchesRole = (!roleFilter || role === roleFilter);
        var matchesDept = (!deptFilter || dept === deptFilter);

        if (matchesKeyword && matchesRole && matchesDept) {
            $row.show();
            visibleIndex++;
            $row.find('.row-sr-no').text(visibleIndex);

            totalPresent += parseInt($row.find('.td-sum-p').text()) || 0;
            totalAbsent += parseInt($row.find('.td-sum-a').text()) || 0;
            totalLeave += parseInt($row.find('.td-sum-l').text()) || 0;
            totalHoliday += parseInt($row.find('.td-sum-h').text()) || 0;
        } else {
            $row.hide();
        }
    });

    if (visibleIndex === 0) {
        $('#noRegisterRecordRow').show();
    } else {
        $('#noRegisterRecordRow').hide();
    }

    // Update Ribbon Counters
    $('#kpi_total_staff').text(visibleIndex);
    $('#kpi_present').text(totalPresent);
    $('#kpi_absent').text(totalAbsent);
    $('#kpi_leave').text(totalLeave);
    $('#kpi_holiday').text(totalHoliday);

    var rate = (totalPresent + totalAbsent > 0) ? Math.round((totalPresent / (totalPresent + totalAbsent)) * 100 * 10) / 10 : 0;
    $('#kpi_rate').text(rate + '%');
}

/* Reset All Filters */
function resetRegisterFilters() {
    $('#staffLiveSearch').val('');
    $('#filterRole').val('');
    $('#filterDept').val('');
    applyLiveFilter();
}

/* Download Matrix as Clean CSV */
function downloadCSV() {
    var csv = [];
    var rows = document.querySelectorAll("#staffRegisterTable tr");

    for (var i = 0; i < rows.length; i++) {
        if (rows[i].style.display === 'none') continue;
        var row = [];
        var cols = rows[i].querySelectorAll("td, th");

        for (var j = 0; j < cols.length; j++) {
            // Strip multiple whitespaces and newlines
            var text = cols[j].innerText.replace(/\s+/g, ' ').trim();
            // Escape double quotes
            text = '"' + text.replace(/"/g, '""') + '"';
            row.push(text);
        }
        csv.push(row.join(","));
    }

    var csvFile = new Blob([csv.join("\n")], { type: "text/csv;charset=utf-8;" });
    var downloadLink = document.createElement("a");
    downloadLink.download = "Staff_Attendance_Register_{{ $selectedMonthName }}_{{ $curr_yrs }}.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

@endsection