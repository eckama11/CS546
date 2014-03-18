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
    while (@ob_end_clean());
    header("Location: ". BASE_URL ."page.php/unauthorized");
    exit;
} // doUnauthorizedRedirect()


function doUnauthenticatedRedirect() {
    $pathInfo = '';
    if (array_key_exists('PATH_INFO', $_SERVER)) {
        $pathInfo = $_SERVER['PATH_INFO'];
        if ($pathInfo{0} != '/')
            $pathInfo = '/'. $pathInfo;
    }

    while (@ob_end_clean());
    header("Location: ". BASE_URL ."index.php". $pathInfo);
    exit;
} // doUnauthenticatedRedirect()


function getLoginRedirect(LoginSession $session, $page) {
    if (!$page)
        $page = ($session->isAdministrator
                    ? "/Admin/Admin"
                    : "/Employee/MyInfo");

    if ($page{0} != '/')
        $page = '/'. $page;

    return BASE_URL ."page.php". $page;
} // getLoginRedirect(LoginSession $session)


function doLoginRedirect(LoginSession $session, $page) {
    while (@ob_end_clean());
    header("Location: ". getLoginRedirect($session, $page));
    exit;
} // doLoginRedirect(LoginSession $session, $page)


function handleDBException($ex) {
    echo '
        <div class="alert alert-danger" style="vertical-align:top;position:absolute;width:100%">
            <span class="glyphicon glyphicon-exclamation-sign" style="color:red;font-size:30px"></span>
            <div style="display:inline-block;padding-left:10px;">
                An error occurred while processing your request.<br/>'.
                htmlentities($ex->getMessage()) .
           '</div>
        </div>
        ';
} // handleDBException($ex)


