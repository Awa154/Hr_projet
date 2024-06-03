<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Document\DocController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Route pour l'authentification
Route::post('/auth/login',[AuthController::class,'login']);
Route::post('/auth/register',[AuthController::class,'register']);

//Route pour les différents documents administratifs
Route::post('doc/doc-store',[DocController::class,'store']);
Route::put('doc/doc-update/{id}',[DocController::class,'update']);
Route::get('/doc/doc-contratActu', [DocController::class, 'contratActu']);
Route::get('/doc/doc-contratTerminer', [DocController::class, 'contratTerminer']);
Route::get('/doc/doc-fichepaieNonPayer', [DocController::class, 'fichepaieNonPayer']);
Route::get('/doc/doc-fichepaiePayer', [DocController::class, 'fichepaiePayer']);

Route::group(['middleware'=> ['auth:sanctum']], function () {
    Route::get('auth/profile',[AuthController::class,'profile']);
    Route::put('auth/profile-edit',[AuthController::class,'profile']);
});

