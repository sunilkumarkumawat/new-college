<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentComplaint extends Model
{
    use SoftDeletes;

    protected $table = 'student_complaints';

    protected $fillable = [
        'session_id', 'branch_id', 'admission_id', 'class_type_id', 'section_id',
        'subject', 'description', 'date', 'status', 'admin_reply', 'replied_by', 'replied_at',
    ];

    public const STATUSES = [
        'pending'     => 'Pending',
        'in_progress' => 'In Progress',
        'resolved'    => 'Resolved',
    ];

    // Admin ka reply/action aane tak student edit/delete kar sakta hai
    public function canStudentEdit()
    {
        return is_null($this->replied_at) && empty($this->admin_reply) && $this->status == 'pending';
    }

    public function Admission()
    {
        return $this->belongsTo('App\Models\Admission', 'admission_id');
    }

    public function ClassType()
    {
        return $this->belongsTo('App\Models\ClassType', 'class_type_id');
    }

    public function Section()
    {
        return $this->belongsTo('App\Models\Master\Section', 'section_id');
    }
}