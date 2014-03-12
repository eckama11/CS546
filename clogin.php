<?php
	include "classes/DBInterface.php";
	$myclass = new DBInterface("localhost",  "u_pay",  "u_pay", "");
	$sql = mysqli_connect("localhost", "u_pay", "", "u_pay") or die(mysql_error());
	mysqli_select_db($sql, "u_pay") or die(mysqli_error()); 
	
		if (!$_POST['username'] | !$_POST['password'] ) {
 			echo "All Fields Required";
 		}
 		
 		$username = stripslashes($_POST['username']);
		$password = stripslashes($_POST['password']);
		$username = mysqli_real_escape_string($sql, $username);
		$password = mysqli_real_escape_string($sql, $password);
		$query = "SELECT * FROM employee WHERE username='$username' and password='$password'"; 
		$result = mysqli_query($sql, $query);
		
		$count = mysqli_num_rows($result);
		if ($count==1) {
			$rv = $myclass->createLoginSession($username, $password);
			header("location:Admin.php");
		}
		else {
			echo "didn't work";
		}
?>