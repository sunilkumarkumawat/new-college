<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollegeFeeHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('college_fee_heads')) {
            Schema::create('college_fee_heads', function (Blueprint $table) {
                $table->id();
                $table->integer('branch_id')->default(1)->index();
                $table->string('name', 100);
                $table->string('fee_type', 100)->default('Tuition');
                $table->string('description', 255)->nullable();
                $table->tinyInteger('status')->default(1);
                $table->bigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['branch_id', 'name']);
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
        Schema::dropIfExists('college_fee_heads');
    }
}
