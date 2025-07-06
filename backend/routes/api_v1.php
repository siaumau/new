<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PosinController;
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

Route::prefix('api/v1')->group(function () {
    Route::apiResource('items', ItemController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('posin', PosinController::class);

    // 美國進貨單生成路由
    Route::patch('posin/{id}/generate-us-purchase-order', [PosinController::class, 'generateUsPurchaseOrder']);

    /**
     * @OA\Get(
     *     path="/api/v1/test",
     *     summary="Test API endpoint",
     *     @OA\Response(response="200", description="Successful operation")
     * )
     */
    Route::get('/test', function () {
        return response()->json(['message' => 'Test successful']);
    });
});
