<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Item",
 *     type="object",
 *     @OA\Property(property="qr_id", type="integer"),
 *     @OA\Property(property="posin_id", type="integer"),
 *     @OA\Property(property="posinitem_id", type="integer"),
 *     @OA\Property(property="item_code", type="string"),
 *     @OA\Property(property="item_name", type="string"),
 *     @OA\Property(property="item_batch", type="string"),
 *     @OA\Property(property="expiry_date", type="string", format="date-time"),
 *     @OA\Property(property="box_number", type="integer"),
 *     @OA\Property(property="location_id", type="integer"),
 *     @OA\Property(property="floor_level", type="string"),
 *     @OA\Property(property="qr_content", type="string"),
 *     @OA\Property(property="file_name", type="string"),
 *     @OA\Property(property="zip_file_name", type="string"),
 *     @OA\Property(property="generated_at", type="string", format="date-time"),
 *     @OA\Property(property="generated_by", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="notes", type="string"),
 *     @OA\Property(property="posin_sn", type="string"),
 *     @OA\Property(property="posin_user", type="string"),
 *     @OA\Property(property="posin_dt", type="string", format="date-time"),
 *     @OA\Property(property="location_code", type="string"),
 *     @OA\Property(property="location_name", type="string"),
 *     @OA\Property(property="building_code", type="string"),
 *     @OA\Property(property="storage_type_code", type="string")
 * )
 */
class ItemController extends Controller
{
    /**
     * @OA\Get(
     *      path="/items",
     *      operationId="getItems",
     *      tags={"default"},
     *      summary="Get list of items",
     *      description="Returns list of items",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 1000);
        $items = Item::paginate($pageSize);

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'total' => $items->total(),
            'page' => $items->currentPage(),
            'pageSize' => $items->perPage(),
            'totalPages' => $items->lastPage(),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/items/{id}",
     *      operationId="getItem",
     *      tags={"default"},
     *      summary="Get item by ID",
     *      description="Returns a single item",
     *      @OA\Parameter(
     *          name="id",
     *          description="Item id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Item not found"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $item = Item::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
                'error' => 'The requested item does not exist.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error fetching item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
