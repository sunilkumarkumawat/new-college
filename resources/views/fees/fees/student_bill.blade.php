 @php
$getFeesType = Helper::feesType();
$getPaymentMode = Helper::getPaymentMode();
$firstAmount = $data['FeesAssign']->total_amount;
$feesDetails = DB::table('fees_assign_details')
            ->where('session_id',$data['session_id'])
            ->where('branch_id',Session::get('branch_id'))
            ->where('fees_assign_id',$data['FeesAssign']->id)
            ->whereNull('deleted_at')
            ->get();
@endphp             
        
<style>
    .centered{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bordered{
        border: 2px solid #002c54;
        height: 140px !important;
        width: 135px;
        padding: 5px;
        background: #e5ecff;
    }
    
     .stu_img{
        width:100%;
        height:100%;
     }
     
     .tabs_listing{
         list-style:none;
         display:flex;
         align-items:center;
         /*justify-content:center;*/
         padding-left:0px;
         margin-bottom:0px;
     }
     
     .tabs_listing li{
        border: 1px solid #002c54;
        color: black;
        padding: 10px 10px;
        /*border-radius: 4px;*/
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 1.4px;
        cursor: pointer;
        margin-left:10px;
        transition:0.3s;
     }
     
     .tabs_listing li:first-child{
         margin-left:0px;
     }
     
     .tabs_listing li:hover{
         background: #002c54;
         color: white;
     }
     
     .trans_card{
         box-shadow:none;
         background:none;
     }
     
     #active_li{
         background: #002c54;
         color: white;
     }
     
     .not_found_div{
         height:300px;
         display:flex;
         align-items:center;
         justify-content:center;
     }
     
        .warning_icon {
          font-size: 60px;
          color: red;
          margin-bottom: 0px;
        }
     
     .assign_note{
        margin-bottom: 0px;
        font-weight:600;
        text-transform: capitalize;
     }
</style>        
 <div>
     
 <div class="row mb-3 mt-2">
     <div class="col-12 col-md-12 text-center p-2"> <h3 class="colored_header">{{ __('fees.Fees Pay') }}</h3></div>
     <div class="col-md-12 mb-3">
        <div class="card trans_card">
            <div class="card-body p-0">
                <ul class="tabs_listing">
                    @if(count($data['sessions']) != 0)
                        @php
                            $sessions = $data['sessions'];
                        @endphp
                        @foreach($sessions as $item)
                            <li class="tab" id="{{ $data['session_id'] == $item->id ? 'active_li' : '' }}" data-id="{{ $item->id ?? '' }}" data-unique_system_id="{{ $data['stuData']['unique_system_id'] ?? '' }}">{{ $item->from_year ?? '' }} - 20{{ $item->to_year ?? '' }}</li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>
 </div>
 <div class="col-md-12 not_found_div" style="display:none;">
        <div class="text-center">
            <h1 class="warning_icon"><i class="fa fa-warning"></i></h1>
            <p class="assign_note">Please Assign the Fees for this Student !</p>
        </div>
 </div>
 </div>
 <div class="col-md-12" id="notfound">
 <div class="row">
        <!--<div class="col-4 col-md-2 centered">-->
        <!--    <div class="card bordered">-->
        <!--        <img src="{{ env('IMAGE_SHOW_PATH').'profile/'.$data['stuData']['image'] }}" -->
        <!--        alt="student_image" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'" class="stu_img">-->
        <!--    </div>-->
        <!--</div>-->
    <div class="col-8 col-md-6">
        <div class='row'>
         <div class="p-2 col-3 col-md-3">
             <label class="text">{{ __('Aggregate Amount') }}</label>
             <input type='text' value='' id='aggregate_amount' class='form-control'/>
             </div>   
        </div>
        <div class="card">
           
            @if(!empty($data['FeesCollect']->amount))
                @php
                    $remainingAmt = ($data['FeesAssign']->total_amount - $data['FeesAssign']->total_discount) - $data['FeesCollect']->amount;
                    $firstAmount = $remainingAmt;
                   
                @endphp
                
                @else
                
                @php
                    $remainingAmt = $data['FeesAssign']->total_amount - $data['FeesAssign']->total_discount;
                    $firstAmount = $remainingAmt;
                @endphp
            @endif

            <div class"card-body">
                <form id='myForm' method="post">
                    @csrf
                    <input  type="hidden" id="admission_id" name="admission_id" value="{{$data['stuData']['id']}}" />
                    <input  type="hidden" id="session_id" name="session_id" value="{{ $data['session_id'] ?? Session::get('session_id') }}" />
                    <input  type="hidden" id="email" name="email" value="{{$data['stuData']['email']}}" />
                    <input  type="hidden" id="mobile" name="mobile" value="{{$data['stuData']['mobile']}}" />
                    <input  type="hidden" id="class_type_id1" name="class_type_id" value="{{$data['stuData']['class_type_id']}}" />
                    
                <div id='add_head_row'>
                 <div class="row m-2" id='head_row'>
                        
                   
    <div class="col-md-3">
                            <label class="text-danger">{{ __('Select Head') }}*</label>
                            <select class="form-control selected_head" data-key1='0' name="selected_head[]" required>
                                @if(!empty($feesDetails))
                                 <option value="">Select Group</option>
                                     @foreach($feesDetails as $fees)
                                      @php
                                $feesGroup  = DB::table('fees_group')->whereNull('deleted_at')->where('id',$fees->fees_group_id)->first();
                            @endphp
 
                                    <option value="{{ $feesGroup->id }}">{{ $feesGroup->name ?? '' }}</option>
                                    @endforeach
                                @endif  
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('fees.Receipt No.') }}</label>
                            <input type="text" class="form-control" placeholder="{{ __('fees.Receipt No.') }}" name="slip_no[]"  value="{{ \App\Helpers\Helper::formatReceiptNo(($data['BillCounter']['counter'] ?? 0) + 1, $data['session_id'] ?? Session::get('session_id')) }}" readonly>
                        </div>
                        <!--<div class="col-md-4 d-none">-->
                        <!--    <label>{{ __('fees.Remaining Amount') }}</label>-->
                            
                  
                        <!--    <input type="text" class="form-control"  placeholder="{{ __('fees.Remaining Amount') }}" id="remain_amount" name="remain_amount[]" onkeypress="javascript:return isNumber(event)" value="{{$firstAmount ?? '' }}" readonly>-->
                        
                        <!--</div>-->
                        <div class="col-md-3">
                            <label class="text-danger">{{ __('common.Amount') }}*</label>
                            <input type="tel" class="form-control change_amount amount_get"  placeholder="{{ __('common.Amount') }}" id="amount_0" name="amount[]" onkeypress="javascript:return isNumber(event)"  required>
                        </div>
                        <div class="col-md-3">
                            <label class="">{{ __('Discount') }}</label>
                            <input type="tel" class="form-control discount"  placeholder="{{ __('common.Amount') }}" id="discount_0" name="discount_amount[]" onkeypress="javascript:return isNumber(event)" >
                        </div>
                        <div class="col-md-2">
                            <label class="">{{ __('Fine') }}</label>
                            <input type="text" class="form-control fine_amount" id='fine_0' value='0'name="fine[]" onkeypress="javascript:return isNumber(event)"  required>
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-danger removeprodtxtbx" id="removerow">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div> </div>
                </div>
                <hr>
                <div class="row m-2">
                    <div class="col-md-3">
                        <label class="text-danger">{{ __('fees.Payment Mode') }}*</label>
                        <select class="form-control" id="payment_mode_id" name="payment_mode_id" required>
                            @if(!empty($getPaymentMode))
                                @foreach($getPaymentMode as $value)
                                <option value="{{ $value->id }}">{{ $value->name ?? ''}}</option>
                                @endforeach
                            @endif  
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label>Total</label>
                        <input type="text" class="form-control" readonly value="0" placeholder="Total Amount" id="total_amount" name="total_amount">
                    </div>
                    <div class="col-md-3">
                        <label>Total Fine</label>
                        <input type="text" class="form-control" readonly value="0" placeholder="Total Amount" id="total_fine" name="total_fine">
                    </div>
                        
                    <div class="col-md-3" id="transition_id_input" style="display:none;">
                        <label>Transaction Id</label>
                        <input type="text" class="form-control" placeholder="Transaction Id" id="transition_id" name="transition_id">
                    </div> 
                    
                    <div class="col-md-3" id="bank_name_input" style="display:none;">
                        <label>Bank Name</label>
                        <input type="text" class="form-control" placeholder="Bank Name" id="bank_name" name="bank_name">
                    </div>
                        
                    <div class="col-md-3">
                        <label class="text-danger">{{ __('Payment Date') }}*</label>
                        <input type='date' class='form-control' name='date' value="{{date('Y-m-d')}}" />
                    </div>
                    <div class="col-md-3 mt-2">
                        <input type='hidden' id='discount_given'class='form-control' name='discount_given' value="" readonly />
                    </div>
                   
                </div>
                <hr />
                    <div class="row m-2">
                    <div class="col-md-6 text-left">
                        <span ><h3>
                            Aggregate : ₹<span id="aggregate">0</span>
                        <br><span class='text-danger'>Discount  : ₹ <span id="d_given">0</span></span><br>
                        Grand Total : ₹<span id="g_total">0</span>
                        </h3></span>
                      
                    </div>
                    <div class="col-md-6 text-right">
                        <span><h3></h3></span>
                    </div>
                    
                    
                    </div>
                <div class="row p-2 ">
                    <div class="col-md-12 text-center">
                        <button type="button" class="btn btn-info addmoreprodtxtbx" id="clonebtn"><i class="fa fa-plus"></i>{{ __('Add Head') }} </button>
                        <button type="submit" id="collect_btn" class="btn btn-primary"><i class="fa fa-money"></i>{{ __('fees.Collect') }} </button>
                    </div>
                </div>
                </form>
            </div>
            
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-body padding_body">
                <p class="heading_text mb-0">Fees Structure :- </p>
               
                    <table id='fee_structure'class="table table-bordered padding_table" style="white-space:nowrap;">
                        <thead>
                            <tr>
                                <th>Fees Type</th>
                                <th>Amount</th>
                                <th>Discount</th>
                                <th>Paid</th>
                                <th class="bg-danger">Paid Fine</th>
                                <th>Pending</th>
                                <th>Due Date</th>
                                <th class="bg-danger">Fine</th>
                               
                                <!--<th>Breakdown</th>-->
                            </tr>
                        </thead>
                        
                        <tbody>
                            @php
                                $total_amount = 0;
                                $total_discount = 0;
                                $total_paid = 0;
                                $total_paid_fine = 0;
                                $total_pending_fine = 0;
                                $total_pending = 0;
                                
                                $headArray = [];
                            @endphp
                            @foreach($feesDetails as $fees)
                            
                            
                            @php
                            
                                $feesGroup = DB::table('fees_group')->whereNull('deleted_at')->where('id',$fees->fees_group_id)->first();
                                    $discount = DB::table('fees_detail')->whereNull('deleted_at')->where('admission_id',$fees->admission_id)->where('fees_group_id',$feesGroup->id)->sum('discount');
                                  
                            @endphp
                            <div class="col-md-12">
                                <tr id='group_{{$feesGroup->id ?? ''}}' class='group_group' >
                                    <td style="word-break: break-word !important;white-space: break-spaces;">{{ $feesGroup->name ?? '' }}</td>
                                    <td>₹ {{ $fees->fees_group_amount ?? '' }}</td>
                                    <td>₹ {{ ($fees->discount ?? 0  ) + $discount}}</td>
                                    <td>
                                        @if(!empty($feesGroup))
                                            @php
                                                $paid = DB::table('fees_detail')->whereNull('deleted_at')->where('admission_id',$fees->admission_id)->where('fees_group_id',$feesGroup->id)->sum('paid_amount');
                                                $paid_fine = DB::table('fees_detail')->whereNull('deleted_at')->where('admission_id',$fees->admission_id)->where('fees_group_id',$feesGroup->id)->sum('installment_fine');
                                               @endphp                              
                                        @endif
                                        
                                        ₹ {{ ($paid ?? 0 )-($discount)}}
                                    </td>
                                    <td class="text-danger">₹ {{ number_format($paid_fine ?? 0, 2) }}</td>
                                    
                                    @php
                                        $pending_amount = (($fees->fees_group_amount) - ($fees->discount) - ($fees->promotion_carry_adjustment ?? 0)) - ($paid);
                                    @endphp
                                    
                                    
                                    @php
                                    $headArray[] = ['pending_by_group_id'=>$feesGroup->id ?? '','pending'=>($pending_amount ?? 0)]
                                    @endphp
                                
                                <td id="pending_by_group_id_{{$feesGroup->id ?? ''}}" class="{{ $pending_amount == 0 ? 'bg-success ' : '' }}"
                                
                                data-pending_amount='{{$pending_amount ?? "0"}}'
                                data-fine="{{ ($pending_amount != 0 && isset($fees->installment_due_date) && $fees->installment_due_date < date('Y-m-d')) ? $fees->installment_fine : 0 }}"
                                
                                >₹ {{$pending_amount ?? "0"}}</td>
                                        
                                        
                                        <td class="{{ ($pending_amount != 0 && isset($fees->installment_due_date) && $fees->installment_due_date < date('Y-m-d')) ? 'bg-danger' : '' }}">
                                            <input type="date" class="{{ $pending_amount == 0 ? 'fees_assign_detail' : '' }}" name="installment_due_date"  data-detail_id="{{ $fees->id ?? '' }}" data-old_value="{{ $fees->installment_due_date ?? '' }}" {{ $pending_amount == 0 ? 'disabled' : '' }} value="{{ $fees->installment_due_date ?? '' }}">
                                        </td>
                                        
                                        <td class="text-danger">₹ {{ ($pending_amount != 0 && isset($fees->installment_due_date) && $fees->installment_due_date < date('Y-m-d')) ? ($pending_amount * $fees->installment_fine ?? 0)/100 : 0 }}</td>
                                    @php
                                        $total_amount += $fees->fees_group_amount ?? 0;
                                        $total_discount += ($fees->discount ?? 0)+($discount);
                                        $total_paid += ($paid ?? 0)-($discount);
                                        $total_paid_fine += $paid_fine ?? 0;
                                        $total_pending_fine += ($pending_amount != 0 && isset($fees->installment_due_date) && $fees->installment_due_date < date('Y-m-d')) ? ($pending_amount * $fees->installment_fine ?? 0)/100 : 0;
                                    @endphp
                            
                                    <!--<td class="text-center">
                                        @if(!empty($fees->fees_breakdown))
                                            <a href="{{ url('fees_breakdown_details') }}/{{ $fees->id }}" target="_blank"><button class="btn btn-xs btn-primary"><i class="fa fa-print"></i></button></a>
                                            @else
                                            <p class="mb-0">NA</p>
                                        @endif
                                    </td>-->
                                </tr>
                            </div>
                            
                          
                            @endforeach
                             
                        </tbody>
                        
                        <tfoot class="tfoot_tr">
                            <tr>
                                <td>Total</td>
                                <td>₹ {{ $total_amount ?? '' }}</td>
                                <td>₹ {{ $total_discount ?? '' }}</td>
                                <td>₹ {{ $total_paid ?? '' }}</td>
                                <td class="bg-danger">₹ {{ number_format($total_paid_fine ?? 0, 2) }}</td>
                                <td id='validate_pending' data-pending="{{ (($total_amount ?? '0') - ($total_discount ?? '0')) - ($total_paid ?? '0') }}">₹ {{ (($total_amount ?? '0') - ($total_discount ?? '0')) - ($total_paid ?? '0') }}</td>
                         <td></td>
                         <td class="bg-danger">₹ {{ $total_pending_fine ?? '' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>        
</div>

       
<div class="card mt-3">
    <div class="card-body">
        <div class="row mt-2">
            <div class="col-md-12 text-left"> 
                <p class="heading_text mb-2">Fee Receipts :-</p>
            </div>
            <div class="col-md-5 text-left mb-2 d-flex" style='align-items: center;'> 
               <span> Select Head :: </span>&nbsp;<select class="form-control w-50" id="receipt_by_head">
                        @if(!empty($feesDetails))
                             <option value="">Select Head</option>
                             @foreach($feesDetails as $fees)
                              @php
                        $feesGroup  = DB::table('fees_group')->whereNull('deleted_at')->where('id',$fees->fees_group_id)->first();
                        @endphp
                        <option value="{{ $feesGroup->id }}">{{ $feesGroup->name ?? '' }}</option>
                        @endforeach
                    @endif  
                </select>
            </div>
        
            
            <div class="col-md-7 text-left mb-2 " style='align-items: center;'> 
             <form target='_blank'action="{{ url('printFeesInvoice') }}" method="post">
                     @csrf
            <div class='d-flex'>
               
             <span>Invoice Print :: </span>&nbsp;  <select class="form-control w-50" name='fees_invoice_id' required>
                                    @if(!empty($data['FeesDetailsInvoices']))
                                     
                                         <option value="">Select Invoice</option>
                                         @foreach($data['FeesDetailsInvoices'] as $val)
                                         
                                        <option value="{{$val->fees_details_id ?? ''}}">{{ $val->invoice_no ?? '' }} [{{ date('d-M-Y', strtotime($val->created_at)) }}]</option>
                                        @endforeach
                                    @endif  
                                </select>
                                &nbsp;<button type="submit" class="btn btn-primary" target="_blank" ><i class="fa fa-money"></i> {{ __('Print Invoice') }}</button>
                       </div>
                       </form>
                          </div>  
                          
    </div>
        <table class="table table-bordered small_td p-3 " id="trColor">
                        <thead class='bg-primary'>
                            <tr>
                                <!--<th>{{ __('common.Date') }}</th>-->
                                <th>{{ __('Head Name') }}</th>
                                <th>{{ __('fees.Receipt No.') }}</th>
                                <th>Payment Date</th>
                                <th>{{ __('common.Amount') }}</th>
                                <th>Fine</th>
                                <th>{{ __('fees.Payment Mode') }}</th>
                                <th>Bank Name</th>
                                <th>Transaction Id</th>
                                <th>{{ __('common.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             @if(!empty($data['FeesDetailsInvoices']))
                                     
                                         @foreach($data['FeesDetailsInvoices'] as $val)
                          
                            <th></th>
                            <th>{{$val->invoice_no ?? ''}}</th>
                                         
                            
                                        @endforeach
                                    @endif  
                            
                        </tbody>
                </table>
    </div>
</div>
</div>


<div class="modal fade" id="revert_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Revert Fees Confirmation</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <form id="revert_fees_form" method="post">
      	    @csrf
            <div class="modal-body">
                <input type="hidden" id="admissionId" name="admission_id">
                <input type="hidden" id="fees_detail_id" name="fees_detail_id">
                <input type="hidden" id="collect_id" name="collect_id">
                <input type="hidden" id="revert_amount" name="revert_amount">
                <input type="hidden" id="sessionID_" name="session_id">
                <h5>{{ __('fees.Are you sure you want to revert fees ? This action is irreversible.') }}</h5>
            </div>
        
            <div class="modal-footer">
                <button type="button" id="hide_modal" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Return</button>
            </div>
        </form>
      </div>
    </div>
  </div>


<!-- Loading screen modal -->
<div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="w-100">
      <div class="modal-body text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="sr-only text-white">Collecting fees please wait...</span>
        </div>
        <p class="mt-2 text-white">Collecting fees please wait...</p>
      </div>
    </div>
  </div>
</div>

 <!-- Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Change</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to change the due date?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelChange" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmChange">Confirm</button>
                </div>
            </div>
        </div>
    </div>


<!--<div id='clonable' style='display:none'>-->
       
<!--</div>-->

 <style>
    /* Centering the loader */
    .loader {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 1050; /* Make sure this is higher than the modal backdrop */
    }
  </style>

 
<script>
  $(document).ready(function() {
            var currentTd, fees_assign_detail_id, value, old_value, field;

            $('#fee_structure').on('blur', '[name="installment_due_date"]', function() {
                currentTd = $(this);
                fees_assign_detail_id = currentTd.data('detail_id');
                value = currentTd.val();
                old_value = currentTd.data('old_value');
                field = currentTd.attr('name');



    
    
    
    function compareValues(value1, value2) {
         const date1 = Date.parse(value1);
    const date2 = Date.parse(value2);
    if (!isNaN(date1) && !isNaN(date2)) {
        // Both values are valid dates
        return date1 !== date2;
    }
    
    return false;
    
    }
                if (compareValues(value,old_value)) {
                    $('#confirmationModal').modal('show');
                }
            });

            $('#confirmChange').on('click', function() {
                $('#confirmationModal').modal('hide');
                
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/updateAssignedFees',  // Replace with your actual route
                    method: 'POST',
                    data: {
                        fees_assign_detail_id: fees_assign_detail_id,
                        value: value,
                        field: field
                    },
                    success: function(response) {
                        toastr.info('Due date has been changed.');
                        currentTd.data('old_value', value);
                        currentTd.val(value);
                        
                        $('#active_li').click();
                    },
                    error: function(xhr) {
                        console.log('An error occurred:', xhr);
                    }
                });
            });

            $('#cancelChange').on('click', function() {
                currentTd.val(old_value);
                $('#confirmationModal').modal('hide');
            });
        });
</script>
            
<script>

// $('#discount,#amount,#discount_per').on('input',function(){
//     toastr.info();
//     calculateAmt($(this).attr('id'))
// });




// function calculateAmt(this_id){
    
//     var discount_val = parseFloat($('#discount').val());
//     var discount_per = parseFloat($('#discount_per').val());
//     var remain_amount = parseFloat($('#remain_amount').val());
//     var amount = parseFloat($('#amount').val());
//     var final_amount = 0;
//     var final_discount = 0;
//     if(discount_per > 100){
//         $('#discount_per').val('');
//     }
//     if(isNaN(discount_val)){discount_val = 0;}
//     if(isNaN(discount_per)){discount_per = 0;}
//     if(isNaN(amount)){amount = 0;}
    
  
//     if(this_id == 'discount_per'){
        
//         final_discount = amount * discount_per / 100;
//         final_amount = amount - amount * discount_per / 100;
//          $('#pay_amt').val(final_amount); 
//          $('#discount_amount').val(final_discount);
//         $('#discount').val('');
        
//     }else if(this_id == 'discount'){
        
//         final_amount = amount - discount_val;
//          $('#discount_amount').val(discount_val);
//         $('#discount_per').val('');
//          $('#pay_amt').val(final_amount); 
//     }
 
    
    
//     $('#final_discount').val(final_discount);
//     if(final_amount < 0){
//         $('#discount_amount').val(amount);
//         $('#discount').val(amount);
//         $('#pay_amt').val(0); 
//         toastr.error('Final Amount Cannot be Negative');
        
//     }
    
// }

$(document).ready(function() {
    
    $('#loadingModal').modal({backdrop: 'static', keyboard: false})  

$('.selected_head').trigger('change');
$('.removeprodtxtbx').eq(0).hide();

$(document).on('keyup','.discount', function() {

    var $element = $(this);
    var $row = $element.closest('.row');
    
    // Get the change_amount and discount elements within the same row
    var $changeAmountElement = $row.find('.change_amount');
    var $discountElement = $row.find('.discount');
    
    // Get the data-validate_amount attribute value
    var validateAmount = parseFloat($changeAmountElement.data('validate_amount')) || 0;
    
    // Get the current values of change_amount and discount
    var changeAmountValue = parseFloat($changeAmountElement.val()) || 0;
    var discountValue = parseFloat($discountElement.val()) || 0;

    if ($element.hasClass('change_amount')) {
        // When change_amount value is modified, reset discount value to zero
        $discountElement.val(0);
        discountValue = 0;

        // Ensure change_amount does not exceed validate_amount
        if (changeAmountValue > validateAmount) {
            $changeAmountElement.val(validateAmount);
        }
    } else if ($element.hasClass('discount')) {
        // When discount value is modified
        if (discountValue > validateAmount) {
            // Set change_amount to validate_amount and discount to zero
            $changeAmountElement.val(validateAmount);
            $discountElement.val(0);
        } else if (changeAmountValue + discountValue > validateAmount) {
            // Adjust change_amount value so that total does not exceed validate_amount
            changeAmountValue = validateAmount - discountValue;
            $changeAmountElement.val(changeAmountValue < 0 ? 0 : changeAmountValue);
        }
    }

$('.change_amount').trigger('keyup');

    var discount = 0; 
    $('.discount').each(function() {
        var dis = parseFloat($(this).val());
        if (!isNaN(dis)) {
            discount += dis;
        }
    });
    
   
    $('#d_given').html(discount);
    $('#discount_given').val(discount);
     var total_amount =  parseFloat($('#total_amount').val());
    var total_fine_amount =  parseFloat($('#total_fine').val());
    var total_discount_amount =  parseFloat($('#discount_given').val());
     
    $('#g_total').html(((total_amount + total_fine_amount) + total_discount_amount)); 
    
});
    
$(document).on('keyup','.change_amount', function() {
    
    // var $element = $(this);
    // var $row = $element.closest('.row');
    
  
    // var $discountElement = $row.find('.discount');
    
    // if(Number($discountElement.val()) > 0){
    //     $discountElement.val(0);
    // }
    
    
    var validate_amount = Number($(this).attr('data-validate_amount'));
    var fine = Number($(this).attr('data-fine'));
    var amount = Number($(this).val());
    var id = Number($(this).attr('id').split('_')[1]);
   
    if(validate_amount<amount)
    {
        toastr.error("Amount can't be greater than pending amount" );
        $(this).val(validate_amount);
    }
    
    var amount_ = 0; 
    $('.amount_get').each(function() {
        var current_row_amount = parseFloat($(this).val());
        if (!isNaN(current_row_amount)) {
            amount_ += current_row_amount;
        }
    });
     
    $('#total_amount').val(amount_);
  //  $('#fine_'+id).val((amount*fine)/100);
    
    var fineAmount_ = 0; 
    $('.fine_amount').each(function() {
        var current_fine_amount = parseFloat($(this).val());
        if (!isNaN(current_fine_amount)) {
            fineAmount_ += current_fine_amount;
        }
    });
     
    $('#total_fine').val(fineAmount_);
     
    var total_amount =  parseFloat($('#total_amount').val());
    var total_fine_amount =  parseFloat($('#total_fine').val());
      var total_discount_amount =  parseFloat($('#discount_given').val());
    $('#g_total').html((total_amount + total_fine_amount) +total_discount_amount); 
    
    
});

$(document).on('keyup','.fine_amount', function() {
    
    var validate_fine = Number($(this).attr('data-validate_fine'));
    var fineAmt = Number($(this).val());
   
    if(validate_fine<fineAmt)
    {
        toastr.error("This Amount can't be greater than assigned fine amount" );
        $(this).val(validate_fine);
    }
    
    var fineAmount_ = 0; 
    $('.fine_amount').each(function() {
        var current_fine_amount = parseFloat($(this).val());
        if (!isNaN(current_fine_amount)) {
            fineAmount_ += current_fine_amount;
        }
    });
     
    $('#total_fine').val(fineAmount_);
    
    var total_amount =  parseFloat($('#total_amount').val());
    var total_fine_amount =  parseFloat($('#total_fine').val());
      var total_discount_amount =  parseFloat($('#discount_given').val());
     
    $('#g_total').html(((total_amount + total_fine_amount) + total_discount_amount)); 
    
     
  
});

 $('#receipt_by_head').change(function() {
    var selected_value = $(this).val();
    
    if(selected_value != '')
    {
          $('.receipt_group_id').hide();   
         $('.receipt_group_id_'+selected_value).show();   
    }
    else
    {
     
     $('.receipt_group_id').show();   
    }
    });
    

    
    
    $('#discount').blur(function() {
        discount_per = parseFloat($(this).val());
        amount = parseFloat($('#amount').val());
        
      if(discount_per > 0)
    {
        $('#discount_per').val(0);
           total = amount - discount_per;
        $('#pay_amt').val(total);
        $('#discount_amount').val(discount_per);
    }
     
    });
    $('#discount_per').blur(function() {
        amount = parseFloat($('#amount').val());
         discount_per = (amount*parseFloat($(this).val()))/100;
          if(discount_per > 0)
    {
         $('#discount').val(0);
         total = amount - discount_per;
        
        $('#pay_amt').val(total);
        $('#discount_amount').val(discount_per);
    }
        
    });

    $('#amount').change(function() {
        amount = parseFloat($(this).val());
        total_assign_amt = $('#total_assign_amt').html();
        remainingAmt = $('#remainingAmt').html();
  
        if(remainingAmt < amount){
            toastr.error('You Can not collect extra amount');
            $(this).val(remainingAmt);
        }else{
        $('#pay_amt').val(amount);
        }
         
    });
    
    $( "#submit_form" ).on( "submit", function( event ) {
 amount = parseFloat($('#amount').val());
 
 if(amount <=0)
 {
     toastr.error('Amount must be greater than zero');
  event.preventDefault();
 }
});

});

</script>
<style>
    .card{
        margin-bottom:0px;
        height:100%;
    }
    
    .colored_header{
        color:red;
    }
    
    .border_box{
        border: 1px solid black;
        padding:10px;
        margin-top: 10px;
    }
    
    .heading_text{
        font-size:18px;
        font-weight:600;
        color:#dc3545;
    }
    
    .padding_body{
        padding:10px;
    }
    
    .padding_table thead tr{
        background:#1f2d3d;
        color:white;
    }

    
    .padding_table th, .padding_table td{
        padding:5px;
        font-size:14px;
    }
    
    .absolute_row{
        position: absolute;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        bottom: 0;
    }
    .rotated-text {
       transform: rotate(-90deg); /* Rotate text 90 degrees clockwise */
        white-space: nowrap; /* Prevent text from wrapping */
     
     
}

    .tfoot_tr{
        background: skyblue;
        font-weight: 600;
    }
    .receipts {
       /* margin-top:-10px;*/
       /*transform: rotate(-5deg); */
       background-color:#49bf45;
       color:#fff;
       
       /* Rotate text 90 degrees clockwise */
        
     
     
}
</style>
          

            
           
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>


$('#payment_mode_id').on('change', function() {
    
    var payment_mode_id = $(this).val();
    if(payment_mode_id == 1){
        $('#transition_id_input').hide();
        $('#bank_name_input').hide();
        $('#bank_name').removeAttr('required');
        $('#bank_name').val('');
        $('#transition_id').removeAttr('required');
        $('#transition_id').val('');
    }else{
        $('#transition_id_input').show();
        $('#bank_name_input').show();
        $('#bank_name').attr('required',true);
        $('#bank_name').val('');
        $('#transition_id').attr('required',true);
        $('#transition_id').val('');
    }
    /*if(this.value == 9){
        
var total_amount=jQuery('#pay_amt').val();
    if(total_amount !=  0){
                  // console.log(result);
                   var options = {
                        "key": "{{ env('RAZORPAY_KEY') }}", 
                        "amount": total_amount*100, 
                       
                        "currency": "INR",
                        "name": "Rukmani Software",
                        "description": "Live Transaction",
                        "image": "https://www.rukmanisoftware.com/public/assets/img/header-logo.png",
                        "handler": function (response){
                         $("#transaction_id").val(response.razorpay_payment_id);  
                        }
                    };
                    
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                    $("#payment_mode_id").val(1); 
    }else{
                toastr.error('not a valid amount');
                 $('.transaction_slip').addClass('d-none'); 
                $("#payment_mode_id").val(1);  

       
    }
}*/
        
});
</script>
<script>
$(document).ready(function() {
 var head_added_count =1;
      var val = [];
      var slip ='';
     var head_length = $('.selected_head option').length-1;
   $(document).ready(function() {
    $('#aggregate_amount').on('keyup', function(event) {
        
        $('.discount').val('');
        $('.fine_amount').val('');
        
        if (event.key === "Enter") {
       
      $('.group_group').removeClass('bg-warning');
       $('#add_head_row .head_row').remove();
        head_added_count = 1;
       val = [];
       slip = '';
     head_length = $('.selected_head option').length-1;
      
 
        let data = @json($headArray);
        
    
        
            let aggregateAmount = parseFloat($(this).val());
            let total_pending = parseInt($("#validate_pending").data('pending'));
            
            if( aggregateAmount > total_pending)
            {
                toastr.error('Amount cannot be greater than total pending.');
                $('#aggregate_amount').val(0);
                return;
            }
            
            var leftAmount = aggregateAmount;
            var filterArray = [];
            data.forEach(item => {
                if (leftAmount > 0 && item.pending >0) {
                    filterArray.push(item);
                    leftAmount -= item.pending;
                  
                        
                    }
               
            });


if(filterArray.length > 0)
{
    
    var count = 0;
     filterArray.forEach(item => {
           count++;
             var amount =  parseInt(item.pending);
           if(count == 1)
           {
                   $('#add_head_row .selected_head').last().val(item.pending_by_group_id).prop('selected', true);
                 
                  
                $('#add_head_row .selected_head').last().trigger('change');
                if(filterArray.length === 1)
                    {
                           $('#add_head_row').find('.change_amount').last().val(amount+(leftAmount));
                           $('#add_head_row').find('.discount').last().val(parseInt(amount)-(amount+(leftAmount)));
                         
                    }
           }
           else
      {      
                 $('#clonebtn').trigger('click');
                 $('#add_head_row .selected_head').last().val(item.pending_by_group_id).prop('selected', true);
                $('#add_head_row .selected_head').last().trigger('change');
                
               
                   
                
                    if(filterArray.length === count)
                    {
                           $('#add_head_row').find('.change_amount').last().val(amount+(leftAmount));
                           $('#add_head_row').find('.discount').last().val(parseInt(amount)-(amount+(leftAmount)));
                         
                    }
                  
     }         
            
           
            $("#group_"+item.pending_by_group_id).addClass('bg-warning');       
            });
  
}
       const total = parseInt($('#total_amount').val());
       const discount_given = total-aggregateAmount;
       
       $('#discount_given').val(discount_given);
       $('#d_given').html(discount_given);
       $('#aggregate').html(aggregateAmount);
       
       
        }
    });
});

    
     
    function calculateTotalAmount(){
         var amount_ = 0; 
         var fine_amount = 0; 
        $('.amount_get').each(function() {
            var current_row_amount = parseFloat($(this).val());
            if (!isNaN(current_row_amount)) {
                amount_ += current_row_amount;
            }
        });
        $('.fine_amount').each(function() {
            var current_fine_amount = parseFloat($(this).val());
            if (!isNaN(current_fine_amount)) {
                fine_amount += current_fine_amount;
            }
        });
        
         $('#total_amount').val(amount_);
         $('#total_fine').val(fine_amount);

         grandTotal();
     }
     
     function grandTotal(){
        var total_amount =  parseFloat($('#total_amount').val());
        var total_fine_amount =  parseFloat($('#total_fine').val());
         
        $('#g_total').html((total_amount + total_fine_amount)); 
         
     }
    
    
    $(document).on('click','.revert_fees',function(){
       var fees_detail_id = $(this).data('id'); 
       var collect_id = $(this).data('collect_id'); 
       var revert_amount = $(this).data('revert_amount'); 
       var admission_id = $(this).data('admission_id'); 
       var sessionID_ = $(this).data('session_id'); 
       
       $('#fees_detail_id').val(fees_detail_id);
       $('#collect_id').val(collect_id);
       $('#revert_amount').val(revert_amount);
       $('#admissionId').val(admission_id);
       $('#sessionID_').val(sessionID_);
    });
    
    
   
     
     
     function selectBox(){
         length = $('.selected_head').length;
      val = [];
     
     for(var i=0; i<length; i++)
     {
         val.push($('.selected_head').eq(i).val())
     }
     
     calculateTotalAmount();
    
     }
     
    $(document).on("change",".selected_head", function(){

    var selected_head = $(this).val();

  
  
    var key = $(this).attr('data-key1');
   
    var pending_amount = Number($('#pending_by_group_id_'+selected_head).attr('data-pending_amount'));
    var fine = Number($('#pending_by_group_id_'+selected_head).attr('data-fine'));
  
   
    if(pending_amount > 0)
    { 
        $('#amount_'+key).val(pending_amount)
        $('#fine_'+key).val((pending_amount*fine)/100);
        $('#amount_'+key).attr('data-validate_amount',pending_amount);
        $('#amount_'+key).attr('data-fine',fine);
        
      
    }
    else
    {
         toastr.options = {
            "preventDuplicates": true,
            "preventOpenDuplicates": true
            };

        if(isNaN(pending_amount))
        {
        toastr.error('!!! Please Select Head !!!')
        }
        else
        {
             toastr.error('!!! No Amount to be taken !!!')
        }
          $('#amount_'+key).val(pending_amount);
          $('#fine_'+key).val(fine);
         $('#amount_'+key).attr('data-validate_amount',pending_amount);
    }
   
   
     if (val.includes(selected_head) && selected_head != '') {
            toastr.error('This head is already selected.');
            $(this).val('');
         $('#amount_'+key).val(0);
          $('#fine_'+key).val(0);
         $('#amount_'+key).attr('data-validate_amount',0);
        }
         selectBox()
    });
    
    
    $(".addmoreprodtxtbx").click(function() {

           var headRowContent = $('#head_row').html();
           var $headRow = $(headRowContent);
            $headRow.find('select').val('');
            
           var old_select = document.querySelectorAll('select[name="selected_head[]"]');
           var old_slip = document.querySelectorAll('input[name="slip_no[]"]')[document.querySelectorAll('input[name="slip_no[]"]').length - 1].value;
        
            val.push(old_select[old_select.length - 1].value);
        if((head_added_count) < head_length)
        {
           $('#add_head_row').append('<div class="row m-2 head_row">' + headRowContent + '</div>');
             var newInput = $('#add_head_row').find('input[name="amount[]"]:last');
    var newId = 'amount_' + ($('#add_head_row').find('input[name="amount[]"]').length - 1);
    newInput.attr('id', newId);
    
    var newInput1 = $('#add_head_row').find('input[name="fine[]"]:last');
    var newId1 = 'fine_' + ($('#add_head_row').find('input[name="fine[]"]').length - 1);
    newInput1.attr('id', newId1);
    
    $('#add_head_row').find('input[name="slip_no[]"]:last').attr('value',parseInt(old_slip)+1)
    //  var newInput = $('#add_head_row').find('input[name="amount[]"]:last');
    // var newId = 'amount_' + ($('#add_head_row').find('input[name="amount[]"]').length - 1);
    // newInput.attr('id', newId);
    
    var selects = document.querySelectorAll('select[name="selected_head[]"]');
            var lastSelect = selects[selects.length - 1];
            lastSelect.setAttribute('data-key1', head_added_count);
          
          Array.from(lastSelect.options).forEach(function(option) {
                if (val.indexOf(option.value) > -1) {
                  //  option.remove();// Disable the option
                }
            });
            
       $('.selected_head').eq($('.selected_head').length-1).trigger('change')
     head_added_count++;
     disbledButton()
        }
        else
        {
            toastr.options = {
"preventDuplicates": true,
"preventOpenDuplicates": true
};
            toastr.error('No Head Left');
        }
            
            
        });
        
        $(document).on('click','.removeprodtxtbx', function() {
        // Find the parent element with class 'head_row' and remove it
       var  parent = $(this).closest('.head_row');
          var selectBoxValue = parent.find('select').val();
          
          
           if (selectBoxValue !== null) {
          
            val = val.filter(function(value) {
                return value !== selectBoxValue;
            });
           }
     $(this).closest('.head_row').remove();
        head_added_count--;
        disbledButton();
        calculateTotalAmount();
    });
    
    
    function disbledButton(){
        $('.removeprodtxtbx').show();
        $('.removeprodtxtbx').eq(0).hide();
        
    } 
    
    
/*     $('#submit_form').submit(function(event) {
        event.preventDefault();
        
         if (selectedValues.includes(selectedValue)) {
            // Value is already selected, perform your error handling or action here
            alert('This value is already selected.');
            // Uncheck the clicked element
            $(this).prop('checked', false);
        } 
        
});*/
});






</script>



<script>
$(document).ready(function(){
    var BASEURL = "{{ url('/') }}";
    
    $('#revert_fees_form').submit(function(event){
        event.preventDefault();
        var formData = $('#revert_fees_form').serialize(); 
        
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            url: BASEURL + '/collect_fees_delete',
            data: formData,
            success: function(data) {
                if(data.status == 'success'){
                    $('#hide_modal').click();
                    toastr.success('Fee Revert Successfully');
                    setTimeout(function() {
                         showData(data.unique_system_id,data.session_id);
                    }, 800);
                }
            }
        });
    });    
    
    
    
  $("#myForm").submit(function(event){
       
    event.preventDefault();
$('#loadingModal').modal('show');
$('#collect_btn').hide();
 var formData = $('#myForm').serialize(); 
    

    
   $.ajax({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        },
        type: 'post',
        url: BASEURL + '/student_pay_submit',
        data: formData,
        success: function(data) {
            if(data.status == 'success')
            {
                $('#loadingModal').modal('hide');
                $('#collect_btn').show();
                  toastr.success('Fee Collected Successfully');
                  showData(data.unique_system_id,data.session_id);
            }
            else
            {
                $('#collect_btn').show();
                $('#loadingModal').modal('hide');
                toastr.error('Something Went Wrong');
            }
        }
    });
  });
});
</script>

<script>
    $(document).ready(function(){
        var BASEURL = "{{ url('/') }}";
        $('.tab').click(function(){
            $('.tab').removeAttr('id');
            $(this).attr('id','active_li');
            var session_id = $(this).data('id');
            var unique_system_id = $(this).data('unique_system_id');
            
            if(session_id != ""){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'post',
                    url: BASEURL + '/student_fees_onclick',
                    data: {unique_system_id : unique_system_id,session_id : session_id},
                    success: function(data) {
                        if(data != 0){
                            $('#student_fees_detail').html(data);
                        }else{
                            $('#notfound').hide();
                            $('.not_found_div').show();
                        }
                    }
                });
            }
        }); 
    });
</script>
