<?php
 
namespace App\Http\Controllers\fees;

use Illuminate\Validation\Validator;
use App\Models\Student;
use App\Models\ClassType;
use App\Models\Admission;
use App\Models\BillCounter;
use App\Models\SmsSetting;
use App\Models\Account;
use App\Models\FeesStructure;
use App\Models\FeesGroup;
use App\Models\FeesMaster;
use App\Models\FeesDiscount;
use App\Models\FeesCollect;
use App\Models\Sessions;
use App\Models\PermissionMessages;
use PDF;
use DB;
use App\Models\fees\FeesAdvance;
use App\Models\fees\FeesAdvanceHistory;
use App\Models\FeesDetail;
use App\Models\Invoice;
use App\Models\StoreItem;
use App\Models\StoreItemRequest;
use App\Models\StoreBillingDetail;
use App\Models\Master\MessageTemplate;
use App\Models\Master\MessageType;
use App\Models\Master\Branch;
use App\Models\Master\PaymentMode;
use App\Models\Setting;
use App\Models\fees\FeesAssign;
use App\Models\fees\FeesDetailsInvoices;
use App\Models\fees\FeesAssignDetail;
use Session;
use Helper;
use Hash;
use Str;
use Redirect;
use Response;
use Auth;
use File;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\WhatsappController;
use App\Services\NotificationService;

class FeesController extends Controller

{
    public function __construct(private NotificationService $notif) {}


    
    
            public function FeesGroupRemoveDuplicateEntries(){
                $feesAssignDuplicateEntries = FeesAssignDetail::get();
                $data = [];
                foreach($feesAssignDuplicateEntries as $item){
                    if (!isset($data[$item->admission_id])) {
                        $data[$item->admission_id] = [];
                    }
                    $data[$item->admission_id][] = ['group_id' => $item->fees_group_id, 'fees_master_id' => $item->fees_master_id];
                }
                $duplicates = [];
                foreach($data as $admission_id => $entries){
                    $group_ids = array_column($entries, 'group_id');
                    $counts = array_count_values($group_ids);
                    $repeated_group_ids = array_filter($counts, function($count) {
                        return $count > 1;
                    });
                    if (!empty($repeated_group_ids)) {
                        $duplicates[$admission_id] = $entries;
                    }
                }
                $add = [];
                foreach($duplicates as $key => $admissionIds){
                    $classArray = [];
                    foreach($admissionIds as $item){ 
                        $getClass = FeesMaster::where('id', $item['fees_master_id'])->first();
                        if (!empty($getClass)) {
                            if (!in_array($getClass->class_type_id, $classArray)) {
                                $classArray[] = $getClass->class_type_id;
                            }
                        }
                    }
                    $add[$key] = $classArray;
                }
                foreach($add as $key => $admissionIds){
                    $deleteClassGroups = []; 
                    for($i=0; $i < count($admissionIds)-1; $i++){
                        $deleteClassGroups[] = $admissionIds[$i]; 
                    }
                    $master = FeesMaster::whereIn('class_type_id',$deleteClassGroups)->pluck('id')->implode(',');
                    if(!empty($master)){
                        $master = explode(',',$master); 
                    }
                    $finalDelete = FeesAssignDetail::whereIn('fees_master_id',$master ?? [])->where('admission_id',$key)->delete();
                }
            }

            public function feeDashboard(){
                return view('fees/fee_dashboard');
            }
  
            public function addFees(Request $request){
                $serach['name'] = $request->name;
                $serach['admission_no'] = $request->admission_no;
                $serach['batch'] = $request->batch;
                $serach['course_id'] = $request->course_id ?? '';
                $serach['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                $serach['admission_type_id'] = $request->admission_type_id ?? '';
                $serach['class_type_id'] = !empty($request->class_type_id) ? $request->class_type_id : 0;
                $serach['unique_system_id'] = $request->unique_system_id ?? '';
                
                $hasFilter = !empty($request->class_type_id) 
                    || !empty($request->name) 
                    || !empty($request->admission_no) 
                    || !empty($request->batch) 
                    || !empty($request->course_id) 
                    || !empty($request->admission_type_id)
                    || !empty($request->unique_system_id)
                    || ($request->isMethod('post') && $request->has('session_id'));

                if ($request->isMethod('post') || $hasFilter) {
                    $value = $request->name;
                    if ($request->class_type_id > 0 || !empty($request->name) || !empty($request->admission_no) || !empty($request->batch) || !empty($request->course_id) || !empty($serach['session_id']) || !empty($request->unique_system_id)) {
                        $data =  Admission::with('ClassTypes')->where('status', 1)->where('school','=',1);
                        $data = $data->where('branch_id', Session::get('branch_id'));

                        if (!empty($serach['session_id'])) {
                            $data = $data->where('session_id', $serach['session_id']);
                        }
                        if (!empty($request->course_id)) {
                            $courseId = $request->course_id;
                            $courseObj = \App\Models\Master\Course::find($courseId);
                            $courseName = $courseObj ? $courseObj->name : null;
                            $data = $data->where(function($q) use ($courseId, $courseName) {
                                $q->whereHas('ClassTypes', function($q2) use ($courseId) {
                                    $q2->where('course_id', $courseId);
                                });
                                if ($courseName) {
                                    $q->orWhere('admissions.course', $courseName);
                                }
                            });
                        }
                       
                        if (!empty($request->name)) {
                            $data = $data->where(function ($query) use ($value) {
                                $query->where('first_name', 'like', '%' . $value . '%');
                                $query->orWhere('userName', 'like', '%' . $value . '%');
                                $query->orWhere('mobile', 'like', '%' . $value . '%');
                                $query->orWhere('aadhaar', 'like', '%' . $value . '%');
                                $query->orWhere('email', 'like', '%' . $value . '%');
                                $query->orWhere('father_name', 'like', '%' . $value . '%');
                                $query->orWhere('mother_name', 'like', '%' . $value . '%');
                                $query->orWhere('address', 'like', '%' . $value . '%');
                                $query->orWhere('admissionNo', 'like', '%' . $value . '%');
                            });
                        }
                        if (!empty($request->batch)) {
                            $data = $data->where("batch", $request->batch);
                        }
                        if (!empty($request->class_type_id)) {
                            $data = $data->where("class_type_id", $request->class_type_id);
                        }
                        if (!empty($request->admission_type_id)) {
                            $data = $data->where("admission_type_id", $request->admission_type_id);
                        }
                        if (!empty($request->admission_no)) {
                            $data = $data->where("admissions.admissionNo", $request->admission_no);
                        }
                        $allstudents = $data->orderBy('id', 'ASC')->get();

                        if (!empty($request->unique_system_id) && $allstudents->where('unique_system_id', $request->unique_system_id)->isEmpty()) {
                            $directStd = Admission::with('ClassTypes')->where('status', 1)->where('unique_system_id', $request->unique_system_id)->first();
                            if ($directStd) {
                                $allstudents->prepend($directStd);
                            }
                        }

                        return  view('fees.fees_collect.add', ['data' => $allstudents, 'serach' => $serach]);
                    } 
                    else {
                        return redirect::to('Fees/add')->with('error', 'Please type the input value  !');
                    }
                }
                return  view('fees.fees_collect.add', ['serach' => $serach]);
            }
                
            public function feesLedgerCollect(Request $request){
                $serach['name'] = $request->name;
                $serach['class_type_id'] = !empty($request->class_type_id) ? $request->class_type_id : 0;
                $serach['course_id'] = $request->course_id ?? '';
                $serach['batch'] = $request->batch ?? '';
                $serach['sr_no'] = !empty($request->sr_no) ? $request->sr_no : "";
                $serach['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');

                $srnoQuery = Admission::where('status', 1)->where('admission_type_id', 1)->where('branch_id', Session::get('branch_id'))->where('school','=',1)->whereNotNull('ledger_no');
                if (!empty($serach['session_id'])) {
                    $srnoQuery = $srnoQuery->where('session_id', $serach['session_id']);
                }
                $srno = $srnoQuery->orderBy('ledger_no')->get();

                if ($request->isMethod('post')) {
                    $request->validate([
                       // 'sr_no' => 'required',
                    ]);
                    $allstudents = Admission::with('ClassTypes')->where('branch_id', Session::get('branch_id'))->where('status', 1)->where('admission_type_id', 1);
                    if (!empty($serach['session_id'])) {
                        $allstudents = $allstudents->where('session_id', $serach['session_id']);
                    }
                    if (!empty($request->course_id)) {
                        $courseId = $request->course_id;
                        $courseObj = \App\Models\Master\Course::find($courseId);
                        $courseName = $courseObj ? $courseObj->name : null;
                        $allstudents = $allstudents->where(function($q) use ($courseId, $courseName) {
                            $q->whereHas('ClassTypes', function($q2) use ($courseId) {
                                $q2->where('course_id', $courseId);
                            });
                            if ($courseName) {
                                $q->orWhere('admissions.course', $courseName);
                            }
                        });
                    }
                    if (!empty($request->class_type_id)) {
                        $allstudents = $allstudents->where('class_type_id', $request->class_type_id);
                    }
                    if (!empty($request->batch)) {
                        $allstudents = $allstudents->where('batch', $request->batch);
                    }
                    if ($request->sr_no != '') {
                        $allstudents = $allstudents->where('ledger_no',$request->sr_no);
                    }
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $allstudents = $allstudents->where(function ($query) use ($value) {
                            $query->orWhere('ledger_no', 'like', '%' . $value . '%');
                            $query->orWhere('father_name', 'like', '%' . $value . '%');
                            $query->orWhere('admissionNo', 'like', '%' . $value . '%');
                            $query->orWhere('mobile', 'like', '%' . $value . '%');
                            $query->orWhere('first_name', 'like', '%' . $value . '%');
                            $query->orWhere('last_name', 'like', '%' . $value . '%');
                        });
                    }
                    $allstudents = $allstudents->whereNotNull('ledger_no')->get();
                    return  view('fees.fees_collect.byLedger', ['data' => $allstudents, 'serach' => $serach,'srno_post'=>$request->sr_no,'srno'=>$srno]);
                }
                return  view('fees.fees_collect.byLedger', ['serach' => $serach,'srno'=>$srno]);
            }

            public function feesGroup(Request $request){
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                $search['course_id'] = $request->course_id ?? '';
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['group_type'] = $request->group_type ?? '';
                $search['search_text'] = $request->search_text ?? '';

                $session_id = Session::get('session_id');
                $branch_id = Session::get('branch_id');
                $user_id = Session::get('id');

                if ($request->isMethod('post')) {
                    $formMode = $request->form_mode ?? 'semester';

                    // Case 1: Multiple Class / Semester Batch Assignment (with Amount & Due Date)
                    if ($formMode === 'semester' || ($request->has('class_type_id') && is_array($request->class_type_id) && $request->has('fee_name'))) {
                        $assignedCount = 0;
                        if ($request->has('class_type_id') && is_array($request->class_type_id)) {
                            foreach ($request->class_type_id as $k => $cTypeId) {
                                $cTypeId = (int)$cTypeId;
                                $fName = trim($request->fee_name[$k] ?? '');
                                $amt = floatval($request->amount[$k] ?? 0);
                                $dueDate = !empty($request->due_date[$k]) ? $request->due_date[$k] : null;
                                $groupType = $request->sem_group_type ?? $request->group_type ?? null;

                                if (!empty($cTypeId) && !empty($fName)) {
                                    // 1. Find or create FeesGroup
                                    $fg = FeesGroup::where('branch_id', $branch_id)
                                        ->where('session_id', $session_id)
                                        ->where('name', $fName)
                                        ->whereNull('deleted_at')
                                        ->first();
                                    if (!$fg) {
                                        $fg = new FeesGroup;
                                        $fg->user_id = $user_id;
                                        $fg->session_id = $session_id;
                                        $fg->branch_id = $branch_id;
                                        $fg->name = $fName;
                                        $fg->fees_refund = $request->fees_refund ?? 'no';
                                        $fg->fees_partial = $request->fees_partial ?? 0;
                                        $fg->group_type = $groupType;
                                        $fg->fees_type = 'full';
                                        $fg->save();
                                    }

                                    // 2. Create or update FeesMaster for this class
                                    $fm = \App\Models\FeesMaster::where('session_id', $session_id)
                                        ->where('branch_id', $branch_id)
                                        ->where('class_type_id', $cTypeId)
                                        ->where('fees_group_id', $fg->id)
                                        ->whereNull('deleted_at')
                                        ->first();
                                    if (!$fm) {
                                        $fm = new \App\Models\FeesMaster;
                                        $fm->user_id = $user_id;
                                        $fm->session_id = $session_id;
                                        $fm->branch_id = $branch_id;
                                        $fm->class_type_id = $cTypeId;
                                        $fm->fees_group_id = $fg->id;
                                    }
                                    $fm->amount = $amt;
                                    $fm->nri = $amt;
                                    $fm->management = $amt;
                                    $fm->govt = $amt;
                                    $fm->installment_due_date = $dueDate;
                                    $fm->editable = 0;
                                    $fm->save();
                                    $assignedCount++;
                                }
                            }
                        }
                        if ($assignedCount > 0) {
                            return redirect::to('feesGroup')->with('message', 'Fees Structure & Heads Saved Successfully for ' . $assignedCount . ' Classes/Semesters !');
                        }
                    }

                    // Case 2: Single Class Assignment
                    if ($formMode === 'single_class' || !empty($request->single_class_type_id)) {
                        $fName = trim($request->single_class_fee_name ?? $request->name ?? '');
                        $cTypeId = (int)$request->single_class_type_id;
                        $amt = floatval($request->single_amount ?? 0);
                        $dueDate = !empty($request->single_due_date) ? $request->single_due_date : null;
                        $groupType = $request->single_class_group_type ?? $request->group_type ?? null;

                        if (empty($cTypeId)) {
                            return redirect::to('feesGroup')->with('error', 'Please select a Class / Semester!');
                        }
                        if (empty($fName)) {
                            return redirect::to('feesGroup')->with('error', 'Please enter a valid Fee Head Name!');
                        }

                        $fg = FeesGroup::where('branch_id', $branch_id)
                            ->where('session_id', $session_id)
                            ->where('name', $fName)
                            ->whereNull('deleted_at')
                            ->first();
                        if (!$fg) {
                            $fg = new FeesGroup;
                            $fg->user_id = $user_id;
                            $fg->session_id = $session_id;
                            $fg->branch_id = $branch_id;
                            $fg->name = $fName;
                            $fg->fees_refund = $request->fees_refund ?? 'no';
                            $fg->fees_partial = $request->fees_partial ?? 0;
                            $fg->group_type = $groupType;
                            $fg->fees_type = 'full';
                            $fg->save();
                        }

                        if ($amt > 0 || !empty($dueDate)) {
                            $fm = \App\Models\FeesMaster::where('session_id', $session_id)
                                ->where('branch_id', $branch_id)
                                ->where('class_type_id', $cTypeId)
                                ->where('fees_group_id', $fg->id)
                                ->whereNull('deleted_at')
                                ->first();
                            if (!$fm) {
                                $fm = new \App\Models\FeesMaster;
                                $fm->user_id = $user_id;
                                $fm->session_id = $session_id;
                                $fm->branch_id = $branch_id;
                                $fm->class_type_id = $cTypeId;
                                $fm->fees_group_id = $fg->id;
                            }
                            $fm->amount = $amt;
                            $fm->nri = $amt;
                            $fm->management = $amt;
                            $fm->govt = $amt;
                            $fm->installment_due_date = $dueDate;
                            $fm->editable = 0;
                            $fm->save();
                        }
                        return redirect::to('feesGroup')->with('message', 'Fee Head & Class Fee Structure Saved Successfully !');
                    }

                    // Case 3: Fee Heads Batch / Single Creation
                    $fName = trim($request->head_only_name ?? $request->name ?? '');
                    $groupType = $request->head_only_group_type ?? $request->group_type ?? null;

                    if (empty($fName)) {
                        return redirect::to('feesGroup')->with('error', 'Please enter a valid Fee Head Name!');
                    }

                    $exists = FeesGroup::where('branch_id', $branch_id)
                                       ->where('session_id', $session_id)
                                       ->where('name', $fName)
                                       ->whereNull('deleted_at')
                                       ->first();
                    if (!$exists) {
                        $fees_group = new FeesGroup;
                        $fees_group->user_id = $user_id;
                        $fees_group->session_id = $session_id;
                        $fees_group->branch_id = $branch_id;
                        $fees_group->name = $fName;
                        $fees_group->fees_refund = $request->fees_refund ?? 'no';
                        $fees_group->fees_partial = $request->fees_partial ?? 0;
                        $fees_group->group_type = $groupType;
                        $fees_group->fees_type = 'full';
                        $fees_group->save();
                        return redirect::to('feesGroup')->with('message', 'Fees Head Added Successfully !');
                    } else {
                        return redirect::to('feesGroup')->with('error', 'Fee Head already exists !');
                    }
                }

                // Query for Fee Groups
                $query = FeesGroup::where('branch_id', Session::get('branch_id'));
                if (!empty($search['session_id']) && $search['session_id'] !== 'all') {
                    $query->where('session_id', $search['session_id']);
                }
                if (!empty($search['group_type'])) {
                    $query->where('group_type', $search['group_type']);
                }
                if (!empty($search['search_text'])) {
                    $query->where('name', 'like', '%' . $search['search_text'] . '%');
                }
                $fees_group_list = $query->orderBy('id', 'ASC')->get();

                // Query for Fees Master
                $feesMasterQuery = \App\Models\FeesMaster::with(['feesGroup', 'ClassTypes.course'])
                    ->where('branch_id', Session::get('branch_id'))
                    ->whereNull('deleted_at');
                if (!empty($search['session_id']) && $search['session_id'] !== 'all') {
                    $feesMasterQuery->where('session_id', $search['session_id']);
                } else {
                    $feesMasterQuery->where('session_id', Session::get('session_id'));
                }
                if (!empty($search['course_id'])) {
                    $course_id = $search['course_id'];
                    $feesMasterQuery->whereHas('ClassTypes', function($q) use ($course_id) {
                        $q->where('course_id', $course_id);
                    });
                }
                if (!empty($search['class_type_id'])) {
                    $feesMasterQuery->where('class_type_id', $search['class_type_id']);
                }

                $allFeesMasterRows = (clone $feesMasterQuery)->orderBy('class_type_id')->orderBy('id')->get();
                $fees_master_list = (clone $feesMasterQuery)->groupBy('class_type_id')->get();

                $courses = Helper::getCourses();
                $classType = Helper::classType();
                $allClassType = Helper::classType();
                $getFeesGroup = Helper::getFeesGroup();
                $getSession = Helper::getSession();

                return view('fees.fees.feesGroup', [
                    'dataview' => $fees_group_list,
                    'feesMasterList' => $fees_master_list,
                    'allFeesMasterRows' => $allFeesMasterRows,
                    'courses' => $courses,
                    'classType' => $classType,
                    'allClassType' => $allClassType,
                    'getFeesGroup' => $getFeesGroup,
                    'getSession' => $getSession,
                    'search' => $search
                ]);
            }

            public function feesGroupEdit(Request $request, $id){
                $data = FeesGroup::find($id);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name' => [
                            'required',
                            Rule::unique('fees_group')->ignore($id)->where(function ($query) use ($request) {
                                return $query->where('branch_id', Session::get('branch_id'))
                                             ->where('session_id', Session::get('session_id'))
                                             ->whereNull('deleted_at');
                            }),
                        ],
                    ]);
                    $data->user_id = Session::get('id');
                    $data->session_id = Session::get('session_id');
                    $data->branch_id = Session::get('branch_id');
                    $data->name = $request->name;
                    $data->fees_refund = $request->fees_refund ?? 'no';
                    $data->fees_partial = $request->fees_partial ?? 0;
                    $data->group_type = $request->group_type ?? null;
                    $data->fees_type = 'full';
                    $data->save();
                    return redirect::to('feesGroup')->with('message', 'Fees Group Updated Successfully !');
                }
                $fees_group_list = FeesGroup::where('session_id', Session::get('session_id'));
                if(Session::get('role_id') > 1){
                    $fees_group_list = $fees_group_list->where('branch_id', Session::get('branch_id'));
                }
                if (!empty(Session::get('admin_branch_id'))) {
                    $fees_group_list = $fees_group_list->where('branch_id', Session::get('admin_branch_id'));
                }
                $fees_group_list = $fees_group_list->orderBy('id', 'DESC')->get();
                return view('fees.fees.feesGroupEdit', ['data' => $data, 'dataview' => $fees_group_list]);
            }
            
            public function feesGroupDelete(Request $request){
                $id = $request->delete_id;
                $feesGroup = FeesGroup::find($id);
                if ($feesGroup) {
                    $session_id = Session::get('session_id');
                    $branch_id = Session::get('branch_id');
                    $isAssigned = \App\Models\fees\FeesAssignDetail::where('fees_group_id', $id)
                        ->where('session_id', $session_id)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->exists();
                    $isCollected = \App\Models\FeesDetail::where('fees_group_id', $id)
                        ->where('session_id', $session_id)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->where('paid_amount', '>', 0)
                        ->exists();

                    if ($isAssigned || $isCollected) {
                        $msg = 'Cannot delete this Fee Head because it has student assignments or fee collections!';
                        if ($request->ajax()) {
                            return response()->json(['status' => false, 'message' => $msg], 422);
                        }
                        return redirect()->back()->with('error', $msg);
                    }

                    $feesGroup->delete();
                    $msg = 'Fees Head Deleted Successfully !';
                    if ($request->ajax()) {
                        return response()->json(['status' => true, 'message' => $msg, 'deleted_id' => $id]);
                    }
                    return redirect()->back()->with('message', $msg);
                }
                $msg = 'Fee Head Not Found !';
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => $msg], 404);
                }
                return redirect()->back()->with('error', $msg);
            }

            public function assignFeeHeadToClasses(Request $request){
                $fees_group_id = $request->fees_group_id;
                $feesGroup = FeesGroup::find($fees_group_id);
                if (!$feesGroup) {
                    if ($request->ajax()) {
                        return response()->json(['status' => false, 'message' => 'Fee Head not found!'], 404);
                    }
                    return redirect()->back()->with('error', 'Fee Head not found!');
                }

                $session_id = Session::get('session_id');
                $branch_id = Session::get('branch_id');
                $user_id = Session::get('id');
                $assign_mode = $request->assign_mode ?? 'class';
                $assignedCount = 0;

                if ($assign_mode === 'course') {
                    $course_ids = $request->course_id ?? [];
                    if (empty($course_ids)) {
                        if ($request->ajax()) {
                            return response()->json(['status' => false, 'message' => 'Please select at least one Course to assign!'], 422);
                        }
                        return redirect()->back()->with('error', 'Please select at least one Course to assign!');
                    }

                    $courseCount = 0;
                    foreach ($course_ids as $courseId) {
                        $amt = (float)($request->course_amount[$courseId] ?? 0);
                        $dueDate = !empty($request->course_due_date[$courseId]) ? $request->course_due_date[$courseId] : null;

                        // Find the 1st (entry) class/semester for this course
                        $firstClass = ClassType::where('course_id', $courseId)
                            ->where(function($q) use ($session_id, $branch_id) {
                                if (!empty($session_id)) $q->where('session_id', $session_id);
                                if (!empty($branch_id)) $q->where('branch_id', $branch_id);
                            })
                            ->whereNull('deleted_at')
                            ->orderBy('orderBy', 'ASC')
                            ->orderBy('id', 'ASC')
                            ->first();

                        if ($firstClass) {
                            $fm = FeesMaster::where('session_id', $session_id)
                                ->where('branch_id', $branch_id)
                                ->where('fees_group_id', $fees_group_id)
                                ->where('class_type_id', $firstClass->id)
                                ->whereNull('deleted_at')
                                ->first();

                            if (!$fm) {
                                $fm = new FeesMaster;
                                $fm->user_id = $user_id;
                                $fm->session_id = $session_id;
                                $fm->branch_id = $branch_id;
                                $fm->fees_group_id = $fees_group_id;
                                $fm->class_type_id = $firstClass->id;
                            }
                            $fm->amount = $amt;
                            $fm->nri = $amt;
                            $fm->management = $amt;
                            $fm->govt = $amt;
                            $fm->installment_due_date = $dueDate;
                            $fm->editable = 0;
                            $fm->save();
                            $assignedCount++;
                        }
                        $courseCount++;
                    }

                    $msg = 'Fee Head "' . $feesGroup->name . '" successfully assigned to ' . $courseCount . ' Course(s)!';
                } else {
                    $class_type_ids = $request->class_type_id ?? [];
                    if (empty($class_type_ids)) {
                        if ($request->ajax()) {
                            return response()->json(['status' => false, 'message' => 'Please select at least one class / semester to assign!'], 422);
                        }
                        return redirect()->back()->with('error', 'Please select at least one class / semester to assign!');
                    }

                    foreach ($class_type_ids as $cId) {
                        $amt = (float)($request->amount[$cId] ?? 0);
                        $dueDate = !empty($request->due_date[$cId]) ? $request->due_date[$cId] : null;

                        $fm = FeesMaster::where('session_id', $session_id)
                            ->where('branch_id', $branch_id)
                            ->where('fees_group_id', $fees_group_id)
                            ->where('class_type_id', $cId)
                            ->whereNull('deleted_at')
                            ->first();

                        if (!$fm) {
                            $fm = new FeesMaster;
                            $fm->user_id = $user_id;
                            $fm->session_id = $session_id;
                            $fm->branch_id = $branch_id;
                            $fm->fees_group_id = $fees_group_id;
                            $fm->class_type_id = $cId;
                        }
                        $fm->amount = $amt;
                        $fm->nri = $amt;
                        $fm->management = $amt;
                        $fm->govt = $amt;
                        $fm->installment_due_date = $dueDate;
                        $fm->editable = 0;
                        $fm->save();
                        $assignedCount++;
                    }

                    $msg = 'Fee Head "' . $feesGroup->name . '" successfully assigned to ' . $assignedCount . ' class(es) in Fees Master!';
                }

                if ($request->ajax()) {
                    return response()->json(['status' => true, 'message' => $msg]);
                }
                return redirect('feesGroup')->with('message', $msg);
            }
 
            public function studentFeesOnclick(Request $request){
                //$this->FeesGroupRemoveDuplicateEntries();
                $BillCounter = BillCounter::where('type','FeesSlip')->get()->first();
                if (!empty($BillCounter)) {
                    $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                    $BillCounterNo = $counter + 1;
                }
                $sessionId = $request->session_id ?? Session::get('session_id');
                $checkStudent = Admission::where('session_id',$sessionId)->where('unique_system_id',$request->unique_system_id)->first();
                $admission_id = null;
                $data['stuData'] = [];
                if(!empty($checkStudent)){
                    $admission_id = $checkStudent->id;
                    $data['stuData'] =  Admission::find($admission_id);
                }
                else{
                    $data['stuData'] = array('first_name'=>'not_found_not_found','class_type_id'=>'not');
                }
                $data['session_id'] =  $sessionId;
                $data['BillCounter'] =  $BillCounterNo;
                $data['sessions'] = [];

                $Sessions_id = Admission::where('unique_system_id', $request->unique_system_id)->pluck('session_id'); 
                $sessions = Sessions::whereIn('id', $Sessions_id)->orderBy('id', 'DESC')->get();

                if(!empty($sessions)){
                    foreach($sessions as $item){
                        if($item->id <= Session::get('session_id')){
                            $data['sessions'][] = $item;
                        }
                    }
                }
                $data['FeesAssign'] =  FeesAssign::where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('admission_id',$admission_id)->first();
                $data['FeesCollect'] =  FeesCollect::where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('admission_id',$admission_id)->first();
                $data['FeesDetailsInvoices'] = FeesDetailsInvoices::where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('admission_id',$admission_id)->whereIn('status',[0,1])->orderBy('id','DESC')->get();
                $data['FeesMaster'] =  FeesMaster::where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('class_type_id', $data['stuData']['class_type_id'])->get();
                $data['stuFeeDet'] =  FeesDetail::with('PaymentMode')->with('Admission')->with('FeesCollect')->where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('admission_id', $admission_id)->where('fees_type',0)->orderBy('id','DESC')->get();
                $data['BillCounter'] = BillCounter::where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('type','FeesSlip')->get()->first();
                $data['inventory'] = StoreItemRequest::where('session_id',$sessionId)->where('branch_id',Session::get('branch_id'))->where('admission_id',$admission_id)->groupBy('receipt_no')->get();
                if (!empty($data['FeesAssign']->total_amount)) {
                    return view('fees.fees_collect.student_bill', ['data' => $data]);
                } else {
                    $data = 0;
                    echo $data;
                }
            }

 
            
            public function inventoryPaySubmit(Request $request){
                $enteredAmount = (Int)$request->get('enteredAmount');
                $collectedData = $request->get('collectedData');
                // Sort the array by 'pending' in ascending order
                usort($collectedData, function ($a, $b) {
                    return $a['pending'] - $b['pending'];
                });
                foreach ($collectedData as $item) {
                    $pending = (Int)($item['pending'] ?? 0);
                    $receipt = $item['receipt'];
                    $admissionId = $item['admission_id'];
                    if ($enteredAmount >= $pending) {
                        // Deduct the full pending amount and save to the database
                        $this->saveStoreReceipt($admissionId, $receipt, $pending);
                        $enteredAmount -= $pending;
                    }
                    else {
                        // Partial payment case, if enteredAmount is less than the pending amount
                        $this->saveStoreReceipt($admissionId, $receipt, $enteredAmount);
                        $enteredAmount = 0;
                        break; // Exit the loop as no more amount left to allocate
                    }
                }
            }
            function saveStoreReceipt($admissionId, $receipt, $amount) {
                if($amount > 0 ){
                    $pay = new StoreBillingDetail;
                    $pay->user_id = Session::get('id');
                    $pay->session_id =Session::get('session_id');
                    $pay->branch_id = Session::get('branch_id');
                    $pay->fees_counter_id = Session::get('counter_id');
                    $pay->admission_id = $admissionId;
                    $pay->receipt_no = $receipt;
                    $pay->amount = $amount;
                    $pay->date = date('Y-m-d');
                    $pay->save();
                }
            }

           public function studentPaySubmit(Request $request){
       
                $transactionStarted = false;
                $fees_details_invoice_id = '';
                $admission = null;
                try {
                    $cheque_image = '';
                    $session_id = $request->session_id ?? Session::get('session_id');
                    $FeesAssign = FeesAssign::where('admission_id',$request->admission_id)->get()->first();
                    $fees_details_id =[];
                    $slip = "";
                    if ($request->isMethod('post')) {
                        //dd($request);
                        $admission_id = $request->admission_id;
                        $admission = Admission::where('id',$admission_id)->first();
                        if (!empty($admission)) {
                            if (!empty($request->selected_head)) {
                                DB::beginTransaction();
                                $transactionStarted = true;

                                $BillCounter = BillCounter::where('session_id',$session_id)->where('branch_id',Session::get('branch_id'))->where('type', 'FeesSlip')->lockForUpdate()->first();
                                if (empty($BillCounter)) {
                                    throw new \Exception('Fees slip counter not found.');
                                }

                                $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                                $BillCounter->counter = $counter + 1;
                                $BillCounter->save();
                                foreach($request->selected_head as $key=> $head){
                                    if( ((int)$request->amount[$key]) != 0  || ((int)$request->discount_amount[$key]) != 0){
                                        $payOld = FeesCollect::where('admission_id',$admission_id)->first();
                                        if(!empty($payOld)){
                                            $pay = $payOld;
                                            $discount = FeesCollect::where('admission_id',$admission_id)->increment('discount', $request->discount_amount[$key] ?? 0);
                                            $amount = FeesCollect::where('admission_id', $admission_id)->increment('amount', $request->amount[$key] ?? 0);
                                            $payDetail = new FeesDetail; //model name
                                            $payDetail->user_id = Session::get('id');
                                            $payDetail->session_id = $session_id;
                                            $payDetail->branch_id = Session::get('branch_id');
                                            $payDetail->fees_collect_id = $payOld->id;
                                            $payDetail->fees_group_id = $head;
                                            $payDetail->receipt_no  = $request->slip_no;
                                            $payDetail->admission_id = $admission_id; 
                                            $payDetail->paid_amount = $request->amount[$key];
                                            $payDetail->installment_fine = $request->fine[$key];
                                            $payDetail->payment_mode_id = $request->payment_mode_id;
                                            $payDetail->discount = $request->discount_amount[$key];
                                            $payDetail->total_amount = $request->amount[$key]+$request->discount_amount[$key];
                                            $payDetail->status = $request->payment_status;
                                            $payDetail->date = $request->date;
                                            $payDetail->save();  
                                            $fees_details_id[]= $payDetail->id;
                                        }
                                        else{
                                            $pay = new FeesCollect;
                                            $pay->user_id = Session::get('id');
                                            $pay->session_id = $session_id;
                                            $pay->branch_id = Session::get('branch_id');
                                            $pay->admission_id = $request->admission_id;
                                            $pay->fees_assign_id = $FeesAssign->id;
                                            $pay->amount = $request->amount[$key];
                                            $pay->save();
                                            $collect_id = $pay->id;
                                            $payDetail = new FeesDetail; //model name
                                            $payDetail->user_id = Session::get('id');
                                            $payDetail->session_id = $session_id;
                                            $payDetail->branch_id = Session::get('branch_id');
                                            $payDetail->fees_collect_id = $collect_id;
                                            $payDetail->fees_group_id = $head;
                                            $payDetail->receipt_no  = $request->slip_no;
                                            $payDetail->admission_id = $admission_id;
                                            $payDetail->paid_amount = $request->amount[$key];
                                            $payDetail->installment_fine = $request->fine[$key];
                                            $payDetail->discount = $request->discount_amount[$key];
                                            $payDetail->total_amount = $request->amount[$key]+$request->discount_amount[$key];
                                            $payDetail->status = $request->payment_status;
                                            $payDetail->date = $request->date;
                                            $payDetail->payment_mode_id = $request->payment_mode_id;
                                            $payDetail->save();
                                            $fees_details_id[]= $payDetail->id;
                                        }
                                        
                                    }
                                }
                            }
                            if (empty($fees_details_id) && $transactionStarted) {
                                throw new \Exception('No valid fee amount selected.');
                            }
                            if(!empty($fees_details_id)){
                                $transaction_slip = '';
                                if ($request->file('payment_receipt')) {
                                    $image = $request->file('payment_receipt');
                                    $path = $image->getRealPath();
                                    $transaction_slip =$image->getClientOriginalName();
                                    $destinationPath = env('IMAGE_UPLOAD_PATH') . 'payment_receipt';
                                    $image->move($destinationPath, $transaction_slip);
                                }
                                $invoice = new FeesDetailsInvoices();
                                $invoice->user_id = Session::get('id');
                                $invoice->session_id = $session_id;
                                $invoice->branch_id = Session::get('branch_id');
                                $invoice->fees_counter_id = Session::get('fees_counter_id');
                                $invoice->admission_id = $admission_id;
                                $invoice->fees_details_id = implode(',',$fees_details_id );
                                $invoice->payment_date = $request->date; 
                                $invoice->payment_mode = $request->payment_mode_id;
                                $invoice->transaction_id = $request->transition_id;
                                $invoice->bank_name = $request->bank_name;
                                $invoice->invoice_no = $request->slip_no;
                                $invoice->status = $request->payment_status;
                                $invoice->cheque_number = $request->cheque_number;
                                $invoice->cheque_date = $request->cheque_date;
                                $invoice->payment_receipt = $transaction_slip;  
                                $invoice->amount = $request->total_amount;
                                $invoice->total_fine = $request->total_fine;
                                $invoice->discount = $request->discount_given;
                                $invoice->remark = $request->other_fee_remark;
                                $invoice->save();
                                $fees_details_invoice_id = $invoice->id;
                                $slip = $invoice->invoice_no;
                                $finalCollectedAmt = (float) ($invoice->amount ?? 0) + (float) ($invoice->total_fine ?? 0) + (float) ($invoice->discount ?? 0);
                                $receiptDate = !empty($invoice->payment_date) ? date('d-m-Y', strtotime($invoice->payment_date)) : date('d-m-Y');
                                
    
                                if ($request->advance_payment == 'yes') {
                                    $existingData = FeesAdvance::where('unique_system_id', $admission->unique_system_id)->first();
                                    $balance = 0;
                                    if (!empty($existingData)) {
                                        $balance = $existingData->balance - $request->total_amount;
                                        $existingData->balance = $balance ?? ''; 
                                        $existingData->save();
                                        $advancahistory = new FeesAdvanceHistory;
                                        $advancahistory->debit = $request->total_amount; 
                                        $advancahistory->user_id = Session::get('id'); 
                                        $advancahistory->session_id = Session::get('session_id'); 
                                        $advancahistory->unique_system_id = $admission->unique_system_id; 
                                        $advancahistory->branch_id = Session::get('branch_id'); 
                                        $advancahistory->date = $request->date; 
                                        $advancahistory->details = "Amount debited for this Receipt No . =" . $request->slip_no; 
                                        $advancahistory->fees_advance_id = $existingData->id; 
                                        $advancahistory->save();
                                    }
                                }

                            }
                        }
                        
                        if ($request->has('checkbox_whatsapp')) {

                            $branch = Branch::find(Session::get('branch_id'));
                            $setting = Setting::where('branch_id',Session::get('branch_id'))->first();
                            $payment_mode = PaymentMode::find($request->payment_mode_id);
                                                
                            if ($request->payment_status == 0) {

                                // ✅ 11. WhatsApp notification
                                if ($admission && $branch && $branch->whatsapp_srvc != 0 && !empty($admission->mobile)) {
                                    $studentName = trim($admission->first_name . ' ' . $admission->last_name);

                                    $template = MessageType::where('template_name','collect_fee')
                                                            ->where('status',1)
                                                            ->first();
                                    
                                    if($template){
                                        
                                        $receipt = url('/feesReceipt') . '/' . $invoice->invoice_no . '/' . $invoice->admission_id;
                                                    
                                                            // 👇 Human readable message (DB के लिए)
                                        $variables = [
                                            '{{1}}' => $studentName ?? '',
                                            '{{2}}' => number_format($finalCollectedAmt,2),
                                            '{{3}}' => $setting->name ?? '',
                                            '{{4}}' => "Mode: ".($payment_mode->name ?? '') . ", Date: ".$receiptDate,
                                            '{{5}}' => $setting->name ?? '',
                                            '{{6}}' => $receipt ?? ''
                                        ];
                                
                                        $content = str_replace(
                                            array_keys($variables),
                                            array_values($variables),
                                            $template->template_content
                                        );

                                        $params = [
                                            1 => $studentName ?? '',
                                            2 => number_format($finalCollectedAmt,2),
                                            3 => $setting->name ?? '',
                                            4 => "Mode: ".($payment_mode->name ?? '') . ", Date: ".$receiptDate,
                                            5 => $setting->name ?? '',
                                            6 => $receipt ?? '',
                                        ];
                                    
                                        $this->notif->enqueue([
                                            'user_id'    => Session::get('id'),
                                            'branch_id'  => Session::get('branch_id'),
                                            'session_id' => Session::get('session_id'),
                                            'recipient'  => $admission->mobile,
                                            'message'    => $content,
                                            'media_link' => $receipt,
                                            'event_type'  => 'fees_payment_successful',
                                            'template_name' => 'collect_fee',
                                            'channel'     => 'whatsapp',
                                            'params'        => $params,
                                        ]);

                                    }
                                }

                            }
                        }

                        if ($transactionStarted) {
                            DB::commit();
                            $transactionStarted = false;
                        }    
                        
                    }
                    $response = $this->callAction('printFeesInvoice', [ 
                        'request' => new Request([
                            'fees_details_invoice_id' => $fees_details_invoice_id,
                        ])
                    ]);
                    return Response::json(array('status' => 'success','unique_system_id'=>$admission->unique_system_id,'session_id' => $admission->session_id,'slip'=>$slip,'fees_details_invoice_id'=>$fees_details_invoice_id)); 
                } catch (\Throwable $e) {
                    if ($transactionStarted) {
                        DB::rollBack();
                    }

                    return Response::json(array('status' => false, 'error' => 'Error in collecting fees'), 500);
                }
            }
            
            public function feesReceipt($receipt_no, $admission_id = null){
                
            
                $explode = [];
                if(!empty($receipt_no)){
                    $invoiceQuery = FeesDetailsInvoices::where('invoice_no', $receipt_no);
                    if (!empty($admission_id)) {
                        $invoiceQuery->where('admission_id', $admission_id);
                    }
                    elseif ((clone $invoiceQuery)->count() !== 1) {
                        abort(404);
                    }

                    $invoiceId = $invoiceQuery->value('id');
                    if (empty($invoiceId)) {
                        abort(404);
                    }

                    $invoice_data =  FeesDetailsInvoices::select('fees_details_invoices.*','admissions.first_name','admissions.batch','admissions.course','admissions.student_type','users.email as user_email',
                    'admissions.last_name','admissions.category','class_types.name as class_name','gender.name as gender_name','class_types.id as class_type_id','admissions.father_name',
                    'admissions.admissionNo','payment_modes.name as payment_mode','payment_modes.id as payment_mode_id')
                    ->leftjoin('admissions as admissions', 'admissions.id', 'fees_details_invoices.admission_id')
                    ->leftjoin('users as users', 'users.id', 'fees_details_invoices.user_id')
                    ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                    ->leftjoin('gender','gender.id','admissions.gender_id')
                    ->leftjoin('payment_modes','payment_modes.id','fees_details_invoices.payment_mode')
                    ->where('fees_details_invoices.id',$invoiceId)->first();
                    if (empty($invoice_data)) {
                        abort(404);
                    }
                  
                    $explode = explode(',',$invoice_data->fees_details_id);
                    $fess_print = FeesDetail::select('fees_detail.*','payment_modes.name as payment_mode','fees_group.name as fees_group_name')
                        ->leftJoin('payment_modes','payment_modes.id','fees_detail.payment_mode_id')
                        ->leftJoin('fees_collect','fees_collect.id','fees_detail.fees_collect_id')
                        ->leftJoin('fees_group','fees_group.id','fees_detail.fees_group_id')
                        ->whereIn('fees_detail.id',$explode);
                        $fess_print=$fess_print->get();
                        
                    $printPreview = Helper::printPreview('Fees Collect');
                    
                    return view($printPreview, ['data'=>$fess_print,'invoice_data'=>$invoice_data]);
                } 
                abort(404);
            }
            
            
            
 
            public function viewFees(Request $request){
                $serach['name'] = $request->name;
                $serach['class_type_id'] = $request->class_type_id;
                $serach['course_id'] = $request->course_id;
                $serach['batch'] = $request->batch;
                $serach['starting'] = $request->starting;
                $serach['ending'] = $request->ending;
                $serach['user_id'] = $request->user_id;
                $serach['admission_no'] = $request->admission_no;
                $serach['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');

                $data =  FeesDetailsInvoices::select('fees_details_invoices.*','class.name as class_name','admissions.admissionNo','admissions.first_name'
                ,'admissions.last_name','users.first_name as users_first_name'
                ,'users.last_name as users_last_name','admissions.father_name','admissions.school','payment_modes.name as payment_mode','payment_modes.id as payment_mode_id')
                ->leftjoin('admissions as admissions', 'admissions.id', 'fees_details_invoices.admission_id')
                ->leftjoin('class_types as class','class.id','admissions.class_type_id')
                ->leftjoin('payment_modes','payment_modes.id','fees_details_invoices.payment_mode')
                ->leftjoin('users','users.id','fees_details_invoices.user_id')
                ->where('fees_details_invoices.branch_id', Session::get('branch_id'))
                ->whereIn('fees_details_invoices.status',[0,1]);

                if (!empty($serach['session_id'])) {
                    $data = $data->where('fees_details_invoices.session_id', $serach['session_id']);
                }
                if (!empty($request->course_id)) {
                    $courseId = $request->course_id;
                    $courseObj = \App\Models\Master\Course::find($courseId);
                    $courseName = $courseObj ? $courseObj->name : null;
                    $data = $data->where(function($q) use ($courseId, $courseName) {
                        $q->where('class.course_id', $courseId);
                        if ($courseName) {
                            $q->orWhere('admissions.course', $courseName);
                        }
                    });
                }
                if (!empty($request->batch)) {
                    $data = $data->where('admissions.batch', $request->batch);
                }
                if (!empty($request->class_type_id)) {
                    $data = $data->where("admissions.class_type_id", $request->class_type_id);
                }
                if (!empty($request->admission_no)) {
                    $data = $data->where("admissions.admissionNo", $request->admission_no);
                }

                if ($request->isMethod('post')) {
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $data = $data->where(function($q) use ($value) {
                            $q->where('admissions.first_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.last_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.father_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.mother_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.admissionNo', $value)
                            ->orWhere('admissions.mobile', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.aadhaar', $value)
                            ->orWhere('admissions.email', 'LIKE', '%' . $value . '%');
                        });
                    }
                    if (!empty($request->starting)) {
                        $data = $data->whereBetween('fees_details_invoices.payment_date', [$request->starting, $request->ending]);
                    }
                    if (!empty($request->user_id)) {
                        $data = $data->where("fees_details_invoices.user_id", $request->user_id);
                    }
                }
                else{
                    $data = $data->whereBetween('fees_details_invoices.payment_date', [date('Y-m-d'), date('Y-m-d')]);
                    $serach['starting'] = date('Y-m-d');
                    $serach['ending'] = date('Y-m-d');
                }
                $data = $data->where('admissions.school','=',1)->orderBy('fees_details_invoices.id', 'DESC')->get();
                
                return view('fees.fees_collect.index', ['data' => $data, 'serach' => $serach]);
            }
    
            public function AssignFeesEdit(Request $request,$id){
                $data = FeesAssignDetail::select('fees_assign_details.*','fees_group.id as feesGroupId')
                ->leftJoin('fees_group','fees_group.id','fees_assign_details.fees_group_id')
                ->where('fees_assign_details.admission_id',$id)
                ->get();
                $feesAssign = FeesAssign::where('admission_id',$id)->first(); 
                if ($request->isMethod('post')) {
                    $feesAssign->emi_check = $request->emi_check;
                    $feesAssign->save();
                    for($i=0; $i < count($request->fees_group_id); $i++ ){
                        $values = FeesAssignDetail::where('fees_assign_id',$request->fees_assign_id[$i])
                        ->where('fees_master_id',$request->fees_master_id[$i])
                        ->where('fees_group_id',$request->fees_group_id[$i])
                        ->where('admission_id',$id)
                        ->first();
                        if(!empty($values)){
                            $values->fees_group_amount = $request->amount[$i];
                            $values->save();
                        }
                        else{
                            $values = new FeesAssignDetail;
                            $values->user_id = Session::get('id');
                            $values->branch_id = Session::get('branch_id');
                            $values->session_id = Session::get('session_id');
                            $values->fees_group_amount = $request->amount[$i];
                            $values->admission_id = $request->admission_id[$i];
                            $values->fees_assign_id = $request->fees_assign_id[$i];
                            $values->fees_master_id = $request->fees_master_id[$i];
                            $values->fees_group_id = $request->fees_group_id[$i];
                            $values->save();
                        }
                    }
                    return redirect::to('student_assign_fees')->with('message', 'Assign Fees Update Successfully.');
                }
                return view('fees.assign_fees_student.edit',['data'=>$data,'feesAssign'=>$feesAssign]);
            }


            public function getFeesDetail(Request $request){
                $admission_id = $request->admission_id;
                $fees = FeesCollect::with('Student')->with('ClassTypes')->with('PaymentMode')->orderBy('id', 'DESC')->groupBy('admission_id')->get();
                $feesDetail = FeesDetail::where('admission_id', $admission_id)->with('FeesType')->orderBy('id', 'DESC')->get();
                $html = "";
                $name = "n";
                $count = 1;
                foreach ($feesDetail   as $key => $item) {
                    $html .= '<tr><td>' . $count++ . '</td><td>' . $item['FeesType']['name'] . '<input type="hidden" name="fees_type_id[]" value="' . $item['fees_type_id'] . '"></td><td title="Click on the amount for edit"><span id="' . $name . $count . '" class="editable">' . $item['amount'] . '</span></td>
                    <td><a href="" class="btn btn-primary  btn-xs ml-3"><i class="fa fa-edit"></i></a></td></tr>';
                    // return view('fees.fees_collect.index',['data'=>$fees,'dataview'=>$feesDetail]);
                }
                echo $html;
            }
  
            public function printPayement($id){
                $explode = explode(',',$id);
                $fess_print = FeesDetail::select('fees_detail.*','admissions.first_name','fees_group.name as fees_group_name','admissions.last_name','class_types.name as class_name','admissions.father_name','admissions.admissionNo','payment_modes.name as payment_mode')
                ->leftJoin('admissions','admissions.id','fees_detail.admission_id')
                ->leftJoin('payment_modes','payment_modes.id','fees_detail.payment_mode_id')
                ->leftJoin('fees_collect','fees_collect.id','fees_detail.fees_collect_id')
                ->leftJoin('fees_group','fees_group.id','fees_detail.fees_group_id')
                ->leftJoin('class_types','class_types.id','admissions.class_type_id')
                ->whereIn('fees_detail.id',$explode)->get();
                
                //dd($fess_print);
                $printPreview = Helper::printPreview('Fees Collect');
                // dd($printPreview);
                return view($printPreview, ['data' => $fess_print]);
                // return view('print_file.student_print.print_fees', ['data' => $fess_print]);
            }
    
            public function printFeesInvoice(Request $request){
                
                $explode = [];
                if(!empty($request->fees_details_invoice_id)){
                    $invoice_data =  FeesDetailsInvoices::select('fees_details_invoices.*','admissions.first_name','admissions.batch','admissions.course','admissions.student_type','users.email as user_email',
                    'admissions.last_name','admissions.category','class_types.name as class_name','gender.name as gender_name','class_types.id as class_type_id','admissions.father_name',
                    'admissions.admissionNo','payment_modes.name as payment_mode','payment_modes.id as payment_mode_id')
                    ->leftjoin('admissions as admissions', 'admissions.id', 'fees_details_invoices.admission_id')
                    ->leftjoin('users as users', 'users.id', 'fees_details_invoices.user_id')
                    ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                    ->leftjoin('gender','gender.id','admissions.gender_id')
                    ->leftjoin('payment_modes','payment_modes.id','fees_details_invoices.payment_mode')
                    ->where('fees_details_invoices.branch_id', Session::get('branch_id'))
                    ->where('fees_details_invoices.id',$request->fees_details_invoice_id)->first();
                  //dd($invoice_data);
                    $explode = explode(',',$invoice_data->fees_details_id);
                    $fess_print = FeesDetail::select('fees_detail.*','payment_modes.name as payment_mode','fees_group.name as fees_group_name')
                        ->leftJoin('payment_modes','payment_modes.id','fees_detail.payment_mode_id')
                        ->leftJoin('fees_collect','fees_collect.id','fees_detail.fees_collect_id')
                        ->leftJoin('fees_group','fees_group.id','fees_detail.fees_group_id')
                        ->whereIn('fees_detail.id',$explode);
                        $fess_print=$fess_print->get();
                        
                    $printPreview = Helper::printPreview('Fees Collect');
                    // dd($printPreview);
                    return view($printPreview, ['data'=>$fess_print,'invoice_data'=>$invoice_data]);
                } 
                else{            
                    return redirect::to('fee_dashboard');
                }
                //dd($fess_print);
            }
    
            public function printPayementGenerate($id){
                $fess_print = FeesDetail::with('Admission')->with('PaymentMode')->with('FeesCollect')->with('ClassTypes')->find($id);
                //dd($fess_print);
                $printPreview =    Helper::printPreview('Fees Collect');
                //dd($printPreview);
                $randomString = Str::random(10);
                $pdf = PDF::loadView($printPreview, ['data' => $fess_print]);
                file_put_contents(env('IMAGE_UPLOAD_PATH'). 'feesPaymentPdf' . '/' .$randomString.$fess_print->receipt_no . '.pdf', $pdf->output());
                $file_url = env('IMAGE_SHOW_PATH') . 'feesPaymentPdf' . '/' .$randomString.$fess_print->receipt_no . '.pdf';  
                FeesDetail::where('id',$id)->update(['fees_pdf_name' => $file_url]);
                return redirect::to('fees/index')->with('message', 'PDF Generated Successfully !');
                // return view($printPreview, ['data' => $fess_print]);
                // return view('print_file.student_print.print_fees', ['data' => $fess_print]);
            }

            public function collectFeesDelete(Request $request){
                $admissionId = $request->admission_id ;
                $fee_invoice_id = $request->fees_invoice_id;
                $data = Admission::where('id',$admissionId)->first();
                $FeesDetailsInvoices = FeesDetailsInvoices::where('session_id',$request->session_id)->where('id',$fee_invoice_id)->first();
                if(!empty($FeesDetailsInvoices)){
                    $explode = explode(',', $FeesDetailsInvoices->fees_details_id);
                    $fees_detail_ids = FeesDetail::whereIn('id',$explode)->update(['status'=>2]);
                    FeesDetailsInvoices::where('session_id',$request->session_id)->where('id',$fee_invoice_id)->update(['status'=>2]);
                    $total_collected = FeesDetail::where('admission_id',$admissionId)->whereIn('status',[0,1])->sum('total_amount');
                    $fees_collect = FeesCollect::where('admission_id',$admissionId)->first();
                    $fees_collect->amount= $total_collected;
                    $fees_collect->save();
                }
                return Response::json(array('status' => 'success','unique_system_id'=>$data->unique_system_id,'session_id' => $data->session_id)); 
            }
  
            public function feesSearchData(Request $request){
                $name = $request->post('name');
                $class_type_id = $request->get('class_type_id');
                $fees_type_id = $request->get('fees_type_id');
                $data =  FeesCollect::with('Student')->with('PaymentMode');
                if (!empty($name)) {
                    $data = $data->where("student_name", $name);
                }
                if (!empty($class_type_id)) {
                    $data = $data->where("class_type_id", $class_type_id);
                }
                $allfees = $data->orderBy('id', 'DESC')->get();
                return  view('fees.fees_collect.fees_search_data', ['data' => $allfees]);
            }

       
            public function feesMasterData(Request $request){
                $data =  FeesMaster::find($request->fees_master_id);
                $paidAmount =  FeesDetail::where('class_type_id', $request->class_type_id)->where('fees_type_id', $data['fees_type_id'])->sum('total_amount');
                //dd($data);
                if ($paidAmount > 0) {
                    $net_amount =  $data['amount'] - $paidAmount;
                } 
                else {
                    $net_amount = $data['amount'];
                }
                echo json_encode($net_amount);
            }

            public function ledgerSave(Request $request){
                if(!empty($request->admission_id)){
                    foreach($request->admission_id as $key => $ids)
                {
                $find = Admission::find($ids);
                    $find->ledger_no = $request->ledger_number[0] ?? null; 
                        $find->save();
                    }
                    return redirect::to('ledger_update')->with('message', 'Ledger Number Updated Successfully');
                }
            }
            
            public function ledgerUpdate(Request $request){
                $serach['name'] = $request->name;
                $serach['class_type_id'] = !empty($request->class_type_id) ? $request->class_type_id : 0;
                if ($request->isMethod('post')) {
                    $value = $request->name;
                    $data = Admission::with('ClassTypes')->where('status', 1)->where('admission_type_id', 1)->where('session_id', Session::get('session_id'))->where('school','=',1);
                    if(Session::get('role_id') > 1){
                        $data = $data->where('branch_id', Session::get('branch_id'));
                    }
                    if (!empty(Session::get('admin_branch_id'))) {
                       $data = $data->where('branch_id', Session::get('admin_branch_id'));
                    }
                    if (!empty($request->name)) {
                        $data = $data->where(function ($query) use ($value) {
                            $query->where('first_name', 'like', '%' . $value . '%');
                            $query->orWhere('userName', 'like', '%' . $value . '%');
                            $query->orWhere('mobile', 'like', '%' . $value . '%');
                            $query->orWhere('aadhaar', 'like', '%' . $value . '%');
                            $query->orWhere('email', 'like', '%' . $value . '%');
                            $query->orWhere('father_name', 'like', '%' . $value . '%');
                            $query->orWhere('mother_name', 'like', '%' . $value . '%');
                            $query->orWhere('address', 'like', '%' . $value . '%');
                            $query->orWhere('admissionNo', 'like', '%' . $value . '%');
                        });
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where("class_type_id", $request->class_type_id);
                    }
                    $allstudents = $data->orderBy('id', 'DESC')->get();
                    return  view('fees.fees_collect.studentSearchList', ['data' => $allstudents]);
                }
                return view('fees.fees_collect.ledgerUpdate',['serach' => $serach]);
            }

            public function feesLedger(Request $request){
                $search['name'] = $request->name;
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course'] = $request->course ?? '';
                $search['starting'] = $request->starting;
                $search['ending'] = $request->ending;
                $search['status'] = $request->status;
                $search['batch'] = $request->batch;
                $search['admissionNo'] = $request->admissionNo;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');

                $data='';
                if ($request->isMethod('post')) {
                    $data = Admission::select('admissions.*','fees_assigns.total_amount','class_types.name as className','fees_assigns.total_discount as assign_discount','fees_collect.amount as collect_amount', 'fees_collect.discount')
                    ->leftJoin('fees_assigns as fees_assigns', 'fees_assigns.admission_id', 'admissions.id')
                    ->leftJoin('class_types', 'class_types.id', 'admissions.class_type_id')
                    ->leftJoin('fees_collect as fees_collect', 'fees_collect.admission_id', 'admissions.id')
                    ->where('admissions.admission_type_id',1)
                    ->where('admissions.school',1)
                    ->where('admissions.branch_id', Session::get('branch_id'))
                    ->groupBy('admissions.id');

                    if (!empty($search['session_id'])) {
                        $data = $data->where('admissions.session_id', $search['session_id']);
                    }
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $data = $data->where(function ($query) use ($value) {
                            $query->where("admissions.first_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.last_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.mobile", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.email", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.aadhaar", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.father_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.mother_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.address", 'like', '%' . $value . '%');
                        });
                    }
                    if ($request->admissionNo != '') {
                        $data = $data->where("admissions.admissionNo", $request->admissionNo);
                    }
                    if (!empty($request->course)) {
                        $courseName = $request->course;
                        $data = $data->where(function($q) use ($courseName) {
                            $q->where('admissions.course', $courseName)
                              ->orWhere('class_types.course_id', function($sub) use ($courseName) {
                                  $sub->select('id')->from('courses')->where('name', $courseName)->limit(1);
                              });
                        });
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where('admissions.class_type_id',$request->class_type_id);
                    }
                    if ($request->batch != '') {
                        $data = $data->where("admissions.batch", $request->batch);
                    }
                    if ($request->status != '') {
                        $data = $data->where("admissions.status", $request->status);
                    }else
                    {
                        $data = $data->where("admissions.status", 1);
                    }
                    if (Session::get('role_id') == 2) {
                        $data = $data->where('admissions.class_type_id', Session::get('class_type_id'));
                    } 
                    $data = $data->orderBy('admissions.id', 'DESC')->get();
                    if (isset($data[0]['class_type_id']) && empty($request->class_type_id)) {
                        $search['class_type_id'] = $data[0]['class_type_id'];
                    }
                }
                
                return view('fees.ledger.view', ['data' => $data, 'search' => $search]);
            }

            public function fees_ledger_view(Request $request) {
                $getFees = FeesAssignDetail::select('fees_assign_details.*', 'fees_group.name as group_name','admissions.first_name as admission_stu_name')
                    ->join('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                    ->join('admissions', 'admissions.id', '=', 'fees_assign_details.admission_id')
                    ->where('admission_id',$request->admission_id)
                    ->get();
               //     dd($getFees);
                
                $html = '';          
                //$html .= '<h1 class="d-none">' . ($getFees->first()->admission_stu_name ?? '') . '</h1>';
                $html .= '<h1 class="d-none admission-stu-name">' . ($getFees->first()->admission_stu_name ?? '') . '</h1>';
              
                $html .= '<table class="table">
                    <thead>
                    
                        <tr class="sky_tr">
                        
                            <th>#</th>
                            <th>Fees Type</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Fine</th>
                            <th>Paid</th>
                            <th style="text-align: right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>';
            
                if (!$getFees->isEmpty()) {
                    $i = 1;
                    $grand_total = 0;
                    $Paids = 0;
                    $Discount = 0;
                    $Fine = 0;
                    $balances = 0;
                    $fine_amt = 0;
            
                    foreach ($getFees as $item) {
                        $feesDetails = FeesDetail::where('fees_type', 0)
                                    ->whereIn('status', [0, 1])
                                    ->where('admission_id',$request->admission_id)
                                    ->where('fees_group_id', $item->fees_group_id)
                                    ->selectRaw('SUM(total_amount) as total_amount, SUM(discount) as total_discount, SUM(installment_fine) as installment_fine')
                                    ->first();
                            
                                $pad = $feesDetails->total_amount;
                                $discounts = $feesDetails->total_discount;
                                $fine_amt = $feesDetails->installment_fine;

            
                        $balance = $item->fees_group_amount-$item->discount - $pad;
                    
            
                        $html .= '<tr>
                            
                            <td>' . $i++ . '</td>
                            <td>' . ($item->group_name ?? '') . '</td>
                            <td>' . (!empty($item->installment_due_date) ? date('d-M-Y', strtotime($item->installment_due_date)) : '') . '</td>
                            <td>' . ($item->fees_group_amount > $pad ? '<span class="label1 label-danger-custom">Unpaid</span>' : '<span class="label1 label-success-custom">Total Paid</span>') . '</td>
                            <td>' . ($item->fees_group_amount-$item->discount ?? '0') . '</td>
                            <td>' . ($discounts ?? '0') . '</td>
                            <td>' . ($fine_amt ?? '0'). '</td>
                            <td>' . ($pad ?? '0') . '</td>
                            <td style="text-align: right;">' . ($balance ?? '') . '</td>
                        </tr>';
            
                        $grand_total += $item->fees_group_amount-$item->discount;
                        $Paids += $pad;
                        $Discount += $discounts;
                        $Fine += $fine_amt;
                        $balances += $balance;
                    }
            
                    $html .= '<tr>
                        <td colspan="12">
                            <div class="row">
                            <div class="col-6"></div>
                                <div class="col-6">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <th colspan="3" style="text-align: right; font-weight: normal;"><strong>Grand Total:</strong> ' . $grand_total . '</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" style="text-align: right; font-weight: normal;"><strong>Paid:</strong> ' . $Paids . '</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" style="text-align: right; font-weight: normal;"><strong>Discount:</strong> ' . $Discount . '</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" style="text-align: right; font-weight: normal;"><strong>Fine:</strong> ' . $Fine . '</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3" style="text-align: right; font-weight: normal;"><strong>Balance:</strong> ' . $balances . '</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>';
                } else {
                    $html .= '<tr class="text-center">
                        <td colspan="9"><b>!! NO DATA FOUND !!</b></td>
                    </tr>';
                }
            
                $html .= '</tbody></table>';
            
                return response()->json(['html' => $html]);
            }
    
            public function feesLedgerPrint($unique_system_id){ 
                
                $student =  Admission::select('admissions.*', 'sessions.from_year', 'class_types.name as class_name','sessions.to_year', 'gender.name as genderName')
                ->leftJoin('gender','gender.id','admissions.gender_id')
                ->leftjoin('sessions','sessions.id','admissions.session_id')
                ->leftjoin('class_types','class_types.id','admissions.class_type_id')->where('unique_system_id',$unique_system_id)->get();
           //  dd($student);
                return view('fees.ledger.fees_ledgr_print', ['student' => $student]);
            }
    
            public function getFeesGroup(Request $request){
                $data =  FeesMaster::Select('fees_master.*','groups.name as fees_group_name','class.name as class_name','session.from_year as from_year','session.to_year as to_year')
                ->leftjoin('fees_group as groups','groups.id','fees_master.fees_group_id')
                ->leftjoin('class_types as class','class.id','fees_master.class_type_id')
                ->leftjoin('sessions as session','session.id','fees_master.session_id')
                ->where('fees_master.class_type_id',$request->class_type_id)->where('fees_master.session_id', $request->session_id)->where('fees_master.branch_id', Session::get('branch_id'))->get();
             // dd($data);
                return Response::json(array('data' => $data)); 
            }
            public function feesRemainderCron(Request $request){
                $search['name'] = $request->name;
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course_id'] = $request->course_id ?? '';
                $search['status'] = $request->status;
                $search['batch'] = $request->batch;
                $search['admissionNo'] = $request->admissionNo;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                
                $session = !empty($search['session_id']) ? $search['session_id'] : Session::get('session_id');
                $branch_id = Session::get('branch_id');
                
                $studentArray=[];
                if ($request->isMethod('post')) {     
                // Start Query
                $admission_ids = Admission::select('admissions.*', 'class_types.name as class_name')
                    ->leftJoin('class_types', 'class_types.id', '=', 'admissions.class_type_id')
                    ->where('admissions.school', 1)
                    ->where('admissions.branch_id', $branch_id);
                
                if (!empty($search['session_id'])) {
                    $admission_ids = $admission_ids->where('admissions.session_id', $search['session_id']);
                }

                // Apply Search Filter
                if (!empty($request->name)) {
                    $value = $request->name;
                    $admission_ids->where(function ($query) use ($value) {
                        $query->where("admissions.first_name", 'like', '%' . $value . '%')
                              ->orWhere("admissions.last_name", 'like', '%' . $value . '%')
                              ->orWhere("admissions.mobile", 'like', '%' . $value . '%')
                              ->orWhere("admissions.email", 'like', '%' . $value . '%')
                              ->orWhere("admissions.aadhaar", 'like', '%' . $value . '%')
                              ->orWhere("admissions.father_name", 'like', '%' . $value . '%')
                              ->orWhere("admissions.mother_name", 'like', '%' . $value . '%')
                              ->orWhere("admissions.address", 'like', '%' . $value . '%');
                    });
                }
                
                // Filter by Admission Number
                if (!empty($request->admissionNo)) {
                    $admission_ids->where("admissions.admissionNo", $request->admissionNo);
                }

                // Filter by Course
                if (!empty($request->course_id)) {
                    $courseId = $request->course_id;
                    $courseObj = \App\Models\Master\Course::find($courseId);
                    $courseName = $courseObj ? $courseObj->name : null;
                    $admission_ids->where(function($q) use ($courseId, $courseName) {
                        $q->where('class_types.course_id', $courseId);
                        if ($courseName) {
                            $q->orWhere('admissions.course', $courseName);
                        }
                    });
                }
                
                // Filter by Class Type
                if (!empty($request->class_type_id)) {
                    $admission_ids->where('admissions.class_type_id', $request->class_type_id);
                }
                
                // Filter by Batch
                if (!empty($request->batch)) {
                    $admission_ids->where("admissions.batch", $request->batch);
                }
                
                // Filter by Status
                if (isset($request->status) && $request->status !== '') {
                    $admission_ids->where("admissions.status", $request->status);
                } else {
                    $admission_ids->where("admissions.status", 1);
                }
                
                $admission_ids = $admission_ids->orderBy('admissions.class_type_id', 'ASC')->get();

                $template = MessageTemplate::Select('message_templates.*','message_types.slug')
                ->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                ->where('message_types.status',1)->where('message_types.slug','feesreminder')->first();
                $setting = Setting::where('session_id',$session)->where('branch_id', Session::get('branch_id'))->first();     
                $studentArray =[];
                foreach($admission_ids as $student){
                    $fees_assigned = FeesAssign::where('admission_id',$student->id)->where('session_id',$session)->first();
                   // $fees_collected = FeesCollect::where('admission_id',$student->id)->where('session_id',$session)->first();
                   $fees_collected = FeesDetail::where('admission_id',$student->id)->whereIn('status',[0,1])->sum('total_amount');

                    $isRemaining = (($fees_assigned->total_amount ?? 0)-($fees_assigned->total_discount ?? 0))-($fees_collected ?? 0);
                    // dd($isRemaining);
                    if($isRemaining >0 ){
                        $getHead =  FeesAssignDetail::Select('fees_assign_details.*','fees_group.name as group_name')
                        ->leftjoin('fees_group','fees_group.id','fees_assign_details.fees_group_id')
                        ->where('admission_id',$student->id)->where('fees_assign_details.session_id',$session)
                        //   ->whereNotNull('installment_due_date')
                        //   ->whereDate('installment_due_date','<=', date('Y-m-d'))
                        ->get();
                        $AllRemainingFees = '';
                        $total_pending = 0;
                        $remainingAmount = 0;
                        $installment_due_date = '';
                        foreach($getHead as $head){
                            //if($head->installment_due_date >= date('Y-m-d') || $head->installment_due_date === null)
                            //{
                                $feesDetails = FeesDetail::where('admission_id',$student->id)->where('fees_group_id',$head->fees_group_id)->whereIn('status',[0,1])->sum('paid_amount');
                                $remainingAmount = (($head->fees_group_amount ?? 0) - ($head->discount ?? 0)) - ($feesDetails ?? 0);
                                if($remainingAmount > 0){
                                    $line = $head->group_name . ' = Rs.' . number_format($remainingAmount);
                                    $AllRemainingFees .= $line . "\n";
                                    $total_pending += $remainingAmount;
                                    $installment_due_date = $head->installment_due_date;
                                }
                            // }
                        }         
                        $AllRemainingFees .= '<span class="bg-danger p-1">*TOTAL PENDING:' . ' = Rs.' . $total_pending.'*</span>';
                        $arrey1 = array(
                            '{#name#}',
                            '{#class_name#}',
                            '{#fees_remain#}',
                            '{#school_name#}',
                            '{#dur_date#}',
                        );
                        $arrey2 = array(
                            ($student->first_name ?? 0).' '.($student->last_name ?? ''),
                            $student->class_name ?? '',
                            preg_replace('/<br\s*\/?>/', '', nl2br($AllRemainingFees)) ,
                            $setting->name ?? '',
                            // date("d-m-Y", strtotime($installment_due_date)),
                            '',
                        );
                       
                        $message = str_replace($arrey1,$arrey2,$template->whatsapp_content);       
                         
                        // if($remainingAmount > 0)
                            // {
                                $studentArray[] =  array( 'id'=>$student->id,
                                'name'=>($student->first_name ?? 0).' '.($student->last_name ?? ''),
                                'className'=>$student->class_name,
                                'class_type_ids'=>$student->class_type_id,
                                'mobile'=>$student->mobile,
                                'admission_id'=>$student->id,
                                'admissionNo'=>$student->admissionNo,
                                'father_name'=>$student->father_name,
                                'category'=>$student->category,
                                'student_type'=>$student->student_type,
                                'course'=>$student->course,
                                'batch'=>$student->batch,
                                'status'=>$student->status,
                                'gender_id'=>$student->gender_id,
                                'session_id'=>$student->session_id,
                                'fees_assigned'=>$fees_assigned->total_amount,
                                'pendings'=>$AllRemainingFees,
                                'message'=>$message,
                            );
                        // }
                    }
                }
            }
            if (isset($studentArray[0]['class_type_ids']) && empty($request->class_type_id)) {
                $search['class_type_id'] = $studentArray[0]['class_type_ids'];
            }
           
              return view('fees.dues.duesList',['data' => $studentArray,'search'=>$search]);
            }
            
    
            public function feesModification(Request $request){
                $admissionNo = $request->admissionNo ?? '';
                $class_type_id= $request->class_type_id ?? '';
                $admission_type_id= $request->admission_type_id_modify ?? '';
                $data =  FeesAssign::Select('fees_assigns.*','admissions.first_name','admissions.last_name','admissions.admissionNo','admissions.mobile')
                ->leftjoin('admissions','admissions.id','fees_assigns.admission_id')->where('fees_assigns.session_id',Session::get('session_id'))
                ->where('fees_assigns.branch_id',Session::get('branch_id'));
                if($class_type_id != ''){
                    $data= $data->where('admissions.class_type_id',$class_type_id);
                }
                if($admission_type_id != ''){
                    $data= $data->where('admissions.admission_type_id',$admission_type_id);
                }
                if($admissionNo != ''){
                    $data= $data->where('admissions.admissionNo',$admissionNo);
                }
                $data = $data ->get();
              
                return view('fees.modification.fees_modification', ['data' => $data]);
            }
    
       
    
            public function updateAssignedFees(Request $request){
                $feesAssignedId = $request->fees_assign_detail_id ?? '';
                $value = $request->value ?? '';
                $field = $request->field ?? '';
                
                if($field === 'installment_due_date'){
                    $value = $request->value ?? NULL;
                }
                $feesAssignDetail = FeesAssignDetail::find($feesAssignedId);
                $admission_id = $feesAssignDetail->admission_id;
                $feesAssignDetail->$field = $value;
                $feesAssignDetail->discount = $request->discountValue;
                $feesAssignDetail->save();
                $feesAssignDetail = FeesAssignDetail::where('branch_id',Session::get('branch_id'))->where('admission_id',$admission_id)->get();   
                $total_amount = 0;
                $total_discount = 0;
                if(!empty($feesAssignDetail)){
                    foreach($feesAssignDetail as $item){ 
                        $total_amount += $item->fees_group_amount ?? 0;
                        $total_discount += $item->discount ?? 0;
                    }
                }
                $feesAssign = FeesAssign::find($feesAssignDetail[0]->fees_assign_id);
                $feesAssign->total_amount = $total_amount ?? 0;
                $feesAssign->total_discount = $total_discount ?? 0;
                $feesAssign->net_amount = $total_amount-$total_discount;
                $feesAssign->save();
                return Response::json(array('message' =>'Fees Updated Successfully' )); 
            }
    
            public function deleteAssignedFees(Request $request){
                $assign_id = $request->fees_assign_detail_id ?? '' ;
                $deleteData = FeesAssignDetail::find($assign_id);
                $admission_id = $deleteData->admission_id;
                $deleteData->delete();  
                $feesAssignDetail=FeesAssignDetail::where('branch_id',Session::get('branch_id'))->where('admission_id',$admission_id)->get();    
                $total_amount = 0;
                $total_discount = 0;
                if(!empty($feesAssignDetail)){
                    foreach($feesAssignDetail as $item){
                        $total_amount += $item->fees_group_amount ?? 0;
                        $total_discount += $item->discount ?? 0;
                    }
                }
                $feesAssign = FeesAssign::find($feesAssignDetail[0]->fees_assign_id);
                $feesAssign->total_amount = $total_amount ?? 0;
                $feesAssign->total_discount = $total_discount ?? 0;
                $feesAssign->net_amount = $total_amount-$total_discount;
                $feesAssign->save();
                return Response::json(array('id' =>$assign_id )); 
            }
            public function getStudentsList(Request $request){
                $class_type_id = $request->class_type_id ?? '';
                $course_id = $request->course_id ?? '';
                $batch = $request->batch ?? '';
                $admissionNo = $request->admissionNo ?? '';

                if(empty($admissionNo) && (empty($course_id) || empty($batch))){
                    return view('fees.modification.admissionList', [
                        'data' => collect([]),
                        'courseFeesMasters' => collect([]),
                        'assignedDetailMap' => []
                    ]);
                }
                
                $data = Admission::with('ClassTypes')
                    ->where('session_id', Session::get('session_id'))
                    ->where('status', 1)
                    ->where('branch_id', Session::get('branch_id'));

                $courseClassIds = [];
                if($class_type_id != ''){
                    $data = $data->where('class_type_id', $class_type_id);
                    $courseClassIds = [$class_type_id];
                } elseif($course_id != '') {
                    $courseClassIds = ClassType::where('course_id', $course_id)->pluck('id')->toArray();
                    $courseObj = \App\Models\Master\Course::find($course_id);
                    $courseName = $courseObj ? $courseObj->name : null;

                    $data = $data->where(function($q) use ($courseClassIds, $courseName) {
                        if(!empty($courseClassIds)){
                            $q->whereIn('class_type_id', $courseClassIds);
                        }
                        if($courseName){
                            $q->orWhere('admissions.course', $courseName);
                        }
                    });
                }
                if($batch != ''){
                    $data = $data->where('admissions.batch', $batch);
                }
                if($request->admission_type_id != ''){
                    $data = $data->where('admission_type_id', $request->admission_type_id);
                }
                if($admissionNo != ''){
                    $data = $data->where(function($q) use ($admissionNo) {
                        $q->where('admissionNo', 'like', '%' . $admissionNo . '%')
                          ->orWhere('first_name', 'like', '%' . $admissionNo . '%')
                          ->orWhere('last_name', 'like', '%' . $admissionNo . '%')
                          ->orWhere('mobile', 'like', '%' . $admissionNo . '%')
                          ->orWhere('father_name', 'like', '%' . $admissionNo . '%');
                    });
                }
                $data = $data->orderBy('first_name', 'ASC')->get();
                
                // Fetch Fee Masters for these classes
                $courseFeesMasters = [];
                if(!empty($courseClassIds)){
                    $courseFeesMasters = FeesMaster::with(['feesGroup', 'ClassTypes'])
                        ->whereIn('class_type_id', $courseClassIds)
                        ->where('session_id', Session::get('session_id'))
                        ->where('branch_id', Session::get('branch_id'))
                        ->orderBy('class_type_id')
                        ->orderBy('id')
                        ->get();
                } else {
                    $courseFeesMasters = FeesMaster::with(['feesGroup', 'ClassTypes'])
                        ->where('session_id', Session::get('session_id'))
                        ->where('branch_id', Session::get('branch_id'))
                        ->orderBy('class_type_id')
                        ->orderBy('id')
                        ->get();
                }

                $studentIds = $data->pluck('id')->toArray();
                $assignedDetails = FeesAssignDetail::whereIn('admission_id', $studentIds)
                    ->where('session_id', Session::get('session_id'))
                    ->where('branch_id', Session::get('branch_id'))
                    ->whereNull('deleted_at')
                    ->get();

                $assignedDetailMap = [];
                foreach($assignedDetails as $ad){
                    $assignedDetailMap[$ad->admission_id . '_' . $ad->fees_master_id] = $ad;
                }

                return view('fees.modification.admissionList', [
                    'data' => $data,
                    'courseFeesMasters' => $courseFeesMasters,
                    'assignedDetailMap' => $assignedDetailMap
                ]);
            }

            public function toggleStudentFeeHead(Request $request){
                $admission_id = $request->admission_id;
                $master_id = $request->fees_master_id;
                $state = $request->state; // 1 = assign, 0 = unassign
                
                $admis = Admission::select('id', 'student_type', 'class_type_id')->find($admission_id);
                if(!$admis){
                    return response()->json(['status' => 'error', 'message' => 'Student not found']);
                }
                
                $fees_master = FeesMaster::find($master_id);
                if(!$fees_master){
                    return response()->json(['status' => 'error', 'message' => 'Fee Master not found']);
                }
                
                $fees_groups = FeesGroup::find($fees_master->fees_group_id);
                
                if($state == 1){
                    $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                    if(!$feesAssign){
                        $feesAssign = new FeesAssign();
                        $feesAssign->user_id = Session::get('id');
                        $feesAssign->session_id = Session::get('session_id');
                        $feesAssign->branch_id = Session::get('branch_id');
                        $feesAssign->admission_id = $admission_id;
                        $feesAssign->total_amount = 0;
                        $feesAssign->net_amount = 0;
                        $feesAssign->save();
                    }
                    
                    if($admis->student_type == "NRI"){
                        $amount = $fees_master->nri;
                    } elseif($admis->student_type == "Management"){
                        $amount = $fees_master->management;
                    } elseif($admis->student_type == "Govt"){
                        $amount = $fees_master->govt;
                    } else {
                        $amount = $fees_master->amount;
                    }
                    
                    $values = FeesAssignDetail::withTrashed()
                        ->where('fees_assign_id', $feesAssign->id)
                        ->where('fees_master_id', $fees_master->id)
                        ->where('admission_id', $admission_id)
                        ->first();
                        
                    if(!$values){
                        $values = new FeesAssignDetail;
                        $values->user_id = Session::get('id');
                        $values->branch_id = Session::get('branch_id');
                        $values->session_id = Session::get('session_id');
                        $values->fees_group_amount = $amount;
                        $values->admission_id = $admission_id;
                        $values->fees_assign_id = $feesAssign->id;
                        $values->class_type_id = $fees_master->class_type_id;
                        $values->fees_master_id = $fees_master->id;
                        $values->fees_group_id = $fees_master->fees_group_id;
                        $values->fees_refund = isset($fees_groups->fees_refund) ? $fees_groups->fees_refund : 'no';
                        $values->installment_month = $fees_master->installment_month;
                        $values->installment_fine = $fees_master->installment_fine;
                        $values->installment_due_date = $fees_master->installment_due_date;
                        $values->save();
                    } elseif($values->trashed()){
                        $values->restore();
                        $values->fees_group_amount = $amount;
                        $values->save();
                    }
                    
                    $total_assign_detail = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('fees_group_amount');
                    $discount_assign_detail = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('discount');
                    $feesAssign->total_amount = $total_assign_detail;
                    $feesAssign->net_amount = ($total_assign_detail - $discount_assign_detail);
                    $feesAssign->save();
                    
                    return response()->json([
                        'status' => 'success',
                        'action' => 'assigned',
                        'head_name' => $fees_groups->name ?? 'Fee Head',
                        'amount' => $amount,
                        'total_amount' => $total_assign_detail,
                        'total_discount' => $discount_assign_detail,
                        'net_amount' => ($total_assign_detail - $discount_assign_detail),
                        'message' => ($fees_groups->name ?? 'Fee Head') . ' assigned successfully'
                    ]);
                } else {
                    $values = FeesAssignDetail::where('fees_master_id', $master_id)
                        ->where('admission_id', $admission_id)
                        ->first();
                        
                    if($values){
                        $hasPayment = FeesDetail::where('admission_id', $admission_id)
                            ->where('fees_group_id', $values->fees_group_id)
                            ->whereIn('status', [0, 1])
                            ->where('paid_amount', '>', 0)
                            ->exists();
                            
                        if($hasPayment){
                            return response()->json(['status' => 'error', 'message' => 'Cannot remove: Payment already exists under this fee head!']);
                        }
                        $values->delete();
                    }
                    
                    $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                    if($feesAssign){
                        $total_assign_detail = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('fees_group_amount');
                        $discount_assign_detail = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('discount');
                        $feesAssign->total_amount = $total_assign_detail;
                        $feesAssign->net_amount = ($total_assign_detail - $discount_assign_detail);
                        $feesAssign->save();
                        $newTotal = $total_assign_detail;
                        $newDiscount = $discount_assign_detail;
                        $newNet = ($total_assign_detail - $discount_assign_detail);
                    } else {
                        $newTotal = 0;
                        $newDiscount = 0;
                        $newNet = 0;
                    }
                    
                    return response()->json([
                        'status' => 'success',
                        'action' => 'unassigned',
                        'head_name' => $fees_groups->name ?? 'Fee Head',
                        'total_amount' => $newTotal,
                        'total_discount' => $newDiscount,
                        'net_amount' => $newNet,
                        'message' => ($fees_groups->name ?? 'Fee Head') . ' unassigned successfully'
                    ]);
                }
            }

            public function bulkAssignCourseFees(Request $request){
                $admissionIds = $request->admissionIds ?? [];
                $feesMasterIds = $request->fees_master_ids ?? [];
                
                if(empty($admissionIds)){
                    return response()->json(['status' => 'error', 'message' => 'Please select at least one student!']);
                }
                
                $assignedCount = 0;
                $updatedTotals = [];
                $updatedDiscounts = [];
                $updatedNets = [];
                foreach($admissionIds as $admission){
                    $admis = Admission::select('id', 'student_type', 'class_type_id')->find($admission);
                    if(!$admis) continue;
                    
                    $targetMasters = $feesMasterIds;
                    if(empty($targetMasters)){
                        $classType = ClassType::find($admis->class_type_id);
                        $courseId = $classType->course_id ?? null;
                        if($courseId){
                            $allClassIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();
                            $targetMasters = FeesMaster::whereIn('class_type_id', $allClassIds)
                                ->where('session_id', Session::get('session_id'))
                                ->where('branch_id', Session::get('branch_id'))
                                ->pluck('id')->toArray();
                        } else {
                            $targetMasters = FeesMaster::where('class_type_id', $admis->class_type_id)
                                ->where('session_id', Session::get('session_id'))
                                ->where('branch_id', Session::get('branch_id'))
                                ->pluck('id')->toArray();
                        }
                    }
                    
                    if(!empty($targetMasters)){
                        $feesAssign = FeesAssign::firstOrCreate(
                            ['admission_id' => $admission],
                            [
                                'user_id' => Session::get('id'),
                                'session_id' => Session::get('session_id'),
                                'branch_id' => Session::get('branch_id'),
                                'total_amount' => 0,
                                'net_amount' => 0
                            ]
                        );
                        
                        foreach($targetMasters as $master_id){
                            $fees_master = FeesMaster::find($master_id);
                            if(!$fees_master) continue;
                            
                            $fees_groups = FeesGroup::find($fees_master->fees_group_id);
                            
                            if($admis->student_type == "NRI"){
                                $amount = $fees_master->nri;
                            } elseif($admis->student_type == "Management"){
                                $amount = $fees_master->management;
                            } elseif($admis->student_type == "Govt"){
                                $amount = $fees_master->govt;
                            } else {
                                $amount = $fees_master->amount;
                            }
                            
                            $values = FeesAssignDetail::withTrashed()
                                ->where('fees_assign_id', $feesAssign->id)
                                ->where('fees_master_id', $fees_master->id)
                                ->where('admission_id', $admission)
                                ->first();
                                
                            if(!$values){
                                $values = new FeesAssignDetail;
                                $values->user_id = Session::get('id');
                                $values->branch_id = Session::get('branch_id');
                                $values->session_id = Session::get('session_id');
                                $values->fees_group_amount = $amount;
                                $values->admission_id = $admission;
                                $values->fees_assign_id = $feesAssign->id;
                                $values->class_type_id = $fees_master->class_type_id;
                                $values->fees_master_id = $fees_master->id;
                                $values->fees_group_id = $fees_master->fees_group_id;
                                $values->fees_refund = isset($fees_groups->fees_refund) ? $fees_groups->fees_refund : 'no';
                                $values->installment_month = $fees_master->installment_month;
                                $values->installment_fine = $fees_master->installment_fine;
                                $values->installment_due_date = $fees_master->installment_due_date;
                                $values->save();
                                $assignedCount++;
                            } elseif($values->trashed()){
                                $values->restore();
                                $values->fees_group_amount = $amount;
                                $values->save();
                                $assignedCount++;
                            }
                        }
                        
                        $total_assign_detail = FeesAssignDetail::where('admission_id', $admission)->whereNull('deleted_at')->sum('fees_group_amount');
                        $discount_assign_detail = FeesAssignDetail::where('admission_id', $admission)->whereNull('deleted_at')->sum('discount');
                        $feesAssign->total_amount = $total_assign_detail;
                        $feesAssign->net_amount = ($total_assign_detail - $discount_assign_detail);
                        $feesAssign->save();
                        
                        $updatedTotals[$admission] = $total_assign_detail;
                        $updatedDiscounts[$admission] = $discount_assign_detail;
                        $updatedNets[$admission] = ($total_assign_detail - $discount_assign_detail);
                    }
                }
                
                return response()->json([
                    'status' => 'success',
                    'count' => $assignedCount,
                    'total_amount' => count($updatedTotals) === 1 ? reset($updatedTotals) : 0,
                    'total_discount' => count($updatedDiscounts) === 1 ? reset($updatedDiscounts) : 0,
                    'net_amount' => count($updatedNets) === 1 ? reset($updatedNets) : 0,
                    'updated_totals' => $updatedTotals,
                    'updated_discounts' => $updatedDiscounts,
                    'updated_nets' => $updatedNets,
                    'message' => 'Fees Structure assigned successfully in real-time!'
                ]);
            }

            public function clearStudentFeeHeads(Request $request){
                $admission_id = $request->admission_id;
                $admissionIds = $request->admissionIds ?? [];
                
                if($admission_id){
                    $admissionIds = [$admission_id];
                }
                
                if(empty($admissionIds)){
                    return response()->json(['status' => 'error', 'message' => 'No student specified to clear fee heads!']);
                }
                
                $clearedCount = 0;
                $updatedTotals = [];
                $updatedDiscounts = [];
                $updatedNets = [];
                
                foreach($admissionIds as $admId){
                    $admis = Admission::find($admId);
                    if(!$admis) continue;
                    
                    // Check if student has paid fee groups
                    $paidGroupIds = FeesDetail::where('admission_id', $admId)
                        ->whereIn('status', [0, 1])
                        ->where('paid_amount', '>', 0)
                        ->pluck('fees_group_id')
                        ->toArray();
                    
                    // Unassign only unpaid fee heads
                    $query = FeesAssignDetail::where('admission_id', $admId);
                    if(!empty($paidGroupIds)){
                        $query->whereNotIn('fees_group_id', $paidGroupIds);
                    }
                    $query->delete();
                        
                    $feesAssign = FeesAssign::where('admission_id', $admId)->first();
                    if($feesAssign){
                        $total = FeesAssignDetail::where('admission_id', $admId)->whereNull('deleted_at')->sum('fees_group_amount');
                        $discount = FeesAssignDetail::where('admission_id', $admId)->whereNull('deleted_at')->sum('discount');
                        $feesAssign->total_amount = $total;
                        $feesAssign->net_amount = ($total - $discount);
                        $feesAssign->save();
                        $updatedTotals[$admId] = $total;
                        $updatedDiscounts[$admId] = $discount;
                        $updatedNets[$admId] = ($total - $discount);
                    } else {
                        $updatedTotals[$admId] = 0;
                        $updatedDiscounts[$admId] = 0;
                        $updatedNets[$admId] = 0;
                    }
                    $clearedCount++;
                }
                
                return response()->json([
                    'status' => 'success',
                    'count' => $clearedCount,
                    'total_amount' => count($updatedTotals) === 1 ? reset($updatedTotals) : 0,
                    'total_discount' => count($updatedDiscounts) === 1 ? reset($updatedDiscounts) : 0,
                    'net_amount' => count($updatedNets) === 1 ? reset($updatedNets) : 0,
                    'updated_totals' => $updatedTotals,
                    'updated_discounts' => $updatedDiscounts,
                    'updated_nets' => $updatedNets,
                    'message' => 'Unpaid fee heads cleared successfully in real-time!'
                ]);
            }

            public function checkStudentCollectedFees(Request $request){
                $admission_id = $request->admission_id;
                $student = Admission::with('ClassTypes')->find($admission_id);
                if(!$student){
                    return response()->json(['status' => 'error', 'message' => 'Student record not found']);
                }

                $invoices = FeesDetailsInvoices::where('admission_id', $admission_id)
                    ->whereIn('status', [0, 1])
                    ->orderBy('id', 'DESC')
                    ->get();

                $paidDetails = FeesDetail::where('admission_id', $admission_id)
                    ->whereIn('status', [0, 1])
                    ->where('paid_amount', '>', 0)
                    ->with(['feesGroup', 'PaymentMode'])
                    ->orderBy('id', 'DESC')
                    ->get();

                $totalPaid = $paidDetails->sum('paid_amount');
                $hasPaid = ($totalPaid > 0) || ($paidDetails->count() > 0) || ($invoices->count() > 0);

                $invoiceList = [];
                foreach($invoices as $inv){
                    $detailIds = array_filter(explode(',', $inv->fees_details_id));
                    $heads = FeesDetail::whereIn('id', $detailIds)->with('feesGroup')->get();
                    $headNames = $heads->pluck('feesGroup.name')->filter()->unique()->implode(', ');
                    
                    $paymentModeObj = \App\Models\PaymentMode::find($inv->payment_mode);
                    $modeName = $paymentModeObj ? $paymentModeObj->name : ($inv->payment_mode ?? 'Cash');

                    $invoiceList[] = [
                        'id' => $inv->id,
                        'invoice_no' => $inv->invoice_no,
                        'amount' => floatval($inv->amount),
                        'discount' => floatval($inv->discount),
                        'fine' => floatval($inv->total_fine),
                        'payment_date' => !empty($inv->payment_date) ? date('d-m-Y', strtotime($inv->payment_date)) : '-',
                        'payment_mode' => $modeName,
                        'heads' => $headNames ?: 'Fees Collection',
                        'receipt_url' => url('feesReceipt/' . $inv->invoice_no . '/' . $admission_id)
                    ];
                }

                $directDetails = [];
                foreach($paidDetails as $pd){
                    $directDetails[] = [
                        'head_name' => $pd->feesGroup->name ?? 'Fee Head',
                        'amount' => floatval($pd->paid_amount),
                        'date' => !empty($pd->date) ? date('d-m-Y', strtotime($pd->date)) : '-',
                        'payment_mode' => $pd->PaymentMode->name ?? 'Cash',
                        'receipt_no' => $pd->receipt_no ?? '-'
                    ];
                }

                return response()->json([
                    'status' => 'success',
                    'has_paid' => $hasPaid,
                    'total_paid' => $totalPaid,
                    'student' => [
                        'id' => $student->id,
                        'name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                        'admissionNo' => $student->admissionNo ?? '-',
                        'class_name' => $student->ClassTypes->name ?? '-',
                        'batch' => $student->batch ?? '-'
                    ],
                    'invoices' => $invoiceList,
                    'paid_details' => $directDetails
                ]);
            }

            public function getStudentFeeDetailsModal(Request $request){
                $admission_id = $request->admission_id;
                $student = Admission::with('ClassTypes')->find($admission_id);
                if(!$student){
                    return '<div class="alert alert-danger p-2 m-2">Student record not found</div>';
                }
                
                $assignedDetails = FeesAssignDetail::where('admission_id', $admission_id)
                    ->leftJoin('fees_group', 'fees_group.id', 'fees_assign_details.fees_group_id')
                    ->select('fees_assign_details.*', 'fees_group.name as fees_group_name')
                    ->whereNull('fees_assign_details.deleted_at')
                    ->get();
                    
                $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                
                return view('fees.fees.student_fee_edit_modal_content', compact('student', 'assignedDetails', 'feesAssign'));
            }

            public function updateStudentFeeDetailInline(Request $request){
                $detail_id = $request->detail_id;
                $detail = FeesAssignDetail::find($detail_id);
                if(!$detail){
                    return response()->json(['status' => 'error', 'message' => 'Fee record not found']);
                }
                
                if($request->has('amount')){
                    $detail->fees_group_amount = floatval($request->amount);
                }
                if($request->has('discount')){
                    $detail->discount = floatval($request->discount);
                }
                if($request->has('due_date')){
                    $detail->installment_due_date = $request->due_date;
                }
                if($request->has('fine')){
                    $detail->installment_fine = floatval($request->fine);
                }
                $detail->save();
                
                $feesAssign = FeesAssign::where('admission_id', $detail->admission_id)->first();
                if($feesAssign){
                    $total = FeesAssignDetail::where('admission_id', $detail->admission_id)->whereNull('deleted_at')->sum('fees_group_amount');
                    $discount = FeesAssignDetail::where('admission_id', $detail->admission_id)->whereNull('deleted_at')->sum('discount');
                    $feesAssign->update([
                        'total_amount' => $total,
                        'net_amount' => ($total - $discount)
                    ]);
                    $net = $total - $discount;
                } else {
                    $total = $detail->fees_group_amount;
                    $discount = $detail->discount ?? 0;
                    $net = $total - $discount;
                }
                
                return response()->json([
                    'status' => 'success',
                    'total_amount' => $total,
                    'total_discount' => $discount,
                    'net_amount' => $net,
                    'message' => 'Fee detail updated successfully'
                ]);
            }

            public function updateStudentFeeDetailsBatch(Request $request){
                $admission_id = $request->admission_id;
                $details = $request->details;
                if(!empty($details) && is_array($details)){
                    foreach($details as $item){
                        if(isset($item['id'])){
                            $detail = FeesAssignDetail::find($item['id']);
                            if($detail && $detail->admission_id == $admission_id){
                                if(isset($item['amount'])) $detail->fees_group_amount = floatval($item['amount']);
                                if(isset($item['discount'])) $detail->discount = floatval($item['discount']);
                                if(isset($item['due_date'])) $detail->installment_due_date = $item['due_date'];
                                if(isset($item['fine'])) $detail->installment_fine = floatval($item['fine']);
                                $detail->save();
                            }
                        }
                    }
                }
                
                $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                if($feesAssign){
                    $total = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('fees_group_amount');
                    $discount = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('discount');
                    $feesAssign->update([
                        'total_amount' => $total,
                        'net_amount' => ($total - $discount)
                    ]);
                    $net = $total - $discount;
                } else {
                    $total = 0;
                    $discount = 0;
                    $net = 0;
                }
                
                return response()->json([
                    'status' => 'success',
                    'total_amount' => $total,
                    'total_discount' => $discount,
                    'net_amount' => $net,
                    'message' => 'All fee details updated successfully'
                ]);
            }
            public function createFeesInstallment(Request $request){
                if(!empty($request->installment_name)){
                    foreach($request->installment_name as $key=> $name)
                {
                $fees_group = FeesGroup::where('name' , $name)->first();            
                if(!empty($fees_group)){
                    $fees_group = $fees_group;
                }
                else
                {
                   $fees_group = new FeesGroup; //model name
                }
                $fees_group->user_id = Session::get('id');
                    $fees_group->session_id = Session::get('session_id');
                    $fees_group->branch_id = Session::get('branch_id');
                    $fees_group->name = $name;
                        $fees_group->fees_type = 'installment';
                        $fees_group->description = $request->description;
                        $fees_group->save();
                    }
                    return redirect::to('feesGroup')->with('message','Fees Group Created successfully');
                }
            }
            public function createFeesInstallmentClassWise(Request $request){
                if(!empty($request->installmentRow)){
                    $returnStatus['fees_master'] = [];
                    foreach($request->installmentRow as $key=> $row){
                        $returnStatus['entry'] = false;
                        $fees_group = FeesGroup::find($request->installment_id[$key]);          
                        if(!empty($fees_group)){
                            $fees_group = $fees_group;
                        }
                        $fees_group->user_id = Session::get('id');
                        $fees_group->session_id = Session::get('session_id');
                        $fees_group->branch_id = Session::get('branch_id');
                        $fees_group->name = $request->installment_name[$key];
                        $fees_group->fees_type = 'installment';
                        $fees_group->save();
                        if(!empty($request->installment_class_type_id)){
                            $fees_master = FeesMaster::where('fees_group_id' , $fees_group->id)->where('class_type_id' , $request->installment_class_type_id)->first();
                            if(!empty($fees_master)){
                                $fees_master = $fees_master;
                                $isUsed1 = FeesDetail::where('fees_group_id',$fees_group->id)->where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->count();
                                $isUsed2 = FeesAssignDetail::where('fees_group_id',$fees_group->id)->where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->count();
                                if(($isUsed1 + $isUsed2) == 0){
                                   $returnStatus['entry'] = true;
                                   $returnStatus['fees_master'][] = $fees_master->id;
                                }else{
                                   $returnStatus['entry'] = false;
                                }
                            }
                            else{
                                $fees_master = new FeesMaster; //model name
                                $returnStatus['entry'] = true;
                                $returnStatus['fees_master'][] = $fees_master->id;
                            }
                            $fees_master->user_id = Session::get('id');
                            $fees_master->session_id = Session::get('session_id');
                            $fees_master->branch_id = Session::get('branch_id');
                            $fees_master->fees_group_id = $fees_group->id;
                            $fees_master->amount = $request->installment_value[$key];
                            $fees_master->installment_month = $request->installment_month[$key];
                            $fees_master->installment_fine = $request->installment_fine[$key];
                            $fees_master->installment_due_date = $request->installment_due_date[$key];
                            $fees_master->class_type_id = $request->installment_class_type_id;
                            $fees_master->save();
                        }
                    }
                    $returnStatus['class_type_id'] = $request->installment_class_type_id;
                    return $returnStatus;
                }
            }
        
            public function feesAssign(Request $request){
                $courses = Helper::getCourses();
                $classType = Helper::classType();
                $allClassType = Helper::classType();
                $getFeesGroup = Helper::getFeesGroup();
                $getSession = Helper::getSession();

                $serach['course_id'] = $request->course_id ?? '';
                $serach['batch'] = $request->batch ?? '';
                $serach['admissionNo'] = $request->admissionNo ?? ($request->admission_no ?? '');

                return view('fees.fees.fees_assign', [
                    'courses' => $courses,
                    'classType' => $classType,
                    'allClassType' => $allClassType,
                    'getFeesGroup' => $getFeesGroup,
                    'getSession' => $getSession,
                    'serach' => $serach
                ]);
            }

            public function assignFeesMultipleStudents(Request $request){
                if(!empty($request->admissionIds)){
                    foreach($request->admissionIds as $admission){
                        $admis = Admission::select('student_type')->find($admission);
                      
                        if(!empty($request->fees_master_ids)){
                            foreach($request->fees_master_ids as $master_id){
                                $fees_master = FeesMaster::find($master_id);
                                $fees_groups = FeesGroup::find($fees_master->fees_group_id);
                                $fees_assign_details = FeesAssignDetail::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))
                                ->where('fees_master_id',$master_id)->where('admission_id',$admission)->first();
                                  
                                    $feesAssign = FeesAssign::where('admission_id',$admission)->first();
                                    if(!empty($feesAssign)){
                                        $feesAssign = $feesAssign;
                                    }else{
                                        $feesAssign = new FeesAssign();
                                    }
                                    $feesAssign->user_id = Session::get('id');
                                    $feesAssign->session_id = Session::get('session_id');
                                    $feesAssign->branch_id = Session::get('branch_id');
                                    $feesAssign->admission_id = $admission;
                                    $feesAssign->save();
                                    $values = FeesAssignDetail::where('fees_assign_id',$feesAssign->id)
                                    ->where('fees_master_id',$fees_master->id)
                                    ->where('fees_group_id',$fees_master->fees_group_id)
                                    ->where('admission_id',$admission)
                                    ->first();
                                    if(!empty($values)){
                                        $values = $values;
                                    }else{
                                        $values = new FeesAssignDetail;
                                    }
                                    if($admis->student_type == "NRI"){
                                    $amount = $fees_master->nri;
                                    }elseif($admis->student_type == "Management"){
                                       
                                        $amount = $fees_master->management;
                                    }elseif($admis->student_type == "Govt"){
                                        $amount = $fees_master->govt;
                                    }else{
                                        $amount = $fees_master->amount;
                                    }
                                  
                                    $values->user_id = Session::get('id');
                                        $values->branch_id = Session::get('branch_id');
                                        $values->session_id = Session::get('session_id');
                                        $values->fees_group_amount = $amount;
                                        $values->admission_id = $admission;
                                        $values->fees_assign_id = $feesAssign->id;
                                        $values->class_type_id = $fees_master->class_type_id;
                                        $values->fees_master_id = $fees_master->id;
                                        $values->fees_group_id = $fees_master->fees_group_id;
                                        if (isset($fees_groups->fees_refund)) {
                                        $values->fees_refund = $fees_groups->fees_refund;
                                        } else {
                                        $values->fees_refund = 'no';
                                        } 
                                        $values->installment_month = $fees_master->installment_month;
                                        $values->installment_fine = $fees_master->installment_fine;
                                        $values->installment_due_date= $fees_master->installment_due_date;
                                    $values->save();
                                    $total_assign_detail = FeesAssignDetail::where('admission_id',$admission)->sum('fees_group_amount');
                                    $discount_assign_detail = FeesAssignDetail::where('admission_id',$admission)->sum('discount');
                                    $amountIncrement = FeesAssign::where('id',$feesAssign->id)->update(['total_amount'=>$total_assign_detail]);
                                    $amountIncrement = FeesAssign::where('id',$feesAssign->id)->update(['net_amount'=>($total_assign_detail-$discount_assign_detail) ]);
                                    //   $amountIncrement = FeesAssign::where('id',$feesAssign->id)->increment('total_amount', $request->installment_value[$key] );
                                    //   $amountIncrement = FeesAssign::where('id',$feesAssign->id)->increment('net_amount', $request->installment_value[$key] );
                                            
                            }
                        }
                    }
                    return redirect()->back()->with('message','Fee Structure assigned successfully to selected students');
                }
                return redirect()->back()->with('error', 'Please select at least one student and fee head');
            }
        
            public function getMasterData(Request $request){
                $query = FeesMaster::select('fees_master.*','fees_group.name as fees_group_name', 'class_types.name as class_name')
                    ->leftJoin('fees_group','fees_group.id','fees_master.fees_group_id')
                    ->leftJoin('class_types','class_types.id','fees_master.class_type_id')
                    ->where('fees_master.session_id',Session::get('session_id'))
                    ->where('fees_master.branch_id',Session::get('branch_id'));

                if(!empty($request->class_type_id)){
                    $query->where('fees_master.class_type_id', $request->class_type_id);
                } elseif(!empty($request->course_id)){
                    $classIds = ClassType::where('course_id', $request->course_id)->pluck('id')->toArray();
                    $query->whereIn('fees_master.class_type_id', $classIds);
                }
                $masterData = $query->orderBy('fees_master.class_type_id')->orderBy('fees_master.id')->get();
                return $masterData; 
            }
        
            public function caReport(Request $request){
                $search['name'] = $request->name;
                $search['user_id'] = $request->user_id;
                $search['class_type_id'] = $request->class_type_id;
                $search['course_id'] = $request->course_id;
                $search['starting'] = $request->starting;
                $search['ending'] = $request->ending;
                $search['admission_no'] = $request->admission_no;
                $search['batch'] = $request->batch;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                $data =  FeesDetailsInvoices::select('fees_details_invoices.*','class.name as class_name',
                'admissions.image','admissions.mobile','admissions.admissionNo','admissions.first_name'
                ,'admissions.last_name','users.first_name as users_first_name'
                ,'admissions.category','admissions.student_type','admissions.course','admissions.batch','admissions.status as ad_status'
                 ,'admissions.gender_id','users.last_name as users_last_name','admissions.father_name','admissions.school','payment_modes.name as payment_mode','payment_modes.id as payment_mode_id'
                )
                ->leftjoin('admissions as admissions', 'admissions.id', 'fees_details_invoices.admission_id')
                ->leftjoin('class_types as class','class.id','admissions.class_type_id')
                ->leftjoin('payment_modes','payment_modes.id','fees_details_invoices.payment_mode')
                ->leftjoin('users','users.id','fees_details_invoices.user_id')
                ->where('fees_details_invoices.branch_id', Session::get('branch_id'));

                if (!empty($search['session_id'])) {
                    $data = $data->where('fees_details_invoices.session_id', $search['session_id']);
                }
                if (!empty($request->course_id)) {
                    $courseId = $request->course_id;
                    $courseObj = \App\Models\Master\Course::find($courseId);
                    $courseName = $courseObj ? $courseObj->name : null;
                    $data = $data->where(function($q) use ($courseId, $courseName) {
                        $q->where('class.course_id', $courseId);
                        if ($courseName) {
                            $q->orWhere('admissions.course', $courseName);
                        }
                    });
                }
                if ($request->batch != '') {
                    $data = $data->where("admissions.batch", $request->batch);
                }
                if (!empty($request->class_type_id)) {
                    $data = $data->where("admissions.class_type_id", $request->class_type_id);
                }
                if (!empty($request->admission_no)) {
                    $data = $data->where("admissions.admissionNo", $request->admission_no);
                }

                if ($request->isMethod('post')) {
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $data = $data->where(function($q) use ($value) {
                            $q->where('admissions.first_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.last_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.father_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.mother_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.admissionNo', $value)
                            ->orWhere('admissions.mobile', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.aadhaar', $value)
                            ->orWhere('admissions.email', 'LIKE', '%' . $value . '%');
                        });
                    }
                    if (!empty($request->starting)) {
                        $data = $data->whereBetween('fees_details_invoices.payment_date', [$request->starting, $request->ending]);
                    }
                    if (!empty($request->user_id)) {
                        $data = $data->where("fees_details_invoices.user_id", $request->user_id);
                    }
                }
                if (Session::get('role_id') > 1) {
                    $data = $data->where('fees_details_invoices.user_id', Session::get('id'));
                }
                $data = $data->where('admissions.school','=',1)->orderBy('fees_details_invoices.id', 'DESC')->get();
                return view('fees.reports.CA', ['data' => $data, 'search' => $search]);
            }
        

            public function fees_cheque(Request $request){
               
                $search['name'] = $request->name;
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course_id'] = $request->course_id ?? '';
                $search['batch'] = $request->batch ?? '';
                $search['starting'] = $request->starting;
                $search['ending'] = $request->ending;
                $search['admission_no'] = $request->admission_no;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                
                if ($request->isMethod('post')) {
                    $update = FeesDetailsInvoices::find($request->id);
                     //dd($update);
                    if(!empty($update))
                    {
                        $update->status = $request->status_id ?? '';
                        $update->remark = $request->remark ?? '';
                        $update->save();
                        $feesDetailsId = explode(',', $update->fees_details_id); // Convert string to array
                        if (!empty($feesDetailsId)) {
                            $fees_ = FeesDetail::whereIn('id', $feesDetailsId)->update(['status' => $request->status_id]);
                
                        } 

                        $admission = Admission::find($update->admission_id);
                        $branch = Branch::find($admission->branch_id);
                        $setting = Setting::where('branch_id',$admission->branch_id)->first();
                        $payment_mode = PaymentMode::find($update->payment_mode);
                        $finalCollectedAmt = (float) ($update->amount ?? 0) + (float) ($update->total_fine ?? 0) + (float) ($update->discount ?? 0);
                        $receiptDate = !empty($update->payment_date) ? date('d-m-Y', strtotime($update->payment_date)) : date('d-m-Y');

                        $printPreview = Helper::printPreview('Fees Collect');
                        if ($request->status_id == 0) {        
                       
                            // ✅ 11. WhatsApp notification
                            if ($admission && $branch && $branch->whatsapp_srvc != 0 && !empty($admission->mobile)) {
                                $studentName = trim($admission->first_name . ' ' . $admission->last_name);

                                $template = MessageType::where('template_name','collect_fee')
                                                        ->where('status',1)
                                                        ->first();
                                
                                if($template){
                                    
                                    $receipt = url('/feesReceipt') . '/' . $update->invoice_no . '/' . $update->admission_id;
                                                
                                                        // 👇 Human readable message (DB के लिए)
                                    $variables = [
                                        '{{1}}' => $studentName ?? '',
                                        '{{2}}' => number_format($finalCollectedAmt,2),
                                        '{{3}}' => $setting->name ?? '',
                                        '{{4}}' => "Mode: ".($payment_mode->name ?? '') . ", Date: ".$receiptDate,
                                        '{{5}}' => $setting->name ?? '',
                                        '{{6}}' => $receipt ?? ''
                                    ];
                            
                                    $content = str_replace(
                                        array_keys($variables),
                                        array_values($variables),
                                        $template->template_content
                                    );

                                    $params = [
                                        1 => $studentName ?? '',
                                        2 => number_format($finalCollectedAmt,2),
                                        3 => $setting->name ?? '',
                                        4 => "Mode: ".($payment_mode->name ?? '') . ", Date: ".$receiptDate,
                                        5 => $setting->name ?? '',
                                        6 => $receipt ?? '',
                                    ];
                                
                                    $this->notif->enqueue([
                                        'user_id'    => Session::get('id'),
                                        'branch_id'  => Session::get('branch_id'),
                                        'session_id' => Session::get('session_id'),
                                        'recipient'  => $admission->mobile,
                                        'message'    => $content,
                                        'media_link' => $receipt,
                                        'event_type'  => 'fees_payment_successful',
                                        'template_name' => 'collect_fee',
                                        'channel'     => 'whatsapp',
                                        'params'        => $params,
                                    ]);

                                }
                            }

                        }elseif($request->status_id == 2) {
                            // ✅ 11. WhatsApp notification
                            if ($admission && $branch && $branch->whatsapp_srvc != 0 && !empty($admission->mobile)) {
                                
                                $studentName = trim($admission->first_name . ' ' . $admission->last_name);

                                $template = MessageType::where('template_name','store_payment_unsuccessful')
                                                        ->where('status',1)
                                                        ->first();
                                if($template){
                                    $variables = [
                                        '{{1}}' => $studentName ?? '',
                                        '{{2}}' => $setting->name ?? ''
                                    ];
                            
                                    $content = str_replace(
                                        array_keys($variables),
                                        array_values($variables),
                                        $template->template_content
                                    );

                                    $params = [
                                        1 => $studentName ?? '',
                                        2 => $setting->name ?? '',
                                    ];
                                
                                    $this->notif->enqueue([
                                        'user_id'    => Session::get('id'),
                                        'branch_id'  => Session::get('branch_id'),
                                        'session_id' => Session::get('session_id'),
                                        'recipient'  => $admission->mobile,
                                        'message'    => $content,
                                        'event_type'  => 'fees_payment_unsuccessful',
                                        'template_name' => 'store_payment_unsuccessful',
                                        'channel'     => 'whatsapp',
                                        'params'        => $params,
                                    ]);
                                }

                            }
                        }
                        
                    }
                }

                    $data =  FeesDetailsInvoices::select('fees_details_invoices.*','class.name as class_name','admissions.admissionNo','admissions.mobile','admissions.first_name'
                    ,'admissions.last_name','admissions.father_name','admissions.school','payment_modes.name as payment_mode','payment_modes.id as payment_mode_id')
                    ->leftjoin('admissions as admissions', 'admissions.id', 'fees_details_invoices.admission_id')
                    ->leftjoin('class_types as class','class.id','admissions.class_type_id')
                    ->leftjoin('payment_modes','payment_modes.id','fees_details_invoices.payment_mode')
                    ->where('fees_details_invoices.branch_id', Session::get('branch_id'))
                    ->where('fees_details_invoices.status', 1);

                    if (!empty($search['session_id'])) {
                        $data = $data->where('fees_details_invoices.session_id', $search['session_id']);
                    }
                    if (!empty($request->course_id)) {
                        $courseId = $request->course_id;
                        $courseObj = \App\Models\Master\Course::find($courseId);
                        $courseName = $courseObj ? $courseObj->name : null;
                        $data = $data->where(function($q) use ($courseId, $courseName) {
                            $q->where('class.course_id', $courseId);
                            if ($courseName) {
                                $q->orWhere('admissions.course', $courseName);
                            }
                        });
                    }
                    if (!empty($request->batch)) {
                        $data = $data->where("admissions.batch", $request->batch);
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where("admissions.class_type_id", $request->class_type_id);
                    }
                    if (!empty($request->admission_no)) {
                        $data = $data->where("admissions.admissionNo", $request->admission_no);
                    }
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $data = $data->where(function($q) use ($value) {
                            $q->where('admissions.first_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.last_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.father_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.mother_name', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.admissionNo', $value)
                            ->orWhere('admissions.mobile', 'LIKE', '%' . $value . '%')
                            ->orWhere('admissions.email', 'LIKE', '%' . $value . '%');
                        });
                    }
                    if (!empty($request->starting)) {
                        $data = $data->whereBetween('fees_details_invoices.payment_date', [$request->starting, $request->ending]);
                    }
                    if (Session::get('role_id') == 2) {
                        $data = $data->where('admissions.class_type_id', Session::get('class_type_id'));
                    } 
                    $data = $data->where('school', '>', 0)->orderBy('fees_details_invoices.payment_date','DESC')->get();

               return view('fees.fees_cheque', ['data' => $data, 'search' => $search]);
            }
     

            public function balanceReport(Request $request){
               
                $search['name'] = $request->name;
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course_id'] = $request->course_id ?? '';
                $search['status'] = $request->status;
                $search['batch'] = $request->batch;
                $search['admissionNo'] = $request->admissionNo;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');

                $data='';

                if ($request->isMethod('post')) {
                    $data = Admission::select('admissions.*','fees_assigns.total_amount','fees_detail.fees_counter_id','fees_detail.date','class_types.name as className','fees_assigns.total_discount as assign_discount','fees_collect.amount as collect_amount', 'fees_collect.discount')
                    ->leftJoin('fees_assigns as fees_assigns', 'fees_assigns.admission_id', 'admissions.id')
                    ->leftJoin('class_types', 'class_types.id', 'admissions.class_type_id')
                    ->leftJoin('fees_collect as fees_collect', 'fees_collect.admission_id', 'admissions.id')
                    ->leftJoin('fees_detail as fees_detail', 'fees_detail.admission_id', 'admissions.id')
                    ->where('admissions.admission_type_id',1)
                    ->where('admissions.school',1)
                    ->where('admissions.branch_id', Session::get('branch_id'))
                    ->groupBy('admissions.admissionNo');

                    if (!empty($search['session_id'])) {
                        $data = $data->where('admissions.session_id', $search['session_id']);
                    }
                    if (!empty($request->course_id)) {
                        $courseId = $request->course_id;
                        $courseObj = \App\Models\Master\Course::find($courseId);
                        $courseName = $courseObj ? $courseObj->name : null;
                        $data = $data->where(function($q) use ($courseId, $courseName) {
                            $q->where('class_types.course_id', $courseId);
                            if ($courseName) {
                                $q->orWhere('admissions.course', $courseName);
                            }
                        });
                    }
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $data = $data->where(function ($query) use ($value) {
                            $query->where("admissions.first_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.last_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.mobile", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.email", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.aadhaar", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.father_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.mother_name", 'like', '%' . $value . '%');
                            $query->orwhere("admissions.address", 'like', '%' . $value . '%');
                        });
                    }
                    if ($request->admissionNo != '') {
                        $data = $data->where("admissions.admissionNo", $request->admissionNo);
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where('admissions.class_type_id',$request->class_type_id);
                    }
                    if (!empty($request->starting)) {
                        $data = $data->whereBetween('fees_detail.date', [$request->starting, $request->ending]);
                    }
                    if ($request->batch != '') {
                        $data = $data->where("admissions.batch", $request->batch);
                    }

                    if ($request->status != '') {
                        $data = $data->where("admissions.status", $request->status);
                    }else
                    {
                        $data = $data->where("admissions.status", 1);
                        $search['status'] = 1;
                    }
                  
                    $data = $data->orderBy('admissions.id', 'DESC')->get();
                }

                return view('fees.ledger.balanceReport', ['data' => $data, 'search' => $search]);
            }
            
            public function temp_fees_collect(Request $request) { 
           
                    $data = DB::table('temp_fees_collect')->whereIn('status', [0, 3])->get();
                   
                    foreach ($data as $temp) {
                        $admission = Admission::where('admissionNo', $temp->student_id)
                            ->where('session_id', $temp->session_id)
                            ->where('branch_id', $temp->branch_id)
                            ->first();
          
        if (!empty($admission)){
           
                        $cash = $temp->cash;
                        $currentAdmission = $admission;
                        $session_id = $currentAdmission->session_id;
            
                        $getAssign = FeesAssignDetail::select('fees_assign_details.*', 'fees_group.name as group_name')
                            ->join('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                            ->where('admission_id', $currentAdmission->id)
                            ->get();
            
                        $courentPayFees = $cash;
                        $total_pending = 0;
                        $hede = [];
                  if($cash > 0){
                

                        foreach ($getAssign as $head) {
                            $feesDetails = FeesDetail::where('admission_id', $currentAdmission->id)
                                ->where('fees_group_id', $head->fees_group_id)
                                ->whereIn('status', [0, 1])
                                ->sum('total_amount');
            
                            $remainingAmount = (($head->fees_group_amount ?? 0) - ($head->discount ?? 0)) - ($feesDetails ?? 0);
            
                            if ($remainingAmount > 0) {
                                $pay = min($courentPayFees, $remainingAmount);
            
                                
                                    $hede[] = [
                                        'fees_group_id' => $head->fees_group_id,
                                        'amount' => $pay
                                    ];
                                    $courentPayFees -= $pay;
                                    $total_pending += $remainingAmount;
                                
                            }
                        }
                        // If there is any remaining amount, add it to the last element in $hede
if (!empty($hede) && $courentPayFees > 0) {
    $lastIndex = count($hede) - 1;
    $hede[$lastIndex]['amount'] += $courentPayFees;
}
            
                        if (!empty($hede)) {
                            $BillCounter = BillCounter::where('session_id', $session_id)
                                ->where('branch_id', $currentAdmission->branch_id)
                                ->where('type', 'FeesSlip')
                                ->first();
            
                            if (!$BillCounter) continue; // Skip if no bill counter found
            
                            $FeesAssign = FeesAssign::where('admission_id', $currentAdmission->id)->first();
            
                            if (empty($FeesAssign)){
                                     DB::table('temp_fees_collect')->where('id', $temp->id)->update(['status' => 4]);

                                 continue; // Skip if no fee assignment found
                            }
            
                            $counter = $BillCounter->counter ?? 0;
                            $BillCounter->counter = $counter + 1;
                            $BillCounter->save();
            
                            $fees_details_id = [];
                            $amount = 0;
            
                            foreach ($hede as $head) {
                                if ($head['amount'] > 0) {
                                    $payOld = FeesCollect::where('admission_id', $currentAdmission->id)->first();
            
                                    if ($payOld) {
                                        FeesCollect::where('admission_id', $currentAdmission->id)
                                            ->increment('amount', $head['amount'] ?? 0);
                                        $collect_id = $payOld->id;
                                    } else {
                                        $pay = new FeesCollect();
                                        $pay->user_id = $currentAdmission->user_id;
                                        $pay->session_id = $currentAdmission->session_id;
                                        $pay->branch_id = $currentAdmission->branch_id;
                                        $pay->admission_id = $currentAdmission->id;
                                        $pay->fees_assign_id = $FeesAssign->id;
                                        $pay->amount = $head['amount'];
                                        $pay->save();
                                        $collect_id = $pay->id;
                                    }
            
                                    // Create Fee Detail Entry
                                    $payDetail = new FeesDetail();
                                    $payDetail->user_id = $currentAdmission->user_id;
                                    $payDetail->session_id = $currentAdmission->session_id;
                                    $payDetail->branch_id = $currentAdmission->branch_id;
                                    $payDetail->fees_collect_id = $collect_id;
                                    $payDetail->fees_group_id = $head['fees_group_id'];
                                    $payDetail->receipt_no = $BillCounter->counter;
                                    $payDetail->admission_id = $currentAdmission->id;
                                    $payDetail->paid_amount = $head['amount'];
                                    $payDetail->installment_fine = 0;
                                    $payDetail->discount = 0;
                                    $payDetail->total_amount = $head['amount'];
                                    $payDetail->status = 0;
                                    $payDetail->date = date('Y-m-d');
                                    $payDetail->payment_mode_id = 1;
                                    $payDetail->save();
            
                                    $fees_details_id[] = $payDetail->id;
                                    $amount += $head['amount'];
                                }
                            }
            
                            if (!empty($fees_details_id)) {
                                $invoice = new FeesDetailsInvoices();
                                $invoice->user_id = $currentAdmission->user_id;
                                $invoice->session_id = $currentAdmission->session_id;
                                $invoice->branch_id = $currentAdmission->branch_id;
                                $invoice->fees_counter_id = 1;
                                $invoice->admission_id = $currentAdmission->id;
                                $invoice->fees_details_id = implode(',', $fees_details_id);
                                $invoice->payment_date = date('Y-m-d');
                                $invoice->payment_mode = 1;
                                $invoice->invoice_no = $BillCounter->counter;
                                $invoice->status = 0;
                                $invoice->amount = $amount;
                                $invoice->total_fine = 0;
                                $invoice->discount = 0;
                                $invoice->save();
                            }
                        }
                                            DB::table('temp_fees_collect')->where('id', $temp->id)->update(['status' => 1]);

                    }else{
                    DB::table('temp_fees_collect')->where('id', $temp->id)->update(['status' => 2]);
}
                    }else{
                    DB::table('temp_fees_collect')->where('id', $temp->id)->update(['status' => 3]);
}
                }
                //dd('sdwww');  
            }
        
}
