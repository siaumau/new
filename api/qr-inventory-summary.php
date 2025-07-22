<?php
/**
 * QR碼庫存匯總API
 * 基於qr_code資料表統計商品的批號、數量和位置資訊
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 只允許 GET 請求
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只允許 GET 請求']);
    exit;
}

// 資料庫連接設定
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 獲取查詢參數
    $itemSn = $_GET['item_sn'] ?? null;
    $location = $_GET['location'] ?? null;
    $batch = $_GET['batch'] ?? null;
    
    // 基本查詢SQL
    $sql = "
        SELECT 
            qr.item_sn,
            qr.item_batch,
            qr.item_expireday,
            qr.location,
            qr.scanned_at,
            qr.created_at,
            COUNT(*) as scan_count,
            SUM(CASE WHEN qr.quantity IS NOT NULL THEN qr.quantity ELSE 1 END) as total_quantity,
            MIN(qr.item_expireday) as earliest_expiry,
            MAX(qr.scanned_at) as last_scan
        FROM qr_code qr
        WHERE 1=1
    ";
    
    $params = [];
    
    // 添加篩選條件
    if ($itemSn) {
        $sql .= " AND qr.item_sn = :item_sn";
        $params['item_sn'] = $itemSn;
    }
    
    if ($location) {
        $sql .= " AND qr.location = :location";
        $params['location'] = $location;
    }
    
    if ($batch) {
        $sql .= " AND qr.item_batch = :batch";
        $params['batch'] = $batch;
    }
    
    // 群組和排序
    $sql .= "
        GROUP BY qr.item_sn, qr.item_batch, qr.location
        ORDER BY qr.item_sn, qr.item_batch, qr.location
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 處理結果數據
    $processedResults = [];
    foreach ($results as $row) {
        // 計算到期天數
        $expiryDate = $row['earliest_expiry'] ? new DateTime($row['earliest_expiry']) : null;
        $today = new DateTime();
        $daysUntilExpiry = $expiryDate ? (int)$today->diff($expiryDate)->format('%r%a') : null;
        
        $processedResults[] = [
            'item_sn' => $row['item_sn'],
            'item_batch' => $row['item_batch'],
            'item_expireday' => $row['earliest_expiry'],
            'location' => $row['location'],
            'quantity' => (int)$row['total_quantity'],
            'scan_count' => (int)$row['scan_count'],
            'days_until_expiry' => $daysUntilExpiry,
            'last_scan' => $row['last_scan'],
            'created_at' => $row['created_at']
        ];
    }
    
    // 如果需要統計摘要
    if (!$itemSn && !$location && !$batch) {
        // 獲取統計摘要
        $summarySQL = "
            SELECT 
                COUNT(DISTINCT qr.item_sn) as total_items,
                COUNT(DISTINCT CONCAT(qr.item_sn, '-', qr.item_batch)) as total_batches,
                COUNT(DISTINCT qr.location) as total_locations,
                SUM(CASE WHEN qr.quantity IS NOT NULL THEN qr.quantity ELSE 1 END) as total_quantity,
                COUNT(DISTINCT CASE 
                    WHEN qr.item_expireday IS NOT NULL 
                    AND DATEDIFF(qr.item_expireday, CURDATE()) <= 180 
                    AND DATEDIFF(qr.item_expireday, CURDATE()) > 0 
                    THEN CONCAT(qr.item_sn, '-', qr.item_batch) 
                END) as expiring_batches,
                COUNT(DISTINCT CASE 
                    WHEN qr.item_expireday IS NOT NULL 
                    AND DATEDIFF(qr.item_expireday, CURDATE()) <= 0 
                    THEN CONCAT(qr.item_sn, '-', qr.item_batch) 
                END) as expired_batches
            FROM qr_code qr
        ";
        
        $summaryStmt = $pdo->prepare($summarySQL);
        $summaryStmt->execute();
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
        
        $response = [
            'success' => true,
            'data' => $processedResults,
            'summary' => [
                'total_items' => (int)$summary['total_items'],
                'total_batches' => (int)$summary['total_batches'], 
                'total_locations' => (int)$summary['total_locations'],
                'total_quantity' => (int)$summary['total_quantity'],
                'expiring_batches' => (int)$summary['expiring_batches'],
                'expired_batches' => (int)$summary['expired_batches']
            ],
            'total' => count($processedResults)
        ];
    } else {
        $response = [
            'success' => true,
            'data' => $processedResults,
            'total' => count($processedResults)
        ];
    }
    
    http_response_code(200);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => '資料庫連接錯誤: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => '系統錯誤: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>