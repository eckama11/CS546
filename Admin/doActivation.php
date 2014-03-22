<?php
require_once(dirname(__FILE__)."/../common.php");

$employeeId = @$_POST['id'];
$activeFlag = @$_POST['status'];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator || ($loginSession->authenticatedEmployee->id == $employeeId))
        throw new Exception("You do not have sufficient access to perform this action");

	if (!is_numeric($activeFlag))
        throw new Exception("Invalid status specified");
    $activeFlag = ((int)$activeFlag != 0);

    // Update the employee
    $employee = $db->readEmployee($employeeId);

    if ($employee->activeFlag == $activeFlag)
        throw new Exception("The employee's status is already set to ". ($activeFlag ? 'active' : 'inactive'));

    $employee->activeFlag = $activeFlag;
    $db->writeEmployee($employee);

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);