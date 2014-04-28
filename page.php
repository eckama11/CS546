<?php
require_once(dirname(__FILE__)."/common.php");

$prefix = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$page = realpath($prefix . @$_SERVER['PATH_INFO'] .".php");

if (!isset($loginSession))
    doUnauthenticatedRedirect();
else if ((substr($page, 0, strlen($prefix)) != $prefix) || !is_readable($page))
    doUnauthorizedRedirect();

ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
        <base href="<?php echo htmlentities(BASE_URL); ?>">
 		<title>UPay</title>
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- StyleSheet -->
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/bootstrap-datepicker.css" />
		<link rel="stylesheet" href="css/custom.css" />
        <script data-main="js/main" src="js/require.js"></script>
<script>
function showError(message) {
    $("#message").text(message);
    var messageAlert = $("#messageAlert");
    messageAlert.css("z-index", "30000");
    messageAlert.show().delay(5000).fadeOut("slow");
}
</script>
	</head>
 
	<body>
		<div class="navbar navbar-inverse navbar-static-top">
			<div class="container">
				<a href="#" class="navbar-brand">
				<span class="glyphicon glyphicon-usd"></span>
				UPay Solutions</a>
				<button class="navbar-toggle" data-toggle="collapse" data-target=".navHeaderCollapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<div class="collapse navbar-collapse navHeaderCollapse">
					<ul class="nav navbar-nav navbar-right">
						<li><a href="page.php/Employee/MyPay">MyPay</a></li>
						<li><a href="page.php/Employee/MyInfo">MyInfo</a></li>
						<li><a href="page.php/Employee/Pass">Account Settings</a></li>
<?php if ($loginSession->isAdministrator) { ?>
						<li class="dropdown">
          					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
          					<span class="glyphicon glyphicon-stats"></span>
          					Info <b class="caret"></b></a>
          					<ul class="dropdown-menu" role="menu">
            					<li><a href="page.php/Admin/SelectEmployee?for=info">View Employee Info</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=paystubs">View Pay Stubs</a></li>
                                <li class="divider"></li>
            					<li><a href="page.php/Admin/SelectProject?for=info">View Project Report</a></li>
                                <li class="divider"></li>
            					<li><a href="page.php/Admin/TaxTable">Tax Table</a></li>
            					<li><a href="page.php/Admin/Ranks">Ranks</a></li>
            					<li><a href="page.php/Admin/Departments">Departments</a></li>
          					</ul>
        				</li>
						<li class="dropdown">
          					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-wrench"></span> Project <b class="caret"></b></a>
          					<ul class="dropdown-menu" role="menu">
            					<li><a href="page.php/Admin/EditProject">Add Project</a></li>
            					<li><a href="page.php/Admin/SelectProject?for=modifyInfo">Modify Project Info</a></li>
            					<li><a href="page.php/Admin/SelectProject?for=modifyEmployees">Modify Project Employees</a></li>
          					</ul>
                        </li>
						<li class="dropdown">
          					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> Employee <b class="caret"></b></a>
          					
          					<ul class="dropdown-menu" role="menu">
            					<li><a href="page.php/Admin/EditEmployee">Add Employee</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=modifyInfo">Modify Employee Info</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=modifySalary">Modify Employee Salary</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=password">Change Employee Password</a></li>
                                <li class="divider"></li>
            					<li><a href="page.php/Admin/Generate">Generate Pay Stubs</a></li>
          					</ul>
        				</li>
<?php } ?>
						<li><a href="logout.php">
						<span class="glyphicon glyphicon-log-out"></span>
						Logout</a></li>
					</ul>
				</div>
			</div>
		</div>

        <div id="messageAlert" class="alert alert-danger" style="display:none;position:fixed;left:20px;right:20px">
            <span id="message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>

<?php	require_once($page); ?>
	</body>
</html>
<?php
    ob_end_flush();
?>