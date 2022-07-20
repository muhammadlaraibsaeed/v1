-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2022 at 07:12 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `id` bigint(20) NOT NULL COMMENT 'Use As F key',
  `classname` varchar(100) COLLATE sjis_bin NOT NULL DEFAULT 'Class One' COMMENT 'Name Much be Unit'
) ENGINE=InnoDB DEFAULT CHARSET=sjis COLLATE=sjis_bin COMMENT='Add Class Name ';

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `classname`) VALUES
(1, 'Class One'),
(2, 'Class two');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `id` bigint(20) NOT NULL,
  `sname` varchar(100) COLLATE sjis_bin NOT NULL COMMENT 'use for section name',
  `classid` bigint(100) NOT NULL COMMENT 'Use class(t) id as Fk'
) ENGINE=InnoDB DEFAULT CHARSET=sjis COLLATE=sjis_bin COMMENT='For Create section of class';

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE sjis_bin NOT NULL DEFAULT 'undefined' COMMENT 'Student Name',
  `fname` varchar(100) COLLATE sjis_bin NOT NULL COMMENT 'Father Name',
  `age` bigint(20) NOT NULL DEFAULT 18 COMMENT 'Age',
  `birthday` date NOT NULL DEFAULT current_timestamp() COMMENT 'Date of Birth',
  `classid` bigint(100) NOT NULL COMMENT 'class name ',
  `sectionid` bigint(20) NOT NULL COMMENT 'Section name'
) ENGINE=InnoDB DEFAULT CHARSET=sjis COLLATE=sjis_bin COMMENT='Student Info';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE` (`classname`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_class_id` (`classid`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_class_id2` (`classid`),
  ADD KEY `fk_sec_id` (`sectionid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Use As F key', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `fk_class_id` FOREIGN KEY (`classid`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_class_id2` FOREIGN KEY (`classid`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sec_id` FOREIGN KEY (`sectionid`) REFERENCES `section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
