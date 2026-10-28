<?php

namespace App\Http\Controllers\fees;

use Illuminate\Validation\Validator;
use App\Models\Student;
use App\Models\StudentFees;
use App\Models\ClassType;
use App\Models\Master\Section;
use App\Models\Admission;
use App\Models\BillCounter;
use App\Models\SmsSetting;
use App\Models\WhatsappSetting;
use App\Models\Account;
use App\Models\FeesStructure;
use App\Models\FeesType;
use App\Models\FeesGroup;
use App\Models\FeesMaster;
use App\Models\RegistrationFeesMaster;
//use App\Models\FeesAssign;
use App\Models\fees\FeesAssign;
use App\Models\fees\FeesAssignDetail;
use App\Models\FeesDiscount;
use App\Models\FeesCollect;
use App\Models\FeesReminder;
use App\Models\FeesDetail;
use App\Models\Setting;
use Session;
use Helper;
use Hash;
use Str;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeesMasterController extends Controller

{



            public function feesMaster(Request $request){
                $search['session_id'] = $request->has('session_id') ? $request->session_id : Session::get('session_id');
                $search['course_id'] = $request->course_id ?? '';
                $search['class_type_id'] = $request->class_type_id ?? '';

                if ($request->isMethod('post') && $request->has('fees_group_id')) {
                    $request->validate([
                        'class_type_id' => 'required',
                    ]);
                    $session_id = Session::get('session_id');
                    foreach ($request->fees_group_id as $key => $data) {
                        $oldData = FeesMaster::where('session_id', $session_id)->where('branch_id', Session::get('branch_id'))
                        ->where('class_type_id', $request->class_type_id)
                        ->where('fees_group_id',$data)->first();
                        if(empty($oldData)){
                            $amount = $request->amount[$key] ?? 0;
                            $fees_master = new FeesMaster; //model name
                            $fees_master->user_id = Session::get('id');
                            $fees_master->session_id = $session_id;
                            $fees_master->branch_id = Session::get('branch_id');
                            $fees_master->fees_group_id = $request->fees_group_id[$key];
                            $fees_master->amount = $amount;
                            $fees_master->boys_amount = !empty($request->boys_amount[$key]) ? floatval($request->boys_amount[$key]) : $amount;
                            $fees_master->girls_amount = !empty($request->girls_amount[$key]) ? floatval($request->girls_amount[$key]) : $amount;
                            $fees_master->nri = $amount;
                            $fees_master->management = $amount;
                            $fees_master->govt = $amount;
                            $fees_master->installment_due_date = $request->installment_due_date[$key] ?? null;
                            $fees_master->editable = $request->editable_value[$key] ?? 0;
                            $fees_master->class_type_id = $request->class_type_id;
                            $fees_master->save();
                        }
                        else{
                            return redirect::to('feesMasterAdd')->with('error', 'Already Assigned !');
                        }
                    }
                    return redirect::to('feesMasterAdd')->with('message', 'Fees Record Added Successfully !');
                }

                $query = FeesMaster::with('feesGroup')->with('ClassTypes')
                    ->where('branch_id', Session::get('branch_id'));

                if (!empty($search['session_id']) && $search['session_id'] !== 'all') {
                    $query->where('session_id', $search['session_id']);
                }

                if (!empty($search['course_id'])) {
                    $course_id = $search['course_id'];
                    $query->whereHas('ClassTypes', function($q) use ($course_id) {
                        $q->where('course_id', $course_id);
                    });
                }

                if (!empty($search['class_type_id'])) {
                    $query->where('class_type_id', $search['class_type_id']);
                }

                $fees_master_list = $query->groupBy('class_type_id')->get();
                $all_data = FeesMaster::with('feesGroup')->with('ClassTypes')->where('session_id', Session::get('session_id'));
      
                $group_type = FeesGroup::where('branch_id', Session::get('branch_id'))->where('session_id', Session::get('session_id'))->where('group_type', 'registration')->get();
                $feesGroupInstallmentsList = FeesGroup::where('fees_type','installment')->get();

                return view('fees.fees_master.feesMaster', [
                    'feesGroupInstallmentsList' => $feesGroupInstallmentsList,
                    'dataview' => $fees_master_list,
                    'allData' => $all_data,
                    'group_type' => $group_type,
                    'search' => $search
                ]);
            }

            public function feesMasterEdit(Request $request, $id){
                $datas = FeesMaster::where('class_type_id', $id)
                ->where('session_id', Session::get('session_id'))
                ->where('branch_id', Session::get('branch_id'))->get();
                if ($request->isMethod('post')) {
                    for ($count = 0; $count < count($request->fees_group_id); $count++) {
                        $old_data = FeesMaster::where('session_id', Session::get('session_id'))
                        ->where('branch_id', Session::get('branch_id'))
                        ->where('fees_group_id',$request->fees_group_id[$count])
                        ->where('class_type_id',$request->class_type_id)
                        ->first();
                        if($old_data != null){
                            $data = $old_data;
                        }else{
                            $data = new FeesMaster;
                        }
                        $amount = $request->amount[$count] ?? 0;
                        $data->fees_group_id = $request->fees_group_id[$count];
                        $data->amount = $amount;
                        $data->nri = $amount;
                        $data->management = $amount;
                        $data->govt = $amount;
                        $data->editable = $request->editable_value[$count] ?? 0;
                        $data->class_type_id = $request->class_type_id;
                        $data->save();
                    }
                    return redirect::to('feesMasterAdd')->with('message', 'Fees Record Updated Successfully !');
                }
                return view('fees.fees_master.feesMasterEdit', ['data' => $datas]);
            }

            public function feesMasterDelete(Request $request){
                $id = $request->delete_id;
                $feesMaster = FeesMaster::find($id);
                if ($feesMaster) {
                    $session_id = Session::get('session_id');
                    $branch_id = Session::get('branch_id');
                    $isAssigned = \App\Models\fees\FeesAssignDetail::where('fees_group_id', $feesMaster->fees_group_id)
                        ->where('class_type_id', $feesMaster->class_type_id)
                        ->where('session_id', $session_id)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->exists();
                    $isCollected = \App\Models\FeesDetail::where('fees_group_id', $feesMaster->fees_group_id)
                        ->where('session_id', $session_id)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->where('paid_amount', '>', 0)
                        ->exists();

                    if ($isAssigned || $isCollected) {
                        $msg = 'Cannot delete this Fee Head from Fees Master because it is already assigned to student(s) or has collected fees!';
                        if ($request->ajax()) {
                            return response()->json(['status' => false, 'message' => $msg], 422);
                        }
                        return redirect()->back()->with('error', $msg);
                    }

                    $feesMaster->delete();
                    $msg = 'Fees Master Record Deleted Successfully !';
                    if ($request->ajax()) {
                        return response()->json(['status' => true, 'message' => $msg, 'deleted_id' => $id]);
                    }
                    return redirect()->back()->with('message', $msg);
                }
                $msg = 'Fees Master Record Not Found !';
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => $msg], 404);
                }
                return redirect()->back()->with('error', $msg);
            }

            public function feesMasterData(Request $request){
                $data =  FeesMaster::find($request->fees_master_id);
                $paidAmount =  FeesDetail::where('class_type_id', $request->class_type_id)->where('fees_type_id', $data['fees_type_id'])->sum('total_amount');
                // dd($request);
                if ($paidAmount > 0) {
                    $net_amount =  $data['amount'] - $paidAmount;
                } else {
                    $net_amount = $data['amount'];
                }
                echo json_encode($net_amount);
            }

            public function mesterClassAmt(Request $request){
                // dd($request);
                $data =  FeesMaster::where('class_type_id',$request->class_type_id)->where('session_id', Session::get('session_id'))->get();
                $feesAssign = '';
                $admission_id = '';
                if(!empty($request->admission_id)){
                    $feesAssign = FeesAssign::where('admission_id',$request->admission_id)->first();
                    $admission_id = $request->admission_id;
                }
                if (count($data) > 0) {
                    return view('fees.fees_master.mesterClassAmt', ['data' => $data, 'feesAssign'=>$feesAssign, 'admission_id'=>$admission_id]);
                } else {
                    return null;
                }
            }

    public function specialFeesMaster(Request $request)
{
    if ($request->isMethod('post')) {
        try {
            foreach ($request->fees_group_ids as $key => $data) {
                // पहले से डाला हुआ data निकालो
                $oldData = RegistrationFeesMaster::where('session_id', Session::get('session_id'))
                    ->where('branch_id', Session::get('branch_id'))
                    ->where('fees_group_id', $data)
                    ->where('course_id', $request->course_id)
                    ->first();

                if (empty($oldData)) {
                    $fees_master = new RegistrationFeesMaster; 
                } else {
                    $fees_master = $oldData; // direct पुराना record use कर लो
                }

                $fees_master->user_id        = Session::get('id');
                $fees_master->session_id     = Session::get('session_id');
                $fees_master->branch_id      = Session::get('branch_id');
                $fees_master->fees_group_id  = $request->fees_group_ids[$key];
                $fees_master->nri            = $request->nri[$key];
                $fees_master->management     = $request->management[$key];
                $fees_master->govt           = $request->govt[$key];
                $fees_master->fees_group_type= $request->fees_group_type;
                $fees_master->course_id      = $request->course_id;

                // checkbox true/false check → अगर उस index पे value है तो 1 नहीं तो 0
                $fees_master->fees_partial   = isset($request->fees_partial[$key]) ? 1 : 0;

                $fees_master->class_type_id  = 1;
                $fees_master->save();
            }

            return response()->json([
                'status'  => true,
                'message' => 'Fees Master Updated Successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}

public function specialFeesMastercourseData(Request $request)
{
    $course_id = $request->course_id;

    // group_type wise लाओ
    $group_type = FeesGroup::where('branch_id', Session::get('branch_id'))
        ->where('session_id', Session::get('session_id'))
        ->where('group_type', 'registration')
        ->get();

    $html = '';

    if ($group_type->isNotEmpty()) {
        foreach ($group_type as $key => $type) {
            $registrationFeeMaster = RegistrationFeesMaster::where('fees_group_id', $type->id)
                ->where('course_id', $course_id)
                ->first();

            // row wise checkbox checked या नहीं
            $checked = ($registrationFeeMaster && $registrationFeeMaster->fees_partial == 1) ? 'checked' : '';

            $html .= '<tr>
                <td>'.$type->name.'
                    <input type="hidden" name="fees_group_ids[]" value="'.($type->id ?? '').'">
                </td>
                <td><input class="form-control" type="text" name="nri[]" value="'.($registrationFeeMaster->nri ?? '').'" placeholder="NRI" onkeypress="return isNumber(event)"></td>
                <td><input class="form-control" type="text" name="management[]" value="'.($registrationFeeMaster->management ?? '').'" placeholder="Management" onkeypress="return isNumber(event)"></td>
                <td><input class="form-control" type="text" name="govt[]" value="'.($registrationFeeMaster->govt ?? '').'" placeholder="Govt." onkeypress="return isNumber(event)"></td>
                <td class="text-center">
                    <input type="checkbox" name="fees_partial['.$key.']" value="1" '.$checked.'>
                </td>
            </tr>';
        }
    } else {
        $html .= '<tr><td colspan="6" class="text-center">!! NO DATA FOUND !!</td></tr>';
    }

    return response()->json(['html' => $html]);
}
    
    

}
