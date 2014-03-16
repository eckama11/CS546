<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
 		<title>Change Employee Password</title>
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- StyleSheet -->
		<link rel="stylesheet" href="../css/bootstrap.min.css" />
		<link rel="stylesheet" href="../css/custom.css" />
		
	</head>
 
	<body>
		</br>
		<div id="loginDiv" class="col-md-2 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;">
			<form class="form-horizontal" method="post" onsubmit="return doLogin(this)">
				<input type="hidden" name="page" id="page" value="<?php echo htmlentities(@$_SERVER['PATH_INFO']); ?>"/>
				<fieldset>
					<legend style="color:black;">Reset Password</legend>
					<div class="control-group">
						<label class="control-label" for="curPass">Current Password</label>
						<div class="controls">
							<input name="curPass" maxlength="50" placeholder="Current password" type="password" class="input-large" id="curPass" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="newPass1">New Password</label>
						<div class="controls">
							<input name="newPass1" maxlength="50" placeholder="New password" type="password" class="input-large" id="newPass1" />
						</div>
					</div>
					<label class="control-label" for="newPass2">Retype New Password</label>
						<div class="controls">
							<input name="newPass2" maxlength="50" placeholder="New password" type="password" class="input-large" id="newPass2" />
						</div>
					</br>
					<div>
						<input class="btn btn-primary" name="commit" type="submit" value="Reset" />
					</div>
				</fieldset>
			</form>
		</div>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
		<script type="text/javascript" src="../js/bootstrap.min.js"></script> 
	</body>
</html>