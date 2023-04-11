<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuBarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_bars', function (Blueprint $table) {
            $table->increments('menu_bars_id');
            $table->foreignId('stock1s_id')->references('stock1s_id')->on('stock1s')->onDelete('cascade');
            $table->string('category');
            $table->string('stocks')->unique();
            $table->string('description')->nullable();
            $table->float('price', 8, 2)->default(0);
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
        Schema::dropIfExists('menu_bars');
    }
}
