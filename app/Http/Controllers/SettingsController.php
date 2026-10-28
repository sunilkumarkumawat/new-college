<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Setting;
use App\Models\City;
use App\Models\IPSetting;
use App\Models\CustomVillageList;
use App\Models\Master\Branch;
use Session;
use Hash;
use Helper;
use File;
use Str;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller

{
            public function settings_dashboard (){
                return view('settings.settings_dashboard');
            }
            
            public function addSetting(Request $request){
               $branch = Branch ::all();
                    if($request->isMethod('post')){
                        $request->validate([
                            'branch_id'  => 'required',
                        ]);
                  $add_setting = new Setting;//model name
                  $data = Branch::find($request->branch_id);
                        /*if($request->file('right_logo')){
                         $image = $request->file('right_logo');
                        $path = $image->getRealPath();      
                        $right_logo =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/right_logo';
                        $image->move($destinationPath, $right_logo);    
                        $data->right_logo = $right_logo;
                     }
                        if($request->file('left_logo')){
                         $image = $request->file('left_logo');
                        $path = $image->getRealPath();      
                        $left_logo =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/left_logo';
                        $image->move($destinationPath, $left_logo);     
                        $data->left_logo = $left_logo;
                     }
                        if($request->file('seal_sign')){
                         $image = $request->file('seal_sign');
                        $path = $image->getRealPath();      
                        $seal_sign =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/seal_sign';
                        $image->move($destinationPath, $seal_sign);     
                        $data->seal_sign = $seal_sign;
                     }
                     */
            		$add_setting->user_id = Session::get('id'); 
            		$add_setting->session_id = Session::get('session_id');
            		$add_setting->role_id = Session::get('role_id');
                    $add_setting->branch_id = $data->id;
            		$add_setting->name = $data->branch_name;
            		$add_setting->mobile  = $data->mobile;
            		$add_setting->gmail = $data->email;
            		$add_setting->country_id = $data->country_id;
            		$add_setting->state_id = $data->state_id;
            		$add_setting->city_id = $data->city_id;
            		$add_setting->pincode = $data->pin_code;
            		$add_setting->address  = $data->address;
            	    $add_setting->save();
                return redirect::to('viewSetting')->with('message', 'Setting Add Successfully.');     
                }
                return view('settings.setting.addSetting',['branch'=>$branch]);
            }
            
            public function viewSetting(Request $request){
               $branch = Branch :: OrderBy('id')->get();
              
                    $setting = Setting::where('branch_id', Session::get('branch_id'))->get();
                
                return view('settings.setting.viewSetting',['data'=>$setting,'branch'=>$branch]);
            }
            public function editSetting(Request $request,$id){
                $branch = Branch :: OrderBy('id')->get();
                  $data = Setting::find($id);
                   $getcitys = City::where('state_id',$data->state_id)->get();
                if($request->isMethod('post')){
                        $request->validate([
                     'name'  => 'required',
                     'gmail'  => 'required',
                     'address'  => 'required',
                     'mobile'  => 'required',
                    //  'tin_no'  => 'required',
                     'pincode'  => 'required',
                     'current_active_session_id'  => 'nullable',
                    //  'right_logo'  => 'required',
                    //  'seal_sign'  => 'required',
                    //  'left_logo'  => 'required',
                     
                     ]);
                     /*   if($request->file('right_logo')){
                         $image = $request->file('right_logo');
                        $path = $image->getRealPath();      
                        $right_logo =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/right_logo';
                        $image->move($destinationPath, $right_logo);
                          if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/right_logo/' . $data->right_logo)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/right_logo/' . $data->right_logo);
                            }
                            $data->right_logo = $right_logo;
                     }*/
                        if($request->file('watermark_image')){
                         $image = $request->file('watermark_image');
                        $path = $image->getRealPath();      
                        $watermark_image =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/watermark_image';
                        $image->move($destinationPath, $watermark_image);
                          if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/watermark_image/' . $data->watermark_image)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/watermark_image/' . $data->watermark_image);
                            }
                            $data->watermark_image = $watermark_image;
                     }
                        if($request->file('left_logo')){
                         $image = $request->file('left_logo');
                        $path = $image->getRealPath();      
                        $left_logo =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/left_logo';
                        $image->move($destinationPath, $left_logo);  
                         if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/left_logo/' . $data->left_logo)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/left_logo/' . $data->left_logo);
                            }
                            $data->left_logo = $left_logo;
                     }
                    if($request->file('seal_sign')){
                        $image = $request->file('seal_sign');
                        $path = $image->getRealPath();      
                        $seal_sign =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/seal_sign';
                        $image->move($destinationPath, $seal_sign);     
                          if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/seal_sign/' . $data->seal_sign)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/seal_sign/' . $data->seal_sign);
                            }
                            $data->seal_sign = $seal_sign;
                    }
                    if($request->file('principal_sign')){
                        $image = $request->file('principal_sign');
                        $path = $image->getRealPath();      
                        $principal_sign =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/principal_sign';
                        $image->move($destinationPath, $principal_sign);     
                          if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/principal_sign/' . $data->principal_sign)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/principal_sign/' . $data->principal_sign);
                            }
                            $data->principal_sign = $principal_sign;
                    }
                    if($request->file('exam_sign')){
                        $image = $request->file('exam_sign');
                        $path = $image->getRealPath();      
                        $exam_sign =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/exam_sign';
                        $image->move($destinationPath, $exam_sign);     
                          if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/exam_sign/' . $data->exam_sign)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/exam_sign/' . $data->exam_sign);
                            }
                            $data->exam_sign = $exam_sign;
                    }
                    if($request->file('treasurer_sign')){
                        $image = $request->file('treasurer_sign');
                        $path = $image->getRealPath();      
                        $treasurer_sign =  time().uniqid().$image->getClientOriginalName();
                        $destinationPath = env('IMAGE_UPLOAD_PATH').'setting/treasurer_sign';
                        $image->move($destinationPath, $treasurer_sign);     
                          if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/treasurer_sign/' . $data->treasurer_sign)) {
                            File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/treasurer_sign/' . $data->treasurer_sign);
                            }
                            $data->treasurer_sign = $treasurer_sign;
                    }
                		$data->user_id = Session::get('id'); 
                		$data->session_id = Session::get('session_id');
                		$data->role_id = Session::get('role_id');
                        //$data->branch_id = $request->branch_id;
                		$data->account_id =$request->account_id;
                		$data->name =$request->name;
                		$data->mobile  = $request->mobile;
                		$data->gmail = $request->gmail;
                		$data->country_id = $request->country_id;
                		$data->loginWithOtp = $request->loginWithOtp;
                		$data->state_id = $request->state_id;
                		$data->city_id = $request->city_id;
                		$data->pincode = $request->pincode;
                		$data->address  = $request->address;
                		$data->tin_no = $request->tin_no;
                		$data->firebase_notification = $request->firebase_notification ?? 0;
                		$dynamicSession = \App\Helpers\helper::resolveCurrentFinancialSession($data->branch_id);
                		$data->current_active_session_id = $request->current_active_session_id ?: $dynamicSession->id;
                	    $data->save();
        	    return redirect::to('viewSetting')->with('message', 'Setting Updated Successfully !');
            }
             return view('settings.setting.editSetting',['data'=>$data,'branch'=>$branch,'getcitys'=>$getcitys]);
            } 

             public function deleteSetting(Request $request){
                $id = $request->delete_id;
                $setting = Setting::find($id);
               if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/watermark_image/' . $setting->watermark_image)) {
                File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/watermark_image/' . $setting->watermark_image);
                }
             /*  if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/right_logo/' . $setting->right_logo)) {
                File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/right_logo/' . $setting->right_logo);
                }*/
               if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/left_logo/' . $setting->left_logo)) {
                File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/left_logo/' . $setting->left_logo);
                }
               if (File::exists(env('IMAGE_UPLOAD_PATH') . 'setting/seal_sign/' . $setting->seal_sign)) {
                File::delete(env('IMAGE_UPLOAD_PATH') . 'setting/seal_sign/' . $setting->seal_sign);
                }
                 $setting->delete();
                 return redirect::to('viewSetting')->with('message', 'Setting Delete Successfully.');
            }
        
            public function addIP(Request $request){
                if($request->isMethod('post')){
                        $request->validate([
                     'ip_address'  => 'required',
                     ]);
                for($count = 0; $count <= count($request->ip_address); $count++){
                    if(isset($request->ip_address[$count])){
                    $ipSetting = new IPSetting;//model name
                    $ipSetting->user_id = Session::get('id');     
                    $ipSetting->role_id = Session::get('id');     
            		$ipSetting->ip_address =$request->ip_address[$count];
            		$ipSetting->remark  = $request->remark[$count];
            		$ipSetting->status  = 1;
            	    $ipSetting->save();
            	    
                    }
                }
                return redirect::to('view_ip_setting')->with('message', 'IP Setting add Successfully.');     
                }
                return view('settings.ip_setting.add');
            }
            
            public function viewIP(){
                //$alladd_setting =  IPSetting::where('session_id',Session::get('session_id'));
                $alladd_setting = IPSetting::all();
                /*if(Session::get('role_id') > 1){
                  $alladd_setting = $alladd_setting->where('branch_id',Session::get('branch_id'));
                }
                $data = $alladd_setting->orderBy('id','ASC')->get();
                dd($data);*/
                return view('settings.ip_setting.view',['data'=>$alladd_setting]);
            }
            public function editIP(Request $request,$id){
                  $data = IPSetting::find($id);
                if($request->isMethod('post')){
                        $request->validate([
                     'ip_address'  => 'required',
                     ]);
        		$data->user_id = Session::get('id');  
        		$data->role_id = Session::get('id');
        		$data->ip_address =$request->ip_address;
        		$data->remark =$request->remark;
        	    $data->save();
        	    return redirect::to('view_ip_setting')->with('message', 'IP Setting Updated Successfully !');
            }
             return view('settings.ip_setting.edit',['data'=>$data]);
            }    
        
             public function deleteIP(Request $request){
                $id = $request->delete_id;
                $setting = IPSetting::find($id)->delete();
                return redirect::to('view_ip_setting')->with('message', 'IP Setting Deleted Successfully.');
            }
            
            public function ipStatus(Request $request){
                
               if($request->id >0){
                $data = IPSetting::where('id',$request->id)->update(['status'=>$request->status]);
               if(!empty($data)){
               echo json_encode(1);
                    }else{
                         echo json_encode(0);
                    }
                }else{
                echo json_encode(2);
                }
            }       
            
            public function addVillageList(Request $request){
                 if($request->isMethod('post')){
                  $add = new CustomVillageList;//model name
                  $add->name = $request->village_name;
                  $add->save();
                   return redirect::to('editSetting/1')->with('message', 'Village Updated Successfully !');
                 }
             }
             
             public function deleteVillageList(Request $request){
                 if($request->isMethod('post')){
              $delete = CustomVillageList::find($request->delete_id);
              $delete->delete();
                   return redirect::to('editSetting/1')->with('error', 'Village Deleted Successfully !');
                 }
             }
     
}    