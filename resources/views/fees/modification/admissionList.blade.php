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
            ->select('admission_id', DB::raw('SUM(discount) as total_discount'), DB::raw('SUM(fees_group_amount) as total_gross'))
            ->get()
            ->keyBy('admission_id');
    @endphp

    @foreach($data as $key => $item)
        @php
            $studentMasters = $courseFeesMasters;
            $assign = $feesAssigns[$item->id] ?? null;
            $grossTotal = $assign->total_amount ?? ($studentDiscounts[$item->id]->total_gross ?? 0);
            $discountTotal = $studentDiscounts[$item->id]->total_discount ?? 0;
            $netTotal = $assign->net_amount ?? ($grossTotal - $discountTotal);
        @endphp
        <tr id="student_row_{{ $item->id }}">
            <td class="text-center align-middle" style="width: 40px;">
                <input type="checkbox" name="admissionIds[]" class="student_select_checkbox" value="{{ $item->id }}" style="cursor: pointer; width: 16px; height: 16px;" />
            </td>
            <td class="text-center align-middle font-weight-bold text-muted" style="width: 40px;">
                {{ $key + 1 }}
            </td>
            <td class="align-middle text-left font-weight-bold text-dark" style="min-width: 140px;">
                <span class="d-block text-truncate" style="max-width: 180px;" title="{{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}">
                    {{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}
                </span>
                @if(!empty($item->student_type) && $item->student_type != 'General')
                    <span class="badge badge-warning text-dark px-1" style="font-size: 10px; font-weight: 600;">
                        {{ $item->student_type }}
                    </span>
                @endif
            </td>
            <td class="text-center align-middle" style="width: 110px;">
                <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11.5px; letter-spacing: 0.3px;">
                    {{ $item->admissionNo ?? '-' }}
                </span>
            </td>
            <td class="text-center align-middle text-nowrap" style="font-size: 12px; width: 120px;">
                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1 d-block mb-1">
                    {{ $item->ClassTypes->name ?? '-' }}
                </span>
                @if(!empty($item->batch))
                    <span class="badge badge-info px-1 py-0" style="font-size: 10.5px;">
                        {{ $item->batch }}
                    </span>
                @endif
            </td>
            <td class="align-middle text-left text-muted" style="font-size: 12px; min-width: 120px;">
                {{ $item->father_name ?? '-' }}
            </td>
            <td class="text-center align-middle text-nowrap" style="font-size: 12px; width: 100px;">
                {{ $item->mobile ?? '-' }}
            </td>
            <td class="align-middle text-left" style="min-width: 270px;">
                @if(!empty($studentMasters) && count($studentMasters) > 0)
                    <!-- Student Row Mini Action Header -->
                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1" style="border-bottom: 1px dashed #cbd5e1;">
                        <span class="text-muted" style="font-size: 11px; font-weight: 600;">
                            <i class="fa fa-list-ul mr-1"></i> Course Heads ({{ count($studentMasters) }})
                        </span>
                        <div>
                            <button type="button" class="btn btn-xs btn-outline-success font-weight-bold px-1 py-0 mr-1 btn-student-assign-all-heads" 
                                    data-admission-id="{{ $item->id }}" 
                                    data-student-name="{{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}"
                                    data-admission-no="{{ $item->admissionNo ?? '-' }}"
                                    data-total-heads="{{ count($studentMasters) }}"
                                    title="Assign all course fee heads to this student" style="font-size: 10.5px;">
                                <i class="fa fa-check-square-o"></i> Check All
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold px-1 py-0 btn-student-unassign-all-heads" 
                                    data-admission-id="{{ $item->id }}" 
                                    data-student-name="{{ $item->first_name ?? '' }} {{ $item->last_name ?? '' }}"
                                    data-admission-no="{{ $item->admissionNo ?? '-' }}"
                                    title="Clear all fee heads for this student" style="font-size: 10.5px;">
                                <i class="fa fa-square-o"></i> Clear
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center" style="gap: 4px;">
                        @foreach($studentMasters as $fm)
                            @php
                                $mapKey = $item->id . '_' . $fm->id;
                                $isAssigned = isset($assignedDetailMap[$mapKey]);
                                
                                if($item->student_type == "NRI"){
                                    $headAmt = $fm->nri ?? $fm->amount;
                                } elseif($item->student_type == "Management"){
                                    $headAmt = $fm->management ?? $fm->amount;
                                } elseif($item->student_type == "Govt"){
                                    $headAmt = $fm->govt ?? $fm->amount;
                                } else {
                                    $headAmt = $fm->amount;
                                }
                                
                                if($isAssigned && isset($assignedDetailMap[$mapKey]->fees_group_amount)){
                                    $headAmt = $assignedDetailMap[$mapKey]->fees_group_amount;
                                }
                            @endphp
                            <label class="fee-head-chip {{ $isAssigned ? 'chip-assigned' : 'chip-unassigned' }}" 
                                   id="chip_{{ $item->id }}_{{ $fm->id }}"
                                   title="{{ $fm->feesGroup->name ?? 'Fee Head' }} (₹{{ number_format($headAmt, 0) }}) - Click to {{ $isAssigned ? 'Remove' : 'Assign' }}">
                                <input type="checkbox" 
                                       class="toggle-fee-head-checkbox" 
                                       data-admission-id="{{ $item->id }}" 
                                       data-master-id="{{ $fm->id }}" 
                                       {{ $isAssigned ? 'checked' : '' }} 
                                       style="cursor: pointer; margin-right: 4px;">
                                <span class="chip-name">{{ $fm->feesGroup->name ?? 'Head' }}</span>
                                <span class="chip-amt">₹{{ number_format($headAmt, 0) }}</span>
                                <span class="chip-status ml-1">
                                    <i class="fa fa-check text-success {{ $isAssigned ? '' : 'd-none' }}" id="icon_{{ $item->id }}_{{ $fm->id }}"></i>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted small font-italic">No Fee Heads Configured for this Course</span>
                @endif
            </td>
            <td class="text-center align-middle text-nowrap" style="min-width: 125px;" id="student_summary_td_{{ $item->id }}">
                <div class="fee-summary-cell d-flex flex-column align-items-center justify-content-center" id="student_fee_summary_{{ $item->id }}">
                    <div class="text-dark font-weight-bold" style="font-size: 11.5px; line-height: 1.2;" title="Total Gross Assigned Fee">
                        <span class="text-muted" style="font-size: 10px; font-weight: normal;">Total:</span> ₹{{ number_format($grossTotal, 0) }}
                    </div>
                    @if($discountTotal > 0)
                        <div class="badge badge-warning text-dark px-1 py-0 my-0.5" style="font-size: 10px; font-weight: 600;" title="Total Discount Given">
                            <i class="fa fa-tag text-danger mr-0.5"></i> Disc: -₹{{ number_format($discountTotal, 0) }}
                        </div>
                    @else
                        <div class="text-muted" style="font-size: 10px; line-height: 1.2;">
                            Disc: ₹0
                        </div>
                    @endif
                    <div class="badge badge-success px-2 py-0.5 mt-0.5 font-weight-bold shadow-xs" style="font-size: 11.5px;" title="Net Payable Fee" id="student_net_{{ $item->id }}">
                        Net: ₹{{ number_format($netTotal, 0) }}
                    </div>
                </div>
            </td>
            <td class="text-center align-middle text-nowrap" style="width: 120px;">
                <button type="button" 
                        class="btn btn-xs btn-outline-primary btn-edit-student-fees font-weight-bold shadow-xs" 
                        data-admission-id="{{ $item->id }}"
                        style="font-size: 11px; padding: 3px 8px; border-radius: 4px;"
                        title="Modify Fees, Allocate Discounts & Adjust Due Dates">
                    <i class="fa fa-sliders mr-1"></i> Modify / Disc.
                </button>
            </td>
        </tr>
    @endforeach
@else
    <tr class="text-center">
        <td colspan="10" class="py-5 text-muted">
            <i class="fa fa-info-circle text-warning fa-2x mb-2 d-block"></i>
            <span class="font-weight-bold" style="font-size: 13px;">No students found matching the selected criteria.</span>
        </td>
    </tr>
@endif