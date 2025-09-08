-- Create stock_logs table for tracking inventory movements
CREATE TABLE IF NOT EXISTS `stock_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(11) NOT NULL,
  `movement_type` enum('IN','OUT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `notes` text,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `inventory_id` (`inventory_id`),
  KEY `date_created` (`date_created`),
  CONSTRAINT `stock_logs_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data to demonstrate the functionality
INSERT INTO `stock_logs` (`inventory_id`, `movement_type`, `quantity`, `previous_stock`, `new_stock`, `notes`, `date_created`) VALUES
(1, 'IN', 100, 0, 100, 'Initial stock', '2025-01-01 08:00:00'),
(1, 'OUT', 10, 100, 90, 'Issued to department', '2025-01-02 10:30:00'),
(2, 'IN', 50, 0, 50, 'New delivery', '2025-01-03 14:15:00'),
(2, 'IN', 25, 50, 75, 'Additional stock', '2025-01-04 09:45:00'),
(3, 'IN', 200, 0, 200, 'Bulk purchase', '2025-01-05 11:20:00'),
(3, 'OUT', 30, 200, 170, 'Office supplies request', '2025-01-06 16:00:00');
