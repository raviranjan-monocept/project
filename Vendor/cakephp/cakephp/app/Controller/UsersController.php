<?php
/**
 * Users Controller - Web + REST API Implementation
 */
App::uses('AppController', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class UsersController extends AppController {

    public $components = array('RequestHandler');

    public function beforeFilter() {
        parent::beforeFilter();
        
        // Allow public access to these actions
        $this->Auth->allow('register', 'api_register', 'token', 'login');
        
        // Disable CSRF for API actions only
        if (in_array($this->request->params['action'], array('api_register', 'token'))) {
            if (isset($this->Security)) {
                $this->Security->csrfCheck = false;
                $this->Security->validatePost = false;
            }
        }
    }

    /**
     * Private helper to check if current user is super admin
     */
    private function _isSuperAdmin() {
        return ($this->Auth->user('role') === 'super_user');
    }

    /**
     * Private helper to check if current user is admin or above
     */
    private function _isAdminOrAbove() {
        $role = $this->Auth->user('role');
        return in_array($role, array('admin', 'super_user'));
    }

    /**
     * Send JSON response helper
     */
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false;
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }

    /**
     * Get input data (JSON or form)
     */
    private function _getRequestData() {
        $data = $this->request->input('json_decode', true);
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }

    /**
     * WEB: Login Page
     * URL: /users/login
     */
    public function login() {
        // If already logged in, redirect to dashboard
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

            // Find user
            $user = $this->User->find('first', array(
                'conditions' => array('User.email' => $email),
                'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username', 'status', 'image')
            ));

            if (!$user) {
                $this->Session->setFlash('Invalid email or password', 'default', array('class' => 'error'));
                return;
            }

            // Check if account is active
            if ($user['User']['status'] == 0) {
                $this->Session->setFlash('Your account has been deactivated', 'default', array('class' => 'error'));
                return;
            }

            // Verify password
            $passwordHasher = new BlowfishPasswordHasher();
            if (!$passwordHasher->check($password, $user['User']['password'])) {
                $this->Session->setFlash('Invalid email or password', 'default', array('class' => 'error'));
                return;
            }

            // Verify role
            if ($user['User']['role'] !== $role) {
                $this->Session->setFlash('Invalid role selected', 'default', array('class' => 'error'));
                return;
            }

            // Check access code for admin/super_user
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

            // Login successful
            $this->Auth->login($user['User']);
            $this->Session->setFlash('Login successful!', 'default', array('class' => 'success'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        
        // Renders: app/View/Users/login.ctp
    }

    /**
     * WEB: Registration Page
     * URL: /users/register
     */
    public function register() {
        // If already logged in, redirect to dashboard
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is('post')) {
            $data = isset($this->request->data['User']) ? $this->request->data['User'] : array();

            // Validation
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
        
        // Renders: app/View/Users/register.ctp
    }

/**
 * User Profile Page
 * URL: /users/profile
 */
public function profile() {
    // Must be logged in
    if (!$this->Auth->user()) {
        $this->Session->setFlash('Please login to access your profile', 'default', array('class' => 'error'));
        return $this->redirect(array('action' => 'login'));
    }

    $userId = $this->Auth->user('id');
    
    // Get full user data from database
    $user = $this->User->findById($userId);
    
    if (!$user) {
        $this->Session->setFlash('User not found', 'default', array('class' => 'error'));
        return $this->redirect(array('action' => 'dashboard'));
    }
    
    // Handle profile update (POST request)
    if ($this->request->is(array('post', 'put'))) {
        
        // Handle file upload for profile image
        if (!empty($this->request->data['User']['image']['name'])) {
            $file = $this->request->data['User']['image'];
            
            // Validate file type and size
            $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            $maxSize = 2097152; // 2MB
            
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize && $file['error'] == 0) {
                
                // Create upload directory if it doesn't exist
                $uploadDir = WWW_ROOT . 'img' . DS . 'users' . DS;
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Get file extension
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Generate unique filename
                $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $uploadPath = $uploadDir . $newFilename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    
                    // Delete old image if exists (except default)
                    if (!empty($user['User']['image']) && 
                        $user['User']['image'] !== 'default-user.jpg' && 
                        $user['User']['image'] !== 'user2-160x160.jpg') {
                        
                        $oldImagePath = WWW_ROOT . 'img' . DS . $user['User']['image'];
                        if (file_exists($oldImagePath)) {
                            @unlink($oldImagePath);
                        }
                    }
                    
                    // Save new image path (relative to webroot/img/)
                    $this->request->data['User']['image'] = 'users/' . $newFilename;
                    
                } else {
                    $this->Session->setFlash('Failed to upload image. Please try again.', 'default', array('class' => 'error'));
                    $this->request->data['User']['image'] = $user['User']['image']; // Keep old image
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
                $this->request->data['User']['image'] = $user['User']['image']; // Keep old image
            }
        } else {
            // No file uploaded, keep existing image
            $this->request->data['User']['image'] = $user['User']['image'];
        }
        
        // Set the user ID for update
        $this->User->id = $userId;
        
        // Disable validation temporarily for easier update
        $this->User->validate = array();
        
        // Attempt to save
        if ($this->User->save($this->request->data, array('validate' => false))) {
            
            // Update successful
            $this->Session->setFlash('Profile updated successfully!', 'default', array('class' => 'success'));
            
            // Refresh user data in Auth session
            $updatedUser = $this->User->findById($userId);
            $this->Session->write('Auth.User', $updatedUser['User']);
            
            // Redirect back to profile to show updated data
            return $this->redirect(array('action' => 'profile'));
            
        } else {
            // Save failed
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
    
    // If no POST data, populate form with existing user data
    if (empty($this->request->data)) {
        $this->request->data = $user;
    }
    
    // Pass user data to view
    $this->set('user', $user);
    $this->set('title_for_layout', 'Edit Profile');
}
   /**
 * WEB: Dashboard
 * URL: /users/dashboard
 */
public function dashboard() {
    // Must be logged in
    if (!$this->Auth->user()) {
        return $this->redirect(array('action' => 'login'));
    }

    $role = $this->Auth->user('role');
    $this->set('role', $role);
    $this->set('user', $this->Auth->user());

    // Add policy stats for admin and super_user
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

    // Super admin specific data
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

    // Render role-specific dashboard views
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
     * WEB: Logout
     * URL: /users/logout
     */
    public function logout() {
        $this->Auth->logout();
        $this->Session->setFlash('You have been logged out.', 'default', array('class' => 'success'));
        return $this->redirect(array('action' => 'login'));
    }

    /**
     * API: Registration (JSON response)
     * POST /api/users/register
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
     * API: Get Authentication Token
     * POST /api/users/token
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
