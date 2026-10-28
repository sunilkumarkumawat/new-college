<?php

namespace App\Models;
use Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TeacherAttendance extends Model
{
       use SoftDeletes;
	protected $table = "teacher_attendance"; //table name
	
	public function Teacher()
    {
        return $this->belongsTo('App\Models\Teacher','teacher_id');
    }
    	public function AttendanceStatus()
    {
        return $this->belongsTo('App\Models\AttendanceStatus','attendance_status_id');
    }
    public static function countTeacherAttendance(){
        try {
            if (Schema::hasTable('teacher_attendance')) {
                $data = TeacherAttendance::where('date', date('Y-m-d'))->where('attendance_status_id', 1);
                if (Session::get('role_id') > 1) {
                    $data = $data->where('branch_id', Session::get('branch_id'));
                }
                return $data->count();
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
	
}
