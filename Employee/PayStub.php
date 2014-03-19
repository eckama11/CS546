<?php
    require_once(dirname(__FILE__)."/../common.php");

    if (!isset($loginSession))
        doUnauthenticatedRedirect();

    $paystubId = @$_GET['id'];

    try {
        $paystub = $db->readPaystub($paystubId);

        if ((!$loginSession->isAdministrator) && ($paystub->employee->id != $loginSession->authenticatedEmployee->id))
            doUnauthorizedRedirect();
    } catch (Exception $ex) {
        handleDBException($ex);
        return;
    }

?>
<div class="container col-md-6 col-md-offset-3">
    <legend>Paystub <?php echo htmlentities($paystub->id); ?></legend>
    <pre><?php echo htmlentities($paystub); ?></pre>
<?php
/*
     * @param   int             $id
     * @param   Date            $payPeriodStartDate
     * @param   Employee        $employee
     * @param   string          $name
     * @param   string          $address
     * @param   string          $rank
     * @param   string          $taxId
     * @param   array[String]   $departments
     * @param   double          $salary
     * @param   int             $numDeductions
     * @param   double          $taxWithheld
     * @param   double          $taxRate
*/
?>
</div>
