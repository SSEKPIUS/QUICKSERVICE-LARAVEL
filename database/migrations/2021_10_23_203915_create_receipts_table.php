<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->increments('receipts_id');
            $table->integer('uID')->default(0);
            $table->string('name')->nullable();
            $table->string('section')->nullable();
            $table->integer('status')->default(5); // 5 unpaid, 10 paid
            $table->float('TTotal', 8, 2)->default(0);
            $table->string('misc')->nullable();
            $table->timestamps();
        });
        // DB::update("ALTER TABLE receipts AUTO_INCREMENT = 1000;");
        DB::table('receipts')->insert(['receipts_id' => 999]);
        DB::table('receipts')->where('receipts_id', 999)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
