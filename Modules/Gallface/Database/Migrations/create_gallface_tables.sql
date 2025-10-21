
-- Create location_api_credentials table if it doesn't exist
CREATE TABLE IF NOT EXISTS `location_api_credentials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(11) NOT NULL,
  `business_location_id` int(11) NOT NULL,
  `mall_code` varchar(50) NOT NULL COMMENT 'hcm, odel, etc.',
  `api_url` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `client_id` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `stall_no` varchar(255) DEFAULT NULL,
  `pos_id` varchar(255) DEFAULT NULL,
  `sync_type` enum('auto','manual') DEFAULT 'manual',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_api_credentials_business_id_index` (`business_id`),
  KEY `location_api_credentials_business_location_id_index` (`business_location_id`),
  KEY `location_api_credentials_mall_code_index` (`mall_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to transactions table if they don't exist
SET @dbname = DATABASE();
SET @tablename = 'transactions';
SET @columnname1 = 'hcm_synced_at';
SET @columnname2 = 'gift_voucher_amount';
SET @columnname3 = 'hcm_loyalty_amount';
SET @columnname4 = 'is_gift_voucher';

-- Check and add hcm_synced_at column
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname1)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname1, " timestamp NULL DEFAULT NULL AFTER updated_at")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add gift_voucher_amount column
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname2)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname2, " decimal(22,4) NOT NULL DEFAULT 0 AFTER final_total")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add hcm_loyalty_amount column
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname3)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname3, " decimal(22,4) NOT NULL DEFAULT 0 AFTER gift_voucher_amount")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add is_gift_voucher column
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname4)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname4, " tinyint(1) NOT NULL DEFAULT 0 AFTER type")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verify tables exist (informational only, won't cause errors)
SELECT 'location_api_credentials table status:' as message, 
  IF(COUNT(*) > 0, 'EXISTS', 'NOT FOUND') as status 
FROM INFORMATION_SCHEMA.TABLES 
WHERE table_schema = DATABASE() 
  AND table_name = 'location_api_credentials';

SELECT 'transactions table columns status:' as message;
SELECT column_name, data_type 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE table_schema = DATABASE() 
  AND table_name = 'transactions' 
  AND column_name IN ('hcm_synced_at', 'gift_voucher_amount', 'hcm_loyalty_amount', 'is_gift_voucher');
