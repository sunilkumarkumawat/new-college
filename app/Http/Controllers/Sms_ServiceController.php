<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Admission;
use App\Models\Enquiry;
use App\Models\WhatsappGroup;
use App\Models\Master\Penalty;
use App\Models\SMS;
use App\Models\BirthdayWishes;
use App\Models\FailedMessages;
use App\Models\SmsSetting;
use App\Models\Master\MessageTemplate;
use App\Models\Master\MessageType;
use App\Models\Master\Branch;
use App\Models\Setting;
use App\Models\WhatsappSetting;
use App\Mail\MyMail;
use Session;
use Hash;
use Mail;
use Helper;
use Str;
use Redirect;
use Auth;
use File;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class Sms_ServiceController extends Controller

{

            public function startQueueWorker(){
                // Run scheduled tasks
                Artisan::call('schedule:run');
                return response()->json(['message' => 'Queue worker started.']);
            }
    
            public function sendMessageTerminal(Request $request){
                return view('sms_service.terminal');
            }
            
            public function groupView(Request $request){
                $search['class_type_id'] = $request->class_type_id ?? '';
                $data = WhatsappGroup::Select('whatsapp_groups.*','class_types.name as class_name')
                ->leftjoin('class_types','class_types.id','whatsapp_groups.class_type_id');
                if($request->class_type_id != ''){
                    $data = $data->where('class_type_id',$request->class_type_id);
                }
                $data= $data->get();
                return view('sms_service.group_view',['data'=>$data,'search'=>$search]);
            }
            
            public function groupStatus(Request $request){
                $search['class_type_id'] = $request->class_type_id ?? '';
                $data = WhatsappGroup::Select('whatsapp_groups.*','class_types.name as class_name')
                ->leftjoin('class_types','class_types.id','whatsapp_groups.class_type_id');
                if($request->class_type_id != ''){
                    $data = $data->where('class_type_id',$request->class_type_id);
                }
                $data= $data->get();
                $update = WhatsappGroup::find($request->id);
                $value = $update->status == 0 ? 1 : 0;
                $update->status = $value;
                $update->save();
                // return view('sms_service.group_view',['data'=>$data,'search'=>$search]);
                return redirect::to('group_view')->with('message', 'Status Updated Successfully.');  
            }
 
            public function groupDelete(Request $request ){
                $id = $request->delete_id;
                $id = WhatsappGroup::find($id)->delete();
                return redirect::to('group_view')->with('message', 'Group Delete Successfully.');
            }
            
            public function groupEdit(Request $request,$id){
                $data = WhatsappGroup::find($id);
                if($request->isMethod('post')){
                    $request->validate([
                        'class_type_id' => 'required',
                        'group_name' => 'required',
                        'group_id' => 'required',
                    ]);
                    $data->group_name = $request->group_name;
                    $data->group_id = $request->group_id;
                    $data->class_type_id = $request->class_type_id;
                    $data->save();
                    return redirect::to('group_view')->with('message', 'Group Edited Successfully.');
                }
                return view('sms_service.group_edit',['data'=>$data]);
            }
    
            public function groupMessagesSend(Request $request){
                if($request->isMethod('post')){
                    $request->validate([
                        'class_type_id' => 'required',
                        'group_name' => 'required',
                        // 'message' => 'required',
                    ]);
                    $data= WhatsappGroup::where('id',$request->group_name)->first();
                    $type ='text';
                    $url =null;
                    $image_data = '';
                    if ($request->file('file')) {
                        $image = $request->file('file');
                        $path = $image->getRealPath();
                        $image_data = time() . uniqid() . $image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH') . 'filepath';
                        $image->move($destinationPath, $image_data);
                        $type ='media';
                        $url = env('IMAGE_SHOW_PATH').'filepath/'.$image_data;
                    }
                    $response = Helper::sendWhatsappGroupMessage($data->group_id,$request->message,$type,$url);
                    $decode = json_decode($response);
                    if($decode->status == 'error'){
                        return redirect::to('group_messages_send')->with('error', 'Something went wrong');
                    }
                    else{
                       return redirect::to('group_messages_send')->with('message', 'Message Sent Successfully');
                    }
                }
                return View('sms_service.group_messages_send');
            }
    
            public function getGroupData(Request $request,$id){
                if(!empty($id)){
                    $getData = array();      
                    $getData = WhatsappGroup::where('class_type_id',$id)->get();
                    $setData ='<option value="">Select</option>';
                    foreach($getData as $item){
                        $setData.='<option value="'.$item->id.'">'.$item->group_name.'</option>';
                    }    
                    echo $setData;
                } 
            }
            
            public function groupAdd(Request $request){
                if($request->isMethod('post')){
                    $request->validate([
                        'class_type_id' => 'required',
                        'group_name' => 'required',
                        'group_id' => 'required',
                    ]);
                    $data = new WhatsappGroup;
                    $data->user_id = Session::get('user_id');
                    $data->session_id = Session::get('session_id');
                    $data->branch_id = Session::get('branch_id');
                    $data->class_type_id = $request->class_type_id;
                    $data->group_name = $request->group_name;
                    $data->group_id = $request->group_id;
                    $data->save();
                    return redirect::to('group_view')->with('message', 'Group Added Successfully.');
                }
                return view('sms_service.group_add');
            }
            
            public function resendMessage(Request $request){
                $data = FailedMessages::where('resend_status',0)->whereNull('deleted_at')->get();
                    if($request->isMethod('post')){
                        if(!empty($request->ids)){
                            foreach($request->ids as $key => $item)
                        {
                        $data = FailedMessages::find($item);
                            if($data->group_id != null){                                   
                                $response =    Helper::sendWhatsappGroupMessage($data->group_id, $data->sender_message,$data->type,$data->media_url);
                            }
                            else{
                                $response = Helper::sendWhatsappMessage($request->mobile[$key],$request->text[$key],$data->type,$request->media_url[$key]);
                            }
                            $data = $data->delete();
                            $decode = json_decode($response);
                            if($decode->status != 'error'){
                                $today = now();
                                $brithday = BirthdayWishes::whereDate('created_at',$today)->update(['status'=>0]);
                            }
                        }
                        return redirect::to('resend_message')->with('message', 'Messages Send Successfully.');
                    }
                }
                return view('sms_service.resend',['data'=>$data]);
            }
    
            public function failedMessagesDelete(Request $request){
                $id = $request->delete_id;
                $id = FailedMessages::find($id)->delete();
                return redirect::to('resend_message')->with('error', 'Group Deleted Successfully.');
            }
            
            public function sendMessage(Request $request){
                $template = MessageTemplate::Select('message_templates.*','message_types.slug')
                ->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                ->where('message_types.status',1)->where('message_types.slug','send-message')->first();
                $branch = Branch::find(Session::get('branch_id'));
                $setting = Setting::where('branch_id',Session::get('branch_id'))->first();
                if($request->isMethod('post')){
                    $url=null;
                    $type='text';
                    $image_data = '';
                    if ($request->file('file')) {
                        $image = $request->file('file');
                        $path = $image->getRealPath();
                        $image_data = time() . uniqid() . $image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH') . 'filepath';
                        $image->move($destinationPath, $image_data);
                        $type ='media';
                        $url = env('IMAGE_SHOW_PATH').'filepath/'.$image_data;
                    }
                    if($request->role_id != 3){
                        if($request->role_id == 1002){
                            if($request->email_checkbox != null){
                                if($branch->email_srvc != 0){
                                    if($request->single_email != ""){
                                        $whatsapp = $request->message;
                                        $emailData = ['email' => $request->single_email, 'data' => $whatsapp, 'subject' => "Important Message"];
                                        Helper::sendMail('email_print.template_print', $emailData);
                                    } 
                                } 
                            }
                            if($request->whatsapp_checkbox != null){
                                if($branch->whatsapp_srvc != 0){
                                    if ($request->single_mobile_number != ""){
                                        $whatsapp = $request->message;
                                        Helper::MessageQueueSetup(Session::get('id'), Session::get('branch_id'), Session::get('session_id'), $template->message_type_id, $request->single_mobile_number, $whatsapp, $url);
                                        //Helper::sendWhatsappMessage($request->single_mobile_number, $whatsapp,$url);
                                        // if(File::exists(env('IMAGE_UPLOAD_PATH').'filepath/'.$image_data)){
                                        //     File::delete(env('IMAGE_UPLOAD_PATH').'filepath/'.$image_data);
                                        // }
                                    }
                                }
                            }
                            if($request->sms_checkbox != null){
                                if($branch->sms_srvc != 0){
                                    if ($request->single_mobile_number != ""){
                                        $sms = $request->message;
                                        Helper::SendMessage($request->single_mobile_number, $sms);
                                        
                                    }
                                }
                            }
                        }
                        else{
                            for($i=0; $i<count($request->admission_id); $i++){
                                if($request->role_id == 1001){
                                    $user = Enquiry::where('id',$request->admission_id[$i])->first();
                                }
                                else{
                                    $user = User::where('id',$request->admission_id[$i])->first();
                                }
                                $arrey1 = array(
                                    '{#name#}',
                                    '{#message#}',
                                    '{#support_no#}',
                                    '{#school_name#}');

                                $arrey2 = array(
                                    $user->first_name." ".$user->last_name,
                                    $request->message,
                                    $setting->mobile,
                                    $setting->name);
                                if(!empty($template) && $template->status != 1){
                                    $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content ?? '');
                                    if (!empty($setting) && $setting->firebase_notification == 1 && !empty($user)) {
                                        Helper::sendNotification(
                                            $template->title ?? 'Important Message',
                                            $whatsapp,
                                            'user',
                                            $user->id
                                        ); 
                                    }
                                    if($user->email != ""){
                                        if($branch->email_srvc != 0){
                                            if($template->email_status != 0){
                                                $message = str_replace($arrey1,$arrey2,$template->email_content);
                                                $emailData = ['email' => $user->email, 'data' => $message, 'subject' => $template->title];
                                                Helper::sendMail('email_print.template_print', $emailData);
                                            } 
                                        } 
                                    }
                                    if($branch->whatsapp_srvc != 0){
                                        if ($user->mobile != ""){
                                            if($template->whatsapp_status != 0){
                                                $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                                Helper::MessageQueueSetup(Session::get('id'), $user->branch_id, $user->session_id, $template->message_type_id, $user->mobile, $whatsapp, $url);
                                                //Helper::sendWhatsappMessage($user->mobile,$whatsapp,$url);
                                                // if(File::exists(env('IMAGE_UPLOAD_PATH').'filepath/'.$image_data)){
                                                //     File::delete(env('IMAGE_UPLOAD_PATH').'filepath/'.$image_data);
                                                // }
                                            }
                                        }
                                    }
                                    if($branch->sms_srvc != 0){
                                        if($user->mobile != ""){
                                            if($template->sms_status != 0){
                                                $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                                Helper::SendMessage($user->mobile, $sms);
                                            }
                                        }
                                    }    
                                }
                            }
                        }
                    }
                    else{
                        for($i=0; $i<count($request->admission_id); $i++){
                            $admission = Admission::where('id',$request->admission_id[$i])->first();
                            $arrey1 = array(
                                '{#name#}',
                                '{#message#}',
                                '{#support_no#}',
                                '{#school_name#}');
                            $arrey2 = array(
                                $admission->first_name." ".$admission->last_name,
                                $request->message,
                                $setting->mobile,
                                $setting->name);
                            if(!empty($template) && $template->status != 1){
                                $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content ?? '');
                                if (!empty($setting) && $setting->firebase_notification == 1 && !empty($admission)) {
                                    Helper::sendNotification(
                                        $template->title ?? 'Important Message',
                                        $whatsapp,
                                        'student',
                                        $admission->id
                                    ); 
                                }
                                if($admission->email != ""){
                                    if($branch->email_srvc != 0){
                                        if($template->email_status != 0){
                                            $message = str_replace($arrey1,$arrey2,$template->email_content);
                                            $emailData = ['email' => $admission->email, 'data' => $message, 'subject' => $template->title];
                                            Helper::sendMail('email_print.template_print', $emailData);
                                            //Mail::to($admission->email)->queue(new MyMail());
                                        } 
                                    } 
                                }
                                if($branch->whatsapp_srvc != 0){
                                    if ($admission->mobile != ""){
                                        if($template->whatsapp_status != 0){
                                            $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                            Helper::MessageQueueSetup(Session::get('id'), $admission->branch_id, $admission->session_id, $template->message_type_id, $admission->mobile, $whatsapp, $url);
                                            //Helper::sendWhatsappMessage($admission->mobile,$whatsapp,$url);
                                            // if(File::exists(env('IMAGE_UPLOAD_PATH').'filepath/'.$image_data)){
                                            //     File::delete(env('IMAGE_UPLOAD_PATH').'filepath/'.$image_data);
                                            // }
                                        }
                                    }
                                }
                                if($branch->sms_srvc != 0){
                                    if($admission->mobile != ""){
                                        if($template->sms_status != 0){
                                            $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                            Helper::SendMessage($admission->mobile, $sms);
                                        }
                                    }
                                }    
                            }
                        }
                    }
                    if(!empty(Session::get('whatsapp_error'))){  
                        return redirect::to('send_message')->with('error', Session::get('whatsapp_error'));
                    }else{
                        return redirect::to('send_message')->with('message', 'Messages Scheduled Successfully.');
                    }
                } 
                return view('sms_service.add');
            }    
    
            public function smsSearchData(Request $request){
                if($request->get('role_id') == 3){
                    $session_id = $request->get('session_id');
                    $course_id = $request->get('course_id');
                    $class_type_id = $request->get('class_type_id');
                    
                    $data = Admission::where('branch_id', Session::get('branch_id'))->where('status', 1);
                    if(!empty($session_id)){
                        $data = $data->where('session_id', $session_id);
                    }
                    if(!empty($course_id)){
                        $courseObj = \App\Models\Master\Course::find($course_id);
                        $courseName = $courseObj ? $courseObj->name : null;
                        $data = $data->where(function($q) use ($course_id, $courseName) {
                            $q->whereHas('ClassTypes', function($q2) use ($course_id) {
                                $q2->where('course_id', $course_id);
                            });
                            if ($courseName) {
                                $q->orWhere('admissions.course', $courseName);
                            }
                        });
                    }
                    if(!empty($class_type_id )){
                       $data = $data->where("class_type_id", $class_type_id);
                    } 
                    $allstudents = $data->orderBy('id', 'DESC')->get(); 
                }
                else{
                    if($request->role_id == 1001){
                        $session_id = $request->get('session_id');
                        $course_id = $request->get('course_id');
                        $class_type_id = $request->get('class_type_id');
                        $data = Enquiry::where('branch_id', Session::get('branch_id'));
                        if(!empty($session_id)){
                            $data = $data->where('session_id', $session_id);
                        }
                        if(!empty($course_id)){
                            $data = $data->where(function($q) use ($course_id) {
                                $q->where('course_id', $course_id)
                                  ->orWhereHas('ClassTypes', function($q2) use ($course_id) {
                                      $q2->where('course_id', $course_id);
                                  });
                            });
                        }
                        if(!empty($class_type_id)){
                            $data = $data->where('class_type_id', $class_type_id);
                        }
                        $allstudents = $data->orderBy('id', 'DESC')->get(); 
                    }
                    else{
                        $allstudents = User::where("role_id", $request->get('role_id'))->where('branch_id', Session::get('branch_id'))->where('status', 1)->orderBy('id', 'DESC')->get(); 
                    }
                }
	            if(count($allstudents) != 0){
                    return view('sms_service.sms_search', ['data' => $allstudents]);
	            }
	            else{
	                return view('sms_service.sms_search', ['data' => "0"]);
	            }
            }
}
