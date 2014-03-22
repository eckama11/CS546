<?php
    require_once(dirname(__FILE__)."/../common.php");
    require_once(dirname(__FILE__)."/EmployeeInfo.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    $emp = $loginSession->authenticatedEmployee;
?>
<div class="container">
	<legend>Employee information for <?php echo htmlentities($emp->name); ?></legend>
    <?php showEmployeeInfo( $emp ); ?>
</div>