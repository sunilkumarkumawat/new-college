<?php

namespace App\Models;

use Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Teacher extends Model
{
    use SoftDeletes;
    protected $table = "users"; // Mapped to users table
    
    public function TeacherDocuments()
    {
        return $this->hasMany('App\Models\TeacherDocuments','teacher_id');
    }
    
    public static function countTeacher(){
        try {
            $data = User::query();
            if(Session::get('role_id') > 1){
               $data = $data->where('branch_id', Session::get('branch_id'));
            }
            return $data->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    public static function countDropTeacher(){
        try {
            $data = User::where('status', 0);
            if(Session::get('role_id') > 1){
               $data = $data->where('branch_id', Session::get('branch_id'));
            }
            return $data->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}