      <?php $anchor = Inflector::slug(strtolower($section['title']), '-'); ?>
      <div class="tab-pane<?php echo ($count === 0) ? ' active': ''; ?>" id="section-<?php echo $anchor; ?>">
        <?php if (isset($section['id']) && !empty($section['id'])) : ?>
        <button rel="popover" id="tab-settings-<?php echo $section['id']; ?>" type="button" class="tab-settings btn btn-link btn-lg" data-container="body" data-toggle="popover" data-placement="bottom" data-original-title="<?php echo __('Section Options'); ?>" data-content='<?php echo $this->element('tab-settings', array('id' => $section['id'])); ?>'><i class="fa fa-gear"></i></button>
        <?php endif; ?>

        <?php
          if (isset($section['id']) && !empty($section['id'])) {
        		echo $this->Form->input('Section.'.$count.'.id', array('type' => 'hidden', 'value' => $section['id']));            
          }
      		echo $this->Form->input('Section.'.$count.'.title', array('div' => array('class' => 'form-group'), 'value' => $section['title'], 'class' => 'section-title form-control'));
         ?>
         <div id="modal-toggles" class="btn-group pull-right">
           <a data-toggle="modal" href="#media-manager" id="media-manager-toggle" class="btn btn-default btn-xs"><i class="fa fa-arrow-circle-o-up"></i> <?php echo __('Insert media'); ?></a>
           <a data-toggle="modal" href="#rich-text-processor" id="rich-text-processor-toggle" class="btn btn-default btn-xs"><i class="fa fa-magic"></i> <?php echo __('Insert Rich Text'); ?></a>
         </div>

         <?php
    		  echo $this->Form->input('Section.'.$count.'.text', array('div' => array('class' => 'form-group'), 'label' => 'Content', 'class' => 'markdown-editor form-control text-editor', 'rows' => 20, 'type' => 'textarea', 'value' => $section['text']));
  		  ?>
      </div>