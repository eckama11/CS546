<?php
require_once("common.php");

/*
{ "redirect" : "Admin/Admin.php" }
{ "error" : "Your password was bad" }
*/

$username = @$_POST['username'];
$password = @$_POST['password'];
$page = @$_POST['page'];

try {
    $session = $db->createLoginSession($username, $password);
    session_id($session->sessionId);
    session_start();

    $rv = (Object)[ "redirect" => getLoginRedirect($session, $page) ];
} catch (Exception $ex) {
    $rv = (Object)[ "error" => $ex->getMessage() ];
}

header("Content-Type: application/json");
echo json_encode($rv);