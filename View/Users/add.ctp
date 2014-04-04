<div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  
  <div class="editions form col-md-9">
  <?php echo $this->Form->create('User'); ?>
		<legend><?php echo __('Add User'); ?></legend>
  	<fieldset>
  	<?php
  		echo $this->Form->input('username', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('email', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('password', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  	?>
  	</fieldset>
  <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-default btn-primary', 'div' => array('class' => 'form-footer'))); ?>
  
  </div>
</div>