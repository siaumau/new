<?php
/**
 * 資料庫連接配置
 * 用於直接 PHP API 文件
 */

// 資料庫配置
$dbHost = '127.0.0.1';
$dbName = 'inventory_system';
$dbUser = 'root';
$dbPass = '';

// 可選：從環境變量讀取配置
if (isset($_ENV['DB_HOST'])) {
    $dbHost = $_ENV['DB_HOST'];
}
if (isset($_ENV['DB_DATABASE'])) {
    $dbName = $_ENV['DB_DATABASE'];
}
if (isset($_ENV['DB_USERNAME'])) {
    $dbUser = $_ENV['DB_USERNAME'];
}
if (isset($_ENV['DB_PASSWORD'])) {
    $dbPass = $_ENV['DB_PASSWORD'];
}
?>