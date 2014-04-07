 <div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  
  <div class="editions index col-md-9">
  
    <?php echo $this->Form->create('Edition', array('class' => 'form-inline')); ?>
  	<fieldset>
  		<legend><?php echo __('Editions'); ?></legend>
      <div class="form-group btn-group">     
  		  <a href="<?php echo $this->Html->url(array('action' => 'index', 'view' => 'tiles')); ?>" class="btn btn-default<?php echo ($this->Session->read('Edition.view') === 'tiles') ? ' active': ''; ?>"><span class="fa fa-th-large"></span></a>
  		  <a href="<?php echo $this->Html->url(array('action' => 'index', 'view' => 'list')); ?>" class="btn btn-default<?php echo ($this->Session->read('Edition.view') === 'list') ? ' active': ''; ?>"><span class="fa fa-list"></span></a>
      </div>
      <div class="form-group">
    		<div class="input-group" style="max-width: 765px;">
      		<?php
            echo $this->Form->input('search', array('div' => false, 'label' => false, 'class' => 'form-control', 'placeholder' => __('Search editions...')));
      		?>
          <span class="input-group-btn">
            <button class="btn btn-default" type="button"><span class="fa fa-search"></span></button>
          </span>
    		</div>
      </div>
  	</fieldset>
  	<?php echo $this->Form->end(); ?>
		<br />
    <?php if (!empty($editions)) : ?>
    <?php
      if ($this->Session->read('Edition.view') === 'list') {
        echo $this->element('list-view');
      } else {
        echo $this->element('tiles-view');        
      }
    ?>
    <?php else : ?>
      <p class="alert alert-info"><?php echo __('No editions found.'); ?></p>
    <?php endif; ?>
  </div>
</div>