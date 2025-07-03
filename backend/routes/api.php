<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * @OA\Get(
 *     path="/test",
 *     summary="Test API endpoint",
 *     @OA\Response(response="200", description="Successful operation")
 * )
 */
Route::get('/test', function () {
    return response()->json(['message' => 'Test successful']);
});
