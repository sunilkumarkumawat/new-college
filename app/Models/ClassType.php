<?php

namespace App\Models;
use Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ClassType extends Model
{
       use SoftDeletes;
	protected $table = "class_types"; //table name
	
    protected $fillable = [
        'user_id',
        'branch_id',
        'session_id',
        'section_id',
        'course_id',
        'master_id',
        'name',
        'orderBy'
    ];

    public function course()
    {
        return $this->belongsTo('App\Models\Master\Course', 'course_id');
    }

    public function admissions()
    {
        return $this->hasMany('App\Models\Admission', 'class_type_id');
    }

 public static function countClasss(){
        $data = ClassType::where('session_id',Session::get('session_id'))->whereNull('deleted_at');
        
        if(Session('role_id') > 1){
            $data = $data->where('branch_id',Session('branch_id'));
        }
         
        $data = $data->count();  
        return $data;
    }	
	 
}