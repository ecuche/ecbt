<?php
declare(strict_types=1);

define("ROOT_PATH", dirname(__DIR__));

spl_autoload_register(function(string $class_name){ 
    require_once ROOT_PATH."/src/".str_replace("\\", "/", $class_name).".php";
});

$dotenv = new Framework\Dotenv;
$dotenv->load(ROOT_PATH."/.env");

set_error_handler("Framework\ErrorHandler::handleError");
set_exception_handler("Framework\ErrorHandler::handleException");

session_start();
$routes = require_once ROOT_PATH."/config/routes.php";
$container = require_once ROOT_PATH."/config/services.php";
$middleware = require_once ROOT_PATH."/config/middleware.php"; 

$dispatcher = new Framework\Dispatcher($router, $container, $middleware);
$request = Framework\Request::createFromGlobals();
$response = $dispatcher->handle($request);
$response->send();