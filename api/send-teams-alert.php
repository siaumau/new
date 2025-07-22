<?php
/**
 * Teams通知發送API
 * 發送庫存警告到Teams頻道
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 只允許 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只允許 POST 請求']);
    exit;
}

/**
 * 記錄日誌
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/teams-alerts.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * 載入Teams設定
 */
function loadTeamsSettings() {
    $settingsFile = __DIR__ . '/../config/teams-settings.json';
    
    if (!file_exists($settingsFile)) {
        throw new Exception('Teams設定檔案不存在');
    }
    
    $content = file_get_contents($settingsFile);
    $settings = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Teams設定檔案格式錯誤');
    }
    
    if (empty($settings['webhookUrl'])) {
        throw new Exception('Teams Webhook URL 未設定');
    }
    
    if (!$settings['enableNotifications']) {
        throw new Exception('Teams通知功能已停用');
    }
    
    return $settings;
}

/**
 * 格式化通知訊息
 */
function formatNotificationMessage($data, $settings) {
    $alertType = $data['alert_type'] ?? 'general';
    $item = $data['item'] ?? null;
    $message = $data['message'] ?? '';
    
    // 根據警告類型選擇圖示和顏色
    $typeConfig = [
        'expiry_warning' => [
            'icon' => '⚠️',
            'color' => 'warning',
            'title' => '商品即將到期提醒'
        ],
        'expired' => [
            'icon' => '🚨',
            'color' => 'attention',
            'title' => '商品已過期警告'
        ],
        'low_stock' => [
            'icon' => '📦',
            'color' => 'good',
            'title' => '低庫存提醒'
        ],
        'test' => [
            'icon' => '🧪',
            'color' => 'good',
            'title' => '測試通知'
        ],
        'general' => [
            'icon' => 'ℹ️',
            'color' => 'good',
            'title' => '庫存通知'
        ]
    ];
    
    $config = $typeConfig[$alertType] ?? $typeConfig['general'];
    
    // 構建訊息內容
    $notificationData = [
        '@type' => 'MessageCard',
        '@context' => 'https://schema.org/extensions',
        'summary' => $config['title'],
        'themeColor' => $config['color'] === 'attention' ? 'FF0000' : ($config['color'] === 'warning' ? 'FFA500' : '00FF00'),
        'title' => $config['icon'] . ' ' . $config['title'],
        'text' => $message,
        'sections' => []
    ];
    
    // 添加商品詳細資訊
    if ($item) {
        $facts = [
            [
                'name' => '商品名稱',
                'value' => $item['item_name'] ?? 'N/A'
            ],
            [
                'name' => '商品序號',
                'value' => $item['item_sn'] ?? 'N/A'
            ]
        ];
        
        if (isset($item['item_spec'])) {
            $facts[] = [
                'name' => '規格',
                'value' => $item['item_spec']
            ];
        }
        
        if (isset($item['batchSummary']) && is_array($item['batchSummary'])) {
            $batchInfo = [];
            foreach ($item['batchSummary'] as $batch) {
                $batchInfo[] = $batch['batch'] . ' (' . $batch['quantity'] . '個)';
            }
            $facts[] = [
                'name' => '批號統計',
                'value' => implode(', ', $batchInfo)
            ];
        }
        
        if (isset($item['nearestExpiry'])) {
            $expiry = $item['nearestExpiry'];
            $facts[] = [
                'name' => '最近到期',
                'value' => date('Y-m-d', strtotime($expiry['expiry_date'])) . ' (' . $expiry['days_until_expiry'] . '天)'
            ];
        }
        
        if (isset($item['locations']) && is_array($item['locations'])) {
            $locationInfo = [];
            foreach ($item['locations'] as $location) {
                $locationInfo[] = $location['location'] . ' (' . $location['quantity'] . '個)';
            }
            $facts[] = [
                'name' => '存放位置',
                'value' => implode(', ', $locationInfo)
            ];
        }
        
        $notificationData['sections'][] = [
            'activityTitle' => '商品詳細資訊',
            'facts' => $facts
        ];
    }
    
    // 添加時間戳記
    $notificationData['sections'][] = [
        'activityTitle' => '通知資訊',
        'facts' => [
            [
                'name' => '發送時間',
                'value' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '來源系統',
                'value' => '庫存管理系統'
            ],
            [
                'name' => '頻道',
                'value' => $settings['channelName'] ?: '預設頻道'
            ]
        ]
    ];
    
    return $notificationData;
}

/**
 * 發送到Teams
 */
function sendToTeams($webhookUrl, $messageData) {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'User-Agent: Inventory-Management-System/1.0'
            ],
            'content' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
            'timeout' => 15
        ]
    ]);
    
    $result = @file_get_contents($webhookUrl, false, $context);
    
    if ($result === false) {
        $error = error_get_last();
        throw new Exception('發送到Teams失敗: ' . ($error['message'] ?? '未知錯誤'));
    }
    
    // 檢查 HTTP 狀態碼
    $statusCode = 200;
    if (isset($http_response_header)) {
        $statusLine = $http_response_header[0];
        preg_match('/HTTP\/\d+\.\d+ (\d+)/', $statusLine, $matches);
        $statusCode = isset($matches[1]) ? (int)$matches[1] : 200;
    }
    
    if ($statusCode < 200 || $statusCode >= 300) {
        throw new Exception("Teams API 回應錯誤，狀態碼: {$statusCode}");
    }
    
    return $result;
}

try {
    // 讀取輸入數據
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '無效的 JSON 數據']);
        exit;
    }
    
    // 驗證必要欄位
    if (empty($data['alert_type'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少 alert_type 欄位']);
        exit;
    }
    
    if (empty($data['message'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少 message 欄位']);
        exit;
    }
    
    // 載入Teams設定
    $settings = loadTeamsSettings();
    
    // 檢查通知類型是否啟用
    $alertType = $data['alert_type'];
    $typeMapping = [
        'expiry_warning' => 'expiring',
        'expired' => 'expired',
        'low_stock' => 'lowStock'
    ];
    
    if (isset($typeMapping[$alertType])) {
        $settingKey = $typeMapping[$alertType];
        if (!isset($settings['notificationTypes'][$settingKey]) || !$settings['notificationTypes'][$settingKey]) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => "通知類型 '{$alertType}' 已停用"
            ]);
            exit;
        }
    }
    
    // 格式化通知訊息
    $messageData = formatNotificationMessage($data, $settings);
    
    // 發送到Teams
    $result = sendToTeams($settings['webhookUrl'], $messageData);
    
    // 記錄成功日誌
    $logData = [
        'alert_type' => $alertType,
        'item_sn' => $data['item']['item_sn'] ?? 'N/A',
        'item_name' => $data['item']['item_name'] ?? 'N/A',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    logMessage("Teams通知發送成功: " . json_encode($logData, JSON_UNESCAPED_UNICODE));
    
    // 記錄到資料庫（可選）
    // saveNotificationLog($data, 'success');
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Teams通知已成功發送',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    logMessage("Teams通知發送失敗: " . $e->getMessage(), 'ERROR');
    
    // 記錄到資料庫（可選）
    // saveNotificationLog($data ?? [], 'failed', $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '發送Teams通知時發生錯誤: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>