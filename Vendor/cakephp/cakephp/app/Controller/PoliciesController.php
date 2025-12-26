<?php
App::uses('AppController', 'Controller');

class PoliciesController extends AppController {
    
    public $name = 'Policies';
    public $uses = array('Policy');
    public $components = array('RequestHandler');
    
    public function beforeFilter() {
        parent::beforeFilter();
        
        $this->Auth->allow('index', 'view', 'add', 'edit', 'delete', 'toggle_status', 'getCategories');
        
        // Only disable CSRF and auto-render for API requests
        if ($this->_isApiRequest()) {
            if (isset($this->Security)) {
                $this->Security->csrfCheck = false;
                $this->Security->validatePost = false;
            }
        }
    }
    
    /**
     * Check if this is an API request
     */
    private function _isApiRequest() {
        // Treat as API request only when the URL starts with `api/`, the
        // request is an AJAX call, or the URL explicitly has a `.json`
        // extension. Avoid relying solely on Accept headers (which some
        // browsers/extensions set) to prevent returning raw JSON for
        // normal browser navigations.
        $hasJsonExt = (isset($this->request->params['_ext']) && $this->request->params['_ext'] === 'json');
        return (
            strpos($this->request->url, 'api/') === 0 ||
            $this->request->is('ajax') ||
            $hasJsonExt
        );
    }
    
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false;
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }
    
    private function _getRequestData() {
        $data = $this->request->input('json_decode', true);
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }
    
    /**
     * Get categories for dropdown
     */
    private function _getCategories() {
        // Try to load Category model if it exists
        App::uses('Category', 'Model');
        
        if (class_exists('Category')) {
            try {
                $Category = new Category();
                $categories = $Category->find('all', array(
                    'fields' => array('id', 'name', 'active'),
                    'conditions' => array('active' => 1),
                    'order' => array('name' => 'ASC')
                ));

                $categoryList = array();
                foreach ($categories as $cat) {
                    $categoryList[$cat['Category']['id']] = $cat['Category']['name'];
                }
                return $categoryList;
            } catch (Exception $e) {
                // Table may be missing; fall back to distinct values from policies.categories
            }
        }

        // Fallback: derive distinct category names from policies table (useful before migration)
        try {
            $results = $this->Policy->find('all', array(
                'fields' => array('DISTINCT Policy.categories'),
                'conditions' => array('Policy.categories !=' => '', 'Policy.categories !=' => null)
            ));
            $list = array();
            foreach ($results as $r) {
                $name = $r['Policy']['categories'];
                if ($name) {
                    $list[$name] = $name;
                }
            }
            return $list;
        } catch (Exception $e) {
            // Last resort: return sample list
            return array(
                1 => 'Health Insurance',
                2 => 'Life Insurance',
                3 => 'Motor Insurance',
                4 => 'Travel Insurance',
                5 => 'Home Insurance'
            );
        }
    }
    
    /**
     * API endpoint to get categories
     */
    public function getCategories() {
        $categories = $this->_getCategories();
        
        if ($this->_isApiRequest()) {
            return $this->_sendResponse(array(
                'success' => true,
                'data' => $categories
            ), 200);
        }
        
        $this->set('categories', $categories);
    }
    
    /**
     * Index - List all policies
     * WEB: /policies (renders index.ctp)
     * API: /api/policies (returns JSON)
     */
    public function index() {
        try {
            $conditions = array();
            
            // Search filter
            if (!empty($this->request->query['search'])) {
                $search = $this->request->query['search'];
                $conditions['OR'] = array(
                    'Policy.title LIKE' => '%' . $search . '%',
                    'Policy.description LIKE' => '%' . $search . '%'
                );
            }
            
            // Status filter
            if (!empty($this->request->query['status'])) {
                $conditions['Policy.status'] = $this->request->query['status'];
            }
            
            // Ensure associated Category data is fetched (belongsTo)
            $policies = $this->Policy->find('all', array(
                'conditions' => $conditions,
                'order' => array('Policy.created' => 'DESC'),
                'recursive' => 0
            ));
            
            // Statistics
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
            
            // API Request - Return JSON
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => true,
                    'data' => $policies,
                    'stats' => $stats,
                    'count' => count($policies)
                ), 200);
            }
            
            // WEB Request - Render view
            // Provide category list for the view (used to map ids to names)
            $categories = $this->_getCategories();
            $this->set('categories', $categories);
            $this->set('policies', $policies);
            $this->set('stats', $stats);
            $this->set('title_for_layout', 'All Policies');
            
        } catch (Exception $e) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ), 500);
            }
            
            $this->Session->setFlash('Error loading policies', 'default', array('class' => 'error'));
            $this->set('policies', array());
            $this->set('stats', array('total' => 0, 'active' => 0, 'draft' => 0, 'archived' => 0));
        }
    }
    
    /**
     * View single policy
     */
    public function view($id = null) {
        if (!$id) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Policy ID required'
                ), 400);
            }
            throw new NotFoundException('Invalid policy');
        }
        
        $policy = $this->Policy->findById($id);
        
        if (!$policy) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Policy not found'
                ), 404);
            }
            throw new NotFoundException('Policy not found');
        }
        
        // API Request
        if ($this->_isApiRequest()) {
            return $this->_sendResponse(array(
                'success' => true,
                'data' => $policy
            ), 200);
        }
        
        // WEB Request
        $this->set('policy', $policy);
        $this->set('title_for_layout', 'View Policy');
    }
    
    /**
     * Add new policy
     */
    public function add() {
        if ($this->request->is('post')) {
            $data = $this->_getRequestData();
            
            $this->Policy->create();
            
            if ($this->Policy->save($data)) {
                $id = $this->Policy->getLastInsertID();
                $policy = $this->Policy->findById($id);
                
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => true,
                        'message' => 'Policy created successfully',
                        'data' => $policy
                    ), 201);
                } else {
                    $this->Session->setFlash('Policy created successfully', 'default', array('class' => 'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            } else {
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => false,
                        'message' => 'Failed to create policy',
                        'errors' => $this->Policy->validationErrors
                    ), 422);
                } else {
                    $this->Session->setFlash('Failed to create policy', 'default', array('class' => 'error'));
                }
            }
        }
        
        // WEB Request - show form
        if (!$this->_isApiRequest()) {
            $categories = $this->_getCategories();
            $this->set('categories', $categories);
            $this->set('title_for_layout', 'Add Policy');
        }
    }
    
    /**
     * Edit policy
     */
    public function edit($id = null) {
        if (!$id) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'ID required'), 400);
            }
            throw new NotFoundException('Invalid policy');
        }
        
        $policy = $this->Policy->findById($id);
        
        if (!$policy) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'Not found'), 404);
            }
            throw new NotFoundException('Policy not found');
        }
        
        if ($this->request->is(array('put', 'post'))) {
            $data = $this->_getRequestData();
            $this->Policy->id = $id;
            
            if ($this->Policy->save($data)) {
                $updated = $this->Policy->findById($id);
                
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => true,
                        'message' => 'Policy updated',
                        'data' => $updated
                    ), 200);
                } else {
                    $this->Session->setFlash('Policy updated', 'default', array('class' => 'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            } else {
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => false,
                        'errors' => $this->Policy->validationErrors
                    ), 422);
                }
            }
        }
        
        if (!$this->request->data) {
            $this->request->data = $policy;
        }
        
        $categories = $this->_getCategories();
        $this->set('categories', $categories);
        $this->set('policy', $policy);
        $this->set('title_for_layout', 'Edit Policy');
    }
    
    /**
     * Delete policy
     */
    public function delete($id = null) {
        if (!$id) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'ID required'), 400);
            }
            throw new NotFoundException('Invalid policy');
        }
        
        if ($this->Policy->delete($id)) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => true,
                    'message' => 'Policy deleted'
                ), 200);
            } else {
                $this->Session->setFlash('Policy deleted', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        } else {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'Delete failed'), 500);
            }
        }
    }
    
    /**
     * Toggle status
     */
    public function toggle_status($id = null) {
        // Accept ID from URL or AJAX/POST body
        $data = $this->_getRequestData();
        if (!$id && !empty($data['id'])) {
            $id = $data['id'];
        }

        if (!$id) {
            if ($this->request->is('ajax') || $this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'ID required'), 400);
            }
            throw new NotFoundException('Invalid policy');
        }

        $policy = $this->Policy->findById($id);
        
        if (!$policy) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'Not found'), 404);
            }
            throw new NotFoundException('Policy not found');
        }
        
        // Toggle between 'active' and 'draft' (draft = inactive/disabled)
        $current = isset($policy['Policy']['status']) ? $policy['Policy']['status'] : '';
        $newStatus = ($current === 'active') ? 'draft' : 'active';
        $this->Policy->id = $id;

        if ($this->Policy->saveField('status', $newStatus)) {
            $updated = $this->Policy->findById($id);

            if ($this->request->is('ajax') || $this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => true,
                    'message' => 'Status updated to ' . $newStatus,
                    'data' => $updated
                ), 200);
            } else {
                $this->Session->setFlash('Status updated', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }

        if ($this->request->is('ajax') || $this->_isApiRequest()) {
            return $this->_sendResponse(array('success' => false, 'message' => 'Update failed'), 500);
        }
    }
}
