<?php
App::uses('AppController', 'Controller');

/**
 * Editions Controller
 *
 * @property Edition $Edition
 */
class EditionsController extends AppController {
  public $helpers = array('Markdown.Markdown');
  public $components = array('Markdown.Markdown', 'Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Edition->recursive = 0;
	  if (isset($this->data['Edition']['search']) && !empty($this->data['Edition']['search'])) {
  	  $query = $this->data['Edition']['search']; 
      $this->Paginator->settings = array(
        'conditions' => array('OR' => array(
          'Edition.name LIKE' => '%'.$query.'%',
          'Edition.description LIKE' => '%'.$query.'%',
          'Edition.author LIKE' => '%'.$query.'%',
          'Edition.publisher LIKE' => '%'.$query.'%'
        ))
      );
	  }
	  if (!$this->Session->check('Edition.view')) {
  	  $this->Session->write('Edition.view', 'tiles');
	  }
	  if (isset($this->params['named']['view']) && $this->params['named']['view'] === 'list') {
  	  $this->Session->write('Edition.view', 'list');
	  } else {
  	  $this->Session->write('Edition.view', 'tiles');
	  }
    $editions = $this->Paginator->paginate('Edition');
    $this->set(compact('editions'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
 
	public function view($id = null) {
		if (!$this->Edition->exists($id)) {
			throw new NotFoundException(__('Invalid edition'));
		}
		$options = array('conditions' => array('Edition.' . $this->Edition->primaryKey => $id));
		$edition = $this->Edition->find('first', $options);
    $this->set('id', $id);
		$this->set('edition', $edition);
		//$this->response->file($file['path'], array('download' => true, 'name' => 'foo'));
		//return $this->response;
	}
	
	
	public function cover_test($id=null) {
	  $this->autoRender = false;
		if (!$this->Edition->exists($id)) {
			throw new NotFoundException(__('Invalid edition'));
		} else {
  		$options = array('recursive' => -1, 'conditions' => array('Edition.' . $this->Edition->primaryKey => $id));
  		$edition = $this->Edition->find('first', $options);
  		
      $cover = @imagecreatetruecolor(1563, 2000) or exit(__('GD is not installed'));
  		
      $stylesZip = EPUB_STYLES.$edition['Edition']['style'];
      $zip = new ZipArchive;
      if ($zip->open($stylesZip) === TRUE) {
      
        $zipName = pathinfo($stylesZip);
        $zipName = $zipName['filename'];
        $tmpStylesPath = TMP.'/styles/'.$zipName.'/';
        $zip->extractTo($tmpStylesPath);
        $zip->close();
        
        $directory = new Folder($tmpStylesPath);
        $css = $directory->find('.*\.css', true);
        $css = array_shift($css);
        
        $file = $tmpStylesPath.$css;
                
    		if (file_exists($file)) {
          $colours = array();
          $fonts = array();

          $file = file_get_contents($file);
          if (!empty($file)) {
            $cssParser = new Sabberworm\CSS\Parser($file);
            $css = $cssParser->parse(); 
            foreach ($css->getAllSelectors() as $block) {
              foreach ($block->getRules() as $rule) {
              
                //Get colours
                if ( in_array($rule->getRule(), array('color', 'background', 'background-color')) ) {
                  $colour = $rule->getValue()->getColor();
                  if (!empty($colour)) {
                    $rgb = array();
                    foreach ($colour as $code) {
                      $rgb[] = $code->getSize();
                    }
                    $rgb = implode(',', $rgb);
                  }
                }
                
                //Get font definitions
                if ( in_array($rule->getRule(), array('font-family')) ) {
                  $font = $rule->getValue()->__toString();
                  if (strpos($font, ',') !== false) {
                    $font = explode(',', $font);
                    $font = array_shift($font);
                  }
                  $font = str_replace(' ', "", str_replace('"', "", $font));
                }                
              }
              if ( !in_array($rgb, $colours) ) {
                $colours[] = $rgb;                
              }

              if ( !in_array($font, $fonts) ) {
                $fonts[] = $font;
              }
            }
          }
    		}
    		$backgroundColour = array_rand($colours, 1);
    		$background = explode(',', $colours[$backgroundColour]);
    		unset($colours[$backgroundColour]);
        $background = imagecolorallocate($cover, $background[0], $background[1], $background[2]);
        if (!empty($colours)) {
      		$titleColour = array_rand($colours, 1);
      		$title = explode(',', $colours[$titleColour]);          
      		unset($colours[$titleColour]);
        } else {
          $title = array(255, 255, 255);
        }
        $title = imagecolorallocate($cover, $title[0], $title[1], $title[2]);
        
        $font = $fonts[array_rand($fonts, 1)];
        $fontPath = $tmpStylesPath.trim($font).'/';
        
        $fontDirectory = new Folder($fontPath);
        $fonts = $fontDirectory->find('.*\.(ttf|otf)', true);
      } else {
        $background = imagecolorallocate($cover, 0, 0, 0);
        $title = imagecolorallocate($cover, 255, 255, 255);
      }

      imagefill($cover , 0,0 , $background);
  
      $font = $fontPath.$fonts[array_rand($fonts)];
      $boundingBox = imagettfbbox(100, 0, $font, $edition['Edition']['name']);
      $y = 100+$boundingBox[3]+100;
      imagettftext($cover, 100, 0, 100, $y, $title, $font, $edition['Edition']['name']);

      $font = $fontPath.$fonts[array_rand($fonts)];
      $boundingBox = imagettfbbox(70, 0, $font, $edition['Edition']['author']);
      $y = $y+$boundingBox[3]+120;
      imagettftext($cover, 70, 0, 100, $y, $title, $font, $edition['Edition']['author']);

      $font = $fontPath.$fonts[array_rand($fonts)];
      $boundingBox = imagettfbbox(30, 0, $font, $edition['Edition']['author']);
      
      $x = ceil((imagesx($cover) - $boundingBox[2]) / 2);
      $y = imagesy($cover)-$boundingBox[3]-100;
      imagettftext($cover, 30, 0, $x, $y, $title, $font, $edition['Edition']['publisher']);
  
      imagepng($cover, TMP.'/test.png');
      imagedestroy($cover);
      
      $directory = new Folder($tmpStylesPath);
      $directory->delete();
		}
	}
	
/**
 * generate method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function generate($id = null) {
	  $this->autoRender = false;
		if (!$this->Edition->exists($id)) {
			throw new NotFoundException(__('Invalid edition'));
		}
		$options = array('conditions' => array('Edition.' . $this->Edition->primaryKey => $id));
		$edition = $this->Edition->find('first', $options);

		$edition['Edition']['name'] = $this->Edition->sanitiseText($edition['Edition']['name']);
		$edition['Edition']['description'] = $this->Edition->sanitiseText($edition['Edition']['description']);
		
		foreach ($edition['Section'] as $index=>$section) {
      $edition['Section'][$index]['text'] = $this->Edition->sanitiseText($edition['Section'][$index]['text']);
  		$edition['Section'][$index]['text'] = $this->Markdown->transform($section['text']);		
		}
 		$cssFile = EPUB_STYLES.$edition['Edition']['style'];
 		
    if ($this->request->is('ajax')) {
      $filepath = $this->EPUB->package($edition, $cssFile, false);
      echo $filepath;
      
    } else {
  		$cssFile = EPUB_STYLES.$edition['Edition']['style'];
      $this->EPUB->package($edition, $cssFile);
    }

		//$this->response->file($file['path'], array('download' => true, 'name' => 'foo'));
		//return $this->response;
	}
	
	
/**
 * markdown_preview method
 *
 * @throws NotFoundException
 * @return void
 */
	public function markdown_preview() {
    $preview = '';
    if ($this->data) {
      $preview = $this->Markdown->transform($this->data);
    }
    $this->set('preview', $preview);
	}

/**
 * get_cover method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function get_cover($id=null) {
	  $this->autoRender = false;
		if (!$this->Edition->exists($id)) {
			throw new NotFoundException(__('Invalid edition'));
		}
    $this->Edition->id = $id;
    echo $this->Edition->field('cover');
	}
/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Edition->create();
			$this->request->data['Edition']['timestamp'] = date('Y-m-d H:i:s');
			if ($this->Edition->save($this->request->data)) {
			  $id = $this->Edition->id;
			  if (isset($this->request->data['Section'][0]) && trim($this->request->data['Section'][0]['text']) == '') {
  			  $this->request->data['Section'][0]['text'] = __('Sample text');
			  }
			  
			  $sections = array(
			    'Edition' => array('id' => $id),
			    'Section' => $this->request->data['Section'][0]
			  );
			  $this->Edition->Section->create();
			  if ($this->Edition->Section->saveAll($sections)) {			  
  				$this->Session->setFlash(__('The edition has been saved'), 'default', array('class' => 'alert alert-success'));
  				$this->redirect(array('action' => 'edit', $id));  			  
			  }
			} else {
				$this->Session->setFlash(__('The edition could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-error'));
			}
		}
    $styles = $this->Edition->getStyles();
    $this->set('styles', $styles);    

		$sections = $this->Edition->Section->find('list');
		$this->set(compact('sections'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Edition->exists($id)) {
			throw new NotFoundException(__('Invalid edition'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		  if (!empty($this->data['Edition']['section-delete'])) {
		    $sections = json_decode($this->data['Edition']['section-delete']);
		    foreach ($sections as $section) {
          $this->Edition->Section->delete($section);
		    }
		  }
		
			if ($this->Edition->save($this->request->data)) {
		    $sections = $this->Edition->saveNewSections($id, $this->request->data['Section']);
			  if ($this->Edition->Section->saveMany($sections)) {
  				$this->Session->setFlash(__('The edition has been saved'), 'default', array('class' => 'alert alert-success'));
  				$this->redirect(array('action' => 'edit', $id));
				} else {
  				$this->Session->setFlash(__('The sections of this edition could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-error'));  				
				}
			} else {
				$this->Session->setFlash(__('The edition could not be saved. Please, try again.'), 'default', array('class' => 'alert alert-error'));
			}
		} else {
			$options = array('conditions' => array('Edition.' . $this->Edition->primaryKey => $id));
			$this->request->data = $this->Edition->find('first', $options);
		}
		$sections = $this->Edition->find('all', array('conditions' => array('Edition.id' => $id), 'contain' => array('Section' => array('order' => 'Section.order ASC'))));
		if (!empty($sections) && isset($sections[0]['Section'])) {
  		$sections = $sections[0]['Section'];
		}

    $styles = $this->Edition->getStyles();
    $this->set('styles', $styles);    
    $this->set('id', $id);
    $chapters = Set::combine($sections, '{n}.title', '{n}.title');
    foreach ($chapters as $key=>$chapter) {
      $slug = strtolower(Inflector::slug($key, '-'));
      $chapters[$slug] = $chapter; 
      unset($chapters[$key]);
    }
    $this->set('chapters', $chapters);
		$this->set(compact('sections'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Edition->id = $id;
		if (!$this->Edition->exists()) {
			throw new NotFoundException(__('Invalid edition'));
		}
		$this->request->onlyAllow('post', 'delete');
		
		$sections = $this->Edition->find('first', array('conditions' => array('Edition.id' => $id), 'contain' => array('Section' => array('order' => 'Section.order ASC'))));
		foreach ($sections['Section'] as $section) {
  		$this->Edition->Section->delete($section['id']);
		}
		
		if ($this->Edition->delete()) {
			$this->Session->setFlash(__('Edition deleted'), 'default', array('class' => 'alert alert-success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Edition was not deleted'), 'default', array('class' => 'alert alert-error'));
		$this->redirect(array('action' => 'index'));
	}
}
