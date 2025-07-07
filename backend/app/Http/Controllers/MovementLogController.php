<?php

namespace App\Http\Controllers;

use App\Models\MovementLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementLogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/movement-logs",
     *     summary="Get movement logs with pagination and filters",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="movement_type",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_range",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Movement logs retrieved successfully")
     * )
     */
    public function index(Request $request)
    {
        $query = MovementLog::query();

        // 搜尋條件
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('from_location_code', 'like', "%{$search}%")
                  ->orWhere('to_location_code', 'like', "%{$search}%");
            });
        }

        // 移動類型篩選
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        // 日期範圍篩選
        if ($request->filled('date_range')) {
            $dateRange = $request->get('date_range');
            $query->whereDate('moved_at', $dateRange);
        }

        // 統計資料
        $statistics = [
            'total' => MovementLog::count(),
            'assignments' => MovementLog::where('movement_type', 'assign')->count(),
            'moves' => MovementLog::where('movement_type', 'move')->count(),
            'returns' => MovementLog::where('movement_type', 'return')->count(),
        ];

        // 分頁
        $perPage = $request->get('per_page', 20);
        $logs = $query->orderBy('moved_at', 'desc')
                      ->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'total' => $logs->total(),
            'per_page' => $logs->perPage(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'statistics' => $statistics
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/movement-logs/{id}",
     *     summary="Get specific movement log",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Movement log retrieved successfully"),
     *     @OA\Response(response="404", description="Movement log not found")
     * )
     */
    public function show($id)
    {
        $log = MovementLog::find($id);

        if (!$log) {
            return response()->json(['message' => 'Movement log not found'], 404);
        }

        return response()->json($log);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/movement-logs/qr-code/{qrCodeId}",
     *     summary="Get movement history for specific QR code",
     *     @OA\Parameter(
     *         name="qrCodeId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Movement history retrieved successfully")
     * )
     */
    public function getQrCodeHistory($qrCodeId)
    {
        $logs = MovementLog::where('qr_code_id', $qrCodeId)
                           ->orderBy('moved_at', 'desc')
                           ->get();

        return response()->json([
            'qr_code_id' => $qrCodeId,
            'movement_history' => $logs
        ]);
    }
}
