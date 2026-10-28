@php
$allClasses = Helper::classType(null, true);
$classType = !empty($allClasses) ? $allClasses->unique('name')->values() : [];
$getAttendanceStatus = Helper::getAttendanceStatus();
$courses = Helper::getCourses();
$batches = Helper::getBatch();
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   STUDENT ATTENDANCE - EXECUTIVE EXCEL GREEN THEME
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
.attendance-layout-row {
    display: flex;
    flex-wrap: wrap;
}

.attendance-card {
    border: 1px solid #9aa7b1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 0;
    overflow: hidden;
    height: calc(100vh - 125px);
    min-height: 480px;
    display: flex;
    flex-direction: column;
}

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
    padding: 0 5px;
    border-radius: 2px;
    background: #f1f5f9;
}
.kpi-badge-total .count-val { background: #334155; color: #ffffff; }
.kpi-badge-present { background: #f0fdf4; border-color: #86efac; color: #166534; }
.kpi-badge-present .count-val { background: #166534; color: #ffffff; }
.kpi-badge-absent { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.kpi-badge-absent .count-val { background: #dc2626; color: #ffffff; }
.kpi-badge-leave { background: #f0f9ff; border-color: #7dd3fc; color: #075985; }
.kpi-badge-leave .count-val { background: #0284c7; color: #ffffff; }
.kpi-badge-unmarked { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.kpi-badge-unmarked .count-val { background: #d97706; color: #ffffff; }

/* Quick Action Buttons */
.btn-quick-action {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    padding: 2px 7px !important;
    height: 24px !important;
    border-radius: 2px !important;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    line-height: 1;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}
.btn-quick-present { background: #f0fdf4; border-color: #86efac; color: #166534; }
.btn-quick-present:hover { background: #166534; color: #ffffff; }
.btn-quick-absent { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.btn-quick-absent:hover { background: #dc2626; color: #ffffff; }
.btn-quick-reset { background: #fffbeb; border-color: #fde68a; color: #b45309; }
.btn-quick-reset:hover { background: #b45309; color: #ffffff; }
.btn-quick-save { background: var(--green-excel); border-color: var(--green-excel-dark); color: #ffffff; }
.btn-quick-save:hover { background: var(--green-excel-dark); color: #ffffff; }

/* Flex Table Containers & Excel Layout */
#attendanceForm {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    margin-bottom: 0;
}

.attendance-scroll-container {
    flex: 1;
    min-height: 0;
    height: 100%;
    overflow: auto;
    background: #ffffff;
    border-bottom: 1px solid var(--line);
    position: relative;
}

.calendar-scroll-container {
    flex: 1;
    min-height: 0;
    height: 100%;
    overflow: auto;
    background: #ffffff;
    border-bottom: 1px solid var(--line);
    position: relative;
}

.attendance-table, .calendar-table {
    margin: 0 !important;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px;
    border-top: 1px solid var(--line) !important;
    border-left: 1px solid var(--line) !important;
}

.attendance-table thead th, .calendar-table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
    text-align: center;
    height: 30px !important;
    line-height: 20px !important;
    border-top: 0 !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    padding: 3px 6px !important;
}

.attendance-table td, .calendar-table td {
    padding: 3px 6px !important;
    border-right: 1px solid var(--line) !important;
    border-bottom: 1px solid var(--line) !important;
    vertical-align: middle !important;
    font-size: 11.5px;
    height: 30px;
    box-sizing: border-box;
}

.attendance-table tbody tr:nth-child(even) td, .calendar-table tbody tr:nth-child(even) td { background-color: #fafafa; }
.attendance-table tbody tr:hover td, .calendar-table tbody tr:hover td { background-color: #fffde7 !important; }

/* Empty state centering with explicit specificity */
.attendance-table td.empty-state-cell,
.calendar-table td.empty-state-cell {
    height: 340px !important;
    min-height: 340px !important;
    padding: 60px 20px !important;
    vertical-align: middle !important;
    text-align: center !important;
    border-right: none !important;
    border-bottom: none !important;
    background-color: #ffffff !important;
}

.empty-state-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    text-align: center;
}

.empty-state-icon {
    font-size: 40px;
    color: #94a3b8;
    margin-bottom: 10px;
    display: inline-block;
    line-height: 1;
}

.empty-state-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
    line-height: 1.4;
}

.empty-state-desc {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.4;
}

/* Attendance Row Status Color Highlights */
.row-status-present td { background-color: #f0fdf4 !important; }
.row-status-absent td { background-color: #fef2f2 !important; }
.row-status-leave td { background-color: #f0f9ff !important; }
.row-status-halfday td { background-color: #fffbeb !important; }
.row-status-holiday td { background-color: #faf5ff !important; }

/* Status Pill Buttons */
.status-btn-group {
    display: inline-flex;
    gap: 2px;
    align-items: center;
    justify-content: center;
}

.btn-att-pill {
    padding: 1px 6px;
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 2px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    line-height: 1.4;
    transition: all 0.12s ease;
}
.btn-att-pill:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}
.btn-att-pill.btn-att-p.active { background: #166534 !important; border-color: #166534 !important; color: #ffffff !important; }
.btn-att-pill.btn-att-a.active { background: #dc2626 !important; border-color: #dc2626 !important; color: #ffffff !important; }
.btn-att-pill.btn-att-l.active { background: #0284c7 !important; border-color: #0284c7 !important; color: #ffffff !important; }
.btn-att-pill.btn-att-h.active { background: #d97706 !important; border-color: #d97706 !important; color: #ffffff !important; }
.btn-att-pill.btn-att-hd.active { background: #7e22ce !important; border-color: #7e22ce !important; color: #ffffff !important; }

.adm-badge {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 2px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    display: inline-block;
}

.student-name-cell {
    display: flex;
    align-items: center;
    white-space: nowrap;
}

/* Event banner */
.event-alert-bar {
    background: #eff6ff;
    border-bottom: 1px solid #bfdbfe;
    padding: 4px 10px;
    font-size: 11px;
    color: #1e40af;
    display: none;
    align-items: center;
    flex-shrink: 0;
}

/* Modal styling matching theme */
.modal-content {
    border: 1px solid #9aa7b1;
    border-radius: 3px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}
.modal-header-excel {
    background: var(--green-excel);
    color: #ffffff;
    padding: 8px 14px;
    border-top-left-radius: 2px;
    border-top-right-radius: 2px;
}
.modal-header-excel .close {
    color: #ffffff;
    opacity: 0.9;
}
</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-2">
        <div class="container-fluid">
            <div class="row attendance-layout-row">
                <!-- ========================================== -->
                <!-- LEFT SECTION: STUDENT ATTENDANCE MARKING   -->
                <!-- ========================================== -->
                <div class="col-12 col-xl-9 col-lg-8 pr-lg-1 mb-2 mb-lg-0 d-flex flex-column">
                    <div class="attendance-card w-100">
                        <!-- Header / Titlebar -->
                        <div class="attendance-titlebar">
                            <div class="attendance-title">
                                <i class="fa fa-calendar-check-o"></i>
                                <span>{{ __('student.Fill Students Attendance') }}</span>
                                <small>| Daily Marking Sheet</small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ url('studentsAttendanceView') }}" class="btn-header-action" title="View Attendance Records">
                                    <i class="fa fa-eye text-primary"></i> {{ __('common.View') }}
                                </a>
                                <a href="{{ url('studentsDashboard') }}" class="btn-header-action" title="Back to Dashboard">
                                    <i class="fa fa-arrow-left text-secondary"></i> {{ __('common.Back') }}
                                </a>
                            </div>
                        </div>

                        <!-- Dynamic Event Notification Alert -->
                        <div id="today_event_banner" class="event-alert-bar">
                            <i class="fa fa-bullhorn text-primary mr-2"></i>
                            <span id="today_event_text"></span>
                        </div>

                        @if(count($classType) > 0)
                        <!-- Filter Toolbar -->
                        <div class="attendance-filter-bar">
                            <form id="attendanceFilterForm" onsubmit="event.preventDefault(); SearchValue();">
                                <div class="row align-items-end no-gutters">
                                    <div class="col-md-2 col-6 px-1 mb-1 mb-md-0">
                                        <label><i class="fa fa-calendar text-success mr-1"></i> Date*</label>
                                        <input type="date" class="form-control font-weight-bold" id="date1" name="date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-2 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('Course') }}</label>
                                        <select class="form-control select2" id="course" name="course">
                                            <option value="">All Courses</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $c)
                                                    <option value="{{ $c->name ?? '' }}">{{ $c->name ?? '' }}</option>
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
                                                    <option value="{{ $b->name ?? '' }}">{{ $b->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6 px-1 mb-1 mb-md-0">
                                        <label>{{ __('Class / Semester') }}</label>
                                        <select class="form-control select2" id="class_type_id" name="class_type_id">
                                            @if(Session::get('role_id') != 2)
                                                <option value="">All Classes / Semesters</option>
                                            @endif
                                            @if(!empty($classType))
                                                @foreach($classType as $type)
                                                    <option value="{{ $type->id ?? '' }}">{{ $type->name ?? '' }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-8 px-1 mb-1 mb-md-0">
                                        <label>{{ __('common.Search By Keywords') }}</label>
                                        <input type="text" class="form-control" id="searchName" name="name" placeholder="Name, Mobile, Adm No...">
                                    </div>
                                    <div class="col-md-1 col-4 px-1 d-flex">
                                        <button type="button" class="btn btn-header-action btn-header-save w-100 justify-content-center" id="btnSearchStudent" style="height: 27px;" onclick="SearchValue()" title="Search Students">
                                            <i class="fa fa-search"></i>
                                        </button>
                                        <button type="button" class="btn btn-header-action ml-1" style="height: 27px;" onclick="resetFilters()" title="Reset Filters">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- KPI Status Summary & Quick Actions Ribbon -->
                        <div class="attendance-ribbon">
                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <div class="kpi-badge kpi-badge-total">
                                    <span>Total:</span>
                                    <span class="count-val" id="kpi_total">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-present">
                                    <i class="fa fa-check text-success"></i> Present:
                                    <span class="count-val" id="kpi_present">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-absent">
                                    <i class="fa fa-times text-danger"></i> Absent:
                                    <span class="count-val" id="kpi_absent">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-leave">
                                    <i class="fa fa-clock-o text-info"></i> Leave:
                                    <span class="count-val" id="kpi_leave">0</span>
                                </div>
                                <div class="kpi-badge kpi-badge-unmarked">
                                    <i class="fa fa-question text-warning"></i> Unmarked:
                                    <span class="count-val" id="kpi_unmarked">0</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap gap-1">
                                <button type="button" class="btn-quick-action btn-quick-present" id="btn-mark-all-present">
                                    <i class="fa fa-check"></i> Mark All Present
                                </button>
                                <button type="button" class="btn-quick-action btn-quick-absent" id="btn-mark-all-absent">
                                    <i class="fa fa-times"></i> Mark All Absent
                                </button>

                                @if(Session::get('role_id') == 1)
                                <button type="button" class="btn-quick-action btn-quick-reset" data-toggle="modal" data-target="#Modal_id">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                                @endif

                                <button type="button" class="btn-quick-action btn-quick-save" id="btn-submit-attendance">
                                    <i class="fa fa-save"></i> Save Attendance
                                </button>
                            </div>
                        </div>

                        <!-- Student Attendance Sheet Table -->
                        <form id="attendanceForm" action="{{ url('studentsAttendanceAdd') }}" method="post">
                            @csrf
                            <input type="hidden" name="date" id="form_submit_date" value="{{ date('Y-m-d') }}">

                            <div class="attendance-scroll-container">
                                <table class="table attendance-table" id="student_list_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 32px;" class="text-center">
                                                <input type="checkbox" id="masterCheckbox" checked>
                                            </th>
                                            <th style="width: 36px;" class="text-center">#</th>
                                            <th style="width: 100px;" class="text-center">Adm No.</th>
                                            <th>Student Details</th>
                                            <th>Class / Semester</th>
                                            <th>Father Name</th>
                                            <th style="width: 90px;" class="text-center">DOB</th>
                                            <th style="width: 160px;" class="text-center">Attendance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="student_list_show">
                                        <tr>
                                            <td colspan="8" class="empty-state-cell">
                                                <div class="empty-state-wrapper">
                                                    <i class="fa fa-users empty-state-icon"></i>
                                                    <div class="empty-state-title">Ready to load attendance sheet</div>
                                                    <div class="empty-state-desc">Select filters and click search button to view students.</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        @else
                        <div class="p-4 text-center text-danger">
                            <i class="fa fa-lock fa-2x mb-2 text-muted"></i>
                            <div>You are not authorized for marking student attendance. Please contact your administrator.</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- RIGHT SECTION: ACADEMIC CALENDAR (ALWAYS)  -->
                <!-- ========================================== -->
                <div class="col-12 col-xl-3 col-lg-4 pl-lg-1 d-flex flex-column">
                    <div class="attendance-card w-100">
                        <!-- Calendar Titlebar -->
                        <div class="attendance-titlebar">
                            <div class="attendance-title">
                                <i class="fa fa-calendar"></i>
                                <span>Academic Calendar</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="{{ url('add_weekend') }}" target="_blank" class="btn-header-action btn-header-save" title="Add / Manage Calendar Events">
                                    <i class="fa fa-plus"></i> Manage
                                </a>
                            </div>
                        </div>

                        <!-- Calendar Excel Table Container -->
                        <div class="calendar-scroll-container">
                            <table class="table calendar-table">
                                <thead>
                                    <tr>
                                        <th style="width: 75px;" class="text-center">Date</th>
                                        <th style="width: 55px;" class="text-center">Day</th>
                                        <th>Event / Schedule</th>
                                    </tr>
                                </thead>
                                <tbody id="calendarTableBody">
                                    <tr>
                                        <td colspan="3" class="empty-state-cell">
                                            <div class="empty-state-wrapper">
                                                <i class="fa fa-spinner fa-spin empty-state-icon"></i>
                                                <div class="empty-state-title">Loading events...</div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Centered Reset Confirmation Modal -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-excel bg-danger text-white">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fa fa-exclamation-triangle mr-1"></i> Reset Attendance Confirmation
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('studentsAttendancdDelete') }}" method="post" id="resetAttendanceForm">
                @csrf
                <div class="modal-body p-3">
                    <input type="hidden" id="modalDate" name="date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" id="class_type_id_Reset" name="class_type_id_Reset">
                    <input type="hidden" id="admission_id_Reset" name="admission_id_Reset">

                    <p class="font-weight-bold text-danger mb-1" style="font-size: 13px;">
                        Are you sure you want to reset attendance for the selected date?
                    </p>
                    <div class="text-muted small mb-2">
                        Target Date: <strong class="text-dark" id="modal_date_display">{{ date('d-m-Y') }}</strong>
                    </div>

                    <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto; background: #f8fafc; font-size: 11px;">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr class="border-bottom font-weight-bold text-muted">
                                    <th style="width: 90px;">Adm No.</th>
                                    <th>Student Name</th>
                                </tr>
                            </thead>
                            <tbody id="selected_students">
                                <tr>
                                    <td colspan="2" class="text-muted text-center py-2">All students of target date will be reset</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3 justify-content-between">
                    <button type="button" class="btn btn-header-action" data-dismiss="modal">{{ __('common.No') }}</button>
                    <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3">
                        <i class="fa fa-trash mr-1"></i> Yes, Reset Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Centered Loading Overlay -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content bg-transparent border-0 shadow-none text-center">
            <div class="modal-body">
                <div class="spinner-border text-light" style="width: 2.5rem; height: 2.5rem;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="mt-2 text-white font-weight-bold small loading_text">Processing...</div>
            </div>
        </div>
    </div>
</div>

<script>
    var academicCalender = [];

    function showLoading(text) {
        $('.loading_text').text(text || 'Processing...');
        $('#loadingModal').modal('show');
    }

    function hideLoading() {
        $('#loadingModal').modal('hide');
    }

    function updateUrlParams() {
        var params = new URLSearchParams();
        var custom_date = $('#date1').val();
        var course = $('#course').val();
        var batch = $('#batch').val();
        var class_type_id = $('#class_type_id').val();
        var name = $('#searchName').val();

        if (custom_date) params.set('date', custom_date);
        if (course) params.set('course', course);
        if (batch) params.set('batch', batch);
        if (class_type_id) params.set('class_type_id', class_type_id);
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

    function resetFilters() {
        $('#course').val('').trigger('change.select2');
        $('#batch').val('').trigger('change.select2');
        $('#searchName').val('');
        $('#date1').val('{{ date("Y-m-d") }}');
        $('#form_submit_date').val('{{ date("Y-m-d") }}');
        $('#modalDate').val('{{ date("Y-m-d") }}');
        $('#modal_date_display').text('{{ date("d-m-Y") }}');
        checkAcademicForDate('{{ date("Y-m-d") }}');

        loadFilteredClasses('', function() {
            window.history.pushState({}, '', window.location.pathname);
            $('.student_list_show').html(
                '<tr>' +
                    '<td colspan="8" class="empty-state-cell">' +
                        '<div class="empty-state-wrapper">' +
                            '<i class="fa fa-users empty-state-icon"></i>' +
                            '<div class="empty-state-title">Ready to load attendance sheet</div>' +
                            '<div class="empty-state-desc">Select filters and click search button to view students.</div>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            );
            updateSummaryKPIs();
            syncSelectedStudents();
        });
    }

    function updateSummaryKPIs() {
        var total = $('.attendance-row').length;
        var present = 0;
        var absent = 0;
        var leave = 0;
        var unmarked = 0;

        $('.attendance_status_input').each(function() {
            var val = $(this).val();
            if (val == '1') present++;
            else if (val == '2') absent++;
            else if (val == '3' || val == '4' || val == '5') leave++;
            else unmarked++;
        });

        $('#kpi_total').text(total);
        $('#kpi_present').text(present);
        $('#kpi_absent').text(absent);
        $('#kpi_leave').text(leave);
        $('#kpi_unmarked').text(unmarked);
    }

    function syncSelectedStudents() {
        var checkedBoxes = $('.checkbox_select:checked');
        var checkedValues = [];
        var rowsHtml = '';

        checkedBoxes.each(function() {
            var val = $(this).val();
            var adm = $(this).data('admission_no') || '-';
            var name = $(this).data('student_name') || '-';
            checkedValues.push(val);
            rowsHtml += '<tr><td>' + adm + '</td><td>' + name + '</td></tr>';
        });

        if (rowsHtml === '') {
            rowsHtml = '<tr><td colspan="2" class="text-muted text-center py-2">All students of target date will be reset</td></tr>';
        }

        $('#selected_students').html(rowsHtml);
        $('#admission_id_Reset').val(checkedValues.join(','));
    }

    function SearchValue(updateUrl) {
        if (typeof updateUrl === 'undefined') {
            updateUrl = true;
        }

        var name = $('#searchName').val() || '';
        var course = $('#course').val() || '';
        var class_type_id = $('#class_type_id').val() || '';
        var batch = $('#batch').val() || '';
        var custom_date = $('#date1').val() || '{{ date("Y-m-d") }}';
        var admissionNo = '';

        if (updateUrl) {
            updateUrlParams();
        }

        $('#class_type_id_Reset').val(class_type_id);
        $('#form_submit_date').val(custom_date);
        $('#modalDate').val(custom_date);

        // Render inline loading animation inside tbody area
        $('.student_list_show').html(
            '<tr>' +
                '<td colspan="8" class="empty-state-cell">' +
                    '<div class="empty-state-wrapper">' +
                        '<div class="spinner-border text-success mb-2" style="width: 2.5rem; height: 2.5rem;" role="status">' +
                            '<span class="sr-only">Loading...</span>' +
                        '</div>' +
                        '<div class="font-weight-bold text-dark" style="font-size: 13.5px;">Loading Student Attendance Sheet...</div>' +
                        '<div class="text-muted small">Please wait while student records are being fetched.</div>' +
                    '</div>' +
                '</td>' +
            '</tr>'
        );

        var searchBtn = $('#btnSearchStudent');
        if (searchBtn.length) {
            searchBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: "{{ url('SearchValueAtten') }}",
            data: {
                custom_date: custom_date,
                course: course,
                class_type_id: class_type_id,
                batch: batch,
                name: name,
                admissionNo: admissionNo
            },
            success: function(response) {
                $('.student_list_show').html(response);
                if (searchBtn.length) {
                    searchBtn.prop('disabled', false).html('<i class="fa fa-search"></i>');
                }
                $('#masterCheckbox').prop('checked', true);
                updateSummaryKPIs();
                syncSelectedStudents();
                checkAcademicForDate(custom_date);
            },
            error: function() {
                if (searchBtn.length) {
                    searchBtn.prop('disabled', false).html('<i class="fa fa-search"></i>');
                }
                toastr.error('Error fetching student attendance list.');
            }
        });
    }

    function fetchAcademicCalendar() {
        $.ajax({
            url: "{{ url('academic_calendar') }}",
            method: 'GET',
            success: function(res) {
                academicCalender = (res && res.data) ? res.data : [];
                renderCalendarTable();
                checkAcademicForDate($('#date1').val());
            }
        });
    }

    function renderCalendarTable() {
        var tbody = $('#calendarTableBody');
        tbody.empty();

        if (!academicCalender || academicCalender.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center text-muted py-3">No events found in academic calendar</td></tr>');
            return;
        }

        var selectedDate = $('#date1').val();

        academicCalender.forEach(function(item) {
            var isCurrent = (item.date === selectedDate);
            var trClass = isCurrent ? 'font-weight-bold bg-warning-light' : '';
            var trStyle = isCurrent ? 'background-color: #fef3c7 !important;' : '';

            var formattedDate = item.date;
            if (item.date) {
                var dParts = item.date.split('-');
                if (dParts.length === 3) {
                    formattedDate = dParts[2] + '-' + dParts[1];
                }
            }

            var tr = $('<tr class="' + trClass + '" style="' + trStyle + '"></tr>');
            tr.append('<td class="text-center">' + formattedDate + '</td>');
            tr.append('<td class="text-center text-muted">' + (item.day ? item.day.substring(0, 3) : '-') + '</td>');
            tr.append('<td>' + (item.event_schedule || '-') + (isCurrent ? ' <span class="badge badge-warning text-dark ml-1">Today</span>' : '') + '</td>');
            tbody.append(tr);
        });
    }

    function checkAcademicForDate(selectedDate) {
        if (!academicCalender || academicCalender.length === 0) return;
        var foundEvent = null;

        for (var i = 0; i < academicCalender.length; i++) {
            if (academicCalender[i].date === selectedDate) {
                foundEvent = academicCalender[i];
                break;
            }
        }

        if (foundEvent) {
            $('#today_event_text').html('<strong>' + foundEvent.event_schedule + '</strong> (' + (foundEvent.day || '') + ')');
            $('#today_event_banner').slideDown();
        } else {
            $('#today_event_banner').slideUp();
        }

        renderCalendarTable();
    }

    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        fetchAcademicCalendar();

        // Check URL query parameters on initial page load
        var urlParams = new URLSearchParams(window.location.search);
        var paramDate = urlParams.get('date');
        var paramCourse = urlParams.get('course');
        var paramBatch = urlParams.get('batch');
        var paramClass = urlParams.get('class_type_id');
        var paramName = urlParams.get('name');

        var hasFilterInUrl = false;

        if (paramDate) {
            $('#date1').val(paramDate);
            $('#form_submit_date').val(paramDate);
            $('#modalDate').val(paramDate);
            var pParts = paramDate.split('-');
            if (pParts.length === 3) {
                $('#modal_date_display').text(pParts[2] + '-' + pParts[1] + '-' + pParts[0]);
            }
            hasFilterInUrl = true;
        }

        if (paramCourse) {
            $('#course').val(paramCourse).trigger('change.select2');
            hasFilterInUrl = true;
        }

        if (paramBatch) {
            $('#batch').val(paramBatch).trigger('change.select2');
            hasFilterInUrl = true;
        }

        if (paramName) {
            $('#searchName').val(paramName);
            hasFilterInUrl = true;
        }

        if (paramClass) {
            hasFilterInUrl = true;
        }

        // Initialize cascading classes and auto-search if filters exist
        loadFilteredClasses(paramClass, function() {
            if (hasFilterInUrl) {
                SearchValue(false);
            }
        });

        $('#date1').on('change', function() {
            var d = $(this).val();
            $('#form_submit_date').val(d);
            $('#modalDate').val(d);
            if (d) {
                var parts = d.split('-');
                if (parts.length === 3) {
                    $('#modal_date_display').text(parts[2] + '-' + parts[1] + '-' + parts[0]);
                }
            }
            checkAcademicForDate(d);
        });

        $(document).on('change', '#course', function() {
            loadFilteredClasses();
        });

        $(document).on('change', '#batch', function() {
            if ($('#course').val()) {
                loadFilteredClasses();
            }
        });

        // Listen for browser Back/Forward navigation
        window.addEventListener('popstate', function(event) {
            var currentParams = new URLSearchParams(window.location.search);
            var curDate = currentParams.get('date') || '{{ date("Y-m-d") }}';
            var curCourse = currentParams.get('course') || '';
            var curBatch = currentParams.get('batch') || '';
            var curClass = currentParams.get('class_type_id') || '';
            var curName = currentParams.get('name') || '';

            $('#date1').val(curDate);
            $('#form_submit_date').val(curDate);
            $('#modalDate').val(curDate);
            var parts = curDate.split('-');
            if (parts.length === 3) {
                $('#modal_date_display').text(parts[2] + '-' + parts[1] + '-' + parts[0]);
            }
            checkAcademicForDate(curDate);

            $('#course').val(curCourse).trigger('change.select2');
            $('#batch').val(curBatch).trigger('change.select2');
            $('#searchName').val(curName);

            loadFilteredClasses(curClass, function() {
                if (curCourse || curBatch || curClass || curName || currentParams.has('date')) {
                    SearchValue(false);
                } else {
                    $('.student_list_show').html(
                        '<tr>' +
                            '<td colspan="8" class="empty-state-cell">' +
                                '<div class="empty-state-wrapper">' +
                                    '<i class="fa fa-users empty-state-icon"></i>' +
                                    '<div class="empty-state-title">Ready to load attendance sheet</div>' +
                                    '<div class="empty-state-desc">Select filters and click search button to view students.</div>' +
                                '</div>' +
                            '</td>' +
                        '</tr>'
                    );
                    updateSummaryKPIs();
                    syncSelectedStudents();
                }
            });
        });

        // Toggle Status Pill
        $(document).on('click', '.btn-att-pill', function(e) {
            e.preventDefault();
            var btn = $(this);
            var studentId = btn.data('id');
            var statusId = btn.data('status-id');
            var input = $('#attendance_status_' + studentId);
            var row = btn.closest('tr');

            input.val(statusId);

            btn.siblings().removeClass('active');
            btn.addClass('active');

            row.removeClass('row-status-present row-status-absent row-status-leave row-status-halfday row-status-holiday');
            if (statusId == '1') row.addClass('row-status-present');
            else if (statusId == '2') row.addClass('row-status-absent');
            else if (statusId == '3') row.addClass('row-status-leave');
            else if (statusId == '4') row.addClass('row-status-halfday');
            else if (statusId == '5') row.addClass('row-status-holiday');

            updateSummaryKPIs();
        });

        // Master checkbox
        $('#masterCheckbox').on('change', function() {
            var checked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', checked);
            syncSelectedStudents();
        });

        // Individual row checkbox
        $(document).on('change', '.row-checkbox', function() {
            var total = $('.row-checkbox').length;
            var checked = $('.row-checkbox:checked').length;
            $('#masterCheckbox').prop('checked', total === checked);
            syncSelectedStudents();
        });

        // Mark All Present
        $('#btn-mark-all-present').on('click', function() {
            $('.attendance-row').each(function() {
                var row = $(this);
                if (row.find('.row-checkbox').prop('checked')) {
                    row.find('.attendance_status_input').val('1');
                    row.find('.btn-att-pill').removeClass('active');
                    row.find('.btn-att-p').addClass('active');
                    row.removeClass('row-status-present row-status-absent row-status-leave row-status-halfday row-status-holiday').addClass('row-status-present');
                }
            });
            updateSummaryKPIs();
            toastr.success('Marked selected students as Present');
        });

        // Mark All Absent
        $('#btn-mark-all-absent').on('click', function() {
            $('.attendance-row').each(function() {
                var row = $(this);
                if (row.find('.row-checkbox').prop('checked')) {
                    row.find('.attendance_status_input').val('2');
                    row.find('.btn-att-pill').removeClass('active');
                    row.find('.btn-att-a').addClass('active');
                    row.removeClass('row-status-present row-status-absent row-status-leave row-status-halfday row-status-holiday').addClass('row-status-absent');
                }
            });
            updateSummaryKPIs();
            toastr.info('Marked selected students as Absent');
        });

        // Save Attendance AJAX
        $('#btn-submit-attendance').on('click', function() {
            var date = $('#date1').val();
            var attendanceRecords = [];

            $('.row-checkbox:checked').each(function() {
                var studentId = $(this).val();
                var statusInput = $('#attendance_status_' + studentId);
                var statusVal = statusInput.val();

                if (!statusVal || statusVal === '') {
                    statusVal = '1';
                    statusInput.val('1');
                }

                attendanceRecords.push({
                    'time': $('#time_' + studentId).val() || '{{ date("H:i:s") }}',
                    'email': statusInput.attr('data-email') || '',
                    'name': statusInput.attr('data-name') || '',
                    'mobile': statusInput.attr('data-mobile') || '',
                    'admission_id': studentId,
                    'class_type_id': statusInput.attr('data-class_type_id') || '',
                    'attendance_status': statusVal
                });
            });

            if (attendanceRecords.length === 0) {
                toastr.warning('Please select at least one student to submit attendance.');
                return;
            }

            var submitBtn = $('#btn-submit-attendance');
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            showLoading('Saving student attendance records...');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ url('studentsAttendanceAdd') }}",
                type: 'POST',
                data: {
                    admission_ids: JSON.stringify(attendanceRecords),
                    date: date
                },
                success: function(res) {
                    hideLoading();
                    submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
                    toastr.success(res.message || 'Attendance saved successfully!');
                    updateSummaryKPIs();
                },
                error: function() {
                    hideLoading();
                    submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Attendance');
                    toastr.error('Failed to save attendance. Please try again.');
                }
            });
        });
    });
</script>
@endsection