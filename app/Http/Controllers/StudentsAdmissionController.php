<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use App\Models\User;
use App\Models\Enquiry;
use App\Models\Admission;
use App\Models\RollNumber;
use App\Models\StudentId;
use App\Models\StudentAction;
use App\Models\exam\FillMarks;
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


class StudentsAdmissionController extends Controller
{
           /*public function unique_system_id(){
                $data = Admission::whereNull('unique_system_id')->get();
                
                if(!empty($data)){ 
                    foreach($data as $item){
                        $find = Admission::find($item->id);
                        $uniqueId = strtoupper(Str::random(10));
                        $find->unique_system_id = $uniqueId;
                        $find->save();
                    }
                }
            }*/
    
            protected function unique_system_id($id){
                $uniqueId = strtoupper(Str::random(10));
                Admission::where('id',$id)->whereNull('unique_system_id')->update(['unique_system_id' => $uniqueId]);
            }

            /**
             * Upload and compress student/parent profile image
             */
            private function handleStudentImageUpload($file, $subfolder, $oldImage = null)
            {
                $filename = time() . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = env('IMAGE_UPLOAD_PATH') . $subfolder . '/';

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                if (!empty($oldImage) && File::exists($destinationPath . $oldImage)) {
                    File::delete($destinationPath . $oldImage);
                }

                $compressedImage = Image::make($file)
                    ->resize(600, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 80);
                $compressedImage->save($destinationPath . $filename);

                return $filename;
            }

            /**
             * Map request input to Admission model attributes
             */
            private function mapAdmissionAttributes(Admission $admission, Request $request, $isEdit = false)
            {
                $admission->user_id = Session::get('id');
                $admission->session_id = Session::get('session_id');
                $admission->branch_id = Session::get('branch_id');

                if (!$isEdit) {
                    $admissionNo = !empty($request->admissionNo) ? $request->admissionNo : self::generateNextAdmissionNo($request->course, $request->course_id);
                    $admission->admissionNo = $admissionNo;
                    $admission->school = '1';
                    $admission->library = '0';
                    $admission->hostel = '0';
                    $admission->password = Hash::make($admissionNo);
                    $admission->confirm_password = $admissionNo;
                    $status = 1;
                    if (($request->newStudentRegistration ?? '') == 'newStudentRegistration') {
                        $status = 'newStudentRegistration';
                    }
                    $admission->status = $status;
                    $admission->userName = strtoupper(str_replace(' ', '', $request->first_name)) . $request->admissionNo;
                }

                $admission->ledger_no = $request->ledger_no;
                $admission->roll_no = $request->roll_no;
                $admission->admission_date = $request->admission_date;
                $admission->admission_type_id = $request->admission_type_id;
                $admission->rte_student = $request->rte_student;
                $admission->class_type_id = $request->class_type_id;

                if (!empty($request->stream_subject)) {
                    $admission->stream_subject = is_array($request->stream_subject) ? implode(',', $request->stream_subject) : $request->stream_subject;
                } else {
                    $admission->stream_subject = null;
                }

                $course_name = $request->course;
                if (empty($course_name) && !empty($request->class_type_id)) {
                    $cType = ClassType::find($request->class_type_id);
                    if ($cType && !empty($cType->course_id)) {
                        $crs = \App\Models\Master\Course::find($cType->course_id);
                        if ($crs) {
                            $course_name = $crs->name;
                        }
                    }
                }
                $admission->course = $course_name;
                $admission->student_type = $request->student_type;
                $admission->batch = $request->batch;
                $admission->first_name = $request->first_name;
                $admission->last_name = $request->last_name;
                $admission->aadhaar = $request->aadhaar;
                $admission->jan_aadhaar = $request->jan_aadhaar;
                $admission->previous_school = $request->previous_school;
                $admission->email = $request->email;
                $admission->mobile = $request->mobile;
                $admission->father_name = $request->father_name;
                $admission->mother_name = $request->mother_name;
                $admission->father_mobile = $request->father_mobile;
                $admission->dob = $request->dob;
                $admission->gender_id = $request->gender_id;
                $admission->blood_group = $request->blood_group;
                $admission->medium = $request->medium;
                $admission->address = $request->address;
                $admission->country_id = $request->country;
                $admission->village_city = $request->village_city;
                $admission->family_id = $request->family_id;
                $admission->srn = $request->srn;
                $admission->religion = $request->religion;
                $admission->category = $request->category;
                $admission->caste_category = $request->caste_category;
                $admission->transport = $request->transport;
                $admission->bus_number = $request->bus_number;
                $admission->bus_route = $request->bus_route;
                $admission->stoppage = $request->stoppage;
                $admission->transpor_charges = $request->transpor_charges;
                $admission->guardian_name = $request->guardian_name;
                $admission->guardian_mobile = $request->guardian_mobile;
                $admission->mother_mob = $request->mother_mob;
                $admission->father_aadhaar = $request->father_aadhaar;
                $admission->mother_aadhaar = $request->mother_aadhaar;
                $admission->family_annual_income = $request->family_annual_income;
                $admission->bank_account = $request->bank_account;
                $admission->bank_name = $request->bank_name;
                $admission->branch_name = $request->branch_name;
                $admission->ifsc = $request->ifsc;
                $admission->micr_code = $request->micr_code;
                $admission->city_id = $request->city;
                $admission->state_id = $request->state;
                $admission->relation_student = $request->relation_student;
                $admission->school_namestudied_last_year = $request->school_namestudied_last_year;
                $admission->house = $request->house;
                $admission->height = $request->height;
                $admission->weight = $request->weight;
                $admission->pincode = $request->pincode;
                $admission->remark_1 = $request->remark_1;
                $admission->bank_account_holder = $request->bank_account_holder;
                $admission->optional_subject = $request->optional_subject;
                $admission->urban = $request->urban;
                $admission->district = $request->district;
                $admission->tehsil = $request->tehsil;
                $admission->father_pancard = $request->father_pancard;
                $admission->mother_pancard = $request->mother_pancard;
                $admission->income_tax_payee_father = $request->income_tax_payee_father;
                $admission->income_tax_payee_mother = $request->income_tax_payee_mother;
                $admission->bpl = $request->bpl;
                $admission->bpl_certificate_no = $request->bpl_certificate_no;
                $admission->father_occupation = $request->father_occupation;
                $admission->mother_occupation = $request->mother_occupation;
                $admission->enrollment_no = $request->enrollment_no;
                $admission->web_registration_no = $request->web_registration_no;
                $admission->agent_id = $request->agent_id;
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
            public function saveAdmissionDatatableFields(Request $request){
                $data = DatatableFields::find(1);
                    if(!empty($data)){
                    $data->fields= implode(',', $request->fields);
                    $data->save();
                    }
                            else
                        {
                            $data = new DatatableFields;
                            $data->fields = implode(',', $request->fields);
                            $data->save();
                        }
                return redirect::to('admissionView')->with('message','Datatable Fields Selected Successfully');
            }
    
            public function updateByExcel(Request $request){
                //dd($request);
                $array = Helper::getAdmissionDatatableFields();
                // $array['SR.NO'] = 'id';
                // $array['Student.Ad. No'] = 'admissionNo';
                // $array['Image'] = 'image';
                // $array['Ledger No'] = 'ledger_no';
                // $array['Srn'] = 'srn';
                // $array['Name'] = 'first_name';
                // $array['F Name'] = 'father_name';
                // $array['M Name'] = 'mother_name';
                // $array['Class'] = 'class_type_id';
                // $array['Mobile'] = 'mobile';
                // $array['D.O.B.'] = 'dob';
                // $array['State'] = 'state_id';
                // $array['City'] = 'city_id';
                // $array['Village'] = 'village_city';
                // $array['Address'] = 'address';
                // $array['Pincode'] = 'pincode';
                // $array['Caste Category'] = 'caste_category';
                // /*$array['Id Proof'] = 'id_proof';
                // $array['Id Number'] = 'id_number';*/
                // $array['Blood Group'] = 'blood_group';
                // $array['House'] = 'house';
                // $array['Height'] = 'height';
                // $array['Weight'] = 'weight';
                // $array['Family Income'] = 'family_annual_income';
                // $array['Email'] = 'email';
                // $array['Family Id'] = 'family_id';
                // $array['Religion'] = 'religion';
                // $array['Category'] = 'category';
                // $array['Aadhar'] = 'aadhaar';
                // $array['Gender'] = 'gender_id';
                // $array['Father Mobile'] = 'father_mobile';
                // $array['Father Aadhaar'] = 'father_aadhaar';
                // $array['Mother Mobile'] = 'mother_mob';
                // $array['Mother Aadhaar'] = 'mother_aadhaar';
                // $array['Guardian Name'] = 'guardian_name';
                // $array['Guardian Mobile'] = 'guardian_mobile';
                // $array['Admission Type'] = 'admission_type_id';
                // $array['Bus No.'] = 'bus_number';
                // $array['Bus Route'] = 'bus_route';
                // $array['Stoppage'] = 'stoppage';
                // $array['Transport Charge'] = 'transpor_charges';
                // $array['Bank Name'] = 'bank_name';
                // $array['Bank Account'] = 'bank_account';
                // $array['Branch Name'] = 'branch_name';
                // $array['IFSC'] = 'ifsc';
                // $array['MICR Code'] = 'micr_code';
                // $array['Remark'] = 'remark_1';
                // $array['Ad. Date'] = 'admission_date';
                // $array['Transport'] = 'transport';
                $branch = Branch::find(Session::get('session_id'));
                $the_file = $request->file('excel');
                try {
                    $spreadsheet = IOFactory::load($the_file->getRealPath());
                    $sheet        = $spreadsheet->getActiveSheet();
                    $row_limit    = $sheet->getHighestDataRow();
                    $column_limit = $sheet->getHighestDataColumn();
                    $row_range    = range(4, $row_limit);
                    $column_range = range('F', $column_limit);
                    $highestColumnNumber = $this->columnLetterToNumber($column_limit);
                    $data = array();
                    $val2 = [];
                    foreach ($row_range as $row) {
                        $val = [];
                            for ($i = 0; $i < $highestColumnNumber; $i++) {
                                $cell = $sheet->getCell($this->indexToColumnName($i) . $row);
                                $value = $cell->getValue();
                                if ($value instanceof RichText) {
                                    $value = $value->getPlainText();
                                }
                                $index = $sheet->getCell($this->indexToColumnName($i) . 3)->getValue() . '';
                                if ($index == 'Class'){
                                    $classType = ClassType::where('name', $value)->first();
                                    $value = $classType->id ?? '';
                                }
                                if ($index == 'Admission Type'){
                                    $admissionTypeMapping = ["Non RTE" => 1, "RTE" => 2];
                                    $value = $admissionTypeMapping[$value] ?? '1';
                                }
                                if ($index == 'Gender'){
                                    $genderTypeMapping = Gender::where('name', $value)->first();
                                    $value = $genderTypeMapping->id ?? '';
                                }
                                if ($index == 'Blood Group'){
                                    $bloodgroupTypeMapping = BloodGroup::where('name', $value)->first();
                                    $value = $bloodgroupTypeMapping->id ?? '';
                                }
                                if ($index == 'State'){
                                    $state = State::where('name', $value)->first();
                                    $value = $state->id ?? '';
                                }
                                if ($index == 'City') {
                                    $city = City::where('name', $value)->first();
                                    $value = $city->id ?? '';
                                }
                                if ($index == 'D.O.B.'){
                                    $value =  $this->convertExcelDate($value);
                                }
                                if ($index == 'Ad. Date'){
                                    $value =  $this->convertExcelDate($value);
                                }
                                if (!empty($array[$index])) {
                                    $val[$array[$index]] = $value;
                                }
                            }
                        $val2[] = $val;
                    }
                    foreach ($val2 as $student) {
                        $fullName = $student['first_name'] ?? '';
                        $nameParts = explode(' ', $fullName);
                        $firstName = '';
                        $lastName ='';
                            if (count($nameParts) > 1) {
                                $lastName = array_pop($nameParts);
                                $firstName = implode(' ', $nameParts);
                            } else{
                                $firstName = $fullName;
                                $lastName = ''; 
                            }
                                $addadmission = Admission::find($student['id']);
                                $addadmission->admissionNo = array_key_exists('admissionNo', $student) ?  $student['admissionNo'] ?? '' : $addadmission->admissionNo ?? null;
                                $addadmission->ledger_no = array_key_exists('ledger_no', $student) ?  $student['ledger_no'] ?? '' : $addadmission->ledger_no ?? null;
                                $addadmission->srn = array_key_exists('srn', $student) ?  $student['srn'] ?? '' : $addadmission->srn ?? null;
                                $addadmission->first_name = array_key_exists('first_name', $student) ?  $firstName : $addadmission->first_name ?? null;
                                $addadmission->last_name = array_key_exists('first_name', $student) ?  $lastName : $addadmission->last_name ?? null;
                                $addadmission->father_name =  array_key_exists('father_name', $student) ?  $student['father_name'] ?? '' : $addadmission->father_name ?? null;
                                $addadmission->mother_name =  array_key_exists('mother_name', $student) ?  $student['mother_name'] ?? '' : $addadmission->mother_name ?? null;
                                $addadmission->class_type_id =  array_key_exists('class_type_id', $student) ?  $student['class_type_id'] ?? '' : $addadmission->class_type_id ?? null;
                                $addadmission->mobile =  array_key_exists('mobile', $student) ?  $student['mobile'] ?? '' : $addadmission->mobile ?? null;
                                $addadmission->dob = array_key_exists('dob', $student) ?  $student['dob'] ?? '' : $addadmission->dob ?? null;
                                $addadmission->state_id = array_key_exists('state_id', $student) ?  $student['state_id'] ?? '' : $addadmission->state_id ?? null;
                                $addadmission->city_id = array_key_exists('city_id', $student) ?  $student['city_id'] ?? '' : $addadmission->city_id ?? null;
                                $addadmission->village_city = array_key_exists('village_city', $student) ?  $student['village_city'] ?? '' : $addadmission->village_city ?? null;
                                $addadmission->address = array_key_exists('address', $student) ?  $student['address'] ?? '' : $addadmission->address ?? null;
                                $addadmission->pincode = array_key_exists('pincode', $student) ?  $student['pincode'] ?? '' : $addadmission->pincode ?? null;
                                $addadmission->caste_category = array_key_exists('caste_category', $student) ?  $student['caste_category'] ?? '' : $addadmission->caste_category ?? null;
                                // $addadmission->id_proof = array_key_exists('id_proof', $student) ?  $student['id_proof'] ?? '' : $addadmission->id_proof ?? null;
                                // $addadmission->id_number = array_key_exists('id_number', $student) ?  $student['id_number'] ?? '' : $addadmission->id_number ?? null;
                                $addadmission->blood_group = array_key_exists('blood_group', $student) ?  $student['blood_group'] ?? '' : $addadmission->blood_group ?? null;
                                $addadmission->house = array_key_exists('house', $student) ?  $student['house'] ?? '' : $addadmission->house ?? null;
                                $addadmission->height = array_key_exists('height', $student) ?  $student['height'] ?? '' : $addadmission->height ?? null;
                                $addadmission->weight = array_key_exists('weight', $student) ?  $student['weight'] ?? '' : $addadmission->weight ?? null;
                                $addadmission->family_annual_income = array_key_exists('family_annual_income', $student) ?  $student['family_annual_income'] ?? '' : $addadmission->family_annual_income ?? null;
                                $addadmission->email = array_key_exists('email', $student) ?  $student['email'] ?? '' : $addadmission->email ?? null;
                                $addadmission->family_id = array_key_exists('family_id', $student) ?  $student['family_id'] ?? '' : $addadmission->family_id ?? null;
                                $addadmission->religion = array_key_exists('religion', $student) ?  $student['religion'] ?? '' : $addadmission->religion ?? null;
                                $addadmission->category = array_key_exists('category', $student) ?  $student['category'] ?? '' : $addadmission->category ?? null;
                                $addadmission->aadhaar = array_key_exists('aadhaar', $student) ?  $student['aadhaar'] ?? '' : $addadmission->aadhaar ?? null;
                                $addadmission->jan_aadhaar = array_key_exists('jan_aadhaar', $student) ?  $student['jan_aadhaar'] ?? '' : $addadmission->jan_aadhaar ?? null;
                                $addadmission->previous_school = array_key_exists('previous_school', $student) ?  $student['previous_school'] ?? '' : $addadmission->previous_school ?? null;
                                $addadmission->gender_id = array_key_exists('gender_id', $student) ?  $student['gender_id'] ?? '' : $addadmission->gender_id ?? null;
                                $addadmission->father_mobile = array_key_exists('father_mobile', $student) ?  $student['father_mobile'] ?? '' : $addadmission->father_mobile ?? null;
                                $addadmission->father_aadhaar = array_key_exists('father_aadhaar', $student) ?  $student['father_aadhaar'] ?? '' : $addadmission->father_aadhaar ?? null;
                                $addadmission->mother_mob = array_key_exists('mother_mob', $student) ?  $student['mother_mob'] ?? '' : $addadmission->mother_mob ?? null;
                                $addadmission->mother_aadhaar = array_key_exists('mother_aadhaar', $student) ?  $student['mother_aadhaar'] ?? '' : $addadmission->mother_aadhaar ?? null;
                                $addadmission->guardian_name = array_key_exists('guardian_name', $student) ?  $student['guardian_name'] ?? '' : $addadmission->guardian_name ?? null;
                                $addadmission->guardian_mobile = array_key_exists('guardian_mobile', $student) ?  $student['guardian_mobile'] ?? '' : $addadmission->guardian_mobile ?? null;
                                $addadmission->admission_type_id = array_key_exists('admission_type_id', $student) ?  $student['admission_type_id'] ?? '' : $addadmission->admission_type_id ?? null;
                                $addadmission->bus_number = array_key_exists('bus_number', $student) ?  $student['bus_number'] ?? '' : $addadmission->bus_number ?? null;
                                $addadmission->bus_route = array_key_exists('bus_route', $student) ?  $student['bus_route'] ?? '' : $addadmission->bus_route ?? null;
                                $addadmission->stoppage = array_key_exists('stoppage', $student) ?  $student['stoppage'] ?? '' : $addadmission->stoppage ?? null;
                                $addadmission->transpor_charges = array_key_exists('transpor_charges', $student) ?  $student['transpor_charges'] ?? '' : $addadmission->transpor_charges ?? null;
                                $addadmission->bank_name = array_key_exists('bank_name', $student) ?  $student['bank_name'] ?? '' : $addadmission->bank_name ?? null;
                                $addadmission->bank_account = array_key_exists('bank_account', $student) ?  $student['bank_account'] ?? '' : $addadmission->bank_account ?? null;
                                $addadmission->branch_name = array_key_exists('branch_name', $student) ?  $student['branch_name'] ?? '' : $addadmission->branch_name ?? null;
                                $addadmission->ifsc = array_key_exists('ifsc', $student) ?  $student['ifsc'] ?? '' : $addadmission->ifsc ?? null;
                                $addadmission->micr_code = array_key_exists('micr_code', $student) ?  $student['micr_code'] ?? '' : $addadmission->micr_code ?? null;
                                $addadmission->remark_1 = array_key_exists('remark_1', $student) ?  $student['remark_1'] ?? '' : $addadmission->remark_1 ?? null;
                                $addadmission->admission_date =   array_key_exists('admission_date', $student) ?  $student['admission_date'] ?? '' : $addadmission->admission_date ?? null;
                                $addadmission->transport = array_key_exists('transport', $student) ?  $student['transport'] ?? '' : $addadmission->transport ?? null;
                                $addadmission->bank_account_holder = array_key_exists('bank_account_holder', $student) ?  $student['bank_account_holder'] ?? '' : $addadmission->bank_account_holder ?? null;
                                $addadmission->optional_subject = array_key_exists('optional_subject', $student) ?  $student['optional_subject'] ?? '' : $addadmission->optional_subject ?? null;
                                $addadmission->urban = array_key_exists('urban', $student) ?  $student['urban'] ?? '' : $addadmission->urban ?? null;
                                $addadmission->district = array_key_exists('district', $student) ?  $student['district'] ?? '' : $addadmission->district ?? null;
                                $addadmission->tehsil = array_key_exists('tehsil', $student) ?  $student['tehsil'] ?? '' : $addadmission->tehsil ?? null;
                                $addadmission->father_pancard = array_key_exists('father_pancard', $student) ?  $student['father_pancard'] ?? '' : $addadmission->father_pancard ?? null;
                                $addadmission->mother_pancard = array_key_exists('mother_pancard', $student) ?  $student['mother_pancard'] ?? '' : $addadmission->mother_pancard ?? null;
                                $addadmission->income_tax_payee_father = array_key_exists('income_tax_payee_father', $student) ?  $student['income_tax_payee_father'] ?? '' : $addadmission->income_tax_payee_father ?? null;
                                $addadmission->income_tax_payee_mother = array_key_exists('income_tax_payee_mother', $student) ?  $student['income_tax_payee_mother'] ?? '' : $addadmission->income_tax_payee_mother ?? null;
                                $addadmission->bpl = array_key_exists('bpl', $student) ?  $student['bpl'] ?? '' : $addadmission->bpl ?? null;
                                $addadmission->bpl_certificate_no = array_key_exists('bpl_certificate_no', $student) ?  $student['bpl_certificate_no'] ?? '' : $addadmission->bpl_certificate_no ?? null;
                                $addadmission->father_occupation = array_key_exists('father_occupation', $student) ?  $student['father_occupation'] ?? '' : $addadmission->father_occupation ?? null;
                                $addadmission->mother_occupation = array_key_exists('mother_occupation', $student) ?  $student['mother_occupation'] ?? '' : $addadmission->mother_occupation ?? null;
                                $addadmission->save();
                            }
                                // DB::table('admissions')->insert($data);
                        }   
                    catch (Exception $e) {
                        $error_code = $e->errorInfo[1];
                        return redirect('admissionAdd')->with('error', 'Error Student Not Added !');
                    }
                return redirect('admissionView')->with('message', 'Student Add Successful !');
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
            
            public function studentExcelAdd(Request $request){   
                $array = Helper::getAdmissionDatatableFields();
                $branch = Branch::find(Session::get('session_id'));
                $the_file = $request->file('excel');
                    try {
                        $spreadsheet = IOFactory::load($the_file->getRealPath());
                        $sheet = $spreadsheet->getActiveSheet();
                        $row_limit = $sheet->getHighestDataRow();
                        $column_limit = $sheet->getHighestDataColumn();
                        $row_range = range(3, $row_limit);
                        //$column_range = range('F', $column_limit);
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
                                    if($index == 'Class'){
                                        $classType = ClassType::where('name', $value)->where('branch_id',Session::get('branch_id'))->first();
                                        $value = $classType->id ?? '';
                                    }
                                    if($index == 'Admission Type'){
                                        $admissionTypeMapping = ["Non RTE" => 1, "RTE" => 2 ];
                                        $value = $admissionTypeMapping[$value] ?? '1';
                                    }
                                    if($index == 'Gender'){
                                        $genderTypeMapping = Gender::where('name', $value)->first();
                                        $value = $genderTypeMapping->id ?? '';
                                    }
                                    if($index == 'Blood Group'){
                                        $bloodgroupTypeMapping = BloodGroup::where('name', $value)->first();
                                        $value = $bloodgroupTypeMapping->id ?? '';
                                    }
                                    if($index == 'State'){
                                        $stateTypeMapping = State::where('name', $value)->first();
                                        $value = $stateTypeMapping->id ?? '';
                                    }
                                    if($index == 'City'){
                                        $cityTypeMapping = City::where('name', $value)->first();
                                        $value = $cityTypeMapping->id ?? '';
                                    }
                                    if ($index == 'D.O.B.') {
                                        $value =  $this->convertExcelDate($value);
                                    }
                                    if ($index == 'Ad. Date') {
                                        $value =  $this->convertExcelDate($value);
                                    }
                                    if($index != 'SR.NO'){
                                        $val[$array[$index]] = $value;
                                    }
                                    if ($this->indexToColumnName($i) == 'B') {
                                        $columnBValue = $value ?? ''; // Handle null values safely
                                    }
                                }
                                $val['session_id']= Session::get('session_id');
                                $val['user_id']= Session::get('id');
                                $val['branch_id']=Session::get('branch_id');
                                $val['status']=1;
                                $val['school']=1;
                                $uniqueId = strtoupper(Str::random(10));
                                $val['unique_system_id'] = $uniqueId;
                                $val['userName'] = !empty($columnBValue) ? $columnBValue : '';
                                $val['password'] = !empty($columnBValue) ? Hash::make($columnBValue) : '';
                                $val['confirm_password'] = !empty($columnBValue) ? $columnBValue : '';
                                $val2[]=$val;
                            }
                    //dd($val2);
                     DB::table('admissions')->insert($val2);
                } 
                catch (Exception $e) {
                $error_code = $e->errorInfo[1];
                return redirect('admissionAdd')->with('error', 'Error Student Not Added !');
                }
                return redirect('admissionView')->with('message', 'Student Add Successful !');
            }

            public function studentProfile(Request $request, $id){
                $data = Admission::find($id);
                $feesDetail = FeesMaster::where('class_type_id')->get();
                   return view('sprofile.studentprofile', ['data' => $data, 'feesDetail' => $feesDetail]);
            }
    
            public function getStreamSubjects(Request $request){
                $data = Subject::where('class_type_id',$request->class_type_id)->get();
                return $data;
            }

            public function admissionAdd(Request $request){
                $BillCounter = BillCounter::where('session_id', Session::get('session_id'))
                    ->where('branch_id', Session::get('branch_id'))
                    ->where('type', 'StudentAdmission')
                    ->first();
                $counter = !empty($BillCounter) ? ($BillCounter->counter ?? 0) : 0;
                $BillCounterNo = $counter + 1;

                if ($request->isMethod('post')) {
                    if (!empty($request->registration_id)) {
                        Enquiry::where('registration_no', $request->registration_id)->update(['status' => 1]);
                    }
                    $addadmission = new Admission();
                    if ($request->file('student_img')) {
                        $addadmission->image = $this->handleStudentImageUpload($request->file('student_img'), 'profile');
                    }
                    if ($request->file('father_img')) {
                        $addadmission->father_img = $this->handleStudentImageUpload($request->file('father_img'), 'father_image');
                    }
                    if ($request->file('mother_img')) {
                        $addadmission->mother_img = $this->handleStudentImageUpload($request->file('mother_img'), 'mother_image');
                    }
                    if (!empty($BillCounter)) {
                        $BillCounter->counter = $counter + 1;
                        $BillCounter->save();
                    }

                    $this->mapAdmissionAttributes($addadmission, $request, false);
                    $addadmission->save();

                    $addadmission_id = $addadmission->id;
                    $this->unique_system_id($addadmission_id);
                            
                            $feesGroupId = null;
                            if (!empty($request->fees_master_id) && is_array($request->fees_assign)) {
                                $feesGroup = new FeesAssign();
                                $feesGroup->user_id = Session::get('id');
                                $feesGroup->session_id = Session::get('session_id');
                                $feesGroup->branch_id = Session::get('branch_id');
                                $feesGroup->admission_id = $addadmission_id;
                                $feesGroup->save();
                                $feesGroupId = $feesGroup->id;
                    
                                $assign_count = 0;
                                $fees_group_amount = 0;
                                $fees_group_discount = 0;
                                
                                for ($count = 0; $count < count($request->fees_master_id); $count++) {
                                    if (in_array($request->fees_master_id[$count], $request->fees_assign)) {
                                        $feesGroupDetail = new FeesAssignDetail(); // model name
                                        $feesGroupDetail->user_id = Session::get('id');
                                        $feesGroupDetail->session_id = Session::get('session_id');
                                        $feesGroupDetail->branch_id = Session::get('branch_id');
                                        $feesGroupDetail->fees_group_id = $request->fees_group_id[$count];
                                        $feesGroupDetail->fees_master_id = $request->fees_assign[$assign_count];
                                        $feesGroupDetail->fees_group_amount = $request->fees_group_amount[$count];
                                        $feesGroupDetail->class_type_id = $request->class_type_id ?? null;
                                        $fees_group_amount += $request->fees_group_amount[$count];
                                        $feesGroupDetail->discount = $request->discount[$count];
                                        $fees_group_discount += $request->discount[$count];
                                        $feesGroupDetail->fees_breakdown = $request->fees_breakdown[$count];
                                        $feesGroupDetail->fees_assign_id = $feesGroupId;
                                        $feesGroupDetail->admission_id = $addadmission_id;
                                        $feesGroupDetail->save();
                                        $assign_count++;
                                    }
                                }
                             
                                $feesGroup->total_amount = $fees_group_amount;
                                $feesGroup->total_discount = $fees_group_discount;
                                $feesGroup->net_amount = $fees_group_amount - $fees_group_discount;
                                $feesGroup->save();
                            }
                           
                            $template = MessageTemplate::Select('message_templates.*','message_types.slug')
                                ->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                                ->where('message_types.status',1)->where('message_types.slug','student-admission')->first();
                            $branch = Branch::find(Session::get('branch_id'));
                            $setting = Setting::where('branch_id',Session::get('branch_id'))->first();
                            if (!empty($template)) {
                                $arrey1 = array(
                                    '{#name#}',
                                    '{#school_name#}',
                                    '{#user_name#}',
                                    '{#password#}',
                                    '{#email#}',
                                    '{#mobile#}');
                                $arrey2 = array(
                                    $addadmission->first_name." ".$addadmission->last_name,
                                    $setting->name ?? '',
                                    $addadmission->userName ?? '',
                                    $addadmission->confirm_password ?? '',
                                    $addadmission->email ?? '',
                                    $addadmission->mobile ?? '');
                                $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content ?? '');
                                if (!empty($setting) && $setting->firebase_notification == 1) {
                                    Helper::sendNotification(
                                        $template->title ?? 'Admission Notification',
                                        $whatsapp,
                                        'student',
                                        $addadmission->id
                                    ); 
                                }
                            }
                        //         if($request->email != ""){
                        //             if($branch->email_srvc != 0){
                        //                 if($template->email_status != 0){
                        //                     $message = str_replace($arrey1,$arrey2,$template->email_content);
                        //                     $emailData = ['email' => $request->email, 'data' => $message, 'subject' => $template->title];
                        //                     Helper::sendMail('email_print.template_print', $emailData);
                        //                 } 
                        //             } 
                        //         }
                                
                        //     if($branch->whatsapp_srvc != 0){
                        //         if ($request->mobile != ""){
                        //             if($template->whatsapp_status != 0){
                        //                 $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                        //                 Helper::sendWhatsappMessage($request->mobile,$whatsapp);
                        //             }
                        //         }
                        //     }
                        //     if($branch->sms_srvc != 0){
                        //         if($request->mobile != ""){
                        //             if($template->sms_status != 0){
                        //                 $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                        //                 Helper::SendMessage($request->mobile, $sms);
                        //             }
                        //         }
                        //     }    
                        // }
                        $url = '/admissionStudentIdPrint/'.$addadmission->id;
                    ?>
                        <script type="text/javascript">
                            window.open("<?=$url?>", "_blank");
                        </script>
                    <?php        
                    return redirect::to('admissionView')->with('message', 'Admission Successful.');
                }
                return view('students.admission.add', ['BillCounter' => $BillCounterNo]);
            }

            public function admissionView(Request $request){
                $generate_user_name = $request->generate_user_name ?? '';
                    // if($generate_user_name != ''){
                    //     //$this->studentUserNameCreate();  
                    // }
                    $search['admissionNo'] = $request->admissionNo;
                    $search['course'] = $request->course;
                    $search['class_type_id'] = $request->class_type_id;
                    $search['state_id'] = $request->state_id;
                    $search['city_id'] = $request->city_id;
                    $search['category'] = $request->category;
                    $search['gender_id'] = $request->gender_id;
                    $search['admission_type_id'] = $request->admission_type_id;
                    $search['blood_group'] = $request->blood_group;
                    $search['transport'] = $request->transport;
                    $search['status'] = $request->status;
                    $search['name'] = $request->name;
                    $search['batch'] = $request->batch;
                    $search['agent_id'] = $request->agent_id;
                    $search['enrollment_no'] = $request->enrollment_no;
                    $search['web_registration_no'] = $request->web_registration_no;

                    $data = Admission::select('admissions.*','class.name as class_name')
                        ->leftJoin('class_types as class','class.id','admissions.class_type_id')
                        ->orderBy('class.orderBy', 'ASC')->with('City')->with('State')->with('Agent');
                    $data = $data->where('admissions.branch_id', Session::get('branch_id'));
                    if (Session::get('role_id') == 2) {
                        $data = $data->where('admissions.class_type_id', $request->class_type_id);
                    }
                    
                    if (!empty($request->admission_id)) {
                        $data = $data->where('admissions.id', $request->admission_id);
                    }
                    if (!empty($request->name)) {
                        $value = $request->name;
                        $data = $data->where(function ($query) use ($value) {
                            $query->where('first_name', 'LIKE', '%' . $value . '%');
                            $query->orWhere('last_name', 'LIKE', '%' . $value . '%');
                            $query->orWhere('father_name', 'LIKE', '%' . $value . '%');
                            $query->orWhere('mobile', 'LIKE', '%' . $value . '%');
                            $query->orWhere('aadhaar', 'LIKE', '%' . $value . '%');
                        });
                    }
                    if (!empty($request->father_name)) {
                        $data = $data->where('admissions.father_name', 'LIKE', '%' . $request->father_name . '%');
                    }
                    if (!empty($request->mother_name)) {
                        $data = $data->where('admissions.mother_name', 'LIKE', '%' . $request->mother_name . '%');
                    }
                    if (!empty($request->mobile)) {
                        $data = $data->where('admissions.mobile', 'LIKE', '%' . $request->mobile . '%');
                    }
                    if (!empty($request->aadhaar)) {
                        $data = $data->where('admissions.aadhaar', 'LIKE', '%' . $request->aadhaar . '%');
                    }
                    if (!empty($request->admissionNo)) {
                        $data = $data->where("admissionNo", $request->admissionNo);
                    }
                    if (!empty($request->agent_id)) {
                        $data = $data->where("admissions.agent_id", $request->agent_id);
                    }
                    if (!empty($request->enrollment_no)) {
                        $data = $data->where("admissions.enrollment_no", 'LIKE', '%' . $request->enrollment_no . '%');
                    }
                    if (!empty($request->web_registration_no)) {
                        $data = $data->where("admissions.web_registration_no", 'LIKE', '%' . $request->web_registration_no . '%');
                    }
                   
                    if (!empty($request->batch)) {
                        $data = $data->where("admissions.batch", $request->batch);
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
                    if (!empty($request->class_type_id)) {
                        $data = $data->where("admissions.class_type_id", $request->class_type_id);
                    }
                    if (!empty($request->state_id)) {
                        $data = $data->where("state_id", $request->state_id);
                    }
                    if (!empty($request->city_id)) {
                        $data = $data->where("city_id", $request->city_id);
                    }
                    if (!empty($request->category)) {
                        $data = $data->where("category", $request->category);
                    }
                    if (!empty($request->gender_id)) {
                        $data = $data->where("gender_id", $request->gender_id);
                    }
                    if (!empty($request->admission_type_id)) {
                        $data = $data->where("admission_type_id", $request->admission_type_id);
                    }
                    if (!empty($request->blood_group)) {
                        $data = $data->where("blood_group", $request->blood_group);
                    }
                    if (!empty($request->transport)) {
                        $data = $data->where("transport", $request->transport);
                    }
                    if ($request->has('status')) {
                        if ($request->status !== '' && $request->status !== 'all') {
                            $data = $data->where("status", $request->status);
                        }
                    } else {
                        $data = $data->where("status", 1);
                    }

                    $perPage = $request->get('per_page', 25);
                    if ($perPage === 'all' || $perPage == -1) {
                        $perPage = 1000;
                    } else {
                        $perPage = (int) $perPage;
                        if ($perPage <= 0) $perPage = 25;
                    }

                    $alladmission = $data->where('school', 1)
                        ->orderBy('admissions.first_name', 'ASC')
                        ->paginate($perPage)
                        ->appends($request->all());

                    if ($request->ajax()) {
                        $table = DB::table('datatable_fields')->first();
                        $dataTable = !empty($table->fields) ? explode(',', $table->fields) : [];
                        $getAdmissionDatatableFields = Helper::getAdmissionDatatableFields();

                        $tbodyHtml = view('students.admission.table_rows', [
                            'data' => $alladmission,
                            'dataTable' => $dataTable,
                            'getAdmissionDatatableFields' => $getAdmissionDatatableFields,
                            'search' => $search
                        ])->render();

                        $paginationHtml = view('students.admission.pagination', [
                            'data' => $alladmission
                        ])->render();

                        return response()->json([
                            'status' => 'success',
                            'html' => $tbodyHtml,
                            'pagination' => $paginationHtml,
                            'total' => $alladmission->total(),
                            'from' => $alladmission->firstItem() ?? 0,
                            'to' => $alladmission->lastItem() ?? 0,
                            'count' => $alladmission->count()
                        ]);
                    }

                    return view('students.admission.view', ['data' => $alladmission, 'search' => $search]);
            }
            
            public function admissionView2(Request $request){
                // dd($request);
                $search['admissionNo'] = $request->admissionNo;
                $search['class_type_id'] = $request->class_type_id;
                $search['state_id'] = $request->state_id;
                $search['city_id'] = $request->city_id;
                $search['name'] = $request->name;
                // $alladmission = Admission::orderBy('first_name', 'ASC')->where('session_id', Session::get('session_id'));
                $alladmission = Admission::select('admissions.*','class.name as class_name')
                    ->leftJoin('class_types as class','class.id','admissions.class_type_id')->orderBy('admissions.first_name', 'ASC')->where('admissions.session_id', Session::get('session_id'));
                    if (Session::get('role_id') > 1) {
                        $alladmission = $alladmission->where('admissions.branch_id', Session::get('branch_id'));
                    }
                $alladmission =  $alladmission->get();
                //dd($search);
                return view('students.admission.view_2', ['data' => $alladmission, 'search' => $search]);
            }

            public function admissionEdit(Request $request, $id){
                $data = Admission::find($id);
                if (!$data) {
                    return redirect::to('admissionView')->with('error', 'Student record not found.');
                }

                if ($request->isMethod('post')) {
                    if ($request->file('student_img')) {
                        $data->image = $this->handleStudentImageUpload($request->file('student_img'), 'profile', $data->image);
                    }
                    if ($request->file('father_img')) {
                        $data->father_img = $this->handleStudentImageUpload($request->file('father_img'), 'father_image', $data->father_img);
                    }
                    if ($request->file('mother_img')) {
                        $data->mother_img = $this->handleStudentImageUpload($request->file('mother_img'), 'mother_image', $data->mother_img);
                    }

                    $this->mapAdmissionAttributes($data, $request, true);
                    $data->save();

                    $this->unique_system_id($id);

                    if (empty($request->update)) {
                        return redirect::to('admissionView')->with('message', 'Admission Updated Successfully !');
                    } else {
                        $value = Session::get('update_n_next_value');
                        session()->put('update_n_next_value', $value . ',' . $id);
                        session()->save();
                        $updated_id = explode(',', Session::get('update_n_next_value'));
                        $next_id = Admission::whereNotIn('id', $updated_id)
                            ->where('class_type_id', $request->class_type_id)
                            ->where('session_id', Session::get('session_id'))
                            ->where('branch_id', Session::get('branch_id'))
                            ->first();

                        if (!empty($next_id)) {
                            return redirect::to('admissionEdit/' . $next_id->id)->with('message', 'Admission Edit For : ' . ($next_id->first_name ?? ''));
                        } else {
                            return redirect::to('admissionView')->with('error', 'No more students for update in this class!');
                        }
                    }
                }

                $getstate = State::where('country_id', $data->country_id ?? 0)->get();
                $getcitie = City::where('state_id', $data->state_id ?? 0)->get();

                return view('students.admission.add', [
                    'data' => $data,
                    'getState' => $getstate,
                    'getCity' => $getcitie,
                    'BillCounter' => $data->admissionNo ?? $data->id,
                    'isEdit' => true
                ]);
            }

            public function admissionDelete(Request $request){
                $id = $request->delete_id;
                $admission = Admission::find($id);
                $fess = FeesDetail::where('admission_id',$admission->id)->get();
                $marks = FillMarks::where('admission_id',$admission->id)->get();
                if(count($marks) > 0 || count($fess) > 0){
                    return redirect::to('admissionView')->with('error', 'This student has not been removed because his fees or field mark Etc. data has been entered.');
                };
                if (File::exists(env('IMAGE_UPLOAD_PATH') . 'father_image/' . $admission->father_img)) {
                    File::delete(env('IMAGE_UPLOAD_PATH') . 'father_image/' . $admission->father_img);
                }
                    $admission->delete();
                return redirect::to('admissionView')->with('message', 'Admission Deleted Successfully !');
            }
            public function admissionStudentSearch(Request $request){
                $search['name'] = $request->name;
                    if ($request->isMethod('post')) {
                        $request->validate([]);
                        $data = Enquiry::with('ClassTypes')->where('session_id', Session::get('session_id'));
                    if (Session::get('role_id') > 1) {
                        $data = $data->where('branch_id', Session::get('branch_id'));
                    }
                    if (!empty(Session::get('admin_branch_id'))) {
                        $data = $data->where('branch_id', Session::get('admin_branch_id'));
                    }
                    if (!empty($request->name)) {
                        $data = $data
                        ->where('first_name', 'like', '%' . $request->name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->name . '%')
                        ->orWhere('mobile', 'like', '%' . $request->name . '%')
                        ->orWhere('email', 'like', '%' . $request->name . '%')
                        ->orWhere('father_name', 'like', '%' . $request->name . '%')
                        ->orWhere('mother_name', 'like', '%' . $request->name . '%')
                        ->orWhere('address', 'like', '%' . $request->name . '%');
                    }
                    if (!empty($request->registration_no)) {
                        $data = $data->where("registration_no", $request->registration_no);
                    }
                    if (!empty($request->class_search_id)) {
                        $data = $data->where("class_type_id", $request->class_search_id);
                    }
                    $allstudents = $data->orderBy('id', 'DESC')->get();
                }
                return view('students.admission.studentSearchView', ['data' => $allstudents]);
            }

            public function admissionStudentOnClick(Request $request){
                $data['stu_data'] = Enquiry::where('id',$request->student_id)->first();
                return $data;
            }

            public function showFeesDetail(Request $request){
                $class_type_id = $request->get('class_id');
                $data = FeesStructure::where("class_type_id", $class_type_id)
                ->orderBy('id', 'DESC')
                ->get();
               return view('students.admission.fees_detail', ['data' => $data]);
            }

            public function birthdatEmail(Request $request){
                $data = Admission::where('dob', date('Y-m-d'))->get();
                if ($data->count() > 0) {
                    foreach ($data as $data) {
                        $emailData = ['email' => $data->email, 'data' => $data, 'subject' => 'Birthday Email.'];
                        Helper::sendmail('print_file.birthday_mail', $emailData);
                    }
                }
                return view('print_file.birthday_mail', ['data' => $data]);
            }
  
            public function admissionStudentPrint(Request $request, $id){
                $student_admission = Admission::select('admissions.*', 'sessions.from_year', 'class_types.name as class_name','sessions.to_year', 'gender.name as genderName','countries.name as country_name','states.name as state_name','citys.name as city_name')
                ->leftJoin('gender','gender.id','admissions.gender_id')
                ->leftjoin('countries','countries.id','admissions.country_id')
                ->leftjoin('states','states.id','admissions.state_id')
                ->leftjoin('citys','citys.id','admissions.city_id')
                ->leftjoin('sessions','sessions.id','admissions.session_id')
                ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                ->where('admissions.id',$id)->first();
                // dd($student_admission);
                // $pdf = PDF::loadView('master.printFilePanel.StudentManagement.template11', ['data'=>$student_admission]);
                //      $pdf->setPaper('A4', 'portrait');
                //     return $pdf->download('StudentManagement.pdf');
                $printPreview = Helper::printPreview('Admission Print');
                // dd($printPreview);
                return view($printPreview, ['data' => $student_admission]);
               // return view('print_file.student_print.admissionStudentPrint', ['data' => $student_admission]);
            }
            public function admissionStudentIdPrint(Request $request, $id){
                // $student_id = Admission::find($id);
                $student_id =  Admission::Select('admissions.*','sessions.from_year','class_types.name as class_name','sessions.to_year')
                ->leftjoin('sessions','sessions.id','admissions.session_id')
                ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                ->where('admissions.id', $id)->first();
                $printPreviewId = Helper::printPreview('Student Id Print');
                //dd($printPreviewId);
                return view($printPreviewId, ['data' => $student_id]);
                // return view('print_file.student_print.admissionStudentIdPrint', ['data' => $student_id]);
            }
            
            public function studentIdGenerator(Request $request, $id){
                $student_idGenerator =  Admission::Select('admissions.*','sessions.from_year','sessions.to_year')
                ->leftjoin('sessions','sessions.id','admissions.session_id')
                ->where('admissions.id', $id)->first();
                $printPreviewId = Helper::printPreview('Student Id Print');
                $randomString = Str::random(10);
                $pdf = PDF::loadView($printPreviewId, ['data' => $student_idGenerator]);
                file_put_contents(env('IMAGE_UPLOAD_PATH'). 'studentIdPdf' . '/' .$randomString.$student_idGenerator->admissionNo . '.pdf', $pdf->output());
                $file_url = env('IMAGE_SHOW_PATH') . 'studentIdPdf' . '/' .$randomString.$student_idGenerator->admissionNo . '.pdf';  
                Admission::where('id',$id)->update(['student_id_pdf_name' => $file_url]);
                return redirect::to('admissionView')->with('message', 'PDF Generated Successfully !');
                // return view('print_file.student_print.admissionStudentIdPrint', ['data' => $student_id]);
            }

            public function studentDetail($id){
                $studentDetail = Admission::select('admissions.*','hostel.name as hostel_name','countries.name as country_name','states.name as state_name','citys.name as city_name','hostel_room.name as room_name','hostel_bed.name as bed_name')
                ->leftjoin('hostel_assign','hostel_assign.admission_id','admissions.id')
                ->leftjoin('hostel','hostel.id','hostel_assign.hostel_id')
                ->leftjoin('hostel_room','hostel_room.id','hostel_assign.room_id')
                ->leftjoin('hostel_bed','hostel_bed.id','hostel_assign.bed_id')
                ->leftjoin('countries','countries.id','admissions.country_id')
                ->leftjoin('states','states.id','admissions.state_id')
                ->leftjoin('citys','citys.id','admissions.city_id')
                ->with(['ClassTypes', 'Gender', 'Branch'])->find($id);

                if (!$studentDetail) {
                    return redirect('admissionView')->with('error', 'Student not found!');
                }

                $feesassiggn = null;
                try {
                    $feesassiggn = FeesAssign::where('admission_id', $id)->first();
                } catch (\Exception $e) {}

                $assignedFeeDetails = collect();
                try {
                    $assignedFeeDetails = DB::table('fees_assign_details')
                        ->leftJoin('fees_group', 'fees_group.id', '=', 'fees_assign_details.fees_group_id')
                        ->where('fees_assign_details.admission_id', $id)
                        ->whereNull('fees_assign_details.deleted_at')
                        ->select('fees_assign_details.*', 'fees_group.name as group_name')
                        ->get();
                } catch (\Exception $e) {}

                $feesDetail = collect();
                try {
                    $feesDetail = FeesDetail::with(['PaymentMode', 'FeesCollect'])
                        ->where('admission_id', $id)
                        ->where('branch_id', Session::get('branch_id'))
                        ->orderBy('date', 'DESC')
                        ->orderBy('id', 'DESC')
                        ->get();
                } catch (\Exception $e) {}

                $attendanceStats = (object)[
                    'total_days' => 0,
                    'present_days' => 0,
                    'absent_days' => 0,
                    'leave_days' => 0,
                    'half_days' => 0,
                    'holiday_days' => 0,
                    'event_days' => 0
                ];
                try {
                    $stats = DB::table('student_attendance')
                        ->where('admission_id', $id)
                        ->where('session_id', Session::get('session_id'))
                        ->selectRaw('
                            COUNT(id) as total_days,
                            SUM(CASE WHEN attendance_status_id = 1 THEN 1 ELSE 0 END) as present_days,
                            SUM(CASE WHEN attendance_status_id = 2 THEN 1 ELSE 0 END) as absent_days,
                            SUM(CASE WHEN attendance_status_id = 3 THEN 1 ELSE 0 END) as leave_days,
                            SUM(CASE WHEN attendance_status_id = 4 THEN 1 ELSE 0 END) as half_days,
                            SUM(CASE WHEN attendance_status_id = 5 THEN 1 ELSE 0 END) as holiday_days,
                            SUM(CASE WHEN attendance_status_id IN (13, 14) THEN 1 ELSE 0 END) as event_days
                        ')
                        ->first();
                    if ($stats) {
                        $attendanceStats = $stats;
                    }
                } catch (\Exception $e) {}

                $recentAttendance = collect();
                try {
                    $recentAttendance = DB::table('student_attendance')
                        ->leftJoin('attendance_status', 'attendance_status.id', '=', 'student_attendance.attendance_status_id')
                        ->where('student_attendance.admission_id', $id)
                        ->where('student_attendance.session_id', Session::get('session_id'))
                        ->select('student_attendance.date', 'student_attendance.attendance_status_id', 'attendance_status.name as status_name')
                        ->orderBy('student_attendance.date', 'DESC')
                        ->limit(20)
                        ->get();
                } catch (\Exception $e) {}

                $streamSubjectsList = [];
                try {
                    if (!empty($studentDetail->class_type_id)) {
                        $streamSubjectsList = Helper::getStreamSubjects($studentDetail->class_type_id);
                    }
                } catch (\Exception $e) {}

                $libraryDetails = collect();
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('library_assign')) {
                        $libQuery = DB::table('library_assign')->where('library_assign.admission_id', $id)->whereNull('library_assign.deleted_at');
                        if (\Illuminate\Support\Facades\Schema::hasTable('library_cabins')) {
                            $libQuery->leftJoin('library_cabins', 'library_cabins.id', '=', 'library_assign.cabin_id')
                                     ->select('library_assign.*', 'library_cabins.name as cabin_name');
                        } else {
                            $libQuery->select('library_assign.*');
                        }
                        $libraryDetails = $libQuery->get();
                    }
                } catch (\Exception $e) {}

                return view('students.admission.studentDetail', [
                    'data' => $studentDetail,
                    'feesDetail' => $feesDetail,
                    'assignfees' => $feesassiggn,
                    'assignedFeeDetails' => $assignedFeeDetails,
                    'attendanceStats' => $attendanceStats,
                    'recentAttendance' => $recentAttendance,
                    'streamSubjectsList' => $streamSubjectsList,
                    'libraryDetails' => $libraryDetails
                ]);
            }
    
            public function multiAdmissionEdit(Request $request){
                if ($request->isMethod('post')) {
                    for($i=0; $i<count($request->admission_id); $i++)
                {
                $data = Admission::find($request->admission_id[$i]);
                if (!empty($request->file('student_img')[$i])) {
                    $image = $request->file('student_img')[$i];
                    $path = $image->getRealPath();
                    $student_image = time() . uniqid() . $image->getClientOriginalName();
                    $destinationPath = env('IMAGE_UPLOAD_PATH') . 'profile';
                    $image->move($destinationPath, $student_image);
                    if (File::exists(env('IMAGE_UPLOAD_PATH') . 'profile/' . $data->image)) {
                        File::delete(env('IMAGE_UPLOAD_PATH') . 'profile/' . $data->image);
                    }
                    $data->image = $student_image;
                }
                if (!empty($request->file('father_img')[$i])) {
                    $image = $request->file('father_img')[$i];
                    $path = $image->getRealPath();
                    $father_image = time() . uniqid() . $image->getClientOriginalName();
                    $destinationPath = env('IMAGE_UPLOAD_PATH') . 'father_image';
                    $image->move($destinationPath, $father_image);
                    if (File::exists(env('IMAGE_UPLOAD_PATH') . 'father_image/' . $data->father_img)) {
                    File::delete(env('IMAGE_UPLOAD_PATH') . 'father_image/' . $data->father_img);
                    }
                    $data->father_img = $father_image;
                }
                if (!empty($request->file('mother_img')[$i])) {
                    $image = $request->file('mother_img')[$i];
                    $path = $image->getRealPath();
                    $mother_image = time() . uniqid() . $image->getClientOriginalName();
                    $destinationPath = env('IMAGE_UPLOAD_PATH') . 'mother_image';
                    $image->move($destinationPath, $mother_image);
                    if (File::exists(env('IMAGE_UPLOAD_PATH') . 'mother_image/' . $data->mother_img)) {
                        File::delete(env('IMAGE_UPLOAD_PATH') . 'mother_image/' . $data->mother_img);
                    }
                    $data->mother_img = $mother_image;
                }
                    $data->admission_date = $request->admission_date[$i];
                    $data->class_type_id = $request->class_type_id[$i];
                    $data->first_name = $request->first_name[$i];
                    $data->last_name = $request->last_name[$i];
                    $data->aadhaar = $request->aadhaar[$i];
                    $data->jan_aadhaar = $request->jan_aadhaar[$i];
                    $data->previous_school = $request->previous_school[$i];
                    $data->email = $request->email[$i];
                    $data->mobile = $request->mobile[$i];
                    $data->father_name = $request->father_name[$i];
                    $data->mother_name = $request->mother_name[$i];
                    $data->father_mobile = $request->father_mobile[$i];
                    $data->dob = $request->dob[$i];
                    $data->gender_id = $request->gender_id[$i];
                    $data->admission_type_id = $request->admission_type_id[$i];
                    $data->address = $request->address[$i];
                    $data->country_id = $request->country[$i];
                    $data->village_city = $request->village_city[$i];
                    $data->city_id = $request->city[$i];
                    $data->state_id = $request->state[$i];
                    $data->pincode = $request->pincode[$i];
                    $data->remark_1 = $request->remark_1[$i];
                    $data->save();
                }
                 return redirect::to('admissionView')->with('message', 'Admission Updated Successfully !');
            }
                $getstate = State::where('country_id', $data['country_id'])->get();
                $getcitie = City::where('state_id', $data['state_id'])->get();
                return view('students.admission.edit', ['data' => $data, 'getState' => $getstate, 'getCity' => $getcitie]);
            }

            /* public function studentUserNameCreate(Request $request)
                {
                    $allStudents = Admission::whereNotNull('dob')->whereNotNull('first_name')->get();
                    foreach($allStudents as $item)
                    {
                        $session = Sessions::where('id',$item->session_id)->first();
                        $initials = substr($item->first_name, 0, 3)
                        $birthYear = date('Y', strtotime($item->dob));
                        $random_number = Str::random(10);
                        $cleanedMobile = preg_replace('/[^0-9]/', '', $item->mobile ?? $random_number);
                        // $username = 'EPA_'.strtoupper($initials) .'_'. $birthYear .'_'. substr($cleanedMobile, -4);
                        $username = 'GG_'.strtoupper($initials)  .'_'. substr($cleanedMobile, -4).'_'.$session->from_year;
                        $update = Admission::find($item->id);
                        $update->username = $username;
                        $update->password = Hash::make('12345678');
                        $update->confirm_password = '12345678';
                        $update->save();
                    }
                   
                }
            */
            
           
            
            public function bulkIdPrint(Request $request){
                $classtype = $request->class_type_id;
                $admission_ids = Admission::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('class_type_id',$classtype)->pluck('id')->implode(',');
                if(!empty($admission_ids)){
                    $data= explode(',',$admission_ids);
                }
                return view('students.student_id.bulkIdPrint',['admission_ids'=>$data]);
            }
    
            public function studentBulkImageUpload(Request $request){
                if($request->isMethod('post')){
                    if ($request->file('image')) {
                        foreach($request->file('image') as $img){
                            $originalName = $img->getClientOriginalName();
                            $filenameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
                            $admission = Admission::where('branch_id', Session::get('branch_id'))->where('session_id', $request->session_id)->where('admissionNo', $filenameWithoutExtension)->first();
                            if($admission){
                                if (File::exists(env('IMAGE_UPLOAD_PATH') . 'profile/' . $admission->image)) {
                                    File::delete(env('IMAGE_UPLOAD_PATH') . 'profile/' . $admission->image);
                                }
                                $student_image = time() . uniqid() . $originalName;
                                $destinationPath = env('IMAGE_UPLOAD_PATH') . 'profile';
                                $img->move($destinationPath, $student_image);
                                $admission->image = $student_image;
                                $admission->save();
                            }
                        }
                         return redirect::to('admissionView')->with('message','Images Updated Succcessfully');
                    }
                }
            }
    
            public function verify_admission(Request $request){
                Admission::where('id',$request->id)->update(['session_id' => $request->session_id, 'status' => 1, 'admission_date' => date('Y-m-d')]);
                $data = Admission::where('id',$request->id)->first();
                $template = "
                Hello {#name#},
                This is Administrator from {#school_name#}. I hope this message finds you well.
                We are pleased to provide you with your credentials to access our school’s online platform:
                Username: {#user_name#}
                Password: {#password#}
                Please ensure you keep these credentials secure and do not share them with others. If you have any questions or need further assistance, feel free to contact us at {#school_mobile#}.
                Thank you and have a great day!
                Best regards,
                Adminitrator
                {#school_name#}
                {#school_mobile#}
                ";    
                $branch = Branch::find(Session::get('branch_id'));
                $setting = Setting::where('branch_id',$branch->id)->first();
                $arrey1 =   array(
                    '{#name#}',
                    '{#school_name#}',
                    '{#user_name#}',
                    '{#password#}',
                    '{#school_mobile#}',
                    '{#mobile#}',);
                $arrey2 = array(
                    $data->first_name ?? '',
                    $setting->name,
                    $data->userName,
                    $data->confirm_password,
                    $setting->mobile,
                    $data->mobile);
                if($branch->whatsapp_srvc != 0){
                    if ($data->mobile != ""){
                        $whatsapp = str_replace($arrey1,$arrey2,$template);
                         Helper::sendWhatsappMessage($data->mobile,$whatsapp);
                    }
                }
                if(Session::get('session_id') != $request->session_id){
                    return redirect::to('admissionView')->with('message','Student Admission Complete Successfully');
                }else{
                    return redirect::to('admissionEdit/'.$request->id)->with('message','Student Admission Complete Successfully');
                }
            }
    
            public function category_wise_report(Request $request){
                $student_id =  Admission::Select('admissions.*','sessions.from_year','class_types.name as class_name','sessions.to_year')
                ->leftjoin('sessions','sessions.id','admissions.session_id')
                ->leftjoin('class_types','class_types.id','admissions.class_type_id')
                ->get();
                return view('students.report/category_wise_report', ['data' => $student_id]);
            }
    
            public function streamUpdate(Request $request){
                $search["exam_id"] = $request->exam_id  ?? "";
                $data = [];
                $list_subject = '';
                $search["class_type_id"] = $request->class_type_id;
                if ($request->isMethod('post')) {
                    $data = Admission::where('class_type_id',$request->class_type_id)->where("branch_id", Session::get("branch_id"))->orderBy('first_name','ASC')->get();
                    $list_subject = Subject::where("class_type_id", $request->class_type_id)->where("branch_id", Session::get("branch_id"))->orderBy("sort_by", "ASC")->get();
                }
                return view('students.admission.stream_update',['search'=>$search,'data'=>$data,'list_subject'=>$list_subject]);
            }
    
            public function streamUpdateSave(Request $request){
                if ($request->isMethod('post')) {
                    if($request->admission_id != [] && $request->subject_id != []){
                        for ($i = 0; $i < count($request->admission_id); $i++) {
                            $data = Admission::find($request->admission_id[$i]);
                            $a1 = !empty($data->stream_subject) ? explode(',', $data->stream_subject) : [];
                            $a2 = $request->subject_id;
                            $r = array_merge($a1, $a2);
                            $r_unique = array_unique($r);
                            //dd($r_unique);
                            if (!empty($request->subject_id)) {
                                $data->stream_subject = implode(',', $r_unique);
                                $data->save();  
                            }
                        }
                        return redirect::to('stream_update')->with('message', 'Admission Updated Successfully!');
                    }else{
                        return redirect::to('stream_update')->with('error', 'please select Students checkbox !');
                    }
                }
            }
            
            public function streamRemove(Request $request,$admission_id,$subject_id){
                $data = Admission::find($admission_id);
                if (!$data) {
                    return response()->json(['error' => 'Admission not found.'], 404);
                }
                $a1 = !empty($data->stream_subject) ? explode(',', $data->stream_subject) : [];
                // Using array_filter to remove the subject
                $a1 = array_filter($a1, function($subject) use ($subject_id) {
                    return $subject != $subject_id;
                });
                // Reindex the array if needed
                $a1 = array_values($a1);
                $data->stream_subject = implode(',', $a1);
                $data->save();
                return response()->json(['success' => 'Subject Updated Successfully '], 200);
            }
            
            public function folderCompressor(Request $request)
            {
                // Prevent timeout & memory issues
                ini_set('memory_limit', '1024M');
                set_time_limit(0);
            
                // Main folder path
                $path = base_path('schoolimage');
            
                // Get all files from folder recursively
                $images = \File::allFiles($path);
            
                foreach ($images as $file) {
            
                    try {
            
                        $filePath = $file->getRealPath();
            
                        // Skip invalid images
                        if (!@getimagesize($filePath)) {
                            \Log::warning("Invalid image skipped: " . $filePath);
                            continue;
                        }
            
                        // Skip already small images (optional)
                        if ($file->getSize() < 100 * 1024) {
                            continue;
                        }
            
                        // Compress & resize image
                        $compressedImage = Image::make($filePath)
                            ->resize(600, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })
                            ->encode('jpg', 80);
            
                        // Save back to SAME location
                        $compressedImage->save($filePath);
            
                        \Log::info("Compressed: " . $filePath);
            
                    } catch (\Exception $e) {
            
                        \Log::error(
                            "Compression failed for: "
                            . $filePath
                            . ' | Error: '
                            . $e->getMessage()
                        );
                    }
                }
            
                return redirect('/')
                    ->with('message', 'Folder compressed successfully!');
            }
                 
            public function imageDelete(Request $request)
          {
    
                $id = $request->delete_image;
                
                $admission = Admission::find($id);
                // dd($admission);
                 if (!$admission) {
        
                        return redirect()->to('admissionView')->with('error', 'Admission not found!');
                    }
                if (File::exists(env('IMAGE_UPLOAD_PATH') . 'profile/' . $admission->image)) {
                    File::delete(env('IMAGE_UPLOAD_PATH') . 'profile/' . $admission->image);
                }
            
                $admission->image = null;
                $admission->save();
            
            
                return redirect()->to('admissionView')->with('message','image deleted successfully!');
            }
        public function login_credential_reports(Request $request)
        {
            $search = [
                'course' => $request->course,
                'batch' => $request->batch,
                'class_type_id' => $request->class_type_id,
                'admissionNo' => $request->admissionNo,
                'name' => $request->name,
                'status' => $request->status,
                'session_id' => $request->session_id ?? '',
            ];
            $baseQuery = Admission::where('admissions.branch_id', Session::get('branch_id'))
                ->where('admissions.status', '1');
                
            if (!empty($search['session_id'])) {
                $baseQuery->where('admissions.session_id', $search['session_id']);
            }
            if (!empty($search['course'])) {
                $courseName = $search['course'];
                $courseObj = \App\Models\Master\Course::where('name', $courseName)->first();
                $courseClassIds = [];
                if ($courseObj) {
                    $courseClassIds = ClassType::where('course_id', $courseObj->id)->pluck('id')->toArray();
                }
                $baseQuery->where(function($q) use ($courseName, $courseClassIds) {
                    $q->where('admissions.course', $courseName);
                    if (!empty($courseClassIds)) {
                        $q->orWhereIn('admissions.class_type_id', $courseClassIds);
                    }
                });
            }
            if (!empty($search['batch'])) {
                $baseQuery->where('admissions.batch', $search['batch']);
            }
            if (!empty($search['class_type_id'])) {
                $baseQuery->where('admissions.class_type_id', $search['class_type_id']);
            }
            if (!empty($search['admissionNo'])) {
                $baseQuery->where('admissions.admissionNo', 'like', '%' . $search['admissionNo'] . '%');
            }
            if (!empty($search['name'])) {
                $value = $search['name'];
                $baseQuery->where(function($q) use ($value) {
                    $q->where('admissions.first_name', 'like', '%' . $value . '%')
                      ->orWhere('admissions.last_name', 'like', '%' . $value . '%')
                      ->orWhere('admissions.father_name', 'like', '%' . $value . '%')
                      ->orWhere('admissions.mobile', 'like', '%' . $value . '%')
                      ->orWhere('admissions.userName', 'like', '%' . $value . '%')
                      ->orWhere('admissions.email', 'like', '%' . $value . '%');
                });
            }

            // Calculate overall metrics from baseQuery matching filters (before status filter)
            $totalStudents = (clone $baseQuery)->count();
            $generatedCount = (clone $baseQuery)->whereNotNull('admissions.userName')->where('admissions.userName', '!=', '')->count();
            $pendingCount = $totalStudents - $generatedCount;

            // Apply status filter to the query
            if (!empty($search['status'])) {
                if ($search['status'] == 'generated' || $search['status'] === '1') {
                    $baseQuery->whereNotNull('admissions.userName')->where('admissions.userName', '!=', '');
                } elseif ($search['status'] == 'pending' || $search['status'] === '0') {
                    $baseQuery->where(function($q) {
                        $q->whereNull('admissions.userName')->orWhere('admissions.userName', '');
                    });
                }
            }

            $perPage = $request->get('per_page', 25);
            if ($perPage === 'all' || $perPage == -1) {
                $perPage = 1000;
            } else {
                $perPage = (int) $perPage;
                if ($perPage <= 0) $perPage = 25;
            }

            $query = $baseQuery->select('admissions.*', 'class.name as class_name')
                ->leftJoin('class_types as class', 'class.id', '=', 'admissions.class_type_id')
                ->orderBy('admissions.first_name', 'ASC');

            $data = $query->paginate($perPage)->appends($request->all());

            if ($request->ajax()) {
                $tbodyHtml = view('students.login_credential.table_rows', [
                    'data' => $data,
                    'search' => $search
                ])->render();

                $paginationHtml = view('students.login_credential.pagination', [
                    'data' => $data
                ])->render();

                return response()->json([
                    'status' => 'success',
                    'html' => $tbodyHtml,
                    'pagination' => $paginationHtml,
                    'total' => $data->total(),
                    'from' => $data->firstItem() ?? 0,
                    'to' => $data->lastItem() ?? 0,
                    'count' => $data->count(),
                    'total_students' => $totalStudents,
                    'generated_count' => $generatedCount,
                    'pending_count' => $pendingCount
                ]);
            }

            return view('students.login_credential.view', [
                'search' => $search, 
                'data' => $data,
                'totalStudents' => $totalStudents,
                'generatedCount' => $generatedCount,
                'pendingCount' => $pendingCount
            ]);
        }

        public function studentUserNameCreate(Request $request)
        {
            if (empty($request->student_ids)) {
                return back()->with('error', 'No students selected.');
            }
        
            $ids = array_filter(explode(',', $request->student_ids));
        
            if (empty($ids)) {
                return back()->with('error', 'Invalid student selection.');
            }
        
            $usernameOrder = !empty($request->username_order) ? explode(',', $request->username_order) : [];
            $passwordOrder = !empty($request->password_order) ? explode(',', $request->password_order) : [];
        
            $nameLetters = $request->name_letters ?? 4;
            $mobileDigits = $request->mobile_digits ?? 4;
        
            $students = Admission::select('admissions.*', 'class.name as class_name')
                ->leftJoin('class_types as class', 'class.id', '=', 'admissions.class_type_id')
                ->whereIn('admissions.id', $ids)
                ->where('admissions.branch_id', Session::get('branch_id'))
                ->get();
        
            foreach ($students as $student) {
                $username = '';
                $password = '';
            
                // 1. Username
                if (!empty($usernameOrder)) {
                    foreach ($usernameOrder as $key) {
                        if (!empty($key)) {
                            $username .= $this->getFieldValue($student, $key, $nameLetters, $mobileDigits);
                        }
                    }
                }
                if (empty($username)) {
                    $username = 'stu_' . ($student->admissionNo ?? $student->id);
                }
            
                // 2. Password Logic
                if (!empty($request->custom_password)) {
                    $password = trim($request->custom_password);
                } elseif (!empty($passwordOrder)) {
                    foreach ($passwordOrder as $key) {
                        if (!empty($key)) {
                            $password .= $this->getFieldValue($student, $key, $nameLetters, $mobileDigits);
                        }
                    }
                    if (empty($password)) {
                        $password = '12345678';
                    }
                } else {
                    $password = '12345678';
                }
            
                $student->userName = $username;
                $student->password = Hash::make($password);
                $student->confirm_password = $password;
                $student->save();
            }
            
            return back()->with('message', count($students) . ' student credentials generated successfully!');
        }

        public function updateSingleStudentCredential(Request $request)
        {
            $request->validate([
                'student_id' => 'required',
                'username' => 'required',
                'password' => 'required|min:4',
            ]);

            $student = Admission::where('branch_id', Session::get('branch_id'))
                ->find($request->student_id);

            if (!$student) {
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => 'Student not found.']);
                }
                return back()->with('error', 'Student not found.');
            }

            // Check if username is already taken by another student in this branch
            $existing = Admission::where('branch_id', Session::get('branch_id'))
                ->where('userName', $request->username)
                ->where('id', '!=', $student->id)
                ->first();

            if ($existing) {
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => 'Username is already in use by another student.']);
                }
                return back()->with('error', 'Username is already in use by another student.');
            }

            $password = trim($request->password);
            $student->userName = trim($request->username);
            $student->password = Hash::make($password);
            $student->confirm_password = $password;
            $student->save();

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Credentials updated successfully for ' . $student->first_name . ' ' . ($student->last_name ?? '') . '!',
                    'data' => [
                        'id' => $student->id,
                        'userName' => $student->userName,
                        'confirm_password' => $student->confirm_password
                    ]
                ]);
            }

            return back()->with('message', 'Credentials updated successfully for ' . $student->first_name . ' ' . ($student->last_name ?? '') . '!');
        }
            
            
        private function getFieldValue($student, $key, $nameLetters = 4, $mobileDigits = 4)
        {
            switch ($key) {
                case 'name':
                    return substr(strtolower(preg_replace('/\s+/', '', $student->first_name)), 0, $nameLetters);
                case 'mobile':
                    return substr(preg_replace('/\D/', '', $student->mobile), -$mobileDigits);
                case 'dob':
                    if (!$student->dob) return '';
                    $dob = date('dmy', strtotime($student->dob)); //e.g. 150805
                    return $dob;
                case 'admission_no':
                    return $student->admissionNo;
                case 'class':
                    return strtolower(preg_replace('/\s+/', '', $student->class_name));
                default:
                    return '';
            }
        }

        public function getClassesByCourse(Request $request)
        {
            $course_name = $request->course;
            $course_id = $request->course_id;
            $batch = $request->batch;
            $branch_id = Session::get('branch_id') ?? Session::get('admin_branch_id');

            $session_id = null;
            if (!empty($batch)) {
                $sessionObj = Sessions::where('from_year', $batch)->orWhere('from_year', 'like', $batch . '%')->first();
                if ($sessionObj) {
                    $session_id = $sessionObj->id;
                }
            }
            if (empty($session_id) && !$request->has('ignore_session')) {
                $session_id = $request->session_id ?: Session::get('session_id');
            }

            $query = ClassType::query();
            if (!empty($branch_id)) {
                $query->where('branch_id', $branch_id);
            }
            if (!empty($session_id)) {
                $query->where('session_id', $session_id);
            }

            if (!empty($course_id)) {
                if (is_numeric($course_id)) {
                    $query->where(function($q) use ($course_id) {
                        $q->where('course_id', $course_id);
                        $courseObj = \App\Models\Master\Course::find($course_id);
                        if ($courseObj) {
                            $q->orWhere('name', 'LIKE', $courseObj->name . ' %')
                              ->orWhere('name', $courseObj->name);
                        }
                    });
                } else {
                    $query->where(function($q) use ($course_id) {
                        $q->where('name', 'LIKE', $course_id . ' %')
                          ->orWhere('name', $course_id);
                        $courseObj = \App\Models\Master\Course::where('name', $course_id)->first();
                        if ($courseObj) {
                            $q->orWhere('course_id', $courseObj->id);
                        }
                    });
                }
            } elseif (!empty($course_name)) {
                $courseQuery = \App\Models\Master\Course::where('name', $course_name);
                if (!empty($branch_id)) {
                    $courseQuery->where('branch_id', $branch_id);
                }
                $course = $courseQuery->first();
                if ($course) {
                    $query->where(function($q) use ($course, $course_name) {
                        $q->where('course_id', $course->id)
                          ->orWhere('name', 'LIKE', $course_name . ' %')
                          ->orWhere('name', $course_name);
                    });
                } else {
                    $query->where(function($q) use ($course_name) {
                        $q->where('name', 'LIKE', $course_name . ' %')
                          ->orWhere('name', $course_name);
                    });
                }
            }

            $classes = $query->orderBy('orderBy', 'ASC')->orderBy('id', 'ASC')->get();
            $classes = $classes->unique('name')->values();
            return response()->json($classes);
        }

        public function sendStudentPushNotification(Request $request)
        {
            $request->validate([
                'admission_ids' => 'required|array',
                'title' => 'required|string|max:150',
                'message' => 'required|string|max:1000',
            ]);

            $admissionIds = $request->admission_ids;
            $title = $request->title;
            $message = $request->message;

            $response = Helper::sendNotification(
                $title,
                $message,
                'student',
                $admissionIds,
                null,
                null,
                [
                    'notification_type' => 'student_notification',
                    'title' => $title,
                    'body' => $message
                ],
                'high'
            );

            return response()->json([
                'status' => true,
                'message' => 'Push notifications sent successfully!',
                'result' => $response
            ]);
        }

        /**
         * Calculate the next real-time admission number for a selected course.
         */
        public static function generateNextAdmissionNo($courseName, $courseId = null)
        {
            if (empty($courseName) && empty($courseId)) {
                return '';
            }

            $classTypeIds = [];
            if (!empty($courseId)) {
                $classTypeIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();
                if (empty($courseName)) {
                    $courseObj = \App\Models\Master\Course::find($courseId);
                    if ($courseObj) {
                        $courseName = $courseObj->name;
                    }
                }
            } elseif (!empty($courseName)) {
                $courseObj = \App\Models\Master\Course::where('name', $courseName)->first();
                if ($courseObj) {
                    $courseId = $courseObj->id;
                    $classTypeIds = ClassType::where('course_id', $courseId)->pluck('id')->toArray();
                }
            }

            $query = Admission::query()
                ->whereNotNull('admissionNo')
                ->where('admissionNo', '!=', '');

            $query->where(function ($q) use ($courseName, $classTypeIds) {
                $hasCondition = false;
                if (!empty($courseName)) {
                    $q->where('course', $courseName)
                      ->orWhere('course', 'like', $courseName . '%');
                    $hasCondition = true;
                }
                if (!empty($classTypeIds)) {
                    $q->orWhereIn('class_type_id', $classTypeIds);
                    $hasCondition = true;
                }
                if (!$hasCondition) {
                    $q->whereRaw('1 = 0');
                }
            });

            $admissionNos = $query->pluck('admissionNo');

            $prefixCounts = [];
            $numbersByPrefix = [];

            foreach ($admissionNos as $admNo) {
                $admNo = trim($admNo);
                if (preg_match('/^([a-zA-Z0-9\.\s_-]+[\/\-_])(\d+)$/', $admNo, $matches)) {
                    $prefix = strtoupper($matches[1]);
                    $num = (int)$matches[2];

                    $prefixCounts[$prefix] = ($prefixCounts[$prefix] ?? 0) + 1;
                    if (!isset($numbersByPrefix[$prefix]) || $num > $numbersByPrefix[$prefix]) {
                        $numbersByPrefix[$prefix] = $num;
                    }
                } elseif (preg_match('/^([a-zA-Z\.\s_-]+)(\d+)$/', $admNo, $matches)) {
                    $prefix = strtoupper($matches[1]) . '/';
                    $num = (int)$matches[2];

                    $prefixCounts[$prefix] = ($prefixCounts[$prefix] ?? 0) + 1;
                    if (!isset($numbersByPrefix[$prefix]) || $num > $numbersByPrefix[$prefix]) {
                        $numbersByPrefix[$prefix] = $num;
                    }
                }
            }

            if (!empty($prefixCounts)) {
                arsort($prefixCounts);
                $dominantPrefix = array_key_first($prefixCounts);
                $maxNum = $numbersByPrefix[$dominantPrefix] ?? 0;
                $cleanPrefix = rtrim($dominantPrefix, '/') . '/';
                return $cleanPrefix . ($maxNum + 1);
            }

            $cleanCourse = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$courseName));
            if (empty($cleanCourse)) {
                $cleanCourse = 'ADM';
            }
            return $cleanCourse . '/1';
        }

        /**
         * AJAX endpoint to retrieve the next admission number dynamically.
         */
        public function getNextAdmissionNo(Request $request)
        {
            $courseName = $request->course;
            $courseId = $request->course_id;

            if (empty($courseName) && empty($courseId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course is required',
                    'admissionNo' => ''
                ]);
            }

            $admissionNo = self::generateNextAdmissionNo($courseName, $courseId);

            return response()->json([
                'status' => 'success',
                'admissionNo' => $admissionNo,
                'course' => $courseName
            ]);
        }

    }
