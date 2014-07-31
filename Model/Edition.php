<?php
App::uses('AppModel', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * Edition Model
 *
 * @property Section $Section
 */
class Edition extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
	public $actsAs = array('Containable');

  public $references;
  public $processedReferences;
  public $firstUrl;
  public $nextUrl;
  public $url;
  public $referenceCount = 0;
  public $totalReferencesCount;
  public $currentReferenceGroupCount;
  public $indexType;

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Section' => array(
			'className' => 'Section',
			'joinTable' => 'editions_sections',
			'foreignKey' => 'edition_id',
			'associationForeignKey' => 'section_id',
			'unique' => false,
			'conditions' => '',
			'fields' => '',
			'order' => 'Section.order ASC',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

  public function saveNewSections($editionId=null, $sections=null) {
    if ($editionId && $sections) {
      $data = array();
      foreach ($sections as $index=>$section) {
        if (!isset($section['id'])) {
          $data[] = array(
            'Edition' => array('id' => $editionId),
            'Section' => $section
          );
          unset($sections[$index]);
        }
      }
      $this->Section->saveAll($data);
      return $sections;
    }
  }
  
  public function getStyles() {
    $styleDir = new Folder(WWW_ROOT.'/files/styles/');
    $files = $styleDir->find('.*\.zip');
    $styles = array('default.zip' => 'default');
    foreach ($files as $file) {
      $file = new File($styleDir->pwd() . DS . $file);
      if ($file->name !== 'default.zip') {
        $title = substr($file->name, 0, -4);
        $styles[$file->name] = trim($title);
      }
      $file->close();
    }
    return $styles;
  }
  
  public function beforeSave($options = array()) {
    if (!empty($this->data['Edition']['section-order'])) {
      $order = json_decode($this->data['Edition']['section-order']);
      foreach ($order as $entry) {
        $data[] = array(
          'Section' => array(
            'order' => (int)$entry->order,
            'id' => (int)$entry->id
          )
        );
      }
      $this->Section->saveAll($data);
    }
    return true;
  }
  
  public function sanitiseText($text=null) {
    if ($text) {
  		//Remove Microsoft Word quotes (http://stackoverflow.com/a/6610752/196750)
  		$text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
  		$text = preg_replace('/[^(\x20-\x7F)]*/','', $text);
  		return $text;      
    }
  }
  
  public function parseIndeces($match) {
    $this->referenceCount += 1;
    $match = strip_tags($match[1]);
/*
    if ($key = array_search($match, $this->processedReferences)) {
      $count = count($this->references[$key]['url'])+1;
      $this->references[$key]['url'][] = '<a href="'.$this->url.'">'.$count.'</a>';
    } else {
      $count = 1;
      $this->references[] = array('reference' => $match, 'url' => array('<a href="'.$this->url.'">'.$count.'</a>'));
      $this->processedReferences[] = $match;
    }
*/
    if ($this->referenceCount === 1) {
      $this->firstUrl = $this->url;
    }
    $url = $this->url;
    $nextReference = $this->referenceCount+1;
    
    //This is the last reference, jump to the first one
    if ($this->totalReferencesCount+1 === $nextReference) {
      $nextReference = 1;
      $url = $this->firstUrl;
    }
    
    //This is the last reference in the current group, jump to the next page, if applicable  
    if ($this->referenceCount === $this->currentReferenceGroupCount) {
      if ($this->currentReferenceGroupCount === 1) {
        $this->referenceCount = 1;
        $nextReference = 1;
      }
      $url = $this->nextUrl;
    }
    
    $refname = Inflector::slug($match, '-');
    $refname = 'index-'.$refname;
    return '<a id="'.$refname.'-'.$this->referenceCount.'" class="'.$this->indexType.'" href="'.$url.'#'.$refname.'-'.$nextReference.'">'.$match.'</a>';
  }
  
  public function generateIndex($sections=null, $type='highlight') {
    if ($sections) {
      if ($type === 'person') {
        $this->indexType = 'is-person';
      } else {
        $this->indexType = 'is-highlight';
      }
    
      $this->references = array();
      $this->processedReferences = array();
      foreach ($sections as $position=>$section) {
        $this->url = Inflector::slug($section['title'], '-').'.xhtml';
        preg_match_all('(\<span class=\"'.$this->indexType.'\">(.*?)\<\/span\>)', $section['text'], $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
          foreach ($matches[1] as $index=>$match) {
            $match = strip_tags($match);
            $key = array_search($match, $this->processedReferences);

            if ($key !== false) {
              $count = count($this->references[$key]['url'])+1;
              $this->references[$key]['url'][] = '<a href="'.$this->url.'">'.$count.'</a>';
              $this->references[$key]['position'][] = $position;
            } else {
              $count = 1;
              $this->references[] = array('reference' => $match, 'position' => array($position), 'url' => array('<a href="'.$this->url.'">'.$count.'</a>'));
              $this->processedReferences[] = $match;
            }
          }
        }
      }
      $formattedReferences = array();
      foreach ($this->references as $reference) {
        if ($reference['position'] > 1) {
          $this->referenceCount = 0;
          $this->totalReferencesCount = 0;
          $occurences = array_count_values($reference['position']);
          $positions = array_unique($reference['position']);
          foreach ($positions as $position) {
            preg_match_all('(\<span class=\"'.$this->indexType.'\">('.$reference['reference'].')\<\/span\>)', $sections[$position]['text'], $matches);
            if (!empty($matches[0])) {
              $this->totalReferencesCount += count($matches[0]);
            }
          }
          foreach ($positions as $count=>$position) {
            $this->currentReferenceGroupCount = $occurences[$position];
            $this->url = Inflector::slug($sections[$position]['title'], '-').'.xhtml';
            if (count($sections) > $position+1) {
              $this->nextUrl = Inflector::slug($sections[$position+1]['title'], '-').'.xhtml';
            } else {
              $this->nextUrl = Inflector::slug($sections[0]['title'], '-').'.xhtml';
            }
            $sections[$position]['text'] = preg_replace_callback('(\<span class=\"'.$this->indexType.'\">('.$reference['reference'].')\<\/span\>)', array($this, 'parseIndeces'), $sections[$position]['text']);
          }          
        }
        $formattedReferences[] = '<li>'.$reference['reference'].' <small>('.implode(', ', $reference['url']).')</small></li>';
      }
      return array(
        'references' => $formattedReferences,
        'sections' => $sections
      );
    }
  }
}
