<?php
require_once(dirname(__FILE__)."/../common.php");

// If the form was posted, verify the old password and update the password if the 2 new passwords match and are acceptable
$id = @$_POST['id'];
$activeFlag = true;
$username = @$_POST['username'];
$password1 = @$_POST['password1'];
$password2 = @$_POST['password2'];
$name = @$_POST['name'];
$address = @$_POST['address'];
$rank = @$_POST['rank'];
$taxId = @$_POST['taxid'];
$numDeductions = @$_POST['numDeductions'];
$salary = @$_POST['salary'];

$departments = @$_POST['departments'];
if (!$departments)
    $departments = [];

$rv = (Object)[];
try {
    if (!isset($loginSession))
        throw new Exception("You do not have sufficient access to perform this action");

    $rank = $db->readRank($rank);

    $departments = array_map(function($deptId) { return $GLOBALS['db']->readDepartment($deptId); }, $departments);

    if (count($departments) == 0)
        throw new Exception("You must select at least one department for employee");
	
	// Verify the taxId is unique
	if ($db->isTaxIdInUse($taxId))
		throw new Exception("The Tax ID '$taxId' is already assigned to another employee");
			
    if ($id != null) {
        // Read existing employee when updating for activeFlag, username & password
        //   fields, which cannot be updated by this service
        $emp = $db->readEmployee($id);

        $activeFlag = $emp->activeFlag;
        $username = $emp->username;
        $password1 = $emp->password;
    } else {
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
    }

    // Create/update the employee record
    $emp = new Employee(
                $id, $activeFlag, $username, $password1,
                $name, $address, $rank, $taxId, $numDeductions, $salary
            );
    $emp = $db->writeEmployee($emp);

    // Create/update the department associations
    $db->writeDepartmentsForEmployee($emp, $departments);

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
