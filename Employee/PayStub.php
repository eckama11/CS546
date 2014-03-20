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
	$startDate = ($paystub->payPeriodStartDate);
	$payPeriodStartDate = $startDate->format('F j, Y');
	$payPeriodEndDate = $startDate->format('M t, Y');
	$departments = ($paystub->departments);
	$netPay = ($paystub->salary) - ($paystub->taxWithheld);
?>
<div class="container col-md-12 col-md-offset-0">
	<!--<pre><?php print_r($paystub) ?></pre>-->
	<TABLE class="table table-striped table-bordered table-condensed">
		<TR>
			<TH COLSPAN="6">
				<BR>
				<H3>Paystub <?php echo htmlentities($paystub->id); ?></H3>
			</TH>
		</TR>
		<TR>
			<TD><b>Address</b></TD>
			<TD>
				<?php 
					echo nl2br($paystub->address); 
				?>
			</TD>
			<TH>Rank</b></TH>
			<TD><?php echo ($paystub->rank); ?>, </TD>
			<TH>Tax Id</b></TH>
			<TD><?php echo ($paystub->taxId); ?></TD>
		
		</TR>
		<TR>
			<TH>Number of Deductions</TH>
			<TD><?php echo ($paystub->numDeductions); ?></TD>
			<TH>Departments</TH>
			<TD>
				<?php
				foreach ($departments as $dept) {
					$deptName = $dept->name;
					if ($dept === end($departments))
						echo $deptName;
					if (!($dept === end($departments)))
						echo $deptName.", ";
				}
				?>
			</TD>
			<TH>Pay Period</TH>
			<TD><?php echo $payPeriodStartDate; ?> - <?php echo $payPeriodEndDate;?></TD>
		</TR>
		<TR>
			<TH>Pay Period Earnings</TH>
			<TD>$ <?php echo ($paystub->salary); ?></TD>
			<TH>Taxes</TH>
			<TD>$ <?php echo ($paystub->taxWithheld); ?></TD>
			<TH><b>Net Pay</TH>
			<TD>$ <?php echo $netPay; ?></TD>
		</TR>
	</TABLE>
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
     *d@param	int				$numDeductions
*/
?>
</div>
