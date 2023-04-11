<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSteamSaunaMassageGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('steam_sauna_massage_guests', function (Blueprint $table) {
            $table->id();
            $table->string('section')->default("massage");
            $table->string('fullname');
            $table->string('service');
            $table->string('fee');
            $table->integer('paid')->default(0);
            $table->integer('time')->default(1);
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
        Schema::dropIfExists('steam_sauna_massage_guests');
    }
}
