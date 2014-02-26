<?php
if (!ob_get_level())
	ob_start();

define('CLASSES_DIR', dirname(__FILE__) .'/classes/');

/**
 * Autoloads a class from this folder or a subfolder (if the class is namespaced)
 */
function __autoload( $class ) {
	require_once( CLASSES_DIR . str_replace("\\", "/", $class) .'.php' );
} // __autoload( $class )




