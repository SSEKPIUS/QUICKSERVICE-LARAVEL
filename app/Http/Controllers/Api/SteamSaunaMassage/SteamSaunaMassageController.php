<?php

namespace App\Http\Controllers\Api\SteamSaunaMassage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MassagePackages;
use App\Models\SteamSaunaPackages;
use App\Models\SteamSaunaMassageGuests;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Receipts\ReceiptsController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PDF;


class SteamSaunaMassageController extends Controller
{
    protected $out;
    protected $stockcontroller;
    protected $receiptsController;
    public function __construct(ReceiptsController $receiptsController)
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $this->receiptsController = $receiptsController;
    }

    public function searchSteamSaunaMassagePaginated(Request $request)
    {
        $guests = [];
        $sumPaid = null;
        $sumHeldPaid = null;
        $totalArrears = null;
        $input =  $request->all();
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull ($to = $input['DRange']['to']);
        if(is_null($from) && is_null($to)){
            $guests = SteamSaunaMassageGuests::where([
                ['fullname', 'like', '%' . $search . '%']
                ])
                ->orWhere([
                    ['service','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['fee','like', '%' . $search . '%']
                    ])
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        }else{
            try {

                $Dfrom = \DateTime::createFromFormat("M d, Y, H:i A", $from);
                $Dto = \DateTime::createFromFormat("M d, Y, H:i A", $to);
                if (!$Dfrom) {
                    throw new \UnexpectedValueException("Could not parse the date: $Dfrom");
                }
                if (!$Dto) {
                    throw new \UnexpectedValueException("Could not parse the date: $Dto");
                }
                $Dfrom = $Dfrom->format("Y-m-d");
                $Dto = $Dto->format("Y-m-d");
                $guests = SteamSaunaMassageGuests::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['fullname', 'like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['service','like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['fee','like', '%' . $search . '%']
                    ])
                ->orderBy('created_at', 'DESC')
                ->get();

                // asses accounts
                foreach ($guests as $guest) {
                    if ($guest->paid == true) {
                        $sumPaid += $guest->fee;
                    } else {
                        $sumHeldPaid += $guest->fee;
                    }
                    $totalArrears += $guest->fee;
                }
                $guests = $this->receiptsController->paginate($guests, 10, null, [], $request);
            } catch (\Throwable $th) {
                throw $th;
            }
        }
        return response([
            'result' => true,
            'guests' => $guests,
            'sumPaid' => $sumPaid,
            'sumHeldPaid' => $sumHeldPaid,
            'totalArrears' => $totalArrears
        ], 200);
    }

    public function delMassage(Request $request)
    {
        //$this->out->writeln("request>>>>" . $request)
        $this->validate($request, [
            'id' => 'required|integer|min:1',

        ]);
        $input =  $request->all();
        $ID = $input['id'];
        DB::beginTransaction();
        try {
            $op =  MassagePackages::where('id', $ID)->delete();
            DB::commit();
            return response([
                'result' => true,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delSauna(Request $request)
    {
        //$this->out->writeln("request>>>>" . $request);
        $this->validate($request, [
            'id' => 'required|integer|min:1',

        ]);
        $input =  $request->all();
        $ID = $input['id'];
        DB::beginTransaction();
        try {
            $op =  SteamSaunaPackages::where('id', $ID)->delete();
            DB::commit();
            return response([
                'result' => true,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function newMassagePackage(Request $request)
    {
        //$this->out->writeln("request>>>>" . $request);
        $this->validate($request, [
            'name' => 'required_with:fee|string|min:3|max:255',
            'fee' => 'required_with:time|integer|min:10000',
            'time' => 'required|integer|min:1|max:4'

        ]);
        $input =  $request->all();

        DB::beginTransaction();
        try {
            $MassagePackages = new MassagePackages;
            $MassagePackages->name = $input['name'];
            $MassagePackages->fee = $input['fee'];
            $MassagePackages->time = $input['time'];
            $MassagePackages->save();

            DB::commit();
            return response([
                'result' => true,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function newSteamSaunaPackage(Request $request)
    {
        //$this->out->writeln("request>>>>" . $request);
        $this->validate($request, [
            'name' => 'required_with:fee|string|min:3|max:255',
            'fee' => 'required|integer|min:10000',

        ]);
        $input =  $request->all();
        DB::beginTransaction();
        try {
            $SteamSaunaPackages = new SteamSaunaPackages;
            $SteamSaunaPackages->name = $input['name'];
            $SteamSaunaPackages->fee = $input['fee'];
            $SteamSaunaPackages->save();

            DB::commit();
            return response([
                'result' => true,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function guestSaunaMasagePaid(Request $request)
    {
        //$this->out->writeln("request>>>>" . $request);
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);

        $input =  $request->all();
        $id = $input['id'];
        $name = $input['fullname'];

        DB::beginTransaction();
        try {
            $guests =  SteamSaunaMassageGuests::where('id', $id)->first();
            $op = SteamSaunaMassageGuests::where('id', $id)->update(['paid' => true]);
            $message = "Successfully cleared $name";

            DB::commit();
            return response([
                'result' => $op,
                'message' => $message
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function newGuestSteamSaunMassage(Request $request)
    {
        $this->out->writeln("request>>>>" . $request);
        $this->validate($request, [
            'section' => 'required_with:fullname|string|min:3|max:255',
            'fullname' => 'required_with:service|string|min:3|max:255',
            'service' => 'required_with:fee|string|min:3|max:255',
            'fee' => 'required_with:time|integer|min:10000',
            'time' => 'required|integer|min:1'

        ]);
        $input =  $request->all();

        DB::beginTransaction();
        try {
            $guests = new SteamSaunaMassageGuests;
            $guests->section = $input['section'];
            $guests->fullname = $input['fullname'];
            $guests->service = $input['service'];
            $guests->fee = $input['fee'];
            $guests->time = $input['time'];
            $guests->save();
            DB::commit();
            return response([
                'result' => true,
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getSteamSaunaMassagePackages(Request $request){
        $massage  = MassagePackages::get();
        $steamsauna = SteamSaunaPackages::get();
        return response([
            'result' => true,
            'steamsauna' => $steamsauna,
            'massage' => $massage
        ], 200);
    }

    public function saunamassagereport()
    {
        $guests = SteamSaunaMassageGuests::limit(100)->get();
        view()->share('guests', $guests ? $guests : array()); // share data to view
        view()->share('sumPaid', "xxxxxxx");
        view()->share('sumHeldPaid', "xxxxxxxx");
        view()->share('totalArrears', "xxxxxxx");
        view()->share('Dfrom', "xxxxxxx");
        view()->share('Dto', "xxxxxxx");
        return view('saunamassagereport', $guests->toArray()); //sampleinvoice.blade.php
    }

    public function saunamassagereportpdf(Request $request)
    {
        $guests = [];
        $sumPaid = null;
        $sumHeldPaid = null;
        $totalArrears = null;
        $input =  $request->all();
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull ($to = $input['DRange']['to']);
        if(is_null($from) && is_null($to)){
            $guests = SteamSaunaMassageGuests::where([
                ['fullname', 'like', '%' . $search . '%']
                ])
                ->orWhere([
                    ['service','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['fee','like', '%' . $search . '%']
                    ])
                ->orderBy('created_at', 'DESC')->paginate(10);
        }else{
            try {

                $Dfrom = \DateTime::createFromFormat("M d, Y, H:i A", $from);
                $Dto = \DateTime::createFromFormat("M d, Y, H:i A", $to);
                if (!$Dfrom) {
                    throw new \UnexpectedValueException("Could not parse the date: $Dfrom");
                }
                if (!$Dto) {
                    throw new \UnexpectedValueException("Could not parse the date: $Dto");
                }
                $Dfrom = $Dfrom->format("Y-m-d");
                $Dto = $Dto->format("Y-m-d");
                $guests = SteamSaunaMassageGuests::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['fullname', 'like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['service','like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['fee','like', '%' . $search . '%']
                    ])
                ->get();

                // asses accounts
                foreach ($guests as $guest) {
                    if ($guest->paid == true) {
                        $sumPaid += $guest->fee;
                    } else {
                        $sumHeldPaid += $guest->fee;
                    }
                    $totalArrears += $guest->fee;
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        $currency_formarmatter = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
        view()->share('guests', $guests ? $guests : array());
        view()->share('sumPaid', $currency_formarmatter->formatCurrency($sumPaid, 'UGX'));
        view()->share('sumHeldPaid', $currency_formarmatter->formatCurrency($sumHeldPaid, 'UGX'));
        view()->share('totalArrears', $currency_formarmatter->formatCurrency($totalArrears, 'UGX'));
        view()->share('Dfrom', $Dfrom);
        view()->share('Dto', $Dto);
        $pdf = PDF::loadView('saunamassagereport', $guests ? $guests->toArray() : array()/*, $sumPaid, $sumHeldPaid, $totalArrears*/);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'pdfBackend' => 'auto',
            'debugCss' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        $pdf->render();
        return $pdf->download('Steam-Sauna-Massage.pdf');
    }

    public function saunamassagereportpdf_cron() {
        try {
            $guests = [];
            $sumPaid = null;
            $sumHeldPaid = null;
            $totalArrears = null;
            $Dfrom = (Carbon::now()->subDays(1))->format("Y-m-d");
            $Dto = $Dfrom;
            $guests = SteamSaunaMassageGuests::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"]
                ])->get();
            // asses accounts
            foreach ($guests as $guest) {
                if ($guest->paid == 1 || $guest->paid == true) {
                    $sumPaid += $guest->fee;
                } else {
                    $sumHeldPaid += $guest->fee;
                }
                $totalArrears += $guest->fee;
            }
            $currency_formarmatter = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
            view()->share('guests', $guests ? $guests : array());
            view()->share('sumPaid', $currency_formarmatter->formatCurrency($sumPaid, 'UGX'));
            view()->share('sumHeldPaid', $currency_formarmatter->formatCurrency($sumHeldPaid, 'UGX'));
            view()->share('totalArrears', $currency_formarmatter->formatCurrency($totalArrears, 'UGX'));
            view()->share('Date_', $Dfrom);
            $pdf = PDF::loadView('saunamassagereport', $guests ? $guests->toArray() : array()/*, $sumPaid, $sumHeldPaid, $totalArrears*/);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'pdfBackend' => 'auto',
                'debugCss' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);
            $pdf->render();
            $saved_file = $pdf->output();
            $this->log("info",  "Successfully created Steam Sauna Massage Report on " . now());
            return  [$saved_file, $sumPaid??0];
        } catch (\Throwable $th) {
            $this->log("critical", $th);
            return [null, 0];
        }
    }


    // #########################
    public function checkNull($val)
    {
        if (is_null($val)) return null;
        if ($val == 'null') return null;
        return $val;
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
