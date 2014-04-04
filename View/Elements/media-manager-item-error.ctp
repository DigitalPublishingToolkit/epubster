                      <li id="<?php echo $this->data['fileId']; ?>" class="file text-danger">
                        <?php printf(__('<strong>Error:</strong> %s <strong>Code:</strong> %s %s', $this->data['errorCode'], $this->data['errorMessage'], $this->data['fileName'])); ?>
                      </li>
