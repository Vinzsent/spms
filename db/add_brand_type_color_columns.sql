-- Add brand, type, and color columns to supplier_transaction table
ALTER TABLE `supplier_transaction` 
ADD COLUMN `brand` varchar(100) NULL AFTER `item_description`,
ADD COLUMN `type` varchar(100) NULL AFTER `brand`,
ADD COLUMN `color` varchar(50) NULL AFTER `type`; 