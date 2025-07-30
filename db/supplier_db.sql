-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 09:08 AM
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
  `product_category` varchar(255) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `tax_identification_number` varchar(100) DEFAULT NULL,
  `date_registered` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `supplier_name`, `contact_person`, `contact_number`, `email_address`, `fax_number`, `website`, `address`, `city`, `province`, `zip_code`, `country`, `business_type`, `product_category`, `payment_terms`, `tax_identification_number`, `date_registered`, `status`, `created_by`, `date_created`, `last_updated_by`, `date_updated`, `notes`) VALUES
(1, 'Erwin', 'dsfsdfsd', '054545454', 'dfsf@gmail.com', '54545454', 'dfdsf', 'fdsf', 'dfdfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdfdfdf', '2025-07-02', 'Active', 1, '0000-00-00 00:00:00', 4, '2025-07-10 07:34:10', 'fdfdfdfd'),
(2, 'Tomas', 'dsfsdfsd', 'dfsdf', 'dfsf@gmail.com', 'dfsdf', 'dfdsf', 'fdsf', 'dfdfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdfdfdf', '2025-07-02', 'Active', 1, '0000-00-00 00:00:00', 4, '2025-07-10 07:35:10', 'fdfdfdfd'),
(3, 'Time', 'dsfsdfsd', 'dfsdf', 'dfsf@gmail.com', 'dfsdf', 'dfdsf', 'fdsf', 'dfdfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdf', 'dfdfdfdf', '2025-07-02', 'Active', 1, '0000-00-00 00:00:00', 4, '2025-07-10 07:35:18', 'fdfdfdfd'),
(4, 'xxxx', 'fdfdf', 'a', 'a@gmail.com', 'dfdf', 'dfdfdf', 'dfdsf', 'fdfd', 'dfdfd', 'dfdfd', 'dfdf', 'fdfdf', 'dfdf', 'dfdfd', 'dfdfd', '2025-07-02', 'Active', 1, '0000-00-00 00:00:00', 1, '2025-07-02 10:52:55', 'dfdfdfdf'),
(5, 'MMM', 'MMM', 'a', 'a@gmail.com', 'dfdf', 'dfdfdf', 'dfdsf', 'fdfd', 'dfdfd', 'dfdfd', 'dfdf', 'fdfdf', 'dfdf', 'dfdfd', 'dfdfd', '2025-07-02', 'Inactive', 1, '0000-00-00 00:00:00', 1, '2025-07-02 10:53:20', 'dfdfdfdf');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transaction`
--

CREATE TABLE `supplier_transaction` (
  `transaction_id` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `sales_type` enum('Cash Sales','Charge Sales') NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_transaction`
--

INSERT INTO `supplier_transaction` (`transaction_id`, `date_received`, `invoice_no`, `sales_type`, `category`, `supplier_id`, `item_description`, `quantity`, `unit`, `unit_price`, `amount`) VALUES
(13, '2025-07-11', '123456', '', 'ICT Equipment and Devices', 1, 'Printers', 3, 'unit', 500.00, 1500.00),
(14, '2025-07-11', '123456', '', 'ICT Equipment and Devices', 1, 'Keyboard', 20, 'unit', 500.00, 10000.00),
(15, '2025-07-11', '564865', '', 'ICT Equipment and Devices', 5, 'Computer ', 50, 'unit', 560.00, 28000.00);

-- --------------------------------------------------------

--
-- Table structure for table `supply_request`
--

CREATE TABLE `supply_request` (
  `request_id` int(11) NOT NULL,
  `date_requested` date NOT NULL,
  `date_needed` date NOT NULL,
  `department_unit` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `sales_type` varchar(50) NOT NULL,
  `category` varchar(255) NOT NULL,
  `request_description` text NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `quality_issued` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_by` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_updated_by` int(11) DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
(4, '', 'Erwin', 'Pogi', 'Acedillo', '', 'MIT', 'admin', 'erwinacedillo@gmail.com', '$2y$10$sqVTg7YdLiuS8Jx2bMY.OuEIkudSO/.ToKuU35RkVpmDAY7RWQCr.');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `supply_request`
--
ALTER TABLE `supply_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `supplier_transaction`
--
ALTER TABLE `supplier_transaction`
  ADD CONSTRAINT `supplier_transaction_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
