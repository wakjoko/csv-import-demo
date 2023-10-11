<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'imports', 'as' => 'imports.'], function () {
    Route::get('', [ImportController::class, 'index'])->name('index');
    Route::post('', [ImportController::class, 'import'])->name('import');
    Route::get('status/{id}', [ImportController::class, 'status'])->name('status');
});
