<?php

// 1. Path constants
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CORE_PATH',   ROOT_PATH . '/core');
define('VIEWS_PATH',  ROOT_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');

// 2. Requires
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Video.php';
require_once APP_PATH . '/services/AuthService.php';
require_once APP_PATH . '/controllers/AuthController.php';
require_once APP_PATH . '/controllers/VideoController.php';
require_once APP_PATH . '/controllers/CommentController.php';

// 3. Start session
session_start();

// 4. Create router
$router = new Router();

// 5. Load routes
require_once CONFIG_PATH . '/routes.php';

// 6. Dispatch
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);