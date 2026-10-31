<?php

namespace App\Models\CollegeFees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admission;
use App\Models\Master\Course;
use App\Models\User;

class CollegeFeeReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'college_fee_receipts';

    protected $fillable = [
        'receipt_no',
        'branch_id',
        'session_id',
        'student_id',
        'course_id',
        'semester_no',
        'payment_date',
        'payment_mode',
        'transaction_id',
        'bank_name',
        'amount_paid',
        'discount_given',
        'remark',
        'cashier_id',
        'bulk_batch_id'
    ];

    public function bulkCollection()
    {
        return $this->belongsTo(CollegeFeeBulkCollection::class, 'bulk_batch_id');
    }

    public function student()
    {
        return $this->belongsTo(Admission::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(CollegeFeeReceiptItem::class, 'receipt_id');
    }

    /**
     * Generate the next sequential receipt number (e.g. RCPT-2026-0005).
     * Includes cancelled / soft-deleted receipts so numbers are never reused.
     */
    public static function generateNextReceiptNo($year = null)
    {
        $year = $year ?: date('Y');
        $prefix = 'RCPT-' . $year . '-';

        // 1. Fetch all receipt numbers for this prefix including soft-deleted
        $existingReceiptNos = self::withTrashed()
            ->where('receipt_no', 'LIKE', $prefix . '%')
            ->pluck('receipt_no');

        $maxSeq = 0;
        foreach ($existingReceiptNos as $rNo) {
            $suffix = substr($rNo, strlen($prefix));
            if (is_numeric($suffix)) {
                $seq = intval($suffix);
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        // Safeguard: also check max id withTrashed to prevent any duplicate
        $maxId = self::withTrashed()->max('id') ?: 0;
        if ($maxId > $maxSeq) {
            $maxSeq = $maxId;
        }

        $nextSeq = $maxSeq + 1;

        // Collision safety loop
        while (self::withTrashed()->where('receipt_no', $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT))->exists()) {
            $nextSeq++;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the next starting sequence integer for batch receipt generation.
     */
    public static function getStartingSequenceNumber($year = null)
    {
        $year = $year ?: date('Y');
        $prefix = 'RCPT-' . $year . '-';

        $existingReceiptNos = self::withTrashed()
            ->where('receipt_no', 'LIKE', $prefix . '%')
            ->pluck('receipt_no');

        $maxSeq = 0;
        foreach ($existingReceiptNos as $rNo) {
            $suffix = substr($rNo, strlen($prefix));
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
        while (self::withTrashed()->where('receipt_no', $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT))->exists()) {
            $nextSeq++;
        }

        return $nextSeq;
    }
}
