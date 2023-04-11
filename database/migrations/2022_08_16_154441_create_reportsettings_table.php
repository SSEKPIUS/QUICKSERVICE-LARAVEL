<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportsettings', function (Blueprint $table) {
            $table->id();
            $table->boolean('CreateCleanerReports')->default(0);
            $table->boolean('CreateLaundryReports')->default(0);
            $table->boolean('CreateHouseReports')->default(0);
            $table->boolean('CreateAccountsReports')->default(0);
            $table->boolean('CreateSteamSaunaMassageReports')->default(0);
            $table->boolean('CreateReceptionReports')->default(0);
            $table->boolean('CreateKitechReports')->default(0);
            $table->boolean('CreateServiceBarReports')->default(0);
            $table->boolean('CreateSupervisorReports')->default(0);
            $table->boolean('CreateFinancialReports')->default(0);
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
        Schema::dropIfExists('reportsettings');
    }
}
