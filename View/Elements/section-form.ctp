      <?php $anchor = Inflector::slug(strtolower($section['title']), '-'); ?>
      <div class="tab-pane<?php echo ($count === 0) ? ' active': ''; ?>" id="section-<?php echo $anchor; ?>">
        <?php if (isset($section['id']) && !empty($section['id'])) : ?>
        <button rel="popover" id="tab-settings-<?php echo $section['id']; ?>" type="button" class="tab-settings btn btn-link btn-lg" data-container="body" data-toggle="popover" data-placement="bottom" data-original-title="<?php echo __('Section Options'); ?>" data-content='<?php echo $this->element('tab-settings', array('id' => $section['id'])); ?>'><i class="fa fa-gear"></i></button>
        <?php endif; ?>

        <?php
          $value = Inflector::slug($section['title'], '-').'.xhtml';
          $description = '<p class="text-muted"><small>'.__('Use this value to make internal links in the edition. Changing the section title will also update the filename, so use with caution.').'</small></p>';
      		echo $this->Form->input('Section.'.$count.'.url', array('div' => array('class' => 'form-group'), 'value' => $value, 'readonly' => 'readonly', 'label' => __('URL'), 'after' => $description, 'class' => 'section-title form-control'));
        ?>
        <?php
          if (isset($section['id']) && !empty($section['id'])) {
        		echo $this->Form->input('Section.'.$count.'.id', array('type' => 'hidden', 'value' => $section['id']));            
          }
      		echo $this->Form->input('Section.'.$count.'.title', array('div' => array('class' => 'form-group'), 'value' => $section['title'], 'class' => 'section-title form-control'));
         ?>

         <div class="toggle-editor pull-right">
           <a href="#" class="btn btn-default btn-xs plain-text" data-toggle="tooltip" data-placement="bottom" data-original-title="<?php echo __('Switch to the plain text editor at your own risk, the WYSIWYG editor (currently selected) creates some peculiar markup.'); ?>"><i class="fa fa-terminal"></i> <?php echo __('Plain text editor'); ?></a>
           <a href="#" class="btn btn-default btn-xs wysiwyg hidden"><i class="fa fa-pencil-square"></i> <?php echo __('WYSIWYG editor'); ?></a>
         </div>
         
         <div class="modal-toggles btn-group pull-right">
           <a data-toggle="modal" href="#media-manager" id="media-manager-toggle-<?php echo $count; ?>" class="btn btn-default btn-xs"><i class="fa fa-arrow-circle-o-up"></i> <?php echo __('Insert media'); ?></a>
           <a data-toggle="modal" href="#rich-text-processor" id="rich-text-processor-toggle-<?php echo $count; ?>" class="btn btn-default btn-xs rich-text-processor disabled"><i class="fa fa-magic"></i> <?php echo __('Insert Rich Text'); ?></a>
         </div>

         <?php
    		  //echo $this->Form->input('Section.'.$count.'.text', array('div' => array('class' => 'form-group'), 'label' => __('Content'), 'class' => 'markdown-editor form-control text-editor', 'rows' => 20, 'type' => 'textarea', 'value' => $section['text']));
          ?>
         <strong><?php echo __('Content'); ?></strong><br />
         <div class="html-editor" id="editor-<?php echo 'Section-'.$count.'-text'; ?>">
          <?php echo $section['text']; ?>
         </div>
         <p class="text-muted"><small><?php echo __('In WYSIWYG-mode, use <code>Shift+Enter</code> to create line breaks and <code>Enter</code> to create a new paragraph.'); ?></small></p>

         <div class="plain-text-editor hidden">
         <?php
            echo $this->Form->input('Section.'.$count.'.text', array('div' => array('class' => 'form-group'), 'label' => false, 'class' => 'form-control', 'id' => 'textarea-Section-'.$count.'-text', 'required' => false, 'class' => 'markdown-editor form-control text-editor', 'rows' => 20, 'type' => 'textarea', 'data-placeholder' => __('Start writing the content of this section...'), 'value' => $section['text']))
         ?>
         </div>
      </div>