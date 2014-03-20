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
    <!--<legend>Paystub <?php echo htmlentities($paystub->id); ?></legend>
    
    <div class="col-sm-10">
    	<label class="col-sm-5 control-label">Address</label>
		<p class="form-control-static"><?php echo ($paystub->address); ?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Rank</label>
		<p class="form-control-static"><?php echo ($paystub->rank); ?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Tax ID</label>
		<p class="form-control-static"><?php echo ($paystub->taxId); ?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Number of Deductions</label>
		<p class="form-control-static"><?php echo ($paystub->numDeductions); ?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Departments</label>
		<p class="form-control-static">
			<?php
				foreach ($departments as $dept) {
					$deptName = $dept->name;
					echo $deptName;
					?>
					<div></div>
					<?php
				}
			?>
		</p>
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Pay Period</label>
    	<p class="form-control-static"><?php echo $payPeriodStartDate; ?> - <?php echo $payPeriodEndDate;?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Pay Period Earnings</label>
		<p class="form-control-static">$ <?php echo ($paystub->salary); ?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Federal Tax Withheld</label>
		<p class="form-control-static">$ <?php echo ($paystub->taxWithheld); ?></p> 
	</div>
	
	<div class="col-sm-10">
    	<label class="col-sm-5 control-label">Net Pay</label>
		<p class="form-control-static">$ <?php echo $netPay; ?></p> 
	</div>-->
	
	<TABLE class="table table-striped table-hover table-bordered table-condensed">
   		<TR>
      		<TH COLSPAN="6">
      			<BR>
      			<H3>Paystub <?php echo htmlentities($paystub->id); ?></H3>
      		</TH>
  		</TR>
  		<TR>
      		<TD><b>Address</b></TD>
      		<TD><?php echo ($paystub->address); ?></TD>
      		<TD><b>Rank</b></TD>
      		<TD><?php echo ($paystub->rank); ?></TD>
      		<TD><b>Tax Id</b></TD>
      		<TD><?php echo ($paystub->taxId); ?></TD>
      		
   		</TR>
   		<TR>
      		<TD><b>Number of Deductions</b></TD>
      		<TD><?php echo ($paystub->numDeductions); ?></TD>
      		<TD><b>Departments</b></TD>
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
      		<TD><b>Pay Period</b></TD>
      		<TD><?php echo $payPeriodStartDate; ?> - <?php echo $payPeriodEndDate;?></TD>
   		</TR>
   		<TR>
   			<TD><b>Pay Period Earnings</b></TD>
      		<TD>$ <?php echo ($paystub->salary); ?></TD>
      		<TD><b>Federal Taxes</b></TD>
      		<TD>$ <?php echo ($paystub->taxWithheld); ?></TD>
      		<TD><b>Net Pay</b></TD>
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
