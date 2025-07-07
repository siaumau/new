<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\Item;
use App\Models\Location;
use App\Models\Posin;
use App\Models\PosinItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QrCodeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/qr-codes",
     *     summary="Get all QR codes with pagination and search",
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
     *         @OA\Schema(type="string", enum={"generated", "printed", "used"})
     *     ),
     *     @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Location ID filter",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Paginated list of QR codes")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $status = $request->input('status');
        $locationId = $request->input('location_id');

        $query = QrCode::with(['posin', 'posinItem', 'location', 'item']);

        // 搜尋條件
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'LIKE', "%{$search}%")
                  ->orWhere('item_name', 'LIKE', "%{$search}%")
                  ->orWhere('item_batch', 'LIKE', "%{$search}%")
                  ->orWhere('qr_content', 'LIKE', "%{$search}%")
                  ->orWhere('file_name', 'LIKE', "%{$search}%")
                  ->orWhere('zip_file_name', 'LIKE', "%{$search}%");
            });
        }

        // 狀態篩選
        if ($status) {
            $query->where('status', $status);
        }

        // 位置篩選
        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        // 排序
        $query->orderBy('generated_at', 'desc');

        $qrCodes = $query->paginate($perPage);

        return response()->json($qrCodes);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qr-codes/{id}",
     *     summary="Get a specific QR code",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="QR code details"),
     *     @OA\Response(response="404", description="QR code not found")
     * )
     */
    public function show($id)
    {
        $qrCode = QrCode::with(['posin', 'posinItem', 'location', 'item'])->find($id);

        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        return response()->json($qrCode);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/qr-codes/{id}",
     *     summary="Update a QR code",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location_id", type="integer", nullable=true),
     *             @OA\Property(property="floor_level", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"generated", "printed", "used"}),
     *             @OA\Property(property="item_inbox_status", type="integer", enum={0, 1}),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response="200", description="QR code updated successfully"),
     *     @OA\Response(response="404", description="QR code not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $qrCode = QrCode::find($id);

        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        $validatedData = $request->validate([
            'location_id' => 'nullable|integer|exists:locations,id',
            'floor_level' => 'nullable|string|max:10',
            'status' => 'nullable|in:generated,printed,used',
            'item_inbox_status' => 'nullable|integer|in:0,1',
            'notes' => 'nullable|string'
        ]);

        $qrCode->update($validatedData);

        return response()->json([
            'message' => 'QR code updated successfully',
            'qr_code' => $qrCode->load(['posin', 'posinItem', 'location', 'item'])
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/qr-codes/{id}",
     *     summary="Delete a QR code",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="QR code deleted successfully"),
     *     @OA\Response(response="404", description="QR code not found")
     * )
     */
    public function destroy($id)
    {
        $qrCode = QrCode::find($id);

        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        $qrCode->delete();

        return response()->json(['message' => 'QR code deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/qr-codes/{id}/update-status",
     *     summary="Update QR code status",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"generated", "printed", "used"})
     *         )
     *     ),
     *     @OA\Response(response="200", description="Status updated successfully"),
     *     @OA\Response(response="404", description="QR code not found")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $qrCode = QrCode::find($id);

        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        $validatedData = $request->validate([
            'status' => 'required|in:generated,printed,used'
        ]);

        $qrCode->update(['status' => $validatedData['status']]);

        return response()->json([
            'message' => 'Status updated successfully',
            'qr_code' => $qrCode->load(['posin', 'posinItem', 'location', 'item'])
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/qr-codes/{id}/assign-location",
     *     summary="Assign location to QR code",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="location_id", type="integer"),
     *             @OA\Property(property="floor_level", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response="200", description="Location assigned successfully"),
     *     @OA\Response(response="404", description="QR code or location not found")
     * )
     */
    public function assignLocation(Request $request, $id)
    {
        $qrCode = QrCode::find($id);

        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        $validatedData = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'floor_level' => 'nullable|string|max:10'
        ]);

        $qrCode->update([
            'location_id' => $validatedData['location_id'],
            'floor_level' => $validatedData['floor_level'] ?? null,
            'item_inbox_status' => 1 // 分配位置時自動標記為已入庫
        ]);

        return response()->json([
            'message' => 'Location assigned successfully',
            'qr_code' => $qrCode->load(['posin', 'posinItem', 'location', 'item'])
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qr-codes/statistics",
     *     summary="Get QR code statistics",
     *     @OA\Response(response="200", description="QR code statistics")
     * )
     */
    public function statistics()
    {
        $stats = [
            'total' => QrCode::count(),
            'by_status' => QrCode::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_inbox_status' => QrCode::select('item_inbox_status', DB::raw('count(*) as count'))
                ->groupBy('item_inbox_status')
                ->pluck('count', 'item_inbox_status'),
            'recent_generated' => QrCode::where('generated_at', '>=', now()->subDays(7))->count(),
            'with_location' => QrCode::whereNotNull('location_id')->count(),
            'without_location' => QrCode::whereNull('location_id')->count()
        ];

        return response()->json($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qr-codes/by-zip-file/{zipFileName}",
     *     summary="Get QR codes by ZIP file name",
     *     @OA\Parameter(
     *         name="zipFileName",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="QR codes in the ZIP file")
     * )
     */
    public function getByZipFile($zipFileName)
    {
        $qrCodes = QrCode::where('zip_file_name', $zipFileName)
            ->with(['posin', 'posinItem', 'location', 'item'])
            ->orderBy('box_number')
            ->get();

        if ($qrCodes->isEmpty()) {
            return response()->json(['message' => 'No QR codes found for this ZIP file'], 404);
        }

        return response()->json([
            'zip_file_name' => $zipFileName,
            'count' => $qrCodes->count(),
            'qr_codes' => $qrCodes
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/qr-codes/batch-update-status",
     *     summary="Batch update QR codes status",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="qr_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="status", type="string", enum={"generated", "printed", "used"})
     *         )
     *     ),
     *     @OA\Response(response="200", description="Batch status update completed"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function batchUpdateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'qr_ids' => 'required|array',
            'qr_ids.*' => 'integer|exists:qr_codes,qr_id',
            'status' => 'required|in:generated,printed,used'
        ]);

        $updatedCount = QrCode::whereIn('qr_id', $validatedData['qr_ids'])
            ->update(['status' => $validatedData['status']]);

        return response()->json([
            'message' => 'Batch status update completed',
            'updated_count' => $updatedCount
        ]);
    }
}
