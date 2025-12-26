<?php
App::uses('AppModel', 'Model');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class User extends Model {
    
    public $name = 'User';
    
    // Validation rules
    public $validate = array(
        'full_name' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Full name is required'
            )
        ),
        'username' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Username is required'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This username is already taken',
                'on' => 'update' // Only check on update if different
            )
        ),
        'email' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Please provide a valid email'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This email is already registered',
                'on' => 'update'
            )
        ),
        'password' => array(
            'notEmpty' => array(
                'rule' => 'notEmpty',
                'message' => 'Password is required',
                'on' => 'create' // Only on registration
            )
        )
    );
    
    // Hash password before saving
    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['password']) && !empty($this->data[$this->alias]['password'])) {
            $passwordHasher = new BlowfishPasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
        }
        return true;
    }
}
