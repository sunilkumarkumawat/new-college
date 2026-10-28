@php
$getHostel = Helper::getHostel();
$getPaymentMode = Helper::getPaymentMode();
$getRole = Helper::getUsers();
@endphp
@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   EXPENSE VOUCHER ENTRY - EXECUTIVE EXCEL GREEN THEME
   (Matches enquiryAdd, admissionView & studentsDashboard layout)
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
}

.content-wrapper {
    background-color: #f4f6f9;
}

.expense-compact-page {
    font-size: 11.5px;
    color: var(--text-main);
}

/* 1. Main Form Card Wrapper */
.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
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
    gap: 12px;
}

.dash-title {
    margin: 0;
    font-size: 14px;
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

.dash-title-tools {
    display: flex;
    align-items: center;
    gap: 6px;
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

/* 3. Section Strips (Compact Ribbon Dividers) */
.form-section-strip {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    border-left: 3.5px solid var(--green-excel);
    padding: 5px 12px;
    margin-bottom: 8px;
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
    gap: 6px;
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

/* 4. Table Grid Styling */
.expense-table-container {
    overflow-x: auto;
    background: #ffffff;
    border: 1px solid var(--line-subtle);
    border-radius: 2px;
}

.expense-grid-table {
    width: 100%;
    margin-bottom: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 11px;
}

.expense-grid-table thead th {
    background: var(--green-excel) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.2px;
    border-right: 1px solid var(--green-excel-dark) !important;
    border-bottom: 1px solid var(--green-excel-dark) !important;
    padding: 6px 7px !important;
    vertical-align: middle !important;
    text-align: center;
    user-select: none;
    white-space: nowrap;
}

.expense-grid-table tbody td {
    padding: 4px 6px !important;
    border-right: 1px solid var(--line-subtle) !important;
    border-bottom: 1px solid var(--line-subtle) !important;
    vertical-align: middle !important;
    box-sizing: border-box;
    background: #ffffff;
}

.expense-grid-table tbody tr:hover td {
    background: #f8fafc;
}

/* Form Controls in Table */
.expense-grid-table .form-control-compact {
    height: 27px !important;
    min-height: 27px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    padding: 2px 6px !important;
    font-size: 11px !important;
    color: #1e293b !important;
    background-color: #ffffff !important;
    transition: all 0.15s ease-in-out;
    width: 100% !important;
    box-sizing: border-box !important;
}
.expense-grid-table .form-control-compact:focus {
    border-color: var(--green-excel) !important;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.18) !important;
    outline: none !important;
}

.expense-grid-table .form-control-compact[readonly] {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #15803d !important;
    font-weight: 700 !important;
    cursor: not-allowed;
    text-align: right;
}

/* Select2 in Table */
.expense-grid-table .select2,
.expense-grid-table .select2-container {
    width: 100% !important;
}
.expense-grid-table .select2-container .select2-selection--single {
    height: 27px !important;
    border: 1px solid var(--line-subtle) !important;
    border-radius: 2px !important;
    padding: 1px 4px !important;
    font-size: 11px !important;
    display: flex !important;
    align-items: center !important;
}
.expense-grid-table .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 23px !important;
    font-size: 11px !important;
    padding-left: 2px !important;
}

/* Row Action Buttons */
.btn-table-action {
    width: 25px;
    height: 25px;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 3px !important;
    font-size: 11px !important;
    border: 1px solid transparent !important;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-table-add { background: #f0fdf4 !important; border-color: #86efac !important; color: #166534 !important; }
.btn-table-add:hover { background: #166534 !important; color: #ffffff !important; }
.btn-table-delete { background: #fff1f2 !important; border-color: #fecdd3 !important; color: #e11d48 !important; }
.btn-table-delete:hover { background: #e11d48 !important; color: #ffffff !important; }

/* 5. Summary & Total Calculation Bar */
.expense-total-strip {
    background: var(--green-excel-light);
    border: 1px solid var(--green-excel-border);
    border-radius: 3px;
    padding: 6px 12px;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.kpi-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 3px;
    font-size: 11.5px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    gap: 5px;
}
.kpi-chip-total {
    border-color: #86efac;
    background: #f0fdf4;
    color: #166534;
    font-size: 13px;
    font-weight: 700;
}
.kpi-chip-total .total-display {
    color: #15803d;
    font-size: 15px;
    font-weight: 800;
}

/* 6. Bottom Action Bar */
.dash-form-footer {
    padding: 8px 14px;
    background: #f8fafc;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.btn-submit-save {
    background: var(--green-excel) !important;
    border: 1px solid var(--green-excel-dark) !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border-radius: 3px !important;
    padding: 5px 20px !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.25);
    cursor: pointer;
    line-height: 1.4;
}
.btn-submit-save:hover {
    background: var(--green-excel-dark) !important;
    box-shadow: 0 2px 6px rgba(33, 115, 70, 0.35);
    transform: translateY(-1px);
}

.btn-reset-clean {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 12px !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
    line-height: 1.4;
}
.btn-reset-clean:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.file-hint-text {
    font-size: 9px;
    color: #64748b;
    margin-top: 1px;
}
</style>

<div class="content-wrapper expense-compact-page">
    <section class="content pt-2 pb-3">
        <div class="container-fluid px-2">
            
            <div class="card dash-main-card">
                
                <!-- 1. Executive Titlebar -->
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-credit-card"></i>
                        <span>{{ __('expense.Add Expense') }}</span>
                        <small>| Multi-Item Expense Voucher Entry</small>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('expenseView') }}" class="btn-header-action" title="View Expense Records">
                            <i class="fa fa-list-ul text-primary mr-1"></i> {{ __('common.View') }} Expenses
                        </a>
                        <a href="{{ url('dashboard') }}" class="btn-header-action" title="Back to Dashboard">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> {{ __('common.Back') }}
                        </a>
                    </div>
                </div>

                <!-- 2. Main Form Body -->
                <form id="expenseForm" action="{{ url('expenseAdd') }}" method="post" enctype="multipart/form-data" class="mb-0">
                    @csrf

                    <div class="p-2">
                        
                        <!-- Header Strip -->
                        <div class="form-section-strip">
                            <span class="form-section-title">
                                <i class="fa fa-pencil-square-o"></i> 1. Expense Voucher Line Items
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-header-action btn-header-add" id="btnAddRowTop" title="Add Another Expense Item">
                                    <i class="fa fa-plus mr-1"></i> Add Row
                                </button>
                                <span class="form-section-badge">Voucher Entry</span>
                            </div>
                        </div>

                        <!-- Table Grid Container -->
                        <div class="expense-table-container">
                            <table class="table expense-grid-table" id="expenseTable">
                                <thead>
                                    <tr>
                                        <th style="width: 32px;">#</th>
                                        <th style="min-width: 170px; text-align: left;">{{ __('Expense Head / Item Name') }} <span class="text-danger">*</span></th>
                                        <th style="min-width: 140px; text-align: left;">{{ __('Staff / Claimed By') }} <span class="text-danger">*</span></th>
                                        <th style="width: 125px;">{{ __('common.Date') }} <span class="text-danger">*</span></th>
                                        <th style="width: 75px;">{{ __('expense.Quantity') }} <span class="text-danger">*</span></th>
                                        <th style="width: 85px;">{{ __('expense.Rate') }} (₹) <span class="text-danger">*</span></th>
                                        <th style="width: 95px;">{{ __('expense.Total') }} (₹)</th>
                                        <th style="min-width: 120px;">{{ __('Payment Mode') }} <span class="text-danger">*</span></th>
                                        <th style="min-width: 130px;">{{ __('Attachment') }}</th>
                                        <th style="min-width: 140px;">{{ __('expense.Description') }}</th>
                                        <th style="width: 38px;">Act</th>
                                    </tr>
                                </thead>
                                <tbody id="table_body">
                                    <tr id="appendRow_0" class="expense-row">
                                        <!-- Index -->
                                        <td class="text-center font-weight-bold text-muted row-index">1</td>

                                        <!-- Name -->
                                        <td>
                                            <input type="text" class="form-control-compact font-weight-bold" 
                                                   id="name_0" name="name[]" 
                                                   placeholder="e.g. Office Stationery, Lab Chemicals" required autofocus>
                                        </td>

                                        <!-- User / Staff -->
                                        <td>
                                            <select class="form-control-compact select2" id="role_0" name="role[]" required>
                                                <option value="">-- Select Staff --</option>
                                                @if(!empty($getRole))
                                                    @foreach($getRole as $item)
                                                        <option value="{{ $item->id }}" {{ ($item->id == Session::get('id')) ? 'selected' : '' }}>
                                                            {{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>

                                        <!-- Date -->
                                        <td>
                                            <input type="date" class="form-control-compact text-center" 
                                                   id="date_0" name="date[]" 
                                                   value="{{ date('Y-m-d') }}" required>
                                        </td>

                                        <!-- Quantity -->
                                        <td>
                                            <input type="text" class="form-control-compact text-center expense-qty" 
                                                   id="quantity_0" name="quantity[]" 
                                                   placeholder="1" value="1" 
                                                   onkeypress="javascript:return isNumber(event)" required>
                                        </td>

                                        <!-- Rate -->
                                        <td>
                                            <input type="text" class="form-control-compact text-right expense-rate" 
                                                   id="rate_0" name="rate[]" 
                                                   placeholder="0.00" 
                                                   onkeypress="javascript:return isNumber(event)" required>
                                        </td>

                                        <!-- Amount -->
                                        <td>
                                            <input type="text" class="form-control-compact text-right amount font-weight-bold text-success" 
                                                   id="amount_0" name="amount[]" 
                                                   placeholder="0.00" value="0.00" readonly required>
                                        </td>

                                        <!-- Payment Mode -->
                                        <td>
                                            <select class="form-control-compact select2" id="payment_mode_id_0" name="payment_mode_id[]" required>
                                                @if(!empty($getPaymentMode))
                                                    @foreach($getPaymentMode as $value)
                                                        <option value="{{ $value->id }}">{{ $value->name ?? '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>

                                        <!-- Attachment -->
                                        <td>
                                            <input type="file" class="form-control-compact expense-attachment" 
                                                   id="attachment_0" name="attachment[]" 
                                                   accept="image/png, image/jpg, image/jpeg, application/pdf">
                                            <div class="file-hint-text">JPG, PNG, PDF < 2MB</div>
                                        </td>

                                        <!-- Description -->
                                        <td>
                                            <input type="text" class="form-control-compact" 
                                                   id="description_0" name="description[]" 
                                                   placeholder="Remarks / Bill details...">
                                        </td>

                                        <!-- Action -->
                                        <td class="text-center">
                                            <button type="button" class="btn-table-action btn-table-delete remove-row-btn" title="Remove Item" style="display: none;">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                            <button type="button" class="btn-table-action btn-table-add add-more-btn" title="Add Row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary & Total Calculation Bar -->
                        <div class="expense-total-strip">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="kpi-chip">
                                    <i class="fa fa-list text-muted"></i>
                                    <span>Total Items:</span>
                                    <strong class="text-dark" id="kpi_item_count">1</strong>
                                </div>
                                <div class="kpi-chip">
                                    <i class="fa fa-calculator text-primary"></i>
                                    <span>Total Quantity:</span>
                                    <strong class="text-primary" id="kpi_total_qty">1</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="kpi-chip kpi-chip-total">
                                    <i class="fa fa-inr"></i>
                                    <span>Grand Total:</span>
                                    <span class="total-display" id="total_amt_display">₹ 0.00</span>
                                </div>
                                <input type="hidden" id="total_amt" name="total_amt" value="0.00">
                            </div>
                        </div>

                    </div>

                    <!-- 5. Form Action Footer -->
                    <div class="dash-form-footer">
                        <div class="text-muted small">
                            <span class="text-danger font-weight-bold">*</span> Ensure all line items have Expense Name, Quantity, and Rate filled.
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-reset-clean" id="btnResetExpense">
                                <i class="fa fa-refresh mr-1"></i> {{ __('common.Reset') }}
                            </button>
                            <button type="submit" id="submitButton" class="btn-submit-save">
                                <i class="fa fa-save mr-1"></i> Save & Generate Voucher
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </section>
</div>

<script>
    var paymentModes = @json($getPaymentMode ?? []);
    var staffUsers = @json($getRole ?? []);
    var currentUserId = "{{ Session::get('id') }}";
    var rowCount = 0;

    function buildPaymentModeOptions() {
        var opts = '';
        if (paymentModes && paymentModes.length > 0) {
            paymentModes.forEach(function(pm) {
                opts += '<option value="' + pm.id + '">' + (pm.name || '') + '</option>';
            });
        }
        return opts;
    }

    function buildStaffOptions() {
        var opts = '<option value="">-- Select Staff --</option>';
        if (staffUsers && staffUsers.length > 0) {
            staffUsers.forEach(function(u) {
                var selected = (u.id == currentUserId) ? 'selected' : '';
                opts += '<option value="' + u.id + '" ' + selected + '>' + (u.first_name || '') + ' ' + (u.last_name || '') + '</option>';
            });
        }
        return opts;
    }

    function calculateRowAmount(rowElem) {
        var qty = parseFloat(rowElem.find('.expense-qty').val()) || 0;
        var rate = parseFloat(rowElem.find('.expense-rate').val()) || 0;
        var amount = (qty * rate);
        rowElem.find('.amount').val(amount.toFixed(2));
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        var total = 0;
        var totalQty = 0;
        var count = 0;

        $('.expense-row').each(function() {
            var row = $(this);
            var amt = parseFloat(row.find('.amount').val()) || 0;
            var qty = parseFloat(row.find('.expense-qty').val()) || 0;
            total += amt;
            totalQty += qty;
            count++;
        });

        $('#kpi_item_count').text(count);
        $('#kpi_total_qty').text(totalQty);
        $('#total_amt').val(total.toFixed(2));
        $('#total_amt_display').text('₹ ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    function reindexRows() {
        var rows = $('.expense-row');
        var totalRows = rows.length;

        rows.each(function(index) {
            var row = $(this);
            row.find('.row-index').text(index + 1);

            if (totalRows <= 1) {
                row.find('.remove-row-btn').hide().prop('disabled', true);
                row.find('.add-more-btn').show();
            } else {
                row.find('.remove-row-btn').show().prop('disabled', false);
                if (index === totalRows - 1) {
                    row.find('.add-more-btn').show();
                } else {
                    row.find('.add-more-btn').hide();
                }
            }
        });
        calculateGrandTotal();
    }

    function addNewExpenseRow() {
        rowCount++;
        var dateToday = "{{ date('Y-m-d') }}";
        var staffOpts = buildStaffOptions();
        var pmOpts = buildPaymentModeOptions();

        var newRowHtml = `
            <tr id="appendRow_${rowCount}" class="expense-row">
                <td class="text-center font-weight-bold text-muted row-index"></td>
                <td>
                    <input type="text" class="form-control-compact font-weight-bold" 
                           id="name_${rowCount}" name="name[]" 
                           placeholder="Expense Name" required>
                </td>
                <td>
                    <select class="form-control-compact select2" id="role_${rowCount}" name="role[]" required>
                        ${staffOpts}
                    </select>
                </td>
                <td>
                    <input type="date" class="form-control-compact text-center" 
                           id="date_${rowCount}" name="date[]" 
                           value="${dateToday}" required>
                </td>
                <td>
                    <input type="text" class="form-control-compact text-center expense-qty" 
                           id="quantity_${rowCount}" name="quantity[]" 
                           placeholder="1" value="1" 
                           onkeypress="javascript:return isNumber(event)" required>
                </td>
                <td>
                    <input type="text" class="form-control-compact text-right expense-rate" 
                           id="rate_${rowCount}" name="rate[]" 
                           placeholder="0.00" 
                           onkeypress="javascript:return isNumber(event)" required>
                </td>
                <td>
                    <input type="text" class="form-control-compact text-right amount font-weight-bold text-success" 
                           id="amount_${rowCount}" name="amount[]" 
                           placeholder="0.00" value="0.00" readonly required>
                </td>
                <td>
                    <select class="form-control-compact select2" id="payment_mode_id_${rowCount}" name="payment_mode_id[]" required>
                        ${pmOpts}
                    </select>
                </td>
                <td>
                    <input type="file" class="form-control-compact expense-attachment" 
                           id="attachment_${rowCount}" name="attachment[]" 
                           accept="image/png, image/jpg, image/jpeg, application/pdf">
                    <div class="file-hint-text">JPG, PNG, PDF < 2MB</div>
                </td>
                <td>
                    <input type="text" class="form-control-compact" 
                           id="description_${rowCount}" name="description[]" 
                           placeholder="Remarks...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn-table-action btn-table-delete remove-row-btn" title="Remove Item">
                        <i class="fa fa-trash-o"></i>
                    </button>
                    <button type="button" class="btn-table-action btn-table-add add-more-btn" title="Add Row">
                        <i class="fa fa-plus"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#table_body').append(newRowHtml);
        $('#appendRow_' + rowCount).find('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        reindexRows();
        $('#name_' + rowCount).focus();
    }

    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Add row triggers
        $(document).on('click', '.add-more-btn, #btnAddRowTop', function(e) {
            e.preventDefault();
            addNewExpenseRow();
        });

        // Remove row trigger (strictly prevented when only 1 row)
        $(document).on('click', '.remove-row-btn', function(e) {
            e.preventDefault();
            var totalRows = $('.expense-row').length;
            if (totalRows <= 1) {
                toastr.warning('At least one expense row is required.');
                return false;
            }
            $(this).closest('tr').remove();
            reindexRows();
        });

        // Realtime calculation on input change
        $(document).on('input keyup change', '.expense-qty, .expense-rate', function() {
            var row = $(this).closest('tr');
            calculateRowAmount(row);
        });

        // File validation
        $(document).on('change', '.expense-attachment', function() {
            var files = this.files;
            if (files && files.length > 0) {
                var file = files[0];
                if (file.size > 2 * 1024 * 1024) {
                    toastr.warning('Attachment file exceeds 2MB limit.');
                    $(this).val('');
                }
            }
        });

        // Form Reset
        $('#btnResetExpense').on('click', function() {
            $('#expenseForm')[0].reset();
            $('#table_body tr:not(:first)').remove();
            reindexRows();
            calculateGrandTotal();
            $('.select2').val('').trigger('change.select2');
            $('#role_0').val(currentUserId).trigger('change.select2');
            $('#payment_mode_id_0').val('1').trigger('change.select2');
        });

        reindexRows();
    });
</script>
@endsection