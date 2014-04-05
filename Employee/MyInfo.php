<?php
    require_once(dirname(__FILE__)."/../common.php");
    require_once(dirname(__FILE__)."/EmployeeInfo.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    $employeeId = @$_GET['id'];
    if ($employeeId != null) {
        if (!$loginSession->isAdministrator)
            doUnauthorizedRedirect();

        try {
            $employee = $db->readEmployee($employeeId);
        } catch (Exception $ex) {
            handleDBException($ex);
            return;
        }
    } else {
        $employee = $loginSession->authenticatedEmployee;
        $employeeId = $employee->id;
    }
?>
<div class="container">
	<legend>Employee information for <?php echo htmlentities($employee->name); ?></legend>
    <?php showEmployeeInfo( $employee ); ?>
</div>