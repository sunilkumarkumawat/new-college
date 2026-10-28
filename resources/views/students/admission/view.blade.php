@php
$getAdmissionDatatableFields = Helper::getAdmissionDatatableFields();
$classType = Helper::classType($search['course'] ?? null);
$allClassType = Helper::classType();
$getState = Helper::getState();
$getcitie = Helper::getCity(); 
$getgenders = Helper::getgender();
$getCountry = Helper::getCountry();
$bloodGroupType = Helper::bloodGroupType();
$gender = DB::table('gender')->whereNull('deleted_at')->pluck('name')->implode(',');
$villageList = DB::table('custom_villages_list')->whereNull('deleted_at')->pluck('name')->implode(',');
$class = DB::table('class_types')->where('branch_id',Session('branch_id'))->where('session_id',Session('session_id'))->whereNull('deleted_at')->pluck('name')->implode(',');

$setting = Db::table('settings')->whereNull('deleted_at')->first();
$stateList = DB::table('states')->where('id', 13)->pluck('name')->implode(',');
$cityList = DB::table('citys')->whereNull('deleted_at')->where('state_id', 13)->take(25)->pluck('name')->implode(',');
$bloodgroupList = DB::table('blood_groups')->whereNull('deleted_at')->pluck('name')->implode(',');
$getSession = Helper::getSession();
$courses = Helper::getCourses();
$batches = Helper::getBatch();

$table = DB::table('datatable_fields')->first();
$dataTable = !empty($table->fields) ? explode(',', $table->fields) : [];

$totalCount = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->total() : count($data);
$firstItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->firstItem() ?? 0) : 1;
$lastItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->lastItem() ?? 0) : count($data);
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   ADMISSION MASTER - STREAMLINED EXCEL GREEN THEME
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
.btn-action-print { background: #f0f9ff !important; border-color: #bae6fd !important; color: #0369a1 !important; }
.btn-action-print:hover { background: #0284c7 !important; color: #ffffff !important; }
.btn-action-edit { background: #eff6ff !important; border-color: #bfdbfe !important; color: #2563eb !important; }
.btn-action-edit:hover { background: #2563eb !important; color: #ffffff !important; }
.btn-action-fees { background: #fffbeb !important; border-color: #fde68a !important; color: #d97706 !important; }
.btn-action-fees:hover { background: #d97706 !important; color: #ffffff !important; }
.btn-action-delete { background: #fff1f2 !important; border-color: #fecdd3 !important; color: #e11d48 !important; }
.btn-action-delete:hover { background: #e11d48 !important; color: #ffffff !important; }
.btn-action-verify {
    background: #fef2f2 !important;
    border: 1px solid #f87171 !important;
    color: #dc2626 !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    padding: 2px 7px !important;
    height: 23px !important;
    border-radius: 3px !important;
}
.btn-action-verify:hover { background: #dc2626 !important; color: #ffffff !important; }

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
}

.admission-table thead tr:first-child th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11.5px;
    white-space: nowrap;
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
    padding: 0 !important;
    vertical-align: middle !important;
    background: #ffffff !important;
    text-align: left !important;
    position: relative !important;
}
.empty-state-sticky-container {
    position: sticky;
    left: 0;
    width: calc(100vw - 280px);
    max-width: 100%;
    min-height: 360px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 30px 15px;
    box-sizing: border-box;
    margin: 0;
}
@media (max-width: 991px) {
    .empty-state-sticky-container {
        width: 100vw !important;
    }
}
.empty-state-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    max-width: 460px;
    background: transparent;
    margin: 0 auto;
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
.admission-pagination .pagination { margin: 0; }
.admission-pagination .page-item .page-link {
    font-size: 11px;
    padding: 3px 8px;
    color: #1e293b;
    border: 1px solid var(--line-subtle);
}
.admission-pagination .page-item.active .page-link {
    background-color: var(--green-excel) !important;
    border-color: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
}

/* Modals */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 3px !important;
    overflow: hidden;
    background: #ffffff !important;
}
.modal-header {
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    color: #1e2d39 !important;
    border-bottom: 1px solid #cbd5e1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}
.modal-title {
    margin: 0 !important;
    color: #1e2d39 !important;
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
    color: #475569 !important;
    cursor: pointer !important;
    padding: 0 4px !important;
    line-height: 1;
}
.modal-body { padding: 14px 16px !important; background: #ffffff !important; }
.modal-footer {
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.btn-modal-cancel {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    padding: 3px 12px !important;
}
.btn-modal-submit {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    padding: 3px 14px !important;
}

/* Column Configuration Modal */
.column-card {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 3px !important;
    padding: 5px 8px !important;
    display: flex !important;
    align-items: center !important;
    gap: 7px !important;
    cursor: pointer !important;
    margin-bottom: 0 !important;
    width: 100% !important;
}
.column-card:hover { border-color: var(--green-excel) !important; background: #f0fdf4 !important; }
.column-card.active-selected { border-color: var(--green-excel) !important; background: #ecfdf5 !important; }
.column-chk { accent-color: var(--green-excel) !important; width: 14px !important; height: 14px !important; margin: 0 !important; }
.column-label { font-size: 11.5px !important; font-weight: 600 !important; color: #1e293b !important; margin: 0 !important; }

/* Modal Search & WhatsApp styles */
.modal-search-group {
    width: 280px;
    display: flex !important;
    align-items: center !important;
    background: #ffffff !important;
    border: 1px solid #a7d0b8 !important;
    border-radius: 3px !important;
}
.modal-search-icon { padding: 4px 7px; color: var(--green-excel); font-size: 11px; }
.modal-search-input { border: none !important; height: 26px !important; padding: 2px 6px !important; font-size: 11.5px !important; width: 100%; outline: none; }
.tag-badge {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    font-size: 10.5px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 3px;
    cursor: pointer;
}
.tag-badge:hover { background: #e2f0d9; color: var(--green-excel); }
.recipients-scroll, .history-scroll { max-height: 200px; overflow-y: auto; border: 1px solid #cbd5e1; background: #ffffff; }
.wa-table { margin: 0 !important; font-size: 11px; }
.badge-status-pending { background-color: #fef3c7; color: #92400e; font-size: 10px; }
.badge-status-sending { background-color: #e0f2fe; color: #0369a1; font-size: 10px; }
.badge-status-sent { background-color: #dcfce7; color: #15803d; font-size: 10px; font-weight: 700; }
.badge-status-failed { background-color: #fee2e2; color: #b91c1c; font-size: 10px; }
.badge-status-missing { background-color: #f1f5f9; color: #64748b; font-size: 10px; }
</style>

<div class="content-wrapper admission-ledger-page">
  <section class="content pt-2 pb-3">
    <div class="container-fluid px-2">
      <div class="card admission-card">
        
        <!-- 1. Executive Titlebar -->
        <div class="admission-titlebar">
          <h1 class="admission-title">
            <i class="fa fa-users text-warning"></i> 
            <span>{{ __('student.View Students Admission') }}</span>
            <small id="totalStudentsBadge">({{ $totalCount }} Students)</small>
          </h1>
          <div class="title-actions">
            <button type="button" class="btn-header-action" id="btn-whatsapp" title="Send WhatsApp Message">
              <i class="fa fa-whatsapp text-success mr-1"></i> WhatsApp
            </button>
            <button type="button" class="btn-header-action" id="btn-checkall" data-status="0" title="Select / Deselect All">
              <i class="fa fa-square-o mr-1" id="check_box_icon"></i> Select All
            </button>
            <button type="button" class="btn-header-action" data-toggle="modal" data-target="#datatableFieldsModal" title="Configure visible columns">
              <i class="fa fa-columns text-primary mr-1"></i> Columns
            </button>
            <a href="{{ url('admissionAdd') }}" class="btn-header-action btn-header-add" title="Add New Admission">
              <i class="fa fa-plus text-success mr-1"></i> {{ __('common.Add') }}
            </a>
            <a href="{{ url('studentsDashboard') }}" class="btn-header-action" title="Back to Dashboard">
              <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
            </a>
          </div>
        </div>

        <!-- 2. Main Filter Form & Table -->
        <form id="admissionFilterForm" action="{{ url('admissionView') }}" method="get" class="w-100">
          <input type="hidden" name="page" id="admissionCurrentPage" value="{{ ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->currentPage() : 1 }}">

          <!-- Table Container with Sticky Thead Filter Row -->
          <div class="admission-scroll">
            <div id="admissionTableLoader" class="admission-loader-bar"></div>
            
            <table id="studentList" class="admission-table">
              <thead>
                <!-- 1st Header Row (Column Titles) -->
                <tr>
                  <th style="width: 36px;" class="text-center">#</th>
                  <th style="width: 50px;" class="text-center">{{ __('common.SR.NO') }}</th>
                  <th style="width: 50px;" class="text-center">{{ __('AD.NO') }}</th>
                  @if(!empty($dataTable))
                    @foreach($dataTable as $val)
                      @if($val == 'Session' || $val == 'Class') @continue @endif
                      <th class="text-center {{ $val == 'Mobile' && Session::get('role_id') != 1 ? 'd-none' : '' }}">{{ $val }}</th>
                    @endforeach
                  @endif
                  <th class="fixed_item text-center" style="width: 130px;">{{ __('common.Action') }}</th>
                </tr>

                <!-- 2nd Header Row: Exact 1-to-1 Respective Filter Row -->
                <tr class="admission-filter-row">
                  <!-- Under # -->
                  <th class="text-center" style="width: 36px;">
                    <button type="button" id="admissionResetBtn" class="btn-row-action" title="Reset All Filters" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b; margin:0 auto;">
                      <i class="fa fa-refresh"></i>
                    </button>
                  </th>

                  <!-- Under SR.NO -->
                  <th class="text-center" style="width: 50px;">
                    <input type="text" class="form-control form-control-sm live-adm-input text-center" name="admission_id" value="{{ $search['admission_id'] ?? '' }}" placeholder="ID">
                  </th>
                  <th class="text-center" style="width: 50px;">
                    <input type="text" class="form-control form-control-sm live-adm-input text-center" name="admissionNo" value="{{ $search['admissionNo'] ?? '' }}" placeholder="AD. NO.">
                  </th>

                  <!-- Under each Dynamic Column Header -->
                  @if(!empty($dataTable))
                    @foreach($dataTable as $val)
                      @if($val == 'Session' || $val == 'Class') @continue @endif
                      @if($val == 'Name')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="name" value="{{ $search['name'] ?? '' }}" placeholder="Name..." style="min-width: 105px;"></th>
                      @elseif($val == 'Course')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" id="course" name="course" style="min-width: 95px;">
                            <option value="">All</option>
                            @if(!empty($courses))
                              @foreach($courses as $c)
                                <option value="{{ $c->name ?? '' }}" {{ (($c->name ?? '') == ($search['course'] ?? '')) ? 'selected' : '' }}>{{ $c->name ?? '' }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @elseif($val == 'Batch')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" id="batch" name="batch" style="min-width: 85px;">
                            <option value="">All</option>
                            @if(!empty($batches))
                              @foreach($batches as $b)
                                <option value="{{ $b->name ?? '' }}" {{ (($b->name ?? '') == ($search['batch'] ?? '')) ? 'selected' : '' }}>{{ $b->name ?? '' }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @elseif($val == 'F Name')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="father_name" value="{{ $search['father_name'] ?? '' }}" placeholder="F Name..." style="min-width: 100px;"></th>
                      @elseif($val == 'M Name')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="mother_name" value="{{ $search['mother_name'] ?? '' }}" placeholder="M Name..." style="min-width: 95px;"></th>
                      @elseif($val == 'Mobile')
                        <th class="{{ Session::get('role_id') != 1 ? 'd-none' : '' }}">
                          @if(Session::get('role_id') == 1)
                            <input type="text" class="form-control form-control-sm live-adm-input" name="mobile" value="{{ $search['mobile'] ?? '' }}" placeholder="Mobile..." style="min-width: 90px;">
                          @endif
                        </th>
                      @elseif($val == 'Aadhar')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="aadhaar" value="{{ $search['aadhaar'] ?? '' }}" placeholder="Aadhar..." style="min-width: 95px;"></th>
                      @elseif($val == 'Student Id' || $val == 'Student.Ad. No')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="admissionNo" value="{{ $search['admissionNo'] ?? '' }}" placeholder="Adm No." style="min-width: 80px;"></th>
                      @elseif($val == 'Gender')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" name="gender_id" style="min-width: 75px;">
                            <option value="">All</option>
                            @if(!empty($getgenders))
                              @foreach($getgenders as $g)
                                <option value="{{ $g->id }}" {{ ($g->id == ($search['gender_id'] ?? '')) ? 'selected' : '' }}>{{ $g->name ?? '' }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @elseif($val == 'State')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" name="state_id" style="min-width: 85px;">
                            <option value="">All</option>
                            @if(!empty($getState))
                              @foreach($getState as $st)
                                <option value="{{ $st->id }}" {{ ($st->id == ($search['state_id'] ?? '')) ? 'selected' : '' }}>{{ $st->name ?? '' }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @elseif($val == 'City')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" name="city_id" style="min-width: 85px;">
                            <option value="">All</option>
                            @if(!empty($getcitie))
                              @foreach($getcitie as $ct)
                                <option value="{{ $ct->id }}" {{ ($ct->id == ($search['city_id'] ?? '')) ? 'selected' : '' }}>{{ $ct->name ?? '' }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @elseif($val == 'Blood Group')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" name="blood_group" style="min-width: 75px;">
                            <option value="">All</option>
                            @if(!empty($bloodGroupType))
                              @foreach($bloodGroupType as $bg)
                                <option value="{{ $bg->id }}" {{ ($bg->id == ($search['blood_group'] ?? '')) ? 'selected' : '' }}>{{ $bg->name ?? '' }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @elseif($val == 'Category')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="category" value="{{ $search['category'] ?? '' }}" placeholder="Category..." style="min-width: 80px;"></th>
                      @elseif($val == 'Admission Type')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" name="admission_type_id" style="min-width: 80px;">
                            <option value="">All</option>
                            <option value="1" {{ ($search['admission_type_id'] ?? '') == '1' ? 'selected' : '' }}>Non RTE</option>
                            <option value="2" {{ ($search['admission_type_id'] ?? '') == '2' ? 'selected' : '' }}>RTE</option>
                          </select>
                        </th>
                      @elseif($val == 'D.O.B.')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="dob" value="{{ $search['dob'] ?? '' }}" placeholder="DOB..." style="min-width: 85px;"></th>
                      @elseif($val == 'Ad. Date')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="admission_date" value="{{ $search['admission_date'] ?? '' }}" placeholder="Date..." style="min-width: 85px;"></th>
                      @elseif($val == 'Student Type')
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="student_type" value="{{ $search['student_type'] ?? '' }}" placeholder="Type..." style="min-width: 75px;"></th>
                      @elseif($val == 'Image')
                        <th></th>
                      @elseif($val == 'Agent')
                        <th>
                          <select class="form-control form-control-sm live-adm-select" id="agent_id" name="agent_id" style="min-width: 95px;">
                            <option value="">All</option>
                            @php
                              $activeAgents = \App\Models\Agent::getActiveAgents();
                            @endphp
                            @if(!empty($activeAgents))
                              @foreach($activeAgents as $ag)
                                <option value="{{ $ag->id }}" {{ ($ag->id == ($search['agent_id'] ?? '')) ? 'selected' : '' }}>{{ $ag->name }}</option>
                              @endforeach
                            @endif
                          </select>
                        </th>
                      @else
                        @php $fKey = $getAdmissionDatatableFields[$val] ?? ''; @endphp
                        <th><input type="text" class="form-control form-control-sm live-adm-input" name="{{ $fKey }}" value="{{ $search[$fKey] ?? '' }}" placeholder="{{ $val }}..." style="min-width: 80px;"></th>
                      @endif
                    @endforeach
                  @endif

                  <!-- Under Action -->
                  <th class="fixed_item text-center" style="width: 130px;">
                    <select class="form-control form-control-sm live-adm-select" id="status" name="status" style="width: 100%; height: 25px; font-size: 11px;" title="Filter by Status">
                      <option value="all" {{ ($search['status'] ?? '') === 'all' ? 'selected' : '' }}>All Status</option>
                      <option value="1" {{ ($search['status'] ?? '1') == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ ($search['status'] ?? '') === '0' ? 'selected' : '' }}>Discontinue</option>
                    </select>
                  </th>
                </tr>
              </thead>

              <!-- Dynamic Rows Container -->
              <tbody id="product_list_show">
                @include('students.admission.table_rows', [
                  'data' => $data,
                  'dataTable' => $dataTable,
                  'getAdmissionDatatableFields' => $getAdmissionDatatableFields
                ])
              </tbody>
            </table>
          </div>

          <!-- 3. Pagination & Per-Page Footer -->
          <div class="admission-pagination-footer">
            <div class="d-flex align-items-center gap-2">
              <span id="admissionEntriesInfo" class="small text-muted font-weight-bold">
                Showing {{ $firstItem }} to {{ $lastItem }} of {{ $totalCount }} students
              </span>
              <div class="d-flex align-items-center ml-3">
                <label class="small text-muted mb-0 mr-1">Show</label>
                <select id="admissionPerPage" name="per_page" class="form-control form-control-sm live-adm-select" style="width: 70px; height: 28px; padding: 2px 6px;">
                  <option value="25" {{ ($search['per_page'] ?? '25') == '25' ? 'selected' : '' }}>25</option>
                  <option value="50" {{ ($search['per_page'] ?? '') == '50' ? 'selected' : '' }}>50</option>
                  <option value="100" {{ ($search['per_page'] ?? '') == '100' ? 'selected' : '' }}>100</option>
                  <option value="250" {{ ($search['per_page'] ?? '') == '250' ? 'selected' : '' }}>250</option>
                  <option value="all" {{ ($search['per_page'] ?? '') == 'all' ? 'selected' : '' }}>All</option>
                </select>
                <label class="small text-muted mb-0 ml-1">per page</label>
              </div>
            </div>
            
            <div id="admissionPaginationContainer">
              @include('students.admission.pagination', ['data' => $data])
            </div>
          </div>

        </form>
      </div>
    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- 1️⃣ VERIFY ADMISSION MODAL -->
<!-- ============================================================== -->
<!-- 1️⃣ VERIFY ADMISSION MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="verify_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-check-circle text-success mr-1"></i> Verify Admission
        </h5>
        <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      
      <form action="{{ url('verify_admission') }}" method="post">
        @csrf
        <div class="modal-body p-3">
          <div class="d-flex align-items-start mb-3">
            <div class="mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="fa fa-user-plus text-success" style="font-size: 19px;"></i>
            </div>
            <div>
              <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Confirm Student Admission</strong>
              <span class="text-muted" style="font-size: 12px;">Move this registration into confirmed students admission list.</span>
            </div>
          </div>

          <input type="hidden" name="id" id="verify_id">
          <div class="form-group mb-0">
            <label class="small font-weight-bold text-dark mb-1">Target Academic Session <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm" name="session_id" id="verifySessionId" required>
              <option value="">Select Session</option>
              @if(!empty($getSession))
                @foreach($getSession as $item)
                  <option value="{{ $item->id }}">{{ $item->from_year ?? '' }} - {{ $item->to_year ?? '' }}</option>
                @endforeach
              @endif
            </select>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
          <button type="submit" class="btn-modal-submit"><i class="fa fa-check mr-1"></i> Confirm & Move</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 2️⃣ DELETE CONFIRMATION MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger">
          <i class="fa fa-trash-o mr-1"></i> {{ __('common.Delete Confirmation') }}
        </h5>
        <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <form action="{{ url('admissionDelete') }}" method="post">
        @csrf
        <div class="modal-body p-3">
          <input type="hidden" id="delete_id" name="delete_id">
          <p class="mb-0 text-dark font-weight-normal" style="font-size: 13px;">
            {{ __('common.Are you sure you want to delete') }}? This student record will be archived.
          </p>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
          <button type="submit" class="btn btn-danger btn-sm px-3" style="font-size: 12px; font-weight: 600;">
            <i class="fa fa-trash mr-1"></i> {{ __('common.Delete') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 3️⃣ PROFILE IMAGE PREVIEW MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="profileImgModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" style="font-size: 13.5px;"><i class="fa fa-user-circle mr-1"></i> Student Photograph</h5>
        <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="modal-body text-center p-3">
        <img id="profileImg" src="" style="width: 100%; max-height: 380px; object-fit: contain; border-radius: 4px; border: 1px solid #cbd5e1;">
      </div>
      <div class="modal-footer py-2">
        <a id="downloadLink" class="btn btn-primary btn-sm" href="" download><i class="fa fa-download mr-1"></i> Download</a>
        <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 4️⃣ DELETE IMAGE MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="deleteImageModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger"><i class="fa fa-image mr-1"></i> Remove Photograph</h5>
        <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <form action="{{ url('deleteStudentImage') }}" method="post">
        @csrf
        <div class="modal-body p-3">
          <input type="hidden" id="delete_image" name="id">
          <p class="mb-0 text-dark" style="font-size: 13px;">Are you sure you want to delete this student photo?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger btn-sm px-3">Delete Photo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 5️⃣ DATATABLE COLUMNS CONFIGURATION MODAL -->
<!-- ============================================================== -->
<div id="datatableFieldsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <h5 class="modal-title">
            <i class="fa fa-columns text-success mr-1"></i> Visible Columns Setup
          </h5>
          <label class="mb-0 ml-3 small pointer font-weight-bold text-dark d-flex align-items-center" style="cursor: pointer;">
            <input type="checkbox" class="mr-1" id="master_checkbox" style="width: 15px; height: 15px; accent-color: #217346; cursor: pointer;"> Select All Columns
          </label>
        </div>
        <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <!-- Real-time Column Search Filter Toolbar -->
      <div class="px-3 py-2 bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="modal-search-group">
          <span class="modal-search-icon"><i class="fa fa-search"></i></span>
          <input type="text" id="column_filter_search" class="modal-search-input" placeholder="Type column name to filter..." autocomplete="off">
          <span id="clear_col_search_btn" class="modal-search-clear" style="display: none;" title="Clear filter">
            <i class="fa fa-times"></i>
          </span>
        </div>
        <div id="column_match_count_wrap">
          <span class="column-match-badge" id="column_match_count">Showing all columns</span>
        </div>
      </div>

      <form action="{{ url('saveAdmissionDatatableFields') }}" method="post">
        @csrf
        <div class="modal-body p-3" style="max-height: 60vh; overflow-y: auto; background: #f8fafc;">
          <div class="row g-2" id="column_cards_container">
            @if(!empty($getAdmissionDatatableFields))
              @foreach($getAdmissionDatatableFields as $key => $dataFields)
                @if($key != 'SR.NO' && $key != 'Session' && $key != 'Class')
                  @php $isChecked = in_array($key, $dataTable); @endphp
                  <div class="col-md-3 col-sm-4 col-6 mb-2 column-card-col">
                    <label class="column-card {{ $isChecked ? 'active-selected' : '' }}" for="field_{{ $dataFields ?? '' }}">
                      <input type="checkbox" class="column-chk" id="field_{{ $dataFields ?? '' }}" name="fields[]" {{ $isChecked ? 'checked' : '' }} value="{{ $key ?? '' }}">
                      <span class="column-label">{{ $key ?? '' }}</span>
                    </label>
                  </div>
                @endif
              @endforeach
            @endif
          </div>

          <div id="no_columns_matched" class="no-matched-box" style="display: none;">
            <div class="no-matched-icon-wrap">
              <i class="fa fa-search"></i>
            </div>
            <div class="no-matched-title">No Matching Columns Found</div>
            <p class="no-matched-subtitle">Try searching with another keyword or reset filter.</p>
            <button type="button" class="btn-reset-search" id="btn_reset_col_search">
              <i class="fa fa-refresh mr-1"></i> Reset Search Filter
            </button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">{{ __('common.Close') }}</button>
          <button type="submit" class="btn-modal-submit"><i class="fa fa-save mr-1"></i> Save Layout</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 6️⃣ WHATSAPP BULK MESSAGING MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="myLargeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <h5 class="modal-title">
            <i class="fa fa-whatsapp text-success mr-2" style="font-size: 16px;"></i> Bulk WhatsApp Broadcast
          </h5>
          <span class="badge border text-dark ml-3 px-2 py-1" id="wa_total_badge" style="background: #e2f0d9; color: #166534 !important; border-color: #a7d0b8 !important;">0 Selected</span>
        </div>
        <button type="button" class="close btn-close-modal" id="close_modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <!-- Live Broadcast Progress Bar (shows during/after send) -->
      <div id="wa_progress_container" class="px-3 py-2 border-bottom" style="display: none; background: #f0fdf4; border-color: #d1e7dd !important;">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="small font-weight-bold text-dark" id="wa_progress_label">
            <i class="fa fa-paper-plane text-success mr-1"></i> Broadcasting in progress...
          </span>
          <span class="small font-weight-bold text-success" id="wa_progress_percent">0%</span>
        </div>
        <div class="progress mb-1" style="height: 7px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="wa_progress_bar" role="progressbar" style="width: 0%;"></div>
        </div>
        <div class="d-flex align-items-center gap-3 small text-muted font-weight-bold mt-1">
          <span>Total: <b class="text-dark" id="wa_stat_total">0</b></span>
          <span>Sent: <b class="text-success" id="wa_stat_sent">0</b></span>
          <span>Failed: <b class="text-danger" id="wa_stat_failed">0</b></span>
          <span>Pending: <b class="text-warning" id="wa_stat_pending">0</b></span>
        </div>
      </div>

      <div class="modal-body p-3" style="background: #f8fafc;">
        <div class="row g-3">
          <!-- Left Column: Compose Message -->
          <div class="col-md-7 border-right pr-md-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="small font-weight-bold text-dark mb-0">Compose WhatsApp Message *</label>
              <span class="small text-muted font-weight-bold" id="wa_char_counter">0 chars</span>
            </div>

            <!-- Variable Tags -->
            <div class="d-flex align-items-center flex-wrap gap-1 mb-2">
              <span class="small text-muted mr-1 font-weight-bold">Insert:</span>
              <span class="tag-badge insert-tag" data-tag="{#name#}" title="Insert Student Name">Name</span>
              <span class="tag-badge insert-tag" data-tag="{#user_name#}" title="Insert Username">Username</span>
              <span class="tag-badge insert-tag" data-tag="{#password#}" title="Insert Password">Password</span>
              <span class="tag-badge insert-tag" data-tag="{#email#}" title="Insert Email">Email</span>
              <span class="tag-badge insert-tag" data-tag="{#school_name#}" title="Insert College Name">College</span>
            </div>

            <div class="form-group mb-2">
              <textarea class="form-control" id="message-text" rows="5" placeholder="Type message here... Click tags above to insert dynamic student details." style="font-size: 12px; resize: vertical;"></textarea>
            </div>

            <!-- Attachment Section -->
            <div class="form-group mb-2">
              <label class="small font-weight-bold text-dark mb-1">Attachment (Optional Image):</label>
              <div class="img-upload-box">
                <input type="file" class="form-control-file" id="attachment-file" accept="image/*" style="cursor: pointer;">
                <div class="mt-2 text-center" id="img_preview_wrap" style="display: none;">
                  <img id="img_preview" src="" style="max-height: 100px; border-radius: 4px; border: 1px solid #cbd5e1;" class="shadow-sm">
                  <button type="button" class="btn btn-xs btn-outline-danger ml-2" id="btn_remove_img" title="Remove image">
                    <i class="fa fa-trash mr-1"></i> Remove
                  </button>
                </div>
              </div>
            </div>

            <!-- Broadcast Controls -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-2 border-top">
              <div class="d-flex align-items-center gap-2">
                <button type="button" id="sendButton" class="btn btn-success btn-sm font-weight-bold px-3 shadow-sm">
                  <i class="fa fa-paper-plane mr-1"></i> Start Broadcast
                </button>
                <button type="button" id="btn_stop_broadcast" class="btn btn-outline-danger btn-sm font-weight-bold px-2" style="display: none;">
                  <i class="fa fa-stop-circle mr-1"></i> Stop
                </button>
              </div>
              <div class="form-check previousIds mb-0" style="display: none;">
                <input type="checkbox" class="form-check-input" id="previousIds" style="margin-top: 2px;">
                <label class="form-check-label small text-muted font-weight-bold" for="previousIds">Skip already sent</label>
              </div>
            </div>
          </div>

          <!-- Right Column: Selected Recipients Queue -->
          <div class="col-md-5 pl-md-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="small font-weight-bold text-dark mb-0">Recipients Queue</label>
              <input type="text" id="search_queue_input" class="form-control form-control-sm" placeholder="Filter queue..." style="height: 24px; font-size: 10.5px; width: 130px; padding: 1px 6px;">
            </div>

            <div class="recipients-scroll shadow-sm">
              <table class="table table-sm wa-table table-hover">
                <thead>
                  <tr>
                    <th style="width: 25px;" class="text-center">#</th>
                    <th style="width: 75px;">Adm No</th>
                    <th>Name</th>
                    <th style="width: 85px;">Mobile</th>
                    <th style="width: 75px;" class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody id="secondary_tbody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Today's Broadcast History Section -->
        <hr class="my-3">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <h6 class="font-weight-bold text-dark small mb-0">
            <i class="fa fa-history text-secondary mr-1"></i> Today's Broadcast History
          </h6>
          <button type="button" class="btn btn-xs border" id="btn_refresh_history" title="Refresh history" style="background: #ffffff; color: #166534; border-color: #cbd5e1;">
            <i class="fa fa-refresh text-success mr-1"></i> Refresh
          </button>
        </div>

        <div class="history-scroll shadow-sm">
          <table class="table table-sm wa-table table-hover">
            <thead>
              <tr>
                <th style="width: 90px;" class="text-center">Msg ID</th>
                <th>Message Snippet</th>
                <th style="width: 80px;" class="text-center">Media</th>
                <th style="width: 70px;" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="third_tbody"></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer py-2">
        <button type="button" class="btn-modal-cancel" id="btn_close_wa_modal" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
    var filterTimer, admissionRequest;
    var baseUrl = '{{ url("admissionView") }}';

    // 1. Serialize parameters for real-time AJAX filtering
    function getFilterParams(targetPage) {
        var formData = $('#admissionFilterForm').serializeArray();
        var params = new URLSearchParams();
        $.each(formData, function(_, item) {
            if ($.trim(item.value) !== '' && item.name !== '_token') {
                params.set(item.name, item.value);
            }
        });
        
        var perPage = $('#admissionPerPage').val();
        if (perPage) params.set('per_page', perPage);

        var page = targetPage || $('#admissionCurrentPage').val() || 1;
        if (page && page > 1) {
            params.set('page', page);
        }

        return params;
    }

    // 2. Real-time AJAX Data Fetch without reload
    function loadAdmissions(updateUrl, targetPage) {
        if (targetPage !== undefined) {
            $('#admissionCurrentPage').val(targetPage);
        } else {
            if (updateUrl) {
                $('#admissionCurrentPage').val(1);
            }
        }

        var params = getFilterParams(targetPage);
        var queryString = params.toString();
        var fullUrl = baseUrl + (queryString ? '?' + queryString : '');

        if (updateUrl !== false) {
            history.replaceState({ admission: true }, '', fullUrl);
        }

        if (admissionRequest) {
            admissionRequest.abort();
        }

        $('#studentList').addClass('table-loading');
        $('#admissionTableLoader').show();

        admissionRequest = $.ajax({
            url: fullUrl,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function(response) {
            if (response && response.status === 'success') {
                $('#product_list_show').html(response.html);
                $('#admissionPaginationContainer').html(response.pagination);

                var total = response.total || 0;
                var from = response.from || 0;
                var to = response.to || 0;
                
                $('#totalStudentsBadge').text('(' + total + ' Students)');
                if (total > 0) {
                    $('#admissionEntriesInfo').text('Showing ' + from + ' to ' + to + ' of ' + total + ' students');
                } else {
                    $('#admissionEntriesInfo').text('Showing 0 to 0 of 0 students');
                }
            }
        })
        .fail(function(xhr, status) {
            if (status !== 'abort') {
                console.error('Error loading admission records:', xhr);
                $('#product_list_show').html('<tr class="admission-empty-row"><td colspan="100" class="admission-empty-cell"><div class="empty-state-sticky-container"><div class="empty-state-wrap"><div class="empty-state-icon-wrap" style="background:#fef2f2; color:#dc2626; border-color:#fca5a5;"><i class="fa fa-exclamation-triangle"></i></div><div class="empty-state-title">Failed to load student data</div><p class="empty-state-subtitle">An error occurred while fetching records. Please try again.</p><button type="button" class="btn-empty-reset" onclick="$(\'#admissionResetBtn\').trigger(\'click\');"><i class="fa fa-refresh mr-1"></i> Retry / Reset</button></div></div></td></tr>');
            }
        })
        .always(function() {
            $('#studentList').removeClass('table-loading');
            $('#admissionTableLoader').hide();
            alignEmptyState();
        });
    }

    function alignEmptyState() {
        var scrollBox = $('.admission-scroll');
        if (scrollBox.length) {
            var visW = scrollBox.width();
            if (visW > 50) {
                $('.empty-state-sticky-container').css('width', visW + 'px');
            }
        }
    }

    $(window).on('resize orientationchange', function() {
        alignEmptyState();
    });

    // 3. Live Event Listeners (Debounced input & immediate select changes)
    $('.live-adm-input').on('input', function() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            loadAdmissions(true);
        }, 350);
    });

    $(document).on('change', '.live-adm-select', function() {
        // Prevent course trigger from firing twice when course updates class select
        if ($(this).attr('id') !== 'course') {
            loadAdmissions(true);
        }
    });

    // 4. AJAX Pagination Switch (Page change in real-time)
    $(document).on('click', '.admission-pagination .page-link', function(e) {
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
            loadAdmissions(true, page);
            $('.admission-scroll').scrollTop(0);
        }
    });

    // 5. Reset All Filters
    $('#admissionResetBtn').on('click', function() {
        $('#admissionFilterForm')[0].reset();
        $('#admissionFilterForm select').val('');
        $('#admissionFilterForm input[type="text"]').val('');
        $('#status').val('1');
        $('#admissionPerPage').val('25');
        $('#admissionCurrentPage').val(1);
        loadAdmissions(true);
    });

    // 6. Dynamic Course -> Class/Semester Cascade
    function updateClassDropdown(classSelect, data) {
        classSelect.empty();
        classSelect.append('<option value="">All Semesters</option>');
        if (data && data.length > 0) {
            $.each(data, function(key, val) {
                classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
            });
        }
        loadAdmissions(true);
    }

    $(document).on('change', '#course', function() {
        var courseName = $(this).val();
        var classSelect = $('#class_type_id');
        
        if (courseName) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course: courseName },
                dataType: "json",
                success: function(data) {
                    updateClassDropdown(classSelect, data);
                },
                error: function() {
                    updateClassDropdown(classSelect, []);
                }
            });
        } else {
            var defaultClasses = [];
            @if(!empty($allClassType))
                @foreach($allClassType as $type)
                    defaultClasses.push({ id: "{{ $type->id }}", name: "{{ $type->name }}" });
                @endforeach
            @endif
            updateClassDropdown(classSelect, defaultClasses);
        }
    });

    // 9. Browser Navigation (Back / Forward State)
    window.addEventListener('popstate', function() {
        loadAdmissions(false);
    });

    // 10. Delegated Action Handlers (Delete, Verify, Profile Img, Checkboxes)
    $(document).on('click', '.deleteData', function() {
        var delete_id = $(this).data('id');
        $('#delete_id').val(delete_id);
    });

    $(document).on('click', '.profileImg', function() {
        var profileImgUrl = $(this).data('img');
        if (profileImgUrl) {
            $('#profileImgModal').modal('show');
            $('#profileImg').attr('src', profileImgUrl);
            $('#downloadLink').attr('href', profileImgUrl);
        }
    });

    $(document).on('click', '.deleteImage', function() {
        var delete_image = $(this).data('id');
        $('#delete_image').val(delete_image);
    });

    $(document).on('click', '.verify_admission', function() {
        var id = $(this).data('id');
        var session_id = $(this).data('session_id');
        $('#verify_id').val(id);
        $('#verifySessionId').val(session_id);
        $('#verify_modal').modal('show');
    });

    // 11. Master Column Visibility Checkbox & Real-time Column Search
    function checkmasterColumn() {
        var visibleCards = $('.column-card-col:visible .column-chk');
        var total = visibleCards.length;
        var checked = visibleCards.filter(':checked').length;
        $('#master_checkbox').prop('checked', total > 0 && total === checked);
    }

    $(document).on('click', '#master_checkbox', function() {
        var isChecked = $(this).is(':checked');
        $('.column-card-col:visible .column-chk').prop('checked', isChecked);
        $('.column-card-col:visible .column-card').toggleClass('active-selected', isChecked);
    });

    $(document).on('change', '.column-chk', function() {
        $(this).closest('.column-card').toggleClass('active-selected', $(this).is(':checked'));
        checkmasterColumn();
    });

    // Real-time Column Filter Search in Modal
    $(document).on('input', '#column_filter_search', function() {
        var query = $.trim($(this).val()).toLowerCase();
        if (query.length > 0) {
            $('#clear_col_search_btn').show();
        } else {
            $('#clear_col_search_btn').hide();
        }

        var visibleCount = 0;
        var totalCount = 0;

        $('.column-card-col').each(function() {
            totalCount++;
            var colName = $(this).find('.column-label').text().toLowerCase();
            if (query === '' || colName.indexOf(query) !== -1) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        if (query === '') {
            $('#column_match_count').text('Showing all (' + totalCount + ') columns');
            $('#no_columns_matched').hide();
        } else {
            $('#column_match_count').text('Found ' + visibleCount + ' of ' + totalCount + ' columns');
            if (visibleCount === 0) {
                $('#no_columns_matched').show();
            } else {
                $('#no_columns_matched').hide();
            }
        }
        checkmasterColumn();
    });

    $(document).on('click', '#clear_col_search_btn, #btn_reset_col_search', function() {
        $('#column_filter_search').val('').trigger('input').focus();
    });

    $('#datatableFieldsModal').on('shown.bs.modal', function() {
        $('#column_filter_search').val('').trigger('input').focus();
    });
    checkmasterColumn();

    // 12. High-Performance Bulk WhatsApp Broadcast System
    function generateRandom8DigitNumber() {
        return Math.floor(10000000 + Math.random() * 90000000);
    }

    var messageId = '';
    var attachment2 = '';
    var waQueue = [];
    var isBroadcasting = false;
    var broadcastStats = { total: 0, sent: 0, failed: 0, pending: 0 };
    var activeWorkers = 0;
    var maxConcurrency = 2; // Optimal concurrency to prevent server/gateway choking

    // Insert Tag at cursor position
    $(document).on('click', '.insert-tag', function() {
        var tag = $(this).data('tag');
        var textarea = document.getElementById('message-text');
        if (!textarea) return;
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();
        updateWaCharCounter();
    });

    function updateWaCharCounter() {
        var len = $('#message-text').val().length;
        $('#wa_char_counter').text(len + ' chars');
    }
    $(document).on('input', '#message-text', updateWaCharCounter);

    // Image Attachment Preview Handler
    $(document).on('change', '#attachment-file', function(e) {
        var file = e.target.files[0];
        if (file) {
            var objUrl = URL.createObjectURL(file);
            $('#img_preview').attr('src', objUrl);
            $('#img_preview_wrap').show();
            attachment2 = '';
        } else {
            $('#img_preview_wrap').hide();
        }
    });

    $(document).on('click', '#btn_remove_img', function() {
        $('#attachment-file').val('');
        $('#img_preview').attr('src', '');
        $('#img_preview_wrap').hide();
        attachment2 = '';
    });

    // Queue Filter Search
    $(document).on('input', '#search_queue_input', function() {
        var q = $.trim($(this).val()).toLowerCase();
        $('#secondary_tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(q === '' || text.indexOf(q) !== -1);
        });
    });

    // Check All Students Action
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

    // Open WhatsApp Broadcast Modal
    $(document).on('click', '#btn-whatsapp', function() {
        var checked = $('.checkbox_id:checked');
        if (checked.length === 0) {
            toastr.warning('Please select at least one student from the list.');
            return;
        }

        messageId = generateRandom8DigitNumber();
        waQueue = [];
        isBroadcasting = false;
        attachment2 = '';
        $('#attachment-file').val('');
        $('#img_preview_wrap').hide();
        $('#message-text').val('');
        updateWaCharCounter();

        $('#wa_progress_container').hide();
        $('#btn_stop_broadcast').hide();
        $('#sendButton').prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Start Broadcast');

        var tbodyHtml = '';
        var validCount = 0;
        var idx = 1;

        checked.each(function() {
            var sid = $(this).val();
            var admNo = $(this).data('admission_no') || '-';
            var name = $(this).data('name') || '-';
            var mobile = $.trim($(this).data('mobile') || '');
            var fName = $(this).data('father_name') || '';

            var statusBadge = '';
            if (mobile && mobile.length >= 10) {
                statusBadge = '<span class="badge badge-status-pending" id="status_' + sid + '">Pending</span>';
                waQueue.push({ id: sid, mobile: mobile, name: name, admNo: admNo });
                validCount++;
            } else {
                statusBadge = '<span class="badge badge-status-missing" id="status_' + sid + '">No Mobile</span>';
            }

            tbodyHtml += '<tr>' +
                '<td class="text-center text-muted font-weight-bold">' + (idx++) + '</td>' +
                '<td class="font-weight-bold text-dark">' + admNo + '</td>' +
                '<td>' + name + '</td>' +
                '<td>' + (mobile || '<span class="text-danger">-</span>') + '</td>' +
                '<td class="text-center">' + statusBadge + '</td>' +
            '</tr>';
        });

        $('#secondary_tbody').html(tbodyHtml);
        $('#wa_total_badge').text(validCount + ' of ' + checked.length + ' Valid');
        $('#myLargeModal').modal('show');
        todayWhatsappMessages();
    });

    // Load Today's History
    function todayWhatsappMessages() {
        $('#third_tbody').html('<tr><td colspan="4" class="text-center py-2 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading history...</td></tr>');
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('todayWhatsappMessages') }}",
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response && response.status && response.data && response.data.length > 0) {
                    var rows = '';
                    response.data.forEach(function(item) {
                        var att = item.attachment 
                            ? '<a target="_blank" href="' + item.attachment + '" class="d-inline-block"><img src="' + item.attachment + '" style="width:28px; height:28px; object-fit:cover; border-radius:3px; border:1px solid #cbd5e1;"/></a>' 
                            : '<span class="text-muted">-</span>';
                        
                        var snippet = item.message ? (item.message.length > 60 ? item.message.substring(0, 60) + '...' : item.message) : '';
                        var fullMsg = (item.message || '').replace(/"/g, '&quot;');

                        rows += '<tr>' +
                            '<td class="text-center font-weight-bold text-muted" style="font-size:10.5px;">#' + item.message_id + '</td>' +
                            '<td class="text-dark">' + snippet + '</td>' +
                            '<td class="text-center">' + att + '</td>' +
                            '<td class="text-center"><button type="button" class="btn btn-xs btn-outline-success useMe font-weight-bold" data-msg="' + fullMsg + '" data-att="' + (item.attachment || '') + '"><i class="fa fa-copy mr-1"></i> Reuse</button></td>' +
                        '</tr>';
                    });
                    $('#third_tbody').html(rows);
                } else {
                    $('#third_tbody').html('<tr><td colspan="4" class="text-center py-2 text-muted">No broadcasts sent today yet.</td></tr>');
                }
            },
            error: function() {
                $('#third_tbody').html('<tr><td colspan="4" class="text-center py-2 text-danger">Failed to load history.</td></tr>');
            }
        });
    }

    $(document).on('click', '#btn_refresh_history', function() {
        todayWhatsappMessages();
    });

    // Reuse message from history
    $(document).on('click', '.useMe', function() {
        var msg = $(this).data('msg') || '';
        var att = $(this).data('att') || '';
        $('#message-text').val(msg);
        updateWaCharCounter();

        if (att) {
            attachment2 = att;
            $('#img_preview').attr('src', att);
            $('#img_preview_wrap').show();
        }
        toastr.info('Message copied to composer.');
    });

    // Start Broadcast Queue Runner
    $(document).on('click', '#sendButton', function() {
        var msg = $.trim($('#message-text').val());
        if (!msg) {
            toastr.error('Please enter a WhatsApp message before sending.');
            $('#message-text').focus();
            return;
        }

        if (waQueue.length === 0) {
            toastr.error('No valid recipients with mobile numbers found in queue.');
            return;
        }

        isBroadcasting = true;
        broadcastStats = {
            total: waQueue.length,
            sent: 0,
            failed: 0,
            pending: waQueue.length
        };

        $('#sendButton').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Broadcasting...');
        $('#btn_stop_broadcast').show();
        $('#wa_progress_container').slideDown(150);
        updateProgressUI();

        // Launch concurrent queue workers
        var queueIndex = 0;
        activeWorkers = 0;

        function runNext() {
            if (!isBroadcasting || queueIndex >= waQueue.length) {
                if (activeWorkers === 0) {
                    finishBroadcast();
                }
                return;
            }

            var item = waQueue[queueIndex++];
            activeWorkers++;

            $('#status_' + item.id).html('<i class="fa fa-spinner fa-spin"></i> Sending').attr('class', 'badge badge-status-sending');

            var fileInput = $('#attachment-file')[0];
            var file = fileInput ? fileInput.files[0] : null;

            var formData = new FormData();
            formData.append('message_id', messageId);
            formData.append('id', item.id);
            formData.append('message', msg);
            formData.append('modal', 'Admission');
            formData.append('mobile', item.mobile);
            formData.append('attachment2', attachment2);
            if (file) formData.append('image', file);

            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('sendWhatsapp') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 30000
            })
            .done(function(resp) {
                if (resp && resp.status) {
                    broadcastStats.sent++;
                    $('#status_' + item.id).html('<i class="fa fa-check"></i> Sent').attr('class', 'badge badge-status-sent');
                } else {
                    broadcastStats.failed++;
                    var reason = (resp && resp.message) ? resp.message : 'Failed';
                    $('#status_' + item.id).text(reason).attr('class', 'badge badge-status-failed').attr('title', reason);
                }
            })
            .fail(function(xhr) {
                broadcastStats.failed++;
                $('#status_' + item.id).text('Error').attr('class', 'badge badge-status-failed').attr('title', 'Network/Server Error');
            })
            .always(function() {
                broadcastStats.pending--;
                activeWorkers--;
                updateProgressUI();
                if (isBroadcasting) {
                    runNext();
                }
            });
        }

        // Spawn workers up to maxConcurrency
        var workersToSpawn = Math.min(maxConcurrency, waQueue.length);
        for (var w = 0; w < workersToSpawn; w++) {
            runNext();
        }
    });

    function updateProgressUI() {
        var completed = broadcastStats.sent + broadcastStats.failed;
        var total = broadcastStats.total || 1;
        var pct = Math.round((completed / total) * 100);

        $('#wa_stat_total').text(broadcastStats.total);
        $('#wa_stat_sent').text(broadcastStats.sent);
        $('#wa_stat_failed').text(broadcastStats.failed);
        $('#wa_stat_pending').text(broadcastStats.pending);

        $('#wa_progress_bar').css('width', pct + '%');
        $('#wa_progress_percent').text(pct + '%');
    }

    function finishBroadcast() {
        isBroadcasting = false;
        $('#sendButton').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Broadcast Completed');
        $('#btn_stop_broadcast').hide();
        $('#wa_progress_label').html('<i class="fa fa-check-circle text-success mr-1"></i> Broadcast Finished (' + broadcastStats.sent + ' Sent, ' + broadcastStats.failed + ' Failed)');
        toastr.success('WhatsApp broadcast completed: ' + broadcastStats.sent + ' sent successfully.');
        todayWhatsappMessages();
    }

    $(document).on('click', '#btn_stop_broadcast', function() {
        isBroadcasting = false;
        $(this).hide();
        $('#sendButton').prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Resume Broadcast');
        $('#wa_progress_label').html('<i class="fa fa-pause-circle text-warning mr-1"></i> Broadcast Paused');
        toastr.warning('Broadcast paused.');
    });

    // 13. Universal Modal Close / Dismiss Handler (Foolproof across Bootstrap 4/5)
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .btn-modal-cancel, #close_modal, #btn_close_wa_modal', function(e) {
        var $targetModal = $(this).closest('.modal');
        if ($targetModal.length) {
            $targetModal.modal('hide');
        }
    });

    $('#myLargeModal').on('hidden.bs.modal', function() {
        isBroadcasting = false;
    });
});
</script>

@endsection