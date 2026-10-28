@if(count($data) != 0)
 
    
    @foreach($data as $key =>$item)
    
    @php
    $feesAssignedDetails = DB::table('fees_assign_details')->where('admission_id',$item->admission_id)->whereNull('deleted_at')->get();
        $count = 0;
        $rowspan = count($feesAssignedDetails);
        $finalAssignedAmt = 0;
        $finalAssignedDis = 0;
        $finalAssignedFine = 0;
    @endphp
    
     @if(!empty($feesAssignedDetails))
@foreach($feesAssignedDetails as $details)
 

 
@php

$feeGroupName = DB::table('fees_group')->where('id',$details->fees_group_id)->whereNull('deleted_at')->first();
$finalAssignedAmt += $details->fees_group_amount;
$finalAssignedDis += $details->discount;
$finalAssignedFine += $details->installment_fine;
$finalUpdatedAmt = ($finalAssignedAmt - $finalAssignedDis);
$updatedAmtPerHead = ($details->fees_group_amount - $details->discount - ($details->promotion_carry_adjustment ?? 0));
$deletable = DB::table('fees_detail')->where('admission_id',$item->admission_id)->where('fees_group_id',$details->fees_group_id)->whereNull('deleted_at')->whereIn('status', [0,1])->sum('total_amount');
@endphp
 <tr style="background-color:{{$key%2 == 0 ? '#c8c8c8' : ''}}">
            @if($count < 1)
     <td rowspan={{$rowspan}} >{{$item->first_name ?? ''}} {{ $item->last_name ?? '' }}</td>
     <td rowspan={{$rowspan}} >{{$item->admissionNo ?? ''}}</td>
        
        <td rowspan={{$rowspan}}>{{$item->mobile}}</td>
        @endif
       
        <td class='text-left'>
        {{$feeGroupName->name ?? ''}} = 
            <!-- 
            
            @if(!empty($deletable))
             
            {{$details->fees_group_amount ?? ''}}
            @else
            
             @endif
  -->
  <input type='text' style="width: 90px" name='fees_group_amount' data-pay_fees="{{$deletable ?? ''}}" class='fees_assign_detail' id="feesGroupAmount_{{ $details->id ?? '' }}" data-old_value="{{$details->fees_group_amount ?? '' }}" data-detail_id='{{$details->id}}' value="{{$details->fees_group_amount ?? ''}}" />
           @if($details->fees_group_amount != $updatedAmtPerHead) 
           <br><small class="text-primary">Updated : {{ $updatedAmtPerHead ?? '' }}</small>
           @endif
        </td>
        <td class='text-left d-flex' width="200">
          
            @if(!empty($deletable))
             
                {{$details->discount ?? ''}}
            @else
            
            <input type="text" name="discount_percent" placeholder="Dis. in %" data-id="{{ $details->id ?? '' }}" class="discountInPercent w-50 fees_assign_detail" data-detail_id='{{$details->id}}' data-old_value='{{$details->discount_percent ?? 0}}' value="{{$details->discount_percent ?? ''}}">
             <input type='text' readonly placeholder="Dis. in Amt" name='discount' class='fees_assign_detail w-50 discountInAmount ' id="discountInAmount_{{ $details->id ?? '' }}" data-detail_id='{{$details->id}}' data-old_value='{{$details->discount ?? 0}}' value="{{$details->discount ?? ''}}" />
            @endif
         
        </td>
        <td class='text-left'>
          
            @if(!empty($deletable))
             
            @if(!empty($details->installment_due_date)) {{ date('d-M-Y', strtotime($details->installment_due_date)) }} @endif
            @else
            
             <input type='date' name='installment_due_date' class='fees_assign_detail' data-detail_id='{{$details->id}}' data-old_value='{{$details->installment_due_date ?? 0}}' value="{{$details->installment_due_date ?? ''}}" />
            @endif
         
        </td>
        <td class='text-left'>
          
            @if(!empty($deletable))
             
            {{$details->installment_fine ?? ''}}
            @else
            
             <input type='number' min="0" max="100" name='installment_fine' class='fees_assign_detail w-100' data-detail_id='{{$details->id}}' data-old_value='{{$details->installment_fine ?? 0}}' value="{{$details->installment_fine ?? 0}}" />
            @endif
         
        </td>
        <td class='text-left'>
        <input type="checkbox" name="fees_refund"  id="refund_fees_value_{{$details->id ?? ''}}" 
        {{ $details->fees_refund == 'yes' ? 'checked' : '' }} onchange="updateRefundFees(this, {{$details->id ?? ''}})"
         data-detail_id='{{$details->id}}' data-old_value="{{$details->fees_refund ?? ''}}"
          value="{{$details->fees_refund ?? 'no'}}">
        </td>
        <td>
           <i style='cursor:pointer' data-detail_id='{{$details->id}}' class="fa fa-times text-danger {{!empty($deletable) ? 'd-none' : ''}} delete_assigned" aria-hidden="true"></i>
        </td>
       <!--   @if($count < 1)-->
       <!-- <td rowspan={{$rowspan}}><button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addFeesModal">Add</button></td>-->
       <!--@endif-->
        
    </tr>
    
    
    @php
    $count++;
    @endphp
@endforeach
<tr class="bg-light border border-warning">
    <td colspan="3" class="text-white"><b>Updated Amount = {{ $finalUpdatedAmt ?? '0' }}</b></td>
    <td class="text-white"><b>Assigned Amount = {{ $finalAssignedAmt ?? '0' }}</b></td>
    <td class="text-white"><b>Assigned Discount = {{ $finalAssignedDis ?? '0' }}</b></td>
    <td></td>
    <td class="text-white"><b>Fine = {{ $finalAssignedFine ?? '0' }}</b></td>
    <td colspan="2"></td>
</tr>
@endif           

    @endforeach
    
@else
<tr class="text-center">
    <td colspan="12">
        <p class="note_text"><i class="fa fa-warning"></i><br> No Student Found </p>
    </td>
</tr>
@endif  
