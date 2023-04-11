<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenu1sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu1s', function (Blueprint $table) {
            $table->increments('menu1s_id');
            $table->foreignId('menus_id')->references('menus_id')->on('menus')->onDelete('cascade');
            $table->string('category')->unique();
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
        Schema::dropIfExists('menu1s');
    }
}
