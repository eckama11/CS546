<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
?>

<script>
	function addEmployee(form) {
		var name = requiredField($(form.elements.name), "You must enter employee's name.");
		var address = requiredField($(form.elements.address), "You must enter employee's address.");
		var rank = requiredField($(form.elements.rank), "You must enter employee's rank");
		var department = requiredField($(form.elements.department), "You must enter employee's department");
		var taxid = requiredField($(form.elements.taxid), "You must enter employee's tax id");
		var numDeductions = requiredField($(form.elements.numDeductions), "You must enter employee's number of deductions");
		var salary = requiredField($(form.elements.salary), "You must enter employee's salary");
		if ((name == "") || (address == "") || (rank == "Select One") || (department == "Select At Least One") 
		|| (taxid == "") || (numDeductions == "") || (salary == "")) {
			showError("You must enter all form information to add employee.");
			return false;
		}
		
		$("#employeeDiv").hide();
		$("#spinner").show();

		$.ajax({
			"type" : "POST",
			"url" : "Admin/doAddEmployee.php",
			"data" : $(form).serialize(),
			"dataType" : "json"
			})
			.done(function(data) {
				$("#spinner").hide();

				if (data.error != null) {
					showError(data.error);
					$("#employeeDiv").show();
				} else
					$("#successDiv").show();
			})
			.fail(function( jqXHR, textStatus, errorThrown ) {
				console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
				console.log(jqXHR.textContent);
			})

		return false;
	}
</script>  
         
<div class="container col-md-6 col-md-offset-3">
	<div class="row" >
		<div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center;display:none">
			<div style="color:black;padding-bottom:32px;">Adding Employee...</div>
			<img src="spinner.gif">
		</div>
		<div id="successDiv" class="col-md-3 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;display:none">
			Employee has been successfully added.
		</div>
		<legend>Add Employee</legend>
		<form role="form" onsubmit="return addEmployee(this);">
			<div id="my-tab-content" class="tab-content">
				<div class="form-group">
					<label  style="color:black;">Name</label>
					<input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
				</div>
				<div class="form-group">
					<label style="color:black;">Address</label></br>
					<textarea class="form-control" rows="5" name="address" id="address" placeholder="Enter Address"></textarea>
				</div>
				<div class="form-group">
					<label style="color:black;">Rank</label>
					<select class="form-control" name="rank" id="rank">
						<?php 
							$ranks = $db->readRanks();
							foreach ($ranks as $rank) {
							echo '<option id="'. htmlentities($rank->id) .'">'. htmlentities($rank->name) .'</option>';
						}?>
					</select>
				</div>
				<div class="form-group">
					<label  style="color:black;">Departments</label>
					<select multiple class="form-control" name="department" id="department">
						<?php 
							$depts = $db->readDepartments();
							foreach ($depts as $dept) {
								echo '<option id="'. htmlentities($dept->id) .'">'. htmlentities($dept->name) .'</option>';
							}
                        ?>
					</select>
				</div>
				<div class="form-group">
					<label style="color:black;">Tax ID</label>
					<input type="text" class="form-control" name="taxid" id="taxid" placeholder="Enter Soc Sec #">
				</div>
				<div class="form-group">
					<label style="color:black;">Number of Deductions</label>
					<input type="text" class="form-control" name="numDeductions" id="numDeductions" placeholder="Enter a number">
				</div>
				<div class="form-group">
					<label style="color:black;">Salary</label>
					<input type="text" class="form-control" name="salary" id="salary" placeholder="Enter a salary">
				</div>
				<button type="submit" class="btn btn-default">Add Employee</button>
				</br>
			</div>
		</form>
	</div>
</div> 
