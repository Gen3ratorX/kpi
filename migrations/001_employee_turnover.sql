-- Turnover tracking (phase 1): who joins, who leaves, and when.
-- Run once on an existing `kpi` database. Safe to re-run (MariaDB 10.0.2+).
-- Fresh installs from kpi.sql / kpi_data.sql already include this.

ALTER TABLE `employee`
  ADD COLUMN IF NOT EXISTS `hire_date` date DEFAULT NULL AFTER `unit_id`,
  ADD COLUMN IF NOT EXISTS `status` enum('active','left') NOT NULL DEFAULT 'active' AFTER `hire_date`,
  ADD COLUMN IF NOT EXISTS `last_login` datetime DEFAULT NULL AFTER `status`;

-- One row per departure, so a reinstated employee who leaves again keeps both records
CREATE TABLE IF NOT EXISTS `employee_exit` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `employee_id` int(100) NOT NULL,
  `exit_date` date NOT NULL,
  `exit_type` enum('resigned','dismissed','retired','contract_ended','other') NOT NULL,
  `reason` text DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_exit_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
