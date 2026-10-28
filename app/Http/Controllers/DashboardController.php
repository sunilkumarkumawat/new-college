<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\SidebarSub;
use App\Models\User;
use App\Models\Dashboard;
use App\Models\FeesMaster;
use App\Models\TeacherAttendance;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\PermissionManagement;
use App\Models\Enquiry;
use App\Models\hostel\HostelAssign;
use App\Models\Admission;
use App\Models\CollegeFees\CollegeFeeAssignment;
use App\Models\CollegeFees\CollegeFeeReceipt;
use App\Models\FeesDiscount;
use App\Models\library\LibraryAssign;
use App\Helpers\helper;
use Session;
use Hash;
use Str;
use Redirect;
use Response;
use Auth;
use App\Models\FeesDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller

{
    
    public function dashboard(){

        
        
        //if(!Session::get('lastVisitDate') || Session::get('lastVisitDate') !== date('Y-m-d')){ return $this->showAuthentication(); }

        $current_date = date('Y-m-d');
        $result = array();
        $barnch =Session::all();
        if(!empty(Session::get('admin_branch_id'))) {
               $branch_id = Session::get('admin_branch_id');
            }else{
               $branch_id = Session::get('branch_id');
            }
        if($barnch['role_id'] == 1 || $barnch['role_id'] == 6){
            $result['today_p_teacher'] = TeacherAttendance::where('branch_id' ,'=', $branch_id)->where('attendance_status_id',1)->where('date',$current_date)->count('id');
            $result['today_p_student'] = StudentAttendance::where('branch_id' ,'=', $branch_id)->where('attendance_status_id',1)->where('date',$current_date)->count('id');
            $result['teacher'] = Teacher::where('branch_id' ,'=', $branch_id)->count('id');
            $result['enquiry'] = Enquiry::where('branch_id' ,'=', $branch_id)->count('id');

            // --- College Fees Executive Live KPIs ---
            $collegeAssign = DB::table('college_fee_assignments')
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('COUNT(DISTINCT student_id) as assigned_students'),
                    DB::raw('SUM(gross_amount) as total_gross'),
                    DB::raw('SUM(discount_amount) as total_discount'),
                    DB::raw('SUM(net_amount) as total_net'),
                    DB::raw('SUM(paid_amount) as total_paid'),
                    DB::raw('SUM(due_amount) as total_due')
                )->first();

            $collegeReceipts = DB::table('college_fee_receipts')
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw('SUM(amount_paid) as total_collected')
                )->first();

            $collegeToday = DB::table('college_fee_receipts')
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->whereDate('payment_date', $current_date)
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount_paid) as amount')
                )->first();

            $collegeDefaultersCount = DB::table('college_fee_assignments')
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->where('due_amount', '>', 0)
                ->distinct('student_id')
                ->count('student_id');

            // Top Defaulter Batches (Ranked by highest pending dues DESC)
            $topDefaulterBatches = DB::table('college_fee_assignments as a')
                ->join('admissions as s', 'a.student_id', '=', 's.id')
                ->join('courses as c', 'a.course_id', '=', 'c.id')
                ->where('a.branch_id', $branch_id)
                ->whereNull('a.deleted_at')
                ->select(
                    'c.id as course_id',
                    'c.name as course_name',
                    'a.semester_no',
                    's.batch',
                    DB::raw('COUNT(DISTINCT s.id) as enrolled_count'),
                    DB::raw('SUM(a.gross_amount) as gross_demand'),
                    DB::raw('SUM(a.discount_amount) as discount_amount'),
                    DB::raw('SUM(a.net_amount) as net_demand'),
                    DB::raw('SUM(a.paid_amount) as total_paid'),
                    DB::raw('SUM(a.due_amount) as total_due'),
                    DB::raw('COUNT(DISTINCT CASE WHEN a.due_amount > 0 THEN s.id END) as defaulters_count')
                )
                ->groupBy('c.id', 'c.name', 'a.semester_no', 's.batch')
                ->having('total_due', '>', 0)
                ->orderBy('total_due', 'DESC')
                ->limit(6)
                ->get();

            // Recent College Fee Operations & Receipts
            $recentReceipts = DB::table('college_fee_receipts as r')
                ->join('admissions as s', 'r.student_id', '=', 's.id')
                ->join('courses as c', 'r.course_id', '=', 'c.id')
                ->where('r.branch_id', $branch_id)
                ->whereNull('r.deleted_at')
                ->select(
                    'r.id', 'r.receipt_no', 'r.amount_paid', 'r.payment_date', 'r.payment_mode', 'r.created_at',
                    's.admissionNo', 's.first_name', 's.last_name', 's.userName', 's.batch',
                    'c.name as course_name', 'r.semester_no'
                )
                ->orderBy('r.id', 'DESC')
                ->limit(8)
                ->get();

            // Payment Mode Breakdown
            $modeBreakdown = DB::table('college_fee_receipts')
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->select('payment_mode', DB::raw('SUM(amount_paid) as total_amt'), DB::raw('COUNT(*) as total_count'))
                ->groupBy('payment_mode')
                ->get();
            return view('dashboard.admin_dashboard', [
                'result' => $result,
                'current_date' => $current_date,
                'collegeAssign' => $collegeAssign,
                'collegeReceipts' => $collegeReceipts,
                'collegeToday' => $collegeToday,
                'collegeDefaultersCount' => $collegeDefaultersCount,
                'topDefaulterBatches' => $topDefaulterBatches,
                'recentReceipts' => $recentReceipts,
                'modeBreakdown' => $modeBreakdown
            ]);
}
        elseif($barnch['role_id'] == 2){
            return view('dashboard.teacher_dashboard',['result'=>$result]);
        } 
        elseif($barnch['role_id'] == 3){
            $hostelDeatils = HostelAssign::select('hostel_assign.*','hostel.name as hostel_name','building.name as building_name',
                        'floor.name as floor_name','room.name as room_name','bed.name as bed_name')
                        ->leftJoin('hostel','hostel.id','hostel_assign.hostel_id')
                        ->leftJoin('hostel_building as building','building.id','hostel_assign.building_id')
                        ->leftJoin('hostel_floor as floor','floor.id','hostel_assign.floor_id')
                        ->leftJoin('hostel_room as room','room.id','hostel_assign.room_id')
                        ->leftJoin('hostel_bed as bed','bed.id','hostel_assign.bed_id')
                        ->where('hostel_assign.id',$barnch['id'])
                        ->where('hostel_assign.branch_id',Session::get('branch_id'))
                        ->where('hostel_assign.session_id',Session::get('session_id'))
                        ->first();

            $result['feesDetail'] = FeesDetail::select('fees_detail.*')
                        ->leftJoin('admissions as admission','admission.id','fees_detail.admission_id')
                ->where('fees_detail.admission_id', $barnch['id'])->get(); 
                
                $class_type = Admission::where('id',$barnch['id'])->first();
                
             
                
            $result['school_fees'] = FeesMaster::select('fees_master.*','group.name as group_name')
                ->leftJoin('fees_group as group','group.id','fees_master.fees_group_id')
                ->where('fees_master.class_type_id', $class_type->class_type_id)->get(); 
            $result['hostel_fees'] = HostelAssign::where('admission_id', $barnch['id'])->get(); 
            $result['library_fees'] = LibraryAssign::where('admission_id', $barnch['id'])->get(); 
                
            
        if(empty($class_type->student_profile_update) || $class_type->student_profile_update == 'no'){
                    return redirect::to('profile/edit/'.$class_type->id)->with('error', 'Please Update Profile.');

        }
                    
              //  dd($result['assigned_fees']);
            
            return view('dashboard.student_dashboard',['result'=>$result,'hostelDeatils'=>$hostelDeatils]);
        }
        elseif($barnch['role_id'] == 4){
            return view('dashboard.libraryAdmin_dashboard',['result'=>$result]);
        }     
        elseif($barnch['role_id'] == 5){
            return view('dashboard.hostelAdmin_dashboard',['result'=>$result]);
        } 
        elseif($barnch['role_id'] == 7){
            return view('dashboard.transportAdmin_dashboard',['result'=>$result]);
        } 
        /*elseif($barnch['role_id'] == 8){
            return view('dashboard.libraryAdmin_dashboard',['result'=>$result]);
        }*/  
        elseif($barnch['role_id'] == 9){
            return view('dashboard.accountant_dashboard',['result'=>$result]);
        } 
        elseif($barnch['role_id'] == 10){
            return view('dashboard.otherSchoolStaff_dashboard',['result'=>$result]);
        }         
        else{
            return view('dashboard.else_dashboard',['result'=>$result]);
        }
     
      
    }

    public function sendAttendanceStatus(){
        
        //$dateTime = date('l jS \of F Y');
        $dateTime = date('l');
        $date = date('Y-m-d');
       /* $adminMail = User::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('role_id','1')->get()->first();*/    
        $staff = TeacherAttendance::with('Teacher')->where('session_id',5)->where('branch_id',1)->where('date',$date)->get();    
            
         

           if($dateTime !== 'Sunday'){
               
      
            $emailData = ['email' => 'skwork91@gmail.com','staff' => $staff,'dateTime' => $dateTime,'subject' => 'Today Staff Attendance.'];
            Helper::sendMail('email_print.admin.send_attendance_status', $emailData);

            $emailData = ['email' => 'veonkumawat@gmail.com','staff' => $staff,'dateTime' => $dateTime,'subject' => 'Today Staff Attendance.'];
            Helper::sendMail('email_print.admin.send_attendance_status', $emailData);
           } 
        return view('test',['staff'=>$staff,'dateTime'=>$dateTime]);
    }  

     public function discountdata(Request $request,$id){
         
        if(!empty($id)){
         
            $data = FeesDiscount::where('id',$id)->get()->first();
            
            $feesDiscount =$data['amount'];
    
           echo $feesDiscount;
            
           } 
    }
    
     public function duedate(Request $request,$id){
         
        if(!empty($id)){
         
            $data = FeesMaster::where('id',$id)->get()->first();
            
            $dueDate =$data['due_date'];
    
           echo $dueDate;
            
           } 
    }
    
    public function allStudentsSearch(Request $request){
        $value = $request->name;
        $data['Student'] = Admission::with('ClassTypes')->where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))
                ->where(function($query) use ($value){
    		        $query->where('first_name', 'like', '%' .$value. '%');
                    $query->orWhere('last_name', 'like', '%' .$value. '%');
                    $query->orWhere('mobile', 'like', '%' .$value. '%');
                    $query->orWhere('aadhaar', 'like', '%' .$value. '%');
                    $query->orWhere('email', 'like', '%' .$value. '%');
                    $query->orWhere('father_name', 'like', '%' .$value. '%');
                    $query->orWhere('mother_name', 'like', '%' .$value. '%');
                    $query->orWhere('address', 'like', '%' .$value. '%');
        		})->get();

        $data['Teacher'] = Teacher::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))
                ->where(function($query) use ($value){
    		        $query->where('first_name', 'like', '%' .$value. '%');
                    $query->orWhere('last_name', 'like', '%' .$value. '%');
                    $query->orWhere('mobile', 'like', '%' .$value. '%');
                    $query->orWhere('aadhaar', 'like', '%' .$value. '%');
                    $query->orWhere('email', 'like', '%' .$value. '%');
                    $query->orWhere('father_name', 'like', '%' .$value. '%');
                    $query->orWhere('address', 'like', '%' .$value. '%');
        		})->get();

        $data['User'] = User::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))
                ->where(function($query) use ($value){
    		        $query->where('first_name', 'like', '%' .$value. '%');
                    $query->orWhere('last_name', 'like', '%' .$value. '%');
                    $query->orWhere('mobile', 'like', '%' .$value. '%');
                    $query->orWhere('email', 'like', '%' .$value. '%');
                    $query->orWhere('father_name', 'like', '%' .$value. '%');
                    $query->orWhere('address', 'like', '%' .$value. '%');
        		})->get();
        		
        $data['SidebarSub'] = SidebarSub::where(function($query) use ($value){
    		        $query->where('sidebar_name', 'like', '%' .$value. '%');
                    $query->orWhere('name', 'like', '%' .$value. '%');
                    $query->orWhere('url', 'like', '%' .$value. '%');
        		})->get();
      return  view('dashboard.admin.all_students',['data'=>$data]);
    }   

    public function taskList(){
        return view('task_list');
    }

    
    public function studentDetail($id){
         $studentDetail = Admission::with('ClassTypes')->with('Section')->find($id);
         $feesDetail =  FeesDetail::with('PaymentMode')->with('FeesCollect')->with('FeesType')->where('admission_id', $id)->where('branch_id',Session::get('branch_id'))->get();
        return view('dashboard.admin.student_detail',['data'=>$studentDetail,'feesDetail'=>$feesDetail]);
    }

    public function stuStatus(Request $request){
        
       if($request->id >0){
        $uniqueId = Admission::findOrFail($request->id);
        $data = Admission::where('unique_system_id',$uniqueId->unique_system_id)->update(['status'=>$request->status]);
       if(!empty($data)){
       echo json_encode(1);
            }else{
                 echo json_encode(0);
            }
        }else{
        echo json_encode(2);
        }
        
    }       


    
    public function minidashboard(){
        return view('dashboard.minidashboard');
    }

    public function getModules(Request $request){
        if($request->isMethod('post')){
            $value = $request->name;
            
            $data = SidebarSub::where(function($query) use ($value){
		        $query->where('sidebar_name', 'like', '%' .$value. '%');
                $query->orWhere('name', 'like', '%' .$value. '%');
                $query->orWhere('url', 'like', '%' .$value. '%');
    		})->get();
    		
    		$sub_id = []; 
    		
    		foreach($data as $item){
    		    $sub_id[] = $item->id;
    		}
    		
    		$getModules = [];
    		
    		$find_permission = PermissionManagement::where('reg_user_id',Session::get('id'))->first();
    		foreach(explode(',', $find_permission->sidebar_sub_id) as $id){
    		    foreach($sub_id as $subId){
    		        if($id == $subId){
    		            $getModules[] = $subId;
    		        }
    		    }
    		}
    		
    		$modules = SidebarSub::whereIn('id',$getModules)->get();
    	//dd($modules);
    		   $html ='';
    		   
            foreach($modules as $item)
            {
                
               $html .= '<div class="col-md-2 info-box mb-3 bg-warning">';
                $html .= '<a href="'.htmlspecialchars(url($item->url), ENT_QUOTES, 'UTF-8') . '" class="d-flex">';
                $html .= '<span class="info-box-icon"><i class="fa '.htmlspecialchars($item->ican, ENT_QUOTES, 'UTF-8') . '"></i></span>';
                $html .= '<div class="info-box-content">';
                $html .= '<span class="info-box-text" style="font-size: 12px; white-space: break-spaces;">' . htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') . '</span>';
                $html .= '<span class="info-box-number" style="font-size: 12px;">5,200</span>';
                $html .= '</div>';
                $html .= '</a>';
                $html .= '</div>';

               
                 
            }
           echo $html;
    		
    	
        }
    }

    public function headerStudentSearch(Request $request){
        $term = trim($request->get('keyword') ?? $request->get('term') ?? $request->get('query') ?? '');
        if (empty($term)) {
            return response()->json(['status' => false, 'data' => [], 'message' => 'Empty search term']);
        }

        $branch_id = !empty(Session::get('admin_branch_id')) ? Session::get('admin_branch_id') : Session::get('branch_id');

        $query = Admission::with(['ClassTypes.course'])
            ->whereNull('deleted_at');

        if (!empty($branch_id)) {
            $query->where('branch_id', $branch_id);
        }

        $query->where(function($q) use ($term) {
            $q->where('first_name', 'like', '%' . $term . '%')
              ->orWhere('last_name', 'like', '%' . $term . '%')
              ->orWhere(DB::raw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,''))"), 'like', '%' . $term . '%')
              ->orWhere('father_name', 'like', '%' . $term . '%')
              ->orWhere('mother_name', 'like', '%' . $term . '%')
              ->orWhere('mobile', 'like', '%' . $term . '%')
              ->orWhere('father_mobile', 'like', '%' . $term . '%')
              ->orWhere('admissionNo', 'like', '%' . $term . '%')
              ->orWhere('unique_system_id', 'like', '%' . $term . '%')
              ->orWhere('batch', 'like', '%' . $term . '%')
              ->orWhere('dob', 'like', '%' . $term . '%')
              ->orWhere('aadhaar', 'like', '%' . $term . '%');
        });

        $students = $query->orderBy('first_name', 'ASC')->limit(15)->get();

        $results = [];
        $imagePath = env('IMAGE_SHOW_PATH', '') . 'profile/';

        foreach ($students as $stu) {
            $fullName = trim(($stu->first_name ?? '') . ' ' . ($stu->last_name ?? ''));

            // Resolve Course ID and Name
            $courseId = $stu->course_id ?? ($stu->ClassTypes->course_id ?? null);
            $courseName = $stu->ClassTypes->course->name ?? '';
            if (empty($courseId) && !empty($stu->course)) {
                $cFind = DB::table('courses')->where('name', trim($stu->course))->first();
                if ($cFind) {
                    $courseId = $cFind->id;
                    $courseName = $cFind->name;
                }
            }
            if (empty($courseName) && !empty($courseId)) {
                $courseName = DB::table('courses')->where('id', $courseId)->value('name') ?? 'N/A';
            }
            if (empty($courseName)) {
                $courseName = !empty($stu->course) ? $stu->course : 'N/A';
            }

            $batchVal = trim($stu->batch ?? '');
            $semesterName = $stu->ClassTypes->name ?? 'N/A';
            $dobFormatted = !empty($stu->dob) && $stu->dob !== '0000-00-00' ? date('d-m-Y', strtotime($stu->dob)) : 'N/A';

            // Resolve student active semester
            $resolvedSem = null;
            $stuAssignment = CollegeFeeAssignment::where('student_id', $stu->id)
                ->whereNull('deleted_at')
                ->orderBy('semester_no', 'DESC')
                ->first();
            if ($stuAssignment && !empty($stuAssignment->semester_no)) {
                $resolvedSem = (int)$stuAssignment->semester_no;
            } else {
                $ctName = $stu->ClassTypes->name ?? '';
                if (preg_match('/(\d+)(?:st|nd|rd|th)?\s*(?:sem|semester)?/i', $ctName, $m)) {
                    $resolvedSem = (int)$m[1];
                }
            }

            // Construct fee_url to college fees structures with batch, course, student_id, semester_no
            $feeUrl = route('college.fees.structures', array_filter([
                'course_id' => $courseId ?: 1,
                'course' => $courseName !== 'N/A' ? $courseName : null,
                'batch' => $batchVal ?: date('Y'),
                'semester_no' => $resolvedSem ?: null,
                'student_id' => $stu->id,
            ]));

            $results[] = [
                'id' => $stu->id,
                'name' => $fullName ?: 'Student #' . $stu->id,
                'admission_no' => $stu->admissionNo ?? '',
                'unique_id' => $stu->unique_system_id ?? '',
                'father_name' => $stu->father_name ?? 'N/A',
                'mother_name' => $stu->mother_name ?? '',
                'mobile' => $stu->mobile ?: ($stu->father_mobile ?? 'N/A'),
                'course' => $courseName,
                'course_id' => $courseId,
                'semester' => $semesterName,
                'batch' => $batchVal ?: 'N/A',
                'dob' => $dobFormatted,
                'photo' => !empty($stu->image) ? $imagePath . $stu->image : null,
                'fee_url' => $feeUrl,
                'profile_url' => url('studentDetail/' . $stu->id)
            ];
        }

        return response()->json([
            'status' => true,
            'count' => count($results),
            'data' => $results
        ]);
    }
}
