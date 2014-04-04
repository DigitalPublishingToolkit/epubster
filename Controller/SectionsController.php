<?php
App::uses('AppController', 'Controller');
/**
 * Pages Controller
 *
 * @property section $Page
 */
class SectionsController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Section->recursive = 0;
		$this->set('sections', $this->paginate());
	}
	
	public function create_section() {
	  $this->autoRender = false;
	  $this->autoLayout = false;
	  $id = $this->request->params['named']['edition'];
	  $count = $this->request->params['named']['count'];
	  if (!empty($id) && !empty($count)) {
	     $section = array(
    		'title' => __('New Tab').' '.$count,
    		'text' => ''
      );
  		$this->set('section', $section);
  		$this->set('count', $count);
  	  $this->render('/Elements/section-form');
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
		$this->Section->id = $id;
		if (!$this->Section->exists()) {
			throw new NotFoundException(__('Invalid section'));
		}
		$this->request->onlyAllow('post', 'delete');
		$edition = $this->Section->find('first', array(
  		'contain' => array('Edition'),
  		'fields' => 'id',
  		'recursive' => -1,
  		'conditions' => array('Section.id' => $id),
		));
		$editionId = $edition['Edition'][0]['id'];
		if ($this->Section->delete()) {
			$this->Session->setFlash(__('Section deleted'));
			$this->redirect(array('controller' => 'editions', 'action' => 'edit', $editionId));
		}
		$this->Session->setFlash(__('Section was not deleted'));
		$this->redirect(array('controller' => 'editions', 'action' => 'index'));
	}
}
