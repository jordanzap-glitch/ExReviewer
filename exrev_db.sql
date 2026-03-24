-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 01:44 AM
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
-- Database: `exrev_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_academicyears`
--

CREATE TABLE `tbl_academicyears` (
  `id` int(11) NOT NULL,
  `sy_start` year(4) DEFAULT NULL,
  `sy_end` year(4) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_academicyears`
--

INSERT INTO `tbl_academicyears` (`id`, `sy_start`, `sy_end`, `is_active`) VALUES
(3, '2026', '2027', 0),
(5, '2027', '2028', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attempts`
--

CREATE TABLE `tbl_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `score` int(5) DEFAULT NULL,
  `subjects_id` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attempts`
--

INSERT INTO `tbl_attempts` (`id`, `user_id`, `score`, `subjects_id`, `date_created`) VALUES
(1, 6, 1, 2, '2026-03-23 23:34:19'),
(2, 6, 0, 2, '2026-03-23 23:34:19'),
(3, 6, 1, 2, '2026-03-23 23:34:19'),
(4, 6, 1, 2, '2026-03-23 23:34:41'),
(5, 6, 0, 2, '2026-03-24 00:43:40');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_modules`
--

CREATE TABLE `tbl_modules` (
  `id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `subjects_id` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_modules`
--

INSERT INTO `tbl_modules` (`id`, `file_path`, `subjects_id`, `date_added`) VALUES
(5, 'pages/student/learning_modules/1774266633_test.pdf', 2, '2026-03-23 11:50:33');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_question_bank`
--

CREATE TABLE `tbl_question_bank` (
  `id` int(11) NOT NULL,
  `question` text DEFAULT NULL,
  `opt_a` text DEFAULT NULL,
  `opt_b` text DEFAULT NULL,
  `opt_c` text DEFAULT NULL,
  `opt_d` text DEFAULT NULL,
  `correct_ans` char(1) DEFAULT NULL,
  `subjects_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `academicyears_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_question_bank`
--

INSERT INTO `tbl_question_bank` (`id`, `question`, `opt_a`, `opt_b`, `opt_c`, `opt_d`, `correct_ans`, `subjects_id`, `remarks`, `academicyears_id`) VALUES
(1, 'sadads', 'aasd', 'asd', 'asd', 'asd', 'A', 1, NULL, NULL),
(2, 'asd', 'asd', 'ads', 'ads', 'asd', 'B', 1, NULL, NULL),
(3, 'testting', 'test1', 'test2', 'test3', 'test4', 'B', 1, 'testing remarks', NULL),
(5, 'testting last', 'opt1 worngss', 'hello', 'test', 'test', 'A', 2, 'try', 3),
(6, 'asd', 'asd', 'asd', 'asd', 'asd', 'C', 2, '', 3),
(7, 'testting', 'testingg123', 'tetign', 'qe', 'asd', 'B', 3, 'qwe', 3),
(8, 'question testion', '12', '123', '1235', '1234', 'A', 2, 'hey trial', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sections`
--

CREATE TABLE `tbl_sections` (
  `id` int(11) NOT NULL,
  `name` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_sections`
--

INSERT INTO `tbl_sections` (`id`, `name`) VALUES
(1, 'D'),
(4, 'A');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_subjects`
--

CREATE TABLE `tbl_subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `exam_duration` smallint(6) DEFAULT NULL,
  `question_items` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_subjects`
--

INSERT INTO `tbl_subjects` (`id`, `name`, `code`, `exam_duration`, `question_items`) VALUES
(2, 'calc', 'tnt101', 60, 1),
(3, 'sd', 'asd', NULL, NULL),
(4, 's', 'a', NULL, NULL),
(5, 'testing', 'test101', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `middle_name` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `is_superuser` tinyint(1) DEFAULT NULL,
  `usertypes_id` int(11) DEFAULT NULL,
  `year_level` enum('1','2','3','4','graduate','Graduate') DEFAULT NULL,
  `sections_id` int(11) DEFAULT NULL,
  `academicyears_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `last_name`, `first_name`, `middle_name`, `email`, `password`, `is_superuser`, `usertypes_id`, `year_level`, `sections_id`, `academicyears_id`) VALUES
(1, NULL, NULL, NULL, 'jordan@gmail.com', '123', NULL, 1, NULL, NULL, NULL),
(6, 'student', 'student', '', 'student@gmail.com', '123', NULL, 2, '4', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_usertypes`
--

CREATE TABLE `tbl_usertypes` (
  `id` int(11) NOT NULL,
  `name` enum('teacher','student') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_usertypes`
--

INSERT INTO `tbl_usertypes` (`id`, `name`) VALUES
(1, 'teacher'),
(2, 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_academicyears`
--
ALTER TABLE `tbl_academicyears`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_attempts`
--
ALTER TABLE `tbl_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_modules`
--
ALTER TABLE `tbl_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_question_bank`
--
ALTER TABLE `tbl_question_bank`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_academicyears_id` (`academicyears_id`);

--
-- Indexes for table `tbl_sections`
--
ALTER TABLE `tbl_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_subjects`
--
ALTER TABLE `tbl_subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_usertypes`
--
ALTER TABLE `tbl_usertypes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_academicyears`
--
ALTER TABLE `tbl_academicyears`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_attempts`
--
ALTER TABLE `tbl_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_modules`
--
ALTER TABLE `tbl_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_question_bank`
--
ALTER TABLE `tbl_question_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_sections`
--
ALTER TABLE `tbl_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_subjects`
--
ALTER TABLE `tbl_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_usertypes`
--
ALTER TABLE `tbl_usertypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_question_bank`
--
ALTER TABLE `tbl_question_bank`
  ADD CONSTRAINT `fk_academicyears_id` FOREIGN KEY (`academicyears_id`) REFERENCES `tbl_academicyears` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
