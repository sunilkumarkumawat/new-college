<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddComprehensiveStaffFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'UniqueId')) {
                $table->string('UniqueId', 50)->nullable()->after('userName');
            }
            if (!Schema::hasColumn('users', 'gender_id')) {
                $table->integer('gender_id')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'marital_status')) {
                $table->string('marital_status', 50)->nullable()->after('gender_id');
            }
            if (!Schema::hasColumn('users', 'blood_group')) {
                $table->string('blood_group', 20)->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('users', 'aadhaar_no')) {
                $table->string('aadhaar_no', 20)->nullable()->after('blood_group');
            }
            if (!Schema::hasColumn('users', 'pan_number')) {
                $table->string('pan_number', 20)->nullable()->after('aadhaar_no');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_no')) {
                $table->string('emergency_contact_no', 20)->nullable()->after('father_mobile');
            }
            if (!Schema::hasColumn('users', 'spouse_name')) {
                $table->string('spouse_name', 200)->nullable()->after('emergency_contact_no');
            }
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 150)->nullable()->after('role_id');
            }
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 150)->nullable()->after('designation');
            }
            if (!Schema::hasColumn('users', 'qualification')) {
                $table->string('qualification', 255)->nullable()->after('department');
            }
            if (!Schema::hasColumn('users', 'work_experience')) {
                $table->string('work_experience', 100)->nullable()->after('qualification');
            }
            if (!Schema::hasColumn('users', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('work_experience');
            }
            if (!Schema::hasColumn('users', 'contract_type')) {
                $table->string('contract_type', 50)->nullable()->after('joining_date');
            }
            if (!Schema::hasColumn('users', 'shift')) {
                $table->string('shift', 50)->nullable()->after('contract_type');
            }
            if (!Schema::hasColumn('users', 'basic_salary')) {
                $table->decimal('basic_salary', 10, 2)->nullable()->after('salary');
            }
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name', 150)->nullable()->after('basic_salary');
            }
            if (!Schema::hasColumn('users', 'account_no')) {
                $table->string('account_no', 50)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('users', 'ifsc_code')) {
                $table->string('ifsc_code', 30)->nullable()->after('account_no');
            }
            if (!Schema::hasColumn('users', 'bank_branch')) {
                $table->string('bank_branch', 150)->nullable()->after('ifsc_code');
            }
            if (!Schema::hasColumn('users', 'epf_no')) {
                $table->string('epf_no', 50)->nullable()->after('bank_branch');
            }
            if (!Schema::hasColumn('users', 'esi_no')) {
                $table->string('esi_no', 50)->nullable()->after('epf_no');
            }
            if (!Schema::hasColumn('users', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'id_proof_doc')) {
                $table->string('id_proof_doc', 255)->nullable()->after('image');
            }
            if (!Schema::hasColumn('users', 'qualification_doc')) {
                $table->string('qualification_doc', 255)->nullable()->after('id_proof_doc');
            }
            if (!Schema::hasColumn('users', 'resume_doc')) {
                $table->string('resume_doc', 255)->nullable()->after('qualification_doc');
            }
            if (!Schema::hasColumn('users', 'other_doc')) {
                $table->string('other_doc', 255)->nullable()->after('resume_doc');
            }
        });

        // Ensure College ERP Roles exist
        $rolesToAdd = [
            'Teacher',
            'Sweeper / Peon',
            'Accountant',
            'Librarian',
            'Clerk / Office Staff',
            'Lab Assistant',
            'Security Guard',
            'Driver',
            'Hostel Warden',
            'Professor / Lecturer'
        ];

        foreach ($rolesToAdd as $roleName) {
            $exists = DB::table('role')->where('name', $roleName)->first();
            if (!$exists) {
                DB::table('role')->insert([
                    'user_id' => 1,
                    'branch_id' => 1,
                    'session_id' => 1,
                    'name' => $roleName,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'UniqueId', 'gender_id', 'marital_status', 'blood_group',
                'aadhaar_no', 'pan_number', 'emergency_contact_no', 'spouse_name',
                'designation', 'department', 'qualification', 'work_experience',
                'joining_date', 'contract_type', 'shift', 'basic_salary',
                'bank_name', 'account_no', 'ifsc_code', 'bank_branch',
                'epf_no', 'esi_no', 'permanent_address', 'id_proof_doc',
                'qualification_doc', 'resume_doc', 'other_doc'
            ]);
        });
    }
}
