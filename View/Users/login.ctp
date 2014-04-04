<div id="content" class="row">
  
  <div class="users form col-md-4 col-md-offset-4">

	<?php echo $this->Session->flash('auth'); ?>

  <?php echo $this->Form->create('User'); ?>
  	<fieldset>
  	<?php
  		echo $this->Form->input('username', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('password', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  	?>
  	
  <?php echo $this->Form->end(array('label' => __('Log in'), 'class' => 'btn btn-default btn-primary', 'div' => array('class' => 'form-footer'))); ?>
  </div>
  
</div>