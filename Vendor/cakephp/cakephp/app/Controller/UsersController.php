<?php
/**
 * Users Controller - Comprehensive Web & REST API Implementation
 * 
 * WORKFLOW OVERVIEW:
 * ==================
 * 1. Public Access: login, register, api_register, token
 * 2. Authenticated Users: dashboard, profile, logout
 * 3. Super User Only: add_admin, edit_admin, delete_admin, view, toggle_status
 * 
 * ROLE HIERARCHY:
 * ===============
 * - guest: Limited access (view only)
 * - user: Standard user privileges
 * - admin: Administrative access with access code
 * - super_user: Full system access, can manage other admins
 * 
 * SECURITY FEATURES:
 * ==================
 * - Password hashing using BlowfishPasswordHasher
 * - Access code protection for admin/super_user roles
 * - CSRF protection (disabled only for API endpoints)
 * - Session-based authentication for web
 * - Token-based authentication for API
 * - Role-based access control (RBAC)
 * 
 * EMAIL FUNCTIONALITY:
 * ====================
 * - Sends access code to newly created admins via Gmail SMTP
 * - Requires app/Config/email.php configuration
 * - Uses inline HTML for professional email formatting
 */
App::uses('AppController', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class UsersController extends AppController {

    // Enable RequestHandler for JSON/XML API responses
    public $components = array('RequestHandler');

    /**
     * BEFORE FILTER
     * =============
     * Runs before every action in this controller
     * 
     * WORKFLOW:
     * 1. Call parent beforeFilter()
     * 2. Allow public access to specified actions
     * 3. Disable CSRF for API endpoints only
     */
    public function beforeFilter() {
        parent::beforeFilter();
        
        // Allow unauthenticated access to these actions
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
     * HELPER: Check if user is Super User
     * 
     * @return bool True if current user has super_user role
     */
    private function _isSuperAdmin() {
        return ($this->Auth->user('role') === 'super_user');
    }

    /**
     * HELPER: Check if user is Admin or above
     * 
     * @return bool True if current user is admin or super_user
     */
    private function _isAdminOrAbove() {
        $role = $this->Auth->user('role');
        return in_array($role, array('admin', 'super_user'));
    }

    /**
     * HELPER: Send JSON Response for API
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return CakeResponse
     */
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false;
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }

    /**
     * HELPER: Get request data from JSON or POST
     * 
     * @return array Request data
     */
    private function _getRequestData() {
        // Try to decode JSON body first
        $data = $this->request->input('json_decode', true);
        // Fall back to POST data if JSON is empty
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }

    /**
     * WEB: LOGIN PAGE AND AUTHENTICATION
     * ===================================
     * URL: /users/login
     * Method: GET (display form), POST (process login)
     * Access: Public
     * 
     * WORKFLOW:
     * 1. Check if user is already logged in → redirect to dashboard
     * 2. If POST request:
     *    a. Validate email and password are provided
     *    b. Find user by email
     *    c. Check if user exists
     *    d. Check if account is active (status = 1)
     *    e. Verify password using BlowfishPasswordHasher
     *    f. Verify role matches selected role
     *    g. For admin/super_user: verify access code
     *    h. Log user in and redirect to dashboard
     * 3. If GET request: display login form
     */
    public function login() {
        // Redirect if already logged in
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is('post')) {
            // Extract login credentials
            $email = !empty($this->request->data['User']['email']) ? $this->request->data['User']['email'] : '';
            $password = !empty($this->request->data['User']['password']) ? $this->request->data['User']['password'] : '';
            $role = !empty($this->request->data['User']['role']) ? $this->request->data['User']['role'] : 'user';
            $accessCode = !empty($this->request->data['User']['access_code']) ? $this->request->data['User']['access_code'] : null;

            // Validate required fields
            if (empty($email) || empty($password)) {
                $this->Session->setFlash('Please enter email and password', 'default', array('class' => 'error'));
                return;
            }

            // Find user by email
            $user = $this->User->find('first', array(
                'conditions' => array('User.email' => $email),
                'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username', 'status', 'image')
            ));

            // Check if user exists
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

            // Verify role matches
            if ($user['User']['role'] !== $role) {
                $this->Session->setFlash('Invalid role selected', 'default', array('class' => 'error'));
                return;
            }

            // For admin/super_user: verify access code
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

            // Login successful - create session and redirect
            $this->Auth->login($user['User']);
            $this->Session->setFlash('Login successful!', 'default', array('class' => 'success'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        // GET request - display login form (view: login.ctp)
    }

    /**
     * WEB: USER REGISTRATION
     * ======================
     * URL: /users/register
     * Method: GET (display form), POST (process registration)
     * Access: Public
     * 
     * WORKFLOW:
     * 1. Check if user is already logged in → redirect to dashboard
     * 2. If POST request:
     *    a. Validate all required fields are filled
     *    b. Verify password confirmation matches
     *    c. Verify email confirmation matches
     *    d. Check user type (user, guest, admin)
     *    e. For admin: verify access code is '0000'
     *    f. Hash password and access code
     *    g. Save user to database
     *    h. Redirect to login page
     * 3. If GET request: display registration form
     */
    public function register() {
        // Redirect if already logged in
        if ($this->Auth->user()) {
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is('post')) {
            $data = isset($this->request->data['User']) ? $this->request->data['User'] : array();

            // Validate required fields
            if (empty($data['full_name']) || empty($data['username']) || 
                empty($data['email']) || empty($data['password'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            // Verify password confirmation
            if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
                $this->Session->setFlash('Passwords do not match.', 'default', array('class' => 'error'));
                return;
            }

            // Verify email confirmation
            if (isset($data['confirm_email']) && $data['email'] !== $data['confirm_email']) {
                $this->Session->setFlash('Email addresses do not match.', 'default', array('class' => 'error'));
                return;
            }

            // Determine user type
            $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

            // For admin registration: verify access code
            if ($userType === 'admin') {
                if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                    $this->Session->setFlash('Invalid access code.', 'default', array('class' => 'error'));
                    return;
                }
                
                // Hash the access code
                $passwordHasher = new BlowfishPasswordHasher();
                $data['access_code'] = $passwordHasher->hash('0000');
            } else {
                $data['access_code'] = null;
            }

            // Set role and status
            $data['role'] = $userType;
            $data['status'] = 1; // Active by default

            // Remove confirmation fields
            unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

            // Save user to database
            $this->User->create();
            if ($this->User->save(array('User' => $data))) {
                $this->Session->setFlash('Account created successfully! Please login.', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'login'));
            } else {
                // Handle validation errors
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
        // GET request - display registration form (view: register.ctp)
    }

    /**
     * WEB: USER PROFILE (VIEW & EDIT)
     * ================================
     * URL: /users/profile
     * Method: GET (display profile), POST/PUT (update profile)
     * Access: Authenticated users only
     * Layout: profile
     * 
     * WORKFLOW:
     * 1. Check if user is logged in
     * 2. Load current user data
     * 3. If POST/PUT request:
     *    a. Handle profile image upload
     *    b. Validate image type and size
     *    c. Save image to /webroot/img/users/
     *    d. Delete old image if exists
     *    e. Update user data in database
     *    f. Update session with new user data
     *    g. Redirect to profile page
     * 4. If GET request: display profile form with current data
     */
    public function profile() {
        // Check authentication
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
        
        // Handle profile update
        if ($this->request->is(array('post', 'put'))) {
            
            // Handle image upload
            if (!empty($this->request->data['User']['image']['name'])) {
                $file = $this->request->data['User']['image'];
                $allowedTypes = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
                $maxSize = 2097152; // 2MB
                
                // Validate file type and size
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize && $file['error'] == 0) {
                    $uploadDir = WWW_ROOT . 'img' . DS . 'users' . DS;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Generate unique filename
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $newFilename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // Delete old image if exists and not default
                        if (!empty($user['User']['image']) && 
                            $user['User']['image'] !== 'default-user.jpg' && 
                            $user['User']['image'] !== 'user2-160x160.jpg') {
                            $oldImagePath = WWW_ROOT . 'img' . DS . $user['User']['image'];
                            if (file_exists($oldImagePath)) {
                                @unlink($oldImagePath);
                            }
                        }
                        
                        // Set new image path
                        $this->request->data['User']['image'] = 'users/' . $newFilename;
                    } else {
                        $this->Session->setFlash('Failed to upload image. Please try again.', 'default', array('class' => 'error'));
                        $this->request->data['User']['image'] = $user['User']['image'];
                    }
                } else {
                    // Image validation failed
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
                // No new image uploaded, keep existing
                $this->request->data['User']['image'] = $user['User']['image'];
            }
            
            // Save updated user data
            $this->User->id = $userId;
            $this->User->validate = array(); // Skip validation
            
            if ($this->User->save($this->request->data, array('validate' => false))) {
                $this->Session->setFlash('Profile updated successfully!', 'default', array('class' => 'success'));
                
                // Update session with new user data
                $updatedUser = $this->User->findById($userId);
                $this->Session->write('Auth.User', $updatedUser['User']);
                
                return $this->redirect(array('action' => 'profile'));
            } else {
                // Handle save errors
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
        
        // Populate form with current user data
        if (empty($this->request->data)) {
            $this->request->data = $user;
        }
        
        $this->set('user', $user);
        $this->set('title_for_layout', 'Edit Profile');
    }

    /**
     * WEB: ROLE-BASED DASHBOARD
     * =========================
     * URL: /users/dashboard
     * Method: GET
     * Access: Authenticated users only
     * Layout: false (each dashboard view has its own layout)
     * 
     * WORKFLOW:
     * 1. Check if user is logged in
     * 2. Get user's role
     * 3. Load role-specific data:
     *    - admin/super_user: Load policy statistics
     *    - super_user: Load admin management data
     * 4. Render appropriate dashboard view based on role:
     *    - super_user → dashboard_super_user.ctp
     *    - admin → dashboard_admin.ctp
     *    - guest → dashboard_guest.ctp
     *    - user → dashboard_user.ctp
     */
    public function dashboard() {
        // Check authentication
        if (!$this->Auth->user()) {
            return $this->redirect(array('action' => 'login'));
        }

        // Don't use default layout
        $this->layout = false;

        $role = $this->Auth->user('role');
        $this->set('role', $role);
        $this->set('user', $this->Auth->user());

        // Load policy statistics for admin and super_user
        if (in_array($role, array('admin', 'super_user'))) {
            $this->loadModel('Policy');

            // Count policies by status
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

            // Get recent policies
            $policies = $this->Policy->find('all', array(
                'order' => array('Policy.created' => 'DESC'),
                'limit' => 5
            ));
            $this->set(compact('policies'));
        }

        // Load admin management data for super_user
        if ($role === 'super_user') {
            // Count total admins (admin + super_user)
            $totalAdmins = $this->User->find('count', array(
                'conditions' => array('User.role' => array('admin', 'super_user'))
            ));

            // Count active admins
            $activeAdmins = $this->User->find('count', array(
                'conditions' => array(
                    'User.role' => array('admin', 'super_user'),
                    'User.status' => 1
                )
            ));

            // Calculate inactive admins
            $inactiveAdmins = $totalAdmins - $activeAdmins;

            // Count super users
            $superAdmins = $this->User->find('count', array(
                'conditions' => array('User.role' => 'super_user')
            ));

            // Count regular users
            $totalUsers = $this->User->find('count', array(
                'conditions' => array('User.role' => array('user', 'guest'))
            ));

            // Get all admins for admin management table
            $admins = $this->User->find('all', array(
                'conditions' => array('User.role' => array('admin', 'super_user')),
                'fields' => array(
                    'User.id', 'User.full_name', 'User.username', 'User.email',
                    'User.role', 'User.status', 'User.modified'
                ),
                'order' => array('User.id' => 'ASC')
            ));

            // Pass data to view
            $this->set(compact(
                'totalAdmins', 'activeAdmins', 'inactiveAdmins',
                'superAdmins', 'totalUsers', 'admins'
            ));
        }

        // Render role-specific dashboard view
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
     * WEB: ADD NEW ADMIN (SUPER USER ONLY)
     * ====================================
     * URL: /users/add_admin
     * Method: GET (display form), POST (create admin)
     * Access: Super User only
     * Layout: false
     * 
     * WORKFLOW:
     * 1. Check if user is super_user
     * 2. If POST request:
     *    a. Validate all required fields
     *    b. Store plain text access code (for email)
     *    c. Hash password and access code
     *    d. Save admin to database
     *    e. Send email with access code to new admin
     *    f. Redirect to dashboard with success/error message
     * 3. If GET request: display add admin form
     * 
     * EMAIL WORKFLOW:
     * 1. Load CakeEmail class
     * 2. Configure SMTP settings from app/Config/email.php
     * 3. Send HTML email with:
     *    - Admin credentials (username, email, role)
     *    - Access code (plain text)
     *    - Welcome message
     * 4. Handle email errors gracefully (admin still created)
     */
    public function add_admin() {
        // Check super user permission
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied. Super user permission required.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Load email library
        App::uses('CakeEmail', 'Network/Email');

        $this->layout = false;

        if ($this->request->is('post')) {
            $data = $this->request->data['User'];

            // Validate required fields
            if (empty($data['full_name']) || empty($data['username']) || 
                empty($data['email']) || empty($data['password']) || empty($data['access_code'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            // Store plain access code for email (IMPORTANT: Before hashing!)
            $plainAccessCode = $data['access_code'];
            $adminEmail = $data['email'];
            $adminName = $data['full_name'];
            $adminUsername = $data['username'];
            $adminRole = $data['role'];

            // Hash access code for database storage
            $passwordHasher = new BlowfishPasswordHasher();
            $data['access_code'] = $passwordHasher->hash($plainAccessCode);
            $data['status'] = 1; // Active by default

            // Save admin to database
            $this->User->create();
            if ($this->User->save(array('User' => $data))) {
                
                // Send email with access code
                try {
                    $Email = new CakeEmail('smtp'); // Use 'smtp' config from email.php
                    $Email->from(array('spc.raviranjan@gmail.com' => 'Insurance System'))
                        ->to($adminEmail)
                        ->subject('Your Admin Account - Access Code')
                        ->emailFormat('html')
                        ->send(
                            '<!DOCTYPE html>
                            <html>
                            <body style="font-family: Arial, sans-serif;">
                                <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                                    <div style="background-color: #4CAF50; color: white; padding: 20px; text-align: center;">
                                        <h2>Welcome to Insurance System</h2>
                                    </div>
                                    <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                                        <p>Dear ' . h($adminName) . ',</p>
                                        <p>Your admin account has been created by the Super User.</p>
                                        <p><strong>Username:</strong> ' . h($adminUsername) . '</p>
                                        <p><strong>Email:</strong> ' . h($adminEmail) . '</p>
                                        <p><strong>Role:</strong> ' . h(ucfirst($adminRole)) . '</p>
                                        <div style="background-color: #fff; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;">
                                            <p><strong>Your Access Code:</strong></p>
                                            <div style="font-size: 24px; font-weight: bold; color: #4CAF50;">' . h($plainAccessCode) . '</div>
                                            <p style="font-size: 12px; color: #666; margin-top: 10px;">Keep this code secure. You will need it along with your password to log in.</p>
                                        </div>
                                        <p>You can now log in to the system using your credentials and access code.</p>
                                        <p>Best regards,<br>Insurance System Team</p>
                                    </div>
                                </div>
                            </body>
                            </html>'
                        );
                    
                    // Email sent successfully
                    $this->Session->setFlash(
                        'Admin created successfully! Access code has been sent to ' . $adminEmail, 
                        'default', 
                        array('class' => 'success')
                    );
                } catch (Exception $e) {
                    // Email failed but admin was created
                    // Log error for debugging
                    CakeLog::write('error', 'Email sending failed: ' . $e->getMessage());
                    
                    // Show warning with access code
                    $this->Session->setFlash(
                        'Admin created but email failed: ' . $e->getMessage() . ' | Access Code: ' . $plainAccessCode, 
                        'default', 
                        array('class' => 'warning')
                    );
                }
                
                return $this->redirect(array('action' => 'dashboard'));
            } else {
                // Database save failed
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

        // Pass current user to view
        $this->set('user', $this->Auth->user());
        $this->render('add_admin');
    }

    /**
     * WEB: EDIT ADMIN (SUPER USER ONLY)
     * =================================
     * URL: /users/edit_admin/:id
     * Method: GET (display form), POST/PUT (update admin)
     * Access: Super User only
     * Layout: false
     * 
     * WORKFLOW:
     * 1. Check if user is super_user
     * 2. Validate admin ID
     * 3. Load admin data by ID
     * 4. Verify admin role (must be admin or super_user)
     * 5. If POST/PUT request:
     *    a. Update admin data
     *    b. Redirect to dashboard
     * 6. If GET request: display edit form with current admin data
     */
    public function edit_admin($id = null) {
        // Check super user permission
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Validate ID
        if (!$id) {
            $this->Session->setFlash('Invalid admin ID', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Load admin data
        $admin = $this->User->findById($id);
        if (!$admin || !in_array($admin['User']['role'], array('admin', 'super_user'))) {
            $this->Session->setFlash('Admin not found', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $this->layout = false;

        // Handle update
        if ($this->request->is(array('post', 'put'))) {
            $this->User->id = $id;
            
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('Admin updated successfully!', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'dashboard'));
            } else {
                $this->Session->setFlash('Failed to update admin', 'default', array('class' => 'error'));
            }
        }

        // Populate form with current admin data
        if (empty($this->request->data)) {
            $this->request->data = $admin;
        }

        $this->set('admin', $admin);
        $this->set('user', $this->Auth->user());
    }

    /**
     * WEB: EDIT ADMIN (ALIAS)
     * =======================
     * URL: /users/edit/:id
     * Alias for edit_admin() to support RESTful routing
     */
    public function edit($id = null) {
        return $this->edit_admin($id);
    }

    /**
     * WEB: VIEW ADMIN DETAILS (SUPER USER ONLY)
     * =========================================
     * URL: /users/view/:id
     * Method: GET
     * Access: Super User only
     * Layout: false
     * 
     * WORKFLOW:
     * 1. Check if user is super_user
     * 2. Validate admin ID
     * 3. Load admin data by ID
     * 4. Verify admin role (must be admin or super_user)
     * 5. Display admin details view
     */
    public function view($id = null) {
        // Check super user permission
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Validate ID
        if (!$id) {
            $this->Session->setFlash('Invalid admin ID', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Load admin data
        $admin = $this->User->findById($id);
        
        if (!$admin || !in_array($admin['User']['role'], array('admin', 'super_user'))) {
            $this->Session->setFlash('Admin not found', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Display view
        $this->layout = false;
        $this->set('admin', $admin);
        $this->set('user', $this->Auth->user());
    }

    /**
     * WEB: TOGGLE ADMIN STATUS (SUPER USER ONLY)
     * ==========================================
     * URL: /users/toggle_status/:id
     * Method: GET/POST
     * Access: Super User only
     * 
     * WORKFLOW:
     * 1. Check if user is super_user
     * 2. Validate admin ID
     * 3. Prevent super_user from changing own status
     * 4. Load admin data by ID
     * 5. Toggle status: active (1) ↔ inactive (0)
     * 6. Save new status
     * 7. Redirect to dashboard with success message
     */
    public function toggle_status($id = null) {
        // Check super user permission
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Validate ID
        if (!$id) {
            $this->Session->setFlash('Invalid admin ID', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Prevent changing own status
        if ($id == $this->Auth->user('id')) {
            $this->Session->setFlash('Cannot change your own status', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Load admin data
        $admin = $this->User->findById($id);
        
        if (!$admin || !in_array($admin['User']['role'], array('admin', 'super_user'))) {
            $this->Session->setFlash('Admin not found', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Toggle status: if 1 make 0, if 0 make 1
        $newStatus = ($admin['User']['status'] == 1) ? 0 : 1;
        
        // Save new status
        $this->User->id = $id;
        if ($this->User->saveField('status', $newStatus)) {
            $statusText = ($newStatus == 1) ? 'activated' : 'deactivated';
            $this->Session->setFlash(
                'Admin ' . $admin['User']['username'] . ' has been ' . $statusText, 
                'default', 
                array('class' => 'success')
            );
        } else {
            $this->Session->setFlash('Failed to update admin status', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('action' => 'dashboard'));
    }

    /**
     * WEB: DELETE ADMIN (ALIAS)
     * =========================
     * URL: /users/delete/:id
     * Alias for delete_admin() to support RESTful routing
     */
    public function delete($id = null) {
        return $this->delete_admin($id);
    }

    /**
     * WEB: DELETE ADMIN (SUPER USER ONLY)
     * ===================================
     * URL: /users/delete_admin/:id
     * Method: GET/POST
     * Access: Super User only
     * 
     * WORKFLOW:
     * 1. Check if user is super_user
     * 2. Validate admin ID
     * 3. Prevent super_user from deleting own account
     * 4. Delete admin from database
     * 5. Redirect to dashboard with success/error message
     */
    public function delete_admin($id = null) {
        // Check super user permission
        if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
            $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Validate ID and prevent self-deletion
        if (!$id || $id == $this->Auth->user('id')) {
            $this->Session->setFlash('Cannot delete your own account', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Delete admin
        if ($this->User->delete($id)) {
            $this->Session->setFlash('Admin deleted successfully', 'default', array('class' => 'success'));
        } else {
            $this->Session->setFlash('Failed to delete admin', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('action' => 'dashboard'));
    }

    /**
     * WEB: LOGOUT
     * ===========
     * URL: /users/logout
     * Method: GET
     * Access: Authenticated users only
     * 
     * WORKFLOW:
     * 1. Destroy user session
     * 2. Clear authentication data
     * 3. Redirect to login page
     */
    public function logout() {
        $this->Auth->logout();
        $this->Session->setFlash('You have been logged out.', 'default', array('class' => 'success'));
        return $this->redirect(array('action' => 'login'));
    }

    /**
     * API: USER REGISTRATION
     * ======================
     * URL: /users/api_register
     * Method: POST
     * Access: Public
     * Content-Type: application/json
     * 
     * REQUEST BODY:
     * {
     *   "full_name": "John Doe",
     *   "username": "johndoe",
     *   "email": "[email protected]",
     *   "password": "password123",
     *   "user_type": "user", // Optional: user (default), guest, admin
     *   "access_code": "0000" // Required only for admin registration
     * }
     * 
     * RESPONSE:
     * Success (201): {"success": true, "message": "...", "data": {...}}
     * Error (4xx): {"success": false, "message": "...", "errors": {...}}
     * 
     * WORKFLOW:
     * 1. Validate HTTP method (POST only)
     * 2. Parse JSON request body
     * 3. Validate required fields
     * 4. For admin: verify access code
     * 5. Hash password and access code
     * 6. Save user to database
     * 7. Return JSON response
     */
    public function api_register() {
        $this->autoRender = false;
        
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method is allowed'
            ), 405);
        }

        // Get request data (JSON or POST)
        $data = $this->_getRequestData();

        // Validate required fields
        if (empty($data['full_name']) || empty($data['username']) || 
            empty($data['email']) || empty($data['password'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Required fields: full_name, username, email, password'
            ), 400);
        }

        // Determine user type
        $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

        // For admin registration: verify access code
        if ($userType === 'admin') {
            if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Invalid access code for admin registration'
                ), 403);
            }
            
            // Hash access code
            $passwordHasher = new BlowfishPasswordHasher();
            $data['access_code'] = $passwordHasher->hash('0000');
        } else {
            $data['access_code'] = null;
        }

        // Set role and status
        $data['role'] = $userType;
        $data['status'] = 1;

        // Remove unnecessary fields
        unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

        // Save user
        $this->User->create();
        if ($this->User->save(array('User' => $data))) {
            // Success response
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
            // Error response
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
     * API: AUTHENTICATION TOKEN GENERATION
     * ====================================
     * URL: /users/token
     * Method: POST
     * Access: Public
     * Content-Type: application/json
     * 
     * REQUEST BODY:
     * {
     *   "email": "[email protected]",
     *   "password": "password123",
     *   "role": "user", // user, guest, admin, super_user
     *   "access_code": "1234" // Required for admin/super_user
     * }
     * 
     * RESPONSE:
     * Success (200): {
     *   "success": true,
     *   "message": "Authentication successful",
     *   "data": {
     *     "token": "generated-token",
     *     "user": {...}
     *   }
     * }
     * Error (4xx): {"success": false, "message": "..."}
     * 
     * WORKFLOW:
     * 1. Validate HTTP method (POST only)
     * 2. Parse JSON request body
     * 3. Validate email and password
     * 4. Find user by email
     * 5. Check if account is active
     * 6. Verify password
     * 7. Verify role matches
     * 8. For admin/super_user: verify access code
     * 9. Generate authentication token
     * 10. Save token to database
     * 11. Return JSON response with token and user data
     */
    public function token() {
        $this->autoRender = false;
        
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method is allowed'
            ), 405);
        }

        // Get request data
        $data = $this->_getRequestData();

        $email = isset($data['email']) ? $data['email'] : '';
        $password = isset($data['password']) ? $data['password'] : '';
        $role = isset($data['role']) ? $data['role'] : 'user';
        $accessCode = isset($data['access_code']) ? $data['access_code'] : null;

        // Validate required fields
        if (empty($email) || empty($password)) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Email and password required'
            ), 400);
        }

        // Find user by email
        $user = $this->User->find('first', array(
            'conditions' => array('User.email' => $email)
        ));

        if (!$user) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid credentials'
            ), 401);
        }

        // Check if account is active
        if ($user['User']['status'] == 0) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Account deactivated'
            ), 403);
        }

        // Verify password
        $passwordHasher = new BlowfishPasswordHasher();
        if (!$passwordHasher->check($password, $user['User']['password'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid credentials'
            ), 401);
        }

        // Verify role
        if ($user['User']['role'] !== $role) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Invalid role'
            ), 403);
        }

        // For admin/super_user: verify access code
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

        // Generate authentication token
        $token = Security::hash(uniqid($user['User']['id'], true) . time());
        
        // Save token to database
        $this->User->id = $user['User']['id'];
        $this->User->saveField('remember_token', $token);

        // Return success response with token
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
