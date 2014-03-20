<?php
require_once(dirname(__FILE__)."/../common.php");

$rv = (Object)[];
try {
    if (!isset($loginSession) || !$loginSession->isAdministrator)
        throw new Exception("You do not have sufficient access to perform this action");

    $rv->numGenerated = $db->generatePayStubs();

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
    throw $rv;
} // try/catch

echo json_encode($rv);
