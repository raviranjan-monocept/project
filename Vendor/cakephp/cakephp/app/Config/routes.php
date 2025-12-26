<?php
// Enable JSON extension parsing
Router::parseExtensions('json');

// Default route - redirect to login
Router::connect('/', array(
    'controller' => 'users',
    'action' => 'login'
));

// Web routes - Users
Router::connect('/users/login', array(
    'controller' => 'users',
    'action' => 'login'
));

Router::connect('/users/register', array(
    'controller' => 'users',
    'action' => 'register'
));

Router::connect('/users/dashboard', array(
    'controller' => 'users',
    'action' => 'dashboard'
));

Router::connect('/users/logout', array(
    'controller' => 'users',
    'action' => 'logout'
));

// API routes - Users
Router::connect('/api/users/register', array(
    'controller' => 'users',
    'action' => 'api_register'
));

Router::connect('/api/users/token', array(
    'controller' => 'users',
    'action' => 'token'
));

// API routes - Policies
Router::connect('/api/policies', array(
    'controller' => 'policies',
    'action' => 'index'
), array(
    '[method]' => 'GET'
));

Router::connect('/api/policies', array(
    'controller' => 'policies',
    'action' => 'add'
), array(
    '[method]' => 'POST'
));

Router::connect('/api/policies/:id', array(
    'controller' => 'policies',
    'action' => 'view'
), array(
    'pass' => array('id'),
    '[method]' => 'GET',
    'id' => '[0-9]+'
));

Router::connect('/api/policies/:id', array(
    'controller' => 'policies',
    'action' => 'edit'
), array(
    'pass' => array('id'),
    '[method]' => 'PUT',
    'id' => '[0-9]+'
));

Router::connect('/api/policies/:id', array(
    'controller' => 'policies',
    'action' => 'delete'
), array(
    'pass' => array('id'),
    '[method]' => 'DELETE',
    'id' => '[0-9]+'
));

Router::connect('/api/policies/:id/toggle-status', array(
    'controller' => 'policies',
    'action' => 'toggle_status'
), array(
    'pass' => array('id'),
    '[method]' => 'POST',
    'id' => '[0-9]+'
));

// Default CakePHP routes
Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

require CAKE . 'Config' . DS . 'routes.php';
