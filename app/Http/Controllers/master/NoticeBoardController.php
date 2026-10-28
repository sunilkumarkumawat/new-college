<?php

namespace App\Http\Controllers\master;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Master\NoticeBoard;
use App\Models\Master\Role;
use App\Models\Master\Library;
use App\Models\Master\Branch;
use App\Models\Master\MessageTemplate;
use App\Models\Master\MessageType;
use App\Models\Setting;
use App\Models\NoticeText;
use App\Models\Admission;
use App\Models\Teacher;
use Session;
use Hash;
use Helper;
use Str;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoticeBoardController extends Controller

{
            public function view(Request $request,$id){
                $data =  NoticeBoard:: where('session_id',Session::get('session_id'))
		        ->where('branch_id',Session::get('branch_id'))->orderBy('id', 'DESC')->get();
                return view('master.notice_board.view',['data'=>$data,'data_id'=>$id]);
             }    
    
            public function add(Request $request){
                if($request->isMethod('post')){
                    $request->validate([
                        'title' => 'required',
                       'message' => 'required',
                       'from_date' => 'required',
                       'to_date' => 'required',
                    ]);
                    if(!empty($request->role_id)){
                        $role_id = implode(',', $request->role_id);
                    }
                    $add = new NoticeBoard;//model name
                    $add->user_id = Session::get('id');
                    $add->session_id = Session::get('session_id');
                    $add->branch_id = Session::get('branch_id');
            		$add->title =$request->title;
            		$add->message =$request->message;
            		$add->from_date =$request->from_date;
            		$add->to_date =$request->to_date;
                    if(!empty($request->role_id)){
                        $add->role_id = $role_id;
                    }
		            $add->save();
		            $template = MessageTemplate::Select('message_templates.*','message_types.slug')
                    ->leftjoin('message_types','message_types.id','message_templates.message_type_id')
                    ->where('message_types.status',1)->where('message_types.slug','noticeboard')->first();
                    $branch = Branch::find(Session::get('branch_id'));
                    $setting = Setting::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->first();
                    $creator_user = User::where('id',Session::get('id'))->where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->first();
                    $role_name = Role::where('id',$creator_user->role_id)->first();
                    $role_count = count($request->role_id);
                    for($i=0; $i < $role_count; $i++){
                        if($request->role_id[$i] != 3){
                            $user = User::where('role_id',$request->role_id[$i])->where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->get();
                            for($us = 0; $us < count($user); $us++){
                                $arrey1 = array(
                                    '{#name#}',
                                    '{#title#}',
                                    '{#from_date#}',
                                    '{#to_date#}',
                                    '{#notice_content#}',
                                    '{#support_no#}',
                                    '{#creator_name#}',
                                    '{#role#}',
                                    '{#mobile_no#}',
                                    '{#school_name#}');
                                $arrey2 = array(
                                    $user[$us]->first_name." ".$user[$us]->last_name,
                                    $request->title,
                                    date('d-m-Y',strtotime($request->from_date)),
                                    date('d-m-Y',strtotime($request->to_date)),
                                    $request->message,
                                    $setting->mobile,
                                    $creator_user->first_name." ".$creator_user->last_name,
                                    $role_name->name,
                                    $creator_user->mobile,
                                    $setting->name);
                                if(!empty($template) && $template->status != 1){
                                    $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content ?? '');
                                    if (!empty($setting) && $setting->firebase_notification == 1) {
                                        Helper::sendNotification(
                                            $template->title ?? 'Notice Board',
                                            $whatsapp,
                                            'user',
                                            $user[$us]->id
                                        ); 
                                    }
                                    if($branch->whatsapp_srvc != 0){
                                        if ($user[$us]->mobile != ""){
                                            if($template->whatsapp_status != 0){
                                                $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                                Helper::sendWhatsappMessage($user[$us]->mobile,$whatsapp);
                                            }
                                        }
                                    }
                                    if($branch->sms_srvc != 0){
                                        if($user[$us]->mobile != ""){
                                            if($template->sms_status != 0){
                                                $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                                Helper::SendMessage($user[$us]->mobile, $sms);
                                            }
                                        }
                                    }    
                                }
                            }    
                        }    
                        else{
                            $students = Admission::where('session_id',Session::get('session_id'))->where('status',1)->where('branch_id',Session::get('branch_id'))->get();
                            for($st = 0; $st < count($students); $st++){
                                $arrey1 = array(
                                '{#name#}',
                                '{#title#}',
                                '{#from_date#}',
                                '{#to_date#}',
                                '{#notice_content#}',
                                '{#support_no#}',
                                '{#creator_name#}',
                                '{#role#}',
                                '{#mobile_no#}',
                                '{#school_name#}');
                                $arrey2 = array(
                                $students[$st]->first_name." ".$students[$st]->last_name,
                                $request->title,
                                date('d-m-Y',strtotime($request->from_date)),
                                date('d-m-Y',strtotime($request->to_date)),
                                $request->message,
                                $setting->mobile,
                                $creator_user->first_name." ".$creator_user->last_name,
                                $role_name->name,
                                $creator_user->mobile,
                                $setting->name);
                                if(!empty($template) && $template->status != 1){
                                    $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content ?? '');
                                    if (!empty($setting) && $setting->firebase_notification == 1) {
                                        Helper::sendNotification(
                                            $template->title ?? 'Notice Board',
                                            $whatsapp,
                                            'student',
                                            $students[$st]->id
                                        ); 
                                    }
                                    if($branch->whatsapp_srvc != 0){
                                        if ($students[$st]->mobile != ""){
                                            if($template->whatsapp_status != 0){
                                                $whatsapp = str_replace($arrey1,$arrey2,$template->whatsapp_content);
                                                Helper::sendWhatsappMessage($students[$st]->mobile,$whatsapp);
                                            }
                                        }
                                    }
                                    if($branch->sms_srvc != 0){
                                        if($students[$st]->mobile != ""){
                                            if($template->sms_status != 0){
                                                $sms = str_replace($arrey1,$arrey2,$template->sms_content);
                                                Helper::SendMessage($students[$st]->mobile, $sms);
                                            }
                                        }
                                    }    
                                }
                            }
                        }
                    }
                    return redirect::to('notice_board/view/0')->with('message', 'Notice Board added Successfully.');
                }
                return view('master.notice_board.add');
            }    
            public function delete(Request $request){
                $book = NoticeBoard::find($request->delete_id)->delete();
                return redirect::to('notice_board/view/0')->with('message', 'NoticeBoard Deleted Successfully.');
            }

            public function edit(Request $request, $id){
                $data = NoticeBoard::find($id);
                if($request->isMethod('post')){
                    $request->validate([
                        'title' => 'required',
                        'message' => 'required',
              
                    ]);
                    if(!empty($request->role_id)){
                        $role_id = implode(',', $request->role_id);
                    }
                    $data->session_id = Session::get('session_id');
                    $data->branch_id = Session::get('branch_id');     
            		$data->title =$request->title;
            		$data->message = $request->message;
            		$data->from_date =$request->from_date;
            		$data->to_date =$request->to_date;
                    if(!empty($request->role_id)){
                        $data->role_id =$role_id;
                    }
		            $data->save();
                    return redirect::to('notice_board/view/0')->with('message', 'NoticeBoard Updated Successfully.');
                }
                return view('master.notice_board.edit',['data'=>$data]);
            } 

} 
