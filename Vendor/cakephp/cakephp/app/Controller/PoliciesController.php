<?php
/**
 * Policies Controller
 *
 * File: app/Controller/PoliciesController.php
 */
App::uses('AppController', 'Controller');

class PoliciesController extends AppController {
    
    public $name = 'Policies';
    public $uses = array('Policy');
    public $components = array('RequestHandler', 'Session');
    public $helpers = array('Html', 'Form', 'Session');
    
    /**
     * Before Filter
     */
    public function beforeFilter() {
        parent::beforeFilter();
        
        // Enable REST API
        if ($this->request->is('api')) {
            $this->Auth->allow();
            $this->Security->csrfCheck = false;
        }
        
        // Set pagination
        $this->paginate = array(
            'limit' => 10,
            'order' => array('Policy.created' => 'DESC')
        );
    }
    
    /**
     * Index - Display all policies
     * GET /policies or /api/policies
     */
    public function index() {
        $this->set('title_for_layout', 'All Policies');
        
        // API request → JSON list
        if ($this->request->is('api') || $this->RequestHandler->prefers('json')) {
            return $this->_restIndex();
        }
        
        // Web request
        $conditions = array();
        
        // Search filter
        if (!empty($this->request->query['search'])) {
            $search = $this->request->query['search'];
            $conditions['OR'] = array(
                'Policy.title LIKE'       => '%' . $search . '%',
                'Policy.description LIKE' => '%' . $search . '%'
            );
        }
        
        // Status filter
        if (!empty($this->request->query['status'])) {
            $conditions['Policy.status'] = $this->request->query['status'];
        }
        
        try {
            $this->set('policies', $this->paginate('Policy', $conditions));
            
            // Stats (used by policies index page; dashboard gets its own stats)
            $stats = array(
                'total'    => $this->Policy->find('count'),
                'active'   => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'active')
                )),
                'draft'    => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'draft')
                )),
                'archived' => $this->Policy->find('count', array(
                    'conditions' => array('Policy.status' => 'archived')
                ))
            );
            $this->set('stats', $stats);
            
        } catch (Exception $e) {
            $this->Session->setFlash(
                'Error loading policies: ' . $e->getMessage(),
                'default',
                array(),
                'error'
            );
            $this->set('policies', array());
            $this->set('stats', array('total' => 0, 'active' => 0, 'draft' => 0, 'archived' => 0));
        }
    }
    
    /**
     * REST API Index
     */
    private function _restIndex() {
        $policies = $this->Policy->find('all', array(
            'order' => array('Policy.created' => 'DESC')
        ));
        
        $this->set(array(
            'success'   => true,
            'data'      => $policies,
            'count'     => count($policies),
            '_serialize'=> array('success', 'data', 'count')
        ));
    }
    
    /**
     * View single policy
     * GET /policies/view/:id or /api/policies/:id
     */
    public function view($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid policy'));
        }
        
        $policy = $this->Policy->findById($id);
        
        if (!$policy) {
            if ($this->request->is('api')) {
                $this->set(array(
                    'success' => false,
                    'error'   => 'Policy not found',
                    '_serialize' => array('success', 'error')
                ));
                $this->response->statusCode(404);
                return;
            }
            
            throw new NotFoundException(__('Policy not found'));
        }
        
        if ($this->request->is('api')) {
            $this->set(array(
                'success' => true,
                'data'    => $policy,
                '_serialize' => array('success', 'data')
            ));
        } else {
            $this->set('policy', $policy);
        }
    }
    
    /**
     * Add new policy
     * POST /policies/add (HTML + modal) or /api/policies (JSON)
     */
    public function add() {
        $this->set('title_for_layout', 'Add New Policy');
        
        if ($this->request->is('post')) {
            $this->Policy->create();
            
            // API → JSON body, Web → normal form
            $data = $this->request->is('api')
                ? $this->request->input('json_decode', true)
                : $this->request->data;
            
            if ($this->Policy->save($data)) {
                $id     = $this->Policy->getLastInsertID();
                $policy = $this->Policy->findById($id);
                
                if ($this->request->is('api')) {
                    $this->set(array(
                        'success' => true,
                        'message' => 'Policy created successfully',
                        'data'    => $policy,
                        '_serialize' => array('success', 'message', 'data')
                    ));
                    $this->response->statusCode(201);
                } else {
                    $this->Session->setFlash(
                        'Policy has been created successfully',
                        'default',
                        array('class' => 'success')
                    );
                    // Back to admin dashboard (modal use-case)
                    return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
                }
            } else {
                if ($this->request->is('api')) {
                    $this->set(array(
                        'success' => false,
                        'error'   => 'Failed to create policy',
                        'validationErrors' => $this->Policy->validationErrors,
                        '_serialize' => array('success', 'error', 'validationErrors')
                    ));
                    $this->response->statusCode(400);
                } else {
                    $this->Session->setFlash(
                        'Failed to create policy. Please try again.',
                        'default',
                        array('class' => 'error')
                    );
                }
            }
        }
    }
    
    /**
     * Edit policy
     * POST/PUT /policies/edit/:id (HTML + modal) or /api/policies/:id
     */
    public function edit($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid policy'));
        }
        
        $policy = $this->Policy->findById($id);
        
        if (!$policy) {
            if ($this->request->is('api')) {
                $this->set(array(
                    'success' => false,
                    'error'   => 'Policy not found',
                    '_serialize' => array('success', 'error')
                ));
                $this->response->statusCode(404);
                return;
            }
            
            throw new NotFoundException(__('Policy not found'));
        }
        
        $this->set('title_for_layout', 'Edit Policy');
        
        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->is('api')
                ? $this->request->input('json_decode', true)
                : $this->request->data;
            
            $this->Policy->id = $id;
            
            if ($this->Policy->save($data)) {
                $updated = $this->Policy->findById($id);
                
                if ($this->request->is('api')) {
                    $this->set(array(
                        'success' => true,
                        'message' => 'Policy updated successfully',
                        'data'    => $updated,
                        '_serialize' => array('success', 'message', 'data')
                    ));
                } else {
                    $this->Session->setFlash(
                        'Policy has been updated successfully',
                        'default',
                        array('class' => 'success')
                    );
                    return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
                }
            } else {
                if ($this->request->is('api')) {
                    $this->set(array(
                        'success' => false,
                        'error'   => 'Failed to update policy',
                        'validationErrors' => $this->Policy->validationErrors,
                        '_serialize' => array('success', 'error', 'validationErrors')
                    ));
                    $this->response->statusCode(400);
                } else {
                    $this->Session->setFlash(
                        'Failed to update policy. Please try again.',
                        'default',
                        array('class' => 'error')
                    );
                }
            }
        }
        
        if (!$this->request->data) {
            $this->request->data = $policy;
        }
        
        $this->set('policy', $policy);
    }
    
    /**
     * Delete policy
     * DELETE /policies/delete/:id or /api/policies/:id
     */
    public function delete($id = null) {
        $this->request->allowMethod(array('post', 'delete'));
        
        if (!$id) {
            if ($this->request->is('api')) {
                $this->set(array(
                    'success' => false,
                    'error'   => 'Invalid policy ID',
                    '_serialize' => array('success', 'error')
                ));
                $this->response->statusCode(400);
                return;
            }
            
            throw new NotFoundException(__('Invalid policy'));
        }
        
        if ($this->Policy->delete($id)) {
            if ($this->request->is('api')) {
                $this->set(array(
                    'success' => true,
                    'message' => 'Policy deleted successfully',
                    '_serialize' => array('success', 'message')
                ));
            } else {
                $this->Session->setFlash(
                    'Policy has been deleted successfully',
                    'default',
                    array('class' => 'success')
                );
            }
        } else {
            if ($this->request->is('api')) {
                $this->set(array(
                    'success' => false,
                    'error'   => 'Failed to delete policy',
                    '_serialize' => array('success', 'error')
                ));
                $this->response->statusCode(400);
            } else {
                $this->Session->setFlash(
                    'Failed to delete policy',
                    'default',
                    array('class' => 'error')
                );
            }
        }
        
        if (!$this->request->is('api')) {
            return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
        }
    }

    /**
     * Toggle active/inactive (status) – for dashboard button
     */
    public function toggle_status($id = null) {
        if (!$id) {
            throw new NotFoundException(__('Invalid policy'));
        }

        $policy = $this->Policy->findById($id);
        if (!$policy) {
            throw new NotFoundException(__('Policy not found'));
        }

        $newStatus = ($policy['Policy']['status'] === 'active') ? 'inactive' : 'active';

        $this->Policy->id = $id;
        if ($this->Policy->saveField('status', $newStatus)) {
            $this->Session->setFlash('Policy status updated.', 'default', array('class' => 'success'));
        } else {
            $this->Session->setFlash('Failed to update policy status.', 'default', array('class' => 'error'));
        }

        return $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
    }
}
