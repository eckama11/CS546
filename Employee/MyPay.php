<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    $employeeId = @$_GET['id'];
    if ($employeeId != null) {
        if (!$loginSession->isAdministrator)
            doUnauthorizedRedirect();
    } else
        $employeeId = $loginSession->authenticatedEmployee->id;

    try {
        $paystubs = $db->readPaystubs($employeeId);
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

?>
<script>
    function selectPaystub(row) {
        var id = row.getAttribute('stub-id');
        window.location.href = 'page.php/Employee/PayStub?id=' + id;
    } // selectPaystub(row)
</script>
<div class="container col-md-6 col-md-offset-3">
    <legend>Select Pay Stub</legend>
    <table class="table table-striped table-hover table-bordered table-condensed">
    <thead><tr>
      <th>ID</th>
      <th>Date</th>
    </tr></thead>
    <tbody>
<?php

    foreach ($paystubs as $stub) {
        echo '<tr onclick="selectPaystub(this)" stub-id="'. $stub->id .'">';
        echo   '<td>'. htmlentities($stub->id) .'</td>';
        echo   '<td>'. htmlentities($stub->payPeriodStartDate->format("Y-m-d")) .'</td>';
        echo '</tr>';
    } // foreach
?>
    </tbody>
    </table>
</div>
