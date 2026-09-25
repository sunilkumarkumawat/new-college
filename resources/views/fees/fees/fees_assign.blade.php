@extends('layout.app')
@section('content')

@php
    $courses = Helper::getCourses();
    $batches = Helper::getBatch();
    $getFeesGroup = Helper::getFeesGroup();
    $getSession = Helper::getSession();
@endphp

<style>
/* Page & Container */
.content-wrapper {
    background-color: #f4f6f9;
}

/* Card Header - Solid Brand Dark with Crisp White Text & Right-Aligned Tools */
.card-outline.card-orange .card-header.bg-primary {
    background-color: #002c54 !important;
    background: #002c54 !important;
    color: #ffffff !important;
    padding: 8px 15px !important;
    border-bottom: 2px solid #001a33 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
}
.card-header.bg-primary h3,
.card-header.bg-primary .card-title {
    color: #ffffff !important;
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    margin: 0 !important;
    float: none !important;
    display: flex !important;
    align-items: center !important;
}
.card-header.bg-primary .card-tools {
    margin-left: auto !important;
    margin-right: 0 !important;
    float: none !important;
    position: static !important;
    display: flex !important;
    align-items: center !important;
}
.card-header.bg-primary .btn {
    background-color: rgba(255, 255, 255, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.45) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    padding: 4px 10px !important;
}
.card-header.bg-primary .btn:hover {
    background-color: rgba(255, 255, 255, 0.35) !important;
    color: #ffffff !important;
    border-color: #ffffff !important;
}

/* Filter Section */
.filter-label {
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #1e293b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Table Header - Solid Dark with Pure White Font */
.table-assign thead,
.table-assign thead tr,
.table-assign thead th {
    background-color: #002c54 !important;
    background: #002c54 !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    padding: 8px 6px !important;
    vertical-align: middle !important;
    border: 1px solid #103a61 !important;
    text-align: center;
}
.table-assign thead th,
.table-assign thead th * {
    color: #ffffff !important;
}

/* Table Body */
.table-assign tbody td {
    padding: 6px 8px !important;
    font-size: 13px !important;
    vertical-align: middle !important;
    color: #0f172a !important;
}
.table-assign tbody tr:hover {
    background-color: #f1f5f9 !important;
}

/* Interactive Real-Time Fee Head Chips */
.fee-head-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    margin: 2px 2px;
    transition: all 0.2s ease;
    user-select: none;
}
.chip-assigned {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #0284c7;
    box-shadow: 0 1px 2px rgba(2, 132, 199, 0.08);
}
.chip-assigned:hover {
    background-color: #bae6fd;
    border-color: #0284c7;
}
.chip-assigned .chip-name {
    color: #0369a1;
    font-weight: 600;
}
.chip-assigned .chip-amt {
    color: #0284c7;
    font-weight: 700;
}

/* Red Focused Highlight for Unassigned Fee Heads */
.chip-unassigned {
    background-color: #fff1f2;
    color: #be123c;
    border: 1px solid #fda4af;
    box-shadow: 0 1px 2px rgba(225, 29, 72, 0.08);
}
.chip-unassigned:hover {
    background-color: #ffe4e6;
    border-color: #f43f5e;
    color: #9f1239;
}
.chip-unassigned .chip-name {
    color: #9f1239;
    font-weight: 600;
}
.chip-unassigned .chip-amt {
    color: #be123c;
    font-weight: 700;
}
.chip-name {
    font-weight: 600;
    margin-right: 4px;
}
.chip-amt {
    font-weight: 700;
    color: #0f172a;
}

/* Badges */
.badge-secondary {
    background-color: #475569 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
}

/* Select2 Customization */
.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple {
    min-height: 33px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 4px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: 13px !important;
    color: #1e293b !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    padding: 1px 4px !important;
    font-size: 12.5px !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #002c54 !important;
    border: 1px solid #001a33 !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 2px 8px !important;
    margin-top: 3px !important;
    border-radius: 4px !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #ffffff !important;
    margin-right: 6px !important;
    font-weight: bold !important;
    opacity: 0.9 !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #facc15 !important;
    opacity: 1 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #002c54 !important;
    color: #ffffff !important;
}

/* Top Course Fee Structure Panel */
.course-fee-structure-card {
    background-color: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.top-structure-head-chip {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 4px 10px;
    margin: 2px 3px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    user-select: none;
}
.top-structure-head-chip:hover {
    border-color: #002c54;
    background: #f1f5f9;
}
.top-structure-head-chip.selected-top-chip {
    background-color: #e0f2fe !important;
    border-color: #0284c7 !important;
    color: #0369a1 !important;
    font-weight: 600;
    box-shadow: 0 0 0 1px #0284c7;
}
.top-structure-head-chip .badge-amt {
    background: #002c54;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    margin-left: 6px;
}
.top-structure-head-chip.selected-top-chip .badge-amt {
    background: #0284c7;
}

/* Mid-bar Strip */
.mid-action-strip {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 12px;
}

/* Toast Notifications */
#fee_toast_box {
    position: fixed;
    top: 20px;
    right: 25px;
    z-index: 9999;
    min-width: 280px;
}

/* Collected Fees Warning Modal High-Contrast Styling */
#feeCollectedWarningModal .modal-content {
    background-color: #ffffff !important;
    border: none !important;
}
#feeCollectedWarningModal .modal-body {
    background-color: #ffffff !important;
}
#feeCollectedWarningModal .modal-footer {
    background-color: #f1f5f9 !important;
    border-top: 1.5px solid #cbd5e1 !important;
}
.table-collected-invoices thead,
.table-collected-invoices thead tr,
.table-collected-invoices thead th {
    background-color: #002c54 !important;
    background: #002c54 !important;
    color: #ffffff !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    padding: 8px 6px !important;
    border: 1px solid #103a61 !important;
    text-align: center !important;
    vertical-align: middle !important;
}
.table-collected-invoices tbody td {
    background-color: #ffffff !important;
    color: #0f172a !important;
    vertical-align: middle !important;
    font-size: 12px !important;
    padding: 6px 8px !important;
    border-color: #e2e8f0 !important;
}
.table-collected-invoices tbody tr:nth-of-type(odd) td {
    background-color: #f8fafc !important;
}
.table-collected-invoices tbody tr:hover td {
    background-color: #f1f5f9 !important;
}
#btn_goto_fee_collect {
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    transition: all 0.15s ease-in-out;
}
#btn_goto_fee_collect:hover {
    background-color: #b91c1c !important;
    border-color: #991b1b !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
}
</style>

<div id="fee_toast_box"></div>

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card card-outline card-orange mb-3 shadow-sm">
                        
                        <!-- Card Header -->
                        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold">
                                <i class="fa fa-users mr-1"></i> Student Fee Assign
                            </h3>
                            <div class="card-tools">
                                <a href="{{ url('feesGroup') }}" class="btn btn-primary btn-sm mr-2" title="Fees Group & Master">
                                    <i class="fa fa-money mr-1"></i> Fees Structure
                                </a>
                                <a href="{{ url('fee_dashboard') }}" class="btn btn-primary btn-sm" title="Back">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            @include('layout.message')

                            <!-- Filter Inputs Row (3 Clean Columns: Course -> Batch -> Search Student) -->
                            <div class="row mb-2">
                                <!-- 1. Course Select -->
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Course <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_course_id" name="course_id" style="width: 100%;">
                                        <option value="">-- Select Course --</option>
                                        @if(!empty($courses))
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ ($course->id == ($serach['course_id'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- 2. Batch Select -->
                                <div class="col-md-4 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Batch <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_batch" name="batch" style="width: 100%;">
                                        <option value="">-- Select Batch --</option>
                                        @if(!empty($batches))
                                            @foreach($batches as $batch)
                                                <option value="{{ $batch->name ?? '' }}" {{ ($batch->name == ($serach['batch'] ?? '')) ? 'selected' : '' }}>{{ $batch->name ?? '' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- 3. Admission No / Student ID / Name Search -->
                                <div class="col-md-4 col-sm-12 mb-2">
                                    <label class="filter-label">
                                        <span>Student ID / Name / Mobile</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm" id="filter_admission_no" name="admissionNo" placeholder="Type name, admission no or mobile to filter..." value="{{ $serach['admissionNo'] ?? '' }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="btn_search_students" title="Search">
                                                <i class="fa fa-search"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" id="btn_clear_filters" title="Reset Filters">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Interactive Course Fee Structure & Master Heads Toolbar (Visible only when both Course & Batch are selected) -->
                            <div class="course-fee-structure-card" id="course_fee_structure_card" style="display: none;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed #cbd5e1;">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                            <i class="fa fa-cubes text-primary mr-1"></i> Fee Structure: <span id="structure_course_batch_label" class="text-primary font-weight-bold">Course - Batch</span> (<span id="structure_head_count" class="text-dark font-weight-bold">0</span> Heads)
                                        </span>
                                        <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11.5px;">
                                            Total Structure Fee: <strong class="text-dark ml-1" id="structure_total_fee">₹0</strong>
                                        </span>
                                        <span class="badge badge-info px-2 py-1" id="selected_heads_badge" style="font-size: 11.5px; display: none;">
                                            <i class="fa fa-check-circle mr-1"></i><span id="selected_heads_count">0</span> Head(s) Selected
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold px-2 py-1" id="btn_top_select_all_heads" style="font-size: 11px;">
                                            <i class="fa fa-check-square-o mr-1"></i> Select All Heads
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold px-2 py-1" id="btn_top_deselect_all_heads" style="font-size: 11px;">
                                            <i class="fa fa-square-o mr-1"></i> Deselect Heads
                                        </button>
                                    </div>
                                </div>

                                <!-- Dynamic Fee Heads Chips Container -->
                                <div class="d-flex flex-wrap align-items-center" id="course_fee_structure_chips_box" style="gap: 4px; min-height: 32px;">
                                    <!-- Loaded via AJAX -->
                                </div>
                            </div>

                            <!-- Fast Action Toolbar & Real-Time Status Strip -->
                            <div class="mid-action-strip d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <div class="mb-1 mb-md-0 d-flex align-items-center flex-wrap" style="gap: 6px;">
                                    <button type="button" class="btn btn-warning btn-xs font-weight-bold shadow-xs text-dark" id="btn_quick_apply_all_loaded" style="font-size: 11.5px; padding: 4px 10px;">
                                        <i class="fa fa-bolt text-dark mr-1"></i> Quick Apply All Heads to ALL Loaded (<span class="total_students_count_text">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-success btn-xs font-weight-bold shadow-xs" id="btn_bulk_assign_realtime" style="font-size: 11.5px; padding: 4px 10px;">
                                        <i class="fa fa-check-circle mr-1"></i> Assign to Selected Students (<span class="selected_students_count_text">0</span>)
                                    </button>
                                    <span class="badge badge-light border text-muted py-1 px-2" style="font-size: 11px;">
                                        <i class="fa fa-info-circle text-primary mr-1"></i> Click any head or Check All button to assign in real-time
                                    </span>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 4px;">
                                    <span class="badge px-2 py-1" id="total_students_badge" style="background-color: #334155; color: #ffffff; font-size: 12px; font-weight: 500;">Total: 0</span>
                                    <span class="badge px-2 py-1 mr-1" id="selected_students_badge" style="background-color: #16a34a; color: #ffffff; font-size: 12px; font-weight: 600;">Selected: 0</span>
                                    <button type="button" class="btn btn-default btn-xs border" id="btn_quick_select_all" style="font-size: 11.5px; padding: 3px 8px;" title="Select all loaded students">
                                        <i class="fa fa-check-square text-primary mr-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs border" id="btn_quick_deselect_all" style="font-size: 11.5px; padding: 3px 8px;" title="Deselect all">
                                        <i class="fa fa-square-o text-danger mr-1"></i>Deselect
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs border" id="btn_clear_filters" style="font-size: 11.5px; padding: 3px 8px;" title="Reset Filters">
                                        <i class="fa fa-refresh text-secondary mr-1"></i>Reset
                                    </button>
                                </div>
                            </div>

                            <!-- High Density Students Table Container -->
                            <div class="table-responsive border rounded" style="max-height: 540px; overflow-y: auto;">
                                <table class="table table-bordered table-striped table-hover table-sm mb-0 table-assign" id="students_assign_table">
                                    <thead style="position: sticky; top: 0; z-index: 10;">
                                        <tr>
                                            <th style="width: 40px; text-align: center; vertical-align: middle;">
                                                <input type="checkbox" id="all_students" style="cursor: pointer;" title="Select All">
                                            </th>
                                            <th style="width: 40px; text-align: center; vertical-align: middle;">#</th>
                                            <th style="text-align: left; vertical-align: middle; min-width: 140px;">Student Name</th>
                                            <th style="width: 110px; text-align: center; vertical-align: middle;">Admission No.</th>
                                            <th style="width: 120px; text-align: center; vertical-align: middle;">Class / Sem</th>
                                            <th style="text-align: left; vertical-align: middle; min-width: 120px;">Father's Name</th>
                                            <th style="width: 100px; text-align: center; vertical-align: middle;">Mobile No.</th>
                                            <th style="text-align: left; vertical-align: middle; min-width: 270px;">Course Fee Heads (Click to Toggle / Auto-Save)</th>
                                            <th style="min-width: 125px; text-align: center; vertical-align: middle;">Fee Summary</th>
                                            <th style="width: 120px; text-align: center; vertical-align: middle;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_students_list">
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <i class="fa fa-filter fa-2x mb-2 text-secondary d-block"></i>
                                                Please select both <b>Course</b> and <b>Batch</b> above to view and assign students.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Student Fee Details & Modification Modal -->
<div class="modal fade" id="studentFeeEditModal" tabindex="-1" role="dialog" aria-labelledby="studentFeeEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg" id="studentFeeEditModalBody">
            <div class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2 text-muted font-weight-bold">Loading Student Fee Details...</p>
            </div>
        </div>
    </div>
</div>

<!-- Custom Theme Confirmation Modal for Fee Assign / Clear Actions -->
<div class="modal fade" id="feeAssignConfirmModal" tabindex="-1" role="dialog" aria-labelledby="feeAssignConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 text-white" id="feeConfirmModalHeader" style="background: linear-gradient(135deg, #002c54 0%, #00172d 100%); border-bottom: 2px solid #ff7b00;">
                <h5 class="modal-title font-weight-bold d-flex align-items-center" id="feeAssignConfirmModalLabel" style="font-size: 16px; letter-spacing: 0.3px; margin: 0;">
                    <i class="fa fa-question-circle mr-2 text-warning" id="feeConfirmIcon"></i>
                    <span id="feeConfirmTitle">Confirm Action</span>
                </h5>
                <button type="button" class="close text-white btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; text-shadow: none; outline: none; cursor: pointer;">
                    <span aria-hidden="true" style="font-size: 22px;">&times;</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4 text-center">
                <!-- Action Icon Badge -->
                <div class="mb-3">
                    <div id="feeConfirmIconWrapper" class="d-inline-flex justify-content-center align-items-center rounded-circle" style="width: 65px; height: 65px; background: rgba(0, 44, 84, 0.08);">
                        <i class="fa fa-check-square-o fa-2x text-success" id="feeConfirmBigIcon"></i>
                    </div>
                </div>

                <!-- Headline -->
                <h6 class="font-weight-bold text-dark mb-2" id="feeConfirmHeadline" style="font-size: 16px;">
                    Assign All Fee Heads?
                </h6>

                <!-- Context Card -->
                <div class="p-3 mb-3 text-left rounded" id="feeConfirmStudentInfoBox" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px;">
                    <div class="d-flex justify-content-between mb-1" id="feeConfirmStudentNameRow">
                        <span class="text-muted"><i class="fa fa-user mr-1 text-primary"></i> Student Name:</span>
                        <strong class="text-dark" id="feeConfirmStudentName">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1" id="feeConfirmStudentAdmRow">
                        <span class="text-muted"><i class="fa fa-id-card mr-1 text-secondary"></i> Admission No:</span>
                        <strong class="badge badge-secondary px-2 py-1" id="feeConfirmStudentAdmNo">-</strong>
                    </div>
                    <div class="d-flex justify-content-between" id="feeConfirmExtraRow">
                        <span class="text-muted"><i class="fa fa-tag mr-1 text-info"></i> Action:</span>
                        <span class="font-weight-bold text-dark" id="feeConfirmExtraDetails">-</span>
                    </div>
                </div>

                <!-- Message Text -->
                <p class="text-muted mb-0" id="feeConfirmMessage" style="font-size: 13px; line-height: 1.5;">
                    Are you sure you want to proceed with this real-time fee update?
                </p>
                <div class="alert alert-warning py-1 px-2 mt-2 mb-0 d-none text-left" id="feeConfirmPaidWarning" style="font-size: 11.5px; border-radius: 6px;">
                    <i class="fa fa-shield mr-1"></i> <strong>Protection:</strong> Fee heads with already recorded payments will remain untouched.
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-2 px-4 bg-light d-flex justify-content-between border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-1 btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 6px;">
                    <i class="fa fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-sm btn-success font-weight-bold px-4 py-1" id="btn_confirm_fee_modal_action" style="border-radius: 6px;">
                    <i class="fa fa-check mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Proceed</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Alert Modal: Cannot Clear Fee Heads (Fees Already Collected) -->
<div class="modal fade" id="feeCollectedWarningModal" tabindex="-1" role="dialog" aria-labelledby="feeCollectedWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 680px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); border-bottom: 3px solid #ef4444;">
                <h5 class="modal-title font-weight-bold d-flex align-items-center" style="font-size: 16px; letter-spacing: 0.3px; margin: 0; color: #ffffff !important;">
                    <i class="fa fa-ban mr-2 text-warning" style="font-size: 20px;"></i>
                    <span>Cannot Clear Fee Heads: Fees Already Collected</span>
                </h5>
                <button type="button" class="close text-white btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; text-shadow: none; outline: none; cursor: pointer;">
                    <span aria-hidden="true" style="font-size: 22px;">&times;</span>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Important Revert Note Alert -->
                <div class="alert alert-danger mb-3 p-3 shadow-xs" style="border-left: 4px solid #dc2626; border-radius: 8px; background-color: #fef2f2;">
                    <div class="d-flex align-items-start">
                        <i class="fa fa-exclamation-triangle text-danger mr-2 mt-1" style="font-size: 18px;"></i>
                        <div>
                            <strong class="text-danger" style="font-size: 14px;">Fee Revert / Refund Required First</strong>
                            <p class="mb-0 text-dark mt-1" style="font-size: 12.5px; line-height: 1.5;">
                                Fee payments have already been collected for this student. You must first <b>revert/refund</b> the collected payments before clearing or unassigning fee heads.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Student Information Card -->
                <div class="p-3 mb-3 rounded shadow-xs" style="background: #f8fafc; border: 1.5px solid #e2e8f0; font-size: 13px;">
                    <div class="row align-items-center">
                        <div class="col-sm-6 mb-1">
                            <span class="text-muted"><i class="fa fa-user mr-1 text-primary"></i> Student:</span>
                            <strong class="text-dark ml-1" id="collected_warn_student_name">-</strong>
                        </div>
                        <div class="col-sm-6 mb-1 text-sm-right">
                            <span class="text-muted"><i class="fa fa-id-card mr-1 text-secondary"></i> Admission No:</span>
                            <strong class="badge badge-secondary px-2 py-1 ml-1" id="collected_warn_admission_no">-</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted"><i class="fa fa-graduation-cap mr-1 text-info"></i> Class / Batch:</span>
                            <span class="font-weight-bold text-dark ml-1" id="collected_warn_class_batch">-</span>
                        </div>
                        <div class="col-sm-6 text-sm-right">
                            <span class="text-muted"><i class="fa fa-money mr-1 text-success"></i> Total Collected:</span>
                            <span class="badge badge-success px-2 py-1 font-weight-bold ml-1" style="font-size: 13px;" id="collected_warn_total_paid">₹0</span>
                        </div>
                    </div>
                </div>

                <!-- Collected Receipts / Invoices Table -->
                <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13px;">
                    <i class="fa fa-file-text-o text-primary mr-1"></i> Collected Fee Receipts & Invoices:
                </h6>
                <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
                    <table class="table table-bordered table-sm table-striped mb-0 text-center table-collected-invoices" style="font-size: 12px; width: 100%;">
                        <thead style="position: sticky; top: 0; z-index: 10; background-color: #002c54;">
                            <tr>
                                <th style="width: 40px; color: #ffffff !important; background-color: #002c54 !important;">#</th>
                                <th style="color: #ffffff !important; background-color: #002c54 !important;">Invoice No.</th>
                                <th class="text-left" style="color: #ffffff !important; background-color: #002c54 !important;">Fee Head(s)</th>
                                <th style="color: #ffffff !important; background-color: #002c54 !important;">Paid Amount</th>
                                <th style="color: #ffffff !important; background-color: #002c54 !important;">Date & Mode</th>
                                <th style="width: 110px; color: #ffffff !important; background-color: #002c54 !important;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="collected_warn_invoices_tbody">
                            <!-- Invoices loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer (Crisp Light Background & High-Contrast Solid Buttons) -->
            <div class="modal-footer py-2.5 px-4 d-flex justify-content-between border-top" style="background-color: #f1f5f9 !important; border-top: 1.5px solid #cbd5e1 !important;">
                <a href="{{ url('Fees/add') }}" id="btn_goto_fee_collect" target="_blank" class="btn btn-sm btn-danger font-weight-bold px-3 py-2 shadow-sm d-inline-flex align-items-center" style="background-color: #dc2626 !important; border-color: #dc2626 !important; color: #ffffff !important; border-radius: 6px; font-size: 13px; text-decoration: none;">
                    <i class="fa fa-arrow-circle-right mr-1.5" style="font-size: 14px;"></i> Go to Fee Revert / Collect Form
                </a>
                <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-4 py-2 btn-close-modal shadow-sm" data-dismiss="modal" data-bs-dismiss="modal" style="background-color: #475569 !important; border-color: #475569 !important; color: #ffffff !important; border-radius: 6px; font-size: 13px;">
                    <i class="fa fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var searchTimer = null;

function renderStudentFeeSummaryHtml(admissionId, gross, discount, net) {
    gross = parseFloat(gross) || 0;
    discount = parseFloat(discount) || 0;
    net = (net !== undefined && net !== null) ? parseFloat(net) : Math.max(0, gross - discount);
    
    var grossFormatted = '₹' + (gross % 1 === 0 ? gross.toLocaleString('en-IN') : gross.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var discFormatted = '₹' + (discount % 1 === 0 ? discount.toLocaleString('en-IN') : discount.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var netFormatted = '₹' + (net % 1 === 0 ? net.toLocaleString('en-IN') : net.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    
    var html = '<div class="fee-summary-cell d-flex flex-column align-items-center justify-content-center" id="student_fee_summary_' + admissionId + '" style="gap: 2px;">' +
        '<div class="text-dark font-weight-bold" style="font-size: 13.5px; line-height: 1.2;" title="Total Gross Assigned Fee">' +
            '<span class="text-muted" style="font-size: 11.5px; font-weight: 600;">Total:</span> ' + grossFormatted +
        '</div>';
    
    if (discount > 0) {
        html += '<div class="badge badge-warning text-dark px-2 py-1" style="font-size: 12px; font-weight: 700; border-radius: 4px;" title="Total Discount Given">' +
            '<i class="fa fa-tag text-danger mr-1"></i> Disc: -' + discFormatted +
        '</div>';
    } else {
        html += '<div class="text-muted font-weight-bold" style="font-size: 11.5px; line-height: 1.2;">' +
            'Disc: ₹0' +
        '</div>';
    }
    
    html += '<div class="badge badge-success px-2 py-1 font-weight-bold shadow-xs" style="font-size: 13.5px; letter-spacing: 0.3px; border-radius: 4px;" title="Net Payable Fee" id="student_net_' + admissionId + '">' +
        'Net: ' + netFormatted +
    '</div>' +
    '</div>';
    
    return html;
}

function updateStudentFeeSummary(admissionId, gross, discount, net) {
    var $td = $('#student_summary_td_' + admissionId);
    if ($td.length > 0) {
        $td.html(renderStudentFeeSummaryHtml(admissionId, gross, discount, net));
    } else {
        $('#student_total_' + admissionId).text('₹' + parseFloat(gross || 0).toLocaleString('en-IN'));
    }
}

function showFeeToast(msg, type = 'success') {
    var bgClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    var toastHtml = '<div class="alert ' + bgClass + ' alert-dismissible shadow-sm py-2 px-3 mb-2" style="font-size: 12.5px; border-radius: 6px;">' +
        '<button type="button" class="close" data-dismiss="alert" style="font-size: 16px;">&times;</button>' +
        '<i class="fa ' + icon + ' mr-1"></i> ' + msg +
        '</div>';
    
    var $toast = $(toastHtml).appendTo('#fee_toast_box');
    setTimeout(function() {
        $toast.fadeOut(400, function() { $(this).remove(); });
    }, 3500);
}

function updateSelectedHeadsBadge() {
    var selectedCount = $('.top-head-checkbox:checked').length;
    var checkedStudentsCount = $('.student_select_checkbox:checked').length;

    if (selectedCount > 0) {
        $('#selected_heads_count').text(selectedCount);
        $('#selected_heads_badge').show();
        $('#btn_bulk_assign_realtime').html('<i class="fa fa-check-circle mr-1"></i> Assign (' + selectedCount + ') Selected Head(s) to Checked (<span class="selected_students_count_text">' + checkedStudentsCount + '</span>)');
    } else {
        $('#selected_heads_badge').hide();
        $('#btn_bulk_assign_realtime').html('<i class="fa fa-check-circle mr-1"></i> Assign to Selected Students (<span class="selected_students_count_text">' + checkedStudentsCount + '</span>)');
    }
}

function updateSelectedCount() {
    var checked = $('.student_select_checkbox:checked').length;
    var total = $('.student_select_checkbox').length;
    
    $('#selected_students_badge').text('Selected: ' + checked);
    $('.selected_students_count_text').text(checked);
    $('#total_students_badge').text('Total: ' + total);
    $('.total_students_count_text').text(total);
    
    if (total > 0 && checked === total) {
        $('#all_students').prop('checked', true);
    } else {
        $('#all_students').prop('checked', false);
    }

    updateSelectedHeadsBadge();
}

function loadStudents() {
    var course_id = $('#filter_course_id').val();
    var batch = $('#filter_batch').val();
    var admissionNo = $('#filter_admission_no').val();
    
    if ((!course_id || !batch) && !admissionNo) {
        var msg = 'Please select both <b>Course</b> and <b>Batch</b> above to view and assign students.';
        if (!course_id && !batch) {
            msg = 'Please select a <b>Course</b> and <b>Batch</b> above to view students.';
        } else if (!course_id) {
            msg = 'Please select a <b>Course</b> above.';
        } else if (!batch) {
            msg = 'Please select a <b>Batch</b> above to load student list.';
        }
        $('#tbody_students_list').html('<tr><td colspan="10" class="text-center py-5 text-muted"><i class="fa fa-filter fa-2x mb-2 text-secondary d-block"></i> ' + msg + '</td></tr>');
        updateSelectedCount();
        return;
    }
    
    $('#tbody_students_list').html('<tr><td colspan="10" class="text-center py-5 text-primary"><i class="fa fa-spinner fa-spin fa-2x"></i><br><span class="mt-2 d-block font-weight-bold" style="font-size: 12.5px;">Loading students and course fee structure...</span></td></tr>');
    
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: "{{ url('getStudentsList') }}",
        method: 'POST',
        data: {
            course_id: course_id,
            batch: batch,
            admissionNo: admissionNo
        },
        success: function(response) {
            $('#tbody_students_list').html(response);
            updateSelectedCount();
        },
        error: function(xhr) {
            $('#tbody_students_list').html('<tr><td colspan="10" class="text-center text-danger py-5"><i class="fa fa-exclamation-triangle mr-1"></i> Error loading students list.</td></tr>');
            updateSelectedCount();
        }
    });
}

function loadFeeMasterHeads(course_id, batch) {
    if (!course_id || !batch) {
        $('#course_fee_structure_card').slideUp(200);
        $('#course_fee_structure_chips_box').empty();
        return;
    }

    var courseName = $('#filter_course_id option:selected').text().trim() || 'Course';
    $('#structure_course_batch_label').text(courseName + ' - ' + batch);

    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: "{{ url('getMasterData') }}",
        method: 'POST',
        data: { 
            course_id: course_id,
            batch: batch 
        },
        success: function(response) {
            if (response && response.length > 0) {
                var totalStructureAmt = 0;
                var html = [];
                for (var i = 0; i < response.length; i++) {
                    var item = response[i];
                    var amt = parseFloat(item.amount || 0);
                    totalStructureAmt += amt;
                    var className = item.class_name ? ' <small class="text-muted font-weight-normal">(' + item.class_name + ')</small>' : '';
                    html.push(
                        '<label class="top-structure-head-chip" id="top_head_chip_' + item.id + '" data-master-id="' + item.id + '" title="Click checkbox to select this fee head for custom assignment">' +
                            '<input type="checkbox" class="top-head-checkbox" value="' + item.id + '" data-name="' + item.fees_group_name + '" style="cursor: pointer; margin-right: 5px;">' +
                            '<span class="head-title font-weight-bold text-dark">' + item.fees_group_name + className + '</span>' +
                            '<span class="badge-amt">₹' + amt.toLocaleString('en-IN') + '</span>' +
                        '</label>'
                    );
                }
                $('#course_fee_structure_chips_box').html(html.join(''));
                $('#structure_head_count').text(response.length);
                $('#structure_total_fee').text('₹' + totalStructureAmt.toLocaleString('en-IN'));
                $('#course_fee_structure_card').slideDown(200);
                updateSelectedHeadsBadge();
            } else {
                $('#course_fee_structure_card').slideUp(200);
                $('#course_fee_structure_chips_box').empty();
            }
        },
        error: function() {
            $('#course_fee_structure_card').slideUp(200);
            $('#course_fee_structure_chips_box').empty();
        }
    });
}

$(document).ready(function() {
    $('.select2').select2();

    // Top structure head checkbox change
    $(document).on('change', '.top-head-checkbox', function() {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            $(this).closest('.top-structure-head-chip').addClass('selected-top-chip');
        } else {
            $(this).closest('.top-structure-head-chip').removeClass('selected-top-chip');
        }
        updateSelectedHeadsBadge();
    });

    // Select All Heads in top structure toolbar
    $('#btn_top_select_all_heads').click(function(e) {
        e.preventDefault();
        $('.top-head-checkbox').prop('checked', true);
        $('.top-structure-head-chip').addClass('selected-top-chip');
        updateSelectedHeadsBadge();
    });

    // Deselect All Heads in top structure toolbar
    $('#btn_top_deselect_all_heads').click(function(e) {
        e.preventDefault();
        $('.top-head-checkbox').prop('checked', false);
        $('.top-structure-head-chip').removeClass('selected-top-chip');
        updateSelectedHeadsBadge();
    });

    function syncAssignUrlParams() {
        var params = new URLSearchParams();
        var courseId = $('#filter_course_id').val();
        var batch = $('#filter_batch').val();
        var admissionNo = $('#filter_admission_no').val();

        if (courseId) params.set('course_id', courseId);
        if (batch) params.set('batch', batch);
        if (admissionNo && admissionNo.trim()) params.set('admissionNo', admissionNo.trim());

        var queryString = params.toString();
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.history.replaceState({}, '', newUrl);
    }

    // Course Select Handler
    $('#filter_course_id').change(function() {
        var courseId = $(this).val();
        var batch = $('#filter_batch').val();
        syncAssignUrlParams();
        if (courseId && batch) {
            loadFeeMasterHeads(courseId, batch);
        } else {
            $('#course_fee_structure_card').slideUp(200);
            $('#course_fee_structure_chips_box').empty();
        }
        loadStudents();
    });

    // Batch Select Handler
    $('#filter_batch').change(function() {
        var batch = $(this).val();
        var courseId = $('#filter_course_id').val();
        syncAssignUrlParams();
        if (courseId && batch) {
            loadFeeMasterHeads(courseId, batch);
        } else {
            $('#course_fee_structure_card').slideUp(200);
            $('#course_fee_structure_chips_box').empty();
        }
        loadStudents();
    });

    // Fast Debounced Student Search
    $('#filter_admission_no').on('input', function() {
        syncAssignUrlParams();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            loadStudents();
        }, 350);
    });

    $('#btn_search_students').click(function() {
        syncAssignUrlParams();
        loadStudents();
    });

    // Reset Filters
    $('#btn_clear_filters').click(function() {
        $('#filter_course_id').val('').trigger('change.select2');
        $('#filter_batch').val('').trigger('change.select2');
        $('#filter_admission_no').val('');
        window.history.replaceState({}, '', window.location.pathname);
        $('#course_fee_structure_card').slideUp(200);
        $('#course_fee_structure_chips_box').empty();
        $('#tbody_students_list').html('<tr><td colspan="10" class="text-center py-5 text-muted"><i class="fa fa-filter fa-2x mb-2 text-secondary d-block"></i> Please select both <b>Course</b> and <b>Batch</b> above.</td></tr>');
        updateSelectedCount();
    });

    // Auto-load if filters were present in URL or $serach
    var urlParams = new URLSearchParams(window.location.search);
    var urlCourseId = urlParams.get('course_id') || '{{ $serach["course_id"] ?? "" }}';
    var urlBatch = urlParams.get('batch') || '{{ $serach["batch"] ?? "" }}';
    var urlAdmissionNo = urlParams.get('admissionNo') || urlParams.get('admission_no') || '{{ $serach["admissionNo"] ?? "" }}';

    if (urlCourseId && $('#filter_course_id').val() != urlCourseId) {
        $('#filter_course_id').val(urlCourseId).trigger('change.select2');
    }
    if (urlBatch && $('#filter_batch').val() != urlBatch) {
        $('#filter_batch').val(urlBatch).trigger('change.select2');
    }
    if (urlAdmissionNo && !$('#filter_admission_no').val()) {
        $('#filter_admission_no').val(urlAdmissionNo);
    }

    var activeCourseId = $('#filter_course_id').val();
    var activeBatch = $('#filter_batch').val();
    var activeAdm = $('#filter_admission_no').val();

    if ((activeCourseId && activeBatch) || activeAdm) {
        if (activeCourseId && activeBatch) {
            loadFeeMasterHeads(activeCourseId, activeBatch);
        }
        loadStudents();
    }

    // Select All / Deselect All Students Checkbox
    $('#all_students').click(function() {
        var isChecked = $(this).prop('checked');
        $('.student_select_checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });

    $('#btn_quick_select_all').click(function() {
        $('.student_select_checkbox').prop('checked', true);
        $('#all_students').prop('checked', true);
        updateSelectedCount();
    });

    $('#btn_quick_deselect_all').click(function() {
        $('.student_select_checkbox').prop('checked', false);
        $('#all_students').prop('checked', false);
        updateSelectedCount();
    });

    $(document).on('change', '.student_select_checkbox', function() {
        updateSelectedCount();
    });

    // REAL-TIME INDIVIDUAL FEE HEAD TOGGLE
    $(document).on('change', '.toggle-fee-head-checkbox', function(e) {
        var $checkbox = $(this);
        var admissionId = $checkbox.data('admission-id');
        var masterId = $checkbox.data('master-id');
        var isChecked = $checkbox.is(':checked') ? 1 : 0;
        var $chip = $('#chip_' + admissionId + '_' + masterId);
        var $icon = $('#icon_' + admissionId + '_' + masterId);

        $chip.css('opacity', '0.6');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('toggleStudentFeeHead') }}",
            method: 'POST',
            data: {
                admission_id: admissionId,
                fees_master_id: masterId,
                state: isChecked
            },
            success: function(res) {
                $chip.css('opacity', '1');
                if (res.status === 'success') {
                    if (res.action === 'assigned') {
                        $chip.removeClass('chip-unassigned').addClass('chip-assigned');
                        $icon.removeClass('d-none');
                        $checkbox.prop('checked', true);
                    } else {
                        $chip.removeClass('chip-assigned').addClass('chip-unassigned');
                        $icon.addClass('d-none');
                        $checkbox.prop('checked', false);
                    }
                    
                    if (res.total_amount !== undefined) {
                        updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                    }
                    showFeeToast(res.message, 'success');
                } else {
                    $checkbox.prop('checked', !isChecked);
                    showFeeToast(res.message || 'Error updating fee head', 'error');
                }
            },
            error: function(xhr) {
                $chip.css('opacity', '1');
                $checkbox.prop('checked', !isChecked);
                showFeeToast('Server error while updating fee head', 'error');
            }
        });
    });

    // GLOBAL PENDING ACTION STATE FOR CUSTOM CONFIRMATION MODAL
    var pendingFeeAction = {
        type: null, // 'assign_all_student', 'clear_all_student', 'quick_apply_loaded', 'bulk_assign_selected'
        admissionId: null,
        admissionIds: [],
        studentName: '',
        admissionNo: '',
        totalHeads: 0,
        masterIds: []
    };

    // TRIGGER MODAL: ASSIGN ALL COURSE HEADS TO SINGLE STUDENT
    $(document).on('click', '.btn-student-assign-all-heads', function(e) {
        e.preventDefault();
        var admissionId = $(this).data('admission-id');
        var studentName = $(this).data('student-name') || 'Selected Student';
        var admissionNo = $(this).data('admission-no') || '-';
        var totalHeads = $(this).data('total-heads') || 'all';

        pendingFeeAction = {
            type: 'assign_all_student',
            admissionId: admissionId,
            admissionIds: [admissionId],
            studentName: studentName,
            admissionNo: admissionNo,
            totalHeads: totalHeads,
            masterIds: []
        };

        // Configure Modal for Assign All
        $('#feeConfirmModalHeader').css('border-bottom', '2px solid #28a745');
        $('#feeConfirmIcon').attr('class', 'fa fa-check-circle mr-2 text-success');
        $('#feeConfirmTitle').text('Assign All Course Fee Heads');
        $('#feeConfirmBigIcon').attr('class', 'fa fa-check-square-o fa-2x text-success');
        $('#feeConfirmIconWrapper').css('background', 'rgba(40, 167, 69, 0.12)');
        $('#feeConfirmHeadline').text('Assign All Course Heads to Student?');
        
        $('#feeConfirmStudentInfoBox').show();
        $('#feeConfirmStudentNameRow').show();
        $('#feeConfirmStudentAdmRow').show();
        $('#feeConfirmStudentName').text(studentName);
        $('#feeConfirmStudentAdmNo').text(admissionNo);
        $('#feeConfirmExtraDetails').html('<span class="text-success font-weight-bold">Assign all (' + totalHeads + ') fee heads in real-time</span>');
        
        $('#feeConfirmMessage').text('Are you sure you want to assign all course fee structure heads to ' + studentName + '? This will update instantly.');
        $('#feeConfirmPaidWarning').addClass('d-none');
        
        $('#btn_confirm_fee_modal_action')
            .removeClass('btn-danger btn-primary btn-warning')
            .addClass('btn-success')
            .prop('disabled', false)
            .html('<i class="fa fa-check-square-o mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Assign All</span>');

        $('#feeAssignConfirmModal').modal('show');
    });

    // TRIGGER MODAL: CLEAR ALL UNPAID HEADS FOR SINGLE STUDENT (WITH PAYMENT CHECK)
    $(document).on('click', '.btn-student-unassign-all-heads', function(e) {
        e.preventDefault();
        var admissionId = $(this).data('admission-id');
        var studentName = $(this).data('student-name') || 'Selected Student';
        var admissionNo = $(this).data('admission-no') || '-';
        var $row = $('#student_row_' + admissionId);
        var assignedCheckboxes = $row.find('.toggle-fee-head-checkbox:checked');
        
        if (assignedCheckboxes.length === 0) {
            showFeeToast('No fee heads are currently assigned to clear for this student', 'error');
            return;
        }

        var $btn = $(this);
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        // First check if any payment has been collected for this student
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('checkStudentCollectedFees') }}",
            method: 'POST',
            data: { admission_id: admissionId },
            success: function(res) {
                $btn.prop('disabled', false).html(originalBtnHtml);
                
                if (res.status === 'success' && res.has_paid && res.total_paid > 0) {
                    // Populate and show the custom warning modal
                    $('#collected_warn_student_name').text(res.student.name);
                    $('#collected_warn_admission_no').text(res.student.admissionNo);
                    $('#collected_warn_class_batch').text(res.student.class_name + ' (' + res.student.batch + ')');
                    $('#collected_warn_total_paid').text('₹' + parseFloat(res.total_paid).toLocaleString('en-IN'));
                    $('#btn_goto_fee_collect').attr('href', "{{ url('Fees/add') }}?admission_id=" + res.student.id);

                    var tbodyHtml = [];
                    if (res.invoices && res.invoices.length > 0) {
                        res.invoices.forEach(function(inv, idx) {
                            tbodyHtml.push(
                                '<tr>' +
                                    '<td class="align-middle font-weight-bold text-muted">' + (idx + 1) + '</td>' +
                                    '<td class="align-middle"><span class="badge badge-light border text-primary font-weight-bold px-2 py-1" style="font-size: 11.5px;">' + inv.invoice_no + '</span></td>' +
                                    '<td class="align-middle text-left"><strong class="text-dark d-block">' + inv.heads + '</strong></td>' +
                                    '<td class="align-middle font-weight-bold text-success" style="font-size: 13px;">₹' + inv.amount.toLocaleString('en-IN') + '</td>' +
                                    '<td class="align-middle text-dark"><span class="d-block font-weight-600">' + inv.payment_date + '</span><span class="badge badge-secondary px-1.5 py-0.5" style="font-size: 10.5px;">' + inv.payment_mode + '</span></td>' +
                                    '<td class="align-middle">' +
                                        '<a href="' + inv.receipt_url + '" target="_blank" class="btn btn-xs btn-primary font-weight-bold px-2.5 py-1 text-white shadow-xs" style="background-color: #0284c7 !important; border-color: #0284c7 !important; font-size: 11px; border-radius: 4px;" title="Preview / Print Receipt">' +
                                            '<i class="fa fa-eye mr-1"></i> Preview' +
                                        '</a>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                    } else if (res.paid_details && res.paid_details.length > 0) {
                        res.paid_details.forEach(function(pd, idx) {
                            tbodyHtml.push(
                                '<tr>' +
                                    '<td class="align-middle font-weight-bold text-muted">' + (idx + 1) + '</td>' +
                                    '<td class="align-middle font-weight-bold text-secondary">' + (pd.receipt_no || '-') + '</td>' +
                                    '<td class="align-middle text-left"><strong class="text-dark d-block">' + pd.head_name + '</strong></td>' +
                                    '<td class="align-middle font-weight-bold text-success" style="font-size: 13px;">₹' + pd.amount.toLocaleString('en-IN') + '</td>' +
                                    '<td class="align-middle text-dark"><span class="d-block font-weight-600">' + pd.date + '</span><span class="badge badge-secondary px-1.5 py-0.5" style="font-size: 10.5px;">' + pd.payment_mode + '</span></td>' +
                                    '<td class="align-middle">' +
                                        '<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">Collected</span>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                    }

                    $('#collected_warn_invoices_tbody').html(tbodyHtml.join(''));
                    $('#feeCollectedWarningModal').modal('show');
                } else {
                    // No collected fees, proceed with standard confirmation modal
                    pendingFeeAction = {
                        type: 'clear_all_student',
                        admissionId: admissionId,
                        admissionIds: [admissionId],
                        studentName: studentName,
                        admissionNo: admissionNo,
                        totalHeads: assignedCheckboxes.length,
                        masterIds: []
                    };

                    $('#feeConfirmModalHeader').css('border-bottom', '2px solid #dc3545');
                    $('#feeConfirmIcon').attr('class', 'fa fa-trash mr-2 text-danger');
                    $('#feeConfirmTitle').text('Clear All Assigned Fee Heads');
                    $('#feeConfirmBigIcon').attr('class', 'fa fa-trash fa-2x text-danger');
                    $('#feeConfirmIconWrapper').css('background', 'rgba(220, 53, 69, 0.12)');
                    $('#feeConfirmHeadline').text('Clear All Fee Heads for Student?');
                    
                    $('#feeConfirmStudentInfoBox').show();
                    $('#feeConfirmStudentNameRow').show();
                    $('#feeConfirmStudentAdmRow').show();
                    $('#feeConfirmStudentName').text(studentName);
                    $('#feeConfirmStudentAdmNo').text(admissionNo);
                    $('#feeConfirmExtraDetails').html('<span class="text-danger font-weight-bold">Clear (' + assignedCheckboxes.length + ') assigned heads</span>');
                    
                    $('#feeConfirmMessage').text('Are you sure you want to unassign all fee heads for ' + studentName + '?');
                    $('#feeConfirmPaidWarning').addClass('d-none');
                    
                    $('#btn_confirm_fee_modal_action')
                        .removeClass('btn-success btn-primary btn-warning')
                        .addClass('btn-danger')
                        .prop('disabled', false)
                        .html('<i class="fa fa-trash mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Clear All</span>');

                    $('#feeAssignConfirmModal').modal('show');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalBtnHtml);
                showFeeToast('Server error while checking payment status', 'error');
            }
        });
    });

    // TRIGGER MODAL: QUICK APPLY ALL HEADS TO ALL LOADED STUDENTS
    $('#btn_quick_apply_all_loaded').click(function(e) {
        e.preventDefault();
        var allStudentIds = [];
        $('.student_select_checkbox').each(function() {
            allStudentIds.push($(this).val());
        });

        if (allStudentIds.length === 0) {
            showFeeToast('No students loaded in the table. Please select Course and Batch first.', 'error');
            return;
        }

        var count = allStudentIds.length;

        pendingFeeAction = {
            type: 'quick_apply_loaded',
            admissionId: null,
            admissionIds: allStudentIds,
            studentName: '',
            admissionNo: '',
            totalHeads: 0,
            masterIds: []
        };

        // Configure Modal for Quick Apply
        $('#feeConfirmModalHeader').css('border-bottom', '2px solid #ffc107');
        $('#feeConfirmIcon').attr('class', 'fa fa-bolt mr-2 text-warning');
        $('#feeConfirmTitle').text('Quick Apply All Fee Heads');
        $('#feeConfirmBigIcon').attr('class', 'fa fa-bolt fa-2x text-warning');
        $('#feeConfirmIconWrapper').css('background', 'rgba(255, 193, 7, 0.15)');
        $('#feeConfirmHeadline').text('Quick Apply to ALL Loaded Students?');
        
        $('#feeConfirmStudentInfoBox').show();
        $('#feeConfirmStudentNameRow').hide();
        $('#feeConfirmStudentAdmRow').hide();
        $('#feeConfirmExtraDetails').html(
            '<span class="badge badge-primary px-2 py-1 mr-1" style="font-size: 12.5px;">' + count + ' Students Loaded</span>' +
            '<span class="badge badge-warning text-dark px-2 py-1" style="font-size: 12.5px;">ALL Course Fee Heads</span>'
        );
        
        $('#feeConfirmMessage').text('Are you sure you want to assign all course fee structure heads to ALL ' + count + ' students loaded in this batch? This will be applied live in real-time.');
        $('#feeConfirmPaidWarning').addClass('d-none');
        
        $('#btn_confirm_fee_modal_action')
            .removeClass('btn-danger btn-warning btn-primary')
            .addClass('btn-success')
            .prop('disabled', false)
            .html('<i class="fa fa-bolt mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Quick Apply (' + count + ')</span>');

        $('#feeAssignConfirmModal').modal('show');
    });

    // TRIGGER MODAL: BULK ASSIGN TO SELECTED/CHECKED STUDENTS
    $('#btn_bulk_assign_realtime').click(function(e) {
        e.preventDefault();
        var selectedStudents = [];
        $('.student_select_checkbox:checked').each(function() {
            selectedStudents.push($(this).val());
        });

        if (selectedStudents.length === 0) {
            showFeeToast('Please select at least one student checkbox in the table.', 'error');
            return;
        }

        var feeMasterIds = [];
        var feeMasterNames = [];
        $('.top-head-checkbox:checked').each(function() {
            feeMasterIds.push($(this).val());
            feeMasterNames.push($(this).data('name'));
        });

        var count = selectedStudents.length;

        pendingFeeAction = {
            type: 'bulk_assign_selected',
            admissionId: null,
            admissionIds: selectedStudents,
            studentName: '',
            admissionNo: '',
            totalHeads: feeMasterIds.length,
            masterIds: feeMasterIds
        };

        // Configure Modal for Selected Bulk Assign
        $('#feeConfirmModalHeader').css('border-bottom', '2px solid #002c54');
        $('#feeConfirmIcon').attr('class', 'fa fa-users mr-2 text-info');
        $('#feeConfirmBigIcon').attr('class', 'fa fa-users fa-2x text-primary');
        $('#feeConfirmIconWrapper').css('background', 'rgba(0, 44, 84, 0.12)');
        
        $('#feeConfirmStudentInfoBox').show();
        $('#feeConfirmStudentNameRow').hide();
        $('#feeConfirmStudentAdmRow').hide();

        if (feeMasterIds.length > 0) {
            var headsPreview = feeMasterNames.slice(0, 3).join(', ') + (feeMasterNames.length > 3 ? ' +' + (feeMasterNames.length - 3) + ' more' : '');
            $('#feeConfirmTitle').text('Assign Selected Fee Heads (' + feeMasterIds.length + ')');
            $('#feeConfirmHeadline').text('Assign ' + feeMasterIds.length + ' Selected Head(s) to ' + count + ' Student(s)?');
            $('#feeConfirmExtraDetails').html(
                '<span class="badge badge-success px-2 py-1 mr-1" style="font-size: 12.5px;">' + count + ' Students Selected</span>' +
                '<span class="badge badge-info px-2 py-1" style="font-size: 12.5px;">' + feeMasterIds.length + ' Head(s): ' + headsPreview + '</span>'
            );
            $('#feeConfirmMessage').text('Are you sure you want to assign the selected (' + feeMasterIds.length + ') fee head(s) to the ' + count + ' checked student(s) in real-time?');
        } else {
            $('#feeConfirmTitle').text('Assign All Course Fee Heads');
            $('#feeConfirmHeadline').text('Assign ALL Course Heads to ' + count + ' Student(s)?');
            $('#feeConfirmExtraDetails').html(
                '<span class="badge badge-success px-2 py-1 mr-1" style="font-size: 12.5px;">' + count + ' Students Selected</span>' +
                '<span class="badge badge-primary px-2 py-1" style="font-size: 12.5px;">All Course Heads</span>'
            );
            $('#feeConfirmMessage').text('Are you sure you want to assign ALL course fee structure heads to the ' + count + ' checked student(s) in real-time?');
        }
        
        $('#feeConfirmPaidWarning').addClass('d-none');
        
        $('#btn_confirm_fee_modal_action')
            .removeClass('btn-danger btn-warning btn-success')
            .addClass('btn-primary')
            .prop('disabled', false)
            .html('<i class="fa fa-check-circle mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Assign to (' + count + ')</span>');

        $('#feeAssignConfirmModal').modal('show');
    });

    // PROCEED EXECUTION HANDLER FOR CUSTOM CONFIRMATION MODAL
    $('#btn_confirm_fee_modal_action').click(function() {
        var $confirmBtn = $(this);
        $confirmBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing in real-time...');

        if (pendingFeeAction.type === 'assign_all_student') {
            var admissionId = pendingFeeAction.admissionId;
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('bulkAssignCourseFees') }}",
                method: 'POST',
                data: {
                    admissionIds: [admissionId],
                    fees_master_ids: []
                },
                success: function(res) {
                    $('#feeAssignConfirmModal').modal('hide');
                    if (res.status === 'success') {
                        // Real-time update row DOM
                        var $row = $('#student_row_' + admissionId);
                        $row.find('.toggle-fee-head-checkbox').prop('checked', true);
                        $row.find('.fee-head-chip').removeClass('chip-unassigned').addClass('chip-assigned');
                        $row.find('[id^="icon_' + admissionId + '_"]').removeClass('d-none');
                        
                        if (res.updated_totals && res.updated_totals[admissionId] !== undefined) {
                            var g = res.updated_totals[admissionId];
                            var d = res.updated_discounts ? res.updated_discounts[admissionId] : 0;
                            var n = res.updated_nets ? res.updated_nets[admissionId] : (g - d);
                            updateStudentFeeSummary(admissionId, g, d, n);
                        } else if (res.total_amount !== undefined && res.total_amount > 0) {
                            updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                        }
                        showFeeToast(pendingFeeAction.studentName + ': All course fee heads assigned successfully!', 'success');
                    } else {
                        showFeeToast(res.message || 'Error assigning heads', 'error');
                    }
                },
                error: function() {
                    $('#feeAssignConfirmModal').modal('hide');
                    showFeeToast('Server error while assigning fee heads', 'error');
                }
            });
        } 
        else if (pendingFeeAction.type === 'clear_all_student') {
            var admissionId = pendingFeeAction.admissionId;
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('clearStudentFeeHeads') }}",
                method: 'POST',
                data: {
                    admission_id: admissionId
                },
                success: function(res) {
                    $('#feeAssignConfirmModal').modal('hide');
                    if (res.status === 'success') {
                        // Real-time update row DOM
                        var $row = $('#student_row_' + admissionId);
                        $row.find('.toggle-fee-head-checkbox').prop('checked', false);
                        $row.find('.fee-head-chip').removeClass('chip-assigned').addClass('chip-unassigned');
                        $row.find('[id^="icon_' + admissionId + '_"]').addClass('d-none');
                        
                        var g = (res.updated_totals && res.updated_totals[admissionId] !== undefined) ? res.updated_totals[admissionId] : (res.total_amount || 0);
                        var d = (res.updated_discounts && res.updated_discounts[admissionId] !== undefined) ? res.updated_discounts[admissionId] : (res.total_discount || 0);
                        var n = (res.updated_nets && res.updated_nets[admissionId] !== undefined) ? res.updated_nets[admissionId] : (res.net_amount || (g - d));
                        updateStudentFeeSummary(admissionId, g, d, n);
                        
                        showFeeToast(pendingFeeAction.studentName + ': Unpaid fee heads cleared successfully!', 'success');
                    } else {
                        showFeeToast(res.message || 'Error clearing fee heads', 'error');
                    }
                },
                error: function() {
                    $('#feeAssignConfirmModal').modal('hide');
                    showFeeToast('Server error while clearing fee heads', 'error');
                }
            });
        }
        else if (pendingFeeAction.type === 'quick_apply_loaded' || pendingFeeAction.type === 'bulk_assign_selected') {
            var targetIds = pendingFeeAction.admissionIds;
            var masterIds = pendingFeeAction.masterIds;

            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('bulkAssignCourseFees') }}",
                method: 'POST',
                data: {
                    admissionIds: targetIds,
                    fees_master_ids: masterIds
                },
                success: function(res) {
                    $('#feeAssignConfirmModal').modal('hide');
                    if (res.status === 'success') {
                        // Real-time update all affected rows
                        targetIds.forEach(function(admId) {
                            var $row = $('#student_row_' + admId);
                            if (masterIds && masterIds.length > 0) {
                                masterIds.forEach(function(mId) {
                                    $row.find('.toggle-fee-head-checkbox[data-master-id="' + mId + '"]').prop('checked', true);
                                    $('#chip_' + admId + '_' + mId).removeClass('chip-unassigned').addClass('chip-assigned');
                                    $('#icon_' + admId + '_' + mId).removeClass('d-none');
                                });
                            } else {
                                $row.find('.toggle-fee-head-checkbox').prop('checked', true);
                                $row.find('.fee-head-chip').removeClass('chip-unassigned').addClass('chip-assigned');
                                $row.find('[id^="icon_' + admId + '_"]').removeClass('d-none');
                            }

                            if (res.updated_totals && res.updated_totals[admId] !== undefined) {
                                var g = res.updated_totals[admId];
                                var d = res.updated_discounts ? res.updated_discounts[admId] : 0;
                                var n = res.updated_nets ? res.updated_nets[admId] : (g - d);
                                updateStudentFeeSummary(admId, g, d, n);
                            }
                        });
                        showFeeToast(res.message || 'Bulk fee assignment completed successfully!', 'success');
                    } else {
                        showFeeToast(res.message || 'Error during bulk assignment', 'error');
                    }
                },
                error: function() {
                    $('#feeAssignConfirmModal').modal('hide');
                    showFeeToast('Server error during bulk fee assignment', 'error');
                }
            });
        }
    });

    // OPEN STUDENT FEE EDIT / MODIFICATION MODAL
    $(document).on('click', '.btn-edit-student-fees', function() {
        var admissionId = $(this).data('admission-id');
        $('#studentFeeEditModal').modal('show');
        $('#studentFeeEditModalBody').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted font-weight-bold">Loading Student Fee Details...</p></div>');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('getStudentFeeDetailsModal') }}",
            method: 'POST',
            data: { admission_id: admissionId },
            success: function(html) {
                $('#studentFeeEditModalBody').html(html);
            },
            error: function() {
                $('#studentFeeEditModalBody').html('<div class="alert alert-danger m-3"><i class="fa fa-exclamation-triangle mr-1"></i> Failed to load fee details.</div>');
            }
        });
    });

    // MODAL: RECALCULATE SUMMARY TOTALS
    function recalculateModalSummary() {
        var total = 0;
        var discount = 0;
        $('#table_student_fee_edit tbody tr').each(function() {
            var amt = parseFloat($(this).find('.input-amount').val()) || 0;
            var disc = parseFloat($(this).find('.input-discount').val()) || 0;
            var net = Math.max(0, amt - disc);
            total += amt;
            discount += disc;
            var netText = (net % 1 === 0) 
                ? '₹' + net.toLocaleString('en-IN') 
                : '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            $(this).find('.net-display').text(netText);
        });
        var netPayable = Math.max(0, total - discount);
        var totalText = (total % 1 === 0) ? '₹' + total.toLocaleString('en-IN') : '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var discText = (discount % 1 === 0) ? '₹' + discount.toLocaleString('en-IN') : '₹' + discount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var netText = (netPayable % 1 === 0) ? '₹' + netPayable.toLocaleString('en-IN') : '₹' + netPayable.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        $('#modal_summary_total').text(totalText);
        $('#modal_summary_discount').text(discText);
        $('#modal_summary_net').text(netText);
    }

    // MODAL: DISCOUNT TYPE OR BASIS CHANGE
    $(document).on('change', '#modal_discount_type, #modal_discount_basis', function() {
        var type = $('#modal_discount_type').val();
        var basis = $('#modal_discount_basis').val();

        if (type === 'percentage') {
            $('#modal_discount_addon').text('%');
            $('#lbl_discount_input').html('<i class="fa fa-percent text-primary mr-1"></i> Discount %');
            $('#modal_total_discount_value').attr('placeholder', 'e.g. 10').attr('max', '100');
        } else {
            $('#modal_discount_addon').text('₹');
            $('#lbl_discount_input').html('<i class="fa fa-tag text-primary mr-1"></i> Discount (₹)');
            $('#modal_total_discount_value').attr('placeholder', 'e.g. 5000').removeAttr('max');
        }

        if (basis === 'total_assigned') {
            $('#discount_explanation_text').html('Discount will be calculated on <b>Total Assigned Fees</b> and distributed equally among checked heads.');
        } else {
            $('#discount_explanation_text').html('Discount will be calculated on <b>Checked Heads Amount</b> and distributed equally among them.');
        }
    });

    // MODAL: CHECK / UNCHECK ALL HEADS
    $(document).on('change', '#modal_check_all_heads', function() {
        var isChecked = $(this).is(':checked');
        $('.modal-head-select').prop('checked', isChecked);
        updateSelectedHeadsBadge();
    });

    // MODAL: INDIVIDUAL HEAD CHECKBOX CHANGE
    $(document).on('change', '.modal-head-select', function() {
        var allChecked = $('.modal-head-select').length === $('.modal-head-select:checked').length;
        $('#modal_check_all_heads').prop('checked', allChecked);
        updateSelectedHeadsBadge();
    });

    function updateSelectedHeadsBadge() {
        var total = $('.modal-head-select').length;
        var checked = $('.modal-head-select:checked').length;
        $('#modal_selected_heads_count').text(checked + ' of ' + total + ' Heads Selected');
    }

    // MODAL: AUTO ALLOCATE DISCOUNT EQUALLY ACROSS SELECTED HEADS (INTEGER ONLY - NO DECIMALS)
    $(document).on('click', '#btn_auto_allocate_discount', function() {
        var discountType = $('#modal_discount_type').val();
        var discountBasis = $('#modal_discount_basis').val();
        var rawDiscountVal = parseFloat($('#modal_total_discount_value').val());

        if (isNaN(rawDiscountVal) || rawDiscountVal <= 0) {
            showFeeToast('Please enter a valid discount value greater than 0', 'warning');
            $('#modal_total_discount_value').focus();
            return;
        }

        var $checkedBoxes = $('.modal-head-select:checked');
        if ($checkedBoxes.length === 0) {
            showFeeToast('Please select at least one fee head checkbox to allocate discount', 'warning');
            return;
        }

        var totalAmountOfSelected = 0;
        var selectedRows = [];
        $checkedBoxes.each(function() {
            var detailId = $(this).data('detail-id');
            var $row = $('#row_detail_' + detailId);
            var amt = Math.round(parseFloat($row.find('.input-amount').val()) || 0);
            totalAmountOfSelected += amt;
            selectedRows.push({ $row: $row, amt: amt });
        });

        var totalAssignedAll = 0;
        $('#table_student_fee_edit tbody tr').each(function() {
            var amt = Math.round(parseFloat($(this).find('.input-amount').val()) || 0);
            totalAssignedAll += amt;
        });

        var calculationBaseAmount = (discountBasis === 'total_assigned') ? totalAssignedAll : totalAmountOfSelected;
        var totalDiscountAmt = 0;

        if (discountType === 'percentage') {
            var pct = Math.min(100, Math.max(0, rawDiscountVal));
            totalDiscountAmt = Math.round((pct / 100) * calculationBaseAmount);
        } else {
            totalDiscountAmt = Math.round(Math.min(rawDiscountVal, calculationBaseAmount));
        }

        var count = selectedRows.length;
        var baseDiscount = Math.floor(totalDiscountAmt / count);
        var remainder = totalDiscountAmt % count;

        $.each(selectedRows, function(index, item) {
            // First 'remainder' heads get baseDiscount + 1, others get baseDiscount (clean whole integer)
            var allocatedDisc = baseDiscount + (index < remainder ? 1 : 0);
            item.$row.find('.input-discount').val(allocatedDisc);
            var net = Math.max(0, item.amt - allocatedDisc);
            item.$row.find('.net-display').text('₹' + net.toLocaleString('en-IN'));
        });

        recalculateModalSummary();
        var basisLabel = (discountBasis === 'total_assigned') ? 'Total Assigned Fees (₹' + totalAssignedAll.toLocaleString('en-IN') + ')' : 'Checked Heads (₹' + totalAmountOfSelected.toLocaleString('en-IN') + ')';
        showFeeToast('₹' + totalDiscountAmt.toLocaleString('en-IN') + ' discount (on ' + basisLabel + ') allocated across ' + count + ' heads!', 'success');
    });

    // MODAL: RESET / CLEAR ALL DISCOUNTS
    $(document).on('click', '#btn_reset_discount', function() {
        var $checkedBoxes = $('.modal-head-select:checked');
        if ($checkedBoxes.length > 0) {
            $checkedBoxes.each(function() {
                var detailId = $(this).data('detail-id');
                var $row = $('#row_detail_' + detailId);
                $row.find('.input-discount').val(0);
            });
        } else {
            $('.input-discount').val(0);
        }
        recalculateModalSummary();
        showFeeToast('Discounts cleared', 'info');
    });

    // MODAL: LIVE CALCULATION ON INPUT CHANGE
    $(document).on('input', '.input-amount, .input-discount', function() {
        var detailId = $(this).data('detail-id');
        var $row = $('#row_detail_' + detailId);
        var amt = parseFloat($row.find('.input-amount').val()) || 0;
        var disc = parseFloat($row.find('.input-discount').val()) || 0;
        var net = Math.max(0, amt - disc);
        $row.find('.net-display').text('₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        recalculateModalSummary();
    });

    // MODAL: SAVE INDIVIDUAL ROW VIA AJAX
    $(document).on('click', '.btn-save-detail-row', function() {
        var detailId = $(this).data('detail-id');
        var admissionId = $(this).data('admission-id');
        var $row = $('#row_detail_' + detailId);
        var $btn = $(this);

        var amount = $row.find('.input-amount').val();
        var discount = $row.find('.input-discount').val();
        var dueDate = $row.find('.input-due-date').val();
        var fine = $row.find('.input-fine').val();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('updateStudentFeeDetailInline') }}",
            method: 'POST',
            data: {
                detail_id: detailId,
                amount: amount,
                discount: discount,
                due_date: dueDate,
                fine: fine
            },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-check text-success"></i> Saved');
                setTimeout(function() {
                    $btn.html('<i class="fa fa-save"></i> Save');
                }, 2000);

                if (res.status === 'success') {
                    if (res.total_amount !== undefined) {
                        $('#modal_summary_total').text('₹' + parseFloat(res.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    if (res.total_discount !== undefined) {
                        $('#modal_summary_discount').text('₹' + parseFloat(res.total_discount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    if (res.net_amount !== undefined) {
                        $('#modal_summary_net').text('₹' + parseFloat(res.net_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                    showFeeToast(res.message || 'Fee updated successfully', 'success');
                } else {
                    showFeeToast(res.message || 'Error updating fee', 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                showFeeToast('Server error while saving fee detail', 'error');
            }
        });
    });

    // MODAL: SAVE ALL DETAILS AT ONCE (BATCH SAVE FOR TOP & BOTTOM BUTTONS)
    $(document).on('click', '.btn-save-all-batch, #btn_save_all_modal_details', function() {
        var admissionId = $(this).data('admission-id');
        var $allBtns = $('.btn-save-all-batch, #btn_save_all_modal_details');
        var details = [];

        $('#table_student_fee_edit tbody tr').each(function() {
            var detailId = $(this).find('.input-amount').data('detail-id');
            if (detailId) {
                details.push({
                    id: detailId,
                    amount: $(this).find('.input-amount').val(),
                    discount: $(this).find('.input-discount').val(),
                    due_date: $(this).find('.input-due-date').val(),
                    fine: $(this).find('.input-fine').val()
                });
            }
        });

        if (details.length === 0) {
            showFeeToast('No fee records found to save', 'warning');
            return;
        }

        $allBtns.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('updateStudentFeeDetailsBatch') }}",
            method: 'POST',
            data: {
                admission_id: admissionId,
                details: details
            },
            success: function(res) {
                $allBtns.prop('disabled', false).html('<i class="fa fa-check text-white mr-1"></i> All Saved');
                setTimeout(function() {
                    $('.btn-save-all-batch').html('<i class="fa fa-check-circle mr-1"></i> Save All');
                    $('#btn_save_all_modal_details').html('<i class="fa fa-check-circle mr-1"></i> Save All Changes');
                }, 2500);

                if (res.status === 'success') {
                    if (res.total_amount !== undefined) {
                        var tot = parseFloat(res.total_amount);
                        $('#modal_summary_total').text('₹' + (tot % 1 === 0 ? tot.toLocaleString('en-IN') : tot.toLocaleString('en-IN', {minimumFractionDigits: 2})));
                    }
                    if (res.total_discount !== undefined) {
                        var disc = parseFloat(res.total_discount);
                        $('#modal_summary_discount').text('₹' + (disc % 1 === 0 ? disc.toLocaleString('en-IN') : disc.toLocaleString('en-IN', {minimumFractionDigits: 2})));
                    }
                    if (res.net_amount !== undefined) {
                        var net = parseFloat(res.net_amount);
                        $('#modal_summary_net').text('₹' + (net % 1 === 0 ? net.toLocaleString('en-IN') : net.toLocaleString('en-IN', {minimumFractionDigits: 2})));
                    }
                    updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                    showFeeToast('All student fee changes saved successfully!', 'success');
                } else {
                    showFeeToast(res.message || 'Error updating fees', 'error');
                }
            },
            error: function() {
                $('.btn-save-all-batch').prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Save All');
                $('#btn_save_all_modal_details').prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Save All Changes');
                showFeeToast('Server error while saving fee details', 'error');
            }
        });
    });

    // ROBUST GLOBAL MODAL DISMISS / CANCEL / CLOSE HANDLER
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .close', function(e) {
        var $modal = $(this).closest('.modal');
        if ($modal.length > 0) {
            $modal.modal('hide');
        }
    });
});
</script>

@endsection
