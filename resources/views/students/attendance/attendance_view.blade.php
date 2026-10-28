@extends('layout.app') 
@section('content')
@php
$attendanceType = Helper::attendanceType();
  $classType = Helper::classType();
  $getPermission = Helper::getPermission();
  $courses = Helper::getCourses();
@endphp
<link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">


 <div class="content-wrapper">

   <section class="content pt-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12 col-md-12">
            <div class="card card-outline card-orange" style="border-top: 3px solid #ff8a00 !important; border: 1px solid #b8c8d5;">
              <div class="card-header" style="background: #002c54 !important; color: #ffffff !important; min-height: 48px; padding: 8px 14px; display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title" style="margin: 0; font-size: 15.5px; font-weight: 700; color: #ffffff !important; display: flex; align-items: center; gap: 8px;">
                  <i class="fa fa-calendar-minus-o"></i> {{ __('student.View Students Attendance') }}
                </h3>
                <div class="card-tools d-flex gap-2">
                    @if(Session::get('role_id') !== 3)
                        <a href="{{url('studentsAttendanceAdd')}}" class="btn btn-light btn-sm {{($getPermission->add == 1) ? '' : 'd-none'}}" style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border: 1px solid #cbd5e1;"><i class="fa fa-plus text-primary mr-1"></i>{{ __('common.Add') }}</a>
                        <a href="{{url('studentsDashboard')}}" class="btn btn-light btn-sm" style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border: 1px solid #cbd5e1;"><i class="fa fa-arrow-left mr-1"></i>{{ __('common.Back') }}</a>
                    @endif
                </div>
              </div>
                    
                @if(count($classType) > 0)
                    <div class="row m-2">
                        @if(Session::get('role_id') == 1)
                            <div class="col-md-2 col-4">
                              <div class="form-group">
                                <label for="State" class="required">{{ __('student.Admission No.') }}</label>
                                 <input type="text" class="form-control" id="admissionNo" name="admissionNo" placeholder="{{ __('student.Admission No.') }}" value="{{ $search['admissionNo'] ?? '' }}">
                              </div>
                            </div>
                        @endif
                        <div class="col-md-2 col-4">
                			<div class="form-group">
                				<label>{{ __('common.Month') }} </label>
                				<select class="form-control" id='date__' name="date">
                                    <option value=''>--{{ __('student.Select Month') }}--</option>
                                    <option {{$search['date'] == 1 ? "selected" : "" }} value='01'>Janaury</option>
                                    <option {{$search['date'] == 2 ? "selected" : "" }} value='02'>February</option>
                                    <option {{$search['date'] == 3 ? "selected" : "" }} value='03'>March</option>
                                    <option {{$search['date'] == 4 ? "selected" : "" }} value='04'>April</option>
                                    <option {{$search['date'] == 5 ? "selected" : "" }} value='05'>May</option>
                                    <option {{$search['date'] == 6 ? "selected" : "" }} value='06'>June</option>
                                    <option {{$search['date'] == 7 ? "selected" : "" }} value='07'>July</option>
                                    <option {{$search['date'] == 8 ? "selected" : "" }} value='08'>August</option>
                                    <option {{$search['date'] == 9 ? "selected" : "" }} value='09'>September</option>
                                    <option {{$search['date'] == 10 ? "selected" : "" }} value='10'>October</option>
                                    <option {{$search['date'] == 11 ? "selected" : "" }} value='11'>November</option>
                                    <option {{$search['date'] == 12 ? "selected" : "" }} value='12'>December</option>
                                    </select> 
                		    </div>
            		    </div>       
            		    @if(Session::get('role_id') == 3)
                            <input type='hidden' class="form-control" id="class_type_id" name="class_type_id"  value="{{Session::get('class_type_id')}}"/>
                    	@else
                    	<div class="col-md-2 col-4">
                			<div class="form-group">
                				<label>{{ __('Course') }}</label>
                				<select class="form-control select2" id="course" name="course">
                                    <option value="">{{ __('common.Select') }}</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $course)
                                            <option value="{{ $course->name ?? '' }}" {{ ($course->name == ($search['course'] ?? '')) ? 'selected' : '' }}>{{ $course->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                		    </div>
            		    </div>
                       <div class="col-md-2 col-4">
                    		<div class="form-group">
                    			<label>{{ __('Class / Semester') }}</label>
                    			<select class="form-control select2" id="class_type_id" name="class_type_id" >
                    			  
                    			    
                    			<option value="">{{ __('common.Select') }}</option>
                                
                                 @if(!empty($classType)) 
                                      @foreach($classType as $type)
                                         <option value="{{ $type->id ?? ''  }}" {{ ($type->id == ($search['class_type_id'] ?? '')) ? 'selected' : '' }}>{{ $type->name ?? ''  }}</option>
                                      @endforeach
                                  @endif
                                </select>
                    	    </div>
                    	</div> 		
                    @endif
                    
                   <!-- @if(Session::get('role_id') == 1)
                        <div class="col-md-4 col-12">
                			<div class="form-group">
                				<label>{{ __('common.Search By Keywords') }}</label>
                				<input type="text" class="form-control" id="name" name="name"  value="{{ $search['name'] ?? '' }}" placeholder="{{ __('common.Ex. Name, Mobile, Email, Aadhaar etc.') }}">
                		    </div>
                		</div> 
                	@endif	-->
                </div>
                @else
                    <p class="text-center text-danger mt-2">You are not yet authorized for viewing attendance .... please contact your administrator</p>
                @endif
            
            
            <div class="col-md-12">
                <div class="flex_items">
                    <div class="instructions">
                        <span class="Present instruction_btn">P</span> Present
                    </div>
                    <div class="instructions">
                        <span class="Absent instruction_btn">A</span> Absent
                    </div>
                    <div class="instructions">
                        <span class="Holiday instruction_btn">H</span> Holiday
                    </div>
                    <div class="instructions">
                        <span class="Leave instruction_btn">L</span> Leave
                    </div>
                    <div class="instructions">
                        <span class="Event instruction_btn">E</span> Event
                    </div>
                </div>
            </div>
            
            <div class="col-md-12 flex_centered">
                <div class="month_box" id="month_box" style="display:none;">
                    <p id="month_year"></p>
                </div>
            </div>
            
            <div class="row" id="calendarContainer">
            </div>
            
            <!--<button onclick="downloadCSV()">Download CSV</button>-->
              </div>
                    
          </div>
            </div>
        </div>
      </div>
    </section>
</div>



<script>
        $(document).ready(function() {
            // Function to generate a calendar with attendance marks
           
        class_type_id

         
            
        });
    </script>
<style>
    .flex_items{
        display: flex;
        align-items: center;
        justify-content: center;
        background: #002c54;
        padding: 10px;
        border-radius: 4px;
        box-shadow: 0px 4px 6px #9d9d9d;
        color:white;
    }
    
    #calendarContainer{
        height: 400px;
        overflow-y: scroll;
        padding: 0px 16px;
    }
    
    .flex_centered{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .month_box p{
        margin-bottom: 0px;
    }
    .month_box{
        margin-top: 10px;
        width: 500px;
        border-radius: 4px;
        text-align: center;
        font-size: 29px;
        font-weight: 600;
        text-shadow: 4px 4px 5px #858585;
    }
    
    .instructions{
        display:flex;
        align-items:center;
    }
    
    .instruction_btn{
        padding: 10px;
        width: 40px;
        height: 40px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        margin-left: 10px;
        color:white;
    }



        /* Calendar styles */
        .container {
            text-align: center; /* Center align the calendars */
        }
        .calendar {
            border: 1px solid #ccc;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
            width: 100%;
            /*max-width: 200px;*/
            text-align: center;
            padding:0px;
            height:255px
        }
        .calendar-header {
            background-color: #002c54;
            color:white;
            padding: 3px;
            text-align: center;
            font-size:13px;
        }
        
        .padding_footer{
            padding: 3px 4px;
            font-size: 13px;
        }
        
        .week-symbols {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        @media only screen and (max-width: 600px) {
          .week-symbols {
            padding: 0px;
          }
          
           .calendar-date {
                width: 20px !important;
                height: 20px !important;
                font-size: 10px !important;
              }
          
          .week-symbol {
            width: 20px !important;
            height: 20px !important;
            font-size: 10px !important;
          }
          
          .calendar-dates{
              gap: 4px !important;
              padding: 0px;
          }
          
          .padding_footer {font-size: 11px;}
          
          .instructions{
              font-size: 11px;
          }
          
          .instruction_btn{
            width: 20px;
            height: 20px;
            margin-right: 8px;
            margin-left: 5px;
            font-size: 10px;
          }
          
          #calendarContainer{
              height:500px;
          }
        }
        
        .week-symbol {
            width: 25px;
            height: 25px;
            line-height: 23px; /* Adjust line-height to vertically center text */
            margin: 1px;
            border: 1px solid #ccc;
            background-color: skyblue; /* Added background color */
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }
        .calendar-body {
            padding: 1px;
            text-align: center;
            height:195px;
        }
        .calendar-dates {
            display: grid;
            grid-template-columns: repeat(7, 1fr); /* 7 columns for each day of the week */
            gap: 2px;
            text-align: center;
        }
        .calendar-date {
            width: 25px;
            height: 25px;
            line-height: 25px;
            border: 1px solid #ccc;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size:12px;
        }
        .Absent {
            background-color: red;
            color: white;
        }
        .Present {
            background-color: green;
            color: white;
        }
        .Event {
            background-color: coral;
            color: white;
        }
        .Holiday {
            background-color: #ffc107;
            color: black;
        }
        .Leave {
            background-color: #17a2b8;
            color: white;
        }
        
        
        .calendar_footer{
            margin-top:10px;
            border-top:1px solid grey;
            background: #002c54;
            color: white;
            padding: 2px 6px 6px 6px;
            position: absolute;
            bottom: 0;
            width: 100%;
        }


    </style>
    
   
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $(document).on('change', '#course', function() {
            var courseName = $(this).val();
            var classSelect = $('#class_type_id');
            
            classSelect.empty();
            @if(Session::get('role_id') != 2)
            classSelect.append('<option value="">{{ __("common.Select") }}</option>');
            @endif
            
            if (courseName) {
                $.ajax({
                    url: "{{ url('getClassesByCourse') }}",
                    type: "GET",
                    data: { course: courseName },
                    dataType: "json",
                    success: function(data) {
                        if (data && data.length > 0) {
                            $.each(data, function(key, val) {
                                classSelect.append('<option value="' + val.id + '">' + val.name + '</option>');
                            });
                        }
                        classSelect.val('').trigger('change.select2').trigger('change');
                    },
                    error: function() {
                        classSelect.val('').trigger('change.select2').trigger('change');
                    }
                });
            } else {
                @if(!empty($classType))
                    @foreach($classType as $type)
                        classSelect.append('<option value="{{ $type->id }}">{{ $type->name }}</option>');
                    @endforeach
                @endif
                classSelect.val('').trigger('change.select2').trigger('change');
            }
        });

        var month =  '';

        function generateCalendar(containerId, year, month, attendanceData, name, total) {
            var container = $("#" + containerId);
            var daysInMonth = new Date(year, month + 1, 0).getDate();
            var firstDayOfWeek = new Date(year, month, 1).getDay();
            var monthName = new Date(year, month, 1).toLocaleString('default', { month: 'long' });

            var weekSymbols = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
            var calendarHTML = '<div class="calendar col-md-2 col-6">';
            calendarHTML += '<div class="calendar-header">' + name + '</div>';
            calendarHTML += '<div class="week-symbols">';

            // Add week symbols
            weekSymbols.forEach(function(symbol) {
                calendarHTML += '<div class="week-symbol">' + symbol + '</div>';
            });

            calendarHTML += '</div><div class="calendar-body">';

            // Create date cells
            calendarHTML += '<div class="calendar-dates">';
            for (var i = 0; i < firstDayOfWeek; i++) {
                calendarHTML += '<div class="calendar-date empty"></div>';
            }
            for (var day = 1; day <= daysInMonth; day++) {
                var dateString = year + '-' + (month + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0');
                var attendanceClass = attendanceData[dateString] ? attendanceData[dateString] : '';
                calendarHTML += '<div class="calendar-date ' + attendanceClass + '">' + day + '</div>';
            }

            calendarHTML += '</div>';
            calendarHTML += '<div class="calendar_footer">' + '<span class="Present padding_footer">P: ' + total["Present"] + '</span>' + ' <span class="Absent padding_footer">A: ' + total["Absent"] + '</span> ' + ' <span class="Leave padding_footer">L: ' + total["Leave"] + '</span> ' + ' <span class="Holiday padding_footer">H: ' + total["Holiday"] + '</span> ' + ' <span class="Event padding_footer">E: ' + total["Event"] + '</span></div>';
            calendarHTML += '</div></div>';
            container.append(calendarHTML);
            $('#month_year').html(monthName + " " + year);
            $('#month_box').show();
          // calendarHTML += '<div class="calendar-header">' + monthName + ' ' + year + '</div>';
        }

        $("#date__").on("change", function() {
            $('#class_type_id').trigger('change');
        });

        function admissionData(admission_id, name) {
            var attendanceData = {};

            $.ajax({
                headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
                type: 'post',
                url: '/getAttendanceDates',
                data: {
                    admission_id: admission_id,
                    month: month
                },
                success: function(response) {
                    console.log(response.data)
                    generateCalendar("calendarContainer", 2024, month - 1, response.data, name, response.total);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }

        var allStudents = @json($allStudents);

        function filterStudents(classTypeId, admissionNo, course) {
            const filteredStudents = allStudents.filter(student => {
                const classMatch = (!classTypeId || student.class_type_id == classTypeId);
                const admissionMatch = (!admissionNo || student.admission_no == admissionNo || student.admissionNo == admissionNo);
                const courseMatch = (!course || student.course == course);
                return classMatch && admissionMatch && courseMatch;
            });
            return filteredStudents;
        }

        function displayStudents(students) {
            month = parseInt($('#date__').val());
            $('#calendarContainer').html('');
            var studentsList = $('#studentsList');
            studentsList.empty();
            if (students.length === 0) {
                studentsList.append('<p>No students found.</p>');
                return;
            }
            
            students.forEach(student => {
                admissionData(student.id, student.first_name);
            });
        }

        $('#class_type_id').change(function() {
            var selectedClassTypeId = $(this).val();
            var admissionNo = $('#admissionNo').val();
            var course = $('#course').val();
            if (admissionNo == "" && selectedClassTypeId == "" && course == "") {
                $('#calendarContainer').html('');
                $('#month_box').hide();
                return;
            }

            var filteredStudents = filterStudents(selectedClassTypeId, admissionNo, course);

            if (filteredStudents.length == 0) {
                toastr.info("Students Not Found");
                $('#calendarContainer').html('');
                $('#month_box').hide();
                return;
            }
            displayStudents(filteredStudents);
        });
        
        function filterByAdmissionNo(admissionNo) {
            const filteredStudents = allStudents.filter(student => {
                return student.admissionNo == admissionNo || student.admission_no == admissionNo;
            });
            return filteredStudents;
        }

        $('#admissionNo').change(function() {
            var selectedClassTypeId = $('#class_type_id').val();
            var admissionNo = $(this).val();
            var filteredStudents = filterByAdmissionNo(admissionNo);

            if (filteredStudents.length == 0) {
                toastr.info("Students Not Found");
                $('#calendarContainer').html('');
                return;
            }

            displayStudents(filteredStudents);
        });
    });
</script>

<script>
     $('#quickForm').submit(function(event) {
        var date_array =[];
        var allStudents = @json($allStudents);

       
        $('#student_list').html('');
        event.preventDefault();
         $('#loadingModal').modal('show');
        var name = $('#name').val();
        var class_type_id = $('#class_type_id').val();
        var date = parseInt($('#date__').val());
      
        var admissionNo = $('#admissionNo').val();
        var URL = "{{ url('/') }}";
        var year = 2024; // Example year
        // Example month (0-indexed, so 3 represents April)
        var daysInMonth = new Date(year, date, 0).getDate();
        var days = $('#days');
        days.html('');
        days.append('<th>Admission No</th>');
        days.append('<th >Name</th>');
        row_days = '';
        for(var i= 1; i<=daysInMonth; i++)
        {
             var date = new Date(year, (month-1), i);
             var dayOfWeek = date.toLocaleString('en', { weekday: 'short' }); 
             row_days += '<th class="days_">' +i+ ' ' + dayOfWeek+ '</th>';
        }
        days.append(row_days);
        var container = $('#student_list');
        var count =0;    
        allStudents.forEach(function(item,index) {

                if(parseInt(item.class_type_id) == parseInt(class_type_id))
                {


                    var row ='<tr><td>' +item.id + '</td><td>'+(item.first_name != null ? item.first_name : "") + (item.last_name != null ? item.last_name : "")+ '</td>';

                    var row2='';

                    var array_d = [];
                    for(var i= 1; i<=daysInMonth; i++)
        {
          var newclass =  year+'-'+$('#date__').val()+'-'+(i<10 ? '0'+i : i);
            array_d.push(newclass) ;
          
             row2 += '<td class='+newclass+'_'+item.id+'></td>';
        }                           
                container.append(row +row2+'</tr>');
                date_array[count]  ={'id':item.id , 'date':array_d}
                count++;
                }
              
            });


          

            var result = [];
            function divideIntoSlots(number) {
   
    var slots = Math.ceil(number / 15);
    var start =0;
    var end =0;

  
    for (var i = 0; i < slots; i++) {
        var slotValue = Math.min(15, number); 
        var end = start + slotValue - 1; // Subtract 1 to ensure end is inclusive
        result.push({ 'from': start, 'to': end });
        start = end + 1;
        number -= slotValue;
    }
    
   
    
    return result;
}



var slots = divideIntoSlots(date_array.length);


var loop =0;

fetchData();
            function fetchData() {

            //  console.log(date_array)
                if(loop < result.length)
                {
        $.ajax({
                 headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')},
            type:'post',
            url: URL+'/studentsAttendanceView',
            data: {data:JSON.stringify(date_array),loop:result[loop]},
            success: function (response) {
                $.each(response.data, function(index, item) {

              
                    $.each(item, function(index2, item2) {

                  var span_row = '';
                  var time24h = item2.time+'';
        var time12h = convertTo12HrFormat(time24h);
                          if(item2.attendance_status_id == 1)
                          {
                            span_row = '<span class="btn btn-success"style="font-size: 10px;"> P </span>'
                          }
                          else if(item2.attendance_status_id == 3){
                            span_row = '<span class="btn btn-danger" style="font-size: 10px;">A</span>'
                          }
                          else if(item2.attendance_status_id == 5){
                            span_row = '<span class="btn btn-dark" style="font-size: 10px;">H</span>'
                          }
                          else if(item2.attendance_status_id == 9){
                            span_row = '<span class="btn btn-info" style="font-size: 10px;">L</span>'
                          }
                          else if(item2.attendance_status_id == 10){
                            span_row = '<span class="btn btn-secondary" style="font-size: 10px;">E</span>'
                          }
                         
                    
                $('.'+item2.date+'_'+item2.admission_id).append(span_row);
           
               
            });
            });

            loop++;
            fetchData();
            }
          });
        }

        }
        });


        function convertTo12HrFormat(time24h) {
        var timeArray = time24h.split(':');
        var hours = parseInt(timeArray[0]);
        var minutes = parseInt(timeArray[1]);
        var period = hours < 12 ? 'AM' : 'PM';

        if (hours === 0) {
            hours = 12;
        } else if (hours > 12) {
            hours = hours - 12;
        }

        return hours + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + period;
    }

</script>

<script>



  
function downloadCSV() {
  let csv = [];
  const rows = document.querySelectorAll("table tr");

  for (const row of rows.values()) {
    const cells = row.querySelectorAll("td, th");
    const rowText = Array.from(cells).map((cell) => cell.innerText);
    csv.push(rowText.join(","));
  }
  const csvFile = new Blob([csv.join("\n")], {
    type: "text/csv;charset=utf-8;"
  });
  saveAs(csvFile, "Students Attendance.csv");
}

</script>        
<style>
    .view {
  margin: auto;
  width: 100%;
}

.wrapper {
  position: relative;
  overflow: auto;
  border: 1px solid black;
  white-space: nowrap;
}

.sticky-col {
  position: -webkit-sticky;
  position: sticky;
  background-color: white;
}

.first-col {
  width: 100px;
  min-width: 100px;
  max-width: 100px;
  left: 0px;
}

.second-col {
  width: 150px;
  min-width: 150px;
  max-width: 150px;
  left: 100px;
}

.paddingTable{
    padding-bottom:20px;    
}
.paddingTable th,td{
    padding:10px;
}
</style>
@endsection 