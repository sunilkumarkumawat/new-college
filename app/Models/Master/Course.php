<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Course extends Model
{
       // use SoftDeletes;
	protected $table = "courses"; //table name

    protected $fillable = [
        'branch_id',
        'session_id',
        'name',
        'duration',
        'total_semester',
        'course_type'
    ];

    public function classes()
    {
        return $this->hasMany(\App\Models\ClassType::class, 'course_id', 'id');
    }
}