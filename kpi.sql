-- KPI Management System - clean install (schema, roles and one administrator)
--
-- Import into an EMPTY database (e.g. `kpi`). Tested on MariaDB 13; written for MariaDB 10.4+ (XAMPP).
-- For a database with demo users, projects and ratings, import kpi_data.sql instead.
-- Upgrading a database created from an older version? Run migrations/001-004 instead.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
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

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
