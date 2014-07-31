<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {
  public $components = array('Paginator');
  
  public function beforeFilter() {
    parent::beforeFilter();
    if ($this->Auth->user('id') != 1 && !in_array($this->request->action, array('login', 'logout'))) {
      $this->redirect(array('controller' => 'editions'));
      exit();
    }
  }

  public function login() {
    if ($this->request->is('post')) {
      if ($this->Auth->login()) {
        return $this->redirect($this->Auth->redirectUrl());
      } else {
        $this->Session->setFlash('<span class="fa fa-minus-circle"></span>'.__('Username or password is incorrect'), 'custom_auth', array(), 'auth');
      }
    }
  }

  public function logout() {
    $this->redirect($this->Auth->logout());
  }

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->User->recursive = 0;
	  if (isset($this->data['User']['search']) && !empty($this->data['User']['search'])) {
  	  $query = $this->data['User']['search']; 
      $this->Paginator->settings = array(
        'conditions' => array('OR' => array(
          'User.username LIKE' => '%'.$query.'%'
        ))
      );
	  }
    $users = $this->Paginator->paginate('User');
    $this->set(compact('users'));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->User->create();
			$this->request->data['User']['timestamp'] = date('Y-m-d H:i:s');
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash('<span class="fa fa-check-circle"></span>'.__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('<span class="fa fa-minus-circle"></span>'.__('The user could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		  if ($id == 1) {
		    unset($this->request->data['User']['password']);
		  }
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash('<span class="fa fa-check-circle"></span>'.__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash('<span class="fa fa-minus-circle"></span>'.__('The user could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($id != 1) {
  		if ($this->User->delete()) {
  			$this->Session->setFlash('<span class="fa fa-times-circle"></span>'.__('User deleted'));
  			$this->redirect(array('action' => 'index'));
  		}  		
		}
		$this->Session->setFlash('<span class="fa fa-minus-circle"></span>'.__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
