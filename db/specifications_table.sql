-- Specifications table for supplier transactions
-- This table stores additional specifications for items in transactions

CREATE TABLE `transaction_specifications` (
  `spec_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `warranty_info` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`spec_id`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `transaction_specifications_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `supplier_transaction` (`transaction_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 