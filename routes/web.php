<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
// Auth::routes(['verify' => true]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/profile/{id}', [App\Http\Controllers\ProfilesController::class, 'index'])->name('profiles.view');

//sample receipt PDF
Route::get('/sampleInvoice', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'sampleInvoice']);
Route::get('/sampleInvoice/pdf', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'sampleInvoicePdf']);

//sauna massage room report
Route::get('/saunamassagereport', [\App\Http\Controllers\Api\SteamSaunaMassage\SteamSaunaMassageController::class, 'saunamassagereport']);
Route::get('/roomsreport', [\App\Http\Controllers\Api\Reception\ReceptionController::class, 'roomsreport']);

//bar kitchen report
Route::get('/receiptreport', [\App\Http\Controllers\Api\Receipts\ReceiptsController::class, 'receiptreport']);

//stock report
Route::get('/stockreport', [\App\Http\Controllers\Api\Stock\StockController::class, 'stockreport']);
