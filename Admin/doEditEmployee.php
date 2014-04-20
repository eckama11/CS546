<?php
require_once(dirname(__FILE__)."/../common.php");

$id = @$_POST['id'];
$name = @$_POST['name'];
$address = @$_POST['address'];
$taxId = @$_POST['taxid'];
$username = @$_POST['username'];

$addNew = !$id;

if ($addNew) {
    $password1 = @$_POST['password1'];
    $password2 = @$_POST['password2'];
    $departments = @$_POST['departments'];
    if (!$departments)
        $departments = [];
    $startDate = @$_POST['startDate'];
    $numDeductions = @$_POST['numDeductions'];
    $rank = @$_POST['rank'];
    $salary = @$_POST['salary'];
}

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    // Verify the username is unique
    if ($db->isUsernameInUse($username))
        throw new Exception("The username '$username' is already assigned to another employee");

    if ($addNew) {
        // Verify the password is valid
        if ($password1 != $password2)
            throw new Exception("The password and verify password do not match");

		// Verify password is > 8 length	
        if (strlen($password1) < 8)
            throw new Exception("The new password must be at least 8 characters long");

        $id = 0;

        $departments = array_map(function($deptId) { return $GLOBALS['db']->readDepartment($deptId); }, $departments);

        if (count($departments) == 0)
            throw new Exception("You must select at least one department for employee");

        $rank = $db->readRank($rank);

        if (!$startDate)
            throw new Exception("You must provide an effective date");
        $startDate = new DateTime($startDate);
        $current = new EmployeeHistory(0, $startDate, null, null, $departments, $rank, $numDeductions, $salary);
    } else {
        // Read existing employee when updating for password
        //   field, which cannot be updated by this service
        $emp = $db->readEmployee($id);

        $password1 = $emp->password;

        $current = $emp->current;
    }

	// Verify the taxId is unique
    $existingEmployeeId = $db->isTaxIdInUse($taxId);
	if (($existingEmployeeId !== false) && ($id != $existingEmployeeId))
		throw new Exception("The Tax ID '$taxId' is already assigned to another employee");

    // Create/update the employee record
    $emp = new Employee(
                $id, $username, $password1,
                $name, $address, $taxId,
                $current
            );

    $emp = $db->writeEmployee($emp);

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
