<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use App\Models\User;
use App\Models\Admission;
use App\Models\Classs;
use App\Models\ClassType;
use App\Models\Subject;
use App\Models\Sessions;
use App\Models\Master\Branch;
use App\Models\TcCertificate;
use App\Models\BillCounter;
use App\Models\SmsSetting;
use App\Models\BloodGroup;
use App\Models\DatatableFields;
use App\Models\FeesMaster;
use App\Models\FeesCollect;
use App\Models\WhatsappSetting;
use App\Models\FeesStructure;
use App\Models\FeesDetail;
use App\Models\Setting;
use App\Models\State;
use App\Models\Gender;
use App\Models\Master\MessageTemplate;
use App\Models\Master\MessageType;
use App\Models\City;
use App\Models\fees\FeesAssign;
use App\Models\fees\FeesAssignDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
use App\Imports\YourImportClassName;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;



class StudentPerformanceController extends Controller
{
    
     public function studentParticularPerformance(Request $request,$id){
      
      $data = Admission::find($id);
    
      return view('students.academic_performance.student_particular_performance',['data'=>$data]);
     }
     public function studentPerformance(Request $request){
         
           $search['admissionNo'] = $request->admissionNo;
        $search['class_type_id'] = $request->class_type_id;
        
        
   $data = Admission::select('admissions.*','class.name as class_name')
                            ->leftJoin('class_types as class','class.id','admissions.class_type_id')
                            ->orderBy('admissions.class_type_id', 'ASC');
      
        if(Session::get('role_id') > 1) {
            $data = $data->where('admissions.branch_id', Session::get('branch_id'));
        }
        if (Session::get('role_id') == 2) {
            $data = $data->where('admissions.class_type_id', $request->class_type_id);
        }
        if (!empty(Session::get('admin_branch_id'))) {
            $data = $data->where('admissions.branch_id', Session::get('admin_branch_id'));
        }
        if (!empty($request->course)) {
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
        if (!empty($request->batch)) {
            $data = $data->where("admissions.batch", $request->batch);
        }
        
            if ($request->name != '') {
                $value = $request->name;
                $data = $data->where(function ($query) use ($value) {
                    $query->where('first_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('last_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('father_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('mother_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('mobile', 'LIKE', '%' . $value . '%');
                    $query->orWhere('email', 'LIKE', '%' . $value . '%');
                    $query->orWhere('aadhaar', 'LIKE', '%' . $value . '%');
                    $query->orWhere('address', 'LIKE', '%' . $value . '%');
                    
                    $query->orWhere('ledger_no', 'LIKE', '%' . $value . '%');
                    $query->orWhere('srn', 'LIKE', '%' . $value . '%');
                    $query->orWhere('dob', 'LIKE', '%' . $value . '%');
                    $query->orWhere('village_city', 'LIKE', '%' . $value . '%');
                    $query->orWhere('address', 'LIKE', '%' . $value . '%');
                    $query->orWhere('pincode', 'LIKE', '%' . $value . '%');
                    $query->orWhere('caste_category', 'LIKE', '%' . $value . '%');
                    $query->orWhere('house', 'LIKE', '%' . $value . '%');
                    
                    $query->orWhere('height', 'LIKE', '%' . $value . '%');
                    $query->orWhere('weight', 'LIKE', '%' . $value . '%');
                    $query->orWhere('family_annual_income', 'LIKE', '%' . $value . '%');
                    $query->orWhere('family_id', 'LIKE', '%' . $value . '%');
                    $query->orWhere('religion', 'LIKE', '%' . $value . '%');
                    $query->orWhere('category', 'LIKE', '%' . $value . '%');
                    $query->orWhere('father_mobile', 'LIKE', '%' . $value . '%');
                    $query->orWhere('father_aadhaar', 'LIKE', '%' . $value . '%');
                    
                    $query->orWhere('mother_mob', 'LIKE', '%' . $value . '%');
                    $query->orWhere('mother_aadhaar', 'LIKE', '%' . $value . '%');
                    $query->orWhere('guardian_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('guardian_mobile', 'LIKE', '%' . $value . '%');
                    $query->orWhere('bus_number', 'LIKE', '%' . $value . '%');
                    $query->orWhere('bus_route', 'LIKE', '%' . $value . '%');
                    $query->orWhere('stoppage', 'LIKE', '%' . $value . '%');
                    $query->orWhere('transpor_charges', 'LIKE', '%' . $value . '%');
                    
                    $query->orWhere('bank_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('bank_account', 'LIKE', '%' . $value . '%');
                    $query->orWhere('branch_name', 'LIKE', '%' . $value . '%');
                    $query->orWhere('ifsc', 'LIKE', '%' . $value . '%');
                    $query->orWhere('micr_code', 'LIKE', '%' . $value . '%');
                    $query->orWhere('remark_1', 'LIKE', '%' . $value . '%');
                    $query->orWhere('admission_date', 'LIKE', '%' . $value . '%');
                });
            }

            if ($request->admissionNo != '') {
                $data = $data->where("admissionNo", $request->admissionNo);
            }
            if ($request->class_type_id != '') {
                $data = $data->where("class_type_id", $request->class_type_id);
            }
            
           
    
                 $data = $data->where("admissions.status", 1)->where('admissions.school',1)->get();
         return view('students.academic_performance.student_performance',['students'=>$data,'search'=>$search]);
     }
    
    
    
    
    
}