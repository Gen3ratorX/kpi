-- Role numbers the code relies on (employee_role.role):
--   0 = General Manager, 1 = Auditor, 2 = Employee (regular staff), 3 = Manager
-- The code used to treat role 2 as Manager, which gave every regular employee manager-level
-- assessment access in their department. Managers now have their own role: after running this,
-- switch each manager / head of department to "Manager" on their employee page.

INSERT IGNORE INTO `employee_role` (`name`, `role`) VALUES ('Manager', 3);
