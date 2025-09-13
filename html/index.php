<?php
/**
 * Entry point for the Composer Bundler web application.
 *
 * Initializes the environment, loads Composer autoloading, 
 * and delegates all request handling to the Router.
 *
 * @project ComposerBundler
 * @author Armin Mansouri
 */

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../vendor/autoload.php');

use ComposerBundler\Router;

$router = new Router($_GET, $_POST);
$router->handle();
