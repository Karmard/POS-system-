-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2024 at 04:08 PM
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
-- Database: `pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `item_id` int(11) NOT NULL,
  `dish_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`item_id`, `dish_name`, `price`, `description`, `image_path`) VALUES
(10, 'Fried plantain', 1600.00, 'Sweet and sour', '../uploads/plant.webp'),
(15, 'Pounded Yam', 400.00, 'Cassava Ugali', '../uploads/pondo.webp'),
(16, 'Jollof Rice', 800.00, 'Nigerian Pilau', '../uploads/jollof.jpg'),
(17, 'Egusi soup', 1500.00, 'Grounded pumpkin leaves with spinach', '../uploads/egusi.jpg'),
(18, 'Ogbono soup', 1350.00, 'Grinded slimy bamia', '../uploads/ogbono.jpg'),
(19, 'Dawa Juice', 800.00, 'Blended honey, ginger and lime', '../uploads/dawa.jpg'),
(20, 'Fried Rice', 1000.00, 'jefhwfb', '../uploads/fried.jpg'),
(21, 'Eba', 450.00, 'Yellow ', '../uploads/eba.jpg'),
(22, 'Chin Chin', 450.00, 'Small mandazis', '../uploads/chin.webp'),
(23, 'Okra soup', 2000.00, 'jdhvkjwvf', '../uploads/okra.webp');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
