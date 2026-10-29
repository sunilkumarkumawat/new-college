@extends('layout.app')
@section('content')

<!-- Chart.js for Executive Financial Visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ========================================================
   COLLEGE FEES DASHBOARD
   Executive Theme Layout (Strictly Matching structures.blade.php & reports.blade.php)
   - Header Ribbon: Excel Green (#217346 / #185c37)
   - Title Font Size: 14.5px Standard
   - Controls & Inputs: 29px Height / 11.5px Font Size
   - Table Headers: Soft Blue (#d9eaf7) & Sticky Top 0
   - Table Footers: Executive Tint (#e7eff5) & Sticky Bottom 0
   - Table Backgrounds: Crisp White (#ffffff) & Theme Hover (#f0fdf4)
   - Action Bars: Neutral Light Gray (#f8fafc) with Subtle Border (#cbd5e1)
   - Buttons: .btn-header-light & .btn-matrix-tool with Theme Hover
   ======================================================== */

:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --line: #cbd5e1;
    --line-border: #9aa7b1;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --bg-page: #f4f6f9;
}

body {
    background-color: var(--bg-page);
    font-size: 12px;
}

.content-wrapper {
    background-color: var(--bg-page);
}

.reports-page-wrapper {
    padding: 10px 14px;
    background: var(--bg-page);
    min-height: calc(100vh - 60px);
}

/* Page Card Container */
.fees-reports-page-card {
    border: 1px solid var(--line-border) !important;
    border-radius: 2px !important;
    box-shadow: none !important;
    background: #ffffff !important;
    margin-bottom: 0 !important;
    overflow: hidden;
}

/* Excel Green Ribbon Header (Standard ERP 14.5px Titlebar) */
.fees-reports-page-card > .card-header {
    min-height: 44px;
    background: var(--green-excel) !important;
    border-radius: 0 !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    padding: 6px 12px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    color: #ffffff !important;
    flex-wrap: wrap;
    gap: 8px;
}

.fees-reports-page-card > .card-header::before,
.fees-reports-page-card > .card-header::after {
    display: none !important;
    content: none !important;
}

.fees-reports-page-card > .card-header .card-title {
    color: #ffffff !important;
    font-size: 14.5px !important;
    letter-spacing: 0.2px;
    font-weight: 700 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center;
    gap: 8px;
}

.fees-reports-page-card > .card-header .card-title i {
    color: #ffc107 !important;
    font-size: 15px;
}

/* Header Action Buttons */
.btn-header-light {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    padding: 4px 10px !important;
    transition: all 0.15s ease-in-out;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    line-height: 1.2;
}

.btn-header-light:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

/* KPI Summary Metric Strip */
.ledger-kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    padding: 10px 12px;
    background: #f8fafc !important;
    border-bottom: 1px solid #cbd5e1;
}

@media (max-width: 992px) {
    .ledger-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px) {
    .ledger-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.kpi-card {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px;
    padding: 8px 12px;
    transition: all 0.15s ease;
}

.kpi-card:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    border-color: #94a3b8 !important;
}

.kpi-card-label {
    font-size: 10px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 2px;
}

.kpi-card-value {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.kpi-card-sub {
    font-size: 10.5px;
    color: #64748b;
    margin-top: 2px;
}

/* Spreadsheet Box & Table Container (Matches structures.blade.php & reports.blade.php) */
.matrix-container-card {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 2px !important;
    overflow: hidden !important;
    margin-bottom: 14px !important;
}

.matrix-action-bar {
    padding: 6px 10px !important;
    background: #f8fafc !important;
    border-bottom: 1px solid #cbd5e1 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
}

.matrix-action-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.matrix-action-title i {
    color: var(--green-excel);
}

.matrix-filter-bar {
    padding: 5px 10px !important;
    background: #f8fafc !important;
    border-bottom: 1px solid #cbd5e1 !important;
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
}

/* Form Controls & Toolbar */
.toolbar-control {
    height: 29px !important;
    min-height: 29px !important;
    padding: 3px 8px !important;
    font-size: 11.5px !important;
    color: #172033 !important;
    background: #ffffff !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
}

.toolbar-control:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;
    outline: none;
}

.search-input-wrapper {
    position: relative;
    display: inline-block;
}

.search-input-wrapper i {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 11.5px;
    pointer-events: none;
}

.search-input-field {
    height: 29px !important;
    padding-left: 26px !important;
    padding-right: 8px !important;
    font-size: 11.5px !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
    outline: none;
    color: #0f172a !important;
    background: #ffffff !important;
    transition: all 0.15s ease;
}

.search-input-field:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;
}

/* Filter Pills */
.filter-pill-btn {
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
    padding: 3px 9px !important;
    border-radius: 2px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    line-height: 1.3 !important;
}

.filter-pill-btn:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}

.filter-pill-btn.active {
    background: var(--green-excel) !important;
    color: #ffffff !important;
    border-color: var(--green-excel-dark) !important;
    font-weight: 700 !important;
}

/* Matrix Table Styling (Strictly Matches structures.blade.php & reports.blade.php) */
.matrix-table {
    width: 100% !important;
    min-width: 100% !important;
    margin-bottom: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px !important;
    border-left: 1px solid #b8c9d9 !important;
    border-top: 1px solid #8fa7b8 !important;
    background: #ffffff !important;
}

.matrix-table th {
    background: #d9eaf7 !important;
    color: #152536 !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.2px !important;
    padding: 5px 8px !important;
    border-bottom: 2px solid #8fa7b8 !important;
    border-right: 1px solid #b8c9d9 !important;
    white-space: nowrap !important;
    position: sticky;
    top: 0;
    z-index: 10;
}

.matrix-table tfoot th,
.matrix-table tfoot td {
    background: #e7eff5 !important;
    color: #1e293b !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    border-top: 2px solid #8fa7b8 !important;
    border-bottom: 2px solid #8fa7b8 !important;
    border-right: 1px solid #b8c9d9 !important;
    white-space: nowrap !important;
    position: sticky;
    bottom: 0;
    z-index: 10;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.06);
}

.matrix-table td {
    background: #ffffff !important;
    color: #0f172a !important;
    padding: 5px 8px !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #cbd5e1 !important;
    border-right: 1px solid #e2e8f0 !important;
    white-space: nowrap !important;
    font-size: 11.5px !important;
}

.matrix-table tr:hover td {
    background-color: #f0fdf4 !important;
}

/* Scrollbars for Reports Table */
.reports-table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
    overflow-x: auto;
    overflow-y: auto;
    width: 100%;
}

.reports-table-responsive::-webkit-scrollbar {
    height: 9px;
    width: 9px;
}
.reports-table-responsive::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
    border: 1px solid #cbd5e1;
}
.reports-table-responsive::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
    border: 2px solid #f1f5f9;
}
.reports-table-responsive::-webkit-scrollbar-thumb:hover {
    background: #475569;
}

/* Progress Bars */
.mini-prog-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 120px;
}

.mini-prog-bar {
    flex-grow: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.mini-prog-fill {
    height: 100%;
    border-radius: 3px;
}

/* Standard ERP Badges (Clean contrast, no dark blue clashes) */
.badge-erp {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    padding: 2px 7px !important;
    border-radius: 2px !important;
    display: inline-block !important;
    line-height: 1.25 !important;
}
.badge-erp-batch { background: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #bfdbfe !important; }
.badge-erp-sem { background: #f8fafc !important; color: #475569 !important; border: 1px solid #cbd5e1 !important; font-size: 9.5px !important; padding: 1px 5px !important; }
.badge-erp-danger { background: #fef2f2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
.badge-erp-success { background: #f0fdf4 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
.badge-erp-info { background: #f0f9ff !important; color: #0369a1 !important; border: 1px solid #bae6fd !important; }

/* Badges for Payment Modes */
.badge-mode {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 2px;
    font-weight: 600;
    display: inline-block;
}
.mode-cash { background: #dcfce7 !important; color: #166534 !important; border: 1px solid #bbf7d0 !important; }
.mode-upi { background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd !important; }
.mode-bank { background: #fef3c7 !important; color: #92400e !important; border: 1px solid #fde68a !important; }
.mode-cheque { background: #f3e8ff !important; color: #6b21a8 !important; border: 1px solid #e9d5ff !important; }

/* Buttons & Tool Links */
.btn-matrix-tool {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    padding: 3px 8px !important;
    height: 26px !important;
    border-radius: 2px !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    line-height: 1 !important;
    text-decoration: none !important;
}

.btn-matrix-tool:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.btn-matrix-tool.btn-accent {
    background: var(--green-excel) !important;
    border-color: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

.btn-matrix-tool.btn-accent:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

.btn-matrix-tool.btn-tool-danger {
    color: #dc2626 !important;
    border-color: #fca5a5 !important;
}

.btn-matrix-tool.btn-tool-danger:hover {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
}

.btn-matrix-tool.btn-tool-danger:hover * {
    color: #ffffff !important;
}
</style>

<div class="content-wrapper">
    <div class="reports-page-wrapper">

        <!-- Main Card Container (Theme Standard) -->
        <div class="card fees-reports-page-card">

            <!-- Executive Ribbon Header (Standard 14.5px Titlebar) -->
            <div class="card-header">
                <div class="card-title">
                    <i class="fa fa-dashboard"></i>
                    <span>College Fees Management Dashboard</span>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                    <!-- Fee Matrix Button -->
                    <a href="{{ route('college.fees.structures') }}" class="btn-header-light" title="Fee Matrix & Student Fee Ledger">
                        <i class="fa fa-table mr-1 text-primary"></i> Fee Matrix
                    </a>
                    <!-- Course Fees Ledger Button -->
                    <a href="{{ route('college.fees.course_ledger') }}" class="btn-header-light" title="Consolidated Course Multi-Semester Fee Ledger">
                        <i class="fa fa-book mr-1 text-warning"></i> Course Ledger
                    </a>

                    <!-- Semester Progression Button -->
                    <a href="{{ route('college.fees.progression') }}" class="btn-header-light" title="Semester Fee Progression">
                        <i class="fa fa-forward mr-1 text-warning"></i> Progression
                    </a>

                    <!-- Reports & Defaulters Button -->
                    <a href="{{ route('college.fees.reports') }}" class="btn-header-light" title="Fee Registers & Defaulter Reports">
                        <i class="fa fa-file-text-o mr-1 text-success"></i> Reports &amp; Defaulters
                    </a>

                    <!-- Common Fee Head Modal -->
                    <button type="button" class="btn-header-light" data-toggle="modal" data-target="#commonFeeHeadModal" title="Manage Global Fee Heads">
                        <i class="fa fa-plus-circle mr-1 text-info"></i> Common Fee Head
                    </button>

                    <!-- Refresh Button -->
                    <a href="{{ route('college.fees.dashboard') }}" class="btn-header-light" title="Refresh Dashboard Data">
                        <i class="fa fa-refresh mr-1 text-secondary"></i> Refresh
                    </a>
                </div>
            </div>

            <!-- KPI Metric Strip (5 Columns Matching ERP Theme) -->
            <div class="ledger-kpi-grid">
                <!-- KPI 1: Active Enrolled Students -->
                <div class="kpi-card" style="border-top-color: #2563eb !important;">
                    <div class="kpi-card-label">Active Enrolled Students</div>
                    <div class="kpi-card-value text-primary">{{ number_format($totalStudents) }}</div>
                    <div class="kpi-card-sub">
                        <span>Boys: <strong>{{ number_format($boysCount) }}</strong></span> &bull; 
                        <span>Girls: <strong>{{ number_format($girlsCount) }}</strong></span>
                    </div>
                </div>

                <!-- KPI 2: Total Fee Demand -->
                <div class="kpi-card" style="border-top-color: #0284c7 !important;">
                    <div class="kpi-card-label">Total Fee Demand</div>
                    <div class="kpi-card-value" style="color: #0369a1;">₹{{ number_format($totalAssigned, 2) }}</div>
                    <div class="kpi-card-sub">
                        <span><strong>{{ count($courseBatchStats) }}</strong> Course-Batches Assigned</span>
                    </div>
                </div>

                <!-- KPI 3: Total Fee Collected -->
                <div class="kpi-card" style="border-top-color: #16a34a !important;">
                    <div class="kpi-card-label">Total Fee Collected</div>
                    <div class="kpi-card-value text-success">₹{{ number_format($totalCollected, 2) }}</div>
                    <div class="kpi-card-sub text-success font-weight-bold">
                        {{ $totalAssigned > 0 ? round(($totalCollected / $totalAssigned) * 100, 1) : 0 }}% Recovery of Demand
                    </div>
                </div>

                <!-- KPI 4: Outstanding Fee Dues -->
                <div class="kpi-card" style="border-top-color: #dc2626 !important;">
                    <div class="kpi-card-label">Outstanding Dues</div>
                    <div class="kpi-card-value text-danger">₹{{ number_format($totalDues, 2) }}</div>
                    <div class="kpi-card-sub text-danger font-weight-bold">
                        <i class="fa fa-exclamation-circle mr-1"></i>{{ number_format($defaultersCount) }} Students with Pending Dues
                    </div>
                </div>

                <!-- KPI 5: Today's Collection -->
                <div class="kpi-card" style="border-top-color: #7c3aed !important;">
                    <div class="kpi-card-label">Today's Collection</div>
                    <div class="kpi-card-value" style="color: #7c3aed;">₹{{ number_format($todayCollected, 2) }}</div>
                    <div class="kpi-card-sub" style="color: #6d28d9; font-weight: 600;">
                        {{ $todayReceiptsCount }} Receipts Generated Today
                    </div>
                </div>
            </div>

            <!-- Page Body Content -->
            <div class="card-body p-3">

                <!-- SECTION 1: TOP DEFAULTER COURSE BATCHES (Replaces individual student list) -->
                <div class="matrix-container-card mb-3">
                    <div class="matrix-action-bar" style="background: #fff8f8 !important; border-bottom: 1px solid #fecaca !important;">
                        <h4 class="matrix-action-title" style="color: #991b1b;">
                            <i class="fa fa-exclamation-triangle" style="color: #dc2626;"></i>
                            Top Defaulter Course Batches
                            <small class="text-muted ml-1" style="font-size: 11px; font-weight: 500;">(Ranked by Highest Outstanding Dues)</small>
                        </h4>
                        <div class="d-flex align-items-center" style="gap: 6px;">
                            <a href="{{ route('college.fees.reports', ['tab' => 'defaulters']) }}" class="btn-matrix-tool btn-tool-danger">
                                <i class="fa fa-users mr-1"></i> Open Full Defaulters Register &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive reports-table-responsive">
                        @if(!empty($topDefaulterBatches) && count($topDefaulterBatches) > 0)
                            <table class="table matrix-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th>Course Name</th>
                                        <th class="text-center" style="width: 90px;">Batch</th>
                                        <th class="text-center" style="width: 120px;">Defaulter Count</th>
                                        <th class="text-right" style="width: 140px;">Fee Demand (₹)</th>
                                        <th class="text-right" style="width: 140px;">Collected (₹)</th>
                                        <th class="text-right" style="width: 150px;">Pending Dues (₹)</th>
                                        <th style="width: 160px;">Recovery Rate</th>
                                        <th class="text-center" style="width: 160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $dIdx = 1; @endphp
                                    @foreach($topDefaulterBatches as $tdb)
                                        @php
                                            $barColor = $tdb['percent'] >= 70 ? '#16a34a' : ($tdb['percent'] >= 40 ? '#f59e0b' : '#dc2626');
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold" style="color: #dc2626;">{{ $dIdx++ }}</td>
                                            <td>
                                                <strong style="color: #0f172a; font-size: 12px;">{{ $tdb['course_name'] }}</strong>
                                                <span class="badge-erp badge-erp-sem ml-1">{{ $tdb['total_semester'] ?? 6 }} Sem</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-erp badge-erp-batch">
                                                    Batch {{ $tdb['batch'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-erp badge-erp-danger">
                                                    <i class="fa fa-user-times mr-1"></i>{{ number_format($tdb['defaulters']) }} Students
                                                </span>
                                                <small class="text-muted d-block" style="font-size: 9.5px;">of {{ $tdb['enrolled'] }} enrolled</small>
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                ₹{{ number_format($tdb['demand'], 2) }}
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                ₹{{ number_format($tdb['collected'], 2) }}
                                            </td>
                                            <td class="text-right font-weight-bold text-danger" style="font-size: 12.5px;">
                                                ₹{{ number_format($tdb['dues'], 2) }}
                                            </td>
                                            <td>
                                                <div class="mini-prog-wrapper">
                                                    <div class="mini-prog-bar">
                                                        <div class="mini-prog-fill" style="width: {{ $tdb['percent'] }}%; background: {{ $barColor }};"></div>
                                                    </div>
                                                    <span style="font-size: 11px; font-weight: 700; color: {{ $barColor }}; min-width: 36px; text-align: right;">
                                                        {{ $tdb['percent'] }}%
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex" style="gap: 4px;">
                                                    <a href="{{ route('college.fees.reports', ['tab' => 'defaulters', 'course_id' => $tdb['course_id'], 'batch' => $tdb['batch']]) }}" class="btn-matrix-tool btn-tool-danger" title="Filter Defaulters Report">
                                                        <i class="fa fa-list mr-1"></i> Defaulters
                                                    </a>
                                                    <a href="{{ route('college.fees.structures', ['course_id' => $tdb['course_id'], 'batch' => $tdb['batch']]) }}" class="btn-matrix-tool" style="color: #166534 !important;" title="View Fee Matrix & Collect">
                                                        <i class="fa fa-calculator mr-1"></i> Matrix
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fa fa-check-circle-o fa-2x text-success mb-1"></i>
                                <div class="font-weight-bold text-success" style="font-size: 13px;">All Course Batches are 100% Cleared!</div>
                                <small>No outstanding fee dues found across any course batch.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- SECTION 2: CHARTING & VISUAL ANALYTICS (2 Columns) -->
                <div class="row">
                    <!-- Left: Course-Batch Comparison Chart -->
                    <div class="col-lg-8 mb-3">
                        <div class="matrix-container-card h-100">
                            <div class="matrix-action-bar">
                                <h4 class="matrix-action-title">
                                    <i class="fa fa-bar-chart"></i> Fee Demand vs Collection vs Outstanding Dues
                                </h4>
                                <span class="text-muted" style="font-size: 11px;">Active Course Batches Overview</span>
                            </div>
                            <div class="p-3" style="position: relative; height: 260px; background: #ffffff;">
                                <canvas id="courseComparisonChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Payment Mode Distribution & Clearance Status -->
                    <div class="col-lg-4 mb-3">
                        <!-- Mode Breakdown Panel -->
                        <div class="matrix-container-card mb-3">
                            <div class="matrix-action-bar">
                                <h4 class="matrix-action-title">
                                    <i class="fa fa-pie-chart"></i> Payment Mode Breakdown
                                </h4>
                                <span class="text-success font-weight-bold" style="font-size: 11px;">₹{{ number_format($totalCollected, 2) }}</span>
                            </div>
                            <div class="p-2" style="position: relative; height: 135px; background: #ffffff;">
                                <canvas id="paymentModeChart"></canvas>
                            </div>
                        </div>

                        <!-- Students Fee Clearance Status -->
                        <div class="matrix-container-card">
                            <div class="matrix-action-bar">
                                <h4 class="matrix-action-title">
                                    <i class="fa fa-tasks"></i> Clearance Status
                                </h4>
                                <span class="text-muted" style="font-size: 11px;">Active Accounts</span>
                            </div>
                            <div class="p-3" style="background: #ffffff;">
                                @php
                                    $totalFeeStudents = max(1, $fullyPaidCount + $partialPaidCount + $unpaidCount);
                                    $pctFull = round(($fullyPaidCount / $totalFeeStudents) * 100, 1);
                                    $pctPart = round(($partialPaidCount / $totalFeeStudents) * 100, 1);
                                    $pctUnpaid = round(($unpaidCount / $totalFeeStudents) * 100, 1);
                                @endphp
                                
                                <div class="d-flex overflow-hidden mb-2" style="height: 10px; border-radius: 4px; background: #e2e8f0;">
                                    <div style="width: {{ $pctFull }}%; background: #16a34a;" title="Fully Paid: {{ $fullyPaidCount }}"></div>
                                    <div style="width: {{ $pctPart }}%; background: #f59e0b;" title="Partial Paid: {{ $partialPaidCount }}"></div>
                                    <div style="width: {{ $pctUnpaid }}%; background: #dc2626;" title="Pending Dues: {{ $unpaidCount }}"></div>
                                </div>

                                <div class="d-flex justify-content-between text-center pt-1">
                                    <div>
                                        <span class="badge-erp badge-erp-success">
                                            Fully Paid
                                        </span>
                                        <div class="font-weight-bold mt-1" style="font-size: 13px; color: #166534;">{{ number_format($fullyPaidCount) }}</div>
                                        <small class="text-muted">{{ $pctFull }}%</small>
                                    </div>
                                    <div style="border-right: 1px solid #e2e8f0;"></div>
                                    <div>
                                        <span class="badge-erp" style="background: #fef3c7 !important; color: #92400e !important; border: 1px solid #fde68a !important;">
                                            Partial Paid
                                        </span>
                                        <div class="font-weight-bold mt-1" style="font-size: 13px; color: #92400e;">{{ number_format($partialPaidCount) }}</div>
                                        <small class="text-muted">{{ $pctPart }}%</small>
                                    </div>
                                    <div style="border-right: 1px solid #e2e8f0;"></div>
                                    <div>
                                        <span class="badge-erp badge-erp-danger">
                                            Pending Dues
                                        </span>
                                        <div class="font-weight-bold mt-1" style="font-size: 13px; color: #991b1b;">{{ number_format($unpaidCount) }}</div>
                                        <small class="text-muted">{{ $pctUnpaid }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: COURSE & BATCH-WISE FEE SUMMARY (ONLY ASSIGNED BATCHES SHOWN) -->
                <div class="matrix-container-card mb-3">
                    <div class="matrix-action-bar">
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <h4 class="matrix-action-title">
                                <i class="fa fa-graduation-cap"></i> Course &amp; Batch-Wise Fee Summary
                            </h4>
                            <span class="badge-erp badge-erp-info">
                                <span id="visibleBatchCount">{{ count($courseBatchStats) }}</span> Assigned Batches
                            </span>
                        </div>

                        <!-- Instant Filter Controls -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <!-- Instant Search Box -->
                            <div class="search-input-wrapper">
                                <i class="fa fa-search"></i>
                                <input type="text" id="courseBatchSearch" class="search-input-field" placeholder="Search Course or Batch..." style="width: 210px;">
                            </div>

                            <!-- Batch Filter Dropdown -->
                            <select id="batchFilterSelect" class="toolbar-control" style="width: 130px;">
                                <option value="">All Batches</option>
                                @foreach($activeBatches as $b)
                                    <option value="{{ $b }}">Batch {{ $b }}</option>
                                @endforeach
                            </select>

                            <!-- Course Filter Dropdown -->
                            <select id="courseFilterSelect" class="toolbar-control" style="width: 170px;">
                                <option value="">All Courses</option>
                                @foreach($activeCourses as $c)
                                    <option value="{{ $c->name }}">{{ $c->name }}</option>
                                @endforeach
                            </select>

                            <!-- Direct Link to Matrix -->
                            <a href="{{ route('college.fees.structures') }}" class="btn-matrix-tool btn-accent">
                                <i class="fa fa-table mr-1"></i> Fee Matrix
                            </a>
                        </div>
                    </div>

                    <!-- Batch Filter Pills Strip (Clean ERP Neutral Background, NO dark blue) -->
                    @if(count($activeBatches) > 1)
                        <div class="matrix-filter-bar">
                            <span class="text-muted font-weight-bold mr-1" style="font-size: 11px;">Quick Batch Filter:</span>
                            <button type="button" class="filter-pill-btn batch-pill active" data-batch="">All Batches ({{ count($courseBatchStats) }})</button>
                            @foreach($activeBatches as $b)
                                @php
                                    $bCount = collect($courseBatchStats)->where('batch', $b)->count();
                                @endphp
                                <button type="button" class="filter-pill-btn batch-pill" data-batch="{{ $b }}">
                                    Batch {{ $b }} ({{ $bCount }})
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Matrix Table -->
                    <div class="table-responsive reports-table-responsive" style="max-height: 480px;">
                        <table class="table matrix-table mb-0" id="courseBatchMatrixTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th>Course Name</th>
                                    <th class="text-center" style="width: 90px;">Batch</th>
                                    <th class="text-center" style="width: 90px;">Enrolled</th>
                                    <th class="text-right" style="width: 140px;">Fee Demand (₹)</th>
                                    <th class="text-right" style="width: 140px;">Collected (₹)</th>
                                    <th class="text-right" style="width: 140px;">Pending Dues (₹)</th>
                                    <th class="text-center" style="width: 100px;">Defaulters</th>
                                    <th style="width: 160px;">Recovery Rate</th>
                                    <th class="text-center" style="width: 160px;">Quick Actions</th>
                                </tr>
                            </thead>
                            <tbody id="courseBatchTbody">
                                @php
                                    $totEnrolled = 0;
                                    $totDemand = 0;
                                    $totCollected = 0;
                                    $totDues = 0;
                                    $totDefaulters = 0;
                                    $rowIdx = 1;
                                @endphp
                                @forelse($courseBatchStats as $cbs)
                                    @php
                                        $totEnrolled += $cbs['enrolled'];
                                        $totDemand += $cbs['demand'];
                                        $totCollected += $cbs['collected'];
                                        $totDues += $cbs['dues'];
                                        $totDefaulters += $cbs['defaulters'];
                                        $barColor = $cbs['percent'] >= 70 ? '#16a34a' : ($cbs['percent'] >= 40 ? '#f59e0b' : '#dc2626');
                                    @endphp
                                    <tr class="course-batch-row" 
                                        data-course="{{ strtolower($cbs['course_name']) }}" 
                                        data-batch="{{ $cbs['batch'] }}"
                                        data-enrolled="{{ $cbs['enrolled'] }}"
                                        data-demand="{{ $cbs['demand'] }}"
                                        data-collected="{{ $cbs['collected'] }}"
                                        data-dues="{{ $cbs['dues'] }}"
                                        data-defaulters="{{ $cbs['defaulters'] }}">
                                        <td class="text-center text-muted row-idx">{{ $rowIdx++ }}</td>
                                        <td>
                                            <strong style="color: #0f172a; font-size: 12px;">{{ $cbs['course_name'] }}</strong>
                                            <span class="badge-erp badge-erp-sem ml-1">{{ $cbs['total_semester'] }} Sem</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-erp badge-erp-batch">
                                                Batch {{ $cbs['batch'] }}
                                            </span>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ number_format($cbs['enrolled']) }}</td>
                                        <td class="text-right font-weight-bold">₹{{ number_format($cbs['demand'], 2) }}</td>
                                        <td class="text-right font-weight-bold text-success">₹{{ number_format($cbs['collected'], 2) }}</td>
                                        <td class="text-right font-weight-bold {{ $cbs['dues'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            ₹{{ number_format($cbs['dues'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if($cbs['defaulters'] > 0)
                                                <span class="badge-erp badge-erp-danger">
                                                    {{ $cbs['defaulters'] }}
                                                </span>
                                            @else
                                                <span class="badge-erp badge-erp-success">
                                                    <i class="fa fa-check"></i> Cleared
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="mini-prog-wrapper">
                                                <div class="mini-prog-bar">
                                                    <div class="mini-prog-fill" style="width: {{ $cbs['percent'] }}%; background: {{ $barColor }};"></div>
                                                </div>
                                                <span style="font-size: 11px; font-weight: 700; color: {{ $barColor }}; min-width: 36px; text-align: right;">
                                                    {{ $cbs['percent'] }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex" style="gap: 4px;">
                                                <a href="{{ route('college.fees.structures', ['course_id' => $cbs['course_id'], 'batch' => $cbs['batch']]) }}" class="btn-matrix-tool" title="View Fee Matrix & Collect">
                                                    <i class="fa fa-table mr-1 text-primary"></i> Matrix
                                                </a>
                                                @if($cbs['dues'] > 0)
                                                    <a href="{{ route('college.fees.reports', ['tab' => 'defaulters', 'course_id' => $cbs['course_id'], 'batch' => $cbs['batch']]) }}" class="btn-matrix-tool btn-tool-danger" title="View Defaulters">
                                                        <i class="fa fa-exclamation-circle"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No course-batches have assigned fees yet. Please assign fees via Fee Matrix.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @php
                                    $overallPct = $totDemand > 0 ? min(100, round(($totCollected / $totDemand) * 100, 1)) : 0;
                                @endphp
                                <tr>
                                    <th colspan="3" class="text-center">TOTAL (ASSIGNED BATCHES)</th>
                                    <th class="text-center" id="footEnrolled">{{ number_format($totEnrolled) }}</th>
                                    <th class="text-right" id="footDemand">₹{{ number_format($totDemand, 2) }}</th>
                                    <th class="text-right text-success" id="footCollected">₹{{ number_format($totCollected, 2) }}</th>
                                    <th class="text-right text-danger" id="footDues">₹{{ number_format($totDues, 2) }}</th>
                                    <th class="text-center" id="footDefaulters">{{ number_format($totDefaulters) }}</th>
                                    <th>
                                        <span class="badge-erp badge-erp-success" id="footRecovery">
                                            {{ $overallPct }}% Overall
                                        </span>
                                    </th>
                                    <th class="text-center">&mdash;</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- SECTION 4: RECENT COLLECTIONS REGISTER -->
                <div class="matrix-container-card mb-0">
                    <div class="matrix-action-bar">
                        <h4 class="matrix-action-title">
                            <i class="fa fa-clock-o text-success"></i> Recent Collections Feed
                            <small class="text-muted ml-1" style="font-size: 11px; font-weight: 500;">(Latest 10 Receipts)</small>
                        </h4>
                        <a href="{{ route('college.fees.reports') }}" class="btn-matrix-tool" title="View Full Fee Register">
                            <i class="fa fa-list mr-1"></i> Full Fee Register &rarr;
                        </a>
                    </div>
                    <div class="table-responsive reports-table-responsive">
                        <table class="table matrix-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Receipt No</th>
                                    <th style="width: 100px;">Date</th>
                                    <th>Student Name &amp; Adm No</th>
                                    <th>Course &amp; Semester</th>
                                    <th style="width: 120px;">Payment Mode</th>
                                    <th class="text-right" style="width: 130px;">Amount Paid (₹)</th>
                                    <th>Cashier</th>
                                    <th class="text-center" style="width: 70px;">Print</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReceipts as $rec)
                                    @php
                                        $mode = strtolower($rec->payment_mode ?? 'cash');
                                        $badgeClass = 'mode-cash';
                                        if(strpos($mode, 'upi') !== false) $badgeClass = 'mode-upi';
                                        elseif(strpos($mode, 'bank') !== false) $badgeClass = 'mode-bank';
                                        elseif(strpos($mode, 'cheque') !== false) $badgeClass = 'mode-cheque';
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('college.fees.receipt', $rec->id) }}" target="_blank" class="font-weight-bold text-primary">
                                                {{ $rec->receipt_no }}
                                            </a>
                                        </td>
                                        <td class="text-muted" style="font-size: 11px;">
                                            {{ date('d-M-Y', strtotime($rec->payment_date)) }}
                                        </td>
                                        <td>
                                            <strong style="color: #0f172a;">{{ $rec->student->first_name ?? 'N/A' }} {{ $rec->student->last_name ?? '' }}</strong>
                                            <small class="text-muted ml-1">(Adm: {{ $rec->student->admissionNo ?? 'N/A' }})</small>
                                        </td>
                                        <td>
                                            <span>{{ $rec->course->name ?? 'Course' }}</span>
                                            <span class="badge-erp badge-erp-sem ml-1">Sem {{ $rec->semester_no ?? 1 }}</span>
                                        </td>
                                        <td>
                                            <span class="badge-mode {{ $badgeClass }}">{{ $rec->payment_mode ?? 'Cash' }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold text-success" style="font-size: 12px;">
                                            ₹{{ number_format($rec->amount_paid, 2) }}
                                        </td>
                                        <td class="text-muted" style="font-size: 11px;">
                                            <i class="fa fa-user-circle-o mr-1"></i>{{ $rec->cashier->name ?? 'System' }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('college.fees.receipt', $rec->id) }}" target="_blank" class="btn-matrix-tool" style="padding: 2px 6px;" title="Print Receipt">
                                                <i class="fa fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No recent receipts recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- /card-body -->

        </div> <!-- /card -->

    </div>
</div>

@include('college_fees.partials.common_head_modal')

<!-- ========================================================
     JAVASCRIPT: CLIENT-SIDE FILTERING & CHART.JS VISUALS
     ======================================================== -->
<script>
$(document).ready(function() {

    // 1. Chart.js: Financial Comparison (Assigned Course-Batches)
    @php
        $chartDataList = collect($courseBatchStats)
            ->filter(fn($i) => ($i['demand'] > 0 || $i['collected'] > 0))
            ->sortByDesc('demand')
            ->take(8)
            ->values();

        $chartLabels = $chartDataList->map(fn($i) => $i['course_name'] . ' (' . $i['batch'] . ')')->all();
        $chartDemand = $chartDataList->map(fn($i) => (float)$i['demand'])->all();
        $chartCollected = $chartDataList->map(fn($i) => (float)$i['collected'])->all();
        $chartDues = $chartDataList->map(fn($i) => (float)$i['dues'])->all();
    @endphp

    var chartCtx = document.getElementById('courseComparisonChart');
    if (chartCtx && typeof Chart !== 'undefined') {
        new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Fee Demand (₹)',
                        data: @json($chartDemand),
                        backgroundColor: '#0284c7',
                        borderRadius: 3,
                        maxBarThickness: 28
                    },
                    {
                        label: 'Collected (₹)',
                        data: @json($chartCollected),
                        backgroundColor: '#16a34a',
                        borderRadius: 3,
                        maxBarThickness: 28
                    },
                    {
                        label: 'Outstanding Dues (₹)',
                        data: @json($chartDues),
                        backgroundColor: '#ef4444',
                        borderRadius: 3,
                        maxBarThickness: 28
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                return '₹' + (value >= 100000 ? (value / 100000).toFixed(1) + 'L' : (value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value));
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Chart.js: Payment Mode Doughnut Chart
    @php
        $modeLabels = [];
        $modeAmounts = [];
        $modeColors = [
            'Cash' => '#16a34a',
            'UPI' => '#0284c7',
            'Bank Transfer' => '#d97706',
            'Cheque' => '#7c3aed'
        ];
        $chartColors = [];

        foreach($modeBreakdown as $mb) {
            $mName = $mb->payment_mode ?: 'Cash';
            $modeLabels[] = $mName . ' (' . $mb->total_count . ')';
            $modeAmounts[] = (float)$mb->total_amt;
            $chartColors[] = $modeColors[$mName] ?? '#64748b';
        }
        if (empty($modeLabels)) {
            $modeLabels = ['No Receipts'];
            $modeAmounts = [1];
            $chartColors = ['#e2e8f0'];
        }
    @endphp

    var modeCtx = document.getElementById('paymentModeChart');
    if (modeCtx && typeof Chart !== 'undefined') {
        new Chart(modeCtx, {
            type: 'doughnut',
            data: {
                labels: @json($modeLabels),
                datasets: [{
                    data: @json($modeAmounts),
                    backgroundColor: @json($chartColors),
                    borderWidth: 1.5,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 10,
                            font: { size: 10.5 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₹' + Number(context.raw).toLocaleString('en-IN', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // 3. Client-Side Instant Filtering for Course-Batch Summary Table
    function filterCourseBatchTable() {
        var query = $('#courseBatchSearch').val().toLowerCase().trim();
        var selectedBatch = $('#batchFilterSelect').val().trim();
        var selectedCourse = $('#courseFilterSelect').val().toLowerCase().trim();

        var visibleCount = 0;
        var sumEnrolled = 0;
        var sumDemand = 0;
        var sumCollected = 0;
        var sumDues = 0;
        var sumDefaulters = 0;

        $('#courseBatchTbody tr.course-batch-row').each(function() {
            var rowCourse = ($(this).data('course') || '').toString().toLowerCase();
            var rowBatch = ($(this).data('batch') || '').toString();
            var rowText = $(this).text().toLowerCase();

            var matchesSearch = !query || rowText.indexOf(query) !== -1;
            var matchesBatch = !selectedBatch || rowBatch === selectedBatch;
            var matchesCourse = !selectedCourse || rowCourse.indexOf(selectedCourse) !== -1;

            if (matchesSearch && matchesBatch && matchesCourse) {
                $(this).show();
                visibleCount++;
                $(this).find('.row-idx').text(visibleCount);

                sumEnrolled += parseFloat($(this).data('enrolled')) || 0;
                sumDemand += parseFloat($(this).data('demand')) || 0;
                sumCollected += parseFloat($(this).data('collected')) || 0;
                sumDues += parseFloat($(this).data('dues')) || 0;
                sumDefaulters += parseInt($(this).data('defaulters')) || 0;
            } else {
                $(this).hide();
            }
        });

        // Update Footers
        $('#visibleBatchCount').text(visibleCount);
        $('#footEnrolled').text(sumEnrolled.toLocaleString('en-IN'));
        $('#footDemand').text('₹' + sumDemand.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footCollected').text('₹' + sumCollected.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footDues').text('₹' + sumDues.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#footDefaulters').text(sumDefaulters.toLocaleString('en-IN'));

        var overallPct = sumDemand > 0 ? Math.min(100, Math.round((sumCollected / sumDemand) * 1000) / 10) : 0;
        $('#footRecovery').text(overallPct + '% Overall');
    }

    // Trigger filters on Search, Dropdown, or Pills
    $('#courseBatchSearch').on('keyup search', filterCourseBatchTable);
    
    $('#batchFilterSelect').on('change', function() {
        var val = $(this).val();
        $('.batch-pill').removeClass('active');
        $('.batch-pill[data-batch="' + val + '"]').addClass('active');
        filterCourseBatchTable();
    });

    $('#courseFilterSelect').on('change', filterCourseBatchTable);

    $('.batch-pill').on('click', function() {
        $('.batch-pill').removeClass('active');
        $(this).addClass('active');
        var b = $(this).data('batch');
        $('#batchFilterSelect').val(b);
        filterCourseBatchTable();
    });

});
</script>

@endsection
