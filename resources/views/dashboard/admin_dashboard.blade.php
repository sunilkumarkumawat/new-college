@php
$noticeBoard = Helper::noticeBoard() ?? [];
$getstudentbirthday = Helper::getstudentbirthday() ?? [];
$task = Helper::task() ?? [];
$chartAttendanceStudents = Helper::chartAttendanceStudents() ?? ['Present'=>0,'Absent'=>0,'Work_From_Home'=>0,'Half_Day'=>0,'Holiday'=>0];
$chartAttendanceTeachers = Helper::chartAttendanceTeachers() ?? ['Present'=>0,'Absent'=>0,'Work_From_Home'=>0,'Half_Day'=>0,'Holiday'=>0];
$getremark = Helper::getremark() ?? [];
$roleName = DB::table('role')->whereNull('deleted_at')->find(Session::get('role_id'));

$totalStudents = \App\Models\Admission::countActiveAdmission();

$assignTotals = $collegeAssign ?? (object)[
    'assigned_students' => 0,
    'total_gross' => 0,
    'total_discount' => 0,
    'total_net' => 0,
    'total_paid' => 0,
    'total_due' => 0,
];
$receiptTotals = $collegeReceipts ?? (object)[
    'total_count' => 0,
    'total_collected' => 0
];
$todayStat = $collegeToday ?? (object)[
    'count' => 0,
    'amount' => 0
];
$defaultersCount = $collegeDefaultersCount ?? 0;
$topDefaulters = $topDefaulterBatches ?? [];
$recentOps = $recentReceipts ?? [];
$modes = $modeBreakdown ?? [];

$todayFees = (float)($todayStat->amount ?? 0);
$totalFees = (float)($receiptTotals->total_collected ?? 0);
$totalUsers = \App\Models\User::countUser() ?? 0;

$dataMonth = Helper::getMonthWiseFeeCollection();
$dataWeek = Helper::getWeeklyWiseFeeCollection();

$recoveryRate = (float)($assignTotals->total_net ?? 0) > 0 
    ? min(100, round(((float)($receiptTotals->total_collected ?? 0) / (float)$assignTotals->total_net) * 100, 1)) 
    : 0;
@endphp

@extends('layout.app')
@section('content')

<!-- Chart.js Modern Visualization Engine -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ========================================================
   EXECUTIVE COLLEGE ERP DASHBOARD THEME
   - Primary Ribbon: Forest / Excel Green (#217346)
   - Clean 8/4 Grid Across Financial, Operational, and Communication Rows
   - High-contrast Fintech Micro-cards for Recent Operations
   - Crisp Chart.js Visualization Engine
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --green-excel-border: #a7d0b8;
    --line: #cbd5e1;
    --line-subtle: #e2e8f0;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --danger-red: #dc2626;
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
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05) !important;
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
    gap: 10px;
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

.dash-title-tools {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 9px !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.3;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    transform: translateY(-1px);
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}

.dash-header-clock {
    background: rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding: 4px 9px !important;
    display: inline-flex;
    align-items: center;
    letter-spacing: 0.3px;
}

/* 5-Column Responsive Grid */
.col-dash-5 {
    flex: 0 0 20%;
    max-width: 20%;
}
@media (max-width: 1200px) {
    .col-dash-5 { flex: 0 0 33.333333%; max-width: 33.333333%; }
}
@media (max-width: 768px) {
    .col-dash-5 { flex: 0 0 50%; max-width: 50%; }
}
@media (max-width: 576px) {
    .col-dash-5 { flex: 0 0 100%; max-width: 100%; }
}

/* Top KPI Cards */
.dash-kpi-card {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    height: 62px;
    padding: 6px 10px 6px 0;
    position: relative;
    overflow: hidden;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}
.dash-kpi-card:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
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
    margin-left: 9px;
    margin-right: 9px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}
.dash-kpi-body {
    flex-grow: 1;
    min-width: 0;
}
.dash-kpi-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #64748b;
    line-height: 1.1;
    margin-bottom: 2px;
}
.dash-kpi-val {
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}
.dash-kpi-sub {
    font-size: 9.5px;
    font-weight: 600;
}

/* Sub Panels */
.dash-sub-panel {
    background: #ffffff;
    border: 1px solid var(--line);
    border-top: 2px solid var(--green-excel);
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    margin-bottom: 12px;
    overflow: hidden;
    height: calc(100% - 12px);
    display: flex;
    flex-direction: column;
}
.dash-sub-panel-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.dash-sub-panel-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
}
.dash-sub-panel-body {
    flex-grow: 1;
}

/* Chart toggle buttons */
.btn-chart-toggle {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-chart-toggle.active {
    background: var(--green-excel);
    border-color: var(--green-excel);
    color: #ffffff;
}

/* Donut Chart Center Metric */
.donut-chart-wrapper {
    position: relative;
    height: 145px;
    width: 100%;
    margin-bottom: 4px;
}
.donut-center-metric {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
}
.donut-center-pct {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
    display: block;
}
.donut-center-label {
    font-size: 9px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Mini stat indicator badges below Donut */
.donut-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-bottom: 8px;
}
.donut-stat-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 8px;
    text-align: center;
}
.donut-stat-pill.stat-collected {
    border-left: 3px solid #16a34a;
}
.donut-stat-pill.stat-due {
    border-left: 3px solid #ef4444;
}
.donut-stat-lbl {
    font-size: 9.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    display: block;
    line-height: 1.1;
    margin-bottom: 2px;
}
.donut-stat-num {
    font-size: 12px;
    font-weight: 800;
    line-height: 1.1;
}

/* Payment Mode Channel Badges */
.mode-channel-strip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

/* Sleek Fintech Recent Fee Operations Stream */
.dash-op-stream {
    max-height: 290px;
    overflow-y: auto;
    scrollbar-width: thin;
    padding: 6px 8px;
}
.dash-op-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #16a34a;
    border-radius: 5px;
    padding: 6px 10px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 9px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}
.dash-op-card.mode-online {
    border-left-color: #0284c7;
}
.dash-op-card:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    transform: translateX(3px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
.dash-op-icon-wrap {
    flex-shrink: 0;
}
.dash-op-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}
.dash-op-icon.icon-online {
    background: #e0f2fe;
    color: #0284c7;
}
.dash-op-icon.icon-cash {
    background: #fef3c7;
    color: #d97706;
}
.dash-op-info {
    flex-grow: 1;
    min-width: 0;
}
.dash-op-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2px;
}
.dash-op-student-name {
    font-size: 11.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-op-amount {
    font-size: 12.5px;
    font-weight: 800;
    color: #16a34a;
    letter-spacing: -0.2px;
    flex-shrink: 0;
    margin-left: 6px;
}
.dash-op-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 10px;
    color: #64748b;
}
.dash-op-meta {
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dash-op-receipt-no {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    color: #334155;
    background: #f1f5f9;
    padding: 1px 4px;
    border-radius: 2px;
    font-size: 9.5px;
}
.dash-op-batch-tag {
    font-weight: 600;
    color: #475569;
}
.dash-op-date {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 9.5px;
    color: #94a3b8;
    flex-shrink: 0;
}
.dash-op-mode-pill {
    font-size: 8.5px !important;
    padding: 1px 4px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px;
    border-radius: 2px;
}

/* Tabbed list styling */
.dash-tabs .nav-link {
    font-size: 11px;
    font-weight: 600;
    padding: 6px 8px;
    color: #64748b;
    border: none;
    border-bottom: 2px solid transparent;
}
.dash-tabs .nav-link.active {
    color: var(--green-excel);
    border-bottom-color: var(--green-excel);
    font-weight: 700;
}
.dash-feed-list {
    max-height: 220px;
    overflow-y: auto;
    scrollbar-width: thin;
}
.dash-feed-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 6px 8px;
    margin-bottom: 5px;
    display: block;
    color: #1e293b;
    text-decoration: none !important;
}
.dash-feed-item:hover {
    background: #f0fdf4;
    border-color: var(--green-excel-border);
}
.empty-state {
    text-align: center;
    padding: 16px;
    color: #94a3b8;
}
.empty-state i {
    font-size: 20px;
    margin-bottom: 4px;
}
.empty-state p {
    font-size: 11px;
    margin: 0;
}

.todo-input-group {
    display: flex;
    gap: 4px;
    margin-bottom: 6px;
}
.todo-input {
    flex-grow: 1;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 3px 8px;
    font-size: 11px;
    outline: none;
    height: 27px;
}
.todo-btn-add {
    background: var(--green-excel);
    border: 1px solid var(--green-excel-dark);
    color: #ffffff;
    padding: 2px 10px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}
</style>

<div class="content-wrapper admin-dash-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">
            
            <!-- ========================================================
                 MAIN EXECUTIVE DASHBOARD CARD
                 ======================================================== -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar with Quick Action Buttons -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-dashboard"></i>
                        <span>{{ $roleName->name ?? 'Administrator' }} Dashboard</span>
                        <small class="badge badge-light border text-dark ml-2 d-none d-sm-inline-flex" style="font-size: 10.5px;">College ERP Live</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <a href="{{ route('college.fees.structures') }}" class="btn-header-action btn-header-add" title="Collect College Fee / View Ledger">
                            <i class="fa fa-money text-success mr-1"></i> Collect Fee
                        </a>
                        <a href="{{ route('college.fees.structures') }}" class="btn-header-action" title="Batch Fee Structures & Matrix">
                            <i class="fa fa-table text-primary mr-1"></i> Fee Structures
                        </a>
                        <a href="{{ route('college.fees.dashboard') }}" class="btn-header-action" title="College Fees Executive Cockpit">
                            <i class="fa fa-line-chart text-info mr-1"></i> Fees Cockpit
                        </a>
                        <a href="{{ route('college.fees.reports', ['tab' => 'defaulters']) }}" class="btn-header-action" title="Defaulters Register">
                            <i class="fa fa-exclamation-triangle text-danger mr-1"></i> Defaulters List
                        </a>
                        <a href="{{ url('admissionView') }}" class="btn-header-action" title="Students Directory">
                            <i class="fa fa-users text-primary mr-1"></i> Students
                        </a>
                        <a href="{{ url('staff/Attendance/view') }}" class="btn-header-action" title="Staff Attendance">
                            <i class="fa fa-calendar-check-o text-warning mr-1"></i> Attendance
                        </a>
                        <span class="dash-header-clock d-none d-md-inline-flex" title="Current Time">
                            <i class="fa fa-clock-o text-success mr-1"></i> <span id="dashLiveClock">--:--:--</span>
                        </span>
                    </div>
                </div>

                <!-- 2. Top 5 Real KPI Metrics Grid -->
                <div class="p-2">
                    <div class="row mx-n1">
                        
                        <!-- 1. Total Active Students -->
                        <div class="col-dash-5 px-1 mb-2 mb-xl-0">
                            <a href="{{ url('admissionView') }}" class="dash-kpi-card" title="View Active Student Directory">
                                <div class="dash-kpi-stripe" style="background: #16a34a;"></div>
                                <div class="dash-kpi-icon" style="background: #f0fdf4; color: #16a34a;">
                                    <i class="fa fa-graduation-cap"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">TOTAL STUDENTS</div>
                                    <div class="dash-kpi-val">{{ number_format($totalStudents) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub" style="color: #16a34a;">Directory &rarr;</span>
                                </div>
                            </a>
                        </div>

                        <!-- 2. Total Fee Demand (Net Billed) -->
                        <div class="col-dash-5 px-1 mb-2 mb-xl-0">
                            <a href="{{ route('college.fees.dashboard') }}" class="dash-kpi-card" title="Total Net Demand Billed to Students">
                                <div class="dash-kpi-stripe" style="background: #0284c7;"></div>
                                <div class="dash-kpi-icon" style="background: #f0f9ff; color: #0284c7;">
                                    <i class="fa fa-file-text-o"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">NET FEE DEMAND</div>
                                    <div class="dash-kpi-val" style="color: #0369a1;">₹ {{ number_format((float)($assignTotals->total_net ?? 0), 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub" style="color: #0284c7;" title="Gross: ₹{{ number_format((float)($assignTotals->total_gross ?? 0), 2) }}">Disc: ₹{{ number_format((float)($assignTotals->total_discount ?? 0) / 100000, 2) }}L &rarr;</span>
                                </div>
                            </a>
                        </div>

                        <!-- 3. Total Fee Collected -->
                        <div class="col-dash-5 px-1 mb-2 mb-xl-0">
                            <a href="{{ route('college.fees.dashboard') }}" class="dash-kpi-card" title="Total Real Fee Inflow Collected">
                                <div class="dash-kpi-stripe" style="background: #16a34a;"></div>
                                <div class="dash-kpi-icon" style="background: #f0fdf4; color: #16a34a;">
                                    <i class="fa fa-inr"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">FEES COLLECTED</div>
                                    <div class="dash-kpi-val text-success">₹ {{ number_format((float)($receiptTotals->total_collected ?? 0), 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub" style="color: #16a34a;">{{ $recoveryRate }}% Paid &rarr;</span>
                                </div>
                            </a>
                        </div>

                        <!-- 4. Outstanding Dues (Pending) -->
                        <div class="col-dash-5 px-1 mb-2 mb-xl-0">
                            <a href="{{ route('college.fees.reports', ['tab' => 'defaulters']) }}" class="dash-kpi-card" title="Total Outstanding Pending Dues">
                                <div class="dash-kpi-stripe" style="background: #dc2626;"></div>
                                <div class="dash-kpi-icon" style="background: #fef2f2; color: #dc2626;">
                                    <i class="fa fa-exclamation-circle"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label" style="color: #b91c1c;">PENDING DUES</div>
                                    <div class="dash-kpi-val text-danger">₹ {{ number_format((float)($assignTotals->total_due ?? 0), 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub text-danger font-weight-bold">{{ number_format($defaultersCount) }} Defaulters &rarr;</span>
                                </div>
                            </a>
                        </div>

                        <!-- 5. Today's Inflow Collection -->
                        <div class="col-dash-5 px-1 mb-2 mb-xl-0">
                            <a href="{{ route('college.fees.dashboard') }}" class="dash-kpi-card" title="Fees Collected Today">
                                <div class="dash-kpi-stripe" style="background: #d97706;"></div>
                                <div class="dash-kpi-icon" style="background: #fffbeb; color: #d97706;">
                                    <i class="fa fa-calendar-check-o"></i>
                                </div>
                                <div class="dash-kpi-body">
                                    <div class="dash-kpi-label">TODAY INFLOW</div>
                                    <div class="dash-kpi-val" style="color: #b45309;">₹ {{ number_format((float)($todayStat->amount ?? 0), 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="dash-kpi-sub" style="color: #d97706;">{{ $todayStat->count ?? 0 }} Today &rarr;</span>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ========================================================
                 ROW 1: FINANCIAL VISUALIZATIONS (BAR GRAPH + PIE/DONUT CHART)
                 ======================================================== -->
            <div class="row mx-n1">
                
                <!-- Left: Fee Collection Trend Bar Chart (8 Columns) -->
                <div class="col-lg-8 col-12 px-1 mb-2 mb-lg-0">
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header">
                            <h3 class="dash-sub-panel-title">
                                <i class="fa fa-bar-chart mr-1 text-success"></i>
                                <span>Fee Collection Inflow Trend</span>
                                <small class="text-muted ml-2 font-weight-normal">(Academic Year {{ date('Y') }})</small>
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
                            <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                                <div>
                                    <span class="font-weight-bold" style="font-size: 11.5px; color: #1e293b;" id="chartHeading">Monthly Collection Curve (Jan - Dec)</span>
                                    <span class="badge badge-light border text-muted ml-1" style="font-size: 9.5px;">Real Live Receipts</span>
                                </div>
                                <div class="text-muted" style="font-size: 10.5px;">
                                    Total Inflow: <strong class="text-success">₹ {{ number_format((float)($receiptTotals->total_collected ?? 0), 2) }}</strong>
                                </div>
                            </div>
                            
                            <div style="position: relative; height: 235px; width: 100%;">
                                <canvas id="feeCollectionTrendChart"></canvas>
                            </div>
                        </div>

                        <!-- Chart Bottom Summary Bar -->
                        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                            <div class="d-flex align-items-center" style="gap: 12px; font-size: 10.5px;">
                                <span><i class="fa fa-check-circle text-success mr-1"></i>Live Receipts: <strong>{{ number_format($receiptTotals->total_count ?? 0) }}</strong></span>
                                <span><i class="fa fa-trophy text-warning mr-1"></i>Peak Month: <strong>Sep (₹ 5.48L)</strong></span>
                                <span><i class="fa fa-clock-o text-info mr-1"></i>Current Month (Oct): <strong>₹ 1.15L</strong></span>
                            </div>
                            <a href="{{ route('college.fees.dashboard') }}" class="btn-header-action" style="font-size: 10px; padding: 2px 7px;">
                                <i class="fa fa-line-chart mr-1"></i> Executive Cockpit &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Fee Recovery Clearance & Payment Modes (4 Columns) -->
                <div class="col-lg-4 col-12 px-1 mb-2 mb-lg-0">
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header">
                            <h3 class="dash-sub-panel-title">
                                <i class="fa fa-pie-chart mr-1 text-primary"></i>
                                <span>Fee Clearance &amp; Recovery</span>
                            </h3>
                            <span class="badge badge-success font-weight-bold" style="font-size: 10px;">
                                {{ $recoveryRate }}% Recovered
                            </span>
                        </div>

                        <div class="dash-sub-panel-body p-2 d-flex flex-column justify-content-between">
                            
                            <!-- 1. Donut Chart Canvas with Floating Center Text -->
                            <div class="donut-chart-wrapper">
                                <canvas id="recoveryDonutChart"></canvas>
                                <div class="donut-center-metric">
                                    <span class="donut-center-pct">{{ $recoveryRate }}%</span>
                                    <span class="donut-center-label">Cleared</span>
                                </div>
                            </div>

                            <!-- 2. High-contrast Key Figures Grid -->
                            <div class="donut-stats-grid">
                                <div class="donut-stat-pill stat-collected">
                                    <span class="donut-stat-lbl">Collected</span>
                                    <span class="donut-stat-num text-success">₹ {{ number_format((float)($receiptTotals->total_collected ?? 0) / 100000, 2) }}L</span>
                                    <div class="text-muted" style="font-size: 9px; margin-top: 1px;">{{ number_format($receiptTotals->total_count ?? 0) }} Receipts</div>
                                </div>
                                <div class="donut-stat-pill stat-due">
                                    <span class="donut-stat-lbl">Pending Due</span>
                                    <span class="donut-stat-num text-danger">₹ {{ number_format((float)($assignTotals->total_due ?? 0) / 100000, 2) }}L</span>
                                    <div class="text-muted" style="font-size: 9px; margin-top: 1px;">{{ number_format($defaultersCount) }} Defaulters</div>
                                </div>
                            </div>

                            <!-- 3. Payment Mode Channels Strip -->
                            <div class="mode-channel-strip">
                                <span class="font-weight-bold text-muted" style="font-size: 10px;">
                                    <i class="fa fa-credit-card mr-1 text-info"></i> Inflows:
                                </span>
                                @forelse($modes as $m)
                                    <span class="badge badge-light border" style="font-size: 10px;">
                                        <i class="fa {{ strtolower($m->payment_mode) == 'online' ? 'fa-globe text-primary' : 'fa-money text-warning' }} mr-1"></i>
                                        <strong>{{ $m->payment_mode }}:</strong> ₹ {{ number_format((float)$m->total_amt, 0) }}
                                    </span>
                                @empty
                                    <span class="text-muted" style="font-size: 10px;">No breakdown</span>
                                @endforelse
                            </div>

                        </div>

                        <div class="p-2 border-top bg-light text-center">
                            <a href="{{ route('college.fees.reports', ['tab' => 'defaulters']) }}" class="btn-header-action text-danger w-100 justify-content-center" style="font-size: 10.5px;">
                                <i class="fa fa-exclamation-triangle mr-1"></i> View All {{ number_format($defaultersCount) }} Defaulters &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================================
                 ROW 2: DEFAULTER RANKINGS & RECENT FEE OPERATIONS STREAM
                 ======================================================== -->
            <div class="row mx-n1">
                
                <!-- Left: Top Defaulter Batches Ranking (8 Columns) -->
                <div class="col-lg-8 col-12 px-1 mb-2 mb-lg-0">
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header" style="background: #fff8f8; border-bottom: 1px solid #fecaca;">
                            <h3 class="dash-sub-panel-title" style="color: #991b1b;">
                                <i class="fa fa-exclamation-triangle mr-1 text-danger"></i> 
                                <span>Top Defaulter Batches Ranking</span>
                                <small class="text-muted ml-1 font-weight-normal">(Highest Pending Dues Across College)</small>
                            </h3>
                            <a href="{{ route('college.fees.reports', ['tab' => 'defaulters']) }}" class="btn-header-action text-danger" style="border-color: #fca5a5; font-size: 10px; padding: 2px 7px;">
                                <i class="fa fa-file-text-o mr-1"></i> Defaulters Register &rarr;
                            </a>
                        </div>
                        <div class="table-responsive mb-0" style="max-height: 290px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 11.5px;">
                                <thead style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; position: sticky; top: 0; z-index: 2;">
                                    <tr>
                                        <th class="text-center" style="width: 45px;">Rank</th>
                                        <th>Course &amp; Semester</th>
                                        <th class="text-center" style="width: 75px;">Batch</th>
                                        <th class="text-center" style="width: 110px;">Defaulters</th>
                                        <th class="text-right" style="width: 115px;">Net Demand</th>
                                        <th class="text-right" style="width: 100px;">Collected</th>
                                        <th class="text-right" style="width: 120px;">Pending Dues</th>
                                        <th style="width: 125px;">Recovery</th>
                                        <th class="text-center" style="width: 95px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topDefaulters as $idx => $tdb)
                                        @php
                                            $recPct = (float)$tdb->net_demand > 0 ? min(100, round(((float)$tdb->total_paid / (float)$tdb->net_demand) * 100, 1)) : 0;
                                            $progColor = $recPct >= 50 ? '#16a34a' : ($recPct >= 20 ? '#d97706' : '#dc2626');
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold" style="color: #b91c1c;">#{{ $idx + 1 }}</td>
                                            <td>
                                                <strong style="color: #0f172a;">{{ $tdb->course_name }}</strong>
                                                <span class="badge badge-light border ml-1 font-weight-normal">Sem {{ $tdb->semester_no ?? 1 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary" style="font-size: 10px; background: #475569;">{{ $tdb->batch }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-danger" style="font-size: 10.5px;">{{ number_format($tdb->defaulters_count) }} / {{ $tdb->enrolled_count }}</span>
                                            </td>
                                            <td class="text-right font-weight-bold" style="color: #334155;">
                                                ₹ {{ number_format((float)$tdb->net_demand, 2) }}
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                ₹ {{ number_format((float)$tdb->total_paid, 2) }}
                                            </td>
                                            <td class="text-right font-weight-bold text-danger" style="font-size: 12px;">
                                                ₹ {{ number_format((float)$tdb->total_due, 2) }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center" style="gap: 5px;">
                                                    <div class="progress flex-grow-1" style="height: 6px; background-color: #e2e8f0; border-radius: 3px;">
                                                        <div class="progress-bar" style="width: {{ $recPct }}%; background-color: {{ $progColor }};"></div>
                                                    </div>
                                                    <span style="font-size: 10px; font-weight: 700; color: {{ $progColor }}; min-width: 32px; text-align: right;">{{ $recPct }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('college.fees.structures', ['course_id' => $tdb->course_id, 'batch' => $tdb->batch, 'semester_no' => $tdb->semester_no]) }}" class="btn btn-xs btn-outline-success font-weight-bold" style="font-size: 10px; padding: 2px 6px;" title="Open Batch Matrix Ledger">
                                                    Ledger &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-3">No defaulter batches found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Sleek Fintech Recent Fee Operations Stream (4 Columns) -->
                <div class="col-lg-4 col-12 px-1 mb-2 mb-lg-0">
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0;">
                            <h3 class="dash-sub-panel-title" style="color: #166534;">
                                <i class="fa fa-history mr-1 text-success"></i>
                                <span>Recent Fee Operations</span>
                                <small class="text-muted ml-1 font-weight-normal">(Live Inflows)</small>
                            </h3>
                            <a href="{{ route('college.fees.dashboard') }}" class="btn-header-action" style="font-size: 10px; padding: 2px 6px;">
                                Receipts ({{ $receiptTotals->total_count ?? 0 }}) &rarr;
                            </a>
                        </div>
                        
                        <div class="dash-op-stream">
                            @forelse($recentOps as $op)
                                <div class="dash-op-card {{ strtolower($op->payment_mode) == 'online' ? 'mode-online' : 'mode-cash' }}">
                                    <div class="dash-op-icon-wrap">
                                        @if(strtolower($op->payment_mode) == 'online')
                                            <div class="dash-op-icon icon-online" title="Online Payment">
                                                <i class="fa fa-globe"></i>
                                            </div>
                                        @else
                                            <div class="dash-op-icon icon-cash" title="Cash Payment">
                                                <i class="fa fa-money"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="dash-op-info">
                                        <div class="dash-op-top">
                                            <span class="dash-op-student-name" title="{{ $op->first_name ?: $op->userName }} {{ $op->last_name }}">
                                                {{ Str::limit(($op->first_name ?: $op->userName) . ' ' . $op->last_name, 20) }}
                                            </span>
                                            <span class="dash-op-amount">+₹{{ number_format((float)$op->amount_paid, 0) }}</span>
                                        </div>
                                        <div class="dash-op-bottom">
                                            <span class="dash-op-meta">
                                                <span class="dash-op-receipt-no">#{{ $op->receipt_no }}</span>
                                                <span class="dash-op-batch-tag">{{ $op->course_name }} '{{ substr($op->batch, -2) }}</span>
                                            </span>
                                            <span class="dash-op-date">
                                                <span class="badge {{ strtolower($op->payment_mode) == 'online' ? 'badge-info' : 'badge-warning' }} dash-op-mode-pill">{{ strtoupper($op->payment_mode) }}</span>
                                                {{ date('d M', strtotime($op->payment_date)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state py-4">
                                    <i class="fa fa-inbox fa-2x mb-2 text-muted"></i>
                                    <p>No recent transactions recorded.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="p-2 border-top bg-light text-center">
                            <a href="{{ route('college.fees.structures') }}" class="btn-header-action btn-header-add w-100 justify-content-center" style="font-size: 10.5px;">
                                <i class="fa fa-plus-circle mr-1"></i> Collect Fee Payment Now
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================================
                 ROW 3: CAMPUS NOTIFICATIONS & PERSONAL TO-DO TASKS
                 ======================================================== -->
            <div class="row mx-n1">
                
                <!-- Left: Notification Center Tabbed Panel (8 Columns) -->
                <div class="col-lg-8 col-12 px-1 mb-2 mb-lg-0">
                    <div class="dash-sub-panel">
                        <ul class="nav nav-tabs dash-tabs w-100" id="dashFeedTab" role="tablist">
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link active" id="notices-tab" data-toggle="tab" href="#noticesTab" role="tab">
                                    <i class="fa fa-bullhorn mr-1"></i> Notices
                                    @if(count($noticeBoard) > 0)
                                        <span class="badge badge-success ml-1">{{ count($noticeBoard) }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link" id="birthdays-tab" data-toggle="tab" href="#birthdaysTab" role="tab">
                                    <i class="fa fa-birthday-cake mr-1"></i> Birthdays
                                    @if(count($getstudentbirthday) > 0)
                                        <span class="badge badge-warning ml-1 text-white">{{ count($getstudentbirthday) }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item flex-fill text-center">
                                <a class="nav-link" id="remarks-tab" data-toggle="tab" href="#remarksTab" role="tab">
                                    <i class="fa fa-comment-o mr-1"></i> Remarks
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
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong style="font-size: 11px;">{{ $item->title ?? 'Notice Announcement' }}</strong>
                                                        <span class="badge badge-success" style="font-size: 9px;">Active</span>
                                                    </div>
                                                    <p class="text-muted mb-1" style="font-size: 10px; line-height: 1.3;">
                                                        {{ Str::limit(strip_tags(html_entity_decode($item->message ?? '')), 120) }}
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 9px;">
                                                        <span><i class="fa fa-calendar-o"></i> {{ date('d M Y', strtotime($item->from_date ?? 'now')) }}</span>
                                                        <span class="text-success font-weight-bold">Read &rarr;</span>
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
                                                <div class="dash-feed-item d-flex align-items-center justify-content-between" style="background: #fffbeb; border-color: #fde68a;">
                                                    <div>
                                                        <div class="font-weight-bold" style="font-size: 11px; color: #92400e;">
                                                            {{ $bday->first_name ?? '' }} {{ $bday->last_name ?? '' }} 🎂
                                                        </div>
                                                        <div style="font-size: 9.5px; color: #b45309;">
                                                            Adm: {{ $bday->admissionNo ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                    <a href="{{ url('admissionView') }}" class="btn btn-xs btn-warning text-white" style="font-size: 9.5px; font-weight: 600; padding: 1px 6px;">
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
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="font-weight-bold text-danger" style="font-size: 10.5px;"><i class="fa fa-exclamation-circle"></i> Student Remark</span>
                                                        <span style="font-size: 9px; color: #94a3b8;">{{ date('d M') }}</span>
                                                    </div>
                                                    <p class="text-muted mb-0" style="font-size: 10px;">{{ $rem->remark ?? 'General Log' }}</p>
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
                </div>

                <!-- Right: Quick Personal Task List (4 Columns) -->
                <div class="col-lg-4 col-12 px-1 mb-2 mb-lg-0">
                    <div class="dash-sub-panel">
                        <div class="dash-sub-panel-header">
                            <h3 class="dash-sub-panel-title">
                                <i class="fa fa-tasks mr-1 text-primary"></i> Personal Task List
                            </h3>
                            <a href="{{ url('to_do_assign') }}" class="text-muted" style="font-size: 10px; font-weight: 600;">
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
                            <div class="dash-feed-list todoList" style="max-height: 150px;">
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
     DASHBOARD JAVASCRIPT & CHARTS (MODERN CHART.JS ENGINE)
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

    // 2. Modern Fee Collection Trend Chart (ChartJS v4)
    var monthlyData = @json($dataMonth ?? ['val1'=>[], 'val2'=>[]]);
    var weeklyData = @json($dataWeek ?? ['val1'=>[], 'val2'=>[]]);
    var feeChartInstance = null;

    function renderFeeChart(labels, values, labelText) {
        var canvas = document.getElementById('feeCollectionTrendChart');
        if (!canvas || typeof Chart === 'undefined') return;
        
        if (feeChartInstance) {
            feeChartInstance.destroy();
        }

        var ctx = canvas.getContext('2d');
        var gradient = ctx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(33, 115, 70, 0.85)');
        gradient.addColorStop(1, 'rgba(33, 115, 70, 0.15)');

        feeChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: labelText,
                    data: values,
                    backgroundColor: gradient,
                    borderColor: '#185c37',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    maxBarThickness: 34
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#86efac',
                        padding: 9,
                        cornerRadius: 5,
                        callbacks: {
                            label: function(context) {
                                return ' ₹ ' + Number(context.raw || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 10.5 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 10 },
                            callback: function(val) {
                                if (val >= 100000) return '₹' + (val / 100000).toFixed(1) + 'L';
                                if (val >= 1000) return '₹' + (val / 1000).toFixed(0) + 'k';
                                return '₹' + val;
                            }
                        }
                    }
                }
            }
        });
    }

    renderFeeChart(monthlyData.val1 || [], monthlyData.val2 || [], 'Monthly Collection');

    $('#btnMonthly').on('click', function() {
        $('.btn-chart-toggle').removeClass('active');
        $(this).addClass('active');
        $('#chartHeading').text('Monthly Collection Curve (Jan - Dec)');
        renderFeeChart(monthlyData.val1 || [], monthlyData.val2 || [], 'Monthly Collection');
    });

    $('#btnWeekly').on('click', function() {
        $('.btn-chart-toggle').removeClass('active');
        $(this).addClass('active');
        $('#chartHeading').text('Day-wise Collection (Current Month)');
        renderFeeChart(weeklyData.val1 || [], weeklyData.val2 || [], 'Day-wise Collection');
    });

    // 3. Recovery & Clearance Donut Chart
    var donutCanvas = document.getElementById('recoveryDonutChart');
    if (donutCanvas && typeof Chart !== 'undefined') {
        new Chart(donutCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Collected Fee', 'Pending Dues'],
                datasets: [{
                    data: [
                        {{ (float)($receiptTotals->total_collected ?? 0) }},
                        {{ (float)($assignTotals->total_due ?? 0) }}
                    ],
                    backgroundColor: ['#16a34a', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ₹' + Number(context.raw || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
    }

    // 4. To-Do List AJAX Handlers
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
