                      <li id="<?php echo $this->data['fileId']; ?>" class="file">
                        
                        <span class="file-name"><?php echo $this->data['fileName']; ?> <small>(<?php echo $this->data['fileSize']; ?>)</small></span>
                        
                        <span class="progress-container">
                          <span class="progress">
                            <span class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                              <span class="sr-only">0% Complete</span>
                            </span>
                          </span>
                        </span>
                        <span class="remove-file pull-right"><i class="fa fa-times-circle"></i></span>
                      </li>
