<?php
require_once(dirname(__FILE__)."/../common.php");

// If the form was posted, verify the old password and update the password if the 2 new passwords match and are acceptable
$employeeId = @$_POST['id'];
$activeFlag = @$_POST['status'];

$rv = (Object)[];
try {
    if (!isset($loginSession))
        throw new Exception("You do not have sufficient access to perform this action");

	if($activeFlag == null) 
		 throw new Exception("You must select a status");
		
    // Update the employee
    $employee = $db->readEmployee($employeeId);
    $employee->activeFlag = $activeFlag;
    $db->writeEmployee($employee);

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);