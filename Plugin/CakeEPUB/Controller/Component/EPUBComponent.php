<?php
App::uses('Component', 'Controller');

/**
 * EPUBComponent
 *
 * @package		app.Controller.Component
 */
class EPUBComponent extends Component {

	public function package($contents=null, $cssFile=null, $download=true) {
	  if ($contents && !empty($contents)) {
  		if (!isset($this->EPUB)) {
  			if (!class_exists('EPub')) {
  				App::import('Vendor', 'CakeEPUB.PHPePub/EPub');
  			}
  			$this->EPUB = new EPub();
  		}  	  
      $content_start =
      "<?xml version=\"1.0\"?>
        <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
      . "<head>"
      . "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
      . "<title>".$contents['Edition']['name']."</title>\n"
      . "</head>\n"
      . "<body>\n";
      
      $this->bookend = "</body>\n</html>\n";
      $this->EPUB->setTitle($contents['Edition']['name']);
      $this->EPUB->setIdentifier("http://JohnJaneDoePublications.com/books/TestBook.html", EPub::IDENTIFIER_URI); // Could also be the ISBN number, prefered for published books, or a UUID.
      $this->EPUB->setLanguage("en");
      $this->EPUB->setDescription($contents['Edition']['description']);
      $this->EPUB->setAuthor($contents['Edition']['author'], $contents['Edition']['author']);
      $this->EPUB->setPublisher($contents['Edition']['publisher'], $contents['Edition']['publisher_website']);
      $this->EPUB->setDate(time());
      $this->EPUB->setRights("Copyright");
      $this->EPUB->setSourceURL("http://example.com");
      
      if ($cssFile) {
        if (file_exists($cssFile)) {

          $cssDir = substr(substr($cssFile, strrpos($cssFile, '/')+1), 0, -4);
      
          $zip = new ZipArchive;
          $zipFile = $zip->open($cssFile);
          if ($zipFile === true) {
            $zip->extractTo(TMP.$cssDir);
            $zip->close();      
     
            $directory = new Folder(TMP.$cssDir);
            $files = $directory->read(false, array('.', '__MACOSX'), true);
            //Should be recursive in a future version
            foreach ($files as $fileArray) {
              foreach ($fileArray as $file) {
                if (is_dir($file)) {
                  $directoryName = substr($file, strrpos($file, '/')+1);
                  $this->EPUB->addDirectory('OEBPS'.DS.$directoryName);
                  $subDirectory = new Folder($file);
                  $subDirectoryFiles = $subDirectory->read(false, array('.', '__MACOSX'), true);
                  foreach ($subDirectoryFiles as $subDirectoryFilesArray) {
                    if (!empty($subDirectoryFilesArray)) {
                      foreach ($subDirectoryFilesArray as $subDirectoryFile) {
                        if (in_array(substr($subDirectoryFile, -3, 3), array('txt', 'css'))) {
                          $mimetype = 'text/plain';
                        } else {
                          $mimetype = 'application/octet-stream';
                        }
                        $subDirectoryFileName = substr($subDirectoryFile, strrpos($subDirectoryFile, "/")+1);
                        $subDirectoryFileData = file_get_contents($subDirectoryFile);
                        $this->EPUB->addFile($directoryName.DS.$subDirectoryFileName, 'file_'.$subDirectoryFileName, $subDirectoryFileData, $mimetype);
                      }
                    }
                  }
                } else {
                  if (substr($file, -4, 4) === '.css') {
                    $cssData = file_get_contents($file);
                    $this->EPUB->addCSSFile("styles.css", "global-css", $cssData);
                  }
                }
              }
            }
            $directory->delete();
          }
        }
      }
      
      if (!empty($contents['Edition']['cover'])) {
        $this->EPUB->setCoverImage(UPLOAD_PATH.$contents['Edition']['cover']);
      } else {
        $cover = sprintf("<h1>%s</h1>\n<h2>By: %s</h2>\n", $contents['Edition']['name'], $contents['Edition']['author']);
        $cover = $content_start . $cover . $this->bookend;
        $this->EPUB->addChapter("Cover", "CoverPage.xhtml", $cover);    
      }
      
      foreach ($contents['Section'] as $section) {
        $content = $content_start.$section['text'].$this->bookend;
        $title = (!empty($section['title'])) ? $section['title']: 'No Title';
        $filename = Inflector::slug($section['title'], '-').'.html';
        //Fetching external images creates ugly file path at the moment
        $this->EPUB->addChapter($title, $filename, $content, true, EPub::EXTERNAL_REF_ADD);
      } 	  
      $this->EPUB->buildTOC();
  	  $this->EPUB->finalize(); // Finalize the book, and build the archive.
  	  
  	  $filename = Inflector::slug(strtolower($contents['Edition']['name']), '-');
  	  if ($download) {
    	  $zipData = $this->EPUB->sendBook($filename);
  	  } else {
    	  $this->EPUB->saveBook($filename, WWW_ROOT.'files/tmp/');    	  
    	  $zipData = SITE_URL.'files/tmp/'.$filename;
  	  }
  	  return $zipData;
    }
	  return false;
	}
}
?>