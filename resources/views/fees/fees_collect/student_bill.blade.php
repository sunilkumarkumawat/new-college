@php
$getFeesType = Helper::feesType();
$getPaymentMode = Helper::getPaymentMode();
$firstAmount = $data['FeesAssign']->total_amount ?? 0;
$fees_assign_details = $data['financial_year_assignments'] ?? DB::table('fees_assign_details')
            ->where('fees_assign_id', $data['FeesAssign']->id ?? 0)
            ->whereNull('deleted_at')->get();
$session = DB::table('sessions')->where('id', $data['session_id'] ?? Session::get('session_id'))->whereNull('deleted_at')->first();
$feesGroupMap = $data['feesGroupMap'] ?? [];
$feesDetailsSummary = $data['feesDetailsSummary'] ?? [];
$invoicesParsed = $data['invoicesParsed'] ?? [];
$fees_advance = App\Models\fees\FeesAdvance::where('unique_system_id', $data['stuData']['unique_system_id'])->first();
@endphp

<style>
/* ========================================================
   STUDENT BILL & COLLECTION - EXCEL EXECUTIVE THEME
   ======================================================== */
.bill-card-wrapper {
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 12px;
    overflow: hidden;
}

.bill-header-bar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

/* Tabs for Academic Sessions / Semesters */
.session-tabs-list {
    list-style: none;
    display: flex;
    align-items: center;
    padding-left: 0;
    margin-bottom: 0;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: thin;
    white-space: nowrap;
}

.session-tabs-list li {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    line-height: 1.2;
}

.session-tabs-list li:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

.session-tabs-list li.active-tab {
    background: #217346 !important;
    border-color: #185c37 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px rgba(33, 115, 70, 0.25);
}

.session-tabs-list li.active-tab small {
    color: #e2f0d9 !important;
}

/* Carry Forward and Advance Fee Alerts */
.carry-forward-lock {
    border: 1px solid #fde68a;
    background: #fffbeb;
    color: #92400e;
    padding: 8px 12px;
    font-size: 11.5px;
    border-radius: 4px;
    margin-bottom: 8px;
}

.advance-fee-badge {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 5px 10px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 11.5px;
    display: inline-flex;
    align-items: center;
}

/* Badge Pills */
.badge-receipt-pill {
    background: #e2f0d9;
    color: #185c37;
    border: 1px solid #c3e6cb;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
}

.badge-soft-success {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 3px;
    display: inline-block;
}
.badge-soft-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    font-size: 10.5px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 3px;
    display: inline-block;
}
.badge-soft-danger {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-size: 10.5px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 3px;
    display: inline-block;
}

/* Form Controls & Table Styling */
#myForm .form-control {
    height: 30px;
    padding: 3px 8px;
    font-size: 11.5px;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
}
#myForm .form-control:focus {
    border-color: #217346;
    box-shadow: 0 0 0 2px rgba(33, 115, 70, 0.15);
}

#myForm label {
    margin-bottom: 2px;
    font-size: 10.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.head-selection-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 6px;
    margin-bottom: 8px;
}

.head-table {
    margin-bottom: 0;
    font-size: 11.5px;
}
.head-table th {
    background: #217346 !important;
    color: #ffffff !important;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 8px !important;
    border: 1px solid #185c37 !important;
}
.head-table td {
    padding: 4px 6px !important;
    vertical-align: middle !important;
    border-color: #e2e8f0;
}

/* Calculation Badges Card */
.calculation-summary-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin: 8px 0;
}
.calc-badge-item {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
}
.calc-badge-item strong {
    font-size: 13px;
    font-weight: 700;
    margin-left: 2px;
}
.calc-badge-item strong.net-total-val {
    font-size: 15.5px;
    color: #15803d;
}

/* Fee Structure Table (Right Column) */
.fee-structure-card {
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    background: #ffffff;
    overflow-x: auto;
}
.fee-structure-table {
    margin-bottom: 0;
    font-size: 11px;
}
.fee-structure-table th {
    background: #217346 !important;
    color: #ffffff !important;
    font-weight: 700;
    padding: 6px 8px !important;
    white-space: nowrap;
    border: 1px solid #185c37 !important;
}
.fee-structure-table td {
    padding: 5px 8px !important;
    vertical-align: middle !important;
    white-space: nowrap;
    border-color: #e2e8f0;
}
.fee-structure-table tfoot td {
    background: #f1f5f9;
    font-weight: 700;
    border-top: 2px solid #cbd5e1;
}

/* Invoice History Table */
.invoice-history-table {
    font-size: 11px;
    margin-bottom: 0;
}
.invoice-history-table th {
    background: #217346 !important;
    color: #ffffff !important;
    font-size: 11px;
    font-weight: 700;
    padding: 7px 8px !important;
    border: 1px solid #185c37 !important;
}
.invoice-history-table td {
    padding: 5px 8px !important;
    vertical-align: middle !important;
    border-color: #e2e8f0;
}
</style>

<div>
    <!-- 1. Academic Session & Semester Selector Bar -->
    <div class="bill-card-wrapper mb-2">
        <div class="bill-header-bar">
            <div class="d-flex align-items-center">
                <span class="font-weight-bold text-dark mr-2" style="font-size: 12.5px;">
                    <i class="fa fa-university text-success mr-1"></i> Academic Session:
                </span>
                <ul class="session-tabs-list">
                    @if(!empty($data['sessions']) && count($data['sessions']) > 0)
                        @foreach($data['sessions'] as $sessionItem)
                            @php $isActive = (int)($data['stuData']['id'] ?? 0) === (int)($sessionItem['admission_id'] ?? 0); @endphp
                            <li class="tab {{ $isActive ? 'active-tab' : '' }}" data-admission_id="{{ $sessionItem['admission_id'] ?? '' }}" data-id="{{ $sessionItem['id'] ?? '' }}" data-unique_system_id="{{ $data['stuData']['unique_system_id'] ?? '' }}">
                                <i class="fa fa-calendar mr-1"></i> {{ $sessionItem['name'] ?? 'Session' }}
                                @if(!empty($sessionItem['class_name'])) &middot; {{ $sessionItem['class_name'] }}@endif
                                @if(empty($sessionItem['has_fees']))<small class="d-block text-muted ml-1">(No Fee)</small>@endif
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-xs btn-success font-weight-bold btnOpenQuickAddFeeGroupModal" 
                        data-admission-id="{{ $data['stuData']['id'] ?? '' }}"
                        data-unique-id="{{ $data['stuData']['unique_system_id'] ?? '' }}"
                        data-session-id="{{ $data['session_id'] ?? Session::get('session_id') }}"
                        data-name="{{ $data['stuData']['first_name'] ?? '' }} {{ $data['stuData']['last_name'] ?? '' }}"
                        data-adm-no="{{ $data['stuData']['admissionNo'] ?? '' }}"
                        data-course="{{ $data['stuData']['ClassTypes']['course']['name'] ?? ($data['stuData']['ClassTypes']['name'] ?? ($data['stuData']['course'] ?? 'Course')) }}"
                        data-batch="{{ $data['stuData']['batch'] ?? '' }}"
                        style="background:#217346;border-color:#185c37;color:#ffffff;padding:3px 9px;font-size:11px;border-radius:3px;box-shadow:0 1px 3px rgba(33,115,70,0.3);">
                    <i class="fa fa-plus-circle mr-1"></i> Quick Add Fee Head
                </button>
                <span class="badge badge-light border text-dark py-1 px-2 font-weight-bold">
                    <i class="fa fa-user mr-1 text-primary"></i> {{ $data['stuData']['first_name'] ?? '' }} {{ $data['stuData']['last_name'] ?? '' }} ({{ $data['stuData']['admissionNo'] ?? '' }})
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Blocks -->
    @if(!empty($data['is_carried_forward']))
        <div class="carry-forward-lock">
            <i class="fa fa-lock mr-1"></i><strong>Collection locked:</strong>
            ₹{{ number_format($data['carried_forward_amount'] ?? 0, 0) }} outstanding has been carried forward
            @if(!empty($data['carried_to_semester'])) to <strong>{{ $data['carried_to_semester'] }}</strong> @endif.
            Collect it from the next semester's <strong>Previous Dues</strong> head.
        </div>
    @endif

    <div class="row mb-2">
        @if(!empty($fees_advance) && $fees_advance->balance > 0)
            <div class="col-12 col-md-4 mb-1">
                <div class="advance-fee-badge">
                    <i class="fa fa-gift mr-1"></i> Student Advance Balance: ₹{{ number_format($fees_advance->balance, 2) }}
                    <input type="hidden" name="fees_advance_balance" id="fees_advance_balance" value="{{ $fees_advance->balance ?? '' }}">
                </div>
            </div>
            <div class="col-12 col-md-4 mb-1">
                <div class="d-flex align-items-center">
                    <label class="mb-0 mr-2 font-weight-bold" style="font-size: 11.5px;">Deposit from Advance?</label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="noOption" name="yesNoAdvance" value="no" class="custom-control-input" checked>
                        <label class="custom-control-label" for="noOption">No</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="yesOption" name="yesNoAdvance" value="yes" class="custom-control-input">
                        <label class="custom-control-label" for="yesOption">Yes</label>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12 col-md-4 mb-1">
            <div class="d-flex align-items-center">
                <label class="mb-0 mr-2 font-weight-bold text-primary" style="font-size: 11.5px; white-space: nowrap;">
                    <i class="fa fa-calculator mr-1"></i> Quick Aggregate:
                </label>
                <input type="text" id="aggregate_amount" class="form-control form-control-sm" placeholder="Type amount & hit Enter" autocomplete="off" style="height: 28px; font-size: 11.5px;">
            </div>
        </div>
    </div>

    <!-- Main Payment & Fee Structure Layout -->
    <div class="row mx-n1">
        <!-- LEFT COLUMN: Fee Pay Form -->
        <div class="col-12 col-lg-6 px-1 mb-2">
            <div class="bill-card-wrapper h-100 p-2">
                @php
                    $slipCounter = is_object($data['BillCounter'] ?? null) ? ($data['BillCounter']->counter ?? 0) : ($data['BillCounter']['counter'] ?? 0);
                    $activeSessionId = $data['session_id'] ?? Session::get('session_id');
                    $formattedSlipNo = $data['formattedSlipNo'] ?? \App\Helpers\Helper::formatReceiptNo($slipCounter + 1, $activeSessionId);
                @endphp

                <form id="myForm" class="{{ !empty($data['is_carried_forward']) ? 'collection-locked' : '' }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="admission_id" name="admission_id" value="{{ $data['stuData']['id'] }}" />
                    <input type="hidden" id="advance_payment" name="advance_payment" value="no" />
                    <input type="hidden" id="session_id" name="session_id" value="{{ $data['session_id'] ?? Session::get('session_id') }}" />
                    <input type="hidden" id="email" name="email" value="{{ $data['stuData']['email'] ?? '' }}" />
                    <input type="hidden" id="mobile" name="mobile" value="{{ $data['stuData']['mobile'] ?? '' }}" />
                    <input type="hidden" id="student_name_hidden" name="name" value="{{ $data['stuData']['first_name'] ?? '' }} {{ $data['stuData']['last_name'] ?? '' }}" />
                    <input type="hidden" id="admission_no_hidden" value="{{ $data['stuData']['admissionNo'] ?? '' }}" />
                    <input type="hidden" id="class_type_id1" name="class_type_id" value="{{ $data['stuData']['class_type_id'] ?? '' }}" />
                    <input type="hidden" name="slip_no" id="form_slip_no" value="{{ $formattedSlipNo }}">

                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                        <span class="font-weight-bold text-dark" style="font-size: 13px;">
                            <i class="fa fa-pencil-square-o text-success mr-1"></i> Fee Payment Entry
                        </span>
                        <span class="badge-receipt-pill">
                            Receipt No: <strong>{{ $formattedSlipNo }}</strong>
                        </span>
                    </div>

                    <!-- Fee Heads Interactive Selection Table -->
                    <div class="head-selection-row">
                        <table class="table table-bordered head-table mb-1">
                            <thead>
                                <tr>
                                    <th style="width: 38%;">Select Fee Head*</th>
                                    <th style="width: 25%;">Amount (₹)*</th>
                                    <th style="width: 20%;">Discount (₹)</th>
                                    <th style="width: 17%;">Fine (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $headArray = []; @endphp
                                @if(!empty($fees_assign_details))
                                    @foreach($fees_assign_details as $key => $fees)
                                        @php
                                            $headName = $feesGroupMap[$fees->fees_group_id] ?? ('Fee Head ' . $fees->fees_group_id);
                                            $summary = $feesDetailsSummary[$fees->admission_id . '_' . $fees->fees_group_id] ?? ['paid' => 0, 'paid_fine' => 0, 'discount' => 0];
                                            $paid = $summary['paid'];
                                            $paid_fine = $summary['paid_fine'];
                                            $paids = $paid + $paid_fine;
                                            $pending_amount = (($fees->fees_group_amount) - ($fees->discount) - ($fees->promotion_carry_adjustment ?? 0)) - ($paid);
                                            $headArray[] = ['pending_by_group_id' => $fees->fees_group_id ?? '', 'pending' => ($pending_amount ?? 0)];
                                        @endphp
                                        @if($fees->fees_group_amount > $paids)
                                            <tr>
                                                <td>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input selected_head pointer" id="checkbox_{{ $key }}" name="selected_head[]" data-fees_assign_detail_id="{{ $fees->id }}" data-head_name="{{ $headName }}" value="{{ $fees->fees_group_id }}">
                                                        <label class="custom-control-label pointer font-weight-bold" for="checkbox_{{ $key }}" style="font-size: 11px; color: #1e293b;">
                                                            {{ $headName }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="tel" class="form-control form-control-sm amount_get aggregate_{{ $fees->fees_group_id }}" placeholder="Amount" id="amount_{{ $fees->id }}" name="amount[]" onkeypress="javascript:return isNumber(event)" required disabled>
                                                </td>
                                                <td>
                                                    <input type="tel" class="form-control form-control-sm discounts" placeholder="Discount" id="discount_{{ $fees->id }}" name="discount_amount[]" onkeypress="javascript:return isNumber(event)" disabled>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm fine_amount" id="fine_{{ $fees->id }}" value="0" name="fine[]" onkeypress="javascript:return isNumber(event)" required disabled>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Mode & Details Grid -->
                    <div class="row mx-n1">
                        <div class="col-md-4 col-sm-6 px-1 mb-1">
                            <label>{{ __('fees.Payment Mode') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="payment_mode_id" name="payment_mode_id" required>
                                @if(!empty($getPaymentMode))
                                    @foreach($getPaymentMode as $value)
                                        <option value="{{ $value->id }}">{{ $value->name ?? '' }}</option>
                                    @endforeach
                                @endif  
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 px-1 mb-1">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control" id="payment_status">
                                <option value="0">Payment Received</option>
                                <option value="1">Payment Pending</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 px-1 mb-1">
                            <label>{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date" id="payment_date" value="{{ date('Y-m-d') }}" required />
                        </div>

                        <!-- Dynamic Payment Fields -->
                        <div class="col-md-6 col-sm-6 px-1 mb-1" id="transition_id_input" style="display:none;">
                            <label>Transaction ID / UTR</label>
                            <input type="text" class="form-control" placeholder="Online Txn ID" id="transition_id" name="transition_id">
                        </div> 
                        <div class="col-md-6 col-sm-6 px-1 mb-1" id="bank_name_input" style="display:none;">
                            <label>Bank Name</label>
                            <input type="text" class="form-control" placeholder="Payer Bank Name" id="bank_name" name="bank_name">
                        </div>
                        <div class="col-md-6 col-sm-6 px-1 mb-1" style="display: none;" id="cheque_number_id">
                            <label>Cheque Number</label>
                            <input type="text" name="cheque_number" id="cheque_number" placeholder="Cheque No." class="form-control">
                        </div>
                        <div class="col-md-6 col-sm-6 px-1 mb-1" style="display: none;" id="cheque_date_id">
                            <label>Cheque Date</label>
                            <input type="date" name="cheque_date" id="cheque_date" class="form-control">
                        </div>
                        <div class="col-md-6 col-sm-6 px-1 mb-1" style="display: none;" id="payment_receipt_id">						
                            <label>Payment Receipt / Cheque Proof</label>
                            <input type="file" accept=".gif, .jpg, .jpeg, .png, .pdf, .doc" class="form-control" name="payment_receipt" id="payment_receipt">
                        </div> 
                        <div class="col-12 px-1 mb-1">
                            <label>{{ __('Remark') }}</label>
                            <input type="text" id="other_fee_remark" class="form-control" name="other_fee_remark" placeholder="Optional payment remarks / notes"/> 
                        </div>
                    </div>

                    <!-- Hidden calculated fields -->
                    <input type="hidden" class="form-control" readonly value="0" id="total_amount" name="total_amount">
                    <input type="hidden" class="form-control" readonly value="0" id="total_fine" name="total_fine">
                    <input type="hidden" id="discount_given" class="form-control" name="discount_given" value="" readonly />

                    <!-- Calculation Summary Bar -->
                    <div class="calculation-summary-card">
                        <div class="calc-badge-item">
                            Head Total: <strong class="text-dark">₹<span id="aggregate">0.00</span></strong>
                        </div>
                        <div class="calc-badge-item">
                            Discount: <strong class="text-danger">₹<span id="d_given">0.00</span></strong>
                        </div>
                        <div class="calc-badge-item">
                            Fine: <strong class="text-warning">₹<span id="f_given">0.00</span></strong>
                        </div>
                        <div class="calc-badge-item">
                            Net Total: <strong class="net-total-val">₹<span id="g_total">0.00</span></strong>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-between pt-1">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input pointer" id="checkbox_whatsapp" name="checkbox_whatsapp" value="1">
                            <label class="custom-control-label pointer text-success font-weight-bold" for="checkbox_whatsapp" style="font-size: 11px;">
                                <i class="fa fa-whatsapp"></i> Send WhatsApp Receipt
                            </label>
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="clonebtn" style="height: 30px; font-size: 11px; font-weight: 600;">
                                <i class="fa fa-plus"></i> Add Head
                            </button>
                            <button type="button" id="btn_collect_regular" class="btn btn-sm btn-success mr-1 collect_trigger_btn" data-action="collect" style="height: 30px; font-size: 11px; font-weight: 700; background: #217346; border: 1px solid #185c37; color: #ffffff;">
                                <i class="fa fa-check mr-1"></i> {{ __('fees.Collect') }}
                            </button>
                            <button type="button" id="btn_collect_print" class="btn btn-sm collect_trigger_btn" data-action="print" style="height: 30px; font-size: 11px; font-weight: 700; background: #185c37; border: 1px solid #0f3d23; color: #ffffff;">
                                <i class="fa fa-print mr-1"></i> Collect &amp; Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: Fee Structure Matrix & Semester Remark -->
        <div class="col-12 col-lg-6 px-1 mb-2">
            <!-- 1. Assigned Fee Structure & Balances Card -->
            <div class="bill-card-wrapper p-2 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">
                        <i class="fa fa-table text-success mr-1"></i> Assigned Fee Structure &amp; Balances
                    </span>
                    <span class="text-muted small">Semester / Year Heads</span>
                </div>

                <div class="fee-structure-card shadow-sm">
                    <table id="fee_structure" class="table table-bordered fee-structure-table">
                        <thead>
                            <tr>
                                <th>Fees Type</th>
                                <th class="text-right">Assigned</th>
                                <th class="text-right">Disc.</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Paid Fine</th>
                                <th class="text-right">Pending</th>
                                <th class="text-center" style="width: 125px;">Due Date</th>
                                <th class="text-right">Fine</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_amount = 0;
                                $total_discount = 0;
                                $total_paid = 0;
                                $total_paid_fine = 0;
                                $total_pending_fine = 0;
                                $total_pending = 0;
                            @endphp
                            @foreach($fees_assign_details as $fees)
                                @php
                                    $headName = $feesGroupMap[$fees->fees_group_id] ?? ('Fee Head ' . $fees->fees_group_id);
                                    $summary = $feesDetailsSummary[$fees->admission_id . '_' . $fees->fees_group_id] ?? ['paid' => 0, 'paid_fine' => 0, 'discount' => 0];
                                    $paid = $summary['paid'];
                                    $paid_fine = $summary['paid_fine'];
                                    $discount = $summary['discount'];
                                    $pending_amount = (($fees->fees_group_amount) - ($fees->discount) - ($fees->promotion_carry_adjustment ?? 0)) - ($paid);
                                    $isOverdue = ($pending_amount > 0 && isset($fees->installment_due_date) && $fees->installment_due_date < date('Y-m-d'));
                                    $headFine = $isOverdue ? (($pending_amount * ($fees->installment_fine ?? 0)) / 100) : 0;
                                @endphp
                                <tr id="group_{{ $fees->fees_group_id ?? '' }}" class="group_group">
                                    <td class="font-weight-bold" style="color: #1e293b;">{{ $headName }}</td>
                                    <td class="text-right">₹{{ number_format($fees->fees_group_amount ?? 0, 0) }}</td>
                                    <td class="text-right text-danger">₹{{ number_format(($fees->discount ?? 0) + $discount, 0) }}</td>
                                    <td class="text-right font-weight-bold" style="color: #15803d;">₹{{ number_format($paid - $discount, 0) }}</td>
                                    <td class="text-right text-muted">₹{{ number_format($paid_fine ?? 0, 2) }}</td>
                                    <td id="pending_by_group_id_{{ $fees->id ?? '' }}" class="text-right font-weight-bold" data-pending_amount="{{ $pending_amount ?? '0' }}" data-fine="{{ $isOverdue ? ($fees->installment_fine ?? 0) : 0 }}">
                                        @if($pending_amount == 0)
                                            <span class="badge-soft-success">₹0</span>
                                        @elseif($pending_amount < 0)
                                            <span class="text-success font-weight-bold" title="Excess / Advance Credit">-₹{{ number_format(abs($pending_amount), 0) }}</span>
                                        @else
                                            <span style="color: #0f172a;">₹{{ number_format($pending_amount ?? 0, 0) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center">
                                            <input type="date" class="form-control form-control-sm p-0 text-center {{ $pending_amount <= 0 ? 'fees_assign_detail' : '' }}" style="height: 23px; font-size: 10.5px; width: 108px; {{ $isOverdue ? 'border: 1px solid #ef4444; background: #fef2f2;' : 'border: 1px solid #cbd5e1;' }}" name="installment_due_date" data-detail_id="{{ $fees->id ?? '' }}" data-old_value="{{ $fees->installment_due_date ?? '' }}" {{ $pending_amount <= 0 ? 'disabled' : '' }} value="{{ $fees->installment_due_date ?? '' }}">
                                            @if($isOverdue)
                                                <i class="fa fa-exclamation-circle text-danger ml-1" title="Overdue Due Date"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right text-danger font-weight-bold">₹{{ number_format($headFine, 2) }}</td>
                                </tr>
                                @php
                                    $total_amount += $fees->fees_group_amount ?? 0;
                                    $total_discount += ($fees->discount ?? 0) + $discount;
                                    $total_paid += ($paid ?? 0) - $discount;
                                    $total_paid_fine += $paid_fine ?? 0;
                                    $total_pending_fine += $headFine;
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total Summary</td>
                                <td class="text-right">₹{{ number_format($total_amount, 0) }}</td>
                                <td class="text-right text-danger">₹{{ number_format($total_discount, 0) }}</td>
                                <td class="text-right font-weight-bold" style="color: #15803d;">₹{{ number_format($total_paid, 0) }}</td>
                                <td class="text-right text-muted">₹{{ number_format($total_paid_fine, 2) }}</td>
                                <td id="validate_pending" class="text-right font-weight-bold" style="color: #0f172a;" data-pending="{{ (($total_amount ?? 0) - ($total_discount ?? 0)) - ($total_paid ?? 0) }}">
                                    ₹{{ number_format((($total_amount ?? 0) - ($total_discount ?? 0)) - ($total_paid ?? 0), 0) }}
                                </td>
                                <td></td>
                                <td class="text-right text-danger font-weight-bold">₹{{ number_format($total_pending_fine, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- 2. Dedicated Semester Fee Assignment Remark Card -->
            <div class="bill-card-wrapper p-2">
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="font-weight-bold text-dark" style="font-size: 12.5px;">
                        <i class="fa fa-commenting-o text-success mr-1"></i> Semester Fee Assignment Remark
                    </span>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 10px;">
                            <i class="fa fa-calendar mr-1 text-success"></i> {{ $session->from_year ?? '' }}{{ !empty($session->to_year) ? '-' . substr($session->to_year, -2) : '' }}
                        </span>
                        <button type="button" class="btn btn-xs btn-outline-secondary ml-1" id="btn_toggle_edit_semester_remark" style="font-size: 10px; padding: 2px 7px;" title="Edit Semester Remark">
                            <i class="fa fa-pencil mr-1"></i> Edit
                        </button>
                    </div>
                </div>

                <!-- Display View -->
                <div id="semester_remark_display_wrapper">
                    @if(!empty($data['FeesAssign']->remark))
                        <div class="p-2 rounded mt-1" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3px solid #217346; color: #166534; font-size: 11.5px; line-height: 1.45;">
                            <i class="fa fa-quote-left text-success mr-1" style="opacity: 0.6; font-size: 10px;"></i>
                            <span id="semester_remark_text_span" class="font-weight-600">{{ $data['FeesAssign']->remark }}</span>
                            <i class="fa fa-quote-right text-success ml-1" style="opacity: 0.6; font-size: 10px;"></i>
                        </div>
                    @else
                        <div class="p-2 rounded mt-1 text-muted font-italic" style="background: #f8fafc; border: 1px dashed #cbd5e1; font-size: 11px;">
                            <i class="fa fa-info-circle mr-1 text-secondary"></i>
                            <span id="semester_remark_text_span">No remark recorded for this semester fee assignment.</span>
                        </div>
                    @endif
                </div>

                <!-- Inline Edit View (Initially Hidden) -->
                <div id="semester_remark_edit_wrapper" style="display: none;" class="mt-2">
                    <textarea id="semester_remark_textarea" class="form-control" rows="2" style="font-size: 11.5px; resize: vertical;" placeholder="Type semester fee assignment remark...">{{ $data['FeesAssign']->remark ?? '' }}</textarea>
                    <div class="d-flex justify-content-end align-items-center mt-1">
                        <button type="button" class="btn btn-xs btn-secondary mr-1" id="btn_cancel_semester_remark" style="font-size: 10.5px; padding: 2px 8px;">Cancel</button>
                        <button type="button" class="btn btn-xs btn-success" id="btn_save_semester_remark" data-admission-id="{{ $data['stuData']['id'] ?? '' }}" style="font-size: 10.5px; padding: 2px 10px; background: #217346; border-color: #185c37;">
                            <i class="fa fa-check mr-1"></i> Save Remark
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PREVIOUS TRANSACTIONS & INVOICE HISTORY TABLE -->
    <div class="bill-card-wrapper p-2 mt-2">
        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
            <span class="font-weight-bold text-dark" style="font-size: 13px;">
                <i class="fa fa-history text-success mr-1"></i> Payment Transaction History (Financial Year: {{ ($session->from_year ?? '') . (!empty($session->to_year) ? '-' . substr($session->to_year, -2) : '') }})
            </span>
            <span class="badge badge-secondary py-1 px-2">{{ count($data['FeesDetailsInvoices'] ?? []) }} Receipts</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover invoice-history-table mb-0">
                <thead>
                    <tr>
                        <th>Head Name</th>
                        <th>Semester / Class</th>
                        <th class="text-center">Receipt No</th>
                        <th>Payment Date</th>
                        <th class="text-right">Amount (₹)</th>
                        <th class="text-right">Discount (₹)</th>
                        <th class="text-right">Fine (₹)</th>
                        <th>Payment Mode</th>
                        <th>Bank / Txn ID</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($data['FeesDetailsInvoices']) && count($data['FeesDetailsInvoices']) > 0)
                        @foreach($data['FeesDetailsInvoices'] as $val)
                            @php
                                $parsed = $invoicesParsed[$val->id] ?? [
                                     'head_names' => '-',
                                     'head_total' => 0,
                                     'discount' => 0,
                                     'head_fine_total' => 0
                                ];
                            @endphp
                            <tr>
                                <td class="font-weight-bold" style="color: #1e293b;">{{ $parsed['head_names'] ?: '-' }}</td>
                                <td>{{ $data['stuData']['ClassTypes']['name'] ?? '-' }}</td>
                                <td class="text-center">
                                    <form target="_blank" action="{{ url('printFeesInvoice') }}" method="post" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="fees_details_invoice_id" value="{{ $val->id }}" />
                                        <button type="submit" class="btn btn-xs btn-outline-secondary font-weight-bold py-0 px-2" title="Print Invoice">
                                            <i class="fa fa-print mr-1"></i>{{ $val->invoice_no ?? '-' }}
                                        </button>
                                    </form>
                                </td>
                                <td>{{ !empty($val->payment_date) ? date('d-m-Y', strtotime($val->payment_date)) : '-' }}</td>
                                <td class="text-right font-weight-bold" style="color: #15803d;">₹{{ number_format($parsed['head_total'], 2) }}</td>
                                <td class="text-right text-danger">₹{{ number_format($parsed['discount'], 2) }}</td>
                                <td class="text-right text-danger">₹{{ number_format($parsed['head_fine_total'], 2) }}</td>
                                <td>
                                    @php
                                        $modeName = '-';
                                        if(!empty($getPaymentMode)) {
                                            foreach($getPaymentMode as $pm) {
                                                if($pm->id == $val->payment_mode) { $modeName = $pm->name; break; }
                                            }
                                        }
                                    @endphp
                                    <span class="badge badge-light border">{{ $modeName }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $val->bank_name ? $val->bank_name . ' | ' : '' }}{{ $val->transaction_id ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($val->status == 0)
                                        <span class="badge-soft-success">Received</span>
                                    @elseif($val->status == 1)
                                        <span class="badge-soft-warning">Pending</span>
                                    @else
                                        <span class="badge-soft-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(Session::get('role_id') == 1 && $val->status != 2)
                                        <button class="btn btn-outline-danger btn-xs revert_fees py-0 px-2" data-session_id="{{ $val->session_id ?? '' }}" data-admission_id="{{ $val->admission_id ?? '' }}" data-id="{{ $val->id }}" data-invoice_no="{{ $val->invoice_no ?? '' }}" data-amount="{{ $parsed['head_total'] ?? 0 }}" data-toggle="modal" data-target="#revert_modal" title="Revert Fee">
                                            <i class="fa fa-undo"></i> Revert
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="11" class="text-center py-3 text-muted">No fee transaction invoices found for this academic session.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================
     MODALS (MATCHING student/promote_add LAYOUT)
     ======================================================== -->

<!-- 1. Custom Fee Collection Confirmation Modal -->
<div class="modal fade" id="collectConfirmModal" tabindex="-1" role="dialog" aria-labelledby="collectConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="collectConfirmModalLabel">
                    <i class="fa fa-check-circle"></i> Confirm Fee Payment
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-start mb-3">
                    <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-money text-success" style="font-size: 17px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Collect Student Fees</strong>
                        <span class="text-muted" style="font-size: 11.5px;">Please review the payment parameters below:</span>
                    </div>
                </div>

                <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Student Name:</span>
                        <strong class="text-dark" id="modal_student_name">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Admission No:</span>
                        <strong class="text-dark" id="modal_admission_no">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Receipt Number:</span>
                        <strong class="text-dark" id="modal_receipt_no">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Payment Mode:</span>
                        <strong class="text-dark" id="modal_payment_mode">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Selected Heads:</span>
                        <strong class="text-dark" id="modal_selected_heads">-</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Total Payable:</span>
                        <strong class="text-success font-weight-bold" style="font-size: 15px;">₹<span id="modal_grand_total">0.00</span></strong>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 11px; border-radius: 3px; background: #e7eff5; border-color: #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-1"></i> This transaction will be recorded in student ledger &amp; daily receipts immediately.
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm font-weight-bold px-3" id="btn_modal_confirm_submit" style="background: #217346; border: 1px solid #185c37; color: #ffffff;">
                    <i class="fa fa-check mr-1"></i> Confirm &amp; Process
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Revert Fees Confirmation Modal -->
<div class="modal fade" id="revert_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-undo text-danger" style="color: #dc2626 !important;"></i> Revert Fee Invoice Confirmation
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="revert_fees_form" method="post">
                @csrf
                <input type="hidden" id="admissionId" name="admission_id">
                <input type="hidden" id="fees_invoice_id" name="fees_invoice_id">
                <input type="hidden" id="sessionID_" name="session_id">
                
                <div class="modal-body p-3">
                    <div class="d-flex align-items-start mb-3">
                        <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa fa-exclamation-triangle text-danger" style="font-size: 17px;"></i>
                        </div>
                        <div>
                            <strong class="d-block text-danger" style="font-size: 13.5px; line-height: 1.3;">Revert Receipt &amp; Restore Dues</strong>
                            <span class="text-muted" style="font-size: 11.5px;">Are you sure you want to revert this fee invoice transaction?</span>
                        </div>
                    </div>

                    <div class="p-2 mb-3 rounded" style="background: #f8fafc; border: 1px solid #cbd5e1; font-size: 11.5px;">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Invoice / Receipt No:</span>
                            <strong class="text-dark" id="revert_inv_label">-</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Invoice Amount:</span>
                            <strong class="text-danger font-weight-bold" style="font-size: 14px;" id="revert_amt_label">-</strong>
                        </div>
                    </div>

                    <div class="alert alert-danger py-2 px-3 mb-0" style="font-size: 11px; border-radius: 3px; background: #fef2f2; border-color: #fecaca; color: #991b1b;">
                        <i class="fa fa-exclamation-circle mr-1"></i> <strong>Warning:</strong> This action is irreversible and will mark the invoice cancelled while restoring the student's pending balance.
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" id="hide_modal" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3">
                        <i class="fa fa-undo mr-1"></i> Revert Fee Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Due Date Change Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="fa fa-calendar"></i> Confirm Due Date Change
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="d-flex align-items-start mb-3">
                    <div class="mr-3" style="width: 38px; height: 38px; border-radius: 50%; background: #e7f5ec; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa fa-calendar-check-o text-success" style="font-size: 17px;"></i>
                    </div>
                    <div>
                        <strong class="d-block text-dark" style="font-size: 13.5px; line-height: 1.3;">Update Installment Due Date</strong>
                        <span class="text-muted" style="font-size: 11.5px;">Are you sure you want to update the due date for this fee installment head?</span>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 11px; border-radius: 3px; background: #e7eff5; border-color: #b8c9d9; color: #1e3a5f;">
                    <i class="fa fa-info-circle mr-1"></i> Late fine calculation will automatically adjust based on the newly selected due date.
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" id="cancelChange" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm font-weight-bold px-3" id="confirmChange" style="background: #217346; border: 1px solid #185c37; color: #ffffff;">
                    <i class="fa fa-check mr-1"></i> Update Due Date
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var pendingSubmitAction = '';

$(document).ready(function () {
    var BASEURL = "{{ url('/') }}";

    // Due Date Change Handler
    var currentTd, fees_assign_detail_id, value, old_value, field;
    $('#fee_structure').on('blur', '[name="installment_due_date"]', function() {
        currentTd = $(this);
        fees_assign_detail_id = currentTd.data('detail_id');
        value = currentTd.val();
        old_value = currentTd.data('old_value');
        field = currentTd.attr('name');

        function compareValues(value1, value2) {
            const date1 = Date.parse(value1);
            const date2 = Date.parse(value2);
            return date1 !== date2;
        }

        if (compareValues(value, old_value)) {
            $('#confirmationModal').modal('show');
        }
    });

    $('#confirmChange').on('click', function() {
        $('#confirmationModal').modal('hide');
        showLoadingOverlay('Updating Due Date', 'Saving new installment schedule...');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            url: BASEURL + '/installment_due_date_update',
            type: 'POST',
            data: {
                fees_assign_detail_id: fees_assign_detail_id,
                value: value,
                field: field
            },
            success: function(response) {
                hideLoadingOverlay();
                toastr.success('Due date updated successfully');
                var sysId = $('#trColor tbody tr.active-student').data('system-id') || '{{ $data["stuData"]["unique_system_id"] ?? "" }}';
                var admId = '{{ $data["stuData"]["id"] ?? "" }}';
                var sessId = '{{ $data["session_id"] ?? "" }}';
                showData(sysId, sessId, admId);
            },
            error: function() {
                hideLoadingOverlay();
                toastr.error('Failed to update due date');
                currentTd.val(old_value);
            }
        });
    });

    $('#cancelChange').on('click', function() {
        currentTd.val(old_value);
        $('#confirmationModal').modal('hide');
    });

    // Aggregate Amount Enter key handler
    $('#aggregate_amount').on('keyup', function (event) {
        let aggregateAmount = parseFloat($(this).val()) || 0;

        const yesNoAdvance = document.querySelector('input[name="yesNoAdvance"]:checked');
        if (yesNoAdvance && yesNoAdvance.value === "yes") {
            let fees_advance_balance = parseFloat($("#fees_advance_balance").val()) || 0;
            if (aggregateAmount > fees_advance_balance) {
                toastr.error('The advance fee for students is Rs ' + fees_advance_balance);
                $('#aggregate_amount').val(0);
                return;
            }
        }

        if (event.key === "Enter") {
            $('.group_group').removeClass('bg-warning');

            let total_pending = parseFloat($("#validate_pending").data('pending')) || 0;
            if (aggregateAmount > total_pending) {
                toastr.error('Amount cannot be greater than total pending (₹' + total_pending + ').');
                $('#aggregate_amount').val(0);
                return;
            }

            let leftAmount = aggregateAmount;
            let filterArray = [];
            let data = @json($headArray);
            
            data.forEach(item => {
                if (leftAmount > 0 && item.pending > 0) {
                    filterArray.push(item);
                    leftAmount -= item.pending;
                }
            });

            if (filterArray.length > 0) {
                $('.selected_head').prop('checked', false).trigger('change');
                filterArray.forEach((item, index) => {
                    let amount = parseFloat(item.pending) || 0;
                    let checkBox = $('.selected_head').filter('[value="' + item.pending_by_group_id + '"]');
                    checkBox.prop('checked', true).trigger('change');
                    $("#group_" + item.pending_by_group_id).addClass('bg-warning');

                    if (index === filterArray.length - 1) {
                        $('.aggregate_' + item.pending_by_group_id).val(amount + leftAmount);
                    }
                });
            }
            updateTotals();
        }
    });

    // Checkbox change handler for individual fee heads
    $(".selected_head").on("change", function () {
        let fees_assign_detail_id = $(this).data("fees_assign_detail_id");

        if ($(this).is(":checked")) {
            $("#amount_" + fees_assign_detail_id).prop("disabled", false);
            $("#discount_" + fees_assign_detail_id).prop("disabled", false);
            $("#fine_" + fees_assign_detail_id).prop("disabled", false);

            var pending_amount = Number($('#pending_by_group_id_' + fees_assign_detail_id).attr('data-pending_amount'));
            var fine = Number($('#pending_by_group_id_' + fees_assign_detail_id).attr('data-fine'));

            if (pending_amount > 0) { 
                $('#amount_' + fees_assign_detail_id).val(pending_amount);
                $('#fine_' + fees_assign_detail_id).val(((pending_amount * fine) / 100).toFixed(2));
                $('#amount_' + fees_assign_detail_id).attr('data-fine', fine);
            }
        } else {
            $("#amount_" + fees_assign_detail_id).prop("disabled", true).val('');
            $("#discount_" + fees_assign_detail_id).prop("disabled", true).val('');
            $("#fine_" + fees_assign_detail_id).prop("disabled", true).val('0');
        }
        updateTotals();
    });

    // Realtime Amount input validation
    $(".amount_get").on("input", function () {
        let fees_assign_detail_id = $(this).attr("id").split("_")[1];
        let pendingAmount = Number($('#pending_by_group_id_' + fees_assign_detail_id).attr('data-pending_amount'));
        let finePercentage = Number($('#amount_' + fees_assign_detail_id).attr('data-fine'));
        let discountValue = Number($('#discount_' + fees_assign_detail_id).val());
        let currentValue = Number($(this).val());

        if (currentValue < 0 || isNaN(currentValue)) {
            $(this).val(0);
        } 

        let maxAllowedAmount = pendingAmount - discountValue;
        // Allow excess payment (amount > pending dues) to record advance collections
        let updatedFine = (Number($(this).val()) * finePercentage) / 100;
        $("#fine_" + fees_assign_detail_id).val(updatedFine.toFixed(2));
        updateTotals();
    });

    // Realtime Discount input validation
    $(".discounts").on("input", function () {
        let fees_assign_detail_id = $(this).attr("id").split("_")[1];
        let originalAmount = Number($('#pending_by_group_id_' + fees_assign_detail_id).attr('data-pending_amount'));
        let discountValue = Number($(this).val());

        if (discountValue < 0 || isNaN(discountValue)) {
            $(this).val(0);
        }
        if (discountValue > originalAmount) {
            $(this).val(originalAmount);
        }
        let newAmount = originalAmount - discountValue;
        $("#amount_" + fees_assign_detail_id).val(newAmount < 0 ? 0 : newAmount);
        updateTotals();
    });

    // Payment Mode toggle fields
    $('#payment_mode_id').on('change', function() {
        var payment_mode_id = $(this).val();
        $("#other_fee_remark").val('');
        $("#payment_status").val(0);
        $("#cheque_number_id").hide();
        $("#cheque_date_id").hide();
        $("#payment_receipt_id").hide();

        if (payment_mode_id == 1) { // Cash
            $('#transition_id_input').hide();
            $('#bank_name_input').hide();
            $('#bank_name').val('');
            $('#transition_id').val('');
        } else if (payment_mode_id == 2) { // Cheque
            $("#cheque_number_id").show();
            $("#cheque_date_id").show();
            $("#payment_receipt_id").show();
            $("#bank_name_input").show();
            $("#payment_status").val(1);
            $("#other_fee_remark").val('This cheque is pending realisation.');
        } else { // Online / DD / Bank Transfer
            $('#transition_id_input').show();
            $('#bank_name_input').show();
            $('#payment_receipt_id').show();
            $('#bank_name').val('');
            $('#transition_id').val('');
        }
    });

    // Confirmation Modal Triggers on Collect Buttons
    $('.collect_trigger_btn').on('click', function(e) {
        e.preventDefault();
        pendingSubmitAction = $(this).data('action');

        let checkedCount = $('.selected_head:checked').length;
        if (checkedCount === 0) {
            toastr.error('Please select at least one fee head to collect.');
            return;
        }

        let grandTotal = parseFloat($('#g_total').text()) || 0;
        if (grandTotal <= 0) {
            toastr.error('Total collection amount must be greater than zero.');
            return;
        }

        // Populate Confirmation Modal Details
        $('#modal_student_name').text($('#student_name_hidden').val());
        $('#modal_admission_no').text($('#admission_no_hidden').val());
        $('#modal_receipt_no').text($('#form_slip_no').val());
        $('#modal_payment_mode').text($('#payment_mode_id option:selected').text());
        $('#modal_grand_total').text(grandTotal.toFixed(2));

        var selectedHeadNames = [];
        $('.selected_head:checked').each(function() {
            var hName = $(this).data('head_name') || 'Fee Head';
            var detailId = $(this).data('fees_assign_detail_id');
            var hAmt = $('#amount_' + detailId).val() || '0';
            selectedHeadNames.push(hName + ' (₹' + hAmt + ')');
        });
        $('#modal_selected_heads').html(selectedHeadNames.join(', '));

        if (pendingSubmitAction === 'print') {
            $('#btn_modal_confirm_submit').html('<i class="fa fa-print mr-1"></i> Confirm &amp; Print');
        } else {
            $('#btn_modal_confirm_submit').html('<i class="fa fa-check mr-1"></i> Confirm &amp; Collect');
        }

        $('#collectConfirmModal').modal('show');
    });

    // Actual submission after confirming in modal
    $('#btn_modal_confirm_submit').on('click', function() {
        $('#collectConfirmModal').modal('hide');
        showLoadingOverlay('Processing Fee Payment', 'Recording ledger entry and generating invoice...');

        var formData = new FormData($('#myForm')[0]);
        if (pendingSubmitAction === 'print') {
            formData.append('print', 'print');
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: BASEURL + '/student_pay_submit',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                hideLoadingOverlay();
                if (data.status == 'success') {
                    toastr.success('Fee Collected Successfully!');
                    showData(data.unique_system_id, data.session_id);

                    if (pendingSubmitAction === 'print') {
                        var fees_details_invoice_id = data.fees_details_invoice_id;
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'post',
                            url: BASEURL + '/printFeesInvoice',
                            data: { fees_details_invoice_id: fees_details_invoice_id },
                            success: function(response) {
                                var printWindow = window.open('', '_blank');
                                printWindow.document.open();
                                printWindow.document.write('<html><head><title>Print Invoice</title>');
                                printWindow.document.write('<style>body{font-size:12px;} table{font-size:12px;}</style>');
                                printWindow.document.write('</head><body>');
                                printWindow.document.write(response);
                                printWindow.document.write('</body></html>');
                                printWindow.document.close();
                                printWindow.onload = function() {
                                    printWindow.focus();
                                    printWindow.print();
                                    printWindow.close();
                                };
                            }
                        });
                    }
                } else {
                    toastr.error('Something went wrong during payment.');
                }
            },
            error: function(xhr) {
                hideLoadingOverlay();
                var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Unable to collect fee.';
                toastr.error(message);
            }
        });
    });

    // Revert Fees Handler
    $(document).on('click', '.revert_fees', function(){
        var fees_invoice_id = $(this).data('id'); 
        var admission_id = $(this).data('admission_id'); 
        var sessionID_ = $(this).data('session_id'); 
        var invNo = $(this).data('invoice_no') || 'Invoice #' + fees_invoice_id;
        var amt = $(this).data('amount') || '0';

        $('#fees_invoice_id').val(fees_invoice_id);
        $('#admissionId').val(admission_id);
        $('#sessionID_').val(sessionID_);
        $('#revert_inv_label').text(invNo);
        $('#revert_amt_label').text('₹' + amt);
    });

    $('#revert_fees_form').submit(function(event){
        event.preventDefault();
        $('#revert_modal').modal('hide');
        showLoadingOverlay('Reverting Fee Invoice', 'Restoring student balance and canceling receipt...');

        var formData = $('#revert_fees_form').serialize(); 
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: BASEURL + '/collect_fees_delete',
            data: formData,
            success: function(data) {
                hideLoadingOverlay();
                if(data.status == 'success'){
                    toastr.success('Fee Reverted Successfully');
                    setTimeout(function() {
                        showData(data.unique_system_id, data.session_id);
                    }, 500);
                } else {
                    toastr.error('Failed to revert fee.');
                }
            },
            error: function() {
                hideLoadingOverlay();
                toastr.error('Error occurred while reverting fee.');
            }
        });
    });

    // Academic Session Tab Clicking
    $(document).off('click', '.tab').on('click', '.tab', function(){
        $('.tab').removeClass('active-tab');
        $(this).addClass('active-tab');
        var admission_id = $(this).data('admission_id');
        var session_id = $(this).data('id');
        var unique_system_id = $(this).data('unique_system_id');
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('session_id', session_id);
        if (admission_id) currentUrl.searchParams.set('admission_id', admission_id);
        window.history.replaceState({}, '', currentUrl.toString());
        
        if (admission_id || session_id != "") {
            showData(unique_system_id, session_id, admission_id);
        }
    });

    $('input[name="yesNoAdvance"]').click(function(){
        if (this.value === "yes") {
            $('#payment_mode_id').val(9);
            $('#aggregate_amount').val('');
            $('#advance_payment').val('yes');
        } else if (this.value === "no") {
            $('#payment_mode_id').val(1);
            $('#advance_payment').val('no');
        }
    });

    // Toggle Edit Semester Remark
    $('#btn_toggle_edit_semester_remark').on('click', function() {
        $('#semester_remark_display_wrapper').hide();
        $('#semester_remark_edit_wrapper').fadeIn(150);
        $('#semester_remark_textarea').focus();
    });

    // Cancel Edit Semester Remark
    $('#btn_cancel_semester_remark').on('click', function() {
        $('#semester_remark_edit_wrapper').hide();
        $('#semester_remark_display_wrapper').fadeIn(150);
    });

    // Save Semester Remark via AJAX
    $('#btn_save_semester_remark').on('click', function() {
        let admissionId = $(this).data('admission-id');
        let newRemark = $('#semester_remark_textarea').val().trim();
        let saveBtn = $(this);
        saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: BASEURL + '/updateStudentFeeRemark',
            data: {
                admission_id: admissionId,
                remark: newRemark
            },
            success: function(res) {
                saveBtn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save Remark');
                if (res.status === 'success') {
                    toastr.success('Semester fee remark updated successfully');
                    if (newRemark !== '') {
                        $('#semester_remark_display_wrapper').html(
                            '<div class="p-2 rounded mt-1" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3px solid #217346; color: #166534; font-size: 11.5px; line-height: 1.45;">' +
                            '<i class="fa fa-quote-left text-success mr-1" style="opacity: 0.6; font-size: 10px;"></i>' +
                            '<span id="semester_remark_text_span" class="font-weight-600">' + $('<div>').text(newRemark).html() + '</span>' +
                            '<i class="fa fa-quote-right text-success ml-1" style="opacity: 0.6; font-size: 10px;"></i>' +
                            '</div>'
                        );
                    } else {
                        $('#semester_remark_display_wrapper').html(
                            '<div class="p-2 rounded mt-1 text-muted font-italic" style="background: #f8fafc; border: 1px dashed #cbd5e1; font-size: 11px;">' +
                            '<i class="fa fa-info-circle mr-1 text-secondary"></i>' +
                            '<span id="semester_remark_text_span">No remark recorded for this semester fee assignment.</span>' +
                            '</div>'
                        );
                    }
                    $('#semester_remark_edit_wrapper').hide();
                    $('#semester_remark_display_wrapper').show();
                } else {
                    toastr.error(res.message || 'Failed to update remark');
                }
            },
            error: function() {
                saveBtn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Save Remark');
                toastr.error('Error occurred while saving semester remark');
            }
        });
    });
});

function updateTotals() {
    let totalAmount = 0;
    let totalDiscount = 0;
    let totalFine = 0;

    $(".selected_head:checked").each(function () {
        let fees_assign_detail_id = $(this).data("fees_assign_detail_id");
        let amount = Number($("#amount_" + fees_assign_detail_id).val()) || 0;
        let discount = Number($("#discount_" + fees_assign_detail_id).val()) || 0;
        let fine = Number($("#fine_" + fees_assign_detail_id).val()) || 0;

        totalAmount += amount;
        totalDiscount += discount;
        totalFine += fine;
    });

    $("#total_amount").val(totalAmount.toFixed(2));
    $("#total_fine").val(totalFine.toFixed(2));
    $("#discount_given").val(totalDiscount.toFixed(2));

    $("#aggregate").text(totalAmount.toFixed(2));
    $("#d_given").text(totalDiscount.toFixed(2));
    $("#f_given").text(totalFine.toFixed(2));
    $("#g_total").text(((totalAmount - totalDiscount) + totalFine).toFixed(2));
}
</script>
