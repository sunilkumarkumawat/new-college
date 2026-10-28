@php
$isEdit = isset($data) && !empty($data->id);
$getstudents = Helper::getstudents();
$getgenders = Helper::getgender();
$getCountry = Helper::getCountry();
$getSetting = Helper::getSetting();
$bloodGroupType = Helper::bloodGroupType();
$getAdmissionDatatableFields = Helper::getAdmissionDatatableFields();
$list = DB::table('custom_villages_list')->orderBy('name','ASC')->whereNull('deleted_at')->get();
$courses = DB::table('courses')->where('branch_id', Session::get('branch_id'))->get();
$batches = DB::table('batches')->get();
$session = DB::table('sessions')->whereNull('deleted_at')->where('id', Session::get('session_id') ?? '')->first();

// Selected Course and Classes
$selectedCourse = old('course', $data->course ?? '');
$classType = !empty($selectedCourse) ? Helper::classType($selectedCourse) : Helper::classType();

// Selected Country, State, City resolution
$selectedCountryId = old('country', $data->country_id ?? ($getSetting->country_id ?? ''));
$selectedStateId = old('state', $data->state_id ?? ($getSetting->state_id ?? ''));

$getState = (isset($getState) && count($getState) > 0) 
    ? $getState 
    : (!empty($selectedCountryId) ? \App\Models\State::where('country_id', $selectedCountryId)->get() : Helper::getState());

$getCity = (isset($getCity) && count($getCity) > 0) 
    ? $getCity 
    : (!empty($selectedStateId) ? \App\Models\City::where('state_id', $selectedStateId)->get() : Helper::getCity());

$streamSubjectArray = [];
if ($isEdit && !empty($data->stream_subject)) {
    $streamSubjectArray = is_array($data->stream_subject) ? $data->stream_subject : explode(',', $data->stream_subject);
}
$streamSubjects = ($isEdit && !empty($data->class_type_id)) ? Helper::getStreamSubjects($data->class_type_id) : collect();
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   COMPACT EXECUTIVE ADMISSION FORM THEME
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

.admission-compact-page {
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

/* Strict Select2 Full Width & Compact Height Override */
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

.select2-container--default .select2-selection--multiple {
    min-height: 28px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    padding: 1px 4px !important;
    width: 100% !important;
}

/* Upload Card/Box Style */
.photo-upload-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px dashed var(--line-subtle);
    border-radius: 3px;
    padding: 5px 8px;
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

/* 5. Bottom Action Bar */
.dash-form-footer {
    padding: 7px 14px;
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
    padding: 4px 18px !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.25);
    cursor: pointer;
    line-height: 1.4;
}
.btn-submit-save:hover {
    background: var(--green-excel-dark) !important;
    box-shadow: 0 2px 6px rgba(33, 115, 70, 0.35);
    transform: translateY(-1px);
}

.btn-reset-clean {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 12px !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
    line-height: 1.4;
}
.btn-reset-clean:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.invalid-feedback-compact {
    display: none;
    font-size: 10.5px;
    color: #dc2626;
    margin-top: 1px;
    font-weight: 600;
}
</style>

<div class="content-wrapper admission-compact-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            
            <div class="card dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-address-book-o"></i>
                        <span>{{ __('student.Students Admission Management') }}</span>
                        <small>| {{ $isEdit ? __('messages.Edit Students Admission') : 'New Admission Entry' }}</small>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('admissionView') }}" class="btn-header-action" title="View Students Admission">
                            <i class="fa fa-eye text-primary mr-1"></i> {{ __('common.View') }} Admissions
                        </a>
                        <a href="{{ url('studentsDashboard') }}" class="btn-header-action" title="Back to Students Dashboard">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Main Form Body -->
                <form id="quickForm_addmission" action="{{ $isEdit ? url('admissionEdit/' . $data->id) : url('admissionAdd') }}" method="post" enctype="multipart/form-data" class="mb-0">
                    @csrf
                    @if($isEdit)
                        <input type="hidden" id="admission_id" value="{{ $data->id ?? '' }}">
                        <input type="hidden" name="update" id="update_action_input" value="">
                    @endif

                    <div class="p-2">
                        
                        <!-- ========================================== -->
                        <!-- SECTION 1: ACADEMIC DETAILS                -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-graduation-cap"></i> 1. Academic Details / शैक्षणिक विवरण
                            </span>
                            <span class="form-section-badge" id="section_admission_no_badge">
                                @if($isEdit)
                                    Student ID: #{{ $data->admissionNo ?? $data->id }} &bull; {{ $data->first_name ?? '' }} {{ $data->last_name ?? '' }}
                                @else
                                    Admission No: {{ old('admissionNo', 'Auto') }}
                                @endif
                            </span>
                        </div>

                        <div class="row px-1">
                            <!-- Course -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Course') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact invalid select2" id="course" name="course" required>
                                    <option value="">{{ __('common.Select') }} Course</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $course)
                                        <option value="{{ $course->name ?? '' }}" data-id="{{ $course->id ?? '' }}" data-duration="{{ $course->duration ?? 1 }}" data-semester="{{ $course->total_semester ?? 0 }}" data-type="{{ $course->course_type ?? 'Semester' }}" {{ ($course->name == old('course', $data->course ?? '')) ? 'selected' : '' }}>
                                            {{ $course->name ?? '' }} ({{ $course->duration ?? 1 }} {{ ($course->duration > 1) ? 'Yrs' : 'Yr' }}{{ ($course->course_type == 'Semester' && $course->total_semester > 0) ? ' / ' . $course->total_semester . ' Sem' : '' }})
                                        </option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="invalid-feedback-compact" id="course_invalid">The Course field is required</span>
                            </div>

                            <!-- Class / Semester -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Class / Semester') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact invalid select2" id="class_type_id" name="class_type_id" required>
                                    <option value="">{{ __('common.Select') }} Class / Semester</option>
                                    @if(!empty($classType))
                                        @foreach($classType as $type)
                                        <option value="{{ $type->id ?? '' }}" data-orderBy="{{ $type->orderBy ?? '' }}" {{ ($type->id == old('class_type_id', $data->class_type_id ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="invalid-feedback-compact" id="class_type_id_invalid">The Class / Semester field is required</span>
                            </div>

                            <!-- Batch -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Batch') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-calendar-check-o text-success"></i></span>
                                    <input type="text" class="form-control-compact font-weight-bold text-success" id="batch" name="batch" 
                                           placeholder="Auto-calculated" value="{{ old('batch', $data->batch ?? '') }}" readonly>
                                </div>
                            </div>

                            <!-- Student Type -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Student Type') }} <span class="req-star">*</span></label>
                                @php $currStudentType = old('student_type', $data->student_type ?? 'Regular'); @endphp
                                <select class="form-control-compact select2" id="student_type" name="student_type">
                                    <option value="Regular" {{ ($currStudentType == 'Regular') ? 'selected' : '' }}>Regular</option>
                                    <option value="Management" {{ ($currStudentType == 'Management') ? 'selected' : '' }}>Management</option>
                                    <option value="Govt" {{ ($currStudentType == 'Govt') ? 'selected' : '' }}>Govt.</option>
                                    <option value="NRI" {{ ($currStudentType == 'NRI') ? 'selected' : '' }}>NRI</option>
                                </select>
                            </div>

                            <!-- Roll No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Roll No') }}</label>
                                <input type="text" class="form-control-compact" name="roll_no" id="roll_no" placeholder="Roll No" value="{{ old('roll_no', $data->roll_no ?? '') }}">
                            </div>

                            <!-- Admission No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('student.Admission No') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-id-card-o text-primary"></i></span>
                                    <input type="text" class="form-control-compact font-weight-bold text-primary" id="admissionNo" name="admissionNo" 
                                           placeholder="Auto-calculated" value="{{ old('admissionNo', $isEdit ? ($data->admissionNo ?? '') : '') }}" readonly required>
                                </div>
                            </div>

                            <!-- Date of Admission -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('student.Date Of Admission') }}</label>
                                <input type="date" class="form-control-compact" id="admission_date" name="admission_date" value="{{ old('admission_date', ($isEdit && !empty($data->admission_date) && $data->admission_date != '1970-01-01') ? date('Y-m-d', strtotime($data->admission_date)) : date('Y-m-d')) }}">
                            </div>

                            <!-- Enrollment No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>Enrollment No</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-id-badge text-muted"></i></span>
                                    <input type="text" class="form-control-compact" id="enrollment_no" name="enrollment_no" 
                                           placeholder="Enter Enrollment No" value="{{ old('enrollment_no', $data->enrollment_no ?? '') }}">
                                </div>
                            </div>

                            <!-- Web Registration No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>Web Registration No</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-globe text-muted"></i></span>
                                    <input type="text" class="form-control-compact" id="web_registration_no" name="web_registration_no" 
                                           placeholder="Enter Web Reg. No" value="{{ old('web_registration_no', $data->web_registration_no ?? '') }}">
                                </div>
                            </div>

                            <!-- Agent / Referrer with Quick Add -->
                            <div class="col-lg-4 col-md-8 col-sm-12 compact-form-group">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="mb-0">Agent / Referrer</label>
                                    <button type="button" class="btn btn-xs btn-outline-success font-weight-bold px-2 py-0" style="font-size: 10.5px; line-height: 1.3;" data-toggle="modal" data-target="#quickAgentModal" data-bs-toggle="modal" data-bs-target="#quickAgentModal">
                                        <i class="fa fa-plus-circle"></i> Quick Add Agent
                                    </button>
                                </div>
                                <select class="form-control-compact select2" id="agent_id" name="agent_id">
                                    <option value="">{{ __('common.Select') }} Agent</option>
                                    @php
                                        $activeAgents = \App\Models\Agent::getActiveAgents();
                                    @endphp
                                    @if(!empty($activeAgents))
                                        @foreach($activeAgents as $agent)
                                            <option value="{{ $agent->id }}" {{ (old('agent_id', $data->agent_id ?? '') == $agent->id) ? 'selected' : '' }}>
                                                {{ $agent->name }} @if(!empty($agent->mobile))({{ $agent->mobile }})@endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Stream Subject (Conditional) -->
                            <div class="col-lg-8 col-md-6 col-sm-12 compact-form-group" id="stream_subject_div" style="display:{{ ($isEdit && !empty($data->stream_subject)) ? 'block' : 'none' }};">
                                <label>Stream Subject <span class="req-star">*</span></label>
                                <select class="form-control-compact select2" multiple id="stream_subject" name="stream_subject[]">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($streamSubjects))
                                        @foreach($streamSubjects as $subject)
                                            <option value="{{ $subject->id ?? '' }}" {{ in_array($subject->id, $streamSubjectArray) ? 'selected' : '' }}>{{ $subject->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 2: STUDENT & PARENT DETAILS        -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-2">
                            <span class="form-section-title">
                                <i class="fa fa-user"></i> 2. Student & Parent Details / छात्र एवं अभिभावक विवरण
                            </span>
                            <span class="form-section-badge">Personal & Family Info</span>
                        </div>

                        <div class="row px-1">
                            <!-- Student Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Student Name') }} <span class="req-star">*</span></label>
                                <input type="text" name="first_name" id="first_name" class="form-control-compact invalid" 
                                       value="{{ old('first_name', $data->first_name ?? '') }}" placeholder="{{ __('Student Name') }}" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" required>
                                <span class="invalid-feedback-compact" id="first_name_invalid">The Student Name field is required</span>
                            </div>

                            <!-- Father's Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Fathers Name') }} <span class="req-star">*</span></label>
                                <input type="text" class="form-control-compact invalid" id="father_name" name="father_name" 
                                       placeholder="{{ __('common.Fathers Name') }}" value="{{ old('father_name', $data->father_name ?? '') }}" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" required>
                                <span class="invalid-feedback-compact" id="father_name_invalid">The Father's name field is required</span>
                            </div>

                            <!-- Mother's Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Mothers Name') }}</label>
                                <input type="text" class="form-control-compact" id="mother_name" name="mother_name" 
                                       placeholder="{{ __('common.Mothers Name') }}" value="{{ old('mother_name', $data->mother_name ?? '') }}" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)">
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Date Of  Birth') }} <span class="req-star">*</span></label>
                                <input type="date" class="form-control-compact invalid" id="dob" name="dob" value="{{ old('dob', ($isEdit && !empty($data->dob) && $data->dob != '1970-01-01') ? date('Y-m-d', strtotime($data->dob)) : '') }}" required>
                                <span class="invalid-feedback-compact" id="dob_invalid">The Date of Birth field is required</span>
                            </div>

                            <!-- Gender -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Gender') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact invalid select2" id="gender_id" name="gender_id" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getgenders))
                                        @foreach($getgenders as $value)
                                        <option value="{{ $value->id }}" {{ ($value->id == old('gender_id', $data->gender_id ?? '')) ? 'selected' : '' }}>{{ $value->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="invalid-feedback-compact" id="gender_id_invalid">The Gender field is required</span>
                            </div>

                            <!-- Student Mobile No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Mobile No.') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-phone text-success"></i></span>
                                    <input type="text" class="form-control-compact" id="mobile" name="mobile" 
                                           placeholder="10-digit Mobile" value="{{ old('mobile', $data->mobile ?? '') }}" 
                                           maxlength="10" minlength="10" onkeypress="javascript:return isNumber(event)" required>
                                </div>
                                <span class="invalid-feedback-compact" id="mobileValidationMessage">Must be at least 10 digits</span>
                            </div>

                            <!-- Father's Contact No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Fathers Contact No') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-phone text-muted"></i></span>
                                    <input type="text" class="form-control-compact" id="father_mobile" name="father_mobile" 
                                           placeholder="Father's Contact" value="{{ old('father_mobile', $data->father_mobile ?? '') }}" 
                                           maxlength="10" minlength="10" onkeypress="javascript:return isNumber(event)" required>
                                </div>
                                <span class="invalid-feedback-compact" id="fathermobileValidationMessage">Must be at least 10 digits</span>
                            </div>

                            <!-- Category -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>Category</label>
                                @php $currCategory = old('category', $data->category ?? 'OBC'); @endphp
                                <select class="form-control-compact select2" id="category" name="category">
                                    
                                    <option value="GEN" {{ ('GEN' == $currCategory) ? 'selected' : '' }}>GEN</option>
                                    <option value="OBC" {{ ('OBC' == $currCategory) ? 'selected' : '' }}>OBC</option>
                                    <option value="SC" {{ ('SC' == $currCategory) ? 'selected' : '' }}>SC</option>
                                    <option value="ST" {{ ('ST' == $currCategory) ? 'selected' : '' }}>ST</option>
                                    <option value="BC" {{ ('BC' == $currCategory) ? 'selected' : '' }}>BC</option>
                                    <option value="SBC" {{ ('SBC' == $currCategory) ? 'selected' : '' }}>SBC</option>
                                    <option value="Other" {{ ('Other' == $currCategory) ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Religion -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>Religion</label>
                                @php $currReligion = old('religion', $data->religion ?? 'Hindu'); @endphp
                                <select class="form-control-compact select2" id="religion" name="religion">
                                    
                                    <option value="Hindu" {{ ('Hindu' == $currReligion) ? 'selected' : '' }}>Hindu</option>
                                    <option value="Islam" {{ ('Islam' == $currReligion) ? 'selected' : '' }}>Islam</option>
                                    <option value="MUSLIM" {{ ('MUSLIM' == $currReligion) ? 'selected' : '' }}>MUSLIM</option>
                                    <option value="Sikh" {{ ('Sikh' == $currReligion) ? 'selected' : '' }}>Sikh</option>
                                    <option value="Buddhism" {{ ('Buddhism' == $currReligion) ? 'selected' : '' }}>Buddhism</option>
                                    <option value="Jain" {{ ('Jain' == $currReligion) ? 'selected' : '' }}>Jain</option>
                                    <option value="Christianity" {{ ('Christianity' == $currReligion) ? 'selected' : '' }}>Christianity</option>
                                    <option value="Other" {{ ('Other' == $currReligion) ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Blood Group -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Blood Group') }}</label>
                                <select class="form-control-compact select2" id="blood_group" name="blood_group">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($bloodGroupType))
                                        @foreach($bloodGroupType as $bloodtype)
                                        <option value="{{ $bloodtype->name ?? '' }}" {{ ($bloodtype->name == old('blood_group', $data->blood_group ?? '')) ? 'selected' : '' }}>{{ $bloodtype->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Aadhaar No -->
                            <div class="col-lg-3 col-md-6 col-sm-6 compact-form-group">
                                <label>{{ __('common.Aadhaar No.') }}</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-id-card-o text-muted"></i></span>
                                    <input type="text" class="form-control-compact" id="aadhaar" name="aadhaar" 
                                           placeholder="12-digit Aadhaar No." value="{{ old('aadhaar', $data->aadhaar ?? '') }}" 
                                           maxlength="12" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-lg-3 col-md-6 col-sm-6 compact-form-group">
                                <label>{{ __('common.E-Mail') }}</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-envelope-o text-muted"></i></span>
                                    <input type="email" class="form-control-compact" id="email" name="email" 
                                           placeholder="student@example.com" value="{{ old('email', $data->email ?? '') }}">
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 3: ADDRESS DETAILS                 -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-2">
                            <span class="form-section-title">
                                <i class="fa fa-map-marker"></i> 3. Address Details / पते का विवरण
                            </span>
                            <span class="form-section-badge">Location Info</span>
                        </div>

                        <div class="row px-1">
                            <!-- Country -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Country') }}</label>
                                <select class="form-control-compact select2" name="country" id="country_id">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getCountry))
                                        @foreach($getCountry as $country)
                                        <option value="{{ $country->id ?? '' }}" {{ ($country->id == $selectedCountryId) ? 'selected' : '' }}>{{ $country->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- State -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.State') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact stateId select2 invalid" id="state_id" name="state" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getState))
                                        @foreach($getState as $state)
                                        <option value="{{ $state->id ?? '' }}" {{ ($state->id == $selectedStateId) ? 'selected' : '' }}>{{ $state->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="invalid-feedback-compact" id="state_id_invalid">The State field is required</span>
                            </div>

                            <!-- City -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.City') }}</label>
                                @php $selectedCityId = old('city', $data->city_id ?? ($getSetting->city_id ?? '')); @endphp
                                <select class="form-control-compact cityId select2" name="city" id="city_id">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getCity))
                                        @foreach($getCity as $cities)
                                        <option value="{{ $cities->id ?? '' }}" {{ ($cities->id == $selectedCityId) ? 'selected' : '' }}>{{ $cities->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Pin Code -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Pin Code') }}</label>
                                <input type="text" class="form-control-compact" id="pincode" name="pincode" 
                                       placeholder="6-digit PIN" value="{{ old('pincode', $data->pincode ?? '') }}" 
                                       maxlength="6" onkeypress="javascript:return isNumber(event)">
                            </div>

                            <!-- Village / City Area -->
                            <div class="col-lg-4 col-md-8 col-sm-12 compact-form-group">
                                <label>{{ __('student.Village/City') }}</label>
                                <input type="text" class="form-control-compact" id="village_city" name="village_city" 
                                       placeholder="Village / Town / Locality" value="{{ old('village_city', $data->village_city ?? '') }}">
                            </div>

                            <!-- Complete Address -->
                            <div class="col-lg-12 col-md-12 col-sm-12 compact-form-group">
                                <label>{{ __('student.Students Address') }}</label>
                                <textarea class="form-control-compact" id="address" name="address" rows="1" 
                                          placeholder="Enter complete residential address...">{{ old('address', $data->address ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 4: DOCUMENT & PHOTO UPLOAD         -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-2">
                            <span class="form-section-title">
                                <i class="fa fa-file-image-o"></i> 4. Document & Photo Upload / दस्तावेज़ एवं फोटो
                            </span>
                            <span class="form-section-badge">Max 2MB (JPG / PNG)</span>
                        </div>

                        <div class="row px-1">
                            <!-- Student Photo -->
                            <div class="col-lg-4 col-md-4 col-sm-12 compact-form-group">
                                <label>{{ __('student.Student Photo') }}</label>
                                <div class="photo-upload-box">
                                    <img id="student_img_preview" 
                                         src="{{ ($isEdit && !empty($data->image)) ? env('IMAGE_SHOW_PATH').'profile/'.$data->image : env('IMAGE_SHOW_PATH').'/student_image/profile_img.png' }}" 
                                         class="photo-preview-thumb" 
                                         onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'" 
                                         alt="Student">
                                    <div class="flex-grow-1">
                                        <input type="file" class="photo-upload-input" name="student_img" id="student_img" accept="image/png, image/jpg, image/jpeg">
                                        <p class="text-danger mb-0 font-weight-bold" id="image_error" style="font-size: 11px;"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Father Photo -->
                            <div class="col-lg-4 col-md-4 col-sm-12 compact-form-group">
                                <label>{{ __('student.Father Photo') }}</label>
                                <div class="photo-upload-box">
                                    <img id="father_img_preview" 
                                         src="{{ ($isEdit && !empty($data->father_img)) ? env('IMAGE_SHOW_PATH').'father_image/'.$data->father_img : env('IMAGE_SHOW_PATH').'/student_image/profile_img.png' }}" 
                                         class="photo-preview-thumb" 
                                         onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'" 
                                         alt="Father">
                                    <div class="flex-grow-1">
                                        <input type="file" class="photo-upload-input" name="father_img" id="father_img" accept="image/png, image/jpg, image/jpeg">
                                        <p class="text-danger mb-0 font-weight-bold" id="image_errors" style="font-size: 11px;"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Mother Photo -->
                            <div class="col-lg-4 col-md-4 col-sm-12 compact-form-group">
                                <label>{{ __('student.Mother Photo') }}</label>
                                <div class="photo-upload-box">
                                    <img id="mother_img_preview" 
                                         src="{{ ($isEdit && !empty($data->mother_img)) ? env('IMAGE_SHOW_PATH').'mother_image/'.$data->mother_img : env('IMAGE_SHOW_PATH').'/student_image/profile_img.png' }}" 
                                         class="photo-preview-thumb" 
                                         onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'" 
                                         alt="Mother">
                                    <div class="flex-grow-1">
                                        <input type="file" class="photo-upload-input" name="mother_img" id="mother_img" accept="image/png, image/jpg, image/jpeg">
                                        <p class="text-danger mb-0 font-weight-bold" id="image_er" style="font-size: 11px;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- 3. Form Action Bar -->
                    <div class="dash-form-footer">
                        <div class="d-flex align-items-center">
                            <span class="small text-muted font-weight-bold" style="font-size: 11px;">
                                <span class="text-danger font-weight-bold">*</span> Fields marked in red asterisk are mandatory.
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($isEdit)
                                <a href="{{ url('admissionView') }}" class="btn-reset-clean">
                                    <i class="fa fa-arrow-left text-secondary mr-1"></i> Cancel
                                </a>
                                <button type="button" class="btn-submit-save" id="is-invalid">
                                    <i class="fa fa-save mr-1"></i> Update Admission
                                </button>
                            @else
                                <button type="reset" class="btn-reset-clean" onclick="$('.select2').val('').trigger('change');">
                                    <i class="fa fa-refresh text-secondary mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn-submit-save" id="is-invalid">
                                    <i class="fa fa-check-circle mr-1"></i> {{ __('common.submit') }} Admission
                                </button>
                            @endif
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function(){
    var baseUrl = "{{ url('/') }}";
    var maxImgSize = 2097152; // 2MB

    // 1. Force Select2 Width to 100%
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%'
        });
    }

    // 2. Batch Calculator & Dynamic Course Classes & Real-Time Admission Number
    function calculateBatch() {
        var selectedOption = $('#course').find(':selected');
        var duration = parseInt(selectedOption.data('duration')) || 0;
        if (duration > 0) {
            var currentYear = new Date().getFullYear();
            var endYear = currentYear + duration;
            var endYearShort = String(endYear).slice(-2);
            $('#batch').val(currentYear + '-' + endYearShort);
        } else {
            $('#batch').val('');
        }
    }

    function fetchNextAdmissionNo() {
        @if(!$isEdit)
        var courseSelect = $('#course');
        var courseName = courseSelect.val();
        var courseId = courseSelect.find(':selected').data('id') || '';

        if (courseName) {
            $('#admissionNo').attr('placeholder', 'Calculating...');
            $.ajax({
                url: baseUrl + "/getNextAdmissionNo",
                type: "GET",
                data: { 
                    course: courseName,
                    course_id: courseId
                },
                dataType: "json",
                success: function(res) {
                    if (res && res.status === 'success' && res.admissionNo) {
                        $('#admissionNo').val(res.admissionNo);
                        $('#section_admission_no_badge').text('Admission No: #' + res.admissionNo);
                    }
                },
                error: function(err) {
                    console.error('Error fetching next admission number:', err);
                }
            });
        } else {
            $('#admissionNo').val('');
            $('#section_admission_no_badge').text('Admission No: Auto');
        }
        @endif
    }

    $('#course').on('change', function() {
        var courseName = $(this).val();
        var classSelect = $('#class_type_id');
        
        calculateBatch();
        fetchNextAdmissionNo();

        if (courseName) {
            $.ajax({
                url: baseUrl + "/getClassesByCourse",
                type: "GET",
                data: { course: courseName },
                dataType: "json",
                success: function(data) {
                    classSelect.empty();
                    classSelect.append('<option value="">{{ __("common.Select") }} Class / Semester</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(key, val) {
                            classSelect.append('<option value="' + val.id + '" data-orderby="' + (val.orderBy || '') + '">' + val.name + '</option>');
                        });
                    }
                    if (classSelect.hasClass('select2')) {
                        classSelect.trigger('change.select2');
                    }
                }
            });
        } else {
            classSelect.empty();
            classSelect.append('<option value="">{{ __("common.Select") }} Class / Semester</option>');
            if (classSelect.hasClass('select2')) {
                classSelect.trigger('change.select2');
            }
        }
    });

    if ($('#course').val()) {
        if (!$('#batch').val()) {
            calculateBatch();
        }
        @if(!$isEdit)
        if (!$('#admissionNo').val()) {
            fetchNextAdmissionNo();
        }
        @endif
    }

    // 3. Class Stream Subject Handler
    $('#class_type_id').change(function(){
        var class_type_id = parseInt($(this).val());
        var orderBy = parseInt($(this).find('option:selected').attr('data-orderBy'));
        
        if (orderBy > 10) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                url: baseUrl + '/getStreamSubjects',
                data: {
                    class_type_id: class_type_id
                },
                success: function(data) {
                    var options = "";
                    $('#stream_subject').html("");
                    if (data && data.length > 0) {
                        for(var i = 0; i < data.length; i++){
                            options += '<option value="'+ data[i].id +'">'+ data[i].name +'</option>';
                        }
                    }
                    $('#stream_subject').html(options);
                    if ($('#stream_subject').hasClass('select2')) {
                        $('#stream_subject').trigger('change.select2');
                    }
                    $('#stream_subject_div').show();
                }
            });
        } else {
            $('#stream_subject').html("");
            if ($('#stream_subject').hasClass('select2')) {
                $('#stream_subject').trigger('change.select2');
            }
            $('#stream_subject_div').hide();
        }
    });

    // 4. Image Validation & Live Thumbnail Preview
    function bindImagePreview(inputId, errorId, previewId) {
        $(inputId).change(function(e){
            $(errorId).html("");
            var file = e.target.files[0];
            if (!file) return;

            var fileName = file.name;
            var extension = fileName.split(".").pop().toLowerCase();
            
            if (extension === "png" || extension === "jpg" || extension === "jpeg") {
                if (file.size > maxImgSize) {
                    $(errorId).html("Please select an image under 2MB");
                    $(this).val('');
                } else {
                    $(errorId).html("");
                    var reader = new FileReader();
                    reader.onload = function(evt) {
                        $(previewId).attr('src', evt.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                $(errorId).html("Only JPG, JPEG, or PNG files are allowed");
                $(this).val('');
            }
        });
    }

    bindImagePreview('#student_img', '#image_error', '#student_img_preview');
    bindImagePreview('#father_img', '#image_errors', '#father_img_preview');
    bindImagePreview('#mother_img', '#image_er', '#mother_img_preview');

    // 5. Submit Validation & Double Submission Protection
    $('#is-invalid').click(function(e){
        e.preventDefault();
        $('#update_action_input').val('');
        var isValid = true;
        var firstErrorElement = null;

        $('.invalid').each(function(){
            var this_value = $(this).val();
            var this_id = $(this).attr('id'); 
            if(!this_value || this_value === ''){
                $('#' + this_id + '_invalid').show();
                if (!firstErrorElement) firstErrorElement = $(this);
                isValid = false;
            } else {
                $('#' + this_id + '_invalid').hide();
            }
        });

        var mobileValue = $('#mobile').val() || '';
        if (mobileValue.length < 10) {
            $('#mobileValidationMessage').show();
            if (!firstErrorElement) firstErrorElement = $('#mobile');
            isValid = false;
        } else {
            $('#mobileValidationMessage').hide();
        }

        var fatherMobileValue = $('#father_mobile').val() || '';
        if (fatherMobileValue.length < 10) {
            $('#fathermobileValidationMessage').show();
            if (!firstErrorElement) firstErrorElement = $('#father_mobile');
            isValid = false;
        } else {
            $('#fathermobileValidationMessage').hide();
        }

        if(isValid){
            var submitBtn = $('#is-invalid');
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');
            $('#quickForm_addmission').trigger('submit');
        } else {
            if (firstErrorElement) {
                $('html, body').animate({
                    scrollTop: firstErrorElement.offset().top - 100
                }, 300);
            }
        }
    });
});
</script>
@include('agents.quick_add_modal')
@endsection