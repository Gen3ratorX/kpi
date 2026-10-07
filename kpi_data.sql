-- KPI Management System - demo database
--
-- WARNING: drops and recreates every table. Use only on a demo/test database.
-- Tested on MariaDB 13; written for MariaDB 10.4+ (as in XAMPP). MySQL 8 is untested.
--
-- Demo logins (employee passwords follow the app's rule: password = username):
--   admin    / Admin@123  Administrator
--   jmensah  / jmensah    Auditor
--   kboateng / kboateng   General Manager
--   aowusu   / aowusu     Manager (Information Systems)
--   petra    / petra      Employee
--   eaddo    / eaddo      Employee
--   ytetteh  / ytetteh    Employee who has left (sign-in is refused)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
DROP TABLE IF EXISTS `employee_role`;
DROP TABLE IF EXISTS `department`;
DROP TABLE IF EXISTS `unit`;
DROP TABLE IF EXISTS `employee`;
DROP TABLE IF EXISTS `employee_exit`;
DROP TABLE IF EXISTS `project`;
DROP TABLE IF EXISTS `assign`;
DROP TABLE IF EXISTS `task`;
DROP TABLE IF EXISTS `task_files`;
DROP TABLE IF EXISTS `performance`;
DROP TABLE IF EXISTS `admin`;

-- Role numbers the code relies on (employee_role.role):
--   0 = General Manager, 1 = Auditor, 2 = Employee (regular staff), 3 = Manager
CREATE TABLE `employee_role` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `role` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `department` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `hod` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `unit` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `department_id` int(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `unit_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Employees are never deleted: leaving is recorded (status + employee_exit) so that tasks
-- and ratings are kept and the turnover model can learn from exits.
CREATE TABLE `employee` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
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
  `hire_date` date DEFAULT NULL,
  `status` enum('active','left') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `is_new` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `employee_role_id` (`employee_role_id`),
  KEY `department_id` (`department_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`employee_role_id`) REFERENCES `employee_role` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `department` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `employee_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- One row per departure, so a reinstated employee who leaves again keeps both records
CREATE TABLE `employee_exit` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `employee_id` int(100) NOT NULL,
  `exit_date` date NOT NULL,
  `exit_type` enum('resigned','dismissed','retired','contract_ended','other') NOT NULL,
  `reason` text DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_exit_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `project` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `date_created` date NOT NULL,
  `deadline` date NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 0,
  `assess` tinyint(1) NOT NULL DEFAULT 0,
  `target` smallint(3) NOT NULL DEFAULT 30,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `assign` (
  `project_id` int(200) NOT NULL,
  `employee_id` int(200) NOT NULL,
  UNIQUE KEY `project_id` (`project_id`,`employee_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `assign_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assign_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `task` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `project_id` int(200) NOT NULL,
  `employee_id` int(200) NOT NULL,
  `description` text NOT NULL,
  `date_created` date NOT NULL,
  `deadline` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `task_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `task_files` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `task_id` int(200) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `file_type` varchar(150) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `task_files_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `performance` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `task_id` int(200) NOT NULL,
  `assessor_id` int(200) NOT NULL,
  `rating` tinyint(3) NOT NULL,
  `date` date NOT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `assessor_id` (`assessor_id`),
  CONSTRAINT `performance_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `performance_ibfk_2` FOREIGN KEY (`assessor_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `admin` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `surname` varchar(100) DEFAULT NULL,
  `other_names` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `employee_role` (`id`, `name`, `role`) VALUES
(1, 'Auditor', 1),
(2, 'Employee', 2),
(4, 'General Manager', 0),
(5, 'Manager', 3);

-- Administrator: username `admin`, password `Admin@123`. Change it after installing.
INSERT INTO `admin` (`id`, `surname`, `other_names`, `username`, `password`, `last_login`, `email`, `phone`) VALUES
(1, 'Admin', 'Demo', 'admin', '$2y$12$ZL0mo94r6FRenBGGh0IiRuHy88Mg10ENN8OlvDule5zv7BD4U.q8m', NULL, 'admin@example.com', NULL);

INSERT INTO `department` (`id`, `name`, `hod`) VALUES
(4, 'Information Systems', 'Ama Owusu');

INSERT INTO `unit` (`id`, `department_id`, `name`) VALUES
(1, 4, 'Software');

INSERT INTO `employee` (`id`, `surname`, `other_names`, `username`, `password`, `phone`, `email`, `location`, `employee_role_id`, `department_id`, `unit_id`, `hire_date`, `status`, `last_login`, `is_new`) VALUES
(1, 'Gligah', 'Petra', 'petra', '$2y$12$ELxs/RvF0uH01ApZ.b9EYuOOxfL0NGgTx0V7h5oUtaLRP2hvfJz7q', '+233200000001', 'petra@example.com', 'Teshie', 2, 4, 1, '2022-03-14', 'active', NULL, 1),
(15, 'Mensah', 'Steven', 'jmensah', '$2y$12$U3/NfcRV1Ps2EMd6yfZ6CO8eLwLtozhXxcqHMtdvoesHZ6PEdCxha', '+233200000002', 'jmensah@example.com', 'Kasoa', 1, 4, 1, '2019-06-03', 'active', NULL, 1),
(16, 'Owusu', 'Ama', 'aowusu', '$2y$12$Ap4DJ2dixhCjwuX.Rz5Ps.SyChv63Fr59z3bDobONRRbMjS/WpsqC', '+233200000003', 'aowusu@example.com', 'Accra', 5, 4, 1, '2018-09-03', 'active', NULL, 1),
(17, 'Boateng', 'Kwame', 'kboateng', '$2y$12$7Ae.j./onlHPhUlmUXaywet/9cZt8BvHEkG3KxG/1lPHLMKL4Tz2W', '+233200000004', 'kboateng@example.com', 'Tema', 4, 4, NULL, '2015-01-12', 'active', NULL, 1),
(18, 'Addo', 'Esi', 'eaddo', '$2y$12$wawE5TPd3lzBwnV5iNQUB.9gNN.50o/UJGDAt2BwBUbr7cJVY8viW', '+233200000005', 'eaddo@example.com', 'Madina', 2, 4, 1, '2024-02-05', 'active', NULL, 1),
(19, 'Tetteh', 'Yaw', 'ytetteh', '$2y$12$6iXlki.1o855tvl1GhVwgePjdQKqdr8ri56bhjzzYgB2g5xXmN7VG', '+233200000006', 'ytetteh@example.com', 'Kumasi', 2, 4, 1, '2021-05-10', 'left', NULL, 1);

INSERT INTO `employee_exit` (`id`, `employee_id`, `exit_date`, `exit_type`, `reason`, `recorded_at`) VALUES
(1, 19, '2026-07-31', 'resigned', 'Moved to a private bank', '2026-07-31 16:00:00');

-- Project 1 is under assessment (tasks locked, ratings open); project 2 is open for tasks
INSERT INTO `project` (`id`, `name`, `date_created`, `deadline`, `is_open`, `assess`, `target`) VALUES
(1, 'Order Management System', '2025-08-22', '2025-08-30', 0, 1, 100),
(2, 'Customer Portal Upgrade', '2026-09-01', '2026-12-15', 1, 0, 80);

INSERT INTO `assign` (`project_id`, `employee_id`) VALUES
(1, 1), (1, 16), (1, 18),
(2, 1), (2, 16), (2, 18);

INSERT INTO `task` (`id`, `project_id`, `employee_id`, `description`, `date_created`, `deadline`) VALUES
(1, 1, 1, 'Design the order database schema', '2025-08-22', '2025-08-30'),
(2, 1, 1, 'Build the order tracking page', '2025-08-22', '2025-08-30'),
(3, 1, 18, 'Write test cases for checkout', '2025-08-23', '2025-08-30'),
(4, 1, 16, 'Review the sprint deliverables', '2025-08-24', '2025-08-30'),
(5, 2, 1, 'Audit the current portal pages', '2026-09-05', '2026-12-15');

-- Petra's self-assessment of her two tasks; the other tasks are left for you to rate
INSERT INTO `performance` (`id`, `task_id`, `assessor_id`, `rating`, `date`, `comments`) VALUES
(1, 2, 1, 40, '2025-08-28', 'Page works; filters still missing'),
(2, 1, 1, 50, '2025-08-28', 'Schema reviewed and approved');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
