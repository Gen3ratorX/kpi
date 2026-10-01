-- Deleting a role used to delete every employee with it (and, by cascade, their tasks
-- and ratings). Make the database refuse instead.
-- Run once on an existing `kpi` database. Fresh installs from kpi.sql / kpi_data.sql already include this.

ALTER TABLE `employee` DROP FOREIGN KEY `employee_ibfk_1`;
ALTER TABLE `employee` ADD CONSTRAINT `employee_ibfk_1`
  FOREIGN KEY (`employee_role_id`) REFERENCES `employee_role` (`id`)
  ON DELETE RESTRICT ON UPDATE CASCADE;
