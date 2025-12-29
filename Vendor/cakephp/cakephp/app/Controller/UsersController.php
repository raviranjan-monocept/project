<?php
/**
 * Users Controller - Comprehensive Web & REST API Implementation
 * 
 * This controller handles all user-related operations including:
 * - Web-based authentication (login/logout)
 * - User registration (both web and API)
 * - User dashboard with role-based views
 * - User profile management with image upload
 * - Admin management (add/edit/delete admins)
 * - API token generation for authenticated requests
 * 
 * Supports multiple user roles: user, guest, admin, super_user
 */
App::uses('AppController', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class UsersController extends AppController {

    // Enable RequestHandler component for JSON/XML responses
    public $components = array('RequestHandler');

    /**
     * Runs before every action in this controller
     * Sets up authentication rules and security settings
     */
    public function beforeFilter() {
        parent::beforeFilter();
        
        // Allow public access to these actions without requiring login
        $this->Auth->allow('register', 'api_register', 'token', 'login');
        
        // Disable CSRF protection for API endpoints only
        if (in_array($this->request->params['action'], array('api_register', 'token'))) {
            if (isset($this->Security)) {
                $this->Security->csrfCheck = false;
                $this->Security->validatePost = false;
            }
        }
    }

    /**
     * Check if logged-in user has super_user role
     */
    private function _isSuperAdmin() {
        return ($this->Auth->user('role') === 'super_user');
    }

    /**
     * Check if logged-in user is admin or super_user
     */
    private function _isAdminOrAbove() {
        $role = $this->Auth->user('role');
        return in_array($role, array('admin', 'super_user'));
    }

    /**
     * Helper function to send JSON responses for API endpoints
     */
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false;
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }

    /**
     * Get request data from either JSON body or form POST
     */
    private function _getRequestData() {
        $data = $this->request->input('json_decode', true);
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }

    /**
     * WEB: Login Page and Authentication Handler
     */
    public function login() {
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is('post')) {
            $email = !empty($this->request->data['User']['email']) ? $this->request->data['User']['email'] : '';
            $password = !empty($this->request->data['User']['password']) ? $this->request->data['User']['password'] : '';
            $role = !empty($this->request->data['User']['role']) ? $this->request->data['User']['role'] : 'user';
            $accessCode = !empty($this->request->data['User']['access_code']) ? $this->request->data['User']['access_code'] : null;

            if (empty($email) || empty($password)) {
                $this->Session->setFlash('Please enter email and password', 'default', array('class' => 'error'));
                return;
            }

            $user = $this->User->find('first', array(
                'conditions' => array('User.email' => $email),
                'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username', 'status', 'image')
            ));

            if (!$user) {
                $this->Session->setFlash('Invalid email or password', 'default', array('class' => 'error'));
                return;
            }

            if ($user['User']['status'] == 0) {
                $this->Session->setFlash('Your account has been deactivated', 'default', array('class' => 'error'));
                return;
            }

            $passwordHasher = new BlowfishPasswordHasher();
            if (!$passwordHasher->check($password, $user['User']['password'])) {
                $this->Session->setFlash('Invalid email or password', 'default', array('class' => 'error'));
                return;
            }

            if ($user['User']['role'] !== $role) {
                $this->Session->setFlash('Invalid role selected', 'default', array('class' => 'error'));
                return;
            }

            if (in_array($role, array('admin', 'super_user'))) {
                if (empty($accessCode)) {
                    $this->Session->setFlash('Access code is required for ' . $role, 'default', array('class' => 'error'));
                    return;
                }

                if (!$passwordHasher->check($accessCode, $user['User']['access_code'])) {
                    $this->Session->setFlash('Invalid access code', 'default', array('class' => 'error'));
                    return;
                }
            }

            $this->Auth->login($user['User']);
            $this->Session->setFlash('Login successful!', 'default', array('class' => 'success'));
            return $this->redirect(array('action' => 'dashboard'));
        }
    }

    /**
     * WEB: Registration Page and Handler
     */
    public function register() {
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is('post')) {
            $data = isset($this->request->data['User']) ? $this->request->data['User'] : array();

            if (empty($data['full_name']) || empty($data['username']) || 
                empty($data['email']) || empty($data['password'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
                $this->Session->setFlash('Passwords do not match.', 'default', array('class' => 'error'));
                return;
            }

            if (isset($data['confirm_email']) && $data['email'] !== $data['confirm_email']) {
                $this->Session->setFlash('Email addresses do not match.', 'default', array('class' => 'error'));
                return;
            }

            $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

            if ($userType === 'admin') {
                if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                    $this->Session->setFlash('Invalid access code.', 'default', array('class' => 'error'));
                    return;
                }
                
                $passwordHasher = new BlowfishPasswordHasher();
                $data['access_code'] = $passwordHasher->hash('0000');
            } else {
                $data['access_code'] = null;
            }

            $data['role'] = $userType;
            $data['status'] = 1;

            unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

            $this->User->create();
            if ($this->User->save(array('User' => $data))) {
                $this->Session->setFlash('Account created successfully! Please login.', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'login'));
            } else {
                $errors = $this->User->validationErrors;
                $errorMsg = 'Registration failed. Please check the form.';
                
                if (isset($errors['email'])) {
                    $errorMsg = 'This email address is already registered.';
                } elseif (isset($errors['username'])) {
                    $errorMsg = 'This username is already taken.';
                }

                $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            }
        }
    }

    /**
     * WEB: User Profile Page with Edit Functionality
     */
    public function profile() {
        if (!$this->Auth->user()) {
            $this->Session->setFlash('Please login to access your profile', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'login'));
        }

        // Use profile layout
        $this->layout = 'profile';

        $userId = $this->Auth->user('id');
        $user = $this->User->findById($userId);
        
        if (!$user) {
            $this->Session->setFlash('User not found', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        
        if ($this->request->is(array('post', 'put'))) {
            
            if (!empty($this->request->data['User']['image']['name'])) {
                $file = $this->request->data['User']['image'];
                $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
                $maxSize = 2097152;
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize && $file['error'] == 0) {
                    $uploadDir = WWW_ROOT . 'img' . DS . 'users' . DS;
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        if (!empty($user['User']['image']) && 
                            $user['User']['image'] !== 'default-user.jpg' && 
                            $user['User']['image'] !== 'user2-160x160.jpg') {
                            $oldImagePath = WWW_ROOT . 'img' . DS . $user['User']['image'];
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath);
                            }
                        }
                        
                        $this->request->data['User']['image'] = 'users/' . $newFilename;
                    } else {
                        $this->Session->setFlash('Failed to upload image. Please try again.', 'default', array('class' => 'error'));
                        $this->request->data['User']['image'] = $user['User']['image'];
                    }
                } else {
                    $errorMsg = 'Invalid file. ';
                    if ($file['size'] > $maxSize) {
                        $errorMsg .= 'File size must be less than 2MB. ';
                    }
                    if (!in_array($file['type'], $allowedTypes)) {
                        $errorMsg .= 'Only JPG, PNG, and GIF files are allowed.';
                    }
                    
                    $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
                    $this->request->data['User']['image'] = $user['User']['image'];
                }
            } else {
                $this->request->data['User']['image'] = $user['User']['image'];
            }
            
            $this->User->id = $userId;
            $this->User->validate = array();
            
            if ($this->User->save($this->request->data, array('validate' => false))) {
                $this->Session->setFlash('Profile updated successfully!', 'default', array('class' => 'success'));
                
                $updatedUser = $this->User->findById($userId);
                $this->Session->write('Auth.User', $updatedUser['User']);
                
                return $this->redirect(array('action' => 'profile'));
            } else {
                $errors = $this->User->validationErrors;
                $errorMsg = 'Failed to update profile. ';
                
                if (isset($errors['email'])) {
                    $errorMsg .= 'Email already exists. ';
                }
                if (isset($errors['username'])) {
                    $errorMsg .= 'Username already taken. ';
                }
                
                $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            }
        }
        
        if (empty($this->request->data)) {
            $this->request->data = $user;
        }
        
        $this->set('user', $user);
        $this->set('title_for_layout', 'Edit Profile');
    }

    /**
     * WEB: Role-Based Dashboard
     */
    public function dashboard() {
        if (!$this->Auth->user()) {
            return $this->redirect(array('action' => 'login'));
        }

        // Don't use layout - dashboard pages are self-contained
        $this->layout = false;

        $role = $this->Auth->user('role');
        $this->set('role', $role);
        $this->set('user', $this->Auth->user());

        if (in_array($role, array('admin', 'super_user'))) {
            $this->loadModel('Policy');

            $stats = array(
                'total' => $this->Policy->find('count'),
                'active' => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'active')
                )),
                'draft' => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'draft')
                )),
                'archived' => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'archived')
                ))
            );
            $this->set(compact('stats'));

            $policies = $this->Policy->find('all', array(
                'order' => array('Policy.created' => 'DESC'),
                'limit' => 5
            ));
            $this->set(compact('policies'));
        }

        if ($role === 'super_user') {
            $totalAdmins = $this->User->find('count', array(
                'conditions' => array('User.role' => array('admin', 'super_user'))
            ));

            $activeAdmins = $this->User->find('count', array(
                'conditions' => array(
                    'User.role' => array('admin', 'super_user'),
                    'User.status' => 1
                )
            ));

            $inactiveAdmins = $totalAdmins - $activeAdmins;

            $superAdmins = $this->User->find('count', array(
                'conditions' => array('User.role' => 'super_user')
            ));

            $totalUsers = $this->User->find('count', array(
                'conditions' => array('User.role' => array('user', 'guest'))
            ));

            $admins = $this->User->find('all', array(
                'conditions' => array('User.role' => array('admin', 'super_user')),
                'fields' => array(
                    'User.id', 'User.full_name', 'User.username', 'User.email',
                    'User.role', 'User.status', 'User.modified'
                ),
                'order' => array('User.id' => 'ASC')
            ));

            $this->set(compact(
                'totalAdmins', 'activeAdmins', 'inactiveAdmins',
                'superAdmins', 'totalUsers', 'admins'
            ));
        }

        switch ($role) {
            case 'super_user':
                $this->render('dashboard_super_user');
                break;
            case 'admin':
                $this->render('dashboard_admin');
                break;
            case 'guest':
                $this->render('dashboard_guest');
                break;
            default:
                $this->render('dashboard_user');
        }
    }

    /**
     * WEB: Add New Admin (Super User Only)
     * URL: /users/add_admin
     */
    public function add_admin() {
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied. Super user permission required.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $this->layout = false;

        if ($this->request->is('post')) {
            $data = $this->request->data['User'];

            // Validate required fields
            if (empty($data['full_name']) || empty($data['username']) || 
                empty($data['email']) || empty($data['password']) || empty($data['access_code'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            // Hash access code
            $passwordHasher = new BlowfishPasswordHasher();
            $data['access_code'] = $passwordHasher->hash($data['access_code']);
            $data['status'] = 1;

            $this->User->create();
            if ($this->User->save(array('User' => $data))) {
                $this->Session->setFlash('Admin created successfully!', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'dashboard'));
            } else {
                $errors = $this->User->validationErrors;
                $errorMsg = 'Failed to create admin. ';
                
                if (isset($errors['email'])) {
                    $errorMsg = 'This email address is already registered.';
                } elseif (isset($errors['username'])) {
                    $errorMsg = 'This username is already taken.';
                }

                $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            }
        }

        $this->set('user', $this->Auth->user());
        $this->render('add_admin');
    }

    /**
     * WEB: Edit Admin (Super User Only)
     */
    public function edit_admin($id = null) {
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id) {
            $this->Session->setFlash('Invalid admin ID', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $admin = $this->User->findById($id);
        if (!$admin || !in_array($admin['User']['role'], array('admin', 'super_user'))) {
            $this->Session->setFlash('Admin not found', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $this->layout = false;

        if ($this->request->is(array('post', 'put'))) {
            $this->User->id = $id;
            
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('Admin updated successfully!', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'dashboard'));
            } else {
                $this->Session->setFlash('Failed to update admin', 'default', array('class' => 'error'));
            }
        }

        if (empty($this->request->data)) {
            $this->request->data = $admin;
        }

        $this->set('admin', $admin);
        $this->set('user', $this->Auth->user());
    }

    /**
     * WEB: Delete Admin (Super User Only)
     */
    public function delete_admin($id = null) {
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id || $id == $this->Auth->user('id')) {
            $this->Session->setFlash('Cannot delete your own account', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->User->delete($id)) {
            $this->Session->setFlash('Admin deleted successfully', 'default', array('class' => 'success'));
        } else {
            $this->Session->setFlash('Failed to delete admin', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('action' => 'dashboard'));
    }

    /**
     * WEB: Logout
     */
    public function logout() {
        $this->Auth->logout();
        $this->Session->setFlash('You have been logged out.', 'default', array('class' => 'success'));
        return $this->redirect(array('action' => 'login'));
    }

    /**
     * API: User Registration Endpoint
     */
    public function api_register() {
        $this->autoRender = false;
        
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method is allowed'
            ), 405);
        }

        $data = $this->_getRequestData();

        if (empty($data['full_name']) || empty($data['username']) || 
            empty($data['email']) || empty($data['password'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Required fields: full_name, username, email, password'
            ), 400);
        }

        $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

        if ($userType === 'admin') {
            if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Invalid access code for admin registration'
                ), 403);
            }
            
            $passwordHasher = new BlowfishPasswordHasher();
            $data['access_code'] = $passwordHasher->hash('0000');
        } else {
            $data['access_code'] = null;
        }

        $data['role'] = $userType;
        $data['status'] = 1;

        unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

        $this->User->create();
        if ($this->User->save(array('User' => $data))) {
            return $this->_sendResponse(array(
                'success' => true,
                'message' => 'User registered successfully',
                'data' => array(
                    'id' => $this->User->id,
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $data['role']
                )
            ), 201);
        } else {
            $errors = $this->User->validationErrors;
            $errorMsg = 'Registration failed';
            
            if (isset($errors['email'])) {
                $errorMsg = 'Email already registered';
            } elseif (isset($errors['username'])) {
                $errorMsg = 'Username already taken';
            }

            return $this->_sendResponse(array(
                'success' => false,
                'message' => $errorMsg,
                'errors' => $errors
            ), 422);
        }
    }

    /**
     * API: Authentication Token Generation
     */
    public function token() {
        $this->autoRender = false;
        
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method is allowed'
            ), 405);
        }

        $data = $this->_getRequestData();

        $email = isset($data['email']) ? $data['email'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $role = isset($data['role']) ? $data['role'] : 'user';
        $accessCode = isset($data['access_code']) ? $data['access_code'] : null;

        if (empty($email) || empty($password)) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Email and password required'
            ), 400);
        }

        $user = $this->User->find('first', array(
            'conditions' => array('User.email' => $email)
        ));

        if (!$user) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid credentials'
            ), 401);
        }

        if ($user['User']['status'] == 0) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Account deactivated'
            ), 403);
        }

        $passwordHasher = new BlowfishPasswordHasher();
        if (!$passwordHasher->check($password, $user['User']['password'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid credentials'
            ), 401);
        }

        if ($user['User']['role'] !== $role) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid role'
            ), 403);
        }

        if (in_array($role, array('admin', 'super_user'))) {
            if (empty($accessCode)) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Access code required'
                ), 400);
            }

            if (!$passwordHasher->check($accessCode, $user['User']['access_code'])) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Invalid access code'
                ), 401);
            }
        }

        $token = Security::hash(uniqid($user['User']['id'], true) . time());
        
        $this->User->id = $user['User']['id'];
        $this->User->saveField('remember_token', $token);

        return $this->_sendResponse(array(
            'success' => true,
            'message' => 'Authentication successful',
            'data' => array(
                'token' => $token,
                'user' => array(
                    'id' => $user['User']['id'],
                    'username' => $user['User']['username'],
                    'email' => $user['User']['email'],
                    'full_name' => $user['User']['full_name'],
                    'role' => $user['User']['role'],
                    'image' => $user['User']['image']
                )
            )
        ), 200);
    }
}
