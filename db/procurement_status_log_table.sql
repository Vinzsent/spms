-- Create table for logging procurement status changes
CREATE TABLE IF NOT EXISTS `procurement_status_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `procurement_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` datetime NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `procurement_id` (`procurement_id`),
  KEY `changed_by` (`changed_by`),
  KEY `changed_at` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraint if the users table exists
-- ALTER TABLE `procurement_status_log` ADD CONSTRAINT `fk_procurement_status_log_user` 
-- FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE CASCADE;

-- Add foreign key constraint if the supplier_transaction table exists
-- ALTER TABLE `procurement_status_log` ADD CONSTRAINT `fk_procurement_status_log_procurement` 
-- FOREIGN KEY (`procurement_id`) REFERENCES `supplier_transaction`(`id`) ON DELETE CASCADE;
