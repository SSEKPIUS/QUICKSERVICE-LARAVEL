<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_snapshots', function (Blueprint $table) {
            $table->increments('snap_id');
            $table->integer('asset_id');
            $table->string('section');
            $table->string('category');
            $table->string('stocks');
            $table->string('unit');
            $table->integer('opening_stock')->default(0);
            $table->integer('closing_stock')->default(0);
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
        Schema::dropIfExists('assets_snapshots');
    }
}
