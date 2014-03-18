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
			"data" : $(form).serialize()
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
		<form role="form" onsubmit="return addEmployee(this);">
			<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
				<li class="active"><a href="#basic" data-toggle="tab">Basic</a></li>
				<li><a href="#rank_department" data-toggle="tab">Rank/Department</a></li>
				<li><a href="#taxes" data-toggle="tab">Taxes</a></li>
				<li><a href="#submit" data-toggle="tab">Submit</a></li>
			</ul>
		
			<div id="my-tab-content" class="tab-content">
				<div class="tab-pane active" id="basic">
					<div class="form-group">
						<label  style="color:black;">Name</label>
						<input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
					</div>
					<div class="form-group">
						<label style="color:black;">Address</label></br>
						<textarea class="form-control" rows="5" name="address" id="address" placeholder="Enter Address"></textarea>
					</div>
				</div>
				<div class="tab-pane" id="rank_department">
					<div class="form-group">
						<label style="color:black;">Rank</label>
						<select class="form-control" name="rank" id="rank">
							<option selected disabled id="">Select One</option>
							<option id="2">President</option>
							<option id="3">Vice-President</option>
							<option id="4">Human Resources Manager</option>
							<option id="5">New Product Manager</option>
							<option id="6">Legacy Product Manager</option>
							<option id="7">Customer Service Manager</option>
							<option id="8">Project Leader</option>
							<option id="9">Senior Software Developer</option>
							<option id="10">Software Developer II</option>
							<option id="11">Software Developer</option>
							<option id="12">Programmer</option>
						</select>
					</div>
					<div class="form-group">
						<label  style="color:black;">Departments</label>
						<select multiple class="form-control" name="department" id="department">
							<option selected disabled id="S_A_L_O">Select At Least One</option>
							<option id="1">Corporate</option>
							<option id="2">Human Resources</option>
							<option id="3">Marketing</option>
							<option id="4">Customer Support</option>
							<option id="5">Quality Assurance</option>
							<option id="6">Graphic Design</option>
							<option id="7">Documentation</option>
							<option id="8">Legacy Product Maintenance</option>
							<option id="9">New Product Development</option>
						</select>
					</div>
				</div>
				<div class="tab-pane" id="taxes">
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
				</div>
				<div class="tab-pane" id="submit">
					<p style="color:black;">Clicking on Add Employee will add </br>
					this employee. Please make sure all </br>
					information is correct before submitting.</p>
					<button type="submit" class="btn btn-default">Add Employee</button>
				</div>
			</div>
		</form>
	</div>
</div> 
