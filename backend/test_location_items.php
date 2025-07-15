<?php

// Bootstrap Laravel
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\LocationController;

echo "Testing location 9 items endpoint...\n";

$controller = new LocationController();
$response = $controller->getLocationItems(9);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Content: " . $response->getContent() . "\n";