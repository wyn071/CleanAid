-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 05:28 PM
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
-- Database: `cleanaid`
--

-- --------------------------------------------------------

--
-- Table structure for table `beneficiary`
--

CREATE TABLE `beneficiary` (
  `beneficiary_id` int(11) NOT NULL,
  `list_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `ext_name` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `beneficiarylist`
--

CREATE TABLE `beneficiarylist` (
  `list_id` int(11) NOT NULL,
  `date_submitted` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duplicaterecord`
--

CREATE TABLE `duplicaterecord` (
  `duplicate_id` int(11) NOT NULL,
  `beneficiary_id` int(11) DEFAULT NULL,
  `processing_id` int(11) DEFAULT NULL,
  `flagged_reason` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `processing_engine`
--

CREATE TABLE `processing_engine` (
  `processing_id` int(11) NOT NULL,
  `list_id` int(11) DEFAULT NULL,
  `processing_date` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `role`, `email`, `password`) VALUES
(1, 'kjtiempo', 'ADMIN', 'kjtiempo@dswd.gov.ph', 'kjtiempo123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `beneficiary`
--
ALTER TABLE `beneficiary`
  ADD PRIMARY KEY (`beneficiary_id`),
  ADD KEY `list_id` (`list_id`);

--
-- Indexes for table `beneficiarylist`
--
ALTER TABLE `beneficiarylist`
  ADD PRIMARY KEY (`list_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `duplicaterecord`
--
ALTER TABLE `duplicaterecord`
  ADD PRIMARY KEY (`duplicate_id`),
  ADD KEY `beneficiary_id` (`beneficiary_id`),
  ADD KEY `processing_id` (`processing_id`);

--
-- Indexes for table `processing_engine`
--
ALTER TABLE `processing_engine`
  ADD PRIMARY KEY (`processing_id`),
  ADD KEY `list_id` (`list_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `beneficiary`
--
ALTER TABLE `beneficiary`
  MODIFY `beneficiary_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `beneficiarylist`
--
ALTER TABLE `beneficiarylist`
  MODIFY `list_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duplicaterecord`
--
ALTER TABLE `duplicaterecord`
  MODIFY `duplicate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `processing_engine`
--
ALTER TABLE `processing_engine`
  MODIFY `processing_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `beneficiary`
--
ALTER TABLE `beneficiary`
  ADD CONSTRAINT `beneficiary_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `beneficiarylist` (`list_id`);

--
-- Constraints for table `beneficiarylist`
--
ALTER TABLE `beneficiarylist`
  ADD CONSTRAINT `beneficiarylist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `duplicaterecord`
--
ALTER TABLE `duplicaterecord`
  ADD CONSTRAINT `duplicaterecord_ibfk_1` FOREIGN KEY (`beneficiary_id`) REFERENCES `beneficiary` (`beneficiary_id`),
  ADD CONSTRAINT `duplicaterecord_ibfk_2` FOREIGN KEY (`processing_id`) REFERENCES `processing_engine` (`processing_id`);

--
-- Constraints for table `processing_engine`
--
ALTER TABLE `processing_engine`
  ADD CONSTRAINT `processing_engine_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `beneficiarylist` (`list_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
