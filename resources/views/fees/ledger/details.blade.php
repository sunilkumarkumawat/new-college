<style>
.ledger-sheet{background:#fff;font-size:13px}.ledger-session{margin-bottom:12px;border:1px solid #9eacb7}.ledger-session:last-child{margin-bottom:0}.ledger-session-title{display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-bottom:1px solid #9eacb7;background:#dbe8f1;color:#20384b;font-size:12px;font-weight:700}.ledger-session-title span{font-size:11px;font-weight:600}.ledger-sheet table{width:100%;margin:0;border-collapse:collapse}.ledger-sheet th,.ledger-sheet td{padding:7px 9px;border-right:1px solid #b8c2ca;border-bottom:1px solid #b8c2ca;vertical-align:middle}.ledger-sheet th:last-child,.ledger-sheet td:last-child{border-right:0}.ledger-sheet thead th{background:#eef2f4;color:#263642;font-size:12px;font-weight:700;text-align:center}.ledger-sheet tbody tr:nth-child(even){background:#fafbfc}.ledger-sheet .head-name{font-weight:600}.ledger-sheet .num{text-align:right;white-space:nowrap}.ledger-sheet .discount{color:#d98600}.ledger-sheet .paid{color:#159447}.ledger-sheet .due{color:#df3542}.ledger-sheet .state{display:inline-block;min-width:58px;padding:2px 7px;border-radius:3px;font-size:10px;font-weight:700;text-align:center}.ledger-sheet .state-paid{background:#def3e4;color:#167b38}.ledger-sheet .state-due{background:#fde5e7;color:#c62d39}.ledger-sheet tfoot td{background:#edf5e9;border-bottom:0;font-weight:700}.ledger-grand-total{margin-top:10px;border:1px solid #9eacb7;background:#e2f0d9}.ledger-grand-total td{font-weight:700}.ledger-sheet-empty{padding:40px;text-align:center;color:#788691;border:1px solid #c4ced6;background:#f8fafb}
</style>
@if($fees->isEmpty())
<div class="ledger-sheet-empty"><i class="fa fa-folder-open-o mr-1"></i> No fee heads are assigned to this student.</div>
@else
<div class="ledger-sheet table-responsive">
@foreach($fees->groupBy('admission_id') as $semesterFees)
@php
    $semesterName = $semesterFees->first()->class_name ?: 'Class / Semester';
    $semesterSummary = [
        'gross' => $semesterFees->sum('gross_amount'),
        'discount' => $semesterFees->sum('discount_amount'),
        'carry' => $semesterFees->sum('carry_amount'),
        'net' => $semesterFees->sum('net_amount'),
        'paid' => $semesterFees->sum('paid_amount'),
        'fine' => $semesterFees->sum('fine_amount'),
        'due' => $semesterFees->sum('due_amount'),
    ];
@endphp
<section class="ledger-session">
<div class="ledger-session-title"><strong>{{ $semesterName }}</strong><span>Net ₹{{ number_format($semesterSummary['net'],0) }} &nbsp;·&nbsp; Paid ₹{{ number_format($semesterSummary['paid'],0) }} &nbsp;·&nbsp; Due ₹{{ number_format($semesterSummary['due'],0) }}</span></div>
<table>
<thead><tr><th style="width:42px">Sr.</th><th style="text-align:left;min-width:190px">Fee Head</th><th>Due Date</th><th>Gross Fee</th><th>Discount</th><th>Carried</th><th>Net Fee</th><th>Paid</th><th>Fine</th><th>Balance</th><th>Status</th></tr></thead>
<tbody>@foreach($semesterFees as $index => $head)<tr><td class="text-center">{{ $index + 1 }}</td><td class="head-name">{{ $head->group_name }}</td><td class="text-center">{{ $head->installment_due_date ? date('d-m-Y',strtotime($head->installment_due_date)) : '-' }}</td><td class="num">₹{{ number_format($head->gross_amount,0) }}</td><td class="num discount">-₹{{ number_format($head->discount_amount,0) }}</td><td class="num discount">-₹{{ number_format($head->carry_amount,0) }}</td><td class="num">₹{{ number_format($head->net_amount,0) }}</td><td class="num paid">₹{{ number_format($head->paid_amount,0) }}</td><td class="num">₹{{ number_format($head->fine_amount,0) }}</td><td class="num due">₹{{ number_format($head->due_amount,0) }}</td><td class="text-center"><span class="state {{ $head->due_amount > 0 ? 'state-due' : 'state-paid' }}">{{ $head->due_amount > 0 ? ($head->paid_amount > 0 ? 'PARTIAL' : 'UNPAID') : 'PAID' }}</span></td></tr>@endforeach</tbody>
<tfoot><tr><td colspan="3">{{ $semesterName }} Total</td><td class="num">₹{{ number_format($semesterSummary['gross'],0) }}</td><td class="num discount">-₹{{ number_format($semesterSummary['discount'],0) }}</td><td class="num discount">-₹{{ number_format($semesterSummary['carry'],0) }}</td><td class="num">₹{{ number_format($semesterSummary['net'],0) }}</td><td class="num paid">₹{{ number_format($semesterSummary['paid'],0) }}</td><td class="num">₹{{ number_format($semesterSummary['fine'],0) }}</td><td class="num due">₹{{ number_format($semesterSummary['due'],0) }}</td><td></td></tr></tfoot>
</table>
</section>
@endforeach
<table class="ledger-grand-total"><tbody><tr><td>Financial Year Total</td><td class="num">Gross ₹{{ number_format($summary['gross'],0) }}</td><td class="num discount">Discount -₹{{ number_format($summary['discount'],0) }}</td><td class="num discount">Carried -₹{{ number_format($summary['carry'],0) }}</td><td class="num">Net ₹{{ number_format($summary['net'],0) }}</td><td class="num paid">Paid ₹{{ number_format($summary['paid'],0) }}</td><td class="num due">Outstanding ₹{{ number_format($summary['due'],0) }}</td></tr></tbody></table>
</div>
@endif
