@php
$setting = DB::table('settings')->where('branch_id', Session::get('branch_id') ?: 1)->first() ?: DB::table('settings')->first();
@endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
        <title>{{ $setting->name ?? '' }}</title>
        <link rel="icon" type="image/x-icon" href="{{ env('IMAGE_SHOW_PATH').'setting/left_logo/'.$setting->left_logo }}" onerror="this.src='{{ env('IMAGE_SHOW_PATH').'default/mini_logo.png' }}'">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/adminlte.min.css') }}">    
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/tempusdominus-bootstrap-4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/icheck-bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/mobilescreen.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/OverlayScrollbars.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/daterangepicker.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/common.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">     
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('public/assets/school/css/select2-bootstrap4.min.css') }}">
        <script src="{{URL::asset('public/assets/school/js/jquery.min.js')}}"></script>
        <script src="{{URL::asset('public/assets/school/js/form/submit-form.js')}}"></script>
    </head>
    <style>
        .centered_flex{
            display:flex;
            margin-bottom:10px;
        }
        
        .centered_flex i{
            font-size: 34px;
        }
        
        .centered_flex p{
           margin-bottom: 0px; 
           font-size: 20px;
           margin-left:10px;
           font-weight:600;
        }
        
        .error_message_whatsapp{
            font-weight: 400;
            text-transform: capitalize;
            line-height: 20px;
            margin-bottom: 20px;
        }
        
        .modal_btn{
            border: none;
            padding: 5px 20px;
            color: black;
            font-weight: 600;
        }
        
        .whatsapp_note{
            margin-bottom: 0px;
            font-size: 10px;
            text-transform: uppercase;
        }
        
    </style>
    
    
    @php
    $cur_route = Route::getFacadeRoot()->current() ? Route::getFacadeRoot()->current()->uri() : '';
    @endphp
    <body class="sidebar-mini layout-fixed layout-navbar-fixed sidebar-no-expand">
        <script>
            (function() {
                var sidebarState = localStorage.getItem('sidebar_state');
                if (sidebarState === 'collapsed') {
                    document.body.classList.add('sidebar-collapse');
                } else if (sidebarState === 'expanded') {
                    document.body.classList.remove('sidebar-collapse');
                }
            })();
        </script>
        <div class="wrapper">
            @include('layout.header')
            @if(Session()->get('role_id') ==3)
                @include('layout.student_sidebar')
            @else
                @include('layout.sidebar')
            @endif
            @include('layout.message')
            @yield('content')
            @include('layout.footer')
            
            <script src="{{URL::asset('public/assets/school/js/jquery-ui.min.js')}}"></script>
            <script>
                $.widget.bridge('uibutton', $.ui.button)
            </script>
            <script src="{{URL::asset('public/assets/school/js/bootstrap.bundle.min.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/moment.min.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/daterangepicker.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/tempusdominus-bootstrap-4.min.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/jquery.overlayScrollbars.min.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/bootstrap5.js')}}"></script>   
            <script src="{{URL::asset('public/assets/school/js/jquery.validate.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/additional-methods.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/adminlte.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/toastr.min.js')}}"></script>
            <script src="{{URL::asset('public/assets/school/js/select2.full.min.js')}}"></script>
    
        <script type="text/javascript">
            function isNumber(evt){
                 var charCode = (evt.which) ? evt.which : event.keyCode
                 if (charCode > 31 && (charCode < 48 || charCode > 57))
                    return false;
            
                 return true;
            }
                  
            $(function () {
                $('.select2').select2()
                
                $('.select2bs4').select2({
                  theme: 'bootstrap4'
                })
            
            })
            
            var calendarElement = $('<div>', {});
            calendarElement.load('{{ url("calendarElement") }}', function() {
                $('#calendarElement').append(calendarElement);
            });
        </script>
                
        <script>
        $('#country_id').on('change', function(e){
                var baseurl = "{{ url('/') }}";
            	var country_id = $(this).val();
                $.ajax({
                     headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
            	  url: baseurl+'/countryData/'+country_id,
            	  success: function(data){
            			$("#state_id").html(data);
            	  }
            	});
            	
            });
        $('#state_id').on('change', function(e){
                var baseurl = "{{ url('/') }}";
            	var state_id = $(this).val();
                $.ajax({
                     headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
            	  url: baseurl+'/stateData/'+state_id,
            	  success: function(data){
            			$("#city_id").html(data);
            	  }
            	});
            	
            });
            
        $('#class_type_id').on('change', function(e){
            var baseurl = "{{ url('/') }}";
        	var class_type_id = $(this).val();
            $.ajax({
                headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
        	    url: baseurl + '/subjectGetData/' + class_type_id,
        	    success: function(data){
        			$("#subject_id").html(data);
        	    }
        	});
        });            
        </script>
    
       
        
        <script>
        
        var timer2 = "5:01";
var interval = setInterval(function() {


  var timer = timer2.split(':');
  //by parsing integer, I avoid all extra string processing
  var minutes = parseInt(timer[0], 10);
  var seconds = parseInt(timer[1], 10);
  --seconds;
  minutes = (seconds < 0) ? --minutes : minutes;
  if (minutes < 0) 
  {
  clearInterval(interval);
   location.reload();
  }
  
  
  seconds = (seconds < 0) ? 59 : seconds;
  seconds = (seconds < 10) ? '0' + seconds : seconds;
  //minutes = (minutes < 10) ?  minutes : minutes;
  

  $('.countdown').html(minutes + ':' + seconds);
  timer2 = minutes + ':' + seconds;
    //console.log(minutes + ':' + seconds);
}, 1000);

    $(document).on( "mousemove keypress", function () {
 timer2 = "5:01";
 
 
});


</script>

<script>
    $(document).ready(function(){
        var whatsapp_error = "{{ Session::get('whatsapp_error') }}";
        
        
        
        if(whatsapp_error != ""){
            $('#whatsapp_error_modal').modal('show');
        }
        
        $('.change_status').click(function(){
            var action = $(this).data('action');
            var whatsapp_error_respose_id = $('#whatsapp_error_respose_id').val();
            
            if(whatsapp_error_respose_id != ""){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'post',
                    url: '/whatsapp_api_response',
                    data: {
                        action: action,
                        whatsapp_error_respose_id: whatsapp_error_respose_id
                    },
                    success: function(data) {
                    //   alert(JSON.stringify(data));
                    }
                });
            }
        });

        // var messageCronStatus = "{{ Session::get('messageCronStatus') }}";
        // toastr.info(messageCronStatus);
        // if (messageCronStatus !== 'working') {
        //    // alert(messageCronStatus);
        //     fetch("{{ url('cronJobs') }}", {
        //         method: "GET"
        //     }).then(response => console.log("Cron job triggered"));
        // }
        
    });

// Synchronize and persist sidebar toggle state across all page redirects
$(document).ready(function() {
    $(document).on('collapsed.lte.pushmenu', function() {
        localStorage.setItem('sidebar_state', 'collapsed');
    });
    $(document).on('shown.lte.pushmenu', function() {
        localStorage.setItem('sidebar_state', 'expanded');
    });

    $(document).on('click', '[data-widget="pushmenu"]', function() {
        setTimeout(function() {
            if ($('body').hasClass('sidebar-collapse')) {
                localStorage.setItem('sidebar_state', 'collapsed');
            } else {
                localStorage.setItem('sidebar_state', 'expanded');
            }
        }, 150);
    });
});
</script>

<script>
$(document).ready(function() {
    var baseUrl = "{{ url('/') }}";
    
    $(document).on('dblclick', '.editable', function() { // Changed here
        var currentTd = $(this);
        var currentValue = $(this).text().trim();
        var field = $(this).attr('data-field');
        var modal = $(this).attr('data-modal');
        var id = $(this).attr('data-id');
       
        var inputField = $(`<input type="text" name="${field}" data-id="${id}" data-modal="${modal}">`).val(currentValue);
     
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
                     url: baseUrl + '/updateSingleField',  
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



<div class="modal fade" id="whatsapp_error_modal">
<div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
    <div class="modal-body">
        <div class="col-md-12">
            <div class="centered_flex">
                <i class="fa fa-exclamation-triangle text-warning"></i>
                <p>WhatsApp API Message Sending Failure</p>
            </div>
        </div>
        
        <div class="col-md-12">
            <p id="whatsapp_error_message" class="error_message_whatsapp">{{ Session::get('whatsapp_error') ?? ''}}</p>
            <input type="hidden" id="whatsapp_error_respose_id" value="{{  Session::get('whatsapp_error_respose_id') ?? ''}}">
        </div>
        
        <div class="col-md-12 text-right">
            <button class="modal_btn bg-white change_status" data-action="Discard" data-bs-dismiss="modal">Discard</button>
            <button class="modal_btn bg-warning change_status" data-action="Confirm" data-bs-dismiss="modal">Confirm</button>
        </div>
        
        <hr>
        
        <div class="col-md-12">
            <p class="whatsapp_note">
                Please Contact your Service Provider.
            </p>
        </div>
    </div>
  </div>
</div>
</div>

    </body>
</html>


@include('initial.initialView')
