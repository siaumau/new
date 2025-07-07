<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\QrCode;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/locations",
     *     summary="Get all locations",
     *     @OA\Response(response="200", description="List of locations")
     * )
     */
    public function index()
    {
        $locations = Location::all();
        return response()->json($locations);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/locations",
     *     summary="Create a new location",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location_code", type="string"),
     *             @OA\Property(property="location_name", type="string"),
     *             @OA\Property(property="building_code", type="string"),
     *             @OA\Property(property="floor_number", type="string"),
     *             @OA\Property(property="floor_area_code", type="string", nullable=true),
     *             @OA\Property(property="storage_type_code", type="string"),
     *             @OA\Property(property="sub_area_code", type="string", nullable=true),
     *             @OA\Property(property="position_code", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="current_stock", type="integer"),
     *             @OA\Property(property="qr_code_data", type="string", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="is_active", type="boolean"),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Location created successfully"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'location_code' => 'required|string|max:20|unique:locations',
            'location_name' => 'required|string|max:100',
            'building_code' => 'required|string|max:10',
            'floor_number' => 'required|string|max:10',
            'floor_area_code' => 'nullable|string|max:10',
            'storage_type_code' => 'required|string|max:20',
            'sub_area_code' => 'nullable|string|max:10',
            'position_code' => 'required|string|max:20',
            'capacity' => 'nullable|integer',
            'current_stock' => 'nullable|integer',
            'qr_code_data' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $location = Location::create($validatedData);
        return response()->json($location, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/locations/{id}",
     *     summary="Get a specific location",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Location details"),
     *     @OA\Response(response="404", description="Location not found")
     * )
     */
    public function show($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
        return response()->json($location);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/locations/{id}/floor-distribution",
     *     summary="Get floor distribution for a specific location",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Floor distribution data"),
     *     @OA\Response(response="404", description="Location not found"),
     *     @OA\Response(response="400", description="Not a shelf type location")
     * )
     */
    public function getFloorDistribution($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // 只有當storage_type_code是'Shelf'時才顯示層架分布
        if ($location->storage_type_code !== 'Shelf') {
            return response()->json(['message' => 'This location is not a shelf type'], 400);
        }

        // 從qr_codes資料表中獲取該位置的層架分布資料
        $floorDistribution = QrCode::where('location_id', $id)
            ->whereNotNull('floor_level')
            ->selectRaw('
                floor_level as floor,
                COUNT(*) as item_count,
                COUNT(DISTINCT item_code) as unique_items,
                GROUP_CONCAT(DISTINCT item_code) as items,
                SUM(CASE WHEN item_inbox_status = 1 THEN 1 ELSE 0 END) as inboxed_items,
                SUM(CASE WHEN item_inbox_status = 0 THEN 1 ELSE 0 END) as pending_items
            ')
            ->groupBy('floor_level')
            ->orderBy('floor_level')
            ->get();

        // 轉換資料格式
        $distributionData = $floorDistribution->map(function ($item) {
            return [
                'floor' => $item->floor,
                'itemCount' => $item->item_count,
                'uniqueItems' => $item->unique_items,
                'items' => explode(',', $item->items),
                'inboxedItems' => $item->inboxed_items,
                'pendingItems' => $item->pending_items
            ];
        });

        return response()->json([
            'location_id' => $id,
            'location_code' => $location->location_code,
            'location_name' => $location->location_name,
            'storage_type' => $location->storage_type_code,
            'floor_distribution' => $distributionData
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/locations/{id}/items",
     *     summary="Get items in a specific location",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Items in location"),
     *     @OA\Response(response="404", description="Location not found")
     * )
     */
    public function getLocationItems($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // 從qr_codes資料表中獲取該位置的商品資料，並關聯item資料表
        $items = QrCode::where('location_id', $id)
            ->with('item:item_id,item_barcode,item_inbox') // 載入關聯的item資料
            ->select([
                'qr_id',
                'item_code',
                'item_name',
                'item_batch',
                'expiry_date',
                'box_number',
                'floor_level',
                'qr_content',
                'generated_at',
                'generated_by',
                'status',
                'item_inbox_status',
                'item_inbox'
            ])
            ->orderBy('floor_level')
            ->orderBy('item_code')
            ->get();

        // 更新item_inbox欄位（從item資料表獲取）
        $items->each(function ($qrCode) {
            if ($qrCode->item && $qrCode->item_inbox !== $qrCode->item->item_inbox) {
                $qrCode->update(['item_inbox' => $qrCode->item->item_inbox]);
            }
        });

        return response()->json([
            'location_id' => $id,
            'location_code' => $location->location_code,
            'location_name' => $location->location_name,
            'items' => $items
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/locations/batch",
     *     summary="Create multiple locations",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="locations",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="location_code", type="string"),
     *                     @OA\Property(property="location_name", type="string"),
     *                     @OA\Property(property="building_code", type="string"),
     *                     @OA\Property(property="floor_number", type="string"),
     *                     @OA\Property(property="floor_area_code", type="string", nullable=true),
     *                     @OA\Property(property="storage_type_code", type="string"),
     *                     @OA\Property(property="sub_area_code", type="string", nullable=true),
     *                     @OA\Property(property="position_code", type="string"),
     *                     @OA\Property(property="capacity", type="integer"),
     *                     @OA\Property(property="current_stock", type="integer"),
     *                     @OA\Property(property="qr_code_data", type="string", nullable=true),
     *                     @OA\Property(property="notes", type="string", nullable=true),
     *                     @OA\Property(property="is_active", type="boolean"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Locations created successfully"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function batchStore(Request $request)
    {
        $request->validate([
            'locations' => 'required|array|min:1',
            'locations.*.location_code' => 'required|string|max:20|unique:locations',
            'locations.*.location_name' => 'required|string|max:100',
            'locations.*.building_code' => 'required|string|max:10',
            'locations.*.floor_number' => 'required|string|max:10',
            'locations.*.floor_area_code' => 'nullable|string|max:10',
            'locations.*.storage_type_code' => 'required|string|max:20',
            'locations.*.sub_area_code' => 'nullable|string|max:10',
            'locations.*.position_code' => 'required|string|max:20',
            'locations.*.capacity' => 'nullable|integer',
            'locations.*.current_stock' => 'nullable|integer',
            'locations.*.qr_code_data' => 'nullable|string',
            'locations.*.notes' => 'nullable|string',
            'locations.*.is_active' => 'nullable|boolean',
        ]);

        $locations = [];
        $errors = [];

        foreach ($request->locations as $index => $locationData) {
            try {
                $location = Location::create($locationData);
                $locations[] = $location;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'location_code' => $locationData['location_code'] ?? null,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Batch creation completed',
            'created_count' => count($locations),
            'error_count' => count($errors),
            'locations' => $locations,
            'errors' => $errors
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/locations/{id}",
     *     summary="Update a location",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location_code", type="string"),
     *             @OA\Property(property="location_name", type="string"),
     *             @OA\Property(property="building_code", type="string"),
     *             @OA\Property(property="floor_number", type="string"),
     *             @OA\Property(property="floor_area_code", type="string", nullable=true),
     *             @OA\Property(property="storage_type_code", type="string"),
     *             @OA\Property(property="sub_area_code", type="string", nullable=true),
     *             @OA\Property(property="position_code", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="current_stock", type="integer"),
     *             @OA\Property(property="qr_code_data", type="string", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="is_active", type="boolean"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Location updated successfully"),
     *     @OA\Response(response="404", description="Location not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        $validatedData = $request->validate([
            'location_code' => 'required|string|max:20|unique:locations,location_code,' . $id,
            'location_name' => 'required|string|max:100',
            'building_code' => 'required|string|max:10',
            'floor_number' => 'required|string|max:10',
            'floor_area_code' => 'nullable|string|max:10',
            'storage_type_code' => 'required|string|max:20',
            'sub_area_code' => 'nullable|string|max:10',
            'position_code' => 'required|string|max:20',
            'capacity' => 'nullable|integer',
            'current_stock' => 'nullable|integer',
            'qr_code_data' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $location->update($validatedData);
        return response()->json($location);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/locations/{id}",
     *     summary="Delete a location",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Location deleted successfully"),
     *     @OA\Response(response="404", description="Location not found"),
     *     @OA\Response(response="400", description="Cannot delete location with items")
     * )
     */
    public function destroy($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        // 檢查該位置是否有商品
        $itemCount = QrCode::where('location_id', $id)->count();
        if ($itemCount > 0) {
            return response()->json([
                'message' => '無法刪除此位置，因為櫃位上還有商品',
                'item_count' => $itemCount
            ], 400);
        }

        $location->delete();
        return response()->json(null, 204);
    }
}
