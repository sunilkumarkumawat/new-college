@php
   $getUser = Helper::getUser();
   $getstudents = Helper::getstudents();
   $getgenders = Helper::getgender();
   $classType = Helper::classType();
   $getCountry = Helper::getCountry();
   $getState = Helper::getState();
   $getCity = Helper::getCity($data['state_id']);
   $teacher = DB::table('users')->where('id',Session::get('id'))->whereNull('deleted_at')->first();
@endphp

@extends('layout.app') 
@section('content')

<div class="content-wrapper">

   <section class="content pt-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card card-outline card-orange">

                <div class="card-header bg-primary">
                    <h3 class="card-title"><i class="fa fa-user-circle-o"></i> &nbsp; View Profile</h3>
                    <div class="card-tools">
                      <a href="{{url('dashboard')}}" class="btn btn-primary  btn-xs"><i class="fa fa-arrow-left"></i> <span class="">{{ __('Back') }} </span></a>
                    </div>
                </div> 
              <div class="card-body box-profile">
                  <form id="quickForm" action="{{ url('profile/edit') }}/{{Session::get('id') ?? '' }}" method="post" enctype="multipart/form-data">
                     @csrf
                  <div class="row mb-3">
                        <div class="col-md-12 text-center">
                            @if(Session::get('role_id')==3)  
                                <img class="profile-user-img img-fluid rounded-circle" src="{{ env('IMAGE_SHOW_PATH').'/profile/'.$getUser['image'] }}" style="width:100px; height:100px;" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'">
                            @else
                                <img class="profile-user-img img-fluid rounded-circle" src="{{ env('IMAGE_SHOW_PATH').'/profile/'.$getUser['image'] }}" style="width:100px; height:100px;" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'/default/user_image.jpg' }}'">
                            @endif                            
                        </div>
                    </div>
                <div class="container rounded bg-white">
                    <div class="row">
                            <div class="col-md-3">
    	    	                <div class="form-group">
    				                <label>Profile Photo</label>
    				                @if(!empty($teacher))
    				                    <input type="file" {{$teacher->teacher_update == 1 ? 'disabled' : ''}} class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" value="{{ $data['photo'] ?? ""  }}">
    				                @else
    				                    <input type="file"  class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" value="{{ $data['photo'] ?? ""  }}">
    		                        @endif
    		                        @error('photo')
            		                <span class="invalid-feedback" role="alert">
            			            <strong>{{ $message }}</strong>
            		                </span>
            			            @enderror
    		                    </div>
    		                </div>
		                
                      	   <div class="col-md-3">
                            <lable>Father Photo</lable>
                            <div class="input file form-control @error('father_img') is-invalid @enderror">
                                <input type="file"  name="father_img" id="father_img" value="{{ $data['father_img'] ?? '' }}">
                                @error('father_img')
                					<span class="invalid-feedback" role="alert">
                						<strong>{{ $message }}</strong>
                					</span>
                				@enderror
                            </div>  
                        </div>
                
                    	<div class="col-md-3">
                            <lable>Mother Photo</lable>
                            <div class="input file form-control @error('mother_img') is-invalid @enderror">
                                <input type="file"  name="mother_img" id="mother_img" value="{{ $data['mother_img'] ?? '' }}">
                                @error('mother_img')
                					<span class="invalid-feedback" role="alert">
                						<strong>{{ $message }}</strong>
                					</span>
                				@enderror
                            </div>  
                        </div>
                    
		                 <div class="col-md-3">
            				<div class="form-group"> 
            					<label>User Name</label>
            					<input type="text" class="form-control "  value="{{ $data['userName'] ?? ""  }}" placeholder="User Name" readonly>
            				</div>
            			</div>
            			
                        <div class="form-group col-md-3">
                            <label style="color:red;"> Name*</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" id="first_name" value="{{ $data['first_name'] ?? ""  }}" placeholder="First name">
                            @error('first_name')
    		                <span class="invalid-feedback" role="alert">
    			                <strong>{{ $message }}</strong>
    		                </span>
    			            @enderror
			            </div>
			            
			        
			            
		                    
                		 
                		
                		 <div class="col-md-3">
                            <div class="form-group">
                              <label style="color:red;">Gender*</label>
                              <select class="form-control @error('gender_id') is-invalid @enderror" id="gender_id" name="gender_id" >
                				<option value="">Select</option>
                                @if(!empty($getgenders)) 
                                      @foreach($getgenders as $value)
                                         <option value="{{ $value->id}} " {{ ( $value->id == $data['gender_id'] ? 'selected' : old('gender_id') ) }}>{{ $value->name ?? ''  }}</option>
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
                            
                      
		
                	   
	              
		
	            		<div class="col-md-3">
            				<div class="form-group"> 
            					<label style="color:red;">Date Of Birth*</label>
            					<input type="date"class="form-control @error('dob') is-invalid @enderror" id="dob" name="dob" value="{{ $data['dob'] ?? old('dob')  }}" placeholder="Date Of Birth" >
            					@error('dob')
            						<span class="invalid-feedback" role="alert">
            							<strong>{{ $message }}</strong>
            						</span>
            					@enderror
                            </div>
            			</div>
            			
		               <div class="form-group col-md-3">
                            <label style="color:red">Mobile*</label>
                                <input type="text" class="form-control @error('mobile') is-invalid @enderror " name="mobile" id="mobile" value="{{ $data['mobile'] ?? old('mobile')  }}" placeholder="Mobile" maxlength="10" onkeypress="javascript:return isNumber(event)" >
                                @error('mobile')
        		                <span class="invalid-feedback" role="alert">
        			            <strong>{{ $message }}</strong>
        		                </span>
        			            @enderror
			            </div>
			            
			            <div class="form-group col-md-3">
                            <label style="color:red;">Email*</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror " name="email" id="email" value="{{ $data['email'] ?? old('email')  }}" placeholder="Email" >
                                @error('email')
        		                <span class="invalid-feedback" role="alert">
        			            <strong>{{ $message }}</strong>
        		                </span>
        			            @enderror
			            </div>
			            
			            
			            <div class="form-group col-md-3">
                            <label style="color:red;">Father Name*</label>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror " name="father_name" id="father_name" value="{{ $data['father_name'] ?? old('father_name')  }}" placeholder="Father Name" >
                            @error('father_name')
    		                <span class="invalid-feedback" role="alert">
    			            <strong>{{ $message }}</strong>
    		                </span>
    			            @enderror
			            </div>
			            
			          
			            
			            <div class="form-group col-md-3">
                            <label style="color:red;">Mother name*</label>
                            <input type="text" class="form-control @error('mother_name') is-invalid @enderror " name="mother_name" id="mother_name" value="{{ $data['mother_name'] ?? old('mother_name')  }}" placeholder="Mother Name" >
                            @error('mother_name')
    		                <span class="invalid-feedback" role="alert">
    			            <strong>{{ $message }}</strong>
    		                </span>
    			            @enderror
			            </div>
			       
			            
		            	 <div class="col-md-3">
                	    	<div class="form-group">
                				<label style="" >Father's Contact No.</label>
                				<input type="text" class="form-control @error('father_mobile') is-invalid @enderror" id="father_mobile" name="father_mobile" placeholder=" Father's Contact No." value="{{ $data['father_mobile'] ?? old('father_mobile') }}" maxlength="10" onkeypress="javascript:return isNumber(event)" >
                				 @error('father_mobile')
            		                <span class="invalid-feedback" role="alert">
            			            <strong>{{ $message }}</strong>
            		                </span>
            			            @enderror
                		    </div>
                		  </div>
                    		  
                    	<div class="col-md-3">
                	    	<div class="form-group">
                				<label style=""> Aadhaar No.</label>
                				<input type="text" class="form-control @error('aadhaar') is-invalid @enderror" id="aadhaar" name="aadhaar" placeholder=" Aadhaar no." value="{{ $data['id_number'] ?? '' }}" maxlength="12" onkeypress="javascript:return isNumber(event)" >
                				 @error('aadhaar')
                					<span class="invalid-feedback" role="alert">
                						<strong>{{ $message }}</strong>
                					</span>
                				@enderror
                		    </div>
                		</div>		  
            		  
            		    
            			<div class="col-md-3" >
                            <div class="form-group">
                                <label style="color:red;" >Country*</label>
                                <select class="form-control select2 @error('father_mobile') is-invalid @enderror" name="country_id" id="country_id"  >
                                    @if(!empty($getCountry)) 
                                      @foreach($getCountry as $country)
                                         <option value="{{ $country->id ?? ''  }}" {{ ( $country['id'] == $data['country_id']) ? 'selected' : old('country_id') }}>{{ $country->name ?? ''  }}</option>
                                      @endforeach
                                    @endif
                                    
                              
                                	@error('country_id')
                						<span class="invalid-feedback" role="alert">
                							<strong>{{ $message }}</strong>
                						</span>
                					@enderror
                                </select>
                            </div>
                        </div>
                        
            			<div class="col-md-3">
            				<div class="form-group"> 
            					<label for="State" class="required" style="color:red;">State*</label>
            					<select class="form-control select2 @error('state_id') is-invalid @enderror" id="state_id" name="state_id" >
                                    @if(!empty($getState)) 
                                    <option value=""> Select </option>
                                        @foreach($getState as $state)
                                            <option value="{{ $state->id ?? ''}}" {{ ( $state['id'] == $data['state_id']) ? 'selected' : '' }}>{{ $state->name ?? old('state_id')}}</option>
                                        @endforeach
                                    @endif
                                    
                                  	
                                </select>
                                @error('state_id')
                						<span class="invalid-feedback" role="alert">
                							<strong>{{ $message }}</strong>
                						</span>
                					@enderror
            				</div>
            			</div>
            			
            			<div class="col-md-3">
            			    <div class="form-group">
            			        <label for="City" style="color:red;">City*</label>
            			        <select class="form-control select2 @error('city_id') is-invalid @enderror" name="city_id" id="city_id"  >
            			            @if(!empty($getCity)) 
                                    <option value=""> Select </option>
                                  @foreach($getCity as $cities)
                                     <option value="{{ $cities->id ?? ''  }}" {{ ( $cities['id'] == $data['city_id']) ? 'selected' : '' }}>{{ $cities->name ?? old('city_id')  }}</option>
                                  @endforeach
                              @endif
            					
            					
            					</select>
                                @error('city_id')
            						<span class="invalid-feedback" role="alert">
            							<strong>{{ $message }}</strong>
            						</span>
            					@enderror
            			    </div>
            			</div>
            			
            			<div class="col-md-3">
            				<div class="form-group"> 
            					<label style="">Address</label>
            					<input type="text"class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ $data['address'] ?? old('address')  }}" placeholder="Address" >
            					@error('address')
            						<span class="invalid-feedback" role="alert">
            							<strong>{{ $message }}</strong>
            						</span>
            					@enderror
                            </div>
            			</div>
            			
            			<div class="col-md-3">
            				<div class="form-group"> 
            					<label style="">Pin Code</label>
            					<input type="text"class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" value="{{ $data['pincode'] ?? old('pincode')  }}" placeholder="Pin Code" >
            					@error('pincode')
            						<span class="invalid-feedback" role="alert">
            							<strong>{{ $message }}</strong>
            						</span>
            					@enderror
                            </div>
            			</div>
                    </div>
                </div>
                            
               
                    <div class="col-md-12 text-center">
    		           <button type="submit" class="btn btn-primary btn-sm">Update</button>
    		        </div>
    		       
            </form>
        </div>
     </div>
</div>
</div>
</div>
</section>
</div>

@endsection