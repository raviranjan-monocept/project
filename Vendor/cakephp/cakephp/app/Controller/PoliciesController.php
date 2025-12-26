<?php
/**
 * Policies Controller - Insurance Policy Management
 * 
 * This controller handles all policy-related operations including:
 * - CRUD operations for insurance policies (Create, Read, Update, Delete)
 * - Policy status management (active, draft, archived)
 * - Category management and filtering
 * - Dual interface support: Web views and REST API endpoints
 * - Search and filter functionality
 * - Policy statistics tracking
 * 
 * Supports both traditional web interface and modern REST API access
 */
App::uses('AppController', 'Controller');

class PoliciesController extends AppController {
    
    // Controller name for CakePHP routing
    public $name = 'Policies';
    
    // Models used by this controller
    public $uses = array('Policy');
    
    // Enable RequestHandler for automatic JSON/XML handling
    public $components = array('RequestHandler');
    
    /**
     * Runs before every action in this controller
     * Configures authentication and security settings
     */
    public function beforeFilter() {
        parent::beforeFilter();
        
        // Allow public access to all policy actions (adjust based on your requirements)
        // In production, you may want to restrict add/edit/delete to authenticated users
        $this->Auth->allow('index', 'view', 'add', 'edit', 'delete', 'toggle_status', 'getCategories');
        
        // Disable CSRF protection only for API requests
        // Web forms still maintain CSRF security
        if ($this->_isApiRequest()) {
            if (isset($this->Security)) {
                $this->Security->csrfCheck = false;
                $this->Security->validatePost = false;
            }
        }
    }
    
    /**
     * Determine if the current request is an API call
     * 
     * Checks multiple indicators to distinguish between browser navigation
     * and API requests to return appropriate response formats
     * 
     * @return boolean True if API request, false for web request
     */
    private function _isApiRequest() {
        // Check for .json extension in URL (e.g., /policies/index.json)
        $hasJsonExt = (isset($this->request->params['_ext']) && $this->request->params['_ext'] === 'json');
        
        // Return true if any of these conditions are met:
        // 1. URL starts with 'api/' (e.g., /api/policies)
        // 2. Request is made via AJAX (XMLHttpRequest)
        // 3. URL has .json extension
        return (
            strpos($this->request->url, 'api/') === 0 ||
            $this->request->is('ajax') ||
            $hasJsonExt
        );
    }
    
    /**
     * Helper function to send JSON responses for API endpoints
     * Sets proper HTTP status codes and content type
     * 
     * @param array $data Response data to encode as JSON
     * @param int $statusCode HTTP status code (200, 404, 500, etc.)
     * @return CakeResponse Response object with JSON body
     */
    private function _sendResponse($data, $statusCode = 200) {
        $this->autoRender = false; // Prevent view rendering
        $this->response->statusCode($statusCode);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }
    
    /**
     * Extract request data from either JSON body or form POST
     * Supports both Content-Type: application/json and form-encoded data
     * 
     * @return array Decoded request data
     */
    private function _getRequestData() {
        // Try to decode JSON from request body first
        $data = $this->request->input('json_decode', true);
        
        // Fall back to traditional form data if no JSON
        if (empty($data)) {
            $data = $this->request->data;
        }
        return $data;
    }
    
    /**
     * Get available policy categories for dropdown/selection
     * 
     * Tries multiple sources in order of preference:
     * 1. Category model/table (if exists)
     * 2. Distinct categories from existing policies
     * 3. Fallback to hardcoded sample categories
     * 
     * @return array Associative array of category_id => category_name
     */
    private function _getCategories() {
        // Attempt to load Category model if it exists
        App::uses('Category', 'Model');
        
        if (class_exists('Category')) {
            try {
                // Instantiate Category model
                $Category = new Category();
                
                // Fetch all active categories
                $categories = $Category->find('all', array(
                    'fields' => array('id', 'name', 'active'),
                    'conditions' => array('active' => 1), // Only active categories
                    'order' => array('name' => 'ASC') // Alphabetical order
                ));

                // Convert to key-value array for dropdown
                $categoryList = array();
                foreach ($categories as $cat) {
                    $categoryList[$cat['Category']['id']] = $cat['Category']['name'];
                }
                return $categoryList;
            } catch (Exception $e) {
                // Category table may not exist yet, continue to fallback
            }
        }

        // Fallback 1: Get distinct category names from policies table
        // Useful during development or migration phase
        try {
            $results = $this->Policy->find('all', array(
                'fields' => array('DISTINCT Policy.categories'),
                'conditions' => array(
                    'Policy.categories !=' => '',   // Not empty string
                    'Policy.categories !=' => null  // Not NULL
                )
            ));
            
            // Build list from existing policy categories
            $list = array();
            foreach ($results as $r) {
                $name = $r['Policy']['categories'];
                if ($name) {
                    $list[$name] = $name; // Use name as both key and value
                }
            }
            return $list;
        } catch (Exception $e) {
            // Policies table may not have categories column yet
        }
        
        // Fallback 2: Return sample/default categories
        // Used when no categories exist in database
        return array(
            1 => 'Health Insurance',
            2 => 'Life Insurance',
            3 => 'Motor Insurance',
            4 => 'Travel Insurance',
            5 => 'Home Insurance'
        );
    }
    
    /**
     * API endpoint to retrieve available categories
     * GET /api/policies/getCategories or /policies/getCategories.json
     * 
     * Useful for populating dropdown menus in frontend applications
     */
    public function getCategories() {
        // Get categories using helper method
        $categories = $this->_getCategories();
        
        // Return JSON response for API requests
        if ($this->_isApiRequest()) {
            return $this->_sendResponse(array(
                'success' => true,
                'data' => $categories
            ), 200);
        }
        
        // For web requests, pass to view
        $this->set('categories', $categories);
    }
    
    /**
     * List all policies with optional filtering
     * 
     * WEB: GET /policies (renders index.ctp view)
     * API: GET /api/policies (returns JSON)
     * 
     * Supports query parameters:
     * - search: Filter by title or description
     * - status: Filter by status (active, draft, archived)
     */
    public function index() {
        try {
            // Initialize search conditions
            $conditions = array();
            
            // Apply search filter if provided
            // Searches in both title and description fields
            if (!empty($this->request->query['search'])) {
                $search = $this->request->query['search'];
                $conditions['OR'] = array(
                    'Policy.title LIKE' => '%' . $search . '%',
                    'Policy.description LIKE' => '%' . $search . '%'
                );
            }
            
            // Apply status filter if provided
            if (!empty($this->request->query['status'])) {
                $conditions['Policy.status'] = $this->request->query['status'];
            }
            
            // Fetch all policies matching conditions
            // recursive = 0 includes belongsTo associations (Category)
            $policies = $this->Policy->find('all', array(
                'conditions' => $conditions,
                'order' => array('Policy.created' => 'DESC'), // Newest first
                'recursive' => 0 // Include associated Category data
            ));
            
            // Calculate statistics for dashboard display
            $stats = array(
                'total' => $this->Policy->find('count'), // All policies
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
            
            // API Request - Return JSON response
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => true,
                    'data' => $policies,
                    'stats' => $stats,
                    'count' => count($policies) // Count of filtered results
                ), 200);
            }
            
            // WEB Request - Render HTML view
            // Load categories for dropdown/filtering in view
            $categories = $this->_getCategories();
            $this->set('categories', $categories);
            $this->set('policies', $policies);
            $this->set('stats', $stats);
            $this->set('title_for_layout', 'All Policies');
            
        } catch (Exception $e) {
            // Handle any database or system errors gracefully
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ), 500); // Internal Server Error
            }
            
            // For web requests, show flash message and empty data
            $this->Session->setFlash('Error loading policies', 'default', array('class' => 'error'));
            $this->set('policies', array());
            $this->set('stats', array('total' => 0, 'active' => 0, 'draft' => 0, 'archived' => 0));
        }
    }
    
    /**
     * View details of a single policy
     * 
     * WEB: GET /policies/view/{id}
     * API: GET /api/policies/view/{id}
     * 
     * @param int $id Policy ID to display
     */
    public function view($id = null) {
        // Validate that ID is provided
        if (!$id) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Policy ID required'
                ), 400); // Bad Request
            }
            throw new NotFoundException('Invalid policy');
        }
        
        // Fetch policy from database
        $policy = $this->Policy->findById($id);
        
        // Check if policy exists
        if (!$policy) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => false,
                    'message' => 'Policy not found'
                ), 404); // Not Found
            }
            throw new NotFoundException('Policy not found');
        }
        
        // API Request - Return JSON
        if ($this->_isApiRequest()) {
            return $this->_sendResponse(array(
                'success' => true,
                'data' => $policy
            ), 200);
        }
        
        // WEB Request - Render view template
        $this->set('policy', $policy);
        $this->set('title_for_layout', 'View Policy');
    }
    
    /**
     * Create new policy
     * 
     * WEB: GET /policies/add (shows form), POST /policies/add (processes form)
     * API: POST /api/policies/add (creates from JSON)
     */
    public function add() {
        // Handle form submission (POST request)
        if ($this->request->is('post')) {
            // Get data from JSON or form
            $data = $this->_getRequestData();
            
            // Create new policy record
            $this->Policy->create();
            
            // Attempt to save to database
            if ($this->Policy->save($data)) {
                // Save successful - get the newly created policy
                $id = $this->Policy->getLastInsertID();
                $policy = $this->Policy->findById($id);
                
                // API response
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => true,
                        'message' => 'Policy created successfully',
                        'data' => $policy
                    ), 201); // Created
                } else {
                    // Web response - redirect to list
                    $this->Session->setFlash('Policy created successfully', 'default', array('class' => 'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            } else {
                // Save failed - validation errors
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => false,
                        'message' => 'Failed to create policy',
                        'errors' => $this->Policy->validationErrors
                    ), 422); // Unprocessable Entity
                } else {
                    $this->Session->setFlash('Failed to create policy', 'default', array('class' => 'error'));
                }
            }
        }
        
        // GET request - show the add form
        if (!$this->_isApiRequest()) {
            // Load categories for dropdown
            $categories = $this->_getCategories();
            $this->set('categories', $categories);
            $this->set('title_for_layout', 'Add Policy');
        }
    }
    
    /**
     * Edit existing policy
     * 
     * WEB: GET /policies/edit/{id} (shows form), POST /policies/edit/{id} (saves changes)
     * API: PUT /api/policies/edit/{id} (updates from JSON)
     * 
     * @param int $id Policy ID to edit
     */
    public function edit($id = null) {
        // Validate ID is provided
        if (!$id) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'ID required'), 400);
            }
            throw new NotFoundException('Invalid policy');
        }
        
        // Fetch existing policy
        $policy = $this->Policy->findById($id);
        
        // Check if policy exists
        if (!$policy) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'Not found'), 404);
            }
            throw new NotFoundException('Policy not found');
        }
        
        // Handle update submission (POST or PUT request)
        if ($this->request->is(array('put', 'post'))) {
            // Get updated data
            $data = $this->_getRequestData();
            
            // Set the ID for update operation
            $this->Policy->id = $id;
            
            // Attempt to save changes
            if ($this->Policy->save($data)) {
                // Update successful - fetch updated data
                $updated = $this->Policy->findById($id);
                
                // API response
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => true,
                        'message' => 'Policy updated',
                        'data' => $updated
                    ), 200);
                } else {
                    // Web response - redirect to list
                    $this->Session->setFlash('Policy updated', 'default', array('class' => 'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            } else {
                // Update failed - validation errors
                if ($this->_isApiRequest()) {
                    return $this->_sendResponse(array(
                        'success' => false,
                        'errors' => $this->Policy->validationErrors
                    ), 422);
                }
            }
        }
        
        // GET request - populate form with existing data
        if (!$this->request->data) {
            $this->request->data = $policy;
        }
        
        // Load categories for dropdown
        $categories = $this->_getCategories();
        $this->set('categories', $categories);
        $this->set('policy', $policy);
        $this->set('title_for_layout', 'Edit Policy');
    }
    
    /**
     * Delete a policy
     * 
     * WEB: POST /policies/delete/{id}
     * API: DELETE /api/policies/delete/{id}
     * 
     * @param int $id Policy ID to delete
     */
    public function delete($id = null) {
        // Validate ID is provided
        if (!$id) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'ID required'), 400);
            }
            throw new NotFoundException('Invalid policy');
        }
        
        // Attempt to delete the policy
        if ($this->Policy->delete($id)) {
            // Deletion successful
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => true,
                    'message' => 'Policy deleted'
                ), 200);
            } else {
                // Web response - redirect to list
                $this->Session->setFlash('Policy deleted', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        } else {
            // Deletion failed
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'Delete failed'), 500);
            }
        }
    }
    
    /**
     * Toggle policy status between active and draft
     * 
     * Used for quick enable/disable functionality
     * WEB: POST /policies/toggle_status/{id}
     * API: POST /api/policies/toggle_status with {id: xxx}
     * AJAX: POST /policies/toggle_status with {id: xxx}
     * 
     * @param int $id Policy ID (optional, can be in POST body)
     */
    public function toggle_status($id = null) {
        // Accept ID from URL parameter or POST/JSON body
        $data = $this->_getRequestData();
        if (!$id && !empty($data['id'])) {
            $id = $data['id'];
        }

        // Validate ID is provided
        if (!$id) {
            if ($this->request->is('ajax') || $this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'ID required'), 400);
            }
            throw new NotFoundException('Invalid policy');
        }

        // Fetch current policy data
        $policy = $this->Policy->findById($id);
        
        // Check if policy exists
        if (!$policy) {
            if ($this->_isApiRequest()) {
                return $this->_sendResponse(array('success' => false, 'message' => 'Not found'), 404);
            }
            throw new NotFoundException('Policy not found');
        }
        
        // Toggle status logic
        // active -> draft (disabled/inactive)
        // draft -> active (enabled)
        $current = isset($policy['Policy']['status']) ? $policy['Policy']['status'] : '';
        $newStatus = ($current === 'active') ? 'draft' : 'active';
        
        // Set ID and update only the status field
        $this->Policy->id = $id;

        // Save the new status
        if ($this->Policy->saveField('status', $newStatus)) {
            // Update successful - fetch updated policy
            $updated = $this->Policy->findById($id);

            // Return JSON for AJAX or API requests
            if ($this->request->is('ajax') || $this->_isApiRequest()) {
                return $this->_sendResponse(array(
                    'success' => true,
                    'message' => 'Status updated to ' . $newStatus,
                    'data' => $updated
                ), 200);
            } else {
                // Web response - redirect back to list
                $this->Session->setFlash('Status updated', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }

        // Update failed
        if ($this->request->is('ajax') || $this->_isApiRequest()) {
            return $this->_sendResponse(array('success' => false, 'message' => 'Update failed'), 500);
        }
    }
}
