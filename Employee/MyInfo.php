<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
?>
<div class="container padded">
Put some employee information here!
</div>