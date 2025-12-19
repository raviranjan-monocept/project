<?php
/**
 * Routes Configuration
 */

// API Routes
Router::mapResources('users');
Router::parseExtensions('json');

// API specific routes
Router::connect('/api/users/login', array(
    'controller' => 'users',
    'action' => 'login',
    'plugin' => null,
    '[method]' => 'POST',
    'ext' => 'json'
));

Router::connect('/api/users/signup', array(
    'controller' => 'users',
    'action' => 'signup',
    'plugin' => null,
    '[method]' => 'POST',
    'ext' => 'json'
));

// Default routes
Router::connect('/', array('controller' => 'users', 'action' => 'login'));
Router::connect('/login', array('controller' => 'users', 'action' => 'login'));
Router::connect('/signup', array('controller' => 'users', 'action' => 'signup'));
Router::connect('/logout', array('controller' => 'users', 'action' => 'logout'));

// Dashboard routes
Router::connect('/dashboard', array('controller' => 'users', 'action' => 'dashboard'));

// Default CakePHP routes
require CAKE . 'Config' . DS . 'routes.php';