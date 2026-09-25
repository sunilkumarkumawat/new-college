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
    .filter-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 12px;
    }
    .form-label-compact {
        font-size: 11.5px;
        font-weight: 700;
        margin-bottom: 2px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }
    .form-control-compact {
        height: 34px !important;
        font-size: 13px !important;
        padding: 4px 8px !important;
        border-radius: 5px !important;
    }
    .select2-container--default .select2-selection--single {
        height: 34px !important;
        padding: 3px 6px !important;
        font-size: 13px !important;
        border: 1px solid #ced4da !important;
        border-radius: 5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        padding-left: 2px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
    .padding_table thead tr{
        background: #002c54;
        position: sticky;
        top: 0;
        color: white;
        z-index: 10;
    }
    .padding_table thead tr th{
        padding: 6px 8px !important;
        font-size: 12.5px;
        font-weight: 600;
    }
    .padding_table tr th, .padding_table tr td{
        font-size: 13px;
        padding: 6px 8px !important;
        vertical-align: middle;
    }
    .padding_table tbody tr:hover {
        background-color: #f1f5f9;
    }
    .padding_table tbody tr.active-student {
        background-color: #002c54 !important;
        color: #fff !important;
    }
    .padding_table tbody tr.active-student td a {
        color: #fff !important;
    }
    .student-preview-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        padding: 8px;
    }
    .student-preview-card table {
        margin-bottom: 0;
    }
    .student-preview-card th, .student-preview-card td {
        padding: 4px 8px !important;
        font-size: 12.5px;
    }
    .student-avatar {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }
</style>

<div class="content-wrapper">
    <section class="content pt-2">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card card-outline card-orange mb-2 shadow-sm">
                        <div class="card-header bg-primary py-2">
                            <h3 class="card-title font-weight-bold" style="font-size: 16px;">
                                <i class="fa fa-money mr-1"></i> {{ __('fees.Collect Student Fees') }}
                            </h3>
                            <div class="card-tools">
                                <a href="{{url('fees/index')}}" class="btn btn-primary btn-xs mr-1" title="View Fees"><i class="fa fa-eye mr-1"></i>{{ __('common.View') }}</a>
                                <a href="{{url('fee_dashboard')}}" class="btn btn-primary btn-xs" title="Back"><i class="fa fa-arrow-left mr-1"></i>{{ __('common.Back') }}</a>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <!-- Compact Filter Form -->
                            <form id="quickForm" method="get" action="{{ url('Fees/add') }}">
                                <div class="filter-card">
                                    <div class="row align-items-end">
                                        <!-- Session -->
                                        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 mb-1">
                                            <label class="form-label-compact"><i class="fa fa-calendar text-primary mr-1"></i>{{ __('Session') }}</label>
                                            <select class="form-control select2 filter-field" id="session_id" name="session_id">
                                                <option value="">{{ __('common.All') }}</option>
                                                @if(!empty($getSession))
                                                    @foreach($getSession as $session)
                                                        <option value="{{ $session->id ?? '' }}" {{ ($session->id == ($serach['session_id'] ?? '')) ? 'selected' : '' }}>{{ $session->from_year ?? '' }} - {{ $session->to_year ?? '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <!-- Course -->
                                        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 mb-1">
                                            <label class="form-label-compact"><i class="fa fa-graduation-cap text-primary mr-1"></i>{{ __('Course') }}</label>
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
                                        <div class="col-xl-2 col-lg-2 col-md-3 col-sm-6 col-12 mb-1">
                                            <label class="form-label-compact"><i class="fa fa-book text-primary mr-1"></i>{{ __('Class/Semester') }}</label>
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
                                        <div class="col-xl-1 col-lg-1 col-md-3 col-sm-6 col-12 mb-1">
                                            <label class="form-label-compact"><i class="fa fa-users text-primary mr-1"></i>{{ __('Batch') }}</label>
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
                                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                                            <label class="form-label-compact"><i class="fa fa-id-card text-primary mr-1"></i>{{ __('student.Admission No.') }}</label>
                                            <input type="text" class="form-control form-control-compact filter-field" id="admission_no" name="admission_no" placeholder="Adm No." value="{{ $serach['admission_no'] ?? '' }}" autocomplete="off">
                                        </div>

                                        <!-- Search By Keywords -->
                                        <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                                            <label class="form-label-compact"><i class="fa fa-search text-primary mr-1"></i>{{ __('common.Search By Keywords') }}</label>
                                            <input type="text" class="form-control form-control-compact filter-field" value="{{ $serach['name'] ?? '' }}" id="name" name="name" placeholder="Name/Mobile/Father" autocomplete="off">
                                        </div>

                                        <!-- Actions -->
                                        <div class="col-xl-1 col-lg-1 col-md-4 col-sm-6 col-12 mb-1 d-flex">
                                            <button type="submit" class="btn btn-primary btn-sm flex-fill mr-1 shadow-sm font-weight-bold" title="{{ __('common.Search') }}">
                                                <i class="fa fa-search"></i>
                                            </button>
                                            <a href="{{ url('Fees/add') }}" class="btn btn-outline-secondary btn-sm flex-fill shadow-sm" title="Reset Filters">
                                                <i class="fa fa-refresh"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if(!empty($data))
                            <div class="row mt-1">
                                <div class="col-12 col-md-7 mb-2">
                                    <div class="border rounded shadow-sm bg-white" style="max-height: 260px; overflow-y: auto;">
                                        <table class="table table-bordered table-hover small_td padding_table mb-0" id="trColor">
                                            <thead>
                                                <tr>
                                                    <th style="width: 100px;">Ledger No.</th>
                                                    <th class="text-center" style="width: 120px;">{{ __('student.Admission No.') }}</th>
                                                    <th>{{ __('common.Name') }}</th>
                                                    <th>{{ __('common.Class') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data as $item)
                                                    @php
                                                        $array[$item->id] = $item;
                                                    @endphp
                                                    <tr class="quickCollect" data-id="{{ $item->id ?? '' }}" data-system-id="{{ $item['unique_system_id'] ?? '' }}" style="cursor: pointer;" onclick="showData('{{ $item['unique_system_id'] }}','{{ $serach['session_id'] ?? Session::get('session_id') }}')">
                                                        <td class="font-weight-bold text-muted">{{ $item->ledger_no ?? 'NA' }}</td>
                                                        <td class="text-center font-weight-bold text-primary">{{ $item['admissionNo'] ?? '' }}</td>
                                                        <td class="font-weight-bold">{{ $item['first_name'] ?? '' }} {{ $item['last_name'] ?? '' }}</td>
                                                        <td><span class="badge badge-info py-1 px-2">{{ $item['ClassTypes']['name'] ?? 'N/A' }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-muted small mt-1 font-italic">
                                        Showing {{ count($data) }} student(s). Click on any student to view fee details.
                                    </div>
                                </div>

                                <div class="col-12 col-md-5 mb-2" id="show_details" style="display:none;">
                                    <div class="student-preview-card">
                                        <table class="table table-bordered table-sm">
                                            <tr>
                                                <th rowspan="6" class="text-center align-middle p-1" style="width: 120px;">
                                                    <img src="" class="student-avatar" id="student-image" />
                                                </th>
                                                <th style="width: 90px;" class="text-muted">Name</th>
                                                <td id="student-name" class="font-weight-bold text-primary"></td>
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Fees Dynamic Detail Panel -->
            <div id="student_fees_detail" class="mt-2"></div>
        </div>
    </section>
</div>

<script>
var selectedSystemId = '{{ $serach["unique_system_id"] ?? "" }}';

$(document).ready(function() {
    // Function to sync filter inputs & selected student into the URL parameters
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

    // Keep URL parameters updated as user changes filters
    $(document).on('change', '.filter-field', function() {
        syncUrlParams();
    });

    $(document).on('input', '#admission_no, #name', function() {
        syncUrlParams();
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
                    syncUrlParams();
                },
                error: function() {
                    updateClassDropdown(classSelect, []);
                    syncUrlParams();
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
            syncUrlParams();
        }
    });

    // Handle student row selection
    $(document).on("click", ".quickCollect", function(){
        var sysId = $(this).data('system-id');
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
    });

    // Auto-open student if unique_system_id, admission_id, or admission_no is present in URL or $serach
    var urlParams = new URLSearchParams(window.location.search);
    var targetSysId = urlParams.get('unique_system_id') || '{{ $serach["unique_system_id"] ?? "" }}';
    var targetAdmId = urlParams.get('admission_id') || '{{ $serach["admission_id"] ?? "" }}';
    var targetAdmNo = urlParams.get('admission_no') || '{{ $serach["admission_no"] ?? "" }}';

    if (targetSysId || targetAdmId || targetAdmNo) {
        var targetRow = null;
        if (targetSysId) {
            targetRow = $('.quickCollect[data-system-id="' + targetSysId + '"]');
        }
        if ((!targetRow || targetRow.length === 0) && targetAdmId) {
            targetRow = $('.quickCollect[data-id="' + targetAdmId + '"]');
        }
        if ((!targetRow || targetRow.length === 0) && targetAdmNo) {
            targetRow = $('.quickCollect').filter(function() {
                return $(this).find('td:eq(1)').text().trim() === targetAdmNo.trim();
            });
        }

        if (targetRow && targetRow.length > 0) {
            targetRow.first().trigger('click');
        } else if (targetSysId) {
            showData(targetSysId, $('#session_id').val() || '{{ Session::get("session_id") }}');
        }
    } else {
        // Auto-click first student if exactly 1 result returned
        @if(!empty($data) && count($data) === 1)
            $(".quickCollect").first().trigger('click');
        @endif
    }
});

function showData(unique_system_id, session_id) {
    var basurl = "{{ url('/') }}";
    $('#student_fees_detail').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><div class="mt-2 text-muted">Loading fee collection details...</div></div>');
    
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }, 
        type: 'post',
        url: basurl + '/student_fees_onclick',
        data: {
            unique_system_id: unique_system_id,
            session_id: session_id,
        },
        success: function(data) {
            if (data == 0) {
                alert('Please Assign the Fees for this Student !');
                $('#student_fees_detail').html('');
            } else {
                $('#student_fees_detail').html(data);
            }
        },
        error: function() {
            $('#student_fees_detail').html('<div class="alert alert-danger">Error loading fees details. Please try again.</div>');
        }
    });
}
</script>

@endsection