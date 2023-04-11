<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

use App\Models\stock;
use App\Models\stock1;
use App\Models\stock2;
use App\Models\assets;
use App\Models\User;
use App\Models\assetsevent;
use App\Models\reportsettings;
use App\Models\reportemails;

use App\Models\assets_snapshot;
use Illuminate\Support\Facades\Log;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

use stdClass;
use App\Http\Controllers\Api\Receipts\ReceiptsController;

use App\Utilities\verifyUserPermission;
use Nette\IOException;
use PhpParser\Node\Stmt\TryCatch;

use function GuzzleHttp\Promise\each;
use function PHPUnit\Framework\throwException;

class StockController extends Controller
{
    protected $verifyuserpermission;
    protected $out;
    protected $receiptsController;
    public function __construct(ReceiptsController $receiptsController, verifyUserPermission $verifyuserpermission)
    {
        $this->verifyuserpermission = $verifyuserpermission;
        $this->receiptsController = $receiptsController;
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
        //$this->out->writeln("id>>>>" . app('request')->__get('id'));
    }

    private function getIDfromReq()
    {
        $id = app('request')->__get('id');
        abort_unless(
            ($id !== null || !is_int($id)),
            403,
            json_encode([
                'state' => 'user_id',
                'message' => 'This user ID is poorly formated'
            ]),
        );
        return $id;
    }

    public function getAllInventoryMenu(Request $request)
    {
        $menuMajor = stock::all();
        $menuMinor = stock1::all();
        $units = stock2::all();
        return response([
            'result' => true,
            'menuMajor' => $menuMajor,
            'menuMinor' => $menuMinor,
            'units' => $units
        ], 200);
    }

    public function addStockMenu(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|string|min:3|max:255'
        ]);

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;
        $type = app('request')->__get('type');

        DB::beginTransaction();
        $object = new stdClass();
        try {
            if ($type == 'Major') {
                $rules = [
                    'category' => 'required|string|min:3|max:255'
                ];
                $validator = Validator::make($request->all(), $rules);
                abort_unless(
                    ($validator),
                    403,
                    json_encode([
                        'state' => 'failed',
                        'message' => 'category fields are Required'
                    ]),
                );
                $stock_ = stock::where('category', app('request')->__get('category'))->first();
                if ($stock_ == null) {
                    $stock = new stock;
                    $stock->category = app('request')->__get('category');
                    $stock->save();
                    //log
                    $object->Category = app('request')->__get('category');
                } else {
                    abort_unless(
                        (false),
                        403,
                        'category already Exists',
                    );
                }
            } elseif ($type == 'Minor') {
                $this->validate($request, [
                    'id' => 'required_with:category|integer|min:1|digits_between: 1,5000',
                    'category' => 'required|string|min:3|max:255'
                ]);

                $stock = new stock1;
                $stock->stocks_id = app('request')->__get('id');
                $stock->category = app('request')->__get('category');
                $stock->save();
                //log
                $object->ID = app('request')->__get('id');
                $object->Category = app('request')->__get('category');
            } elseif ($type == 'Unit') {
                $rules = [
                    'category' => 'required|string|min:3|max:255'
                ];
                $validator = Validator::make($request->all(), $rules);
                abort_unless(
                    ($validator),
                    403,
                    json_encode([
                        'state' => 'failed',
                        'message' => 'category fields are Required'
                    ]),
                );
                $unit = new stock2;
                $unit->category = app('request')->__get('category');
                $unit->save();
                //log
                $object->Category = app('request')->__get('category');
            }
            $this->addAssetEvent($UserSection, $userID, "Added Stock Menu ($type category) Log: " .   str_replace(' ', '', print_r($object, true)), $UserName);

            DB::commit();
            return response([
                'result' => true
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delStockMenu(Request $request)
    {

        $this->validate($request, [
            'type' => 'required_with:id|string|min:3|max:255',
            'id' => 'required|integer|min:1|digits_between: 1,5000'
        ]);

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;

        $type = app('request')->__get('type');
        $id = $this->getIDfromReq();

        DB::beginTransaction();
        $object = new stdClass();
        try {
            if ($type == 'Major') {
                $smenu = stock1::where('stocks_id', $id)->first();
                abort_unless(
                    ($smenu == null),
                    403,
                    'Major stock has Minor category'
                );
                $smenu = stock::where('stocks_id', $id)->first();
                $category = $smenu->category;
                $op =  stock::where('stocks_id', $id)->delete();
                //log
                $object->Category = $smenu->category;
            } elseif ($type == 'Minor') {
                $smenu = assets::where('stock1s_id', $id)->first();
                abort_unless(
                    ($smenu == null),
                    403,
                    'Minor stock has Assets assigned'
                );
                $smenu = stock1::where('stock1s_id', $id)->first();
                $category = $smenu->category;
                $op =  stock1::where('stock1s_id', $id)->delete();
                //log
                $object->Category = $smenu->category;
            } elseif ($type == 'Unit') {
                $smenu = stock2::where('stock2s_id', $id)->first();
                $tmp = assets::where('unit', $smenu->category)->first();
                abort_unless(
                    ($tmp == null),
                    403,
                    'This Unit has Assets assigned'
                );
                $category = $smenu->category;
                $op =  stock2::where('stock2s_id', $id)->delete();
                //log
                $object->Quantities = $smenu->category;
            }

            $this->addAssetEvent($UserSection, $userID, "Deleted Stock Menu ($type category)  Log: " .   str_replace(' ', '', print_r($object, true)), $UserName);
            DB::commit();
            return response([
                'result' => $op,
                'category' => $category
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delStock(Request $request)
    {
        $this->validate($request, [
            'type' => 'required_with:id|string|min:3|max:255',
            'id' => 'required_with:email|integer|min:1|digits_between: 1,5000',
            'email' => 'required_with:password|string|min:3|max:255',
            'password' => 'required|string|min:3|max:255'
        ]);

        $supervisor_email = $this->verifyuserpermission->authProcessWithSupervisor();

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;

        abort_unless(
            (strtolower($UserSection) == 'supervisor'),
            403,
            'Only SUPERVISOR Account can Delete Stock'
        );

        $type = app('request')->__get('type');
        $id = $this->getIDfromReq();

        DB::beginTransaction();
        $object = new stdClass();
        try {
            $asset = assets::where('asset_id', $id)->first();
            abort_unless(
                ($type == $asset->section),
                403,
                json_encode([
                    'state' => 'failed',
                    'message' => 'Asset ID does not match Section'
                ]),
            );
            if ($asset->section != "STORE") { // restore stock
                $inbound = $asset->inbound;
                $outbound = $asset->outbound;
                $diff = $inbound - $outbound;
                $asset_store = assets::where([
                    ['section', '=', 'STORE'],
                    ['category', '=', $asset->category],
                    ['stock1s_id', '=', $asset->stock1s_id],
                    ['stocks', '=', $asset->stocks],
                    ['unit', '=', $asset->unit]
                ])->first();
                if ($asset_store != null) {
                    $id_ = $asset_store->asset_id;
                    $inbound_ = $asset_store->inbound;
                    $inbound_  += $diff;
                    $DBunit = $asset_store->unit;
                    $DBstocks = $asset_store->stocks;
                    $op =  assets::where('asset_id', $id_)->update(['inbound' => $inbound_]);
                    $this->addAssetEvent($UserSection, $userID, "Deleted  Stock ($type  $DBstocks)   Log::" .  str_replace(' ', '', print_r($object, true)). " :: Authorised by $supervisor_email", $UserName);
                    $this->addAssetEvent($UserSection, $userID, "Returned  $diff $DBunit(s) of $DBstocks to STORE's Stock", $asset->section);
                }
            }

            $category = $asset->category;
            $section = $asset->section;
            $stocks = $asset->stocks;
            $op =  assets::where('asset_id', $id)->delete();
            //log
            $object->Data = $asset;
            $this->addAssetEvent($UserSection, $userID, "Deleted  Stock ( $section-$category-$stocks) Log: " .  str_replace(' ', '', print_r($object, true)) . " :: Authorised by $supervisor_email", $UserName);
            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getAllInventoryStock(Request $request)
    {
        $assets = [];
        $input =  $request->all();
        $selection = $this->checkNull($input['selection']);
        $search =  $this->checkNull($input['search']);

        $assets = assets::where([
            ['stocks', 'like', '%' . $search . '%'],
            ['section', 'like', '%' . $selection . '%']
        ])
            ->orWhere([
                ['category', 'like', '%' . $search . '%'],
                ['section', 'like', '%' . $selection . '%']
            ])
            ->orderBy('created_at', 'DESC')->get();

        return response([
            'result' => true,
            'stock' => $assets
        ], 200);
    }

    public function getPaginatedInventoryLogs(Request $request)
    {
        $logs = [];
        $input =  $request->all();
        $selection = $this->checkNull($input['selection']);
        $search =  $this->checkNull($input['search']);
        $from = $this->checkNull($input['DRange']['from']);
        $to = $this->checkNull($to = $input['DRange']['to']);

        if (is_null($from) && is_null($to)) {
            $logs = assetsevent::where([
                ['event', 'like', '%' . $search . '%'],
                ['section', 'like', '%' . $selection . '%']
            ])
                ->orWhere([
                    ['onrequestof', 'like', '%' . $search . '%'],
                    ['section', 'like', '%' . $selection . '%']
                ])
                ->orderBy('created_at', 'DESC')->paginate(50);
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
            $logs = assetsevent::where([
                ['created_at', '>=', $Dfrom . " 00:00:00"],
                ['created_at', '<=', $Dto . " 23:59:59"],
                ['event', 'like', '%' . $search . '%'],
                ['section', 'like', '%' . $selection . '%']
            ])->orWhere([
                ['created_at', '>=', $Dfrom . " 00:00:00"],
                ['created_at', '<=', $Dto . " 23:59:59"],
                ['onrequestof', 'like', '%' . $search . '%'],
                ['section', 'like', '%' . $selection . '%']
            ])
                ->orderBy('created_at', 'DESC')->paginate(50);
        }

        return response([
            'result' => true,
            'logs' => $logs
        ], 200);
    }

    public function AddStocks(Request $request)
    {
        $this->validate($request, [
            'section' => 'required_with:stocks|string|min:3|max:255',
            'stocks' => 'required_with:stock1s_id|string|min:3|max:255',
            'stock1s_id' => 'required_with:category|integer|min:1|digits_between: 1,5000',
            'category' => 'required_with:unit|string|min:3|max:255',
            'unit' => 'required|string|min:3|max:255',
        ]);

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;

        $section = app('request')->__get('section');
        abort_unless(
            (strtolower($section) == 'store'),
            403,
            'Only STORE Account can Add Stock'
        );

        $category = app('request')->__get('category');
        $stocks = app('request')->__get('stocks');
        $asset = assets::where([
            ['section', '=', $section],
            ['category', '=', $category],
            ['stocks', '=', $stocks]
        ])->first();
        if ($asset != null) {
            abort_unless(
                ($asset->count() == 0),
                403,
                'This Record Already Exists.'
            );
        }

        DB::beginTransaction();
        $object = new stdClass();
        try {
            $assets = new assets;
            $assets->section = app('request')->__get('section');
            $assets->category = app('request')->__get('category');
            $assets->stocks = app('request')->__get('stocks');
            $assets->stock1s_id = app('request')->__get('stock1s_id');
            $assets->unit = app('request')->__get('unit');
            $assets->save();
            //log
            $object->Section = app('request')->__get('section');
            $object->Category = app('request')->__get('category');
            $object->Item = app('request')->__get('stocks');
            $object->Unit = app('request')->__get('unit');

            $type = "Stock";
            $this->addAssetEvent($UserSection, $userID, "Added  Stock ($type category)  Log: " .   str_replace(' ', '', print_r($object, true)), $UserName);
            DB::commit();
            return response([
                'result' => true
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function inboundStock(Request $request)
    {
        $this->validate($request, [
            'id' => 'required_with:inbound|integer|min:1|digits_between: 1,5000',
            'inbound' => 'required|integer|min:1|digits_between: 1,5000'
        ]);

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        abort_unless(
            (strtolower($UserSection) == 'store'),
            403,
            'Only STORE Account can Add to Stock'
        );

        DB::beginTransaction();
        try {
            $id = app('request')->__get('id');
            $inbound_ = app('request')->__get('inbound');
            $asset = assets::where('asset_id', $id)->first();
            $inbound  =  $inbound_ + $asset->inbound;
            $DBunit = $asset->unit;
            $DBstocks = $asset->stocks;
            $op =  assets::where('asset_id', $id)->update([
                'inbound' => $inbound,
                'updated_at' => now(),
            ]);

            $this->addAssetEvent($UserSection, $userID, "Added  $inbound_ $DBunit(s) of $DBstocks to Stock", 'STORE');
            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function fixInboundStock(Request $request){
        $this->validate($request, [
            'id' => 'required_with:inbound|integer|min:1|digits_between: 1,5000',
            'inbound' => 'required_with:email|integer|min:1|digits_between: 1,5000',
            'email' => 'required_with:password|string|min:3|max:255',
            'password' => 'required|string|min:1|max:255'
        ]);
        $supervisor_email = $this->verifyuserpermission->authProcessWithSupervisor();

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        abort_unless(
            (strtolower($UserSection) == 'supervisor'),
            403,
            'Only SUPERVISOR Account can Correct Stock'
        );

        DB::beginTransaction();
        try {
            $id = app('request')->__get('id');
            $inbound_ = app('request')->__get('inbound');
            $asset = assets::where('asset_id', $id)->first();
            $inbound  =  $inbound_ + $asset->inbound;
            $DBunit = $asset->unit;
            $DBstocks = $asset->stocks;
            $op =  assets::where('asset_id', $id)->update([
                'inbound' => $inbound,
                'updated_at' => now(),
            ]);

            $this->addAssetEvent($asset->section, $userID, "Stock Correction: Added  $inbound_ $DBunit(s) of $DBstocks to Stock; Authorised by $supervisor_email",  $asset->section);
            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function outboundStock(Request $request)
    {
        $this->validate($request, [
            'id' => 'required_with:section|integer|min:1|digits_between: 1,5000',
            'section' => 'required_with:category|string|min:3|max:255',
            'onrequestof' => 'required_with:outbound|string|min:3|max:255',
            'outbound' => 'required|integer|min:1|digits_between: 1,5000',
        ]);

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        abort_unless(
            (strtolower($UserSection) == 'store'),
            403,
            'Only STORE Account can Transfer  Stock'
        );

        DB::beginTransaction();
        try {
            $id = app('request')->__get('id');
            $section = app('request')->__get('section');
            $onrequestof = app('request')->__get('onrequestof');
            $outboundData = app('request')->__get('outbound');
            $DBasset = assets::where('asset_id', $id)->first();
            $DBcategory = $DBasset->category;
            $DBstock1s_id = $DBasset->stock1s_id;
            $DBstocks = $DBasset->stocks;
            $DBunit = $DBasset->unit;
            $DBinbound = $DBasset->inbound;
            $DBoutbound = $DBasset->outbound;

            abort_unless(
                (intval($outboundData) <= (intval($DBinbound) - intval($DBoutbound))),
                403,
                'Trasfer is impossible due to limited Stock'
            );
            $op = assets::where('asset_id', $id)->update([
                'outbound' => intval($DBoutbound) + intval($outboundData),
                'updated_at' => now(),
            ]);

            $selectAsset = DB::table('assets') // check if row entry exists and update for user recipient
                ->where('section', '=', $section)
                ->where('category', '=', $DBcategory)
                ->where('stocks', '=', $DBstocks)
                ->first();
            if (is_null($selectAsset)) {
                $DBasset = new assets;
                $DBasset->section =  $section;
                $DBasset->category = $DBcategory;
                $DBasset->stocks = $DBstocks;
                $DBasset->stock1s_id = $DBstock1s_id;
                $DBasset->unit = $DBunit;
                $DBasset->inbound = intval($outboundData) + 1; // deafault outbound is 1
                $DBasset->save();
            } else {
                $op = assets::where('section', $section)->where('category', $DBcategory)->where('stocks', $DBstocks)->update([
                    'inbound' => intval($DBoutbound) + intval($outboundData),
                    'updated_at' => now(),
                ]);
            }
            $this->addAssetEvent($section, $userID, "Recieved $outboundData $DBunit(s) of $DBstocks", $onrequestof);
            $this->addAssetEvent($UserSection, $userID, "Trasfered  $outboundData $DBunit(s) of $DBstocks to $section", $onrequestof);

            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function fixOutboundStock(Request $request){ 
        $this->validate($request, [
            'id' => 'required_with:outbound|integer|min:1|digits_between: 1,5000',
            'outbound' => 'required_with:email|integer|min:1|digits_between: 1,5000',
            'email' => 'required_with:password|string|min:3|max:255',
            'password' => 'required|string|min:1|max:255'
        ]);
        $supervisor_email = $this->verifyuserpermission->authProcessWithSupervisor();

        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );
        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;

        abort_unless(
            (strtolower($UserSection) == 'supervisor'),
            403,
            'Only SUPERVISOR Account can Correct Stock'
        );

        DB::beginTransaction();
        try {
            $id = app('request')->__get('id');
            $outboundData = app('request')->__get('outbound');
            $DBasset = assets::where('asset_id', $id)->first();
            $DBcategory = $DBasset->category;
            $DBstock1s_id = $DBasset->stock1s_id;
            $DBstocks = $DBasset->stocks;
            $DBunit = $DBasset->unit;
            $DBinbound = $DBasset->inbound;
            $DBoutbound = $DBasset->outbound;

            abort_unless(
                (intval($outboundData) <= (intval($DBinbound) - intval($DBoutbound))),
                403,
                'Correction is impossible due to limited Stock'
            );
            $op = assets::where('asset_id', $id)->update([
                'outbound' => intval($DBoutbound) + intval($outboundData),
                'updated_at' => now(),
            ]);

            $this->addAssetEvent($DBasset->section, $userID, "Stock Correction: Removed  $outboundData $DBunit(s) of $DBstocks from Stock; Authorised by $supervisor_email",  $DBasset->section);

            DB::commit();
            return response([
                'result' => $op
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function consumeStock(Request $request)
    {
        $this->validate($request, [
            'id' => 'required_with:selectedUseOperation|integer',
            'selectedUseOperation' => 'required_with:stockOperationsData|string|min:3|max:255',
            'stockOperationsData' => 'required|integer|min:1|digits_between: 1,500',
        ]);

        $input =  $request->all();
        $asset_id = $input['id'];
        $stockselectedUseOperation = $input['selectedUseOperation'];
        $stockOperationsData = $input['stockOperationsData'];
        $supervisorQAC = $input['qac']??null;
        $supervisor_email = null;
        switch ($stockselectedUseOperation) {
            case 'Sold':
                # resume code...
                break;
            case 'Used':
                # resume code...
                break;
            default:
                abort_unless(
                    ($supervisorQAC <> null),
                    403,
                    "This Operation Needs Supervisor's Quick Access Code!"
                );
                $supervisor_email = $this->verifyuserpermission->authProcessWithSupervisorQAC();
                break;
        }
        return $this->RunConsumeStock($request, $asset_id, $stockselectedUseOperation, $stockOperationsData, $supervisor_email);
    }

    public function RunConsumeStock($request, $asset_id, $stockselectedUseOperation, $stockOperationsData,  $supervisor_email)
    {
        $extraMessage = "";
        if ($supervisor_email <> null){
            $extraMessage = " : Auhtorised by $supervisor_email";
        }
        $userID = $request->session()->get('userID');
        abort_unless(
            ($userID != null),
            403,
            'No user in Session'
        );

        $user = User::where('id', $userID)->first();
        $UserSection = $user->section;
        $UserName = $user->name;

        DB::beginTransaction();
        try {
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
            $op = assets::where('asset_id', $asset_id)->update([
                'outbound' => intval($DBoutbound) + intval($stockOperationsData),
                'updated_at' => now(),
            ]);

            $this->addAssetEvent($UserSection, $userID, "$stockselectedUseOperation  $stockOperationsData $DBunit(s) of $DBstocks $extraMessage" , $UserName);
            DB::commit();
            return response([
                'result' => $op,
                'message' => "$stockselectedUseOperation  $stockOperationsData $DBunit(s) of $DBstocks"
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function setreportsettings(Request $request){
        $this->validate($request, [
            'CreateCleanerReports' => 'required_with:CreateLaundryReports|boolean',
            'CreateLaundryReports' => 'required_with:CreateHouseReports|boolean',
            'CreateHouseReports' => 'required_with:CreateAccountsReports|boolean',
            'CreateAccountsReports' => 'required_with:CreateSteamSaunaMassageReports|boolean',
            'CreateSteamSaunaMassageReports' => 'required_with:CreateReceptionReports|boolean',
            'CreateReceptionReports' => 'required_with:CreateKitechReports|boolean',
            'CreateKitechReports' => 'required_with:CreateServiceBarReports|boolean',
            'CreateServiceBarReports' => 'required_with:CreateSupervisorReports|boolean',
            'CreateSupervisorReports' => 'required_with:CreateFinancialReports|boolean',
            'CreateFinancialReports' => 'required_with:emails|boolean',
            'emails' => 'array'
        ]);
        $input =  $request->all();
        DB::beginTransaction();
        try {
            $settings = DB::table('reportsettings')->first();
            if(is_null($settings)){
                $settings = new reportsettings;
                $settings->CreateCleanerReports = $input['CreateCleanerReports'];
                $settings->CreateLaundryReports = $input['CreateLaundryReports'];
                $settings->CreateHouseReports = $input['CreateHouseReports'];
                $settings->CreateAccountsReports = $input['CreateAccountsReports'];
                $settings->CreateSteamSaunaMassageReports = $input['CreateSteamSaunaMassageReports'];
                $settings->CreateReceptionReports = $input['CreateReceptionReports'];
                $settings->CreateKitechReports = $input['CreateKitechReports'];
                $settings->CreateServiceBarReports = $input['CreateServiceBarReports'];
                $settings->CreateSupervisorReports = $input['CreateSupervisorReports'];
                $settings->CreateFinancialReports = $input['CreateFinancialReports'];
                $settings->created_at = now();
                $settings->updated_at = now();
                $settings->save();
            }else{
                $op = DB::table('reportsettings')
                ->update([
                    'CreateCleanerReports' => $input['CreateCleanerReports'],
                    'CreateLaundryReports' => $input['CreateLaundryReports'],
                    'CreateHouseReports' => $input['CreateHouseReports'],
                    'CreateAccountsReports' => $input['CreateAccountsReports'],
                    'CreateSteamSaunaMassageReports' => $input['CreateSteamSaunaMassageReports'],
                    'CreateReceptionReports' => $input['CreateReceptionReports'],
                    'CreateKitechReports' => $input['CreateKitechReports'],
                    'CreateServiceBarReports' => $input['CreateServiceBarReports'],
                    'CreateSupervisorReports' => $input['CreateSupervisorReports'],
                    'CreateFinancialReports' => $input['CreateFinancialReports'],
                    'updated_at' => now(),
                ]);
            }
            DB::table('reportemails')->delete();
            foreach ($input['emails'] as $value) {
            try {
                $email = new reportemails;
                $email->email = $value['email'];
                $email->created_at = now();
                $email->updated_at = now();
                $email->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
            }
            DB::commit();
            return response([
                'result' => true
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } 
    }

    public function reportsettings(Request $request){
        $reportsettings =  reportsettings::get()->first();
        if (is_null($reportsettings)) {
            $object = new stdClass();
            $object->CreateStockReports = false;
            $object->CreateBarKitechReports = false;
            $object->CreateRoomReports = false;
            $object->CreateSteamSaunaMassageReports = false;
            $reportsettings =   $object;
        }
        $reportemails = DB::table('reportemails')
        ->select('email')
        ->groupBy('email')
        ->get();
        return response([
            'reportsettings' =>$reportsettings,
            'reportemails' =>$reportemails,
        ], 200);    
    }

    public function stockreportlist(Request $request){
        $input =  $request->all();
        $selection = $this->checkNull($input['selection']);

        $list = Storage::disk('public_downloads')->allFiles();
        $list_ = array();
        foreach ($list as $lst){
            if (substr_compare($lst, '.pdf', -strlen('.pdf')) === 0) {
                array_push($list_, $lst);
            }
        }
        unset($lst);
        $filtered = array_filter($list_,function ($val) use ($selection) {
            return str_contains($val, $selection);
        });
        rsort($filtered );
        $paginated = $this->receiptsController->paginate(collect($filtered), 14, null, [], $request);
        return response([
            'list' =>$paginated,
        ], 200);
    }

//*** Download the report PDF
    public function stockreportlist_download(Request $request){
        $this->validate($request, [
            'file' => 'required|string|min:3|max:255'
        ]);
        $input =  $request->all();
        $filename =  $input['file'];
        return response() 
        ->download(Storage::disk('public_downloads')->path($filename));
    }

    public function stockreport(Request $request){
        $arr_ =  $this->assess_data();
        view()->share('stock',  $arr_);
        view()->share('Date',  (Carbon::now()->subDays(1))->setTimezone('Africa/Kampala')->format("M, d Y"));  //reports run imediately past mid night
        return view('stockreport', $arr_); //stockreport.blade.php
    }

    public function stockreportpdf_cron($reportsFilter){
        DB::beginTransaction();
        try {
            $arr_ =  $this->assess_data(true);
            $saved_files = array();
            foreach($arr_ as $dpt){
                $section = $dpt[0]->section;
                if (in_array( $section, $reportsFilter)) {
                    view()->share('stock',  $dpt);
                    view()->share('Date',  (Carbon::now()->subDays(1))->format("M, d Y"));  //reports run imediately past mid night
                    $pdf = PDF::loadView('stockreport_sections', $dpt->toArray()); // stockreport_sections.blade.php
                    $pdf->setPaper('A4', 'portrait');
                    $pdf->setOptions([
                        'dpi' => 50,//150,
                        'pdfBackend' => 'auto',
                        'debugCss' => true,
                        'isPhpEnabled' => true,
                        'defaultFont' => 'sans-serif'
                    ]);
                    $pdf->render(); // Render the HTML as PDF  but causesmemory leak php ini limit
                    $saved_file = $pdf->output();
                    if(Storage::disk('public_downloads')->put((Carbon::now()->subDays(1))->format("Y-m-d")."-$section-StockReport.pdf",  $saved_file)) {
                        $this->log("info",  "Successfully created $section Stock Report on " . now());
                        $obj=new stdClass;
                        $obj->section= $section;
                        $obj->file=$saved_file;
                        array_push($saved_files, $obj);
                    } else {
                        $this->log("critical", "Could not Save $section Stock Report on " . now());
                    }
                }
            }
            DB::commit();
            return  $saved_files;
        } catch (\Throwable $th) {
            DB::rollback();
            $this->log("critical", $th);
            return null;
        }
    }

    public function assess_data($segment = false){
//*** SYNC asset - asset_snapsopt -  capture current Stock
        $assets = assets::orderBy('section', 'DESC')->get();
        if ($assets == null) { // no assets
            return null;
        } else { // assess assets
            foreach($assets as $asset){
//*** ADD CURRENT SNAP STOCK
                $asset_snap = assets_snapshot::where([ ['asset_id', '=', $asset->asset_id],   ['section', '=', $asset->section], ['category', '=', $asset->category], ['stocks', '=', $asset->stocks] ])->first();
                if ($asset_snap == null) { // no assets_snaps, new DB
                    $asset_snap_ = new assets_snapshot;
                    $asset_snap_->asset_id = $asset->asset_id;
                    $asset_snap_->section = $asset->section;
                    $asset_snap_->category = $asset->category;
                    $asset_snap_->stocks = $asset->stocks;
                    $asset_snap_->unit = $asset->unit;
                    $asset_snap_->opening_stock =  ($asset->inbound - $asset->outbound);
                    $asset_snap_->closing_stock =  ($asset->inbound - $asset->outbound);
                    $asset_snap_->save();               
                } else {
//*** UPDATE CURRENT SNAP STOCK
                    $openingStock = $asset_snap->closing_stock;
                    $closingStock = ($asset->inbound - $asset->outbound);
                    $op = assets_snapshot::where([  ['asset_id', '=', $asset->asset_id],   ['section', '=', $asset->section], ['category', '=', $asset->category], ['stocks', '=', $asset->stocks] ])
                    ->update([
                        'opening_stock' =>$openingStock,
                        'closing_stock' => $closingStock,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if ($segment === true){
            $sections = assets_snapshot::pluck('section')->unique();
            $dptArr = array();
            foreach($sections as $section){
                 $dpt = assets_snapshot::where('section',$section)->orderBy('categry', 'desc')->get();
                 array_push($dptArr, $dpt);
            }
            return $dptArr;
        } else {
            // return assets_snapshot::all()->groupBy("section")->map(function($item){
            //    return $item->groupBy("category"); 
            // });
            return assets_snapshot::all();
        }
    }

//EVENT LOG**************************
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

