<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
    $employeeId = @$_GET['id'];
    try {
        $emp = $db->readEmployee($employeeId);
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }
?>