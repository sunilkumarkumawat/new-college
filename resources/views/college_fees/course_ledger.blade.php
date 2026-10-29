@extends('layout.app')

@section('content')

<!-- SheetJS for Native Excel (.xlsx) Export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<style>
/* ========================================================
   CONSOLIDATED COURSE FEES LEDGER (MULTI-SEMESTER MATRIX)
   Executive ERP Theme (Matching structures.blade.php & reports.blade.php)
   - Header Ribbon: Excel Forest Green (#217346 / #185c37)
   - Table Headers: Soft Executive Blue (#d9eaf7) with Slate Borders (#8fa7b8)
   - Table Footer: Solid Opaque Executive Tint (#e7eff5) with zero bleed-through
   - Sticky First 3 Columns (Sr., Adm, Student Name) with solid background & crisp divider
   ======================================================== */

:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --header-blue: #d9eaf7;
    --header-blue-border: #8fa7b8;
    --grid-border: #b8c9d9;
    --footer-bg: #e7eff5;
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

.course-ledger-page-wrapper {
    padding: 10px 14px;
    background: var(--bg-page);
    min-height: calc(100vh - 60px);
}

/* Page Card Container */
.course-ledger-page-card {
    border: 1px solid #9aa7b1 !important;
    border-radius: 2px !important;
    box-shadow: none !important;
    background: #ffffff !important;
    margin-bottom: 0 !important;
    overflow: hidden;
}

/* Excel Green Ribbon Header */
.course-ledger-page-card > .card-header {
    min-height: 44px;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    padding: 8px 14px !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.course-ledger-page-card > .card-header::before,
.course-ledger-page-card > .card-header::after {
    display: none !important;
}

.course-ledger-page-card > .card-header .card-title {
    font-size: 14.5px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 0 !important;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.2px;
}

.course-ledger-page-card > .card-header .card-title i.fa-book {
    color: #ffc107 !important;
    font-size: 15px;
}

.btn-header-light {
    height: 28px;
    padding: 0 11px;
    font-size: 11.5px;
    font-weight: 600;
    color: #1e293b !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    text-decoration: none !important;
    transition: all 0.15s ease;
}

.btn-header-light:hover {
    background: #f8fafc !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

/* Filter Toolbar */
.ledger-filter-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
    padding: 10px 14px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.filter-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.filter-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-control {
    height: 29px;
    padding: 2px 8px;
    font-size: 11.5px;
    border: 1px solid #9aa7b1;
    border-radius: 2px;
    background-color: #ffffff;
    color: #0f172a;
    outline: none;
    transition: border-color 0.15s ease;
}

.filter-control:focus {
    border-color: var(--green-excel);
    box-shadow: 0 0 0 1px var(--green-excel);
}

/* KPI Summary Ribbon */
.ledger-kpi-ribbon {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    padding: 10px 14px;
    background: #ffffff;
    border-bottom: 1px solid #cbd5e1;
}

.kpi-tile {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 2px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-left: 3px solid #cbd5e1;
}

.kpi-tile.kpi-students { border-left-color: #3b82f6; }
.kpi-tile.kpi-net { border-left-color: #6366f1; }
.kpi-tile.kpi-paid { border-left-color: #10b981; }
.kpi-tile.kpi-due { border-left-color: #ef4444; }
.kpi-tile.kpi-rate { border-left-color: #f59e0b; }

.kpi-tile-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.kpi-tile-val {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.2;
}

/* Table Responsive Container */
.matrix-table-container {
    overflow-x: auto;
    overflow-y: auto;
    min-height: 380px;
    max-height: calc(100vh - 270px);
    border-bottom: 1px solid #9aa7b1;
    position: relative;
    background: #ffffff;
}

/* Custom Scrollbars */
.matrix-table-container::-webkit-scrollbar {
    width: 8px;
    height: 9px;
}
.matrix-table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
}
.matrix-table-container::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 3px;
}
.matrix-table-container::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Table Base Grid */
.table-course-ledger {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 11.5px;
    table-layout: auto;
    border-left: 1px solid var(--grid-border);
    border-top: 1px solid var(--header-blue-border);
}

.table-course-ledger th,
.table-course-ledger td {
    padding: 4px 6px;
    border-right: 1px solid var(--grid-border);
    border-bottom: 1px solid #cbd5e1;
    white-space: nowrap;
    vertical-align: middle;
}

/* Table Headers - Soft Blue ERP Theme */
.table-course-ledger thead th {
    background: var(--header-blue) !important;
    color: #152536 !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    padding: 5px 6px !important;
    border-bottom: 1px solid var(--header-blue-border) !important;
    border-right: 1px solid var(--grid-border) !important;
    white-space: nowrap !important;
    position: sticky;
    box-sizing: border-box;
}

/* Tier 1 Header Row */
.table-course-ledger thead tr.tier-1 th {
    top: 0;
    z-index: 20;
    height: 28px !important;
    line-height: 16px !important;
}

/* Tier 1 Rowspan=2 Sticky Student Headers */
.table-course-ledger thead tr.tier-1 th[rowspan="2"] {
    top: 0 !important;
    height: 54px !important;
    vertical-align: middle !important;
}

/* Tier 2 Subheader Row */
.table-course-ledger thead tr.tier-2 th {
    top: 28px !important;
    z-index: 19;
    height: 26px !important;
    line-height: 16px !important;
    font-size: 10.5px !important;
    border-bottom: 2px solid var(--header-blue-border) !important;
}

/* Sticky First 3 Columns (Sr., Adm, Student Name) */
.table-course-ledger th.col-sticky-sr,
.table-course-ledger td.col-sticky-sr {
    position: sticky !important;
    left: 0px !important;
    width: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    text-align: center !important;
    padding: 4px 2px !important;
}

.table-course-ledger th.col-sticky-adm,
.table-course-ledger td.col-sticky-adm {
    position: sticky !important;
    left: 38px !important;
    width: 95px !important;
    min-width: 95px !important;
    max-width: 95px !important;
    font-weight: 600 !important;
    padding: 4px 6px !important;
}

.table-course-ledger th.col-sticky-name,
.table-course-ledger td.col-sticky-name {
    position: sticky !important;
    left: 133px !important;
    width: 180px !important;
    min-width: 180px !important;
    max-width: 180px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    padding: 4px 8px !important;
    border-right: 2px solid var(--header-blue-border) !important;
    box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.12) !important;
}

/* Sticky thead Columns */
.table-course-ledger thead th.col-sticky-sr,
.table-course-ledger thead th.col-sticky-adm,
.table-course-ledger thead th.col-sticky-name {
    background: var(--header-blue) !important;
    color: #152536 !important;
    z-index: 35 !important;
    top: 0 !important;
}

/* Sticky tbody Columns */
.table-course-ledger tbody td.col-sticky-sr,
.table-course-ledger tbody td.col-sticky-adm,
.table-course-ledger tbody td.col-sticky-name {
    background-color: #ffffff !important;
    z-index: 15 !important;
}

/* Sticky tfoot Columns (Solid Opaque - Zero bleed-through!) */
.table-course-ledger tfoot td.col-sticky-sr,
.table-course-ledger tfoot td.col-sticky-adm,
.table-course-ledger tfoot td.col-sticky-name {
    background-color: var(--footer-bg) !important;
    color: #152536 !important;
    z-index: 35 !important;
    bottom: 0 !important;
}

/* Table Body Row Hover */
.table-course-ledger tbody tr:hover td {
    background-color: var(--green-excel-light) !important;
}

.table-course-ledger tbody tr:hover td.col-sticky-sr,
.table-course-ledger tbody tr:hover td.col-sticky-adm,
.table-course-ledger tbody tr:hover td.col-sticky-name {
    background-color: #e6fced !important;
}

/* Table Footer - Solid Opaque Sticky Executive Tint */
.table-course-ledger tfoot th,
.table-course-ledger tfoot td {
    position: sticky !important;
    bottom: 0 !important;
    z-index: 20 !important;
    background: var(--footer-bg) !important;
    color: #1e293b !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    border-top: 2px solid var(--header-blue-border) !important;
    border-bottom: 2px solid var(--header-blue-border) !important;
    border-right: 1px solid var(--grid-border) !important;
    padding: 5px 6px !important;
    white-space: nowrap !important;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.06);
}

/* Soft Executive Badges */
.badge-status {
    padding: 3px 6px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 2px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    display: inline-block;
}
.badge-status.badge-paid {
    background: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #86efac !important;
}
.badge-status.badge-partial {
    background: #fef3c7 !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
}
.badge-status.badge-unpaid {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1px solid #fca5a5 !important;
}

/* Bottom Status Footer */
.ledger-status-bar {
    background: #f8fafc !important;
    border-top: 1px solid #cbd5e1 !important;
    padding: 8px 14px !important;
    font-size: 11.5px !important;
    color: #475569 !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

/* Loading Overlay */
.ledger-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.85);
    z-index: 99;
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

/* ========================================================
   FIXED BOTTOM HORIZONTAL SCROLL TOOLBOX (EXECUTIVE ERP)
   ======================================================== */
.ledger-fixed-scroll-toolbox {
    position: fixed;
    bottom: 82px;
    z-index: 1045;
    background: #217346;
    background: linear-gradient(135deg, #185c37 0%, #217346 100%);
    color: #ffffff;
    border-radius: 40px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.35), 0 3px 8px rgba(24, 92, 55, 0.4);
    padding: 5px 14px;
    display: none;
    align-items: center;
    border: 1.5px solid #14532d;
    backdrop-filter: blur(10px);
    user-select: none;
    transition: opacity 0.2s ease, transform 0.15s ease, bottom 0.15s ease;
}

.ledger-fixed-scroll-toolbox .toolbox-label {
    font-size: 11px;
    font-weight: 800;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.ledger-fixed-scroll-toolbox .btn-toolbox-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    font-weight: 700;
    font-size: 11px;
    padding: 4px 11px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.15s ease;
    outline: none;
    line-height: 1.3;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}

.ledger-fixed-scroll-toolbox .btn-toolbox-action:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

.ledger-fixed-scroll-toolbox .btn-toolbox-action:active {
    transform: translateY(0);
    background: #e2e8f0;
}

.ledger-fixed-scroll-toolbox .btn-toolbox-action-accent {
    background: #f59e0b;
    border-color: #d97706;
    color: #0f172a !important;
    font-weight: 800 !important;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.45);
}

.ledger-fixed-scroll-toolbox .btn-toolbox-action-accent:hover {
    background: #fbbf24;
    border-color: #f59e0b;
    color: #000000 !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.6);
    transform: translateY(-1px);
}

.ledger-fixed-scroll-toolbox .toolbox-slider-track {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(15, 47, 28, 0.65);
    border-radius: 20px;
    padding: 3px 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.ledger-fixed-scroll-toolbox .slider-end-label {
    font-size: 9.5px;
    font-weight: 700;
    color: #dcfce7;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

.ledger-fixed-scroll-toolbox .toolbox-range-slider {
    width: 130px;
    height: 6px;
    cursor: pointer;
    accent-color: #f59e0b;
    margin: 0;
    vertical-align: middle;
}

</style>

<div class="content-wrapper">
    <div class="course-ledger-page-wrapper">
        <div class="card course-ledger-page-card">

            <!-- Excel Green Ribbon Header -->
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-book"></i> Course Fees Ledger
                    <span class="badge badge-light text-dark ml-2" style="font-size: 10.5px; font-weight: 700; border: 1px solid #cbd5e1;">Multi-Semester Master</span>
                </h3>
                <div class="card-tools d-flex align-items-center" style="gap: 6px;">
                    <a href="{{ url('college/fees/structures') }}" class="btn-header-light" title="Fee Assignment Matrix (Single Semester)">
                        <i class="fa fa-table text-success mr-1"></i> Fee Matrix
                    </a>
                    <a href="{{ url('college/fees/reports') }}" class="btn-header-light" title="Fee Reports & Register">
                        <i class="fa fa-file-text text-primary mr-1"></i> Reports
                    </a>
                    <a href="{{ url('college/fees/dashboard') }}" class="btn-header-light" title="Fees Executive Dashboard">
                        <i class="fa fa-dashboard text-info mr-1"></i> Dashboard
                    </a>
                    <button type="button" class="btn-header-light" onclick="loadCourseLedgerData()" title="Reload Data">
                        <i class="fa fa-refresh text-dark"></i>
                    </button>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="ledger-filter-toolbar">
                <div class="filter-group">
                    <!-- Course Selector -->
                    <div class="filter-item">
                        <label class="filter-label" for="selCourse">Course:</label>
                        <select id="selCourse" class="filter-control" style="min-width: 175px;" onchange="onCourseOrBatchChange()">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" data-sems="{{ $c->total_semester ?: 4 }}" {{ (!empty($courseId) && $c->id == $courseId) ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Batch Selector -->
                    <div class="filter-item">
                        <label class="filter-label" for="selBatch">Batch:</label>
                        <select id="selBatch" class="filter-control" style="min-width: 120px;" onchange="onCourseOrBatchChange()">
                            <option value="">-- Batch --</option>
                            @foreach($batches as $b)
                                <option value="{{ $b }}" {{ (!empty($batch) && $b == $batch) ? 'selected' : '' }}>Batch {{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="filter-item">
                        <label class="filter-label" for="selStatus">Status:</label>
                        <select id="selStatus" class="filter-control" style="min-width: 115px;" onchange="applyFilters()">
                            <option value="all">All Status</option>
                            <option value="paid">Paid (₹0 Due)</option>
                            <option value="partial">Partial</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="overdue">Has Outstanding (> ₹0)</option>
                        </select>
                    </div>

                    <!-- Gender Filter -->
                    <div class="filter-item">
                        <label class="filter-label" for="selGender">Gender:</label>
                        <select id="selGender" class="filter-control" style="min-width: 90px;" onchange="applyFilters()">
                            <option value="all">All</option>
                            <option value="boys">Boys</option>
                            <option value="girls">Girls</option>
                        </select>
                    </div>

                    <!-- Live Search -->
                    <div class="filter-item">
                        <input type="text" id="txtSearch" class="filter-control" placeholder="Search student, adm no..." style="min-width: 185px;" onkeyup="applyFiltersDebounced()">
                    </div>
                </div>

                <!-- Action Buttons: Native Excel & CSV Export -->
                <div class="filter-group">
                    <button type="button" class="btn btn-sm btn-success py-1 px-2 font-weight-bold" style="font-size: 11.5px; border-radius: 2px;" onclick="exportToExcel()" title="Export complete multi-semester ledger to Microsoft Excel (.xlsx)">
                        <i class="fa fa-file-excel-o mr-1"></i> Export Excel (.xlsx)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 font-weight-bold" style="font-size: 11.5px; border-radius: 2px; background: #ffffff; border-color: #cbd5e1;" onclick="exportToCSV()" title="Export clean tabular CSV file">
                        <i class="fa fa-file-text-o mr-1"></i> Export CSV
                    </button>
                </div>
            </div>

            <!-- KPI Summary Ribbon -->
            <div class="ledger-kpi-ribbon">
                <div class="kpi-tile kpi-students">
                    <div class="kpi-tile-label">Total Enrolled</div>
                    <div class="kpi-tile-val" id="kpiTotalStudents">0</div>
                </div>
                <div class="kpi-tile kpi-net">
                    <div class="kpi-tile-label">Total Course Fees</div>
                    <div class="kpi-tile-val" id="kpiTotalDemand">₹0.00</div>
                </div>
                <div class="kpi-tile kpi-paid">
                    <div class="kpi-tile-label">Total Fees Collected</div>
                    <div class="kpi-tile-val text-success" id="kpiTotalCollected">₹0.00</div>
                </div>
                <div class="kpi-tile kpi-due">
                    <div class="kpi-tile-label">Total Outstanding Balance</div>
                    <div class="kpi-tile-val text-danger" id="kpiTotalBalance">₹0.00</div>
                </div>
                <div class="kpi-tile kpi-rate">
                    <div class="kpi-tile-label">Collection Rate</div>
                    <div class="kpi-tile-val text-warning" id="kpiCollectionRate">0.0%</div>
                </div>
            </div>

            <!-- Table Responsive Area -->
            <div class="matrix-table-container" id="ledgerTableWrapper">
                <!-- Loading Overlay -->
                <div class="ledger-loading-overlay" id="ledgerLoadingOverlay">
                    <i class="fa fa-spinner fa-spin fa-2x text-success mb-2"></i>
                    <span class="font-weight-bold text-dark" style="font-size: 12px;">Loading Consolidated Ledger...</span>
                </div>

                <table class="table table-course-ledger" id="tblCourseLedger">
                    <thead id="tblHead">
                        <!-- Dynamic 2-tier headers rendered via JavaScript -->
                    </thead>
                    <tbody id="tblBody">
                        <tr>
                            <td colspan="15" class="text-center py-5 text-muted">
                                <i class="fa fa-info-circle fa-2x mb-2 text-info d-block"></i>
                                Select Course and Batch above to load the multi-semester ledger.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="tblFoot">
                        <!-- Dynamic Grand Totals rendered via JavaScript -->
                    </tfoot>
                </table>
            </div>

            <!-- Fixed Bottom Horizontal Scrolling Tool Box (Theme Green Header Ribbon Style) -->
            <div id="ledgerFixedScrollToolbox" class="ledger-fixed-scroll-toolbox" style="display: none;">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="toolbox-label">
                        <i class="fa fa-arrows-h mr-1" style="color: #86efac;"></i> Scroll Table:
                    </span>

                    <button type="button" class="btn-toolbox-action" onclick="scrollLedgerTable('start')" title="Jump to first column (Student Name)">
                        <i class="fa fa-angle-double-left mr-1" style="color: #217346;"></i> Start
                    </button>

                    <button type="button" class="btn-toolbox-action btn-ledger-hold-scroll" data-dir="left" onclick="scrollLedgerTable('left')" title="Scroll Left (Click or Hold to slide)">
                        <i class="fa fa-chevron-left mr-1" style="color: #217346;"></i> Left
                    </button>

                    <!-- Interactive Drag-to-Scroll Slider Bar -->
                    <div class="toolbox-slider-track">
                        <span class="slider-end-label">Student</span>
                        <input type="range" min="0" max="100" value="0" id="ledgerBottomScrollSlider" class="toolbox-range-slider" oninput="onLedgerSliderChange(this.value)" title="Drag slider to scroll table horizontally">
                        <span class="slider-end-label">Total</span>
                    </div>

                    <button type="button" class="btn-toolbox-action btn-ledger-hold-scroll" data-dir="right" onclick="scrollLedgerTable('right')" title="Scroll Right (Click or Hold to slide)">
                        Right <i class="fa fa-chevron-right ml-1" style="color: #217346;"></i>
                    </button>

                    <button type="button" class="btn-toolbox-action btn-toolbox-action-accent" onclick="scrollLedgerTable('end')" title="Jump directly to Course Total & Status columns">
                        Total <i class="fa fa-angle-double-right ml-1"></i>
                    </button>
                </div>
            </div>

            <!-- Bottom Status Footer -->
            <div class="ledger-status-bar">
                <div>
                    <span id="lblStudentCount" class="font-weight-bold" style="color: #1e293b;">Showing 0 students</span>
                    <span class="mx-2" style="color: #94a3b8;">•</span>
                    <span style="color: #64748b;">Values in Indian Rupees (₹)</span>
                    <span class="mx-2" style="color: #94a3b8;">•</span>
                    <span class="text-muted"><i class="fa fa-info-circle mr-1 text-info"></i>Scroll right to view all semesters & totals</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 6px;">
                    <span class="badge badge-status badge-paid"><i class="fa fa-check mr-1"></i> Paid: ₹0 Due</span>
                    <span class="badge badge-status badge-partial"><i class="fa fa-clock-o mr-1"></i> Partial Paid</span>
                    <span class="badge badge-status badge-unpaid"><i class="fa fa-exclamation-circle mr-1"></i> Outstanding Due</span>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Global cache for current ledger dataset
var currentLedgerData = null;
var searchTimer = null;


// ========================================================
// FIXED BOTTOM HORIZONTAL SCROLL TOOLBOX FOR LEDGER MATRIX
// ========================================================
function scrollLedgerTable(direction) {
    var el = document.getElementById('ledgerTableWrapper');
    if (!el) return;
    
    var step = 380;
    if (direction === 'left') {
        el.scrollBy({ left: -step, behavior: 'smooth' });
    } else if (direction === 'right') {
        el.scrollBy({ left: step, behavior: 'smooth' });
    } else if (direction === 'start') {
        el.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (direction === 'end') {
        el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
    }
}

function positionFixedScrollToolbox() {
    var tableEl = document.getElementById('ledgerTableWrapper');
    var toolbox = document.getElementById('ledgerFixedScrollToolbox');
    if (!tableEl || !toolbox) return;

    var rect = tableEl.getBoundingClientRect();
    var centerX = rect.left + (rect.width / 2);
    toolbox.style.left = centerX + 'px';
    toolbox.style.transform = 'translateX(-50%)';

    // Position safely above tfoot so table footer summary totals remain 100% visible
    var footEl = document.getElementById('tblFoot');
    if (footEl && footEl.children.length > 0) {
        var footRect = footEl.getBoundingClientRect();
        if (footRect.height > 0 && footRect.top > 0) {
            var bottomOffset = Math.max(78, Math.round(window.innerHeight - footRect.top + 8));
            toolbox.style.bottom = bottomOffset + 'px';
            return;
        }
    }

    var fallbackBottom = Math.max(78, Math.round(window.innerHeight - rect.bottom + 48));
    toolbox.style.bottom = fallbackBottom + 'px';
}

function updateLedgerScrollIndicators() {
    var tableWrap = document.getElementById('ledgerTableWrapper');
    var toolbox = document.getElementById('ledgerFixedScrollToolbox');
    var slider = document.getElementById('ledgerBottomScrollSlider');

    if (!tableWrap || !toolbox) return;

    var scrollWidth = tableWrap.scrollWidth;
    var clientWidth = tableWrap.clientWidth;
    var scrollLeft = tableWrap.scrollLeft;
    var maxScroll = Math.max(0, scrollWidth - clientWidth);

    if (maxScroll > 15) {
        toolbox.style.display = 'flex';
        positionFixedScrollToolbox();

        if (slider) {
            var pct = Math.round((scrollLeft / maxScroll) * 100);
            slider.value = pct;
        }
    } else {
        toolbox.style.display = 'none';
    }
}

function onLedgerSliderChange(val) {
    var tableWrap = document.getElementById('ledgerTableWrapper');
    if (!tableWrap) return;

    var maxScroll = Math.max(0, tableWrap.scrollWidth - tableWrap.clientWidth);
    var targetScroll = (parseFloat(val) / 100) * maxScroll;
    tableWrap.scrollLeft = targetScroll;
}

var ledgerHoldScrollInterval = null;

function startHoldScroll(dir) {
    stopHoldScroll();
    var tableWrap = document.getElementById('ledgerTableWrapper');
    if (!tableWrap) return;

    var step = (dir === 'left') ? -35 : 35;
    ledgerHoldScrollInterval = setInterval(function() {
        tableWrap.scrollLeft += step;
    }, 25);
}

function stopHoldScroll() {
    if (ledgerHoldScrollInterval) {
        clearInterval(ledgerHoldScrollInterval);
        ledgerHoldScrollInterval = null;
    }
}

function initLedgerScrollHandlers() {
    var tableWrap = document.getElementById('ledgerTableWrapper');

    if (tableWrap) {
        tableWrap.addEventListener('scroll', function() {
            updateLedgerScrollIndicators();
        });

        // Click-and-drag horizontal scrolling
        var isDown = false;
        var startX = 0;
        var startScrollLeft = 0;

        tableWrap.addEventListener('mousedown', function(e) {
            var tag = (e.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'button' || tag === 'a' || tag === 'select' || e.target.closest('button') || e.target.closest('input')) {
                return;
            }
            isDown = true;
            startX = e.pageX - tableWrap.offsetLeft;
            startScrollLeft = tableWrap.scrollLeft;
            tableWrap.style.cursor = 'grabbing';
            tableWrap.style.userSelect = 'none';
        });

        window.addEventListener('mouseup', function() {
            if (isDown) {
                isDown = false;
                if (tableWrap) {
                    tableWrap.style.cursor = 'default';
                    tableWrap.style.userSelect = '';
                }
            }
            stopHoldScroll();
        });

        tableWrap.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - tableWrap.offsetLeft;
            var walk = (x - startX) * 1.5;
            tableWrap.scrollLeft = startScrollLeft - walk;
        });
    }

    // Attach hold events to buttons
    $(document).on('mousedown touchstart', '.btn-ledger-hold-scroll', function(e) {
        var dir = $(this).data('dir');
        if (dir) startHoldScroll(dir);
    });

    $(document).on('mouseup mouseleave touchend', '.btn-ledger-hold-scroll', function() {
        stopHoldScroll();
    });

    $(window).on('resize scroll', function() {
        positionFixedScrollToolbox();
        updateLedgerScrollIndicators();
    });
}

$(document).ready(function() {
    initLedgerScrollHandlers();
    var initialCourse = $('#selCourse').val();
    var initialBatch = $('#selBatch').val();
    if (initialCourse && initialBatch) {
        loadCourseLedgerData();
    }
});

function onCourseOrBatchChange() {
    loadCourseLedgerData();
}

function applyFiltersDebounced() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        applyFilters();
    }, 200);
}

function loadCourseLedgerData() {
    var courseId = $('#selCourse').val();
    var batch = $('#selBatch').val();

    if (!courseId || !batch) {
        $('#tblBody').html('<tr><td colspan="15" class="text-center py-5 text-muted"><i class="fa fa-info-circle fa-2x mb-2 text-info d-block"></i>Please select both Course and Batch to view the ledger.</td></tr>');
        $('#tblHead').empty();
        $('#tblFoot').empty();
        return;
    }

    $('#ledgerLoadingOverlay').css('display', 'flex');

    $.ajax({
        url: "{{ route('college.fees.course_ledger_data') }}",
        type: "GET",
        data: {
            course_id: courseId,
            batch: batch
        },
        dataType: "json",
        success: function(res) {
            $('#ledgerLoadingOverlay').hide();
            if (res.status === 'success') {
                currentLedgerData = res;
                applyFilters();
            } else {
                toastr.error(res.message || "Failed to load ledger data.");
            }
        },
        error: function(err) {
            $('#ledgerLoadingOverlay').hide();
            toastr.error("Server error while loading course fee ledger.");
        }
    });
}

function getFilteredRows() {
    if (!currentLedgerData || !currentLedgerData.rows) return [];

    var statusFilter = $('#selStatus').val();
    var genderFilter = $('#selGender').val();
    var search = $('#txtSearch').val().toLowerCase().trim();

    return currentLedgerData.rows.filter(function(r) {
        // Gender filter
        if (genderFilter === 'boys' && r.gender !== 'Male') return false;
        if (genderFilter === 'girls' && r.gender !== 'Female') return false;

        // Status filter
        if (statusFilter === 'paid' && r.totals.status !== 'Paid') return false;
        if (statusFilter === 'partial' && r.totals.status !== 'Partial') return false;
        if (statusFilter === 'unpaid' && r.totals.status !== 'Unpaid') return false;
        if (statusFilter === 'overdue' && r.totals.due <= 0) return false;

        // Search filter
        if (search) {
            var match = (
                (r.student_name && r.student_name.toLowerCase().indexOf(search) !== -1) ||
                (r.admission_no && r.admission_no.toLowerCase().indexOf(search) !== -1) ||
                (r.father_name && r.father_name.toLowerCase().indexOf(search) !== -1) ||
                (r.mobile && r.mobile.indexOf(search) !== -1)
            );
            if (!match) return false;
        }

        return true;
    });
}

function applyFilters() {
    var filteredRows = getFilteredRows();
    var sems = currentLedgerData ? currentLedgerData.semesters : [];
    renderLedgerTable(filteredRows, sems);
    updateKPIs(filteredRows);
}

function updateKPIs(rows) {
    var totalStudents = rows.length;
    var totalDemand = 0;
    var totalPaid = 0;
    var totalBalance = 0;

    rows.forEach(function(r) {
        totalDemand += parseFloat(r.totals.net || 0);
        totalPaid += parseFloat(r.totals.paid || 0);
        totalBalance += parseFloat(r.totals.due || 0);
    });

    var rate = totalDemand > 0 ? ((totalPaid / totalDemand) * 100).toFixed(1) : '0.0';

    $('#kpiTotalStudents').text(totalStudents);
    $('#kpiTotalDemand').text('₹' + formatMoney(totalDemand));
    $('#kpiTotalCollected').text('₹' + formatMoney(totalPaid));
    $('#kpiTotalBalance').text('₹' + formatMoney(totalBalance));
    $('#kpiCollectionRate').text(rate + '%');

    $('#lblStudentCount').text('Showing ' + totalStudents + ' of ' + (currentLedgerData ? currentLedgerData.kpi.total_enrolled : 0) + ' enrolled students');
}

function renderLedgerTable(rows, semesters) {
    var semCount = semesters.length;

    // --- Build Header ---
    var thTier1 = '<tr class="tier-1">';
    var thTier2 = '<tr class="tier-2">';

    // Tier 1 - Fixed Sticky Student Info Columns with rowspan=2
    thTier1 += '<th class="col-sticky-sr" rowspan="2">Sr.</th>';
    thTier1 += '<th class="col-sticky-adm" rowspan="2">Adm / Regn</th>';
    thTier1 += '<th class="col-sticky-name" rowspan="2">Student Name</th>';
    thTier1 += '<th rowspan="2" style="min-width: 135px; border-right: 2px solid #8fa7b8;">Father Name</th>';

    // Dynamic Semesters Header Groups (Tier 1 & Tier 2)
    semesters.forEach(function(s) {
        thTier1 += '<th colspan="3" style="text-align: center; font-weight: 700; border-right: 2px solid #8fa7b8;">Semester ' + s + '</th>';
        thTier2 += '<th style="background: #e0f2fe !important; color: #0369a1 !important; text-align: right; min-width: 80px;">Net</th>';
        thTier2 += '<th style="background: #dcfce7 !important; color: #15803d !important; text-align: right; min-width: 80px;">Paid</th>';
        thTier2 += '<th style="background: #fef3c7 !important; color: #b45309 !important; text-align: right; min-width: 80px; border-right: 2px solid #8fa7b8;">Due</th>';
    });

    // Consolidated Course Total directly after Semesters (Previous Dues removed)
    thTier1 += '<th colspan="4" style="background: #d1fae5 !important; color: #065f46 !important; text-align: center; font-weight: 800; border-right: 1px solid #8fa7b8; border-left: 2px solid #8fa7b8;">Course Total</th>';
    thTier2 += '<th style="background: #e0f2fe !important; color: #0369a1 !important; text-align: right; font-weight: 800; min-width: 100px; border-left: 2px solid #8fa7b8;">Total Course Fees</th>';
    thTier2 += '<th style="background: #dcfce7 !important; color: #15803d !important; text-align: right; font-weight: 800; min-width: 100px;">Total Collected</th>';
    thTier2 += '<th style="background: #fee2e2 !important; color: #b91c1c !important; text-align: right; font-weight: 800; min-width: 90px;">Balance Due</th>';
    thTier2 += '<th style="background: #e7eff5 !important; color: #1e293b !important; text-align: center; font-weight: 800; width: 75px;">Status</th>';

    thTier1 += '</tr>';
    thTier2 += '</tr>';

    $('#tblHead').html(thTier1 + thTier2);

    // --- Build Body ---
    if (rows.length === 0) {
        var totalCols = 4 + (semCount * 3) + 4;
        $('#tblBody').html('<tr><td colspan="' + totalCols + '" class="text-center py-5 text-muted"><i class="fa fa-folder-open-o fa-2x mb-2 text-warning d-block"></i>No student records match the selected filters.</td></tr>');
        $('#tblFoot').empty();
        return;
    }

    var tbHtml = '';
    var footTotals = {
        sems: {},
        grand: { net: 0, paid: 0, due: 0 }
    };
    semesters.forEach(function(s) {
        footTotals.sems[s] = { net: 0, paid: 0, due: 0 };
    });

    rows.forEach(function(r, idx) {
        tbHtml += '<tr>';

        // Sticky Student Info
        tbHtml += '<td class="col-sticky-sr text-muted">' + (idx + 1) + '</td>';
        tbHtml += '<td class="col-sticky-adm">' + escapeHtml(r.admission_no) + '</td>';
        tbHtml += '<td class="col-sticky-name">' + escapeHtml(r.student_name) + '</td>';
        tbHtml += '<td class="text-muted" style="border-right: 2px solid #8fa7b8;">' + escapeHtml(r.father_name) + '</td>';

        // Semester Columns
        semesters.forEach(function(s) {
            var sData = r.semesters[s] || { net: 0, paid: 0, due: 0 };
            var net = parseFloat(sData.net || 0);
            var paid = parseFloat(sData.paid || 0);
            var due = parseFloat(sData.due || 0);

            footTotals.sems[s].net += net;
            footTotals.sems[s].paid += paid;
            footTotals.sems[s].due += due;

            tbHtml += '<td style="text-align: right;">' + (net > 0 ? '₹' + formatMoney(net) : '<span class="text-muted">-</span>') + '</td>';
            tbHtml += '<td style="text-align: right; color: #16a34a; font-weight: ' + (paid > 0 ? '600' : 'normal') + ';">' + (paid > 0 ? '₹' + formatMoney(paid) : '<span class="text-muted">-</span>') + '</td>';
            tbHtml += '<td style="text-align: right; color: ' + (due > 0 ? '#dc2626' : (due < 0 ? '#16a34a' : '#64748b')) + '; font-weight: ' + (due != 0 ? '700' : 'normal') + '; border-right: 2px solid #8fa7b8;">' + (due != 0 ? (due < 0 ? '(-₹' + formatMoney(Math.abs(due)) + ')' : '₹' + formatMoney(due)) : '<span class="text-muted">₹0</span>') + '</td>';
        });



        // Course Grand Total
        var gNet = parseFloat(r.totals.net || 0);
        var gPaid = parseFloat(r.totals.paid || 0);
        var gDue = parseFloat(r.totals.due || 0);

        footTotals.grand.net += gNet;
        footTotals.grand.paid += gPaid;
        footTotals.grand.due += gDue;

        var statusClass = (r.totals.status === 'Paid') ? 'badge-paid' : ((r.totals.status === 'Partial') ? 'badge-partial' : 'badge-unpaid');

        tbHtml += '<td style="text-align: right; font-weight: 700; border-left: 2px solid #8fa7b8;">₹' + formatMoney(gNet) + '</td>';
        tbHtml += '<td style="text-align: right; font-weight: 700; color: #15803d;">₹' + formatMoney(gPaid) + '</td>';
        tbHtml += '<td style="text-align: right; font-weight: 800; color: ' + (gDue > 0 ? '#b91c1c' : (gDue < 0 ? '#15803d' : '#0f172a')) + ';">' + (gDue < 0 ? '(-₹' + formatMoney(Math.abs(gDue)) + ')' : '₹' + formatMoney(gDue)) + '</td>';
        tbHtml += '<td style="text-align: center;"><span class="badge-status ' + statusClass + '">' + r.totals.status + '</span></td>';

        tbHtml += '</tr>';
    });

    $('#tblBody').html(tbHtml);

    // --- Build Footer (Solid Opaque Executive Columns - No Bleed-through!) ---
    var tfHtml = '<tr>';

    // Sticky Student Info Footer Cells
    tfHtml += '<td class="col-sticky-sr text-center"><i class="fa fa-calculator text-muted"></i></td>';
    tfHtml += '<td class="col-sticky-adm text-center font-weight-bold" style="color: #1e3a5f;">TOTAL</td>';
    tfHtml += '<td class="col-sticky-name font-weight-bold" style="color: #1e3a5f;">' + rows.length + ' Students</td>';
    tfHtml += '<td style="border-right: 2px solid #8fa7b8;"></td>';

    // Semesters Totals
    semesters.forEach(function(s) {
        var st = footTotals.sems[s];
        tfHtml += '<td style="text-align: right; background: #e0f2fe !important; color: #0369a1 !important; font-weight: 700;">₹' + formatMoney(st.net) + '</td>';
        tfHtml += '<td style="text-align: right; background: #dcfce7 !important; color: #15803d !important; font-weight: 700;">₹' + formatMoney(st.paid) + '</td>';
        tfHtml += '<td style="text-align: right; background: #fef3c7 !important; color: ' + (st.due > 0 ? '#b91c1c' : '#0f172a') + ' !important; font-weight: 800; border-right: 2px solid #8fa7b8;">₹' + formatMoney(st.due) + '</td>';
    });



    // Course Grand Totals
    tfHtml += '<td style="text-align: right; background: #e0f2fe !important; color: #0369a1 !important; font-weight: 800; font-size: 12px; border-left: 2px solid #8fa7b8;">₹' + formatMoney(footTotals.grand.net) + '</td>';
    tfHtml += '<td style="text-align: right; background: #dcfce7 !important; color: #15803d !important; font-weight: 800; font-size: 12px;">₹' + formatMoney(footTotals.grand.paid) + '</td>';
    tfHtml += '<td style="text-align: right; background: #fee2e2 !important; color: ' + (footTotals.grand.due > 0 ? '#b91c1c' : '#0f172a') + ' !important; font-weight: 800; font-size: 12px;">₹' + formatMoney(footTotals.grand.due) + '</td>';
    tfHtml += '<td style="text-align: center; color: #64748b; font-weight: 700;">-</td>';

    tfHtml += '</tr>';

    $('#tblFoot').html(tfHtml);
}

// --- Native Excel (.xlsx) Export using SheetJS ---
function exportToExcel() {
    var rows = getFilteredRows();
    if (!rows || rows.length === 0) {
        toastr.warning("No data available to export.");
        return;
    }

    var courseName = $('#selCourse option:selected').text().trim();
    var batch = $('#selBatch').val();
    var semesters = currentLedgerData.semesters;

    var excelData = [];

    // Title rows
    excelData.push(["SHRI BABU LAL GROUP OF INSTITUTIONS - CONSOLIDATED COURSE FEES LEDGER"]);
    excelData.push(["Course: " + courseName, "Batch: " + batch, "Generated: " + new Date().toLocaleString()]);
    excelData.push([]); // Blank separator

    // 2-Tier Headers
    var headerRow1 = ["Sr.", "Admission No", "Student Name", "Father Name"];
    var headerRow2 = ["", "", "", ""];

    semesters.forEach(function(s) {
        headerRow1.push("Semester " + s, "", "");
        headerRow2.push("Net Demand", "Paid", "Due");
    });

    headerRow1.push("Course Total", "", "", "Status");
    headerRow2.push("Total Course Fees", "Total Collected", "Balance Due", "");

    excelData.push(headerRow1);
    excelData.push(headerRow2);

    // Sum accumulators for exported rows
    var footTotals = {
        sems: {},
        grand: { net: 0, paid: 0, due: 0 }
    };
    semesters.forEach(function(s) {
        footTotals.sems[s] = { net: 0, paid: 0, due: 0 };
    });

    // Student Rows
    rows.forEach(function(r, idx) {
        var row = [
            idx + 1,
            r.admission_no,
            r.student_name,
            r.father_name
        ];

        semesters.forEach(function(s) {
            var sData = r.semesters[s] || { net: 0, paid: 0, due: 0 };
            var net = parseFloat(sData.net || 0);
            var paid = parseFloat(sData.paid || 0);
            var due = parseFloat(sData.due || 0);

            footTotals.sems[s].net += net;
            footTotals.sems[s].paid += paid;
            footTotals.sems[s].due += due;

            row.push(net, paid, due);
        });



        var gNet = parseFloat(r.totals.net || 0);
        var gPaid = parseFloat(r.totals.paid || 0);
        var gDue = parseFloat(r.totals.due || 0);

        footTotals.grand.net += gNet;
        footTotals.grand.paid += gPaid;
        footTotals.grand.due += gDue;

        row.push(gNet, gPaid, gDue, r.totals.status);

        excelData.push(row);
    });

    // Grand Total Row
    var totRow = ["TOTAL (" + rows.length + " Students)", "", "", ""];
    semesters.forEach(function(s) {
        var st = footTotals.sems[s];
        totRow.push(st.net, st.paid, st.due);
    });
    totRow.push(footTotals.grand.net, footTotals.grand.paid, footTotals.grand.due, "");
    excelData.push(totRow);

    // Create Worksheet
    var ws = XLSX.utils.aoa_to_sheet(excelData);

    // Merge Cells for 2-Tier Header
    ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: headerRow1.length - 1 } }, // Title
        { s: { r: 3, c: 0 }, e: { r: 4, c: 0 } }, // Sr.
        { s: { r: 3, c: 1 }, e: { r: 4, c: 1 } }, // Adm
        { s: { r: 3, c: 2 }, e: { r: 4, c: 2 } }, // Name
        { s: { r: 3, c: 3 }, e: { r: 4, c: 3 } }, // Father
    ];

    var colIdx = 4;
    semesters.forEach(function() {
        ws['!merges'].push({ s: { r: 3, c: colIdx }, e: { r: 3, c: colIdx + 2 } });
        colIdx += 3;
    });

    // Course Total merge (3 cols: Total Course Fees, Total Collected, Balance Due)
    ws['!merges'].push({ s: { r: 3, c: colIdx }, e: { r: 3, c: colIdx + 2 } });
    // Status column merge (row 3 to 4)
    ws['!merges'].push({ s: { r: 3, c: colIdx + 3 }, e: { r: 4, c: colIdx + 3 } });

    // Workbook
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Course Fees Ledger");

    var fileName = "Course_Fees_Ledger_" + courseName.replace(/[^a-zA-Z0-9]/g, "_") + "_" + batch + ".xlsx";
    XLSX.writeFile(wb, fileName);
    toastr.success("Excel ledger exported successfully!");
}

// --- CSV Export Fallback ---
function exportToCSV() {
    var rows = getFilteredRows();
    if (!rows || rows.length === 0) {
        toastr.warning("No data available to export.");
        return;
    }

    var courseName = $('#selCourse option:selected').text().trim();
    var batch = $('#selBatch').val();
    var semesters = currentLedgerData.semesters;

    var csv = [];
    csv.push('"SHRI BABU LAL GROUP OF INSTITUTIONS - CONSOLIDATED COURSE FEES LEDGER"');
    csv.push('"Course: ' + courseName + '","Batch: ' + batch + '","Generated: ' + new Date().toLocaleString() + '"');
    csv.push('');

    // Headers
    var h1 = ['Sr.', 'Admission No', 'Student Name', 'Father Name'];
    semesters.forEach(function(s) {
        h1.push('Sem ' + s + ' Net', 'Sem ' + s + ' Paid', 'Sem ' + s + ' Due');
    });
    h1.push('Total Course Fees', 'Total Collected', 'Balance Due', 'Status');
    csv.push(h1.map(function(c) { return '"' + c + '"'; }).join(','));

    // Rows
    rows.forEach(function(r, idx) {
        var row = [
            idx + 1,
            r.admission_no,
            r.student_name,
            r.father_name
        ];
        semesters.forEach(function(s) {
            var sData = r.semesters[s] || { net: 0, paid: 0, due: 0 };
            row.push(sData.net, sData.paid, sData.due);
        });

        row.push(r.totals.net, r.totals.paid, r.totals.due, r.totals.status);

        csv.push(row.map(function(c) { return '"' + (c !== null ? c : '') + '"'; }).join(','));
    });

    var csvContent = "\ufeff" + csv.join(String.fromCharCode(10));
    var blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    var link = document.createElement("a");
    var url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "Course_Fees_Ledger_" + courseName.replace(/[^a-zA-Z0-9]/g, "_") + "_" + batch + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    toastr.success("CSV ledger exported successfully!");
}

function formatMoney(num) {
    if (isNaN(num) || num === null) return '0.00';
    return parseFloat(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(text) {
    if (!text) return '';
    return $('<div>').text(text).html();
}
</script>
@endsection
