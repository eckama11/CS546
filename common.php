<?php
if (!ob_get_level())
	ob_start();

define('CLASSES_DIR', dirname(__FILE__) .'/classes/');

define('HTTPS', @$_SERVER['HTTPS'] != '' && @$_SERVER['HTTPS'] != 'off');

define('SERVER_URL',
			'http'. (HTTPS ? 's' : '') .'://'.
			$_SERVER['SERVER_NAME'] . (@$_SERVER['SERVER_PORT'] != (HTTPS ? 443 : 80) ? ':'.$_SERVER['SERVER_PORT'] : '') .
			(@$_SERVER['PHP_AUTH_USER'] ? '@'. @$_SERVER['PHP_AUTH_USER'] .':'. @$_SERVER['PHP_AUTH_PW'] : '') .'/');

define('BASE_URL', SERVER_URL . substr(dirname($_SERVER['SCRIPT_NAME']), 1) .'/');



/**
 * Autoloads a class from this folder or a subfolder (if the class is namespaced)
 */
function __autoload( $class ) {
	require_once( CLASSES_DIR . str_replace("\\", "/", $class) .'.php' );
} // __autoload( $class )


$db = new DBInterface("localhost", "u_pay", "u_pay", "u_pay");


/**
 * Initiates a PHP session if a session ID cookie is found.
 */
if (array_key_exists(session_name(), $_COOKIE)) {
    try {
        session_start();
        $loginSession = $db->readLoginSession($_COOKIE[session_name()]);
    } catch (Exception $ex) {
        // Unable to restore session for some reason
        session_destroy();
        unset($loginSession);
    }
}


function doUnauthorizedRedirect() {
    header("Location: ". BASE_URL ."index.php");
} // doUnauthorizedRedirect()


function getLoginRedirect(LoginSession $session) {
    return BASE_URL .
            ($session->isAdministrator
                ? "Admin/Admin.php"
                : "Employee/Employee.php");
} // getLoginRedirect(LoginSession $session)


function doLoginRedirect(LoginSession $session) {
    header("Location: ". getLoginRedirect($session));
    exit;
} // doLoginRedirect(LoginSession $session)

