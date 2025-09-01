-- Add status column to supplier_transaction table
ALTER TABLE `supplier_transaction` 
ADD COLUMN `status` varchar(50) DEFAULT 'Pending' AFTER `amount`;

-- Update existing records to have a default status
UPDATE `supplier_transaction` SET `status` = 'Pending' WHERE `status` IS NULL; 