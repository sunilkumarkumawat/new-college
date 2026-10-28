<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use App\Models\User;
use App\Models\Setting;
use App\Models\CcForm;
use App\Models\Student;
use App\Models\Editor;
use App\Models\Admission;
use App\Models\EventeCertificate;
use App\Models\SportCertificate;
use App\Models\TcCertificate;
use App\Models\Master\MessageTemplate;
use App\Models\Master\MessageType;
use App\Models\Master\Branch;
use Session;
use Carbon\Carbon;
use Hash;
use Helper;
use Redirect;
use Str;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF;

class CertificateController extends Controller

{
            public function certificate_dashboard(Request $request){
                return view('certificate/certificate_dashboard');
            }
            
            public function certificateEditor(Request $request){
                if($request->isMethod('Post')){
                    $data = new Editor;
                    $data->editor_code = $request->editor_code;
                    $data->name = $request->name;
                    $data->save();
                    return $data;
                }
                return view('certificate.editor');
            }

            public function add(Request $request){
                if ($request->isMethod('post')) {
                    $addcharacter = new CcForm; //model name
                    $addcharacter->user_id = Session::get('id');
                    $addcharacter->session_id = Session::get('session_id');
                    $addcharacter->branch_id = Session::get('branch_id');
                    $addcharacter->class_name = $request->class_name;
                    $addcharacter->admissionNo = $request->admissionNo;
                    $addcharacter->admission_id = $request->admission_id;
                    $addcharacter->iessu_date = $request->iessu_date;
                    $addcharacter->achievement_for = $request->achievement_for;
                    $addcharacter->save();
                    $template = MessageTemplate::Select('message_templates.*', 'message_types.slug')
                    ->leftjoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                    ->where('message_types.status', 1)->where('message_types.slug', 'student-certificate')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('branch_id', Session::get('branch_id'))->first();
                    $studentdetail = Admission::where('id', $request->admission_id)->get()->first();
                    $arrey1 =   array(
                        '{#school_name#}',
                        '{#name#}'
                    );
                    $arrey2 = array(
                        $setting->name,
                        $studentdetail->first_name . " " . $studentdetail->last_name
                    );
                    if ($template->status != 1) {
                        if ($studentdetail->email != "") {
                            if ($branch->email_srvc != 0) {
                                if ($template->email_status != 0) {
                                    $message = str_replace($arrey1, $arrey2, $template->email_content);
                                    $emailData = ['email' => $studentdetail->email, 'data' => $message, 'subject' => $template->title];
                                    Helper::sendMail('email_print.template_print', $emailData);
                                }
                            }
                        }
                        if ($branch->whatsapp_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->whatsapp_status != 0) {
                                    $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content);
                                    Helper::sendWhatsappMessage($studentdetail->mobile, $whatsapp);
                                }
                            }
                        }
                        if ($branch->sms_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->sms_status != 0) {
                                    $sms = str_replace($arrey1, $arrey2, $template->sms_content);
                                    Helper::SendMessage($studentdetail->mobile, $sms);
                                }
                            }
                        }
                    }
                    return redirect::to('cc/form/index')->with('message', 'CC Form add Successfully.');
                }
                return view('certificate.cc_form.add');
            }

            public function ccFormIndex(Request $request){
                $search['class_type_id'] = $request->class_type_id;
                $search['admissionNo'] = $request->admissionNo;
                $data = CcForm::select('c_certificates_form.*', 'admissions.first_name', 'admissions.last_name', 'admissions.father_name as father_names')
                ->leftjoin('admissions', 'admissions.id', 'c_certificates_form.admission_id')
                ->where('c_certificates_form.session_id', Session::get('session_id'))
                ->where('c_certificates_form.branch_id', Session::get('branch_id'));
                if (Session::get('role_id') > 1) {
                    $data = $data->where('c_certificates_form.branch_id', Session::get('branch_id'));
                }
                if ($request->isMethod('post')) {
                    if (!empty($request->admissionNo)) {
                        $data = $data->where('c_certificates_form.admission_id', $request->admissionNo);
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where('admissions.class_type_id', $request->class_type_id);
                    }
                }
                $data = $data->orderBy('c_certificates_form.id', 'DESC')->get();
                return view('certificate.cc_form.index', ['data' => $data, 'search' => $search]);
            }

            /*public function ccPrint(Request $request, $id){
                $cc_certificate_pr = CcForm::select('c_certificates_form.*', 'admissions.class_type_id','admissions.first_name', 'admissions.last_name', 'admissions.father_name as father_names')
                ->leftjoin('admissions', 'admissions.id', 'c_certificates_form.admission_id')
                ->where('c_certificates_form.session_id', Session::get('session_id'))
                ->where('c_certificates_form.id', $id)->get()->first();
                $class_type = 1;
                if(!empty($cc_certificate_pr)){
                    $class_type  = $cc_certificate_pr->class_type_id;
                }
                if($class_type < 4){
                    return view('print_file.certificate.cc_print_younger', ['certificate_data' => $cc_certificate_pr]);
                }
                else{
                    return view('print_file.certificate.cc_print_elder', ['certificate_data' => $cc_certificate_pr]);
                }
            }*/

            public function ccPrint(Request $request, $id){
                $student = Admission::with(['ClassTypes', 'Gender', 'Branch'])->find($id);
                if (!$student) {
                    $student = Admission::with(['ClassTypes', 'Gender', 'Branch'])
                        ->where('unique_system_id', (string)$id)
                        ->first();
                }

                $cc_form = null;
                if ($student) {
                    $cc_form = CcForm::where('admission_id', $student->id)->latest()->first();
                } else {
                    $cc_form = CcForm::find($id);
                    if ($cc_form && !empty($cc_form->admission_id)) {
                        $student = Admission::with(['ClassTypes', 'Gender', 'Branch'])->find($cc_form->admission_id);
                    }
                }

                if (!$student) {
                    return redirect()->back()->with('error', 'Student record not found for Character Certificate.');
                }

                return view('print_file.certificate.character_certificate_a5', [
                    'student' => $student,
                    'cc_form' => $cc_form
                ]);
            }

            public function formEdit(Request $request, $id){
                $data = CcForm::select('c_certificates_form.*', 'admissions.admissionNo','admissions.father_name as stu_father_name', 'admissions.first_name as stu_first_name', 'admissions.last_name as stu_last_name')
                ->leftjoin('admissions', 'admissions.id', 'c_certificates_form.admission_id')
                ->where('c_certificates_form.id', $id)->first();
                if ($request->isMethod('post')) {
                    $request->validate([
                        'admission_id' => 'required',
                        //'father_name' => 'required',
                        'iessu_date' => 'required',
                    ]);
                    $ccform = CcForm::find($id);
                    //  $ccform->user_id = Session::get('id');
                    // $ccform->session_id = Session::get('session_id');
                    // $ccform->branch_id = Session::get('branch_id');
                    // $ccform->name = $request->name;
                    // $ccform->father_name = $request->father_name;
                    $ccform->class_name = $request->class_name;
                    $ccform->admission_id = $request->admission_id;
                    $ccform->achievement_for = $request->achievement_for;
                    $ccform->iessu_date = $request->iessu_date;
                    $ccform->save();
                    return redirect::to('cc/form/index')->with('message', 'CC Form  Update Successfully.');
                }
                return view('certificate.cc_form.edit', ['data' => $data]);
            }

            public function delete(Request $request){
                $id = $request->delete_id;
                $students = CcForm::find($id)->delete();
                return redirect::to('cc/form/index')->with('message', 'CC Form Delete Successfully.');
            }

            public function eventeCertificateAdd(Request $request){
                // dd($request);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name' => 'required',
                        // 'organized_date' => 'required',
                    ]);
                    // dd($request);
                    $addevente = new EventeCertificate; //model name
                    $addevente->user_id = Session::get('id');
                    $addevente->session_id = Session::get('session_id');
                    $addevente->branch_id = Session::get('branch_id');
                    $addevente->name = $request->name;
                    $addevente->admission_id = $request->admission_id;
                    //$addevente->admission_id= $request->registration_no;
                    $addevente->class_type_id = $request->class_type_id;
                    $addevente->father_mobile = $request->father_mobile;
                    $addevente->father_name = $request->father_name;
                    $addevente->event_type = $request->event_type;
                    $addevente->organized_date = $request->organized_date;
                    $addevente->rank = $request->rank;
                    $addevente->save();
                    $template =  MessageTemplate::Select('message_templates.*', 'message_types.slug')
                    ->leftjoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                    ->where('message_types.status', 1)->where('message_types.slug', 'student-event')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('branch_id', Session::get('branch_id'))->first();
                    $studentdetail = Admission::where('id', $request->admission_id)->get()->first();
                    $arrey1 =   array(
                        '{#school_name#}',
                        '{#name#}',
                        '{#event_type#}',
                        '{#organized_date#}'
                    );
                    $arrey2 = array(
                        $setting->name,
                        $studentdetail->first_name . " " . $studentdetail->last_name,
                        $request->event_type,
                        $request->organized_date
                    );
                    /*if ($template->status != 1) {
                        if ($studentdetail->email != "") {
                            if ($branch->email_srvc != 0) {
                                if ($template->email_status != 0) {
                                    $message = str_replace($arrey1, $arrey2, $template->email_content);
                                    $emailData = ['email' => $studentdetail->email, 'data' => $message, 'subject' => $template->title];
                                    Helper::sendMail('email_print.template_print', $emailData);
                                }
                            }
                        }
                        if ($branch->whatsapp_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->whatsapp_status != 0) {
                                    $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content);
                                    Helper::sendWhatsappMessage($studentdetail->mobile, $whatsapp);
                                }
                            }
                        }
                        if ($branch->sms_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->sms_status != 0) {
                                    $sms = str_replace($arrey1, $arrey2, $template->sms_content);
                                    Helper::SendMessage($studentdetail->mobile, $sms);
                                }
                            }
                        }
                    }*/
                    return redirect::to('evente/certificate/index')->with('message', 'Evente Certificate Add Successfully.');
                }
                return view('certificate.evente_certificate.add');
            }
            
            public function sportAdd(Request $request){
                // dd($request);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'organized_date' => 'required',
                    ]);
                    //dd($request);
                    $addsport = new SportCertificate; //model name
                    $addsport->user_id = Session::get('id');
                    $addsport->session_id = Session::get('session_id');
                    $addsport->branch_id = Session::get('branch_id');
                    $addsport->name = $request->name;
                    $addsport->admission_id = $request->admission_id;
                    $addsport->class_type_id = $request->class_type_id;
                    $addsport->father_mobile = $request->father_mobile;
                    $addsport->father_name = $request->father_name;
                    $addsport->event_type = $request->event_type;
                    $addsport->organized_date = $request->organized_date;
                    $addsport->rank = $request->rank;
                    $addsport->save();
                    $template =  MessageTemplate::Select('message_templates.*', 'message_types.slug')
                    ->leftjoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                    ->where('message_types.status', 1)->where('message_types.slug', 'studentsport')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('branch_id', Session::get('branch_id'))->first();
                    $studentdetail = Admission::where('id', $request->admission_id)->get()->first();
                    $arrey1 =   array(
                        '{#school_name#}',
                        '{#name#}',
                        '{#sports_name#}',
                        '{#event_date#}'
                    );
                    $arrey2 = array(
                        $setting->name,
                        $studentdetail->first_name . " " . $studentdetail->last_name,
                        $request->event_type,
                        date('d-m-Y', strtotime($request->organized_date))
                    );
                    if ($template->status != 1) {
                        if ($studentdetail->email != "") {
                            if ($branch->email_srvc != 0) {
                                if ($template->email_status != 0) {
                                    $message = str_replace($arrey1, $arrey2, $template->email_content);
                                    $emailData = ['email' => $studentdetail->email, 'data' => $message, 'subject' => $template->title];
                                    Helper::sendMail('email_print.template_print', $emailData);
                                }
                            }
                        }
                        if ($branch->whatsapp_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->whatsapp_status != 0) {
                                    $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content);
                                    Helper::sendWhatsappMessage($studentdetail->mobile, $whatsapp);
                                }
                            }
                        }
                        if ($branch->sms_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->sms_status != 0) {
                                    $sms = str_replace($arrey1, $arrey2, $template->sms_content);
                                    Helper::SendMessage($studentdetail->mobile, $sms);
                                }
                            }
                        }
                    }
                    return redirect::to('sport/certificate/add')->with('message', 'Sport Certificate Added Successfully.');
                }
                return view('certificate.sports.add');
            }

            public function certificateIndex(Request $request){
                $search['class_type_id'] = $request->class_type_id;
                $search['admissionNo'] = $request->admissionNo;
                $data = EventeCertificate::select('evente_certificates.*', 'class_types.name as class_name', 'admissions.admissionNo', 'admissions.first_name', 'admissions.last_name', 'admissions.father_name as father_names')
                ->leftJoin('admissions', 'admissions.id', '=', 'evente_certificates.admission_id')
                ->leftJoin('class_types', 'class_types.id', '=', 'evente_certificates.class_type_id')
                ->where('evente_certificates.session_id', Session::get('session_id'))
                ->where('evente_certificates.branch_id', Session::get('branch_id'))
                ->where('evente_certificates.deleted_at', null);
                if (Session::get('role_id') > 1) {
                    $data = $data->where('evente_certificates.branch_id', Session::get('branch_id'));
                }
                if ($request->isMethod('post')) {
                    if (!empty($request->admissionNo)) {
                        $data = $data->where('evente_certificates.admission_id', $request->admissionNo);
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where('evente_certificates.class_type_id', $request->class_type_id);
                    }
                }
                $data = $data->orderBy('evente_certificates.id', 'DESC')->get();
                return view('certificate.evente_certificate.index', ['data' => $data, 'search' => $search]);
            }
            
            public function sportndex(Request $request){
                $search['class_type_id'] = $request->class_type_id;
                $search['admissionNo'] = $request->admissionNo;
                $data = SportCertificate::select('sports_certificates.*', 'class_types.name as class_name', 'admissions.admissionNo', 'admissions.first_name', 'admissions.last_name', 'admissions.father_name as father_names')
                ->leftJoin('admissions', 'admissions.id', '=', 'sports_certificates.admission_id')
                ->leftJoin('class_types', 'class_types.id', '=', 'sports_certificates.class_type_id')
                ->where('sports_certificates.session_id', Session::get('session_id'))
                ->where('sports_certificates.branch_id', Session::get('branch_id'))
                ->where('sports_certificates.deleted_at', null);
                if (Session::get('role_id') > 1) {
                    $data = $data->where('sports_certificates.branch_id', Session::get('branch_id'));
                }
                if ($request->isMethod('post')) {
                    if (!empty($request->admissionNo)) {
                        $data = $data->where('sports_certificates.admission_id', $request->admissionNo);
                    }
                    if (!empty($request->class_type_id)) {
                        $data = $data->where('sports_certificates.class_type_id', $request->class_type_id);
                    }
                }
                $data = $data->orderBy('sports_certificates.id', 'DESC')->get();
                //  $addevente =  EventeCertificate::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->orderBy('id', 'DESC')->get();
                return view('certificate.sports.index', ['data' => $data, 'search' => $search]);
            }

            public function sportEdit(Request $request, $id){
                $data = SportCertificate::select('sports_certificates.*', 'admissions.admissionNo','admissions.father_name as stu_father_name', 'admissions.first_name as stu_name')
                ->leftjoin('admissions', 'admissions.id', 'sports_certificates.admission_id')
                ->where('sports_certificates.id', $id)->first();
                // $data = SportCertificate::find($id);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name' => 'required',
                    ]);
                    $data->name = $request->name;
                    /*	$data->student_roll_no= $request->student_roll_no;*/
                    $data->event_type = $request->event_type;
                    $data->organized_date = $request->organized_date;
                    $data->rank = $request->rank;
                    $data->save();
                    return redirect::to('sport/certificate/index')->with('message', 'Sport Certificate Update Successfully.');
                }
                return view('certificate.sports.edit', ['data' => $data]);
            }

            public function eventePrint(Request $request, $id){
                $evente_certificates = EventeCertificate::Select('evente_certificates.*', 'class_types.name as class_name', 'admissions.father_name as father_names', 'admissions.dob','sessions.from_year', 'sessions.to_year', 'admissions.mother_name', 'admissions.address', 'admissions.image', 'admissions.admissionNo', 'admissions.first_name', 'admissions.last_name')
                ->leftjoin('admissions', 'admissions.id', 'evente_certificates.admission_id')
                ->leftjoin('sessions', 'sessions.id', 'evente_certificates.session_id')
                ->leftjoin('class_types as class_types', 'class_types.id', 'evente_certificates.class_type_id')->where('evente_certificates.id', $id)->get()->first();
                //dd($evente_certificates); 
                return view('master.printFilePanel.CertificateManagement.template02', ['data' => $evente_certificates]);
                // return view('print_file.certificate.evente_print', ['data' => $evente_certificates]);
            }

            public function certificateEdit(Request $request, $id){
                $data = EventeCertificate::select('evente_certificates.*', 'admissions.admissionNo','admissions.father_name as stu_father_name', 'admissions.first_name as stu_name')
                ->leftjoin('admissions', 'admissions.id', 'evente_certificates.admission_id')
                ->where('evente_certificates.id', $id)->first();
                // $data = EventeCertificate::find($id);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name' => 'required',
                    ]);
                    $data->name = $request->name;
                    /*	$data->student_roll_no= $request->student_roll_no;*/
                    $data->event_type = $request->event_type;
                    $data->organized_date = $request->organized_date;
                    $data->rank = $request->rank;
                    $data->save();
                    return redirect::to('evente/certificate/index')->with('message', 'Evente Certificate Update Successfully.');
                }
                return view('certificate.evente_certificate.edit', ['data' => $data]);
            }

            public function evente_delete(Request $request){
                $id = $request->delete_id;
                $evente = EventeCertificate::find($id)->delete();
                return redirect::to('evente/certificate/index')->with('message', 'Evente Certificate Delete Successfully.');
            }
            
            public function sport_delete(Request $request){
                $id = $request->delete_id;
                $evente = SportCertificate::find($id)->delete();
                return redirect::to('sport/certificate/index')->with('message', 'Sport Certificate Delete Successfully.');
            }

            public function sportPrint(Request $request, $id){
                $sport_certificate_pr =  SportCertificate::Select('sports_certificates.*', 'class_types.name as class_name', 'admissions.first_name as stu_first_name', 'admissions.last_name as stu_last_name', 'admissions.mother_name', 'admissions.address', 'admissions.image')
                ->leftjoin('admissions', 'admissions.id', 'sports_certificates.admission_id')
                ->leftjoin('class_types as class_types', 'class_types.id', 'sports_certificates.class_type_id')->where('sports_certificates.id', $id)->get()->first();
                //   $pdf = PDF::loadView('master.printFilePanel.CertificateManagement.template07', ['data'=>$sport_certificate_pr]);
                //      $pdf->setPaper('A4', 'landscape');
                //     return $pdf->download('CertificateManagement.pdf');
                $printPreview = Helper::printPreview('Sports certificate');
                return view($printPreview, ['data' => $sport_certificate_pr]);
                return view('master.printFilePanel.CertificateManagement.template03', ['data' => $sport_certificate_pr]);
            }

            public function tcCertificateAdd(Request $request){
                //  dd($request);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name' => 'required',
                        'class_type_id' => 'required',
                        'taken_result' => 'required',
                        'father_name' => 'required',
                        'mother_name' => 'required',
                        'dob' => 'required',
                        // 'students_admission_no' => 'required',
                        // 'admission_date' => 'required',
                        'issue_date' => 'required',
                    ]);
                    //dd($request);
                    $addtc_certificate = new TcCertificate; //model name
                    $addtc_certificate->user_id = Session::get('id');
                    $addtc_certificate->session_id = Session::get('session_id');
                    $addtc_certificate->branch_id = Session::get('branch_id');
                    $addtc_certificate->name = $request->name;
                    $addtc_certificate->student_class = $request->class_type_id;
                    $addtc_certificate->admission_id = $request->admission_id;
                    $addtc_certificate->taken_result = $request->taken_result;
                    $addtc_certificate->father_name = $request->father_name;
                    $addtc_certificate->father_mobile = $request->father_mobile;
                    $addtc_certificate->mother_name = $request->mother_name;
                    $addtc_certificate->dob = $request->dob;
                    $addtc_certificate->students_admission_no = $request->students_admission_no;
                    $addtc_certificate->fail_pass = $request->fail_pass;
                    $addtc_certificate->subject = $request->subjects_studied;
                    $addtc_certificate->paid_school_dues = $request->paid_school_dues;
                    $addtc_certificate->higher_class = $request->higher_class;
                    $addtc_certificate->any_scholarship = $request->any_scholarship;
                    $addtc_certificate->behavior = $request->behavior;
                    $addtc_certificate->sports_certificate = $request->sports_certificate;
                    $addtc_certificate->reasons_leaving = $request->reasons_leaving;
                    $addtc_certificate->sssm_id_no = $request->sssm_id_no;
                    $addtc_certificate->any_remark = $request->any_remark;
                    $addtc_certificate->student_uid_no = $request->student_uid_no;
                    $addtc_certificate->admission_date = $request->admission_date;
                    $addtc_certificate->issue_date = $request->issue_date;
                    $addtc_certificate->due_any = $request->due_any;
                    $addtc_certificate->conduct_behaviour = $request->behavior;
                    $addtc_certificate->mudium = $request->mudium;
                    $addtc_certificate->save();
                    $template =  MessageTemplate::Select('message_templates.*', 'message_types.slug')
                    ->leftjoin('message_types', 'message_types.id', 'message_templates.message_type_id')
                    ->where('message_types.status', 1)->where('message_types.slug', 'student-Tc')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('branch_id', Session::get('branch_id'))->first();
                    $studentdetail = Admission::where('id', $request->admission_id)->get()->first();
                    $arrey1 =   array(
                        '{#school_name#}',
                        '{#name#}',
                        '{#contact_no#}'
                    );
                    $arrey2 = array(
                        $setting->name,
                        $studentdetail->first_name . " " . $studentdetail->last_name,
                        $setting->mobile
                    );
                    if ($template->status != 1) {
                        if ($studentdetail->email != "") {
                            if ($branch->email_srvc != 0) {
                                if ($template->email_status != 0) {
                                    $message = str_replace($arrey1, $arrey2, $template->email_content);
                                    $emailData = ['email' => $studentdetail->email, 'data' => $message, 'subject' => $template->title];
                                    Helper::sendMail('email_print.template_print', $emailData);
                                }
                            }
                        }
                        if ($branch->whatsapp_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->whatsapp_status != 0) {
                                    $whatsapp = str_replace($arrey1, $arrey2, $template->whatsapp_content);
                                    Helper::sendWhatsappMessage($studentdetail->mobile, $whatsapp);
                                }
                            }
                        }
                        if ($branch->sms_srvc != 0) {
                            if ($studentdetail->mobile != "") {
                                if ($template->sms_status != 0) {
                                    $sms = str_replace($arrey1, $arrey2, $template->sms_content);
                                    Helper::SendMessage($studentdetail->mobile, $sms);
                                }
                            }
                        }
                    }
                    return redirect::to('tc/certificate/index')->with('message', 'Tc Certificate Add Successfully.');
                }
                return view('certificate.tc_certificate.add');
            }

            public function tcIndex(Request $request){
                //dd($request);
                $search['student_class'] = $request->student_class;
                $search['admissionNo'] = $request->admissionNo;
                $data = TcCertificate::select('tc_certificates.*', 'class_types.name as class_name', 'admissions.admissionNo', 'admissions.first_name', 'admissions.last_name', 'admissions.father_name as father_names', 'admissions.mother_name as mother_names')
                ->leftJoin('admissions', 'admissions.id', '=', 'tc_certificates.admission_id')
                ->leftJoin('class_types', 'class_types.id', '=', 'tc_certificates.student_class')
                ->where('tc_certificates.session_id', Session::get('session_id'))
                ->where('tc_certificates.branch_id', Session::get('branch_id'))
                ->where('tc_certificates.deleted_at', null);
                if (Session::get('role_id') > 1) {
                    $data = $data->where('tc_certificates.branch_id', Session::get('branch_id'));
                }
                if ($request->isMethod('post')) {
                    if (!empty($request->admissionNo)) {
                        $data = $data->where('tc_certificates.admission_id', $request->admissionNo);
                    }
                    if (!empty($request->student_class)) {
                        $data = $data->where('tc_certificates.student_class', $request->student_class);
                    }
                }
                $data = $data->orderBy('tc_certificates.id', 'DESC')->get();
                // $addtc_certificate =  TcCertificate::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->get();
                return view('certificate.tc_certificate.index', ['data' => $data, 'search' => $search]);
            }

            public function tcPrint(Request $request, $id){
                $student = Admission::with(['ClassTypes', 'Gender', 'Branch'])->find($id);
                if (!$student) {
                    $student = Admission::with(['ClassTypes', 'Gender', 'Branch'])
                        ->where('unique_system_id', (string)$id)
                        ->first();
                }

                $tc_certificate_pr = null;
                if ($student) {
                    $tc_certificate_pr = TcCertificate::select('tc_certificates.*', 'class_types.name as ClassName', 'admissions.first_name as first_names', 'admissions.last_name', 'admissions.father_name as admissions_father_name', 'admissions.mother_name as admissions_mother_name')
                        ->leftjoin('admissions', 'admissions.id', 'tc_certificates.admission_id')
                        ->leftjoin('class_types', 'class_types.id', 'admissions.class_type_id')
                        ->where('tc_certificates.admission_id', $student->id)
                        ->latest('tc_certificates.id')
                        ->first();
                } else {
                    $tc_certificate_pr = TcCertificate::select('tc_certificates.*', 'class_types.name as ClassName', 'admissions.first_name as first_names', 'admissions.last_name', 'admissions.father_name as admissions_father_name', 'admissions.mother_name as admissions_mother_name')
                        ->leftjoin('admissions', 'admissions.id', 'tc_certificates.admission_id')
                        ->leftjoin('class_types', 'class_types.id', 'admissions.class_type_id')
                        ->where('tc_certificates.id', $id)
                        ->first();

                    if ($tc_certificate_pr && !empty($tc_certificate_pr->admission_id)) {
                        $student = Admission::with(['ClassTypes', 'Gender', 'Branch'])->find($tc_certificate_pr->admission_id);
                    }
                }

                if (!$student && !$tc_certificate_pr) {
                    return redirect()->back()->with('error', 'Student or TC record not found.');
                }

                return view('print_file.certificate.transfer_certificate_a4', [
                    'student' => $student,
                    'certificate_data' => $tc_certificate_pr
                ]);
            }
            
            public function tcPrintFormate(Request $request){
                return view('print_file.certificate.tc_print_formmate');
            }
            
            public function nocPrint(Request $request, $id){
                $data = TcCertificate::find($id);
                return view('certificate.tc_certificate.noc_print', ['data' => $data]);
            }

            public function tcEdit(Request $request, $id){
                $data = TcCertificate::select('tc_certificates.*', 'admissions.father_name as stu_father_name', 'admissions.first_name as stu_name', 'admissions.mother_name as mother_names')
                ->leftjoin('admissions', 'admissions.id', 'tc_certificates.admission_id')
                ->where('tc_certificates.id', $id)->first();
                // $data = TcCertificate::find($id);
                if ($request->isMethod('post')) {
                    $request->validate([
                        'name' => 'required',
                        'class_type_id' => 'required',
                        'taken_result' => 'required',
                        'father_name' => 'required',
                        'mother_name' => 'required',
                        'dob' => 'required',
                        // 'students_admission_no' => 'required',
                        // 'admission_date' => 'required',
                        'issue_date' => 'required',
                    ]);
                    $data->name = $request->name;
                    $data->student_class = $request->class_type_id;
                    $data->taken_result = $request->taken_result;
                    $data->father_name = $request->father_name;
                    $data->mother_name = $request->mother_name;
                    $data->dob = $request->dob;
                    $data->students_admission_no = $request->students_admission_no;
                    $data->fail_pass = $request->fail_pass;
                    $data->subject = $request->subjects_studied;
                    $data->paid_school_dues = $request->paid_school_dues;
                    $data->higher_class = $request->higher_class;
                    $data->any_scholarship = $request->any_scholarship;
                    $data->behavior = $request->behavior;
                    $data->sports_certificate = $request->sports_certificate;
                    $data->reasons_leaving = $request->reasons_leaving;
                    $data->sssm_id_no = $request->sssm_id_no;
                    $data->any_remark = $request->any_remark;
                    $data->student_uid_no = $request->student_uid_no;
                    $data->admission_date = $request->admission_date;
                    $data->admission_id = $request->admission_id;
                    $data->issue_date = $request->issue_date;
                    $data->due_any = $request->due_any;
                    $data->conduct_behaviour = $request->behavior;
                    $data->mudium = $request->mudium;
                    $data->save();
                    return redirect::to('tc/certificate/index')->with('message', 'Tc Certificate Update Successfully.');
                }
                return view('certificate.tc_certificate.edit', ['data' => $data]);
            }

            public function tc_delete(Request $request){
                $id = $request->delete_id;
                $evente = TcCertificate::find($id)->delete();
                return redirect::to('tc/certificate/index')->with('message', 'Tc Certificate Delete Successfully.');
            }

            public function CCFormStdData(Request $request){
                $class_type_id = $request->get('class_type_id');
                $data =  Student::with('ClassTypes')->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'));
                if (!empty($class_type_id)) {
                    $data = $data->where("class_type_id", $class_type_id);
                }
                if (!empty($student)) {
                    $data = $data->where("student_id", $student);
                }
                if (!empty($rigistration)) {
                    $data = $data->where("rigistration_no", $rigistration);
                }
            }

            public function certificateSearch(Request $request){
                $class_type_id = $request->get('class_type_id');
                $admissionNo = $request->get('admissionNo');
                if ($request->isMethod('post')) {
                    $data =  Admission::select('admissions.*', 'class_types.name as class_name')
                        ->leftJoin('class_types', 'class_types.id', 'admissions.class_type_id')
                        ->where('admissions.session_id', Session::get('session_id'))
                        ->where('admissions.branch_id', Session::get('branch_id'))
                        ->where('admissions.school', 1);
                    if (!empty($admissionNo)) {
                        $data = $data->where("admissions.admissionNo", $admissionNo);
                    }
                    if (!empty($class_type_id)) {
                        $data = $data->where("admissions.class_type_id", $class_type_id);
                    }
                    $allstudents = $data->orderBy('admissions.id', 'DESC')->get();
                }
                return  view('certificate.cc_form.search_certificate', ['data' => $allstudents]);
            }

            public function certificateAddClick(Request $request){
                $student_id = $request->get('student_id');
                $data =  Student::with('ClassTypes');
                if (!empty($student_id)) {
                    $data = $data->where("id", $student_id);
                }
                $students = $data->orderBy('id', 'DESC')->get();
                echo $students[0];
            }

            public function sportSearch(Request $request){
                $class_type_id = $request->get('class_type_id');
                $country_id = $request->get('country_id');
                $state_id = $request->get('state_id');
                $city_id = $request->get('city_id');
                $admissionNo = $request->get('admissionNo');
                $data =  Admission::with('ClassTypes')->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))->where('school', 1);
                if (!empty($admissionNo)) {
                    $data = $data->where("id", $admissionNo);
                }
                if (!empty($class_type_id)) {
                    $data = $data->where("class_type_id", $class_type_id);
                }
                if (!empty($country_id)) {
                    $data = $data->where("country_id", $country_id);
                }
                if (!empty($state_id)) {
                    $data = $data->where("state_id", $state_id);
                }
                if (!empty($city_id)) {
                    $data = $data->where("city_id", $city_id);
                }
                $allstudents = $data->orderBy('id', 'DESC')->get();
                return  view('certificate.sports.sports_search', ['data' => $allstudents]);
            }

            public function sportAddClick(Request $request){
                $student_id = $request->get('student_id');
                $data =  Student::with('ClassTypes')->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'));
                if (!empty($student_id)) {
                    $data = $data->where("id", $student_id);
                }
                $students = $data->orderBy('id', 'DESC')->get();
                echo $students[0];
            }

            public function eventeSearch(Request $request){
                $class_type_id = $request->get('class_type_id');
                $country_id = $request->get('country_id');
                $state_id = $request->get('state_id');
                $city_id = $request->get('city_id');
                $admissionNo = $request->get('admissionNo');
                $data =  Admission::with('ClassTypes')->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))->where('school', 1);
                if (!empty($admissionNo)) {
                    $data = $data->where("id", $admissionNo);
                }
                if (!empty($class_type_id)) {
                    $data = $data->where("class_type_id", $class_type_id);
                }
                if (!empty($country_id)) {
                    $data = $data->where("country_id", $country_id);
                }
                if (!empty($state_id)) {
                    $data = $data->where("state_id", $state_id);
                }
                if (!empty($city_id)) {
                    $data = $data->where("city_id", $city_id);
                }
                $allstudents = $data->orderBy('id', 'DESC')->get();
                return  view('certificate.evente_certificate.evente_search', ['data' => $allstudents]);
            }

            public function eventeAddClick(Request $request){
                $student_id = $request->get('student_id');
                $data =  Student::with('ClassTypes')->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'));
                if (!empty($student_id)) {
                    $data = $data->where("id", $student_id);
                }
                $students = $data->orderBy('id', 'DESC')->get();
                echo $students[0];
            }
            
            public function tcSearch(Request $request){
                $class_type_id = $request->get('class_type_id');
                $country_id = $request->get('country_id');
                $state_id = $request->get('state_id');
                $city_id = $request->get('city_id');
                $admissionNo = $request->get('admissionNo');
                if ($request->isMethod('post')) {
                    $data =  Admission::where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'))->where('school', 1);
                    if (!empty($admissionNo)) {
                        $data = $data->where("id", $admissionNo);
                    }
                    if (!empty($class_type_id)) {
                        $data = $data->where("class_type_id", $class_type_id);
                    }
                    if (!empty($country_id)) {
                        $data = $data->where("country_id", $country_id);
                    }
                    if (!empty($state_id)) {
                        $data = $data->where("state_id", $state_id);
                    }
                    if (!empty($city_id)) {
                        $data = $data->where("city_id", $city_id);
                    }
                }
                $allstudents = $data->orderBy('id', 'DESC')->get();
                return  view('certificate.tc_certificate.tc_search', ['data' => $allstudents]);
            }

            public function tcAddClick(Request $request){
                $student_id = $request->get('student_id');
                $data =  Student::with('ClassTypes')->where('session_id', Session::get('session_id'))->where('branch_id', Session::get('branch_id'));
                if (!empty($student_id)) {
                    $data = $data->where("id", $student_id);
                }
                $students = $data->orderBy('id', 'DESC')->get();
                echo $students[0];
            }
}
