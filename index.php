<?php

/**
 * Inital start
 * simple MVC Tasker CRUD
 */

// APP or NAMESPACE
// set a constant that holds the project's folder path, like "/var/www/".
// DIRECTORY_SEPARATOR adds a slash to the end of the path
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
// Setting APP where application stores, like "/var/www/application/".
define('APP', ROOT . 'application' . DIRECTORY_SEPARATOR);

//if added composer, it will be automatically added
if (file_exists(ROOT . 'vendor/autoload.php')) {
    require ROOT . 'vendor/autoload.php';
}

// load application config (error reporting etc.)
require APP . 'config/config.php';

// PDO-debug, a simple function that shows the SQL query (when using PDO).
if (ENVIRONMENT == 'development' || ENVIRONMENT == 'dev') {
    require APP . 'lib/Helper.php';
}

// load application class
require APP . 'core/application.php';
require APP . 'core/controller.php';

// start the application
$app = new Application();

