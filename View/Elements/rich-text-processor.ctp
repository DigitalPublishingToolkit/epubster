        <div class="modal fade" id="rich-text-processor" tabindex="-1" role="dialog" aria-labelledby="rich-text-processor" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-magic"></i> <?php echo __('Insert Rich Text'); ?></h4>
              </div>
              <div class="modal-body">
                <p><?php echo __('Paste formatted text from Microsoft Word, or any other word processor, into the textarea below and it will be automatically converted to Markdown upon insertion.'); ?></p>
                <p><?php echo __('Please note that conversion to Markdown will not be able to parse all style characteristics, manual editing is still required.'); ?></p>
                <div class="form-group">
                  <div id="rich-text-preprocessor" class="form-control" contenteditable="true"></div>
                  <textarea id="rich-text-processed" class="form-control" disabled="disabled"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <button id="insert-rich-text" type="button" class="btn btn-primary"><?php echo __('Insert text as Markdown'); ?></button>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div>
        <?php
          echo $this->Html->script(array(
            '/assets/markitdown/Saxonce/Saxonce.nocache.js',
            '/scripts/rich-text-processor.min'
          ), array('inline' => false));
        ?>
        <script type="application/xslt+xml" language="xslt2.0" src="<?php echo $this->Html->url('/assets/markitdown/xsl/html2mk.xsl'); ?>" data-initial-template="start"></script>