<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('guest')->group(function () {
    //ADMIN
    Route::post('api/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->name('logout');
    Route::post('web-auth', [\App\Http\Controllers\Api\AuthController::class, 'login'])->name('web-auth');
    Route::post('api-Users', [\App\Http\Controllers\Api\AuthController::class, 'Users'])->name('api-Users');

    //USERS
    Route::post('permission', [\App\Http\Controllers\Api\UsersController::class, 'TogglePermissions'])->name('permission');
    Route::post('del-User', [\App\Http\Controllers\Api\UsersController::class, 'DeleteUser'])->name('del-User');
    Route::post('verifyUser', [\App\Http\Controllers\Api\UsersController::class, 'VerifyUser'])->name('verifyUser');
    Route::post('verifyAuthEmailUser', [\App\Http\Controllers\Api\UsersController::class, 'AuthEmailUserCode'])->name('verifyAuthEmailUser');
    Route::post('updateProfPic', [\App\Http\Controllers\Api\UsersController::class, 'updateProfPic'])->name('updateProfPic');
    Route::post('AllUsers', [\App\Http\Controllers\Api\UsersController::class, 'AllUsers'])->name('AllUsers');

    //menu
    Route::post('getMenuKitchen', [\App\Http\Controllers\Api\Menu\menuController::class, 'getMenuKitchen'])->name('getMenuKitchen');
    Route::post('getMenuBar', [\App\Http\Controllers\Api\Menu\menuController::class, 'getMenuBar'])->name('getMenuBar');
    Route::post('delMenu', [\App\Http\Controllers\Api\Menu\menuController::class, 'delMenu'])->name('delMenu');
    Route::post('addMenu', [\App\Http\Controllers\Api\Menu\menuController::class, 'addMenu'])->name('addMenu');
    Route::post('addMenuBar', [\App\Http\Controllers\Api\Menu\menuController::class, 'addMenuBar'])->name('addMenuBar');
    Route::post('delMenuBar', [\App\Http\Controllers\Api\Menu\menuController::class, 'delMenuBar'])->name('delMenuBar');
    Route::post('getAllMenu', [\App\Http\Controllers\Api\Menu\menuController::class, 'getAllMenu'])->name('getAllMenu');

    //stock
    Route::post('getAllInventoryMenu', [\App\Http\Controllers\Api\Stock\StockController::class, 'getAllInventoryMenu'])->name('getAllInventoryMenu');
    Route::post('addStockMenu', [\App\Http\Controllers\Api\Stock\StockController::class, 'addStockMenu'])->name('addStockMenu');
    Route::post('delStockMenu', [\App\Http\Controllers\Api\Stock\StockController::class, 'delStockMenu'])->name('delStockMenu');
    Route::post('delStock', [\App\Http\Controllers\Api\Stock\StockController::class, 'delStock'])->name('delStock');
    Route::post('getAllInventoryStock', [\App\Http\Controllers\Api\Stock\StockController::class, 'getAllInventoryStock'])->name('getAllInventoryStock');
    Route::post('AddStocks', [\App\Http\Controllers\Api\Stock\StockController::class, 'AddStocks'])->name('AddStocks');
    Route::post('inboundStock', [\App\Http\Controllers\Api\Stock\StockController::class, 'inboundStock'])->name('inboundStock');
    Route::post('inboundStock/fix', [\App\Http\Controllers\Api\Stock\StockController::class, 'fixInboundStock'])->name('fixInboundStock');
    Route::post('outboundStock', [\App\Http\Controllers\Api\Stock\StockController::class, 'outboundStock'])->name('outboundStock');
    Route::post('outboundStock/fix', [\App\Http\Controllers\Api\Stock\StockController::class, 'fixOutboundStock'])->name('fixOutboundStock');
    Route::post('consumeStock', [\App\Http\Controllers\Api\Stock\StockController::class, 'consumeStock'])->name('consumeStock');
    Route::post('getPaginatedInventoryLogs', [\App\Http\Controllers\Api\Stock\StockController::class, 'getPaginatedInventoryLogs'])->name('getPaginatedInventoryLogs');
    Route::post('stockreportlist', [\App\Http\Controllers\Api\Stock\StockController::class, 'stockreportlist'])->name('stockreportlist');
    Route::get('stockreportlist_download', [\App\Http\Controllers\Api\Stock\StockController::class, 'stockreportlist_download'])->name('stockreportlist_download');
    Route::post('setreportsettings', [\App\Http\Controllers\Api\Stock\StockController::class, 'setreportsettings'])->name('setreportsettings');
    Route::get('reportsettings', [\App\Http\Controllers\Api\Stock\StockController::class, 'reportsettings'])->name('reportsettings');

    //system logs
    Route::post('getPaginatedSystemLogs', [\App\Http\Controllers\Systemlogs::class, 'getPaginatedSystemLogs'])->name('getPaginatedSystemLogs');

    //Receipts
    Route::post('getReceipts', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'getReceipts'])->name('getReceipts');
    Route::post('getPaginatedReceipts', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'getPaginatedReceipts'])->name('getPaginatedReceipts');
    Route::post('getOrders', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'getOrders'])->name('getOrders');
    Route::post('getPaginatedOrders', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'getPaginatedOrders'])->name('getPaginatedOrders');
    Route::post('changeorderState', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'changeorderState'])->name('changeorderState');
    Route::post('requestCancelOrder', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'requestCancelOrder'])->name('requestCancelOrder');
    Route::post('addReceipts', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'addReceipts'])->name('addReceipts');
    Route::post('updateReceipts', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'updateReceipts'])->name('updateReceipts');
    Route::post('receiptreport/pdf', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'receiptreportpdf'])->name('receiptreportpdf');

    //Hotel Rooms AND Guests
    Route::post('searchSortGuests', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'searchSortGuests'])->name('searchSortGuests');
    Route::post('guestsroomsPaginated', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'guestsroomsPaginated'])->name('guestsroomsPaginated');
    Route::post('guestsrooms', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'guestsrooms'])->name('guestsrooms');
    Route::post('newGuest', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'newGuest'])->name('newGuest');
    Route::post('newRoom', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'newRoom'])->name('newRoom');
    Route::post('delRoom', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'delRoom'])->name('delRoom');
    Route::post('newGuestcheckOut', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'newGuestcheckOut'])->name('newGuestcheckOut');
    Route::post('newGuestPaid', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'newGuestPaid'])->name('newGuestPaid');
    Route::post('newGuestClose', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'newGuestClose'])->name('newGuestClose');
    Route::post('roomsreport/pdf', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'roomsreportpdf'])->name('roomsreportpdf');

    //STEAM-SAUNA-MASSAGE
    Route::post('searchSteamSaunaMassagePaginated', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'searchSteamSaunaMassagePaginated'])->name('searchSteamSaunaMassagePaginated');
    Route::post('newMassagePackage', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'newMassagePackage'])->name('newMassagePackage');
    Route::post('newSteamSaunaPackage', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'newSteamSaunaPackage'])->name('newSteamSaunaPackage');
    Route::post('delMassage', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'delMassage'])->name('delMassage');
    Route::post('delSauna', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'delSauna'])->name('delSauna');
    Route::post('guestSaunaMasagePaid', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'guestSaunaMasagePaid'])->name('guestSaunaMasagePaid');
    Route::post('newGuestSteamSaunMassage', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'newGuestSteamSaunMassage'])->name('newGuestSteamSaunMassage');
    Route::post('saunamassagereport/pdf', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'saunamassagereportpdf'])->name('saunamassagereportpdf');
    Route::post('getSteamSaunaMassagePackages',  [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'getSteamSaunaMassagePackages'])->name('getSteamSaunaMassagePackages');
    //METRICS
    Route::post('getmetrics/all', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'getmetrics'])->name('getmetrics');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
