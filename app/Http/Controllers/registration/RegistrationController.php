<?php

namespace App\Http\Controllers\registration;

use Illuminate\Support\Facades\Validator;
use App\Models\registration\Registration;
use App\Models\Sessions;
use App\Models\Master\Branch;
use App\Models\BillCounter;
use App\Models\BloodGroup;
use App\Models\Admission;
use App\Models\FeesGroup;
use App\Models\FeesMaster;
use App\Models\WhatsappSetting;
use App\Models\FeesDetail;
use App\Models\Master\PaymentMode;
use App\Services\NotificationService;
use App\Models\RegFeesAssignDetails;
use App\Models\FeesCollect;
use App\Models\fees\FeesAssign;
use App\Models\fees\FeesAssignDetail;
use App\Models\Setting;
use App\Models\State;
use App\Models\RegistrationFeesMaster;
use App\Models\Gender;
use App\Models\Master\MessageTemplate;
use App\Models\Master\MessageType;
use App\Models\ClassType;
use App\Models\City;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\fees\FeesDetailsInvoices;
use Session;
use Hash;
use PDF;
use Helper;
use Str;
use Mail;
use File;
use DB;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;


class RegistrationController extends Controller
{
    
    public function __construct(private NotificationService $notif) {}

          public function registration(Request $request)
            {
                
                 $BillCounter = BillCounter::where('type', 'RegistrationFees')->get()->first();
                    if (!empty($BillCounter)) {
                        $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                        $BillCounterNo = $counter + 1;
                    }
            // dd(Session::get('role_id'));
                if ($request->isMethod('post')) {
                    try {
                        $year = now()->year;
                        $random = strtoupper(Str::random(6));
                        $rollno = substr($request->neet_roll_no, -4);
                        $uniqueId = "{$year}-{$rollno}-{$random}";
                        
                         $getSetting = Setting::where('branch_id', $request->branch_id)->first();
                         
                        $registration = Registration::where('neet_roll_no',$request->neet_roll_no)->where('batch',$request->batch)->first();
                            if(!empty($registration)){
                                        $registration = $registration;
                                    }else{
                                $registration = new Registration();
                                    
                                    }
                                $registration->branch_id = $request->branch_id;
                        $registration->session_id = $getSetting->current_active_session_id;
                        $registration->neet_roll_no = $request->neet_roll_no;
                        $registration->student_type = $request->student_type;
                        $registration->course_id = $request->course_id;
                        $registration->batch = $request->batch;
                        $registration->first_name = $request->first_name;
                        $registration->email = $request->email;
                        $registration->mobile = $request->mobile;
                        $registration->father_name = $request->father_name;
                        $registration->mother_name = $request->mother_name;
                        $registration->registration_date = $request->registration_date;
                        $registration->dob = $request->dob;
                        $registration->gender_id = $request->gender_id;
                        $registration->blood_group = $request->blood_group;
                        $registration->address = $request->address;
                        $registration->country_id = $request->country_id;
                        $registration->village_city = $request->village_city;
                        $registration->city_id = $request->city_id;
                        $registration->state_id = $request->state_id;
                        $registration->pincode = $request->pincode;
                        $registration->religion = $request->religion;
                        $registration->category = $request->category;
                        $registration->unique_registration_id = $uniqueId;
                        
                        $registration->save();
        
                        return response()->json([
                            'status' => true,
                            'message' => 'Information Saved',
                            'registration_id' => $registration->id,
                        ]);
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Server error: ' . $e->getMessage()
                        ], 500);
                    }
                }
        
                return view('registration.registration', ['BillCounter' => $BillCounterNo]);
            }
 
            public function saveFees(Request $request)
                { 
                   $BillCounter = BillCounter::where('type', 'RegistrationFees')->first();
                   if (!empty($BillCounter)) {
                        $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                        $BillCounterNo = $counter + 1;
                    }
                    try {
                        if (empty($request->registration_id)) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Missing registration ID.'
                            ], 422);
                        }
                
                        // Get registration object to access ->id
                        $registration = Registration::find($request->registration_id);
                   
                        if (!$registration) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Invalid registration ID.'
                            ], 404);
                        }

                        $payment_date = date('Y-m-d');
                        $registration_invoice_no = $BillCounterNo;

                        if (!empty($request->registration_fees_master_ids) && is_array($request->registration_fees_master_ids)) {
                            $total_amount = 0;
                            for ($i = 0; $i < count($request->registration_fees_master_ids); $i++) {
                                $fees_master_id = $request->registration_fees_master_ids[$i] ?? null;
                                $amount = $request->amount[$i] ?? 0;
                
                                if ($fees_master_id) {
                                    $registration_fees_master_ids = RegistrationFeesMaster::where('id', $fees_master_id)
                                        ->where('branch_id', $registration->branch_id)
                                        ->first();
                   // dd($registration);
                                    if (!$registration_fees_master_ids) {
                                        continue;
                                    }
                                
                                    $RegfeesGroupDetail = new RegFeesAssignDetails();
                                   
                                    $BillCounter->counter = $BillCounterNo;
                                    $BillCounter->save();
                                    $RegfeesGroupDetail->user_id =  1;
                                    $RegfeesGroupDetail->session_id = 6;
                                    $RegfeesGroupDetail->branch_id = $registration->branch_id;
                                    $RegfeesGroupDetail->fees_group_id = $registration_fees_master_ids->fees_group_id;
                                    $RegfeesGroupDetail->fees_master_id = $fees_master_id;
                                    $RegfeesGroupDetail->fees_group_amount = $amount;
                                    $RegfeesGroupDetail->registration_id = $registration->id;
                                    $RegfeesGroupDetail->payment_mode_id = $request->payment_mode_id;
                                    $RegfeesGroupDetail->transaction_id = $request->transaction_id;
                                    $RegfeesGroupDetail->registration_invoice_no = $registration_invoice_no;
                                    $RegfeesGroupDetail->payment_date = $payment_date;
                                   
                                    $RegfeesGroupDetail->save();
                                   $total_amount +=$amount;
                                }
                            }

                            //if ($request->payment_mode_id == 6) {

                                $branch = Branch::find($registration->branch_id);
                                $setting = Setting::where('branch_id', $registration->branch_id)->first();
                                $payment_mode = PaymentMode::find($request->payment_mode_id);

                                $finalCollectedAmt = $total_amount;
                                $receiptDate = date('d-m-Y', strtotime($payment_date));

                                if (
                                    $registration &&
                                    $branch &&
                                    $branch->whatsapp_srvc != 0 &&
                                    !empty($registration->mobile)
                                ) {

                                    $studentName = trim($registration->first_name . ' ' . $registration->last_name);

                                    $template = MessageType::where('template_name', 'collect_fee')
                                        ->where('status', 1)
                                        ->first();

                                    if ($template) {

                                        $receipt = url('registrationList/print') . '/'
                                            . $registration->id . '/'
                                            . $payment_date . '/'
                                            . $registration_invoice_no;

                                        $variables = [
                                            '{{1}}' => $studentName,
                                            '{{2}}' => number_format($finalCollectedAmt, 2),
                                            '{{3}}' => $setting->name ?? '',
                                            '{{4}}' => 'Mode: ' . ($payment_mode->name ?? '') . ', Date: ' . $receiptDate,
                                            '{{5}}' => $setting->name ?? '',
                                            '{{6}}' => $receipt,
                                        ];

                                        $content = str_replace(
                                            array_keys($variables),
                                            array_values($variables),
                                            $template->template_content
                                        );

                                        $params = [
                                            1 => $studentName,
                                            2 => number_format($finalCollectedAmt, 2),
                                            3 => $setting->name ?? '',
                                            4 => 'Mode: ' . ($payment_mode->name ?? '') . ', Date: ' . $receiptDate,
                                            5 => $setting->name ?? '',
                                            6 => $receipt,
                                        ];

                                        $this->notif->enqueue([
                                            'user_id'        => Session::get('id'),
                                            'branch_id'      => $registration->branch_id,
                                            'session_id'     => Session::get('session_id'),
                                            'recipient'      => $registration->mobile,
                                            'message'        => $content,
                                            'media_link'     => $receipt,
                                            'event_type'     => 'registration_fee_successful',
                                            'template_name'  => 'collect_fee',
                                            'channel'        => 'whatsapp',
                                            'params'         => $params,
                                        ]);
                                    }
                                }
                            //}
                  
                            return response()->json([
                                'status' => true,
                                'message' => 'Fees saved successfully.',
                                'registration_id' => $registration->id,
                                'payment_date' => $payment_date,
                                'registration_invoice_no' => $registration_invoice_no
                            ]);
                        }
                
                        return response()->json([
                            'status' => false,
                            'message' => 'No fee data received.'
                        ], 422);
                
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Error saving fees: ' . $e->getMessage()
                        ], 500);
                    }
                }

            
            
            public function registrationDashboard(){
                return view('registration.registrationDashboard');
            }

            public function registrationList(Request $request){
                  
                    $search['name'] = $request->name;
                    $search['status'] = $request->status;
                    // $data = Registration::where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'));
                     $data = Registration::select('registrations.*','courses.name as courses_name')
                            ->leftJoin('courses', 'courses.id', 'registrations.course_id')
                            ->where('registrations.session_id', Session::get('session_id'))
                            ->where('registrations.branch_id', Session::get('branch_id'));

                    if ($request->isMethod('post')) {
                        if ($request->name != '') {
                            $value = $request->name;
                            $data = $data->where(function ($query) use ($value) {
                                $query->where('first_name', 'LIKE', '%' . $value . '%');
                                $query->orWhere('father_name', 'LIKE', '%' . $value . '%');
                                $query->orWhere('mother_name', 'LIKE', '%' . $value . '%');
                                $query->orWhere('mobile', 'LIKE', '%' . $value . '%');
                                $query->orWhere('email', 'LIKE', '%' . $value . '%');
                                $query->orWhere('address', 'LIKE', '%' . $value . '%');
                                $query->orWhere('dob', 'LIKE', '%' . $value . '%');
                                $query->orWhere('village_city', 'LIKE', '%' . $value . '%');
                                $query->orWhere('address', 'LIKE', '%' . $value . '%');
                                $query->orWhere('pincode', 'LIKE', '%' . $value . '%');
                                $query->orWhere('religion', 'LIKE', '%' . $value . '%');
                                $query->orWhere('category', 'LIKE', '%' . $value . '%');
                                $query->orWhere('registration_date', 'LIKE', '%' . $value . '%');
                                $query->orWhere('blood_group', 'LIKE', '%' . $value . '%');
                                $query->orWhere('unique_registration_id', 'LIKE', '%' . $value . '%');
                                $query->orWhere('neet_roll_no', 'LIKE', '%' . $value . '%');
                                $query->orWhere('batch', 'LIKE', '%' . $value . '%');
                                $query->orWhere('student_type', 'LIKE', '%' . $value . '%');
                            });
                        }
                       
                    
                   if ($request->status != '') {
                            $data = $data->where("registrations.status", $request->status);
                        }
                    }else{
                       $data = $data->where('registrations.status',0); 
                    }
                $alladmission = $data->orderBy('registrations.registration_date','DESC')->get();
              
                return view('registration.registrationList', ['data' => $alladmission, 'search' => $search]);
            }
        

        public function registrationListGroup(Request $request){

            $serach['starting'] = $request->starting;
            $serach['ending'] = $request->ending;

            $groups = RegFeesAssignDetails::select(
                    'reg_fees_assign_details.registration_id',
                    'reg_fees_assign_details.registration_invoice_no',
                    'reg_fees_assign_details.payment_date',
                    'reg_fees_assign_details.payment_mode_id',
                    DB::raw('SUM(reg_fees_assign_details.fees_group_amount) as total_amount'),
                    DB::raw('GROUP_CONCAT(fees_group.name SEPARATOR ", ") as fees_group_names'),
                    'registrations.first_name',
                    'registrations.father_name',
                    'registrations.mother_name',
                    'registrations.neet_roll_no',
                    'registrations.student_type',
                    'registrations.batch',
                    'registrations.blood_group',
                    'registrations.mobile',
                    'registrations.category',
                    'registrations.email',
                    'registrations.unique_registration_id',
                    'registrations.registration_date'
                )
                ->leftJoin('fees_group', 'reg_fees_assign_details.fees_group_id', '=', 'fees_group.id')
                ->leftJoin('registrations', 'registrations.id', '=', 'reg_fees_assign_details.registration_id')
                ->where('registrations.session_id', Session::get('session_id'))
                ->where('registrations.branch_id', Session::get('branch_id'));

            if ($request->isMethod('post')) {
                if (!empty($request->starting)) {
                    $groups->whereBetween('reg_fees_assign_details.payment_date', [$request->starting, $request->ending]);
                }
            } else {
                $groups->whereDate('reg_fees_assign_details.payment_date', date('Y-m-d'));
            }

            $groups = $groups
                ->groupBy(
                    'reg_fees_assign_details.registration_id',
                    'reg_fees_assign_details.registration_invoice_no',
                    'reg_fees_assign_details.payment_date',
                    'reg_fees_assign_details.payment_mode_id',
                    'registrations.first_name',
                    'registrations.father_name',
                    'registrations.mother_name',
                    'registrations.neet_roll_no',
                    'registrations.student_type',
                    'registrations.batch',
                    'registrations.blood_group',
                    'registrations.mobile',
                    'registrations.category',
                    'registrations.email',
                    'registrations.unique_registration_id',
                    'registrations.registration_date'
                )
                ->orderBy('reg_fees_assign_details.id', 'DESC')
                ->get();

            return view('registration.registrationListGroup', ['data' => $groups, 'serach' => $serach]);
        }
            
      

        public function listGroupprint($id, $payment_date = null, $registration_invoice_no = null)
        {
            $query = RegFeesAssignDetails::select(
                    'reg_fees_assign_details.*',
                    'fees_group.name as fees_group_name',
                    'payment_modes.name as payment_mode',
                    'registrations.first_name',
                    'registrations.father_name',
                    'registrations.mother_name',
                    'registrations.neet_roll_no',
                    'registrations.mobile',
                    'registrations.email',
                    'registrations.batch',
                    'registrations.student_type',
                    'registrations.category',
                    'courses.name as course_name',
                    'branch.branch_name as branch_name',
                    'gender.name as gender_name'
                )
                ->leftjoin('fees_group', 'fees_group.id', '=', 'reg_fees_assign_details.fees_group_id')
                ->leftjoin('payment_modes', 'payment_modes.id', '=', 'reg_fees_assign_details.payment_mode_id')
                ->leftjoin('registrations', 'registrations.id', '=', 'reg_fees_assign_details.registration_id')
                ->leftjoin('courses', 'courses.id', '=', 'registrations.course_id')
                ->leftjoin('branch', 'branch.id', '=', 'registrations.branch_id')
                ->leftjoin('gender','gender.id', '=', 'registrations.gender_id')
                ->where('reg_fees_assign_details.registration_id', $id);

            if (!empty($payment_date)) {
                $query->whereDate('reg_fees_assign_details.payment_date', date('Y-m-d', strtotime($payment_date)));
            } else {
                $query->whereDate('reg_fees_assign_details.payment_date', date('Y-m-d'));
            }

            $data = $query->where('reg_fees_assign_details.registration_invoice_no', $registration_invoice_no)->get();

            if ($data->isEmpty()) {
                return redirect()->back()->with('error', 'No record found for this date!');
            }

            $reg = Registration::find($id);
            $getSetting = Setting::where('branch_id', $reg->branch_id)->first();

            return view('registration.registrationListprint', [
                'reg' => $reg,
                'data' => $data,
                'getSetting' => $getSetting
            ]);
        }

            
        public function getByNeet(Request $request)
        {
            $neet = $request->neet_roll_no;

            $student = Registration::where('neet_roll_no', $neet)->first();

            if ($student) {
                return response()->json([
                    'success' => true,
                    'data' => $student
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Not found'
                ]);
            }
        }
       public function duplicateRegistration(Request $request)
            {
                $neet = $request->neet_roll_no;
            
                $student = Registration::where('neet_roll_no', $neet)->where('status', 0)->first();
            
                if ($student) {
                    return response()->json([
                        'success' => false,
                        'message' => 'NEET Roll No. already exists!'
                    ]);
                }
            
                return response()->json([
                    'success' => true,
                    'message' => 'Available'
                ]);
            }
    
public function getFeesGroupByType(Request $request)
{
    
    $getSetting = Setting::where('branch_id', $request->branch_id)->first();

    $groups = RegistrationFeesMaster::select(
        'registration_fees_masters.*',
        'fees_group.name as fees_group_name',
        'registration_fees_masters.fees_partial'
    )
    ->leftJoin('fees_group', 'registration_fees_masters.fees_group_id', '=', 'fees_group.id')
    ->where('registration_fees_masters.session_id', $getSetting->current_active_session_id)
    ->where('registration_fees_masters.branch_id', $request->branch_id)
    ->whereNotNull('registration_fees_masters.management')
    ->where('fees_group.group_type','registration')
        ->where('course_id', $request->course_id)

    ->whereNull('fees_group.deleted_at')->get();

    
    
    $student_type = $request->student_type; // या Session से ले सकते हैं
                $html = '';
                $idsHtmlAmount = '';
                
                foreach ($groups as $group) {
    $amount = 0;

    if ($student_type === 'NRI') {
        $amount = $group['nri'];
    } elseif ($student_type === 'Management') {
        $amount = $group['management'];
    } else {
        $amount = $group['govt'];
    }


    $alreadyPaidAmount = 0;

    if (!empty($request->registration_id)) {
        $alreadyPaidAmount = RegFeesAssignDetails::where('registration_id', $request->registration_id)
            ->where('fees_group_id', $group->fees_group_id)
            ->where('branch_id', $request->branch_id)
            ->sum('fees_group_amount');
    }

    $amount = max(0, $amount - $alreadyPaidAmount);

    $partialCheckbox = '';

    if (
        $group->fees_partial == 1 &&
        $alreadyPaidAmount == 0 &&
        $amount > 0
    ) {
        $partialCheckbox = '
            <label for="partial_'.$group->id.'" class="pointer">
                <input type="checkbox"
                    class="partial_checkbox pointer"
                    data-full="'.$amount.'"
                    data-id="'.$group->id.'"
                    id="partial_'.$group->id.'">
                Pay Partial (50%)
            </label>';
    }

                  $html .= '<li class="list-group-item d-flex justify-content-between align-items-center flex-column align-items-start">
                        <div class="w-100 d-flex justify-content-between">
                            <div> <label for="head_'.$group['id'].'" class="pointer">';
                    
                    if ($amount > 0) {
                        $html .= '<input type="checkbox" id="head_'.$group['id'].'" class="fees_group_checkbox pointer" value="'.$group['id'].'" data-amount="'.$amount.'"> ';
                    }
                    
                    $html .= $group['fees_group_name'] . '</label></div>' . $partialCheckbox;
                    
                    // ✅ अगर amount = 0 है
                    if ($amount == 0) {
                        $html .= '<span class="text-danger ml-2">Already paid this head</span>';
                    }
                    
                    // ✅ अगर amount > 0 है
                    if ($amount > 0) {
                        $html .= '
                            <span class="badge badge-primary badge-pill">₹
                                <span class="amount_display" data-id="'.$group['id'].'">'.$amount.'</span>
                            </span>
                        ';
                    }
                    
                    $html .= '
                        </div>
                    </li>';




    $idsHtmlAmount .= '<input type="hidden" name="amount[]" value="'.$amount.'">';
}

               // dd($html);
                // Blade में भेजने के लिए आप return कर सकते हैं
                return response()->json([
                    'status' => true,
                    'html' => $html,
                    'idsHtmlAmount' => $idsHtmlAmount
                ]);


    if ($groups->count()) {
        return response()->json([
            'status' => true,
                    'html' => $html,
                    'idsHtmlAmount' => $idsHtmlAmount
        ]);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'No fees groups found for this student type.'
        ]);
    }
}



public function counselling(Request $request)
{

    if ($request->isMethod('post')) {
        //dd($request);
        
            $registration = Registration::find($request->registration_id);
            if (!$registration) {
                return response()->json([
                    'status' => false,
                    'message' => 'Registration not found.'
                ], 404);
            }
            
                   $fees_group_ids = RegFeesAssignDetails::where('registration_id', $registration->id)->pluck('fees_group_id'); // returns a collection of scalar values

                $fees_master = FeesMaster::where('class_type_id', $request->class_type_id)->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))
                    ->whereIn('fees_group_id', $fees_group_ids)
                    ->get();

if ($fees_master && $fees_master->count() > 0) {

            // Update counselling details
            $registration->counselling_date = date('Y-m-d');
            $registration->status =1;
            $registration->save();
              $admission  = new Admission();
             $BillCounter = BillCounter::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('type', 'StudentAdmission')->get()->first();

                 $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                        $BillCounter->counter = $counter + 1;
                        $BillCounter->save();
                        $admission->user_id = Session::get('id');
                        $admission->session_id = Session::get('session_id');
                        $admission->admissionNo = $BillCounter->counter;
                        $admission->unique_system_id = strtoupper(Str::random(10));
                        $admission->school = '1';
                        $admission->library = '0';
                        $admission->hostel = '0';
                        $admission->roll_no = $registration->neet_roll_no;
                        $admission->admission_date =  $registration->registration_date;
                        $admission->admission_type_id = 1;
                        $admission->branch_id = $registration->branch_id;
                     //   $admission->neet_roll_no = $registration->neet_roll_no;
                        $admission->student_type = $registration->student_type;
                        $admission->batch = $registration->batch;
                        $admission->first_name = $registration->first_name;
                        $admission->email = $registration->email;
                        $admission->mobile = $registration->mobile;
                        $admission->father_name = $registration->father_name;
                        $admission->mother_name = $registration->mother_name;
                        $admission->dob = $registration->dob;
                        $admission->gender_id = $registration->gender_id;
                        $admission->blood_group = $registration->blood_group;
                        $admission->address = $registration->address;
                        $admission->country_id = $registration->country_id;
                        $admission->village_city = $registration->village_city;
                        $admission->city_id = $registration->city_id;
                        $admission->state_id = $registration->state_id;
                        $admission->pincode = $registration->pincode;
                        $admission->religion = $registration->religion;
                        $admission->category = $registration->category;
                        //$admission->unique_registration_id = $registration->unique_registration_id;
                        
                        $admission->status = 1;
                        $admission->admission_date = date('Y-m-d');
                        $admission->class_type_id = $request->class_type_id;
            
            
                            
                        $admission->userName = strtoupper(str_replace(' ', '', $registration->first_name)) . $BillCounter->counter;
                        $admission->password = Hash::make($BillCounter->counter);
                        $admission->confirm_password = $BillCounter->counter;
                        $admission->save();

            // Fees assignment
            $data = RegFeesAssignDetails::where('registration_id', $registration->id)->get();

            $feesGroup = new FeesAssign();
            $feesGroup->user_id = Session::get('id');
            $feesGroup->session_id = Session::get('session_id');
            $feesGroup->branch_id = Session::get('branch_id');
            $feesGroup->admission_id = $admission->id;
            $feesGroup->save();

            $FeesAssignId = $feesGroup->id;
            $fees_group_amount = 0;
            $fees_group_discount = 0;

            foreach($data as $value) {
              
                   $fees_master = FeesMaster::where('class_type_id',$request->class_type_id)->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))
                    ->where('fees_group_id', $value->fees_group_id)
                    ->first();
                   
                    if (!$fees_master) {
                    // default amount या error handle
                        $amountAssign = 0; // या return / continue
                    } else {
                    
                        if ($admission->student_type == "NRI") {
                            $amountAssign = $fees_master->nri;
                        } elseif ($admission->student_type == "Management") {
                            $amountAssign = $fees_master->management;
                        } elseif ($admission->student_type == "Govt") {
                            $amountAssign = $fees_master->govt;
                        } else {
                            $amountAssign = $fees_master->amount;
                            $genderId = (int)($admission->gender_id ?? 0);
                            if ($genderId === 1 && !empty($fees_master->boys_amount)) {
                                $amountAssign = (float)$fees_master->boys_amount;
                            } elseif ($genderId === 2 && !empty($fees_master->girls_amount)) {
                                $amountAssign = (float)$fees_master->girls_amount;
                            }
                        }
                    }

                    $amount =$amountAssign;
                    $Payamount = $value->fees_group_amount;
                

                $feesGroupDetail = new FeesAssignDetail();
                $feesGroupDetail->user_id = Session::get('id');
                $feesGroupDetail->session_id = Session::get('session_id');
                $feesGroupDetail->branch_id = Session::get('branch_id');
                $feesGroupDetail->fees_group_id = $value->fees_group_id;
                $feesGroupDetail->fees_master_id = $fees_master->id ?? '';
                $feesGroupDetail->fees_group_amount = $amount ?? 0;
                $fees_group_amount += $amount ?? 0;
                $feesGroupDetail->fees_assign_id = $FeesAssignId;
                $feesGroupDetail->admission_id = $admission->id;
                $feesGroupDetail->save();
                $this->studentPaySubmit($admission->id,$value->fees_master_id,$value->fees_group_id,$Payamount,$value->payment_mode_id,$value->transaction_id);
            }

            $feesGroup->total_amount = $fees_group_amount;
            $feesGroup->total_discount = $fees_group_discount;
            $feesGroup->net_amount = $fees_group_amount - $fees_group_discount;
            $feesGroup->save();
}else{
                    return redirect::to('registrationList')->with('error', 'Fees Group Master Not Add  !');

}
                    return redirect::to('registrationList')->with('message', 'Counselling details updated successfully.');

            
       
    }

    return view('registration.counselling');
}



            public function studentPaySubmit($admission_id,$fees_master_id,$fees_group_id,$amount,$payment_mode_id,$transaction_id){

                $BillCounter = BillCounter::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('type', 'FeesSlip')->get()->first();
                $FeesAssign = FeesAssign::where('admission_id',$admission_id)->get()->first();
                $fees_details_id =[];
                    if (!empty($admission_id)) {
                        if (!empty($fees_master_id)) {
                            $counter = !empty($BillCounter->counter) ? $BillCounter->counter : 0;
                            $BillCounter->counter = $counter + 1;
                            $BillCounter->save();
                            $slip_no = $BillCounter->counter;
                               

                                        $pay = new FeesCollect;
                                        $pay->user_id = Session::get('id');
                                        $pay->session_id = Session::get('session_id');
                                        $pay->branch_id = Session::get('branch_id');
                                        $pay->admission_id = $admission_id;
                                        $pay->fees_assign_id = $FeesAssign->id;
                                        $pay->amount = $amount;
                                        $pay->save();
                                        $collect_id = $pay->id;
                                        
                                        $payDetail = new FeesDetail; //model name
                                        $payDetail->user_id = Session::get('id');
                                        $payDetail->session_id = Session::get('session_id');
                                        $payDetail->branch_id = Session::get('branch_id');
                                        $payDetail->fees_collect_id = $collect_id;
                                        $payDetail->fees_group_id = $fees_group_id;
                                        $payDetail->receipt_no  = $slip_no;
                                        $payDetail->admission_id = $admission_id;
                                        $payDetail->paid_amount = $amount;
                                        $payDetail->total_amount = $amount;
                                        $payDetail->status = 0;
                                        $payDetail->date = date('Y-m-d');;
                                        $payDetail->payment_mode_id = $payment_mode_id;
                                        $payDetail->save();
                                        $fees_details_id[]= $payDetail->id;
                                    
                                    
                                }
                            
                        if(!empty($fees_details_id)){
                           
                            $invoice = new FeesDetailsInvoices();
                            $invoice->user_id = Session::get('id');
                            $invoice->session_id = Session::get('session_id');
                            $invoice->branch_id = Session::get('branch_id');
                            $invoice->fees_counter_id = 1;
                            $invoice->admission_id = $admission_id;
                            $invoice->fees_details_id = implode(',',$fees_details_id );
                            $invoice->payment_date = date('Y-m-d');; 
                            $invoice->payment_mode = $payment_mode_id;
                            $invoice->transaction_id = $transaction_id;
                            $invoice->invoice_no = $slip_no;
                            $invoice->status = 0;
                            $invoice->amount = $amount;
                            $invoice->save();
                        
                            
 
                       

                        }
                    }

                    
                    
                    
                    
                    
                    
                    
                
               
                
            }
            
          
            
            public function print($id)
            {
                $data = RegFeesAssignDetails::select(
                        'reg_fees_assign_details.*',
                        'fees_group.name as fees_group_name',
                        'payment_modes.name as payment_mode',
                        'registrations.first_name',
                        'registrations.father_name',
                        'registrations.mother_name',
                        'registrations.neet_roll_no',
                        'registrations.mobile',
                        'registrations.email',
                        'registrations.batch',
                        'registrations.student_type',
                        'registrations.category',
                        'courses.name as course_name',
                        'branch.branch_name as branch_name',
                        'gender.name as gender_name'
                    )
                    ->leftjoin('fees_group', 'fees_group.id', '=', 'reg_fees_assign_details.fees_group_id')
                    ->leftjoin('payment_modes', 'payment_modes.id', '=', 'reg_fees_assign_details.payment_mode_id')
                    ->leftjoin('registrations', 'registrations.id', '=', 'reg_fees_assign_details.registration_id')
                    ->leftjoin('courses', 'courses.id', '=', 'registrations.course_id')
                    ->leftjoin('branch', 'branch.id', '=', 'registrations.branch_id')
                    ->leftjoin('gender','gender.id', '=', 'registrations.gender_id')
                    ->where('reg_fees_assign_details.registration_id', $id)
                    ->get();

                $reg = Registration::find($id);
                $getSetting = Setting::where('branch_id', $reg->branch_id)->first();

                return view('registration.print', ['reg' => $reg, 'data' => $data, 'getSetting' => $getSetting]);
            }


}     