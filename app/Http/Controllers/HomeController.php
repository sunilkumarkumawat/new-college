<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Admin;
use App\Models\State;
use App\Models\City;
use App\Models\WhatsappApiResponse;
use App\Models\Classs;
use App\Models\ClassType;
use App\Models\Student;
use App\Models\Sessions;
use App\Models\Admission;
use App\Models\exam\AssignExam;
use App\Models\examoffline\AssignOfflineExam;
use App\Models\hostel\HostelBuilding;
use App\Models\hostel\HostelFloor;
use App\Models\hostel\HostelRoom;
use App\Models\hostel\HostelBed;
use App\Models\Master\BusRouteAssign;
use App\Models\Master\TeacherSubject;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\library\LibraryCabin;
use Session;
use Hash;
use Str;
use App;
use Redirect;
use Response;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller

{
    public function updateSingleField(Request $request){
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
    
    
    public function countryData(Request $request,$id){
        if(!empty($id)){
            $getState = array();      
         
            $getState = State::where('country_id',$id)->get();
            
            $stateData ='<option value="">Select</option>';
            foreach($getState as $state)
            {
               $stateData.='
               <option value="'.$state->id.'">'.$state->name.'</option>';
            }
           echo $stateData;
        } 
    }
       
    
    public function stateData(Request $request,$id){
        if(!empty($id)){
            $getState = array();      
         
            $getState = City::where('state_id',$id)->get();
            
            $cityData ='<option value="">Select</option>';
            foreach($getState as $city){
                $cityData.='<option value="'.$city->id.'">'.$city->name.'</option>';
            }    
            echo $cityData;
        } 
    }
    
    
     public function examData(Request $request,$class_type_id){
     $data = array();    
         if(!empty($class_type_id)){

	       $data =  AssignExam::select('assign_exams.*','exam.id as exam_id','exam.name as exam_name')
	    ->leftjoin('exams as exam','assign_exams.exam_id','exam.id')
        ->where('assign_exams.class_type_id',$class_type_id)
        ->where('assign_exams.session_id',Session::get('session_id'))
        ->where('assign_exams.branch_id',Session::get('branch_id'))
        ->get();
	 
        $examData ='<option value="">Select</option>';
            foreach($data as $exam){
           $examData.='
           <option value="'.$exam['exam_id'].'">'.$exam['exam_name'].'</option>';
           }
        echo $examData;
       } 
    }    
    
       
     public function studentData ($id){
         $data = array();    
         if(!empty($id)){
        $data = Student::where('section_id',$id)->get();
        $sectionData ='';
            foreach($data as $class){
           $sectionData.='
           <option value="'.$class['id'].'">'.$class['name'].'</option>';
           }
        echo $sectionData;
       } 
     }

    public function busData(Request $request,$id){
     $data = array();    
         if(!empty($id)){
        $data = BusRouteAssign::with('bus')->where('route_id',$id)->get();
      
        $busData ='';
            foreach($data as $bus){
           $busData.='
           <option value="'.$bus['bus_id'].'">'.$bus['bus']['name'].'</option>';
           }
        echo $busData;
       } 
    }   
 
     public function libraryData(Request $request,$id){
         $data = array();    
             if(!empty($id)){
            $data = LibraryCabin::with('Library')->where('library_id',$id)->get();
            $libraryData ='<option value="">Select</option>';
                foreach($data as $type){
               $libraryData.='
               <option value="'.$type['id'].'">'.$type['name'].'</option>';
               }
            echo $libraryData;
           }  
     } 
    
     
      public function change(Request $request)
    {
        App::setLocale($request->lang);
        session()->put('locale', $request->lang);
       
        return redirect()->back();
    }
    
    public function subjectGetData(Request $request, $id){ 
        $data = array();    
        if(!empty($id)){
            $subject = Subject::where('class_type_id', $id);
              if(Session::get('role_id') == 2)
            {
                
                      $checkClassTeacher= Teacher::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('id',Session::get('teacher_id'))->where('class_type_id',$id)->first();
                 $subjects = TeacherSubject::where('session_id',Session::get('session_id'))->where('branch_id',Session::get('branch_id'))->where('teacher_id',Session::get('teacher_id'))->where('class_type_id', $id)->groupBy('subject_id')->get();
              
                
              if(empty($checkClassTeacher))
              {
              if(!empty($subjects))
              {
                   $att = array();
                  foreach($subjects as $item)
                  {
                      $att[] = $item->subject_id;
                  }
                  
                  $subject =$subject->whereIn('id',$att);
              }
                
            }
            }
          
            $subject = $subject->get();
         
            $subjectData ='<option value="">Select</option>';
            foreach($subject as $item){
                $subjectData.='
                <option value="'.$item['id'].'">'.$item['name'].'</option>';
            }
            echo $subjectData;
        } 
    }  
 public function sectionDataId(Request $request)
{
    if(!empty($request->sessionData)){
        // Fetch the selected session data
        $data = Sessions::find($request->sessionData);
        $new_session_id = $data['id'];
        
        // Update the active session ID in the browser session
        Session::put('session_id', $new_session_id);

        // ==========================================
        // AUTO-COPY CLASSES LOGIC START
        // ==========================================
        $branch_id = Session::get('branch_id');
        $last_session_id = $new_session_id - 1; 

        // 1. Check if classes already exist in the new session for this branch
        $existingClassesCount = ClassType::where('branch_id', $branch_id)
                                         ->where('session_id', $new_session_id)
                                         ->count();

        // 2. If the new session is completely empty (0 classes), copy the data
        if ($existingClassesCount == 0) {
            
            // Fetch all classes from the previous session
            $oldClasses = ClassType::where('branch_id', $branch_id)
                                   ->where('session_id', $last_session_id)
                                   ->get();

            // If there is data in the previous session, save it to the new session
            if ($oldClasses->isNotEmpty()) {
                foreach ($oldClasses as $oldClass) {
                    $newClass = new ClassType; 
                    $newClass->user_id = Session::get('id');
                    $newClass->session_id = $new_session_id;   // Assigning the newly selected session
                    $newClass->branch_id = $branch_id; 
                    $newClass->course_id = $oldClass->course_id;
                    $newClass->master_id = $oldClass->master_id;
                    $newClass->section_id = $oldClass->section_id;
                    $newClass->name = $oldClass->name;
                    $newClass->orderBy = $oldClass->orderBy;
                    $newClass->save();
                }
            }
        }
        // ==========================================
        // AUTO-COPY CLASSES LOGIC END
        // ==========================================

        return back()->with('message', 'Session Changed Successfully.');
    } else {
        return back()->with('error', 'Session Not Changed ! ');
    }
}

    public function calendarElement(Request $request){
        return view('dashboard.calendar');
    }
 
}