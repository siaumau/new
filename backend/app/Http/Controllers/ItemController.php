<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/items",
     *     summary="Get all items",
     *     @OA\Response(response="200", description="List of items")
     * )
     */
    public function index()
    {
        $items = Item::all();
        return response()->json($items);
    }

    /**
     * @OA\Post(
     *     path="/items",
     *     summary="Create a new item",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item_name", type="string"),
     *             @OA\Property(property="item_cid", type="integer"),
     *             @OA\Property(property="item_sn", type="string"),
     *             @OA\Property(property="item_spec", type="string"),
     *             @OA\Property(property="item_save", type="integer"),
     *             @OA\Property(property="item_price", type="number"),
     *             @OA\Property(property="suggested_retail_price", type="integer"),
     *             @OA\Property(property="item_note", type="string"),
     *             @OA\Property(property="item_open", type="integer"),
     *             @OA\Property(property="item_sort", type="integer"),
     *             @OA\Property(property="item_mstock", type="integer"),
     *             @OA\Property(property="item_type", type="string"),
     *             @OA\Property(property="item_years", type="string"),
     *             @OA\Property(property="item_holdmonth", type="integer"),
     *             @OA\Property(property="item_outvyear", type="string"),
     *             @OA\Property(property="item_predict", type="integer"),
     *             @OA\Property(property="item_insertdate", type="string", format="date-time"),
     *             @OA\Property(property="item_editdate", type="string", format="date-time"),
     *             @OA\Property(property="item_barcode", type="string"),
     *             @OA\Property(property="item_inbox", type="integer"),
     *             @OA\Property(property="ppt_id", type="integer"),
     *             @OA\Property(property="item_vcode", type="string"),
     *             @OA\Property(property="item_size", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Item created successfully"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'item_name' => 'required|string|max:200',
            'item_cid' => 'required|boolean',
            'item_sn' => 'required|string|max:200',
            'item_spec' => 'required|string|max:200',
            'item_save' => 'required|integer',
            'item_price' => 'required|numeric',
            'suggested_retail_price' => 'required|integer',
            'item_note' => 'required|string',
            'item_open' => 'required|boolean',
            'item_sort' => 'required|integer',
            'item_mstock' => 'required|boolean',
            'item_type' => 'required|string',
            'item_years' => 'nullable|string|max:10',
            'item_holdmonth' => 'required|boolean',
            'item_outvyear' => 'required|string',
            'item_predict' => 'required|boolean',
            'item_insertdate' => 'required|date',
            'item_editdate' => 'required|date',
            'item_barcode' => 'required|string|max:20',
            'item_inbox' => 'required|integer',
            'ppt_id' => 'required|integer',
            'item_vcode' => 'nullable|string|max:8',
            'item_size' => 'nullable|string|max:20',
        ]);

        $item = Item::create($validatedData);
        return response()->json($item, 201);
    }

    /**
     * @OA\Get(
     *     path="/items/{id}",
     *     summary="Get a specific item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Item details"),
     *     @OA\Response(response="404", description="Item not found")
     * )
     */
    public function show($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        return response()->json($item);
    }

    /**
     * @OA\Put(
     *     path="/items/{id}",
     *     summary="Update an item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item_name", type="string"),
     *             @OA\Property(property="item_cid", type="integer"),
     *             @OA\Property(property="item_sn", type="string"),
     *             @OA\Property(property="item_spec", type="string"),
     *             @OA\Property(property="item_save", type="integer"),
     *             @OA\Property(property="item_price", type="number"),
     *             @OA\Property(property="suggested_retail_price", type="integer"),
     *             @OA\Property(property="item_note", type="string"),
     *             @OA\Property(property="item_open", type="integer"),
     *             @OA\Property(property="item_sort", type="integer"),
     *             @OA\Property(property="item_mstock", type="integer"),
     *             @OA\Property(property="item_type", type="string"),
     *             @OA\Property(property="item_years", type="string"),
     *             @OA\Property(property="item_holdmonth", type="integer"),
     *             @OA\Property(property="item_outvyear", type="string"),
     *             @OA\Property(property="item_predict", type="integer"),
     *             @OA\Property(property="item_insertdate", type="string", format="date-time"),
     *             @OA\Property(property="item_editdate", type="string", format="date-time"),
     *             @OA\Property(property="item_barcode", type="string"),
     *             @OA\Property(property="item_inbox", type="integer"),
     *             @OA\Property(property="ppt_id", type="integer"),
     *             @OA\Property(property="item_vcode", type="string"),
     *             @OA\Property(property="item_size", type="string"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Item updated successfully"),
     *     @OA\Response(response="404", description="Item not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $validatedData = $request->validate([
            'item_name' => 'required|string|max:200',
            'item_cid' => 'required|boolean',
            'item_sn' => 'required|string|max:200',
            'item_spec' => 'required|string|max:200',
            'item_save' => 'required|integer',
            'item_price' => 'required|numeric',
            'suggested_retail_price' => 'required|integer',
            'item_note' => 'required|string',
            'item_open' => 'required|boolean',
            'item_sort' => 'required|integer',
            'item_mstock' => 'required|boolean',
            'item_type' => 'required|string',
            'item_years' => 'nullable|string|max:10',
            'item_holdmonth' => 'required|boolean',
            'item_outvyear' => 'required|string',
            'item_predict' => 'required|boolean',
            'item_insertdate' => 'required|date',
            'item_editdate' => 'required|date',
            'item_barcode' => 'required|string|max:20',
            'item_inbox' => 'required|integer',
            'ppt_id' => 'required|integer',
            'item_vcode' => 'nullable|string|max:8',
            'item_size' => 'nullable|string|max:20',
        ]);

        $item->update($validatedData);
        return response()->json($item);
    }

    /**
     * @OA\Delete(
     *     path="/items/{id}",
     *     summary="Delete an item",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Item deleted successfully"),
     *     @OA\Response(response="404", description="Item not found")
     * )
     */
    public function destroy($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();
        return response()->json(null, 204);
    }
}
