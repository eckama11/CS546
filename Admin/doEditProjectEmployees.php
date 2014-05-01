<?php
require_once(dirname(__FILE__)."/../common.php");

$projectId = @$_POST['projectId'];


$projectEmployeeId = @$_POST['id'];
$startDate = @$_POST['startDate'];
$endDate = @$_POST['endDate'];
$project = @$_POST['project'];
$department = @$_POST['department'];
$employee = @$_POST['employee'];
$percentAllocation = @$_POST['percentAllocation'];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    $project = $db->readProject($project);

    if (!$startDate)
        throw new Exception("You must provide a start date");
    $startDate = new DateTime($startDate);
    $startDate->setTime(0, 0, 0);

    if (($startDate < $project->startDate) || ($startDate > $project->endDate))
        throw new Exception("The start date must be between ". $project->startDate->format("m/d/Y") ." and ". $project->endDate->format("m/d/Y"));

    if ($endDate) {
        $endDate = new DateTime($endDate);
        $endDate->setTime(0, 0, 0);
        if (($endDate < $project->startDate) || ($endDate > $project->endDate))
            throw new Exception("The end date must be between ". $project->startDate->format("m/d/Y") ." and ". $project->endDate->format("m/d/Y"));
    } else
        $endDate = null;

    if ($projectEmployeeId) {
        $current = $db->readProjectEmployeeAssociations($projectEmployeeId);

        if ($endDate &&
            $current->lastPayPeriodEndDate &&
            ($endDate != $current->endDate) &&
            ($endDate < $current->lastPayPeriodEndDate)
        ) {
            throw new Exception("The end date cannot be set earlier than the last pay period end date");
        }

        $department = $current->department;
        $employee = $current->employee;
    } else {
        $projectEmployeeId = 0;

        $department = $db->readDepartment($department);

        // Verify the department is assigned to the project
        $depts = $db->readDepartmentsForProject($project->id);
        $found = false;
        foreach ($depts as $dept) {
            if ($dept->id == $department->id) {
                $found = true;
                break;
            }
        } // foreach
        if (!$found)
            throw new Exception("The specified department is not assigned to the project.");

        $employee = $db->readEmployee($employee);
    }

    // TODO: Verify that adding the new record will not overlap in dates with a (different)
    //          existing record for the employee for the same project/department

    // TODO: Verify that the new (or updated) record will not cause the employee to exceed 100% allocation

    $entry = new ProjectEmployee(
                $projectEmployeeId,
                $startDate,
                $endDate,
                ($projectEmployeeId ? $current->lastPayPeriodEndDate : null),
                $project,
                $employee,
                $department,
                $percentAllocation
            );

    $entry = $db->writeProjectEmployeeAssociation($entry);

    $rv->id = $entry->id;
    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
