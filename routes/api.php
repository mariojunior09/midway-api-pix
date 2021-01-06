<?php

use App\Model\ApiPixModel;
use App\Procedures\HelperProcedures;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::Post('pix-gerar-cobranca','ApiPixController@createCobBradesco');
Route::get('pix-get-cobranca/{txid}','ApiPixController@getCobrancaBradescoByTxId');

Route::get('get-teste-ibge','ApiPixController@getIbge');



