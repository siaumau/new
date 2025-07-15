<?php

namespace App\Http\Controllers;

use App\Services\PosinService;
use App\Http\Requests\PosinCreateRequest;
use App\Http\Requests\PosinUpdateRequest;
use App\Http\Resources\PosinResource;
use App\Http\Resources\PosinCollection;
use App\Http\Resources\PosinItemResource;
use App\Exceptions\BusinessLogicException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Posin Management",
 *     description="進貨單管理相關API"
 * )
 */
class PosinControllerRefactored extends Controller
{
    protected PosinService $posinService;

    public function __construct(PosinService $posinService)
    {
        $this->posinService = $posinService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posin",
     *     summary="Get all posin records with pagination and search",
     *     tags={"Posin Management"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status filter",
     *         @OA\Schema(type="string", enum={"all", "進行中", "已完成"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of posin records",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="進貨單列表獲取成功"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PosinResource")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'status' => $request->input('status', 'all'),
            ];
            
            $perPage = $request->input('per_page', 10);
            
            $result = $this->posinService->getPosinList($filters, $perPage);
            
            return response()->json([
                'success' => true,
                'message' => '進貨單列表獲取成功',
                'data' => $result['data'],
                'pagination' => [
                    'current_page' => $result['current_page'],
                    'last_page' => $result['last_page'],
                    'per_page' => $result['per_page'],
                    'total' => $result['total'],
                    'from' => $result['from'],
                    'to' => $result['to'],
                ],
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'filters_applied' => array_filter($filters),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::index', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '獲取進貨單列表時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/posin",
     *     summary="Create a new posin record",
     *     tags={"Posin Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PosinCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Posin record created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="進貨單創建成功"),
     *             @OA\Property(property="data", ref="#/components/schemas/PosinResource")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=400, description="Business logic error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(PosinCreateRequest $request): JsonResponse
    {
        try {
            $posin = $this->posinService->createPosin($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => '進貨單創建成功',
                'data' => new PosinResource($posin),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ]
            ], 201);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::store', [
                'error' => $e->getMessage(),
                'request' => $request->validated()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '創建進貨單時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posin/{id}",
     *     summary="Get a specific posin record",
     *     tags={"Posin Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Posin record details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PosinResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Posin record not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $posin = $this->posinService->getPosin($id);
            
            return response()->json([
                'success' => true,
                'data' => new PosinResource($posin),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::show', [
                'posin_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '獲取進貨單詳情時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/posin/{id}",
     *     summary="Update a posin record",
     *     tags={"Posin Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PosinUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Posin record updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="進貨單更新成功"),
     *             @OA\Property(property="data", ref="#/components/schemas/PosinResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Posin record not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=400, description="Business logic error")
     * )
     */
    public function update(PosinUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $posin = $this->posinService->updatePosin($id, $request->validated());
            
            return response()->json([
                'success' => true,
                'message' => '進貨單更新成功',
                'data' => new PosinResource($posin),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::update', [
                'posin_id' => $id,
                'error' => $e->getMessage(),
                'request' => $request->validated()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '更新進貨單時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posin/{id}",
     *     summary="Delete a posin record",
     *     tags={"Posin Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Posin record deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="進貨單刪除成功")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Posin record not found"),
     *     @OA\Response(response=400, description="Cannot delete - business logic error")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->posinService->deletePosin($id);
            
            return response()->json([
                'success' => true,
                'message' => '進貨單刪除成功',
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::destroy', [
                'posin_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '刪除進貨單時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/posin/{id}/items",
     *     summary="Get posin items for a specific posin record",
     *     tags={"Posin Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of posin items",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PosinItemResource"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Posin record not found")
     * )
     */
    public function getPosinItems(int $id): JsonResponse
    {
        try {
            $items = $this->posinService->getPosinItems($id);
            
            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'items_count' => count($items),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::getPosinItems', [
                'posin_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '獲取進貨單項目時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/posin/{id}/generate-us-purchase-order",
     *     summary="Generate US purchase order for a posin record",
     *     tags={"Posin Management"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="US purchase order generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="美國進貨單轉換成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="status", type="string", example="generated")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Posin record not found"),
     *     @OA\Response(response=400, description="Already converted or cannot convert")
     * )
     */
    public function generateUsPurchaseOrder(int $id): JsonResponse
    {
        try {
            $result = $this->posinService->convertToUsPurchaseOrder($id);
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'status' => $result['status']
                ],
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::generateUsPurchaseOrder', [
                'posin_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '轉換美國進貨單時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/posin/batch",
     *     summary="Batch import purchase orders from CSV data",
     *     tags={"Posin Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="purchase_orders",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BatchPosinItem")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch import completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="批量匯入完成"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="created_count", type="integer"),
     *                 @OA\Property(property="error_count", type="integer"),
     *                 @OA\Property(property="errors", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=400, description="Business logic error")
     * )
     */
    public function batchStore(Request $request): JsonResponse
    {
        // 基本驗證
        $request->validate([
            'purchase_orders' => 'required|array|min:1',
            'purchase_orders.*.order_number' => 'required|string',
            'purchase_orders.*.user_name' => 'required|string',
            'purchase_orders.*.order_date' => 'required|date',
            'purchase_orders.*.item_id' => 'required|integer',
            'purchase_orders.*.item_batch' => 'required|string|max:20',
            'purchase_orders.*.item_count' => 'required|integer|min:1',
            'purchase_orders.*.item_price' => 'required|numeric|min:0',
            'purchase_orders.*.itemtype' => 'required|integer'
        ]);

        try {
            $result = $this->posinService->batchImportPosin($request->input('purchase_orders'));
            
            return response()->json([
                'success' => true,
                'message' => '批量匯入完成',
                'data' => $result,
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (BusinessLogicException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error('Error in PosinController::batchStore', [
                'error' => $e->getMessage(),
                'data_count' => count($request->input('purchase_orders', []))
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'type' => 'ServerError',
                    'message' => '批量匯入時發生錯誤',
                ],
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }
}