<?php
/**
 * Teams通知設定API
 * 管理Teams PowerAutomate通知的設定參數
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 設定檔案路徑
$settingsFile = __DIR__ . '/../config/teams-settings.json';
$settingsDir = dirname($settingsFile);

// 確保設定目錄存在
if (!is_dir($settingsDir)) {
    mkdir($settingsDir, 0755, true);
}

/**
 * 記錄日誌
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/teams.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * 獲取預設設定
 */
function getDefaultSettings() {
    return [
        'webhookUrl' => '',
        'channelName' => '',
        'enableNotifications' => true,
        'notificationTypes' => [
            'expiring' => true,
            'expired' => true,
            'lowStock' => true
        ],
        'lastUpdated' => date('Y-m-d H:i:s'),
        'updatedBy' => 'system'
    ];
}

/**
 * 讀取設定
 */
function loadSettings() {
    global $settingsFile;
    
    if (!file_exists($settingsFile)) {
        $defaultSettings = getDefaultSettings();
        saveSettings($defaultSettings);
        return $defaultSettings;
    }
    
    $content = file_get_contents($settingsFile);
    $settings = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage("Teams設定檔案格式錯誤，使用預設設定", 'WARNING');
        return getDefaultSettings();
    }
    
    // 合併預設設定以確保所有欄位存在
    $defaultSettings = getDefaultSettings();
    $settings = array_merge($defaultSettings, $settings);
    
    // 確保 notificationTypes 是完整的
    if (isset($settings['notificationTypes'])) {
        $settings['notificationTypes'] = array_merge(
            $defaultSettings['notificationTypes'], 
            $settings['notificationTypes']
        );
    }
    
    return $settings;
}

/**
 * 儲存設定
 */
function saveSettings($settings) {
    global $settingsFile;
    
    $settings['lastUpdated'] = date('Y-m-d H:i:s');
    
    // 敏感資訊處理 - 只記錄 webhook URL 的前後部分
    $logSettings = $settings;
    if (!empty($logSettings['webhookUrl'])) {
        $url = $logSettings['webhookUrl'];
        $logSettings['webhookUrl'] = substr($url, 0, 30) . '...' . substr($url, -10);
    }
    
    $result = file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('無法儲存Teams設定檔案');
    }
    
    logMessage("Teams通知設定已更新: " . json_encode($logSettings, JSON_UNESCAPED_UNICODE));
    return true;
}

/**
 * 驗證設定
 */
function validateSettings($settings) {
    $errors = [];
    
    // 檢查 webhook URL
    if (isset($settings['webhookUrl']) && !empty($settings['webhookUrl'])) {
        if (!filter_var($settings['webhookUrl'], FILTER_VALIDATE_URL)) {
            $errors[] = "Webhook URL 格式不正確";
        } else {
            // 檢查是否為 Azure Logic Apps URL
            $url = strtolower($settings['webhookUrl']);
            if (strpos($url, 'logic.azure.com') === false && strpos($url, 'prod-') === false) {
                $errors[] = "請使用有效的 PowerAutomate Webhook URL";
            }
        }
    }
    
    // 檢查通知類型
    if (isset($settings['notificationTypes'])) {
        if (!is_array($settings['notificationTypes'])) {
            $errors[] = "通知類型設定必須是陣列格式";
        } else {
            $validTypes = ['expiring', 'expired', 'lowStock'];
            foreach ($settings['notificationTypes'] as $type => $enabled) {
                if (!in_array($type, $validTypes)) {
                    $errors[] = "無效的通知類型: {$type}";
                }
                if (!is_bool($enabled)) {
                    $errors[] = "通知類型 {$type} 的值必須是布林值";
                }
            }
        }
    }
    
    // 檢查頻道名稱
    if (isset($settings['channelName']) && !empty($settings['channelName'])) {
        if (strlen($settings['channelName']) > 100) {
            $errors[] = "頻道名稱不能超過100個字符";
        }
    }
    
    return $errors;
}

/**
 * 測試 webhook 連接
 */
function testWebhook($webhookUrl) {
    if (empty($webhookUrl)) {
        throw new Exception('Webhook URL 不能為空');
    }
    
    $testData = [
        'title' => '🧪 庫存管理系統測試通知',
        'message' => '這是一則測試訊息，用於驗證Teams通知設定是否正確。',
        'type' => 'test',
        'timestamp' => date('Y-m-d H:i:s'),
        'items' => [
            [
                'name' => '測試商品',
                'status' => '測試中',
                'details' => '如果您看到此訊息，表示Teams通知設定已正確完成！'
            ]
        ]
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'User-Agent: Inventory-Management-System/1.0'
            ],
            'content' => json_encode($testData),
            'timeout' => 10
        ]
    ]);
    
    $result = @file_get_contents($webhookUrl, false, $context);
    
    if ($result === false) {
        $error = error_get_last();
        throw new Exception('Webhook 測試失敗: ' . ($error['message'] ?? '未知錯誤'));
    }
    
    // 檢查 HTTP 狀態碼
    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0];
        preg_match('/HTTP\/\d+\.\d+ (\d+)/', $statusLine, $matches);
        $statusCode = isset($matches[1]) ? (int)$matches[1] : 0;
        
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new Exception("Webhook 回應狀態碼: {$statusCode}");
        }
    }
    
    logMessage("Webhook 測試成功: " . substr($webhookUrl, 0, 30) . '...');
    return true;
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // 讀取設定
            $settings = loadSettings();
            
            // 在回傳時隱藏敏感資訊
            $safeSettings = $settings;
            if (!empty($safeSettings['webhookUrl'])) {
                $url = $safeSettings['webhookUrl'];
                $safeSettings['webhookUrl'] = substr($url, 0, 30) . '...' . substr($url, -10);
                $safeSettings['_hasWebhook'] = true;
            } else {
                $safeSettings['_hasWebhook'] = false;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $safeSettings
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'POST':
            // 更新設定
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '無效的 JSON 數據']);
                exit;
            }
            
            // 檢查是否為測試請求
            if (isset($data['_test']) && $data['_test'] === true) {
                // 測試 webhook
                try {
                    $currentSettings = loadSettings();
                    $webhookUrl = $data['webhookUrl'] ?? $currentSettings['webhookUrl'];
                    
                    testWebhook($webhookUrl);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Webhook 測試成功'
                    ], JSON_UNESCAPED_UNICODE);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Webhook 測試失敗: ' . $e->getMessage()
                    ], JSON_UNESCAPED_UNICODE);
                }
                exit;
            }
            
            // 驗證設定
            $errors = validateSettings($data);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => '設定驗證失敗',
                    'errors' => $errors
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // 載入當前設定並合併
            $currentSettings = loadSettings();
            $newSettings = array_merge($currentSettings, $data);
            
            // 儲存設定
            saveSettings($newSettings);
            
            echo json_encode([
                'success' => true,
                'message' => 'Teams通知設定已成功更新',
                'data' => array_merge($newSettings, ['webhookUrl' => '***已設定***'])
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '不支援的請求方法']);
            break;
    }
    
} catch (Exception $e) {
    logMessage("Teams設定處理錯誤: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => '服務器錯誤: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>