-- Update supplier_transaction table to use department/unit instead of user
-- First, drop the existing foreign key constraint if it exists
ALTER TABLE `supplier_transaction` 
DROP FOREIGN KEY IF EXISTS `supplier_transaction_ibfk_2`;

-- Drop the issued_to_user column
ALTER TABLE `supplier_transaction` 
DROP COLUMN IF EXISTS `issued_to_user`;

-- Add the new issued_to_department column
ALTER TABLE `supplier_transaction` 
ADD COLUMN `issued_to_department` varchar(100) NULL AFTER `status`; 