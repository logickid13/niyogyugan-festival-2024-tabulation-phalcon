-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 14, 2024 at 02:41 AM
-- Server version: 10.3.17-MariaDB
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qsadmin_niyogyugan_scoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `float_votes`
--

CREATE TABLE `float_votes` (
  `id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `fullname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `voters_address` varchar(255) NOT NULL,
  `cellphone_number` varchar(11) NOT NULL,
  `facebook_profile` varchar(255) NOT NULL,
  `data_privacy` tinyint(1) NOT NULL COMMENT 'In compliance with the requirements of Republic Act No. 10773 otherwise known as the Data Privacy Act of 2012, the Provincial Tourism commits to ensure that all personal information obtained will be secured and remain confidential. Collected personal information will only be utilized for purpose of validation. The personal information shall not be shared or disclosed with other parties without consent unless the disclosure is required by, or in compliance with applicable laws and regulations.',
  `float_vote_choices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `date_registered` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `float_votes`
--
ALTER TABLE `float_votes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `float_votes`
--
ALTER TABLE `float_votes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
