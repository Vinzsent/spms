-- Aircon Maintenance Records Table
-- Stores multiple maintenance entries for each aircon unit

CREATE TABLE IF NOT EXISTS `aircon_maintenance` (
  `maintenance_id` int(11) NOT NULL AUTO_INCREMENT,
  `aircon_id` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `technician` varchar(255) DEFAULT NULL,
  `next_scheduled_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`maintenance_id`),
  KEY `aircon_id` (`aircon_id`),
  CONSTRAINT `fk_aircon_maintenance` FOREIGN KEY (`aircon_id`) REFERENCES `aircons` (`aircon_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
