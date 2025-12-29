<?php
/**
 * FAQs REST API Controller
 * 
 * All CRUD operations via REST API
 * Only super_user role can create, update, delete FAQs
 * Regular users can only view FAQs
 */
App::uses('AppController', 'Controller');

class FaqsController extends AppController {

    public $components = array('RequestHandler');
    
    /**
     * Setup authentication and permissions
     */
   public function beforeFilter() {
    parent::beforeFilter();
    
    // Allow public access to these actions
    $this->Auth->allow('api_index', 'api_view', 'api_categories', 'api_search');
    
    // Disable CSRF for API endpoints AND add/edit/delete actions
    $disableCsrfActions = array('api_register', 'token', 'api_index', 'api_view', 'api_add', 'api_edit', 'api_delete', 'api_categories', 'api_search', 'add', 'edit', 'delete');
    
    if (in_array($this->request->params['action'], $disableCsrfActions)) {
        if (isset($this->Security)) {
            $this->Security->csrfCheck = false;
            $this->Security->validatePost = false;
        }
    }
}

    
    /**
     * Check if current user is super admin
     */
    private function _isSuperAdmin() {
        return ($this->Auth->user('role') === 'super_user');
    }
    
    /**
     * Send JSON response
     */
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false;
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }
    
    /**
     * Get request data from JSON or form
     */
    private function _getRequestData() {
        $data = $this->request->input('json_decode', true);
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }

    /**
 * WEB: FAQ Management Page (Super User Only)
 * URL: /faqs/manage
 */
public function manage() {
    if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
        $this->Session->setFlash('Access denied. Super user permission required.', 'default', array('class' => 'error'));
        return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
    }

    // DON'T set layout - let it render as standalone page
    $this->layout = false;  // This is important!

    $faqs = $this->Faq->find('all', array(
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
    
    $this->set('faqs', $grouped);
}


/**
 * WEB: Add New FAQ (Super User Only)
 * URL: /faqs/add
 */
/**
 * WEB: Add New FAQ (Super User Only)
 * URL: /faqs/add
 */
public function add() {
    // Check super user permission
    if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
        $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
        return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
    }

    if ($this->request->is('post')) {
        // Get data from request
        $data = $this->request->data;
        
        // Debug: Check what data is being received
        // CakeLog::write('debug', 'FAQ Data: ' . print_r($data, true));
        
        // Validate required fields
        if (empty($data['Faq']['category']) || empty($data['Faq']['question']) || empty($data['Faq']['answer'])) {
            $this->Session->setFlash('Please fill in all required fields.', 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'manage'));
        }

        // Prepare FAQ data
        $faqData = array(
            'category' => $data['Faq']['category'],
            'question' => $data['Faq']['question'],
            'answer' => $data['Faq']['answer'],
            'display_order' => isset($data['Faq']['display_order']) ? $data['Faq']['display_order'] : 0,
            'is_featured' => isset($data['Faq']['is_featured']) ? 1 : 0,
            'status' => isset($data['Faq']['status']) ? $data['Faq']['status'] : 'active',
            'created_by' => $this->Auth->user('id')
        );

        // Try to save
        $this->Faq->create();
        if ($this->Faq->save(array('Faq' => $faqData))) {
            $this->Session->setFlash('FAQ created successfully!', 'default', array('class' => 'success'));
            return $this->redirect(array('action' => 'manage'));
        } else {
            // Get validation errors
            $errors = $this->Faq->validationErrors;
            $errorMsg = 'Failed to create FAQ: ';
            
            if (!empty($errors)) {
                foreach ($errors as $field => $error) {
                    $errorMsg .= $field . ' - ' . implode(', ', (array)$error) . '; ';
                }
            }
            
            $this->Session->setFlash($errorMsg, 'default', array('class' => 'error'));
            return $this->redirect(array('action' => 'manage'));
        }
    }

    return $this->redirect(array('action' => 'manage'));
}


/**
 * WEB: Edit FAQ (Super User Only)
 * URL: /faqs/edit/:id
 */
public function edit($id = null) {
    // Check super user permission
    if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
        $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
        return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
    }

    if (!$id) {
        $this->Session->setFlash('Invalid FAQ ID', 'default', array('class' => 'error'));
        return $this->redirect(array('action' => 'manage'));
    }

    $faq = $this->Faq->findById($id);
    if (!$faq) {
        $this->Session->setFlash('FAQ not found', 'default', array('class' => 'error'));
        return $this->redirect(array('action' => 'manage'));
    }

    $this->layout = 'dashboard';

    if ($this->request->is(array('post', 'put'))) {
        $this->Faq->id = $id;
        
        if ($this->Faq->save($this->request->data)) {
            $this->Session->setFlash('FAQ updated successfully!', 'default', array('class' => 'success'));
            return $this->redirect(array('action' => 'manage'));
        } else {
            $this->Session->setFlash('Failed to update FAQ', 'default', array('class' => 'error'));
        }
    }

    if (empty($this->request->data)) {
        $this->request->data = $faq;
    }

    $this->set('faq', $faq);
    $this->set('title_for_layout', 'Edit FAQ');
}

/**
 * WEB: Delete FAQ (Super User Only)
 * URL: /faqs/delete/:id
 */
public function delete($id = null) {
    // Check super user permission
    if (!$this->Auth->user() || !$this->_isSuperAdmin()) {
        $this->Session->setFlash('Access denied', 'default', array('class' => 'error'));
        return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
    }

    $this->request->allowMethod('post');

    if (!$id) {
        $this->Session->setFlash('Invalid FAQ ID', 'default', array('class' => 'error'));
        return $this->redirect(array('action' => 'manage'));
    }

    if ($this->Faq->delete($id)) {
        $this->Session->setFlash('FAQ deleted successfully', 'default', array('class' => 'success'));
    } else {
        $this->Session->setFlash('Failed to delete FAQ', 'default', array('class' => 'error'));
    }

    return $this->redirect(array('action' => 'manage'));
}

    /**
 * WEB: Display FAQ page for users
 * URL: /faqs or /faqs/index
 */
public function index() {
    // Set layout based on user login status
    if ($this->Auth->user()) {
        $this->layout = 'dashboard'; // Use dashboard layout if logged in
    } else {
        $this->layout = 'default'; // Use default layout for public
    }
    
    // Get all active FAQs grouped by category
    $faqs = $this->Faq->getGroupedFaqs('active');
    
    // Get all categories for filter
    $categories = $this->Faq->find('list', array(
        'fields' => array('DISTINCT Faq.category', 'Faq.category'),
        'conditions' => array('Faq.status' => 'active'),
        'order' => array('Faq.category' => 'ASC')
    ));
    
    $this->set(compact('faqs', 'categories'));
    $this->set('title_for_layout', 'FAQ / Help Center');
}

/**
 * WEB: Search FAQs
 * URL: /faqs/search
 */
public function search() {
    if ($this->Auth->user()) {
        $this->layout = 'dashboard';
    } else {
        $this->layout = 'default';
    }
    
    $keyword = '';
    $results = array();
    
    if ($this->request->is('post') || !empty($this->request->query('q'))) {
        $keyword = !empty($this->request->data['Faq']['keyword']) 
            ? $this->request->data['Faq']['keyword'] 
            : $this->request->query('q');
        
        if (!empty($keyword)) {
            $results = $this->Faq->search($keyword);
        }
    }
    
    $this->set(compact('keyword', 'results'));
    $this->set('title_for_layout', 'Search FAQs');
}

    /**
     * Check super user permission
     */
    private function _checkSuperUserPermission() {
        if (!$this->Auth->user()) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Authentication required'
            ), 401);
        }
        
        if (!$this->_isSuperAdmin()) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Access denied. Super user permission required.'
            ), 403);
        }
        
        return null; // Permission granted
    }
    
    /**
     * API: Get all FAQs (Public)
     * GET /api/faqs
     * Optional query params: category, status, featured
     */
    public function api_index() {
        $this->autoRender = false;
        
        if (!$this->request->is('get')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only GET method allowed'
            ), 405);
        }
        
        // Build conditions
        $conditions = array();
        
        // Filter by category
        if (!empty($this->request->query('category'))) {
            $conditions['Faq.category'] = $this->request->query('category');
        }
        
        // Filter by status (default: active for public, all for super admin)
        $status = $this->request->query('status');
        if ($this->_isSuperAdmin()) {
            if (!empty($status)) {
                $conditions['Faq.status'] = $status;
            }
        } else {
            $conditions['Faq.status'] = 'active';
        }
        
        // Filter by featured
        if ($this->request->query('featured') === '1') {
            $conditions['Faq.is_featured'] = 1;
        }
        
        // Get grouped or flat list
        $grouped = $this->request->query('grouped') === '1';
        
        if ($grouped) {
            $faqs = $this->Faq->getGroupedFaqs($conditions['Faq.status']);
        } else {
            $faqs = $this->Faq->find('all', array(
                'conditions' => $conditions,
                'order' => array('Faq.category' => 'ASC', 'Faq.display_order' => 'ASC'),
                'contain' => array('Creator')
            ));
        }
        
        return $this->_sendResponse(array(
            'success' => true,
            'count' => $grouped ? count($faqs) : count($faqs),
            'data' => $faqs
        ), 200);
    }
    
    /**
     * API: Get single FAQ (Public)
     * GET /api/faqs/:id
     */
    public function api_view($id = null) {
        $this->autoRender = false;
        
        if (!$this->request->is('get')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only GET method allowed'
            ), 405);
        }
        
        if (!$id) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ ID required'
            ), 400);
        }
        
        $faq = $this->Faq->find('first', array(
            'conditions' => array('Faq.id' => $id),
            'contain' => array('Creator')
        ));
        
        if (!$faq) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ not found'
            ), 404);
        }
        
        // Non-super users can only see active FAQs
        if (!$this->_isSuperAdmin() && $faq['Faq']['status'] !== 'active') {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ not found'
            ), 404);
        }
        
        return $this->_sendResponse(array(
            'success' => true,
            'data' => $faq
        ), 200);
    }
    
    /**
     * API: Create new FAQ (Super Admin Only)
     * POST /api/faqs
     */
    public function api_add() {
        $this->autoRender = false;
        
        // Check permission
        $permissionError = $this->_checkSuperUserPermission();
        if ($permissionError) return $permissionError;
        
        if (!$this->request->is('post')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only POST method allowed'
            ), 405);
        }
        
        $data = $this->_getRequestData();
        
        // Validate required fields
        if (empty($data['category']) || empty($data['question']) || empty($data['answer'])) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Required fields: category, question, answer'
            ), 400);
        }
        
        // Set created_by
        $data['created_by'] = $this->Auth->user('id');
        
        // Set defaults
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }
        if (!isset($data['display_order'])) {
            $data['display_order'] = 0;
        }
        if (!isset($data['is_featured'])) {
            $data['is_featured'] = 0;
        }
        
        $this->Faq->create();
        if ($this->Faq->save(array('Faq' => $data))) {
            $faqId = $this->Faq->id;
            $faq = $this->Faq->findById($faqId);
            
            return $this->_sendResponse(array(
                'success' => true,
                'message' => 'FAQ created successfully',
                'data' => $faq
            ), 201);
        } else {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Failed to create FAQ',
                'errors' => $this->Faq->validationErrors
            ), 422);
        }
    }
    
    /**
     * API: Update FAQ (Super Admin Only)
     * PUT /api/faqs/:id
     */
    public function api_edit($id = null) {
        $this->autoRender = false;
        
        // Check permission
        $permissionError = $this->_checkSuperUserPermission();
        if ($permissionError) return $permissionError;
        
        if (!$this->request->is(array('put', 'post'))) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only PUT/POST method allowed'
            ), 405);
        }
        
        if (!$id) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ ID required'
            ), 400);
        }
        
        $faq = $this->Faq->findById($id);
        if (!$faq) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ not found'
            ), 404);
        }
        
        $data = $this->_getRequestData();
        
        $this->Faq->id = $id;
        if ($this->Faq->save(array('Faq' => $data))) {
            $updatedFaq = $this->Faq->findById($id);
            
            return $this->_sendResponse(array(
                'success' => true,
                'message' => 'FAQ updated successfully',
                'data' => $updatedFaq
            ), 200);
        } else {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Failed to update FAQ',
                'errors' => $this->Faq->validationErrors
            ), 422);
        }
    }
    
    /**
     * API: Delete FAQ (Super Admin Only)
     * DELETE /api/faqs/:id
     */
    public function api_delete($id = null) {
        $this->autoRender = false;
        
        // Check permission
        $permissionError = $this->_checkSuperUserPermission();
        if ($permissionError) return $permissionError;
        
        if (!$this->request->is(array('delete', 'post'))) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only DELETE method allowed'
            ), 405);
        }
        
        if (!$id) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ ID required'
            ), 400);
        }
        
        $faq = $this->Faq->findById($id);
        if (!$faq) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'FAQ not found'
            ), 404);
        }
        
        if ($this->Faq->delete($id)) {
            return $this->_sendResponse(array(
                'success' => true,
                'message' => 'FAQ deleted successfully'
            ), 200);
        } else {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Failed to delete FAQ'
            ), 500);
        }
    }
    
    /**
     * API: Get all FAQ categories (Public)
     * GET /api/faqs/categories
     */
    public function api_categories() {
        $this->autoRender = false;
        
        if (!$this->request->is('get')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only GET method allowed'
            ), 405);
        }
        
        $categories = $this->Faq->find('all', array(
            'fields' => array('DISTINCT Faq.category'),
            'conditions' => array('Faq.status' => 'active'),
            'order' => array('Faq.category' => 'ASC')
        ));
        
        $categoryList = array();
        foreach ($categories as $cat) {
            $categoryList[] = $cat['Faq']['category'];
        }
        
        return $this->_sendResponse(array(
            'success' => true,
            'count' => count($categoryList),
            'data' => $categoryList
        ), 200);
    }
    
    /**
     * API: Search FAQs (Public)
     * GET /api/faqs/search?q=keyword
     */
    public function api_search() {
        $this->autoRender = false;
        
        if (!$this->request->is('get')) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Only GET method allowed'
            ), 405);
        }
        
        $keyword = $this->request->query('q');
        if (empty($keyword)) {
            return $this->_sendResponse(array(
                'success' => false,
                'message' => 'Search keyword required (parameter: q)'
            ), 400);
        }
        
        $results = $this->Faq->search($keyword);
        
        return $this->_sendResponse(array(
            'success' => true,
            'keyword' => $keyword,
            'count' => count($results),
            'data' => $results
        ), 200);
    }
}
