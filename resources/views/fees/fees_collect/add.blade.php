@php
$getstudents = Helper::getstudents();
$courses = Helper::getCourses();
$classType = Helper::classType($serach['course_id'] ?? null);
$allClassType = Helper::classType();
$getPaymentMode = Helper::getPaymentMode();
$getSession = Helper::getSession();
$batches = Helper::getBatch();
$array = [];
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   FEES COLLECT PAGE - EXCEL GREEN EXECUTIVE THEME (#217346)
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

.fees-collect-page {
    background-color: #f4f6f9;
    font-size: 12px;
}

/* Main Unified Dashboard Card Wrapper */
.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

/* Executive Header Bar */
.dash-titlebar {
    min-height: 42px;
    padding: 6px 14px;
    background: var(--green-excel) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.dash-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.dash-title i {
    color: #ffc107;
}

.dash-title small {
    font-size: 11.5px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 6px;
}

.dash-title-tools {
    display: flex;
    align-items: center;
    gap: 6px;
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

/* Unified Filter Toolbar */
.filter-card {
    background: #f8fafc;
    border-bottom: 1px solid var(--line-subtle);
    padding: 10px 14px 8px 14px;
}
.form-label-compact {
    font-size: 10.5px;
    font-weight: 700;
    margin-bottom: 2px;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    display: block;
}
.form-control-compact {
    height: 30px !important;
    font-size: 12px !important;
    padding: 3px 8px !important;
    border-radius: 3px !important;
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
}
.form-control-compact:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15) !important;
}

.select2-container--default .select2-selection--single {
    height: 30px !important;
    padding: 2px 6px !important;
    font-size: 12px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 3px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px !important;
    padding-left: 2px !important;
    color: #1e293b !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px !important;
}

/* Student Search Results Table */
.student-list-card {
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    background: #ffffff;
    overflow: hidden;
}
.student-table-scroll {
    max-height: 260px;
    overflow-y: auto;
}
.student-table {
    margin-bottom: 0;
    font-size: 11.5px;
}
.student-table thead th {
    background: #217346 !important;
    color: #ffffff !important;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 7px 10px !important;
    border: 1px solid #185c37 !important;
    position: sticky;
    top: 0;
    z-index: 5;
}
.student-table tbody tr {
    cursor: pointer;
    transition: all 0.12s ease;
}
.student-table tbody tr:hover {
    background-color: #f0fdf4 !important;
}
.student-table tbody tr.active-student {
    background-color: #217346 !important;
    color: #ffffff !important;
}
.student-table tbody tr.active-student td,
.student-table tbody tr.active-student td strong,
.student-table tbody tr.active-student td span {
    color: #ffffff !important;
}
.badge-class-pill {
    display: inline-block;
    padding: 2px 8px;
    font-size: 10.5px;
    font-weight: 600;
    border-radius: 3px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
}
.student-table tbody tr.active-student .badge-class-pill {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
}
.student-table td {
    padding: 6px 10px !important;
    vertical-align: middle !important;
    border-top: 1px solid #e2e8f0;
}
.student-list-footer {
    background: #f8fafc !important;
    border-top: 1px solid #e2e8f0 !important;
    padding: 6px 12px !important;
    font-size: 11px !important;
    color: #475569 !important;
}

/* Student Preview Card */
.student-preview-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-top: 3px solid #217346;
    border-radius: 4px;
    padding: 10px;
    height: 100%;
}
.student-preview-card table th,
.student-preview-card table td {
    background: #ffffff !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
    vertical-align: middle !important;
}
.student-preview-card table th.student-avatar-cell {
    background: #f8fafc !important;
    padding: 4px !important;
    text-align: center !important;
    border: 1px solid #e2e8f0 !important;
}
.student-avatar {
    width: 95px;
    height: 95px;
    object-fit: cover;
    border-radius: 4px;
    border: 2px solid #217346 !important;
    background: #ffffff;
    padding: 2px;
}
.badge-soft-selected {
    background: #e2f0d9;
    color: #185c37;
    border: 1px solid #c3e6cb;
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 3px;
}

/* ========================================================
   MODAL THEME (Matching student/promote_add layout)
   ======================================================== */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 4px !important;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.22) !important;
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
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
}

/* Modern Frosted Waiting Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
}
.loading-card {
    background: #ffffff;
    border-radius: 6px;
    padding: 24px 32px;
    text-align: center;
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
    max-width: 320px;
    width: 90%;
    border-top: 4px solid var(--green-excel);
}
.loading-spinner {
    width: 42px;
    height: 42px;
    border: 3.5px solid #e2e8f0;
    border-top: 3.5px solid var(--green-excel);
    border-radius: 50%;
    animation: spinLoader 0.75s linear infinite;
    margin: 0 auto 14px auto;
}
@keyframes spinLoader {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.loading-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
}
.loading-sub {
    font-size: 11.5px;
    color: #64748b;
    margin: 0;
}
</style>

<div class="content-wrapper fees-collect-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">
            
            <!-- Main Unified Fee Collect Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-money"></i>
                        <span>{{ __('fees.Collect Student Fees') }}</span>
                        <small>Counter Fee Collection, Receipts &amp; Ledger Desk</small>
                    </h1>
                    
                    <div class="dash-title-tools">
                        <a href="{{ url('ca_report') }}" class="btn-header-action" title="Fee Receipts">
                            <i class="fa fa-file-text-o text-warning mr-1"></i> Fee Receipts
                        </a>
                        <a href="{{ url('fees/ledger') }}" class="btn-header-action" title="Fees Ledger">
                            <i class="fa fa-book text-info mr-1"></i> Fees Ledger
                        </a>
                        <a href="{{ url('fee_dashboard') }}" class="btn-header-action" title="Back to Dashboard">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> Back
                        </a>
                    </div>
                </div>

                <!-- 2. Compact Filter Form -->
                <form id="quickForm" method="get" action="{{ url('Fees/add') }}">
                    <div class="filter-card">
                        <div class="row align-items-end mx-n1">
                            <!-- Course -->
                            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-graduation-cap text-success mr-1"></i>{{ __('Course') }}</label>
                                <select class="form-control select2 filter-field" id="course_id" name="course_id">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id ?? '' }}" {{ ($course->id == ($serach['course_id'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Class / Semester -->
                            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-book text-success mr-1"></i>{{ __('Class/Semester') }}</label>
                                <select class="form-control select2 filter-field" id="class_type_id" name="class_type_id">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($classType))
                                        @foreach($classType as $type)
                                            @if(Session::get('role_id') !== 2)
                                                 <option value="{{ $type->id ?? '' }}" {{ ($type->id == ($serach['class_type_id'] ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                            @else
                                                 <option value="{{ $type->id ?? '' }}" {{ ($type->id == ($serach['class_type_id'] ?? '')) ? 'selected' : '' }} {{ ($type->id !== Session::get('class_type_id')) ? 'hidden' : '' }}>{{ $type->name ?? '' }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Batch -->
                            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-users text-success mr-1"></i>{{ __('Batch') }}</label>
                                <select class="form-control select2 filter-field" id="batch" name="batch">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($batches))
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->name ?? '' }}" {{ ($batch->name == ($serach['batch'] ?? '')) ? 'selected' : '' }}>{{ $batch->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>                           
                            </div>

                            <!-- Admission No / Student ID -->
                            <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-id-card text-success mr-1"></i>{{ __('student.Admission No.') }}</label>
                                <input type="text" class="form-control form-control-compact filter-field" id="admission_no" name="admission_no" placeholder="Adm No." value="{{ $serach['admission_no'] ?? '' }}" autocomplete="off">
                            </div>

                            <!-- Search By Keywords -->
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 px-1 mb-1">
                                <label class="form-label-compact"><i class="fa fa-search text-success mr-1"></i>{{ __('common.Search By Keywords') }}</label>
                                <input type="text" class="form-control form-control-compact filter-field" value="{{ $serach['name'] ?? '' }}" id="name" name="name" placeholder="Student Name / Mobile / Father" autocomplete="off">
                            </div>

                            <!-- Search & Reset Actions -->
                            <div class="col-xl-1 col-lg-1 col-md-6 col-sm-6 col-12 px-1 mb-1 d-flex">
                                <button type="submit" class="btn btn-sm btn-success flex-fill mr-1 shadow-sm font-weight-bold" style="height: 30px; background: #217346; border-color: #185c37;" title="{{ __('common.Search') }}">
                                    <i class="fa fa-search"></i>
                                </button>
                                <a href="{{ url('Fees/add') }}" class="btn btn-sm btn-outline-secondary flex-fill shadow-sm d-flex align-items-center justify-content-center" style="height: 30px;" title="Reset Filters">
                                    <i class="fa fa-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- 3. Student Search Results (If Found) -->
                @if(!empty($data))
                <div class="p-2">
                    <div class="row mx-n1">
                        <div class="col-12 col-md-7 px-1 mb-2">
                            <div class="student-list-card shadow-sm">
                                <div class="student-table-scroll">
                                    <table class="table table-bordered table-hover student-table" id="trColor">
                                        <thead>
                                            <tr>
                                                <th style="width: 85px;">Ledger No</th>
                                                <th class="text-center" style="width: 110px;">{{ __('student.Admission No.') }}</th>
                                                <th>{{ __('common.Name') }}</th>
                                                <th>Father's Name</th>
                                                <th>{{ __('common.Class') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data as $item)
                                                @php
                                                    $array[$item->id] = $item;
                                                @endphp
                                                <tr class="quickCollect" data-id="{{ $item->id ?? '' }}" data-session-id="{{ $item->session_id ?? '' }}" data-system-id="{{ $item['unique_system_id'] ?? '' }}">
                                                    <td class="font-weight-bold text-muted">{{ $item->ledger_no ?? 'NA' }}</td>
                                                    <td class="text-center font-weight-bold" style="color: #1e293b;">{{ $item['admissionNo'] ?? '' }}</td>
                                                    <td class="font-weight-bold" style="color: #0f172a;">{{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}</td>
                                                    <td class="text-muted">{{ $item['father_name'] ?? '-' }}</td>
                                                    <td><span class="badge-class-pill">{{ $item['ClassTypes']['name'] ?? 'N/A' }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="student-list-footer d-flex justify-content-between align-items-center">
                                    <span><i class="fa fa-users text-success mr-1"></i> Showing <strong style="color: #0f172a;">{{ count($data) }}</strong> matching student(s).</span>
                                    <span class="font-italic text-muted">Click student row to load payment window</span>
                                </div>
                            </div>
                        </div>

                        <!-- Student Preview Profile Card -->
                        <div class="col-12 col-md-5 px-1 mb-2" id="show_details" style="display:none;">
                            <div class="student-preview-card shadow-sm">
                                <div class="d-flex align-items-center mb-2 pb-1 border-bottom">
                                    <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 12.5px;">
                                        <i class="fa fa-id-badge text-success mr-1"></i> Student Information
                                    </h6>
                                    <span class="badge-soft-selected py-1 px-2 ml-auto">Selected</span>
                                </div>
                                <table class="table table-bordered table-sm mb-0" style="font-size: 11.5px;">
                                    <tr>
                                        <th rowspan="5" class="student-avatar-cell align-middle" style="width: 105px;">
                                            <img src="" class="student-avatar" id="student-image" alt="Profile" />
                                        </th>
                                        <th style="width: 75px;" class="text-muted">Name</th>
                                        <td id="student-name" class="font-weight-bold" style="color: #0f172a;"></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Mobile</th>
                                        <td id="student-mobile" class="font-weight-bold"></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Father</th>
                                        <td id="father-name"></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Mother</th>
                                        <td id="mother-name"></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">F. Mobile</th>
                                        <td id="father-mobile"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="p-4 text-center text-muted">
                    <div class="mb-2">
                        <i class="fa fa-search-plus fa-3x text-muted" style="opacity: 0.4;"></i>
                    </div>
                    <h6 class="font-weight-bold text-secondary mb-1">Search or Select Student to Collect Fees</h6>
                    <p class="small text-muted mb-0">Use Course, Class, Batch, Admission No or Student Name keywords above to locate student.</p>
                </div>
                @endif

            </div>

            <!-- Student Fees Dynamic Detail Panel -->
            <div id="student_fees_detail" class="mt-2"></div>
        </div>
    </section>
</div>

<!-- Modern Fullscreen Loading Overlay -->
<div class="loading-overlay" id="appLoadingOverlay">
    <div class="loading-card">
        <div class="loading-spinner"></div>
        <div class="loading-title" id="overlayTitle">Processing Payment</div>
        <p class="loading-sub" id="overlaySubtitle">Collecting fee and generating receipt, please wait...</p>
    </div>
</div>

<!-- Modal: Quick Add & Assign Fee Group / Head -->
<div class="modal fade" id="quickAddFeeGroupModal" tabindex="-1" role="dialog" aria-labelledby="quickAddFeeGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
        <div class="modal-content" style="border: 1px solid #9eacb7; border-radius: 6px; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
            <div class="modal-header" style="background: #217346; color: #ffffff; padding: 10px 14px;">
                <h6 class="modal-title font-weight-bold text-white mb-0" id="quickAddFeeGroupModalLabel" style="font-size: 13.5px;">
                    <i class="fa fa-plus-circle mr-1"></i> Quick Add &amp; Assign Fee Group / Head
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickAddFeeGroupForm">
                @csrf
                <input type="hidden" name="admission_id" id="quick_modal_admission_id" value="">
                <input type="hidden" name="unique_system_id" id="quick_modal_unique_system_id" value="">
                <input type="hidden" name="session_id" id="quick_modal_session_id" value="">

                <div class="modal-body p-3" style="font-size: 12px; background: #ffffff;">
                    
                    <!-- Selected Student / Class Info Banner -->
                    <div class="p-2 mb-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><i class="fa fa-user text-success mr-1"></i> Student: <strong id="quick_modal_stu_name" class="text-dark">-</strong></div>
                            <div><i class="fa fa-id-badge text-primary mr-1"></i> Adm No: <strong id="quick_modal_stu_adm" class="text-dark">-</strong></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 11px;">
                            <div><i class="fa fa-book text-success mr-1"></i> Course: <span id="quick_modal_stu_course">-</span></div>
                            <div><i class="fa fa-calendar text-info mr-1"></i> Batch: <span id="quick_modal_stu_batch">-</span></div>
                        </div>
                    </div>

                    <!-- Fee Head Name -->
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                            <i class="fa fa-tag text-success mr-1"></i> Fee Group / Head Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="fee_name" id="quick_modal_fee_name" list="existingFeeHeadsList" class="form-control form-control-sm" placeholder="e.g. Exam Fee, Hostel Fee, Admission Fee, Activity Fee..." required autocomplete="off" style="font-size: 12px; height: 32px;">
                        <datalist id="existingFeeHeadsList">
                            @php
                                $existingFeeHeads = DB::table('fees_group')
                                    ->where('branch_id', Session::get('branch_id'))
                                    ->whereNull('deleted_at')
                                    ->pluck('name')
                                    ->unique();
                            @endphp
                            @if(!empty($existingFeeHeads))
                                @foreach($existingFeeHeads as $fHead)
                                    <option value="{{ $fHead }}">
                                @endforeach
                            @endif
                        </datalist>
                    </div>

                    <div class="row mx-n1 mb-2">
                        <!-- Amount -->
                        <div class="col-6 px-1">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                                <i class="fa fa-inr text-success mr-1"></i> Amount (₹) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" min="1" name="amount" id="quick_modal_amount" class="form-control form-control-sm" placeholder="e.g. 5000" required style="font-size: 12px; height: 32px;">
                        </div>
                        <!-- Due Date -->
                        <div class="col-6 px-1">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">
                                <i class="fa fa-calendar text-primary mr-1"></i> Due Date
                            </label>
                            <input type="date" name="due_date" id="quick_modal_due_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="font-size: 12px; height: 32px;">
                        </div>
                    </div>

                    <div class="row mx-n1 mb-2">
                        <!-- Refundable -->
                        <div class="col-6 px-1">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">Refundable?</label>
                            <select name="fees_refund" id="quick_modal_refund" class="form-control form-control-sm" style="font-size: 11.5px; height: 32px;">
                                <option value="no">No</option>
                                <option value="yes">Yes (Security Deposit)</option>
                            </select>
                        </div>
                        <!-- Group Type -->
                        <div class="col-6 px-1">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 11px;">Group Type</label>
                            <select name="group_type" id="quick_modal_group_type" class="form-control form-control-sm" style="font-size: 11.5px; height: 32px;">
                                <option value="other">General Fee Head</option>
                                <option value="tuition">Tuition Fee</option>
                                <option value="exam">Exam Fee</option>
                                <option value="hostel">Hostel Fee</option>
                                <option value="bus">Transport / Bus</option>
                            </select>
                        </div>
                    </div>

                    <!-- Assign Scope Options -->
                    <div class="form-group mb-1 mt-3 p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <label class="font-weight-bold text-dark d-block mb-1" style="font-size: 11px;">
                            <i class="fa fa-users text-primary mr-1"></i> Assignment Target:
                        </label>
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="targetCurrentStudent" name="assign_target" value="current_student" class="custom-control-input" checked>
                            <label class="custom-control-label font-weight-bold text-dark" for="targetCurrentStudent" style="cursor: pointer; font-size: 11.5px;">
                                Only for Current Selected Student (<span id="target_student_name_label" class="text-success">This Student</span>)
                            </label>
                            <small class="d-block text-muted pl-4">Assigns this fee head immediately to this student's account only.</small>
                        </div>
                        <div class="custom-control custom-radio mt-2">
                            <input type="radio" id="targetAllCourseBatch" name="assign_target" value="all_course_batch" class="custom-control-input">
                            <label class="custom-control-label font-weight-bold text-primary" for="targetAllCourseBatch" style="cursor: pointer; font-size: 11.5px;">
                                Assign to All Students in this Course &amp; Batch (<span id="target_course_batch_label">Course &amp; Batch</span>)
                            </label>
                            <small class="d-block text-muted pl-4">Assigns this fee head to all enrolled students of this course/class &amp; batch.</small>
                        </div>
                    </div>

                </div>

                <div class="modal-footer p-2" style="background: #f1f4f6; border-top: 1px solid #cbd5e1;">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnSubmitQuickAddFeeGroup" class="btn btn-sm btn-success font-weight-bold" style="background: #217346; border-color: #185c37; padding: 4px 14px;">
                        <span id="btnSubmitText"><i class="fa fa-check-circle mr-1"></i> Save &amp; Assign Head</span>
                        <span id="btnSubmitSpinner" style="display: none;"><i class="fa fa-circle-o-notch fa-spin mr-1"></i> Assigning...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var selectedSystemId = '{{ $serach["unique_system_id"] ?? "" }}';

function showLoadingOverlay(title, subtitle) {
    if (title) $('#overlayTitle').text(title);
    if (subtitle) $('#overlaySubtitle').text(subtitle);
    $('#appLoadingOverlay').css('display', 'flex').hide().fadeIn(150);
}

function hideLoadingOverlay() {
    $('#appLoadingOverlay').fadeOut(150);
}

$(document).ready(function() {
    // Sync filter parameters to URL
    function syncUrlParams(extraSystemId) {
        var params = new URLSearchParams();
        var sessionId = $('#session_id').val();
        var courseId = $('#course_id').val();
        var classTypeId = $('#class_type_id').val();
        var batch = $('#batch').val();
        var admissionNo = $('#admission_no').val();
        var name = $('#name').val();
        var systemId = (extraSystemId !== undefined) ? extraSystemId : ($('#trColor tbody tr.active-student').data('system-id') || selectedSystemId);

        if (sessionId) params.set('session_id', sessionId);
        if (courseId) params.set('course_id', courseId);
        if (classTypeId) params.set('class_type_id', classTypeId);
        if (batch) params.set('batch', batch);
        if (admissionNo) params.set('admission_no', admissionNo.trim());
        if (name) params.set('name', name.trim());
        if (systemId) params.set('unique_system_id', systemId);

        var queryString = params.toString();
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.history.replaceState({}, '', newUrl);
    }

    $(document).on('change', '.filter-field', function() {
        selectedSystemId = '';
        $('#trColor tbody tr').removeClass('active-student');
        syncUrlParams('');
    });

    $(document).on('input', '#admission_no, #name', function() {
        selectedSystemId = '';
        $('#trColor tbody tr').removeClass('active-student');
        syncUrlParams('');
    });

    function updateClassDropdown(classSelect, data, selectedId) {
        classSelect.empty();
        classSelect.append('<option value="">{{ __("common.Select") }}</option>');
        if (data && data.length > 0) {
            $.each(data, function(key, val) {
                var isSelected = (selectedId && selectedId == val.id) ? 'selected' : '';
                classSelect.append('<option value="' + val.id + '" ' + isSelected + '>' + val.name + '</option>');
            });
        }
        if (classSelect.hasClass("select2-hidden-accessible")) {
            classSelect.select2('destroy');
        }
        classSelect.select2();
        if (!selectedId) {
            classSelect.val('').trigger('change');
        }
    }

    $(document).on('change', '#course_id', function() {
        var courseId = $(this).val();
        var classSelect = $('#class_type_id');
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course_id: courseId },
                dataType: "json",
                success: function(data) {
                    updateClassDropdown(classSelect, data);
                    syncUrlParams('');
                },
                error: function() {
                    updateClassDropdown(classSelect, []);
                    syncUrlParams('');
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
            syncUrlParams('');
        }
    });

    // Handle student row selection
    $(document).on("click", ".quickCollect", function(){
        var sysId = $(this).data('system-id');
        var admId = $(this).data('id');
        var sessId = $(this).data('session-id');
        selectedSystemId = sysId;
        
        $('#trColor tbody tr').removeClass('active-student');
        $(this).addClass('active-student');

        syncUrlParams(sysId);

        @if(!empty($array))
            var array = @json($array);
            var id = $(this).data('id');
            var student = array[id];

            if (student) {
                const IMAGE_SHOW_PATH = "{{ env('IMAGE_SHOW_PATH') }}";
                const path = student.image 
                    ? `${IMAGE_SHOW_PATH}profile/${student.image}` 
                    : `${IMAGE_SHOW_PATH}default/user_image.jpg`;

                $("#student-image").attr("src", path);
                $("#student-name").text((student.first_name || '') + ' ' + (student.last_name || ''));
                $("#student-mobile").text(student.mobile || '-');
                $("#father-name").text(student.father_name || '-');
                $("#mother-name").text(student.mother_name || '-');
                $("#father-mobile").text(student.father_mobile || '-');

                $('#show_details').show();
            }
        @endif

        showData(sysId, sessId, admId);
    });

    // Auto-open student if matching row is found or exactly 1 result returned
    var urlParams = new URLSearchParams(window.location.search);
    var targetSysId = urlParams.get('unique_system_id');
    var targetAdmId = urlParams.get('admission_id');
    var targetAdmNo = urlParams.get('admission_no');

    var targetRow = null;
    if (targetAdmId) {
        targetRow = $('.quickCollect[data-id="' + targetAdmId + '"]');
    }
    if ((!targetRow || targetRow.length === 0) && targetAdmNo) {
        targetRow = $('.quickCollect').filter(function() {
            return $(this).find('td:eq(1)').text().trim() === targetAdmNo.trim();
        });
    }
    if ((!targetRow || targetRow.length === 0) && targetSysId) {
        targetRow = $('.quickCollect[data-system-id="' + targetSysId + '"]');
    }

    if (targetRow && targetRow.length > 0) {
        targetRow.first().trigger('click');
    } else {
        @if(!empty($data) && count($data) === 1)
            $(".quickCollect").first().trigger('click');
        @endif
    }

    // Open Quick Add Fee Group Modal
    $(document).on('click', '.btnOpenQuickAddFeeGroupModal, .btnOpenQuickAddModalDirect', function() {
        var $btn = $(this);
        var admId = $btn.data('admission-id') || ($('.quickCollect.active-student').data('id') || '');
        var sysId = $btn.data('unique-id') || ($('.quickCollect.active-student').data('system-id') || '');
        var sessId = $btn.data('session-id') || $('#session_id').val();
        var stuName = $btn.data('name') || ($('.quickCollect.active-student').find('td:eq(3)').text().trim() || 'Selected Student');
        var admNo = $btn.data('adm-no') || ($('.quickCollect.active-student').find('td:eq(1)').text().trim() || '-');
        var course = $btn.data('course') || ($('#course_id option:selected').text() || 'Current Course');
        var batch = $btn.data('batch') || ($('#batch option:selected').val() || '2026');

        $('#quick_modal_admission_id').val(admId);
        $('#quick_modal_unique_system_id').val(sysId);
        $('#quick_modal_session_id').val(sessId);

        $('#quick_modal_stu_name').text(stuName);
        $('#quick_modal_stu_adm').text(admNo);
        $('#quick_modal_stu_course').text(course);
        $('#quick_modal_stu_batch').text(batch);

        $('#target_student_name_label').text(stuName);
        $('#target_course_batch_label').text(course + (batch ? ' - ' + batch : ''));

        $('#quick_modal_fee_name').val('');
        $('#quick_modal_amount').val('');
        $('#targetCurrentStudent').prop('checked', true);

        $('#quickAddFeeGroupModal').modal('show');
    });

    // Submit Quick Add Fee Group Form via AJAX
    $('#quickAddFeeGroupForm').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#btnSubmitQuickAddFeeGroup');
        var feeName = $('#quick_modal_fee_name').val();
        var amount = $('#quick_modal_amount').val();

        if (!feeName || !amount || parseFloat(amount) <= 0) {
            toastr.warning('Please enter a valid Fee Head Name and Amount.');
            return;
        }

        $btn.prop('disabled', true);
        $('#btnSubmitText').hide();
        $('#btnSubmitSpinner').show();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('quick_add_assign_fees_group') }}",
            type: "POST",
            data: $form.serialize(),
            dataType: "json",
            success: function(res) {
                $btn.prop('disabled', false);
                $('#btnSubmitText').show();
                $('#btnSubmitSpinner').hide();

                if (res && res.status) {
                    $('#quickAddFeeGroupModal').modal('hide');
                    toastr.success(res.message || 'Fee Head assigned successfully!');
                    
                    // Real-time reload of student bill table without full page reload
                    if (res.unique_system_id) {
                        showData(res.unique_system_id, res.session_id, res.admission_id);
                    }
                } else {
                    toastr.error(res.message || 'Failed to assign fee head.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                $('#btnSubmitText').show();
                $('#btnSubmitSpinner').hide();
                var errMsg = 'Error saving fee head. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                toastr.error(errMsg);
            }
        });
    });
});

function showData(unique_system_id, session_id, admission_id) {
    var basurl = "{{ url('/') }}";
    $('#student_fees_detail').html('<div class="dash-main-card p-4 text-center"><div class="spinner-border text-success" role="status"></div><div class="mt-2 font-weight-bold text-dark" style="font-size: 13px;">Loading Fee Collection &amp; Structure...</div><p class="text-muted small mb-0">Please wait while ledger records are calculated</p></div>');
    
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }, 
        type: 'post',
        url: basurl + '/student_fees_onclick',
        data: {
            unique_system_id: unique_system_id,
            session_id: session_id,
            admission_id: admission_id
        },
        success: function(data) {
            if (data == 0) {
                toastr.warning('Please Assign the Fees for this Student !');
                $('#student_fees_detail').html('<div class="dash-main-card p-4 text-center"><i class="fa fa-exclamation-triangle text-warning fa-2x mb-2"></i><div class="font-weight-bold text-dark" style="font-size: 14px;">No Fees Assigned for this Student</div><p class="text-muted small">You can quickly assign fee heads directly from here or go to Fee Assign module.</p><div class="d-flex justify-content-center gap-2 mt-2"><button type="button" class="btn btn-sm btn-success font-weight-bold btnOpenQuickAddModalDirect mr-2" data-admission-id="' + admission_id + '" data-unique-id="' + unique_system_id + '" data-session-id="' + session_id + '"><i class="fa fa-plus-circle mr-1"></i> Quick Add &amp; Assign Fee Head</button><a href="{{ url("fees_assign") }}" class="btn btn-sm btn-primary"><i class="fa fa-users mr-1"></i> Go to Fee Assign</a></div></div>');
            } else {
                $('#student_fees_detail').html(data);
            }
        },
        error: function() {
            $('#student_fees_detail').html('<div class="alert alert-danger font-weight-bold">Error loading fees details. Please try again.</div>');
        }
    });
}
</script>

@endsection