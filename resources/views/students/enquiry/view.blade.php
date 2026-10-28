@php
  $getCourses = Helper::getCourses();
  $classType = Helper::classType();
  $getState = Helper::getState();
  $getCity = Helper::getCity();
  $getCountry = Helper::getCountry();
  $getPermission = Helper::getPermission();
  $getEnquiryStatus = Helper::getEnquiryStatus();
  $getSession = Helper::getSession();
@endphp
@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   ENQUIRY DIRECTORY - EXCEL GREEN EXECUTIVE THEME
   (Matching admissionView, promote & studentsDashboard)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --green-excel-border: #a7d0b8;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --text-main: #0f172a;
}

.enquiry-ledger-page {
    font-size: 11.5px;
    color: var(--text-main);
    background-color: #f4f6f9;
}

/* 1. Main Table Card */
.enquiry-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 15px;
    overflow: hidden;
}

/* 2. Executive Titlebar */
.enquiry-titlebar {
    min-height: 44px;
    padding: 6px 14px;
    background: var(--green-excel) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.enquiry-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.enquiry-title i {
    color: #ffc107;
}

.enquiry-title small {
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
    padding: 4px 10px !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.35;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-add:hover {
    background: #dcfce7 !important;
    border-color: #4ade80 !important;
    color: #14532d !important;
}

/* 3. Table Scroll and Ledger Grid Structure */
.enquiry-scroll {
    height: calc(100vh - 125px);
    min-height: 480px;
    overflow: auto;
    background: #ffffff;
    border: 1px solid var(--line) !important;
    position: relative;
    display: flex;
    flex-direction: column;
}

.enquiry-table {
    margin: 0 !important;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px;
    border-top: 1px solid var(--line) !important;
    border-left: 1px solid var(--line) !important;
    flex: 1 1 auto;
    height: 100%;
}

.enquiry-table tbody.product_list_show {
    height: 100%;
}

.enquiry-empty-row {
    height: 100%;
}

.enquiry-empty-cell {
    height: 100% !important;
    vertical-align: middle !important;
    text-align: center !important;
    padding: 30px 15px !important;
    background-color: #ffffff !important;
}

.empty-state-card {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    padding: 20px 30px;
}

.empty-state-icon-wrap {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #f0fdf4;
    border: 2px solid #86efac;
    color: var(--green-excel);
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(33, 115, 70, 0.12);
}

.empty-state-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #166534;
    margin-bottom: 4px;
}

.empty-state-subtitle {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 14px 0;
}

.btn-empty-reset {
    background: #ffffff;
    border: 1.5px solid var(--green-excel);
    color: var(--green-excel);
    font-size: 11.5px;
    font-weight: 700;
    border-radius: 20px;
    padding: 4px 18px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease-in-out;
}
.btn-empty-reset:hover {
    background: var(--green-excel);
    color: #ffffff;
}

.enquiry-table th,
.enquiry-table td {
    padding: 4px 6px !important;
    border-right: 1px solid var(--line) !important;
    border-bottom: 1px solid var(--line) !important;
    vertical-align: middle !important;
    white-space: nowrap;
}

/* 1st Header Row (Titles) */
.enquiry-table thead tr:first-child th {
    position: sticky;
    top: 0;
    z-index: 5;
    background-color: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11.5px;
    text-align: center;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    letter-spacing: 0.1px;
}

/* 2nd Header Row (Sticky Filter Row) */
.enquiry-table thead tr.enquiry-filter-row th {
    position: sticky;
    top: 27px;
    z-index: 6;
    background-color: var(--green-excel-light) !important;
    border-bottom: 2px solid var(--green-excel-border) !important;
    border-right: 1px solid var(--line) !important;
    padding: 3px 4px !important;
}

.enquiry-table thead tr.enquiry-filter-row .form-control {
    height: 25px !important;
    font-size: 11px !important;
    padding: 2px 5px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    background: #ffffff !important;
    color: #1e293b !important;
    width: 100%;
}
.enquiry-table thead tr.enquiry-filter-row .form-control:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.2) !important;
    outline: none !important;
}

/* Row Styling & Hover */
.enquiry-table tbody tr:nth-child(even) td {
    background-color: #fafafa;
}

.enquiry-table tbody tr:hover td {
    background-color: #fffde7 !important; /* Soft Yellow Excel Hover */
}

/* Sticky Action Column */
.enquiry-table .fixed_item {
    position: sticky !important;
    right: 0px;
    background-color: #ffffff;
    z-index: 2;
    border-left: 1px solid var(--line) !important;
    border-right: 1px solid var(--line) !important;
    box-shadow: -2px 0 4px rgba(33, 115, 70, 0.08);
}

.enquiry-table tbody tr:nth-child(even) .fixed_item {
    background-color: #fafafa !important;
}

.enquiry-table tbody tr:hover .fixed_item {
    background-color: #fffde7 !important;
}

.enquiry-table th.fixed_item {
    background-color: var(--green-excel) !important;
    color: #ffffff !important;
    z-index: 7 !important;
    border-left: 1px solid var(--green-excel-dark) !important;
}

.enquiry-table thead tr.enquiry-filter-row th.fixed_item {
    background-color: var(--green-excel-light) !important;
    z-index: 8 !important;
    border-left: 1px solid var(--line) !important;
}

/* Modern & Attractive Table Row Action Buttons */
.btn-row-action {
    width: 25px;
    height: 25px;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 4px !important;
    font-size: 11px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.15s ease-in-out !important;
    border: 1px solid transparent !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.btn-row-action:hover {
    transform: scale(1.08);
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
}
.btn-action-view {
    background: #f0fdf4 !important;
    border-color: #bbf7d0 !important;
    color: #166534 !important;
}
.btn-action-view:hover {
    background: #166534 !important;
    border-color: #166534 !important;
    color: #ffffff !important;
}
.btn-action-print {
    background: #f0f9ff !important;
    border-color: #bae6fd !important;
    color: #0369a1 !important;
}
.btn-action-print:hover {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #ffffff !important;
}
.btn-action-edit {
    background: #eff6ff !important;
    border-color: #bfdbfe !important;
    color: #2563eb !important;
}
.btn-action-edit:hover {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
}
.btn-action-remark {
    background: #fffbeb !important;
    border-color: #fde68a !important;
    color: #d97706 !important;
}
.btn-action-remark:hover {
    background: #d97706 !important;
    border-color: #d97706 !important;
    color: #ffffff !important;
}
.btn-action-delete {
    background: #fff1f2 !important;
    border-color: #fecdd3 !important;
    color: #e11d48 !important;
}
.btn-action-delete:hover {
    background: #e11d48 !important;
    border-color: #e11d48 !important;
    color: #ffffff !important;
}

/* Badges */
.badge-course-pill {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    padding: 2px 7px;
    border-radius: 3px;
    font-weight: 700;
    font-size: 11px;
}
.badge-status-pill {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10.5px;
    font-weight: 700;
    display: inline-block;
}

/* Modals */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 4px !important;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,.25) !important;
    background: #ffffff !important;
}
.modal-header {
    padding: 9px 14px !important;
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
    color: #475569 !important;
    cursor: pointer !important;
    padding: 0 4px !important;
    line-height: 1;
    opacity: 0.8;
}
.btn-close-modal:hover {
    color: #0f172a !important;
    opacity: 1;
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
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 12px !important;
}
.btn-modal-cancel:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-modal-submit {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding: 4px 14px !important;
}
.btn-modal-submit:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}
</style>

<div class="content-wrapper enquiry-ledger-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            <div class="card enquiry-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="enquiry-titlebar">
                    <h1 class="enquiry-title">
                        <i class="fa fa-address-book-o"></i> 
                        <span>{{ __('student.View Students Enquiry') }}</span>
                        <small>({{ !empty($data) ? count($data) : 0 }} records)</small>
                    </h1>
                    <div class="title-actions">
                        @if($getPermission->add == 1)
                            <a href="{{ url('enquiryAdd') }}" class="btn-header-action btn-header-add" title="Add New Enquiry">
                                <i class="fa fa-plus text-success mr-1"></i> {{ __('common.Add') }}
                            </a>
                        @endif
                        <a href="{{ url('studentsDashboard') }}" class="btn-header-action" title="Back to Students Dashboard">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Main Filter Form with Sticky Thead Filter Row -->
                <form id="enquiryFilterForm" action="{{ url('enquiryView') }}" method="post" class="w-100 mb-0">
                    @csrf
                    
                    <div class="enquiry-scroll">
                        <table id="example1" class="enquiry-table">
                            <thead>
                                <!-- 1st Header Row: Column Titles -->
                                <tr>
                                    <th style="width: 36px;" class="text-center">#</th>
                                    <th style="width: 85px;" class="text-center">{{ __('student.Reg.No.') }}</th>
                                    <th style="min-width: 140px;">{{ __('common.Name') }}</th>
                                    <th style="width: 100px;" class="text-center">{{ __('common.Mobile No.') }}</th>
                                    <th style="width: 110px;" class="text-center">{{ __('Course') }}</th>
                                    <th style="width: 120px;" class="text-center">{{ __('Class / Semester') }}</th>
                                    <th style="min-width: 120px;">{{ __('common.F Name') }}</th>
                                    <th style="min-width: 110px;" class="text-center">Agent / Referrer</th>
                                    <th style="min-width: 110px;" class="text-center">Enrollment / Reg No</th>
                                    <th style="width: 90px;" class="text-center">{{ __('student.Reg.\Date') }}</th>
                                    <th style="width: 95px;" class="text-center">{{ __('Status') }}</th>
                                    @if($getPermission->download == 1 || $getPermission->edit == 1 || $getPermission->deletes == 1)   
                                        <th class="fixed_item text-center" style="width: 130px;">{{ __('common.Action') }}</th>
                                    @endif
                                </tr>

                                <!-- 2nd Header Row: Exact 1-to-1 Respective Column Filter Row -->
                                <tr class="enquiry-filter-row">
                                    <!-- 1. Under # -->
                                    <th class="text-center" style="width: 36px;">
                                        <a href="{{ url('enquiryView') }}" class="btn-row-action" title="Reset All Filters" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b; margin:0 auto;">
                                            <i class="fa fa-refresh"></i>
                                        </a>
                                    </th>

                                    <!-- 2. Under Reg.No. -->
                                    <th style="width: 85px;">
                                        <input type="text" 
                                               class="form-control live-enquiry-input text-center" 
                                               name="registration_no" 
                                               id="filter_reg_no" 
                                               value="{{ $search['registration_no'] ?? '' }}" 
                                               placeholder="Reg No..."
                                               title="Filter by Registration Number">
                                    </th>

                                    <!-- 3. Under Name -->
                                    <th style="min-width: 140px;">
                                        <input type="text" 
                                               class="form-control live-enquiry-input" 
                                               name="name" 
                                               id="filter_name" 
                                               value="{{ $search['name'] ?? '' }}" 
                                               placeholder="Name / Email..."
                                               title="Filter by Student Name or Email">
                                    </th>

                                    <!-- 4. Under Mobile No. -->
                                    <th style="width: 100px;">
                                        <input type="text" 
                                               class="form-control live-enquiry-input text-center" 
                                               name="mobile" 
                                               id="filter_mobile" 
                                               value="{{ $search['mobile'] ?? '' }}" 
                                               placeholder="Mobile..."
                                               title="Filter by Mobile Number">
                                    </th>

                                    <!-- 5. Under Course -->
                                    <th style="width: 110px;">
                                        <select class="form-control live-enquiry-select" id="course_id" name="course_id" title="Filter Course">
                                            <option value="">All Courses</option>
                                            @if(!empty($getCourses)) 
                                                @foreach($getCourses as $course)
                                                    <option value="{{ $course->id }}" {{ ($course->id == ($search['course_id'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- 6. Under Class / Semester -->
                                    <th style="width: 120px;">
                                        <select class="form-control live-enquiry-select" id="class_type_id" name="class_type_id" title="Filter Class / Semester">
                                            <option value="">All Semesters</option>
                                            @if(!empty($classType)) 
                                                @foreach($classType as $type)
                                                    @if(empty($search['course_id']) || $type->course_id == $search['course_id'])
                                                        <option value="{{ $type->id ?? '' }}" {{ ($type->id == ($search['class_type_id'] ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- 7. Under F Name -->
                                    <th style="min-width: 120px;">
                                        <input type="text" 
                                               class="form-control live-enquiry-input" 
                                               name="father_name" 
                                               id="filter_father_name" 
                                               value="{{ $search['father_name'] ?? '' }}" 
                                               placeholder="Father Name..."
                                               title="Filter by Father's Name">
                                    </th>

                                    <!-- 8. Under Agent -->
                                    <th style="min-width: 110px;">
                                        <select class="form-control live-enquiry-select" id="filter_agent_id" name="agent_id" title="Filter by Agent">
                                            <option value="">All Agents</option>
                                            @php
                                                $activeAgentsList = \App\Models\Agent::getActiveAgents();
                                            @endphp
                                            @if(!empty($activeAgentsList))
                                                @foreach($activeAgentsList as $ag)
                                                    <option value="{{ $ag->id }}" {{ ($ag->id == ($search['agent_id'] ?? '')) ? 'selected' : '' }}>{{ $ag->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- 9. Under Enrollment / Reg No -->
                                    <th style="min-width: 110px;">
                                        <input type="text" 
                                               class="form-control live-enquiry-input text-center" 
                                               name="enrollment_no" 
                                               value="{{ $search['enrollment_no'] ?? '' }}" 
                                               placeholder="Enroll / Reg...">
                                    </th>

                                    <!-- 10. Under Reg. Date -->
                                    <th style="width: 90px;">
                                        <input type="date" 
                                               class="form-control live-enquiry-input" 
                                               id="filter_reg_date" 
                                               name="registration_date" 
                                               value="{{ $search['registration_date'] ?? '' }}"
                                               title="Filter by Registration Date">
                                    </th>

                                    <!-- 11. Under Status -->
                                    <th style="width: 95px;">
                                        <select class="form-control live-enquiry-select" id="enquiry_status" name="enquiry_status" title="Filter Enquiry Status">
                                            <option value="">All Statuses</option>
                                            @if(!empty($getEnquiryStatus))
                                                @foreach($getEnquiryStatus as $type)
                                                    <option value="{{ $type->id ?? '' }}" {{ ($type->id == ($search['enquiry_status'] ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- 12. Under Action (Fixed Sticky) -->
                                    @if($getPermission->download == 1 || $getPermission->edit == 1 || $getPermission->deletes == 1)
                                        <th class="fixed_item text-center" style="width: 130px;">
                                            <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                                                <button type="submit" class="btn-row-action btn-action-view" style="width: 25px; height: 25px;" title="Apply Filter">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                <button type="submit" name="pdf" value="pdf" class="btn-row-action btn-action-print {{ ($getPermission->download == 1) ? '' : 'd-none' }}" style="width: 25px; height: 25px;" title="Export PDF">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </button>
                                            </div>
                                        </th>
                                    @endif
                                </tr>
                            </thead>

                            <!-- Dynamic Table Rows -->
                            <tbody class="product_list_show">
                                @if(!empty($data) && count($data) > 0)
                                    @php $i = 1; @endphp
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="text-center text-muted font-weight-bold">{{ $i++ }}</td>
                                            <td class="text-center font-weight-bold text-dark">{{ $item['registration_no'] ?? '-' }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}</strong>
                                                @if(!empty($item['email']))
                                                    <small class="text-muted d-block" style="font-size: 10px;">{{ $item['email'] }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center font-weight-bold text-muted">
                                                {{ $item['mobile'] ?? '-' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-course-pill">{{ $item['course_name'] ?? '-' }}</span>
                                            </td>
                                            <td class="text-center font-weight-bold text-dark">
                                                {{ $item['class_name'] ?? '-' }}
                                            </td>
                                            <td>{{ $item['father_name'] ?? '-' }}</td>
                                            <td class="text-center">
                                                @if(!empty($item->Agent->name))
                                                    <span class="badge badge-light border font-weight-bold" style="color: #166534; background: #f0fdf4;">
                                                        {{ $item->Agent->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($item->enrollment_no))
                                                    <div class="font-weight-bold text-dark">{{ $item->enrollment_no }}</div>
                                                @endif
                                                @if(!empty($item->web_registration_no))
                                                    <small class="text-muted d-block" style="font-size: 10px;">Web: {{ $item->web_registration_no }}</small>
                                                @endif
                                                @if(empty($item->enrollment_no) && empty($item->web_registration_no))
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ !empty($item['registration_date']) ? date('d-M-Y', strtotime($item['registration_date'])) : '-' }}</td>
                                            @php
                                                $enquiry = DB::table('enquiry_status')->where('id', $item->enquiry_status)->whereNull('deleted_at')->first();
                                                $statusName = $enquiry->name ?? 'Pending';
                                                $badgeBg = '#f1f5f9';
                                                $badgeColor = '#475569';
                                                $badgeBorder = '#cbd5e1';
                                                if ($statusName == 'Joined' || $statusName == 'Admitted') {
                                                    $badgeBg = '#dcfce7'; $badgeColor = '#15803d'; $badgeBorder = '#86efac';
                                                } elseif ($statusName == 'Pending' || $statusName == 'Under Followup') {
                                                    $badgeBg = '#fef3c7'; $badgeColor = '#92400e'; $badgeBorder = '#fde68a';
                                                } elseif ($statusName == 'Rejected' || $statusName == 'Not Interested') {
                                                    $badgeBg = '#fee2e2'; $badgeColor = '#b91c1c'; $badgeBorder = '#fca5a5';
                                                }
                                            @endphp
                                            <td class="text-center">
                                                <span class="badge-status-pill" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }};">
                                                    {{ $statusName }}
                                                </span>
                                            </td>
                                        
                                            @if($getPermission->download == 1 || $getPermission->edit == 1 || $getPermission->deletes == 1)
                                                <td class="fixed_item text-center">
                                                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                                                        <a href="{{ url('studentRegistrationDetail', $item->id) }}" class="btn-row-action btn-action-view" title="View Enquiry Details">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        @if($getPermission->download == 1)
                                                            <a href="{{ url('registrationPrint', $item->id) }}" target="_blank" class="btn-row-action btn-action-print" title="Print Registration Slip">
                                                                <i class="fa fa-print"></i>
                                                            </a>
                                                        @endif
                                                        @if($getPermission->edit == 1)
                                                            <a href="javascript:;" data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#remark_id" data-toggle="modal" data-target="#remark_id" class="remark btn-row-action btn-action-remark" title="Add Follow-up Remark">
                                                                <i class="fa fa-bell"></i>
                                                            </a>                                      
                                                            <a href="{{ url('enquiryEdit', $item->id) }}" class="btn-row-action btn-action-edit" title="Edit Enquiry">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                        @endif
                                                        @if($getPermission->deletes == 1)
                                                            <button type="button" data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#Modal_id" data-toggle="modal" data-target="#Modal_id" class="deleteData btn-row-action btn-action-delete" title="Delete Enquiry">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>  
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="enquiry-empty-row">
                                        <td colspan="100" class="enquiry-empty-cell">
                                            <div class="empty-state-card">
                                                <div class="empty-state-icon-wrap">
                                                    <i class="fa fa-address-book-o"></i>
                                                </div>
                                                <div class="empty-state-title">No student enquiries found</div>
                                                <p class="empty-state-subtitle">No records matched the selected filter criteria.</p>
                                                <a href="{{ url('enquiryView') }}" class="btn-empty-reset">
                                                    <i class="fa fa-refresh"></i> Reset All Filters
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================== -->
<!-- 1️⃣ DELETE CONFIRMATION MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fa fa-trash-o mr-1"></i> {{ __('common.Delete Confirmation') }}
                </h5>
                <button type="button" class="close btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form action="{{ url('enquiryDelete') }}" method="post">
                @csrf
                <input type="hidden" id="delete_id" name="delete_id">

                <div class="modal-body p-3">
                    <div class="d-flex align-items-start mb-2">
                        <div class="mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa fa-exclamation-triangle text-danger" style="font-size: 19px;"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Confirm Enquiry Deletion</strong>
                            <span class="text-muted" style="font-size: 12px;">{{ __('common.Are you sure you want to delete') }}? This student enquiry will be removed.</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal" data-dismiss="modal">{{ __('common.Close') }}</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3" style="font-size: 12px; font-weight: 600;">
                        <i class="fa fa-trash mr-1"></i> {{ __('common.Delete') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 2️⃣ ADD REMARK MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="remark_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-bell text-warning mr-1"></i> {{ __('student.Remark Add') }}
                </h5>
                <button type="button" class="close btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <form action="{{ url('enquiryRemarkAdd') }}" method="post">
                @csrf
                <input type="hidden" id="student_id" name="student_id" value="">

                <div class="modal-body p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6 mb-2">    
                            <label class="form-label small font-weight-bold text-dark mb-1">{{ __('Reminder Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm @error('date') is-invalid @enderror" id="date" name="date" value="{{ date('Y-m-d') }}" required>	
                        </div>                 
                                   
                        <div class="col-md-6 mb-2">    
                            <label class="form-label small font-weight-bold text-dark mb-1">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" id="enquiry_status_id" name="enquiry_status_id" required>
                                <option value="">{{ __('common.Select') }}</option>
                                @if(!empty($getEnquiryStatus))
                                    @foreach($getEnquiryStatus as $type)
                                        <option value="{{ $type->id ?? '' }}">{{ $type->name ?? '' }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>                 
                    </div>

                    <div class="form-group mb-0">    
                        <label class="form-label small font-weight-bold text-dark mb-1">{{ __('student.Remark') }} <span class="text-danger">*</span></label>
                        <textarea id="remark" name="remark" class="form-control form-control-sm" rows="3" placeholder="Enter follow-up remarks / notes here..." required></textarea>
                    </div>
                </div>														
                
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal" data-dismiss="modal">{{ __('common.Close') }}</button>
                    <button type="submit" class="btn-modal-submit" id="submitButton"><i class="fa fa-check mr-1"></i> {{ __('common.Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Delete Trigger
    $(document).on('click', '.deleteData', function() {
        var delete_id = $(this).data('id'); 
        $('#delete_id').val(delete_id); 
    });

    // Remark Trigger
    $(document).on('click', '.remark', function(){
        var student_id = $(this).data('id');
        $("#student_id").val(student_id);
    });

    // Auto submit on filter dropdown changes
    $('.live-enquiry-select').on('change', function() {
        $('#enquiryFilterForm').submit();
    });

    // Dynamic Course-dependent Classes / Semesters loader
    $(document).on('change', '#course_id', function() {
        var courseId = $(this).val();
        var classSelect = $('#class_type_id');
        
        classSelect.empty();
        classSelect.append('<option value="">All Semesters</option>');
        
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course_id: courseId },
                dataType: "json",
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(key, val) {
                            classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                        });
                    }
                }
            });
        } else {
            @if(!empty($classType))
                @foreach($classType as $type)
                    classSelect.append('<option value="{{ $type->id ?? '' }}">{{ $type->name ?? '' }}</option>');
                @endforeach
            @endif
        }
    });

    // Universal Modal Close / Dismiss Handler
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .btn-modal-cancel', function(e) {
        var $targetModal = $(this).closest('.modal');
        if ($targetModal.length) {
            $targetModal.modal('hide');
        }
    });
});
</script>

@endsection
