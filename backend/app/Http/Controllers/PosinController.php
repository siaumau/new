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
     *     summary="Get all posin records",
     *     @OA\Response(response="200", description="List of posin records")
     * )
     */
    public function index()
    {
        $posin = Posin::with('posinItems')->get();
        return response()->json($posin);
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
