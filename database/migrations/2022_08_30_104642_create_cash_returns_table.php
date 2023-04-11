<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_returns', function (Blueprint $table) {
            $table->id();
             $table->float('Rooms', 8, 2)->default(0);
             $table->float('Sauna_Masssage', 8, 2)->default(0);
             $table->float('Bar_Kitchen', 8, 2)->default(0);
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_returns');
    }
}