-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 09, 2026 at 04:50 PM
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
-- Database: `eventease`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `status` enum('present','absent') NOT NULL DEFAULT 'absent',
  `check_in_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `registration_id`, `status`, `check_in_time`, `created_at`) VALUES
(1, 21, 'present', '2026-10-09 19:50:16', '2026-10-09 14:20:16'),
(2, 22, 'present', '2026-10-09 19:56:21', '2026-10-09 14:26:21');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(255) NOT NULL,
  `organizer` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 100,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `registration_open` tinyint(1) NOT NULL DEFAULT 1,
  `registration_deadline` datetime DEFAULT NULL,
  `status` enum('draft','published','cancelled','completed') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `venue`, `organizer`, `capacity`, `price`, `image`, `registration_open`, `registration_deadline`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Hack', 'hi', '2026-10-16', '20:48:00', '20:47:00', 'Atmiya uni', 'HOD', 100, 0.00, 'event_6ac79b913f8d41.44791484.jpg', 1, NULL, 'published', '2026-10-04 15:14:36', '2026-10-08 13:33:05'),
(2, 'Workshop', 'dghtyjtyjm', '2026-10-09', '10:58:00', '12:58:00', 'Atmiya University', 'cs', 100, 30.00, 'event_6ac8de4d5f4617.43669908.png', 1, '2026-10-09 17:59:00', 'published', '2026-10-09 12:30:05', '2026-10-09 12:30:05');

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
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('not_required','pending','paid','failed') NOT NULL DEFAULT 'not_required'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `registration_code`, `qr_code`, `registered_at`, `payment_status`) VALUES
(20, 34, 2, 'EVT-2026-19D00764', NULL, '2026-10-09 14:16:20', 'pending'),
(21, 34, 1, 'EVT-2026-E81D26D6', 'EVT-2026-E81D26D6.png', '2026-10-09 14:17:14', 'not_required'),
(22, 36, 1, 'EVT-2026-D5705268', 'EVT-2026-D5705268.png', '2026-10-09 14:24:34', 'not_required');

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
(15, 'zala komal', 'Physics', 'B.Sc. Physics', 'Semester 2', '2308017098', '8320604925', '8320604925', '2026-10-02 10:50:35'),
(16, 'Komal', 'Industrial Chemistry', 'B.Sc. Industrial Chemistry', 'Semester 5', '12451420', '8320604925', '9898744122', '2026-10-04 07:54:49'),
(17, 'vbfgjftmnfhtmfgh', 'Mathematics', 'B.Sc. Mathematics', 'Semester 1', '1245142000', '8320604925', '9898744122', '2026-10-04 08:00:05'),
(18, 'Komal', 'Industrial Chemistry', 'B.Sc. Industrial Chemistry', 'Semester 4', '1245142001', '8320604925', '9898744122', '2026-10-04 08:01:18'),
(19, 'komal', 'Computer Science', 'M.Sc. Computer Science', 'Semester 2', '2308017099', '8320604925', '8320604925', '2026-10-08 13:40:19'),
(20, 'Komal', 'Industrial Chemistry', 'B.Sc. Industrial Chemistry', 'Semester 4', '1240142000', '8320604925', '9898744122', '2026-10-09 12:36:04'),
(34, 'abc', 'Microbiology', 'B.Sc. Microbiology', 'Semester 7', '101', '8320604925', '9898744122', '2026-10-09 14:16:20'),
(36, 'dev zala', 'Mathematics', 'B.Sc. Mathematics', 'Semester 4', '102', '8320604925', '9898744122', '2026-10-09 14:24:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

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
  ADD UNIQUE KEY `unique_student_event` (`student_id`,`event_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_number` (`enrollment_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE;

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
