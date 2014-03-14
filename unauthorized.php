<?php
    require_once(dirname(__FILE__)."/common.php");
    if (!isset($loginSession))
        doLogoutRedirect();
?>
<div class="alert alert-warning">
Sorry, you are not authorized to see that content.
</div>