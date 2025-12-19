<?php
/**
 * Users Controller
 */
App::uses('AppController', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('login', 'signup', 'logout');
    }

    /**
     * Login action - Web interface
     */
 /**
 * Login action - Web interface
 */
public function login() {
    // If already logged in, redirect to dashboard
    if ($this->Auth->user()) {
        $role = $this->Auth->user('role');
        return $this->redirect($this->_getDashboardUrl($role));
    }

    // Get role from POST or session (for preserving after failed login)
    $selectedRole = null;
    if ($this->request->is('post')) {
        // Store selected role in session for form re-population
        if (isset($this->request->data['User']['role'])) {
            $selectedRole = $this->request->data['User']['role'];
            $this->Session->write('login.selected_role', $selectedRole);
        }
    } else {
        // Get role from session (for form re-population after failed login)
        $selectedRole = $this->Session->read('login.selected_role');
    }

    // Pass selected role to view
    $this->set('selectedRole', $selectedRole ?: 'user');

    if ($this->request->is('post')) {
        // Check if this is an API request
        if ($this->RequestHandler->prefers('json') || $this->request->header('Accept') === 'application/json') {
            return $this->_apiLogin();
        }

        $email = isset($this->request->data['User']['email']) ? $this->request->data['User']['email'] : '';
        $password = isset($this->request->data['User']['password']) ? $this->request->data['User']['password'] : '';
        $role = isset($this->request->data['User']['role']) ? $this->request->data['User']['role'] : 'user';
        $accessCode = isset($this->request->data['User']['access_code']) ? $this->request->data['User']['access_code'] : null;
        $rememberMe = isset($this->request->data['User']['remember_me']) ? $this->request->data['User']['remember_me'] : false;

        // Validate required fields
        if (empty($email) || empty($password)) {
            $this->Session->setFlash('Please enter valid Email ID', 'default', array('class' => 'error'));
            return;
        }

        // Find user
        $user = $this->User->find('first', array(
            'conditions' => array('User.email' => $email),
            'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username')
        ));

        if (!$user) {
            $this->Session->setFlash('The email address or password is incorrect. Please try again or reset your password', 'default', array('class' => 'error'));
            return;
        }

        // Verify password
        $passwordHasher = new BlowfishPasswordHasher();
        if (!$passwordHasher->check($password, $user['User']['password'])) {
            $this->Session->setFlash('The email address or password is incorrect. Please try again or reset your password', 'default', array('class' => 'error'));
            return;
        }

        // Verify role matches
        if ($user['User']['role'] !== $role) {
            $this->Session->setFlash('Invalid user role. Please select the correct role from the dropdown.', 'default', array('class' => 'error'));
            return;
        }

        // Verify access code for admin and super_user
        if (in_array($role, array('admin', 'super_user'))) {
            if (empty($accessCode)) {
                $this->Session->setFlash('Access code is required for ' . ucwords(str_replace('_', ' ', $role)), 'default', array('class' => 'error'));
                return;
            }

            if (!$passwordHasher->check($accessCode, $user['User']['access_code'])) {
                $this->Session->setFlash('Invalid access code. Please try again.', 'default', array('class' => 'error'));
                return;
            }
        }

        // Handle remember me
        if ($rememberMe) {
            $token = Security::hash(uniqid(rand(), true));
            $this->User->id = $user['User']['id'];
            $this->User->saveField('remember_token', $token);
            $this->Cookie->write('remember_token', $token, true, '+2 weeks');
        }

        // Clear session role storage on successful login
        $this->Session->delete('login.selected_role');
        
        // Login successful
        $this->Auth->login($user['User']);
        $this->Session->write('Auth.User', $user['User']);
        $this->Session->write('Auth.User.role', $user['User']['role']);

        // Redirect based on role
        return $this->redirect($this->_getDashboardUrl($role));
    }
}

    /**
     * API Login
     */
    private function _apiLogin() {
        $email = $this->request->data['email'];
        $password = $this->request->data['password'];
        $role = isset($this->request->data['role']) ? $this->request->data['role'] : 'user';
        $accessCode = isset($this->request->data['access_code']) ? $this->request->data['access_code'] : null;

        // Validate required fields
        if (empty($email) || empty($password)) {
            return $this->_sendJsonResponse(false, 'Please enter valid Email ID');
        }

        // Find user
        $user = $this->User->find('first', array(
            'conditions' => array('User.email' => $email),
            'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username')
        ));

        if (!$user) {
            return $this->_sendJsonResponse(false, 'The email address or password is incorrect');
        }

        // Verify password
        $passwordHasher = new BlowfishPasswordHasher();
        if (!$passwordHasher->check($password, $user['User']['password'])) {
            return $this->_sendJsonResponse(false, 'The email address or password is incorrect');
        }

        // Verify role matches
        if ($user['User']['role'] !== $role) {
            return $this->_sendJsonResponse(false, 'Invalid user role');
        }

        // Verify access code for admin and super_user
        if (in_array($role, array('admin', 'super_user'))) {
            if (empty($accessCode)) {
                return $this->_sendJsonResponse(false, 'Access code is required');
            }

            if (!$passwordHasher->check($accessCode, $user['User']['access_code'])) {
                return $this->_sendJsonResponse(false, 'Invalid access code');
            }
        }

        // Generate token
        $token = Security::hash(uniqid(rand(), true));
        
        return $this->_sendJsonResponse(true, 'Login successful', array(
            'token' => $token,
            'user' => array(
                'id' => $user['User']['id'],
                'email' => $user['User']['email'],
                'role' => $user['User']['role'],
                'full_name' => $user['User']['full_name'],
                'username' => $user['User']['username']
            ),
            'redirect' => $this->_getDashboardUrl($role)
        ));
    }

    /**
     * Signup action - Web interface
     */
    public function signup() {
        if ($this->request->is('post')) {
            // Check if this is an API request
            if ($this->RequestHandler->prefers('json') || $this->request->header('Accept') === 'application/json') {
                return $this->_apiSignup();
            }

            // Log the incoming data for debugging
            CakeLog::write('debug', 'Signup POST data: ' . print_r($this->request->data, true));

            $this->User->create();
            
            // Get form data
            $data = isset($this->request->data['User']) ? $this->request->data['User'] : array();
            
            // Check if data exists
            if (empty($data)) {
                $this->Session->setFlash('No data received. Please fill in all fields.', 'default', array('class' => 'error'));
                return;
            }
            
            $userType = isset($data['user_type']) ? $data['user_type'] : 'user';
            
            // Validate required fields
            if (empty($data['full_name']) || empty($data['username']) || empty($data['email']) || 
                empty($data['confirm_email']) || empty($data['password']) || empty($data['confirm_password'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }
            
            // Validate passwords match
            if ($data['password'] !== $data['confirm_password']) {
                $this->Session->setFlash('Passwords do not match. Please try again.', 'default', array('class' => 'error'));
                return;
            }
            
            // Validate emails match
            if ($data['email'] !== $data['confirm_email']) {
                $this->Session->setFlash('Email addresses do not match. Please try again.', 'default', array('class' => 'error'));
                return;
            }
            
            // Validate access code for admin
            if ($userType === 'admin') {
                if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                    $this->Session->setFlash('Invalid access code. Please enter the correct code.', 'default', array('class' => 'error'));
                    return;
                }
                
                // Hash the access code
                $passwordHasher = new BlowfishPasswordHasher();
                $data['access_code'] = $passwordHasher->hash('0000');
            } else {
                $data['access_code'] = null;
            }
            
            // Set role
            $data['role'] = $userType;
            
            // Remove confirm fields before saving
            unset($data['confirm_password']);
            unset($data['confirm_email']);
            unset($data['user_type']);
            
            // Prepare data for saving
            $saveData = array('User' => $data);
            
            CakeLog::write('debug', 'Data to save: ' . print_r($saveData, true));
            
            if ($this->User->save($saveData)) {
                $this->Session->setFlash('Account created successfully! Please login.', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'login'));
            } else {
                $errors = $this->User->validationErrors;
                CakeLog::write('debug', 'Validation errors: ' . print_r($errors, true));
                
                $errorMsg = 'Registration failed. Please check the form and try again.';
                
                if (isset($errors['email'])) {
                    $errorMsg = 'This email address is already registered.';
                } elseif (isset($errors['username'])) {
                    $errorMsg = 'This username is already taken.';
                } elseif (isset($errors['full_name'])) {
                    $errorMsg = $errors['full_name'][0];
                }
                
                $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            }
        }
    }

    /**
     * API Signup
     */
    private function _apiSignup() {
        $this->User->create();
        
        $data = $this->request->data;
        $userType = isset($data['user_type']) ? $data['user_type'] : 'user';
        
        // Validate access code for admin
        if ($userType === 'admin') {
            if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                return $this->_sendJsonResponse(false, 'Invalid access code');
            }
            
            // Hash the access code
            $passwordHasher = new BlowfishPasswordHasher();
            $data['access_code'] = $passwordHasher->hash('0000');
        } else {
            $data['access_code'] = null;
        }
        
        // Set role
        $data['role'] = $userType;
        
        // Validate passwords match
        if ($data['password'] !== $data['confirm_password']) {
            return $this->_sendJsonResponse(false, 'Passwords do not match');
        }
        
        // Validate emails match
        if ($data['email'] !== $data['confirm_email']) {
            return $this->_sendJsonResponse(false, 'Email addresses do not match');
        }
        
        // Remove confirm fields
        unset($data['confirm_password']);
        unset($data['confirm_email']);
        unset($data['user_type']);
        
        if ($this->User->save($data)) {
            return $this->_sendJsonResponse(true, 'Account created successfully', array(
                'redirect' => '/login'
            ));
        } else {
            $errors = $this->User->validationErrors;
            $errorMsg = 'Registration failed';
            
            if (isset($errors['email'])) {
                $errorMsg = 'This email address is already registered';
            } elseif (isset($errors['username'])) {
                $errorMsg = 'This username is already taken';
            }
            
            return $this->_sendJsonResponse(false, $errorMsg);
        }
    }

    /**
     * Logout action
     */
    public function logout() {
        $this->Cookie->delete('remember_token');
        return $this->redirect($this->Auth->logout());
    }

    /**
     * Dashboard action
     */
    public function dashboard() {
        if (!$this->Auth->user()) {
            return $this->redirect(array('action' => 'login'));
        }

        $role = $this->Auth->user('role');
        $this->set('role', $role);
        $this->set('user', $this->Auth->user());

        // Load role-specific view
        switch ($role) {
            case 'admin':
                $this->render('dashboard_admin');
                break;
            case 'super_user':
                $this->render('dashboard_super_user');
                break;
            case 'guest':
                $this->render('dashboard_guest');
                break;
            default:
                $this->render('dashboard_user');
        }
    }

    /**
     * Get dashboard URL based on role
     */
    private function _getDashboardUrl($role) {
        return array('controller' => 'users', 'action' => 'dashboard');
    }
}