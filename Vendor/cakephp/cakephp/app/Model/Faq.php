<?php
App::uses('AppModel', 'Model');

class Faq extends AppModel {
    
    public $name = 'Faq';
    
    public $validate = array(
        'category' => array(
            'required' => array(
                'rule' => 'notBlank',
                'message' => 'Category is required'
            )
        ),
        'question' => array(
            'required' => array(
                'rule' => 'notBlank',
                'message' => 'Question is required'
            ),
            'minLength' => array(
                'rule' => array('minLength', 10),
                'message' => 'Question must be at least 10 characters'
            )
        ),
        'answer' => array(
            'required' => array(
                'rule' => 'notBlank',
                'message' => 'Answer is required'
            ),
            'minLength' => array(
                'rule' => array('minLength', 20),
                'message' => 'Answer must be at least 20 characters'
            )
        ),
        'display_order' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'message' => 'Display order must be a number',
                'allowEmpty' => true
            )
        ),
        'status' => array(
            'valid' => array(
                'rule' => array('inList', array('active', 'inactive')),
                'message' => 'Invalid status'
            )
        )
    );
    
    public $belongsTo = array(
        'Creator' => array(
            'className' => 'User',
            'foreignKey' => 'created_by',
            'conditions' => '',
            'fields' => array('id', 'full_name', 'email'),
            'order' => ''
        )
    );
    
    /**
     * Get FAQs grouped by category
     */
    public function getGroupedFaqs($status = 'active') {
        $faqs = $this->find('all', array(
            'conditions' => array('Faq.status' => $status),
            'order' => array('Faq.category' => 'ASC', 'Faq.display_order' => 'ASC'),
            'contain' => array('Creator')
        ));
        
        $grouped = array();
        foreach ($faqs as $faq) {
            $category = $faq['Faq']['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = array();
            }
            $grouped[$category][] = $faq;
        }
        
        return $grouped;
    }
    
    /**
     * Get featured FAQs
     */
    public function getFeatured($limit = 5) {
        return $this->find('all', array(
            'conditions' => array(
                'Faq.status' => 'active',
                'Faq.is_featured' => 1
            ),
            'order' => array('Faq.display_order' => 'ASC'),
            'limit' => $limit
        ));
    }
    
    /**
     * Search FAQs
     */
    public function search($keyword) {
        return $this->find('all', array(
            'conditions' => array(
                'Faq.status' => 'active',
                'OR' => array(
                    'Faq.question LIKE' => '%' . $keyword . '%',
                    'Faq.answer LIKE' => '%' . $keyword . '%',
                    'Faq.category LIKE' => '%' . $keyword . '%'
                )
            ),
            'order' => array('Faq.display_order' => 'ASC')
        ));
    }
}
