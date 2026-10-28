<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use App\Models\User;
use App\Models\ClassType;
use App\Models\Setting;
use App\Models\FeesMaster;
use App\Models\Enquiry;
use App\Models\Admission;
use App\Models\StudentAction;
use App\Models\Classs;
use App\Models\BillCounter;
use App\Models\SmsSetting;
use App\Models\WhatsappSetting;
use App\Models\Teacher;
use App\Models\State;
use App\Models\Remark;
use App\Models\City;
use App\Models\Sessions;
use App\Models\Master\MessageTemplate;
use App\Models\fees\FeesAssign;
use App\Models\fees\FeesAssignDetail;
use App\Models\FeesDetail;
use App\Models\FeesCollect;
use App\Models\fees\FeesDetailsInvoices;
use App\Models\Master\SchoolDesk;
use App\Models\Master\MessageType;
use App\Models\Master\Branch;
use App\Models\Master\Course;
use Session;
use Hash;
use Helper;
use QrCode;
use Response;
use Str;
use PDF;
use Mail;
use DB;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnquiryController extends Controller

{
            public function studentsDashboard(){
                $branch_id = Session::get('branch_id');
                $session_id = Session::get('session_id');
                
                $totalEnquiries = Enquiry::where(function($q) use ($branch_id) {
                    if (Session::get('role_id') > 1 && !empty($branch_id)) {
                        $q->where('branch_id', $branch_id);
                    }
                })->where('session_id', $session_id)->count();

                $totalAdmissions = Admission::where(function($q) use ($branch_id) {
                    if (Session::get('role_id') > 1 && !empty($branch_id)) {
                        $q->where('branch_id', $branch_id);
                    }
                })->where('session_id', $session_id)->where('status', '1')->count();

                $totalPromoted = Admission::where(function($q) use ($branch_id) {
                    if (Session::get('role_id') > 1 && !empty($branch_id)) {
                        $q->where('branch_id', $branch_id);
                    }
                })->where('session_id', $session_id)->where('status', '1')
                ->whereIn('admissionNo', function($sub) use ($branch_id) {
                    $sub->select('admissionNo')
                        ->from('admissions')
                        ->whereNull('deleted_at');
                    if (Session::get('role_id') > 1 && !empty($branch_id)) {
                        $sub->where('branch_id', $branch_id);
                    }
                    $sub->groupBy('admissionNo')
                        ->havingRaw('count(*) > 1');
                })->count();

                $totalPendingCredentials = Admission::where(function($q) use ($branch_id) {
                    if (Session::get('role_id') > 1 && !empty($branch_id)) {
                        $q->where('branch_id', $branch_id);
                    }
                })->where('session_id', $session_id)->where('status', '1')->where(function($q) {
                    $q->whereNull('userName')->orWhere('userName', '');
                })->count();

                return view('students/studentsDashboard', compact(
                    'totalEnquiries',
                    'totalAdmissions',
                    'totalPromoted',
                    'totalPendingCredentials'
                ));
            }
 
            public function qrCode(Request $request){
                $BillCounter = BillCounter::where('session_id',Session::get('session_id'))->where('type', 'StudentRegistration')->first();
                $BillCounterNo = null; // Initialize BillCounterNo variable
                if(Session::get('role_id') > 1 && !empty($BillCounter)){
                    $BillCounter = $BillCounter->where('branch_id',Session::get('branch_id'));
                }
                if (!empty($BillCounter)) {
                    $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                    $BillCounterNo = $counter + 1;
                }
                if ($request->isMethod('post')) {
                    $request->validate([
                        'first_name' => 'required',
                        'mobile' => 'required|digits:10',
                        'father_name' => 'required',
                    ]);
                    // Increment counter
                    if (!empty($BillCounter)) {
                        $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                        $BillCounter->counter = $counter + 1;
                        $BillCounter->save();
                    }
                    $addqr = new Enquiry;
                    $addqr->user_id = Session::get('id');
                    $addqr->session_id = Session::get('session_id');
                    $addqr->branch_id = Session::get('branch_id');
                    $addqr->registration_no = $BillCounterNo; // Use BillCounterNo here
                    $addqr->first_name = $request->first_name;
                    $addqr->mobile = $request->mobile;
                    $addqr->father_name = $request->father_name;
                    $addqr->remark_1 = $request->remark_1;
                    $addqr->status = 2;
                    $addqr->save();
                    return redirect::to('qr_code')->with('message', 'Enquiry Add Successful.');
                }
                return view('students.enquiry.enquiry_qrCode',['BillCounter' => $BillCounterNo]);
            }

            public function enquiryAdd(Request $request){
                $BillCounter = BillCounter::where('session_id',Session::get('session_id'))->where('type', 'StudentRegistration')->where('branch_id',Session::get('branch_id'))->get()->first();
                if ($request->isMethod('post')) {
                    $request->validate([
                        'first_name' => 'required',
                        'gender_id' => 'required',
                        'mobile' => 'required|digits:10',
                        'father_name' => 'required',
                        'mother_name' => 'required',
                    ]);
                    $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                    $BillCounter->counter = $counter + 1;
                    $BillCounter->save();
                    $addstudents = new Enquiry; //model name
                    $addstudents->user_id = Session::get('id');
                    $addstudents->session_id = Session::get('session_id');
                    $addstudents->branch_id = Session::get('branch_id');
                    $addstudents->registration_no = $request->registration_no;
                    $addstudents->first_name = $request->first_name;
                    $addstudents->last_name = $request->last_name;
                    $addstudents->id_proof = $request->id_proof;
                    $addstudents->id_number = $request->id_number;
                    $addstudents->email  = $request->email;
                    $addstudents->mobile  = $request->mobile;
                    $addstudents->course_id = $request->course_id;
                    $addstudents->class_type_id = $request->class_type_id;
                    if (empty($addstudents->course_id) && !empty($request->class_type_id)) {
                        $classObj = ClassType::find($request->class_type_id);
                        if ($classObj) {
                            $addstudents->course_id = $classObj->course_id;
                        }
                    }
                    $addstudents->father_name = $request->father_name;
                    $addstudents->mother_name = $request->mother_name;
                    $addstudents->father_mobile = $request->father_mobile;
                    $addstudents->dob = $request->dob;
                    $addstudents->gender_id = $request->gender_id;
                    $addstudents->enrollment_no = $request->enrollment_no;
                    $addstudents->web_registration_no = $request->web_registration_no;
                    $addstudents->agent_id = $request->agent_id;
                    $addstudents->registration_date = date('Y-m-d');
                    $addstudents->address = $request->address;
                    $addstudents->remark_1 = $request->remark_1;
                    $addstudents->save();
                    $template =  MessageTemplate::Select('message_templates.*','message_types.slug')
                    ->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                    ->where('message_types.status',1)->where('message_types.slug','student-registration')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('branch_id',Session::get('branch_id'))->first();
                        $arrey1 =   array(
                            '{#name#}',
                            '{#school_name#}',
                            '{#email#}',
                            '{#mobile#}');
                        $arrey2 = array(
                            $request->first_name." ".$request->last_name,
                            $setting->name,
                            $request->email,
                            $request->mobile);
                        if (!empty($template) && $template->status != 1) {
                            if($request->email != ""){
                                if($branch->email_srvc != 0){
                                    if($template->email_status != 0){
                                        $message = str_replace($arrey1,$arrey2,$template->email_content);
                                        $emailData = ['email' => $request->email, 'data' => $message, 'subject' => $template->title];
                                        Helper::sendMail('email_print.template_print', $emailData);
                                    } 
                                } 
                            }
                        if($branch->whatsapp_srvc != 0){
                            if ($request->mobile != ""){
                                if($template->whatsapp_status != 0){
                                    $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                    Helper::sendWhatsappMessage($request->mobile,$whatsapp);
                                }
                            }
                        }
                        if($branch->sms_srvc != 0){
                            if($request->mobile != ""){
                                if($template->sms_status != 0){
                                    $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                    Helper::SendMessage($request->mobile, $sms);
                                }
                            }
                        }    
                    }
                    return redirect::to('enquiryView')->with('message', 'Enquiry Add Successful.');
                }
                return view('students.enquiry.add', ['BillCounter' => $BillCounter]);
            }

            public function enquiryView(Request $request){
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                $search['registration_no'] = $request->registration_no;
                $search['name'] = $request->name;
                $search['mobile'] = $request->mobile;
                $search['father_name'] = $request->father_name;
                $search['course_id'] = $request->course_id;
                $search['class_type_id'] = $request->class_type_id;
                $search['registration_date'] = $request->registration_date;
                $search['reminder_date'] = $request->reminder_date;
                $search['enquiry_status'] = $request->enquiry_status;
                $search['state_id'] = $request->state_id;
                $search['city_id'] = $request->city_id;
                $search['agent_id'] = $request->agent_id;
                $search['enrollment_no'] = $request->enrollment_no;
                $search['web_registration_no'] = $request->web_registration_no;

                $data = Enquiry::select(
                    'enquirys.*',
                    'class_types.name as class_name',
                    DB::raw('COALESCE(c1.name, c2.name) as course_name')
                )
                ->with('Agent')
                ->leftJoin('class_types', 'class_types.id', '=', 'enquirys.class_type_id')
                ->leftJoin('courses as c1', 'c1.id', '=', 'enquirys.course_id')
                ->leftJoin('courses as c2', 'c2.id', '=', 'class_types.course_id');

                if (Session::get('role_id') > 1) {
                    $data = $data->where('enquirys.branch_id', Session::get('branch_id'));
                }
                if (!empty(Session::get('admin_branch_id'))) {
                    $data = $data->where('enquirys.branch_id', Session::get('admin_branch_id'));
                }
                if (!empty($search['session_id'])) {
                    $data = $data->where('enquirys.session_id', $search['session_id']);
                }

                // 1-to-1 Respective Column Filter Matches
                if (!empty($request->registration_no)) {
                    $data = $data->where('enquirys.registration_no', 'like', '%' . $request->registration_no . '%');
                }
                if (!empty($request->name)) {
                    $nameVal = $request->name;
                    $data = $data->where(function ($query) use ($nameVal) {
                        $query->where('enquirys.first_name', 'like', '%' . $nameVal . '%')
                              ->orWhere('enquirys.last_name', 'like', '%' . $nameVal . '%')
                              ->orWhere(DB::raw("CONCAT(enquirys.first_name, ' ', enquirys.last_name)"), 'like', '%' . $nameVal . '%')
                              ->orWhere('enquirys.email', 'like', '%' . $nameVal . '%');
                    });
                }
                if (!empty($request->mobile)) {
                    $data = $data->where('enquirys.mobile', 'like', '%' . $request->mobile . '%');
                }
                if (!empty($request->father_name)) {
                    $data = $data->where('enquirys.father_name', 'like', '%' . $request->father_name . '%');
                }
                if (!empty($request->course_id)) {
                    $courseId = $request->course_id;
                    $data = $data->where(function($q) use ($courseId) {
                        $q->where('enquirys.course_id', $courseId)
                          ->orWhere('class_types.course_id', $courseId);
                    });
                }
                if (!empty($request->class_type_id)) {
                    $data = $data->where('enquirys.class_type_id', $request->class_type_id);
                }
                if (!empty($request->registration_date)) {
                    $data = $data->whereDate('enquirys.registration_date', $request->registration_date);
                }
                if (!empty($request->reminder_date)) {
                    $data = $data->whereDate('enquirys.reminder_date', $request->reminder_date);
                }
                if (!empty($request->enquiry_status)) {
                    $data = $data->where('enquirys.enquiry_status', $request->enquiry_status);
                }
                if (!empty($request->state_id)) {
                    $data = $data->where('enquirys.state_id', $request->state_id);
                }
                if (!empty($request->city_id)) {
                    $data = $data->where('enquirys.city_id', $request->city_id);
                }
                if (!empty($request->agent_id)) {
                    $data = $data->where('enquirys.agent_id', $request->agent_id);
                }
                if (!empty($request->enrollment_no)) {
                    $data = $data->where('enquirys.enrollment_no', 'like', '%' . $request->enrollment_no . '%');
                }
                if (!empty($request->web_registration_no)) {
                    $data = $data->where('enquirys.web_registration_no', 'like', '%' . $request->web_registration_no . '%');
                }
                $allstudents = $data->orderBy('enquirys.id', 'DESC')->get();
                if($request->pdf == "pdf"){
                    $allstudents = $data->orderBy('enquirys.id', 'DESC')->get();
                    $pdf = PDF::loadView('print_file.pdf.registration_list',['data'=>$allstudents]);
                    return $pdf->download('student_registration.pdf');
                }
                 return view('students.enquiry.view', ['data' => $allstudents, 'search' => $search]);
            }

            public function enquiryEdit(Request $request, $id){
                //dd($request);
                $data = Enquiry::find($id);
                    if ($request->isMethod('post')) {
                    $request->validate([
                        'first_name' => 'required',
                        'gender_id' => 'required',
                        'mobile' => 'required|digits:10',
                        'father_name' => 'required',
                        'mother_name' => 'required',
                    ]);
                   // $data = new Enquiry; //model name
                    $data->user_id = Session::get('id');
                    $data->session_id = Session::get('session_id');
                    $data->branch_id = Session::get('branch_id');
                    $data->registration_no = $request->registration_no;
                    $data->first_name = $request->first_name;
                    $data->last_name = $request->last_name;
                    $data->id_proof = $request->id_proof;
                    $data->id_number = $request->id_number;
                    $data->email  = $request->email;
                    $data->mobile  = $request->mobile;
                    $data->course_id = $request->course_id;
                    $data->class_type_id = $request->class_type_id;
                    if (empty($data->course_id) && !empty($request->class_type_id)) {
                        $classObj = ClassType::find($request->class_type_id);
                        if ($classObj) {
                            $data->course_id = $classObj->course_id;
                        }
                    }
                    $data->father_name = $request->father_name;
                    $data->mother_name = $request->mother_name;
                    $data->father_mobile = $request->father_mobile;
                    $data->dob = $request->dob;
                    $data->gender_id = $request->gender_id;
                    $data->enrollment_no = $request->enrollment_no;
                    $data->web_registration_no = $request->web_registration_no;
                    $data->agent_id = $request->agent_id;
                    $data->registration_date = date('Y-m-d');
                    $data->address = $request->address;
                    $data->remark_1 = $request->remark_1;
                    $data->save();
                    return redirect::to('enquiryView')->with('message', 'Enquiry Updated Successfully.');
                }
                $getstate = State::where('country_id', $data['country_id'])->get();
                $getcitie = City::where('state_id', $data['state_id'])->get();
                $getcitie = City::where('state_id', $data['state_id'])->get();
                    return view('students.enquiry.edit', ['data' => $data, 'getState' => $getstate, 'getCity' => $getcitie]);
            }

            public function enquiryDelete(Request $request){
                $data = Enquiry::find($request->delete_id)->delete();
                return redirect::to('enquiryView')->with('message', 'Enquiry Deleted Successfully !');
            }

            public function studentIdPrintMultiple(Request $request){
                $request->validate([
                    'checkbox'  => 'required',
                ]);
                $ids =  Admission::Select('admissions.*','class_types.name as class_name')
                ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                ->find($request->checkbox);
                // $ids = Admission::select("*")->find($request->checkbox);
                //$student_id =  Admission::find($id);
                //   $printPreviewId = Helper::printPreview('Student Id Print');
                //      return view($printPreviewId, ['data' => $ids]);
                 return view('print_file.student_print.multipal_id_print', ['data' => $ids]);
            }

            public function registrationPrint(Request $request, $id){
                $registration =  Enquiry::with('ClassTypes')->with('Gender')->find($id);
                $printPreview =    Helper::printPreview('Enquiry Print');
                //   dd($printPreview);
                return view($printPreview, ['data' => $registration]);
                //return view('print_file.student_print.registration_print', ['data' => $registration]);
            }
 
            public function action_add(Request $request){
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name'  => 'required',
                        'student_class'  => 'required',
                    ]);
                    $addstudentaction = new StudentAction; //model name
                    $addstudentaction->user_id = Session::get('id');
                    $addstudentaction->session_id = Session::get('session_id');
                    $addstudentaction->branch_id = Session::get('branch_id');
                    $addstudentaction->student_old_new = $request->spl_files1;
                    $addstudentaction->name = $request->name;
                    $addstudentaction->student_class = $request->student_class;
                    $addstudentaction->student_roll_no = $request->student_roll_no;
                    $addstudentaction->save();
                    return redirect::to('student_action_index')->with('message', 'Students Id Update Successfully.');
                }
                return view('students.student_action.action_add');
            }

            public function student_action_index(){
                $allstudent_action =  StudentAction::where('session_id', Session::get('session_id'))
                ->where('branch_id', Session::get('branch_id'))->orderBy('id', 'DESC')->get();
                return view('students.student_action.student_action_index', ['data' => $allstudent_action]);
            }

            public function class_type_search(Request $request){
                if (!empty($request->class_type_id)) {
                    $data = array();
                    $data = Classs::where('class_id', $request->class_type_id)->get();
                    $stateData = '';
                    foreach ($data as $class) {
                        $stateData .= '
                        <option value="' . $class->id . '">' . $class->name . '</option>';
                    }
                    echo $stateData;
                }
            }
            
            public function studentRegistrationDetail(Request $request, $id){
                $data = Enquiry::find($id);
                $remark = Remark::where('Student_id', $data['id'])->orderBy('id', 'DESC')->get();
                if (!empty($request->class_type_id)) {
                    $data = $data->where("class_type_id", $request->class_type_id);
                }
                return view('students.enquiry.studentRegistrationDetail', ['data' => $data, 'remark' => $remark]);
            }

            public function enquiryRemarkAdd(Request $request){
                if ($request->isMethod('post')) {
                    $request->validate([]);
                    $enquiry = Enquiry::where('id',$request->student_id)->update(['enquiry_status'=>$request->enquiry_status_id,'message'=>$request->remark,'reminder_date'=>$request->date]);
                    $remark = new Remark; //model name
                    $remark->session_id = Session::get('session_id');
                    $remark->branch_id = Session::get('branch_id');
                    $remark->date = $request->date;
                    $remark->student_id = $request->student_id;
                    $remark->enquiry_status_id = $request->enquiry_status_id;
                    $remark->remark = $request->remark;
                    $remark->save();
                    return redirect::to('enquiryView')->with('message', 'Remark Add Successful.');
                }
            }

            public function enquiryRemarkEdit(Request $request){
                $data = Remark::find($request->student_id);
                if ($request->isMethod('post')) {
                    /*$request->validate([
                       'date' => 'required'
                    ]);*/
                    $data->date = $request->date;
                    $data->remark = $request->remark;
                    $data->student_id = $data->student_id;
                    $data->save();
                    return redirect::to('studentRegistrationDetail/'. $data->student_id)->with('message', 'Remark Edit Successfully !');
                }
                return view('students.enquiry.studentRegistrationDetail', ['data' => $data]);
            }
    
            public function studentDetails(Request $request){
                $search['name'] = $request->name;
                $search['course'] = $request->course;
                $search['batch'] = $request->batch;
                $search['class_type_id'] = $request->class_type_id;
                $data = Admission::with('ClassTypes')->with('FeesAssign')->where('status','1');
                if(Session::get('role_id') > 1){
                    $data = $data->where('branch_id',Session::get('branch_id'));
                }
                if (!empty(Session::get('admin_branch_id'))) {
                    $data = $data->where('branch_id', Session::get('admin_branch_id'));
                }
                if(!empty($request->name)){
                    $value = $request->name;
                    $data->where(function($query) use ($value){
                        $query->where("first_name", 'like', '%' .$value. '%')
                            ->orWhere("last_name", 'like', '%' .$value. '%')
                            ->orWhere("father_name", 'like', '%' .$value. '%')
                            ->orWhere("mother_name", 'like', '%' .$value. '%')
                            ->orWhere("mobile", 'like', '%' .$value. '%')
                            ->orWhere("email", 'like', '%' .$value. '%')
                            ->orWhere("aadhaar", 'like', '%' .$value. '%');
                    });
                }
                if(!empty($request->admissionNo)){
                    $data = $data->where("admissionNo", $request->admissionNo);
                }
                if(!empty($request->course)){
                    $courseName = $request->course;
                    $courseObj = \App\Models\Master\Course::where('name', $courseName)->first();
                    $courseClassIds = [];
                    if ($courseObj) {
                        $courseClassIds = ClassType::where('course_id', $courseObj->id)->pluck('id')->toArray();
                    }
                    $data = $data->where(function($q) use ($courseName, $courseClassIds) {
                        $q->where('admissions.course', $courseName);
                        if (!empty($courseClassIds)) {
                            $q->orWhereIn('admissions.class_type_id', $courseClassIds);
                        }
                    });
                }
                if(!empty($request->batch)){
                    $data = $data->where("batch", $request->batch);
                }
                if(!empty($request->class_type_id)){
                    $data = $data->where("class_type_id", $request->class_type_id);
                }
                $allstudents = $data->orderBy('first_name','ASC')->get();
                return view('students/student_details',['data'=>$allstudents,'search'=>$search]);
            }

            public function promoteAdd(Request $request){
                $search['name'] = $request->name;
                $search['course'] = $request->course;
                $search['batch'] = $request->batch;
                $search['class_type_id'] = $request->class_type_id;
                $search['admissionNo'] = $request->admissionNo;
                $search['status'] = $request->has('status') ? $request->status : '1';
                if ($request->isMethod('post') || !empty($request->course) || !empty($request->batch) || !empty($request->class_type_id) || !empty($request->admissionNo) || !empty($request->name)) {
                    $data = Admission::with('ClassTypes');
                    if(Session::get('role_id') > 1){
                        $data = $data->where('branch_id', Session::get('branch_id'));
                    }
                    if (!empty(Session::get('admin_branch_id'))) {
                        $data = $data->where('branch_id', Session::get('admin_branch_id'));
                    }
                    if(!empty($request->batch)){
                        $data = $data->where("batch", $request->batch);
                    }
                    if(!empty($request->name)){
                        $value = $request->name;
                        $data->where(function($query) use ($value){
                            $query->where("first_name", 'like', '%' .$value. '%');
                            $query->orWhere("last_name", 'like', '%' .$value. '%');
                            $query->orWhere("father_name", 'like', '%' .$value. '%');
                            $query->orWhere("mother_name", 'like', '%' .$value. '%');
                            $query->orWhere("mobile", 'like', '%' .$value. '%');
                            $query->orWhere("email", 'like', '%' .$value. '%');
                            $query->orWhere("aadhaar", 'like', '%' .$value. '%');
                        });
                    }
                    if(!empty($request->admissionNo)){
                       $data = $data->where("admissionNo", $request->admissionNo);
                    }
                    if(!empty($request->course)){
                       $data = $data->where("course", $request->course);
                    }           
                    if(!empty($request->class_type_id)){
                        $selectedClass = ClassType::find($request->class_type_id);
                        if ($selectedClass) {
                            $siblingIds = ClassType::where('course_id', $selectedClass->course_id)
                                ->where(function($q) use ($selectedClass) {
                                    $q->where('orderBy', $selectedClass->orderBy)
                                      ->orWhere('name', $selectedClass->name);
                                })
                                ->pluck('id')
                                ->toArray();
                            $siblingIds[] = (int)$request->class_type_id;
                            $data = $data->whereIn('class_type_id', array_unique($siblingIds));
                        } else {
                            $data = $data->where("class_type_id", $request->class_type_id);
                        }
                    }

                    // Status handling (1 = Active, 0 = Inactive, all = Both)
                    if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
                        $data = $data->where("status", $request->status);
                    } else if (!$request->has('status') || $request->status === '1') {
                        $data = $data->where("status", 1);
                    }

                    // A student can have history admission rows across semesters.
                    // Keep only the latest active row for each student.
                    $allstudents = $data->orderBy('id', 'DESC')->get()
                        ->unique(function ($admission) {
                            return !empty($admission->unique_system_id)
                                ? $admission->unique_system_id
                                : (!empty($admission->admissionNo) ? ($admission->admissionNo . '_' . $admission->first_name) : ('admission_' . $admission->id));
                        })
                        ->sortBy('first_name')
                        ->values();

                    $promotionAdmissionIds = $allstudents->pluck('id')->all();
                    $assignedTotals = FeesAssignDetail::whereIn('admission_id', $promotionAdmissionIds)
                        ->whereNull('deleted_at')
                        ->groupBy('admission_id')
                        ->select(
                            'admission_id',
                            DB::raw('SUM(fees_group_amount) as assigned_total'),
                            DB::raw('SUM(discount) as discount_total'),
                            DB::raw('SUM(promotion_carry_adjustment) as carry_adjustment_total'),
                            DB::raw('SUM(discount) as academic_discount_total')
                        )
                        ->get()
                        ->keyBy('admission_id');
                    $paidTotals = FeesDetail::whereIn('admission_id', $promotionAdmissionIds)
                        ->whereIn('status', [0, 1])
                        ->whereNull('deleted_at')
                        ->groupBy('admission_id')
                        ->select('admission_id', DB::raw('SUM(paid_amount) as paid_total'))
                        ->get()
                        ->keyBy('admission_id');
                    $allstudents->each(function ($student) use ($assignedTotals, $paidTotals) {
                        $assigned = $assignedTotals[$student->id] ?? null;
                        $rawDue = (float)($assigned->assigned_total ?? 0)
                            - (float)($assigned->discount_total ?? 0)
                            - (float)($assigned->carry_adjustment_total ?? 0)
                            - (float)($paidTotals[$student->id]->paid_total ?? 0);
                        $student->previous_due = round($rawDue, 2);
                        $student->previous_discount = (float)($assigned->academic_discount_total ?? 0);
                    });
                    $session = Sessions::all();
                    return view('students/promote/promote',['search'=>$search,'data'=>$allstudents,'session'=>$session]);
                }
                $session = Sessions::all();
                return view('students/promote/promote',['search'=>$search,'session'=>$session]);
            }  
            
            public function getPromotionProgressionInfo(Request $request)
            {
                try {
                    $courseName = $request->course;
                    $batch = $request->batch;
                    $selectedClassId = $request->class_type_id ? (int)$request->class_type_id : null;
                    $branchId = Session::get('branch_id');

                    if (empty($courseName)) {
                        return response()->json(['status' => false, 'message' => 'Course is required.'], 422);
                    }

                    $course = \App\Models\Master\Course::where('name', $courseName)->first();
                    if (!$course) {
                        return response()->json(['status' => false, 'message' => 'Course not found.'], 404);
                    }

                    // 1. All semester classes for this course ordered 1 to 8, etc.
                    $allCourseClassesQuery = ClassType::where(function($q) use ($course, $courseName) {
                        $q->where('course_id', $course->id)
                          ->orWhere('name', 'LIKE', $courseName . ' %');
                    })->whereNull('deleted_at');
                    if (!empty($branchId)) {
                        $allCourseClassesQuery->where(function($q) use ($branchId) {
                            $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                        });
                    }
                    $allCourseClasses = $allCourseClassesQuery->orderBy('orderBy', 'ASC')->orderBy('id', 'ASC')->get();
                    $uniqueClasses = $allCourseClasses->unique('orderBy')->values();

                    // 2. Identify current active class / semester to promote from
                    $currentClass = null;
                    if ($selectedClassId) {
                        $currentClass = ClassType::find($selectedClassId);
                    }

                    if (!$currentClass && !empty($batch)) {
                        // Find latest semester where students of this batch are actively enrolled
                        $admissionQuery = Admission::join('class_types', 'class_types.id', '=', 'admissions.class_type_id')
                            ->where('admissions.status', '1')
                            ->where('admissions.batch', $batch)
                            ->where(function($q) use ($course, $courseName) {
                                $q->where('class_types.course_id', $course->id)
                                  ->orWhere('admissions.course', $courseName);
                            })
                            ->whereNull('admissions.deleted_at');

                        if (!empty($branchId)) {
                            $admissionQuery->where('admissions.branch_id', $branchId);
                        }

                        $currentClass = $admissionQuery
                            ->orderBy('class_types.orderBy', 'DESC')
                            ->orderBy('admissions.id', 'DESC')
                            ->select('class_types.*')
                            ->first();
                    }

                    // Fallback to first class if not found
                    if (!$currentClass && $uniqueClasses->isNotEmpty()) {
                        $currentClass = $uniqueClasses->first();
                    }

                    // Map $currentClass to its corresponding instance in $uniqueClasses
                    $resolvedCurrentClass = null;
                    if ($currentClass) {
                        $resolvedCurrentClass = $uniqueClasses->first(function($c) use ($currentClass) {
                            return (int)$c->id === (int)$currentClass->id
                                || (!empty($currentClass->orderBy) && (int)$c->orderBy === (int)$currentClass->orderBy)
                                || (trim(strtolower($c->name)) === trim(strtolower($currentClass->name)));
                        });
                    }
                    if (!$resolvedCurrentClass && $uniqueClasses->isNotEmpty()) {
                        $resolvedCurrentClass = $uniqueClasses->first();
                    }

                    $currentOrder = $resolvedCurrentClass ? (int)$resolvedCurrentClass->orderBy : ($currentClass ? (int)$currentClass->orderBy : 1);
                    $nextOrder = $currentOrder + 1;

                    // Yearly or Even semester progression suggests Next Academic Session
                    $isYearly = ($resolvedCurrentClass && stripos($resolvedCurrentClass->name, 'Year') !== false) || ($currentClass && stripos($currentClass->name, 'Year') !== false);
                    $isNextSession = $isYearly || ($currentOrder % 2 === 0);

                    $sourceSessionId = (int)($resolvedCurrentClass->session_id ?? ($currentClass->session_id ?? (Session::get('session_id') ?: 7)));
                    $nextSession = Sessions::where('id', '>', $sourceSessionId)->orderBy('id', 'ASC')->first();
                    $targetSessionId = ($isNextSession && $nextSession) ? (int)$nextSession->id : $sourceSessionId;

                    // Target class for next semester
                    $targetClass = $uniqueClasses->firstWhere('orderBy', $nextOrder);
                    if (!$targetClass) {
                        $targetClass = $allCourseClasses->firstWhere('orderBy', $nextOrder);
                    }
                    if (!$targetClass) {
                        $targetClass = ClassType::where('course_id', $course->id)
                            ->where('orderBy', $nextOrder)
                            ->whereNull('deleted_at')
                            ->first();
                    }

                    return response()->json([
                        'status' => true,
                        'course' => $course->name,
                        'course_id' => $course->id,
                        'batch' => $batch,
                        'current_classes' => $uniqueClasses,
                        'current_class_id' => $resolvedCurrentClass ? (int)$resolvedCurrentClass->id : ($currentClass ? (int)$currentClass->id : null),
                        'current_class_name' => $resolvedCurrentClass ? $resolvedCurrentClass->name : ($currentClass ? $currentClass->name : null),
                        'current_order' => $currentOrder,
                        'next_order' => $nextOrder,
                        'is_next_session' => $isNextSession,
                        'suggested_mode' => $isNextSession ? 'next_session' : 'same_session',
                        'target_session_id' => $targetSessionId,
                        'target_classes' => $uniqueClasses,
                        'target_class_id' => $targetClass ? (int)$targetClass->id : null,
                        'target_class_name' => $targetClass ? $targetClass->name : null,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
                }
            }

            public function studentsPromoteAdd(Request $request){
                if ($request->isMethod('post')) {
                    $request->validate([
                        'promote_class_type_id' => 'required',
                        'date' => 'required',
                    ]);
                    if(!empty($request->admission_ids)){
                        $promoteClass = ClassType::with('course')->find($request->promote_class_type_id);
                        if(!$promoteClass){
                            $errorMsg = 'Selected promotion class / semester was not found.';
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json(['status' => false, 'message' => $errorMsg], 422);
                            }
                            return redirect::back()->withInput()->with('error', $errorMsg);
                        }
                        $selectedAdmissions = Admission::whereIn('id', $request->admission_ids)->get();
                        $targetCourse = !empty($request->promote_course) ? $request->promote_course : ($promoteClass->course->name ?? null);
                        $targetCourseId = $promoteClass ? $promoteClass->course_id : null;
                        $firstBatch = !empty($request->promote_batch) ? $request->promote_batch : ($selectedAdmissions->first()->batch ?? '2026');
                        $branchId = Session::get('branch_id') ?: ($selectedAdmissions->first()->branch_id ?? 1);

                        // Auto-assign session_id from promoteClass or Session context
                        $defaultSessionId = !empty($promoteClass->session_id) ? $promoteClass->session_id : (Session::get('session_id') ?: 7);
                        $sessionId = !empty($request->session_id) ? $request->session_id : $defaultSessionId;
                        $request->merge(['session_id' => $sessionId]);

                        $promotedCount = 0;
                        $alreadyPromotedCount = 0;
                        $carriedDueTotal = 0;
                        $carriedAdvanceTotal = 0;
                        $continuedDiscountTotal = 0;
                        $continueDiscountAdmissionIds = array_map('intval', $request->continue_discount_admission_ids ?? []);

                        // Increase script execution timeout for batch promotions
                        @ini_set('max_execution_time', 300);
                        if (function_exists('set_time_limit')) {
                            @set_time_limit(300);
                        }

                        // Pre-resolve target semester fee masters once for the entire batch
                        $batchFeeMasters = \App\Helpers\helper::getBatchSemesterFeeMasters(
                            $branchId,
                            $targetCourseId,
                            $request->promote_class_type_id,
                            $firstBatch,
                            $request->session_id
                        );

                        $resolvedBaseMasters = collect([]);
                        foreach($batchFeeMasters as $bfm){
                            if(strtolower(trim(optional($bfm->feesGroup)->name ?? '')) === 'previous dues'){
                                continue;
                            }

                            $targetFm = FeesMaster::where('branch_id', $branchId)
                                ->where('session_id', $request->session_id)
                                ->where('class_type_id', $request->promote_class_type_id)
                                ->where('fees_group_id', $bfm->fees_group_id)
                                ->where('batch', $firstBatch)
                                ->whereNull('deleted_at')
                                ->first();

                            if(!$targetFm){
                                $targetFm = new FeesMaster;
                                $targetFm->user_id = Session::get('id');
                                $targetFm->branch_id = $branchId;
                                $targetFm->session_id = $request->session_id;
                                $targetFm->class_type_id = $request->promote_class_type_id;
                                $targetFm->course_id = $targetCourseId;
                                $targetFm->batch = $firstBatch;
                                $targetFm->fees_group_id = $bfm->fees_group_id;
                                $targetFm->amount = $bfm->amount;
                                $targetFm->nri = $bfm->nri;
                                $targetFm->management = $bfm->management;
                                $targetFm->govt = $bfm->govt;
                                $targetFm->installment_due_date = $bfm->installment_due_date;
                                $targetFm->installment_fine = $bfm->installment_fine;
                                $targetFm->installment_month = $bfm->installment_month;
                                $targetFm->editable = $bfm->editable;
                                $targetFm->fees_refund = $bfm->fees_refund;
                                $targetFm->save();
                            }

                            $resolvedBaseMasters->push($targetFm);
                        }

                        // Pre-resolve Previous Dues group and master once for the target class/session
                        $previousDuesGroup = \App\Models\FeesGroup::where(function($q) use ($branchId) {
                                $q->where('branch_id', $branchId)->orWhereNull('branch_id')->orWhere('id', 1);
                            })
                            ->whereRaw('LOWER(TRIM(name)) = ?', ['previous dues'])
                            ->whereNull('deleted_at')
                            ->first();

                        if(!$previousDuesGroup){
                            $previousDuesGroup = \App\Models\FeesGroup::whereRaw('LOWER(TRIM(name)) = ?', ['previous dues'])
                                ->whereNull('deleted_at')
                                ->first();
                        }

                        $previousDuesMaster = null;
                        if($previousDuesGroup){
                            $previousDuesMaster = FeesMaster::where('branch_id', $branchId)
                                ->where('session_id', $request->session_id)
                                ->where('class_type_id', $request->promote_class_type_id)
                                ->where('fees_group_id', $previousDuesGroup->id)
                                ->whereNull('deleted_at')
                                ->first();

                            if(!$previousDuesMaster){
                                $previousDuesMaster = new FeesMaster();
                                $previousDuesMaster->user_id = Session::get('id');
                                $previousDuesMaster->branch_id = $branchId;
                                $previousDuesMaster->session_id = $request->session_id;
                                $previousDuesMaster->class_type_id = $request->promote_class_type_id;
                                $previousDuesMaster->course_id = $targetCourseId;
                                $previousDuesMaster->batch = $firstBatch;
                                $previousDuesMaster->fees_group_id = $previousDuesGroup->id;
                                $previousDuesMaster->amount = 0;
                                $previousDuesMaster->nri = 0;
                                $previousDuesMaster->management = 0;
                                $previousDuesMaster->govt = 0;
                                $previousDuesMaster->editable = 1;
                                $previousDuesMaster->fees_refund = 'no';
                                $previousDuesMaster->save();
                            }
                        }

                        DB::beginTransaction();
                        try {
                            foreach($request->admission_ids as $key => $item){
                                $candidate = Admission::find($item);
                                if(!$candidate) continue;
                                $continuePreviousDiscount = in_array((int)$candidate->id, $continueDiscountAdmissionIds, true);
                                $student_name = $candidate->first_name;
                                $father_name = $candidate->father_name;
                                $mobile = $candidate->mobile; 
                                $admissionNo = $candidate->admissionNo ?? ''; 
                                $alreadyPromotedQuery = Admission::where('class_type_id', $request->promote_class_type_id)
                                    ->where('session_id', $request->session_id);
                                if(!empty($candidate->unique_system_id)){
                                    $alreadyPromotedQuery->where('unique_system_id', $candidate->unique_system_id);
                                } else {
                                    $alreadyPromotedQuery->where('admissionNo', $admissionNo)
                                        ->where('first_name', $student_name)
                                        ->where('father_name', $father_name)
                                        ->where('mobile', $mobile);
                                }
                                $isAlreadyPromoted = $alreadyPromotedQuery->first();
                                if(empty($isAlreadyPromoted)){
                                    $BillCounter = BillCounter::where('type', 'StudentAdmission')
                                    ->where('session_id', $request->session_id)->first();
                                    if(!empty($BillCounter)){
                                        $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                                        $BillCounter->counter = $counter + 1;
                                        $BillCounter->save();
                                    }
                                    $oldRow = Admission::find($item);
                                    $newRow = $oldRow->replicate();
                                    $newRow->session_id = $request->session_id;
                                    $newRow->admission_date = $request->date;
                                    $newRow->class_type_id = $request->promote_class_type_id;
                                    if(!empty($targetCourse)){
                                        $newRow->course = $targetCourse;
                                    }
                                    if(!empty($request->promote_batch)){
                                        $newRow->batch = $request->promote_batch;
                                    }
                                    $newRow->save();

                                    // Calculate previous semester balance (Net - Paid).
                                    // If positive, student has pending dues.
                                    // If negative, student has excess payment/advance credit (to be shown as negative Previous Dues head).
                                    $oldFeeDetails = FeesAssignDetail::where('admission_id', $oldRow->id)
                                        ->whereNull('deleted_at')
                                        ->get();
                                    $previousSemesterDiscount = $oldFeeDetails->sum(function ($detail) {
                                        return max(0, (float)$detail->discount);
                                    });
                                    $oldPaidByGroup = FeesDetail::where('admission_id', $oldRow->id)
                                        ->whereIn('status', [0, 1])
                                        ->whereNull('deleted_at')
                                        ->groupBy('fees_group_id')
                                        ->select('fees_group_id', DB::raw('SUM(paid_amount) as paid_amount'))
                                        ->pluck('paid_amount', 'fees_group_id');

                                    $oldTotalAssigned = (float)$oldFeeDetails->sum('fees_group_amount');
                                    $oldTotalDiscount = (float)$oldFeeDetails->sum('discount');
                                    $oldTotalCarryAdjustment = (float)$oldFeeDetails->sum('promotion_carry_adjustment');
                                    $oldTotalPaid = (float)FeesDetail::where('admission_id', $oldRow->id)
                                        ->whereIn('status', [0, 1])
                                        ->whereNull('deleted_at')
                                        ->sum('paid_amount');

                                    $previousDue = round(($oldTotalAssigned - $oldTotalDiscount - $oldTotalCarryAdjustment) - $oldTotalPaid, 2);

                                    $feesMasters = collect($resolvedBaseMasters);
                                    if($previousDue != 0 && !empty($previousDuesMaster)){
                                        $feesMasters->push($previousDuesMaster);
                                    }

                                    if($feesMasters->isNotEmpty()){
                                        $feesAssign = FeesAssign::firstOrCreate(
                                            ['admission_id' => $newRow->id],
                                            [
                                                'user_id' => Session::get('id'),
                                                'session_id' => $request->session_id,
                                                'branch_id' => $newRow->branch_id,
                                                'total_amount' => 0,
                                                'total_discount' => 0,
                                                'net_amount' => 0,
                                            ]
                                        );

                                        foreach($feesMasters as $feesMaster){
                                            $amount = $feesMaster->amount ?: 0;

                                            if(strtolower(trim(optional($feesMaster->feesGroup)->name ?? '')) === 'previous dues'){
                                                $amount = $previousDue;
                                            } else {
                                                $genderId = (int)($newRow->gender_id ?? 0);
                                                if ($genderId === 1 && !empty($feesMaster->boys_amount)) {
                                                    $amount = (float)$feesMaster->boys_amount;
                                                } elseif ($genderId === 2 && !empty($feesMaster->girls_amount)) {
                                                    $amount = (float)$feesMaster->girls_amount;
                                                }
                                            }

                                            FeesAssignDetail::updateOrCreate(
                                                [
                                                    'fees_assign_id' => $feesAssign->id,
                                                    'fees_master_id' => $feesMaster->id,
                                                    'admission_id' => $newRow->id,
                                                ],
                                                [
                                                    'user_id' => Session::get('id'),
                                                    'branch_id' => $newRow->branch_id,
                                                    'session_id' => $request->session_id,
                                                    'fees_group_amount' => floatval($amount),
                                                    'class_type_id' => $feesMaster->class_type_id,
                                                    'fees_group_id' => $feesMaster->fees_group_id,
                                                    'fees_refund' => optional($feesMaster->feesGroup)->fees_refund ?: 'no',
                                                    'installment_month' => $feesMaster->installment_month,
                                                    'installment_fine' => $feesMaster->installment_fine,
                                                    'installment_due_date' => $feesMaster->installment_due_date,
                                                ]
                                            );
                                        }

                                        // Optionally continue the genuine academic discount into
                                        // the promoted semester. Never apply it to Previous Dues.
                                        if($continuePreviousDiscount && $previousSemesterDiscount > 0){
                                            $remainingDiscount = $previousSemesterDiscount;
                                            $targetDetails = FeesAssignDetail::with('feesGroup')
                                                ->where('fees_assign_id', $feesAssign->id)
                                                ->whereNull('deleted_at')
                                                ->orderBy('id')
                                                ->get();
                                            foreach($targetDetails as $targetDetail){
                                                if($remainingDiscount <= 0) break;
                                                if(strtolower(trim(optional($targetDetail->feesGroup)->name ?? '')) === 'previous dues') continue;
                                                $capacity = max(0, (float)$targetDetail->fees_group_amount - (float)$targetDetail->discount);
                                                if($capacity <= 0) continue;
                                                $appliedDiscount = min($capacity, $remainingDiscount);
                                                $targetDetail->discount = (float)$targetDetail->discount + $appliedDiscount;
                                                $targetDetail->save();
                                                $remainingDiscount -= $appliedDiscount;
                                                $continuedDiscountTotal += $appliedDiscount;
                                            }
                                        }

                                        $total = FeesAssignDetail::where('fees_assign_id', $feesAssign->id)->whereNull('deleted_at')->sum('fees_group_amount');
                                        $discount = FeesAssignDetail::where('fees_assign_id', $feesAssign->id)->whereNull('deleted_at')->sum('discount');
                                        $feesAssign->total_amount = $total;
                                        $feesAssign->total_discount = $discount;
                                        $feesAssign->net_amount = $total - $discount;
                                        $feesAssign->save();

                                        if($previousDue < 0){
                                            $advanceRemark = "Previous semester excess/advance of ₹" . number_format(abs($previousDue), 2) . " carried forward as negative Previous Dues.";
                                            $feesAssign->remark = trim(($feesAssign->remark ? $feesAssign->remark . " | " : "") . $advanceRemark);
                                            $carriedAdvanceTotal += abs($previousDue);
                                        }
                                        $feesAssign->save();

                                        // Close the old semester's outstanding after it has been
                                        // successfully carried to the new Previous Dues head.
                                        $remainingCarry = $previousDue;
                                        if($remainingCarry > 0){
                                            foreach($oldFeeDetails as $oldDetail){
                                                if($remainingCarry <= 0) break;
                                                $paid = (float)($oldPaidByGroup[$oldDetail->fees_group_id] ?? 0);
                                                $headDue = max(0, (float)$oldDetail->fees_group_amount - (float)$oldDetail->discount - (float)($oldDetail->promotion_carry_adjustment ?? 0) - $paid);
                                                if($headDue <= 0) continue;
                                                $adjustment = min($headDue, $remainingCarry);
                                                $oldDetail->promotion_carry_adjustment = (float)($oldDetail->promotion_carry_adjustment ?? 0) + $adjustment;
                                                $oldDetail->save();
                                                $remainingCarry -= $adjustment;
                                            }

                                            $oldFeesAssign = FeesAssign::where('admission_id', $oldRow->id)->first();
                                            if($oldFeesAssign){
                                                $oldTotal = FeesAssignDetail::where('admission_id', $oldRow->id)->whereNull('deleted_at')->sum('fees_group_amount');
                                                $oldDiscount = FeesAssignDetail::where('admission_id', $oldRow->id)->whereNull('deleted_at')->sum('discount');
                                                $oldCarryAdjustment = FeesAssignDetail::where('admission_id', $oldRow->id)->whereNull('deleted_at')->sum('promotion_carry_adjustment');
                                                $oldFeesAssign->total_amount = $oldTotal;
                                                $oldFeesAssign->total_discount = $oldDiscount;
                                                $oldFeesAssign->net_amount = max(0, $oldTotal - $oldDiscount - $oldCarryAdjustment);
                                                $oldFeesAssign->save();
                                            }
                                            $carriedDueTotal += $previousDue;
                                        }
                                    }

                                    // A student can have several admission rows for history/fees,
                                    // but only the latest promoted class must remain active.
                                    $previousAdmissions = Admission::where('branch_id', $newRow->branch_id)
                                        ->where('id', '!=', $newRow->id)
                                        ->where('status', 1);
                                    if(!empty($newRow->unique_system_id)){
                                        $previousAdmissions->where('unique_system_id', $newRow->unique_system_id);
                                    } else {
                                        $previousAdmissions->where('admissionNo', $newRow->admissionNo)
                                            ->where('first_name', $newRow->first_name)
                                            ->where('father_name', $newRow->father_name)
                                            ->where('mobile', $newRow->mobile);
                                    }
                                    $previousAdmissions->update(['status' => 0]);
                                    $newRow->status = 1;
                                    $newRow->save();
                                    $promotedCount++;
                                } else {
                                    // Repair legacy promotions where both source and target rows
                                    // were left active.
                                    $otherActiveAdmissions = Admission::where('branch_id', $isAlreadyPromoted->branch_id)
                                        ->where('id', '!=', $isAlreadyPromoted->id)
                                        ->where('status', 1);
                                    if(!empty($isAlreadyPromoted->unique_system_id)){
                                        $otherActiveAdmissions->where('unique_system_id', $isAlreadyPromoted->unique_system_id);
                                    } else {
                                        $otherActiveAdmissions->where('admissionNo', $isAlreadyPromoted->admissionNo)
                                            ->where('first_name', $isAlreadyPromoted->first_name)
                                            ->where('father_name', $isAlreadyPromoted->father_name)
                                            ->where('mobile', $isAlreadyPromoted->mobile);
                                    }
                                    $otherActiveAdmissions->update(['status' => 0]);
                                    $isAlreadyPromoted->status = 1;
                                    $isAlreadyPromoted->save();
                                    $alreadyPromotedCount++;
                                }
                            }

                            // Commit the transaction only when all selected students are completely processed
                            DB::commit();

                            $message = $promotedCount . ' student(s) promoted successfully.';
                            if($alreadyPromotedCount > 0){
                                $message .= ' ' . $alreadyPromotedCount . ' student(s) were already in the selected semester.';
                            }
                            if($carriedDueTotal > 0){
                                $message .= ' Previous dues of ₹' . number_format($carriedDueTotal, 2) . ' carried forward and cleared from the previous semester.';
                            }
                            if($carriedAdvanceTotal > 0){
                                $message .= ' Previous advance credit of ₹' . number_format($carriedAdvanceTotal, 2) . ' carried forward as negative Previous Dues.';
                            }
                            if($continuedDiscountTotal > 0){
                                $message .= ' Previous academic discount of ₹' . number_format($continuedDiscountTotal, 2) . ' continued in the promoted semester.';
                            }

                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json([
                                    'status' => true,
                                    'message' => $message,
                                    'promoted_count' => $promotedCount,
                                    'already_promoted_count' => $alreadyPromotedCount,
                                    'carried_due_total' => $carriedDueTotal,
                                    'continued_discount_total' => $continuedDiscountTotal,
                                ]);
                            }

                            return redirect::to('student/promote_add')->with('message', $message);
                        } catch (\Exception $e) {
                            // Any error (timeout, exception, database failure) rolls back everything safely
                            DB::rollBack();
                            \Log::error('Student Promotion Failed - Rolled Back: ' . $e->getMessage(), [
                                'trace' => $e->getTraceAsString(),
                                'admission_ids' => $request->admission_ids,
                                'target_class' => $request->promote_class_type_id,
                            ]);

                            $errorMessage = 'Promotion failed and has been completely rolled back (no changes were made). Error: ' . $e->getMessage();
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json([
                                    'status' => false,
                                    'message' => $errorMessage,
                                    'error_detail' => $e->getMessage()
                                ], 500);
                            }

                            return redirect::back()->withInput()->with('error', $errorMessage);
                        }
                    } else {
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Please select at least one student to promote.'
                            ], 422);
                        }
                        return redirect::to('student/promote_add')->with('error', 'Please select at least one student to promote.');
                    }
                }
            }
            
            public function enquiry_qr_generate(){
                $file= QrCode::size(300)->generate('https://techvblogs.com/blog/generate-qr-code-laravel-8') ;
                return Response::download($file);
            }
}
