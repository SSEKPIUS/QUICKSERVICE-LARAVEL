<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\Stock\StockController;
use App\Http\Controllers\Api\Receipts\ReceiptsController;
use App\Utilities\verifyUserPermission;
use App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController;
use App\Http\Controllers\Api\Reception\ReceptionController;
use App\Http\Controllers\CashReturnsController;
use App\Models\reportsettings;
use App\Utilities\verifyEmailClass;
use Illuminate\Support\Carbon;
use App\Utilities\encryptDecrypt;
use stdClass;


class create_reports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_reports:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Daily Reports for mail and local storage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reportsettings =  reportsettings::get()->first();
        if (is_null($reportsettings)) {
            return true;
        }

        $receiptsController = new ReceiptsController;
        $verifyuserpermission = new verifyUserPermission;
        //$collection_reports = collect([]);
        $collection_reports = new StdClass();
        $collection_reports->files =  array();
        $reportsFilter =  array();

        if($reportsettings->CreateCleanerReports == 1) array_push($reportsFilter, "CLEANER");
        if($reportsettings->CreateHouseReports == 1) array_push($reportsFilter, "HOUSE");
        if($reportsettings->CreateAccountsReports == 1) array_push($reportsFilter, "ACCOUNTS");
        if($reportsettings->CreateSteamSaunaMassageReports == 1) array_push($reportsFilter, "STEAM-SAUNA-MASSAGE");
        if($reportsettings->CreateReceptionReports == 1) array_push($reportsFilter, "RECEPTION");
        if($reportsettings->CreateKitechReports == 1) array_push($reportsFilter, "KITCHEN");
        if($reportsettings->CreateServiceBarReports == 1) array_push($reportsFilter, "SERVICE-BAR");
        if($reportsettings->CreateSupervisorReports == 1) array_push($reportsFilter, "SUPERVISOR");

        if ( $reportsettings->CreateFinancialReports == 1) {
            [$saved_Receipt_Report, $sumPaid_bar_kitchen] =  $receiptsController ->receiptreportpdf_cron();
            $report = new StdClass();
            $report->name = 'Cash-bar-kitchen-report-'. (Carbon::now()->subDays(1))->format("Y-m-d") .'.pdf';
            $report->file = $saved_Receipt_Report;
            if($report->file) array_push($collection_reports->files, $report);

            $h = new ReceptionController($receiptsController);
            [$saved_Rooms_Report, $sumPaid_rooms] =  $h->roomsreportpdf_cron();
            $report = new StdClass();
            $report->name = 'Cash-Roooms-Report-'. (Carbon::now()->subDays(1))->format("Y-m-d") .'.pdf';
            $report->file = $saved_Rooms_Report;
            if($report->file) array_push($collection_reports->files, $report);

            $h = new SteamSaunaMassageController($receiptsController);
            [$saved_SteamSaunaMassage_Report, $sumPaid_sauna_masage] =  $h->saunamassagereportpdf_cron(); //sauna report
            $report = new StdClass();
            $report->name = 'Cash-Steam-Sauna-Massage-'. (Carbon::now()->subDays(1))->format("Y-m-d") .'.pdf';
            $report->file = $saved_SteamSaunaMassage_Report;
            if($report->file) array_push($collection_reports->files, $report);
        }
    
        $h = new StockController($receiptsController, $verifyuserpermission);
        $saved_Stock_Reports =  $h->stockreportpdf_cron($reportsFilter); //stock report
        if ($saved_Stock_Reports){
            foreach($saved_Stock_Reports as  $saved_Stock_Report){
                $report = new StdClass();
                $report->name = "Stock-$saved_Stock_Report->section-" . (Carbon::now()->subDays(1))->format("Y-m-d") . ".pdf";
                $report->file = $saved_Stock_Report->file;
                if($report->file) array_push($collection_reports->files, $report);
            }
        }
        $doughnut = [$sumPaid_rooms??0, $sumPaid_sauna_masage??0, $sumPaid_bar_kitchen??0]; //'Rooms','Sauna_Masssage','Bar_Kitchen'
        $h = new CashReturnsController();
        [$line_labels, $line_rooms, $line_sauna_masage, $line_bar_kitchen] =  $h->revenuereportpdf_cron($sumPaid_rooms, $sumPaid_sauna_masage, $sumPaid_bar_kitchen);
        if($collection_reports->files){
            $enc = new encryptDecrypt;
            $mail = new verifyEmailClass($enc);
            $mail->sendEmailReports($collection_reports,  $doughnut,  $line_labels??[],  $line_rooms??[],  $line_sauna_masage??[],  $line_bar_kitchen??[]);
        }
        return true;
    }
}
