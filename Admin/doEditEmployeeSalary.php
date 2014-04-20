<?php
require_once(dirname(__FILE__)."/../common.php");

$employeeId = @$_POST['employeeId'];
$historyId = @$_POST['historyId'];
$startDate = @$_POST['startDate'];
$endDate = @$_POST['endDate'];
$departments = @$_POST['departments'];
if (!$departments)
    $departments = [];
$rank = @$_POST['rank'];
$numDeductions = @$_POST['numDeductions'];
$salary = @$_POST['salary'];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    if (count($departments) == 0)
        throw new Exception("You must select at least one department for employee");

    $departments = array_map(function($deptId) { return $GLOBALS['db']->readDepartment($deptId); }, $departments);
    $rank = $db->readRank($rank);

    if (!$startDate)
        throw new Exception("You must provide a start date");
    $startDate = new DateTime($startDate);

    if ($endDate)
        $endDate = new DateTime($endDate);

    $entry = $db->readEmployeeHistory();
    $current = new EmployeeHistory(0, $startDate, $endDate, null, $departments, $rank, $numDeductions, $salary);

//    $emp = $db->readEmployee($employeeId);


    throw new Exception("TODO: Finish me!");
/*
    // Create/update the employee record
    $emp = new Employee(
                $employeeId, $username, $password1,
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
