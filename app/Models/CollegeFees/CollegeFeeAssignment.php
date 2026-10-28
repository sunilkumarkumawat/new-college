<?php

namespace App\Models\CollegeFees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admission;
use App\Models\Master\Course;

class CollegeFeeAssignment extends Model
{
    use SoftDeletes;

    protected $table = 'college_fee_assignments';

    protected $fillable = [
        'branch_id',
        'student_id',
        'course_id',
        'semester_no',
        'structure_id',
        'head_name',
        'gross_amount',
        'discount_amount',
        'discount_reason',
        'net_amount',
        'paid_amount',
        'due_amount',
        'is_carried_dues',
        'status',
        'created_by'
    ];

    public function student()
    {
        return $this->belongsTo(Admission::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function structure()
    {
        return $this->belongsTo(CollegeFeeStructure::class, 'structure_id');
    }
}
