<?php
App::uses('AppModel', 'Model');

/**
 * Item Model
 *
 * @property Item $Item
 */
class Item extends AppModel {
  var $actsAs = array('Containable');
  
	var $validate = array(
		'filename' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'The filename should not be empty',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
	var $belongsTo = array(
	 'Edition' => array(
	   'className' => 'Edition',
	   'foreignKey' => 'edition_id',
    )
  );

  private function validateUpload($data){
  	$file = array_shift($data);
  	if ((isset($file['file']['error']) && $file['file']['error'] == 0) || (!empty( $file['file']['tmp_name']) && $file['file']['tmp_name'] != 'none')) {
      $restricted = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py", ".psd", ".ai");
      foreach ($restricted as $mimeType) {
        if(preg_match("/". $mimeType . "\z/i", $file['file']['name'])) {
          return false;
        }
      }
  		return is_uploaded_file($file['file']['tmp_name']);
  	}
  	return false;
  }
	
  private function resizeImage($imageName, $filePath, $fileData, $type) {
    list($originalWidth, $originalHeight) = getimagesize($filePath);
    switch($type) {
      case "resize":
        $newWidth = IMAGE_MEDIUM_WIDTH;
        $newHeight = IMAGE_MEDIUM_HEIGHT;
        $storePath = UPLOAD_PATH.'/medium/';
        if (!file_exists($storePath)) {
          mkdir($storePath);
        }
        
        $originalRatio = $originalWidth/$originalHeight;
    
        if ($newWidth/$newHeight > $originalRatio) {
          $newWidth = $newHeight*$originalRatio;
        }
        else {
          $newHeight = $newWidth/$originalRatio;
        }
        $resizeWidth = $originalWidth;
        $resizeHeight = $originalHeight;
      break;
      case "crop":
        $newWidth = IMAGE_THUMB_WIDTH;
        $newHeight = IMAGE_THUMB_HEIGHT;
        $storePath = UPLOAD_PATH.'/thumbnails/';
        if (!file_exists($storePath)) {
          mkdir($storePath);
        }

        $sourceAspectRatio = $originalWidth / $originalHeight;
        $desiredAspectRatio = $newWidth / $newHeight;
        
        if ($sourceAspectRatio > $desiredAspectRatio) {
          $tempHeight = $newHeight;
          $tempWidth = (int)( $newHeight * $sourceAspectRatio );
        } else {
          $tempWidth = $newWidth;
          $tempHeight = (int)( $newWidth / $sourceAspectRatio );
        }
      break;
    }      
    
    switch($fileData['type']) {
      case "image/jpeg":
      case "image/pjpeg":
        $imageInstance = imagecreatefromjpeg($filePath);
        if ($type == "resize") {
          $imageCanvas = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresampled($imageCanvas, $imageInstance, 0, 0, 0, 0, $newWidth, $newHeight, $resizeWidth, $resizeHeight);
        } else {
          $imageTmp = imagecreatetruecolor( $tempWidth, $tempHeight );
          imagecopyresampled($imageTmp,$imageInstance,0, 0, 0, 0, $tempWidth, $tempHeight, $originalWidth, $originalHeight);
          $x = ( $tempWidth - $newWidth ) / 2;
          $y = ( $tempHeight - $newHeight ) / 2;
        
          $imageCanvas = imagecreatetruecolor( $newWidth, $newHeight );
          imagecopy($imageCanvas, $imageTmp, 0, 0, $x, $y, $newWidth, $newHeight);
        }
        imagejpeg($imageCanvas, $storePath . $imageName, 100);
        imagedestroy($imageCanvas);
        return true;
      break;
      case "image/png";
      case "image/x-png";
        $imageInstance = imagecreatefrompng($filePath);
        if ($type == "resize") {
          $imageCanvas = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresampled($imageCanvas, $imageInstance, 0, 0, 0, 0, $newWidth, $newHeight, $resizeWidth, $resizeHeight);
        } else {
          $imageTmp = imagecreatetruecolor( $tempWidth, $tempHeight );
          imagecopyresampled($imageTmp,$imageInstance,0, 0, 0, 0, $tempWidth, $tempHeight, $originalWidth, $originalHeight);
          $x = ( $tempWidth - $newWidth ) / 2;
          $y = ( $tempHeight - $newHeight ) / 2;
        
          $imageCanvas = imagecreatetruecolor( $newWidth, $newHeight );
          imagecopy($imageCanvas, $imageTmp, 0, 0, $x, $y, $newWidth, $newHeight);
        }
        imagepng($imageCanvas, $storePath . $imageName, 9);
        imagedestroy($imageCanvas);
        return true;
      break;
      case "image/gif":
        $imageInstance = imagecreatefromgif($filePath);
        if ($type == "resize") {
          $imageCanvas = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresampled($imageCanvas, $imageInstance, 0, 0, 0, 0, $newWidth, $newHeight, $resizeWidth, $resizeHeight);
        } else {
          $imageTmp = imagecreatetruecolor( $tempWidth, $tempHeight );
          imagecopyresampled($imageTmp,$imageInstance,0, 0, 0, 0, $tempWidth, $tempHeight, $originalWidth, $originalHeight);
          $x = ( $tempWidth - $newWidth ) / 2;
          $y = ( $tempHeight - $newHeight ) / 2;
        
          $imageCanvas = imagecreatetruecolor( $newWidth, $newHeight );
          imagecopy($imageCanvas, $imageTmp, 0, 0, $x, $y, $newWidth, $newHeight);
        }
        imagegif($imageCanvas, $storePath . $imageName);
        imagedestroy($imageCanvas);
        return true;
      break;
    }
  }
  
  function generateFilename($name) {
    $fileExtension = substr(strrchr($name, '.'), 1);
    $fileName = md5(time()*rand(127834,2347854689734593).'8234dk&(fhas08^*(^&*284578347t83') . '.' . $fileExtension;
    return $fileName;
  }

  function saveFile($data, $fileName) {
  	$file = array_shift($data);
  	$fileData = $file['file'];
    $fileType = $file['type'];

    // Settings
    $targetDir = UPLOAD_PATH;
    if ($file['package']) {
      $targetDir = WWW_ROOT.'/files/styles/';      
    }
    
    $cleanupTargetDir = true; // Remove old files
    $maxFileAge = 5 * 3600; // Temp file age in seconds
    
    // 5 minutes execution time
    @set_time_limit(5 * 60);
    
    
    // Get parameters
    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
    
    // Clean the fileName for security reasons
    $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
    
    // Make sure the fileName is unique but only if chunking is disabled
    if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
    	$ext = strrpos($fileName, '.');
    	$fileName_a = substr($fileName, 0, $ext);
    	$fileName_b = substr($fileName, $ext);
    
    	$count = 1;
    	while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
    		$count++;
    
    	$fileName = $fileName_a . '_' . $count . $fileName_b;
    }
    
    $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
    
    // Create target dir
    if (!file_exists($targetDir))
    	@mkdir($targetDir);
    
    // Remove old temp files	
    if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
    	while (($file = readdir($dir)) !== false) {
    		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
    
    		// Remove temp file if it is older than the max age and is not the current file
    		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
    			@unlink($tmpfilePath);
    		}
    	}
    
    	closedir($dir);
    } else
    	die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
    	
    
    // Look for the content type header
    if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
    	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
    
    if (isset($_SERVER["CONTENT_TYPE"]))
    	$contentType = $_SERVER["CONTENT_TYPE"];
    // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
    if (strpos($contentType, "multipart") !== false) {
    	if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    		// Open temp file
    		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    		if ($out) {
    			// Read binary input stream and append it to temp file
    			$in = fopen($_FILES['file']['tmp_name'], "rb");
    
    			if ($in) {
    				while ($buff = fread($in, 4096))
    					fwrite($out, $buff);
    			} else
    				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    			fclose($in);
    			fclose($out);
    			@unlink($_FILES['file']['tmp_name']);
    		} else
    			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    	} else
    		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
    } else {
    	// Open temp file
    	$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    	if ($out) {
    		// Read binary input stream and append it to temp file
    		$in = fopen("php://input", "rb");
    
    		if ($in) {
    			while ($buff = fread($in, 4096))
    				fwrite($out, $buff);
    		} else
    			die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    
    		fclose($in);
    		fclose($out);
    	} else
    		die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    }
    
    // Check if file has been uploaded
    if (!$chunks || $chunk == $chunks - 1) {
    	// Strip the temp .part suffix off 
    	rename("{$filePath}.part", $filePath);
    }
    
    $uploadDir = $targetDir.$fileName;
    $this->resizeImage($fileName,$uploadDir,$fileData,"crop");
    if ($this->resizeImage($fileName,$uploadDir,$fileData,"resize")) {
      return $fileName;
    } else {
      return false;
    }
    
    //die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    return false;
  }
  
  function removeFile($id) {
    $this->id = $id;
    $fileName = $this->field('filename');
    if (!empty($fileName) && file_exists(UPLOAD_PATH.$fileName)) {
      if (unlink(UPLOAD_PATH.$fileName)) {
        unlink(UPLOAD_PATH.'/medium/'.$fileName);
        unlink(UPLOAD_PATH.'/thumbnails/'.$fileName);
        return true;
      } else {
        return false;
      }
    }
    return true;
  }
}
?>