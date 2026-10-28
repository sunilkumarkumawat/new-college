@extends('layout.app')
@section('content')

<style>
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

.content-wrapper {
    background-color: var(--bg-page);
}

.fees-progression-page-card {
    border: 1px solid var(--line-border) !important;
    border-radius: 2px !important;
    box-shadow: none !important;
    background: #ffffff;
    margin-bottom: 0 !important;
}

/* Excel Green Ribbon Header (Standard ERP 14.5px Titlebar) */
.fees-progression-page-card > .card-header {
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

.fees-progression-page-card > .card-header::before,
.fees-progression-page-card > .card-header::after {
    display: none !important;
    content: none !important;
}

.fees-progression-page-card > .card-header .card-title {
    color: #ffffff !important;
    font-size: 14.5px !important;
    letter-spacing: 0.2px;
    font-weight: 700 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center;
}

.fees-progression-page-card > .card-header .card-title i {
    color: #ffc107 !important;
    margin-right: 8px;
    font-size: 15px;
}

/* Standard Header Light Buttons */
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
.progression-kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
}

@media (max-width: 992px) {
    .progression-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.kpi-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-top: 3px solid var(--green-excel);
    border-radius: 2px;
    padding: 8px 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

.kpi-card.kpi-warning { border-top-color: #f59e0b; }
.kpi-card.kpi-danger { border-top-color: #ef4444; }
.kpi-card.kpi-info { border-top-color: #0ea5e9; }
.kpi-card.kpi-purple { border-top-color: #6366f1; }

.kpi-card-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 2px;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    font-weight: 500;
    margin-top: 2px;
}

/* Unified Compact Filter Toolbar */
.progression-filter-toolbar {
    background: #ffffff;
    border-bottom: 1px solid #cbd5e1;
    padding: 10px 12px;
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
    padding: 3px 8px !important;
    font-size: 11.5px !important;
    color: #172033 !important;
    background: #ffffff !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
    width: 100%;
}

.toolbar-control:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;
    outline: none;
}

.btn-search-action {
    height: 29px !important;
    padding: 3px 12px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    border-radius: 2px !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.btn-search-action:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

/* Spreadsheet Box & Table */
.matrix-container-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 2px;
    margin: 10px 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.matrix-action-bar {
    padding: 8px 10px;
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
    width: 250px;
}
.search-input-wrapper i {
    position: absolute;
    left: 8px;
    top: 8px;
    color: #94a3b8;
    font-size: 11.5px;
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
}
.search-input-field:focus {
    border-color: var(--green-excel);
    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25);
}

.filter-pill-btn {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    font-size: 11px;
    font-weight: 600;
    color: #334155;
    padding: 3px 8px;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.filter-pill-btn:hover {
    background: #f1f5f9;
}
.filter-pill-btn.active {
    background: var(--green-excel);
    color: #ffffff;
    border-color: var(--green-excel-dark);
}

.btn-matrix-tool {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    height: 29px;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
}
.btn-matrix-tool:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

/* Primary Execution Button */
.btn-execute-progression {
    background: var(--green-excel);
    color: #ffffff;
    border: 1px solid var(--green-excel-dark);
    font-size: 12px;
    font-weight: 700;
    height: 29px;
    padding: 3px 14px;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.btn-execute-progression:hover {
    background: var(--green-excel-dark);
    color: #ffffff;
}
.btn-execute-progression:disabled {
    background: #94a3b8;
    border-color: #94a3b8;
    cursor: not-allowed;
    opacity: 0.8;
}

/* Matrix Table Styling */
.matrix-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 11.5px;
}
.matrix-table th {
    background: #d9eaf7 !important;
    color: #152536 !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 6px 8px !important;
    border-bottom: 2px solid #8fa7b8 !important;
    border-right: 1px solid #b8c9d9 !important;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 5;
}
.matrix-table td {
    padding: 6px 8px !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #cbd5e1 !important;
    border-right: 1px solid #e2e8f0 !important;
}
.matrix-table tr:hover td {
    background-color: #f8fafc;
}

.btn-quick-single-progress {
    background: #ffffff;
    border: 1px solid #10b981;
    color: #047857;
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-quick-single-progress:hover {
    background: #10b981;
    color: #ffffff;
}

/* Modals Matching Promote Page */
.modal-content {
    border-radius: 2px !important;
    border: 1px solid #9aa7b1 !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
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
}
.modal-title i {
    color: var(--green-excel) !important;
    font-size: 14px !important;
    margin-right: 6px;
}
.modal-header .close {
    margin: 0 !important;
    padding: 2px 6px !important;
    color: #344450 !important;
    opacity: 0.85 !important;
    font-size: 20px !important;
    background: transparent !important;
    border: 0 !important;
    cursor: pointer !important;
}
.modal-header .close:hover {
    color: #000000 !important;
    opacity: 1 !important;
}
.modal-body {
    padding: 12px 14px !important;
    background: #ffffff !important;
}
.modal-footer {
    padding: 8px 12px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
}
/* Deactivated Student Styling */
.student-row-deactive {
    background-color: #f8fafc !important;
    opacity: 0.72;
}
.student-row-deactive td {
    color: #64748b !important;
}
.student-row-deactive .student-checkbox {
    cursor: not-allowed;
    opacity: 0.5;
}

/* Animation for Auto-detected Semester Dropdowns */
@keyframes semPulseGlow {
    0% {
        box-shadow: 0 0 0 0 rgba(33, 115, 70, 0.7);
        border-color: #217346 !important;
        background-color: #f0fdf4 !important;
    }
    40% {
        box-shadow: 0 0 0 6px rgba(33, 115, 70, 0.2);
        border-color: #16a34a !important;
        background-color: #dcfce7 !important;
    }
    100% {
        box-shadow: 0 0 0 0 rgba(33, 115, 70, 0);
        border-color: var(--line-border) !important;
        background-color: #ffffff !important;
    }
}
.sem-auto-pulse {
    animation: semPulseGlow 1.8s ease-in-out !important;
}

/* Standardized Theme Button & Hover Rules (High Contrast Legibility) */
.btn-outline-secondary {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
}
.btn-outline-secondary:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-outline-secondary:hover * {
    color: #0f172a !important;
}
.btn-secondary {
    background: #e2e8f0 !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
}
.btn-secondary:hover {
    background: #cbd5e1 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-secondary:hover * {
    color: #0f172a !important;
}
.btn-outline-primary {
    background: #ffffff !important;
    border: 1px solid #0284c7 !important;
    color: #0284c7 !important;
}
.btn-outline-primary:hover {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #ffffff !important;
}
.btn-outline-primary:hover * {
    color: #ffffff !important;
}
.btn-outline-success {
    background: #ffffff !important;
    border: 1px solid #16a34a !important;
    color: #166534 !important;
}
.btn-outline-success:hover {
    background: #16a34a !important;
    border-color: #16a34a !important;
    color: #ffffff !important;
}
.btn-outline-success:hover * {
    color: #ffffff !important;
}
.btn-outline-danger {
    background: #ffffff !important;
    border: 1px solid #ef4444 !important;
    color: #dc2626 !important;
}
.btn-outline-danger:hover {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
}
.btn-outline-danger:hover * {
    color: #ffffff !important;
}
.btn-matrix-tool:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-matrix-tool:hover * {
    color: #0f172a !important;
}
.btn-matrix-tool:hover .text-success { color: #16a34a !important; }
.btn-matrix-tool:hover .text-primary { color: #0284c7 !important; }
.btn-matrix-tool:hover .text-secondary { color: #64748b !important; }
.btn-matrix-tool:hover .text-danger { color: #dc2626 !important; }
.filter-pill-btn:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
.filter-pill-btn.active:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
    border-color: var(--green-excel-dark) !important;
}
.sem-pill-btn:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
.sem-pill-btn.active:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
    border-color: var(--green-excel-dark) !important;
}
.mode-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}
.mode-toggle-btn.active:hover {
    background: #ffffff !important;
    color: var(--green-excel-dark) !important;
}

</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            <div class="card fees-progression-page-card">

                <!-- Excel Green Ribbon Header (Standard ERP 14.5px Titlebar) -->
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-forward"></i> Batch Semester Progression &amp; Fee Rollover
                        <span class="badge badge-light text-dark ml-2" style="font-size: 10px; font-weight: 700; border: 1px solid rgba(255,255,255,0.4);">Executive Matrix</span>
                    </h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 6px;">
                        <button type="button" class="btn-header-light" onclick="openCommonHeadModal()" title="Common Fee Head Master">
                            <i class="fa fa-cubes text-warning mr-1"></i> Common Fee Head
                        </button>
                        <a href="{{ route('college.fees.structures') }}" class="btn-header-light" title="Fee Matrix & Ledger">
                            <i class="fa fa-table text-primary mr-1"></i> Fee Matrix
                        </a>
                        <a href="{{ route('college.fees.dashboard') }}" class="btn-header-light" title="Fees Dashboard">
                            <i class="fa fa-dashboard text-info mr-1"></i> Dashboard
                        </a>
                    </div>
                </div>

                <!-- Top 5 KPI Metrics Strip -->
                <div class="progression-kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-card-label">
                            <span>Eligible Students</span>
                            <i class="fa fa-users text-muted"></i>
                        </div>
                        <div class="kpi-card-value text-dark" id="kpiTotalStudents">{{ $summary['total'] ?? 0 }} Students</div>
                        <div class="kpi-card-sub">Enrolled in current selection</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-card-label">
                            <span>Zero Dues (Cleared)</span>
                            <i class="fa fa-check-circle text-success"></i>
                        </div>
                        <div class="kpi-card-value text-success" id="kpiClearedStudents">{{ $summary['cleared'] ?? 0 }} Students</div>
                        <div class="kpi-card-sub">Ready for clean semester upgrade</div>
                    </div>
                    <div class="kpi-card kpi-danger">
                        <div class="kpi-card-label">
                            <span>Pending Dues</span>
                            <i class="fa fa-exclamation-circle text-danger"></i>
                        </div>
                        <div class="kpi-card-value text-danger" id="kpiDuesStudents">{{ $summary['dues'] ?? 0 }} Students</div>
                        <div class="kpi-card-sub" id="kpiDuesSub">₹{{ number_format($summary['total_dues'] ?? 0, 2) }} carryover</div>
                    </div>
                    <div class="kpi-card kpi-info">
                        <div class="kpi-card-label">
                            <span>Advance Credit</span>
                            <i class="fa fa-arrow-down text-primary"></i>
                        </div>
                        <div class="kpi-card-value text-primary" id="kpiAdvanceStudents">{{ $summary['advance'] ?? 0 }} Students</div>
                        <div class="kpi-card-sub" id="kpiAdvanceSub">₹{{ number_format($summary['total_advance'] ?? 0, 2) }} auto-credit</div>
                    </div>
                    <div class="kpi-card kpi-purple">
                        <div class="kpi-card-label">
                            <span>Selected for Upgrade</span>
                            <i class="fa fa-check-square-o text-indigo"></i>
                        </div>
                        <div class="kpi-card-value" style="color: #6366f1;" id="kpiSelectedStudents">0 / {{ $summary['total'] ?? 0 }}</div>
                        <div class="kpi-card-sub" id="kpiSelectedSub">Select students below</div>
                    </div>
                </div>

                <!-- Unified Filter Toolbar (Excel 29px Input Strip) -->
                <div class="progression-filter-toolbar">
                    <div class="form-row align-items-end">
                        
                        <div class="col-md-3 mb-1">
                            <label class="toolbar-label">Course</label>
                            <select id="prog_course_id" class="toolbar-control" onchange="onCourseOrBatchChange()">
                                <option value="">-- Choose Course --</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" data-sems="{{ $c->total_semester ?: 6 }}" {{ $c->id == $courseId ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->total_semester ?: 6 }} Semesters)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <label class="toolbar-label">Batch</label>
                            <select id="prog_batch" class="toolbar-control" onchange="onCourseOrBatchChange()">
                                @for($y = (int)date('Y') + 1; $y >= 2022; $y--)
                                    <option value="{{ $y }}" {{ $batch == $y ? 'selected' : '' }}>Batch {{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="toolbar-label">
                                Source Semester (From)
                                <span id="autoDetectedSemBadge" class="badge badge-light border text-success ml-1" style="font-size: 9px; display: none;">
                                    <i class="fa fa-magic mr-1"></i>Auto
                                </span>
                            </label>
                            <select id="prog_from_semester" class="toolbar-control" onchange="onFromSemesterChange()">
                                @for($s = 1; $s <= ($maxSemesters ?? 6); $s++)
                                    <option value="{{ $s }}" {{ $s == $fromSemester ? 'selected' : '' }}>Semester {{ $s }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <label class="toolbar-label">Target Semester (To)</label>
                            <select id="prog_to_semester" class="toolbar-control" onchange="onTargetSemesterChange()">
                                @for($s = 1; $s <= ($maxSemesters ?? 6); $s++)
                                    <option value="{{ $s }}" {{ $s == $toSemester ? 'selected' : '' }}>Semester {{ $s }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <label class="toolbar-label d-none d-md-block">&nbsp;</label>
                            <button type="button" class="btn-search-action w-100" onclick="loadProgressionStudents()" title="Refresh Roster">
                                <i class="fa fa-refresh mr-1"></i> Reload
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Excel Spreadsheet Card -->
                <div class="matrix-container-card">

                    <!-- Action Bar Above Spreadsheet -->
                    <div class="matrix-action-bar">
                        
                        <!-- Left: Live Search & Filter Pills -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            
                            <!-- Search Field -->
                            <div class="search-input-wrapper">
                                <i class="fa fa-search"></i>
                                <input type="text" id="progressionSearchInput" class="search-input-field" placeholder="Search by name, adm no, mobile..." oninput="onProgressionSearch()">
                            </div>

                            <!-- Filter Pills -->
                            <div class="d-flex align-items-center flex-wrap" style="gap: 4px;">
                                <button type="button" class="filter-pill-btn active" data-filter="all" onclick="applyStatusFilter('all', this)">
                                    All (<span id="pillCountAll">{{ $summary['total'] ?? 0 }}</span>)
                                </button>
                                <button type="button" class="filter-pill-btn" data-filter="upgraded" onclick="applyStatusFilter('upgraded', this)">
                                    <i class="fa fa-check-circle text-success"></i> Upgraded (<span id="pillCountUpgraded">{{ $summary['upgraded'] ?? 0 }}</span>)
                                </button>
                                <button type="button" class="filter-pill-btn" data-filter="pending" onclick="applyStatusFilter('pending', this)">
                                    <i class="fa fa-clock-o text-warning"></i> Pending (<span id="pillCountPending">{{ $summary['pending'] ?? 0 }}</span>)
                                </button>
                                <button type="button" class="filter-pill-btn" data-filter="cleared" onclick="applyStatusFilter('cleared', this)">
                                    <i class="fa fa-check text-success"></i> Cleared (<span id="pillCountCleared">{{ $summary['cleared'] ?? 0 }}</span>)
                                </button>
                                <button type="button" class="filter-pill-btn" data-filter="dues" onclick="applyStatusFilter('dues', this)">
                                    <i class="fa fa-exclamation-triangle text-danger"></i> Dues (<span id="pillCountDues">{{ $summary['dues'] ?? 0 }}</span>)
                                </button>
                                <button type="button" class="filter-pill-btn" data-filter="advance" onclick="applyStatusFilter('advance', this)">
                                    <i class="fa fa-arrow-down text-primary"></i> Advance (<span id="pillCountAdvance">{{ $summary['advance'] ?? 0 }}</span>)
                                </button>
                                <button type="button" class="filter-pill-btn" data-filter="selected" onclick="applyStatusFilter('selected', this)">
                                    <i class="fa fa-check-square-o text-indigo"></i> Selected (<span id="pillCountSelected">0</span>)
                                </button>
                            </div>

                        </div>

                        <!-- Right: Fast Selection Tools & Primary Action -->
                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                            
                            <!-- Selection Shortcuts -->
                            <button type="button" class="btn-matrix-tool" onclick="selectAll(true)" title="Select all filtered students">
                                <i class="fa fa-check-square-o text-success mr-1"></i> Select All
                            </button>
                            <button type="button" class="btn-matrix-tool" onclick="selectAll(false)" title="Uncheck all students">
                                <i class="fa fa-square-o text-secondary mr-1"></i> Clear
                            </button>
                            <button type="button" class="btn-matrix-tool" onclick="selectClearedOnly()" title="Select only students with zero dues">
                                <i class="fa fa-filter text-primary mr-1"></i> Zero Dues Only
                            </button>

                            <!-- Primary Execution Button -->
                            <button type="button" id="btnExecuteProgression" class="btn-execute-progression" onclick="openConfirmProgressionModal()">
                                <i class="fa fa-arrow-circle-right"></i>
                                <span>Upgrade <strong id="btnSelectedCount">0</strong> Students to Sem <strong id="btnTargetSem">{{ $toSemester }}</strong></span>
                            </button>

                        </div>

                    </div>

                    <!-- Banner displayed when all students in this selection are already upgraded -->
                    <div id="alertAllUpgradedBanner" class="alert alert-success d-flex align-items-center justify-content-between p-2 mb-2" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 12px; color: #166534; {{ (!empty($summary['upgraded']) && empty($summary['pending'])) ? '' : 'display: none !important;' }}">
                        <div>
                            <i class="fa fa-check-circle mr-1" style="font-size: 14px;"></i>
                            <strong>Progression Already Completed:</strong> All <span id="bannerUpgradedCount">{{ $summary['upgraded'] ?? 0 }}</span> students have already been upgraded from <strong>Semester <span id="bannerFromSem">{{ $fromSemester }}</span></strong> to <strong>Semester <span id="bannerToSem">{{ $toSemester }}</span></strong>.
                        </div>
                        <div>
                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                <i class="fa fa-graduation-cap mr-1"></i> Active in Sem <span id="bannerTargetSem">{{ $toSemester }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Spreadsheet Table -->
                    <div class="table-responsive" style="max-height: calc(100vh - 290px); overflow-y: auto;">
                        <table class="table matrix-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 38px;" class="text-center">
                                        <input type="checkbox" id="masterCheckbox" onchange="toggleMasterCheckbox(this)" checked title="Select / Deselect All">
                                    </th>
                                    <th style="width: 45px;" class="text-center">#</th>
                                    <th style="width: 120px;">Adm No</th>
                                    <th>Student Details</th>
                                    <th>Father's Name</th>
                                    <th style="width: 110px;">Mobile</th>
                                    <th style="width: 100px;" class="text-center">Current</th>
                                    <th style="width: 100px;" class="text-center">Target</th>
                                    <th style="width: 130px;" class="text-right" id="thBalanceHeader">Sem {{ $fromSemester }} Balance</th>
                                    <th style="width: 160px;" class="text-center">Rollover Action</th>
                                    <th style="width: 90px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="progressionTableBody">
                                @forelse($students as $idx => $stu)
                                    @php
                                        $isUpgraded = !empty($stu->is_upgraded);
                                        $isDeactive = !empty($stu->is_deactive);
                                    @endphp
                                    <tr class="student-row {{ $isDeactive ? 'student-row-deactive text-muted' : '' }} {{ $isUpgraded ? 'student-row-upgraded' : '' }}" 
                                        data-id="{{ $stu->id }}"
                                        data-name="{{ strtolower($stu->name) }}"
                                        data-adm="{{ strtolower($stu->admissionNo) }}"
                                        data-father="{{ strtolower($stu->father_name ?? '') }}"
                                        data-mobile="{{ strtolower($stu->mobile ?? '') }}"
                                        data-is-deactive="{{ $isDeactive ? '1' : '0' }}"
                                        data-is-upgraded="{{ $isUpgraded ? '1' : '0' }}"
                                        data-balance-type="{{ $stu->balance > 0 ? 'dues' : ($stu->balance < 0 ? 'advance' : 'cleared') }}">
                                        
                                        <td class="text-center">
                                            @if($isDeactive)
                                                <input type="checkbox" class="student-checkbox" value="{{ $stu->id }}" disabled title="Deactivated student cannot be progressed">
                                            @elseif($isUpgraded)
                                                <input type="checkbox" class="student-checkbox" value="{{ $stu->id }}" disabled style="opacity: 0.35; cursor: not-allowed;" title="Already upgraded to Semester {{ $toSemester }}">
                                            @else
                                                <input type="checkbox" class="student-checkbox" value="{{ $stu->id }}" onchange="updateSelectedCounters()" checked>
                                            @endif
                                        </td>
                                        <td class="text-center text-muted font-weight-bold">{{ $idx + 1 }}</td>
                                        <td>
                                            <span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-size: 11px;">
                                                {{ $stu->admissionNo ?: 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: {{ $isDeactive ? '#64748b' : '#0f172a' }};">
                                                {{ $stu->name }}
                                                @if($isDeactive)
                                                    <span class="badge badge-secondary ml-1" style="font-size: 9px; font-weight: 700; background: #94a3b8; color: #ffffff;" title="Student is deactive in Admissions">Deactive</span>
                                                @elseif($isUpgraded)
                                                    <span class="badge badge-success ml-1 font-weight-bold" style="font-size: 9px; background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;" title="Progression to Semester {{ $toSemester }} completed">Upgraded</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                <span class="badge badge-light border">{{ $stu->gender }}</span>
                                                <span class="ml-1">Batch {{ $stu->batch }}</span>
                                                @if(!empty($stu->class_name))
                                                    <span class="ml-1 text-info font-weight-bold">({{ $stu->class_name }})</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>{{ $stu->father_name ?: 'N/A' }}</td>
                                        <td>{{ $stu->mobile ?: 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($isUpgraded)
                                                <span class="badge badge-success font-weight-bold" style="font-size: 10.5px; background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;" title="Current semester is {{ $stu->actual_current_sem ?? $toSemester }}">
                                                    <i class="fa fa-check mr-1"></i>Sem {{ $stu->actual_current_sem ?? $toSemester }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary" style="font-size: 10.5px;">Sem {{ $fromSemester }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isUpgraded)
                                                <span class="badge badge-light border text-muted" style="font-size: 10.5px;" title="Already upgraded to Semester {{ $toSemester }}">
                                                    <i class="fa fa-check text-success mr-1"></i>Completed
                                                </span>
                                            @else
                                                <span class="badge badge-success" style="font-size: 10.5px;">Sem {{ $toSemester }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            @if($stu->balance > 0)
                                                <span class="text-danger">Due: ₹{{ number_format($stu->balance, 2) }}</span>
                                            @elseif($stu->balance < 0)
                                                <span class="text-primary">Adv: ₹{{ number_format(abs($stu->balance), 2) }}</span>
                                            @else
                                                <span class="text-success"><i class="fa fa-check-circle"></i> Cleared</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isUpgraded)
                                                <span class="badge badge-success font-weight-bold" style="font-size: 10.5px; padding: 3px 8px; background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;" title="Progression to Semester {{ $toSemester }} already completed">
                                                    <i class="fa fa-check-circle text-success mr-1"></i> Upgraded to Sem {{ $toSemester }}
                                                </span>
                                            @elseif($isDeactive)
                                                <span class="badge badge-secondary" style="font-size: 10.5px; opacity: 0.8;" title="Deactivated student - progression suspended">
                                                    <i class="fa fa-ban mr-1"></i> Disabled
                                                </span>
                                            @elseif($stu->balance > 0)
                                                <span class="badge badge-warning" style="font-size: 10.5px;" title="Carry forward to next semester fee ledger">
                                                    <i class="fa fa-history mr-1"></i> Carries Dues
                                                </span>
                                            @elseif($stu->balance < 0)
                                                <span class="badge badge-info" style="font-size: 10.5px;" title="Carried forward to next semester as negative Previous Dues">
                                                    <i class="fa fa-minus-circle mr-1"></i> Carries Adv
                                                </span>
                                            @else
                                                <span class="badge badge-success" style="font-size: 10.5px;" title="Clean new semester fee assignment">
                                                    <i class="fa fa-check mr-1"></i> Standard New
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($isUpgraded)
                                                <span class="badge badge-light border text-success font-weight-bold px-2 py-1" style="font-size: 10.5px; background: #f0fdf4; border-color: #bbf7d0 !important;" title="Progression already done">
                                                    <i class="fa fa-check text-success mr-1"></i> Upgraded
                                                </span>
                                            @elseif($isDeactive)
                                                <button type="button" class="btn btn-xs btn-light border text-muted" disabled style="opacity: 0.6; cursor: not-allowed; font-size: 10px; padding: 2px 7px;">
                                                    <i class="fa fa-ban mr-1"></i> Disabled
                                                </button>
                                            @else
                                                <button type="button" class="btn-quick-single-progress" onclick="progressSingleStudent({{ $stu->id }}, '{{ addslashes($stu->name) }}')" title="Upgrade this student only">
                                                    <i class="fa fa-arrow-right"></i> Upgrade
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="fa fa-graduation-cap fa-3x text-muted mb-2" style="opacity: 0.35;"></i>
                                            <div class="font-weight-bold" style="font-size: 13px;">No active students found for this Course &amp; Batch selection.</div>
                                            <small>Please select a Course and Batch from the toolbar above to load students.</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </section>
</div>

<!-- ==========================================
     MODAL: Preview Target Fee Structure
     ========================================== -->
<div class="modal fade" id="modalTargetFeeStructure" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-cubes"></i> Target Semester Fee Heads Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark" id="modalTargetCourseLabel">Course: -</strong><br>
                        <small class="text-muted" id="modalTargetSemLabel">Target: Semester 2</small>
                    </div>
                    <div>
                        <a href="{{ route('college.fees.structures') }}" class="btn btn-xs btn-outline-success font-weight-bold" target="_blank">
                            <i class="fa fa-external-link mr-1"></i> Open Fee Matrix
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table matrix-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fee Head Name</th>
                                <th class="text-right">Boys Fee</th>
                                <th class="text-right">Girls Fee</th>
                            </tr>
                        </thead>
                        <tbody id="targetStructuresModalBody">
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">Loading target fee heads...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: Confirm Bulk Semester Progression
     ========================================== -->
<div class="modal fade" id="modalConfirmProgression" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-graduation-cap"></i> Confirm Semester Progression
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-start mb-3">
                    <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-arrow-up text-success" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Progress Selected Students</strong>
                        <span class="text-muted" style="font-size: 11.5px;">Please review the progression parameters below:</span>
                    </div>
                </div>

                <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Target Course:</span>
                        <strong class="text-dark" id="confModalCourse">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Batch:</span>
                        <strong class="text-dark" id="confModalBatch">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Semester Progression:</span>
                        <strong class="text-success font-weight-bold" id="confModalSemRange">Sem 1 &rarr; Sem 2</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Students Selected:</span>
                        <strong class="text-success font-weight-bold" id="confModalStudentCount">0 Students</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Students Carrying Dues:</span>
                        <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700; border: 1px solid #fde68a;" id="confModalDuesCount">0 Students</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Students With Advance:</span>
                        <span class="badge" style="background: #e0f2fe; color: #0284c7; font-weight: 700; border: 1px solid #bae6fd;" id="confModalAdvCount">0 Students</span>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mb-2" id="progressionInfoNote" style="font-size: 11px; border-radius: 2px; background: #e7eff5; border-color: #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-1"></i> Target semester standard fee structures will be assigned automatically. Previous semester dues will carry forward, and advance balances will be added as negative Previous Dues.
                </div>

                <div class="text-danger small font-weight-bold mb-0" style="font-size: 11px;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> Please verify that the target semester fee structures have been configured before proceeding.
                </div>

                <!-- Live Execution Progress & Status Container -->
                <div id="progressionProgressContainer" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small font-weight-bold text-dark" id="progressionProgressText">
                            <i class="fa fa-spinner fa-spin text-success mr-1"></i> Processing progression securely...
                        </span>
                        <span class="badge badge-light border text-muted" id="progressionAtomicBadge" style="font-size: 10px;">
                            <i class="fa fa-shield text-success mr-1"></i> Atomic Safe
                        </span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 4px; background: #e2e8f0;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="progressionProgressBar" role="progressbar" style="width: 100%;"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                        <i class="fa fa-info-circle mr-1"></i> In case of any interruption or timeout, automatic database rollback ensures zero data loss.
                    </small>
                </div>

                <div id="progressionErrorAlert" class="alert alert-danger py-2 px-3 mt-3 mb-0" style="font-size: 11.5px; border-radius: 4px; display: none;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> <span id="progressionErrorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" id="btnCancelProgression" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmExecute" class="btn btn-search-action px-3" onclick="executeConfirmedProgression()">
                    <i class="fa fa-check mr-1"></i> Confirm &amp; Progress Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Common Fee Head Modal -->
@include('college_fees.partials.common_head_modal')

<!-- ==========================================
     MODAL: Auto Semester Detected Notification
     ========================================== -->
<div class="modal fade" id="modalAutoSemesterDetected" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;" role="document">
        <div class="modal-content shadow-lg" style="border: 1px solid #16a34a; border-radius: 6px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background: #217346; color: #ffffff;">
                <h6 class="modal-title font-weight-bold d-flex align-items-center mb-0" style="font-size: 13.5px;">
                    <i class="fa fa-magic mr-2" style="color: #fef08a;"></i> Active Semester Detected
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 text-center">
                <div class="mb-2">
                    <span class="badge badge-light border px-2 py-1" style="font-size: 11.5px; color: #1e293b;">
                        <i class="fa fa-graduation-cap text-success mr-1"></i> <strong id="detectedModalCourse">Course</strong> &bull; <span id="detectedModalBatch">Batch</span>
                    </span>
                </div>

                <!-- Visual Progression Path -->
                <div class="d-flex align-items-center justify-content-center my-3 py-2 px-3 rounded" style="background: #f0fdf4; border: 1px dashed #86efac; gap: 12px;">
                    <div class="text-center">
                        <small class="text-muted d-block font-weight-bold" style="font-size: 10px; text-transform: uppercase;">Current / Active</small>
                        <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 13px;" id="detectedModalCurSem">Semester -</span>
                    </div>
                    <div>
                        <i class="fa fa-arrow-right text-success" style="font-size: 20px;"></i>
                    </div>
                    <div class="text-center">
                        <small class="text-muted d-block font-weight-bold" style="font-size: 10px; text-transform: uppercase;">Target Upgrade</small>
                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size: 13px;" id="detectedModalTgtSem">Semester -</span>
                    </div>
                </div>

                <div class="text-muted mb-2" style="font-size: 11.5px; line-height: 1.4;" id="detectedModalReason">
                    Active students found in this semester. Dropdowns and student roster have been updated automatically.
                </div>

                <div class="d-flex justify-content-center align-items-center" style="gap: 6px; font-size: 11px;">
                    <span class="badge badge-light border text-muted">
                        <i class="fa fa-users text-primary mr-1"></i> <span id="detectedModalStudentCount">0 Students</span>
                    </span>
                    <span class="badge badge-light border text-success">
                        <i class="fa fa-clock-o mr-1"></i> Auto-closing in <strong id="autoCloseTimer">2</strong>s
                    </span>
                </div>
            </div>
            <div class="modal-footer py-1 px-3 bg-light justify-content-between">
                <small class="text-muted" style="font-size: 10.5px;">You can change semesters manually if needed</small>
                <button type="button" class="btn btn-search-action btn-sm px-3" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fa fa-check mr-1"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>


<script>
// State Store
var currentStudents = @json($students ?? []);
var currentTargetStructures = @json($targetStructures ?? []);
var currentActiveFilter = 'all';

$(document).ready(function() {
    updateSelectedCounters();
    refreshTargetStructureInfo();
});

function showToast(type, msg) {
    if (typeof toastr !== 'undefined') {
        toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: 3500 };
        if (type === 'success') toastr.success(msg);
        else if (type === 'error') toastr.error(msg);
        else toastr.info(msg);
    } else {
        alert(msg);
    }
}

// Master Checkbox
function toggleMasterCheckbox(master) {
    var isChecked = master.checked;
    master.indeterminate = false;
    $('.student-row:visible:not([data-is-deactive="1"]):not([data-is-upgraded="1"]) .student-checkbox:not(:disabled)').prop('checked', isChecked);
    updateSelectedCounters();
}

function selectAll(check) {
    if (check) {
        $('.student-row:visible:not([data-is-deactive="1"]):not([data-is-upgraded="1"]) .student-checkbox:not(:disabled)').prop('checked', true);
    } else {
        $('.student-row .student-checkbox').prop('checked', false);
    }
    var master = $('#masterCheckbox')[0];
    if (master) {
        master.checked = check;
        master.indeterminate = false;
    }
    updateSelectedCounters();
}

function selectClearedOnly() {
    $('.student-row').each(function() {
        var isDeactive = $(this).attr('data-is-deactive') === '1';
        var isUpgraded = $(this).attr('data-is-upgraded') === '1';
        if (isDeactive || isUpgraded) {
            $(this).find('.student-checkbox').prop('checked', false);
            return;
        }
        var bType = $(this).attr('data-balance-type');
        var isZeroDues = (bType === 'cleared' || bType === 'advance');
        $(this).find('.student-checkbox:not(:disabled)').prop('checked', isZeroDues);
    });
    updateSelectedCounters();
}

// Live Update of Counters
function updateSelectedCounters() {
    var totalVisiblePending = $('.student-row:visible:not([data-is-deactive="1"]):not([data-is-upgraded="1"])').length;
    var totalChecked = $('.student-checkbox:checked:not(:disabled)').length;
    var totalAll = $('.student-row').length;
    var upgradedCount = $('.student-row[data-is-upgraded="1"]').length;
    var pendingCount = $('.student-row:not([data-is-upgraded="1"]):not([data-is-deactive="1"])').length;

    // Filter pill counts
    var clearedCount = $('.student-row[data-balance-type="cleared"]:not([data-is-upgraded="1"])').length;
    var duesCount = $('.student-row[data-balance-type="dues"]:not([data-is-upgraded="1"])').length;
    var advCount = $('.student-row[data-balance-type="advance"]:not([data-is-upgraded="1"])').length;

    $('#pillCountAll').text(totalAll);
    $('#pillCountUpgraded').text(upgradedCount);
    $('#pillCountPending').text(pendingCount);
    $('#pillCountCleared').text(clearedCount);
    $('#pillCountDues').text(duesCount);
    $('#pillCountAdvance').text(advCount);
    $('#pillCountSelected').text(totalChecked);

    // Update Master Checkbox Indeterminate state
    var master = $('#masterCheckbox')[0];
    if (master) {
        if (totalChecked === 0) {
            master.checked = false;
            master.indeterminate = false;
        } else if (totalChecked === totalVisiblePending && totalVisiblePending > 0) {
            master.checked = true;
            master.indeterminate = false;
        } else {
            master.checked = false;
            master.indeterminate = (totalChecked > 0);
        }
    }

    // KPI Selected
    $('#kpiSelectedStudents').text(totalChecked + ' / ' + (pendingCount > 0 ? pendingCount : totalAll));
    if (totalChecked > 0) {
        $('#kpiSelectedSub').text(totalChecked + ' students queued');
    } else if (upgradedCount > 0 && pendingCount === 0) {
        $('#kpiSelectedSub').text('All students upgraded');
    } else {
        $('#kpiSelectedSub').text('No students selected');
    }

    // Button Selected
    var toSem = $('#prog_to_semester').val() || 2;
    $('#btnTargetSem').text(toSem);

    if (totalChecked === 0) {
        $('#btnExecuteProgression').prop('disabled', true);
        if (upgradedCount > 0 && pendingCount === 0) {
            $('#btnExecuteProgression').html('<i class="fa fa-check-circle mr-1"></i> <span>All <strong>' + upgradedCount + '</strong> Students Upgraded to Sem <strong>' + toSem + '</strong></span>');
        } else {
            $('#btnExecuteProgression').html('<i class="fa fa-arrow-circle-right mr-1"></i> <span>Upgrade <strong id="btnSelectedCount">0</strong> Students to Sem <strong id="btnTargetSem">' + toSem + '</strong></span>');
        }
    } else {
        $('#btnExecuteProgression').prop('disabled', false).html('<i class="fa fa-arrow-circle-right"></i> <span>Upgrade <strong id="btnSelectedCount">' + totalChecked + '</strong> Students to Sem <strong id="btnTargetSem">' + toSem + '</strong></span>');
    }
}

// Instant Filter Pills
function applyStatusFilter(filterType, btn) {
    currentActiveFilter = filterType;
    $('.filter-pill-btn').removeClass('active');
    $(btn).addClass('active');
    filterTableRows();
}

// Search Filter
function onProgressionSearch() {
    filterTableRows();
}

function filterTableRows() {
    var query = ($('#progressionSearchInput').val() || '').toLowerCase().trim();

    $('.student-row').each(function() {
        var row = $(this);
        var name = row.attr('data-name') || '';
        var adm = row.attr('data-adm') || '';
        var father = row.attr('data-father') || '';
        var mobile = row.attr('data-mobile') || '';
        var bType = row.attr('data-balance-type') || '';
        var isUpgraded = row.attr('data-is-upgraded') === '1';
        var isDeactive = row.attr('data-is-deactive') === '1';
        var isChecked = row.find('.student-checkbox').is(':checked');

        var matchesQuery = !query || name.indexOf(query) !== -1 || adm.indexOf(query) !== -1 || father.indexOf(query) !== -1 || mobile.indexOf(query) !== -1;
        var matchesFilter = true;

        if (currentActiveFilter === 'upgraded') matchesFilter = isUpgraded;
        else if (currentActiveFilter === 'pending') matchesFilter = (!isUpgraded && !isDeactive);
        else if (currentActiveFilter === 'cleared') matchesFilter = (bType === 'cleared');
        else if (currentActiveFilter === 'dues') matchesFilter = (bType === 'dues');
        else if (currentActiveFilter === 'advance') matchesFilter = (bType === 'advance');
        else if (currentActiveFilter === 'selected') matchesFilter = isChecked;

        if (matchesQuery && matchesFilter) {
            row.show();
        } else {
            row.hide();
        }
    });

    updateSelectedCounters();
}

// Filters Change
function onFromSemesterChange() {
    var fromSem = parseInt($('#prog_from_semester').val()) || 1;
    var toSem = fromSem + 1;
    $('#prog_to_semester').val(toSem);
    $('#thBalanceHeader').text('Sem ' + fromSem + ' Balance');
    $('#btnTargetSem').text(toSem);
    loadProgressionStudents();
}

function onTargetSemesterChange() {
    var toSem = $('#prog_to_semester').val() || 2;
    $('#btnTargetSem').text(toSem);
    loadProgressionStudents();
}

var autoCloseInterval = null;

function onCourseOrBatchChange() {
    var courseId = $('#prog_course_id').val();
    var batch = $('#prog_batch').val();

    if (!courseId) {
        renderStudentsRoster([], []);
        return;
    }

    var opt = $('#prog_course_id').find(':selected');
    var maxSem = parseInt(opt.data('sems')) || 6;

    // Show detecting state on badge
    $('#autoDetectedSemBadge').html('<i class="fa fa-spinner fa-spin mr-1"></i>Detecting...').show();

    $.ajax({
        url: "{{ route('college.fees.progression.detect_batch_sem') }}",
        type: "GET",
        data: {
            course_id: courseId,
            batch: batch
        },
        dataType: 'json',
        success: function(res) {
            var curSem = 1;
            var tgtSem = 2;
            var courseMaxSem = maxSem;

            if (res.status === 'success') {
                curSem = parseInt(res.current_semester) || 1;
                tgtSem = parseInt(res.target_semester) || Math.min(curSem + 1, maxSem);
                if (res.max_semesters) courseMaxSem = parseInt(res.max_semesters);

                // Populate modal content
                $('#detectedModalCourse').text(res.course_name || opt.text().trim());
                $('#detectedModalBatch').text('Batch ' + res.batch);
                $('#detectedModalCurSem').text('Semester ' + curSem);
                $('#detectedModalTgtSem').text('Semester ' + tgtSem);
                $('#detectedModalStudentCount').text((res.student_count || 0) + ' Students');
                if (res.reason) {
                    $('#detectedModalReason').text(res.reason);
                } else {
                    $('#detectedModalReason').text('Active batch detected in Semester ' + curSem + '. Target upgrade set to Semester ' + tgtSem + '.');
                }

                // Show modal & start 2-second countdown
                $('#modalAutoSemesterDetected').modal('show');
                var countdown = 2;
                $('#autoCloseTimer').text(countdown);
                if (autoCloseInterval) clearInterval(autoCloseInterval);
                autoCloseInterval = setInterval(function() {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(autoCloseInterval);
                        $('#modalAutoSemesterDetected').modal('hide');
                    } else {
                        $('#autoCloseTimer').text(countdown);
                    }
                }, 1000);
            }

            // Rebuild dropdown options dynamically
            var fromHtml = '', toHtml = '';
            for (var s = 1; s <= courseMaxSem; s++) {
                fromHtml += '<option value="' + s + '" ' + (s === curSem ? 'selected' : '') + '>Semester ' + s + '</option>';
                toHtml += '<option value="' + s + '" ' + (s === tgtSem ? 'selected' : '') + '>Semester ' + s + '</option>';
            }
            $('#prog_from_semester').html(fromHtml).val(curSem);
            $('#prog_to_semester').html(toHtml).val(tgtSem);

            // Add pulse glow animation to highlight detected semesters
            $('#prog_from_semester, #prog_to_semester').removeClass('sem-auto-pulse');
            setTimeout(function() {
                $('#prog_from_semester, #prog_to_semester').addClass('sem-auto-pulse');
            }, 60);

            $('#autoDetectedSemBadge').html('<i class="fa fa-magic mr-1"></i>Auto').show().fadeOut(3500);

            // Reload students roster
            loadProgressionStudents();
        },
        error: function() {
            $('#autoDetectedSemBadge').hide();
            // Fallback manual rebuild
            var fromHtml = '', toHtml = '';
            var curSem = parseInt($('#prog_from_semester').val()) || 1;
            if (curSem > maxSem) curSem = 1;
            for (var s = 1; s <= maxSem; s++) {
                fromHtml += '<option value="' + s + '" ' + (s === curSem ? 'selected' : '') + '>Semester ' + s + '</option>';
                toHtml += '<option value="' + s + '" ' + (s === Math.min(curSem + 1, maxSem) ? 'selected' : '') + '>Semester ' + s + '</option>';
            }
            $('#prog_from_semester').html(fromHtml);
            $('#prog_to_semester').html(toHtml);
            loadProgressionStudents();
        }
    });
}

function onProgressionFilterChange() {
    onCourseOrBatchChange();
}

// Load Students via AJAX
function loadProgressionStudents() {
    var courseId = $('#prog_course_id').val();
    var batch = $('#prog_batch').val();
    var fromSem = $('#prog_from_semester').val();
    var toSem = $('#prog_to_semester').val();

    if (!courseId) {
        renderStudentsRoster([], []);
        return;
    }

    $('#thBalanceHeader').text('Sem ' + fromSem + ' Balance');
    $('#btnTargetSem').text(toSem);
    $('#progressionTableBody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-success"></i><br><span class="text-muted mt-2 d-inline-block font-weight-bold">Loading student spreadsheet roster...</span></td></tr>');

    $.ajax({
        url: "{{ route('college.fees.progression') }}",
        type: "GET",
        data: {
            course_id: courseId,
            batch: batch,
            from_semester: fromSem,
            to_semester: toSem
        },
        dataType: 'json',
        success: function(res) {
            currentStudents = res.students || [];
            currentTargetStructures = res.target_structures || [];
            
            // Update KPI cards
            if (res.summary) {
                $('#kpiTotalStudents').text(res.summary.total + ' Students');
                $('#kpiClearedStudents').text(res.summary.cleared + ' Students');
                $('#kpiDuesStudents').text(res.summary.dues + ' Students');
                $('#kpiDuesSub').text('₹' + parseFloat(res.summary.total_dues || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' carryover');
                $('#kpiAdvanceStudents').text(res.summary.advance + ' Students');
                $('#kpiAdvanceSub').text('₹' + parseFloat(res.summary.total_advance || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' auto-credit');
            }

            // Update Progression Completed Banner
            var fromSem = $('#prog_from_semester').val();
            var toSem = $('#prog_to_semester').val();
            var upgradedCount = res.summary ? (res.summary.upgraded || 0) : 0;
            var pendingCount = res.summary ? (res.summary.pending || 0) : 0;

            if (upgradedCount > 0 && pendingCount === 0) {
                $('#bannerUpgradedCount').text(upgradedCount);
                $('#bannerFromSem').text(fromSem);
                $('#bannerToSem').text(toSem);
                $('#bannerTargetSem').text(toSem);
                $('#alertAllUpgradedBanner').attr('style', 'background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 12px; color: #166534; display: flex !important;');
            } else {
                $('#alertAllUpgradedBanner').attr('style', 'display: none !important;');
            }

            refreshTargetStructureInfo();
            renderStudentsRoster(currentStudents, currentTargetStructures);
        },
        error: function(xhr) {
            showToast('error', 'Failed to load students roster.');
            $('#progressionTableBody').html('<tr><td colspan="11" class="text-center text-danger py-4 font-weight-bold">Error loading roster. Please check network.</td></tr>');
        }
    });
}

function refreshTargetStructureInfo() {
    var count = (currentTargetStructures && currentTargetStructures.length) || 0;
    if ($('#lblTargetHeadsCount').length) {
        $('#lblTargetHeadsCount').text(count + ' Heads');
    }
    if ($('#btnTargetHeadsCount').length) {
        $('#btnTargetHeadsCount').text(count + ' Heads');
    }
    if ($('#targetHeadsBadgeText').length) {
        if (count === 0) {
            $('#targetHeadsBadgeText').html('<i class="fa fa-exclamation-triangle text-danger mr-1"></i> Target Fees: <strong class="text-danger">0 Configured</strong>');
        } else {
            $('#targetHeadsBadgeText').html('<i class="fa fa-cubes text-success mr-1"></i> Target Fees: <strong class="text-success">' + count + ' Heads</strong>');
        }
    }
}

// Render Spreadsheet Rows
function renderStudentsRoster(students, targetStructures) {
    var fromSem = $('#prog_from_semester').val() || 1;
    var toSem = $('#prog_to_semester').val() || 2;
    var tbody = '';

    if (!students || students.length === 0) {
        tbody = '<tr><td colspan="11" class="text-center py-5 text-muted">' +
                '<i class="fa fa-graduation-cap fa-3x text-muted mb-2" style="opacity: 0.35;"></i>' +
                '<div class="font-weight-bold" style="font-size: 13px;">No students found for this Course &amp; Batch selection.</div>' +
                '<small>Please select a Course and Batch from the toolbar above to load students.</small>' +
                '</td></tr>';
        $('#masterCheckbox').prop('checked', false);
    } else {
        students.forEach(function(stu, idx) {
            var isDeactive = !!stu.is_deactive;
            var isUpgraded = !!stu.is_upgraded;
            var bType = 'cleared';
            var balanceHtml = '<span class="text-success"><i class="fa fa-check-circle"></i> Cleared</span>';
            var rolloverBadge = '<span class="badge badge-success" style="font-size: 10.5px;" title="Clean new semester fee assignment"><i class="fa fa-check mr-1"></i> Standard New</span>';

            var bal = parseFloat(stu.balance || 0);
            if (bal > 0) {
                bType = 'dues';
                balanceHtml = '<span class="text-danger">Due: ₹' + bal.toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
                rolloverBadge = '<span class="badge badge-warning" style="font-size: 10.5px;" title="Carry forward to next semester fee ledger"><i class="fa fa-history mr-1"></i> Carries Dues</span>';
            } else if (bal < 0) {
                bType = 'advance';
                balanceHtml = '<span class="text-primary font-weight-bold">Adv: ₹' + Math.abs(bal).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
                rolloverBadge = '<span class="badge badge-info" style="font-size: 10.5px;" title="Carried forward to next semester as negative Previous Dues"><i class="fa fa-minus-circle mr-1"></i> Carries Adv</span>';
            }

            if (isUpgraded) {
                rolloverBadge = '<span class="badge badge-success font-weight-bold" style="font-size: 10.5px; padding: 3px 8px; background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;" title="Progression to Semester ' + toSem + ' already completed"><i class="fa fa-check-circle text-success mr-1"></i> Upgraded to Sem ' + toSem + '</span>';
            } else if (isDeactive) {
                rolloverBadge = '<span class="badge badge-secondary" style="font-size: 10.5px; opacity: 0.8;" title="Deactivated student - progression suspended"><i class="fa fa-ban mr-1"></i> Disabled</span>';
            }

            var safeName = (stu.name || '').replace(/'/g, "\\'");
            var rowClass = 'student-row' + (isDeactive ? ' student-row-deactive text-muted' : '') + (isUpgraded ? ' student-row-upgraded' : '');
            var chkHtml = '';
            if (isDeactive) {
                chkHtml = '<input type="checkbox" class="student-checkbox" value="' + stu.id + '" disabled title="Deactivated student cannot be progressed">';
            } else if (isUpgraded) {
                chkHtml = '<input type="checkbox" class="student-checkbox" value="' + stu.id + '" disabled style="opacity: 0.35; cursor: not-allowed;" title="Already upgraded to Semester ' + toSem + '">';
            } else {
                chkHtml = '<input type="checkbox" class="student-checkbox" value="' + stu.id + '" onchange="updateSelectedCounters()" checked>';
            }

            var statusBadge = '';
            if (isDeactive) {
                statusBadge = '<span class="badge badge-secondary ml-1" style="font-size: 9px; font-weight: 700; background: #94a3b8; color: #ffffff;" title="Student is deactive in Admissions">Deactive</span>';
            } else if (isUpgraded) {
                statusBadge = '<span class="badge badge-success ml-1 font-weight-bold" style="font-size: 9px; background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;" title="Progression to Semester ' + toSem + ' completed">Upgraded</span>';
            }

            var actionBtn = '';
            if (isDeactive) {
                actionBtn = '<button type="button" class="btn btn-xs btn-light border text-muted" disabled style="opacity: 0.6; cursor: not-allowed; font-size: 10px; padding: 2px 7px;"><i class="fa fa-ban mr-1"></i> Disabled</button>';
            } else if (isUpgraded) {
                actionBtn = '<span class="badge badge-light border text-success font-weight-bold px-2 py-1" style="font-size: 10.5px; background: #f0fdf4; border-color: #bbf7d0 !important;" title="Progression already done"><i class="fa fa-check text-success mr-1"></i> Upgraded</span>';
            } else {
                actionBtn = '<button type="button" class="btn-quick-single-progress" onclick="progressSingleStudent(' + stu.id + ', \'' + safeName + '\')" title="Upgrade this student only"><i class="fa fa-arrow-right"></i> Upgrade</button>';
            }

            var currentSemBadge = isUpgraded
                ? '<span class="badge badge-success font-weight-bold" style="font-size: 10.5px; background: #e7f5ec; color: #166534; border: 1px solid #bbf7d0;" title="Current semester is ' + (stu.actual_current_sem || toSem) + '"><i class="fa fa-check mr-1"></i>Sem ' + (stu.actual_current_sem || toSem) + '</span>'
                : '<span class="badge badge-secondary" style="font-size: 10.5px;">Sem ' + fromSem + '</span>';

            var targetSemBadge = isUpgraded
                ? '<span class="badge badge-light border text-muted" style="font-size: 10.5px;" title="Already upgraded to Semester ' + toSem + '"><i class="fa fa-check text-success mr-1"></i>Completed</span>'
                : '<span class="badge badge-success" style="font-size: 10.5px;">Sem ' + toSem + '</span>';

            var classNameText = stu.class_name ? '<span class="ml-1 text-info font-weight-bold">(' + stu.class_name + ')</span>' : '';
            var nameColor = isDeactive ? '#64748b' : '#0f172a';

            tbody += '<tr class="' + rowClass + '" ' +
                'data-id="' + stu.id + '" ' +
                'data-name="' + (stu.name || '').toLowerCase() + '" ' +
                'data-adm="' + (stu.admissionNo || '').toLowerCase() + '" ' +
                'data-father="' + (stu.father_name || '').toLowerCase() + '" ' +
                'data-mobile="' + (stu.mobile || '').toLowerCase() + '" ' +
                'data-is-deactive="' + (isDeactive ? '1' : '0') + '" ' +
                'data-is-upgraded="' + (isUpgraded ? '1' : '0') + '" ' +
                'data-balance-type="' + bType + '">' +
                '<td class="text-center">' + chkHtml + '</td>' +
                '<td class="text-center text-muted font-weight-bold">' + (idx + 1) + '</td>' +
                '<td>' +
                    '<span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-size: 11px;">' +
                        (stu.admissionNo || 'N/A') +
                    '</span>' +
                '</td>' +
                '<td>' +
                    '<div style="font-weight: 700; color: ' + nameColor + ';">' + stu.name + statusBadge + '</div>' +
                    '<small class="text-muted">' +
                        '<span class="badge badge-light border">' + (stu.gender || 'N/A') + '</span>' +
                        '<span class="ml-1">Batch ' + (stu.batch || '') + '</span>' +
                        classNameText +
                    '</small>' +
                '</td>' +
                '<td>' + (stu.father_name || 'N/A') + '</td>' +
                '<td>' + (stu.mobile || 'N/A') + '</td>' +
                '<td class="text-center">' + currentSemBadge + '</td>' +
                '<td class="text-center">' + targetSemBadge + '</td>' +
                '<td class="text-right font-weight-bold">' + balanceHtml + '</td>' +
                '<td class="text-center">' + rolloverBadge + '</td>' +
                '<td class="text-center">' + actionBtn + '</td>' +
            '</tr>';
        });
    }

    $('#progressionTableBody').html(tbody);
    filterTableRows();
}

function openTargetStructureModal() {
    var courseName = $('#prog_course_id').find(':selected').text() || 'Selected Course';
    var toSem = $('#prog_to_semester').val() || 2;

    $('#modalTargetCourseLabel').text('Course: ' + courseName);
    $('#modalTargetSemLabel').text('Target: Semester ' + toSem);

    var tbody = '';
    if (!currentTargetStructures || currentTargetStructures.length === 0) {
        tbody = '<tr><td colspan="4" class="text-center py-4 text-warning font-weight-bold">' +
                '<i class="fa fa-exclamation-triangle mr-1"></i> No fee structures configured for Semester ' + toSem + ' yet.<br>' +
                '<a href="{{ route('college.fees.structures') }}" class="btn btn-xs btn-primary mt-2" target="_blank">Configure in Fee Matrix</a>' +
                '</td></tr>';
    } else {
        currentTargetStructures.forEach(function(head, i) {
            tbody += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td><strong>' + head.head_name + '</strong></td>' +
                '<td class="text-right font-weight-bold text-dark">₹' + parseFloat(head.amount_boys || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-right font-weight-bold text-dark">₹' + parseFloat(head.amount_girls || 0).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</td>' +
            '</tr>';
        });
    }

    $('#targetStructuresModalBody').html(tbody);
    $('#modalTargetFeeStructure').modal('show');
}

// Open Confirmation Modal
function openConfirmProgressionModal() {
    var checkedBoxes = $('.student-checkbox:checked:not(:disabled)');
    if (checkedBoxes.length === 0) {
        showToast('error', 'Please select at least one student to progress.');
        return;
    }

    var courseName = $('#prog_course_id').find(':selected').text() || 'Course';
    var batch = $('#prog_batch').val();
    var fromSem = $('#prog_from_semester').val();
    var toSem = $('#prog_to_semester').val();

    var duesCount = 0;
    var advCount = 0;
    checkedBoxes.each(function() {
        var row = $(this).closest('.student-row');
        var bType = row.attr('data-balance-type');
        if (bType === 'dues') duesCount++;
        else if (bType === 'advance') advCount++;
    });

    $('#confModalCourse').text(courseName);
    $('#confModalBatch').text('Batch ' + batch);
    $('#confModalSemRange').html('Sem ' + fromSem + ' &rarr; Sem ' + toSem);
    $('#confModalStudentCount').text(checkedBoxes.length + ' Students');
    $('#confModalDuesCount').text(duesCount + ' Students');
    $('#confModalAdvCount').text(advCount + ' Students');

    $('#progressionProgressContainer').hide();
    $('#progressionErrorAlert').hide();
    $('#btnConfirmExecute').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm &amp; Progress Now');
    $('#btnCancelProgression').prop('disabled', false);

    $('#modalConfirmProgression').modal('show');
}

// Execute Progression
function executeConfirmedProgression() {
    var checkedBoxes = $('.student-checkbox:checked:not(:disabled)');
    var studentIds = [];
    checkedBoxes.each(function() {
        studentIds.push($(this).val());
    });

    if (studentIds.length === 0) {
        showToast('error', 'No students selected.');
        return;
    }

    var courseId = $('#prog_course_id').val();
    var batch = $('#prog_batch').val();
    var fromSem = $('#prog_from_semester').val();
    var toSem = $('#prog_to_semester').val();

    var btn = $('#btnConfirmExecute');
    var cancelBtn = $('#btnCancelProgression');

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Progressing...');
    cancelBtn.prop('disabled', true);

    $('#progressionProgressContainer').slideDown(150);
    $('#progressionErrorAlert').slideUp(100);

    $.ajax({
        url: "{{ route('college.fees.progression.execute') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            course_id: courseId,
            batch: batch,
            from_semester: fromSem,
            to_semester: toSem,
            student_ids: studentIds
        },
        dataType: 'json',
        timeout: 180000,
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm &amp; Progress Now');
            cancelBtn.prop('disabled', false);
            $('#progressionProgressContainer').hide();
            $('#modalConfirmProgression').modal('hide');
            showToast('success', res.message || 'Progression executed successfully!');
            loadProgressionStudents();
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm &amp; Progress Now');
            cancelBtn.prop('disabled', false);
            $('#progressionProgressContainer').hide();
            var errMsg = 'Progression failed.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errMsg = xhr.responseJSON.message;
            }
            $('#progressionErrorMessage').text(errMsg);
            $('#progressionErrorAlert').slideDown(150);
            showToast('error', errMsg);
        }
    });
}

// Single Student Upgrade
function progressSingleStudent(studentId, studentName) {
    var row = $('.student-row[data-id="' + studentId + '"]');
    if (row.attr('data-is-deactive') === '1') {
        showToast('error', 'Deactivated student cannot be progressed.');
        return;
    }

    var toSem = $('#prog_to_semester').val() || 2;
    var fromSem = $('#prog_from_semester').val() || 1;
    var courseId = $('#prog_course_id').val();
    var batch = $('#prog_batch').val();

    if (!confirm('Progress ' + studentName + ' from Semester ' + fromSem + ' to Semester ' + toSem + '?\nTarget fee structures and balance carryover will be applied automatically.')) {
        return;
    }

    $.ajax({
        url: "{{ route('college.fees.progression.execute') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            course_id: courseId,
            batch: batch,
            from_semester: fromSem,
            to_semester: toSem,
            student_ids: [studentId]
        },
        dataType: 'json',
        success: function(res) {
            showToast('success', res.message || 'Student upgraded successfully!');
            loadProgressionStudents();
        },
        error: function(xhr) {
            var errMsg = 'Failed to progress student.';
            if (xhr.responseJSON && xhr.responseJSON.message) errMsg = xhr.responseJSON.message;
            showToast('error', errMsg);
        }
    });
}
</script>

@endsection
