@extends('layout.app')
@section('content')

<style>
/* ========================================================
   COLLEGE FEE REPORTS & REGISTERS
   Executive Matrix Theme Layout (Matching structures.blade.php)
   - Header Ribbon: Excel Green (#217346 / #1e7e34)
   - Title Font Size: 14.5px Standard
   - Controls & Inputs: 29px Height / 11.5px Font Size
   - Table Headers: Soft Blue (#d9eaf7) & Sticky Top 0
   - Table Footers: Executive Tint (#e7eff5) & Sticky Bottom 0
   - Action Buttons: .btn-matrix-tool with Theme Hover
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
    background: #ffffff;
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

/* Nav Tabs Strip */
.reports-tab-strip {
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
    padding: 0 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.nav-tabs-simple {
    border-bottom: none;
    margin-bottom: 0;
    gap: 4px;
}

.nav-tabs-simple .nav-link {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 8px 16px;
    border-radius: 0;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    background: transparent;
    cursor: pointer;
}

.nav-tabs-simple .nav-link:hover {
    color: var(--green-excel);
    background: rgba(33, 115, 70, 0.05);
}

.nav-tabs-simple .nav-link.active {
    color: var(--green-excel);
    border-bottom-color: var(--green-excel);
    background: #ffffff;
    font-weight: 800;
}

/* Compact Filter Toolbar (Unified 29px Input Strip matching structures & progression) */
.reports-filter-panel {
    background: #ffffff;
    border-bottom: 1px solid #cbd5e1;
    padding: 8px 12px;
}

.toolbar-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 3px;
    display: block;
    white-space: nowrap;
}

.toolbar-control {
    height: 29px !important;
    min-height: 29px !important;
    padding: 3px 8px !important;
    font-size: 11.5px !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
    color: #172033 !important;
    background: #ffffff !important;
    width: 100%;
    outline: none;
    transition: all 0.15s ease;
}

.toolbar-control:focus {
    border-color: var(--green-excel);
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25);
}

/* Buttons & Action Tools */
.btn-search-action {
    height: 29px !important;
    padding: 3px 14px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    line-height: 1;
    transition: all 0.15s ease;
    text-decoration: none !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
}

.btn-search-action:hover {
    background: var(--green-excel-dark) !important;
    border-color: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

.btn-matrix-tool {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    padding: 3px 10px !important;
    height: 29px !important;
    border-radius: 2px !important;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
}

.btn-matrix-tool:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

/* Spreadsheet Box & Table Container (Directly matches structures.blade.php) */
.matrix-container-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 2px;
    overflow: hidden;
}

.matrix-action-bar {
    padding: 6px 10px;
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.search-input-wrapper {
    position: relative;
    width: 290px;
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
    width: 100%;
    height: 29px;
    padding-left: 26px;
    padding-right: 8px;
    font-size: 11.5px;
    border: 1px solid #b8c4d1;
    border-radius: 2px;
    outline: none;
    color: #0f172a;
    background: #ffffff;
    transition: all 0.15s ease;
}

.search-input-field:focus {
    border-color: var(--green-excel);
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25);
}

/* Scrollbars for Reports Table */
.reports-table-responsive::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}
.reports-table-responsive::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}
.reports-table-responsive::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 6px;
    border: 2px solid #f1f5f9;
}
.reports-table-responsive::-webkit-scrollbar-thumb:hover {
    background: #475569;
}

.reports-table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
    overflow-x: auto;
    overflow-y: auto;
    width: 100%;
}

/* Matrix Table Core Styling (Strictly cloned from structures.blade.php) */
.matrix-table {
    width: 100% !important;
    min-width: 100% !important;
    max-width: none !important;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 11.5px;
    table-layout: auto;
    border-left: 1px solid #b8c9d9;
    border-top: 1px solid #8fa7b8;
}

.matrix-table th {
    background: #d9eaf7 !important;
    color: #152536 !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.2px;
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
    padding: 4px 8px !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #cbd5e1 !important;
    border-right: 1px solid #e2e8f0 !important;
    white-space: nowrap;
    font-size: 11.5px;
    color: #0f172a;
}

.matrix-table tr:hover td {
    background-color: #f8fafc;
}

/* Row styling for cancelled receipts */
.matrix-table tr.receipt-row-cancelled {
    background-color: #fffaf0;
}
.matrix-table tr.receipt-row-cancelled:hover td {
    background-color: #fff1f2;
}

/* Specific Column Helper Classes */
.col-matrix-idx {
    width: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    text-align: center !important;
    padding: 4px 2px !important;
    white-space: nowrap !important;
}

.col-matrix-student {
    min-width: 190px !important;
    max-width: none !important;
    white-space: nowrap !important;
    padding: 3px 8px !important;
}

.col-matrix-amount {
    white-space: nowrap !important;
    text-align: right !important;
    padding: 3px 8px !important;
    font-weight: 700;
}

.col-matrix-action {
    white-space: nowrap !important;
    text-align: center !important;
    padding: 3px 6px !important;
    width: 1%;
}

.student-cell-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 11.5px;
    line-height: 1.25;
    white-space: nowrap;
}

.student-cell-sub {
    font-size: 10px;
    color: #64748b;
    line-height: 1.2;
    margin-top: 1px;
}

/* Compact Badges & Indicators */
.badge-tag-course {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    padding: 1px 5px;
    border-radius: 2px;
    font-weight: 700;
    font-size: 10px;
    display: inline-block;
}

.badge-tag-batch {
    background: #fef9c3;
    color: #854d0e;
    border: 1px solid #fde047;
    padding: 1px 5px;
    border-radius: 2px;
    font-weight: 700;
    font-size: 10px;
    display: inline-block;
}

.badge-tag-sem {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    padding: 1px 5px;
    border-radius: 2px;
    font-weight: 600;
    font-size: 10px;
    display: inline-block;
}

.badge-mode-pill {
    padding: 1px 6px;
    border-radius: 2px;
    font-weight: 700;
    font-size: 10px;
    display: inline-block;
}
.mode-cash { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.mode-upi { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.mode-bank { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.mode-cheque { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }

/* Status Badges */
.badge-status-paid {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
    font-weight: 800;
    font-size: 10px;
    padding: 1.5px 6px;
    border-radius: 2px;
    display: inline-block;
    letter-spacing: 0.3px;
}

.badge-status-cancelled {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
    font-weight: 800;
    font-size: 10px;
    padding: 1.5px 6px;
    border-radius: 2px;
    display: inline-block;
    letter-spacing: 0.3px;
}
</style>

<div class="content-wrapper">
    <div class="reports-page-wrapper">

        <!-- Page Card Container -->
        <div class="card fees-reports-page-card">

            <!-- Card Header (Consistent 14.5px Titlebar matching structures.blade.php) -->
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-file-text-o"></i>
                    College Fee Reports &amp; Registers
                </h3>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('college.fees.dashboard') }}" class="btn-header-light" title="College Fees Dashboard">
                        <i class="fa fa-dashboard text-success mr-1"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Tab Navigation Strip (URL Query Parameter Supported, No # Hash) -->
            <div class="reports-tab-strip">
                <ul class="nav nav-tabs nav-tabs-simple" id="reportTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab !== 'defaulters' ? 'active' : '' }}" id="register-tab" href="javascript:void(0)" onclick="switchReportTab('register')" role="tab">
                            <i class="fa fa-list-alt mr-1 text-primary"></i> Fee Collection Register
                            <span class="badge badge-primary ml-1" id="tabReceiptsBadge">{{ count($receipts) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'defaulters' ? 'active' : '' }}" id="defaulters-tab" href="javascript:void(0)" onclick="switchReportTab('defaulters')" role="tab">
                            <i class="fa fa-exclamation-triangle mr-1 text-danger"></i> Defaulters &amp; Outstanding Dues
                            <span class="badge badge-danger ml-1" id="tabDefaultersBadge">{{ count($defaulters) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Compact Filter Toolbar (Unified 29px Input Strip matching structures & progression) -->
            <div class="reports-filter-panel">
                <form id="filterReportsForm">
                    <!-- Row 1: Primary Academic Filters & Status Filter -->
                    <div class="form-row align-items-end">
                        
                        <!-- Financial Year / Session Filter -->
                        <div class="col-md-2 form-group mb-1">
                            <label class="toolbar-label">Financial Year</label>
                            <select name="session_id" id="rep_session_id" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="">All Financial Years</option>
                                @foreach($financialYears as $fy)
                                    <option value="{{ $fy->id }}" {{ $sessionId == $fy->id ? 'selected' : '' }}>Session {{ $fy->year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Course Filter -->
                        <div class="col-md-3 form-group mb-1">
                            <label class="toolbar-label">Course</label>
                            <select name="course_id" id="rep_course_id" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="">All Courses</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Batch Filter (Course ke sath Batch) -->
                        <div class="col-md-2 form-group mb-1">
                            <label class="toolbar-label">Batch</label>
                            <select name="batch" id="rep_batch" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="">All Batches</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b }}" {{ $batch == $b ? 'selected' : '' }}>Batch {{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Semester Filter -->
                        <div class="col-md-1 form-group mb-1">
                            <label class="toolbar-label">Semester</label>
                            <select name="semester_no" id="rep_semester_no" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="">All</option>
                                @for($s = 1; $s <= 8; $s++)
                                    <option value="{{ $s }}" {{ $semesterNo == $s ? 'selected' : '' }}>Sem {{ $s }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Receipt Status Filter (All, Paid Only, Cancelled Only) -->
                        <div class="col-md-2 form-group mb-1" id="group_rep_receipt_status" style="{{ $activeTab === 'defaulters' ? 'display: none;' : '' }}">
                            <label class="toolbar-label">Receipt Status</label>
                            <select name="receipt_status" id="rep_receipt_status" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="all" {{ ($receiptStatus ?? 'all') == 'all' ? 'selected' : '' }}>All (Paid &amp; Cancelled)</option>
                                <option value="paid" {{ ($receiptStatus ?? 'all') == 'paid' ? 'selected' : '' }}>Paid Only</option>
                                <option value="cancelled" {{ ($receiptStatus ?? 'all') == 'cancelled' ? 'selected' : '' }}>Cancelled Only</option>
                            </select>
                        </div>

                        <!-- Payment Mode Filter -->
                        <div class="col-md-2 form-group mb-1" id="group_rep_payment_mode" style="{{ $activeTab === 'defaulters' ? 'display: none;' : '' }}">
                            <label class="toolbar-label">Payment Mode</label>
                            <select name="payment_mode" id="rep_payment_mode" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="">All Modes</option>
                                <option value="Cash" {{ $paymentMode == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="UPI" {{ $paymentMode == 'UPI' ? 'selected' : '' }}>UPI / QR</option>
                                <option value="Bank Transfer" {{ $paymentMode == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ $paymentMode == 'Cheque' ? 'selected' : '' }}>Cheque / DD</option>
                            </select>
                        </div>

                    </div>

                    <!-- Row 2: Cashier, Date Range, Min Dues and Action Buttons -->
                    <div class="form-row align-items-end mt-1 pt-1">
                        
                        <!-- Cashier Filter -->
                        <div class="col-md-2 form-group mb-1" id="group_rep_cashier_id" style="{{ $activeTab === 'defaulters' ? 'display: none;' : '' }}">
                            <label class="toolbar-label">Cashier / Staff</label>
                            <select name="cashier_id" id="rep_cashier_id" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="">All Cashiers</option>
                                @foreach($cashiers as $usr)
                                    <option value="{{ $usr->id }}" {{ $cashierId == $usr->id ? 'selected' : '' }}>{{ $usr->first_name }} {{ $usr->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- From Date -->
                        <div class="col-md-2 form-group mb-1">
                            <label class="toolbar-label">Date From</label>
                            <input type="date" name="from_date" id="rep_from_date" class="toolbar-control" value="{{ $fromDate }}">
                        </div>

                        <!-- To Date -->
                        <div class="col-md-2 form-group mb-1">
                            <label class="toolbar-label">Date To</label>
                            <input type="date" name="to_date" id="rep_to_date" class="toolbar-control" value="{{ $toDate }}">
                        </div>

                        <!-- Min Dues filter (visible on Defaulters tab) -->
                        <div class="col-md-2 form-group mb-1" id="boxMinDuesFilter" style="{{ $activeTab === 'defaulters' ? '' : 'display: none;' }}">
                            <label class="toolbar-label">Min Outstanding Due</label>
                            <select name="min_dues" id="rep_min_dues" class="toolbar-control" onchange="applyReportsFilter()">
                                <option value="1" {{ $minDues == 1 ? 'selected' : '' }}>All (&gt; ₹0)</option>
                                <option value="500" {{ $minDues == 500 ? 'selected' : '' }}>&gt;= ₹500</option>
                                <option value="1000" {{ $minDues == 1000 ? 'selected' : '' }}>&gt;= ₹1,000</option>
                                <option value="5000" {{ $minDues == 5000 ? 'selected' : '' }}>&gt;= ₹5,000</option>
                            </select>
                        </div>

                        <!-- Action Buttons aligned neatly on the right -->
                        <div class="col-md form-group mb-1 text-right">
                            <button type="submit" id="btnFilterApply" class="btn btn-search-action px-3" title="Apply Filters">
                                <i class="fa fa-filter mr-1"></i> Apply Filter
                            </button>
                            <button type="button" class="btn btn-matrix-tool px-3 ml-1" onclick="resetReportsFilter()" title="Reset all filters">
                                <i class="fa fa-refresh mr-1"></i> Reset
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="reportTabsContent">

                <!-- Tab 1: Collection Register -->
                <div class="tab-pane fade {{ $activeTab !== 'defaulters' ? 'show active' : '' }}" id="registerTabContent" role="tabpanel">
                    <div class="matrix-container-card">
                        
                        <!-- Action Bar Above Spreadsheet (Matches structures.blade.php) -->
                        <div class="matrix-action-bar">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <div class="search-input-wrapper">
                                    <i class="fa fa-search"></i>
                                    <input type="text" id="rep_live_search_reg" class="search-input-field" placeholder="Search by student, adm no, receipt no, status..." onkeyup="filterReceiptsLive(this.value)">
                                </div>
                                <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px; padding: 4px 8px; border-radius: 2px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-list-alt text-primary"></i> <span id="lblReceiptsCount">{{ count($receipts) }}</span> Receipts
                                </span>
                                <span class="badge badge-light border text-success font-weight-bold" style="font-size: 11px; padding: 4px 8px; border-radius: 2px; display: inline-flex; align-items: center; gap: 4px;" id="lblTotalCollected">
                                    <i class="fa fa-inr text-success"></i> Active: ₹{{ number_format($totalCollected, 2) }}
                                </span>
                                <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11px; padding: 4px 8px; border-radius: 2px; display: inline-flex; align-items: center; gap: 4px;" id="lblCancelledSummary">
                                    <i class="fa fa-ban text-danger"></i> Cancelled: <span id="lblCancelledCount">{{ $cancelledCount ?? 0 }}</span> (₹<span id="lblCancelledAmt">{{ number_format($totalCancelled ?? 0, 2) }}</span>)
                                </span>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                <button type="button" class="btn-matrix-tool" onclick="exportActiveTabToCSV()" title="Export current fee receipts to CSV">
                                    <i class="fa fa-file-excel-o text-success mr-1"></i> Export CSV
                                </button>
                            </div>
                        </div>

                        <!-- Table Responsive Scroll Container -->
                        <div class="table-responsive reports-table-responsive" id="receiptsTableResponsive">
                            <table class="table matrix-table" id="tblReceipts">
                                <thead>
                                    <tr>
                                        <th class="col-matrix-idx">#</th>
                                        <th style="min-width: 125px;">Receipt No</th>
                                        <th style="min-width: 80px;" class="text-center">Status</th>
                                        <th style="min-width: 95px;">Date</th>
                                        <th class="col-matrix-student">Student Details</th>
                                        <th style="min-width: 175px;">Course &amp; Batch</th>
                                        <th style="min-width: 110px;">Payment Mode</th>
                                        <th class="col-matrix-amount" style="min-width: 110px;">Amount Paid</th>
                                        <th style="min-width: 140px;">Cashier / Handled By</th>
                                        <th class="col-matrix-action">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="receiptsTableBody">
                                    @forelse($receipts as $idx => $r)
                                        @php
                                            $studentName = trim(($r->student->first_name ?? '') . ' ' . ($r->student->last_name ?? ''));
                                            $modeKey = strtolower(str_replace(' ', '', $r->payment_mode ?? 'cash'));
                                            $isCancelled = !is_null($r->deleted_at);
                                        @endphp
                                        <tr class="receipt-data-row {{ $isCancelled ? 'receipt-row-cancelled' : '' }}" data-status="{{ $isCancelled ? 'cancelled' : 'paid' }}" data-search="{{ strtolower($r->receipt_no . ' ' . ($isCancelled ? 'cancelled void' : 'paid active') . ' ' . $studentName . ' ' . ($r->student->admissionNo ?? '') . ' ' . ($r->student->mobile ?? '') . ' ' . ($r->course->name ?? '') . ' ' . ($r->student->batch ?? '')) }}">
                                            <td class="col-matrix-idx text-muted font-weight-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <a href="{{ route('college.fees.receipt', $r->id) }}" target="_blank" class="font-weight-bold" style="color: {{ $isCancelled ? '#dc2626' : '#0284c7' }}; text-decoration: none;" title="Click to view &amp; print receipt">
                                                    {{ $r->receipt_no }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @if($isCancelled)
                                                    <span class="badge-status-cancelled">CANCELLED</span>
                                                @else
                                                    <span class="badge-status-paid">PAID</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span>{{ date('d-M-Y', strtotime($r->payment_date)) }}</span>
                                            </td>
                                            <td class="col-matrix-student">
                                                <div class="student-cell-name">{{ $studentName ?: 'N/A' }}</div>
                                                <div class="student-cell-sub">
                                                    <strong>Adm:</strong> {{ $r->student->admissionNo ?? 'N/A' }}
                                                    @if(!empty($r->student->mobile))
                                                        &bull; {{ $r->student->mobile }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                                    <span class="badge-tag-course">{{ $r->course->name ?? 'Course' }}</span>
                                                    <span class="badge-tag-batch">Batch {{ $r->student->batch ?? 'N/A' }}</span>
                                                    <span class="badge-tag-sem">Sem {{ $r->semester_no }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-mode-pill mode-{{ $modeKey }}">
                                                    {{ $r->payment_mode }}
                                                </span>
                                                @if(!empty($r->transaction_id))
                                                    <div class="text-muted" style="font-size: 9.5px; line-height: 1.1; margin-top: 2px;">
                                                        Txn: {{ $r->transaction_id }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="col-matrix-amount">
                                                @if($isCancelled)
                                                    <span style="text-decoration: line-through; color: #94a3b8; font-weight: 600;">₹{{ number_format($r->amount_paid, 2) }}</span>
                                                    <small class="text-danger font-weight-bold d-block" style="font-size: 9.5px; line-height: 1.1;">Void / Reverted</small>
                                                @else
                                                    <span class="font-weight-bold text-success" style="font-size: 12.5px;">₹{{ number_format($r->amount_paid, 2) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span>{{ trim(($r->cashier->first_name ?? '') . ' ' . ($r->cashier->last_name ?? '')) ?: 'System / Admin' }}</span>
                                            </td>
                                            <td class="col-matrix-action">
                                                <a href="{{ route('college.fees.receipt', $r->id) }}" target="_blank" class="btn btn-xs btn-matrix-tool py-0 px-2" style="height: 23px; font-size: 10.5px;" title="Print Receipt">
                                                    <i class="fa fa-print text-primary mr-1"></i> Print
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="rowReceiptsEmpty">
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <i class="fa fa-file-text-o fa-2x text-muted mb-2"></i><br>
                                                <strong>No fee receipts match your selected filter criteria.</strong><br>
                                                <small>Try selecting a different financial year, course or status.</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-right font-weight-bold text-uppercase">Total Active Collection:</td>
                                        <td class="text-right font-weight-bold text-success" style="font-size: 12.5px;" id="lblTableTotalCollected">
                                            ₹{{ number_format($totalCollected, 2) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- Tab 2: Defaulters & Outstanding Dues List -->
                <div class="tab-pane fade {{ $activeTab === 'defaulters' ? 'show active' : '' }}" id="defaultersTabContent" role="tabpanel">
                    <div class="matrix-container-card">
                        
                        <!-- Action Bar Above Spreadsheet (Matches structures.blade.php) -->
                        <div class="matrix-action-bar">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <div class="search-input-wrapper">
                                    <i class="fa fa-search"></i>
                                    <input type="text" id="rep_live_search_def" class="search-input-field" placeholder="Search by student name, adm no, mobile, course..." onkeyup="filterDefaultersLive(this.value)">
                                </div>
                                <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11px; padding: 4px 8px; border-radius: 2px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa fa-exclamation-triangle text-danger"></i> <span id="lblDefaultersBadgeCount">{{ count($defaulters) }}</span> Defaulters
                                </span>
                                <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11px; padding: 4px 8px; border-radius: 2px; display: inline-flex; align-items: center; gap: 4px;" id="lblDefaultersTotalDues">
                                    <i class="fa fa-inr text-danger"></i> Total Dues: ₹{{ number_format($totalDefaultersDues, 2) }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                <button type="button" class="btn-matrix-tool" onclick="exportActiveTabToCSV()" title="Export defaulters list to CSV">
                                    <i class="fa fa-file-excel-o text-success mr-1"></i> Export CSV
                                </button>
                            </div>
                        </div>

                        <!-- Table Responsive Scroll Container -->
                        <div class="table-responsive reports-table-responsive" id="defaultersTableResponsive">
                            <table class="table matrix-table" id="tblDefaulters">
                                <thead>
                                    <tr>
                                        <th class="col-matrix-idx">#</th>
                                        <th style="min-width: 110px;">Admission No</th>
                                        <th class="col-matrix-student">Student &amp; Parent Details</th>
                                        <th style="min-width: 175px;">Course &amp; Batch</th>
                                        <th style="min-width: 115px;">Mobile No</th>
                                        <th class="col-matrix-amount" style="min-width: 105px;">Total Demand</th>
                                        <th class="col-matrix-amount" style="min-width: 105px;">Total Paid</th>
                                        <th class="col-matrix-amount" style="min-width: 110px;">Pending Dues</th>
                                        <th class="col-matrix-action" style="min-width: 120px;">Quick Action</th>
                                    </tr>
                                </thead>
                                <tbody id="defaultersTableBody">
                                    @forelse($defaulters as $idx => $def)
                                        @php
                                            $studentName = trim(($def->student->first_name ?? '') . ' ' . ($def->student->last_name ?? ''));
                                        @endphp
                                        <tr class="defaulter-data-row" data-search="{{ strtolower($studentName . ' ' . ($def->student->admissionNo ?? '') . ' ' . ($def->student->mobile ?? '') . ' ' . ($def->course->name ?? '') . ' ' . ($def->student->batch ?? '')) }}">
                                            <td class="col-matrix-idx text-muted font-weight-bold">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $def->student->admissionNo ?? 'N/A' }}</strong>
                                            </td>
                                            <td class="col-matrix-student">
                                                <div class="student-cell-name">{{ $studentName ?: 'N/A' }}</div>
                                                <div class="student-cell-sub">
                                                    <strong>F:</strong> {{ $def->student->father_name ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                                    <span class="badge-tag-course">{{ $def->course->name ?? 'Course' }}</span>
                                                    <span class="badge-tag-batch">Batch {{ $def->student->batch ?? 'N/A' }}</span>
                                                    <span class="badge-tag-sem">Sem {{ $def->current_sem }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if(!empty($def->student->mobile))
                                                    <a href="tel:{{ $def->student->mobile }}" style="color: #0284c7; font-weight: 700; text-decoration: none;">
                                                        <i class="fa fa-phone mr-1"></i>{{ $def->student->mobile }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="col-matrix-amount text-muted">
                                                ₹{{ number_format($def->total_demand, 2) }}
                                            </td>
                                            <td class="col-matrix-amount text-success">
                                                ₹{{ number_format($def->total_paid, 2) }}
                                            </td>
                                            <td class="col-matrix-amount font-weight-bold text-danger" style="font-size: 12.5px;">
                                                ₹{{ number_format($def->total_due, 2) }}
                                            </td>
                                            <td class="col-matrix-action">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="{{ route('college.fees.structures') }}" class="btn btn-xs btn-matrix-tool py-0 px-2 text-success font-weight-bold" style="height: 23px; font-size: 10.5px; border-color: #86efac; background: #f0fdf4;" title="Open Fee Matrix">
                                                        <i class="fa fa-calculator text-success mr-1"></i> Collect
                                                    </a>
                                                    @if(!empty($def->student->mobile))
                                                        <a href="https://api.whatsapp.com/send?phone=91{{ $def->student->mobile }}&text=Dear%20Student%20{{ urlencode($studentName) }},%20your%20pending%20college%20fee%20for%20{{ urlencode($def->course->name ?? 'Course') }}%20(Batch%20{{ $def->student->batch }})%20is%20Rs.{{ number_format($def->total_due, 2) }}.%20Please%20deposit%20at%20the%20college%20counter." target="_blank" class="btn btn-xs btn-matrix-tool py-0 px-2 text-success" style="height: 23px; font-size: 10.5px; border-color: #86efac; background: #f0fdf4;" title="Send WhatsApp Reminder">
                                                            <i class="fa fa-whatsapp text-success"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="rowDefaultersEmpty">
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fa fa-check-circle-o fa-3x text-success mb-2"></i><br>
                                                <strong class="text-success" style="font-size: 13px;">Excellent! No outstanding fee defaulters found.</strong>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-right font-weight-bold text-uppercase">Total Outstanding Dues:</td>
                                        <td class="text-right font-weight-bold text-danger" style="font-size: 12.5px;" id="lblTableTotalDefaultersDues">
                                            ₹{{ number_format($totalDefaultersDues, 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>

        </div> <!-- /card -->

    </div>
</div>

<script>
// Tab Switching with URL Query Parameter (?tab=defaulters or ?tab=register, NO # hash)
function switchReportTab(tabKey) {
    if (tabKey === 'defaulters') {
        $('#register-tab').removeClass('active');
        $('#defaulters-tab').addClass('active');
        $('#registerTabContent').removeClass('show active');
        $('#defaultersTabContent').addClass('show active');

        $('#boxMinDuesFilter').show();
        $('#group_rep_receipt_status').hide();
        $('#group_rep_payment_mode').hide();
        $('#group_rep_cashier_id').hide();
    } else {
        tabKey = 'register';
        $('#defaulters-tab').removeClass('active');
        $('#register-tab').addClass('active');
        $('#defaultersTabContent').removeClass('show active');
        $('#registerTabContent').addClass('show active');

        $('#boxMinDuesFilter').hide();
        $('#group_rep_receipt_status').show();
        $('#group_rep_payment_mode').show();
        $('#group_rep_cashier_id').show();
    }

    // Update URL query string cleanly without '#' hash
    var currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('tab', tabKey);
    currentUrl.hash = ''; // Remove any trailing # hash
    window.history.replaceState({ tab: tabKey }, '', currentUrl.toString());

    setTimeout(adjustReportsTableHeight, 50);
}

// Dynamic Table Height calculation (Cloned from structures.blade.php adjustMatrixTableHeight)
function adjustReportsTableHeight() {
    var bottomMargin = 40;
    $('.reports-table-responsive').each(function() {
        var rect = this.getBoundingClientRect();
        var available = window.innerHeight - rect.top - bottomMargin;
        var finalHeight = Math.max(380, Math.floor(available));
        this.style.height = finalHeight + 'px';
        this.style.maxHeight = finalHeight + 'px';
    });
}

$(document).ready(function() {
    // Check if initial URL had hash or query parameter
    if (window.location.hash) {
        var hash = window.location.hash;
        if (hash.indexOf('defaulters') !== -1) {
            switchReportTab('defaulters');
        } else {
            switchReportTab('register');
        }
    } else {
        var params = new URLSearchParams(window.location.search);
        var curTab = params.get('tab');
        if (curTab === 'defaulters') {
            switchReportTab('defaulters');
        } else {
            switchReportTab('register');
        }
    }
    adjustReportsTableHeight();
});

$(window).on('resize scroll', function() {
    adjustReportsTableHeight();
});

// Live Instant Search for Receipts
function filterReceiptsLive(val) {
    var query = (val || '').toLowerCase().trim();
    var visibleCount = 0;
    
    $('.receipt-data-row').each(function() {
        var searchData = $(this).data('search') || '';
        if (!query || searchData.indexOf(query) !== -1) {
            $(this).show();
            visibleCount++;
        } else {
            $(this).hide();
        }
    });
    
    $('#lblReceiptsCount').text(visibleCount);
}

// Live Instant Search for Defaulters
function filterDefaultersLive(val) {
    var query = (val || '').toLowerCase().trim();
    var visibleCount = 0;
    
    $('.defaulter-data-row').each(function() {
        var searchData = $(this).data('search') || '';
        if (!query || searchData.indexOf(query) !== -1) {
            $(this).show();
            visibleCount++;
        } else {
            $(this).hide();
        }
    });
    
    $('#lblDefaultersBadgeCount').text(visibleCount);
}

// Reset Filters
function resetReportsFilter() {
    $('#rep_session_id').val('');
    $('#rep_from_date').val('');
    $('#rep_to_date').val('');
    $('#rep_course_id').val('');
    $('#rep_batch').val('');
    $('#rep_semester_no').val('');
    $('#rep_receipt_status').val('all');
    $('#rep_payment_mode').val('');
    $('#rep_cashier_id').val('');
    $('#rep_min_dues').val('1');
    $('#rep_live_search_reg').val('');
    $('#rep_live_search_def').val('');
    applyReportsFilter();
}

// Apply Filter via AJAX
$('#filterReportsForm').on('submit', function(e) {
    e.preventDefault();
    applyReportsFilter();
});

function applyReportsFilter() {
    var sessionId = $('#rep_session_id').val();
    var fromDate = $('#rep_from_date').val();
    var toDate = $('#rep_to_date').val();
    var courseId = $('#rep_course_id').val();
    var batch = $('#rep_batch').val();
    var semesterNo = $('#rep_semester_no').val();
    var receiptStatus = $('#rep_receipt_status').val() || 'all';
    var paymentMode = $('#rep_payment_mode').val();
    var cashierId = $('#rep_cashier_id').val();
    var minDues = $('#rep_min_dues').val() || 1;

    var btn = $('#btnFilterApply');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Filtering...');

    $('#receiptsTableBody').html('<tr><td colspan="10" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x text-success"></i><br><span class="mt-2 font-weight-bold">Loading records...</span></td></tr>');
    $('#defaultersTableBody').html('<tr><td colspan="9" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x text-danger"></i><br><span class="mt-2 font-weight-bold">Loading records...</span></td></tr>');

    $.ajax({
        url: "{{ route('college.fees.reports') }}",
        type: "GET",
        data: {
            session_id: sessionId,
            from_date: fromDate,
            to_date: toDate,
            course_id: courseId,
            batch: batch,
            semester_no: semesterNo,
            receipt_status: receiptStatus,
            payment_mode: paymentMode,
            cashier_id: cashierId,
            min_dues: minDues
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-filter mr-1"></i> Apply Filter');
            renderReportsData(res);
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fa fa-filter mr-1"></i> Apply Filter');
            if (typeof toastr !== 'undefined') {
                toastr.error('Failed to load report data.');
            }
            $('#receiptsTableBody').html('<tr><td colspan="10" class="text-center text-danger py-4">Error loading receipts.</td></tr>');
            $('#defaultersTableBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Error loading defaulters.</td></tr>');
        }
    });
}

function renderReportsData(res) {
    // 1. Update Tab Badges & Counts
    $('#tabReceiptsBadge').text(res.receipts_count || 0);
    $('#tabDefaultersBadge').text(res.defaulters_count || 0);
    $('#lblReceiptsCount').text(res.receipts_count || 0);
    $('#lblDefaultersBadgeCount').text(res.defaulters_count || 0);

    var totalCollFormatted = '₹' + parseFloat(res.total_collected || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    var totalCancelledFormatted = parseFloat(res.total_cancelled || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    var totalDuesFormatted = '₹' + parseFloat(res.total_defaulters_dues || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });

    $('#lblTotalCollected').html('<i class="fa fa-inr text-success"></i> Active: ' + totalCollFormatted);
    $('#lblTableTotalCollected').text(totalCollFormatted);
    $('#lblCancelledCount').text(res.cancelled_count || 0);
    $('#lblCancelledAmt').text(totalCancelledFormatted);

    $('#lblDefaultersTotalDues').html('<i class="fa fa-inr text-danger"></i> Total Dues: ' + totalDuesFormatted);
    $('#lblTableTotalDefaultersDues').text(totalDuesFormatted);

    // 2. Render Receipts Table (Comprehensive listing with status pills & formatting)
    var rBody = '';
    if (!res.receipts || res.receipts.length === 0) {
        rBody = '<tr id="rowReceiptsEmpty"><td colspan="10" class="text-center py-5 text-muted"><i class="fa fa-file-text-o fa-2x text-muted mb-2"></i><br><strong>No fee receipts match your selected filter criteria.</strong></td></tr>';
    } else {
        res.receipts.forEach(function(r, idx) {
            var isCancelled = !!r.is_cancelled;
            var modeKey = (r.payment_mode || 'cash').toLowerCase().replace(/\s+/g, '');
            var txnHtml = r.transaction_id ? '<div class="text-muted" style="font-size: 9.5px; line-height: 1.1; margin-top: 2px;">Txn: ' + r.transaction_id + '</div>' : '';
            var searchData = (r.receipt_no + ' ' + (isCancelled ? 'cancelled void' : 'paid active') + ' ' + r.student_name + ' ' + (r.admissionNo || '') + ' ' + (r.mobile || '') + ' ' + (r.course_name || '') + ' ' + (r.batch || '')).toLowerCase();

            var statusBadge = isCancelled 
                ? '<span class="badge-status-cancelled">CANCELLED</span>'
                : '<span class="badge-status-paid">PAID</span>';

            var receiptLinkColor = isCancelled ? '#dc2626' : '#0284c7';
            var rowClass = isCancelled ? 'receipt-data-row receipt-row-cancelled' : 'receipt-data-row';

            var amountCell = isCancelled
                ? '<span style="text-decoration: line-through; color: #94a3b8; font-weight: 600;">₹' + parseFloat(r.amount_paid).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</span><small class="text-danger font-weight-bold d-block" style="font-size: 9.5px; line-height: 1.1;">Void / Reverted</small>'
                : '<span class="font-weight-bold text-success" style="font-size: 12.5px;">₹' + parseFloat(r.amount_paid).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</span>';

            rBody += '<tr class="' + rowClass + '" data-status="' + (isCancelled ? 'cancelled' : 'paid') + '" data-search="' + searchData + '">' +
                '<td class="col-matrix-idx text-muted font-weight-bold">' + (idx + 1) + '</td>' +
                '<td><a href="' + r.receipt_url + '" target="_blank" class="font-weight-bold" style="color: ' + receiptLinkColor + '; text-decoration: none;">' + r.receipt_no + '</a></td>' +
                '<td class="text-center">' + statusBadge + '</td>' +
                '<td><span>' + r.payment_date + '</span></td>' +
                '<td class="col-matrix-student">' +
                    '<div class="student-cell-name">' + (r.student_name || 'N/A') + '</div>' +
                    '<div class="student-cell-sub"><strong>Adm:</strong> ' + (r.admissionNo || 'N/A') + (r.mobile ? ' &bull; ' + r.mobile : '') + '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex align-items-center gap-1 flex-wrap">' +
                        '<span class="badge-tag-course">' + (r.course_name || 'Course') + '</span>' +
                        '<span class="badge-tag-batch">Batch ' + (r.batch || 'N/A') + '</span>' +
                        '<span class="badge-tag-sem">Sem ' + r.semester_no + '</span>' +
                    '</div>' +
                '</td>' +
                '<td><span class="badge-mode-pill mode-' + modeKey + '">' + r.payment_mode + '</span>' + txnHtml + '</td>' +
                '<td class="col-matrix-amount">' + amountCell + '</td>' +
                '<td><span>' + (r.cashier_name || 'System / Admin') + '</span></td>' +
                '<td class="col-matrix-action">' +
                    '<a href="' + r.receipt_url + '" target="_blank" class="btn btn-xs btn-matrix-tool py-0 px-2" style="height: 23px; font-size: 10.5px;" title="Print Receipt">' +
                        '<i class="fa fa-print text-primary mr-1"></i> Print' +
                    '</a>' +
                '</td>' +
            '</tr>';
        });
    }
    $('#receiptsTableBody').html(rBody);

    // 3. Render Defaulters Table
    var dBody = '';
    if (!res.defaulters || res.defaulters.length === 0) {
        dBody = '<tr id="rowDefaultersEmpty"><td colspan="9" class="text-center py-5 text-muted"><i class="fa fa-check-circle-o fa-3x text-success mb-2"></i><br><strong class="text-success" style="font-size: 13px;">Excellent! No outstanding fee defaulters found.</strong></td></tr>';
    } else {
        res.defaulters.forEach(function(def, idx) {
            var mobileHtml = def.mobile 
                ? '<a href="tel:' + def.mobile + '" style="color: #0284c7; font-weight: 700; text-decoration: none;"><i class="fa fa-phone mr-1"></i>' + def.mobile + '</a>'
                : '<span class="text-muted">N/A</span>';

            var waBtn = def.mobile
                ? '<a href="https://api.whatsapp.com/send?phone=91' + def.mobile + '&text=Dear%20Student%20' + encodeURIComponent(def.student_name) + ',%20your%20pending%20college%20fee%20for%20' + encodeURIComponent(def.course_name) + '%20(Batch%20' + def.batch + ')%20is%20Rs.' + parseFloat(def.total_due).toFixed(2) + '.%20Please%20deposit%20at%20the%20college%20counter." target="_blank" class="btn btn-xs btn-matrix-tool py-0 px-2 text-success" style="height: 23px; font-size: 10.5px; border-color: #86efac; background: #f0fdf4;" title="Send WhatsApp Reminder"><i class="fa fa-whatsapp text-success"></i></a>'
                : '';

            var searchData = (def.student_name + ' ' + (def.admissionNo || '') + ' ' + (def.mobile || '') + ' ' + (def.course_name || '') + ' ' + (def.batch || '')).toLowerCase();

            dBody += '<tr class="defaulter-data-row" data-search="' + searchData + '">' +
                '<td class="col-matrix-idx text-muted font-weight-bold">' + (idx + 1) + '</td>' +
                '<td><strong class="text-dark">' + (def.admissionNo || 'N/A') + '</strong></td>' +
                '<td class="col-matrix-student">' +
                    '<div class="student-cell-name">' + (def.student_name || 'N/A') + '</div>' +
                    '<div class="student-cell-sub"><strong>F:</strong> ' + (def.father_name || 'N/A') + '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex align-items-center gap-1 flex-wrap">' +
                        '<span class="badge-tag-course">' + (def.course_name || 'Course') + '</span>' +
                        '<span class="badge-tag-batch">Batch ' + (def.batch || 'N/A') + '</span>' +
                        '<span class="badge-tag-sem">Sem ' + def.current_sem + '</span>' +
                    '</div>' +
                '</td>' +
                '<td>' + mobileHtml + '</td>' +
                '<td class="col-matrix-amount text-muted">₹' + parseFloat(def.total_demand || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</td>' +
                '<td class="col-matrix-amount text-success">₹' + parseFloat(def.total_paid || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</td>' +
                '<td class="col-matrix-amount font-weight-bold text-danger" style="font-size: 12.5px;">₹' + parseFloat(def.total_due || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + '</td>' +
                '<td class="col-matrix-action">' +
                    '<div class="d-inline-flex gap-1">' +
                        '<a href="{{ route('college.fees.structures') }}" class="btn btn-xs btn-matrix-tool py-0 px-2 text-success font-weight-bold" style="height: 23px; font-size: 10.5px; border-color: #86efac; background: #f0fdf4;" title="Open Fee Matrix">' +
                            '<i class="fa fa-calculator text-success mr-1"></i> Collect' +
                        '</a>' +
                        waBtn +
                    '</div>' +
                '</td>' +
            '</tr>';
        });
    }
    $('#defaultersTableBody').html(dBody);

    filterReceiptsLive($('#rep_live_search_reg').val());
    filterDefaultersLive($('#rep_live_search_def').val());
    setTimeout(adjustReportsTableHeight, 50);
}

// Client-Side CSV Export (Generates fully formatted CSV with status, headers & subjects)
function exportActiveTabToCSV() {
    var isReceipts = $('#registerTabContent').hasClass('show') && $('#registerTabContent').hasClass('active');
    var todayStr = new Date().toISOString().slice(0, 10);
    var filename = (isReceipts ? 'College_Fee_Collection_Register_' : 'College_Fee_Defaulters_List_') + todayStr + '.csv';

    var csvRows = [];

    if (isReceipts) {
        // Receipts Header with Status
        csvRows.push([
            '"#"',
            '"Receipt No"',
            '"Status"',
            '"Payment Date"',
            '"Student Name"',
            '"Admission No"',
            '"Mobile No"',
            '"Course / Subject"',
            '"Batch"',
            '"Semester"',
            '"Payment Mode"',
            '"Transaction ID"',
            '"Amount Paid (INR)"',
            '"Cashier / Handled By"'
        ].join(','));

        $('#tblReceipts tbody tr.receipt-data-row').each(function(idx) {
            if ($(this).is(':visible')) {
                var cols = $(this).find('td');
                var receiptNo = cols.eq(1).text().trim();
                var status = cols.eq(2).text().trim();
                var paymentDate = cols.eq(3).text().trim();
                var studentName = cols.eq(4).find('.student-cell-name').text().trim();
                var studentDetails = cols.eq(4).find('.student-cell-sub').text().trim();
                var admMatch = studentDetails.match(/Adm:\s*([^\s•]+)/);
                var admNo = admMatch ? admMatch[1] : '';
                var mobMatch = studentDetails.match(/•\s*([0-9]+)/);
                var mob = mobMatch ? mobMatch[1] : '';

                var courseName = cols.eq(5).find('.badge-tag-course').text().trim();
                var batchName = cols.eq(5).find('.badge-tag-batch').text().replace('Batch', '').trim();
                var semName = cols.eq(5).find('.badge-tag-sem').text().replace('Sem', '').trim();

                var mode = cols.eq(6).find('.badge-mode-pill').text().trim();
                var txnText = cols.eq(6).find('.text-muted').text().replace('Txn:', '').trim();
                var amount = cols.eq(7).text().replace('₹', '').replace('Void / Reverted', '').replace(/,/g, '').trim();
                var cashier = cols.eq(8).text().trim();

                csvRows.push([
                    '"' + (idx + 1) + '"',
                    '"' + receiptNo.replace(/"/g, '""') + '"',
                    '"' + status.replace(/"/g, '""') + '"',
                    '"' + paymentDate.replace(/"/g, '""') + '"',
                    '"' + studentName.replace(/"/g, '""') + '"',
                    '"' + admNo.replace(/"/g, '""') + '"',
                    '"' + mob.replace(/"/g, '""') + '"',
                    '"' + courseName.replace(/"/g, '""') + '"',
                    '"' + batchName.replace(/"/g, '""') + '"',
                    '"' + semName.replace(/"/g, '""') + '"',
                    '"' + mode.replace(/"/g, '""') + '"',
                    '"' + txnText.replace(/"/g, '""') + '"',
                    '"' + amount.replace(/"/g, '""') + '"',
                    '"' + cashier.replace(/"/g, '""') + '"'
                ].join(','));
            }
        });
    } else {
        // Defaulters Header
        csvRows.push([
            '"#"',
            '"Admission No"',
            '"Student Name"',
            '"Father Name"',
            '"Course / Subject"',
            '"Batch"',
            '"Semester"',
            '"Mobile No"',
            '"Total Demand (INR)"',
            '"Total Paid (INR)"',
            '"Pending Dues (INR)"'
        ].join(','));

        $('#tblDefaulters tbody tr.defaulter-data-row').each(function(idx) {
            if ($(this).is(':visible')) {
                var cols = $(this).find('td');
                var admNo = cols.eq(1).text().trim();
                var studentName = cols.eq(2).find('.student-cell-name').text().trim();
                var fatherName = cols.eq(2).find('.student-cell-sub').text().replace('F:', '').trim();

                var courseName = cols.eq(3).find('.badge-tag-course').text().trim();
                var batchName = cols.eq(3).find('.badge-tag-batch').text().replace('Batch', '').trim();
                var semName = cols.eq(3).find('.badge-tag-sem').text().replace('Sem', '').trim();

                var mob = cols.eq(4).text().trim();
                var demand = cols.eq(5).text().replace('₹', '').replace(/,/g, '').trim();
                var paid = cols.eq(6).text().replace('₹', '').replace(/,/g, '').trim();
                var due = cols.eq(7).text().replace('₹', '').replace(/,/g, '').trim();

                csvRows.push([
                    '"' + (idx + 1) + '"',
                    '"' + admNo.replace(/"/g, '""') + '"',
                    '"' + studentName.replace(/"/g, '""') + '"',
                    '"' + fatherName.replace(/"/g, '""') + '"',
                    '"' + courseName.replace(/"/g, '""') + '"',
                    '"' + batchName.replace(/"/g, '""') + '"',
                    '"' + semName.replace(/"/g, '""') + '"',
                    '"' + mob.replace(/"/g, '""') + '"',
                    '"' + demand.replace(/"/g, '""') + '"',
                    '"' + paid.replace(/"/g, '""') + '"',
                    '"' + due.replace(/"/g, '""') + '"'
                ].join(','));
            }
        });
    }

    var csvString = '\uFEFF' + csvRows.join('\r\n');
    var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>

@endsection
