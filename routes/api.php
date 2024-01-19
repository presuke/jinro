<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Room as RoomV1;
use App\Http\Controllers\v1\Player as PlayerV1;
use App\Http\Controllers\v1\Play as PlayV1;

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

Route::prefix('/v1')->group(function () {
    Route::prefix('/room')->group(function () {
        Route::get('getAll', [RoomV1::Class, 'getAll']);
        Route::get('get', [RoomV1::Class, 'get']);
        Route::post('create', [RoomV1::Class, 'create']);
        Route::post('remove', [RoomV1::Class, 'remove']);
        Route::post('restart', [RoomV1::Class, 'restart']);
    });
    Route::prefix('/player')->group(function () {
        Route::post('create', [PlayerV1::Class, 'create']);
    });
    Route::prefix('/play')->group(function () {
        Route::post('login', [PlayV1::Class, 'login']);
        Route::post('action', [PlayV1::Class, 'action']);
        Route::get('getRoomStatus', [PlayV1::Class, 'getRoomStatus']);
        Route::get('confirmAction', [PlayV1::Class, 'confirmAction']);
    });
});
