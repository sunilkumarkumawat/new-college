@php
$getCourses = Helper::getCourses();
$getTypeclass = Helper::classType();
$getCountry = Helper::getCountry();
$getgenders = Helper::getgender();
@endphp
@extends('layout.app') 
@section('content')

<input type="hidden" id="session_id" value="{{ Session::get('role_id') ?? '' }}">
<div class="content-wrapper">
   

   <section class="content pt-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-12">    
    <div class="card card-outline card-orange">
             <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fa fa-edit"></i> &nbsp;{{ __('student.Edit Students Enquiry') }} </h3>
            <div class="card-tools">
            <a href="{{url('enquiryView')}}" class="btn btn-primary  btn-sm" ><i class="fa fa-eye"></i> {{ __('View') }} </a>
            <a href="{{url('studentsDashboard')}}" class="btn btn-primary  btn-sm" ><i class="fa fa-arrow-left"></i> {{ __('common.Back') }} </a>
            </div>
            
            </div>         
        <form id="quickForm" action="{{ url('enquiryEdit') }}/{{$data['id']}} ??  '' " method="post" enctype="multipart/form-data">   
         @csrf
    <div class="row m-2">
		<div class="col-md-2">
			<div class="form-group">
				<label style="color:red;">{{ __('student.Registration No') }}*</label>
				<input type="text" class="form-control @error('registration_no') is-invalid @enderror" id="registration_no" name="registration_no" placeholder="{{ __('student.Registration No') }}" readonly value="{{ $data['registration_no'] ?? '' }}">
		        @error('registration_no')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
		</div>
	
		<div class="col-md-2">
	    	<div class="form-group">
				<label style="color:red;">{{ __('common.First Name') }}*</label>
				<input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" placeholder="{{ __('common.First Name') }}" value="{{ $data->first_name ?? old('first_name') }}">
		         @error('first_name')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
		</div>
	
		<div class="col-md-2">
	    	<div class="form-group">
				<label style="color:red;">{{ __('common.Last Name') }}*</label>
				<input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" placeholder="{{ __('common.Last Name') }}" value="{{ $data->last_name ?? old('last_name') }}">
		         @error('last_name')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
		</div>

		 <div class="col-md-2">
                 	<div class="form-group">
				<label style="color:red;">{{ __('student.Student Mobile No.') }}*</label>
				<input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" placeholder="{{ __('student.Student Mobile No.') }}" value="{{ $data->mobile ?? old('mobile') }}" maxlength="10" onkeypress="javascript:return isNumber(event)">
				 @error('mobile')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
            </div>

      	<div class="col-md-2">
	    	<div class="form-group">
				<label>{{ __('common.E-Mail') }}</label>
				<input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="{{ __('common.E-Mail') }}" value="{{ $data->email ?? old('email') }}">
		    </div>
		</div>
	    
	    <div class="col-md-2">
	    	<div class="form-group">
                  <label style="color:red;">{{ __('common.Gender') }}*</label>
                  <select class="form-control @error('gender_id') is-invalid @enderror" id="gender_id" name="gender_id">
    				<option value="">{{ __('common.Select') }}</option>
                    @if(!empty($getgenders)) 
                          @foreach($getgenders as $value)
                             <option value="{{ $value->id }}" {{ $value->id == old('gender_id', $data->gender_id) ? 'selected' : ''}}>{{ $value->name ?? ''  }}</option>
                          @endforeach
                      @endif
                    </select>
                     @error('gender_id')
    					<span class="invalid-feedback" role="alert">
    						<strong>{{ $message }}</strong>
    					</span>
    				@enderror
                </div>
		</div>

	    

		 <div class="col-md-2">
	    	<div class="form-group">
				<label>{{ __('common.DOB') }}</label>
				<input type="date" class="form-control" id="dob" name="dob" placeholder="{{ __('common.DOB') }}" value="{{ $data->dob ?? old('dob') }}">
		    </div>
		  </div>
		  
		  <div class="col-md-2">
	    	<div class="form-group">
				<label style="color:red;">{{ __('common.Fathers Name') }}*</label>
				<input type="text" class="form-control @error('father_name') is-invalid @enderror" id="father_name" name="father_name" placeholder="{{ __('common.Fathers Name') }}" value="{{ $data->father_name ?? old('father_name') }}">
				@error('father_name')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
		</div>	
        <div class="col-md-2">
	    	<div class="form-group">
				<label style="color:red;">{{ __('common.Mothers Name') }}*</label>
				<input type="text" class="form-control @error('mother_name') is-invalid @enderror" id="mother_name" name="mother_name" placeholder="{{ __('common.Mothers Name') }}" value="{{ $data->mother_name ?? old('mother_name') }}">
				 @error('mother_name')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
		</div>
        <div class="col-md-2">
	    	<div class="form-group">
			    <label>{{ __('common.Fathers Contact No') }}</label>
			    <input type="tel" class="form-control" id="father_mobile" name="father_mobile" placeholder="{{ __('common.Fathers Contact No') }}" value="{{ $data->father_mobile ?? old('father_mobile') }}" maxlength="10" minlength="10" onkeypress="javascript:return isNumber(event)">
	        </div>
	    </div>

	    <div class="col-md-2">
			<div class="form-group">
				<label>{{ __('Course') }}</label>
				<select class="select2 form-control" id="course_id" name="course_id">
				   <option value="">{{ __('common.Select') }}</option>
                 @if(!empty($getCourses)) 
                      @foreach($getCourses as $course)
                         <option value="{{ $course->id }}" {{ $course->id == old('course_id', $data->course_id) ? 'selected' : ''}}>{{ $course->name ?? '' }}</option>
                      @endforeach
                  @endif
                </select>
		    </div>
		</div>

	    <div class="col-md-2">
			<div class="form-group">
				<label>{{ __('Class / Semester') }}</label>
				<select class="select2 form-control" id="class_type_id" name="class_type_id">
				   <option value="">{{ __('common.Select') }}</option>
                 @if(!empty($getTypeclass)) 
                      @foreach($getTypeclass as $type)
                         <option value="{{ $type->id ?? ''  }}" {{ $type->id == old('class_type_id', $data->class_type_id) ? 'selected' : ''}}>{{ $type->name ?? ''  }}</option>
                      @endforeach
                  @endif
                </select>
		    </div>
		</div>
	    <div class="col-md-2">
			<div class="form-group">
				<label>Enrollment No</label>
				<input type="text" class="form-control" name="enrollment_no" id="enrollment_no" placeholder="Enrollment No" value="{{ $data->enrollment_no ?? old('enrollment_no') }}">
			</div>
		</div>

	    <div class="col-md-2">
			<div class="form-group">
				<label>Web Registration No</label>
				<input type="text" class="form-control" name="web_registration_no" id="web_registration_no" placeholder="Web Reg. No" value="{{ $data->web_registration_no ?? old('web_registration_no') }}">
			</div>
		</div>

	    <div class="col-md-4">
			<div class="form-group">
				<div class="d-flex align-items-center justify-content-between mb-1">
					<label class="mb-0">Agent / Referrer</label>
					<button type="button" class="btn btn-xs btn-outline-success font-weight-bold px-2 py-0" style="font-size: 10.5px; line-height: 1.3;" data-toggle="modal" data-target="#quickAgentModal" data-bs-toggle="modal" data-bs-target="#quickAgentModal">
						<i class="fa fa-plus-circle"></i> Quick Add Agent
					</button>
				</div>
				<select class="select2 form-control" id="agent_id" name="agent_id">
					<option value="">{{ __('common.Select') }} Agent</option>
					@php
						$activeAgents = \App\Models\Agent::getActiveAgents();
					@endphp
					@if(!empty($activeAgents))
						@foreach($activeAgents as $agent)
							<option value="{{ $agent->id }}" {{ (($data->agent_id ?? old('agent_id')) == $agent->id) ? 'selected' : '' }}>
								{{ $agent->name }} @if(!empty($agent->mobile))({{ $agent->mobile }})@endif
							</option>
						@endforeach
					@endif
				</select>
			</div>
		</div>

	    <div class="col-md-2">
			<div class="form-group">
				<label for="City" >Select ID Proof</label>
				<select class="form-control" id="id_proof" name="id_proof" autocomplete="off">
                    <option value="">Select ID Proof</option>
                    <option value="Aadhar Card" {{ 'Aadhar Card' == old('id_proof', $data->id_proof) ? 'selected' : ''}}>Aadhar Card</option>
                    <option value="Voter ID Card" {{"Voter ID Card" == old('id_proof', $data->id_proof) ? 'selected' : ''}}>Voter ID Card</option>
                    <option value="Driving License" {{"Driving License" == old('id_proof', $data->id_proof) ? 'selected' : ''}}>Driving License</option>
                    <option value="PAN Card" {{"PAN Card" == old('id_proof', $data->id_proof) ? 'selected' : ''}}>PAN Card</option>
                </select>
			</div>
		</div>
		<div class="col-md-2">
			<div class="form-group">
				<label for="id_number">ID Number</label>
				<div class="input-group">
				    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fa fa-credit-card"></i>
                        </span>
                    </div>
			        <input class="form-control" name="id_number" id="id_number" placeholder="ID Number" value="{{ $data->id_number ?? old('id_number') }}">
				</div>
			</div>
		</div>
		

	    <div class="col-md-2">
	    	<div class="form-group">
				<label>{{ __('student.Remark') }}</label>
				<input type="text" class="form-control" id="remark_1" name="remark_1" placeholder="{{ __('student.Remark') }}" value="{{ $data->remark_1 ?? old('remark_1') }}">
			</div>
		</div>
	    
		 <div class="col-md-12">
	    	<div class="form-group">
				<label>{{ __('student.Students Address') }}</label>
				
			
				 <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address"  placeholder="student.Students Address" rows="4" cols="50"  value="">{{ $data->address ?? old('address') }} </textarea>
				@error('address')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
		    </div>
	    </div>
	</div>

    <div class="row m-2">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary " id="submitButton">{{ __('common.Update') }}</button>
        </div>
    </div>
    
    </form>
    </div>
   </div>
   </div>
   </div>
   </section>
</div>  
 
<script>
    $( document ).ready(function() {
    var session_id = $('#session_id').val();
    if(session_id != 1){
        $("#aadhaar").attr('readonly','true');
    }
}); 
</script>
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    // Dynamic Course-dependent Classes / Semesters loader
    $('#course_id').on('change', function() {
        var courseId = $(this).val();
        var classSelect = $('#class_type_id');
        
        if (courseId) {
            $.ajax({
                url: "{{ url('getClassesByCourse') }}",
                type: "GET",
                data: { course_id: courseId },
                dataType: "json",
                success: function(data) {
                    classSelect.empty();
                    classSelect.append('<option value="">{{ __("common.Select") }}</option>');
                    if (data && data.length > 0) {
                        $.each(data, function(key, val) {
                            classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                        });
                    }
                    if (classSelect.hasClass('select2')) {
                        classSelect.trigger('change');
                    }
                }
            });
        }
    });

  })

</script>
@include('agents.quick_add_modal')
@endsection    
    