
@php

$setting = DB::table('settings')->whereNull('deleted_at')->first();

$branch = Helper::getAllBranch();

@endphp
@extends('layout.app')
@section('content')

<style>
    .fixed_item{
        position:sticky !important;
        right:-8px;
        background-color:white;
        z-index:111;
        box-shadow: -6px 2px 6px #cecece;
    }
    
    .dropdown-menu.show {
        left: -79px !important;
    }
    
    .flex_centered{
        display:flex;
        align-items:center;
        /*justify-content: space-between;*/
        height: 55px;
    }
    
    .flex_centered a{
        margin-left:10px;
    }
    
    .nowrap{
        white-space:nowrap;
        font-size:14px;
    }
    
    .colored_table thead tr{
        background-color:#002c54;
        color:white;
    }
    .colored_table thead tr th{
        padding:10px;
    }
    
    .overflow_scroll{
        height:250px;
        overflow:scroll;
    }
</style>

<div class="content-wrapper">
  <section class="content pt-3">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12 col-md-12">
          <div class="card card-outline card-orange" style="border-top: 3px solid #ff8a00 !important; border: 1px solid #b8c8d5;">
            <div class="card-header" style="background: #002c54 !important; color: #ffffff !important; min-height: 48px; padding: 8px 14px; display: flex; align-items: center; justify-content: space-between;">
              <h3 class="card-title" style="margin: 0; font-size: 15.5px; font-weight: 700; color: #ffffff !important; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-id-card-o"></i> Student ID Cards Generator & Print
              </h3>
              <div class="card-tools d-flex gap-2">
                <a href="{{ env('IMAGE_SHOW_PATH') . 'Id Card Format.xlsx' }}" class="btn btn-light btn-sm" download style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border: 1px solid #cbd5e1;">
                  <i class="fa fa-download text-success mr-1"></i><span class="Display_none_mobile"> Download Blank Excel </span>
                </a>
                <a href="{{url('studentsDashboard')}}" class="btn btn-light btn-sm" style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border: 1px solid #cbd5e1;">
                  <i class="fa fa-arrow-left mr-1"></i><span class="Display_none_mobile"> {{ __('common.Back') }} </span>
                </a>
              </div>
            </div>
            
               <div class="row m-2">
            <div class="col-12 col-md-6">
                   <form action="{{ url('customIdCardExcelUpload') }}" method="post" enctype='multipart/form-data'>
              @csrf
                   <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="color:red;">{{ __('user.Branch ID') }} *</label>
                                <select class="form-control @error('branch_id') is-invalid @enderror select2" name="branch_id" name="branch_id" required>
                                    <option value="">{{ __('common.Select') }}</option>
                                @if(!empty($branch)) 
                                          @foreach($branch as $Branch)
                                             <option value="{{ $Branch->id ?? ''  }}">{{ $Branch->branch_name ?? ''  }}</option>
                                          @endforeach
                                @endif                                    
                                </select>
                                @error('branch_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                   <div class="col-8 col-md-6">    
    <div class="form-group">
                    <label>{{ __('Upload Users ') }}<span class='text-danger'> [By Excel]</span></label>
                    <input type="file" class="form-control" name="excel" />
                  </div>
        </div>
        <div class="col-4 col-md-2">
                  <label class="text-white">{{ __('Update') }}</label>
                  <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                </div>
                </div>
                </form>
                </div>
   
                
            <div class="col-md-6">
                <form action="{{ url('customIdCatdImageUpload') }}" method="post" enctype='multipart/form-data'>
                  @csrf
                   <div class="row">
                        <div class="col-md-8">    
                            <div class="form-group">
                                <label>Choose Images<span class='text-danger'>*</span></label>
                                <input type="file" class="form-control" name="image[]" multiple required/>
                            </div>
                        </div>
                        
                    
                        
                        <div class="col-md-2">
                            <label class="text-white">{{ __('Update') }}</label>
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>
                    </div>
                </form>                
            </div>


  
        </div>
        
        <hr class="m-0 ml-2 mr-2 border-secondary">
       
 
        
        <hr class="m-0 ml-2 mr-2">
       <div class="row">
            <div class="col-md-6">
        <form action="{{ url('customIdCard') }}" method="post" enctype='multipart/form-data'>
              @csrf
            <div class="row m-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="">{{ __('user.Branch ID') }} </label>
                        <select class="form-control @error('branch_id') is-invalid @enderror select2" name="branch_id" name="branch_id" >
                            <option value="">All Branch</option>
                        @if(!empty($branch)) 
                                    @foreach($branch as $Branch)
                                        <option value="{{ $Branch->id ?? ''  }}" {{ $Branch->id == $search['branch_id'] ? 'selected' : '' }}>{{ $Branch->branch_name ?? ''  }}</option>
                                    @endforeach
                        @endif                                    
                        </select>
                        @error('branch_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="">Designation</label>
                        <select class="form-control  select2" name="designation" name="designation" >
                            <option value="">All </option>
                            <option value="Student" {{ ("Student" == $search['designation']) ? 'selected' : '' }}>Student </option>
                            <option value="Staff" {{ ("Staff" == $search['designation']) ? 'selected' : '' }}>Staff </option>
                            <option value="Intern" {{ ("Intern" == $search['designation']) ? 'selected' : '' }}>Intern </option>
                        </select>
                    </div>
                </div>
                  
                <div class="col-md-1">
                  <label class="text-white mt-n3">{{ __('') }}</label>
                  <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </div>
        </form>
        </div>
        <div class="col-md-6">
            <form action="{{ url('visitorPass') }}" method="post" enctype='multipart/form-data' target="_blank">
              @csrf
            <div class="row m-2" style="    border: 1px solid #5d5d5d;">
               
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="">{{ __('Visitor Card Count') }} </label>
                        <input type="number" class="form-control" name="Visitor_pass" id="Visitor_pass"  min="1"    >
                    </div>
                </div>
                  
                <div class="col-md-5">
                  <label class="text-white mt-5">{{ __('') }}</label>
                  <button type="submit" class="btn btn-success">{{ __('Generate Ids') }}</button>
                </div>
            </div>
        </form>
        </div>
             
       </div>
        <form id="customIdPrintForm" action="{{url('customIdPrintMultiple')}}" target="_blank" method="post" >
            @csrf
            <div class="row m-2">
              <div class="col-12" style="overflow-x:scroll;">
                <small class="text-danger">*For update a field just double click the value and it will be editable.</small>
                <table id="studentList" class="table table-bordered table-striped dataTable dtr-inline nowrap">
                  <thead id='main_thead' style="background: #002c54 !important; color: #ffffff !important;">
                    <tr role="row">
                      <th style="background: #002c54 !important; color: #ffffff !important;"><input class="" type="checkbox" id="view1"> {{ __('common.SR.NO') }}</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Image</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Name</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Designation</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Department</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Branch</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Roll No.</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Employee Code</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Blood Group</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Mobile</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">D.O.B.</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">D.O.J.</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Valid Upto</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Hostler</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Internship</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Internship Start Date</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Internship End Date</th>
                      <th style="background: #002c54 !important; color: #ffffff !important;">Address</th>
                    </tr>
                  </thead>
                  <tbody id="">

                    @if(!empty($data))
                        @php
                            $i=1;
                        @endphp
                    @foreach ($data as $item)
                   
                  <tr id="tr_{{$item->id ?? ''}}">
                    <td><input type="checkbox"  data-value="view" name="checkbox[]" class="viewcheck" value="{{$item->id ?? ''}}"> {{ $i++ }}</td>
                    <td class="text-center">
                            <img width='50px'height='50px' style='border-radius:3px;padding:1px; ' class="profileImg pointer deleteImage" src="{{ env('IMAGE_SHOW_PATH').'customIdCard/'.$item['image'] }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/user_image.jpg' }}'" >
                        </td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='first_name' data-modal='CustomIdCard'>{{ $item->first_name ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='designation' data-modal='CustomIdCard'>{{ $item->designation ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='department' data-modal='CustomIdCard'>{{ $item->department ?? '' }}</td>
                    <td class="" data-id="{{ $item->id ?? '' }}" data-field='branch_name' data-modal='CustomIdCard'>{{ $item->branch_name ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='roll_no' data-modal='CustomIdCard'>{{ $item->roll_no ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='employee_code' data-modal='CustomIdCard'>{{ $item->employee_code ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='blood_group' data-modal='CustomIdCard'>{{ $item->blood_group ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='mobile' data-modal='CustomIdCard'>{{ $item->mobile ?? '' }}</td>
                    
                    
                    
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='dob' data-modal='CustomIdCard' data-type='date'>{{ ($item->dob) ? date('d-m-Y', strtotime($item->dob)) : '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='date_of_joining' data-modal='CustomIdCard' data-type='date'>{{ ($item->date_of_joining) ? date('d-m-Y', strtotime($item->date_of_joining)) : '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='valid_upto' data-modal='CustomIdCard' data-type='date'>{{ ($item->valid_upto) ? date('d-m-Y', strtotime($item->valid_upto)) : '' }}</td>
                    
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='hostler' data-modal='CustomIdCard'>{{ $item->hostler ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='internship' data-modal='CustomIdCard'>{{ $item->internship ?? '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='internship_start_date' data-modal='CustomIdCard' data-type='date'>{{ ($item->internship_start_date) ? date('d-m-Y', strtotime($item->internship_start_date)) : '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='internship_completion_date' data-modal='CustomIdCard' data-type='date'>{{ ($item->internship_completion_date) ? date('d-m-Y', strtotime($item->internship_completion_date)) : '' }}</td>
                    <td class="editableFiels pointer" data-id="{{ $item->id ?? '' }}" data-field='address' data-modal='CustomIdCard'>{{ $item->address ?? '' }}</td>
                    <!-- <td class="fixed_item"> 
                        <div class="flex_centered">
                            
                            <a href="{{url('customIdPrint',$item->id)}}" target="blank">
                                <button class="btn btn-success btn-xs" title="Admission ID"><i class="fa fa-credit-card"></i></button>
                            </a>
                        </div>
                    
                      </td>     -->

                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
            @if(!empty($data))
                <div class="col-6 text-center mb-3 mt-2">
                    <button class="btn btn-success" target="_blank">{{ __('student.Generate Ids') }}</button>
                </div>
                <div class="col-6 text-center mb-3 mt-2">
                    <button type="button" id="deleteBtn" class="btn btn-danger" target="_blank">{{ __('Delete') }}</button>
                </div>
            @endif
            </div>
        </form>
            </div>
            </div>
            </div>
            </div>
            </section>
</div>

<style>
    .viewcheck, #view1{
        width: 20px;height: 20px;
        cursor: pointer;
    }
</style>

<script>
$(document).ready(function() {

    $("#view1").click(function(){
        if ($(this).is(':checked')) {
            $(".viewcheck").attr('checked', false);
            $(".viewcheck").attr('checked', true);
        }else{
            $(".viewcheck").attr('checked', false);
        }
    }); 

    var baseUrl = "{{ url('/') }}";
    
    $('#deleteBtn').click(function(){
        var formData = new FormData(document.getElementById("customIdPrintForm"));
        var btn = $("#deleteBtn");
        $.ajax({
            url: "{{ url('deleteCustomIdCard') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function () {
                btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> ' + btn.text());
                $(".error, .alert").remove(); 
                $(".is-invalid").removeClass('is-invalid');
            },
            success: function (response) {
                if(response.status === true){
                    toastr.success('Selected Ids Deleted Successfully');
                    response.id.forEach(function (id) {
                        $('#tr_' + id).addClass('d-none');
                    });
                }
                btn.prop("disabled", false).text(btn.text());
            },
            error: function (xhr) {
                console.log("Error Triggered:", xhr);
                var errorMessage = xhr.responseJSON?.message || "An unexpected error occurred.";
                toastr.error(errorMessage);
                $(".alert").hide().fadeIn();
                btn.prop("disabled", false).text(btn.text());
            },
        });
    });


    $(document).on('dblclick', '.editableFiels', function() { // Changed here
        var currentTd = $(this);
        var currentValue = $(this).text().trim();
        var field = $(this).attr('data-field');
        var modal = $(this).attr('data-modal');
        var id = $(this).attr('data-id');
        var type = $(this).attr('data-type');
       
        if(type == 'date'){
            var inputField = $(`<input type="date" name="${field}" data-id="${id}" data-modal="${modal}" >`).val(currentValue);
        }else{
            var inputField = $(`<input type="text" name="${field}" data-id="${id}" data-modal="${modal}" style="width:100%">`).val(currentValue);
        }
        
     
        $(this).empty().append(inputField);
        
        inputField.focus();

        inputField.blur(function() {
            var newValue = $(this).val().trim();
            $(this).parent().text(currentValue);
        });

        inputField.focusout(function(event) {
            var input = $(this);
            var inputData = $(this).val();

            if(inputData != '') {
                var inputField = $(this).attr('name');
                var inputModal = $(this).attr('data-modal'); 
                var inputId = $(this).attr('data-id');
                
                $.ajax({ 
                     headers: { 
                         'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                     },
                     url: baseUrl + '/updateSingleFieldCustomIdCard',  
                     type: 'POST',  
                     contentType: 'application/json',
                     data: JSON.stringify({ name: inputField, value: inputData, id: inputId, modal: inputModal }), 
                     success: function(response) {
                         if(response.status) {
                             currentTd.text(inputData);
                             toastr.success(response.message);
                         } else {
                             input.blur();
                             toastr.error(response.message);
                         }
                     },
                     error: function(xhr, status, error) {
                         console.error('Error saving data:', error);
                     }
                 });
            } else {
                input.blur();
                currentTd.text(currentValue);
                toastr.error('Nullable field not be allowed');
            }
        });
    });
});

</script>


@endsection