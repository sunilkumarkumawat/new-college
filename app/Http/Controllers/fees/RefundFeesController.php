<?php

namespace App\Http\Controllers\fees;

use Illuminate\Validation\Validator;
use App\Models\Setting;
use App\Models\fees\FeesCounter;
use App\Models\fees\FeesAdvance;
use App\Models\fees\FeesAdvanceHistory;
use App\Models\fees\FeesAssignDetail;
use App\Models\FeesDetail;
use App\Models\Admission;
use App\Models\fees\FeesDetailsInvoices;
use App\Models\BillCounter;
use Session;
use App\Models\FeesCollect;
use Helper;
use Hash;
use Response;
use Str;
use DB;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RefundFeesController extends Controller

{ 
     
            public function RefundFees(Request $request)  {

                $search['name'] = $request->name;
                $search['class_type_id'] = $request->class_type_id ?? '';
                $search['course_id'] = $request->course_id ?? '';
                $search['starting'] = $request->starting;
                $search['ending'] = $request->ending;
                $search['status'] = $request->status;
                $search['batch'] = $request->batch;
                $search['admissionNo'] = $request->admissionNo;
                $search['paid_report'] = $request->paid_report;
                $search['session_id'] = $request->has('session_id') ? $request->session_id : '';

                $data='';
                $hasFilter = $request->isMethod('post') 
                    || !empty($search['course_id']) 
                    || !empty($search['batch']) 
                    || !empty($search['class_type_id']) 
                    || !empty($search['admissionNo']) 
                    || !empty($search['name']) 
                    || !empty($search['starting'])
                    || (!empty($search['session_id']) && $request->has('session_id'));

                if ($hasFilter) {
                    $data = Admission::select('admissions.*', 'class_types.name as className')
                    ->leftJoin('class_types', 'class_types.id', 'admissions.class_type_id')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('fees_assign_details')
                            ->whereColumn('fees_assign_details.admission_id', 'admissions.id')
                            ->where('fees_assign_details.fees_refund', 'yes');
                    })
                    ->where('admissions.school', 1)
                    ->where('admissions.branch_id', Session::get('branch_id'));

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
                        $data = $data->where("admissionNo", $request->admissionNo);
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where('admissions.class_type_id',$request->class_type_id);
                    }
                    if ($request->batch != '') {
                        $data = $data->where("batch", $request->batch);
                    }
                    if (!empty($request->starting)) {
                        $data = $data->whereBetween('fees_detail.date', [$request->starting, $request->ending]);
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

                return view('fees.RefundFees.view',['data' => $data,'search'=>$search]);
            } 


            public function feesRefundView(Request $request) {
                $getFees = FeesAssignDetail::select('fees_assign_details.*', 'fees_group.name as group_name', 'admissions.first_name as admission_stu_name')
                ->join('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                ->join('admissions', 'admissions.id', '=', 'fees_assign_details.admission_id')
                ->where('admission_id', $request->admission_id)
                ->where('fees_assign_details.fees_refund', 'yes')
                ->get();
            
            $html = '<h1 class="d-none admission-stu-name">' . ($getFees->first()->admission_stu_name ?? '') . '</h1>';
            
            $html .= '<table class="table">
                <thead>
                    <tr class="sky_tr">
                        <th></th>
                        <th>Fees Type</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th style="text-align: right;">Balance</th>
                    </tr>
                </thead>
                <tbody>';
            
            if (!$getFees->isEmpty()) {
                $i = 1;
                $grand_total = 0;
                $Paids = 0;
                $balances = 0;
            
                foreach ($getFees as $item) {
                    $feesDetails = FeesDetail::where('fees_type', 0)
                        ->where('status',3)
                        ->where('admission_id', $request->admission_id)
                        ->where('fees_group_id', $item->fees_group_id)
                        ->selectRaw('SUM(total_amount) as total_amount, SUM(discount) as total_discount, SUM(installment_fine) as installment_fine')
                        ->first();
            
                    $pad = $feesDetails->total_amount ?? 0;
                    $discounts = $feesDetails->total_discount ?? 0;
                    $fine_amt = $feesDetails->installment_fine ?? 0;
            
                    $balance = ($item->fees_group_amount - $item->discount - ($item->promotion_carry_adjustment ?? 0)) - $pad;
            
                    $html .= '<tr>
                        <td>' . $i++ . '</td>
                        <td>' . htmlspecialchars($item->group_name ?? '') . '</td>
                        <td>' . ($item->fees_group_amount > $pad ? '<span class="label1 label-danger-custom">Unpaid</span>' : '<span class="label1 label-success-custom">Total Paid</span>') . '</td>
                        <td>' . ($item->fees_group_amount - $item->discount - ($item->promotion_carry_adjustment ?? 0)) . '</td>
                        <td>' . $pad . '</td>
                        <td style="text-align: right;">' . $balance . '</td>
                    </tr>';
            
                    $grand_total += ($item->fees_group_amount - $item->discount - ($item->promotion_carry_adjustment ?? 0));
                    $Paids += $pad;
                    $balances += $balance;
                }
            
                $html .= '<tr>
                    <td colspan="6">
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
                    <td colspan="6"><b>!! NO DATA FOUND !!</b></td>
                </tr>';
            }
            $csrfToken = csrf_token(); // Laravel's CSRF token
            $html .= '</tbody></table>';
            $html .= '<form id="feesForm"  method="post">
          <input type="hidden" name="_token" value="' . $csrfToken . '">
            <table class="table">
                <thead>
                   <tr class="sky_tr">
                      <th>Select</th>
                      <th>Fees Type</th>
                      <th>Amount</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($getFees as $item) {
            $feesDetails = FeesDetail::where('fees_type', 0)
                ->where('status',3)
                ->where('admission_id', $request->admission_id)
                ->where('fees_group_id', $item->fees_group_id)
                ->selectRaw('SUM(total_amount) as total_amount, SUM(discount) as total_discount, SUM(installment_fine) as installment_fine')
                ->first();
        
            $pad = $feesDetails->total_amount ?? 0;
            $discounts = $feesDetails->total_discount ?? 0;
            $fine_amt = $feesDetails->installment_fine ?? 0;
            $balance = ($item->fees_group_amount - $item->discount - ($item->promotion_carry_adjustment ?? 0)) - $pad;
        
            $html .= '<tr>
                <td>
                    <input type="checkbox" class="amount-checkbox" name="fees_assign_details[]" value="' . $item->id . '" onclick="toggleAmountInput(this)">
                    <input type="hidden"  name="admission_id" value="'. $item->admission_id . '" >
                </td>
                <td>' . htmlspecialchars($item->group_name ?? '') . '</td>
                <td>
                    <input type="text" name="amounts[]" class="amount-input" value="' . $balance . '" disabled>
                </td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        
        // **Payment Mode and Date Fields Below the Table**
        $html .= '<div class="row">
                    <div class="col-md-3">
                        <label class="text-danger">Payment Mode*</label>
                        <select class="form-control" id="payment_mode_id" name="payment_mode_id" required disabled>
                            <option value="">Select Mode</option>';
                            if (!empty(Helper::getPaymentMode())) {
                                foreach (Helper::getPaymentMode() as $value) {
                                    $html .= '<option value="' . $value->id . '">' . ($value->name ?? '') . '</option>';
                                }
                            }
        $html .=    '</select>
                    </div>
                    <div class="col-md-3">
                        <label class="text-danger">Payment Date*</label>
                        <input type="date" class="form-control" name="payment_date" id="payment_date" value="' . date('Y-m-d') . '" disabled />
                    </div>
                </div>';
        
        // **Submit Button**
        $html .= '    <button type="submit" class="btn btn-primary mt-3" id="submitFees" disabled>Submit</button>';
        
        // **Close the Form**
        $html .= '</form>';
                
            

                return response()->json(['html' => $html]);
            }


            public function feesRefundSave(Request $request){ 
               //dd($request);
                $cheque_image = '';
                $receipt_no = '';
                $session_id = $request->session_id ?? Session::get('session_id');
                $BillCounter = BillCounter::where('session_id',$session_id)->where('branch_id',Session::get('branch_id'))->where('type', 'FeesSlip')->get()->first();
                if ($request->isMethod('post')) {
                   
                    $admission_id = $request->admission_id;
                    $data = Admission::where('id',$admission_id)->first();
                    if (!empty($admission_id)) {
                        if (!empty($request->fees_assign_details)) {
                            $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                            $BillCounter->counter = $counter + 1;
                            $BillCounter->save();
                            $total_amount = 0;
                            foreach($request->fees_assign_details as $key=> $head){
                              
                                $fees_assign_details = FeesAssignDetail::find($head);
                                    $payOld = FeesCollect::where('admission_id',$fees_assign_details->admission_id)->first();
                                    if(!empty($payOld)){
                                        $pay = $payOld;
                                        $amount = FeesCollect::where('admission_id', $fees_assign_details->admission_id)->increment('amount', $request->amount[$key] ?? 0);
                                        $payDetail = new FeesDetail; //model name
                                        $payDetail->user_id = Session::get('id');
                                        $payDetail->session_id = $session_id;
                                        $payDetail->branch_id = Session::get('branch_id');
                                        $payDetail->fees_collect_id = $payOld->id;
                                        $payDetail->fees_group_id = $fees_assign_details->fees_group_id;
                                        $payDetail->receipt_no  = $BillCounter->counter;
                                        $payDetail->admission_id = $fees_assign_details->admission_id; 
                                        $payDetail->paid_amount = $request->amounts[$key];
                                        $payDetail->installment_fine = 0;
                                        $payDetail->payment_mode_id = $request->payment_mode_id;
                                        $payDetail->discount = 0;
                                        $payDetail->total_amount = $request->amounts[$key];
                                        $payDetail->status = 3;
                                         $payDetail->date = $request->payment_date;
                                        $payDetail->save();  
                                        $fees_details_id[]= $payDetail->id;
                                        $total_amount +=$request->amounts[$key];
                                        $receipt_no = $payDetail->receipt_no;
                                    }
                                    
                                    
                                
                            }
                        }
                        if(!empty($fees_details_id)){
                         
                            $invoice = new FeesDetailsInvoices();
                            $invoice->user_id = Session::get('id');
                            $invoice->session_id = $session_id;
                            $invoice->branch_id = Session::get('branch_id');
                            $invoice->fees_counter_id = Session::get('fees_counter_id');
                            $invoice->admission_id = $admission_id;
                            $invoice->fees_details_id = implode(',',$fees_details_id );
                            $invoice->payment_date = $request->payment_date; 
                            $invoice->payment_mode = $request->payment_mode_id;
                            $invoice->invoice_no = $receipt_no;
                            $invoice->status = 3;
                            $invoice->amount = $total_amount;
                            $invoice->total_fine = 0;
                            $invoice->discount = 0;
                           
                            $invoice->save();
                          
 

                        }
                    }
                    
                  
                }
              
                return Response::json(array('status' => 'success')); 
            }
            
}
