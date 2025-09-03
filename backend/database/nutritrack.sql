-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 03, 2025 at 03:37 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nutritrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_announcements`
--

CREATE TABLE `tbl_announcements` (
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `img_content` varchar(255) DEFAULT NULL,
  `post_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_announcements`
--

INSERT INTO `tbl_announcements` (`announcement_id`, `user_id`, `zone_id`, `content`, `img_content`, `post_date`) VALUES
(1, 1001, 1, 'Reminder: Monthly nutrition monitoring scheduled for February 15, 2024. All parents are encouraged to bring their children.', NULL, '2024-02-10 08:00:00'),
(2, 1002, 2, 'Vaccination drive on February 20, 2024. Please bring vaccination cards and birth certificates.', NULL, '2024-02-15 10:30:00'),
(3, 1003, 3, 'Health education seminar for parents on February 25, 2024 at 2:00 PM. Topics include proper nutrition for children.', NULL, '2024-02-20 14:15:00'),
(4, 1001, NULL, 'New feeding program launching March 1, 2024. Malnourished children will receive supplementary nutrition.', NULL, '2024-02-25 09:45:00'),
(5, 1005, 5, 'Growth monitoring session on March 10, 2024. Early detection of malnutrition is crucial for child development.', NULL, '2024-03-05 11:20:00'),
(6, 1007, 7, 'Community Health Fair on March 20, 2024. Free consultations and health screening available.', NULL, '2024-03-15 16:30:00'),
(7, 1002, 8, 'Catch-up immunization program for children with incomplete vaccines. Schedule: March 25, 2024.', NULL, '2024-03-20 08:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_audit_log`
--

CREATE TABLE `tbl_audit_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `module_accessed` varchar(100) DEFAULT NULL,
  `log_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_audit_log`
--

INSERT INTO `tbl_audit_log` (`log_id`, `user_id`, `activity_type`, `module_accessed`, `log_date`) VALUES
(1, 1001, 'Login', 'Authentication', '2024-01-15 08:00:00'),
(2, 1002, 'Child Registration', 'Child Management', '2024-01-15 08:30:00'),
(3, 1002, 'Nutrition Record Entry', 'Nutrition Tracking', '2024-01-20 09:00:00'),
(4, 1003, 'Child Registration', 'Child Management', '2024-01-16 10:00:00'),
(5, 1001, 'Report Generation', 'Reporting', '2024-01-25 10:00:00'),
(6, 1005, 'Vaccine Administration', 'Immunization', '2024-01-21 11:00:00'),
(7, 1007, 'Event Creation', 'Event Management', '2024-02-01 09:30:00'),
(8, 1001, 'Announcement Post', 'Communication', '2024-02-10 08:00:00'),
(9, 1002, 'Data Update', 'Child Management', '2024-02-15 14:20:00'),
(10, 1003, 'System Access', 'Authentication', '2024-02-20 07:45:00'),
(11, 1005, 'Record Flagging', 'Quality Control', '2024-01-22 12:00:00'),
(12, 1001, 'User Management', 'Administration', '2024-02-25 16:15:00'),
(13, 1007, 'Logout', 'Authentication', '2024-02-28 17:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_barangay`
--

CREATE TABLE `tbl_barangay` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(60) NOT NULL,
  `purok_number` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_barangay`
--

INSERT INTO `tbl_barangay` (`zone_id`, `zone_name`, `purok_number`) VALUES
(1, 'Minuya Proper', 'Purok 1'),
(2, 'Minuya Proper', 'Purok 2'),
(3, 'Minuya Proper', 'Purok 3'),
(4, 'Minuya Proper', 'Purok 4'),
(5, 'Minuya Proper', 'Purok 5'),
(6, 'Minuya Proper', 'Purok 6'),
(7, 'Minuya Proper', 'Purok 7'),
(8, 'Minuya Proper', 'Purok 8');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_child`
--

CREATE TABLE `tbl_child` (
  `child_id` int(11) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `registered_by` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_child`
--

INSERT INTO `tbl_child` (`child_id`, `zone_id`, `registered_by`, `first_name`, `last_name`, `birthdate`, `gender`, `created_at`) VALUES
(1, 1, 1000, 'Ivan', 'Inocencio', '2016-04-02', 'Male', '2025-09-02 15:12:05'),
(2, 1, 1002, 'Miguel', 'Santos', '2020-03-15', 'Male', '2024-01-15 00:30:00'),
(3, 1, 1002, 'Sofia', 'Garcia', '2019-07-22', 'Female', '2024-01-15 01:15:00'),
(4, 2, 1003, 'Carlos', 'Rodriguez', '2021-01-10', 'Male', '2024-01-16 02:00:00'),
(5, 3, 1005, 'Isabella', 'Martinez', '2020-11-05', 'Female', '2024-01-16 03:30:00'),
(6, 3, 1005, 'Diego', 'Lopez', '2018-09-18', 'Male', '2024-01-17 00:45:00'),
(7, 4, 1007, 'Valentina', 'Hernandez', '2021-05-30', 'Female', '2024-01-17 06:20:00'),
(8, 5, 1002, 'Sebastian', 'Gonzalez', '2019-12-12', 'Male', '2024-01-18 01:30:00'),
(9, 6, 1003, 'Camila', 'Perez', '2020-08-07', 'Female', '2024-01-18 07:45:00'),
(10, 7, 1005, 'Mateo', 'Torres', '2021-02-28', 'Male', '2024-01-19 02:15:00'),
(11, 8, 1007, 'Lucia', 'Flores', '2019-04-14', 'Female', '2024-01-19 05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_events`
--

CREATE TABLE `tbl_events` (
  `event_id` int(11) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `event_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_events`
--

INSERT INTO `tbl_events` (`event_id`, `zone_id`, `title`, `description`, `event_date`) VALUES
(1, 1, 'Nutrition Monitoring Day', 'Monthly nutrition assessment and monitoring for all children in Purok 1', '2025-10-15 08:00:00'),
(2, 2, 'Vaccination Drive', 'Complete vaccination program for children aged 0-5 years', '2025-10-20 09:00:00'),
(3, 3, 'Health Education Seminar', 'Seminar on proper nutrition and child care for parents', '2025-11-25 14:00:00'),
(4, 4, 'Feeding Program Launch', 'Launch of supplementary feeding program for malnourished children', '2024-03-01 10:00:00'),
(5, 5, 'Growth Monitoring', 'Regular growth monitoring and assessment session', '2024-03-10 08:30:00'),
(6, 1, 'Parent Education Workshop', 'Workshop on identifying malnutrition signs in children', '2024-03-15 13:00:00'),
(7, 7, 'Community Health Fair', 'Health fair with free check-ups and consultations', '2024-03-20 07:00:00'),
(8, 8, 'Immunization Catch-up', 'Catch-up immunization for children with incomplete vaccines', '2024-03-25 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_flagged_record`
--

CREATE TABLE `tbl_flagged_record` (
  `flagged_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `issue_type` varchar(100) DEFAULT NULL,
  `date_flagged` datetime DEFAULT current_timestamp(),
  `flagged_status` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_flagged_record`
--

INSERT INTO `tbl_flagged_record` (`flagged_id`, `child_id`, `issue_type`, `date_flagged`, `flagged_status`) VALUES
(1, 1, 'Underweight', '2024-01-21 10:00:00', 'Active'),
(2, 2, 'Overweight', '2025-01-22 12:00:00', 'Active'),
(3, 3, 'Underweight', '2025-01-22 16:00:00', 'Under Review'),
(4, 4, 'Severely Underweight', '2024-01-24 11:00:00', 'Active'),
(5, 5, 'Incomplete Vaccination', '2024-01-25 09:30:00', 'Resolved');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nutrition_status`
--

CREATE TABLE `tbl_nutrition_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_nutrition_status`
--

INSERT INTO `tbl_nutrition_status` (`status_id`, `status_name`) VALUES
(1, 'Healthy'),
(2, 'Underweight'),
(3, 'Severely Underweight'),
(4, 'Overweight');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nutritrion_record`
--

CREATE TABLE `tbl_nutritrion_record` (
  `nutrition_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `bmi` float DEFAULT NULL,
  `date_recorded` datetime DEFAULT current_timestamp(),
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_nutritrion_record`
--

INSERT INTO `tbl_nutritrion_record` (`nutrition_id`, `child_id`, `recorded_by`, `weight`, `height`, `bmi`, `date_recorded`, `status_id`) VALUES
(1, 1, 1002, 14.5, 95, 16.1, '2024-01-20 09:00:00', 1),
(2, 2, 1002, 12.8, 88.5, 16.3, '2024-01-20 09:30:00', 2),
(3, 3, 1003, 11.2, 82, 16.7, '2024-01-21 10:00:00', 1),
(4, 4, 1005, 13.8, 91.5, 16.5, '2024-01-21 11:00:00', 1),
(5, 5, 1005, 20.5, 110, 16.9, '2024-01-22 08:30:00', 4),
(6, 6, 1007, 10.8, 78.5, 17.5, '2024-01-22 14:00:00', 2),
(7, 7, 1002, 15.2, 96.8, 16.2, '2024-01-23 09:15:00', 1),
(8, 8, 1003, 14, 94.2, 15.8, '2024-01-23 15:30:00', 1),
(9, 9, 1005, 9.8, 75, 17.4, '2024-01-24 10:45:00', 3),
(10, 10, 1007, 16.8, 102.5, 16, '2024-01-24 13:15:00', 1),
(11, 1, 1002, 14.8, 95.5, 16.2, '2024-02-20 09:00:00', 1),
(12, 2, 1002, 13.1, 89, 16.5, '2024-02-20 09:30:00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_report`
--

CREATE TABLE `tbl_report` (
  `report_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `report_type` varchar(255) NOT NULL,
  `report_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_report`
--

INSERT INTO `tbl_report` (`report_id`, `child_id`, `generated_by`, `report_type`, `report_date`) VALUES
(1, 2, 1001, 'Malnutrition Assessment Report', '2024-01-25 10:00:00'),
(2, 5, 1001, 'Growth Monitoring Report', '2024-01-25 11:30:00'),
(3, 6, 1005, 'Nutrition Status Report', '2024-01-26 09:15:00'),
(4, 9, 1001, 'Severe Malnutrition Alert Report', '2024-01-26 14:20:00'),
(5, 1, 1002, 'Monthly Progress Report', '2024-02-01 08:30:00'),
(6, 3, 1003, 'Vaccination Compliance Report', '2024-02-01 10:45:00'),
(7, 4, 1005, 'Health Status Summary', '2024-02-02 13:15:00'),
(8, 7, 1002, 'Nutrition Improvement Report', '2024-02-02 15:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_roles`
--

CREATE TABLE `tbl_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_roles`
--

INSERT INTO `tbl_roles` (`role_id`, `role_name`) VALUES
(1, 'User'),
(2, 'Admin'),
(3, 'Barangay Health Worker');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `role_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `username`, `password`, `status`, `role_id`) VALUES
(1000, 'admin', '$2y$10$fie7pdiAWDbVe/1Gm3mj.uWdNKmQtB5MIZcyGKV9IrzOIdeNjrak.', 'Active', 2),
(1001, 'admin_maria', '$2y$10$IR5Fye.FdPqYEg0eQNTubOa2SG75NlU7wax/dtM8Jfur3yufIyhae', 'Active', 2),
(1002, 'bhw_juan', '$2y$10$k/nmvGf9Zw0da0n7p/7oY.aKSbhTw6DcLf1w523jm/EPNstWaa0he', 'Active', 3),
(1003, 'bhw_ana', '$2y$10$K6kY0yWvcdB46QEn4SH9t.PWAdqCkmRwAdW79yDaaIWPvEqdUHu8q', 'Active', 3),
(1004, 'user_pedro', '$2y$10$OBQFMqUOVDOEJp5WexL8COycPfD.OQ.5QNoUNNlA1dtFDaVlB4TsC', 'Active', 1),
(1005, 'bhw_rosa', '$2y$10$xyFRDMQO/5JKZraTfpIFFuCfOwuCUCRUjIFB9M/bbJSn5pnsh7hEm', 'Active', 3),
(1006, 'user_linda', '$2y$10$xLOgcxt8H7neNp4lzD2Kqen1NO79N3b3G32.NZ/Txqrkcb5oAapma', 'Active', 1),
(1007, 'bhw_carlos', '$2y$10$ScXbB0gxOBzZsttaGJ5WauW0lsMGZqfGIweSoGV6vwHESTWkqETjK', 'Active', 3),
(1008, 'user_sofia', '$2y$10$zt86.IUTWqO4bOCM64KuO.UciR/pwHfATJ6zqxjeeOnAf3ti6L5Ae', 'Active', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_vaccine_record`
--

CREATE TABLE `tbl_vaccine_record` (
  `vaccine_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `administered_by` int(11) DEFAULT NULL,
  `vaccine_name` varchar(100) DEFAULT NULL,
  `vaccine_status` enum('Completed','Ongoing','Incomplete') NOT NULL DEFAULT 'Ongoing',
  `vaccine_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_vaccine_record`
--

INSERT INTO `tbl_vaccine_record` (`vaccine_id`, `child_id`, `administered_by`, `vaccine_name`, `vaccine_status`, `vaccine_date`) VALUES
(1, 1, 1002, 'BCG', 'Completed', '2020-03-20 10:00:00'),
(2, 1, 1002, 'Hepatitis B', 'Completed', '2020-04-15 10:30:00'),
(3, 1, 1002, 'DPT-1', 'Completed', '2020-05-15 11:00:00'),
(4, 2, 1002, 'BCG', 'Completed', '2019-08-01 09:00:00'),
(5, 2, 1002, 'Hepatitis B', 'Completed', '2019-09-01 09:30:00'),
(6, 3, 1003, 'BCG', 'Completed', '2021-02-10 10:15:00'),
(7, 3, 1003, 'Hepatitis B', 'Ongoing', '2021-03-10 10:45:00'),
(8, 4, 1005, 'BCG', 'Completed', '2020-12-05 11:30:00'),
(9, 4, 1005, 'DPT-1', 'Completed', '2021-01-05 12:00:00'),
(10, 5, 1005, 'BCG', 'Completed', '2018-10-18 08:30:00'),
(11, 5, 1005, 'Hepatitis B', 'Completed', '2018-11-18 09:00:00'),
(12, 6, 1007, 'BCG', 'Ongoing', '2021-06-30 14:00:00'),
(13, 7, 1002, 'BCG', 'Completed', '2020-01-12 10:00:00'),
(14, 8, 1003, 'Hepatitis B', 'Completed', '2020-09-07 15:30:00'),
(15, 9, 1005, 'BCG', 'Incomplete', '2021-03-28 11:15:00'),
(16, 10, 1007, 'DPT-1', 'Completed', '2019-05-14 14:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_announcements`
--
ALTER TABLE `tbl_announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `fk_zoneId_anc` (`zone_id`);

--
-- Indexes for table `tbl_audit_log`
--
ALTER TABLE `tbl_audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_userid_log` (`user_id`);

--
-- Indexes for table `tbl_barangay`
--
ALTER TABLE `tbl_barangay`
  ADD PRIMARY KEY (`zone_id`);

--
-- Indexes for table `tbl_child`
--
ALTER TABLE `tbl_child`
  ADD PRIMARY KEY (`child_id`),
  ADD KEY `fk_registered_by` (`registered_by`),
  ADD KEY `fk_zone_id` (`zone_id`);

--
-- Indexes for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_zoneId` (`zone_id`);

--
-- Indexes for table `tbl_flagged_record`
--
ALTER TABLE `tbl_flagged_record`
  ADD PRIMARY KEY (`flagged_id`),
  ADD KEY `fk_childid_flagged` (`child_id`);

--
-- Indexes for table `tbl_nutrition_status`
--
ALTER TABLE `tbl_nutrition_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `tbl_nutritrion_record`
--
ALTER TABLE `tbl_nutritrion_record`
  ADD PRIMARY KEY (`nutrition_id`),
  ADD KEY `fk_status_id` (`status_id`),
  ADD KEY `fk_childId_nutrition` (`child_id`),
  ADD KEY `fk_userid_nutrition` (`recorded_by`);

--
-- Indexes for table `tbl_report`
--
ALTER TABLE `tbl_report`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_childId_report` (`child_id`),
  ADD KEY `fk_userId_report` (`generated_by`);

--
-- Indexes for table `tbl_roles`
--
ALTER TABLE `tbl_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- Indexes for table `tbl_vaccine_record`
--
ALTER TABLE `tbl_vaccine_record`
  ADD PRIMARY KEY (`vaccine_id`),
  ADD KEY `fk_childId_vaccine` (`child_id`),
  ADD KEY `fk_userId_vaccine` (`administered_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_announcements`
--
ALTER TABLE `tbl_announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_audit_log`
--
ALTER TABLE `tbl_audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_barangay`
--
ALTER TABLE `tbl_barangay`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_child`
--
ALTER TABLE `tbl_child`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_events`
--
ALTER TABLE `tbl_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_flagged_record`
--
ALTER TABLE `tbl_flagged_record`
  MODIFY `flagged_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_nutrition_status`
--
ALTER TABLE `tbl_nutrition_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_nutritrion_record`
--
ALTER TABLE `tbl_nutritrion_record`
  MODIFY `nutrition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_report`
--
ALTER TABLE `tbl_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_roles`
--
ALTER TABLE `tbl_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1009;

--
-- AUTO_INCREMENT for table `tbl_vaccine_record`
--
ALTER TABLE `tbl_vaccine_record`
  MODIFY `vaccine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_announcements`
--
ALTER TABLE `tbl_announcements`
  ADD CONSTRAINT `fk_zoneId_anc` FOREIGN KEY (`zone_id`) REFERENCES `tbl_barangay` (`zone_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_audit_log`
--
ALTER TABLE `tbl_audit_log`
  ADD CONSTRAINT `fk_userid_log` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_child`
--
ALTER TABLE `tbl_child`
  ADD CONSTRAINT `fk_registered_by` FOREIGN KEY (`registered_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_zone_id` FOREIGN KEY (`zone_id`) REFERENCES `tbl_barangay` (`zone_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD CONSTRAINT `fk_zoneId` FOREIGN KEY (`zone_id`) REFERENCES `tbl_barangay` (`zone_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_flagged_record`
--
ALTER TABLE `tbl_flagged_record`
  ADD CONSTRAINT `fk_childid_flagged` FOREIGN KEY (`child_id`) REFERENCES `tbl_child` (`child_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_nutritrion_record`
--
ALTER TABLE `tbl_nutritrion_record`
  ADD CONSTRAINT `fk_childId_nutrition` FOREIGN KEY (`child_id`) REFERENCES `tbl_child` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_status_id` FOREIGN KEY (`status_id`) REFERENCES `tbl_nutrition_status` (`status_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_userid_nutrition` FOREIGN KEY (`recorded_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_report`
--
ALTER TABLE `tbl_report`
  ADD CONSTRAINT `fk_childId_report` FOREIGN KEY (`child_id`) REFERENCES `tbl_child` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_userId_report` FOREIGN KEY (`generated_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `tbl_roles` (`role_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_vaccine_record`
--
ALTER TABLE `tbl_vaccine_record`
  ADD CONSTRAINT `fk_childId_vaccine` FOREIGN KEY (`child_id`) REFERENCES `tbl_child` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_userId_vaccine` FOREIGN KEY (`administered_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
