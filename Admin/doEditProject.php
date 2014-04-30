<?php
require_once(dirname(__FILE__)."/../common.php");

$id = @$_POST['id'];
$name = trim(@$_POST['name']);
$description = trim(@$_POST['description']);
$startDate = @$_POST['startDate'];
$endDate = @$_POST['endDate'];
$otherCosts = @$_POST['otherCosts'];
$departments = @$_POST['departments'];
if (!$departments)
    $departments = [];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    if (!strlen($name))
        throw new Exception("You must provide a project name");

    // Verify the project name is unique
    if ($db->isProjectNameInUse($name, ($id ? $id : null)))
        throw new Exception("The name '$name' is already assigned to another project");

    if (!strlen($description))
        $description = null;

    if (!$startDate)
        throw new Exception("You must provide a start date");
    $startDate = new DateTime($startDate);

    if (!$endDate)
        throw new Exception("You must provide an end date");
    $endDate = new DateTime($endDate);

    $departments = array_map(function($deptId) { return $GLOBALS['db']->readDepartment($deptId); }, $departments);
    if (count($departments) == 0)
        throw new Exception("You must select at least one department for employee");

    if (!$id)
        $id = 0;

    $project = new Project($id, $startDate, $endDate, $name, $description, $otherCosts);

    $project = $db->writeProject($project);

    $db->writeDepartmentsForProject($project->id, $departments);

    $rv->id = $project->id;
    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
