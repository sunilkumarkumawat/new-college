<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Student;
use App\Models\Admission;
use App\Models\RollNumber;
use App\Models\StudentId;
use App\Models\StudentAction;
use App\Models\Classs;
use App\Models\BillCounter;
use App\Models\SmsSetting;
use App\Models\ClassType;
use App\Models\BloodGroup;
use App\Models\Gender;
use App\Models\CustomIdCard;
use App\Models\Master\Branch;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Session;
use Hash;
use Helper;
use Str;
use Mail;
use DB;
use Redirect;
use Auth;
use File;
use Carbon\Carbon;
use Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentsIdController extends Controller

{

            public function studentsIdData(Request $request){
        	     $admission_no = $request->get('admission_no');
        	     $class_type_id = $request->get('class_type_id');
        	     $country_id = $request->get('country_id');
        	     $state_id = $request->get('state_id');
        	     $city_id = $request->get('city_id');
                 $data =  Admission::with('ClassTypes');
                if(Session::get('role_id') > 1){
                    $data = $data->where('branch_id', Session::get('branch_id'));
                }
                if (!empty(Session::get('admin_branch_id'))) {
                    $data = $data->where('branch_id', Session::get('admin_branch_id'));
                }
                if(!empty($admission_no )){
                    $data = $data ->where("admissionNo", $admission_no);
                }
                if(!empty($class_type_id )){
                    $data = $data ->where("class_type_id", $class_type_id);
                } 
                if(!empty($country_id)){
                    $data = $data ->where("country_id", $country_id);
                }
                if(!empty($state_id)){
                    $data = $data ->where("state_id", $state_id);
                }
                if(!empty($city_id)){
                    $data = $data ->where("district_id", $city_id);
                }
                $alladmission = $data->orderBy('id','DESC')->get();
                return  view('students.student_id.student_id_Search',['data'=>$alladmission]);
            }    
    
            public function studentIdIndex(Request $request){
                $data = Admission::with('ClassTypes')->where('status',1);
                if(Session::get('role_id') > 1){
                    $data = $data->where('branch_id',Session::get('branch_id'));
                }
                if (!empty(Session::get('admin_branch_id'))) {
                    $data = $data->where('branch_id', Session::get('admin_branch_id'));
                }
                if (Session::get('role_id') == 2) {
                    $data = $data->where('class_type_id', Session::get('class_type_id'));
                }
                $search['admission_id'] = $request->admission_id;
                $search['class_search_id'] = $request->class_search_id;
                $search['course'] = $request->course;
                $search['batch'] = $request->batch;
                
                if(!empty($request->admission_id)){
                    $data = $data->where('id',$request->admission_id);
                }
                if(!empty($request->class_search_id)){
                    $data = $data->where("class_type_id", $request->class_search_id);
                }
                if(!empty($request->course)){
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
                if(!empty($request->batch)){
                    $data = $data->where("batch", $request->batch);
                }
                
                $alladmission = $data->where('school',1)->orderBy('id','DESC')->get();
                return view('students.student_id.index',['data'=>$alladmission,'search'=>$search]);
            }

            public function customIdCard(Request $request){
                $search['branch_id'] = $request->branch_id;
                $search['designation'] = $request->designation;
                $data = CustomIdCard::select('custom_id_card.*','branch.branch_name as branch_name')
                ->leftJoin('branch','branch.id','custom_id_card.branch_id');
                if($request->isMethod('post')){
                    if(!empty($request->branch_id)){
                        $data = $data->where('custom_id_card.branch_id', $request->branch_id);
                    }
                    if (!empty($request->designation)) {
                        if ($request->designation == 'Student') {
                            $data = $data->where('custom_id_card.designation', 'Student');
                        } else if ($request->designation == 'Intern') {
                            $data = $data->where('custom_id_card.designation', 'Intern');
                        } else {
                            $data = $data->whereNotIn('custom_id_card.designation', ['Student', 'Intern']);
                        }
                    }

                }
                $data = $data->get();
                return view('students.student_id.customIdCard', ['data' => $data, 'search' => $search]);
            }

            public function customIdCatdImageUpload(Request $request){
                if($request->isMethod('post')){
               
                    if ($request->file('image')) {
                        foreach($request->file('image') as $img){
                            $originalName = $img->getClientOriginalName();
                            $filenameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
                            $admission = CustomIdCard::where('roll_no', $filenameWithoutExtension)->orWhere('employee_code', $filenameWithoutExtension)->first();
                            if($admission){
                                if (File::exists(env('IMAGE_UPLOAD_PATH') . 'customIdCard/' . $admission->image)) {
                                    File::delete(env('IMAGE_UPLOAD_PATH') . 'customIdCard/' . $admission->image);
                                }
                                $student_image = $originalName;
                                $destinationPath = env('IMAGE_UPLOAD_PATH') . 'customIdCard';
                                $img->move($destinationPath, $student_image);
                                $admission->image = $student_image;
                                $admission->save();
                            }
                        }
                         return redirect::to('customIdCard')->with('message','Images Updated Succcessfully');
                    }
                }
            }

            public function customIdCardExcelUpload(Request $request){   
                
                $the_file = $request->file('excel');
                    try {
                        $spreadsheet = IOFactory::load($the_file->getRealPath());
                        $sheet = $spreadsheet->getActiveSheet();
                        $row_limit = $sheet->getHighestDataRow();
                        $column_limit = $sheet->getHighestDataColumn();
                        $row_range = range(2, $row_limit);
                        $highestColumnNumber = $this->columnLetterToNumber($column_limit);
                        $data = array();
                        $val2 = [];
                            foreach ($row_range as $row) {
                                $val =[];
                                for($i =0; $i<$highestColumnNumber;$i++){
                                $cell =$sheet->getCell($this->indexToColumnName($i) . $row);
                                $value = $cell->getValue();
                                // Check if the cell contains rich text
                                    if ($value instanceof RichText) {
                                         $value = $value->getPlainText();
                                    }
                                        $index = $sheet->getCell($this->indexToColumnName($i) . 2)->getValue().'';
                                
                                    
                                    if ($this->indexToColumnName($i) == 'B') {
                                        $val['first_name'] = $value;
                                    }
                                  if ($this->indexToColumnName($i) == 'C') {
                                        $val['designation'] = str_replace(' ', '', $value);
                                    }
                                    if ($this->indexToColumnName($i) == 'D') {
                                        $val['department'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'E') {
                                        $val['roll_no'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'F') {
                                        $val['blood_group'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'G') {
                                        $val['date_of_joining'] = $this->convertExcelDate($value);
                                    }
                                    if ($this->indexToColumnName($i) == 'H') {
                                        $val['address'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'I') {
                                        $val['mobile'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'J') {
                                        $val['dob'] = $this->convertExcelDate($value);
                                    }
                                    if ($this->indexToColumnName($i) == 'K') {
                                        $val['valid_upto'] = $this->convertExcelDate($value);
                                    }
                                    if ($this->indexToColumnName($i) == 'L') {
                                        $val['hostler'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'M') {
                                        $val['employee_code'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'N') {
                                        $val['batch'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'O') {
                                        $val['internship'] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'P') {
                                        $val['internship_start_date'] = $this->convertExcelDate($value);
                                    }
                                    if ($this->indexToColumnName($i) == 'Q') {
                                        $val['internship_completion_date'] = $this->convertExcelDate($value);
                                    }
                                }
                              //  dd($val);
                                $val['session_id']= Session::get('session_id');
                                $val['branch_id'] = $request->branch_id;
                                $val['status']=1;
                                $val2[]=$val;
                            }
                    //dd($val2);
                     DB::table('custom_id_card')->insert($val2);
                } 
                catch (Exception $e) {
                $error_code = $e->errorInfo[1];
                return redirect('customIdCard')->with('error', 'Error Data Not Added !');
                }
                return redirect('customIdCard')->with('message', 'Data Add Successful !');
            }

            public function customIdPrint(Request $request, $id){
                
                $data =  CustomIdCard::find($id);
              
                return view('students.student_id.customIdPrint', ['data' => $data]);
            }
            
            public function visitorPass(Request $request)
            {
                $count = $request->Visitor_pass ?? 1;   
            
                return view('students.student_id.visterPass', compact('count'));
            }

           /* public function visterPass(Request $request){
                
                 return view('students.student_id.visterPass');
            }*/
            public function customIdPrintMultiple(Request $request){
                $request->validate([
                    'checkbox'  => 'required',
                ]);
                $ids =  CustomIdCard::select('custom_id_card.*','branch.branch_name as college_name','settings.address as college_address', 'settings.seal_sign as college_seal_sign', 'settings.gmail as college_email', 'settings.left_logo as college_left_logo')
                        ->leftJoin('branch','branch.id','custom_id_card.branch_id')
                        ->leftJoin('settings','branch.id','settings.branch_id')->find($request->checkbox);
                 return view('students.student_id.customIdPrintMultiple', ['data' => $ids]);
            }

            function indexToColumnName($index) {
                $columnName = '';
                    while ($index >= 0) {
                        $columnName = chr(($index % 26) + 65) . $columnName;
                        $index = intdiv($index, 26) - 1;
                    }
                return $columnName;
            }

            function columnLetterToNumber($columnLetter) {
                $columnNumber = 0;
                $length = strlen($columnLetter);
                    for ($i = 0; $i < $length; $i++) {
                        $columnNumber = $columnNumber * 26 + (ord($columnLetter[$i]) - ord('A') + 1);
                    }
                return $columnNumber;
            }

            protected function convertExcelDate($date){
                if(is_numeric($date)) {
                        return Carbon::createFromFormat('Y-m-d', '1899-12-30')->addDays($date);
                    }elseif (is_string($date)) {
                        try{
                            return Carbon::createFromFormat('Y-m-d', $date);
                        } catch (\Exception $e) {
                            try{
                                return Carbon::createFromFormat('d-m-Y', $date);
                            }catch (\Exception $e) {
                                try {
                                    return Carbon::createFromFormat('m-d-Y', $date);
                                }catch (\Exception $e) {
                                        return null;
                                }
                            }
                        }
                    }
                     return null;
            }
    
    public function updateSingleFieldCustomIdCard(Request $request){
        if($request->isMethod("post")){
            $field_name = $request->name;
            $value = $request->value;
            $modal = 'App\Models\\' . $request->modal ?? '';
            $id = $request->id;
            $data = $modal::where('id',$id)->first();
            $data->$field_name = $value;
            $data->save();
            
            if(!empty($data)){
                return Response::json(array('status' => true,'message' => "Field Save Successfully")); 
            }else{
                return Response::json(array('status' => false,'message' => "Something Went Wrong")); 
            }
        
            
        }
    }

    public function deleteCustomIdCard(Request $request){

        try{
            $delete = CustomIdCard::whereIn('id', $request->checkbox)->delete();
            return Response::json(array('status' => true,'message' => 'Ids Deleted Successfully', 'id' => $request->checkbox)); 
        }
        catch (Exception $e) {
            return Response::json(array('status' => false,'message' => $e->message)); 
        }
        
    }

}