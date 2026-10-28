@extends('layout.app')
@section('content')

@php
    $courses = Helper::getCourses();
    $batches = Helper::getBatch();
    $getFeesGroup = Helper::getFeesGroup();
    $getSession = Helper::getSession();
@endphp

<style>
/* Page & Container */
.content-wrapper {
    background-color: #f4f6f9;
}

/* Card Header - Solid Brand Dark with Crisp White Text & Right-Aligned Tools */
.card-outline.card-orange .card-header.bg-primary {
    background-color: #002c54 !important;
    background: #002c54 !important;
    color: #ffffff !important;
    padding: 8px 15px !important;
    border-bottom: 2px solid #001a33 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
}
.card-header.bg-primary h3,
.card-header.bg-primary .card-title {
    color: #ffffff !important;
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    margin: 0 !important;
    float: none !important;
    display: flex !important;
    align-items: center !important;
}
.card-header.bg-primary .card-tools {
    margin-left: auto !important;
    margin-right: 0 !important;
    float: none !important;
    position: static !important;
    display: flex !important;
    align-items: center !important;
}
.card-header.bg-primary .btn {
    background-color: rgba(255, 255, 255, 0.2) !important;
    border: 1px solid rgba(255, 255, 255, 0.45) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    padding: 4px 10px !important;
}
.card-header.bg-primary .btn:hover {
    background-color: rgba(255, 255, 255, 0.35) !important;
    color: #ffffff !important;
    border-color: #ffffff !important;
}

/* Filter Section */
.filter-label {
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #1e293b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Table Header - Solid Dark with Pure White Font */
.table-assign > thead,
.table-assign > thead > tr,
.table-assign > thead > tr > th {
    background-color: #002c54 !important;
    background: #002c54 !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    padding: 8px 6px !important;
    vertical-align: middle !important;
    border: 1px solid #103a61 !important;
    text-align: center;
}
.table-assign > thead > tr > th,
.table-assign > thead > tr > th * {
    color: #ffffff !important;
}

/* Table Body */
.table-assign > tbody > tr > td {
    padding: 6px 8px !important;
    font-size: 13px !important;
    vertical-align: middle !important;
    color: #0f172a !important;
}
.table-assign > tbody > tr:hover {
    background-color: #f1f5f9 !important;
}

/* Interactive Real-Time Fee Head Chips */
.fee-head-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    margin: 2px 2px;
    transition: all 0.2s ease;
    user-select: none;
}
.chip-assigned {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #0284c7;
    box-shadow: 0 1px 2px rgba(2, 132, 199, 0.08);
}
.chip-assigned:hover {
    background-color: #bae6fd;
    border-color: #0284c7;
}
.chip-assigned .chip-name {
    color: #0369a1;
    font-weight: 600;
}
.chip-assigned .chip-amt {
    color: #0284c7;
    font-weight: 700;
}

/* Red Focused Highlight for Unassigned Fee Heads */
.chip-unassigned {
    background-color: #fff1f2;
    color: #be123c;
    border: 1px solid #fda4af;
    box-shadow: 0 1px 2px rgba(225, 29, 72, 0.08);
}
.chip-unassigned:hover {
    background-color: #ffe4e6;
    border-color: #f43f5e;
    color: #9f1239;
}
.chip-unassigned .chip-name {
    color: #9f1239;
    font-weight: 600;
}
.chip-unassigned .chip-amt {
    color: #be123c;
    font-weight: 700;
}
.chip-name {
    font-weight: 600;
    margin-right: 4px;
}
.chip-amt {
    font-weight: 700;
    color: #0f172a;
}

/* Dark Container Light Text Contrast Fixes */
.kpi-dark-card {
    background: linear-gradient(135deg, #002c54 0%, #001f3b 100%) !important;
    border: 1.5px solid #001a33 !important;
    color: #ffffff !important;
    border-radius: 6px !important;
    min-height: 58px !important;
}
.kpi-dark-card .info-box-text {
    color: #cbd5e1 !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
}
.kpi-dark-card .info-box-number {
    color: #ffffff !important;
    font-weight: 800 !important;
}
.kpi-dark-card .info-box-icon {
    background: rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
}

#tfoot_summary_row, #tfoot_summary_row td {
    background-color: #e2f0d9 !important;
    color: #1f2937 !important;
    height: 30px !important;
    padding: 2px 5px !important;
    font-size: 10px !important;
    line-height: 1.1 !important;
    border-color: #9eb58f !important;
}
#overall_payment_summary_deck {
    display: none !important;
}
#students_assign_scroll {
    overflow: auto;
    min-height: 280px;
    scrollbar-gutter: stable;
}
#students_assign_table > thead > tr > th {
    position: sticky;
    top: 0;
    z-index: 12;
}
#students_assign_table #tfoot_summary_row,
#students_assign_table #tfoot_summary_row td {
    position: sticky;
    bottom: 0;
    z-index: 11;
    box-shadow: 0 -2px 5px rgba(0, 23, 45, 0.3);
}
#students_assign_table tfoot {
    position: sticky;
    bottom: 0;
    z-index: 15;
}
/* Sticky columns: Checkbox (0px), Sr No (40px), Student Name/Info (80px) on left; Actions (125px) & Outstanding/Excess (0px) on right */
#students_assign_table .col-fixed-check {
    position: sticky !important;
    left: 0;
    width: 40px;
    min-width: 40px;
    max-width: 40px;
}
#students_assign_table .col-fixed-sr {
    position: sticky !important;
    left: 40px;
    width: 40px;
    min-width: 40px;
    max-width: 40px;
}
#students_assign_table .col-fixed-student {
    position: sticky !important;
    left: 80px;
    width: 230px;
    min-width: 230px;
    max-width: 230px;
    box-shadow: 3px 0 5px rgba(0, 35, 67, 0.12);
}
#students_assign_table .col-actions {
    position: sticky !important;
    right: 125px;
    width: 135px;
    min-width: 135px;
    max-width: 135px;
    box-shadow: -3px 0 5px rgba(0, 35, 67, 0.12);
}
#students_assign_table .col-fixed-balance {
    position: sticky !important;
    right: 0;
    width: 125px;
    min-width: 125px;
    max-width: 125px;
}

/* Header sticky layer */
#students_assign_table > thead > tr:first-child > .col-fixed-check,
#students_assign_table > thead > tr:first-child > .col-fixed-sr,
#students_assign_table > thead > tr:first-child > .col-fixed-student,
#students_assign_table > thead > tr:first-child > .col-actions,
#students_assign_table > thead > tr:first-child > .col-fixed-balance {
    z-index: 25 !important;
    background: #d9eaf7 !important;
}

#students_assign_table > thead > .excel-filter-row > .col-fixed-check,
#students_assign_table > thead > .excel-filter-row > .col-fixed-sr,
#students_assign_table > thead > .excel-filter-row > .col-fixed-student,
#students_assign_table > thead > .excel-filter-row > .col-actions,
#students_assign_table > thead > .excel-filter-row > .col-fixed-balance {
    z-index: 24 !important;
    background: #f3f6f8 !important;
}

/* Body sticky layer */
#students_assign_table > tbody > tr > .col-fixed-check,
#students_assign_table > tbody > tr > .col-fixed-sr,
#students_assign_table > tbody > tr > .col-fixed-student,
#students_assign_table > tbody > tr > .col-actions,
#students_assign_table > tbody > tr > .col-fixed-balance {
    z-index: 9 !important;
    background: #ffffff !important;
}
#students_assign_table > tbody > tr:nth-child(even) > .col-fixed-check,
#students_assign_table > tbody > tr:nth-child(even) > .col-fixed-sr,
#students_assign_table > tbody > tr:nth-child(even) > .col-fixed-student,
#students_assign_table > tbody > tr:nth-child(even) > .col-actions,
#students_assign_table > tbody > tr:nth-child(even) > .col-fixed-balance {
    background: #fafafa !important;
}
#students_assign_table > tbody > tr:hover > .col-fixed-check,
#students_assign_table > tbody > tr:hover > .col-fixed-sr,
#students_assign_table > tbody > tr:hover > .col-fixed-student,
#students_assign_table > tbody > tr:hover > .col-actions,
#students_assign_table > tbody > tr:hover > .col-fixed-balance {
    background: #fffde7 !important;
}

/* Footer sticky layer */
#students_assign_table > tfoot > tr > .col-fixed-check,
#students_assign_table > tfoot > tr > .col-fixed-sr,
#students_assign_table > tfoot > tr > .col-fixed-student,
#students_assign_table > tfoot > tr > .col-actions,
#students_assign_table > tfoot > tr > .col-fixed-balance {
    bottom: 0;
    z-index: 26 !important;
    background: #e2f0d9 !important;
}

/* Remark is normal scrollable column */
#students_assign_table .col-remark {
    position: static !important;
    min-width: 170px;
    width: 170px;
    box-shadow: none !important;
}
/* Compact segmented buttons for balance status quick filter */
.balance-filter-group {
    display: flex !important;
    width: 100% !important;
    max-width: 120px !important;
    height: 22px !important;
    border-radius: 4px !important;
    overflow: hidden !important;
    border: 1px solid #94a3b8 !important;
    background: #ffffff !important;
    padding: 0 !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}
.balance-filter-group .btn-status-toggle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 1 1 0 !important;
    width: 33.33% !important;
    min-width: 0 !important;
    max-width: 33.34% !important;
    padding: 0 !important;
    margin: 0 !important;
    height: 100% !important;
    font-size: 8px !important;
    font-weight: 800 !important;
    line-height: 1 !important;
    text-align: center !important;
    text-transform: uppercase !important;
    letter-spacing: -0.3px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: clip !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    outline: none !important;
    cursor: pointer !important;
    transition: all 0.12s ease-in-out !important;
    box-sizing: border-box !important;
    border-right: 1px solid #cbd5e1 !important;
}
.balance-filter-group .btn-status-toggle:last-child {
    border-right: none !important;
}

/* Inactive (Unselected) State - High Contrast & Clearly Visible on White */
.balance-filter-group .btn-status-toggle[data-status="dues"] {
    background: #ffffff !important;
    color: #b91c1c !important; /* Bold Crimson Red */
}
.balance-filter-group .btn-status-toggle[data-status="excess"] {
    background: #ffffff !important;
    color: #15803d !important; /* Bold Forest Green */
}
.balance-filter-group .btn-status-toggle[data-status="cleared"] {
    background: #ffffff !important;
    color: #1e293b !important; /* Bold Dark Slate */
}

/* Active (Selected) State - Solid Filled with Crisp White Text */
.balance-filter-group .btn-status-toggle[data-status="dues"].active {
    background: #dc2626 !important;
    color: #ffffff !important;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.25) !important;
}
.balance-filter-group .btn-status-toggle[data-status="excess"].active {
    background: #16a34a !important;
    color: #ffffff !important;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.25) !important;
}
.balance-filter-group .btn-status-toggle[data-status="cleared"].active {
    background: #334155 !important;
    color: #ffffff !important;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.25) !important;
}

.balance-filter-group .btn-status-toggle:hover {
    filter: brightness(0.92);
}
#students_assign_table .btn-grid-reset,
.btn-grid-reset {
    height: 24px !important;
    font-size: 10px !important;
    padding: 0 6px !important;
    line-height: 22px !important;
    border-radius: 4px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    border: 1px solid #b91c1c !important;
    background-color: #dc2626 !important;
    background: #dc2626 !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    cursor: pointer !important;
    text-transform: uppercase !important;
    letter-spacing: 0.4px !important;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.35) !important;
    transition: all 0.15s ease-in-out !important;
}
#students_assign_table .btn-grid-reset *,
.btn-grid-reset * {
    color: #ffffff !important;
}
#students_assign_table .btn-grid-reset:hover,
.btn-grid-reset:hover {
    background-color: #991b1b !important;
    background: #991b1b !important;
    color: #ffffff !important;
    border-color: #7f1d1d !important;
    box-shadow: 0 2px 5px rgba(153, 27, 27, 0.45) !important;
}
#students_assign_table .btn-grid-reset:active,
.btn-grid-reset:active {
    transform: scale(0.96) !important;
}
.legacy-grid-filters,
.legacy-grid-actions {
    display: none !important;
}
.fees-assign-page-card .card-body {
    padding: 0 !important;
}
.fees-assign-page-card {
    margin-bottom: 0 !important;
    border: 1px solid #9aa7b1 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}
.fees-assign-page-card > .card-header {
    min-height: 46px;
    background: #217346 !important;
    border-radius: 0 !important;
    border-bottom: 1px solid #185c37 !important;
    padding: 7px 10px !important;
}
.fees-assign-page-card > .card-header .card-title { font-size:15px !important; letter-spacing:.2px; }
#ledger_active_context {
    display:inline-block;
    font-size:11px;
    font-weight:600;
    color:#d8f3e4;
    letter-spacing:0;
    white-space:nowrap;
}
.fees-assign-page-card > .card-header .btn { border-radius:2px !important; box-shadow:none !important; }
#students_assign_scroll {
    border: 0 !important;
    border-radius: 0 !important;
    background:#fff;
}
#students_assign_table { border-collapse: separate; border-spacing: 0; }
#students_assign_table > thead > tr:first-child > th {
    background:#d9eaf7 !important;
    color:#152536 !important;
    border-color:#8fa7b8 !important;
    font-size:11.5px !important;
    height:34px;
    text-transform:none;
}
#students_assign_table > thead > .excel-filter-row > th {
    background:#f3f6f8 !important;
    border-color:#aebbc5 !important;
}
#students_assign_table > tbody > tr > td {
    background:#fff;
    border-color:#c6cdd3 !important;
    padding:4px 5px !important;
}
#students_assign_table > tbody > tr:nth-child(even) > td { background:#fafafa; }
#students_assign_table > tbody > tr:hover > td { background:#fffde7 !important; }
.student-fee-ledger { border-collapse:collapse !important; }
.student-fee-ledger th {
    background:#e2f0d9 !important;
    color:#1f3b2d !important;
    border:1px solid #9eb58f !important;
    height:24px;
    font-size:10px !important;
    text-align:center;
}
.student-fee-ledger td { border:1px solid #c7d1c0 !important; height:23px; }
.student-fee-ledger tbody tr:hover td { background:#fff8cf !important; }
.grid-filter-caption { font-weight:700; }
#students_assign_table .fee-summary-cell { display:none !important; }
#students_assign_table {
    width: 100% !important;
    min-width: 1320px !important;
    table-layout: fixed;
}
.student-fee-ledger {
    width: 100%;
    table-layout: fixed;
    font-size: 10.5px;
    background: #fff;
}
.student-fee-ledger th { background:#f2f2f2 !important; color:#222 !important; border:1px solid #a6a6a6 !important; padding:2px 4px !important; font-size:9.5px !important; font-weight:700 !important; }
.student-fee-ledger td { border:1px solid #c8c8c8 !important; padding:2px 4px !important; height:21px !important; vertical-align:middle !important; }
.student-fee-ledger .ledger-head { width:24%; font-weight:700; }
.student-fee-ledger .ledger-money { width:9%; text-align:right; white-space:nowrap; }
.student-fee-ledger .ledger-receipt { width:12%; }
.student-fee-ledger .ledger-date { width:13%; white-space:nowrap; }
.student-fee-ledger .ledger-mode { width:10%; }
.student-fee-ledger-wrap { margin:0 !important; overflow:visible !important; }
.student-fee-ledger th,
.student-fee-ledger td { line-height:1.05 !important; }
.student-identity-name { font-size:12px; line-height:1.15; }
.student-identity-meta { display:flex; align-items:center; gap:7px; margin-top:3px; white-space:nowrap; }
.student-admission-chip {
    display:inline-block;
    padding:1px 5px;
    background:#eef2f5;
    color:#263746;
    border:1px solid #b8c2ca;
    border-radius:2px;
    font-size:9.5px;
    font-weight:700;
    line-height:1.25;
}
.student-mobile-text { color:#52606d; font-size:10px; font-weight:500; }
#tfoot_summary_row .btn-link { color:#355d45 !important; }
.overall-ledger-summary > div { background:#fff; border:1px solid #ccd5cf; padding:6px 8px; text-align:center; }
.overall-ledger-summary small { display:block; color:#647067; font-size:10.5px; text-transform:uppercase; }
.overall-ledger-summary strong { display:block; font-size:15px; }
.ledger-semester-toggle { background:#fff; color:#1f2937; font-size:11px; border-radius:0; }
.ledger-semester-toggle:hover { background:#edf5ef; }
.overall-ledger-table { font-size:12px; }
.overall-ledger-table th { background:#eef2ef; color:#27332b; padding:6px !important; font-size:11.5px; }
.overall-ledger-table td { background:#fff; padding:6px !important; vertical-align:middle; }
.overall-ledger-header {
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
    min-height:72px;
    padding:11px 18px !important;
    background:#217346 !important;
    color:#fff !important;
    border-bottom:0 !important;
}
.overall-ledger-heading { min-width:0; display:flex !important; flex-direction:column !important; align-items:flex-start !important; }
.overall-ledger-header .modal-title {
    display:block !important;
    margin:0 0 3px !important;
    padding:0 !important;
    color:#fff !important;
    font-size:19px !important;
    font-weight:700 !important;
    line-height:1.2 !important;
}
.overall-ledger-student-meta { display:flex; align-items:center; flex-wrap:wrap; gap:7px; margin-top:3px; font-size:12px; color:#e7f6ed; }
.overall-ledger-student-meta strong { color:#fff; font-size:12.5px; }
.overall-ledger-student-meta span { border-left:1px solid rgba(255,255,255,.4); padding-left:6px; }
.overall-ledger-close { position:static !important; margin:0 0 0 12px !important; padding:3px 7px !important; color:#fff !important; opacity:.9 !important; font-size:23px !important; line-height:1 !important; }
#overallStudentLedgerModal .modal-dialog { max-width:94vw; }
#overallStudentLedgerModal .modal-content { border:0; border-radius:4px; overflow:hidden; }
#overallStudentLedgerModal .modal-body { max-height:calc(100vh - 170px); overflow-y:auto; }
.overall-ledger-summary { border:1px solid #bcc9c0; }
.overall-ledger-summary > div { border-width:0 0 0 1px; min-height:62px; display:flex; flex-direction:column; justify-content:center; }
.overall-ledger-summary > div:first-child { border-left:0; }
.ledger-semester-card { border-radius:2px !important; overflow:hidden; }
.ledger-semester-toggle {
    display:grid !important;
    grid-template-columns:minmax(190px, 1fr) auto 74px;
    align-items:center;
    gap:12px;
    padding:9px 12px !important;
}
.ledger-semester-name { display:flex; align-items:baseline; gap:8px; min-width:0; }
.ledger-semester-name strong { font-size:12.5px; }
.ledger-semester-name small { color:#6b7280; font-size:11.5px; }
.ledger-semester-figures { color:#334155; font-size:12px; white-space:nowrap; }
.ledger-semester-toggle > .badge { justify-self:end; min-width:58px; }
@media (max-width: 768px) {
    .ledger-semester-toggle { grid-template-columns:1fr auto; }
    .ledger-semester-figures { grid-column:1 / -1; grid-row:2; white-space:normal; }
    .overall-ledger-summary { overflow-x:auto; flex-wrap:nowrap; }
    .overall-ledger-summary > div { min-width:125px; }
}
.student-row-actions { display:grid; grid-template-columns:1fr; gap:3px; }
.student-row-actions .btn { width:100%; margin:0 !important; padding:3px 5px !important; font-size:10px !important; border-radius:2px !important; white-space:nowrap; }
.ledger-footer-totals {
    display:grid;
    grid-template-columns:24fr 9fr 9fr 9fr 12fr 13fr 10fr 9fr;
    align-items:center;
    gap:3px;
    width:100%;
}
.ledger-footer-totals > span {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    min-width:0;
    padding:1px 3px;
    border-left:1px solid #b7c9ad;
    line-height:1.05;
}
.ledger-footer-totals > span:first-child { border-left:0; }
.ledger-footer-totals small { color:#607064; font-size:8.5px; font-weight:600; }
.ledger-footer-totals strong { color:#243329; font-size:10.5px; white-space:nowrap; }
.ledger-footer-totals .ledger-footer-discount strong { color:#b7791f; }
.ledger-footer-totals .ledger-footer-net strong { color:#2563a6; }
.ledger-footer-totals .ledger-footer-paid strong { color:#16803a; }
.ledger-footer-totals .ledger-footer-due strong { color:#c53030; }
.ledger-footer-outstanding small {
    display:block;
    color:#607064;
    font-size:8.5px;
    font-weight:600;
    line-height:1;
}
.ledger-footer-outstanding strong {
    display:block;
    color:#c53030;
    font-size:11px;
    line-height:1.15;
    white-space:nowrap;
}
#students_assign_table th,
#students_assign_table td {
    box-sizing: border-box;
}
.excel-filter-row th {
    top: 37px !important;
    z-index: 13 !important;
    background: #eef4f8 !important;
    padding: 4px !important;
    border-color: #cbd5e1 !important;
}
.excel-filter-row .form-control,
.excel-filter-row select,
.excel-filter-row input {
    height: 29px !important;
    min-height: 29px !important;
    padding: 3px 6px !important;
    font-size: 11.5px !important;
    color: #172033 !important;
    background: #ffffff !important;
    border: 1px solid #b8c4d1 !important;
    border-radius: 3px !important;
}
#students_assign_table .excel-filter-row select,
#students_assign_table .excel-filter-row input,
#students_assign_table .excel-filter-row option {
    color: #172033 !important;
}
.excel-filter-row .btn {
    height: 29px;
    padding: 3px 7px !important;
    font-size: 11px !important;
    white-space: nowrap;
}
.excel-filter-row .grid-filter-caption {
    display: block;
    color: #475569 !important;
    font-size: 9px;
    line-height: 10px;
    text-transform: uppercase;
    letter-spacing: .35px;
    margin-bottom: 2px;
}
.assigned-heads-column {
    display: none !important;
}

/* Badges */
.badge-secondary {
    background-color: #334155 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}

/* Select2 Customization */
.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple {
    min-height: 33px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 4px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    font-size: 13px !important;
    color: #1e293b !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered {
    padding: 1px 4px !important;
    font-size: 12.5px !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #002c54 !important;
    border: 1px solid #001a33 !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 2px 8px !important;
    margin-top: 3px !important;
    border-radius: 4px !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #ffffff !important;
    margin-right: 6px !important;
    font-weight: bold !important;
    opacity: 0.9 !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #facc15 !important;
    opacity: 1 !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #002c54 !important;
    color: #ffffff !important;
}

/* Top Course Fee Structure Panel */
.course-fee-structure-card {
    background-color: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.top-structure-head-chip {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 4px 10px;
    margin: 2px 3px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    user-select: none;
}
.top-structure-head-chip:hover {
    border-color: #002c54;
    background: #f1f5f9;
}
.top-structure-head-chip.selected-top-chip {
    background-color: #e0f2fe !important;
    border-color: #0284c7 !important;
    color: #0369a1 !important;
    font-weight: 600;
    box-shadow: 0 0 0 1px #0284c7;
}
.top-structure-head-chip .badge-amt {
    background: #002c54;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    margin-left: 6px;
}
.top-structure-head-chip.selected-top-chip .badge-amt {
    background: #0284c7;
}

/* Mid-bar Strip */
.mid-action-strip {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 12px;
}

/* Toast Notifications */
#fee_toast_box {
    position: fixed;
    top: 20px;
    right: 25px;
    z-index: 9999;
    min-width: 280px;
}

/* Assignment controls are intentionally separated from the view-only list. */
#course_fee_structure_card,
#btn_quick_apply_all_loaded,
#btn_bulk_assign_realtime {
    display: none !important;
}
.table-assign .toggle-fee-head-checkbox,
.table-assign .btn-student-assign-all-heads,
.table-assign .btn-student-unassign-all-heads,
.table-assign .chip-unassigned {
    display: none !important;
}
.table-assign .fee-head-chip {
    cursor: default !important;
}

/* Collected Fees Warning Modal High-Contrast Styling */
#feeCollectedWarningModal .modal-content {
    background-color: #ffffff !important;
    border: none !important;
}
#feeCollectedWarningModal .modal-body {
    background-color: #ffffff !important;
}
#feeCollectedWarningModal .modal-footer {
    background-color: #f1f5f9 !important;
    border-top: 1.5px solid #cbd5e1 !important;
}
.table-collected-invoices thead,
.table-collected-invoices thead tr,
.table-collected-invoices thead th {
    background-color: #002c54 !important;
    background: #002c54 !important;
    color: #ffffff !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    padding: 8px 6px !important;
    border: 1px solid #103a61 !important;
    text-align: center !important;
    vertical-align: middle !important;
}
.table-collected-invoices tbody td {
    background-color: #ffffff !important;
    color: #0f172a !important;
    vertical-align: middle !important;
    font-size: 12px !important;
    padding: 6px 8px !important;
    border-color: #e2e8f0 !important;
}
.table-collected-invoices tbody tr:nth-of-type(odd) td {
    background-color: #f8fafc !important;
}
.table-collected-invoices tbody tr:hover td {
    background-color: #f1f5f9 !important;
}
#btn_goto_fee_collect {
    background-color: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    transition: all 0.15s ease-in-out;
}
#btn_goto_fee_collect:hover {
    background-color: #b91c1c !important;
    border-color: #991b1b !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
}
</style>

<div id="fee_toast_box"></div>

<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card card-outline card-orange mb-3 shadow-sm fees-assign-page-card">
                        
                        <!-- Card Header -->
                        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold">
                                <i class="fa fa-table mr-1"></i> Semester Fee Assignment & Receipt Ledger
                                <span id="ledger_active_context" class="ml-2"></span>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success btn-sm mr-2" id="grid_bulk_assign" title="Bulk Assign Current Semester Fees" style="background:#16a34a !important; border-color:#16a34a !important;">
                                    <i class="fa fa-plus-circle mr-1"></i> Bulk Assign
                                </button>
                                <a href="{{ url('feesGroup') }}" class="btn btn-primary btn-sm mr-2" title="Fees Group & Master">
                                    <i class="fa fa-money mr-1"></i> Fees Structure
                                </a>
                                <a href="{{ url('fee_dashboard') }}" class="btn btn-primary btn-sm" title="Back">
                                    <i class="fa fa-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            @include('layout.message')

                            <!-- Filter Inputs Row: Course -> Class/Semester -> Batch -> Student -->
                            <div class="row mb-2 legacy-grid-filters">
                                <!-- 1. Academic Session -->
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Academic Session <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_session_id" name="session_id" style="width: 100%;">
                                        @if(!empty($getSession))
                                            @foreach($getSession as $sessionItem)
                                                <option value="{{ $sessionItem->id }}" {{ ((string)$sessionItem->id === (string)($serach['session_id'] ?? Session::get('session_id'))) ? 'selected' : '' }}>
                                                    {{ $sessionItem->from_year ?? '' }}-{{ $sessionItem->to_year ?? '' }}{{ (int)$sessionItem->id === (int)Session::get('session_id') ? ' (Current)' : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- 1. Course Select -->
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Course <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_course_id" name="course_id" style="width: 100%;">
                                        <option value="">-- Select Course --</option>
                                        @if(!empty($courses))
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}" {{ ($course->id == ($serach['course_id'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- 2. Class / Semester Select -->
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Class / Semester</span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_class_type_id" name="class_type_id" style="width: 100%;">
                                        <option value="">-- All Current Semesters --</option>
                                        @if(!empty($allClassType))
                                            @foreach($allClassType as $type)
                                                @if(empty($serach['course_id']) || (int)($type->course_id ?? 0) === (int)$serach['course_id'])
                                                    <option value="{{ $type->id }}" {{ ((string)$type->id === (string)($serach['class_type_id'] ?? '')) ? 'selected' : '' }}>{{ $type->name ?? '' }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- 3. Batch Select -->
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Batch <span class="text-danger">*</span></span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_batch" name="batch" style="width: 100%;">
                                        <option value="">-- Select Batch --</option>
                                        @if(!empty($batches))
                                            @foreach($batches as $batch)
                                                <option value="{{ $batch->name ?? '' }}" {{ ($batch->name == ($serach['batch'] ?? '')) ? 'selected' : '' }}>{{ $batch->name ?? '' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Gender Select -->
                                <div class="col-md-2 col-sm-6 mb-2">
                                    <label class="filter-label">
                                        <span>Gender</span>
                                    </label>
                                    <select class="form-control form-control-sm select2" id="filter_gender_id" name="gender_id" style="width: 100%;">
                                        <option value="">-- All Genders --</option>
                                        <option value="1" {{ (($serach['gender_id'] ?? '') == '1') ? 'selected' : '' }}>👦 Boys (Male)</option>
                                        <option value="2" {{ (($serach['gender_id'] ?? '') == '2') ? 'selected' : '' }}>👧 Girls (Female)</option>
                                    </select>
                                </div>

                                <!-- 5. Admission No / Student ID / Name Search -->
                                <div class="col-md-2 col-sm-12 mb-2">
                                    <label class="filter-label">
                                        <span>Student ID / Name</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm" id="filter_admission_no" name="admissionNo" placeholder="Name / adm / mobile..." value="{{ $serach['admissionNo'] ?? '' }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="btn_search_students" title="Search">
                                                <i class="fa fa-search"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" id="btn_clear_filters" title="Reset Filters">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Interactive Course Fee Structure & Master Heads Toolbar (Visible only when both Course & Batch are selected) -->
                            <div class="course-fee-structure-card" id="course_fee_structure_card" style="display: none;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-1" style="border-bottom: 1px dashed #cbd5e1;">
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                            <i class="fa fa-cubes text-primary mr-1"></i> Fee Structure: <span id="structure_course_batch_label" class="text-primary font-weight-bold">Course - Batch</span> (<span id="structure_head_count" class="text-dark font-weight-bold">0</span> Heads)
                                        </span>
                                        <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11.5px;">
                                            Total Structure Fee: <strong class="text-dark ml-1" id="structure_total_fee">₹0</strong>
                                        </span>
                                        <span class="badge badge-info px-2 py-1" id="selected_heads_badge" style="font-size: 11.5px; display: none;">
                                            <i class="fa fa-check-circle mr-1"></i><span id="selected_heads_count">0</span> Head(s) Selected
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold px-2 py-1" id="btn_top_select_all_heads" style="font-size: 11px;">
                                            <i class="fa fa-check-square-o mr-1"></i> Select All Heads
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold px-2 py-1" id="btn_top_deselect_all_heads" style="font-size: 11px;">
                                            <i class="fa fa-square-o mr-1"></i> Deselect Heads
                                        </button>
                                    </div>
                                </div>

                                <!-- Dynamic Fee Heads Chips Container -->
                                <div class="d-flex flex-wrap align-items-center" id="course_fee_structure_chips_box" style="gap: 4px; min-height: 32px;">
                                    <!-- Loaded via AJAX -->
                                </div>
                            </div>

                            <!-- Fast Action Toolbar & Real-Time Status Strip -->
                            <div class="mid-action-strip d-flex flex-wrap justify-content-between align-items-center mb-2 legacy-grid-actions">
                                <div class="mb-1 mb-md-0 d-flex align-items-center flex-wrap" style="gap: 6px;">
                                    <button type="button" class="btn btn-warning btn-xs font-weight-bold shadow-xs text-dark" id="btn_quick_apply_all_loaded" style="font-size: 11.5px; padding: 4px 10px;">
                                        <i class="fa fa-bolt text-dark mr-1"></i> Quick Apply All Heads to ALL Loaded (<span class="total_students_count_text">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-success btn-xs font-weight-bold shadow-xs" id="btn_bulk_assign_realtime" style="font-size: 11.5px; padding: 4px 10px;">
                                        <i class="fa fa-check-circle mr-1"></i> Assign to Selected Students (<span class="selected_students_count_text">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm" id="btn_open_bulk_assign_modal">
                                        <i class="fa fa-plus-circle mr-1"></i> Bulk Assign Current Semester Fees
                                    </button>
                                    <span class="badge badge-light border text-muted py-1 px-2" style="font-size: 11px;">
                                        <i class="fa fa-eye text-primary mr-1"></i> View-only list: current active semester and assigned heads.
                                    </span>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 4px;">
                                    <span class="badge px-2 py-1" id="total_students_badge" style="background-color: #334155; color: #ffffff; font-size: 12px; font-weight: 500;">Total: 0</span>
                                    <span class="badge px-2 py-1 mr-1" id="selected_students_badge" style="background-color: #16a34a; color: #ffffff; font-size: 12px; font-weight: 600;">Selected: 0</span>
                                    <button type="button" class="btn btn-default btn-xs border" id="btn_quick_select_all" style="font-size: 11.5px; padding: 3px 8px;" title="Select all loaded students">
                                        <i class="fa fa-check-square text-primary mr-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs border" id="btn_quick_deselect_all" style="font-size: 11.5px; padding: 3px 8px;" title="Deselect all">
                                        <i class="fa fa-square-o text-danger mr-1"></i>Deselect
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs border" id="btn_clear_filters_mid" style="font-size: 11.5px; padding: 3px 8px;" title="Reset Filters">
                                        <i class="fa fa-refresh text-secondary mr-1"></i>Reset
                                    </button>
                                </div>
                            </div>

                            <!-- Overall Payment & Fee Summary KPI Cards Deck -->
                            <div class="row mb-2" id="overall_payment_summary_deck">
                                <!-- 1. Loaded Students -->
                                <div class="col-xl-2 col-md-4 col-sm-6 col-6 mb-2">
                                    <div class="info-box kpi-dark-card shadow-xs p-2 mb-0 d-flex align-items-center">
                                        <span class="info-box-icon elevation-0 d-flex align-items-center justify-content-center mr-2" style="width: 36px; height: 36px; border-radius: 6px; font-size: 15px;">
                                            <i class="fa fa-users"></i>
                                        </span>
                                        <div class="info-box-content p-0" style="line-height: 1.2;">
                                            <span class="info-box-text text-uppercase" style="font-size: 10.5px;">Students</span>
                                            <span class="info-box-number" id="summary_card_students" style="font-size: 15px;">0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Gross Total Fee -->
                                <div class="col-xl-2 col-md-4 col-sm-6 col-6 mb-2">
                                    <div class="info-box kpi-dark-card shadow-xs p-2 mb-0 d-flex align-items-center">
                                        <span class="info-box-icon elevation-0 d-flex align-items-center justify-content-center mr-2" style="width: 36px; height: 36px; border-radius: 6px; font-size: 15px;">
                                            <i class="fa fa-money"></i>
                                        </span>
                                        <div class="info-box-content p-0" style="line-height: 1.2;">
                                            <span class="info-box-text text-uppercase" style="font-size: 10.5px;">Gross Fee</span>
                                            <span class="info-box-number" id="summary_card_gross" style="font-size: 15px;">₹0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Total Discount Granted -->
                                <div class="col-xl-2 col-md-4 col-sm-6 col-6 mb-2">
                                    <div class="info-box shadow-xs p-2 mb-0 d-flex align-items-center" style="border: 1.5px solid #fde68a; background-color: #fffbeb !important; border-radius: 6px; min-height: 58px;">
                                        <span class="info-box-icon elevation-0 d-flex align-items-center justify-content-center mr-2" style="background: #d97706; color: #ffffff !important; width: 36px; height: 36px; border-radius: 6px; font-size: 15px;">
                                            <i class="fa fa-tags"></i>
                                        </span>
                                        <div class="info-box-content p-0" style="line-height: 1.2;">
                                            <span class="info-box-text font-weight-bold text-uppercase" style="font-size: 10.5px; color: #b45309 !important;">Discounts</span>
                                            <span class="info-box-number font-weight-bold" id="summary_card_discount" style="font-size: 15px; color: #dc2626 !important;">₹0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Net Assigned Fee -->
                                <div class="col-xl-2 col-md-4 col-sm-6 col-6 mb-2">
                                    <div class="info-box shadow-xs p-2 mb-0 d-flex align-items-center" style="border: 1.5px solid #bae6fd; background-color: #f0f9ff !important; border-radius: 6px; min-height: 58px;">
                                        <span class="info-box-icon elevation-0 d-flex align-items-center justify-content-center mr-2" style="background: #0284c7; color: #ffffff !important; width: 36px; height: 36px; border-radius: 6px; font-size: 15px;">
                                            <i class="fa fa-file-text-o"></i>
                                        </span>
                                        <div class="info-box-content p-0" style="line-height: 1.2;">
                                            <span class="info-box-text font-weight-bold text-uppercase" style="font-size: 10.5px; color: #0369a1 !important;">Net Fee</span>
                                            <span class="info-box-number font-weight-bold" id="summary_card_net" style="font-size: 15px; color: #0284c7 !important;">₹0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Total Collected / Paid -->
                                <div class="col-xl-2 col-md-4 col-sm-6 col-6 mb-2">
                                    <div class="info-box shadow-xs p-2 mb-0 d-flex align-items-center" style="border: 1.5px solid #bbf7d0; background-color: #f0fdf4 !important; border-radius: 6px; min-height: 58px;">
                                        <span class="info-box-icon elevation-0 d-flex align-items-center justify-content-center mr-2" style="background: #16a34a; color: #ffffff !important; width: 36px; height: 36px; border-radius: 6px; font-size: 15px;">
                                            <i class="fa fa-check-circle"></i>
                                        </span>
                                        <div class="info-box-content p-0" style="line-height: 1.2;">
                                            <span class="info-box-text font-weight-bold text-uppercase" style="font-size: 10.5px; color: #15803d !important;">Collected</span>
                                            <span class="info-box-number font-weight-bold" id="summary_card_paid" style="font-size: 15px; color: #16a34a !important;">₹0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Total Due / Pending -->
                                <div class="col-xl-2 col-md-4 col-sm-6 col-6 mb-2">
                                    <div class="info-box shadow-xs p-2 mb-0 d-flex align-items-center" style="border: 1.5px solid #fecaca; background-color: #fef2f2 !important; border-radius: 6px; min-height: 58px;">
                                        <span class="info-box-icon elevation-0 d-flex align-items-center justify-content-center mr-2" style="background: #dc2626; color: #ffffff !important; width: 36px; height: 36px; border-radius: 6px; font-size: 15px;">
                                            <i class="fa fa-clock-o"></i>
                                        </span>
                                        <div class="info-box-content p-0" style="line-height: 1.2;">
                                            <span class="info-box-text font-weight-bold text-uppercase" style="font-size: 10.5px; color: #b91c1c !important;">Pending Due</span>
                                            <span class="info-box-number font-weight-bold" id="summary_card_pending" style="font-size: 15px; color: #dc2626 !important;">₹0</span>
                                            <small id="summary_card_excess_wrapper" style="font-size: 10px; color: #047857; display: none; font-weight: 700;">Adv: <span id="summary_card_excess">+₹0</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- High Density Students Table Container -->
                            <div class="table-responsive border rounded" id="students_assign_scroll">
                                <table class="table table-bordered table-striped table-hover table-sm mb-0 table-assign" id="students_assign_table">
                                    <thead style="position: sticky; top: 0; z-index: 10;">
                                        <tr>
                                            <th class="col-fixed-check" style="width: 40px; text-align: center; vertical-align: middle;">
                                                <input type="checkbox" id="all_students" style="cursor: pointer;" title="Select All">
                                            </th>
                                            <th class="col-fixed-sr" style="width: 40px; text-align: center; vertical-align: middle;">#</th>
                                            <th class="col-fixed-student" style="width:230px; text-align:left; vertical-align:middle;">Student / Admission / Mobile</th>
                                            <th style="width:210px; text-align: center; vertical-align: middle;">Course / Batch</th>
                                            <th style="width:170px; text-align: left; vertical-align: middle;">Father's Name</th>
                                            <th style="width: 570px; text-align: center; vertical-align: middle;">Semester Fee & Receipt Details</th>
                                            <th style="width: 170px; text-align: center; vertical-align: middle;" class="col-remark">Remark</th>
                                            <th style="width: 135px; text-align: center; vertical-align: middle;" class="col-actions">Actions</th>
                                            <th class="col-fixed-balance" style="width: 125px; text-align: center; vertical-align: middle;">Outstanding / Excess</th>
                                        </tr>
                                        <tr class="excel-filter-row">
                                            <th class="col-fixed-check"></th>
                                            <th class="col-fixed-sr"></th>
                                            <th class="col-fixed-student">
                                                <span class="grid-filter-caption">Quick Student Search</span>
                                                <input type="text" id="grid_filter_student_lookup" class="form-control" placeholder="Name / admission / mobile">
                                            </th>
                                            <th>
                                                <span class="grid-filter-caption">Course / Batch</span>
                                                <div class="d-flex" style="gap:3px">
                                                    <select id="grid_filter_course" class="form-control" style="width:60%">
                                                        <option value="">All Courses</option>
                                                        @foreach($courses as $course)
                                                            <option value="{{ $course->id }}">{{ $course->name ?? '' }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select id="grid_filter_batch" class="form-control" style="width:40%">
                                                        <option value="">All Batches</option>
                                                        @foreach($batches as $batch)
                                                            <option value="{{ $batch->name ?? '' }}">{{ $batch->name ?? '' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="grid-filter-caption mb-0">Father / Gender</span>
                                                </div>
                                                <div class="d-flex" style="gap:3px">
                                                    <input type="text" id="grid_filter_father_name" class="form-control" placeholder="Search father" style="width:60%">
                                                    <select id="grid_filter_gender" class="form-control" style="width:40%; font-size:11px; padding:2px;">
                                                        <option value="">All</option>
                                                        <option value="1">👦 Boy</option>
                                                        <option value="2">👧 Girl</option>
                                                    </select>
                                                </div>
                                            </th>
                                            <th class="text-center"></th>
                                            <th class="col-remark">
                                                <span class="grid-filter-caption">Remark Search</span>
                                                <input type="text" id="grid_filter_remark" class="form-control" placeholder="Search remark">
                                            </th>
                                            <th class="col-actions align-middle text-center" style="padding: 2px 4px !important; width: 135px; min-width: 135px; max-width: 135px; box-sizing: border-box;">
                                                <button type="button" 
                                                        id="grid_reset_filters" 
                                                        class="btn btn-grid-reset w-100" 
                                                        style="background-color: #dc2626 !important; background: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important; font-weight: 800 !important; height: 24px !important;"
                                                        title="Reset all spreadsheet filters (Search, Father, Gender, Remark, Balance status)">
                                                    <i class="fa fa-refresh mr-1" style="color: #ffffff !important;"></i> <span style="color: #ffffff !important; font-weight: 800 !important;">Reset Filters</span>
                                                </button>
                                            </th>
                                            <th class="col-fixed-balance align-middle" style="padding: 2px 2px !important; width: 125px; min-width: 125px; max-width: 125px; box-sizing: border-box;">
                                                <div class="balance-filter-group" role="group" aria-label="Balance Status Quick Filter">
                                                    <button type="button" 
                                                            class="btn-status-toggle active" 
                                                            data-status="dues" 
                                                            id="btn_filter_dues"
                                                            title="Filter Pending Dues (Click to toggle)">
                                                        Dues
                                                    </button>
                                                    <button type="button" 
                                                            class="btn-status-toggle active" 
                                                            data-status="excess" 
                                                            id="btn_filter_excess"
                                                            title="Filter Excess Amount (Click to toggle)">
                                                        Excess
                                                    </button>
                                                    <button type="button" 
                                                            class="btn-status-toggle active" 
                                                            data-status="cleared" 
                                                            id="btn_filter_cleared"
                                                            title="Filter Cleared / Zero Balance (Click to toggle)">
                                                        Clear
                                                    </button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_students_list">
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fa fa-filter fa-2x mb-2 text-secondary d-block"></i>
                                                Please select both <b>Course</b> and <b>Batch</b> above to view and assign students.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr id="tfoot_summary_row">
                                            <td class="col-fixed-check"></td>
                                            <td class="col-fixed-sr"></td>
                                            <td class="col-fixed-student text-dark font-weight-bold align-middle px-2">
                                                <i class="fa fa-calculator mr-1"></i><span id="tfoot_summary_label">TOTAL (0 Students)</span>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td class="align-middle px-2">
                                                <div class="ledger-footer-totals">
                                                    <span class="ledger-footer-discount"><small>Discount</small><strong id="tfoot_total_discount">-₹0</strong></span>
                                                    <span><small>Fee</small><strong id="tfoot_total_gross">₹0</strong></span>
                                                    <span class="ledger-footer-paid"><small>Paid</small><strong id="tfoot_total_paid">₹0</strong></span>
                                                    <span class="ledger-footer-due"><small>Dues</small><strong id="tfoot_total_dues">₹0</strong></span>
                                                    <span></span>
                                                    <span></span>
                                                    <span></span>
                                                    <span class="ledger-footer-net"><small>Net</small><strong id="tfoot_total_net">₹0</strong></span>
                                                </div>
                                            </td>
                                            <td class="col-remark"></td>
                                            <td class="text-center align-middle col-actions">
                                                <button type="button" class="btn btn-xs btn-link p-0" onclick="recalculatePagePaymentSummary()" title="Recalculate totals"><i class="fa fa-refresh"></i></button>
                                            </td>
                                            <td class="text-center align-middle col-fixed-balance ledger-footer-outstanding">
                                                <div style="line-height: 1.2;">
                                                    <small class="d-block text-muted" style="font-size: 9px; font-weight: 600;">Outstanding</small>
                                                    <strong id="tfoot_total_pending" class="text-danger" style="font-size: 13px;">₹0</strong>
                                                </div>
                                                <div id="tfoot_excess_wrapper" class="mt-1" style="display: none; line-height: 1.1; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                                                    <small class="d-block text-success font-weight-bold" style="font-size: 9px;">Advance</small>
                                                    <strong id="tfoot_total_excess" class="text-success" style="font-size: 12px;">+₹0</strong>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Dedicated Bulk Assignment Modal -->
<!-- Dedicated Modal Styling (Matching Promote Students Excel layout) -->
<style>
/* ========================================================
   MODAL THEME (Matching Promote Students Excel layout)
   ======================================================== */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 2px !important;
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
    letter-spacing: 0.2px;
}
.modal-title i {
    color: #217346 !important;
    font-size: 14px !important;
    margin-right: 6px;
}
.modal-title i.text-danger {
    color: #dc2626 !important;
}
.modal-title i.text-success {
    color: #166534 !important;
}
.modal-title i.text-info {
    color: #0369a1 !important;
}
.modal-header .close,
.modal-header .btn-close-modal {
    margin: 0 !important;
    padding: 2px 6px !important;
    color: #344450 !important;
    opacity: 0.9 !important;
    font-size: 20px !important;
    line-height: 1 !important;
    background: transparent !important;
    border: 0 !important;
    cursor: pointer !important;
    text-shadow: none !important;
    transition: all 0.15s ease-in-out;
}
.modal-header .close:hover,
.modal-header .btn-close-modal:hover {
    color: #000000 !important;
    opacity: 1 !important;
}
.modal-body {
    padding: 12px 14px !important;
    background: #ffffff !important;
}
.modal-footer {
    display: flex !important;
    align-items: center;
    justify-content: flex-end !important;
    gap: 7px;
    padding: 7px 12px !important;
    background: #f1f4f6 !important;
    border-top: 1px solid #aebbc5 !important;
}

/* Modal Standard Buttons */
.btn-modal-cancel {
    height: 30px !important;
    margin: 0 !important;
    padding: 3px 14px !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #ffffff !important;
    color: #52616d !important;
    border: 1px solid #9eacb7 !important;
    transition: all 0.15s ease-in-out;
}
.btn-modal-cancel:hover {
    background: #e9edef !important;
    color: #1e2d39 !important;
    border-color: #7b8b98 !important;
}
.btn-modal-submit {
    height: 30px !important;
    margin: 0 !important;
    padding: 3px 14px !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #217346 !important;
    border: 1px solid #185c37 !important;
    color: #ffffff !important;
    transition: all 0.15s ease-in-out;
}
.btn-modal-submit:hover {
    background: #195b37 !important;
    border-color: #134629 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.3);
}
.btn-modal-danger {
    height: 30px !important;
    margin: 0 !important;
    padding: 3px 14px !important;
    border-radius: 2px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    background: #dc2626 !important;
    border: 1px solid #b91c1c !important;
    color: #ffffff !important;
    transition: all 0.15s ease-in-out;
}
.btn-modal-danger:hover {
    background: #b91c1c !important;
    border-color: #991b1b !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.3);
}

/* Chips & Badges from promote page */
.sheet-chip {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 2px;
    font-size: 10.5px;
    font-weight: 600;
    line-height: 1.2;
}
.sheet-chip-course {
    background-color: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.sheet-chip-batch {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}

/* Bulk fee heads table */
#bulk_fee_heads_table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 11.5px;
}
#bulk_fee_heads_table th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #d9eaf7 !important;
    color: #152536 !important;
    border-right: 1px solid #8fa7b8;
    border-bottom: 2px solid #8fa7b8;
    padding: 6px 8px;
    font-weight: 700;
    text-align: center;
}
#bulk_fee_heads_table td {
    padding: 5px 8px;
    border-right: 1px solid #c6cdd3;
    border-bottom: 1px solid #c6cdd3;
    vertical-align: middle;
}
#bulk_fee_heads_table tbody tr:nth-child(even) td {
    background: #fafafa;
}
#bulk_fee_heads_table tbody tr:hover td {
    background: #fffde7 !important;
}

/* Multi-modal Overlay / Stacked Dimming */
.modal.modal-under-overlay {
    filter: brightness(0.48) contrast(0.92) !important;
    pointer-events: none !important;
    transition: filter 0.2s ease-in-out;
}
.modal.modal-under-overlay .modal-content {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.45) !important;
}
.modal-backdrop.modal-stack {
    opacity: 0.65 !important;
}
</style>

<!-- Bulk Fee Assign Modal (Promote layout) -->
<div class="modal fade" id="bulkFeeAssignModal" tabindex="-1" role="dialog" aria-labelledby="bulkFeeAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 820px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title font-weight-bold" id="bulkFeeAssignModalLabel">
                        <i class="fa fa-check-square-o text-success mr-2"></i>Bulk Assign Current Semester Fees
                    </h5>
                    <small style="color: #64748b; font-size: 10.5px; display: block; margin-top: 2px;">
                        Course heads are listed class-wise; assignment applies only to each student's current class.
                    </small>
                </div>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="p-2 mb-3 rounded d-flex justify-content-between align-items-center" style="background: #e7eff5; border: 1px solid #b8c9d9; font-size: 11.5px; color: #1e3a5f;">
                    <div>
                        <i class="fa fa-users text-primary mr-1"></i>
                        <span class="sheet-chip sheet-chip-batch mr-2">
                            <strong id="bulk_modal_student_count">0</strong> student(s) selected
                        </span>
                        <span id="bulk_modal_class_summary" class="font-weight-bold text-dark"></span>
                    </div>
                    <span class="small text-muted" style="font-size: 10.5px;">
                        <i class="fa fa-info-circle mr-1 text-primary"></i> Assignment applies to current semester
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-dark" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fa fa-tags text-primary mr-1"></i> Select Fee Heads to Assign:
                    </span>
                    <div>
                        <button type="button" class="btn btn-xs btn-outline-primary px-2" id="bulk_select_all_heads" style="height: 24px; font-size: 11px; border-radius: 2px;">
                            <i class="fa fa-check-square mr-1"></i> Select all
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary px-2 ml-1" id="bulk_clear_all_heads" style="height: 24px; font-size: 11px; border-radius: 2px;">
                            <i class="fa fa-square-o mr-1"></i> Clear
                        </button>
                    </div>
                </div>

                <div id="bulk_fee_heads_box" class="table-responsive border" style="min-height: 90px; max-height: 320px; overflow-y: auto;">
                    <div class="text-center text-muted py-3">
                        <i class="fa fa-spinner fa-spin mr-1"></i> Loading current semester heads...
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mt-2 mb-0 d-flex align-items-start" style="font-size: 11px; border-radius: 2px; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569;">
                    <i class="fa fa-shield text-success mr-2 mt-1" style="font-size: 13px; flex-shrink: 0;"></i>
                    <div>
                        <strong>Safe Assignment:</strong> Already assigned heads will remain unchanged; missing selected heads will be added in real time without duplicating.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-modal-submit font-weight-bold" id="btn_modal_assign_selected">
                    <i class="fa fa-check-circle mr-1"></i> Assign Selected Heads
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Student Fee Details & Modification Modal -->
<div class="modal fade" id="studentFeeEditModal" tabindex="-1" role="dialog" aria-labelledby="studentFeeEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg" id="studentFeeEditModalBody">
            <div class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2 text-muted font-weight-bold">Loading Student Fee Details...</p>
            </div>
        </div>
    </div>
</div>

<!-- Custom Theme Confirmation Modal for Fee Assign / Clear Actions -->
<div class="modal fade" id="overallStudentLedgerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" id="overallStudentLedgerBody"></div>
    </div>
</div>

<!-- Custom Theme Confirmation Modal for Fee Assign / Clear Actions (Promote Layout) -->
<div class="modal fade" id="feeAssignConfirmModal" tabindex="-1" role="dialog" aria-labelledby="feeAssignConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
        <div class="modal-content">
            <!-- Modal Header (Promote style) -->
            <div class="modal-header" id="feeConfirmModalHeader">
                <h5 class="modal-title font-weight-bold" id="feeAssignConfirmModalLabel">
                    <i class="fa fa-users text-success mr-2" id="feeConfirmIcon"></i>
                    <span id="feeConfirmTitle">Assign Selected Fee Heads</span>
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <!-- Modal Body (Promote style) -->
            <div class="modal-body p-3">
                <!-- Top Row: 38px Circle Icon + Headline & Subtitle -->
                <div class="d-flex align-items-start mb-3">
                    <div id="feeConfirmIconWrapper" class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-users text-success" id="feeConfirmBigIcon" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;" id="feeConfirmHeadline">Assign Selected Fee Heads</strong>
                        <span class="text-muted" style="font-size: 11.5px;" id="feeConfirmMessage">Please review the parameters below before confirming:</span>
                    </div>
                </div>

                <!-- Parameters Summary Box (Promote style) -->
                <div class="p-2 mb-3 rounded" id="feeConfirmStudentInfoBox" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom" id="feeConfirmStudentNameRow">
                        <span class="text-muted">Student Name:</span>
                        <strong class="text-dark" id="feeConfirmStudentName">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom" id="feeConfirmStudentAdmRow">
                        <span class="text-muted">Admission No:</span>
                        <strong class="text-dark" id="feeConfirmStudentAdmNo">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1" id="feeConfirmExtraRow">
                        <span class="text-muted">Target / Action:</span>
                        <span class="font-weight-bold text-dark" id="feeConfirmExtraDetails">-</span>
                    </div>
                </div>

                <!-- Alert Box (Promote style) -->
                <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-start" style="font-size: 11px; border-radius: 2px; background: #e7eff5; border: 1px solid #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-2 mt-1" style="font-size: 13px; flex-shrink: 0;"></i>
                    <div>
                        <strong>Real-time Update:</strong> Fee heads will be assigned to student ledger balances immediately in real time.
                    </div>
                </div>
                <div class="alert alert-warning py-2 px-3 mt-2 mb-0 d-none d-flex align-items-start" id="feeConfirmPaidWarning" style="font-size: 11px; border-radius: 2px; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e;">
                    <i class="fa fa-shield mr-2 mt-1" style="font-size: 13px; flex-shrink: 0;"></i>
                    <div>
                        <strong>Protection:</strong> Fee heads with already recorded payments will remain untouched.
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Promote style) -->
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-modal-submit font-weight-bold" id="btn_confirm_fee_modal_action">
                    <i class="fa fa-check mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Proceed</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Alert Modal: Cannot Clear Fee Heads (Fees Already Collected) -->
<div class="modal fade" id="feeCollectedWarningModal" tabindex="-1" role="dialog" aria-labelledby="feeCollectedWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 660px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="feeCollectedWarningModalLabel">
                    <i class="fa fa-ban text-danger mr-2"></i> Cannot Clear Fee Heads: Fees Already Collected
                </h5>
                <button type="button" class="close btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <!-- Top Circle Icon & Headline -->
                <div class="d-flex align-items-start mb-3">
                    <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-shield text-danger" style="font-size: 18px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Fee Clearing Blocked</strong>
                        <span class="text-muted" style="font-size: 11.5px;">Student payment receipts are actively recorded for this student in the database.</span>
                    </div>
                </div>

                <!-- Alert Notice Box -->
                <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-start" style="font-size: 11.5px; border-radius: 2px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
                    <i class="fa fa-exclamation-triangle mr-2 mt-1" style="font-size: 14px; flex-shrink: 0;"></i>
                    <div>
                        <strong class="d-block" style="font-size: 12px; font-weight: 700;">Fee Revert / Refund Required First</strong>
                        Fee payments have already been collected for this student. You must first refund/revert all collected receipts before clearing or unassigning fee heads.
                    </div>
                </div>

                <!-- Student Info Card -->
                <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Student:</span>
                        <strong class="text-dark" id="collected_warn_student_name">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Admission No:</span>
                        <strong class="text-dark" id="collected_warn_admission_no">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Class / Batch:</span>
                        <span class="sheet-chip sheet-chip-batch" id="collected_warn_class_batch">-</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted">Total Paid Amount:</span>
                        <strong class="text-danger font-weight-bold" style="font-size: 13px;" id="collected_warn_total_paid">₹0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="text-muted">Audit Protection Status:</span>
                        <span class="sheet-chip" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                            <i class="fa fa-lock mr-1"></i> Locked &bull; Protected Record
                        </span>
                    </div>
                </div>

                <!-- Receipts Table -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-weight-bold text-dark" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                        Collected Fee Receipts &amp; Invoices
                    </span>
                    <span class="sheet-chip sheet-chip-batch" style="font-size: 10px;">
                        <i class="fa fa-lock text-danger mr-1"></i> Protected Receipts
                    </span>
                </div>
                <div class="table-responsive border" style="max-height: 190px; overflow-y: auto;">
                    <table class="table table-bordered table-sm table-hover mb-0 text-center table-collected-invoices" style="font-size: 11.5px; border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr style="background: #d9eaf7 !important; color: #152536 !important; border-bottom: 2px solid #8fa7b8;">
                                <th style="width: 40px; border-color: #8fa7b8; font-weight: 700;">#</th>
                                <th style="border-color: #8fa7b8; font-weight: 700;">Invoice No.</th>
                                <th class="text-left" style="border-color: #8fa7b8; font-weight: 700;">Fee Head(s)</th>
                                <th style="width: 100px; border-color: #8fa7b8; font-weight: 700;">Paid Amount</th>
                                <th style="width: 110px; border-color: #8fa7b8; font-weight: 700;">Date &amp; Mode</th>
                                <th style="width: 90px; border-color: #8fa7b8; font-weight: 700;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="collected_warn_invoices_tbody">
                            <!-- Invoices loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <a href="{{ url('Fees/add') }}" id="btn_goto_fee_collect" target="_blank" class="btn btn-sm btn-modal-danger font-weight-bold d-inline-flex align-items-center" style="text-decoration: none;">
                    <i class="fa fa-arrow-circle-right mr-1"></i> Go to Fee Revert / Collect Form
                </a>
                <button type="button" class="btn btn-modal-cancel btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="fa fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var searchTimer = null;
var loadedFeeMasters = [];

function renderStudentBalanceCellHtml(admissionId, pending, excess) {
    var p = parseFloat(pending) || 0;
    var e = parseFloat(excess) || 0;
    if (p > 0) {
        return '<span class="d-block text-muted font-weight-600" style="font-size:10px;">Outstanding</span>' +
            '<strong id="student_balance_' + admissionId + '" class="text-danger d-block font-weight-bold" style="font-size:14px; line-height: 1.2;">₹' + Math.round(p).toLocaleString('en-IN') + '</strong>' +
            '<span class="badge badge-danger px-1.5 py-0.5 mt-1" style="font-size: 9px; font-weight: 700;">Pending</span>';
    } else if (e > 0) {
        return '<span class="d-block font-weight-700 text-uppercase" style="font-size:9.5px; color:#047857;">Excess / Adv.</span>' +
            '<strong id="student_balance_' + admissionId + '" class="text-success d-block font-weight-800" style="font-size:14px; line-height: 1.2;">+₹' + Math.round(e).toLocaleString('en-IN') + '</strong>' +
            '<span class="badge badge-success px-1.5 py-0.5 mt-1" style="font-size: 9px; font-weight: 700; background-color: #10b981;">Advance Paid</span>';
    } else {
        return '<span class="d-block text-muted font-weight-600" style="font-size:10px;">Balance</span>' +
            '<strong id="student_balance_' + admissionId + '" class="text-secondary d-block font-weight-bold" style="font-size:14px; line-height: 1.2;">₹0</strong>' +
            '<span class="badge badge-light border text-success px-1.5 py-0.5 mt-1" style="font-size: 9px; font-weight: 600;">Cleared</span>';
    }
}

function renderStudentFeeSummaryHtml(admissionId, gross, discount, net, paid, pending, excess) {
    gross = parseFloat(gross) || 0;
    discount = parseFloat(discount) || 0;
    net = (net !== undefined && net !== null) ? parseFloat(net) : Math.max(0, gross - discount);
    paid = parseFloat(paid) || 0;
    pending = (pending !== undefined && pending !== null) ? parseFloat(pending) : Math.max(0, net - paid);
    excess = (excess !== undefined && excess !== null) ? parseFloat(excess) : Math.max(0, paid - net);
    
    var grossFormatted = '₹' + (gross % 1 === 0 ? gross.toLocaleString('en-IN') : gross.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var discFormatted = '₹' + (discount % 1 === 0 ? discount.toLocaleString('en-IN') : discount.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var netFormatted = '₹' + (net % 1 === 0 ? net.toLocaleString('en-IN') : net.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var paidFormatted = '₹' + (paid % 1 === 0 ? paid.toLocaleString('en-IN') : paid.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var pendingFormatted = '₹' + (pending % 1 === 0 ? pending.toLocaleString('en-IN') : pending.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    var excessFormatted = '₹' + (excess % 1 === 0 ? excess.toLocaleString('en-IN') : excess.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    
    var html = '<div class="fee-summary-cell d-flex flex-column align-items-center justify-content-center" id="student_fee_summary_' + admissionId + '" style="gap: 2.5px;">' +
        '<div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11.5px;" title="Gross Assigned Amount">' +
            '<span class="text-muted font-weight-600">Gross:</span>' +
            '<span class="text-dark font-weight-bold">' + grossFormatted + '</span>' +
        '</div>';
    
    if (discount > 0) {
        html += '<div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #fffbeb; border-radius: 4px; border: 1px solid #fef3c7; font-size: 11.5px;" title="Discount Granted">' +
            '<span class="font-weight-600" style="color: #d97706 !important;"><i class="fa fa-tag mr-1"></i>Disc:</span>' +
            '<span class="text-danger font-weight-bold">-' + discFormatted + '</span>' +
        '</div>';
    }
    
    html += '<div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #e0f2fe; border-radius: 4px; border: 1px solid #bae6fd; font-size: 12px;" title="Net Payable Amount" id="student_net_' + admissionId + '">' +
        '<span class="text-primary font-weight-700">Net:</span>' +
        '<span class="text-primary font-weight-bold">' + netFormatted + '</span>' +
    '</div>' +
    '<div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #f0fdf4; border-radius: 4px; border: 1px solid #bbf7d0; font-size: 11.5px;" title="Total Collected Payment">' +
        '<span class="text-success font-weight-600"><i class="fa fa-check-circle mr-1"></i>Paid:</span>' +
        '<span class="text-success font-weight-bold">' + paidFormatted + '</span>' +
    '</div>';
    
    if (pending > 0) {
        html += '<div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #fef2f2; border-radius: 4px; border: 1px solid #fecaca; font-size: 11.5px;" title="Remaining Pending Due">' +
            '<span class="text-danger font-weight-700"><i class="fa fa-clock-o mr-1"></i>Due:</span>' +
            '<span class="text-danger font-weight-bold">' + pendingFormatted + '</span>' +
        '</div>';
    } else if (excess > 0) {
        html += '<div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #ecfdf5; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 11.5px;" title="Advance / Excess Amount Paid">' +
            '<span class="text-success font-weight-700"><i class="fa fa-arrow-circle-up mr-1"></i>Advance:</span>' +
            '<span class="text-success font-weight-bold">+' + excessFormatted + '</span>' +
        '</div>';
    } else if (gross > 0) {
        html += '<div class="d-flex justify-content-center align-items-center w-100 px-2 py-0.5" style="background: #dcfce7; border-radius: 4px; border: 1px solid #86efac; font-size: 11px;">' +
            '<span class="text-success font-weight-bold"><i class="fa fa-check mr-1"></i>Fully Cleared</span>' +
        '</div>';
    }
    
    html += '</div>';
    return html;
}

function updateStudentFeeSummary(admissionId, gross, discount, net, paid) {
    var $row = $('#student_row_' + admissionId);
    if ($row.length > 0) {
        if (paid === undefined || paid === null) {
            paid = parseFloat($row.attr('data-paid')) || 0;
        }
        var g = parseFloat(gross) || 0;
        var d = parseFloat(discount) || 0;
        var n = (net !== undefined && net !== null) ? parseFloat(net) : Math.max(0, g - d);
        var p = parseFloat(paid) || 0;
        var due = Math.max(0, n - p);
        var excess = Math.max(0, p - n);

        $row.attr('data-gross', g);
        $row.attr('data-discount', d);
        $row.attr('data-net', n);
        $row.attr('data-paid', p);
        $row.attr('data-pending', due);
        $row.attr('data-excess', excess);

        var $td = $('#student_summary_td_' + admissionId);
        if ($td.length > 0) {
            var $summary = $td.find('.fee-summary-cell');
            if ($summary.length) {
                $summary.replaceWith(renderStudentFeeSummaryHtml(admissionId, g, d, n, p, due, excess));
            }
        }
        var $balCell = $('#student_balance_cell_' + admissionId);
        if ($balCell.length > 0) {
            $balCell.html(renderStudentBalanceCellHtml(admissionId, due, excess));
        }
    }
    recalculatePagePaymentSummary();
}

function recalculatePagePaymentSummary() {
    var totalStudents = 0;
    var totalGross = 0;
    var totalDiscount = 0;
    var totalNet = 0;
    var totalPaid = 0;
    var totalPending = 0;
    var totalExcess = 0;

    var hasActiveClientFilter = !!($('#grid_filter_remark').val() || $('.btn-status-toggle:not(.active)').length > 0);
    var $rows = $('.student-fee-row:visible');
    if ($rows.length === 0 && !hasActiveClientFilter) {
        $rows = $('.student-fee-row');
    }

    $rows.each(function() {
        totalStudents++;
        var g = parseFloat($(this).attr('data-gross')) || 0;
        var d = parseFloat($(this).attr('data-discount')) || 0;
        var c = parseFloat($(this).attr('data-carry-adjustment')) || 0;
        var parsedNet = parseFloat($(this).attr('data-net'));
        var n = Number.isNaN(parsedNet) ? Math.max(0, g - d - c) : parsedNet;
        var p = parseFloat($(this).attr('data-paid')) || 0;
        var due = parseFloat($(this).attr('data-pending'));
        if (Number.isNaN(due)) {
            due = Math.max(0, n - p);
        }
        var exc = parseFloat($(this).attr('data-excess'));
        if (Number.isNaN(exc)) {
            exc = Math.max(0, p - n);
        }

        totalGross += g;
        totalDiscount += d;
        totalNet += n;
        totalPaid += p;
        totalPending += due;
        totalExcess += exc;
    });

    var formatCur = function(num) {
        return '₹' + (num % 1 === 0 ? num.toLocaleString('en-IN') : num.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    };

    // Update KPI Deck Cards
    $('#summary_card_students').text(totalStudents + (totalStudents === 1 ? ' Student' : ' Students'));
    $('#summary_card_gross').text(formatCur(totalGross));
    $('#summary_card_discount').text(formatCur(totalDiscount));
    $('#summary_card_net').text(formatCur(totalNet));
    $('#summary_card_paid').text(formatCur(totalPaid));
    $('#summary_card_pending').text(formatCur(totalPending));
    if (totalExcess > 0) {
        $('#summary_card_excess').text('+' + formatCur(totalExcess));
        $('#summary_card_excess_wrapper').show();
    } else {
        $('#summary_card_excess_wrapper').hide();
    }

    // Update Table Footer Values if footer exists
    $('#tfoot_summary_label').text('TOTAL (' + totalStudents + ' Students)');
    $('#tfoot_total_gross').text(formatCur(totalGross));
    $('#tfoot_total_discount').text('-' + formatCur(totalDiscount));
    $('#tfoot_total_net').text(formatCur(totalNet));
    $('#tfoot_total_paid').text(formatCur(totalPaid));
    $('#tfoot_total_dues').text(formatCur(totalPending));
    $('#tfoot_total_pending').text(formatCur(totalPending));
    if (totalExcess > 0) {
        $('#tfoot_total_excess').text('+' + formatCur(totalExcess));
        $('#tfoot_excess_wrapper').show();
    } else {
        $('#tfoot_excess_wrapper').hide();
    }
}

function resizeStudentAssignTable() {
    var container = document.getElementById('students_assign_scroll');
    if (!container) return;
    var headingRow = document.querySelector('#students_assign_table thead tr:first-child');
    var headingHeight = headingRow ? headingRow.getBoundingClientRect().height : 37;
    document.querySelectorAll('#students_assign_table .excel-filter-row th').forEach(function(cell) {
        cell.style.top = headingHeight + 'px';
    });
    var top = container.getBoundingClientRect().top;
    var availableHeight = Math.floor(window.innerHeight - top - 18);
    var tableHeight = Math.max(280, availableHeight);
    container.style.height = tableHeight + 'px';
    container.style.maxHeight = tableHeight + 'px';
}

function syncSpreadsheetFilters() {
    $('#grid_filter_course').val($('#filter_course_id').val());
    $('#grid_filter_batch').val($('#filter_batch').val());
    var gridUrlParams = new URLSearchParams(window.location.search);
    $('#grid_filter_student_lookup').val(gridUrlParams.get('student_lookup') || $('#filter_admission_no').val() || '');
    $('#grid_filter_father_name').val(gridUrlParams.get('father_name') || '');
    $('#grid_filter_gender').val($('#filter_gender_id').val() || gridUrlParams.get('gender_id') || '');
}

function updateLedgerHeadingContext() {
    var course = $('#filter_course_id option:selected').text().trim();
    var batch = $('#filter_batch').val();
    var semester = $('#tbody_students_list .student-fee-row:first').data('class-name') || '';
    var parts = [];
    if (course && !course.match(/^--/)) parts.push(course);
    if (semester) parts.push(semester);
    if (batch) parts.push('Batch ' + batch);
    $('#ledger_active_context').text(parts.length ? '— ' + parts.join(' · ') : '');
}

function showFeeToast(msg, type = 'success') {
    var bgClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    var toastHtml = '<div class="alert ' + bgClass + ' alert-dismissible shadow-sm py-2 px-3 mb-2" style="font-size: 12.5px; border-radius: 6px;">' +
        '<button type="button" class="close" data-dismiss="alert" style="font-size: 16px;">&times;</button>' +
        '<i class="fa ' + icon + ' mr-1"></i> ' + msg +
        '</div>';
    
    var $toast = $(toastHtml).appendTo('#fee_toast_box');
    setTimeout(function() {
        $toast.fadeOut(400, function() { $(this).remove(); });
    }, 3500);
}

function updateSelectedHeadsBadge() {
    var selectedCount = $('.top-head-checkbox:checked').length;
    var checkedStudentsCount = $('.student_select_checkbox:checked').length;

    if (selectedCount > 0) {
        $('#selected_heads_count').text(selectedCount);
        $('#selected_heads_badge').show();
        $('#btn_bulk_assign_realtime').html('<i class="fa fa-check-circle mr-1"></i> Assign (' + selectedCount + ') Selected Head(s) to Checked (<span class="selected_students_count_text">' + checkedStudentsCount + '</span>)');
    } else {
        $('#selected_heads_badge').hide();
        $('#btn_bulk_assign_realtime').html('<i class="fa fa-check-circle mr-1"></i> Assign to Selected Students (<span class="selected_students_count_text">' + checkedStudentsCount + '</span>)');
    }
}

function updateSelectedCount() {
    var checked = $('.student_select_checkbox:checked').length;
    var total = $('.student_select_checkbox').length;
    
    $('#selected_students_badge').text('Selected: ' + checked);
    $('.selected_students_count_text').text(checked);
    $('#total_students_badge').text('Total: ' + total);
    $('.total_students_count_text').text(total);
    
    if (total > 0 && checked === total) {
        $('#all_students').prop('checked', true);
    } else {
        $('#all_students').prop('checked', false);
    }

    updateSelectedHeadsBadge();
}

function loadStudents() {
    var session_id = '{{ Session::get('session_id') }}';
    var course_id = $('#filter_course_id').val();
    var class_type_id = $('#filter_class_type_id').val();
    var batch = $('#filter_batch').val();
    var student_lookup = $('#grid_filter_student_lookup').val();
    var father_name = $('#grid_filter_father_name').val();
    var gender_id = $('#filter_gender_id').val() || $('#grid_filter_gender').val();
    
    if ((!course_id || !batch) && !student_lookup) {
        var msg = 'Please select both <b>Course</b> and <b>Batch</b> above to view and assign students.';
        if (!course_id && !batch) {
            msg = 'Please select a <b>Course</b> and <b>Batch</b> above to view students.';
        } else if (!course_id) {
            msg = 'Please select a <b>Course</b> above.';
        } else if (!batch) {
            msg = 'Please select a <b>Batch</b> above to load student list.';
        }
        $('#tbody_students_list').html('<tr><td colspan="9" class="text-center py-5 text-muted"><i class="fa fa-filter fa-2x mb-2 text-secondary d-block"></i> ' + msg + '</td></tr>');
        updateSelectedCount();
        recalculatePagePaymentSummary();
        return;
    }
    
    $('#tbody_students_list').html('<tr><td colspan="9" class="text-center py-5 text-primary"><i class="fa fa-spinner fa-spin fa-2x"></i><br><span class="mt-2 d-block font-weight-bold" style="font-size: 12.5px;">Loading students and course fee structure...</span></td></tr>');
    
    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: "{{ url('getStudentsList') }}",
        method: 'POST',
        data: {
            session_id: session_id,
            course_id: course_id,
            class_type_id: class_type_id,
            batch: batch,
            student_lookup: student_lookup,
            father_name: father_name,
            gender_id: gender_id,
        },
        success: function(response) {
            $('#tbody_students_list').html(response);
            applyClientSideGridFilters();
            updateLedgerHeadingContext();
            resizeStudentAssignTable();
        },
        error: function(xhr) {
            $('#tbody_students_list').html('<tr><td colspan="9" class="text-center text-danger py-5"><i class="fa fa-exclamation-triangle mr-1"></i> Error loading students list.</td></tr>');
            updateSelectedCount();
            recalculatePagePaymentSummary();
        }
    });
}

function loadFeeMasterHeads(course_id, batch) {
    if (!course_id || !batch) {
        $('#course_fee_structure_card').slideUp(200);
        $('#course_fee_structure_chips_box').empty();
        return;
    }

    var courseName = $('#filter_course_id option:selected').text().trim() || 'Course';
    var className = $('#filter_class_type_id option:selected').text().trim();
    var classLabel = $('#filter_class_type_id').val() ? ' - ' + className : '';
    $('#structure_course_batch_label').text(courseName + classLabel + ' - ' + batch);

    $.ajax({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: "{{ url('getMasterData') }}",
        method: 'POST',
        data: { 
            session_id: $('#filter_session_id').val(),
            course_id: course_id,
            class_type_id: $('#filter_class_type_id').val(),
            batch: batch 
        },
        success: function(response) {
            loadedFeeMasters = Array.isArray(response) ? response : [];
            if ($('#bulkFeeAssignModal').hasClass('show')) {
                renderBulkAssignmentHeads();
            }
            if (response && response.length > 0) {
                var totalStructureAmt = 0;
                var html = [];
                for (var i = 0; i < response.length; i++) {
                    var item = response[i];
                    var amt = parseFloat(item.amount || 0);
                    var bAmt = parseFloat(item.boys_amount || amt);
                    var gAmt = parseFloat(item.girls_amount || amt);
                    totalStructureAmt += amt;
                    var className = item.class_name ? ' <small class="text-muted font-weight-normal">(' + item.class_name + ')</small>' : '';
                    var genderChip = '';
                    if (bAmt !== amt || gAmt !== amt) {
                        genderChip = ' <span class="badge badge-light border text-primary ml-1" style="font-size:10px;">👦 ₹' + bAmt.toLocaleString('en-IN') + '</span><span class="badge badge-light border text-danger ml-1" style="font-size:10px;">👧 ₹' + gAmt.toLocaleString('en-IN') + '</span>';
                    }
                    html.push(
                        '<label class="top-structure-head-chip" id="top_head_chip_' + item.id + '" data-master-id="' + item.id + '" title="Click checkbox to select this fee head for custom assignment">' +
                            '<input type="checkbox" class="top-head-checkbox" value="' + item.id + '" data-name="' + item.fees_group_name + '" style="cursor: pointer; margin-right: 5px;">' +
                            '<span class="head-title font-weight-bold text-dark">' + item.fees_group_name + className + '</span>' +
                            '<span class="badge-amt">₹' + amt.toLocaleString('en-IN') + '</span>' +
                            genderChip +
                        '</label>'
                    );
                }
                $('#course_fee_structure_chips_box').empty().append(html.join(''));
                $('#structure_head_count').text(response.length);
                $('#structure_total_fee').text('₹' + totalStructureAmt.toLocaleString('en-IN'));
                $('#course_fee_structure_card').slideDown(200);
                updateSelectedHeadsBadge();
            } else {
                loadedFeeMasters = [];
                $('#course_fee_structure_card').slideUp(200);
                $('#course_fee_structure_chips_box').empty();
            }
        },
        error: function() {
            loadedFeeMasters = [];
            $('#course_fee_structure_card').slideUp(200);
            $('#course_fee_structure_chips_box').empty();
        }
    });
}

function renderBulkAssignmentHeads() {
    var selectedClassIds = {};
    var classNames = [];
    $('.student_select_checkbox:checked').each(function() {
        var $row = $(this).closest('.student-fee-row');
        var classId = String($row.data('class-id') || '');
        var className = String($row.data('class-name') || 'Current Semester');
        if (classId) selectedClassIds[classId] = true;
        if (classNames.indexOf(className) === -1) classNames.push(className);
    });

    var safe = function(value) { return $('<div>').text(value == null ? '' : value).html(); };
    var sheetRows = loadedFeeMasters.filter(function(item) {
        return selectedClassIds[String(item.class_type_id)] === true;
    }).map(function(item) {
        var amount = parseFloat(item.amount || 0);
        var bAmt = parseFloat(item.boys_amount || amount);
        var gAmt = parseFloat(item.girls_amount || amount);
        var dueDate = item.installment_due_date ? String(item.installment_due_date).split('-').reverse().join('-') : '-';
        var cat = (item.fees_group_type || 'compulsory').toLowerCase();
        var isCompulsory = cat.indexOf('option') === -1 && cat.indexOf('hostel') === -1;
        var typeBadge = isCompulsory 
            ? '<span class="badge badge-light border text-primary" style="font-size:10px; padding:2px 5px;">Compulsory</span>' 
            : '<span class="badge badge-light border text-warning" style="font-size:10px; padding:2px 5px;">Optional</span>';

        return '<tr data-class-id="' + item.class_type_id + '">' +
            '<td class="bulk-check"><input type="checkbox" class="top-head-checkbox" value="' + item.id + '" data-compulsory="' + (isCompulsory ? 1 : 0) + '" data-name="' + safe(item.fees_group_name) + '"></td>' +
            '<td>' + safe(item.class_name || 'Current Semester') + '</td>' +
            '<td class="bulk-head-name">' + safe(item.fees_group_name || 'Fee Head') + '</td>' +
            '<td class="text-center">' + typeBadge + '</td>' +
            '<td class="bulk-amount">&#8377;' + amount.toLocaleString('en-IN') + '</td>' +
            '<td class="text-right text-primary font-weight-bold" style="font-size:12px;">&#8377;' + bAmt.toLocaleString('en-IN') + '</td>' +
            '<td class="text-right text-danger font-weight-bold" style="font-size:12px;">&#8377;' + gAmt.toLocaleString('en-IN') + '</td>' +
            '<td class="text-center">' + safe(dueDate) + '</td></tr>';
    });
    var sheet = sheetRows.length
        ? '<table id="bulk_fee_heads_table" class="table table-bordered table-sm mb-0"><thead><tr><th>Select</th><th>Class / Semester</th><th style="text-align:left">Fee Head</th><th style="text-align:center">Type</th><th>Base Amt</th><th style="color:#0284c7;">👦 Boys</th><th style="color:#e11d48;">👧 Girls</th><th>Due Date</th></tr></thead><tbody>' + sheetRows.join('') + '</tbody></table>' +
          '<div class="px-2 py-1 mt-1 text-muted small" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:2px;"><i class="fa fa-info-circle text-info mr-1"></i> <strong>Smart Gender Pricing:</strong> Boys (👦) and Girls (👧) will automatically receive their respective fee amounts when assigned.</div>'
        : '<div class="alert alert-warning m-2">No fee heads are configured for the selected students\' current semester.</div>';
    $('#bulk_modal_student_count').text($('.student_select_checkbox:checked').length);
    $('#bulk_modal_class_summary').text(classNames.join(', '));
    $('#bulk_fee_heads_box').html(sheet);

    // Default select compulsory fee heads automatically
    $('#bulk_fee_heads_box .top-head-checkbox[data-compulsory="1"]').prop('checked', true);

    updateSelectedHeadsBadge();
}

$(document).ready(function() {
    $('.select2').select2();
    syncSpreadsheetFilters();
    resizeStudentAssignTable();
    $(window).on('resize', resizeStudentAssignTable);

    $('#grid_filter_course').on('change', function() {
        $('#filter_course_id').val($(this).val()).trigger('change');
    });
    $('#grid_filter_batch').on('change', function() {
        $('#filter_batch').val($(this).val()).trigger('change');
    });
    $('#grid_filter_student_lookup').on('input', function() {
        syncAssignUrlParams();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadStudents, 350);
    });
    $('#grid_filter_father_name').on('input', function() {
        syncAssignUrlParams();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadStudents, 350);
    });
    $('#grid_filter_gender').on('change', function() {
        $('#filter_gender_id').val($(this).val()).trigger('change');
    });

    window.applyClientSideGridFilters = function() {
        var remarkQuery = ($('#grid_filter_remark').val() || '').toLowerCase().trim();
        
        var showDues = $('#btn_filter_dues').hasClass('active');
        var showExcess = $('#btn_filter_excess').hasClass('active');
        var showCleared = $('#btn_filter_cleared').hasClass('active');

        $('#tbody_students_list tr.student-fee-row').each(function() {
            var $row = $(this);
            
            // 1. Remark filter check
            var remarkMatch = true;
            if (remarkQuery) {
                var rText = ($row.find('.col-remark').text() || '').toLowerCase();
                remarkMatch = (rText.indexOf(remarkQuery) !== -1);
            }

            // 2. Balance status check
            var status = $row.attr('data-balance-status');
            if (!status) {
                var p = parseFloat($row.attr('data-pending')) || 0;
                var e = parseFloat($row.attr('data-excess')) || 0;
                status = (p > 0) ? 'dues' : ((e > 0) ? 'excess' : 'cleared');
            }

            var statusMatch = false;
            if (status === 'dues' && showDues) statusMatch = true;
            if (status === 'excess' && showExcess) statusMatch = true;
            if (status === 'cleared' && showCleared) statusMatch = true;

            if (remarkMatch && statusMatch) {
                $row.show();
            } else {
                $row.hide();
            }
        });

        updateSelectedCount();
        recalculatePagePaymentSummary();
    };

    $('#grid_filter_remark').on('input', function() {
        applyClientSideGridFilters();
    });

    $(document).on('click', '.btn-status-toggle', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        applyClientSideGridFilters();
    });
    $('#grid_bulk_assign').on('click', function() { $('#btn_open_bulk_assign_modal').trigger('click'); });
    $('#grid_select_all').on('click', function() { $('#btn_quick_select_all').trigger('click'); });
    $('#grid_deselect_all').on('click', function() { $('#btn_quick_deselect_all').trigger('click'); });
    $('#grid_reset_filters').on('click', function(e) {
        e.preventDefault();
        
        var hadServerFilters = !!(
            $('#grid_filter_student_lookup').val() || 
            $('#grid_filter_father_name').val() || 
            $('#filter_gender_id').val() || 
            $('#grid_filter_gender').val() || 
            $('#filter_admission_no').val()
        );

        // 1. Reset text and select filter inputs
        $('#grid_filter_student_lookup').val('');
        $('#filter_admission_no').val('');
        $('#grid_filter_father_name').val('');
        $('#grid_filter_gender').val('');
        $('#filter_gender_id').val('');
        $('#grid_filter_remark').val('');

        // 2. Set all balance status buttons back to active
        $('.btn-status-toggle').addClass('active');

        // 3. Immediately unhide matching rows client-side
        applyClientSideGridFilters();

        // 4. Update browser URL search params
        syncAssignUrlParams();

        // 5. If server filters were active, reload list
        if (hadServerFilters) {
            loadStudents();
        } else {
            showFeeToast('All table filters have been reset.', 'info');
        }
    });

    $('#btn_open_bulk_assign_modal').on('click', function() {
        var selectedCount = $('.student_select_checkbox:checked').length;
        if (!selectedCount) {
            showFeeToast('Please select at least one student from the view list first.', 'error');
            return;
        }
        renderBulkAssignmentHeads();
        $('#bulkFeeAssignModal').modal('show');
    });

    $('#bulk_select_all_heads').on('click', function() {
        $('#bulk_fee_heads_box .top-head-checkbox').prop('checked', true).closest('.top-structure-head-chip').addClass('selected-top-chip');
        updateSelectedHeadsBadge();
    });

    $('#bulk_clear_all_heads').on('click', function() {
        $('#bulk_fee_heads_box .top-head-checkbox').prop('checked', false).closest('.top-structure-head-chip').removeClass('selected-top-chip');
        updateSelectedHeadsBadge();
    });

    $('#btn_modal_assign_selected').on('click', function() {
        if ($('#bulk_fee_heads_box .top-head-checkbox:checked').length === 0) {
            showFeeToast('Please select at least one current-semester fee head.', 'error');
            return;
        }
        $('#btn_bulk_assign_realtime').trigger('click');
    });

    // Top structure head checkbox change
    $(document).on('change', '.top-head-checkbox', function() {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            $(this).closest('.top-structure-head-chip').addClass('selected-top-chip');
        } else {
            $(this).closest('.top-structure-head-chip').removeClass('selected-top-chip');
        }
        updateSelectedHeadsBadge();
    });

    // Select All Heads in top structure toolbar
    $('#btn_top_select_all_heads').click(function(e) {
        e.preventDefault();
        $('.top-head-checkbox').prop('checked', true);
        $('.top-structure-head-chip').addClass('selected-top-chip');
        updateSelectedHeadsBadge();
    });

    // Deselect All Heads in top structure toolbar
    $('#btn_top_deselect_all_heads').click(function(e) {
        e.preventDefault();
        $('.top-head-checkbox').prop('checked', false);
        $('.top-structure-head-chip').removeClass('selected-top-chip');
        updateSelectedHeadsBadge();
    });

    function syncAssignUrlParams() {
        var params = new URLSearchParams();
        var sessionId = $('#filter_session_id').val();
        var courseId = $('#filter_course_id').val();
        var classTypeId = $('#filter_class_type_id').val();
        var batch = $('#filter_batch').val();
        var genderId = $('#filter_gender_id').val();
        var studentLookup = $('#grid_filter_student_lookup').val();
        var fatherName = $('#grid_filter_father_name').val();

        if (sessionId) params.set('session_id', sessionId);
        if (courseId) params.set('course_id', courseId);
        if (classTypeId) params.set('class_type_id', classTypeId);
        if (batch) params.set('batch', batch);
        if (genderId) params.set('gender_id', genderId);
        if (studentLookup && studentLookup.trim()) params.set('student_lookup', studentLookup.trim());
        if (fatherName && fatherName.trim()) params.set('father_name', fatherName.trim());

        var queryString = params.toString();
        var newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.history.replaceState({}, '', newUrl);
    }

    // Course Select Handler
    $('#filter_course_id').change(function() {
        var courseId = $(this).val();
        var batch = $('#filter_batch').val();
        var classSelect = $('#filter_class_type_id');
        var currentClassId = classSelect.val();

        classSelect.empty().append('<option value="">-- All Current Semesters --</option>');
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: 'GET',
                dataType: 'json',
                data: { course_id: courseId, session_id: $('#filter_session_id').val() },
                success: function(classes) {
                    var targetClassId = String(currentClassId || urlClassTypeId || '');
                    $.each(classes || [], function(_, item) {
                        classSelect.append(new Option(item.name, item.id, false, String(item.id) === targetClassId));
                    });
                    if (targetClassId) {
                        classSelect.val(targetClassId);
                    }
                    classSelect.trigger('change.select2');
                }
            });
        } else {
            @if(!empty($allClassType))
                @foreach($allClassType as $type)
                    classSelect.append(new Option(@json($type->name ?? ''), '{{ $type->id }}'));
                @endforeach
            @endif
            classSelect.trigger('change.select2');
        }
        syncAssignUrlParams();
        if (courseId && batch) {
            loadFeeMasterHeads(courseId, batch);
        } else {
            $('#course_fee_structure_card').slideUp(200);
            $('#course_fee_structure_chips_box').empty();
        }
        loadStudents();
    });

    // Session controls both historical admissions and session-specific semesters.
    $('#filter_session_id').change(function() {
        var sessionId = $(this).val();
        var courseId = $('#filter_course_id').val();
        var batch = $('#filter_batch').val();
        var classSelect = $('#filter_class_type_id');

        classSelect.empty().append('<option value="">-- All Current Semesters --</option>');
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: 'GET',
                dataType: 'json',
                data: { course_id: courseId, session_id: sessionId },
                success: function(classes) {
                    $.each(classes || [], function(_, item) {
                        classSelect.append(new Option(item.name, item.id));
                    });
                    classSelect.trigger('change.select2');
                }
            });
        } else {
            classSelect.trigger('change.select2');
        }

        syncAssignUrlParams();
        if (courseId && batch) loadFeeMasterHeads(courseId, batch);
        loadStudents();
    });

    // Class / Semester Filter Handler
    $('#filter_class_type_id').change(function() {
        var courseId = $('#filter_course_id').val();
        var batch = $('#filter_batch').val();
        syncAssignUrlParams();
        if (courseId && batch) {
            loadFeeMasterHeads(courseId, batch);
        }
        loadStudents();
    });

    // Batch Select Handler
    $('#filter_batch').change(function() {
        var batch = $(this).val();
        var courseId = $('#filter_course_id').val();
        syncAssignUrlParams();
        if (courseId && batch) {
            loadFeeMasterHeads(courseId, batch);
        } else {
            $('#course_fee_structure_card').slideUp(200);
            $('#course_fee_structure_chips_box').empty();
        }
        loadStudents();
    });

    // Gender Select Handler
    $('#filter_gender_id').change(function() {
        syncAssignUrlParams();
        loadStudents();
    });

    // Fast Debounced Student Search
    $('#filter_admission_no').on('input', function() {
        syncAssignUrlParams();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            loadStudents();
        }, 350);
    });

    $('#btn_search_students').click(function() {
        syncAssignUrlParams();
        loadStudents();
    });

    // Reset Filters
    $('#btn_clear_filters, #btn_clear_filters_mid').click(function() {
        $('#filter_course_id').val('').trigger('change.select2');
        $('#filter_class_type_id').val('').trigger('change.select2');
        $('#filter_batch').val('').trigger('change.select2');
        $('#filter_gender_id').val('').trigger('change.select2');
        $('#filter_session_id').val('{{ Session::get('session_id') }}').trigger('change.select2');
        $('#filter_admission_no').val('');
        $('#grid_filter_student_lookup, #grid_filter_father_name, #grid_filter_gender, #grid_filter_remark').val('');
        $('.btn-status-toggle').addClass('active');
        window.history.replaceState({}, '', window.location.pathname);
        $('#course_fee_structure_card').slideUp(200);
        $('#course_fee_structure_chips_box').empty();
        $('#tbody_students_list').html('<tr><td colspan="9" class="text-center py-5 text-muted"><i class="fa fa-filter fa-2x mb-2 text-secondary d-block"></i> Please select both <b>Course</b> and <b>Batch</b> above.</td></tr>');
        updateLedgerHeadingContext();
        updateSelectedCount();
        recalculatePagePaymentSummary();
    });

    // Auto-load if filters were present in URL or $serach
    var urlParams = new URLSearchParams(window.location.search);
    var urlSessionId = urlParams.get('session_id') || '{{ $serach["session_id"] ?? Session::get("session_id") }}';
    var urlCourseId = urlParams.get('course_id') || '{{ $serach["course_id"] ?? "" }}';
    var urlClassTypeId = urlParams.get('class_type_id') || '{{ $serach["class_type_id"] ?? "" }}';
    var urlBatch = urlParams.get('batch') || '{{ $serach["batch"] ?? "" }}';
    var urlGenderId = urlParams.get('gender_id') || '{{ $serach["gender_id"] ?? "" }}';
    var urlAdmissionNo = urlParams.get('admissionNo') || urlParams.get('admission_no') || '{{ $serach["admissionNo"] ?? "" }}';

    if (urlSessionId && $('#filter_session_id').val() != urlSessionId) {
        $('#filter_session_id').val(urlSessionId).trigger('change.select2');
    }
    if (urlCourseId && $('#filter_course_id').val() != urlCourseId) {
        $('#filter_course_id').val(urlCourseId).trigger('change.select2');
    }
    if (urlBatch && $('#filter_batch').val() != urlBatch) {
        $('#filter_batch').val(urlBatch).trigger('change.select2');
    }
    if (urlGenderId && $('#filter_gender_id').val() != urlGenderId) {
        $('#filter_gender_id').val(urlGenderId).trigger('change.select2');
    }
    if (urlClassTypeId && $('#filter_class_type_id').val() != urlClassTypeId) {
        $('#filter_class_type_id').val(urlClassTypeId).trigger('change.select2');
    }
    if (urlAdmissionNo && !$('#filter_admission_no').val()) {
        $('#filter_admission_no').val(urlAdmissionNo);
    }

    var activeCourseId = $('#filter_course_id').val();
    var activeBatch = $('#filter_batch').val();
    var activeAdm = $('#filter_admission_no').val();

    if ((activeCourseId && activeBatch) || activeAdm) {
        if (activeCourseId && activeBatch) {
            loadFeeMasterHeads(activeCourseId, activeBatch);
        }
        loadStudents();
    }

    // Select All / Deselect All Students Checkbox
    $('#all_students').click(function() {
        var isChecked = $(this).prop('checked');
        $('.student-fee-row:visible .student_select_checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });

    $('#btn_quick_select_all').click(function() {
        $('.student-fee-row:visible .student_select_checkbox').prop('checked', true);
        $('#all_students').prop('checked', true);
        updateSelectedCount();
    });

    $('#btn_quick_deselect_all').click(function() {
        $('.student_select_checkbox').prop('checked', false);
        $('#all_students').prop('checked', false);
        updateSelectedCount();
    });

    $(document).on('change', '.student_select_checkbox', function() {
        updateSelectedCount();
    });

    // REAL-TIME INDIVIDUAL FEE HEAD TOGGLE
    $(document).on('change', '.toggle-fee-head-checkbox', function(e) {
        var $checkbox = $(this);
        var admissionId = $checkbox.data('admission-id');
        var masterId = $checkbox.data('master-id');
        var isChecked = $checkbox.is(':checked') ? 1 : 0;
        var $chip = $('#chip_' + admissionId + '_' + masterId);
        var $icon = $('#icon_' + admissionId + '_' + masterId);

        $chip.css('opacity', '0.6');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('toggleStudentFeeHead') }}",
            method: 'POST',
            data: {
                admission_id: admissionId,
                fees_master_id: masterId,
                state: isChecked
            },
            success: function(res) {
                $chip.css('opacity', '1');
                if (res.status === 'success') {
                    if (res.action === 'assigned') {
                        $chip.removeClass('chip-unassigned').addClass('chip-assigned');
                        $icon.removeClass('d-none');
                        $checkbox.prop('checked', true);
                    } else {
                        $chip.removeClass('chip-assigned').addClass('chip-unassigned');
                        $icon.addClass('d-none');
                        $checkbox.prop('checked', false);
                    }
                    
                    if (res.total_amount !== undefined) {
                        updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                    }
                    showFeeToast(res.message, 'success');
                } else {
                    $checkbox.prop('checked', !isChecked);
                    showFeeToast(res.message || 'Error updating fee head', 'error');
                }
            },
            error: function(xhr) {
                $chip.css('opacity', '1');
                $checkbox.prop('checked', !isChecked);
                showFeeToast('Server error while updating fee head', 'error');
            }
        });
    });

    // GLOBAL PENDING ACTION STATE FOR CUSTOM CONFIRMATION MODAL
    var pendingFeeAction = {
        type: null, // 'assign_all_student', 'clear_all_student', 'quick_apply_loaded', 'bulk_assign_selected'
        admissionId: null,
        admissionIds: [],
        studentName: '',
        admissionNo: '',
        totalHeads: 0,
        masterIds: []
    };

    // TRIGGER MODAL: ASSIGN ALL COURSE HEADS TO SINGLE STUDENT
    $(document).on('click', '.btn-student-assign-all-heads', function(e) {
        e.preventDefault();
        var admissionId = $(this).data('admission-id');
        var studentName = $(this).data('student-name') || 'Selected Student';
        var admissionNo = $(this).data('admission-no') || '-';
        var totalHeads = $(this).data('total-heads') || 'all';

        pendingFeeAction = {
            type: 'assign_all_student',
            admissionId: admissionId,
            admissionIds: [admissionId],
            studentName: studentName,
            admissionNo: admissionNo,
            totalHeads: totalHeads,
            masterIds: []
        };

        // Configure Modal for Assign All (Promote Theme)
        $('#feeConfirmModalHeader').css('border-bottom', '1px solid #aebbc5');
        $('#feeConfirmIcon').attr('class', 'fa fa-check-circle mr-2 text-success');
        $('#feeConfirmTitle').text('Assign All Course Fee Heads');
        $('#feeConfirmBigIcon').attr('class', 'fa fa-check-square-o text-success').css('font-size', '18px');
        $('#feeConfirmIconWrapper').css({ 'background': '#e7f5ec', 'width': '38px', 'height': '38px', 'border-radius': '50%' });
        $('#feeConfirmHeadline').text('Assign All Course Heads to Student?');
        
        $('#feeConfirmStudentInfoBox').show();
        $('#feeConfirmStudentNameRow').show();
        $('#feeConfirmStudentAdmRow').show();
        $('#feeConfirmStudentName').text(studentName);
        $('#feeConfirmStudentAdmNo').text(admissionNo);
        $('#feeConfirmExtraDetails').html('<span class="sheet-chip sheet-chip-course font-weight-bold">Assign all (' + totalHeads + ') fee heads in real-time</span>');
        
        $('#feeConfirmMessage').text('Are you sure you want to assign all course fee structure heads to ' + studentName + '? This will update instantly.');
        $('#feeConfirmPaidWarning').addClass('d-none');
        
        $('#btn_confirm_fee_modal_action')
            .removeClass('btn-modal-danger')
            .addClass('btn-modal-submit')
            .prop('disabled', false)
            .html('<i class="fa fa-check-square-o mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Assign All</span>');

        $('#feeAssignConfirmModal').modal('show');
    });

    // TRIGGER MODAL: CLEAR ALL UNPAID HEADS FOR SINGLE STUDENT (WITH PAYMENT CHECK)
    $(document).on('click', '.btn-student-unassign-all-heads', function(e) {
        e.preventDefault();
        var admissionId = $(this).data('admission-id');
        var studentName = $(this).data('student-name') || 'Selected Student';
        var admissionNo = $(this).data('admission-no') || '-';
        var $row = $('#student_row_' + admissionId);
        var assignedCheckboxes = $row.find('.toggle-fee-head-checkbox:checked');
        
        if (assignedCheckboxes.length === 0) {
            showFeeToast('No fee heads are currently assigned to clear for this student', 'error');
            return;
        }

        var $btn = $(this);
        var originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        // First check if any payment has been collected for this student
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('checkStudentCollectedFees') }}",
            method: 'POST',
            data: { admission_id: admissionId },
            success: function(res) {
                $btn.prop('disabled', false).html(originalBtnHtml);
                
                if (res.status === 'success' && res.has_paid && res.total_paid > 0) {
                    // Populate and show the custom warning modal
                    $('#collected_warn_student_name').text(res.student.name);
                    $('#collected_warn_admission_no').text(res.student.admissionNo);
                    $('#collected_warn_class_batch').text(res.student.class_name + ' (' + res.student.batch + ')');
                    $('#collected_warn_total_paid').text('₹' + parseFloat(res.total_paid).toLocaleString('en-IN'));
                    
                    var collectParams = new URLSearchParams();
                    collectParams.set('admission_id', res.student.id);
                    if (res.student.unique_system_id) collectParams.set('unique_system_id', res.student.unique_system_id);
                    if (res.student.admissionNo && res.student.admissionNo !== '-') collectParams.set('admission_no', res.student.admissionNo);
                    if (res.student.course_id) collectParams.set('course_id', res.student.course_id);
                    if (res.student.class_type_id) collectParams.set('class_type_id', res.student.class_type_id);
                    if (res.student.batch && res.student.batch !== '-') collectParams.set('batch', res.student.batch);
                    if (res.student.session_id) collectParams.set('session_id', res.student.session_id);

                    $('#btn_goto_fee_collect').attr('href', "{{ url('Fees/add') }}?" + collectParams.toString());

                    var tbodyHtml = [];
                    if (res.invoices && res.invoices.length > 0) {
                        res.invoices.forEach(function(inv, idx) {
                            tbodyHtml.push(
                                '<tr>' +
                                    '<td class="align-middle font-weight-bold text-muted">' + (idx + 1) + '</td>' +
                                    '<td class="align-middle"><span class="badge badge-light border text-primary font-weight-bold px-2 py-1" style="font-size: 11.5px;">' + inv.invoice_no + '</span></td>' +
                                    '<td class="align-middle text-left"><strong class="text-dark d-block">' + inv.heads + '</strong></td>' +
                                    '<td class="align-middle font-weight-bold text-success" style="font-size: 13px;">₹' + inv.amount.toLocaleString('en-IN') + '</td>' +
                                    '<td class="align-middle text-dark"><span class="d-block font-weight-600">' + inv.payment_date + '</span><span class="badge badge-secondary px-1.5 py-0.5" style="font-size: 10.5px;">' + inv.payment_mode + '</span></td>' +
                                    '<td class="align-middle">' +
                                        '<a href="' + inv.receipt_url + '" target="_blank" class="btn btn-xs btn-primary font-weight-bold px-2.5 py-1 text-white shadow-xs" style="background-color: #0284c7 !important; border-color: #0284c7 !important; font-size: 11px; border-radius: 4px;" title="Preview / Print Receipt">' +
                                            '<i class="fa fa-eye mr-1"></i> Preview' +
                                        '</a>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                    } else if (res.paid_details && res.paid_details.length > 0) {
                        res.paid_details.forEach(function(pd, idx) {
                            tbodyHtml.push(
                                '<tr>' +
                                    '<td class="align-middle font-weight-bold text-muted">' + (idx + 1) + '</td>' +
                                    '<td class="align-middle font-weight-bold text-secondary">' + (pd.receipt_no || '-') + '</td>' +
                                    '<td class="align-middle text-left"><strong class="text-dark d-block">' + pd.head_name + '</strong></td>' +
                                    '<td class="align-middle font-weight-bold text-success" style="font-size: 13px;">₹' + pd.amount.toLocaleString('en-IN') + '</td>' +
                                    '<td class="align-middle text-dark"><span class="d-block font-weight-600">' + pd.date + '</span><span class="badge badge-secondary px-1.5 py-0.5" style="font-size: 10.5px;">' + pd.payment_mode + '</span></td>' +
                                    '<td class="align-middle">' +
                                        '<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">Collected</span>' +
                                    '</td>' +
                                '</tr>'
                            );
                        });
                    }

                    $('#collected_warn_invoices_tbody').html(tbodyHtml.join(''));
                    $('#feeCollectedWarningModal').modal('show');
                } else {
                    // No collected fees, proceed with standard confirmation modal
                    pendingFeeAction = {
                        type: 'clear_all_student',
                        admissionId: admissionId,
                        admissionIds: [admissionId],
                        studentName: studentName,
                        admissionNo: admissionNo,
                        totalHeads: assignedCheckboxes.length,
                        masterIds: []
                    };

                    $('#feeConfirmModalHeader').css('border-bottom', '1px solid #aebbc5');
                    $('#feeConfirmIcon').attr('class', 'fa fa-trash mr-2 text-danger');
                    $('#feeConfirmTitle').text('Clear All Assigned Fee Heads');
                    $('#feeConfirmBigIcon').attr('class', 'fa fa-trash text-danger').css('font-size', '18px');
                    $('#feeConfirmIconWrapper').css({ 'background': '#fee2e2', 'width': '38px', 'height': '38px', 'border-radius': '50%' });
                    $('#feeConfirmHeadline').text('Clear All Fee Heads for Student?');
                    
                    $('#feeConfirmStudentInfoBox').show();
                    $('#feeConfirmStudentNameRow').show();
                    $('#feeConfirmStudentAdmRow').show();
                    $('#feeConfirmStudentName').text(studentName);
                    $('#feeConfirmStudentAdmNo').text(admissionNo);
                    $('#feeConfirmExtraDetails').html('<span class="sheet-chip" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-weight: 600;">Clear (' + assignedCheckboxes.length + ') assigned heads</span>');
                    
                    $('#feeConfirmMessage').text('Are you sure you want to unassign all fee heads for ' + studentName + '?');
                    $('#feeConfirmPaidWarning').addClass('d-none');
                    
                    $('#btn_confirm_fee_modal_action')
                        .removeClass('btn-modal-submit')
                        .addClass('btn-modal-danger')
                        .prop('disabled', false)
                        .html('<i class="fa fa-trash mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Clear All</span>');

                    $('#feeAssignConfirmModal').modal('show');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalBtnHtml);
                showFeeToast('Server error while checking payment status', 'error');
            }
        });
    });

    // TRIGGER MODAL: QUICK APPLY ALL HEADS TO ALL LOADED STUDENTS
    $('#btn_quick_apply_all_loaded').click(function(e) {
        e.preventDefault();
        var allStudentIds = [];
        $('.student_select_checkbox').each(function() {
            allStudentIds.push($(this).val());
        });

        if (allStudentIds.length === 0) {
            showFeeToast('No students loaded in the table. Please select Course and Batch first.', 'error');
            return;
        }

        var count = allStudentIds.length;

        pendingFeeAction = {
            type: 'quick_apply_loaded',
            admissionId: null,
            admissionIds: allStudentIds,
            studentName: '',
            admissionNo: '',
            totalHeads: 0,
            masterIds: []
        };

        // Configure Modal for Quick Apply
        $('#feeConfirmModalHeader').css('border-bottom', '1px solid #aebbc5');
        $('#feeConfirmIcon').attr('class', 'fa fa-bolt mr-2 text-warning');
        $('#feeConfirmTitle').text('Quick Apply All Fee Heads');
        $('#feeConfirmBigIcon').attr('class', 'fa fa-bolt text-warning').css('font-size', '18px');
        $('#feeConfirmIconWrapper').css({ 'background': '#fef3c7', 'width': '38px', 'height': '38px', 'border-radius': '50%' });
        $('#feeConfirmHeadline').text('Quick Apply to ALL Loaded Students?');
        
        $('#feeConfirmStudentInfoBox').show();
        $('#feeConfirmStudentNameRow').hide();
        $('#feeConfirmStudentAdmRow').hide();
        $('#feeConfirmExtraDetails').html(
            '<span class="sheet-chip sheet-chip-course mr-1">' + count + ' Students Loaded</span>' +
            '<span class="sheet-chip sheet-chip-batch">ALL Course Fee Heads</span>'
        );
        
        $('#feeConfirmMessage').text('Are you sure you want to assign all course fee structure heads to ALL ' + count + ' students loaded in this batch? This will be applied live in real-time.');
        $('#feeConfirmPaidWarning').addClass('d-none');
        
        $('#btn_confirm_fee_modal_action')
            .removeClass('btn-modal-danger')
            .addClass('btn-modal-submit')
            .prop('disabled', false)
            .html('<i class="fa fa-bolt mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Quick Apply (' + count + ')</span>');

        $('#feeAssignConfirmModal').modal('show');
    });

    // TRIGGER MODAL: BULK ASSIGN TO SELECTED/CHECKED STUDENTS
    $('#btn_bulk_assign_realtime').click(function(e) {
        e.preventDefault();
        var selectedStudents = [];
        $('.student_select_checkbox:checked').each(function() {
            selectedStudents.push($(this).val());
        });

        if (selectedStudents.length === 0) {
            showFeeToast('Please select at least one student checkbox in the table.', 'error');
            return;
        }

        var feeMasterIds = [];
        var feeMasterNames = [];
        $('.top-head-checkbox:checked').each(function() {
            feeMasterIds.push($(this).val());
            feeMasterNames.push($(this).data('name'));
        });

        var count = selectedStudents.length;

        pendingFeeAction = {
            type: 'bulk_assign_selected',
            admissionId: null,
            admissionIds: selectedStudents,
            studentName: '',
            admissionNo: '',
            totalHeads: feeMasterIds.length,
            masterIds: feeMasterIds
        };

        // Configure Modal for Selected Bulk Assign
        $('#feeConfirmModalHeader').css('border-bottom', '1px solid #aebbc5');
        $('#feeConfirmIcon').attr('class', 'fa fa-users mr-2 text-success');
        $('#feeConfirmBigIcon').attr('class', 'fa fa-users text-success').css('font-size', '18px');
        $('#feeConfirmIconWrapper').css({ 'background': '#e7f5ec', 'width': '38px', 'height': '38px', 'border-radius': '50%' });
        
        $('#feeConfirmStudentInfoBox').show();
        $('#feeConfirmStudentNameRow').hide();
        $('#feeConfirmStudentAdmRow').hide();

        if (feeMasterIds.length > 0) {
            var headsPreview = feeMasterNames.slice(0, 3).join(', ') + (feeMasterNames.length > 3 ? ' +' + (feeMasterNames.length - 3) + ' more' : '');
            $('#feeConfirmTitle').text('Assign Selected Fee Heads (' + feeMasterIds.length + ')');
            $('#feeConfirmHeadline').text('Assign ' + feeMasterIds.length + ' Selected Head(s) to ' + count + ' Student(s)?');
            $('#feeConfirmExtraDetails').html(
                '<span class="sheet-chip sheet-chip-course mr-1">' + count + ' Students Selected</span>' +
                '<span class="sheet-chip sheet-chip-batch">' + feeMasterIds.length + ' Head(s): ' + headsPreview + '</span>'
            );
            $('#feeConfirmMessage').text('Are you sure you want to assign the selected (' + feeMasterIds.length + ') fee head(s) to the ' + count + ' checked student(s) in real-time?');
        } else {
            $('#feeConfirmTitle').text('Assign All Course Fee Heads');
            $('#feeConfirmHeadline').text('Assign ALL Course Heads to ' + count + ' Student(s)?');
            $('#feeConfirmExtraDetails').html(
                '<span class="sheet-chip sheet-chip-course mr-1">' + count + ' Students Selected</span>' +
                '<span class="sheet-chip sheet-chip-batch">All Course Heads</span>'
            );
            $('#feeConfirmMessage').text('Are you sure you want to assign ALL course fee structure heads to the ' + count + ' checked student(s) in real-time?');
        }
        
        $('#feeConfirmPaidWarning').addClass('d-none');
        
        $('#btn_confirm_fee_modal_action')
            .removeClass('btn-modal-danger')
            .addClass('btn-modal-submit')
            .prop('disabled', false)
            .html('<i class="fa fa-check-circle mr-1"></i> <span id="btn_confirm_fee_modal_text">Yes, Assign to (' + count + ')</span>');

        $('#feeAssignConfirmModal').modal('show');
    });

    // PROCEED EXECUTION HANDLER FOR CUSTOM CONFIRMATION MODAL
    $('#btn_confirm_fee_modal_action').click(function() {
        var $confirmBtn = $(this);
        $confirmBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing in real-time...');

        if (pendingFeeAction.type === 'assign_all_student') {
            var admissionId = pendingFeeAction.admissionId;
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('bulkAssignCourseFees') }}",
                method: 'POST',
                data: {
                    admissionIds: [admissionId],
                    fees_master_ids: []
                },
                success: function(res) {
                    $('#feeAssignConfirmModal').modal('hide');
                    if (res.status === 'success') {
                        // Real-time update row DOM
                        var $row = $('#student_row_' + admissionId);
                        $row.find('.toggle-fee-head-checkbox').prop('checked', true);
                        $row.find('.fee-head-chip').removeClass('chip-unassigned').addClass('chip-assigned');
                        $row.find('[id^="icon_' + admissionId + '_"]').removeClass('d-none');
                        
                        if (res.updated_totals && res.updated_totals[admissionId] !== undefined) {
                            var g = res.updated_totals[admissionId];
                            var d = res.updated_discounts ? res.updated_discounts[admissionId] : 0;
                            var n = res.updated_nets ? res.updated_nets[admissionId] : (g - d);
                            updateStudentFeeSummary(admissionId, g, d, n);
                        } else if (res.total_amount !== undefined && res.total_amount > 0) {
                            updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                        }
                        showFeeToast(pendingFeeAction.studentName + ': All course fee heads assigned successfully!', 'success');
                    } else {
                        showFeeToast(res.message || 'Error assigning heads', 'error');
                    }
                },
                error: function() {
                    $('#feeAssignConfirmModal').modal('hide');
                    showFeeToast('Server error while assigning fee heads', 'error');
                }
            });
        } 
        else if (pendingFeeAction.type === 'clear_all_student') {
            var admissionId = pendingFeeAction.admissionId;
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('clearStudentFeeHeads') }}",
                method: 'POST',
                data: {
                    admission_id: admissionId
                },
                success: function(res) {
                    $('#feeAssignConfirmModal').modal('hide');
                    if (res.status === 'success') {
                        // Real-time update row DOM
                        var $row = $('#student_row_' + admissionId);
                        $row.find('.toggle-fee-head-checkbox').prop('checked', false);
                        $row.find('.fee-head-chip').removeClass('chip-assigned').addClass('chip-unassigned');
                        $row.find('[id^="icon_' + admissionId + '_"]').addClass('d-none');
                        
                        var g = (res.updated_totals && res.updated_totals[admissionId] !== undefined) ? res.updated_totals[admissionId] : (res.total_amount || 0);
                        var d = (res.updated_discounts && res.updated_discounts[admissionId] !== undefined) ? res.updated_discounts[admissionId] : (res.total_discount || 0);
                        var n = (res.updated_nets && res.updated_nets[admissionId] !== undefined) ? res.updated_nets[admissionId] : (res.net_amount || (g - d));
                        updateStudentFeeSummary(admissionId, g, d, n);
                        
                        showFeeToast(pendingFeeAction.studentName + ': Unpaid fee heads cleared successfully!', 'success');
                    } else {
                        showFeeToast(res.message || 'Error clearing fee heads', 'error');
                    }
                },
                error: function() {
                    $('#feeAssignConfirmModal').modal('hide');
                    showFeeToast('Server error while clearing fee heads', 'error');
                }
            });
        }
        else if (pendingFeeAction.type === 'quick_apply_loaded' || pendingFeeAction.type === 'bulk_assign_selected') {
            var targetIds = pendingFeeAction.admissionIds;
            var masterIds = pendingFeeAction.masterIds;

            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ url('bulkAssignCourseFees') }}",
                method: 'POST',
                data: {
                    admissionIds: targetIds,
                    fees_master_ids: masterIds
                },
                success: function(res) {
                    $('#feeAssignConfirmModal').modal('hide');
                    if (res.status === 'success') {
                        // Real-time update all affected rows
                        targetIds.forEach(function(admId) {
                            var $row = $('#student_row_' + admId);
                            if (masterIds && masterIds.length > 0) {
                                masterIds.forEach(function(mId) {
                                    $row.find('.toggle-fee-head-checkbox[data-master-id="' + mId + '"]').prop('checked', true);
                                    $('#chip_' + admId + '_' + mId).removeClass('chip-unassigned').addClass('chip-assigned');
                                    $('#icon_' + admId + '_' + mId).removeClass('d-none');
                                });
                            } else {
                                $row.find('.toggle-fee-head-checkbox').prop('checked', true);
                                $row.find('.fee-head-chip').removeClass('chip-unassigned').addClass('chip-assigned');
                                $row.find('[id^="icon_' + admId + '_"]').removeClass('d-none');
                            }

                            if (res.updated_totals && res.updated_totals[admId] !== undefined) {
                                var g = res.updated_totals[admId];
                                var d = res.updated_discounts ? res.updated_discounts[admId] : 0;
                                var n = res.updated_nets ? res.updated_nets[admId] : (g - d);
                                updateStudentFeeSummary(admId, g, d, n);
                            }
                        });
                        $('#bulkFeeAssignModal').modal('hide');
                        loadStudents();
                        showFeeToast(res.message || 'Bulk fee assignment completed successfully!', 'success');
                    } else {
                        showFeeToast(res.message || 'Error during bulk assignment', 'error');
                    }
                },
                error: function() {
                    $('#feeAssignConfirmModal').modal('hide');
                    showFeeToast('Server error during bulk fee assignment', 'error');
                }
            });
        }
    });

    // OPEN STUDENT FEE EDIT / MODIFICATION MODAL
    $(document).on('click', '.btn-edit-student-fees', function() {
        var admissionId = $(this).data('admission-id');
        $('#studentFeeEditModal').modal('show');
        $('#studentFeeEditModalBody').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted font-weight-bold">Loading Student Fee Details...</p></div>');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('getStudentFeeDetailsModal') }}",
            method: 'POST',
            data: { admission_id: admissionId },
            success: function(html) {
                $('#studentFeeEditModalBody').html(html);
                syncModalAvailableFeeHeads();
            },
            error: function() {
                $('#studentFeeEditModalBody').html('<div class="alert alert-danger m-3"><i class="fa fa-exclamation-triangle mr-1"></i> Failed to load fee details.</div>');
            }
        });
    });

    $(document).on('click', '.btn-overall-ledger', function() {
        var admissionId = $(this).data('admission-id');
        $('#overallStudentLedgerModal').modal('show');
        $('#overallStudentLedgerBody').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-success"></i><p class="mt-2 text-muted font-weight-bold">Loading complete student ledger...</p></div>');
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('getStudentOverallLedger') }}",
            method: 'POST',
            data: { admission_id: admissionId },
            success: function(html) { $('#overallStudentLedgerBody').html(html); },
            error: function() { $('#overallStudentLedgerBody').html('<div class="alert alert-danger m-3">Unable to load the overall ledger.</div>'); }
        });
    });

    // READ-ONLY ASSIGNED HEADS BREAKDOWN
    $(document).on('click', '.btn-head-breakdown', function() {
        var admissionId = $(this).data('admission-id');
        $('#studentFeeEditModal').modal('show');
        $('#studentFeeEditModalBody').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted font-weight-bold">Loading fee-head breakdown...</p></div>');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('getStudentFeeDetailsModal') }}",
            method: 'POST',
            data: { admission_id: admissionId },
            success: function(html) {
                var $body = $('#studentFeeEditModalBody').html(html);
                $body.find('.modal-title').html('<i class="fa fa-list-alt mr-1"></i> Assigned Fee Heads Breakdown');
                $body.find('.modal-body > .card').first().hide();
                $body.find('input, select').prop('disabled', true);
                $body.find('.btn-save-detail-row, .btn-save-all-batch, .btn-revert-saved-data').hide();
                $body.find('#table_student_fee_edit th:first-child, #table_student_fee_edit td:first-child, #table_student_fee_edit th:last-child, #table_student_fee_edit td:last-child').hide();
            },
            error: function() {
                $('#studentFeeEditModalBody').html('<div class="alert alert-danger m-3"><i class="fa fa-exclamation-triangle mr-1"></i> Failed to load fee-head breakdown.</div>');
            }
        });
    });

    // MODAL: RECALCULATE SUMMARY TOTALS
    function recalculateModalSummary() {
        var total = 0;
        var discount = 0;
        $('#table_student_fee_edit tbody tr').each(function() {
            var amt = parseFloat($(this).find('.input-amount').val()) || 0;
            var disc = parseFloat($(this).find('.input-discount').val()) || 0;
            var net = Math.max(0, amt - disc);
            total += amt;
            discount += disc;
            var netText = (net % 1 === 0) 
                ? '₹' + net.toLocaleString('en-IN') 
                : '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            $(this).find('.net-display').text(netText);
        });
        var netPayable = Math.max(0, total - discount);
        var totalText = (total % 1 === 0) ? '₹' + total.toLocaleString('en-IN') : '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var discText = (discount % 1 === 0) ? '₹' + discount.toLocaleString('en-IN') : '₹' + discount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var netText = (netPayable % 1 === 0) ? '₹' + netPayable.toLocaleString('en-IN') : '₹' + netPayable.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        $('#modal_summary_total').text(totalText);
        $('#modal_summary_discount').text(discText);
        $('#modal_summary_net').text(netText);
    }

    // MODAL: DISCOUNT TYPE OR BASIS CHANGE
    $(document).on('change', '#modal_discount_type, #modal_discount_basis', function() {
        var type = $('#modal_discount_type').val();
        var basis = $('#modal_discount_basis').val();

        if (type === 'percentage') {
            $('#modal_discount_addon').text('%');
            $('#lbl_discount_input').html('<i class="fa fa-percent text-primary mr-1"></i> Discount %');
            $('#modal_total_discount_value').attr('placeholder', 'e.g. 10').attr('max', '100');
        } else {
            $('#modal_discount_addon').text('₹');
            $('#lbl_discount_input').html('<i class="fa fa-tag text-primary mr-1"></i> Discount (₹)');
            $('#modal_total_discount_value').attr('placeholder', 'e.g. 5000').removeAttr('max');
        }

        if (basis === 'total_assigned') {
            $('#discount_explanation_text').html('Discount will be calculated on <b>Total Assigned Fees</b> and distributed equally among checked heads.');
        } else {
            $('#discount_explanation_text').html('Discount will be calculated on <b>Checked Heads Amount</b> and distributed equally among them.');
        }
    });

    // MODAL: CHECK / UNCHECK ALL HEADS
    $(document).on('change', '#modal_check_all_heads', function() {
        var isChecked = $(this).is(':checked');
        $('.modal-head-select').prop('checked', isChecked);
        updateSelectedHeadsBadge();
    });

    // MODAL: INDIVIDUAL HEAD CHECKBOX CHANGE
    $(document).on('change', '.modal-head-select', function() {
        var allChecked = $('.modal-head-select').length === $('.modal-head-select:checked').length;
        $('#modal_check_all_heads').prop('checked', allChecked);
        updateSelectedHeadsBadge();
    });

    function updateSelectedHeadsBadge() {
        var total = $('.modal-head-select').length;
        var checked = $('.modal-head-select:checked').length;
        $('#modal_selected_heads_count').text(checked + ' of ' + total + ' Heads Selected');
    }

    // Ensure already assigned/added fee heads are disabled in the "+ Add Head" dropdown
    function syncModalAvailableFeeHeads() {
        var $select = $('#modal_available_fee_head');
        if (!$select.length) return;

        var existingMasterIds = [];
        var existingNames = [];
        $('#table_student_fee_edit tbody tr.fee-detail-row').each(function() {
            var mId = $(this).data('master-id');
            var hName = $.trim($(this).data('head-name') || '').toLowerCase();
            if (mId) existingMasterIds.push(String(mId));
            if (hName) existingNames.push(hName);
        });

        var availableCount = 0;
        $select.find('option').each(function() {
            var val = $(this).val();
            if (!val) return;
            var optName = $.trim($(this).data('name') || '').toLowerCase();
            if (existingMasterIds.indexOf(String(val)) !== -1 || existingNames.indexOf(optName) !== -1) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
                availableCount++;
            }
        });

        if (availableCount === 0) {
            $select.prop('disabled', true);
            $('#btn_add_fee_head_to_modal').prop('disabled', true);
        } else {
            $select.prop('disabled', false);
            $('#btn_add_fee_head_to_modal').prop('disabled', false);
        }
    }

    // Add an available current-semester head as an unsaved editable row.
    $(document).on('click', '#btn_add_fee_head_to_modal', function() {
        var $select = $('#modal_available_fee_head');
        var $option = $select.find('option:selected');
        var masterId = $option.val();
        if (!masterId) {
            showFeeToast('Please select an available fee head', 'warning');
            return;
        }
        if ($('#table_student_fee_edit tbody tr[data-master-id="' + masterId + '"]').length) {
            showFeeToast('This fee head is already present in the table', 'warning');
            return;
        }

        var headName = $option.data('name') || 'Fee Head';
        var safeHeadName = $('<div>').text(headName).html();
        var amount = parseFloat($option.data('amount')) || 0;
        var dueDate = $option.data('due-date') || '';
        var rowKey = 'new_' + masterId;
        var rowNo = $('#table_student_fee_edit tbody tr.fee-detail-row').length + 1;
        var rowHtml = '<tr id="row_detail_' + rowKey + '" class="fee-detail-row table-success" data-new-row="1" data-master-id="' + masterId + '" data-head-name="' + safeHeadName + '" data-paid-amount="0" data-original-amount="' + amount + '" data-original-discount="0" data-original-due-date="' + dueDate + '" data-original-fine="0">' +
            '<td class="text-center align-middle"><input type="checkbox" class="modal-head-select" data-detail-id="' + rowKey + '" checked></td>' +
            '<td class="text-center align-middle text-muted">' + rowNo + '</td>' +
            '<td class="align-middle font-weight-bold text-dark">' + safeHeadName + '<span class="badge badge-success ml-1">New</span></td>' +
            '<td><input type="number" min="0" step="1" class="form-control form-control-sm text-right input-amount" data-master-id="' + masterId + '" value="' + amount + '"></td>' +
            '<td><input type="number" min="0" step="1" class="form-control form-control-sm text-right input-discount" value="0"></td>' +
            '<td class="align-middle text-right font-weight-bold net-display">₹' + amount.toLocaleString('en-IN') + '</td>' +
            '<td><input type="date" class="form-control form-control-sm input-due-date" value="' + dueDate + '"><input type="hidden" class="input-fine" value="0"></td>' +
            '<td class="text-center align-middle"><span class="badge badge-light border">₹0</span></td>' +
            '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-new-head" title="Remove unsaved head"><i class="fa fa-times"></i></button></td>' +
            '</tr>';
        $('#table_student_fee_edit tbody').append(rowHtml);
        $select.val('');
        syncModalAvailableFeeHeads();
        recalculateModalSummary();
        updateSelectedHeadsBadge();
        showFeeToast(headName + ' added. Enter amount and click Save All Changes.', 'success');
    });

    $(document).on('click', '.btn-remove-new-head', function() {
        var $row = $(this).closest('tr');
        $row.remove();
        syncModalAvailableFeeHeads();
        recalculateModalSummary();
        updateSelectedHeadsBadge();
    });

    // Delete an assigned head only while no collection exists against it.
    $(document).on('click', '.btn-delete-assigned-head', function() {
        var $btn = $(this);
        var admissionId = $btn.data('admission-id');
        var masterId = $btn.data('master-id');
        var headName = $btn.data('head-name') || 'Fee Head';
        if (!window.confirm('Remove "' + headName + '" from this student? This is allowed only when its collected amount is ₹0.')) {
            return;
        }
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('toggleStudentFeeHead') }}",
            method: 'POST',
            data: { admission_id: admissionId, fees_master_id: masterId, state: 0 },
            success: function(res) {
                if (res.status !== 'success') {
                    $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                    showFeeToast(res.message || 'Unable to delete fee head', 'error');
                    return;
                }
                updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                loadStudents();
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url: "{{ url('getStudentFeeDetailsModal') }}",
                    method: 'POST',
                    data: { admission_id: admissionId },
                    success: function(html) {
                        $('#studentFeeEditModalBody').html(html);
                        syncModalAvailableFeeHeads();
                    }
                });
                showFeeToast(res.message || 'Fee head deleted successfully', 'success');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to delete fee head';
                showFeeToast(message, 'error');
            }
        });
    });

    // MODAL: AUTO ALLOCATE DISCOUNT EQUALLY / PROPORTIONALLY ACROSS SELECTED HEADS WITH CAPACITY CAP
    $(document).on('click', '#btn_auto_allocate_discount', function() {
        var discountType = $('#modal_discount_type').val();
        var discountBasis = $('#modal_discount_basis').val();
        var rawDiscountVal = parseFloat($('#modal_total_discount_value').val());

        if (isNaN(rawDiscountVal) || rawDiscountVal <= 0) {
            showFeeToast('Please enter a valid discount value greater than 0', 'warning');
            $('#modal_total_discount_value').focus();
            return;
        }

        var $checkedBoxes = $('.modal-head-select:checked');
        if ($checkedBoxes.length === 0) {
            showFeeToast('Please select at least one fee head checkbox to allocate discount', 'warning');
            return;
        }

        var totalAmountOfSelected = 0;
        var totalCapacityOfSelected = 0;
        var selectedRows = [];
        $checkedBoxes.each(function() {
            var detailId = $(this).data('detail-id');
            var $row = $('#row_detail_' + detailId);
            var amt = Math.round(parseFloat($row.find('.input-amount').val()) || 0);
            var paid = parseFloat($row.attr('data-paid-amount') || $row.find('.input-discount').attr('data-paid-amount') || 0);
            var cap = Math.max(0, amt - paid);
            totalAmountOfSelected += amt;
            totalCapacityOfSelected += cap;
            selectedRows.push({ $row: $row, amt: amt, paid: paid, capacity: cap, headName: $row.attr('data-head-name') || 'Fee Head' });
        });

        if (totalCapacityOfSelected <= 0) {
            showFeeToast('Cannot allocate discount: Selected fee heads have no remaining pending balance (already fully collected).', 'warning');
            return;
        }

        var totalAssignedAll = 0;
        $('#table_student_fee_edit tbody tr').each(function() {
            var amt = Math.round(parseFloat($(this).find('.input-amount').val()) || 0);
            totalAssignedAll += amt;
        });

        var calculationBaseAmount = (discountBasis === 'total_assigned') ? totalAssignedAll : totalAmountOfSelected;
        var totalDiscountAmt = 0;

        if (discountType === 'percentage') {
            var pct = Math.min(100, Math.max(0, rawDiscountVal));
            totalDiscountAmt = Math.round((pct / 100) * calculationBaseAmount);
        } else {
            totalDiscountAmt = Math.round(Math.min(rawDiscountVal, calculationBaseAmount));
        }

        // Cap requested discount to total allocatable pending capacity across selected heads
        var isCappedByCapacity = false;
        if (totalDiscountAmt > totalCapacityOfSelected) {
            totalDiscountAmt = totalCapacityOfSelected;
            isCappedByCapacity = true;
        }

        // Distribute totalDiscountAmt across eligible heads without exceeding any head's capacity
        var remainingDisc = totalDiscountAmt;
        var eligibleRows = selectedRows.filter(function(r) { return r.capacity > 0; });
        
        // Reset discounts for checked heads first
        $.each(selectedRows, function(i, r) {
            r.$row.find('.input-discount').val(0);
        });

        var allocations = {};
        $.each(eligibleRows, function(i, r) {
            allocations[r.$row.attr('id')] = 0;
        });

        while (remainingDisc > 0 && eligibleRows.length > 0) {
            var count = eligibleRows.length;
            var share = Math.floor(remainingDisc / count);
            var rem = remainingDisc % count;
            
            var anyAllocated = false;
            var nextEligible = [];

            $.each(eligibleRows, function(idx, item) {
                var rowId = item.$row.attr('id');
                var currentAlloc = allocations[rowId] || 0;
                var room = item.capacity - currentAlloc;
                
                if (room > 0) {
                    var toAdd = share + (idx < rem ? 1 : 0);
                    if (toAdd > room) {
                        toAdd = room;
                    }
                    if (toAdd > 0) {
                        allocations[rowId] = currentAlloc + toAdd;
                        remainingDisc -= toAdd;
                        anyAllocated = true;
                    }
                    if (allocations[rowId] < item.capacity) {
                        nextEligible.push(item);
                    }
                }
            });

            if (!anyAllocated || remainingDisc <= 0) {
                break;
            }
            eligibleRows = nextEligible;
        }

        $.each(selectedRows, function(index, item) {
            var rowId = item.$row.attr('id');
            var allocatedDisc = allocations[rowId] || 0;
            item.$row.find('.input-discount').val(allocatedDisc);
            var net = Math.max(0, item.amt - allocatedDisc);
            item.$row.find('.net-display').text('₹' + net.toLocaleString('en-IN'));
        });

        recalculateModalSummary();
        var basisLabel = (discountBasis === 'total_assigned') ? 'Total Assigned Fees (₹' + totalAssignedAll.toLocaleString('en-IN') + ')' : 'Checked Heads (₹' + totalAmountOfSelected.toLocaleString('en-IN') + ')';
        var capNote = isCappedByCapacity ? ' (capped to pending balance capacity)' : '';
        showFeeToast('₹' + totalDiscountAmt.toLocaleString('en-IN') + ' discount (on ' + basisLabel + ')' + capNote + ' allocated across selected heads!', 'success');
    });

    // MODAL: RESET / CLEAR ALL DISCOUNTS TO ZERO
    $(document).on('click', '#btn_reset_discount', function() {
        var $checkedBoxes = $('.modal-head-select:checked');
        if ($checkedBoxes.length > 0) {
            $checkedBoxes.each(function() {
                var detailId = $(this).data('detail-id');
                var $row = $('#row_detail_' + detailId);
                $row.find('.input-discount').val(0);
                var amt = parseFloat($row.find('.input-amount').val()) || 0;
                $row.find('.net-display').text('₹' + amt.toLocaleString('en-IN'));
            });
        } else {
            $('.fee-detail-row').each(function() {
                $(this).find('.input-discount').val(0);
                var amt = parseFloat($(this).find('.input-amount').val()) || 0;
                $(this).find('.net-display').text('₹' + amt.toLocaleString('en-IN'));
            });
        }
        recalculateModalSummary();
        showFeeToast('Discounts set to 0 for selected heads', 'info');
    });

    // MODAL: RESET / REVERT BACK TO LAST SAVED DATABASE STATE
    $(document).on('click', '.btn-revert-saved-data, #btn_revert_saved_data', function() {
        var $rows = $('#table_student_fee_edit tbody tr.fee-detail-row');
        if ($rows.length === 0) return;

        $rows.each(function() {
            var $row = $(this);
            var origAmt = ($row.attr('data-original-amount') !== undefined && $row.attr('data-original-amount') !== '') 
                ? $row.attr('data-original-amount') 
                : ($row.find('.input-amount').data('original-value') !== undefined ? $row.find('.input-amount').data('original-value') : 0);
            
            var origDisc = ($row.attr('data-original-discount') !== undefined && $row.attr('data-original-discount') !== '') 
                ? $row.attr('data-original-discount') 
                : ($row.find('.input-discount').data('original-value') !== undefined ? $row.find('.input-discount').data('original-value') : 0);
            
            var origDueDate = ($row.attr('data-original-due-date') !== undefined) 
                ? $row.attr('data-original-due-date') 
                : ($row.find('.input-due-date').data('original-value') || '');
            
            var origFine = ($row.attr('data-original-fine') !== undefined) 
                ? $row.attr('data-original-fine') 
                : ($row.find('.input-fine').data('original-value') || 0);

            var amtNum = parseFloat(origAmt) || 0;
            var discNum = parseFloat(origDisc) || 0;
            var netNum = Math.max(0, amtNum - discNum);

            $row.find('.input-amount').removeClass('is-invalid').val(origAmt);
            $row.find('.input-discount').removeClass('is-invalid').val(origDisc);
            $row.find('.input-due-date').val(origDueDate || '');
            $row.find('.input-fine').val(origFine || 0);

            var netText = (netNum % 1 === 0) 
                ? '₹' + netNum.toLocaleString('en-IN') 
                : '₹' + netNum.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            $row.find('.net-display').text(netText);
        });

        // Reset discount value input field & checkboxes
        $('#modal_total_discount_value').val('');
        var origRemark = $('#modal_student_remark').attr('data-original-remark') || '';
        $('#modal_student_remark').val(origRemark);
        $('.modal-head-select').prop('checked', true);
        $('#modal_check_all_heads').prop('checked', true);
        updateSelectedHeadsBadge();
        recalculateModalSummary();
        showFeeToast('Reverted all fee amounts and discounts back to saved state!', 'info');
    });

    // MODAL: LIVE CALCULATION & PENDING BALANCE VALIDATION ON INPUT CHANGE
    $(document).on('input change', '.input-amount, .input-discount', function() {
        var detailId = $(this).data('detail-id');
        var $row = detailId ? $('#row_detail_' + detailId) : $(this).closest('tr.fee-detail-row');
        var headName = $row.attr('data-head-name') || 'Fee Head';
        var paidAmt = parseFloat($row.attr('data-paid-amount') || 0);
        
        var $amtInput = $row.find('.input-amount');
        var $discInput = $row.find('.input-discount');
        
        var amt = parseFloat($amtInput.val()) || 0;
        var disc = parseFloat($discInput.val()) || 0;

        // Validation 1: Amount cannot be less than collected/paid amount
        if (amt < paidAmt) {
            $amtInput.addClass('is-invalid');
            showFeeToast('Amount for ' + headName + ' cannot be less than collected amount (₹' + paidAmt.toLocaleString('en-IN') + ')', 'warning');
        } else {
            $amtInput.removeClass('is-invalid');
        }

        // Validation 2: Discount cannot exceed pending balance (amt - paidAmt)
        var maxDisc = Math.max(0, amt - paidAmt);
        if (disc > maxDisc) {
            $discInput.addClass('is-invalid');
            showFeeToast('Discount for ' + headName + ' cannot exceed pending balance (₹' + maxDisc.toLocaleString('en-IN') + ')', 'warning');
        } else {
            $discInput.removeClass('is-invalid');
        }

        var net = Math.max(0, amt - disc);
        var netText = (net % 1 === 0) 
            ? '₹' + net.toLocaleString('en-IN') 
            : '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        $row.find('.net-display').text(netText);
        recalculateModalSummary();
    });

    // MODAL: SAVE INDIVIDUAL ROW VIA AJAX
    $(document).on('click', '.btn-save-detail-row', function() {
        var detailId = $(this).data('detail-id');
        var admissionId = $(this).data('admission-id');
        var $row = $('#row_detail_' + detailId);
        var $btn = $(this);
        var headName = $row.attr('data-head-name') || 'Fee Head';
        var paidAmt = parseFloat($row.attr('data-paid-amount') || 0);

        var amount = parseFloat($row.find('.input-amount').val()) || 0;
        var discount = parseFloat($row.find('.input-discount').val()) || 0;
        var dueDate = $row.find('.input-due-date').val();
        var fine = $row.find('.input-fine').val();

        if (amount < paidAmt) {
            showFeeToast('Amount for ' + headName + ' (₹' + amount.toLocaleString('en-IN') + ') cannot be less than collected amount (₹' + paidAmt.toLocaleString('en-IN') + ')', 'error');
            $row.find('.input-amount').focus();
            return;
        }

        var maxDisc = Math.max(0, amount - paidAmt);
        if (discount > maxDisc) {
            showFeeToast('Discount for ' + headName + ' (₹' + discount.toLocaleString('en-IN') + ') cannot exceed pending balance (₹' + maxDisc.toLocaleString('en-IN') + ')', 'error');
            $row.find('.input-discount').focus();
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('updateStudentFeeDetailInline') }}",
            method: 'POST',
            data: {
                detail_id: detailId,
                amount: amount,
                discount: discount,
                due_date: dueDate,
                fine: fine
            },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-check text-success"></i> Saved');
                setTimeout(function() {
                    $btn.html('<i class="fa fa-save"></i> Save');
                }, 2000);

                if (res.status === 'success') {
                    $row.find('.input-amount, .input-discount').removeClass('is-invalid');
                    // Update original attributes on this row
                    $row.attr('data-original-amount', amount);
                    $row.attr('data-original-discount', discount);
                    $row.attr('data-original-due-date', dueDate);
                    $row.attr('data-original-fine', fine);
                    $row.find('.input-amount').data('original-value', amount);
                    $row.find('.input-discount').data('original-value', discount);
                    $row.find('.input-due-date').data('original-value', dueDate);
                    $row.find('.input-fine').data('original-value', fine);

                    if (res.total_amount !== undefined) {
                        $('#modal_summary_total').text('₹' + parseFloat(res.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    if (res.total_discount !== undefined) {
                        $('#modal_summary_discount').text('₹' + parseFloat(res.total_discount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    if (res.net_amount !== undefined) {
                        $('#modal_summary_net').text('₹' + parseFloat(res.net_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                    updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                    showFeeToast(res.message || 'Fee updated successfully', 'success');
                } else {
                    showFeeToast(res.message || 'Error updating fee', 'error');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                var errJson = xhr.responseJSON;
                if (errJson && errJson.message) {
                    showFeeToast(errJson.message, 'error');
                } else {
                    showFeeToast('Server error while saving fee detail', 'error');
                }
            }
        });
    });

    // MODAL: SAVE ALL DETAILS AT ONCE (BATCH SAVE FOR TOP & BOTTOM BUTTONS)
    $(document).on('click', '.btn-save-all-batch, #btn_save_all_modal_details', function() {
        var admissionId = $(this).data('admission-id');
        var $allBtns = $('.btn-save-all-batch, #btn_save_all_modal_details');
        var details = [];
        var validationError = null;

        $('#table_student_fee_edit tbody tr.fee-detail-row').each(function() {
            if (validationError) return;
            var $r = $(this);
            var detailId = $r.find('.input-amount').data('detail-id');
            var headName = $r.attr('data-head-name') || 'Fee Head';
            var paidAmt = parseFloat($r.attr('data-paid-amount') || 0);

            var amt = parseFloat($r.find('.input-amount').val()) || 0;
            var disc = parseFloat($r.find('.input-discount').val()) || 0;
            var dueDate = $r.find('.input-due-date').val();
            var fine = $r.find('.input-fine').val();

            if (amt < paidAmt) {
                validationError = 'Amount for ' + headName + ' (₹' + amt.toLocaleString('en-IN') + ') cannot be less than collected amount (₹' + paidAmt.toLocaleString('en-IN') + ')';
                $r.find('.input-amount').focus();
                return;
            }

            var maxDisc = Math.max(0, amt - paidAmt);
            if (disc > maxDisc) {
                validationError = 'Discount for ' + headName + ' (₹' + disc.toLocaleString('en-IN') + ') cannot exceed pending balance (₹' + maxDisc.toLocaleString('en-IN') + ')';
                $r.find('.input-discount').focus();
                return;
            }

            var masterId = $r.data('master-id') || $r.find('.input-amount').data('master-id');
            if (detailId && !$r.data('new-row')) {
                details.push({
                    id: detailId,
                    amount: amt,
                    discount: disc,
                    due_date: dueDate,
                    fine: fine
                });
            } else if (masterId) {
                details.push({
                    fees_master_id: masterId,
                    amount: amt,
                    discount: disc,
                    due_date: dueDate,
                    fine: fine
                });
            }
        });

        if (validationError) {
            showFeeToast(validationError, 'error');
            return;
        }

        if (details.length === 0) {
            showFeeToast('No fee records found to save', 'warning');
            return;
        }

        $allBtns.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('updateStudentFeeDetailsBatch') }}",
            method: 'POST',
            data: {
                admission_id: admissionId,
                details: details,
                remark: $('#modal_student_remark').val()
            },
            success: function(res) {
                $allBtns.prop('disabled', false).html('<i class="fa fa-check text-white mr-1"></i> All Saved');
                setTimeout(function() {
                    $('.btn-save-all-batch').html('<i class="fa fa-check-circle mr-1"></i> Save All');
                    $('#btn_save_all_modal_details').html('<i class="fa fa-check-circle mr-1"></i> Save All Changes');
                }, 2500);

                if (res.status === 'success') {
                    $('#table_student_fee_edit tbody tr.fee-detail-row .input-amount, #table_student_fee_edit tbody tr.fee-detail-row .input-discount').removeClass('is-invalid');
                    // Update original attributes on all rows
                    $('#table_student_fee_edit tbody tr.fee-detail-row').each(function() {
                        var $r = $(this);
                        var a = $r.find('.input-amount').val();
                        var d = $r.find('.input-discount').val();
                        var dd = $r.find('.input-due-date').val();
                        var f = $r.find('.input-fine').val();
                        $r.attr('data-original-amount', a);
                        $r.attr('data-original-discount', d);
                        $r.attr('data-original-due-date', dd);
                        $r.attr('data-original-fine', f);
                        $r.find('.input-amount').data('original-value', a);
                        $r.find('.input-discount').data('original-value', d);
                        $r.find('.input-due-date').data('original-value', dd);
                        $r.find('.input-fine').data('original-value', f);
                    });

                    if (res.total_amount !== undefined) {
                        var tot = parseFloat(res.total_amount);
                        $('#modal_summary_total').text('₹' + (tot % 1 === 0 ? tot.toLocaleString('en-IN') : tot.toLocaleString('en-IN', {minimumFractionDigits: 2})));
                    }
                    if (res.total_discount !== undefined) {
                        var disc = parseFloat(res.total_discount);
                        $('#modal_summary_discount').text('₹' + (disc % 1 === 0 ? disc.toLocaleString('en-IN') : disc.toLocaleString('en-IN', {minimumFractionDigits: 2})));
                    }
                    if (res.net_amount !== undefined) {
                        var net = parseFloat(res.net_amount);
                        $('#modal_summary_net').text('₹' + (net % 1 === 0 ? net.toLocaleString('en-IN') : net.toLocaleString('en-IN', {minimumFractionDigits: 2})));
                    }
                    if (res.remark !== undefined) {
                        $('#modal_student_remark').attr('data-original-remark', res.remark || '');
                        var $remarkCell = $('#student_remark_td_' + admissionId);
                        if ($remarkCell.length) {
                            $remarkCell.attr('data-remark', res.remark || '');
                            if (res.remark && res.remark.trim() !== '') {
                                $remarkCell.html('<div class="remark-badge p-1 px-2 rounded" style="background: #fefce8; border: 1px solid #fef08a; font-size: 11.5px; color: #854d0e; word-break: break-word; line-height: 1.3;" title="' + $('<div>').text(res.remark).html() + '"><i class="fa fa-commenting-o mr-1 text-warning"></i><span>' + $('<div>').text(res.remark).html() + '</span></div>');
                            } else {
                                $remarkCell.html('<span class="text-muted small font-italic remark-empty" style="color: #94a3b8 !important;">— No Remark —</span>');
                            }
                        }
                    }
                    updateStudentFeeSummary(admissionId, res.total_amount, res.total_discount, res.net_amount);
                    loadStudents();
                    $.ajax({
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        url: "{{ url('getStudentFeeDetailsModal') }}",
                        method: 'POST',
                        data: { admission_id: admissionId },
                        success: function(html) {
                            $('#studentFeeEditModalBody').html(html);
                            syncModalAvailableFeeHeads();
                        }
                    });
                    showFeeToast('All student fee changes saved successfully!', 'success');
                } else {
                    showFeeToast(res.message || 'Error updating fees', 'error');
                }
            },
            error: function(xhr) {
                $('.btn-save-all-batch').prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Save All');
                $('#btn_save_all_modal_details').prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Save All Changes');
                var errJson = xhr.responseJSON;
                if (errJson && errJson.message) {
                    showFeeToast(errJson.message, 'error');
                } else {
                    showFeeToast('Server error while saving fee details', 'error');
                }
            }
        });
    });

    // Save student remark independently
    $(document).on('click', '#btn_save_modal_remark', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var admissionId = $btn.data('admission-id') || $('#studentFeeEditModalBody .modal-body').data('admission-id');
        var remarkVal = $('#modal_student_remark').val();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');
        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: "{{ url('updateStudentFeeRemark') }}",
            method: 'POST',
            data: {
                admission_id: admissionId,
                remark: remarkVal
            },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Saved');
                setTimeout(function() {
                    $btn.html('<i class="fa fa-save mr-1"></i> Save Remark');
                }, 2000);
                if (res.status === 'success') {
                    showFeeToast('Fee remark saved successfully!', 'success');
                    $('#modal_student_remark').attr('data-original-remark', res.remark || '');
                    var $remarkCell = $('#student_remark_td_' + admissionId);
                    if ($remarkCell.length) {
                        $remarkCell.attr('data-remark', res.remark || '');
                        if (res.remark && res.remark.trim() !== '') {
                            $remarkCell.html('<div class="remark-badge p-1 px-2 rounded" style="background: #fefce8; border: 1px solid #fef08a; font-size: 11.5px; color: #854d0e; word-break: break-word; line-height: 1.3;" title="' + $('<div>').text(res.remark).html() + '"><i class="fa fa-commenting-o mr-1 text-warning"></i><span>' + $('<div>').text(res.remark).html() + '</span></div>');
                        } else {
                            $remarkCell.html('<span class="text-muted small font-italic remark-empty" style="color: #94a3b8 !important;">— No Remark —</span>');
                        }
                    }
                } else {
                    showFeeToast(res.message || 'Error saving remark', 'error');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Save Remark');
                showFeeToast('Failed to save fee remark', 'error');
            }
        });
    });

    // ========================================================
    // STACKED MULTI-MODAL OVERLAY & Z-INDEX MANAGEMENT
    // (Provides dark dimming overlay on the background modal when 2 modals are open)
    // ========================================================
    $(document).on('show.bs.modal', '.modal', function () {
        var $visibleModals = $('.modal:visible').not(this);
        if ($visibleModals.length > 0) {
            // Calculate higher z-index for the newly opened modal
            var baseZ = 1050 + (25 * $visibleModals.length);
            $(this).css('z-index', baseZ + 10);

            // Add dimming overlay class to background modal(s)
            $visibleModals.addClass('modal-under-overlay');

            setTimeout(function() {
                var $backdrops = $('.modal-backdrop');
                if ($backdrops.length > 0) {
                    $backdrops.last().css('z-index', baseZ + 5).addClass('modal-stack');
                }
            }, 10);
        }
    });

    $(document).on('hidden.bs.modal', '.modal', function () {
        $(this).css('z-index', '');
        var $remainingModals = $('.modal:visible');
        if ($remainingModals.length > 0) {
            // Restore normal brightness to the top-most visible modal
            $remainingModals.last().removeClass('modal-under-overlay');
            // Ensure body keeps modal-open class for scroll integrity
            $('body').addClass('modal-open');
        }
    });

    // ROBUST GLOBAL MODAL DISMISS / CANCEL / CLOSE HANDLER
    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .btn-close-modal, .close', function(e) {
        var $modal = $(this).closest('.modal');
        if ($modal.length > 0) {
            $modal.modal('hide');
        }
    });
});
</script>

@endsection
