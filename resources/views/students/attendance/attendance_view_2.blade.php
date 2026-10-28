@extends('layout.app') 
@section('content')
@php
$allClasses = Helper::classType(null, true);
$classType = !empty($allClasses) ? $allClasses->unique('name')->values() : [];
$getPermission = Helper::getPermission();
$courses = Helper::getCourses();
$batches = Helper::getBatch();
@endphp

<style>
/* ========================================================
   STUDENT ATTENDANCE VIEW GRID - EXECUTIVE EXCEL GREEN THEME
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #eef6f0;
    --green-excel-border: #8eb99b;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
    --brand-navy: #002c54;
}

.content-wrapper {
    background-color: #f4f6f9;
}

/* Card & Equal Height Flex Containers */
.attendance-grid-card {
    border: 1px solid #9aa7b1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 0;
    overflow: hidden;
    height: calc(100vh - 125px);
    min-height: 520px;
    display: flex;
    flex-direction: column;
}

/* Executive Titlebar */
.attendance-titlebar {
    min-height: 38px;
    padding: 5px 10px;
    background: var(--green-excel) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-shrink: 0;
}

.attendance-title {
    margin: 0;
    font-size: 13.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #ffffff !important;
}

.attendance-title small {
    font-size: 11px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 4px;
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 2px 8px !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.5;
    cursor: pointer;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-header-action.btn-header-save {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-save:hover {
    background: #166534 !important;
    color: #ffffff !important;
}

/* Filter Toolbar */
.attendance-filter-bar {
    background: #f8fafc;
    border-bottom: 1px solid var(--line);
    padding: 6px 10px;
    flex-shrink: 0;
}
.attendance-filter-bar label {
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.3px;
    color: #475569;
    margin-bottom: 2px;
    display: block;
}
.attendance-filter-bar .form-control {
    height: 27px !important;
    min-height: 27px !important;
    font-size: 11px !important;
    padding: 2px 6px !important;
    border-radius: 2px !important;
    border: 1px solid #9fb3c4 !important;
    color: #0f172a !important;
    background: #ffffff;
}
.attendance-filter-bar .form-control:focus {
    border-color: var(--green-excel) !important;
    outline: none !important;
}
.attendance-filter-bar .select2-container .select2-selection--single {
    height: 27px !important;
    font-size: 11px !important;
    border-radius: 2px !important;
    border: 1px solid #9fb3c4 !important;
}
.attendance-filter-bar .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 25px !important;
    padding-left: 6px !important;
    font-size: 11px !important;
    color: #0f172a !important;
}

/* KPI Summary Ribbon */
.attendance-ribbon {
    background: var(--green-excel-light);
    border-bottom: 1px solid var(--green-excel-border);
    padding: 4px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
}

.kpi-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 7px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    gap: 4px;
    line-height: 1.2;
}
.kpi-badge .count-val {
    font-weight: 700;
}
.kpi-badge-total {
    border-color: #94a3b8;
    background: #0f172a;
    color: #ffffff;
}
.kpi-badge-present {
    border-color: #86efac;
    background: #f0fdf4;
    color: #166534;
}
.kpi-badge-absent {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #991b1b;
}
.kpi-badge-leave {
    border-color: #fde68a;
    background: #fffbeb;
    color: #854d0e;
}
.kpi-badge-holiday {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1e40af;
}
.kpi-badge-halfday {
    border-color: #a5f3fc;
    background: #ecfeff;
    color: #0e7490;
}
.kpi-badge-progress {
    border-color: #fed7aa;
    background: #fff7ed;
    color: #c2410c;
    display: none;
}

/* Scroll Container for Grid Table */
.grid-scroll-container {
    flex: 1 1 auto;
    overflow: auto;
    background: #ffffff;
    position: relative;
}

/* Compact Excel Table */
.attendance-grid-table {
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11px;
    margin-bottom: 0 !important;
}

.attendance-grid-table th,
.attendance-grid-table td {
    padding: 3px 4px !important;
    vertical-align: middle !important;
    border-right: 1px solid var(--line-subtle) !important;
    border-bottom: 1px solid var(--line-subtle) !important;
    border-top: none !important;
    border-left: none !important;
    text-align: center;
    background: #ffffff;
}

.attendance-grid-table thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background: var(--brand-navy) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.2px;
    border-bottom: 1px solid #08223a !important;
    border-right: 1px solid #143d66 !important;
    user-select: none;
}

/* Sticky Column 1: Index */
.attendance-grid-table .sticky-col-idx {
    position: sticky;
    left: 0;
    z-index: 10;
    min-width: 32px;
    max-width: 32px;
    background: #f8fafc;
}
.attendance-grid-table thead th.sticky-col-idx {
    z-index: 25;
    background: var(--brand-navy) !important;
}

/* Sticky Column 2: Admission No */
.attendance-grid-table .sticky-col-adm {
    position: sticky;
    left: 32px;
    z-index: 10;
    min-width: 75px;
    max-width: 75px;
    background: #f8fafc;
}
.attendance-grid-table thead th.sticky-col-adm {
    z-index: 25;
    background: var(--brand-navy) !important;
}

/* Sticky Column 3: Student Details */
.attendance-grid-table .sticky-col-name {
    position: sticky;
    left: 107px;
    z-index: 10;
    min-width: 155px;
    max-width: 180px;
    text-align: left !important;
    background: #f8fafc;
}
.attendance-grid-table thead th.sticky-col-name {
    z-index: 25;
    background: var(--brand-navy) !important;
}

/* Hover Row Effect */
.attendance-grid-table tbody tr:hover td {
    background-color: #f1f5f9;
}
.attendance-grid-table tbody tr:hover .sticky-col-idx,
.attendance-grid-table tbody tr:hover .sticky-col-adm,
.attendance-grid-table tbody tr:hover .sticky-col-name {
    background-color: #e2e8f0;
}

/* Sunday Header Accent */
.attendance-grid-table thead th.th-sunday {
    background: #7f1d1d !important;
    color: #fecaca !important;
    border-right: 1px solid #991b1b !important;
}

/* Sticky Right Summary Columns */
.attendance-grid-table .sticky-col-right-p {
    position: sticky;
    right: 140px;
    z-index: 10;
    min-width: 28px;
    max-width: 28px;
    background: #ffffff;
    border-left: 2px solid var(--line) !important;
    box-shadow: -3px 0 6px rgba(0,0,0,0.08);
}
.attendance-grid-table .sticky-col-right-a {
    position: sticky;
    right: 112px;
    z-index: 10;
    min-width: 28px;
    max-width: 28px;
    background: #ffffff;
}
.attendance-grid-table .sticky-col-right-h {
    position: sticky;
    right: 84px;
    z-index: 10;
    min-width: 28px;
    max-width: 28px;
    background: #ffffff;
}
.attendance-grid-table .sticky-col-right-l {
    position: sticky;
    right: 56px;
    z-index: 10;
    min-width: 28px;
    max-width: 28px;
    background: #ffffff;
}
.attendance-grid-table .sticky-col-right-hd {
    position: sticky;
    right: 28px;
    z-index: 10;
    min-width: 28px;
    max-width: 28px;
    background: #ffffff;
}
.attendance-grid-table .sticky-col-right-e {
    position: sticky;
    right: 0px;
    z-index: 10;
    min-width: 28px;
    max-width: 28px;
    background: #ffffff;
}

/* Sticky Right Summary Headers */
.attendance-grid-table thead th.sticky-col-right-p {
    z-index: 25;
    background: #166534 !important;
    border-right: 1px solid #14532d !important;
    border-left: 2px solid #0f5132 !important;
    box-shadow: -3px 0 6px rgba(0,0,0,0.15);
}
.attendance-grid-table thead th.sticky-col-right-a {
    z-index: 25;
    background: #991b1b !important;
    border-right: 1px solid #7f1d1d !important;
}
.attendance-grid-table thead th.sticky-col-right-h {
    z-index: 25;
    background: #1e40af !important;
    border-right: 1px solid #1e3a8a !important;
}
.attendance-grid-table thead th.sticky-col-right-l {
    z-index: 25;
    background: #854d0e !important;
    border-right: 1px solid #713f12 !important;
}
.attendance-grid-table thead th.sticky-col-right-hd {
    z-index: 25;
    background: #0e7490 !important;
    border-right: 1px solid #155e75 !important;
}
.attendance-grid-table thead th.sticky-col-right-e {
    z-index: 25;
    background: #475569 !important;
    border-right: 1px solid #334155 !important;
}

/* Hover Row Effect on Sticky Right */
.attendance-grid-table tbody tr:hover td.sticky-col-right-p,
.attendance-grid-table tbody tr:hover td.sticky-col-right-a,
.attendance-grid-table tbody tr:hover td.sticky-col-right-h,
.attendance-grid-table tbody tr:hover td.sticky-col-right-l,
.attendance-grid-table tbody tr:hover td.sticky-col-right-hd,
.attendance-grid-table tbody tr:hover td.sticky-col-right-e {
    background-color: #e2e8f0 !important;
}

/* Student Details Cell Styling */
.student-name-text {
    font-weight: 700;
    color: #0f172a;
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
.student-sub-text {
    font-size: 9.5px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    line-height: 1.1;
}

/* Status Badges */
.badge-status {
    display: inline-block;
    min-width: 17px;
    font-size: 9.5px;
    font-weight: 700;
    padding: 1px 3px;
    border-radius: 2px;
    line-height: 1.1;
    text-align: center;
}
.badge-status-p {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
}
.badge-status-a {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}
.badge-status-h {
    background: #dbeafe;
    color: #1d4ed8;
    border: 1px solid #93c5fd;
}
.badge-status-l {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.badge-status-hd {
    background: #cffafe;
    color: #0e7490;
    border: 1px solid #a5f3fc;
}
.badge-status-e {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.time-in {
    color: #15803d;
    font-size: 8px;
    font-weight: 700;
    line-height: 1.0;
    white-space: nowrap;
    margin-top: 1px;
}
.time-out {
    color: #b91c1c;
    font-size: 8px;
    font-weight: 700;
    line-height: 1.0;
    white-space: nowrap;
    margin-top: 1px;
}

.cell-sunday {
    background-color: #fff1f2 !important;
}

/* Empty State Centered Inside Table */
.empty-state-cell {
    padding: 40px 15px !important;
    text-align: center !important;
    background: #ffffff !important;
}
.empty-state-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    max-width: 380px;
}
.empty-state-icon {
    font-size: 38px;
    color: #94a3b8;
    margin-bottom: 8px;
}
.empty-state-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 2px;
}
.empty-state-desc {
    font-size: 11px;
    color: #64748b;
}

/* Bottom Legend Ribbon */
.attendance-footer-bar {
    background: #f8fafc;
    border-top: 1px solid var(--line);
    padding: 4px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
    font-size: 11px;
}
.legend-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 600;
    color: #475569;
    margin-right: 8px;
}
</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-2">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="attendance-grid-card">
                        <!-- Header / Titlebar -->
                        <div class="attendance-titlebar">
                            <div class="attendance-title">
                                <i class="fa fa-table"></i>
                                <span>{{ __('student.View Students Attendance') }}</span>
                                <small>| Monthly Grid Sheet</small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                @if(Session::get('role_id') !== 3)
                                <a href="{{ url('studentsAttendanceAdd') }}" class="btn-header-action btn-header-save" title="Mark Student Attendance">
                                    <i class="fa fa-plus"></i> {{ __('common.Add') }}
                                </a>
                                @endif
                                <a href="{{ url('studentsDashboard') }}" class="btn-header-action" title="Back to Dashboard">
                                    <i class="fa fa-arrow-left text-secondary"></i> {{ __('common.Back') }}
                                </a>
                            </div>
                        </div>

                        <!-- Filter Toolbar -->
                        <div class="attendance-filter-bar">
                            <form id="attendanceGridFilterForm" onsubmit="event.preventDefault(); triggerGridSearch();">
                                <div class="row align-items-end no-gutters">
                                    <div class="col-md-2 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('common.Month') }}*</label>
                                        <select class="form-control select2" id="date__" name="date">
                                            <option value="01" {{ ($search['date'] ?? date('m')) == '01' ? 'selected' : '' }}>January</option>
                                            <option value="02" {{ ($search['date'] ?? date('m')) == '02' ? 'selected' : '' }}>February</option>
                                            <option value="03" {{ ($search['date'] ?? date('m')) == '03' ? 'selected' : '' }}>March</option>
                                            <option value="04" {{ ($search['date'] ?? date('m')) == '04' ? 'selected' : '' }}>April</option>
                                            <option value="05" {{ ($search['date'] ?? date('m')) == '05' ? 'selected' : '' }}>May</option>
                                            <option value="06" {{ ($search['date'] ?? date('m')) == '06' ? 'selected' : '' }}>June</option>
                                            <option value="07" {{ ($search['date'] ?? date('m')) == '07' ? 'selected' : '' }}>July</option>
                                            <option value="08" {{ ($search['date'] ?? date('m')) == '08' ? 'selected' : '' }}>August</option>
                                            <option value="09" {{ ($search['date'] ?? date('m')) == '09' ? 'selected' : '' }}>September</option>
                                            <option value="10" {{ ($search['date'] ?? date('m')) == '10' ? 'selected' : '' }}>October</option>
                                            <option value="11" {{ ($search['date'] ?? date('m')) == '11' ? 'selected' : '' }}>November</option>
                                            <option value="12" {{ ($search['date'] ?? date('m')) == '12' ? 'selected' : '' }}>December</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('Year') }}*</label>
                                        <select class="form-control select2" id="year" name="year">
                                            @for($y = 2023; $y <= 2030; $y++)
                                                <option value="{{ $y }}" {{ ($search['year'] ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('Course') }}</label>
                                        <select class="form-control select2" id="course" name="course">
                                            <option value="">All Courses</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $c)
                                                    <option value="{{ $c->name ?? '' }}" {{ (($search['course'] ?? '') == ($c->name ?? '')) ? 'selected' : '' }}>{{ $c->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('Batch') }}</label>
                                        <select class="form-control select2" id="batch" name="batch">
                                            <option value="">All Batches</option>
                                            @if(!empty($batches))
                                                @foreach($batches as $b)
                                                    <option value="{{ $b->name ?? '' }}" {{ (($search['batch'] ?? '') == ($b->name ?? '')) ? 'selected' : '' }}>{{ $b->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('Class / Semester') }}</label>
                                        <select class="form-control select2" id="class_type_id" name="class_type_id">
                                            @if(Session::get('role_id') != 2)
                                                <option value="">All Classes / Semesters</option>
                                            @endif
                                            @if(!empty($classType))
                                                @foreach($classType as $type)
                                                    <option value="{{ $type->id ?? '' }}" {{ (($search['class_type_id'] ?? '') == ($type->id ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-1 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('student.Admission No.') }}</label>
                                        <input type="text" class="form-control" id="admissionNo" name="admissionNo" placeholder="Adm No." value="{{ $search['admissionNo'] ?? '' }}">
                                    </div>
                                    <div class="col-md-1 col-8 px-1 mb-1 mb-md-0">
                                        <label>{{ __('common.Search By Keywords') }}</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Name, Mobile..." value="{{ $search['name'] ?? '' }}">
                                    </div>
                                    <div class="col-md-1 col-4 px-1 d-flex">
                                        <button type="button" class="btn btn-header-action btn-header-save w-100 justify-content-center" id="btnSearchGrid" style="height: 27px;" onclick="triggerGridSearch()" title="Search Grid">
                                            <i class="fa fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-header-action ml-1" style="height: 27px;" onclick="resetGridFilters()" title="Reset Filters">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- KPI Summary Ribbon -->
                        <div class="attendance-ribbon">
                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <div class="kpi-badge kpi-badge-total">
                                    <span>Total:</span>
                                    <span class="count-val" id="kpi_total">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-present">
                                    <span class="badge-status badge-status-p">P</span> Present:
                                    <span class="count-val" id="kpi_present">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-absent">
                                    <span class="badge-status badge-status-a">A</span> Absent:
                                    <span class="count-val" id="kpi_absent">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-leave">
                                    <span class="badge-status badge-status-l">L</span> Leave:
                                    <span class="count-val" id="kpi_leave">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-holiday">
                                    <span class="badge-status badge-status-h">H</span> Holiday:
                                    <span class="count-val" id="kpi_holiday">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-halfday">
                                    <span class="badge-status badge-status-hd">HD</span> Half Day:
                                    <span class="count-val" id="kpi_halfday">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-progress" id="kpi_progress_badge">
                                    <i class="fa fa-spinner fa-spin mr-1"></i> <span id="kpi_progress_text">Loading attendance...</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <button type="button" class="btn-header-action" onclick="downloadCSV()" title="Export CSV Sheet">
                                    <i class="fa fa-download text-success"></i> Download CSV
                                </button>
                            </div>
                        </div>

                        <!-- Grid Table Scroll Container -->
                        <div class="grid-scroll-container">
                            <table class="table attendance-grid-table" id="table">
                                <thead>
                                    <tr id="days" role="row">
                                        <th class="sticky-col-idx">#</th>
                                        <th class="sticky-col-adm">Adm No.</th>
                                        <th class="sticky-col-name">Student Details</th>
                                    </tr>
                                </thead>
                                <tbody id="student_list">
                                    <tr>
                                        <td colspan="40" class="empty-state-cell">
                                            <div class="empty-state-wrapper">
                                                <i class="fa fa-calendar-check-o empty-state-icon"></i>
                                                <div class="empty-state-title">Ready to view attendance grid</div>
                                                <div class="empty-state-desc">Select Month, Year, and Course/Class filters then click search.</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bottom Legend Ribbon -->
                        <div class="attendance-footer-bar">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="legend-pill"><span class="badge-status badge-status-p">P</span> {{ __('Present') }}</span>
                                <span class="legend-pill"><span class="badge-status badge-status-a">A</span> {{ __('Absent') }}</span>
                                <span class="legend-pill"><span class="badge-status badge-status-h">H</span> {{ __('Holiday') }}</span>
                                <span class="legend-pill"><span class="badge-status badge-status-l">L</span> {{ __('Leave') }}</span>
                                <span class="legend-pill"><span class="badge-status badge-status-hd">HD</span> {{ __('Half Day') }}</span>
                                <span class="legend-pill"><span class="badge-status badge-status-e">E</span> {{ __('Event') }}</span>
                                <span class="legend-pill"><span class="time-in mr-1"><i class="fa fa-clock-o"></i> In</span> Check-in</span>
                                <span class="legend-pill"><span class="time-out mr-1"><i class="fa fa-clock-o"></i> Out</span> Check-out</span>
                            </div>
                            <div class="text-muted small">
                                <i class="fa fa-info-circle text-primary mr-1"></i> Showing monthly records for {{ date('F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    var allStudents = @json($allStudents ?? []);

    function updateUrlParams() {
        var params = new URLSearchParams();
        var date = $('#date__').val();
        var year = $('#year').val();
        var course = $('#course').val();
        var batch = $('#batch').val();
        var class_type_id = $('#class_type_id').val();
        var admissionNo = $('#admissionNo').val();
        var name = $('#name').val();

        if (date) params.set('date', date);
        if (year) params.set('year', year);
        if (course) params.set('course', course);
        if (batch) params.set('batch', batch);
        if (class_type_id) params.set('class_type_id', class_type_id);
        if (admissionNo) params.set('admissionNo', admissionNo);
        if (name) params.set('name', name);

        var queryString = params.toString();
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.history.pushState({ path: newUrl }, '', newUrl);
    }

    function updateClassDropdown(classSelect, data, selectedVal) {
        classSelect.empty();
        @if(Session::get('role_id') != 2)
        classSelect.append('<option value="">All Classes / Semesters</option>');
        @endif
        if (data && data.length > 0) {
            $.each(data, function(key, val) {
                var isSelected = (selectedVal && selectedVal.toString() === val.id.toString()) ? 'selected' : '';
                classSelect.append('<option value="' + val.id + '" ' + isSelected + '>' + val.name + '</option>');
            });
        }
        if (classSelect.hasClass("select2-hidden-accessible")) {
            classSelect.select2('destroy');
        }
        classSelect.select2({ theme: 'bootstrap4', width: '100%' });
        if (selectedVal) {
            classSelect.val(selectedVal).trigger('change.select2');
        }
    }

    function loadFilteredClasses(selectedClassId, callback) {
        var courseName = $('#course').val();
        var batch = $('#batch').val();
        var classSelect = $('#class_type_id');
        if (courseName) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course: courseName, batch: batch, ignore_session: 1 },
                dataType: "json",
                success: function(data) {
                    updateClassDropdown(classSelect, data, selectedClassId);
                    if (typeof callback === 'function') callback();
                },
                error: function() {
                    updateClassDropdown(classSelect, [], selectedClassId);
                    if (typeof callback === 'function') callback();
                }
            });
        } else {
            var defaultClasses = [];
            @if(!empty($classType))
                @foreach($classType as $type)
                    defaultClasses.push({ id: "{{ $type->id }}", name: "{{ $type->name }}" });
                @endforeach
            @endif
            updateClassDropdown(classSelect, defaultClasses, selectedClassId);
            if (typeof callback === 'function') callback();
        }
    }

    function resetGridFilters() {
        $('#course').val('').trigger('change.select2');
        $('#batch').val('').trigger('change.select2');
        $('#name').val('');
        $('#admissionNo').val('');
        $('#date__').val('{{ date("m") }}').trigger('change.select2');
        $('#year').val('{{ date("Y") }}').trigger('change.select2');

        loadFilteredClasses('', function() {
            window.history.pushState({}, '', window.location.pathname);
            $('#student_list').html(
                '<tr>' +
                    '<td colspan="40" class="empty-state-cell">' +
                        '<div class="empty-state-wrapper">' +
                            '<i class="fa fa-calendar-check-o empty-state-icon"></i>' +
                            '<div class="empty-state-title">Ready to view attendance grid</div>' +
                            '<div class="empty-state-desc">Select Month, Year, and Course/Class filters then click search.</div>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            );
            $('#kpi_total').text(0);
            $('#kpi_present').text(0);
            $('#kpi_absent').text(0);
            $('#kpi_leave').text(0);
            $('#kpi_holiday').text(0);
            $('#kpi_halfday').text(0);
            $('#kpi_progress_badge').hide();
        });
    }

    function triggerGridSearch(updateUrl) {
        if (typeof updateUrl === 'undefined') {
            updateUrl = true;
        }

        if (updateUrl) {
            updateUrlParams();
        }

        var date_array = [];
        var name = ($('#name').val() || '').toLowerCase().trim();
        var course = $('#course').val() || '';
        var class_type_id = $('#class_type_id').val() || '';
        var batch = $('#batch').val() || '';
        var monthVal = $('#date__').val() || '{{ date("m") }}';
        var date = parseInt(monthVal, 10);
        var admissionNo = ($('#admissionNo').val() || '').trim();
        var year = $('#year').val() || '{{ date("Y") }}';
        var monthIndex = date - 1;
        var daysInMonth = new Date(year, date, 0).getDate();

        var searchBtn = $('#btnSearchGrid');
        if (searchBtn.length) {
            searchBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        }

        // Render Header Days
        var days = $('#days');
        days.html('');
        days.append('<th class="sticky-col-idx">#</th>');
        days.append('<th class="sticky-col-adm">Adm No.</th>');
        days.append('<th class="sticky-col-name">Student Details</th>');

        var row_days = '';
        for (var i = 1; i <= daysInMonth; i++) {
            var dt = new Date(year, monthIndex, i);
            var dayOfWeek = dt.toLocaleString('en', { weekday: 'short' });
            var isSunday = (dt.getDay() === 0);
            var sunClass = isSunday ? 'th-sunday' : '';
            row_days += '<th class="text-center ' + sunClass + '" style="min-width:32px; padding:3px 2px;"><span style="font-size:11px; font-weight:700;">' + i + '</span><br><span style="font-size:8.5px; font-weight:normal; opacity:0.9;">' + dayOfWeek + '</span></th>';
        }

        row_days += '<th class="text-center sticky-col-right-p" style="min-width:28px; padding:3px 2px; font-size:10px;" title="Present">P</th>';
        row_days += '<th class="text-center sticky-col-right-a" style="min-width:28px; padding:3px 2px; font-size:10px;" title="Absent">A</th>';
        row_days += '<th class="text-center sticky-col-right-h" style="min-width:28px; padding:3px 2px; font-size:10px;" title="Holiday">H</th>';
        row_days += '<th class="text-center sticky-col-right-l" style="min-width:28px; padding:3px 2px; font-size:10px;" title="Leave">L</th>';
        row_days += '<th class="text-center sticky-col-right-hd" style="min-width:28px; padding:3px 2px; font-size:10px;" title="Half Day">HD</th>';
        row_days += '<th class="text-center sticky-col-right-e" style="min-width:28px; padding:3px 2px; font-size:10px;" title="Event">E</th>';
        days.append(row_days);

        var container = $('#student_list');
        container.html('');
        var count = 0;

        allStudents.forEach(function(item) {
            var matchClass = true;
            if (class_type_id) {
                matchClass = (parseInt(item.class_type_id) == parseInt(class_type_id));
            }
            var matchCourse = true;
            if (course) {
                matchCourse = (item.course == course);
            }
            var matchBatch = true;
            if (batch) {
                matchBatch = (item.batch == batch);
            }
            var matchAdmissionNo = true;
            if (admissionNo) {
                matchAdmissionNo = (item.admissionNo == admissionNo || item.id == admissionNo);
            }
            var matchName = true;
            if (name) {
                var fullName = ((item.first_name || '') + ' ' + (item.last_name || '')).toLowerCase();
                var mobile = (item.mobile || '');
                var email = (item.email || '').toLowerCase();
                var aadhaar = (item.aadhaar || '');
                matchName = (fullName.includes(name) || mobile.includes(name) || email.includes(name) || aadhaar.includes(name));
            }

            if (matchClass && matchCourse && matchBatch && matchAdmissionNo && matchName) {
                var admDisplay = item.admissionNo ? item.admissionNo : item.id;
                var nameDisplay = item.first_name + (item.last_name ? ' ' + item.last_name : '');
                var courseDisplay = item.course || '';
                var batchDisplay = item.batch ? ' (' + item.batch + ')' : '';

                var row = '<tr id="' + item.id + '">' +
                    '<td class="sticky-col-idx font-weight-bold text-muted text-center">' + (count + 1) + '</td>' +
                    '<td class="sticky-col-adm font-weight-bold text-center"><span class="badge badge-light border text-dark">' + admDisplay + '</span></td>' +
                    '<td class="sticky-col-name" title="' + nameDisplay + '">' +
                        '<span class="student-name-text">' + nameDisplay + '</span>' +
                        '<span class="student-sub-text">' + courseDisplay + batchDisplay + '</span>' +
                    '</td>';

                var row2 = '';
                var array_d = [];
                for (var i = 1; i <= daysInMonth; i++) {
                    var dtCell = new Date(year, monthIndex, i);
                    var isSunCell = (dtCell.getDay() === 0);
                    var cellSunClass = isSunCell ? ' cell-sunday' : '';
                    var dayStr = (i < 10 ? '0' + i : i);
                    var newclass = year + '-' + monthVal + '-' + dayStr;
                    array_d.push(newclass);
                    row2 += '<td class="attendance-cell ' + newclass + '_' + item.id + cellSunClass + '"></td>';
                }

                var row3 = '';
                row3 += '<td class="text-center font-weight-bold text-success sticky-col-right-p persent_' + item.id + '">0</td>';
                row3 += '<td class="text-center font-weight-bold text-danger sticky-col-right-a absent_' + item.id + '">0</td>';
                row3 += '<td class="text-center font-weight-bold text-primary sticky-col-right-h holiday_' + item.id + '">0</td>';
                row3 += '<td class="text-center font-weight-bold text-warning sticky-col-right-l leave_' + item.id + '">0</td>';
                row3 += '<td class="text-center font-weight-bold text-info sticky-col-right-hd halfday_' + item.id + '">0</td>';
                row3 += '<td class="text-center font-weight-bold text-secondary sticky-col-right-e event_' + item.id + '">0</td>';

                container.append(row + row2 + row3 + '</tr>');
                date_array[count] = { 'id': item.id, 'date': array_d };
                count++;
            }
        });

        $('#kpi_total').text(count);

        if (count === 0) {
            if (searchBtn.length) {
                searchBtn.prop('disabled', false).html('<i class="fa fa-search"></i>');
            }
            container.html(
                '<tr>' +
                    '<td colspan="' + (daysInMonth + 9) + '" class="empty-state-cell">' +
                        '<div class="empty-state-wrapper">' +
                            '<i class="fa fa-user-times empty-state-icon"></i>' +
                            '<div class="empty-state-title">No Students Found</div>' +
                            '<div class="empty-state-desc">No student records match the selected filter criteria.</div>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            );
            $('#kpi_present').text(0);
            $('#kpi_absent').text(0);
            $('#kpi_leave').text(0);
            $('#kpi_holiday').text(0);
            $('#kpi_halfday').text(0);
            $('#kpi_progress_badge').hide();
            return;
        }

        // Slot batch asynchronous fetching
        var result = [];
        function divideIntoSlots(number) {
            var slots = Math.ceil(number / 20);
            var start = 0;
            for (var i = 0; i < slots; i++) {
                var slotValue = Math.min(20, number);
                var end = start + slotValue - 1;
                result.push({ 'from': start, 'to': end });
                start = end + 1;
                number -= slotValue;
            }
            return result;
        }

        var slots = divideIntoSlots(date_array.length);
        var loop = 0;
        $('#kpi_progress_badge').show();

        fetchData();
        function fetchData() {
            if (loop < result.length) {
                $('#kpi_progress_text').text('Loading batch ' + (loop + 1) + ' of ' + result.length + '...');
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    type: 'POST',
                    url: "{{ url('studentsAttendanceViewTable') }}",
                    data: { data: JSON.stringify(date_array), loop: result[loop] },
                    success: function (response) {
                        if (response && response.data) {
                            $.each(response.data, function(index, item) {
                                $.each(item, function(index2, item2) {
                                    var span_row = '';
                                    var inTime = convertTo12HrFormat(item2.time);
                                    var outTime = convertTo12HrFormat(item2.out_time);
                                    var timeHtml = '';
                                    if (inTime) timeHtml += '<span class="time-in">In:' + inTime + '</span>';
                                    if (outTime) timeHtml += '<span class="time-out">Out:' + outTime + '</span>';

                                    if (item2.attendance_status_id == 1) {
                                        span_row = '<div class="d-flex flex-column align-items-center"><span class="badge-status badge-status-p">P</span>' + timeHtml + '</div>';
                                    } else if (item2.attendance_status_id == 2) {
                                        span_row = '<span class="badge-status badge-status-a">A</span>';
                                    } else if (item2.attendance_status_id == 3 || item2.attendance_status_id == 9) {
                                        span_row = '<span class="badge-status badge-status-l">L</span>';
                                    } else if (item2.attendance_status_id == 4) {
                                        span_row = '<div class="d-flex flex-column align-items-center"><span class="badge-status badge-status-hd">HD</span>' + timeHtml + '</div>';
                                    } else if (item2.attendance_status_id == 5) {
                                        span_row = '<span class="badge-status badge-status-h">H</span>';
                                    } else if (item2.attendance_status_id == 10) {
                                        span_row = '<span class="badge-status badge-status-e">E</span>';
                                    }
                                    $('.' + item2.date + '_' + item2.admission_id).html(span_row);
                                });
                            });
                        }

                        loop++;
                        fetchData();
                        countStatuses();
                    },
                    error: function() {
                        loop++;
                        fetchData();
                    }
                });
            } else {
                $('#kpi_progress_badge').hide();
                if (searchBtn.length) {
                    searchBtn.prop('disabled', false).html('<i class="fa fa-search"></i>');
                }
            }
        }
    }

    function countStatuses() {
        var totalPresent = 0, totalAbsent = 0, totalHoliday = 0, totalLeave = 0, totalHalfDay = 0;
        const table = document.getElementById('table');
        if (!table) return;
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            let present = 0, absent = 0, holiday = 0, event = 0, leave = 0, halfday = 0;
            const rowId = row.id;
            if (!rowId) continue;

            const totalCells = row.cells.length;
            const dayCellEnd = totalCells - 6;
            for (let i = 3; i < dayCellEnd; i++) {
                const cell = row.cells[i];
                const badge = cell.querySelector('.badge-status');
                if (badge) {
                    const status = badge.innerText.trim();
                    switch (status) {
                        case 'P':
                            present++;
                            totalPresent++;
                            break;
                        case 'A':
                            absent++;
                            totalAbsent++;
                            break;
                        case 'H':
                            holiday++;
                            totalHoliday++;
                            break;
                        case 'L':
                            leave++;
                            totalLeave++;
                            break;
                        case 'HD':
                            halfday++;
                            totalHalfDay++;
                            break;
                        case 'E':
                            event++;
                            break;
                    }
                }
            }

            const presentCell = document.querySelector(`.persent_${rowId}`);
            const absentCell = document.querySelector(`.absent_${rowId}`);
            const holidayCell = document.querySelector(`.holiday_${rowId}`);
            const leaveCell = document.querySelector(`.leave_${rowId}`);
            const halfdayCell = document.querySelector(`.halfday_${rowId}`);
            const eventCell = document.querySelector(`.event_${rowId}`);

            if (presentCell) presentCell.innerText = present;
            if (absentCell) absentCell.innerText = absent;
            if (holidayCell) holidayCell.innerText = holiday;
            if (leaveCell) leaveCell.innerText = leave;
            if (halfdayCell) halfdayCell.innerText = halfday;
            if (eventCell) eventCell.innerText = event;
        }

        $('#kpi_present').text(totalPresent);
        $('#kpi_absent').text(totalAbsent);
        $('#kpi_leave').text(totalLeave);
        $('#kpi_holiday').text(totalHoliday);
        $('#kpi_halfday').text(totalHalfDay);
    }

    function convertTo12HrFormat(time24h) {
        if (!time24h || time24h === 'null' || time24h === 'undefined' || time24h === '00:00:00' || String(time24h).trim() === '') {
            return '';
        }
        var timeStr = String(time24h).trim();
        var timeArray = timeStr.split(':');
        if (timeArray.length < 2) return timeStr;
        var hours = parseInt(timeArray[0], 10);
        var minutes = parseInt(timeArray[1], 10);
        if (isNaN(hours) || isNaN(minutes)) return '';
        var period = hours < 12 ? 'AM' : 'PM';

        if (hours === 0) {
            hours = 12;
        } else if (hours > 12) {
            hours = hours - 12;
        }

        return hours + ':' + (minutes < 10 ? '0' + minutes : minutes) + ' ' + period;
    }

    function downloadCSV() {
        var monthText = $('#date__ option:selected').text();
        var yearText = $('#year').val();
        var classText = $('#class_type_id option:selected').text() || 'All Classes';
        var courseText = $('#course option:selected').text() || 'All Courses';

        let csv = [];
        csv.push(`"Attendance Report - ${monthText} ${yearText}"`);
        csv.push(`"Course: ${courseText}, Class: ${classText}"`);
        csv.push("");

        const rows = document.querySelectorAll("#table tr");

        for (const row of rows.values()) {
            const cells = row.querySelectorAll("td, th");
            if (cells.length <= 1) continue;
            const rowText = Array.from(cells).map((cell) => {
                return '"' + cell.innerText.replace(/"/g, '""').replace(/\r?\n/g, ' ').trim() + '"';
            });
            csv.push(rowText.join(","));
        }

        const csvContent = "data:text/csv;charset=utf-8," + encodeURIComponent(csv.join("\n"));
        const link = document.createElement("a");
        link.setAttribute("href", csvContent);
        link.setAttribute("download", `Student_Attendance_${monthText}_${yearText}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // URL Parameters handling
        var urlParams = new URLSearchParams(window.location.search);
        var paramDate = urlParams.get('date');
        var paramYear = urlParams.get('year');
        var paramCourse = urlParams.get('course');
        var paramBatch = urlParams.get('batch');
        var paramClass = urlParams.get('class_type_id');
        var paramAdm = urlParams.get('admissionNo');
        var paramName = urlParams.get('name');

        var hasParams = false;

        if (paramDate) {
            $('#date__').val(paramDate).trigger('change.select2');
            hasParams = true;
        }
        if (paramYear) {
            $('#year').val(paramYear).trigger('change.select2');
            hasParams = true;
        }
        if (paramCourse) {
            $('#course').val(paramCourse).trigger('change.select2');
            hasParams = true;
        }
        if (paramBatch) {
            $('#batch').val(paramBatch).trigger('change.select2');
            hasParams = true;
        }
        if (paramAdm) {
            $('#admissionNo').val(paramAdm);
            hasParams = true;
        }
        if (paramName) {
            $('#name').val(paramName);
            hasParams = true;
        }
        if (paramClass) {
            hasParams = true;
        }

        loadFilteredClasses(paramClass, function() {
            if (hasParams || allStudents.length > 0) {
                triggerGridSearch(false);
            }
        });

        $(document).on('change', '#course', function() {
            loadFilteredClasses();
        });

        $(document).on('change', '#batch', function() {
            if ($('#course').val()) {
                loadFilteredClasses();
            }
        });

        // Handle Browser History Back / Forward
        window.addEventListener('popstate', function() {
            var currentParams = new URLSearchParams(window.location.search);
            var curDate = currentParams.get('date') || '{{ date("m") }}';
            var curYear = currentParams.get('year') || '{{ date("Y") }}';
            var curCourse = currentParams.get('course') || '';
            var curBatch = currentParams.get('batch') || '';
            var curClass = currentParams.get('class_type_id') || '';
            var curAdm = currentParams.get('admissionNo') || '';
            var curName = currentParams.get('name') || '';

            $('#date__').val(curDate).trigger('change.select2');
            $('#year').val(curYear).trigger('change.select2');
            $('#course').val(curCourse).trigger('change.select2');
            $('#batch').val(curBatch).trigger('change.select2');
            $('#admissionNo').val(curAdm);
            $('#name').val(curName);

            loadFilteredClasses(curClass, function() {
                triggerGridSearch(false);
            });
        });
    });
</script>
@endsection