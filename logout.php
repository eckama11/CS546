<?php
require_once("common.php");

if ($loginSession != null) {
    $db->destroyLoginSession($loginSession);
    session_destroy();
}

header("Location: ". BASE_URL . "index.php");