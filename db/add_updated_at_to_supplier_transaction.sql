-- Add updated_at column to supplier_transaction table if it doesn't exist
ALTER TABLE `supplier_transaction` 
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add updated_at column to supplier_transaction table (alternative syntax for older MySQL versions)
-- ALTER TABLE `supplier_transaction` 
-- ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- If the above doesn't work, you can manually add the column:
-- ALTER TABLE `supplier_transaction` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
