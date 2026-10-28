@php
    $courses = Helper::getCourses();
    $batches = Helper::getBatch();
    $classType = Helper::classType($search['course'] ?? null);

    $totalCount = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->total() : count($data);
    $firstItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->firstItem() ?? 0) : ($totalCount > 0 ? 1 : 0);
    $lastItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->lastItem() ?? 0) : $totalCount;
    
    $totalStudents = $totalStudents ?? $totalCount;
    $generatedCount = $generatedCount ?? 0;
    $pendingCount = $pendingCount ?? ($totalStudents - $generatedCount);
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   LOGIN CREDENTIAL MASTER - STREAMLINED EXCEL THEME
   (Strictly matching admissionView design & standards)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #eef6f0;
    --green-excel-border: #8eb99b;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --navy: #002c54;
}

.content-wrapper {
    background-color: #f4f6f9;
}

/* Card & Titlebar */
.admission-card {
    border: 1px solid #9aa7b1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

.admission-titlebar {
    min-height: 42px;
    padding: 6px 12px;
    background: var(--green-excel) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.admission-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.admission-title small {
    font-size: 11.5px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 6px;
}

.admission-titlebar .title-actions {
    display: flex;
    gap: 5px;
    align-items: center;
    flex-wrap: wrap;
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
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    cursor: pointer;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-header-action.btn-header-generate {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-generate:hover {
    background: #dcfce7 !important;
    border-color: #4ade80 !important;
}

/* KPI Summary Ribbon */
.kpi-mini-strip {
    display: flex;
    gap: 8px;
    padding: 6px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
    align-items: center;
}

.kpi-pill {
    padding: 3px 9px;
    border-radius: 3px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.kpi-pill:hover {
    border-color: #94a3b8;
    transform: translateY(-1px);
}
.kpi-pill.active-filter {
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.3);
    font-weight: 700;
}
.kpi-pill strong {
    font-weight: 700;
}

/* Action Icons & Row Elements */
.btn-row-action {
    width: 24px;
    height: 24px;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 3px !important;
    font-size: 11px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    border: 1px solid transparent !important;
}
.btn-row-action:hover { transform: scale(1.08); }
.btn-action-edit { background: #eff6ff !important; border-color: #bfdbfe !important; color: #2563eb !important; }
.btn-action-edit:hover { background: #2563eb !important; color: #ffffff !important; }
.btn-action-whatsapp { background: #f0fdf4 !important; border-color: #bbf7d0 !important; color: #16a34a !important; }
.btn-action-whatsapp:hover { background: #16a34a !important; color: #ffffff !important; }
.btn-action-copy { background: #faf5ff !important; border-color: #e9d5ff !important; color: #9333ea !important; }
.btn-action-copy:hover { background: #9333ea !important; color: #ffffff !important; }

/* Table & Sticky Filter Headers */
.admission-scroll {
    height: calc(100vh - 195px);
    min-height: 400px;
    max-height: calc(100vh - 195px);
    overflow-y: auto;
    overflow-x: auto;
    background: #ffffff;
    border: 1px solid #b8c8d5 !important;
    position: relative;
}

.admission-table {
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px;
    width: 100% !important;
}

.admission-table > thead > tr:first-child > th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    padding: 6px 6px !important;
    font-size: 11px;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    white-space: nowrap;
    text-align: left;
}

.admission-table > thead > tr.admission-filter-row > th {
    position: sticky;
    top: 29px;
    z-index: 11;
    background: var(--green-excel-light) !important;
    color: #1e462d !important;
    padding: 3px 4px !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 2px solid var(--green-excel-border) !important;
}

.admission-table > thead > tr.admission-filter-row .form-control {
    height: 26px !important;
    padding: 1px 5px !important;
    border: 1px solid #aebdca !important;
    border-radius: 2px !important;
    background: #ffffff !important;
    font-size: 11px !important;
    color: #1e293b !important;
}
.admission-table > thead > tr.admission-filter-row .form-control:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15) !important;
}

.admission-table > tbody > tr > td {
    padding: 4px 6px !important;
    font-size: 11.5px;
    vertical-align: middle;
    border-right: 1px solid #cbd5e1 !important;
    border-bottom: 1px solid #cbd5e1 !important;
    line-height: 1.3;
}

.admission-table > tbody > tr:nth-child(even) {
    background-color: #f8fafc;
}
.admission-table > tbody > tr:hover {
    background-color: #fffde7 !important;
}

/* Badges & Monospace Tags */
.credential-code {
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 11.5px;
    letter-spacing: 0.5px;
    padding: 2px 5px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    color: #0f172a;
    display: inline-block;
}

.btn-cred-copy, .btn-cred-toggle {
    border: none;
    background: transparent;
    padding: 1px 4px;
    font-size: 11px;
    color: #64748b;
    cursor: pointer;
    border-radius: 2px;
    transition: all 0.15s;
}
.btn-cred-copy:hover { color: #0284c7; background: #e0f2fe; }
.btn-cred-toggle:hover { color: #334155; background: #e2e8f0; }

.badge-adm-no {
    background: #f8fafc;
    color: #002c54;
    border: 1px solid #b8c8d5;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 3px;
}
.badge-course {
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
    font-size: 10.5px;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: 600;
}
.badge-batch {
    background: #fefce8;
    color: #854d0e;
    border: 1px solid #fef08a;
    font-size: 10.5px;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: 600;
}
.badge-status-active {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
    font-size: 10.5px;
    padding: 2px 7px;
    border-radius: 10px;
    font-weight: 600;
}
.badge-status-pending {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    font-size: 10.5px;
    padding: 2px 7px;
    border-radius: 10px;
    font-weight: 600;
}
.badge-status-missing {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
}

.user-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #334155;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid #cbd5e1;
    flex-shrink: 0;
}

/* Table Overlay Loading Spinner */
.table-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    z-index: 20;
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(1px);
}

/* Pagination Footer Bar */
.admission-pagination-bar {
    padding: 6px 12px;
    background: #f8fafc;
    border-top: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: #475569;
    flex-wrap: wrap;
    gap: 8px;
}

.credential-pagination .pagination {
    margin: 0;
}
.credential-pagination .page-link {
    padding: 3px 8px;
    font-size: 11.5px;
    color: #1e293b;
    border-color: #cbd5e1;
}
.credential-pagination .page-item.active .page-link {
    background-color: var(--green-excel);
    border-color: var(--green-excel);
    color: #ffffff;
}

/* ========================================================
   MODAL CENTERING & EXECUTIVE THEME STYLING
   ======================================================== */
.modal {
    text-align: center;
    padding: 0 !important;
}

.modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.modal-dialog {
    margin: auto !important;
    display: inline-block;
    text-align: left;
    vertical-align: middle;
}

.modal-dialog.modal-dialog-centered {
    display: flex !important;
    align-items: center !important;
    min-height: calc(100vh - 40px) !important;
    justify-content: center !important;
}

.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 4px !important;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,.25) !important;
    background: #ffffff !important;
    width: 100%;
}

.modal-header {
    padding: 8px 14px !important;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.modal-title {
    margin: 0 !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    display: flex;
    align-items: center;
    gap: 7px;
}

.btn-close-modal {
    background: transparent !important;
    border: 0 !important;
    font-size: 18px !important;
    color: #ffffff !important;
    cursor: pointer !important;
    padding: 0 4px !important;
    line-height: 1;
    opacity: 0.85;
}
.btn-close-modal:hover { opacity: 1; color: #ffffff !important; }

.modal-body {
    padding: 14px 16px !important;
    background: #ffffff !important;
    color: #334155;
}

.modal-footer {
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
    display: flex;
    justify-content: flex-end;
    gap: 6px;
}

.btn-modal-cancel {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 12px !important;
    cursor: pointer;
}
.btn-modal-cancel:hover { background: #f1f5f9 !important; color: #0f172a !important; }

.btn-modal-submit {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 14px !important;
    cursor: pointer;
}
.btn-modal-submit:hover { background: var(--green-excel-dark) !important; }

.suggestion-badge {
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.suggestion-badge:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}

/* Generator Formula Option Cards */
.formula-card {
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    background: #f8fafc;
    padding: 10px 12px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.formula-card-title {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--green-excel-dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 5px;
}

.formula-option-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 9px;
    border-radius: 4px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    margin-bottom: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}

.formula-option-row:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}

.formula-option-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.formula-checkbox {
    width: 15px !important;
    height: 15px !important;
    margin: 0 !important;
    position: static !important;
    cursor: pointer;
    accent-color: var(--green-excel);
    flex-shrink: 0;
}

.formula-option-label {
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    cursor: pointer;
}

.formula-tag {
    font-size: 10.5px;
    padding: 2px 6px;
    border-radius: 3px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-family: monospace;
}

.formula-stepper {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10.5px;
    color: #64748b;
}

.formula-stepper input {
    width: 42px;
    height: 22px;
    padding: 1px 3px;
    text-align: center;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    font-weight: 600;
    font-size: 11px;
}

/* Single Credential Modal Specialized Styling */
.single-cred-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 9px 12px;
}

.student-badge-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #eef6f0;
    color: var(--green-excel-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    border: 1.5px solid var(--green-excel-border);
    flex-shrink: 0;
}

.single-cred-input {
    font-family: 'Consolas', 'Courier New', monospace !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    letter-spacing: 0.5px;
    background-color: #ffffff !important;
    background: #ffffff !important;
    color: #0f172a !important;
    height: 35px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 0 4px 4px 0 !important;
    padding: 6px 10px !important;
}
.single-cred-input:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15) !important;
    background-color: #ffffff !important;
}
.single-cred-input:-webkit-autofill,
.single-cred-input:-webkit-autofill:hover,
.single-cred-input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
    -webkit-text-fill-color: #0f172a !important;
}

.single-cred-prepend {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    border-right: none !important;
    color: #475569 !important;
    padding: 0 11px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-top-left-radius: 4px !important;
    border-bottom-left-radius: 4px !important;
    font-size: 13px !important;
}

.chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
}

.chip-label {
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
    margin-right: 2px;
}

.suggestion-chip {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
    display: inline-block;
}
.suggestion-chip:hover {
    background: #166534;
    color: #ffffff;
    border-color: #166534;
    transform: translateY(-1px);
}

.preset-chip {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
    display: inline-block;
}
.preset-chip:hover {
    background: #334155;
    color: #ffffff;
    border-color: #334155;
    transform: translateY(-1px);
}

.btn-cred-action-append {
    background: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    padding: 0 10px !important;
    font-size: 12px !important;
    height: 35px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.15s ease !important;
}
.btn-cred-action-append:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
}
.btn-cred-auto {
    background: #f0fdf4 !important;
    border: 1px solid #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
    border-top-right-radius: 4px !important;
    border-bottom-right-radius: 4px !important;
    padding: 0 10px !important;
    font-size: 11.5px !important;
}
.btn-cred-auto:hover {
    background: #166534 !important;
    color: #ffffff !important;
    border-color: #166534 !important;
}
</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            <div class="admission-card">
                
                <!-- ================= TITLEBAR ================= -->
                <div class="admission-titlebar">
                    <h1 class="admission-title">
                        <i class="fa fa-key"></i> 
                        {{ __('Login Credential Reports & Portal Access') }}
                        <small id="title_count_badge">({{ $totalStudents }} Students)</small>
                    </h1>
                    <div class="title-actions">
                        <button type="button" class="btn-header-action btn-header-generate" id="openModalBtn" title="Bulk Generate Usernames & Passwords">
                            <i class="fa fa-cogs mr-1"></i> Bulk Generate
                        </button>
                        <button type="button" class="btn-header-action" id="btn_refresh_table" title="Instant Refresh Table">
                            <i class="fa fa-refresh mr-1"></i> Refresh
                        </button>
                        <a href="{{ url('studentsDashboard') }}" class="btn-header-action">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- ================= KPI METRIC STRIP ================= -->
                <div class="kpi-mini-strip">
                    <div class="kpi-pill kpi-filter-btn {{ empty($search['status']) ? 'active-filter' : '' }}" data-status="" title="Show All Students">
                        <i class="fa fa-users text-muted"></i>
                        <span class="text-muted">Total:</span>
                        <strong class="text-dark" id="total_count_display">{{ $totalStudents }}</strong>
                    </div>
                    
                    <div class="kpi-pill kpi-filter-btn {{ ($search['status'] ?? '') == 'generated' ? 'active-filter' : '' }}" data-status="generated" style="border-color: #86efac; background: #f0fdf4;" title="Filter Active / Generated">
                        <i class="fa fa-check-circle text-success"></i>
                        <span class="text-success font-weight-bold">Active / Generated:</span>
                        <strong class="text-success" id="generated_count_display">{{ $generatedCount }}</strong>
                    </div>
                    
                    <div class="kpi-pill kpi-filter-btn {{ ($search['status'] ?? '') == 'pending' ? 'active-filter' : '' }}" data-status="pending" style="border-color: #fde047; background: #fefce8;" title="Filter Pending Credentials">
                        <i class="fa fa-clock-o text-warning"></i>
                        <span class="text-warning font-weight-bold">Pending:</span>
                        <strong class="text-warning" id="pending_count_display">{{ $pendingCount }}</strong>
                    </div>

                    <div class="ml-auto d-flex align-items-center" style="gap: 6px;">
                        <span class="text-muted small">Show:</span>
                        <select class="form-control form-control-sm cred-filter-select" id="credPerPage" name="per_page" style="width: 75px; height: 26px; padding: 1px 5px; font-size: 11px;">
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                            <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                            <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>
                </div>

                <!-- ================= MAIN FILTER FORM & TABLE ================= -->
                <form id="credentialFilterForm" action="{{ url('login_credential_reports') }}" method="get">
                    <input type="hidden" name="page" id="credCurrentPage" value="{{ request('page', 1) }}">
                    
                    <div class="admission-scroll">
                        <!-- Loading Overlay -->
                        <div class="table-loading-overlay" id="tableLoadingOverlay">
                            <div class="text-center">
                                <i class="fa fa-circle-o-notch fa-spin fa-2x text-success"></i>
                                <span class="d-block font-weight-bold text-dark mt-1" style="font-size: 11px;">Loading data...</span>
                            </div>
                        </div>

                        <table class="table admission-table table-bordered mb-0">
                            <thead>
                                <!-- Column Headers -->
                                <tr>
                                    <th style="width: 38px;" class="text-center">
                                        <input type="checkbox" id="select_all" class="pointer" style="width: 14px; height: 14px; vertical-align: middle;">
                                    </th>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="min-width: 110px;">{{ __('student.Admission No.') }}</th>
                                    <th style="min-width: 160px;">{{ __('student.Student Name') }}</th>
                                    <th style="min-width: 110px;">{{ __('common.Mobile') }}</th>
                                    <th style="min-width: 110px;">{{ __('Course') }}</th>
                                    <th style="min-width: 90px;">{{ __('Batch') }}</th>
                                    <th style="min-width: 130px;">{{ __('Class / Semester') }}</th>
                                    <th style="min-width: 130px;">{{ __('UserName') }}</th>
                                    <th style="min-width: 140px;">{{ __('Password') }}</th>
                                    <th style="min-width: 90px;" class="text-center">{{ __('Status') }}</th>
                                    <th style="min-width: 95px;" class="text-center">{{ __('common.Action') }}</th>
                                </tr>

                                <!-- Thead Filter Row (Matching admissionView Pattern) -->
                                <tr class="admission-filter-row">
                                    <th colspan="2" class="text-center">
                                        <button type="button" id="btn_reset_filters" class="btn btn-light btn-xs border" title="Reset All Filters" style="height: 24px; padding: 1px 6px; width: 100%;">
                                            <i class="fa fa-refresh text-secondary"></i>
                                        </button>
                                    </th>
                                    <th>
                                        <input type="text" class="form-control cred-filter-input" name="admissionNo" id="filter_admissionNo" value="{{ $search['admissionNo'] ?? '' }}" placeholder="Adm No.">
                                    </th>
                                    <th>
                                        <input type="text" class="form-control cred-filter-input" name="name" id="filter_name" value="{{ $search['name'] ?? '' }}" placeholder="Search name, father, mob...">
                                    </th>
                                    <th>
                                        <span class="small text-muted font-italic" style="font-size: 10px;"><i class="fa fa-filter"></i> Search above</span>
                                    </th>
                                    <th>
                                        <select class="form-control cred-filter-select" id="search_course" name="course">
                                            <option value="">All Courses</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->name ?? '' }}" {{ ($search['course'] ?? '') == $course->name ? 'selected' : '' }}>
                                                        {{ $course->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>
                                    <th>
                                        <select class="form-control cred-filter-select" id="search_batch" name="batch">
                                            <option value="">All Batches</option>
                                            @if(!empty($batches))
                                                @foreach($batches as $batch)
                                                    <option value="{{ $batch->name ?? '' }}" {{ ($search['batch'] ?? '') == $batch->name ? 'selected' : '' }}>
                                                        {{ $batch->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>
                                    <th>
                                        <select class="form-control cred-filter-select" id="search_class_type_id" name="class_type_id">
                                            <option value="">All Semesters</option>
                                            @if(!empty($classType))
                                                @foreach($classType as $type)
                                                    <option value="{{ $type->id ?? '' }}" {{ ($type->id == ($search['class_type_id'] ?? '')) ? 'selected' : '' }}>
                                                        {{ $type->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>
                                    <th colspan="2">
                                        <span class="small text-muted font-italic" style="font-size: 10px;"><i class="fa fa-bolt"></i> Real-time AJAX</span>
                                    </th>
                                    <th>
                                        <select class="form-control cred-filter-select" id="filter_status" name="status">
                                            <option value="">All</option>
                                            <option value="generated" {{ ($search['status'] ?? '') == 'generated' ? 'selected' : '' }}>Active</option>
                                            <option value="pending" {{ ($search['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        </select>
                                    </th>
                                    <th class="text-center">
                                        <button type="submit" class="btn btn-xs" style="background: var(--green-excel); color: #fff; height: 24px; padding: 1px 8px;" title="Apply Filter">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            
                            <!-- Table Body Partial -->
                            <tbody id="credential_tbody">
                                @include('students.login_credential.table_rows', ['data' => $data, 'search' => $search])
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= PAGINATION & COUNTER FOOTER ================= -->
                    <div class="admission-pagination-bar">
                        <div id="credentialEntriesInfo">
                            Showing <strong id="entries_from">{{ $firstItem }}</strong> to <strong id="entries_to">{{ $lastItem }}</strong> of <strong id="entries_total">{{ $totalCount }}</strong> students
                        </div>
                        <div id="credentialPaginationContainer">
                            @include('students.login_credential.pagination', ['data' => $data])
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

<!-- ============================================================== -->
<!-- 1️⃣ SINGLE STUDENT CREDENTIAL MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="singleCredentialModal" tabindex="-1" role="dialog" aria-labelledby="singleModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 470px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="singleModalTitle">
                    <i class="fa fa-id-card-o mr-1"></i> Edit Student Login Credentials
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="singleCredentialForm">
                @csrf
                <input type="hidden" id="single_student_id" name="student_id">

                <div class="modal-body p-3">
                    <!-- Student Identity Card -->
                    <div class="single-cred-card mb-3">
                        <div class="d-flex align-items-center">
                            <div class="student-badge-avatar mr-2" id="modal_student_avatar">
                                S
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between">
                                    <strong id="modal_student_name" class="text-dark font-weight-bold" style="font-size: 13.5px;">Student Name</strong>
                                    <span class="badge badge-adm-no font-weight-bold" id="modal_student_adm">Adm: -</span>
                                </div>
                                <div class="text-muted small mt-1 d-flex flex-wrap align-items-center" style="gap: 4px; font-size: 11px;">
                                    <span class="badge badge-course" id="modal_student_course">-</span>
                                    <span class="badge badge-batch" id="modal_student_batch">-</span>
                                    <span class="badge badge-light border text-dark font-weight-600" id="modal_student_class">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Username Field -->
                    <div class="form-group mb-3">
                        <label for="single_username" class="small font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="fa fa-user text-success mr-1"></i> Portal Username <span class="text-danger">*</span></span>
                            <span class="text-muted font-normal" style="font-size: 10.5px;">Unique login ID</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="single-cred-prepend"><i class="fa fa-user-circle-o"></i></span>
                            </div>
                            <input type="text" class="form-control single-cred-input" id="single_username" name="username" required placeholder="Enter Portal Username" autocomplete="off">
                        </div>
                        <div class="chip-group mt-2">
                            <span class="chip-label"><i class="fa fa-lightbulb-o text-warning mr-1"></i>Suggestions:</span>
                            <span class="suggestion-chip" id="btn_suggest_adm">Adm No</span>
                            <span class="suggestion-chip" id="btn_suggest_stu">stu_admNo</span>
                            <span class="suggestion-chip" id="btn_suggest_namemob">Name+Mobile</span>
                            <span class="suggestion-chip" id="btn_suggest_adm_course">Adm_Course</span>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group mb-1">
                        <label for="single_password" class="small font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                            <span><i class="fa fa-lock text-success mr-1"></i> Portal Password <span class="text-danger">*</span></span>
                            <span class="text-muted font-normal" style="font-size: 10.5px;">Min 4 characters</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="single-cred-prepend"><i class="fa fa-key"></i></span>
                            </div>
                            <input type="password" class="form-control single-cred-input" id="single_password" name="password" required minlength="4" placeholder="Enter Portal Password" autocomplete="off" style="border-radius: 0 !important;">
                            <div class="input-group-append">
                                <button class="btn btn-cred-action-append" type="button" id="btn_toggle_single_pass" title="Show / Hide Password">
                                    <i class="fa fa-eye" id="single_pass_eye"></i>
                                </button>
                                <button class="btn btn-cred-auto" type="button" id="btn_random_pass" title="Generate Random Secure Password">
                                    <i class="fa fa-random mr-1"></i> Auto
                                </button>
                            </div>
                        </div>
                        <div class="chip-group mt-2">
                            <span class="chip-label"><i class="fa fa-bookmark-o text-info mr-1"></i>Presets:</span>
                            <span class="preset-chip" onclick="$('#single_password').val('12345678')">12345678</span>
                            <span class="preset-chip" id="btn_preset_mobile">Mobile No</span>
                            <span class="preset-chip" id="btn_preset_dob">DOB (DDMMYY)</span>
                            <span class="preset-chip" onclick="$('#single_password').val('Student@123')">Student@123</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn-modal-submit" id="btn_save_single_cred">
                        <i class="fa fa-check mr-1"></i> Save & Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 2️⃣ BULK USERNAME & PASSWORD GENERATOR MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="generateModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form id="bulkGenerateForm" action="{{ url('studentUserNameCreate') }}" method="POST">
            @csrf
            <input type="hidden" name="student_ids" id="bulk_student_ids">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateModalTitle">
                        <i class="fa fa-cogs mr-1"></i> Bulk Username & Password Generator
                    </h5>
                    <button type="button" class="btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <div class="modal-body p-3">
                    <!-- Status Ribbon -->
                    <div class="d-flex align-items-center justify-content-between p-2 mb-3 rounded" style="background: #eef6f0; border: 1px solid #8eb99b; color: #166534; font-size: 12px;">
                        <div>
                            <i class="fa fa-check-square-o mr-1 font-weight-bold"></i> 
                            <strong id="bulk_selected_count_text">Generating credentials for selected students.</strong>
                        </div>
                        <span class="badge" style="background: #166534; color: #ffffff; font-size: 11px;">Batch Action</span>
                    </div>

                    <div class="row g-2">
                        <!-- USERNAME FORMULA CARD -->
                        <div class="col-md-6 mb-2">
                            <div class="formula-card">
                                <div class="formula-card-title">
                                    <i class="fa fa-user-circle"></i> Username Formula Fields
                                </div>
                                
                                <label class="formula-option-row" for="username_admission_no">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="username_admission_no" checked>
                                        <span class="formula-option-label">Admission No.</span>
                                    </div>
                                    <span class="formula-tag">e.g. 2026001</span>
                                </label>

                                <label class="formula-option-row" for="username_name">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="username_name">
                                        <span class="formula-option-label">First Name</span>
                                    </div>
                                    <div class="formula-stepper">
                                        <span>first</span>
                                        <input type="number" name="name_letters" value="4" min="1" max="15" onclick="event.stopPropagation()">
                                        <span>chars</span>
                                    </div>
                                </label>

                                <label class="formula-option-row" for="username_mobile">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="username_mobile">
                                        <span class="formula-option-label">Mobile Digits</span>
                                    </div>
                                    <div class="formula-stepper">
                                        <span>last</span>
                                        <input type="number" name="mobile_digits" value="4" min="1" max="10" onclick="event.stopPropagation()">
                                        <span>digits</span>
                                    </div>
                                </label>

                                <label class="formula-option-row" for="username_dob">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="username_dob">
                                        <span class="formula-option-label">Date of Birth</span>
                                    </div>
                                    <span class="formula-tag">DDMMYY</span>
                                </label>

                                <label class="formula-option-row" for="username_class">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="username_class">
                                        <span class="formula-option-label">Class Code</span>
                                    </div>
                                    <span class="formula-tag">e.g. bca1</span>
                                </label>
                            </div>
                        </div>

                        <!-- PASSWORD FORMULA CARD -->
                        <div class="col-md-6 mb-2">
                            <div class="formula-card">
                                <div class="formula-card-title">
                                    <i class="fa fa-lock"></i> Password Formula & Options
                                </div>

                                <label class="formula-option-row" for="password_mobile">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="password_mobile" checked>
                                        <span class="formula-option-label">Student Mobile Number</span>
                                    </div>
                                    <span class="formula-tag">10 digits</span>
                                </label>

                                <label class="formula-option-row" for="password_admission_no">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="password_admission_no">
                                        <span class="formula-option-label">Admission No.</span>
                                    </div>
                                    <span class="formula-tag">Adm No</span>
                                </label>

                                <label class="formula-option-row" for="password_dob">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="password_dob">
                                        <span class="formula-option-label">Date of Birth</span>
                                    </div>
                                    <span class="formula-tag">DDMMYY</span>
                                </label>

                                <label class="formula-option-row" for="password_name">
                                    <div class="formula-option-left">
                                        <input class="formula-checkbox" type="checkbox" id="password_name">
                                        <span class="formula-option-label">First Name</span>
                                    </div>
                                    <span class="formula-tag">Name</span>
                                </label>

                                <!-- Custom Static Password -->
                                <div class="mt-auto pt-2 border-top">
                                    <label class="small font-weight-bold text-dark mb-1 d-block" for="custom_password">
                                        <i class="fa fa-key text-muted mr-1"></i> Or Fixed Common Password:
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="custom_password" name="custom_password" class="form-control" placeholder="Fixed password (overrides formula)">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="$('#custom_password').val('12345678').trigger('input')" title="Set Default 12345678">
                                                12345678
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Fields for Orders -->
                    <input type="hidden" name="username_order" id="username_order">
                    <input type="hidden" name="password_order" id="password_order">

                    <!-- Sample Inputs for preview -->
                    <div class="d-none">
                        <input type="hidden" id="ex_name" value="Rahul">
                        <input type="hidden" id="ex_mobile" value="9876543210">
                        <input type="hidden" id="ex_dob" value="2002-05-15">
                        <input type="hidden" id="ex_admission" value="2026001">
                    </div>

                    <!-- LIVE SAMPLE PREVIEW BANNER -->
                    <div class="p-2 mt-2 rounded" style="background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom" style="font-size: 11px;">
                            <span class="font-weight-bold text-dark"><i class="fa fa-eye text-primary mr-1"></i> Live Formula Preview:</span>
                            <span class="text-muted">Sample Student: <strong>Rahul Sharma</strong> (Adm: 2026001 | Mob: 9876543210)</span>
                        </div>
                        <div class="row text-center pt-1" style="font-size: 12px;">
                            <div class="col-6 border-right">
                                <span class="text-muted d-block small">Generated Username</span>
                                <span class="credential-code text-primary font-weight-bold" id="usernamePreview" style="font-size: 12.5px;">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block small">Generated Password</span>
                                <span class="credential-code text-success font-weight-bold" id="passwordPreview" style="font-size: 12.5px;">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal-submit" id="btn_submit_bulk">
                        <i class="fa fa-bolt mr-1"></i> Generate for Selected Students
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    var searchTimer = null;
    var currentStudentData = {};
    var activeAjaxRequest = null;

    // ========================================================
    // 1. REAL-TIME AJAX FILTERING & DEBOUNCE
    // ========================================================
    function getFilterParams(targetPage) {
        var formData = $('#credentialFilterForm').serializeArray();
        var params = new URLSearchParams();
        
        $.each(formData, function(_, item) {
            if ($.trim(item.value) !== '' && item.name !== '_token') {
                params.set(item.name, item.value);
            }
        });

        var perPage = $('#credPerPage').val();
        if (perPage) {
            params.set('per_page', perPage);
        }

        if (targetPage !== undefined) {
            params.set('page', targetPage);
        } else {
            var curr = $('#credCurrentPage').val();
            if (curr && curr > 1) {
                params.set('page', curr);
            }
        }

        return params;
    }

    function loadCredentials(updateUrl, targetPage) {
        if (targetPage !== undefined) {
            $('#credCurrentPage').val(targetPage);
        } else if (updateUrl) {
            $('#credCurrentPage').val(1);
        }

        var params = getFilterParams(targetPage);
        var fullUrl = "{{ url('login_credential_reports') }}?" + params.toString();

        if (updateUrl && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', fullUrl);
        }

        if (activeAjaxRequest) {
            activeAjaxRequest.abort();
        }

        $('#tableLoadingOverlay').css('display', 'flex');

        activeAjaxRequest = $.ajax({
            url: fullUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function(response) {
            if (response && response.status === 'success') {
                $('#credential_tbody').html(response.html);
                $('#credentialPaginationContainer').html(response.pagination);
                
                // Update Counters & Badges
                $('#entries_from').text(response.from || 0);
                $('#entries_to').text(response.to || 0);
                $('#entries_total').text(response.total || 0);

                if (response.total_students !== undefined) {
                    $('#total_count_display').text(response.total_students);
                    $('#title_count_badge').text('(' + response.total_students + ' Students)');
                }
                if (response.generated_count !== undefined) {
                    $('#generated_count_display').text(response.generated_count);
                }
                if (response.pending_count !== undefined) {
                    $('#pending_count_display').text(response.pending_count);
                }

                // Uncheck Select All
                $('#select_all').prop('checked', false);
            }
        })
        .fail(function(xhr, status) {
            if (status !== 'abort') {
                toastr.error('Failed to load credentials. Please try again.');
            }
        })
        .always(function() {
            $('#tableLoadingOverlay').hide();
        });
    }

    // Input Debounce Search (300ms)
    $('.cred-filter-input').on('keyup input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            loadCredentials(true, 1);
        }, 300);
    });

    // Dropdowns Change Instant AJAX
    $('.cred-filter-select').on('change', function () {
        loadCredentials(true, 1);
    });

    // Per Page Dropdown Change
    $('#credPerPage').on('change', function () {
        loadCredentials(true, 1);
    });

    // Prevent default form submit & trigger AJAX instead
    $('#credentialFilterForm').on('submit', function (e) {
        e.preventDefault();
        loadCredentials(true, 1);
    });

    // Reset Filters Button
    $('#btn_reset_filters').on('click', function () {
        $('#filter_admissionNo').val('');
        $('#filter_name').val('');
        $('#search_course').val('');
        $('#search_batch').val('');
        $('#search_class_type_id').val('');
        $('#filter_status').val('');
        $('#credPerPage').val('25');
        $('#credCurrentPage').val('1');

        $('.kpi-filter-btn').removeClass('active-filter');
        $('.kpi-filter-btn[data-status=""]').addClass('active-filter');

        // Reset class dropdown
        $('#search_course').trigger('change');
        loadCredentials(true, 1);
    });

    // Refresh Button
    $('#btn_refresh_table').on('click', function () {
        loadCredentials(false);
    });

    // KPI Strip Clickable Filters
    $('.kpi-filter-btn').on('click', function () {
        var status = $(this).data('status');
        $('#filter_status').val(status);
        $('.kpi-filter-btn').removeClass('active-filter');
        $(this).addClass('active-filter');
        loadCredentials(true, 1);
    });

    // AJAX Pagination Link Click
    $(document).on('click', '.credential-pagination .page-link', function (e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (!page) {
            var href = $(this).attr('href');
            if (href) {
                var match = href.match(/page=(\d+)/);
                if (match) page = match[1];
            }
        }
        if (page) {
            loadCredentials(true, page);
        }
    });

    // ========================================================
    // 2. DYNAMIC COURSE-SEMESTER CASCADING
    // ========================================================
    $('#search_course').on('change', function () {
        var courseName = $(this).val();
        var classSelect = $('#search_class_type_id');

        classSelect.empty();
        classSelect.append('<option value="">All Semesters</option>');

        if (courseName) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course: courseName },
                dataType: "json",
                success: function (data) {
                    if (data && data.length > 0) {
                        $.each(data, function (key, val) {
                            classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                        });
                    }
                }
            });
        } else {
            @if(!empty($classType))
                @foreach($classType as $type)
                    classSelect.append('<option value="{{ $type->id }}">{{ $type->name }}</option>');
                @endforeach
            @endif
        }
    });

    // ========================================================
    // 3. SELECT ALL CHECKBOX
    // ========================================================
    $('#select_all').on('change', function () {
        $('.student_checkbox').prop('checked', $(this).is(':checked'));
    });

    $(document).on('change', '.student_checkbox', function () {
        var total = $('.student_checkbox').length;
        var checked = $('.student_checkbox:checked').length;
        $('#select_all').prop('checked', total > 0 && total === checked);
    });

    // ========================================================
    // 4. BULK GENERATE MODAL
    // ========================================================
    $('#openModalBtn').on('click', function (e) {
        var selectedIds = [];
        $('.student_checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one student from the table.');
            return false;
        }

        $('#bulk_student_ids').val(selectedIds.join(','));
        $('#bulk_selected_count_text').text('Generating credentials for ' + selectedIds.length + ' selected student(s).');
        $('#generateModal').modal('show');
    });

    // ========================================================
    // 5. SINGLE STUDENT CREDENTIAL MODAL
    // ========================================================
    $(document).on('click', '.edit-credential-btn', function () {
        var btn = $(this);
        var id = btn.data('id');
        var name = btn.data('name');
        var adm = btn.data('adm');
        var course = btn.data('course');
        var batch = btn.data('batch');
        var className = btn.data('class');
        var username = btn.data('username') || '';
        var password = btn.data('password') || '';
        var mobile = btn.data('mobile') || '';
        var dob = btn.data('dob') || '';

        currentStudentData = {
            id: id,
            name: name,
            adm: adm,
            mobile: mobile,
            course: course,
            batch: batch,
            className: className,
            dob: dob
        };

        $('#single_student_id').val(id);
        $('#modal_student_avatar').text((name || 'S').trim().charAt(0).toUpperCase());
        $('#modal_student_name').text(name || 'Student');
        $('#modal_student_adm').text('Adm: ' + (adm || 'N/A'));
        $('#modal_student_course').text(course || '-');
        $('#modal_student_batch').text(batch || '-');
        $('#modal_student_class').text(className || '-');

        $('#single_username').val(username || (adm ? adm : ''));
        $('#single_password').val(password || '12345678');
        $('#single_password').attr('type', 'password');
        $('#single_pass_eye').removeClass('fa-eye-slash').addClass('fa-eye');

        $('#singleCredentialModal').modal('show');
    });

    // Suggestion Buttons for Username
    $('#btn_suggest_adm').on('click', function () {
        if (currentStudentData.adm) {
            $('#single_username').val(currentStudentData.adm);
        }
    });
    $('#btn_suggest_stu').on('click', function () {
        if (currentStudentData.adm) {
            $('#single_username').val('stu_' + currentStudentData.adm);
        }
    });
    $('#btn_suggest_namemob').on('click', function () {
        var cleanName = (currentStudentData.name || '').toLowerCase().replace(/\s+/g, '').substring(0, 4);
        var cleanMob = (currentStudentData.mobile || '').replace(/\D/g, '').slice(-4);
        $('#single_username').val(cleanName + (cleanMob || '1234'));
    });
    $('#btn_suggest_adm_course').on('click', function () {
        var adm = currentStudentData.adm || '';
        var course = (currentStudentData.course || '').replace(/[^a-zA-Z0-9]/g, '').substring(0, 6);
        $('#single_username').val(adm + (course ? '_' + course : ''));
    });

    // Preset Buttons for Password
    $('#btn_preset_mobile').on('click', function () {
        var mob = (currentStudentData.mobile || '').replace(/\D/g, '');
        if (mob) {
            $('#single_password').val(mob);
        } else {
            toastr.warning('No mobile number available for this student.');
        }
    });
    $('#btn_preset_dob').on('click', function () {
        var dob = currentStudentData.dob;
        if (dob) {
            var parts = dob.split('-');
            if (parts.length === 3) {
                $('#single_password').val(parts[2] + parts[1] + parts[0].slice(2));
                return;
            }
        }
        toastr.warning('No valid DOB available for this student.');
    });

    // Toggle Single Password Visibility
    $('#btn_toggle_single_pass').on('click', function () {
        var passField = $('#single_password');
        var eyeIcon = $('#single_pass_eye');
        if (passField.attr('type') === 'password') {
            passField.attr('type', 'text');
            eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passField.attr('type', 'password');
            eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate Random Password
    $('#btn_random_pass').on('click', function () {
        var randomChars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#$';
        var generatedPass = '';
        for (var i = 0; i < 8; i++) {
            generatedPass += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
        }
        $('#single_password').val(generatedPass);
        $('#single_password').attr('type', 'text');
        $('#single_pass_eye').removeClass('fa-eye').addClass('fa-eye-slash');
    });

    // Submit Single Credential Form via AJAX
    $('#singleCredentialForm').on('submit', function (e) {
        e.preventDefault();
        var studentId = $('#single_student_id').val();
        var username = $('#single_username').val().trim();
        var password = $('#single_password').val().trim();

        if (!username || !password) {
            toastr.error('Username and password are required.');
            return;
        }

        $('#btn_save_single_cred').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "{{ url('updateSingleStudentCredential') }}",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                student_id: studentId,
                username: username,
                password: password
            },
            dataType: "json",
            success: function (res) {
                $('#btn_save_single_cred').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save & Update');
                if (res.status) {
                    toastr.success(res.message);
                    $('#singleCredentialModal').modal('hide');

                    // Refresh row data cleanly via AJAX
                    loadCredentials(false);
                } else {
                    toastr.error(res.message || 'Failed to update credentials.');
                }
            },
            error: function (xhr) {
                $('#btn_save_single_cred').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save & Update');
                toastr.error('An error occurred while updating.');
            }
        });
    });
});

// ========================================================
// 6. GLOBAL UTILITY FUNCTIONS (Copy & Toggle)
// ========================================================
function copyToClipboard(text, message) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(function () {
        toastr.info(message || 'Copied to clipboard!');
    }).catch(function () {
        toastr.error('Failed to copy');
    });
}

function togglePasswordVisibility(studentId) {
    var masked = $('#pass_masked_' + studentId);
    var plain = $('#pass_plain_' + studentId);
    var icon = $('#pass_eye_icon_' + studentId);

    if (plain.hasClass('d-none')) {
        plain.removeClass('d-none');
        masked.addClass('d-none');
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        plain.addClass('d-none');
        masked.removeClass('d-none');
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
}

// ========================================================
// 7. BULK FORMULA GENERATOR LIVE PREVIEW LOGIC
// ========================================================
document.addEventListener('DOMContentLoaded', function () {
    const usernameFields = [
        { id: 'username_admission_no', key: 'admission_no' },
        { id: 'username_name', key: 'name' },
        { id: 'username_mobile', key: 'mobile' },
        { id: 'username_dob', key: 'dob' },
        { id: 'username_class', key: 'class' }
    ];

    const passwordFields = [
        { id: 'password_admission_no', key: 'admission_no' },
        { id: 'password_name', key: 'name' },
        { id: 'password_mobile', key: 'mobile' },
        { id: 'password_dob', key: 'dob' },
        { id: 'password_class', key: 'class' }
    ];

    const example = {
        name: document.getElementById('ex_name'),
        mobile: document.getElementById('ex_mobile'),
        dob: document.getElementById('ex_dob'),
        admission: document.getElementById('ex_admission'),
    };

    function getFieldValue(key) {
        switch (key) {
            case 'name':
                const len = document.querySelector('[name="name_letters"]') ? document.querySelector('[name="name_letters"]').value || 4 : 4;
                return (example.name ? example.name.value || '' : '').trim().toLowerCase().replace(/\s+/g, '').substring(0, len);
            case 'mobile':
                const digits = document.querySelector('[name="mobile_digits"]') ? document.querySelector('[name="mobile_digits"]').value || 4 : 4;
                return (example.mobile ? example.mobile.value || '' : '').trim().replace(/\D/g, '').slice(-digits);
            case 'dob':
                const dob = example.dob ? example.dob.value : '';
                if (!dob) return '';
                const parts = dob.split('-');
                return parts.length === 3 ? parts[2] + parts[1] + parts[0].slice(2) : '';
            case 'admission_no':
                return (example.admission ? example.admission.value || '' : '').trim();
            case 'class':
                return 'bca1';
            default:
                return '';
        }
    }

    function updateOrdersAndPreview() {
        const u_order = [];
        const p_order = [];
        let username = '';
        let password = '';

        usernameFields.forEach(f => {
            const cb = document.getElementById(f.id);
            if (cb && cb.checked) {
                u_order.push(f.key);
                username += getFieldValue(f.key);
            }
        });

        const customPassEl = document.getElementById('custom_password');
        const customPass = customPassEl ? customPassEl.value.trim() : '';
        if (customPass) {
            password = customPass;
        } else {
            passwordFields.forEach(f => {
                const cb = document.getElementById(f.id);
                if (cb && cb.checked) {
                    p_order.push(f.key);
                    password += getFieldValue(f.key);
                }
            });
        }

        var uInput = document.getElementById('username_order');
        var pInput = document.getElementById('password_order');
        if (uInput) uInput.value = u_order.join(',');
        if (pInput) pInput.value = p_order.join(',');
        
        var uPrev = document.getElementById('usernamePreview');
        var pPrev = document.getElementById('passwordPreview');
        if (uPrev) uPrev.innerText = username || '[Username]';
        if (pPrev) pPrev.innerText = password || '[Password]';
    }

    document.querySelectorAll('#generateModal input').forEach(el => {
        el.addEventListener('input', updateOrdersAndPreview);
        el.addEventListener('change', updateOrdersAndPreview);
    });

    updateOrdersAndPreview();
});
</script>
@endsection