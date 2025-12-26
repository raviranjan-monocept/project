<?php
App::uses('AppModel', 'Model');

class Category extends AppModel {
    public $name = 'Category';

    public $validate = array(
        'name' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Category name is required'
            )
        )
    );

    // Ensure category names are unique so DB unique constraint doesn't raise an exception
    public function beforeValidate($options = array()) {
        if (isset($this->data[$this->alias]['name'])) {
            $this->validate['name']['isUnique'] = array(
                'rule' => 'isUnique',
                'message' => 'Category name must be unique'
            );
        }
        return parent::beforeValidate($options);
    }
}
