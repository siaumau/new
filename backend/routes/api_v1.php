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

    // 位置相關的額外路由
    Route::get('locations/{id}/floor-distribution', [LocationController::class, 'getFloorDistribution']);
    Route::get('locations/{id}/items', [LocationController::class, 'getLocationItems']);

    // 美國進貨單生成路由
    Route::patch('posin/{id}/generate-us-purchase-order', [PosinController::class, 'generateUsPurchaseOrder']);

    // 進貨單商品項目相關路由
    Route::get('posin/{id}/items', [PosinController::class, 'getPosinItems']);
    Route::delete('posin-items/{id}', [PosinController::class, 'deletePosinItem']);

    // QR Code相關路由
    Route::get('check-qr-generated/{posinitem_id}', [PosinController::class, 'checkQRGenerated']);
    Route::post('generate-qr-labels', [PosinController::class, 'generateQRLabels']);
    Route::post('save-qr-files', [PosinController::class, 'saveQRFiles']);

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
