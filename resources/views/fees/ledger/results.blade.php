<tbody>
@if(!$hasFilter)
<tr><td colspan="13"><div class="empty-state"><i class="fa fa-filter"></i>Select a course or batch to load the student ledger.</div></td></tr>
@elseif($data->isEmpty())
<tr><td colspan="13"><div class="empty-state"><i class="fa fa-search"></i>No students found for the selected filters.</div></td></tr>
@else
@foreach($data as $index => $student)
<tr><td class="text-center">{{ $index + 1 }}</td><td><span class="student-name">{{ trim(($student->first_name ?? '').' '.($student->last_name ?? '')) }}</span><span class="student-meta"><span class="adm-chip">{{ $student->admissionNo ?: '-' }}</span><span><i class="fa fa-phone"></i> {{ $student->mobile ?: '-' }}</span></span></td><td class="text-center"><span class="course-name">{{ $student->course ?: '-' }}</span><span class="meta-chip">Batch: {{ $student->batch ?: '-' }}</span><span class="meta-chip">{{ $student->className ?: '-' }}</span></td><td>{{ $student->father_name ?: '-' }}</td><td class="text-center"><span class="status-pill {{ (int)$student->status === 1 ? 'status-active' : 'status-inactive' }}">{{ (int)$student->status === 1 ? 'Active' : 'Inactive' }}</span></td><td class="money">₹{{ number_format($student->gross_amount,0) }}</td><td class="money discount">-₹{{ number_format($student->discount_amount,0) }}</td><td class="money discount">-₹{{ number_format($student->carry_amount,0) }}</td><td class="money">₹{{ number_format($student->net_amount,0) }}</td><td class="money paid">₹{{ number_format($student->paid_amount,0) }}</td><td class="money">₹{{ number_format($student->fine_amount,0) }}</td><td class="money due">₹{{ number_format($student->due_amount,0) }}</td><td><div class="action-stack"><button type="button" class="btn btn-outline-info ledger-details" data-id="{{ $student->id }}" data-toggle="modal" data-target="#ledgerModal"><i class="fa fa-eye"></i> Details</button></div></td></tr>
@endforeach
@endif
</tbody>
@if($hasFilter && $data->isNotEmpty())
<tfoot><tr><td colspan="5">TOTAL SUMMARY ({{ $data->count() }} STUDENTS)</td><td class="money">₹{{ number_format($summary['gross'],0) }}</td><td class="money discount">-₹{{ number_format($summary['discount'],0) }}</td><td class="money discount">-₹{{ number_format($summary['carry'],0) }}</td><td class="money">₹{{ number_format($summary['net'],0) }}</td><td class="money paid">₹{{ number_format($summary['paid'],0) }}</td><td class="money">₹{{ number_format($summary['fine'],0) }}</td><td class="money due">₹{{ number_format($summary['due'],0) }}</td><td></td></tr></tfoot>
@endif
