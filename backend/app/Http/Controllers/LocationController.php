<?php

namespace App\Http\Controllers;

use App\Models\Location;
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
     *     @OA\Response(response="404", description="Location not found")
     * )
     */
    public function destroy($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        $location->delete();
        return response()->json(null, 204);
    }
}
