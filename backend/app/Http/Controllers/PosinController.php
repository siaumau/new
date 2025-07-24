<?php

namespace App\Http\Controllers;

use App\Models\Posin;
use App\Models\PosinItem;
use App\Models\QrCode;
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
        try {
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
            try {
                $purchaseDate = '';
                $createdAt = '';

                if ($posin->posin_dt) {
                    try {
                        $purchaseDate = date('Y/n/j', strtotime($posin->posin_dt));
                        $createdAt = date('Y/n/j', strtotime($posin->posin_dt));
                    } catch (\Exception $e) {
                        // 如果日期格式有問題，使用原始值
                        $purchaseDate = $posin->posin_dt;
                        $createdAt = $posin->posin_dt;
                    }
                }

                $formattedItems[] = [
                    'id' => $posin->posin_id,
                    'order_number' => $posin->posin_sn ?? '',
                    'supplier' => $posin->posin_user ?? '',
                    'purchase_date' => $purchaseDate,
                    'created_at' => $createdAt,
                    'status' => $posin->posin_log ? '已完成' : '進行中',
                    'items_count' => $posin->posinItems ? $posin->posinItems->count() : 0,
                    'notes' => $posin->posin_note ?? '',
                    'us_purchase_order_status' => $posin->us_purchase_order_status ?? 'pending',
                    'posin_items' => $posin->posinItems ?? []
                ];
            } catch (\Exception $e) {
                // 如果某個記錄有問題，記錄錯誤但繼續處理其他記錄
                \Illuminate\Support\Facades\Log::error('Error formatting posin record: ' . $e->getMessage(), [
                    'posin_id' => $posin->posin_id ?? 'unknown'
                ]);
                continue;
            }
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in PosinController index: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
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
     *                 @OA\Property(property="item_validyear", type="string")
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
     *                 @OA\Property(property="item_validyear", type="string")
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
     * @OA\Get(
     *     path="/api/v1/check-qr-generated/{posinitem_id}",
     *     summary="Check if QR codes have been generated for a posin item",
     *     @OA\Parameter(
     *         name="posinitem_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="QR generation status"),
     *     @OA\Response(response="404", description="Posin item not found")
     * )
     */
    public function checkQRGenerated($posinitemId)
    {
        try {
            $posinItem = PosinItem::find($posinitemId);
            if (!$posinItem) {
                return response()->json(['message' => 'Posin item not found'], 404);
            }

            $qrCodes = QrCode::where('posinitem_id', $posinitemId)->get();

            return response()->json([
                'generated' => $qrCodes->count() > 0,
                'count' => $qrCodes->count(),
                'zip_file_name' => $qrCodes->first()->zip_file_name ?? null,
                'generated_at' => $qrCodes->first()->generated_at ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error checking QR generation status', 'error' => $e->getMessage()], 500);
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
            'item.posin_id' => 'required|integer',
            'item.item_id' => 'required|integer',
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

            // 檢查是否已經生成過 QR Code
            $existingQRs = QrCode::where('posinitem_id', $item['posinitem_id'])->count();
            if ($existingQRs > 0) {
                return response()->json(['message' => 'QR codes already generated for this item'], 400);
            }

            // 獲取商品詳細資訊（包括 item_inbox）
            $itemDetails = \App\Models\Item::find($item['item_id']);
            $itemInbox = $itemDetails ? $itemDetails->item_inbox : 48;

            // 獲取進貨單資訊（包括 posin_note）
            $posinDetails = \App\Models\Posin::find($item['posin_id']);
            $posinNote = $posinDetails ? $posinDetails->posin_note : '';

            // 生成 ZIP 檔案名稱
            $zipFileName = $this->generateZipFileName($item, $count);

            DB::beginTransaction();

            // 生成 QR codes 並寫入資料表
            $qrCodes = [];
            for ($i = 1; $i <= $count; $i++) {
                $qrData = $this->generateQRData($item, $i);
                $fileName = $this->generateFileName($item, $i);

                // 寫入 qr_codes 資料表
                QrCode::create([
                    'posin_id' => $item['posin_id'],
                    'posinitem_id' => $item['posinitem_id'],
                    'item_id' => $item['item_id'], // 添加item_id
                    'item_code' => $item['item_sn'],
                    'item_name' => $item['item_name'],
                    'item_batch' => $item['item_batch'],
                    'expiry_date' => $item['item_expireday'], // 從posinitem獲取到期日
                    'box_number' => $i,
                    'qr_content' => $qrData,
                    'file_name' => $fileName,
                    'zip_file_name' => $zipFileName,
                    'generated_at' => now(),
                    'generated_by' => 'user', // 這裡可以改為實際的使用者
                    'status' => 'generated',
                    'notes' => "外箱標籤 {$i}/{$count}",
                    'item_inbox_status' => 0, // 預設為未入庫
                    'item_inbox' => $itemInbox // 從item資料表獲取的每箱產品數量
                ]);

                $qrCodes[] = [
                    'data' => $qrData,
                    'serial' => str_pad($i, 3, '0', STR_PAD_LEFT),
                    'item_info' => array_merge($item, [
                        'item_inbox' => $itemInbox,
                        'posin_note' => $posinNote
                    ]),
                    'file_name' => $fileName
                ];
            }

            DB::commit();

            // 回傳 QR Code 資料給前端處理
            return response()->json([
                'message' => 'QR codes generated successfully',
                'qr_codes' => $qrCodes,
                'zip_file_name' => $zipFileName,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error generating QR labels', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate QR data string for an item
     */
    private function generateQRData($item, $serialNumber)
    {
        $expireDate = $item['item_expireday'] ? date('Ym', strtotime($item['item_expireday'])) : '';
        $serial = str_pad($serialNumber, 9, '0', STR_PAD_LEFT);

        return $item['item_sn'] . '-' . $item['item_batch'] . '-' . $expireDate . '-' . $serial;
    }

    /**
     * Generate ZIP file name for QR labels
     */
    private function generateZipFileName($item, $count)
    {
        $date = date('Y-m-d');
        $time = date('His');
        return $item['item_sn'] . '-' . $item['item_batch'] . '-' . $date . '_' . $time . '_QR_LABELS_QTY_' . $count . '.zip';
    }

    /**
     * Generate individual file name for QR code
     */
    private function generateFileName($item, $serialNumber)
    {
        $expireDate = $item['item_expireday'] ? date('Ymd', strtotime($item['item_expireday'])) : date('Ymd');
        $serial = str_pad($serialNumber, 9, '0', STR_PAD_LEFT);
        return $item['item_sn'] . '-' . $item['item_batch'] . '-' . $expireDate . '-' . $serial . '.png';
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
            $html .= '標籤: '  . count($qrCodes).'箱之'.$qrCode['serial'];
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</body></html>';

        // For now, return HTML content
        // In production, you should convert this to PDF
        return $html;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/save-qr-files",
     *     summary="Save QR code files to public directory",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary")),
     *                 @OA\Property(property="zip_file", type="string", format="binary"),
     *                 @OA\Property(property="item_info", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Files saved successfully"),
     *     @OA\Response(response="500", description="Error saving files")
     * )
     */
    public function saveQRFiles(Request $request)
    {
        try {
            // 確保 qr_codes 目錄存在
            $qrCodesPath = public_path('qr_codes');
            if (!file_exists($qrCodesPath)) {
                mkdir($qrCodesPath, 0755, true);
            }

            $savedFiles = [];

            // 處理個別檔案
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $fileName = $file->getClientOriginalName();
                    $filePath = $qrCodesPath . '/' . $fileName;

                    // 儲存檔案
                    $file->move($qrCodesPath, $fileName);
                    $savedFiles[] = $fileName;
                }
            }

            // 處理 ZIP 檔案
            if ($request->hasFile('zip_file')) {
                $zipFile = $request->file('zip_file');
                $zipFileName = $zipFile->getClientOriginalName();
                $zipFilePath = $qrCodesPath . '/' . $zipFileName;

                // 儲存 ZIP 檔案
                $zipFile->move($qrCodesPath, $zipFileName);
                $savedFiles[] = $zipFileName;
            }

            // 記錄儲存資訊
            $itemInfo = json_decode($request->input('item_info'), true);
            $logMessage = sprintf(
                'QR Code 檔案已儲存到 public/qr_codes 目錄 - 商品: %s (SN: %s, 批號: %s, 數量: %d)',
                $itemInfo['item_name'] ?? '未知',
                $itemInfo['item_sn'] ?? '未知',
                $itemInfo['item_batch'] ?? '未知',
                $itemInfo['count'] ?? 0
            );

            \Illuminate\Support\Facades\Log::info($logMessage);

            return response()->json([
                'message' => 'Files saved successfully',
                'saved_files' => $savedFiles,
                'directory' => $qrCodesPath
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saving QR files: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error saving files',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/posin/batch",
     *     summary="Batch import purchase orders from CSV data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="purchase_orders", type="array", @OA\Items(
     *                 @OA\Property(property="order_number", type="string"),
     *                 @OA\Property(property="user_name", type="string"),
     *                 @OA\Property(property="order_date", type="string"),
     *                 @OA\Property(property="expected_date", type="string"),
     *                 @OA\Property(property="notes", type="string"),
     *                 @OA\Property(property="item_id", type="integer"),
     *                 @OA\Property(property="item_batch", type="string"),
     *                 @OA\Property(property="item_count", type="integer"),
     *                 @OA\Property(property="item_price", type="number"),
     *                 @OA\Property(property="item_expireday", type="string"),
     *                 @OA\Property(property="item_validyear", type="integer"),
     *                 @OA\Property(property="itemtype", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response="200", description="Batch import completed"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function batchStore(Request $request)
    {
        $request->validate([
            'purchase_orders' => 'required|array|min:1',
            'purchase_orders.*.order_number' => 'required|string',
            'purchase_orders.*.user_name' => 'required|string',
            'purchase_orders.*.order_date' => 'nullable|date',
            'purchase_orders.*.expected_date' => 'nullable|date',
            'purchase_orders.*.notes' => 'nullable|string',
            'purchase_orders.*.item_id' => 'required|integer',
            'purchase_orders.*.item_batch' => 'required|string|max:20',
            'purchase_orders.*.item_count' => 'required|integer|min:1',
            'purchase_orders.*.item_price' => 'nullable|numeric|min:0',
            'purchase_orders.*.item_expireday' => 'nullable|date',
            'purchase_orders.*.item_validyear' => 'nullable|integer',
            'purchase_orders.*.itemtype' => 'nullable|integer'
        ]);

        DB::beginTransaction();

        try {
            $purchaseOrdersData = $request->input('purchase_orders');
            $createdCount = 0;
            $errorCount = 0;
            $errors = [];

            // 按進貨單號分組資料
            $groupedOrders = [];
            foreach ($purchaseOrdersData as $orderData) {
                $orderNumber = $orderData['order_number'];
                if (!isset($groupedOrders[$orderNumber])) {
                    $groupedOrders[$orderNumber] = [
                        'order_info' => [
                            'order_number' => $orderData['order_number'],
                            'user_name' => $orderData['user_name'],
                            'order_date' => $orderData['order_date'] ?? now()->format('Y-m-d'),
                            'expected_date' => $orderData['expected_date'] ?? null,
                            'notes' => $orderData['notes'] ?? null,
                        ],
                        'items' => []
                    ];
                }

                // 添加商品項目
                $groupedOrders[$orderNumber]['items'][] = [
                    'item_id' => $orderData['item_id'],
                    'item_batch' => $orderData['item_batch'],
                    'item_count' => $orderData['item_count'],
                    'item_price' => $orderData['item_price'] ?? 0,
                    'item_expireday' => $orderData['item_expireday'] ?? null,
                    'item_validyear' => $orderData['item_validyear'] ?? null,
                    'itemtype' => $orderData['itemtype'] ?? 1
                ];
            }

                                    // 處理每個進貨單
            foreach ($groupedOrders as $orderNumber => $orderGroup) {
                try {
                    $orderInfo = $orderGroup['order_info'];
                    $hasErrors = false;
                    $itemErrors = [];

                    // 先驗證所有商品是否存在，如果有任何商品不存在就不創建進貨單
                    foreach ($orderGroup['items'] as $itemData) {
                        $item = \App\Models\Item::find($itemData['item_id']);
                        if (!$item) {
                            $hasErrors = true;
                            $itemErrors[] = "商品ID {$itemData['item_id']} 不存在";
                        }
                    }

                    // 如果有商品不存在，跳過整個進貨單
                    if ($hasErrors) {
                        $errorCount++;
                        $errors[] = [
                            'order_number' => $orderNumber,
                            'error' => implode('; ', $itemErrors)
                        ];
                        continue;
                    }

                    // 檢查進貨單是否已存在
                    $existingPosin = Posin::where('posin_sn', $orderNumber)->first();

                    if ($existingPosin) {
                        // 檢查美國進貨單狀態，如果已經提交則不允許重複匯入
                        if ($existingPosin->us_purchase_order_status === 'generated' ||
                            $existingPosin->us_purchase_order_status === 'reviewed' ||
                            $existingPosin->us_purchase_order_status === 'completed') {
                            $errorCount++;
                            $errors[] = [
                                'order_number' => $orderNumber,
                                'error' => '進貨單已提交為美國進貨單，無法重複匯入'
                            ];
                            continue;
                        }

                        // 更新進貨單資訊
                        $existingPosin->update([
                            'posin_user' => $orderInfo['user_name'],
                            'posin_dt' => $orderInfo['order_date'],
                            'posin_note' => $orderInfo['notes'],
                        ]);

                        $posin = $existingPosin;
                    } else {
                        // 創建新的進貨單
                        $posin = Posin::create([
                            '_users_id' => 1, // 預設用戶ID，可以根據需要調整
                            'posin_sn' => $orderInfo['order_number'],
                            'posin_user' => $orderInfo['user_name'],
                            'posin_dt' => $orderInfo['order_date'],
                            'posin_note' => $orderInfo['notes'],
                            'us_purchase_order_status' => 'pending'
                        ]);
                        $createdCount++;
                    }

                    // 創建進貨單項目，檢查重複項目
                    $addedItemsCount = 0;
                    foreach ($orderGroup['items'] as $itemData) {
                        // 獲取商品資訊（已經驗證過存在）
                        $item = \App\Models\Item::find($itemData['item_id']);

                        // 檢查是否已存在相同的項目 (posin_id + item_id + item_batch + item_expireday)
                        $existingItem = PosinItem::where('posin_id', $posin->posin_id)
                            ->where('item_id', $itemData['item_id'])
                            ->where('item_batch', $itemData['item_batch'])
                            ->where('item_expireday', $itemData['item_expireday'])
                            ->first();

                        if ($existingItem) {
                            // 重複項目，記錄錯誤但不影響其他項目
                            $itemErrors[] = "商品ID {$itemData['item_id']} 批次 {$itemData['item_batch']} 到期日 {$itemData['item_expireday']} 已存在";
                            continue;
                        }

                        // 創建新的進貨單項目
                        PosinItem::create([
                            'posin_id' => $posin->posin_id,
                            'itemtype' => $itemData['itemtype'],
                            'item_id' => $itemData['item_id'],
                            'item_name' => $item->item_name,
                            'item_sn' => $item->item_sn,
                            'item_spec' => $item->item_spec,
                            'item_batch' => $itemData['item_batch'],
                            'item_count' => $itemData['item_count'],
                            'item_price' => $itemData['item_price'],
                            'item_expireday' => $itemData['item_expireday'],
                            'item_validyear' => $itemData['item_validyear']
                        ]);
                        $addedItemsCount++;
                    }

                    // 如果進貨單已存在且有新增項目，也算作成功
                    if ($existingPosin && $addedItemsCount > 0) {
                        $createdCount++;
                    }

                    // 如果有項目重複錯誤，記錄但不影響整個進貨單
                    if (!empty($itemErrors)) {
                        $errors[] = [
                            'order_number' => $orderNumber,
                            'error' => '部分項目重複: ' . implode('; ', $itemErrors)
                        ];
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'order_number' => $orderNumber,
                        'error' => $e->getMessage()
                    ];

                    // 記錄錯誤
                    \Illuminate\Support\Facades\Log::error("批量匯入進貨單失敗: {$orderNumber}", [
                        'error' => $e->getMessage(),
                        'order_data' => $orderGroup
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => '批量匯入完成',
                'created_count' => $createdCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('批量匯入進貨單失敗: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => '批量匯入失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qr-files",
     *     summary="Get list of saved QR code files",
     *     @OA\Response(response="200", description="List of QR code files")
     * )
     */
    public function getQRFiles()
    {
        try {
            $qrCodesPath = public_path('qr_codes');
            
            if (!file_exists($qrCodesPath)) {
                return response()->json(['files' => []]);
            }

            $files = [];
            $directories = scandir($qrCodesPath);
            
            foreach ($directories as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                
                $dirPath = $qrCodesPath . '/' . $dir;
                if (is_dir($dirPath)) {
                    $dirFiles = scandir($dirPath);
                    $pngFiles = array_filter($dirFiles, function($file) {
                        return pathinfo($file, PATHINFO_EXTENSION) === 'png';
                    });
                    $htmlFiles = array_filter($dirFiles, function($file) {
                        return pathinfo($file, PATHINFO_EXTENSION) === 'html';
                    });
                    
                    $allFiles = array_merge(array_values($pngFiles), array_values($htmlFiles));
                    
                    if (!empty($allFiles)) {
                        $files[] = [
                            'folder_name' => $dir,
                            'files' => $allFiles,
                            'file_count' => count($allFiles),
                            'file_types' => [
                                'png' => count($pngFiles),
                                'html' => count($htmlFiles)
                            ],
                            'created_at' => date('Y-m-d H:i:s', filemtime($dirPath))
                        ];
                    }
                }
            }

            // 按創建時間排序，最新的在前
            usort($files, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return response()->json(['files' => $files]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('獲取QR檔案列表失敗: ' . $e->getMessage());
            return response()->json([
                'message' => '獲取檔案列表失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/save-qr-html",
     *     summary="Save QR code HTML file",
     *     @OA\Response(response="200", description="HTML file saved successfully")
     * )
     */
    public function saveQRHTML(Request $request)
    {
        try {
            // 確保 qr_codes 目錄存在
            $qrCodesPath = public_path('qr_codes');
            if (!file_exists($qrCodesPath)) {
                mkdir($qrCodesPath, 0755, true);
            }

            $savedFiles = [];

            // 處理HTML檔案
            if ($request->hasFile('html_file')) {
                $file = $request->file('html_file');
                $fileName = $file->getClientOriginalName();
                
                // 根據商品資訊創建目錄名稱
                $itemInfo = json_decode($request->input('item_info'), true);
                $dirName = sprintf(
                    '%s_%s_%s_%d_labels',
                    $itemInfo['item_sn'],
                    $itemInfo['item_batch'],
                    date('Y-m-d_H-i-s'),
                    $itemInfo['count']
                );

                $itemPath = $qrCodesPath . '/' . $dirName;
                if (!file_exists($itemPath)) {
                    mkdir($itemPath, 0755, true);
                }

                // 儲存HTML檔案
                $filePath = $itemPath . '/' . $fileName;
                $file->move($itemPath, $fileName);
                $savedFiles[] = $fileName;

                // 記錄到日誌
                \Illuminate\Support\Facades\Log::info(
                    'QR Code HTML檔案已儲存到 public/qr_codes 目錄 - 商品: %s (SN: %s, 批號: %s, 數量: %d)',
                    [
                        $itemInfo['item_name'],
                        $itemInfo['item_sn'],
                        $itemInfo['item_batch'],
                        $itemInfo['count']
                    ]
                );
            }

            return response()->json([
                'message' => 'QR Code HTML檔案已成功儲存',
                'saved_files' => $savedFiles
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('儲存QR HTML檔案失敗: ' . $e->getMessage());
            return response()->json([
                'message' => '儲存HTML檔案失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
