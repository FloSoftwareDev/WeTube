<?php

// Front controller — every request to the site ends up here.

// 1. Path constants so we don't have to repeat ../../../ everywhere
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CORE_PATH',   ROOT_PATH . '/core');
define('VIEWS_PATH',  ROOT_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');

// 2. Load all the classes we'll use
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Video.php';
require_once APP_PATH . '/models/Comment.php';
require_once APP_PATH . '/services/AuthService.php';
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/VideoController.php';
require_once APP_PATH . '/controllers/CommentController.php';
require_once APP_PATH . '/controllers/SearchController.php';

// 3. Start the PHP session (so we can remember who's logged in)
session_start();

// 4. Create the router and load the route definitions
$router = new Router();
require_once CONFIG_PATH . '/routes.php';

// 5. Match the current URL to a controller and run it
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
