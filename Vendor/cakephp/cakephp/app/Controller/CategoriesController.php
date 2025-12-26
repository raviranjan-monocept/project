<?php
App::uses('AppController', 'Controller');

class CategoriesController extends AppController {
    public $name = 'Categories';
    public $uses = array('Category');
    public $components = array('Session', 'RequestHandler');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('index', 'add', 'edit', 'delete', 'toggle_status');
    }

    // helper to send JSON response
    private function _sendJsonResponse($data, $status = 200) {
        $this->autoRender = false;
        $this->response->statusCode($status);
        $this->response->type('application/json');
        $this->response->body(json_encode($data));
        return $this->response;
    }

    // List categories and render index view
    public function index() {
        $categories = array();
        try {
            $categories = $this->Category->find('all', array(
                'order' => array('Category.name' => 'ASC')
            ));
        } catch (Exception $e) {
            // If table missing or other DB error, return empty list for web and API
            $categories = array();
        }

        if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
            return $this->_sendJsonResponse(array('success' => true, 'data' => $categories));
        }

        $this->set('categories', $categories);
        $this->set('title_for_layout', 'All Categories');
    }

    // Add category (handles POST)
    public function add() {
        if ($this->request->is('post')) {
            // If request body is JSON (AJAX fetch with application/json), populate request->data
            if (empty($this->request->data)) {
                $json = $this->request->input('json_decode', true);
                if ($json) {
                    $this->request->data = $json;
                }
            }

            $this->Category->create();
            if ($this->Category->save($this->request->data)) {
                $category = $this->Category->findById($this->Category->id);
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => true, 'data' => $category), 201);
                }
                $this->Session->setFlash('Category created successfully', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $errors = $this->Category->validationErrors;
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => false, 'errors' => $errors), 422);
                }
                $this->Session->setFlash('Failed to create category', 'default', array('class' => 'error'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    // Edit category
    public function edit($id = null) {
        if (!$id) {
            throw new NotFoundException('Invalid category');
        }

        $category = $this->Category->findById($id);
        if (!$category) {
            throw new NotFoundException('Category not found');
        }

        if ($this->request->is(array('post', 'put'))) {
            $this->Category->id = $id;
            if ($this->Category->save($this->request->data)) {
                $category = $this->Category->findById($id);
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => true, 'data' => $category), 200);
                }
                $this->Session->setFlash('Category updated', 'default', array('class' => 'success'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $errors = $this->Category->validationErrors;
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => false, 'errors' => $errors), 422);
                }
                $this->Session->setFlash('Failed to update category', 'default', array('class' => 'error'));
            }
        }

        if (!$this->request->data) {
            $this->request->data = $category;
        }

        return $this->redirect(array('action' => 'index'));
    }

    // Delete category
    public function delete($id = null) {
        if (!$id) {
            throw new NotFoundException('Invalid category');
        }

        if ($this->request->is('post')) {
            if ($this->Category->delete($id)) {
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => true), 200);
                }
                $this->Session->setFlash('Category deleted', 'default', array('class' => 'success'));
            } else {
                if ($this->RequestHandler->prefers('json') || isset($this->request->params['ext']) && $this->request->params['ext'] === 'json') {
                    return $this->_sendJsonResponse(array('success' => false), 500);
                }
                $this->Session->setFlash('Delete failed', 'default', array('class' => 'error'));
            }
        }

        return $this->redirect(array('action' => 'index'));
    }

    // Toggle active status via AJAX/POST. Returns JSON {success: true, active: 0|1}
    public function toggle_status($id = null) {
        $this->autoRender = false;
        $this->response->type('application/json');

        // Accept POST or JSON body
        if ($this->request->is('post')) {
            if (empty($this->request->data)) {
                $json = $this->request->input('json_decode', true);
                if ($json) {
                    $this->request->data = $json;
                }
            }
        }

        $reqId = null;
        if ($id) $reqId = $id;
        elseif (!empty($this->request->data['id'])) $reqId = $this->request->data['id'];

        if (!$reqId) {
            echo json_encode(array('success' => false, 'message' => 'Invalid id'));
            return;
        }

        $category = $this->Category->findById($reqId);
        if (!$category) {
            echo json_encode(array('success' => false, 'message' => 'Category not found'));
            return;
        }

        $current = isset($category['Category']['active']) ? (int)$category['Category']['active'] : 0;
        $new = $current ? 0 : 1;

        $this->Category->id = $reqId;
        if ($this->Category->saveField('active', $new)) {
            echo json_encode(array('success' => true, 'active' => $new));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Save failed'));
        }
    }
}
