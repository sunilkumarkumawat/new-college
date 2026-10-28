@extends('layout.app')
@section('content')

@php
    $totalStudents = count($data);
    $totalUsers = count($data2);
    $formattedDate = $targetDate->format('d M, Y');
    $dateInputValue = $targetDate->format('Y-m-d');
    $schoolName = Session::get('school_name') ?? 'our institution';
@endphp

<style>
/* ========================================================
   BIRTHDAY CELEBRATION - STREAMLINED EXCEL GREEN THEME
   (Exact Match to admissionView Layout & Aesthetics)
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

.title-actions {
    display: flex;
    gap: 6px;
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
    line-height: 1.5;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-header-action.btn-header-send {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-send:hover {
    background: #166534 !important;
    color: #ffffff !important;
}

/* Header Date Picker Wrap */
.header-date-picker {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 2px 7px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 25px;
}
.header-date-picker input[type="date"] {
    border: none;
    outline: none;
    font-size: 11px;
    font-weight: 600;
    color: #1e293b;
    background: transparent;
    padding: 0;
    cursor: pointer;
}
.header-date-picker .btn-today-badge {
    background: #eef6f0;
    color: var(--green-excel);
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 2px;
    border: 1px solid var(--green-excel-border);
    cursor: pointer;
    text-decoration: none;
}
.header-date-picker .btn-today-badge:hover {
    background: var(--green-excel);
    color: #ffffff;
}

/* Tab Navigation Bar */
.birthday-tabs-container {
    background: #eef6f0;
    border-bottom: 2px solid var(--green-excel-border);
    padding: 6px 10px 0 10px;
    display: flex;
    gap: 6px;
    align-items: flex-end;
}
.b-tab-btn {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-bottom: none;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    margin-bottom: -2px;
    outline: none !important;
}
.b-tab-btn:hover {
    background: #f8fafc;
    color: var(--green-excel);
}
.b-tab-btn.active {
    background: #ffffff;
    border-color: var(--green-excel-border);
    border-top: 3px solid var(--green-excel);
    color: var(--green-excel);
    font-weight: 700;
    padding-bottom: 8px;
    z-index: 2;
}
.b-tab-pill {
    font-size: 10px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 10px;
    background: #e2e8f0;
    color: #1e293b;
}
.b-tab-btn.active .b-tab-pill {
    background: var(--green-excel);
    color: #ffffff;
}
.b-tab-pending-pill {
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 3px;
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

/* Scroll Container & Table */
.admission-scroll {
    height: calc(100vh - 215px);
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

/* Row Action Buttons */
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
.btn-action-wa { background: #dcfce7 !important; border-color: #86efac !important; color: #15803d !important; }
.btn-action-wa:hover { background: #15803d !important; color: #ffffff !important; }
.btn-action-retry { background: #fee2e2 !important; border-color: #fca5a5 !important; color: #b91c1c !important; }
.btn-action-retry:hover { background: #b91c1c !important; color: #ffffff !important; }

/* Status Badges */
.badge-status-sent {
    background-color: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #86efac !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    padding: 2px 7px !important;
    border-radius: 3px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
}
.badge-status-pending {
    background-color: #fef3c7 !important;
    color: #92400e !important;
    border: 1px solid #fde68a !important;
    font-size: 10px !important;
    font-weight: 600 !important;
    padding: 2px 7px !important;
    border-radius: 3px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
}
.badge-status-failed {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1px solid #fca5a5 !important;
    font-size: 10px !important;
    font-weight: 600 !important;
    padding: 2px 7px !important;
    border-radius: 3px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
}

/* Empty State (admissionView exact match) */
.admission-table tbody tr.admission-empty-row {
    height: calc(100vh - 290px) !important;
    min-height: 360px !important;
    background: #ffffff !important;
}
.admission-table tbody tr.admission-empty-row:hover td {
    background-color: #ffffff !important;
}
.admission-table tbody tr.admission-empty-row td.admission-empty-cell {
    height: calc(100vh - 290px) !important;
    min-height: 360px !important;
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
    min-height: 340px;
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
    border: none !important;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.btn-empty-reset:hover {
    background: var(--green-excel-dark) !important;
}

/* Footer Summary Bar */
.admission-footer {
    padding: 6px 14px;
    background: #ffffff;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 11.5px;
}

/* ========================================================
   CUSTOM THEME MODALS (Exact match to admissionView modals)
   ======================================================== */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 3px !important;
    overflow: hidden;
    background: #ffffff !important;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15) !important;
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
.btn-close-modal:hover {
    color: #0f172a !important;
}
.modal-body {
    padding: 14px 16px !important;
    background: #ffffff !important;
    font-size: 12px;
}
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
    padding: 4px 14px !important;
    cursor: pointer;
}
.btn-modal-cancel:hover {
    background: #f8fafc !important;
    color: #1e293b !important;
}
.btn-modal-submit {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    padding: 4px 16px !important;
    cursor: pointer;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
}
.btn-modal-submit:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

/* Modal Search & Table styles */
.modal-search-group {
    width: 220px;
    display: flex !important;
    align-items: center !important;
    background: #ffffff !important;
    border: 1px solid #a7d0b8 !important;
    border-radius: 3px !important;
}
.modal-search-icon {
    padding: 4px 7px;
    color: var(--green-excel);
    font-size: 11px;
}
.modal-search-input {
    border: none !important;
    height: 25px !important;
    padding: 2px 6px !important;
    font-size: 11.5px !important;
    width: 100%;
    outline: none;
}
.tag-badge {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    font-size: 10.5px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 3px;
    cursor: default;
}
.tag-badge.active-tag {
    background: #e2f0d9;
    color: var(--green-excel);
    border-color: #8eb99b;
}
.recipients-scroll {
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    border-radius: 2px;
}
.wa-table {
    margin: 0 !important;
    font-size: 11px;
}
.wa-table thead th {
    position: sticky;
    top: 0;
    background: #f1f4f6 !important;
    border-bottom: 1px solid #cbd5e1 !important;
    padding: 4px 6px !important;
    font-size: 10.5px;
    z-index: 1;
}
.wa-table tbody td {
    padding: 4px 6px !important;
    vertical-align: middle;
}
</style>

<div class="content-wrapper admission-ledger-page">

  {{-- Session Notifications --}}
  @if(session('message'))
  <div class="container-fluid px-2 pt-2">
    <div class="alert alert-success alert-dismissible fade show p-2 mb-1" role="alert" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-size: 12px; border-radius: 3px;">
      <i class="fa fa-check-circle mr-1"></i> <strong>Success:</strong> {{ session('message') }}
      <button type="button" class="close p-2" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="font-size: 16px; color: #166534;">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
  @endif

  @if(session('error'))
  <div class="container-fluid px-2 pt-2">
    <div class="alert alert-danger alert-dismissible fade show p-2 mb-1" role="alert" style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; font-size: 12px; border-radius: 3px;">
      <i class="fa fa-exclamation-circle mr-1"></i> <strong>Notice:</strong> {{ session('error') }}
      <button type="button" class="close p-2" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close" style="font-size: 16px; color: #991b1b;">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  </div>
  @endif

  <section class="content pt-2 pb-3">
    <div class="container-fluid px-2">
      <div class="card admission-card">
        
        <!-- 1. Executive Titlebar -->
        <div class="admission-titlebar">
          <h1 class="admission-title">
            <i class="fa fa-birthday-cake text-warning"></i> 
            <span>Today's Birthday Celebration</span>
            <small id="totalBirthdayBadge">({{ $totalStudents }} Students, {{ $totalUsers }} Staff)</small>
          </h1>
          <div class="title-actions">
            <!-- Filter Date picker -->
            <div class="header-date-picker" title="Filter by Celebration Date">
              <i class="fa fa-calendar text-muted"></i>
              <input type="date" id="celebrationDateInput" value="{{ $dateInputValue }}">
              @if(!$isToday)
                <a href="{{ url('happy_birthday') }}" class="btn-today-badge" title="Go to Today">Today</a>
              @endif
            </div>

            <!-- Send Wishes Top Button -->
            <button type="button" class="btn-header-action btn-header-send" id="btn-send-wishes-top" title="Send Wishes to Selected">
              <i class="fa fa-paper-plane text-success mr-1"></i> Send Wishes (<span class="selectedCountText">0</span>)
            </button>

            <!-- Global Select All Button -->
            <button type="button" class="btn-header-action" id="btn-checkall" data-status="0" title="Select / Deselect All">
              <i class="fa fa-square-o mr-1" id="check_box_icon"></i> Select All
            </button>

            <!-- Back to Dashboard / Terminal -->
            <a href="{{ Session::get('role_id') !== 3 ? url('send_message_terminal') : url('studentsDashboard') }}" class="btn-header-action" title="Back">
              <i class="fa fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>

        <!-- 2. Segmented Navigation Tabs -->
        <div class="birthday-tabs-container">
          <button type="button" class="b-tab-btn active" id="tab-btn-students" data-tab="students">
            <i class="fa fa-graduation-cap"></i> Students Birthday
            <span class="b-tab-pill" id="badge-total-students">{{ $totalStudents }}</span>
            @if($studentPendingCount > 0)
              <span class="b-tab-pending-pill" id="badge-pending-students">{{ $studentPendingCount }} Pending</span>
            @endif
          </button>
          <button type="button" class="b-tab-btn" id="tab-btn-users" data-tab="users">
            <i class="fa fa-user-circle"></i> Staff / Users Birthday
            <span class="b-tab-pill" id="badge-total-users">{{ $totalUsers }}</span>
            @if($userPendingCount > 0)
              <span class="b-tab-pending-pill" id="badge-pending-users">{{ $userPendingCount }} Pending</span>
            @endif
          </button>
        </div>

        <!-- 3. Form Wrapping Both Tables -->
        <form id="birthdayWishesForm" action="{{ url('send_wishes') }}" method="post" class="w-100">
          @csrf
          <input type="hidden" name="filter_date" value="{{ $dateInputValue }}">

          <!-- ==========================================
               TAB PANE 1: STUDENTS BIRTHDAY TABLE
               ========================================== -->
          <div class="tab-pane-container" id="pane-students">
            <div class="admission-scroll">
              <table id="studentsTable" class="admission-table">
                <thead>
                  <!-- 1st Header Row (Titles) -->
                  <tr>
                    <th style="width: 38px;" class="text-center">
                      <input type="checkbox" id="select_all_student" title="Select All Pending Students" style="cursor: pointer;">
                    </th>
                    <th style="width: 55px;" class="text-center">SR.NO</th>
                    <th class="text-left" style="min-width: 140px;">Student Name</th>
                    <th class="text-left" style="min-width: 110px;">Class / Course</th>
                    <th class="text-left" style="min-width: 130px;">Father's Name</th>
                    <th class="text-center" style="min-width: 95px;">Mobile</th>
                    <th class="text-left" style="min-width: 130px;">E-Mail</th>
                    <th class="text-center" style="width: 90px;">D.O.B.</th>
                    <th class="text-center" style="width: 60px;">Age</th>
                    <th class="text-center" style="width: 100px;">Status</th>
                    <th class="fixed_item text-center" style="width: 90px;">Action</th>
                  </tr>

                  <!-- 2nd Header Row: Sticky Instant Filter Row -->
                  <tr class="admission-filter-row">
                    <th class="text-center" style="width: 38px;">
                      <button type="button" class="btn-row-action filter-reset-btn" data-target="students" title="Reset Filters" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b; margin:0 auto;">
                        <i class="fa fa-refresh"></i>
                      </button>
                    </th>
                    <th class="text-center" style="width: 55px;">
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="sr" placeholder="ID">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="name" placeholder="Search Name...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="class" placeholder="Filter Class...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="father" placeholder="Filter Father...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="mobile" placeholder="Mobile...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="email" placeholder="Email...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="dob" placeholder="DD/MM">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="age" placeholder="Age">
                    </th>
                    <th class="text-center">
                      <select class="form-control form-control-sm live-filter-select" data-col="status">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                      </select>
                    </th>
                    <th class="fixed_item"></th>
                  </tr>
                </thead>

                <tbody id="studentsTbody">
                  @if(!empty($data) && count($data) > 0)
                    @php $i = 1; @endphp
                    @foreach($data as $item)
                      @php
                        $wish = $item->wish_record ?? null;
                        $isSent = !empty($wish) && $wish->status == 0;
                        $isFailed = !empty($wish) && $wish->status == 1;
                        $isPending = empty($wish);
                        $fullName = trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? ''));
                        $courseClass = !empty($item->class_name) ? $item->class_name : ($item->course ?? '-');
                        $dobDisplay = !empty($item->dob) ? date('d/m/Y', strtotime($item->dob)) : '-';
                        $age = !empty($item->dob) ? \Carbon\Carbon::parse($item->dob)->age . ' Yrs' : '-';
                        $statusKey = $isSent ? 'sent' : ($isFailed ? 'failed' : 'pending');
                        $waMsgStudent = "🎉🎂 Happy Birthday " . $fullName . "! 🎂🎉 Wishing you a wonderful year ahead from " . $schoolName . "!";
                        $waUrlStudent = "https://api.whatsapp.com/send?phone=91" . preg_replace('/[^0-9]/', '', $item->mobile ?? '') . "&text=" . urlencode($waMsgStudent);
                      @endphp
                      <tr class="student-row"
                          data-sr="{{ $item->id }}"
                          data-name="{{ strtolower($fullName) }}"
                          data-class="{{ strtolower($courseClass) }}"
                          data-father="{{ strtolower($item->father_name ?? '') }}"
                          data-mobile="{{ $item->mobile ?? '' }}"
                          data-email="{{ strtolower($item->email ?? '') }}"
                          data-dob="{{ $dobDisplay }}"
                          data-age="{{ $age }}"
                          data-status="{{ $statusKey }}">
                        
                        <!-- Checkbox -->
                        <td class="text-center">
                          @if($isSent)
                            <i class="fa fa-check text-success" title="Wishes Already Sent"></i>
                          @else
                            <input type="checkbox" name="checkbox_student[]" class="chk-recipient chk-student count" value="{{ $item->id }}" style="cursor: pointer;">
                            <input type="hidden" name="mobile_student[{{ $item->id }}]" value="{{ $item->mobile }}">
                            <input type="hidden" name="first_name_student[{{ $item->id }}]" value="{{ $fullName }}">
                            <input type="hidden" name="role_id_student[{{ $item->id }}]" value="{{ $item->role_id ?? 3 }}">
                          @endif
                        </td>

                        <!-- SR / ID -->
                        <td class="text-center font-weight-bold text-muted">{{ $item->id }}</td>

                        <!-- Student Name -->
                        <td>
                          <span class="font-weight-bold text-dark">{{ $fullName }}</span>
                          @if(!empty($item->admissionNo))
                            <small class="text-muted d-block" style="font-size: 10px;">Adm: {{ $item->admissionNo }}</small>
                          @endif
                        </td>

                        <!-- Class / Course -->
                        <td>{{ $courseClass }}</td>

                        <!-- Father's Name -->
                        <td>{{ $item->father_name ?? '-' }}</td>

                        <!-- Mobile -->
                        <td class="text-center">
                          @if(!empty($item->mobile))
                            <span class="font-weight-bold">{{ $item->mobile }}</span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>

                        <!-- Email -->
                        <td>{{ $item->email ?? '-' }}</td>

                        <!-- DOB -->
                        <td class="text-center">{{ $dobDisplay }}</td>

                        <!-- Age -->
                        <td class="text-center">{{ $age }}</td>

                        <!-- Status Badge -->
                        <td class="text-center">
                          @if($isSent)
                            <span class="badge-status-sent"><i class="fa fa-check"></i> Sent</span>
                          @elseif($isFailed)
                            <span class="badge-status-failed" title="{{ $wish->failed_message ?? 'Failed to send' }}">
                              <i class="fa fa-exclamation-triangle"></i> Failed
                            </span>
                          @else
                            <span class="badge-status-pending"><i class="fa fa-clock-o"></i> Pending</span>
                          @endif
                        </td>

                        <!-- Action Icons -->
                        <td class="fixed_item text-center">
                          <div class="d-inline-flex gap-1 align-items-center">
                            @if(!empty($item->mobile))
                              <button type="button" class="btn-row-action btn-action-wa btn-preview-wish"
                                      data-name="{{ $fullName }}"
                                      data-role="{{ $courseClass }}"
                                      data-mobile="{{ $item->mobile }}"
                                      data-msg="{{ $waMsgStudent }}"
                                      data-wa-url="{{ $waUrlStudent }}"
                                      title="Preview & Send WhatsApp Wish">
                                <i class="fa fa-whatsapp"></i>
                              </button>
                            @endif
                            <a href="{{ url('admissionDetail', $item->id) }}" target="_blank" class="btn-row-action btn-action-view" title="View Student Profile">
                              <i class="fa fa-eye"></i>
                            </a>
                            @if($isFailed)
                              <a href="{{ url('resend_message') }}" class="btn-row-action btn-action-retry" title="Resend Failed Message">
                                <i class="fa fa-repeat"></i>
                              </a>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  @endif

                  <!-- Empty State Row (Students) -->
                  <tr class="admission-empty-row" id="studentsEmptyRow" style="{{ count($data) == 0 ? '' : 'display: none;' }}">
                    <td colspan="11" class="admission-empty-cell">
                      <div class="empty-state-sticky-container">
                        <div class="empty-state-wrap">
                          <div class="empty-state-icon-wrap">
                            <i class="fa fa-birthday-cake"></i>
                          </div>
                          <div class="empty-state-title">No Students Birthday Found</div>
                          <div class="empty-state-subtitle">No student records match for {{ $formattedDate }}.</div>
                          <button type="button" class="btn-empty-reset filter-reset-btn" data-target="students">
                            <i class="fa fa-refresh mr-1"></i> Reset Filters
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ==========================================
               TAB PANE 2: STAFF / USERS BIRTHDAY TABLE
               ========================================== -->
          <div class="tab-pane-container" id="pane-users" style="display: none;">
            <div class="admission-scroll">
              <table id="usersTable" class="admission-table">
                <thead>
                  <!-- 1st Header Row (Titles) -->
                  <tr>
                    <th style="width: 38px;" class="text-center">
                      <input type="checkbox" id="select_all_user" title="Select All Pending Staff" style="cursor: pointer;">
                    </th>
                    <th style="width: 55px;" class="text-center">SR.NO</th>
                    <th class="text-left" style="min-width: 140px;">Staff / User Name</th>
                    <th class="text-left" style="min-width: 110px;">Role / Designation</th>
                    <th class="text-left" style="min-width: 130px;">Father's Name</th>
                    <th class="text-center" style="min-width: 95px;">Mobile</th>
                    <th class="text-left" style="min-width: 130px;">E-Mail</th>
                    <th class="text-center" style="width: 90px;">D.O.B.</th>
                    <th class="text-center" style="width: 60px;">Age</th>
                    <th class="text-center" style="width: 100px;">Status</th>
                    <th class="fixed_item text-center" style="width: 90px;">Action</th>
                  </tr>

                  <!-- 2nd Header Row: Sticky Instant Filter Row -->
                  <tr class="admission-filter-row">
                    <th class="text-center" style="width: 38px;">
                      <button type="button" class="btn-row-action filter-reset-btn" data-target="users" title="Reset Filters" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b; margin:0 auto;">
                        <i class="fa fa-refresh"></i>
                      </button>
                    </th>
                    <th class="text-center" style="width: 55px;">
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="sr" placeholder="ID">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="name" placeholder="Search Name...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="role" placeholder="Filter Role...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="father" placeholder="Filter Father...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="mobile" placeholder="Mobile...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm live-filter-input" data-col="email" placeholder="Email...">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="dob" placeholder="DD/MM">
                    </th>
                    <th>
                      <input type="text" class="form-control form-control-sm text-center live-filter-input" data-col="age" placeholder="Age">
                    </th>
                    <th class="text-center">
                      <select class="form-control form-control-sm live-filter-select" data-col="status">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                      </select>
                    </th>
                    <th class="fixed_item"></th>
                  </tr>
                </thead>

                <tbody id="usersTbody">
                  @if(!empty($data2) && count($data2) > 0)
                    @php $j = 1; @endphp
                    @foreach($data2 as $item)
                      @php
                        $wish = $item->wish_record ?? null;
                        $isSent = !empty($wish) && $wish->status == 0;
                        $isFailed = !empty($wish) && $wish->status == 1;
                        $isPending = empty($wish);
                        $fullName = trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? ''));
                        $roleName = !empty($item->role_name) ? $item->role_name : 'Staff';
                        $dobDisplay = !empty($item->dob) ? date('d/m/Y', strtotime($item->dob)) : '-';
                        $age = !empty($item->dob) ? \Carbon\Carbon::parse($item->dob)->age . ' Yrs' : '-';
                        $statusKey = $isSent ? 'sent' : ($isFailed ? 'failed' : 'pending');
                        $waMsgUser = "🎉🎂 Happy Birthday " . $fullName . "! 🎂🎉 Wishing you happiness and great success ahead from " . $schoolName . "!";
                        $waUrlUser = "https://api.whatsapp.com/send?phone=91" . preg_replace('/[^0-9]/', '', $item->mobile ?? '') . "&text=" . urlencode($waMsgUser);
                      @endphp
                      <tr class="user-row"
                          data-sr="{{ $item->id }}"
                          data-name="{{ strtolower($fullName) }}"
                          data-role="{{ strtolower($roleName) }}"
                          data-father="{{ strtolower($item->father_name ?? '') }}"
                          data-mobile="{{ $item->mobile ?? '' }}"
                          data-email="{{ strtolower($item->email ?? '') }}"
                          data-dob="{{ $dobDisplay }}"
                          data-age="{{ $age }}"
                          data-status="{{ $statusKey }}">
                        
                        <!-- Checkbox -->
                        <td class="text-center">
                          @if($isSent)
                            <i class="fa fa-check text-success" title="Wishes Already Sent"></i>
                          @else
                            <input type="checkbox" name="checkbox_user[]" class="chk-recipient chk-user count" value="{{ $item->id }}" style="cursor: pointer;">
                            <input type="hidden" name="mobile_user[{{ $item->id }}]" value="{{ $item->mobile }}">
                            <input type="hidden" name="first_name_user[{{ $item->id }}]" value="{{ $fullName }}">
                            <input type="hidden" name="role_id_user[{{ $item->id }}]" value="{{ $item->role_id ?? 1 }}">
                          @endif
                        </td>

                        <!-- SR / ID -->
                        <td class="text-center font-weight-bold text-muted">{{ $item->id }}</td>

                        <!-- Staff Name -->
                        <td>
                          <span class="font-weight-bold text-dark">{{ $fullName }}</span>
                        </td>

                        <!-- Role / Designation -->
                        <td>
                          <span class="badge badge-light border text-dark">{{ $roleName }}</span>
                        </td>

                        <!-- Father's Name -->
                        <td>{{ $item->father_name ?? '-' }}</td>

                        <!-- Mobile -->
                        <td class="text-center">
                          @if(!empty($item->mobile))
                            <span class="font-weight-bold">{{ $item->mobile }}</span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>

                        <!-- Email -->
                        <td>{{ $item->email ?? '-' }}</td>

                        <!-- DOB -->
                        <td class="text-center">{{ $dobDisplay }}</td>

                        <!-- Age -->
                        <td class="text-center">{{ $age }}</td>

                        <!-- Status Badge -->
                        <td class="text-center">
                          @if($isSent)
                            <span class="badge-status-sent"><i class="fa fa-check"></i> Sent</span>
                          @elseif($isFailed)
                            <span class="badge-status-failed" title="{{ $wish->failed_message ?? 'Failed to send' }}">
                              <i class="fa fa-exclamation-triangle"></i> Failed
                            </span>
                          @else
                            <span class="badge-status-pending"><i class="fa fa-clock-o"></i> Pending</span>
                          @endif
                        </td>

                        <!-- Action Icons -->
                        <td class="fixed_item text-center">
                          <div class="d-inline-flex gap-1 align-items-center">
                            @if(!empty($item->mobile))
                              <button type="button" class="btn-row-action btn-action-wa btn-preview-wish"
                                      data-name="{{ $fullName }}"
                                      data-role="{{ $roleName }}"
                                      data-mobile="{{ $item->mobile }}"
                                      data-msg="{{ $waMsgUser }}"
                                      data-wa-url="{{ $waUrlUser }}"
                                      title="Preview & Send WhatsApp Wish">
                                <i class="fa fa-whatsapp"></i>
                              </button>
                            @endif
                            @if($isFailed)
                              <a href="{{ url('resend_message') }}" class="btn-row-action btn-action-retry" title="Resend Failed Message">
                                <i class="fa fa-repeat"></i>
                              </a>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  @endif

                  <!-- Empty State Row (Users) -->
                  <tr class="admission-empty-row" id="usersEmptyRow" style="{{ count($data2) == 0 ? '' : 'display: none;' }}">
                    <td colspan="11" class="admission-empty-cell">
                      <div class="empty-state-sticky-container">
                        <div class="empty-state-wrap">
                          <div class="empty-state-icon-wrap">
                            <i class="fa fa-birthday-cake"></i>
                          </div>
                          <div class="empty-state-title">No Staff Birthday Found</div>
                          <div class="empty-state-subtitle">No staff records match for {{ $formattedDate }}.</div>
                          <button type="button" class="btn-empty-reset filter-reset-btn" data-target="users">
                            <i class="fa fa-refresh mr-1"></i> Reset Filters
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 4. Executive Bottom Footer Summary Bar -->
          <div class="admission-footer">
            <div class="d-flex align-items-center gap-3">
              <span class="text-muted" id="footerSummary">
                Showing {{ $totalStudents }} students ({{ $studentPendingCount }} pending, {{ $studentSentCount }} sent)
              </span>
              <span class="badge badge-light border px-2 py-1" id="selectedSummary">
                <i class="fa fa-check-square-o text-success mr-1"></i> <strong class="selectedCountText">0</strong> recipients selected
              </span>
            </div>
            <div>
              <button type="button" class="btn btn-sm btn-success px-4 font-weight-bold shadow-sm" id="btn-submit-bottom">
                <i class="fa fa-paper-plane mr-1"></i> Send Birthday Wishes
              </button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </section>
</div>

<!-- ============================================================== -->
<!-- 1️⃣ CUSTOM THEME MODAL: NOTICE / ALERT (Replaces browser alert) -->
<!-- ============================================================== -->
<div class="modal fade" id="birthdayAlertModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 420px;">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title text-warning">
          <i class="fa fa-exclamation-circle text-warning mr-1"></i>
          <span id="alertModalTitle">Selection Required</span>
        </h5>
        <button type="button" class="btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="modal-body p-3">
        <div class="d-flex align-items-start gap-3">
          <div style="width: 42px; height: 42px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
            <i class="fa fa-users"></i>
          </div>
          <div>
            <h6 class="font-weight-bold text-dark mb-1" id="alertModalHeading">No Recipient Selected</h6>
            <p class="text-muted mb-0" id="alertModalMessage" style="font-size: 12px; line-height: 1.5;">
              Please select at least one student or staff member by clicking their checkbox before sending wishes.
            </p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-modal-submit" data-dismiss="modal" data-bs-dismiss="modal">
          <i class="fa fa-check mr-1"></i> Okay, Understood
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 2️⃣ CUSTOM THEME MODAL: BROADCAST CONFIRMATION & PREVIEW      -->
<!-- (Replaces browser confirm with rich UI matching admissionView) -->
<!-- ============================================================== -->
<div class="modal fade" id="confirmWishesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <div class="d-flex align-items-center">
          <h5 class="modal-title">
            <i class="fa fa-paper-plane text-success mr-2" style="font-size: 15px;"></i> Confirm Birthday Wishes Broadcast
          </h5>
          <span class="badge border ml-3 px-2 py-1" id="modalRecipientBadge" style="background: #eef6f0; color: #166534 !important; border-color: #8eb99b !important; font-size: 11px;">0 Selected</span>
        </div>
        <button type="button" class="btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <div class="modal-body p-3" style="background: #f8fafc;">
        <!-- Notification Alert Strip -->
        <div class="p-2 mb-3 rounded border d-flex align-items-center justify-content-between" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
          <div class="d-flex align-items-center">
            <i class="fa fa-whatsapp text-success mr-2" style="font-size: 16px;"></i>
            <span class="small text-dark font-weight-bold">
              Birthday greetings will be sent to all selected recipients via official WhatsApp API and Firebase notification.
            </span>
          </div>
          <span class="badge badge-light border text-muted">Celebration: {{ $formattedDate }}</span>
        </div>

        <div class="row g-3">
          <!-- Left Column: Message Template Preview -->
          <div class="col-md-6 border-right pr-md-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="small font-weight-bold text-dark mb-0">Message Template Preview</label>
              <span class="small text-muted font-weight-bold">Official Format</span>
            </div>

            <!-- Dynamic Tags Display -->
            <div class="d-flex align-items-center flex-wrap gap-1 mb-2">
              <span class="small text-muted mr-1 font-weight-bold">Auto Tags:</span>
              <span class="tag-badge active-tag" title="Replaced with recipient name">{#name#}</span>
              <span class="tag-badge active-tag" title="Replaced with college name">{#school_name#}</span>
            </div>

            <!-- Template Box -->
            <div class="p-2 rounded border bg-white shadow-sm mb-2" style="font-size: 11.5px; line-height: 1.5; color: #1e293b; border-color: #cbd5e1 !important; max-height: 200px; overflow-y: auto;">
              <p class="mb-1 font-weight-bold text-success">🎉🎂 Happy Birthday, {#name#}! 🎂🎉</p>
              <p class="mb-1 text-muted">
                Wishing you, {#name#}, a day filled with laughter, love, and all the happiness in the world! May your special day be as amazing as you are. 🌟✨
              </p>
              <p class="mb-1 text-muted">
                Cheers to another year of adventures, memories, and dreams coming true! 🥳🎈
              </p>
              <p class="mb-0 text-dark font-weight-bold">
                — Warm wishes from {{ $schoolName }}
              </p>
            </div>
            <small class="text-muted"><i class="fa fa-info-circle mr-1"></i> Recipient details will be dynamically formatted for each message.</small>
          </div>

          <!-- Right Column: Selected Recipients Queue with Live Filter -->
          <div class="col-md-6 pl-md-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="small font-weight-bold text-dark mb-0">Selected Recipients Queue</label>
              <div class="modal-search-group">
                <i class="fa fa-search modal-search-icon"></i>
                <input type="text" id="modalSearchQueue" class="modal-search-input" placeholder="Search in Queue...">
              </div>
            </div>

            <div class="recipients-scroll shadow-sm">
              <table class="table table-sm wa-table table-hover">
                <thead>
                  <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th>Name</th>
                    <th style="width: 100px;">Class / Role</th>
                    <th style="width: 90px;" class="text-center">Mobile</th>
                  </tr>
                </thead>
                <tbody id="modalQueueTbody">
                  <!-- Populated dynamically via JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div class="mr-auto small text-muted font-weight-bold">
          Ready to dispatch to <strong class="text-dark" id="modalTotalCountText">0</strong> recipients
        </div>
        <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn-modal-submit" id="modalConfirmSendBtn">
          <i class="fa fa-paper-plane mr-1"></i> Confirm & Send Wishes
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- 3️⃣ CUSTOM THEME MODAL: SINGLE WISH PREVIEW & DIRECT SEND      -->
<!-- (Opened when clicking the row WhatsApp action icon)           -->
<!-- ============================================================== -->
<div class="modal fade" id="singleWishPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-whatsapp text-success mr-2" style="font-size: 15px;"></i> Send Birthday Wish via WhatsApp
        </h5>
        <button type="button" class="btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>

      <div class="modal-body p-3" style="background: #f8fafc;">
        <!-- Recipient Brief Card -->
        <div class="p-2 mb-2 rounded border bg-white d-flex align-items-center justify-content-between shadow-sm">
          <div>
            <span class="font-weight-bold text-dark d-block" id="singleWishName" style="font-size: 13px;">-</span>
            <small class="text-muted" id="singleWishRole">-</small>
          </div>
          <div class="text-right">
            <span class="badge border px-2 py-1 font-weight-bold text-dark" style="background: #eef6f0; border-color: #a7d0b8 !important;" id="singleWishMobile">-</span>
          </div>
        </div>

        <label class="small font-weight-bold text-dark mb-1">Pre-filled Greeting Message:</label>
        <div class="p-2 rounded border bg-white shadow-sm" style="font-size: 11.5px; line-height: 1.5; color: #1e293b; max-height: 140px; overflow-y: auto;">
          <p class="mb-0 text-dark" id="singleWishMessage" style="white-space: pre-line;">-</p>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-modal-cancel" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
        <a href="#" target="_blank" class="btn-modal-submit" id="singleWishSendBtn">
          <i class="fa fa-whatsapp mr-1"></i> Open in WhatsApp Web
        </a>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    let currentTab = 'students';

    // 1. Tab Switching Logic
    $('.b-tab-btn').on('click', function() {
        var targetTab = $(this).data('tab');
        if (targetTab === currentTab) return;

        $('.b-tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.tab-pane-container').hide();
        $('#pane-' + targetTab).show();
        currentTab = targetTab;

        updateFooterSummary();
        updateCheckAllIcon();
    });

    // 2. Date Picker Filter Navigation
    $('#celebrationDateInput').on('change', function() {
        var selectedDate = $(this).val();
        if (selectedDate) {
            window.location.href = "{{ url('happy_birthday') }}?date=" + selectedDate;
        }
    });

    // 3. Selection Counter & Dynamic Update
    function updateSelectedCount() {
        var totalChecked = $('.chk-recipient:checked').length;
        $('.selectedCountText').text(totalChecked);
        
        var activeTabChecked = currentTab === 'students' ? $('.chk-student:checked').length : $('.chk-user:checked').length;
        var activeTabTotal = currentTab === 'students' ? $('.chk-student:visible').length : $('.chk-user:visible').length;

        var chkAllId = currentTab === 'students' ? '#select_all_student' : '#select_all_user';
        if (activeTabTotal > 0 && activeTabChecked === activeTabTotal) {
            $(chkAllId).prop('checked', true);
            $('#check_box_icon').removeClass('fa-square-o').addClass('fa-check-square-o');
            $('#btn-checkall').data('status', 1);
        } else {
            $(chkAllId).prop('checked', false);
            $('#check_box_icon').removeClass('fa-check-square-o').addClass('fa-square-o');
            $('#btn-checkall').data('status', 0);
        }
    }

    $(document).on('change', '.chk-recipient', function() {
        updateSelectedCount();
    });

    // 4. Tab Specific Select All
    $('#select_all_student').on('click', function() {
        var isChecked = $(this).is(':checked');
        $('.student-row:visible .chk-student').prop('checked', isChecked);
        updateSelectedCount();
    });

    $('#select_all_user').on('click', function() {
        var isChecked = $(this).is(':checked');
        $('.user-row:visible .chk-user').prop('checked', isChecked);
        updateSelectedCount();
    });

    // 5. Global Top Select All Button
    $('#btn-checkall').on('click', function() {
        var status = $(this).data('status');
        var targetClass = currentTab === 'students' ? '.student-row:visible .chk-student' : '.user-row:visible .chk-user';
        var tableSelectAll = currentTab === 'students' ? '#select_all_student' : '#select_all_user';

        if (status == 0) {
            $(targetClass).prop('checked', true);
            $(tableSelectAll).prop('checked', true);
            $(this).data('status', 1);
            $('#check_box_icon').removeClass('fa-square-o').addClass('fa-check-square-o');
        } else {
            $(targetClass).prop('checked', false);
            $(tableSelectAll).prop('checked', false);
            $(this).data('status', 0);
            $('#check_box_icon').removeClass('fa-check-square-o').addClass('fa-square-o');
        }
        updateSelectedCount();
    });

    function updateCheckAllIcon() {
        var activeTabChecked = currentTab === 'students' ? $('.chk-student:checked').length : $('.chk-user:checked').length;
        var activeTabTotal = currentTab === 'students' ? $('.chk-student:visible').length : $('.chk-user:visible').length;

        if (activeTabTotal > 0 && activeTabChecked === activeTabTotal) {
            $('#check_box_icon').removeClass('fa-square-o').addClass('fa-check-square-o');
            $('#btn-checkall').data('status', 1);
        } else {
            $('#check_box_icon').removeClass('fa-check-square-o').addClass('fa-square-o');
            $('#btn-checkall').data('status', 0);
        }
    }

    // 6. Instant High-Performance Client-Side Live Filters
    function applyFilter(tableType) {
        var pane = $('#pane-' + tableType);
        var rowClass = tableType === 'students' ? '.student-row' : '.user-row';
        var emptyRowId = tableType === 'students' ? '#studentsEmptyRow' : '#usersEmptyRow';

        var filterInputs = pane.find('.live-filter-input, .live-filter-select');
        var activeFilters = {};

        filterInputs.each(function() {
            var col = $(this).data('col');
            var val = $(this).val().trim().toLowerCase();
            if (val !== '') {
                activeFilters[col] = val;
            }
        });

        var visibleCount = 0;
        pane.find(rowClass).each(function() {
            var row = $(this);
            var matches = true;

            for (var col in activeFilters) {
                var rowVal = (row.data(col) || '').toString().toLowerCase();
                var searchVal = activeFilters[col];

                if (col === 'status') {
                    if (rowVal !== searchVal) {
                        matches = false;
                        break;
                    }
                } else {
                    if (rowVal.indexOf(searchVal) === -1) {
                        matches = false;
                        break;
                    }
                }
            }

            if (matches) {
                row.show();
                visibleCount++;
            } else {
                row.hide();
            }
        });

        if (visibleCount === 0) {
            $(emptyRowId).show();
        } else {
            $(emptyRowId).hide();
        }

        updateFooterSummary();
        updateSelectedCount();
    }

    $('.live-filter-input').on('keyup', function() {
        var tableType = $(this).closest('.tab-pane-container').attr('id').replace('pane-', '');
        applyFilter(tableType);
    });

    $('.live-filter-select').on('change', function() {
        var tableType = $(this).closest('.tab-pane-container').attr('id').replace('pane-', '');
        applyFilter(tableType);
    });

    // 7. Reset Filter Buttons
    $('.filter-reset-btn').on('click', function() {
        var target = $(this).data('target') || currentTab;
        var pane = $('#pane-' + target);
        pane.find('.live-filter-input').val('');
        pane.find('.live-filter-select').val('');
        applyFilter(target);
    });

    // 8. Footer Summary Update
    function updateFooterSummary() {
        if (currentTab === 'students') {
            var visibleCount = $('.student-row:visible').length;
            var totalCount = $('.student-row').length;
            $('#footerSummary').text('Showing ' + visibleCount + ' of ' + totalCount + ' students ({{ $studentPendingCount }} pending, {{ $studentSentCount }} sent)');
        } else {
            var visibleCount = $('.user-row:visible').length;
            var totalCount = $('.user-row').length;
            $('#footerSummary').text('Showing ' + visibleCount + ' of ' + totalCount + ' staff members ({{ $userPendingCount }} pending, {{ $userSentCount }} sent)');
        }
    }

    // 9. Custom Theme Alert Helper Function (Replaces browser alert)
    function showCustomAlert(title, heading, message) {
        $('#alertModalTitle').text(title);
        $('#alertModalHeading').text(heading);
        $('#alertModalMessage').text(message);
        $('#birthdayAlertModal').modal('show');
    }

    // 10. Send Wishes Trigger -> Opens Custom Confirmation Modal (Replaces browser confirm)
    $('#btn-send-wishes-top, #btn-submit-bottom').on('click', function(e) {
        e.preventDefault();
        var checkedRecipients = $('.chk-recipient:checked');
        var totalChecked = checkedRecipients.length;

        if (totalChecked === 0) {
            showCustomAlert(
                "Selection Required",
                "No Recipient Selected",
                "Please select at least one student or staff member by clicking their checkbox before sending wishes."
            );
            return false;
        }

        // Build the recipient queue list inside the custom confirmation modal
        $('#modalRecipientBadge').text(totalChecked + ' Selected');
        $('#modalTotalCountText').text(totalChecked);

        var queueRows = '';
        var counter = 1;
        checkedRecipients.each(function() {
            var row = $(this).closest('tr');
            var isStudent = row.hasClass('student-row');
            var name = row.find('td:nth-child(3) .font-weight-bold').text().trim();
            var roleOrClass = row.find('td:nth-child(4)').text().trim();
            var mobile = row.find('td:nth-child(6)').text().trim();

            queueRows += '<tr class="modal-queue-row" data-search="' + (name + ' ' + roleOrClass + ' ' + mobile).toLowerCase() + '">';
            queueRows += '<td class="text-center text-muted font-weight-bold">' + (counter++) + '</td>';
            queueRows += '<td><strong class="text-dark">' + name + '</strong></td>';
            queueRows += '<td><span class="badge ' + (isStudent ? 'badge-success' : 'badge-primary') + '" style="font-size: 9.5px;">' + roleOrClass + '</span></td>';
            queueRows += '<td class="text-center font-weight-bold text-dark">' + mobile + '</td>';
            queueRows += '</tr>';
        });

        $('#modalQueueTbody').html(queueRows);
        $('#modalSearchQueue').val('');
        $('#confirmWishesModal').modal('show');
    });

    // 11. Modal Internal Recipient Filter
    $('#modalSearchQueue').on('keyup', function() {
        var query = $(this).val().trim().toLowerCase();
        $('.modal-queue-row').each(function() {
            var searchData = $(this).data('search') || '';
            if (query === '' || searchData.indexOf(query) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 12. Confirm Button Click inside Custom Modal -> Submits Form
    $('#modalConfirmSendBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Sending Wishes...');
        $('#birthdayWishesForm').submit();
    });

    // 13. Single Row WhatsApp Action -> Custom Modal Preview
    $(document).on('click', '.btn-preview-wish', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var role = $(this).data('role');
        var mobile = $(this).data('mobile');
        var msg = $(this).data('msg');
        var waUrl = $(this).data('wa-url');

        $('#singleWishName').text(name);
        $('#singleWishRole').text(role);
        $('#singleWishMobile').text(mobile);
        $('#singleWishMessage').text(msg);
        $('#singleWishSendBtn').attr('href', waUrl);

        $('#singleWishPreviewModal').modal('show');
    });

    // Initialize counts on load
    updateSelectedCount();
    updateFooterSummary();
});
</script>

@endsection