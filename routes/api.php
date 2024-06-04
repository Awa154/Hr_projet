<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Document\DocController;
use App\Http\Controllers\Document\ListeController;
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

//Affichage des contrats
Route::get('/doc/doc-contratActu', [DocController::class, 'contratActu']);
Route::get('/doc/doc-contratTerminer', [DocController::class, 'contratTerminer']);

//Affichage des fiches de paies
Route::get('/doc/doc-fichepaieNonPayer', [DocController::class, 'fichepaieNonPayer']);
Route::get('/doc/doc-fichepaiePayer', [DocController::class, 'fichepaiePayer']);

//Affichege de la liste des employés
Route::get('/doc/liste-employe', [ListeController::class, 'listeEmploye']);

//Affichege de la liste des employés
Route::get('/doc/liste-entreprise', [ListeController::class, 'listeEntreprise']);


Route::group(['middleware'=> ['auth:sanctum']], function () {

    //Utilisateur connecter
    Route::get('auth/profile',[AuthController::class,'profile']);
    Route::put('auth/profile-edit',[AuthController::class,'edit']);
    Route::put('auth/change-password',[AuthController::class,'editPassword']);

    //Affichage des contrats pour l'employé connecter
    Route::get('auth/doc-contratActuEmploye', [DocController::class, 'contratActuEmploye']);
    Route::get('auth/doc-contratTerminer', [DocController::class, 'contratTerminerEmploye']);

    //Affichage des fiches de paies pour l'employé connecter
    Route::get('auth/doc-fichepaieNonPayerEmploye', [DocController::class, 'fichepaieNonPayerEmploye']);
    Route::get('auth/doc-fichepaiePayerEmploye', [DocController::class, 'fichepaiePayerEmploye']);

     //Affichage des contrats pour l'entreprise connecter
    Route::get('auth/doc-contratActuEntreprise', [DocController::class, 'contratActuEntreprise']);
    Route::get('auth/doc-contratTerminerEntreprise', [DocController::class, 'contratTerminerEntreprise']);

    //Affichage des fiches de paies pour l'entreprise connecter
    Route::get('auth/doc-fichepaieNonPayerEntreprise', [DocController::class, 'fichepaieNonPayerEntreprise']);
    Route::get('auth/doc-fichepaiePayerEntreprise', [DocController::class, 'fichepaiePayerEntreprise']);
});

