<?php

namespace App\Models;
use Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Enquiry extends Model
{
        use SoftDeletes;
	protected $table = "enquirys"; //table name
	
	public  function course()
    {
        return $this->belongsTo('App\Models\Master\Course','course_id');
    }
    
	public  function ClassTypes()
    {
        return $this->belongsTo('App\Models\ClassType','class_type_id');
    }
    
	public  function Section()
    {
        return $this->belongsTo('App\Models\Master\Section','section_id');
    }
    
    public  function Admission()
    {
        return $this->hasMany('App\Models\Admission','student_id');
    }
    
    public  function Gender()
    {
        return $this->belongsTo('App\Models\Gender','gender_id');
    }

    public function Agent()
    {
        return $this->belongsTo('App\Models\Agent','agent_id');
    }
    
    public static function countTotalRegistration(){
        $Registration = Enquiry::where('session_id',Session::get('session_id'));
        if(Session::get('role_id') > 1){
            $countTotalRegistration = $Registration->where('branch_id',Session::get('branch_id'));
        }
        $countTotalRegistration = $Registration->count();

        return $countTotalRegistration; 
    }
    
    
}


