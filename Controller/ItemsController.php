<?php
App::uses('AppController', 'Controller');
/**
 * Item Controller
 *
 * @property section $File
 */
class ItemsController extends AppController {

/**
 * file_item method
 *
 * @return void
 */
	public function file_item() {
	  $this->autoLayout = false;
    $this->render('/Elements/media-manager-item');
	}

/**
 * file_error method
 *
 * @return void
 */
	public function file_error() {
	  $this->autoLayout = false;
    $this->render('/Elements/media-manager-item-error');
	}

/**
 * file_library method
 *
 * @return void
 */
	public function file_library() {
	  $this->autoLayout = false;
	  $items = $this->Item->find('all', array(
	    'options' => array('Item.edition_id' => $this->data['editionId']),
	    'recursive' => -1
	  ));
	  $this->set('items', $items);
    $this->render('/Elements/media-manager-library');
	}

/**
 * file_upload method
 *
 * @return void
 */
	public function file_upload() {
	  $this->autoLayout = false;
    $this->autoRender = false;
    if (isset($this->params['form']['file']) && isset($this->params['data']['editionId'])) {
      $file = $this->params['form']['file'];
      
      if ($file['type'] === 'application/octet-stream') {
        $fileType = getimagesize($file['tmp_name']);
        $file['type'] = $fileType['mime'];
      }
      
      $data = array(
        'Item' => array(
          'caption' => $file['name'],
          'type' => $file['type'],
          'size' => $file['size'],
          'edition_id' => $this->params['data']['editionId'],
          'file' => $file,
          'timestamp' => date('Y-m-d H:i:s')
        )
      );
      $fileName = $this->Item->saveFile($data);
      if ($fileName !== false) {
    		$this->Item->create();
    		$data['Item']['filename'] = $fileName;
        if ($this->Item->save($data)) {
          if (isset($this->params['data']['type']) && $this->params['data']['type'] === 'cover') {
            $this->Item->Edition->save(array(
              'Edition' => array(
                'cover' => $fileName,
                'id' => $this->params['data']['editionId']
              )
            ));            
          }
          echo sprintf(__('The file "%s" has been saved'), $data['Item']['filename']);
        } else {
          echo sprintf(__('The file "%s" could not be saved. Please, try again.'), $data['Item']['filename']);
        }
      } else {
        echo sprintf(__('The file "%s" could not be saved. Please, try again.'), $data['Item']['filename']);
      }
    } else {
      //$this->redirect('/');
      exit();
    }
	}
	
/**
 * file_delete method
 *
 * @return void
 */
	public function file_delete($id=null) {
	  $this->autoLayout = false;
    $this->autoRender = false;	
    if (isset($this->data['fileId']) && !empty($this->data['fileId'])) {
      $id = $this->data['fileId'];
    }
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for file'), 'default', array('class' => 'alert alert-warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Item->removeFile($id)) {
  		if ($this->Item->delete($id)) {
        $this->Session->setFlash(__d('File deleted'), 'default', array('class' => 'alert alert-success'));
  		}
		}
		$this->Session->setFlash(__('File was not deleted'), 'default', array('class' => 'alert alert-error'));
    exit();
	}
}