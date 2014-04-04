  <?php
    $actions = null;
    switch($this->request->controller) {
      case 'editions':
        $actions = array(
          array('action' => 'index', 'label' => __('All editions'), 'icon' => 'fa fa-bars', 'badge' => @$editionCount),
          array('action' => 'add', 'label' => __('Add new edition'), 'icon' => 'fa fa-plus')
        );
        if ($this->request->action === 'view' && isset($id)) {
          $actions[] = array('action' => 'edit/'.$id, 'label' => __('Edit this edition'), 'icon' => 'fa fa-code', 'divider' => true);
        }
        if ($this->request->action === 'edit' && isset($id)) {
          $actions[] = array('action' => 'view/'.$id, 'label' => __('View this edition'), 'icon' => 'fa fa-eye', 'divider' => true);
        }
        if (($this->request->action === 'edit' || $this->request->action === 'view') && isset($id)) {
          $actions[] = array('action' => 'generate/'.$id, 'label' => __('Generate EPUB'), 'icon' => 'fa fa-cogs');
        }
      break;
      case 'users':
        $actions = array(
          array('action' => 'index', 'label' => __('All users'), 'icon' => 'fa fa-bars', 'badge' => @$userCount),
          array('action' => 'add', 'label' => __('Add new user'), 'icon' => 'fa fa-plus')
        );
      break;
    }
  ?>
  <div class="actions col-md-3">
    <div class="panel panel-default sidebar-nav">
      <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Actions'); ?></h3>
      </div>
    	<ul class="nav nav-list">
    	  <?php if ($actions) : ?>
     		<?php foreach ($actions as $action) : ?>
     		<?php $active = ($action['action'] === $this->request->action) ? 'active': ''; ?>
     		<?php $active .= (isset($action['divider']) && $action['divider']) ? ' divider': ''; ?>
     		<?php $link = '<i class="'.$action['icon'].'"></i> '.$action['label']; ?>
     		<?php $link .= (isset($action['badge']) && !empty($action['badge'])) ? ' <span class="badge pull-right">'.$action['badge'].'</span>': ''; ?>
    		<li class="<?php echo $active; ?>"><?php echo $this->Html->link($link, array('action' => $action['action']), array('escape' => false)); ?></li>
     		<?php endforeach; ?>
    	  <?php endif; ?>
    	</ul>
    </div>
  </div>