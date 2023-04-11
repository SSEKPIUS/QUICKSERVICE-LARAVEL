<?php

namespace App\Http\Controllers\Api\Reception;

use App\Http\Controllers\Controller;
use App\Models\hotel_guests;
use App\Models\hotel_rooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Receipts\ReceiptsController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PDF;

class ReceptionController extends Controller
{
    protected $out;
    protected $stockcontroller;
    protected $receiptsController;
    public function __construct(ReceiptsController $receiptsController)
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $this->receiptsController = $receiptsController;
         //$this->out->writeln("request>>>>" . $request);
    }

    public function  guestsroomsPaginated(Request $request)
    {
        $per_page =  app('request')->__get('per_page');
        $guests = hotel_guests::orderBy('id', 'desc')->limit(30)->get();
        $rooms = hotel_rooms::orderBy('occupied', 'DESC')->paginate($per_page ? $per_page : 10);
        return response([
            'result' => true,
            'guests' => $guests,
            'rooms' => $rooms
        ], 200);
    }

    public function  guestsrooms (Request $request)
    {
        $guests = hotel_guests::orderBy('id', 'desc')->limit(30)->get();
        $rooms = hotel_rooms::orderBy('occupied', 'DESC')->get();
        return response([
            'result' => true,
            'guests' => $guests,
            'rooms' => $rooms
        ], 200);
    }

    public function searchSortGuests(Request $request)
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
            $guests = hotel_guests::where([
                ['fullname', 'like', '%' . $search . '%']
                ])
                ->orWhere([
                    ['idNum','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['aob','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['email','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['roomNo','like', '%' . $search . '%']
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
                $guests = hotel_guests::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['fullname', 'like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['idNum','like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['aob','like', '%' . $search . '%']
                    ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['email','like', '%' . $search . '%']
                    ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['roomNo','like', '%' . $search . '%']
                    ])
                ->orderBy('created_at', 'DESC')
                ->get();

                // asses accounts
                foreach ($guests as $guest) {
                    if ($guest->paid == true) {
                        $roomNo = $guest->roomNo;
                        $room =  hotel_rooms::where('roomNo', $roomNo)->first();
                        $sumPaid += $room->fee;
                    }
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

    public function newGuest (Request $request) {
        $this->validate($request, [
            'fullname' => 'required_with:idType|string|min:3|max:255',
            'idType' => 'required_with:idNum|string|min:3|max:255',
            'idNum' => 'required_with:aob|string|min:3|max:255',
            'aob' => 'required:rdays|string|min:3|max:255',
            'rdays' => 'required:Room|integer|min:1',
            'Room' => 'required|array'

        ]);
        $input =  $request->all();

        DB::beginTransaction();
        try {
            $roomNo = $input['Room']['roomNo'];
            $guests = new hotel_guests;
            $guests->fullname = $input['fullname'];
            $guests->idType = $input['idType'];
            $guests->idNum = $input['idNum'];
            $guests->email = $input['email'];
            $guests->aob = $input['aob'];
            $guests->roomNo = $roomNo;
            $guests->rdays = $input['rdays'];
            $guests->save();
            //update Room Number
            $op = hotel_rooms::where('roomNo', $roomNo)->update(['updated_at' => now(), 'occupied' => true]);

            DB::commit();
            return response([
                'result' => true,
                'message' => 'success'
            ], 200);
        }catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function newRoom(Request $request)
    {
        $this->validate($request, [
            'type' => 'required_with:roomNo|string|min:3|max:255',
            'roomNo' => 'required_with:beds|string|min:3|max:255',
            'beds' => 'required_with:fee|integer|min:1|max:4',
            'fee' => 'required|integer|min:10000',
        ]);
        $input =  $request->all();

        DB::beginTransaction();
        try {
            $rooms = new hotel_rooms;
            $rooms->type = $input['type'];
            $rooms->roomNo = $input['roomNo'];
            $rooms->beds = $input['beds'];
            $rooms->fee = $input['fee'];
            $rooms->save();

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

    public function delRoom (Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);
        $input =  $request->all();
        $ID = $input['id'];
        DB::beginTransaction();
        try {
            $room =  hotel_rooms::where('id', $ID)->first();
            $guests = hotel_guests::where('roomNo', $room->roomNo )->first();
            abort_unless(
                (!$guests),
                403,
                "The room can't be deleted ounce used!"
             );
            $op =  hotel_rooms::where('id', $ID)->delete();
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

    public function newGuestcheckOut(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);

        $input =  $request->all();
        $id = $input['id'];
        $name = $input['fullname'];

        DB::beginTransaction();
        try {
            $guests =  hotel_guests::where('id', $id)->first();
            $paid = $guests->paid;
            $roomNo = $guests->roomNo; 
            abort_unless(
                ( $paid == true),
                403,
                'The guest has  Uncleared fees!'
            );
            $op = hotel_guests::where('id', $id)->update(['leaveDate' => now(), 'status' => 'inactive']);
            $message = "Successfully Checked Out $name";
            $op = hotel_rooms::where('roomNo', $roomNo)->update(['updated_at' => now(), 'occupied' => false]);

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

    public function newGuestPaid (Request $request){
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);

        $input =  $request->all();
        $id = $input['id'];
        $name = $input['fullname'];

        DB::beginTransaction();
        try {
            $guests =  hotel_guests::where('id', $id)->first();
            $op = hotel_guests::where('id', $id)->update(['paid' => true, 'status' =>'active', 'checkIn' => now()]);
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

    public function newGuestClose (Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|min:1',
        ]);

        $input =  $request->all();
        $id = $input['id'];
        $name = $input['fullname'];

        DB::beginTransaction();
        try {
            $guests =  hotel_guests::where('id', $id)->first();
            $roomNo = $guests->roomNo; 
            $op = hotel_guests::where('id', $id)->update(['status' => 'inactive']);
            $message = "Successfully closed $name";

            //update Room Number
            $op = hotel_rooms::where('roomNo', $roomNo)->update(['updated_at' => now(), 'occupied' => false]);

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

    public function roomsreport(){
        $guests = hotel_guests::limit(100)->get();
        view()->share('guests', $guests); // share data to view
        view()->share('sumPaid', "xxxxxxx");
        view()->share('sumHeldPaid', "xxxxxxxx");
        view()->share('totalArrears', "xxxxxxx");
        view()->share('Dfrom', "xxxxxxx");
        view()->share('Dto', "xxxxxxx");
        return view('roomsreport', $guests->toArray()); //sampleinvoice.blade.php
    }

    public function roomsreportpdf(Request $request){
        $guests = [];
        $sumPaid = null;
        $sumHeldPaid = null;
        $totalArrears = null;
        $input =  $request->all();
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull ($to = $input['DRange']['to']);

        if(is_null($from) && is_null($to)){
            $guests = hotel_guests::where([
                ['fullname', 'like', '%' . $search . '%']
                ])
                ->orWhere([
                    ['idNum','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['aob','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['email','like', '%' . $search . '%']
                    ])
                ->orWhere([
                    ['roomNo','like', '%' . $search . '%']
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
                $guests = hotel_guests::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['fullname', 'like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['idNum','like', '%' . $search . '%']
                ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['aob','like', '%' . $search . '%']
                    ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['email','like', '%' . $search . '%']
                    ]) 
                ->orWhere([
                    ['created_at','>=', $Dfrom . " 00:00:00"],
                    ['created_at','<=', $Dto . " 23:59:59"],
                    ['roomNo','like', '%' . $search . '%']
                    ])
                ->get();

                // asses accounts
                foreach ($guests as $guest) {
                    $room =  hotel_rooms::where('roomNo', $guest->roomNo)->first();
                    $cash_value = $room->fee * $guest->rdays;
                    if ($guest->paid == true) {
                        $sumPaid +=  $cash_value;
                    } else {
                        $sumHeldPaid +=  $cash_value;
                    }
                    $totalArrears +=  $cash_value;
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
        $pdf = PDF::loadView('roomsreport', $guests ? $guests->toArray() : array()/*, $sumPaid, $sumHeldPaid, $totalArrears*/);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'pdfBackend' => 'auto',
            'debugCss' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        $pdf->render();
        return $pdf->download('Roooms-Report.pdf');
    }

    public function roomsreportpdf_cron(){
        try {
            $guests = [];
            $sumPaid = null;
            $sumHeldPaid = null;
            $totalArrears = null;
            $Dfrom = (Carbon::now()->subDays(1))->format("Y-m-d");
            $Dto = $Dfrom;
            $guests = hotel_guests::where([
                ['checkIn','>=', $Dfrom . " 00:00:00"],
                ['checkIn','<=', $Dto . " 23:59:59"]
                ])->get();
            // asses accounts
            foreach ($guests as $guest) {
                $room =  hotel_rooms::where('roomNo', $guest->roomNo)->first();
                $cash_value = $room->fee * $guest->rdays;
                if ($guest->paid == true) {
                    $sumPaid +=  $cash_value;
                } else {
                    $sumHeldPaid +=  $cash_value;
                }
                $totalArrears +=  $cash_value;
            }
            $currency_formarmatter = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
            view()->share('guests', $guests ? $guests : array());
            view()->share('sumPaid', $currency_formarmatter->formatCurrency($sumPaid, 'UGX'));
            view()->share('sumHeldPaid', $currency_formarmatter->formatCurrency($sumHeldPaid, 'UGX'));
            view()->share('totalArrears', $currency_formarmatter->formatCurrency($totalArrears, 'UGX'));
            view()->share('Date_', $Dfrom);
            $pdf = PDF::loadView('roomsreport', $guests ? $guests->toArray() : array()/*, $sumPaid, $sumHeldPaid, $totalArrears*/);
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
            $this->log("info",  "Successfully created Rooms Report on " . now());
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
