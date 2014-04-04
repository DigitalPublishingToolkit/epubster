        <div class="modal fade" id="media-manager" tabindex="-1" role="dialog" aria-labelledby="media-manager" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-arrow-circle-o-up"></i> <?php echo __('Insert media'); ?></h4>
              </div>
              <div class="modal-body">
                <div class="btn-group btn-group-justified">
                  <a href="" id="toggle-file-uploader" class="btn btn-default disabled"><?php echo __('File Upload'); ?></a>
                  <a href="" id="toggle-file-library" class="btn btn-default"><?php echo __('Library'); ?></a>
                </div>

                <div id="file-uploader">
                  <div id="filelist">
                    <p id="runtime-alert" class="alert alert alert-danger"><i class="fa fa-gears"></i> <?php echo __('No runtime found.'); ?></p>
                    <ul id="files">
                    </ul>
                  </div>

                  <a id="select-files" class="btn btn-default" href="javascript:;"><?php echo __('Select files to upload'); ?></a> 
                  <a id="upload-files" class="btn btn-default btn-success" href="javascript:;">Upload files</a>
                  
                </div>
                
                <div id="file-library">                 
                </div>

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <button id="insert-media" type="button" class="btn btn-primary"><?php echo __('Insert media'); ?></button>
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div>