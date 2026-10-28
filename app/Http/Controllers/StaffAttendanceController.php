<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Salary;
use App\Models\SmsSetting;
use App\Models\WhatsappSetting;
use App\Models\Master\MessageTemplate;
use App\Models\Master\Weekendcalendar;
use App\Models\Master\Role;
use App\Models\Master\Branch;
use App\Models\Setting;
use App\Models\AttendanceStatus;
use App\Models\TeacherAttendance;
use Session;
use Hash;
use Helper;
use Str;
use Response;
use Redirect;
use Carbon\Carbon;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function autoStaffAttendance(Request $request){
        $attendance_status = $request->attendance_status; 
        $validatedData = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);   
        if ($request->has('date') && !empty($request->date)) {
            $dateOfEvent = Carbon::createFromFormat('Y-m-d', $request->date);
        } else {
            return response()->json(['error' => 'Date is missing'], 400);
        }
        $event = Weekendcalendar::whereDate('date',$dateOfEvent)->first();
        $staff_id = User::where('branch_id',Session::get('branch_id'))->where('status', 1)->get();
        if(!empty($staff_id)){
            foreach($staff_id as $key => $item){
                $oldData = TeacherAttendance::where('staff_id',$item->id)->where('date',$dateOfEvent)->first();
                if(!empty($oldData)){
                    $attendance = $oldData;
                } else {
                    $attendance = new TeacherAttendance;
                }  
                $attendance->user_id = Session::get('id');
                $attendance->session_id = Session::get('session_id');
                $attendance->branch_id = Session::get('branch_id');
                $attendance->staff_id = $item->id;
                $attendance->date = $dateOfEvent;
                $attendance->attendance_status_id = $attendance_status;
                if(!empty($event)){
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
        // 1. AJAX request to get existing attendance by date
        if ($request->ajax() && $request->has('get_attendance_by_date')) {
            $targetDate = $request->get('date', date('Y-m-d'));
            $attendanceMap = TeacherAttendance::where('branch_id', Session::get('branch_id'))
                ->where('date', $targetDate)
                ->get(['staff_id', 'attendance_status_id', 'time'])
                ->keyBy('staff_id');
            return response()->json([
                'status' => 'success',
                'date' => $targetDate,
                'attendance' => $attendanceMap
            ]);
        }

        if($request->isMethod('post')){
            $teacher_id = $request->staff_id;
            $attendance_status = $request->attendance_status;
            $name = $request->first_name;
            $mobile = $request->mobile;
            $email = $request->email;
            
            if(!empty($teacher_id)){
                for($count = 0; $count < count($teacher_id); $count++){
                    if(isset($teacher_id[$count])){
                        if($attendance_status[$count] == 3 || $attendance_status[$count] == 5){
                            TeacherAttendance::where('staff_id',$teacher_id[$count])->where('date',$request->date)->where('branch_id', Session::get('branch_id'))->delete();
                        }
                        $last_data = TeacherAttendance::where('staff_id',$teacher_id[$count])->where('date',$request->date)->first();
                        if(!empty($last_data)){
                            $attendance = $last_data;
                        } else {
                            $attendance = new TeacherAttendance;
                        }
                        $attendance->user_id = Session::get('id');
                        $attendance->session_id = Session::get('session_id');
                        $attendance->branch_id = Session::get('branch_id');
                        $attendance->staff_id = $teacher_id[$count];
                        $attendance->date = $request->date;
                        $attendance->time = $request->time;
                        $attendance->attendance_status_id = $attendance_status[$count];
                        if($attendance_status[$count] == 3 || $attendance_status[$count] == 5){
                            $attendance->current_attendance_status_id = $attendance_status[$count];
                        }
                        $attendance->save();
                        if(!empty($name) && isset($name[$count])){
                            $attendance->name = $name[$count];
                        }
                        if(!empty($mobile) && isset($mobile[$count])){
                            $attendance->mobile = $mobile[$count];
                        }
                        $oldData = TeacherAttendance::select('teacher_attendance.*','users.mobile','users.first_name','users.email','attendance_status.name as attendance_status_name','users.last_name')
                        ->leftjoin('users','users.id','teacher_attendance.staff_id')
                        ->leftjoin('attendance_status','attendance_status.id','teacher_attendance.attendance_status_id')
                        ->where('teacher_attendance.staff_id',$teacher_id[$count])
                        ->where('teacher_attendance.date',$request->date)
                        ->whereNull('teacher_attendance.deleted_at')
                        ->first();
                        
                        $template = MessageTemplate::Select('message_templates.*','message_types.slug')
                        ->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                        ->where('message_types.status',1)->where('message_types.slug','teacher-attendance')->first();
                        
                        $branch = Branch::find(Session::get('branch_id'));
                        $setting = Setting::where('branch_id',Session::get('branch_id'))->first();
                        $arrey1 = array(
                            '{#teacher_name#}',
                            '{#today_day#}',
                            '{#attendance_status#}',
                            '{#school_name#}',
                            '{#school_query_no#}'
                        );
                        $arrey2 = array(
                            ($oldData->first_name ?? '')." ".($oldData->last_name ?? ''),
                            date('d-m-Y',strtotime($request->date)),
                            $oldData->attendance_status_name ?? '',
                            $setting->name ?? '',
                            $setting->mobile ?? ''
                        );
                        if (!empty($template) && $template->status != 1) {
                            $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content ?? '');
                            if (!empty($setting) && $setting->firebase_notification == 1) {
                                Helper::sendNotification(
                                    $template->title ?? 'Attendence',
                                    $whatsapp,
                                    'user',
                                    $oldData->staff_id
                                ); 
                            }
                            if(!empty($oldData->email)){
                                if($branch->email_srvc != 0){
                                    if($template->email_status != 0){
                                        $message = str_replace($arrey1,$arrey2,$template->email_content);
                                        $emailData = ['email' => $oldData->email, 'data' => $message, 'subject' => $template->title]; 
                                        Helper::sendMail('email_print.template_print', $emailData);
                                    } 
                                } 
                            }
                            if($branch->whatsapp_srvc != 0){
                                if (!empty($oldData->mobile)){
                                    if($template->whatsapp_status != 0){
                                        $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                        Helper::sendWhatsappMessage($oldData->mobile,$whatsapp);
                                    }
                                }
                            }
                            if($branch->sms_srvc != 0){
                                if(!empty($oldData->mobile)){
                                    if($template->sms_status != 0){
                                        $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                        Helper::SendMessage($oldData->mobile, $sms);
                                    }
                                }
                            }    
                        }
                    }    		
                }  
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Attendance Saved Successfully!'
                    ]);
                }
                return redirect::to('staff/Attendance/view')->with('message', 'Attendance Added Successfully !');            
            }
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please select staff!'
                ], 422);
            }
            return redirect::to('staff_attendance_add')->with('error', 'Please Select Staff !');  
        }

        $branchId = Session::get('branch_id');
        $userRoleId = Session::get('role_id');
        $userId = Session::get('id');

        $query = User::with('roleName')
            ->where('branch_id', $branchId)
            ->where('status', 1);

        if ($userRoleId == 2) {
            $query->where('id', $userId);
        }

        $allteachers = $query->orderBy('id', 'DESC')->get();
        $roles = Role::orderBy('id', 'ASC')->get(['id', 'name']);
        $departments = User::where('branch_id', $branchId)->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');
        $todayAttendance = TeacherAttendance::where('branch_id', $branchId)->where('date', date('Y-m-d'))->get()->keyBy('staff_id');

        return view('staff/staff_attendance/add', [
            'data' => $allteachers,
            'roles' => $roles,
            'departments' => $departments,
            'todayAttendance' => $todayAttendance
        ]);
    }

    public function searchValueStaffAtten(Request $request){
        $search['name'] = $request->name;
        $data = User::where('branch_id',Session::get('branch_id'))->where('status',1);
        if($request->isMethod('post')){
            if(!empty($request->name)){
                $data = $data->where(function($query) use ($request) {
                    $query->where("first_name", 'like', '%' .$request->name. '%')
                    ->orWhere("last_name", 'like', '%' .$request->name. '%')
                    ->orWhere("mobile", 'like', '%' .$request->name. '%')
                    ->orWhere("email", 'like', '%' .$request->name. '%')
                    ->orWhere("userName", 'like', '%' .$request->name. '%');
                });
            }
        }
        $allstaff = $data->orderBy('id','DESC')->get();
        return view('staff/staff_attendance/attendance_Search',['data'=>$allstaff]);
    }     

    public function view(Request $request){
        $branchId = Session::get('branch_id');
        $userRoleId = Session::get('role_id');
        $userId = Session::get('id');

        $month = !empty($request->date) ? sprintf("%02d", $request->date) : date("m");
        $year = !empty($request->year) ? $request->year : date("Y");

        $search['name'] = $request->name ?? '';
        $search['date'] = $month;
        $search['year'] = $year;
        $search['role_id'] = $request->role_id ?? '';
        $search['department'] = $request->department ?? '';

        $totel_month_day = (int)date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
        $curr_mnt = $month;
        $curr_yrs = $year;

        $staffQuery = User::with('roleName')
            ->where('branch_id', $branchId)
            ->where('status', 1);

        if ($userRoleId == 2) {
            $staffQuery->where('id', $userId);
        }

        if (!empty($request->role_id)) {
            $staffQuery->where('role_id', $request->role_id);
        }

        if (!empty($request->department)) {
            $staffQuery->where('department', $request->department);
        }

        if (!empty($request->name)) {
            $val = $request->name;
            $staffQuery->where(function($q) use ($val) {
                $q->where('first_name', 'like', "%$val%")
                  ->orWhere('last_name', 'like', "%$val%")
                  ->orWhere('mobile', 'like', "%$val%")
                  ->orWhere('email', 'like', "%$val%")
                  ->orWhere('userName', 'like', "%$val%");
            });
        }

        $all_teachers = $staffQuery->orderBy('first_name', 'ASC')->get();
        $staffIds = $all_teachers->pluck('id')->toArray();

        // 1 Optimized Single Query for entire month's attendance
        $attendanceRecords = TeacherAttendance::where('branch_id', $branchId)
            ->whereIn('staff_id', $staffIds)
            ->whereMonth('date', $curr_mnt)
            ->whereYear('date', $curr_yrs)
            ->whereNull('deleted_at')
            ->get(['id', 'staff_id', 'date', 'time', 'attendance_status_id']);

        $atnrecord = [];
        foreach ($attendanceRecords as $record) {
            $dateKey = is_string($record->date) ? substr($record->date, 0, 10) : (is_object($record->date) ? $record->date->format('Y-m-d') : $record->date);
            $atnrecord[$record->staff_id][$dateKey] = [
                'id' => $record->id,
                'staff_id' => $record->staff_id,
                'date' => $dateKey,
                'time' => $record->time,
                'attendance_status_id' => $record->attendance_status_id
            ];
        }

        $AttStatus = AttendanceStatus::get()->keyBy('id')->toArray();
        $roles = Role::orderBy('id', 'ASC')->get(['id', 'name']);
        $departments = User::where('branch_id', $branchId)->whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');

        return view('staff/staff_attendance/index', [
            'data' => $atnrecord,
            'all_teachers' => $all_teachers,
            'AttStatus' => $AttStatus,
            'roles' => $roles,
            'departments' => $departments,
            'curr_yrs' => $curr_yrs,
            'curr_mnt' => $curr_mnt,
            'totel_month_day' => $totel_month_day,
            'search' => $search
        ]);
    }

    public function staff_attendance_save(Request $request){
        $Update = '';
        if($request->teacher_id){
            $attendance = TeacherAttendance::where('staff_id',$request->teacher_id)->where('date',$request->date)->where('branch_id', Session::get('branch_id'))->first();
            if(!empty($attendance)){
                // update existing
            } else {
                $attendance = new TeacherAttendance;
                $Update = 2;
            }
            $attendance->user_id = Session::get('id');
            $attendance->session_id = Session::get('session_id');
            $attendance->branch_id = Session::get('branch_id');
            $attendance->staff_id = $request->teacher_id;
            $attendance->attendance_status_id = $request->attendance_id;
            $attendance->date = date('Y-m-d');
            $attendance->save();
            if($Update == 2){
                return Response::json(array('stutes' => 2)); 
            } else {
                return Response::json(array('stutes' => 1)); 
            }
        } else {
            return Response::json(array('stutes' => 0)); 
        }
    }
}
