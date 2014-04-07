  	<table class="table" cellpadding="0" cellspacing="0">
  	<tr>
  			<?php $caret = '<span class="caret"></span>'; ?>
  			<th><?php echo $this->Paginator->sort('id', __('Id').$caret, array('escape' => false)); ?></th>
  			<th><?php echo $this->Paginator->sort('name', __('Name').$caret, array('escape' => false)); ?></th>
  			<th><?php echo $this->Paginator->sort('description', __('Description').$caret, array('escape' => false)); ?></th>
  			<th><?php echo $this->Paginator->sort('timestamp', __('Timestamp').$caret, array('escape' => false)); ?></th>
  			<th class="actions"><?php echo __('Actions'); ?></th>
  	</tr>
  	<?php foreach ($editions as $edition): ?>
  	<tr>
  		<td><?php echo h($edition['Edition']['id']); ?> &nbsp;</td>
  		<td class="title"><?php echo h($edition['Edition']['name']); ?>&nbsp;</td>
  		<td class="description"><?php echo h($edition['Edition']['description']); ?>&nbsp;</td>
  		<td><?php echo h($edition['Edition']['timestamp']); ?>&nbsp;</td>
  		<td class="actions">
  		  <div class="btn-group">
    			<?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('action' => 'view', $edition['Edition']['id']), array('class' => 'btn btn-default btn-sm', 'escape' => false)); ?><?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('action' => 'edit', $edition['Edition']['id']), array('class' => 'btn btn-default btn-sm', 'escape' => false)); ?><?php echo $this->Form->postLink('<span class="fa fa-trash-o"></span> '.__('Delete'), array('action' => 'delete', $edition['Edition']['id']), array('class' => 'btn btn-default btn-sm', 'escape' => false), __('Are you sure you want to delete # %s?', $edition['Edition']['id'])); ?>
  		  </div>
  		</td>
  	</tr>
  <?php endforeach; ?>
  	</table>

  	<?php //echo $this->element('pagination'); ?>
