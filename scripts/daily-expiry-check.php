<?php
/**
 * 每日短效期商品檢查排程
 * 此腳本應該透過 cron job 每日執行
 * 
 * 使用方式:
 * 在 crontab 中添加以下行 (每天早上9點執行):
 * 0 9 * * * /usr/bin/php /path/to/this/script/daily-expiry-check.php
 */

// 設定時區
date_default_timezone_set('Asia/Taipei');

// 載入必要檔案
require_once __DIR__ . '/../config/database.php';

/**
 * 記錄日誌
 */
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/daily-expiry-check.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * 載入短效期設定
 */
function loadExpirySettings() {
    $settingsFile = __DIR__ . '/../config/expiry-settings.json';
    
    if (!file_exists($settingsFile)) {
        return [
            'warningDays' => 180,
            'criticalDays' => 90,
            'enableAutoNotification' => true,
            'notificationTime' => '09:00'
        ];
    }
    
    $content = file_get_contents($settingsFile);
    $settings = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage("設定檔案格式錯誤，使用預設設定", 'WARNING');
        return [
            'warningDays' => 180,
            'criticalDays' => 90,
            'enableAutoNotification' => true,
            'notificationTime' => '09:00'
        ];
    }
    
    return $settings;
}

/**
 * 載入Teams設定
 */
function loadTeamsSettings() {
    $settingsFile = __DIR__ . '/../config/teams-settings.json';
    
    if (!file_exists($settingsFile)) {
        return null;
    }
    
    $content = file_get_contents($settingsFile);
    $settings = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($settings['webhookUrl'])) {
        return null;
    }
    
    return $settings;
}

/**
 * 取得即將到期的商品
 */
function getExpiringItems($pdo, $warningDays, $criticalDays) {
    $sql = "
        SELECT 
            i.item_id,
            i.item_name,
            i.item_sn,
            i.item_spec,
            qr.item_batch,
            qr.item_expireday,
            qr.location,
            COUNT(*) as quantity,
            DATEDIFF(qr.item_expireday, CURDATE()) as days_until_expiry,
            CASE 
                WHEN DATEDIFF(qr.item_expireday, CURDATE()) <= 0 THEN 'expired'
                WHEN DATEDIFF(qr.item_expireday, CURDATE()) <= ? THEN 'critical'
                WHEN DATEDIFF(qr.item_expireday, CURDATE()) <= ? THEN 'warning'
                ELSE 'normal'
            END as status
        FROM qr_code qr
        INNER JOIN items i ON qr.item_sn = i.item_sn
        WHERE qr.item_expireday IS NOT NULL
        AND DATEDIFF(qr.item_expireday, CURDATE()) <= ?
        GROUP BY i.item_sn, qr.item_batch, qr.location
        ORDER BY days_until_expiry ASC, i.item_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$criticalDays, $warningDays, $warningDays]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 發送Teams通知
 */
function sendTeamsNotification($webhookUrl, $expiringItems, $settings) {
    // 分類商品
    $expired = array_filter($expiringItems, function($item) {
        return $item['status'] === 'expired';
    });
    
    $critical = array_filter($expiringItems, function($item) {
        return $item['status'] === 'critical';
    });
    
    $warning = array_filter($expiringItems, function($item) {
        return $item['status'] === 'warning';
    });
    
    // 構建通知內容
    $sections = [];
    
    if (!empty($expired)) {
        $expiredList = [];
        foreach (array_slice($expired, 0, 10) as $item) { // 限制顯示前10項
            $expiredList[] = $item['item_name'] . ' (' . $item['item_sn'] . ') - 批號:' . $item['item_batch'] . ' - 已過期' . abs($item['days_until_expiry']) . '天';
        }
        
        $sections[] = [
            'activityTitle' => '🚨 已過期商品 (' . count($expired) . '項)',
            'activitySubtitle' => '請立即處理以下已過期商品',
            'text' => implode("\n", $expiredList) . (count($expired) > 10 ? "\n...還有" . (count($expired) - 10) . "項" : "")
        ];
    }
    
    if (!empty($critical)) {
        $criticalList = [];
        foreach (array_slice($critical, 0, 10) as $item) {
            $criticalList[] = $item['item_name'] . ' (' . $item['item_sn'] . ') - 批號:' . $item['item_batch'] . ' - ' . $item['days_until_expiry'] . '天後到期';
        }
        
        $sections[] = [
            'activityTitle' => '⚠️ 緊急到期商品 (' . count($critical) . '項)',
            'activitySubtitle' => '以下商品將在' . $settings['criticalDays'] . '天內到期',
            'text' => implode("\n", $criticalList) . (count($critical) > 10 ? "\n...還有" . (count($critical) - 10) . "項" : "")
        ];
    }
    
    if (!empty($warning)) {
        $warningList = [];
        foreach (array_slice($warning, 0, 5) as $item) { // 警告級別顯示較少
            $warningList[] = $item['item_name'] . ' (' . $item['item_sn'] . ') - 批號:' . $item['item_batch'] . ' - ' . $item['days_until_expiry'] . '天後到期';
        }
        
        $sections[] = [
            'activityTitle' => '📅 即將到期商品 (' . count($warning) . '項)',
            'activitySubtitle' => '以下商品將在' . $settings['warningDays'] . '天內到期',
            'text' => implode("\n", $warningList) . (count($warning) > 5 ? "\n...還有" . (count($warning) - 5) . "項" : "")
        ];
    }
    
    // 構建完整訊息
    $messageData = [
        '@type' => 'MessageCard',
        '@context' => 'https://schema.org/extensions',
        'summary' => '庫存到期日報告',
        'themeColor' => !empty($expired) ? 'FF0000' : (!empty($critical) ? 'FFA500' : '00AA00'),
        'title' => '📊 每日庫存到期監控報告',
        'text' => '系統檢測到以下商品需要注意到期日：',
        'sections' => $sections
    ];
    
    // 添加統計資訊
    $messageData['sections'][] = [
        'activityTitle' => '📈 統計摘要',
        'facts' => [
            [
                'name' => '已過期',
                'value' => count($expired) . ' 項'
            ],
            [
                'name' => '緊急到期',
                'value' => count($critical) . ' 項'
            ],
            [
                'name' => '即將到期',
                'value' => count($warning) . ' 項'
            ],
            [
                'name' => '檢查時間',
                'value' => date('Y-m-d H:i:s')
            ]
        ]
    ];
    
    // 發送請求
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
        throw new Exception('發送Teams通知失敗: ' . ($error['message'] ?? '未知錯誤'));
    }
    
    return true;
}

/**
 * 主要執行函數
 */
function main() {
    logMessage("開始執行每日短效期檢查");
    
    try {
        // 載入設定
        $expirySettings = loadExpirySettings();
        
        // 檢查是否啟用自動通知
        if (!$expirySettings['enableAutoNotification']) {
            logMessage("自動通知已停用，跳過執行");
            return;
        }
        
        // 檢查執行時間
        $currentTime = date('H:i');
        $targetTime = $expirySettings['notificationTime'];
        $timeDiff = abs(strtotime($currentTime) - strtotime($targetTime));
        
        // 允許5分鐘誤差
        if ($timeDiff > 300) {
            logMessage("非執行時間 (當前: {$currentTime}, 設定: {$targetTime})，跳過執行");
            return;
        }
        
        // 連接資料庫
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 取得即將到期的商品
        $expiringItems = getExpiringItems(
            $pdo, 
            $expirySettings['warningDays'], 
            $expirySettings['criticalDays']
        );
        
        if (empty($expiringItems)) {
            logMessage("沒有發現即將到期的商品");
            return;
        }
        
        logMessage("發現 " . count($expiringItems) . " 項即將到期的商品");
        
        // 載入Teams設定並發送通知
        $teamsSettings = loadTeamsSettings();
        if ($teamsSettings && $teamsSettings['enableNotifications']) {
            sendTeamsNotification(
                $teamsSettings['webhookUrl'], 
                $expiringItems, 
                $expirySettings
            );
            logMessage("Teams通知已發送");
        } else {
            logMessage("Teams通知未設定或已停用，跳過發送");
        }
        
        logMessage("每日短效期檢查完成");
        
    } catch (Exception $e) {
        logMessage("執行錯誤: " . $e->getMessage(), 'ERROR');
        
        // 可以在這裡添加錯誤通知邏輯
        // 例如發送錯誤報告到Teams或寄送電子郵件
    }
}

// 執行主函數
main();
?>