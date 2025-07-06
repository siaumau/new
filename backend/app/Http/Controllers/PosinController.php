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
     *     path="/api/v1/posin",
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
                'notes' => $posin->posin_note,
                'us_purchase_order_status' => $posin->us_purchase_order_status ?? 'pending',
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
     *     path="/api/v1/posin",
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
     *     path="/api/v1/posin/{id}",
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
     *     path="/api/v1/posin/{id}",
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
            'posin_dt' => 'nullable|date',
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
     * @OA\Patch(
     *     path="/api/v1/posin/{id}/generate-us-purchase-order",
     *     summary="Generate US purchase order for a posin record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="US purchase order generated successfully"),
     *     @OA\Response(response="404", description="Posin record not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function generateUsPurchaseOrder($id)
    {
        $posin = Posin::find($id);
        if (!$posin) {
            return response()->json(['message' => 'Posin record not found'], 404);
        }

        // 檢查是否已經產生過美國進貨單
        if ($posin->us_purchase_order_status !== 'pending') {
            return response()->json([
                'message' => 'US purchase order has already been generated for this record',
                'status' => $posin->us_purchase_order_status
            ], 422);
        }

        try {
            $posin->update([
                'us_purchase_order_status' => 'generated'
            ]);

            return response()->json([
                'message' => 'US purchase order generated successfully',
                'status' => 'generated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating US purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posin/{id}",
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

    /**
     * @OA\Get(
     *     path="/api/v1/posin/{id}/items",
     *     summary="Get posin items for a specific posin record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="List of posin items"),
     *     @OA\Response(response="404", description="Posin record not found")
     * )
     */
    public function getPosinItems($id)
    {
        $posin = Posin::find($id);
        if (!$posin) {
            return response()->json(['message' => 'Posin record not found'], 404);
        }

        $posinItems = $posin->posinItems()->with('item')->get();

        // Format the data to match the frontend expectations
        $formattedItems = $posinItems->map(function ($item) use ($posin) {
            return [
                'posinitem_id' => $item->posinitem_id,
                'posin_id' => $item->posin_id,
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_sn' => $item->item_sn,
                'item_spec' => $item->item_spec,
                'item_batch' => $item->item_batch,
                'item_count' => $item->item_count,
                'item_price' => $item->item_price,
                'item_expireday' => $item->item_expireday,
                'item_validyear' => $item->item_validyear,
                'itemtype' => $item->itemtype,
                'posin' => [
                    'posin_id' => $posin->posin_id,
                    'posin_sn' => $posin->posin_sn,
                    'posin_user' => $posin->posin_user,
                    'posin_dt' => $posin->posin_dt,
                    'posin_note' => $posin->posin_note,
                ]
            ];
        });

        return response()->json($formattedItems);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posin-items/{id}",
     *     summary="Delete a posin item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Posin item deleted successfully"),
     *     @OA\Response(response="404", description="Posin item not found")
     * )
     */
    public function deletePosinItem($id)
    {
        try {
            $posinItem = PosinItem::find($id);

            if (!$posinItem) {
                return response()->json(['message' => 'Posin item not found'], 404);
            }

            $posinItem->delete();

            return response()->json(['message' => 'Posin item deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting posin item', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/generate-qr-labels",
     *     summary="Generate QR code labels for a posin item",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item", type="object"),
     *             @OA\Property(property="count", type="integer")
     *         )
     *     ),
     *     @OA\Response(response="200", description="QR labels generated successfully"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function generateQRLabels(Request $request)
    {
        $validatedData = $request->validate([
            'item' => 'required|array',
            'item.posinitem_id' => 'required|integer',
            'item.item_sn' => 'required|string',
            'item.item_name' => 'required|string',
            'item.item_spec' => 'required|string',
            'item.item_batch' => 'required|string',
            'item.item_count' => 'required|integer',
            'item.item_expireday' => 'nullable|date',
            'count' => 'required|integer|min:1'
        ]);

        try {
            $item = $validatedData['item'];
            $count = $validatedData['count'];

            // Generate QR codes
            $qrCodes = [];
            for ($i = 1; $i <= $count; $i++) {
                $qrData = $this->generateQRData($item, $i);
                $qrCodes[] = [
                    'data' => $qrData,
                    'serial' => str_pad($i, 3, '0', STR_PAD_LEFT),
                    'item_info' => $item
                ];
            }

            // Generate PDF with QR codes
            $pdfContent = $this->generateQRLabelsPDF($qrCodes);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="qr_labels_' . $item['item_sn'] . '.pdf"'
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error generating QR labels', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate QR data string for an item
     */
    private function generateQRData($item, $serialNumber)
    {
        $expireDate = $item['item_expireday'] ? date('Ym', strtotime($item['item_expireday'])) : '';
        $serial = str_pad($serialNumber, 3, '0', STR_PAD_LEFT);

        return $item['item_sn'] . $item['item_batch'] . $expireDate . $serial;
    }

    /**
     * Generate PDF with QR code labels
     */
    private function generateQRLabelsPDF($qrCodes)
    {
        // This is a placeholder implementation
        // In a real application, you would use a library like DomPDF or TCPDF
        // along with a QR code generator like SimpleSoftwareIO/simple-qrcode

        $html = '<html><body>';
        $html .= '<style>
            .label {
                width: 200px;
                height: 150px;
                border: 1px solid #000;
                margin: 10px;
                padding: 10px;
                float: left;
                font-family: Arial, sans-serif;
                font-size: 12px;
            }
            .qr-code {
                width: 80px;
                height: 80px;
                border: 1px solid #ccc;
                margin: 5px auto;
                display: block;
                text-align: center;
                line-height: 80px;
                background-color: #f0f0f0;
            }
            .item-info {
                margin-top: 10px;
                font-size: 10px;
            }
        </style>';

        foreach ($qrCodes as $qrCode) {
            $html .= '<div class="label">';
            $html .= '<div class="qr-code">QR: ' . $qrCode['data'] . '</div>';
            $html .= '<div class="item-info">';
            $html .= '<strong>' . $qrCode['item_info']['item_name'] . '</strong><br>';
            $html .= '序號: ' . $qrCode['item_info']['item_sn'] . '<br>';
            $html .= '規格: ' . $qrCode['item_info']['item_spec'] . '<br>';
            $html .= '批號: ' . $qrCode['item_info']['item_batch'] . '<br>';
            $html .= '編碼: ' . $qrCode['data'] . '<br>';
            $html .= '標籤: ' . $qrCode['serial'] . '/' . count($qrCodes);
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</body></html>';

        // For now, return HTML content
        // In production, you should convert this to PDF
        return $html;
    }
}
