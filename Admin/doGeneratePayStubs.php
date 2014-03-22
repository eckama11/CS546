<?php
require_once(dirname(__FILE__)."/../common.php");

$payPeriodStartDate = @$_GET['payPeriodStartDate'];

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    $rv = $db->generatePayStubs($payPeriodStartDate);

    $fmt = "Y-m-d";
    $dateRange = $rv->startDate->format($fmt) ." to ". $rv->endDate->format($fmt);

    if ($rv->numGenerated > 0)
        $rv->message = "Successfully generated ". $rv->numGenerated ." pay stubs for ". $dateRange;
    else
        $rv->message = "No pay stubs to generate for ". $dateRange;

    $fmt = "Y-m-d";
    $rv->generationDate = $rv->generationDate->format($fmt);
    $rv->startDate = $rv->startDate->format($fmt);
    $rv->endDate = $rv->endDate->format($fmt);

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
