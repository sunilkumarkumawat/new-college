@extends('layout.app')
@section('content')
@php
    $getSetting = Helper::getSetting();
    $getUser = $data;

    // Fees Calculations
    $assignedTotal = 0;
    if (!empty($assignedFeeDetails) && $assignedFeeDetails->count() > 0) {
        $assignedTotal = $assignedFeeDetails->sum(function($item) {
            return $item->fees_group_amount ?? $item->total_amount ?? 0;
        });
    } elseif (!empty($assignfees)) {
        $assignedTotal = $assignfees->total_amount ?? $assignfees->fees_group_amount ?? 0;
    }

    $discountTotal = 0;
    if (!empty($assignedFeeDetails) && $assignedFeeDetails->count() > 0) {
        $discountTotal = $assignedFeeDetails->sum(function($item) {
            return $item->discount ?? 0;
        });
    } elseif (!empty($assignfees)) {
        $discountTotal = $assignfees->discount ?? 0;
    }

    $paidTotal = 0;
    if (!empty($feesDetail) && $feesDetail->count() > 0) {
        $paidTotal = $feesDetail->sum('total_amount');
    }

    $netAssignedTotal = max(0, $assignedTotal - $discountTotal);
    $balanceDue = max(0, $netAssignedTotal - $paidTotal);

    // Attendance Calculations
    $totalWorkingDays = $attendanceStats->total_days ?? 0;
    $presentDays = $attendanceStats->present_days ?? 0;
    $absentDays = $attendanceStats->absent_days ?? 0;
    $leaveDays = $attendanceStats->leave_days ?? 0;
    $halfDays = $attendanceStats->half_days ?? 0;
    $holidayDays = $attendanceStats->holiday_days ?? 0;

    $effectiveAttended = $presentDays + ($halfDays * 0.5);
    $attendancePercent = $totalWorkingDays > 0 ? round(($effectiveAttended / $totalWorkingDays) * 100, 1) : 0;

    // DOB & Age
    $dobFormatted = !empty($getUser->dob) && $getUser->dob != '1970-01-01' ? date('d-M-Y', strtotime($getUser->dob)) : null;
    $age = '';
    if (!empty($getUser->dob) && $getUser->dob != '1970-01-01') {
        try {
            $birthDate = new DateTime($getUser->dob);
            $today = new DateTime('today');
            $age = $birthDate->diff($today)->y . ' Yrs';
        } catch (\Exception $e) {
            $age = '';
        }
    }

    // Stream Subjects
    $studentStreamSubjectIds = !empty($getUser->stream_subject) ? explode(',', $getUser->stream_subject) : [];
    $selectedStreamSubjects = [];
    if (!empty($streamSubjectsList) && !empty($studentStreamSubjectIds)) {
        foreach ($streamSubjectsList as $sub) {
            if (in_array($sub->id, $studentStreamSubjectIds)) {
                $selectedStreamSubjects[] = $sub->name;
            }
        }
    }

    // Relative URL for Direct Fee Collection
    $collectFeesUrl = url('Fees/add') . '?' . http_build_query(array_filter([
        'admission_id' => $getUser->id ?? null,
        'unique_system_id' => $getUser->unique_system_id ?? null,
        'admission_no' => $getUser->admissionNo ?? null,
        'course_id' => $getUser->ClassTypes->course_id ?? null,
        'class_type_id' => $getUser->class_type_id ?? null,
        'batch' => $getUser->batch ?? null,
    ]));
@endphp

<style>
/* ========================================================
   STUDENT PROFILE MASTER - EXECUTIVE TABBED EXCEL GREEN THEME
   (Strictly matching enquiryAdd, admissionView & staff/add_teachers layout)
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
    --text-muted: #64748b;
}

.content-wrapper {
    background-color: #f4f6f9;
}

.student-compact-page {
    font-size: 11.5px;
    color: var(--text-main);
}

/* Fallback Grid Gap System for BS4/BS5 Compatibility */
.row.g-1 { margin-left: -3px; margin-right: -3px; }
.row.g-1 > [class*="col"] { padding-left: 3px; padding-right: 3px; }

.row.g-2 { margin-left: -5px; margin-right: -5px; }
.row.g-2 > [class*="col"] { padding-left: 5px; padding-right: 5px; }

.row.g-3 { margin-left: -8px; margin-right: -8px; }
.row.g-3 > [class*="col"] { padding-left: 8px; padding-right: 8px; }

/* 1. Main Card Wrapper */
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
    flex-wrap: wrap;
}

.dash-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    color: #ffffff !important;
}

.dash-title i {
    color: #ffc107;
    margin-right: 8px;
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
    flex-wrap: wrap;
}
.dash-title-tools > * {
    margin-left: 6px;
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
    cursor: pointer;
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-add:hover {
    background: #166534 !important;
    color: #ffffff !important;
}

/* 3. Hero Identity Card with Perfect Spacing */
.student-hero-card {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 14px 16px;
    margin-bottom: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.student-hero-flex {
    display: flex;
    align-items: center;
}

.student-avatar-box {
    position: relative;
    width: 76px;
    height: 76px;
    min-width: 76px;
    flex-shrink: 0;
    margin-right: 14px; /* Explicit margin so it never overlaps in BS4/BS5 */
    cursor: pointer;
}
.student-avatar-box img {
    width: 76px;
    height: 76px;
    border-radius: 4px;
    object-fit: cover;
    border: 2px solid var(--green-excel);
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}
.avatar-zoom-icon {
    position: absolute;
    bottom: -3px;
    right: -3px;
    background: var(--green-excel);
    color: #fff;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    border: 1.5px solid #ffffff;
}

.student-info-main {
    flex-grow: 1;
    min-width: 0;
}

.student-name-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 5px;
}

.student-name-text {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1.2;
    margin: 0 8px 0 0;
}

.badge-status-active {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-right: 6px;
}
.badge-status-inactive {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-right: 6px;
}
.badge-status-passout {
    background: #e0e7ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-right: 6px;
}
.badge-status-type {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: 700;
}

.student-chips-line {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    font-size: 11px;
    color: #475569;
    margin-bottom: 6px;
}
.chip-item {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 2px 8px;
    margin-right: 6px;
    margin-bottom: 3px;
    display: inline-flex;
    align-items: center;
}
.chip-item i {
    margin-right: 4px;
}

.student-contact-quick-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}
.contact-pill {
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 12px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    transition: all 0.15s ease;
    border: 1px solid transparent;
    margin-right: 6px;
    margin-bottom: 3px;
}
.contact-pill i {
    margin-right: 4px;
}
.contact-pill.phone { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.contact-pill.phone:hover { background: #1d4ed8; color: #fff; }
.contact-pill.whatsapp { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.contact-pill.whatsapp:hover { background: #15803d; color: #fff; }
.contact-pill.email { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
.contact-pill.email:hover { background: #c2410c; color: #fff; }

/* KPI Metric Boxes */
.kpi-metric-box {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 8px 10px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.kpi-metric-box.kmb-alert-due {
    background: #fff5f5;
    border-color: #fca5a5;
}
.kpi-metric-box.kmb-alert-clear {
    background: #f0fdf4;
    border-color: #86efac;
}
.kmb-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
    letter-spacing: 0.3px;
    line-height: 1.2;
}
.kmb-value {
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1.2;
    margin: 3px 0 2px;
}
.kmb-sub {
    font-size: 9.5px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 4. Tab Navigation Bar */
.profile-nav-tabs {
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 16px;
    margin-top: 4px;
    padding: 0 2px;
    display: flex;
    flex-wrap: wrap;
    background: transparent;
}
.profile-nav-tabs .nav-item {
    margin-right: 4px;
    margin-bottom: -2px;
}
.profile-nav-tabs .nav-link {
    font-size: 11.5px;
    font-weight: 600;
    color: #475569;
    border: 1px solid transparent;
    border-radius: 4px 4px 0 0;
    padding: 8px 14px;
    background: transparent;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    text-decoration: none !important;
}
.profile-nav-tabs .nav-link i {
    margin-right: 6px;
}
.profile-nav-tabs .nav-link:hover {
    color: var(--green-excel);
    background: #f8fafc;
    border-color: #e2e8f0 #e2e8f0 transparent;
}
.profile-nav-tabs .nav-link.active {
    color: var(--green-excel) !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-bottom: 2px solid #ffffff !important;
    font-weight: 700 !important;
}
.profile-nav-tabs .nav-link .tab-count-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 9.5px;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 700;
    margin-left: 6px;
}
.profile-nav-tabs .nav-link.active .tab-count-badge {
    background: #dcfce7;
    color: #166534;
}

/* 5. Section Strips (Compact Ribbon Dividers) */
.form-section-strip {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-left: 3.5px solid var(--green-excel);
    padding: 6px 12px;
    margin-top: 8px;
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
}
.form-section-title i {
    margin-right: 6px;
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

/* 6. Field Key-Value Elements with Proper Margins & Padding */
.compact-detail-group {
    margin-bottom: 10px;
    width: 100%;
}

.compact-detail-label {
    font-size: 11px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 3px;
    display: block;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.compact-detail-value {
    min-height: 30px;
    height: 30px;
    border: 1px solid var(--line-subtle);
    border-radius: 3px;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 600;
    color: #1e293b;
    background-color: #f8fafc;
    width: 100%;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.compact-detail-value.highlight-green {
    background-color: #f0fdf4;
    border-color: #a7d0b8;
    color: #166534;
    font-weight: 700;
}
.compact-detail-value.highlight-navy {
    background-color: #f0f4f8;
    border-color: #cbd5e1;
    color: var(--navy);
    font-weight: 700;
}

/* 7. Parent Card */
.parent-detail-card {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 14px;
    margin-bottom: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.pdc-head {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 10px;
    margin-bottom: 10px;
}
.pdc-avatar {
    margin-right: 12px;
}
.pdc-avatar img {
    width: 48px;
    height: 48px;
    border-radius: 4px;
    object-fit: cover;
    border: 1.5px solid var(--green-excel);
    cursor: pointer;
}
.pdc-avatar-icon {
    width: 48px;
    height: 48px;
    border-radius: 4px;
    background: #e0f2fe;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 12px;
}
.pdc-name-role h6 {
    margin: 0 0 2px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--navy);
}
.pdc-name-role span {
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
}
.pdc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    padding: 4px 0;
    border-bottom: 1px dashed #f1f5f9;
}
.pdc-row:last-child { border-bottom: none; }
.pdc-row span { color: var(--text-muted); }
.pdc-row strong { color: var(--text-main); }

/* 8. Tables */
.expense-table-container {
    overflow-x: auto;
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 3px;
    margin-bottom: 12px;
}

.profile-grid-table {
    width: 100%;
    margin-bottom: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11px;
}

.profile-grid-table thead th {
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.2px;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    padding: 6px 8px !important;
    vertical-align: middle !important;
    text-align: center;
    white-space: nowrap;
}

.profile-grid-table tbody td {
    padding: 5px 8px !important;
    border-right: 1px solid var(--line-subtle) !important;
    border-bottom: 1px solid var(--line-subtle) !important;
    vertical-align: middle !important;
    background: #ffffff;
}
.profile-grid-table tbody tr:hover td {
    background: #f8fafc;
}

/* 9. Doc Action Grid */
.doc-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}
.doc-action-card {
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 4px;
    padding: 12px;
    display: flex;
    align-items: center;
    transition: all 0.15s ease;
}
.doc-action-card:hover {
    border-color: var(--green-excel-border);
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    transform: translateY(-1px);
}
.dac-icon {
    width: 38px;
    height: 38px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    margin-right: 10px;
}
.dac-info {
    flex-grow: 1;
    overflow: hidden;
}
.dac-info h6 {
    margin: 0 0 2px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--navy);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dac-info p {
    margin: 0 0 5px;
    font-size: 10px;
    color: var(--text-muted);
}

/* 10. Executive Footer */
.dash-form-footer {
    padding: 10px 16px;
    background: #f8fafc;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}

/* ==============================================
   MODALS - STREAMLINED EXCEL GREEN THEME
   (Strictly matches login_credential_reports & admissionView)
   ============================================== */
.modal {
    text-align: center;
    padding: 0 !important;
}

.modal.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.modal-dialog {
    margin: auto !important;
    display: inline-block;
    text-align: left;
    vertical-align: middle;
}

.modal-dialog.modal-dialog-centered {
    display: flex !important;
    align-items: center !important;
    min-height: calc(100vh - 40px) !important;
    justify-content: center !important;
}

.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 4px !important;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,.25) !important;
    background: #ffffff !important;
    width: 100%;
}

.modal-header {
    padding: 8px 14px !important;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.modal-title {
    margin: 0 !important;
    color: #ffffff !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    display: flex;
    align-items: center;
    gap: 7px;
}
.modal-title i {
    color: #ffc107;
    margin-right: 6px;
}

.btn-close-modal {
    background: transparent !important;
    border: 0 !important;
    font-size: 18px !important;
    color: #ffffff !important;
    cursor: pointer !important;
    padding: 0 4px !important;
    line-height: 1;
    opacity: 0.85;
}
.btn-close-modal:hover { opacity: 1; color: #ffffff !important; }

.modal-body {
    padding: 14px 16px !important;
    background: #ffffff !important;
    color: #334155;
}

.modal-footer {
    padding: 8px 14px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #cbd5e1 !important;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
}

.btn-modal-cancel {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    padding: 3px 12px !important;
    cursor: pointer;
    line-height: 1.35;
    text-decoration: none !important;
}
.btn-modal-cancel:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.btn-modal-submit {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    padding: 3px 14px !important;
    cursor: pointer;
    line-height: 1.35;
}
.btn-modal-submit:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}

/* Custom Modal Components matching login_credential_reports */
.single-cred-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-left: 3.5px solid var(--green-excel);
    border-radius: 4px;
    padding: 10px 12px;
}
.student-badge-avatar {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 4px;
    background: var(--green-excel);
    color: #ffffff;
    font-weight: 800;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--green-excel-dark);
}
.single-cred-prepend {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-right: none;
    border-radius: 2px 0 0 2px;
    padding: 4px 10px;
    color: #64748b;
    font-size: 13px;
    display: flex;
    align-items: center;
}
.single-cred-input {
    height: 32px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 0 2px 2px 0 !important;
    font-size: 12px !important;
    padding: 3px 10px !important;
    background: #ffffff !important;
}
.single-cred-input:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15) !important;
}
</style>

<div class="content-wrapper student-compact-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            
            <!-- MAIN EXECUTIVE CARD -->
            <div class="card dash-main-card">
                
                <!-- 1. Executive Top Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-user-circle-o"></i>
                        <span>Student Comprehensive Profile</span>
                        <small>| {{ $getUser->first_name ?? '' }} {{ $getUser->last_name ?? '' }} (Adm No: #{{ $getUser->admissionNo ?? '—' }})</small>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('admissionView') }}" class="btn-header-action" title="Back to Admission List">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> Back
                        </a>

                        @if($getUser->status != 3)
                            <a href="{{ url('admissionEdit', $getUser->id) }}" class="btn-header-action" title="Edit Student Admission">
                                <i class="fa fa-edit text-primary mr-1"></i> Edit
                            </a>
                        @endif

                        <a href="{{ $collectFeesUrl }}" class="btn-header-action btn-header-add" title="Collect Fees">
                            <i class="fa fa-money mr-1"></i> Collect Fee
                        </a>

                        <!-- Print Dropdown -->
                        <div class="dropdown d-inline-block">
                            <button class="btn-header-action dropdown-toggle" type="button" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-print text-dark mr-1"></i> Print Docs
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 11.5px; border-radius: 3px;">
                                <li>
                                    <a class="dropdown-item py-1" href="{{ url('admissionStudentPrint', $getUser->id) }}" target="_blank">
                                        <i class="fa fa-file-text-o text-success me-2 mr-2"></i> Admission Form
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-1" href="{{ url('admissionStudentIdPrint', $getUser->id) }}" target="_blank">
                                        <i class="fa fa-id-card-o text-primary me-2 mr-2"></i> Student ID Card
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-1" href="{{ url('fees_ledger_print', $getUser->id) }}" target="_blank">
                                        <i class="fa fa-book text-warning me-2 mr-2"></i> Fees Ledger Statement
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item py-1" href="{{ url('cc_print', $getUser->id) }}" target="_blank">
                                        <i class="fa fa-certificate text-info me-2 mr-2"></i> Character Certificate (CC)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-1" href="{{ url('tc_print', $getUser->id) }}" target="_blank">
                                        <i class="fa fa-file-pdf-o text-danger me-2 mr-2"></i> Transfer Certificate (TC)
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Login Info -->
                        <button type="button" class="btn-header-action loginDetail" 
                                data-user_name="{{ $getUser->userName ?? '' }}" 
                                data-confirm_password="{{ $getUser->confirm_password ?? '' }}" 
                                data-first_name="{{ $getUser->first_name ?? '' }}" 
                                data-last_name="{{ $getUser->last_name ?? '' }}" 
                                title="View Login Credentials">
                            <i class="fa fa-key text-warning mr-1"></i> Login Info
                        </button>

                        <!-- Status Toggle -->
                        @if($getUser->status != 3)
                            @if($getUser->status == 1)
                                <button type="button" class="btn-header-action change_status" data-id="{{ $getUser->id }}" data-status="0" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#myModal" data-target="#myModal" title="Deactivate Student">
                                    <i class="fa fa-pause text-danger mr-1"></i> Disable
                                </button>
                            @else
                                <button type="button" class="btn-header-action change_status" data-id="{{ $getUser->id }}" data-status="1" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#myModal" data-target="#myModal" title="Activate Student">
                                    <i class="fa fa-play text-success mr-1"></i> Enable
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="p-3">
                    
                    <!-- 2. Hero Identity Card & KPI Ribbon -->
                    <div class="student-hero-card">
                        <div class="row align-items-center g-3">
                            
                            <!-- Left: Student Avatar & Quick Chips -->
                            <div class="col-12 col-lg-5">
                                <div class="student-hero-flex">
                                    <div class="student-avatar-box">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'profile/'.$getUser->image }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" alt="Student" class="img-lightbox-trigger" title="Click to enlarge photo">
                                        <span class="avatar-zoom-icon"><i class="fa fa-search-plus"></i></span>
                                    </div>
                                    <div class="student-info-main">
                                        <div class="student-name-row">
                                            <h4 class="student-name-text">{{ $getUser->first_name ?? '' }} {{ $getUser->last_name ?? '' }}</h4>
                                            @if($getUser->status == 1)
                                                <span class="badge-status-active"><i class="fa fa-check-circle"></i> Active</span>
                                            @elseif($getUser->status == 0)
                                                <span class="badge-status-inactive"><i class="fa fa-ban"></i> Inactive</span>
                                            @elseif($getUser->status == 3)
                                                <span class="badge-status-passout"><i class="fa fa-graduation-cap"></i> Passout</span>
                                            @endif
                                            @if(!empty($getUser->student_type))
                                                <span class="badge-status-type">{{ $getUser->student_type }}</span>
                                            @endif
                                        </div>
                                        <div class="student-chips-line">
                                            <span class="chip-item"><i class="fa fa-id-badge text-muted"></i> Adm: <strong>#{{ $getUser->admissionNo ?? '—' }}</strong></span>
                                            @if(!empty($getUser->roll_no))
                                                <span class="chip-item"><i class="fa fa-list-ol text-muted"></i> Roll: <strong>{{ $getUser->roll_no }}</strong></span>
                                            @endif
                                            <span class="chip-item"><i class="fa fa-graduation-cap text-muted"></i> <strong>{{ $getUser->ClassTypes->name ?? '—' }}</strong></span>
                                            @if(!empty($getUser->course))
                                                <span class="chip-item"><i class="fa fa-book text-muted"></i> <strong>{{ $getUser->course }}</strong></span>
                                            @endif
                                        </div>
                                        <!-- Quick Contact Bar -->
                                        <div class="student-contact-quick-bar">
                                            @if(!empty($getUser->mobile))
                                                <a href="tel:{{ $getUser->mobile }}" class="contact-pill phone" title="Call Student"><i class="fa fa-phone"></i> {{ $getUser->mobile }}</a>
                                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $getUser->mobile) }}" target="_blank" class="contact-pill whatsapp" title="WhatsApp"><i class="fa fa-whatsapp"></i> Chat</a>
                                            @endif
                                            @if(!empty($getUser->email))
                                                <a href="mailto:{{ $getUser->email }}" class="contact-pill email" title="Send Email"><i class="fa fa-envelope"></i> {{ $getUser->email }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: 4 Executive KPI Metric Cards -->
                            <div class="col-12 col-lg-7">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <div class="kpi-metric-box">
                                            <div class="kmb-label">Assigned Fee</div>
                                            <div class="kmb-value">₹{{ number_format($netAssignedTotal, 2) }}</div>
                                            <div class="kmb-sub text-muted">Gross: ₹{{ number_format($assignedTotal, 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="kpi-metric-box">
                                            <div class="kmb-label">Fee Paid</div>
                                            <div class="kmb-value text-success">₹{{ number_format($paidTotal, 2) }}</div>
                                            <div class="kmb-sub text-success">
                                                {{ $netAssignedTotal > 0 ? round(($paidTotal / $netAssignedTotal) * 100, 1) . '% Cleared' : '100% Cleared' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="kpi-metric-box {{ $balanceDue > 0 ? 'kmb-alert-due' : 'kmb-alert-clear' }}">
                                            <div class="kmb-label {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">Pending Dues</div>
                                            <div class="kmb-value {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($balanceDue, 2) }}</div>
                                            <div class="kmb-sub {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $balanceDue > 0 ? 'Payment Due' : 'All Clear' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="kpi-metric-box">
                                            <div class="kmb-label">Attendance</div>
                                            <div class="kmb-value {{ $attendancePercent >= 75 ? 'text-success' : ($attendancePercent >= 60 ? 'text-warning' : 'text-danger') }}">{{ $attendancePercent }}%</div>
                                            <div class="kmb-sub text-muted">P: {{ $presentDays }} | A: {{ $absentDays }} | L: {{ $leaveDays }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 3. TAB NAVIGATION PANELS -->
                    <ul class="nav nav-tabs profile-nav-tabs" id="studentProfileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-profile-btn" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-profile" data-target="#tab-profile" type="button" role="tab" aria-controls="tab-profile" aria-selected="true">
                                <i class="fa fa-user-circle-o"></i> Basic Profile & KYC
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-family-btn" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-family" data-target="#tab-family" type="button" role="tab" aria-controls="tab-family" aria-selected="false">
                                <i class="fa fa-users"></i> Parents & Guardian
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-fees-btn" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-fees" data-target="#tab-fees" type="button" role="tab" aria-controls="tab-fees" aria-selected="false">
                                <i class="fa fa-credit-card"></i> Fees & Ledger
                                <span class="tab-count-badge">{{ !empty($feesDetail) ? $feesDetail->count() : 0 }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-attendance-btn" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-attendance" data-target="#tab-attendance" type="button" role="tab" aria-controls="tab-attendance" aria-selected="false">
                                <i class="fa fa-calendar-check-o"></i> Attendance
                                <span class="tab-count-badge">{{ $attendancePercent }}%</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-facilities-btn" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-facilities" data-target="#tab-facilities" type="button" role="tab" aria-controls="tab-facilities" aria-selected="false">
                                <i class="fa fa-building-o"></i> Campus Facilities
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-docs-btn" data-bs-toggle="tab" data-toggle="tab" data-bs-target="#tab-docs" data-target="#tab-docs" type="button" role="tab" aria-controls="tab-docs" aria-selected="false">
                                <i class="fa fa-print"></i> Documents & Prints
                            </button>
                        </li>
                    </ul>

                    <!-- 4. TAB CONTENTS -->
                    <div class="tab-content" id="studentProfileTabContent">
                        
                        <!-- ==============================================
                             TAB 1: BASIC PROFILE & KYC
                             ============================================== -->
                        <div class="tab-pane fade show active" id="tab-profile" role="tabpanel" aria-labelledby="tab-profile-btn">
                            
                            <!-- Academic Details Strip -->
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-graduation-cap"></i> 1. Academic & Admission Details
                                </span>
                                <span class="form-section-badge">Academic Enrollment</span>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Course Name</label>
                                        <div class="compact-detail-value highlight-navy">{{ $getUser->course ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Batch Duration</label>
                                        <div class="compact-detail-value">{{ $getUser->batch ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Class / Semester</label>
                                        <div class="compact-detail-value highlight-navy">{{ $getUser->ClassTypes->name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Roll Number</label>
                                        <div class="compact-detail-value font-monospace">{{ $getUser->roll_no ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Enrollment Number</label>
                                        <div class="compact-detail-value highlight-green font-monospace">{{ $getUser->enrollment_no ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Web Reg. No.</label>
                                        <div class="compact-detail-value highlight-green font-monospace">{{ $getUser->web_registration_no ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Admission Number</label>
                                        <div class="compact-detail-value highlight-green font-monospace">#{{ $getUser->admissionNo ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Date of Admission</label>
                                        <div class="compact-detail-value">
                                            {{ !empty($getUser->admission_date) && $getUser->admission_date != '1970-01-01' ? date('d-M-Y', strtotime($getUser->admission_date)) : '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Admission Type</label>
                                        <div class="compact-detail-value">
                                            @if($getUser->admission_type_id == 1) Regular
                                            @elseif($getUser->admission_type_id == 2) Non-Regular
                                            @elseif($getUser->admission_type_id == 3) Other
                                            @else — @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Student Quota / Type</label>
                                        <div class="compact-detail-value">{{ $getUser->student_type ?? 'General' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Medium of Instruction</label>
                                        <div class="compact-detail-value">{{ $getUser->medium ?? 'Hindi/English' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Ledger / SRN Number</label>
                                        <div class="compact-detail-value font-monospace">{{ $getUser->ledger_no ?? $getUser->srn ?? '—' }}</div>
                                    </div>
                                </div>
                                @if(!empty($selectedStreamSubjects))
                                    <div class="col-12 col-md-6">
                                        <div class="compact-detail-group">
                                            <label class="compact-detail-label">Stream Subjects</label>
                                            <div class="compact-detail-value" style="height: auto; min-height: 30px; white-space: normal;">
                                                @foreach($selectedStreamSubjects as $subj)
                                                    <span class="badge bg-light text-dark border mr-1 me-1 mb-1">{{ $subj }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($getUser->previous_school))
                                    <div class="col-12 col-md-6">
                                        <div class="compact-detail-group">
                                            <label class="compact-detail-label">Previous School / College</label>
                                            <div class="compact-detail-value" style="height: auto; min-height: 30px; white-space: normal;">{{ $getUser->previous_school }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Personal Details Strip -->
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-id-card-o"></i> 2. Personal & KYC Details
                                </span>
                                <span class="form-section-badge">Personal Bio</span>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Student Name</label>
                                        <div class="compact-detail-value">{{ $getUser->first_name ?? '' }} {{ $getUser->last_name ?? '' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Date of Birth</label>
                                        <div class="compact-detail-value">{{ $dobFormatted ?? '—' }} @if($age) <small class="text-muted ml-1">({{ $age }})</small> @endif</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Gender</label>
                                        <div class="compact-detail-value">{{ $getUser->Gender->name ?? ($getUser->gender_id == 1 ? 'Male' : ($getUser->gender_id == 2 ? 'Female' : '—')) }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Blood Group</label>
                                        <div class="compact-detail-value"><span class="badge bg-danger-subtle text-danger border">{{ $getUser->blood_group ?? '—' }}</span></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Category / Caste</label>
                                        <div class="compact-detail-value">{{ $getUser->category ?? $getUser->caste_category ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Religion</label>
                                        <div class="compact-detail-value">{{ $getUser->religion ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Aadhaar Number</label>
                                        <div class="compact-detail-value font-monospace">{{ $getUser->aadhaar ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Jan Aadhaar Number</label>
                                        <div class="compact-detail-value font-monospace">{{ $getUser->jan_aadhaar ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Height / Weight</label>
                                        <div class="compact-detail-value">{{ $getUser->height ? $getUser->height . ' cm' : '—' }} / {{ $getUser->weight ? $getUser->weight . ' kg' : '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">House / Sector</label>
                                        <div class="compact-detail-value">{{ $getUser->house ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Student Mobile</label>
                                        <div class="compact-detail-value font-monospace">
                                            @if(!empty($getUser->mobile))
                                                <a href="tel:{{ $getUser->mobile }}" class="text-dark font-weight-bold text-decoration-none">{{ $getUser->mobile }}</a>
                                            @else — @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Email Address</label>
                                        <div class="compact-detail-value">{{ $getUser->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Residential Details Strip -->
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-map-marker"></i> 3. Residential & Permanent Address
                                </span>
                                <span class="form-section-badge">Location</span>
                            </div>

                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Full Street Address</label>
                                        <div class="compact-detail-value font-weight-bold" style="height: auto; min-height: 30px; white-space: normal;">{{ $getUser->address ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Village / City</label>
                                        <div class="compact-detail-value">{{ $getUser->village_city ?? $getUser->city_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Tehsil</label>
                                        <div class="compact-detail-value">{{ $getUser->tehsil ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">District</label>
                                        <div class="compact-detail-value">{{ $getUser->district ?? $getUser->city_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">State</label>
                                        <div class="compact-detail-value">{{ $getUser->state_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Country</label>
                                        <div class="compact-detail-value">{{ $getUser->country_name ?? 'India' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">PIN Code</label>
                                        <div class="compact-detail-value font-monospace font-weight-bold">{{ $getUser->pincode ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ==============================================
                             TAB 2: PARENTS & GUARDIAN
                             ============================================== -->
                        <div class="tab-pane fade" id="tab-family" role="tabpanel" aria-labelledby="tab-family-btn">
                            
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-users"></i> Parents & Family Background
                                </span>
                                <span class="form-section-badge">Family KYC</span>
                            </div>

                            <div class="row g-3">
                                <!-- Father Card -->
                                <div class="col-12 col-md-4">
                                    <div class="parent-detail-card h-100">
                                        <div class="pdc-head">
                                            <div class="pdc-avatar">
                                                <img src="{{ env('IMAGE_SHOW_PATH').'father_image/'.$getUser->father_img }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" alt="Father" class="img-lightbox-trigger" title="Father Photo">
                                            </div>
                                            <div class="pdc-name-role">
                                                <span>Father Details</span>
                                                <h6>{{ $getUser->father_name ?? '—' }}</h6>
                                            </div>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Mobile No:</span>
                                            <strong>
                                                @if(!empty($getUser->father_mobile))
                                                    <a href="tel:{{ $getUser->father_mobile }}" class="text-decoration-none">{{ $getUser->father_mobile }}</a>
                                                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $getUser->father_mobile) }}" target="_blank" class="text-success ml-1"><i class="fa fa-whatsapp"></i></a>
                                                @else — @endif
                                            </strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Aadhaar No:</span>
                                            <strong class="font-monospace">{{ $getUser->father_aadhaar ?? '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>PAN Card:</span>
                                            <strong class="font-monospace">{{ $getUser->father_pancard ?? '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Occupation:</span>
                                            <strong>{{ $getUser->father_occupation ?? '—' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mother Card -->
                                <div class="col-12 col-md-4">
                                    <div class="parent-detail-card h-100">
                                        <div class="pdc-head">
                                            <div class="pdc-avatar">
                                                <img src="{{ env('IMAGE_SHOW_PATH').'mother_image/'.$getUser->mother_img }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" alt="Mother" class="img-lightbox-trigger" title="Mother Photo">
                                            </div>
                                            <div class="pdc-name-role">
                                                <span>Mother Details</span>
                                                <h6>{{ $getUser->mother_name ?? '—' }}</h6>
                                            </div>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Mobile No:</span>
                                            <strong>
                                                @if(!empty($getUser->mother_mob))
                                                    <a href="tel:{{ $getUser->mother_mob }}" class="text-decoration-none">{{ $getUser->mother_mob }}</a>
                                                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $getUser->mother_mob) }}" target="_blank" class="text-success ml-1"><i class="fa fa-whatsapp"></i></a>
                                                @else — @endif
                                            </strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Aadhaar No:</span>
                                            <strong class="font-monospace">{{ $getUser->mother_aadhaar ?? '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>PAN Card:</span>
                                            <strong class="font-monospace">{{ $getUser->mother_pancard ?? '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Occupation:</span>
                                            <strong>{{ $getUser->mother_occupation ?? '—' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guardian & Financial Card -->
                                <div class="col-12 col-md-4">
                                    <div class="parent-detail-card h-100">
                                        <div class="pdc-head">
                                            <div class="pdc-avatar-icon"><i class="fa fa-shield"></i></div>
                                            <div class="pdc-name-role">
                                                <span>Guardian / Household</span>
                                                <h6>{{ $getUser->guardian_name ?? 'Parents / Family' }}</h6>
                                            </div>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Guardian Mob:</span>
                                            <strong>{{ $getUser->guardian_mobile ?? '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Annual Income:</span>
                                            <strong class="text-success">{{ $getUser->family_annual_income ? '₹' . $getUser->family_annual_income : '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>Family ID:</span>
                                            <strong class="font-monospace">{{ $getUser->family_id ?? '—' }}</strong>
                                        </div>
                                        <div class="pdc-row">
                                            <span>BPL Status:</span>
                                            <strong>{{ $getUser->bpl ?? 'No' }} {{ $getUser->bpl_certificate_no ? '(' . $getUser->bpl_certificate_no . ')' : '' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ==============================================
                             TAB 3: FEES & FINANCIAL LEDGER
                             ============================================== -->
                        <div class="tab-pane fade" id="tab-fees" role="tabpanel" aria-labelledby="tab-fees-btn">
                            
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-credit-card"></i> Fees Structure & Payment Transactions
                                </span>
                                <div class="d-flex align-items-center">
                                    <a href="{{ $collectFeesUrl }}" class="btn-header-action btn-header-add py-0 px-2 mr-2" style="height: 22px;">
                                        <i class="fa fa-plus-circle mr-1"></i> Collect Fee
                                    </a>
                                    <a href="{{ url('fees_ledger_print', $getUser->id) }}" target="_blank" class="btn-header-action py-0 px-2 mr-2" style="height: 22px;">
                                        <i class="fa fa-print mr-1"></i> Print Ledger
                                    </a>
                                    <span class="form-section-badge">Financial</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <!-- Left: Assigned Fee Heads Table -->
                                <div class="col-12 col-lg-6">
                                    <h6 class="font-weight-bold text-dark mb-2" style="font-size: 11.5px;">
                                        <i class="fa fa-list-alt text-success mr-1"></i> Assigned Fee Heads Breakdown
                                    </h6>
                                    <div class="expense-table-container">
                                        <table class="table profile-grid-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 35px;">#</th>
                                                    <th style="text-align: left;">Assigned Fee Head</th>
                                                    <th style="width: 90px; text-align: right;">Amount</th>
                                                    <th style="width: 80px; text-align: right;">Discount</th>
                                                    <th style="width: 95px; text-align: right;">Net (₹)</th>
                                                    <th style="width: 70px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($assignedFeeDetails) && $assignedFeeDetails->count() > 0)
                                                    @php $sr = 1; @endphp
                                                    @foreach($assignedFeeDetails as $assignHead)
                                                        @php
                                                            $headAmt = $assignHead->fees_group_amount ?? $assignHead->total_amount ?? 0;
                                                            $headDiscount = $assignHead->discount ?? 0;
                                                            $headNet = isset($assignHead->net_amount) ? $assignHead->net_amount : ($headAmt - $headDiscount);
                                                        @endphp
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-muted">{{ $sr++ }}</td>
                                                            <td><strong>{{ $assignHead->group_name ?? 'Fee Head #' . $assignHead->fees_group_id }}</strong></td>
                                                            <td class="text-right">₹{{ number_format($headAmt, 2) }}</td>
                                                            <td class="text-right text-warning">₹{{ number_format($headDiscount, 2) }}</td>
                                                            <td class="text-right font-weight-bold">₹{{ number_format($headNet, 2) }}</td>
                                                            <td class="text-center"><span class="badge bg-success-subtle text-success border">Assigned</span></td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td class="text-center">1</td>
                                                        <td><strong>Tuition / Academic Fee</strong></td>
                                                        <td class="text-right">₹{{ number_format($assignedTotal, 2) }}</td>
                                                        <td class="text-right text-warning">₹{{ number_format($discountTotal, 2) }}</td>
                                                        <td class="text-right font-weight-bold">₹{{ number_format($netAssignedTotal, 2) }}</td>
                                                        <td class="text-center"><span class="badge bg-info-subtle text-info border">Standard</span></td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: var(--green-excel-light); font-weight: 700;">
                                                    <td colspan="2" class="text-right">Total:</td>
                                                    <td class="text-right">₹{{ number_format($assignedTotal, 2) }}</td>
                                                    <td class="text-right text-warning">₹{{ number_format($discountTotal, 2) }}</td>
                                                    <td class="text-right text-success">₹{{ number_format($netAssignedTotal, 2) }}</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <!-- Right: Payment Transactions Table -->
                                <div class="col-12 col-lg-6">
                                    <h6 class="font-weight-bold text-dark mb-2" style="font-size: 11.5px;">
                                        <i class="fa fa-history text-primary mr-1"></i> Payment Transaction Log
                                    </h6>
                                    <div class="expense-table-container">
                                        <table class="table profile-grid-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 35px;">#</th>
                                                    <th style="width: 90px;">Receipt</th>
                                                    <th style="width: 85px;">Date</th>
                                                    <th>Fee Type</th>
                                                    <th style="width: 80px;">Mode</th>
                                                    <th style="width: 95px; text-align: right;">Paid (₹)</th>
                                                    <th style="width: 45px;">Act</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($feesDetail) && $feesDetail->count() > 0)
                                                    @php $txSr = 1; @endphp
                                                    @foreach($feesDetail as $tx)
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-muted">{{ $txSr++ }}</td>
                                                            <td class="text-center font-monospace text-primary">#{{ $tx->receipt_no ?? $tx->id }}</td>
                                                            <td class="text-center">{{ !empty($tx->date) ? date('d-M-y', strtotime($tx->date)) : '—' }}</td>
                                                            <td class="text-center">
                                                                @if($tx->fees_type == 0) College
                                                                @elseif($tx->fees_type == 1) Hostel
                                                                @elseif($tx->fees_type == 2) Library
                                                                @else General @endif
                                                            </td>
                                                            <td class="text-center">{{ $tx->PaymentMode->name ?? 'Cash' }}</td>
                                                            <td class="text-right font-weight-bold text-success">₹{{ number_format($tx->total_amount ?? 0, 2) }}</td>
                                                            <td class="text-center">
                                                                <a href="{{ url('print_payement', $tx->id) }}" target="_blank" class="btn-header-action py-0 px-1" style="height: 18px; font-size: 9.5px;" title="Print Receipt">
                                                                    <i class="fa fa-print text-success"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="7" class="text-center py-3 text-muted">
                                                            <i class="fa fa-info-circle mr-1"></i> No fee receipts logged yet.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            @if(!empty($feesDetail) && $feesDetail->count() > 0)
                                                <tfoot>
                                                    <tr style="background-color: var(--green-excel-light); font-weight: 700;">
                                                        <td colspan="5" class="text-right">Total Paid:</td>
                                                        <td class="text-right text-success">₹{{ number_format($paidTotal, 2) }}</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ==============================================
                             TAB 4: ATTENDANCE HISTORY & ANALYTICS
                             ============================================== -->
                        <div class="tab-pane fade" id="tab-attendance" role="tabpanel" aria-labelledby="tab-attendance-btn">
                            
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-calendar-check-o"></i> Attendance Analytics & Activity Log
                                </span>
                                <div class="d-flex align-items-center">
                                    <a href="{{ url('studentsAttendanceViewTable') }}" class="btn-header-action py-0 px-2 mr-2" style="height: 22px;">
                                        <i class="fa fa-calendar mr-1"></i> Full Attendance Sheet
                                    </a>
                                    <span class="form-section-badge">{{ $attendancePercent }}% Score</span>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-5">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Attendance Score & Exam Eligibility (Min 75%)</label>
                                        <div class="compact-detail-value p-2 d-flex flex-column justify-content-center" style="min-height: 58px; height: auto;">
                                            <div class="d-flex justify-content-between align-items-center mb-1 w-100">
                                                <span class="font-weight-bold {{ $attendancePercent >= 75 ? 'text-success' : ($attendancePercent >= 60 ? 'text-warning' : 'text-danger') }}">{{ $attendancePercent }}% Aggregate</span>
                                                <span class="text-muted" style="font-size: 10px;">Total Working: <strong>{{ $totalWorkingDays }} Days</strong></span>
                                            </div>
                                            <div class="progress w-100" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                                                <div class="progress-bar {{ $attendancePercent >= 75 ? 'bg-success' : ($attendancePercent >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min(100, max(0, $attendancePercent)) }}%;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-7">
                                    <div class="row g-2">
                                        <div class="col-3">
                                            <div class="compact-detail-group text-center">
                                                <label class="compact-detail-label">Present (P)</label>
                                                <div class="compact-detail-value justify-content-center font-weight-bold text-success">{{ $presentDays }}</div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="compact-detail-group text-center">
                                                <label class="compact-detail-label">Absent (A)</label>
                                                <div class="compact-detail-value justify-content-center font-weight-bold text-danger">{{ $absentDays }}</div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="compact-detail-group text-center">
                                                <label class="compact-detail-label">Leave (L)</label>
                                                <div class="compact-detail-value justify-content-center font-weight-bold text-warning">{{ $leaveDays }}</div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="compact-detail-group text-center">
                                                <label class="compact-detail-label">Half Day</label>
                                                <div class="compact-detail-value justify-content-center font-weight-bold text-info">{{ $halfDays }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($recentAttendance) && $recentAttendance->count() > 0)
                                    <div class="col-12">
                                        <h6 class="font-weight-bold text-dark mb-2" style="font-size: 11.5px;">
                                            <i class="fa fa-history text-secondary mr-1"></i> Recent Daily Attendance Entries
                                        </h6>
                                        <div class="expense-table-container">
                                            <table class="table profile-grid-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 35px;">#</th>
                                                        <th style="width: 120px;">Date</th>
                                                        <th style="width: 110px;">Weekday</th>
                                                        <th style="width: 110px;">Attendance Status</th>
                                                        <th style="text-align: left;">Remarks / Session Faculty</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $attSr = 1; @endphp
                                                    @foreach($recentAttendance->take(10) as $attLog)
                                                        @php $logDate = strtotime($attLog->date); @endphp
                                                        <tr>
                                                            <td class="text-center font-weight-bold text-muted">{{ $attSr++ }}</td>
                                                            <td class="text-center font-monospace"><strong>{{ date('d-M-Y', $logDate) }}</strong></td>
                                                            <td class="text-center text-muted">{{ date('l', $logDate) }}</td>
                                                            <td class="text-center">
                                                                @if($attLog->attendance_status_id == 1)
                                                                    <span class="badge bg-success-subtle text-success border px-2 py-1"><i class="fa fa-check mr-1"></i> Present</span>
                                                                @elseif($attLog->attendance_status_id == 2)
                                                                    <span class="badge bg-danger-subtle text-danger border px-2 py-1"><i class="fa fa-times mr-1"></i> Absent</span>
                                                                @elseif($attLog->attendance_status_id == 3)
                                                                    <span class="badge bg-warning-subtle text-warning border px-2 py-1"><i class="fa fa-clock-o mr-1"></i> Leave</span>
                                                                @elseif($attLog->attendance_status_id == 4)
                                                                    <span class="badge bg-info-subtle text-info border px-2 py-1"><i class="fa fa-adjust mr-1"></i> Half Day</span>
                                                                @elseif($attLog->attendance_status_id == 5)
                                                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="fa fa-star mr-1"></i> Holiday</span>
                                                                @else
                                                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $attLog->status_name ?? 'Other' }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $attLog->status_name ?? 'Logged by Faculty' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>

                        <!-- ==============================================
                             TAB 5: CAMPUS FACILITIES
                             ============================================== -->
                        <div class="tab-pane fade" id="tab-facilities" role="tabpanel" aria-labelledby="tab-facilities-btn">
                            
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-building-o"></i> Hostel, Transport & Library Facilities
                                </span>
                                <span class="form-section-badge">Campus Services</span>
                            </div>

                            <div class="row g-3">
                                <!-- Hostel Block -->
                                <div class="col-12 col-md-4">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Hostel Facility</label>
                                        <div class="compact-detail-value">{{ $getUser->hostel_name ?? 'Day Scholar (No Hostel)' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Hostel Room</label>
                                        <div class="compact-detail-value">{{ $getUser->room_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Bed Number</label>
                                        <div class="compact-detail-value">{{ $getUser->bed_name ?? '—' }}</div>
                                    </div>
                                </div>

                                <!-- Transport Block -->
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Transport Opted</label>
                                        <div class="compact-detail-value font-weight-bold">{{ $getUser->transport ?? 'No' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Bus Vehicle No</label>
                                        <div class="compact-detail-value font-monospace">{{ $getUser->bus_number ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Bus Route / Stoppage</label>
                                        <div class="compact-detail-value">{{ $getUser->bus_route ? $getUser->bus_route . ' (' . ($getUser->stoppage ?? 'Main') . ')' : '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="compact-detail-group">
                                        <label class="compact-detail-label">Monthly Transport Charges</label>
                                        <div class="compact-detail-value text-success font-weight-bold">
                                            {{ !empty($getUser->transpor_charges) ? '₹' . number_format($getUser->transpor_charges, 2) : '—' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Library Block -->
                                @if(!empty($libraryDetails) && $libraryDetails->count() > 0)
                                    <div class="col-12">
                                        <h6 class="font-weight-bold text-dark mb-2" style="font-size: 11.5px;">
                                            <i class="fa fa-book text-warning mr-1"></i> Library Seat & Study Cabin Allocations
                                        </h6>
                                        <div class="expense-table-container">
                                            <table class="table profile-grid-table">
                                                <thead>
                                                    <tr>
                                                        <th>Cabin / Desk</th>
                                                        <th>Study Slot</th>
                                                        <th>Assign Date</th>
                                                        <th>Renewal Due Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($libraryDetails as $lib)
                                                        <tr>
                                                            <td class="font-weight-bold">{{ $lib->cabin_name ?? 'Main Reading Room' }}</td>
                                                            <td>{{ $lib->study_time ?? 'Standard Access' }}</td>
                                                            <td class="text-center">{{ !empty($lib->date) ? date('d-M-Y', strtotime($lib->date)) : '—' }}</td>
                                                            <td class="text-center">{{ !empty($lib->renew_date) ? date('d-M-Y', strtotime($lib->renew_date)) : '—' }}</td>
                                                            <td class="text-center">
                                                                @if(date('Y-m-d') <= ($lib->renew_date ?? ''))
                                                                    <span class="badge bg-success-subtle text-success border">Active</span>
                                                                @else
                                                                    <span class="badge bg-danger-subtle text-danger border">Expired</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>

                        <!-- ==============================================
                             TAB 6: DOCUMENTS & PRINT CENTER
                             ============================================== -->
                        <div class="tab-pane fade" id="tab-docs" role="tabpanel" aria-labelledby="tab-docs-btn">
                            
                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-camera"></i> Student & Family KYC Photographs
                                </span>
                                <span class="form-section-badge">KYC Photos</span>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-4">
                                    <div class="parent-detail-card text-center">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'profile/'.$getUser->image }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" alt="Student Photo" class="img-lightbox-trigger mb-2" style="width: 88px; height: 88px; object-fit: cover; border-radius: 4px; border: 2px solid var(--green-excel); cursor: pointer;">
                                        <h6 class="mb-1 font-weight-bold" style="font-size: 12px;">Student Photograph</h6>
                                        <span class="badge bg-success-subtle text-success border">Profile KYC</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="parent-detail-card text-center">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'father_image/'.$getUser->father_img }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" alt="Father Photo" class="img-lightbox-trigger mb-2" style="width: 88px; height: 88px; object-fit: cover; border-radius: 4px; border: 2px solid var(--navy); cursor: pointer;">
                                        <h6 class="mb-1 font-weight-bold" style="font-size: 12px;">Father Photograph</h6>
                                        <span class="badge bg-primary-subtle text-primary border">Father KYC</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="parent-detail-card text-center">
                                        <img src="{{ env('IMAGE_SHOW_PATH').'mother_image/'.$getUser->mother_img }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" alt="Mother Photo" class="img-lightbox-trigger mb-2" style="width: 88px; height: 88px; object-fit: cover; border-radius: 4px; border: 2px solid var(--navy); cursor: pointer;">
                                        <h6 class="mb-1 font-weight-bold" style="font-size: 12px;">Mother Photograph</h6>
                                        <span class="badge bg-info-subtle text-info border">Mother KYC</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-strip">
                                <span class="form-section-title">
                                    <i class="fa fa-print"></i> Institutional Certificates & Printable Forms
                                </span>
                                <span class="form-section-badge">Printable Docs</span>
                            </div>

                            <div class="doc-action-grid">
                                <div class="doc-action-card">
                                    <div class="dac-icon bg-success-subtle text-success"><i class="fa fa-file-text-o"></i></div>
                                    <div class="dac-info">
                                        <h6>Admission Form</h6>
                                        <p>Standard printed admission dossier.</p>
                                        <a href="{{ url('admissionStudentPrint', $getUser->id) }}" target="_blank" class="btn-header-action py-0 px-2" style="height: 20px; font-size: 10px;">
                                            <i class="fa fa-print mr-1"></i> Print Form
                                        </a>
                                    </div>
                                </div>
                                <div class="doc-action-card">
                                    <div class="dac-icon bg-primary-subtle text-primary"><i class="fa fa-id-card-o"></i></div>
                                    <div class="dac-info">
                                        <h6>Student ID Card</h6>
                                        <p>Campus student identity badge.</p>
                                        <a href="{{ url('admissionStudentIdPrint', $getUser->id) }}" target="_blank" class="btn-header-action py-0 px-2" style="height: 20px; font-size: 10px;">
                                            <i class="fa fa-print mr-1"></i> Print ID
                                        </a>
                                    </div>
                                </div>
                                <div class="doc-action-card">
                                    <div class="dac-icon bg-warning-subtle text-warning"><i class="fa fa-book"></i></div>
                                    <div class="dac-info">
                                        <h6>Fees Ledger</h6>
                                        <p>Complete head-wise financial statement.</p>
                                        <a href="{{ url('fees_ledger_print', $getUser->id) }}" target="_blank" class="btn-header-action py-0 px-2" style="height: 20px; font-size: 10px;">
                                            <i class="fa fa-print mr-1"></i> Print Ledger
                                        </a>
                                    </div>
                                </div>
                                <div class="doc-action-card">
                                    <div class="dac-icon bg-info-subtle text-info"><i class="fa fa-certificate"></i></div>
                                    <div class="dac-info">
                                        <h6>Character Certificate</h6>
                                        <p>Official conduct certificate (CC).</p>
                                        <a href="{{ url('cc_print', $getUser->id) }}" target="_blank" class="btn-header-action py-0 px-2" style="height: 20px; font-size: 10px;">
                                            <i class="fa fa-print mr-1"></i> Print CC
                                        </a>
                                    </div>
                                </div>
                                <div class="doc-action-card">
                                    <div class="dac-icon bg-danger-subtle text-danger"><i class="fa fa-file-pdf-o"></i></div>
                                    <div class="dac-info">
                                        <h6>Transfer Certificate</h6>
                                        <p>Institutional clearance & TC.</p>
                                        <a href="{{ url('tc_print', $getUser->id) }}" target="_blank" class="btn-header-action py-0 px-2" style="height: 20px; font-size: 10px;">
                                            <i class="fa fa-print mr-1"></i> Print TC
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- 5. Executive Footer Strip -->
                <div class="dash-form-footer">
                    <div class="text-muted small">
                        <i class="fa fa-info-circle mr-1 text-primary"></i> 
                        Record ID: <strong>#{{ $getUser->id }}</strong> | Session: <strong>{{ Session::get('session_name') ?? 'Current Session' }}</strong> | Created: <strong>{{ !empty($getUser->created_at) ? date('d-M-Y h:i A', strtotime($getUser->created_at)) : '—' }}</strong>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="{{ url('admissionStudentPrint', $getUser->id) }}" target="_blank" class="btn-header-action mr-2">
                            <i class="fa fa-print mr-1 text-success"></i> Admission Form
                        </a>
                        <a href="{{ url('admissionStudentIdPrint', $getUser->id) }}" target="_blank" class="btn-header-action mr-2">
                            <i class="fa fa-id-card-o mr-1 text-primary"></i> Student ID
                        </a>
                        <a href="{{ url('fees_ledger_print', $getUser->id) }}" target="_blank" class="btn-header-action">
                            <i class="fa fa-book mr-1 text-warning"></i> Fee Ledger
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- ==============================================
     MODALS: Login Credentials, Status, Lightbox
     ============================================== -->

<!-- 1. Single Student Credential Modal -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-labelledby="loginModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalTitle">
                    <i class="fa fa-id-card-o mr-1"></i> Student Login Credentials
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Student Identity Card -->
                <div class="single-cred-card mb-3">
                    <div class="d-flex align-items-center">
                        <div class="student-badge-avatar mr-2" id="modal_student_avatar">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between">
                                <strong class="text-dark font-weight-bold" style="font-size: 13.5px;"><span id="modal_first_name"></span> <span id="modal_last_name"></span></strong>
                                <span class="badge font-weight-bold" style="background:#eef6f0; color:#166534; border:1px solid #8eb99b;">Adm: #{{ $getUser->admissionNo ?? '—' }}</span>
                            </div>
                            <div class="text-muted small mt-1 d-flex flex-wrap align-items-center" style="gap: 4px; font-size: 11px;">
                                <span class="badge badge-light border text-dark">{{ $getUser->ClassTypes->name ?? 'Student' }}</span>
                                @if(!empty($getUser->course))
                                    <span class="badge badge-light border text-secondary">{{ $getUser->course }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Username Field -->
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                        <span><i class="fa fa-user text-success mr-1"></i> Portal Username <span class="text-danger">*</span></span>
                        <span class="text-muted font-normal" style="font-size: 10.5px;">Unique login ID</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="single-cred-prepend"><i class="fa fa-user-circle-o"></i></span>
                        </div>
                        <input type="text" class="form-control single-cred-input font-monospace text-primary font-weight-bold" id="userNameInput" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary py-0 px-2 copy-btn" type="button" data-target="userNameInput" title="Copy Username" style="border-radius: 0 2px 2px 0; border: 1px solid #cbd5e1; border-left: none; background: #f8fafc; font-size: 11px; font-weight: 600;">
                                <i class="fa fa-copy mr-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                        <span><i class="fa fa-lock text-success mr-1"></i> Portal Password <span class="text-danger">*</span></span>
                        <span class="text-muted font-normal" style="font-size: 10.5px;">Secret Password</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="single-cred-prepend"><i class="fa fa-key"></i></span>
                        </div>
                        <input type="password" class="form-control single-cred-input font-monospace text-success font-weight-bold" id="confirmPasswordInput" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary py-0 px-2" type="button" id="btn_toggle_pass" title="Show / Hide Password" style="border: 1px solid #cbd5e1; border-left: none; background: #f8fafc; font-size: 11px;">
                                <i class="fa fa-eye" id="pass_eye_icon"></i>
                            </button>
                            <button class="btn btn-outline-secondary py-0 px-2 copy-btn" type="button" data-target="confirmPasswordInput" title="Copy Password" style="border-radius: 0 2px 2px 0; border: 1px solid #cbd5e1; border-left: none; background: #f8fafc; font-size: 11px; font-weight: 600;">
                                <i class="fa fa-copy mr-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Open Student Portal Button -->
                <a href="{{ url('student/login') }}" target="_blank" class="btn btn-modal-submit w-100 justify-content-center text-center d-block py-2">
                    <i class="fa fa-external-link mr-1"></i> Open Student Login Portal
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Status Change Confirmation Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="statusModalTitle">
                    <i class="fa fa-exclamation-triangle text-warning mr-1"></i> Confirm Status Change
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center p-3">
                <div class="single-cred-card text-center mb-0">
                    <div class="mb-2">
                        <i class="fa fa-question-circle text-warning" style="font-size: 38px;"></i>
                    </div>
                    <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 13.5px;">Change Student Status?</h6>
                    <p class="text-muted small mb-0">Are you sure you want to change the active status of <strong>{{ $getUser->first_name ?? '' }} {{ $getUser->last_name ?? '' }}</strong>?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel mr-2" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm px-3 change_status1" data-bs-dismiss="modal" data-dismiss="modal" style="font-size: 11.5px; font-weight: 700; border-radius: 2px;">
                    <i class="fa fa-check mr-1"></i> Yes, Proceed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Lightbox Modal for Photo Preview -->
<div class="modal fade" id="imageLightboxModal" tabindex="-1" role="dialog" aria-labelledby="lightboxModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lightboxModalTitle">
                    <i class="fa fa-image text-white mr-1"></i> Photograph Preview
                </h5>
                <button type="button" class="btn-close-modal" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="lightboxImg" src="" alt="Full Preview" style="width: 100%; max-height: 380px; object-fit: contain; border-radius: 4px; border: 1px solid #cbd5e1; background: #fff;">
            </div>
            <div class="modal-footer">
                <a id="lightboxDownloadLink" class="btn btn-primary btn-sm mr-2" href="" target="_blank" download style="font-size: 11px; font-weight: 600;">
                    <i class="fa fa-download mr-1"></i> Download
                </a>
                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==============================================
     INTERACTIVE JAVASCRIPT
     ============================================== -->
<script>
$(document).ready(function() {
    var studentStatusId = null;
    var studentTargetStatus = null;

    // 1. Universal Modal Close / Dismiss Handler (Foolproof across Bootstrap 4/5)
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .btn-modal-cancel', function(e) {
        var $targetModal = $(this).closest('.modal');
        if ($targetModal.length) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modalInstance = bootstrap.Modal.getInstance($targetModal[0]);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
            $targetModal.modal('hide');
        }
    });

    // 2. URL Hash tab active support
    var hash = window.location.hash;
    if (hash) {
        var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]') || document.querySelector('button[data-target="' + hash + '"]');
        if (triggerEl) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                var tab = new bootstrap.Tab(triggerEl);
                tab.show();
            } else if ($.fn.tab) {
                $(triggerEl).tab('show');
            }
        }
    }

    // 3. Tab click change hash without jump
    $('.profile-nav-tabs button').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('data-bs-target') || $(e.target).attr('data-target');
        if (target) {
            history.replaceState(null, null, target);
        }
    });

    // 4. Login modal populate
    $(document).on('click', '.loginDetail', function(e) {
        e.preventDefault();
        var userName = $(this).data('user_name');
        var confirmPassword = $(this).data('confirm_password');
        var firstName = $(this).data('first_name');
        var lastName = $(this).data('last_name');

        $('#userNameInput').val(userName || 'N/A');
        $('#confirmPasswordInput').val(confirmPassword || 'N/A');
        $('#confirmPasswordInput').attr('type', 'password');
        $('#pass_eye_icon').removeClass('fa-eye-slash').addClass('fa-eye');
        $('#modal_first_name').text(firstName || '');
        $('#modal_last_name').text(lastName || '');

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var myModal = new bootstrap.Modal(document.getElementById('Modal_id'));
            myModal.show();
        } else {
            $('#Modal_id').modal('show');
        }
    });

    // Toggle Password Visibility
    $(document).on('click', '#btn_toggle_pass', function() {
        var input = $('#confirmPasswordInput');
        var icon = $('#pass_eye_icon');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // 5. 1-Click Copy Credential
    $(document).on('click', '.copy-btn', function(e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        var targetEl = $('#' + targetId);
        var textToCopy = targetEl.is('input') ? targetEl.val().trim() : targetEl.text().trim();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function() {
                toastr.success('Copied: ' + textToCopy);
            }).catch(function() {
                fallbackCopy(textToCopy);
            });
        } else {
            fallbackCopy(textToCopy);
        }
    });

    function fallbackCopy(text) {
        var tempInput = document.createElement('input');
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        toastr.success('Copied: ' + text);
    }

    // 6. Status toggle modal setup
    $(document).on('click', '.change_status', function(e) {
        studentStatusId = $(this).data('id');
        studentTargetStatus = $(this).data('status');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var statusModal = new bootstrap.Modal(document.getElementById('myModal'));
            statusModal.show();
        } else {
            $('#myModal').modal('show');
        }
    });

    // 7. Confirm status change AJAX
    $(document).on('click', '.change_status1', function(e) {
        e.preventDefault();
        if (!studentStatusId) return;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: '/stu_status',
            data: {
                status: studentTargetStatus,
                id: studentStatusId
            },
            success: function(response) {
                toastr.success('Student status updated successfully!');
                setTimeout(function() {
                    location.reload();
                }, 800);
            },
            error: function() {
                toastr.error('Failed to change status. Please try again.');
            }
        });
    });

    // 8. Lightbox image preview
    $(document).on('click', '.img-lightbox-trigger', function() {
        var imgSrc = $(this).attr('src');
        if (imgSrc && !imgSrc.includes('user_image.jpg')) {
            $('#lightboxImg').attr('src', imgSrc);
            $('#lightboxDownloadLink').attr('href', imgSrc);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var lbModal = new bootstrap.Modal(document.getElementById('imageLightboxModal'));
                lbModal.show();
            } else {
                $('#imageLightboxModal').modal('show');
            }
        }
    });
});
</script>

@endsection
