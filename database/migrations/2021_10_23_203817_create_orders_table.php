<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('orders_id');
            $table->string('section');
            $table->foreignId('receipts_id')->references('receipts_id')->on('receipts')->onDelete('cascade');
            $table->string('OrderID');
            $table->string('Category');
            $table->string('dish');
            $table->string('Description')->nullable();
            $table->float('cost', 8, 2)->default(0);
            $table->integer('qty')->default(0);
            $table->string('SentFrom');
            $table->integer('status')->default(5); // 5 pending, 10 new  15 served 20 cancelling, 25 cancelled
            $table->string('reason')->nullable();
            $table->integer('uID');
            $table->string('Names');
            $table->string('destTbl')->nullable();
            $table->string('destRmn')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
