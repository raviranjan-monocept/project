<?php
/**
 * Application Controller
 */
App::uses('Controller', 'Controller');

class AppController extends Controller {

    public $components = array(
        'Session',
        'Cookie',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'users', 'action' => 'dashboard'),
            'logoutRedirect' => array('controller' => 'users', 'action' => 'login'),
            'authenticate' => array(
                'Form' => array(
                    'fields' => array('username' => 'email'),
                    'passwordHasher' => 'Blowfish'
                )
            ),
            'authorize' => array('Controller'),
            'unauthorizedRedirect' => array('controller' => 'users', 'action' => 'login'),
            'authError' => 'You are not authorized to access that location.'
        ),
        'Security',
        'RequestHandler'
    );

    public $helpers = array('Html', 'Form', 'Session');
 public function beforeRender() {
        parent::beforeRender();

        // Always define $user for views/elements
        $loggedIn = $this->Auth->user();   // array or null
        $this->set('user', $loggedIn);     // can be null, but never undefined
    }
    public function beforeFilter() {
        // Set layout for API requests
        if ($this->RequestHandler->prefers('json')) {
            $this->layout = 'ajax';
        }

        // Configure Auth component
        $this->Auth->allow();
        
        // Security settings
        $this->Security->csrfCheck = false;
        $this->Security->validatePost = false;
        $this->Security->csrfUseOnce = false;
    }

    public function isAuthorized($user) {
        // Default: all logged in users are authorized
        return true;
    }

    protected function _sendJsonResponse($success, $message, $data = array()) {
        $this->autoRender = false;
        $this->response->type('json');
        $response = array(
            'success' => $success,
            'message' => $message,
            'data' => $data
        );
        $this->response->body(json_encode($response));
        return $this->response;
    }
}