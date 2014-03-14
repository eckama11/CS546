<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doLogoutRedirect();
?>
<div>
Welcome, <?php echo htmlentities($loginSession->authenticatedEmployee->name); ?>!
</div>