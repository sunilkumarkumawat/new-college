<?php

namespace App\Models\CollegeFees;

use Illuminate\Database\Eloquent\Model;

class CollegeFeeReceiptItem extends Model
{
    protected $table = 'college_fee_receipt_items';

    protected $fillable = [
        'receipt_id',
        'assignment_id',
        'head_name',
        'amount_allocated'
    ];

    public function receipt()
    {
        return $this->belongsTo(CollegeFeeReceipt::class, 'receipt_id');
    }

    public function assignment()
    {
        return $this->belongsTo(CollegeFeeAssignment::class, 'assignment_id');
    }
}
