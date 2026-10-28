@php
$noticeBoard = Helper::noticeBoard() ?? [];
$getstudentbirthday = Helper::getstudentbirthday() ?? [];
$task = Helper::task() ?? [];
$chartAttendanceStudents = Helper::chartAttendanceStudents() ?? ['Present'=>0,'Absent'=>0,'Work_From_Home'=>0,'Half_Day'=>0,'Holiday'=>0];
$chartAttendanceTeachers = Helper::chartAttendanceTeachers() ?? ['Present'=>0,'Absent'=>0,'Work_From_Home'=>0,'Half_Day'=>0,'Holiday'=>0];
$getremark = Helper::getremark() ?? [];
$roleName = DB::table('role')->whereNull('deleted_at')->find(Session::get('role_id'));

$totalStudents = \App\Models\Admission::countActiveAdmission();
$todayFees = \App\Models\fees\FeesDetailsInvoices::todayCollection() ?? 0;
$totalFees = \App\Models\FeesDetail::totalCollection() ?? 0;
$totalUsers = \App\Models\User::countUser() ?? 0;

$dataMonth = Helper::getMonthWiseFeeCollection();
$dataWeek = Helper::getWeeklyWiseFeeCollection();
@endphp

@extends('layout.app')
@section('content')

<style>
/* ========================================================
   EXCEL GREEN EXECUTIVE THEME (#217346) - MATCHING studentsDashboard
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --green-excel-border: #a7d0b8;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --text-main: #0f172a;
    --text-muted: #64748b;
}

.admin-dash-page {
    background-color: #f4f6f9;
    font-size: 12px;
}

/* Main Dashboard Card Wrapper */
.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

/* Executive Header Bar */
.dash-titlebar {
    min-height: 44px;
    padding: 6px 14px;
    background: var(--green-excel) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.dash-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.dash-title i {
    color: #ffc107;
}

.dash-title small {
    font-size: 11.5px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 6px;
}

.dash-title-tools {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

/* Header Quick Action Buttons */
.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 10px !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.35;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-add:hover {
    background: #dcfce7 !important;
    border-color: #4ade80 !important;
    color: #14532d !important;
}

/* Header Live Clock Badge */
.dash-header-clock {
    background: rgba(0, 0, 0, 0.28) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding: 4px 10px !important;
    display: inline-flex;
    align-items: center;
    line-height: 1.35;
    letter-spacing: 0.3px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
}
.dash-header-clock i {
    color: #86efac !important;
    margin-right: 5px;
}

/* Compact KPI Stat Cards (Matching theme) */
.dash-kpi-card {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    height: 60px;
    padding: 6px 12px 6px 0;
    position: relative;
    overflow: hidden;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.dash-kpi-card:hover {
    background: #f0fdf4;
    border-color: var(--green-excel);
    box-shadow: 0 2px 6px rgba(33, 115, 70, 0.12);
    transform: translateY(-1px);
}

.dash-kpi-stripe {
    width: 4px;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
}

.dash-kpi-icon {
    width: 38px;
    height: 38px;
    border-radius: 4px;
    margin-left: 10px;
    margin-right: 10px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.dash-kpi-body {
    flex-grow: 1;
    min-width: 0;
}

.dash-kpi-label {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #64748b;
    line-height: 1.1;
    margin-bottom: 2px;
}

.dash-kpi-val {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

.dash-kpi-sub {
    font-size: 10px;
    color: var(--green-excel);
    font-weight: 600;
    margin-top: 1px;
}

/* Secondary Panels / Sub-Cards */
.dash-sub-panel {
    background: #ffffff;
    border: 1px solid var(--line);
    border-top: 2px solid var(--green-excel);
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    margin-bottom: 12px;
    overflow: hidden;
}

.dash-sub-panel-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dash-sub-panel-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dash-sub-panel-title i {
    color: var(--green-excel);
}

.dash-sub-panel-body {
    padding: 10px 12px;
}

/* Chart Switch Buttons */
.btn-chart-toggle {
    background: #ffffff;
    color: #334155;
    border: 1px solid #cbd5e1;
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 3px;
    transition: all 0.15s ease;
    line-height: 1.2;
}

.btn-chart-toggle:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.btn-chart-toggle.active {
    background: var(--green-excel);
    color: #ffffff;
    border-color: var(--green-excel);
}

/* Tab Navigation */
.dash-tabs {
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.dash-tabs .nav-link {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 7px 12px;
    background: transparent;
    border-radius: 0;
    transition: all 0.15s;
}

.dash-tabs .nav-link.active {
    color: var(--green-excel);
    border-bottom-color: var(--green-excel);
    background: #ffffff;
    font-weight: 700;
}

/* Feed Lists */
.dash-feed-list {
    max-height: 310px;
    overflow-y: auto;
    scrollbar-width: thin;
    padding-right: 2px;
}

.dash-feed-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 8px 10px;
    margin-bottom: 6px;
    transition: all 0.15s ease;
    text-decoration: none !important;
    display: block;
    color: #1e293b;
}

.dash-feed-item:hover {
    background: #f0fdf4;
    border-color: var(--green-excel-border);
}

.dash-feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2px;
}

.dash-feed-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.dash-feed-desc {
    font-size: 10.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.35;
}

/* Birthday item */
.bday-card {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 3px;
    padding: 6px 10px;
    margin-bottom: 6px;
}

.bday-avatar-mini {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #f59e0b;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 11px;
    flex-shrink: 0;
}

/* Attendance Pills */
.att-badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}

.att-pill {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 10.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.att-pill-green { background: #dcfce7; color: #15803d; }
.att-pill-red { background: #fee2e2; color: #b91c1c; }
.att-pill-amber { background: #fef3c7; color: #b45309; }
.att-pill-blue { background: #e0f2fe; color: #0369a1; }

/* Financial Bar */
.fin-bar {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.fin-stat-box {
    display: flex;
    align-items: center;
    gap: 6px;
}

.fin-stat-label {
    font-size: 10.5px;
    color: #64748b;
    font-weight: 600;
}

.fin-stat-val {
    font-size: 12.5px;
    font-weight: 800;
    color: #0f172a;
}

/* To-Do Input */
.todo-input-group {
    display: flex;
    gap: 6px;
    margin-bottom: 8px;
}

.todo-input {
    flex-grow: 1;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 4px 8px;
    font-size: 11px;
    outline: none;
    height: 28px;
}

.todo-input:focus {
    border-color: var(--green-excel);
}

.todo-btn-add {
    background: var(--green-excel);
    color: #ffffff;
    border: none;
    border-radius: 3px;
    padding: 0 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    height: 28px;
}

.empty-state {
    text-align: center;
    padding: 16px 8px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 1.5rem;
    margin-bottom: 4px;
}

.empty-state p {
    font-size: 11px;
    margin: 0;
}
</style>

<div class="content-wrapper admin-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">
            
            <!-- ========================================================
                 MAIN EXECUTIVE DASHBOARD CARD
                 ======================================================== -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar with Core Quick Action Buttons -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-dashboard"></i>
                        <span>{{ $roleName->name ?? 'Administrator' }} Dashboard</span>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <a href="{{ url('Fees/add') }}" class="btn-header-action btn-header-add" title="Collect Fee">
                            <i class="fa fa-money text-success mr-1"></i> Collect Fee
                        </a>
                        <a href="{{ url('admissionView') }}" class="btn-header-action" title="Students Directory">
                            <i class="fa fa-users text-primary mr-1"></i> Students
                        </a>
                        <a href="{{ url('admissionAdd') }}" class="btn-header-action" title="New Admission">
                            <i class="fa fa-user-plus text-success mr-1"></i> New Admission
                        </a>
                        <a href="{{ url('staff/Attendance/view') }}" class="btn-header-action" title="Staff Attendance">
                            <i class="fa fa-calendar-check-o text-warning mr-1"></i> Staff Attendance
                        </a>
                        <a href="{{ url('fee_dashboard') }}" class="btn-header-action" title="Fees Reports">
                            <i class="fa fa-bar-chart text-info mr-1"></i> Fee Reports
                        </a>
                        <a href="{{ url('notice_board/view') }}" class="btn-header-action" title="Notices & Circulars">
                            <i class="fa fa-bullhorn text-danger mr-1"></i> Notices
                        </a>
                        <span class="dash-header-clock d-none d-md-inline-flex" title="Current Time">
                            <i class="fa fa-clock-o"></i> <span id="dashLiveClock">--:--:--</span>
                        </span>
                    </div>
                </div>

                <!-- 2. Top 4 KPI Metrics Grid -->
                <div class="p-2">
                    <div class="row mx-n1">
                        
                        <!-- Total Active Students -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-12 px-1 mb-2 mb-xl-0">
                            <a href="{{ url('admissionView') }}" class="dash-kpi-card" title="View Student Directory">
                                <div class="dash-kpi-stripe" style="background: #16a34a;"></div>
                                <div class="dash-kpi-icon" style="background: #f0fdf4; color: #16a34a;">
                                    <i class="fa fa-graduation-cap"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">{{ __('dashboard.TOTAL STUDENTS') }}</div>
                                    <div class="dash-kpi-val">{{ number_format($totalStudents) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub">Directory &rarr;</span>
                                </div>
                            </a>
                        </div>

                        <!-- Today's Collection -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-12 px-1 mb-2 mb-xl-0">
                            <form id="todayFeeForm" action="{{ url('fees/index') }}" method="post" style="margin: 0;">
                                @csrf  
                                <input type="hidden" name="starting" value="{{ date('Y-m-d') }}">
                                <input type="hidden" name="ending" value="{{ date('Y-m-d') }}">
                                <div class="dash-kpi-card" onclick="document.getElementById('todayFeeForm').submit();" style="cursor: pointer;" title="View Today Transactions">
                                    <div class="dash-kpi-stripe" style="background: #d97706;"></div>
                                    <div class="dash-kpi-icon" style="background: #fffbeb; color: #d97706;">
                                        <i class="fa fa-inr"></i>
                                    </div>
                                    <div class="dash-kpi-body">
                                        <div class="dash-kpi-label">{{ __('dashboard.TODAY COLLECTION') }}</div>
                                        <div class="dash-kpi-val">₹ {{ number_format($todayFees, 2) }}</div>
                                    </div>
                                    <div class="text-right">
                                        <span class="dash-kpi-sub" style="color: #d97706;">Ledger &rarr;</span>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Total Fees Collection -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-12 px-1 mb-2 mb-xl-0">
                            <a href="{{ url('fee_dashboard') }}" class="dash-kpi-card" title="Open Fees Dashboard">
                                <div class="dash-kpi-stripe" style="background: #2563eb;"></div>
                                <div class="dash-kpi-icon" style="background: #eff6ff; color: #2563eb;">
                                    <i class="fa fa-line-chart"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">{{ __('dashboard.FEES COLLECTION') }}</div>
                                    <div class="dash-kpi-val">₹ {{ number_format($totalFees, 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub" style="color: #2563eb;">Overview &rarr;</span>
                                </div>
                            </a>
                        </div>

                        <!-- Total Staff & Users -->
                        <div class="col-xl-3 col-lg-6 col-md-6 col-12 px-1 mb-2 mb-xl-0">
                            <a href="{{ url('viewUser') }}" class="dash-kpi-card" title="Manage Staff & Users">
                                <div class="dash-kpi-stripe" style="background: #7c3aed;"></div>
                                <div class="dash-kpi-icon" style="background: #faf5ff; color: #7c3aed;">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">{{ __('dashboard.TOTAL USERS') }}</div>
                                    <div class="dash-kpi-val">{{ number_format($totalUsers) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub" style="color: #7c3aed;">Users &rarr;</span>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ========================================================
                 3. ANALYTICS, ATTENDANCE & NOTIFICATIONS (2-COLUMN LAYOUT)
                 ======================================================== -->
            <div class="row mx-n1">
                
                <!-- Left Column (8 cols): Fee Chart & Attendance -->
                <div class="col-lg-8 col-12 px-1">
                    
                    <!-- Monthly Fee Chart Panel -->
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header">
                            <h3 class="dash-sub-panel-title">
                                <i class="fa fa-bar-chart"></i> <span id="chartHeading">Monthly Fee Collection Trend</span>
                            </h3>
                            <div class="d-flex align-items-center" style="gap: 4px;">
                                <button id="btnMonthly" class="btn-chart-toggle active">
                                    <i class="fa fa-calendar"></i> Monthly View
                                </button>
                                <button id="btnWeekly" class="btn-chart-toggle">
                                    <i class="fa fa-calendar-check-o"></i> Day-wise ({{ date('d') }} Days)
                                </button>
                            </div>
                        </div>
                        <div class="dash-sub-panel-body p-2">
                            <div style="position: relative; height: 240px; width: 100%;">
                                <canvas id="myChart"></canvas>
                            </div>
                        </div>
                        <div class="fin-bar">
                            <div class="fin-stat-box">
                                <span class="fin-stat-label">Today's Inflow:</span>
                                <span class="fin-stat-val text-success">₹ {{ number_format($todayFees, 2) }}</span>
                            </div>
                            <div class="fin-stat-box">
                                <span class="fin-stat-label">Session Overall:</span>
                                <span class="fin-stat-val text-primary">₹ {{ number_format($totalFees, 2) }}</span>
                            </div>
                            <div>
                                <a href="{{ url('Fees/add') }}" class="btn-header-action btn-header-add" style="padding: 3px 8px; font-size: 10.5px;">
                                    <i class="fa fa-plus mr-1"></i> Collect Fee Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Today Attendance Overview (Students & Staff) -->
                    <div class="row mx-n1">
                        
                        <!-- Student Attendance -->
                        <div class="col-md-6 col-12 px-1 mb-2">
                            <div class="dash-sub-panel mb-0" style="height: 100%;">
                                <div class="dash-sub-panel-header">
                                    <h3 class="dash-sub-panel-title">
                                        <i class="fa fa-user-circle"></i> Student Attendance
                                    </h3>
                                    <a href="{{ url('studentsAttendanceView') }}" class="text-muted" style="font-size: 10.5px; font-weight: 600;">
                                        View Register &rarr;
                                    </a>
                                </div>
                                <div class="dash-sub-panel-body">
                                    <div style="position: relative; height: 140px;">
                                        <canvas id="studentAttendanceChart"></canvas>
                                    </div>
                                    <div class="att-badge-row justify-content-center">
                                        <span class="att-pill att-pill-green"><i class="fa fa-check"></i> Present: {{ $chartAttendanceStudents['Present'] ?? 0 }}</span>
                                        <span class="att-pill att-pill-red"><i class="fa fa-times"></i> Absent: {{ $chartAttendanceStudents['Absent'] ?? 0 }}</span>
                                        <span class="att-pill att-pill-amber"><i class="fa fa-clock-o"></i> Half Day: {{ $chartAttendanceStudents['Half_Day'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Staff Attendance -->
                        <div class="col-md-6 col-12 px-1 mb-2">
                            <div class="dash-sub-panel mb-0" style="height: 100%;">
                                <div class="dash-sub-panel-header">
                                    <h3 class="dash-sub-panel-title">
                                        <i class="fa fa-briefcase"></i> Staff Attendance
                                    </h3>
                                    <a href="{{ url('staff/Attendance/view') }}" class="text-muted" style="font-size: 10.5px; font-weight: 600;">
                                        View Register &rarr;
                                    </a>
                                </div>
                                <div class="dash-sub-panel-body">
                                    <div style="position: relative; height: 140px;">
                                        <canvas id="teacherAttendanceChart"></canvas>
                                    </div>
                                    <div class="att-badge-row justify-content-center">
                                        <span class="att-pill att-pill-green"><i class="fa fa-check"></i> Present: {{ $chartAttendanceTeachers['Present'] ?? 0 }}</span>
                                        <span class="att-pill att-pill-red"><i class="fa fa-times"></i> Absent: {{ $chartAttendanceTeachers['Absent'] ?? 0 }}</span>
                                        <span class="att-pill att-pill-blue"><i class="fa fa-home"></i> Leave: {{ $chartAttendanceTeachers['Work_From_Home'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Right Column (4 cols): Notifications, Birthdays & To-Do -->
                <div class="col-lg-4 col-12 px-1">
                    
                    <!-- Notification Center Tabbed Panel -->
                    <div class="dash-sub-panel">
                        <ul class="nav nav-tabs dash-tabs w-100" id="dashFeedTab" role="tablist">
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link active" id="notices-tab" data-toggle="tab" href="#noticesTab" role="tab">
                                    <i class="fa fa-bullhorn"></i> Notices
                                    @if(count($noticeBoard) > 0)
                                        <span class="badge badge-success ml-1">{{ count($noticeBoard) }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link" id="birthdays-tab" data-toggle="tab" href="#birthdaysTab" role="tab">
                                    <i class="fa fa-birthday-cake"></i> Birthdays
                                    @if(count($getstudentbirthday) > 0)
                                        <span class="badge badge-warning ml-1 text-white">{{ count($getstudentbirthday) }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link" id="remarks-tab" data-toggle="tab" href="#remarksTab" role="tab">
                                    <i class="fa fa-comment-o"></i> Remarks
                                    @if(count($getremark) > 0)
                                        <span class="badge badge-danger ml-1">{{ count($getremark) }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>

                        <div class="dash-sub-panel-body p-2">
                            <div class="tab-content" id="dashFeedTabContent">
                                
                                <!-- Notices Tab -->
                                <div class="tab-pane fade show active" id="noticesTab" role="tabpanel">
                                    <div class="dash-feed-list">
                                        @if(!empty($noticeBoard) && count($noticeBoard) > 0)
                                            @foreach($noticeBoard as $item)
                                                <a href="{{ url('notice_board/view') }}/{{ $item->id }}" class="dash-feed-item">
                                                    <div class="dash-feed-header">
                                                        <span class="dash-feed-title">{{ $item->title ?? 'Notice Announcement' }}</span>
                                                        <span class="badge badge-success" style="font-size: 9.5px;">Active</span>
                                                    </div>
                                                    <p class="dash-feed-desc">
                                                        {{ Str::limit(strip_tags(html_entity_decode($item->message ?? '')), 85) }}
                                                    </p>
                                                    <div class="mt-1 d-flex justify-content-between align-items-center">
                                                        <span style="font-size: 9.5px; color: #94a3b8;"><i class="fa fa-calendar-o"></i> {{ date('d M Y', strtotime($item->from_date ?? 'now')) }}</span>
                                                        <span style="font-size: 10px; color: var(--green-excel); font-weight: 600;">Read &rarr;</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        @else
                                            <div class="empty-state">
                                                <i class="fa fa-bell-slash-o"></i>
                                                <p>No active notices right now.</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-center pt-2 mt-1 border-top">
                                        <a href="{{ url('notice_board/add') }}" class="btn-header-action btn-header-add" style="font-size: 10px; padding: 2px 8px;">
                                            <i class="fa fa-plus mr-1"></i> Post Notice
                                        </a>
                                    </div>
                                </div>

                                <!-- Birthdays Tab -->
                                <div class="tab-pane fade" id="birthdaysTab" role="tabpanel">
                                    <div class="dash-feed-list">
                                        @if(!empty($getstudentbirthday) && count($getstudentbirthday) > 0)
                                            @foreach($getstudentbirthday as $bday)
                                                <div class="bday-card">
                                                    <div class="bday-avatar-mini">
                                                        {{ strtoupper(substr($bday->first_name ?? 'S', 0, 1)) }}
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="font-weight-bold" style="font-size: 11px; color: #92400e;">
                                                            {{ $bday->first_name ?? '' }} {{ $bday->last_name ?? '' }} 🎂
                                                        </div>
                                                        <div style="font-size: 9.5px; color: #b45309;">
                                                            Adm No: {{ $bday->admissionNo ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                    <a href="{{ url('admissionView') }}" class="btn btn-xs btn-warning text-white" style="border-radius: 3px; font-size: 9.5px; font-weight: 600; padding: 1px 6px;">
                                                        Wish 🎉
                                                    </a>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="empty-state">
                                                <i class="fa fa-birthday-cake"></i>
                                                <p>No student birthdays today.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Remarks Tab -->
                                <div class="tab-pane fade" id="remarksTab" role="tabpanel">
                                    <div class="dash-feed-list">
                                        @if(!empty($getremark) && count($getremark) > 0)
                                            @foreach($getremark as $rem)
                                                <a href="{{ url('students/index') }}" class="dash-feed-item">
                                                    <div class="dash-feed-header">
                                                        <span class="dash-feed-title text-danger"><i class="fa fa-exclamation-circle"></i> Student Remark</span>
                                                        <span style="font-size: 9.5px; color: #94a3b8;">{{ date('d M') }}</span>
                                                    </div>
                                                    <p class="dash-feed-desc">
                                                        {{ $rem->remark ?? 'General Student Log' }}
                                                    </p>
                                                </a>
                                            @endforeach
                                        @else
                                            <div class="empty-state">
                                                <i class="fa fa-commenting-o"></i>
                                                <p>No remarks logged today.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Quick Personal Task List -->
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header">
                            <h3 class="dash-sub-panel-title">
                                <i class="fa fa-tasks"></i> Personal Task List
                            </h3>
                            <a href="{{ url('to_do_assign') }}" class="text-muted" style="font-size: 10.5px; font-weight: 600;">
                                View All &rarr;
                            </a>
                        </div>
                        <div class="dash-sub-panel-body p-2">
                            <div class="todo-input-group">
                                <input type="text" id="taskInput" class="todo-input" placeholder="Type new task...">
                                <button type="button" class="todo-btn-add add_task">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                            <div class="dash-feed-list todoList" style="max-height: 170px;">
                                <div class="empty-state">
                                    <i class="fa fa-spinner fa-spin"></i>
                                    <p>Loading tasks...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>
</div>

<!-- ========================================================
     DASHBOARD JAVASCRIPT & CHARTS (LIGHTWEIGHT & OPTIMIZED)
     ======================================================== -->
<script>
$(document).ready(function() {
    
    // 1. Live Digital Clock
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        $('#dashLiveClock').text(hours + ':' + minutes + ':' + seconds + ' ' + ampm);
    }
    updateClock();
    setInterval(updateClock, 1000);

    // 2. Fee Collection Chart (ChartJS)
    var monthlyData = @json($dataMonth ?? ['val1'=>[], 'val2'=>[]]);
    var weeklyData = @json($dataWeek ?? ['val1'=>[], 'val2'=>[]]);
    var feeChartInstance = null;

    function renderFeeChart(labels, values, labelText) {
        var ctx = document.getElementById('myChart');
        if (!ctx) return;
        
        if (feeChartInstance) {
            feeChartInstance.destroy();
        }

        var chartCtx = ctx.getContext('2d');
        var gradient = chartCtx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(33, 115, 70, 0.85)');
        gradient.addColorStop(1, 'rgba(33, 115, 70, 0.15)');

        feeChartInstance = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: labelText,
                    data: values,
                    backgroundColor: gradient,
                    borderColor: '#185c37',
                    borderWidth: 1.5,
                    borderRadius: 4,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    backgroundColor: '#1e293b',
                    titleFontColor: '#ffffff',
                    bodyFontColor: '#86efac',
                    cornerRadius: 4,
                    callbacks: {
                        label: function(tooltipItem) {
                            return ' ₹ ' + Number(tooltipItem.yLabel).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#64748b', fontSize: 10.5 }
                    }],
                    yAxes: [{
                        gridLines: { color: '#f1f5f9', zeroLineColor: '#e2e8f0' },
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#64748b',
                            fontSize: 10.5,
                            callback: function(value) {
                                if (value >= 100000) return '₹ ' + (value / 100000).toFixed(1) + 'L';
                                if (value >= 1000) return '₹ ' + (value / 1000).toFixed(1) + 'k';
                                return '₹ ' + value;
                            }
                        }
                    }]
                }
            }
        });
    }

    renderFeeChart(monthlyData.val1 || [], monthlyData.val2 || [], 'Monthly Collection');

    $('#btnMonthly').on('click', function() {
        $('.btn-chart-toggle').removeClass('active');
        $(this).addClass('active');
        $('#chartHeading').text('Monthly Fee Collection Trend');
        renderFeeChart(monthlyData.val1 || [], monthlyData.val2 || [], 'Monthly Collection');
    });

    $('#btnWeekly').on('click', function() {
        $('.btn-chart-toggle').removeClass('active');
        $(this).addClass('active');
        $('#chartHeading').text('Day-wise Collection (Current Month)');
        renderFeeChart(weeklyData.val1 || [], weeklyData.val2 || [], 'Day-wise Collection');
    });

    // 3. Student Attendance Donut Chart
    var stuCtx = document.getElementById('studentAttendanceChart');
    if (stuCtx) {
        new Chart(stuCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Half Day', 'WFH/Leave'],
                datasets: [{
                    data: [
                        {{ $chartAttendanceStudents['Present'] ?? 0 }},
                        {{ $chartAttendanceStudents['Absent'] ?? 0 }},
                        {{ $chartAttendanceStudents['Half_Day'] ?? 0 }},
                        {{ $chartAttendanceStudents['Work_From_Home'] ?? 0 }}
                    ],
                    backgroundColor: ['#16a34a', '#ef4444', '#f59e0b', '#3b82f6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                cutoutPercentage: 70,
                legend: { display: false }
            }
        });
    }

    // 4. Staff Attendance Donut Chart
    var teaCtx = document.getElementById('teacherAttendanceChart');
    if (teaCtx) {
        new Chart(teaCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Half Day', 'WFH/Leave'],
                datasets: [{
                    data: [
                        {{ $chartAttendanceTeachers['Present'] ?? 0 }},
                        {{ $chartAttendanceTeachers['Absent'] ?? 0 }},
                        {{ $chartAttendanceTeachers['Half_Day'] ?? 0 }},
                        {{ $chartAttendanceTeachers['Work_From_Home'] ?? 0 }}
                    ],
                    backgroundColor: ['#16a34a', '#ef4444', '#f59e0b', '#3b82f6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                cutoutPercentage: 70,
                legend: { display: false }
            }
        });
    }

    // 5. To-Do List AJAX Handlers
    function loadTodoList() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
        $.ajax({
            url: '{{ url("task_list") }}',
            type: 'post',
            data: { status: "1" },
            success: function(result) {
                if (result && result.trim().length > 0) {
                    $('.todoList').html(result);
                } else {
                    $('.todoList').html('<div class="empty-state"><i class="fa fa-check-circle-o text-success"></i><p>No pending tasks.</p></div>');
                }
            },
            error: function() {
                $('.todoList').html('<div class="empty-state"><p class="text-muted">No tasks available.</p></div>');
            }
        });
    }
    loadTodoList();

    $(document).on('click', '.add_task', function() {
        var task = $('#taskInput').val().trim();
        if (!task) {
            $('#taskInput').focus().css('border-color', '#ef4444');
            return;
        }
        $('#taskInput').css('border-color', '');
        
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
        $.ajax({
            type: "POST",
            url: "{{ url('add/task') }}",
            data: { task: task },
            success: function(response) {
                toastr.success('Task added successfully.');
                $('#taskInput').val('');
                loadTodoList();
            }
        });
    });

    $('#taskInput').on('keypress', function(e) {
        if (e.which === 13) {
            $('.add_task').click();
        }
    });

    $(document).on('click', ".task_status", function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        $.ajax({
            url: '{{ url("status/task") }}',
            type: 'post',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { status: status, id: id },
            success: function() {
                toastr.success('Task updated.');
                loadTodoList();
            }
        });
    });

    $(document).on('click', ".task_delete", function() {
        var task_id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{ url('delete/task') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { task_id: task_id },
            success: function() {
                toastr.error('Task deleted.');
                loadTodoList();
            }
        });
    });

});
</script>

@endsection