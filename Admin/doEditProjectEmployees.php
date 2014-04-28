<?php
require_once(dirname(__FILE__)."/../common.php");

$projectId = @$_POST['projectId'];


$historyId = @$_POST['id'];
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
    $startDate->setTime(0, 0, 0);

    if ($endDate) {
        $endDate = new DateTime($endDate);
        $endDate->setTime(0, 0, 0);
    } else
        $endDate = null;

    if ($projectEmployeeId) {
        $current = $db->readEmployeesForProject($projectId);

        if ($endDate &&
            $current->lastPayPeriodEndDate &&
            ($endDate != $current->endDate) &&
            ($endDate < $current->lastPayPeriodEndDate)
        ) {
            throw new Exception("The end date cannot be set earlier than the last pay period end date");
        }
    } else
        $historyId = 0;

    $entry = new EmployeeHistory(
                $historyId,
                $startDate,
                $endDate,
                ($historyId ? $current->lastPayPeriodEndDate : null),
                $departments,
                $rank,
                $numDeductions,
                $salary
            );

    $entry = $db->writeEmployeeHistory($employeeId, $entry);

    $rv->id = $entry->id;
    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
