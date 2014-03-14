<?php

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
 		<title>Add Employee</title>
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- StyleSheet -->
		<link rel="stylesheet" href="../css/bootstrap.min.css" />
		<link rel="stylesheet" href="../css/custom.css" />
		
	</head>
 
	<body>
		<div class="navbar navbar-inverse navbar-static-top">
			<div class="container">
				<a href="#" class="navbar-brand">UPay Solutions</a>
				<button class="navbar-toggle" data-toggle="collapse" data-target=".navHeaderCollapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<div class="collapse navbar-collapse navHeaderCollapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="MyPay.php">MyPay</a></li>
						<li><a href="MyInfo.php">MyInfo</a></li>
						<li><a href="Pass.php">Account Settings</a></li>
						<li class="dropdown">
          					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
          					<ul class="dropdown-menu">
            					<li><a href="AddEmployee.php">Add Employee</a></li>
            					<li><a href="Activation.php">Activate/Deactivate</a></li>
            					<li><a href="ViewEmpStub.php">View Pay Stubs</a></li>
            					<li><a href="ChangeEmpPass.php">Change Employee Passwords</a></li>
            					<li><a href="Modify.php">Modify Employee</a></li>
            					<li><a href="Generate.php">Generate Pay Stubs</a></li>
          					</ul>
        				</li>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="container padded">
    		<div class="row" >
    			<div class="col-md-3 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;">
           			<ul class="nav nav-tabs">
           				<li>
           			<form role="form">
  						<div class="form-group">
    						<label name="name" style="color:black;">Name</label>
    						<input type="text" class="form-control" id="name" placeholder="Enter name">
  						</div>
  						<div class="form-group">
    						<label name="address" style="color:black;">Address</label>
    						<input type="text" class="form-control" id="address" placeholder="Enter Address">
  						</div>
  					</form>
  					</li>
  					<li>
  					<form role="form">
  						<div class="form-group">
  							<label name="rank" style="color:black;">Rank</label>
   							<select class="form-control">
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
  							<label name="taxid" style="color:black;">Tax ID</label>
    						<input type="text" class="form-control" id="taxid" placeholder="Enter Soc Sec #">
  						</div>
  						<div class="form-group">
  							<label name="numDeductions" style="color:black;">Number of Deductions</label>
    						<input type="text" class="form-control" id="taxid" placeholder="Enter a number">
  						</div>
  						<div class="form-group">
  							<label name="salary" style="color:black;">Salary</label>
    						<input type="text" class="form-control" id="taxid" placeholder="Enter a salary">
  						</div>
  						
  						<div class="form-group">
  							<label name="rank" style="color:black;">Departments</label>
  							<select multiple class="form-control">
  								<option selected disabled id="">Select At Least One</option>
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
  						<div class="checkbox">
    						<label>
      							<input type="checkbox"> Check me out
   							</label>
  						</div>
  						<button type="submit" class="btn btn-default">Submit</button>
					</form>
					</li>
					</ul>
				</div>
			</div>
		</div>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
		<script type="text/javascript" src="../js/bootstrap.min.js"></script> 
	</body>
</html>