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
	$payPeriodStartDate = $paystub->payPeriodStartDate->format('F j, Y');
	$payPeriodEndDate = $paystub->payPeriodEndDate->format('F j, Y');
?>
<div class="container col-md-12 col-md-offset-0">
	<!--<pre><?php print_r($paystub) ?></pre>-->
	<TABLE class="table table-striped table-bordered table-condensed">
		<TR>
			<TH COLSPAN="3" style="padding-top: 10px">
				<H3>Paystub <?php echo htmlentities($paystub->id); ?></H3>
			</TH>
		</TR>
		<tr>
			<th colspan="3"></th>
        </tr>
        <tr>
            <th style="width:33%">Employee #</th>
            <th>Tax ID</th>
            <th style="width:33%">Pay Period</th>
        </tr>
        <tr>
            <TD><?php echo htmlentities($paystub->id); ?></TD>
			<TD><?php echo htmlentities($paystub->taxId); ?></TD>
			<TD><?php echo htmlentities($payPeriodStartDate ." - ". $payPeriodEndDate); ?></TD>
        </tr>
        <tr>
            <th>Name</th>
            <th>Address</th>
            <th>Num. Deductions</th>
        </tr>
        <tr>
            <TD><?php echo htmlentities($paystub->name); ?></TD>
			<TD><?php echo nl2br(htmlentities($paystub->address)); ?></TD>
			<TD><?php echo ($paystub->numDeductions); ?></TD>
        </tr>
        <tr>
            <th>Rank</th>
            <th colspan="2">Department</th>
        </tr>
        <tr>
			<TD><?php echo ($paystub->rank); ?></TD>
			<td colspan="2"><?php
                echo <<<EOT
                <table style="width:100%">
                    <thead>
                        <tr style="border-bottom:1px solid black">
                            <th style="width:50%">Name</th>
                            <th>Manager</th>
                        </tr>
                    </thead>
                    <tbody>
EOT;

                foreach ($paystub->departments as $dept) {
                    if (count($dept->managers) > 0)
                        $managers = implode(", ", $dept->managers);
                    else
                        $managers = 'No Manager Assigned';

                    echo '<tr>'.
                            '<td>'. htmlentities($dept->name) .'</td>'.
                            '<td>'. htmlentities($managers) .'</td>'.
                         '</tr>';
                } // foreach

                echo '</tbody></table>';
            ?></td>
        </tr>
		<tr>
			<th colspan="3"></th>
        </tr>
		<tr>
			<th></th>
			<th>Current Period</th>
			<th>Year To Date</th>
        </tr>
		<tr>
			<th>Earnings</th>
			<td class="currency">$ <?php echo number_format($paystub->salary, 2); ?></td>
			<td class="currency">$ <?php echo number_format($paystub->salaryYTD, 2); ?></td>
        </tr>
		<tr>
			<th>Deductions</th>
			<td class="currency">$ <?php echo number_format($paystub->deductions, 2); ?></td>
			<td class="currency">$ <?php echo number_format($paystub->deductionsYTD, 2); ?></td>
        </tr>
		<tr>
			<th>Tax Withheld</th>
			<td class="currency">$ <?php echo number_format($paystub->taxWithheld, 2); ?></td>
			<td class="currency">$ <?php echo number_format($paystub->taxWithheldYTD, 2); ?></td>
        </tr>
		<tr>
			<th>Net Pay</th>
			<th class="currency">$ <?php echo number_format($paystub->salary - $paystub->taxWithheld, 2); ?></th>
			<th class="currency">$ <?php echo number_format($paystub->salaryYTD - $paystub->taxWithheldYTD, 2); ?></th>
        </tr>
	</TABLE>
</div>