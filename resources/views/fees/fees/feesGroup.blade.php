@php
$getPermission = Helper::getPermission();
$getSession = Helper::getSession();
$classType = Helper::classType();
$courses = $courses ?? Helper::getCourses();

// Usage map & count to find which heads are used in fees_master
$feesGroupUsageMap = [];
$headUsageCount = [];
if (!empty($allFeesMasterRows)) {
    foreach ($allFeesMasterRows as $row) {
        if (!empty($row->fees_group_id)) {
            $feesGroupUsageMap[$row->fees_group_id] = true;
            $headUsageCount[$row->fees_group_id] = ($headUsageCount[$row->fees_group_id] ?? 0) + 1;
        }
    }
}
$feeHeadsList = [];
if (!empty($dataview)) {
    foreach ($dataview as $head) {
        if (!preg_match('/-\s*(Sem|Semester|Year)\s*\d+/i', $head->name)) {
            $feeHeadsList[] = $head;
        }
    }
}
$noClassHeads = $feeHeadsList;

// Check which fees_group / fees_master rows are assigned to students or have payments collected
$assignedMap = [];
$collectedMap = [];
$activeSessionId = Session::get('session_id');
$activeBranchId = Session::get('branch_id');

$assignedRows = \App\Models\fees\FeesAssignDetail::where('session_id', $activeSessionId)
    ->where('branch_id', $activeBranchId)
    ->whereNull('deleted_at')
    ->select('fees_group_id', 'class_type_id')
    ->get();
foreach ($assignedRows as $ar) {
    $assignedMap[$ar->fees_group_id] = true;
    $assignedMap[$ar->fees_group_id . '_' . $ar->class_type_id] = true;
}

$collectedRows = \App\Models\FeesDetail::where('session_id', $activeSessionId)
    ->where('branch_id', $activeBranchId)
    ->whereNull('deleted_at')
    ->where('paid_amount', '>', 0)
    ->select('fees_group_id')
    ->get();
foreach ($collectedRows as $cr) {
    $collectedMap[$cr->fees_group_id] = true;
}

// Existing assignments mapping by fees_group_id for modal prefill
$existingHeadAssignments = [];
if (!empty($allFeesMasterRows)) {
    foreach ($allFeesMasterRows as $row) {
        $fgId = (int)$row->fees_group_id;
        $cTypeId = (int)$row->class_type_id;
        $courseId = !empty($row->ClassTypes->course_id) ? (int)$row->ClassTypes->course_id : null;
        $amt = (float)($row->amount ?? 0);
        $boysAmt = !empty($row->boys_amount) ? (float)$row->boys_amount : $amt;
        $girlsAmt = !empty($row->girls_amount) ? (float)$row->girls_amount : $amt;
        $due = !empty($row->installment_due_date) ? date('Y-m-d', strtotime($row->installment_due_date)) : '';

        if (!isset($existingHeadAssignments[$fgId])) {
            $existingHeadAssignments[$fgId] = [
                'courses' => [],
                'classes' => []
            ];
        }

        if ($courseId && !isset($existingHeadAssignments[$fgId]['courses'][$courseId])) {
            $existingHeadAssignments[$fgId]['courses'][$courseId] = [
                'amount' => $amt,
                'boys_amount' => $boysAmt,
                'girls_amount' => $girlsAmt,
                'due_date' => $due,
                'class_type_id' => $cTypeId
            ];
        }

        $existingHeadAssignments[$fgId]['classes'][$cTypeId] = [
            'amount' => $amt,
            'boys_amount' => $boysAmt,
            'girls_amount' => $girlsAmt,
            'due_date' => $due,
            'course_id' => $courseId
        ];
    }
}
@endphp

@extends('layout.app') 
@section('content')

<style>
/* Strictly Scoped Styles for Fees Unified Setup Page (Does NOT affect Sidebar, Header, or other pages) */
.fees-unified-page {
    --brand-dark: #002c54;
    --brand-dark-hover: #004b8d;
    --brand-darker: #001a33;
    --brand-light: #f8fafc;
    --brand-border: #cbd5e1;
}

/* Base Buttons & Dark Background High-Contrast Rules */
.fees-unified-page .btn-primary {
    background-color: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    color: #ffffff !important;
}
.fees-unified-page .btn-primary:hover, 
.fees-unified-page .btn-primary:focus, 
.fees-unified-page .btn-primary:active {
    background-color: var(--brand-dark-hover) !important;
    border-color: var(--brand-dark-hover) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2) !important;
}
.fees-unified-page .btn-primary i {
    color: inherit !important;
}

/* Card Headers with Dark Background */
.fees-unified-page .card-header.bg-primary {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-bottom: 1px solid var(--brand-darker) !important;
}
.fees-unified-page .card-header.bg-primary .card-title,
.fees-unified-page .card-header.bg-primary h3,
.fees-unified-page .card-header.bg-primary h5 {
    color: #ffffff !important;
    margin-bottom: 0;
}
.fees-unified-page .card-header.bg-primary .card-title i,
.fees-unified-page .card-header.bg-primary h3 i {
    color: #ffffff !important;
}
.fees-unified-page .card-header.bg-primary .badge-light {
    background: #ffffff !important;
    color: var(--brand-dark) !important;
    font-weight: 700;
}

/* Clean Category Badges with High Contrast */
.fees-unified-page .badge-academic {
    background-color: #e0f2fe !important;
    color: #0369a1 !important;
    border: 1px solid #bae6fd !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-admission {
    background-color: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-refundable {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1px solid #fca5a5 !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-examination {
    background-color: #f3e8ff !important;
    color: #7e22ce !important;
    border: 1px solid #e9d5ff !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-practical {
    background-color: #ccfbf1 !important;
    color: #0f766e !important;
    border: 1px solid #99f6e4 !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-facility {
    background-color: #fef3c7 !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-hostel_transport {
    background-color: #ffedd5 !important;
    color: #c2410c !important;
    border: 1px solid #fed7aa !important;
    font-weight: 600 !important;
}
.fees-unified-page .badge-other {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
    font-weight: 600 !important;
}

/* Course Selector Container */
.fees-unified-page .course-selector-box {
    background: #f0f7ff !important;
    border: 1px solid #b8daff !important;
    border-radius: 6px;
    padding: 8px 10px;
    margin-bottom: 10px;
}

/* Structure Mode Toggle Buttons */
.fees-unified-page .mode-toggle-btn {
    flex: 1;
    padding: 6px 8px;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
    border: 1px solid var(--brand-border);
    background: #ffffff;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.2s ease;
}
.fees-unified-page .mode-toggle-btn:hover {
    background: #e2e8f0;
    color: var(--brand-dark);
    border-color: #94a3b8;
}
.fees-unified-page .mode-toggle-btn.active {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-color: var(--brand-dark) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.fees-unified-page .mode-toggle-btn.active:hover {
    background: var(--brand-dark-hover) !important;
    color: #ffffff !important;
}
.fees-unified-page .mode-toggle-btn.active small {
    color: #e2e8f0 !important;
}
.fees-unified-page .mode-toggle-btn:first-child {
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}
.fees-unified-page .mode-toggle-btn:last-child {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}

/* Quick Preset Pills */
.fees-unified-page .quick-preset-btn {
    display: inline-block;
    padding: 2px 8px;
    margin: 2px 1px;
    font-size: 10.5px;
    font-weight: 600;
    border-radius: 10px;
    border: 1px solid #b8daff;
    background: #eef6ff;
    color: #004085;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}
.fees-unified-page .quick-preset-btn:hover {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-color: var(--brand-dark) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
}
.fees-unified-page .quick-preset-btn:hover i {
    color: #ffffff !important;
}
.fees-unified-page .text-purple { color: #9333ea !important; }
.fees-unified-page .text-teal { color: #0d9488 !important; }
.fees-unified-page .text-orange { color: #ea580c !important; }

/* Custom Centered Modals */
.fees-unified-page.modal .modal-dialog-centered {
    display: flex !important;
    align-items: center !important;
    min-height: calc(100% - 3.5rem) !important;
}
.fees-unified-page.modal .modal-content {
    border-radius: 8px !important;
    overflow: hidden !important;
}
.fees-unified-page.modal .modal-header .close {
    opacity: 0.9 !important;
    text-shadow: none !important;
    outline: none !important;
}
.fees-unified-page.modal .modal-header .close:hover {
    opacity: 1 !important;
}

/* Preview Box */
.fees-unified-page .preview-badge {
    display: inline-block;
    padding: 3px 7px;
    margin: 2px 1px;
    font-size: 10.5px;
    font-weight: 600;
    border-radius: 4px;
    background: #ffffff;
    color: #004085;
    border: 1px solid #99caff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.fees-unified-page .preview-box-container {
    background: #ffffff;
    border: 1px solid #c2d4ea;
    border-radius: 5px;
    padding: 8px;
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
}
.fees-unified-page #preview_box {
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
}
.fees-unified-page .preview-header {
    background: #e8f2fc;
    color: var(--brand-dark);
    font-weight: 700;
    font-size: 11.5px;
    padding: 4px 8px;
    border-radius: 4px;
    margin-bottom: 6px;
}
.fees-unified-page .fee-master-amount-box {
    background: #fffdf5;
    border: 1px solid #ffeeba;
    border-radius: 5px;
    padding: 8px 10px;
    margin-top: 8px;
    margin-bottom: 8px;
}

/* Table Headers (Strict High-Contrast White Font on Dark Background) */
.fees-unified-page table thead tr th,
.fees-unified-page .table thead tr th,
.fees-unified-page .padding_table thead tr th,
.fees-unified-page #example1 thead tr th {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    padding: 7px 9px !important;
    font-weight: 600 !important;
    border-color: var(--brand-darker) !important;
    vertical-align: middle !important;
}
.fees-unified-page table thead tr th *,
.fees-unified-page .padding_table thead tr th *,
.fees-unified-page #example1 thead tr th * {
    color: #ffffff !important;
}

/* Executive Course Cards, KPI Chips & Quick Filters */
.fees-unified-page .badge-light {
    background-color: #f1f5f9 !important;
    color: #1e293b !important;
    border: 1px solid #cbd5e1 !important;
}
.fees-unified-page .card-header.bg-primary .badge-light,
.fees-unified-page .course-card-header .badge-light {
    background-color: #ffffff !important;
    color: var(--brand-dark) !important;
    font-weight: 700 !important;
    border: none !important;
}

.fees-unified-page .kpi-chip-card {
    background: #ffffff !important;
    border: 1px solid var(--brand-border) !important;
    border-radius: 6px;
    padding: 6px 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.fees-unified-page .kpi-chip-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}
.fees-unified-page .kpi-chip-icon {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.fees-unified-page .kpi-chip-icon.icon-courses {
    background: #e0f2fe !important;
    color: #0284c7 !important;
}
.fees-unified-page .kpi-chip-icon.icon-classes {
    background: #e0e7ff !important;
    color: #4338ca !important;
}
.fees-unified-page .kpi-chip-icon.icon-pool {
    background: #dcfce7 !important;
    color: #15803d !important;
}
.fees-unified-page .kpi-chip-icon.icon-heads {
    background: #fef3c7 !important;
    color: #b45309 !important;
}
.fees-unified-page .kpi-chip-val {
    font-size: 13.5px;
    font-weight: 700;
    line-height: 1.1;
    color: #0f172a;
}
.fees-unified-page .kpi-chip-label {
    font-size: 9px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Quick Filters Box Container */
.fees-unified-page .quick-filters-box {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px;
    padding: 8px 10px;
    margin-bottom: 10px;
}
.fees-unified-page .quick-filters-box label {
    color: var(--brand-dark) !important;
    font-weight: 700 !important;
    font-size: 11.5px !important;
}
.fees-unified-page .quick-filters-box label i {
    color: var(--brand-dark-hover) !important;
}
.fees-unified-page .quick-filters-box .text-muted,
.fees-unified-page .quick-filters-box small {
    color: #475569 !important;
    font-weight: 600 !important;
    font-size: 10px !important;
}

/* Course Filter Scroll with Left & Right Arrows */
.fees-unified-page .course-filter-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    position: relative;
    width: 100%;
}
.fees-unified-page .course-scroll-btn {
    width: 26px;
    height: 26px;
    min-width: 26px;
    border-radius: 50%;
    background: #ffffff !important;
    border: 1px solid var(--brand-border) !important;
    color: var(--brand-dark) !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.15s ease-in-out;
    padding: 0;
    font-size: 11px;
}
.fees-unified-page .course-scroll-btn:hover {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-color: var(--brand-dark) !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
}
.fees-unified-page .course-filter-scroll {
    display: flex;
    overflow-x: auto;
    gap: 6px;
    padding: 3px 2px;
    flex-grow: 1;
    scroll-behavior: smooth;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE & Edge */
}
.fees-unified-page .course-filter-scroll::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* Course Filter Pills */
.fees-unified-page .course-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    border: 1px solid var(--brand-border) !important;
    background: #ffffff !important;
    color: #1e293b !important;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.fees-unified-page .course-pill-btn i {
    color: #0284c7 !important;
}
.fees-unified-page .course-pill-btn .badge-counter {
    background: #e2e8f0 !important;
    color: #0f172a !important;
    border-radius: 10px;
    padding: 1px 6px;
    font-size: 9.5px;
    font-weight: 700;
}
.fees-unified-page .course-pill-btn:hover {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-color: var(--brand-dark) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.fees-unified-page .course-pill-btn:hover i {
    color: #ffffff !important;
}
.fees-unified-page .course-pill-btn:hover .badge-counter {
    background: rgba(255,255,255,0.25) !important;
    color: #ffffff !important;
}
.fees-unified-page .course-pill-btn.active {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-color: var(--brand-dark) !important;
    box-shadow: 0 2px 5px rgba(0,44,84,0.3) !important;
}
.fees-unified-page .course-pill-btn.active:hover {
    background: var(--brand-dark-hover) !important;
    color: #ffffff !important;
}
.fees-unified-page .course-pill-btn.active i {
    color: #ffffff !important;
}
.fees-unified-page .course-pill-btn.active .badge-counter {
    background: rgba(255,255,255,0.25) !important;
    color: #ffffff !important;
}

/* Course Cards */
.fees-unified-page .course-card {
    background: #ffffff;
    border: 1px solid var(--brand-border);
    border-radius: 8px;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
    overflow: hidden;
}
.fees-unified-page .course-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #94a3b8;
}
.fees-unified-page .course-card-header {
    background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-dark-hover) 100%) !important;
    color: #ffffff !important;
    padding: 8px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.fees-unified-page .course-card-title {
    font-size: 13px;
    font-weight: 700;
    margin: 0;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 6px;
}
.fees-unified-page .course-card-title i {
    color: #ffffff !important;
}

/* Setup Button Inside Course Card */
.fees-unified-page .course-card-header .btn-setup-course {
    background: #ffffff !important;
    color: var(--brand-dark) !important;
    border: 1px solid #ffffff !important;
    font-weight: 700 !important;
    transition: all 0.15s ease-in-out;
}
.fees-unified-page .course-card-header .btn-setup-course:hover {
    background: var(--brand-darker) !important;
    color: #ffffff !important;
    border-color: var(--brand-darker) !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
}
.fees-unified-page .course-card-header .btn-setup-course:hover i {
    color: #ffffff !important;
}

/* Empty Course State */
.fees-unified-page .empty-course-box {
    background: #f8fafc !important;
    border: 1.5px dashed #cbd5e1 !important;
    border-radius: 6px;
    padding: 16px 12px;
    text-align: center;
}
.fees-unified-page .empty-course-box .empty-msg {
    color: #334155 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    margin-bottom: 8px;
}
.fees-unified-page .empty-course-box .empty-msg i {
    color: #0284c7 !important;
    margin-right: 4px;
}
.fees-unified-page .empty-course-box .empty-msg strong {
    color: var(--brand-dark) !important;
}
.fees-unified-page .btn-configure-course {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border: 1px solid var(--brand-dark) !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    padding: 4px 12px !important;
    border-radius: 4px;
    transition: all 0.15s ease-in-out;
}
.fees-unified-page .btn-configure-course:hover {
    background: var(--brand-dark-hover) !important;
    color: #ffffff !important;
    border-color: var(--brand-dark-hover) !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.18) !important;
}
.fees-unified-page .btn-configure-course i {
    color: #ffffff !important;
}

/* Modal Headers & Tables */
.fees-unified-page.modal .modal-header.bg-primary,
.modal.fees-unified-page .modal-header.bg-primary {
    background: var(--brand-dark) !important;
    color: #ffffff !important;
    border-bottom: 1px solid var(--brand-darker) !important;
}
.fees-unified-page.modal .modal-header.bg-primary .modal-title,
.modal.fees-unified-page .modal-header.bg-primary .modal-title {
    color: #ffffff !important;
}
.fees-unified-page.modal .modal-header.bg-primary .close,
.modal.fees-unified-page .modal-header.bg-primary .close {
    color: #ffffff !important;
    text-shadow: none;
    opacity: 0.9;
    transition: opacity 0.15s ease, color 0.15s ease;
}
.fees-unified-page.modal .modal-header.bg-primary .close:hover,
.modal.fees-unified-page .modal-header.bg-primary .close:hover {
    color: #ffdd57 !important;
    opacity: 1;
}
.fees-unified-page.modal table thead th,
.modal.fees-unified-page table thead th {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    font-weight: 700 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    font-size: 11.5px !important;
}
.fees-unified-page.modal table tbody td,
.modal.fees-unified-page table tbody td {
    color: #1e293b !important;
    font-size: 11.5px !important;
}

/* Top Action Bar Buttons */
.fees-unified-page .top-action-bar .btn {
    font-size: 11.5px !important;
    font-weight: 600 !important;
    padding: 5px 12px !important;
    border-radius: 4px !important;
    transition: all 0.15s ease-in-out !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.fees-unified-page .top-action-bar .btn-primary {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    color: #ffffff !important;
}
.fees-unified-page .top-action-bar .btn-primary:hover {
    background: var(--brand-dark-hover) !important;
    border-color: var(--brand-dark-hover) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(0,44,84,0.3) !important;
}
.fees-unified-page .top-action-bar .btn-secondary {
    background: #475569 !important;
    border-color: #475569 !important;
    color: #ffffff !important;
}
.fees-unified-page .top-action-bar .btn-secondary:hover {
    background: #334155 !important;
    border-color: #334155 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(51,65,85,0.3) !important;
}
</style>

<div class="content-wrapper fees-unified-page">
    <section class="content pt-2">
        <div class="container-fluid">
            <!-- Top Action Header Bar -->
            <div class="row align-items-center mb-2 top-action-bar">
                <div class="{{($getPermission->add == 1) ? 'col-md-5' : 'col-md-4'}}">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb p-0 mb-0" style="background:none;">
                            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-home"></i> Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{url('fee_dashboard')}}">Fee Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Unified Fees Setup</li>
                        </ol>
                    </nav>
                </div>
                <div class="{{($getPermission->add == 1) ? 'col-md-7' : 'col-md-8'}} text-md-right d-flex justify-content-end align-items-center flex-wrap" style="gap: 6px;">
                    <button type="button" class="btn btn-warning btn-sm font-weight-bold text-dark text-nowrap shadow-sm" id="btn_open_clone_modal" data-toggle="modal" data-target="#cloneFeesModal" data-bs-toggle="modal" data-bs-target="#cloneFeesModal" title="Clone / Copy Fee Setup from Previous Financial Year">
                        <i class="fa fa-clone mr-1"></i> Clone Previous Year Setup
                    </button>
                    <a href="{{url('fee_dashboard')}}" class="btn btn-secondary btn-sm font-weight-bold text-nowrap">
                        <i class="fa fa-arrow-left"></i> {{ __('messages.Back') }}
                    </a>
                </div>
            </div>

            <div class="row">   
                <!-- Left Side: Create Fee Heads & Assign to Fees Master -->
                <div class="col-md-5 pr-0 {{($getPermission->add == 1) ? '' : 'd-none'}}">
                    <div class="card card-outline card-orange mr-1">
                        <div class="card-header bg-primary py-2 d-flex align-items-center justify-content-between">
                            <h3 class="card-title font-weight-bold mb-0" style="font-size:14px;"><i class="fa fa-money"></i> &nbsp;Fee Structure & Heads Setup</h3>
                            <button type="button" id="btn_reset_form" class="btn btn-danger btn-xs font-weight-bold shadow-none" style="display: none; font-size: 11px; padding: 2px 8px; border-radius: 4px;" data-toggle="modal" data-target="#reset_form_confirm_modal" data-bs-toggle="modal" data-bs-target="#reset_form_confirm_modal">
                                <i class="fa fa-refresh"></i> Reset Form
                            </button>
                        </div>                 
                        
                        <div class="card-body p-2">
                            <!-- Course Auto-Detect Box -->
                            <div class="course-selector-box" id="course_selector_box">
                                <label class="font-weight-bold text-primary mb-1 d-flex justify-content-between align-items-center" style="font-size:11.5px;">
                                    <span><i class="fa fa-graduation-cap"></i> Select Course (Auto-Detects Semesters / Years): <span class="text-danger" id="course_req_star">*</span></span>
                                    <span class="badge badge-primary px-2" id="course_mode_badge" style="font-size: 10px;">Required</span>
                                </label>
                                <select class="form-control form-control-sm select2 font-weight-bold" id="course_selector" onchange="onCourseSelected(this, false)">
                                    <option value="">-- Select Course (e.g. BA, BSc, B.Ed) --</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $c)
                                            @php
                                                $cType = !empty($c->course_type) ? $c->course_type : 'Semester';
                                                $dur = !empty($c->duration) ? (int)$c->duration : 3;
                                                $totSem = !empty($c->total_semester) && (int)$c->total_semester > 0 ? (int)$c->total_semester : ($dur * 2);
                                            @endphp
                                            <option value="{{ $c->id }}" 
                                                data-name="{{ $c->name }}" 
                                                data-type="{{ strtolower($cType) }}" 
                                                data-duration="{{ $dur }}" 
                                                data-semesters="{{ $totSem }}">
                                                {{ $c->name }} &nbsp; [{{ ($cType == 'Yearly') ? ($dur . ' Year(s) - Yearly') : ($totSem . ' Semesters') }}]
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div id="course_detected_info" class="text-success mt-1 font-weight-bold" style="font-size:10.5px; display:none;">
                                    <i class="fa fa-check-circle"></i> <span id="course_detected_text"></span>
                                </div>
                            </div>

                            <!-- Structure Mode Selector -->
                            <label class="font-weight-bold mb-1 text-muted" style="font-size:11px;">Structure Mode:</label>
                            <div class="d-flex mb-2">
                                <div class="mode-toggle-btn active" id="btn_mode_semester" onclick="switchMode('semester')">
                                    <i class="fa fa-graduation-cap"></i> Semester-Wise<br><small style="font-size:9.5px;">(All Semesters)</small>
                                </div>
                                <div class="mode-toggle-btn" id="btn_mode_single_class" onclick="switchMode('single_class')">
                                    <i class="fa fa-book"></i> Single Class<br><small style="font-size:9.5px;">(1 Class / Sem)</small>
                                </div>
                                <div class="mode-toggle-btn" id="btn_mode_single_head" onclick="switchMode('single_head')">
                                    <i class="fa fa-tag"></i> Fee Head Only<br><small style="font-size:9.5px;">(No Class)</small>
                                </div>
                            </div>

                            <form id="quickForm" action="{{ url('feesGroup') }}" method="post">
                                @csrf
                                <input type="hidden" name="mode" id="form_mode" value="semester">

                                <!-- MODE 1: ALL SEMESTERS (BATCH) -->
                                <div id="section_semester">
                                    <!-- Notice shown when NO course is selected -->
                                    <div id="course_required_notice" class="p-3 text-center border rounded bg-white my-2" style="border: 1.5px dashed #007bff !important; border-radius: 6px;">
                                        <i class="fa fa-graduation-cap text-primary" style="font-size: 28px;"></i>
                                        <h6 class="font-weight-bold text-dark mt-2 mb-1" style="font-size: 13px;">Please Select a Course Above</h6>
                                        <p class="text-muted mb-0" style="font-size: 11px;">
                                            Select a Course from the dropdown above (e.g. <strong>BA, BSc, B.Ed</strong>) to auto-detect semesters and generate class-wise fee structure.
                                        </p>
                                    </div>

                                    <!-- Controls shown when a course is selected -->
                                    <div id="section_semester_controls" style="display: none;">
                                        <div class="form-group mb-2">
                                            <label class="font-weight-bold text-dark mb-1" style="font-size:11.5px;">Fee Head Base Name*</label>
                                            <div class="mb-1 d-flex flex-wrap" style="gap: 3px;">
                                                <span class="quick-preset-btn" onclick="setSemBase('Tuition Fee', 'academic')"><i class="fa fa-graduation-cap text-primary mr-1"></i>Tuition Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Semester Exam Fee', 'examination')"><i class="fa fa-pencil text-purple mr-1"></i>Exam Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Practical / Lab Fee', 'practical')"><i class="fa fa-flask text-teal mr-1"></i>Practical Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Library & Book Bank Fee', 'facility')"><i class="fa fa-book text-warning mr-1"></i>Library Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Campus Development Fee', 'academic')">Development Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Computer Lab & IT Fee', 'facility')">Computer / IT Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Sports & Activity Fee', 'facility')">Sports & Activity</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Hostel & Mess Fee', 'hostel_transport')">Hostel Fee</span>
                                                <span class="quick-preset-btn" onclick="setSemBase('Transportation / Bus Fee', 'hostel_transport')">Transport Fee</span>
                                            </div>
                                            <input type="text" class="form-control form-control-sm font-weight-bold" id="sem_base_name" value="Tuition Fee" placeholder="e.g. Tuition Fee, Exam Fee" oninput="updateSemPreview()">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold mb-1" style="font-size:11px;">Total Semesters / Years*</label>
                                                    <select class="form-control form-control-sm font-weight-bold" id="sem_count" onchange="updateSemPreview()">
                                                        <option value="1">1 Semester</option>
                                                        <option value="2">2 Semesters (1 Year)</option>
                                                        <option value="3">3 Semesters</option>
                                                        <option value="4">4 Semesters (2 Years)</option>
                                                        <option value="5">5 Semesters</option>
                                                        <option value="6" selected>6 Semesters (3 Years)</option>
                                                        <option value="7">7 Semesters</option>
                                                        <option value="8">8 Semesters (4 Years)</option>
                                                        <option value="9">9 Semesters</option>
                                                        <option value="10">10 Semesters (5 Years)</option>
                                                        <option value="12">12 Semesters (6 Years)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="font-weight-bold mb-1" style="font-size:11px;">Category</label>
                                                    <select class="form-control form-control-sm" name="sem_group_type" id="sem_group_type">
                                                        <option value="academic" selected>Academic (Tuition / University)</option>
                                                        <option value="examination">Examination</option>
                                                        <option value="practical">Laboratory & Practical</option>
                                                        <option value="facility">Campus Facility & Library</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fees Master Assignment Settings (Default Amount) -->
                                        <div class="fee-master-amount-box mb-2">
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold text-dark mb-1" style="font-size:11.5px;">
                                                    <i class="fa fa-inr text-success"></i> Default Amount (Applied to all semesters):
                                                </label>
                                                <input type="text" class="form-control form-control-sm font-weight-bold text-success" id="batch_common_amount" placeholder="e.g. 15000" value="15000" oninput="syncCommonAmount(this.value)" onkeypress="javascript:return isNumber(event)">
                                            </div>
                                        </div>

                                        <!-- Due Date Schedule Generator Box -->
                                        <div class="card p-2 mb-2" style="background: #f0f7ff; border: 1px solid #b8daff; border-radius: 4px;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="font-weight-bold text-primary" style="font-size: 11px;">
                                                    <i class="fa fa-calendar-check-o text-primary"></i> Auto Due Date Schedule
                                                </span>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 font-weight-bold" style="font-size: 10px;" onclick="applyDueDateSchedule()">
                                                    <i class="fa fa-magic"></i> Auto-Fill Dates
                                                </button>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-6 pr-1">
                                                    <div class="form-group mb-1">
                                                        <label class="mb-0 text-muted" style="font-size: 10px; font-weight: 600;">Start Date (Sem 1)</label>
                                                        <input type="date" class="form-control form-control-sm" id="schedule_start_date" value="{{ date('Y-m-10') }}" onchange="applyDueDateSchedule()" style="font-size: 11px; height: 26px; padding: 2px 4px;">
                                                    </div>
                                                </div>
                                                <div class="col-6 pl-1">
                                                    <div class="form-group mb-1">
                                                        <label class="mb-0 text-muted" style="font-size: 10px; font-weight: 600;">Interval / Gap</label>
                                                        <select class="form-control form-control-sm" id="schedule_interval" onchange="applyDueDateSchedule()" style="font-size: 10.5px; height: 26px; padding: 2px 4px;">
                                                            <option value="6" selected>Every 6 Months (Semester)</option>
                                                            <option value="1">Every 1 Month (Monthly)</option>
                                                            <option value="2">Every 2 Months (Bi-Monthly)</option>
                                                            <option value="3">Every 3 Months (Quarterly)</option>
                                                            <option value="4">Every 4 Months (Tri-Annual)</option>
                                                            <option value="12">Every 12 Months (Yearly)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6 pr-1">
                                                    <div class="form-group mb-0">
                                                        <label class="mb-0 text-muted" style="font-size: 10px; font-weight: 600;">Due Day of Month</label>
                                                        <select class="form-control form-control-sm" id="schedule_due_day" onchange="applyDueDateSchedule()" style="font-size: 10.5px; height: 26px; padding: 2px 4px;">
                                                            <option value="same" selected>Same Day as Start Date</option>
                                                            <option value="1">1st of Month</option>
                                                            <option value="5">5th of Month</option>
                                                            <option value="10">10th of Month</option>
                                                            <option value="15">15th of Month</option>
                                                            <option value="20">20th of Month</option>
                                                            <option value="25">25th of Month</option>
                                                            <option value="last">Last Day of Month</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6 pl-1">
                                                    <div class="form-group mb-0">
                                                        <label class="mb-0 text-muted" style="font-size: 10px; font-weight: 600;">Quick Actions</label>
                                                        <div class="btn-group btn-group-sm d-flex" role="group">
                                                            <button type="button" class="btn btn-outline-secondary btn-xs w-50" style="font-size:9.5px; height: 26px; padding: 1px 3px;" onclick="clearDueDates()" title="Clear all due dates">
                                                                <i class="fa fa-times text-danger"></i> Clear
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-xs w-50" style="font-size:9.5px; height: 26px; padding: 1px 3px;" onclick="applyDueDateSchedule()" title="Calculate & Fill">
                                                                <i class="fa fa-refresh"></i> Apply
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-1" style="font-size: 9px; line-height: 1.1;">
                                                <i class="fa fa-info-circle text-info"></i> Auto-generates due dates across semesters based on selected interval.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODE 2: SINGLE CLASS ASSIGNMENT -->
                                <div id="section_single_class" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold mb-1" style="font-size:11px;">Select Class / Semester*</label>
                                        <select class="form-control form-control-sm select2 font-weight-bold" name="single_class_type_id" id="single_class_type_id" onchange="updateSingleClassPreview()">
                                            <option value="">-- Select Class / Semester --</option>
                                            @if(!empty($classType))
                                                @foreach($classType as $cl)
                                                    <option value="{{ $cl->id }}" data-course-id="{{ $cl->course_id }}">{{ $cl->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark mb-1" style="font-size:11.5px;">Fee Head Name*</label>
                                        <div class="mb-1 d-flex flex-wrap" style="gap: 3px;">
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Tuition Fee', 'academic')"><i class="fa fa-graduation-cap text-primary mr-1"></i>Tuition Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Admission Fee', 'admission')">Admission Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Semester Exam Fee', 'examination')"><i class="fa fa-pencil text-purple mr-1"></i>Exam Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('University Exam Fee', 'examination')">University Exam</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Practical / Lab Fee', 'practical')"><i class="fa fa-flask text-teal mr-1"></i>Practical Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Library Fee', 'facility')"><i class="fa fa-book text-warning mr-1"></i>Library Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Caution Money (Refundable)', 'refundable')"><i class="fa fa-undo text-danger mr-1"></i>Caution Money</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Degree / Convocation Fee', 'examination')">Degree Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Hostel & Mess Fee', 'hostel_transport')">Hostel Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Transportation Fee', 'hostel_transport')">Transport Fee</span>
                                            <span class="quick-preset-btn" onclick="setSingleClassName('Campus Development Fee', 'academic')">Development Fee</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm font-weight-bold" id="single_class_fee_name" name="single_class_fee_name" placeholder="e.g. Tuition Fee, Admission Fee" oninput="updateSingleClassPreview()">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold mb-1" style="font-size:10.5px;">Amount (₹)*</label>
                                                <input type="text" class="form-control form-control-sm font-weight-bold text-success" name="single_amount" id="single_class_amount" placeholder="Amount (₹)" oninput="updateSingleClassPreview()" onkeypress="javascript:return isNumber(event)">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold mb-1" style="font-size:10.5px;">Due Date</label>
                                                <input type="date" class="form-control form-control-sm" name="single_due_date" id="single_class_due_date">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold mb-1" style="font-size:11px;">Category</label>
                                        <select class="form-control form-control-sm" name="single_class_group_type" id="single_class_group_type">
                                            <option value="academic">Academic (Tuition / University)</option>
                                            <option value="admission">Admission & Registration</option>
                                            <option value="examination">Examination</option>
                                            <option value="practical">Laboratory & Practical</option>
                                            <option value="facility">Campus Facility & Library</option>
                                            <option value="refundable">Refundable Deposit (Caution Money)</option>
                                            <option value="hostel_transport">Hostel & Transport</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- MODE 3: SINGLE FEE HEAD ONLY -->
                                <div id="section_single_head" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold text-dark mb-1 d-flex justify-content-between align-items-center" style="font-size:11.5px;">
                                            <span>Fee Head Name*</span>
                                            <span class="text-muted" style="font-size: 10px; font-weight: normal;"><i class="fa fa-mouse-pointer"></i> Click quick tag to auto-fill</span>
                                        </label>
                                        
                                        <!-- Quick Presets for College Fee Heads -->
                                        <div class="mb-2 p-1 border rounded bg-white">
                                            <div class="d-flex flex-wrap" style="gap: 3px;">
                                                <!-- Admission & Registration -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Admission Fee', 'admission', 'no')"><i class="fa fa-tag text-success mr-1"></i>Admission Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Registration / Form Fee', 'admission', 'no')">Registration Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Enrollment & Eligibility Fee', 'admission', 'no')">Enrollment Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Prospectus & Application Fee', 'admission', 'no')">Prospectus Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('ID Card & Uniform Fee', 'admission', 'no')">ID Card & Uniform</span>

                                                <!-- Academic & Tuition -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Tuition Fee', 'academic', 'no')"><i class="fa fa-graduation-cap text-primary mr-1"></i>Tuition Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Campus Development Fee', 'academic', 'no')">Development Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Training & Placement (TPO) Fee', 'academic', 'no')">Placement & Training</span>

                                                <!-- Examination & Degrees -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Semester Exam Fee', 'examination', 'no')"><i class="fa fa-pencil text-purple mr-1"></i>Semester Exam Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('University / Board Exam Fee', 'examination', 'no')">University Exam</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Practical Exam Fee', 'examination', 'no')">Practical Exam</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Backlog / ATKT Exam Fee', 'examination', 'no')">Backlog / ATKT Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Degree & Convocation Fee', 'examination', 'no')">Degree & Convocation</span>

                                                <!-- Labs, Library & Facilities -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Laboratory / Practical Fee', 'practical', 'no')"><i class="fa fa-flask text-teal mr-1"></i>Lab / Practical Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Library & Book Bank Fee', 'facility', 'no')"><i class="fa fa-book text-warning mr-1"></i>Library Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Computer Lab & IT Fee', 'facility', 'no')">Computer & IT Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Sports & Gymnasium Fee', 'facility', 'no')">Sports & Gym Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Cultural & Annual Activity Fee', 'facility', 'no')">Cultural / Fest Fee</span>

                                                <!-- Hostel & Transport -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Hostel & Mess Fee', 'hostel_transport', 'no')"><i class="fa fa-bed text-orange mr-1"></i>Hostel & Mess Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Transportation / Bus Fee', 'hostel_transport', 'no')"><i class="fa fa-bus text-orange mr-1"></i>Bus / Transport Fee</span>

                                                <!-- Refundable Security Deposits -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Caution Money (Refundable)', 'refundable', 'yes')"><i class="fa fa-undo text-danger mr-1"></i>Caution Money (Refundable)</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Library Security Deposit', 'refundable', 'yes')"><i class="fa fa-undo text-danger mr-1"></i>Library Security Deposit</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Hostel Security Deposit', 'refundable', 'yes')"><i class="fa fa-undo text-danger mr-1"></i>Hostel Security Deposit</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Laboratory Security Deposit', 'refundable', 'yes')"><i class="fa fa-undo text-danger mr-1"></i>Lab Security Deposit</span>

                                                <!-- Welfare & Misc -->
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Student Welfare & Insurance Fee', 'other', 'no')">Student Welfare / Insurance</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Alumni Association Fee', 'other', 'no')">Alumni Fee</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Transfer / Migration Certificate Fee', 'other', 'no')">TC & Migration</span>
                                                <span class="quick-preset-btn" onclick="setHeadOnly('Late Payment Fine / Penalty', 'other', 'no')">Late Fine / Penalty</span>
                                            </div>
                                        </div>
                                        
                                        <input type="text" class="form-control form-control-sm font-weight-bold" id="head_only_name" name="head_only_name" placeholder="e.g. Admission Fee, Semester Exam Fee, Caution Money" oninput="updateHeadOnlyPreview()">
                                    </div>

                                    <div class="form-group mb-2">
                                        <label class="font-weight-bold mb-1 text-dark" style="font-size:11.5px;">Category*</label>
                                        <select class="form-control form-control-sm font-weight-bold" name="head_only_group_type" id="head_only_group_type">
                                            <option value="admission">Admission & Registration</option>
                                            <option value="refundable">Refundable Deposit (Caution Money)</option>
                                            <option value="academic">Academic (Tuition / University)</option>
                                            <option value="examination">Examination</option>
                                            <option value="practical">Laboratory & Practical</option>
                                            <option value="facility">Campus Facility & Library</option>
                                            <option value="hostel_transport">Hostel & Transport</option>
                                            <option value="other">Other / Miscellaneous</option>
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group mb-2 p-2 border rounded bg-white">
                                                <input type="checkbox" id="head_only_refund" onchange="updateHeadOnlyRefund(this)">
                                                <label for="head_only_refund" class="font-weight-normal mb-0 pointer" style="font-size:11px;">Refundable Fee</label>
                                                <input type="hidden" id="fees_refund" name="fees_refund" value="no">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-2 p-2 border rounded bg-white">
                                                <input type="checkbox" id="head_only_partial" onchange="updateHeadOnlyPartial(this)">
                                                <label for="head_only_partial" class="font-weight-normal mb-0 pointer" style="font-size:11px;">Partial (50%)</label>
                                                <input type="hidden" id="fees_partial" name="fees_partial" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Batch Inputs for Form Submission -->
                                <div id="batch_inputs_container"></div>

                                <!-- Live Clean Preview Box (High Contrast & Editable) -->
                                <div class="preview-box-container mt-2 mb-2" id="preview_box_container" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center preview-header">
                                        <span><i class="fa fa-pencil-square-o text-primary"></i> <span id="preview_title">Fee Heads & Structure Preview:</span></span>
                                        <span class="badge badge-primary px-2" id="preview_count_badge">6 Semesters</span>
                                    </div>
                                    <div id="preview_box">
                                        <!-- dynamic badges/rows -->
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold py-2 shadow-sm" id="submit_btn">
                                    <i class="fa fa-check-circle"></i> Save Fee Structure & Heads
                                </button>
                            </form>
                        </div>
                    </div>          
                </div>
                
                <!-- Right Side: Unified Course Fee Structures & Fee Heads Master -->
                <div class="{{($getPermission->add == 1) ? 'col-md-7 pl-0' : 'col-md-12 pl-0'}}">
                    <div class="card card-outline card-orange ml-1">
                        <div class="card-header bg-primary py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 6px;">
                            <h3 class="card-title font-weight-bold mb-0" style="font-size:14px;">
                                <i class="fa fa-cubes"></i> &nbsp;<span id="right_header_title">Course Fee Structures & Heads</span>
                            </h3>

                            <!-- View Mode Switcher Tabs -->
                            <div class="btn-group btn-group-sm bg-white p-1 rounded shadow-sm" role="group" id="right_tab_switcher">
                                <button type="button" class="btn btn-sm btn-primary font-weight-bold active" id="btn_tab_structures" onclick="switchRightTab('structures')" style="font-size: 11px; padding: 3px 10px;">
                                    <i class="fa fa-th-large mr-1"></i> Course Fee Structures
                                </button>
                                <button type="button" class="btn btn-sm btn-light font-weight-bold text-dark" id="btn_tab_no_class" onclick="switchRightTab('no_class_heads')" style="font-size: 11px; padding: 3px 10px;">
                                    <i class="fa fa-tags mr-1 text-primary"></i> Fee Heads Master
                                    <span class="badge badge-primary ml-1" id="total_no_class_heads_badge">{{ count($feeHeadsList) }}</span>
                                </button>
                            </div>
                        </div>  
                        
                        <div class="card-body p-2">
                                    @php
                                        $activeSessionId = $search['session_id'] ?? Session::get('session_id');
                                        if ($activeSessionId === 'all') {
                                            $activeSessionId = Session::get('session_id');
                                        }

                                        // Group all fees_master rows by class_type_id
                                        $feesMasterByClass = [];
                                        $totalAmountConfigured = 0;
                                        $distinctHeads = [];
                                        $coursesWithFees = [];
                                        $configuredClassIds = [];

                                        if (!empty($allFeesMasterRows)) {
                                            foreach ($allFeesMasterRows as $row) {
                                                $cId = $row->class_type_id;
                                                if (!isset($feesMasterByClass[$cId])) {
                                                    $feesMasterByClass[$cId] = [];
                                                }
                                                $feesMasterByClass[$cId][] = $row;
                                                $totalAmountConfigured += (float)($row->amount ?? 0);
                                                if (!empty($row->fees_group_id)) {
                                                    $distinctHeads[$row->fees_group_id] = true;
                                                }
                                                if (!empty($row->ClassTypes->course_id)) {
                                                    $coursesWithFees[$row->ClassTypes->course_id] = true;
                                                }
                                                $configuredClassIds[$cId] = true;
                                            }
                                        }

                                        $totalConfiguredCourses = count($coursesWithFees);
                                        $totalConfiguredClasses = count($configuredClassIds);
                                        $totalDistinctHeads = count($distinctHeads);
                                    @endphp

                                    <!-- TAB 1: CLASS-WISE FEES MASTER STRUCTURE -->
                                    <div id="container_class_structures">
                                        <!-- Top KPI Summary Cards -->
                                        <div class="row mb-2">
                                            <div class="col-6 col-md-3 mb-1">
                                                <div class="kpi-chip-card">
                                                    <div class="kpi-chip-icon icon-courses">
                                                        <i class="fa fa-graduation-cap"></i>
                                                    </div>
                                                    <div>
                                                        <div class="kpi-chip-val text-primary">{{ $totalConfiguredCourses }} <small class="text-muted" style="font-size:10px;">/ {{ count($courses ?? []) }}</small></div>
                                                        <div class="kpi-chip-label">Configured Courses</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3 mb-1">
                                                <div class="kpi-chip-card">
                                                    <div class="kpi-chip-icon icon-classes">
                                                        <i class="fa fa-book"></i>
                                                    </div>
                                                    <div>
                                                        <div class="kpi-chip-val text-info">{{ $totalConfiguredClasses }} <small class="text-muted" style="font-size:10px;">Semesters</small></div>
                                                        <div class="kpi-chip-label">Active Classes</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3 mb-1">
                                                <div class="kpi-chip-card">
                                                    <div class="kpi-chip-icon icon-pool">
                                                        <i class="fa fa-inr"></i>
                                                    </div>
                                                    <div>
                                                        <div class="kpi-chip-val text-success">₹{{ number_format($totalAmountConfigured) }}</div>
                                                        <div class="kpi-chip-label">Total Fee Pool</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3 mb-1">
                                                <div class="kpi-chip-card">
                                                    <div class="kpi-chip-icon icon-heads">
                                                        <i class="fa fa-tags"></i>
                                                    </div>
                                                    <div>
                                                        <div class="kpi-chip-val text-warning">{{ $totalDistinctHeads }} <small class="text-muted" style="font-size:10px;">Heads</small></div>
                                                        <div class="kpi-chip-label">Fee Heads Used</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quick Course Filter Pills Bar -->
                                        <div class="quick-filters-box">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="font-weight-bold mb-0">
                                                    <i class="fa fa-filter"></i> Quick Course Filters:
                                                </label>
                                                <small>Click course to filter instantly</small>
                                            </div>
                                            <div class="course-filter-wrapper">
                                                <button type="button" class="course-scroll-btn" onclick="scrollCourseFilters(-220)" title="Scroll Left">
                                                    <i class="fa fa-chevron-left"></i>
                                                </button>
                                                <div class="course-filter-scroll" id="course_pill_list">
                                                    <button type="button" class="course-pill-btn active" data-course-id="all" onclick="filterByCourse('all', this)">
                                                        <i class="fa fa-globe"></i> All Courses <span class="badge-counter">{{ $totalConfiguredCourses }}</span>
                                                    </button>
                                                    @if(!empty($courses))
                                                        @foreach($courses as $c)
                                                            @php
                                                                $cClasses = !empty($allClassType) ? $allClassType->where('course_id', $c->id) : collect();
                                                                $cConfiguredCount = 0;
                                                                $cTotalFee = 0;
                                                                foreach($cClasses as $cl) {
                                                                    if (isset($feesMasterByClass[$cl->id])) {
                                                                        $cConfiguredCount++;
                                                                        foreach($feesMasterByClass[$cl->id] as $fRow) {
                                                                            $cTotalFee += (float)($fRow->amount ?? 0);
                                                                        }
                                                                    }
                                                                }
                                                            @endphp
                                                            <button type="button" class="course-pill-btn" data-course-id="{{ $c->id }}" onclick="filterByCourse('{{ $c->id }}', this)">
                                                                <i class="fa fa-graduation-cap"></i> {{ $c->name }}
                                                                <span class="badge-counter">₹{{ number_format($cTotalFee) }}</span>
                                                            </button>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <button type="button" class="course-scroll-btn" onclick="scrollCourseFilters(220)" title="Scroll Right">
                                                    <i class="fa fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Course-wise Cards Layout -->
                                        <div id="course_cards_container" class="mt-2">
                                            @if(!empty($courses))
                                                @php $renderedCourses = 0; @endphp
                                                @foreach($courses as $c)
                                                    @php
                                                        $cClasses = !empty($allClassType) ? $allClassType->where('course_id', $c->id) : collect();
                                                        $cConfiguredCount = 0;
                                                        $cTotalFee = 0;
                                                        $cAllFeesMaster = [];

                                                        foreach($cClasses as $cl) {
                                                            if (isset($feesMasterByClass[$cl->id])) {
                                                                $cConfiguredCount++;
                                                                foreach($feesMasterByClass[$cl->id] as $fRow) {
                                                                    $cAllFeesMaster[] = $fRow;
                                                                    $cTotalFee += (float)($fRow->amount ?? 0);
                                                                }
                                                            }
                                                        }
                                                        $dur = !empty($c->duration) ? (int)$c->duration : 3;
                                                        $totSem = !empty($c->total_semester) && (int)$c->total_semester > 0 ? (int)$c->total_semester : ($dur * 2);
                                                        $renderedCourses++;
                                                    @endphp

                                                    <div class="course-card course-group-item" id="course_card_{{ $c->id }}" data-course-id="{{ $c->id }}" data-course-name="{{ strtolower($c->name) }}">
                                                        <!-- Course Executive Banner -->
                                                        <div class="course-card-header">
                                                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                                                <span class="course-card-title">
                                                                    <i class="fa fa-graduation-cap"></i> {{ $c->name }}
                                                                </span>
                                                                <span class="badge badge-light text-dark font-weight-bold" style="font-size:10px;">
                                                                    {{ ($c->course_type == 'Yearly') ? ($dur . ' Year(s) - Yearly') : ($totSem . ' Semesters') }}
                                                                </span>
                                                                @if(count($cAllFeesMaster) > 0)
                                                                    <span class="badge badge-warning text-dark font-weight-bold" style="font-size:10px;">
                                                                        {{ count($cAllFeesMaster) }} Fee Head{{ count($cAllFeesMaster) == 1 ? '' : 's' }} Configured
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-secondary" style="font-size:10px;">Not Configured</span>
                                                                @endif
                                                            </div>
                                                            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                                                <span class="badge badge-success px-2 py-1 font-weight-bold course-total-fee-badge" data-total="{{ $cTotalFee }}" style="font-size:11.5px;">
                                                                    ₹{{ number_format($cTotalFee) }} Total Course Fee
                                                                </span>
                                                                <button type="button" class="btn btn-xs btn-setup-course font-weight-bold shadow-sm" onclick="selectCourseForSetup('{{ $c->id }}')" style="font-size:10.5px;">
                                                                    <i class="fa fa-pencil-square-o"></i> Update Fees Structure
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Course Body: Fee Heads Breakdown Table -->
                                                        <div class="p-2">
                                                            @if(count($cAllFeesMaster) > 0)
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-striped table-hover mb-0 padding_table" style="font-size: 11.5px;">
                                                                        <thead>
                                                                            <tr>
                                                                                <th width="40px" class="text-center">#</th>
                                                                                <th>Fee Head Name</th>
                                                                                <th width="150px" class="text-center">Category</th>
                                                                                <th width="130px" class="text-center">Due Date</th>
                                                                                <th width="130px" class="text-right">Amount (₹)</th>
                                                                                <th width="65px" class="text-center">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($cAllFeesMaster as $idx => $sRow)
                                                                                @php
                                                                                    $amt = (float)($sRow->amount ?? 0);
                                                                                    $fgName = $sRow->feesGroup->name ?? 'Fee Head';
                                                                                    $clName = $sRow->ClassTypes->name ?? '-';
                                                                                    $fgType = $sRow->feesGroup->group_type ?? 'academic';
                                                                                    $badgeClass = 'badge-academic';
                                                                                    if ($fgType === 'examination') $badgeClass = 'badge-examination';
                                                                                    elseif ($fgType === 'practical') $badgeClass = 'badge-practical';
                                                                                    elseif ($fgType === 'admission') $badgeClass = 'badge-admission';
                                                                                    elseif ($fgType === 'facility') $badgeClass = 'badge-facility';
                                                                                    elseif ($fgType === 'refundable') $badgeClass = 'badge-refundable';
                                                                                    elseif ($fgType === 'hostel_transport') $badgeClass = 'badge-hostel_transport';

                                                                                    $dueDateStr = !empty($sRow->installment_due_date) ? date('d-M-y', strtotime($sRow->installment_due_date)) : '-';
                                                                                    $isFmAssigned = !empty($assignedMap[$sRow->fees_group_id . '_' . $sRow->class_type_id]);
                                                                                    $isFmCollected = !empty($collectedMap[$sRow->fees_group_id]);
                                                                                @endphp
                                                                                <tr id="fm_row_{{ $sRow->id }}" class="fees-master-row" data-amount="{{ $amt }}">
                                                                                    <td class="text-center align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                                                                    <td class="align-middle font-weight-bold text-dark">
                                                                                        {{ $fgName }}
                                                                                        @if(strtolower(trim($sRow->feesGroup->fees_refund ?? '')) === 'yes')
                                                                                            <span class="badge badge-refundable ml-1" style="font-size: 8.5px; padding: 2px 4px;">Refundable</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        <span class="badge {{ $badgeClass }}" style="font-size: 10px; padding: 3px 6px;">{{ ucfirst(str_replace('_', ' & ', $fgType)) }}</span>
                                                                                    </td>
                                                                                    <td class="text-center align-middle text-muted">
                                                                                        @if($dueDateStr !== '-')
                                                                                            <span class="badge badge-light border text-info font-weight-bold" style="font-size: 10px; padding: 2px 6px;">
                                                                                                <i class="fa fa-calendar-check-o"></i> {{ $dueDateStr }}
                                                                                            </span>
                                                                                        @else
                                                                                            <span class="text-muted font-italic">-</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-right align-middle font-weight-bold text-success" style="font-size: 12.5px;">
                                                                                        ₹{{ number_format($amt) }}
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        @if(!$isFmAssigned && !$isFmCollected)
                                                                                            <button type="button" 
                                                                                                    class="btn btn-xs btn-outline-danger" 
                                                                                                    title="Delete {{ $fgName }} from {{ $c->name }}" 
                                                                                                    onclick="confirmDeleteFeesMaster('{{ $sRow->id }}', '{{ addslashes($fgName) }}', '{{ addslashes($c->name) }}')"
                                                                                                    style="font-size: 11px; padding: 2px 7px;">
                                                                                                <i class="fa fa-trash"></i>
                                                                                            </button>
                                                                                        @else
                                                                                            <span class="badge badge-light border text-muted" style="font-size: 9px; padding: 2px 4px;" title="{{ $isFmCollected ? 'Fees Collected (Locked)' : 'Assigned to Students (Locked)' }}">
                                                                                                <i class="fa fa-lock text-secondary"></i>
                                                                                            </span>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                        <tfoot>
                                                                            <tr class="bg-light font-weight-bold">
                                                                                <td colspan="4" class="text-right align-middle" style="font-size: 12px;">Total Course Fee ({{ count($cAllFeesMaster) }} Fee Heads):</td>
                                                                                <td class="text-right align-middle text-success course-card-foot-total" style="font-size: 12.5px;">₹{{ number_format($cTotalFee) }}</td>
                                                                                <td></td>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="empty-course-box">
                                                                    <div class="empty-msg">
                                                                        <i class="fa fa-info-circle"></i> No fee structure configured for <strong>{{ $c->name }}</strong> in this session.
                                                                    </div>
                                                                    <button type="button" class="btn btn-configure-course shadow-sm" onclick="selectCourseForSetup('{{ $c->id }}')">
                                                                        <i class="fa fa-plus-circle"></i> Click Here to Configure {{ $c->name }}
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-light text-center border">
                                                    No courses available to display.
                                                </div>
                                            @endif

                                            <div id="no_course_search_results" class="p-4 text-center border rounded bg-white mt-2" style="display:none; border: 1.5px dashed #cbd5e1 !important;">
                                                <i class="fa fa-graduation-cap text-muted mb-2" style="font-size:24px;"></i>
                                                <h6 class="font-weight-bold text-dark mb-1" style="font-size:13px;">No Fee Structures Found</h6>
                                                <p class="text-muted mb-2" style="font-size:11px;">Try selecting "All Courses" from the filter bar above.</p>
                                                <button type="button" class="btn btn-xs btn-primary font-weight-bold" onclick="$('.course-pill-btn[data-course-id=\'all\']').click()">Show All Courses</button>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt-2">
                                            <p class="text-muted mb-0" style="font-size:11px;">
                                                <i class="fa fa-info-circle text-info"></i> <b>Tip:</b> Creating fee heads here with Amount & Due Date automatically configures Fees Master for student admissions & collection.
                                            </p>
                                        </div>
                                    </div>

                                     <!-- TAB 2: ALL FEE HEADS MASTER TABULAR LIST -->
                                    <div id="container_no_class_heads" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded border" style="background: #f8fafc;">
                                            <div class="d-flex align-items-center">
                                                <span id="no_class_count_badge" class="badge badge-primary mr-2" style="font-size: 11px; padding: 4px 7px;">
                                                    <i class="fa fa-tags"></i> {{ count($feeHeadsList) }}
                                                </span>
                                                <div>
                                                    <span class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                                        Fee Heads Master (All Created Heads)
                                                    </span>
                                                    <span class="text-muted d-block" style="font-size: 10.5px;">All created fee heads. You can assign any head to multiple courses or specific classes anytime.</span>
                                                </div>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-xs btn-primary font-weight-bold" onclick="switchMode('single_head')" style="font-size: 11px; padding: 3px 8px;">
                                                    <i class="fa fa-plus-circle mr-1"></i> Add Fee Head
                                                </button>
                                            </div>
                                        </div>

                                        @if(count($feeHeadsList) > 0)
                                            <div class="table-responsive border rounded" style="background: #ffffff;">
                                                <table class="table table-bordered table-striped table-hover mb-0 padding_table" style="font-size: 11.5px;">
                                                    <thead>
                                                        <tr>
                                                            <th width="40px" class="text-center">#</th>
                                                            <th>Fee Head Name</th>
                                                            <th width="150px" class="text-center">Category</th>
                                                            <th width="120px" class="text-center">Properties</th>
                                                            <th width="140px" class="text-center">Mapped Status</th>
                                                            <th width="120px" class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($feeHeadsList as $idx => $nh)
                                                            @php
                                                                $nhCat = strtolower(trim($nh->group_type ?? 'other'));
                                                                if ($nhCat === 'registration') $nhCat = 'admission';
                                                                $nhCatClass = 'badge-other';
                                                                if ($nhCat === 'academic') $nhCatClass = 'badge-academic';
                                                                elseif ($nhCat === 'admission') $nhCatClass = 'badge-admission';
                                                                elseif ($nhCat === 'examination') $nhCatClass = 'badge-examination';
                                                                elseif ($nhCat === 'practical') $nhCatClass = 'badge-practical';
                                                                elseif ($nhCat === 'facility') $nhCatClass = 'badge-facility';
                                                                elseif ($nhCat === 'refundable') $nhCatClass = 'badge-refundable';
                                                                elseif ($nhCat === 'hostel_transport') $nhCatClass = 'badge-hostel_transport';

                                                                $isRef = strtolower(trim($nh->fees_refund ?? '')) === 'yes';
                                                                $isPart = ($nh->fees_partial ?? 0) == 1;
                                                                $usageCount = $headUsageCount[$nh->id] ?? 0;
                                                            @endphp
                                                            <tr id="no_class_row_{{ $nh->id }}">
                                                                <td class="text-center align-middle font-weight-bold text-muted">{{ $idx + 1 }}</td>
                                                                <td class="align-middle font-weight-bold text-dark">
                                                                    {{ $nh->name }}
                                                                    @if($isRef)
                                                                        <span class="badge badge-refundable ml-1" style="font-size: 9px; padding: 2px 5px;" title="Refundable Deposit">
                                                                            <i class="fa fa-undo"></i> Refundable
                                                                        </span>
                                                                    @endif
                                                                    @if($isPart)
                                                                        <span class="badge badge-academic ml-1" style="font-size: 9px; padding: 2px 5px;" title="Partial Payment (50%) Allowed">
                                                                            <i class="fa fa-adjust"></i> 50% Partial
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center align-middle">
                                                                    <span class="badge {{ $nhCatClass }}" style="font-size: 10.5px; padding: 3px 8px;">{{ ucfirst(str_replace('_', ' & ', $nhCat)) }}</span>
                                                                </td>
                                                                <td class="text-center align-middle">
                                                                    @if($isRef)
                                                                        <span class="badge badge-refundable" style="font-size: 10px; padding: 3px 6px;"><i class="fa fa-undo mr-1"></i> Refundable</span>
                                                                    @elseif($isPart)
                                                                        <span class="badge badge-academic" style="font-size: 10px; padding: 3px 6px;"><i class="fa fa-adjust mr-1"></i> 50% Partial</span>
                                                                    @else
                                                                        <span class="badge badge-other" style="font-size: 10px; padding: 3px 6px;">Standard</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center align-middle">
                                                                    @if($usageCount > 0)
                                                                        <span class="badge badge-success" style="font-size: 10px; padding: 3px 7px;" title="Mapped in Fees Master">
                                                                            <i class="fa fa-check-circle mr-1"></i> Mapped ({{ $usageCount }} Class{{ $usageCount == 1 ? '' : 'es' }})
                                                                        </span>
                                                                    @else
                                                                        <span class="badge badge-light border text-muted" style="font-size: 10px; padding: 3px 7px;">
                                                                            <i class="fa fa-clock-o mr-1 text-secondary"></i> Unassigned
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center align-middle text-nowrap">
                                                                    <button type="button" 
                                                                            class="btn btn-xs btn-outline-primary btn-edit-fee-head" 
                                                                            data-id="{{ $nh->id }}" 
                                                                            data-name="{{ $nh->name }}" 
                                                                            data-category="{{ $nhCat }}" 
                                                                            data-refund="{{ $nh->fees_refund ?? 'no' }}" 
                                                                            data-partial="{{ $nh->fees_partial ?? 0 }}"
                                                                            title="Edit Fee Head" 
                                                                            style="font-size: 11px; padding: 2px 7px;">
                                                                        <i class="fa fa-pencil"></i>
                                                                    </button>
                                                                    <button type="button" 
                                                                            class="btn btn-xs text-white btn-assign-fee-head" 
                                                                            data-id="{{ $nh->id }}" 
                                                                            data-name="{{ addslashes($nh->name) }}"
                                                                            title="Assign this Fee Head to Courses or Classes" 
                                                                            style="font-size: 11px; padding: 2px 8px; background-color: #002c54; border-color: #002c54;">
                                                                        <i class="fa fa-share-alt mr-1"></i> Assign Head
                                                                    </button>
                                                                    @php
                                                                        $isNhAssigned = !empty($assignedMap[$nh->id]);
                                                                        $isNhCollected = !empty($collectedMap[$nh->id]);
                                                                    @endphp
                                                                    @if(!$isNhAssigned && !$isNhCollected)
                                                                        <button type="button" 
                                                                           class="btn btn-xs btn-outline-danger btn-delete-fee-head" 
                                                                           data-id="{{ $nh->id }}" 
                                                                           data-name="{{ addslashes($nh->name) }}"
                                                                           title="Delete Fee Head" 
                                                                           style="font-size: 11px; padding: 2px 7px;">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    @else
                                                                        <span class="badge badge-light border text-muted ml-1" style="font-size: 10px; padding: 3px 5px;" title="{{ $isNhCollected ? 'Fees Collected (Locked)' : 'Assigned to Students (Locked)' }}">
                                                                            <i class="fa fa-lock text-secondary"></i>
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="p-4 text-center text-muted border rounded" style="font-size: 12px; background: #ffffff;">
                                                <i class="fa fa-info-circle text-info fa-2x mb-2 d-block"></i>
                                                No fee heads found. Click "Add Fee Head" to create one.
                                            </div>
                                        @endif
                                    </div>
                        </div>
                    </div>          
                </div>
            </div>  
        </div>
    </section>
</div>

@php
    $currentSessionId = Session::get('session_id');
    $currentSessionName = '';
    $otherSessions = [];
    if (!empty($getSession)) {
        foreach ($getSession as $s) {
            $sName = ($s->from_year ?? '') . ' - ' . ($s->to_year ?? '');
            if ($s->id == $currentSessionId) {
                $currentSessionName = $sName;
            } else {
                $otherSessions[] = $s;
            }
        }
    }
    if (empty($currentSessionName)) {
        $currentSessionName = 'Current Session (ID: ' . $currentSessionId . ')';
    }
@endphp

<!-- Clone / Copy Fees Structure from Previous Financial Year Modal -->
<div class="modal fade fees-unified-page" id="cloneFeesModal" tabindex="-1" role="dialog" aria-labelledby="cloneFeesModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px; overflow: hidden;">
            <div class="modal-header py-2.5 px-3 d-flex justify-content-between align-items-center" style="background-color: #002c54 !important; border-bottom: 2px solid #ff7b00;">
                <h5 class="modal-title font-weight-bold d-flex align-items-center mb-0" id="cloneFeesModalLabel" style="font-size: 15px; color: #ffffff !important;">
                    <i class="fa fa-clone mr-2 text-warning"></i> Clone Previous Financial Year Fee Setup
                </h5>
                <button type="button" class="close text-white btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; text-shadow: none; outline: none; cursor: pointer;">
                    <span aria-hidden="true" style="font-size: 22px; color: #ffffff;">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-3" style="background-color: #f8fafc;">
                <!-- Step / Flow Indicator Ribbon -->
                <div class="card mb-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #002c54 0%, #00172d 100%); color: #ffffff; border-radius: 8px;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-5 text-center text-md-left mb-2 mb-md-0">
                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mb-1" style="font-size: 11px;">SOURCE SESSION</span>
                                <div class="font-weight-bold" style="font-size: 14px; color: #facc15;" id="clone_source_title_display">Select Previous Session</div>
                                <small style="color: #cbd5e1; font-size: 11px;">Copy structure, heads & amounts from</small>
                            </div>
                            <div class="col-md-2 text-center mb-2 mb-md-0">
                                <div class="d-inline-flex justify-content-center align-items-center rounded-circle" style="width: 36px; height: 36px; background: rgba(255,255,255,0.15);">
                                    <i class="fa fa-arrow-right text-warning"></i>
                                </div>
                            </div>
                            <div class="col-md-5 text-center text-md-right">
                                <span class="badge badge-success font-weight-bold px-2 py-1 mb-1" style="font-size: 11px;">TARGET ACTIVE SESSION</span>
                                <div class="font-weight-bold text-white" style="font-size: 14px;">{{ $currentSessionName }}</div>
                                <small style="color: #cbd5e1; font-size: 11px;">Paste as new fee setup in active year</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Selection Input -->
                <div class="form-group mb-3 bg-white p-3 border rounded shadow-xs">
                    <label class="font-weight-bold text-dark mb-1" style="font-size: 12.5px;">
                        <i class="fa fa-calendar-check-o text-primary mr-1"></i> Select Previous Financial Year to Clone From: <span class="text-danger">*</span>
                    </label>
                    <select class="form-control font-weight-bold" id="clone_source_session_id" style="height: 38px; font-size: 13px;">
                        <option value="">-- Select Source Session / Financial Year --</option>
                        @if(!empty($otherSessions))
                            @foreach($otherSessions as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->from_year ?? '' }} - {{ $s->to_year ?? '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Dynamic Preview Card for Selected Source Session -->
                <div id="clone_preview_container" style="display: none;">
                    <div class="card mb-3 border shadow-xs" style="background-color: #002c54 !important; border: 1.5px solid #001a33 !important; border-radius: 8px;">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color: #001f3b !important; border-bottom: 1px solid #103a61 !important;">
                            <span class="font-weight-bold" style="font-size: 12.5px; color: #ffffff !important;">
                                <i class="fa fa-check-circle mr-1" style="color: #4ade80 !important;"></i> Source Session Structure Summary (<span id="clone_source_session_name" style="color: #38bdf8 !important; font-weight: 800;">-</span>):
                            </span>
                            <span class="badge px-2.5 py-1 font-weight-bold" id="clone_heads_count_badge" style="background-color: #0284c7 !important; color: #ffffff !important; font-size: 11.5px;">0 Fee Heads</span>
                        </div>
                        <div class="card-body p-3" style="background-color: #002c54 !important;">
                            <div class="row mb-3 text-center">
                                <div class="col-3 p-1">
                                    <div class="p-2 border rounded shadow-xs" style="background-color: #00172d !important; border-color: #1e3a5f !important;">
                                        <small class="d-block font-weight-bold" style="font-size: 10px; text-transform: uppercase; color: #cbd5e1 !important; letter-spacing: 0.5px;">Courses</small>
                                        <span class="font-weight-bold" id="clone_stat_courses" style="font-size: 16px; color: #38bdf8 !important;">0</span>
                                    </div>
                                </div>
                                <div class="col-3 p-1">
                                    <div class="p-2 border rounded shadow-xs" style="background-color: #00172d !important; border-color: #1e3a5f !important;">
                                        <small class="d-block font-weight-bold" style="font-size: 10px; text-transform: uppercase; color: #cbd5e1 !important; letter-spacing: 0.5px;">Classes</small>
                                        <span class="font-weight-bold" id="clone_stat_classes" style="font-size: 16px; color: #7dd3fc !important;">0</span>
                                    </div>
                                </div>
                                <div class="col-3 p-1">
                                    <div class="p-2 border rounded shadow-xs" style="background-color: #00172d !important; border-color: #1e3a5f !important;">
                                        <small class="d-block font-weight-bold" style="font-size: 10px; text-transform: uppercase; color: #cbd5e1 !important; letter-spacing: 0.5px;">Fee Heads</small>
                                        <span class="font-weight-bold" id="clone_stat_heads" style="font-size: 16px; color: #facc15 !important;">0</span>
                                    </div>
                                </div>
                                <div class="col-3 p-1">
                                    <div class="p-2 border rounded shadow-xs" style="background-color: #00172d !important; border-color: #1e3a5f !important;">
                                        <small class="d-block font-weight-bold" style="font-size: 10px; text-transform: uppercase; color: #cbd5e1 !important; letter-spacing: 0.5px;">Total Pool</small>
                                        <span class="font-weight-bold" id="clone_stat_amount" style="font-size: 16px; color: #4ade80 !important;">₹0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- List of Fee Heads -->
                            <div class="mb-1">
                                <label class="font-weight-bold mb-1.5 small d-block" style="font-size: 11.5px; color: #cbd5e1 !important;">Fee Heads that will be cloned:</label>
                                <div id="clone_heads_chips_box" class="d-flex flex-wrap" style="gap: 5px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="clone_loading_indicator" class="text-center py-4" style="display: none;">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted font-weight-bold" style="font-size: 12px;">Fetching Previous Year Fee Setup Data...</p>
                </div>

                <!-- Instructions & Guarantees Alert -->
                <div class="p-3 mb-0 shadow-sm" style="background-color: #002c54 !important; border: 1.5px solid #0284c7 !important; color: #ffffff !important; border-radius: 8px;">
                    <strong class="d-block mb-1.5" style="color: #38bdf8 !important; font-size: 12.5px;"><i class="fa fa-info-circle mr-1"></i> What happens when you clone?</strong>
                    <ul class="pl-3 mb-0" style="line-height: 1.5; color: #f1f5f9 !important; font-size: 12px;">
                        <li>All Fee Heads (Tuition, Exam, Caution Money, etc.) and categories are copied into the active session <b style="color: #facc15 !important;">{{ $currentSessionName }}</b>.</li>
                        <li>All Course & Semester fee structures, class amounts, and installment due dates are mapped and updated for the new academic session.</li>
                        <li>Existing fee collections and receipts from previous years remain 100% intact and untouched.</li>
                    </ul>
                </div>
            </div>
            
            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm px-3 btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fa fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3 shadow-sm" id="btn_confirm_clone_fees" disabled>
                    <i class="fa fa-check-circle mr-1"></i> Confirm & Clone Fee Setup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Fees Group Modal (Theme-Based Custom Centered Modal) -->
<div class="modal fade fees-unified-page" id="Modal_id" tabindex="-1" role="dialog" aria-labelledby="deleteFeesGroupLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger py-2 text-white">
                <h5 class="modal-title text-white font-weight-bold" id="deleteFeesGroupLabel" style="font-size: 13.5px;">
                    <i class="fa fa-trash"></i> Delete Fee Head
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="delete_fees_group_form" action="{{ url('feesGroupDelete') }}" method="post">
                @csrf
                <div class="modal-body text-center p-3">
                    <i class="fa fa-exclamation-triangle text-danger mb-2" style="font-size: 32px;"></i>
                    <h6 class="font-weight-bold text-dark mb-1" id="delete_fg_title">Delete Fee Head?</h6>
                    <p class="text-muted mb-0" id="delete_fg_desc" style="font-size: 11.5px;">
                        Are you sure you want to delete this fee head? This action cannot be undone.
                    </p>
                    <input type="hidden" name="delete_id" id="delete_id">
                </div>
                <div class="modal-footer py-2 justify-content-center bg-light">
                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" style="font-size: 11.5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold" style="font-size: 11.5px;">
                        <i class="fa fa-trash"></i> Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Fees Master Head Confirmation Modal -->
<div class="modal fade fees-unified-page" id="delete_fees_master_modal" tabindex="-1" role="dialog" aria-labelledby="deleteFeesMasterLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger py-2 text-white">
                <h5 class="modal-title text-white font-weight-bold" id="deleteFeesMasterLabel" style="font-size: 13.5px;">
                    <i class="fa fa-trash"></i> Remove Fee Head
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="delete_fees_master_form" action="{{ url('feesMasterDelete') }}" method="post">
                @csrf
                <div class="modal-body text-center p-3">
                    <i class="fa fa-exclamation-triangle text-danger mb-2" style="font-size: 30px;"></i>
                    <h6 class="font-weight-bold text-dark mb-1" id="delete_fm_title">Remove Fee Head from Class?</h6>
                    <p class="text-muted mb-0" id="delete_fm_desc" style="font-size: 11.5px;">
                        Are you sure you want to remove this fee head from this semester's fee structure?
                    </p>
                    <input type="hidden" name="delete_id" id="delete_fees_master_id">
                </div>
                <div class="modal-footer py-2 justify-content-center bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal" style="font-size: 11.5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold" style="font-size: 11.5px;">
                        <i class="fa fa-trash"></i> Yes, Remove
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Section for Unified Fees Setup -->

<!-- 0. Reset Form Confirmation Modal -->
<div class="modal fade fees-unified-page" id="reset_form_confirm_modal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title font-weight-bold" style="font-size: 13.5px;" id="resetModalLabel">
                    <i class="fa fa-exclamation-triangle"></i> Reset Form Confirmation
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3">
                <i class="fa fa-refresh text-danger mb-2" style="font-size: 32px;"></i>
                <h6 class="font-weight-bold text-dark mb-1" style="font-size: 13px;">Form Data Will Be Cleared!</h6>
                <p class="text-muted mb-0" style="font-size: 11px;">
                    Are you sure you want to reset? Any unsaved fee head entries and course selections in the form will be lost.
                </p>
            </div>
            <div class="modal-footer py-2 justify-content-center bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal" style="font-size: 11.5px;">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger btn-sm font-weight-bold" onclick="executeFormReset()" style="font-size: 11.5px;">
                    <i class="fa fa-check"></i> Yes, Reset Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 1. Quick In-Place Edit Fee Head Modal -->
<div class="modal fade fees-unified-page" id="edit_fee_head_modal" tabindex="-1" aria-labelledby="editHeadModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title font-weight-bold" style="font-size: 13.5px;" id="editHeadModalLabel">
                    <i class="fa fa-pencil-square-o"></i> Edit Fee Head Details
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit_fee_head_form" action="" method="post">
                @csrf
                <div class="modal-body p-3">
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11.5px;">Fee Head Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm font-weight-bold" name="name" id="modal_edit_head_name" placeholder="Enter Fee Head Name" required>
                    </div>
                    
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-dark mb-1" style="font-size: 11.5px;">Category / Fee Type</label>
                        <select class="form-control form-control-sm font-weight-bold" name="group_type" id="modal_edit_head_group_type">
                            <option value="academic">Academic (Tuition / University)</option>
                            <option value="admission">Admission & Registration</option>
                            <option value="refundable">Refundable Deposit (Caution Money)</option>
                            <option value="examination">Examination</option>
                            <option value="practical">Laboratory & Practical</option>
                            <option value="facility">Campus Facility & Library</option>
                            <option value="hostel_transport">Hostel & Transport</option>
                            <option value="other">Other / Miscellaneous</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-2 p-2 border rounded bg-white">
                                <input type="checkbox" id="modal_edit_head_refund" onchange="document.getElementById('modal_edit_fees_refund').value = this.checked ? 'yes' : 'no'">
                                <label for="modal_edit_head_refund" class="font-weight-normal mb-0 pointer" style="font-size:11px;">Refundable Deposit</label>
                                <input type="hidden" id="modal_edit_fees_refund" name="fees_refund" value="no">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-2 p-2 border rounded bg-white">
                                <input type="checkbox" id="modal_edit_head_partial" onchange="document.getElementById('modal_edit_fees_partial').value = this.checked ? '1' : '0'">
                                <label for="modal_edit_head_partial" class="font-weight-normal mb-0 pointer" style="font-size:11px;">Partial (50%)</label>
                                <input type="hidden" id="modal_edit_fees_partial" name="fees_partial" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal" style="font-size: 11.5px;">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold" style="font-size: 11.5px;">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Fee Head Modal (Course Wise or Class Wise) -->
<div class="modal fade fees-unified-page" id="assign_fee_head_modal" tabindex="-1" aria-labelledby="assignFeeHeadModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" style="max-width: 850px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 text-white" style="background: linear-gradient(135deg, #002c54 0%, #004b8d 100%);">
                <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 13.5px;" id="assignFeeHeadModalLabel">
                    <i class="fa fa-share-alt mr-1"></i> Assign Fee Head: <span id="assign_fee_head_title" class="text-warning font-weight-bold"></span>
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assign_fee_head_form" action="{{ url('assignFeeHeadToClasses') }}" method="post">
                @csrf
                <input type="hidden" name="fees_group_id" id="assign_fees_group_id" value="">
                <input type="hidden" name="assign_mode" id="assign_head_mode_input" value="course">
                
                <div class="modal-body p-3 bg-light">
                    <!-- Mode Switcher Nav / Pills -->
                    <div class="d-flex justify-content-center mb-3">
                        <div class="btn-group p-1 bg-white border rounded shadow-sm" style="gap: 5px;">
                            <button type="button" class="btn btn-sm btn-primary font-weight-bold px-3 py-1.5 assign-mode-pill" id="mode_pill_course" onclick="switchAssignHeadMode('course')" style="border-radius: 6px; font-size: 12px; background-color: #002c54; border-color: #002c54;">
                                <i class="fa fa-graduation-cap mr-1"></i> Assign to Course (Entire Course)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold px-3 py-1.5 assign-mode-pill" id="mode_pill_class" onclick="switchAssignHeadMode('class')" style="border-radius: 6px; font-size: 12px;">
                                <i class="fa fa-sitemap mr-1"></i> Assign to Class / Semester
                            </button>
                        </div>
                    </div>

                    <!-- ===================== PANEL 1: COURSE WISE ===================== -->
                    <div id="assign_panel_course" class="assign-mode-panel">
                        <div class="alert alert-info py-2 px-3 mb-2 font-weight-normal" style="font-size: 11.5px; border-left: 4px solid #002c54;">
                            <i class="fa fa-info-circle mr-1 text-primary"></i> 
                            Select courses to assign this fee head course-level. To assign to specific classes/semesters individually, switch to the <strong>"Assign to Class / Semester"</strong> tab.
                        </div>

                        <!-- Course Quick Apply Bar -->
                        <div class="card border mb-2 shadow-none bg-white">
                            <div class="card-body p-2">
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-dark mb-1 d-block"><i class="fa fa-inr text-success"></i> Common (₹):</label>
                                        <input type="number" step="0.01" min="0" id="assign_course_common_amount" class="form-control form-control-sm font-weight-bold" placeholder="e.g. 4000">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-primary mb-1 d-block"><i class="fa fa-male"></i> Boys (₹):</label>
                                        <input type="number" step="0.01" min="0" id="assign_course_common_boys_amount" class="form-control form-control-sm font-weight-bold" placeholder="Boys">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-danger mb-1 d-block"><i class="fa fa-female"></i> Girls (₹):</label>
                                        <input type="number" step="0.01" min="0" id="assign_course_common_girls_amount" class="form-control form-control-sm font-weight-bold" placeholder="Girls">
                                    </div>
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-dark mb-1 d-block"><i class="fa fa-calendar text-info"></i> Due Date:</label>
                                        <input type="date" id="assign_course_common_due_date" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2 mt-auto">
                                        <button type="button" class="btn btn-sm btn-info btn-block font-weight-bold" onclick="applyCourseCommonAssignValues()" title="Apply common values to selected courses" style="padding: 5px 6px; font-size: 11px;">
                                            <i class="fa fa-bolt"></i> Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Courses Table -->
                        <div class="card border shadow-none bg-white mb-0">
                            <div class="card-header py-2 bg-white d-flex justify-content-between align-items-center border-bottom">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="assign_course_master_check" onchange="toggleAllCourseCheckboxes(this)">
                                    <label class="custom-control-label font-weight-bold text-dark small" for="assign_course_master_check" style="cursor: pointer;">Select All Courses</label>
                                </div>
                                <div>
                                    <span class="badge badge-primary px-2 py-1" id="assign_course_selected_counter" style="font-size: 11px; background-color: #002c54;">0 Courses Selected</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 290px; overflow-y: auto;">
                                <table class="table table-bordered table-hover table-sm mb-0" id="assign_modal_courses_table" style="font-size: 12px;">
                                    <thead style="position: sticky; top: 0; z-index: 2; background-color: #f1f5f9; color: #1e293b;">
                                        <tr>
                                            <th class="text-center align-middle" style="width: 40px;">#</th>
                                            <th class="align-middle">Course Name</th>
                                            <th class="align-middle" style="width: 140px;">General (₹) <span class="text-danger">*</span></th>
                                            <th class="align-middle" style="width: 120px;"><i class="fa fa-male text-primary mr-1"></i>Boys (₹)</th>
                                            <th class="align-middle" style="width: 120px;"><i class="fa fa-female text-danger mr-1"></i>Girls (₹)</th>
                                            <th class="align-middle" style="width: 140px;">Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($courses))
                                            @foreach($courses as $c)
                                                <tr class="assign-course-row" id="assign_course_row_{{ $c->id }}">
                                                    <td class="text-center align-middle">
                                                        <div class="custom-control custom-checkbox d-inline-block">
                                                            <input type="checkbox" name="course_id[]" value="{{ $c->id }}" class="custom-control-input assign-course-checkbox" id="chk_course_{{ $c->id }}" onchange="onCourseRowCheck(this)">
                                                            <label class="custom-control-label" for="chk_course_{{ $c->id }}" style="cursor: pointer;"></label>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <label for="chk_course_{{ $c->id }}" class="mb-0 font-weight-bold text-dark pointer" style="cursor: pointer;">
                                                            {{ $c->name }}
                                                        </label>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text px-1 text-muted" style="font-size: 10px;">₹</span>
                                                            </div>
                                                            <input type="number" step="0.01" min="0" name="course_amount[{{ $c->id }}]" id="assign_course_amt_{{ $c->id }}" class="form-control form-control-sm assign-course-amount-input font-weight-bold text-right" placeholder="0.00" disabled required style="background: #e9ecef;">
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="number" step="0.01" min="0" name="course_boys_amount[{{ $c->id }}]" id="assign_course_boys_amt_{{ $c->id }}" class="form-control form-control-sm assign-course-boys-input font-weight-bold text-right text-primary" placeholder="Boys" disabled style="background: #e9ecef;">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="number" step="0.01" min="0" name="course_girls_amount[{{ $c->id }}]" id="assign_course_girls_amt_{{ $c->id }}" class="form-control form-control-sm assign-course-girls-input font-weight-bold text-right text-danger" placeholder="Girls" disabled style="background: #e9ecef;">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="date" name="course_due_date[{{ $c->id }}]" id="assign_course_due_{{ $c->id }}" class="form-control form-control-sm assign-course-due-input" disabled style="background: #e9ecef;">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ===================== PANEL 2: CLASS / SEMESTER WISE ===================== -->
                    <div id="assign_panel_class" class="assign-mode-panel" style="display: none;">
                        <!-- Quick Filter & Apply Bar -->
                        <div class="card border mb-2 shadow-none bg-white">
                            <div class="card-body p-2">
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-dark mb-1 d-block"><i class="fa fa-filter text-primary"></i> Course:</label>
                                        <select id="assign_modal_course_filter" class="form-control form-control-sm" onchange="filterModalAssignClasses(this.value)">
                                            <option value="">-- All Courses --</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-dark mb-1 d-block"><i class="fa fa-inr text-success"></i> Common (₹):</label>
                                        <input type="number" step="0.01" min="0" id="assign_modal_common_amount" class="form-control form-control-sm font-weight-bold" placeholder="General">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-primary mb-1 d-block"><i class="fa fa-male"></i> Boys (₹):</label>
                                        <input type="number" step="0.01" min="0" id="assign_modal_common_boys_amount" class="form-control form-control-sm font-weight-bold" placeholder="Boys">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-danger mb-1 d-block"><i class="fa fa-female"></i> Girls (₹):</label>
                                        <input type="number" step="0.01" min="0" id="assign_modal_common_girls_amount" class="form-control form-control-sm font-weight-bold" placeholder="Girls">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <label class="small font-weight-bold text-dark mb-1 d-block"><i class="fa fa-calendar text-info"></i> Due Date:</label>
                                        <input type="date" id="assign_modal_common_due_date" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-1 mt-auto p-0">
                                        <button type="button" class="btn btn-sm btn-info btn-block font-weight-bold" onclick="applyModalCommonAssignValues()" title="Apply common values to selected classes" style="padding: 5px 4px; font-size: 11px;">
                                            <i class="fa fa-bolt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Classes Selection Table -->
                        <div class="card border shadow-none bg-white mb-0">
                            <div class="card-header py-2 bg-white d-flex justify-content-between align-items-center border-bottom">
                                <div class="custom-control custom-checkbox mr-3">
                                    <input type="checkbox" class="custom-control-input" id="assign_modal_master_check" onchange="toggleAllModalAssignCheckboxes(this)">
                                    <label class="custom-control-label font-weight-bold text-dark small" for="assign_modal_master_check" style="cursor: pointer;">Select All Visible Classes</label>
                                </div>
                                <div>
                                    <span class="badge badge-primary px-2 py-1" id="assign_selected_counter" style="font-size: 11px; background-color: #002c54;">0 Classes Selected</span>
                                </div>
                            </div>
                            <div class="card-body p-0" style="max-height: 290px; overflow-y: auto;">
                                <table class="table table-bordered table-hover table-sm mb-0" id="assign_modal_classes_table" style="font-size: 12px;">
                                    <thead style="position: sticky; top: 0; z-index: 2; background-color: #f1f5f9; color: #1e293b;">
                                        <tr>
                                            <th class="text-center align-middle" style="width: 40px;">#</th>
                                            <th class="align-middle" style="width: 120px;">Course</th>
                                            <th class="align-middle">Class / Semester</th>
                                            <th class="align-middle" style="width: 130px;">General (₹) <span class="text-danger">*</span></th>
                                            <th class="align-middle" style="width: 110px;"><i class="fa fa-male text-primary mr-1"></i>Boys (₹)</th>
                                            <th class="align-middle" style="width: 110px;"><i class="fa fa-female text-danger mr-1"></i>Girls (₹)</th>
                                            <th class="align-middle" style="width: 130px;">Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($classType))
                                            @foreach($classType as $cl)
                                                <tr class="assign-class-row" id="assign_row_{{ $cl->id }}" data-course-id="{{ $cl->course_id ?? '' }}">
                                                    <td class="text-center align-middle">
                                                        <div class="custom-control custom-checkbox d-inline-block">
                                                            <input type="checkbox" name="class_type_id[]" value="{{ $cl->id }}" class="custom-control-input assign-class-checkbox" id="chk_assign_{{ $cl->id }}" onchange="onModalAssignRowCheck(this)">
                                                            <label class="custom-control-label" for="chk_assign_{{ $cl->id }}" style="cursor: pointer;"></label>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle font-weight-bold text-secondary">
                                                        {{ $cl->course->name ?? 'General' }}
                                                    </td>
                                                    <td class="align-middle">
                                                        <label for="chk_assign_{{ $cl->id }}" class="mb-0 font-weight-bold text-dark pointer" style="cursor: pointer;">
                                                            {{ $cl->name }}
                                                        </label>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text px-1 text-muted" style="font-size: 10px;">₹</span>
                                                            </div>
                                                            <input type="number" step="0.01" min="0" name="amount[{{ $cl->id }}]" id="assign_amt_{{ $cl->id }}" class="form-control form-control-sm assign-amount-input font-weight-bold text-right" placeholder="0.00" disabled required style="background: #e9ecef;">
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="number" step="0.01" min="0" name="class_boys_amount[{{ $cl->id }}]" id="assign_boys_amt_{{ $cl->id }}" class="form-control form-control-sm assign-class-boys-input font-weight-bold text-right text-primary" placeholder="Boys" disabled style="background: #e9ecef;">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="number" step="0.01" min="0" name="class_girls_amount[{{ $cl->id }}]" id="assign_girls_amt_{{ $cl->id }}" class="form-control form-control-sm assign-class-girls-input font-weight-bold text-right text-danger" placeholder="Girls" disabled style="background: #e9ecef;">
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="date" name="due_date[{{ $cl->id }}]" id="assign_due_{{ $cl->id }}" class="form-control form-control-sm assign-due-date-input" disabled style="background: #e9ecef;">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-bs-dismiss="modal" data-dismiss="modal" style="font-size: 11.5px;">
                        <i class="fa fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold" id="btn_save_assign_head" style="font-size: 11.5px; background-color: #002c54; border-color: #002c54;" disabled>
                        <i class="fa fa-save mr-1"></i> Save & Assign Head
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function scrollCourseFilters(offset) {
    var container = document.getElementById('course_pill_list');
    if (container) {
        container.scrollBy({
            left: offset,
            behavior: 'smooth'
        });
    }
}

// Global helper functions for Assign Fee Head Modal (Course & Class modes)
function switchAssignHeadMode(mode) {
    $('#assign_head_mode_input').val(mode);
    if (mode === 'course') {
        $('#mode_pill_course').removeClass('btn-outline-secondary').addClass('btn-primary').css({'background-color': '#002c54', 'border-color': '#002c54', 'color': '#ffffff'});
        $('#mode_pill_class').removeClass('btn-primary').addClass('btn-outline-secondary').css({'background-color': '', 'border-color': '', 'color': ''});
        $('#assign_panel_course').show();
        $('#assign_panel_class').hide();
    } else {
        $('#mode_pill_class').removeClass('btn-outline-secondary').addClass('btn-primary').css({'background-color': '#002c54', 'border-color': '#002c54', 'color': '#ffffff'});
        $('#mode_pill_course').removeClass('btn-primary').addClass('btn-outline-secondary').css({'background-color': '', 'border-color': '', 'color': ''});
        $('#assign_panel_class').show();
        $('#assign_panel_course').hide();
    }
    updateModalAssignSelectionState();
}

function onCourseRowCheck(checkbox) {
    var tr = $(checkbox).closest('tr');
    var amtInput = tr.find('.assign-course-amount-input');
    var boysInput = tr.find('.assign-course-boys-input');
    var girlsInput = tr.find('.assign-course-girls-input');
    var dueInput = tr.find('.assign-course-due-input');

    if (checkbox.checked) {
        tr.addClass('table-primary');
        amtInput.prop('disabled', false).css('background', '#ffffff');
        boysInput.prop('disabled', false).css('background', '#ffffff');
        girlsInput.prop('disabled', false).css('background', '#ffffff');
        dueInput.prop('disabled', false).css('background', '#ffffff');
        var commonAmt = $('#assign_course_common_amount').val();
        var commonBoys = $('#assign_course_common_boys_amount').val();
        var commonGirls = $('#assign_course_common_girls_amount').val();
        var commonDue = $('#assign_course_common_due_date').val();
        if (commonAmt && !amtInput.val()) {
            amtInput.val(commonAmt);
        }
        if (commonBoys && !boysInput.val()) {
            boysInput.val(commonBoys);
        }
        if (commonGirls && !girlsInput.val()) {
            girlsInput.val(commonGirls);
        }
        if (commonDue && !dueInput.val()) {
            dueInput.val(commonDue);
        }
    } else {
        tr.removeClass('table-primary');
        amtInput.prop('disabled', true).css('background', '#e9ecef');
        boysInput.prop('disabled', true).css('background', '#e9ecef');
        girlsInput.prop('disabled', true).css('background', '#e9ecef');
        dueInput.prop('disabled', true).css('background', '#e9ecef');
    }
    updateModalAssignSelectionState();
}

function toggleAllCourseCheckboxes(masterCheck) {
    var isChecked = masterCheck.checked;
    $('#assign_modal_courses_table tbody tr.assign-course-row').each(function() {
        var chk = $(this).find('.assign-course-checkbox');
        chk.prop('checked', isChecked);
        var amtInput = $(this).find('.assign-course-amount-input');
        var boysInput = $(this).find('.assign-course-boys-input');
        var girlsInput = $(this).find('.assign-course-girls-input');
        var dueInput = $(this).find('.assign-course-due-input');

        if (isChecked) {
            $(this).addClass('table-primary');
            amtInput.prop('disabled', false).css('background', '#ffffff');
            boysInput.prop('disabled', false).css('background', '#ffffff');
            girlsInput.prop('disabled', false).css('background', '#ffffff');
            dueInput.prop('disabled', false).css('background', '#ffffff');
            var commonAmt = $('#assign_course_common_amount').val();
            var commonBoys = $('#assign_course_common_boys_amount').val();
            var commonGirls = $('#assign_course_common_girls_amount').val();
            var commonDue = $('#assign_course_common_due_date').val();
            if (commonAmt && !amtInput.val()) amtInput.val(commonAmt);
            if (commonBoys && !boysInput.val()) boysInput.val(commonBoys);
            if (commonGirls && !girlsInput.val()) girlsInput.val(commonGirls);
            if (commonDue && !dueInput.val()) dueInput.val(commonDue);
        } else {
            $(this).removeClass('table-primary');
            amtInput.prop('disabled', true).css('background', '#e9ecef');
            boysInput.prop('disabled', true).css('background', '#e9ecef');
            girlsInput.prop('disabled', true).css('background', '#e9ecef');
            dueInput.prop('disabled', true).css('background', '#e9ecef');
        }
    });
    updateModalAssignSelectionState();
}

function applyCourseCommonAssignValues() {
    var commonAmt = $('#assign_course_common_amount').val();
    var commonBoys = $('#assign_course_common_boys_amount').val();
    var commonGirls = $('#assign_course_common_girls_amount').val();
    var commonDue = $('#assign_course_common_due_date').val();

    if (!commonAmt && !commonBoys && !commonGirls && !commonDue) {
        toastr.warning('Please enter a common amount, boys amount, girls amount or due date first.');
        return;
    }

    var checkedCount = 0;
    $('#assign_modal_courses_table tbody tr.assign-course-row').each(function() {
        var chk = $(this).find('.assign-course-checkbox');
        if (chk.is(':checked')) {
            checkedCount++;
            if (commonAmt) $(this).find('.assign-course-amount-input').val(commonAmt);
            if (commonBoys) $(this).find('.assign-course-boys-input').val(commonBoys);
            if (commonGirls) $(this).find('.assign-course-girls-input').val(commonGirls);
            if (commonDue) $(this).find('.assign-course-due-input').val(commonDue);
        }
    });

    if (checkedCount === 0) {
        toastr.info('Please select at least one course checkbox to apply values, or click "Select All".');
    } else {
        toastr.success('Applied to ' + checkedCount + ' selected course(s).');
    }
}

function filterModalAssignClasses(courseId) {
    $('#assign_modal_classes_table tbody tr.assign-class-row').each(function() {
        var rowCourseId = $(this).attr('data-course-id') || '';
        if (!courseId || rowCourseId === courseId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    updateModalAssignSelectionState();
}

function onModalAssignRowCheck(checkbox) {
    var tr = $(checkbox).closest('tr');
    var amtInput = tr.find('.assign-amount-input');
    var boysInput = tr.find('.assign-class-boys-input');
    var girlsInput = tr.find('.assign-class-girls-input');
    var dueInput = tr.find('.assign-due-date-input');

    if (checkbox.checked) {
        tr.addClass('table-primary');
        amtInput.prop('disabled', false).css('background', '#ffffff');
        boysInput.prop('disabled', false).css('background', '#ffffff');
        girlsInput.prop('disabled', false).css('background', '#ffffff');
        dueInput.prop('disabled', false).css('background', '#ffffff');
        var commonAmt = $('#assign_modal_common_amount').val();
        var commonBoys = $('#assign_modal_common_boys_amount').val();
        var commonGirls = $('#assign_modal_common_girls_amount').val();
        var commonDue = $('#assign_modal_common_due_date').val();
        if (commonAmt && !amtInput.val()) {
            amtInput.val(commonAmt);
        }
        if (commonBoys && !boysInput.val()) {
            boysInput.val(commonBoys);
        }
        if (commonGirls && !girlsInput.val()) {
            girlsInput.val(commonGirls);
        }
        if (commonDue && !dueInput.val()) {
            dueInput.val(commonDue);
        }
    } else {
        tr.removeClass('table-primary');
        amtInput.prop('disabled', true).css('background', '#e9ecef');
        boysInput.prop('disabled', true).css('background', '#e9ecef');
        girlsInput.prop('disabled', true).css('background', '#e9ecef');
        dueInput.prop('disabled', true).css('background', '#e9ecef');
    }
    updateModalAssignSelectionState();
}

function toggleAllModalAssignCheckboxes(masterCheck) {
    var isChecked = masterCheck.checked;
    $('#assign_modal_classes_table tbody tr.assign-class-row:visible').each(function() {
        var chk = $(this).find('.assign-class-checkbox');
        chk.prop('checked', isChecked);
        var amtInput = $(this).find('.assign-amount-input');
        var boysInput = $(this).find('.assign-class-boys-input');
        var girlsInput = $(this).find('.assign-class-girls-input');
        var dueInput = $(this).find('.assign-due-date-input');

        if (isChecked) {
            $(this).addClass('table-primary');
            amtInput.prop('disabled', false).css('background', '#ffffff');
            boysInput.prop('disabled', false).css('background', '#ffffff');
            girlsInput.prop('disabled', false).css('background', '#ffffff');
            dueInput.prop('disabled', false).css('background', '#ffffff');
            var commonAmt = $('#assign_modal_common_amount').val();
            var commonBoys = $('#assign_modal_common_boys_amount').val();
            var commonGirls = $('#assign_modal_common_girls_amount').val();
            var commonDue = $('#assign_modal_common_due_date').val();
            if (commonAmt && !amtInput.val()) amtInput.val(commonAmt);
            if (commonBoys && !boysInput.val()) boysInput.val(commonBoys);
            if (commonGirls && !girlsInput.val()) girlsInput.val(commonGirls);
            if (commonDue && !dueInput.val()) dueInput.val(commonDue);
        } else {
            $(this).removeClass('table-primary');
            amtInput.prop('disabled', true).css('background', '#e9ecef');
            boysInput.prop('disabled', true).css('background', '#e9ecef');
            girlsInput.prop('disabled', true).css('background', '#e9ecef');
            dueInput.prop('disabled', true).css('background', '#e9ecef');
        }
    });
    updateModalAssignSelectionState();
}

function applyModalCommonAssignValues() {
    var commonAmt = $('#assign_modal_common_amount').val();
    var commonBoys = $('#assign_modal_common_boys_amount').val();
    var commonGirls = $('#assign_modal_common_girls_amount').val();
    var commonDue = $('#assign_modal_common_due_date').val();

    if (!commonAmt && !commonBoys && !commonGirls && !commonDue) {
        toastr.warning('Please enter a common amount, boys amount, girls amount or due date first.');
        return;
    }

    var checkedCount = 0;
    $('#assign_modal_classes_table tbody tr.assign-class-row:visible').each(function() {
        var chk = $(this).find('.assign-class-checkbox');
        if (chk.is(':checked')) {
            checkedCount++;
            if (commonAmt) $(this).find('.assign-amount-input').val(commonAmt);
            if (commonBoys) $(this).find('.assign-class-boys-input').val(commonBoys);
            if (commonGirls) $(this).find('.assign-class-girls-input').val(commonGirls);
            if (commonDue) $(this).find('.assign-due-date-input').val(commonDue);
        }
    });

    if (checkedCount === 0) {
        toastr.info('Please select at least one class checkbox to apply values, or click "Select All".');
    } else {
        toastr.success('Applied to ' + checkedCount + ' selected class(es).');
    }
}

function updateModalAssignSelectionState() {
    var mode = $('#assign_head_mode_input').val() || 'course';

    if (mode === 'course') {
        var totalCourses = $('#assign_modal_courses_table tbody tr.assign-course-row').length;
        var checkedCourses = $('#assign_modal_courses_table tbody tr.assign-course-row .assign-course-checkbox:checked').length;
        $('#assign_course_selected_counter').text(checkedCourses + ' Course' + (checkedCourses === 1 ? '' : 's') + ' Selected');

        if (checkedCourses > 0) {
            $('#btn_save_assign_head').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Save & Assign to ' + checkedCourses + ' Selected Course' + (checkedCourses === 1 ? '' : 's'));
        } else {
            $('#btn_save_assign_head').prop('disabled', true).html('<i class="fa fa-save mr-1"></i> Save & Assign Head');
        }

        var masterCourse = $('#assign_course_master_check');
        if (totalCourses > 0 && checkedCourses === totalCourses) {
            masterCourse.prop('checked', true).prop('indeterminate', false);
        } else if (checkedCourses > 0 && checkedCourses < totalCourses) {
            masterCourse.prop('checked', false).prop('indeterminate', true);
        } else {
            masterCourse.prop('checked', false).prop('indeterminate', false);
        }
    } else {
        var visibleRows = $('#assign_modal_classes_table tbody tr.assign-class-row:visible');
        var totalVisible = visibleRows.length;
        var totalChecked = $('#assign_modal_classes_table tbody tr.assign-class-row .assign-class-checkbox:checked').length;
        $('#assign_selected_counter').text(totalChecked + ' Class' + (totalChecked === 1 ? '' : 'es') + ' Selected');

        if (totalChecked > 0) {
            $('#btn_save_assign_head').prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Save & Assign to ' + totalChecked + ' Selected Class' + (totalChecked === 1 ? '' : 'es'));
        } else {
            $('#btn_save_assign_head').prop('disabled', true).html('<i class="fa fa-save mr-1"></i> Save & Assign Head');
        }

        var visibleChecked = visibleRows.find('.assign-class-checkbox:checked').length;
        var master = $('#assign_modal_master_check');
        if (totalVisible > 0 && visibleChecked === totalVisible) {
            master.prop('checked', true).prop('indeterminate', false);
        } else if (visibleChecked > 0 && visibleChecked < totalVisible) {
            master.prop('checked', false).prop('indeterminate', true);
        } else {
            master.prop('checked', false).prop('indeterminate', false);
        }
    }
}

window.switchAssignHeadMode = switchAssignHeadMode;
window.onCourseRowCheck = onCourseRowCheck;
window.toggleAllCourseCheckboxes = toggleAllCourseCheckboxes;
window.applyCourseCommonAssignValues = applyCourseCommonAssignValues;
window.filterModalAssignClasses = filterModalAssignClasses;
window.onModalAssignRowCheck = onModalAssignRowCheck;
window.toggleAllModalAssignCheckboxes = toggleAllModalAssignCheckboxes;
window.applyModalCommonAssignValues = applyModalCommonAssignValues;
window.updateModalAssignSelectionState = updateModalAssignSelectionState;

var currentMode = 'semester';
var currentCourseClasses = [];
var existingHeadAssignments = {!! json_encode($existingHeadAssignments ?? []) !!};
var courseFeeStructures = @json($courseFeeStructures ?? []);
var editingCourseId = null;

var allDefaultClasses = [
    @if(!empty($allClassType))
        @foreach($allClassType as $type)
            { id: "{{ $type->id }}", name: "{{ $type->name }}" },
        @endforeach
    @endif
];

function onCourseSelected(selectElem, editExisting) {
    var opt = selectElem.options[selectElem.selectedIndex];
    var infoDiv = document.getElementById('course_detected_info');
    var infoText = document.getElementById('course_detected_text');
    var noticeDiv = document.getElementById('course_required_notice');
    var controlsDiv = document.getElementById('section_semester_controls');
    var previewContainer = document.getElementById('preview_box_container');
    var submitBtn = document.getElementById('submit_btn');

    if (!opt || !opt.value) {
        infoDiv.style.display = 'none';
        currentCourseClasses = [];
        
        if (currentMode === 'semester') {
            if (noticeDiv) noticeDiv.style.display = 'block';
            if (controlsDiv) controlsDiv.style.display = 'none';
            if (previewContainer) previewContainer.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.className = 'btn btn-secondary btn-sm btn-block font-weight-bold py-2 disabled';
            submitBtn.innerHTML = '<i class="fa fa-hand-o-up"></i> Please Select a Course to Continue';
        }
        
        var singleClassSelect = $('#single_class_type_id');
        singleClassSelect.empty().append('<option value="">-- Select Class / Semester --</option>');
        @if(!empty($classType))
            @foreach($classType as $cl)
                singleClassSelect.append('<option value="{{ $cl->id }}" data-course-id="{{ $cl->course_id }}">{{ $cl->name }}</option>');
            @endforeach
        @endif
        return;
    }

    var courseId = opt.value;
    editingCourseId = editExisting ? String(courseId) : null;
    var courseName = opt.getAttribute('data-name');
    var courseType = opt.getAttribute('data-type') || 'semester';
    var duration = parseInt(opt.getAttribute('data-duration')) || 3;
    var totalSemesters = parseInt(opt.getAttribute('data-semesters')) || (duration * 2);

    infoDiv.style.display = 'block';

    if (courseType === 'yearly') {
        infoText.innerText = 'Detected: ' + courseName + ' (Yearly - ' + duration + ' Years)';
        document.getElementById('sem_count').value = duration;
    } else {
        infoText.innerText = 'Detected: ' + courseName + ' (Semester - ' + totalSemesters + ' Semesters)';
        document.getElementById('sem_count').value = totalSemesters;
    }

    // Fetch classes of selected course
    $.ajax({
        url: "{{ url('getClassesByCourse') }}",
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            course_id: courseId
        },
        dataType: 'json',
        success: function(res) {
            currentCourseClasses = res || [];
            
            // Populate single class dropdown with only classes of this course
            var singleClassSelect = $('#single_class_type_id');
            singleClassSelect.empty().append('<option value="">-- Select Class / Semester --</option>');
            $.each(currentCourseClasses, function(idx, item) {
                singleClassSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
            });

            if (currentMode === 'semester') {
                if (noticeDiv) noticeDiv.style.display = 'none';
                if (controlsDiv) controlsDiv.style.display = 'block';
                if (previewContainer) previewContainer.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.className = 'btn btn-primary btn-sm btn-block font-weight-bold py-2 shadow-sm';
                updateSemPreview();
            } else if (currentMode === 'single_class') {
                updateSingleClassPreview();
            }
        },
        error: function() {
            currentCourseClasses = [];
            if (currentMode === 'semester') {
                if (noticeDiv) noticeDiv.style.display = 'block';
                if (controlsDiv) controlsDiv.style.display = 'none';
                if (previewContainer) previewContainer.style.display = 'none';
                submitBtn.disabled = true;
                submitBtn.className = 'btn btn-secondary btn-sm btn-block font-weight-bold py-2 disabled';
                submitBtn.innerHTML = '<i class="fa fa-hand-o-up"></i> Please Select a Course to Continue';
            }
        }
    });
}

function switchMode(mode) {
    currentMode = mode;
    sessionStorage.setItem('fees_group_mode', mode);
    document.getElementById('form_mode').value = mode;

    document.getElementById('btn_mode_semester').classList.remove('active');
    document.getElementById('btn_mode_single_class').classList.remove('active');
    document.getElementById('btn_mode_single_head').classList.remove('active');

    document.getElementById('section_semester').style.display = 'none';
    document.getElementById('section_single_class').style.display = 'none';
    document.getElementById('section_single_head').style.display = 'none';

    var courseBox = document.getElementById('course_selector_box');
    var courseBadge = document.getElementById('course_mode_badge');
    var courseReqStar = document.getElementById('course_req_star');
    var noticeDiv = document.getElementById('course_required_notice');
    var controlsDiv = document.getElementById('section_semester_controls');
    var previewContainer = document.getElementById('preview_box_container');
    var submitBtn = document.getElementById('submit_btn');
    var courseSelected = document.getElementById('course_selector').value !== '';

    if (mode === 'semester') {
        switchRightTab('structures');
        document.getElementById('btn_mode_semester').classList.add('active');
        document.getElementById('section_semester').style.display = 'block';
        courseBox.style.display = 'block';
        courseBadge.innerText = 'Required';
        courseBadge.className = 'badge badge-primary px-2';
        courseReqStar.style.display = 'inline';

        if (courseSelected && currentCourseClasses.length > 0) {
            if (noticeDiv) noticeDiv.style.display = 'none';
            if (controlsDiv) controlsDiv.style.display = 'block';
            if (previewContainer) previewContainer.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.className = 'btn btn-primary btn-sm btn-block font-weight-bold py-2 shadow-sm';
            updateSemPreview();
        } else {
            if (noticeDiv) noticeDiv.style.display = 'block';
            if (controlsDiv) controlsDiv.style.display = 'none';
            if (previewContainer) previewContainer.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.className = 'btn btn-secondary btn-sm btn-block font-weight-bold py-2 disabled';
            submitBtn.innerHTML = '<i class="fa fa-hand-o-up"></i> Please Select a Course to Continue';
        }
    } else if (mode === 'single_class') {
        switchRightTab('structures');
        document.getElementById('btn_mode_single_class').classList.add('active');
        document.getElementById('section_single_class').style.display = 'block';
        courseBox.style.display = 'block';
        courseBadge.innerText = 'Filter Course';
        courseBadge.className = 'badge badge-info px-2';
        courseReqStar.style.display = 'none';
        if (previewContainer) previewContainer.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.className = 'btn btn-primary btn-sm btn-block font-weight-bold py-2 shadow-sm';
        updateSingleClassPreview();
    } else {
        switchRightTab('no_class_heads');
        document.getElementById('btn_mode_single_head').classList.add('active');
        document.getElementById('section_single_head').style.display = 'block';
        courseBox.style.display = 'none';
        if (previewContainer) previewContainer.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.className = 'btn btn-primary btn-sm btn-block font-weight-bold py-2 shadow-sm';
        updateHeadOnlyPreview();
    }
    checkFormHasData();
}

// Right Panel Tab Switcher (Class-wise Fees Master vs No-Class Fee Heads)
function switchRightTab(tab) {
    sessionStorage.setItem('fees_group_right_tab', tab);
    if (tab === 'no_class_heads') {
        $('#btn_tab_no_class').removeClass('btn-light text-dark').addClass('btn-primary active');
        $('#btn_tab_structures').removeClass('btn-primary active').addClass('btn-light text-dark');
        $('#container_class_structures').hide();
        $('#container_no_class_heads').stop(true, true).fadeIn(150);
        $('#right_header_title').text('Fee Heads Master');
    } else {
        $('#btn_tab_structures').removeClass('btn-light text-dark').addClass('btn-primary active');
        $('#btn_tab_no_class').removeClass('btn-primary active').addClass('btn-light text-dark');
        $('#container_no_class_heads').hide();
        $('#container_class_structures').stop(true, true).fadeIn(150);
        $('#right_header_title').text('Class-wise Fees Master');
    }
}

function setSemBase(name, cat) {
    document.getElementById('sem_base_name').value = name;
    if (cat) document.getElementById('sem_group_type').value = cat;
    updateSemPreview();
    checkFormHasData();
}

function setSingleClassName(name, cat) {
    document.getElementById('single_class_fee_name').value = name;
    if (cat) document.getElementById('single_class_group_type').value = cat;
    updateSingleClassPreview();
    checkFormHasData();
}

function setHeadOnly(name, cat, isRefund) {
    document.getElementById('head_only_name').value = name;
    if (cat) document.getElementById('head_only_group_type').value = cat;
    var refCheck = document.getElementById('head_only_refund');
    if (isRefund === 'yes') {
        refCheck.checked = true;
        document.getElementById('fees_refund').value = 'yes';
    } else {
        refCheck.checked = false;
        document.getElementById('fees_refund').value = 'no';
    }
    updateHeadOnlyPreview();
    checkFormHasData();
}

function updateHeadOnlyRefund(checkbox) {
    document.getElementById('fees_refund').value = checkbox.checked ? 'yes' : 'no';
}

function updateHeadOnlyPartial(checkbox) {
    document.getElementById('fees_partial').value = checkbox.checked ? 1 : 0;
}

function syncCommonAmount(val) {
    $('.sem-row-amount').val(val);
}

function applyDueDateSchedule() {
    var startDateInput = document.getElementById('schedule_start_date');
    if (!startDateInput) return;
    var startDateVal = startDateInput.value;
    if (!startDateVal) return;
    
    var intervalMonths = parseInt(document.getElementById('schedule_interval').value) || 6;
    var dueDayRule = document.getElementById('schedule_due_day').value;
    
    var startParts = startDateVal.split('-');
    if (startParts.length < 3) return;
    
    var baseYear = parseInt(startParts[0]);
    var baseMonth = parseInt(startParts[1]) - 1; // 0-indexed month
    var baseDay = parseInt(startParts[2]);

    var activeSequenceIndex = 0;
    var setupRows = document.querySelectorAll('.sem-setup-row');
    setupRows.forEach(function(row) {
        var checkbox = row.querySelector('.sem-row-check');
        var input = row.querySelector('.sem-row-due');
        if (!input || (checkbox && !checkbox.checked)) {
            return;
        }

        // Multiple fee heads may belong to the same semester. Use the actual
        // class/semester position instead of the visual row position so they
        // receive the same due date. Unchecked rows do not consume an interval.
        var classInput = row.querySelector('input[name="class_type_id[]"]');
        var classId = classInput ? String(classInput.value) : '';
        var semesterIndex = currentCourseClasses.findIndex(function(cl) {
            return String(cl.id) === classId;
        });
        if (semesterIndex < 0) {
            semesterIndex = activeSequenceIndex;
        }
        activeSequenceIndex++;

        var targetMonthTotal = baseMonth + (semesterIndex * intervalMonths);
        var targetYear = baseYear + Math.floor(targetMonthTotal / 12);
        var targetMonth = targetMonthTotal % 12;
        
        var targetDay = baseDay;
        if (dueDayRule === 'last') {
            targetDay = new Date(targetYear, targetMonth + 1, 0).getDate();
        } else if (dueDayRule !== 'same') {
            targetDay = parseInt(dueDayRule);
        }
        
        // Ensure day doesn't exceed total days in that month (e.g. Feb 28/29)
        var maxDaysInMonth = new Date(targetYear, targetMonth + 1, 0).getDate();
        if (targetDay > maxDaysInMonth) {
            targetDay = maxDaysInMonth;
        }
        
        var formattedMonth = String(targetMonth + 1).padStart(2, '0');
        var formattedDay = String(targetDay).padStart(2, '0');
        input.value = targetYear + '-' + formattedMonth + '-' + formattedDay;
    });
}

function clearDueDates() {
    var dueInputs = document.querySelectorAll('.sem-row-due');
    dueInputs.forEach(function(input) {
        input.value = '';
    });
}

function escapeSemValue(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function updateSemPreview() {
    var baseName = document.getElementById('sem_base_name').value.trim() || 'Tuition Fee';
    var count = parseInt(document.getElementById('sem_count').value) || 6;
    var commonAmount = document.getElementById('batch_common_amount').value || '15000';
    var selectedCourseId = String(document.getElementById('course_selector').value || '');
    var existingRows = (editingCourseId === selectedCourseId && courseFeeStructures[selectedCourseId])
        ? courseFeeStructures[selectedCourseId]
        : [];

    var html = '';

    if (currentCourseClasses.length > 0) {
        var previewRows = [];
        if (existingRows.length > 0) {
            previewRows = existingRows.map(function(row) {
                return {
                    fees_master_id: row.fees_master_id || row.id || '',
                    fees_group_id: row.fees_group_id || '',
                    class_type_id: row.class_type_id,
                    class_name: row.class_name || '',
                    fee_name: row.fee_name || 'Fee Head',
                    amount: row.amount == null ? 0 : row.amount,
                    due_date: row.due_date || ''
                };
            });

            var amountFrequency = {};
            previewRows.forEach(function(row) {
                var key = String(row.amount);
                amountFrequency[key] = (amountFrequency[key] || 0) + 1;
            });
            var mostCommonAmount = Object.keys(amountFrequency).sort(function(a, b) {
                return amountFrequency[b] - amountFrequency[a];
            })[0];
            if (mostCommonAmount !== undefined) {
                document.getElementById('batch_common_amount').value = mostCommonAmount;
                commonAmount = mostCommonAmount;
            }
        } else {
            var loopCount = Math.min(count, currentCourseClasses.length);
            for (var p = 0; p < loopCount; p++) {
                previewRows.push({
                    fees_master_id: '',
                    fees_group_id: '',
                    class_type_id: currentCourseClasses[p].id,
                    class_name: currentCourseClasses[p].name,
                    fee_name: baseName + ' - Sem ' + (p + 1),
                    amount: commonAmount,
                    due_date: ''
                });
            }
        }

        var loopCount = previewRows.length;
        html += '<div class="table-responsive" style="height: auto; border: 1px solid #c2d4ea; border-radius: 4px;">';
        html += '<table class="table table-sm table-bordered table-striped mb-0 text-dark" style="font-size: 11px; background: #ffffff;" id="sem_setup_table">';
        html += '<thead style="background: #002c54; color: #ffffff; position: sticky; top: 0; z-index: 2;">';
        html += '<tr>';
        html += '<th style="padding: 4px 5px; width: 28px; background: #002c54; color: #ffffff; text-align: center;"><input type="checkbox" id="check_all_sem_rows" checked onchange="toggleAllSemRows(this)" title="Check/Uncheck All"></th>';
        html += '<th style="padding: 4px 6px; width: 22%; background: #002c54; color: #ffffff;">Class / Sem</th>';
        html += '<th style="padding: 4px 6px; width: 28%; background: #002c54; color: #ffffff;">Fee Head Name</th>';
        html += '<th style="padding: 4px 6px; width: 20%; background: #002c54; color: #ffffff;">Amount (₹)</th>';
        html += '<th style="padding: 4px 6px; width: 22%; background: #002c54; color: #ffffff;">Due Date</th>';
        html += '<th style="padding: 4px 4px; width: 28px; background: #002c54; color: #ffffff; text-align: center;"><i class="fa fa-trash text-white"></i></th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        for (var i = 0; i < loopCount; i++) {
            var previewRow = previewRows[i];
            var className = previewRow.class_name;
            if (!className) {
                var matchedClass = currentCourseClasses.find(function(cl) {
                    return String(cl.id) === String(previewRow.class_type_id);
                });
                className = matchedClass ? matchedClass.name : 'Class / Semester';
            }

            html += '<tr class="sem-setup-row" id="sem_row_' + i + '">';
            html += '<td style="vertical-align: middle; padding: 4px 5px; text-align: center;">';
            html += '<input type="checkbox" class="sem-row-check" checked onchange="onSemRowToggle(this)" title="Include this semester in setup">';
            html += '</td>';
            html += '<td style="vertical-align: middle; padding: 4px 6px;">';
            html += '<strong class="text-primary">' + escapeSemValue(className) + '</strong>';
            html += '<input type="hidden" name="class_type_id[]" value="' + escapeSemValue(previewRow.class_type_id) + '" class="sem-input-field">';
            html += '<input type="hidden" name="fees_master_id[]" value="' + escapeSemValue(previewRow.fees_master_id || '') + '" class="sem-input-field">';
            html += '<input type="hidden" name="fees_group_id[]" value="' + escapeSemValue(previewRow.fees_group_id || '') + '" class="sem-input-field">';
            html += '</td>';
            html += '<td style="vertical-align: middle; padding: 4px 6px;">';
            html += '<input type="text" name="fee_name[]" class="form-control form-control-sm p-1 font-weight-bold text-dark sem-head-name sem-input-field" value="' + escapeSemValue(previewRow.fee_name) + '" style="font-size: 11px; height: 26px; border: 1px solid #ced4da;">';
            html += '</td>';
            html += '<td style="vertical-align: middle; padding: 4px 6px;">';
            html += '<input type="number" name="amount[]" class="form-control form-control-sm p-1 font-weight-bold text-success text-right sem-row-amount sem-input-field" value="' + escapeSemValue(previewRow.amount) + '" min="0" style="font-size: 11px; height: 26px; border: 1px solid #28a745; background: #f8fff9;">';
            html += '</td>';
            html += '<td style="vertical-align: middle; padding: 4px 6px;">';
            html += '<input type="date" name="due_date[]" class="form-control form-control-sm p-1 sem-row-due sem-input-field" value="' + escapeSemValue(previewRow.due_date) + '" style="font-size: 10px; height: 26px; border: 1px solid #ced4da;">';
            html += '</td>';
            html += '<td style="vertical-align: middle; padding: 4px 4px; text-align: center;">';
            html += '<button type="button" class="btn btn-xs btn-outline-danger p-0 border-0" title="Remove this semester from setup" onclick="removeSemRow(this)"><i class="fa fa-trash text-danger" style="font-size: 13px;"></i></button>';
            html += '</td>';
            html += '</tr>';
        }

        html += '</tbody></table></div>';
        document.getElementById('batch_inputs_container').innerHTML = '';
        document.getElementById('submit_btn').innerHTML = '<i class="fa fa-check-circle"></i> Save ' + loopCount + ' Semester Fee Structure (Fees Master)';
    }

    document.getElementById('preview_title').innerText = 'Semester-wise Fees & Amount Setup:';
    document.getElementById('preview_box').innerHTML = html;
    updateSemCountBadgeAndBtn();

    // Do not overwrite saved due dates while editing an existing structure.
    if (existingRows.length === 0) {
        applyDueDateSchedule();
    }
}

function onSemRowToggle(checkbox) {
    var tr = $(checkbox).closest('tr');
    var isChecked = $(checkbox).is(':checked');
    if (isChecked) {
        tr.removeClass('table-secondary text-muted').css('opacity', '1');
        tr.find('.sem-input-field').prop('disabled', false);
        tr.find('.sem-row-amount').css('background', '#f8fff9');
    } else {
        tr.addClass('table-secondary text-muted').css('opacity', '0.5');
        tr.find('.sem-input-field').prop('disabled', true);
        tr.find('.sem-row-amount').css('background', '#f1f5f9');
    }
    updateSemCountBadgeAndBtn();
}

function removeSemRow(btn) {
    var tr = $(btn).closest('tr');
    tr.remove();
    updateSemCountBadgeAndBtn();
}

function toggleAllSemRows(masterCheck) {
    var isChecked = $(masterCheck).is(':checked');
    $('.sem-row-check').each(function() {
        $(this).prop('checked', isChecked);
        onSemRowToggle(this);
    });
}

function updateSemCountBadgeAndBtn() {
    var activeCount = $('.sem-row-check:checked').length;
    var totalRows = $('.sem-row-check').length;
    
    $('#preview_count_badge').text(activeCount + ' / ' + totalRows + ' Semesters');
    
    var submitBtn = document.getElementById('submit_btn');
    if (submitBtn) {
        if (activeCount > 0) {
            submitBtn.disabled = false;
            submitBtn.className = 'btn btn-primary btn-sm btn-block font-weight-bold py-2 shadow-sm';
            submitBtn.innerHTML = '<i class="fa fa-check-circle"></i> Save ' + activeCount + ' Semester Fee Structure (Fees Master)';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'btn btn-secondary btn-sm btn-block font-weight-bold py-2 disabled';
            submitBtn.innerHTML = '<i class="fa fa-exclamation-circle"></i> Please select at least 1 Semester to Save';
        }
    }
}

function confirmDeleteFeesMaster(id, headName, className) {
    $('#delete_fees_master_id').val(id);
    $('#delete_fm_title').html('Remove <b>' + headName + '</b>?');
    $('#delete_fm_desc').html('Are you sure you want to remove <b>' + headName + '</b> from <b>' + className + '</b> fee structure?');
    $('#delete_fees_master_modal').modal('show');
}

function updateSingleClassPreview() {
    var clElem = document.getElementById('single_class_type_id');
    var clName = clElem.options[clElem.selectedIndex] ? clElem.options[clElem.selectedIndex].text : 'Selected Class';
    var feeName = document.getElementById('single_class_fee_name').value.trim() || 'Tuition Fee';
    var amount = document.getElementById('single_class_amount').value || '0';

    var html = '<div class="d-flex justify-content-between align-items-center p-2 border rounded bg-white" style="font-size:11.5px;">'
             + '<span><strong class="text-primary">' + clName + '</strong> &rarr; <span class="badge badge-light border text-dark">' + feeName + '</span></span>'
             + '<span class="font-weight-bold text-success" style="font-size:13px;">₹' + amount + '</span>'
             + '</div>';

    document.getElementById('preview_title').innerText = 'Single Class Fee Master:';
    document.getElementById('preview_box').innerHTML = html;
    document.getElementById('batch_inputs_container').innerHTML = '';
    document.getElementById('preview_count_badge').innerText = '1 Class';
    document.getElementById('submit_btn').innerHTML = '<i class="fa fa-check-circle"></i> Save Class Fee Structure (Fees Master)';
}

function updateHeadOnlyPreview() {
    var name = document.getElementById('head_only_name').value.trim() || 'Admission Fee';
    var html = '<div class="p-2 border rounded bg-white"><span class="badge badge-primary font-weight-bold" style="font-size:12px;"><i class="fa fa-tag"></i> ' + name + '</span></div>';

    document.getElementById('preview_title').innerText = 'Fee Head:';
    document.getElementById('preview_box').innerHTML = html;
    document.getElementById('batch_inputs_container').innerHTML = '';
    document.getElementById('preview_count_badge').innerText = 'Head Only';
    document.getElementById('submit_btn').innerHTML = '<i class="fa fa-plus-circle"></i> Create Fee Head';
}

// Right Panel View Mode Switcher (Cards vs Table)
function switchRightView(mode) {
    sessionStorage.setItem('fees_group_right_view', mode);
    if (mode === 'cards') {
        $('#btn_view_cards').addClass('active');
        $('#btn_view_table').removeClass('active');
        $('#course_cards_container').show();
        $('#master_table_container').hide();
    } else {
        $('#btn_view_cards').removeClass('active');
        $('#btn_view_table').addClass('active');
        $('#course_cards_container').hide();
        $('#master_table_container').show();
        if ($.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().columns.adjust().responsive.recalc();
        }
    }
}

// Course Quick Filter Pills Handler
function filterByCourse(courseId, btnElement) {
    sessionStorage.setItem('fees_group_course_filter', courseId);
    $('.course-pill-btn').removeClass('active');
    if (btnElement) {
        $(btnElement).addClass('active');
    } else {
        $('.course-pill-btn[data-course-id="' + courseId + '"]').addClass('active');
    }

    if (courseId === 'all') {
        $('.course-group-item').show();
    } else {
        $('.course-group-item').hide();
        $('#course_card_' + courseId).show();
    }
    checkVisibleCourseCards();
}

function checkVisibleCourseCards() {
    var visible = $('.course-group-item:visible').length;
    if (visible === 0) {
        $('#no_course_search_results').show();
    } else {
        $('#no_course_search_results').hide();
    }
}

// Auto-select course in left unified setup form
function selectCourseForSetup(courseId) {
    sessionStorage.setItem('fees_group_mode', 'semester');
    sessionStorage.setItem('fees_group_right_tab', 'structures');
    var sel = document.getElementById('course_selector');
    if (!sel) return;
    sel.value = courseId;
    $(sel).trigger('change.select2');
    switchMode('semester');
    onCourseSelected(sel, true);
    checkFormHasData();
    
    // Smooth scroll to left form
    $('html, body').animate({
        scrollTop: $('#course_selector_box').offset().top - 70
    }, 400);
}

// Check if Left Form has any selected course or entered data to toggle Reset Form button
function checkFormHasData() {
    var courseVal = $('#course_selector').val();
    var singleName = $('#single_class_fee_name').val();
    var singleClassType = $('#single_class_type_id').val();
    var singleAmt = $('#single_class_amount').val();
    var headOnlyName = $('#head_only_name').val();
    var semCommonAmt = $('#sem_common_amount').val();
    
    var hasData = false;
    if (courseVal && courseVal !== '') {
        hasData = true;
    } else if (singleName && singleName.trim() !== '') {
        hasData = true;
    } else if (singleClassType && singleClassType !== '') {
        hasData = true;
    } else if (singleAmt && singleAmt.trim() !== '') {
        hasData = true;
    } else if (headOnlyName && headOnlyName.trim() !== '') {
        hasData = true;
    } else if (semCommonAmt && semCommonAmt.trim() !== '') {
        hasData = true;
    }
    
    if (hasData) {
        $('#btn_reset_form').stop(true, true).fadeIn(150);
    } else {
        $('#btn_reset_form').stop(true, true).fadeOut(150);
    }
}

// Reset Entire Left Unified Form with complete state clearance
function executeFormReset() {
    sessionStorage.removeItem('fees_group_mode');
    sessionStorage.removeItem('fees_group_right_tab');
    sessionStorage.removeItem('fees_group_course_filter');

    // 1. Reset Course Selector & Select2
    var courseSel = $('#course_selector');
    courseSel.val('').trigger('change.select2');
    $('#course_detected_info').hide();
    $('#course_detected_text').text('');
    currentCourseClasses = [];
    
    // 2. Reset Semester Section
    $('#section_semester_controls').hide();
    $('#course_required_notice').show();
    $('#sem_base_name').val('Tuition Fee');
    $('#sem_group_type').val('academic');
    $('#sem_refundable').val('no');
    $('#sem_partial').val('0');
    $('#sem_same_amount').prop('checked', true);
    $('#sem_common_amount').val('');
    $('#sem_common_due_date').val('');
    $('#sem_amount_row').show();
    
    // 3. Reset Single Class Section
    $('#single_class_type_id').val('').trigger('change.select2');
    $('#single_class_fee_name').val('');
    $('#single_class_amount').val('');
    $('#single_class_due_date').val('');
    $('#single_class_group_type').val('academic');
    
    // 4. Reset Fee Head Only Section
    $('#head_only_name').val('');
    $('#head_only_group_type').val('admission');
    $('#head_only_refund').prop('checked', false);
    $('#head_only_partial').prop('checked', false);
    $('#fees_refund').val('no');
    $('#fees_partial').val('0');
    
    // 5. Clear preview & batch inputs
    $('#batch_inputs_container').empty();
    $('#preview_box').empty();
    $('#preview_box_container').hide();
    
    // 6. Reset Submit Button
    var submitBtn = document.getElementById('submit_btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.className = 'btn btn-secondary btn-sm btn-block font-weight-bold py-2 disabled';
        submitBtn.innerHTML = '<i class="fa fa-hand-o-up"></i> Please Select a Course to Continue';
    }
    
    // 7. Switch back to semester mode
    switchMode('semester');
    
    // 8. Hide Reset Button
    $('#btn_reset_form').hide();
    
    // 9. Close Modal
    $('#reset_form_confirm_modal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
}

function updateClassDropdownByCourse(courseId, classSelect, defaultClasses) {
    if (courseId) {
        $.ajax({
            url: "{{ url('getClassesByCourse') }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", course_id: courseId },
            dataType: "json",
            success: function(data) {
                classSelect.empty().append('<option value="">-- {{ __("common.Select") }} --</option>');
                if (data && data.length > 0) {
                    $.each(data, function(key, val) {
                        classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                    });
                }
                classSelect.val('').trigger('change');
            }
        });
    } else {
        classSelect.empty().append('<option value="">-- {{ __("common.Select") }} --</option>');
        if (defaultClasses && defaultClasses.length > 0) {
            $.each(defaultClasses, function(key, val) {
                classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
            });
        }
        classSelect.val('').trigger('change');
    }
}

function confirmDeleteFeesMaster(id, name, className) {
    $('#delete_fees_master_id').val(id);
    $('#delete_fm_title').text('Remove ' + name + ' from ' + className + '?');
    $('#delete_fm_desc').text('Are you sure you want to remove "' + name + '" fee head from ' + className + '\'s fee structure?');
    $('#delete_fees_master_modal').modal('show');
}

$(document).ready(function() {
    var savedMode = sessionStorage.getItem('fees_group_mode') || 'semester';
    var savedRightTab = sessionStorage.getItem('fees_group_right_tab');
    var savedRightView = sessionStorage.getItem('fees_group_right_view');
    var savedCourseFilter = sessionStorage.getItem('fees_group_course_filter');

    // 1. Restore Right View Mode (Cards vs Table)
    if (savedRightView) {
        switchRightView(savedRightView);
    }

    // 2. Restore Course Filter if present
    if (savedCourseFilter && savedCourseFilter !== 'all') {
        var filterBtn = $('.course-pill-btn[data-course-id="' + savedCourseFilter + '"]');
        if (filterBtn.length) {
            filterByCourse(savedCourseFilter, filterBtn[0]);
        }
    }

    // 3. Restore Left Form Mode & Auto-detect course
    var initCourse = document.getElementById('course_selector');
    if (savedMode === 'single_head') {
        switchMode('single_head');
    } else if (savedMode === 'single_class') {
        switchMode('single_class');
        if (initCourse && initCourse.value) {
            onCourseSelected(initCourse);
        }
    } else {
        // semester mode
        switchMode('semester');
        if (initCourse && initCourse.value) {
            onCourseSelected(initCourse);
        }
    }

    // 4. Restore Right Tab if saved
    if (savedRightTab) {
        switchRightTab(savedRightTab);
    }

    checkFormHasData();

    // Preserve active tab & mode on form submissions
    $('#quickForm').on('submit', function() {
        sessionStorage.setItem('fees_group_mode', currentMode);
        if (currentMode === 'single_head') {
            sessionStorage.setItem('fees_group_right_tab', 'no_class_heads');
        } else {
            sessionStorage.setItem('fees_group_right_tab', 'structures');
        }
    });

    $('#edit_fee_head_form').on('submit', function() {
        sessionStorage.setItem('fees_group_mode', 'single_head');
        sessionStorage.setItem('fees_group_right_tab', 'no_class_heads');
    });

    // Listen to course selection & form inputs to toggle Reset Form button
    $(document).on('change', '#course_selector', function() {
        checkFormHasData();
    });
    $(document).on('input change', '#quickForm input, #quickForm select, #course_selector', function() {
        checkFormHasData();
    });
    
    // Delete Fee Head Modal Trigger for No-Class Heads
    $(document).on('click', '.btn-delete-fee-head, .deleteData', function(e) {
        e.preventDefault();
        var delete_id = $(this).data('id');
        var headName = $(this).data('name') || 'this fee head';
        $('#delete_id').val(delete_id);
        $('#delete_fg_title').text('Delete "' + headName + '"?');
        $('#delete_fg_desc').text('Are you sure you want to permanently delete "' + headName + '"? This action cannot be undone.');
        $('#Modal_id').modal('show');
    });

    // In-place Edit Fee Head Modal Handler
    $(document).on('click', '.btn-edit-fee-head', function() {
        var headId = $(this).data('id');
        var headName = $(this).data('name');
        var category = $(this).data('category');
        var isRefund = String($(this).data('refund')).toLowerCase() === 'yes';
        var isPartial = parseInt($(this).data('partial')) === 1;

        $('#modal_edit_head_name').val(headName);
        $('#modal_edit_head_group_type').val(category);
        $('#modal_edit_head_refund').prop('checked', isRefund);
        $('#modal_edit_fees_refund').val(isRefund ? 'yes' : 'no');
        $('#modal_edit_head_partial').prop('checked', isPartial);
        $('#modal_edit_fees_partial').val(isPartial ? '1' : '0');

        $('#edit_fee_head_form').attr('action', "{{ url('feesGroupEdit') }}/" + headId);
        $('#edit_fee_head_modal').modal('show');
    });

    // AJAX Deletion for Fees Master (Course Card Heads)
    $('#delete_fees_master_form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalHtml = submitBtn.html();
        var id = $('#delete_fees_master_id').val();

        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Removing...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalHtml);
                $('#delete_fees_master_modal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                if (response && response.status) {
                    toastr.success(response.message || 'Fee head removed successfully!');

                    var row = $('#fm_row_' + id);
                    if (row.length) {
                        var amt = parseFloat(row.data('amount')) || 0;
                        var card = row.closest('.course-card');

                        // Recalculate Course Total Fee
                        var courseBadge = card.find('.course-total-fee-badge');
                        var currentCourseTotal = parseFloat(courseBadge.data('total')) || 0;
                        var newCourseTotal = Math.max(0, currentCourseTotal - amt);
                        courseBadge.data('total', newCourseTotal);
                        courseBadge.text('₹' + Number(newCourseTotal).toLocaleString('en-IN') + ' Total Course Fee');

                        var footTotal = card.find('.course-card-foot-total');
                        if (footTotal.length) {
                            footTotal.text('₹' + Number(newCourseTotal).toLocaleString('en-IN'));
                        }

                        row.fadeOut(250, function() {
                            $(this).remove();
                            var tbody = card.find('tbody');
                            if (tbody.find('tr').length === 0) {
                                location.reload();
                            }
                        });
                    }
                } else {
                    toastr.error((response && response.message) ? response.message : 'Unable to remove fee head.');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalHtml);
                $('#delete_fees_master_modal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                var errMsg = 'An error occurred while removing fee head.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                toastr.error(errMsg);
            }
        });
    });

    // AJAX Deletion for Standalone / No-Class Fee Heads
    $('#delete_fees_group_form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalHtml = submitBtn.html();
        var id = $('#delete_id').val();

        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalHtml);
                $('#Modal_id').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                if (response && response.status) {
                    toastr.success(response.message || 'Fee head deleted successfully!');

                    var row = $('#no_class_row_' + id);
                    if (row.length) {
                        row.fadeOut(250, function() {
                            $(this).remove();

                            // Update badge counter
                            var countBadge = $('#no_class_count_badge');
                            if (countBadge.length) {
                                var curCount = parseInt(countBadge.text().trim()) || 0;
                                var newCount = Math.max(0, curCount - 1);
                                countBadge.html('<i class="fa fa-tag"></i> ' + newCount);
                            }
                        });
                    }
                } else {
                    toastr.error((response && response.message) ? response.message : 'Unable to delete fee head.');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalHtml);
                $('#Modal_id').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                var errMsg = 'An error occurred while deleting fee head.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                toastr.error(errMsg);
            }
        });
    });

    // Open Assign Fee Head Modal
    $(document).on('click', '.btn-assign-fee-head', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#assign_fees_group_id').val(id);
        $('#assign_fee_head_title').text(name);

        var existingData = (existingHeadAssignments && existingHeadAssignments[id]) ? existingHeadAssignments[id] : { courses: {}, classes: {} };

        // Populate Course Mode inputs and table
        $('#assign_course_common_amount').val('');
        $('#assign_course_common_boys_amount').val('');
        $('#assign_course_common_girls_amount').val('');
        $('#assign_course_common_due_date').val('');
        $('#assign_modal_courses_table tbody tr.assign-course-row').each(function() {
            var chk = $(this).find('.assign-course-checkbox');
            var courseId = parseInt(chk.val()) || chk.val();
            var amt = $(this).find('.assign-course-amount-input');
            var boys = $(this).find('.assign-course-boys-input');
            var girls = $(this).find('.assign-course-girls-input');
            var due = $(this).find('.assign-course-due-input');

            if (existingData.courses && existingData.courses[courseId]) {
                var cData = existingData.courses[courseId];
                chk.prop('checked', true);
                $(this).addClass('table-primary');
                amt.val(cData.amount != null ? cData.amount : '').prop('disabled', false).css('background', '#ffffff');
                boys.val(cData.boys_amount != null ? cData.boys_amount : '').prop('disabled', false).css('background', '#ffffff');
                girls.val(cData.girls_amount != null ? cData.girls_amount : '').prop('disabled', false).css('background', '#ffffff');
                due.val(cData.due_date || '').prop('disabled', false).css('background', '#ffffff');
            } else {
                chk.prop('checked', false);
                $(this).removeClass('table-primary');
                amt.val('').prop('disabled', true).css('background', '#e9ecef');
                boys.val('').prop('disabled', true).css('background', '#e9ecef');
                girls.val('').prop('disabled', true).css('background', '#e9ecef');
                due.val('').prop('disabled', true).css('background', '#e9ecef');
            }
        });

        // Populate Class Mode inputs and table
        $('#assign_modal_course_filter').val('');
        $('#assign_modal_common_amount').val('');
        $('#assign_modal_common_boys_amount').val('');
        $('#assign_modal_common_girls_amount').val('');
        $('#assign_modal_common_due_date').val('');
        $('#assign_modal_classes_table tbody tr.assign-class-row').each(function() {
            $(this).show();
            var chk = $(this).find('.assign-class-checkbox');
            var classId = parseInt(chk.val()) || chk.val();
            var amt = $(this).find('.assign-amount-input');
            var boys = $(this).find('.assign-class-boys-input');
            var girls = $(this).find('.assign-class-girls-input');
            var due = $(this).find('.assign-due-date-input');

            if (existingData.classes && existingData.classes[classId]) {
                var clData = existingData.classes[classId];
                chk.prop('checked', true);
                $(this).addClass('table-primary');
                amt.val(clData.amount != null ? clData.amount : '').prop('disabled', false).css('background', '#ffffff');
                boys.val(clData.boys_amount != null ? clData.boys_amount : '').prop('disabled', false).css('background', '#ffffff');
                girls.val(clData.girls_amount != null ? clData.girls_amount : '').prop('disabled', false).css('background', '#ffffff');
                due.val(clData.due_date || '').prop('disabled', false).css('background', '#ffffff');
            } else {
                chk.prop('checked', false);
                $(this).removeClass('table-primary');
                amt.val('').prop('disabled', true).css('background', '#e9ecef');
                boys.val('').prop('disabled', true).css('background', '#e9ecef');
                girls.val('').prop('disabled', true).css('background', '#e9ecef');
                due.val('').prop('disabled', true).css('background', '#e9ecef');
            }
        });

        // Set default mode to Course and update counters
        switchAssignHeadMode('course');

        $('#assign_fee_head_modal').modal('show');
    });

    // AJAX Form Submission for Assign Fee Head
    $('#assign_fee_head_form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var mode = $('#assign_head_mode_input').val() || 'course';

        if (mode === 'course') {
            var checkedCourses = form.find('.assign-course-checkbox:checked');
            if (checkedCourses.length === 0) {
                toastr.error('Please select at least one Course to assign!');
                return;
            }
        } else {
            var checkedClasses = form.find('.assign-class-checkbox:checked');
            if (checkedClasses.length === 0) {
                toastr.error('Please select at least one Class / Semester to assign!');
                return;
            }
        }

        var submitBtn = $('#btn_save_assign_head');
        var originalHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalHtml);
                $('#assign_fee_head_modal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();

                if (response && response.status) {
                    toastr.success(response.message || 'Fee Head successfully assigned!');
                    // Reload page after short delay to refresh fee structures with active tab preserved
                    setTimeout(function() {
                        location.reload();
                    }, 600);
                } else {
                    toastr.error((response && response.message) ? response.message : 'Unable to assign fee head.');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalHtml);
                var errMsg = 'An error occurred while assigning fee head.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                toastr.error(errMsg);
            }
        });
    });

    // ==========================================
    // CLONE PREVIOUS YEAR FEE SETUP JS LOGIC
    // ==========================================
    $('#clone_source_session_id').on('change', function() {
        var sourceSessionId = $(this).val();
        if (!sourceSessionId) {
            $('#clone_preview_container').hide();
            $('#clone_loading_indicator').hide();
            $('#btn_confirm_clone_fees').prop('disabled', true);
            $('#clone_source_title_display').text('Select Previous Session');
            return;
        }

        $('#clone_preview_container').hide();
        $('#clone_loading_indicator').show();
        $('#btn_confirm_clone_fees').prop('disabled', true);

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('getFeesStructureSessionSummary') }}",
            type: 'POST',
            data: { session_id: sourceSessionId },
            success: function(res) {
                $('#clone_loading_indicator').hide();
                if (res.status === 'success') {
                    $('#clone_source_session_name').text(res.session_name);
                    $('#clone_source_title_display').text(res.session_name);
                    $('#clone_heads_count_badge').text(res.total_heads + ' Fee Heads');
                    $('#clone_stat_courses').text(res.configured_courses);
                    $('#clone_stat_classes').text(res.configured_classes + ' Sems');
                    $('#clone_stat_heads').text(res.total_heads);
                    $('#clone_stat_amount').text('₹' + parseFloat(res.total_amount || 0).toLocaleString('en-IN'));

                    var chipsHtml = [];
                    if (res.fee_group_names && res.fee_group_names.length > 0) {
                        res.fee_group_names.forEach(function(name) {
                            chipsHtml.push('<span class="badge px-2.5 py-1" style="background-color: #0284c7 !important; color: #ffffff !important; border: 1px solid #38bdf8 !important; font-size: 11.5px; font-weight: 600; border-radius: 4px;">' + name + '</span>');
                        });
                    } else {
                        chipsHtml.push('<span class="text-white-50 font-italic small" style="color: #94a3b8 !important;">No fee heads configured in this session</span>');
                    }
                    $('#clone_heads_chips_box').html(chipsHtml.join(' '));

                    if (res.total_heads > 0 || res.fees_masters_count > 0) {
                        $('#clone_preview_container').slideDown(200);
                        $('#btn_confirm_clone_fees').prop('disabled', false);
                    } else {
                        toastr.warning('The selected session has no fee heads or fee structures configured to clone.');
                        $('#clone_preview_container').slideDown(200);
                        $('#btn_confirm_clone_fees').prop('disabled', true);
                    }
                } else {
                    toastr.error(res.message || 'Unable to fetch session details.');
                }
            },
            error: function() {
                $('#clone_loading_indicator').hide();
                toastr.error('Failed to load session summary.');
            }
        });
    });

    // Confirm & Execute Clone Action
    $('#btn_confirm_clone_fees').click(function(e) {
        e.preventDefault();
        var sourceSessionId = $('#clone_source_session_id').val();
        if (!sourceSessionId) {
            toastr.error('Please select a source session first!');
            return;
        }

        var $btn = $(this);
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Cloning Fee Setup...');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('cloneFeesStructure') }}",
            type: 'POST',
            data: { source_session_id: sourceSessionId },
            success: function(res) {
                $btn.prop('disabled', false).html(originalBtnHtml);
                if (res.status === 'success') {
                    $('#cloneFeesModal').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    toastr.success(res.message || 'Fee setup cloned successfully!');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(res.message || 'Error occurred while cloning fee setup.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalBtnHtml);
                var err = 'Server error during cloning.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    err = xhr.responseJSON.message;
                }
                toastr.error(err);
            }
        });
    });

    // Global Modal Backdrop and Dismiss Handlers
    $('.modal').on('hidden.bs.modal', function() {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function(e) {
        var modal = $(this).closest('.modal');
        modal.modal('hide');
        setTimeout(function() {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }, 150);
    });
});
</script>

@endsection
