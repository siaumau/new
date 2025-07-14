<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Inventory System API Documentation",
 *      description="API documentation for the Inventory System",
 *      @OA\Contact(
 *          email="your-email@example.com"
 *      )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/controller-test",
     *     summary="Test Controller API endpoint",
     *     @OA\Response(response="200", description="Successful operation")
     * )
     */
    public function controllerTest()
    {
        return response()->json(['message' => 'Controller Test successful']);
    }
}
