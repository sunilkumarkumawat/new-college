<div class="modal-header py-2 bg-primary text-white" style="background-color: #002c54 !important;">
    <h5 class="modal-title font-weight-bold" style="font-size: 15px; color: #ffffff !important;">
        <i class="fa fa-user-circle mr-1"></i> Fee Modification: {{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }} ({{ $student->admissionNo ?? '-' }})
    </h5>
    <button type="button" class="close text-white btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; color: #fff; outline: none;">
        <span aria-hidden="true" style="font-size: 22px;">&times;</span>
    </button>
</div>

<div class="modal-body p-3" style="background-color: #f8fafc;" data-admission-id="{{ $student->id }}">
    <!-- Student Summary Ribbon -->
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="p-2 border rounded bg-white shadow-xs">
                <small class="text-muted d-block font-weight-bold" style="font-size: 11px; text-transform: uppercase;">Class / Semester</small>
                <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $student->ClassTypes->name ?? '-' }}</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="p-2 border rounded bg-white shadow-xs">
                <small class="text-muted d-block font-weight-bold" style="font-size: 11px; text-transform: uppercase;">Student Type</small>
                <span class="badge badge-info px-2 py-1" style="font-size: 11.5px;">{{ $student->student_type ?? 'General' }}</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="p-2 border rounded bg-white shadow-xs">
                <small class="text-muted d-block font-weight-bold" style="font-size: 11px; text-transform: uppercase;">Total Assigned</small>
                <span class="font-weight-bold text-primary" style="font-size: 13.5px;" id="modal_summary_total" data-total-assigned="{{ floatval($feesAssign->total_amount ?? 0) }}">
                    ₹{{ ($feesAssign->total_amount == floor($feesAssign->total_amount ?? 0)) ? number_format($feesAssign->total_amount ?? 0, 0) : number_format($feesAssign->total_amount ?? 0, 2) }}
                </span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="p-2 border rounded bg-white shadow-xs">
                <small class="text-muted d-block font-weight-bold" style="font-size: 11px; text-transform: uppercase;">Net Payable</small>
                <span class="font-weight-bold text-success" style="font-size: 13.5px;" id="modal_summary_net">
                    ₹{{ ($feesAssign->net_amount == floor($feesAssign->net_amount ?? 0)) ? number_format($feesAssign->net_amount ?? 0, 0) : number_format($feesAssign->net_amount ?? 0, 2) }}
                </span>
            </div>
        </div>
    </div>

    @if(!empty($assignedDetails) && count($assignedDetails) > 0)
        <!-- Total Discount & Auto Allocation Panel (Horizontally Aligned Controls) -->
        <div class="card mb-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #eef4f9 0%, #e2edf8 100%); border: 1px solid #cbd5e1 !important; border-radius: 8px;">
            <div class="card-body p-2">
                <div class="row align-items-end g-2">
                    <!-- 1. Discount Type -->
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                        <label class="font-weight-bold text-dark mb-1 small d-block" style="font-size: 11px; white-space: nowrap;">
                            <i class="fa fa-sliders text-primary mr-1"></i> Discount Type
                        </label>
                        <select id="modal_discount_type" class="form-control form-control-sm" style="height: 34px; font-weight: 600; border-radius: 6px;">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>

                    <!-- 2. Calculate On Basis -->
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                        <label class="font-weight-bold text-dark mb-1 small d-block" style="font-size: 11px; white-space: nowrap;">
                            <i class="fa fa-calculator text-primary mr-1"></i> Calculate On
                        </label>
                        <select id="modal_discount_basis" class="form-control form-control-sm" style="height: 34px; font-weight: 600; border-radius: 6px;">
                            <option value="checked">Checked Heads</option>
                            <option value="total_assigned">Total Assigned Fees</option>
                        </select>
                    </div>

                    <!-- 3. Discount Input -->
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                        <label class="font-weight-bold text-dark mb-1 small d-block" id="lbl_discount_input" style="font-size: 11px; white-space: nowrap;">
                            <i class="fa fa-tag text-primary mr-1"></i> Discount Value
                        </label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold bg-white text-primary" id="modal_discount_addon" style="height: 34px; border-radius: 6px 0 0 6px;">%</span>
                            </div>
                            <input type="number" 
                                   id="modal_total_discount_value" 
                                   class="form-control form-control-sm" 
                                   placeholder="e.g. 10" 
                                   min="0" 
                                   step="1" 
                                   style="height: 34px; font-weight: 600; border-radius: 0 6px 6px 0;">
                        </div>
                    </div>

                    <!-- 4. Auto Allocate Button -->
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                        <label class="d-none d-md-block" style="font-size: 11px; visibility: hidden;">Action</label>
                        <button type="button" 
                                id="btn_auto_allocate_discount" 
                                class="btn btn-sm btn-primary w-100 font-weight-bold shadow-xs d-flex align-items-center justify-content-center" 
                                style="height: 34px; border-radius: 6px;">
                            <i class="fa fa-magic mr-1"></i> Auto Allocate
                        </button>
                    </div>

                    <!-- 5. Clear Discounts Button -->
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                        <label class="d-none d-md-block" style="font-size: 11px; visibility: hidden;">Reset</label>
                        <button type="button" 
                                id="btn_reset_discount" 
                                class="btn btn-sm btn-outline-secondary w-100 font-weight-bold shadow-xs d-flex align-items-center justify-content-center" 
                                style="height: 34px; border-radius: 6px;">
                            <i class="fa fa-eraser mr-1"></i> Clear Disc.
                        </button>
                    </div>

                    <!-- 6. Save All Changes Button (Top Quick Action) -->
                    <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-12 mb-1">
                        <label class="d-none d-md-block" style="font-size: 11px; visibility: hidden;">Save</label>
                        <button type="button" 
                                class="btn btn-sm btn-success w-100 font-weight-bold shadow-xs d-flex align-items-center justify-content-center btn-save-all-batch" 
                                data-admission-id="{{ $student->id }}"
                                style="height: 34px; border-radius: 6px;">
                            <i class="fa fa-check-circle mr-1"></i> Save All
                        </button>
                    </div>
                </div>

                <div class="mt-1 d-flex justify-content-between align-items-center text-muted px-1" style="font-size: 11.5px;">
                    <span>
                        <i class="fa fa-info-circle text-info mr-1"></i> 
                        <span id="discount_explanation_text">Discount will be calculated on <b>Checked Heads</b> and distributed equally among them.</span>
                    </span>
                    <span id="modal_selected_heads_count" class="badge badge-primary px-2 py-1" style="font-size: 11px;">
                        {{ count($assignedDetails) }} Heads Selected
                    </span>
                </div>
            </div>
        </div>

        <!-- Assigned Fee Heads Table (Fine Column Hidden) -->
        <div class="table-responsive border rounded bg-white shadow-xs">
            <table class="table table-bordered table-sm mb-0" id="table_student_fee_edit" style="font-size: 12.5px;">
                <thead style="background-color: #002c54; color: #ffffff;">
                    <tr>
                        <th style="width: 35px; text-align: center;">
                            <input type="checkbox" id="modal_check_all_heads" checked title="Select / Deselect All">
                        </th>
                        <th style="width: 35px; text-align: center;">#</th>
                        <th>Fee Head</th>
                        <th style="width: 125px;">Amount (₹)</th>
                        <th style="width: 125px;">Discount (₹)</th>
                        <th style="width: 125px;">Net (₹)</th>
                        <th style="width: 145px;">Due Date</th>
                        <th style="width: 85px; text-align: center;">Paid</th>
                        <th style="width: 85px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedDetails as $k => $detail)
                        @php
                            $headTotal = floatval($detail->fees_group_amount ?? 0);
                            $headDisc = floatval($detail->discount ?? 0);
                            $headNet = $headTotal - $headDisc;
                        @endphp
                        <tr id="row_detail_{{ $detail->id }}" class="fee-detail-row">
                            <td class="text-center align-middle">
                                <input type="checkbox" 
                                       class="modal-head-select" 
                                       data-detail-id="{{ $detail->id }}" 
                                       checked>
                            </td>
                            <td class="text-center align-middle text-muted">{{ $k + 1 }}</td>
                            <td class="align-middle font-weight-bold text-dark">
                                {{ $detail->fees_group_name ?? 'Fee Head' }}
                            </td>
                            <td class="align-middle">
                                <input type="number" 
                                       step="1" 
                                       min="0"
                                       class="form-control form-control-sm text-right modal-detail-input input-amount" 
                                       data-detail-id="{{ $detail->id }}" 
                                       value="{{ ($headTotal == floor($headTotal)) ? intval($headTotal) : $headTotal }}" 
                                       style="font-size: 12px; height: 28px; padding: 2px 6px; font-weight: 600;">
                            </td>
                            <td class="align-middle">
                                <input type="number" 
                                       step="1" 
                                       min="0"
                                       class="form-control form-control-sm text-right modal-detail-input input-discount" 
                                       data-detail-id="{{ $detail->id }}" 
                                       value="{{ ($headDisc == floor($headDisc)) ? intval($headDisc) : $headDisc }}" 
                                       style="font-size: 12px; height: 28px; padding: 2px 6px; font-weight: 600;">
                            </td>
                            <td class="align-middle text-right font-weight-bold text-dark net-display" id="net_disp_{{ $detail->id }}">
                                ₹{{ ($headNet == floor($headNet)) ? number_format($headNet, 0) : number_format($headNet, 2) }}
                            </td>
                            <td class="align-middle">
                                <input type="date" 
                                       class="form-control form-control-sm modal-detail-input input-due-date" 
                                       data-detail-id="{{ $detail->id }}" 
                                       value="{{ $detail->installment_due_date ? date('Y-m-d', strtotime($detail->installment_due_date)) : '' }}" 
                                       style="font-size: 11.5px; height: 28px; padding: 2px 4px;">
                                <!-- Hidden Fine Field -->
                                <input type="hidden" 
                                       class="input-fine" 
                                       data-detail-id="{{ $detail->id }}" 
                                       value="{{ floatval($detail->installment_fine ?? 0) }}">
                            </td>
                            <td class="align-middle text-center">
                                @if(($detail->paid_amount ?? 0) > 0)
                                    <span class="badge badge-success px-1 py-1" style="font-size: 11px;">₹{{ number_format($detail->paid_amount, 0) }}</span>
                                @else
                                    <span class="badge badge-light text-muted border px-1 py-1" style="font-size: 11px;">₹0</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <button type="button" 
                                        class="btn btn-sm btn-primary btn-save-detail-row px-2 py-0" 
                                        data-detail-id="{{ $detail->id }}" 
                                        data-admission-id="{{ $student->id }}"
                                        title="Save this row individually"
                                        style="font-size: 11px; height: 26px; line-height: 24px;">
                                    <i class="fa fa-save"></i> Save
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-warning text-center py-4 mb-0">
            <i class="fa fa-exclamation-circle fa-2x mb-2 d-block"></i>
            <strong>No Fee Heads Currently Assigned</strong>
            <p class="small text-muted mb-0 mt-1">Please close this modal and check the fee head checkboxes on the main table to assign fee heads to this student.</p>
        </div>
    @endif
</div>

<div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
    <div>
        @if(!empty($assignedDetails) && count($assignedDetails) > 0)
            <button type="button" 
                    class="btn btn-success btn-sm font-weight-bold px-3 shadow-sm btn-save-all-batch" 
                    id="btn_save_all_modal_details" 
                    data-admission-id="{{ $student->id }}">
                <i class="fa fa-check-circle mr-1"></i> Save All Changes
            </button>
        @endif
    </div>
    <button type="button" class="btn btn-secondary btn-sm px-3 btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal">
        <i class="fa fa-times mr-1"></i> Close
    </button>
</div>
