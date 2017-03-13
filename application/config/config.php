<?php

define('ENVIRONMENT', 'development');

if (ENVIRONMENT == 'development' || ENVIRONMENT == 'dev') {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}else{
	error_reporting(0);
	ini_set("display_errors", 0);
}

/* all config*/
define('URL_PUBLIC_FOLDER', '/');//if we work on subfolder use '/subfolder'
define('UPLOAD_FOLDER', 'uploads');//if we work on subfolder use '/subfolder'
define('URL_PROTOCOL', '//');//  auto(//) or (http://) or (https://)
define('URL_DOMAIN', $_SERVER['HTTP_HOST']);
define( 'URL_SUB_FOLDER', str_replace( URL_PUBLIC_FOLDER, '', dirname($_SERVER['SCRIPT_NAME']) ) );
define('URL', str_replace( '\\', '/', URL_PROTOCOL . URL_DOMAIN . URL_SUB_FOLDER) );
define('URL_UPLOAD', URL . UPLOAD_FOLDER);

/**
 * Configuration for: Database
 * Database credentials, database type etc.
 */
define('DB_TYPE', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'test');
define('DB_USER', 'main');
define('DB_PASS', '135qet');
define('DB_CHARSET', 'utf8');
