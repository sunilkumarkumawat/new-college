    @php
  $feesType = Helper::feesType();
  $classType = Helper::classType();
  $actionPermission = Helper::actionPermission();
  $getPermission = Helper::getPermission();

@endphp
@extends('layout.app') 
@section('content')

<style>
/* ========================================================
   FEES REMINDER - EXCEL GREEN EXECUTIVE THEME (#217346)
   ======================================================== */
:root {
    --green-excel: #217346;
    --line: #b8c8d5;
    --line-subtle: #cbd5e1;
}

.fees-reminder-page {
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
</style>

<div class="content-wrapper fees-reminder-page">
    <section class="content pt-2">
      <div class="container-fluid px-2">
        <div class="row">   
    <div class="col-md-12 {{($getPermission->add == 1) ? '' : 'd-none'}}">
            <div class="dash-main-card">
             <div class="dash-titlebar">
                <h1 class="dash-title"><i class="fa fa-clock-o text-warning mr-1"></i> {{ __('fees.Add Fees Reminder')}} </h1>
                <div class="dash-title-tools">
                    <a href="{{ url('fee_dashboard') }}" class="btn-header-action" title="Back">
                        <i class="fa fa-arrow-left text-secondary mr-1"></i> Back
                    </a>
                </div>
            </div>   
            
        
                    <div class="row m-2">
                    
                    	           
                        <div class="col-md-2">
            		<div class="form-group">
            			<label>{{ __('student.Admission No.') }}</label>
            			<input type="text" id="admissionNo" name="admissionNo" placeholder = "{{ __('student.Admission No.') }}" class="form-control">
            	    </div>
            	</div>
                        <div class="col-md-2">
            		<div class="form-group">
            			<label>{{ __('messages.Class') }}</label>
            			<select class="form-control select2" id="class_type_id " name="class_search_id" >
            			<option value="">{{ __('messages.Select') }}</option>
                         @if(!empty($classType)) 
                              @foreach($classType as $type)
                                 <option value="{{ $type->id ?? ''  }}">{{ $type->name ?? ''  }}</option>
                              @endforeach
                          @endif
                        </select>
            	    </div>
            	</div>
            	

            		<div class="col-md-4">
    			<div class="form-group">
    				<label>{{ __('messages.Search By Keywords') }}</label>
    				<input type="text" class="form-control" id="searchName" name="name" placeholder="{{ __('messages.Ex. Name, Mobile, Email, Aadhaar, Father/Mother Name etc.') }}" value="{{ $search['name'] ?? '' }}">
    		    </div>
    		</div>                      	
                        <div class="col-md-1 ">
                             <label class="text-white">Search</label>
                    	    <button type="button" class="btn btn-primary" onclick="SearchValue()" >{{ __('messages.Search') }}</button>
                    	</div>
                   			
                    </div>
                      
           
            
                <form id="quickForm" action="{{ url('fees_reminder') }}" method="post">
  
                @csrf
                <div class="row m-2 student_list_show">
                    
                </div>
                
                 <hr>
                 
                <div class="row m-2">
                        <div class="col-md-3">
                			<label style="color:red;">{{ __('fees.Reminder Name') }}*</label>
                			<input class="form-control @error('name') is-invalid @enderror"  onkeydown="return /[a-zA-Z ]/i.test(event.key)" type="text" name="name" id="name" placeholder="{{ __('fees.Reminder Name') }}">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror                    			
                    	</div>
                        
                        <div class="col-md-3">
                			<label style="color:red;">{{ __('fees.Reminder Date') }}*</label>
            				<input class="form-control @error('reminder_date') is-invalid @enderror" type="date" id="reminder_date" name="reminder_date">
                             @error('reminder_date')
            					<span class="invalid-feedback" role="alert">
            						<strong>{{ $message }}</strong>
            					</span>
            				@enderror                  			
                    	</div>
                        
                        <div class="col-md-3">
                			<label style="color:red;">{{ __('fees.Due Date') }}*</label>
            				<input class="form-control @error('due_date') is-invalid @enderror" type="date" id="due_date" name="due_date" placeholder="Due Date">
                             @error('due_date')
            					<span class="invalid-feedback" role="alert">
            						<strong>{{ $message }}</strong>
            					</span>
            				@enderror                  			
                    	</div>                     	
                     	
                                       	
                     	
                        <div class="col-md-7">
                    			<label style="color:red;">{{ __('fees.Reminder Message') }}*</label>
                    			<textarea class="form-control @error('message') is-invalid @enderror mb-3" type="text" name="message" id="message" placeholder="{{ __('fees.Reminder Message') }}"></textarea>
                             @error('message')
            					<span class="invalid-feedback" role="alert">
            						<strong>{{ $message }}</strong>
            					</span>
            				@enderror 
                    	</div>                      	
                </div>
 
                <div class="row m-2">
                    <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary">{{ __('messages.submit') }} </button>
                    </div>
                </div>
                </form>
            </div>          
        </div>
        
    <div class="col-md-12">
            <div class="card card-outline card-orange ml-1">
             <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fa fa-money"></i> &nbsp;{{ __('fees.Fees Reminder List') }} </h3>
            <div class="card-tools">
            <!--<a href="{{url('students/add')}}" class="btn btn-primary  btn-sm" ><i class="fa fa-plus"></i> Add</a>-->
            <a href="{{url('fee_dashboard')}}" class="btn btn-primary  btn-sm" ><i class="fa fa-arrow-left"></i> {{ __('messages.Back') }} </a>
            </div>
             </div>  
              <div class="row m-2">
                    <div class="col-md-12">
                	</div>
                    <div class="col-md-12"  style="overflow-x:auto;">
                       <table id="" class="table table-bordered table-striped dataTable dtr-inline ">
                          <thead>
                          <tr role="row">
                              <th>{{ __('messages.Sr.No.') }}</th>
                              @if($getPermission->edit == 1)
                              <th>{{ __('master.Status') }}</th>
                              @endif
                              <th>{{ __('fees.Reminder') }}</th>
                              <th>{{ __('fees.Due Date') }}</th>
                              <th>{{ __('fees.Reminder Date') }}</th>
                              <th>{{ __('fees.Message') }}</th>
                              @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                              <th>{{ __('messages.Action') }}</th>
                              @endif
                            </tr>
                          </thead>
                          <tbody id="">
                          
                          @if(!empty($dataview))
                                @php
                                   $i=1
                                @endphp
                                @foreach ($dataview  as $item)
                                <tr>
                                        <td>{{ $i++ }}</td>
                                        @if($getPermission->edit == 1)
                                        <td> 
										@if($actionPermission['edit'] == 1)
										
										@if($item->status==1)

                                        <button data-toggle="modal" data-target="#statusModal" data-id="{{ $item['id'] ?? '' }}" class="btn btn-success btn-sm reminderStatus" data-status="{{$item['status'] ?? ''}}">Active</button>
                                                         
                                        @else
                                            
                                        <button data-toggle="modal" data-target="#statusModal" data-id="{{ $item['id'] ?? '' }}" class="btn btn-danger btn-sm reminderStatus" data-status="{{$item['status'] ?? ''}}">Inactive</button>
                                                           
                                        @endif 
									<!--	<a data-id="{{ $item['id'] ?? '' }}" style="{{  $item['status'] == 0 ? 'display:none'   : ''  }}" data-status="0" class="btn btn-xs btn-success change_status" data-bs-toggle="modal" data-bs-target="#statusModel"> &nbsp; Active &nbsp;</a>
										<a data-id="{{ $item['id'] ?? '' }}" style="{{  $item['status'] == 1 ? 'display:none'   : ''  }}" data-status="1" class="btn btn-xs btn-danger change_status" data-bs-toggle="modal" data-bs-target="#statusModel">Deactive</a> -->
									
										<!--<button style="{{  $item['status'] == 0 ? 'display:none'   : ''  }}" class="btn btn-xs btn-success disabled"> &nbsp; Active &nbsp;</button>
										<button style="{{  $item['status'] == 1 ? 'display:none'   : ''  }}" class="btn btn-xs btn-danger disabled">Deactive</button> -->
										@endif
										</td>
										@endif
                                        <td>{{ $item['name'] ?? '' }}</td>
                                        <td>{{date('d-m-Y', strtotime($item['due_date'])) ?? '' }}</td>
                                        <td>{{date('d-m-Y', strtotime($item['reminder_date'])) ?? '' }}</td>
                                        <td>{{ $item['message'] ?? '' }} </td>
                                        @if($getPermission->edit == 1 || $getPermission->deletes == 1)
                                        <td>
                                            <a  class="pl-3" data-toggle="dropdown" aria-expanded="false">
                                             <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            </a>
                                            <ul class="dropdown-menu" style="">
                                             @if($getPermission->edit == 1)
                                              <a href="{{ url('fees_reminder_edit') }}/{{$item['id'] ?? '' }}">
                                                  <li class="dropdown-item text-primary" title="Edit">
                                                      <i class="fa fa-edit text-primary"></i>
                                                      Edit
                                                </li>
                                              </a>
                                            @endif
                                             @if($getPermission->deletes == 1)
                                              <a href="javascript:;"  data-id='{{$item->id}}' data-bs-toggle="modal" data-bs-target="#Modal_id"  class="deleteData">
                                                  <li class="dropdown-item text-danger" title="Delete"><i class="fa fa-trash-o text-danger"></i>
                                                      Delete
                                                </li>
                                                </a>
                                                @endif
                                            </ul>
                                        </td>
                                        @endif
                                    </tr>
                            @endforeach
                            @endif
                          </tbody>
                        </table>
                	</div>
                </div>
            </div>          
        </div>
    </div>  

</div>
</section>
</div>
<script>
    $(document).on('click', ".reminder_status", function () {
    var id = $(this).data("id");
    var status = $(this).data("status");
    var basurl = "{{ url('/') }}";
    if(confirm('Are you sure ?')){
      
        $.ajax({
            url: basurl+'fees_reminder_status',
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { status: status, id: id },
            success: function (response) {

                /*if (response == 0) {
                    alert("Internal Server Error");
                }else if (response == 1) {
                    alert("Internal Server Error");
                }
                else {
                    alert("Internal Servasaser Error");
                }*/
            },
        });
    }

});

</script>

<script>
    
        function SearchValue() {
            var name = $('#searchName').val();
            var registration_no = $('#registration_no').val();
            var class_search_id = $('#class_type_id :selected').val();
            var admissionNo = $('#admissionNo').val();
            if(class_search_id > 0 || registration_no != '' || name != ''){
               $.ajax({
                     headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
                type:'post',
                url: '/fees_reminder_search',
                data: {class_search_id:class_search_id,name:name,admissionNo:admissionNo},
                success: function (data) {

                    $('.student_list_show').html(data);
                   
                }
              }); 
            }else{
                toastr.error('Please put a value in one column !');
            }
        };
        
        function showData(student_id) {
            $.ajax({
                     headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
                type:'post',
                url: '/student_add_click',
                data: {student_id : student_id},
                 dataType: 'json',
                success: function (data) {

                 if(data){
                $('#first_name').val(data.first_name);
                $('#last_name').val(data.last_name);
                $('#aadhaar').val(data.aadhaar);
                $('#student_id').val(data.name);
                $('#gender_id').val(data.gender_id);
                $('#class_type_id').val(data.class_type_id);
                $('#dob').val(data.dob);
                $('#mobile').val(data.mobile);
                $('#email').val(data.email);
                $('#father_name').val(data.father_name);
                $('#mother_name').val(data.mother_name);
                $('#father_mobile').val(data.father_mobile);
                $('#admission_type_id').val(data.admission_type_id);
               
                $('#sms_contact_no').val(data.sms_contact_no);
                $('#village_city').val(data.village_city);
                $('#address').val(data.address);
                $('#pincode').val(data.pincode);
                $('#remark_1').val(data.remark_1);
                $('#country_id1').val(data.country_id);
                $('.stateId').val(data.state_id);
                $('.cityId').val(data.city_id);
               
                $('#student_img').val(data.student_img);
                $('#father_img').val(data.father_img);
                $('#mother_img').val(data.mother_img);
                $('#student_roll_no').val(data.roll_no);
                
                $('#school_name').val(data.school_name);
                $('#date_of_admission').val(data.dob);
                 }else{
                     alert('No more records found');
                 }
                    
                }
              });
        };
        
</script>

<script>
    $('#fees_type_id').on('change', function(e){
    
	var fees_type_id = $(this).val();
    $.ajax({
         headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
	  url: '/duedate/'+fees_type_id,
	  success: function(data){
      $("#due_date").val(data);
	  }
	});	
});
</script>

<script>
    $('.Status').click(function() {
	var status = $(this).data('id');
	$('#status').val(status);
});
/*$(document).on('click', ".change_status", function() {
	var id = $(this).data("id");
	var status = $(this).data("status");
	if(confirm('Are you sure you want to Change Status ?')) {
		$.ajax({
			url: 'fees_reminder_status',
			type: 'post',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				status: status,
				id: id
			},
			success: function(response) {
				if(response == 0) {
					location.reload(true);
				} else if(response == 1) {
					location.reload(true);
				} else {
					alert("Internal Servasaser Error");
				}
			},
		});
	}
});*/
</script>
<script>
  $('.deleteData').click(function() {
  var delete_id = $(this).data('id'); 
  $('#delete_id').val(delete_id); 
  } );
  
  
  $('.reminderStatus').click(function(){
      var status = $(this).data('status');
      var id = $(this).data('id');
      $('#id').val(id);
      $('#status_id').val(status);
  });
</script>

<!-- The Modal -->
<div class="modal" id="statusModal">
  <div class="modal-dialog">
    <div class="modal-content" style="background: #555b5beb;">

      <div class="modal-header">
        <h4 class="modal-title text-white">Status Change Confirmation</h4>
        <button type="button" class="btn-close" data-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>

      <form action="{{url('fees_reminder_status')}}" method="post">
              	 @csrf
      <div class="modal-body">
              <input type=hidden id="id" name=id>
              <input type=hidden id="status_id" name=status_id>
              <h5 class="text-white">Are you sure you want to delete  ?</h5>
      </div>
      <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect remove-data-from-delete-form" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger waves-effect waves-light">Delete</button>
         </div>
       </form>
    </div>
  </div>
</div>

<!-- The Modal -->
<div class="modal" id="Modal_id">
  <div class="modal-dialog">
    <div class="modal-content" style="background: #555b5beb;">

      <div class="modal-header">
        <h4 class="modal-title text-white">Delete Confirmation</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>

      <form action="{{ url('fees_reminder_delete') }}" method="post">
              	 @csrf
      <div class="modal-body">
              <input type=hidden id="delete_id" name=delete_id>
              <h5 class="text-white">Are you sure you want to delete  ?</h5>
      </div>
      <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect remove-data-from-delete-form" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger waves-effect waves-light">Delete</button>
         </div>
       </form>
    </div>
  </div>
</div>
@endsection      