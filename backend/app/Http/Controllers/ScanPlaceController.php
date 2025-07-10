<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\Location;
use App\Models\MovementLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ScanPlaceController extends Controller
{
    /**
     * 驗證櫃位QR Code
     */
    public function validateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'location_code' => 'required|string'
        ]);

        try {
            $location = Location::where('location_code', $request->location_code)
                               ->where('is_active', true)
                               ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => '無效的櫃位代碼或櫃位已停用'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'location_code' => $location->location_code,
                    'location_name' => $location->location_name,
                    'building' => $location->building,
                    'capacity' => $location->capacity,
                    'current_stock' => $location->current_stock
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Location validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '櫃位驗證失敗'
            ], 500);
        }
    }

    /**
     * 驗證商品箱子QR Code
     */
    public function validateBox(Request $request): JsonResponse
    {
        $request->validate([
            'box_code' => 'required|string'
        ]);

        try {
            $qrCode = QrCode::where('qr_content', $request->box_code)
                           ->orWhere('box_number', $request->box_code)
                           ->first();

            if (!$qrCode) {
                return response()->json([
                    'success' => false,
                    'message' => '無效的商品箱子QR Code'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_code_id' => $qrCode->qr_id,
                    'item_code' => $qrCode->item_code,
                    'item_name' => $qrCode->item_name,
                    'box_number' => $qrCode->box_number,
                    'batch' => $qrCode->item_batch,
                    'location_id' => $qrCode->location_id,
                    'inbox_status' => $qrCode->item_inbox_status,
                    'status' => $qrCode->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Box validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '商品箱子驗證失敗'
            ], 500);
        }
    }

    /**
     * 商品歸位綁定（首次入庫）
     */
    public function firstBinding(Request $request): JsonResponse
    {
        $request->validate([
            'location_code' => 'required|string',
            'box_code' => 'required|string',
            'binding_option' => 'required|in:bind-only,bind-and-inbox'
        ]);

        try {
            DB::beginTransaction();

            // 驗證櫃位
            $location = Location::where('location_code', $request->location_code)
                               ->where('is_active', true)
                               ->first();

            if (!$location) {
                throw ValidationException::withMessages([
                    'location_code' => ['無效的櫃位代碼']
                ]);
            }

            // 驗證商品箱子
            $qrCode = QrCode::where('qr_content', $request->box_code)
                           ->orWhere('box_number', $request->box_code)
                           ->first();

            if (!$qrCode) {
                throw ValidationException::withMessages([
                    'box_code' => ['無效的商品箱子QR Code']
                ]);
            }

            // 檢查是否已綁定其他櫃位
            if ($qrCode->location_id && $qrCode->location_id !== $location->id) {
                // 取得已綁定的櫃位名稱
                $boundLocation = Location::find($qrCode->location_id);
                $boundLocationName = $boundLocation ? $boundLocation->location_name : '未知櫃位';

                throw ValidationException::withMessages([
                    'box_code' => ['該商品箱子已綁定其他櫃位：' . $boundLocationName]
                ]);
            }

            // 更新QR Code記錄
            $qrCode->location_id = $location->id;

            if ($request->binding_option === 'bind-and-inbox') {
                $qrCode->item_inbox_status = 1;
                $qrCode->status = 'used';
            }

            $qrCode->save();

            // 記錄移動日誌
            MovementLog::create([
                'qr_code_id' => $qrCode->qr_id,
                'item_code' => $qrCode->item_code,
                'item_name' => $qrCode->item_name,
                'box_number' => $qrCode->box_number,
                'from_location_id' => null,
                'from_location_code' => null,
                'to_location_id' => $location->id,
                'to_location_code' => $request->location_code,
                'movement_type' => 'assign',
                'reason' => $request->binding_option === 'bind-and-inbox' ? '綁定並入庫' : '僅綁定',
                'operator' => 'system', // 實際應該從認證用戶獲取
                'moved_at' => now()
            ]);

            // 更新櫃位庫存
            if ($request->binding_option === 'bind-and-inbox') {
                $location->increment('current_stock');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->binding_option === 'bind-and-inbox' ? '綁定並入庫成功' : '綁定成功',
                'data' => [
                    'qr_code_id' => $qrCode->qr_id,
                    'location_code' => $request->location_code,
                    'binding_option' => $request->binding_option
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('First binding error: ' . $e->getMessage());
            Log::error('First binding error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => '綁定操作失敗',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * 加工/出貨處理
     */
    public function processShipping(Request $request): JsonResponse
    {
        $request->validate([
            'box_code' => 'required|string',
            'outbound_type' => 'required|in:processing,shipping'
        ]);

        try {
            DB::beginTransaction();

            // 驗證商品箱子
            $qrCode = QrCode::where('qr_content', $request->box_code)
                           ->orWhere('box_number', $request->box_code)
                           ->first();

            if (!$qrCode) {
                throw ValidationException::withMessages([
                    'box_code' => ['無效的商品箱子QR Code']
                ]);
            }

            // 檢查是否已綁定櫃位且已入庫
            if (!$qrCode->location_id) {
                throw ValidationException::withMessages([
                    'box_code' => ['該商品箱子尚未綁定櫃位']
                ]);
            }

            if ($qrCode->item_inbox_status !== 1) {
                throw ValidationException::withMessages([
                    'box_code' => ['該商品箱子尚未入庫']
                ]);
            }

            $originalLocationId = $qrCode->location_id;
            $targetLocation = $request->outbound_type === 'processing' ? 'CH七樓加工區' : 'OUT_SHIPPING';

            // 更新QR Code記錄
            $qrCode->location_id = null; // 暫時設為null，因為目標位置可能不是location表中的記錄
            $qrCode->item_inbox_status = 0; // 設為pending
            $qrCode->save();

            // 記錄移動日誌
            MovementLog::create([
                'qr_code_id' => $qrCode->qr_id,
                'item_code' => $qrCode->item_code,
                'item_name' => $qrCode->item_name,
                'box_number' => $qrCode->box_number,
                'from_location_id' => $originalLocationId,
                'to_location_code' => $targetLocation,
                'movement_type' => $request->outbound_type === 'processing' ? 'move' : 'move',
                'reason' => $request->outbound_type === 'processing' ? '送至加工區' : '出貨',
                'operator' => 'system',
                'moved_at' => now()
            ]);

            // 更新原櫃位庫存
            $originalLocationModel = Location::find($originalLocationId);
            if ($originalLocationModel && $originalLocationModel->current_stock > 0) {
                $originalLocationModel->decrement('current_stock');
            }

            DB::commit();

            $message = $request->outbound_type === 'processing'
                ? '加工出庫成功！商品已移至 CH七樓加工區'
                : '出貨成功！商品已標記為出貨狀態';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'qr_code_id' => $qrCode->qr_id,
                    'from_location_id' => $originalLocationId,
                    'to_location' => $targetLocation,
                    'outbound_type' => $request->outbound_type
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process shipping error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '出庫操作失敗'
            ], 500);
        }
    }

    /**
     * 加工完成後歸還櫃位
     */
    public function returnToStock(Request $request): JsonResponse
    {
        $request->validate([
            'location_code' => 'required|string',
            'box_code' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // 驗證櫃位
            $location = Location::where('location_code', $request->location_code)
                               ->where('is_active', true)
                               ->first();

            if (!$location) {
                throw ValidationException::withMessages([
                    'location_code' => ['無效的櫃位代碼']
                ]);
            }

            // 驗證商品箱子
            $qrCode = QrCode::where('qr_content', $request->box_code)
                           ->orWhere('box_number', $request->box_code)
                           ->first();

            if (!$qrCode) {
                throw ValidationException::withMessages([
                    'box_code' => ['無效的商品箱子QR Code']
                ]);
            }

            // 檢查是否在加工區（由於加工區不在location表中，檢查location_id是否為null）
            if ($qrCode->location_id !== null) {
                // 取得當前櫃位名稱
                $currentLocation = Location::find($qrCode->location_id);
                $currentLocationName = $currentLocation ? $currentLocation->location_name : '未知櫃位';

                throw ValidationException::withMessages([
                    'box_code' => ['該商品箱子不在加工區，當前位置：' . $currentLocationName]
                ]);
            }

            $originalLocationCode = 'CH七樓加工區';

            // 更新QR Code記錄
            $qrCode->location_id = $location->id;
            $qrCode->item_inbox_status = 1;
            $qrCode->save();

            // 記錄移動日誌
            MovementLog::create([
                'qr_code_id' => $qrCode->qr_id,
                'item_code' => $qrCode->item_code,
                'item_name' => $qrCode->item_name,
                'box_number' => $qrCode->box_number,
                'from_location_code' => $originalLocationCode,
                'to_location_id' => $location->id,
                'to_location_code' => $request->location_code,
                'movement_type' => 'return',
                'reason' => '加工完成歸還',
                'operator' => 'system',
                'moved_at' => now()
            ]);

            // 更新櫃位庫存
            $location->increment('current_stock');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '歸還成功！商品已入庫到指定櫃位',
                'data' => [
                    'qr_code_id' => $qrCode->qr_id,
                    'from_location' => $originalLocationCode,
                    'to_location' => $request->location_code
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return to stock error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '歸還操作失敗'
            ], 500);
        }
    }
}
