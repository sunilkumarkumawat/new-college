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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

            public function feeDashboard(Request $request){
                return view('fees/fee_dashboard');
            }
  
            public function addFees(Request $request){
                $admissionId = $request->admission_id;
                $directStudent = null;
                if (!empty($admissionId)) {
                    $directStudent = Admission::with('ClassTypes')->where('status', 1)->find($admissionId);
                } elseif (!empty($request->unique_system_id) && empty($request->course_id) && empty($request->batch) && empty($request->class_type_id) && empty($request->name) && empty($request->admission_no)) {
                    $directStudent = Admission::with('ClassTypes')->where('status', 1)->where('unique_system_id', $request->unique_system_id)->first();
                }

                $serach['name'] = $request->name ?? '';
                $serach['admission_no'] = $request->admission_no ?? ($directStudent->admissionNo ?? '');
                $serach['batch'] = $request->batch ?? ($directStudent->batch ?? '');
                $serach['course_id'] = $request->course_id ?? ($directStudent->ClassTypes->course_id ?? '');
                $serach['admission_type_id'] = $request->admission_type_id ?? '';
                $serach['class_type_id'] = !empty($request->class_type_id) ? $request->class_type_id : ($directStudent->class_type_id ?? 0);
                $serach['unique_system_id'] = $request->unique_system_id ?? ($directStudent->unique_system_id ?? '');
                $serach['admission_id'] = $admissionId ?? ($directStudent->id ?? '');
                
                $hasFilter = !empty($serach['class_type_id']) 
                    || !empty($serach['name']) 
                    || !empty($serach['admission_no']) 
                    || !empty($serach['batch']) 
                    || !empty($serach['course_id']) 
                    || !empty($serach['admission_type_id'])
                    || !empty($serach['unique_system_id'])
                    || !empty($serach['admission_id'])
                    || !empty($directStudent);

                if ($request->isMethod('post') || $hasFilter) {
                    $value = $serach['name'];
                    if ($serach['class_type_id'] > 0 || !empty($serach['name']) || !empty($serach['admission_no']) || !empty($serach['batch']) || !empty($serach['course_id']) || !empty($serach['unique_system_id']) || !empty($serach['admission_id'])) {
                        $data =  Admission::with('ClassTypes')->where('status', 1)->where('school','=',1);
                        $data = $data->where('branch_id', Session::get('branch_id'));
                        if (!empty($serach['course_id'])) {
                            $courseId = $serach['course_id'];
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
                       
                        if (!empty($serach['name'])) {
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
                        if (!empty($serach['batch'])) {
                            $data = $data->where("batch", $serach['batch']);
                        }
                        if (!empty($serach['class_type_id'])) {
                            $data = $data->where("class_type_id", $serach['class_type_id']);
                        }
                        if (!empty($serach['admission_type_id'])) {
                            $data = $data->where("admission_type_id", $serach['admission_type_id']);
                        }
                        if (!empty($serach['admission_no'])) {
                            $admVal = $serach['admission_no'];
                            $data = $data->where(function($q) use ($admVal) {
                                $q->where("admissions.admissionNo", $admVal)
                                  ->orWhere("admissions.id", $admVal);
                            });
                        } elseif (!empty($serach['admission_id'])) {
                            $data = $data->where("admissions.id", $serach['admission_id']);
                        }
                        $allstudents = $data->orderBy('id', 'ASC')->get();

                        if ($directStudent && $allstudents->where('id', $directStudent->id)->isEmpty()) {
                            // Only prepend directStudent if it matches any specified batch/course filters
                            $matchesBatch = empty($serach['batch']) || ($directStudent->batch == $serach['batch']);
                            $matchesCourse = empty($serach['course_id']) || (($directStudent->ClassTypes->course_id ?? null) == $serach['course_id']);
                            $matchesAdmNo = empty($serach['admission_no']) || ($directStudent->admissionNo == $serach['admission_no'] || $directStudent->id == $serach['admission_no']);
                            
                            if ($matchesBatch && $matchesCourse && $matchesAdmNo) {
                                $allstudents->prepend($directStudent);
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
                $serach['session_id'] = $request->has('session_id') ? $request->session_id : '';

                $srnoQuery = Admission::where('status', 1)->where('admission_type_id', 1)->where('branch_id', Session::get('branch_id'))->where('school','=',1)->whereNotNull('ledger_no');
                if (!empty($serach['session_id'])) {
                    $srnoQuery = $srnoQuery->where('session_id', $serach['session_id']);
                }
                $srno = $srnoQuery->orderBy('ledger_no')->get();

                $hasFilter = $request->isMethod('post') 
                    || !empty($serach['course_id']) 
                    || !empty($serach['batch']) 
                    || !empty($serach['class_type_id']) 
                    || !empty($serach['sr_no']) 
                    || !empty($serach['name'])
                    || (!empty($serach['session_id']) && $request->has('session_id'));

                if ($hasFilter) {
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

            public function quickAddAssignFeesGroup(Request $request)
            {
                $admissionId = $request->admission_id;
                $feeName = trim($request->fee_name ?? '');
                $amount = floatval($request->amount ?? 0);
                $dueDate = !empty($request->due_date) ? $request->due_date : null;
                $groupType = $request->group_type ?? null;
                $feesRefund = $request->fees_refund ?? 'no';
                $assignTarget = $request->assign_target ?? 'current_student';

                if (empty($feeName)) {
                    return response()->json(['status' => false, 'message' => 'Please enter a Fee Head / Group Name.'], 422);
                }
                if ($amount <= 0) {
                    return response()->json(['status' => false, 'message' => 'Please enter a valid amount greater than 0.'], 422);
                }

                $branchId = Session::get('branch_id');
                $userId = Session::get('id');

                $student = Admission::with('ClassTypes')->whereNull('deleted_at')->find($admissionId);
                if (!$student && !empty($request->unique_system_id)) {
                    $student = Admission::with('ClassTypes')->where('unique_system_id', $request->unique_system_id)->whereNull('deleted_at')->first();
                }
                if (!$student) {
                    return response()->json(['status' => false, 'message' => 'Student record not found.'], 404);
                }

                $activeSessionId = $request->session_id ?: ($student->session_id ?: Session::get('session_id'));
                $classTypeId = $student->class_type_id;
                $courseId = $student->ClassTypes->course_id ?? ($student->course_id ?? null);
                $batch = $student->batch ?: '2026';

                DB::beginTransaction();
                try {
                    // 1. Find or create FeesGroup
                    $fg = FeesGroup::where('branch_id', $branchId)
                        ->where('name', $feeName)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$fg) {
                        $fg = new FeesGroup;
                        $fg->user_id = $userId;
                        $fg->session_id = $activeSessionId;
                        $fg->branch_id = $branchId;
                        $fg->name = $feeName;
                    }
                    $fg->fees_refund = $feesRefund;
                    $fg->group_type = $groupType;
                    $fg->fees_type = 'full';
                    $fg->save();

                    // 2. Find or create FeesMaster
                    $fm = FeesMaster::where('branch_id', $branchId)
                        ->where('class_type_id', $classTypeId)
                        ->where('fees_group_id', $fg->id)
                        ->whereNull('deleted_at');
                    if (!empty($batch)) {
                        $fm->where('batch', $batch);
                    }
                    $fm = $fm->first();

                    if (!$fm) {
                        $fm = new FeesMaster;
                        $fm->user_id = $userId;
                        $fm->session_id = $activeSessionId;
                        $fm->branch_id = $branchId;
                        $fm->class_type_id = $classTypeId;
                        $fm->fees_group_id = $fg->id;
                        $fm->course_id = $courseId;
                        $fm->batch = $batch;
                    }
                    $fm->amount = $amount;
                    $fm->boys_amount = $request->filled('boys_amount') ? floatval($request->boys_amount) : $amount;
                    $fm->girls_amount = $request->filled('girls_amount') ? floatval($request->girls_amount) : $amount;
                    $fm->nri = $amount;
                    $fm->management = $amount;
                    $fm->govt = $amount;
                    $fm->installment_due_date = $dueDate;
                    $fm->save();

                    // 3. Assign to student(s)
                    $targetAdmissions = [];
                    if ($assignTarget === 'all_course_batch') {
                        $targetQuery = Admission::where('branch_id', $branchId)
                            ->where('class_type_id', $classTypeId)
                            ->whereNull('deleted_at')
                            ->where('status', 1);
                        if (!empty($batch)) {
                            $targetQuery->where('batch', $batch);
                        }
                        if (!empty($activeSessionId)) {
                            $targetQuery->where('session_id', $activeSessionId);
                        }
                        $targetAdmissions = $targetQuery->get();
                    } else {
                        $targetAdmissions = collect([$student]);
                    }

                    $assignedStudentsCount = 0;
                    foreach ($targetAdmissions as $adm) {
                        $feesAssign = FeesAssign::where('admission_id', $adm->id)->first();
                        if (!$feesAssign) {
                            $feesAssign = new FeesAssign;
                            $feesAssign->user_id = $userId;
                            $feesAssign->session_id = $adm->session_id ?: $activeSessionId;
                            $feesAssign->branch_id = $branchId;
                            $feesAssign->admission_id = $adm->id;
                            $feesAssign->total_amount = 0;
                            $feesAssign->net_amount = 0;
                            $feesAssign->save();
                        }

                        $assignDetail = FeesAssignDetail::withTrashed()
                            ->where('fees_assign_id', $feesAssign->id)
                            ->where('fees_group_id', $fg->id)
                            ->where('admission_id', $adm->id)
                            ->first();

                        if (!$assignDetail) {
                            $assignDetail = new FeesAssignDetail;
                            $assignDetail->user_id = $userId;
                            $assignDetail->branch_id = $branchId;
                            $assignDetail->session_id = $adm->session_id ?: $activeSessionId;
                            $assignDetail->admission_id = $adm->id;
                            $assignDetail->fees_assign_id = $feesAssign->id;
                            $assignDetail->class_type_id = $classTypeId;
                            $assignDetail->fees_master_id = $fm->id;
                            $assignDetail->fees_group_id = $fg->id;
                            $assignDetail->fees_refund = $feesRefund;
                            $assignDetail->installment_due_date = $dueDate;
                        } elseif ($assignDetail->trashed()) {
                            $assignDetail->restore();
                        }

                        $targetAmt = $amount;
                        $genderId = (int)($adm->gender_id ?? 0);
                        if ($genderId === 1 && !empty($fm->boys_amount)) {
                            $targetAmt = (float)$fm->boys_amount;
                        } elseif ($genderId === 2 && !empty($fm->girls_amount)) {
                            $targetAmt = (float)$fm->girls_amount;
                        }
                        $assignDetail->fees_group_amount = $targetAmt;
                        $assignDetail->save();

                        $totalAssign = FeesAssignDetail::where('admission_id', $adm->id)->whereNull('deleted_at')->sum('fees_group_amount');
                        $discountAssign = FeesAssignDetail::where('admission_id', $adm->id)->whereNull('deleted_at')->sum('discount');
                        $feesAssign->total_amount = $totalAssign;
                        $feesAssign->net_amount = ($totalAssign - $discountAssign);
                        $feesAssign->save();

                        $assignedStudentsCount++;
                    }

                    DB::commit();

                    $msg = $assignTarget === 'all_course_batch'
                        ? "Fee Head '{$feeName}' (₹{$amount}) assigned successfully to all {$assignedStudentsCount} students of this Course & Batch!"
                        : "Fee Head '{$feeName}' (₹{$amount}) assigned successfully to {$student->first_name}!";

                    return response()->json([
                        'status' => true,
                        'message' => $msg,
                        'fee_name' => $feeName,
                        'amount' => $amount,
                        'admission_id' => $student->id,
                        'unique_system_id' => $student->unique_system_id,
                        'session_id' => $activeSessionId,
                        'assigned_count' => $assignedStudentsCount
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Error saving fee head: ' . $e->getMessage()
                    ], 500);
                }
            }

            public function feesGroup(Request $request){
                $search['course_id'] = $request->course_id ?? '';
                $search['batch'] = $request->batch ?? '';
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['group_type'] = $request->group_type ?? '';
                $search['search_text'] = $request->search_text ?? '';

                $branch_id = Session::get('branch_id');
                $user_id = Session::get('id');
                $defaultSessionId = (int)Session::get('session_id');

                $setupReturnUrl = 'feesGroup?' . http_build_query(array_filter([
                    'course_id' => $request->course_id ?? '',
                    'batch' => $request->batch ?? '',
                    'class_type_id' => $request->single_class_type_id ?? ($request->class_type_id ?? ''),
                ], function ($value) { return $value !== '' && $value !== null; }));

                if ($request->isMethod('post')) {
                    $formMode = $request->form_mode ?? 'semester';
                    $batch = $request->batch ?: '2026';
                    $selectedCourseId = !empty($request->course_id) ? (int)$request->course_id : null;

                    // Case 1: Multiple Class / Semester Batch Assignment (with Amount & Due Date)
                    if ($formMode === 'semester' || ($request->has('class_type_id') && is_array($request->class_type_id) && $request->has('fee_name'))) {
                        $assignedCount = 0;
                        if ($request->has('class_type_id') && is_array($request->class_type_id)) {
                            foreach ($request->class_type_id as $k => $cTypeId) {
                                $cTypeId = (int)$cTypeId;
                                $feesMasterId = (int)($request->fees_master_id[$k] ?? 0);
                                $fName = trim($request->fee_name[$k] ?? '');
                                $amt = floatval($request->amount[$k] ?? 0);
                                $dueDate = !empty($request->due_date[$k]) ? $request->due_date[$k] : null;
                                $groupType = $request->sem_group_type ?? $request->group_type ?? null;

                                if (!empty($cTypeId) && !empty($fName)) {
                                    $clObj = ClassType::find($cTypeId);
                                    $rowCourseId = $selectedCourseId ?: ($clObj ? (int)$clObj->course_id : null);

                                    $fm = null;
                                    if ($feesMasterId > 0) {
                                        $fm = \App\Models\FeesMaster::where('id', $feesMasterId)
                                            ->where('branch_id', $branch_id)
                                            ->where('class_type_id', $cTypeId)
                                            ->whereNull('deleted_at')
                                            ->first();
                                    }

                                    if (!$fm) {
                                        $fm = \App\Models\FeesMaster::where('branch_id', $branch_id)
                                            ->where('class_type_id', $cTypeId)
                                            ->where('batch', $batch)
                                            ->whereNull('deleted_at')
                                            ->whereHas('feesGroup', function ($query) use ($fName) {
                                                $query->where('name', $fName)->whereNull('deleted_at');
                                            })
                                            ->first();
                                    }

                                    $fg = $fm ? FeesGroup::where('id', $fm->fees_group_id)
                                        ->where('branch_id', $branch_id)
                                        ->whereNull('deleted_at')
                                        ->first() : null;

                                    if (!$fg) {
                                        $fg = FeesGroup::where('branch_id', $branch_id)
                                            ->where('name', $fName)
                                            ->whereNull('deleted_at')
                                            ->first();
                                    }

                                    if (!$fg) {
                                        $fg = new FeesGroup;
                                        $fg->user_id = $user_id;
                                        $fg->session_id = $defaultSessionId;
                                        $fg->branch_id = $branch_id;
                                        $fg->name = $fName;
                                    }
                                    $fg->fees_refund = $request->fees_refund ?? 'no';
                                    $fg->fees_partial = $request->fees_partial ?? 0;
                                    $fg->group_type = $groupType;
                                    $fg->fees_type = 'full';
                                    $fg->save();

                                    if (!$fm) {
                                        $fm = new \App\Models\FeesMaster;
                                        $fm->user_id = $user_id;
                                        $fm->session_id = $defaultSessionId;
                                        $fm->branch_id = $branch_id;
                                        $fm->class_type_id = $cTypeId;
                                    }
                                    $fm->fees_group_id = $fg->id;
                                    $fm->course_id = $rowCourseId;
                                    $fm->batch = $batch;
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
                            return redirect::to('feesGroup?' . http_build_query(array_filter([
                                'course_id' => $selectedCourseId,
                                'batch' => $batch,
                            ])))->with('message', 'Fees Structure & Heads Saved Successfully for ' . $assignedCount . ' Classes/Semesters (Batch ' . $batch . ') !');
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
                        $targetClass = ClassType::where('id', $cTypeId)
                            ->where('branch_id', $branch_id)
                            ->whereNull('deleted_at')
                            ->first();
                        if (!$targetClass) {
                            return redirect::to('feesGroup')->with('error', 'Selected semester not found for this branch.');
                        }

                        $fg = null;
                        if (!empty($request->single_fees_group_id)) {
                            $fg = FeesGroup::where('id', (int)$request->single_fees_group_id)
                                ->where('branch_id', $branch_id)
                                ->whereNull('deleted_at')->first();
                        }
                        if (!$fg) {
                            $fg = FeesGroup::where('branch_id', $branch_id)
                                ->where('name', $fName)
                                ->whereNull('deleted_at')
                                ->first();
                        }
                        if (!$fg) {
                            $fg = new FeesGroup;
                            $fg->user_id = $user_id;
                            $fg->session_id = $defaultSessionId;
                            $fg->branch_id = $branch_id;
                            $fg->name = $fName;
                        }
                        $fg->fees_refund = $request->fees_refund ?? 'no';
                        $fg->fees_partial = $request->fees_partial ?? 0;
                        $fg->group_type = $groupType;
                        $fg->fees_type = 'full';
                        $fg->save();

                        $fm = \App\Models\FeesMaster::where('branch_id', $branch_id)
                            ->where('class_type_id', $cTypeId)
                            ->where('fees_group_id', $fg->id)
                            ->where('batch', $request->batch ?: '2026')
                            ->whereNull('deleted_at')
                            ->first();

                        if (!$fm) {
                            $fm = new \App\Models\FeesMaster;
                            $fm->user_id = $user_id;
                            $fm->session_id = $defaultSessionId;
                            $fm->branch_id = $branch_id;
                            $fm->class_type_id = $cTypeId;
                            $fm->fees_group_id = $fg->id;
                        }
                        $fm->course_id = $targetClass->course_id;
                        $fm->batch = $request->batch ?: '2026';
                        $fm->amount = $amt;
                        $fm->nri = $amt;
                        $fm->management = $amt;
                        $fm->govt = $amt;
                        $fm->installment_due_date = $dueDate;
                        $fm->editable = 0;
                        $fm->save();
                        return redirect::to('feesGroup?' . http_build_query([
                            'course_id' => $targetClass->course_id,
                            'batch' => $request->batch ?: '2026',
                            'class_type_id' => $cTypeId,
                        ]))->with('message', 'Fee Head & Class Fee Structure Saved Successfully!');
                    }

                    // Case 3: Fee Heads Batch / Single Creation
                    $fName = trim($request->head_only_name ?? $request->name ?? '');
                    $groupType = $request->head_only_group_type ?? $request->group_type ?? null;

                    if (empty($fName)) {
                        return redirect::to('feesGroup')->with('error', 'Please enter a valid Fee Head Name!');
                    }

                    $exists = FeesGroup::where('branch_id', $branch_id)
                                       ->where('name', $fName)
                                       ->whereNull('deleted_at')
                                       ->first();
                    if (!$exists) {
                        $fees_group = new FeesGroup;
                        $fees_group->user_id = $user_id;
                        $fees_group->session_id = $defaultSessionId;
                        $fees_group->branch_id = $branch_id;
                        $fees_group->name = $fName;
                        $fees_group->fees_refund = $request->fees_refund ?? 'no';
                        $fees_group->fees_partial = $request->fees_partial ?? 0;
                        $fees_group->group_type = $groupType;
                        $fees_group->fees_type = 'full';
                        $fees_group->save();
                        return redirect::to($setupReturnUrl)->with('message', 'Fee head created. Select a semester to assign its amount and due date.');
                    } else {
                        return redirect::to($setupReturnUrl)->with('error', 'Fee head already exists in Master Library.');
                    }
                }

                // Query for Fee Groups (Master Heads)
                $query = FeesGroup::where('branch_id', $branch_id)
                    ->whereNull('deleted_at');
                if (!empty($search['group_type'])) {
                    $query->where('group_type', $search['group_type']);
                }
                if (!empty($search['search_text'])) {
                    $query->where('name', 'like', '%' . $search['search_text'] . '%');
                }
                $fees_group_list = $query->orderBy('name', 'ASC')->get()->unique('name');

                // Query for Fees Master
                $feesMasterQuery = \App\Models\FeesMaster::with(['feesGroup', 'ClassTypes.course'])
                    ->where('branch_id', $branch_id)
                    ->whereNull('deleted_at');

                if (!empty($search['batch'])) {
                    $feesMasterQuery->where('batch', $search['batch']);
                }
                if (!empty($search['course_id'])) {
                    $course_id = $search['course_id'];
                    $feesMasterQuery->where(function($q) use ($course_id) {
                        $q->where('course_id', $course_id)
                          ->orWhereHas('ClassTypes', function($cq) use ($course_id) {
                              $cq->where('course_id', $course_id);
                          });
                    });
                }
                if (!empty($search['class_type_id'])) {
                    $feesMasterQuery->where('class_type_id', $search['class_type_id']);
                }

                $allFeesMasterRows = (clone $feesMasterQuery)->orderBy('class_type_id')->orderBy('id')->get();
                $fees_master_list = (clone $feesMasterQuery)->groupBy('class_type_id')->get();

                $courses = Helper::getCourses();
                $allBranchCourses = \App\Models\Master\Course::where('branch_id', $branch_id)
                    ->orderBy('name', 'ASC')
                    ->get();

                // Distinct unique classes per course for this branch
                $allBranchClasses = ClassType::where('branch_id', $branch_id)
                    ->whereNull('deleted_at')
                    ->orderBy('orderBy', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->unique(function($item) {
                        return $item->course_id . '_' . $item->name;
                    });

                $allBranchMasters = \App\Models\FeesMaster::with('feesGroup', 'ClassTypes')
                    ->where('branch_id', $branch_id)
                    ->whereNull('deleted_at')
                    ->get();

                // Group masters by class_type_id and course_id+class_name
                $mastersByClass = [];
                foreach ($allBranchMasters as $bm) {
                    $mastersByClass[$bm->class_type_id][] = $bm;
                    $cName = optional($bm->ClassTypes)->name;
                    $cId = $bm->course_id ?: optional($bm->ClassTypes)->course_id;
                    if ($cId && $cName) {
                        $mastersByClass[$cId . '_' . $cName][] = $bm;
                    }
                }

                // Build Structured Course -> Semester -> Fees Heads Hierarchy
                $structuredCourses = [];
                $totalConfiguredSemesters = 0;

                foreach ($allBranchCourses as $c) {
                    $courseClasses = $allBranchClasses->where('course_id', $c->id);
                    $cSemesters = [];
                    $courseTotal = 0;
                    $cConfiguredCount = 0;

                    foreach ($courseClasses as $cls) {
                        // Find heads mapped to this class ID or to course + semester name
                        $cHeads = $mastersByClass[$cls->id] ?? ($mastersByClass[$c->id . '_' . $cls->name] ?? []);
                        // Deduplicate multiple rows of same master ID
                        $cHeads = collect($cHeads)->unique('id');
                        $semTotal = 0;
                        $headsList = [];

                        foreach ($cHeads as $h) {
                            $amt = (float)($h->amount ?? 0);
                            $boysAmt = !empty($h->boys_amount) ? (float)$h->boys_amount : $amt;
                            $girlsAmt = !empty($h->girls_amount) ? (float)$h->girls_amount : $amt;
                            $semTotal += $amt;
                            $headsList[] = [
                                'fees_master_id' => $h->id,
                                'fees_group_id' => (int)$h->fees_group_id,
                                'batch' => $h->batch ?: '2026',
                                'head_name' => optional($h->feesGroup)->name ?: 'Fee Head',
                                'category' => optional($h->feesGroup)->group_type ?: 'compulsory',
                                'amount' => $amt,
                                'boys_amount' => $boysAmt,
                                'girls_amount' => $girlsAmt,
                                'due_date' => !empty($h->installment_due_date) ? date('Y-m-d', strtotime($h->installment_due_date)) : '',
                                'due_date_formatted' => !empty($h->installment_due_date) ? date('d-m-Y', strtotime($h->installment_due_date)) : '—',
                            ];
                        }

                        if (count($headsList) > 0) {
                            $cConfiguredCount++;
                            $totalConfiguredSemesters++;
                        }
                        $courseTotal += $semTotal;

                        $cSemesters[] = [
                            'class_type_id' => $cls->id,
                            'name' => $cls->name,
                            'order_by' => $cls->orderBy,
                            'total_fee' => $semTotal,
                            'heads_count' => count($headsList),
                            'heads' => $headsList,
                        ];
                    }

                    $structuredCourses[] = [
                        'course_id' => $c->id,
                        'name' => $c->name,
                        'duration' => $c->duration,
                        'course_type' => $c->course_type,
                        'semesters_count' => $courseClasses->count(),
                        'configured_count' => $cConfiguredCount,
                        'total_course_fee' => $courseTotal,
                        'semesters' => $cSemesters,
                    ];
                }

                $masterFeesGroups = FeesGroup::where('branch_id', $branch_id)
                    ->whereNull('deleted_at')
                    ->orderBy('name', 'ASC')
                    ->get()
                    ->unique('name');

                $adBatches = Admission::whereNotNull('batch')->where('batch', '!=', '')->distinct()->pluck('batch')->toArray();
                $fmBatches = \App\Models\FeesMaster::whereNotNull('batch')->where('batch', '!=', '')->distinct()->pluck('batch')->toArray();
                $currYear = (int)date('Y');
                $fallbackBatches = [(string)($currYear - 1), (string)$currYear, (string)($currYear + 1), '2026'];
                $availableBatches = array_values(array_unique(array_filter(array_merge($adBatches, $fmBatches, $fallbackBatches))));
                sort($availableBatches);

                return view('fees.fees.feesGroup_simple', [
                    'dataview' => $fees_group_list,
                    'feesMasterList' => $fees_master_list,
                    'allFeesMasterRows' => $allFeesMasterRows,
                    'courses' => $courses,
                    'classType' => $allBranchClasses,
                    'allClassType' => $allBranchClasses,
                    'getFeesGroup' => Helper::getFeesGroup(),
                    'masterFeesGroups' => $masterFeesGroups,
                    'search' => $search,
                    'structuredCourses' => $structuredCourses,
                    'totalConfiguredSemesters' => $totalConfiguredSemesters,
                    'allBranchCourses' => $allBranchCourses,
                    'availableBatches' => $availableBatches,
                ]);
            }

            public function saveSemesterFeeHead(Request $request)
            {
                try {
                    $branch_id = Session::get('branch_id');
                    $user_id = Session::get('id');
                    $defaultSessionId = (int)Session::get('session_id');
                    $class_type_id = (int)$request->class_type_id;
                    $category = $request->new_group_type ?? ($request->group_type ?? 'Compulsory');
                    $amount = floatval($request->amount ?? 0);
                    $dueDate = $request->installment_due_date ?? ($request->due_date ?? null);
                    $batch = $request->filled('batch') ? trim($request->batch) : '2026';

                    if (empty($class_type_id)) {
                        return response()->json(['status' => false, 'message' => 'Please select a valid Semester / Class.'], 422);
                    }

                    $fee_name = '';
                    $fg = null;
                    if ($request->filled('fees_group_id')) {
                        $fg = FeesGroup::where('id', $request->fees_group_id)
                            ->where('branch_id', $branch_id)
                            ->first();

                        if ($fg) {
                            $fee_name = $fg->name;
                            $category = $fg->group_type ?? $category;
                        }
                    }

                    if (empty($fee_name)) {
                        $fee_name = trim($request->new_group_name ?? ($request->fee_name ?? ''));
                    }

                    if (empty($fee_name)) {
                        return response()->json(['status' => false, 'message' => 'Fee Head Name is required.'], 422);
                    }

                    $cls = ClassType::where('id', $class_type_id)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->first();
                    if (!$cls) {
                        return response()->json(['status' => false, 'message' => 'Semester not found for this branch.'], 404);
                    }

                    // Find or create FeesGroup
                    if (!$fg) {
                        $fg = FeesGroup::where('branch_id', $branch_id)
                            ->where('name', $fee_name)
                            ->whereNull('deleted_at')
                            ->first();
                    }

                    if (!$fg) {
                        $fg = new FeesGroup;
                        $fg->user_id = $user_id;
                        $fg->session_id = $defaultSessionId;
                        $fg->branch_id = $branch_id;
                        $fg->name = $fee_name;
                        $fg->group_type = $category;
                        $fg->fees_refund = 'no';
                        $fg->fees_partial = 0;
                        $fg->fees_type = 'full';
                        $fg->save();
                    }

                    // Find or create FeesMaster by branch_id, class_type_id, fees_group_id, batch
                    $tFm = FeesMaster::where('branch_id', $branch_id)
                        ->where('class_type_id', $class_type_id)
                        ->where('fees_group_id', $fg->id)
                        ->where('batch', $batch)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$tFm) {
                        $tFm = new FeesMaster;
                        $tFm->user_id = $user_id;
                        $tFm->session_id = $defaultSessionId;
                        $tFm->branch_id = $branch_id;
                        $tFm->class_type_id = $class_type_id;
                        $tFm->fees_group_id = $fg->id;
                    }

                    $tFm->course_id = $cls->course_id;
                    $tFm->batch = $batch;
                    $tFm->amount = $amount;
                    $tFm->boys_amount = $request->filled('boys_amount') ? floatval($request->boys_amount) : $amount;
                    $tFm->girls_amount = $request->filled('girls_amount') ? floatval($request->girls_amount) : $amount;
                    $tFm->nri = $amount;
                    $tFm->management = $amount;
                    $tFm->govt = $amount;
                    $tFm->installment_due_date = $dueDate;
                    $tFm->editable = 0;
                    $tFm->save();

                    // Recalculate semester heads and total
                    $updatedHeads = FeesMaster::with('feesGroup')
                        ->where('branch_id', $branch_id)
                        ->where('class_type_id', $class_type_id)
                        ->where('batch', $batch)
                        ->whereNull('deleted_at')
                        ->get();

                    $semTotal = 0;
                    $headsFormatted = [];
                    foreach ($updatedHeads as $h) {
                        $amt = floatval($h->amount ?? 0);
                        $semTotal += $amt;
                        $headsFormatted[] = [
                            'id' => $h->id,
                            'fees_master_id' => $h->id,
                            'fees_group_id' => (int)$h->fees_group_id,
                            'name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'head_name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'category' => optional($h->feesGroup)->group_type ?: 'Compulsory',
                            'amount' => $amt,
                            'boys_amount' => !empty($h->boys_amount) ? (float)$h->boys_amount : $amt,
                            'girls_amount' => !empty($h->girls_amount) ? (float)$h->girls_amount : $amt,
                            'batch' => $h->batch ?: $batch,
                            'due_date' => !empty($h->installment_due_date) ? date('Y-m-d', strtotime($h->installment_due_date)) : '',
                            'due_date_formatted' => !empty($h->installment_due_date) ? date('d-m-Y', strtotime($h->installment_due_date)) : '—',
                        ];
                    }

                    // Recalculate whole course total
                    $courseSemTotal = FeesMaster::where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->where('batch', $batch)
                        ->where(function($q) use ($cls) {
                            $q->where('course_id', $cls->course_id)
                              ->orWhereHas('ClassTypes', function($cq) use ($cls) {
                                  $cq->where('course_id', $cls->course_id);
                              });
                        })
                        ->sum('amount');

                    return response()->json([
                        'status' => true,
                        'has_heads' => count($headsFormatted) > 0,
                        'message' => 'Fee Head saved successfully to ' . $cls->name . ' for Batch ' . $batch . '!',
                        'class_type_id' => $class_type_id,
                        'class_name' => $cls->name,
                        'batch' => $batch,
                        'course_id' => $cls->course_id,
                        'total_fee' => $semTotal,
                        'semester_total' => $semTotal,
                        'course_total' => (float)$courseSemTotal,
                        'heads_count' => count($headsFormatted),
                        'heads' => $headsFormatted,
                        'added_head_name' => $fee_name,
                        'added_head_id' => $fg->id,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
            }

            public function updateSemesterFeeHead(Request $request)
            {
                try {
                    $id = (int)$request->fees_master_id;
                    $fm = FeesMaster::with(['feesGroup', 'ClassTypes'])->find($id);
                    if (!$fm) {
                        return response()->json(['status' => false, 'message' => 'Fee Head record not found.'], 404);
                    }

                    $amount = floatval($request->amount ?? 0);
                    $dueDate = $request->installment_due_date ?? ($request->due_date ?? null);
                    $fm->amount = $amount;
                    $fm->boys_amount = $request->filled('boys_amount') ? floatval($request->boys_amount) : $amount;
                    $fm->girls_amount = $request->filled('girls_amount') ? floatval($request->girls_amount) : $amount;
                    $fm->nri = $amount;
                    $fm->management = $amount;
                    $fm->govt = $amount;
                    $fm->installment_due_date = $dueDate;
                    if ($request->filled('batch')) {
                        $fm->batch = trim($request->batch);
                    }
                    if (empty($fm->course_id) && $fm->ClassTypes) {
                        $fm->course_id = $fm->ClassTypes->course_id;
                    }
                    $fm->save();

                    if ($request->filled('fee_name')) {
                        $newName = trim($request->fee_name);
                        if ($fm->feesGroup && $fm->feesGroup->name !== $newName) {
                            $existing = FeesGroup::where('branch_id', $fm->branch_id)
                                ->where('name', $newName)
                                ->whereNull('deleted_at')
                                ->first();
                            if ($existing) {
                                $fm->fees_group_id = $existing->id;
                                $fm->save();
                            } else {
                                $newFg = new FeesGroup;
                                $newFg->user_id = Session::get('id');
                                $newFg->session_id = Session::get('session_id');
                                $newFg->branch_id = $fm->branch_id;
                                $newFg->name = $newName;
                                $newFg->group_type = $request->group_type ?? ($fm->feesGroup->group_type ?? 'academic');
                                $newFg->fees_refund = 'no';
                                $newFg->fees_partial = 0;
                                $newFg->fees_type = 'full';
                                $newFg->save();
                                $fm->fees_group_id = $newFg->id;
                                $fm->save();
                            }
                        }
                    }

                    // Recalculate
                    $updatedHeads = FeesMaster::with('feesGroup')
                        ->where('branch_id', $fm->branch_id)
                        ->where('class_type_id', $fm->class_type_id)
                        ->where('batch', $fm->batch)
                        ->whereNull('deleted_at')
                        ->get();

                    $semTotal = 0;
                    $headsFormatted = [];
                    foreach ($updatedHeads as $h) {
                        $amt = floatval($h->amount ?? 0);
                        $semTotal += $amt;
                        $headsFormatted[] = [
                            'id' => $h->id,
                            'fees_master_id' => $h->id,
                            'fees_group_id' => (int)$h->fees_group_id,
                            'name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'head_name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'category' => optional($h->feesGroup)->group_type ?: 'academic',
                            'amount' => $amt,
                            'boys_amount' => !empty($h->boys_amount) ? (float)$h->boys_amount : $amt,
                            'girls_amount' => !empty($h->girls_amount) ? (float)$h->girls_amount : $amt,
                            'batch' => $h->batch ?: $fm->batch,
                            'due_date' => !empty($h->installment_due_date) ? date('Y-m-d', strtotime($h->installment_due_date)) : '',
                            'due_date_formatted' => !empty($h->installment_due_date) ? date('d-m-Y', strtotime($h->installment_due_date)) : '—',
                        ];
                    }

                    $courseId = $fm->course_id ?: optional($fm->ClassTypes)->course_id;
                    $courseSemTotal = FeesMaster::where('branch_id', $fm->branch_id)
                        ->whereNull('deleted_at')
                        ->where('batch', $fm->batch)
                        ->where(function($q) use ($courseId) {
                            $q->where('course_id', $courseId)
                              ->orWhereHas('ClassTypes', function($cq) use ($courseId) {
                                  $cq->where('course_id', $courseId);
                              });
                        })
                        ->sum('amount');

                    return response()->json([
                        'status' => true,
                        'message' => 'Fee Head updated successfully!',
                        'class_type_id' => $fm->class_type_id,
                        'course_id' => $courseId,
                        'semester_total' => $semTotal,
                        'course_total' => (float)$courseSemTotal,
                        'heads_count' => count($headsFormatted),
                        'heads' => $headsFormatted,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
            }

            public function deleteSemesterFeeHead(Request $request)
            {
                try {
                    $id = (int)$request->fees_master_id;
                    $fm = FeesMaster::with('ClassTypes')->find($id);
                    if (!$fm) {
                        return response()->json(['status' => false, 'message' => 'Fee Head record not found.'], 404);
                    }

                    $collectedQuery = \App\Models\FeesDetail::with(['Admission', 'ClassTypes'])
                        ->where('fees_group_id', $fm->fees_group_id)
                        ->where('branch_id', $fm->branch_id)
                        ->where('paid_amount', '>', 0)
                        ->whereNull('deleted_at');

                    $isCollected = $collectedQuery->exists();

                    if ($isCollected) {
                        $collections = $collectedQuery->orderBy('id', 'DESC')->limit(15)->get();
                        $totalCollectedSum = (float)\App\Models\FeesDetail::where('fees_group_id', $fm->fees_group_id)
                            ->where('branch_id', $fm->branch_id)
                            ->where('paid_amount', '>', 0)
                            ->whereNull('deleted_at')
                            ->sum('paid_amount');

                        $collectedStudents = [];
                        foreach ($collections as $cd) {
                            $collectedStudents[] = [
                                'invoice_no' => $cd->invoice_no ?? ('INV-' . $cd->id),
                                'student_name' => optional($cd->Admission)->first_name . ' ' . optional($cd->Admission)->last_name,
                                'admission_no' => optional($cd->Admission)->admissionNo ?? '-',
                                'paid_amount' => (float)$cd->paid_amount,
                                'date' => !empty($cd->date) ? date('d-m-Y', strtotime($cd->date)) : (!empty($cd->created_at) ? $cd->created_at->format('d-m-Y') : '—'),
                                'payment_mode' => $cd->payment_mode_id ?? 'Cash'
                            ];
                        }

                        return response()->json([
                            'status' => false,
                            'has_collected' => true,
                            'head_name' => optional($fm->feesGroup)->name ?: 'Fee Head',
                            'class_name' => optional($fm->ClassTypes)->name ?: 'Semester',
                            'total_collected' => $totalCollectedSum,
                            'collected_count' => count($collectedStudents),
                            'collections' => $collectedStudents,
                            'message' => 'Cannot delete: Fees have already been collected from student(s) for this Fee Head!'
                        ], 422);
                    }

                    $class_type_id = $fm->class_type_id;
                    $branch_id = $fm->branch_id;
                    $batch = $fm->batch;
                    $course_id = $fm->course_id ?: optional($fm->ClassTypes)->course_id;

                    $fm->delete();

                    $updatedHeads = FeesMaster::with('feesGroup')
                        ->where('branch_id', $branch_id)
                        ->where('class_type_id', $class_type_id)
                        ->where('batch', $batch)
                        ->whereNull('deleted_at')
                        ->get();

                    $semTotal = 0;
                    $headsFormatted = [];
                    foreach ($updatedHeads as $h) {
                        $amt = floatval($h->amount ?? 0);
                        $semTotal += $amt;
                        $headsFormatted[] = [
                            'fees_master_id' => $h->id,
                            'fees_group_id' => (int)$h->fees_group_id,
                            'head_name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'category' => optional($h->feesGroup)->group_type ?: 'academic',
                            'amount' => $amt,
                            'batch' => $h->batch ?: $batch,
                            'due_date' => !empty($h->installment_due_date) ? date('Y-m-d', strtotime($h->installment_due_date)) : '',
                            'due_date_formatted' => !empty($h->installment_due_date) ? date('d-m-Y', strtotime($h->installment_due_date)) : '—',
                        ];
                    }

                    $courseSemTotal = FeesMaster::where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->where('batch', $batch)
                        ->where(function($q) use ($course_id) {
                            $q->where('course_id', $course_id)
                              ->orWhereHas('ClassTypes', function($cq) use ($course_id) {
                                  $cq->where('course_id', $course_id);
                              });
                        })
                        ->sum('amount');

                    return response()->json([
                        'status' => true,
                        'message' => 'Fee Head removed from semester successfully.',
                        'class_type_id' => $class_type_id,
                        'course_id' => $course_id,
                        'semester_total' => $semTotal,
                        'course_total' => (float)$courseSemTotal,
                        'heads_count' => count($headsFormatted),
                        'heads' => $headsFormatted,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
            }

            public function copySemesterFees(Request $request)
            {
                try {
                    $fromClassId = (int)($request->from_class_type_id ?? $request->source_class_type_id);
                    $toClassId = (int)($request->to_class_type_id ?? $request->target_class_type_id);
                    $branch_id = Session::get('branch_id');
                    $user_id = Session::get('id');
                    $defaultSessionId = (int)Session::get('session_id');
                    $batch = $request->batch ?: '2026';

                    if (empty($fromClassId) || empty($toClassId)) {
                        return response()->json(['status' => false, 'message' => 'Please select both source and target semesters.'], 422);
                    }
                    if ($fromClassId === $toClassId) {
                        return response()->json(['status' => false, 'message' => 'Source and target semester cannot be the same.'], 422);
                    }

                    $sourceHeads = FeesMaster::where('branch_id', $branch_id)
                        ->where('class_type_id', $fromClassId)
                        ->whereNull('deleted_at')
                        ->get();

                    if ($sourceHeads->isEmpty()) {
                        return response()->json(['status' => false, 'message' => 'Source semester has no fee heads to copy.'], 422);
                    }

                    $targetClass = ClassType::where('id', $toClassId)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->first();
                    if (!$targetClass) {
                        return response()->json(['status' => false, 'message' => 'Target semester not found for this branch.'], 404);
                    }

                    $copiedCount = 0;
                    foreach ($sourceHeads as $src) {
                        $targetBatch = $request->batch ?: ($src->batch ?: '2026');
                        $existing = FeesMaster::where('branch_id', $branch_id)
                            ->where('class_type_id', $toClassId)
                            ->where('fees_group_id', $src->fees_group_id)
                            ->where('batch', $targetBatch)
                            ->whereNull('deleted_at')
                            ->first();

                        if (!$existing) {
                            $newFm = new FeesMaster;
                            $newFm->user_id = $user_id;
                            $newFm->session_id = $defaultSessionId;
                            $newFm->branch_id = $branch_id;
                            $newFm->class_type_id = $toClassId;
                            $newFm->course_id = $targetClass->course_id;
                            $newFm->batch = $targetBatch;
                            $newFm->fees_group_id = $src->fees_group_id;
                            $newFm->amount = $src->amount;
                            $newFm->boys_amount = !empty($src->boys_amount) ? $src->boys_amount : $src->amount;
                            $newFm->girls_amount = !empty($src->girls_amount) ? $src->girls_amount : $src->amount;
                            $newFm->nri = $src->nri ?? $src->amount;
                            $newFm->management = $src->management ?? $src->amount;
                            $newFm->govt = $src->govt ?? $src->amount;
                            $newFm->installment_due_date = $src->installment_due_date;
                            $newFm->editable = 0;
                            $newFm->save();
                            $copiedCount++;
                        }
                    }

                    $updatedHeads = FeesMaster::with('feesGroup')
                        ->where('branch_id', $branch_id)
                        ->where('class_type_id', $toClassId)
                        ->where('batch', $batch)
                        ->whereNull('deleted_at')
                        ->get();

                    $semTotal = 0;
                    $headsFormatted = [];
                    foreach ($updatedHeads as $h) {
                        $amt = floatval($h->amount ?? 0);
                        $semTotal += $amt;
                        $headsFormatted[] = [
                            'id' => $h->id,
                            'fees_master_id' => $h->id,
                            'fees_group_id' => (int)$h->fees_group_id,
                            'head_name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'category' => optional($h->feesGroup)->group_type ?: 'academic',
                            'amount' => $amt,
                            'boys_amount' => !empty($h->boys_amount) ? (float)$h->boys_amount : $amt,
                            'girls_amount' => !empty($h->girls_amount) ? (float)$h->girls_amount : $amt,
                            'batch' => $h->batch ?: $batch,
                            'due_date' => !empty($h->installment_due_date) ? date('Y-m-d', strtotime($h->installment_due_date)) : '',
                            'due_date_formatted' => !empty($h->installment_due_date) ? date('d-m-Y', strtotime($h->installment_due_date)) : '—',
                        ];
                    }

                    $courseSemTotal = FeesMaster::where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->where('batch', $batch)
                        ->where(function($q) use ($targetClass) {
                            $q->where('course_id', $targetClass->course_id)
                              ->orWhereHas('ClassTypes', function($cq) use ($targetClass) {
                                  $cq->where('course_id', $targetClass->course_id);
                              });
                        })
                        ->sum('amount');

                    return response()->json([
                        'status' => true,
                        'message' => "Successfully copied {$copiedCount} fee head(s) to {$targetClass->name}!",
                        'copied_count' => $copiedCount,
                        'class_type_id' => $toClassId,
                        'course_id' => $targetClass->course_id,
                        'semester_total' => $semTotal,
                        'course_total' => (float)$courseSemTotal,
                        'heads_count' => count($headsFormatted),
                        'heads' => $headsFormatted,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
            }

            public function saveMasterFeeHead(Request $request)
            {
                try {
                    $branch_id = Session::get('branch_id');
                    $name = trim($request->name ?? '');
                    $category = $request->group_type ?? 'academic';

                    if (empty($name)) {
                        return response()->json(['status' => false, 'message' => 'Fee Head name is required.'], 422);
                    }

                    $existing = FeesGroup::where('branch_id', $branch_id)
                        ->where('name', $name)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($existing) {
                        return response()->json(['status' => false, 'message' => 'Fee Head with this name already exists in Master Library.'], 422);
                    }

                    $fg = new FeesGroup;
                    $fg->user_id = Session::get('id');
                    $fg->session_id = Session::get('session_id');
                    $fg->branch_id = $branch_id;
                    $fg->name = $name;
                    $fg->group_type = $category;
                    $fg->fees_refund = 'no';
                    $fg->fees_partial = 0;
                    $fg->fees_type = 'full';
                    $fg->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Fee Head created in Master Library successfully!',
                        'head' => [
                            'id' => $fg->id,
                            'name' => $fg->name,
                            'category' => $fg->group_type,
                        ]
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
            }

            public function deleteMasterFeeHead(Request $request)
            {
                try {
                    $id = (int)$request->fees_group_id;
                    $fg = FeesGroup::find($id);
                    if (!$fg) {
                        return response()->json(['status' => false, 'message' => 'Fee Head not found.'], 404);
                    }

                    $isUsedInMasters = FeesMaster::where('fees_group_id', $id)->whereNull('deleted_at')->exists();
                    $collectedQuery = \App\Models\FeesDetail::with(['Admission', 'ClassTypes'])
                        ->where('fees_group_id', $id)
                        ->where('paid_amount', '>', 0)
                        ->whereNull('deleted_at');
                    $isUsedInCollections = $collectedQuery->exists();

                    if ($isUsedInCollections) {
                        $collections = $collectedQuery->orderBy('id', 'DESC')->limit(15)->get();
                        $totalCollectedSum = (float)\App\Models\FeesDetail::where('fees_group_id', $id)
                            ->where('paid_amount', '>', 0)
                            ->whereNull('deleted_at')
                            ->sum('paid_amount');

                        $collectedStudents = [];
                        foreach ($collections as $cd) {
                            $collectedStudents[] = [
                                'invoice_no' => $cd->invoice_no ?? ('INV-' . $cd->id),
                                'student_name' => optional($cd->Admission)->first_name . ' ' . optional($cd->Admission)->last_name,
                                'admission_no' => optional($cd->Admission)->admissionNo ?? '-',
                                'paid_amount' => (float)$cd->paid_amount,
                                'date' => !empty($cd->date) ? date('d-m-Y', strtotime($cd->date)) : (!empty($cd->created_at) ? $cd->created_at->format('d-m-Y') : '—'),
                                'payment_mode' => $cd->payment_mode_id ?? 'Cash'
                            ];
                        }

                        return response()->json([
                            'status' => false,
                            'has_collected' => true,
                            'head_name' => $fg->name,
                            'class_name' => 'All Semesters',
                            'total_collected' => $totalCollectedSum,
                            'collected_count' => count($collectedStudents),
                            'collections' => $collectedStudents,
                            'message' => 'Cannot delete: Fees have already been collected from student(s) for this Fee Head!'
                        ], 422);
                    }

                    if ($isUsedInMasters) {
                        return response()->json([
                            'status' => false,
                            'has_collected' => false,
                            'message' => 'Cannot delete: This Fee Head is currently assigned to one or more semesters. Please remove it from semesters first.'
                        ], 422);
                    }

                    $fg->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'Fee Head deleted from Master Library successfully.',
                        'fees_group_id' => $id,
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
                }
            }

            public function getSemesterFeeStatus(Request $request)
            {
                try {
                    $branch_id = Session::get('branch_id');
                    $class_type_id = (int)$request->class_type_id;
                    $session_id = $request->filled('session_id') ? (int)$request->session_id : (int)Session::get('session_id');
                    $batch = $request->filled('batch') ? trim($request->batch) : null;
                    $course_id = $request->filled('course_id') ? (int)$request->course_id : null;

                    if (empty($class_type_id)) {
                        return response()->json(['status' => false, 'message' => 'Semester ID is required.'], 422);
                    }

                    $classObj = ClassType::with('course')->find($class_type_id);
                    if (!$classObj) {
                        return response()->json(['status' => false, 'message' => 'Semester not found.'], 404);
                    }
                    $branch_id = !empty($classObj->branch_id) ? (int)$classObj->branch_id : (int)Session::get('branch_id');
                    if (!$course_id && $classObj->course_id) {
                        $course_id = (int)$classObj->course_id;
                    }

                    // Resolve fee heads batch-wise using Course + Batch + Semester architecture
                    $heads = \App\Helpers\helper::getBatchSemesterFeeMasters($branch_id, $course_id, $class_type_id, $batch, $session_id);

                    $headsList = [];
                    $totalFee = 0;
                    foreach ($heads as $h) {
                        $amt = (float)($h->amount ?? 0);
                        $totalFee += $amt;
                        $headsList[] = [
                            'id' => $h->id,
                            'name' => optional($h->feesGroup)->name ?: 'Fee Head',
                            'category' => optional($h->feesGroup)->group_type ?: 'Compulsory',
                            'amount' => $amt,
                            'due_date' => $h->installment_due_date ? date('d-m-Y', strtotime($h->installment_due_date)) : '—'
                        ];
                    }

                    return response()->json([
                        'status' => true,
                        'has_heads' => count($headsList) > 0,
                        'heads_count' => count($headsList),
                        'total_fee' => $totalFee,
                        'heads' => $headsList,
                        'class_name' => $classObj->name ?? 'Selected Semester',
                        'course_id' => $course_id,
                        'course_name' => optional($classObj->course)->name ?: '',
                        'session_id' => $session_id,
                        'batch' => $batch
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
                }
            }

            public function getFeesStructureSessionSummary(Request $request){
                try {
                    $branch_id = Session::get('branch_id');
                    $session_id = $request->session_id;

                    if (empty($session_id)) {
                        return response()->json(['status' => 'error', 'message' => 'Invalid session ID provided']);
                    }

                    $sessionObj = \App\Models\Sessions::find($session_id);
                    $sessionName = $sessionObj ? (($sessionObj->from_year ?? '') . ' - ' . ($sessionObj->to_year ?? '')) : ('Session #' . $session_id);

                    // Fee groups in this session
                    $feeGroups = FeesGroup::where('branch_id', $branch_id)
                        ->where('session_id', $session_id)
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'ASC')
                        ->get();

                    // Fees masters in this session
                    $feesMasters = \App\Models\FeesMaster::with(['feesGroup', 'ClassTypes.course'])
                        ->where('branch_id', $branch_id)
                        ->where('session_id', $session_id)
                        ->whereNull('deleted_at')
                        ->get();

                    $distinctHeads = [];
                    $coursesMap = [];
                    $classesMap = [];
                    $totalAmount = 0;

                    foreach ($feesMasters as $fm) {
                        $totalAmount += floatval($fm->amount ?? 0);
                        if (!empty($fm->fees_group_id)) {
                            $distinctHeads[$fm->fees_group_id] = $fm->feesGroup->name ?? 'Fee Head';
                        }
                        if (!empty($fm->class_type_id)) {
                            $classesMap[$fm->class_type_id] = true;
                        }
                        if (!empty($fm->ClassTypes->course_id)) {
                            $cName = $fm->ClassTypes->course->name ?? 'Course';
                            $coursesMap[$fm->ClassTypes->course_id] = $cName;
                        }
                    }

                    $allGroupNames = $feeGroups->pluck('name')->toArray();

                    return response()->json([
                        'status' => 'success',
                        'session_name' => $sessionName,
                        'total_heads' => $feeGroups->count(),
                        'configured_courses' => count($coursesMap),
                        'course_names' => array_values($coursesMap),
                        'configured_classes' => count($classesMap),
                        'fees_masters_count' => $feesMasters->count(),
                        'total_amount' => $totalAmount,
                        'fee_group_names' => $allGroupNames
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }

            public function cloneFeesStructure(Request $request){
                try {
                    $branch_id = Session::get('branch_id');
                    $user_id = Session::get('id');
                    $target_session_id = Session::get('session_id');
                    $source_session_id = $request->source_session_id;

                    if (empty($source_session_id)) {
                        return response()->json(['status' => 'error', 'message' => 'Please select a source session to clone from!']);
                    }

                    if ($source_session_id == $target_session_id) {
                        return response()->json(['status' => 'error', 'message' => 'Source session and target session cannot be the same!']);
                    }

                    $sourceSession = \App\Models\Sessions::find($source_session_id);
                    $targetSession = \App\Models\Sessions::find($target_session_id);

                    if (!$sourceSession || !$targetSession) {
                        return response()->json(['status' => 'error', 'message' => 'Invalid source or target session!']);
                    }

                    $sourceSessionName = ($sourceSession->from_year ?? '') . ' - ' . ($sourceSession->to_year ?? '');
                    $targetSessionName = ($targetSession->from_year ?? '') . ' - ' . ($targetSession->to_year ?? '');

                    // Calculate year difference for intelligent due date adjusting
                    $yearDiff = 0;
                    if (!empty($sourceSession->from_year) && !empty($targetSession->from_year) && is_numeric($sourceSession->from_year) && is_numeric($targetSession->from_year)) {
                        $yearDiff = intval($targetSession->from_year) - intval($sourceSession->from_year);
                    }

                    // Fetch source fee groups
                    $sourceFeeGroups = FeesGroup::where('branch_id', $branch_id)
                        ->where('session_id', $source_session_id)
                        ->whereNull('deleted_at')
                        ->get();

                    // Fetch source fee masters
                    $sourceFeesMasters = \App\Models\FeesMaster::where('branch_id', $branch_id)
                        ->where('session_id', $source_session_id)
                        ->whereNull('deleted_at')
                        ->get();

                    if ($sourceFeeGroups->isEmpty() && $sourceFeesMasters->isEmpty()) {
                        return response()->json(['status' => 'error', 'message' => 'The selected source session (' . $sourceSessionName . ') does not have any fee structure or fee heads configured.']);
                    }

                    \DB::beginTransaction();

                    $groupMap = []; // old_group_id => new_group_id
                    $clonedHeadsCount = 0;
                    $clonedMastersCount = 0;

                    // 1. Clone / Map Fee Groups
                    foreach ($sourceFeeGroups as $sfg) {
                        $existingFg = FeesGroup::where('branch_id', $branch_id)
                            ->where('session_id', $target_session_id)
                            ->where('name', $sfg->name)
                            ->whereNull('deleted_at')
                            ->first();

                        if ($existingFg) {
                            $groupMap[$sfg->id] = $existingFg->id;
                        } else {
                            $newFg = new FeesGroup;
                            $newFg->user_id = $user_id;
                            $newFg->session_id = $target_session_id;
                            $newFg->branch_id = $branch_id;
                            $newFg->name = $sfg->name;
                            $newFg->fees_refund = $sfg->fees_refund ?? 'no';
                            $newFg->fees_partial = $sfg->fees_partial ?? 0;
                            $newFg->group_type = $sfg->group_type ?? 'academic';
                            $newFg->fees_type = $sfg->fees_type ?? 'full';
                            $newFg->save();

                            $groupMap[$sfg->id] = $newFg->id;
                            $clonedHeadsCount++;
                        }
                    }

                    // 2. Clone / Map Fees Masters
                    foreach ($sourceFeesMasters as $sfm) {
                        $newGroupId = $groupMap[$sfm->fees_group_id] ?? null;
                        if (!$newGroupId) {
                            // Try to find fee group in target session by source group name
                            $srcGroup = FeesGroup::find($sfm->fees_group_id);
                            if ($srcGroup) {
                                $targetGroup = FeesGroup::where('branch_id', $branch_id)
                                    ->where('session_id', $target_session_id)
                                    ->where('name', $srcGroup->name)
                                    ->whereNull('deleted_at')
                                    ->first();
                                if ($targetGroup) {
                                    $newGroupId = $targetGroup->id;
                                    $groupMap[$sfm->fees_group_id] = $newGroupId;
                                } else {
                                    $newFg = new FeesGroup;
                                    $newFg->user_id = $user_id;
                                    $newFg->session_id = $target_session_id;
                                    $newFg->branch_id = $branch_id;
                                    $newFg->name = $srcGroup->name;
                                    $newFg->fees_refund = $srcGroup->fees_refund ?? 'no';
                                    $newFg->fees_partial = $srcGroup->fees_partial ?? 0;
                                    $newFg->group_type = $srcGroup->group_type ?? 'academic';
                                    $newFg->fees_type = $srcGroup->fees_type ?? 'full';
                                    $newFg->save();

                                    $newGroupId = $newFg->id;
                                    $groupMap[$sfm->fees_group_id] = $newGroupId;
                                    $clonedHeadsCount++;
                                }
                            }
                        }

                        if ($newGroupId && !empty($sfm->class_type_id)) {
                            // Map source session class_type_id to target session class_type_id by class name
                            $targetClassTypeId = $sfm->class_type_id;
                            $sourceClass = \App\Models\ClassType::find($sfm->class_type_id);
                            if ($sourceClass) {
                                $targetClass = \App\Models\ClassType::where('session_id', $target_session_id)
                                    ->where('branch_id', $branch_id)
                                    ->where('name', $sourceClass->name)
                                    ->first();
                                if ($targetClass) {
                                    $targetClassTypeId = $targetClass->id;
                                }
                            }

                            // Shift installment due date by year difference if date exists
                            $newDueDate = null;
                            if (!empty($sfm->installment_due_date) && $sfm->installment_due_date != '0000-00-00') {
                                if ($yearDiff != 0) {
                                    $newDueDate = date('Y-m-d', strtotime("+" . $yearDiff . " years", strtotime($sfm->installment_due_date)));
                                } else {
                                    $newDueDate = $sfm->installment_due_date;
                                }
                            }

                            $existingFm = \App\Models\FeesMaster::where('session_id', $target_session_id)
                                ->where('branch_id', $branch_id)
                                ->where('class_type_id', $targetClassTypeId)
                                ->where('fees_group_id', $newGroupId)
                                ->whereNull('deleted_at')
                                ->first();

                            if (!$existingFm) {
                                $newFm = new \App\Models\FeesMaster;
                                $newFm->user_id = $user_id;
                                $newFm->session_id = $target_session_id;
                                $newFm->branch_id = $branch_id;
                                $newFm->class_type_id = $targetClassTypeId;
                                $newFm->fees_group_id = $newGroupId;
                                $newFm->amount = $sfm->amount ?? 0;
                                $newFm->nri = $sfm->nri ?? $sfm->amount ?? 0;
                                $newFm->management = $sfm->management ?? $sfm->amount ?? 0;
                                $newFm->govt = $sfm->govt ?? $sfm->amount ?? 0;
                                $newFm->installment_due_date = $newDueDate;
                                $newFm->installment_fine = $sfm->installment_fine ?? 0;
                                $newFm->editable = 0;
                                $newFm->save();

                                $clonedMastersCount++;
                            } else {
                                // Update if exists
                                $existingFm->amount = $sfm->amount ?? 0;
                                $existingFm->nri = $sfm->nri ?? $sfm->amount ?? 0;
                                $existingFm->management = $sfm->management ?? $sfm->amount ?? 0;
                                $existingFm->govt = $sfm->govt ?? $sfm->amount ?? 0;
                                if ($newDueDate) {
                                    $existingFm->installment_due_date = $newDueDate;
                                }
                                $existingFm->save();
                            }
                        }
                    }

                    \DB::commit();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Fee setup cloned successfully from ' . $sourceSessionName . ' to ' . $targetSessionName . '! (' . $clonedHeadsCount . ' new heads, ' . $clonedMastersCount . ' class fee structures cloned/synced)',
                        'cloned_heads_count' => $clonedHeadsCount,
                        'cloned_masters_count' => $clonedMastersCount,
                        'source_session_name' => $sourceSessionName,
                        'target_session_name' => $targetSessionName
                    ]);
                } catch (\Exception $e) {
                    \DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Error during cloning: ' . $e->getMessage()]);
                }
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
                $batch = $request->filled('batch') ? trim($request->batch) : null;
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
                                ->where(function($q) use ($batch) {
                                    if ($batch) $q->where('batch', $batch);
                                })
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
                            $fm->course_id = $courseId;
                            if ($batch) {
                                $fm->batch = $batch;
                            }
                            $fm->amount = $amt;
                            $fm->boys_amount = !empty($request->course_boys_amount[$courseId]) ? (float)$request->course_boys_amount[$courseId] : (!empty($request->boys_amount) ? (float)$request->boys_amount : $amt);
                            $fm->girls_amount = !empty($request->course_girls_amount[$courseId]) ? (float)$request->course_girls_amount[$courseId] : (!empty($request->girls_amount) ? (float)$request->girls_amount : $amt);
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

                        $cClass = ClassType::find($cId);
                        $courseId = $cClass->course_id ?? null;

                        $fm = FeesMaster::where('session_id', $session_id)
                            ->where('branch_id', $branch_id)
                            ->where('fees_group_id', $fees_group_id)
                            ->where('class_type_id', $cId)
                            ->where(function($q) use ($batch) {
                                if ($batch) $q->where('batch', $batch);
                            })
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
                        $fm->course_id = $courseId;
                        if ($batch) {
                            $fm->batch = $batch;
                        }
                        $fm->amount = $amt;
                        $fm->boys_amount = !empty($request->class_boys_amount[$cId]) ? (float)$request->class_boys_amount[$cId] : (!empty($request->boys_amount) ? (float)$request->boys_amount : $amt);
                        $fm->girls_amount = !empty($request->class_girls_amount[$cId]) ? (float)$request->class_girls_amount[$cId] : (!empty($request->girls_amount) ? (float)$request->girls_amount : $amt);
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
                $activeSessionId = $request->session_id ?? Session::get('session_id');

                $checkStudent = null;
                if (!empty($request->admission_id)) {
                    $checkStudent = Admission::find($request->admission_id);
                }

                if (empty($checkStudent) && !empty($request->unique_system_id)) {
                    if (!empty($request->session_id)) {
                        $candidateStudent = Admission::where('session_id', $request->session_id)->where('unique_system_id', $request->unique_system_id)->first();
                        if (!empty($candidateStudent)) {
                            $hasAssign = FeesAssign::where('admission_id', $candidateStudent->id)->where('total_amount', '>', 0)->first();
                            if (!empty($hasAssign)) {
                                $checkStudent = $candidateStudent;
                            }
                        }
                    }
                    if (empty($checkStudent)) {
                        // Find admission with assigned fees across all sessions for this student
                        $admissionIds = Admission::where('unique_system_id', $request->unique_system_id)->pluck('id');
                        $assigned = FeesAssign::whereIn('admission_id', $admissionIds)->where('total_amount', '>', 0)->orderBy('id', 'DESC')->first();
                        if (!empty($assigned)) {
                            $checkStudent = Admission::find($assigned->admission_id);
                        } else {
                            $checkStudent = Admission::where('unique_system_id', $request->unique_system_id)->orderBy('id', 'DESC')->first();
                        }
                    }
                }

                if (empty($activeSessionId)) {
                    $activeSessionId = $request->session_id ?? ($checkStudent->session_id ?? null);
                }

                $BillCounter = BillCounter::where('session_id', $activeSessionId)->where('branch_id', Session::get('branch_id'))->where('type', 'FeesSlip')->first();
                if (empty($BillCounter) && !empty(Session::get('session_id'))) {
                    $BillCounter = BillCounter::where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))->where('type', 'FeesSlip')->first();
                }
                if (empty($BillCounter)) {
                    $BillCounter = BillCounter::where('branch_id', Session::get('branch_id'))->where('type', 'FeesSlip')->first();
                }
                if (empty($BillCounter)) {
                    $BillCounter = BillCounter::where('type', 'FeesSlip')->first();
                }
                $BillCounterNo = !empty($BillCounter->counter) ? $BillCounter->counter + 1 : 1;

                $admission_id = null;
                $data['stuData'] = [];
                if (!empty($checkStudent)) {
                    $admission_id = $checkStudent->id;
                    $data['stuData'] = $checkStudent;
                } else {
                    $data['stuData'] = ['first_name' => 'not_found_not_found', 'class_type_id' => 'not'];
                }

                $formattedSlipNo = Helper::formatReceiptNo($BillCounterNo, $activeSessionId);
                $data['session_id'] = $activeSessionId;
                $data['BillCounter'] = $BillCounter;
                $data['BillCounterNo'] = $BillCounterNo;
                $data['formattedSlipNo'] = $formattedSlipNo;
                $data['sessions'] = [];
                $data['student_courses'] = [];
                $financialYearAdmissionIds = !empty($admission_id) ? [$admission_id] : [];

                $uniqueSysId = $checkStudent->unique_system_id ?? $request->unique_system_id;
                if (!empty($uniqueSysId)) {
                    $allStudentAdmissions = Admission::with(['ClassTypes.course', 'ClassTypes'])
                        ->where('unique_system_id', $uniqueSysId)
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'ASC')
                        ->get();

                    $sessionNames = Sessions::whereIn('id', $allStudentAdmissions->pluck('session_id')->filter()->unique())
                        ->get()->keyBy('id');
                    foreach ($allStudentAdmissions as $tabAdmission) {
                        $sessionId = $tabAdmission->session_id;
                        if (empty($sessionId)) continue;
                        $sessionRow = $sessionNames->get($sessionId);
                        $sessionLabel = $sessionRow
                            ? trim(($sessionRow->from_year ?? '') . '-' . ($sessionRow->to_year ?? ''), '-')
                            : 'Session ' . $sessionId;
                        $data['sessions'][] = [
                            'id' => (int)$sessionId,
                            'name' => $sessionLabel,
                            'admission_id' => $tabAdmission->id ?? null,
                            'class_name' => $tabAdmission->ClassTypes->name ?? '',
                            'has_fees' => FeesAssign::where('admission_id', $tabAdmission->id)->where('total_amount', '>', 0)->exists(),
                        ];
                    }
                    usort($data['sessions'], function($a, $b) {
                        return [$a['id'], $a['admission_id']] <=> [$b['id'], $b['admission_id']];
                    });

                    // Each tab is one semester admission, even when two semesters
                    // belong to the same financial year.
                    $selectedSemesterAdmission = $allStudentAdmissions->firstWhere('id', (int)$admission_id);
                    if (!$selectedSemesterAdmission) {
                        $selectedSemesterAdmission = $allStudentAdmissions->where('session_id', $activeSessionId)->sortByDesc('id')->first();
                    }
                    if ($selectedSemesterAdmission) {
                        $checkStudent = $selectedSemesterAdmission;
                        $admission_id = $selectedSemesterAdmission->id;
                        $activeSessionId = $selectedSemesterAdmission->session_id;
                        $data['session_id'] = $activeSessionId;
                        $data['stuData'] = $selectedSemesterAdmission;
                        $financialYearAdmissionIds = [$selectedSemesterAdmission->id];
                    }

                    $courseAdmissions = [];
                    foreach ($allStudentAdmissions as $adm) {
                        $cId = $adm->ClassTypes->course_id ?? ($adm->course ?? ('course_' . ($adm->class_type_id ?? '0')));
                        $courseName = $adm->ClassTypes->course->name ?? ($adm->ClassTypes->name ?? ($adm->course ?? 'Course'));
                        $assign = FeesAssign::where('admission_id', $adm->id)->where('total_amount', '>', 0)->first();

                        if (!isset($courseAdmissions[$cId])) {
                            $courseAdmissions[$cId] = [
                                'admission' => $adm,
                                'course_id' => $cId,
                                'course_name' => $courseName,
                                'batch' => $adm->batch ?? '',
                                'has_fees' => !empty($assign)
                            ];
                        } else {
                            if ($adm->id == $admission_id || (!empty($assign) && !$courseAdmissions[$cId]['has_fees'])) {
                                $courseAdmissions[$cId]['admission'] = $adm;
                                if (!empty($adm->batch)) {
                                    $courseAdmissions[$cId]['batch'] = $adm->batch;
                                }
                            }
                            if (!empty($assign)) {
                                $courseAdmissions[$cId]['has_fees'] = true;
                            }
                        }
                    }
                    $data['student_courses'] = array_values($courseAdmissions);
                }

                $data['FeesAssign'] = FeesAssign::where('admission_id', $admission_id)->first();
                if (empty($data['FeesAssign'])) {
                    $data['FeesAssign'] = FeesAssign::where('session_id', $activeSessionId)->where('branch_id', Session::get('branch_id'))->where('admission_id', $admission_id)->first();
                }

                $financialYearAdmissionIds = !empty($financialYearAdmissionIds) ? $financialYearAdmissionIds : [$admission_id];
                $financialAssignments = FeesAssignDetail::whereIn('admission_id', $financialYearAdmissionIds)
                    ->whereNull('deleted_at')
                    ->orderBy('class_type_id')->orderBy('id')->get();
                $data['carried_forward_amount'] = (float)$financialAssignments->sum('promotion_carry_adjustment');
                $data['is_carried_forward'] = $data['carried_forward_amount'] > 0;
                $nextSemesterAdmission = !empty($uniqueSysId)
                    ? Admission::with('ClassTypes')->where('unique_system_id', $uniqueSysId)->where('id', '>', $admission_id)->orderBy('id')->first()
                    : null;
                $data['carried_to_semester'] = $nextSemesterAdmission->ClassTypes->name ?? '';

                if (!empty($data['FeesAssign'])) {
                    $data['FeesAssign']->total_amount = $financialAssignments->sum('fees_group_amount');
                    $data['FeesAssign']->total_discount = $financialAssignments->sum('discount');
                    $data['FeesAssign']->net_amount = $data['FeesAssign']->total_amount - $data['FeesAssign']->total_discount;
                }

                $fyCollectedAmount = FeesCollect::whereIn('admission_id', $financialYearAdmissionIds)
                    ->where('session_id', $activeSessionId)->whereNull('deleted_at')->sum('amount');
                $data['FeesCollect'] = (object) ['amount' => $fyCollectedAmount];
                $data['financial_year_admission_ids'] = $financialYearAdmissionIds;
                $data['financial_year_assignments'] = $financialAssignments;
                $data['FeesDetailsInvoices'] = FeesDetailsInvoices::whereIn('admission_id', $financialYearAdmissionIds)
                    ->where('session_id', $activeSessionId)->whereIn('status', [0, 1])->orderBy('payment_date', 'DESC')->orderBy('id', 'DESC')->get();
                
                // Pre-fetch Fee Groups Map for instant lookup
                $groupIds = $financialAssignments->pluck('fees_group_id')->filter()->unique();
                $data['feesGroupMap'] = DB::table('fees_group')->whereIn('id', $groupIds)->pluck('name', 'id')->toArray();

                // Pre-fetch Fee Details Summaries
                $detailSummaries = DB::table('fees_detail')
                    ->selectRaw('admission_id, fees_group_id, SUM(total_amount) AS paid, SUM(installment_fine) AS paid_fine, SUM(discount) AS discount')
                    ->whereIn('admission_id', $financialYearAdmissionIds)
                    ->whereIn('status', [0, 1])
                    ->whereNull('deleted_at')
                    ->groupBy('admission_id', 'fees_group_id')
                    ->get();

                $summaryMap = [];
                foreach ($detailSummaries as $ds) {
                    $summaryMap[$ds->admission_id . '_' . $ds->fees_group_id] = [
                        'paid' => (float)$ds->paid,
                        'paid_fine' => (float)$ds->paid_fine,
                        'discount' => (float)$ds->discount
                    ];
                }
                $data['feesDetailsSummary'] = $summaryMap;

                // Pre-fetch Invoices Details to eliminate loop queries
                $allInvoiceDetailIds = [];
                foreach ($data['FeesDetailsInvoices'] as $inv) {
                    if (!empty($inv->fees_details_id)) {
                        $ids = explode(',', $inv->fees_details_id);
                        foreach ($ids as $idVal) {
                            $idVal = trim($idVal);
                            if (!empty($idVal)) $allInvoiceDetailIds[] = (int)$idVal;
                        }
                    }
                }
                $allInvoiceDetailIds = array_unique($allInvoiceDetailIds);

                $allDetailsRows = !empty($allInvoiceDetailIds) ? DB::table('fees_detail')
                    ->leftJoin('fees_group', 'fees_detail.fees_group_id', '=', 'fees_group.id')
                    ->whereIn('fees_detail.id', $allInvoiceDetailIds)
                    ->whereNull('fees_detail.deleted_at')
                    ->select('fees_detail.id', 'fees_detail.fees_group_id', 'fees_group.name as group_name', 'fees_detail.paid_amount', 'fees_detail.discount', 'fees_detail.installment_fine', 'fees_detail.payment_mode_id', 'fees_detail.discount_remark', 'fees_detail.payment_info', 'fees_detail.other_fee_remark', 'fees_detail.receipt_no')
                    ->get()
                    ->keyBy('id') : collect([]);

                $invoicesParsed = [];
                foreach ($data['FeesDetailsInvoices'] as $inv) {
                    $headNames = [];
                    $headTotal = 0;
                    $discTotal = 0;
                    $fineTotal = 0;
                    if (!empty($inv->fees_details_id)) {
                        $detailIds = explode(',', $inv->fees_details_id);
                        foreach ($detailIds as $dId) {
                            $dId = trim($dId);
                            if (isset($allDetailsRows[$dId])) {
                                $row = $allDetailsRows[$dId];
                                $isAdv = ($row->payment_mode_id == 9)
                                    || (strpos($row->discount_remark ?? '', 'Excess advance adjusted') !== false)
                                    || (strpos($row->payment_info ?? '', 'Excess advance adjusted') !== false)
                                    || (strpos($row->other_fee_remark ?? '', 'Excess advance adjusted') !== false)
                                    || ((int)$row->receipt_no >= 16521);
                                if ($isAdv) {
                                    $headNames[] = 'Advance Fees';
                                } elseif (!empty($row->group_name)) {
                                    $headNames[] = $row->group_name;
                                }
                                $headTotal += (float)$row->paid_amount;
                                $discTotal += (float)$row->discount;
                                $fineTotal += (float)$row->installment_fine;
                            }
                        }
                    }
                    $invoicesParsed[$inv->id] = [
                        'head_names' => implode(', ', array_unique($headNames)),
                        'head_total' => $headTotal,
                        'discount' => $discTotal,
                        'head_fine_total' => $fineTotal
                    ];
                }
                $data['invoicesParsed'] = $invoicesParsed;

                $data['FeesMaster'] = FeesMaster::where('session_id', $activeSessionId)->where('branch_id', Session::get('branch_id'))->where('class_type_id', $data['stuData']['class_type_id'] ?? null)->get();
                if ($data['FeesMaster']->isEmpty() && !empty($data['stuData']['class_type_id'])) {
                    $data['FeesMaster'] = FeesMaster::where('class_type_id', $data['stuData']['class_type_id'])->get();
                }

                $data['stuFeeDet'] = FeesDetail::with('PaymentMode')->with('Admission')->with('FeesCollect')
                    ->whereIn('admission_id', $financialYearAdmissionIds)->where('session_id', $activeSessionId)
                    ->where('fees_type', 0)->orderBy('date', 'DESC')->orderBy('id', 'DESC')->get();
                $data['inventory'] = StoreItemRequest::where('admission_id', $admission_id)->groupBy('receipt_no')->get();

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
                    // Collection must post into the financial year selected in
                    // the student ledger tab, not blindly into the global session.
                    $session_id = $request->session_id ?? Session::get('session_id');
                    $FeesAssign = FeesAssign::where('admission_id',$request->admission_id)->get()->first();
                    $carriedForwardAmount = FeesAssignDetail::where('admission_id', $request->admission_id)
                        ->whereNull('deleted_at')->sum('promotion_carry_adjustment');
                    if ($carriedForwardAmount > 0) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Collection is locked because this semester outstanding has been carried forward to the next semester.'
                        ], 422);
                    }
                    $fees_details_id =[];
                    $slip = "";
                    if ($request->isMethod('post')) {
                        //dd($request);
                        $admission_id = $request->admission_id;
                        $admission = Admission::where('id',$admission_id)->first();
                        if (!empty($admission)) {
                            if (empty($session_id)) {
                                $session_id = $admission->session_id;
                            }
                            if (!empty($request->selected_head)) {
                                DB::beginTransaction();
                                $transactionStarted = true;

                                $BillCounter = BillCounter::where('session_id', $session_id)->where('branch_id', Session::get('branch_id'))->where('type', 'FeesSlip')->lockForUpdate()->first();
                                if (empty($BillCounter) && !empty(Session::get('session_id'))) {
                                    $BillCounter = BillCounter::where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))->where('type', 'FeesSlip')->lockForUpdate()->first();
                                }
                                if (empty($BillCounter)) {
                                    $BillCounter = BillCounter::where('branch_id', Session::get('branch_id'))->where('type', 'FeesSlip')->lockForUpdate()->first();
                                }
                                if (empty($BillCounter)) {
                                    $BillCounter = BillCounter::where('type', 'FeesSlip')->lockForUpdate()->first();
                                }
                                if (empty($BillCounter)) {
                                    $BillCounter = new BillCounter();
                                    $BillCounter->user_id = Session::get('id') ?? 1;
                                    $BillCounter->session_id = $session_id;
                                    $BillCounter->branch_id = Session::get('branch_id');
                                    $BillCounter->type = 'FeesSlip';
                                    $BillCounter->counter = 0;
                                }

                                $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                                $BillCounter->counter = $counter + 1;
                                $BillCounter->session_id = $session_id;
                                $BillCounter->save();

                                $slip_no = Helper::formatReceiptNo($BillCounter->counter, $session_id);

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
                                            $payDetail->receipt_no  = $slip_no;
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
                                            $payDetail->receipt_no  = $slip_no;
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
                                $invoice->invoice_no = $slip_no;
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
                                        $advancahistory->details = "Amount debited for this Receipt No . =" . $slip_no; 
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

                        $setting = Setting::where('branch_id', Session::get('branch_id'))->first();
                        if (!empty($setting) && $setting->firebase_notification == 1 && !empty($admission)) {
                            $paymentModeObj = PaymentMode::find($request->payment_mode_id);
                            Helper::sendNotification(
                                'Fee Payment Received',
                                'We have received your payment of ₹'.number_format($finalCollectedAmt, 2).' on '.$receiptDate.' via '.($paymentModeObj->name ?? 'Cash').'. Thank you!',
                                'student',
                                $admission->id
                            );
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
                    return Response::json(array('status' => 'success','unique_system_id'=>$admission->unique_system_id,'session_id' => $session_id,'slip'=>$slip,'fees_details_invoice_id'=>$fees_details_invoice_id)); 
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
                    $invoiceId = $invoiceQuery->value('id');

                    // If not found with this specific admission_id (e.g. promoted student linking to previous semester receipt),
                    // fallback to the original invoice for this receipt_no
                    if (empty($invoiceId)) {
                        $invoiceId = FeesDetailsInvoices::where('invoice_no', $receipt_no)->value('id');
                    }

                    // If still not found anywhere in invoices table, auto-sync from fees_detail ONLY if no invoice exists
                    if (empty($invoiceId)) {
                        $detailQuery = FeesDetail::where('receipt_no', $receipt_no)->whereNull('deleted_at');
                        if (!empty($admission_id)) {
                            $detailQuery->where('admission_id', $admission_id);
                        }
                        $details = $detailQuery->get();
                        if ($details->isNotEmpty()) {
                            $first = $details->first();
                            $inv = new FeesDetailsInvoices();
                            $inv->user_id = $first->user_id ?: 1;
                            $inv->session_id = $first->session_id ?: 1;
                            $inv->branch_id = $first->branch_id ?: 1;
                            $inv->fees_counter_id = $first->fees_counter_id ?: 1;
                            $inv->admission_id = $first->admission_id;
                            $inv->fees_details_id = $details->pluck('id')->implode(',');
                            $inv->payment_date = $first->date;
                            $inv->payment_mode = $first->payment_mode_id ?: 1;
                            $inv->transaction_id = $first->transition_id;
                            $inv->bank_name = $first->bank_name;
                            $inv->invoice_no = $receipt_no;
                            $inv->status = $first->status !== null ? $first->status : 0;
                            $inv->cheque_number = $first->cheque_number;
                            $inv->cheque_date = $first->cheque_date;
                            $inv->amount = $details->sum('paid_amount');
                            $inv->total_fine = $details->sum('installment_fine');
                            $inv->discount = $details->sum('discount');
                            $inv->remark = (!empty($first->other_fee_remark) && $first->other_fee_remark !== '0') ? $first->other_fee_remark : ($first->discount_remark ?: ($first->payment_info ?: ''));
                            $inv->save();
                            $invoiceId = $inv->id;
                            FeesDetail::whereIn('id', $details->pluck('id'))->update(['invoice_id' => $invoiceId]);
                        }
                    }

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
                    $raw_details = FeesDetail::select('fees_detail.*','payment_modes.name as payment_mode','fees_group.name as fees_group_name')
                        ->leftJoin('payment_modes','payment_modes.id','fees_detail.payment_mode_id')
                        ->leftJoin('fees_collect','fees_collect.id','fees_detail.fees_collect_id')
                        ->leftJoin('fees_group','fees_group.id','fees_detail.fees_group_id')
                        ->whereIn('fees_detail.id',$explode)
                        ->get();
                        
                    $fess_print = $this->prepareReceiptItems($raw_details);
                        
                    $printPreview = Helper::printPreview('Fees Collect');
                    
                    return view($printPreview, ['data'=>$fess_print,'invoice_data'=>$invoice_data]);
                } 
                abort(404);
            }

            protected function prepareReceiptItems($raw_details) {
                $advanceGroupId = FeesGroup::where('name', 'Advance Fees')->value('id');
                if (!$advanceGroupId) {
                    $adv = new FeesGroup();
                    $adv->user_id = 1;
                    $adv->branch_id = 1;
                    $adv->session_id = 7;
                    $adv->name = 'Advance Fees';
                    $adv->fees_type = 'full';
                    $adv->fees_refund = 'no';
                    $adv->fees_partial = 0;
                    $adv->save();
                    $advanceGroupId = $adv->id;
                }

                $fess_print = collect();
                foreach ($raw_details as $item) {
                    $isAdvPayment = ($item->payment_mode_id == 9)
                        || (strpos($item->discount_remark ?? '', 'Excess advance adjusted') !== false)
                        || (strpos($item->payment_info ?? '', 'Excess advance adjusted') !== false)
                        || (strpos($item->other_fee_remark ?? '', 'Excess advance adjusted') !== false)
                        || ((int)$item->receipt_no >= 16521);

                    if ($isAdvPayment) {
                        $advItem = clone $item;
                        $advItem->fees_group_id = $advanceGroupId;
                        $advItem->fees_group_name = 'Advance Fees';
                        $fess_print->push($advItem);
                        continue;
                    }

                    $assignDetail = FeesAssignDetail::where('admission_id', $item->admission_id)
                        ->where('fees_group_id', $item->fees_group_id)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($assignDetail && $item->fees_group_id != $advanceGroupId) {
                        $gross = (float)($assignDetail->fees_group_amount ?? 0);
                        $disc = (float)($assignDetail->discount ?? 0);
                        $carry = (float)($assignDetail->promotion_carry_adjustment ?? 0);
                        $netAssigned = max(0, $gross - $disc - $carry);

                        $priorPaid = (float)FeesDetail::where('admission_id', $item->admission_id)
                            ->where('fees_group_id', $item->fees_group_id)
                            ->where('id', '<', $item->id)
                            ->whereNull('deleted_at')
                            ->sum('paid_amount');

                        $headDue = max(0, $netAssigned - $priorPaid);
                        $paidAmt = (float)$item->paid_amount;

                        if ($headDue > 0 && $paidAmt > $headDue) {
                            // 1. Fee Head Name up to assigned due amount
                            $headItem = clone $item;
                            $headItem->paid_amount = $headDue;
                            $headItem->total_amount = $headDue + (float)($item->discount ?? 0);
                            $fess_print->push($headItem);

                            // 2. Advance Fees for the left/excess amount
                            $leftAmount = $paidAmt - $headDue;
                            $advItem = clone $item;
                            $advItem->id = $item->id . '_adv';
                            $advItem->fees_group_id = $advanceGroupId;
                            $advItem->fees_group_name = 'Advance Fees';
                            $advItem->paid_amount = $leftAmount;
                            $advItem->total_amount = $leftAmount;
                            $advItem->installment_fine = 0;
                            $advItem->discount = 0;
                            $fess_print->push($advItem);
                            continue;
                        } elseif ($headDue == 0 && $paidAmt > 0 && $netAssigned > 0) {
                            $advItem = clone $item;
                            $advItem->fees_group_id = $advanceGroupId;
                            $advItem->fees_group_name = 'Advance Fees';
                            $fess_print->push($advItem);
                            continue;
                        }
                    }

                    $fess_print->push($item);
                }

                return $fess_print;
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
                
                $fess_print = $this->prepareReceiptItems($fess_print);
                $printPreview = Helper::printPreview('Fees Collect');
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
                    $raw_details = FeesDetail::select('fees_detail.*','payment_modes.name as payment_mode','fees_group.name as fees_group_name')
                        ->leftJoin('payment_modes','payment_modes.id','fees_detail.payment_mode_id')
                        ->leftJoin('fees_collect','fees_collect.id','fees_detail.fees_collect_id')
                        ->leftJoin('fees_group','fees_group.id','fees_detail.fees_group_id')
                        ->whereIn('fees_detail.id',$explode)
                        ->get();
                        
                    $fess_print = $this->prepareReceiptItems($raw_details);
                        
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
                $search = [
                    'name' => $request->name ?? '',
                    'course' => $request->course ?? '',
                    'course_id' => $request->course_id ?? '',
                    'batch' => $request->batch ?? '',
                    'admissionNo' => $request->admissionNo ?? '',
                    'session_id' => $request->session_id ?? '',
                    'layout' => $request->layout === 'consolidated' ? 'consolidated' : 'standalone',
                ];

                $data = collect();
                $summary = ['gross' => 0, 'discount' => 0, 'carry' => 0, 'net' => 0, 'paid' => 0, 'fine' => 0, 'due' => 0];
                $hasFilter = $request->isMethod('post') 
                    || !empty($search['course_id']) 
                    || !empty($search['course']) 
                    || !empty($search['batch']) 
                    || !empty($search['admissionNo']) 
                    || !empty($search['name'])
                    || !empty($search['session_id']);

                if ($hasFilter) {
                    $data = Admission::select('admissions.*', 'class_types.name as className', 'academic_sessions.from_year', 'academic_sessions.to_year')
                    ->leftJoin('class_types', 'class_types.id', 'admissions.class_type_id')
                    ->leftJoin('sessions as academic_sessions', 'academic_sessions.id', 'admissions.session_id')
                    ->where('admissions.admission_type_id',1)
                    ->where('admissions.school',1)
                    ->where('admissions.branch_id', Session::get('branch_id'));

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
                    if (!empty($request->course) || !empty($request->course_id)) {
                        $courseName = $request->course;
                        $courseId = $request->course_id;
                        if (empty($courseName) && !empty($courseId)) {
                            $cObj = \App\Models\Master\Course::find($courseId);
                            $courseName = $cObj ? $cObj->name : null;
                        }
                        $data = $data->where(function($q) use ($courseName, $courseId) {
                            if ($courseId) {
                                $q->where('class_types.course_id', $courseId);
                            }
                            if ($courseName) {
                                $q->orWhere('admissions.course', $courseName)
                                  ->orWhere('class_types.course_id', function($sub) use ($courseName) {
                                      $sub->select('id')->from('courses')->where('name', $courseName)->limit(1);
                                  });
                            }
                        });
                    }
                    if ($request->batch != '') {
                        $data = $data->where("admissions.batch", $request->batch);
                    }
                    if (Session::get('role_id') == 2) {
                        $data = $data->where('admissions.class_type_id', Session::get('class_type_id'));
                    } 
                    $data = $data->orderByDesc('admissions.status')
                        ->orderByDesc('admissions.id')
                        ->get()
                        ->unique(function($student) {
                            return !empty($student->unique_system_id)
                                ? 'uid:' . $student->unique_system_id
                                : 'adm:' . strtoupper(trim((string)$student->admissionNo));
                        })
                        ->sortBy(function($student) {
                            return strtoupper(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')));
                        })
                        ->values();

                    $admissionIds = $data->pluck('id');
                    if ($admissionIds->isNotEmpty()) {
                        $assignments = DB::table('fees_assign_details')
                            ->whereIn('admission_id', $admissionIds)
                            ->whereNull('deleted_at')
                            ->select('admission_id')
                            ->selectRaw('SUM(fees_group_amount) as gross_amount')
                            ->selectRaw('SUM(discount) as discount_amount')
                            ->selectRaw('SUM(COALESCE(promotion_carry_adjustment, 0)) as carry_amount')
                            ->groupBy('admission_id')->get()->keyBy('admission_id');

                        $payments = DB::table('fees_detail')
                            ->whereIn('admission_id', $admissionIds)
                            ->whereIn('status', [0, 1])
                            ->whereNull('deleted_at')
                            ->select('admission_id')
                            ->selectRaw('SUM(paid_amount) as paid_amount')
                            ->selectRaw('SUM(installment_fine) as fine_amount')
                            ->groupBy('admission_id')->get()->keyBy('admission_id');

                        $data->each(function ($student) use ($assignments, $payments, &$summary) {
                            $assigned = $assignments->get($student->id);
                            $payment = $payments->get($student->id);
                            $student->gross_amount = (float) ($assigned->gross_amount ?? 0);
                            $student->discount_amount = (float) ($assigned->discount_amount ?? 0);
                            $student->carry_amount = (float) ($assigned->carry_amount ?? 0);
                            $student->net_amount = max(0, $student->gross_amount - $student->discount_amount - $student->carry_amount);
                            $student->paid_amount = (float) ($payment->paid_amount ?? 0);
                            $student->fine_amount = (float) ($payment->fine_amount ?? 0);
                            $student->due_amount = max(0, $student->net_amount - $student->paid_amount);

                            foreach (['gross', 'discount', 'carry', 'net', 'paid', 'fine', 'due'] as $key) {
                                $summary[$key] += $student->{$key . '_amount'};
                            }
                        });
                    }
                }

                $consolidatedData = collect();
                if ($hasFilter && $search['layout'] === 'consolidated' && $data->isNotEmpty()) {
                    $uniqueIds = $data->pluck('unique_system_id')->filter()->unique()->values();
                    $admissionNos = $data->pluck('admissionNo')->filter()->unique()->values();
                    $relatedAdmissions = Admission::select('admissions.*', 'class_types.name as className', 'academic_sessions.from_year', 'academic_sessions.to_year')
                        ->leftJoin('class_types', 'class_types.id', 'admissions.class_type_id')
                        ->leftJoin('sessions as academic_sessions', 'academic_sessions.id', 'admissions.session_id')
                        ->where('admissions.branch_id', Session::get('branch_id'))
                        ->where(function ($q) use ($uniqueIds, $admissionNos) {
                            if ($uniqueIds->isNotEmpty()) $q->whereIn('admissions.unique_system_id', $uniqueIds);
                            if ($admissionNos->isNotEmpty()) $q->orWhereIn('admissions.admissionNo', $admissionNos);
                        })->orderBy('academic_sessions.from_year')->orderBy('admissions.id')->get();

                    $relatedIds = $relatedAdmissions->pluck('id');
                    $headsByAdmission = DB::table('fees_assign_details')
                        ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                        ->whereIn('fees_assign_details.admission_id', $relatedIds)->whereNull('fees_assign_details.deleted_at')
                        ->select('fees_assign_details.*', 'fees_group.name as head_name')->orderBy('fees_assign_details.id')->get()->groupBy('admission_id');
                    $paidByHead = DB::table('fees_detail')->whereIn('admission_id', $relatedIds)->whereIn('status', [0,1])->whereNull('deleted_at')
                        ->select('admission_id', 'fees_group_id')->selectRaw('SUM(paid_amount) as paid_amount')->selectRaw('SUM(installment_fine) as fine_amount')
                        ->groupBy('admission_id', 'fees_group_id')->get()->keyBy(function ($row) { return $row->admission_id . ':' . $row->fees_group_id; });

                    foreach ($data as $student) {
                        $identityAdmissions = $relatedAdmissions->filter(function ($admission) use ($student) {
                            return (!empty($student->unique_system_id) && $admission->unique_system_id === $student->unique_system_id)
                                || (empty($student->unique_system_id) && strtoupper(trim($admission->admissionNo)) === strtoupper(trim($student->admissionNo)));
                        });
                        $years = collect();
                        foreach ($identityAdmissions as $admission) {
                            $yearName = trim(($admission->from_year ?? '') . '-' . ($admission->to_year ?? ''), '-') ?: 'Unspecified Year';
                            $heads = collect($headsByAdmission->get($admission->id, collect()))->map(function ($head) use ($paidByHead, $admission) {
                                $payment = $paidByHead->get($admission->id . ':' . $head->fees_group_id);
                                $head->gross = (float)$head->fees_group_amount;
                                $head->discount_amount = (float)$head->discount;
                                $head->carry = (float)($head->promotion_carry_adjustment ?? 0);
                                $head->net = max(0, $head->gross - $head->discount_amount - $head->carry);
                                $head->paid = (float)($payment->paid_amount ?? 0);
                                $head->fine = (float)($payment->fine_amount ?? 0);
                                $head->due = max(0, $head->net - $head->paid);
                                return $head;
                            });
                            if (!$years->has($yearName)) $years->put($yearName, ['name'=>$yearName, 'semesters'=>collect(), 'gross'=>0, 'discount'=>0, 'carry'=>0, 'net'=>0, 'paid'=>0, 'fine'=>0, 'due'=>0]);
                            $year = $years->get($yearName);
                            $semester = ['name'=>$admission->className ?: 'Class / Semester', 'admission_id'=>$admission->id, 'heads'=>$heads, 'gross'=>$heads->sum('gross'), 'discount'=>$heads->sum('discount_amount'), 'carry'=>$heads->sum('carry'), 'net'=>$heads->sum('net'), 'paid'=>$heads->sum('paid'), 'fine'=>$heads->sum('fine'), 'due'=>$heads->sum('due')];
                            $year['semesters']->push($semester);
                            foreach (['gross','discount','carry','net','paid','fine','due'] as $key) $year[$key] += $semester[$key];
                            $years->put($yearName, $year);
                        }
                        $consolidatedData->push(['student'=>$student, 'years'=>$years]);
                    }
                }
                
                if ($request->ajax()) {
                    return response()->json([
                        'html' => $search['layout'] === 'consolidated'
                            ? view('fees.ledger.consolidated_results', compact('consolidatedData', 'hasFilter'))->render()
                            : view('fees.ledger.results', compact('data', 'summary', 'hasFilter'))->render(),
                        'count' => $data->count(),
                    ]);
                }

                return view('fees.ledger.view', compact('data', 'search', 'summary', 'hasFilter', 'consolidatedData'));
            }

            public function feesLedgerExport(Request $request, $format)
            {
                abort_unless(in_array($format, ['xlsx', 'csv'], true), 404);

                $query = Admission::select('admissions.*', 'class_types.name as className')
                    ->leftJoin('class_types', 'class_types.id', '=', 'admissions.class_type_id')
                    ->where('admissions.admission_type_id', 1)
                    ->where('admissions.school', 1)
                    ->where('admissions.branch_id', Session::get('branch_id'));
                if ($request->filled('session_id')) $query->where('admissions.session_id', $request->session_id);
                if ($request->filled('name')) {
                    $value = $request->name;
                    $query->where(function ($q) use ($value) {
                        $q->where('admissions.first_name', 'like', '%' . $value . '%')
                            ->orWhere('admissions.last_name', 'like', '%' . $value . '%')
                            ->orWhere('admissions.mobile', 'like', '%' . $value . '%')
                            ->orWhere('admissions.father_name', 'like', '%' . $value . '%');
                    });
                }
                if ($request->filled('admissionNo')) $query->where('admissions.admissionNo', 'like', '%' . $request->admissionNo . '%');
                if ($request->filled('batch')) $query->where('admissions.batch', $request->batch);
                if ($request->filled('course')) {
                    $course = $request->course;
                    $query->where(function ($q) use ($course) {
                        $q->where('admissions.course', $course)->orWhere('class_types.course_id', function ($sub) use ($course) {
                            $sub->select('id')->from('courses')->where('name', $course)->limit(1);
                        });
                    });
                }
                if (Session::get('role_id') == 2) $query->where('admissions.class_type_id', Session::get('class_type_id'));
                $students = $query->orderByDesc('admissions.status')->orderByDesc('admissions.id')->get()
                    ->unique(function ($student) { return !empty($student->unique_system_id) ? 'uid:' . $student->unique_system_id : 'adm:' . strtoupper(trim((string) $student->admissionNo)); })
                    ->sortBy(function ($student) { return strtoupper(trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))); })->values();
                abort_if($students->isEmpty(), 404, 'No student records match the selected filters.');

                $uids = $students->pluck('unique_system_id')->filter()->unique()->values();
                $admissionNos = $students->pluck('admissionNo')->filter()->unique()->values();
                $admissions = Admission::select('admissions.*', 'academic_sessions.from_year', 'academic_sessions.to_year')
                    ->leftJoin('sessions as academic_sessions', 'academic_sessions.id', '=', 'admissions.session_id')
                    ->where('admissions.branch_id', Session::get('branch_id'))
                    ->where(function ($q) use ($uids, $admissionNos) {
                        if ($uids->isNotEmpty()) $q->whereIn('admissions.unique_system_id', $uids);
                        if ($admissionNos->isNotEmpty()) $q->orWhereIn('admissions.admissionNo', $admissionNos);
                    })->orderBy('academic_sessions.from_year')->orderBy('admissions.id')->get();

                $ids = $admissions->pluck('id');
                $assignments = DB::table('fees_assign_details')->whereIn('admission_id', $ids)->whereNull('deleted_at')
                    ->select('admission_id')->selectRaw('SUM(fees_group_amount) as gross')->selectRaw('SUM(discount) as discount')
                    ->selectRaw('SUM(COALESCE(promotion_carry_adjustment, 0)) as carry')->groupBy('admission_id')->get()->keyBy('admission_id');
                $payments = DB::table('fees_detail')->whereIn('admission_id', $ids)->whereIn('status', [0, 1])->whereNull('deleted_at')
                    ->select('admission_id')->selectRaw('SUM(paid_amount) as paid')->groupBy('admission_id')->get()->keyBy('admission_id');

                $rows = [];
                foreach ($students as $student) {
                    $studentAdmissions = $admissions->filter(function ($admission) use ($student) {
                        return (!empty($student->unique_system_id) && $admission->unique_system_id === $student->unique_system_id)
                            || (empty($student->unique_system_id) && strtoupper(trim((string) $admission->admissionNo)) === strtoupper(trim((string) $student->admissionNo)));
                    });
                    $years = [];
                    foreach ($studentAdmissions as $admission) {
                        $year = trim(($admission->from_year ?? '') . '-' . ($admission->to_year ?? ''), '-') ?: 'Unspecified Year';
                        $assigned = $assignments->get($admission->id);
                        $payment = $payments->get($admission->id);
                        $gross = (float) ($assigned->gross ?? 0);
                        $discount = (float) ($assigned->discount ?? 0);
                        $carry = (float) ($assigned->carry ?? 0);
                        $net = max(0, $gross - $discount - $carry);
                        $paid = (float) ($payment->paid ?? 0);
                        if (!isset($years[$year])) $years[$year] = ['gross'=>0, 'discount'=>0, 'carry'=>0, 'net'=>0, 'paid'=>0, 'due'=>0];
                        $years[$year]['gross'] += $gross;
                        $years[$year]['discount'] += $discount;
                        $years[$year]['carry'] += $carry;
                        $years[$year]['net'] += $net;
                        $years[$year]['paid'] += $paid;
                        $years[$year]['due'] += max(0, $net - $paid);
                    }
                    foreach ($years as $year => $totals) {
                        $rows[] = [count($rows) + 1, trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')), $student->admissionNo ?: '-', $student->mobile ?: '-', $student->course ?: '-', $student->batch ?: '-', $year, $totals['gross'], $totals['discount'], $totals['carry'], $totals['net'], $totals['paid'], $totals['due']];
                    }
                }

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Consolidated Ledger');
                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'STUDENT FEES LEDGER — CONSOLIDATED');
                $sheet->mergeCells('A2:M2');
                $subtitle = collect([$request->filled('course') ? 'Course: ' . $request->course : null, $request->filled('batch') ? 'Batch: ' . $request->batch : null, 'Financial year-wise summary', 'Generated: ' . now()->format('d-m-Y H:i')])->filter()->implode('   |   ');
                $sheet->setCellValue('A2', $subtitle);
                $sheet->fromArray([['Sr. No.', 'Student Name', 'Admission No.', 'Mobile', 'Course', 'Batch', 'Financial Year', 'Gross Fee (₹)', 'Discount (₹)', 'Carried Forward (₹)', 'Net Payable (₹)', 'Collected (₹)', 'Outstanding (₹)']], null, 'A4');
                if ($rows) $sheet->fromArray($rows, null, 'A5');
                $lastRow = max(4, 4 + count($rows));
                $sheet->getStyle('A1:M1')->applyFromArray(['font'=>['bold'=>true,'size'=>16,'color'=>['rgb'=>'FFFFFF']], 'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'00365F']], 'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER]]);
                $sheet->getStyle('A2:M2')->applyFromArray(['font'=>['italic'=>true,'size'=>10,'color'=>['rgb'=>'415466']], 'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'DCEAF4']]]);
                $sheet->getStyle('A4:M4')->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']], 'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'217346']], 'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true], 'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'B7C8B7']]]]);
                if ($lastRow >= 5) {
                    $sheet->getStyle('A5:M' . $lastRow)->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'D5DEE5']]], 'alignment'=>['vertical'=>Alignment::VERTICAL_CENTER]]);
                    for ($r = 5; $r <= $lastRow; $r++) if ($r % 2 === 0) $sheet->getStyle('A' . $r . ':M' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F7FA');
                    $sheet->getStyle('H5:M' . $lastRow)->getNumberFormat()->setFormatCode('₹#,##0.00;[Red]-₹#,##0.00');
                    $sheet->getStyle('I5:J' . $lastRow)->getFont()->getColor()->setRGB('D98200');
                    $sheet->getStyle('L5:L' . $lastRow)->getFont()->getColor()->setRGB('168A43');
                    $sheet->getStyle('M5:M' . $lastRow)->getFont()->getColor()->setRGB('D93643');
                }
                foreach (['A'=>8,'B'=>25,'C'=>18,'D'=>16,'E'=>18,'F'=>10,'G'=>18,'H'=>17,'I'=>16,'J'=>22,'K'=>18,'L'=>16,'M'=>18] as $col => $width) $sheet->getColumnDimension($col)->setWidth($width);
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(34);
                $sheet->freezePane('A5');
                $sheet->setAutoFilter('A4:M' . $lastRow);
                $filename = Str::slug(implode('-', array_filter(['student-fees-ledger', $request->course, $request->batch, 'consolidated']))) . '-' . now()->format('Ymd-His') . '.' . $format;
                $writer = $format === 'csv' ? new Csv($spreadsheet) : new Xlsx($spreadsheet);
                if ($format === 'csv') $writer->setUseBOM(true);
                return response()->streamDownload(function () use ($writer) { $writer->save('php://output'); }, $filename, ['Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
            }

            public function fees_ledger_view(Request $request) {
                $requestedIds = collect(json_decode($request->admission_ids ?: '[]', true))
                    ->push($request->admission_id)
                    ->filter()
                    ->map(function ($id) { return (int) $id; })
                    ->unique()
                    ->values();
                $admissionIds = Admission::whereIn('id', $requestedIds)
                    ->where('branch_id', Session::get('branch_id'))
                    ->pluck('id');

                $fees = FeesAssignDetail::select('fees_assign_details.*', 'fees_group.name as group_name', 'class_types.name as class_name')
                    ->join('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                    ->join('admissions', 'admissions.id', '=', 'fees_assign_details.admission_id')
                    ->leftJoin('class_types', 'class_types.id', '=', 'admissions.class_type_id')
                    ->whereIn('fees_assign_details.admission_id', $admissionIds)
                    ->whereNull('fees_assign_details.deleted_at')
                    ->orderBy('admissions.id')
                    ->orderBy('fees_assign_details.id')
                    ->get();

                $payments = DB::table('fees_detail')
                    ->whereIn('admission_id', $admissionIds)
                    ->whereIn('status', [0, 1])
                    ->whereNull('deleted_at')
                    ->select('admission_id', 'fees_group_id')
                    ->selectRaw('SUM(paid_amount) as paid_amount')
                    ->selectRaw('SUM(installment_fine) as fine_amount')
                    ->groupBy('admission_id', 'fees_group_id')->get()->keyBy(function ($payment) {
                        return $payment->admission_id . ':' . $payment->fees_group_id;
                    });

                $summary = ['gross' => 0, 'discount' => 0, 'carry' => 0, 'net' => 0, 'paid' => 0, 'fine' => 0, 'due' => 0];
                $fees->each(function ($head) use ($payments, &$summary) {
                    $payment = $payments->get($head->admission_id . ':' . $head->fees_group_id);
                    $head->gross_amount = (float) $head->fees_group_amount;
                    $head->discount_amount = (float) $head->discount;
                    $head->carry_amount = (float) ($head->promotion_carry_adjustment ?? 0);
                    $head->net_amount = max(0, $head->gross_amount - $head->discount_amount - $head->carry_amount);
                    $head->paid_amount = (float) ($payment->paid_amount ?? 0);
                    $head->fine_amount = (float) ($payment->fine_amount ?? 0);
                    $head->due_amount = max(0, $head->net_amount - $head->paid_amount);
                    foreach (array_keys($summary) as $key) {
                        $summary[$key] += $head->{$key . '_amount'};
                    }
                });

                return response()->json([
                    'html' => view('fees.ledger.details', compact('fees', 'summary'))->render()
                ]);
            }
    
            public function feesLedgerPrint($unique_system_id){ 
                $student =  Admission::select('admissions.*', 'sessions.from_year', 'class_types.name as class_name','sessions.to_year', 'gender.name as genderName')
                ->leftJoin('gender','gender.id','admissions.gender_id')
                ->leftjoin('sessions','sessions.id','admissions.session_id')
                ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                ->with(['Branch', 'ClassTypes'])
                ->where('admissions.id', $unique_system_id)
                ->get();

                if ($student->isEmpty()) {
                    $student =  Admission::select('admissions.*', 'sessions.from_year', 'class_types.name as class_name','sessions.to_year', 'gender.name as genderName')
                    ->leftJoin('gender','gender.id','admissions.gender_id')
                    ->leftjoin('sessions','sessions.id','admissions.session_id')
                    ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                    ->with(['Branch', 'ClassTypes'])
                    ->where('admissions.unique_system_id', (string)$unique_system_id)
                    ->get();
                }

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
                $search['session_id'] = $request->has('session_id') ? $request->session_id : '';
                
                $session = !empty($search['session_id']) ? $search['session_id'] : '';
                $branch_id = Session::get('branch_id');
                
                $studentArray=[];
                $hasFilter = $request->isMethod('post') 
                    || !empty($search['course_id']) 
                    || !empty($search['batch']) 
                    || !empty($search['class_type_id']) 
                    || !empty($search['admissionNo']) 
                    || !empty($search['name'])
                    || (!empty($search['session_id']) && $request->has('session_id'));

                if ($hasFilter) {     
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
                $setting = Setting::where('branch_id', Session::get('branch_id'))->first();     
                $studentArray =[];
                foreach($admission_ids as $student){
                    $fees_assigned_query = FeesAssign::where('admission_id',$student->id);
                    if (!empty($session)) {
                        $fees_assigned_query->where('session_id', $session);
                    }
                    $fees_assigned = $fees_assigned_query->first();

                    $fees_collected = FeesDetail::where('admission_id',$student->id)->whereIn('status',[0,1])->sum('total_amount');

                    $isRemaining = (($fees_assigned->total_amount ?? 0)-($fees_assigned->total_discount ?? 0))-($fees_collected ?? 0);
                    if($isRemaining > 0){
                        $getHeadQuery = FeesAssignDetail::Select('fees_assign_details.*','fees_group.name as group_name')
                        ->leftjoin('fees_group','fees_group.id','fees_assign_details.fees_group_id')
                        ->where('admission_id',$student->id);
                        if (!empty($session)) {
                            $getHeadQuery->where('fees_assign_details.session_id', $session);
                        }
                        $getHead = $getHeadQuery->get();
                        //   ->whereNotNull('installment_due_date')
                        //   ->whereDate('installment_due_date','<=', date('Y-m-d'))

                        $AllRemainingFees = '';
                        $total_pending = 0;
                        $remainingAmount = 0;
                        $installment_due_date = '';
                        foreach($getHead as $head){
                            //if($head->installment_due_date >= date('Y-m-d') || $head->installment_due_date === null)
                            //{
                                $feesDetails = FeesDetail::where('admission_id',$student->id)->where('fees_group_id',$head->fees_group_id)->whereIn('status',[0,1])->sum('paid_amount');
                                $remainingAmount = (($head->fees_group_amount ?? 0) - ($head->discount ?? 0) - ($head->promotion_carry_adjustment ?? 0)) - ($feesDetails ?? 0);
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
                $studentName = trim($request->student_name ?? '');
                $fatherName = trim($request->father_name ?? '');
                $mobile = trim($request->mobile ?? '');
                $studentLookup = trim($request->student_lookup ?? '');
                $activeSessionId = Session::get('session_id');
                $activeBranchId = Session::get('branch_id');
                $selectedSessionId = $request->session_id ?: $activeSessionId;

                if(empty($admissionNo) && empty($studentLookup) && (empty($course_id) || empty($batch))){
                    return view('fees.modification.admissionList', [
                        'data' => collect([]),
                        'courseFeesMasters' => collect([]),
                        'assignedDetailMap' => []
                    ]);
                }
                
                $data = Admission::with('ClassTypes.course')
                    ->where('branch_id', $activeBranchId);

                // If class_type_id belongs to a different session than batch, resolve to batch's session class
                if(!empty($class_type_id) && !empty($batch)){
                    $inputClass = ClassType::find($class_type_id);
                    if($inputClass){
                        $batchSessionId = Sessions::where('from_year', $batch)->orWhere('from_year', 'like', $batch . '%')->value('id');
                        if($batchSessionId && $inputClass->session_id != $batchSessionId){
                            $matchedClassId = ClassType::where('name', $inputClass->name)->where('session_id', $batchSessionId)->value('id');
                            if($matchedClassId){
                                $class_type_id = $matchedClassId;
                                $selectedSessionId = $batchSessionId;
                            }
                        }
                    }
                }

                // The operational list follows the student's active admission,
                // not the globally selected financial year. A 2026 batch student
                // must remain visible after the application moves to 2027-28.
                if(empty($class_type_id)){
                    $data->where('status', 1);
                } else {
                    $data->where('session_id', $selectedSessionId);
                }

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
                    $data->where('admissionNo', 'like', '%' . $admissionNo . '%');
                }
                if($studentName !== ''){
                    $data->where(function($q) use ($studentName) {
                        $q->where('first_name', 'like', '%' . $studentName . '%')
                          ->orWhere('last_name', 'like', '%' . $studentName . '%')
                          ->orWhereRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ['%' . $studentName . '%']);
                    });
                }
                if($studentLookup !== ''){
                    $data->where(function($q) use ($studentLookup) {
                        $q->where('admissionNo', 'like', '%' . $studentLookup . '%')
                          ->orWhere('first_name', 'like', '%' . $studentLookup . '%')
                          ->orWhere('last_name', 'like', '%' . $studentLookup . '%')
                          ->orWhere('mobile', 'like', '%' . $studentLookup . '%')
                          ->orWhereRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) LIKE ?", ['%' . $studentLookup . '%']);
                    });
                }
                if($mobile !== ''){
                    $data->where('mobile', 'like', '%' . $mobile . '%');
                }
                if($fatherName !== ''){
                    $data->where('father_name', 'like', '%' . $fatherName . '%');
                }
                if(!empty($request->gender_id)){
                    $data->where('admissions.gender_id', $request->gender_id);
                }
                $data = $data->get()
                    ->sortByDesc('id')
                    ->unique(function ($admission) {
                        return !empty($admission->unique_system_id) ? $admission->unique_system_id : 'admission_' . $admission->id;
                    })
                    ->sortBy('first_name')
                    ->values();
                
                // Determine target session according to the filtered batch or students
                $targetSessionId = $data->isNotEmpty() ? ($data->first()->session_id ?? null) : null;
                if(!$targetSessionId && !empty($batch)){
                    $targetSessionId = Admission::where('batch', $batch)
                        ->where('branch_id', $activeBranchId)
                        ->whereNotNull('session_id')
                        ->value('session_id');
                    if(!$targetSessionId){
                        $startYear = trim(explode('-', $batch)[0]);
                        if(!empty($startYear)){
                            $targetSessionId = Sessions::where('from_year', $startYear)->value('id');
                        }
                    }
                }
                if(!$targetSessionId){
                    $targetSessionId = $activeSessionId;
                }

                $classIdsForSession = ClassType::where('session_id', $targetSessionId);
                if(!empty($class_type_id)){
                    $givenClass = ClassType::find($class_type_id);
                    if($givenClass && $givenClass->session_id != $targetSessionId){
                        $matched = ClassType::where('name', $givenClass->name)->where('session_id', $targetSessionId)->pluck('id')->toArray();
                        $classIdsForSession = !empty($matched) ? $matched : [$class_type_id];
                    } else {
                        $classIdsForSession = [$class_type_id];
                    }
                } elseif($data->isNotEmpty()){
                    $classIdsForSession = $data->pluck('class_type_id')->filter()->unique()->values()->toArray();
                } elseif(!empty($course_id)){
                    $classIdsForSession = $classIdsForSession->where('course_id', $course_id)->pluck('id')->toArray();
                    if(empty($classIdsForSession)){
                        $classIdsForSession = ClassType::where('course_id', $course_id)->pluck('id')->toArray();
                    }
                } else {
                    $classIdsForSession = [];
                }

                $courseFeesMasters = [];
                if(!empty($classIdsForSession)){
                    $courseFeesMasters = FeesMaster::with(['feesGroup', 'ClassTypes'])
                        ->whereIn('class_type_id', $classIdsForSession)
                        ->where('session_id', $targetSessionId)
                        ->where('branch_id', $activeBranchId)
                        ->whereNull('deleted_at')
                        ->orderBy('class_type_id')
                        ->orderBy('id')
                        ->get();
                }

                if($courseFeesMasters->isEmpty()){
                    $courseFeesMasters = FeesMaster::with(['feesGroup', 'ClassTypes'])
                        ->where(function($q) use ($courseClassIds, $targetSessionId, $activeSessionId) {
                            if(!empty($courseClassIds)){
                                $q->whereIn('class_type_id', $courseClassIds);
                            }
                            $q->whereIn('session_id', array_unique(array_filter([$targetSessionId, $activeSessionId])));
                        })
                        ->where('branch_id', $activeBranchId)
                        ->whereNull('deleted_at')
                        ->orderBy('class_type_id')
                        ->orderBy('id')
                        ->get();
                }
                
                // Ensure unique fee heads per class/group
                $courseFeesMasters = $courseFeesMasters->unique(function($item) {
                    return $item->class_type_id . '_' . $item->fees_group_id;
                })->values();

                $studentIds = $data->pluck('id')->toArray();
                $assignedDetails = FeesAssignDetail::whereIn('admission_id', $studentIds)
                    ->where('branch_id', $activeBranchId)
                    ->whereNull('deleted_at')
                    ->get();

                $assignedDetailMap = [];
                foreach($assignedDetails as $ad){
                    $assignedDetailMap[$ad->admission_id . '_' . $ad->fees_master_id] = $ad;
                    $assignedDetailMap[$ad->admission_id . '_group_' . $ad->fees_group_id] = $ad;
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
                
                $admis = Admission::select('id', 'student_type', 'class_type_id', 'session_id', 'gender_id')->find($admission_id);
                if(!$admis){
                    return response()->json(['status' => 'error', 'message' => 'Student not found']);
                }
                
                $fees_master = FeesMaster::find($master_id);
                if(!$fees_master){
                    return response()->json(['status' => 'error', 'message' => 'Fee Master not found']);
                }
                if((int)$fees_master->class_type_id !== (int)$admis->class_type_id){
                    return response()->json(['status' => 'error', 'message' => 'Only the student current semester fee head can be assigned.']);
                }
                
                $fees_groups = FeesGroup::find($fees_master->fees_group_id);
                $studentSessionId = $admis->session_id ?? Session::get('session_id');
                
                if($state == 1){
                    $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                    if(!$feesAssign){
                        $feesAssign = new FeesAssign();
                        $feesAssign->user_id = Session::get('id');
                        $feesAssign->session_id = $studentSessionId;
                        $feesAssign->branch_id = Session::get('branch_id');
                        $feesAssign->admission_id = $admission_id;
                        $feesAssign->total_amount = 0;
                        $feesAssign->net_amount = 0;
                        $feesAssign->save();
                    }
                    
                    $amount = $fees_master->amount ?: 0;
                    $genderId = (int)($admis->gender_id ?? 0);
                    if ($genderId === 1 && !empty($fees_master->boys_amount)) {
                        $amount = (float)$fees_master->boys_amount;
                    } elseif ($genderId === 2 && !empty($fees_master->girls_amount)) {
                        $amount = (float)$fees_master->girls_amount;
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
                        $values->session_id = $studentSessionId;
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
                        $feesAssign->total_discount = $discount_assign_detail;
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
                $branchId = Session::get('branch_id');
                $activeSessionId = Session::get('session_id');
                $userId = Session::get('id');
                $admissionIds = array_values(array_unique(array_filter(array_map('intval', $request->admissionIds ?? []))));
                $feesMasterIds = array_values(array_unique(array_filter(array_map('intval', $request->fees_master_ids ?? []))));

                if(empty($admissionIds)){
                    return response()->json(['status' => 'error', 'message' => 'Please select at least one student!']);
                }

                $admissions = Admission::select('id', 'student_type', 'class_type_id', 'session_id', 'gender_id')
                    ->where('branch_id', $branchId)
                    ->whereIn('id', $admissionIds)
                    ->get()
                    ->keyBy('id');

                if($admissions->isEmpty()){
                    return response()->json(['status' => 'error', 'message' => 'No valid students found for this branch!']);
                }

                // Load all required masters once. Previously these were queried again for
                // every student/head combination, causing large batches to time out.
                $mastersQuery = FeesMaster::with('feesGroup')
                    ->where('branch_id', $branchId)
                    ->where('session_id', $activeSessionId)
                    ->whereNull('deleted_at');

                if(!empty($feesMasterIds)){
                    $mastersQuery->whereIn('id', $feesMasterIds);
                } else {
                    $courseIds = ClassType::whereIn('id', $admissions->pluck('class_type_id')->filter()->unique())
                        ->pluck('course_id')->filter()->unique();
                    $courseClassIds = ClassType::whereIn('course_id', $courseIds)->pluck('id');
                    $mastersQuery->whereIn('class_type_id', $courseClassIds);
                }

                $masters = $mastersQuery->get()->keyBy('id');
                if($masters->isEmpty()){
                    return response()->json(['status' => 'error', 'message' => 'No active fee heads found for the selected course/session!']);
                }

                $assignedCount = 0;
                $updatedTotals = [];
                $updatedDiscounts = [];
                $updatedNets = [];

                DB::transaction(function () use ($admissions, $masters, $branchId, $userId, &$assignedCount, &$updatedTotals, &$updatedDiscounts, &$updatedNets) {
                    $now = now();
                    $validAdmissionIds = $admissions->keys()->all();

                    $feesAssigns = FeesAssign::withTrashed()->whereIn('admission_id', $validAdmissionIds)->get()->keyBy('admission_id');
                    foreach($admissions as $admissionId => $student){
                        $feesAssign = $feesAssigns->get($admissionId);
                        if(!$feesAssign){
                            $feesAssign = new FeesAssign;
                            $feesAssign->admission_id = $admissionId;
                            $feesAssign->total_amount = 0;
                            $feesAssign->net_amount = 0;
                        } elseif($feesAssign->trashed()){
                            $feesAssign->restore();
                        }
                        $feesAssign->user_id = $userId;
                        $feesAssign->branch_id = $branchId;
                        $feesAssign->session_id = $student->session_id ?: Session::get('session_id');
                        $feesAssign->save();
                        $feesAssigns->put($admissionId, $feesAssign);
                    }

                    $masterIds = $masters->keys()->all();
                    $existingDetails = FeesAssignDetail::withTrashed()
                        ->whereIn('admission_id', $validAdmissionIds)
                        ->whereIn('fees_master_id', $masterIds)
                        ->get()
                        ->keyBy(function ($detail) {
                            return $detail->admission_id . '_' . $detail->fees_master_id;
                        });

                    $newDetails = [];
                    foreach($admissions as $admissionId => $student){
                        $feesAssign = $feesAssigns->get($admissionId);
                        // A student receives only the fee heads of the class/semester
                        // currently present on the admission row. Future semesters are
                        // assigned after promotion creates the next admission row.
                        foreach($masters->where('class_type_id', $student->class_type_id) as $master){
                            $key = $admissionId . '_' . $master->id;
                            $detail = $existingDetails->get($key);
                            $amount = $master->amount ?: 0;
                            $genderId = (int)($student->gender_id ?? 0);
                            if ($genderId === 1 && !empty($master->boys_amount)) {
                                $amount = (float)$master->boys_amount;
                            } elseif ($genderId === 2 && !empty($master->girls_amount)) {
                                $amount = (float)$master->girls_amount;
                            }

                            if($detail && !$detail->trashed()){
                                continue;
                            }

                            $row = [
                                'user_id' => $userId,
                                'branch_id' => $branchId,
                                'session_id' => $student->session_id ?: Session::get('session_id'),
                                'fees_group_amount' => $amount ?: 0,
                                'admission_id' => $admissionId,
                                'fees_assign_id' => $feesAssign->id,
                                'class_type_id' => $master->class_type_id,
                                'fees_master_id' => $master->id,
                                'fees_group_id' => $master->fees_group_id,
                                'fees_refund' => optional($master->feesGroup)->fees_refund ?: 'no',
                                'installment_month' => $master->installment_month,
                                'installment_fine' => $master->installment_fine ?: 0,
                                'installment_due_date' => $master->installment_due_date,
                                'updated_at' => $now,
                                'deleted_at' => null,
                            ];

                            if($detail){
                                DB::table('fees_assign_details')->where('id', $detail->id)->update($row);
                            } else {
                                $row['created_at'] = $now;
                                $row['discount'] = 0;
                                $row['discount_percent'] = 0;
                                $newDetails[] = $row;
                            }
                            $assignedCount++;
                        }
                    }

                    foreach(array_chunk($newDetails, 500) as $detailChunk){
                        DB::table('fees_assign_details')->insert($detailChunk);
                    }

                    $totals = FeesAssignDetail::whereIn('admission_id', $validAdmissionIds)
                        ->whereNull('deleted_at')
                        ->groupBy('admission_id')
                        ->select('admission_id', DB::raw('SUM(fees_group_amount) as total_amount'), DB::raw('SUM(discount) as total_discount'))
                        ->get()->keyBy('admission_id');

                    foreach($feesAssigns as $admissionId => $feesAssign){
                        $total = (float) optional($totals->get($admissionId))->total_amount;
                        $discount = (float) optional($totals->get($admissionId))->total_discount;
                        $net = $total - $discount;
                        $feesAssign->total_amount = $total;
                        $feesAssign->total_discount = $discount;
                        $feesAssign->net_amount = $net;
                        $feesAssign->save();
                        $updatedTotals[$admissionId] = $total;
                        $updatedDiscounts[$admissionId] = $discount;
                        $updatedNets[$admissionId] = $net;
                    }
                });
                
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
                try {
                    $admission_id = $request->admission_id;
                    $student = Admission::with('ClassTypes')->find($admission_id);
                    if(!$student){
                        return response()->json(['status' => 'error', 'message' => 'Student record not found']);
                    }

                    $invoices = FeesDetailsInvoices::where('admission_id', $admission_id)
                        ->whereIn('status', [0, 1])
                        ->orderBy('id', 'DESC')
                        ->get();

                    $paidDetails = FeesDetail::leftJoin('fees_group', 'fees_group.id', '=', 'fees_detail.fees_group_id')
                        ->leftJoin('payment_modes', 'payment_modes.id', '=', 'fees_detail.payment_mode_id')
                        ->select('fees_detail.*', 'fees_group.name as fees_group_name', 'payment_modes.name as payment_mode_name')
                        ->where('fees_detail.admission_id', $admission_id)
                        ->whereIn('fees_detail.status', [0, 1])
                        ->where('fees_detail.paid_amount', '>', 0)
                        ->whereNull('fees_detail.deleted_at')
                        ->orderBy('fees_detail.id', 'DESC')
                        ->get();

                    $totalPaid = $paidDetails->sum('paid_amount');
                    $hasPaid = ($totalPaid > 0) || ($paidDetails->count() > 0) || ($invoices->count() > 0);

                    $invoiceList = [];
                    foreach($invoices as $inv){
                        $detailIds = array_filter(explode(',', (string)$inv->fees_details_id));
                        $headNames = '';
                        if(!empty($detailIds)){
                            $headRows = FeesDetail::leftJoin('fees_group', 'fees_group.id', '=', 'fees_detail.fees_group_id')
                                ->whereIn('fees_detail.id', $detailIds)
                                ->select('fees_detail.*', 'fees_group.name as group_name')
                                ->get();
                            $namesList = [];
                            foreach ($headRows as $hr) {
                                $isAdv = ($hr->payment_mode_id == 9)
                                    || (strpos($hr->discount_remark ?? '', 'Excess advance adjusted') !== false)
                                    || (strpos($hr->payment_info ?? '', 'Excess advance adjusted') !== false)
                                    || (strpos($hr->other_fee_remark ?? '', 'Excess advance adjusted') !== false)
                                    || ((int)$hr->receipt_no >= 16521);
                                if ($isAdv) {
                                    $namesList[] = 'Advance Fees';
                                } elseif (!empty($hr->group_name)) {
                                    $namesList[] = $hr->group_name;
                                }
                            }
                            $headNames = implode(', ', array_unique(array_filter($namesList)));
                        }
                        
                        $modeName = '';
                        if(!empty($inv->payment_mode)){
                            if(is_numeric($inv->payment_mode)){
                                $paymentModeObj = PaymentMode::find($inv->payment_mode);
                                $modeName = $paymentModeObj ? $paymentModeObj->name : 'Mode #' . $inv->payment_mode;
                            } else {
                                $modeName = $inv->payment_mode;
                            }
                        }
                        if(empty($modeName)) $modeName = 'Cash';

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
                            'head_name' => $pd->fees_group_name ?? 'Fee Head',
                            'amount' => floatval($pd->paid_amount),
                            'date' => !empty($pd->date) ? date('d-m-Y', strtotime($pd->date)) : '-',
                            'payment_mode' => $pd->payment_mode_name ?? 'Cash',
                            'receipt_no' => $pd->receipt_no ?? '-'
                        ];
                    }

                    return response()->json([
                        'status' => 'success',
                        'has_paid' => $hasPaid,
                        'total_paid' => $totalPaid,
                        'student' => [
                            'id' => $student->id,
                            'unique_system_id' => $student->unique_system_id ?? '',
                            'name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                            'admissionNo' => $student->admissionNo ?? '-',
                            'class_name' => $student->ClassTypes->name ?? '-',
                            'class_type_id' => $student->class_type_id ?? '',
                            'course_id' => $student->ClassTypes->course_id ?? ($student->course ?? ''),
                            'batch' => $student->batch ?? '-',
                            'session_id' => $student->session_id ?? ''
                        ],
                        'invoices' => $invoiceList,
                        'paid_details' => $directDetails
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ], 500);
                }
            }

            public function getStudentOverallLedger(Request $request){
                $current = Admission::with('ClassTypes.course')->findOrFail($request->admission_id);
                $admissions = Admission::with('ClassTypes.course')
                    ->where('branch_id', $current->branch_id)
                    ->when(!empty($current->unique_system_id), function($q) use ($current) {
                        $q->where('unique_system_id', $current->unique_system_id);
                    }, function($q) use ($current) {
                        $q->where('admissionNo', $current->admissionNo);
                    })
                    ->orderBy('id')
                    ->get();

                $sessionMap = Sessions::whereIn('id', $admissions->pluck('session_id')->filter()->unique())
                    ->get()
                    ->mapWithKeys(function($session) {
                        return [$session->id => trim(($session->from_year ?? '') . '-' . ($session->to_year ?? ''), '-')];
                    });

                $semesterLedgers = collect();
                foreach($admissions as $admission){
                    $heads = DB::table('fees_assign_details')
                        ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                        ->where('fees_assign_details.admission_id', $admission->id)
                        ->whereNull('fees_assign_details.deleted_at')
                        ->select('fees_assign_details.*', 'fees_group.name as head_name')
                        ->orderBy('fees_assign_details.id')
                        ->get();
                    $payments = DB::table('fees_detail')
                        ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_detail.fees_group_id')
                        ->leftJoin('payment_modes', 'payment_modes.id', '=', 'fees_detail.payment_mode_id')
                        ->where('fees_detail.admission_id', $admission->id)
                        ->whereIn('fees_detail.status', [0, 1])
                        ->whereNull('fees_detail.deleted_at')
                        ->where('fees_detail.paid_amount', '>', 0)
                        ->select('fees_detail.*', 'fees_group.name as head_name', 'payment_modes.name as mode_name')
                        ->orderBy('fees_detail.date')->orderBy('fees_detail.id')->get();
                    $gross = (float)$heads->sum('fees_group_amount');
                    $discount = (float)$heads->sum('discount');
                    $carry = (float)$heads->sum('promotion_carry_adjustment');
                    $net = max(0, $gross - $discount - $carry);
                    $paid = (float)$payments->sum('paid_amount');
                    $due = max(0, $net - $paid);
                    $sessionName = $sessionMap->get($admission->session_id, 'Session');
                    $semesterLedgers->push(compact('admission','heads','payments','gross','discount','carry','net','paid','due','sessionName'));
                }
                return view('fees.modification.overallStudentLedger', [
                    'student' => $current,
                    'semesterLedgers' => $semesterLedgers,
                    'overallGross' => $semesterLedgers->sum('gross'),
                    'overallDiscount' => $semesterLedgers->sum('discount'),
                    'overallNet' => $semesterLedgers->sum('net'),
                    'overallPaid' => $semesterLedgers->sum('paid'),
                    'overallDue' => $semesterLedgers->sum('due'),
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

                // Attach accurate paid_amount from FeesDetail for each fee head
                foreach($assignedDetails as $detail){
                    $collectedPaid = FeesDetail::where('admission_id', $admission_id)
                        ->where('fees_group_id', $detail->fees_group_id)
                        ->whereIn('status', [0, 1])
                        ->whereNull('deleted_at')
                        ->sum('paid_amount');
                    $detail->paid_amount = floatval($collectedPaid ?? ($detail->paid_amount ?? 0));
                }
                    
                $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                $assignedMasterIds = $assignedDetails->pluck('fees_master_id')->filter()->map(function($id) {
                    return (int)$id;
                })->unique()->all();
                $assignedGroupIds = $assignedDetails->pluck('fees_group_id')->filter()->map(function($id) {
                    return (int)$id;
                })->unique()->all();
                $assignedHeadNames = $assignedDetails->pluck('fees_group_name')->map(function($n) {
                    return strtolower(trim(preg_replace('/\s+/', ' ', $n ?? '')));
                })->filter()->unique()->all();

                $branchId = Session::get('branch_id') ?: $student->branch_id;
                $availableFeeMasters = FeesMaster::with('feesGroup')
                    ->when(!empty($branchId), function($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    })
                    ->where('class_type_id', $student->class_type_id)
                    ->where('session_id', $student->session_id)
                    ->whereNull('deleted_at')
                    ->when(!empty($student->batch), function($query) use ($student) {
                        $query->where(function($q) use ($student) {
                            $q->where('batch', $student->batch)->orWhereNull('batch');
                        });
                    })
                    ->when(!empty($assignedMasterIds), function($query) use ($assignedMasterIds) {
                        $query->whereNotIn('id', $assignedMasterIds);
                    })
                    ->when(!empty($assignedGroupIds), function($query) use ($assignedGroupIds) {
                        $query->whereNotIn('fees_group_id', $assignedGroupIds);
                    })
                    ->orderBy('id')->get();

                // Double filter in-memory to ensure any sibling records or matching head names/previous dues are excluded
                $availableFeeMasters = $availableFeeMasters->filter(function($master) use ($student, $assignedMasterIds, $assignedGroupIds, $assignedHeadNames) {
                    if (in_array((int)$master->id, $assignedMasterIds, true)) {
                        return false;
                    }
                    if ($master->fees_group_id && in_array((int)$master->fees_group_id, $assignedGroupIds, true)) {
                        return false;
                    }
                    if (!empty($student->batch) && !empty($master->batch) && (string)$master->batch !== (string)$student->batch) {
                        return false;
                    }
                    $groupName = strtolower(trim(preg_replace('/\s+/', ' ', optional($master->feesGroup)->name ?? '')));
                    if (empty($groupName) || str_contains($groupName, 'previous dues')) {
                        return false;
                    }
                    if (in_array($groupName, $assignedHeadNames, true)) {
                        return false;
                    }
                    return true;
                })->unique(function($m) {
                    return strtolower(trim(preg_replace('/\s+/', ' ', optional($m->feesGroup)->name ?? ''))) ?: $m->id;
                })->values();
                
                return view('fees.fees.student_fee_edit_modal_content', compact('student', 'assignedDetails', 'feesAssign', 'availableFeeMasters'));
            }

            public function updateStudentFeeDetailInline(Request $request){
                $detail_id = $request->detail_id;
                $detail = FeesAssignDetail::find($detail_id);
                if(!$detail){
                    return response()->json(['status' => 'error', 'message' => 'Fee record not found'], 404);
                }

                $collectedPaid = FeesDetail::where('admission_id', $detail->admission_id)
                    ->where('fees_group_id', $detail->fees_group_id)
                    ->whereIn('status', [0, 1])
                    ->whereNull('deleted_at')
                    ->sum('paid_amount');
                $paidAmount = floatval($collectedPaid ?? ($detail->paid_amount ?? 0));

                $newAmount = $request->has('amount') ? floatval($request->amount) : floatval($detail->fees_group_amount);
                $newDiscount = $request->has('discount') ? floatval($request->discount) : floatval($detail->discount);

                if($newAmount < $paidAmount){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Fee head amount (₹' . number_format($newAmount, 0) . ') cannot be less than already collected amount (₹' . number_format($paidAmount, 0) . ')'
                    ], 422);
                }

                $maxDiscount = max(0, $newAmount - $paidAmount);
                if($newDiscount > $maxDiscount){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Discount (₹' . number_format($newDiscount, 0) . ') cannot exceed pending balance (₹' . number_format($maxDiscount, 0) . ')'
                    ], 422);
                }
                
                if($request->has('amount')){
                    $detail->fees_group_amount = $newAmount;
                }
                if($request->has('discount')){
                    $detail->discount = $newDiscount;
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
                        'total_discount' => $discount,
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
                $student = Admission::find($admission_id);
                if(!$student){
                    return response()->json(['status' => 'error', 'message' => 'Student record not found'], 404);
                }
                if(!empty($details) && is_array($details)){
                    // 1. Validation loop to ensure no row violates constraints
                    foreach($details as $item){
                        if(isset($item['id'])){
                            $detail = FeesAssignDetail::find($item['id']);
                            if($detail && $detail->admission_id == $admission_id){
                                $collectedPaid = FeesDetail::where('admission_id', $admission_id)
                                    ->where('fees_group_id', $detail->fees_group_id)
                                    ->whereIn('status', [0, 1])
                                    ->whereNull('deleted_at')
                                    ->sum('paid_amount');
                                $paidAmount = floatval($collectedPaid ?? ($detail->paid_amount ?? 0));
                                
                                $newAmt = isset($item['amount']) ? floatval($item['amount']) : floatval($detail->fees_group_amount);
                                $newDisc = isset($item['discount']) ? floatval($item['discount']) : floatval($detail->discount);

                                if($newAmt < $paidAmount){
                                    $headName = DB::table('fees_group')->where('id', $detail->fees_group_id)->value('name') ?? 'Fee Head';
                                    return response()->json([
                                        'status' => 'error',
                                        'message' => "Amount for {$headName} (₹" . number_format($newAmt, 0) . ") cannot be less than collected amount (₹" . number_format($paidAmount, 0) . ")"
                                    ], 422);
                                }

                                $maxDisc = max(0, $newAmt - $paidAmount);
                                if($newDisc > $maxDisc){
                                    $headName = DB::table('fees_group')->where('id', $detail->fees_group_id)->value('name') ?? 'Fee Head';
                                    return response()->json([
                                        'status' => 'error',
                                        'message' => "Discount for {$headName} (₹" . number_format($newDisc, 0) . ") cannot exceed pending balance (₹" . number_format($maxDisc, 0) . ")"
                                    ], 422);
                                }
                            }
                        } elseif(!empty($item['fees_master_id'])) {
                            $master = FeesMaster::where('id', $item['fees_master_id'])
                                ->where('branch_id', Session::get('branch_id'))
                                ->where('class_type_id', $student->class_type_id)
                                ->where('session_id', $student->session_id)
                                ->whereNull('deleted_at')->first();
                            if(!$master){
                                return response()->json(['status' => 'error', 'message' => 'Selected fee head is not available for this class / semester.'], 422);
                            }
                            $newAmt = max(0, floatval($item['amount'] ?? 0));
                            $newDisc = max(0, floatval($item['discount'] ?? 0));
                            if($newDisc > $newAmt){
                                return response()->json(['status' => 'error', 'message' => 'Discount cannot exceed the new fee-head amount.'], 422);
                            }
                        }
                    }

                    // 2. Save records after validation passes
                    DB::transaction(function() use ($details, $admission_id, $student) {
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
                        } elseif(!empty($item['fees_master_id'])) {
                            $master = FeesMaster::with('feesGroup')->where('id', $item['fees_master_id'])
                                ->where('branch_id', Session::get('branch_id'))
                                ->where('class_type_id', $student->class_type_id)
                                ->where('session_id', $student->session_id)
                                ->whereNull('deleted_at')->first();
                            if(!$master) continue;
                            $feesAssign = FeesAssign::firstOrCreate(
                                ['admission_id' => $admission_id],
                                ['user_id' => Session::get('id'), 'branch_id' => Session::get('branch_id'), 'session_id' => $student->session_id, 'total_amount' => 0, 'total_discount' => 0, 'net_amount' => 0]
                            );
                            $detail = FeesAssignDetail::withTrashed()->where('admission_id', $admission_id)->where('fees_master_id', $master->id)->first();
                            if(!$detail) $detail = new FeesAssignDetail;
                            $detail->user_id = Session::get('id');
                            $detail->branch_id = Session::get('branch_id');
                            $detail->session_id = $student->session_id;
                            $detail->admission_id = $admission_id;
                            $detail->fees_assign_id = $feesAssign->id;
                            $detail->class_type_id = $student->class_type_id;
                            $detail->fees_master_id = $master->id;
                            $detail->fees_group_id = $master->fees_group_id;
                            $detail->fees_group_amount = max(0, floatval($item['amount'] ?? 0));
                            $detail->discount = max(0, floatval($item['discount'] ?? 0));
                            $detail->discount_percent = 0;
                            $detail->fees_refund = optional($master->feesGroup)->fees_refund ?: 'no';
                            $detail->installment_month = $master->installment_month;
                            $detail->installment_fine = max(0, floatval($item['fine'] ?? $master->installment_fine ?? 0));
                            $detail->installment_due_date = !empty($item['due_date']) ? $item['due_date'] : $master->installment_due_date;
                            $detail->deleted_at = null;
                            $detail->save();
                        }
                    }
                    });
                }
                
                $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                if(!$feesAssign){
                    $feesAssign = new FeesAssign();
                    $feesAssign->user_id = Session::get('id');
                    $feesAssign->branch_id = Session::get('branch_id');
                    $feesAssign->session_id = $student->session_id;
                    $feesAssign->admission_id = $admission_id;
                    $feesAssign->total_amount = 0;
                    $feesAssign->total_discount = 0;
                    $feesAssign->net_amount = 0;
                    $feesAssign->save();
                }
                $total = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('fees_group_amount');
                $discount = FeesAssignDetail::where('admission_id', $admission_id)->whereNull('deleted_at')->sum('discount');
                $updateData = [
                    'total_amount' => $total,
                    'total_discount' => $discount,
                    'net_amount' => ($total - $discount)
                ];
                if ($request->has('remark')) {
                    $updateData['remark'] = $request->remark;
                }
                $feesAssign->update($updateData);
                $net = $total - $discount;
                $savedRemark = $request->has('remark') ? $request->remark : ($feesAssign->remark ?? '');
                
                return response()->json([
                    'status' => 'success',
                    'total_amount' => $total,
                    'total_discount' => $discount,
                    'net_amount' => $net,
                    'remark' => $savedRemark,
                    'message' => 'All fee details updated successfully'
                ]);
            }

            public function updateStudentFeeRemark(Request $request){
                $admission_id = $request->admission_id;
                $remark = $request->remark;
                $student = Admission::find($admission_id);
                if(!$student){
                    return response()->json(['status' => 'error', 'message' => 'Student record not found'], 404);
                }

                $feesAssign = FeesAssign::where('admission_id', $admission_id)->first();
                if(!$feesAssign){
                    $feesAssign = new FeesAssign();
                    $feesAssign->user_id = Session::get('id');
                    $feesAssign->branch_id = Session::get('branch_id');
                    $feesAssign->session_id = $student->session_id;
                    $feesAssign->admission_id = $admission_id;
                    $feesAssign->total_amount = 0;
                    $feesAssign->total_discount = 0;
                    $feesAssign->net_amount = 0;
                }
                $feesAssign->remark = $remark;
                $feesAssign->save();

                return response()->json([
                    'status' => 'success',
                    'admission_id' => $admission_id,
                    'remark' => $remark,
                    'message' => 'Remark saved successfully'
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
                $selectedSessionId = $request->session_id ?? Session::get('session_id');
                $classQuery = ClassType::where('session_id', $selectedSessionId)
                    ->where('branch_id', Session::get('branch_id'))
                    ->orderBy('orderBy', 'ASC');
                $allClassType = $classQuery->get();
                $classType = $allClassType;
                $getFeesGroup = Helper::getFeesGroup();
                $getSession = Helper::getSession();

                $serach['course_id'] = $request->course_id ?? '';
                $serach['class_type_id'] = $request->class_type_id ?? '';
                $serach['session_id'] = $request->session_id ?? Session::get('session_id');
                $serach['batch'] = $request->batch ?? '';
                $serach['admissionNo'] = $request->admissionNo ?? ($request->admission_no ?? '');

                if (!empty($serach['batch'])) {
                    $batchSessionId = Sessions::where('from_year', $serach['batch'])->orWhere('from_year', 'like', $serach['batch'] . '%')->value('id');
                    if ($batchSessionId) {
                        $selectedSessionId = $batchSessionId;
                        $serach['session_id'] = $batchSessionId;
                        $classQuery = ClassType::where('session_id', $batchSessionId)
                            ->where('branch_id', Session::get('branch_id'))
                            ->orderBy('orderBy', 'ASC');
                        $allClassType = $classQuery->get();
                        $classType = $allClassType;
                        if (!empty($serach['class_type_id'])) {
                            $inputClass = ClassType::find($serach['class_type_id']);
                            if ($inputClass && $inputClass->session_id != $batchSessionId) {
                                $matchedClassId = ClassType::where('name', $inputClass->name)->where('session_id', $batchSessionId)->value('id');
                                if ($matchedClassId) {
                                    $serach['class_type_id'] = $matchedClassId;
                                }
                            }
                        }
                    }
                }

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
                        $admis = Admission::select('student_type', 'gender_id')->find($admission);
                      
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
                                        $genderId = (int)($admis->gender_id ?? 0);
                                        if ($genderId === 1 && !empty($fees_master->boys_amount)) {
                                            $amount = (float)$fees_master->boys_amount;
                                        } elseif ($genderId === 2 && !empty($fees_master->girls_amount)) {
                                            $amount = (float)$fees_master->girls_amount;
                                        }
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
                $activeSessionId = Session::get('session_id');
                $activeBranchId = Session::get('branch_id');
                $batch = $request->filled('batch') ? trim($request->batch) : null;
                $courseId = $request->filled('course_id') ? (int)$request->course_id : null;
                $classTypeId = $request->filled('class_type_id') ? (int)$request->class_type_id : null;

                $targetSessionId = $request->session_id ?: null;
                if(!$targetSessionId && !empty($batch)){
                    $targetSessionId = Admission::where('batch', $batch)
                        ->where('branch_id', $activeBranchId)
                        ->whereNotNull('session_id')
                        ->value('session_id');
                    if(!$targetSessionId){
                        $startYear = trim(explode('-', $batch)[0]);
                        if(!empty($startYear)){
                            $targetSessionId = Sessions::where('from_year', $startYear)->value('id');
                        }
                    }
                }
                if(!$targetSessionId && !empty($classTypeId)){
                    $targetSessionId = ClassType::where('id', $classTypeId)->value('session_id');
                }
                if(!$targetSessionId){
                    $targetSessionId = $activeSessionId;
                }

                $buildBaseQuery = function($sessId = null) use ($activeBranchId, $batch, $courseId) {
                    $q = FeesMaster::select('fees_master.*','fees_group.name as fees_group_name', 'fees_group.group_type as fees_group_type', 'class_types.name as class_name')
                        ->leftJoin('fees_group','fees_group.id','fees_master.fees_group_id')
                        ->leftJoin('class_types','class_types.id','fees_master.class_type_id')
                        ->where('fees_master.branch_id', $activeBranchId)
                        ->whereNull('fees_master.deleted_at');

                    if ($sessId) {
                        $q->where('fees_master.session_id', $sessId);
                    }
                    if ($batch) {
                        $q->where(function($bQ) use ($batch) {
                            $bQ->where('fees_master.batch', $batch)->orWhereNull('fees_master.batch');
                        });
                    }
                    if ($courseId) {
                        $q->where(function($cQ) use ($courseId) {
                            $cQ->where('fees_master.course_id', $courseId)
                               ->orWhere('class_types.course_id', $courseId);
                        });
                    }
                    return $q;
                };

                $query = $buildBaseQuery($targetSessionId);

                if(!empty($classTypeId)){
                    $classObj = ClassType::find($classTypeId);
                    if($classObj && $classObj->session_id != $targetSessionId){
                        $matchedClassId = ClassType::where('name', $classObj->name)->where('session_id', $targetSessionId)->value('id');
                        $query->where('fees_master.class_type_id', $matchedClassId ?: $classTypeId);
                    } else {
                        $query->where('fees_master.class_type_id', $classTypeId);
                    }
                } elseif(!empty($courseId)){
                    $classIds = ClassType::where('course_id', $courseId)->where('session_id', $targetSessionId)->pluck('id')->toArray();
                    if(empty($classIds)){
                        $classIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();
                    }
                    if (!empty($classIds)) {
                        $query->whereIn('fees_master.class_type_id', $classIds);
                    }
                }
                $masterData = $query->orderBy('fees_master.class_type_id')->orderBy('fees_master.id')->get();

                if($masterData->isEmpty() && $targetSessionId != $activeSessionId){
                    $fallbackQuery = $buildBaseQuery($activeSessionId);
                    if(!empty($classTypeId)){
                        $fallbackClassObj = ClassType::find($classTypeId);
                        if($fallbackClassObj){
                            $matchedInActive = ClassType::where('name', $fallbackClassObj->name)->where('session_id', $activeSessionId)->value('id');
                            if($matchedInActive){
                                $fallbackQuery->where('fees_master.class_type_id', $matchedInActive);
                            }
                        }
                    } elseif(!empty($courseId)){
                        $activeClassIds = ClassType::where('course_id', $courseId)->where('session_id', $activeSessionId)->pluck('id')->toArray();
                        if(!empty($activeClassIds)){
                            $fallbackQuery->whereIn('fees_master.class_type_id', $activeClassIds);
                        }
                    }
                    $masterData = $fallbackQuery->orderBy('fees_master.class_type_id')->orderBy('fees_master.id')->get();
                }

                // If still empty and batch + courseId (or class) is given, find across ANY session for this cohort!
                if ($masterData->isEmpty() && (!empty($courseId) || !empty($classTypeId))) {
                    $anySessionQuery = $buildBaseQuery(null);
                    if (!empty($classTypeId)) {
                        $classObj = ClassType::find($classTypeId);
                        if ($classObj) {
                            $siblingIds = ClassType::where('course_id', $classObj->course_id)
                                ->where(function($sq) use ($classObj) {
                                    $sq->where('name', $classObj->name)->orWhere('orderBy', $classObj->orderBy);
                                })
                                ->pluck('id')
                                ->toArray();
                            if (!empty($siblingIds)) {
                                $anySessionQuery->whereIn('fees_master.class_type_id', $siblingIds);
                            }
                        }
                    } elseif (!empty($courseId)) {
                        $anyClassIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();
                        if (!empty($anyClassIds)) {
                            $anySessionQuery->whereIn('fees_master.class_type_id', $anyClassIds);
                        }
                    }
                    $masterData = $anySessionQuery->orderBy('fees_master.class_type_id')->orderBy('fees_master.id')->get();
                }

                // Keep class-wise rows distinct. The same fee group may be configured
                // separately for multiple semesters with a different amount/due date.
                return $masterData->unique('id')->values();
            }
        
            public function caReport(Request $request){
                $branchId = Session::get('branch_id');
                $search = [
                    'session_id' => $request->session_id ?? Session::get('session_id'),
                    'course_id' => $request->course_id ?? '',
                    'batch' => $request->batch ?? '',
                    'starting' => $request->starting ?? '',
                    'ending' => $request->ending ?? '',
                    'payment_mode' => $request->payment_mode ?? '',
                    'user_id' => $request->user_id ?? '',
                    'receipt_status' => $request->receipt_status ?? '',
                    'receipt_no' => $request->receipt_no ?? '',
                    'student' => $request->student ?? '',
                ];

                $query = FeesDetailsInvoices::query()
                    ->select(
                        'fees_details_invoices.*', 'admissions.admissionNo', 'admissions.first_name',
                        'admissions.last_name', 'admissions.mobile', 'admissions.course', 'admissions.batch',
                        'class_types.name as class_name', 'payment_modes.name as payment_mode_name',
                        'users.first_name as collector_first_name', 'users.last_name as collector_last_name'
                    )
                    ->leftJoin('admissions', 'admissions.id', '=', 'fees_details_invoices.admission_id')
                    ->leftJoin('class_types', 'class_types.id', '=', 'admissions.class_type_id')
                    ->leftJoin('payment_modes', 'payment_modes.id', '=', 'fees_details_invoices.payment_mode')
                    ->leftJoin('users', 'users.id', '=', 'fees_details_invoices.user_id')
                    ->where('fees_details_invoices.branch_id', $branchId)
                    ->where('admissions.school', 1);

                if ($search['session_id'] !== '') $query->where('fees_details_invoices.session_id', $search['session_id']);
                if ($search['course_id'] !== '') {
                    $course = \App\Models\Master\Course::find($search['course_id']);
                    $query->where(function ($q) use ($search, $course) {
                        $q->where('class_types.course_id', $search['course_id']);
                        if ($course) $q->orWhere('admissions.course', $course->name);
                    });
                }
                if ($search['batch'] !== '') $query->where('admissions.batch', $search['batch']);
                if ($search['starting'] !== '') $query->whereDate('fees_details_invoices.payment_date', '>=', $search['starting']);
                if ($search['ending'] !== '') $query->whereDate('fees_details_invoices.payment_date', '<=', $search['ending']);
                if ($search['payment_mode'] !== '') $query->where('fees_details_invoices.payment_mode', $search['payment_mode']);
                if ($search['receipt_status'] !== '') $query->where('fees_details_invoices.status', $search['receipt_status']);
                if ($search['user_id'] !== '') $query->where('fees_details_invoices.user_id', $search['user_id']);
                if (Session::get('role_id') > 1) $query->where('fees_details_invoices.user_id', Session::get('id'));
                if ($search['receipt_no'] !== '') {
                    $query->where('fees_details_invoices.invoice_no', 'like', '%' . $search['receipt_no'] . '%');
                }
                if ($search['student'] !== '') {
                    $term = $search['student'];
                    $query->where(function ($q) use ($term) {
                        $q->where('admissions.admissionNo', 'like', "%{$term}%")
                            ->orWhere('admissions.first_name', 'like', "%{$term}%")
                            ->orWhere('admissions.last_name', 'like', "%{$term}%")
                            ->orWhere('admissions.mobile', 'like', "%{$term}%");
                    });
                }

                $totals = (clone $query)->reorder()->select(DB::raw(
                    'COUNT(DISTINCT fees_details_invoices.id) as receipt_count,
                     SUM(CASE WHEN fees_details_invoices.status = 0 THEN fees_details_invoices.amount ELSE 0 END) as received_amount,
                     SUM(CASE WHEN fees_details_invoices.status = 0 THEN fees_details_invoices.discount ELSE 0 END) as discount_amount,
                     SUM(CASE WHEN fees_details_invoices.status = 0 THEN fees_details_invoices.total_fine ELSE 0 END) as fine_amount,
                     SUM(CASE WHEN fees_details_invoices.status = 1 THEN 1 ELSE 0 END) as pending_count,
                     SUM(CASE WHEN fees_details_invoices.status NOT IN (0,1) THEN 1 ELSE 0 END) as cancelled_count'
                ))->first();

                $invoices = $query->orderByDesc('fees_details_invoices.payment_date')
                    ->orderByDesc('fees_details_invoices.id')->paginate(100)->withQueryString();
                $paymentModes = PaymentMode::orderBy('name')->get();

                if ($request->ajax()) {
                    return response()->json(['html' => view('fees.reports.ca_invoice_rows', compact('invoices', 'totals'))->render()]);
                }

                return view('fees.reports.CA', compact('invoices', 'totals', 'paymentModes', 'search'));
            }

            public function caReportLegacyUnused(Request $request){
                $activeBranchId = Session::get('branch_id');
                $activeSessionId = Session::get('session_id');

                $search['name'] = $request->name ?? '';
                $search['user_id'] = $request->user_id ?? '';
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course_id'] = $request->course_id ?? '';
                $search['starting'] = $request->starting ?? '';
                $search['ending'] = $request->ending ?? '';
                $search['admission_no'] = $request->admission_no ?? '';
                $search['batch'] = $request->batch ?? '';
                $search['session_id'] = $request->has('session_id') ? $request->session_id : ($activeSessionId ?? '');
                $search['payment_status'] = $request->payment_status ?? '';
                $search['payment_mode'] = $request->payment_mode ?? '';
                $search['tab'] = $request->tab ?? 'ledger';

                // 1. Build Students Query
                $studentsQuery = Admission::with(['ClassTypes', 'gender'])
                    ->where('admissions.branch_id', $activeBranchId)
                    ->where('admissions.school', 1);

                if(!empty($search['session_id'])){
                    $studentsQuery->where('admissions.session_id', $search['session_id']);
                }
                if(!empty($search['course_id'])){
                    $courseId = $search['course_id'];
                    $courseObj = \App\Models\Master\Course::find($courseId);
                    $courseName = $courseObj ? $courseObj->name : null;
                    $studentsQuery->where(function($q) use ($courseId, $courseName) {
                        $q->whereHas('ClassTypes', function($cq) use ($courseId) {
                            $cq->where('course_id', $courseId);
                        });
                        if($courseName){
                            $q->orWhere('admissions.course', $courseName);
                        }
                    });
                }
                if(!empty($search['class_type_id'])){
                    $studentsQuery->where('admissions.class_type_id', $search['class_type_id']);
                }
                if(!empty($search['batch'])){
                    $studentsQuery->where('admissions.batch', $search['batch']);
                }
                if(!empty($search['admission_no'])){
                    $studentsQuery->where('admissions.admissionNo', $search['admission_no']);
                }
                if(!empty($search['name'])){
                    $val = $search['name'];
                    $studentsQuery->where(function($q) use ($val) {
                        $q->where('admissions.first_name', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.last_name', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.father_name', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.admissionNo', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.mobile', 'LIKE', '%' . $val . '%');
                    });
                }

                // One operational row per student: within the selected financial year,
                // prefer the currently active semester admission and then the latest row.
                // When a semester filter is explicitly selected the historical semester
                // remains available, but duplicate admission rows are still suppressed.
                $rawStudents = $studentsQuery
                    ->orderBy('admissions.first_name', 'ASC')
                    ->orderByDesc('admissions.status')
                    ->orderByDesc('admissions.id')
                    ->get()
                    ->unique(function($student) {
                        if(!empty($student->unique_system_id)){
                            return 'uid:' . $student->unique_system_id;
                        }

                        return 'adm:' . strtoupper(trim((string)$student->admissionNo));
                    })
                    ->values();
                $allStudentIds = $rawStudents->pluck('id')->toArray();

                // 2. Query Assigned Fees (Gross & Discount) per student
                $assignedQuery = DB::table('fees_assign_details')
                    ->whereIn('admission_id', $allStudentIds)
                    ->where('branch_id', $activeBranchId)
                    ->whereNull('deleted_at');
                if(!empty($search['session_id'])){
                    $assignedQuery->where('session_id', $search['session_id']);
                }
                $assignedSummary = $assignedQuery->groupBy('admission_id')
                    ->select(
                        'admission_id',
                        DB::raw('SUM(fees_group_amount) as total_gross'),
                        DB::raw('SUM(discount) as total_discount'),
                        DB::raw('SUM(COALESCE(promotion_carry_adjustment, 0)) as total_carry_adjustment'),
                        DB::raw('COUNT(id) as total_heads')
                    )
                    ->get()
                    ->keyBy('admission_id');

                // 3. Query Paid Fees & Fines per student
                $paidQuery = DB::table('fees_detail')
                    ->whereIn('admission_id', $allStudentIds)
                    ->where('branch_id', $activeBranchId)
                    ->whereIn('status', [0, 1])
                    ->whereNull('deleted_at');
                if(!empty($search['session_id'])){
                    $paidQuery->where('session_id', $search['session_id']);
                }
                if(!empty($search['starting'])){
                    $paidQuery->whereDate('date', '>=', $search['starting']);
                }
                if(!empty($search['ending'])){
                    $paidQuery->whereDate('date', '<=', $search['ending']);
                }
                if(!empty($search['payment_mode'])){
                    $paidQuery->where('payment_mode_id', $search['payment_mode']);
                }
                if(!empty($search['user_id'])){
                    $paidQuery->where('user_id', $search['user_id']);
                }
                $paidSummary = $paidQuery->groupBy('admission_id')
                    ->select(
                        'admission_id',
                        DB::raw('SUM(paid_amount) as total_paid'),
                        DB::raw('SUM(installment_fine) as total_fine')
                    )
                    ->get()
                    ->keyBy('admission_id');

                // 4. Map students with calculations & Apply Payment Status Filter
                $studentList = collect([]);
                $grandGross = 0;
                $grandDiscount = 0;
                $grandCarryAdjustment = 0;
                $grandNet = 0;
                $grandPaid = 0;
                $grandPending = 0;
                $grandFine = 0;
                $assignedCount = 0;

                foreach($rawStudents as $st){
                    $gross = (float)($assignedSummary[$st->id]->total_gross ?? 0);
                    $disc = (float)($assignedSummary[$st->id]->total_discount ?? 0);
                    $carryAdjustment = (float)($assignedSummary[$st->id]->total_carry_adjustment ?? 0);
                    $net = max(0, $gross - $disc - $carryAdjustment);
                    $paid = (float)($paidSummary[$st->id]->total_paid ?? 0);
                    $fine = (float)($paidSummary[$st->id]->total_fine ?? 0);
                    $due = max(0, $net - $paid);
                    $headsCount = (int)($assignedSummary[$st->id]->total_heads ?? 0);

                    // Determine Status
                    if($gross == 0){
                        $statusKey = 'not_assigned';
                        $statusLabel = 'Not Assigned';
                        $statusClass = 'badge-secondary';
                    } elseif($due == 0 && $paid > 0){
                        $statusKey = 'paid';
                        $statusLabel = 'Fully Paid';
                        $statusClass = 'badge-success';
                    } elseif($paid > 0 && $due > 0){
                        $statusKey = 'partial';
                        $statusLabel = 'Partially Paid';
                        $statusClass = 'badge-warning';
                    } else {
                        $statusKey = 'unpaid';
                        $statusLabel = 'Unpaid / Due';
                        $statusClass = 'badge-danger';
                    }

                    // Filter by Payment Status if requested
                    if(!empty($search['payment_status']) && $search['payment_status'] != 'all' && $search['payment_status'] !== $statusKey){
                        continue;
                    }

                    // Attach calculated fields
                    $st->gross_amount = $gross;
                    $st->discount_amount = $disc;
                    $st->carry_adjustment_amount = $carryAdjustment;
                    $st->net_amount = $net;
                    $st->paid_amount = $paid;
                    $st->pending_amount = $due;
                    $st->fine_amount = $fine;
                    $st->heads_count = $headsCount;
                    $st->fee_status_key = $statusKey;
                    $st->fee_status_label = $statusLabel;
                    $st->fee_status_class = $statusClass;

                    if($gross > 0) $assignedCount++;
                    $grandGross += $gross;
                    $grandDiscount += $disc;
                    $grandCarryAdjustment += $carryAdjustment;
                    $grandNet += $net;
                    $grandPaid += $paid;
                    $grandPending += $due;
                    $grandFine += $fine;

                    $studentList->push($st);
                }

                // 5. Invoices Query (Transaction Receipts)
                $invoicesQuery = FeesDetailsInvoices::select(
                    'fees_details_invoices.*',
                    'class.name as class_name',
                    'admissions.image',
                    'admissions.mobile',
                    'admissions.admissionNo',
                    'admissions.first_name',
                    'admissions.last_name',
                    'users.first_name as users_first_name',
                    'users.last_name as users_last_name',
                    'admissions.category',
                    'admissions.student_type',
                    'admissions.course',
                    'admissions.batch',
                    'admissions.status as ad_status',
                    'admissions.gender_id',
                    'admissions.father_name',
                    'admissions.school',
                    'payment_modes.name as payment_mode_name',
                    'payment_modes.id as payment_mode_id'
                )
                ->leftJoin('admissions', 'admissions.id', '=', 'fees_details_invoices.admission_id')
                ->leftJoin('class_types as class', 'class.id', '=', 'admissions.class_type_id')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'fees_details_invoices.payment_mode')
                ->leftJoin('users', 'users.id', '=', 'fees_details_invoices.user_id')
                ->where('fees_details_invoices.branch_id', $activeBranchId)
                ->where('admissions.school', 1);

                if(!empty($search['session_id'])){
                    $invoicesQuery->where('fees_details_invoices.session_id', $search['session_id']);
                }
                if(!empty($search['course_id'])){
                    $courseId = $search['course_id'];
                    $courseObj = \App\Models\Master\Course::find($courseId);
                    $courseName = $courseObj ? $courseObj->name : null;
                    $invoicesQuery->where(function($q) use ($courseId, $courseName) {
                        $q->where('class.course_id', $courseId);
                        if ($courseName) {
                            $q->orWhere('admissions.course', $courseName);
                        }
                    });
                }
                if(!empty($search['class_type_id'])){
                    $invoicesQuery->where('admissions.class_type_id', $search['class_type_id']);
                }
                if(!empty($search['batch'])){
                    $invoicesQuery->where('admissions.batch', $search['batch']);
                }
                if(!empty($search['admission_no'])){
                    $invoicesQuery->where('admissions.admissionNo', $search['admission_no']);
                }
                if(!empty($search['name'])){
                    $val = $search['name'];
                    $invoicesQuery->where(function($q) use ($val) {
                        $q->where('admissions.first_name', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.last_name', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.father_name', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.admissionNo', 'LIKE', '%' . $val . '%')
                          ->orWhere('admissions.mobile', 'LIKE', '%' . $val . '%')
                          ->orWhere('fees_details_invoices.invoice_no', 'LIKE', '%' . $val . '%');
                    });
                }
                if(!empty($search['starting'])){
                    $invoicesQuery->whereDate('fees_details_invoices.payment_date', '>=', $search['starting']);
                }
                if(!empty($search['ending'])){
                    $invoicesQuery->whereDate('fees_details_invoices.payment_date', '<=', $search['ending']);
                }
                if(!empty($search['payment_mode'])){
                    $invoicesQuery->where('fees_details_invoices.payment_mode', $search['payment_mode']);
                }
                if(!empty($search['user_id'])){
                    $invoicesQuery->where('fees_details_invoices.user_id', $search['user_id']);
                }
                if(Session::get('role_id') > 1){
                    $invoicesQuery->where('fees_details_invoices.user_id', Session::get('id'));
                }
                $invoices = $invoicesQuery->orderBy('fees_details_invoices.id', 'DESC')->get();

                // 6. Fee Head Breakdown Summary
                $headSummaryQuery = DB::table('fees_assign_details')
                    ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                    ->whereIn('fees_assign_details.admission_id', $allStudentIds)
                    ->where('fees_assign_details.branch_id', $activeBranchId)
                    ->whereNull('fees_assign_details.deleted_at');
                if(!empty($search['session_id'])){
                    $headSummaryQuery->where('fees_assign_details.session_id', $search['session_id']);
                }
                $headSummary = $headSummaryQuery->groupBy('fees_assign_details.fees_group_id', 'fees_group.name')
                    ->select(
                        'fees_assign_details.fees_group_id',
                        'fees_group.name as head_name',
                        DB::raw('COUNT(fees_assign_details.id) as assigned_count'),
                        DB::raw('SUM(fees_assign_details.fees_group_amount) as total_assigned_amt'),
                        DB::raw('SUM(fees_assign_details.discount) as total_discount_amt'),
                        DB::raw('SUM(COALESCE(fees_assign_details.promotion_carry_adjustment, 0)) as total_carry_adjustment_amt')
                    )
                    ->orderBy('total_assigned_amt', 'DESC')
                    ->get();

                // Calculate collected amount per fee group
                foreach($headSummary as $head){
                    $headPaid = DB::table('fees_detail')
                        ->whereIn('admission_id', $allStudentIds)
                        ->where('fees_group_id', $head->fees_group_id)
                        ->where('branch_id', $activeBranchId)
                        ->whereIn('status', [0, 1])
                        ->whereNull('deleted_at');
                    if(!empty($search['session_id'])) $headPaid->where('session_id', $search['session_id']);
                    if(!empty($search['starting'])) $headPaid->whereDate('date', '>=', $search['starting']);
                    if(!empty($search['ending'])) $headPaid->whereDate('date', '<=', $search['ending']);
                    if(!empty($search['payment_mode'])) $headPaid->where('payment_mode_id', $search['payment_mode']);
                    if(!empty($search['user_id'])) $headPaid->where('user_id', $search['user_id']);
                    $headPaid = $headPaid->sum('paid_amount');
                    $head->total_paid_amt = (float)$headPaid;
                    $head->net_amt = max(0, (float)$head->total_assigned_amt - (float)$head->total_discount_amt - (float)$head->total_carry_adjustment_amt);
                    $head->due_amt = max(0, $head->net_amt - $head->total_paid_amt);
                }

                $summary = [
                    'total_students' => count($studentList),
                    'total_assigned_students' => $assignedCount,
                    'total_gross' => $grandGross,
                    'total_discount' => $grandDiscount,
                    'total_carry_adjustment' => $grandCarryAdjustment,
                    'total_net' => $grandNet,
                    'total_paid' => $grandPaid,
                    'total_pending' => $grandPending,
                    'total_fine' => $grandFine,
                    'total_invoices_count' => count($invoices),
                    'collection_percentage' => $grandNet > 0 ? round(($grandPaid / $grandNet) * 100, 1) : 0
                ];

                $paymentModes = \App\Models\Master\PaymentMode::all();

                return view('fees.reports.CA', [
                    'data' => $invoices, // backward compatibility
                    'invoices' => $invoices,
                    'studentList' => $studentList,
                    'headSummary' => $headSummary,
                    'summary' => $summary,
                    'paymentModes' => $paymentModes,
                    'search' => $search
                ]);
            }
        

            public function fees_cheque(Request $request){
               
                $search['name'] = $request->name;
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course_id'] = $request->course_id ?? '';
                $search['batch'] = $request->batch ?? '';
                $search['starting'] = $request->starting;
                $search['ending'] = $request->ending;
                $search['admission_no'] = $request->admission_no;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : '';
                
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
            
                            $remainingAmount = (($head->fees_group_amount ?? 0) - ($head->discount ?? 0) - ($head->promotion_carry_adjustment ?? 0)) - ($feesDetails ?? 0);
            
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
