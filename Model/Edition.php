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
			'order' => '',
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
    $styles = array('default' => 'default');
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
}
