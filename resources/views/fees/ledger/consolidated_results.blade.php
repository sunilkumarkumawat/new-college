@php
    $financialYears = $consolidatedData->flatMap(function($entry){ return $entry['years']->keys(); })->unique()->sort()->values();
@endphp
<tbody><tr><td colspan="13" style="padding:0;border:0">
@if(!$hasFilter)
<div class="empty-state"><i class="fa fa-filter"></i>Select course or batch to load the consolidated ledger.</div>
@elseif($consolidatedData->isEmpty())
<div class="empty-state"><i class="fa fa-search"></i>No consolidated ledger records found.</div>
@else
<table class="consolidated-grid" style="width:{{ 300 + ($financialYears->count() * 300) }}px;min-width:{{ 300 + ($financialYears->count() * 300) }}px;max-width:{{ 300 + ($financialYears->count() * 300) }}px">
<colgroup><col style="width:180px"><col style="width:120px">@foreach($financialYears as $year)<col style="width:300px">@endforeach</colgroup>
<thead><tr><th class="student-col" style="text-align:left">Student / Admission / Mobile</th><th class="course-col">Course / Batch</th>@foreach($financialYears as $year)<th class="year-column">{{ $year }}</th>@endforeach</tr></thead>
<tbody>
@foreach($consolidatedData as $entry)
@php $student=$entry['student']; $years=$entry['years']; @endphp
<tr>
<td class="student-col"><span class="student-name">{{ trim(($student->first_name ?? '').' '.($student->last_name ?? '')) }}</span><span class="student-meta"><span class="adm-chip">{{ $student->admissionNo ?: '-' }}</span><span><i class="fa fa-phone"></i> {{ $student->mobile ?: '-' }}</span></span></td>
<td class="course-col text-center"><span class="course-name">{{ $student->course ?: '-' }}</span><span class="meta-chip">Batch: {{ $student->batch ?: '-' }}</span></td>
@foreach($financialYears as $yearName)
@php $year=$years->get($yearName); @endphp
<td class="year-cell">
@if($year)
<div class="year-summary">
<div class="year-total-grid"><span><small>Gross Fee</small><strong>₹{{ number_format($year['gross'],0) }}</strong></span><span><small>Discount</small><strong class="text-warning">-₹{{ number_format($year['discount'],0) }}</strong></span><span><small>Carried Forward</small><strong class="text-warning">-₹{{ number_format($year['carry'],0) }}</strong></span><span><small>Net Payable</small><strong>₹{{ number_format($year['net'],0) }}</strong></span><span><small>Collected</small><strong class="text-success">₹{{ number_format($year['paid'],0) }}</strong></span><span><small>Outstanding</small><strong class="text-danger">₹{{ number_format($year['due'],0) }}</strong></span></div>
<div class="year-summary-action"><button type="button" class="btn btn-outline-primary btn-sm consolidated-year-details" data-admission-ids='@json($year['semesters']->pluck('admission_id')->values())' data-year="{{ $yearName }}" data-toggle="modal" data-target="#ledgerModal"><i class="fa fa-eye mr-1"></i> View Details</button></div>
</div>
@else<div class="no-year-data">No record</div>@endif
</td>
@endforeach
</tr>
@endforeach
</tbody></table>
@endif
</td></tr></tbody>
