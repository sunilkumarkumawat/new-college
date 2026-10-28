<?php

namespace App\Http\Controllers\master;
use Illuminate\Validation\Validator; 
use App\Models\User;
use App\Models\Admin;
use App\Models\Master\Course;
use App\Models\Master\Batch;
use App\Models\ClassType;
use Session;
use Hash;
use Str;
use Redirect;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
class ClassController extends Controller

{
    
 
            public function copyLastSessionClasses(Request $request)
            {
                $request->validate([
                    'branch_id' => 'required',
                ]);
            
                $currentSessionId = Session::get('session_id');
                $destinationBranchId = $request->branch_id; 
                
                // Ye wo branch hai jisme aap abhi login hain (Source)
                $sourceBranchId = Session::get('branch_id'); 
                
                // Pichle session ki ID
                $lastSessionId = $currentSessionId - 1; 
            
                // Source branch se pichle session ki classes fetch karna
                $oldClasses = ClassType::where('branch_id', $sourceBranchId)
                                       ->where('session_id', $lastSessionId)
                                       ->get();
            
                if($oldClasses->isEmpty()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No classes found in the previous session for the main branch.'
                    ]);
                }
            
                $copiedCount = 0;
            
                foreach ($oldClasses as $oldClass) {
                    // Check karna ki kya ye class destination branch ke current session me already hai
                    $exists = ClassType::where('branch_id', $destinationBranchId)
                                       ->where('session_id', $currentSessionId)
                                       ->where('name', $oldClass->name)
                                       ->exists();
            
                    if (!$exists) {
                        $newClass = new ClassType; 
                        $newClass->user_id = Session::get('id');
                        $newClass->session_id = $currentSessionId;   
                        $newClass->branch_id = $destinationBranchId; 
                        $newClass->course_id = $oldClass->course_id;
                        $newClass->master_id = $oldClass->master_id;
                        $newClass->section_id = $oldClass->section_id;
                        $newClass->name = $oldClass->name;
                        $newClass->orderBy = $oldClass->orderBy;
                        $newClass->save();
                        
                        $copiedCount++;
                    }
                }
            
                if($copiedCount > 0) {
                    return response()->json([
                        'status' => 'success',
                        'message' => $copiedCount . ' classes successfully copied.'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'info',
                        'message' => 'These classes already exist in the selected branch.'
                    ]);
                }
            }


            public function add(Request $request){
                if($request->isMethod('post')){
                    $request->validate([
                        'name'  => 'required',
                    ]);
                    $orderBy = 0;
                    $arrayOfStrings = [
                        '1st','1', '2nd','2', '3rd','3', '4th','4', '5th','5', '6th','6', '7th','7', '8th','8', '9th','9', '10th','10',
                        'first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'
                    ];
                    $number = [1,1, 2,2, 3,3, 4,4, 5,5, 6,6, 7,7, 8,8, 9,9, 10,10, 1,2,3,4,5,6,7,8,9,10];
                    $stringToCheck = strtolower($request->name);
                    foreach ($arrayOfStrings as $key => $string) {
                        if (strpos($stringToCheck,$string) !== false) {
                            $orderBy = $number[$key];
                            break;
                        }
                    }

                    if (!empty($request->id)) {
                        $class = ClassType::find($request->id);
                        $msg = 'Class / Semester Updated Successfully.';
                    } else {
                        $class = new ClassType;
                        $class->user_id = Session::get('id');
                        $class->session_id = Session::get('session_id') ?? 1;
                        $class->branch_id = Session::get('branch_id');
                        $msg = 'Class / Semester Added Successfully.';
                    }

                    $class->course_id = $request->course_id;
                    $class->name = $request->name;
                    if ($orderBy > 0 || empty($class->orderBy)) {
                        $class->orderBy = $orderBy;
                    }
                    $class->save();

                    return redirect::to('add_class')->with('message', $msg);
                }

                $editData = null;
                if ($request->has('edit_id') && !empty($request->edit_id)) {
                    $editData = ClassType::find($request->edit_id);
                }

                $alladd_type = ClassType::with('course')
                    ->withCount('admissions')
                    ->where('branch_id', Session::get('branch_id'))
                    ->where('session_id', Session::get('session_id'))
                    ->orderBy('course_id', 'ASC')
                    ->orderBy('orderBy', 'ASC')
                    ->get();

                $courses = Course::where('branch_id', Session::get('branch_id'))->orderBy('name', 'ASC')->get();
                return view('admin.class.add', ['data' => $alladd_type, 'courses' => $courses, 'editData' => $editData]);
            }    

            public function edit(Request $request, $id = null){
                $editId = $id ?? $request->id;
                if($request->isMethod('post')){
                    $request->validate([
                        'name'  => 'required',
                    ]);
                    $data = ClassType::find($editId);
                    if ($data) {
                        $data->session_id = Session::get('session_id') ?? 1;
                        $data->branch_id = Session::get('branch_id');
                        $data->course_id = $request->course_id ?? $data->course_id;
                        $data->name = $request->name;
                        $data->save();
                    }
                    return redirect::to('add_class')->with('message', 'Class / Semester Updated Successfully.');
                }
                
                return redirect::to('add_class?edit_id=' . $editId);
            }

            public function delete(Request $request){
                $id = $request->delete_id;
                $sss = ClassType::find($id);
                if ($sss) {
                    $sss->delete();
                }
                return redirect::to('add_class')->with('message', 'Class / Semester Deleted Successfully.');
            }
            



            public function course_add(Request $request){
                if($request->isMethod('post')){
                    $request->validate([
                        'name' => 'required',
                    ]);
                    
                    if (!empty($request->id)) {
                        $course = Course::find($request->id);
                        $msg = 'Course Stream Updated Successfully.';
                    } else {
                        $course = new Course;
                        $course->session_id = Session::get('session_id') ?? 1;
                        $course->branch_id = Session::get('branch_id');
                        $msg = 'Course Stream Added Successfully.';
                    }

                    $course->name = $request->name;
                    $course->duration = $request->duration ?? 1;
                    $course->total_semester = $request->total_semester ?? 0;
                    $course->course_type = $request->course_type ?? 'Semester';
                   
                    $course->save();
                    return redirect::to('course_add')->with('message', $msg);
                }

                $editData = null;
                if ($request->has('edit_id') && !empty($request->edit_id)) {
                    $editData = Course::find($request->edit_id);
                }

                $viewcourse = Course::withCount('classes')
                    ->where('branch_id', Session::get('branch_id'))
                    ->orderBy('id', 'ASC')
                    ->get();

                return view('master.course.add', ['data' => $viewcourse, 'editData' => $editData]);
            }   

            public function course_edit(Request $request, $id = null){
                $editId = $id ?? $request->id;
                if($request->isMethod('post')){
                    $request->validate([
                        'name' => 'required',
                    ]);
                    $data = Course::find($editId);
                    if ($data) {
                        $data->session_id = Session::get('session_id') ?? 1;
                        $data->branch_id = Session::get('branch_id');
                        $data->name = $request->name;
                        $data->duration = $request->duration ?? 1;
                        $data->total_semester = $request->total_semester ?? 0;
                        $data->course_type = $request->course_type ?? 'Semester';
                        $data->save(); 
                    }
                    return redirect::to('course_add')->with('message', 'Course Details Edited Successfully.');
                }
                return redirect::to('course_add?edit_id=' . $editId);
            }
            
            public function course_delete(Request $request){
                $id = $request->delete_id;
                if ($id) {
                    Course::find($id)?->delete();
                }
                return redirect::to('course_add')->with('message', 'Course Stream Deleted Successfully.');
            }

}
