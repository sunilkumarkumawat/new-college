@php
    $getCountry = Helper::getCountry();
    $getState = Helper::getState();
    $getCity = Helper::getCity();
    $getaccounts = Helper::getaccount();
    $getSession = Helper::getSession();
@endphp

@extends('layout.app')
@section('content')

<style>
/* ========================================================
   EXECUTIVE SETTINGS EDIT FORM THEME (Excel Green System)
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

.setting-compact-page {
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
    margin-bottom: 16px;
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
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 3px 10px !important;
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

/* 3. Form Section Strips */
.form-section-strip {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-left: 3.5px solid var(--green-excel);
    padding: 6px 12px;
    margin-top: 10px;
    margin-bottom: 12px;
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
    padding: 1px 8px;
    font-size: 10px;
    font-weight: 700;
}

/* 4. Compact Form Control Overrides */
.compact-form-group {
    margin-bottom: 12px;
    width: 100%;
}

.compact-form-group label {
    font-size: 11px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 3px;
    display: block;
    line-height: 1.25;
}

.compact-form-group label .req-star {
    color: #dc2626;
    margin-left: 2px;
    font-weight: 700;
}

.form-control-compact {
    height: 30px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    padding: 2px 8px !important;
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

.form-control-compact[readonly], .form-control-compact[disabled] {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #475569 !important;
    font-weight: 600;
    cursor: not-allowed;
}

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
    padding: 2px 8px;
    color: #64748b;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
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

/* Select2 Custom Styling to Match Compact Input Height */
.select2-container--default .select2-selection--single {
    height: 30px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    padding: 1px 6px !important;
    font-size: 11.5px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
    color: #1e293b !important;
    padding-left: 2px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px !important;
}

/* Image Upload & Preview Cards */
.branding-card {
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 10px;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.preview-thumb-box {
    width: 100%;
    height: 64px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    padding: 4px;
    overflow: hidden;
}

.preview-thumb-box img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

/* Toggle Switch Styling */
.switch-container {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-top: 4px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 22px;
    margin-bottom: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .3s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--green-excel);
}

input:checked + .slider:before {
    transform: translateX(22px);
}

.switch-label-text {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
}

/* Action Buttons */
.btn-submit-executive {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding: 6px 22px !important;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-submit-executive:hover {
    background: var(--green-excel-dark) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.18);
    transform: translateY(-1px);
}

.btn-reset-clean {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 6px 16px !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease-in-out;
}

.btn-reset-clean:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
}

.field-error {
    font-size: 10.5px;
    color: #dc2626;
    margin-top: 2px;
    font-weight: 600;
    display: block;
}
</style>

<div class="content-wrapper setting-compact-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            
            <div class="card dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-cogs"></i>
                        <span>{{ __('setting.Edit Setting') }}</span>
                        <small>| System Configuration & Institution Profile</small>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('viewSetting') }}" class="btn-header-action" title="Back to Settings List">
                            <i class="fa fa-arrow-left mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Settings Form Body -->
                <form id="quickForm" action="{{ url('editSetting') }}/{{ $data->id }}" method="post" enctype="multipart/form-data" class="mb-0">
                    @csrf
                    
                    <div class="p-3">
                        
                        <!-- ========================================== -->
                        <!-- SECTION 1: INSTITUTION & SYSTEM CONFIG    -->
                        <!-- ========================================== -->
                        <div class="form-section-strip mt-0">
                            <span class="form-section-title">
                                <i class="fa fa-university"></i> 1. Institution Profile & Academic Session Configuration
                            </span>
                            <span class="form-section-badge">General Settings</span>
                        </div>

                        <div class="row">
                            @if(Session::get('role_id') == 1)
                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="branch_id">{{ __('setting.Branch') }}<span class="req-star">*</span></label>
                                    <select class="form-control-compact select2 @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" {{ $data->branch_id == null ? '' : 'disabled' }}>
                                        <option value="">{{ __('common.Select') }}</option>
                                        @if(!empty($branch))
                                            @foreach($branch as $Branch)
                                                <option value="{{ $Branch->id ?? '' }}" {{ ($Branch['id'] == $data['branch_id']) ? 'selected' : '' }}>
                                                    {{ $Branch->branch_name ?? '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('branch_id')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            @endif

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="name">{{ __('common.Name') }} / Institution Name<span class="req-star">*</span></label>
                                    <div class="compact-input-group">
                                        <span class="compact-addon-icon"><i class="fa fa-institution"></i></span>
                                        <input type="text" class="form-control-compact @error('name') is-invalid @enderror" onkeydown="return /[a-zA-Z ]/i.test(event.key)" id="name" name="name" placeholder="{{ __('common.Name') }}" value="{{ old('name', $data->name) }}">
                                    </div>
                                    @error('name')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    @php
                                        $resolvedActiveSession = \App\Helpers\helper::resolveCurrentFinancialSession($data['branch_id'] ?? 1);
                                    @endphp
                                    <label for="current_active_session_id">
                                        {{ __('Current Active Session') }}
                                        <span class="badge badge-success ml-1" style="font-size: 9.5px; background: #16a34a; color: #fff; padding: 2px 6px; border-radius: 3px; font-weight: 600;">Dynamic FY</span>
                                    </label>
                                    <div class="compact-input-group">
                                        <span class="compact-addon-icon" style="color: #16a34a;"><i class="fa fa-calendar-check-o"></i></span>
                                        <input type="text" class="form-control-compact font-weight-bold" style="background-color: #f8fafc; color: #0f172a;" value="{{ $resolvedActiveSession->from_year ?? '' }} - {{ $resolvedActiveSession->to_year ?? '' }} (Auto-managed)" readonly>
                                    </div>
                                    <input type="hidden" id="current_active_session_id" name="current_active_session_id" value="{{ $resolvedActiveSession->id }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="firebaseNotification">Push Notifications</label>
                                    <div class="switch-container">
                                        <label class="switch">
                                            <input type="checkbox" name="firebase_notification" id="firebaseNotification" value="1" {{ $data->firebase_notification ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <span class="switch-label-text" id="firebaseStatusText">
                                            {{ $data->firebase_notification ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 2: CONTACT & LOCATION DETAILS      -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-map-marker"></i> 2. Contact Information & Location Details
                            </span>
                            <span class="form-section-badge">Contact & Address</span>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="mobile">{{ __('common.Mobile No.') }}<span class="req-star">*</span></label>
                                    <div class="compact-input-group">
                                        <span class="compact-addon-icon"><i class="fa fa-phone"></i></span>
                                        <input type="text" class="form-control-compact @error('mobile') is-invalid @enderror" id="mobile" name="mobile" placeholder="{{ __('common.Mobile No.') }}" value="{{ old('mobile', $data->mobile) }}" maxlength="10" onkeypress="javascript:return isNumber(event)">
                                    </div>
                                    @error('mobile')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="gmail">{{ __('common.E-Mail') }}<span class="req-star">*</span></label>
                                    <div class="compact-input-group">
                                        <span class="compact-addon-icon"><i class="fa fa-envelope"></i></span>
                                        <input type="text" class="form-control-compact @error('gmail') is-invalid @enderror" id="gmail" name="gmail" placeholder="{{ __('common.E-Mail') }}" value="{{ old('gmail', $data->gmail) }}">
                                    </div>
                                    @error('gmail')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="country_id">{{ __('common.Country') }}</label>
                                    <select class="form-control-compact select2" name="country_id" id="country_id">
                                        <option value="">{{ __('common.Select') }}</option>
                                        @if(!empty($getCountry)) 
                                            @foreach($getCountry as $country)
                                                <option value="{{ $country->id ?? '' }}" {{ ($country['id'] == $data['country_id']) ? 'selected' : '' }}>
                                                    {{ $country->name ?? '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('country_id')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="state_id">{{ __('common.State') }}</label>
                                    <select class="form-control-compact select2" id="state_id" name="state_id">
                                        <option value="">{{ __('common.Select') }}</option>
                                        @if(!empty($getState)) 
                                            @foreach($getState as $state)
                                                <option value="{{ $state->id ?? '' }}" {{ ($state['id'] == $data['state_id']) ? 'selected' : '' }}>
                                                    {{ $state->name ?? '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('state_id')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="city_id">{{ __('common.City') }}</label>
                                    <select class="form-control-compact select2" name="city_id" id="city_id">
                                        <option value="">{{ __('common.Select') }}</option>
                                        @if(!empty($getcitys)) 
                                            @foreach($getcitys as $cities)
                                                <option value="{{ $cities->id ?? '' }}" {{ ($cities['id'] == $data['city_id']) ? 'selected' : '' }}>
                                                    {{ $cities->name ?? '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('city_id')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="compact-form-group">
                                    <label for="pincode">{{ __('common.Pin Code') }}<span class="req-star">*</span></label>
                                    <div class="compact-input-group">
                                        <span class="compact-addon-icon"><i class="fa fa-map-pin"></i></span>
                                        <input type="text" class="form-control-compact @error('pincode') is-invalid @enderror" onkeypress="javascript:return isNumber(event)" maxlength="6" id="pincode" name="pincode" placeholder="{{ __('common.Pin Code') }}" value="{{ old('pincode', $data->pincode) }}">
                                    </div>
                                    @error('pincode')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="compact-form-group">
                                    <label for="address">{{ __('common.Address') }}<span class="req-star">*</span></label>
                                    <div class="compact-input-group">
                                        <span class="compact-addon-icon"><i class="fa fa-home"></i></span>
                                        <input type="text" class="form-control-compact @error('address') is-invalid @enderror" id="address" name="address" placeholder="{{ __('common.Address') }}" value="{{ old('address', $data->address) }}">
                                    </div>
                                    @error('address')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- SECTION 3: BRANDING, WATERMARK & STAMPS   -->
                        <!-- ========================================== -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-picture-o"></i> 3. Institutional Branding, Watermark & Signatures
                            </span>
                            <span class="form-section-badge">Branding & Assets</span>
                        </div>

                        <div class="row">
                            <!-- Left Logo -->
                            <div class="col-md-4 mb-3">
                                <div class="branding-card">
                                    <label for="left_logo" class="font-weight-bold mb-2">
                                        <i class="fa fa-image text-success mr-1"></i> {{ __('setting.Left Logo') }}
                                    </label>
                                    <div class="preview-thumb-box">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'/setting/left_logo/'.$data['left_logo'] }}" id="left_logo_preview" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/no_image.png' }}'" alt="Left Logo">
                                    </div>
                                    <input type="file" class="form-control-compact @error('left_logo') is-invalid @enderror" id="left_logo" name="left_logo" accept="image/png, image/jpg, image/jpeg">
                                    <span class="field-error" id="image_errors"></span>
                                    @error('left_logo')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Watermark Image -->
                            <div class="col-md-4 mb-3">
                                <div class="branding-card">
                                    <label for="watermark_image" class="font-weight-bold mb-2">
                                        <i class="fa fa-certificate text-primary mr-1"></i> {{ __('Water Mark Image') }}
                                    </label>
                                    <div class="preview-thumb-box">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'/setting/watermark_image/'.$data['watermark_image'] }}" id="watermark_image_preview" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/rukmani_logo.png' }}'" alt="Watermark Image">
                                    </div>
                                    <input type="file" class="form-control-compact @error('watermark_image') is-invalid @enderror" id="watermark_image" name="watermark_image" accept="image/png, image/jpg, image/jpeg">
                                    <span class="field-error" id="image_erro"></span>
                                    @error('watermark_image')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Seal & Sign -->
                            <div class="col-md-4 mb-3">
                                <div class="branding-card">
                                    <label for="seal_sign" class="font-weight-bold mb-2">
                                        <i class="fa fa-pencil-square-o text-warning mr-1"></i> {{ __('setting.Seal & Sign.') }}
                                    </label>
                                    <div class="preview-thumb-box">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'/setting/seal_sign/'.$data['seal_sign'] }}" id="seal_sign_preview" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/no_image.png' }}'" alt="Seal & Sign">
                                    </div>
                                    <input type="file" class="form-control-compact @error('seal_sign') is-invalid @enderror" id="seal_sign" name="seal_sign" accept="image/png, image/jpg, image/jpeg">
                                    <span class="field-error" id="image_er"></span>
                                    @error('seal_sign')
                                        <span class="field-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Bar -->
                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn-submit-executive">
                                    <i class="fa fa-save"></i> {{ __('common.Update') }} {{ __('setting.Edit Setting') }}
                                </button>
                                <a href="{{ url('viewSetting') }}" class="btn-reset-clean ml-2">
                                    <i class="fa fa-times"></i> {{ __('common.Cancel') }}
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </section>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        const Img_Size = 2097152; // 2MB limit

        // Image validation & preview function
        function handleImageUpload(inputEl, errorElId, previewImgId) {
            $(inputEl).change(function(e){
                $('#' + errorElId).html("");
                var file = e.target.files[0];
                if (!file) return;

                var fileName = file.name;
                var extension = fileName.split(".").pop().toLowerCase();
                
                if (["png", "jpg", "jpeg"].includes(extension)) {
                    if (file.size > Img_Size) {
                        $('#' + errorElId).html("Please select image under 2MB.");
                        $(this).val('');
                    } else {
                        $('#' + errorElId).html("");
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $('#' + previewImgId).attr('src', e.target.result);
                        }
                        reader.readAsDataURL(file);
                    }
                } else {
                    $('#' + errorElId).html("Invalid image format (PNG, JPG, JPEG required).");
                    $(this).val('');
                }
            });
        }

        handleImageUpload('#left_logo', 'image_errors', 'left_logo_preview');
        handleImageUpload('#watermark_image', 'image_erro', 'watermark_image_preview');
        handleImageUpload('#seal_sign', 'image_er', 'seal_sign_preview');

        // Toggle text update
        $('#firebaseNotification').on('change', function(){
            const isChecked = $(this).is(':checked');
            $('#firebaseStatusText').text(isChecked ? 'Enabled' : 'Disabled');
        });
    });
</script>

@endsection