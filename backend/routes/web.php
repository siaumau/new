<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

/**
 * @OA\Get(
 *     path="/web-test",
 *     summary="Test Web API endpoint",
 *     @OA\Response(response="200", description="Successful operation")
 * )
 */
Route::get('/web-test', function () {
    return response()->json(['message' => 'Web Test successful']);
});
