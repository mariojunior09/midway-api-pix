<?php

use App\Http\Controllers\banco\Banco;
use App\Http\Controllers\HelperBancoDoBrasil;
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

Route::post('pix-gerar-cobranca', 'ApiPixController@sendCobrancaPix');
Route::put('cadastro-url-webhook', 'ApiPixController@putWebHookUrl');
Route::post('get-cobranca-webhook', 'ApiPixController@getCobByWebHook');
Route::get('pix-get-cobranca/{txid}', 'ApiPixController@getCobrancaBradescoByTxId');
Route::get('teste-webhook', 'ApiPixController@testeWebhook');
