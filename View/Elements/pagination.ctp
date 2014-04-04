    <?php if ($this->Paginator->hasPage(1)) : ?>
    <?php $col-md- = isset($col-md-) ? $col-md- : 9; ?>
    <?php $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : 1; ?>

  	<div class="pagination row">
    	<div class="col-md-9">
        <?php if ($this->Paginator->hasPage(2)) : ?>
        <ul>
          <?php 
            echo $this->Paginator->prev(
        			'&larr;',
        			array(
        				'escape' => false,
        				'tag' => 'li'
        			),
        			'<a onclick="return false;">&larr;</a>',
        			array(
        				'class'=>'disabled prev',
        				'escape' => false,
        				'tag' => 'li'
        			)
        		);
          ?>
          <?php $count = $page + $col-md-; ?>
          <?php $i = $page - $col-md-; ?>
          <?php while ($i < $count): ?>
          	<?php $options = ''; ?>
          	<?php if ($i == $page): ?>
          		<?php $options = ' class="active"'; ?>
          	<?php endif; ?>
          	<?php if ($this->Paginator->hasPage($i) && $i > 0): ?>
          	<?php
          	  $params = array("page" => $i);
              if( is_array($this->request->params['named']) ) {
                foreach($this->request->params['named'] as &$item) {
                   $item = urlencode($item);
                }
              }
              $params = array_merge($params, $this->request->params['named']);              
          	?>         	
          		<li<?php echo $options; ?>><?php echo $this->Html->link($i, $params); ?></li>
          	<?php endif; ?>
          	<?php $i += 1; ?>
          <?php endwhile; ?>
  
        	<?php 
          	echo $this->Paginator->next(
        			'&rarr;',
        			array(
        				'escape' => false,
        				'tag' => 'li'
        			),
        			'<a onclick="return false;">&rarr;</a>',
        			array(
        				'class' => 'disabled next',
        				'escape' => false,
        				'tag' => 'li'
        			)
        		);
          ?>
        </ul>
        <?php endif; ?>
    	</div>

      <col-md- class="pagination-count col-md-3"><?php echo $this->Paginator->counter(array('format' => 'Page %page% of %pages%, Total: %count% objects')); ?></col-md->

  	</div>
  	<?php endif; ?>