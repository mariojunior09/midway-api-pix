<?php

use App\Http\Controllers\HelperController;
use Illuminate\Support\Facades\Route;
use \App\pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

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




Route::get('/',function(){
    return view('welcome');
});