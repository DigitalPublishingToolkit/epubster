<div id="form-validation-error" class="alert alert-danger"><?php echo __('Please fill out all the required metadata'); ?></div>

<div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  
  <div class="editions form col-md-9">
  <?php echo $this->Form->create('Edition'); ?>
	<fieldset>
		<legend><?php echo __('Edit Edition'); ?></legend>
  	<?php
  		echo $this->Form->input('id');
  	?>
    <ul id="edition-tabs" class="nav nav-tabs">
      <li class="active"><a href="#tab-about"><span class="fa fa-pencil-square-o"></span> <?php echo __('About'); ?></a></li>
      <li><a href="#tab-sections"><span class="fa fa-bookmark-o"></span> <?php echo __('Sections'); ?></a></li>
      <li><a href="#tab-content"><span class="fa fa-file-text-o"></span> <?php echo __('Content'); ?></a></li>
      <li><a href="#tab-design"><span class="fa fa-picture-o"></span> <?php echo __('Design'); ?></a></li>
    </ul>
    	
    <div class="tab-content">
      <div class="tab-pane active" id="tab-about">

        <p class="explanation"><?php echo __('Please fill in the required metadata below, this data is used by e-readers to build a collection of EPUBs.'); ?></p>
      
        <?php
      		echo $this->Form->input('name', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Title of the publication'), 'class' => 'form-control'));
      		echo $this->Form->input('description', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Description'), 'class' => 'form-control'));
        ?>
        
        <div class="row">
          <div class="col-md-6">
            <?php
          		echo $this->Form->input('author', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Author(s)'), 'class' => 'form-control'));
            ?>
          </div>
          <div class="col-md-6">
          <?php
        		echo $this->Form->input('website', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Website'), 'class' => 'form-control'));
          ?>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
          <?php
        		echo $this->Form->input('publisher', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Publisher'), 'class' => 'form-control'));
          ?>
        </div>
          <div class="col-md-6">
          <?php
        		echo $this->Form->input('publisher_website', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Publisher website'), 'class' => 'form-control'));
          ?>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <?php 
          		echo $this->Form->input('identifier', array('div' => array('class' => 'form-group'), 'required', 'label' => __('Identifier (ISBN)'), 'class' => 'form-control'));       
            ?>
          </div>
        </div>
        
      </div>
      <div class="tab-pane" id="tab-sections">
        <p class="explanation"><?php echo __('Changing the order of the sections will allow you to manipulate the internal structure of the edition and its table of contents.'); ?></p>
      
        <p><strong><?php echo __('Change the order of the sections'); ?></strong></p>
        <?php if (!empty($chapters)) : ?>
        <ul id="section-sorting">
          <?php $count=0; ?>
          <?php foreach ($sections as $section) : ?>
          <li id="section-<?php echo $section['id']; ?>" class="ui-state-default"><span class="section-prefix"><span class="fa fa-sort"></span></span> <span class="section-label"><?php echo $section['title']; ?></span> <a href="#" id="section-delete-<?php echo $section['id']; ?>" class="section-delete"<?php echo ($count === 0) ? ' style="display : none;"': ''; ?>><span class="fa fa-times"></span> <?php echo __('Remove section'); ?></a></li>
          <?php $count++; ?>
          <?php endforeach; ?>
        </ul>
        <?php
      		echo $this->Form->input('section-order', array('type' => 'hidden'));
      		echo $this->Form->input('section-delete', array('type' => 'hidden'));
        ?>        
        <?php else : ?>
        <p class="notice"><?php echo __('No chapters present in this publication'); ?></p>
        <?php endif; ?>         
      </div>
      <div class="tab-pane" id="tab-content">
        <p class="explanation"><?php echo __('Create sections below using <a href="https://daringfireball.net/projects/markdown/">Markdown</a> or use the <a href="http://en.wikipedia.org/wiki/WYSIWYG">WYSIWYG</a> editor. Images may also be included here using the upload function and relevant Markdown syntax for including media.'); ?></p>
        <?php
        	echo $this->Form->input('chapters', array('div' => array('class' => 'form-group'), 'label' => __('Select a section'), 'class' => 'form-control', 'options' => $chapters));
        ?>
        <br />
      	<ul class="nav nav-tabs" id="section-tabs">
        <?php $count=0; ?>
      	<?php foreach ($sections as $section) : ?>
      	  <?php $anchor = Inflector::slug(strtolower($section['title']), ''); ?>
      	  <?php $title = $section['title']; ?>
      	  <?php $title = (strlen($section['title']) > 10) ? mb_substr($title, 0, 10).'...': $title; ?>
          <li<?php echo ($count === 0) ? ' class="active"': ''; ?>><a href="#section-<?php echo $anchor; ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $section['title']; ?>"><?php echo $title; ?></a></li>
          <?php $count++; ?>
        <?php endforeach; ?>
          <li><a id="add-new-section" href="<?php echo $this->Html->url(array('controller' => 'sections', 'action' => 'create_section', 'edition' => $id, 'count' => $count)); ?>" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo __('Create new section'); ?>"><strong><i class="fa fa-plus"></i></strong></a></li>
      	</ul>
        
        <div class="tab-content">
        <?php echo $this->element('media-manager'); ?>
        <?php echo $this->element('rich-text-processor'); ?>
        <?php $count=0; ?>
        <?php foreach ($sections as $section) : ?>
          <?php echo $this->element('section-form', array('section' => $section, 'count' => $count)); ?>
          <?php $count++; ?>
        <?php endforeach; ?>
      	</div>        
      </div>
      <div class="tab-pane" id="tab-design">
        <p class="explanation"><?php echo __('Change the cover of the edition by uploading an image file. The stylesheet to be used for this publication may also be selected. Stylesheet packages (Zip archives) should be uploaded to the <code>webroot/files/styles</code> directory on the server.'); ?></p>
        <strong><?php echo __('Cover'); ?></strong>
        <div id="cover-image" class="row">
          <div class="col-md-3">
            <div id="cover-image-current" style="background-image: url('<?php echo $this->Html->url('/files/uploads/'.$this->data['Edition']['cover']); ?>')">
            </div>
          </div>
          <div class="col-md-3">
            <span class="fa fa-book"></span> <?php echo __('Current cover design'); ?>
          </div>

          <div class="col-md-3">
            <div id="cover-uploader">
              <a href="javascript:;" id="cover-image-upload" class="cover-uploader">
                <span class="fa fa-plus"></span>
              </a>
            </div>
          </div>
          <div class="col-md-3">
            <span class="fa fa-plus"></span> <?php echo __('Add new cover'); ?>
          </div>
        </div>
        
        <label for="EditionStyle"><?php echo __('Style'); ?></label>
        <div id="edition-style-select-group" class="form-group">
          <div id="edition-style-select">
            <?php
            	echo $this->Form->input('style', array('div' => array('class' => 'form-group'), 'label' => false, 'class' => 'form-control ', 'options' => $styles));
            ?>
          </div>
          <a href="javascript:;" id="edition-style-upload" class="btn btn-default edition-style-uploader">
            <span class="fa fa-cloud-upload"></span> <?php echo __('New style package'); ?>
          </a>
        </div>
      </div>
    </div>
	</fieldset>
	<?php echo $this->Form->input('<span class="fa fa-check"></span> '.__('Save edition'), array('div' => array('class' => 'form-footer'), 'class' => 'btn btn-default btn-primary', 'label' => false, 'type' => 'button')); ?>
  <?php echo $this->Form->end(); ?>
  </div>
</div>