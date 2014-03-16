<!DOCTYPE html>
<html lang="en">
	<head>
	<!-- Le styles -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>
	</head>
 
	<body>
		<div class="container col-md-6 col-md-offset-3">
			<div id="content" >
				<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
					<li class="active"><a href="#basic" data-toggle="tab">Basic</a></li>
					<li><a href="#orange" data-toggle="tab">Rank/Department</a></li>
					<li><a href="#taxes" data-toggle="tab">Taxes</a></li>
					<li><a href="#submit" data-toggle="tab">Submit</a></li>
				</ul>
				<div id="my-tab-content" class="tab-content">
					<div class="tab-pane active" id="basic">
						<form role="form">
							<div class="form-group">
								<label name="name" style="color:black;">Name</label>
								<input type="text" class="form-control" id="name" placeholder="Enter name">
							</div>
						<div class="form-group">
							<label name="address" style="color:black;">Address</label></br>
							<textarea class="form-control" rows="5" placeholder="Enter Address"></textarea>
						</div>
					</div>
		
					<div class="tab-pane" id="orange">
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
					</div>
					<div class="tab-pane" id="taxes">
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
					</div>
					<div class="tab-pane" id="submit">
						<p style="color:yellow;">Clicking on Add Employee will add </br>
						this employee. Please make sure all </br>
						information is correct before submitting.</p>
						<button type="submit" class="btn btn-default">Add Employee</button>
						</form>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('#tabs').tab();
				});
			</script>    
		</div> <!-- container -->
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
	</body>
</html>