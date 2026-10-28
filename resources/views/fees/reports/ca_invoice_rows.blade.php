<tbody>
@forelse($invoices as $invoice)
<tr>
<td class="text-center fixed-receipt"><form action="{{ url('printFeesInvoice') }}" method="post" target="_blank" class="d-inline">@csrf<button name="fees_details_invoice_id" value="{{ $invoice->id }}" class="btn btn-link btn-sm p-0 receipt-link">{{ $invoice->invoice_no ?: '-' }}</button></form></td>
<td class="fixed-student"><span class="student-name">{{ trim(($invoice->first_name ?? '').' '.($invoice->last_name ?? '')) }}</span><span class="subtext"><span class="chip">{{ $invoice->admissionNo ?: '-' }}</span> &nbsp;<i class="fa fa-phone"></i> {{ $invoice->mobile ?: '-' }}</span></td>
<td class="text-center">{{ $invoice->payment_date ? date('d-m-Y',strtotime($invoice->payment_date)) : '-' }}</td>
<td class="text-center"><strong>{{ $invoice->course ?: '-' }}</strong><span class="subtext"><span class="chip">Batch: {{ $invoice->batch ?: '-' }}</span> <span class="chip">{{ $invoice->class_name ?: '-' }}</span></span></td>
<td class="text-center">{{ $invoice->payment_mode_name ?: 'Cash' }}</td><td class="text-center">{{ $invoice->transaction_id ?: ($invoice->cheque_number ?: '-') }}</td><td>{{ trim(($invoice->collector_first_name ?? '').' '.($invoice->collector_last_name ?? '')) ?: '-' }}</td>
<td class="money">₹{{ number_format($invoice->discount ?? 0,0) }}</td><td class="money fine">₹{{ number_format($invoice->total_fine ?? 0,0) }}</td><td class="money received">₹{{ number_format($invoice->amount ?? 0,0) }}</td>
<td class="text-center">@if((int)$invoice->status===0)<span class="status received-status">RECEIVED</span>@elseif((int)$invoice->status===1)<span class="status pending-status">PENDING</span>@else<span class="status cancelled-status">CANCELLED</span>@endif</td>
<td class="text-center fixed-action print-action"><form action="{{ url('printFeesInvoice') }}" method="post" target="_blank">@csrf<button name="fees_details_invoice_id" value="{{ $invoice->id }}" class="btn btn-outline-primary btn-xs"><i class="fa fa-print"></i> Invoice</button></form></td>
</tr>
@empty
<tr><td colspan="12"><div class="empty"><i class="fa fa-file-text-o fa-2x d-block mb-2"></i>No receipt invoices found for the selected filters.</div></td></tr>
@endforelse
</tbody>
<tfoot><tr><td colspan="4"><div class="pagination-wrap"><span class="summary-label">{{ number_format($totals->receipt_count ?? 0) }} RECEIPTS</span>@if($invoices->hasPages()){{ $invoices->onEachSide(1)->links() }}@endif</div></td><td colspan="3" class="text-right">RECEIVED TOTALS</td><td class="money">Discount: ₹{{ number_format($totals->discount_amount ?? 0,0) }}</td><td class="money fine">Fine: ₹{{ number_format($totals->fine_amount ?? 0,0) }}</td><td class="money received">Paid: ₹{{ number_format($totals->received_amount ?? 0,0) }}</td><td class="text-center"><span class="summary-label">Pending: {{ number_format($totals->pending_count ?? 0) }} | Cancelled: {{ number_format($totals->cancelled_count ?? 0) }}</span></td><td class="fixed-action print-action"></td></tr></tfoot>
