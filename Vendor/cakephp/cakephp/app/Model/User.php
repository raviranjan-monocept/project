<?php
/**
 * User Model
 */
App::uses('AppModel', 'Model');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class User extends AppModel {

    public $name = 'User';

    public $validate = array(
        'full_name' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter your full name',
                'required' => true
            )
        ),
        'username' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a username',
                'required' => true
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This username is already taken'
            ),
            'minLength' => array(
                'rule' => array('minLength', 3),
                'message' => 'Username must be at least 3 characters long'
            )
        ),
        'email' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter your email address',
                'required' => true
            ),
            'email' => array(
                'rule' => 'email',
                'message' => 'Please enter a valid email address'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This email address is already registered'
            )
        ),
        'password' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Please enter a password',
                'required' => true,
                'on' => 'create'
            ),
            'minLength' => array(
                'rule' => array('minLength', 6),
                'message' => 'Password must be at least 6 characters long'
            )
        ),
        'role' => array(
            'valid' => array(
                'rule' => array('inList', array('user', 'guest', 'admin', 'super_user')),
                'message' => 'Please select a valid role',
                'allowEmpty' => false
            )
        )
    );

    /**
     * Before save callback
     */
    public function beforeSave($options = array()) {
        // Hash password if it's being set/changed
        if (isset($this->data[$this->alias]['password'])) {
            $passwordHasher = new BlowfishPasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
        }
        
        // Ensure created and modified timestamps
        if (!$this->id) {
            $this->data[$this->alias]['created'] = date('Y-m-d H:i:s');
        }
        $this->data[$this->alias]['modified'] = date('Y-m-d H:i:s');
        
        return true;
    }

    /**
     * Find user by email and role
     */
    public function findByEmailAndRole($email, $role) {
        return $this->find('first', array(
            'conditions' => array(
                'User.email' => $email,
                'User.role' => $role
            )
        ));
    }

    /**
     * Verify access code
     */
    public function verifyAccessCode($userId, $code) {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }

        $passwordHasher = new BlowfishPasswordHasher();
        return $passwordHasher->check($code, $user['User']['access_code']);
    }
}