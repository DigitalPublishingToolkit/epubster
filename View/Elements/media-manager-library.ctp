                  <?php if (isset($items) && !empty($items)) : ?>
                  <div class="row">
                    <?php foreach ($items as $item) : ?>
                    <div class="thumbnail-container col-sm-6 col-md-3">
                      <a href="#" id="file-<?php echo $item['Item']['id']; ?>" class="thumbnail">
                        <i data-toggle="tooltip" data-placement="right" title="<?php echo __('Delete File'); ?>" class="tooltip-toggle icon icon-remove-sign text-danger delete-file"></i>

                        <i data-toggle="tooltip" data-placement="right" title="<?php echo __('Selected'); ?>" class="tooltip-toggle icon icon-ok-sign selected-file"></i>
                        <?php echo $this->Html->image(BASE_UPLOAD_PATH.$item['Item']['filename'], array('data-filename' => $item['Item']['filename'], 'alt' => $item['Item']['caption'])); ?>
                      </a>
                    </div>
                    <?php endforeach; ?>
                  </div>
                  <?php else : ?>
                  <p class="alert alert-info"><?php echo __('No files uploaded'); ?></p>
                  <?php endif; ?>