<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsAndAddFieldsToAdmissionsEnquirys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('agents')) {
            Schema::create('agents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('name');
                $table->string('mobile', 30)->nullable();
                $table->string('email', 150)->nullable();
                $table->text('address')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('admissions', function (Blueprint $table) {
            if (!Schema::hasColumn('admissions', 'enrollment_no')) {
                $table->string('enrollment_no', 100)->nullable()->after('roll_no');
            }
            if (!Schema::hasColumn('admissions', 'web_registration_no')) {
                $table->string('web_registration_no', 100)->nullable()->after('enrollment_no');
            }
            if (!Schema::hasColumn('admissions', 'agent_id')) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('web_registration_no');
            }
        });

        Schema::table('enquirys', function (Blueprint $table) {
            if (!Schema::hasColumn('enquirys', 'enrollment_no')) {
                $table->string('enrollment_no', 100)->nullable()->after('registration_no');
            }
            if (!Schema::hasColumn('enquirys', 'web_registration_no')) {
                $table->string('web_registration_no', 100)->nullable()->after('enrollment_no');
            }
            if (!Schema::hasColumn('enquirys', 'agent_id')) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('web_registration_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enquirys', function (Blueprint $table) {
            if (Schema::hasColumn('enquirys', 'agent_id')) {
                $table->dropColumn('agent_id');
            }
            if (Schema::hasColumn('enquirys', 'web_registration_no')) {
                $table->dropColumn('web_registration_no');
            }
            if (Schema::hasColumn('enquirys', 'enrollment_no')) {
                $table->dropColumn('enrollment_no');
            }
        });

        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'agent_id')) {
                $table->dropColumn('agent_id');
            }
            if (Schema::hasColumn('admissions', 'web_registration_no')) {
                $table->dropColumn('web_registration_no');
            }
            if (Schema::hasColumn('admissions', 'enrollment_no')) {
                $table->dropColumn('enrollment_no');
            }
        });

        Schema::dropIfExists('agents');
    }
}
