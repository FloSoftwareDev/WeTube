<?php

/**
 * Application routes.
 *
 * This file is loaded by /public/index.php after the Router is created.
 * The $router variable is already available in scope.
 */

// =====================================================
// Auth routes (public)
// =====================================================
$router->get('/login',     [AuthController::class, 'showLogin']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout',   [AuthController::class, 'logout'])->middleware('auth');

// =====================================================
// Public video routes
// =====================================================
$router->get('/',           [VideoController::class, 'index']);
$router->get('/watch/{id}', [VideoController::class, 'show']);
$router->get('/search',     [VideoController::class, 'search']);

// =====================================================
// Protected video routes
// =====================================================
$router->get('/profile',            [VideoController::class, 'profile'])->middleware('auth');
$router->post('/profile',           [VideoController::class, 'profile'])->middleware('auth');
$router->get('/upload',             [VideoController::class, 'upload'])->middleware('auth');
$router->post('/upload',            [VideoController::class, 'upload'])->middleware('auth');
$router->get('/video/{id}/edit',    [VideoController::class, 'edit'])->middleware('auth');
$router->post('/video/{id}',        [VideoController::class, 'update'])->middleware('auth');
$router->post('/video/{id}/delete', [VideoController::class, 'destroy'])->middleware('auth');
$router->post('/video/{id}/like',   [VideoController::class, 'like'])->middleware('auth');

// =====================================================
// Comment routes (all require login)
// =====================================================
$router->post('/video/{id}/comment',  [CommentController::class, 'store'])->middleware('auth');
$router->post('/comment/{id}/reply',  [CommentController::class, 'reply'])->middleware('auth');
$router->post('/comment/{id}/delete', [CommentController::class, 'destroy'])->middleware('auth');