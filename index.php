<?php
require_once("common.php");
if (isset($loginSession))
    doLoginRedirect($loginSession);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
 		<title>Login</title>
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- StyleSheet -->
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/custom.css" />
		<style>
        input { max-width: 100%; }
        .error { border: 1px solid #b94a48!important; background-color: #fee!important; }
        </style>
<script type="text/javascript">
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
    $("#messageAlert").show().delay(3000).fadeOut("slow");
}

function doLogin(form) {
    var username = requiredField($(form.elements.username), "You must enter a username");
    var password = requiredField($(form.elements.password), "You must enter a password");
    if ((username == "") || (password == "")) {
        showError("You must enter both a username and a password.");
        return false;
    }

    $("#loginForm").hide();
    $("#spinner").show();

    $.ajax({
        "type" : "POST",
        "url" : "doLogin.php",
        "data" : $(form).serialize(),
        "dataType" : "json"
        })
        .done(function(data) {
            if (data.error != null) {
                $("#loginForm").show();
                $("#spinner").hide();
                showError(data.error);
            } else
                document.location.href = data.redirect;
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            console.log("Error: "+ textStatus +" (errorThrown="+ errorThrown +")");
            console.log(jqXHR);
        })
    return false;
} // doLogin
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
					</ul>
				</div>
			</div>
		</div>

        <div id="messageAlert" class="alert alert-danger" style="display:none;position:absolute;width:100%">
            <span id="message"></span>
        </div>

   		<div class="container padded">
    		<div class="row" >
    			<div id="spinner" class="col-md-2 col-md-offset-5" style="padding-bottom:10px;text-align:center;display:none">
                    <div style="color:black;padding-bottom:32px;">Authenticating...</div>
                    <img src="spinner.gif">
                </div>
    			<div id="loginForm" class="col-md-2 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;">
            		<form class="form-horizontal" method="post" onsubmit="return doLogin(this)">
               			<fieldset>
                  			<legend style="color:black;">Login</legend>
                   			<div class="control-group">
                        		<label class="control-label" for="username" style="color:black;">Username</label>
                        		<div class="controls">
                        			<input style="color:black;" name="username" maxlength="50" placeholder="Enter your username..." type="text" class="input-large" id="username" />
                    			</div>
                    		</div>
                			<div class="control-group">
                        		<label class="control-label" for="password" style="color:black;">Password</label>
                        		<div class="controls">
                            		<input style="color:black;" name="password" maxlength="50" placeholder="Enter your password..." type="password" class="input-large" id="password" />
                        		</div>
                        	</div>
                        	</br>
                        	<div>
                        		<input class="btn btn-primary" name="commit" type="submit" value="Log In" />
                        	</div>
                		</fieldset>
           			</form>
        		</div>
    		</div>
		</div>

		<!-- JavaScript -->
		<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
		<script src="js/bootstrap.js"></script>
	</body>
</html>
	