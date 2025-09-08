-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 03:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `supplier_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_subcategories`
--

CREATE TABLE `account_subcategories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_subcategories`
--

INSERT INTO `account_subcategories` (`id`, `parent_id`, `name`, `created_at`) VALUES
(1, 1, 'Property and Equipment', '2025-08-19 02:56:43'),
(3, 2, 'Supplies and Materials', '2025-08-19 03:47:30'),
(7, 1, 'Intangible Assets', '2025-08-20 02:08:27'),
(8, 2, 'Janitorial & Cleaning Expenses (Main/BED Campus)', '2025-08-20 02:11:28'),
(9, 2, 'Testing Materials (Main BED Campus)', '2025-08-20 02:12:04'),
(11, 2, 'Library Expenses (Main/ BED Campus)', '2025-08-20 02:13:08'),
(12, 2, 'Laboratory Expenses', '2025-08-20 02:13:34'),
(13, 2, 'Medical Expenses (Main/ BED Campus)', '2025-08-20 02:14:16'),
(14, 2, 'Repairs and Maintenance (Main/ BED Campus)', '2025-08-20 02:14:34');

-- --------------------------------------------------------

--
-- Table structure for table `account_sub_subcategories`
--

CREATE TABLE `account_sub_subcategories` (
  `id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_sub_subcategories`
--

INSERT INTO `account_sub_subcategories` (`id`, `subcategory_id`, `name`, `created_at`, `updated_at`) VALUES
(5, 1, 'Schoo Building - Main Campus', '2025-08-20 01:26:28', '2025-08-20 02:08:49'),
(6, 1, 'School Building - BED Campus', '2025-08-20 02:09:05', '2025-08-20 02:09:05'),
(7, 1, 'Furniture and Fixtures', '2025-08-20 02:09:28', '2025-08-20 02:21:36'),
(8, 1, 'Laboratory Equipment', '2025-08-20 02:09:44', '2025-08-20 02:09:44'),
(9, 1, 'Office Equipment', '2025-08-20 02:09:53', '2025-08-20 02:09:53'),
(10, 1, 'Computers', '2025-08-20 02:09:59', '2025-08-20 02:09:59'),
(11, 1, 'Vehicle', '2025-08-20 02:10:03', '2025-08-20 02:10:03'),
(12, 7, 'Software', '2025-08-20 02:10:18', '2025-08-20 02:10:18'),
(13, 7, 'Patents and License', '2025-08-20 02:10:26', '2025-08-20 02:10:26'),
(14, 3, 'Office Supplies (Main/ BED Campus)', '2025-08-20 02:32:23', '2025-08-20 02:32:23'),
(15, 3, 'Electrical Supplies (Main/ BED Campus)', '2025-08-20 02:32:43', '2025-08-20 02:32:43'),
(16, 3, 'School Supplies (Main/ BED Campus)', '2025-08-20 02:33:00', '2025-08-20 02:33:00'),
(17, 3, 'Textbooks (Main/ BED Campus)', '2025-08-20 02:33:22', '2025-08-20 02:33:22'),
(18, 12, 'Laboratory Equipment - CJE', '2025-08-20 02:33:58', '2025-08-20 02:33:58'),
(19, 12, 'Laboratory Equipment - HME', '2025-08-20 02:34:04', '2025-08-20 02:34:04'),
(20, 12, 'Laboratory Equipment - Science (TED)', '2025-08-20 02:34:14', '2025-08-20 02:34:14'),
(21, 12, 'Laboratory Equipment - Science (BED)', '2025-08-20 02:34:25', '2025-08-20 02:34:25'),
(22, 12, 'Laboratory Equipment - Physics (BED)', '2025-08-20 02:34:40', '2025-08-20 02:34:40'),
(23, 12, 'Laboratory Equipment - TLE', '2025-08-20 02:34:48', '2025-08-20 02:34:48');

-- --------------------------------------------------------

--
-- Table structure for table `account_sub_sub_subcategories`
--

CREATE TABLE `account_sub_sub_subcategories` (
  `id` int(11) NOT NULL,
  `sub_subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_sub_sub_subcategories`
--

INSERT INTO `account_sub_sub_subcategories` (`id`, `sub_subcategory_id`, `name`, `created_at`, `updated_at`) VALUES
(2, 7, 'Furniture and Fixtures - Main Campus', '2025-08-20 02:21:55', '2025-08-20 02:21:55'),
(3, 7, 'Funiture and Fixtures - BED Campus', '2025-08-20 02:22:07', '2025-08-20 02:22:07'),
(4, 10, 'Computers - Main Campus', '2025-08-20 02:28:51', '2025-08-20 02:28:51'),
(5, 10, 'Computers - BED Campus', '2025-08-20 02:29:00', '2025-08-20 02:29:00'),
(6, 8, 'Laboratory Equipment - CJE', '2025-08-20 02:29:41', '2025-08-20 02:29:41'),
(7, 8, 'Laboratory Equipment - HME', '2025-08-20 02:29:54', '2025-08-20 02:29:54'),
(8, 8, 'Laboratory Equipment - Science (TED)', '2025-08-20 02:30:15', '2025-08-20 02:30:15'),
(9, 8, 'Laboratory Equipment - Science (BED)', '2025-08-20 02:30:23', '2025-08-20 02:30:23'),
(10, 8, 'Laboratory Equipment - Physics (BED)', '2025-08-20 02:30:35', '2025-08-20 02:30:35'),
(11, 8, 'Laboratory Equipment - TLE', '2025-08-20 02:30:46', '2025-08-20 02:30:46'),
(12, 9, 'Ofiice Equipment - Main Campus', '2025-08-20 02:31:13', '2025-08-20 02:31:13'),
(13, 9, 'Office Equipment - BED Campus', '2025-08-20 02:31:29', '2025-08-20 02:31:29'),
(15, 11, 'Tesla', '2025-08-20 07:19:33', '2025-08-20 07:19:33');

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

CREATE TABLE `account_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_types`
--

INSERT INTO `account_types` (`id`, `name`) VALUES
(1, 'Assets'),
(2, 'Expenses');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `supplier_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Discontinued') DEFAULT 'Active',
  `received_notes` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `category`, `description`, `current_stock`, `unit`, `unit_cost`, `reorder_level`, `supplier_id`, `location`, `status`, `received_notes`, `created_by`, `date_created`, `last_updated_by`, `date_updated`) VALUES
(25, 'Ball Pen', 'Office Supplies', 'Titus ball pen', 100, 'pc', 8.00, 0, 27, 'Storage 1', 'Active', NULL, 14, '2025-08-27 08:38:26', NULL, '2025-08-27 08:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `related_id`, `related_type`, `is_read`, `created_at`, `updated_at`) VALUES
(122, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: Ball Pen na malakasan', 36, 'supply_request', 1, '2025-08-27 00:54:33', '2025-08-27 03:16:19'),
(123, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: Ball Pen na malakasan', 36, 'supply_request', 0, '2025-08-27 00:54:33', '2025-08-27 00:54:33'),
(124, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: Ball Pen na malakasan', 36, 'supply_request', 1, '2025-08-27 00:54:33', '2025-08-27 07:14:29'),
(125, 12, 'request', '📝 Request Status Updated', 'Your supply request has been Noted by Erwin Acedillo. The approval process is progressing.', 36, 'supply_request', 0, '2025-08-27 03:16:38', '2025-08-27 03:16:38'),
(126, 12, 'issued', '📦 Item Issued!', 'Your requested item has been issued by Juliet Geremino: Ball Pen na malakasan. You can now collect your items from the supply office.', 36, 'supplier_transaction', 0, '2025-08-27 07:10:10', '2025-08-27 07:10:10'),
(127, 13, 'request', 'New Property Request', 'A new Property request has been submitted by Faculty: Lamborgini sana hehe', 37, 'supply_request', 0, '2025-08-27 07:12:46', '2025-08-27 07:12:46'),
(128, 15, 'request', 'New Property Request', 'A new Property request has been submitted by Faculty: Lamborgini sana hehe', 37, 'supply_request', 0, '2025-08-27 07:12:46', '2025-08-27 07:12:46'),
(129, 17, 'request', 'New Property Request', 'A new Property request has been submitted by Faculty: Lamborgini sana hehe', 37, 'supply_request', 1, '2025-08-27 07:12:46', '2025-08-27 08:29:30'),
(130, 12, 'request', '📝 Request Status Updated', 'Your supply request has been Noted by Erwin Acedillo. The approval process is progressing.', 37, 'supply_request', 0, '2025-08-27 07:13:52', '2025-08-27 07:13:52'),
(131, 12, 'approved', '🎉 Request Approved!', 'Great news! Your supply request has been approved by Mary Grace Baytola. Your items will be processed for issuance soon.', 37, 'supply_request', 0, '2025-08-27 07:14:52', '2025-08-27 07:14:52');

-- --------------------------------------------------------

--
-- Table structure for table `procurement`
--

CREATE TABLE `procurement` (
  `procurement_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `invoice_path` varchar(500) DEFAULT NULL,
  `delivery_receipt_path` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_notes` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Received','Cancelled') DEFAULT 'Pending',
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `received_by` int(11) DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement`
--

INSERT INTO `procurement` (`procurement_id`, `item_name`, `supplier_id`, `quantity`, `unit`, `unit_price`, `total_amount`, `invoice_path`, `delivery_receipt_path`, `notes`, `received_notes`, `status`, `created_by`, `date_created`, `received_by`, `date_received`, `last_updated_by`, `date_updated`) VALUES
(4, 'Lamborgini', 27, 1, 'pc', 15000.00, 15000.00, 'uploads/procurement/invoice_1756279931_68aeb47b169ff.jpg', 'uploads/procurement/receipt_1756279931_68aeb47b16ca7.png', 'So mahal man diay ni', '', 'Pending', 15, '2025-08-27 15:32:11', 15, '2025-08-27 00:00:00', 15, '2025-08-28 08:49:15');

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `log_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`log_id`, `inventory_id`, `movement_type`, `quantity`, `previous_stock`, `new_stock`, `notes`, `created_by`, `date_created`) VALUES
(17, 25, 'IN', 100, 0, 100, 'Initial stock entry', 14, '2025-08-27 08:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `fax_number` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `tax_identification_number` varchar(100) DEFAULT NULL,
  `date_registered` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` varchar(255) DEFAULT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `supplier_name`, `contact_person`, `contact_number`, `email_address`, `fax_number`, `website`, `address`, `city`, `province`, `zip_code`, `country`, `business_type`, `category`, `payment_terms`, `tax_identification_number`, `date_registered`, `status`, `created_by`, `date_created`, `last_updated_by`, `date_updated`, `notes`) VALUES
(27, 'All Supplies', 'Bernadette', '092923435', 'berna@gmail.com', '123-456-789', 'allsupplier.com', 'Toril Davao City', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'IT Equipment Supplier', 'ICT Equipment and Devices', 'Cash', '123-456', '2025-08-27', 'Active', 14, '2025-08-27 02:35:01', NULL, NULL, 'They have all supplies needed to school');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transaction`
--

CREATE TABLE `supplier_transaction` (
  `transaction_id` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `sales_type` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supply_request`
--

CREATE TABLE `supply_request` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_requested` varchar(255) NOT NULL,
  `date_needed` varchar(255) NOT NULL,
  `department_unit` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `sales_type` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `request_description` varchar(255) NOT NULL,
  `unit_cost` varchar(255) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `quantity_requested` varchar(255) NOT NULL,
  `quality_issued` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `request_type` varchar(255) DEFAULT NULL,
  `noted_by` varchar(100) DEFAULT NULL,
  `noted_date` datetime DEFAULT NULL,
  `checked_by` varchar(100) DEFAULT NULL,
  `checked_date` datetime DEFAULT NULL,
  `verified_by` varchar(100) DEFAULT NULL,
  `verified_date` datetime DEFAULT NULL,
  `issued_by` varchar(100) DEFAULT NULL,
  `issued_date` datetime DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_request`
--

INSERT INTO `supply_request` (`request_id`, `user_id`, `date_requested`, `date_needed`, `department_unit`, `purpose`, `sales_type`, `category`, `request_description`, `unit_cost`, `total_cost`, `unit`, `quantity_requested`, `quality_issued`, `amount`, `request_type`, `noted_by`, `noted_date`, `checked_by`, `checked_date`, `verified_by`, `verified_date`, `issued_by`, `issued_date`, `approved_by`, `approved_date`, `remarks`) VALUES
(36, 12, '2025-08-27', '2025-08-27', 'Faculty', 'To write grades', 'Cash', 'Supplies and Materials', 'Ball Pen na malakasan', '8', 32.00, 'pc', '4', '', 32.00, 'consumables', 'Erwin Acedillo', '2025-08-27 11:16:38', NULL, NULL, NULL, NULL, 'Juliet Geremino', '2025-08-27 15:10:10', NULL, NULL, NULL),
(37, 12, '2025-08-27', '2025-08-28', 'Faculty', 'To have a service to home', 'Cash', 'Vehicle', 'Lamborgini sana hehe', '', 0.00, 'pc', '1', '', 0.00, 'property', 'Erwin Acedillo', '2025-08-27 15:13:52', NULL, NULL, NULL, NULL, NULL, NULL, 'Mary Grace Baytola', '2025-08-27 15:14:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_list`
--

CREATE TABLE `transaction_list` (
  `transaction_id` int(11) NOT NULL,
  `date_received` date DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `sales_type` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_specifications`
--

CREATE TABLE `transaction_specifications` (
  `spec_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `size` varchar(100) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `warranty_info` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `title` varchar(10) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `academic_title` varchar(50) DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `title`, `first_name`, `middle_name`, `last_name`, `suffix`, `academic_title`, `user_type`, `email`, `password`) VALUES
(4, 'Sir', 'Erwin', 'Pogi', 'Acedillo', '', 'MIT', 'MIS Head', 'erwinacedillo@gmail.com', '$2y$10$sqVTg7YdLiuS8Jx2bMY.OuEIkudSO/.ToKuU35RkVpmDAY7RWQCr.'),
(8, 'Admin', 'Vincent', 'Pogi', 'Crame', 'NONE', 'MIS PROGRAMMER', 'Admin', 'vincentcrame7@gmail.com', '$2y$10$QhG8ni2hkHR9F8FZ6xIbouic51sVQgAV/Li3ibJVIHz6ElL01HiPi'),
(9, 'Sir', 'Staff', 'Staff', 'Staff', 'staff', 'Staff', 'Staff', 'staff@gmail.com', '$2y$10$Ax96ZYijBBqpRMLdwnoOduMZk8GJNe/OEW4FLVQ2177xBT0VFDr4u'),
(11, 'Dr.', 'Ruben', '', 'Dela Cruz', 'N/A', 'Ph.D', 'School President', 'ruben@gmail.com', '$2y$10$VYwZKAJTXrGQsZ9f5rVdMuprkCHYM5yDBROLG4b0D78KEfTyOSNsm'),
(12, 'Sir', 'June Carl', '', 'Echavia', 'N/A', 'Teacher', 'Faculty', 'junecarl@gmail.com', '$2y$10$eCxmouJR3YSa1HPGz7NAM.u5UXUZuMOD/sn9sSY4.M9BNff8r1hY.'),
(13, 'Sir', 'Erwin', '', 'Acedillo', '', '', 'Immediate Head', 'erwin@gmail.com', '$2y$10$SbF0o6H2YlbpZTONszE25.8uXRLonPewEzshwFKf3K0L.KeRZBPny'),
(14, 'Maam', 'Juliet', '', 'Geremino', '', '', 'Supply In-charge', 'juliet@gmail.com', '$2y$10$xcXtjDe//VUPb0YNFX3nn.qaallQC.sS3Qrgrf04takFkqSz.g/ha'),
(15, 'Maam', 'Marilou', '', 'Suarez', '', '', 'Purchasing Officer', 'marilou@gmail.com', '$2y$10$e3OrYs2.Z6tqN4m0tjalfuP7.2xzyH61sE8iyLr.36xzz3NCWjgJO'),
(16, 'Dr.', 'Cinna', '', 'Rose', 'N/A', 'M.D', 'VP for Finance & Administration', 'cinna@gmail.com', '$2y$10$l4seTrgPgUaBLoz9rXgx7.cjQjwaQOi8s3JaB3hdt/1xZfKxBNgqO'),
(17, 'Maam', 'Mary Grace', '', 'Baytola', '', 'Maam', 'Property Custodian', 'marygrace@gmail.com', '$2y$10$sYnybRrh2aZIoAbrdE6wa.czJsMt1QeCGevUy3RFSYSLdN8mzlea2'),
(18, 'Mrs.', 'Accounting', 'Accounting', 'Accounting', '', 'Accounting', 'Accounting Officer', 'accounting@gmail.com', '$2y$10$cIRoFFSBLD7hV9X4GnDgNO7uVh1eiv9WIG5QD5Ibc4fJH9jGtK66C');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_subcategories`
--
ALTER TABLE `account_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `account_sub_subcategories`
--
ALTER TABLE `account_sub_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- Indexes for table `account_sub_sub_subcategories`
--
ALTER TABLE `account_sub_sub_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_subcategory_id` (`sub_subcategory_id`);

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `procurement`
--
ALTER TABLE `procurement`
  ADD PRIMARY KEY (`procurement_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `inventory_id` (`inventory_id`),
  ADD KEY `movement_type` (`movement_type`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `date_created` (`date_created`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supply_request`
--
ALTER TABLE `supply_request`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `transaction_list`
--
ALTER TABLE `transaction_list`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `transaction_specifications`
--
ALTER TABLE `transaction_specifications`
  ADD PRIMARY KEY (`spec_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_subcategories`
--
ALTER TABLE `account_subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `account_sub_subcategories`
--
ALTER TABLE `account_sub_subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `account_sub_sub_subcategories`
--
ALTER TABLE `account_sub_sub_subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `procurement`
--
ALTER TABLE `procurement`
  MODIFY `procurement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `supply_request`
--
ALTER TABLE `supply_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `transaction_list`
--
ALTER TABLE `transaction_list`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_specifications`
--
ALTER TABLE `transaction_specifications`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_subcategories`
--
ALTER TABLE `account_subcategories`
  ADD CONSTRAINT `account_subcategories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `account_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_sub_subcategories`
--
ALTER TABLE `account_sub_subcategories`
  ADD CONSTRAINT `account_sub_subcategories_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `account_subcategories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_sub_sub_subcategories`
--
ALTER TABLE `account_sub_sub_subcategories`
  ADD CONSTRAINT `account_sub_sub_subcategories_ibfk_1` FOREIGN KEY (`sub_subcategory_id`) REFERENCES `account_sub_subcategories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `procurement`
--
ALTER TABLE `procurement`
  ADD CONSTRAINT `procurement_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `procurement_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `stock_logs_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_logs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  ADD CONSTRAINT `supplier_transaction_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`);

--
-- Constraints for table `transaction_specifications`
--
ALTER TABLE `transaction_specifications`
  ADD CONSTRAINT `transaction_specifications_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `supplier_transaction` (`transaction_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
