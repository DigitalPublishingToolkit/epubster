 <div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  
  <div class="users index col-md-9">
    <?php echo $this->Form->create('User'); ?>
  	<fieldset>
  		<legend><?php echo __('Users'); ?></legend>
  		<div class="input-group">
    		<?php
          echo $this->Form->input('search', array('div' => false, 'label' => false, 'class' => 'form-control', 'placeholder' => __('Search users...')));
    		?>
        <span class="input-group-btn">
          <button class="btn btn-default" type="button"><span class="fa fa-search"></span></button>
        </span>
  		</div>
  	</fieldset>
  	<?php echo $this->Form->end(); ?>
		<br />
    <?php if (!empty($users)) : ?>
  	<table class="table" cellpadding="0" cellspacing="0">
  	<tr>
  			<?php $caret = '<span class="caret"></span>'; ?>
  			<th><?php echo $this->Paginator->sort('id', __('Id').$caret, array('escape' => false)); ?></th>
  			<th><?php echo $this->Paginator->sort('username', __('Username').$caret, array('escape' => false)); ?></th>
  			<th><?php echo $this->Paginator->sort('email', __('Email').$caret, array('escape' => false)); ?></th>
  			<th><?php echo $this->Paginator->sort('timestamp', __('Timestamp').$caret, array('escape' => false)); ?></th>
  			<th class="actions"><?php echo __('Actions'); ?></th>
  	</tr>
  	<?php foreach ($users as $user): ?>
  	<tr>
  		<td><?php echo h($user['User']['id']); ?> &nbsp;</td>
  		<td><?php echo h($user['User']['username']); ?>&nbsp;</td>
  		<td><?php echo h($user['User']['email']); ?>&nbsp;</td>
  		<td><?php echo h($user['User']['timestamp']); ?>&nbsp;</td>
  		<td class="actions">
  		  <div class="btn-group">
  		    <?php if ($user['User']['id'] == 1) : ?>
    			<?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('action' => 'edit', $user['User']['id']), array('class' => 'btn btn-default btn-sm', 'escape' => false)); ?>
  		    <?php else : ?>
    			<?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('action' => 'edit', $user['User']['id']), array('class' => 'btn btn-default btn-sm', 'escape' => false)); ?><?php echo $this->Form->postLink('<span class="fa fa-trash-o"></span> '.__('Delete'), array('action' => 'delete', $user['User']['id']), array('class' => 'btn btn-default btn-sm', 'escape' => false), __('Are you sure you want to delete # %s?', $user['User']['id'])); ?>
    			<?php endif; ?>
  		  </div>
  		</td>
  	</tr>
  <?php endforeach; ?>
  	</table>

  	<?php //echo $this->element('pagination'); ?>
    <?php else : ?>
      <p class="alert alert-info"><?php echo __('No users found.'); ?></p>
    <?php endif; ?>
  </div>
</div>