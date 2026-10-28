@php
$getCourses = Helper::getCourses();
$getTypeclass = Helper::classType();
$getCountry = Helper::getCountry();
$getState = Helper::getState();
$getCity = Helper::getCity();
$getgenders = Helper::getgender();
$regNo = ($BillCounter->counter + 1) ?? '';
@endphp
@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   COMPACT EXECUTIVE ENQUIRY FORM THEME
   (Matches studentsDashboard, admissionView & promote layout)
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

.enquiry-compact-page {
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
    min-height: 48px !important;
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
    display: block;
    font-size: 10.5px;
    color: #dc2626;
    margin-top: 1px;
    font-weight: 600;
}
</style>

<div class="content-wrapper enquiry-compact-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            
            <div class="card dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-address-book-o"></i>
                        <span>{{ __('student.Students Enquiry') }}</span>
                        <small>| New Registration Entry</small>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('enquiryView') }}" class="btn-header-action" title="View Leads & Enquiries">
                            <i class="fa fa-list-ul text-primary mr-1"></i> {{ __('common.View') }} Enquiries
                        </a>
                        <a href="{{ url('studentsDashboard') }}" class="btn-header-action" title="Back to Students Dashboard">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Main Form Body -->
                <form id="quickForm" action="{{ url('enquiryAdd') }}" method="post" enctype="multipart/form-data" class="mb-0">
                    @csrf

                    <div class="p-2">
                        
                        <!-- ========================================== -->
                        <!-- SECTION 1: APPLICANT STUDENT DETAILS       -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-user"></i> 1. Applicant Student Details
                            </span>
                            <span class="form-section-badge">Primary Info</span>
                        </div>

                        <div class="row px-1">
                            <!-- Registration No -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('student.Registration No') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-hashtag text-success"></i></span>
                                    <input type="text" class="form-control-compact font-weight-bold text-success @error('registration_no') is-invalid @enderror" 
                                           id="registration_no" name="registration_no" 
                                           placeholder="Reg. No" readonly value="{{ $regNo }}">
                                </div>
                                @error('registration_no')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- First Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.First Name') }} <span class="req-star">*</span></label>
                                <input type="text" class="form-control-compact @error('first_name') is-invalid @enderror" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="first_name" name="first_name" 
                                       placeholder="First name" value="{{ old('first_name') }}" required autofocus>
                                @error('first_name')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Last Name') }}</label>
                                <input type="text" class="form-control-compact @error('last_name') is-invalid @enderror" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="last_name" name="last_name" 
                                       placeholder="Last name" value="{{ old('last_name') }}">
                                @error('last_name')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Gender') }} <span class="req-star">*</span></label>
                                <select class="form-control-compact select2 @error('gender_id') is-invalid @enderror" id="gender_id" name="gender_id" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($getgenders)) 
                                        @foreach($getgenders as $value)
                                            <option value="{{ $value->id }}" {{ ($value->id == old('gender_id')) ? 'selected' : '' }}>{{ $value->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('gender_id')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-lg-2 col-md-4 col-sm-6 compact-form-group">
                                <label>Date of Birth (DOB)</label>
                                <input type="date" class="form-control-compact" id="dob" name="dob" value="{{ old('dob') }}">
                            </div>

                            <!-- Student Mobile No -->
                            <div class="col-lg-3 col-md-6 col-sm-6 compact-form-group">
                                <label>{{ __('student.Student Mobile No.') }} <span class="req-star">*</span></label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-phone text-success"></i></span>
                                    <input type="tel" class="form-control-compact @error('mobile') is-invalid @enderror" 
                                           id="mobile" name="mobile" 
                                           placeholder="10-digit mobile" value="{{ old('mobile') }}" 
                                           maxlength="10" minlength="10" onkeypress="javascript:return isNumber(event)" required>
                                </div>
                                @error('mobile')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-lg-3 col-md-6 col-sm-6 compact-form-group">
                                <label>{{ __('common.E-Mail') }}</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-envelope-o text-muted"></i></span>
                                    <input type="email" class="form-control-compact" id="email" name="email" 
                                           placeholder="student@example.com" value="{{ old('email') }}">
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 2: PARENTS & GUARDIAN DETAILS      -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-2">
                            <span class="form-section-title">
                                <i class="fa fa-users"></i> 2. Parent / Guardian Details
                            </span>
                            <span class="form-section-badge">Family Info</span>
                        </div>

                        <div class="row px-1">
                            <!-- Father's Name -->
                            <div class="col-lg-4 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Fathers Name') }} <span class="req-star">*</span></label>
                                <input type="text" class="form-control-compact @error('father_name') is-invalid @enderror" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="father_name" name="father_name" 
                                       placeholder="Father's full name" value="{{ old('father_name') }}" required>
                                @error('father_name')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Mother's Name -->
                            <div class="col-lg-4 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Mothers Name') }} <span class="req-star">*</span></label>
                                <input type="text" class="form-control-compact @error('mother_name') is-invalid @enderror" 
                                       onkeydown="return /[a-zA-Z ]/i.test(event.key)" 
                                       id="mother_name" name="mother_name" 
                                       placeholder="Mother's full name" value="{{ old('mother_name') }}" required>
                                @error('mother_name')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Father's Contact -->
                            <div class="col-lg-4 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('common.Fathers Contact No') }}</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-phone text-muted"></i></span>
                                    <input type="tel" class="form-control-compact" id="father_mobile" name="father_mobile" 
                                           placeholder="10-digit contact number" value="{{ old('father_mobile') }}" 
                                           maxlength="10" minlength="10" onkeypress="javascript:return isNumber(event)">
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 3: ACADEMIC CHOICE & IDENTITY      -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-2">
                            <span class="form-section-title">
                                <i class="fa fa-graduation-cap"></i> 3. Academic Preference & Document Identity
                            </span>
                            <span class="form-section-badge">Admission & ID</span>
                        </div>

                        <div class="row px-1">
                            <!-- Course -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Course') }}</label>
                                <select class="form-control-compact select2" id="course_id" name="course_id">
                                    <option value="">{{ __('common.Select') }} Course</option>
                                    @if(!empty($getCourses)) 
                                        @foreach($getCourses as $course)
                                            <option value="{{ $course->id }}" {{ ($course->id == old('course_id')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Class / Semester -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>{{ __('Class / Semester') }}</label>
                                <select class="form-control-compact select2" id="class_type_id" name="class_type_id">
                                    <option value="">{{ __('common.Select') }} Class / Semester</option>
                                    @if(!empty($getTypeclass)) 
                                        @foreach($getTypeclass as $type)
                                            <option value="{{ $type->id ?? '' }}" {{ ($type->id == old('class_type_id')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Enrollment No -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>Enrollment No</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-id-badge text-muted"></i></span>
                                    <input type="text" class="form-control-compact" name="enrollment_no" id="enrollment_no" 
                                           placeholder="Enter Enrollment No" value="{{ old('enrollment_no') }}">
                                </div>
                            </div>

                            <!-- Web Registration No -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>Web Registration No</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-globe text-muted"></i></span>
                                    <input type="text" class="form-control-compact" name="web_registration_no" id="web_registration_no" 
                                           placeholder="Enter Web Reg. No" value="{{ old('web_registration_no') }}">
                                </div>
                            </div>

                            <!-- Agent / Referrer with Quick Add -->
                            <div class="col-lg-6 col-md-8 col-sm-12 compact-form-group">
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
                                            <option value="{{ $agent->id }}" {{ (old('agent_id') == $agent->id) ? 'selected' : '' }}>
                                                {{ $agent->name }} @if(!empty($agent->mobile))({{ $agent->mobile }})@endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- ID Proof Type -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>ID Proof Document</label>
                                <select class="form-control-compact select2" id="id_proof" name="id_proof">
                                    <option value="">Select ID Type</option>
                                    <option value="Aadhar Card" {{ old('id_proof') == 'Aadhar Card' ? 'selected' : '' }}>Aadhar Card</option>
                                    <option value="Voter ID Card" {{ old('id_proof') == 'Voter ID Card' ? 'selected' : '' }}>Voter ID Card</option>
                                    <option value="Driving License" {{ old('id_proof') == 'Driving License' ? 'selected' : '' }}>Driving License</option>
                                    <option value="PAN Card" {{ old('id_proof') == 'PAN Card' ? 'selected' : '' }}>PAN Card</option>
                                </select>
                            </div>

                            <!-- ID Proof Number -->
                            <div class="col-lg-3 col-md-4 col-sm-6 compact-form-group">
                                <label>ID Proof Number</label>
                                <div class="compact-input-group">
                                    <span class="compact-addon-icon"><i class="fa fa-id-card-o text-muted"></i></span>
                                    <input type="text" class="form-control-compact" name="id_number" id="id_number" 
                                           placeholder="Enter ID number" value="{{ old('id_number') }}">
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 4: ADDRESS & REMARKS               -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-2">
                            <span class="form-section-title">
                                <i class="fa fa-map-marker"></i> 4. Residential Address & Counsellor Remarks
                            </span>
                            <span class="form-section-badge">Location & Remarks</span>
                        </div>

                        <div class="row px-1">
                            <!-- Address -->
                            <div class="col-lg-8 col-md-7 col-sm-12 compact-form-group">
                                <label>{{ __('student.Students Address') }}</label>
                                <textarea class="form-control-compact @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="2"
                                          placeholder="Enter complete residential address...">{{ old('address') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback-compact">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Remarks -->
                            <div class="col-lg-4 col-md-5 col-sm-12 compact-form-group">
                                <label>{{ __('student.Remark') }} / Notes</label>
                                <textarea class="form-control-compact" id="remark_1" name="remark_1" rows="2"
                                          placeholder="Counsellor notes or referral source...">{{ old('remark_1') }}</textarea>
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
                            <button type="reset" class="btn-reset-clean" onclick="$('.select2').val('').trigger('change');">
                                <i class="fa fa-refresh text-secondary mr-1"></i> Reset
                            </button>
                            <button type="submit" class="btn-submit-save" id="submitButton">
                                <i class="fa fa-check-circle mr-1"></i> Save & Register Enquiry
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(function () {
    // 1. Initialize Compact Select2 with width 100%
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%'
        });
    }

    // 2. Dynamic Course-dependent Classes / Semesters loader
    $('#course_id').on('change', function() {
        var courseId = $(this).val();
        var classSelect = $('#class_type_id');
        
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course_id: courseId, ignore_session: 1 },
                dataType: "json",
                beforeSend: function() {
                    classSelect.prop('disabled', true);
                },
                success: function(data) {
                    classSelect.empty();
                    classSelect.append('<option value="">{{ __("common.Select") }} Class / Semester</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(key, val) {
                            classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                        });
                    }
                    classSelect.prop('disabled', false);
                    if (classSelect.hasClass('select2')) {
                        classSelect.trigger('change.select2');
                    }
                },
                error: function() {
                    classSelect.prop('disabled', false);
                }
            });
        }
    });

    // 3. Prevent Double Submission on submit
    $('#quickForm').on('submit', function() {
        $('#submitButton').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Submitting...');
    });
});
</script>
@include('agents.quick_add_modal')
@endsection