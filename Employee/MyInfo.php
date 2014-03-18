<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
?>
<div class="container padded">
	<legend>Employee information for <?php echo htmlentities($loginSession->authenticatedEmployee->name); ?></legend>
	<table class="table table-striped table-hover table-bordered table-condensed">
		<tr>
		  <td>Username</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->username); ?></td>
		</tr>
		<tr>
		  <td>Status</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->activeFlag); ?></td>
		</tr>
		<tr>
		  <td>Address</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->address); ?></td>
		</tr>
		<tr>
		  <td>Rank</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->rank); ?></td>
		</tr>
		<tr>
		  <td>Tax Id</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->taxId); ?></td>
		</tr>
		<tr>
		  <td>Number of Deductions</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->numDeductions); ?></td>
		</tr>
		<tr>
		  <td>Salary</td>
		  <td><?php echo htmlentities($loginSession->authenticatedEmployee->salary); ?></td>
		</tr>
	</table>
</div>