-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 06:03 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `foodwaste`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`) VALUES
(2, 'admin', 'admin@gmail.com', '$2y$10$y.3TM4eKZPQioGeq.4Sj6O3efS/R3wGWXjJoXpvULzXwG2KuFXDjG');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('Fruits and Vegetables','Dairy Products','Meat and Fish','Grains and Cereals','Baked Goods','Prepared Foods','Beverages','Condiments and Sauces') NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `donor` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `category`, `picture`, `donor`, `created_at`, `date_created`, `last_modified`, `expiry_date`, `quantity`) VALUES
(19, '1', 'Fruits and Vegetables', '', 'res1', '2024-10-04 06:52:54', '2024-10-04 06:52:54', '2024-10-04 06:52:54', '2024-10-31', 3);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `restaurant_name` varchar(255) DEFAULT NULL,
  `ngo_name` varchar(255) NOT NULL,
  `requested_quantity` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `approval_date` datetime DEFAULT NULL,
  `receive_method` enum('delivery','pickup') DEFAULT NULL,
  `receive_time` datetime DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `rejection_remark` varchar(255) DEFAULT NULL,
  `delivery_completed` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `id`, `name`, `restaurant_name`, `ngo_name`, `requested_quantity`, `status`, `request_date`, `approval_date`, `receive_method`, `receive_time`, `address`, `rejection_remark`, `delivery_completed`) VALUES
(44, 19, '1', 'res1', 'ngo1', 1, 'approved', '2024-10-15 21:41:55', '2024-10-15 21:42:03', 'delivery', '2024-10-18 12:11:00', '5th Avenue, New York, NY, USA', NULL, 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Restaurant','NGO') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `phone_number`, `password`, `user_type`) VALUES
(6, 'res1', 'res1@gmail.com', '1.1', '$2y$10$49zFF13akGXLfvUUNIUOJ.vdfffPsoXd0fjizD/H6RempYMQSDHl6', 'Restaurant'),
(7, 'res2', 'res2@gmail.com', '1.2', '$2y$10$FqsvHJxn3llxyc3kJC6YjeHKqMm4/QObHouheLsGw1FrpsH4yAQDy', 'Restaurant'),
(8, 'ngo1', 'ngo1@gmail.com', '2.1', '$2y$10$KX21lKFEKGJUcsT4vm3yAO/uGdp5sUp2Rb360zCCdUF5d5CmrNfPW', 'NGO'),
(9, 'ngo2', 'ngo2@gmail.com', '2.2', '$2y$10$88NzwZcXfynkZJRTG/.WIeu2HhIFjGbZyjIVatQmch5gbQMCc6f9S', 'NGO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donor` (`donor`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `id` (`id`),
  ADD KEY `username` (`ngo_name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`donor`) REFERENCES `user` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`ngo_name`) REFERENCES `user` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
