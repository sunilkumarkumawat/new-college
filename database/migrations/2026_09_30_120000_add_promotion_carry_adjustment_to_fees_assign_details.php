<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromotionCarryAdjustmentToFeesAssignDetails extends Migration
{
    public function up()
    {
        Schema::table('fees_assign_details', function (Blueprint $table) {
            $table->decimal('promotion_carry_adjustment', 12, 2)
                ->default(0)
                ->after('discount');
        });
    }

    public function down()
    {
        Schema::table('fees_assign_details', function (Blueprint $table) {
            $table->dropColumn('promotion_carry_adjustment');
        });
    }
}
