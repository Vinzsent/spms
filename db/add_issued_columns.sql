-- Add issued_to_user and issued_date columns to supplier_transaction table
ALTER TABLE `supplier_transaction` 
ADD COLUMN `issued_to_user` int(11) NULL AFTER `status`,
ADD COLUMN `issued_date` datetime NULL AFTER `issued_to_user`;

-- Add foreign key constraint for issued_to_user
ALTER TABLE `supplier_transaction` 
ADD CONSTRAINT `supplier_transaction_ibfk_2` 
FOREIGN KEY (`issued_to_user`) REFERENCES `user` (`id`) ON DELETE SET NULL; 