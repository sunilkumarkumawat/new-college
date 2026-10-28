<?php

namespace App\Models\CollegeFees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\Course;

class CollegeFeeStructure extends Model
{
    use SoftDeletes;

    protected $table = 'college_fee_structures';

    protected $fillable = [
        'branch_id',
        'course_id',
        'semester_no',
        'batch',
        'head_name',
        'amount_boys',
        'amount_girls',
        'is_refundable',
        'due_date',
        'created_by'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
