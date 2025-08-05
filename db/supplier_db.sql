-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2025 at 02:36 AM
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
(25, 'Appliances For All', 'Erwin', '092923145', 'erwin@gmail.com', '123-456-789', 'appliance.com', 'Davao City', 'Lapu-Lapu City', 'Cebu', '8000', 'Philippines', 'IT Equipment Supplier', 'Subscription, License, and Software Services', 'Monthly Cash', '123-456-789', '2025-08-04', 'Active', 9, '2025-08-04 05:20:15', NULL, NULL, 'N/A');

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
  `status` varchar(255) NOT NULL,
  `issued_to_department` varchar(200) DEFAULT NULL,
  `issued_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_transaction`
--

INSERT INTO `supplier_transaction` (`transaction_id`, `date_received`, `invoice_no`, `sales_type`, `category`, `supplier_id`, `item_description`, `brand`, `type`, `color`, `quantity`, `unit`, `unit_price`, `amount`, `status`, `issued_to_department`, `issued_date`) VALUES
(31, '2025-08-04', '12345', 'Cash', 'ICT Equipment and Devices', 25, '5G internet and gaming chair', NULL, NULL, NULL, 1, 'pc', 100.00, 100.00, 'Pending', NULL, NULL),
(32, '2025-08-04', '12', 'Cash', 'School Building Improvements', 25, 'Paint', 'Boysen', 'Paint', 'RGB', 1, 'can', 150.00, 150.00, 'Pending', NULL, NULL);

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
  `unit_cost` varchar(255) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `quantity_requested` varchar(255) NOT NULL,
  `quality_issued` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `noted_by` varchar(255) DEFAULT NULL,
  `checked_by` varchar(255) DEFAULT NULL,
  `verified_by` varchar(255) DEFAULT NULL,
  `approved_by` varchar(255) DEFAULT NULL,
  `issued_by` varchar(255) DEFAULT NULL,
  `date_released` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_request`
--

INSERT INTO `supply_request` (`request_id`, `user_id`, `date_requested`, `date_needed`, `department_unit`, `purpose`, `sales_type`, `category`, `request_description`, `unit_cost`, `total_cost`, `unit`, `quantity_requested`, `quality_issued`, `amount`, `noted_by`, `checked_by`, `verified_by`, `approved_by`, `issued_by`, `date_released`) VALUES
(8, 0, '2025-08-02', '2025-08-27', 'HR', 'No Purpose', 'Cash', 'ICT Equipment and Devices', 'No Description', '11', 11.00, 'pc', '1', 'Good', 11.00, '', '', '', '', '', ''),
(9, 9, '2025-08-04', '2025-08-06', 'MIS', 'Is to have a smooth gaming experience', 'Cash', 'Office Equipment', 'Gaming Chair and 5G internet', '100', 100.00, 'pc', '1', 'Good', 100.00, '', '', '', '', '', ''),
(10, 12, '2025-08-05', '2025-08-08', 'Faculty', 'To have a better gaming experience', 'Cash', 'Office Equipment', 'Gaming Chair', '100', 100.00, 'pc', '1', 'I hope it is good', 100.00, '', '', '', '', '', '');

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
(4, '', 'Erwin', 'Pogi', 'Acedillo', '', 'MIT', 'admin', 'erwinacedillo@gmail.com', '$2y$10$sqVTg7YdLiuS8Jx2bMY.OuEIkudSO/.ToKuU35RkVpmDAY7RWQCr.'),
(8, 'MIS ADMIN', 'Vincent', 'Pogi', 'Crame', 'NONE', 'MIS PROGRAMMER', 'Admin', 'vincentcrame7@gmail.com', '$2y$10$QhG8ni2hkHR9F8FZ6xIbouic51sVQgAV/Li3ibJVIHz6ElL01HiPi'),
(9, 'staff', 'Staff', 'Staff', 'Staff', 'staff', 'Staff', 'Staff', 'staff@gmail.com', '$2y$10$Ax96ZYijBBqpRMLdwnoOduMZk8GJNe/OEW4FLVQ2177xBT0VFDr4u'),
(11, 'Dr', 'Robert', '', 'James', 'N/A', 'Ph.D', 'School President', 'robert@gmail.com', '$2y$10$VYwZKAJTXrGQsZ9f5rVdMuprkCHYM5yDBROLG4b0D78KEfTyOSNsm'),
(12, 'Dr', 'June Carl', '', 'Echavia', 'N/A', 'Teacher', 'Faculty', 'junecarl@gmail.com', '$2y$10$eCxmouJR3YSa1HPGz7NAM.u5UXUZuMOD/sn9sSY4.M9BNff8r1hY.'),
(13, '', 'Maria', '', 'Amor', '', '', 'Immediate Head', 'maria@gmail.com', '$2y$10$SbF0o6H2YlbpZTONszE25.8uXRLonPewEzshwFKf3K0L.KeRZBPny'),
(14, '', 'Jane', '', 'Doe', '', '', 'Supply In-charge', 'jane@gmail.com', '$2y$10$xcXtjDe//VUPb0YNFX3nn.qaallQC.sS3Qrgrf04takFkqSz.g/ha'),
(15, '', 'Emily', '', 'Charles', '', '', 'Purchasing Officer', 'emily@gmail.com', '$2y$10$e3OrYs2.Z6tqN4m0tjalfuP7.2xzyH61sE8iyLr.36xzz3NCWjgJO'),
(16, 'Dr.', 'Cinna', '', 'Rose', 'N/A', 'M.D', 'VP for Finance & Administration', 'cinna@gmail.com', '$2y$10$l4seTrgPgUaBLoz9rXgx7.cjQjwaQOi8s3JaB3hdt/1xZfKxBNgqO');

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
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `supply_request`
--
ALTER TABLE `supply_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
