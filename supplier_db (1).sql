-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2025 at 08:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
(17, 15, 'School Building - Main Campus', '2025-08-30 01:25:08'),
(18, 15, 'School Building - BED Campus', '2025-08-30 01:25:20'),
(19, 16, 'Furniture and Fixtures - Main Campus', '2025-08-30 01:26:19'),
(20, 16, 'Furniture and Fixtures - BED Campus', '2025-08-30 01:26:30'),
(21, 17, 'Laboratory Equipment - CJE', '2025-08-30 01:26:46'),
(22, 17, 'Laboratory Equipment - HME', '2025-08-30 01:27:12'),
(23, 17, 'Laboratory Equipment - Science (TED)', '2025-08-30 01:27:24'),
(24, 17, 'Laboratory Equipment - Science (BED)', '2025-08-30 01:27:35'),
(25, 17, 'Laboratory Equipment - Physics (BED)', '2025-08-30 01:28:02'),
(26, 17, 'Laboratory Equipment - TLE', '2025-08-30 01:28:10'),
(27, 18, 'Office Equipment - Main Campus', '2025-08-30 01:28:28'),
(28, 18, 'Office Equipment - BED Campus', '2025-08-30 01:28:42'),
(29, 19, 'Computers - Main Campus', '2025-08-30 01:28:55'),
(30, 19, 'Computers - BED Campus', '2025-08-30 01:29:23'),
(31, 20, 'Service Vehicle', '2025-08-30 01:29:53'),
(32, 21, 'Software', '2025-08-30 01:30:05'),
(33, 21, 'Patents and License', '2025-08-30 01:30:12'),
(37, 23, 'Office Supplies (Main/ BED Campus)', '2025-08-30 01:39:44'),
(38, 23, 'Electrical Supplies (Main/ BED Campus)', '2025-08-30 01:39:59'),
(39, 23, 'School Supplies (Main/ BED Campus)', '2025-08-30 01:40:17'),
(40, 23, 'Textbooks (Main/ BED Campus)', '2025-08-30 01:40:32'),
(41, 24, 'Janitorial & Cleaning Expenses - Main Campus', '2025-08-30 01:41:48'),
(42, 24, 'Janitorial & Cleaning Expenses - BED Campus', '2025-08-30 01:41:53'),
(43, 25, 'Testing Materials - Main Campus', '2025-08-30 01:42:27'),
(44, 25, 'Testing Materials - BED Campus', '2025-08-30 01:42:33'),
(45, 26, 'Library Expenses - Main Campus', '2025-08-30 01:43:07'),
(46, 26, 'Library Expenses - BED Campus', '2025-08-30 01:43:15'),
(47, 27, 'Laboratory Equipment - CJE', '2025-08-30 01:43:58'),
(48, 27, 'Laboratory Equipment - HME', '2025-08-30 01:44:04'),
(49, 27, 'Laboratory Equipment - Science (TED)', '2025-08-30 01:44:12'),
(50, 27, 'Laboratory Equipment - Science (BED)', '2025-08-30 01:44:23'),
(51, 27, 'Laboratory Equipment - Physics (BED)', '2025-08-30 01:44:34'),
(52, 27, 'Laboratory Equipment - TLE', '2025-08-30 01:44:46'),
(53, 28, 'Medical Expenses - Main Campus', '2025-08-30 01:45:20'),
(54, 28, 'Medical Expenses - BED Campus', '2025-08-30 01:45:25'),
(55, 29, 'Repairs and Maintenance - Main Campus', '2025-08-30 01:45:52'),
(56, 29, 'Repairs and Maintenance - BED Campus', '2025-08-30 01:46:03');

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
(14, 'Assets'),
(19, 'Computers'),
(22, 'Expenses'),
(16, 'Furniture and Fixtures'),
(21, 'Intangible Assets'),
(24, 'Janitorial & Cleaning Expenses'),
(17, 'Laboratory Equipment'),
(27, 'Laboratory Expenses'),
(26, 'Library Expenses'),
(28, 'Medical Expenses'),
(18, 'Office Equipment'),
(15, 'Property and Equipment'),
(29, 'Repairs and Maintenance'),
(23, 'Supplies and Materials'),
(25, 'Testing Materials'),
(20, 'Vehicle');

-- --------------------------------------------------------

--
-- Table structure for table `canvass`
--

CREATE TABLE `canvass` (
  `canvass_id` int(11) NOT NULL,
  `canvass_date` date NOT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('Draft','Completed','Approved','Cancelled') DEFAULT 'Draft',
  `canvassed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canvass_items`
--

CREATE TABLE `canvass_items` (
  `canvass_item_id` int(11) NOT NULL,
  `canvass_id` int(11) NOT NULL,
  `item_number` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `item_description` text NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canvass_status_history`
--

CREATE TABLE `canvass_status_history` (
  `csh_id` int(11) NOT NULL,
  `canvass_id` int(11) NOT NULL,
  `old_status` enum('Draft','Completed','Approved','Cancelled') DEFAULT NULL,
  `new_status` enum('Draft','Completed','Approved','Cancelled') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `supplier_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `receiver` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Discontinued') DEFAULT NULL,
  `received_notes` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `category`, `description`, `current_stock`, `quantity`, `unit`, `unit_cost`, `reorder_level`, `supplier_id`, `location`, `receiver`, `status`, `received_notes`, `created_by`, `date_created`, `last_updated_by`, `date_updated`) VALUES
(1044, 'Staple Wire #10', 'Supplies and Materials', '# 10 staple wire 12/1', 17, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1045, 'Staple Wire #35', 'Supplies and Materials', '#35  staple wire', 45, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1046, 'Battery AA Energizer', 'Supplies and Materials', 'AA Energizer battery', 121, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1047, 'Battery AA Eveready', 'Supplies and Materials', 'AA Eveready battery', 96, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1048, 'Battery AAA Energizer', 'Supplies and Materials', 'AAA energizer battery', 120, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1049, 'Alcohol', 'Supplies and Materials', 'Alcohol', 4, 0, 'gals.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 14, '2025-09-10 11:43:09'),
(1050, 'Ball Pen', 'Supplies and Materials', 'Ball pen', 131, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1051, 'Binder Clip (Big)', 'Supplies and Materials', 'Binder clip big', 5, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1052, 'Binder Clip (Small)', 'Supplies and Materials', 'Binder clip small', 7, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1053, 'Blank CD-R', 'Supplies and Materials', 'Blank CD-R tape', 46, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1054, 'Bond Paper A4', 'Supplies and Materials', 'Bond paper A4', 60, 0, 'reams', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 14, '2025-09-10 11:43:09'),
(1055, 'Bond Paper Short', 'Supplies and Materials', 'Bond paper short', 130, 0, 'reams', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1056, 'Brother Ink (Black)', 'Supplies and Materials', 'Brother ink black', 16, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1057, 'Brother Ink (Cyan)', 'Supplies and Materials', 'Brother ink cyan', 7, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1058, 'Brother Ink (Magenta)', 'Supplies and Materials', 'Brother ink magenta', 6, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1059, 'Brother Ink (Yellow)', 'Supplies and Materials', 'Brother ink yellow', 7, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1060, 'Brown Envelope Long', 'Supplies and Materials', 'Brown envelope long ', 830, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1061, 'Canon 810 Ink (Black)', 'Supplies and Materials', 'Canon 810 black', 7, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1062, 'Canon 811 Ink (Colored)', 'Supplies and Materials', 'Canon 811 colored', 7, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1063, 'Carbon Paper', 'Supplies and Materials', 'Carbon paper', 5, 0, 'packs', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1064, 'Century Pins', 'Supplies and Materials', 'Century pins', 2, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1065, 'Certificate Holder Short', 'Supplies and Materials', 'Certificate holder short', 16, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1066, 'Clear Book Long', 'Supplies and Materials', 'Clear book long', 1, 0, 'pc.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1067, 'Cotton 300g', 'Supplies and Materials', 'Cotton 300 grams', 3, 0, 'packs', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1068, 'Correction Tape (Disposable)', 'Supplies and Materials', 'Correction tape disposable', 66, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1069, 'Correction Tape Plus', 'Supplies and Materials', 'Correction tape plus', 46, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1070, 'Correction Tape Refill', 'Supplies and Materials', 'Correction tape refill', 21, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1071, 'Battery D2 Eveready', 'Supplies and Materials', 'D2 Eveready battery', 12, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1072, 'Double-Sided Tape', 'Supplies and Materials', 'Double sided tape', 9, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1073, 'Epson Ink (Black)', 'Supplies and Materials', 'Epson ink black', 69, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1074, 'Epson Ink (Cyan)', 'Supplies and Materials', 'Epson ink cyan', 63, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1075, 'Epson Ink (Magenta)', 'Supplies and Materials', 'Epson ink magenta', 61, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1076, 'Epson Ink (Yellow)', 'Supplies and Materials', 'Epson ink yellow', 71, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1077, 'Expanding Envelope Brown (w/ Garter)', 'Supplies and Materials', 'Exp. Envelop w/garter brown', 100, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1078, 'Expanding Envelope Green (w/ Garter)', 'Supplies and Materials', 'Exp. envelop w/garter green', 118, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1079, 'Fastener', 'Supplies and Materials', 'Fastener ', 13, 0, 'boxes.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1080, 'Filer Green', 'Supplies and Materials', 'Filer green', 60, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1081, 'Folder Brown Long', 'Supplies and Materials', 'Folder brown long', 300, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1082, 'Folder Brown Short', 'Supplies and Materials', 'Folder brown short', 70, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1083, 'Expanding Folder Green Long', 'Supplies and Materials', 'Folder exp. Green long', 266, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1084, 'Folder White Long', 'Supplies and Materials', 'Folder white  long', 100, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1085, 'Folder White Short', 'Supplies and Materials', 'Folder white short', 300, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1086, 'Folder Short Green', 'Supplies and Materials', 'Folder short green', 50, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1087, 'Glue Small Elmer?s 40g', 'Supplies and Materials', 'Glue small elmers 40g', 14, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1088, 'Glue Elmer?s 130g', 'Supplies and Materials', 'Glue elmers    130g', 29, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1089, 'Glue Gun', 'Supplies and Materials', 'Glue gun', 1, 0, 'pc.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1090, 'HP 21 Ink (Black)', 'Supplies and Materials', 'HP 21 black', 4, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1091, 'HP 680 Ink (Black)', 'Supplies and Materials', 'HP 680 black', 4, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1092, 'HP 680 Ink (Colored)', 'Supplies and Materials', 'HP 680 colored', 6, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1093, 'HP 932 Ink (Black)', 'Supplies and Materials', 'HP 932 black', 4, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1094, 'Laminating Film A4', 'Supplies and Materials', 'Laminating film A4 ', 1, 0, 'pack', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1095, 'Laminating Film Long', 'Supplies and Materials', 'Laminating film long', 2, 0, 'pack', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1096, 'Laminating Film Short', 'Supplies and Materials', 'Laminating film short', 3, 0, 'packs', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1097, 'Long Brown Expanding Envelope', 'Supplies and Materials', 'Long brown envelope exp.', 100, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1098, 'Mailing Envelope White', 'Supplies and Materials', 'Mailing envelop white', 450, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1099, 'Manila Paper', 'Supplies and Materials', 'Manila paper', 800, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1100, 'Masking Tape', 'Supplies and Materials', 'Masking tape', 90, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1101, 'My Gel Refill', 'Supplies and Materials', 'My Gel refill', 12, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1102, 'My Gel Sign Pen', 'Supplies and Materials', 'My gel sign ', 7, 0, 'doz./84', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1103, 'Newsprint Long', 'Supplies and Materials', 'Newsprint long', 859, 0, 'reams', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1104, 'Newsprint Short', 'Supplies and Materials', 'Newsprint short', 49, 0, 'reams', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1105, 'Packing Tape', 'Supplies and Materials', 'Packing tape', 13, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1106, 'Paper Clip (Big)', 'Supplies and Materials', 'Paper clip big', 57, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1107, 'Paper Clip (Small)', 'Supplies and Materials', 'Paper clip small', 30, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1108, 'Parchment Paper Short', 'Supplies and Materials', 'Parchment paper short', 450, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1109, 'Permanent Ink (Pilot)', 'Supplies and Materials', 'Permanent ink Pilot', 80, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1110, 'Permanent Marker', 'Supplies and Materials', 'Permanent marker', 5, 0, 'doz.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1111, 'Photo Paper 20/1', 'Supplies and Materials', 'Photo paper  20/1', 80, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1112, 'Pixma 706 Ink (Colored)', 'Supplies and Materials', 'Pixma 706 colored', 1, 0, 'pc.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1113, 'Pixma 790 Ink (Black)', 'Supplies and Materials', 'Pixma 790 black', 1, 0, 'pc.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1114, 'Pixma 790 Ink (Cyan)', 'Supplies and Materials', 'Pixma 790 cyan', 2, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1115, 'Pixma 790 Ink (Magenta)', 'Supplies and Materials', 'Pixma 790 magenta', 2, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1116, 'Pixma 790 Ink (Yellow)', 'Supplies and Materials', 'Pixma 790 yellow', 2, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1117, 'Plastic Exp. Envelope w/ Holder (5)', 'Supplies and Materials', 'Plastic envelop exp.5 with holder', 230, 0, 'ocs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1118, 'Plastic Cover', 'Supplies and Materials', 'Plastic cover', 5, 0, 'rolls', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1119, 'Plastic Folder Long', 'Supplies and Materials', 'Plastic folder long', 72, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1120, 'Printer Ribbon', 'Supplies and Materials', 'Printer ribbon', 34, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1121, 'Puncher', 'Supplies and Materials', 'Puncher', 4, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1122, 'Push Pins', 'Supplies and Materials', 'Push pins', 17, 0, 'packs', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1123, 'Record Book 500 Pages', 'Supplies and Materials', 'Record book 500pages', 24, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1124, 'Record Book 300 Pages', 'Supplies and Materials', 'Record book 300pages', 13, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1125, 'Record Book 150 Pages', 'Supplies and Materials', 'Record book 150 pages', 27, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1126, 'Rubber Band', 'Supplies and Materials', 'Rubber bond', 8, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1127, 'Scotch Tape', 'Supplies and Materials', 'Scotch tape', 50, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1128, 'Sharpener Small', 'Supplies and Materials', 'Sharpener small', 11, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1129, 'Smart TV', 'Property and Equipment', 'Smart TV', 1, 0, 'pc.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:46:00'),
(1130, 'Staple Wire Remover', 'Supplies and Materials', 'Staple wire remover', 2, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1131, 'Stapler #35 Joy', 'Supplies and Materials', ' Stapler #35 Joy, ', 4, 0, 'pcs.+6', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1132, 'Special Paper Flordeliz', 'Supplies and Materials', 'Special paper Flordeliz', 970, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1133, 'Stamp Pad Ink Small', 'Supplies and Materials', 'Stamp pad ink small', 2, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1134, 'Stapler #10', 'Supplies and Materials', 'Stapler # 10', 6, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1135, 'Sticker Paper 10/1', 'Supplies and Materials', 'Sticker paper  10/1', 730, 0, '', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1136, 'Test Good Sign Pen', 'Supplies and Materials', 'Test good sign pen', 5, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1137, 'Thumbtacks', 'Supplies and Materials', 'Thumbtacks', 38, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1138, 'Typewriter Ribbon 12/1', 'Supplies and Materials', 'Typewriter ribbon   12/1', 12, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1139, 'USB Flash Drive 16GB', 'Supplies and Materials', 'USB Flash drive 16GB', 3, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1140, 'USB Flash Drive 32GB', 'Supplies and Materials', 'USB Flash drive 32 GB', 3, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1141, 'UV Ink (Black)', 'Supplies and Materials', 'UV ink black', 3, 0, 'bots', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1142, 'UV Ink (Cyan)', 'Supplies and Materials', 'UV ink cyan', 5, 0, 'bots.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1143, 'UV Ink (Magenta)', 'Supplies and Materials', 'UV ink magenta', 6, 0, 'bots.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1144, 'UV Ink (Yellow)', 'Supplies and Materials', 'UV ink yellow', 4, 0, 'bots.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1145, 'Vellum Long Cream Assorted', 'Supplies and Materials', 'Vellum long cream, assorted color', 180, 0, 'pcs.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1146, 'Wall Clock', 'Supplies and Materials', 'Wall clock (P305.00)', 2, 0, 'unit', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1147, 'Whiteboard Ink', 'Supplies and Materials', 'Wytebord ink', 52, 0, 'bots.', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1148, 'Whiteboard Marker', 'Supplies and Materials', 'Wytebord marker', 6, 0, 'boxes', 0.00, 0, 27, '', 'Supply In-charge', '', '', 0, '0000-00-00 00:00:00', 0, '2025-09-10 11:43:09'),
(1152, 'Bond Paper', 'Supplies and Materials', 'Bond Paper', 200, NULL, 'reams', 210.00, 0, 27, NULL, NULL, 'Active', NULL, 17, '2025-09-09 16:03:05', 8, '2025-09-12 11:04:25');

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
(191, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 1, 'supply_request', 0, '2025-09-12 05:27:55', '2025-09-12 05:27:55'),
(192, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 1, 'supply_request', 0, '2025-09-12 05:27:55', '2025-09-12 05:27:55'),
(193, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 1, 'supply_request', 0, '2025-09-12 05:27:55', '2025-09-12 05:27:55'),
(194, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 2, 'supply_request', 0, '2025-09-12 05:30:32', '2025-09-12 05:30:32'),
(195, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 2, 'supply_request', 0, '2025-09-12 05:30:32', '2025-09-12 05:30:32'),
(196, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 2, 'supply_request', 0, '2025-09-12 05:30:32', '2025-09-12 05:30:32'),
(197, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 3, 'supply_request', 0, '2025-09-12 05:52:11', '2025-09-12 05:52:11'),
(198, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 3, 'supply_request', 0, '2025-09-12 05:52:11', '2025-09-12 05:52:11'),
(199, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 3, 'supply_request', 0, '2025-09-12 05:52:11', '2025-09-12 05:52:11'),
(200, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 4, 'supply_request', 0, '2025-09-12 05:54:11', '2025-09-12 05:54:11'),
(201, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 4, 'supply_request', 0, '2025-09-12 05:54:11', '2025-09-12 05:54:11'),
(202, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 4, 'supply_request', 0, '2025-09-12 05:54:11', '2025-09-12 05:54:11'),
(203, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 5, 'supply_request', 0, '2025-09-12 06:03:03', '2025-09-12 06:03:03'),
(204, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 5, 'supply_request', 0, '2025-09-12 06:03:03', '2025-09-12 06:03:03'),
(205, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 5, 'supply_request', 0, '2025-09-12 06:03:03', '2025-09-12 06:03:03'),
(206, 13, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 6, 'supply_request', 0, '2025-09-12 06:08:52', '2025-09-12 06:08:52'),
(207, 15, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 6, 'supply_request', 0, '2025-09-12 06:08:52', '2025-09-12 06:08:52'),
(208, 14, 'request', 'New Consumables Request', 'A new Consumables request has been submitted by Faculty: asdafer', 6, 'supply_request', 0, '2025-09-12 06:08:52', '2025-09-12 06:08:52'),
(209, 13, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 7, 'supply_request', 0, '2025-09-12 06:24:56', '2025-09-12 06:24:56'),
(210, 15, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 7, 'supply_request', 0, '2025-09-12 06:24:56', '2025-09-12 06:24:56'),
(211, 13, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 8, 'supply_request', 0, '2025-09-12 06:30:34', '2025-09-12 06:30:34'),
(212, 15, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 8, 'supply_request', 0, '2025-09-12 06:30:34', '2025-09-12 06:30:34'),
(213, 13, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 9, 'supply_request', 0, '2025-09-12 06:37:05', '2025-09-12 06:37:05'),
(214, 15, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 9, 'supply_request', 0, '2025-09-12 06:37:05', '2025-09-12 06:37:05'),
(215, 13, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 10, 'supply_request', 0, '2025-09-12 06:38:28', '2025-09-12 06:38:28'),
(216, 15, 'request', 'New Supply Request', 'A new Supply request has been submitted by Faculty: asdafer', 10, 'supply_request', 0, '2025-09-12 06:38:28', '2025-09-12 06:38:28');

-- --------------------------------------------------------

--
-- Table structure for table `procurement`
--

CREATE TABLE `procurement` (
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
-- Table structure for table `property_inventory`
--

CREATE TABLE `property_inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `supplier_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `receiver` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Discontinued') DEFAULT NULL,
  `received_notes` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_inventory`
--

INSERT INTO `property_inventory` (`inventory_id`, `item_name`, `category`, `description`, `current_stock`, `quantity`, `unit`, `unit_cost`, `reorder_level`, `supplier_id`, `location`, `receiver`, `status`, `received_notes`, `created_by`, `date_created`, `last_updated_by`, `date_updated`) VALUES
(1156, 'Bond Paper', 'Office Supplies (Main/ BED Campus)', 'Bond Paper', 100, NULL, 'reams', 210.00, 0, 27, NULL, 'Property Custodian', 'Active', NULL, 8, '2025-09-12 11:07:00', 8, '2025-09-12 11:07:00');

-- --------------------------------------------------------

--
-- Table structure for table `property_request`
--

CREATE TABLE `property_request` (
  `property_id` int(11) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `date_requested` varchar(255) DEFAULT NULL,
  `date_return` varchar(255) DEFAULT NULL,
  `temporary_transfer` varchar(255) DEFAULT NULL,
  `permanent_transfer` varchar(255) DEFAULT NULL,
  `reason_for_transfer` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `request_description` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `quantity_requested` varchar(255) DEFAULT NULL,
  `request_type` varchar(255) NOT NULL,
  `tagging` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_request`
--

INSERT INTO `property_request` (`property_id`, `user_id`, `date_requested`, `date_return`, `temporary_transfer`, `permanent_transfer`, `reason_for_transfer`, `category`, `item_name`, `request_description`, `brand`, `color`, `type`, `quantity_requested`, `request_type`, `tagging`, `status`) VALUES
(10, '12', '2025-09-12', '2025-09-13', 'Temporary Transfer', '', 'asdafer', 'Laboratory Equipment - HME', 'asdafer', 'asdafer', 'asdafer', 'asdafer', 'asafer', '11', 'nonconsumables', 'property', '');

-- --------------------------------------------------------

--
-- Table structure for table `property_stock_logs`
--

CREATE TABLE `property_stock_logs` (
  `log_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `request_id` int(255) NOT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `requester_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `receiver` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_stock_logs`
--

INSERT INTO `property_stock_logs` (`log_id`, `inventory_id`, `request_id`, `movement_type`, `requester_name`, `quantity`, `previous_stock`, `new_stock`, `notes`, `created_by`, `date_created`, `receiver`) VALUES
(90, 1156, 0, 'IN', NULL, 100, 0, 100, 'Received from supplier transaction #28. ', 8, '2025-09-12 11:07:00', 'Property Custodian');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `po_date` date NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'Check',
  `payment_details` text DEFAULT NULL,
  `cash_amount` decimal(15,2) DEFAULT 0.00,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('Draft','Pending','Approved','Rejected','Completed','Cancelled') DEFAULT 'Draft',
  `prepared_by` int(11) DEFAULT NULL,
  `checked_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `prepared_date` datetime DEFAULT NULL,
  `checked_date` datetime DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_attachments`
--

CREATE TABLE `purchase_order_attachments` (
  `poa_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `poi_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `item_number` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_status_history`
--

CREATE TABLE `purchase_order_status_history` (
  `posh_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `old_status` enum('Draft','Pending','Approved','Rejected','Completed','Cancelled') DEFAULT NULL,
  `new_status` enum('Draft','Pending','Approved','Rejected','Completed','Cancelled') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `log_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `request_id` int(255) NOT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `requester_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `receiver` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`log_id`, `inventory_id`, `request_id`, `movement_type`, `requester_name`, `quantity`, `previous_stock`, `new_stock`, `notes`, `created_by`, `date_created`, `receiver`) VALUES
(86, 1152, 0, 'IN', NULL, 100, 0, 100, 'Received from supplier transaction #28. ', 17, '2025-09-09 16:03:05', NULL),
(87, 1152, 0, 'IN', NULL, 100, 100, 200, 'Received from supplier transaction #28. ', 8, '2025-09-12 11:04:25', 'Property Custodian');

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
(27, 'All Supplies', 'Bernadette', '092923435', 'berna@gmail.com', '123-456-789', 'allsupplier.com', 'Toril Davao City', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'IT Equipment Supplier', 'ICT Equipment and Devices', 'Cash', '123-456', '2025-08-27', 'Active', 14, '2025-08-27 02:35:01', NULL, NULL, 'They have all supplies needed to school'),
(28, 'Mark', '123123545', '092929485', 'mark@gmail.com', '1230-345', 'paperone.com', 'Davao City', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'IT Equipment Supplier', 'ICT Equipment and Devices', 'Cash', '123-456', '2025-09-03', 'Active', 15, '2025-09-03 07:49:13', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transaction`
--

CREATE TABLE `supplier_transaction` (
  `procurement_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `sales_type` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `brand_model` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `receiver` varchar(255) DEFAULT NULL,
  `invoice_path` varchar(500) DEFAULT NULL,
  `delivery_receipt_path` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_notes` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `received_by` int(11) DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_transaction`
--

INSERT INTO `supplier_transaction` (`procurement_id`, `item_name`, `supplier_id`, `invoice_no`, `sales_type`, `category`, `quantity`, `brand_model`, `color`, `type`, `unit`, `unit_price`, `total_amount`, `receiver`, `invoice_path`, `delivery_receipt_path`, `notes`, `received_notes`, `status`, `created_by`, `date_created`, `received_by`, `date_received`, `last_updated_by`, `date_updated`) VALUES
(28, 'Bond Paper', 27, '123-456', 'Cash', 'Office Supplies (Main/ BED Campus)', 100, 'Paper One', 'White', 'A4', 'reams', 210.00, 21000.00, 'Property Custodian', NULL, NULL, '', NULL, 'Added', 15, '2025-09-09 00:00:00', NULL, NULL, 15, '2025-09-12 11:07:00'),
(29, 'Pentel pen', 27, '123', 'Cash', 'Office Supplies (Main/ BED Campus)', 1, 'Marker', 'Black', 'Ball Pen', 'pcs', 100.00, 100.00, 'Supply In-charge', NULL, NULL, '', NULL, 'Pending', 15, '2025-09-09 00:00:00', NULL, NULL, NULL, NULL);

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
  `item_name` varchar(255) NOT NULL,
  `request_description` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
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
  `remarks` text DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `email` varchar(255) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `academic_title` varchar(50) DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `title`, `first_name`, `middle_name`, `last_name`, `email`, `suffix`, `academic_title`, `user_type`, `username`, `password`) VALUES
(4, 'Sir', 'Erwin', 'Pogi', 'Acedillo', '', '', 'MIT', 'MIS Head', 'erwinacedillo@gmail.com', '$2y$10$sqVTg7YdLiuS8Jx2bMY.OuEIkudSO/.ToKuU35RkVpmDAY7RWQCr.'),
(8, 'Admin', 'Vincent', 'Pogi', 'Crame', '', 'NONE', 'MIS PROGRAMMER', 'Admin', 'vincentcrame7@gmail.com', '$2y$10$8TDCeARnXXbW3eWjEeULOOQ.SC/.8SYiRypiDmLOwdv/5.KVEHwXe'),
(9, 'Sir', 'Staff', 'Staff', 'Staff', '', 'staff', 'Staff', 'Staff', 'staff@gmail.com', '$2y$10$Ax96ZYijBBqpRMLdwnoOduMZk8GJNe/OEW4FLVQ2177xBT0VFDr4u'),
(11, 'Dr.', 'Ruben', '', 'Dela Cruz', '', 'N/A', 'Ph.D', 'School President', 'ruben@gmail.com', '$2y$10$VYwZKAJTXrGQsZ9f5rVdMuprkCHYM5yDBROLG4b0D78KEfTyOSNsm'),
(12, 'Sir', 'June Carl', '', 'Echavia', '', 'N/A', 'Teacher', 'Faculty', 'junecarl@gmail.com', '$2y$10$eCxmouJR3YSa1HPGz7NAM.u5UXUZuMOD/sn9sSY4.M9BNff8r1hY.'),
(13, 'Sir', 'Erwin', '', 'Acedillo', '', '', '', 'Immediate Head', 'erwin@gmail.com', '$2y$10$SbF0o6H2YlbpZTONszE25.8uXRLonPewEzshwFKf3K0L.KeRZBPny'),
(14, 'Maam', 'Juliet', '', 'Geremino', '', '', '', 'Supply In-charge', 'juliet@gmail.com', '$2y$10$xcXtjDe//VUPb0YNFX3nn.qaallQC.sS3Qrgrf04takFkqSz.g/ha'),
(15, 'Maam', 'Marilou', '', 'Suarez', '', '', '', 'Purchasing Officer', 'marilou@gmail.com', '$2y$10$e3OrYs2.Z6tqN4m0tjalfuP7.2xzyH61sE8iyLr.36xzz3NCWjgJO'),
(16, 'Dr.', 'Cinna', '', 'Rose', '', 'N/A', 'M.D', 'VP for Finance & Administration', 'cinna@gmail.com', '$2y$10$l4seTrgPgUaBLoz9rXgx7.cjQjwaQOi8s3JaB3hdt/1xZfKxBNgqO'),
(17, 'Maam', 'Mary Grace', '', 'Baytola', '', '', 'Maam', 'Property Custodian', 'marygrace@gmail.com', '$2y$10$sYnybRrh2aZIoAbrdE6wa.czJsMt1QeCGevUy3RFSYSLdN8mzlea2'),
(18, 'Mrs.', 'Accounting', 'Accounting', 'Accounting', '', '', 'Accounting', 'Accounting Officer', '18-000124', '$2y$10$q2kC6hJCgSYE1maEK56lue8MQmHdvgO.S/hjBgfY3xlbyC8vzFROy');

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
-- Indexes for table `canvass`
--
ALTER TABLE `canvass`
  ADD PRIMARY KEY (`canvass_id`),
  ADD KEY `canvassed_by` (`canvassed_by`),
  ADD KEY `idx_canvass_date` (`canvass_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `canvass_items`
--
ALTER TABLE `canvass_items`
  ADD PRIMARY KEY (`canvass_item_id`),
  ADD UNIQUE KEY `unique_canvass_item` (`canvass_id`,`item_number`),
  ADD KEY `idx_canvass_id` (`canvass_id`),
  ADD KEY `idx_item_number` (`item_number`),
  ADD KEY `idx_supplier_name` (`supplier_name`);

--
-- Indexes for table `canvass_status_history`
--
ALTER TABLE `canvass_status_history`
  ADD PRIMARY KEY (`csh_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_canvass_id_history` (`canvass_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
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
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `property_inventory`
--
ALTER TABLE `property_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `property_request`
--
ALTER TABLE `property_request`
  ADD PRIMARY KEY (`property_id`);

--
-- Indexes for table `property_stock_logs`
--
ALTER TABLE `property_stock_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `movement_type` (`movement_type`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `date_created` (`date_created`),
  ADD KEY `inventory_id` (`inventory_id`) USING BTREE;

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `prepared_by` (`prepared_by`),
  ADD KEY `checked_by` (`checked_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_po_number` (`po_number`),
  ADD KEY `idx_po_date` (`po_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `purchase_order_attachments`
--
ALTER TABLE `purchase_order_attachments`
  ADD PRIMARY KEY (`poa_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_po_id_attachments` (`po_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`poi_id`),
  ADD UNIQUE KEY `unique_po_item` (`po_id`,`item_number`),
  ADD KEY `idx_po_id` (`po_id`),
  ADD KEY `idx_item_number` (`item_number`);

--
-- Indexes for table `purchase_order_status_history`
--
ALTER TABLE `purchase_order_status_history`
  ADD PRIMARY KEY (`posh_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_po_id_history` (`po_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

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
  ADD PRIMARY KEY (`procurement_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `status` (`status`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `canvass`
--
ALTER TABLE `canvass`
  MODIFY `canvass_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `canvass_items`
--
ALTER TABLE `canvass_items`
  MODIFY `canvass_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `canvass_status_history`
--
ALTER TABLE `canvass_status_history`
  MODIFY `csh_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1153;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `procurement`
--
ALTER TABLE `procurement`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `property_inventory`
--
ALTER TABLE `property_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1157;

--
-- AUTO_INCREMENT for table `property_request`
--
ALTER TABLE `property_request`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `property_stock_logs`
--
ALTER TABLE `property_stock_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `purchase_order_attachments`
--
ALTER TABLE `purchase_order_attachments`
  MODIFY `poa_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `poi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `purchase_order_status_history`
--
ALTER TABLE `purchase_order_status_history`
  MODIFY `posh_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  MODIFY `procurement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `supply_request`
--
ALTER TABLE `supply_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `transaction_list`
--
ALTER TABLE `transaction_list`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaction_specifications`
--
ALTER TABLE `transaction_specifications`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Constraints for table `canvass`
--
ALTER TABLE `canvass`
  ADD CONSTRAINT `canvass_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `canvass_ibfk_2` FOREIGN KEY (`canvassed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `canvass_items`
--
ALTER TABLE `canvass_items`
  ADD CONSTRAINT `canvass_items_ibfk_1` FOREIGN KEY (`canvass_id`) REFERENCES `canvass` (`canvass_id`) ON DELETE CASCADE;

--
-- Constraints for table `canvass_status_history`
--
ALTER TABLE `canvass_status_history`
  ADD CONSTRAINT `canvass_status_history_ibfk_1` FOREIGN KEY (`canvass_id`) REFERENCES `canvass` (`canvass_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `canvass_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `procurement`
--
ALTER TABLE `procurement`
  ADD CONSTRAINT `procurement_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`);

--
-- Constraints for table `property_stock_logs`
--
ALTER TABLE `property_stock_logs`
  ADD CONSTRAINT `fk_property_stock_logs_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `property_inventory` (`inventory_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `property_stock_logs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`prepared_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`checked_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_order_attachments`
--
ALTER TABLE `purchase_order_attachments`
  ADD CONSTRAINT `purchase_order_attachments_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_status_history`
--
ALTER TABLE `purchase_order_status_history`
  ADD CONSTRAINT `purchase_order_status_history_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `user` (`id`);

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
  ADD CONSTRAINT `supplier_transaction_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_transaction_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transaction_specifications`
--
ALTER TABLE `transaction_specifications`
  ADD CONSTRAINT `transaction_specifications_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `procurement` (`transaction_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
