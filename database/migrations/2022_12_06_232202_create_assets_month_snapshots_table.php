<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsMonthSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets_month_snapshots', function (Blueprint $table) {
            $table->increments('snap_id');
            $table->string('section');
            $table->string('mode');
            $table->string('category');
            $table->string('stocks');
            $table->string('unit');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        /*
             {#3706
                +"asset_id": "89",
                +"section": "STORE",
                +"category": "spices",
                +"stock1s_id": "52",
                +"stocks": "bread crumbs",
                +"unit": "tins",
                +"inbound": "3",
                +"outbound": "3",
                +"created_at": "2022-06-24 12:08:29",
                +"updated_at": "2022-11-13 18:13:18",
            }
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets_month_snapshots');
    }
}
