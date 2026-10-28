<?php

namespace App\Models\CollegeFees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollegeFeeHead extends Model
{
    use SoftDeletes;

    protected $table = 'college_fee_heads';

    protected $fillable = [
        'branch_id',
        'name',
        'fee_type',
        'description',
        'status',
        'created_by'
    ];
}
