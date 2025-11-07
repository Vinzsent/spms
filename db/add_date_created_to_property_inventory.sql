-- Add date_created column to property_inventory table if it doesn't exist
-- This script is safe to run multiple times

-- Check if column exists and add it if it doesn't
SET @dbname = DATABASE();
SET @tablename = "property_inventory";
SET @columnname = "date_created";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 'Column date_created already exists in property_inventory table.' AS result;",
  CONCAT("ALTER TABLE `", @tablename, "` ADD COLUMN `", @columnname, "` datetime DEFAULT current_timestamp() AFTER `created_by`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

