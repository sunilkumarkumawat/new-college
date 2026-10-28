@extends('layout.app')
@section('content')

@php
    $allCourses = $structuredCourses ?? [];
    $totalCoursesCount = count($allCourses);
    $totalSemestersCount = 0;
    $totalConfiguredHeads = 0;
    $grandTotalFees = 0;

    foreach($allCourses as $c) {
        $totalSemestersCount += count($c['semesters'] ?? []);
        $grandTotalFees += (float)($c['total_course_fee'] ?? 0);
        foreach($c['semesters'] ?? [] as $s) {
            $totalConfiguredHeads += count($s['heads'] ?? []);
        }
    }
@endphp

<style>
/* ========================================================
   EXCEL SPREADSHEET THEME (Matching https://girraj.test/fees_assign)
   ======================================================== */
.content-wrapper {
    background-color: #f4f6f9;
}

/* Card Container */
.fees-assign-page-card {
    margin-bottom: 0 !important;
    border: 1px solid #9aa7b1 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

/* Excel Green Ribbon Header */
.fees-assign-page-card > .card-header::after,
.fees-assign-page-card > .card-header::before {
    display: none !important;
    content: none !important;
}
.fees-assign-page-card > .card-header {
    min-height: 44px;
    background: #217346 !important;
    border-radius: 0 !important;
    border-bottom: 1px solid #185c37 !important;
    padding: 6px 12px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    color: #ffffff !important;
}
.fees-assign-page-card > .card-header .card-title {
    color: #ffffff !important;
    font-size: 14.5px !important;
    letter-spacing: .2px;
    font-weight: 700 !important;
    margin: 0 !important;
    float: none !important;
    display: flex !important;
    align-items: center;
}
.fees-assign-page-card > .card-header .card-tools {
    margin-left: auto !important;
    margin-right: 0 !important;
    float: none !important;
    position: static !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
}

/* Header Buttons Styling & Safe Hover States */
.btn-header-success {
    background: #16a34a !important;
    border: 1px solid #15803d !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    padding: 4px 10px !important;
    transition: all 0.15s ease-in-out;
}
.btn-header-success:hover {
    background: #15803d !important;
    border-color: #166534 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.btn-header-light {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    padding: 4px 10px !important;
    transition: all 0.15s ease-in-out;
}
.btn-header-light:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
}

.fees-assign-page-card .card-body {
    padding: 0 !important;
}

/* ========================================================
   UNIFIED EXCEL FILTER TOOLBAR (Compact, Single-Bar Layout)
   ======================================================== */
.unified-filter-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
    padding: 7px 10px;
}
.toolbar-label {
    font-size: 10.5px;
    font-weight: 700;
    margin-bottom: 2px;
    color: #334155;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    line-height: 1.1;
}
.toolbar-control {
    height: 29px !important;
    min-height: 29px !important;
    padding: 3px 6px !important;
    font-size: 11.5px !important;
    color: #172033 !important;
    background: #ffffff !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
    width: 100%;
}
.toolbar-control:focus {
    border-color: #217346 !important;
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;
}

/* Reset Button Hover State */
.btn-filter-reset {
    height: 29px !important;
    padding: 3px 10px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
    border: 1px solid #94a3b8 !important;
    color: #475569 !important;
    border-radius: 2px !important;
    transition: all 0.15s ease-in-out;
}
.btn-filter-reset:hover {
    background: #e2e8f0 !important;
    border-color: #64748b !important;
    color: #0f172a !important;
}

/* High-Density Excel Spreadsheet Table */
#fees_sheet_scroll {
    overflow: auto;
    max-height: calc(100vh - 190px);
    scrollbar-gutter: stable;
    border: 0 !important;
    background: #fff;
}
#fees_spreadsheet_table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100% !important;
    min-width: 1180px !important;
    table-layout: fixed;
}
#fees_spreadsheet_table > thead > tr:first-child > th {
    position: sticky;
    top: 0;
    z-index: 15;
    background: #d9eaf7 !important;
    color: #152536 !important;
    border-color: #8fa7b8 !important;
    font-size: 11.5px !important;
    height: 33px;
    font-weight: 700 !important;
    padding: 4px 6px !important;
    vertical-align: middle !important;
    border-right: 1px solid #8fa7b8;
    border-bottom: 1px solid #8fa7b8;
}

/* Spreadsheet Body Cells */
#fees_spreadsheet_table > tbody > tr > td {
    background: #fff;
    border-color: #c6cdd3 !important;
    border-right: 1px solid #c6cdd3;
    border-bottom: 1px solid #c6cdd3;
    padding: 4px 6px !important;
    font-size: 11.5px !important;
    vertical-align: middle !important;
    line-height: 1.25;
}
#fees_spreadsheet_table > tbody > tr:nth-child(even) > td {
    background: #fafafa;
}
#fees_spreadsheet_table > tbody > tr:hover > td {
    background: #fffde7 !important; /* Excel Yellow Hover */
}

/* Course Group Header Rows */
.row-course-group > td {
    background: #eef4f9 !important;
    color: #0f2c4d !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    padding: 5px 8px !important;
    border-top: 2px solid #8fa7b8 !important;
    border-bottom: 1px solid #8fa7b8 !important;
}

/* Sticky Action Column */
#fees_spreadsheet_table > thead > tr > th:last-child,
#fees_spreadsheet_table > tbody > tr > td:last-child,
#fees_spreadsheet_table > tfoot > tr > td:last-child {
    position: sticky !important;
    right: 0;
    min-width: 130px;
    width: 130px;
    box-shadow: -3px 0 5px rgba(0, 35, 67, 0.08);
}
#fees_spreadsheet_table > thead > tr:first-child > th:last-child {
    z-index: 22 !important;
    background: #d9eaf7 !important;
}
#fees_spreadsheet_table > tbody > tr > td:last-child {
    z-index: 8;
    background: #fff !important;
}
#fees_spreadsheet_table > tbody > tr:nth-child(even) > td:last-child {
    background: #fafafa !important;
}
#fees_spreadsheet_table > tbody > tr:hover > td:last-child {
    background: #fffde7 !important;
}
#fees_spreadsheet_table > tfoot > tr > td:last-child {
    z-index: 20 !important;
    background: #e2f0d9 !important;
}

/* Spreadsheet Sticky Footer */
#fees_spreadsheet_table tfoot,
#tfoot_summary_row,
#tfoot_summary_row td {
    position: sticky;
    bottom: 0;
    z-index: 18;
    background-color: #e2f0d9 !important;
    color: #1f2937 !important;
    font-size: 11px !important;
    font-weight: 700;
    padding: 5px 8px !important;
    border-color: #9eb58f !important;
    border-top: 2px solid #9eb58f !important;
}

/* Fee Head Chips (Spreadsheet Badge Style from fees_assign) */
.fee-chip {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 10.5px;
    font-weight: 600;
    line-height: 1.3;
}
.fee-chip-compulsory {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #0284c7;
}
.fee-chip-optional {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #f59e0b;
}
.fee-chip-library {
    background-color: #f3e8ff;
    color: #6b21a8;
    border: 1px solid #a855f7;
}
.fee-chip-other {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #94a3b8;
}

/* ========================================================
   HIGH-CONTRAST ACTION BUTTONS & SAFE HOVER STATES
   ======================================================== */
.btn-sheet-action {
    height: 24px;
    padding: 2px 7px !important;
    font-size: 11px !important;
    border-radius: 2px !important;
    line-height: 1.2;
    font-weight: 700 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease-in-out;
}

/* Edit Button */
.btn-sheet-edit {
    color: #0369a1 !important;
    border: 1px solid #0284c7 !important;
    background: #e0f2fe !important;
}
.btn-sheet-edit:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
    box-shadow: 0 1px 3px rgba(2, 132, 199, 0.3);
}

/* Copy Button */
.btn-sheet-copy {
    color: #334155 !important;
    border: 1px solid #94a3b8 !important;
    background: #f1f5f9 !important;
}
.btn-sheet-copy:hover {
    background: #475569 !important;
    color: #ffffff !important;
    border-color: #475569 !important;
    box-shadow: 0 1px 3px rgba(71, 85, 105, 0.3);
}

/* Delete Button */
.btn-sheet-del {
    color: #dc2626 !important;
    border: 1px solid #f87171 !important;
    background: #fee2e2 !important;
}
.btn-sheet-del:hover {
    background: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
}

/* Course Group Inline Add Head Button */
.btn-course-add {
    color: #166534 !important;
    border: 1px solid #22c55e !important;
    background: #e7f5ec !important;
}
.btn-course-add:hover {
    background: #16a34a !important;
    color: #ffffff !important;
    border-color: #16a34a !important;
    box-shadow: 0 1px 3px rgba(22, 163, 74, 0.3);
}

/* Direct Assign to Students Button */
.btn-sheet-assign {
    color: #166534 !important;
    border: 1px solid #16a34a !important;
    background: #f0fdf4 !important;
}
.btn-sheet-assign:hover {
    background: #16a34a !important;
    color: #ffffff !important;
    border-color: #15803d !important;
    box-shadow: 0 1px 3px rgba(22, 163, 74, 0.3);
}

/* ========================================================
   MODAL THEME (Matching Promote Students Excel layout)
   ======================================================== */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 2px !important;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.22) !important;
}
.modal-header {
    padding: 8px 12px !important;
    background: #f1f4f6 !important;
    color: #1e2d39 !important;
    border-bottom: 1px solid #aebbc5 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.modal-title {
    margin: 0 !important;
    color: #1e2d39 !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    letter-spacing: 0.2px;
}
.modal-title i {
    color: #217346 !important;
    font-size: 14px !important;
    margin-right: 6px;
}
.modal-title i.text-danger {
    color: #dc2626 !important;
}
.modal-header .close,
.modal-header .btn-close-modal {
    margin: 0 !important;
    padding: 2px 6px !important;
    color: #344450 !important;
    opacity: 0.9 !important;
    font-size: 20px !important;
    line-height: 1 !important;
    background: transparent !important;
    border: 0 !important;
    cursor: pointer !important;
    text-shadow: none !important;
    transition: all 0.15s ease-in-out;
}
.modal-header .close:hover,
.modal-header .btn-close-modal:hover {
    color: #000000 !important;
    opacity: 1 !important;
}
.modal-body {
    padding: 12px 14px !important;
    background: #ffffff !important;
}

/* Chips & Badges from promote page */
.sheet-chip {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 2px;
    font-size: 10.5px;
    font-weight: 600;
    line-height: 1.2;
}
.sheet-chip-course {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.sheet-chip-batch {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}

/* Input Panel for Modals (Replaces buggy bg-light) */
.sheet-input-panel {
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 3px !important;
    color: #1e293b !important;
}

/* Modal Context Callout Box */
.modal-context-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    margin-bottom: 10px;
    border: 1px solid #aebbc5;
    background: #e7eff5;
    font-size: 11.5px;
    color: #1e293b;
    border-radius: 2px;
}

/* Modal Labels & Inputs */
.modal-label {
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 3px;
    color: #1e293b;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.modal-control {
    height: 30px !important;
    min-height: 30px !important;
    padding: 3px 7px !important;
    font-size: 12px !important;
    color: #172033 !important;
    background: #ffffff !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
}
.modal-control:focus {
    border-color: #217346 !important;
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;
}

.modal-footer {
    display: flex !important;
    align-items: center;
    justify-content: flex-end !important;
    gap: 7px;
    padding: 7px 12px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #aebbc5 !important;
}
.btn-modal-cancel {
    height: 30px !important;
    margin: 0 !important;
    padding: 3px 12px !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #ffffff !important;
    color: #52616d !important;
    border: 1px solid #9eacb7 !important;
    transition: all 0.15s ease-in-out;
}
.btn-modal-cancel:hover {
    background: #e9edef !important;
    color: #1e293b !important;
    border-color: #7b8b98 !important;
}

.btn-modal-submit {
    height: 30px !important;
    margin: 0 !important;
    padding: 3px 14px !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #217346 !important;
    border: 1px solid #185c37 !important;
    color: #ffffff !important;
    transition: all 0.15s ease-in-out;
}
.btn-modal-submit:hover {
    background: #195b37 !important;
    border-color: #134629 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.3);
}

.btn-modal-danger {
    height: 30px !important;
    margin: 0 !important;
    padding: 3px 14px !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #dc2626 !important;
    border: 1px solid #b91c1c !important;
    color: #ffffff !important;
    transition: all 0.15s ease-in-out;
}
.btn-modal-danger:hover {
    background: #b91c1c !important;
    border-color: #991b1b !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
}
</style>

<div class="content-wrapper">
    <section class="content pt-2">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    
                    <div class="card fees-assign-page-card">
                        
                        <!-- 1. Card Header: Excel Ribbon Theme (#217346) -->
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa fa-table mr-1"></i> Fees Structure Master Spreadsheet
                            </h3>
                            <div class="card-tools d-flex align-items-center" style="gap: 5px;">
                                <button type="button" class="btn btn-header-success" onclick="openAddModalGlobal()">
                                    <i class="fa fa-plus-circle mr-1"></i> + Add Fee Head
                                </button>
                                <button type="button" class="btn btn-header-light" data-toggle="modal" data-target="#modalFeeLibrary">
                                    <i class="fa fa-book mr-1 text-primary"></i> Master Heads Library
                                </button>
                                <button type="button" class="btn btn-header-light" onclick="openCopyModalGlobal()">
                                    <i class="fa fa-clone mr-1 text-info"></i> Clone / Copy Heads
                                </button>
                                <a href="javascript:void(0)" onclick="goToFeesAssignSheet()" class="btn btn-header-light" title="Go to Fees Assign Sheet with selected filters">
                                    <i class="fa fa-users mr-1 text-success"></i> Fees Assign &rarr;
                                </a>
                                <a href="{{ url('fee_dashboard') }}" class="btn btn-header-light" title="Back to Dashboard">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <!-- 2. Combined Single-Bar Filter & Search Strip -->
                        <div class="unified-filter-toolbar">
                            <form method="GET" action="{{ url('feesGroup') }}" id="spreadsheetFilterForm" class="row align-items-end" style="margin-left: -3px; margin-right: -3px;">
                                
                                <!-- 1. Course Filter Dropdown -->
                                <div class="col-lg-3 col-md-4 col-sm-6 px-1 mb-1 mb-lg-0">
                                    <label class="toolbar-label">Filter Course</label>
                                    <select id="grid_filter_course" class="form-control toolbar-control font-weight-bold" onchange="onFilterCourseChange(this.value)">
                                        <option value="">-- Select Course --</option>
                                        <option value="ALL" {{ ($search['course_id'] ?? '') === 'ALL' ? 'selected' : '' }}>-- All Courses ({{ count($allCourses) }}) --</option>
                                        @foreach($allCourses as $c)
                                            <option value="{{ $c['course_id'] }}" {{ ($search['course_id'] ?? '') == $c['course_id'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Semester Filter Dropdown (Dynamic based on selected course) -->
                                <div class="col-lg-2 col-md-4 col-sm-6 px-1 mb-1 mb-lg-0">
                                    <label class="toolbar-label">Filter Semester</label>
                                    <select id="grid_filter_semester" class="form-control toolbar-control" onchange="filterSpreadsheetRows()">
                                        <option value="">All Semesters</option>
                                        @php $uniqueSemNames = []; @endphp
                                        @foreach($allCourses as $c)
                                            @foreach($c['semesters'] as $s)
                                                @if(!in_array($s['name'], $uniqueSemNames))
                                                    @php $uniqueSemNames[] = $s['name']; @endphp
                                                    <option value="{{ $s['name'] }}">{{ $s['name'] }}</option>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 3. Admission Batch Filter -->
                                <div class="col-lg-2 col-md-4 col-sm-6 px-1 mb-1 mb-lg-0">
                                    <label class="toolbar-label">Admission Batch</label>
                                    <select id="grid_filter_batch" class="form-control toolbar-control font-weight-bold" onchange="filterSpreadsheetRows()">
                                        <option value="">-- Select Batch --</option>
                                        <option value="ALL" {{ ($search['batch'] ?? '') === 'ALL' ? 'selected' : '' }}>All Batches</option>
                                        @foreach($availableBatches ?? ['2027', '2026', '2025', '2024'] as $b)
                                            <option value="{{ $b }}" {{ ($search['batch'] ?? '') == $b ? 'selected' : '' }}>Batch {{ $b }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 4. Fee Head Category Filter -->
                                <div class="col-lg-2 col-md-3 col-sm-6 px-1 mb-1 mb-lg-0">
                                    <label class="toolbar-label">Category</label>
                                    <select id="grid_filter_category" class="form-control toolbar-control" onchange="filterSpreadsheetRows()">
                                        <option value="">All</option>
                                        <option value="compulsory">Compulsory</option>
                                        <option value="optional">Optional</option>
                                        <option value="library">Library</option>
                                        <option value="hostel">Hostel</option>
                                        <option value="examination">Examination</option>
                                    </select>
                                </div>

                                <!-- 5. Live Search Input -->
                                <div class="col-lg-2 col-md-6 col-sm-6 px-1 mb-1 mb-lg-0">
                                    <label class="toolbar-label">Live Search</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="grid_filter_keyword" class="form-control toolbar-control" placeholder="Search head, course..." onkeyup="filterSpreadsheetRows()">
                                        <div class="input-group-append">
                                            <button class="btn btn-light border px-2" type="button" onclick="$('#grid_filter_keyword').val(''); filterSpreadsheetRows();" title="Clear Search">
                                                <i class="fa fa-times text-muted"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Reset Filters Button -->
                                <div class="col-lg-1 col-md-3 col-sm-6 px-1 text-right">
                                    <button type="button" class="btn btn-filter-reset btn-block" onclick="resetAllFilters()" title="Reset All Filters">
                                        <i class="fa fa-refresh mr-1"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- 3. High-Density Excel Spreadsheet Table (Single Clean Header) -->
                        <div class="table-responsive" id="fees_sheet_scroll">
                            <table class="table table-bordered table-striped table-hover table-sm mb-0" id="fees_spreadsheet_table">
                                <thead>
                                    <tr>
                                        <th style="width: 38px; text-align: center;">#</th>
                                        <th style="width: 170px;">Course Name</th>
                                        <th style="width: 135px;">Class / Semester</th>
                                        <th style="width: 85px; text-align: center;">Batch</th>
                                        <th style="width: 190px;">Fee Head Name</th>
                                        <th style="width: 110px; text-align: center;">Category / Type</th>
                                        <th style="width: 105px; text-align: right;">Amount (₹)</th>
                                        <th style="width: 105px; text-align: right;"><i class="fa fa-male text-primary mr-1"></i>Boys (₹)</th>
                                        <th style="width: 105px; text-align: right;"><i class="fa fa-female text-danger mr-1"></i>Girls (₹)</th>
                                        <th style="width: 95px; text-align: center;">Due Date</th>
                                        <th style="width: 125px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fees_sheet_tbody">
                                    <!-- Initial State Prompt Row -->
                                    <tr id="row_initial_state" class="d-none">
                                        <td colspan="11" class="text-center py-5 text-muted" style="background: #fafbfc;">
                                            <div class="py-4">
                                                <i class="fa fa-filter fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                                                <h5 class="font-weight-bold text-dark mb-1">Select Filters to View Fees Structure</h5>
                                                <p class="small text-muted mb-0">Please select a <strong>Course</strong> or <strong>Admission Batch</strong> from the filter bar above to view fee heads and semester structures.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- No Match State Row -->
                                    <tr id="row_no_match_state" class="d-none">
                                        <td colspan="11" class="text-center py-5 text-muted" style="background: #fafbfc;">
                                            <div class="py-4">
                                                <i class="fa fa-search fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
                                                <h5 class="font-weight-bold text-dark mb-1">No Matching Fee Structure Found</h5>
                                                <p class="small text-muted mb-0">No courses or semesters matched your selected filter criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $rowGlobalIndex = 1; @endphp
                                    @forelse($allCourses as $course)
                                        <!-- Course Section Banner Row -->
                                        <tr class="row-course-group" data-course-id="{{ $course['course_id'] }}">
                                            <td colspan="11">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="fa fa-graduation-cap text-primary mr-1"></i>
                                                        <span class="text-dark">{{ $course['name'] }}</span>
                                                        @if(!empty($course['duration']))
                                                            <span class="badge badge-light border text-muted ml-1" style="font-size: 10.5px;">{{ $course['duration'] }}</span>
                                                        @endif
                                                        <span class="badge badge-light border text-secondary ml-1" style="font-size: 10.5px;">
                                                            {{ count($course['semesters']) }} Semesters
                                                        </span>
                                                    </div>
                                                    <div class="d-flex align-items-center" style="gap: 6px;">
                                                        <span class="text-muted small">Course Total:</span>
                                                        <span class="badge badge-light border text-success font-weight-bold course-total-amount" id="course_total_{{ $course['course_id'] }}" style="font-size: 12px;">
                                                            ₹{{ number_format($course['total_course_fee'], 2) }}
                                                        </span>
                                                        <button type="button" class="btn btn-sheet-action btn-course-add" 
                                                                onclick="openAddModalForCourse('{{ $course['course_id'] }}', '{{ addslashes($course['name']) }}')">
                                                            <i class="fa fa-plus mr-1"></i> Add Head
                                                        </button>
                                                        <button type="button" class="btn btn-sheet-action btn-sheet-assign" 
                                                                onclick="assignCourseDirect('{{ $course['course_id'] }}')" 
                                                                title="Open Fees Assign for this course">
                                                            <i class="fa fa-user-plus mr-1"></i> Assign to Students &rarr;
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Semesters & Heads Rows -->
                                        @if(count($course['semesters']) > 0)
                                            @foreach($course['semesters'] as $sem)
                                                @php
                                                    $hasHeads = count($sem['heads']) > 0;
                                                @endphp
                                                @if($hasHeads)
                                                    @foreach($sem['heads'] as $head)
                                                        @php
                                                            $cat = strtolower($head['category'] ?? 'compulsory');
                                                            $chipClass = 'fee-chip-compulsory';
                                                            if(str_contains($cat, 'option')) $chipClass = 'fee-chip-optional';
                                                            elseif(str_contains($cat, 'lib')) $chipClass = 'fee-chip-library';
                                                            elseif(str_contains($cat, 'hostel')) $chipClass = 'fee-chip-other';
                                                        @endphp
                                                        <tr class="sheet-data-row" 
                                                            id="row_master_{{ $head['fees_master_id'] }}"
                                                            data-course-id="{{ $course['course_id'] }}"
                                                            data-course-name="{{ strtolower($course['name']) }}"
                                                            data-semester-id="{{ $sem['class_type_id'] }}"
                                                            data-semester-name="{{ strtolower($sem['name']) }}"
                                                            data-batch="{{ strtolower($head['batch'] ?? '2026') }}"
                                                            data-head-name="{{ strtolower($head['head_name']) }}"
                                                            data-category="{{ $cat }}"
                                                            data-amount="{{ $head['amount'] }}"
                                                            data-boys-amount="{{ $head['boys_amount'] ?? $head['amount'] }}"
                                                            data-girls-amount="{{ $head['girls_amount'] ?? $head['amount'] }}">
                                                            
                                                            <td class="text-center text-muted sheet-row-num">{{ $rowGlobalIndex++ }}</td>
                                                            <td>
                                                                <strong class="text-dark">{{ $course['name'] }}</strong>
                                                            </td>
                                                            <td>
                                                                <i class="fa fa-bookmark-o text-muted mr-1"></i>{{ $sem['name'] }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="sheet-chip sheet-chip-batch">Batch {{ $head['batch'] ?? '2026' }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="font-weight-bold text-dark">{{ $head['head_name'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="fee-chip {{ $chipClass }} text-capitalize">
                                                                    {{ $head['category'] ?? 'Compulsory' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-right font-weight-bold text-dark sheet-col-amount">
                                                                ₹{{ number_format($head['amount'], 2) }}
                                                            </td>
                                                            <td class="text-right font-weight-bold text-primary sheet-col-boys-amount">
                                                                ₹{{ number_format($head['boys_amount'] ?? $head['amount'], 2) }}
                                                            </td>
                                                            <td class="text-right font-weight-bold text-danger sheet-col-girls-amount">
                                                                ₹{{ number_format($head['girls_amount'] ?? $head['amount'], 2) }}
                                                            </td>
                                                            <td class="text-center text-muted small">
                                                                {{ $head['due_date_formatted'] ?? '—' }}
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center" style="gap: 3px;">
                                                                    <button type="button" class="btn btn-sheet-action btn-sheet-assign" 
                                                                            onclick="assignSemesterDirect('{{ $course['course_id'] }}', '{{ $sem['class_type_id'] }}')" 
                                                                            title="Assign this semester's fee heads to students">
                                                                        <i class="fa fa-users"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sheet-action btn-sheet-edit" 
                                                                            onclick='openEditFeeHeadModal(@json($head), "{{ $sem['class_type_id'] }}", "{{ $course['course_id'] }}")' 
                                                                            title="Edit Amount & Due Date">
                                                                        <i class="fa fa-pencil"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sheet-action btn-sheet-copy" 
                                                                            onclick="openCopyModal('{{ $course['course_id'] }}', '{{ $sem['class_type_id'] }}', '{{ addslashes($sem['name']) }}')" 
                                                                            title="Copy to another semester">
                                                                        <i class="fa fa-copy"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sheet-action btn-sheet-del" 
                                                                            onclick="confirmDeleteHead('{{ $head['fees_master_id'] }}', '{{ addslashes($head['head_name']) }}')" 
                                                                            title="Remove Fee Head">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif

                                                <!-- Empty Semester Notice Row (Rendered for every semester, shown when active batch/filters have no heads) -->
                                                <tr class="sheet-data-row sheet-empty-sem-row {{ $hasHeads ? 'd-none' : '' }}" 
                                                    id="row_empty_sem_{{ $sem['class_type_id'] }}"
                                                    data-course-id="{{ $course['course_id'] }}"
                                                    data-course-name="{{ strtolower($course['name']) }}"
                                                    data-semester-id="{{ $sem['class_type_id'] }}"
                                                    data-semester-name="{{ strtolower($sem['name']) }}"
                                                    data-batch=""
                                                    data-head-name=""
                                                    data-category=""
                                                    data-amount="0"
                                                    data-boys-amount="0"
                                                    data-girls-amount="0">
                                                    <td class="text-center text-muted sheet-row-num">-</td>
                                                    <td><strong class="text-dark">{{ $course['name'] }}</strong></td>
                                                    <td><i class="fa fa-bookmark-o text-muted mr-1"></i>{{ $sem['name'] }}</td>
                                                    <td colspan="7" class="text-muted small">
                                                        <i class="fa fa-info-circle mr-1 text-info"></i> <span class="empty-sem-msg">No fee heads configured for this semester yet.</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sheet-action btn-course-add" 
                                                                onclick="openAddFeeHeadModal('{{ $course['course_id'] }}', '{{ $sem['class_type_id'] }}', '{{ addslashes($sem['name']) }}')">
                                                            <i class="fa fa-plus mr-1"></i> Add Head
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="sheet-data-row" data-course-id="{{ $course['course_id'] }}">
                                                <td colspan="11" class="text-center py-2 text-muted small">
                                                    No semesters configured for {{ $course['name'] }} in Master Setup.
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">
                                                <i class="fa fa-folder-open-o fa-2x mb-2 text-secondary d-block"></i>
                                                No courses or fee structures found for this academic session.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr id="tfoot_summary_row">
                                        <td colspan="6" class="text-right align-middle">
                                            <i class="fa fa-calculator mr-1"></i>
                                            <span id="tfoot_total_label">TOTAL: {{ $totalConfiguredHeads }} Configured Heads</span>
                                        </td>
                                        <td class="text-right align-middle text-success font-weight-bold" id="tfoot_total_amount" style="font-size: 13px;">
                                            ₹{{ number_format($grandTotalFees, 2) }}
                                        </td>
                                        <td class="text-right align-middle text-primary font-weight-bold" id="tfoot_total_boys" style="font-size: 13px;">
                                            ₹{{ number_format($grandTotalFees, 2) }}
                                        </td>
                                        <td class="text-right align-middle text-danger font-weight-bold" id="tfoot_total_girls" style="font-size: 13px;">
                                            ₹{{ number_format($grandTotalFees, 2) }}
                                        </td>
                                        <td colspan="2" class="text-center align-middle">
                                            <button type="button" class="btn btn-xs btn-link text-dark p-0 font-weight-bold" onclick="recalculateSheetTotals()" title="Refresh Sheet Totals">
                                                <i class="fa fa-refresh mr-1"></i> Recalculate
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ================= MODAL: ADD FEE HEAD (Spreadsheet Layout Matching fees_assign) ================= -->
<div class="modal fade" id="modalAddFeeHead" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-plus-circle"></i> Add Fee Head to Semester
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddFeeHead" onsubmit="handleSaveSemesterFeeHead(event)">
                @csrf

                <div class="modal-body">
                    <div class="modal-context-box">
                        <span><strong id="addModalContextText">Select Course & Semester</strong></span>
                    </div>

                    <!-- Course, Semester & Batch -->
                    <div class="row">
                        <div class="col-4 mb-2">
                            <label class="modal-label">Course <span class="text-danger">*</span></label>
                            <select class="form-control modal-control" name="course_id" id="addModalCourseId" required onchange="onModalCourseChange(this.value)">
                                <option value="">-- Course --</option>
                                @foreach($allCourses as $c)
                                    <option value="{{ $c['course_id'] }}">{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4 mb-2">
                            <label class="modal-label">Semester / Class <span class="text-danger">*</span></label>
                            <select class="form-control modal-control" name="class_type_id" id="addModalClassTypeId" required>
                                <option value="">-- Semester --</option>
                            </select>
                        </div>
                        <div class="col-4 mb-2">
                            <label class="modal-label">Admission Batch <span class="text-danger">*</span></label>
                            <select class="form-control modal-control font-weight-bold" name="batch" id="addModalBatch" required>
                                @foreach($availableBatches ?? ['2026', '2025', '2024'] as $b)
                                    <option value="{{ $b }}" {{ ($search['batch'] ?? '2026') == $b ? 'selected' : '' }}>Batch {{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Fee Head Selection or Create New -->
                    <div class="form-group mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="modal-label mb-0">Fee Head <span class="text-danger">*</span></label>
                            <a href="javascript:void(0)" class="small font-weight-bold text-primary" onclick="toggleNewHeadInput()">
                                <span id="btnToggleNewHeadText">+ Create New Head</span>
                            </a>
                        </div>
                        <div id="selectHeadWrapper">
                            <select class="form-control modal-control" name="fees_group_id" id="addFeeGroupSelect" required>
                                <option value="">-- Choose Existing Fee Head --</option>
                                @foreach($masterFeesGroups ?? [] as $mfg)
                                    <option value="{{ $mfg->id }}">{{ $mfg->name }} ({{ $mfg->group_type ?? 'Compulsory' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="newHeadWrapper" class="d-none mt-1 p-2 sheet-input-panel">
                            <label class="modal-label mb-1">New Fee Head Name:</label>
                            <input type="text" name="new_group_name" id="newGroupNameInput" class="form-control modal-control mb-2" placeholder="e.g. Tuition Fee, Exam Fee">
                            <label class="modal-label mb-1">Category / Type:</label>
                            <select name="new_group_type" class="form-control modal-control">
                                <option value="Compulsory">Compulsory</option>
                                <option value="Optional">Optional</option>
                                <option value="Library">Library</option>
                                <option value="Hostel">Hostel</option>
                                <option value="Examination">Examination</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fee Amount & Due Date Side by Side -->
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="modal-label">Base Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold" style="border-radius: 2px 0 0 2px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="amount" id="addModalAmount" class="form-control modal-control font-weight-bold" placeholder="0.00" required oninput="syncAddModalGenderAmounts(this.value)">
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="modal-label">Due Date</label>
                            <input type="date" name="installment_due_date" class="form-control modal-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Gender Specific Pricing (Boys & Girls) -->
                    <div class="row">
                        <div class="col-6 mb-1">
                            <label class="modal-label text-primary font-weight-bold"><i class="fa fa-male mr-1"></i>Boys Amount (₹)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-primary" style="border-radius: 2px 0 0 2px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="boys_amount" id="addModalBoysAmount" class="form-control modal-control font-weight-bold text-primary" placeholder="Boys Amount">
                            </div>
                            <small class="text-muted" style="font-size: 10px;">Leave blank to use Base Amount</small>
                        </div>
                        <div class="col-6 mb-1">
                            <label class="modal-label text-danger font-weight-bold"><i class="fa fa-female mr-1"></i>Girls Amount (₹)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-danger" style="border-radius: 2px 0 0 2px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="girls_amount" id="addModalGirlsAmount" class="form-control modal-control font-weight-bold text-danger" placeholder="Girls Amount">
                            </div>
                            <small class="text-muted" style="font-size: 10px;">Leave blank to use Base Amount</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-modal-submit" id="btnAddSubmit">
                        <i class="fa fa-check mr-1"></i> Save to Semester
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL: EDIT FEE HEAD ================= -->
<div class="modal fade" id="modalEditFeeHead" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-pencil-square-o"></i> Edit Fee Head: <span id="editModalHeadName" class="text-primary font-weight-bold"></span>
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditFeeHead" onsubmit="handleUpdateSemesterFeeHead(event)">
                @csrf
                <input type="hidden" name="fees_master_id" id="editModalMasterId">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-8 mb-2">
                            <label class="modal-label">Fee Head Name</label>
                            <input type="text" name="fee_name" id="editModalHeadNameInput" class="form-control modal-control font-weight-bold">
                        </div>
                        <div class="col-4 mb-2">
                            <label class="modal-label">Admission Batch</label>
                            <select name="batch" id="editModalBatch" class="form-control modal-control font-weight-bold">
                                @foreach($availableBatches ?? ['2026', '2025', '2024'] as $b)
                                    <option value="{{ $b }}">Batch {{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="modal-label">Base Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold" style="border-radius: 2px 0 0 2px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="amount" id="editModalAmount" class="form-control modal-control font-weight-bold" required>
                            </div>
                        </div>

                        <div class="col-6 mb-2">
                            <label class="modal-label">Installment Due Date</label>
                            <input type="date" name="installment_due_date" id="editModalDueDate" class="form-control modal-control">
                        </div>
                    </div>

                    <!-- Gender Specific Pricing (Boys & Girls) -->
                    <div class="row">
                        <div class="col-6 mb-1">
                            <label class="modal-label text-primary font-weight-bold"><i class="fa fa-male mr-1"></i>Boys Amount (₹)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-primary" style="border-radius: 2px 0 0 2px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="boys_amount" id="editModalBoysAmount" class="form-control modal-control font-weight-bold text-primary" placeholder="Boys Amount">
                            </div>
                        </div>

                        <div class="col-6 mb-1">
                            <label class="modal-label text-danger font-weight-bold"><i class="fa fa-female mr-1"></i>Girls Amount (₹)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-danger" style="border-radius: 2px 0 0 2px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="girls_amount" id="editModalGirlsAmount" class="form-control modal-control font-weight-bold text-danger" placeholder="Girls Amount">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-modal-submit" id="btnEditSubmit">
                        <i class="fa fa-save mr-1"></i> Update Fee Head
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL: CLONE / COPY FEE HEADS ================= -->
<div class="modal fade" id="modalCopySemesterFees" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-clone"></i> Clone Fee Heads to Another Semester
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCopySemesterFees" onsubmit="handleCopySemesterFees(event)">
                @csrf

                <div class="modal-body">
                    <div class="modal-context-box">
                        <span><i class="fa fa-info-circle mr-1 text-primary"></i> Duplicate all fee heads in 1-click without creating duplicates.</span>
                    </div>

                    <div class="form-group mb-2">
                        <label class="modal-label">Source Semester (From) <span class="text-danger">*</span></label>
                        <select name="source_class_type_id" id="copySourceClassTypeSelect" class="form-control modal-control" required>
                            <option value="">-- Choose Source Semester --</option>
                            @foreach($allCourses as $c)
                                <optgroup label="{{ $c['name'] }}">
                                    @foreach($c['semesters'] as $s)
                                        <option value="{{ $s['class_type_id'] }}">{{ $c['name'] }} &rarr; {{ $s['name'] }} ({{ count($s['heads']) }} heads)</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label class="modal-label">Target Semester (To) <span class="text-danger">*</span></label>
                        <select name="target_class_type_id" id="copyTargetClassTypeSelect" class="form-control modal-control" required>
                            <option value="">-- Choose Target Semester --</option>
                            @foreach($allCourses as $c)
                                <optgroup label="{{ $c['name'] }}">
                                    @foreach($c['semesters'] as $s)
                                        <option value="{{ $s['class_type_id'] }}">{{ $c['name'] }} &rarr; {{ $s['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-1">
                        <label class="modal-label">Admission Batch (Cohort) <span class="text-danger">*</span></label>
                        <select name="batch" class="form-control modal-control font-weight-bold">
                            @foreach($availableBatches ?? ['2026', '2025', '2024'] as $b)
                                <option value="{{ $b }}" {{ ($search['batch'] ?? '2026') == $b ? 'selected' : '' }}>Batch {{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-modal-submit" id="btnCopySubmit">
                        <i class="fa fa-copy mr-1"></i> Copy Heads Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL: MASTER FEE HEADS LIBRARY ================= -->
<div class="modal fade" id="modalFeeLibrary" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 720px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-book"></i> Master Fee Heads Library
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2">
                <!-- Add New Master Head Form (Compact Excel Filter Strip) -->
                <form id="formAddMasterHead" onsubmit="handleSaveMasterHead(event)" class="mb-2 p-2 rounded" style="background: #e7eff5; border: 1px solid #b8c9d9;">
                    @csrf
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa fa-plus-circle text-success mr-1" style="font-size: 13px;"></i>
                        <span class="font-weight-bold text-dark" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">Create New Reusable Fee Head</span>
                    </div>
                    <div class="row align-items-end g-1">
                        <div class="col-md-5 col-12 mb-1 mb-md-0">
                            <label class="modal-label mb-1" style="font-size: 10.5px; font-weight: 700; color: #1e293b; text-transform: uppercase;">Fee Head Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="masterHeadNameInput" class="form-control modal-control font-weight-bold" placeholder="e.g. Laboratory Fee, Exam Fee" required style="height: 29px !important; font-size: 11.5px;">
                        </div>
                        <div class="col-md-4 col-7 mb-1 mb-md-0">
                            <label class="modal-label mb-1" style="font-size: 10.5px; font-weight: 700; color: #1e293b; text-transform: uppercase;">Category / Type</label>
                            <select name="group_type" class="form-control modal-control" style="height: 29px !important; font-size: 11.5px;">
                                <option value="Compulsory">Compulsory</option>
                                <option value="Optional">Optional</option>
                                <option value="Library">Library</option>
                                <option value="Hostel">Hostel</option>
                                <option value="Examination">Examination</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-5">
                            <button type="submit" class="btn btn-sm btn-block font-weight-bold" id="btnSaveMasterHead" style="height: 29px; font-size: 11px; background: #217346; border: 1px solid #185c37; color: #ffffff; border-radius: 2px;">
                                <i class="fa fa-plus mr-1"></i> + Create Head
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Existing Master Heads Table in Excel Spreadsheet Style -->
                <div class="d-flex justify-content-between align-items-center px-1 mb-1">
                    <span class="font-weight-bold text-dark" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                        Configured Master Heads (<span id="masterHeadsCountText">{{ count($masterFeesGroups ?? []) }}</span>)
                    </span>
                    <span class="small text-muted" style="font-size: 10.5px;">
                        <i class="fa fa-info-circle mr-1 text-primary"></i> Reusable across all courses & semesters
                    </span>
                </div>
                <div class="table-responsive border" style="max-height: 310px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 11.5px; border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr style="background: #d9eaf7 !important; color: #152536 !important; border-bottom: 2px solid #8fa7b8;">
                                <th style="width: 45px; text-align: center; border-color: #8fa7b8;">#</th>
                                <th style="border-color: #8fa7b8;">Fee Head Name</th>
                                <th style="width: 140px; text-align: center; border-color: #8fa7b8;">Category / Type</th>
                                <th class="text-center" style="width: 70px; border-color: #8fa7b8;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($masterFeesGroups ?? [] as $mIndex => $mHead)
                                <tr id="masterHeadRow_{{ $mHead->id }}">
                                    <td class="text-center align-middle text-muted" style="border-color: #c6cdd3;">{{ $mIndex + 1 }}</td>
                                    <td class="font-weight-bold text-dark align-middle" style="border-color: #c6cdd3;">{{ $mHead->name }}</td>
                                    <td class="text-center align-middle" style="border-color: #c6cdd3;">
                                        @php
                                            $gType = strtolower($mHead->group_type ?? 'compulsory');
                                            $chipClass = 'fee-chip-compulsory';
                                            if (str_contains($gType, 'option')) $chipClass = 'fee-chip-optional';
                                            elseif (str_contains($gType, 'lib')) $chipClass = 'fee-chip-library';
                                            elseif ($gType !== 'compulsory') $chipClass = 'fee-chip-other';
                                        @endphp
                                        <span class="fee-chip {{ $chipClass }}">{{ $mHead->group_type ?? 'Compulsory' }}</span>
                                    </td>
                                    <td class="text-center align-middle" style="border-color: #c6cdd3;">
                                        <button type="button" class="btn btn-sheet-action btn-sheet-del" 
                                                onclick="handleDeleteMasterHead('{{ $mHead->id }}', '{{ addslashes($mHead->name) }}')" 
                                                title="Delete Fee Head"
                                                style="height: 24px; padding: 2px 7px; font-size: 11px; border-radius: 2px;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No master fee heads created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 px-3" style="background: #f1f4f6; border-top: 1px solid #cbd5e1;">
                <button type="button" class="btn btn-secondary btn-sm px-3 btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" style="height: 30px; font-size: 11.5px; border-radius: 2px; background: #ffffff; color: #475569; border: 1px solid #94a3b8;">
                    <i class="fa fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: FEE COLLECTED WARNING (Cannot Delete Head) ================= -->
<div class="modal fade" id="modalFeeCollectedWarning" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 640px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-ban text-danger mr-2"></i> Cannot Delete Fee Head: Fees Already Collected
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Top Circle Icon & Headline -->
                <div class="d-flex align-items-start mb-3">
                    <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-shield text-danger" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Fee Head Deletion Blocked</strong>
                        <span class="text-muted" style="font-size: 11.5px;">Student payment receipts are actively recorded against this fee head in the database.</span>
                    </div>
                </div>

                <!-- Alert Notice Box (Promote style) -->
                <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-start" style="font-size: 11.5px; border-radius: 2px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
                    <i class="fa fa-exclamation-triangle mr-2 mt-1" style="font-size: 14px; flex-shrink: 0;"></i>
                    <div>
                        <strong class="d-block" style="font-size: 12px; font-weight: 700;">Fee Revert / Refund Required First</strong>
                        Fee payments have already been collected from student(s) for this fee head. You must first refund/revert all collected receipts before removing this fee head from the structure.
                    </div>
                </div>

                <!-- Parameters Summary Box (Promote style) -->
                <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Target Fee Head:</span>
                        <strong class="text-dark" id="warn_collected_head_name">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Total Amount Collected:</span>
                        <strong class="text-danger font-weight-bold" style="font-size: 13px;" id="warn_collected_total_amount">₹0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Audit Protection Status:</span>
                        <span class="sheet-chip" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                            <i class="fa fa-lock mr-1"></i> Locked &bull; Protected Record
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="text-muted">Required Action:</span>
                        <span class="sheet-chip sheet-chip-batch">Revert / Refund receipts in Student Fees Collection module</span>
                    </div>
                </div>

                <!-- Recent Collected Receipts Spreadsheet Table -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-weight-bold text-dark" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                        Recent Collected Receipts / Invoices (<span id="warn_collected_invoices_count">0</span>)
                    </span>
                    <span class="sheet-chip sheet-chip-batch" style="font-size: 10px;">
                        <i class="fa fa-lock text-danger mr-1"></i> Protected Receipts
                    </span>
                </div>
                <div class="table-responsive border" style="max-height: 190px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover mb-0 text-center" style="font-size: 11.5px; border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr style="background: #d9eaf7 !important; color: #152536 !important; border-bottom: 2px solid #8fa7b8;">
                                <th style="width: 40px; border-color: #8fa7b8; font-weight: 700;">#</th>
                                <th style="border-color: #8fa7b8; font-weight: 700;">Invoice No.</th>
                                <th class="text-left" style="border-color: #8fa7b8; font-weight: 700;">Student Name</th>
                                <th style="width: 110px; border-color: #8fa7b8; font-weight: 700;">Paid Amount</th>
                                <th style="width: 100px; border-color: #8fa7b8; font-weight: 700;">Date</th>
                            </tr>
                        </thead>
                        <tbody id="warn_collected_invoices_tbody">
                            <!-- Invoices populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fa fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: CONFIRM DELETE FEE HEAD ================= -->
<div class="modal fade" id="modalConfirmDeleteFeeHead" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fa fa-trash text-danger mr-2"></i> Confirm Delete Fee Head
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-start mb-3">
                    <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-trash text-danger" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;" id="confirmDeleteModalTitle">Delete Fee Head</strong>
                        <span class="text-muted" style="font-size: 11.5px;" id="confirmDeleteModalDesc">Please review the details below before removing this fee head:</span>
                    </div>
                </div>

                <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Target Fee Head:</span>
                        <strong class="text-dark" id="confirmDeleteTargetName">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Collection Status:</span>
                        <span class="sheet-chip" style="background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;">
                            <i class="fa fa-check mr-1"></i> 0 Payments &bull; Safe to Delete
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="text-muted">Ledger Impact:</span>
                        <span class="sheet-chip sheet-chip-batch">Unassigned from semester ledger</span>
                    </div>
                </div>

                <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-start" style="font-size: 11px; border-radius: 2px; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e;">
                    <i class="fa fa-exclamation-triangle mr-2 mt-1" style="font-size: 13px; flex-shrink: 0;"></i>
                    <div>
                        <strong>Notice:</strong> This action cannot be undone. If any student fee collection is detected during removal, deletion will be blocked automatically.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-modal-danger font-weight-bold" id="btnExecuteDeleteFeeHead">
                    <i class="fa fa-trash mr-1"></i> Confirm Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Courses and semesters data dictionary for fast client-side interaction
    const coursesTreeData = @json($allCourses);
    let isCreatingNewHeadInModal = false;

    // ==========================================
    // SEAMLESS NAVIGATION TO FEES ASSIGN PAGE
    // ==========================================
    function goToFeesAssignSheet() {
        let courseId = $('#grid_filter_course').val() || '';
        let semesterId = $('#grid_filter_semester').val() || '';
        let batch = $('#grid_filter_batch').val() || '';

        let params = new URLSearchParams();
        if (courseId) params.set('course_id', courseId);
        if (semesterId) params.set('class_type_id', semesterId);
        if (batch) params.set('batch', batch);

        window.location.href = "{{ url('fees_assign') }}" + (params.toString() ? '?' + params.toString() : '');
    }

    function assignCourseDirect(courseId) {
        let batch = $('#grid_filter_batch').val() || '';
        let params = new URLSearchParams();
        if (courseId) params.set('course_id', courseId);
        if (batch) params.set('batch', batch);
        window.location.href = "{{ url('fees_assign') }}" + (params.toString() ? '?' + params.toString() : '');
    }

    function assignSemesterDirect(courseId, semesterId) {
        let batch = $('#grid_filter_batch').val() || '';
        let params = new URLSearchParams();
        if (courseId) params.set('course_id', courseId);
        if (semesterId) params.set('class_type_id', semesterId);
        if (batch) params.set('batch', batch);
        window.location.href = "{{ url('fees_assign') }}" + (params.toString() ? '?' + params.toString() : '');
    }

    // ==========================================
    // BULLETPROOF MODAL DISMISS / CLOSE HANDLER
    // (Works across Bootstrap 4, Bootstrap 5 and jQuery)
    // ==========================================
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .close', function(e) {
        e.preventDefault();
        var $modal = $(this).closest('.modal');
        if ($modal.length > 0) {
            try { $modal.modal('hide'); } catch(err) {}
            try {
                if (window.bootstrap && bootstrap.Modal) {
                    var bsModal = bootstrap.Modal.getInstance($modal[0]);
                    if (bsModal) bsModal.hide();
                }
            } catch(err) {}
            $modal.removeClass('show').hide();
            $('body').removeClass('modal-open').css('padding-right', '');
            $('.modal-backdrop').remove();
        }
    });

    // ==========================================
    // DYNAMIC FILTER TOOLBAR: COURSE -> SEMESTER
    // ==========================================
    function onFilterCourseChange(courseId) {
        let $semSelect = $('#grid_filter_semester');
        $semSelect.empty().append('<option value="">All Semesters</option>');

        if (courseId && courseId !== 'ALL') {
            let course = coursesTreeData.find(c => String(c.course_id) === String(courseId));
            if (course && course.semesters) {
                course.semesters.forEach(s => {
                    $semSelect.append(`<option value="${s.class_type_id}">${s.name}</option>`);
                });
            }
        } else {
            // All courses: populate all distinct semester names
            let addedNames = new Set();
            coursesTreeData.forEach(c => {
                (c.semesters || []).forEach(s => {
                    if (!addedNames.has(s.name)) {
                        addedNames.add(s.name);
                        $semSelect.append(`<option value="${s.name}">${s.name}</option>`);
                    }
                });
            });
        }
        filterSpreadsheetRows();
    }

    // Modal Semester options generator
    function onModalCourseChange(courseId, preselectSemesterId = null) {
        let $semSelect = $('#addModalClassTypeId');
        $semSelect.empty().append('<option value="">-- Select Semester --</option>');

        if (!courseId) return;

        let selectedCourse = coursesTreeData.find(c => String(c.course_id) === String(courseId));
        if (selectedCourse && selectedCourse.semesters) {
            selectedCourse.semesters.forEach(s => {
                let isSel = preselectSemesterId && String(s.class_type_id) === String(preselectSemesterId) ? 'selected' : '';
                $semSelect.append(`<option value="${s.class_type_id}" ${isSel}>${s.name}</option>`);
            });
        }
    }

    // Open Add Modal from Top Button
    function openAddModalGlobal() {
        $('#formAddFeeHead')[0].reset();
        $('#addModalBoysAmount, #addModalGirlsAmount').data('user-edited', false);
        isCreatingNewHeadInModal = false;
        $('#selectHeadWrapper').removeClass('d-none');
        $('#newHeadWrapper').addClass('d-none');
        $('#addFeeGroupSelect').attr('required', 'required');
        $('#newGroupNameInput').removeAttr('required');
        $('#btnToggleNewHeadText').text('+ Create New Head');
        $('#addModalContextText').text('Select Course & Semester');

        let curBatch = $('#grid_filter_batch').val();
        if (curBatch && curBatch !== 'ALL') {
            $('#addModalBatch').val(curBatch);
        }

        let firstCourseId = coursesTreeData.length > 0 ? coursesTreeData[0].course_id : '';
        if (firstCourseId) {
            $('#addModalCourseId').val(firstCourseId);
            onModalCourseChange(firstCourseId);
        }
        $('#modalAddFeeHead').modal('show');
    }

    // Open Add Modal pre-filtered for a specific course
    function openAddModalForCourse(courseId, courseName) {
        $('#formAddFeeHead')[0].reset();
        $('#addModalBoysAmount, #addModalGirlsAmount').data('user-edited', false);
        $('#addModalCourseId').val(courseId);
        $('#addModalContextText').text(courseName);

        let curBatch = $('#grid_filter_batch').val();
        if (curBatch && curBatch !== 'ALL') {
            $('#addModalBatch').val(curBatch);
        }

        onModalCourseChange(courseId);
        $('#modalAddFeeHead').modal('show');
    }

    // Open Add Modal pre-filtered for specific course & semester
    function openAddFeeHeadModal(courseId, semesterId, semesterName) {
        $('#formAddFeeHead')[0].reset();
        $('#addModalBoysAmount, #addModalGirlsAmount').data('user-edited', false);
        $('#addModalCourseId').val(courseId);
        $('#addModalContextText').text(`${semesterName}`);

        let curBatch = $('#grid_filter_batch').val();
        if (curBatch && curBatch !== 'ALL') {
            $('#addModalBatch').val(curBatch);
        }

        onModalCourseChange(courseId, semesterId);
        $('#modalAddFeeHead').modal('show');
    }

    // Helper: auto sync Boys & Girls amounts when Base Amount changes if not customized
    function syncAddModalGenderAmounts(val) {
        let $b = $('#addModalBoysAmount');
        let $g = $('#addModalGirlsAmount');
        if (!$b.data('user-edited')) {
            $b.val(val);
        }
        if (!$g.data('user-edited')) {
            $g.val(val);
        }
    }

    $(document).on('input', '#addModalBoysAmount, #addModalGirlsAmount', function() {
        $(this).data('user-edited', true);
    });

    // Toggle creating new fee head inside semester add modal
    function toggleNewHeadInput() {
        isCreatingNewHeadInModal = !isCreatingNewHeadInModal;
        if (isCreatingNewHeadInModal) {
            $('#selectHeadWrapper').addClass('d-none');
            $('#newHeadWrapper').removeClass('d-none');
            $('#addFeeGroupSelect').removeAttr('required');
            $('#newGroupNameInput').attr('required', 'required');
            $('#btnToggleNewHeadText').html('&larr; Choose Existing Head');
        } else {
            $('#selectHeadWrapper').removeClass('d-none');
            $('#newHeadWrapper').addClass('d-none');
            $('#addFeeGroupSelect').attr('required', 'required');
            $('#newGroupNameInput').removeAttr('required');
            $('#btnToggleNewHeadText').text('+ Create New Head');
        }
    }

    // Open Edit Modal
    function openEditFeeHeadModal(head, semesterId, courseId) {
        $('#editModalMasterId').val(head.fees_master_id);
        $('#editModalHeadName').text(head.head_name);
        $('#editModalHeadNameInput').val(head.head_name);
        $('#editModalAmount').val(head.amount);
        $('#editModalBoysAmount').val(head.boys_amount !== undefined && head.boys_amount !== null ? head.boys_amount : head.amount);
        $('#editModalGirlsAmount').val(head.girls_amount !== undefined && head.girls_amount !== null ? head.girls_amount : head.amount);
        $('#editModalDueDate').val(head.due_date ? head.due_date.substring(0, 10) : '');
        $('#editModalBatch').val(head.batch || '2026');
        $('#modalEditFeeHead').modal('show');
    }

    // Open Copy Modal
    function openCopyModalGlobal() {
        $('#copySourceClassTypeSelect').val('');
        $('#copyTargetClassTypeSelect').val('');
        let curBatch = $('#grid_filter_batch').val();
        if (curBatch && curBatch !== 'ALL') {
            $('#modalCopySemesterFees select[name="batch"]').val(curBatch);
        }
        $('#modalCopySemesterFees').modal('show');
    }

    function openCopyModal(courseId, semesterId, semesterName) {
        $('#copySourceClassTypeSelect').val(semesterId);
        $('#copyTargetClassTypeSelect').val('');
        let curBatch = $('#grid_filter_batch').val();
        if (curBatch && curBatch !== 'ALL') {
            $('#modalCopySemesterFees select[name="batch"]').val(curBatch);
        }
        $('#modalCopySemesterFees').modal('show');
    }

    // ==========================================
    // INSTANT CLIENT-SIDE FILTERING & SEARCH
    // ==========================================
    function filterSpreadsheetRows() {
        let rawCourse = $('#grid_filter_course').val() || '';
        let rawBatch = $('#grid_filter_batch').val() || '';
        let rawSem = $('#grid_filter_semester').val() || '';
        let rawCat = $('#grid_filter_category').val() || '';
        let keyword = ($('#grid_filter_keyword').val() || '').toLowerCase().trim();

        // Check if any filter is active
        let isFilterApplied = (rawCourse !== '') || (rawBatch !== '') || (rawSem !== '') || (rawCat !== '') || (keyword !== '');

        if (!isFilterApplied) {
            $('.sheet-data-row').addClass('d-none');
            $('.row-course-group').addClass('d-none');
            $('#row_initial_state').removeClass('d-none');
            $('#row_no_match_state').addClass('d-none');
            $('#tfoot_total_label').text('TOTAL: 0 Configured Heads');
            $('#tfoot_total_amount').text('₹0.00');
            $('#tfoot_total_boys').text('₹0.00');
            $('#tfoot_total_girls').text('₹0.00');
            return;
        }

        $('#row_initial_state').addClass('d-none');

        let selCourse = (rawCourse === 'ALL') ? '' : rawCourse;
        let selBatch = (rawBatch === 'ALL') ? '' : rawBatch.toLowerCase().trim();
        let selSem = rawSem;
        let selCat = rawCat.toLowerCase().trim();

        let visibleRowsCount = 0;
        let visibleAmountSum = 0;
        let visibleBoysSum = 0;
        let visibleGirlsSum = 0;
        let visibleCoursesSet = new Set();
        let matchedHeadsPerSemester = {};
        let courseTotals = {};

        // 1. Process regular data rows
        $('.sheet-data-row:not(.sheet-empty-sem-row)').each(function() {
            let $row = $(this);
            let cId = String($row.attr('data-course-id') || '');
            let cName = String($row.attr('data-course-name') || '');
            let sId = String($row.attr('data-semester-id') || '');
            let sName = String($row.attr('data-semester-name') || '');
            let rBatch = String($row.attr('data-batch') || '').toLowerCase().trim();
            let hName = String($row.attr('data-head-name') || '');
            let cat = String($row.attr('data-category') || '');
            let amt = parseFloat($row.attr('data-amount') || 0);
            let bAmt = parseFloat($row.attr('data-boys-amount') || amt);
            let gAmt = parseFloat($row.attr('data-girls-amount') || amt);

            let match = true;

            // Course filter
            if (selCourse && cId !== String(selCourse)) match = false;

            // Semester filter (by class_type_id or semester name)
            if (selSem) {
                if (sId !== String(selSem) && sName !== String(selSem).toLowerCase()) {
                    match = false;
                }
            }

            // Batch filter
            if (selBatch && rBatch !== selBatch) match = false;

            // Category filter
            if (selCat && cat.indexOf(selCat) === -1) match = false;

            // Keyword filter across all fields
            if (keyword && cName.indexOf(keyword) === -1 && sName.indexOf(keyword) === -1 && hName.indexOf(keyword) === -1 && rBatch.indexOf(keyword) === -1) {
                match = false;
            }

            if (match) {
                $row.removeClass('d-none');
                visibleCoursesSet.add(cId);
                visibleRowsCount++;
                visibleAmountSum += amt;
                visibleBoysSum += bAmt;
                visibleGirlsSum += gAmt;
                matchedHeadsPerSemester[sId] = (matchedHeadsPerSemester[sId] || 0) + 1;
                courseTotals[cId] = (courseTotals[cId] || 0) + amt;
            } else {
                $row.addClass('d-none');
            }
        });

        // 2. Process empty semester placeholder rows
        $('.sheet-empty-sem-row').each(function() {
            let $row = $(this);
            let cId = String($row.attr('data-course-id') || '');
            let cName = String($row.attr('data-course-name') || '');
            let sId = String($row.attr('data-semester-id') || '');
            let sName = String($row.attr('data-semester-name') || '');

            let semMatch = true;
            if (selCourse && cId !== String(selCourse)) semMatch = false;
            if (selSem && sId !== String(selSem) && sName !== String(selSem).toLowerCase()) semMatch = false;
            if (keyword && cName.indexOf(keyword) === -1 && sName.indexOf(keyword) === -1) semMatch = false;

            let hasMatchingHeads = (matchedHeadsPerSemester[sId] || 0) > 0;

            if (semMatch && !hasMatchingHeads) {
                if (selCat) {
                    // When category filter is active, don't show empty placeholders
                    $row.addClass('d-none');
                } else {
                    $row.removeClass('d-none');
                    visibleCoursesSet.add(cId);
                    if (selBatch) {
                        $row.find('.empty-sem-msg').text(`No fee heads configured for Batch ${selBatch.toUpperCase()} yet.`);
                    } else {
                        $row.find('.empty-sem-msg').text('No fee heads configured for this semester yet.');
                    }
                }
            } else {
                $row.addClass('d-none');
            }
        });

        // 3. Renumber visible data rows consecutively
        let rowCounter = 1;
        $('.sheet-data-row:not(.d-none):not(.sheet-empty-sem-row)').each(function() {
            $(this).find('.sheet-row-num').text(rowCounter++);
        });

        // 4. Show/Hide course group header rows and dynamically update course total
        $('.row-course-group').each(function() {
            let cId = String($(this).attr('data-course-id'));
            if (visibleCoursesSet.has(cId)) {
                $(this).removeClass('d-none');
                let cTotal = courseTotals[cId] || 0;
                $(this).find('.course-total-amount').text(`₹${cTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
            } else {
                $(this).addClass('d-none');
            }
        });

        // 5. Show/Hide No Match message
        if (visibleCoursesSet.size === 0) {
            $('#row_no_match_state').removeClass('d-none');
        } else {
            $('#row_no_match_state').addClass('d-none');
        }

        // 6. Update footer totals
        $('#tfoot_total_label').text(`TOTAL: ${visibleRowsCount} Configured Heads`);
        $('#tfoot_total_amount').text(`₹${visibleAmountSum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
        $('#tfoot_total_boys').text(`₹${visibleBoysSum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
        $('#tfoot_total_girls').text(`₹${visibleGirlsSum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
    }

    // Initialize filters on page load
    $(document).ready(function() {
        let initialCourse = $('#grid_filter_course').val();
        if (initialCourse && initialCourse !== 'ALL') {
            onFilterCourseChange(initialCourse);
        } else {
            filterSpreadsheetRows();
        }
    });

    // Recalculate Totals
    function recalculateSheetTotals() {
        filterSpreadsheetRows();
        toastr.info('Spreadsheet totals refreshed.');
    }

    // Reset All Filters
    function resetAllFilters() {
        $('#grid_filter_course').val('');
        $('#grid_filter_semester').val('');
        $('#grid_filter_batch').val('');
        $('#grid_filter_category').val('');
        $('#grid_filter_keyword').val('');
        onFilterCourseChange('');
    }

    // ==========================================
    // AJAX ACTIONS
    // ==========================================
    // 1. Save Fee Head
    function handleSaveSemesterFeeHead(e) {
        e.preventDefault();
        let $btn = $('#btnAddSubmit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

        let formData = $('#formAddFeeHead').serialize();
        $.ajax({
            url: "{{ url('saveSemesterFeeHead') }}",
            type: "POST",
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save to Semester');
                if (res.status === true || res.status === 'success') {
                    $('#modalAddFeeHead').modal('hide');
                    toastr.success(res.message || 'Fee head added successfully!');
                    setTimeout(() => location.reload(), 400);
                } else {
                    toastr.error(res.message || 'Failed to add fee head.');
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save to Semester');
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error saving fee head.';
                toastr.error(msg);
            }
        });
    }

    // 2. Update Fee Head
    function handleUpdateSemesterFeeHead(e) {
        e.preventDefault();
        let $btn = $('#btnEditSubmit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Updating...');

        let formData = $('#formEditFeeHead').serialize();
        $.ajax({
            url: "{{ url('updateSemesterFeeHead') }}",
            type: "POST",
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Update Fee Head');
                if (res.status === true || res.status === 'success') {
                    $('#modalEditFeeHead').modal('hide');
                    toastr.success(res.message || 'Fee head updated successfully!');
                    setTimeout(() => location.reload(), 400);
                } else {
                    toastr.error(res.message || 'Failed to update fee head.');
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Update Fee Head');
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error updating fee head.';
                toastr.error(msg);
            }
        });
    }

    // Pending Delete Action Callback for Modal
    let pendingDeleteAction = null;

    // Helper: Display custom alert modal if fees have been collected
    function showFeeCollectedWarningModal(data, defaultHeadName) {
        let headName = data.head_name || defaultHeadName || 'Fee Head';
        let totalAmt = parseFloat(data.total_collected || 0);
        let collections = data.collections || [];

        $('#warn_collected_head_name').text(headName);
        $('#warn_collected_total_amount').text('₹' + totalAmt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#warn_collected_invoices_count').text(`${collections.length} receipt${collections.length === 1 ? '' : 's'} recorded`);

        let html = '';
        if (collections.length > 0) {
            collections.forEach(function(item, idx) {
                let amt = parseFloat(item.paid_amount || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                html += `<tr>
                    <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
                    <td class="font-weight-bold text-primary">${item.invoice_no || '-'}</td>
                    <td class="text-left">
                        <span class="font-weight-bold text-dark">${item.student_name || 'Student'}</span>
                        <small class="text-muted d-block" style="font-size: 10px;">Adm: ${item.admission_no || '-'}</small>
                    </td>
                    <td class="text-right font-weight-bold text-success">₹${amt}</td>
                    <td class="text-center text-muted">${item.date || '-'}</td>
                </tr>`;
            });
        } else {
            html = `<tr><td colspan="5" class="text-center py-2 text-muted">No receipt details found.</td></tr>`;
        }
        $('#warn_collected_invoices_tbody').html(html);
        $('#modalFeeCollectedWarning').modal('show');
    }

    // Modal Execute Delete Button Handler
    $(document).on('click', '#btnExecuteDeleteFeeHead', function(e) {
        e.preventDefault();
        if (typeof pendingDeleteAction === 'function') {
            pendingDeleteAction();
        }
    });

    // 3. Delete Semester Fee Head (Custom Modal Flow)
    function confirmDeleteHead(masterId, headName) {
        let targetName = headName || ($('#row_master_' + masterId).attr('data-head-name') || 'Fee Head');
        $('#confirmDeleteModalTitle').text('Remove Fee Head from Semester');
        $('#confirmDeleteModalDesc').text('Are you sure you want to remove this fee head from this semester? If fee collection exists, deletion will be blocked.');
        $('#confirmDeleteTargetName').text(targetName);

        pendingDeleteAction = function() {
            let $btn = $('#btnExecuteDeleteFeeHead');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Removing...');

            $.ajax({
                url: "{{ url('deleteSemesterFeeHead') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    fees_master_id: masterId
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-trash mr-1"></i> Confirm Delete');
                    $('#modalConfirmDeleteFeeHead').modal('hide');

                    if (res.status === true || res.status === 'success') {
                        toastr.success(res.message || 'Fee head removed!');
                        $('#row_master_' + masterId).fadeOut(250, function() {
                            $(this).remove();
                            filterSpreadsheetRows();
                        });
                    } else if (res.has_collected) {
                        showFeeCollectedWarningModal(res, targetName);
                    } else {
                        toastr.error(res.message || 'Unable to delete fee head.');
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('<i class="fa fa-trash mr-1"></i> Confirm Delete');
                    $('#modalConfirmDeleteFeeHead').modal('hide');

                    let res = err.responseJSON;
                    if (res && res.has_collected) {
                        showFeeCollectedWarningModal(res, targetName);
                    } else {
                        let msg = res && res.message ? res.message : 'Error deleting fee head.';
                        toastr.error(msg);
                    }
                }
            });
        };

        $('#btnExecuteDeleteFeeHead').prop('disabled', false).html('<i class="fa fa-trash mr-1"></i> Confirm Delete');
        $('#modalConfirmDeleteFeeHead').modal('show');
    }

    // 4. Copy Semester Fees
    function handleCopySemesterFees(e) {
        e.preventDefault();
        let $btn = $('#btnCopySubmit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Copying...');

        let formData = $('#formCopySemesterFees').serialize();
        $.ajax({
            url: "{{ url('copySemesterFees') }}",
            type: "POST",
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-copy mr-1"></i> Copy Heads Now');
                if (res.status === true || res.status === 'success') {
                    $('#modalCopySemesterFees').modal('hide');
                    toastr.success(res.message || 'Fee heads copied successfully!');
                    setTimeout(() => location.reload(), 400);
                } else {
                    toastr.error(res.message || 'Failed to copy fee heads.');
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html('<i class="fa fa-copy mr-1"></i> Copy Heads Now');
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error copying fee heads.';
                toastr.error(msg);
            }
        });
    }

    // 5. Master Fee Head Save
    function handleSaveMasterHead(e) {
        e.preventDefault();
        let $btn = $('#btnSaveMasterHead');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Creating...');

        let formData = $('#formAddMasterHead').serialize();
        $.ajax({
            url: "{{ url('saveMasterFeeHead') }}",
            type: "POST",
            data: formData,
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-plus mr-1"></i> Create Head');
                if (res.status === true || res.status === 'success') {
                    toastr.success(res.message || 'Master fee head created!');
                    $('#masterHeadNameInput').val('');
                    setTimeout(() => location.reload(), 400);
                } else {
                    toastr.error(res.message || 'Failed to create master head.');
                }
            },
            error: function(err) {
                $btn.prop('disabled', false).html('<i class="fa fa-plus mr-1"></i> Create Head');
                let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error creating fee head.';
                toastr.error(msg);
            }
        });
    }

    // 6. Delete Master Head (Custom Modal Flow)
    function handleDeleteMasterHead(groupId, headName) {
        let targetName = headName || 'Master Fee Head #' + groupId;
        $('#confirmDeleteModalTitle').text('Delete Master Fee Head');
        $('#confirmDeleteModalDesc').text('Are you sure you want to delete this fee head from the master library? If any collections or semester assignments exist, deletion will be blocked.');
        $('#confirmDeleteTargetName').text(targetName);

        pendingDeleteAction = function() {
            let $btn = $('#btnExecuteDeleteFeeHead');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Deleting...');

            $.ajax({
                url: "{{ url('deleteMasterFeeHead') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    fees_group_id: groupId
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-trash mr-1"></i> Confirm Delete');
                    $('#modalConfirmDeleteFeeHead').modal('hide');

                    if (res.status === true || res.status === 'success') {
                        toastr.success(res.message || 'Fee head deleted.');
                        $('#masterHeadRow_' + groupId).fadeOut(250, function() {
                            $(this).remove();
                        });
                    } else if (res.has_collected) {
                        showFeeCollectedWarningModal(res, targetName);
                    } else {
                        toastr.error(res.message || 'Cannot delete fee head.');
                    }
                },
                error: function(err) {
                    $btn.prop('disabled', false).html('<i class="fa fa-trash mr-1"></i> Confirm Delete');
                    $('#modalConfirmDeleteFeeHead').modal('hide');

                    let res = err.responseJSON;
                    if (res && res.has_collected) {
                        showFeeCollectedWarningModal(res, targetName);
                    } else {
                        let msg = res && res.message ? res.message : 'Error deleting fee head.';
                        toastr.error(msg);
                    }
                }
            });
        };

        $('#btnExecuteDeleteFeeHead').prop('disabled', false).html('<i class="fa fa-trash mr-1"></i> Confirm Delete');
        $('#modalConfirmDeleteFeeHead').modal('show');
    }
</script>

@endsection
