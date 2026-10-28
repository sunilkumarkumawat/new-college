<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Admission;
use App\Models\Salary;
use App\Models\SmsSetting;
use App\Models\WhatsappSetting;
use App\Models\TeacherAttendance;
use App\Models\TeacherCategory;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\Master\MessageTemplate;
use App\Models\Master\Weekendcalendar;
use App\Models\AttendanceStatus;
use App\Models\Setting;
use App\Models\Master\Branch;
use App\Models\ClassType;
use App\Models\Master\Course;
use Session;
use Hash;
use Helper;
use Str;
use Redirect;
use Carbon\Carbon;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{

            // public function add(Request $request){
                //  if($request->isMethod('post')){
                    // $admissionNo =$request->admission_id;
                    // $name =$request->name;
                    //  $mobile =$request->mobile;
                    //  $class_type_id =$request->class_type_id;
                    //  $attendance_status =$request->attendance_status;
                    // $email =$request->email;
                        // if(!empty($admissionNo)){
                        // $dateTime = date('l jS \of F Y h:i:s A');
                            // for($count = 0; $count <= count($admissionNo); $count++){
                                // if(isset($admissionNo[$count])){
                                    // // $oldData = StudentAttendance::where('admission_id',$admissionNo[$count])->where('date',$request->date)->get()->first();
                                    //// if(!empty($oldData)){
                                    // //$attendance =$oldData;
                                    // //}else{
                                    // // }  
                                        // $currentTime = Carbon::now();
                                        // $formattedTime = $currentTime->format('Y-m-d H:i:s');
                                        //$attendance = new StudentAttendance;//model name
                                        // $attendance->user_id = Session::get('id');
                                        // $attendance->session_id = Session::get('session_id');
                                        //$attendance->branch_id = Session::get('branch_id');
                                        //$attendance->class_type_id= $class_type_id[$count];
                                        //$attendance->admission_id= $admissionNo[$count];
                                        //$attendance->date  = $request->date;
                                        //$attendance->time  = $formattedTime;
                                        //$attendance->attendance_status_id  = $attendance_status[$count];
                                        //$attendance->save();
                                        //$attendance->name= $name[$count];
                                        //$attendance->mobile= $mobile[$count];
                                    	//$studentDetail = Admission::where('id',$admissionNo[$count])->get()->first();
                                        //$AttendanceStatus = AttendanceStatus::where('id',$attendance_status[$count])->get()->first();
                                        // $template =  MessageTemplate::Select('message_templates.*','message_types.slug')
                                        //->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                                        //->where('message_types.status',1)->where('message_types.slug','attendance')->first();
                                        //$branch = Branch::find(Session::get('branch_id'));
                                        //$setting = Setting::where('branch_id',Session::get('branch_id'))->first(); 
                                        //$arrey1 =   array(
                                        //'{#school_name#}',
                                        //'{#today_day#}',
                                        //'{#attendance_status#}',
                                        //'{#name#}',
                                        //'{#support_no#}');
                                        //$arrey2 = array(
                                        //$setting->name,
                                        //date('d-m-Y',strtotime($request->date)),
                                        //$AttendanceStatus->name,
                                        //$studentDetail->first_name." ".$studentDetail->last_name,
                                        //$setting->mobile);
                                        //if($template->status != 1){
                                            //if($studentDetail->email != ""){
                                                //if($branch->email_srvc != 0){
                                                //if($template->email_status != 0){
                                                //$message = str_replace($arrey1,$arrey2,$template->email_content);
                                                //$emailData = ['email' => $studentDetail->email, 'data' => $message, 'subject' => $template->title];
                                                //Helper::sendMail('email_print.template_print', $emailData);
                                            //} 
                                        //} 
                                    //}
                                    //  if($branch->whatsapp_srvc != 0){
                                    //      if ($studentDetail->mobile != ""){
                                    //          if($template->whatsapp_status != 0){
                                    //              $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                    //              Helper::sendWhatsappMessage($studentDetail->mobile,$whatsapp);
                                    //          }
                                    //      }
                                    //  }
                                    //  if($branch->sms_srvc != 0){
                                    //      if($studentDetail->mobile != ""){
                                    //          if($template->sms_status != 0){
                                    //              $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                    //              Helper::SendMessage($studentDetail->mobile, $sms);
                                    //          }
                                    //      }
                                    // }       
                                //} 
                             //}
                        //}    
                        //return redirect::to('studentsAttendanceView')->with('message', 'Attendance Submit Successfully !');            
                    // }
                    // return redirect::to('studentsAttendanceAdd')->with('error', 'Please Select Students !');  
                // 
                //return view('students/attendance/attendance_add');
            // }
            public function sundayAutoSubmitAttendance(Request $request){
                $sundays = collect();
                $currentDate = Carbon::now();
                $firstDayOfMonth = $currentDate->copy()->startOfMonth();
                $lastDayOfMonth = $currentDate->copy()->endOfMonth();
                // Loop through the month to find all Sundays
                for ($date = $firstDayOfMonth; $date->lte($lastDayOfMonth); $date->addDay()) {
                    if ($date->isSunday()) {
                        $sundays->push($date->format('Y-m-d'));
                    }
                }
                $admissionNo = Admission::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->get();
                    if(!empty($admissionNo)){
                       foreach($sundays as  $date)
                       {
                       foreach($admissionNo as $key=>  $item){
                            $oldData = StudentAttendance::where('admission_id',$item->id)->where('date',$date)->first();
                            if(!empty($oldData)){
                                $attendance = $oldData;
                            }else{
                                $attendance = new StudentAttendance;//model name
                            }  
                            $attendance->user_id = Session::get('id');
                            $attendance->session_id = Session::get('session_id');
                            $attendance->branch_id = Session::get('branch_id');
                        	$attendance->class_type_id= $item->class_type_id;
                    		$attendance->admission_id= $item->id;
                            //  $attendance->time  = date('H:i:s');
                    		$attendance->date  = $date;
                    		$attendance->attendance_status_id  = 5;
                            $attendance->save();
                        } 
                    }
                }    
            }
            
            public function getAttendanceDates(Request $request){
                $admission_id = $request->admission_id ?? '';
                $month = $request->month ?? '';
                $user = $request->user ?? 'Student';
                $data =[];
                if($user == 'Student'){
                    $data =  StudentAttendance::Select('student_attendance.*','attendance_status.name as atten_status')
                    ->leftjoin('attendance_status','student_attendance.attendance_status_id','attendance_status.id')
                    ->where('student_attendance.session_id',Session::get('session_id'))
                    ->where('student_attendance.branch_id',Session::get('branch_id'))
                    ->where('student_attendance.admission_id',$admission_id)
                    ->whereMonth('student_attendance.date','=',$month)->get();
                }
            else{
                $data =  TeacherAttendance::Select('teacher_attendance.*','attendance_status.name as atten_status')
                    ->leftjoin('attendance_status','teacher_attendance.attendance_status_id','attendance_status.id')
                    ->where('teacher_attendance.session_id',Session::get('session_id'))
                    ->where('teacher_attendance.branch_id',Session::get('branch_id'))
                    ->where('teacher_attendance.teacher_id',$admission_id)
                    ->whereMonth('teacher_attendance.date','=',$month)->get();
                }  
                $attendance = [];
                $total['Present'] = 0;
                $total['Absent'] = 0;
                $total['Holiday'] = 0;
                $total['Leave'] = 0;
                $total['Event'] = 0;
                if(!empty($data))
                foreach ($data as $item) {
                    $attendance[$item->date] = $item->atten_status;
                    if($item->atten_status =='Present')
                    {
                     $total['Present'] += 1;
                    }
                    if($item->atten_status =='Absent')
                    {
                        $total['Absent'] += 1;
                    }
                    if($item->atten_status =='Holiday')
                    {
                        $total['Holiday'] += 1;
                    }
                    if($item->atten_status =='Leave')
                    {
                        $total['Leave'] += 1;
                    }
                    if($item->atten_status =='Event')
                    {
                         $total['Event'] += 1;
                    }
                }
                return response()->json(['data' => $attendance, 'total'=>$total]);
            }
    
            public function autoStudentAttendance(Request $request){
                $attendance_status = $request->attendanceStatus; 
                //dd($request->dateOfEvent);
                $dateOfEvent = Carbon::createFromFormat('Y-m-d', $request->date);
                $event = Weekendcalendar::whereDate('date',$dateOfEvent)->first();
                $admissionNo = Admission::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->get();
                if(!empty($admissionNo)){
                    $dateTime = date('l jS \of F Y h:i:s A');
                    foreach($admissionNo as $key=>  $item){
                        $oldData = StudentAttendance::where('admission_id',$item->id)->where('date',$dateOfEvent)->first();
                        if(!empty($oldData)){
                            $attendance = $oldData;
                        }else{
                            $attendance = new StudentAttendance;//model name
                        }  
                        $attendance->user_id = Session::get('id');
                        $attendance->session_id = Session::get('session_id');
                        $attendance->branch_id = Session::get('branch_id');
                    	$attendance->class_type_id= $item->class_type_id;
                		$attendance->admission_id= $item->id;
                        //$attendance->time  = date('H:i:s');
                		$attendance->date  = $dateOfEvent;
                		$attendance->attendance_status_id  = $attendance_status;
                        if(!empty($event))
                        {
                            $attendance->save();
                        }
                    } 
                    if(!empty($event)){
                        $event->is_attendance_submitted = 1;
                        $event->save();
                    }
                }    
            }
            
            public function add(Request $request){
                if($request->isMethod('post')){
                    $admissionNo = is_array($request->admission_ids) ? $request->admission_ids : json_decode($request->admission_ids);
                    if(!empty($admissionNo)){
                        $dateTime = date('l jS \of F Y h:i:s A');
                        $currentTime = Carbon::now();
                        $formattedTime = $currentTime->format('Y-m-d H:i:s');
                        $savedCount = 0;

                        foreach($admissionNo as $key => $item){
                            $item = (object) $item;
                            if (empty($item->admission_id)) continue;

                            $oldData = StudentAttendance::where('admission_id', $item->admission_id)
                                ->where('branch_id', Session::get('branch_id'))
                                ->where('date', $request->date)
                                ->first();

                            if(!empty($oldData)){
                                $attendance = $oldData;
                            } else {
                                $attendance = new StudentAttendance;
                            }  

                            $attendance->user_id = Session::get('id');
                            $attendance->session_id = Session::get('session_id');
                            $attendance->branch_id = Session::get('branch_id');
                            $attendance->class_type_id = $item->class_type_id ?? null;
                            $attendance->admission_id = $item->admission_id;
                            $attendance->date = $request->date;
                            $attendance->attendance_status_id = $item->attendance_status ?? 1;
                            $attendance->name = $item->name ?? '';
                            $attendance->mobile = $item->mobile ?? '';
                            $attendance->save();
                            $savedCount++;

                            // Optional messaging notification logic
                            $studentDetail = Admission::where('id', $item->admission_id)->first();
                            $AttendanceStatus = AttendanceStatus::where('id', $attendance->attendance_status_id)->first();
                            $template = MessageTemplate::select('message_templates.*', 'message_types.slug')
                                ->leftjoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                                ->where('message_types.status', 1)
                                ->where('message_types.slug', 'attendance')
                                ->first();
                            $branch = Branch::find(Session::get('branch_id'));
                            $setting = Setting::where('branch_id', Session::get('branch_id'))->first(); 

                            if ($studentDetail && $AttendanceStatus && $setting) {
                                $arrey1 = [
                                    '{#school_name#}',
                                    '{#today_day#}',
                                    '{#attendance_status#}',
                                    '{#name#}',
                                    '{#support_no#}'
                                ];
                                $arrey2 = [
                                    $setting->name ?? '',
                                    date('d-m-Y', strtotime($request->date)),
                                    $AttendanceStatus->name ?? '',
                                    ($studentDetail->first_name ?? '') . " " . ($studentDetail->last_name ?? ''),
                                    $setting->mobile ?? ''
                                ];

                                if (!empty($template) && $template->status != 1) {
                                    $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content ?? '');
                                    if (!empty($setting) && $setting->firebase_notification == 1) {
                                        Helper::sendNotification(
                                            $template->title ?? 'Attendance',
                                            $whatsapp,
                                            'student',
                                            $item->admission_id
                                        ); 
                                    }
                                    if(!empty($branch) && $branch->whatsapp_srvc != 0 && !empty($studentDetail->mobile) && !empty($template->whatsapp_status)){
                                        Helper::sendWhatsappMessage($studentDetail->mobile, $whatsapp);
                                    }
                                }
                            }
                        }    

                        if ($request->ajax()) {
                            return response()->json([
                                'status' => 'success',
                                'message' => 'Attendance for ' . $savedCount . ' student(s) submitted successfully!',
                                'count' => $savedCount
                            ]);
                        }

                        return redirect::to('studentsAttendanceView')->with('message', 'Attendance Submitted Successfully !');            
                    }

                    if ($request->ajax()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Please select at least one student to mark attendance.'
                        ], 422);
                    }

                    return redirect::to('studentsAttendanceAdd')->with('error', 'Please Select Students !');  
                }
                return view('students/attendance/attendance_add');
            }
    
            public function studentPanelAttendanceView(Request $request){
                return view('students/attendance/studentPanelAttendanceView');
            }
            
            public function view(Request $request){
                $search['name'] = $request->name;
                $search['course'] = $request->course;
                $search['batch'] = $request->batch;
                $search['class_type_id'] = $request->class_type_id;
                $search['admissionNo'] = $request->admissionNo;
                $search['date'] = !empty($request->date) ? $request->date : date("m");
                $allStudents = Admission::where('branch_id',Session::get('branch_id'));
                if(Session::get('role_id') == 3){
                   $allStudents = $allStudents->where('id',Session::get('id'));
                }
                if(!empty($request->course)){
                    $courseName = $request->course;
                    $courseObj = \App\Models\Master\Course::where('name', $courseName)->first();
                    $courseClassIds = [];
                    if ($courseObj) {
                        $courseClassIds = ClassType::where('course_id', $courseObj->id)->pluck('id')->toArray();
                    }
                    $allStudents = $allStudents->where(function($q) use ($courseName, $courseClassIds) {
                        $q->where('course', $courseName);
                        if (!empty($courseClassIds)) {
                            $q->orWhereIn('class_type_id', $courseClassIds);
                        }
                    });
                }
                if(!empty($request->batch)){
                    $allStudents = $allStudents->where('batch', $request->batch);
                }
                if(!empty($request->class_type_id)){
                    $allStudents = $allStudents->where('class_type_id', $request->class_type_id);
                }
                if(!empty($request->admissionNo)){
                    $allStudents = $allStudents->where('admissionNo', $request->admissionNo);
                }
                if(!empty($request->name)){
                    $allStudents = $allStudents->where(function($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->name . '%')
                          ->orWhere('last_name', 'like', '%' . $request->name . '%')
                          ->orWhere('mobile', 'like', '%' . $request->name . '%')
                          ->orWhere('email', 'like', '%' . $request->name . '%')
                          ->orWhere('aadhaar', 'like', '%' . $request->name . '%');
                    });
                }
                $allStudents=$allStudents->orderBy('first_name','ASC')->get();
                if($request->isMethod('post') && $request->has('data')){
                    // dd($request->loop['to']);
                    $data = json_decode($request->data);
                    if(!empty($data)){
                        $attendance =[];
                        foreach($data as $key=> $item){
                            if ((intval($request->loop['from']) <= $key) && (intval($request->loop['to']) >= $key)) {
                                $attendance[] = StudentAttendance::where('admission_id',$item->id)->whereIn('date',$item->date)->get();
                            }  
                        }
                    }
                    return response()->json(['data' => $attendance]);
                }
                if(Session::get('role_id') == 3){
                    return view('students/attendance/studentPanelAttendanceView');
                }
                elseif(Session::get('role_id') == 1){
                    return redirect::to('studentsAttendanceViewTable'); 
                }
                else{
                    return view('students/attendance/attendance_view',['search'=>$search,'allStudents'=>$allStudents]);
                }
            }    
            
            public function viewTable(Request $request){
                $search['name'] = $request->name;
                $search['course'] = $request->course;
                $search['class_type_id'] = $request->class_type_id;
                $search['batch'] = $request->batch;
                $search['admissionNo'] = $request->admissionNo;
                $search['year'] = !empty($request->year) ? $request->year : date("Y");
                $search['date'] = !empty($request->date) ? $request->date : date("m");

                $allStudents = Admission::where('branch_id',Session::get('branch_id'));
                if(Session::get('role_id') == 3){
                   $allStudents = $allStudents->where('id',Session::get('id'));
                }
                if(!empty($request->course)){
                    $courseName = $request->course;
                    $courseObj = \App\Models\Master\Course::where('name', $courseName)->first();
                    $courseClassIds = [];
                    if ($courseObj) {
                        $courseClassIds = ClassType::where('course_id', $courseObj->id)->pluck('id')->toArray();
                    }
                    $allStudents = $allStudents->where(function($q) use ($courseName, $courseClassIds) {
                        $q->where('course', $courseName);
                        if (!empty($courseClassIds)) {
                            $q->orWhereIn('class_type_id', $courseClassIds);
                        }
                    });
                }
                if(!empty($request->class_type_id)){
                    $targetClass = ClassType::find($request->class_type_id);
                    if ($targetClass) {
                        $sameNameIds = ClassType::where('name', $targetClass->name)->pluck('id')->toArray();
                        $allStudents = $allStudents->whereIn('class_type_id', $sameNameIds);
                    } else {
                        $allStudents = $allStudents->where('class_type_id', $request->class_type_id);
                    }
                }
                if(!empty($request->batch)){
                    $allStudents = $allStudents->where('batch', $request->batch);
                }
                if(!empty($request->admissionNo)){
                    $allStudents = $allStudents->where('admissionNo', $request->admissionNo);
                }
                if(!empty($request->name)){
                    $allStudents = $allStudents->where(function($q) use ($request) {
                        $q->where('first_name', 'like', '%' . $request->name . '%')
                          ->orWhere('last_name', 'like', '%' . $request->name . '%')
                          ->orWhere('mobile', 'like', '%' . $request->name . '%')
                          ->orWhere('email', 'like', '%' . $request->name . '%')
                          ->orWhere('aadhaar', 'like', '%' . $request->name . '%');
                    });
                }
                $allStudents = $allStudents->select('id', 'admissionNo', 'first_name', 'last_name', 'mobile', 'email', 'aadhaar', 'course', 'class_type_id', 'batch')->orderBy('first_name','ASC')->get();

                if($request->isMethod('post') && $request->has('data')){
                    $data = json_decode($request->data);
                    $attendance = [];
                    if(!empty($data)){
                        $from = intval($request->loop['from'] ?? 0);
                        $to = intval($request->loop['to'] ?? 0);
                        $targetItems = array_slice($data, $from, ($to - $from + 1));
                        
                        $admissionIds = array_column($targetItems, 'id');
                        $allDates = [];
                        foreach($targetItems as $tItem){
                            if(!empty($tItem->date)){
                                $allDates = array_merge($allDates, (array)$tItem->date);
                            }
                        }
                        $allDates = array_unique($allDates);

                        if(!empty($admissionIds) && !empty($allDates)){
                            $records = StudentAttendance::whereIn('admission_id', $admissionIds)
                                ->whereIn('date', $allDates)
                                ->select('id', 'admission_id', 'date', 'time', 'out_time', 'attendance_status_id')
                                ->get()
                                ->groupBy('admission_id');

                            foreach($targetItems as $tItem){
                                $attendance[] = isset($records[$tItem->id]) ? $records[$tItem->id]->values() : [];
                            }
                        }
                    }
                    return response()->json(['data' => $attendance]);
                }
                return view('students/attendance/attendance_view_2',['search'=>$search,'allStudents'=>$allStudents]);
            }    

            public function SearchValueAtten(Request $request){
                $search['name'] = $request->name;
                $search['course'] = $request->course;
                $search['class_type_id'] = $request->class_type_id;
                $search['batch'] = $request->batch;
                $search['admissionNo'] = $request->admissionNo;

                $data = Admission::with('ClassTypes')
                    ->where('branch_id', Session::get('branch_id'))
                    ->where('status', '1');

                if (!empty(Session::get('admin_branch_id'))) {
                    $data = $data->where('branch_id', Session::get('admin_branch_id'));
                }

                if (!empty($request->course)) {
                    $courseName = $request->course;
                    $courseObj = \App\Models\Master\Course::where('name', $courseName)->first();
                    $courseClassIds = [];
                    if ($courseObj) {
                        $courseClassIds = ClassType::where('course_id', $courseObj->id)->pluck('id')->toArray();
                    }
                    $data = $data->where(function($q) use ($courseName, $courseClassIds) {
                        $q->where('course', $courseName);
                        if (!empty($courseClassIds)) {
                            $q->orWhereIn('class_type_id', $courseClassIds);
                        }
                    });
                }

                if (!empty($request->batch)) {
                    $data = $data->where('batch', $request->batch);
                }

                if (!empty($request->class_type_id)) {
                    $targetClass = ClassType::find($request->class_type_id);
                    if ($targetClass) {
                        $sameNameClassIds = ClassType::where('name', $targetClass->name)->pluck('id')->toArray();
                        $data = $data->whereIn('class_type_id', $sameNameClassIds);
                    } else {
                        $data = $data->where('class_type_id', $request->class_type_id);
                    }
                }

                if (!empty($request->admissionNo)) {
                    $data = $data->where('admissionNo', 'like', '%' . $request->admissionNo . '%');
                }

                if (!empty($request->name)) {
                    $value = $request->name;
                    $data->where(function($query) use ($value) {
                        $query->where('first_name', 'like', '%' . $value . '%')
                            ->orWhere('last_name', 'like', '%' . $value . '%')
                            ->orWhere('father_name', 'like', '%' . $value . '%')
                            ->orWhere('mother_name', 'like', '%' . $value . '%')
                            ->orWhere('mobile', 'like', '%' . $value . '%')
                            ->orWhere('email', 'like', '%' . $value . '%')
                            ->orWhere('aadhaar', 'like', '%' . $value . '%')
                            ->orWhere('admissionNo', 'like', '%' . $value . '%');
                    });
                }

                $allstudents = $data->orderBy('first_name', 'ASC')->get();

                $date = !empty($request->custom_date) ? date('Y-m-d', strtotime($request->custom_date)) : date('Y-m-d');
                $studentIds = $allstudents->pluck('id')->toArray();
                $attendances = [];
                if (!empty($studentIds)) {
                    $attendances = StudentAttendance::whereIn('admission_id', $studentIds)
                        ->whereDate('date', $date)
                        ->pluck('attendance_status_id', 'admission_id')
                        ->toArray();
                }

                return view('students/attendance/attendance_Search', [
                    'data' => $allstudents,
                    'search' => $search,
                    'custom_date' => $date,
                    'attendances' => $attendances
                ]);
            }   
            
            public function attendanceSendMassage(Request $request){
                $todate = date('Y-m-d');
                $studentdata = StudentAttendance::Select('student_attendance.*','admissions.name','admissions.mobile')
                ->leftjoin('admissions','admissions.id','student_attendance.admission_id')->where('student_attendance.status',0)->whereDate('date',$todate)->get();
                foreach($studentdata as $item){
                    if(!empty($item['mobile'])){
                        $data_count = StudentAttendance:: where('admission_id',$item->admission_id)->whereDate('date',$todate)->count(); 
                        $mobile =$item['mobile'];
                        if($item->status == 0 && $data_count == 1){
                            $SmsSetting = SmsSetting::where('category','StudentIN')->get()->first();
                            $setting = Setting::find(1);
                            $message = !empty($SmsSetting->message) ? $SmsSetting->message : '' ;
                            $template_id = $SmsSetting->template_id;
                            $name =$setting['mobile'];
                            $message_otp = str_replace(array('{#var#}'), array($name), $message);
                        }else{
                            $SmsSetting = SmsSetting::where('category','StudentOUT')->get()->first();
                            $setting = Setting::find(1);
                            $message = !empty($SmsSetting->message) ? $SmsSetting->message : '' ;
                            $template_id = $SmsSetting->template_id;
                            $name =$setting['mobile'];
                            $message_otp = str_replace(array('{#var#}'), array($name), $message);
                        }
                        Helper::SendMessage($mobile,$message_otp,$template_id); 
                        StudentAttendance::where('admission_id',$item['admission_id'])->update(['status'=>1]);
                    }
                }  
            } 
            
            public function attendancedelete(Request $request){
                $query = StudentAttendance::where('branch_id', Session::get('branch_id'))
                    ->whereDate('date', $request->date);

                if (!empty($request->admission_id_Reset)) {
                    $ids = explode(',', $request->admission_id_Reset);
                    $query->whereIn('admission_id', $ids);
                }

                $query->forceDelete();
                return redirect::to('studentsAttendanceAdd')->with('message', 'Attendance Reset Successfully !');
            }
}
