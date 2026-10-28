@php
$actionPermission = Helper::actionPermission();
$roleId = Session::get('role_id') ?? 1;
$canAdd = ($roleId == 1) || (!empty($actionPermission) && (is_array($actionPermission) ? ($actionPermission['add'] ?? 0) : ($actionPermission->add ?? 0)) == 1);
$totalCount = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->total() : count($data);
$firstItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->firstItem() ?? 0) : 1;
$lastItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->lastItem() ?? 0) : count($data);
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   USER DIRECTORY MASTER - STREAMLINED EXCEL GREEN THEME
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #eef6f0;
    --green-excel-border: #8eb99b;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
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
    flex-wrap: wrap;
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
    background: #dcfce7 !important;
    color: #14532d !important;
}

/* Action Icons */
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
.btn-action-view { background: #f0fdf4 !important; border-color: #bbf7d0 !important; color: #166534 !important; }
.btn-action-view:hover { background: #166534 !important; color: #ffffff !important; }
.btn-action-edit { background: #eff6ff !important; border-color: #bfdbfe !important; color: #2563eb !important; }
.btn-action-edit:hover { background: #2563eb !important; color: #ffffff !important; }
.btn-action-delete { background: #fff1f2 !important; border-color: #fecdd3 !important; color: #e11d48 !important; }
.btn-action-delete:hover { background: #e11d48 !important; color: #ffffff !important; }

/* Table & Sticky Filter Headers */
.admission-scroll {
    height: calc(100vh - 180px);
    min-height: 400px;
    overflow: auto;
    background: #ffffff;
    border: 1px solid #b8c8d5 !important;
    position: relative;
}

.admission-table {
    margin: 0 !important;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px;
    border-top: 1px solid #b8c8d5 !important;
    border-left: 1px solid #b8c8d5 !important;
}

.admission-table tbody tr {
    height: 32px !important;
}

.admission-table th,
.admission-table td {
    padding: 3px 6px !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 1px solid #b8c8d5 !important;
    vertical-align: middle !important;
    box-sizing: border-box;
    white-space: nowrap;
}

.admission-table thead tr:first-child th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11.5px;
    text-align: center;
    height: 32px !important;
    line-height: 22px !important;
    border-top: 0 !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
}

.admission-table thead tr.admission-filter-row th {
    position: sticky;
    top: 32px;
    z-index: 6;
    background: var(--green-excel-light) !important;
    color: #1e462d !important;
    padding: 3px !important;
    height: 33px !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 2px solid var(--green-excel-border) !important;
}

.admission-table thead tr.admission-filter-row .form-control {
    height: 26px !important;
    min-height: 26px !important;
    padding: 1px 4px !important;
    border: 1px solid #9fb3c4 !important;
    border-radius: 2px !important;
    background: #ffffff !important;
    font-size: 11px !important;
    color: #0f172a !important;
    width: 100%;
}
.admission-table thead tr.admission-filter-row .form-control:focus {
    border-color: var(--green-excel) !important;
    outline: none !important;
}

.admission-table tbody tr:nth-child(even) td { background-color: #fafafa; }
.admission-table tbody tr:hover td { background-color: #fffde7 !important; }

/* Sticky Action Column */
.admission-table .fixed_item {
    position: sticky !important;
    right: 0;
    background-color: #ffffff;
    z-index: 2;
    border-left: 1px solid #b8c8d5 !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 1px solid #b8c8d5 !important;
}
.admission-table tbody tr:nth-child(even) .fixed_item { background-color: #fafafa !important; }
.admission-table tbody tr:hover .fixed_item { background-color: #fffde7 !important; }
.admission-table th.fixed_item {
    background-color: var(--green-excel) !important;
    color: #ffffff !important;
    z-index: 7 !important;
    border-left: 1px solid var(--green-excel-dark) !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
}
.admission-table thead tr.admission-filter-row th.fixed_item {
    background-color: var(--green-excel-light) !important;
    z-index: 8 !important;
    border-left: 1px solid #b8c8d5 !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 2px solid var(--green-excel-border) !important;
}

/* Loading & Pagination */
.admission-table.table-loading { opacity: 0.45; pointer-events: none; }
.admission-loader-bar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--green-excel);
    z-index: 10;
    display: none;
}

/* Empty State Full Table Center */
.admission-table tbody tr.admission-empty-row {
    height: calc(100vh - 270px) !important;
    min-height: 380px !important;
    background: #ffffff !important;
}
.admission-table tbody tr.admission-empty-row:hover td {
    background-color: #ffffff !important;
}
.admission-table tbody tr.admission-empty-row td.admission-empty-cell {
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
    border-radius: 4px !important;
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

.admission-pagination-footer {
    padding: 6px 14px;
    background: #f8fafc;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.user-pagination .pagination { margin: 0; }
.user-pagination .page-item .page-link {
    font-size: 11px;
    padding: 3px 8px;
    color: #1e293b;
    border: 1px solid var(--line-subtle);
}
.user-pagination .page-item.active .page-link {
    background-color: var(--green-excel) !important;
    border-color: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
}

/* Status Badges */
.badge-status-active {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
    padding: 2px 7px;
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-status-inactive {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
    padding: 2px 7px;
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.role-badge {
    font-size: 10.5px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 3px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    display: inline-block;
}

/* ========================================================
   MODAL THEME (Matching https://girraj.test/student/promote_add)
   ======================================================== */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 2px !important;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.22) !important;
    background: #ffffff !important;
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
    display: flex !important;
    align-items: center !important;
}
.modal-title i {
    color: #217346 !important;
    font-size: 14px !important;
    margin-right: 6px;
}
.modal-title.text-danger i {
    color: #dc2626 !important;
}
.modal-header .close {
    margin: 0 !important;
    padding: 2px 6px !important;
    color: #344450 !important;
    opacity: 0.9 !important;
    font-size: 20px !important;
    background: transparent !important;
    border: 0 !important;
    cursor: pointer !important;
}
.modal-header .close:hover {
    color: #000000 !important;
}
.modal-body {
    padding: 12px 14px !important;
    background: #ffffff !important;
}
.modal-footer {
    padding: 8px 12px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 6px !important;
}
.btn-search-action {
    height: 29px !important;
    padding: 3px 12px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #217346 !important;
    border: 1px solid #185c37 !important;
    color: #ffffff !important;
    border-radius: 2px !important;
    transition: all 0.15s ease-in-out;
}
.btn-search-action:hover {
    background: #185c37 !important;
    color: #ffffff !important;
}
.row-updated-highlight {
    animation: rowFlash 1.5s ease-in-out;
}
@keyframes rowFlash {
    0% { background-color: #fef08a !important; }
    50% { background-color: #dcfce7 !important; }
    100% { background-color: transparent; }
}

.modal-header-green {
    padding: 8px 14px !important;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.modal-title-green {
    margin: 0 !important;
    color: #ffffff !important;
    font-size: 13.5px !important;
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
}

.detail-section-title {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--green-excel-dark);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border-bottom: 2px solid var(--green-excel-border);
    padding-bottom: 4px;
    margin-bottom: 10px;
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-item {
    font-size: 11.5px;
    margin-bottom: 6px;
    display: flex;
}
.detail-label {
    width: 125px;
    font-weight: 600;
    color: #475569;
    flex-shrink: 0;
}
.detail-value {
    color: #0f172a;
    font-weight: 500;
    word-break: break-word;
}

/* ========================================================
   EXECUTIVE STAFF PROFILE MODAL MODERN STYLES
   ======================================================== */
.modal-header-executive {
    background: linear-gradient(135deg, #185c37 0%, #217346 100%) !important;
    padding: 10px 16px !important;
    color: #ffffff !important;
    border-bottom: 2px solid #144d2d !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.modal-header-executive .header-icon-box {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}
.modal-header-executive .modal-title-main {
    font-size: 14.5px;
    font-weight: 700;
    line-height: 1.25;
    color: #ffffff !important;
}
.modal-header-executive .modal-subtitle-text {
    font-size: 11px;
    color: #d1fae5;
    font-weight: 400;
}
.modal-header-executive .btn-close-modal {
    background: transparent !important;
    border: none !important;
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    opacity: 0.85;
    cursor: pointer;
    transition: opacity 0.15s ease-in-out;
}
.modal-header-executive .btn-close-modal:hover {
    opacity: 1;
}

.staff-hero-sidebar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 16px 12px;
    text-align: center;
    height: 100%;
}
.staff-hero-avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: 8px;
}
.staff-hero-avatar {
    width: 96px;
    height: 96px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
.staff-hero-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-bottom: 4px;
}
.staff-hero-code {
    display: inline-block;
    background: #edf2f7;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-weight: 700;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 3px;
    margin-bottom: 6px;
}
.staff-meta-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 8px 10px;
    text-align: left;
    font-size: 11px;
    margin-top: 10px;
}
.staff-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px dashed #e2e8f0;
}
.staff-meta-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.staff-section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}
.staff-section-card:last-child {
    margin-bottom: 0;
}
.staff-section-card-header {
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    padding: 6px 12px;
    font-size: 11.5px;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.staff-section-card-body {
    padding: 10px 12px;
}
.staff-field-box {
    display: flex;
    flex-direction: column;
    margin-bottom: 8px;
}
.staff-field-label {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    margin-bottom: 2px;
}
.staff-field-value {
    font-size: 11.5px;
    font-weight: 600;
    color: #0f172a;
    word-break: break-word;
}
</style>

<div class="content-wrapper user-directory-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            <div class="card admission-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="admission-titlebar">
                    <h1 class="admission-title">
                        <i class="fa fa-users text-warning"></i>
                        <span>{{ __('Staff & User Management') }}</span>
                        <small id="totalUsersBadge">({{ $totalCount }} Users)</small>
                    </h1>
                    <div class="title-actions">
                        <button type="button" class="btn-header-action" id="btn-checkall" data-status="0" title="Select / Deselect All">
                            <i class="fa fa-square-o mr-1" id="check_box_icon"></i> Select All
                        </button>
                        <button type="button" class="btn-header-action" id="btnPrintTable" onclick="printUserTable();" title="Print Directory">
                            <i class="fa fa-print text-primary mr-1"></i> Print
                        </button>
                        @if($canAdd)
                            <a href="{{ url('addUser') }}" class="btn-header-action btn-header-add" title="Add User">
                                <i class="fa fa-plus text-success mr-1"></i> {{ __('common.Add') }} User
                            </a>
                        @endif
                        <a href="{{ url('user_dashboard') }}" class="btn-header-action" title="Back to Dashboard">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Main Filter Form & Table with Sticky Respective Thead Filters -->
                <form id="userFilterForm" action="{{ url('viewUser') }}" method="get" class="w-100">
                    <input type="hidden" name="page" id="userCurrentPage" value="{{ ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->currentPage() : 1 }}">

                    <!-- Table Container with Sticky Thead Filter Row -->
                    <div class="admission-scroll">
                        <div id="userTableLoader" class="admission-loader-bar"></div>

                        <table id="userList" class="admission-table">
                            <thead>
                                <!-- 1st Header Row (Column Titles) -->
                                <tr>
                                    <th style="width: 36px;" class="text-center">#</th>
                                    <th style="width: 50px;" class="text-center">{{ __('common.SR.NO') }}</th>
                                    <th style="width: 44px;" class="text-center">Photo</th>
                                    <th style="width: 95px;" class="text-center">Staff ID</th>
                                    <th style="min-width: 140px;">Staff Name</th>
                                    <th style="min-width: 110px;">Role</th>
                                    <th style="min-width: 100px;">Department</th>
                                    <th style="min-width: 100px;">Designation</th>
                                    <th style="min-width: 110px;">Mobile</th>
                                    <th style="min-width: 130px;">Email</th>
                                    <th style="min-width: 90px;" class="text-center">Joining Date</th>
                                    <th style="min-width: 85px;" class="text-center">Status</th>
                                    <th class="fixed_item text-center" style="width: 110px;">{{ __('common.Action') }}</th>
                                </tr>

                                <!-- 2nd Header Row: Exact 1-to-1 Respective Live Filter Row -->
                                <tr class="admission-filter-row">
                                    <!-- Under # -->
                                    <th class="text-center" style="width: 36px;">
                                        <button type="button" id="userResetBtn" class="btn-row-action" title="Reset All Filters" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b; margin:0 auto;">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </th>

                                    <!-- Under SR.NO -->
                                    <th class="text-center" style="width: 50px;">
                                        <input type="text" class="form-control form-control-sm live-user-input text-center" name="id" value="{{ $search['id'] ?? '' }}" placeholder="ID">
                                    </th>

                                    <!-- Under Photo -->
                                    <th style="width: 44px;"></th>

                                    <!-- Under Staff ID -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-user-input" name="UniqueId" value="{{ $search['UniqueId'] ?? '' }}" placeholder="Staff ID..." style="min-width: 90px;">
                                    </th>

                                    <!-- Under Staff Name -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-user-input" name="name" value="{{ $search['name'] ?? '' }}" placeholder="Name / Username..." style="min-width: 130px;">
                                    </th>

                                    <!-- Under Role -->
                                    <th>
                                        <select class="form-control form-control-sm live-user-select" name="role_id" style="min-width: 100px;">
                                            <option value="">All</option>
                                            @if(!empty($roles))
                                                @foreach($roles as $r)
                                                    <option value="{{ $r->id }}" {{ (($search['role_id'] ?? '') == $r->id) ? 'selected' : '' }}>{{ $r->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- Under Department -->
                                    <th>
                                        <select class="form-control form-control-sm live-user-select" name="department" style="min-width: 95px;">
                                            <option value="">All</option>
                                            @if(!empty($departments))
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept }}" {{ (($search['department'] ?? '') == $dept) ? 'selected' : '' }}>{{ $dept }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- Under Designation -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-user-input" name="designation" value="{{ $search['designation'] ?? '' }}" placeholder="Designation..." style="min-width: 95px;">
                                    </th>

                                    <!-- Under Mobile -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-user-input" name="mobile" value="{{ $search['mobile'] ?? '' }}" placeholder="Mobile..." style="min-width: 100px;">
                                    </th>

                                    <!-- Under Email -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-user-input" name="email" value="{{ $search['email'] ?? '' }}" placeholder="Email..." style="min-width: 120px;">
                                    </th>

                                    <!-- Under Joining Date -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-user-input text-center" name="joining_date" value="{{ $search['joining_date'] ?? '' }}" placeholder="Date..." style="min-width: 85px;">
                                    </th>

                                    <!-- Under Status (Status Filter Dropdown) -->
                                    <th class="text-center" style="min-width: 85px;">
                                        <select class="form-control form-control-sm live-user-select" id="status" name="status" style="width: 100%; height: 25px; font-size: 11px;" title="Filter by Status">
                                            <option value="all" {{ ($search['status'] ?? '') === 'all' ? 'selected' : '' }}>All Status</option>
                                            <option value="1" {{ ($search['status'] ?? '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ ($search['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </th>

                                    <!-- Under Action (Sticky fixed_item) -->
                                    <th class="fixed_item text-center" style="width: 110px;"></th>
                                </tr>
                            </thead>

                            <!-- Dynamic Real-Time Rows Container -->
                            <tbody id="product_list_show">
                                @include('user.users.table_rows', ['data' => $data])
                            </tbody>
                        </table>
                    </div>

                    <!-- 3. Pagination & Per-Page Footer -->
                    <div class="admission-pagination-footer">
                        <div class="d-flex align-items-center gap-2">
                            <span id="userEntriesInfo" class="small text-muted font-weight-bold">
                                Showing {{ $firstItem }} to {{ $lastItem }} of {{ $totalCount }} users
                            </span>
                            <div class="d-flex align-items-center ml-3">
                                <label class="small text-muted mb-0 mr-1">Show</label>
                                <select id="userPerPage" name="per_page" class="form-control form-control-sm live-user-select" style="width: 70px; height: 28px; padding: 2px 6px;">
                                    <option value="25" {{ ($search['per_page'] ?? '25') == '25' ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($search['per_page'] ?? '') == '50' ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ ($search['per_page'] ?? '') == '100' ? 'selected' : '' }}>100</option>
                                    <option value="250" {{ ($search['per_page'] ?? '') == '250' ? 'selected' : '' }}>250</option>
                                    <option value="all" {{ ($search['per_page'] ?? '') == 'all' ? 'selected' : '' }}>All</option>
                                </select>
                                <label class="small text-muted mb-0 ml-1">per page</label>
                            </div>
                        </div>

                        <div id="userPaginationContainer">
                            @include('user.users.pagination', ['data' => $data])
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

<!-- ========================================================
     MODAL: USER FULL DETAILS VIEW
     ======================================================== -->
<!-- ========================================================
     MODAL: USER FULL DETAILS VIEW (EXECUTIVE MODERN DOSSIER)
     ======================================================== -->
<div class="modal fade" id="userDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 8px; overflow: hidden;">
            
            <!-- 1. Executive Header -->
            <div class="modal-header-executive">
                <div class="d-flex align-items-center">
                    <div class="header-icon-box mr-2">
                        <i class="fa fa-id-card text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title-main mb-0" id="modalStaffName">
                            Staff Profile Details
                        </h5>
                        <small class="modal-subtitle-text">
                            Comprehensive Employee Record & Official Dossier
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="modalHeaderStatusBadge" class="mr-2"></span>
                    <button type="button" class="btn-close-modal" data-dismiss="modal" aria-label="Close" title="Close Modal">&times;</button>
                </div>
            </div>

            <!-- 2. Modal Body -->
            <div class="modal-body p-3" style="background: #f8fafc;">
                <div class="row">
                    
                    <!-- Left Column: Hero Sidebar -->
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                        <div class="staff-hero-sidebar shadow-sm">
                            <div class="staff-hero-avatar-wrap">
                                <img id="modalUserPhoto" 
                                     src="{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}" 
                                     onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'"
                                     class="staff-hero-avatar">
                            </div>
                            <h6 class="staff-hero-name" id="modalUserNameOverview">-</h6>
                            <div class="mb-1">
                                <span class="badge px-2 py-1 font-weight-bold" id="modalUserRoleOverview" style="font-size: 11px;">-</span>
                            </div>
                            <div class="staff-hero-code" id="modalStaffCodeOverview">EMP-0000</div>
                            <div id="modalStatusBadgeOverview" class="mb-2"></div>

                            <div class="staff-meta-card">
                                <div class="staff-meta-row">
                                    <span class="text-muted"><i class="fa fa-at mr-1 text-secondary"></i> Username:</span>
                                    <strong class="text-dark" id="modalUsernameVal">-</strong>
                                </div>
                                <div class="staff-meta-row">
                                    <span class="text-muted"><i class="fa fa-calendar-check-o mr-1 text-secondary"></i> Joining:</span>
                                    <strong class="text-dark" id="modalJoiningVal">-</strong>
                                </div>
                                <div class="staff-meta-row">
                                    <span class="text-muted"><i class="fa fa-clock-o mr-1 text-secondary"></i> Shift:</span>
                                    <strong class="text-dark" id="modalShiftVal">-</strong>
                                </div>
                                <div class="staff-meta-row">
                                    <span class="text-muted"><i class="fa fa-sitemap mr-1 text-secondary"></i> Dept:</span>
                                    <strong class="text-dark" id="modalDeptSidebar">-</strong>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="#" id="modalSidebarEditBtn" class="btn btn-sm btn-outline-success btn-block font-weight-bold">
                                    <i class="fa fa-pencil mr-1"></i> Edit Staff Record
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Structured Cards -->
                    <div class="col-lg-9 col-md-8">
                        
                        <!-- Card 1: Personal & Demographic Info -->
                        <div class="staff-section-card">
                            <div class="staff-section-card-header">
                                <i class="fa fa-user-circle text-success"></i> 1. Personal & Demographic Information
                            </div>
                            <div class="staff-section-card-body">
                                <div class="row">
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Father's Name</span>
                                        <span class="staff-field-value" id="modalFatherName">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Mother's Name</span>
                                        <span class="staff-field-value" id="modalMotherName">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Spouse Name</span>
                                        <span class="staff-field-value" id="modalSpouseName">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Gender / DOB</span>
                                        <span class="staff-field-value" id="modalGenderDob">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Marital Status</span>
                                        <span class="staff-field-value" id="modalMaritalStatus">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Blood Group</span>
                                        <span class="staff-field-value text-danger font-weight-bold" id="modalBloodGroup">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">Aadhaar Number</span>
                                        <span class="staff-field-value text-monospace" id="modalAadhaarNo">-</span>
                                    </div>
                                    <div class="col-sm-3 col-6 staff-field-box">
                                        <span class="staff-field-label">PAN Number</span>
                                        <span class="staff-field-value text-monospace" id="modalPanNo">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Designation & Employment Details -->
                        <div class="staff-section-card">
                            <div class="staff-section-card-header">
                                <i class="fa fa-briefcase text-primary"></i> 2. Designation & Employment Details
                            </div>
                            <div class="staff-section-card-body">
                                <div class="row">
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Department</span>
                                        <span class="staff-field-value" id="modalDepartment">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Designation</span>
                                        <span class="staff-field-value font-weight-bold text-dark" id="modalDesignation">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Qualification</span>
                                        <span class="staff-field-value" id="modalQualification">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Experience</span>
                                        <span class="staff-field-value" id="modalExperience">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Contract Type</span>
                                        <span class="staff-field-value" id="modalContractType">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Basic Salary</span>
                                        <span class="staff-field-value text-success font-weight-bold" style="font-size: 13px;" id="modalSalary">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Contact & Address Information -->
                        <div class="staff-section-card">
                            <div class="staff-section-card-header">
                                <i class="fa fa-map-marker text-danger"></i> 3. Contact & Address Information
                            </div>
                            <div class="staff-section-card-body">
                                <div class="row">
                                    <div class="col-sm-4 col-12 staff-field-box">
                                        <span class="staff-field-label">Primary Mobile</span>
                                        <span class="staff-field-value">
                                            <a id="modalMobileLink" href="#" class="text-primary font-weight-bold text-decoration-none">
                                                <i class="fa fa-phone mr-1"></i><span id="modalMobile">-</span>
                                            </a>
                                        </span>
                                    </div>
                                    <div class="col-sm-4 col-12 staff-field-box">
                                        <span class="staff-field-label">Email Address</span>
                                        <span class="staff-field-value">
                                            <a id="modalEmailLink" href="#" class="text-primary text-decoration-none">
                                                <i class="fa fa-envelope-o mr-1"></i><span id="modalEmail">-</span>
                                            </a>
                                        </span>
                                    </div>
                                    <div class="col-sm-4 col-12 staff-field-box">
                                        <span class="staff-field-label">Emergency Mobile</span>
                                        <span class="staff-field-value text-muted" id="modalEmergencyNo">-</span>
                                    </div>
                                    <div class="col-sm-5 col-12 staff-field-box">
                                        <span class="staff-field-label">Present Address</span>
                                        <span class="staff-field-value" id="modalPresentAddress">-</span>
                                    </div>
                                    <div class="col-sm-5 col-12 staff-field-box">
                                        <span class="staff-field-label">Permanent Address</span>
                                        <span class="staff-field-value" id="modalPermAddress">-</span>
                                    </div>
                                    <div class="col-sm-2 col-12 staff-field-box">
                                        <span class="staff-field-label">Pincode</span>
                                        <span class="staff-field-value font-weight-bold" id="modalPincode">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Bank Account & Statutory Details -->
                        <div class="staff-section-card">
                            <div class="staff-section-card-header">
                                <i class="fa fa-university text-info"></i> 4. Bank Account & Statutory Details
                            </div>
                            <div class="staff-section-card-body">
                                <div class="row">
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Bank Name</span>
                                        <span class="staff-field-value font-weight-bold text-dark" id="modalBankName">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Account Number</span>
                                        <span class="staff-field-value font-weight-bold text-dark text-monospace" id="modalAccountNo">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">IFSC Code</span>
                                        <span class="staff-field-value">
                                            <span class="badge badge-light border text-monospace font-weight-bold text-dark px-2" id="modalIfscCode">-</span>
                                        </span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">Bank Branch</span>
                                        <span class="staff-field-value" id="modalBankBranch">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">EPF Number</span>
                                        <span class="staff-field-value text-monospace" id="modalEpfNo">-</span>
                                    </div>
                                    <div class="col-sm-4 col-6 staff-field-box">
                                        <span class="staff-field-label">ESI Number</span>
                                        <span class="staff-field-value text-monospace" id="modalEsiNo">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 5: Documents Uploaded -->
                        <div class="staff-section-card">
                            <div class="staff-section-card-header">
                                <i class="fa fa-folder-open text-warning"></i> 5. Uploaded Documents & Records
                            </div>
                            <div class="staff-section-card-body">
                                <div class="d-flex flex-wrap gap-2" id="modalDocButtons">
                                    <!-- Dynamic Document Buttons -->
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- 3. Modal Footer -->
            <div class="modal-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted d-none d-sm-block">
                    <i class="fa fa-shield text-success mr-1"></i> Verified Staff Record &bull; Girraj College Management System
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printUserProfileModal()">
                        <i class="fa fa-print mr-1"></i> Print Profile
                    </button>
                    <a href="#" id="modalEditBtn" class="btn btn-sm btn-success px-3 font-weight-bold">
                        <i class="fa fa-pencil mr-1"></i> Edit Profile
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================
     MODAL: CHANGE STATUS CONFIRMATION (Matching student/promote_add)
     ======================================================== -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-toggle-on"></i> Change Account Status
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('userStatus') }}" method="POST" id="statusForm">
                @csrf
                <input type="hidden" id="status_id" name="status_id">
                <input type="hidden" id="status_user_id" name="id">

                <div class="modal-body p-3">
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3" id="statusModalIconBox" style="width: 38px; height: 38px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa fa-toggle-on text-success" id="statusModalTopIcon" style="font-size: 18px;"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;" id="statusModalTitleText">Change User Account Status</strong>
                            <span class="text-muted" style="font-size: 11.5px;">Please review the status parameters below:</span>
                        </div>
                    </div>

                    <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Staff Name:</span>
                            <strong class="text-dark" id="statusModalStaffName">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Staff / Unique ID:</span>
                            <strong class="text-dark" id="statusModalStaffCode">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Role & Dept:</span>
                            <span class="text-dark" id="statusModalStaffRole">-</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Target New Status:</span>
                            <span id="statusModalNewStatusBadge">-</span>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0" id="statusModalInfoNote" style="font-size: 11px; border-radius: 2px; background: #e7eff5; border-color: #b8c9d9; color: #1e3a5f;">
                        <i class="fa fa-info-circle mr-1"></i> <span id="statusModalInfoText">Switching status will update portal access and user authentication permissions.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-search-action px-3" id="btnConfirmStatus">
                        <i class="fa fa-check mr-1"></i> Confirm & Change Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL: DELETE CONFIRMATION (Matching student/promote_add)
     ======================================================== -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #991b1b !important;">
                    <i class="fa fa-trash text-danger"></i> Delete User Confirmation
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('deleteUser') }}" method="POST" id="deleteUserForm">
                @csrf
                <input type="hidden" id="delete_id" name="delete_id">

                <div class="modal-body p-3">
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa fa-trash-o text-danger" style="font-size: 18px;"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Delete Staff Member</strong>
                            <span class="text-muted" style="font-size: 11.5px;">Are you sure you want to permanently delete this user?</span>
                        </div>
                    </div>

                    <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Staff Name:</span>
                            <strong class="text-dark" id="deleteModalStaffName">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Staff / Unique ID:</span>
                            <strong class="text-dark" id="deleteModalStaffCode">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Role & Dept:</span>
                            <span class="text-dark" id="deleteModalStaffRole">-</span>
                        </div>
                    </div>

                    <div class="alert alert-danger py-2 px-3 mb-0" style="font-size: 11px; border-radius: 2px; background: #fef2f2; border-color: #fecaca; color: #991b1b;">
                        <i class="fa fa-exclamation-triangle mr-1"></i> <strong>Warning:</strong> This action cannot be undone and will permanently remove this user profile and privileges.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3 font-weight-bold" id="btnConfirmDelete" style="height: 29px; font-size: 11.5px; border-radius: 2px; background: #dc2626; border-color: #b91c1c;">
                        <i class="fa fa-trash mr-1"></i> Confirm & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL: PHOTO PREVIEW
     ======================================================== -->
<div class="modal fade" id="profileImgModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="profileImgName"><i class="fa fa-user"></i> User Photo</h6>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="profileImg" src="" class="img-fluid rounded border shadow-sm" style="max-height: 280px; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    var filterTimer = null;
    var userRequest = null;

    // 1. Real-time AJAX Loader Function
    function loadUsers(updateUrl, targetPage) {
        if (targetPage !== undefined) {
            $('#userCurrentPage').val(targetPage);
        }

        var form = $('#userFilterForm');
        var formData = form.serialize();
        var fullUrl = form.attr('action') + '?' + formData;

        if (updateUrl !== false && window.history.pushState) {
            window.history.pushState({ path: fullUrl }, '', fullUrl);
        }

        if (userRequest) {
            userRequest.abort();
        }

        $('#userList').addClass('table-loading');
        $('#userTableLoader').show();

        userRequest = $.ajax({
            url: fullUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function(response) {
            if (response && response.status === 'success') {
                $('#product_list_show').html(response.html);
                $('#userPaginationContainer').html(response.pagination);

                var total = response.total || 0;
                var from = response.from || 0;
                var to = response.to || 0;

                $('#totalUsersBadge').text('(' + total + ' Users)');
                if (total > 0) {
                    $('#userEntriesInfo').text('Showing ' + from + ' to ' + to + ' of ' + total + ' users');
                } else {
                    $('#userEntriesInfo').text('Showing 0 to 0 of 0 users');
                }
            }
        })
        .fail(function(xhr, status) {
            if (status !== 'abort') {
                console.error('Error loading user directory records:', xhr);
                $('#product_list_show').html('<tr class="admission-empty-row"><td colspan="100" class="admission-empty-cell"><div class="empty-state-wrap"><div class="empty-state-icon-wrap" style="background:#fef2f2; color:#dc2626; border-color:#fca5a5;"><i class="fa fa-exclamation-triangle"></i></div><div class="empty-state-title">Failed to load user data</div><p class="empty-state-subtitle">An error occurred while fetching records. Please try again.</p><button type="button" class="btn-empty-reset" onclick="$(\'#userResetBtn\').trigger(\'click\');"><i class="fa fa-refresh mr-1"></i> Retry / Reset</button></div></td></tr>');
            }
        })
        .always(function() {
            $('#userList').removeClass('table-loading');
            $('#userTableLoader').hide();
        });
    }

    // 2. Live Event Listeners (Debounced input & immediate select changes)
    $(document).on('input', '.live-user-input', function() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            loadUsers(true, 1);
        }, 350);
    });

    $(document).on('change', '.live-user-select', function() {
        loadUsers(true, 1);
    });

    // 3. AJAX Pagination Switch (Page change in real-time)
    $(document).on('click', '.user-pagination .page-link', function(e) {
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
            loadUsers(true, page);
            $('.admission-scroll').scrollTop(0);
        }
    });

    // 4. Reset All Filters
    $('#userResetBtn').on('click', function() {
        $('#userFilterForm')[0].reset();
        $('#userFilterForm select').val('');
        $('#userFilterForm input[type="text"]').val('');
        $('#status').val('1');
        $('#userPerPage').val('25');
        $('#userCurrentPage').val(1);
        loadUsers(true, 1);
    });

    // 5. Select All Checkboxes
    $(document).on('click', '#btn-checkall', function() {
        var status = parseInt($(this).attr('data-status'));
        if (status === 0) {
            $(this).attr('data-status', 1);
            $('#check_box_icon').removeClass('fa-square-o').addClass('fa-check-square');
            $('.checkbox_id').prop('checked', true);
        } else {
            $(this).attr('data-status', 0);
            $('#check_box_icon').removeClass('fa-check-square').addClass('fa-square-o');
            $('.checkbox_id').prop('checked', false);
        }
    });

    // 6. Photo Preview Modal
    $(document).on('click', '.profileImg, .view-avatar-preview', function() {
        var img = $(this).data('img');
        var name = $(this).data('name') || 'User Photo';
        if (img) {
            $('#profileImgName').text(name);
            $('#profileImg').attr('src', img);
            $('#profileImgModal').modal('show');
        }
    });

    // 7. Trigger Status Modal (Matching promote_add modal parameter box)
    $(document).on('click', '.btn-trigger-status', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var userId = $(this).data('id');
        var newStatus = parseInt($(this).data('status'));
        var userName = $(this).data('name') || 'Staff Member';
        var staffId = $(this).data('staff-id') || ('EMP-' + String(userId).padStart(4, '0'));
        var role = $(this).data('role') || 'User';
        var dept = $(this).data('dept') || 'General';

        $('#status_user_id').val(userId);
        $('#status_id').val(newStatus);
        $('#statusModalStaffName').text(userName);
        $('#statusModalStaffCode').text(staffId);
        $('#statusModalStaffRole').text(role + ' / ' + dept);

        if (newStatus === 1) {
            $('#statusModalIconBox').css('background', '#e7f5ec');
            $('#statusModalTopIcon').removeClass('fa-ban text-danger text-warning').addClass('fa-toggle-on text-success');
            $('#statusModalTitleText').text('Activate Staff Account');
            $('#statusModalNewStatusBadge').html('<span class="badge-status-active"><i class="fa fa-check"></i> Active</span>');
            $('#statusModalInfoText').html('Activating this user will enable portal login access and active permissions.');
            $('#btnConfirmStatus').removeClass('btn-danger').addClass('btn-search-action').html('<i class="fa fa-check mr-1"></i> Confirm & Activate');
        } else {
            $('#statusModalIconBox').css('background', '#fef3c7');
            $('#statusModalTopIcon').removeClass('fa-toggle-on text-success').addClass('fa-ban text-danger');
            $('#statusModalTitleText').text('Deactivate Staff Account');
            $('#statusModalNewStatusBadge').html('<span class="badge-status-inactive"><i class="fa fa-times"></i> Inactive</span>');
            $('#statusModalInfoText').html('Deactivating this user will suspend portal login access and restrict operations.');
            $('#btnConfirmStatus').removeClass('btn-search-action').addClass('btn-danger').html('<i class="fa fa-ban mr-1"></i> Confirm & Deactivate');
        }

        $('#statusModal').modal('show');
    });

    // 8. Submit Status Form via Real-time AJAX (No Page Reload)
    $('#statusForm').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#btnConfirmStatus');
        var userId = $('#status_user_id').val();
        var nextStatus = parseInt($('#status_id').val());
        var originalBtnHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Updating...');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                $('#statusModal').modal('hide');
                $btn.prop('disabled', false).html(originalBtnHtml);

                if (res && res.status === 'success') {
                    // Update DOM row in real-time
                    var $row = $('#row-user-' + userId);
                    if ($row.length) {
                        var $cell = $row.find('.status-col-cell');
                        var $statusBtn = $cell.find('.btn-trigger-status');
                        var userName = $statusBtn.data('name') || $('#statusModalStaffName').text();
                        var staffId = $statusBtn.data('staff-id') || $('#statusModalStaffCode').text();
                        var role = $statusBtn.data('role') || 'User';
                        var dept = $statusBtn.data('dept') || 'General';

                        if (nextStatus === 1) {
                            $cell.html(
                                '<span class="badge-status-active btn-trigger-status" ' +
                                'data-id="' + userId + '" ' +
                                'data-name="' + userName + '" ' +
                                'data-staff-id="' + staffId + '" ' +
                                'data-role="' + role + '" ' +
                                'data-dept="' + dept + '" ' +
                                'data-status="0" title="Click to Change Status">' +
                                '<i class="fa fa-check"></i> Active</span>'
                            );
                        } else {
                            $cell.html(
                                '<span class="badge-status-inactive btn-trigger-status" ' +
                                'data-id="' + userId + '" ' +
                                'data-name="' + userName + '" ' +
                                'data-staff-id="' + staffId + '" ' +
                                'data-role="' + role + '" ' +
                                'data-dept="' + dept + '" ' +
                                'data-status="1" title="Click to Change Status">' +
                                '<i class="fa fa-times"></i> Inactive</span>'
                            );
                        }

                        // Row Flash animation
                        $row.addClass('row-updated-highlight');
                        setTimeout(function() {
                            $row.removeClass('row-updated-highlight');
                        }, 1500);
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Status updated successfully!');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Failed to update status.');
                    } else {
                        alert(res.message || 'Failed to update status.');
                    }
                }
            },
            error: function(xhr) {
                $('#statusModal').modal('hide');
                $btn.prop('disabled', false).html(originalBtnHtml);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error occurred while changing status.');
                } else {
                    alert('Error occurred while changing status.');
                }
            }
        });
    });

    // 9. Trigger Delete Modal (Matching promote_add modal parameter box)
    $(document).on('click', '.btn-trigger-delete', function(e) {
        e.preventDefault();
        var deleteId = $(this).data('id');
        var userName = $(this).data('name') || 'Staff Member';
        var staffId = $(this).data('staff-id') || ('EMP-' + String(deleteId).padStart(4, '0'));
        var role = $(this).data('role') || 'User';
        var dept = $(this).data('dept') || 'General';

        $('#delete_id').val(deleteId);
        $('#deleteModalStaffName').text(userName);
        $('#deleteModalStaffCode').text(staffId);
        $('#deleteModalStaffRole').text(role + ' / ' + dept);
        $('#Modal_id').modal('show');
    });

    // 10. Submit Delete Form via Real-time AJAX (No Page Reload)
    $('#deleteUserForm').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#btnConfirmDelete');
        var deleteId = $('#delete_id').val();
        var originalBtnHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Deleting...');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                $('#Modal_id').modal('hide');
                $btn.prop('disabled', false).html(originalBtnHtml);

                if (res && res.status === 'success') {
                    var $row = $('#row-user-' + deleteId);
                    if ($row.length) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            if ($('#product_list_show tr').length === 0) {
                                loadUsers(false);
                            }
                        });
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'User deleted successfully.');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res.message || 'Failed to delete user.');
                    } else {
                        alert(res.message || 'Failed to delete user.');
                    }
                }
            },
            error: function(xhr) {
                $('#Modal_id').modal('hide');
                $btn.prop('disabled', false).html(originalBtnHtml);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error occurred while deleting user.');
                } else {
                    alert('Error occurred while deleting user.');
                }
            }
        });
    });

    // 11. Open User Full Detail View Modal (Executive Dossier View)
    $(document).on('click', '.btn-view-user-detail', function() {
        var $btn = $(this);
        var rawUser = $btn.attr('data-user') || $btn.data('user');
        var user = rawUser;
        if (typeof user === 'string') {
            try {
                user = JSON.parse(user);
            } catch(e) {
                user = {};
            }
        }
        if (!user || typeof user !== 'object') {
            user = {};
        }

        // Fill fallback properties from explicit HTML attributes
        user.id = user.id || $btn.data('id') || $btn.attr('data-id');
        var attrName = $btn.data('name') || $btn.attr('data-name');
        var attrStaffId = $btn.data('staff-id') || $btn.attr('data-staff-id');
        var attrRole = $btn.data('role') || $btn.attr('data-role');
        var attrDept = $btn.data('dept') || $btn.attr('data-dept');
        var attrUsername = $btn.data('username') || $btn.attr('data-username');
        var attrMobile = $btn.data('mobile') || $btn.attr('data-mobile');
        var attrEmail = $btn.data('email') || $btn.attr('data-email');
        var attrStatus = $btn.data('status') !== undefined ? $btn.data('status') : $btn.attr('data-status');
        var imgUrl = $btn.data('img') || $btn.attr('data-img') || '{{ env('IMAGE_SHOW_PATH') }}default/user_image.jpg';

        var fullName = ((user.first_name || '') + ' ' + (user.last_name || '')).trim() || attrName || user.userName || attrUsername || 'Staff Member';
        var staffCode = user.UniqueId || attrStaffId || (user.id ? ('EMP-' + String(user.id).padStart(4, '0')) : 'EMP-0001');
        var roleName = (user.role_name && user.role_name.name) ? user.role_name.name : (attrRole || (user.role_id == 1 ? 'Admin' : (user.role_id == 2 ? 'Teacher' : 'Staff')));
        var department = user.department || attrDept || 'General';

        // Header info
        $('#modalStaffName').text(fullName + ' (' + staffCode + ')');
        
        // Status Badges
        var isActive = (user.status == 1 || attrStatus == 1);
        if (isActive) {
            $('#modalStatusBadgeOverview').html('<span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i> Active</span>');
            $('#modalHeaderStatusBadge').html('<span class="badge badge-success border border-white text-white px-2 py-1"><i class="fa fa-circle text-white mr-1" style="font-size: 8px;"></i> Active</span>');
        } else {
            $('#modalStatusBadgeOverview').html('<span class="badge badge-danger px-2 py-1"><i class="fa fa-times-circle mr-1"></i> Inactive</span>');
            $('#modalHeaderStatusBadge').html('<span class="badge badge-danger border border-white text-white px-2 py-1"><i class="fa fa-circle text-white mr-1" style="font-size: 8px;"></i> Inactive</span>');
        }

        // Hero overview
        $('#modalUserNameOverview').text(fullName);
        $('#modalUserRoleOverview').text(roleName);
        if (roleName.toLowerCase().indexOf('admin') !== -1) {
            $('#modalUserRoleOverview').css({'background': '#4338ca', 'color': '#ffffff'});
        } else if (roleName.toLowerCase().indexOf('teacher') !== -1) {
            $('#modalUserRoleOverview').css({'background': '#0f766e', 'color': '#ffffff'});
        } else {
            $('#modalUserRoleOverview').css({'background': '#0369a1', 'color': '#ffffff'});
        }
        $('#modalStaffCodeOverview').text('ID: ' + staffCode);
        $('#modalUserPhoto').attr('src', imgUrl);

        // Sidebar metadata
        $('#modalUsernameVal').text(user.userName || attrUsername || '-');
        $('#modalJoiningVal').text(user.joining_date || '-');
        $('#modalShiftVal').text(user.shift || 'Regular');
        $('#modalDeptSidebar').text(department);

        // Personal
        $('#modalFatherName').text(user.father_name || '-');
        $('#modalMotherName').text(user.mother_name || '-');
        $('#modalSpouseName').text(user.spouse_name || '-');
        $('#modalGenderDob').text((user.gender || '-') + (user.dob ? (' (' + user.dob + ')') : ''));
        $('#modalMaritalStatus').text(user.marital_status || '-');
        $('#modalBloodGroup').text(user.blood_group || '-');
        $('#modalAadhaarNo').text(user.aadhaar_no || '-');
        $('#modalPanNo').text(user.pan_number || '-');

        // Employment
        $('#modalDepartment').text(department);
        $('#modalDesignation').text(user.designation || '-');
        $('#modalQualification').text(user.qualification || '-');
        $('#modalExperience').text(user.work_experience ? (user.work_experience + ' Years') : '-');
        $('#modalContractType').text(user.contract_type || 'Regular');
        var salaryVal = user.basic_salary || user.salary;
        $('#modalSalary').text(salaryVal ? '₹ ' + parseFloat(salaryVal).toLocaleString('en-IN', {minimumFractionDigits: 2}) : '-');

        // Contact & Address
        var primaryMobile = user.mobile || attrMobile || '-';
        $('#modalMobile').text(primaryMobile);
        if (primaryMobile && primaryMobile !== '-') {
            $('#modalMobileLink').attr('href', 'tel:' + primaryMobile);
        } else {
            $('#modalMobileLink').removeAttr('href');
        }

        var primaryEmail = user.email || attrEmail || '-';
        $('#modalEmail').text(primaryEmail);
        if (primaryEmail && primaryEmail !== '-') {
            $('#modalEmailLink').attr('href', 'mailto:' + primaryEmail);
        } else {
            $('#modalEmailLink').removeAttr('href');
        }

        $('#modalEmergencyNo').text(user.emergency_contact_no || user.father_mobile || '-');
        $('#modalPresentAddress').text(user.address || '-');
        $('#modalPermAddress').text(user.permanent_address || user.address || '-');
        $('#modalPincode').text(user.pincode || '-');

        // Bank
        $('#modalBankName').text(user.bank_name || '-');
        $('#modalAccountNo').text(user.account_no || '-');
        $('#modalIfscCode').text(user.ifsc_code || '-');
        $('#modalBankBranch').text(user.bank_branch || '-');
        $('#modalEpfNo').text(user.epf_no || '-');
        $('#modalEsiNo').text(user.esi_no || '-');

        // Documents
        var docBase = '{{ env('IMAGE_SHOW_PATH') }}profile/';
        var docHtml = '';
        if (user.id_proof_doc) {
            docHtml += '<a href="' + docBase + user.id_proof_doc + '" target="_blank" class="btn btn-xs btn-outline-primary mr-1 mb-1 font-weight-bold"><i class="fa fa-id-card-o mr-1"></i> ID Proof</a>';
        }
        if (user.qualification_doc) {
            docHtml += '<a href="' + docBase + user.qualification_doc + '" target="_blank" class="btn btn-xs btn-outline-success mr-1 mb-1 font-weight-bold"><i class="fa fa-graduation-cap mr-1"></i> Qualification</a>';
        }
        if (user.resume_doc) {
            docHtml += '<a href="' + docBase + user.resume_doc + '" target="_blank" class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold"><i class="fa fa-file-pdf-o mr-1"></i> Resume / CV</a>';
        }
        if (user.other_doc) {
            docHtml += '<a href="' + docBase + user.other_doc + '" target="_blank" class="btn btn-xs btn-outline-secondary mr-1 mb-1 font-weight-bold"><i class="fa fa-paperclip mr-1"></i> Other Doc</a>';
        }
        if (!docHtml) {
            docHtml = '<span class="text-muted font-italic" style="font-size: 11px;"><i class="fa fa-info-circle mr-1"></i> No attached documents found.</span>';
        }
        $('#modalDocButtons').html(docHtml);

        // Edit links
        var editUrl = user.id ? ('{{ url('editUser') }}/' + user.id) : '#';
        $('#modalEditBtn').attr('href', editUrl);
        $('#modalSidebarEditBtn').attr('href', editUrl);

        $('#userDetailModal').modal('show');
    });

    // 12. Universal Modal Close / Dismiss Handler
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .btn-close-modern, .btn-modern-cancel', function() {
        var $targetModal = $(this).closest('.modal');
        if ($targetModal.length) {
            $targetModal.modal('hide');
        }
    });

    // 13. Browser Navigation (Back / Forward State)
    window.addEventListener('popstate', function() {
        loadUsers(false);
    });
});

function printUserProfileModal() {
    var modalHtml = document.getElementById('userDetailModal').querySelector('.modal-body').innerHTML;
    var staffTitle = document.getElementById('modalStaffName').innerText || 'Staff Profile';
    var printWindow = window.open('', '', 'height=800,width=1000');
    printWindow.document.write('<html><head><title>' + staffTitle + '</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">');
    printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">');
    printWindow.document.write('<style>body{font-family: Arial, sans-serif; font-size: 11px; padding: 20px;} .staff-section-card{border: 1px solid #ccc; margin-bottom: 12px; border-radius: 4px;} .staff-section-card-header{background: #f1f5f9; padding: 6px 10px; font-weight: bold; border-bottom: 1px solid #ccc;} .staff-section-card-body{padding: 8px 10px;} .staff-field-label{font-size: 9.5px; color: #666; display: block;} .staff-field-value{font-weight: bold;} #modalSidebarEditBtn{display:none;} .btn{display:none;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h4 style="color:#217346; margin-bottom: 12px; border-bottom: 2px solid #217346; padding-bottom: 4px;">' + staffTitle + '</h4>');
    printWindow.document.write(modalHtml);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function printUserTable() {
    var printContents = document.getElementById('userList').outerHTML;
    var printWindow = window.open('', '', 'height=700,width=1000');
    printWindow.document.write('<html><head><title>Staff & User Directory</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">');
    printWindow.document.write('<style>body{font-family: Arial, sans-serif; font-size: 11px; padding: 15px;} table{width:100%; border-collapse: collapse;} th, td{border: 1px solid #ccc; padding: 4px 6px;} th{background-color: #217346; color: white;} .fixed_item, .admission-filter-row{display:none;} .btn-row-action{display:none;}</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<h4 style="color:#217346; margin-bottom: 10px;">Staff & User Directory</h4>');
    printWindow.document.write(printContents);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 500);
}
</script>

@endsection