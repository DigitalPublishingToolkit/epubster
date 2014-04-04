<div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  
  <div class="editions form col-md-9">
  <?php echo $this->Form->create('User'); ?>
		<legend><?php echo __('Edit User'); ?></legend>
  	<fieldset>
  	<?php
  		echo $this->Form->input('id');
  		echo $this->Form->input('username', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		echo $this->Form->input('email', array('div' => array('class' => 'form-group'), 'class' => 'form-control'));
  		if ($this->request->data['User']['id'] != 1) {
    		echo $this->Form->input('password', array('div' => array('class' => 'form-group'), 'class' => 'form-control', 'value' => ''));
  		}
  	?>
  	</fieldset>
  	<?php echo $this->Form->input('<span class="fa fa-check"></span> '.__('Save user'), array('div' => array('class' => 'form-footer'), 'class' => 'btn btn-default btn-primary', 'label' => false, 'type' => 'button')); ?>
    <?php echo $this->Form->end(); ?>
  </div>
</div>