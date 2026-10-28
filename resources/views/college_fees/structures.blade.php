@extends('layout.app')

@section('content')



<style>

/* ========================================================

   STUDENT FEE LEDGER & BATCH FEE ASSIGNMENT MATRIX

   Executive Theme Layout (Standard ERP Pattern)

   - Header Ribbon: Excel Green (#217346)

   - Title Font Size: 14.5px Standard

   - Controls & Inputs: 29px Height / 11.5px Font Size

   - Buttons: Light Header Buttons & Green Action Buttons

   - Modals: Soft Gray Header (#f1f4f6) & Excel Blue Table

   ======================================================== */

:root {

    --green-excel: #217346;

    --green-excel-dark: #185c37;

    --green-excel-light: #f0fdf4;

    --line: #cbd5e1;

    --line-border: #9aa7b1;

    --text-main: #0f172a;

    --text-muted: #64748b;

    --bg-page: #f4f6f9;

}



.content-wrapper {

    background-color: var(--bg-page);

}



.fees-matrix-page-card {

    border: 1px solid var(--line-border) !important;

    border-radius: 2px !important;

    box-shadow: none !important;

    background: #ffffff;

    margin-bottom: 0 !important;

}



/* Excel Green Ribbon Header (Standard ERP 14.5px Titlebar) */

.fees-matrix-page-card > .card-header {

    min-height: 44px;

    background: var(--green-excel) !important;

    border-radius: 0 !important;

    border-bottom: 1px solid var(--green-excel-dark) !important;

    padding: 6px 12px !important;

    display: flex !important;

    justify-content: space-between !important;

    align-items: center !important;

    width: 100% !important;

    color: #ffffff !important;

    flex-wrap: wrap;

    gap: 8px;

}



.fees-matrix-page-card > .card-header::before,

.fees-matrix-page-card > .card-header::after {

    display: none !important;

    content: none !important;

}



.fees-matrix-page-card > .card-header .card-title {

    color: #ffffff !important;

    font-size: 14.5px !important;

    letter-spacing: 0.2px;

    font-weight: 700 !important;

    margin: 0 !important;

    display: flex !important;

    align-items: center;

}



.fees-matrix-page-card > .card-header .card-title i {

    color: #ffc107 !important;

    margin-right: 8px;

    font-size: 15px;

}



/* Standard Header Light Buttons */

.btn-header-light {

    background: #ffffff !important;

    border: 1px solid #cbd5e1 !important;

    color: #1e293b !important;

    font-size: 11.5px !important;

    font-weight: 600 !important;

    border-radius: 2px !important;

    padding: 4px 10px !important;

    transition: all 0.15s ease-in-out;

    text-decoration: none !important;

    display: inline-flex;

    align-items: center;

    line-height: 1.2;

}

.btn-header-light.active-total-mode {
    background: #e8f5e9 !important;
    border-color: #81c784 !important;
    color: #1b5e20 !important;
    font-weight: 700 !important;
}

.btn-header-light.active-total-mode:hover {
    background: #c8e6c9 !important;
    border-color: #66bb6a !important;
    color: #1b5e20 !important;
}

.btn-header-light:hover {

    background: #f1f5f9 !important;

    border-color: #94a3b8 !important;

    color: #0f172a !important;

}



/* Mode Switcher Pill in Header */

.mode-toggle-group {

    background: rgba(0, 0, 0, 0.22);

    border: 1px solid rgba(255, 255, 255, 0.25);

    border-radius: 3px;

    padding: 2px;

    display: inline-flex;

}



.mode-toggle-btn {

    border: none;

    background: transparent;

    padding: 3px 10px;

    font-size: 11px;

    font-weight: 700;

    border-radius: 2px;

    cursor: pointer;

    transition: all 0.15s ease;

    color: #e2f0d9;

    text-decoration: none !important;

}



.mode-toggle-btn.active {

    background: #ffffff;

    color: var(--green-excel-dark);

    box-shadow: 0 1px 2px rgba(0,0,0,0.15);

}



.status-pill-autosave {

    font-size: 11px;

    font-weight: 600;

    color: #ffffff;

    padding: 4px 9px;

    background: rgba(255, 255, 255, 0.18);

    border: 1px solid rgba(255, 255, 255, 0.3);

    border-radius: 2px;

    display: inline-flex;

    align-items: center;

    gap: 4px;

}



/* KPI Summary Metric Strip */

.ledger-kpi-grid {

    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 10px;

    padding: 10px 12px;

    background: #f8fafc;

    border-bottom: 1px solid #cbd5e1;

}



.kpi-card {

    background: #ffffff;

    border: 1px solid #cbd5e1;

    border-top: 3px solid var(--green-excel);

    border-radius: 2px;

    padding: 8px 12px;

}



.kpi-card-label {

    font-size: 10.5px;

    font-weight: 700;

    color: #475569;

    text-transform: uppercase;

    letter-spacing: 0.35px;

    margin-bottom: 2px;

}



.kpi-card-value {

    font-size: 18px;

    font-weight: 800;

    color: #0f172a;

    line-height: 1.2;

}



/* Main Split Area */

.ledger-main-layout {

    display: flex;

    padding: 10px 12px;

    gap: 12px;

    align-items: flex-start;

    background: #f4f6f9;

}



.ledger-sidebar {

    width: 260px;

    flex-shrink: 0;

}



.ledger-content {

    flex-grow: 1;

    min-width: 0;

}



/* Sidebar Panels */

.side-card {

    background: #ffffff;

    border: 1px solid #cbd5e1;

    border-radius: 2px;

    padding: 10px 12px;

    margin-bottom: 10px;

}



.side-label {

    font-size: 10.5px;

    font-weight: 700;

    text-transform: uppercase;

    color: #334155;

    letter-spacing: 0.35px;

    margin-bottom: 4px;

    display: block;

}



.toolbar-control {

    height: 29px !important;

    min-height: 29px !important;

    padding: 3px 8px !important;

    font-size: 11.5px !important;

    color: #172033 !important;

    background: #ffffff !important;

    border: 1px solid #b8c4d1 !important;

    border-radius: 2px !important;

    width: 100%;

}

.toolbar-control:focus {

    border-color: var(--green-excel) !important;

    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25) !important;

    outline: none;

}



/* Action Buttons Matching Promote Page */

.btn-search-action {

    height: 29px !important;

    padding: 3px 12px !important;

    font-size: 11.5px !important;

    font-weight: 700 !important;

    background: var(--green-excel) !important;

    border: 1px solid var(--green-excel-dark) !important;

    color: #ffffff !important;

    border-radius: 2px !important;

    transition: all 0.15s ease-in-out;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    line-height: 1;

}

.btn-search-action:hover {

    background: var(--green-excel-dark) !important;

    color: #ffffff !important;

}

/* ERP Theme Modal Buttons & Tools */
.btn-collect-tool {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    padding: 3px 8px !important;
    height: 26px !important;
    display: inline-flex !important;
    align-items: center !important;
    line-height: 1 !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
}
.btn-collect-tool:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

/* Payment Mode Segmented Pills */
.collect-mode-group {
    display: inline-flex;
    flex-wrap: wrap;
    background: #f1f5f9;
    padding: 3px;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    gap: 4px;
}
.collect-mode-btn {
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    padding: 4px 12px !important;
    height: 28px !important;
    cursor: pointer !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: all 0.15s ease !important;
    line-height: 1;
}
.collect-mode-btn:hover {
    background: #e2e8f0 !important;
    color: #0f172a !important;
}
.collect-mode-btn.active {
    background: var(--green-excel) !important;
    color: #ffffff !important;
    border-color: var(--green-excel-dark) !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15) !important;
}

/* Collect Modal Bottom Buttons */
.btn-collect-cancel {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    height: 32px !important;
    padding: 4px 14px !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}
.btn-collect-cancel:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-collect-submit {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    border-radius: 2px !important;
    height: 32px !important;
    padding: 4px 16px !important;
    cursor: pointer !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
    transition: all 0.15s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}
.btn-collect-submit:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
}




/* Semester Pills */

.sem-pills-wrapper {

    display: flex;

    flex-wrap: wrap;

    gap: 4px;

}



.sem-pill-btn {

    border: 1px solid #cbd5e1;

    background: #ffffff;

    padding: 4px 9px;

    border-radius: 2px;

    font-size: 11px;

    font-weight: 600;

    color: #334155;

    cursor: pointer;

    transition: all 0.15s ease;

}

.sem-pill-btn:hover {

    background: #f1f5f9;

}

.sem-pill-btn.active {

    background: var(--green-excel);

    color: #ffffff;

    border-color: var(--green-excel-dark);

}



/* Batch First Fees Card */

.batch-fees-card {

    background: #e7eff5;

    border: 1px solid #b8c9d9;

    border-radius: 2px;

    padding: 10px 12px;

    margin-bottom: 10px;

}

.batch-fees-title {

    font-size: 10.5px;

    font-weight: 700;

    color: #1e293b;

    text-transform: uppercase;

    letter-spacing: 0.35px;

    margin-bottom: 6px;

    display: flex;

    align-items: center;

}

.batch-fees-row {

    display: flex;

    justify-content: space-between;

    font-size: 11.5px;

    margin-bottom: 2px;

}

.batch-fees-row span {

    color: #475569;

}

.batch-fees-row strong {

    color: #0f172a;

    font-weight: 700;

}

.batch-fees-status {

    font-size: 10.5px;

    color: #166534;

    margin: 6px 0 8px 0;

    display: flex;

    align-items: center;

    gap: 4px;

    font-weight: 600;

}



/* Filter Pills */

.filter-pills-row {

    display: flex;

    flex-wrap: wrap;

    gap: 4px;

}

.filter-pill-btn {

    border: 1px solid #cbd5e1;

    background: #ffffff;

    padding: 3px 8px;

    border-radius: 2px;

    font-size: 11px;

    font-weight: 600;

    color: #334155;

    cursor: pointer;

    transition: all 0.15s ease;

}

.filter-pill-btn:hover {

    background: #f1f5f9;

}

.filter-pill-btn.active {

    background: var(--green-excel);

    color: #ffffff;

    border-color: var(--green-excel-dark);

}



/* Spreadsheet Box & Table */

.matrix-container-card {

    background: #ffffff;

    border: 1px solid #cbd5e1;

    border-radius: 2px;

    overflow: hidden;

}



.matrix-action-bar {

    padding: 6px 10px;

    background: #f8fafc;

    border-bottom: 1px solid #cbd5e1;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 8px;

    flex-wrap: wrap;

}



.search-input-wrapper {

    position: relative;

    width: 250px;

}

.search-input-wrapper i {

    position: absolute;

    left: 8px;

    top: 8px;

    color: #94a3b8;

    font-size: 11.5px;

}

.search-input-field {

    width: 100%;

    height: 29px;

    padding-left: 26px;

    padding-right: 8px;

    font-size: 11.5px;

    border: 1px solid #b8c4d1;

    border-radius: 2px;

    outline: none;

}

.search-input-field:focus {

    border-color: var(--green-excel);

    box-shadow: 0 0 0 1px rgba(33, 115, 70, 0.25);

}



.btn-matrix-tool {

    background: #ffffff;

    border: 1px solid #cbd5e1;

    color: #1e293b;

    font-size: 11.5px;

    font-weight: 600;

    padding: 3px 10px;

    height: 29px;

    border-radius: 2px;

    cursor: pointer;

    transition: all 0.15s ease;

    display: inline-flex;

    align-items: center;

    text-decoration: none !important;

}

.btn-matrix-tool:hover {

    background: #f1f5f9;

    border-color: #94a3b8;

    color: #0f172a;

}



/* Custom Scrollbars for Matrix Table */
#matrixTableResponsive::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}
#matrixTableResponsive::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
}
#matrixTableResponsive::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 6px;
    border: 2px solid #f1f5f9;
}
#matrixTableResponsive::-webkit-scrollbar-thumb:hover {
    background: #475569;
}

#matrixTableResponsive {
    scrollbar-width: auto;
    scrollbar-color: #94a3b8 #f1f5f9;
}

/* Fixed Bottom Horizontal Scrolling Tool Box */
.matrix-fixed-scroll-toolbox {
    position: fixed;
    bottom: 85px; /* Positioned safely above tfoot totals row so data remains 100% visible */
    z-index: 1045;
    background: #217346;
    background: linear-gradient(135deg, #185c37 0%, #217346 100%);
    color: #ffffff;
    border-radius: 40px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.35), 0 3px 8px rgba(24, 92, 55, 0.4);
    padding: 5px 14px;
    display: none;
    align-items: center;
    border: 1.5px solid #14532d;
    backdrop-filter: blur(10px);
    user-select: none;
    transition: opacity 0.2s ease, transform 0.15s ease, bottom 0.15s ease;
}

.matrix-fixed-scroll-toolbox .toolbox-label {
    font-size: 11px;
    font-weight: 800;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.matrix-fixed-scroll-toolbox .btn-toolbox-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    font-weight: 700;
    font-size: 11px;
    padding: 4px 11px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.15s ease;
    outline: none;
    line-height: 1.3;
    white-space: nowrap;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
}

.matrix-fixed-scroll-toolbox .btn-toolbox-action:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

.matrix-fixed-scroll-toolbox .btn-toolbox-action:active {
    transform: translateY(0);
    background: #e2e8f0;
}

.matrix-fixed-scroll-toolbox .btn-toolbox-action-accent {
    background: #f59e0b;
    border-color: #d97706;
    color: #0f172a !important;
    font-weight: 800 !important;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.45);
}

.matrix-fixed-scroll-toolbox .btn-toolbox-action-accent:hover {
    background: #fbbf24;
    border-color: #f59e0b;
    color: #000000 !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.6);
    transform: translateY(-1px);
}

.matrix-fixed-scroll-toolbox .toolbox-slider-track {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(15, 47, 28, 0.65);
    border-radius: 20px;
    padding: 3px 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.matrix-fixed-scroll-toolbox .slider-end-label {
    font-size: 9.5px;
    font-weight: 700;
    color: #dcfce7;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

.matrix-fixed-scroll-toolbox .toolbox-range-slider {
    width: 130px;
    height: 6px;
    cursor: pointer;
    accent-color: #f59e0b;
    margin: 0;
    vertical-align: middle;
}

/* Matrix Table Styling */

.matrix-table {

    width: 100% !important;

    min-width: 100% !important;

    max-width: none !important;

    margin-bottom: 0;

    border-collapse: separate;

    border-spacing: 0;

    font-size: 11.5px;

    table-layout: auto;

    border-left: 1px solid #b8c9d9;

    border-top: 1px solid #8fa7b8;

}

.matrix-table th {

    background: #d9eaf7 !important;

    color: #152536 !important;

    font-size: 10.5px !important;

    font-weight: 700 !important;

    text-transform: uppercase;

    letter-spacing: 0.2px;

    padding: 5px 6px !important;

    border-bottom: 2px solid #8fa7b8 !important;

    border-right: 1px solid #b8c9d9 !important;

    white-space: nowrap !important;

    position: sticky;

    top: 0;

    z-index: 10;

}

.matrix-table tfoot th,
.matrix-table tfoot td {
    background: #e7eff5 !important;
    color: #1e293b !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    border-top: 2px solid #8fa7b8 !important;
    border-bottom: 2px solid #8fa7b8 !important;
    border-right: 1px solid #b8c9d9 !important;
    white-space: nowrap !important;
    position: sticky;
    bottom: 0;
    z-index: 10;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.06);
}

.matrix-table td {

    padding: 4px 6px !important;

    vertical-align: middle !important;

    border-bottom: 1px solid #cbd5e1 !important;

    border-right: 1px solid #e2e8f0 !important;

    white-space: nowrap;

}

.col-matrix-cb {
    width: 34px !important;
    min-width: 34px !important;
    max-width: 34px !important;
    text-align: center !important;
    padding: 4px 2px !important;
    white-space: nowrap !important;
}

.col-matrix-idx {
    width: 38px !important;
    min-width: 38px !important;
    max-width: 38px !important;
    text-align: center !important;
    padding: 4px 2px !important;
    white-space: nowrap !important;
}

.col-matrix-student {
    min-width: 180px !important;
    max-width: none !important;
    white-space: nowrap !important;
    padding: 3px 8px !important;
}

.col-matrix-amount {
    white-space: nowrap !important;
    text-align: right !important;
    padding: 3px 6px !important;
    width: 1%;
}

.col-matrix-action {
    white-space: nowrap !important;
    text-align: center !important;
    padding: 3px 6px !important;
    width: 1%;
}

.matrix-table tr:hover td {

    background-color: #f8fafc;

}

/* Animated Highlight for Target Student Row (from Global Header Search) */
@keyframes rowStudentSearchPulse {
    0% {
        background-color: #fef08a !important;
        border-top: 3px solid #d97706 !important;
        border-bottom: 3px solid #d97706 !important;
        box-shadow: inset 0 2px 0 #d97706, inset 0 -2px 0 #d97706, 0 0 16px rgba(217, 119, 6, 0.55) !important;
    }
    50% {
        background-color: #fde047 !important;
        border-top: 3px solid #b45309 !important;
        border-bottom: 3px solid #b45309 !important;
        box-shadow: inset 0 3px 0 #b45309, inset 0 -3px 0 #b45309, 0 0 24px rgba(180, 83, 9, 0.85) !important;
    }
    100% {
        background-color: #fef9c3 !important;
        border-top: 3px solid #ca8a04 !important;
        border-bottom: 3px solid #ca8a04 !important;
        box-shadow: inset 0 2px 0 #ca8a04, inset 0 -2px 0 #ca8a04, 0 0 14px rgba(202, 138, 4, 0.5) !important;
    }
}

.matrix-table tr.row-student-searched > td,
tr.row-student-searched > td {
    animation: rowStudentSearchPulse 0.35s ease-in-out infinite alternate !important;
    background-color: #fef08a !important;
    background-image: none !important;
    color: #1e293b !important;
    position: relative !important;
    z-index: 10 !important;
}

.matrix-table tr.row-student-searched > td:first-child,
tr.row-student-searched > td:first-child {
    border-left: 4.5px solid #b45309 !important;
}

.matrix-table tr.row-student-searched > td:last-child,
tr.row-student-searched > td:last-child {
    border-right: 4.5px solid #b45309 !important;
}

.matrix-table tr.row-student-searched td .cell-fee-input,
tr.row-student-searched td .cell-fee-input {
    background-color: #fffbeb !important;
    border-color: #d97706 !important;
    font-weight: 700 !important;
}

.matrix-table tr.row-student-focused > td,
tr.row-student-focused > td {
    background: #fef9c3 !important;
    background-color: #fef9c3 !important;
    background-image: none !important;
    border-top: 2px solid #ca8a04 !important;
    border-bottom: 2px solid #ca8a04 !important;
    transition: background-color 0.3s ease !important;
    position: relative !important;
    z-index: 5 !important;
}

.matrix-table tr.row-student-focused:hover > td,
tr.row-student-focused:hover > td {
    background: #fef08a !important;
    background-color: #fef08a !important;
}

.matrix-table tr.row-student-focused > td:first-child,
tr.row-student-focused > td:first-child {
    border-left: 4.5px solid #ca8a04 !important;
}

.matrix-table tr.row-student-focused > td:last-child,
tr.row-student-focused > td:last-child {
    border-right: 4.5px solid #ca8a04 !important;
}

.matrix-table tr.row-student-focused td .cell-fee-input,
tr.row-student-focused td .cell-fee-input {
    background-color: #fffbeb !important;
    border-color: #d97706 !important;
    font-weight: 700 !important;
}



/* Editable Cell */

.cell-fee-input {

    width: 68px;

    height: 24px;

    border: 1px solid #b8c4d1;

    border-radius: 2px;

    padding: 2px 6px;

    font-size: 11.5px;

    font-weight: 600;

    color: #0f172a;

    text-align: right;

    background: #ffffff;

    transition: all 0.15s ease;

}

.cell-fee-input:focus {

    border: 1.5px solid var(--green-excel) !important;

    background: #f0fdf4 !important;

    outline: none;

}



.student-cell-name {

    font-weight: 700;

    color: #0f172a;

    font-size: 11px;

    line-height: 1.2;

    max-width: none;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}

.student-cell-sub {

    font-size: 9.5px;

    color: #64748b;

    margin-top: 1px;

    line-height: 1.1;

}



.custom-head-tag {

    display: inline-flex;

    align-items: center;

    padding: 1px 6px;

    font-size: 10px;

    font-weight: 600;

    background: #f1f5f9;

    border: 1px solid #cbd5e1;

    border-radius: 2px;

    margin: 1px;

    color: #334155;

    white-space: nowrap;

}

.custom-head-tag .del-head-btn {

    cursor: pointer;

    margin-left: 4px;

    color: #ef4444;

}



.discount-reason-pill {

    font-size: 9.5px;

    font-weight: 700;

    color: #b45309;

    background: #fef3c7;

    border: 1px solid #fde68a;

    padding: 1px 5px;

    border-radius: 2px;

    cursor: pointer;

    white-space: nowrap;

}

.discount-reason-pill:hover {

    background: #fde68a;

}



.preset-badge-pill {

    display: inline-block;

    padding: 2px 8px;

    font-size: 11px;

    font-weight: 600;

    background: #f1f5f9;

    color: #334155;

    border-radius: 2px;

    cursor: pointer;

    border: 1px solid #cbd5e1;

    margin: 2px;

    user-select: none;

    transition: all 0.15s ease;

}

.preset-badge-pill:hover {

    background: var(--green-excel);

    color: #ffffff;

    border-color: var(--green-excel-dark);

}



.matrix-footer-note {

    padding: 6px 12px;

    background: #f8fafc;

    border-top: 1px solid #cbd5e1;

    font-size: 11px;

    color: #475569;

}



.matrix-loading-overlay {

    position: absolute;

    top: 0; left: 0; right: 0; bottom: 0;

    background: rgba(255, 255, 255, 0.75);

    display: flex;

    align-items: center;

    justify-content: center;

    z-index: 10;

}



/* Modals Matching Promote Students Page Theme */

.modal-content {

    border: 1px solid #9eacb7 !important;

    border-radius: 2px !important;

    overflow: hidden;

    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22) !important;

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

    color: var(--green-excel) !important;

    font-size: 14px !important;

    margin-right: 6px;

}

.modal-header .close {

    margin: 0 !important;

    padding: 2px 6px !important;

    color: #344450 !important;

    opacity: 0.85 !important;

    font-size: 20px !important;

    background: transparent !important;

    border: 0 !important;

    cursor: pointer !important;

}

.modal-header .close:hover {

    color: #000000 !important;

    opacity: 1 !important;

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



/* Quick Assign Modal Custom Styling */

.btn-qa-head-chip {

    font-size: 11px;

    font-weight: 600;

    padding: 4px 10px;

    border-radius: 4px;

    border: 1px solid #cbd5e1;

    background: #ffffff;

    color: #1e293b;

    cursor: pointer;

    transition: all 0.15s ease;

    display: inline-flex;

    align-items: center;

    gap: 6px;

    line-height: 1.3;

    user-select: none;

}

.btn-qa-head-chip:hover {

    background: #f0fdf4;

    border-color: #217346;

    color: #166534;

    transform: translateY(-1px);

}

.btn-qa-head-chip.active {

    background: #217346 !important;

    border-color: #185c37 !important;

    color: #ffffff !important;

    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.25);

}

.btn-qa-head-chip.active .chip-icon {

    color: #a7f3d0 !important;

}

.btn-qa-head-chip .chip-type {

    font-size: 9.5px;

    font-weight: 500;

    opacity: 0.8;

}

.btn-qa-head-chip.active .chip-type {

    color: #d1fae5;

}

.qa-chips-container {

    display: flex;

    flex-wrap: wrap;

    gap: 6px;

    max-height: 130px;

    overflow-y: auto;

    padding: 8px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 4px;

}

.qa-section-header {

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    color: #334155;

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 6px;

}

.qa-selected-table th {

    background: #d9eaf7 !important;

    color: #152536 !important;

    font-size: 11px !important;

    font-weight: 700 !important;

    padding: 6px 8px !important;

    border-bottom: 2px solid #8fa7b8 !important;

}

.qa-selected-table td {

    padding: 4px 8px !important;

    font-size: 11.5px !important;

    vertical-align: middle !important;

}

.qa-summary-bar {

    background: #f0fdf4;

    border: 1px solid #bbf7d0;

    border-radius: 4px;

    padding: 6px 12px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    font-size: 11.5px;

}


/* Animated pulsing border for receipt currently in edit mode */
@keyframes receiptBorderGlow {
    0% {
        border-color: #f59e0b;
        box-shadow: 0 0 0 1.5px #f59e0b, 0 0 8px rgba(245, 158, 11, 0.45);
        background-color: #fffdf5;
    }
    50% {
        border-color: #d97706;
        box-shadow: 0 0 0 3px #d97706, 0 0 16px rgba(217, 119, 6, 0.7);
        background-color: #fef3c7;
    }
    100% {
        border-color: #f59e0b;
        box-shadow: 0 0 0 1.5px #f59e0b, 0 0 8px rgba(245, 158, 11, 0.45);
        background-color: #fffdf5;
    }
}

.collect-history-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    transition: all 0.2s ease;
}

.collect-history-card.is-editing-receipt {
    border: 2px solid #f59e0b !important;
    animation: receiptBorderGlow 1.25s infinite ease-in-out !important;
    position: relative;
    z-index: 5;
}

#receipt_edit_mode_bar {
    display: none !important;
    border: 1.5px solid #f59e0b;
    background: #fffbeb;
    color: #92400e;
    border-radius: 4px;
}

#receipt_edit_mode_bar.is-active {
    display: flex !important;
}


/* Standardized Theme Button & Hover Rules (High Contrast Legibility) */
.btn-outline-secondary {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
}
.btn-outline-secondary:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-outline-secondary:hover * {
    color: #0f172a !important;
}
.btn-secondary {
    background: #e2e8f0 !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
}
.btn-secondary:hover {
    background: #cbd5e1 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-secondary:hover * {
    color: #0f172a !important;
}
.btn-outline-primary {
    background: #ffffff !important;
    border: 1px solid #0284c7 !important;
    color: #0284c7 !important;
}
.btn-outline-primary:hover {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #ffffff !important;
}
.btn-outline-primary:hover * {
    color: #ffffff !important;
}
.btn-outline-success {
    background: #ffffff !important;
    border: 1px solid #16a34a !important;
    color: #166534 !important;
}
.btn-outline-success:hover {
    background: #16a34a !important;
    border-color: #16a34a !important;
    color: #ffffff !important;
}
.btn-outline-success:hover * {
    color: #ffffff !important;
}
.btn-outline-danger {
    background: #ffffff !important;
    border: 1px solid #ef4444 !important;
    color: #dc2626 !important;
}
.btn-outline-danger:hover {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
}
.btn-outline-danger:hover * {
    color: #ffffff !important;
}
.btn-matrix-tool:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-matrix-tool:hover * {
    color: #0f172a !important;
}
.btn-matrix-tool:hover .text-success { color: #16a34a !important; }
.btn-matrix-tool:hover .text-primary { color: #0284c7 !important; }
.btn-matrix-tool:hover .text-secondary { color: #64748b !important; }
.btn-matrix-tool:hover .text-danger { color: #dc2626 !important; }
.filter-pill-btn:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
.filter-pill-btn.active:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
    border-color: var(--green-excel-dark) !important;
}
.sem-pill-btn:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
.sem-pill-btn.active:hover {
    background: var(--green-excel-dark) !important;
    color: #ffffff !important;
    border-color: var(--green-excel-dark) !important;
}
.mode-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}
.mode-toggle-btn.active:hover {
    background: #ffffff !important;
    color: var(--green-excel-dark) !important;
}

</style>



<div class="content-wrapper">

    <section class="content pt-2 pb-3">

        <div class="container-fluid px-2">

            <div class="card fees-matrix-page-card">



                <!-- Excel Green Ribbon Header (Standard ERP 14.5px Titlebar) -->

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fa fa-table"></i> Student Fee Ledger &amp; Batch Assignment Matrix

                        <span class="badge badge-light text-dark ml-2" style="font-size: 10px; font-weight: 700; border: 1px solid rgba(255,255,255,0.4);">Executive Matrix</span>

                    </h3>

                    <div class="card-tools d-flex align-items-center" style="gap: 6px;">

                        

                        {{-- Mode Switcher Pill (Commented out as requested - can be uncommented later)
                        <div class="mode-toggle-group">
                            <button type="button" class="mode-toggle-btn active" id="btnModeAssign">Assign Mode</button>
                            <a href="#" class="mode-toggle-btn" id="btnModeCollect">Collect Mode</a>
                        </div>
                        --}}

                        <!-- Auto-saved Indicator -->

                        <div class="status-pill-autosave" id="autoSaveIndicator">

                            <i class="fa fa-check"></i> <span id="autoSaveText">Auto-saved</span>

                        </div>

                        {{-- Fee Collection Mode Toggle Button (Commented out as requested - can be uncommented later)
                        <button type="button" class="btn-header-light" id="btnHeaderToggleCollectMode" onclick="promptSwitchCollectionMode()" title="Switch between Total Dues collection and Itemized head-wise collection">
                            <i class="fa fa-toggle-off text-muted mr-1" id="iconHeaderCollectMode"></i>
                            <span id="lblHeaderCollectModeText">Collection: Total Dues</span>
                            <span class="badge badge-secondary ml-1" id="badgeHeaderCollectMode" style="font-size: 9.5px; padding: 2px 5px;">OFF</span>
                        </button>
                        --}}

                        <!-- Header Action Buttons -->

                        <button type="button" class="btn-header-light" onclick="openCommonHeadModal()" title="Common Fee Head Master">

                            <i class="fa fa-cubes text-success mr-1"></i> Common Fee Head

                        </button>

                        {{-- Cashier Terminal Button (Commented out as requested - can be uncommented later)
                        <a href="#" class="btn-header-light" title="Cashier Terminal">
                            <i class="fa fa-calculator text-primary mr-1"></i> Cashier Terminal
                        </a>
                        --}}

                        <a href="{{ url('college/fees/dashboard') }}" class="btn-header-light" title="Fees Dashboard">

                            <i class="fa fa-dashboard text-info mr-1"></i> Dashboard

                        </a>

                    </div>

                </div>







                <!-- Main Split Area: Left Sidebar (260px) + Right Matrix Table -->

                <div class="ledger-main-layout">

                    

                    <!-- Left Sidebar Controls -->

                    <div class="ledger-sidebar">

                        

                        <!-- Course Selector -->

                        <div class="side-card">

                            <label class="side-label" for="selLedgerCourse">Course <span class="text-danger">*</span></label>

                            <select id="selLedgerCourse" class="toolbar-control" onchange="onFilterChange(true)">

                                @foreach($courses as $c)

                                    <option value="{{ $c->id }}" data-sems="{{ $c->total_semester ?: 6 }}" {{ $c->id == $courseId ? 'selected' : '' }}>

                                        {{ $c->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>



                        <!-- Admission Batch Selector -->

                        <div class="side-card">

                            <label class="side-label" for="selLedgerBatch">Admission Batch <span class="text-danger">*</span></label>

                            <select id="selLedgerBatch" class="toolbar-control" onchange="onFilterChange(true)">

                                @foreach($batches as $b)

                                    <option value="{{ $b }}" {{ $b == $batch ? 'selected' : '' }}>Batch {{ $b }}</option>

                                @endforeach

                            </select>

                        </div>



                        <!-- Semester Selector Pills -->

                        <div class="side-card">

                            <label class="side-label">Semester</label>

                            <div class="sem-pills-wrapper" id="semPillsContainer">

                                @for($s = 1; $s <= ($maxSemesters ?? 6); $s++)

                                    <button type="button" class="sem-pill-btn {{ $s == $semesterNo ? 'active' : '' }}" onclick="selectSemester({{ $s }})">

                                        Sem {{ $s }}

                                    </button>

                                @endfor

                            </div>

                        </div>



                        <!-- Batch First Fees Card -->

                        <div class="batch-fees-card">

                            <div class="batch-fees-title">

                                <i class="fa fa-sliders text-success mr-1"></i> Batch First Fees

                            </div>

                            <div class="batch-fees-row">

                                <span>Boys Standard:</span>

                                <strong id="batchFeeBoysAmt">₹0</strong>

                            </div>

                            <div class="batch-fees-row">

                                <span>Girls Standard:</span>

                                <strong id="batchFeeGirlsAmt">₹0</strong>

                            </div>

                            <div class="batch-fees-status" id="batchFeeStatusText">

                                <i class="fa fa-check-circle"></i> <span>Configured · 0 Students Assigned</span>

                            </div>

                            <button type="button" class="btn btn-search-action btn-block" onclick="openBatchFeesModal()">

                                <i class="fa fa-cog mr-1"></i> Set / Edit Batch Fees

                            </button>

                        </div>



                        <!-- Fee Status Filter -->

                        <div class="side-card">

                            <label class="side-label">Fee Status</label>

                            <div class="filter-pills-row">

                                <button type="button" class="filter-pill-btn active" data-status="all" onclick="selectStatusFilter(this, 'all')">All</button>

                                <button type="button" class="filter-pill-btn" data-status="pending" onclick="selectStatusFilter(this, 'pending')">Pending</button>

                                <button type="button" class="filter-pill-btn" data-status="partial" onclick="selectStatusFilter(this, 'partial')">Partial</button>

                                <button type="button" class="filter-pill-btn" data-status="paid" onclick="selectStatusFilter(this, 'paid')">Paid</button>

                            </div>

                        </div>



                        <!-- Gender Filter -->

                        <div class="side-card">

                            <label class="side-label">Gender</label>

                            <div class="filter-pills-row">

                                <button type="button" class="filter-pill-btn active" data-gender="all" onclick="selectGenderFilter(this, 'all')">All</button>

                                <button type="button" class="filter-pill-btn" data-gender="boys" onclick="selectGenderFilter(this, 'boys')">Boys</button>

                                <button type="button" class="filter-pill-btn" data-gender="girls" onclick="selectGenderFilter(this, 'girls')">Girls</button>

                            </div>

                        </div>



                    </div>



                    <!-- Main Content: Matrix Spreadsheet -->

                    <div class="ledger-content">

                        <div class="matrix-container-card position-relative">

                            

                            <!-- Unified Filter & Action Toolbar -->

                            <div class="matrix-action-bar">

                                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">

                                    <div class="search-input-wrapper">

                                        <i class="fa fa-search"></i>

                                        <input type="text" id="matrixSearchInput" class="search-input-field" placeholder="Search by student name or roll no..." onkeyup="onSearchInput(this.value)">

                                    </div>

                                    <span class="badge badge-light border text-dark font-weight-bold" id="badgeSelectedCount" style="font-size: 11px; padding: 5px 8px; display: inline-flex; align-items: center; gap: 4px;">

                                        <i class="fa fa-check-square-o text-success"></i> <span id="txtSelectedCount">0</span> Selected

                                    </span>

                                    <button type="button" class="btn-matrix-tool" onclick="selectAllStudents(true)" title="Select all students in table">

                                        <i class="fa fa-check-square-o text-success mr-1"></i> All

                                    </button>

                                    <button type="button" class="btn-matrix-tool" onclick="selectAllStudents(false)" title="Clear student selection">

                                        <i class="fa fa-square-o text-secondary mr-1"></i> Clear

                                    </button>

                                </div>

                                <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">



                                    <button type="button" class="btn-matrix-tool" id="btnToggleGlobalNotes" onclick="toggleDiscountNotesGlobal()" title="Toggle discount notes visibility for students">
                                        <i class="fa fa-eye text-primary mr-1" id="iconGlobalNotes"></i> <span id="lblGlobalNotes">Show Notes</span>
                                    </button>

                                    <button type="button" class="btn-search-action px-3" onclick="openQuickAssignFeesModal()" id="btnQuickAssignFees" title="Assign common fee heads to selected students">

                                        <i class="fa fa-magic mr-1"></i> Quick Assign Fees (<span id="btnQuickAssignCount">0</span>)

                                    </button>

                                    <button type="button" class="btn-matrix-tool" onclick="exportMatrixTable()" title="Export Fee Matrix to CSV">

                                        <i class="fa fa-download text-primary mr-1"></i> Export CSV

                                    </button>

                                </div>

                            </div>



                            <!-- Matrix Table Wrapper (Auto-adjusted height to fit screen with overflow) -->
                            <div class="table-responsive" id="matrixTableResponsive" style="height: calc(100vh - 200px); min-height: 380px; overflow-x: auto; overflow-y: auto;">

                                <table class="table matrix-table" id="matrixTable">

                                    <thead id="matrixTableHead">

                                        <!-- Dynamic Headers Injected by JS -->

                                    </thead>

                                    <tbody id="matrixTableBody">

                                        <tr>

                                            <td colspan="10" class="text-center py-5 text-muted">

                                                <i class="fa fa-spinner fa-spin fa-2x mb-2 text-primary"></i><br>

                                                Loading student fee ledger matrix...

                                            </td>

                                        </tr>

                                    </tbody>

                                    <tfoot id="matrixTableFoot">

                                        <!-- Dynamic Totals & Stats Injected by JS -->

                                    </tfoot>

                                </table>

                            </div>



                            <!-- Fixed Bottom Horizontal Scrolling Tool Box (Theme Green Header Ribbon Style) -->
                            <div id="matrixFixedScrollToolbox" class="matrix-fixed-scroll-toolbox" style="display: none;">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span class="toolbox-label">
                                        <i class="fa fa-arrows-h mr-1" style="color: #86efac;"></i> Scroll Table:
                                    </span>

                                    <button type="button" class="btn-toolbox-action" onclick="scrollMatrixTable('start')" title="Jump to first column (Student Name)">
                                        <i class="fa fa-angle-double-left mr-1" style="color: #217346;"></i> Start
                                    </button>

                                    <button type="button" class="btn-toolbox-action btn-matrix-hold-scroll" data-dir="left" onclick="scrollMatrixTable('left')" title="Scroll Left (Click or Hold to slide)">
                                        <i class="fa fa-chevron-left mr-1" style="color: #217346;"></i> Left
                                    </button>

                                    <!-- Interactive Drag-to-Scroll Slider Bar -->
                                    <div class="toolbox-slider-track">
                                        <span class="slider-end-label">Student</span>
                                        <input type="range" min="0" max="100" value="0" id="matrixBottomScrollSlider" class="toolbox-range-slider" oninput="onMatrixSliderChange(this.value)" title="Drag slider to scroll table horizontally">
                                        <span class="slider-end-label">Action</span>
                                    </div>

                                    <button type="button" class="btn-toolbox-action btn-matrix-hold-scroll" data-dir="right" onclick="scrollMatrixTable('right')" title="Scroll Right (Click or Hold to slide)">
                                        Right <i class="fa fa-chevron-right ml-1" style="color: #217346;"></i>
                                    </button>

                                    <button type="button" class="btn-toolbox-action btn-toolbox-action-accent" onclick="scrollMatrixTable('end')" title="Jump directly to Action & Outstanding columns">
                                        Action <i class="fa fa-angle-double-right ml-1"></i>
                                    </button>
                                </div>
                            </div>



                            <!-- Footer Note matching reference image -->

                            <div class="matrix-footer-note">

                                <i class="fa fa-info-circle text-info mr-1"></i>

                                Bordered cells are editable &bull; Use 'Collect' to collect fees & generate itemized receipts &bull; Net payable cannot be reduced below already collected payments.

                            </div>



                            <!-- Loading Indicator Overlay -->

                            <div class="matrix-loading-overlay" id="matrixLoadingOverlay" style="display: none;">

                                <div class="text-center">

                                    <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>

                                    <div class="font-weight-bold text-dark" style="font-size: 11.5px;">Updating fee ledger...</div>

                                </div>

                            </div>



                        </div>

                    </div>



                </div>



            </div>

        </div>

    </section>

</div>



<!-- ========================================================

     MODAL: SET / EDIT BATCH FIRST FEES (SEMESTER TUITION FEE ONLY)

     ======================================================== -->

<div class="modal fade" id="modalBatchFees" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="fa fa-graduation-cap"></i> Set Semester Tuition Fee (<span id="lblBatchFeesCourse">Course</span> &bull; Sem <span id="lblBatchFeesSem">1</span> &bull; Batch <span id="lblBatchFeesBatch">2026</span>)

                </h5>

                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">

                <!-- Locked Notice Alert -->

                <div class="alert alert-warning border mb-2 p-2" id="batchFeeLockedAlert" style="font-size: 11.5px; background: #fffbeb; border-color: #fde68a !important; color: #92400e; display: none;">

                    <i class="fa fa-lock fa-lg mr-2 text-warning"></i>

                    <strong>Tuition Fee is Locked:</strong> This semester fee has already been assigned to <strong id="lblBatchFeesAssignedCount" class="text-danger">0</strong> students. It is strictly non-editable and cannot be deleted.

                </div>



                <!-- Active Info Alert -->

                <div class="alert alert-light border mb-2 p-2" id="batchFeeInfoAlert" style="font-size: 11.5px; background: #f8fafc;">

                    <i class="fa fa-info-circle text-info mr-1"></i>

                    Configure standard semester Tuition Fee for this batch. Once assigned to students, it becomes non-editable and cannot be deleted.

                </div>



                <div class="table-responsive border mb-2" style="border-color: #cbd5e1 !important;">

                    <table class="table table-sm table-bordered mb-0" style="font-size: 11.5px;">

                        <thead>

                            <tr style="background: #d9eaf7 !important; color: #152536 !important; border-bottom: 2px solid #8fa7b8; font-size: 11px; font-weight: 700; text-transform: uppercase;">

                                <th style="width: 45px; text-align: center;">#</th>

                                <th>Fee Head Name</th>

                                <th class="text-right" style="width: 170px;">Boys Amount (₹)</th>

                                <th class="text-right" style="width: 170px;">Girls Amount (₹)</th>

                                <th class="text-center" style="width: 100px;">Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td class="text-center text-muted align-middle font-weight-bold">1</td>

                                <td class="align-middle">

                                    <div class="d-flex align-items-center">

                                        <strong class="text-dark" style="font-size: 12.5px;">Tuition Fee</strong>

                                        <span class="badge badge-success ml-2" style="font-size: 9.5px;">Semester Standard</span>

                                    </div>

                                    <small class="text-muted">Standard academic course tuition fee</small>

                                </td>

                                <td class="align-middle">

                                    <input type="number" step="0.01" min="0" id="batch_tuition_boys" class="toolbar-control text-right font-weight-bold" placeholder="0.00" style="font-size: 12px;">

                                </td>

                                <td class="align-middle">

                                    <input type="number" step="0.01" min="0" id="batch_tuition_girls" class="toolbar-control text-right font-weight-bold" placeholder="0.00" style="font-size: 12px;">

                                </td>

                                <td class="text-center align-middle" id="cellBatchTuitionStatus">

                                    <span class="badge badge-success font-weight-bold">Active</span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>



                <div class="p-2 border rounded mb-1" id="panelBatchAutoApply" style="background: #eef6f0; border-color: #a7d7b5 !important;">

                    <div class="custom-control custom-checkbox">

                        <input type="checkbox" class="custom-control-input" id="chkAutoApplyBatchFees" checked>

                        <label class="custom-control-label font-weight-bold text-success" for="chkAutoApplyBatchFees" style="font-size: 11.5px; cursor: pointer;">

                            <i class="fa fa-magic mr-1"></i> Auto-assign this Tuition Fee to all enrolled batch students in 1-Click

                        </label>

                        <small class="text-muted d-block ml-4" style="font-size: 10px;">

                            Enrolled students will receive this Tuition Fee upon saving. Once assigned, this fee is locked and cannot be changed or deleted.

                        </small>

                    </div>

                </div>



            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">

                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-search-action px-3" id="btnSaveBatchFeesConfig" onclick="submitBatchStandardFees()">

                    <i class="fa fa-check-circle mr-1"></i> Save &amp; Apply Tuition Fee

                </button>

            </div>

        </div>

    </div>

</div>



<!-- ========================================================

     MODAL: ADD CUSTOM HEAD TO STUDENT

     ======================================================== -->

<!-- ========================================================
     MODAL: COLLECT FEE (PIXEL-PERFECT FROM USER SPEC IMAGE)
     ======================================================== -->
<!-- ========================================================
     MODAL: COLLECT FEE (NEW ERP THEME LAYOUT & 100% PROFESSIONAL ENGLISH)
     ======================================================== -->
<!-- ========================================================
     MODAL: COLLECT FEE (ERP THEME ALIGNED & 100% PROFESSIONAL ENGLISH)
     ======================================================== -->
<!-- ========================================================
     MODAL: COLLECT FEE (ERP THEME ALIGNED, MULTI-MODE SPLIT & 100% PROFESSIONAL ENGLISH)
     ======================================================== -->

<!-- ========================================================
     MODAL: CONFIRM ACTION ALERT (CONFIRMATION MODAL ALERT)
     ======================================================== -->
<div class="modal fade" id="modalFeeActionConfirm" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
        <div class="modal-content" style="border: 1px solid #9aa7b1; border-radius: 5px; box-shadow: 0 15px 35px rgba(0,0,0,0.25); overflow: hidden;">
            <div class="modal-header d-flex align-items-center justify-content-between px-3 py-2" style="background: #f1f4f6; border-bottom: 1px solid #cbd5e1; min-height: 48px;">
                <h5 class="modal-title font-weight-bold mb-0" id="confirmModalTitle" style="font-size: 14px; color: #1e2d39; display: flex; align-items: center;">
                    <i class="fa fa-exclamation-triangle text-warning mr-2"></i> Confirm Action
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.75; font-size: 20px; line-height: 1; border: none; background: transparent;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-start" style="gap: 14px;">
                    <div id="confirmModalIconWrapper" style="width: 44px; height: 44px; min-width: 44px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; font-size: 20px;">
                        <i id="confirmModalIcon" class="fa fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div id="confirmModalMessage" style="font-size: 12.5px; color: #334155; line-height: 1.5;">
                            Are you sure you want to proceed?
                        </div>
                        <div id="confirmModalSubnote" class="text-muted mt-2" style="font-size: 11px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-3 py-2 d-flex justify-content-end" style="background: #f8fafc; border-top: 1px solid #e2e8f0; gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-dismiss="modal" data-bs-dismiss="modal" id="btnConfirmModalCancel">
                    No, Cancel
                </button>
                <button type="button" class="btn btn-sm btn-danger px-3 font-weight-bold" id="btnConfirmModalAction">
                    Yes, Proceed
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCollectFee" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 1060px;">
        <div class="modal-content" style="border: 1px solid #9aa7b1; border-radius: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.18); overflow: hidden;">
            
            <!-- Excel Ribbon Green Header: Vertically Centered Content -->
            <div class="modal-header d-flex align-items-center justify-content-between px-3 py-2" style="background: #217346; color: #ffffff; border-bottom: 2px solid #185c37; min-height: 52px;">
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <div id="collect_student_avatar" style="width: 36px; height: 36px; min-width: 36px; border-radius: 50%; background: #ffffff; color: #217346; font-weight: 800; font-size: 13.5px; display: flex; align-items: center; justify-content: center; text-transform: uppercase; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                        ST
                    </div>
                    <div class="d-flex flex-column justify-content-center" style="line-height: 1.25;">
                        <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15px; display: flex; align-items: center;">
                            <i class="fa fa-money mr-2" style="color: #217346;"></i> Collect / Modify Fee &mdash; <span id="collect_student_name" class="ml-1">Student Name</span>
                        </h5>
                        <div id="collect_student_sub" style="font-size: 11.5px; color: #475569; font-weight: 600; margin-top: 2px;">
                            BC26-035 &bull; B.COM &bull; Batch 2026 &bull; Semester 1
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <span id="collect_status_badge" class="badge badge-light px-2 py-1 font-weight-bold" style="font-size: 11px;">
                        Unpaid
                    </span>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; text-shadow: none; font-size: 22px; line-height: 1; padding: 0 4px; margin: 0;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body p-3" style="background: #f4f6f9;">
                <!-- Full-width English Instruction Callout Banner -->
                
                <!-- Active Receipt Modification Bar -->
                <div id="receipt_edit_mode_bar" class="alert alert-warning py-2 px-3 mb-3 align-items-center justify-content-between">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <i class="fa fa-pencil-square-o fa-lg text-warning"></i>
                        <div>
                            <span class="font-weight-bold" style="font-size: 12.5px;">Modifying Existing Receipt:</span>
                            <span id="lblEditReceiptNo" class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 11px;">RCPT-...</span>
                            <span class="mx-2">&bull;</span> Original Amount: <strong id="lblEditReceiptOrigAmt">₹0</strong>
                            <div style="font-size: 11px; color: #78350f; margin-top: 2px;">
                                Adjust fee head allocations or payment details below. Outstanding dues will be automatically recalculated.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold px-2 py-1" onclick="exitReceiptEditMode()" title="Discard modifications and exit edit mode">
                        <i class="fa fa-times mr-1"></i> Exit Edit Mode
                    </button>
                </div>

                <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" id="collect_modal_mode_banner" style="font-size: 12px; border-radius: 3px; background: #e7eff5; border: 1px solid #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-2 fa-lg text-primary" id="collect_modal_mode_banner_icon"></i>
                    <div id="collect_modal_mode_banner_text">
                        <strong>Partial / Itemized Payment Mode:</strong>
                        Click any fee head's collect box to select it. Enter the partial or full amount being collected today. Any uncollected amount remains due for subsequent collections.
                        <a href="javascript:void(0)" onclick="promptSwitchCollectionMode()" class="text-primary font-weight-bold ml-1" style="text-decoration: underline;">Switch to Total Dues</a>
                    </div>
                </div>

                <!-- 2-Column Split Body -->
                <div class="row">
                    <!-- Left Section: Fee Heads to Collect & Payment Mode -->
                    <div class="col-lg-7 col-md-7 pr-lg-2">
                        <div class="card mb-0" style="border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: none; background: #ffffff;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                        <i class="fa fa-list-ul text-muted mr-1"></i> Fee Heads to Collect
                                    </span>
                                    <!-- Quick Tool Buttons Styled with ERP Theme -->
                                    <div class="d-flex align-items-center" style="gap: 5px;">
                                        <span class="badge badge-success px-2 py-1" id="badgeTotalModeActivePill" style="display: none; font-size: 11px;">
                                            <i class="fa fa-calculator mr-1"></i> Total Dues Mode Active
                                        </span>
                                        <div class="d-flex align-items-center" id="collect_quick_tools_wrapper" style="gap: 5px;">
                                            <button type="button" class="btn-collect-tool" onclick="collectSelectAllDue(true)" title="Select and prefill all heads with remaining dues">
                                            <i class="fa fa-check-square-o text-success mr-1"></i> Select All Due
                                        </button>
                                        <button type="button" class="btn-collect-tool" onclick="collectSelectTuitionOnly()" title="Quick select only Tuition Fee">
                                            <i class="fa fa-graduation-cap text-primary mr-1"></i> Tuition Only
                                        </button>
                                        <button type="button" class="btn-collect-tool" onclick="collectSelectAllDue(false)" title="Clear all selections">
                                            <i class="fa fa-times text-danger mr-1"></i> Clear All
                                        </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Table with Excel Theme Styling, Crisp Padding & Vertical Centering -->
                                <div class="table-responsive" style="border: 1px solid #b8c9d9; border-radius: 2px;">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                        <thead style="background: #e7eff5; color: #1e3a5f; font-weight: 700; border-bottom: 2px solid #b8c9d9;">
                                            <tr>
                                                <th class="col-head-cb" style="width: 38px; text-align: center; vertical-align: middle; padding: 6px 4px;">&#10003;</th>
                                                <th style="vertical-align: middle; padding: 6px 10px;">Fee Head</th>
                                                <th class="text-right" style="vertical-align: middle; padding: 6px 10px;">Net Payable</th>
                                                <th class="text-right" style="vertical-align: middle; padding: 6px 10px;">Paid Earlier</th>
                                                <th class="text-right" style="vertical-align: middle; padding: 6px 10px;">Due Amount</th>
                                                <th class="col-head-collect text-right" style="width: 135px; vertical-align: middle; padding: 6px 10px;">Collect Now</th>
                                                <th style="width: 42px; text-align: center; vertical-align: middle; padding: 6px 4px;" title="Delete this fee head from student">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyCollectHeads">
                                            <!-- Dynamic rows loaded via AJAX -->
                                        </tbody>
                                        <tfoot style="background: #f1f5f9; font-weight: 800; border-top: 2px solid #b8c9d9;">
                                            <tr>
                                                <td colspan="2" id="collect_tbl_foot_label" class="align-middle" style="padding: 7px 10px;">Total</td>
                                                <td class="text-right align-middle" id="collect_tbl_total_net" style="padding: 7px 10px;">₹0</td>
                                                <td class="text-right align-middle text-muted" id="collect_tbl_total_paid" style="padding: 7px 10px;">₹0</td>
                                                <td class="text-right align-middle text-danger font-weight-bold" id="collect_tbl_total_due" style="padding: 7px 10px;">₹0</td>
                                                <td class="col-head-collect text-right align-middle text-success font-weight-bold" id="collect_tbl_total_now" style="font-size: 13.5px; padding: 7px 10px;">₹0</td>
                                                <td style="padding: 7px 4px;"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Payment Details Box: Single or Multi-Mode Split -->
                                <div id="collect_payment_mode_container" class="mt-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="side-label font-weight-bold text-muted mb-0" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <i class="fa fa-credit-card mr-1 text-primary"></i> Payment Mode &amp; Details
                                        </label>
                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                            <button type="button" class="btn-collect-tool" id="btnToggleSplitMode" onclick="toggleSplitPaymentMode()" title="Toggle between single payment mode and multi-mode split (e.g. UPI + Cash)">
                                                <i class="fa fa-sliders text-primary mr-1"></i> <span id="lblToggleSplitText">+ Split Mode (Multi)</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Mode A: Single Payment Mode (Default) -->
                                    <div id="collect_single_mode_panel">
                                        <div class="collect-mode-group mb-3" id="collect_mode_buttons">
                                            <button type="button" class="collect-mode-btn" onclick="setCollectPaymentMode(this, 'Cash')">Cash</button>
                                            <button type="button" class="collect-mode-btn active" onclick="setCollectPaymentMode(this, 'UPI')">UPI</button>
                                            <button type="button" class="collect-mode-btn" onclick="setCollectPaymentMode(this, 'Card')">Card</button>
                                            <button type="button" class="collect-mode-btn" onclick="setCollectPaymentMode(this, 'Cheque')">Cheque</button>
                                            <button type="button" class="collect-mode-btn" onclick="setCollectPaymentMode(this, 'Bank Transfer')">Bank Transfer</button>
                                        </div>
                                        <input type="hidden" id="input_collect_mode" value="UPI">

                                        <div class="row">
                                            <div class="col-md-7">
                                                <label class="side-label font-weight-bold text-muted mb-1 d-block" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Reference / Transaction No.</label>
                                                <input type="text" id="input_collect_ref" class="toolbar-control form-control form-control-sm font-weight-bold" placeholder="e.g. UPI-5521780934 / Cheque No">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="side-label font-weight-bold text-muted mb-1 d-block" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Collection Date</label>
                                                <input type="date" id="input_collect_date" class="toolbar-control form-control form-control-sm font-weight-bold" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mode B: Multi-Mode Split Payment (e.g. ₹400 UPI + ₹200 Cash) -->
                                    <div id="collect_split_mode_panel" style="display: none;">
                                        <div class="p-2 mb-2 rounded" style="background: #e0f2fe; border: 1px solid #bae6fd; font-size: 11px; color: #0369a1;">
                                            <i class="fa fa-info-circle mr-1"></i>
                                            <strong>Split Payment Mode:</strong> Allocate the total collecting amount across multiple modes. The sum of split amounts must match the total collecting amount.
                                        </div>

                                        <div id="split_mode_rows_container">
                                            <!-- Dynamically added split rows -->
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2" style="border-top: 1px dashed #cbd5e1;">
                                            <button type="button" class="btn-collect-tool" onclick="addSplitPaymentRow()" title="Add another payment mode split">
                                                <i class="fa fa-plus text-success mr-1"></i> Add Another Mode
                                            </button>
                                            <div id="split_summary_status" class="font-weight-bold" style="font-size: 11.5px;">
                                                Allocated: <span id="lblSplitAllocated" class="text-success">₹0</span> / <span id="lblSplitTarget">₹0</span>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-7">
                                                <label class="side-label font-weight-bold text-muted mb-1 d-block" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Common Remarks (Optional)</label>
                                                <input type="text" id="input_collect_split_remark" class="toolbar-control form-control form-control-sm" placeholder="Split payment notes / Cashier remark">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="side-label font-weight-bold text-muted mb-1 d-block" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px;">Collection Date</label>
                                                <input type="date" id="input_collect_split_date" class="toolbar-control form-control form-control-sm font-weight-bold" value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Payment History & Collection Summary -->
                    <div class="col-lg-5 col-md-5 pl-lg-2 mt-3 mt-md-0">
                        <div class="card mb-0" style="border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: none; background: #ffffff; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                            <i class="fa fa-history text-muted mr-1"></i> Payment History
                                        </span>
                                        <span class="badge badge-light border text-muted font-weight-bold" id="lblCollectHistoryCount" style="font-size: 11px;">0 receipts</span>
                                    </div>
                                    <div class="text-muted mb-2" style="font-size: 11px;">Receipts recorded for this semester</div>

                                    <!-- Compact History Cards Container (Fixed Height with Overflow) -->
                                    <div id="collect_receipts_history_container" style="max-height: 180px; overflow-y: hidden; padding-right: 2px; transition: max-height 0.25s ease;">
                                        <!-- Dynamically loaded past receipts -->
                                    </div>

                                    <!-- Expand / Collapse Button (Visible ONLY when receipts overflow fixed height) -->
                                    <div id="collect_history_expand_wrapper" class="text-center mt-1" style="display: none;">
                                        <button type="button" class="btn btn-xs btn-link font-weight-bold text-primary p-0" id="btnToggleHistoryExpand" onclick="toggleHistoryExpand()" style="font-size: 11px; text-decoration: none;">
                                            <i class="fa fa-angle-down mr-1"></i> View All Receipts
                                        </button>
                                    </div>
                                </div>

                                <!-- Summary & Action Buttons Box (Hidden when Semester is Progressed) -->
                                <div id="collect_action_summary_box" class="mt-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                                    
                                    <!-- Total Dues Mode Dedicated Amount Input -->
                                    <div id="collect_total_mode_input_wrapper" class="mb-3 p-2 rounded" style="background: #f0fdf4; border: 1.5px solid #86efac; display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="font-weight-bold text-success mb-0" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                                <i class="fa fa-calculator mr-1"></i> Amount To Collect
                                            </label>
                                            <button type="button" class="btn btn-xs btn-link p-0 font-weight-bold text-success" onclick="resetTotalModeToFullDue()" style="font-size: 10.5px;" title="Reset to full outstanding due amount">
                                                Full Due
                                            </button>
                                        </div>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text font-weight-bold" style="background: #ffffff; color: #166534;">₹</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" id="input_collect_total_mode_amt" class="form-control form-control-sm font-weight-bold text-right" style="font-size: 15px; color: #166534;" placeholder="0.00" oninput="onTotalModeCollectAmtChange(this.value)">
                                        </div>
                                        <div class="text-muted d-block mt-1" style="font-size: 10px;">
                                            <i class="fa fa-info-circle mr-1 text-success"></i> Sum of all heads. Auto-allocated upon generating receipt.
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                        <span class="text-muted">Total Dues:</span>
                                        <strong class="text-dark" id="lblCollectSummaryTotalDue">₹0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 13px;">
                                        <span class="font-weight-bold text-dark">Collecting Now:</span>
                                        <span class="font-weight-bold text-success" style="font-size: 16px;" id="lblCollectSummaryNow">₹0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2" style="font-size: 12px;">
                                        <span class="text-muted">Balance After Collection:</span>
                                        <strong class="text-danger" id="lblCollectSummaryBalance">₹0</strong>
                                    </div>
                                    <div class="text-muted mb-3" style="font-size: 11px;">
                                        Sequential Receipt: <strong class="text-dark" id="lblCollectSummaryReceiptNo">RCPT-...</strong>
                                    </div>

                                    <!-- Theme Aligned Bottom Action Buttons -->
                                    <div class="d-flex" style="gap: 8px;">
                                        <button type="button" class="btn-collect-cancel" data-dismiss="modal" data-bs-dismiss="modal" style="width: 32%;">
                                            Cancel
                                        </button>
                                        <button type="button" class="btn-collect-submit" id="btnSubmitCollectPayment" onclick="submitCollectPayment()" style="width: 68%;">
                                            <i class="fa fa-check mr-1"></i> Collect <span id="btnCollectLabelAmt" class="mx-1">₹0</span> &amp; Receipt
                                        </button>
                                    </div>
                                </div>

                                <!-- Progressed / Closed Semester Info Box (Archived Ledger Summary - Image 3 Layout) -->
                                <div id="collect_progressed_info_box" class="mt-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; display: none;">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2" style="border-bottom: 1px solid #cbd5e1;">
                                        <span class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                            <i class="fa fa-folder-open-o text-primary mr-1"></i> Archived Ledger Summary
                                        </span>
                                        <span class="badge badge-info px-2 py-1 font-weight-bold" id="lblProgressedTargetBadge" style="font-size: 10.5px;">
                                            Progressed
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                        <span class="text-muted">Total Semester Fee:</span>
                                        <strong class="text-dark" id="lblProgressedSumNet">₹0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                                        <span class="text-muted">Total Paid in This Sem:</span>
                                        <strong class="text-success" id="lblProgressedSumPaid">₹0</strong>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded mt-2" id="progressed_transfer_dues_row" style="font-size: 12px; background: #e0f2fe; border: 1px solid #bae6fd;">
                                        <span class="font-weight-bold text-dark" id="lblProgressedTransferLabel" style="font-size: 11.5px;">Transferred Dues:</span>
                                        <strong class="font-weight-bold" id="lblProgressedTransferAmt" style="font-size: 13.5px;">₹0</strong>
                                    </div>

                                    <div class="p-2 mb-3 rounded" style="background: #f1f5f9; border: 1px dashed #cbd5e1; font-size: 11px; color: #475569; line-height: 1.45;">
                                        <i class="fa fa-lock text-secondary mr-1"></i>
                                        <strong>Fee Collection Locked:</strong> This past semester is closed. Outstanding balances or advance credits have rolled over into the active semester.
                                    </div>

                                    <button type="button" class="btn btn-secondary btn-block font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" style="height: 36px; font-size: 12.5px;">
                                        <i class="fa fa-times mr-1"></i> Close Ledger
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Excel/ERP Themed Collection Mode Switch Confirmation Modal (Matches promote.blade.php) -->
<div class="modal fade" id="modalConfirmCollectionMode" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;" role="document">
        <div class="modal-content" style="border: 1px solid #9eacb7; border-radius: 4px; box-shadow: 0 8px 24px rgba(0,0,0,0.22); overflow: hidden;">
            <div class="modal-header d-flex align-items-center justify-content-between" style="padding: 10px 14px; background: #f1f4f6; border-bottom: 1px solid #aebbc5;">
                <h5 class="modal-title font-weight-bold mb-0" id="colModeModalTitle" style="font-size: 14px; color: #1e2d39;">
                    <i class="fa fa-sliders text-success mr-2"></i> Switch Fee Collection Mode
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="font-size: 20px; line-height: 1; border: none; background: transparent; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" style="background: #ffffff;">
                <div class="d-flex align-items-start mb-3">
                    <div id="colModeModalIconWrapper" class="mr-3" style="width: 42px; height: 42px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i id="colModeModalIcon" class="fa fa-calculator text-success" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" id="colModeModalHeading" style="font-size: 14px; line-height: 1.3;">Enable Total Dues Collection Mode?</strong>
                        <span class="text-muted" id="colModeModalSubheading" style="font-size: 11.5px;">This will change how fee collection operates in the cashier modal.</span>
                    </div>
                </div>

                <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Current Active Mode:</span>
                        <strong class="text-dark" id="colModeModalCurrent">Itemized (Head-wise)</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Switching To:</span>
                        <strong class="text-success font-weight-bold" id="colModeModalTarget">Total Dues (Consolidated)</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Collect Now Fields:</span>
                        <strong class="text-primary font-weight-bold" id="colModeModalFieldBehavior">Hidden (Automatic Total)</strong>
                    </div>
                </div>

                <div class="alert py-2 px-3 mb-0" id="colModeModalInfoAlert" style="font-size: 11.5px; border-radius: 3px; background: #e7eff5; border: 1px solid #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-1" id="colModeModalAlertIcon"></i>
                    <span id="colModeModalNoteText">In <strong>Total Dues Mode</strong>, individual head input boxes are hidden. Only the overall outstanding balance across all heads will be collected.</span>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end" style="padding: 9px 14px; background: #f1f4f6; border-top: 1px solid #cbd5e1; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-search-action px-3" id="btnConfirmSwitchColMode" onclick="applyCollectionModeSwitch()">
                    <i class="fa fa-check mr-1"></i> Confirm &amp; Switch Mode
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalQuickAssignFees" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="fa fa-magic"></i> Quick Assign Fees (<span id="lblQuickAssignSelectedCount">0 Students Selected</span>)

                </h5>

                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">

                <!-- Info Banner -->

                <div class="alert alert-light border mb-2 p-2 d-flex justify-content-between align-items-center" style="font-size: 11.5px; background: #f8fafc;">

                    <div>

                        <i class="fa fa-info-circle text-info mr-1"></i>

                        Assigning fee heads to <strong class="text-success" id="qaModalSelectedCount">0 Students</strong> in 

                        <strong class="text-dark" id="qaModalCourseLabel">Course</strong> &bull; Batch <strong class="text-dark" id="qaModalBatchLabel">2026</strong> &bull; Sem <strong class="text-dark" id="qaModalSemLabel">1</strong>

                    </div>

                    <div>

                        <button type="button" class="btn btn-xs btn-outline-success font-weight-bold" onclick="toggleQuickCreateHeadPanel()">

                            <i class="fa fa-plus mr-1"></i> + Create New Common Fee Head

                        </button>

                    </div>

                </div>



                <!-- Inline Create New Common Fee Head Form -->

                <div id="panelQuickCreateHead" class="p-2 border rounded mb-2" style="background: #eef6f0; border-color: #a7d7b5 !important; display: none;">

                    <div class="d-flex justify-content-between align-items-center mb-1">

                        <strong style="font-size: 11.5px; color: #166534;"><i class="fa fa-plus-circle mr-1"></i> Create New Common Fee Head</strong>

                        <button type="button" class="close" onclick="toggleQuickCreateHeadPanel()" style="font-size: 16px;">&times;</button>

                    </div>

                    <div class="form-row align-items-end">

                        <div class="col-md-4 form-group mb-1">

                            <label class="side-label">Fee Head Name <span class="text-danger">*</span></label>

                            <input type="text" id="qa_new_head_name" class="toolbar-control font-weight-bold" placeholder="e.g. Lab Kit Fee, Bus Fee">

                        </div>

                        <div class="col-md-3 form-group mb-1">

                            <label class="side-label">Fee Type</label>

                            <select id="qa_new_fee_type" class="toolbar-control">

                                <option value="Tuition">Tuition</option>

                                <option value="Examination">Examination</option>

                                <option value="Admission &amp; Registration">Admission &amp; Registration</option>

                                <option value="Laboratory">Laboratory</option>

                                <option value="Library">Library</option>

                                <option value="Hostel &amp; Mess">Hostel &amp; Mess</option>

                                <option value="Transport">Transport</option>

                                <option value="Sports &amp; Cultural">Sports &amp; Cultural</option>

                                <option value="Development Charges">Development Charges</option>

                                <option value="Caution Money">Caution Money</option>

                                <option value="Optional">Optional</option>

                                <option value="Miscellaneous">Miscellaneous</option>

                            </select>

                        </div>

                        <div class="col-md-2 form-group mb-1">

                            <label class="side-label">Boys (₹)</label>

                            <input type="number" step="0.01" min="0" id="qa_new_boys_amt" class="toolbar-control text-right font-weight-bold" placeholder="0.00">

                        </div>

                        <div class="col-md-2 form-group mb-1">

                            <label class="side-label">Girls (₹)</label>

                            <input type="number" step="0.01" min="0" id="qa_new_girls_amt" class="toolbar-control text-right font-weight-bold" placeholder="0.00">

                        </div>

                        <div class="col-md-1 form-group mb-1">

                            <button type="button" id="btnSubmitQuickCreateHead" class="btn btn-search-action w-100" onclick="submitQuickCreateHead()" title="Save head and add to list">

                                <i class="fa fa-plus"></i>

                            </button>

                        </div>

                    </div>

                </div>



                <!-- Section 1: Quick Common Fee Head Buttons -->

                <div class="mb-3">

                    <div class="qa-section-header">

                        <span><i class="fa fa-th-large text-success mr-1"></i> Available Common Fee Heads (Click to Add / Remove)</span>

                        <span class="text-muted" style="font-size: 10px; font-weight: normal; text-transform: none;">Click any button to toggle selection</span>

                    </div>

                    <div id="qaQuickButtonsContainer" class="qa-chips-container">

                        <!-- Buttons dynamically populated -->

                    </div>

                </div>



                <!-- Section 2: Selected Fee Heads List Table -->

                <div class="mb-2">

                    <div class="qa-section-header">

                        <span>

                            <i class="fa fa-th-list text-primary mr-1"></i> Selected Fee Heads for Assignment

                            <span class="badge badge-success ml-1 font-weight-bold" id="qaSelectedHeadsCountBadge">0</span>

                        </span>

                        <div>

                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="clearAllSelectedHeads()" style="font-size: 10px; padding: 1px 7px;">

                                <i class="fa fa-trash-o mr-1"></i> Clear All

                            </button>

                        </div>

                    </div>



                    <div class="table-responsive border" style="max-height: 250px; overflow-y: auto; border-color: #cbd5e1 !important;">

                        <table class="table table-sm table-bordered table-hover mb-0 qa-selected-table">

                            <thead>

                                <tr>

                                    <th style="width: 38px; text-align: center;">#</th>

                                    <th>Fee Head Name</th>

                                    <th style="width: 140px;">Fee Type</th>

                                    <th class="text-right" style="width: 135px;">Boys Amount (₹)</th>

                                    <th class="text-right" style="width: 135px;">Girls Amount (₹)</th>

                                    <th style="width: 45px; text-align: center;">Remove</th>

                                </tr>

                            </thead>

                            <tbody id="qaSelectedHeadsTbody">

                                <!-- Populated dynamically -->

                            </tbody>

                        </table>

                    </div>

                </div>



                <!-- Assignment Preference (Radio Options) -->

                <div class="qa-mode-box mb-2 p-2 border rounded" style="background: #f8fafc; border-color: #cbd5e1 !important;">

                    <div class="d-flex align-items-center justify-content-between mb-1">

                        <span style="font-size: 11.5px; font-weight: 700; color: #1e293b;">

                            <i class="fa fa-sliders text-primary mr-1"></i> Assignment Preference:

                        </span>

                        <span class="text-muted" style="font-size: 10.5px;">For students who already have these fee heads</span>

                    </div>

                    <div class="d-flex align-items-center flex-wrap p-2 rounded" style="gap: 20px; font-size: 12px; background: #ffffff; border: 1px solid #e2e8f0;">

                        <div class="custom-control custom-radio">

                            <input type="radio" id="qaModeUnassigned" name="qa_assign_mode" class="custom-control-input" value="unassigned" checked>

                            <label class="custom-control-label font-weight-bold text-dark cursor-pointer mb-0" for="qaModeUnassigned" style="cursor: pointer;">

                                <i class="fa fa-user-plus text-primary mr-1"></i> Only assign to unassigned students

                                <small class="text-muted d-block font-weight-normal" style="font-size: 10.5px; line-height: 1.2;">(Default: Skip students who already have this fee head)</small>

                            </label>

                        </div>

                        <div class="custom-control custom-radio border-left pl-4" style="border-color: #e2e8f0 !important;">

                            <input type="radio" id="qaModeOverwrite" name="qa_assign_mode" class="custom-control-input" value="overwrite">

                            <label class="custom-control-label font-weight-bold text-dark cursor-pointer mb-0" for="qaModeOverwrite" style="cursor: pointer;">

                                <i class="fa fa-refresh text-warning mr-1"></i> Overwrite already assigned students

                                <small class="text-muted d-block font-weight-normal" style="font-size: 10.5px; line-height: 1.2;">(Update existing fee head amount to entered value)</small>

                            </label>

                        </div>

                    </div>

                </div>



                <!-- Summary Bar -->

                <div class="qa-summary-bar mb-1">

                    <div class="text-muted" style="font-size: 11px;">

                        <i class="fa fa-info-circle mr-1 text-success"></i>

                        Assigning to <strong class="text-dark" id="qaSelectedStudentsCountBottom">0</strong> selected student(s).

                    </div>

                    <div class="d-flex align-items-center" style="gap: 15px; font-size: 11.5px;">

                        <div>

                            <span class="text-muted">Boys Total:</span>

                            <strong class="text-success ml-1" id="qaSummaryBoysTotal">₹0.00</strong>

                        </div>

                        <div class="border-left pl-3" style="border-color: #cbd5e1 !important;">

                            <span class="text-muted">Girls Total:</span>

                            <strong class="text-success ml-1" id="qaSummaryGirlsTotal">₹0.00</strong>

                        </div>

                    </div>

                </div>



            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">

                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-search-action px-3" id="btnSubmitQuickAssignFees" onclick="submitQuickAssignFees()">

                    <i class="fa fa-check-circle mr-1"></i> Assign to Selected Students

                </button>

            </div>

        </div>

    </div>

</div>



<!-- ========================================================

     MODAL: CONFIRM QUICK ASSIGN FEES (Matches promote.blade.php)

     ======================================================== -->

<div class="modal fade" id="modalConfirmQuickAssign" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;" role="document">

        <div class="modal-content" style="border: 1px solid #9eacb7; border-radius: 4px; box-shadow: 0 8px 24px rgba(0,0,0,0.22); overflow: hidden;">

            <div class="modal-header d-flex align-items-center justify-content-between" style="padding: 10px 14px; background: #f1f4f6; border-bottom: 1px solid #aebbc5;">

                <h5 class="modal-title font-weight-bold mb-0" style="font-size: 14px; color: #1e2d39;">

                    <i class="fa fa-magic text-success mr-2"></i> Confirm Fee Head Assignment

                </h5>

                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="font-size: 20px; line-height: 1; border: none; background: transparent; opacity: 0.8;">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body p-3" style="background: #ffffff;">

                <div class="d-flex align-items-start mb-3">

                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">

                        <i class="fa fa-clipboard-check text-success" style="font-size: 20px;"></i>

                    </div>

                    <div>

                        <strong class="d-block text-dark" style="font-size: 14px; line-height: 1.3;">Assign Fee Heads to Selected Students?</strong>

                        <span class="text-muted" style="font-size: 11.5px;">Please verify assignment parameters before execution:</span>

                    </div>

                </div>



                <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">

                    <div class="d-flex justify-content-between py-1 border-bottom">

                        <span class="text-muted">Target Course &amp; Sem:</span>

                        <strong class="text-dark" id="qaConfirmCourseBatchSem">-</strong>

                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom">

                        <span class="text-muted">Selected Students:</span>

                        <strong class="text-success font-weight-bold" id="qaConfirmStudentCount">0 Students</strong>

                    </div>

                    <div class="d-flex justify-content-between py-1 border-bottom">

                        <span class="text-muted">Assignment Mode:</span>

                        <span id="qaConfirmModeBadge"><span class="badge badge-secondary font-weight-bold">Only Unassigned</span></span>

                    </div>

                    <div class="pt-2">

                        <span class="text-muted d-block mb-1 font-weight-bold">Fee Head(s) to Assign:</span>

                        <div id="qaConfirmHeadsList" class="p-2 border rounded" style="background: #ffffff; max-height: 110px; overflow-y: auto;">

                            <!-- Populated dynamically -->

                        </div>

                    </div>

                </div>



                <div class="alert py-2 px-3 mb-0" id="qaConfirmInfoAlert" style="font-size: 11.5px; border-radius: 3px; background: #e7eff5; border: 1px solid #b8c9d9; color: #1e3a5f;">

                    <i class="fa fa-info-circle mr-1" id="qaConfirmAlertIcon"></i>

                    <span id="qaConfirmAlertText">Only students without this fee head will be assigned. Existing assignments remain untouched.</span>

                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end" style="padding: 9px 14px; background: #f1f4f6; border-top: 1px solid #cbd5e1; gap: 8px;">

                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal" id="btnCancelExecuteQuickAssign">Cancel</button>

                <button type="button" class="btn btn-search-action px-3" id="btnConfirmExecuteQuickAssign" onclick="executeQuickAssignFees()">

                    <i class="fa fa-check mr-1"></i> Confirm &amp; Assign Fees

                </button>

            </div>

        </div>

    </div>

</div>



<!-- ========================================================

     MODAL: EDIT DISCOUNT REASON (INDIVIDUAL)

     ======================================================== -->

<div class="modal fade" id="modalEditReason" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 380px;">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="fa fa-tag text-warning mr-1"></i> Course-Wide Discount Note

                </h5>

                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">

                <input type="hidden" id="reason_student_id">

                <div class="text-muted mb-2" style="font-size: 11px;">This note will be common across all semesters for this course.</div>
                <label class="side-label">Reason Tag / Note:</label>

                <input type="text" id="input_reason_text" class="toolbar-control mb-2 font-weight-bold" placeholder="e.g. Merit, Sibling">

                <div class="d-flex flex-wrap mb-2" style="gap: 2px;">

                    <span class="preset-badge-pill" onclick="$('#input_reason_text').val('Merit')">Merit</span>

                    <span class="preset-badge-pill" onclick="$('#input_reason_text').val('Sibling')">Sibling</span>

                    <span class="preset-badge-pill" onclick="$('#input_reason_text').val('Scholarship')">Scholarship</span>

                    <span class="preset-badge-pill" onclick="$('#input_reason_text').val('Staff ward')">Staff ward</span>

                    <span class="preset-badge-pill" onclick="$('#input_reason_text').val('10% concession')">10% concession</span>

                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end" style="gap: 6px;">

                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-search-action px-3" onclick="saveDiscountReasonTag()">

                    <i class="fa fa-check mr-1"></i> Save Tag

                </button>

            </div>

        </div>

    </div>

</div>



@include('college_fees.partials.common_head_modal')



<script>

// ========================================================

// STUDENT FEE LEDGER JAVASCRIPT CONTROLLER (100% REAL-TIME)

// ========================================================

function showCommonToast(type, msg) {

    if (typeof toastr !== 'undefined' && toastr[type]) {

        toastr[type](msg);

    } else {

        alert(msg);

    }

}



var currentCourseId = {{ $courseId ?? 1 }};

var currentBatch = "{{ $batch ?? date('Y') }}";

var currentSemester = {{ $semesterNo ?? 1 }};

var currentStatusFilter = 'all';

var currentGenderFilter = 'all';

var currentSearchQuery = '';

var autoOpenStudentId = {{ !empty($autoOpenStudentId) ? (int)$autoOpenStudentId : 'null' }};
if (!autoOpenStudentId) {
    try {
        var _urlParams = new URLSearchParams(window.location.search);
        if (_urlParams.has('student_id') && _urlParams.get('student_id')) {
            autoOpenStudentId = parseInt(_urlParams.get('student_id'));
        }
    } catch(e) {}
}

var ledgerDataCache = null;

var debounceTimer = null;



// ========================================================
// REAL-TIME BROWSER URL SYNC (COURSE, BATCH, SEMESTER, STUDENT)
// ========================================================
function updateBrowserURL(paramsToUpdate) {
    if (!window.history || !window.history.replaceState) return;

    try {
        var currentUrl = new URL(window.location.href);
        var searchParams = currentUrl.searchParams;

        // Sync course_id
        if (typeof currentCourseId !== 'undefined' && currentCourseId) {
            searchParams.set('course_id', currentCourseId);
        }

        // Sync batch
        if (typeof currentBatch !== 'undefined' && currentBatch) {
            searchParams.set('batch', currentBatch);
        }

        // Sync semester_no
        if (typeof currentSemester !== 'undefined' && currentSemester && currentSemester !== 'auto') {
            searchParams.set('semester_no', currentSemester);
        }

        // Update custom params (e.g. student_id)
        if (paramsToUpdate) {
            for (var key in paramsToUpdate) {
                if (paramsToUpdate.hasOwnProperty(key)) {
                    var val = paramsToUpdate[key];
                    if (val === null || val === undefined || val === '') {
                        searchParams.delete(key);
                    } else {
                        searchParams.set(key, val);
                    }
                }
            }
        }

        var newRelativePathQuery = currentUrl.pathname + '?' + searchParams.toString();
        window.history.replaceState({ path: newRelativePathQuery }, '', newRelativePathQuery);
    } catch(e) {
        console.error('Error updating browser URL:', e);
    }
}

// On Page Load

$(document).ready(function() {

    // Initial URL sync
    updateBrowserURL();

    // Listen to modal close event to remove student_id from URL in real time
    $('#modalCollectFee').on('hidden.bs.modal', function() {
        activeCollectStudentId = null;
        updateBrowserURL({ student_id: null });
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        try {
            var params = new URLSearchParams(window.location.search);
            var pCourse = params.get('course_id');
            var pBatch = params.get('batch');
            var pSem = params.get('semester_no');
            var pStudent = params.get('student_id');

            var reloadMatrix = false;
            if (pCourse && pCourse != currentCourseId) {
                currentCourseId = pCourse;
                $('#selLedgerCourse').val(pCourse);
                reloadMatrix = true;
            }
            if (pBatch && pBatch != currentBatch) {
                currentBatch = pBatch;
                $('#selLedgerBatch').val(pBatch);
                reloadMatrix = true;
            }
            if (pSem && pSem != currentSemester) {
                currentSemester = parseInt(pSem);
                reloadMatrix = true;
            }

            if (reloadMatrix) {
                if (pStudent) autoOpenStudentId = parseInt(pStudent);
                loadLedgerMatrix();
            } else {
                if (pStudent) {
                    if (activeCollectStudentId != pStudent || !$('#modalCollectFee').hasClass('show')) {
                        openCollectFeeModal(parseInt(pStudent));
                    }
                } else {
                    if ($('#modalCollectFee').hasClass('show')) {
                        $('#modalCollectFee').modal('hide');
                    }
                }
            }
        } catch(e) {}
    });

    updateCollectionModeHeaderUI();
    loadLedgerMatrix();
    initMatrixScrollHandlers();

    // Safety fallback (only if matrix load completely failed after 8s)
    if (autoOpenStudentId) {
        setTimeout(function() {
            if (autoOpenStudentId && !$('#modalCollectFee').hasClass('show') && !$('#modalCollectFee').is(':visible')) {
                var fallbackId = autoOpenStudentId;
                autoOpenStudentId = null;
                openCollectFeeModal(fallbackId);
            }
        }, 8000);
    }

});

// ========================================================
// AUTO HEIGHT & FOOTER TOTALS CALCULATION FOR MATRIX TABLE
// ========================================================
function adjustMatrixTableHeight() {
    var tableWrap = document.getElementById('matrixTableResponsive');
    if (!tableWrap) return;

    var rect = tableWrap.getBoundingClientRect();
    // 45px margin for bottom spacing and footer note
    var bottomMargin = 45;
    var available = window.innerHeight - rect.top - bottomMargin;
    var finalHeight = Math.max(380, Math.floor(available));
    tableWrap.style.height = finalHeight + 'px';
    tableWrap.style.maxHeight = finalHeight + 'px';
}

function recalculateMatrixTableFoot() {
    var data = ledgerDataCache;
    if (!data || !data.rows || data.rows.length === 0) {
        $('#matrixTableFoot').empty();
        return;
    }

    var stdHeads = data.standard_heads || [];
    var students = data.rows || [];

    var sumHeadTotals = {};
    stdHeads.forEach(function(h) { sumHeadTotals[h.head_name] = 0; });
    var sumGross = 0;
    var sumDiscount = 0;
    var sumNet = 0;
    var sumPaid = 0;
    var sumDue = 0;

    students.forEach(function(stu) {
        stdHeads.forEach(function(h) {
            var headVal = stu.head_values ? stu.head_values[h.head_name] : null;
            if (headVal && headVal.assigned) {
                sumHeadTotals[h.head_name] += (parseFloat(headVal.amount) || 0);
            }
        });
        sumGross += (parseFloat(stu.gross) || 0);
        sumDiscount += (parseFloat(stu.discount) || 0);
        sumNet += (parseFloat(stu.net) || 0);
        sumPaid += (parseFloat(stu.paid) || 0);
        if (!stu.is_progressed) {
            var dueVal = Math.round(((parseFloat(stu.net) || 0) - (parseFloat(stu.paid) || 0)) * 100) / 100;
            if (dueVal > 0) sumDue += dueVal;
        }
    });

    var tfootHtml = '<tr>';
    tfootHtml += '<td class="col-matrix-cb text-center align-middle font-weight-bold" style="background: #e7eff5; color: #1e3a5f;">&Sigma;</td>';
    tfootHtml += '<td class="col-matrix-idx align-middle text-center font-weight-bold" style="background: #e7eff5; color: #1e3a5f;">' + students.length + '</td>';
    tfootHtml += '<td class="col-matrix-student align-middle font-weight-bold" style="background: #e7eff5; color: #1e3a5f; padding: 6px 10px;">' +
                 '  TOTAL (' + students.length + ' Students)' +
                 '</td>';

    stdHeads.forEach(function(h) {
        var val = sumHeadTotals[h.head_name] || 0;
        tfootHtml += '<td class="col-matrix-amount font-weight-bold align-middle" style="background: #e7eff5; color: #1e3a5f; padding: 6px 6px;">₹' + numberFormat(val) + '</td>';
    });

    tfootHtml += '<td class="col-matrix-amount font-weight-bold align-middle text-dark" style="background: #e7eff5; padding: 6px 6px;">₹' + numberFormat(sumGross) + '</td>';
    tfootHtml += '<td class="col-matrix-amount font-weight-bold align-middle text-muted" style="background: #e7eff5; padding: 6px 6px;">₹' + numberFormat(sumDiscount) + '</td>';
    tfootHtml += '<td class="col-matrix-amount font-weight-bold align-middle text-primary" style="background: #e0f2fe; padding: 6px 6px; font-size: 12px;">₹' + numberFormat(sumNet) + '</td>';
    tfootHtml += '<td class="col-matrix-amount font-weight-bold align-middle text-success" style="background: #fef3c7; padding: 6px 6px; font-size: 12px;">₹' + numberFormat(sumPaid) + '</td>';
    tfootHtml += '<td class="col-matrix-amount font-weight-bold align-middle text-danger" style="background: #fee2e2; padding: 6px 6px; font-size: 12px;">₹' + numberFormat(sumDue) + '</td>';
    tfootHtml += '<td class="col-matrix-action align-middle font-weight-bold text-center text-muted" style="background: #e7eff5; padding: 6px 4px; font-size: 10px;">Total</td>';
    tfootHtml += '</tr>';

    $('#matrixTableFoot').html(tfootHtml);
    setTimeout(function() {
        positionFixedScrollToolbox();
    }, 40);
}

// ========================================================
// FIXED BOTTOM HORIZONTAL SCROLL TOOLBOX FOR MATRIX TABLE
// ========================================================
function scrollMatrixTable(direction) {
    var el = document.getElementById('matrixTableResponsive');
    if (!el) return;
    
    var step = 380;
    if (direction === 'left') {
        el.scrollBy({ left: -step, behavior: 'smooth' });
    } else if (direction === 'right') {
        el.scrollBy({ left: step, behavior: 'smooth' });
    } else if (direction === 'start') {
        el.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (direction === 'end') {
        el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
    }
}

function positionFixedScrollToolbox() {
    var tableEl = document.getElementById('matrixTableResponsive');
    var toolbox = document.getElementById('matrixFixedScrollToolbox');
    if (!tableEl || !toolbox) return;

    var rect = tableEl.getBoundingClientRect();
    var centerX = rect.left + (rect.width / 2);
    toolbox.style.left = centerX + 'px';
    toolbox.style.transform = 'translateX(-50%)';

    // Position safely above tfoot so table footer summary totals remain 100% visible and unblocked
    var footEl = document.getElementById('matrixTableFoot');
    if (footEl && footEl.children.length > 0) {
        var footRect = footEl.getBoundingClientRect();
        if (footRect.height > 0 && footRect.top > 0) {
            // Position toolbox with 8px clearance above tfoot's top edge
            var bottomOffset = Math.max(82, Math.round(window.innerHeight - footRect.top + 8));
            toolbox.style.bottom = bottomOffset + 'px';
            return;
        }
    }

    var fallbackBottom = Math.max(82, Math.round(window.innerHeight - rect.bottom + 48));
    toolbox.style.bottom = fallbackBottom + 'px';
}

function updateMatrixScrollIndicators() {
    var tableWrap = document.getElementById('matrixTableResponsive');
    var toolbox = document.getElementById('matrixFixedScrollToolbox');
    var slider = document.getElementById('matrixBottomScrollSlider');

    if (!tableWrap || !toolbox) return;

    var scrollWidth = tableWrap.scrollWidth;
    var clientWidth = tableWrap.clientWidth;
    var scrollLeft = tableWrap.scrollLeft;
    var maxScroll = Math.max(0, scrollWidth - clientWidth);

    if (maxScroll > 15) {
        toolbox.style.display = 'flex';
        positionFixedScrollToolbox();

        if (slider) {
            var pct = Math.round((scrollLeft / maxScroll) * 100);
            slider.value = pct;
        }
    } else {
        toolbox.style.display = 'none';
    }
}

function onMatrixSliderChange(val) {
    var tableWrap = document.getElementById('matrixTableResponsive');
    if (!tableWrap) return;

    var maxScroll = Math.max(0, tableWrap.scrollWidth - tableWrap.clientWidth);
    var targetScroll = (parseFloat(val) / 100) * maxScroll;
    tableWrap.scrollLeft = targetScroll;
}

var matrixHoldScrollInterval = null;

function startHoldScroll(dir) {
    stopHoldScroll();
    var tableWrap = document.getElementById('matrixTableResponsive');
    if (!tableWrap) return;

    var step = (dir === 'left') ? -35 : 35;
    matrixHoldScrollInterval = setInterval(function() {
        tableWrap.scrollLeft += step;
    }, 25);
}

function stopHoldScroll() {
    if (matrixHoldScrollInterval) {
        clearInterval(matrixHoldScrollInterval);
        matrixHoldScrollInterval = null;
    }
}

function initMatrixScrollHandlers() {
    var tableWrap = document.getElementById('matrixTableResponsive');

    if (tableWrap) {
        tableWrap.addEventListener('scroll', function() {
            updateMatrixScrollIndicators();
        });

        // Click-and-drag horizontal scrolling
        var isDown = false;
        var startX = 0;
        var startScrollLeft = 0;

        tableWrap.addEventListener('mousedown', function(e) {
            var tag = (e.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'button' || tag === 'a' || tag === 'select' || e.target.closest('button') || e.target.closest('input')) {
                return;
            }
            isDown = true;
            startX = e.pageX - tableWrap.offsetLeft;
            startScrollLeft = tableWrap.scrollLeft;
            tableWrap.style.cursor = 'grabbing';
            tableWrap.style.userSelect = 'none';
        });

        window.addEventListener('mouseup', function() {
            if (isDown) {
                isDown = false;
                if (tableWrap) {
                    tableWrap.style.cursor = 'default';
                    tableWrap.style.userSelect = '';
                }
            }
            stopHoldScroll();
        });

        tableWrap.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - tableWrap.offsetLeft;
            var walk = (x - startX) * 1.5;
            tableWrap.scrollLeft = startScrollLeft - walk;
        });
    }

    // Attach hold events to buttons
    $(document).on('mousedown touchstart', '.btn-matrix-hold-scroll', function(e) {
        var dir = $(this).data('dir');
        if (dir) startHoldScroll(dir);
    });

    $(document).on('mouseup mouseleave touchend', '.btn-matrix-hold-scroll', function() {
        stopHoldScroll();
    });

    $(window).on('resize scroll', function() {
        adjustMatrixTableHeight();
        positionFixedScrollToolbox();
        updateMatrixScrollIndicators();
    });
}



// Load Ledger Matrix from Backend via AJAX

function loadLedgerMatrix() {

    $('#matrixLoadingOverlay').show();



    $.ajax({

        url: "{{ route('college.fees.ledger_matrix_data') }}",

        type: "GET",

        data: {

            course_id: currentCourseId,

            batch: currentBatch,

            semester_no: currentSemester,

            status: currentStatusFilter,

            gender: currentGenderFilter,

            search: currentSearchQuery

        },

        dataType: 'json',

        success: function(res) {

            $('#matrixLoadingOverlay').hide();

            if (res.status === 'success') {

                if (res.active_semester) {
                    currentSemester = parseInt(res.active_semester);
                    updateBrowserURL(); // Sync active semester in URL
                }

                ledgerDataCache = res;

                renderKPIs(res.kpi);

                var maxSems = (res.course && res.course.max_semesters) ? res.course.max_semesters : (res.max_semesters || 6);
                renderSemesterPills(maxSems);

                renderBatchFeesCard(res.kpi);

                renderMatrixTable(res);

            }

        },

        error: function(xhr) {

            $('#matrixLoadingOverlay').hide();

            showCommonToast('error', 'Failed to load fee ledger matrix.');

        }

    });

}



// Render Top KPI Metrics

function renderKPIs(kpi) {

    if (!kpi) return;

    $('#kpiStudentsCount').text(kpi.filtered_count + ' / ' + kpi.total_enrolled);

    $('#kpiNetPayable').text('₹' + parseFloat(kpi.net_payable).toLocaleString('en-IN', {minimumFractionDigits: 0, maximumFractionDigits: 2}));

    $('#kpiAlreadyPaid').text('₹' + parseFloat(kpi.already_paid).toLocaleString('en-IN', {minimumFractionDigits: 0, maximumFractionDigits: 2}));

    $('#kpiToBeAssigned').text(kpi.to_be_assigned + ' Students');

}



// Render Batch First Fees Card

function renderBatchFeesCard(kpi) {

    if (!kpi) return;

    $('#batchFeeBoysAmt').text('₹' + parseFloat(kpi.batch_first_boys).toLocaleString('en-IN'));

    $('#batchFeeGirlsAmt').text('₹' + parseFloat(kpi.batch_first_girls).toLocaleString('en-IN'));

    $('#batchFeeStatusText span').text('Configured · ' + kpi.assigned_count + ' Students Assigned');

}



// Render Dynamic Semester Pills

function renderSemesterPills(maxSems) {

    var html = '';

    for (var s = 1; s <= (maxSems || 6); s++) {

        var activeClass = (s === currentSemester) ? 'active' : '';

        html += '<button type="button" class="sem-pill-btn ' + activeClass + '" data-sem="' + s + '" onclick="selectSemester(' + s + ')">Sem ' + s + '</button>';

    }

    $('#semPillsContainer').html(html);

}



// Event: Select Semester

function selectSemester(sem) {

    currentSemester = parseInt(sem);

    $('.sem-pill-btn').removeClass('active');

    $('.sem-pill-btn[data-sem="' + sem + '"]').addClass('active');
    $('.sem-pill-btn:contains("Sem ' + sem + '")').addClass('active');

    updateBrowserURL({
        semester_no: currentSemester,
        student_id: null
    });

    loadLedgerMatrix();

}



// Event: Filter Change (Course / Batch) - Automatically selects active semester

function onFilterChange(resetSem) {
    if (resetSem) {
        window.lastHighlightedStudentId = null;
    }

    currentCourseId = $('#selLedgerCourse').val();

    currentBatch = $('#selLedgerBatch').val();

    if (resetSem) {

        currentSemester = 'auto'; // Automatically resolved to active semester by backend

    }

    updateBrowserURL({
        course_id: currentCourseId,
        batch: currentBatch,
        semester_no: currentSemester !== 'auto' ? currentSemester : null,
        student_id: null
    });

    loadLedgerMatrix();

}



// Event: Fee Status Filter

function selectStatusFilter(btn, status) {

    $('.filter-pills-row .filter-pill-btn[data-status]').removeClass('active');

    $(btn).addClass('active');

    currentStatusFilter = status;

    loadLedgerMatrix();

}



// Event: Gender Filter

function selectGenderFilter(btn, gender) {

    $('.filter-pills-row .filter-pill-btn[data-gender]').removeClass('active');

    $(btn).addClass('active');

    currentGenderFilter = gender;

    loadLedgerMatrix();

}



// Event: Real-time Search Input with Debounce

function onSearchInput(val) {

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(function() {

        currentSearchQuery = $.trim(val);

        loadLedgerMatrix();

    }, 350);

}



// Auto-save Flash Animation

function triggerAutoSaveFeedback() {

    var pill = $('#autoSaveIndicator');

    pill.css({'background': '#15803d', 'border-color': '#166534'});

    $('#autoSaveText').text('Saving...');

    setTimeout(function() {

        pill.css({'background': 'rgba(255, 255, 255, 0.18)', 'border-color': 'rgba(255, 255, 255, 0.3)'});

        $('#autoSaveText').text('Auto-saved');

    }, 700);

}



// Render Matrix Spreadsheet Table

function renderMatrixTable(data) {

    var stdHeads = data.standard_heads || [];

    var students = data.rows || [];



    // Build Table Header

    var thHtml = '<tr>';

    thHtml += '<th class="col-matrix-cb"><input type="checkbox" id="masterStudentCheckbox" onchange="toggleMasterStudentCheckbox(this)" title="Select / Deselect all students"></th>';

    thHtml += '<th class="col-matrix-idx">#</th>';

    thHtml += '<th class="col-matrix-student">Student / Roll</th>';

    stdHeads.forEach(function(h) {

        thHtml += '<th class="col-matrix-amount">' + escapeHtml(h.head_name) + '</th>';

    });

    thHtml += '<th class="col-matrix-amount">Gross</th>';

    thHtml += '<th class="col-matrix-amount">Discount</th>';

    thHtml += '<th class="col-matrix-amount" style="background: #e0f2fe !important;">Net</th>';

    thHtml += '<th class="col-matrix-amount" style="background: #fef3c7 !important;">Paid (Locked)</th>';

    thHtml += '<th class="col-matrix-amount" style="background: #fee2e2 !important;">Outstanding</th>';

    thHtml += '<th class="col-matrix-action">Action</th>';

    thHtml += '</tr>';

    $('#matrixTableHead').html(thHtml);



    // Build Table Body Rows

    if (students.length === 0) {

        var colSpan = 9 + stdHeads.length;

        $('#matrixTableBody').html('<tr><td colspan="' + colSpan + '" class="text-center py-5 text-muted"><i class="fa fa-folder-open-o fa-2x mb-2"></i><br>No students found matching current filters.</td></tr>');
        $('#matrixTableFoot').empty();
        adjustMatrixTableHeight();
        onStudentSelectChange();
        setTimeout(updateMatrixScrollIndicators, 100);

        return;

    }



    var tbHtml = '';

    students.forEach(function(stu, idx) {

        tbHtml += '<tr id="studentRow_' + stu.student_id + '">';

        

        var isProgressed = stu.is_progressed || false;

        // Student Checkbox
        tbHtml += '<td class="col-matrix-cb align-middle">';
        if (isProgressed) {
            tbHtml += '<input type="checkbox" class="student-select-cb" disabled title="Student has progressed to Semester ' + stu.next_semester + ' (Closed)">';
        } else {
            tbHtml += '<input type="checkbox" class="student-select-cb" value="' + stu.student_id + '" onchange="onStudentSelectChange()">';
        }
        tbHtml += '</td>';

        // #
        tbHtml += '<td class="col-matrix-idx text-muted align-middle" style="font-size: 11px;">' + (idx + 1) + '</td>';

        // Student Info
        var genderBadge = stu.is_girl 
            ? '<span class="badge" style="background: #fce7f3; color: #be185d; font-size: 8.5px; padding: 1px 3px; font-weight: 600; line-height: 1; border-radius: 3px;">Girl</span>'
            : '<span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 8.5px; padding: 1px 3px; font-weight: 600; line-height: 1; border-radius: 3px;">Boy</span>';

        tbHtml += '<td class="col-matrix-student align-middle">';
        tbHtml += '  <div class="d-flex align-items-center" style="gap: 4px;">';
        tbHtml += '    <span class="student-cell-name" title="' + escapeHtml(stu.name) + '">' + escapeHtml(stu.name) + '</span>';
        tbHtml += '    ' + genderBadge;
        tbHtml += '  </div>';
        tbHtml += '  <div class="student-cell-sub"><i class="fa fa-id-badge mr-1 text-muted"></i>' + escapeHtml(stu.roll_no) + '</div>';
        tbHtml += '</td>';

        // Standard Fee Head Columns
        stdHeads.forEach(function(h) {
            var headVal = stu.head_values[h.head_name];
            var isAssigned = headVal ? headVal.assigned : false;
            var amount = headVal ? headVal.amount : 0;
            var defAmt = headVal ? headVal.default_amount : 0;
            var lowerHeadName = (h.head_name || '').toLowerCase();
            var isPrevDues = lowerHeadName.indexOf('previous dues') !== -1 || lowerHeadName.indexOf('prev dues') !== -1 || lowerHeadName.indexOf('previous fee') !== -1 || lowerHeadName.indexOf('prev fee') !== -1 || !!h.is_previous_dues;

            tbHtml += '<td class="col-matrix-amount align-middle">';
            if (isProgressed) {
                if (isAssigned) {
                    tbHtml += '<span class="font-weight-bold" style="font-size: 11.5px; color: #475569;">₹' + numberFormat(amount) + '</span>';
                } else {
                    tbHtml += '<span class="text-muted">&mdash;</span>';
                }
            } else if (isAssigned) {
                var negStyle = (amount < 0) ? ' style="color: #0369a1 !important; font-weight: 700; background: #f0f9ff;" title="Advance credit carried forward from previous semester" ' : '';
                tbHtml += '<input type="number" step="0.01" class="cell-fee-input" ' + negStyle + ' value="' + amount + '" ' +
                          'data-student-id="' + stu.student_id + '" ' +
                          'data-head-name="' + h.head_name + '" ' +
                          'data-orig-val="' + amount + '" ' +
                          'onblur="onCellFeeBlur(this)">';
            } else if (isPrevDues) {
                // User requirement: previous dues head me kabhi bhi "+ Add" button nahi ayega
                tbHtml += '<span class="text-muted" style="font-size: 11px;">&mdash;</span>';
            } else {
                tbHtml += '<button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" ' +
                          'onclick="assignOptionalHead(' + stu.student_id + ', \'' + addslashes(h.head_name) + '\', ' + defAmt + ')" ' +
                          'style="font-size: 10px; padding: 1px 6px;">+ Add</button>';
            }
            tbHtml += '</td>';
        });

        // Gross
        tbHtml += '<td class="col-matrix-amount font-weight-bold" id="cellGross_' + stu.student_id + '" style="font-size: 11.5px;">₹' + stu.gross + '</td>';

        // Discount
        tbHtml += '<td class="col-matrix-amount align-middle">';
        if (isProgressed) {
            tbHtml += '  <div class="d-flex align-items-center justify-content-end" style="gap: 4px;">';
            tbHtml += '    <span class="font-weight-bold" style="font-size: 11.5px; color: #475569;">₹' + numberFormat(stu.discount) + '</span>';
            var noteDisplay = isGlobalDiscountNotesVisible ? 'inline-block' : 'none';
            if (stu.discount_reason) {
                tbHtml += '    <span class="discount-reason-pill discount-note-pill" style="display: ' + noteDisplay + ';" ' +
                          'title="' + escapeHtml(stu.discount_reason) + '">' +
                          escapeHtml(stu.discount_reason) + '</span>';
            }
            tbHtml += '  </div>';
        } else {
            tbHtml += '  <div class="d-flex align-items-center justify-content-end" style="gap: 4px;">';
            tbHtml += '    <input type="number" step="0.01" class="cell-fee-input" style="width: 62px;" value="' + stu.discount + '" ' +
                      'data-student-id="' + stu.student_id + '" ' +
                      'data-orig-val="' + stu.discount + '" ' +
                      'onblur="onDiscountBlur(this)">';

            var noteDisplay = isGlobalDiscountNotesVisible ? 'inline-block' : 'none';
            if (stu.discount_reason) {
                var safeReason = escapeHtml(stu.discount_reason).replace(/'/g, "\\'");
                tbHtml += '    <span class="discount-reason-pill discount-note-pill" style="display: ' + noteDisplay + ';" ' +
                          'onclick="openReasonModal(' + stu.student_id + ', \'' + safeReason + '\')" title="Click to edit note">' +
                          escapeHtml(stu.discount_reason) + '</span>';
            } else {
                tbHtml += '    <span class="discount-reason-pill text-muted discount-note-pill" style="display: ' + noteDisplay + '; background:#f1f5f9; border-color:#cbd5e1;" ' +
                          'onclick="openReasonModal(' + stu.student_id + ', \'\')" title="Add note">+ Note</span>';
            }
            tbHtml += '  </div>';
        }
        tbHtml += '</td>';

        // Net
        tbHtml += '<td class="col-matrix-amount font-weight-bold text-dark" id="cellNet_' + stu.student_id + '" style="font-size: 11.5px; background: #f0f9ff;">₹' + stu.net + '</td>';

        // Paid (Locked)
        tbHtml += '<td class="col-matrix-amount text-success font-weight-bold" id="cellPaid_' + stu.student_id + '" style="font-size: 11.5px; background: #fffbeb;">';
        if (stu.paid > 0) {
            tbHtml += '₹' + stu.paid + ' <i class="fa fa-lock text-muted ml-1" style="font-size: 10px;" title="Paid amount is locked"></i>';
        } else {
            tbHtml += '₹0';
        }
        tbHtml += '</td>';

        // Outstanding (Net - Paid or 0 if progressed)
        var dueVal = isProgressed ? 0 : (Math.round((parseFloat(stu.net) - parseFloat(stu.paid)) * 100) / 100);
        var dueBg = isProgressed ? '#f8fafc' : ((dueVal > 0) ? '#fff5f5' : (dueVal < 0 ? '#f0fdf4' : '#ffffff'));

        tbHtml += '<td class="col-matrix-amount font-weight-bold" id="cellDue_' + stu.student_id + '" style="font-size: 11.5px; background: ' + dueBg + ';">';
        if (isProgressed) {
            tbHtml += '<span class="text-muted">₹0</span>';
            var trAmt = stu.transferred_dues || 0;
            if (trAmt > 0) {
                tbHtml += '<div><span class="badge badge-info" style="font-size: 8px; font-weight: 500;" title="₹' + numberFormat(trAmt) + ' transferred to Sem ' + stu.next_semester + ' as Previous Dues"><i class="fa fa-arrow-right mr-1"></i>₹' + numberFormat(trAmt) + ' to Sem ' + stu.next_semester + '</span></div>';
            } else if (trAmt < 0) {
                tbHtml += '<div><span class="badge badge-success" style="font-size: 8px; font-weight: 500;" title="-₹' + numberFormat(Math.abs(trAmt)) + ' advance transferred to Sem ' + stu.next_semester + '"><i class="fa fa-arrow-right mr-1"></i>-₹' + numberFormat(Math.abs(trAmt)) + ' Adv to Sem ' + stu.next_semester + '</span></div>';
            } else {
                tbHtml += '<div><span class="badge badge-success" style="font-size: 8px; font-weight: 500;"><i class="fa fa-check mr-1"></i>Cleared</span></div>';
            }
        } else if (dueVal > 0) {
            tbHtml += '<span class="text-danger">₹' + numberFormat(dueVal) + '</span>';
        } else if (dueVal < 0) {
            tbHtml += '<span class="text-success">-₹' + numberFormat(Math.abs(dueVal)) + ' <small class="badge badge-success px-1" style="font-size: 8.5px;">Adv</small></span>';
        } else if (parseFloat(stu.net) > 0 && parseFloat(stu.paid) > 0) {
            tbHtml += '<span class="badge badge-success px-2 py-1" style="font-size: 9.5px;"><i class="fa fa-check mr-1"></i>Cleared</span>';
        } else {
            tbHtml += '<span class="text-muted">₹0</span>';
        }
        tbHtml += '</td>';

        // Actions: Collect / Modify Fee Button
        tbHtml += '<td class="col-matrix-action align-middle" id="cellAction_' + stu.student_id + '">';
        var stuPaid = parseFloat(stu.paid) || 0;
        var stuNet = parseFloat(stu.net) || 0;
        var stuDue = Math.round((stuNet - stuPaid) * 100) / 100;
        var isFullyPaidWithReceipt = (stuPaid > 0 && stuDue === 0);

        if (isProgressed) {
            tbHtml += '  <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" ' +
                      'onclick="openCollectFeeModal(' + stu.student_id + ')" ' +
                      'title="View past semester records (Closed - Progressed to Sem ' + stu.next_semester + ')" style="font-size: 10px; padding: 2px 7px; border-radius: 3px;">' +
                      '    <i class="fa fa-eye mr-1 text-primary"></i> View Only' +
                      '  </button>';
        } else if (isFullyPaidWithReceipt) {
            tbHtml += '  <button type="button" class="btn btn-xs btn-outline-success font-weight-bold" ' +
                      'onclick="openCollectFeeModal(' + stu.student_id + ')" ' +
                      'title="View payments, modify receipts, or collect advance fees" style="font-size: 10.5px; padding: 2px 8px; border-radius: 3px;">' +
                      '    <i class="fa fa-pencil-square-o mr-1"></i> Paid (Modify)' +
                      '  </button>';
        } else if (stu.status !== 'Unassigned' || stu.gross != 0 || stuPaid > 0) {
            tbHtml += '  <button type="button" class="btn btn-xs btn-success font-weight-bold" ' +
                      'onclick="openCollectFeeModal(' + stu.student_id + ')" ' +
                      'title="Collect fees, modify receipts, or manage heads" style="font-size: 10.5px; padding: 2px 8px; border-radius: 3px; background: #16a34a; border-color: #16a34a;">' +
                      '    <i class="fa fa-pencil-square-o mr-1"></i> Collect / Modify' +
                      '  </button>';
        } else {
            tbHtml += '  <span class="text-muted" style="font-size: 10.5px;">&mdash;</span>';
        }
        tbHtml += '</td>';

        tbHtml += '</tr>';

    });

    // Spacer row so the bottom-most student row remains accessible above the floating toolbar
    tbHtml += '<tr class="matrix-bottom-spacer-row" style="height: 38px; border: none !important;">';
    tbHtml += '  <td colspan="100" style="height: 38px; border: none !important; background: transparent !important; padding: 0 !important; pointer-events: none;"></td>';
    tbHtml += '</tr>';

    $('#matrixTableBody').html(tbHtml);

    recalculateMatrixTableFoot();
    adjustMatrixTableHeight();
    onStudentSelectChange();
    setTimeout(updateMatrixScrollIndicators, 100);

    // Auto-open Collect/Modify modal if student_id was specified in URL
    if (autoOpenStudentId) {
        var targetStudentId = autoOpenStudentId;
        autoOpenStudentId = null; // Consume immediately so subsequent filter changes don't re-trigger

        var $targetRow = $('#studentRow_' + targetStudentId);
        if ($targetRow.length) {
            // 1. Apply vibrant pulsating animation effect immediately to every cell of this row
            $targetRow.addClass('row-student-searched');

            // 2. Center the target row in both the table container and page
            scrollStudentRowToCenter($targetRow, true);

            // 3. Keep animation active with standard 750 ms delay before modal opening
            setTimeout(function() {
                // Settle animation to solid focus highlight when modal opens
                $targetRow.removeClass('row-student-searched').addClass('row-student-focused');
                openCollectFeeModal(targetStudentId);
            }, 750);
        } else {
            // Fallback if student row not in current matrix view
            setTimeout(function() {
                openCollectFeeModal(targetStudentId);
            }, 750);
        }
    } else if (window.lastHighlightedStudentId) {
        // Re-apply highlight to focused student row and maintain centered scroll
        var $prevRow = $('#studentRow_' + window.lastHighlightedStudentId);
        if ($prevRow.length) {
            $prevRow.addClass('row-student-focused');
            scrollStudentRowToCenter($prevRow, false);
        }
    }

}



// Real-Time Cell Blur: Fee Amount Update

function onCellFeeBlur(input) {

    var studentId = $(input).data('student-id');

    var headName = $(input).data('head-name');

    var origVal = parseFloat($(input).data('orig-val')) || 0;

    var newVal = parseFloat($(input).val()) || 0;



    if (origVal === newVal) return;



    if (newVal < 0 && (headName || '').toLowerCase().indexOf('previous dues') === -1) {

        showCommonToast('error', 'Fee amount cannot be negative.');

        $(input).val(origVal);

        return;

    }



    triggerAutoSaveFeedback();



    $.ajax({

        url: "{{ route('college.fees.update_cell_fee') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            student_id: studentId,

            course_id: currentCourseId,

            semester_no: currentSemester,

            head_name: headName,

            amount: newVal

        },

        dataType: 'json',

        success: function(res) {

            if (res.status === 'success') {

                if (newVal <= 0) {

                    showCommonToast('success', headName + ' removed.');

                    loadLedgerMatrix();

                } else {

                    $(input).data('orig-val', newVal);

                    updateRowValues(studentId, res.row);

                }

            }

        },

        error: function(xhr) {

            $(input).val(origVal);

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Cell update failed.';

            showCommonToast('error', msg);

        }

    });

}



// Assign Optional Head Inline

function assignOptionalHead(studentId, headName, defAmt) {

    var lower = (headName || '').toLowerCase();
    if (lower.indexOf('previous dues') !== -1 || lower.indexOf('prev dues') !== -1 || lower.indexOf('previous fee') !== -1 || lower.indexOf('prev fee') !== -1) {
        showCommonToast('error', 'Previous Dues is an automatic progression rollover head and cannot be manually added.');
        return;
    }

    var amt = parseFloat(defAmt) || 0;

    if (amt <= 0) {

        var inputAmt = prompt("Enter fee amount for " + headName + ":", "0");

        if (inputAmt === null) return;

        amt = parseFloat(inputAmt) || 0;

        if (amt <= 0) {

            showCommonToast('error', 'Please enter a valid amount greater than 0.');

            return;

        }

    }



    triggerAutoSaveFeedback();



    $.ajax({

        url: "{{ route('college.fees.update_cell_fee') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            student_id: studentId,

            course_id: currentCourseId,

            semester_no: currentSemester,

            head_name: headName,

            amount: amt

        },

        dataType: 'json',

        success: function(res) {

            if (res.status === 'success') {

                loadLedgerMatrix();

            }

        },

        error: function(xhr) {

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Optional head assignment failed.';

            showCommonToast('error', msg);

        }

    });

}



// Real-Time Discount Blur Update

function onDiscountBlur(input) {

    var studentId = $(input).data('student-id');

    var origVal = parseFloat($(input).data('orig-val')) || 0;

    var newVal = parseFloat($(input).val()) || 0;



    if (origVal === newVal) return;



    if (newVal < 0) {

        showCommonToast('error', 'Discount cannot be negative.');

        $(input).val(origVal);

        return;

    }



    triggerAutoSaveFeedback();



    $.ajax({

        url: "{{ route('college.fees.update_discount') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            student_id: studentId,

            semester_no: currentSemester,

            discount_amount: newVal

        },

        dataType: 'json',

        success: function(res) {

            if (res.status === 'success') {

                $(input).data('orig-val', newVal);

                updateRowValues(studentId, res.row);

            }

        },

        error: function(xhr) {

            $(input).val(origVal);

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Unable to apply discount.';

            showCommonToast('error', msg);

        }

    });

}



// Update Row Totals Dynamically

function updateRowValues(studentId, rowData) {

    if (!rowData) return;

    $('#cellGross_' + studentId).text('₹' + rowData.gross);

    $('#cellNet_' + studentId).text('₹' + rowData.net);

    $('#cellPaid_' + studentId).text('₹' + rowData.paid);

    var dueVal = Math.round((parseFloat(rowData.net) - parseFloat(rowData.paid)) * 100) / 100;
    var dueBg = (dueVal > 0) ? '#fff5f5' : (dueVal < 0 ? '#f0fdf4' : '#ffffff');
    var dueHtml = '';
    if (dueVal > 0) {
        dueHtml = '<span class="text-danger">₹' + numberFormat(dueVal) + '</span>';
    } else if (dueVal < 0) {
        dueHtml = '<span class="text-success">-₹' + numberFormat(Math.abs(dueVal)) + ' <small class="badge badge-success px-1" style="font-size: 8.5px;">Adv</small></span>';
    } else if (parseFloat(rowData.net) > 0 && parseFloat(rowData.paid) > 0) {
        dueHtml = '<span class="badge badge-success px-2 py-1" style="font-size: 9.5px;"><i class="fa fa-check mr-1"></i>Cleared</span>';
    } else {
        dueHtml = '<span class="text-muted">₹0</span>';
    }
    $('#cellDue_' + studentId).html(dueHtml).css('background', dueBg);

    // Update Action button in row
    var paidVal = parseFloat(rowData.paid) || 0;
    var netVal = parseFloat(rowData.net) || 0;
    var roundDue = Math.round((netVal - paidVal) * 100) / 100;
    var isFullyPaidWithReceipt = (paidVal > 0 && roundDue === 0);

    var actionHtml = '';
    if (rowData.is_progressed) {
        actionHtml = '<button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" ' +
                     'onclick="openCollectFeeModal(' + studentId + ')" ' +
                     'title="View past semester records (Closed)" style="font-size: 10px; padding: 2px 7px; border-radius: 3px;">' +
                     '  <i class="fa fa-eye mr-1 text-primary"></i> View Only' +
                     '</button>';
    } else if (isFullyPaidWithReceipt) {
        actionHtml = '<button type="button" class="btn btn-xs btn-outline-success font-weight-bold" ' +
                     'onclick="openCollectFeeModal(' + studentId + ')" ' +
                     'title="View payments, modify receipts, or collect advance fees" style="font-size: 10.5px; padding: 2px 8px; border-radius: 3px;">' +
                     '  <i class="fa fa-pencil-square-o mr-1"></i> Paid (Modify)' +
                     '</button>';
    } else if (rowData.status !== 'Unassigned' || parseFloat(rowData.gross) !== 0 || paidVal > 0) {
        actionHtml = '<button type="button" class="btn btn-xs btn-success font-weight-bold" ' +
                     'onclick="openCollectFeeModal(' + studentId + ')" ' +
                     'title="Collect fees, modify receipts, or manage heads" style="font-size: 10.5px; padding: 2px 8px; border-radius: 3px; background: #16a34a; border-color: #16a34a;">' +
                     '  <i class="fa fa-pencil-square-o mr-1"></i> Collect / Modify' +
                     '</button>';
    } else {
        actionHtml = '<span class="text-muted" style="font-size: 10.5px;">&mdash;</span>';
    }
    $('#cellAction_' + studentId).html(actionHtml);

    recalculateMatrixTableFoot();

}



// Global Discount Notes Visibility Toggle
var isGlobalDiscountNotesVisible = false;

function toggleDiscountNotesGlobal() {
    isGlobalDiscountNotesVisible = !isGlobalDiscountNotesVisible;
    var btn = $('#btnToggleGlobalNotes');
    var icon = $('#iconGlobalNotes');
    var lbl = $('#lblGlobalNotes');

    if (isGlobalDiscountNotesVisible) {
        $('.discount-note-pill').css('display', 'inline-block');
        btn.addClass('active').css({ 'background': '#fef3c7', 'border-color': '#f59e0b', 'color': '#92400e', 'font-weight': 'bold' });
        icon.removeClass('fa-eye text-primary').addClass('fa-eye-slash text-warning');
        lbl.text('Hide Notes');
    } else {
        $('.discount-note-pill').css('display', 'none');
        btn.removeClass('active').css({ 'background': '', 'border-color': '', 'color': '', 'font-weight': '' });
        icon.removeClass('fa-eye-slash text-warning').addClass('fa-eye text-primary');
        lbl.text('Show Notes');
    }
}

// Open Reason Tag Modal

function openReasonModal(studentId, currentReason) {

    $('#reason_student_id').val(studentId);

    $('#input_reason_text').val(currentReason);

    $('#modalEditReason').modal('show');

}



// Save Discount Reason Tag

function saveDiscountReasonTag() {

    var studentId = $('#reason_student_id').val();

    var reason = $.trim($('#input_reason_text').val());



    $.ajax({

        url: "{{ route('college.fees.update_discount') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            student_id: studentId,

            semester_no: currentSemester,

            discount_reason: reason

        },

        dataType: 'json',

        success: function(res) {

            $('#modalEditReason').modal('hide');

            showCommonToast('success', 'Discount reason tag updated.');

            loadLedgerMatrix();

        },

        error: function(xhr) {

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to update reason.';

            showCommonToast('error', msg);

        }

    });

}



// Open Add Custom Head Modal

// ========================================================
// ========================================================
// COLLECT FEE MODAL INTERACTION, MULTI-MODE SPLIT & RECEIPT HANDLERS
// ========================================================

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

var activeCollectStudentId = null;
var activeCollectDataCache = null;
var activeEditingReceiptId = null;
var pendingConfirmCallback = null;
var isSplitPaymentActive = false;
var splitPaymentRows = [];
var isHistoryExpanded = false;

function showFeeActionConfirm(options) {
    $('#confirmModalTitle').html(options.title || 'Confirm Action');
    $('#confirmModalMessage').html(options.message || 'Are you sure you want to proceed?');
    $('#confirmModalSubnote').html(options.subnote || '');
    
    var iconWrapper = $('#confirmModalIconWrapper');
    var icon = $('#confirmModalIcon');
    var actionBtn = $('#btnConfirmModalAction');

    if (options.type === 'danger') {
        iconWrapper.css({ 'background': '#fee2e2', 'color': '#dc2626' });
        icon.attr('class', options.icon || 'fa fa-trash');
        actionBtn.attr('class', 'btn btn-sm btn-danger px-3 font-weight-bold').text(options.btnText || 'Yes, Delete');
    } else if (options.type === 'warning') {
        iconWrapper.css({ 'background': '#fef3c7', 'color': '#d97706' });
        icon.attr('class', options.icon || 'fa fa-exclamation-triangle');
        actionBtn.attr('class', 'btn btn-sm btn-warning px-3 font-weight-bold text-dark').text(options.btnText || 'Yes, Confirm');
    } else {
        iconWrapper.css({ 'background': '#dcfce7', 'color': '#16a34a' });
        icon.attr('class', options.icon || 'fa fa-check-circle');
        actionBtn.attr('class', 'btn btn-sm btn-success px-3 font-weight-bold').text(options.btnText || 'Yes, Proceed');
    }

    pendingConfirmCallback = options.onConfirm || null;
    $('#modalFeeActionConfirm').modal('show');
}

$(document).on('click', '#btnConfirmModalAction', function() {
    $('#modalFeeActionConfirm').modal('hide');
    if (typeof pendingConfirmCallback === 'function') {
        var cb = pendingConfirmCallback;
        pendingConfirmCallback = null;
        cb();
    }
});

// Helper: Scroll target student row to vertical center of table container and page
function scrollStudentRowToCenter($targetRow, animate) {
    if (!$targetRow || !$targetRow.length) return;

    var rowEl = $targetRow[0];
    var tableWrap = document.getElementById('matrixTableResponsive');
    if (tableWrap) {
        var wrapRect = tableWrap.getBoundingClientRect();
        var rowRect = rowEl.getBoundingClientRect();

        // Relative distance from table container top to row top
        var currentRelativeTop = rowRect.top - wrapRect.top;
        var currentScroll = tableWrap.scrollTop;
        var wrapHeight = tableWrap.clientHeight;

        // Position row centered in the table container viewport
        var targetScroll = currentScroll + currentRelativeTop - (wrapHeight / 2) + (rowRect.height / 2);
        var maxScroll = tableWrap.scrollHeight - tableWrap.clientHeight;
        targetScroll = Math.max(0, Math.min(targetScroll, maxScroll));

        if (animate === false) {
            tableWrap.scrollTop = targetScroll;
        } else {
            $(tableWrap).stop(true, false).animate({ scrollTop: targetScroll }, 250);
        }
    }

    try {
        rowEl.scrollIntoView({ behavior: (animate === false ? 'auto' : 'smooth'), block: 'center', inline: 'nearest' });
    } catch(e) {}
}

function openCollectFeeModal(studentId) {
    activeCollectStudentId = studentId;
    activeEditingReceiptId = null;
    window.lastHighlightedStudentId = studentId;

    // Highlight the respective table row in yellow (#fef9c3)
    $('.matrix-table tbody tr').removeClass('row-student-focused row-student-searched');
    var $targetRow = $('#studentRow_' + studentId);
    if ($targetRow.length) {
        $targetRow.addClass('row-student-focused');

        // Scroll selected row to center of table and viewport
        scrollStudentRowToCenter($targetRow, true);
    }

    // Real-time URL sync with current student_id
    updateBrowserURL({ student_id: studentId });
    isSplitPaymentActive = false;
    splitPaymentRows = [];
    isHistoryExpanded = false;

    $('#receipt_edit_mode_bar').removeClass('is-active');
    $('.collect-history-card').removeClass('is-editing-receipt');
    $('.badge-editing-indicator').remove();
    $('#btnSubmitCollectPayment').html('<i class="fa fa-check mr-1"></i> Collect <span id="btnCollectLabelAmt" class="mx-1">₹0</span> &amp; Receipt')
        .removeClass('btn btn-warning').addClass('btn-collect-submit')
        .css({ 'background': '', 'border-color': '', 'color': '' });

    $('#collect_single_mode_panel').show();
    $('#collect_split_mode_panel').hide();
    $('#collect_payment_mode_container').show();
    $('#collect_action_summary_box').show();
    $('#collect_progressed_info_box').hide();
    $('#modalCollectFeeProgressedBanner').remove();
    $('#lblToggleSplitText').text('+ Split Mode (Multi)');
    $('#tbodyCollectHeads').html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading fee heads...</td></tr>');
    $('#collect_receipts_history_container').html('<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin"></i></div>');
    $('#collect_history_expand_wrapper').hide();
    $('#modalCollectFee').modal('show');

    $.ajax({
        url: "{{ route('college.fees.get_student_collect_data') }}",
        type: "GET",
        data: {
            student_id: studentId,
            semester_no: currentSemester
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                activeCollectDataCache = res;
                renderCollectModal(res);
            } else {
                showCommonToast('error', res.message || 'Failed to load collection details.');
                $('#modalCollectFee').modal('hide');
            }
        },
        error: function(xhr) {
            showCommonToast('error', 'Error fetching collection details.');
            $('#modalCollectFee').modal('hide');
        }
    });
}

function renderCollectModal(data) {
    var stu = data.student || {};
    var heads = data.heads || [];
    var receipts = data.receipts || [];
    var isProgressed = stu.is_progressed || false;

    $('#modalCollectFeeProgressedBanner').remove();
    if (isProgressed) {
        var transferAmt = parseFloat(stu.transferred_dues) || 0;
        var badgePill = '';
        var transferLabel = 'Outstanding Dues Transferred:';
        var transferFormatted = '₹0.00 (All Cleared)';
        var transferColor = 'text-dark';
        var transferRowBg = 'background: #f8fafc; border: 1px solid #e2e8f0;';

        if (transferAmt > 0) {
            badgePill = '<span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 11px;"><i class="fa fa-arrow-circle-right mr-1"></i>Transferred Due: ₹' + numberFormat(transferAmt) + '</span>';
            transferLabel = 'Dues Transferred to Sem ' + (stu.next_semester || '') + ':';
            transferFormatted = '₹' + numberFormat(transferAmt);
            transferColor = 'text-danger';
            transferRowBg = 'background: #fef2f2; border: 1.5px solid #fecaca;';
        } else if (transferAmt < 0) {
            var advAmt = Math.abs(transferAmt);
            badgePill = '<span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;"><i class="fa fa-arrow-circle-right mr-1"></i>Advance Credit: -₹' + numberFormat(advAmt) + '</span>';
            transferLabel = 'Advance Carried to Sem ' + (stu.next_semester || '') + ':';
            transferFormatted = '-₹' + numberFormat(advAmt);
            transferColor = 'text-success';
            transferRowBg = 'background: #f0fdf4; border: 1.5px solid #bbf7d0;';
        } else {
            badgePill = '<span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;"><i class="fa fa-check mr-1"></i>Dues Cleared</span>';
            transferLabel = 'Closing Balance:';
            transferFormatted = '₹0.00 (All Cleared)';
            transferColor = 'text-success';
            transferRowBg = 'background: #f0fdf4; border: 1px solid #bbf7d0;';
        }

        var bannerHtml = '<div id="modalCollectFeeProgressedBanner" class="p-3 mb-3 rounded" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1.5px solid #7dd3fc; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">' +
                         '  <div class="d-flex align-items-start" style="gap: 12px;">' +
                         '    <div style="width: 38px; height: 38px; min-width: 38px; border-radius: 50%; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">' +
                         '      <i class="fa fa-history"></i>' +
                         '    </div>' +
                         '    <div class="flex-grow-1">' +
                         '      <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">' +
                         '        <div class="d-flex align-items-center" style="gap: 8px;">' +
                         '          <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 11px; letter-spacing: 0.3px; text-transform: uppercase;">' +
                         '            Semester Progressed' +
                         '          </span>' +
                         '          <strong class="text-dark" style="font-size: 13.5px;">' +
                         '            Semester ' + (stu.semester_no || currentSemester) + ' Closed &bull; Transferred to Semester ' + (stu.next_semester || '') +
                         '          </strong>' +
                         '        </div>' +
                         '        <div>' + badgePill + '</div>' +
                         '      </div>' +
                         '      <div class="mt-2 text-secondary" style="font-size: 12px; line-height: 1.5;">' +
                         '        <i class="fa fa-info-circle text-primary mr-1"></i> This student has progressed to <strong>Semester ' + (stu.next_semester || '') + '</strong>. ' +
                         '        All outstanding balances / advance credits have been transferred to Semester ' + (stu.next_semester || '') + ' as <strong>Previous Dues</strong>. ' +
                         '        Fee collection is locked for this past semester.' +
                         '      </div>' +
                         '    </div>' +
                         '  </div>' +
                         '</div>';

        $('#modalCollectFee .modal-body').prepend(bannerHtml);

        // Hide collection boxes (Image 2 and Payment Details)
        $('#collect_action_summary_box').hide();
        $('#collect_payment_mode_container').hide();
        $('#collect_modal_mode_banner').hide();
        $('#collect_quick_tools_wrapper').hide();
        $('#badgeTotalModeActivePill').hide();

        // Populate Progressed Info Box in right column
        $('#lblProgressedTargetBadge').text('Progressed to Sem ' + (stu.next_semester || ''));
        var totalNet = (data.totals && data.totals.net) ? parseFloat(data.totals.net) : 0;
        var totalPaid = (data.totals && data.totals.paid) ? parseFloat(data.totals.paid) : 0;
        $('#lblProgressedSumNet').text('₹' + numberFormat(totalNet));
        $('#lblProgressedSumPaid').text('₹' + numberFormat(totalPaid));
        $('#lblProgressedTransferLabel').text(transferLabel);
        $('#lblProgressedTransferAmt').attr('class', 'font-weight-bold ' + transferColor).text(transferFormatted);
        $('#progressed_transfer_dues_row').attr('style', 'font-size: 12px; ' + transferRowBg);
        $('#collect_progressed_info_box').show();

        // Adjust modal header
        $('#modalCollectFee .modal-title').html('<i class="fa fa-archive mr-2" style="color: #217346;"></i> Archived Fee Ledger &mdash; <span id="collect_student_name">' + escapeHtml(stu.name || 'Student') + '</span>');
    } else {
        $('#collect_action_summary_box').show();
        $('#collect_payment_mode_container').show();
        $('#collect_progressed_info_box').hide();
        $('#collect_quick_tools_wrapper').show();

        // Normal modal title
        $('#modalCollectFee .modal-title').html('<i class="fa fa-money mr-2" style="color: #217346;"></i> Collect / Modify Fee &mdash; <span id="collect_student_name">' + escapeHtml(stu.name || 'Student') + '</span>');
    }

    // Header Initials
    var initials = 'ST';
    if (stu.name) {
        var parts = stu.name.trim().split(/\s+/);
        if (parts.length >= 2) {
            initials = parts[0][0] + parts[1][0];
        } else if (parts.length === 1 && parts[0].length > 0) {
            initials = parts[0].substr(0, 2);
        }
    }
    $('#collect_student_avatar').text(initials.toUpperCase());
    $('#collect_student_name').text(stu.name || 'Student');
    $('#collect_student_sub').text((stu.roll_no || '') + ' • ' + (stu.course_name || '') + ' • Batch ' + (stu.batch || '') + ' • Semester ' + (stu.semester_no || currentSemester));

    // Status Badge
    var badgeClass = 'badge-secondary';
    if (stu.status === 'Paid') {
        badgeClass = 'badge-success';
    } else if (stu.status === 'Partial') {
        badgeClass = 'badge-warning';
    } else if (stu.status === 'Unpaid') {
        badgeClass = 'badge-danger';
    }
    $('#collect_status_badge').attr('class', 'badge ' + badgeClass + ' px-2 py-1 font-weight-bold').text(stu.status || 'Unpaid');

    // Receipt No Preview
    var nextRcpt = data.next_receipt_no || 'RCPT-...';
    $('#lblCollectSummaryReceiptNo').text(nextRcpt);

    // Table rows: Allow collecting advance even if due <= 0, add delete fee head action button
    var tbHtml = '';
    if (heads.length === 0) {
        tbHtml = '<tr><td colspan="7" class="text-center py-4 text-muted align-middle">No fee heads assigned to this student for Semester ' + currentSemester + '.</td></tr>';
    } else {
        heads.forEach(function(h) {
            var isAdvance = (h.due < 0);
            var isCleared = (h.due === 0 && h.paid > 0);
            var isTuition = (h.head_name || '').toLowerCase().indexOf('tuition') !== -1 || (h.head_name || '').toLowerCase().indexOf('tution') !== -1;
            var canCollect = isTuition || (h.due > 0);

            tbHtml += '<tr data-assign-id="' + h.assignment_id + '">';
            
            // Checkbox: perfectly centered (disabled if non-tuition and no due)
            tbHtml += '<td class="col-head-cb text-center align-middle" style="padding: 6px 4px;">';
            if (isProgressed) {
                tbHtml += '  <i class="fa fa-lock text-muted" style="font-size: 11px;" title="Semester closed"></i>';
            } else if (!canCollect) {
                tbHtml += '  <input type="checkbox" disabled class="collect-head-cb" style="margin: 0; vertical-align: middle; width: 16px; height: 16px; opacity: 0.4;" title="No pending dues (Advance only allowed on Tuition Fee)">';
            } else {
                tbHtml += '  <input type="checkbox" class="collect-head-cb" onchange="onCollectHeadCheckChange(this)" style="margin: 0; vertical-align: middle; cursor: pointer; width: 16px; height: 16px;">';
            }
            tbHtml += '</td>';

            // Fee head
            tbHtml += '<td class="align-middle font-weight-bold text-dark" style="font-size: 12.5px; padding: 6px 10px;">' + escapeHtml(h.head_name) + '</td>';

            // Net Payable
            tbHtml += '<td class="text-right align-middle text-dark font-weight-bold" style="font-size: 12px; padding: 6px 10px;">₹' + numberFormat(h.net) + '</td>';

            // Paid earlier
            tbHtml += '<td class="text-right align-middle text-muted font-weight-bold" style="font-size: 12px; padding: 6px 10px;">₹' + numberFormat(h.paid) + '</td>';

            // Due Amount: Supports negative advance outstanding or transferred
            tbHtml += '<td class="text-right align-middle" style="padding: 6px 10px;">';
            if (isProgressed) {
                tbHtml += '<span class="text-muted font-weight-bold" style="font-size: 12px;">₹0</span> <small class="text-info d-block" style="font-size: 9px; line-height: 1;">Transferred</small>';
            } else if (isAdvance) {
                tbHtml += '<span class="text-success font-weight-bold" style="font-size: 12.5px;">-₹' + numberFormat(Math.abs(h.due)) + ' <small class="badge badge-success px-1" style="font-size: 8.5px;">Advance</small></span>';
            } else if (isCleared) {
                tbHtml += '<span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>Cleared</span>';
            } else {
                tbHtml += '<span class="text-danger font-weight-bold" style="font-size: 12.5px;">₹' + numberFormat(h.due) + '</span>';
            }
            tbHtml += '</td>';

            // Collect now input: Non-tuition heads cannot exceed their due; advance only on Tuition Fee!
            tbHtml += '<td class="col-head-collect text-right align-middle" style="padding: 5px 10px;">';
            if (isProgressed) {
                tbHtml += '<span class="text-muted font-weight-bold" style="font-size: 11px;">Closed</span>';
            } else if (!canCollect) {
                tbHtml += '  <div class="d-flex flex-column align-items-end justify-content-center">';
                tbHtml += '    <input type="number" step="0.01" min="0" max="0" disabled class="form-control form-control-sm text-right font-weight-bold collect-head-amt" ' +
                          'value="0" placeholder="0.00" data-due="' + h.due + '" data-assign-id="' + h.assignment_id + '" data-is-tuition="0" ' +
                          'style="width: 100px; height: 28px; padding: 2px 8px; font-size: 12px; border-color: #cbd5e1; background: #f8fafc; color: #94a3b8; margin: 0;">';
                tbHtml += '    <small class="collect-amt-note text-muted d-block mt-1 font-weight-bold" style="font-size: 9.5px; line-height: 1;">No advance allowed</small>';
                tbHtml += '  </div>';
            } else {
                var maxAttr = isTuition ? '' : ('max="' + h.due + '"');
                tbHtml += '  <div class="d-flex flex-column align-items-end justify-content-center">';
                tbHtml += '    <input type="number" step="0.01" min="0" ' + maxAttr + ' class="form-control form-control-sm text-right font-weight-bold collect-head-amt" ' +
                          'value="0" placeholder="0.00" data-due="' + h.due + '" data-assign-id="' + h.assignment_id + '" data-is-tuition="' + (isTuition ? '1' : '0') + '" ' +
                          'onclick="onCollectHeadInputInteraction(this)" onfocus="onCollectHeadInputInteraction(this)" oninput="onCollectHeadAmountChange(this)" ' +
                          'style="width: 100px; height: 28px; padding: 2px 8px; font-size: 12px; border-color: #cbd5e1; background: #ffffff; margin: 0;">';
                tbHtml += '    <small class="collect-amt-note text-muted d-block mt-1 font-weight-bold" style="font-size: 9.5px; line-height: 1;">Click to collect</small>';
                tbHtml += '  </div>';
            }
            tbHtml += '</td>';

            // Action: Delete Fee Head button (Only if paid <= 0, not progressed, and NOT core system head)
            var lowerHeadName = (h.head_name || '').toLowerCase();
            var isCoreHead = lowerHeadName.indexOf('tuition') !== -1 || lowerHeadName.indexOf('tution') !== -1 || lowerHeadName.indexOf('previous dues') !== -1 || lowerHeadName.indexOf('prev dues') !== -1 || lowerHeadName.indexOf('previous fee') !== -1 || lowerHeadName.indexOf('prev fee') !== -1;

            tbHtml += '<td class="text-center align-middle" style="padding: 5px 6px;">';
            if (isProgressed) {
                tbHtml += '<span class="text-muted" style="font-size: 11px;">&mdash;</span>';
            } else if (isCoreHead) {
                tbHtml += '  <span class="text-muted" title="System core fee head (' + escapeHtml(h.head_name) + ') - protected and cannot be deleted" style="font-size: 11px;">' +
                          '    <i class="fa fa-lock text-muted"></i>' +
                          '  </span>';
            } else if (h.paid > 0) {
                tbHtml += '  <span class="text-muted" title="Payment has been collected on this fee head (Locked - cannot be deleted)" style="font-size: 11px;">' +
                          '    <i class="fa fa-lock text-muted"></i>' +
                          '  </span>';
            } else {
                tbHtml += '  <button type="button" class="btn btn-xs btn-outline-danger p-1" style="line-height: 1; border-radius: 3px;" ' +
                          'onclick="confirmDeleteStudentHead(' + h.assignment_id + ', \'' + escapeHtml(h.head_name).replace(/'/g, "\\'") + '\')" ' +
                          'title="Delete ' + escapeHtml(h.head_name) + ' from student">' +
                          '    <i class="fa fa-trash"></i>' +
                          '  </button>';
            }
            tbHtml += '</td>';

            tbHtml += '</tr>';
        });
    }
    $('#tbodyCollectHeads').html(tbHtml);

    // Payment History Cards (Print, Download, Modify & Cancel)
    $('#lblCollectHistoryCount').text(receipts.length + ' receipt' + (receipts.length === 1 ? '' : 's'));
    var histHtml = '';
    if (receipts.length === 0) {
        histHtml = '<div class="text-center py-4 text-muted" style="font-size: 11.5px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px;">' +
                   '  <i class="fa fa-file-text-o fa-2x mb-2 text-muted"></i><br>No receipts recorded yet for this semester.' +
                   '</div>';
    } else {
        receipts.forEach(function(r) {
            var isCancelled = (r.is_cancelled === true);
            var headPills = '';
            if (r.items && r.items.length > 0) {
                r.items.forEach(function(it) {
                    var pillStyle = isCancelled ? 'style="font-size: 9.5px; padding: 2px 5px; text-decoration: line-through; opacity: 0.7;"' : 'style="font-size: 9.5px; padding: 2px 5px;"';
                    headPills += '<span class="badge badge-light border text-secondary font-weight-bold" ' + pillStyle + '>' + escapeHtml(it.head_name) + ' ₹' + numberFormat(it.amount) + '</span>';
                });
            }

            var isEditingThis = (activeEditingReceiptId === r.id);
            var editingCardClass = isEditingThis ? ' is-editing-receipt' : '';
            var editingBadge = isEditingThis 
                ? ' <span class="badge badge-warning text-dark font-weight-bold ml-1 badge-editing-indicator" style="font-size: 9px;"><i class="fa fa-spinner fa-spin mr-1"></i>Modifying</span>'
                : '';

            var cardStyle = isCancelled ? 'background: #fff5f5; border: 1.5px dashed #fca5a5;' : '';
            var statusBadge = isCancelled 
                ? ' <span class="badge badge-danger text-white ml-1 font-weight-bold" style="font-size: 9px; padding: 2px 6px; border-radius: 3px;"><i class="fa fa-ban mr-1"></i>CANCELLED</span>' 
                : '';
            var amtClass = isCancelled ? 'text-muted' : 'text-success';
            var amtStyle = isCancelled ? 'font-size: 12.5px; text-decoration: line-through;' : 'font-size: 12.5px;';

            histHtml += '<div id="receipt_card_' + r.id + '" class="collect-history-card p-2 mb-1 rounded' + editingCardClass + '" style="font-size: 11px; ' + cardStyle + '">';
            histHtml += '  <div class="d-flex align-items-center justify-content-between">';
            histHtml += '    <div>';
            histHtml += '      <span class="font-weight-bold text-dark">' + r.payment_date + '</span>';
            histHtml += '      <span class="text-muted mx-1">&bull;</span>';
            histHtml += '      <span class="badge badge-light border text-dark font-weight-bold receipt-no-badge" style="font-size: 10px;">' + r.receipt_no + '</span>' + editingBadge + statusBadge;
            histHtml += '      <span class="text-muted ml-1 font-weight-bold" style="font-size: 10px;">(' + escapeHtml(r.payment_mode) + ')</span>';
            histHtml += '    </div>';
            histHtml += '    <span class="font-weight-bold ' + amtClass + '" style="' + amtStyle + '">₹' + numberFormat(r.amount_paid) + '</span>';
            histHtml += '  </div>';

            if (isCancelled && r.cancelled_at) {
                histHtml += '  <div class="text-danger mt-1" style="font-size: 9.5px; font-weight: 600;"><i class="fa fa-ban mr-1"></i>Receipt cancelled on ' + r.cancelled_at + '</div>';
            }

            histHtml += '  <div class="d-flex align-items-center justify-content-between mt-1 pt-1" style="border-top: 1px dashed #e2e8f0;">';
            histHtml += '    <div class="d-flex flex-wrap" style="gap: 3px;">' + headPills + '</div>';
            histHtml += '    <div class="d-flex" style="gap: 3px;">';
            
            // Cancelled receipts CANNOT be edited or cancelled again!
            if (!isCancelled && !isProgressed) {
                histHtml += '      <button type="button" class="btn-collect-tool text-primary font-weight-bold" style="font-size: 10.5px; height: 22px; padding: 1px 6px;" onclick="startEditReceipt(' + r.id + ')" title="Modify receipt">';
                histHtml += '        <i class="fa fa-pencil text-primary mr-1"></i> Edit';
                histHtml += '      </button>';
                histHtml += '      <button type="button" class="btn-collect-tool text-danger font-weight-bold" style="font-size: 10.5px; height: 22px; padding: 1px 6px;" onclick="confirmCancelReceipt(' + r.id + ', \'' + r.receipt_no + '\', ' + r.amount_paid + ')" title="Cancel receipt & revert payments">';
                histHtml += '        <i class="fa fa-ban text-danger mr-1"></i> Cancel';
                histHtml += '      </button>';
            }

            // Print and Download are ALWAYS available (with watermark!)
            var printTitle = isCancelled ? 'Open & print cancelled receipt' : 'Open & print receipt in new tab';
            var downloadTitle = isCancelled ? 'Download cancelled receipt PDF' : 'Download PDF in new tab';
            var printBtnColor = isCancelled ? 'text-danger font-weight-bold' : 'text-success';

            histHtml += '      <a href="' + r.print_url + '" target="_blank" class="btn-collect-tool" style="font-size: 10.5px; height: 22px; padding: 1px 6px; text-decoration: none;" title="' + printTitle + '">';
            histHtml += '        <i class="fa fa-print mr-1 ' + printBtnColor + '"></i> Print';
            histHtml += '      </a>';
            histHtml += '      <a href="' + r.download_url + '" target="_blank" class="btn-collect-tool" style="font-size: 10.5px; height: 22px; padding: 1px 6px; text-decoration: none;" title="' + downloadTitle + '">';
            histHtml += '        <i class="fa fa-download mr-1 text-primary"></i> Download';
            histHtml += '      </a>';
            histHtml += '    </div>';
            histHtml += '  </div>';

            histHtml += '</div>';
        });
    }
    $('#collect_receipts_history_container').html(histHtml);

    // Dynamic Expand Button
    setTimeout(function() {
        var container = document.getElementById('collect_receipts_history_container');
        var expandWrapper = $('#collect_history_expand_wrapper');
        var expandBtn = $('#btnToggleHistoryExpand');
        if (container && container.scrollHeight > 185) {
            expandWrapper.show();
            expandBtn.html('<i class="fa fa-angle-down mr-1"></i> View All (' + receipts.length + ') Receipts');
            $('#collect_receipts_history_container').css({ 'max-height': '180px', 'overflow-y': 'hidden' });
        } else {
            expandWrapper.hide();
            $('#collect_receipts_history_container').css({ 'max-height': 'none', 'overflow-y': 'visible' });
        }
    }, 100);

    recalculateCollectTotals();
    applyCollectionModeToModal();
}

function toggleHistoryExpand() {
    var container = $('#collect_receipts_history_container');
    var btn = $('#btnToggleHistoryExpand');
    var totalCount = (activeCollectDataCache && activeCollectDataCache.receipts) ? activeCollectDataCache.receipts.length : 0;

    isHistoryExpanded = !isHistoryExpanded;
    if (isHistoryExpanded) {
        container.css({ 'max-height': '400px', 'overflow-y': 'auto' });
        btn.html('<i class="fa fa-angle-up mr-1"></i> Collapse Receipts');
    } else {
        container.css({ 'max-height': '180px', 'overflow-y': 'hidden' });
        btn.html('<i class="fa fa-angle-down mr-1"></i> View All (' + totalCount + ') Receipts');
    }
}

// Auto-select head when clicked
function onCollectHeadInputInteraction(input) {
    var tr = $(input).closest('tr');
    var cb = tr.find('.collect-head-cb');
    var dueVal = parseFloat($(input).data('due')) || 0;
    var curVal = parseFloat($(input).val()) || 0;

    if (!cb.is(':checked') && !cb.prop('disabled')) {
        cb.prop('checked', true);
    }

    if (curVal <= 0 && dueVal > 0) {
        $(input).val(dueVal);
        $(input).css({ 'background': '#f0fdf4', 'border-color': '#217346' });
        tr.find('.collect-amt-note').removeClass('text-warning text-muted text-info').addClass('text-success').html('<i class="fa fa-check-circle mr-1"></i>Full payment');
        recalculateCollectTotals();
    }

    $(input).select();
}

function onCollectHeadCheckChange(cb) {
    var tr = $(cb).closest('tr');
    var input = tr.find('.collect-head-amt');
    var dueVal = parseFloat(input.data('due')) || 0;

    if ($(cb).is(':checked')) {
        var autoFill = dueVal > 0 ? dueVal : 0;
        input.val(autoFill).css({ 'background': '#f0fdf4', 'border-color': '#217346' });
        if (dueVal > 0) {
            tr.find('.collect-amt-note').removeClass('text-warning text-muted text-info').addClass('text-success').html('<i class="fa fa-check-circle mr-1"></i>Full payment');
        } else {
            tr.find('.collect-amt-note').removeClass('text-warning text-success text-info').addClass('text-muted').text('Enter amount');
        }
    } else {
        input.val('0').css({ 'background': '#ffffff', 'border-color': '#cbd5e1' });
        tr.find('.collect-amt-note').removeClass('text-success text-warning text-info').addClass('text-muted').text('Click to collect');
    }
    recalculateCollectTotals();
}

// Amount Change: Advance is strictly allowed on Tuition Fee only!
function onCollectHeadAmountChange(input) {
    var tr = $(input).closest('tr');
    var cb = tr.find('.collect-head-cb');
    var dueVal = parseFloat($(input).data('due')) || 0;
    var val = parseFloat($(input).val()) || 0;
    var headName = tr.find('td:nth-child(2)').text().trim().toLowerCase();
    var isTuition = headName.indexOf('tuition') !== -1 || headName.indexOf('tution') !== -1;

    if (val < 0) {
        val = 0;
        $(input).val(0);
    }

    // USER RULE: Advance cannot be collected on Previous Dues or non-tuition heads!
    if (!isTuition && dueVal <= 0 && val > 0) {
        val = 0;
        $(input).val(0);
        cb.prop('checked', false);
        showCommonToast('warning', 'Advance amount can only be collected under Tuition Fee, not under ' + tr.find('td:nth-child(2)').text().trim() + '.');
        recalculateCollectTotals();
        return;
    }

    if (!isTuition && dueVal > 0 && val > dueVal) {
        val = dueVal;
        $(input).val(dueVal);
        showCommonToast('warning', 'Advance amount is not allowed on ' + tr.find('td:nth-child(2)').text().trim() + '. Capped to pending due ₹' + numberFormat(dueVal));
    }

    var noteEl = tr.find('.collect-amt-note');
    if (val > 0) {
        if (!cb.is(':checked') && !cb.prop('disabled')) {
            cb.prop('checked', true);
        }

        if (val > dueVal && dueVal > 0) {
            var advance = val - dueVal;
            $(input).css({ 'background': '#f0fdf4', 'border-color': '#16a34a' });
            noteEl.removeClass('text-warning text-muted text-success').addClass('text-info font-weight-bold')
                .html('<i class="fa fa-plus-circle mr-1"></i>Advance ₹' + numberFormat(advance));
        } else if (val === dueVal) {
            $(input).css({ 'background': '#f0fdf4', 'border-color': '#217346' });
            noteEl.removeClass('text-warning text-muted text-info').addClass('text-success')
                .html('<i class="fa fa-check-circle mr-1"></i>Full payment');
        } else if (dueVal <= 0) {
            $(input).css({ 'background': '#f0fdf4', 'border-color': '#16a34a' });
            noteEl.removeClass('text-warning text-muted text-success').addClass('text-info font-weight-bold')
                .html('<i class="fa fa-plus-circle mr-1"></i>Advance Fee ₹' + numberFormat(val));
        } else {
            var remaining = dueVal - val;
            $(input).css({ 'background': '#fffbeb', 'border-color': '#f59e0b' });
            noteEl.removeClass('text-success text-muted text-info').addClass('text-warning')
                .html('<i class="fa fa-clock-o mr-1"></i>Part payment · Due ₹' + numberFormat(remaining));
        }
    } else {
        cb.prop('checked', false);
        $(input).css({ 'background': '#ffffff', 'border-color': '#cbd5e1' });
        noteEl.removeClass('text-success text-warning text-info font-weight-bold').addClass('text-muted')
            .text('Click to collect');
    }

    recalculateCollectTotals();
}

function collectSelectAllDue(selectAll) {
    $('#tbodyCollectHeads tr').each(function() {
        var cb = $(this).find('.collect-head-cb');
        if (cb.length > 0 && !cb.prop('disabled')) {
            cb.prop('checked', selectAll);
            var input = $(this).find('.collect-head-amt');
            var dueVal = parseFloat(input.data('due')) || 0;
            if (selectAll && dueVal > 0) {
                input.val(dueVal).css({ 'background': '#f0fdf4', 'border-color': '#217346' });
                $(this).find('.collect-amt-note').removeClass('text-warning text-muted text-info').addClass('text-success').html('<i class="fa fa-check-circle mr-1"></i>Full payment');
            } else {
                input.val('0').css({ 'background': '#ffffff', 'border-color': '#cbd5e1' });
                $(this).find('.collect-amt-note').removeClass('text-success text-warning text-info').addClass('text-muted').text('Click to collect');
            }
        }
    });
    recalculateCollectTotals();
}

function collectSelectTuitionOnly() {
    $('#tbodyCollectHeads tr').each(function() {
        var cb = $(this).find('.collect-head-cb');
        var headName = $(this).find('td:nth-child(2)').text().toLowerCase();
        var isTuition = (headName.indexOf('tuition') !== -1);
        var input = $(this).find('.collect-head-amt');
        var dueVal = parseFloat(input.data('due')) || 0;

        if (isTuition && cb.length > 0 && !cb.prop('disabled')) {
            cb.prop('checked', true);
            var autoFill = dueVal > 0 ? dueVal : 0;
            input.val(autoFill).css({ 'background': '#f0fdf4', 'border-color': '#217346' });
            $(this).find('.collect-amt-note').removeClass('text-warning text-muted text-info').addClass('text-success').html('<i class="fa fa-check-circle mr-1"></i>Full payment');
        } else if (cb.length > 0 && !cb.prop('disabled')) {
            cb.prop('checked', false);
            input.val('0').css({ 'background': '#ffffff', 'border-color': '#cbd5e1' });
            $(this).find('.collect-amt-note').removeClass('text-success text-warning text-info').addClass('text-muted').text('Click to collect');
        }
    });
    recalculateCollectTotals();
}

function setCollectPaymentMode(btn, mode) {
    $('.collect-mode-btn').removeClass('active');
    $(btn).addClass('active');
    $('#input_collect_mode').val(mode);
}

// Split payment mode helpers
function toggleSplitPaymentMode() {
    isSplitPaymentActive = !isSplitPaymentActive;
    if (isSplitPaymentActive) {
        $('#collect_single_mode_panel').hide();
        $('#collect_split_mode_panel').slideDown(200);
        $('#lblToggleSplitText').html('&larr; Single Mode');

        if (splitPaymentRows.length === 0) {
            var totalNow = getCurrentlyCollectingTotal();
            var half1 = Math.round((totalNow / 2) * 100) / 100;
            var half2 = Math.round((totalNow - half1) * 100) / 100;

            splitPaymentRows = [
                { id: 1, mode: 'UPI', amount: half1 > 0 ? half1 : '', ref: '' },
                { id: 2, mode: 'Cash', amount: half2 > 0 ? half2 : '', ref: '' }
            ];
        }
        renderSplitPaymentRows();
    } else {
        $('#collect_split_mode_panel').hide();
        $('#collect_single_mode_panel').slideDown(200);
        $('#lblToggleSplitText').text('+ Split Mode (Multi)');
    }
    recalculateCollectTotals();
}

function renderSplitPaymentRows() {
    var container = $('#split_mode_rows_container');
    container.empty();

    splitPaymentRows.forEach(function(row, idx) {
        var rowHtml = '<div class="split-mode-row d-flex align-items-center mb-2" data-row-idx="' + idx + '" style="gap: 8px;">';
        
        rowHtml += '<div style="width: 140px; min-width: 130px;">';
        rowHtml += '  <select class="form-control form-control-sm font-weight-bold split-mode-select" onchange="onSplitRowModeChange(' + idx + ', this.value)">';
        ['UPI', 'Cash', 'Card', 'Cheque', 'Bank Transfer', 'Demand Draft'].forEach(function(m) {
            var sel = (row.mode === m) ? ' selected' : '';
            rowHtml += '<option value="' + m + '"' + sel + '>' + m + '</option>';
        });
        rowHtml += '  </select>';
        rowHtml += '</div>';

        rowHtml += '<div style="width: 130px;">';
        rowHtml += '  <div class="input-group input-group-sm">';
        rowHtml += '    <div class="input-group-prepend"><span class="input-group-text font-weight-bold" style="background: #f1f5f9;">₹</span></div>';
        rowHtml += '    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right font-weight-bold split-amt-input" ' +
                   'value="' + (row.amount !== '' ? row.amount : '') + '" placeholder="0.00" oninput="onSplitRowAmountChange(' + idx + ', this.value)">';
        rowHtml += '  </div>';
        rowHtml += '</div>';

        rowHtml += '<div class="flex-grow-1">';
        rowHtml += '  <input type="text" class="form-control form-control-sm font-weight-bold split-ref-input" ' +
                   'value="' + (row.ref ? escapeHtml(row.ref) : '') + '" placeholder="Txn ID / Cheque No / Remarks" oninput="onSplitRowRefChange(' + idx + ', this.value)">';
        rowHtml += '</div>';

        if (splitPaymentRows.length > 1) {
            rowHtml += '<div>';
            rowHtml += '  <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeSplitPaymentRow(' + idx + ')" title="Remove split row" style="height: 28px; width: 28px; padding: 0;">';
            rowHtml += '    <i class="fa fa-times"></i>';
            rowHtml += '  </button>';
            rowHtml += '</div>';
        }

        rowHtml += '</div>';
        container.append(rowHtml);
    });

    recalculateSplitTotals();
}

function addSplitPaymentRow() {
    var nextMode = 'Cash';
    var existingModes = splitPaymentRows.map(function(r) { return r.mode; });
    ['Card', 'Cheque', 'Bank Transfer', 'Cash', 'UPI'].forEach(function(m) {
        if (existingModes.indexOf(m) === -1) {
            nextMode = m;
        }
    });

    var totalNow = getCurrentlyCollectingTotal();
    var currentSum = 0;
    splitPaymentRows.forEach(function(r) { currentSum += (parseFloat(r.amount) || 0); });
    var rem = Math.max(0, Math.round((totalNow - currentSum) * 100) / 100);

    splitPaymentRows.push({
        id: Date.now(),
        mode: nextMode,
        amount: rem > 0 ? rem : '',
        ref: ''
    });
    renderSplitPaymentRows();
}

function removeSplitPaymentRow(idx) {
    if (splitPaymentRows.length > 1) {
        splitPaymentRows.splice(idx, 1);
        renderSplitPaymentRows();
    }
}

function onSplitRowModeChange(idx, val) {
    if (splitPaymentRows[idx]) {
        splitPaymentRows[idx].mode = val;
    }
}

function onSplitRowAmountChange(idx, val) {
    if (splitPaymentRows[idx]) {
        splitPaymentRows[idx].amount = val;
        recalculateSplitTotals();
    }
}

function onSplitRowRefChange(idx, val) {
    if (splitPaymentRows[idx]) {
        splitPaymentRows[idx].ref = val;
    }
}

function recalculateSplitTotals() {
    var totalNow = getCurrentlyCollectingTotal();
    var allocated = 0;
    splitPaymentRows.forEach(function(r) {
        allocated += (parseFloat(r.amount) || 0);
    });

    $('#lblSplitAllocated').text('₹' + numberFormat(allocated));
    $('#lblSplitTarget').text('₹' + numberFormat(totalNow));

    var diff = Math.abs(totalNow - allocated);
    if (diff < 0.01 && totalNow > 0) {
        $('#split_summary_status').html('<span class="text-success"><i class="fa fa-check-circle mr-1"></i>Split Matched (₹' + numberFormat(allocated) + ' / ₹' + numberFormat(totalNow) + ')</span>');
    } else if (allocated < totalNow) {
        var rem = totalNow - allocated;
        $('#split_summary_status').html('<span class="text-warning"><i class="fa fa-clock-o mr-1"></i>₹' + numberFormat(rem) + ' unallocated (Total ₹' + numberFormat(totalNow) + ')</span>');
    } else {
        var over = allocated - totalNow;
        $('#split_summary_status').html('<span class="text-danger"><i class="fa fa-exclamation-triangle mr-1"></i>₹' + numberFormat(over) + ' over-allocated</span>');
    }
}

// ========================================================
// COLLECTION MODE CONTROLLER: TOTAL DUES VS HEAD-WISE
// ========================================================
var pendingSwitchTargetMode = null;

function getCollectionMode() {
    return localStorage.getItem('college_fee_collect_mode') || 'head_wise';
}

function setCollectionMode(mode) {
    localStorage.setItem('college_fee_collect_mode', mode);
}

function updateCollectionModeHeaderUI() {
    var btn = $('#btnHeaderToggleCollectMode');
    if (!btn.length) return;
    var mode = getCollectionMode();
    var btn = $('#btnHeaderToggleCollectMode');
    var icon = $('#iconHeaderCollectMode');
    var txt = $('#lblHeaderCollectModeText');
    var badge = $('#badgeHeaderCollectMode');

    if (mode === 'total_dues') {
        btn.addClass('active-total-mode');
        icon.attr('class', 'fa fa-toggle-on text-success mr-1');
        txt.text('Collection: Total Dues');
        badge.attr('class', 'badge badge-success ml-1').css({ 'font-size': '9.5px', 'padding': '2px 5px' }).text('ON');
    } else {
        btn.removeClass('active-total-mode');
        icon.attr('class', 'fa fa-toggle-off text-muted mr-1');
        txt.text('Collection: Head-wise');
        badge.attr('class', 'badge badge-secondary ml-1').css({ 'font-size': '9.5px', 'padding': '2px 5px' }).text('OFF');
    }
}

function promptSwitchCollectionMode() {
    var currentMode = getCollectionMode();
    pendingSwitchTargetMode = (currentMode === 'total_dues') ? 'head_wise' : 'total_dues';

    if (pendingSwitchTargetMode === 'total_dues') {
        $('#colModeModalTitle').html('<i class="fa fa-calculator text-success mr-2"></i> Switch to Total Dues Collection Mode');
        $('#colModeModalIconWrapper').css({ 'background': '#e7f5ec' });
        $('#colModeModalIcon').attr('class', 'fa fa-calculator text-success');
        $('#colModeModalHeading').text('Enable Total Dues Collection Mode?');
        $('#colModeModalSubheading').text('Collect Now fields will be hidden. Payment will be collected against overall outstanding balance.');
        $('#colModeModalCurrent').text('Itemized (Head-wise)');
        $('#colModeModalTarget').text('Total Dues (Consolidated)').attr('class', 'text-success font-weight-bold');
        $('#colModeModalFieldBehavior').text('Hidden (Auto Total Allocation)').attr('class', 'text-success font-weight-bold');
        $('#colModeModalInfoAlert').attr('class', 'alert py-2 px-3 mb-0').css({ 'background': '#f0fdf4', 'border': '1px solid #86efac', 'color': '#166534' });
        $('#colModeModalAlertIcon').attr('class', 'fa fa-calculator mr-1 text-success');
        $('#colModeModalNoteText').html('In <strong>Total Dues Mode</strong>, individual head inputs are hidden. Only the overall outstanding balance across all heads combined will be collected.');
        $('#btnConfirmSwitchColMode').attr('class', 'btn btn-search-action px-3')
            .html('<i class="fa fa-check mr-1"></i> Confirm & Enable Total Dues Mode');
    } else {
        $('#colModeModalTitle').html('<i class="fa fa-list-ul text-primary mr-2"></i> Switch to Head-wise Collection Mode');
        $('#colModeModalIconWrapper').css({ 'background': '#e0f2fe' });
        $('#colModeModalIcon').attr('class', 'fa fa-list-ul text-primary');
        $('#colModeModalHeading').text('Enable Head-wise (Itemized) Collection Mode?');
        $('#colModeModalSubheading').text('Collect Now column will be shown so you can enter amounts for specific fee heads.');
        $('#colModeModalCurrent').text('Total Dues (Consolidated)');
        $('#colModeModalTarget').text('Itemized (Head-wise)').attr('class', 'text-primary font-weight-bold');
        $('#colModeModalFieldBehavior').text('Visible (Manual Per-Head Entry)').attr('class', 'text-primary font-weight-bold');
        $('#colModeModalInfoAlert').attr('class', 'alert py-2 px-3 mb-0').css({ 'background': '#e7eff5', 'border': '1px solid #b8c9d9', 'color': '#1e3a5f' });
        $('#colModeModalAlertIcon').attr('class', 'fa fa-info-circle mr-1 text-primary');
        $('#colModeModalNoteText').html('In <strong>Head-wise Mode</strong>, cashier can select individual fee heads (Tuition, Exam, etc.) and enter customized amounts per head.');
        $('#btnConfirmSwitchColMode').attr('class', 'btn btn-primary px-3 font-weight-bold')
            .html('<i class="fa fa-check mr-1"></i> Confirm & Enable Head-wise Mode');
    }

    $('#modalConfirmCollectionMode').modal('show');
}

function applyCollectionModeSwitch() {
    if (!pendingSwitchTargetMode) return;
    setCollectionMode(pendingSwitchTargetMode);
    $('#modalConfirmCollectionMode').modal('hide');

    updateCollectionModeHeaderUI();

    if ($('#modalCollectFee').hasClass('show')) {
        applyCollectionModeToModal();
    }

    var label = (pendingSwitchTargetMode === 'total_dues') ? 'Total Dues Mode' : 'Head-wise Mode';
    showCommonToast('success', 'Fee collection mode switched to ' + label + ' successfully!');
}

function applyCollectionModeToModal() {
    if (activeCollectDataCache && activeCollectDataCache.student && activeCollectDataCache.student.is_progressed) {
        $('.col-head-cb').hide();
        $('.col-head-collect').hide();
        $('#collect_tbl_foot_label').attr('colspan', '1');
        $('#collect_quick_tools_wrapper').hide();
        $('#badgeTotalModeActivePill').hide();
        $('#collect_modal_mode_banner').hide();
        $('#collect_action_summary_box').hide();
        $('#collect_payment_mode_container').hide();
        $('#collect_progressed_info_box').show();
        recalculateCollectTotals();
        return;
    }

    var isTotalMode = (getCollectionMode() === 'total_dues');

    if (isTotalMode) {
        $('.col-head-cb').hide();
        $('.col-head-collect').hide();
        $('#collect_tbl_foot_label').attr('colspan', '1');

        $('#collect_quick_tools_wrapper').hide();
        $('#badgeTotalModeActivePill').show();

        var totalDue = 0;
        if (activeCollectDataCache && activeCollectDataCache.totals) {
            totalDue = parseFloat(activeCollectDataCache.totals.due) || 0;
        }

        $('#collect_modal_mode_banner').removeClass('alert-info').addClass('alert-success')
            .css({ 'background': '#f0fdf4', 'border-color': '#86efac', 'color': '#166534' });
        $('#collect_modal_mode_banner i').attr('class', 'fa fa-calculator mr-2 fa-lg text-success');
        $('#collect_modal_mode_banner_text').html(
            '<strong>Total Dues Collection Mode:</strong> ' +
            'Individual head entry is hidden. Collecting against student\'s total outstanding balance across all heads. ' +
            '<a href="javascript:void(0)" onclick="promptSwitchCollectionMode()" class="text-success font-weight-bold ml-1" style="text-decoration: underline;">Switch to Head-wise</a>'
        );

        $('#collect_total_mode_input_wrapper').show();
        var defaultCollect = Math.max(0, totalDue);
        $('#input_collect_total_mode_amt').val(defaultCollect > 0 ? defaultCollect : 0);
    } else {
        $('.col-head-cb').show();
        $('.col-head-collect').show();
        $('#collect_tbl_foot_label').attr('colspan', '2');

        $('#collect_quick_tools_wrapper').show();
        $('#badgeTotalModeActivePill').hide();

        $('#collect_modal_mode_banner').removeClass('alert-success').addClass('alert-info')
            .css({ 'background': '#e7eff5', 'border-color': '#b8c9d9', 'color': '#1e3a5f' });
        $('#collect_modal_mode_banner i').attr('class', 'fa fa-info-circle mr-2 fa-lg text-primary');
        $('#collect_modal_mode_banner_text').html(
            '<strong>Partial / Itemized Payment Mode:</strong> ' +
            'Click any fee head\'s collect box to select it. Enter the partial or full amount being collected today. ' +
            '<a href="javascript:void(0)" onclick="promptSwitchCollectionMode()" class="text-primary font-weight-bold ml-1" style="text-decoration: underline;">Switch to Total Dues</a>'
        );

        $('#collect_total_mode_input_wrapper').hide();
    }

    recalculateCollectTotals();
}

function onTotalModeCollectAmtChange(val) {
    recalculateCollectTotals();
}

function resetTotalModeToFullDue() {
    var totalDue = 0;
    if (activeCollectDataCache && activeCollectDataCache.totals) {
        totalDue = parseFloat(activeCollectDataCache.totals.due) || 0;
    }
    var defaultCollect = Math.max(0, totalDue);
    $('#input_collect_total_mode_amt').val(defaultCollect > 0 ? defaultCollect : 0);
    recalculateCollectTotals();
}

function allocateTotalAcrossHeads(heads, totalAmount) {
    var allocations = [];
    var remaining = totalAmount;

    // Filter positive due heads (Tuition, Exam, Previous Dues, etc.)
    var positiveHeads = heads.filter(function(h) {
        return (parseFloat(h.due) || 0) > 0;
    });

    // Fallback if no positive heads: find tuition head
    if (positiveHeads.length === 0 && heads.length > 0) {
        var tHead = heads.find(function(h) {
            var n = (h.head_name || '').toLowerCase();
            return n.indexOf('tuition') !== -1 || n.indexOf('tution') !== -1;
        }) || heads[0];
        positiveHeads = [tHead];
    }

    positiveHeads.forEach(function(h) {
        if (remaining <= 0) return;
        var due = Math.max(0, parseFloat(h.due) || 0);
        var isTuition = (h.head_name || '').toLowerCase().indexOf('tuition') !== -1 || (h.head_name || '').toLowerCase().indexOf('tution') !== -1;
        // Non-tuition heads (Previous Dues, etc.) can never take more than their outstanding due!
        var alloc = (!isTuition) ? Math.min(due, remaining) : ((positiveHeads.length === 1 && remaining > due) ? remaining : Math.min(due, remaining));
        if (alloc > 0) {
            allocations.push({
                assignment_id: h.assignment_id,
                amount: Math.round(alloc * 100) / 100
            });
            remaining -= alloc;
        }
    });

    // If remaining amount (advance excess paid beyond all positive dues)
    // USER RULE: Advance amount ONLY belongs to Tuition Fee head, NEVER Previous Dues or other heads!
    if (remaining > 0.009) {
        var tuitionHead = heads.find(function(h) {
            var n = (h.head_name || '').toLowerCase();
            return n.indexOf('tuition') !== -1 || n.indexOf('tution') !== -1;
        });

        if (tuitionHead) {
            var existingAlloc = allocations.find(function(a) { return a.assignment_id === tuitionHead.assignment_id; });
            if (existingAlloc) {
                existingAlloc.amount = Math.round((existingAlloc.amount + remaining) * 100) / 100;
            } else {
                allocations.push({
                    assignment_id: tuitionHead.assignment_id,
                    amount: Math.round(remaining * 100) / 100
                });
            }
        } else if (allocations.length > 0) {
            allocations[0].amount = Math.round((allocations[0].amount + remaining) * 100) / 100;
        }
        remaining = 0;
    }

    return allocations;
}

$(document).on('hidden.bs.modal', '#modalConfirmCollectionMode', function () {
    if ($('#modalCollectFee').hasClass('show')) {
        $('body').addClass('modal-open');
    }
});

function getCurrentlyCollectingTotal() {
    var totalNow = 0;
    if (getCollectionMode() === 'total_dues') {
        totalNow = parseFloat($('#input_collect_total_mode_amt').val()) || 0;
    } else {
        $('#tbodyCollectHeads tr').each(function() {
            var cb = $(this).find('.collect-head-cb');
            if (cb.length > 0 && cb.is(':checked')) {
                var amt = parseFloat($(this).find('.collect-head-amt').val()) || 0;
                totalNow += amt;
            }
        });
    }
    return totalNow;
}

// Recalculates totals & allows negative balance if paying advance
function recalculateCollectTotals() {
    var totalNet = 0;
    var totalPaid = 0;
    var totalDue = 0;
    var totalNow = 0;

    if (activeCollectDataCache && activeCollectDataCache.totals) {
        totalNet = parseFloat(activeCollectDataCache.totals.net) || 0;
        totalPaid = parseFloat(activeCollectDataCache.totals.paid) || 0;
        totalDue = parseFloat(activeCollectDataCache.totals.due) || 0;
    }

    if (activeCollectDataCache && activeCollectDataCache.student && activeCollectDataCache.student.is_progressed) {
        $('#collect_tbl_total_net').text('₹' + numberFormat(totalNet));
        $('#collect_tbl_total_paid').text('₹' + numberFormat(totalPaid));
        $('#collect_tbl_total_due').html('<span class="text-muted font-weight-bold">₹0</span> <small class="text-info d-block" style="font-size: 9px; line-height: 1;">Transferred</small>');
        $('#collect_tbl_total_now').text('—');
        return;
    }

    totalNow = getCurrentlyCollectingTotal();
    var balanceAfter = totalDue - totalNow;

    $('#collect_tbl_total_net').text('₹' + numberFormat(totalNet));
    $('#collect_tbl_total_paid').text('₹' + numberFormat(totalPaid));

    if (totalDue < 0) {
        $('#collect_tbl_total_due').html('<span class="text-success font-weight-bold">-₹' + numberFormat(Math.abs(totalDue)) + ' (Adv)</span>');
        $('#lblCollectSummaryTotalDue').html('<span class="text-success font-weight-bold">-₹' + numberFormat(Math.abs(totalDue)) + ' (Advance)</span>');
    } else {
        $('#collect_tbl_total_due').html('<span class="text-danger font-weight-bold">₹' + numberFormat(totalDue) + '</span>');
        $('#lblCollectSummaryTotalDue').text('₹' + numberFormat(totalDue));
    }

    $('#collect_tbl_total_now').text('₹' + numberFormat(totalNow));
    $('#lblCollectSummaryNow').text('₹' + numberFormat(totalNow));

    if (balanceAfter < 0) {
        $('#lblCollectSummaryBalance').removeClass('text-danger text-dark').addClass('text-success font-weight-bold')
            .text('-₹' + numberFormat(Math.abs(balanceAfter)) + ' (Advance)');
    } else if (balanceAfter === 0) {
        $('#lblCollectSummaryBalance').removeClass('text-danger text-success').addClass('text-dark font-weight-bold')
            .text('₹0.00 (Cleared)');
    } else {
        $('#lblCollectSummaryBalance').removeClass('text-success text-dark').addClass('text-danger font-weight-bold')
            .text('₹' + numberFormat(balanceAfter));
    }

    $('#btnCollectLabelAmt').text('₹' + numberFormat(totalNow));

    if (isSplitPaymentActive) {
        recalculateSplitTotals();
    }
}

// Delete Fee Head with Confirmation Modal Alert (Only uncollected fee heads)
function confirmDeleteStudentHead(assignId, headName) {
    showFeeActionConfirm({
        title: '<i class="fa fa-trash text-danger mr-1"></i> Delete Fee Head',
        message: "Are you sure you want to remove fee head <strong>" + escapeHtml(headName) + "</strong> from this student's fee structure?",
        subnote: "The student's net payable and outstanding balances will be recalculated.",
        type: 'danger',
        icon: 'fa fa-trash',
        btnText: 'Yes, Delete Head',
        onConfirm: function() {
            $('#matrixLoadingOverlay').show();
            $.ajax({
                url: "{{ route('college.fees.remove_student_head') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    assignment_id: assignId
                },
                dataType: 'json',
                success: function(res) {
                    $('#matrixLoadingOverlay').hide();
                    showCommonToast('success', 'Fee head deleted successfully.');
                    openCollectFeeModal(activeCollectStudentId);
                    loadLedgerMatrix();
                },
                error: function(xhr) {
                    $('#matrixLoadingOverlay').hide();
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete fee head.';
                    showCommonToast('error', msg);
                }
            });
        }
    });
}

// Cancel Receipt with Confirmation Modal Alert
function confirmCancelReceipt(receiptId, receiptNo, amount) {
    showFeeActionConfirm({
        title: '<i class="fa fa-ban text-danger mr-1"></i> Cancel Fee Receipt',
        message: 'Are you sure you want to <strong>CANCEL Receipt ' + escapeHtml(receiptNo) + '</strong> (₹' + numberFormat(amount) + ')?',
        subnote: "This will reverse all payments allocated by this receipt and recalculate the student's outstanding fees.",
        type: 'danger',
        icon: 'fa fa-ban',
        btnText: 'Yes, Cancel Receipt',
        onConfirm: function() {
            $('#matrixLoadingOverlay').show();
            $.ajax({
                url: "{{ route('college.fees.cancel_receipt') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    receipt_id: receiptId
                },
                dataType: 'json',
                success: function(res) {
                    $('#matrixLoadingOverlay').hide();
                    showCommonToast('success', res.message || 'Receipt cancelled successfully.');
                    if (activeEditingReceiptId === receiptId) {
                        exitReceiptEditMode();
                    }
                    openCollectFeeModal(activeCollectStudentId);
                    loadLedgerMatrix();
                },
                error: function(xhr) {
                    $('#matrixLoadingOverlay').hide();
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to cancel receipt.';
                    showCommonToast('error', msg);
                }
            });
        }
    });
}

// Start Edit Mode for an Existing Receipt
function startEditReceipt(receiptId) {
    $('#matrixLoadingOverlay').show();
    $.ajax({
        url: "{{ url('college/fees/receipt') }}/" + receiptId + "/edit-data",
        type: "GET",
        dataType: 'json',
        success: function(res) {
            $('#matrixLoadingOverlay').hide();
            if (res.status === 'success') {
                var r = res.receipt;
                activeEditingReceiptId = r.id;

                $('#lblEditReceiptNo').text(r.receipt_no);
                $('#lblEditReceiptOrigAmt').text('₹' + numberFormat(r.amount_paid));
                $('#receipt_edit_mode_bar').addClass('is-active');

                // Animate card infinite border & add modifying badge
                $('.collect-history-card').removeClass('is-editing-receipt');
                $('.badge-editing-indicator').remove();
                var currentCard = $('#receipt_card_' + r.id);
                currentCard.addClass('is-editing-receipt');
                currentCard.find('.receipt-no-badge').after(' <span class="badge badge-warning text-dark font-weight-bold ml-1 badge-editing-indicator" style="font-size: 9px;"><i class="fa fa-spinner fa-spin mr-1"></i>Modifying</span>');

                $('#input_collect_date').val(r.payment_date);
                $('#input_collect_ref').val(r.transaction_id || '');

                var pMode = r.payment_mode || 'Cash';
                if (pMode.indexOf('Split:') !== -1) {
                    if (!isSplitPaymentActive) {
                        toggleSplitPaymentMode();
                    }
                    $('#input_collect_split_remark').val(r.remark || '');
                    $('#input_collect_split_date').val(r.payment_date);
                } else {
                    if (isSplitPaymentActive) {
                        toggleSplitPaymentMode();
                    }
                    $('.collect-mode-btn').removeClass('active');
                    $('.collect-mode-btn').each(function() {
                        if ($(this).text().trim().toLowerCase() === pMode.toLowerCase()) {
                            $(this).addClass('active');
                        }
                    });
                    $('#input_collect_mode').val(pMode);
                }

                var itemMap = {};
                if (r.items) {
                    r.items.forEach(function(it) {
                        if (it.assignment_id) {
                            itemMap[it.assignment_id] = it.amount;
                        }
                    });
                }

                $('#tbodyCollectHeads tr').each(function() {
                    var assignId = parseInt($(this).data('assign-id'));
                    var input = $(this).find('.collect-head-amt');
                    var cb = $(this).find('.collect-head-cb');
                    var noteEl = $(this).find('.collect-amt-note');

                    if (itemMap.hasOwnProperty(assignId)) {
                        var allocAmt = itemMap[assignId];
                        cb.prop('checked', true);
                        input.val(allocAmt).css({ 'background': '#f0fdf4', 'border-color': '#217346' });
                        noteEl.removeClass('text-warning text-muted').addClass('text-success').html('<i class="fa fa-pencil mr-1"></i>Allocated in receipt');
                    } else {
                        cb.prop('checked', false);
                        input.val('0').css({ 'background': '#ffffff', 'border-color': '#cbd5e1' });
                        noteEl.removeClass('text-success text-warning').addClass('text-muted').text('Click to collect');
                    }
                });

                $('#btnSubmitCollectPayment').html('<i class="fa fa-save mr-1"></i> Update Receipt (' + r.receipt_no + ') & Recalculate')
                    .removeClass('btn-collect-submit').addClass('btn btn-warning font-weight-bold text-dark')
                    .css({ 'background': '#f59e0b', 'border-color': '#d97706', 'color': '#1e293b' });

                recalculateCollectTotals();
                showCommonToast('info', 'Loaded receipt ' + r.receipt_no + ' for modification.');
            }
        },
        error: function(xhr) {
            $('#matrixLoadingOverlay').hide();
            showCommonToast('error', 'Failed to load receipt details for modification.');
        }
    });
}

function exitReceiptEditMode() {
    activeEditingReceiptId = null;
    $('#receipt_edit_mode_bar').removeClass('is-active');
    $('.collect-history-card').removeClass('is-editing-receipt');
    $('.badge-editing-indicator').remove();
    $('#btnSubmitCollectPayment').html('<i class="fa fa-check mr-1"></i> Collect <span id="btnCollectLabelAmt" class="mx-1">₹0</span> &amp; Receipt')
        .removeClass('btn btn-warning').addClass('btn-collect-submit')
        .css({ 'background': '', 'border-color': '', 'color': '' });

    $('#tbodyCollectHeads tr').each(function() {
        $(this).find('.collect-head-cb').prop('checked', false);
        $(this).find('.collect-head-amt').val('0').css({ 'background': '#ffffff', 'border-color': '#cbd5e1' });
        $(this).find('.collect-amt-note').removeClass('text-success text-warning text-info font-weight-bold').addClass('text-muted').text('Click to collect');
    });
    recalculateCollectTotals();
}

// Submit Payment or Submit Receipt Update with Confirmation Modal Alert
function submitCollectPayment() {
    var isTotalMode = (getCollectionMode() === 'total_dues');
    var selectedHeads = [];
    var totalCollecting = 0;

    if (isTotalMode) {
        totalCollecting = parseFloat($('#input_collect_total_mode_amt').val()) || 0;
        if (totalCollecting <= 0) {
            showCommonToast('error', 'Please enter a valid total amount to collect greater than zero.');
            $('#input_collect_total_mode_amt').focus();
            return;
        }

        var availableHeads = (activeCollectDataCache && activeCollectDataCache.heads) ? activeCollectDataCache.heads : [];
        if (availableHeads.length === 0) {
            showCommonToast('error', 'No fee heads assigned to this student to allocate payment.');
            return;
        }

        selectedHeads = allocateTotalAcrossHeads(availableHeads, totalCollecting);
        if (selectedHeads.length === 0) {
            showCommonToast('error', 'Unable to allocate amount across fee heads.');
            return;
        }
    } else {
        $('#tbodyCollectHeads tr').each(function() {
            var cb = $(this).find('.collect-head-cb');
            if (cb.length > 0 && cb.is(':checked')) {
                var assignId = parseInt($(this).data('assign-id'));
                var amt = parseFloat($(this).find('.collect-head-amt').val()) || 0;
                if (assignId > 0 && amt > 0) {
                    selectedHeads.push({
                        assignment_id: assignId,
                        amount: amt
                    });
                    totalCollecting += amt;
                }
            }
        });

        if (selectedHeads.length === 0 || totalCollecting <= 0) {
            showCommonToast('error', 'Please select at least one fee head with an amount greater than 0.');
            return;
        }
    }

    var paymentMode = 'Cash';
    var refNo = '';
    var paymentDate = "{{ date('Y-m-d') }}";
    var payloadSplits = [];

    if (isSplitPaymentActive) {
        var splitSum = 0;
        splitPaymentRows.forEach(function(r) {
            var a = parseFloat(r.amount) || 0;
            if (a > 0) {
                splitSum += a;
                payloadSplits.push({
                    mode: r.mode,
                    amount: a,
                    ref: r.ref || ''
                });
            }
        });

        if (Math.abs(splitSum - totalCollecting) > 0.01) {
            showCommonToast('error', 'Split amounts sum (₹' + numberFormat(splitSum) + ') must exactly match Total Collecting (₹' + numberFormat(totalCollecting) + ')!');
            return;
        }

        paymentDate = $('#input_collect_split_date').val() || "{{ date('Y-m-d') }}";
        refNo = $.trim($('#input_collect_split_remark').val());
        paymentMode = 'Split';
    } else {
        paymentMode = $('#input_collect_mode').val() || 'Cash';
        refNo = $.trim($('#input_collect_ref').val());
        paymentDate = $('#input_collect_date').val() || "{{ date('Y-m-d') }}";
    }

    var stuName = $('#collect_student_name').text().trim() || 'Student';

    // Check if we are UPDATING an existing receipt or COLLECTING a new one
    if (activeEditingReceiptId) {
        var rNo = $('#lblEditReceiptNo').text().trim() || 'Receipt';
        showFeeActionConfirm({
            title: '<i class="fa fa-save text-warning mr-1"></i> Confirm Receipt Update',
            message: 'Are you sure you want to <strong>UPDATE ' + escapeHtml(rNo) + '</strong> with new total <strong>₹' + numberFormat(totalCollecting) + '</strong> (' + escapeHtml(paymentMode) + ')?',
            subnote: 'Previous payment allocations will be replaced and student outstanding dues will be recalculated immediately.',
            type: 'warning',
            icon: 'fa fa-save',
            btnText: 'Yes, Update & Recalculate',
            onConfirm: function() {
                var btn = $('#btnSubmitCollectPayment');
                var origBtnHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Updating...');

                $.ajax({
                    url: "{{ route('college.fees.update_receipt') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        receipt_id: activeEditingReceiptId,
                        payment_mode: paymentMode,
                        payment_date: paymentDate,
                        transaction_id: refNo,
                        heads: selectedHeads,
                        payment_splits: payloadSplits
                    },
                    dataType: 'json',
                    success: function(res) {
                        btn.prop('disabled', false).html(origBtnHtml);
                        if (res.status === 'success') {
                            showCommonToast('success', res.message || 'Receipt updated successfully!');
                            exitReceiptEditMode();
                            openCollectFeeModal(activeCollectStudentId);
                            loadLedgerMatrix();
                        } else {
                            showCommonToast('error', res.message || 'Receipt update failed.');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(origBtnHtml);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Receipt update failed.';
                        showCommonToast('error', msg);
                    }
                });
            }
        });
    } else {
        // NEW Collection with Confirmation Modal Alert
        showFeeActionConfirm({
            title: '<i class="fa fa-check-circle text-success mr-1"></i> Confirm Fee Collection',
            message: 'Collect <strong>₹' + numberFormat(totalCollecting) + '</strong> for <strong>' + escapeHtml(stuName) + '</strong> via <strong>' + escapeHtml(paymentMode) + '</strong>?',
            subnote: 'A sequential fee receipt will be recorded and student ledger updated immediately.',
            type: 'success',
            icon: 'fa fa-check-circle',
            btnText: 'Yes, Collect & Generate Receipt',
            onConfirm: function() {
                var btn = $('#btnSubmitCollectPayment');
                var origBtnHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Processing...');

                $.ajax({
                    url: "{{ route('college.fees.collect_payment') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        student_id: activeCollectStudentId,
                        semester_no: currentSemester,
                        payment_mode: paymentMode,
                        payment_date: paymentDate,
                        transaction_id: refNo,
                        heads: selectedHeads,
                        payment_splits: payloadSplits
                    },
                    dataType: 'json',
                    success: function(res) {
                        btn.prop('disabled', false).html(origBtnHtml);
                        if (res.status === 'success') {
                            showCommonToast('success', res.message || 'Payment collected successfully!');
                            $('#modalCollectFee').modal('hide');
                            if (res.print_url) {
                                window.open(res.print_url, '_blank');
                            }
                            loadLedgerMatrix();
                        } else {
                            showCommonToast('error', res.message || 'Payment collection failed.');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(origBtnHtml);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Payment collection failed.';
                        showCommonToast('error', msg);
                    }
                });
            }
        });
    }
}

function numberFormat(val) {
    return (parseFloat(val) || 0).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

// ========================================================

function getSelectedStudentIds() {

    var ids = [];

    $('.student-select-cb:checked').each(function() {

        ids.push($(this).val());

    });

    return ids;

}



function onStudentSelectChange() {

    var totalVisible = $('.student-select-cb:visible').length;

    if (totalVisible === 0) {

        totalVisible = $('.student-select-cb').length;

    }

    var selectedIds = getSelectedStudentIds();

    var count = selectedIds.length;



    $('#txtSelectedCount').text(count);

    $('#btnQuickAssignCount').text(count);



    var master = $('#masterStudentCheckbox')[0];

    if (master) {

        if (count === 0) {

            master.checked = false;

            master.indeterminate = false;

        } else if (count === totalVisible) {

            master.checked = true;

            master.indeterminate = false;

        } else {

            master.checked = false;

            master.indeterminate = true;

        }

    }

}



function toggleMasterStudentCheckbox(master) {

    var isChecked = master.checked;

    master.indeterminate = false;

    $('.student-select-cb').prop('checked', isChecked);

    onStudentSelectChange();

}



function selectAllStudents(check) {

    $('.student-select-cb').prop('checked', check);

    var master = $('#masterStudentCheckbox')[0];

    if (master) {

        master.checked = check;

        master.indeterminate = false;

    }

    onStudentSelectChange();

}



// ========================================================

// QUICK ASSIGN FEES (TO SELECTED STUDENTS)

// ========================================================

var commonHeadsLibraryCache = [];

var qaActiveSelectedHeads = {}; // Map: head_name -> { head_name, fee_type, amount_boys, amount_girls }



function openQuickAssignFeesModal() {

    var selectedIds = getSelectedStudentIds();

    if (selectedIds.length === 0) {

        showCommonToast('error', 'Please select at least one student using the checkboxes first.');

        return;

    }



    $('#lblQuickAssignSelectedCount').text(selectedIds.length + ' Students Selected');

    $('#qaModalSelectedCount').text(selectedIds.length + ' Students');

    $('#qaSelectedStudentsCountBottom').text(selectedIds.length);

    $('#qaModalCourseLabel').text($('#selLedgerCourse option:selected').text());

    $('#qaModalBatchLabel').text(currentBatch);

    $('#qaModalSemLabel').text(currentSemester);



    // Reset create panel

    $('#panelQuickCreateHead').hide();



    // Initialize active selected heads cleanly so user can choose which head(s) to assign

    qaActiveSelectedHeads = {};



    // Reset assignment preference to default (Only unassigned)

    $('input[name="qa_assign_mode"][value="unassigned"]').prop('checked', true);



    // Load Common Heads from backend and render

    loadCommonHeadsForQuickAssign();



    $('#modalQuickAssignFees').modal('show');

}



function toggleQuickCreateHeadPanel() {

    $('#panelQuickCreateHead').slideToggle(200);

}



function loadCommonHeadsForQuickAssign() {

    $('#qaQuickButtonsContainer').html('<div class="text-muted p-2" style="font-size: 11px;"><i class="fa fa-spinner fa-spin mr-1 text-success"></i> Loading common fee heads...</div>');



    $.ajax({

        url: "{{ route('college.fees.structures.common_heads') }}",

        type: "GET",

        dataType: 'json',

        success: function(res) {

            commonHeadsLibraryCache = res.common_heads || [];

            renderQaQuickButtons();

            renderQaSelectedHeadsTable();

        },

        error: function() {

            $('#qaQuickButtonsContainer').html('<div class="text-danger p-2" style="font-size: 11px;">Failed to load fee heads library.</div>');

            renderQaSelectedHeadsTable();

        }

    });

}



function renderQaQuickButtons() {

    var container = $('#qaQuickButtonsContainer');

    container.empty();



    if (commonHeadsLibraryCache.length === 0) {

        container.html('<div class="text-muted p-2" style="font-size: 11px;"><i class="fa fa-info-circle mr-1"></i> No fee heads found in library. Use the "+ Create New Common Fee Head" button above.</div>');

        return;

    }



    var visibleCount = 0;
    commonHeadsLibraryCache.forEach(function(h) {

        var lowerH = (h.head_name || '').toLowerCase().trim();
        var isTuition = lowerH.indexOf('tuition') !== -1 || lowerH.indexOf('tution') !== -1;
        var isPrevDues = lowerH.indexOf('previous dues') !== -1 || lowerH.indexOf('prev dues') !== -1 || lowerH.indexOf('previous fee') !== -1 || lowerH.indexOf('prev fee') !== -1 || lowerH.indexOf('previous') !== -1 || !!h.is_previous_dues;
        if (isTuition || isPrevDues) {
            return; // Never show Tuition Fees or Previous Dues in Quick Assign
        }

        visibleCount++;
        var isSelected = qaActiveSelectedHeads.hasOwnProperty(h.head_name);

        var activeClass = isSelected ? ' active' : '';

        var iconClass = isSelected ? 'fa-check-circle' : 'fa-plus-circle';



        var btn = $('<button type="button" class="btn-qa-head-chip' + activeClass + '"></button>');

        btn.attr('data-head-name', h.head_name);

        btn.attr('title', isSelected ? 'Click to deselect ' + h.head_name : 'Click to select ' + h.head_name);



        btn.html(

            '<i class="fa ' + iconClass + ' chip-icon"></i>' +

            '<span>' + $('<div>').text(h.head_name).html() + '</span>' +

            '<span class="chip-type">(' + $('<div>').text(h.fee_type || 'Optional').html() + ')</span>'

        );



        container.append(btn);

    });

    if (visibleCount === 0) {
        container.html('<div class="text-muted p-2" style="font-size: 11px;"><i class="fa fa-info-circle mr-1"></i> No other fee heads available in library. Use the "+ Create New Common Fee Head" button above to add custom fee heads.</div>');
    }

}



function toggleQaHeadSelection(headName) {

    var lowerH = (headName || '').toLowerCase().trim();
    if (lowerH.indexOf('tuition') !== -1 || lowerH.indexOf('tution') !== -1 || lowerH.indexOf('previous dues') !== -1 || lowerH.indexOf('prev dues') !== -1 || lowerH.indexOf('previous fee') !== -1 || lowerH.indexOf('prev fee') !== -1 || lowerH.indexOf('previous') !== -1) {
        return; // Guard: Tuition Fees and Previous Dues cannot be selected in Quick Assign
    }

    if (qaActiveSelectedHeads[headName]) {

        delete qaActiveSelectedHeads[headName];

    } else {

        var boysVal = 0;

        var girlsVal = 0;

        var feeType = 'Tuition';



        if (ledgerDataCache && ledgerDataCache.standard_heads) {

            var std = ledgerDataCache.standard_heads.find(function(s) { return s.head_name === headName; });

            if (std) {

                boysVal = parseFloat(std.amount_boys) || 0;

                girlsVal = parseFloat(std.amount_girls) || 0;

                feeType = std.fee_type || 'Tuition';

            }

        }



        if (!feeType || feeType === 'Tuition') {

            var lib = commonHeadsLibraryCache.find(function(l) { return l.head_name === headName; });

            if (lib && lib.fee_type) {

                feeType = lib.fee_type;

            }

        }



        qaActiveSelectedHeads[headName] = {

            head_name: headName,

            fee_type: feeType,

            amount_boys: boysVal,

            amount_girls: girlsVal

        };

    }



    renderQaQuickButtons();

    renderQaSelectedHeadsTable();

}



function renderQaSelectedHeadsTable() {

    var tbody = $('#qaSelectedHeadsTbody');

    tbody.empty();



    var headKeys = Object.keys(qaActiveSelectedHeads);

    $('#qaSelectedHeadsCountBadge').text(headKeys.length);



    if (headKeys.length === 0) {

        tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fa fa-hand-pointer mr-1 text-success"></i> No fee heads selected. Click any quick button above to select fee heads.</td></tr>');

        updateQaSummaryTotals();

        return;

    }



    var idx = 1;

    headKeys.forEach(function(key) {

        var item = qaActiveSelectedHeads[key];

        var boysVal = (item.amount_boys > 0) ? item.amount_boys : '';

        var girlsVal = (item.amount_girls > 0) ? item.amount_girls : '';



        var row = $('<tr class="qa-selected-row"></tr>');

        row.attr('data-head-name', item.head_name);



        var tdIndex = $('<td class="text-center text-muted font-weight-bold align-middle"></td>').text(idx++);

        var tdName = $('<td class="align-middle"></td>').html('<strong class="text-dark">' + $('<div>').text(item.head_name).html() + '</strong>');

        var tdType = $('<td class="align-middle"></td>').html('<span class="badge badge-light border text-muted" style="font-size: 10px;">' + $('<div>').text(item.fee_type || 'Tuition').html() + '</span>');

        

        var tdBoys = $('<td class="text-right align-middle"></td>').html(

            '<input type="number" step="0.01" min="0" class="toolbar-control qa-selected-input-boys text-right font-weight-bold" value="' + boysVal + '" placeholder="0.00">'

        );

        var tdGirls = $('<td class="text-right align-middle"></td>').html(

            '<input type="number" step="0.01" min="0" class="toolbar-control qa-selected-input-girls text-right font-weight-bold" value="' + girlsVal + '" placeholder="0.00">'

        );

        var tdRemove = $('<td class="text-center align-middle"></td>').html(

            '<button type="button" class="btn btn-xs btn-outline-danger btn-qa-remove-head" data-head-name="' + $('<div>').text(item.head_name).html() + '" title="Remove from selection"><i class="fa fa-times"></i></button>'

        );



        row.append(tdIndex, tdName, tdType, tdBoys, tdGirls, tdRemove);

        tbody.append(row);

    });



    updateQaSummaryTotals();

}



function onQaAmountChange(headName, gender, val) {

    if (qaActiveSelectedHeads[headName]) {

        var num = parseFloat(val) || 0;

        if (gender === 'boys') {

            qaActiveSelectedHeads[headName].amount_boys = num;

        } else {

            qaActiveSelectedHeads[headName].amount_girls = num;

        }

        updateQaSummaryTotals();

    }

}



function updateQaSummaryTotals() {

    var boysSum = 0;

    var girlsSum = 0;

    Object.keys(qaActiveSelectedHeads).forEach(function(key) {

        var item = qaActiveSelectedHeads[key];

        boysSum += parseFloat(item.amount_boys) || 0;

        girlsSum += parseFloat(item.amount_girls) || 0;

    });



    $('#qaSummaryBoysTotal').text('₹' + boysSum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    $('#qaSummaryGirlsTotal').text('₹' + girlsSum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

}



function removeQaSelectedHead(headName) {

    if (qaActiveSelectedHeads[headName]) {

        delete qaActiveSelectedHeads[headName];

        renderQaQuickButtons();

        renderQaSelectedHeadsTable();

    }

}



function clearAllSelectedHeads() {

    qaActiveSelectedHeads = {};

    renderQaQuickButtons();

    renderQaSelectedHeadsTable();

}



// Create New Head Inline inside Quick Assign modal

function submitQuickCreateHead() {

    var headName = $.trim($('#qa_new_head_name').val());

    var feeType = $('#qa_new_fee_type').val() || 'Tuition';

    var boysAmt = parseFloat($('#qa_new_boys_amt').val()) || 0;

    var girlsAmt = parseFloat($('#qa_new_girls_amt').val()) || 0;



    if (!headName) {
        showCommonToast('error', 'Please enter a fee head name.');
        return;
    }

    var lowerH = headName.toLowerCase();
    if (lowerH.indexOf('tuition') !== -1 || lowerH.indexOf('tution') !== -1 || lowerH.indexOf('previous dues') !== -1 || lowerH.indexOf('prev dues') !== -1 || lowerH.indexOf('previous fee') !== -1 || lowerH.indexOf('prev fee') !== -1) {
        showCommonToast('error', 'Tuition Fees and Previous Dues are protected system core fee heads and cannot be modified or re-created.');
        return;
    }



    var btn = $('#btnSubmitQuickCreateHead');

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');



    $.ajax({

        url: "{{ route('college.fees.structures.save_common') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            head_name: headName,

            group_type: feeType,

            description: ''

        },

        dataType: 'json',

        success: function(res) {

            btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');

            showCommonToast('success', res.message || 'Fee head created.');



            var newHead = {

                id: res.head ? res.head.id : Date.now(),

                head_name: headName,

                fee_type: feeType

            };

            

            var exists = commonHeadsLibraryCache.some(function(h) { return h.head_name === headName; });

            if (!exists) {

                commonHeadsLibraryCache.unshift(newHead);

            }



            // Automatically select the new head with entered amounts

            qaActiveSelectedHeads[headName] = {

                head_name: headName,

                fee_type: feeType,

                amount_boys: boysAmt,

                amount_girls: girlsAmt

            };



            // Re-render

            renderQaQuickButtons();

            renderQaSelectedHeadsTable();



            // Clear inputs & slide up panel

            $('#qa_new_head_name').val('');

            $('#qa_new_boys_amt').val('');

            $('#qa_new_girls_amt').val('');

            $('#panelQuickCreateHead').slideUp(200);

        },

        error: function(xhr) {

            btn.prop('disabled', false).html('<i class="fa fa-plus"></i>');

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to create fee head.';

            showCommonToast('error', msg);

        }

    });

}



// Global cached payload for confirmation modal
var pendingQuickAssignPayload = null;

// Step 1: Open Confirmation Modal upon clicking "Assign to Selected Students"
function submitQuickAssignFees() {
    var selectedStudentIds = getSelectedStudentIds();
    if (selectedStudentIds.length === 0) {
        showCommonToast('error', 'Please select at least one student.');
        return;
    }

    var headKeys = Object.keys(qaActiveSelectedHeads);
    if (headKeys.length === 0) {
        showCommonToast('error', 'Please select at least one fee head to assign.');
        return;
    }

    var selectedHeads = [];
    headKeys.forEach(function(k) {
        var h = qaActiveSelectedHeads[k];
        selectedHeads.push({
            head_name: h.head_name,
            amount_boys: parseFloat(h.amount_boys) || 0,
            amount_girls: parseFloat(h.amount_girls) || 0
        });
    });

    var assignMode = $('input[name="qa_assign_mode"]:checked').val() || 'unassigned';

    // Store payload for confirmation step
    pendingQuickAssignPayload = {
        _token: "{{ csrf_token() }}",
        course_id: currentCourseId,
        batch: currentBatch,
        semester_no: currentSemester,
        student_ids: selectedStudentIds,
        heads: selectedHeads,
        assign_mode: assignMode
    };

    // Populate confirmation modal fields
    var courseName = $('#selLedgerCourse option:selected').text() || 'Course';
    $('#qaConfirmCourseBatchSem').text(courseName + ' • Batch ' + currentBatch + ' • Sem ' + currentSemester);
    $('#qaConfirmStudentCount').text(selectedStudentIds.length + ' Student(s)');

    if (assignMode === 'overwrite') {
        $('#qaConfirmModeBadge').html('<span class="badge badge-warning text-dark font-weight-bold" style="font-size: 11px;"><i class="fa fa-refresh mr-1"></i> Overwrite Already Assigned</span>');
        $('#qaConfirmInfoAlert').attr('class', 'alert py-2 px-3 mb-0 alert-warning').css({ 'background': '#fffbeb', 'border': '1px solid #fde68a', 'color': '#92400e' });
        $('#qaConfirmAlertIcon').attr('class', 'fa fa-exclamation-triangle mr-1 text-warning');
        $('#qaConfirmAlertText').html('<strong>Attention:</strong> Existing fee head amounts on already assigned students will be overwritten/updated to the new values.');
    } else {
        $('#qaConfirmModeBadge').html('<span class="badge badge-info font-weight-bold" style="font-size: 11px;"><i class="fa fa-user-plus mr-1"></i> Only Unassigned Students</span>');
        $('#qaConfirmInfoAlert').attr('class', 'alert py-2 px-3 mb-0 alert-info').css({ 'background': '#e7eff5', 'border': '1px solid #b8c9d9', 'color': '#1e3a5f' });
        $('#qaConfirmAlertIcon').attr('class', 'fa fa-info-circle mr-1 text-primary');
        $('#qaConfirmAlertText').html('Only students without these fee head(s) will be assigned. Students who already have them will be safely skipped.');
    }

    // Build head preview list
    var headsHtml = '';
    selectedHeads.forEach(function(h, idx) {
        var borderClass = (idx < selectedHeads.length - 1) ? ' border-bottom' : '';
        headsHtml += '<div class="d-flex justify-content-between align-items-center py-1' + borderClass + '" style="font-size: 11.5px;">' +
            '<span><i class="fa fa-tag text-success mr-1"></i> <strong>' + $('<div>').text(h.head_name).html() + '</strong></span>' +
            '<span class="text-muted">Boys: <strong class="text-dark">₹' + (h.amount_boys || 0).toFixed(2) + '</strong> | Girls: <strong class="text-dark">₹' + (h.amount_girls || 0).toFixed(2) + '</strong></span>' +
        '</div>';
    });
    $('#qaConfirmHeadsList').html(headsHtml);

    // Reset button in confirmation modal
    $('#btnConfirmExecuteQuickAssign').prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm &amp; Assign Fees');
    $('#btnCancelExecuteQuickAssign').prop('disabled', false);

    // Open confirmation modal
    $('#modalConfirmQuickAssign').modal('show');
}

// Step 2: Actually execute the quick assignment after confirmation
function executeQuickAssignFees() {
    if (!pendingQuickAssignPayload) {
        showCommonToast('error', 'Assignment session expired. Please try again.');
        $('#modalConfirmQuickAssign').modal('hide');
        return;
    }

    var btnConfirm = $('#btnConfirmExecuteQuickAssign');
    var btnCancel = $('#btnCancelExecuteQuickAssign');

    btnConfirm.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Assigning Fees...');
    btnCancel.prop('disabled', true);

    $.ajax({
        url: "{{ route('college.fees.assign_heads_to_selected') }}",
        type: "POST",
        data: pendingQuickAssignPayload,
        dataType: 'json',
        success: function(res) {
            btnConfirm.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm &amp; Assign Fees');
            btnCancel.prop('disabled', false);

            $('#modalConfirmQuickAssign').modal('hide');
            $('#modalQuickAssignFees').modal('hide');
            pendingQuickAssignPayload = null;

            showCommonToast('success', res.message || 'Fee heads assigned successfully.');
            loadLedgerMatrix();
        },
        error: function(xhr) {
            btnConfirm.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Confirm &amp; Assign Fees');
            btnCancel.prop('disabled', false);

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to assign fee heads.';
            showCommonToast('error', msg);
        }
    });
}

// Restore scroll on parent modal when confirmation modal is hidden
$(document).on('hidden.bs.modal', '#modalConfirmQuickAssign', function () {
    if ($('#modalQuickAssignFees').hasClass('show')) {
        $('body').addClass('modal-open');
    }
});



// Event Delegation for Quick Assign elements

$(document).on('click', '.btn-qa-head-chip', function() {

    var headName = $(this).data('head-name');

    toggleQaHeadSelection(headName);

});



$(document).on('click', '.btn-qa-remove-head', function() {

    var headName = $(this).data('head-name');

    removeQaSelectedHead(headName);

});



$(document).on('input', '.qa-selected-input-boys', function() {

    var headName = $(this).closest('tr').data('head-name');

    onQaAmountChange(headName, 'boys', $(this).val());

});



$(document).on('input', '.qa-selected-input-girls', function() {

    var headName = $(this).closest('tr').data('head-name');

    onQaAmountChange(headName, 'girls', $(this).val());

});



// Open Set / Edit Batch Standard Fees Modal (Semester Tuition Fee Only)

function openBatchFeesModal() {

    $('#lblBatchFeesCourse').text($('#selLedgerCourse option:selected').text());

    $('#lblBatchFeesSem').text(currentSemester);

    $('#lblBatchFeesBatch').text(currentBatch);



    var existingHeads = (ledgerDataCache && ledgerDataCache.standard_heads) ? ledgerDataCache.standard_heads : [];

    // Strictly find Tuition Fee head only

    var tuitionHead = existingHeads.find(function(h) {

        var n = (h.head_name || '').toLowerCase().trim();

        return n === 'tuition fee' || n.indexOf('tuition') !== -1;

    });



    var boysVal = tuitionHead ? (parseFloat(tuitionHead.amount_boys) || 0) : 0;

    var girlsVal = tuitionHead ? (parseFloat(tuitionHead.amount_girls) || 0) : 0;



    $('#batch_tuition_boys').val(boysVal > 0 ? boysVal : '');

    $('#batch_tuition_girls').val(girlsVal > 0 ? girlsVal : '');



    // Check if Tuition Fee has already been assigned to students

    var assignedCount = 0;

    if (ledgerDataCache && ledgerDataCache.kpi) {

        assignedCount = ledgerDataCache.kpi.tuition_assigned_count || ledgerDataCache.kpi.assigned_count || 0;

    }



    var isLocked = (assignedCount > 0 && tuitionHead);



    if (isLocked) {

        $('#batchFeeLockedAlert').show();

        $('#batchFeeInfoAlert').hide();

        $('#lblBatchFeesAssignedCount').text(assignedCount);

        $('#batch_tuition_boys').prop('disabled', true).addClass('bg-light text-muted');

        $('#batch_tuition_girls').prop('disabled', true).addClass('bg-light text-muted');

        $('#cellBatchTuitionStatus').html('<span class="badge badge-warning text-dark"><i class="fa fa-lock mr-1"></i> Locked</span>');

        $('#panelBatchAutoApply').hide();

        $('#btnSaveBatchFeesConfig').prop('disabled', true)

            .removeClass('btn-search-action')

            .addClass('btn-secondary')

            .html('<i class="fa fa-lock mr-1"></i> Tuition Fee Locked (Assigned)');

    } else {

        $('#batchFeeLockedAlert').hide();

        $('#batchFeeInfoAlert').show();

        $('#batch_tuition_boys').prop('disabled', false).removeClass('bg-light text-muted');

        $('#batch_tuition_girls').prop('disabled', false).removeClass('bg-light text-muted');

        $('#cellBatchTuitionStatus').html('<span class="badge badge-success"><i class="fa fa-unlock mr-1"></i> Editable</span>');

        $('#panelBatchAutoApply').show();

        $('#btnSaveBatchFeesConfig').prop('disabled', false)

            .removeClass('btn-secondary')

            .addClass('btn-search-action')

            .html('<i class="fa fa-check-circle mr-1"></i> Save & Apply Tuition Fee');

    }



    $('#modalBatchFees').modal('show');

}



// Submit Batch Tuition Fee

function submitBatchStandardFees() {

    var boysAmt = parseFloat($('#batch_tuition_boys').val()) || 0;

    var girlsAmt = parseFloat($('#batch_tuition_girls').val()) || 0;



    if (boysAmt <= 0 && girlsAmt <= 0) {

        showCommonToast('error', 'Please enter a valid Tuition Fee amount.');

        return;

    }



    var autoApply = $('#chkAutoApplyBatchFees').is(':checked') ? 1 : 0;

    var btn = $('#btnSaveBatchFeesConfig');

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');



    $.ajax({

        url: "{{ route('college.fees.save_batch_standard_fees') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            course_id: currentCourseId,

            batch: currentBatch,

            semester_no: currentSemester,

            heads: [

                {

                    head_name: 'Tuition Fee',

                    amount_boys: boysAmt,

                    amount_girls: girlsAmt

                }

            ],

            auto_apply: autoApply

        },

        dataType: 'json',

        success: function(res) {

            btn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Save & Apply Tuition Fee');

            $('#modalBatchFees').modal('hide');

            showCommonToast('success', res.message || 'Tuition fee saved successfully.');

            loadLedgerMatrix();

        },

        error: function(xhr) {

            btn.prop('disabled', false).html('<i class="fa fa-check-circle mr-1"></i> Save & Apply Tuition Fee');

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to save tuition fee.';

            showCommonToast('error', msg);

        }

    });

}



// Quick Assign Standard Fees to All Unassigned Students

function quickAssignAllRemaining() {

    if (!ledgerDataCache || !ledgerDataCache.standard_heads || ledgerDataCache.standard_heads.length === 0) {

        showCommonToast('error', 'Please configure Batch Standard Fees first using [Set / edit batch fees].');

        openBatchFeesModal();

        return;

    }



    if (!confirm('Auto-assign standard fee heads to all unassigned students in this batch and semester?')) {

        return;

    }



    $('#matrixLoadingOverlay').show();



    $.ajax({

        url: "{{ route('college.fees.save_batch_standard_fees') }}",

        type: "POST",

        data: {

            _token: "{{ csrf_token() }}",

            course_id: currentCourseId,

            batch: currentBatch,

            semester_no: currentSemester,

            heads: ledgerDataCache.standard_heads,

            auto_apply: 1

        },

        dataType: 'json',

        success: function(res) {

            $('#matrixLoadingOverlay').hide();

            showCommonToast('success', res.message || 'Standard fees assigned successfully.');

            loadLedgerMatrix();

        },

        error: function(xhr) {

            $('#matrixLoadingOverlay').hide();

            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Quick assignment failed.';

            showCommonToast('error', msg);

        }

    });

}



// Export Matrix Table to CSV

function exportMatrixTable() {

    if (!ledgerDataCache || !ledgerDataCache.rows) {

        showCommonToast('error', 'No data to export.');

        return;

    }



    var csv = [];

    var stdHeads = ledgerDataCache.standard_heads || [];

    

    // Header

    var headerRow = ['Roll No', 'Student Name', 'Gender'];

    stdHeads.forEach(function(h) { headerRow.push(h.head_name); });

    headerRow.push('Gross', 'Discount', 'Discount Reason', 'Net', 'Paid', 'Outstanding', 'Status');

    csv.push(headerRow.join(','));



    // Rows

    ledgerDataCache.rows.forEach(function(r) {

        var row = [

            '"' + r.roll_no + '"',

            '"' + r.name + '"',

            r.gender

        ];

        stdHeads.forEach(function(h) {

            var v = r.head_values[h.head_name];

            row.push(v ? v.amount : 0);

        });

        var dueAmt = Math.round((parseFloat(r.net) - parseFloat(r.paid)) * 100) / 100;
        row.push(r.gross, r.discount, '"' + (r.discount_reason || '') + '"', r.net, r.paid, dueAmt, r.status);

        csv.push(row.join(','));

    });



    var csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");

    var encodedUri = encodeURI(csvContent);

    var link = document.createElement("a");

    link.setAttribute("href", encodedUri);

    link.setAttribute("download", "Fee_Ledger_" + $('#selLedgerCourse option:selected').text().replace(/\s+/g, '_') + "_Sem" + currentSemester + ".csv");

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);

}



// Helper: Escape Special Characters in JavaScript strings

function addslashes(str) {

    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');

}

</script>



@endsection

