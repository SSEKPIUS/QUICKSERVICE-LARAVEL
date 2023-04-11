<?php

namespace App\Http\Controllers\Api\Receipts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\orders;
use App\Models\receipts;
use App\Models\User;
use App\Models\assetsevent;
use App\Models\assets;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
//use PDF;
use PDF;
use PhpParser\Node\Stmt\TryCatch;
use App\Utilities\LengthAwarePaginatorCustom;
class ReceiptsController extends Controller
{
    protected $out;
    public function __construct()
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $this->out->writeln("id>>>>" . app('request')->__get('id'));
    }

    public function getReceipts(Request $request)
    {
        $receipts = receipts::whereDate('created_at', Carbon::today())->get();
        list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_) = $this->ReceiptAppendOrdes($receipts);
        return response([
            'result' => true,
            'receipts' => $receiptsTmp
        ], 200);
    }

    public function getOrders(Request $request)
    {
        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section; 
        // 5 pending, 10 new  15 served 20 cancelling 25 cancelled
        $A = 10;
        $B = 20;
        $orders = orders::whereDate('created_at', Carbon::today())
            ->where('section', $UserSection)
            ->orderBy('receipts_id', 'DESC')->get();
        return response([
            'result' => true,
            'orders' => $orders
        ], 200);
    }

    public function changeorderState(Request $request)
    {
        $this->validate($request, [
            'id' => 'required_with:state|integer|min:1|digits_between: 1,5000',
            'state' => 'required|integer|min:1|digits_between: 1,5000'
        ]);
        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        DB::beginTransaction();
        try {
            $id = app('request')->__get('id');
            $state = app('request')->__get('state');
            $message = app('request')->__get('message');
            //check if reciept was paid 
            $order =  orders::where('orders_id', $id)->first();
            $receiptsid = $order->receipts_id;
            $section = $order->section;
            abort_unless(
                ($UserSection == $section),
                403,
                "This Order Belongs To $section You Cant Alter it"
            );

            $receipts =  receipts::where('receipts_id', intval($receiptsid))->first();
            abort_unless( // 5 unpaid, 10 paid
                (intval($receipts->status) == 5),
                403,
                'Reciept was Confirmed, you cant change the state of this order'
            );

            //check if order was cancelled
            abort_unless(  // 5 pending, 10 new  15 served 20 cancelling 25 cancelled
                (intval($order->status) <> 25),
                403,
                'Order was Cancelled, you cant change the state of this order'
            );

            //update the order
            $op =  orders::where('orders_id', $id)->update(['status' => $state, 'reason' => $message]);

            //check if order was served or cancelled and from SERVICE-BAR, then update SERVICE-BAR INVENTORY
            //get asset_id
            $tmpSection = $section;
            $tmpCategory = $order->Category;
            $tmpDish = $order->dish;
            $tmpQty = $order->qty;
            $tmpstockselectedUseOperation = "Sold";  //Sold, Used, Expired, Lost, Damaged
            // get curent selection
            $DBasset = assets::where(
                function ($query) use ($tmpSection, $tmpCategory, $tmpDish) {
                    $query->where('section', '=', $tmpSection)
                        ->Where('category', '=', $tmpCategory)
                        ->Where('stocks', '=', $tmpDish);
                }
            )->first();
            // 5 pending, 10 new  15 served 20 cancelling 25 cancelled
            if ($UserSection == 'SERVICE-BAR' ){ // 15 served
                if ($state == 15) {
                    abort_unless(
                        (!is_null($DBasset)),
                        403,
                        'Transaction Failed due to limited Stock in SERVICE-BAR Inventory'
                    );
    
                    $this->RunConsumeStock($request, $DBasset->asset_id, $tmpstockselectedUseOperation, $tmpQty);
                } else if ($state == 25){ // 25 served
                    $tmpstockselectedUseOperation = "Cancelled Sale";  //Sold, Used, Expired, Lost, Damaged
                    $this->RunUnConsumeStock($request, $DBasset->asset_id, $tmpstockselectedUseOperation, $tmpQty);
                }
            }

            DB::commit();
            return response([
                'result' => $op,
                'category' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }


    function RunConsumeStock($request, $asset_id, $stockselectedUseOperation, $stockOperationsData)
    {
        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;
    
        // get curent selection
        $DBasset = assets::where('asset_id', $asset_id)->first();
        $DBstocks = $DBasset->stocks;
        $DBunit = $DBasset->unit;
        $DBinbound = $DBasset->inbound;
        $DBoutbound = $DBasset->outbound;

        abort_unless(
            (intval($stockOperationsData) <= (intval($DBinbound) - intval($DBoutbound))), // get real asset balance
            403,
            'There is Limited Stock available!'
        );
        $op = assets::where('asset_id', $asset_id)->update(['outbound' => intval($DBoutbound) + intval($stockOperationsData)]);

        $this->addAssetEvent($UserSection, $userID, "$stockselectedUseOperation  $stockOperationsData $DBunit(s) of $DBstocks", $UserName);
        return true;
    }


    function RunUnConsumeStock($request, $asset_id, $stockselectedUseOperation, $stockOperationsData)
    {
        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;

        // get curent selection
        $DBasset = assets::where('asset_id', $asset_id)->first();
        $DBstocks = $DBasset->stocks;
        $DBunit = $DBasset->unit;
        $DBinbound = $DBasset->inbound;
        $DBoutbound = $DBasset->outbound;

        $op = assets::where('asset_id', $asset_id)->update(['inbound' => intval($DBinbound) + intval($stockOperationsData)]);

        $this->addAssetEvent($UserSection, $userID, "$stockselectedUseOperation  $stockOperationsData $DBunit(s) of $DBstocks", $UserName);
        return true;
    }

    public function requestCancelOrder(Request $request)
    { 
        $this->validate($request, [
            'id' => 'required_with:state|integer|min:1|digits_between: 1,5000',
            'state' => 'required|integer|min:1|digits_between: 1,5000'
        ]);
        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        DB::beginTransaction();
        try {
            $id = app('request')->__get('id');
            $state = app('request')->__get('state');
            //check if reciept was paid 
            $orders =  orders::where('orders_id', $id)->first();
            $receiptsid = $orders->receipts_id;
            $receipts =  receipts::where('receipts_id', intval($receiptsid))->first();
            abort_unless( // 5 unpaid, 10 paid
                (intval($receipts->status) == 5),
                403,
                'Reciept was Confirmed, you cant change the state of this order'
            );

            $op =  orders::where('orders_id', $id)->update(['status' => $state]);

            DB::commit();
            return response([
                'result' => $op,
                'message' => "$UserSection will soon cancel Order $orders->Category $orders->dish "
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getPaginatedOrders(Request $request)
    {
        $orders = [];
        $input =  $request->all();
        $selection = $this->checkNull($input['selection']);
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull ($to = $input['DRange']['to']);
        if(is_null($from) && is_null($to)){
            $orders = orders::where([
                    ['Category', 'like', '%' . $search . '%'],
                    ['section','like', '%' . $selection . '%']
                ])
                ->orWhere([
                    ['dish', 'like', '%' . $search . '%'],
                    ['section','like', '%' . $selection . '%']
                ])
                ->orWhere([
                    ['Description', 'like', '%' . $search . '%'],
                    ['section','like', '%' . $selection . '%']
                ])
                ->orderBy('created_at', 'DESC')->paginate(5);
        } else {
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
            $orders = orders::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['Category', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])                
            ->orWhere([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['dish', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])
            ->orWhere([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['Description', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])
            ->orderBy('created_at', 'DESC')->paginate(5);
        }
        return response([
            'result' => true,
            'orders' => $orders
        ], 200);
    }

    public function getPaginatedReceipts(Request $request)
    {
        $receipts = [];
        $sumPaid = null;
        $sumHeldPaid = null;
        $totalArrears = null;
        $input =  $request->all();
        $selection = $this->checkNull($input['selection']);
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull ($to = $input['DRange']['to']);

        if(is_null($from) && is_null($to)){
            //$receipts = receipts::all();
            $receipts = receipts::where([
                ['name', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
                ])
                ->orWhere([
                    ['receipts_id', 'like', '%' . $search . '%'],
                    ['section','like', '%' . $selection . '%']
                ])
                ->orderBy('created_at', 'DESC')->get();
            list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_)  = $this->ReceiptAppendOrdes($receipts);
            $receipts = $this->paginate($receiptsTmp, 5, null, [], $request);
        } else {
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
            $receipts = receipts::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['name', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])
            ->orWhere([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['receipts_id', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])
            ->orderBy('created_at', 'DESC')->get();

            list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_) = $this->ReceiptAppendOrdes($receipts);
            $receipts = $this->paginate($receiptsTmp, 5, null, [], $request);
            $totalArrears = $sumExpectedTmp_;
            $sumPaid = $sumActualTmp_;
            $sumHeldPaid = $sumActualHeldTmp_;
        }
        return response([
            'result' => true,
            'invoices' => $receipts,
            'sumPaid' => $sumPaid,
            'sumHeldPaid' => $sumHeldPaid,
            'totalArrears' => $totalArrears
        ], 200);
    }
    
    public function addReceipts(Request $request)
    {
        $this->validate($request, [
            'uID' => 'required_with:name|integer|min:1|digits_between: 1,5000',
            'name' => 'required_with:section|string|min:3|max:255',
            'section' => 'required_with:status|string|min:3|max:255',
            'status' => 'required_with:TTotal|integer|min:1|digits_between: 1,5000',
            'TTotal' => 'required_with:orders|integer|min:1|digits_between: 1,5000000',
            'orders' => 'required|array'
        ]);

        $input =  $request->all();
        abort_unless(
            (count($input['orders']) > 0),
            403,
            'Provide orders please.'
        );

        DB::beginTransaction();
        try {
            $receipts = new receipts;
            $receipts->uID = $input['uID'];
            $receipts->name = $input['name'];
            $receipts->section = $input['section'];
            $receipts->status = $input['status'];
            $receipts->TTotal = $input['TTotal'];
            $receipts->save();
            $receipts =  DB::table('receipts')->latest('receipts_id')->first();
            $receipts_id = $receipts->receipts_id;
            abort_unless(
                ($receipts_id != null),
                403,
                'Failed Receipt'
            );

            foreach ($input['orders'] as $value) {
                $orders = new orders;
                $orders->section = $value['section'];
                $orders->receipts_id = $receipts_id;
                $orders->OrderID = 'B' . strval(rand(1000, 2000));
                $orders->Category = $value['category'];
                $orders->dish = $value['dish'];
                $orders->Description = $value['description'];
                $orders->cost = $value['price'];
                $orders->qty = $value['qty'];
                $orders->SentFrom = $value['sentfrom'];
                $orders->status = $value['status'];
                $orders->uID = $value['uID'];
                $orders->Names = $value['names'];
                $value['destTbl'] ? ($orders->destTbl = 'TB' . $value['destTbl']) : null;
                $value['destRmn'] ? ($orders->destRmn = 'RM' . $value['destRmn']) : null;
                $orders->save();
            }

            DB::commit();
            return response([
                'result' => true,
                'category' => app('request')->__get('stocks')
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateReceipts(Request $request)
    {
        $this->validate($request, [
            'id' => 'required_with:mode|integer|min:1|digits_between: 1,5000',
            'mode' => 'required|string|min:3|max:255'
        ]);

        $input =  $request->all();
        $id = $input['id'];
        $mode = $input['mode'];

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        DB::beginTransaction();
        try {
            $op = false;
            $message = "";
            switch ($mode) {
                case 'CONFIRM':
                    //check if reciept has pending orders 
                    $orders =  orders::where('receipts_id', $id)->get();
                    $total = 0;
                    foreach ($orders as $order) {
                        $status = $order->status;
                        abort_unless(
                            (($status == 15) || ($status == 25) /*false*/), // 5 pending, 10 new  15 served 20 cancelling, 25 cancelled
                            403,
                            'There are Uncleared Orders!'
                        );

                        if ($status == 15) {
                            $total += intval($order->cost);
                        } // clear cancelled orders
                    }
                    $op =  receipts::where('receipts_id', $id)->update(['TTotal' => $total, 'status' => 10]);
                    $message = "Successfully Signed Off Invoice ID:$id";
                    break;
                case 'CLOSE':
                    //check if reciept has pending orders 
                    $orders =  orders::where('receipts_id', $id)->get();
                    foreach ($orders as $order) {
                        $status = $order->status;
                        abort_unless(
                            (($status == 15) || ($status == 25) /*false*/), // 5 pending, 10 new  15 served 20 cancelling, 25 cancelled
                            403,
                            'There are Uncleared Orders!'
                        );
                    }
                    $op =  receipts::where('receipts_id', $id)->update(['status' => 15]);
                    $message = "Successfully Closed Invoice ID:$id";
                    break;
                default:
                    abort_unless(
                        (false),
                        403,
                        'Operation MODE is missing'
                    );
                    break;
            }
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

    public function receiptreport(Request $request){
        $receipts = receipts::limit(100)->get();
        list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_) = $this->ReceiptAppendOrdes($receipts);
        view()->share('receipts', $receiptsTmp); // share data to view
        view()->share('sumPaid', "xxxxxxx");
        view()->share('sumHeldPaid', "xxxxxxxx");
        view()->share('totalArrears', "xxxxxxx");
        $Dfrom = (Carbon::now()->subDays(1))->format("Y-m-d");
        $Dto = Carbon::now()->format("Y-m-d");
        view()->share('Dfrom', $Dfrom);
        view()->share('Dto',  $Dto);
        return view('receiptsreport', $receiptsTmp); //sampleinvoice.blade.php
    }

    public function receiptreportpdf(Request $request)
    {
        $receipts = [];
        $sumPaid = null;
        $sumHeldPaid = null;
        $totalArrears = null;
        $input =  $request->all();
        $selection = $this->checkNull($input['selection']);
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull ($to = $input['DRange']['to']);

        if(is_null($from) && is_null($to)){
            //$receipts = receipts::all();
            $receipts = receipts::where([
                ['name', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
                ])
                ->orWhere([
                    ['receipts_id', 'like', '%' . $search . '%'],
                    ['section','like', '%' . $selection . '%']
                ])
                ->orderBy('created_at', 'DESC')->get();
            list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_)  = $this->ReceiptAppendOrdes($receipts);
            $receipts = $this->paginate($receiptsTmp, 5, null, [], $request);
        } else {
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
            $receipts = receipts::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['name', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])
            ->orWhere([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"],
                ['receipts_id', 'like', '%' . $search . '%'],
                ['section','like', '%' . $selection . '%']
            ])
            ->orderBy('created_at', 'DESC')->get();

            list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_) = $this->ReceiptAppendOrdes($receipts);
            $totalArrears = $sumExpectedTmp_;
            $sumPaid = $sumActualTmp_;
            $sumHeldPaid = $sumActualHeldTmp_;
        }

        $currency_formarmatter = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
        view()->share('receipts', $receiptsTmp ? $receiptsTmp : array());
        view()->share('sumPaid', $currency_formarmatter->formatCurrency($sumPaid, 'UGX'));
        view()->share('sumHeldPaid', $currency_formarmatter->formatCurrency($sumHeldPaid, 'UGX'));
        view()->share('totalArrears', $currency_formarmatter->formatCurrency($totalArrears, 'UGX'));
        view()->share('Dfrom', $Dfrom);
        view()->share('Dto', $Dto);
        $pdf = PDF::loadView('receiptsreport', $receiptsTmp ? $receiptsTmp : array()/*, $sumPaid, $sumHeldPaid, $totalArrears*/);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'pdfBackend' => 'auto',
            'debugCss' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
        $pdf->render();
        return $pdf->download('bar-kitchen-report.pdf');
    }

    public function receiptreportpdf_cron(){
        try {
            $receipts = [];
            $sumPaid = null;
            $sumHeldPaid = null;
            $totalArrears = null;
            $Dfrom = (Carbon::now()->subDays(1))->format("Y-m-d");
            $Dto =  $Dfrom;
            $receipts = receipts::where([
                ['created_at','>=', $Dfrom . " 00:00:00"],
                ['created_at','<=', $Dto . " 23:59:59"]
            ])
            ->orderBy('created_at', 'DESC')->get();
    
            list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_) = $this->ReceiptAppendOrdes($receipts);
            $totalArrears = $sumExpectedTmp_;
            $sumPaid = $sumActualTmp_;
            $sumHeldPaid = $sumActualHeldTmp_;
    
            $currency_formarmatter = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
            view()->share('receipts', $receiptsTmp ? $receiptsTmp : array());
            view()->share('sumPaid', $currency_formarmatter->formatCurrency($sumPaid, 'UGX'));
            view()->share('sumHeldPaid', $currency_formarmatter->formatCurrency($sumHeldPaid, 'UGX'));
            view()->share('totalArrears', $currency_formarmatter->formatCurrency($totalArrears, 'UGX'));
            view()->share('Date_', $Dfrom);
            $pdf = PDF::loadView('receiptsreport', $receiptsTmp ? $receiptsTmp : array()/*, $sumPaid, $sumHeldPaid, $totalArrears*/);
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
            $this->log("info",  "Successfully created Receipts Report on " . now());
            return   [$saved_file, $sumPaid??0];
        } catch (\Throwable $th) {
            $this->log("critical", $th);
            return [null, 0];
        }
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function addAssetEvent($section, $userID, $event, $onrequestof)
    {
        $DBasset = new assetsevent;
        $DBasset->section =  $section;
        $DBasset->user = $userID;
        $DBasset->event = $event;
        $DBasset->onrequestof = $onrequestof;
        $DBasset->department = $section;
        $DBasset->save();
    }

    public function checkNull($val)
    {
        if (is_null($val)) return null;
        if ($val=='null') return null;
        return $val;
    }

    public function paginate($items, $perPage = 5, $page = null, $options = [], $request = null)
    {
        $url = env('APP_URL') . ':' . env('SERVER_PORT') . '/' . $request->path();
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginatorCustom($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options, $url);
    }

    private function ReceiptAppendOrdes($receipts)
    {
        $sumExpectedTmp_ = 0;
        $sumActualTmp_ = 0;
        $sumActualHeldTmp_ = 0;

        $receiptsTmp = array();
        foreach ($receipts as $receipt) {
            $sumActualTmpPass = true;
            $receipts_id  = $receipt->receipts_id;
            $orders = orders::where('receipts_id', $receipts_id)->get();
            $receiptObject = new receiptObject();
            $receiptObject->set_receipts_id($receipt->receipts_id);
            $receiptObject->set_uID($receipt->uID);
            $receiptObject->set_name($receipt->name);
            $receiptObject->set_section($receipt->section);
            $receiptObject->set_status($receipt->status);
            $receiptObject->set_TTotal($receipt->TTotal);
            $receiptObject->set_created_at($receipt->created_at);
            $receiptObject->set_updated_at($receipt->updated_at);
            $receiptObject->set_orders($orders);
            array_push($receiptsTmp, $receiptObject);
            // 5 pending, 10 new  15 served 20 cancelling, 25 cancelled
            foreach ($orders as $order) {
                if ($order->status != 15) { // if invoice has  pending orders 
                    $sumActualTmpPass = false;
                    break;
                }
            }

            if ($sumActualTmpPass == true) { // if all orders on receipt served 
                $sumActualTmp_ += $receipt->TTotal;
            } else {
                $sumActualHeldTmp_ += $receipt->TTotal;
            }

            $sumExpectedTmp_ += $receipt->TTotal; // all invoices
        }
        return array($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_);  //  list($receiptsTmp, $sumExpectedTmp_, $sumActualTmp_, $sumActualHeldTmp_) 
    }

    public function sampleInvoice()
    {
        //$receipts = receipts::all();
        $receipts = receipts::limit(100)->get();
        view()->share('receipts', $receipts); // share data to view
        //return view('sampleinvoice', compact('receipts')); //sampleinvoice.blade.php
        return view('sampleinvoice', $receipts->toArray()); //sampleinvoice.blade.php
    }

    public function sampleInvoicePdf()
    {
        // retreive all records from db
        $receipts = receipts::limit(20)->get();
        view()->share('receipts', $receipts); // share data to view
        $pdf = PDF::loadView('sampleinvoice', $receipts->toArray()); // selecting PDF view
        //$pdf = \App::make('sampleinvoice');

        //$pdf->setPaper('L', 'landscape');
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'pdfBackend' => 'auto',
            'debugCss' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);

        $pdf->render(); // Render the HTML as PDF
        //$html = view('sampleinvoice')->render();
        //$pdf->loadHTML($html);
        /*
rootDir: "{app_directory}/vendor/dompdf/dompdf"
tempDir: "/tmp" (available in config/dompdf.php)
fontDir: "{app_directory}/storage/fonts/" (available in config/dompdf.php)
fontCache: "{app_directory}/storage/fonts/" (available in config/dompdf.php)
chroot: "{app_directory}" (available in config/dompdf.php)
logOutputFile: "/tmp/log.htm"
defaultMediaType: "screen" (available in config/dompdf.php)
defaultPaperSize: "a4" (available in config/dompdf.php)
defaultFont: "serif" (available in config/dompdf.php)
dpi: 96 (available in config/dompdf.php)
fontHeightRatio: 1.1 (available in config/dompdf.php)
isPhpEnabled: false (available in config/dompdf.php)
isRemoteEnabled: true (available in config/dompdf.php)
isJavascriptEnabled: true (available in config/dompdf.php)
isHtml5ParserEnabled: false (available in config/dompdf.php)
isFontSubsettingEnabled: false (available in config/dompdf.php)
debugPng: false
debugKeepTemp: false
debugCss: false
debugLayout: false
debugLayoutLines: true
debugLayoutBlocks: true
debugLayoutInline: true
debugLayoutPaddingBox: true
pdfBackend: "CPDF" (available in config/dompdf.php)
pdflibLicense: ""
adminUsername: "user"
adminPassword: "password"
        */



        //Storage::put('public/pdf/invoice.pdf', $pdf->output());
        //return $pdf->download('sampleinvoice.pdf');  // download PDF file with download method
        return $pdf->stream(); //// Output the generated PDF to Browser
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


class receiptObject
{
    // Properties
    public $receipts_id;
    public $uID;
    public $name;
    public $section;
    public $status;
    public $TTotal;
    public $created_at;
    public $updated_at;
    public $orders;

    // Methods
    function set_receipts_id($receipts_id)
    {
        $this->receipts_id = $receipts_id;
    }
    function get_receipts_id()
    {
        return $this->receipts_id;
    }

    function set_uID($uID)
    {
        $this->uID = $uID;
    }
    function get_uID()
    {
        return $this->uID;
    }

    function set_name($name)
    {
        $this->name = $name;
    }
    function get_name()
    {
        return $this->name;
    }

    function set_section($section)
    {
        $this->section = $section;
    }
    function get_section()
    {
        return $this->section;
    }

    function set_status($status)
    {
        $this->status = $status;
    }
    function get_status()
    {
        return $this->status;
    }

    function set_TTotal($TTotal)
    {
        $this->TTotal = $TTotal;
    }
    function get_TTotal()
    {
        return $this->TTotal;
    }

    function set_created_at($created_at)
    {
        $this->created_at = $created_at;
    }
    function get_created_at()
    {
        return $this->created_at;
    }

    function set_updated_at($updated_at)
    {
        $this->updated_at = $updated_at;
    }
    function get_updated_at()
    {
        return $this->updated_at;
    }

    function set_orders($orders)
    {
        $this->orders = $orders;
    }
    function get_orders()
    {
        return $this->orders;
    }
}
