<?php
require_once(dirname(__FILE__)."/../common.php");

$id = @$_POST['id'];
$activeFlag = true;
$name = @$_POST['name'];
$address = @$_POST['address'];
$taxId = @$_POST['taxid'];

$addNew = !$id;

if ($addNew) {
    $username = @$_POST['username'];
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

    if ($addNew) {
        // Verify the username is unique
        if ($db->isUsernameInUse($username))
            throw new Exception("The username '$username' is already assigned to another employee");

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
        // Read existing employee when updating for activeFlag, username & password
        //   fields, which cannot be updated by this service
        $emp = $db->readEmployee($id);

        $activeFlag = $emp->activeFlag;
        $username = $emp->username;
        $password1 = $emp->password;

        if (!$activeFlag)
            throw new Exception("Inactive employees cannot be updated.");

        $current = $emp->current;
    }

	// Verify the taxId is unique
    $existingEmployeeId = $db->isTaxIdInUse($taxId);
	if (($existingEmployeeId !== false) && ($id != $existingEmployeeId))
		throw new Exception("The Tax ID '$taxId' is already assigned to another employee");

    // Create/update the employee record
    $emp = new Employee(
                $id, $activeFlag, $username, $password1,
                $name, $address, $taxId,
                $current
            );

    $emp = $db->writeEmployee($emp);

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
