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
Router::connect('/profile', array('controller' => 'users', 'action' => 'profile'));


// Dashboard routes
Router::connect('/dashboard', array('controller' => 'users', 'action' => 'dashboard'));

// Super Admin Routes
Router::connect('/users/manage_users', array(
    'controller' => 'users',
    'action' => 'manage_users'
));

Router::connect('/users/add_admin', array(
    'controller' => 'users',
    'action' => 'add_admin'
));

Router::connect('/users/edit/:id', array(
    'controller' => 'users',
    'action' => 'edit'
), array(
    'pass' => array('id'),
    'id' => '[0-9]+'
));

Router::connect('/users/view/:id', array(
    'controller' => 'users',
    'action' => 'view'
), array(
    'pass' => array('id'),
    'id' => '[0-9]+'
));

Router::connect('/users/delete/:id', array(
    'controller' => 'users',
    'action' => 'delete'
), array(
    'pass' => array('id'),
    'id' => '[0-9]+'
));

Router::connect('/users/toggle_status/:id', array(
    'controller' => 'users',
    'action' => 'toggle_status'
), array(
    'pass' => array('id'),
    'id' => '[0-9]+'
));
// Default CakePHP routes
require CAKE . 'Config' . DS . 'routes.php';