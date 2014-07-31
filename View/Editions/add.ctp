<div id="form-validation-error" class="alert alert-danger"><?php echo __('Please fill out all the required metadata'); ?></div>

<div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>  
 
  <div class="editions form col-md-9">
  <?php echo $this->Form->create('Edition'); ?>
  	<fieldset>
		<legend><?php echo __('Add Edition'); ?></legend>
    <ul id="edition-tabs" class="nav nav-tabs">
      <li class="active"><a href="#tab-about"><span class="fa fa-pencil-square-o"></span> <?php echo __('About'); ?></a></li>
      <li class="disabled"><a href="javascript:void();"><span class="fa fa-bookmark-o"></span> <?php echo __('Sections'); ?></a></li>
      <li><a href="#tab-content"><span class="fa fa-file-text-o"></span> <?php echo __('Content'); ?></a></li>
      <li class="disabled"><a href="javascript:void();"><span class="fa fa-picture-o"></span> <?php echo __('Design'); ?></a></li>
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
		
      <div class="tab-pane" id="tab-content">
        <p class="explanation"><?php echo __('Create sections below using <a href="https://daringfireball.net/projects/markdown/">Markdown</a> or use the <a href="http://en.wikipedia.org/wiki/WYSIWYG">WYSIWYG</a> editor. Images may also be included here using the upload function and relevant Markdown syntax for including media.'); ?></p>
        
        <?php
      		echo $this->Form->input('index', array('div' => array('class' => 'form-group checkbox'), 'label' => __('Generate an alphabetical index with backreferences based on highlighted words in the edition.'), 'value' => 1, 'type' => 'checkbox'));
      	?>
        <br />
        
      	<ul class="nav nav-tabs" id="section-tabs">
          <li class="active"><a href="#section-sample-section">Sample Section</a></li>
          <li class="alert alert-warning"><?php echo __('Save this edition to create more sections'); ?></li>
      	</ul>
        
        <div class="tab-content">
          <?php echo $this->element('section-form', array('section' => array('title' => 'Sample Section', 'text' => ''), 'count' => 0)); ?>
      	</div>
      </div>
    </div>
  	</fieldset>
	<?php echo $this->Form->input('<span class="fa fa-check"></span> '.__('Save edition'), array('div' => array('class' => 'form-footer'), 'class' => 'btn btn-default btn-primary', 'label' => false, 'type' => 'button')); ?>
  <?php echo $this->Form->end(); ?>
  </div>
</div>