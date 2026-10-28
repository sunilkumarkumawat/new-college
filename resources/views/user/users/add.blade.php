@php
$isEdit = isset($data) && !empty($data->id);
$getCountry = Helper::getCountry();
$getState = Helper::getState();
$roleType = Helper::roleType();
$sidebar = Helper::getSiderbar();
$getPermisnByBranch = Helper::getPermisnByBranch();
$allPermisn = !empty($getPermisnByBranch['branch_sidebar_id']) ? explode(',', $getPermisnByBranch['branch_sidebar_id']) : [];
$allowSubSidebar = !empty($getPermisnByBranch['sidebar_sub_id']) ? explode(',', $getPermisnByBranch['sidebar_sub_id']) : [];
$getSetting = Helper::getSetting();
$getgenders = Helper::getgender();
$bloodGroupType = Helper::bloodGroupType();
$isHindi = Session::get('locale') == 'hi';

// Selected state and cities
$selectedStateId = old('state', $data->state_id ?? ($getSetting->state_id ?? ''));
$getCity = !empty($selectedStateId) ? Helper::getCity($selectedStateId) : Helper::getCity();

// Load submodules grouped by sidebar_id
$subSidebarsGrouped = DB::table('sidebar_sub')
    ->whereNull('deleted_at')
    ->orderBy('orderBy', 'ASC')
    ->orderBy('id', 'ASC')
    ->get()
    ->groupBy('sidebar_id');

// Assigned permissions if in edit mode
$assignedSidebarIds = !empty($add_pr->sidebar_id) ? explode(',', $add_pr->sidebar_id) : [];
$assignedSubSidebarIds = !empty($add_pr->sidebar_sub_id) ? explode(',', $add_pr->sidebar_sub_id) : [];
@endphp

@extends('layout.app')
@section('content')

<style>
/* ========================================================
   COMPACT EXECUTIVE USER / STAFF FORM THEME (#217346)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #f0fdf4;
    --green-excel-border: #a7d0b8;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --navy: #002c54;
    --text-main: #0f172a;
}

.user-form-compact-page {
    font-size: 11.5px;
    color: var(--text-main);
    background-color: #f4f6f9;
}

/* 1. Main Form Card Wrapper */
.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

/* 2. Executive Titlebar */
.dash-titlebar {
    min-height: 42px;
    padding: 6px 14px;
    background: var(--green-excel);
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
    font-size: 11px;
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
    padding: 3px 9px !important;
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

/* 3. Section Strips (Compact Ribbon Dividers) */
.form-section-strip {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-left: 3.5px solid var(--green-excel);
    padding: 4px 10px;
    margin-top: 10px;
    margin-bottom: 8px;
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.form-section-title {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    color: #166534;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-section-badge {
    background: #ffffff;
    color: #166534;
    border: 1px solid var(--green-excel-border);
    border-radius: 10px;
    padding: 1px 7px;
    font-size: 10px;
    font-weight: 700;
}

/* 4. Compact Form Elements */
.compact-form-group {
    margin-bottom: 7px;
    width: 100%;
}

.compact-form-group label {
    font-size: 11px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 2px;
    display: block;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.compact-form-group label .req-star {
    color: #dc2626;
    margin-left: 2px;
    font-weight: 700;
}

.form-control-compact {
    height: 28px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    padding: 2px 7px !important;
    font-size: 11.5px !important;
    color: #1e293b !important;
    background-color: #ffffff !important;
    transition: all 0.15s ease-in-out;
    width: 100% !important;
    display: block !important;
    box-sizing: border-box !important;
}
.form-control-compact:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.18) !important;
    outline: none !important;
}

.form-control-compact[readonly] {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #475569 !important;
    font-weight: 700;
    cursor: not-allowed;
}

textarea.form-control-compact {
    height: auto !important;
    min-height: 28px !important;
    resize: vertical;
    padding: 4px 7px !important;
}

/* Input Icon Addons */
.compact-input-group {
    display: flex !important;
    align-items: stretch !important;
    width: 100% !important;
}
.compact-addon-icon {
    background: #f8fafc;
    border: 1px solid var(--line-subtle);
    border-right: none;
    border-radius: 2px 0 0 2px;
    padding: 2px 6px;
    color: #64748b;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    flex-shrink: 0;
}
.compact-input-group .form-control-compact {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    flex: 1 1 auto !important;
    width: 1% !important;
}
.compact-input-group:focus-within .compact-addon-icon {
    border-color: var(--green-excel);
    color: var(--green-excel);
    background: #f0fdf4;
}

/* Select2 Full Width & Compact Height Override */
.select2,
.select2-container,
.select2-container--default,
.select2-container--bootstrap4 {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    display: block !important;
    box-sizing: border-box !important;
}

.select2-container .select2-selection--single {
    height: 28px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    padding: 1px 5px !important;
    font-size: 11.5px !important;
    width: 100% !important;
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px !important;
    color: #1e293b !important;
    padding-left: 2px !important;
    padding-right: 20px !important;
    font-size: 11.5px !important;
    width: 100% !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 26px !important;
    right: 4px !important;
    top: 1px !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.18) !important;
}

/* Upload Card/Box Style */
.photo-upload-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px dashed var(--line-subtle);
    border-radius: 3px;
    padding: 4px 8px;
    transition: all 0.15s ease-in-out;
}
.photo-upload-box:hover {
    border-color: var(--green-excel);
    background: #f0fdf4;
}
.photo-preview-thumb {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 3px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    flex-shrink: 0;
}
.photo-upload-input {
    font-size: 11px;
    color: #475569;
    width: 100%;
}

/* Document Upload Tile */
.doc-upload-tile {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 6px 8px;
    transition: all 0.15s ease;
}
.doc-upload-tile:hover {
    border-color: var(--green-excel-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.doc-upload-tile label {
    font-size: 10.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ========================================================
   UNIFIED PERMISSION MATRIX - EXECUTIVE CARD GRID
   ======================================================== */
.perm-matrix-wrapper {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 10px;
}

.perm-toolbar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 8px 12px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.perm-action-badge {
    cursor: pointer;
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
}
.perm-action-badge input[type="checkbox"] {
    accent-color: var(--green-excel);
    width: 13px;
    height: 13px;
    cursor: pointer;
}

.badge-action-pill {
    padding: 2px 7px;
    border-radius: 3px;
    font-size: 10.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-edit { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-delete { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.badge-download { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

/* Module Permission Card */
.perm-module-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: all 0.18s ease-in-out;
    display: flex;
    flex-direction: column;
    height: calc(100% - 12px);
}
.perm-module-card:hover {
    border-color: var(--green-excel);
    box-shadow: 0 2px 8px rgba(33, 115, 70, 0.12);
}
.perm-module-card.is-selected {
    border-color: var(--green-excel);
    background: #fcfffd;
}

/* Card Header */
.perm-module-header {
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    padding: 6px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    transition: background 0.15s ease;
}
.perm-module-card.is-selected .perm-module-header {
    background: #e7f5ed;
    border-bottom-color: var(--green-excel-border);
}

.perm-module-title-label {
    cursor: pointer;
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    user-select: none;
}
.perm-module-title-label input[type="checkbox"] {
    accent-color: var(--green-excel);
    width: 15px;
    height: 15px;
    cursor: pointer;
}

.perm-count-badge {
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
}
.perm-module-card.is-selected .perm-count-badge {
    background: var(--green-excel);
    color: #ffffff;
    border-color: var(--green-excel-dark);
}

/* Card Body with Sub-features Checklist */
.perm-module-body {
    padding: 8px 10px;
    flex: 1 1 auto;
    background: #ffffff;
    max-height: 240px;
    overflow-y: auto;
}
.perm-module-body::-webkit-scrollbar {
    width: 3.5px;
}
.perm-module-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.sub-features-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 4px;
}

.sub-feature-item {
    padding: 3px 6px;
    border-radius: 3px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    transition: all 0.12s ease;
}
.sub-feature-item:hover {
    background: #f0fdf4;
    border-color: #dcfce7;
}
.sub-feature-item.is-checked {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.sub-feature-label {
    cursor: pointer;
    margin: 0;
    font-size: 11px;
    font-weight: 500;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 6px;
    width: 100%;
    user-select: none;
}
.sub-feature-label input[type="checkbox"] {
    accent-color: var(--green-excel);
    width: 13.5px;
    height: 13.5px;
    cursor: pointer;
    flex-shrink: 0;
}
.sub-feature-icon {
    font-size: 10px;
    color: #64748b;
    width: 13px;
    text-align: center;
    flex-shrink: 0;
}
.sub-feature-item.is-checked .sub-feature-name {
    color: #166534;
    font-weight: 600;
}
.sub-feature-item.is-checked .sub-feature-icon {
    color: var(--green-excel);
}

/* Custom Checkbox Pill */
.custom-check-pill {
    cursor: pointer;
    margin: 0;
    font-size: 11px;
    font-weight: 600;
    color: #1e293b;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.custom-check-pill input[type="checkbox"] {
    accent-color: var(--green-excel);
    width: 14px;
    height: 14px;
    cursor: pointer;
}

/* 5. Bottom Action Bar */
.dash-form-footer {
    padding: 8px 14px;
    background: #f8fafc;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.btn-submit-save {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding: 5px 22px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.25);
}
.btn-submit-save:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(33, 115, 70, 0.35);
}

.btn-form-reset {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 14px !important;
    transition: all 0.15s ease;
}
.btn-form-reset:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
}
</style>

<div class="content-wrapper user-form-compact-page">
    <section class="content pt-2 pb-4">
        <div class="container-fluid">
            
            <!-- Main Form Card -->
            <div class="dash-main-card">
                
                <!-- Executive Header Bar -->
                <div class="dash-titlebar">
                    <div class="dash-title">
                        <i class="fa {{ $isEdit ? 'fa-pencil-square-o' : 'fa-user-plus' }}"></i>
                        <span>{{ $isEdit ? __('user.Edit User') . ' / Staff Profile' : __('user.Add User') . ' / Staff Registration' }}</span>
                        <small class="d-none d-md-inline-block">| {{ $isEdit ? 'Update Profile, Credentials & Role Permissions' : 'Multi-Role College Staff, Faculty & Employee Profile' }}</small>
                    </div>
                    <div class="dash-title-tools">
                        @if($isEdit)
                        <a href="{{ url('addUser') }}" class="btn-header-action" title="Add New User">
                            <i class="fa fa-plus text-success mr-1"></i> Add New User
                        </a>
                        @endif
                        <a href="{{ url('viewUser') }}" class="btn-header-action" title="User Directory">
                            <i class="fa fa-users text-primary mr-1"></i> {{ __('common.View') }} Directory
                        </a>
                        <a href="{{ url('user_dashboard') }}" class="btn-header-action" title="User Dashboard">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- Form Body -->
                <form id="userForm" action="{{ $isEdit ? url('editUser', $data->id) : url('addUser') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="p-3">

                        <!-- ========================================== -->
                        <!-- SECTION 1: ROLE & EMPLOYMENT DETAILS      -->
                        <!-- ========================================== -->
                        <div class="form-section-strip" style="margin-top: 0;">
                            <span class="form-section-title">
                                <i class="fa fa-id-badge"></i> 1. Role & Employment Profile / पद एवं रोजगार विवरण
                            </span>
                            <span class="form-section-badge">Dynamic Role & Staff Code</span>
                        </div>

                        <div class="row px-1">
                            
                            @if(Session::get('role_id') == 1)
                            <!-- Branch ID for Admin -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>{{ __('user.Branch ID') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact select2" name="branch_id" id="branch_id">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($branch)) 
                                        @foreach($branch as $Branch)
                                            <option value="{{ $Branch->id ?? '' }}" {{ (old('branch_id', $data->branch_id ?? Session::get('branch_id')) == $Branch->id) ? 'selected' : '' }}>
                                                {{ $Branch->branch_name ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif                                    
                                </select>
                            </div>
                            @endif

                            <!-- Role Selection -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="mb-0">{{ __('user.Role') }} <span class="req-star">*</span></label>
                                    <a href="javascript:void(0)" class="text-success font-weight-bold" style="font-size: 10px;" data-toggle="modal" data-target="#addRoleModal">
                                        <i class="fa fa-plus-circle"></i> + Add New Role
                                    </a>
                                </div>
                                <select class="form-control-compact select2 @error('role_id') is-invalid @enderror" name="role_id" id="role_id" required>
                                    <option value="">-- {{ __('common.Select') }} Role --</option>
                                    @if(!empty($roleType))
                                        @foreach($roleType as $item)
                                            @if($item->id != 3) {{-- Exclude Student --}}
                                            <option value="{{ $item->id ?? '' }}" {{ (old('role_id', $data->role_id ?? '') == $item->id) ? 'selected' : '' }}>
                                                {{ $item->name ?? '' }}
                                            </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                @error('role_id')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Employee / Staff Code -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Staff / Employee ID <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-hashtag text-primary"></i></span>
                                    <input type="text" class="form-control-compact font-weight-bold" id="UniqueId" name="UniqueId" 
                                           value="{{ old('UniqueId', $data->UniqueId ?? ($autoEmployeeId ?? '')) }}" placeholder="e.g. EMP-0001" required>
                                </div>
                            </div>

                            <!-- Designation -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Designation / पद</label>
                                <input type="text" class="form-control-compact" id="designation" name="designation" 
                                       value="{{ old('designation', $data->designation ?? '') }}" placeholder="e.g. Assistant Professor, Accountant, Peon">
                            </div>

                            <!-- Department -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Department / विभाग</label>
                                <select class="form-control-compact select2" id="department" name="department">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @php $dept = old('department', $data->department ?? ''); @endphp
                                    <option value="Academic / Teaching" {{ $dept == 'Academic / Teaching' ? 'selected' : '' }}>Academic / Teaching</option>
                                    <option value="Administration" {{ $dept == 'Administration' ? 'selected' : '' }}>Administration</option>
                                    <option value="Accounts & Finance" {{ $dept == 'Accounts & Finance' ? 'selected' : '' }}>Accounts & Finance</option>
                                    <option value="Library" {{ $dept == 'Library' ? 'selected' : '' }}>Library</option>
                                    <option value="Laboratory / Technical" {{ $dept == 'Laboratory / Technical' ? 'selected' : '' }}>Laboratory / Technical</option>
                                    <option value="Housekeeping & Sanitation" {{ $dept == 'Housekeeping & Sanitation' ? 'selected' : '' }}>Housekeeping & Sanitation (Sweeper/Peon)</option>
                                    <option value="Transport" {{ $dept == 'Transport' ? 'selected' : '' }}>Transport</option>
                                    <option value="Security & Safety" {{ $dept == 'Security & Safety' ? 'selected' : '' }}>Security & Safety</option>
                                    <option value="Hostel Management" {{ $dept == 'Hostel Management' ? 'selected' : '' }}>Hostel Management</option>
                                    <option value="Other" {{ $dept == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Date of Joining -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Date of Joining / नियुक्ति तिथि</label>
                                <input type="date" class="form-control-compact" id="joining_date" name="joining_date" 
                                       value="{{ old('joining_date', $data->joining_date ?? date('Y-m-d')) }}">
                            </div>

                            <!-- Contract Type -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Employment Type / प्रकार</label>
                                <select class="form-control-compact select2" id="contract_type" name="contract_type">
                                    @php $ctype = old('contract_type', $data->contract_type ?? 'Permanent'); @endphp
                                    <option value="Permanent" {{ $ctype == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                    <option value="Contractual" {{ $ctype == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                    <option value="Part-time" {{ $ctype == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                    <option value="Probation" {{ $ctype == 'Probation' ? 'selected' : '' }}>Probation</option>
                                    <option value="Daily Wage / Temporary" {{ $ctype == 'Daily Wage / Temporary' ? 'selected' : '' }}>Daily Wage / Temporary</option>
                                </select>
                            </div>

                            <!-- Work Shift -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Work Shift / कार्य पाली</label>
                                <select class="form-control-compact select2" id="shift" name="shift">
                                    @php $shft = old('shift', $data->shift ?? 'General Shift'); @endphp
                                    <option value="General Shift" {{ $shft == 'General Shift' ? 'selected' : '' }}>General Shift (Day)</option>
                                    <option value="Morning Shift" {{ $shft == 'Morning Shift' ? 'selected' : '' }}>Morning Shift</option>
                                    <option value="Evening Shift" {{ $shft == 'Evening Shift' ? 'selected' : '' }}>Evening Shift</option>
                                    <option value="Night Shift" {{ $shft == 'Night Shift' ? 'selected' : '' }}>Night Shift</option>
                                </select>
                            </div>

                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 2: PERSONAL & DEMOGRAPHIC DETAILS  -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-user"></i> 2. Personal & Demographic Details / व्यक्तिगत विवरण
                            </span>
                            <span class="form-section-badge">Identity & Family Info</span>
                        </div>

                        <div class="row px-1">

                            <!-- First Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.First Name') }} <span class="req-star">*</span></label>
                                <input type="text" class="form-control-compact @error('first_name') is-invalid @enderror" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" id="first_name" name="first_name" 
                                       value="{{ old('first_name', $data->first_name ?? '') }}" placeholder="{{ __('common.First Name') }}" required>
                                @error('first_name')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Last Name') }} <span class="req-star">*</span></label>
                                <input type="text" class="form-control-compact @error('last_name') is-invalid @enderror" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" id="last_name" name="last_name" 
                                       value="{{ old('last_name', $data->last_name ?? '') }}" placeholder="{{ __('common.Last Name') }}" required>
                                @error('last_name')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Father's Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Fathers Name') }}</label>
                                <input type="text" class="form-control-compact" onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="father_name" name="father_name" value="{{ old('father_name', $data->father_name ?? '') }}" placeholder="{{ __('common.Fathers Name') }}">
                            </div>

                            <!-- Mother's Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Mothers Name') }}</label>
                                <input type="text" class="form-control-compact" onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="mother_name" name="mother_name" value="{{ old('mother_name', $data->mother_name ?? '') }}" placeholder="{{ __('common.Mothers Name') }}">
                            </div>

                            <!-- Gender -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>{{ __('common.Gender') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact select2" id="gender_id" name="gender_id" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getgenders))
                                        @foreach($getgenders as $value)
                                            <option value="{{ $value->id }}" {{ (old('gender_id', $data->gender_id ?? '') == $value->id) ? 'selected' : '' }}>
                                                {{ $value->name ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>{{ __('common.Date Of  Birth') }}</label>
                                <input type="date" class="form-control-compact" id="dob" name="dob" value="{{ old('dob', $data->dob ?? '') }}">
                            </div>

                            <!-- Marital Status -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Marital Status / वैवाहिक स्थिति</label>
                                <select class="form-control-compact select2" id="marital_status" name="marital_status">
                                    @php $mstatus = old('marital_status', $data->marital_status ?? 'Single'); @endphp
                                    <option value="Single" {{ $mstatus == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ $mstatus == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Divorced" {{ $mstatus == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                    <option value="Widowed" {{ $mstatus == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                </select>
                            </div>

                            <!-- Blood Group -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Blood Group / रक्त समूह</label>
                                <select class="form-control-compact select2" id="blood_group" name="blood_group">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($bloodGroupType))
                                        @foreach($bloodGroupType as $bg)
                                            <option value="{{ $bg->name }}" {{ (old('blood_group', $data->blood_group ?? '') == $bg->name) ? 'selected' : '' }}>
                                                {{ $bg->name ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Spouse Name -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Spouse Name / पति/पत्नी का नाम</label>
                                <input type="text" class="form-control-compact" onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="spouse_name" name="spouse_name" value="{{ old('spouse_name', $data->spouse_name ?? '') }}" placeholder="Spouse Name">
                            </div>

                            <!-- Aadhaar Card Number -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Aadhaar No. / आधार सं.</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-id-card text-info"></i></span>
                                    <input type="text" class="form-control-compact" id="aadhaar_no" name="aadhaar_no" 
                                           value="{{ old('aadhaar_no', $data->aadhaar_no ?? '') }}" placeholder="12-digit Aadhaar" maxlength="12" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>

                            <!-- PAN Card Number -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>PAN Number / पैन सं.</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-credit-card text-muted"></i></span>
                                    <input type="text" class="form-control-compact text-uppercase" id="pan_number" name="pan_number" 
                                           value="{{ old('pan_number', $data->pan_number ?? '') }}" placeholder="e.g. ABCDE1234F" maxlength="10">
                                </div>
                            </div>

                            <!-- Emergency Contact Number -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Emergency Contact / आपातकालीन सं.</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-phone text-danger"></i></span>
                                    <input type="text" class="form-control-compact" id="emergency_contact_no" name="emergency_contact_no" 
                                           value="{{ old('emergency_contact_no', $data->emergency_contact_no ?? '') }}" placeholder="10-digit Mobile" maxlength="10" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>

                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 3: ACADEMIC & QUALIFICATIONS       -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-graduation-cap"></i> 3. Academic & Professional Qualifications / शैक्षणिक योग्यता
                            </span>
                            <span class="form-section-badge">Degree & Experience</span>
                        </div>

                        <div class="row px-1">
                            <!-- Highest Qualification -->
                            <div class="col-lg-4 col-md-6 col-sm-12 compact-form-group">
                                <label>Highest Qualification / उच्चतम योग्यता</label>
                                <input type="text" class="form-control-compact" id="qualification" name="qualification" 
                                       value="{{ old('qualification', $data->qualification ?? '') }}" placeholder="e.g. Ph.D, M.Sc, M.Com, B.Ed, 12th Pass, 10th Pass">
                            </div>

                            <!-- Work Experience -->
                            <div class="col-lg-4 col-md-6 col-sm-12 compact-form-group">
                                <label>Total Work Experience / कुल कार्य अनुभव</label>
                                <input type="text" class="form-control-compact" id="work_experience" name="work_experience" 
                                       value="{{ old('work_experience', $data->work_experience ?? '') }}" placeholder="e.g. 5 Years in University / College">
                            </div>

                            <!-- Father's Contact / Alternate Phone -->
                            <div class="col-lg-4 col-md-6 col-sm-12 compact-form-group">
                                <label>Alternate Phone / वैकल्पिक संपर्क</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-phone text-muted"></i></span>
                                    <input type="text" class="form-control-compact" id="father_mobile" name="father_mobile" 
                                           value="{{ old('father_mobile', $data->father_mobile ?? '') }}" placeholder="10-digit Phone" maxlength="10" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 4: CONTACT & ADDRESS DETAILS       -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-address-book"></i> 4. Contact & Address Details / संपर्क एवं पता विवरण
                            </span>
                            <span class="form-section-badge">Location & Communication</span>
                        </div>

                        <div class="row px-1">

                            <!-- Primary Mobile -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Mobile No.') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-mobile text-success"></i></span>
                                    <input type="text" class="form-control-compact @error('mobile') is-invalid @enderror" 
                                           id="mobile" name="mobile" value="{{ old('mobile', $data->mobile ?? '') }}" placeholder="10-digit Mobile No." 
                                           maxlength="10" onkeypress="javascript:return isNumber(event)" required>
                                </div>
                                @error('mobile')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email Address -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Email') }}</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control-compact @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $data->email ?? '') }}" placeholder="e.g. staff@college.edu">
                                </div>
                                @error('email')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- State -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.State') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact select2 @error('state') is-invalid @enderror" id="state_id" name="state" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getState)) 
                                        @foreach($getState as $state)
                                            <option value="{{ $state->id ?? '' }}" {{ (old('state', $data->state_id ?? ($getSetting->state_id ?? '')) == $state->id) ? 'selected' : '' }}>
                                                {{ $state->name ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('state')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.City') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact select2 @error('city') is-invalid @enderror" name="city" id="city_id" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getCity)) 
                                        @foreach($getCity as $cities)
                                            <option value="{{ $cities->id ?? '' }}" {{ (old('city', $data->city_id ?? ($getSetting->city_id ?? '')) == $cities->id) ? 'selected' : '' }}>
                                                {{ $cities->name ?? '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('city')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Pincode -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>Pincode / पिन कोड</label>
                                <input type="text" class="form-control-compact" id="pincode" name="pincode" 
                                       value="{{ old('pincode', $data->pincode ?? '') }}" placeholder="6-digit Pincode" maxlength="6" onkeypress="javascript:return isNumber(event)">
                            </div>

                            <!-- Current Address -->
                            <div class="col-lg-6 col-md-6 col-sm-12 compact-form-group">
                                <label>{{ __('common.Address') }} (Current / पत्राचार पता) <span class="req-star">*</span></label>
                                <textarea class="form-control-compact @error('address') is-invalid @enderror" id="address" name="address" 
                                          rows="2" placeholder="House/Flat No., Street, Landmark, Area" required>{{ old('address', $data->address ?? '') }}</textarea>
                                @error('address')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Permanent Address -->
                            <div class="col-lg-6 col-md-6 col-sm-12 compact-form-group">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="mb-0">Permanent Address / स्थायी पता</label>
                                    <label class="custom-check-pill" style="font-size: 10px; font-weight: 500;">
                                        <input type="checkbox" id="same_as_current"> Same as Current Address
                                    </label>
                                </div>
                                <textarea class="form-control-compact" id="permanent_address" name="permanent_address" 
                                          rows="2" placeholder="Village/Town, Tehsil, District, State">{{ old('permanent_address', $data->permanent_address ?? '') }}</textarea>
                            </div>

                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 5: PAYROLL & BANKING DETAILS       -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-money"></i> 5. Payroll & Banking Information / वेतन एवं बैंक खाता विवरण
                            </span>
                            <span class="form-section-badge">Salary & PF Accounts</span>
                        </div>

                        <div class="row px-1">

                            <!-- Monthly Salary / Basic Pay -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>{{ __('common.Salary') }} / Basic Pay (₹)</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon font-weight-bold text-success">₹</span>
                                    <input type="text" class="form-control-compact" id="salary" name="salary" 
                                           value="{{ old('salary', $data->salary ?? ($data->basic_salary ?? '')) }}" placeholder="Monthly CTC / Basic" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>

                            <!-- Bank Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>Bank Name / बैंक का नाम</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-university text-primary"></i></span>
                                    <input type="text" class="form-control-compact" id="bank_name" name="bank_name" 
                                           value="{{ old('bank_name', $data->bank_name ?? '') }}" placeholder="e.g. State Bank of India, HDFC">
                                </div>
                            </div>

                            <!-- Account Number -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>Bank Account No. / खाता सं.</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-credit-card text-secondary"></i></span>
                                    <input type="text" class="form-control-compact" id="account_no" name="account_no" 
                                           value="{{ old('account_no', $data->account_no ?? '') }}" placeholder="Account Number" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>

                            <!-- IFSC Code -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>IFSC Code / आईएफएससी कोड</label>
                                <input type="text" class="form-control-compact text-uppercase" id="ifsc_code" name="ifsc_code" 
                                       value="{{ old('ifsc_code', $data->ifsc_code ?? '') }}" placeholder="e.g. SBIN0001234">
                            </div>

                            <!-- Branch Name -->
                            <div class="col-lg-2 col-md-3 col-sm-6 compact-form-group">
                                <label>Bank Branch / शाखा</label>
                                <input type="text" class="form-control-compact" id="bank_branch" name="bank_branch" 
                                       value="{{ old('bank_branch', $data->bank_branch ?? '') }}" placeholder="Branch Location">
                            </div>

                            <!-- EPF Number -->
                            <div class="col-lg-3 col-md-3 col-sm-6 compact-form-group">
                                <label>EPF / PF Account No.</label>
                                <input type="text" class="form-control-compact" id="epf_no" name="epf_no" 
                                       value="{{ old('epf_no', $data->epf_no ?? '') }}" placeholder="Provident Fund Number">
                            </div>

                            <!-- ESI Number -->
                            <div class="col-lg-3 col-md-3 col-sm-6 compact-form-group">
                                <label>ESI Account No.</label>
                                <input type="text" class="form-control-compact" id="esi_no" name="esi_no" 
                                       value="{{ old('esi_no', $data->esi_no ?? '') }}" placeholder="ESI Registration Number">
                            </div>

                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 6: LOGIN & SYSTEM CREDENTIALS      -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-lock"></i> 6. System Login & Security Credentials / लॉगिन एवं सुरक्षा विवरण
                            </span>
                            <span class="form-section-badge">{{ $isEdit ? 'Update Password (Optional)' : 'ERP Authentication' }}</span>
                        </div>

                        <div class="row px-1">

                            <!-- User Name -->
                            <div class="col-lg-4 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('user.User Name') }} / Login ID <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-user-circle text-primary"></i></span>
                                    <input type="text" class="form-control-compact @error('userName') is-invalid @enderror" 
                                           id="userName" name="userName" value="{{ old('userName', $data->userName ?? '') }}" placeholder="Unique Username / ID" required>
                                </div>
                                @error('userName')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="col-lg-4 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Password') }} @if(!$isEdit)<span class="req-star">*</span>@endif</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-key text-warning"></i></span>
                                    <input type="password" class="form-control-compact @error('password') is-invalid @enderror" 
                                           id="password" name="password" value="{{ old('password') }}" 
                                           placeholder="{{ $isEdit ? 'Leave blank to keep current password' : 'Minimum 4 characters' }}" 
                                           {{ $isEdit ? '' : 'required' }}>
                                </div>
                                @error('password')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-lg-4 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Confirm Password') }} @if(!$isEdit)<span class="req-star">*</span>@endif</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-check text-success"></i></span>
                                    <input type="password" class="form-control-compact @error('confirm_password') is-invalid @enderror" 
                                           id="confirm_password" name="confirm_password" value="{{ old('confirm_password') }}" 
                                           placeholder="{{ $isEdit ? 'Leave blank to keep current password' : 'Re-enter Password' }}" 
                                           {{ $isEdit ? '' : 'required' }}>
                                </div>
                                @error('confirm_password')
                                    <span class="text-danger" style="font-size: 10px;">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 7: PHOTO & DOCUMENT ATTACHMENTS    -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-paperclip"></i> 7. Photo & Document Attachments / फोटो एवं दस्तावेज
                            </span>
                            <span class="form-section-badge">KYC & Certifications</span>
                        </div>

                        <div class="row px-1">

                            <!-- Profile Photo -->
                            <div class="col-lg-3 col-md-6 col-sm-12 compact-form-group">
                                <div class="photo-upload-box">
                                    @php
                                        $userImg = !empty($data->image) ? env('IMAGE_SHOW_PATH').'profile/'.$data->image : env('IMAGE_SHOW_PATH').'default/user_image.jpg';
                                    @endphp
                                    <img id="photo_preview" src="{{ $userImg }}" 
                                         onerror="this.src='{{ asset('assets/images/user-default.png') }}'" class="photo-preview-thumb" alt="Preview">
                                    <div class="photo-upload-input">
                                        <label class="font-weight-bold mb-1" style="font-size: 11px;">
                                            <i class="fa fa-camera text-primary"></i> {{ __('common.Photo') }} (Max 2MB)
                                        </label>
                                        <input type="file" class="form-control-file" id="photo" name="photo" 
                                               accept="image/png, image/jpg, image/jpeg" style="font-size: 10.5px;">
                                        <span class="text-danger" id="image_error" style="font-size: 9.5px;"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- ID Proof Document -->
                            <div class="col-lg-3 col-md-6 col-sm-12 compact-form-group">
                                <div class="doc-upload-tile">
                                    <label>
                                        <span><i class="fa fa-id-card-o text-info"></i> ID Proof (Aadhaar/Voter)</span>
                                        @if(!empty($data->id_proof_doc))
                                            <a href="{{ env('IMAGE_SHOW_PATH').'profile/'.$data->id_proof_doc }}" target="_blank" class="badge badge-info" style="font-size: 9px;"><i class="fa fa-external-link"></i> View</a>
                                        @endif
                                    </label>
                                    <input type="file" class="form-control-file" id="id_proof_doc" name="id_proof_doc" 
                                           accept=".pdf,.jpg,.jpeg,.png" style="font-size: 10.5px;">
                                </div>
                            </div>

                            <!-- Qualification Certificate -->
                            <div class="col-lg-3 col-md-6 col-sm-12 compact-form-group">
                                <div class="doc-upload-tile">
                                    <label>
                                        <span><i class="fa fa-certificate text-warning"></i> Qualification Doc</span>
                                        @if(!empty($data->qualification_doc))
                                            <a href="{{ env('IMAGE_SHOW_PATH').'profile/'.$data->qualification_doc }}" target="_blank" class="badge badge-warning text-dark" style="font-size: 9px;"><i class="fa fa-external-link"></i> View</a>
                                        @endif
                                    </label>
                                    <input type="file" class="form-control-file" id="qualification_doc" name="qualification_doc" 
                                           accept=".pdf,.jpg,.jpeg,.png" style="font-size: 10.5px;">
                                </div>
                            </div>

                            <!-- Resume / Bio-Data -->
                            <div class="col-lg-3 col-md-6 col-sm-12 compact-form-group">
                                <div class="doc-upload-tile">
                                    <label>
                                        <span><i class="fa fa-file-text-o text-secondary"></i> Resume / CV / Other</span>
                                        @if(!empty($data->resume_doc))
                                            <a href="{{ env('IMAGE_SHOW_PATH').'profile/'.$data->resume_doc }}" target="_blank" class="badge badge-secondary" style="font-size: 9px;"><i class="fa fa-external-link"></i> View</a>
                                        @endif
                                    </label>
                                    <input type="file" class="form-control-file" id="resume_doc" name="resume_doc" 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="font-size: 10.5px;">
                                </div>
                            </div>

                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 8: EASY-PICK MODULE PERMISSION MATRIX -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-shield"></i> 8. Module & Sub-Panel Access Permissions / मॉड्यूल एवं उप-पैनल अधिकार
                            </span>
                            <span class="form-section-badge">Visual Permission Matrix</span>
                        </div>

                        <div class="perm-matrix-wrapper">
                            
                            <!-- Matrix Control Toolbar -->
                            <div class="perm-toolbar">
                                <div class="d-flex align-items-center gap-3">
                                    <label class="custom-check-pill font-weight-bold" style="font-size: 12px; color: var(--green-excel);">
                                        <input type="checkbox" id="select_all_permissions"> 
                                        <span>Select All Modules & Features / सभी मॉड्यूल एवं उप-पैनल चुनें</span>
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline-secondary ml-2 py-0 px-2" id="btn_clear_all_perms" style="font-size: 10px;">
                                        <i class="fa fa-times"></i> Clear All
                                    </button>
                                </div>

                                <!-- Live Search / Filter -->
                                <div class="d-flex align-items-center gap-2">
                                    <div class="compact-input-group" style="width: 220px;">
                                        <span class="compact-addon-icon"><i class="fa fa-search text-muted"></i></span>
                                        <input type="text" id="filter_permissions" class="form-control-compact" placeholder="Filter module / feature...">
                                    </div>
                                </div>

                                <!-- Action Rights Badges -->
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted font-weight-bold mr-1" style="font-size: 10.5px;">Action Rights:</span>
                                    <label class="perm-action-badge">
                                        <input type="checkbox" id="edit" name="edit" value="1" {{ (!empty($add_pr->edit) && $add_pr->edit == 1) ? 'checked' : '' }}> 
                                        <span class="badge-action-pill badge-edit"><i class="fa fa-pencil"></i> Edit Access</span>
                                    </label>
                                    <label class="perm-action-badge">
                                        <input type="checkbox" id="delete" name="delete" value="1" {{ (!empty($add_pr->deletes) && $add_pr->deletes == 1) ? 'checked' : '' }}> 
                                        <span class="badge-action-pill badge-delete"><i class="fa fa-trash"></i> Delete Access</span>
                                    </label>
                                    <label class="perm-action-badge">
                                        <input type="checkbox" id="download" name="download" value="1" {{ (!empty($add_pr->download) && $add_pr->download == 1) ? 'checked' : '' }}> 
                                        <span class="badge-action-pill badge-download"><i class="fa fa-download"></i> Download Access</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Modules Hierarchical Cards Grid -->
                            <div class="row" id="permission_cards_container">
                                @if(!empty($sidebar))
                                    @foreach($sidebar as $sdata)
                                        @foreach($allPermisn as $permisnData)
                                            @if($sdata['id'] == $permisnData)
                                                @php
                                                    $subItems = $subSidebarsGrouped[$sdata['id']] ?? collect();
                                                    $allowedSubs = $subItems->filter(function($sub) use ($allowSubSidebar) {
                                                        return in_array($sub->id, $allowSubSidebar);
                                                    });
                                                    $moduleTitle = ($isHindi && !empty($sdata['hindi_name'])) ? $sdata['hindi_name'] : ($sdata['name'] ?? 'Module');
                                                    $isParentAssigned = in_array($sdata['id'], $assignedSidebarIds);
                                                @endphp
                                                <div class="col-xl-4 col-lg-6 col-md-6 col-12 perm-card-col" data-module-name="{{ strtolower($moduleTitle) }}">
                                                    <div class="perm-module-card {{ $isParentAssigned ? 'is-selected' : '' }}" id="card_module_{{ $sdata['id'] }}">
                                                        
                                                        <!-- Module Header -->
                                                        <div class="perm-module-header">
                                                            <label class="perm-module-title-label">
                                                                <input type="checkbox" name="sidebar_id[]" class="parent-module-cb" 
                                                                       id="sidebar_{{ $sdata['id'] }}" data-module-id="{{ $sdata['id'] }}" 
                                                                       value="{{ $sdata['id'] }}" {{ $isParentAssigned ? 'checked' : '' }}>
                                                                <i class="fa {{ $sdata->ican ?? 'fa-cube' }} text-success"></i>
                                                                <span>{{ $moduleTitle }}</span>
                                                            </label>
                                                            <span class="perm-count-badge">
                                                                {{ $allowedSubs->count() }} Features
                                                            </span>
                                                        </div>

                                                        <!-- Module Sub-Features Checklist -->
                                                        <div class="perm-module-body">
                                                            @if($allowedSubs->count() > 0)
                                                                <div class="sub-features-grid">
                                                                    @foreach($allowedSubs as $subItem)
                                                                        @php
                                                                            $isSubAssigned = in_array($subItem->id, $assignedSubSidebarIds);
                                                                        @endphp
                                                                        <div class="sub-feature-item {{ $isSubAssigned ? 'is-checked' : '' }}" data-sub-name="{{ strtolower($subItem->name ?? '') }}">
                                                                            <label class="sub-feature-label">
                                                                                <input type="checkbox" name="sidebar_sub_id[]" 
                                                                                       class="sub-module-cb sub_of_{{ $sdata['id'] }}" 
                                                                                       data-parent-id="{{ $sdata['id'] }}" 
                                                                                       id="sub_{{ $subItem->id }}" 
                                                                                       value="{{ $subItem->id }}" 
                                                                                       {{ $isSubAssigned ? 'checked' : '' }}>
                                                                                <i class="fa {{ $subItem->ican ?? 'fa-circle-o' }} sub-feature-icon"></i>
                                                                                <span class="sub-feature-name">{{ $subItem->name ?? '' }}</span>
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="text-muted text-center py-2" style="font-size: 10.5px;">
                                                                    <i class="fa fa-check-circle text-success mr-1"></i> Direct Module Access (No Sub-panels)
                                                                </div>
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endif
                            </div>

                            @error('sidebar_id')
                                <div class="alert alert-danger py-1 px-2 mt-2 mb-0" style="font-size: 11px;">
                                    <i class="fa fa-exclamation-triangle mr-1"></i> Please select at least one module or permission.
                                </div>
                            @enderror

                        </div>

                    </div>

                    <!-- Executive Bottom Action Bar -->
                    <div class="dash-form-footer">
                        <div>
                            <span class="text-muted" style="font-size: 11px;">
                                <i class="fa fa-info-circle text-success mr-1"></i> Ensure all mandatory fields marked with (<span class="req-star">*</span>) are completed accurately.
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="reset" class="btn-form-reset">
                                <i class="fa fa-refresh mr-1"></i> Reset Form
                            </button>
                            <button type="submit" class="btn-submit-save">
                                <i class="fa {{ $isEdit ? 'fa-save' : 'fa-check-circle' }}"></i> {{ $isEdit ? 'Update User / Staff Profile' : 'Save User / Staff Profile' }}
                            </button>
                        </div>
                    </div>

                </form>

            </div>

        </div>
    </section>
</div>

<!-- ========================================== -->
<!-- QUICK ADD ROLE MODAL                       -->
<!-- ========================================== -->
<div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content" style="border-radius: 4px; overflow: hidden; border: 1px solid var(--line);">
            <div class="modal-header" style="background: var(--green-excel); color: #ffffff; padding: 8px 12px;">
                <h6 class="modal-title font-weight-bold" id="addRoleModalLabel" style="font-size: 13px;">
                    <i class="fa fa-plus-circle text-warning mr-1"></i> Add Dynamic Role
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size: 18px; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" style="font-size: 11.5px;">
                <div class="form-group mb-2">
                    <label class="font-weight-bold text-dark mb-1">Role Name / पद का नाम <span class="req-star">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="new_role_name" placeholder="e.g. Sweeper, Lab Assistant, HOD">
                    <span class="text-danger d-none" id="role_error_msg" style="font-size: 10px;">Role name is required!</span>
                </div>
            </div>
            <div class="modal-footer bg-light p-2 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="font-size: 11px;">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSaveQuickRole" style="background: var(--green-excel); border-color: var(--green-excel-dark); font-size: 11px;">
                    <i class="fa fa-save mr-1"></i> Save Role
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
$(document).ready(function(){
    // Initialize Select2 elements
    if ($('.select2').length) {
        $('.select2').select2({
            width: '100%'
        });
    }

    // Photo Preview & Validation
    $('#photo').change(function(e){
        $('#image_error').html("");
        var file = e.target.files[0];
        if (file) {
            var fileName = file.name;
            var extension = fileName.split('.').pop().toLowerCase();
            if (['png', 'jpg', 'jpeg'].includes(extension)) {
                if (file.size > (2 * 1024 * 1024)) {
                    $('#image_error').html("Image size must be under 2MB");
                    $(this).val('');
                } else {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#photo_preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                $('#image_error').html("Please select a valid image file (PNG, JPG, JPEG)");
                $(this).val('');
            }
        }
    });

    // Same as Current Address Checkbox Toggle
    $('#same_as_current').change(function(){
        if ($(this).is(':checked')) {
            $('#permanent_address').val($('#address').val());
        } else {
            $('#permanent_address').val('');
        }
    });

    $('#address').on('input', function(){
        if ($('#same_as_current').is(':checked')) {
            $('#permanent_address').val($(this).val());
        }
    });

    /* ========================================================
       PERMISSIONS HIERARCHICAL SELECTION LOGIC
       ======================================================== */

    // Update Card Highlighting
    function updateCardHighlight(moduleId) {
        var $card = $('#card_module_' + moduleId);
        var isParentChecked = $('#sidebar_' + moduleId).is(':checked');
        var hasSubChecked = $('.sub_of_' + moduleId + ':checked').length > 0;

        if (isParentChecked || hasSubChecked) {
            $card.addClass('is-selected');
        } else {
            $card.removeClass('is-selected');
        }
    }

    // 1. Parent Module Checkbox Change
    $(document).on('change', '.parent-module-cb', function(){
        var moduleId = $(this).data('module-id');
        var isChecked = $(this).is(':checked');

        // Check/uncheck all sub-modules under this module
        $('.sub_of_' + moduleId).prop('checked', isChecked).each(function(){
            if (isChecked) {
                $(this).closest('.sub-feature-item').addClass('is-checked');
            } else {
                $(this).closest('.sub-feature-item').removeClass('is-checked');
            }
        });

        updateCardHighlight(moduleId);
    });

    // 2. Child Sub-Module Checkbox Change
    $(document).on('change', '.sub-module-cb', function(){
        var parentId = $(this).data('parent-id');
        var isChecked = $(this).is(':checked');

        if (isChecked) {
            $(this).closest('.sub-feature-item').addClass('is-checked');
            // Auto check parent module
            $('#sidebar_' + parentId).prop('checked', true);
        } else {
            $(this).closest('.sub-feature-item').removeClass('is-checked');
        }

        updateCardHighlight(parentId);
    });

    // 3. Global "Select All" Toggle
    $('#select_all_permissions').change(function(){
        var isAllChecked = $(this).is(':checked');
        $('.parent-module-cb').prop('checked', isAllChecked);
        $('.sub-module-cb').prop('checked', isAllChecked);

        if (isAllChecked) {
            $('.sub-feature-item').addClass('is-checked');
            $('.perm-module-card').addClass('is-selected');
        } else {
            $('.sub-feature-item').removeClass('is-checked');
            $('.perm-module-card').removeClass('is-selected');
        }
    });

    // 4. "Clear All" Button
    $('#btn_clear_all_perms').click(function(){
        $('#select_all_permissions').prop('checked', false);
        $('.parent-module-cb').prop('checked', false);
        $('.sub-module-cb').prop('checked', false);
        $('.sub-feature-item').removeClass('is-checked');
        $('.perm-module-card').removeClass('is-selected');
    });

    // 5. Live Search / Filter for Permissions
    $('#filter_permissions').on('keyup input', function(){
        var query = $(this).val().toLowerCase().trim();

        if (!query) {
            $('.perm-card-col').show();
            $('.sub-feature-item').show();
            return;
        }

        $('.perm-card-col').each(function(){
            var moduleName = $(this).data('module-name') || '';
            var hasMatchingSub = false;

            $(this).find('.sub-feature-item').each(function(){
                var subName = $(this).data('sub-name') || '';
                if (subName.indexOf(query) > -1 || moduleName.indexOf(query) > -1) {
                    $(this).show();
                    hasMatchingSub = true;
                } else {
                    $(this).hide();
                }
            });

            if (moduleName.indexOf(query) > -1 || hasMatchingSub) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 6. Dynamic Role Change -> Auto Select Role Permissions (when adding or changing role)
    $("#role_id").change(function() {
        var baseUrl = "{{ url('/') }}";
        var roleId = $(this).val();
        if (!roleId) return;

        // If editing and role wasn't changed from original, skip auto-reset unless confirmed
        @if($isEdit)
        if (!confirm('Apply default permissions for the selected role? Existing custom permissions will be updated.')) {
            return;
        }
        @endif

        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });

        $.ajax({
            type: "POST",
            url: baseUrl + "/user_side_per",
            data: { role_id: roleId },
            dataType: "html",
            success: function (response) {
                // Clear existing
                $('.parent-module-cb').prop('checked', false);
                $('.sub-module-cb').prop('checked', false);
                $('.sub-feature-item').removeClass('is-checked');
                $('.perm-module-card').removeClass('is-selected');

                if (response) {
                    var words = response.split(",");
                    for (var i = 0; i < words.length; i++) {
                        var id = words[i].trim();
                        if (id) {
                            var $parentCb = $("#sidebar_" + id);
                            if ($parentCb.length) {
                                $parentCb.prop('checked', true);
                                $('.sub_of_' + id).prop('checked', true).each(function(){
                                    $(this).closest('.sub-feature-item').addClass('is-checked');
                                });
                                updateCardHighlight(id);
                            }
                        }
                    }
                }
            }
        });
    });

    // Quick Add Role via Modal
    $('#btnSaveQuickRole').click(function(){
        var roleName = $('#new_role_name').val().trim();
        if (!roleName) {
            $('#role_error_msg').removeClass('d-none');
            return;
        }
        $('#role_error_msg').addClass('d-none');

        $.ajax({
            url: "{{ url('quick_add_role') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: roleName
            },
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    var newOption = new Option(response.name, response.id, true, true);
                    $('#role_id').append(newOption).trigger('change');
                    $('#new_role_name').val('');
                    $('#addRoleModal').modal('hide');
                }
            },
            error: function() {
                alert('Could not add role. Please try again.');
            }
        });
    });

    // State to City Dynamic Cascading
    $('#state_id').change(function(){
        var stateId = $(this).val();
        if (stateId) {
            $.ajax({
                url: "{{ url('stateData') }}/" + stateId,
                type: 'GET',
                success: function(data){
                    $('#city_id').html(data);
                    if ($('#city_id').hasClass('select2')) {
                        $('#city_id').select2('destroy').select2({ width: '100%' });
                    }
                }
            });
        }
    });

});

// Helper for numeric inputs
function isNumber(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
</script>

@endsection