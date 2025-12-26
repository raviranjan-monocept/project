<?php
App::uses('Controller', 'Controller');

class AppController extends Controller {
    
    public $components = array(
        'Session',
        'RequestHandler',
        'Auth' => array(
            'authenticate' => array(
                'Form' => array(
                    'passwordHasher' => 'Blowfish',
                    'fields' => array('username' => 'email')
                )
            ),
            'loginAction' => array(
                'controller' => 'users',
                'action' => 'login'
            ),
            'loginRedirect' => array(
                'controller' => 'users',
                'action' => 'dashboard'
            ),
            'logoutRedirect' => array(
                'controller' => 'users',
                'action' => 'login'
            ),
            'authError' => 'Please login to access this page.',
            'authorize' => array('Controller')
        )
    );

    public function isAuthorized($user) {
        return true;
    }
}
