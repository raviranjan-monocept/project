<?php
/**
 * Categories Controller - Category Management System
 * 
 * This controller manages insurance policy categories including:
 * - CRUD operations (Create, Read, Update, Delete)
 * - Active/Inactive status toggling
 * - Dual interface support: Web views and REST API/AJAX endpoints
 * - Category listing with alphabetical ordering
 * 
 * Categories are used to organize and classify insurance policies
 * (e.g., Health Insurance, Life Insurance, Motor Insurance, etc.)
 */
App::uses('AppController', 'Controller');

class CategoriesController extends AppController {
    
    // Controller name for CakePHP routing
    public $name = 'Categories';
    
    // Models used by this controller
    public $uses = array('Category');
    
    // Enable Session and RequestHandler components
    // Session: For flash messages in web interface
    // RequestHandler: For automatic JSON/XML handling
    public $components = array('Session', 'RequestHandler');

    /**
     * Runs before every action in this controller
     * Configures authentication and access permissions
     */
    public function beforeFilter() {
        parent::beforeFilter();
        
        // Allow public access to all category actions
        // In production, you may want to restrict these to admin users only
        $this->Auth->allow('index', 'add', 'edit', 'delete', 'toggle_status');
    }

    /**
     * Helper function to send JSON responses for API/AJAX endpoints
     * Sets proper HTTP status codes and content type
     * 
     * @param array $data Response data to encode as JSON
     * @param int $status HTTP status code (200, 201, 404, 500, etc.)
     * @return CakeResponse Response object with JSON body
     */
    private function _sendJsonResponse($data, $status = 200) {
        $this->autoRender = false; // Prevent view rendering
        $this->response->statusCode($status);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }

    /**
     * List all categories with alphabetical ordering
     * 
     * WEB: GET /categories (renders index.ctp view)
     * API: GET /categories.json (returns JSON)
     * 
     * Displays all categories in alphabetical order by name
     * Handles database errors gracefully by returning empty list
     */
    public function index() {
        $categories = array();
        
        try {
            // Fetch all categories ordered alphabetically by name
            $categories = $this->Category->find('all', array(
                'order' => array('Category.name' => 'ASC')
            ));
        } catch (Exception $e) {
            // If categories table doesn't exist or database error occurs,
            // return empty list instead of crashing
            // This allows the system to work even before migration is run
            $categories = array();
        }

        // Check if this is a JSON API request
        // Checks both Accept header preference and .json URL extension
        if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
            return $this->_sendJsonResponse(array('success' => true, 'data' => $categories));
        }

        // WEB Request - Pass data to view template
        $this->set('categories', $categories);
        $this->set('title_for_layout', 'All Categories');
    }

    /**
     * Create new category
     * 
     * WEB: POST /categories/add (from HTML form)
     * API: POST /categories/add.json (from JSON body)
     * AJAX: POST /categories/add (from fetch/axios with JSON)
     * 
     * Validates and saves new category to database
     * After successful creation, redirects to index (web) or returns JSON (API)
     */
    public function add() {
        // Only process POST requests (form submissions)
        if ($this->request->is('post')) {
            // Handle JSON request body for AJAX/API calls
            // If request->data is empty, try to parse JSON from raw input
            if (empty($this->request->data)) {
                $json = $this->request->input('json_decode', true);
                if ($json) {
                    $this->request->data = $json;
                }
            }

            // Create new category record
            $this->Category->create();
            
            // Attempt to save to database
            if ($this->Category->save($this->request->data)) {
                // Save successful - fetch the newly created category with ID
                $category = $this->Category->findById($this->Category->id);
                
                // Check if JSON response is expected
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => true, 'data' => $category), 201); // 201 Created
                }
                
                // Web response - flash message and redirect
                $this->Session->setFlash('Category created successfully', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            } else {
                // Save failed - validation errors occurred
                $errors = $this->Category->validationErrors;
                
                // Return validation errors as JSON for API
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => false, 'errors' => $errors), 422); // 422 Unprocessable Entity
                }
                
                // Web response - error flash message
                $this->Session->setFlash('Failed to create category', 'default', array('class' => 'error'));
            }
        }
        
        // Redirect back to index after processing
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Edit existing category
     * 
     * WEB: GET /categories/edit/{id} (shows form), POST /categories/edit/{id} (saves changes)
     * API: PUT /categories/edit/{id}.json (updates from JSON)
     * 
     * @param int $id Category ID to edit
     * 
     * Updates category name and/or active status
     * Validates ID and checks if category exists before updating
     */
    public function edit($id = null) {
        // Validate that ID is provided
        if (!$id) {
            throw new NotFoundException('Invalid category');
        }

        // Fetch existing category from database
        $category = $this->Category->findById($id);
        
        // Check if category exists
        if (!$category) {
            throw new NotFoundException('Category not found');
        }

        // Handle update submission (POST or PUT request)
        if ($this->request->is(array('post', 'put'))) {
            // Set the category ID for update operation
            $this->Category->id = $id;
            
            // Attempt to save changes
            if ($this->Category->save($this->request->data)) {
                // Update successful - fetch updated category
                $category = $this->Category->findById($id);
                
                // Return JSON for API requests
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => true, 'data' => $category), 200);
                }
                
                // Web response - success message and redirect
                $this->Session->setFlash('Category updated', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            } else {
                // Update failed - validation errors
                $errors = $this->Category->validationErrors;
                
                // Return errors as JSON for API
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => false, 'errors' => $errors), 422);
                }
                
                // Web response - error message
                $this->Session->setFlash('Failed to update category', 'default', array('class' => 'error'));
            }
        }

        // GET request - populate form with existing data
        if (!$this->request->data) {
            $this->request->data = $category;
        }

        // Redirect back to index
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Delete a category
     * 
     * WEB: POST /categories/delete/{id}
     * API: DELETE /categories/delete/{id}.json
     * 
     * @param int $id Category ID to delete
     * 
     * Warning: Deleting a category may affect policies that reference it
     * Consider implementing soft-delete or checking for related policies first
     */
    public function delete($id = null) {
        // Validate that ID is provided
        if (!$id) {
            throw new NotFoundException('Invalid category');
        }

        // Only process POST requests (to prevent accidental deletion via GET)
        if ($this->request->is('post')) {
            // Attempt to delete the category
            if ($this->Category->delete($id)) {
                // Deletion successful
                
                // Return JSON success for API
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => true), 200);
                }
                
                // Web response - success message
                $this->Session->setFlash('Category deleted', 'default', array('class' => 'success'));
            } else {
                // Deletion failed (category may not exist or database error)
                
                // Return JSON error for API
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => false), 500); // 500 Internal Server Error
                }
                
                // Web response - error message
                $this->Session->setFlash('Delete failed', 'default', array('class' => 'error'));
            }
        }

        // Redirect back to index
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Toggle category active status (enable/disable)
     * 
     * AJAX: POST /categories/toggle_status/{id}
     * API: POST /categories/toggle_status with {id: xxx} in body
     * 
     * @param int $id Category ID (optional, can be in POST body)
     * 
     * Returns JSON response: {success: true, active: 0|1}
     * 
     * This is typically called via AJAX for quick enable/disable functionality
     * without page reload. Active categories are shown in dropdowns,
     * while inactive categories are hidden but not deleted.
     */
    public function toggle_status($id = null) {
        // Disable automatic view rendering - we'll output JSON manually
        $this->autoRender = false;
        $this->response->type('application/json');

        // Handle JSON request body for AJAX calls
        if ($this->request->is('post')) {
            // If request->data is empty, try to parse JSON from raw input
            if (empty($this->request->data)) {
                $json = $this->request->input('json_decode', true);
                if ($json) {
                    $this->request->data = $json;
                }
            }
        }

        // Get category ID from URL parameter or POST body
        $reqId = null;
        if ($id) {
            $reqId = $id; // From URL: /toggle_status/5
        } elseif (!empty($this->request->data['id'])) {
            $reqId = $this->request->data['id']; // From POST body: {id: 5}
        }

        // Validate that ID was provided
        if (!$reqId) {
            echo json_encode(array('success' => false, 'message' => 'Invalid id'));
            return;
        }

        // Fetch the category from database
        $category = $this->Category->findById($reqId);
        
        // Check if category exists
        if (!$category) {
            echo json_encode(array('success' => false, 'message' => 'Category not found'));
            return;
        }

        // Get current active status (default to 0 if not set)
        $current = isset($category['Category']['active']) ? (int)$category['Category']['active'] : 0;
        
        // Toggle the status
        // If currently active (1), make inactive (0)
        // If currently inactive (0), make active (1)
        $new = $current ? 0 : 1;

        // Update only the 'active' field in database
        $this->Category->id = $reqId;
        if ($this->Category->saveField('active', $new)) {
            // Update successful - return new status
            echo json_encode(array('success' => true, 'active' => $new));
        } else {
            // Update failed
            echo json_encode(array('success' => false, 'message' => 'Save failed'));
        }
    }
}
