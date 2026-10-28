@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $getPermission = Helper::getPermission();
    $isEdit = isset($editData) && !empty($editData->id);
    $selectedCourseId = old('course_id', $editData->course_id ?? '');
    $classNameValue = old('name', $editData->name ?? '');
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   CLASS & SEMESTER MANAGEMENT - ADMISSION ADD THEME SYSTEM
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

.class-manage-page {
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

/* ========================================================
   MODAL THEME (Matching promote_add modal layout)
   ======================================================== */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 3px !important;
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

<div class="content-wrapper class-manage-page">
    <section class="content pt-2">
        <div class="container-fluid px-2">

            <!-- Main Unified Card -->
            <div class="dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-university"></i>
                        <span>Class &amp; Semester Management</span>
                        <small>Configure academic classes, section divisions &amp; course curriculums</small>
                    </h1>
                    
                    <div class="d-flex align-items-center gap-2">
                        <div class="dash-quick-filter mr-2">
                            <i class="fa fa-search"></i>
                            <input type="search" id="classTableSearch" placeholder="Quick find class..." autocomplete="off">
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
                                        {{ $isEdit ? 'Edit Class / Semester' : 'Add Class / Semester' }}
                                    </span>
                                    @if($isEdit)
                                        <a href="{{ url('add_class') }}" class="btn btn-xs btn-outline-secondary" title="Cancel Edit">
                                            <i class="fa fa-times mr-1"></i> Cancel Edit
                                        </a>
                                    @endif
                                </div>
                                <div class="adm-card-body">
                                    <form id="classForm" action="{{ url('add_class') }}" method="post">
                                        @csrf
                                        @if($isEdit)
                                            <input type="hidden" name="id" value="{{ $editData->id }}">
                                        @endif

                                        <div class="form-group mb-3">
                                            <label class="adm-label">Course / Degree Stream <span class="text-danger">*</span></label>
                                            <select class="form-control select2 adm-input @error('course_id') is-invalid @enderror" id="course_id" name="course_id" required>
                                                <option value="">-- Select Course --</option>
                                                @if(!empty($courses))
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id }}" {{ ($selectedCourseId == $course->id) ? 'selected' : '' }}>
                                                            {{ $course->name }} ({{ $course->duration }} {{ $course->duration > 1 ? 'Yrs' : 'Yr' }})
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('course_id')
                                                <span class="text-danger font-weight-bold" style="font-size: 10.5px;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="adm-label">Class / Semester Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control adm-input @error('name') is-invalid @enderror" id="name" name="name" placeholder="e.g. BA 1st Sem, BCA 2nd Sem, M.Sc 1st Yr" value="{{ $classNameValue }}" required autocomplete="off">
                                            <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                                <i class="fa fa-info-circle text-info"></i> Sort priority is automatically calculated from semester/year terms.
                                            </small>
                                            @error('name')
                                                <span class="text-danger font-weight-bold" style="font-size: 10.5px;">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-2">
                                            <button type="submit" class="btn btn-adm-primary" id="submitButton">
                                                <i class="fa {{ $isEdit ? 'fa-check' : 'fa-save' }} mr-1"></i>
                                                {{ $isEdit ? 'Update Class' : 'Save Class' }}
                                            </button>
                                            @if($isEdit)
                                                <a href="{{ url('add_class') }}" class="btn btn-sm btn-light border">Reset Form</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Classes Directory List -->
                        @php
                            $totalClasses = count($data ?? []);
                            $uniqueCoursesCount = (!empty($data) && count($data) > 0) ? $data->pluck('course_id')->unique()->count() : 0;
                            $totalStudentsCount = (!empty($data) && count($data) > 0) ? $data->sum('admissions_count') : 0;
                            $groupedData = (!empty($data) && count($data) > 0) ? $data->groupBy(function($item) {
                                return $item->course->name ?? 'General / Unassigned';
                            }) : collect();
                        @endphp
                        <div class="{{ ($getPermission->add == 1 || $getPermission->edit == 1) ? 'col-lg-8 col-md-7 col-12' : 'col-12' }}">
                            <div class="adm-card">
                                <div class="adm-card-header">
                                    <span><i class="fa fa-list-alt mr-1"></i> Class &amp; Semester Directory ({{ $totalClasses }})</span>
                                    <span id="classSearchCount" class="text-success font-weight-bold" style="display: none; font-size: 11px;"></span>
                                </div>
                                <div class="adm-card-body p-0">
                                    <div class="table-responsive table-scroll-container">
                                        <table class="adm-table" id="classDirectoryTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;" class="text-center">#</th>
                                                    <th>Course Stream</th>
                                                    <th>Class / Semester Name</th>
                                                    <th class="text-center" style="width: 110px;">Students</th>
                                                    @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                                        <th class="text-center" style="width: 90px;">Action</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($groupedData) && $groupedData->count() > 0)
                                                    @php $globalIndex = 1; @endphp
                                                    @foreach ($groupedData as $courseName => $classItems)
                                                        <tr class="course-group-header" style="background: #f1f5f9; font-weight: 700; color: #1e293b; border-bottom: 1px solid #cbd5e1; border-top: 2px solid #cbd5e1;">
                                                            <td colspan="{{ ($getPermission->edit == 1 || $getPermission->deletes == 1) ? 5 : 4 }}" class="py-1 px-3">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <span style="font-size: 11.5px;">
                                                                        <i class="fa fa-graduation-cap text-success mr-2"></i>
                                                                        <strong>{{ $courseName }}</strong>
                                                                    </span>
                                                                    <span class="badge" style="background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; font-weight: 600; font-size: 10px;">
                                                                        <i class="fa fa-list-ol mr-1"></i> {{ count($classItems) }} {{ count($classItems) > 1 ? 'Semesters / Classes' : 'Semester / Class' }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @foreach ($classItems as $item)
                                                            @php
                                                                $admCount = $item->admissions_count ?? 0;
                                                            @endphp
                                                            <tr class="class-row-item" data-keywords="{{ strtolower(($item->course->name ?? 'General') . ' ' . $item->name) }}">
                                                                <td class="text-center text-muted font-weight-bold">{{ $globalIndex++ }}</td>
                                                                <td>
                                                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600; font-size: 10.5px;">
                                                                        {{ $item->course->name ?? 'General' }}
                                                                    </span>
                                                                </td>
                                                                <td><strong style="color: #0f172a;">{{ $item->name }}</strong></td>
                                                                <td class="text-center">
                                                                    @if($admCount > 0)
                                                                        <span class="badge badge-success px-2 py-1" title="Enrolled Students">
                                                                            <i class="fa fa-users mr-1"></i> {{ $admCount }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-light text-muted border">0 Enrolled</span>
                                                                    @endif
                                                                </td>
                                                                 @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                                                    <td class="text-center">
                                                                        <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                                                                            @if($getPermission->edit == 1)
                                                                                <a href="{{ url('add_class') }}?edit_id={{ $item->id }}" class="btn-row-action btn-action-edit" title="Edit Class / Semester">
                                                                                    <i class="fa fa-edit"></i>
                                                                                </a>
                                                                            @endif
                                                                            @if($getPermission->deletes == 1 && $admCount == 0)
                                                                                <button type="button" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-course="{{ $item->course->name ?? 'General' }}" data-students="{{ $admCount }}" data-bs-toggle="modal" data-bs-target="#Modal_id" data-toggle="modal" data-target="#Modal_id" class="btn-row-action btn-action-delete deleteData" title="Delete Class / Semester">
                                                                                    <i class="fa fa-trash-o"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="{{ ($getPermission->edit == 1 || $getPermission->deletes == 1) ? 5 : 4 }}" class="text-center py-4 text-muted">
                                                            <i class="fa fa-folder-open-o fa-2x mb-2 text-secondary d-block"></i>
                                                            No classes or semesters found. Create your first class using the form.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot class="adm-table-foot">
                                                <tr>
                                                    <th class="text-center font-weight-bold" style="background: #f8fafc; color: #334155;">
                                                        <i class="fa fa-calculator text-success"></i>
                                                    </th>
                                                    <th class="font-weight-bold" style="background: #f8fafc; color: #1e293b;">
                                                        <span class="badge" style="background: #217346; color: #ffffff; font-size: 11px; padding: 4px 8px;">
                                                            <i class="fa fa-graduation-cap mr-1"></i> {{ $uniqueCoursesCount }} Total Courses
                                                        </span>
                                                    </th>
                                                    <th class="font-weight-bold" style="background: #f8fafc; color: #1e293b;">
                                                        <span class="badge" style="background: #0284c7; color: #ffffff; font-size: 11px; padding: 4px 8px;">
                                                            <i class="fa fa-list-alt mr-1"></i> {{ $totalClasses }} Total Classes / Semesters
                                                        </span>
                                                    </th>
                                                    <th class="text-center font-weight-bold" style="background: #f8fafc; color: #1e293b;">
                                                        <span class="badge" style="background: #475569; color: #ffffff; font-size: 11px; padding: 4px 8px;">
                                                            <i class="fa fa-users mr-1"></i> {{ $totalStudentsCount }} Enrolled
                                                        </span>
                                                    </th>
                                                    @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                                        <th style="background: #f8fafc;"></th>
                                                    @endif
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

<!-- Custom Excel-Themed Delete Confirmation Modal (Matching promote_add) -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-exclamation-triangle text-danger"></i> Delete Class Confirmation
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('class_delete') }}" method="post" id="deleteClassForm">
                @csrf
                <div class="modal-body p-3">
                    <input type="hidden" id="delete_id" name="delete_id">
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fff1f2; border: 1px solid #fecdd3; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa fa-trash-o text-danger" style="font-size: 18px;"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Delete Class / Semester</strong>
                            <span class="text-muted" style="font-size: 11.5px;">Review class details before confirming deletion:</span>
                        </div>
                    </div>

                    <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Class / Semester Name:</span>
                            <strong class="text-dark" id="deleteClassName">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Course Stream:</span>
                            <strong class="text-dark" id="deleteCourseName">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Enrolled Students:</span>
                            <strong class="text-success font-weight-bold" id="deleteStudentCount">0 Students</strong>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mb-0" style="font-size: 11px; border-radius: 2px; background: #fffbebf5; border-color: #fde68a; color: #92400e;">
                        <i class="fa fa-info-circle mr-1"></i> Permanent Action: Once deleted, this class record cannot be restored.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3">
                        <i class="fa fa-trash-o mr-1"></i> Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function(){
    // Delete Modal Handler - Real-Time Data Population
    $(document).on('click', '.deleteData', function() {
        var delete_id = $(this).data('id');
        var name = $(this).data('name') || '';
        var course = $(this).data('course') || '';
        var students = $(this).data('students') ?? 0;

        $('#delete_id').val(delete_id);
        $('#deleteClassName').text(name ? name : '-');
        $('#deleteCourseName').text(course ? course : '-');
        $('#deleteStudentCount').text(students + (parseInt(students) === 1 ? ' Student' : ' Students'));
        
        if (parseInt(students) > 0) {
            $('#deleteStudentCount').removeClass('text-success').addClass('text-danger');
        } else {
            $('#deleteStudentCount').removeClass('text-danger').addClass('text-success');
        }
    });

    // Real-Time Table Filter
    $('#classTableSearch').on('input', function(){
        var term = $.trim($(this).val()).toLowerCase();
        var rows = $('.class-row-item');
        var matched = 0;

        if (term === '') {
            rows.show();
            $('.course-group-header').show();
            $('#classSearchCount').hide();
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

        // Hide course headers if no visible rows belong to them
        $('.course-group-header').each(function(){
            var hasVisibleChild = false;
            var curr = $(this).next();
            while (curr.length > 0 && !curr.hasClass('course-group-header')) {
                if (curr.is(':visible')) {
                    hasVisibleChild = true;
                    break;
                }
                curr = curr.next();
            }
            if (hasVisibleChild) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('#classSearchCount').text('Showing ' + matched + ' of ' + rows.length).show();
    });
});
</script>

@endsection
