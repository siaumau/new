<?php
/**
 * 短效期設定API
 * 管理商品到期提醒的設定參數
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
$settingsFile = __DIR__ . '/../config/expiry-settings.json';
$settingsDir = dirname($settingsFile);

// 確保設定目錄存在
if (!is_dir($settingsDir)) {
    mkdir($settingsDir, 0755, true);
}

/**
 * 記錄日誌
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/expiry.log';
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
        'warningDays' => 180,     // 6個月
        'criticalDays' => 90,     // 3個月
        'enableAutoNotification' => true,
        'notificationTime' => '09:00',
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
        logMessage("設定檔案格式錯誤，使用預設設定", 'WARNING');
        return getDefaultSettings();
    }
    
    // 合併預設設定以確保所有欄位存在
    return array_merge(getDefaultSettings(), $settings);
}

/**
 * 儲存設定
 */
function saveSettings($settings) {
    global $settingsFile;
    
    $settings['lastUpdated'] = date('Y-m-d H:i:s');
    
    $result = file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('無法儲存設定檔案');
    }
    
    logMessage("短效期設定已更新");
    return true;
}

/**
 * 驗證設定
 */
function validateSettings($settings) {
    $errors = [];
    
    // 檢查必要欄位
    $requiredFields = ['warningDays', 'criticalDays', 'enableAutoNotification'];
    foreach ($requiredFields as $field) {
        if (!isset($settings[$field])) {
            $errors[] = "缺少必要欄位: {$field}";
        }
    }
    
    // 檢查數值範圍
    if (isset($settings['warningDays'])) {
        if (!is_numeric($settings['warningDays']) || $settings['warningDays'] <= 0 || $settings['warningDays'] > 365) {
            $errors[] = "警告天數必須介於 1-365 之間";
        }
    }
    
    if (isset($settings['criticalDays'])) {
        if (!is_numeric($settings['criticalDays']) || $settings['criticalDays'] <= 0) {
            $errors[] = "嚴重警告天數必須大於 0";
        }
    }
    
    // 檢查邏輯關係
    if (isset($settings['warningDays']) && isset($settings['criticalDays'])) {
        if ($settings['criticalDays'] >= $settings['warningDays']) {
            $errors[] = "嚴重警告天數必須小於警告天數";
        }
    }
    
    // 檢查時間格式
    if (isset($settings['notificationTime'])) {
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $settings['notificationTime'])) {
            $errors[] = "通知時間格式不正確 (應為 HH:MM)";
        }
    }
    
    return $errors;
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // 讀取設定
            $settings = loadSettings();
            
            echo json_encode([
                'success' => true,
                'data' => $settings
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
            
            // 記錄操作
            logMessage("短效期設定已更新: " . json_encode($data, JSON_UNESCAPED_UNICODE));
            
            echo json_encode([
                'success' => true,
                'message' => '設定已成功更新',
                'data' => $newSettings
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => '不支援的請求方法']);
            break;
    }
    
} catch (Exception $e) {
    logMessage("設定處理錯誤: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => '服務器錯誤: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>