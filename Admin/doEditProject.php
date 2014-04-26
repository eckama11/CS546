<?php
require_once(dirname(__FILE__)."/../common.php");

$id = @$_POST['id'];
$name = trim(@$_POST['name']);
$description = trim(@$_POST['description']);
$startDate = @$_POST['startDate'];
$endDate = @$_POST['endDate'];
$otherCosts = @$_POST['otherCosts'];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    if (!strlen($name))
        throw new Exception("You must provide a project name");

    if (!strlen($description))
        $description = null;

    if (!$startDate)
        throw new Exception("You must provide a start date");
    $startDate = new DateTime($startDate);

    if ($endDate)
        $endDate = new DateTime($endDate);
    else
        $endDate = null;

    if (!$id)
        $id = 0;

    $project = new Project($id, $startDate, $endDate, $name, $description, $otherCosts);

    $project = $db->writeProject($project);

    $rv->id = $project->id;
    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
