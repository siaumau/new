<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\QrCodeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PosinController;
use App\Http\Controllers\MovementLogController;
use App\Http\Controllers\ScanPlaceController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('api/v1')->group(function () {
    Route::apiResource('items', ItemController::class);
    Route::get('qr-codes-binded', [QrCodeController::class, 'index']);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('posin', PosinController::class);

    // 位置相關的額外路由
    Route::post('locations/batch', [LocationController::class, 'batchStore']);
    Route::get('locations/{id}/floor-distribution', [LocationController::class, 'getFloorDistribution']);
    Route::get('locations/{id}/items', [LocationController::class, 'getLocationItems']);

    // 進貨單相關的額外路由
    Route::post('posin/batch', [PosinController::class, 'batchStore']);

    // 美國進貨單生成路由
    Route::patch('posin/{id}/generate-us-purchase-order', [PosinController::class, 'generateUsPurchaseOrder']);

    // 進貨單商品項目相關路由
    Route::get('posin/{id}/items', [PosinController::class, 'getPosinItems']);
    Route::delete('posin-items/{id}', [PosinController::class, 'deletePosinItem']);

    // QR Code相關路由
    Route::get('check-qr-generated/{posinitem_id}', [PosinController::class, 'checkQRGenerated']);
    Route::post('generate-qr-labels', [PosinController::class, 'generateQRLabels']);
    Route::get('qr-codes', [QrCodeController::class, 'index']);
    Route::get('qr-codes/{id}', [QrCodeController::class, 'show']);
    Route::post('qr-codes/{id}/assign-location', [QrCodeController::class, 'assignLocation']);
    Route::post('qr-codes/{id}/update-status', [QrCodeController::class, 'updateStatus']);
    Route::post('qr-codes/scan-assign', [QrCodeController::class, 'scanAssign']);

    // QR Code 標籤管理路由
    Route::get('qr-codes/statistics', [QrCodeController::class, 'statistics']);
    Route::get('qr-codes/by-zip-file/{zipFileName}', [QrCodeController::class, 'getByZipFile']);
    Route::post('qr-codes/batch-update-status', [QrCodeController::class, 'batchUpdateStatus']);

    // 移動記錄相關路由
    Route::get('movement-logs', [MovementLogController::class, 'index']);
    Route::get('movement-logs/{id}', [MovementLogController::class, 'show']);
    Route::get('movement-logs/qr-code/{qrCodeId}', [MovementLogController::class, 'getQrCodeHistory']);

    // 掃描歸位相關路由
    Route::post('scan-place/validate-location', [ScanPlaceController::class, 'validateLocation']);
    Route::post('scan-place/validate-box', [ScanPlaceController::class, 'validateBox']);
    Route::post('scan-place/first-binding', [ScanPlaceController::class, 'firstBinding']);
    Route::post('scan-place/process-shipping', [ScanPlaceController::class, 'processShipping']);
    Route::post('scan-place/return-to-stock', [ScanPlaceController::class, 'returnToStock']);

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
