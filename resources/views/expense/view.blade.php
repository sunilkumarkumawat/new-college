@php
    $getPermission = Helper::getPermission();
    $roleId = Session::get('role_id') ?? 1;
    $canAdd = ($roleId == 1) || (!empty($getPermission) && (is_array($getPermission) ? ($getPermission['add'] ?? 0) : ($getPermission->add ?? 0)) == 1);
    
    $totalCount = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->total() : count($data);
    $firstItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->firstItem() ?? 0) : 1;
    $lastItem = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->lastItem() ?? 0) : count($data);
    $perPageValue = $search['per_page'] ?? '25';
@endphp
@extends('layout.app')
@section('content')

<style>
/* ========================================================
   EXPENSE REGISTER MASTER - STREAMLINED EXCEL GREEN THEME
   (Matching 1:1 with https://girraj.test/viewUser layout)
   ======================================================== */
:root {
    --green-excel: #217346;
    --green-excel-dark: #185c37;
    --green-excel-light: #eef6f0;
    --green-excel-border: #8eb99b;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
}

.content-wrapper {
    background-color: #f4f6f9;
}

/* Card & Titlebar */
.admission-card {
    border: 1px solid #9aa7b1 !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 2px !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

.admission-titlebar {
    min-height: 42px;
    padding: 6px 12px;
    background: var(--green-excel) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}

.admission-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.admission-title small {
    font-size: 11.5px;
    font-weight: 400;
    color: #e2f0d9;
    margin-left: 6px;
}

.admission-titlebar .title-actions {
    display: flex;
    gap: 5px;
    align-items: center;
    flex-wrap: wrap;
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
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.btn-header-action:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.btn-header-action.btn-header-add {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
    font-weight: 700 !important;
}
.btn-header-action.btn-header-add:hover {
    background: #dcfce7 !important;
    color: #14532d !important;
}

/* Action Icons */
.btn-row-action {
    width: 24px;
    height: 24px;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 3px !important;
    font-size: 11px !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    border: 1px solid transparent !important;
}
.btn-row-action:hover { transform: scale(1.08); }
.btn-action-view { background: #f0fdf4 !important; border-color: #bbf7d0 !important; color: #166534 !important; }
.btn-action-view:hover { background: #166534 !important; color: #ffffff !important; }
.btn-action-edit { background: #eff6ff !important; border-color: #bfdbfe !important; color: #2563eb !important; }
.btn-action-edit:hover { background: #2563eb !important; color: #ffffff !important; }
.btn-action-delete { background: #fff1f2 !important; border-color: #fecdd3 !important; color: #e11d48 !important; }
.btn-action-delete:hover { background: #e11d48 !important; color: #ffffff !important; }

/* Table & Sticky Filter Headers */
.admission-scroll {
    height: calc(100vh - 180px);
    min-height: 400px;
    overflow: auto;
    background: #ffffff;
    border: 1px solid #b8c8d5 !important;
    position: relative;
}

.admission-table {
    margin: 0 !important;
    width: 100%;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11.5px;
    border-top: 1px solid #b8c8d5 !important;
    border-left: 1px solid #b8c8d5 !important;
}

.admission-table tbody tr {
    height: 32px !important;
}

.admission-table th,
.admission-table td {
    padding: 3px 6px !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 1px solid #b8c8d5 !important;
    vertical-align: middle !important;
    box-sizing: border-box;
    white-space: nowrap;
}

.admission-table thead tr:first-child th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11.5px;
    text-align: center;
    height: 32px !important;
    line-height: 22px !important;
    border-top: 0 !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
}

.admission-table thead tr.admission-filter-row th {
    position: sticky;
    top: 32px;
    z-index: 6;
    background: var(--green-excel-light) !important;
    color: #1e462d !important;
    padding: 3px !important;
    height: 33px !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 2px solid var(--green-excel-border) !important;
}

.admission-table thead tr.admission-filter-row .form-control {
    height: 26px !important;
    min-height: 26px !important;
    padding: 1px 4px !important;
    border: 1px solid #9fb3c4 !important;
    border-radius: 2px !important;
    background: #ffffff !important;
    font-size: 11px !important;
    color: #0f172a !important;
    width: 100%;
}
.admission-table thead tr.admission-filter-row .form-control:focus {
    border-color: var(--green-excel) !important;
    outline: none !important;
}

.admission-table tbody tr:nth-child(even) td { background-color: #fafafa; }
.admission-table tbody tr:hover td { background-color: #fffde7 !important; }

/* Sticky Action Column */
.admission-table .fixed_item {
    position: sticky !important;
    right: 0;
    background-color: #ffffff;
    z-index: 2;
    border-left: 1px solid #b8c8d5 !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 1px solid #b8c8d5 !important;
}
.admission-table tbody tr:nth-child(even) .fixed_item { background-color: #fafafa !important; }
.admission-table tbody tr:hover .fixed_item { background-color: #fffde7 !important; }
.admission-table th.fixed_item {
    background-color: var(--green-excel) !important;
    color: #ffffff !important;
    z-index: 7 !important;
    border-left: 1px solid var(--green-excel-dark) !important;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
}
.admission-table thead tr.admission-filter-row th.fixed_item {
    background-color: var(--green-excel-light) !important;
    z-index: 8 !important;
    border-left: 1px solid #b8c8d5 !important;
    border-right: 1px solid #b8c8d5 !important;
    border-bottom: 2px solid var(--green-excel-border) !important;
}

/* Loading & Pagination */
.admission-table.table-loading { opacity: 0.45; pointer-events: none; }
.admission-loader-bar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--green-excel);
    z-index: 10;
    display: none;
}

/* Empty State Full Table Center */
.admission-table tbody tr.admission-empty-row {
    height: calc(100vh - 270px) !important;
    min-height: 380px !important;
    background: #ffffff !important;
}
.admission-table tbody tr.admission-empty-row:hover td {
    background-color: #ffffff !important;
}
.admission-table tbody tr.admission-empty-row td.admission-empty-cell {
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
    border-radius: 4px !important;
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

.admission-pagination-footer {
    padding: 6px 14px;
    background: #f8fafc;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.user-pagination .pagination { margin: 0; }
.user-pagination .page-item .page-link {
    font-size: 11px;
    padding: 3px 8px;
    color: #1e293b;
    border: 1px solid var(--line-subtle);
}
.user-pagination .page-item.active .page-link {
    background-color: var(--green-excel) !important;
    border-color: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
}

/* Modal Theme (Matching promote_add) */
.modal-content {
    border: 1px solid #9eacb7 !important;
    border-radius: 3px !important;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.22) !important;
    background: #ffffff !important;
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
    display: flex !important;
    align-items: center !important;
}
.modal-title i {
    color: #dc2626 !important;
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
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 6px !important;
}
</style>

<div class="content-wrapper expense-directory-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            <div class="card admission-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="admission-titlebar">
                    <h1 class="admission-title">
                        <i class="fa fa-money text-warning"></i>
                        <span>Expense Register Master</span>
                        <small id="totalExpenseBadge">({{ $totalCount }} Records)</small>
                    </h1>
                    <div class="title-actions">
                        <button type="button" class="btn-header-action" id="btnPrintTable" onclick="window.print();" title="Print Directory">
                            <i class="fa fa-print text-primary mr-1"></i> Print
                        </button>
                        @if($canAdd)
                            <a href="{{ url('expenseAdd') }}" class="btn-header-action btn-header-add" title="Add Expense">
                                <i class="fa fa-plus text-success mr-1"></i> Add Expense
                            </a>
                        @endif
                        <a href="{{ url('master_dashboard') }}" class="btn-header-action" title="Back to Dashboard">
                            <i class="fa fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>

                <!-- 2. Main Filter Form & Table with Sticky Respective Thead Filters -->
                <form id="expenseFilterForm" action="{{ url('expenseView') }}" method="get" class="w-100">
                    <input type="hidden" name="page" id="expenseCurrentPage" value="{{ ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $data->currentPage() : 1 }}">

                    <!-- Table Container with Sticky Thead Filter Row -->
                    <div class="admission-scroll">
                        <div id="expenseTableLoader" class="admission-loader-bar"></div>

                        <table id="expenseList" class="admission-table">
                            <thead>
                                <!-- 1st Header Row (Column Titles) -->
                                <tr>
                                    <th style="width: 36px;" class="text-center">#</th>
                                    <th style="width: 50px;" class="text-center">SR.NO</th>
                                    <th style="min-width: 170px;">Expense Voucher / Item Title</th>
                                    <th style="min-width: 130px;">Staff / Created By</th>
                                    <th style="min-width: 110px;" class="text-center">Date</th>
                                    <th style="min-width: 110px;" class="text-center">Payment Mode</th>
                                    <th style="min-width: 110px;" class="text-center">Qty × Rate</th>
                                    <th style="min-width: 120px;" class="text-right">Amount (₹)</th>
                                    <th style="min-width: 75px;" class="text-center">File</th>
                                    @if($getPermission->edit == 1 || $getPermission->download == 1 || $getPermission->deletes == 1)
                                        <th class="fixed_item text-center" style="width: 90px;">Action</th>
                                    @endif
                                </tr>

                                <!-- 2nd Header Row: Exact 1-to-1 Respective Live Filter Row -->
                                <tr class="admission-filter-row">
                                    <!-- Under # -->
                                    <th class="text-center" style="width: 36px;">
                                        <button type="button" id="expenseResetBtn" class="btn-row-action" title="Reset All Filters" style="background:#ffffff; border:1px solid #cbd5e1; color:#64748b; margin:0 auto;">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                    </th>

                                    <!-- Under SR.NO -->
                                    <th class="text-center" style="width: 50px;">
                                        <input type="text" class="form-control form-control-sm live-expense-input text-center" name="id" value="{{ $search['id'] ?? '' }}" placeholder="ID">
                                    </th>

                                    <!-- Under Voucher Title -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-expense-input" name="name" value="{{ $search['name'] ?? '' }}" placeholder="Voucher title, description..." style="min-width: 160px;">
                                    </th>

                                    <!-- Under Staff / Created By -->
                                    <th>
                                        <input type="text" class="form-control form-control-sm live-expense-input" name="user_name" value="{{ $search['user_name'] ?? '' }}" placeholder="Staff name..." style="min-width: 120px;">
                                    </th>

                                    <!-- Under Date -->
                                    <th class="text-center">
                                        <input type="date" class="form-control form-control-sm live-expense-input" name="from_date" value="{{ $search['from_date'] ?? '' }}" style="min-width: 105px;">
                                    </th>

                                    <!-- Under Payment Mode -->
                                    <th class="text-center">
                                        <select class="form-control form-control-sm live-expense-select" name="payment_mode_id" style="min-width: 100px;">
                                            <option value="">All Modes</option>
                                            @if(!empty($paymentModes))
                                                @foreach($paymentModes as $pm)
                                                    <option value="{{ $pm->id }}" {{ (($search['payment_mode_id'] ?? '') == $pm->id) ? 'selected' : '' }}>
                                                        {{ $pm->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </th>

                                    <!-- Under Qty x Rate -->
                                    <th></th>

                                    <!-- Under Amount -->
                                    <th>
                                        <input type="number" step="any" class="form-control form-control-sm live-expense-input text-right" name="amount" value="{{ $search['amount'] ?? '' }}" placeholder="Amount ₹" style="min-width: 100px;">
                                    </th>

                                    <!-- Under File -->
                                    <th></th>

                                    @if($getPermission->edit == 1 || $getPermission->download == 1 || $getPermission->deletes == 1)
                                        <th class="fixed_item"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="expenseTableBody">
                                @include('expense.table_rows')
                            </tbody>
                        </table>
                    </div>

                    <!-- 3. Pagination Footer (Matching viewUser 1:1) -->
                    <div class="admission-pagination-footer">
                        <div class="d-flex align-items-center gap-2" style="font-size: 11.5px; color: #475569;">
                            <span>Showing <strong id="expenseFromItem">{{ $firstItem }}</strong> to <strong id="expenseToItem">{{ $lastItem }}</strong> of <strong id="expenseTotalFooter">{{ $totalCount }}</strong> expenses</span>
                            <div class="d-inline-flex align-items-center ml-2">
                                <label class="mr-1 mb-0 font-weight-normal text-muted">Per page:</label>
                                <select class="form-control form-control-sm live-expense-select" name="per_page" id="expensePerPage" style="height: 24px; padding: 1px 4px; font-size: 11px; width: 65px;">
                                    <option value="25" {{ ($perPageValue == '25') ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($perPageValue == '50') ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ ($perPageValue == '100') ? 'selected' : '' }}>100</option>
                                    <option value="all" {{ ($perPageValue == 'all' || $perPageValue == '-1') ? 'selected' : '' }}>All</option>
                                </select>
                            </div>
                        </div>

                        <div id="paginationContainer">
                            @include('expense.pagination')
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

<!-- Delete Confirmation Modal (Matching promote_add) -->
<div class="modal fade" id="Modal_id" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-exclamation-triangle text-danger"></i> Delete Expense Confirmation
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('expenseDelete') }}" method="post" id="deleteExpenseForm">
                @csrf
                <div class="modal-body p-3">
                    <input type="hidden" id="delete_id" name="delete_id">
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fff1f2; border: 1px solid #fecdd3; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa fa-trash-o text-danger" style="font-size: 18px;"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Delete Expense Voucher</strong>
                            <span class="text-muted" style="font-size: 11.5px;">Review voucher details before confirming deletion:</span>
                        </div>
                    </div>

                    <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Voucher Title:</span>
                            <strong class="text-dark" id="deleteExpenseName">-</strong>
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mb-0" style="font-size: 11px; border-radius: 2px; background: #fffbebf5; border-color: #fde68a; color: #92400e;">
                        <i class="fa fa-info-circle mr-1"></i> Permanent Action: Once deleted, this voucher record will be removed from financial ledgers.
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
var expenseDebounceTimer;

function fetchExpenseData(url) {
    var targetUrl = url || $('#expenseFilterForm').attr('action');
    var formData = $('#expenseFilterForm').serialize();

    $('#expenseTableLoader').show();
    $('#expenseList').addClass('table-loading');

    $.ajax({
        url: targetUrl,
        type: 'GET',
        data: formData,
        dataType: 'json',
        success: function(res) {
            $('#expenseTableLoader').hide();
            $('#expenseList').removeClass('table-loading');

            if (res.status === 'success') {
                $('#expenseTableBody').html(res.html);
                $('#paginationContainer').html(res.pagination);

                // Update Counts 1:1
                var total = res.total || 0;
                var from = res.from || 0;
                var to = res.to || 0;

                $('#totalExpenseBadge').text('(' + total + ' Records)');
                $('#expenseTotalFooter').text(total);
                $('#expenseFromItem').text(from);
                $('#expenseToItem').text(to);
            }
        },
        error: function() {
            $('#expenseTableLoader').hide();
            $('#expenseList').removeClass('table-loading');
        }
    });
}

function resetExpenseFilters() {
    $('.live-expense-input').val('');
    $('.live-expense-select').val('');
    $('#expensePerPage').val('25');
    $('#expenseCurrentPage').val('1');
    fetchExpenseData("{{ url('expenseView') }}");
}

$(function(){
    // Delete Modal Handler
    $(document).on('click', '.deleteData', function() {
        var delete_id = $(this).data('id');
        var name = $(this).data('name') || '';
        $('#delete_id').val(delete_id);
        $('#deleteExpenseName').text(name ? name : '-');
    });

    // Real-Time Input Debounce
    $(document).on('input', '.live-expense-input', function(){
        clearTimeout(expenseDebounceTimer);
        $('#expenseCurrentPage').val('1');
        expenseDebounceTimer = setTimeout(function(){
            fetchExpenseData();
        }, 300);
    });

    // Instant Select Change
    $(document).on('change', '.live-expense-select', function(){
        $('#expenseCurrentPage').val('1');
        fetchExpenseData();
    });

    // Reset Button Click
    $('#expenseResetBtn').on('click', function(){
        resetExpenseFilters();
    });

    // Pagination Link Clicks
    $(document).on('click', '#paginationContainer a.page-link', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var page = $(this).data('page');
        if (page) {
            $('#expenseCurrentPage').val(page);
        }
        if (url) {
            fetchExpenseData(url);
        }
    });
});
</script>

@endsection