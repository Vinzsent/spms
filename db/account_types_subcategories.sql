-- Create account_types table
CREATE TABLE IF NOT EXISTS `account_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create account_subcategories table
CREATE TABLE IF NOT EXISTS `account_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `account_subcategories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `account_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some sample account types
INSERT IGNORE INTO `account_types` (`name`) VALUES 
('ICT Equipment and Devices'),
('Office Equipment'),
('School Building Improvements'),
('Furniture and Fixtures'),
('Subscription, License, and Software Services');

-- Create account_sub_subcategories table (children of subcategories)
CREATE TABLE IF NOT EXISTS `account_sub_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subcategory_id` (`subcategory_id`),
  CONSTRAINT `account_sub_subcategories_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `account_subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
