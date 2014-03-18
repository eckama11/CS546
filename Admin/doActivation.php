<?php
require_once(dirname(__FILE__)."/../common.php");

// If the form was posted, verify the old password and update the password if the 2 new passwords match and are acceptable
$employeeId = @$_POST['employeeId'];

$rv = (Object)[];
try {
    if (!isset($loginSession))
        throw new Exception("You do not have sufficient access to perform this action");

    // Update the employee
    // Needs to be done

    $rv->success = true;
} catch (Exception $ex) {
    $rv->error = $ex->getMessage();
} // try/catch

echo json_encode($rv);