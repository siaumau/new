<?php
/**
 * Locations API
 * 管理位置資料的 CRUD 操作
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 載入資料庫配置
require_once __DIR__ . '/../config/database.php';

/**
 * 記錄日誌
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/locations.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

try {
    // 連接資料庫
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 解析路徑來處理不同的端點
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // 移除查詢參數並分割路徑
    $pathParts = explode('/', trim($path, '/'));
    
    // 處理路由
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequest($pdo, $pathParts);
            break;
            
        case 'POST':
            handlePostRequest($pdo, $pathParts);
            break;
            
        case 'PUT':
            handlePutRequest($pdo, $pathParts);
            break;
            
        case 'DELETE':
            handleDeleteRequest($pdo, $pathParts);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '不支援的請求方法']);
            break;
    }
    
} catch (PDOException $e) {
    logMessage("資料庫連接錯誤: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
} catch (Exception $e) {
    logMessage("處理請求時發生錯誤: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '服務器錯誤']);
}

/**
 * 處理 GET 請求
 */
function handleGetRequest($pdo, $pathParts) {
    // 檢查是否為特定位置的查詢
    if (count($pathParts) >= 4 && $pathParts[3] !== '') {
        $locationId = $pathParts[3];
        getLocationById($pdo, $locationId);
    } else {
        getAllLocations($pdo);
    }
}

/**
 * 處理 POST 請求
 */
function handlePostRequest($pdo, $pathParts) {
    // 檢查是否為 CSV 匯入
    if (count($pathParts) >= 4 && $pathParts[3] === 'import-csv') {
        handleCSVImport($pdo);
    } else {
        createLocation($pdo);
    }
}

/**
 * 處理 PUT 請求
 */
function handlePutRequest($pdo, $pathParts) {
    if (count($pathParts) >= 4 && $pathParts[3] !== '') {
        $locationId = $pathParts[3];
        updateLocation($pdo, $locationId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少位置ID']);
    }
}

/**
 * 處理 DELETE 請求
 */
function handleDeleteRequest($pdo, $pathParts) {
    if (count($pathParts) >= 4 && $pathParts[3] !== '') {
        $locationId = $pathParts[3];
        deleteLocation($pdo, $locationId);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少位置ID']);
    }
}

/**
 * 獲取所有位置
 */
function getAllLocations($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                location_code,
                location_name,
                building_code,
                storage_type_code,
                sub_area_code,
                floor_number,
                created_at,
                updated_at
            FROM locations 
            ORDER BY building_code, storage_type_code, sub_area_code
        ");
        
        $stmt->execute();
        $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $locations,
            'count' => count($locations)
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        logMessage("獲取位置列表失敗: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '獲取位置列表失敗']);
    }
}

/**
 * 根據ID獲取特定位置
 */
function getLocationById($pdo, $locationId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                location_code,
                location_name,
                building_code,
                storage_type_code,
                sub_area_code,
                floor_number,
                created_at,
                updated_at
            FROM locations 
            WHERE id = ?
        ");
        
        $stmt->execute([$locationId]);
        $location = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($location) {
            echo json_encode([
                'success' => true,
                'data' => $location
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '位置不存在']);
        }
        
    } catch (Exception $e) {
        logMessage("獲取位置詳情失敗: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '獲取位置詳情失敗']);
    }
}

/**
 * 創建新位置
 */
function createLocation($pdo) {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的 JSON 數據']);
            return;
        }
        
        // 驗證必要欄位
        $requiredFields = ['location_code', 'location_name', 'building_code', 'storage_type_code', 'sub_area_code'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "缺少必要欄位: {$field}"]);
                return;
            }
        }
        
        // 檢查是否已存在相同的組合
        if (checkDuplicateLocation($pdo, $data['building_code'], $data['storage_type_code'], $data['sub_area_code'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '此位置組合已存在']);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO locations (
                location_code, location_name, building_code, 
                storage_type_code, sub_area_code, floor_number, 
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['location_code'],
            $data['location_name'],
            $data['building_code'],
            $data['storage_type_code'],
            $data['sub_area_code'],
            $data['floor_number'] ?? null
        ]);
        
        $locationId = $pdo->lastInsertId();
        
        logMessage("新位置已創建: ID={$locationId}, Code={$data['location_code']}");
        
        echo json_encode([
            'success' => true,
            'message' => '位置已成功創建',
            'data' => ['id' => $locationId]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        logMessage("創建位置失敗: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '創建位置失敗']);
    }
}

/**
 * 更新位置
 */
function updateLocation($pdo, $locationId) {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的 JSON 數據']);
            return;
        }
        
        // 檢查位置是否存在
        $checkStmt = $pdo->prepare("SELECT id FROM locations WHERE id = ?");
        $checkStmt->execute([$locationId]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '位置不存在']);
            return;
        }
        
        // 構建更新查詢
        $updateFields = [];
        $values = [];
        
        $allowedFields = ['location_code', 'location_name', 'building_code', 'storage_type_code', 'sub_area_code', 'floor_number'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '沒有要更新的欄位']);
            return;
        }
        
        $updateFields[] = "updated_at = NOW()";
        $values[] = $locationId;
        
        $sql = "UPDATE locations SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        logMessage("位置已更新: ID={$locationId}");
        
        echo json_encode([
            'success' => true,
            'message' => '位置已成功更新'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        logMessage("更新位置失敗: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '更新位置失敗']);
    }
}

/**
 * 刪除位置
 */
function deleteLocation($pdo, $locationId) {
    try {
        // 檢查位置是否存在
        $checkStmt = $pdo->prepare("SELECT location_code FROM locations WHERE id = ?");
        $checkStmt->execute([$locationId]);
        $location = $checkStmt->fetch();
        
        if (!$location) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '位置不存在']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
        $stmt->execute([$locationId]);
        
        logMessage("位置已刪除: ID={$locationId}, Code={$location['location_code']}");
        
        echo json_encode([
            'success' => true,
            'message' => '位置已成功刪除'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        logMessage("刪除位置失敗: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '刪除位置失敗']);
    }
}

/**
 * 處理CSV匯入
 */
function handleCSVImport($pdo) {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '無效的 JSON 數據']);
            return;
        }
        
        if (empty($data['csv_data'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '缺少 CSV 數據']);
            return;
        }
        
        $csvLines = explode("\n", trim($data['csv_data']));
        $errors = [];
        $validRows = [];
        $duplicateCheck = [];
        
        // 跳過標題行
        if (count($csvLines) > 1) {
            array_shift($csvLines);
        }
        
        foreach ($csvLines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $columns = str_getcsv($line);
            $rowNumber = $lineNumber + 2; // +2 因為跳過標題行且從1開始計數
            
            // 檢查欄位數量
            if (count($columns) < 5) {
                $errors[] = "第{$rowNumber}行: 欄位數量不足";
                continue;
            }
            
            $building_code = trim($columns[2] ?? '');
            $storage_type_code = trim($columns[3] ?? '');
            $sub_area_code = trim($columns[4] ?? '');
            
            // 檢查必要欄位是否為空
            if (empty($building_code) || empty($storage_type_code) || empty($sub_area_code)) {
                $errors[] = "第{$rowNumber}行: building_code, storage_type_code, sub_area_code 不能為空";
                continue;
            }
            
            // 檢查組合是否重複
            $combination = "{$building_code}|{$storage_type_code}|{$sub_area_code}";
            if (isset($duplicateCheck[$combination])) {
                $errors[] = "第{$rowNumber}行: 與第{$duplicateCheck[$combination]}行有重複的位置組合";
                continue;
            }
            $duplicateCheck[$combination] = $rowNumber;
            
            // 檢查資料庫中是否已存在
            if (checkDuplicateLocation($pdo, $building_code, $storage_type_code, $sub_area_code)) {
                $errors[] = "第{$rowNumber}行: 資料庫中已存在此位置組合";
                continue;
            }
            
            $validRows[] = [
                'location_code' => trim($columns[0] ?? ''),
                'location_name' => trim($columns[1] ?? ''),
                'building_code' => $building_code,
                'storage_type_code' => $storage_type_code,
                'sub_area_code' => $sub_area_code,
                'floor_number' => trim($columns[5] ?? '') ?: null
            ];
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '匯入失敗',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (empty($validRows)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '沒有有效的數據行'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // 開始事務
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO locations (
                    location_code, location_name, building_code, 
                    storage_type_code, sub_area_code, floor_number, 
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $importedCount = 0;
            foreach ($validRows as $row) {
                $stmt->execute([
                    $row['location_code'],
                    $row['location_name'],
                    $row['building_code'],
                    $row['storage_type_code'],
                    $row['sub_area_code'],
                    $row['floor_number']
                ]);
                $importedCount++;
            }
            
            $pdo->commit();
            
            logMessage("CSV匯入成功: 已匯入 {$importedCount} 筆位置資料");
            
            echo json_encode([
                'success' => true,
                'message' => "成功匯入 {$importedCount} 筆位置資料",
                'imported_count' => $importedCount
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        logMessage("CSV匯入失敗: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'CSV匯入失敗: ' . $e->getMessage()]);
    }
}

/**
 * 檢查位置組合是否重複
 */
function checkDuplicateLocation($pdo, $building_code, $storage_type_code, $sub_area_code, $excludeId = null) {
    $sql = "SELECT id FROM locations WHERE building_code = ? AND storage_type_code = ? AND sub_area_code = ?";
    $params = [$building_code, $storage_type_code, $sub_area_code];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetch() !== false;
}
?>