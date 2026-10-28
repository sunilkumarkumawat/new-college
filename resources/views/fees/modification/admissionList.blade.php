@if(!empty($data) && count($data) > 0)
    @php
        $studentIds = $data->pluck('id')->toArray();
        $feesAssigns = DB::table('fees_assigns')
            ->whereNull('deleted_at')
            ->whereIn('admission_id', $studentIds)
            ->get()
            ->keyBy('admission_id');

        $studentDiscounts = DB::table('fees_assign_details')
            ->whereNull('deleted_at')
            ->whereIn('admission_id', $studentIds)
            ->groupBy('admission_id')
            ->select('admission_id', DB::raw('SUM(discount) as total_discount'), DB::raw('SUM(promotion_carry_adjustment) as total_carry_adjustment'), DB::raw('SUM(fees_group_amount) as total_gross'))
            ->get()
            ->keyBy('admission_id');

        $studentPaid = DB::table('fees_detail')
            ->whereIn('admission_id', $studentIds)
            ->whereIn('status', [0, 1])
            ->whereNull('deleted_at')
            ->groupBy('admission_id')
            ->select('admission_id', DB::raw('SUM(paid_amount) as total_paid'))
            ->get()
            ->keyBy('admission_id');

        $studentPaymentRows = DB::table('fees_detail')
            ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_detail.fees_group_id')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'fees_detail.payment_mode_id')
            ->whereIn('fees_detail.admission_id', $studentIds)
            ->whereIn('fees_detail.status', [0, 1])
            ->whereNull('fees_detail.deleted_at')
            ->where('fees_detail.paid_amount', '>', 0)
            ->select('fees_detail.*', 'fees_group.name as fee_head_name', 'payment_modes.name as payment_mode_name')
            ->orderBy('fees_detail.date')
            ->orderBy('fees_detail.id')
            ->get()
            ->groupBy('admission_id');

        $grandGross = 0;
        $grandDiscount = 0;
        $grandCarryAdjustment = 0;
        $grandNet = 0;
        $grandPaid = 0;
        $grandPending = 0;
        $grandExcess = 0;
    @endphp

    @foreach($data as $key => $item)
        @php
            $studentMasters = $courseFeesMasters->where('class_type_id', $item->class_type_id)->values();
            $assignedCurrentCount = $studentMasters->filter(function($master) use ($item, $assignedDetailMap) {
                return isset($assignedDetailMap[$item->id . '_' . $master->id])
                    || isset($assignedDetailMap[$item->id . '_group_' . $master->fees_group_id]);
            })->count();
            $assign = $feesAssigns[$item->id] ?? null;
            $grossTotal = (float)($assign->total_amount ?? ($studentDiscounts[$item->id]->total_gross ?? 0));
            $discountTotal = (float)($studentDiscounts[$item->id]->total_discount ?? 0);
            $carryAdjustmentTotal = (float)($studentDiscounts[$item->id]->total_carry_adjustment ?? 0);
            $netTotal = (float)($assign->net_amount ?? max(0, $grossTotal - $discountTotal - $carryAdjustmentTotal));
            $paidTotal = (float)($studentPaid[$item->id]->total_paid ?? 0);
            $pendingTotal = (float)max(0, $netTotal - $paidTotal);
            $excessTotal = (float)max(0, $paidTotal - $netTotal);
            $ledgerPayments = $studentPaymentRows->get($item->id, collect())->groupBy('fees_group_id');
            $ledgerHeads = $studentMasters->map(function($master) use ($item, $assignedDetailMap, $ledgerPayments) {
                $assigned = $assignedDetailMap[$item->id . '_' . $master->id]
                    ?? ($assignedDetailMap[$item->id . '_group_' . $master->fees_group_id] ?? null);
                if(!$assigned) return null;
                $gross = (float)($assigned->fees_group_amount ?? 0);
                $net = max(0, $gross - (float)($assigned->discount ?? 0) - (float)($assigned->promotion_carry_adjustment ?? 0));
                $payments = $ledgerPayments->get($master->fees_group_id, collect());
                $paid = (float)$payments->sum('paid_amount');
                return (object)[
                    'name' => optional($master->feesGroup)->name ?? 'Fee Head',
                    'fee' => $net,
                    'paid' => $paid,
                    'due' => max(0, $net - $paid),
                    'payments' => $payments,
                ];
            })->filter()->values();

            $grandGross += $grossTotal;
            $grandDiscount += $discountTotal;
            $grandCarryAdjustment += $carryAdjustmentTotal;
            $grandNet += $netTotal;
            $grandPaid += $paidTotal;
            $grandPending += $pendingTotal;
            $grandExcess += $excessTotal;
        @endphp
        <tr id="student_row_{{ $item->id }}" 
            class="student-fee-row" 
            data-admission-id="{{ $item->id }}" 
            data-gender="{{ $item->gender_id }}"
            data-class-id="{{ $item->class_type_id }}"
            data-class-name="{{ $item->ClassTypes->name ?? '-' }}"
            data-gross="{{ $grossTotal }}" 
            data-discount="{{ $discountTotal }}" 
            data-carry-adjustment="{{ $carryAdjustmentTotal }}"
            data-net="{{ $netTotal }}" 
            data-paid="{{ $paidTotal }}" 
            data-pending="{{ $pendingTotal }}"
            data-excess="{{ $excessTotal }}"
            data-balance-status="{{ $pendingTotal > 0 ? 'dues' : ($excessTotal > 0 ? 'excess' : 'cleared') }}">
            <td class="text-center align-middle col-fixed-check" style="width: 40px;">
                @if((int)$item->status === 1)
                    <input type="checkbox" name="admissionIds[]" class="student_select_checkbox" value="{{ $item->id }}" style="cursor: pointer; width: 16px; height: 16px;" />
                @else
                    <input type="checkbox" disabled style="width: 16px; height: 16px;" title="Historical semester is view only" />
                @endif
            </td>
            <td class="text-center align-middle font-weight-bold text-muted col-fixed-sr" style="width: 40px;">
                {{ $key + 1 }}
            </td>
            <td class="align-middle text-left text-dark col-fixed-student" style="width:230px;">
                <span class="d-block text-truncate font-weight-bold student-identity-name" style="max-width:220px;" title="{{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}">
                    {{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}
                </span>
                <div class="student-identity-meta">
                    <span class="student-admission-chip">{{ $item->admissionNo ?? '-' }}</span>
                    @if((int)($item->gender_id ?? 0) === 1)
                        <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 9.5px; padding: 1px 4px; background: #eff6ff;" title="Male / Boy">👦 Boy</span>
                    @elseif((int)($item->gender_id ?? 0) === 2)
                        <span class="badge badge-light border text-danger font-weight-bold" style="font-size: 9.5px; padding: 1px 4px; background: #fff1f2;" title="Female / Girl">👧 Girl</span>
                    @endif
                    <span class="student-mobile-text"><i class="fa fa-mobile mr-1"></i>{{ $item->mobile ?? '-' }}</span>
                </div>
                @if((int)$item->status !== 1)
                    <span class="badge badge-secondary px-1" style="font-size: 10px;">Historical</span>
                @endif
                @if(!empty($item->student_type) && $item->student_type != 'General')
                    <span class="badge badge-warning text-dark px-1" style="font-size: 10px; font-weight: 600;">
                        {{ $item->student_type }}
                    </span>
                @endif
            </td>
            <td class="text-center align-middle" style="font-size: 12px; width: 210px;">
                <span class="d-block font-weight-bold text-dark text-truncate" title="{{ optional(optional($item->ClassTypes)->course)->name ?? '-' }}">
                    {{ optional(optional($item->ClassTypes)->course)->name ?? '-' }}
                </span>
                <span class="badge badge-info px-2 py-1 mt-1" style="font-size: 10.5px;">
                    Batch: {{ $item->batch ?? '-' }}
                </span>
            </td>
            <td class="align-middle text-left text-muted" style="font-size: 12px; min-width: 120px;">
                {{ $item->father_name ?? '-' }}
            </td>
            @if(false)
            <td class="align-middle text-left assigned-heads-column" style="min-width: 270px;">
                @if(!empty($studentMasters) && count($studentMasters) > 0)
                    <!-- Student Row Mini Action Header -->
                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1" style="border-bottom: 1px dashed #cbd5e1;">
                        <span class="text-muted" style="font-size: 11px; font-weight: 600;">
                            <i class="fa fa-eye mr-1"></i> {{ $item->ClassTypes->name ?? 'Current Semester' }} Heads ({{ count($studentMasters) }})
                        </span>
                        <span class="badge badge-light border px-2 py-1">View only</span>
                    </div>

                    <div class="d-flex flex-wrap align-items-center" style="gap: 4px;">
                        @if($assignedCurrentCount === 0)
                            <span class="badge badge-warning text-dark px-2 py-1">No current-semester fee assigned</span>
                        @endif
                        @foreach($studentMasters as $fm)
                            @php
                                $mapKey = $item->id . '_' . $fm->id;
                                $groupKey = $item->id . '_group_' . $fm->fees_group_id;
                                $assignedRecord = $assignedDetailMap[$mapKey] ?? ($assignedDetailMap[$groupKey] ?? null);
                                $isAssigned = !empty($assignedRecord);
                                
                                if($item->student_type == "NRI"){
                                    $headAmt = $fm->nri ?? $fm->amount;
                                } elseif($item->student_type == "Management"){
                                    $headAmt = $fm->management ?? $fm->amount;
                                } elseif($item->student_type == "Govt"){
                                    $headAmt = $fm->govt ?? $fm->amount;
                                } else {
                                    $headAmt = $fm->amount;
                                    $genderId = (int)($item->gender_id ?? 0);
                                    if ($genderId === 1 && !empty($fm->boys_amount)) {
                                        $headAmt = (float)$fm->boys_amount;
                                    } elseif ($genderId === 2 && !empty($fm->girls_amount)) {
                                        $headAmt = (float)$fm->girls_amount;
                                    }
                                }
                                
                                if($isAssigned && isset($assignedRecord->fees_group_amount)){
                                    $headAmt = $assignedRecord->fees_group_amount;
                                }
                            @endphp
                            @if($isAssigned)
                            <label class="fee-head-chip {{ $isAssigned ? 'chip-assigned' : 'chip-unassigned' }}" 
                                   id="chip_{{ $item->id }}_{{ $fm->id }}"
                                   title="{{ $fm->feesGroup->name ?? 'Fee Head' }} (₹{{ number_format($headAmt, 0) }}) - Click to {{ $isAssigned ? 'Remove' : 'Assign' }}">
                                <span class="chip-name">{{ $fm->feesGroup->name ?? 'Head' }}</span>
                                <span class="chip-amt">₹{{ number_format($headAmt, 0) }}</span>
                                <span class="chip-status ml-1">
                                    <i class="fa fa-check text-success {{ $isAssigned ? '' : 'd-none' }}" id="icon_{{ $item->id }}_{{ $fm->id }}"></i>
                                </span>
                            </label>
                            @endif
                        @endforeach
                    </div>
                @else
                    <span class="text-muted small font-italic">No Fee Heads Configured for this Course</span>
                @endif
            </td>
            @endif
            <td class="text-center align-middle" style="min-width: 155px; padding: 6px 8px !important;" id="student_summary_td_{{ $item->id }}">
                <div class="student-fee-ledger-wrap">
                    <table class="table table-bordered mb-0 student-fee-ledger">
                        <thead>
                            <tr>
                                <th class="ledger-head">Head</th>
                                <th class="ledger-money">Fee</th>
                                <th class="ledger-money">Paid</th>
                                <th class="ledger-money">Dues</th>
                                <th class="ledger-receipt">Recp. No.</th>
                                <th class="ledger-date">Date</th>
                                <th class="ledger-mode">Mode</th>
                                <th class="ledger-money">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerHeads as $ledgerHead)
                                @php $headPayments = $ledgerHead->payments; $headRowspan = max(1, $headPayments->count()); @endphp
                                @if($headPayments->isEmpty())
                                    <tr>
                                        <td class="ledger-head">{{ $ledgerHead->name }}</td>
                                        <td class="ledger-money">{{ number_format($ledgerHead->fee, 0) }}</td>
                                        <td class="ledger-money text-success font-weight-bold">{{ number_format($ledgerHead->paid, 0) }}</td>
                                        <td class="ledger-money text-danger font-weight-bold">{{ number_format($ledgerHead->due, 0) }}</td>
                                        <td colspan="4" class="text-center text-muted">—</td>
                                    </tr>
                                @else
                                    @foreach($headPayments as $paymentIndex => $payment)
                                        <tr>
                                            @if($paymentIndex === 0)
                                                <td rowspan="{{ $headRowspan }}" class="ledger-head">{{ $ledgerHead->name }}</td>
                                                <td rowspan="{{ $headRowspan }}" class="ledger-money">{{ number_format($ledgerHead->fee, 0) }}</td>
                                                <td rowspan="{{ $headRowspan }}" class="ledger-money text-success font-weight-bold">{{ number_format($ledgerHead->paid, 0) }}</td>
                                                <td rowspan="{{ $headRowspan }}" class="ledger-money text-danger font-weight-bold">{{ number_format($ledgerHead->due, 0) }}</td>
                                            @endif
                                            <td class="ledger-receipt">
                                                @if(!empty($payment->receipt_no))
                                                    <a href="{{ url('feesReceipt/'.$payment->receipt_no.'/'.$item->id) }}" target="_blank">{{ $payment->receipt_no }}</a>
                                                @else - @endif
                                            </td>
                                            <td class="ledger-date">{{ !empty($payment->date) ? date('d-m-Y', strtotime($payment->date)) : '-' }}</td>
                                            <td class="ledger-mode">{{ $payment->payment_mode_name ?? 'Cash' }}</td>
                                            <td class="ledger-money font-weight-bold">{{ number_format($payment->paid_amount, 0) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No fee heads assigned for this semester.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="fee-summary-cell d-flex flex-column align-items-center justify-content-center" id="student_fee_summary_{{ $item->id }}" style="gap: 2.5px;">
                    <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11.5px;" title="Gross Assigned Amount">
                        <span class="text-muted font-weight-600">Gross:</span>
                        <span class="text-dark font-weight-bold">₹{{ number_format($grossTotal, 0) }}</span>
                    </div>
                    @if($discountTotal > 0)
                        <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #fffbeb; border-radius: 4px; border: 1px solid #fef3c7; font-size: 11.5px;" title="Discount Granted">
                            <span class="font-weight-600" style="color: #d97706 !important;"><i class="fa fa-tag mr-1"></i>Disc:</span>
                            <span class="text-danger font-weight-bold">-₹{{ number_format($discountTotal, 0) }}</span>
                        </div>
                    @endif
                    @if($carryAdjustmentTotal > 0)
                        <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #f3e8ff; border-radius: 4px; border: 1px solid #d8b4fe; font-size: 11.5px;" title="Outstanding transferred to the promoted semester Previous Dues head">
                            <span class="font-weight-600" style="color: #7e22ce !important;"><i class="fa fa-exchange mr-1"></i>Carried:</span>
                            <span class="font-weight-bold" style="color: #7e22ce !important;">₹{{ number_format($carryAdjustmentTotal, 0) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #e0f2fe; border-radius: 4px; border: 1px solid #bae6fd; font-size: 12px;" title="Net Payable Amount" id="student_net_{{ $item->id }}">
                        <span class="text-primary font-weight-700">Net:</span>
                        <span class="text-primary font-weight-bold">₹{{ number_format($netTotal, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #f0fdf4; border-radius: 4px; border: 1px solid #bbf7d0; font-size: 11.5px;" title="Total Collected Payment">
                        <span class="text-success font-weight-600"><i class="fa fa-check-circle mr-1"></i>Paid:</span>
                        <span class="text-success font-weight-bold">₹{{ number_format($paidTotal, 0) }}</span>
                    </div>
                    @if($pendingTotal > 0)
                        <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #fef2f2; border-radius: 4px; border: 1px solid #fecaca; font-size: 11.5px;" title="Remaining Pending Due">
                            <span class="text-danger font-weight-700"><i class="fa fa-clock-o mr-1"></i>Due:</span>
                            <span class="text-danger font-weight-bold">₹{{ number_format($pendingTotal, 0) }}</span>
                        </div>
                    @elseif($excessTotal > 0)
                        <div class="d-flex justify-content-between align-items-center w-100 px-2 py-0.5" style="background: #ecfdf5; border-radius: 4px; border: 1px solid #a7f3d0; font-size: 11.5px;" title="Advance / Excess Amount Paid">
                            <span class="text-success font-weight-700"><i class="fa fa-arrow-circle-up mr-1"></i>Advance:</span>
                            <span class="text-success font-weight-bold">+₹{{ number_format($excessTotal, 0) }}</span>
                        </div>
                    @elseif($grossTotal > 0)
                        <div class="d-flex justify-content-center align-items-center w-100 px-2 py-0.5" style="background: #dcfce7; border-radius: 4px; border: 1px solid #86efac; font-size: 11px;">
                            <span class="text-success font-weight-bold"><i class="fa fa-check mr-1"></i>Fully Cleared</span>
                        </div>
                    @endif
                </div>
            </td>
            @php
                $collectQuery = http_build_query(array_filter([
                    'admission_id' => $item->id,
                    'unique_system_id' => $item->unique_system_id ?? null,
                    'admission_no' => $item->admissionNo ?? null,
                    'course_id' => optional($item->ClassTypes)->course_id,
                    'class_type_id' => $item->class_type_id,
                    'batch' => $item->batch,
                    'session_id' => $item->session_id,
                ], function($value) { return $value !== null && $value !== ''; }));
            @endphp
            <td class="text-left align-middle col-remark" style="width: 170px; font-size: 11.5px; padding: 6px 8px !important;" id="student_remark_td_{{ $item->id }}" data-remark="{{ $assign->remark ?? '' }}">
                @if(!empty($assign->remark))
                    <div class="remark-badge p-1 px-2 rounded" style="background: #fefce8; border: 1px solid #fef08a; font-size: 11.5px; color: #854d0e; word-break: break-word; line-height: 1.3;" title="{{ $assign->remark }}">
                        <i class="fa fa-commenting-o mr-1 text-warning"></i><span>{{ $assign->remark }}</span>
                    </div>
                @else
                    <span class="text-muted small font-italic remark-empty" style="color: #94a3b8 !important;">— No Remark —</span>
                @endif
            </td>
            <td class="text-center align-middle text-nowrap col-actions" style="width:135px;">
                <div class="student-row-actions">
                <button type="button"
                        class="btn btn-xs btn-outline-info btn-overall-ledger font-weight-bold"
                        data-admission-id="{{ $item->id }}"
                        title="View complete semester-wise fee and receipt ledger">
                    <i class="fa fa-book mr-1"></i> Overall Ledger
                </button>
                <button type="button" 
                        class="btn btn-xs btn-outline-primary btn-edit-student-fees font-weight-bold" 
                        data-admission-id="{{ $item->id }}"
                        {{ (int)$item->status !== 1 ? 'disabled' : '' }}
                        title="{{ (int)$item->status !== 1 ? 'Historical semester is view only' : 'Modify Fees, Allocate Discounts & Adjust Due Dates' }}">
                    <i class="fa {{ (int)$item->status !== 1 ? 'fa-lock' : 'fa-sliders' }} mr-1"></i> {{ (int)$item->status !== 1 ? 'View Only' : 'Assign / Edit' }}
                </button>
                <a href="{{ url('Fees/add') }}?{{ $collectQuery }}" target="_blank" class="btn btn-xs btn-success font-weight-bold" title="Open fee collection for this student">
                    <i class="fa fa-inr mr-1"></i> Collect Fee
                </a>
                </div>
            </td>
            <td class="text-center align-middle col-fixed-balance" style="width:125px;" id="student_balance_cell_{{ $item->id }}">
                @if($pendingTotal > 0)
                    <span class="d-block text-muted font-weight-600" style="font-size:10px;">Outstanding</span>
                    <strong id="student_balance_{{ $item->id }}" class="text-danger d-block font-weight-bold" style="font-size:14px; line-height: 1.2;">
                        ₹{{ number_format($pendingTotal, 0) }}
                    </strong>
                    <span class="badge badge-danger px-1.5 py-0.5 mt-1" style="font-size: 9px; font-weight: 700;">Pending</span>
                @elseif($excessTotal > 0)
                    <span class="d-block font-weight-700 text-uppercase" style="font-size:9.5px; color:#047857;">Excess / Adv.</span>
                    <strong id="student_balance_{{ $item->id }}" class="text-success d-block font-weight-800" style="font-size:14px; line-height: 1.2;">
                        +₹{{ number_format($excessTotal, 0) }}
                    </strong>
                    <span class="badge badge-success px-1.5 py-0.5 mt-1" style="font-size: 9px; font-weight: 700; background-color: #10b981;">Advance Paid</span>
                @else
                    <span class="d-block text-muted font-weight-600" style="font-size:10px;">Balance</span>
                    <strong id="student_balance_{{ $item->id }}" class="text-secondary d-block font-weight-bold" style="font-size:14px; line-height: 1.2;">
                        ₹0
                    </strong>
                    <span class="badge badge-light border text-success px-1.5 py-0.5 mt-1" style="font-size: 9px; font-weight: 600;">Cleared</span>
                @endif
            </td>
        </tr>
    @endforeach

    @if(false)
    <!-- Legacy AJAX footer: the live footer now belongs to the table's permanent tfoot. -->
    <tr id="tfoot_summary_row" style="background-color: #002c54 !important; color: #ffffff !important; font-weight: 700; border-top: 2px solid #001a33 !important;">
        <td colspan="6" class="text-right align-middle py-2 px-3" style="font-size: 13px; color: #ffffff !important; letter-spacing: 0.3px; background-color: #002c54 !important;">
            <i class="fa fa-calculator mr-1" style="color: #38bdf8 !important;"></i> <span id="tfoot_summary_label" style="color: #ffffff !important; font-weight: 700;">TOTAL SUMMARY ({{ count($data) }} Students Loaded)</span>:
        </td>
        <td class="text-center align-middle py-2" style="font-size: 12px; background-color: #002c54 !important;">
            <span class="badge badge-light text-dark font-weight-bold px-2 py-1 shadow-xs" style="font-size: 11px; background-color: #f8fafc !important; color: #0f172a !important;">Overall Loaded Total</span>
        </td>
        <td class="align-middle py-2 px-2 text-nowrap" style="min-width: 155px; background-color: #00172d !important;">
            <div class="d-flex flex-column" style="gap: 2px; font-size: 11.5px; color: #ffffff !important;">
                <div class="d-flex justify-content-between">
                    <span style="color: #cbd5e1 !important; font-weight: 600;">Gross:</span>
                    <strong id="tfoot_total_gross" style="color: #ffffff !important; font-weight: 800;">₹{{ number_format($grandGross, 0) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span style="color: #fde047 !important; font-weight: 600;">Discount:</span>
                    <strong id="tfoot_total_discount" style="color: #facc15 !important; font-weight: 800;">-₹{{ number_format($grandDiscount, 0) }}</strong>
                </div>
                @if($grandCarryAdjustment > 0)
                    <div class="d-flex justify-content-between">
                        <span style="color: #d8b4fe !important; font-weight: 600;">Carried Forward:</span>
                        <strong style="color: #e9d5ff !important; font-weight: 800;">₹{{ number_format($grandCarryAdjustment, 0) }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between" style="border-top: 1px solid rgba(255,255,255,0.25); padding-top: 1px;">
                    <span style="color: #7dd3fc !important; font-weight: 700;">Net Fee:</span>
                    <strong id="tfoot_total_net" style="color: #38bdf8 !important; font-weight: 800;">₹{{ number_format($grandNet, 0) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span style="color: #86efac !important; font-weight: 700;">Total Paid:</span>
                    <strong id="tfoot_total_paid" style="color: #4ade80 !important; font-weight: 800;">₹{{ number_format($grandPaid, 0) }}</strong>
                </div>
                <div class="d-flex justify-content-between" style="border-top: 1px dashed rgba(255,255,255,0.25); padding-top: 1px;">
                    <span style="color: #fca5a5 !important; font-weight: 700;">Total Due:</span>
                    <strong id="tfoot_total_pending" style="color: #f87171 !important; font-weight: 800;">₹{{ number_format($grandPending, 0) }}</strong>
                </div>
            </div>
        </td>
        <td class="text-center align-middle py-2" style="background-color: #002c54 !important;">
            <button type="button" class="btn btn-xs btn-outline-light font-weight-bold px-2 py-1 shadow-xs" onclick="recalculatePagePaymentSummary()" title="Recalculate Summary" style="border-color: #94a3b8; color: #ffffff;">
                <i class="fa fa-refresh mr-1"></i> Refresh
            </button>
        </td>
    </tr>
    @endif
@else
    <tr class="text-center">
        <td colspan="9" class="py-5 text-muted">
            <i class="fa fa-info-circle text-warning fa-2x mb-2 d-block"></i>
            <span class="font-weight-bold" style="font-size: 13px;">No students found matching the selected criteria.</span>
        </td>
    </tr>
@endif
