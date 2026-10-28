@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $getPermission = Helper::getPermission();
    $isEdit = isset($editData) && !empty($editData->id);
    $courseNameValue = old('name', $editData->name ?? '');
    $courseTypeValue = old('course_type', $editData->course_type ?? 'Semester');
    $durationValue = old('duration', $editData->duration ?? 3);
    $totalSemesterValue = old('total_semester', $editData->total_semester ?? ($courseTypeValue == 'Yearly' ? 0 : 6));

    $totalCourses = count($data ?? []);
    $semesterTypeCount = (!empty($data) && count($data) > 0) ? $data->where('course_type', 'Semester')->count() : 0;
    $yearlyTypeCount = (!empty($data) && count($data) > 0) ? $data->where('course_type', 'Yearly')->count() : 0;
    $totalConfiguredClasses = (!empty($data) && count($data) > 0) ? $data->sum('classes_count') : 0;
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   COURSE STREAM MANAGEMENT - EXECUTIVE THEME SYSTEM
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

.course-manage-page {
    font-size: 11.5px;
    color: var(--text-main);
    background-color: #f4f6f9;
}

/* Main Dashboard Card Wrapper */
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

/* Fast Search input */
.dash-quick-filter {
    position: relative;
    display: flex;
    align-items: center;
}
.dash-quick-filter i {
    position: absolute;
    left: 8px;
    color: #e2f0d9;
    font-size: 11px;
    pointer-events: none;
}
.dash-quick-filter input {
    height: 27px;
    width: 170px;
    padding: 2px 8px 2px 24px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 3px;
    font-size: 11.5px;
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    transition: all 0.2s ease;
}
.dash-quick-filter input::placeholder {
    color: #e2f0d9;
    opacity: 0.85;
}
.dash-quick-filter input:focus {
    width: 210px;
    background: #ffffff;
    color: #0f172a;
    border-color: #ffffff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.35);
}

/* Section Cards */
.adm-card {
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    margin-bottom: 12px;
    overflow: hidden;
}

.adm-card-header {
    background: #f8fafc;
    border-bottom: 1px solid var(--line-subtle);
    padding: 8px 12px;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.adm-card-header i {
    color: var(--green-excel);
}

.adm-card-body {
    padding: 12px;
}

.adm-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 3px;
    display: block;
}

.adm-input {
    height: 31px;
    font-size: 11.5px;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 4px 8px;
    width: 100%;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.adm-input:focus {
    border-color: var(--green-excel);
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15);
    outline: none;
}

.btn-adm-primary {
    background: var(--green-excel) !important;
    border-color: var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    padding: 5px 16px !important;
    border-radius: 3px !important;
    transition: all 0.15s ease;
}

.btn-adm-primary:hover {
    background: var(--green-excel-dark) !important;
    box-shadow: 0 2px 5px rgba(24, 92, 55, 0.3);
}

/* Executive Theme Action Buttons */
.btn-row-action {
    width: 25px;
    height: 25px;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 4px !important;
    font-size: 11.5px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.15s ease-in-out !important;
    border: 1px solid transparent !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.btn-row-action:hover {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.btn-action-edit {
    background: #eff6ff !important;
    border-color: #bfdbfe !important;
    color: #2563eb !important;
}
.btn-action-edit:hover {
    background: #2563eb !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
}
.btn-action-delete {
    background: #fff1f2 !important;
    border-color: #fecdd3 !important;
    color: #e11d48 !important;
}
.btn-action-delete:hover {
    background: #e11d48 !important;
    border-color: #be123c !important;
    color: #ffffff !important;
}

/* Styled Table & Scroll Container */
.table-scroll-container {
    max-height: calc(100vh - 210px);
    min-height: 250px;
    overflow-y: auto;
}

.table-scroll-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.table-scroll-container::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.table-scroll-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.table-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.adm-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 11.5px;
}

.adm-table th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f1f5f9 !important;
    color: #334155;
    font-weight: 700;
    padding: 7px 10px;
    border-bottom: 2px solid #cbd5e1;
    border-top: 1px solid #cbd5e1;
    border-left: 1px solid #cbd5e1;
    border-right: 1px solid #cbd5e1;
    white-space: nowrap;
}

.adm-table tfoot th {
    position: sticky;
    bottom: 0;
    z-index: 3;
    background: #f8fafc !important;
    border-top: 2px solid #cbd5e1 !important;
    border-bottom: 1px solid #cbd5e1;
    padding: 7px 10px;
}

.adm-table td {
    padding: 6px 10px;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
}

.adm-table tbody tr:hover {
    background-color: #f0fdf4;
}
</style>

<div class="content-wrapper course-manage-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            <!-- Main Unified Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-book"></i>
                        <span>Course Stream Management</span>
                        <small>Configure academic degree streams, evaluation systems &amp; durations</small>
                    </h1>
                    
                    <div class="d-flex align-items-center gap-2">
                        <div class="dash-quick-filter mr-2">
                            <i class="fa fa-search"></i>
                            <input type="search" id="courseTableSearch" placeholder="Quick find course..." autocomplete="off">
                        </div>
                        <a href="{{ url('master_dashboard') }}" class="btn-header-action" title="Back to Master Dashboard">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> Back
                        </a>
                    </div>
                </div>

                <!-- 2. Form & Table Grid -->
                <div class="p-3">
                    <div class="row">
                        
                        <!-- Left Column: Add / Edit Form -->
                        <div class="col-lg-4 col-md-5 col-12 mb-3 {{ ($getPermission->add == 1 || $getPermission->edit == 1) ? '' : 'd-none' }}">
                            <div class="adm-card">
                                <div class="adm-card-header">
                                    <span>
                                        <i class="fa {{ $isEdit ? 'fa-edit' : 'fa-plus-circle' }} mr-1"></i>
                                        {{ $isEdit ? 'Edit Course Stream' : 'Add Course Stream' }}
                                    </span>
                                    @if($isEdit)
                                        <a href="{{ url('course_add') }}" class="btn btn-xs btn-outline-secondary" title="Cancel Edit">
                                            <i class="fa fa-times mr-1"></i> Cancel Edit
                                        </a>
                                    @endif
                                </div>
                                <div class="adm-card-body">
                                    <form id="courseForm" action="{{ url('course_add') }}" method="post">
                                        @csrf
                                        @if($isEdit)
                                            <input type="hidden" name="id" value="{{ $editData->id }}">
                                        @endif

                                        <div class="form-group mb-3">
                                            <label class="adm-label">Course / Degree Stream Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control adm-input @error('name') is-invalid @enderror" id="name" name="name" placeholder="e.g. B.A., B.C.A., M.Sc, B.Tech" value="{{ $courseNameValue }}" required autocomplete="off">
                                            @error('name')
                                                <span class="text-danger font-weight-bold" style="font-size: 10.5px;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="adm-label">Course Evaluation System <span class="text-danger">*</span></label>
                                            <select class="form-control adm-input" id="course_type" name="course_type" required onchange="calculateSemesters()">
                                                <option value="Semester" {{ ($courseTypeValue == 'Semester') ? 'selected' : '' }}>Semester Wise</option>
                                                <option value="Yearly" {{ ($courseTypeValue == 'Yearly') ? 'selected' : '' }}>Yearly Wise</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="adm-label">Course Duration <span class="text-danger">*</span></label>
                                            <select class="form-control adm-input" id="duration" name="duration" required onchange="calculateSemesters()">
                                                <option value="1" {{ ($durationValue == 1) ? 'selected' : '' }}>1 Year</option>
                                                <option value="2" {{ ($durationValue == 2) ? 'selected' : '' }}>2 Years</option>
                                                <option value="3" {{ ($durationValue == 3) ? 'selected' : '' }}>3 Years</option>
                                                <option value="4" {{ ($durationValue == 4) ? 'selected' : '' }}>4 Years</option>
                                                <option value="5" {{ ($durationValue == 5) ? 'selected' : '' }}>5 Years</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="adm-label">Total Semesters / Terms <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control adm-input" id="total_semester" name="total_semester" min="0" max="12" value="{{ $totalSemesterValue }}" placeholder="e.g. 6" required>
                                            <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                                <i class="fa fa-info-circle text-info"></i> Automatically set to 0 for Yearly courses (2 semesters per year for Semester courses).
                                            </small>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-2">
                                            <button type="submit" class="btn btn-adm-primary" id="submitButton">
                                                <i class="fa {{ $isEdit ? 'fa-check' : 'fa-save' }} mr-1"></i>
                                                {{ $isEdit ? 'Update Course' : 'Save Course' }}
                                            </button>
                                            @if($isEdit)
                                                <a href="{{ url('course_add') }}" class="btn btn-sm btn-light border">Reset Form</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Courses Directory List -->
                        <div class="{{ ($getPermission->add == 1 || $getPermission->edit == 1) ? 'col-lg-8 col-md-7 col-12' : 'col-12' }}">
                            <div class="adm-card">
                                <div class="adm-card-header">
                                    <span><i class="fa fa-list-alt mr-1"></i> Course Stream Directory ({{ $totalCourses }})</span>
                                    <span id="courseSearchCount" class="text-success font-weight-bold" style="display: none; font-size: 11px;"></span>
                                </div>
                                <div class="adm-card-body p-0">
                                    <div class="table-responsive table-scroll-container">
                                        <table class="adm-table" id="courseDirectoryTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;" class="text-center">#</th>
                                                    <th>Course Stream Name</th>
                                                    <th class="text-center" style="width: 140px;">Type</th>
                                                    <th class="text-center" style="width: 110px;">Duration</th>
                                                    <th class="text-center" style="width: 130px;">Total Semesters</th>
                                                    @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                                        <th class="text-center" style="width: 90px;">Action</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($data) && count($data) > 0)
                                                    @php $i = 1; @endphp
                                                    @foreach ($data as $item)
                                                        <tr class="course-row-item" data-keywords="{{ strtolower($item->name . ' ' . $item->course_type) }}">
                                                            <td class="text-center text-muted font-weight-bold">{{ $i++ }}</td>
                                                            <td>
                                                                <strong style="color: #0f172a; font-size: 12px;">{{ $item->name }}</strong>
                                                            </td>
                                                            <td class="text-center">
                                                                @if(($item->course_type ?? '') == 'Yearly')
                                                                    <span class="badge badge-warning text-dark px-2 py-1" style="font-weight: 600; font-size: 10px;">
                                                                        <i class="fa fa-calendar-o mr-1"></i> Yearly Wise
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-success px-2 py-1" style="font-weight: 600; font-size: 10px;">
                                                                        <i class="fa fa-clock-o mr-1"></i> Semester Wise
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center font-weight-bold text-secondary">
                                                                {{ $item->duration ?? 1 }} {{ ($item->duration > 1) ? 'Years' : 'Year' }}
                                                            </td>
                                                            <td class="text-center">
                                                                @if(($item->course_type ?? '') == 'Yearly' || ($item->total_semester ?? 0) == 0)
                                                                    <span class="badge badge-light border text-muted">N/A (Yearly)</span>
                                                                @else
                                                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600;">
                                                                        {{ $item->total_semester }} Semesters
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                                                <td class="text-center">
                                                                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                                                                        @if($getPermission->edit == 1)
                                                                            <a href="{{ url('course_add') }}?edit_id={{ $item->id }}" class="btn-row-action btn-action-edit" title="Edit Course Stream">
                                                                                <i class="fa fa-edit"></i>
                                                                            </a>
                                                                        @endif
                                                                        @if($getPermission->deletes == 1)
                                                                            <button type="button" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-bs-toggle="modal" data-bs-target="#Modal_id" class="btn-row-action btn-action-delete deleteData" title="Delete Course Stream">
                                                                                <i class="fa fa-trash-o"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="{{ ($getPermission->edit == 1 || $getPermission->deletes == 1) ? 6 : 5 }}" class="text-center py-4 text-muted">
                                                            <i class="fa fa-folder-open-o fa-2x mb-2 text-secondary d-block"></i>
                                                            No course streams found. Create your first course using the form.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot class="adm-table-foot">
                                                <tr>
                                                    <th class="text-center font-weight-bold" style="background: #f8fafc; color: #334155;">
                                                        <i class="fa fa-calculator text-success"></i>
                                                    </th>
                                                    <th colspan="{{ ($getPermission->edit == 1 || $getPermission->deletes == 1) ? 5 : 4 }}" class="font-weight-bold" style="background: #f8fafc; color: #1e293b;">
                                                        <span class="badge" style="background: #217346; color: #ffffff; font-size: 11px; padding: 4px 10px;">
                                                            <i class="fa fa-book mr-1"></i> {{ $totalCourses }} Total Courses Registered
                                                        </span>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </section>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-top: 3px solid #dc2626 !important;">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold text-danger" style="font-size: 14px;">
                    <i class="fa fa-exclamation-triangle mr-1"></i> Delete Course Stream Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('course_delete') }}" method="post">
                @csrf
                <div class="modal-body py-3">
                    <input type="hidden" id="delete_id" name="delete_id">
                    <p class="mb-0 text-dark" style="font-size: 12.5px;">
                        Are you sure you want to delete course stream <strong id="deleteCourseName" class="text-danger"></strong>?
                    </p>
                    <small class="text-muted d-block mt-1">This action cannot be undone if no classes or students are assigned to this course.</small>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
                        <i class="fa fa-trash-o mr-1"></i> Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateSemesters() {
    var type = $('#course_type').val();
    var duration = parseInt($('#duration').val()) || 1;
    if (type === 'Yearly') {
        $('#total_semester').val(0);
    } else {
        $('#total_semester').val(duration * 2);
    }
}

$(function(){
    // Delete Modal Handler
    $(document).on('click', '.deleteData', function() {
        var delete_id = $(this).data('id');
        var name = $(this).data('name');
        $('#delete_id').val(delete_id);
        $('#deleteCourseName').text(name ? '"' + name + '"' : '');
    });

    // Real-Time Table Filter
    $('#courseTableSearch').on('input', function(){
        var term = $.trim($(this).val()).toLowerCase();
        var rows = $('.course-row-item');
        var matched = 0;

        if (term === '') {
            rows.show();
            $('#courseSearchCount').hide();
            return;
        }

        rows.each(function(){
            var keywords = $(this).data('keywords') || '';
            if (keywords.indexOf(term) !== -1) {
                $(this).show();
                matched++;
            } else {
                $(this).hide();
            }
        });

        $('#courseSearchCount').text('Showing ' + matched + ' of ' + rows.length).show();
    });
});
</script>

@endsection
