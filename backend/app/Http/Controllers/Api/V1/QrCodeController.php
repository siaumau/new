<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/qr-codes-binded",
     *      operationId="getQrCodes",
     *      tags={"default"},
     *      summary="Get list of qr codes with item info",
     *      description="Returns list of qr codes with their associated item info",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Item")),
     *              @OA\Property(property="total", type="integer"),
     *              @OA\Property(property="page", type="integer"),
     *              @OA\Property(property="pageSize", type="integer"),
     *              @OA\Property(property="totalPages", type="integer")
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 1000);
        $page = $request->input('page', 1);

        $items = Item::with('qrCodes')->paginate($pageSize);

        $data = $items->map(function ($item) {
            return $item->qrCodes->map(function ($qrCode) use ($item) {
                return [
                    'qr_id' => $qrCode->qr_id,
                    'posin_id' => $qrCode->posin_id,
                    'posinitem_id' => $qrCode->posinitem_id,
                    'item_code' => $item->item_sn,
                    'item_name' => $item->item_name,
                    'item_batch' => $qrCode->item_batch,
                    'expiry_date' => $qrCode->expiry_date,
                    'box_number' => $qrCode->box_number,
                    'location_id' => $qrCode->location_id,
                    'floor_level' => $qrCode->floor_level,
                    'qr_content' => $qrCode->qr_content,
                    'file_name' => $qrCode->file_name,
                    'zip_file_name' => $qrCode->zip_file_name,
                    'generated_at' => $qrCode->generated_at,
                    'generated_by' => $qrCode->generated_by,
                    'status' => $qrCode->status,
                    'notes' => $qrCode->notes,
                    'posin_sn' => optional($qrCode->posin)->posin_sn,
                    'posin_user' => optional($qrCode->posin)->posin_user,
                    'posin_dt' => optional($qrCode->posin)->posin_dt,
                    'location_code' => optional($qrCode->location)->location_code,
                    'location_name' => optional($qrCode->location)->location_name,
                    'building_code' => optional($qrCode->location)->building,
                    'storage_type_code' => optional($qrCode->location)->storageType,
                ];
            });
        })->flatten(1);

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $items->total(),
            'page' => $items->currentPage(),
            'pageSize' => $items->perPage(),
            'totalPages' => $items->lastPage(),
        ]);
    }
}
