<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCourseIdAndBatchToFeesMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fees_master', function (Blueprint $table) {
            if (!Schema::hasColumn('fees_master', 'course_id')) {
                $table->unsignedInteger('course_id')->nullable()->after('branch_id')->index();
            }
            if (!Schema::hasColumn('fees_master', 'batch')) {
                $table->string('batch', 50)->nullable()->after('course_id')->index();
            }
        });

        // Backfill existing rows with course_id from class_types and default batch '2026'
        $masters = DB::table('fees_master')->whereNull('deleted_at')->get();
        foreach ($masters as $m) {
            $class = DB::table('class_types')->where('id', $m->class_type_id)->first();
            $courseId = $class ? $class->course_id : null;
            DB::table('fees_master')->where('id', $m->id)->update([
                'course_id' => $courseId,
                'batch' => $m->batch ?: '2026'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fees_master', function (Blueprint $table) {
            if (Schema::hasColumn('fees_master', 'batch')) {
                $table->dropColumn('batch');
            }
            if (Schema::hasColumn('fees_master', 'course_id')) {
                $table->dropColumn('course_id');
            }
        });
    }
}
