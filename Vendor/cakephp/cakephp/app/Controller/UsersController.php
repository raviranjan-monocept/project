<?php
/**
 * Users Controller - Comprehensive Web & REST API Implementation
 * 
 * This controller handles all user-related operations including:
 * - Web-based authentication (login/logout)
 * - User registration (both web and API)
 * - User dashboard with role-based views
 * - User profile management with image upload
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
        // 'register' and 'login' are for web forms
        // 'api_register' and 'token' are for REST API endpoints
        $this->Auth->allow('register', 'api_register', 'token', 'login');
        
        // Disable CSRF protection for API endpoints only
        // Web forms still have CSRF protection enabled
        if (in_array($this->request->params['action'], array('api_register', 'token'))) {
            if (isset($this->Security)) {
                $this->Security->csrfCheck = false;
                $this->Security->validatePost = false;
            }
        }
    }

    /**
     * Check if logged-in user has super_user role
     * 
     * @return boolean True if user is super admin, false otherwise
     */
    private function _isSuperAdmin() {
        return ($this->Auth->user('role') === 'super_user');
    }

    /**
     * Check if logged-in user is admin or super_user
     * 
     * @return boolean True if user is admin or above, false otherwise
     */
    private function _isAdminOrAbove() {
        $role = $this->Auth->user('role');
        return in_array($role, array('admin', 'super_user'));
    }

    /**
     * Helper function to send JSON responses for API endpoints
     * Sets proper HTTP status code and content type
     * 
     * @param array $data Response data to send as JSON
     * @param int $statusCode HTTP status code (default: 200)
     * @return CakeResponse Response object
     */
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false; // Don't render a view template
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }

    /**
     * Get request data from either JSON body or form POST
     * Tries JSON first, falls back to regular form data
     * 
     * @return array Request data
     */
    private function _getRequestData() {
        // Try to decode JSON from request body
        $data = $this->request->input('json_decode', true);
        // If no JSON data, use regular form data
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }

    /**
     * WEB: Login Page and Authentication Handler
     * URL: /users/login
     * 
     * Handles both GET (show form) and POST (process login) requests
     * Supports role-based login with access codes for admin/super_user
     */
    public function login() {
        // If user is already logged in, redirect to dashboard
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Handle login form submission
        if ($this->request->is('post')) {
            // Extract login credentials from form
            $email = !empty($this->request->data['User']['email']) ? $this->request->data['User']['email'] : '';
            $password = !empty($this->request->data['User']['password']) ? $this->request->data['User']['password'] : '';
            $role = !empty($this->request->data['User']['role']) ? $this->request->data['User']['role'] : 'user';
            $accessCode = !empty($this->request->data['User']['access_code']) ? $this->request->data['User']['access_code'] : null;

            // Validate that email and password are provided
            if (empty($email) || empty($password)) {
                $this->Session->setFlash('Please enter email and password', 'default', array('class' => 'error'));
                return;
            }

            // Find user by email in database
            $user = $this->User->find('first', array(
                'conditions' => array('User.email' => $email),
                'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username', 'status', 'image')
            ));

            // Check if user exists
            if (!$user) {
                $this->Session->setFlash('Invalid email or password', 'default', array('class' => 'error'));
                return;
            }

            // Check if user account is active (status = 1)
            if ($user['User']['status'] == 0) {
                $this->Session->setFlash('Your account has been deactivated', 'default', array('class' => 'error'));
                return;
            }

            // Verify password using Blowfish hashing algorithm
            $passwordHasher = new BlowfishPasswordHasher();
            if (!$passwordHasher->check($password, $user['User']['password'])) {
                $this->Session->setFlash('Invalid email or password', 'default', array('class' => 'error'));
                return;
            }

            // Verify that selected role matches user's actual role
            if ($user['User']['role'] !== $role) {
                $this->Session->setFlash('Invalid role selected', 'default', array('class' => 'error'));
                return;
            }

            // For admin and super_user, verify access code
            if (in_array($role, array('admin', 'super_user'))) {
                // Access code is required for elevated privileges
                if (empty($accessCode)) {
                    $this->Session->setFlash('Access code is required for ' . $role, 'default', array('class' => 'error'));
                    return;
                }

                // Verify access code matches stored hashed code
                if (!$passwordHasher->check($accessCode, $user['User']['access_code'])) {
                    $this->Session->setFlash('Invalid access code', 'default', array('class' => 'error'));
                    return;
                }
            }

            // All validations passed - log the user in
            $this->Auth->login($user['User']);
            $this->Session->setFlash('Login successful!', 'default', array('class' => 'success'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        
        // GET request - renders login form view
        // Renders: app/View/Users/login.ctp
    }

    /**
     * WEB: Registration Page and Handler
     * URL: /users/register
     * 
     * Allows new users to create accounts
     * Supports both regular users and admin registration with access code
     */
    public function register() {
        // If already logged in, no need to register - redirect to dashboard
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Handle registration form submission
        if ($this->request->is('post')) {
            // Extract user data from form submission
            $data = isset($this->request->data['User']) ? $this->request->data['User'] : array();

            // Validate required fields
            if (empty($data['full_name']) || empty($data['username']) || 
                empty($data['email']) || empty($data['password'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            // Verify password confirmation matches
            if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
                $this->Session->setFlash('Passwords do not match.', 'default', array('class' => 'error'));
                return;
            }

            // Verify email confirmation matches
            if (isset($data['confirm_email']) && $data['email'] !== $data['confirm_email']) {
                $this->Session->setFlash('Email addresses do not match.', 'default', array('class' => 'error'));
                return;
            }

            // Determine user type (regular user or admin)
            $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

            // Handle admin registration with access code
            if ($userType === 'admin') {
                // Verify admin access code (hardcoded as '0000' for demo)
                if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                    $this->Session->setFlash('Invalid access code.', 'default', array('class' => 'error'));
                    return;
                }
                
                // Hash the access code before storing
                $passwordHasher = new BlowfishPasswordHasher();
                $data['access_code'] = $passwordHasher->hash('0000');
            } else {
                // Regular users don't have access codes
                $data['access_code'] = null;
            }

            // Set role and activate account
            $data['role'] = $userType;
            $data['status'] = 1; // Active by default

            // Remove confirmation fields before saving
            unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

            // Attempt to save new user to database
            $this->User->create();
            if ($this->User->save(array('User' => $data))) {
                // Registration successful
                $this->Session->setFlash('Account created successfully! Please login.', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'login'));
            } else {
                // Registration failed - handle validation errors
                $errors = $this->User->validationErrors;
                $errorMsg = 'Registration failed. Please check the form.';
                
                // Provide specific error messages
                if (isset($errors['email'])) {
                    $errorMsg = 'This email address is already registered.';
                } elseif (isset($errors['username'])) {
                    $errorMsg = 'This username is already taken.';
                }

                $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            }
        }
        
        // GET request - renders registration form view
        // Renders: app/View/Users/register.ctp
    }

    /**
     * WEB: User Profile Page with Edit Functionality
     * URL: /users/profile
     * 
     * Displays and allows editing of logged-in user's profile
     * Supports profile image upload with validation
     */
    public function profile() {
        // Ensure user is logged in
        if (!$this->Auth->user()) {
            $this->Session->setFlash('Please login to access your profile', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'login'));
        }

        // Get current user's ID from session
        $userId = $this->Auth->user('id');
        
        // Fetch complete user data from database
        $user = $this->User->findById($userId);
        
        // Handle edge case where user is deleted while logged in
        if (!$user) {
            $this->Session->setFlash('User not found', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        
        // Handle profile update submission
        if ($this->request->is(array('post', 'put'))) {
            
            // Process profile image upload if provided
            if (!empty($this->request->data['User']['image']['name'])) {
                $file = $this->request->data['User']['image'];
                
                // Define allowed image types and maximum size
                $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
                $maxSize = 2097152; // 2MB in bytes
                
                // Validate file type, size, and upload success
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize && $file['error'] == 0) {
                    
                    // Create upload directory if it doesn't exist
                    $uploadDir = WWW_ROOT . 'img' . DS . 'users' . DS;
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true); // Create with full permissions
                    }
                    
                    // Get file extension
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    // Generate unique filename to prevent conflicts
                    // Format: user_[id]_[timestamp].[extension]
                    $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $newFilename;
                    
                    // Move uploaded file from temp location to permanent location
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        
                        // Delete old profile image if it exists
                        // Don't delete default images
                        if (!empty($user['User']['image']) && 
                            $user['User']['image'] !== 'default-user.jpg' && 
                            $user['User']['image'] !== 'user2-160x160.jpg') {
                            
                            $oldImagePath = WWW_ROOT . 'img' . DS . $user['User']['image'];
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath); // @ suppresses errors if deletion fails
                            }
                        }
                        
                        // Save new image path (relative to webroot/img/)
                        $this->request->data['User']['image'] = 'users/' . $newFilename;
                        
                    } else {
                        // File upload failed
                        $this->Session->setFlash('Failed to upload image. Please try again.', 'default', array('class' => 'error'));
                        $this->request->data['User']['image'] = $user['User']['image']; // Keep old image
                    }
                    
                } else {
                    // File validation failed - build error message
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
                // No new image uploaded, keep existing image
                $this->request->data['User']['image'] = $user['User']['image'];
            }
            
            // Set the user ID for update operation
            $this->User->id = $userId;
            
            // Disable validation for easier update (optional - can be improved)
            $this->User->validate = array();
            
            // Attempt to save updated profile data
            if ($this->User->save($this->request->data, array('validate' => false))) {
                
                // Update successful
                $this->Session->setFlash('Profile updated successfully!', 'default', array('class' => 'success'));
                
                // Refresh user data in Auth session so changes reflect immediately
                $updatedUser = $this->User->findById($userId);
                $this->Session->write('Auth.User', $updatedUser['User']);
                
                // Redirect to profile page to show updated data
                return $this->redirect(array('action' => 'profile'));
                
            } else {
                // Save failed - handle validation errors
                $errors = $this->User->validationErrors;
                $errorMsg = 'Failed to update profile. ';
                
                // Provide specific error messages
                if (isset($errors['email'])) {
                    $errorMsg .= 'Email already exists. ';
                }
                if (isset($errors['username'])) {
                    $errorMsg .= 'Username already taken. ';
                }
                
                $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            }
        }
        
        // On GET request or after failed save, populate form with existing data
        if (empty($this->request->data)) {
            $this->request->data = $user;
        }
        
        // Pass user data to view template
        $this->set('user', $user);
        $this->set('title_for_layout', 'Edit Profile');
    }

    /**
     * WEB: Role-Based Dashboard
     * URL: /users/dashboard
     * 
     * Displays different dashboard views based on user role
     * - super_user: Full admin panel with user management
     * - admin: Policy management dashboard
     * - user/guest: Basic user dashboard
     */
    public function dashboard() {
        // Ensure user is logged in
        if (!$this->Auth->user()) {
            return $this->redirect(array('action' => 'login'));
        }

        // Get current user's role and data
        $role = $this->Auth->user('role');
        $this->set('role', $role);
        $this->set('user', $this->Auth->user());

        // Load policy statistics for admin and super_user roles
        if (in_array($role, array('admin', 'super_user'))) {
            $this->loadModel('Policy');

            // Calculate policy statistics
            $stats = array(
                'total' => $this->Policy->find('count'), // Total policies
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

            // Fetch 5 most recent policies for quick view
            $policies = $this->Policy->find('all', array(
                'order' => array('Policy.created' => 'DESC'),
                'limit' => 5
            ));
            $this->set(compact('policies'));
        }

        // Load super admin specific data
        if ($role === 'super_user') {
            // Count total admins (including super admins)
            $totalAdmins = $this->User->find('count', array(
                'conditions' => array('User.role' => array('admin', 'super_user'))
            ));

            // Count active admins only
            $activeAdmins = $this->User->find('count', array(
                'conditions' => array(
                    'User.role' => array('admin', 'super_user'),
                    'User.status' => 1
                )
            ));

            // Calculate inactive admins
            $inactiveAdmins = $totalAdmins - $activeAdmins;

            // Count super admins
            $superAdmins = $this->User->find('count', array(
                'conditions' => array('User.role' => 'super_user')
            ));

            // Count regular users
            $totalUsers = $this->User->find('count', array(
                'conditions' => array('User.role' => array('user', 'guest'))
            ));

            // Fetch complete list of all admins for management
            $admins = $this->User->find('all', array(
                'conditions' => array('User.role' => array('admin', 'super_user')),
                'fields' => array(
                    'User.id', 'User.full_name', 'User.username', 'User.email',
                    'User.role', 'User.status', 'User.modified'
                ),
                'order' => array('User.id' => 'ASC')
            ));

            // Pass all super admin data to view
            $this->set(compact(
                'totalAdmins', 'activeAdmins', 'inactiveAdmins',
                'superAdmins', 'totalUsers', 'admins'
            ));
        }

        // Render appropriate dashboard view based on user role
        switch ($role) {
            case 'super_user':
                // Full admin panel with user management
                $this->render('dashboard_super_user');
                break;
            case 'admin':
                // Policy management dashboard
                $this->render('dashboard_admin');
                break;
            case 'guest':
                // Limited guest dashboard
                $this->render('dashboard_guest');
                break;
            default:
                // Standard user dashboard
                $this->render('dashboard_user');
        }
    }

    /**
     * WEB: Logout
     * URL: /users/logout
     * 
     * Logs out current user and clears session
     */
    public function logout() {
        // Clear Auth session and logout user
        $this->Auth->logout();
        $this->Session->setFlash('You have been logged out.', 'default', array('class' => 'success'));
        return $this->redirect(array('action' => 'login'));
    }

    /**
     * API: User Registration Endpoint
     * POST /api/users/register
     * 
     * Allows registration via REST API with JSON request/response
     * Supports both regular user and admin registration
     */
    public function api_register() {
        $this->autoRender = false; // Don't render view - return JSON only
        
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method is allowed'
            ), 405); // Method Not Allowed
        }

        // Get request data (JSON or form-encoded)
        $data = $this->_getRequestData();

        // Validate required fields
        if (empty($data['full_name']) || empty($data['username']) || 
            empty($data['email']) || empty($data['password'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Required fields: full_name, username, email, password'
            ), 400); // Bad Request
        }

        // Determine user type (default to regular user)
        $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

        // Handle admin registration with access code verification
        if ($userType === 'admin') {
            // Verify admin access code
            if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Invalid access code for admin registration'
                ), 403); // Forbidden
            }
            
            // Hash access code before storing
            $passwordHasher = new BlowfishPasswordHasher();
            $data['access_code'] = $passwordHasher->hash('0000');
        } else {
            // Regular users don't need access code
            $data['access_code'] = null;
        }

        // Set role and activate account
        $data['role'] = $userType;
        $data['status'] = 1; // Active by default

        // Clean up confirmation fields
        unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

        // Attempt to save new user
        $this->User->create();
        if ($this->User->save(array('User' => $data))) {
            // Registration successful - return created user data
            return $this->_sendResponse(array(
                'success' => true,
                'message' => 'User registered successfully',
                'data' => array(
                    'id' => $this->User->id,
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => $data['role']
                )
            ), 201); // Created
        } else {
            // Registration failed - return validation errors
            $errors = $this->User->validationErrors;
            $errorMsg = 'Registration failed';
            
            // Provide specific error message
            if (isset($errors['email'])) {
                $errorMsg = 'Email already registered';
            } elseif (isset($errors['username'])) {
                $errorMsg = 'Username already taken';
            }

            return $this->_sendResponse(array(
                'success' => false,
                'message' => $errorMsg,
                'errors' => $errors
            ), 422); // Unprocessable Entity
        }
    }

    /**
     * API: Authentication Token Generation
     * POST /api/users/token
     * 
     * Authenticates user and returns bearer token for API access
     * Token is stored in database for validation in subsequent requests
     */
    public function token() {
        $this->autoRender = false; // Return JSON only
        
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method is allowed'
            ), 405); // Method Not Allowed
        }

        // Get authentication credentials from request
        $data = $this->_getRequestData();

        $email = isset($data['email']) ? $data['email'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $role = isset($data['role']) ? $data['role'] : 'user';
        $accessCode = isset($data['access_code']) ? $data['access_code'] : null;

        // Validate required credentials
        if (empty($email) || empty($password)) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Email and password required'
            ), 400); // Bad Request
        }

        // Find user by email
        $user = $this->User->find('first', array(
            'conditions' => array('User.email' => $email)
        ));

        // Check if user exists
        if (!$user) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid credentials'
            ), 401); // Unauthorized
        }

        // Check if account is active
        if ($user['User']['status'] == 0) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Account deactivated'
            ), 403); // Forbidden
        }

        // Verify password
        $passwordHasher = new BlowfishPasswordHasher();
        if (!$passwordHasher->check($password, $user['User']['password'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid credentials'
            ), 401); // Unauthorized
        }

        // Verify role matches
        if ($user['User']['role'] !== $role) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid role'
            ), 403); // Forbidden
        }

        // Verify access code for elevated roles
        if (in_array($role, array('admin', 'super_user'))) {
            // Access code is required
            if (empty($accessCode)) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Access code required'
                ), 400); // Bad Request
            }

            // Verify access code is correct
            if (!$passwordHasher->check($accessCode, $user['User']['access_code'])) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Invalid access code'
                ), 401); // Unauthorized
            }
        }

        // Generate unique authentication token
        // Token format: hash(user_id + unique_string + timestamp)
        $token = Security::hash(uniqid($user['User']['id'], true) . time());
        
        // Save token to database for future validation
        $this->User->id = $user['User']['id'];
        $this->User->saveField('remember_token', $token);

        // Return token and user data
        return $this->_sendResponse(array(
            'success' => true,
            'message' => 'Authentication successful',
            'data' => array(
                'token' => $token, // Use this token in Authorization header
                'user' => array(
                    'id' => $user['User']['id'],
                    'username' => $user['User']['username'],
                    'email' => $user['User']['email'],
                    'full_name' => $user['User']['full_name'],
                    'role' => $user['User']['role'],
                    'image' => $user['User']['image']
                )
            )
        ), 200); // OK
    }
}
