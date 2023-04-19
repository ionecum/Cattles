<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

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

define('EXPIRE', 300); // The expiration time for the game
define('SIZE', 4); // Size of secret and guess number

Route::get('/', function () {
    return view('welcome');
});
Route::controller(GameController::class)->group(function () {
    Route::get('/game/start/{name}/{age}', 'start');
    // the ? means optional parameter
    Route::get('/game/combinate/{number?}', 'combination');
});
