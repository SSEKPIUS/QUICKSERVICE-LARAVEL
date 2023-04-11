<?php

namespace App\Http\Controllers;

use App\Models\CashReturns;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CashReturnsController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function show(CashReturns $stock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function edit(CashReturns $stock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CashReturns $stock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\stock  $stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(CashReturns $stock)
    {
        //
    }

    public function revenuereportpdf_cron($Rooms=0, $Sauna_Masssage=0, $Bar_Kitchen=0){
        $line_labels = array();
        $line_rooms = array();
        $line_sauna_masage = array();
        $line_bar_kitchen = array();
        try {
            if ($Rooms !=0 || $Sauna_Masssage != 0 || $Bar_Kitchen != 0){
                $cash = new CashReturns;
                $cash->Rooms =  $Rooms;
                $cash->Sauna_Masssage = $Sauna_Masssage;
                $cash->Bar_Kitchen = $Bar_Kitchen;
                $cash->created_at = Carbon::now()->subDays(1);
                $cash->updated_at = Carbon::now()->subDays(1);
                $cash->save();
            }
    
            $Dfrom = Carbon::now()->subDays(30);
            $Dto = Carbon::now();
            $cash =  CashReturns::where([
                        ['created_at','>=', $Dfrom . " 00:00:00"],
                        ['created_at','<=', $Dto . " 23:59:59"]
                        ])->get();
    
            foreach($cash as $cash_){
                array_push($line_labels, '"' . $cash_->created_at->format("Y-m-d") .'"' );
                array_push($line_rooms, $cash_->Rooms);
                array_push($line_sauna_masage, $cash_->Sauna_Masssage);
                array_push($line_bar_kitchen, $cash_->Bar_Kitchen);
            }
        } catch (\Throwable $th) {
            $this->log("critical", $th);
        }
        return  [$line_labels, $line_rooms, $line_sauna_masage, $line_bar_kitchen];
    }

    private function log($mode, $message){
        try {
            switch ($mode) {
                case 'critical':
                    Log::critical( $message);
                    break;
                case 'info':
                    Log::info( $message);
                    break;
                default:
                    # code...
                    break;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
