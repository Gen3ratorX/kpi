-- Files attached to tasks (used by TaskControl::saveTaskWithFile / getTaskFiles).
-- The code needed this table but neither SQL dump created it, so fresh installs crashed
-- when viewing assessments. Safe to run on a database that already has it.

CREATE TABLE IF NOT EXISTS `task_files` (
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
