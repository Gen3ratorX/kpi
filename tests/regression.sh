#!/usr/bin/env bash
# Regression and security test suite for the KPI Management System.
#
# Exercises every create/edit/delete action through the real HTTP endpoints, the access
# rules for each role, and SQL-injection / file-upload attacks against every kind of input.
#
# Requirements: the app running (see README "Running locally"), curl, and the mariadb or
# mysql command-line client with access to the app's database.
#
#   bash tests/regression.sh
#
# Settings (environment variables):
#   BASE_URL   where the app is served          (default http://127.0.0.1:8766)
#   DB_NAME    the app's database                (default kpi)
#   DB_CLIENT  database client command           (default: mariadb or mysql, user root)
#
# WARNING: the suite re-imports kpi_data.sql into DB_NAME before and after running, so
# use it only on the demo database.

set -u
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
B="${BASE_URL:-http://127.0.0.1:8766}"
DB_NAME="${DB_NAME:-kpi}"
if [ -z "${DB_CLIENT:-}" ]; then
  if command -v mariadb >/dev/null; then DB_CLIENT="mariadb -u root"; else DB_CLIENT="mysql -u root"; fi
fi
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
ad=$TMP/admin; st=$TMP/staff; au=$TMP/auditor; mg=$TMP/manager; gm=$TMP/gm
pass=0; fail=0

q() { $DB_CLIENT -N "$DB_NAME" -e "$1"; }
reset_db() { $DB_CLIENT "$DB_NAME" < "$ROOT/kpi_data.sql"; }
ok() { pass=$((pass+1)); printf '  ok    %s\n' "$1"; }
bad() { fail=$((fail+1)); printf '  FAIL  %s\n' "$1"; }
check() { # name, regex matched against "HTTP_CODE BODY", curl args...
  local name=$1 want=$2; shift 2
  local out code body
  out=$(curl -s -w ' %{http_code}' "$@"); code=${out##* }; body=${out% *}
  if [[ "$code $body" =~ $want ]]; then pass=$((pass+1)); printf '  ok    %-64s %s\n' "$name" "$code"
  else fail=$((fail+1)); printf '  FAIL  %-64s %s %s\n' "$name" "$code" "${body:0:160}"; fi
}
login() { curl -s -c "$1" -b "$1" --data-urlencode task=signIn --data-urlencode "username=$2" --data-urlencode "password=$3" "$B/auth.php" >/dev/null; }

curl -s -o /dev/null "$B/" || { echo "The app is not reachable at $B. Start it first (see README)."; exit 2; }
q "SELECT 1" >/dev/null || { echo "Cannot reach database '$DB_NAME' with: $DB_CLIENT"; exit 2; }
echo "Resetting '$DB_NAME' to the demo data (kpi_data.sql)..."
reset_db
uploads_before=$(ls "$ROOT/uploads/tasks" 2>/dev/null)

echo "== Sign-in, including SQL injection on the login form =="
check "login: ' OR '1'='1 bypass is rejected" '^400' --data-urlencode task=signIn --data-urlencode "username=' OR '1'='1" --data-urlencode "password=x" "$B/auth.php"
check "login: admin'-- comment bypass is rejected" '^400' --data-urlencode task=signIn --data-urlencode "username=admin'-- " --data-urlencode "password=x" "$B/auth.php"
check "login: trailing backslash trick is rejected" '^400' --data-urlencode task=signIn --data-urlencode 'username=\' --data-urlencode "password=x" "$B/auth.php"
check "login: employee who has left is refused" '^401' --data-urlencode task=signIn --data-urlencode "username=ytetteh" --data-urlencode "password=ytetteh" "$B/auth.php"
check "login: demo admin" 'SUCCESS' -c "$ad" -b "$ad" --data-urlencode task=signIn --data-urlencode "username=admin" --data-urlencode "password=Admin@123" "$B/auth.php"

echo "== Every admin page loads without PHP errors =="
for pg in index.php employees.php "employees.php?page=abc" "employees.php?page=-5" employee_form.php "employee_form.php?id=15" \
          roles.php role_form.php "role_form.php?id=1" departments.php department_form.php "department_form.php?id=4" units.php \
          unit_form.php "unit_form.php?id=1" project_form.php "project_detail.php?id=1" create.php risk.php; do
  body=$(curl -s -b "$ad" -w ' %{http_code}' "$B/admin/$pg")
  if [[ "${body##* }" != 200 ]]; then bad "GET $pg returned ${body##* }"
  elif [[ "$body" =~ \<b\>(Warning|Deprecated|Fatal\ error|Notice)\</b\> ]]; then bad "GET $pg shows a PHP ${BASH_REMATCH[1]}"
  else ok "GET $pg"; fi
done
check "admin pages require sign-in" '^302' "$B/admin/index.php"

echo "== SQL injection through page ids =="
for pg in "employee_form.php?id=15%20OR%201=1" "role_form.php?id=1%20UNION%20SELECT%201,username,password%20FROM%20admin" \
          "unit_form.php?id=1%20OR%20SLEEP(3)" "project_detail.php?id=1%20OR%201=1"; do
  start=$(date +%s); body=$(curl -s -b "$ad" "$B/admin/$pg"); took=$(( $(date +%s) - start ))
  if [[ "$body" == *'$2y$'* || $took -ge 3 ]]; then bad "injection worked: $pg"; else ok "no leak or delay: $pg"; fi
done

echo "== Employees =="
MANAGER_ROLE=$(q "SELECT id FROM employee_role WHERE role=3")
check "create employee with a hire date" '^201' -b "$ad" -d "task=saveEmployee&surname=Testperson&otherNames=Ama&phone=0240000000&email=amatest@example.com&location=Accra&role=2&department=4&unit=&username=atestperson&hireDate=2024-03-01" "$B/admin/utils.php"
check "create a manager whose name contains a quote" '^201' -b "$ad" --data-urlencode task=saveEmployee --data-urlencode "surname=O'Brien" -d "otherNames=Kofi&phone=0240000001&email=kobrien@example.com&location=Kumasi&role=$MANAGER_ROLE&department=4&unit=1&username=kobrien&hireDate=" "$B/admin/utils.php"
check "duplicate username is refused (400)" '^400' -b "$ad" -d "task=saveEmployee&surname=X&otherNames=Y&phone=1&email=other@example.com&location=Z&role=2&department=4&username=atestperson" "$B/admin/utils.php"
check "impossible hire date is refused" '^400.*Invalid hire date' -b "$ad" -d "task=saveEmployee&surname=X&otherNames=Y&phone=1&email=x2@example.com&location=Z&role=2&department=4&username=xy&hireDate=2024-02-31" "$B/admin/utils.php"
SID=$(q "SELECT id FROM employee WHERE username='atestperson'"); MID=$(q "SELECT id FROM employee WHERE username='kobrien'")
check "edit employee" '^201' -b "$ad" -d "task=editEmployee&employeeId=$SID&surname=Testperson&otherNames=Ama&phone=0240000000&email=amatest@example.com&location=Tema&role=2&department=4&unit=1&username=atestperson&hireDate=2024-03-01" "$B/admin/utils.php"
[[ "$(q "SELECT CONCAT(location,'|',unit_id,'|',hire_date) FROM employee WHERE id=$SID")" == "Tema|1|2024-03-01" ]] && ok "edit saved exactly" || bad "edit not saved"
before=$(q "SELECT COUNT(*) FROM employee WHERE location='Hacked'")
curl -s -o /dev/null -b "$ad" --data-urlencode task=editEmployee --data-urlencode "employeeId=1 OR 1=1" -d "surname=A&otherNames=B&phone=1&email=h@example.com&location=Hacked&role=2&department=4&username=hacked" "$B/admin/utils.php"
[[ $(q "SELECT COUNT(*) FROM employee WHERE location='Hacked'") -le $((before+1)) ]] && ok "injected employeeId changes at most one row" || bad "injected employeeId changed many rows"
check "mark employee as left" 'SUCCESS' -b "$ad" -d "task=markEmployeeLeft&employeeId=$SID&exitDate=2026-09-30&exitType=resigned&reason=test" "$B/admin/utils.php"
check "mark as left twice is refused" '^409' -b "$ad" -d "task=markEmployeeLeft&employeeId=$SID&exitDate=2026-09-30&exitType=resigned" "$B/admin/utils.php"
check "invalid exit type is refused" '^400' -b "$ad" -d "task=markEmployeeLeft&employeeId=15&exitDate=2026-09-30&exitType=vanished" "$B/admin/utils.php"
check "reinstate" 'SUCCESS' -b "$ad" -d "task=reinstateEmployee&employeeId=$SID" "$B/admin/utils.php"

echo "== Roles, departments, units =="
check "create role" '^201' -b "$ad" -d "task=saveRole&name=Intern&role=7" "$B/admin/utils.php"
RID=$(q "SELECT id FROM employee_role WHERE name='Intern'")
check "edit role" '^200' -b "$ad" -d "task=editRole&roleId=$RID&name=Trainee&role=7" "$B/admin/utils.php"
check "delete unused role" '^(200|204)' -b "$ad" -d "task=deleteRole&roleId=$RID" "$B/admin/utils.php"
check "delete role still in use is refused (409)" '^409' -b "$ad" -d "task=deleteRole&roleId=2" "$B/admin/utils.php"
check "create department" '^201' -b "$ad" --data-urlencode task=saveDepartment --data-urlencode "department=Finance & Admin" --data-urlencode "hod=Kofi O'Brien" "$B/admin/utils.php"
DID=$(q "SELECT id FROM department WHERE name LIKE 'Finance%'")
check "edit department (no SQL echoed back)" '^200 \{"status":"SUCCESS"\}$' -b "$ad" --data-urlencode task=editDepartment -d "departmentId=$DID" --data-urlencode "department=Finance" --data-urlencode "hod=Ama Mensah" "$B/admin/utils.php"
check "search heads of department (managers only)" 'Brien' -b "$ad" "$B/admin/utils.php?task=searchDepartmentHead&q=Kofi"
check "search heads: injection in the search text" '^200 \{"employees":\[\]\}' -b "$ad" "$B/admin/utils.php?task=searchDepartmentHead&q=%25'%20OR%201=1%20--%20"
check "create unit" '^201' -b "$ad" -d "task=saveUnit&unit=Payroll&department=$DID" "$B/admin/utils.php"
UNIT=$(q "SELECT id FROM unit WHERE name='Payroll'")
check "edit unit" '^200' -b "$ad" -d "task=editUnit&unitId=$UNIT&unit=Payroll%20Ops&department=$DID" "$B/admin/utils.php"
check "delete unit" '^200' -b "$ad" -d "task=deleteUnit&unitId=$UNIT" "$B/admin/utils.php"
check "delete department" '^200' -b "$ad" -d "task=deleteDepartment&departmentId=$DID" "$B/admin/utils.php"
check "delete unit with injected id" '^200' -b "$ad" --data-urlencode task=deleteUnit --data-urlencode "unitId=0 OR 1=1" "$B/admin/utils.php"
[[ $(q "SELECT COUNT(*) FROM unit") -ge 1 ]] && ok "units table intact" || bad "units table wiped"

echo "== Projects =="
check "search employees to assign" 'Testperson' -b "$ad" "$B/admin/utils.php?task=searchProjectEmployees&employeeRole=Employee&q=Test"
check "search employees: injection in role name" '^500' -b "$ad" "$B/admin/utils.php?task=searchProjectEmployees&employeeRole=x'%20OR%20'1'='1&q="
check "create project with two employees" '^201' -b "$ad" -d "task=saveProject&projectName=Regression%20Project&deadline=2026-12-31&isOpen=1&target=60&assignedEmployees=$SID,$MID" "$B/admin/utils.php"
PID=$(q "SELECT id FROM project WHERE name='Regression Project'")
[[ $(q "SELECT COUNT(*) FROM assign WHERE project_id=$PID") == 2 ]] && ok "both employees assigned" || bad "assignments wrong"
check "update project option" 'SUCCESS' -b "$ad" -d "task=updateProjectOptions&projectId=$PID&option=assessment&value=no" "$B/admin/utils.php"

echo "== Employee portal: tasks =="
login "$st" atestperson atestperson; login "$mg" kobrien kobrien; login "$au" jmensah jmensah; login "$gm" kboateng kboateng
check "staff portal home" '^200' -b "$st" "$B/employee/"
check "staff adds a task" 'SUCCESS' -b "$st" -d "task=saveTask&projectId=$PID" --data-urlencode "employeeTask=Prepare report <b>draft</b>" "$B/employee/utils.php"
check "injected projectId is reduced to the number" 'SUCCESS' -b "$st" --data-urlencode task=saveTask --data-urlencode "projectId=$PID OR 1=1" -d "employeeTask=x" "$B/employee/utils.php"
printf '%%PDF-1.4 test\n' > "$TMP/evidence.pdf"; printf '<?php echo 1;' > "$TMP/shell.php"; cp "$TMP/shell.php" "$TMP/shell.png"
check "staff uploads a PDF as evidence" 'SUCCESS' -b "$st" -F task=saveTaskWithFile -F projectId=$PID --form-string "employeeTask=<script>alert(1)</script>" -F "file=@$TMP/evidence.pdf;type=application/pdf" "$B/employee/utils.php"
[[ "$(q "SELECT description FROM task WHERE description LIKE '%alert%'")" != *'<script>'* ]] && ok "task description stored escaped (no raw <script>)" || bad "raw <script> stored"
check "upload: .php labelled as an image is refused" '^400' -b "$st" -F task=saveTaskWithFile -F projectId=$PID -F "employeeTask=a" -F "file=@$TMP/shell.php;type=image/png" "$B/employee/utils.php"
check "upload: PHP code renamed to .png is refused" '^400' -b "$st" -F task=saveTaskWithFile -F projectId=$PID -F "employeeTask=b" -F "file=@$TMP/shell.png;type=image/png" "$B/employee/utils.php"
TID=$(q "SELECT id FROM task WHERE employee_id=$SID ORDER BY id LIMIT 1")
check "staff edits own task" 'SUCCESS' -b "$st" -d "task=editTask&taskId=$TID&employeeTask=Edited" "$B/employee/utils.php"
check "staff cannot edit someone else's task" '^403' -b "$st" -d "task=editTask&taskId=1&employeeTask=hacked" "$B/employee/utils.php"
check "staff cannot delete someone else's task" '^403' -b "$st" -d "task=deleteTask&taskId=1" "$B/employee/utils.php"
check "auditor cannot add tasks" '^403' -b "$au" -d "task=saveTask&projectId=$PID&employeeTask=x" "$B/employee/utils.php"
check "manager adds own task" 'SUCCESS' -b "$mg" -d "task=saveTask&projectId=$PID&employeeTask=Manager%20task" "$B/employee/utils.php"
check "employee pages require sign-in" '^302' "$B/employee/"

echo "== Assessment =="
q "UPDATE project SET is_open=0, assess=1 WHERE id=$PID"
check "tasks are locked during assessment" '^403' -b "$st" -d "task=editTask&taskId=$TID&employeeTask=late%20change" "$B/employee/utils.php"
check "staff views own ratings" '"assessments"' -b "$st" "$B/employee/utils.php?task=getTaskAssessments&employeeId=$SID&projectId=$PID&readOnly=false&assessorRole="
check "staff: injection in assessorRole" '^200' -b "$st" "$B/employee/utils.php?task=getTaskAssessments&employeeId=$SID&projectId=$PID&readOnly=1&assessorRole=1)%20OR%201=1%20--%20"
check "manager views staff in own department" '"assessments"' -b "$mg" "$B/employee/utils.php?task=getTaskAssessments&employeeId=$SID&projectId=$PID"
check "manager rates a staff task" 'SUCCESS' -b "$mg" -d "task=saveAssessment&taskId=$TID&rating=80&comments=Solid" "$B/employee/utils.php"
check "staff cannot rate the manager" '^403' -b "$st" -d "task=saveAssessment&taskId=$(q "SELECT id FROM task WHERE employee_id=$MID LIMIT 1")&rating=10" "$B/employee/utils.php"
check "auditor rates a task (quote in comment)" 'SUCCESS' -b "$au" -d "task=saveAssessment&taskId=$TID&rating=70" --data-urlencode "comments=Good, but don't rush" "$B/employee/utils.php"
AUDITOR=$(q "SELECT id FROM employee WHERE username='jmensah'")
AID=$(q "SELECT id FROM performance WHERE task_id=$TID AND assessor_id=$AUDITOR")
check "auditor updates own rating" 'SUCCESS' -b "$au" -d "task=saveAssessment&taskId=$TID&assessmentId=$AID&rating=90&comments=Better" "$B/employee/utils.php"
check "auditor cannot overwrite the manager's rating" '^403' -b "$au" -d "task=saveAssessment&taskId=$TID&assessmentId=$(q "SELECT id FROM performance WHERE task_id=$TID AND assessor_id=$MID")&rating=1" "$B/employee/utils.php"
curl -s -o /dev/null -b "$au" -d "task=saveAssessment&taskId=$TID&assessmentId=$AID" --data-urlencode "rating=90, comments=(SELECT password FROM admin LIMIT 1)" "$B/employee/utils.php"
[[ "$(q "SELECT comments FROM performance WHERE id=$AID")" != '$2y$'* ]] && ok "injected rating cannot write the admin hash" || bad "injection wrote the admin hash"
check "staff cannot close assessment" '^403' -b "$st" -d "task=closeAssessment&projectId=$PID" "$B/employee/utils.php"
check "general manager closes assessment" 'SUCCESS' -b "$gm" -d "task=closeAssessment&projectId=$PID" "$B/employee/utils.php"
check "admin deletes the project" '^(200|204)' -b "$ad" -d "task=deleteProject&projectId=$PID" "$B/admin/utils.php"

echo "== Clean-up =="
for f in $(ls "$ROOT/uploads/tasks" 2>/dev/null); do
  grep -qx "$f" <<< "$uploads_before" || rm -f "$ROOT/uploads/tasks/$f"
done
reset_db && ok "demo database restored"

echo
echo "passed $pass, failed $fail"
[ "$fail" -eq 0 ]
