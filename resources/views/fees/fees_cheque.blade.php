@php
  $courses = Helper::getCourses();
  $classType = Helper::classType($search['course_id'] ?? null);
  $allClassType = Helper::classType();
  $batches = Helper::getBatch();
  $getSession = Helper::getSession();
  $getCountry = Helper::getCountry();
  $role = Helper::roleType();
  $actionPermission = Helper::actionPermission();
@endphp

@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   FEES APPROVAL - EXCEL GREEN EXECUTIVE THEME (#217346)
   ======================================================== */
:root {
    --green-excel: #217346;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
}

.fees-cheque-page {
    background-color: #f4f6f9;
    font-size: 12px;
}

.dash-main-card {
    border: 1px solid var(--line) !important;
    border-top: 3px solid var(--green-excel) !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06) !important;
    background: #ffffff;
    margin-bottom: 12px;
    overflow: hidden;
}

.dash-titlebar {
    min-height: 42px;
    padding: 6px 14px;
    background: var(--green-excel) !important;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.dash-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 700;
    letter-spacing: 0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #ffffff !important;
}

.btn-header-action {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    padding: 4px 10px !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.btn-header-action:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
}

.padding_table thead tr {
    background: #1e293b !important;
    color: white !important;
}
.padding_table th, .padding_table td {
    padding: 6px 8px !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}
.padding_table tbody tr:hover {
    background-color: #f0fdf4 !important;
}
</style>

<div class="content-wrapper fees-cheque-page">
   <section class="content pt-2">
        <div class="container-fluid px-2">
            <div class="dash-main-card">
                <div class="dash-titlebar">
                    <h1 class="dash-title">
                        <i class="fa fa-check-square-o text-warning"></i>
                        <span>{{ __('Fees Approval') }}</span>
                    </h1>
                    <div class="dash-title-tools">
                        <a href="{{ url('fee_dashboard') }}" class="btn-header-action" title="Back">
                            <i class="fa fa-arrow-left text-secondary mr-1"></i> {{ __('messages.Back') }}
                        </a>
                    </div>
                </div>  
        
        <form id="quickForm" action="{{ url('fees_cheque') }}" method="post">
          @csrf
          <div class="row m-2">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Session</label>
                    <select class="form-control select2" id="session_id" name="session_id">
                        <option value="all" {{ ($search['session_id'] ?? '') == 'all' ? 'selected' : '' }}>All</option>
                        @if(!empty($getSession))
                            @foreach($getSession as $session)
                                <option value="{{ $session->id ?? '' }}" {{ ($session->id == ($search['session_id'] ?? Session::get('session_id'))) ? 'selected' : '' }}>{{ $session->from_year ?? ''  }} - {{ $session->to_year ?? ''  }}</option>
                            @endforeach
                        @endif
                    </select>                 	    
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>{{ __('Course') }}</label>
                    <select class="form-control select2" id="course_id" name="course_id">
                        <option value="">{{ __('common.Select') }}</option>
                        @if(!empty($courses))
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ ($course->id == ($search['course_id'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>{{ __('Class/Semester') }}</label>
                    <select class="form-control select2" id="class_type_id" name="class_type_id">
                        <option value="">{{ __('common.Select') }}</option>
                        @if(!empty($classType))
                            @foreach($classType as $class)
                                <option value="{{ $class->id ?? '' }}" {{ ($class->id == ($search['class_type_id'] ?? '')) ? 'selected' : '' }}>{{ $class->name ?? '' }}</option>
                            @endforeach
                        @endif
                    </select>                 	    
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label>{{ __('Batch') }}</label>
                    <select class="form-control select2" id="batch" name="batch">
                        <option value="">{{ __('common.Select') }}</option>
                        @if(!empty($batches))
                            @foreach($batches as $batch)
                            <option value="{{ $batch->name ?? ''  }}" {{ ($batch->name == ($search['batch'] ?? '')) ? 'selected' : '' }}>{{ $batch->name ?? ''  }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label for="admissionNo">{{ __('student.Admission No.') }}</label>
                    <input type="text" class="form-control" id="admissionNo" name="admissionNo" placeholder="{{ __('student.Admission No.') }}" value="{{ $search['admissionNo'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label>{{ __('fees.From Date') }}</label>
                    <input type="date" class="form-control" id="starting" name="starting" value="{{ $search['starting'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label>{{ __('fees.To Date') }}</label>
                    <input type="date" class="form-control" id="ending" name="ending" value="{{ $search['ending'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group"> 
                    <label>{{ __('messages.Keywords') }}</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="{{ __('messages.Search') }}" value="{{$search['name'] ?? ''}}">
                </div>
            </div>
            <div class="col-md-1">
                <label for="" style="color: white;">Search</label>
                <button type="submit" class="btn btn-primary" >{{ __('messages.Search') }}</button>
            </div>
          </div>
        </form>
        
    	<div class="row m-2">
    	    <div class="col-md-12 head_table text-center"></div>
    	    </div>
    	<div class="row m-2">
		    <div class="col-md-12  ">	
        <table id="example1" class="table table-bordered table-striped dataTable dtr-inline padding_table">
          <thead>
          <tr role="row">
            <th>{{ __('messages.Sr.No.') }}</th>
            <th>Receipt No.</th>
            <th>Status</th>
            <th>Transaction Id/UTR No.</th>
            <th>{{ __('student.Admission No.') }}</th>
              <th>Class</th>
                <th>{{ __('Student Name') }}</th>
            <th>{{ __('messages.Fathers Name') }}</th>
            <th>{{ __('messages.Mobile') }}</th>
            <th>{{ __('Payment date') }}</th>
            <th>{{ __('Payment Modes') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Action') }}</th>
         
          </thead>
         <tbody>
             
             @if(!empty($data))
             @php
             $amount = 0;
             @endphp
             @foreach($data as $key => $receipt)
              
             <tr>
                 
                 <td>{{$key+1}}</td>
               
                 <td class='d-flex'>
                  
                     <form action={{url('printFeesInvoice')}} method="post" target="_blank">
                         @csrf
                         <button class="text-primary" type="submit" id="fees_details_invoice_id" style="border: none; background: transparent; border-bottom: 2px solid #1f2d3d;" name="fees_details_invoice_id" value="{{$receipt->id ?? ''}}">{{$receipt->invoice_no ?? ''}}</button>
                     </form>
                    
                 </td>
                 <td>
                    @if($receipt->status == 0)
                        <span style="color: green;">Approved</span>
                    @elseif($receipt->status == 1)
                        <span style="color: red;"> Pending </span>
                    @elseif($receipt->status == 2)
                       <span style="color: red;">Cancel/Dishonoured </span>
                    @endif
                </td>
                <td>{{ $receipt->transaction_id ?? '' }} 
                    @if(!empty($receipt->payment_receipt))<i class="fa fa-file-image-o pointer receiptAttachment" data-img=" {{ env('IMAGE_SHOW_PATH'). 'payment_receipt/' . $receipt->payment_receipt }} "></i>@endif
                    <small class="text-primary">A/C Holder : {{ $receipt->account_holder ?? '' }}</small>
                </td>
                 <td>{{$receipt->admissionNo ?? ''}}</td>
                 <td>{{$receipt->class_name ?? ''}}</td>
                 <td>{{$receipt->first_name ?? ''}} {{$receipt->last_name ?? ''}}</td>
                 <td>{{$receipt->father_name ?? ''}}</td>
                 <td>{{$receipt->mobile ?? ''}}</td>
                 <td>{{date('d-m-Y', strtotime($receipt->payment_date ?? ''))}}</td>
                 <td>{{$receipt->payment_mode ?? ''}}</td>
                 <td>
                    {{$receipt->amount ?? ''}}
                    @php
                    $amount +=$receipt->amount;
                    @endphp
                 </td>
                 	<td> 
                        <select name="status" data-id="{{ $receipt['id'] ?? '' }}" class="form-control statusDrop ">
                            <option value="1" {{ $receipt->status == 1 ? 'selected' : '' }}> Pending </option>
                              <option value="0" {{ $receipt->status == 0 ? 'selected' : '' }}>Approved </option>
                              
                              <option value="2" {{ $receipt->status == 2 ? 'selected' : '' }}> Cancel/Dishonoured</option>
                     </select>
                        
					</td>
             </tr>
             
             @endforeach
              
             @endif
         </tbody>
         <tfoot>
 <tr>
                 
                 <td colspan="11" style="text-align: right;"> Total Amount</td>
               
                 <td colspan="2"> {{$amount ??  ''}} </td>
              
             </tr></tfoot>
        </table>
        </div>
        </div>
    </div>

    </div>
  </div>
</div>
</section>
</div>

<script src="{{URL::asset('public/assets/school/js/jquery.min.js')}}"></script>
<script>
$('.profileImg').click(function(){
    var profileImgUrl = $(this).data('img');
    if(profileImgUrl != ''){
        $('#profileImgModal').modal('toggle');
        $('#profileImg').attr('src',profileImgUrl);
    }
});
</script>
 <div id="profileImgModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">

    <div class="modal-content">
      <!--<div class="modal-header">
        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
      </div>-->
      <div class="modal-body">
        <img id="profileImg" src="" width="100%" height="470px">
      </div>
     <!-- <div class="modal-footer">
        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
      </div>-->
    </div>

  </div>
</div>       

<div class="modal fade" id="statusModal">
  <div class="modal-dialog">
    <div class="modal-content" style="background: #ffffff;"> <!-- Changed background to white -->
      <div class="modal-header">
        <h4 class="modal-title text-dark">Change Status Confirmation</h4> <!-- Changed text color to dark -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <i class="fa fa-times" aria-hidden="true"></i>
        </button>
      </div>

      <form action="{{ url('fees_cheque') }}" method="post">
        @csrf
        <div class="modal-body">
          <input type="hidden" id="status_id" name="status_id">
          <input type="hidden" id="id" name="id">
         
          <h5 class="text-dark" id='status_message'>Are you sure you want to change the status?</h5> <!-- Changed text color to dark -->
          
          <div class='form-group'>
              <label for="remark" class='form-label'>Remark</label> <!-- Added 'for' attribute for better accessibility -->
              <textarea id="remark" name="remark" class="form-control" rows="3" placeholder="Enter your remarks here..."></textarea> <!-- Added form-control for styling -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> <!-- Changed button color to secondary -->
          <button type="submit" class="btn btn-danger">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="receiptAttachmentModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg ">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <img id="receiptAttachmentImg" class="d-none" src="" width="100%" height="100%" >
        <iframe id="receiptAttachmentIframe" class="d-none" src="" width="100%" height="480px" ></iframe>
        <i id="fileIcon" class="fa fa-file d-none" style="font-size: 50px;"></i>
      </div>
      <div class="modal-footer" style="display:flex;justify-content:space-between;">
           <a href="" id="downloadLink" download><button class="btn btn-primary btn-xs" title="Download Image"><i class="fa fa-download" aria-hidden="true"></i> Download Receipt</button></a>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script>
    $(document).ready(function(){
        
        $('.statusDrop').change(function(){
           var status = $(this).val(); 
            $('#status_id').val(status);
            $('#id').val($(this).data('id'));
           
           
           if(status == 2)
           {
            //   $('#status_message').html('This cheque has been dishonoured by the bank for.the.reason.…');
               $('#remark').html('This Transaction has been dishonoured by the bank for.the.reason.…');
           }
           else if(status == 0)
           {
              // $('#status_message').html('Cheque has been realised');  
                     $('#remark').html('Transaction has been approved');
           }
           else
           {
              // $('#status_message').html('This cheque is pending.realisation');  
                     $('#remark').html('This Transaction is pending');
           }
           
           
            $('#statusModal').modal('show');
        });
        
        $('.deleteData').click(function() {
        	var delete_id = $(this).data('id');
        	$('#delete_id').val(delete_id);
        });
        
        $('.userStatus').click(function(){
            var status = $(this).data('status');
            $('#status_id').val(status);
            $('#id').val($(this).data('id'));
        });

        $('.receiptAttachment').click(function(){
            var receiptFileUrl = $(this).data('img');
            receiptFileUrl = receiptFileUrl.split('?')[0];
            var fileExtension = receiptFileUrl.split('.').pop().toLowerCase().trim();
          
            if(receiptFileUrl !== ''){
                $('#receiptAttachmentModal').modal('toggle'); 
                $('#downloadLink').attr('href', receiptFileUrl);

                if (["jpg", "jpeg", "png", "gif", "webp"].includes(fileExtension)) {
                    $('#receiptAttachmentImg').attr('src', receiptFileUrl).removeClass('d-none');
                    $('#receiptAttachmentIframe, #fileIcon, #fileMessage').addClass('d-none'); 
                } else if (fileExtension === "pdf") {
                    $('#receiptAttachmentIframe').attr('src', receiptFileUrl).removeClass('d-none');
                    $('#receiptAttachmentImg, #fileIcon, #fileMessage').addClass('d-none'); 
                } else {
                    $('#fileIcon, #fileMessage').removeClass('d-none');
                    $('#receiptAttachmentImg, #receiptAttachmentIframe').addClass('d-none');
                }
            } else {
                toastr.danger("No file URL found");
            }
        });

        function updateClassDropdown(classSelect, data) {
            classSelect.empty();
            classSelect.append('<option value="">{{ __("common.Select") }}</option>');
            if (data && data.length > 0) {
                $.each(data, function(key, val) {
                    classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                });
            }
            if (classSelect.hasClass("select2-hidden-accessible")) {
                classSelect.select2('destroy');
            }
            classSelect.select2();
            classSelect.val('').trigger('change');
        }

        $(document).on('change', '#course_id', function() {
            var courseId = $(this).val();
            var classSelect = $('#class_type_id');
            if (courseId) {
                $.ajax({
                    url: "{{ url('getClassesByCourse') }}",
                    type: "GET",
                    data: { course_id: courseId },
                    dataType: "json",
                    success: function(data) {
                        updateClassDropdown(classSelect, data);
                    },
                    error: function() {
                        updateClassDropdown(classSelect, []);
                    }
                });
            } else {
                var defaultClasses = [];
                @if(!empty($allClassType))
                    @foreach($allClassType as $type)
                        defaultClasses.push({ id: "{{ $type->id }}", name: "{{ $type->name }}" });
                    @endforeach
                @endif
                updateClassDropdown(classSelect, defaultClasses);
            }
        });
    });
</script>
@endsection 