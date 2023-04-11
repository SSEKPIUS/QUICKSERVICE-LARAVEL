<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_guests', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('idType');
            $table->string('idNum');
            $table->string('email');
            $table->string('aob');
            $table->boolean('paid')->default(false);
            $table->string('checkIn')->nullable();
            $table->string('leaveDate')->nullable();
            $table->string('status')->default('checked');
            $table->integer('roomNo')->references('roomNo')->on('hotel_rooms')->onDelete('abort');  
            $table->integer('rdays')->default(1);  
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
        Schema::dropIfExists('hotel_guests');
    }
}
