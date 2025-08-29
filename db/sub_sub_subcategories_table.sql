-- Create account_sub_sub_subcategories table (children of account_sub_subcategories)
CREATE TABLE IF NOT EXISTS `account_sub_sub_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sub_subcategory_id` (`sub_subcategory_id`),
  CONSTRAINT `account_sub_sub_subcategories_ibfk_1` FOREIGN KEY (`sub_subcategory_id`) REFERENCES `account_sub_subcategories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
