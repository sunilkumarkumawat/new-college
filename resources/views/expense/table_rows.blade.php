@php
    $getPermission = Helper::getPermission();
    $i = ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($data->firstItem() ?? 1) : 1;
@endphp

@if(!empty($data) && count($data) > 0)
    @foreach ($data as $item)
        @php
            $userName = !empty($item->user_first_name) ? trim($item->user_first_name . ' ' . ($item->user_last_name ?? '')) : 'Staff / Admin';
            $pmName = !empty($item->payment_mode_name) ? $item->payment_mode_name : 'Cash';
            $badgeBg = '#059669';
            if (strpos(strtolower($pmName), 'cash') !== false) {
                $badgeBg = '#059669';
            } elseif (strpos(strtolower($pmName), 'upi') !== false || strpos(strtolower($pmName), 'online') !== false || strpos(strtolower($pmName), 'bank') !== false) {
                $badgeBg = '#0284c7';
            } else {
                $badgeBg = '#475569';
            }
        @endphp
        <tr>
            <td class="text-center">
                <input type="checkbox" class="checkbox_id" value="{{ $item->id }}">
            </td>
            <td class="text-center font-weight-bold text-muted">{{ $item->id }}</td>
            <td>
                <strong style="color: #0f172a; font-size: 12px;">{{ $item->name ?? 'Not Mentioned' }}</strong>
                @if(!empty($item->description))
                    <small class="text-muted d-block" style="font-size: 10.5px; line-height: 1.2;">{{ Str::limit($item->description, 60) }}</small>
                @endif
            </td>
            <td>
                <span class="text-secondary font-weight-bold" style="font-size: 11.5px;">
                    <i class="fa fa-user-circle-o text-muted mr-1"></i> {{ $userName }}
                </span>
            </td>
            <td class="text-center">
                <span class="badge badge-light border text-dark" style="font-size: 10.5px;">
                    <i class="fa fa-calendar-o text-secondary mr-1"></i> {{ !empty($item->date) ? date('d-m-Y', strtotime($item->date)) : '-' }}
                </span>
            </td>
            <td class="text-center">
                <span class="badge" style="background: {{ $badgeBg }}; color: #ffffff; font-size: 10px; font-weight: 600; padding: 3px 8px;">
                    {{ $pmName }}
                </span>
            </td>
            <td class="text-center text-muted" style="font-size: 11px;">
                {{ $item->quantity ?? 1 }} × ₹{{ number_format($item->rate ?? $item->amount, 2) }}
            </td>
            <td class="text-right font-weight-bold text-success" style="font-size: 12.5px;">
                ₹ {{ number_format($item->amount ?? 0, 2) }}
            </td>
            <td class="text-center">
                @if(!empty($item->attachment))
                    <a href="{{ env('IMAGE_SHOW_PATH') . 'expense/' . $item->attachment }}" target="_blank" class="badge badge-info" style="font-size: 10.5px; padding: 3px 6px;" title="View Receipt File">
                        <i class="fa fa-paperclip mr-1"></i> File
                    </a>
                @else
                    <span class="text-muted" style="font-size: 10px;">-</span>
                @endif
            </td>
            @if($getPermission->edit == 1 || $getPermission->download == 1 || $getPermission->deletes == 1)
                <td class="fixed_item text-center">
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                        @if($getPermission->download == 1)
                            <a href="{{ url('expensePrint') }}/{{ $item->id }}" target="_blank" class="btn-row-action btn-action-view" title="Print Voucher">
                                <i class="fa fa-print"></i>
                            </a>
                        @endif
                        @if($getPermission->edit == 1)
                            <a href="{{ url('expenseEdit') }}/{{ $item->id }}" class="btn-row-action btn-action-edit" title="Edit Expense">
                                <i class="fa fa-edit"></i>
                            </a>
                        @endif
                        @if($getPermission->deletes == 1)
                            <button type="button" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-bs-toggle="modal" data-bs-target="#Modal_id" class="btn-row-action btn-action-delete deleteData" title="Delete Expense">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        @endif
                    </div>
                </td>
            @endif
        </tr>
    @endforeach
@else
    <tr class="admission-empty-row">
        <td colspan="{{ ($getPermission->edit == 1 || $getPermission->download == 1 || $getPermission->deletes == 1) ? 10 : 9 }}" class="admission-empty-cell">
            <div class="empty-state-wrap">
                <div class="empty-state-icon-wrap">
                    <i class="fa fa-folder-open-o"></i>
                </div>
                <div class="empty-state-title">No Expense Vouchers Found</div>
                <div class="empty-state-subtitle">No expense records match your active filter criteria. Try adjusting dates or clearing search parameters.</div>
                <button type="button" class="btn-empty-reset" onclick="resetExpenseFilters()">
                    <i class="fa fa-refresh mr-1"></i> Clear All Filters
                </button>
            </div>
        </td>
    </tr>
@endif
