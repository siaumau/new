<?php

namespace App\Http\Controllers;

use App\Models\Posin;
use App\Models\PosinItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosinController extends Controller
{
    /**
     * @OA\Get(
     *     path="/posin",
     *     summary="Get all posin records with pagination and search",
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
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Paginated list of posin records")
     * )
     */
    public function index(Request $request)
    {
        $query = Posin::with('posinItems');

        // 搜尋功能
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('posin_sn', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('posin_user', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('posin_note', 'LIKE', "%{$searchTerm}%");
            });
        }

        // 狀態篩選（這裡我們用一個簡單的邏輯來判斷狀態）
        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            // 由於原始表格沒有狀態欄位，我們根據 posin_log 是否為空來判斷
            if ($request->status === '已完成') {
                $query->whereNotNull('posin_log');
            } elseif ($request->status === '進行中') {
                $query->whereNull('posin_log');
            }
        }

        // 排序
        $query->orderBy('posin_dt', 'desc');

        // 分頁
        $perPage = $request->get('per_page', 10);
        $posinRecords = $query->paginate($perPage);

                // 格式化每個項目的資料以符合前端需求
        $formattedItems = [];
        foreach ($posinRecords->items() as $posin) {
            $formattedItems[] = [
                'id' => $posin->posin_id,
                'order_number' => $posin->posin_sn,
                'supplier' => $posin->posin_user,
                'purchase_date' => $posin->posin_dt ? date('Y/n/j', strtotime($posin->posin_dt)) : '',
                'created_at' => $posin->posin_dt ? date('Y/n/j', strtotime($posin->posin_dt)) : '',
                'status' => $posin->posin_log ? '已完成' : '進行中',
                'items_count' => $posin->posinItems->count(),
                'total_amount' => $posin->posinItems->sum('item_price'),
                'notes' => $posin->posin_note,
                'posin_items' => $posin->posinItems
            ];
        }

        return response()->json([
            'data' => $formattedItems,
            'current_page' => $posinRecords->currentPage(),
            'last_page' => $posinRecords->lastPage(),
            'per_page' => $posinRecords->perPage(),
            'total' => $posinRecords->total(),
            'from' => $posinRecords->firstItem(),
            'to' => $posinRecords->lastItem(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/posin",
     *     summary="Create a new posin record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="_users_id", type="integer"),
     *             @OA\Property(property="posin_sn", type="string"),
     *             @OA\Property(property="posin_user", type="string"),
     *             @OA\Property(property="posin_dt", type="string", format="date-time"),
     *             @OA\Property(property="posin_note", type="string"),
     *             @OA\Property(property="posin_items", type="array", @OA\Items(
     *                 @OA\Property(property="itemtype", type="integer"),
     *                 @OA\Property(property="item_id", type="integer"),
     *                 @OA\Property(property="item_name", type="string"),
     *                 @OA\Property(property="item_sn", type="string"),
     *                 @OA\Property(property="item_spec", type="string"),
     *                 @OA\Property(property="item_batch", type="string"),
     *                 @OA\Property(property="item_count", type="integer"),
     *                 @OA\Property(property="item_price", type="number"),
     *                 @OA\Property(property="item_expireday", type="string", format="date"),
     *                 @OA\Property(property="item_validyear", type="string"),
     *             ))
     *         )
     *     ),
     *     @OA\Response(response="201", description="Posin record created successfully"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '_users_id' => 'required|integer',
            'posin_sn' => 'required|string',
            'posin_user' => 'required|string',
            'posin_dt' => 'required|date',
            'posin_note' => 'required|string',
            'posin_items' => 'required|array',
            'posin_items.*.itemtype' => 'required|integer',
            'posin_items.*.item_id' => 'required|integer',
            'posin_items.*.item_name' => 'required|string',
            'posin_items.*.item_sn' => 'required|string',
            'posin_items.*.item_spec' => 'required|string',
            'posin_items.*.item_batch' => 'required|string|max:20',
            'posin_items.*.item_count' => 'required|integer',
            'posin_items.*.item_price' => 'required|numeric',
            'posin_items.*.item_expireday' => 'nullable|date',
            'posin_items.*.item_validyear' => 'nullable|string|max:10',
        ]);

        DB::beginTransaction();
        try {
            $posin = Posin::create($validatedData);

            foreach ($validatedData['posin_items'] as $itemData) {
                $posin->posinItems()->create($itemData);
            }

            DB::commit();
            return response()->json($posin->load('posinItems'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating posin record', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/posin/{id}",
     *     summary="Get a specific posin record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Posin record details"),
     *     @OA\Response(response="404", description="Posin record not found")
     * )
     */
    public function show($id)
    {
        $posin = Posin::with('posinItems')->find($id);
        if (!$posin) {
            return response()->json(['message' => 'Posin record not found'], 404);
        }
        return response()->json($posin);
    }

    /**
     * @OA\Put(
     *     path="/posin/{id}",
     *     summary="Update a posin record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="_users_id", type="integer"),
     *             @OA\Property(property="posin_sn", type="string"),
     *             @OA\Property(property="posin_user", type="string"),
     *             @OA\Property(property="posin_dt", type="string", format="date-time"),
     *             @OA\Property(property="posin_note", type="string"),
     *             @OA\Property(property="posin_items", type="array", @OA\Items(
     *                 @OA\Property(property="posinitem_id", type="integer", nullable=true),
     *                 @OA\Property(property="itemtype", type="integer"),
     *                 @OA\Property(property="item_id", type="integer"),
     *                 @OA\Property(property="item_name", type="string"),
     *                 @OA\Property(property="item_sn", type="string"),
     *                 @OA\Property(property="item_spec", type="string"),
     *                 @OA\Property(property="item_batch", type="string"),
     *                 @OA\Property(property="item_count", type="integer"),
     *                 @OA\Property(property="item_price", type="number"),
     *                 @OA\Property(property="item_expireday", type="string", format="date"),
     *                 @OA\Property(property="item_validyear", type="string"),
     *             ))
     *         )
     *     ),
     *     @OA\Response(response="200", description="Posin record updated successfully"),
     *     @OA\Response(response="404", description="Posin record not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $posin = Posin::find($id);
        if (!$posin) {
            return response()->json(['message' => 'Posin record not found'], 404);
        }

        $validatedData = $request->validate([
            '_users_id' => 'required|integer',
            'posin_sn' => 'required|string',
            'posin_user' => 'required|string',
            'posin_dt' => 'required|date',
            'posin_note' => 'required|string',
            'posin_items' => 'required|array',
            'posin_items.*.posinitem_id' => 'nullable|integer',
            'posin_items.*.itemtype' => 'required|integer',
            'posin_items.*.item_id' => 'required|integer',
            'posin_items.*.item_name' => 'required|string',
            'posin_items.*.item_sn' => 'required|string',
            'posin_items.*.item_spec' => 'required|string',
            'posin_items.*.item_batch' => 'required|string|max:20',
            'posin_items.*.item_count' => 'required|integer',
            'posin_items.*.item_price' => 'required|numeric',
            'posin_items.*.item_expireday' => 'nullable|date',
            'posin_items.*.item_validyear' => 'nullable|string|max:10',
        ]);

        DB::beginTransaction();
        try {
            $posin->update($validatedData);

            // Delete existing posin items and re-create them
            $posin->posinItems()->delete();
            foreach ($validatedData['posin_items'] as $itemData) {
                $posin->posinItems()->create($itemData);
            }

            DB::commit();
            return response()->json($posin->load('posinItems'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating posin record', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/posin/{id}",
     *     summary="Delete a posin record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Posin record deleted successfully"),
     *     @OA\Response(response="404", description="Posin record not found")
     * )
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $posin = Posin::find($id);
            if (!$posin) {
                return response()->json(['message' => 'Posin record not found'], 404);
            }

            $posin->posinItems()->delete();
            $posin->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting posin record', 'error' => $e->getMessage()], 500);
        }
    }
}
