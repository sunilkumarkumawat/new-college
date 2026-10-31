<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollegeFeeBulkCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('college_fee_bulk_collections')) {
            Schema::create('college_fee_bulk_collections', function (Blueprint $table) {
                $table->id();
                $table->string('bulk_code', 50)->unique();
                $table->integer('branch_id')->default(1);
                $table->integer('session_id')->nullable()->index();
                $table->integer('course_id')->index();
                $table->string('batch', 20)->index();
                $table->tinyInteger('semester_no')->default(1);
                $table->string('head_name', 100);
                $table->decimal('amount_per_student', 10, 2)->default(0);
                $table->integer('total_students')->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->date('payment_date')->index();
                $table->string('payment_mode', 50)->default('Cash');
                $table->string('transaction_id', 150)->nullable();
                $table->string('bank_name', 100)->nullable();
                $table->text('remark')->nullable();
                $table->bigInteger('cashier_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('college_fee_receipts') && !Schema::hasColumn('college_fee_receipts', 'bulk_batch_id')) {
            Schema::table('college_fee_receipts', function (Blueprint $table) {
                $table->bigInteger('bulk_batch_id')->nullable()->index()->after('cashier_id');
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
        if (Schema::hasTable('college_fee_receipts') && Schema::hasColumn('college_fee_receipts', 'bulk_batch_id')) {
            Schema::table('college_fee_receipts', function (Blueprint $table) {
                $table->dropColumn('bulk_batch_id');
            });
        }

        Schema::dropIfExists('college_fee_bulk_collections');
    }
}
