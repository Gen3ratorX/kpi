-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 02:30 PM
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
-- Database: `kpi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(200) NOT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `other_names` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `surname`, `other_names`, `username`, `password`, `last_login`, `email`, `phone`) VALUES
(1, NULL, NULL, 'eoffei', '$2y$10$WsG.9lZaVbbhg5zb3PhZvOKD5El.QT6KVE0FzPMhYD/CmO8bSwZKK', '2025-08-22 11:43:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assign`
--

CREATE TABLE `assign` (
  `project_id` int(200) NOT NULL,
  `employee_id` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assign`
--

INSERT INTO `assign` (`project_id`, `employee_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `hod` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`, `hod`) VALUES
(4, 'Information Systems', 'John Mensah');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `other_names` varchar(200) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `location` varchar(200) NOT NULL,
  `employee_role_id` int(100) NOT NULL,
  `department_id` int(100) DEFAULT NULL,
  `unit_id` int(100) DEFAULT NULL,
  `is_new` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `surname`, `other_names`, `username`, `password`, `phone`, `email`, `location`, `employee_role_id`, `department_id`, `unit_id`, `is_new`) VALUES
(1, 'Gligah', 'Petra', 'petra', '$2y$10$jERrpRpkaL42VJuqUDEDAOhhjPk4aTf0aOACigPVgTrUHtjqkXWFG', '+23327562526363', 'ebenezeroffei@outlook.com', 'Teshie', 2, 4, 1, 1),
(15, 'Mensah', 'Steven', 'jmensah', '$2y$10$kdMmva78C/KVIgMTPgRLPOm3zlNBMv7i5qKfvTZqiDahELVjAovAG', '+23327562526363', 'john@gmail.com', 'Kasoa', 1, 4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_role`
--

CREATE TABLE `employee_role` (
  `id` int(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `role` tinyint(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_role`
--

INSERT INTO `employee_role` (`id`, `name`, `role`) VALUES
(1, 'Auditor', 1),
(2, 'Employee', 2),
(4, 'General Manager', 0);

-- --------------------------------------------------------

--
-- Table structure for table `performance`
--

CREATE TABLE `performance` (
  `id` int(200) NOT NULL,
  `task_id` int(200) NOT NULL,
  `assessor_id` int(200) NOT NULL,
  `rating` tinyint(3) NOT NULL,
  `date` date NOT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance`
--

INSERT INTO `performance` (`id`, `task_id`, `assessor_id`, `rating`, `date`, `comments`) VALUES
(1, 2, 1, 40, '2025-08-22', ''),
(2, 1, 1, 50, '2025-08-22', '');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `id` int(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `date_created` date NOT NULL,
  `deadline` date NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 0,
  `assess` tinyint(1) NOT NULL DEFAULT 0,
  `target` smallint(3) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`id`, `name`, `date_created`, `deadline`, `is_open`, `assess`, `target`) VALUES
(1, 'Order Management System', '2025-08-22', '2025-08-30', 0, 1, 100);

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE `task` (
  `id` int(200) NOT NULL,
  `project_id` int(200) NOT NULL,
  `employee_id` int(200) NOT NULL,
  `description` text NOT NULL,
  `date_created` date NOT NULL,
  `deadline` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task`
--

INSERT INTO `task` (`id`, `project_id`, `employee_id`, `description`, `date_created`, `deadline`) VALUES
(1, 1, 1, 'Nice', '2025-08-22', '2025-08-30'),
(2, 1, 1, 'I have a good God', '2025-08-22', '2025-08-30');

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `id` int(200) NOT NULL,
  `department_id` int(200) NOT NULL,
  `name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`id`, `department_id`, `name`) VALUES
(1, 4, 'Software');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assign`
--
ALTER TABLE `assign`
  ADD UNIQUE KEY `project_id` (`project_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `employee_role_id` (`employee_role_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `employee_role`
--
ALTER TABLE `employee_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `role` (`role`);

--
-- Indexes for table `performance`
--
ALTER TABLE `performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `assessor_id` (`assessor_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_role`
--
ALTER TABLE `employee_role`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `performance`
--
ALTER TABLE `performance`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `task`
--
ALTER TABLE `task`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assign`
--
ALTER TABLE `assign`
  ADD CONSTRAINT `assign_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assign_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`employee_role_id`) REFERENCES `employee_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `employee_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `performance`
--
ALTER TABLE `performance`
  ADD CONSTRAINT `performance_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `performance_ibfk_2` FOREIGN KEY (`assessor_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `task_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `unit`
--
ALTER TABLE `unit`
  ADD CONSTRAINT `unit_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
