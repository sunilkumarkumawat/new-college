<div class="modal-header py-2" style="background: #217346 !important; color: #ffffff !important; border-bottom: 1px solid #185c37 !important; border-radius: 0 !important; display: flex !important; align-items: center !important; justify-content: space-between !important;">
    <h5 class="modal-title font-weight-bold mb-0" style="font-size: 14.5px; color: #ffffff !important; letter-spacing: 0.2px;">
        <i class="fa fa-sliders mr-1 text-white"></i> Fee & Discount Modification: <span class="text-white">{{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</span> ({{ $student->admissionNo ?? '-' }})
    </h5>
    <button type="button" class="close text-white btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; color: #ffffff !important; text-shadow: none; font-size: 20px; line-height: 1; border: 0; background: transparent; cursor: pointer;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

@php
    $modalTotalDiscount = !empty($assignedDetails) ? $assignedDetails->sum('discount') : 0;
    $modalTotalAssigned = floatval($feesAssign->total_amount ?? 0);
    $modalNetPayable = floatval($feesAssign->net_amount ?? ($modalTotalAssigned - $modalTotalDiscount));
@endphp

<div class="modal-body p-2" style="background-color: #ffffff;" data-admission-id="{{ $student->id }}">
    <!-- Excel Student Summary Ribbon Panel -->
    <div class="d-flex flex-wrap align-items-center mb-2" style="border: 1px solid #b8c4d1; background: #f8fafc; font-size: 11.5px; border-radius: 2px; overflow: hidden;">
        <div class="p-2 border-right flex-fill" style="border-color: #cbd5e1 !important;">
            <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; line-height: 1.1;">Class / Semester</small>
            <span class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $student->ClassTypes->name ?? '-' }}</span>
        </div>
        <div class="p-2 border-right flex-fill" style="border-color: #cbd5e1 !important;">
            <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; line-height: 1.1;">Student Type</small>
            <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 11px; padding: 2px 6px;">{{ $student->student_type ?? 'General' }}</span>
        </div>
        <div class="p-2 border-right flex-fill" style="border-color: #cbd5e1 !important;">
            <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; line-height: 1.1;">Gender</small>
            @if((int)($student->gender_id ?? 0) === 1)
                <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 11px; padding: 2px 6px; background: #eff6ff;">👦 Boy</span>
            @elseif((int)($student->gender_id ?? 0) === 2)
                <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 11px; padding: 2px 6px; background: #fff1f2;">👧 Girl</span>
            @else
                <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 11px; padding: 2px 6px;">-</span>
            @endif
        </div>
        <div class="p-2 border-right flex-fill" style="border-color: #cbd5e1 !important;">
            <small class="text-muted d-block font-weight-bold text-uppercase" style="font-size: 10px; line-height: 1.1;">Total Assigned</small>
            <span class="font-weight-bold text-dark" style="font-size: 13px;" id="modal_summary_total" data-total-assigned="{{ $modalTotalAssigned }}">
                ₹{{ ($modalTotalAssigned == floor($modalTotalAssigned)) ? number_format($modalTotalAssigned, 0) : number_format($modalTotalAssigned, 2) }}
            </span>
        </div>
        <div class="p-2 border-right flex-fill" style="border-color: #cbd5e1 !important;">
            <small class="text-danger d-block font-weight-bold text-uppercase" style="font-size: 10px; line-height: 1.1;">Total Discount</small>
            <span class="font-weight-bold text-danger" style="font-size: 13px;" id="modal_summary_discount">
                ₹{{ ($modalTotalDiscount == floor($modalTotalDiscount)) ? number_format($modalTotalDiscount, 0) : number_format($modalTotalDiscount, 2) }}
            </span>
        </div>
        <div class="p-2 flex-fill" style="background: #e2f0d9;">
            <small class="text-success d-block font-weight-bold text-uppercase" style="font-size: 10px; line-height: 1.1;">Net Payable</small>
            <span class="font-weight-bold text-success" style="font-size: 13.5px;" id="modal_summary_net">
                ₹{{ ($modalNetPayable == floor($modalNetPayable)) ? number_format($modalNetPayable, 0) : number_format($modalNetPayable, 2) }}
            </span>
        </div>
    </div>

    @if((!empty($assignedDetails) && count($assignedDetails) > 0) || (!empty($availableFeeMasters) && count($availableFeeMasters) > 0))
        <!-- Excel Unified Discount & Auto Allocation Toolbar -->
        <div class="mb-2 p-2 rounded" style="background: #e7eff5; border: 1px solid #b8c9d9;">
            <div class="row align-items-end g-1">
                <!-- 1. Discount Type -->
                <div class="col-md-2 col-6 mb-1 mb-md-0">
                    <label class="modal-label mb-1" style="font-size: 10.5px; font-weight: 700; color: #1e293b; text-transform: uppercase;">Discount Type</label>
                    <select id="modal_discount_type" class="form-control form-control-sm" style="height: 29px; font-size: 11.5px; border: 1px solid #b8c4d1; border-radius: 2px;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (₹)</option>
                    </select>
                </div>

                <!-- 2. Calculate On Basis -->
                <div class="col-md-2 col-6 mb-1 mb-md-0">
                    <label class="modal-label mb-1" style="font-size: 10.5px; font-weight: 700; color: #1e293b; text-transform: uppercase;">Calculate On</label>
                    <select id="modal_discount_basis" class="form-control form-control-sm" style="height: 29px; font-size: 11.5px; border: 1px solid #b8c4d1; border-radius: 2px;">
                        <option value="checked">Checked Heads</option>
                        <option value="total_assigned">Total Assigned Fees</option>
                    </select>
                </div>

                <!-- 3. Discount Input -->
                <div class="col-md-2 col-6 mb-1 mb-md-0">
                    <label class="modal-label mb-1" id="lbl_discount_input" style="font-size: 10.5px; font-weight: 700; color: #1e293b; text-transform: uppercase;">Discount Value</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold" id="modal_discount_addon" style="height: 29px; font-size: 11px; background: #ffffff; border: 1px solid #b8c4d1; border-radius: 2px 0 0 2px;">%</span>
                        </div>
                        <input type="number" 
                               id="modal_total_discount_value" 
                               class="form-control form-control-sm font-weight-bold text-dark" 
                               placeholder="e.g. 10" 
                               min="0" 
                               step="1" 
                               style="height: 29px; font-size: 11.5px; border: 1px solid #b8c4d1; border-radius: 0 2px 2px 0;">
                    </div>
                </div>

                <!-- Action Buttons Aligned Right -->
                <div class="col-md-6 col-12 d-flex justify-content-end align-items-center" style="gap: 5px;">
                    <button type="button" 
                            id="btn_auto_allocate_discount" 
                            class="btn btn-sm btn-primary font-weight-bold" 
                            style="height: 29px; font-size: 11px; border-radius: 2px; padding: 2px 10px; background: #0369a1; border-color: #0284c7;">
                        <i class="fa fa-magic mr-1"></i> Auto Allocate
                    </button>
                    <button type="button" 
                            id="btn_reset_discount" 
                            class="btn btn-sm btn-outline-danger font-weight-bold" 
                            style="height: 29px; font-size: 11px; border-radius: 2px; padding: 2px 10px; background: #ffffff;"
                            title="Set discounts to 0 for checked heads">
                        <i class="fa fa-eraser mr-1"></i> Clear
                    </button>
                    <button type="button" 
                            id="btn_revert_saved_data" 
                            class="btn btn-sm btn-outline-secondary font-weight-bold btn-revert-saved-data" 
                            style="height: 29px; font-size: 11px; border-radius: 2px; padding: 2px 10px; background: #ffffff;"
                            title="Reset / Revert all changes back to last saved state">
                        <i class="fa fa-undo mr-1"></i> Reset
                    </button>
                    <button type="button" 
                            class="btn btn-sm btn-success font-weight-bold btn-save-all-batch" 
                            data-admission-id="{{ $student->id }}"
                            style="height: 29px; font-size: 11px; border-radius: 2px; padding: 2px 12px; background: #217346; border-color: #185c37;">
                        <i class="fa fa-check mr-1"></i> Save All
                    </button>
                </div>
            </div>

            <div class="mt-2 pt-1 border-top d-flex justify-content-between align-items-center text-muted" style="border-color: #cbd5e1 !important; font-size: 11px;">
                <span>
                    <i class="fa fa-info-circle text-primary mr-1"></i> 
                    <span id="discount_explanation_text">Discount will be calculated on <b>Checked Heads</b> and distributed equally among them.</span>
                </span>
                <span id="modal_selected_heads_count" class="badge badge-light border text-dark font-weight-bold" style="font-size: 10.5px; padding: 2px 6px;">
                    {{ count($assignedDetails) }} Heads Selected
                </span>
            </div>
        </div>

        @php
            $assignedNameList = $assignedDetails->pluck('fees_group_name')->map(function($n) {
                return strtolower(trim(preg_replace('/\s+/', ' ', $n ?? '')));
            })->filter()->all();
            $filteredBladeMasters = collect($availableFeeMasters)->filter(function($m) use ($assignedNameList) {
                $gn = strtolower(trim(preg_replace('/\s+/', ' ', optional($m->feesGroup)->name ?? '')));
                return !empty($gn) && !str_contains($gn, 'previous dues') && !in_array($gn, $assignedNameList, true);
            });
        @endphp
        @if($filteredBladeMasters->count() > 0)
            <div class="d-flex align-items-center px-2 py-1 mb-2 rounded" id="modal_add_fee_head_panel" style="background: #f8fafc; border: 1px solid #cbd5e1; gap: 8px;">
                <span class="font-weight-bold text-success text-nowrap" style="font-size: 11.5px;">
                    <i class="fa fa-plus-circle mr-1"></i> Add Head:
                </span>
                <select id="modal_available_fee_head" class="form-control form-control-sm" style="height: 28px; font-size: 11.5px; border-radius: 2px;">
                    <option value="">-- Select an unassigned current-semester fee head --</option>
                    @foreach($filteredBladeMasters as $master)
                        @php
                            $genderId = (int)($student->gender_id ?? 0);
                            if ($student->student_type === 'NRI') {
                                $defaultAmount = $master->nri;
                            } elseif ($student->student_type === 'Management') {
                                $defaultAmount = $master->management;
                            } elseif ($student->student_type === 'Govt') {
                                $defaultAmount = $master->govt;
                            } elseif ($genderId === 1 && !empty($master->boys_amount)) {
                                $defaultAmount = $master->boys_amount;
                            } elseif ($genderId === 2 && !empty($master->girls_amount)) {
                                $defaultAmount = $master->girls_amount;
                            } else {
                                $defaultAmount = $master->amount;
                            }
                        @endphp
                        <option value="{{ $master->id }}" data-name="{{ optional($master->feesGroup)->name ?? 'Fee Head' }}" data-amount="{{ floatval($defaultAmount ?? 0) }}" data-due-date="{{ $master->installment_due_date ?? '' }}">
                            {{ optional($master->feesGroup)->name ?? 'Fee Head' }} — Default ₹{{ number_format($defaultAmount ?? 0, 0) }}
                        </option>
                    @endforeach
                </select>
                <button type="button" id="btn_add_fee_head_to_modal" class="btn btn-sm btn-success font-weight-bold text-nowrap px-3" style="height: 28px; font-size: 11px; border-radius: 2px; background: #217346; border-color: #185c37;">
                    <i class="fa fa-plus mr-1"></i> Add to Table
                </button>
            </div>
        @else
            <div class="d-flex align-items-center px-2 py-1 mb-2 rounded" id="modal_add_fee_head_panel" style="background: #f0fdf4; border: 1px dashed #86efac; gap: 8px;">
                <span class="text-success small font-weight-bold">
                    <i class="fa fa-check-circle text-success mr-1"></i> All fee heads for this semester/batch are already assigned to this student.
                </span>
            </div>
        @endif

        <!-- Assigned Fee Heads Spreadsheet Table -->
        <div class="table-responsive border" style="max-height: calc(100vh - 430px); min-height: 140px; overflow-y: auto;">
            <table class="table table-bordered table-sm mb-0" id="table_student_fee_edit" style="font-size: 11.5px; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background: #d9eaf7; color: #152536; border-bottom: 2px solid #8fa7b8;">
                        <th style="width: 38px; text-align: center; border-color: #8fa7b8;">
                            <input type="checkbox" id="modal_check_all_heads" checked title="Select / Deselect All" style="cursor: pointer; accent-color: #217346;">
                        </th>
                        <th style="width: 35px; text-align: center; border-color: #8fa7b8;">#</th>
                        <th style="border-color: #8fa7b8;">Fee Head</th>
                        <th style="width: 125px; text-align: right; border-color: #8fa7b8;">Amount (₹)</th>
                        <th style="width: 125px; text-align: right; border-color: #8fa7b8;">Discount (₹)</th>
                        <th style="width: 125px; text-align: right; border-color: #8fa7b8;">Net (₹)</th>
                        <th style="width: 140px; border-color: #8fa7b8;">Due Date</th>
                        <th style="width: 80px; text-align: center; border-color: #8fa7b8;">Paid</th>
                        <th style="width: 80px; text-align: center; border-color: #8fa7b8;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignedDetails as $k => $detail)
                        @php
                            $headTotal = floatval($detail->fees_group_amount ?? 0);
                            $headDisc = floatval($detail->discount ?? 0);
                            $headPaid = floatval($detail->paid_amount ?? 0);
                            $headNet = $headTotal - $headDisc;
                            $valAmount = ($headTotal == floor($headTotal)) ? intval($headTotal) : $headTotal;
                            $valDisc = ($headDisc == floor($headDisc)) ? intval($headDisc) : $headDisc;
                            $valDueDate = $detail->installment_due_date ? date('Y-m-d', strtotime($detail->installment_due_date)) : '';
                            $valFine = floatval($detail->installment_fine ?? 0);
                        @endphp
                        <tr id="row_detail_{{ $detail->id }}" class="fee-detail-row" data-master-id="{{ $detail->fees_master_id }}"
                            data-head-name="{{ $detail->fees_group_name ?? 'Fee Head' }}"
                            data-paid-amount="{{ $headPaid }}"
                            data-original-amount="{{ $valAmount }}"
                            data-original-discount="{{ $valDisc }}"
                            data-original-due-date="{{ $valDueDate }}"
                            data-original-fine="{{ $valFine }}">
                            <td class="text-center align-middle" style="border-color: #c6cdd3;">
                                <input type="checkbox" 
                                       class="modal-head-select" 
                                       data-detail-id="{{ $detail->id }}" 
                                       data-paid-amount="{{ $headPaid }}"
                                       checked
                                       style="cursor: pointer; accent-color: #217346;">
                            </td>
                            <td class="text-center align-middle text-muted" style="border-color: #c6cdd3;">{{ $k + 1 }}</td>
                            <td class="align-middle font-weight-bold text-dark" style="border-color: #c6cdd3;">
                                {{ $detail->fees_group_name ?? 'Fee Head' }}
                            </td>
                            <td class="align-middle" style="border-color: #c6cdd3;">
                                <input type="number" 
                                       step="1" 
                                       min="0"
                                       class="form-control form-control-sm text-right modal-detail-input input-amount font-weight-bold" 
                                       data-detail-id="{{ $detail->id }}" 
                                       data-paid-amount="{{ $headPaid }}"
                                       data-original-value="{{ $valAmount }}"
                                       value="{{ $valAmount }}" 
                                       style="font-size: 11.5px; height: 26px; padding: 2px 5px; border: 1px solid #b8c4d1; border-radius: 2px;">
                            </td>
                            <td class="align-middle" style="border-color: #c6cdd3;">
                                <input type="number" 
                                       step="1" 
                                       min="0"
                                       class="form-control form-control-sm text-right modal-detail-input input-discount font-weight-bold text-danger" 
                                       data-detail-id="{{ $detail->id }}" 
                                       data-paid-amount="{{ $headPaid }}"
                                       data-original-value="{{ $valDisc }}"
                                       value="{{ $valDisc }}" 
                                       style="font-size: 11.5px; height: 26px; padding: 2px 5px; border: 1px solid #b8c4d1; border-radius: 2px;">
                            </td>
                            <td class="align-middle text-right font-weight-bold text-dark net-display" id="net_disp_{{ $detail->id }}" style="border-color: #c6cdd3;">
                                ₹{{ ($headNet == floor($headNet)) ? number_format($headNet, 0) : number_format($headNet, 2) }}
                            </td>
                            <td class="align-middle" style="border-color: #c6cdd3;">
                                <input type="date" 
                                       class="form-control form-control-sm modal-detail-input input-due-date" 
                                       data-detail-id="{{ $detail->id }}" 
                                       data-original-value="{{ $valDueDate }}"
                                       value="{{ $valDueDate }}" 
                                       style="font-size: 11px; height: 26px; padding: 2px 4px; border: 1px solid #b8c4d1; border-radius: 2px;">
                                <!-- Hidden Fine Field -->
                                <input type="hidden" 
                                       class="input-fine" 
                                       data-detail-id="{{ $detail->id }}" 
                                       data-original-value="{{ $valFine }}"
                                       value="{{ $valFine }}">
                            </td>
                            <td class="align-middle text-center" style="border-color: #c6cdd3;">
                                @if(($detail->paid_amount ?? 0) > 0)
                                    <span class="badge badge-success px-1 py-1" style="font-size: 10.5px;">₹{{ number_format($detail->paid_amount, 0) }}</span>
                                @else
                                    <span class="badge badge-light text-muted border px-1 py-1" style="font-size: 10.5px;">₹0</span>
                                @endif
                            </td>
                            <td class="align-middle text-center" style="border-color: #c6cdd3;">
                                <div class="d-flex justify-content-center" style="gap: 3px;">
                                    <button type="button" class="btn btn-sm btn-primary btn-save-detail-row px-2 py-0" data-detail-id="{{ $detail->id }}" data-admission-id="{{ $student->id }}" title="Save this row individually" style="font-size: 10.5px; height: 24px; line-height: 22px; border-radius: 2px; background: #0369a1; border-color: #0284c7;">
                                        <i class="fa fa-save"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm {{ $headPaid > 0 ? 'btn-outline-secondary' : 'btn-outline-danger btn-delete-assigned-head' }} px-2 py-0"
                                            data-detail-id="{{ $detail->id }}" data-master-id="{{ $detail->fees_master_id }}" data-admission-id="{{ $student->id }}" data-head-name="{{ $detail->fees_group_name ?? 'Fee Head' }}"
                                            {{ $headPaid > 0 ? 'disabled' : '' }} title="{{ $headPaid > 0 ? 'Cannot delete: collection exists for this head' : 'Delete this uncollected fee head' }}" style="font-size: 10.5px; height: 24px; line-height: 22px; border-radius: 2px;">
                                        <i class="fa {{ $headPaid > 0 ? 'fa-lock' : 'fa-trash' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Common Fee / Concession Remark (Textarea 4-5 rows) -->
        <div class="mt-2 p-2 rounded" style="background: #fffbeb; border: 1px solid #fde68a;">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="modal_student_remark" class="mb-0 font-weight-bold" style="font-size: 11.5px; color: #92400e;">
                    <i class="fa fa-commenting mr-1 text-warning"></i> Fee / Concession Remark:
                </label>
                <button type="button" 
                        id="btn_save_modal_remark" 
                        class="btn btn-xs font-weight-bold px-2 py-0 btn-save-remark-only" 
                        data-admission-id="{{ $student->id }}" 
                        style="height: 24px; font-size: 11px; border-radius: 2px; background: #d97706; border-color: #b45309; color: #ffffff;"
                        title="Save remark immediately">
                    <i class="fa fa-save mr-1"></i> Save Remark
                </button>
            </div>
            <textarea id="modal_student_remark" 
                      name="remark" 
                      rows="4" 
                      class="form-control text-dark font-weight-normal" 
                      placeholder="Enter common fee / concession remark here (e.g. Concession reason, special approval, installment agreement)..." 
                      data-original-remark="{{ $feesAssign->remark ?? '' }}"
                      style="font-size: 12px; border-radius: 2px; border: 1px solid #fcd34d; background: #ffffff; resize: vertical; line-height: 1.4;">{{ $feesAssign->remark ?? '' }}</textarea>
        </div>
    @else
        <div class="alert alert-warning text-center py-4 mb-0" style="border-radius: 2px;">
            <i class="fa fa-exclamation-circle fa-2x mb-2 d-block text-warning"></i>
            <strong>No Fee Heads Currently Assigned</strong>
            <p class="small text-muted mb-0 mt-1">Please close this modal and check the fee head checkboxes on the main table to assign fee heads to this student.</p>
        </div>
    @endif
</div>

<div class="modal-footer py-2 px-3 d-flex justify-content-between" style="background: #f1f4f6; border-top: 1px solid #cbd5e1;">
    <div class="d-flex align-items-center" style="gap: 6px;">
        @if((!empty($assignedDetails) && count($assignedDetails) > 0) || (!empty($availableFeeMasters) && count($availableFeeMasters) > 0))
            <button type="button" 
                    class="btn btn-success btn-sm font-weight-bold px-3 btn-save-all-batch" 
                    id="btn_save_all_modal_details" 
                    data-admission-id="{{ $student->id }}"
                    style="height: 30px; font-size: 11.5px; border-radius: 2px; background: #217346; border-color: #185c37;">
                <i class="fa fa-check-circle mr-1"></i> Save All Changes
            </button>
            <button type="button" 
                    class="btn btn-outline-secondary btn-sm font-weight-bold px-3 btn-revert-saved-data" 
                    title="Reset / Revert all changes back to last saved database state"
                    style="height: 30px; font-size: 11.5px; border-radius: 2px; background: #ffffff;">
                <i class="fa fa-undo mr-1"></i> Reset to Saved
            </button>
        @endif
    </div>
    <button type="button" class="btn btn-secondary btn-sm px-3 btn-close-modal" data-dismiss="modal" data-bs-dismiss="modal" style="height: 30px; font-size: 11.5px; border-radius: 2px; background: #ffffff; color: #475569; border: 1px solid #94a3b8;">
        <i class="fa fa-times mr-1"></i> Close
    </button>
</div>
