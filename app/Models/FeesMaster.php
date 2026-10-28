<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FeesMaster extends Model
{
        use SoftDeletes;
	protected $table = "fees_master"; //table name

     public function feesGroup()
    {
        return $this->belongsTo('App\Models\FeesGroup','fees_group_id');
    }

    public function ClassTypes()
    {
        return $this->belongsTo('App\Models\ClassType','class_type_id');
    }    
    
    public function course()
    {
        return $this->belongsTo('App\Models\Master\Course', 'course_id');
    }
    
}