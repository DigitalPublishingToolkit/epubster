<div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  
  <div class="editions form col-md-9">
  <?php echo $this->Form->create('Edition'); ?>
		<legend><?php echo __('Add Edition'); ?></legend>
  	<fieldset>
  	<?php
  		echo $this->Form->input('name', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('description', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('author', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('publisher', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('publisher_website', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('style', array('div' => array('class' => 'form-group'), 'class' => 'form-control', 'options' => $styles));
  	?>
  	
  	<ul class="nav nav-tabs" id="section-tabs">
      <li class="active"><a href="#section-sample-section">Sample Section</a></li>
      <li class="alert alert-warning"><?php echo __('Save this edition to create more sections'); ?></li>
  	</ul>
    
    <div class="tab-content">
      <?php echo $this->element('section-form', array('section' => array('title' => 'Sample Section', 'text' => ''), 'count' => 0)); ?>
  	</div>

  	</fieldset>
  <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-default btn-primary', 'div' => array('class' => 'form-footer'))); ?>
  </div>
</div>