<?php
require_once(dirname(__FILE__)."/../common.php");

// If the form was posted, verify the old password and update the password if the 2 new passwords match and are acceptable
$name = @$_POST['name'];
$address = @$_POST['address'];
$rank = @$_POST['rank'];
$department = @$_POST['department'];
$taxid = @$_POST['taxid'];
$numDeductions = @$_POST['numDeductions'];
$salary = @$_POST['salary'];

$rv = (Object)[];
try {
    if (!isset($loginSession))
        throw new Exception("You do not have sufficient access to perform this action");
	
	// Verify taxid is 9 digits long
	//if (preg_match("/[0-9]{9}/", $taxid))
	//	throw new Exception("The tax id number should be 9 digits.");

	// Verify numdDeducitons is a number
	if (preg_match("/[0-9]+/", $numDeductions))
		throw new Exception("The number of deductions must be a number.");
		
	//Verify salary is a real number unsigned
    if (preg_match("/[0-9]{1,9}([.][0-9]{2})?/", $salary))
    	throw new Exception("The salary must be a positve number.");
    
    // add employee
	//this part needs to be added


    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);
