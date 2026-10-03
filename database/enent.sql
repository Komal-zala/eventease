-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2026 at 01:35 PM
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
-- Database: `enent`
--

-- --------------------------------------------------------

-- 1. Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
   
    password VARCHAR(255) NOT NULL,
   
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


--
-- Table structure for table `events`
--

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    venue VARCHAR(255) NOT NULL,
    organizer VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 100,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image VARCHAR(255) DEFAULT NULL,
    registration_open TINYINT(1) NOT NULL DEFAULT 1,
    registration_deadline DATETIME DEFAULT NULL,
    status ENUM(
        'draft',
        'published',
        'cancelled',
        'completed'
    ) NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);
-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `registration_code` varchar(50) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `registration_code`, `qr_code`, `registered_at`) VALUES
(2, 4, NULL, 'EVT-2026-6C1DAB76', 'EVT-2026-6C1DAB76.svg', '2026-10-02 09:51:10'),
(3, 5, NULL, 'EVT-2026-14BF8361', 'EVT-2026-14BF8361.svg', '2026-10-02 09:59:16'),
(4, 6, NULL, 'EVT-2026-BFCA62B4', 'EVT-2026-BFCA62B4.svg', '2026-10-02 10:01:26'),
(5, 7, NULL, 'EVT-2026-F9E8CA7C', 'EVT-2026-F9E8CA7C.svg', '2026-10-02 10:04:16'),
(6, 8, NULL, 'EVT-2026-2EFBC69E', 'EVT-2026-2EFBC69E.svg', '2026-10-02 10:05:53'),
(7, 9, NULL, 'EVT-2026-8F9822E0', 'EVT-2026-8F9822E0.png', '2026-10-02 10:09:12'),
(8, 10, NULL, 'EVT-2026-CB2BF95F', 'EVT-2026-CB2BF95F.png', '2026-10-02 10:21:19'),
(9, 11, NULL, 'EVT-2026-59DB50A4', 'EVT-2026-59DB50A4.png', '2026-10-02 10:25:42'),
(10, 12, NULL, 'EVT-2026-8AFC4719', 'EVT-2026-8AFC4719.png', '2026-10-02 10:28:28'),
(11, 13, NULL, 'EVT-2026-2A59B516', 'EVT-2026-2A59B516.png', '2026-10-02 10:30:00'),
(12, 14, NULL, 'EVT-2026-875DD0ED', 'EVT-2026-875DD0ED.png', '2026-10-02 10:34:53'),
(13, 15, NULL, 'EVT-2026-32C8BA5F', 'EVT-2026-32C8BA5F.png', '2026-10-02 10:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `program` varchar(100) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `enrollment_number` varchar(50) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `whatsapp_number` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `department`, `program`, `semester`, `enrollment_number`, `phone_number`, `whatsapp_number`, `created_at`) VALUES
(1, 'zala komal', 'IT & CA', 'M.Sc. IT & CA', 'Semester 1', '230801703', '8320601715', '9898811311', '2026-10-02 08:37:52'),
(2, 'zala komal', 'IT & CA', 'M.Sc. IT & CA', 'Semester 1', '230801702', '8320604925', '8320604925', '2026-10-02 09:43:32'),
(3, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 6', '230801705', '8320604925', '8320604925', '2026-10-02 09:48:32'),
(4, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 1', '230801708', '8320604925', '8320604925', '2026-10-02 09:51:10'),
(5, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 2', '230801701', '8320604925', '8320604925', '2026-10-02 09:59:16'),
(6, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 1', '230801706', '8320604925', '8320604925', '2026-10-02 10:01:26'),
(7, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 3', '230801710', '8320604925', '8320604925', '2026-10-02 10:04:16'),
(8, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 4', '230801711', '8320604925', '8320604925', '2026-10-02 10:05:53'),
(9, 'zala komal j', 'Mathematics', 'M.Sc. Mathematics', 'Semester 1', '230801713', '8320604925', '8320604925', '2026-10-02 10:09:12'),
(10, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 1', '230801601', '8320604925', '8320604925', '2026-10-02 10:21:19'),
(11, 'zala komal j', 'Chemistry', 'B.Sc. Chemistry', 'Semester 1', '230801600', '8320604925', '8320604925', '2026-10-02 10:25:42'),
(12, 'zala komal j', 'IT & CA', 'M.Sc. IT & CA', 'Semester 3', '230801401', '8320604925', '8320604925', '2026-10-02 10:28:28'),
(13, 'zala komal', 'Mathematics', 'B.Sc. Mathematics', 'Semester 2', '230801470', '8320604925', '8320604925', '2026-10-02 10:30:00'),
(14, 'zala komal', 'Mathematics', 'M.Sc. Mathematics', 'Semester 6', '230184013', '8320604925', '8320604925', '2026-10-02 10:34:52'),
(15, 'zala komal', 'Physics', 'B.Sc. Physics', 'Semester 2', '2308017098', '8320604925', '8320604925', '2026-10-02 10:50:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_code` (`registration_code`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_number` (`enrollment_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
