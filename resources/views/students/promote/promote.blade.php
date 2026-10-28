@php
    $classType = Helper::classType($search['course'] ?? null, true);
    if (!empty($classType)) {
        $classType = $classType->unique('orderBy')->values();
    }
    $allClassType = Helper::classType(null, true);
    $courses = Helper::getCourses();
    $batches = Helper::getBatch();
    $getAttendanceStatus = Helper::getAttendanceStatus();
    $masterFeesGroups = \App\Models\FeesGroup::where('branch_id', Session::get('branch_id'))
        ->whereNull('deleted_at')
        ->orderBy('name', 'ASC')
        ->get()
        ->unique('name');
@endphp
@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   EXCEL SPREADSHEET THEME (Matching https://girraj.test/feesGroup)
   ======================================================== */
.content-wrapper {
    background-color: #f4f6f9;
}

.fees-assign-page-card {
    margin-bottom: 0 !important;
    border: 1px solid #9aa7b1 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

.select2-container.select2-locked .select2-selection {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    cursor: not-allowed !important;
    opacity: 0.9;
}
.select2-container.select2-locked .select2-selection__arrow {
    display: none !important;
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
    display: flex !important;
    align-items: center;
}
.fees-assign-page-card > .card-header .card-tools {
    margin-left: auto !important;
    margin-right: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
}

/* Header Buttons */
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
}
.btn-header-light:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.fees-assign-page-card .card-body {
    padding: 0 !important;
}

/* ========================================================
   UNIFIED EXCEL FILTER TOOLBAR (Single Compact Toolbar)
   ======================================================== */
.unified-filter-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
    padding: 8px 12px;
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

/* Buttons in Toolbar */
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
.btn-filter-reset {
    height: 29px !important;
    padding: 3px 10px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
    border: 1px solid #94a3b8 !important;
    color: #475569 !important;
    border-radius: 2px !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
}
.btn-filter-reset:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
}

/* Select2 Compact Adjustments */
.select2-container .select2-selection--single {
    height: 29px !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 2px !important;
    padding: 1px 4px !important;
    font-size: 11.5px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
    padding-left: 4px !important;
    color: #172033 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 27px !important;
}

/* ========================================================
   PROMOTION TARGET CONFIGURATION BOX (Excel Styled Panel)
   ======================================================== */
.promotion-target-panel {
    background: #f1f5f9;
    border-bottom: 2px solid #cbd5e1;
    padding: 10px 12px;
}
.promotion-mode-option {
    display: flex;
    align-items: center;
    cursor: pointer;
    border: 1px solid #cbd5e1;
    border-radius: 2px;
    padding: 6px 10px;
    background: #ffffff;
    margin-bottom: 0;
    transition: all 0.15s ease;
}
.promotion-mode-option.active {
    border-color: #217346;
    background: #f0fdf4;
    box-shadow: 0 0 0 1px #217346;
}
.promotion-mode-option input[type="radio"] {
    margin-right: 6px;
    cursor: pointer;
}

/* Checkbox Style */
.checkbox-excel {
    width: 15px;
    height: 15px;
    vertical-align: middle;
    cursor: pointer;
    accent-color: #217346;
}

@keyframes pulseHeadHighlight {
    0% { transform: scale(0.96); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}
.new-head-pulse {
    animation: pulseHeadHighlight 1.8s cubic-bezier(0.4, 0, 0.6, 1) 2;
    background: #ecfdf5 !important;
    border-color: #10b981 !important;
    color: #065f46 !important;
    font-weight: 700 !important;
}

/* ========================================================
   EXCEL SPREADSHEET TABLE
   ======================================================== */
.promote-sheet-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100% !important;
    margin-bottom: 0 !important;
}
.promote-sheet-table > thead > tr > th {
    background: #d9eaf7 !important;
    color: #152536 !important;
    border-color: #8fa7b8 !important;
    font-size: 11.5px !important;
    height: 33px;
    font-weight: 700 !important;
    padding: 5px 8px !important;
    vertical-align: middle !important;
    border-right: 1px solid #8fa7b8;
    border-bottom: 1px solid #8fa7b8;
}
.promote-sheet-table > tbody > tr > td {
    background: #ffffff;
    border-color: #c6cdd3 !important;
    border-right: 1px solid #c6cdd3;
    border-bottom: 1px solid #c6cdd3;
    padding: 4px 8px !important;
    font-size: 11.5px !important;
    vertical-align: middle !important;
    line-height: 1.25;
}
.promote-sheet-table > tbody > tr:nth-child(even) > td {
    background: #fafafa;
}
.promote-sheet-table > tbody > tr:hover > td {
    background: #fffde7 !important; /* Excel Yellow Hover */
}

/* Chips & Badges */
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

/* Promote Action Bar Bottom */
.promote-bottom-bar {
    background: #e2f0d9;
    border-top: 2px solid #9eb58f;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.btn-promote-submit {
    height: 32px;
    padding: 4px 16px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    background: #217346 !important;
    border: 1px solid #185c37 !important;
    color: #ffffff !important;
    border-radius: 2px !important;
    transition: all 0.15s ease-in-out;
}
.btn-promote-submit:hover {
    background: #185c37 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

/* ========================================================
   MODAL THEME (Matching feesGroup layout)
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
    padding: 8px 12px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
}
</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            <div class="card fees-assign-page-card">
                <!-- Excel Green Ribbon Header -->
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-graduation-cap mr-2"></i> {{ __('Promote Students') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ url('feesGroup') }}" class="btn-header-light">
                            <i class="fa fa-table mr-1"></i> Fee Master
                        </a>
                        <a href="{{ url('fees_assign') }}" class="btn-header-light">
                            <i class="fa fa-money mr-1"></i> Fee Assign
                        </a>
                        <a href="{{ url('studentsDashboard') }}" class="btn-header-light">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- Unified Filter Toolbar (Step 1: Source Student Filter) -->
                <div class="unified-filter-toolbar">
                    <form id="quickForm" action="{{ url('student/promote_add') }}" method="post">
                        @csrf 
                        <div class="row align-items-end g-2">
                            <div class="col-md-3 col-6">
                                <label class="toolbar-label text-danger font-weight-bold">{{ __('Course') }} *</label>
                                <select class="toolbar-control select2" id="search_course" name="course">
                                    <option value="">-- Select Course --</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $course)
                                            <option value="{{ $course->name ?? '' }}" {{ ($search['course'] ?? '') == $course->name ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="toolbar-label text-danger font-weight-bold">{{ __('Batch') }} *</label>
                                <select class="toolbar-control select2" id="search_batch" name="batch">
                                    <option value="">-- Select Batch --</option>
                                    @if(!empty($batches))
                                        @foreach($batches as $b)
                                            <option value="{{ $b->name ?? '' }}" {{ ($search['batch'] ?? '') == $b->name ? 'selected' : '' }}>{{ $b->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="toolbar-label">{{ __('Class / Semester') }} <span class="badge badge-light border text-muted" id="badgeSearchClassStatus" style="font-size: 8.5px; font-weight: 600; display: none;">Latest</span></label>
                                <select class="toolbar-control select2" id="search_class_type_id" name="class_type_id">
                                    <option value="">-- All Semesters --</option>
                                    @if(!empty($classType)) 
                                        @foreach($classType as $type)
                                            <option value="{{ $type->id ?? '' }}" {{ ($search['class_type_id'] ?? '') == $type->id ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="toolbar-label">{{ __('Status') }}</label>
                                <select class="toolbar-control select2" id="search_status" name="status">
                                    <option value="1" {{ ($search['status'] ?? '1') === '1' ? 'selected' : '' }}>Active Students</option>
                                    <option value="all" {{ ($search['status'] ?? '') === 'all' ? 'selected' : '' }}>All (Active + Inactive)</option>
                                    <option value="0" {{ ($search['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive Students</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="toolbar-label">{{ __('Keywords / Adm No') }}</label>
                                <input type="text" class="toolbar-control" id="name" name="name" placeholder="Name, Mobile, Adm No..." value="{{ $search['name'] ?? ($search['admissionNo'] ?? '') }}">
                            </div>
                            <div class="col-md-1 col-6 d-flex" style="gap: 4px;">
                                <button type="submit" class="btn-search-action flex-grow-1" id="btnFilterSubmit" title="Filter Students">
                                    <i class="fa fa-search"></i>
                                </button>
                                <a href="{{ url('student/promote_add') }}" class="btn-filter-reset" title="Reset Filters">
                                    <i class="fa fa-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Promotion Target & Selection Form -->
                <form id="promoteForm" action="{{ url('studentsPromoteAdd') }}" method="post">
                    @csrf 

                    <!-- Hidden inputs to ensure disabled selects submit their values -->
                    <input type="hidden" id="hidden_promote_course" name="promote_course" value="{{ $search['course'] ?? '' }}">
                    <input type="hidden" id="hidden_promote_batch" name="promote_batch" value="{{ $search['batch'] ?? '' }}">

                    <!-- Compact Promotion Target Configuration Panel -->
                    <div class="promotion-target-panel">
                        <div class="row align-items-end g-2">
                            <div class="col-md-3 col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="toolbar-label text-danger mb-0">{{ __('Promote To Course') }} *</label>
                                    <span class="badge badge-light border text-muted" id="badgeCourseLocked" role="button" title="Auto-synced with selected Course. Click to toggle lock/unlock." style="font-size: 9px; cursor: pointer;"><i class="fa fa-lock mr-1"></i>Auto</span>
                                </div>
                                <select class="toolbar-control select2" id="promote_course" disabled required>
                                    <option value="">-- Select Target Course --</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $c)
                                            <option value="{{ $c->name ?? '' }}" {{ ($search['course'] ?? '') == $c->name ? 'selected' : '' }}>{{ $c->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="toolbar-label text-danger mb-0">{{ __('Promote To Class / Semester') }} *</label>
                                    <span class="badge border text-success" id="badgeNextSem" style="font-size: 9px; background: #ecfdf5; border-color: #a7f3d0;"><i class="fa fa-arrow-right mr-1"></i>Next Sem</span>
                                </div>
                                <select class="toolbar-control select2" id="promote_class_type_id" name="promote_class_type_id" required>
                                    <option value="">-- Select Target Semester --</option>
                                    @if(!empty($classType))
                                        @foreach($classType as $promoteClass)
                                            <option value="{{ $promoteClass->id ?? '' }}">{{ $promoteClass->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="toolbar-label mb-0">{{ __('Promote To Batch') }}</label>
                                    <span class="badge badge-light border text-muted" id="badgeBatchLocked" role="button" title="Auto-synced with selected Batch. Click to toggle lock/unlock." style="font-size: 9px; cursor: pointer;"><i class="fa fa-lock mr-1"></i>Auto</span>
                                </div>
                                <select class="toolbar-control select2" id="promote_batch" disabled>
                                    <option value="">Current / Same Batch</option>
                                    @if(!empty($batches))
                                        @foreach($batches as $b)
                                            <option value="{{ $b->name ?? '' }}" {{ ($search['batch'] ?? '') == $b->name ? 'selected' : '' }}>{{ $b->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 col-6">
                                <label class="toolbar-label text-danger">{{ __('Promotion Date') }} *</label>
                                <input class="toolbar-control" type="date" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                            </div> 
                        </div>

                        <!-- Real-time Target Semester Fee Status Banner -->
                        <div id="target_fee_status_container" class="mt-2" style="display: none;">
                            <!-- Rendered dynamically by JS -->
                        </div>
                    </div>

                    <!-- Students Spreadsheet Table -->
                    <div class="table-responsive" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                        <table class="table table-bordered promote-sheet-table">
                            <thead>
                                <tr>
                                    <th width="45" class="text-center">
                                        <input type="checkbox" id="masterCheckbox" class="checkbox-excel" checked title="Select / Deselect All">
                                    </th>
                                    <th width="45" class="text-center">#</th>
                                    <th width="110">{{ __('Admission No.') }}</th>
                                    <th>{{ __('Student Name') }}</th>
                                    <th>{{ __('Father Name') }}</th>
                                    <th>{{ __('Course') }}</th>
                                    <th>{{ __('Current Semester') }}</th>
                                    <th class="text-right" width="110">Prev. Due</th>
                                    <th class="text-right" width="110">Discount</th>
                                    <th class="text-center" width="135" title="Toggle Continue Discount for All Eligible">
                                        <label class="mb-0 d-inline-flex align-items-center pointer text-dark" for="continue_previous_discount_all" style="cursor: pointer; font-size: 11px;">
                                            <input type="checkbox" id="continue_previous_discount_all" class="checkbox-excel mr-1">
                                            <span>Continue Disc?</span>
                                        </label>
                                    </th>
                                    <th width="100">{{ __('Batch') }}</th>
                                    <th width="110">{{ __('Mobile') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($data))
                                    @if($data->count() > 0)
                                        @php $i = 1; @endphp
                                        @foreach ($data as $item)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="student-checkbox checkbox-excel" name="admission_ids[]" value="{{ $item['id'] ?? '' }}" checked>
                                                </td>
                                                <td class="text-center text-muted">{{ $i++ }}</td>
                                                <td><span class="font-weight-bold">{{ $item['admissionNo'] ?? '' }}</span></td>
                                                <td>
                                                    <strong class="text-dark">{{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}</strong>
                                                </td>
                                                <td class="text-muted">{{ $item['father_name'] ?? '' }}</td>
                                                <td>
                                                    <span class="sheet-chip sheet-chip-course">{{ $item['course'] ?? ($item['ClassTypes']['course']['name'] ?? 'N/A') }}</span>
                                                </td>
                                                <td><span class="font-weight-bold">{{ $item['ClassTypes']['name'] ?? '' }}</span></td>
                                                <td class="text-right">
                                                    @if(($item->previous_due ?? 0) < 0)
                                                        <strong class="text-success" title="Advance / Excess Amount Paid">-₹{{ number_format(abs($item->previous_due), 2) }}</strong>
                                                        <span class="badge badge-success px-1 py-0.5 ml-1" style="font-size: 8.5px; font-weight: 700; background-color: #10b981; vertical-align: middle;">Adv.</span>
                                                    @elseif(($item->previous_due ?? 0) > 0)
                                                        <strong class="text-danger">₹{{ number_format($item->previous_due, 2) }}</strong>
                                                    @else
                                                        <span class="text-muted font-weight-bold">₹0.00</span>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <span class="text-primary font-weight-bold">₹{{ number_format($item->previous_discount ?? 0, 2) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                           class="continue-discount-checkbox checkbox-excel"
                                                           name="continue_discount_admission_ids[]"
                                                           value="{{ $item['id'] ?? '' }}"
                                                           {{ ($item->previous_discount ?? 0) <= 0 ? 'disabled' : '' }}
                                                           title="{{ ($item->previous_discount ?? 0) > 0 ? 'Continue discount in promoted semester' : 'No discount available' }}">
                                                </td>
                                                <td>
                                                    <span class="sheet-chip sheet-chip-batch">{{ $item['batch'] ?? '-' }}</span>
                                                </td>
                                                <td class="text-muted">{{ $item['mobile'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="12" class="text-center text-danger py-4">
                                                <i class="fa fa-exclamation-circle fa-2x mb-2 text-danger"></i><br>
                                                <strong>No Students Found!</strong> No active students match the selected filter criteria.
                                            </td>
                                        </tr>
                                    @endif
                                @else
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            <i class="fa fa-search fa-2x mb-2 text-secondary"></i><br>
                                            Please choose a <strong>Course</strong> or <strong>Class / Semester</strong> from the toolbar above and click <strong>Filter</strong>.
                                        </td>
                                    </tr>
                                @endif    
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Action Ribbon -->
                    @if(!empty($data) && $data->count() > 0)
                        <div class="promote-bottom-bar">
                            <div class="d-flex align-items-center flex-wrap" style="font-size: 12px; color: #1e293b; gap: 10px;">
                                <span>Total Listed: <strong class="text-dark">{{ $data->count() }}</strong></span>
                                <span class="text-muted">|</span>
                                <span>Selected for Promotion: <strong class="text-success" id="selectedCountDisplay">{{ $data->count() }}</strong></span>
                                <span class="text-muted">|</span>
                                <label class="mb-0 d-inline-flex align-items-center pointer text-dark" for="continue_previous_discount_all_bottom" style="font-weight: 600; font-size: 11.5px; cursor: pointer;">
                                    <input type="checkbox" id="continue_previous_discount_all_bottom" class="checkbox-excel mr-1">
                                    <span>Continue Discount for All</span>
                                </label>
                            </div>
                            <div>
                                <button type="button" class="btn-promote-submit" id="btnTriggerPromoteConfirm">
                                    <i class="fa fa-graduation-cap mr-1"></i> {{ __('Promote Selected Students') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </section>
</div>

<!-- Custom Excel-Themed Promotion Confirmation Modal -->
<div class="modal fade" id="modalConfirmPromotion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-graduation-cap"></i> Confirm Student Promotion
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
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Promote Selected Students</strong>
                        <span class="text-muted" style="font-size: 11.5px;">Please review the promotion parameters below:</span>
                    </div>
                </div>

                <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Target Course:</span>
                        <strong class="text-dark" id="modalTargetCourse">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Target Semester:</span>
                        <strong class="text-dark" id="modalTargetSemester">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Students to Promote:</span>
                        <strong class="text-success font-weight-bold" id="modalCountStudents">0 Students</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Continuing Discount:</span>
                        <strong class="text-primary font-weight-bold" id="modalCountDiscount">0 Students</strong>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mb-0" id="promotionInfoNote" style="font-size: 11px; border-radius: 2px; background: #e7eff5; border-color: #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-1"></i> New admission ledger rows will be generated for the target semester. Previous semester dues will carry forward automatically.
                </div>

                <!-- Live Execution Progress & Status Container -->
                <div id="promotionProgressContainer" class="mt-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small font-weight-bold text-dark" id="promotionProgressText">
                            <i class="fa fa-spinner fa-spin text-success mr-1"></i> Processing promotion securely...
                        </span>
                        <span class="badge badge-light border text-muted" id="promotionAtomicBadge" style="font-size: 10px;">
                            <i class="fa fa-shield text-success mr-1"></i> Atomic Safe
                        </span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 4px; background: #e2e8f0;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="promotionProgressBar" role="progressbar" style="width: 100%;"></div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                        <i class="fa fa-info-circle mr-1"></i> In case of any interruption or timeout, automatic database rollback ensures zero data loss.
                    </small>
                </div>

                <div id="promotionErrorAlert" class="alert alert-danger py-2 px-3 mt-3 mb-0" style="font-size: 11.5px; border-radius: 4px; display: none;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> <span id="promotionErrorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" id="btnCancelPromotion" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-search-action px-3" id="btnExecutePromotion">
                    <i class="fa fa-check mr-1"></i> Confirm & Promote
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Fee Head Modal for Promotion Flow (Excel Styled) -->
<div class="modal fade" id="modalQuickAddFeeHead" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-plus-circle"></i> Assign Fee Head to Semester
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickAddModalForm" onsubmit="handleSaveQuickFeeHead(event)">
                @csrf
                <input type="hidden" name="class_type_id" id="quickAddClassTypeId">
                <input type="hidden" name="session_id" id="quickAddSessionId">
                <input type="hidden" name="batch" id="quickAddBatch">
                <input type="hidden" name="course_id" id="quickAddCourseId">

                <div class="modal-body">
                    <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center" style="background: #e7eff5; border: 1px solid #b8c9d9; font-size: 11.5px; color: #1e3a5f;">
                        <span>Target Semester: <strong id="quickAddModalClassName" class="text-dark">-</strong></span>
                        <span class="badge badge-info" style="font-size: 10px;">Fees Setup</span>
                    </div>

                    <!-- Fee Head Selection or Create New -->
                    <div class="form-group mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="toolbar-label mb-0" style="font-size: 11px;">Fee Head <span class="text-danger">*</span></label>
                            <a href="javascript:void(0)" class="small font-weight-bold text-success" onclick="toggleQuickNewHead()">
                                <span id="btnToggleQuickNewHeadText">+ Create New Head</span>
                            </a>
                        </div>
                        <div id="quickSelectHeadWrapper">
                            <select class="toolbar-control" name="fees_group_id" id="quickAddFeeGroupSelect" required style="height: 32px !important;">
                                <option value="">-- Choose Existing Fee Head --</option>
                                @foreach($masterFeesGroups ?? [] as $mfg)
                                    <option value="{{ $mfg->id }}">{{ $mfg->name }} ({{ $mfg->group_type ?? 'Compulsory' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="quickNewHeadWrapper" class="d-none mt-2 p-2 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                            <label class="toolbar-label mb-1">New Fee Head Name:</label>
                            <input type="text" name="new_group_name" id="quickNewGroupNameInput" class="toolbar-control mb-2" placeholder="e.g. Tuition Fee, Development Fee">
                            <label class="toolbar-label mb-1">Category / Type:</label>
                            <select name="new_group_type" class="toolbar-control">
                                <option value="Compulsory">Compulsory</option>
                                <option value="Optional">Optional</option>
                                <option value="Examination">Examination</option>
                                <option value="Library">Library</option>
                                <option value="Hostel">Hostel</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fee Amount & Due Date -->
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="toolbar-label text-danger mb-1">Amount (₹) *</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold" style="background: #f1f5f9; border-color: #b8c4d1; font-size: 11px;">₹</span>
                                </div>
                                <input type="number" step="0.01" name="amount" class="form-control toolbar-control font-weight-bold" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="toolbar-label mb-1">Installment Due Date</label>
                            <input type="date" name="installment_due_date" class="toolbar-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-search-action px-3" id="btnQuickSaveSubmit">
                        <i class="fa fa-check mr-1"></i> Assign to Semester
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // ==========================================
    // AUTO PROGRESSION & TARGET FIELDS SYNC
    // ==========================================
    var isFieldsLocked = true;

    function lockTargetFields(locked) {
        isFieldsLocked = locked;
        if (locked) {
            $('#promote_course, #promote_batch').prop('disabled', true);
            $('#promote_course, #promote_batch').next('.select2-container').addClass('select2-locked');
            $('#badgeCourseLocked, #badgeBatchLocked').html('<i class="fa fa-lock mr-1"></i>Auto').removeClass('badge-warning text-dark').addClass('badge-light text-muted');
        } else {
            $('#promote_course, #promote_batch').prop('disabled', false);
            $('#promote_course, #promote_batch').next('.select2-container').removeClass('select2-locked');
            $('#badgeCourseLocked, #badgeBatchLocked').html('<i class="fa fa-unlock mr-1"></i>Manual').removeClass('badge-light text-muted').addClass('badge-warning text-dark');
        }
    }

    $(document).on('click', '#badgeCourseLocked, #badgeBatchLocked', function() {
        lockTargetFields(!isFieldsLocked);
    });

    var isProgressionSyncing = false;

    function syncProgression(triggerSource) {
        var course = $('#search_course').val();
        var batch = $('#search_batch').val();
        var currentClassId = (triggerSource === 'class_change') ? $('#search_class_type_id').val() : '';

        if (!course) {
            if (isFieldsLocked) {
                $('#promote_course').val('').trigger('change.select2');
                $('#hidden_promote_course').val('');
                $('#promote_class_type_id').empty().append('<option value="">-- Select Target Semester --</option>').trigger('change.select2');
            }
            $('#target_fee_status_container').slideUp(150).empty();
            return;
        }

        // Auto-sync Target Course
        if (isFieldsLocked) {
            $('#promote_course').val(course).trigger('change.select2');
            $('#hidden_promote_course').val(course);
        }

        // Auto-sync Target Batch
        if (isFieldsLocked) {
            $('#promote_batch').val(batch).trigger('change.select2');
            $('#hidden_promote_batch').val(batch);
        }

        isProgressionSyncing = true;

        $.ajax({
            url: "{{ url('getPromotionProgressionInfo') }}",
            type: "GET",
            data: {
                course: course,
                batch: batch,
                class_type_id: currentClassId
            },
            dataType: "json",
            success: function(res) {
                if (!res.status) {
                    isProgressionSyncing = false;
                    return;
                }

                // 1. Populate search Class / Semester dropdown (unless user manually changed class)
                if (triggerSource !== 'class_change') {
                    var searchClassSelect = $('#search_class_type_id');
                    var initialSearchClassId = "{{ $search['class_type_id'] ?? '' }}";
                    searchClassSelect.empty().append('<option value="">-- All Semesters --</option>');
                    if (res.current_classes && res.current_classes.length > 0) {
                        $.each(res.current_classes, function(k, c) {
                            var isLatest = (res.current_class_id && c.id == res.current_class_id);
                            var badge = isLatest ? ' (Latest)' : '';
                            searchClassSelect.append('<option value="' + c.id + '" data-order="' + c.orderBy + '">' + c.name + badge + '</option>');
                        });
                    }

                    // Auto-select latest active semester
                    var classToSelect = (triggerSource === 'initial' && initialSearchClassId) ? initialSearchClassId : res.current_class_id;
                    if (classToSelect) {
                        searchClassSelect.val(classToSelect).trigger('change.select2');
                        $('#badgeSearchClassStatus').show().text('Latest: ' + (res.current_class_name || 'Active'));
                    }
                }

                // 2. Populate Target Class / Semester and auto-select Next Semester
                var promoteClassSelect = $('#promote_class_type_id');
                promoteClassSelect.empty().append('<option value="">-- Select Target Semester --</option>');
                if (res.target_classes && res.target_classes.length > 0) {
                    $.each(res.target_classes, function(k, tc) {
                        var isNext = (res.target_class_id && tc.id == res.target_class_id);
                        var badge = isNext ? ' (Next Semester)' : '';
                        promoteClassSelect.append('<option value="' + tc.id + '" data-order="' + tc.orderBy + '">' + tc.name + badge + '</option>');
                    });
                }

                if (res.target_class_id) {
                    promoteClassSelect.val(res.target_class_id).trigger('change.select2');
                    $('#badgeNextSem').html('<i class="fa fa-arrow-right mr-1"></i>Next: ' + (res.target_class_name || 'Semester'));
                }

                // Keep target course and target batch locked
                lockTargetFields(isFieldsLocked);

                // 3. Verify Fee Structure for target semester
                checkTargetSemesterFeeStatus();

                isProgressionSyncing = false;
            },
            error: function() {
                isProgressionSyncing = false;
            }
        });
    }

    // Dynamic Dependent Class loading on Search Course change
    $(document).on('change', '#search_course', function() {
        syncProgression('course_change');
    });

    // Dynamic Dependent Class loading on Search Batch change
    $(document).on('change', '#search_batch', function() {
        syncProgression('batch_change');
    });

    // When user manually selects a semester in search toolbar
    $(document).on('change', '#search_class_type_id', function() {
        if (!isProgressionSyncing && $(this).val()) {
            syncProgression('class_change');
        }
    });

    // Target Course manual change (if unlocked)
    $(document).on('change', '#promote_course', function() {
        if (isFieldsLocked) return;
        var courseName = $(this).val();
        $('#hidden_promote_course').val(courseName);
        var classSelect = $('#promote_class_type_id');
        classSelect.empty().append('<option value="">-- Select Target Semester --</option>');
        
        if (courseName) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course: courseName },
                dataType: "json",
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(key, val) {
                            classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                        });
                    }
                    classSelect.val('').trigger('change.select2');
                }
            });
        }
    });

    $(document).on('change', '#promote_batch', function() {
        $('#hidden_promote_batch').val($(this).val());
    });

    // Checkbox Master Toggles & Counts
    function updateSelectedCount() {
        var total = $('.student-checkbox').length;
        var checked = $('.student-checkbox:checked').length;
        $('#selectedCountDisplay').text(checked);
        $('#masterCheckbox').prop('checked', total > 0 && total === checked);
    }

    $('#masterCheckbox').on('change', function() {
        $('.student-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    $(document).on('change', '.student-checkbox', updateSelectedCount);

    // Discount Continuation Master Toggle
    function eligibleDiscountCheckboxes() {
        return $('.continue-discount-checkbox:not(:disabled)');
    }

    function updateDiscountMasterState() {
        var eligible = eligibleDiscountCheckboxes();
        var checked = eligible.filter(':checked').length;
        var isAllChecked = eligible.length > 0 && checked === eligible.length;
        var isIndeterminate = checked > 0 && checked < eligible.length;
        $('#continue_previous_discount_all, #continue_previous_discount_all_bottom')
            .prop('checked', isAllChecked)
            .prop('indeterminate', isIndeterminate);
    }

    $(document).on('change', '#continue_previous_discount_all, #continue_previous_discount_all_bottom', function() {
        var shouldContinue = $(this).is(':checked');
        eligibleDiscountCheckboxes().prop('checked', shouldContinue);
        updateDiscountMasterState();
    });

    $(document).on('change', '.continue-discount-checkbox', updateDiscountMasterState);
    updateDiscountMasterState();

    lockTargetFields(true);

    if ($('#search_course').val() && $('#search_batch').val()) {
        syncProgression('initial');
    } else if ($('#search_course').val()) {
        syncProgression('course_change');
    }

    // ==========================================
    // TARGET SEMESTER FEE HEADS VERIFICATION
    // ==========================================
    var currentTargetFeeStatus = null;

    // Expose functions globally for modals and events
    window.renderTargetFeeStatus = renderTargetFeeStatus;
    window.checkTargetSemesterFeeStatus = checkTargetSemesterFeeStatus;

    function renderTargetFeeStatus(res, highlightHead) {
        var $container = $('#target_fee_status_container');
        currentTargetFeeStatus = res;

        if (res && (res.has_heads || (res.heads && res.heads.length > 0))) {
            var heads = res.heads || [];
            var totalFee = res.total_fee !== undefined ? Number(res.total_fee) : Number(res.semester_total || 0);
            var headsCount = res.heads_count !== undefined ? res.heads_count : heads.length;
            var className = res.class_name || $('#promote_class_type_id option:selected').text().trim() || 'Target Semester';
            var batchInfo = res.batch ? (' (Batch ' + res.batch + ')') : '';

            var headsBadges = heads.map(function(h) {
                var hName = h.name || h.head_name || 'Fee Head';
                var isJustAdded = highlightHead && (hName.toLowerCase().trim() === String(highlightHead).toLowerCase().trim());
                var badgeClass = isJustAdded
                    ? 'badge border mr-1 mb-1 shadow-sm new-head-pulse'
                    : 'badge badge-light border text-dark mr-1 mb-1';
                var badgeStyle = isJustAdded
                    ? 'font-size: 11.5px; padding: 4px 8px; background-color: #ecfdf5 !important; border-color: #10b981 !important; color: #065f46 !important;'
                    : 'font-size: 11px; padding: 3px 6px;';
                var newTag = isJustAdded ? ' <span class="badge badge-success ml-1" style="font-size: 8.5px; vertical-align: middle;">NEW</span>' : '';

                return '<span class="' + badgeClass + '" style="' + badgeStyle + '">' +
                    '<strong>' + hName + '</strong>: ₹' + Number(h.amount).toLocaleString('en-IN') + newTag +
                    '</span>';
            }).join('');

            var html = '<div class="alert alert-success py-2 px-3 mb-0" style="font-size: 12px; border-radius: 4px; border-left: 4px solid #217346; background: #f0fdf4; border-color: #bbf7d0; color: #166534; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">' +
                '<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">' +
                '    <div>' +
                '        <i class="fa fa-check-circle text-success mr-1"></i> ' +
                '        <strong>Fee Structure Ready:</strong> ' + headsCount + ' Head(s) configured for <b>' + className + '</b>' + batchInfo + '. Total: <b>₹' + totalFee.toLocaleString('en-IN') + '</b>' +
                '    </div>' +
                '    <button type="button" class="btn btn-xs btn-outline-success font-weight-bold" onclick="openQuickAddFeeHeadModal()">' +
                '        <i class="fa fa-plus mr-1"></i> Add Another Head' +
                '    </button>' +
                '</div>' +
                '<div class="mt-1 d-flex flex-wrap align-items-center">' + headsBadges + '</div>' +
                '</div>';

            $container.html(html).show();
        } else {
            var className = (res && res.class_name) || $('#promote_class_type_id option:selected').text().trim() || 'Selected Semester';
            var html = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size: 12px; border-radius: 4px; border-left: 4px solid #dc2626; background: #fef2f2; border-color: #fecaca; color: #991b1b;">' +
                '<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">' +
                '    <div>' +
                '        <i class="fa fa-exclamation-triangle text-danger mr-1"></i> ' +
                '        <strong>No Fee Heads Found!</strong> Target Semester <b>' + className + '</b> par koi fee heads assign nahi hain.' +
                '        <span class="d-block text-dark mt-0.5" style="font-size: 11px;">Student promote hone se pehle is semester par fee head assign hona zaroori hai.</span>' +
                '    </div>' +
                '    <button type="button" class="btn btn-sm btn-danger font-weight-bold shadow-sm" onclick="openQuickAddFeeHeadModal()">' +
                '        <i class="fa fa-plus-circle mr-1"></i> + Assign Fee Head Now' +
                '    </button>' +
                '</div>' +
                '</div>';

            $container.html(html).show();
        }
    }

    function checkTargetSemesterFeeStatus(highlightHead) {
        var classTypeId = $('#promote_class_type_id').val();
        var batch = $('#promote_batch').val() || $('#search_batch').val() || '';
        var courseId = $('#promote_course').val() || '';
        var $container = $('#target_fee_status_container');

        if (!classTypeId) {
            $container.hide().empty();
            currentTargetFeeStatus = null;
            return;
        }

        // Only show spinner if container is empty or hidden
        if ($container.is(':hidden') || $container.find('.alert').length === 0) {
            $container.html('<div class="p-2 border rounded bg-white text-muted small"><i class="fa fa-spinner fa-spin mr-1"></i> Checking fee structure for target semester...</div>').show();
        }

        $.ajax({
            url: "{{ url('getSemesterFeeStatus') }}",
            type: "GET",
            data: { class_type_id: classTypeId, batch: batch, course_id: courseId },
            dataType: "json",
            success: function(res) {
                renderTargetFeeStatus(res, highlightHead);
            },
            error: function() {
                if ($container.find('.alert-success').length === 0) {
                    $container.html('<div class="alert alert-warning py-1 px-2 mb-0 small">Unable to verify semester fee status.</div>');
                }
            }
        });
    }

    $(document).on('change', '#promote_class_type_id, #promote_batch, #search_batch', function() {
        checkTargetSemesterFeeStatus();
    });

    // Trigger Promotion Custom Modal Confirmation
    $('#btnTriggerPromoteConfirm').on('click', function(e) {
        e.preventDefault();

        // 1. Check if course and semester are selected
        var promoteCourse = $('#promote_course').val();
        var promoteClass = $('#promote_class_type_id').val();
        if (!promoteCourse || !promoteClass) {
            toastr.warning('Please select Target Course and Target Class/Semester first.');
            return false;
        }

        // 2. Check if students are selected
        var checkedStudents = $('.student-checkbox:checked').length;
        if (checkedStudents === 0) {
            toastr.error('Please select at least one student to promote.');
            return false;
        }

        // 3. Check target semester fee structure
        if (!currentTargetFeeStatus || currentTargetFeeStatus.has_heads === false) {
            var semName = $('#promote_class_type_id option:selected').text();
            toastr.error('Cannot promote: Semester "' + semName + '" has no fee heads assigned! Please click "+ Assign Fee Head Now" to configure fees first.');
            openQuickAddFeeHeadModal();
            return false;
        }

        // 4. Fill Confirmation Modal details
        var discountStudents = $('.continue-discount-checkbox:checked').filter(function() {
            return $(this).closest('tr').find('.student-checkbox').is(':checked');
        }).length;

        $('#modalTargetCourse').text($('#promote_course option:selected').text());
        $('#modalTargetSemester').text($('#promote_class_type_id option:selected').text());
        $('#modalCountStudents').text(checkedStudents + ' Student(s)');
        $('#modalCountDiscount').text(discountStudents + ' Student(s)');

        // Reset modal state
        $('#promotionProgressContainer').hide();
        $('#promotionErrorAlert').hide();
        $('#btnExecutePromotion').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm & Promote');
        $('#btnCancelPromotion').prop('disabled', false);

        $('#modalConfirmPromotion').modal('show');
    });

    // Execute Promotion via AJAX with atomic transaction & rollback error safety
    $('#btnExecutePromotion').on('click', function() {
        var $btn = $(this);
        var $cancelBtn = $('#btnCancelPromotion');
        
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Promoting...');
        $cancelBtn.prop('disabled', true);
        
        $('#promote_course').prop('disabled', false);
        $('#promote_batch').prop('disabled', false);
        $('#hidden_promote_course').val($('#promote_course').val());
        $('#hidden_promote_batch').val($('#promote_batch').val());

        $('#promotionProgressContainer').slideDown(150);
        $('#promotionErrorAlert').slideUp(100);

        var formElement = document.getElementById('promoteForm');
        var formData = new FormData(formElement);

        $.ajax({
            url: "{{ url('studentsPromoteAdd') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 180000, // 3 minutes timeout safety
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch(e) {}
                }
                if (response && response.status === true) {
                    $('#promotionProgressBar').removeClass('bg-success').addClass('bg-success').css('width', '100%');
                    $('#promotionProgressText').html('<i class="fa fa-check-circle text-success mr-1"></i> Promotion completed successfully!');
                    toastr.success(response.message || 'Students promoted successfully!');
                    
                    // Redirect or reload after brief visual confirmation
                    setTimeout(function() {
                        window.location.href = "{{ url('student/promote_add') }}";
                    }, 800);
                } else {
                    var errMsg = (response && response.message) ? response.message : 'Promotion encountered an error.';
                    handlePromotionFailure(errMsg);
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = '';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out! Database transaction has been automatically rolled back to prevent partial updates. No student data was altered.';
                } else if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        var errList = [];
                        $.each(xhr.responseJSON.errors, function(k, v) {
                            if (Array.isArray(v)) errList.push(v.join(' '));
                            else errList.push(v);
                        });
                        errorMsg = errList.join(' ');
                    }
                } else if (xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        errorMsg = parsed.message || 'Server error occurred.';
                    } catch(e) {
                        errorMsg = 'Server responded with status (' + xhr.status + ': ' + (error || 'Error') + '). Any incomplete promotion operations were rolled back safely.';
                    }
                } else {
                    errorMsg = 'An unexpected error occurred (' + status + '). Any incomplete promotion operations were rolled back automatically.';
                }
                handlePromotionFailure(errorMsg);
            }
        });

        function handlePromotionFailure(msg) {
            $btn.prop('disabled', false).html('<i class="fa fa-refresh mr-1"></i> Retry Promotion');
            $cancelBtn.prop('disabled', false);
            $('#promotionProgressContainer').slideUp(100);
            $('#promotionErrorMessage').text(msg);
            $('#promotionErrorAlert').slideDown(150);
            toastr.error(msg, 'Promotion Error & Rollback');
        }
    });

    $('#promoteForm').on('submit', function(e) {
        // Prevent accidental native submit if user presses Enter
        e.preventDefault();
        $('#btnExecutePromotion').trigger('click');
    });
});

// Quick Add Fee Head Modal Functions
window.openQuickAddFeeHeadModal = function() {
    var classTypeId = $('#promote_class_type_id').val();
    var className = $('#promote_class_type_id option:selected').text().trim() || 'Selected Semester';
    var sessionId = $('#new_session_id').val() || '{{ Session::get('session_id') }}';

    if (!classTypeId) {
        toastr.warning('Please select a target Class / Semester first.');
        return;
    }

    var batch = $('#promote_batch').val() || $('#search_batch').val() || '2026';
    var courseId = $('#promote_course').val() || '';

    $('#quickAddModalForm')[0].reset();
    $('#quickAddClassTypeId').val(classTypeId);
    $('#quickAddSessionId').val(sessionId);
    $('#quickAddBatch').val(batch);
    $('#quickAddCourseId').val(courseId);
    $('#quickAddModalClassName').text(className);
    $('#quickNewHeadWrapper').addClass('d-none');
    $('#quickSelectHeadWrapper').removeClass('d-none');
    $('#quickAddFeeGroupSelect').attr('required', 'required');
    $('#quickNewGroupNameInput').removeAttr('required');
    $('#btnToggleQuickNewHeadText').text('+ Create New Head');

    $('#modalQuickAddFeeHead').modal('show');
};

window.toggleQuickNewHead = function() {
    var $newWrap = $('#quickNewHeadWrapper');
    var $selWrap = $('#quickSelectHeadWrapper');
    var $btnText = $('#btnToggleQuickNewHeadText');
    if ($newWrap.hasClass('d-none')) {
        $newWrap.removeClass('d-none');
        $selWrap.addClass('d-none');
        $('#quickAddFeeGroupSelect').removeAttr('required').val('');
        $('#quickNewGroupNameInput').attr('required', 'required').focus();
        $btnText.text('← Choose Existing');
    } else {
        $newWrap.addClass('d-none');
        $selWrap.removeClass('d-none');
        $('#quickAddFeeGroupSelect').attr('required', 'required');
        $('#quickNewGroupNameInput').removeAttr('required').val('');
        $btnText.text('+ Create New Head');
    }
};

window.handleSaveQuickFeeHead = function(e) {
    if (e) {
        e.preventDefault();
    }
    var $btn = $('#btnQuickSaveSubmit');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Assigning...');

    var formData = $('#quickAddModalForm').serialize();
    var newNameInput = $('#quickNewGroupNameInput').val();
    var selectedOptionText = $('#quickAddFeeGroupSelect option:selected').text();
    var enteredHeadName = (newNameInput && newNameInput.trim()) 
        ? newNameInput.trim() 
        : (selectedOptionText ? selectedOptionText.replace(/\s*\(.*?\)\s*$/, '').trim() : '');

    $.ajax({
        url: "{{ url('saveSemesterFeeHead') }}",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(res) {
            $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Assign to Semester');
            if (res.status === true || res.status === 'success') {
                $('#modalQuickAddFeeHead').modal('hide');
                toastr.success(res.message || 'Fee head assigned successfully!');

                var targetHeadName = res.added_head_name || enteredHeadName;

                // 1. Instant Real-time UI rendering directly from server response
                try {
                    if (typeof window.renderTargetFeeStatus === 'function') {
                        window.renderTargetFeeStatus(res, targetHeadName);
                    }
                } catch (err) {
                    console.error('renderTargetFeeStatus error:', err);
                }

                // 2. Dynamically add newly created head to modal dropdown so it's readily selectable
                if (res.added_head_name && res.added_head_id) {
                    var exists = $('#quickAddFeeGroupSelect option[value="' + res.added_head_id + '"]').length > 0;
                    if (!exists) {
                        $('#quickAddFeeGroupSelect').append('<option value="' + res.added_head_id + '">' + res.added_head_name + '</option>');
                    }
                }

                // 3. Re-verify in background to ensure all session caches are aligned
                setTimeout(function() {
                    if (typeof window.checkTargetSemesterFeeStatus === 'function') {
                        window.checkTargetSemesterFeeStatus(targetHeadName);
                    }
                }, 300);
            } else {
                toastr.error(res.message || 'Failed to assign fee head.');
            }
        },
        error: function(err) {
            $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Assign to Semester');
            var msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error assigning fee head.';
            toastr.error(msg);
        }
    });
};
</script>
@endsection
