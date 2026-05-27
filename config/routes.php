<?php

/**
 * Application routes.
 *
 * Loaded by /public/index.php. The $router variable already exists.
 * The third argument 'auth' means: only logged-in users can use this route.
 */

// Auth (public)
$router->get('/login',     [AuthController::class, 'showLogin']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout',   [AuthController::class, 'logout'], 'auth');

// Public video pages
$router->get('/',           [VideoController::class, 'index']);
$router->get('/watch/{id}', [VideoController::class, 'show']);
$router->get('/search',     [VideoController::class, 'search']);

// Pages that require login
$router->get('/profile',         [VideoController::class, 'profile'], 'auth');
$router->post('/profile',        [VideoController::class, 'profile'], 'auth');
$router->get('/upload',          [VideoController::class, 'upload'], 'auth');
$router->post('/upload',         [VideoController::class, 'upload'], 'auth');
$router->get('/video/{id}/edit', [VideoController::class, 'edit'], 'auth');
$router->post('/video/{id}',     [VideoController::class, 'update'], 'auth');

// Comments (require login)
$router->post('/video/{id}/comment',  [CommentController::class, 'store'], 'auth');
$router->post('/comment/{id}/reply',  [CommentController::class, 'reply'], 'auth');
$router->post('/comment/{id}/delete', [CommentController::class, 'destroy'], 'auth');
