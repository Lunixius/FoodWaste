-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2024 at 06:20 PM
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
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `category`, `picture`, `donor`, `date_created`, `last_modified`, `expiry_date`, `quantity`) VALUES
(21, 'Cola 1L', 'Beverages', 'Vought International Baseball logo.jpg', 'res1', '2024-10-17 17:21:57', '2024-10-17 17:21:57', '2024-10-19', 5),
(24, '324234234', 'Condiments and Sauces', '', 'res1', '2024-10-25 07:35:15', '2024-10-25 07:37:52', '2024-10-31', 3),
(25, '78', 'Meat and Fish', '', 'res1', '2024-10-25 07:49:03', '2024-10-25 07:57:24', '2024-11-01', 3);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('user','admin') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `receiver_type`, `subject`, `message`, `timestamp`, `is_read`, `attachment`) VALUES
(23, 6, 8, 'user', '', 'what', '2024-10-26 15:23:42', 0, NULL),
(25, 6, 8, 'user', '', 'yes?', '2024-10-26 15:29:05', 0, NULL),
(31, 6, 8, 'user', '', 'wer', '2024-10-26 15:38:09', 0, NULL),
(33, 6, 8, 'user', '', 'wrqrqrq', '2024-10-26 15:41:08', 0, NULL),
(35, 6, 8, 'user', '', 'Huh?', '2024-10-26 15:56:11', 0, NULL),
(42, 6, 8, 'user', '', '45', '2024-10-26 16:02:11', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_change_requests`
--

CREATE TABLE `password_change_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `new_password` varchar(255) NOT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('Fruits and Vegetables','Dairy Products','Meat and Fish','Grains and Cereals','Baked Goods','Prepared Foods','Beverages','Condiments and Sauces') DEFAULT NULL,
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

INSERT INTO `requests` (`request_id`, `id`, `name`, `category`, `restaurant_name`, `ngo_name`, `requested_quantity`, `status`, `request_date`, `approval_date`, `receive_method`, `receive_time`, `address`, `rejection_remark`, `delivery_completed`) VALUES
(55, 21, 'Cola 1L', 'Beverages', 'res1', 'ngo1', 1, 'approved', '2024-10-18 02:54:05', '2024-10-18 03:01:13', 'delivery', '2024-10-18 16:59:00', '1 Sierpnia 8, Warsaw, Poland', NULL, 'completed'),
(56, 24, '324234234', 'Condiments and Sauces', 'res1', 'ngo1', 3, 'approved', '2024-10-25 15:35:35', '2024-10-25 15:36:01', 'pickup', '2024-10-31 16:38:00', '5th Avenue, New York, NY, USA', NULL, 'completed'),
(57, 25, '78', 'Meat and Fish', 'res1', 'ngo2', 2, 'approved', '2024-10-25 15:56:42', '2024-10-25 15:57:36', 'delivery', '2024-12-25 15:59:00', 'Sanayi, 41 Burda AVM, Ömer Türkçakal Bulvarı, İzmit/Kocaeli, Türkiye', NULL, 'completed');

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
(6, 'res1', 'res1@gmail.com', '1.1', '$2y$10$WqGIXgBUmvvUmzPoG3cwxepkUrMAXJ0.pfbkJh2vcsFwN9RgZAmlm', 'Restaurant'),
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
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `password_change_requests`
--
ALTER TABLE `password_change_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `username` (`ngo_name`),
  ADD KEY `fk_inventory` (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `password_change_requests`
--
ALTER TABLE `password_change_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

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
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_inventory` FOREIGN KEY (`id`) REFERENCES `inventory` (`id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`ngo_name`) REFERENCES `user` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
