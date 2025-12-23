<?php
/**
 * Users Controller - Enhanced with Super Admin functionality
 */
App::uses('AppController', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('login', 'signup', 'logout');
    }

    /**
     * Check if current user is super admin
     */
    private function _isSuperAdmin() {
        return ($this->Auth->user('role') === 'super_user');
    }

    /**
     * Check if current user is admin or super admin
     */
    private function _isAdminOrAbove() {
        $role = $this->Auth->user('role');
        return in_array($role, array('admin', 'super_user'));
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

        // For admin + super_user we also show policy stats + short list
        if (in_array($role, array('admin', 'super_user'))) {
            $this->loadModel('Policy');

            // Stats for cards
            $stats = array(
                'total'    => $this->Policy->find('count'),
                'active'   => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'active')
                )),
                'draft'    => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'draft')
                )),
                'archived' => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'archived')
                )),
            );
            $this->set(compact('stats'));

            // Latest policies for “Policy List” table on dashboard
            $policies = $this->Policy->find('all', array(
                'order' => array('Policy.created' => 'DESC'),
                'limit' => 5
            ));
            $this->set(compact('policies'));
        }

        // Load role-specific view
        switch ($role) {
            case 'super_user':
                return $this->_superAdminDashboard();
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
     * Super Admin Dashboard
     */
    private function _superAdminDashboard() {
        $totalAdmins = $this->User->find('count', array(
            'conditions' => array('User.role' => array('admin', 'super_user'))
        ));

        $activeAdmins = $this->User->find('count', array(
            'conditions' => array(
                'User.role'   => array('admin', 'super_user'),
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

        $this->render('dashboard_super_user');
    }

    /**
     * View all users (Super Admin only)
     */
    public function manage_users() {
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash('You are not authorized to access this page.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $users = $this->User->find('all', array(
            'conditions' => array('User.role' => array('user', 'guest')),
            'fields' => array(
                'User.id', 'User.full_name', 'User.username', 'User.email',
                'User.role', 'User.status', 'User.created', 'User.modified'
            ),
            'order' => array('User.id' => 'DESC')
        ));

        $this->set('users', $users);
    }

    /**
     * Add Admin (Super Admin only)
     */
    public function add_admin() {
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash('You are not authorized to access this page.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is('post')) {
            $this->User->create();

            $data = $this->request->data['User'];

            if (empty($data['full_name']) || empty($data['username']) ||
                empty($data['email']) || empty($data['password'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            $passwordHasher = new BlowfishPasswordHasher();
            if ($data['role'] === 'admin' || $data['role'] === 'super_user') {
                $accessCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $data['access_code'] = $passwordHasher->hash($accessCode);
                $plainAccessCode     = $accessCode;
            } else {
                $data['access_code'] = null;
                $plainAccessCode     = null;
            }

            $data['status'] = 1;

            if ($this->User->save(array('User' => $data))) {
                $message = 'Admin created successfully!';
                if ($plainAccessCode) {
                    $message .= ' Access Code: ' . $plainAccessCode . ' (Save this, it won\'t be shown again)';
                }
                $this->Session->setFlash($message, 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'dashboard'));
            } else {
                $errors   = $this->User->validationErrors;
                $errorMsg = 'Failed to create admin.';

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
     * View User Details (Super Admin/Admin)
     */
    public function view($id = null) {
        if (!$this->_isAdminOrAbove()) {
            $this->Session->setFlash('You are not authorized to access this page.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id) {
            $this->Session->setFlash('Invalid user ID.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $user = $this->User->findById($id);

        if (!$user) {
            $this->Session->setFlash('User not found.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $this->set('user', $user);
    }

    /**
     * Edit User (Super Admin only) – DOES NOT touch Auth->login()
     */
    public function edit($id = null) {
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash('You are not authorized to access this page.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id) {
            $this->Session->setFlash('Invalid user ID.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $user = $this->User->findById($id);

        if (!$user) {
            $this->Session->setFlash('User not found.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->data['User'];

            if ($id == $this->Auth->user('id') && $user['User']['role'] === 'super_user') {
                unset($data['role']);
            }

            if (empty($data['password'])) {
                unset($data['password']);
            }

            if (isset($data['regenerate_access_code']) && $data['regenerate_access_code']) {
                if (in_array($data['role'], array('admin', 'super_user'))) {
                    $passwordHasher = new BlowfishPasswordHasher();
                    $accessCode     = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                    $data['access_code'] = $passwordHasher->hash($accessCode);
                    $this->Session->write('new_access_code', $accessCode);
                }
            }

            $this->User->id = $id;
            if ($this->User->save(array('User' => $data))) {
                $message = 'User updated successfully!';
                if ($this->Session->check('new_access_code')) {
                    $message .= ' New Access Code: ' . $this->Session->read('new_access_code');
                    $this->Session->delete('new_access_code');
                }
                $this->Session->setFlash($message, 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'dashboard'));
            } else {
                $this->Session->setFlash('Failed to update user.', 'default', array('class' => 'error'));
            }
        }

        if (!$this->request->data) {
            $this->request->data = $user;
            unset($this->request->data['User']['password']);
        }

        $this->set('user', $user);
    }

    /**
     * Delete User (Super Admin only)
     */
    public function delete($id = null) {
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash('You are not authorized to access this page.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id) {
            $this->Session->setFlash('Invalid user ID.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($id == $this->Auth->user('id')) {
            $this->Session->setFlash('You cannot delete your own account.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $user = $this->User->findById($id);

        if (!$user) {
            $this->Session->setFlash('User not found.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($this->User->delete($id)) {
            $this->Session->setFlash('User deleted successfully!', 'default', array('class' => 'success'));
        } else {
            $this->Session->setFlash('Failed to delete user.', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('action' => 'dashboard'));
    }

    /**
     * Toggle User Status (Super Admin only)
     */
    public function toggle_status($id = null) {
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash('You are not authorized to perform this action.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id) {
            $this->Session->setFlash('Invalid user ID.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if ($id == $this->Auth->user('id')) {
            $this->Session->setFlash('You cannot change your own status.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $user = $this->User->findById($id);

        if (!$user) {
            $this->Session->setFlash('User not found.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $newStatus = ($user['User']['status'] == 1) ? 0 : 1;

        $this->User->id = $id;
        if ($this->User->saveField('status', $newStatus)) {
            $statusText = ($newStatus == 1) ? 'activated' : 'deactivated';
            $this->Session->setFlash("User {$statusText} successfully!", 'default', array('class' => 'success'));
        } else {
            $this->Session->setFlash('Failed to update user status.', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('action' => 'dashboard'));
    }

    /**
     * Regenerate Access Code (Super Admin only)
     */
    public function regenerate_access_code($id = null) {
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash('You are not authorized to perform this action.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!$id) {
            $this->Session->setFlash('Invalid user ID.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $user = $this->User->findById($id);

        if (!$user) {
            $this->Session->setFlash('User not found.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        if (!in_array($user['User']['role'], array('admin', 'super_user'))) {
            $this->Session->setFlash('Access code can only be generated for admin users.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $passwordHasher = new BlowfishPasswordHasher();
        $accessCode     = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $this->User->id = $id;
        if ($this->User->saveField('access_code', $passwordHasher->hash($accessCode))) {
            $this->Session->setFlash("New access code generated: {$accessCode} (Save this, it won't be shown again)", 'default', array('class' => 'success'));
        } else {
            $this->Session->setFlash('Failed to regenerate access code.', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('action' => 'dashboard'));
    }

    /**
     * Login action - Web interface
     */
    public function login() {
        if ($this->Auth->user()) {
            $role = $this->Auth->user('role');
            return $this->redirect($this->_getDashboardUrl($role));
        }

        $selectedRole = null;
        if ($this->request->is('post')) {
            if (isset($this->request->data['User']['role'])) {
                $selectedRole = $this->request->data['User']['role'];
                $this->Session->write('login.selected_role', $selectedRole);
            }
        } else {
            $selectedRole = $this->Session->read('login.selected_role');
        }

        $this->set('selectedRole', $selectedRole ?: 'user');

        if ($this->request->is('post')) {
            if ($this->RequestHandler->prefers('json') || $this->request->header('Accept') === 'application/json') {
                return $this->_apiLogin();
            }

            $email      = !empty($this->request->data['User']['email']) ? $this->request->data['User']['email'] : '';
            $password   = !empty($this->request->data['User']['password']) ? $this->request->data['User']['password'] : '';
            $role       = !empty($this->request->data['User']['role']) ? $this->request->data['User']['role'] : 'user';
            $accessCode = !empty($this->request->data['User']['access_code']) ? $this->request->data['User']['access_code'] : null;
            $rememberMe = !empty($this->request->data['User']['remember_me']);

            if (empty($email) || empty($password)) {
                $this->Session->setFlash('Please enter valid Email ID', 'default', array('class' => 'error'));
                return;
            }

            $user = $this->User->find('first', array(
                'conditions' => array('User.email' => $email),
                'fields' => array('id', 'email', 'password', 'role', 'access_code', 'full_name', 'username', 'status', 'image')
            ));

            if (!$user) {
                $this->Session->setFlash('The email address or password is incorrect. Please try again or reset your password', 'default', array('class' => 'error'));
                return;
            }

            if ($user['User']['status'] == 0) {
                $this->Session->setFlash('Your account has been deactivated. Please contact administrator.', 'default', array('class' => 'error'));
                return;
            }

            $passwordHasher = new BlowfishPasswordHasher();
            if (!$passwordHasher->check($password, $user['User']['password'])) {
                $this->Session->setFlash('The email address or password is incorrect. Please try again or reset your password', 'default', array('class' => 'error'));
                return;
            }

            if ($user['User']['role'] !== $role) {
                $this->Session->setFlash('Invalid user role. Please select the correct role from the dropdown.', 'default', array('class' => 'error'));
                return;
            }

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

            if ($rememberMe) {
                $token = Security::hash(uniqid(rand(), true));
                $this->User->id = $user['User']['id'];
                $this->User->saveField('remember_token', $token);
                $this->Cookie->write('remember_token', $token, true, '+2 weeks');
            }

            $this->Session->delete('login.selected_role');

            $this->Auth->login($user['User']);
            $this->Session->write('Auth.User', $user['User']);
            $this->Session->write('Auth.User.role', $user['User']['role']);

            return $this->redirect($this->_getDashboardUrl($role));
        }
    }

    /**
     * Profile - logged in user updates own details
     */
    public function profile() {
        // Must be logged in
        $userId = $this->Auth->user('id');
        if (!$userId) {
            $this->Session->setFlash('Please login to access your profile.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'login'));
        }

        // Load current user
        $user = $this->User->findById($userId);
        if (!$user) {
            $this->Session->setFlash('User not found.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'logout'));
        }

        // Handle POST/PUT
        if ($this->request->is(array('post', 'put'))) {

            // Prevent role/status change
            unset($this->request->data['User']['role'], $this->request->data['User']['status']);

            // Preserve username
            if (empty($this->request->data['User']['username']) && !empty($user['User']['username'])) {
                $this->request->data['User']['username'] = $user['User']['username'];
            }

            // Image upload
            if (!empty($this->request->data['User']['image']['tmp_name'])) {
                $file = $this->request->data['User']['image'];

                $allowedTypes = array('image/jpeg', 'image/png', 'image/gif');
                if (!in_array($file['type'], $allowedTypes)) {
                    $this->Session->setFlash('Please upload a valid image (JPG, PNG, GIF).', 'default', array('class' => 'error'));
                    return;
                }

                $uploadDir = WWW_ROOT . 'img' . DS . 'users' . DS;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'user_' . $userId . '_' . time() . '.' . $ext;
                $fullPath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                    $this->request->data['User']['image'] = 'users/' . $fileName;
                } else {
                    $this->Session->setFlash('Failed to upload image. Please try again.', 'default', array('class' => 'error'));
                    return;
                }
            } else {
                unset($this->request->data['User']['image']);
            }

            $this->User->id = $userId;
            if ($this->User->save($this->request->data)) {
                // Refresh Auth session for THIS user only
                $updated = $this->User->findById($userId);
                $this->Auth->login($updated['User']);

                $this->Session->setFlash('Profile updated successfully.', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'profile'));
            } else {
                CakeLog::write('debug', 'PROFILE VALIDATION ERRORS: ' . print_r($this->User->validationErrors, true));
                $this->Session->setFlash('Failed to update profile. Please check the form.', 'default', array('class' => 'error'));
            }
        }

        if (empty($this->request->data)) {
            $this->request->data = $user;
        }

        $this->set('user', $user);
    }

    /**
     * Signup action - Web interface
     */
    public function signup() {
        if ($this->request->is('post')) {
            if ($this->RequestHandler->prefers('json') || $this->request->header('Accept') === 'application/json') {
                return $this->_apiSignup();
            }

            CakeLog::write('debug', 'Signup POST data: ' . print_r($this->request->data, true));

            $this->User->create();

            $data = isset($this->request->data['User']) ? $this->request->data['User'] : array();

            if (empty($data)) {
                $this->Session->setFlash('No data received. Please fill in all fields.', 'default', array('class' => 'error'));
                return;
            }

            $userType = isset($data['user_type']) ? $data['user_type'] : 'user';

            if (empty($data['full_name']) || empty($data['username']) || empty($data['email']) ||
                empty($data['confirm_email']) || empty($data['password']) || empty($data['confirm_password'])) {
                $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
                return;
            }

            if ($data['password'] !== $data['confirm_password']) {
                $this->Session->setFlash('Passwords do not match. Please try again.', 'default', array('class' => 'error'));
                return;
            }

            if ($data['email'] !== $data['confirm_email']) {
                $this->Session->setFlash('Email addresses do not match. Please try again.', 'default', array('class' => 'error'));
                return;
            }

            if ($userType === 'admin') {
                if (empty($data['access_code']) || $data['access_code'] !== '0000') {
                    $this->Session->setFlash('Invalid access code. Please enter the correct code.', 'default', array('class' => 'error'));
                    return;
                }

                $passwordHasher     = new BlowfishPasswordHasher();
                $data['access_code'] = $passwordHasher->hash('0000');
            } else {
                $data['access_code'] = null;
            }

            $data['role']   = $userType;
            $data['status'] = 1;

            unset($data['confirm_password'], $data['confirm_email'], $data['user_type']);

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
     * Logout action
     */
    public function logout() {
        $this->Cookie->delete('remember_token');
        return $this->redirect($this->Auth->logout());
    }

    /**
     * Get dashboard URL based on role
     */
    private function _getDashboardUrl($role) {
        return array('controller' => 'users', 'action' => 'dashboard');
    }
}
