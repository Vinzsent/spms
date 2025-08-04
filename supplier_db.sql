-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 02, 2025 at 06:09 AM
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
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `category`, `description`, `current_stock`, `unit`, `unit_cost`, `reorder_level`, `supplier_id`, `location`, `status`, `created_by`, `date_created`, `last_updated_by`, `date_updated`) VALUES
(21, 'asdafer', 'Office Supplies', 'asdafer', 124, 'pc', 11.00, 11, 21, 'Storage Room A', 'Active', NULL, '2025-07-30 14:01:37', 5, '2025-07-31 08:39:57'),
(22, 'Headset', 'Other', 'Nothing to lose', 200, 'pc', 100.00, 100, 16, 'Store A-1', 'Active', NULL, '2025-07-30 15:44:48', 5, '2025-07-31 11:08:46');

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

INSERT INTO `procurement` (`procurement_id`, `item_name`, `supplier_id`, `quantity`, `unit`, `unit_price`, `total_amount`, `invoice_path`, `delivery_receipt_path`, `notes`, `status`, `created_by`, `date_created`, `received_by`, `date_received`, `last_updated_by`, `date_updated`) VALUES
(1, 'asdafer', 23, 1, 'pc', 1.00, 1.00, 'uploads/procurement/invoice_1753854528_6889b2401976a.jpg', 'uploads/procurement/receipt_1753854528_6889b240198f0.jpg', 'asdafer', 'Pending', NULL, '2025-07-30 13:48:48', NULL, NULL, 5, '2025-07-30 16:26:21'),
(2, 'asdafer', 8, 11, 'box', 11.00, 121.00, 'uploads/procurement/invoice_1753862026_6889cf8a2fd81.jpg', 'uploads/procurement/receipt_1753862026_6889cf8a2ff9b.jpg', 'adafer', 'Pending', NULL, '2025-07-30 15:53:46', NULL, NULL, 5, '2025-07-31 07:31:11');

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
(1, 21, 'IN', 11, 0, 11, 'Initial stock entry', NULL, '2025-07-30 14:01:37'),
(2, 21, 'IN', 1, 11, 12, '', NULL, '2025-07-30 14:02:33'),
(3, 21, 'IN', 123, 12, 135, '', NULL, '2025-07-30 14:02:42'),
(4, 22, 'IN', 100, 0, 100, 'Initial stock entry', NULL, '2025-07-30 15:44:48'),
(5, 22, 'OUT', 11, 100, 89, '11', NULL, '2025-07-31 08:39:35'),
(6, 21, 'OUT', 11, 135, 124, '11', NULL, '2025-07-31 08:39:57'),
(7, 22, 'IN', 111, 89, 200, 'adafer', NULL, '2025-07-31 11:08:46');

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
(7, 'DFESTORE.COM', 'Erwin P. Acedillo', '293-9928', 'dfe@yahoo.com', '293-9928', 'www.dfe.com', '168 5th A Street, Ecoland', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'Machinery and Equipment Supplier', 'Heavy Machinery', 'asdafer', '734-438-579-000', '2025-07-12', 'Active', 4, '0000-00-00 00:00:00', NULL, NULL, 'Their executive firm have mean light within'),
(8, 'Cstore', 'vincent m. crame', '123', 'vincent@gmail.com', '123', 'sparkmobile.com', 'davao city', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'Air Conditioning Equipment Supplier', 'Air Conditioning Units and Cooling Systems', 'asdafer', '123', '2025-07-22', 'Active', 5, '2025-07-22 05:06:58', NULL, NULL, 'notes example'),
(10, 'Ryan Fisher', 'Martin and Sanchez', '09292497885', 'bassmichelle@price.com', '+1-711-868-1052x007', 'https://smith.com', 'Torrejos Compound', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'Air Conditioning Equipment Supplier', 'Air Conditioning Units and Cooling Systems', 'asdafer', '412-746-6533', '2025-07-22', 'Active', 5, '2025-07-22 07:37:23', NULL, NULL, 'Same paper identify would'),
(11, 'Christina Buchanan', 'Rivera-Schaefer', '+1-711-868-1052x007', 'cpatterson@duncan.net', '001-164-001-4521x92928', 'https://smith.com', 'New Calebburgh', 'Davao City', 'Davao del Sur', '8000', 'Philippines', 'Equipment Maintenance Provider', 'Repairs and Maintenance – Equipment and Devices', 'asdfer', '001-164-001-4521x92928', '2025-07-12', 'Active', 5, '2025-07-22 07:40:57', NULL, NULL, 'Example Notes'),
(12, 'Lawson-Gardner', 'Michael Lester', '001-725-932-9718x190', 'colefrancisco@owens.org', '02021', 'https://johnson.com/', 'Manila', 'Bogo City', 'Cebu', '8000', 'Philippines', 'Furniture Supplier', 'Furniture and Fixtures', 'asdafer', '020-260-4945x795', '2025-07-14', 'Active', 5, '2025-07-22 07:44:47', NULL, NULL, 'Example Notes'),
(13, 'Wilkins', 'Wise, Nicholas Martin', '705-889-4513', 'higginskevin@copeland.info', '01884', 'http://parker-jenkins.com', 'Ilocos Norte', 'Laoag City', 'Ilocos Norte', '8000', 'Philippines', 'Laboratory Equipment Supplier', 'Laboratory Equipment', 'asdafer', '5114371031', '2025-07-10', 'Active', 5, '2025-07-22 08:33:27', NULL, NULL, 'Notes Example'),
(14, 'Miller', 'Amy Gonzales', '(850)429-6889x9622', 'tkelley@lee-phillips.com', '769.057.0439', 'http://tran.com/', 'Davao City', 'Santa Cruz', 'Davao del Sur', '8000', 'Philippines', 'Laboratory Equipment Supplier', 'Lab Chemicals and Reagents', '', '769.057.0439', '', '', 5, '2025-07-22 08:40:03', NULL, NULL, 'Example Notes'),
(15, 'George-Sharp', 'John Lewis', '942.669.5581x49035', 'mcguirehannah@jones.org', '33063', 'https://www.mooney-hogan.com/', 'Cebu', 'Tabogon', 'Cebu', '', 'Philippines', 'Construction and Renovation Contractor', 'Contruction Materials', 'asdafer', '378-502-1402', '2025-07-09', 'Active', 5, '2025-07-22 08:41:49', NULL, NULL, 'Example Notes'),
(16, 'Price', 'Patricia Lloyd', '001-448-324-6057x641', 'felicia91@rodriguez.net', '63423', 'http://lambert.com', 'Cebu', 'Naga City', 'Cebu', '8000', 'Philippines', 'Construction and Renovation Contractor', 'Contruction Materials', '', '001-291-752-2359x1807', '2025-07-16', 'Active', 5, '2025-07-22 08:48:34', NULL, NULL, 'Example Notes'),
(17, 'Long and Chase', 'Jesse Sutton', '+1-750-672-0762', 'carol60@miller-olsen.net', '97249', 'https://www.jennings.com', 'a@gmail.com', 'Magsaysay', 'Davao del Sur', '8000', 'Philippines', '', '', '', '123', '', '', 5, '2025-07-22 09:02:20', NULL, NULL, ''),
(18, 'Winters', 'Wendy Warren', '+1-114-330-4636', 'david74@gutierrez.com', '68197', 'https://parker-larson.com', 'Morris Wells Apt', 'Bantayan', 'Cebu', '8000', 'Philippines', 'Construction and Renovation Contractor', 'Renovation Services', '', '001-005-777-1862x491', '2025-07-18', 'Active', 5, '2025-07-22 09:06:25', NULL, NULL, 'Example Notes'),
(19, 'Paul-Tucker', 'Stephanie Baker', '001-614-306-7192x6183', 'sean35@miles.com', '(553)602-0557x9604', 'http://www.patterson.biz', 'Cebu', 'Lapu-Lapu City', 'Cebu', '8000', 'Philippines', 'Construction and Renovation Contractor', 'Renovation Services', '', '(553)602-0557x9604', '2025-07-17', 'Active', 5, '2025-07-22 09:11:44', NULL, NULL, ''),
(20, 'Bennett-Robinson', 'Chase Stanley MD', '115-686-9962', 'jimenezjeffrey@smith-shaffer.biz', '969-887-0411', 'http://www.carter.com', '', 'Argao', 'Cebu', '8000', 'Philippines', 'Machinery and Equipment Supplier', 'Heavy Machinery', '', '115-686-9962', '2025-07-16', 'Active', 5, '2025-07-22 09:13:02', NULL, NULL, 'example notes'),
(21, 'Vstore', 'watson', '1', 'watson@gmail.com', '1', 'sparkmobile.com', 'Davao City', 'Paoay', 'Ilocos Norte', '8000', 'Philippines', '', '', 'asdafer', '1', '2025-07-24', 'Active', 5, '2025-07-24 06:39:06', NULL, NULL, 'notes lamang'),
(22, 'w', 'w', '1', 'w@gmail.com', '1', 'w.com', 'w', 'Paoay', 'Ilocos Norte', '8000', 'Philippines', 'Medical Supplies Provider', 'Medicines', 'asdafer', '1', '2025-07-13', 'Active', 5, '2025-07-24 06:40:23', NULL, NULL, 'notes lamang'),
(23, 'Tfix', 'C', '123', 'c@gmail.com', '123', 'c123.com', 'Davao City', 'Barili', 'Cebu', '8000', 'Philippines', 'Construction and Renovation Contractor', 'Construction Materials', 'asdafer', '123', '2025-07-29', 'Active', 5, '2025-07-29 04:46:46', NULL, NULL, 'notes lamang');

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
  `quantity` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `issued_to_department` int(11) DEFAULT NULL,
  `issued_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_transaction`
--

INSERT INTO `supplier_transaction` (`transaction_id`, `date_received`, `invoice_no`, `sales_type`, `category`, `supplier_id`, `item_description`, `quantity`, `unit`, `unit_price`, `amount`, `status`, `issued_to_department`, `issued_date`) VALUES
(16, '2025-07-21', '123456', 'Cash', 'ICT Equipment and Devices', 7, 'Computer System', 1, 'unit', 1.00, 1.00, 'Pending', NULL, NULL),
(17, '2025-07-21', '123', 'Cash', 'ICT Equipment and Devices', 7, 'example', 1, 'meter', 1.00, 1.00, 'Pending', NULL, NULL),
(18, '2025-07-21', '12345', 'Credit', 'Office Supplies and Materials', 7, 'example2', 1, 'g', 150.00, 150.00, 'Pending', NULL, NULL),
(19, '2025-07-22', '123', 'Cash', 'ICT Equipment and Devices', 8, 'notes example', 10, 'bar', 10.00, 100.00, 'Pending', NULL, NULL),
(20, '2025-07-18', '25007', 'Credit', 'Office Equipment', 12, 'None', 1, 'ton', 11.00, 11.00, 'Pending', NULL, NULL),
(21, '2025-07-16', '36118', 'Credit', 'Laboratory Equipment', 20, 'I need lab equipment ASAP!', 1, 'roll', 100.00, 100.00, 'Pending', NULL, NULL),
(22, '2025-07-16', '1231', 'Cash', 'School Building Improvements', 13, 'Building renovation', 1, 'service', 111.00, 111.00, 'Pending', NULL, NULL),
(23, '2025-07-15', '36118', 'Cash', 'Other Machinery and Equipment', 17, 'Notes', 1, 'g', 111.00, 111.00, 'Pending\n', NULL, NULL),
(24, '2025-07-14', '1564', 'Cash', 'Air Conditioning Units and Cooling Systems', 15, 'asdafer001', 1, 'kg', 12.00, 12.00, 'Pending\n', NULL, NULL),
(25, '2025-07-14', '2345', 'Credit', 'Office Equipment', 18, 'I need gaming chair', 1, 'unit', 444.00, 444.00, 'Pending\n', NULL, NULL),
(26, '2025-07-13', '1564', 'Cash', 'Air Conditioning Units and Cooling Systems', 14, 'i need aircon here', 1, 'pc', 112.00, 112.00, 'Pending', NULL, NULL),
(27, '2025-07-23', '246', 'Cash', 'Electrical and Lighting Supplies', 11, 'I need it now', 1, 'pc', 1.00, 1.00, 'Pending', NULL, NULL),
(28, '2025-07-23', '14', 'Cash', 'Printing and Reproduction Services', 12, 'Printing shop', 11, 'pc', 1.00, 11.00, 'Pending', NULL, NULL),
(29, '2025-08-01', '11', 'Cash', 'ICT Equipment and Devices', 22, 'asdafer', 1, 'pc', 1.00, 1.00, 'Pending', NULL, NULL),
(30, '2025-08-01', 's', 'Cash', 'ICT Equipment and Devices', 14, 's', 11, 'pc', 11.00, 121.00, 'Pending\n', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supply_request`
--

CREATE TABLE `supply_request` (
  `request_id` int(11) NOT NULL,
  `date_requested` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_needed` varchar(255) NOT NULL,
  `department_unit` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `sales_type` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `request_description` varchar(255) NOT NULL,
  `unit_cost` varchar(255) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `quantity_requested` varchar(255) NOT NULL,
  `quality_issued` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_request`
--

INSERT INTO `supply_request` (`request_id`, `date_requested`, `user_id`, `date_needed`, `department_unit`, `purpose`, `sales_type`, `category`, `request_description`, `unit_cost`, `total_cost`, `unit`, `quantity_requested`, `quality_issued`, `amount`) VALUES
(8, '2025-08-02', 0, '2025-08-27', 'HR', 'No Purpose', 'Cash', 'ICT Equipment and Devices', 'No Description', '11', 11.00, 'pc', '1', 'Good', 11.00);

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

--
-- Dumping data for table `transaction_specifications`
--

INSERT INTO `transaction_specifications` (`spec_id`, `transaction_id`, `brand`, `serial_number`, `type`, `size`, `model`, `warranty_info`, `additional_notes`, `created_at`, `updated_at`) VALUES
(1, 26, 'Sonya', 'None', '12134', 'Size', 'model', 'no waranty', 'This is nice', '2025-08-01 06:19:37', '2025-08-02 03:56:29'),
(2, 24, 'sss', 'sss', 'ss', 'ss', 'ss', 'ss', 'sss', '2025-08-01 06:20:22', '2025-08-01 06:20:40');

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
(4, '', 'Erwin', 'Pogi', 'Acedillo', '', 'MIT', 'admin', 'erwinacedillo@gmail.com', '$2y$10$sqVTg7YdLiuS8Jx2bMY.OuEIkudSO/.ToKuU35RkVpmDAY7RWQCr.'),
(8, 'MIS ADMIN', 'Vincent', 'Pogi', 'Crame', 'NONE', 'MIS PROGRAMMER', 'Admin', 'vincentcrame7@gmail.com', '$2y$10$3f03bn5Qjz8Px1.trf2sPefhnZeqShDKwhW50Ivu8OcjlP.1sX5Oe'),
(9, '', 'Staff', 'Staff', 'Staff', '', 'Staff', 'Staff', 'staff@gmail.com', '$2y$10$Ax96ZYijBBqpRMLdwnoOduMZk8GJNe/OEW4FLVQ2177xBT0VFDr4u');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `procurement`
--
ALTER TABLE `procurement`
  MODIFY `procurement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `supply_request`
--
ALTER TABLE `supply_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `supplier_transaction_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`),
  ADD CONSTRAINT `supplier_transaction_ibfk_2` FOREIGN KEY (`issued_to_department`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transaction_specifications`
--
ALTER TABLE `transaction_specifications`
  ADD CONSTRAINT `transaction_specifications_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `supplier_transaction` (`transaction_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
