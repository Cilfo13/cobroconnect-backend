<?php

use App\Http\Controllers\APIController;
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

Route::get('/hello', function () {
    return response()->json(['mensaje' => 'Hola Mundo']);
});
Route::get('/repositories', function () {
    $data = [
        [
            "id" => 'jaredpalmer.formik',
            "fullName" => 'jaredpalmer/formik',
            "description" => 'Build forms in React, without the tears',
            "language" => 'TypeScript',
            "forksCount" => 1589,
            "stargazersCount" => 21553,
            "ratingAverage" => 88,
            "reviewCount" => 4,
            "ownerAvatarUrl" => 'https://avatars2.githubusercontent.com/u/4060187?v=4',
        ],
        [
            "id" => 'rails.rails',
            "fullName" => 'rails/rails',
            "description" => 'Ruby on Rails',
            "language" => 'Ruby',
            "forksCount" => 18349,
            "stargazersCount" => 45377,
            "ratingAverage" => 100,
            "reviewCount" => 2,
            "ownerAvatarUrl" => 'https://avatars1.githubusercontent.com/u/4223?v=4',
        ],
        [
            "id" => 'django.django',
            "fullName" => 'django/django',
            "description" => 'The Web framework for perfectionists with deadlines.',
            "language" => 'Python',
            "forksCount" => 21015,
            "stargazersCount" => 48496,
            "ratingAverage" => 73,
            "reviewCount" => 5,
            "ownerAvatarUrl" => 'https://avatars2.githubusercontent.com/u/27804?v=4',
        ],
    ];

    return response()->json($data);
});

//JWT ROUTES 
Route::post('/login', 'App\Http\Controllers\AuthJWTController@login');
Route::middleware('jwt.verify')->group(function () {
    Route::get('/misclientes', [APIController::class, 'MisClientes']);
    Route::post('/cobrar', [APIController::class, 'cobrar']);
    Route::post('/anotarTransferencia', [APIController::class, 'anotarTransferencia']);
    Route::get('/generarPDF', [APIController::class, 'generarPDFRapido']);
    Route::get('/generarInformeCobranzas', [APIController::class, 'generarInformeCobranzas']);
    Route::post('/logout', 'App\Http\Controllers\AuthJWTController@logout');
    Route::post('/refresh', 'App\Http\Controllers\AuthJWTController@refresh');
    // Otras rutas protegidas que deseas definir para la API
    // Route::get('/todos', 'App\Http\Controllers\TodoController@index');
});