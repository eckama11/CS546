<?php
require_once(dirname(__FILE__)."/../common.php");

$id = @$_POST['id'];

$departments = @$_POST['departments'];
if (!$departments)
    $departments = [];
$startDate = @$_POST['startDate'];
$numDeductions = @$_POST['numDeductions'];
$rank = @$_POST['rank'];
$salary = @$_POST['salary'];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    // Read existing employee when updating for username & password
    //   fields, which cannot be updated by this service
    $emp = $db->readEmployee($id);

    $departments = array_map(function($deptId) { return $GLOBALS['db']->readDepartment($deptId); }, $departments);

    if (count($departments) == 0)
        throw new Exception("You must select at least one department for employee");

    $rank = $db->readRank($rank);

    if (!$startDate)
        throw new Exception("You must provide an effective date");
    $startDate = new DateTime($startDate);
    $current = new EmployeeHistory(0, $startDate, null, null, $departments, $rank, $numDeductions, $salary);

    throw new Exception("TODO: Finish me!");
/*
    // Create/update the employee record
    $emp = new Employee(
                $id, $username, $password1,
                $name, $address, $taxId,
                $current
            );

    $emp = $db->writeEmployee($emp);
*/

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
