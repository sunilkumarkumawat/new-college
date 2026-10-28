<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('fees_assigns') && !Schema::hasColumn('fees_assigns', 'remark')) {
            Schema::table('fees_assigns', function (Blueprint $table) {
                $table->text('remark')->nullable()->after('total_discount');
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
        if (Schema::hasTable('fees_assigns') && Schema::hasColumn('fees_assigns', 'remark')) {
            Schema::table('fees_assigns', function (Blueprint $table) {
                $table->dropColumn('remark');
            });
        }
    }
};
