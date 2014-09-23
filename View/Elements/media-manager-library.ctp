                  <?php if (isset($items) && !empty($items)) : ?>
                  <div class="row">
                    <?php foreach ($items as $item) : ?>
                    <div class="thumbnail-container col-sm-6 col-md-3">
                      <a href="#" id="file-<?php echo $item['Item']['id']; ?>" class="thumbnail">
                        <span data-toggle="tooltip" data-placement="right" title="<?php echo __('Delete File'); ?>" class="tooltip-toggle fa fa-times-circle text-danger delete-file"></span> <span data-toggle="tooltip" data-placement="right" title="<?php echo __('Selected'); ?>" class="tooltip-toggle fa fa-check-circle text-success selected-file"></span>
                        <?php echo $this->Html->image(BASE_UPLOAD_PATH.$item['Item']['filename'], array('data-filename' => $item['Item']['filename'], 'alt' => $item['Item']['caption'])); ?>

                        <span data-toggle="tooltip" data-placement="right" title="<?php echo __('Caption'); ?>" class="tooltip-toggle file-caption"><span class="file-caption-inner"><span class="fa fa-file-text"></span></span></span>
                      </a>
                    </div>
                    <div id="file-caption-<?php echo $item['Item']['id']; ?>" class="file-caption-form hidden">
                      <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <textarea class="form-control" rows="3"><?php echo $item['Item']['caption']; ?></textarea>
                      <input type="hidden" class="file-id" value="<?php echo $item['Item']['id']; ?>" name="file-id" />
                    </div>
                    <?php endforeach; ?>
                  </div>
                  <?php else : ?>
                  <p class="alert alert-info"><?php echo __('No files uploaded'); ?></p>
                  <?php endif; ?>