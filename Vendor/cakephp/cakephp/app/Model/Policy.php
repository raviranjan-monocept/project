<?php
App::uses('AppModel', 'Model');

class Policy extends AppModel {
    
    public $name = 'Policy';
    
    /**
     * Associations - Define relationship with Category model
     * This tells CakePHP to automatically fetch category data with policies
     */
    public $belongsTo = array(
        'Category' => array(
            'className' => 'Category',
            'foreignKey' => 'category_id', // The field in policies table
            'conditions' => '',
            'fields' => array('id', 'name', 'active'),
            'order' => ''
        )
    );
    
    /**
     * Validation rules
     */
    public $validate = array(
        'title' => array(
            'rule' => 'notBlank',
            'message' => 'Title is required'
        ),
        'status' => array(
            'rule' => array('inList', array('active', 'draft', 'archived')),
            'message' => 'Invalid status'
        )
    );
}
