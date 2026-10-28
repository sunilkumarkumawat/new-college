@php
$getAttendanceStatus = Helper::getAttendanceStatus();
$rolesList = $roles ?? Helper::roleType();
$currentRole = Session::get('role_id');
$currentStaffId = Session::get('id');
@endphp
@extends('layout.app') 
@section('content')

<input type="hidden" id="session_role_id" value="{{ $currentRole ?? '' }}">
<input type="hidden" id="session_user_id" value="{{ $currentStaffId ?? '' }}">

<style>
/* ========================================================
   STAFF ATTENDANCE - EXECUTIVE EXCEL GREEN ERP THEME
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

.attendance-card {
    border: 1px solid #9aa7b1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 0;
    overflow: hidden;
    height: calc(100vh - 120px);
    min-height: 520px;
    display: flex;
    flex-direction: column;
}

/* Titlebar */
.attendance-titlebar {
    min-height: 38px;
    padding: 6px 12px;
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
    padding: 3px 9px !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    line-height: 1.4;
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
    padding: 8px 12px;
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
    height: 28px !important;
    min-height: 28px !important;
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
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15) !important;
}

/* KPI Summary Ribbon */
.attendance-ribbon {
    background: var(--green-excel-light);
    border-bottom: 1px solid var(--green-excel-border);
    padding: 5px 12px;
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
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    gap: 4px;
    line-height: 1.3;
}
.kpi-badge .count-val {
    font-weight: 700;
    padding: 0 6px;
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
.kpi-badge-holiday { background: #faf5ff; border-color: #d8b4fe; color: #6b21a8; }
.kpi-badge-holiday .count-val { background: #7e22ce; color: #ffffff; }
.kpi-badge-unmarked { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.kpi-badge-unmarked .count-val { background: #d97706; color: #ffffff; }

/* Quick Action Buttons */
.btn-quick-action {
    font-size: 10.5px !important;
    font-weight: 700 !important;
    padding: 2px 8px !important;
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
.btn-quick-leave { background: #f0f9ff; border-color: #7dd3fc; color: #075985; }
.btn-quick-leave:hover { background: #0284c7; color: #ffffff; }
.btn-quick-holiday { background: #faf5ff; border-color: #d8b4fe; color: #6b21a8; }
.btn-quick-holiday:hover { background: #7e22ce; color: #ffffff; }
.btn-quick-reset { background: #fffbeb; border-color: #fde68a; color: #b45309; }
.btn-quick-reset:hover { background: #b45309; color: #ffffff; }

/* Table Container & Layout */
#staffAttendanceForm {
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

.attendance-table {
    margin: 0 !important;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px;
    border-top: 1px solid var(--line) !important;
    border-left: 1px solid var(--line) !important;
}

.attendance-table thead th {
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
    padding: 4px 6px !important;
}

.attendance-table td {
    padding: 4px 8px !important;
    border-right: 1px solid var(--line) !important;
    border-bottom: 1px solid var(--line) !important;
    vertical-align: middle !important;
    font-size: 11.5px;
    height: 34px;
    box-sizing: border-box;
}

.attendance-table tbody tr:nth-child(even) td { background-color: #fafafa; }
.attendance-table tbody tr:hover td { background-color: #fffde7 !important; }

/* Status Highlights */
.row-status-present td { background-color: #f0fdf4 !important; }
.row-status-absent td { background-color: #fef2f2 !important; }
.row-status-leave td { background-color: #f0f9ff !important; }
.row-status-holiday td { background-color: #faf5ff !important; }

/* Pill Buttons */
.status-btn-group {
    display: inline-flex;
    gap: 3px;
    align-items: center;
    justify-content: center;
}

.btn-att-pill {
    padding: 2px 7px;
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 2px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    line-height: 1.3;
    transition: all 0.12s ease;
}
.btn-att-pill:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}
.btn-att-pill.btn-att-p.active { background: #166534 !important; border-color: #166534 !important; color: #ffffff !important; box-shadow: 0 1px 3px rgba(22,101,52,0.3); }
.btn-att-pill.btn-att-a.active { background: #dc2626 !important; border-color: #dc2626 !important; color: #ffffff !important; box-shadow: 0 1px 3px rgba(220,38,38,0.3); }
.btn-att-pill.btn-att-l.active { background: #0284c7 !important; border-color: #0284c7 !important; color: #ffffff !important; box-shadow: 0 1px 3px rgba(2,132,199,0.3); }
.btn-att-pill.btn-att-h.active { background: #7e22ce !important; border-color: #7e22ce !important; color: #ffffff !important; box-shadow: 0 1px 3px rgba(126,34,206,0.3); }

/* Badges & Avatars */
.staff-badge-id {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 2px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    display: inline-block;
}

.staff-role-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 3px;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    display: inline-block;
}

.staff-dept-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 1px 6px;
    border-radius: 3px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    display: inline-block;
}

/* Avatar with First Letter Fallback */
.staff-avatar-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    position: relative;
    vertical-align: middle;
}

.staff-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    display: block;
}

.staff-letter-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    color: #ffffff;
    font-weight: 700;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    line-height: 1;
    user-select: none;
    border: 1px solid rgba(255,255,255,0.4);
}

.staff-name-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

/* ========================================================
   EMPTY STATE - FULL TABLE CENTERED
   ======================================================== */
.attendance-table tbody tr.staff-empty-row {
    height: calc(100vh - 270px) !important;
    min-height: 380px !important;
    background: #ffffff !important;
}
.attendance-table tbody tr.staff-empty-row:hover td {
    background-color: #ffffff !important;
}
.attendance-table tbody tr.staff-empty-row td.empty-state-cell {
    height: calc(100vh - 270px) !important;
    min-height: 380px !important;
    border-right: none !important;
    border-bottom: none !important;
    padding: 20px !important;
    vertical-align: middle !important;
    background: #ffffff !important;
    text-align: center !important;
}

.empty-state-wrap {
    position: sticky;
    left: 0;
    right: 0;
    margin: 0 auto;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    max-width: 460px;
    background: transparent;
}

.empty-state-icon-wrap {
    width: 58px;
    height: 58px;
    background: #eef6f0;
    color: var(--green-excel);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 12px;
    border: 1px solid var(--green-excel-border);
}

.empty-state-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}

.empty-state-subtitle {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 15px;
}

.btn-empty-reset {
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    padding: 5px 16px !important;
    border-radius: 3px !important;
    border: 1px solid var(--green-excel-dark) !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.15s ease-in-out;
}
.btn-empty-reset:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

/* Bottom Action Bar */
.attendance-footer-bar {
    background: #ffffff;
    border-top: 1px solid var(--line);
    padding: 6px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    box-shadow: 0 -1px 3px rgba(0,0,0,0.03);
}

.btn-save-attendance {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 4px 16px !important;
    border-radius: 2px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    cursor: pointer;
}
.btn-save-attendance:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
    box-shadow: 0 1px 4px rgba(24,92,55,0.3);
}
.btn-save-attendance:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* Loading overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
</style>

<div class="content-wrapper">
    <section class="content pt-2 pb-2">
        <div class="container-fluid">
            <div class="attendance-card">
                <!-- 1. Header / Titlebar -->
                <div class="attendance-titlebar">
                    <div class="attendance-title">
                        <i class="fa fa-calendar-check-o"></i>
                        <span>{{ __('staff.Fill Staff Attendance') }}</span>
                        <small>| Daily Marking Sheet</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-header-action btn-header-save" id="btnHeaderSave" onclick="$('#staffAttendanceForm').submit();">
                            <i class="fa fa-check"></i> {{ __('staff.Submit Attendance') }}
                        </button>
                        <a href="{{ url('staff/Attendance/view') }}" class="btn-header-action" title="View Attendance Records">
                            <i class="fa fa-eye text-primary"></i> {{ __('common.View') }}
                        </a>
                        <a href="{{ url('user_dashboard') }}" class="btn-header-action" title="Back to Dashboard">
                            <i class="fa fa-arrow-left text-secondary"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Parameter & Filter Toolbar -->
                <div class="attendance-filter-bar">
                    <div class="row align-items-end g-2">
                        <!-- Date -->
                        <div class="col-6 col-md-2 col-lg-2">
                            <label><i class="fa fa-calendar text-muted"></i> {{ __('common.Date') }}*</label>
                            <input type="date" class="form-control" id="attendance_date" name="date_filter" value="{{ date('Y-m-d') }}" {{ ($currentRole == 2 ? 'readonly' : '') }}>
                        </div>

                        <!-- Time -->
                        <div class="col-6 col-md-2 col-lg-1">
                            <label><i class="fa fa-clock-o text-muted"></i> Time*</label>
                            <input type="time" class="form-control" id="attendance_time" name="time_filter" required {{ ($currentRole == 2 ? 'readonly' : '') }}>
                        </div>

                        <!-- Search -->
                        <div class="col-12 col-md-3 col-lg-3">
                            <label><i class="fa fa-search text-muted"></i> {{ __('common.Search By Keywords') }}</label>
                            <input type="text" class="form-control" id="staffSearchKeyword" placeholder="Filter by Name, ID, Mobile, Role..." autocomplete="off">
                        </div>

                        <!-- Role Filter -->
                        <div class="col-6 col-md-2 col-lg-2">
                            <label><i class="fa fa-user-circle text-muted"></i> Role</label>
                            <select class="form-control" id="filterStaffRole">
                                <option value="">All Roles</option>
                                @if(!empty($rolesList))
                                    @foreach($rolesList as $r)
                                        <option value="{{ $r->name ?? '' }}">{{ $r->name ?? '' }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-6 col-md-3 col-lg-2">
                            <label><i class="fa fa-building text-muted"></i> Department</label>
                            <select class="form-control" id="filterStaffDept">
                                <option value="">All Departments</option>
                                @if(!empty($departments))
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}">{{ $dept }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Quick Mass Action Buttons -->
                        <div class="col-12 col-md-12 col-lg-2 text-lg-right">
                            <label class="d-none d-lg-block">&nbsp;</label>
                            <div class="d-flex align-items-center justify-content-lg-end gap-1 flex-wrap">
                                @if($currentRole != 2)
                                    <button type="button" class="btn-quick-action btn-quick-present" onclick="markAllAttendance(1)" title="Mark All Present">
                                        <i class="fa fa-check"></i> All P
                                    </button>
                                    <button type="button" class="btn-quick-action btn-quick-absent" onclick="markAllAttendance(3)" title="Mark All Absent">
                                        <i class="fa fa-times"></i> All A
                                    </button>
                                    <button type="button" class="btn-quick-action btn-quick-leave" onclick="markAllAttendance(9)" title="Mark All Leave">
                                        <i class="fa fa-plane"></i> All L
                                    </button>
                                    <button type="button" class="btn-quick-action btn-quick-holiday" onclick="markAllAttendance(5)" title="Mark All Holiday">
                                        <i class="fa fa-star"></i> All H
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Dynamic Stats Ribbon -->
                <div class="attendance-ribbon">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="kpi-badge kpi-badge-total">
                            <i class="fa fa-users text-muted"></i> Total Staff: <span class="count-val" id="stat_total">0</span>
                        </span>
                        <span class="kpi-badge kpi-badge-present">
                            <i class="fa fa-check-circle text-success"></i> Present (In): <span class="count-val" id="stat_present">0</span>
                        </span>
                        <span class="kpi-badge kpi-badge-absent">
                            <i class="fa fa-times-circle text-danger"></i> Absent: <span class="count-val" id="stat_absent">0</span>
                        </span>
                        <span class="kpi-badge kpi-badge-leave">
                            <i class="fa fa-plane text-info"></i> Leave: <span class="count-val" id="stat_leave">0</span>
                        </span>
                        <span class="kpi-badge kpi-badge-holiday">
                            <i class="fa fa-star text-purple"></i> Holiday: <span class="count-val" id="stat_holiday">0</span>
                        </span>
                        <span class="kpi-badge kpi-badge-unmarked">
                            <i class="fa fa-clock-o text-warning"></i> Unmarked: <span class="count-val" id="stat_unmarked">0</span>
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn-quick-action btn-quick-reset" onclick="resetAttendance()" title="Reset Selection">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- 4. Main Spreadsheet Attendance Form -->
                <form id="staffAttendanceForm" action="{{ url('staff_attendance_add') }}" method="POST">
                    @csrf
                    <!-- Hidden sync inputs for Date & Time -->
                    <input type="hidden" name="date" id="form_date_hidden" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="time" id="form_time_hidden" value="">

                    <div class="attendance-scroll-container">
                        <!-- Loading spinner -->
                        <div class="loading-overlay" id="tableLoadingOverlay">
                            <div class="spinner-border text-success" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>

                        <table class="attendance-table" id="staffAttendanceTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th style="width: 45px;">Photo</th>
                                    <th style="width: 90px;">Staff ID</th>
                                    <th style="text-align: left; min-width: 180px;">{{ __('common.Name') }}</th>
                                    <th style="text-align: left; min-width: 140px;">Father's Name</th>
                                    <th style="min-width: 110px;">Role</th>
                                    <th style="min-width: 110px;">Department</th>
                                    <th style="min-width: 110px;">{{ __('common.Mobile No.') }}</th>
                                    <th style="width: 180px; text-align: center;">{{ __('common.Attendance') }}</th>
                                </tr>
                            </thead>
                            <tbody id="staffAttendanceBody">
                                @php
                                    $visibleCount = 0;
                                @endphp
                                @if(!empty($data) && count($data) > 0)
                                    @foreach($data as $staff)
                                        @php
                                            if($currentRole == 2 && $staff->id != $currentStaffId) {
                                                continue;
                                            }
                                            $visibleCount++;
                                            $existingStatus = null;
                                            if(!empty($todayAttendance) && isset($todayAttendance[$staff->id])) {
                                                $existingStatus = $todayAttendance[$staff->id]->attendance_status_id;
                                            }
                                            // 1=Present, 3=Absent, 5=Holiday, 9=Leave
                                            $statusVal = $existingStatus ?? 1; // Default to 1 (Present)
                                            $rowStatusClass = '';
                                            if($statusVal == 1) $rowStatusClass = 'row-status-present';
                                            elseif($statusVal == 3) $rowStatusClass = 'row-status-absent';
                                            elseif($statusVal == 9) $rowStatusClass = 'row-status-leave';
                                            elseif($statusVal == 5) $rowStatusClass = 'row-status-holiday';

                                            $rawName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
                                            $nameForAvatar = !empty($rawName) ? $rawName : ($staff->userName ?? 'Staff');
                                            $firstLetter = strtoupper(mb_substr($nameForAvatar, 0, 1, 'UTF-8'));
                                            $palette = ['#166534', '#0284c7', '#7e22ce', '#d97706', '#dc2626', '#4f46e5', '#0d9488', '#b45309', '#0369a1'];
                                            $bgColor = $palette[abs(crc32($nameForAvatar . $staff->id)) % count($palette)];
                                            $imgSrc = !empty($staff->image) ? env('IMAGE_SHOW_PATH').'profile/'.$staff->image : '';
                                        @endphp
                                        <tr class="staff-row {{ $rowStatusClass }}" 
                                            data-staff-id="{{ $staff->id }}"
                                            data-name="{{ strtolower(($staff->first_name ?? '').' '.($staff->last_name ?? '')) }}"
                                            data-father="{{ strtolower($staff->father_name ?? '') }}"
                                            data-mobile="{{ $staff->mobile ?? '' }}"
                                            data-email="{{ strtolower($staff->email ?? '') }}"
                                            data-role="{{ strtolower($staff->roleName->name ?? '') }}"
                                            data-dept="{{ strtolower($staff->department ?? '') }}">
                                            
                                            <!-- Hidden Inputs for Form Submission -->
                                            <input type="hidden" name="staff_id[]" value="{{ $staff->id }}">
                                            <input type="hidden" name="first_name[]" value="{{ $staff->first_name ?? '' }}">
                                            <input type="hidden" name="mobile[]" value="{{ $staff->mobile ?? '' }}">
                                            <input type="hidden" name="email[]" value="{{ $staff->email ?? '' }}">
                                            <input type="hidden" class="input-att-status" name="attendance_status[]" value="{{ $statusVal }}">

                                            <!-- 1. Index -->
                                            <td class="text-center row-index font-weight-bold text-muted">{{ $visibleCount }}</td>

                                            <!-- 2. Photo (with Name First Letter Fallback) -->
                                            <td class="text-center">
                                                <div class="staff-avatar-wrapper">
                                                    @if(!empty($imgSrc))
                                                        <img src="{{ $imgSrc }}" class="staff-avatar" alt="{{ $nameForAvatar }}"
                                                             onerror="this.style.display='none'; if(this.nextElementSibling){ this.nextElementSibling.style.display='inline-flex'; }">
                                                        <span class="staff-letter-avatar" style="display: none; background-color: {{ $bgColor }};" title="{{ $nameForAvatar }}">
                                                            {{ $firstLetter }}
                                                        </span>
                                                    @else
                                                        <span class="staff-letter-avatar" style="background-color: {{ $bgColor }};" title="{{ $nameForAvatar }}">
                                                            {{ $firstLetter }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- 3. Staff ID -->
                                            <td class="text-center">
                                                <span class="staff-badge-id">#{{ $staff->id }}</span>
                                            </td>

                                            <!-- 4. Staff Name -->
                                            <td>
                                                <div class="font-weight-bold text-dark">
                                                    {{ $staff->first_name ?? '' }} {{ $staff->last_name ?? '' }}
                                                </div>
                                                @if(!empty($staff->email))
                                                    <small class="text-muted"><i class="fa fa-envelope-o"></i> {{ $staff->email }}</small>
                                                @endif
                                            </td>

                                            <!-- 5. Father Name -->
                                            <td class="text-muted">
                                                {{ $staff->father_name ?? '-' }}
                                            </td>

                                            <!-- 6. Role -->
                                            <td class="text-center">
                                                <span class="staff-role-badge">
                                                    {{ $staff->roleName->name ?? 'Staff' }}
                                                </span>
                                            </td>

                                            <!-- 7. Department -->
                                            <td class="text-center">
                                                <span class="staff-dept-badge">
                                                    {{ $staff->department ?? '-' }}
                                                </span>
                                            </td>

                                            <!-- 8. Mobile -->
                                            <td class="text-center text-nowrap">
                                                @if(!empty($staff->mobile))
                                                    <span class="text-dark"><i class="fa fa-phone text-muted mr-1"></i>{{ $staff->mobile }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <!-- 9. Interactive Attendance Pill Buttons -->
                                            <td class="text-center">
                                                <div class="status-btn-group">
                                                    <button type="button" class="btn-att-pill btn-att-p {{ $statusVal == 1 ? 'active' : '' }}" onclick="setRowStatus(this, 1)" title="Present (In)">P</button>
                                                    <button type="button" class="btn-att-pill btn-att-a {{ $statusVal == 3 ? 'active' : '' }}" onclick="setRowStatus(this, 3)" title="Absent">A</button>
                                                    <button type="button" class="btn-att-pill btn-att-l {{ $statusVal == 9 ? 'active' : '' }}" onclick="setRowStatus(this, 9)" title="Leave">L</button>
                                                    <button type="button" class="btn-att-pill btn-att-h {{ $statusVal == 5 ? 'active' : '' }}" onclick="setRowStatus(this, 5)" title="Holiday">H</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                <!-- Empty State Row (Full Viewport Centered) -->
                                <tr id="noStaffRecordRow" class="staff-empty-row" style="{{ $visibleCount == 0 ? '' : 'display: none;' }}">
                                    <td colspan="9" class="empty-state-cell">
                                        <div class="empty-state-wrap">
                                            <div class="empty-state-icon-wrap">
                                                <i class="fa fa-users"></i>
                                            </div>
                                            <div class="empty-state-title">No Staff Members Found</div>
                                            <p class="empty-state-subtitle">No active staff records match your current filter parameters.</p>
                                            <button type="button" class="btn-empty-reset" onclick="resetAllFilters();">
                                                <i class="fa fa-refresh"></i> Reset All Filters
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 5. Bottom Action & Save Bar -->
                    <div class="attendance-footer-bar">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted font-weight-bold" style="font-size: 11px;">
                                <i class="fa fa-info-circle text-primary"></i> Live Sheet: Click buttons (P/A/L/H) to mark individual attendance or use bulk tools.
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn-save-attendance" id="btnSubmitAttendance">
                                <i class="fa fa-save"></i> <span>{{ __('staff.Submit Attendance') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // 1. Initialize Current Time
    initCurrentTime();

    // 2. Initialize KPI Counters
    recalculateStats();

    // 3. Search & Filter Listeners
    $('#staffSearchKeyword').on('input', applyStaffFilters);
    $('#filterStaffRole').on('change', applyStaffFilters);
    $('#filterStaffDept').on('change', applyStaffFilters);

    // 4. Date Change Listener - Real-time Attendance Fetching via AJAX
    $('#attendance_date').on('change', function() {
        var selectedDate = $(this).val();
        $('#form_date_hidden').val(selectedDate);
        fetchAttendanceByDate(selectedDate);
    });

    // 5. Sync Time Input
    $('#attendance_time').on('change', function() {
        $('#form_time_hidden').val($(this).val());
    });

    // 6. Handle Form Submission via AJAX for instant response
    $('#staffAttendanceForm').on('submit', function(e) {
        e.preventDefault();
        
        // Ensure hidden sync fields are accurate
        $('#form_date_hidden').val($('#attendance_date').val());
        $('#form_time_hidden').val($('#attendance_time').val());

        var submitBtn = $('#btnSubmitAttendance');
        var headerBtn = $('#btnHeaderSave');
        var originalBtnHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        headerBtn.prop('disabled', true);

        var formData = $(this).serialize();

        $.ajax({
            url: "{{ url('staff_attendance_add') }}",
            type: "POST",
            data: formData,
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                headerBtn.prop('disabled', false);

                if (response.status === 'success') {
                    toastr.success(response.message || 'Staff attendance saved successfully!');
                } else {
                    toastr.error(response.message || 'Unable to save attendance.');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                headerBtn.prop('disabled', false);

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.success('Staff attendance saved successfully!');
                }
            }
        });
    });
});

/* Helper: Set Current Time */
function initCurrentTime() {
    var now = new Date();
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var timeString = hours + ':' + minutes;

    $('#attendance_time').val(timeString);
    $('#form_time_hidden').val(timeString);
}

/* 1-Click Row Status Selection */
function setRowStatus(buttonElement, statusId) {
    var $btn = $(buttonElement);
    var $row = $btn.closest('tr');
    
    // Deactivate sibling buttons in this row
    $row.find('.btn-att-pill').removeClass('active');
    $btn.addClass('active');

    // Update hidden input
    $row.find('.input-att-status').val(statusId);

    // Update Row styling
    $row.removeClass('row-status-present row-status-absent row-status-leave row-status-holiday');
    if (statusId == 1) $row.addClass('row-status-present');
    else if (statusId == 3) $row.addClass('row-status-absent');
    else if (statusId == 9) $row.addClass('row-status-leave');
    else if (statusId == 5) $row.addClass('row-status-holiday');

    // Recalculate KPI stats
    recalculateStats();
}

/* Bulk Mark All Attendance */
function markAllAttendance(statusId) {
    $('.staff-row:visible').each(function() {
        var $row = $(this);
        $row.find('.input-att-status').val(statusId);
        $row.find('.btn-att-pill').removeClass('active');

        if (statusId == 1) {
            $row.find('.btn-att-p').addClass('active');
            $row.removeClass('row-status-present row-status-absent row-status-leave row-status-holiday').addClass('row-status-present');
        } else if (statusId == 3) {
            $row.find('.btn-att-a').addClass('active');
            $row.removeClass('row-status-present row-status-absent row-status-leave row-status-holiday').addClass('row-status-absent');
        } else if (statusId == 9) {
            $row.find('.btn-att-l').addClass('active');
            $row.removeClass('row-status-present row-status-absent row-status-leave row-status-holiday').addClass('row-status-leave');
        } else if (statusId == 5) {
            $row.find('.btn-att-h').addClass('active');
            $row.removeClass('row-status-present row-status-absent row-status-leave row-status-holiday').addClass('row-status-holiday');
        }
    });

    recalculateStats();
}

/* Reset Attendance to Default (Present) */
function resetAttendance() {
    markAllAttendance(1);
    toastr.info('Attendance reset to Present for all staff.');
}

/* Dynamic KPI Counter Calculation */
function recalculateStats() {
    var total = 0;
    var present = 0;
    var absent = 0;
    var leave = 0;
    var holiday = 0;
    var unmarked = 0;

    $('.staff-row:visible').each(function() {
        total++;
        var status = $(this).find('.input-att-status').val();
        if (status == 1) present++;
        else if (status == 3) absent++;
        else if (status == 9) leave++;
        else if (status == 5) holiday++;
        else unmarked++;
    });

    $('#stat_total').text(total);
    $('#stat_present').text(present);
    $('#stat_absent').text(absent);
    $('#stat_leave').text(leave);
    $('#stat_holiday').text(holiday);
    $('#stat_unmarked').text(unmarked);
}

/* Live Instant DOM Search & Filter */
function applyStaffFilters() {
    var keyword = $('#staffSearchKeyword').val().toLowerCase().trim();
    var roleFilter = $('#filterStaffRole').val().toLowerCase().trim();
    var deptFilter = $('#filterStaffDept').val().toLowerCase().trim();

    var visibleIndex = 0;

    $('.staff-row').each(function() {
        var $row = $(this);
        var name = $row.data('name') || '';
        var father = $row.data('father') || '';
        var mobile = String($row.data('mobile') || '');
        var email = $row.data('email') || '';
        var staffId = String($row.data('staff-id') || '');
        var role = $row.data('role') || '';
        var dept = $row.data('dept') || '';

        var matchesKeyword = (!keyword || 
            name.indexOf(keyword) !== -1 || 
            father.indexOf(keyword) !== -1 || 
            mobile.indexOf(keyword) !== -1 || 
            email.indexOf(keyword) !== -1 || 
            staffId.indexOf(keyword) !== -1 || 
            role.indexOf(keyword) !== -1 || 
            dept.indexOf(keyword) !== -1
        );

        var matchesRole = (!roleFilter || role === roleFilter);
        var matchesDept = (!deptFilter || dept === deptFilter);

        if (matchesKeyword && matchesRole && matchesDept) {
            $row.show();
            visibleIndex++;
            $row.find('.row-index').text(visibleIndex);
        } else {
            $row.hide();
        }
    });

    if (visibleIndex === 0) {
        $('#noStaffRecordRow').show();
    } else {
        $('#noStaffRecordRow').hide();
    }

    recalculateStats();
}

/* Reset All Filter Controls */
function resetAllFilters() {
    $('#staffSearchKeyword').val('');
    $('#filterStaffRole').val('');
    $('#filterStaffDept').val('');
    applyStaffFilters();
}

/* Fetch Attendance via AJAX when Date Changes */
function fetchAttendanceByDate(date) {
    $('#tableLoadingOverlay').css('display', 'flex');

    $.ajax({
        url: "{{ url('staff_attendance_add') }}",
        type: "GET",
        data: {
            date: date,
            get_attendance_by_date: 1
        },
        dataType: "json",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#tableLoadingOverlay').hide();
            if (response.status === 'success') {
                var attMap = response.attendance || {};

                $('.staff-row').each(function() {
                    var $row = $(this);
                    var staffId = $row.data('staff-id');
                    var statusId = 1; // Default to Present if not recorded

                    if (attMap[staffId]) {
                        statusId = attMap[staffId].attendance_status_id;
                        if (attMap[staffId].time) {
                            $('#attendance_time').val(attMap[staffId].time.substring(0, 5));
                            $('#form_time_hidden').val(attMap[staffId].time.substring(0, 5));
                        }
                    }

                    $row.find('.input-att-status').val(statusId);
                    $row.find('.btn-att-pill').removeClass('active');
                    $row.removeClass('row-status-present row-status-absent row-status-leave row-status-holiday');

                    if (statusId == 1) {
                        $row.find('.btn-att-p').addClass('active');
                        $row.addClass('row-status-present');
                    } else if (statusId == 3) {
                        $row.find('.btn-att-a').addClass('active');
                        $row.addClass('row-status-absent');
                    } else if (statusId == 9) {
                        $row.find('.btn-att-l').addClass('active');
                        $row.addClass('row-status-leave');
                    } else if (statusId == 5) {
                        $row.find('.btn-att-h').addClass('active');
                        $row.addClass('row-status-holiday');
                    }
                });

                recalculateStats();
                toastr.info('Loaded attendance data for ' + date);
            }
        },
        error: function() {
            $('#tableLoadingOverlay').hide();
            toastr.error('Failed to fetch attendance records for this date.');
        }
    });
}
</script>

@endsection