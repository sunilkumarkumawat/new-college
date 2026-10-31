<?php

namespace App\Models\CollegeFees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Master\Course;
use App\Models\User;

class CollegeFeeBulkCollection extends Model
{
    use SoftDeletes;

    protected $table = 'college_fee_bulk_collections';

    protected $fillable = [
        'bulk_code',
        'branch_id',
        'session_id',
        'course_id',
        'batch',
        'semester_no',
        'head_name',
        'amount_per_student',
        'total_students',
        'total_amount',
        'payment_date',
        'payment_mode',
        'transaction_id',
        'bank_name',
        'remark',
        'cashier_id',
    ];

    public function receipts()
    {
        return $this->hasMany(CollegeFeeReceipt::class, 'bulk_batch_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Generate the next sequential bulk batch code (e.g. BLK-2026-0001).
     */
    public static function generateNextBulkCode($year = null)
    {
        $year = $year ?: date('Y');
        $prefix = 'BLK-' . $year . '-';

        $existingCodes = self::withTrashed()
            ->where('bulk_code', 'LIKE', $prefix . '%')
            ->pluck('bulk_code');

        $maxSeq = 0;
        foreach ($existingCodes as $code) {
            $suffix = substr($code, strlen($prefix));
            if (is_numeric($suffix)) {
                $seq = intval($suffix);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $maxId = self::withTrashed()->max('id') ?: 0;
        if ($maxId > $maxSeq) {
            $maxSeq = $maxId;
        }

        $nextSeq = $maxSeq + 1;

        while (self::withTrashed()->where('bulk_code', $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT))->exists()) {
            $nextSeq++;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
