<?php
App::uses('AppModel', 'Model');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

class User extends AppModel {
    
    public $name = 'User';
    
    public $validate = array(
        'full_name' => array(
            'rule' => 'notBlank',
            'message' => 'Full name is required'
        ),
        'username' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Username is required'
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This username is already taken'
            )
        ),
        'email' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Email is required'
            ),
            'email' => array(
                'rule' => 'email',
                'message' => 'Please provide a valid email address'
            ),
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This email is already registered'
            )
        ),
        'password' => array(
            'rule' => 'notBlank',
            'message' => 'Password is required',
            'on' => 'create'
        )
    );
    
    public function beforeSave($options = array()) {
        if (isset($this->data[$this->alias]['password']) && !empty($this->data[$this->alias]['password'])) {
            $passwordHasher = new BlowfishPasswordHasher();
            $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
        }
        return true;
    }
}
