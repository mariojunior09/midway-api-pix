<?php

use App\PixModel;
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


Route::put('cadastro-url-webhook', 'ApiPixController@putWebHookUrl');
Route::post('pix-gerar-cobranca', 'ApiPixController@createCobBradesco');
Route::post('get-cobranca-webhook/e570607e-3f4d-489a-bc0f-f885b4a59cc9', 'ApiPixController@getCobByWebHook');
Route::get('pix-get-cobranca/{txid}', 'ApiPixController@getCobrancaBradescoByTxId');

Route::get('teste-webhook', 'ApiPixController@testeWebhook');


Route::get('teste', function () {
    $pixModel  =  new PixModel();
    $dadosPix = $pixModel->vw_config();
    dd($dadosPix);
    return  $dadosPix;
});
