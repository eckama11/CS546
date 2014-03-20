<?php
require_once(dirname(__FILE__)."/common.php");

$prefix = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$page = realpath($prefix . @$_SERVER['PATH_INFO'] .".php");

if (!isset($loginSession))
    doUnauthenticatedRedirect();
else if ((substr($page, 0, strlen($prefix)) != $prefix) || !is_readable($page))
    doUnauthorizedRedirect();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
        <base href="<?php echo htmlentities(BASE_URL); ?>">
 		<title>UPay</title>
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- StyleSheet -->
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/custom.css" />
<script>
function requiredField(elem, errorMsg) {
    var rv = elem.val();
    if (rv == "") {
        elem.tooltip("destroy")
            .addClass("error")
            .data("title", errorMsg)
            .tooltip();
    } else {
        elem.tooltip("destroy")
            .removeClass("error")
            .data("title", "");
    }
    return rv;
}

function showError(message) {
    $("#message").text(message);
    var messageAlert = $("#messageAlert");
    messageAlert.css("z-index", "30000");
    messageAlert.show().delay(3000).fadeOut("slow");
}
</script>
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
						<li><a href="page.php/Employee/MyPay">MyPay</a></li>
						<li><a href="page.php/Employee/MyInfo">MyInfo</a></li>
						<li><a href="page.php/Employee/Pass">Account Settings</a></li>
<?php if ($loginSession->isAdministrator) { ?>
						<li class="dropdown">
          					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
          					<ul class="dropdown-menu">
            					<li><a href="page.php/Admin/EditEmployee">Add Employee</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=modify">Modify Employee</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=activation">Activate/Deactivate</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=paystubs">View Pay Stubs</a></li>
            					<li><a href="page.php/Admin/SelectEmployee?for=password">Change Employee Passwords</a></li>
            					<li><a href="page.php/Admin/Generate">Generate Pay Stubs</a></li>
          					</ul>
        				</li>
<?php } ?>
						<li><a href="logout.php">Logout</a></li>
					</ul>
				</div>
			</div>
		</div>

        <div id="messageAlert" class="alert alert-danger" style="display:none;position:fixed;left:20px;right:20px">
            <span id="message"></span>
        </div>
<?php	require_once($page); ?>
		<!-- JavaScript -->
		<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
		<script src="js/bootstrap.js"></script>
	</body>
</html>
	