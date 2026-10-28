<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollegeFeesModuleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. College Fee Structures (Master)
        if (!Schema::hasTable('college_fee_structures')) {
            Schema::create('college_fee_structures', function (Blueprint $table) {
                $table->id();
                $table->integer('branch_id')->default(1);
                $table->integer('course_id')->index();
                $table->tinyInteger('semester_no')->default(1);
                $table->string('batch', 20)->nullable()->index();
                $table->string('head_name', 100);
                $table->decimal('amount_boys', 10, 2)->default(0);
                $table->decimal('amount_girls', 10, 2)->default(0);
                $table->tinyInteger('is_refundable')->default(0);
                $table->date('due_date')->nullable();
                $table->bigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['course_id', 'semester_no', 'batch'], 'cfs_course_sem_batch_idx');
            });
        }

        // 2. College Fee Assignments (Per Student Fee Ledger)
        if (!Schema::hasTable('college_fee_assignments')) {
            Schema::create('college_fee_assignments', function (Blueprint $table) {
                $table->id();
                $table->integer('branch_id')->default(1);
                $table->bigInteger('student_id')->index();
                $table->integer('course_id')->index();
                $table->tinyInteger('semester_no')->default(1);
                $table->bigInteger('structure_id')->nullable()->index();
                $table->string('head_name', 100);
                $table->decimal('gross_amount', 10, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('net_amount', 10, 2)->default(0);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('due_amount', 10, 2)->default(0);
                $table->tinyInteger('is_carried_dues')->default(0);
                $table->string('status', 20)->default('Unpaid'); // Unpaid, Partial, Paid
                $table->bigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['student_id', 'semester_no'], 'cfa_student_sem_idx');
            });
        }

        // 3. College Fee Receipts (Payments)
        if (!Schema::hasTable('college_fee_receipts')) {
            Schema::create('college_fee_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_no', 50)->unique();
                $table->integer('branch_id')->default(1);
                $table->integer('session_id')->nullable()->index();
                $table->bigInteger('student_id')->index();
                $table->integer('course_id')->index();
                $table->tinyInteger('semester_no')->default(1);
                $table->date('payment_date')->index();
                $table->string('payment_mode', 30)->default('Cash'); // Cash, UPI, Bank Transfer, Cheque
                $table->string('transaction_id', 100)->nullable();
                $table->string('bank_name', 100)->nullable();
                $table->decimal('amount_paid', 10, 2)->default(0);
                $table->decimal('discount_given', 10, 2)->default(0);
                $table->text('remark')->nullable();
                $table->bigInteger('cashier_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. College Fee Receipt Items (Head-wise Breakdown)
        if (!Schema::hasTable('college_fee_receipt_items')) {
            Schema::create('college_fee_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('receipt_id')->index();
                $table->bigInteger('assignment_id')->nullable()->index();
                $table->string('head_name', 100);
                $table->decimal('amount_allocated', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('college_fee_receipt_items');
        Schema::dropIfExists('college_fee_receipts');
        Schema::dropIfExists('college_fee_assignments');
        Schema::dropIfExists('college_fee_structures');
    }
}
