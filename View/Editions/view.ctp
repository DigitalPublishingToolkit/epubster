<div id="content" class="row">
  
  <?php echo $this->element('actions'); ?>
  <div class="editions view col-md-9">
    <h1 class="page-title"><?php echo __('View Edition'); ?></h1>
    <?php echo $this->Html->scriptBlock(sprintf('$(document).ready(function() { generateEPUB(%s); });', $id)); ?>
    <div id="epub-container">
    </div>
  </div>
</div>