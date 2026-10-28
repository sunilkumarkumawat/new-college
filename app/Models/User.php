<?php

namespace App\Models;
use Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Model
{
        use SoftDeletes;
	protected $table = "users"; //table name
	protected $fillable = ['password','body'];
	
public function PrManagement()
    {
        return $this->hasMany('App\Models\PermissionManagement','reg_user_id');
    }
	
public function roleName()
    {
        return $this->belongsTo('App\Models\Role','role_id');
    }
    
    public static function countUser(){
        $data = User::where('branch_id',Session::get('branch_id'));
        if(Session::get('role_id') == 1){
            $data = $data->count();
        }else{
            $data = $data->where('branch_id',Session::get('branch_id'))->count();
        }
        return $data;
    }
    

 public static function countTodaysBirthday(){
        $data=User::where('session_id',Session::get('session_id'))->where('dob',date('Y-m-d'));
        
        if(Session::get('role_id') > 1){
            $data = $data->where('branch_id',Session::get('branch_id'));
        }
        
        $data = $data->count();
        return $data;
    }
    
    public function Branch()
    {
        return $this->belongsTo('App\Models\Master\Branch','branch_id');
    }

    public function getNameAttribute()
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: ($this->userName ?? 'User');
    }
    
}