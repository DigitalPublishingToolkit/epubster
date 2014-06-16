    <div class="edition-tile-sort">
      <div class="dropdown pull-right">
        <?php $sort = (isset($this->params['named']['sort'])) ? ucfirst($this->params['named']['sort']): 'Id'; ?>
        <?php $caret = '<span class="caret"></span>'; ?>
        <a data-toggle="dropdown" class="btn btn-default btn-sm" href="#"><span class="fa fa-sort-alpha-asc"></span> <?php echo __('Sort by: '); ?> <strong><?php echo $sort; ?></strong> <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
          <?php $class = (isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'id') ? 'active': ''; ?>
    			<li class="<?php echo $class; ?>"><?php echo $this->Paginator->sort('id', __('Id').$caret, array('escape' => false)); ?></li>
          <?php $class = (isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'name') ? 'active': ''; ?>
    			<li class="<?php echo $class; ?>"><?php echo $this->Paginator->sort('name', __('Name').$caret, array('escape' => false)); ?></li>
          <?php $class = (isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'description') ? 'active': ''; ?>
    			<li class="<?php echo $class; ?>"><?php echo $this->Paginator->sort('description', __('Description').$caret, array('escape' => false)); ?></li>
          <?php $class = (isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'timestamp') ? 'active': ''; ?>
    			<li class="<?php echo $class; ?>"><?php echo $this->Paginator->sort('timestamp', __('Timestamp').$caret, array('escape' => false)); ?></li>
        </ul>
      </div>
    </div>
  	<div class="row">
  	<?php $count=0; ?>
  	<?php foreach ($editions as $edition): ?>
  	<?php
  	  if ($count === 2) {
    	  echo '</div><div class="row">';
  	  }
    ?>
  	<div class="edition-tile col-md-6">
  	  <a href="<?php echo $this->Html->url(array('action' => 'edit', $edition['Edition']['id'])); ?>" class="edition-tile-cover" style="background-image: url('<?php echo $this->Html->url('/files/uploads/'.$edition['Edition']['cover']); ?>');">
  	  </a>
  	  <div class="edition-tile-body">
        <h2><?php echo h($edition['Edition']['name']); ?></h2>
    		<?php $description = (strlen($edition['Edition']['description']) > 325) ? mb_substr($edition['Edition']['description'], 0, 325).'...': $edition['Edition']['description']; ?>

        <p><?php echo h($description); ?></p>
        
        <div class="edition-tile-buttons btn-group">
        	<?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('action' => 'view', $edition['Edition']['id']), array('class' => 'btn btn-default btn-xs', 'escape' => false)); ?><?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('action' => 'edit', $edition['Edition']['id']), array('class' => 'btn btn-default btn-xs', 'escape' => false)); ?><?php echo $this->Form->postLink('<span class="fa fa-trash-o"></span> '.__('Delete'), array('action' => 'delete', $edition['Edition']['id']), array('class' => 'btn btn-default btn-xs', 'escape' => false), __('Are you sure you want to delete # %s?', $edition['Edition']['id'])); ?>
        </div>
  	  </div>
  	</div>
  	<?php $count++; ?>
  <?php endforeach; ?>