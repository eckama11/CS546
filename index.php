<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
 		<title>Login</title>
 		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- StyleSheet -->
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/custom.css" />
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
						<li><a href=#">Problem Description</a></li>
						<li><a href="#">Assumptions</a></li>
					</ul>
				</div>
			</div>
		</div>
	
   		<div class="container padded">
    		<div class="row" >
    			<div class="col-md-2 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;">
            		<form class="form-horizontal" method="post" action="/form/">
               			<fieldset>
                  			<legend>Login</legend>
                   			<div class="control-group">
                        		<label class="control-label" for="id_username">Username</label>
                        		<div class="controls">
                        			<input name="username" maxlength="100" placeholder="Enter your username..." type="text" class="input-large" id="id_username" />
                    			</div>
                    		</div>
                			<div class="control-group">
                        		<label class="control-label" for="id_password">Password</label>
                        		<div class="controls">
                            		<input name="password" maxlength="100" placeholder="Enter your password..." type="password" class="input-large" id="id_password" />
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
	