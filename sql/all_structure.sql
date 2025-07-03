-- --------------------------------------------------------
-- 主機:                           192.168.2.200
-- 伺服器版本:                        10.4.34-MariaDB-log - MariaDB Server
-- 伺服器作業系統:                      Linux
-- HeidiSQL 版本:                  12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- 傾印 inventory_system 的資料庫結構
CREATE DATABASE IF NOT EXISTS `inventory_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `inventory_system`;

-- 傾印  資料表 inventory_system.item 結構
CREATE TABLE IF NOT EXISTS `item` (
  `item_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(200) NOT NULL COMMENT '品項名稱',
  `item_cid` tinyint(1) NOT NULL DEFAULT 1 COMMENT '產品類型',
  `item_sn` varchar(200) NOT NULL COMMENT '型號',
  `item_spec` varchar(200) NOT NULL COMMENT '規格',
  `item_eng` text DEFAULT NULL COMMENT '產品英文',
  `item_save` bigint(20) NOT NULL COMMENT '安全庫存量',
  `item_save2` bigint(20) DEFAULT NULL COMMENT '銷售最低庫存量',
  `item_price` decimal(10,0) NOT NULL COMMENT '成本',
  `suggested_retail_price` int(10) NOT NULL DEFAULT 0 COMMENT '建議售價',
  `item_note` text NOT NULL,
  `item_open` tinyint(1) NOT NULL,
  `item_sort` bigint(20) NOT NULL,
  `item_mstock` tinyint(1) NOT NULL COMMENT '希望可撐月數',
  `item_type` text NOT NULL COMMENT '看管類別',
  `item_years` varchar(10) DEFAULT NULL COMMENT '有效年數',
  `item_holdmonth` tinyint(1) NOT NULL COMMENT '希望可撐月數',
  `item_outvyear` text NOT NULL COMMENT '目前出貨效期',
  `item_predict` tinyint(1) NOT NULL COMMENT '是否要預估',
  `item_insertdate` datetime NOT NULL,
  `item_editdate` datetime NOT NULL,
  `item_barcode` varchar(20) NOT NULL COMMENT '產品條碼',
  `item_inbox` smallint(2) NOT NULL COMMENT '每箱產品數量',
  `ppt_id` bigint(20) NOT NULL COMMENT '膚質檢測用',
  `item_vcode` varchar(8) DEFAULT NULL COMMENT '即期品安全驗證碼',
  `item_size` varchar(20) DEFAULT '' COMMENT '尺寸size',
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1085 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.locations 結構
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_code` varchar(20) NOT NULL COMMENT '位置完整代碼',
  `location_name` varchar(100) NOT NULL COMMENT '位置名稱',
  `building_code` varchar(10) NOT NULL COMMENT '所在地代碼',
  `floor_number` varchar(10) NOT NULL COMMENT '樓層',
  `floor_area_code` varchar(10) DEFAULT NULL COMMENT '樓層區碼',
  `storage_type_code` varchar(20) NOT NULL COMMENT '存放類別代碼',
  `sub_area_code` varchar(10) DEFAULT NULL COMMENT '存放小區/層代碼',
  `position_code` varchar(20) NOT NULL COMMENT '存放代碼',
  `capacity` int(11) DEFAULT 0 COMMENT '容量',
  `current_stock` int(11) DEFAULT 0 COMMENT '目前庫存',
  `qr_code_data` text DEFAULT NULL COMMENT 'QR Code資料',
  `notes` text DEFAULT NULL COMMENT '備註',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '是否啟用',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_location_code` (`location_code`),
  KEY `idx_building_floor` (`building_code`,`floor_number`),
  KEY `idx_storage_type` (`storage_type_code`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='位置資訊表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.location_code_settings 結構
CREATE TABLE IF NOT EXISTS `location_code_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_type` enum('building','storage_type','area','sub_area') NOT NULL COMMENT '代碼類型',
  `code_key` varchar(10) NOT NULL COMMENT '代碼鍵值',
  `code_value` varchar(20) NOT NULL COMMENT '代碼對應值',
  `description` varchar(100) DEFAULT NULL COMMENT '說明',
  `max_length` int(11) DEFAULT 2 COMMENT '最大長度',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '是否啟用',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code_type_key` (`code_type`,`code_key`)
) ENGINE=InnoDB AUTO_INCREMENT=417 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='位置代碼設定表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.location_product_bindings 結構
CREATE TABLE IF NOT EXISTS `location_product_bindings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL COMMENT '位置ID',
  `location_code` varchar(20) NOT NULL COMMENT '位置代碼',
  `product_code` varchar(50) NOT NULL COMMENT '商品條碼',
  `product_name` varchar(200) DEFAULT NULL COMMENT '商品名稱',
  `quantity` int(11) DEFAULT 1 COMMENT '數量',
  `binding_date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '綁定時間',
  `binding_user` varchar(100) DEFAULT 'System User' COMMENT '綁定操作員',
  `notes` text DEFAULT NULL COMMENT '備註',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '是否有效',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_location_product` (`location_id`,`product_code`),
  KEY `idx_location_id` (`location_id`),
  KEY `idx_location_code` (`location_code`),
  KEY `idx_product_code` (`product_code`),
  KEY `idx_binding_date` (`binding_date`),
  CONSTRAINT `location_product_bindings_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='位置商品綁定表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.operation_logs 結構
CREATE TABLE IF NOT EXISTS `operation_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL COMMENT '用戶ID',
  `user_name` varchar(100) DEFAULT NULL COMMENT '用戶名稱',
  `operation_type` varchar(50) NOT NULL COMMENT '操作類型',
  `operation_module` varchar(50) NOT NULL COMMENT '操作模組',
  `operation_description` text NOT NULL COMMENT '操作描述',
  `target_id` varchar(50) DEFAULT NULL COMMENT '操作目標ID',
  `target_type` varchar(50) DEFAULT NULL COMMENT '操作目標類型',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` text DEFAULT NULL COMMENT '用戶代理',
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '請求資料' CHECK (json_valid(`request_data`)),
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '回應資料' CHECK (json_valid(`response_data`)),
  `execution_time_ms` int(11) DEFAULT NULL COMMENT '執行時間(毫秒)',
  `status` enum('success','failure','warning') DEFAULT 'success' COMMENT '操作狀態',
  `error_message` text DEFAULT NULL COMMENT '錯誤訊息',
  `description` char(255) DEFAULT NULL,
  `target_object` char(255) DEFAULT NULL,
  `user_ip` char(255) DEFAULT NULL,
  `details` char(255) DEFAULT NULL,
  `session_id` char(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_operation_type` (`operation_type`),
  KEY `idx_operation_module` (`operation_module`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_target` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='操作記錄表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.permissions 結構
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL COMMENT '模組名稱',
  `name` varchar(50) NOT NULL COMMENT '權限名稱',
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '可執行動作' CHECK (json_valid(`actions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='權限表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.posin 結構
CREATE TABLE IF NOT EXISTS `posin` (
  `posin_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `_users_id` bigint(20) NOT NULL,
  `_users_id2` bigint(20) DEFAULT NULL,
  `posin_sn` text NOT NULL,
  `posin_user` text NOT NULL,
  `posin_user2` text DEFAULT NULL,
  `posin_dt` datetime NOT NULL,
  `posin_log` datetime DEFAULT NULL,
  `posin_note` text NOT NULL,
  PRIMARY KEY (`posin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=918 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.posinitem 結構
CREATE TABLE IF NOT EXISTS `posinitem` (
  `posinitem_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `posin_id` bigint(20) NOT NULL,
  `itemtype` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_name` text NOT NULL,
  `item_sn` text NOT NULL,
  `item_spec` text NOT NULL,
  `item_batch` varchar(20) NOT NULL COMMENT '批號',
  `item_count` bigint(20) NOT NULL,
  `item_price` decimal(10,0) NOT NULL,
  `item_expireday` date DEFAULT NULL COMMENT '到期日',
  `item_validyear` varchar(10) DEFAULT NULL COMMENT '有效年數',
  PRIMARY KEY (`posinitem_id`),
  KEY `item_id` (`item_id`),
  KEY `posin_id` (`posin_id`),
  KEY `idx_item_batch` (`item_batch`),
  KEY `idx_item_sn` (`item_sn`(1024)),
  KEY `idx_item_expireday` (`item_expireday`)
) ENGINE=InnoDB AUTO_INCREMENT=10656 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.qr_codes 結構
CREATE TABLE IF NOT EXISTS `qr_codes` (
  `qr_id` int(11) NOT NULL AUTO_INCREMENT,
  `posin_id` int(11) DEFAULT NULL,
  `posinitem_id` int(11) DEFAULT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_batch` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `box_number` int(11) NOT NULL DEFAULT 1 COMMENT '箱號（流水號）',
  `location_id` int(11) DEFAULT NULL COMMENT '位置ID',
  `floor_level` varchar(10) DEFAULT NULL COMMENT '樓層（僅層架類型位置使用）',
  `qr_content` text NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `zip_file_name` varchar(255) DEFAULT NULL COMMENT 'ZIP檔案名稱',
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` varchar(100) DEFAULT NULL,
  `status` enum('generated','printed','used') DEFAULT 'generated',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`qr_id`),
  KEY `idx_posin_id` (`posin_id`),
  KEY `idx_posinitem_id` (`posinitem_id`),
  KEY `idx_generated_at` (`generated_at`),
  KEY `idx_box_number` (`box_number`),
  KEY `idx_zip_file_name` (`zip_file_name`),
  KEY `idx_item_code` (`item_code`) USING BTREE,
  KEY `idx_location_id` (`location_id`),
  KEY `idx_floor_level` (`floor_level`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.roles 結構
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名稱',
  `description` text DEFAULT NULL COMMENT '角色描述',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.role_permissions 結構
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色權限關聯表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.system_settings 結構
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL COMMENT '設定鍵值',
  `setting_value` text NOT NULL COMMENT '設定值',
  `setting_type` varchar(20) DEFAULT 'string' COMMENT '設定類型 (string, int, json)',
  `description` varchar(255) DEFAULT NULL COMMENT '設定描述',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='系統設定表';

-- 取消選取資料匯出。

-- 傾印  資料表 inventory_system.users 結構
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '使用者姓名',
  `email` varchar(255) NOT NULL COMMENT '電子郵件',
  `password` varchar(255) DEFAULT NULL COMMENT '使用者密碼（加密）',
  `role_id` int(11) DEFAULT NULL COMMENT '角色ID',
  `status` enum('active','inactive') DEFAULT 'active' COMMENT '狀態',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='使用者表';

-- 取消選取資料匯出。

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
