<?php
/**
 * Create an ePub compatible book file.
 *
 * Please note, once finalized a book can no longer have chapters of data added or changed.
 *
 * License: GNU LGPL, Attribution required for commercial implementations, requested for everything else.
 *
 * Thanks to: Adam Schmalhofer and Kirstyn Fox for invaluable input and for "nudging" me in the right direction :)
 *
 * @author A. Grandt <php@grandt.com>
 * @copyright 2009-2013 A. Grandt
 * @license GNU LGPL 2.1
 * @version 3.00
 * @link http://www.phpclasses.org/package/6115
 * @link https://github.com/Grandt/PHPePub
 * @uses Zip.php version 1.40; http://www.phpclasses.org/browse/package/6110.html or https://github.com/Grandt/PHPZip
 */
class EPub {
    const VERSION = 3.00;
    const REQ_ZIP_VERSION = 1.40;

    const IDENTIFIER_UUID = 'UUID';
    const IDENTIFIER_URI = 'URI';
    const IDENTIFIER_ISBN = 'ISBN';

    /** Ignore all external references, and do not process the file for these */
    const EXTERNAL_REF_IGNORE = 0;
    /** Process the file for external references and add them to the book */
    const EXTERNAL_REF_ADD = 1;
    /** Process the file for external references and add them to the book, but remove images, and img tags */
    const EXTERNAL_REF_REMOVE_IMAGES = 2;
    /** Process the file for external references and add them to the book, but replace images, and img tags with [image] */
    const EXTERNAL_REF_REPLACE_IMAGES = 3;
	
    const DIRECTION_LEFT_TO_RIGHT = "ltr";
    const DIRECTION_RIGHT_TO_LEFT = "rtl";
	
	const BOOK_VERSION_EPUB2 = "2.0";
    const BOOK_VERSION_EPUB3 = "3.0";

    private $bookVersion = EPub::BOOK_VERSION_EPUB2;

	public $maxImageWidth = 768;
    public $maxImageHeight = 1024;

    public $splitDefaultSize = 250000;
	/** Gifs can crash some early ADE based readers, and are disabled by default. 
	 * getImage will convert these if it can, unless this is set to TRUE.
	 */
    public $isGifImagesEnabled = FALSE;

    private $zip;

    private $title = "";
    private $language = "en";
    private $identifier = "";
    private $identifierType = "";
    private $description = "";
    private $author = "";
    private $authorSortKey = "";
    private $publisherName = "";
    private $publisherURL = "";
    private $date = 0;
    private $rights = "";
    private $coverage = "";
    private $relation = "";
    private $sourceURL = "";

    private $chapterCount = 0;
    private $opf = NULL;
    private $ncx = NULL;
    private $isFinalized = FALSE;
    private $isCoverImageSet = FALSE;

    private $fileList = array();

    /**
     * Used for building the TOC.
     * If this list is overwritten it MUST contain at least "text" as an element.
     */
    public $referencesOrder = NULL;

    private $dateformat = 'Y-m-d\TH:i:s.000000P'; // ISO 8601 long
    private $dateformatShort = 'Y-m-d'; // short date format to placate ePubChecker.
    private $headerDateFormat = "D, d M Y H:i:s T";

    protected $isCurlInstalled;
    protected $isGdInstalled;
    protected $isExifInstalled;
    protected $isFileGetContentsInstalled;
    protected $isFileGetContentsExtInstalled;

    private $bookRoot = "OEBPS/";
    private $docRoot = NULL;
    private $EPubMark = TRUE;
    private $generator = "";

    private $log = NULL;
    public $isLogging = TRUE;

    public $encodeHTML = FALSE;

    private $mimetypes = array(
        "js" => "application/x-javascript", "swf" => "application/x-shockwave-flash", "xht" => "application/xhtml+xml", "xhtml" => "application/xhtml+xml", "zip" => "application/zip",
        "aif" => "audio/x-aiff", "aifc" => "audio/x-aiff", "aiff" => "audio/x-aiff", "au" => "audio/basic", "kar" => "audio/midi", "m3u" => "audio/x-mpegurl", "mid" => "audio/midi", "midi" => "audio/midi", "mp2" => "audio/mpeg", "mp3" => "audio/mpeg", "mpga" => "audio/mpeg", "oga" => "audio/ogg", "ogg" => "audio/ogg", "ra" => "audio/x-realaudio", "ram" => "audio/x-pn-realaudio", "rm" => "audio/x-pn-realaudio", "rpm" => "audio/x-pn-realaudio-plugin", "snd" => "audio/basic", "wav" => "audio/x-wav",
        "bmp" => "image/bmp", "djv" => "image/vnd.djvu", "djvu" => "image/vnd.djvu", "gif" => "image/gif", "ief" => "image/ief", "jpe" => "image/jpeg", "jpeg" => "image/jpeg", "jpg" => "image/jpeg", "pbm" => "image/x-portable-bitmap", "pgm" => "image/x-portable-graymap", "png" => "image/png", "pnm" => "image/x-portable-anymap", "ppm" => "image/x-portable-pixmap", "ras" => "image/x-cmu-raster", "rgb" => "image/x-rgb", "tif" => "image/tif", "tiff" => "image/tiff", "wbmp" => "image/vnd.wap.wbmp", "xbm" => "image/x-xbitmap", "xpm" => "image/x-xpixmap", "xwd" => "image/x-windowdump",
        "asc" => "text/plain", "css" => "text/css", "etx" => "text/x-setext", "htm" => "text/html", "html" => "text/html", "rtf" => "text/rtf", "rtx" => "text/richtext", "sgm" => "text/sgml", "sgml" => "text/sgml", "tsv" => "text/tab-seperated-values", "txt" => "text/plain", "wml" => "text/vnd.wap.wml", "wmls" => "text/vnd.wap.wmlscript", "xml" => "text/xml", "xsl" => "text/xml",
        "avi" => "video/x-msvideo", "mov" => "video/quicktime", "movie" => "video/x-sgi-movie", "mp4" => "video/mp4", "mpe" => "video/mpeg", "mpeg" => "video/mpeg", "mpg" => "video/mpeg", "mxu" => "video/vnd.mpegurl", "ogv" => "video/ogg", "qt" => "video/quicktime", "webm" => "video/webm");

    // These are the ONLY allowed types in that these are the ones ANY reader must support, any other MUST have the fallback attribute pointing to one of these.
    private $coreMediaTypes = array("image/gif", "image/jpeg", "image/png", "image/svg+xml", "application/xhtml+xml", "application/x-dtbook+xml", "application/xml", "application/x-dtbncx+xml", "text/css", "text/x-oeb1-css", "text/x-oeb1-document");

    private $opsContentTypes = array("application/xhtml+xml", "application/x-dtbook+xml", "application/xml", "application/x-dtbncx+xml", "text/x-oeb1-document");

    private $forbiddenCharacters = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");

	private $htmlContentHeader = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n<title></title>\n</head>\n<body>\n";
	private $htmlContentFooter = "</body>\n</html>\n";

    /**
     * Class constructor.
     *
     * @return void
     */
    function __construct($bookVersion = EPub::BOOK_VERSION_EPUB2, $languageCode = "en", $writingDirection = EPub::DIRECTION_LEFT_TO_RIGHT) {
        include_once("Zip.php");
		include_once("Logger.php");
		
		$this->bookVersion = $bookVersion;
        $this->log = new Logger("EPub", $this->isLogging);

        /* Prepare Logging. Just in case it's used. later */
        if ($this->isLogging) {
            $this->log->logLine("EPub class version....: " . self::VERSION);
            $this->log->logLine("EPub req. Zip version.: " . self::REQ_ZIP_VERSION);
            $this->log->logLine("Zip version...........: " . Zip::VERSION);
            $this->log->dumpInstalledModules();
        }

        if (!defined("Zip::VERSION") || Zip::VERSION < self::REQ_ZIP_VERSION) {
            die("<p>EPub version " . self::VERSION . " requires Zip.php at version " . self::REQ_ZIP_VERSION . " or higher.<br />You can obtain the latest version from <a href=\"http://www.phpclasses.org/browse/package/6110.html\">http://www.phpclasses.org/browse/package/6110.html</a>.</p>");
        }

        include_once("EPubChapterSplitter.php");
        include_once("EPub.HtmlEntities.php");
        include_once("EPub.NCX.php");
        include_once("EPub.OPF.php");

        $this->initialize($languageCode, $writingDirection);
    }

    /**
     * Class destructor
     *
     * @return void
     * @TODO make sure elements in the destructor match the current class elements
     */
    function __destruct() {
        $this->zip = NULL;
        $this->title = "";
        $this->author = "";
        $this->publisher = "";
        $this->publishDate = 0;
        $this->bookId = "";
        $this->opf = NULL;
        $this->ncx = NULL;
        $this->chapterCount = 0;
        $this->coverage = "";
        $this->relation = "";
        $this->generator = "";
        $this->log = NULL;
    }

    /**
     *
     * Enter description here ...
     *
     */
    private function initialize($languageCode = "en", $writingDirection = EPub::DIRECTION_LEFT_TO_RIGHT) {
        $this->referencesOrder = array(
        "cover" => "Cover Page",
        Reference::TITLE_PAGE => "Title Page",
        Reference::ACKNOWLEDGEMENTS => "Acknowledgements",
        Reference::BIBLIOGRAPHY => "Bibliography",
        Reference::COLOPHON => "Colophon",
        Reference::COPYRIGHT_PAGE => "Copyright",
        Reference::DEDICATION => "Dedication",
        Reference::EPIGRAPH => "Epigraph",
        Reference::FOREWORD => "Foreword",
        Reference::TABLE_OF_CONTENTS => "Table of Contents",
        Reference::NOTES => "Notes",
        Reference::PREFACE => "Preface",
        Reference::TEXT => "First Page",
        Reference::LIST_OF_ILLUSTRATIONS => "List of Illustrations",
        Reference::LIST_OF_TABLES => "List of Tables",
        Reference::GLOSSARY => "Glossary",
        Reference::INDEX => "Index");

        $this->docRoot = $_SERVER["DOCUMENT_ROOT"] . "/";

        $this->isCurlInstalled = extension_loaded('curl') && function_exists('curl_version');
        $this->isGdInstalled = extension_loaded('gd') && function_exists('gd_info');
        $this->isExifInstalled = extension_loaded('exif') && function_exists('exif_imagetype');
        $this->isFileGetContentsInstalled = function_exists('file_get_contents');
        $this->isFileGetContentsExtInstalled = $this->isFileGetContentsInstalled && ini_get('allow_url_fopen');

        $this->zip = new Zip();
        $this->zip->setExtraField(FALSE);
        $this->zip->addFile("application/epub+zip", "mimetype");
        $this->zip->setExtraField(TRUE);
        $this->zip->addDirectory("META-INF");

        $this->content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<container version=\"1.0\" xmlns=\"urn:oasis:names:tc:opendocument:xmlns:container\">\n\t<rootfiles>\n\t\t<rootfile full-path=\"" . $this->bookRoot . "book.opf\" media-type=\"application/oebps-package+xml\" />\n\t</rootfiles>\n</container>\n";

		if (!$this->isEPubVersion2()) {
			$this->htmlContentHeader = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
			. "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
			. "<head>"
			. "<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
			. "<title></title>\n"
			. "</head>\n"
			. "<body>\n";
		}
		
        $this->zip->addFile($this->content, "META-INF/container.xml", 0, NULL, FALSE);
        $this->content = NULL;
        $this->ncx = new Ncx(NULL, NULL, NULL, $languageCode, $writingDirection);
        $this->opf = new Opf();
		$this->ncx->setVersion($this->bookVersion);
		$this->opf->setVersion($this->bookVersion);
        $this->opf->addItem("ncx", "book.ncx", Ncx::MIMETYPE);
        $this->chapterCount = 0;
    }

    /**
     *
     * @param String $fileName Filename to use for the file, must be unique for the book.
     * @param String $fileId Unique identifier for the file.
     * @param String $fileData File data
     * @param String $mimetype file mime type
     * @return bool $success
     */
    function addFile($fileName, $fileId,  $fileData, $mimetype) {
        if ($this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return FALSE;
        }
        $fileName = Zip::getRelativePath($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);

        $compress = (strpos($mimetype, "image/") !== 0);
        //print "<p>$mimetype : " . ($compress ? "yes" : "no") . "</p>\n";
        $this->zip->addFile($fileData, $this->bookRoot.$fileName, 0, NULL, $compress);

        $this->fileList[$fileName] = $fileName;
        $this->opf->addItem($fileId, $fileName, $mimetype);
        return TRUE;
    }

    /**
     *
     * @param String $fileName Filename to use for the file, must be unique for the book.
     * @param String $fileId Unique identifier for the file.
     * @param String $filePath File path
     * @param String $mimetype file mime type
     * @return bool $success
     */
    function addLargeFile($fileName, $fileId,  $filePath, $mimetype) {
        if ($this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return FALSE;
        }
        $fileName = Zip::getRelativePath($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);

        if ($this->zip->addLargeFile($filePath, $this->bookRoot.$fileName)) {
            $this->fileList[$fileName] = $fileName;
            $this->opf->addItem($fileId, $fileName, $mimetype);
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     *
     * @param String $fileName Filename to use for the file, must be unique for the book.
     * @return bool $success
     */
    function addDirectory($dirName) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->zip->addDirectory($dirName);
        return TRUE;
    }

    /**
     * Add a CSS file to the book.
     *
     * @param String $fileName Filename to use for the CSS file, must be unique for the book.
     * @param String $fileId Unique identifier for the file.
     * @param String $fileData CSS data
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? See documentation for <code>processCSSExternalReferences</code> for explanation. Default is EPub::EXTERNAL_REF_IGNORE.
     * @param String $baseDir Default is "", meaning it is pointing to the document root. NOT used if $externalReferences is set to EPub::EXTERNAL_REF_IGNORE.
     *
     * @return bool $success
     */
    function addCSSFile($fileName, $fileId,  $fileData, $externalReferences = EPub::EXTERNAL_REF_IGNORE, $baseDir = "") {
        if ($this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return FALSE;
        }
        $fileName = Zip::getRelativePath($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);

        if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
            $cssDir = pathinfo($fileName);
            $cssDir = preg_replace('#^[/\.]+#i', "", $cssDir["dirname"] . "/");
            if (!empty($cssDir)) {
                $cssDir = preg_replace('#[^/]+/#i', "../", $cssDir);
            }

            $this->processCSSExternalReferences($fileData, $externalReferences, $baseDir, $cssDir);
        }

        $this->addFile($fileName, "css_" . $fileId, $fileData, "text/css");

        return TRUE;
    }

    /**
     * Add a chapter to the book, as a chapter should not exceed 250kB, you can parse an array with multiple parts as $chapterData.
     * These will still only show up as a single chapter in the book TOC.
     *
     * @param String $chapterName Name of the chapter, will be use din the TOC
     * @param String $fileName Filename to use for the chapter, must be unique for the book.
     * @param String $chapter Chapter text in XHTML or array $chapterData valid XHTML data for the chapter. File should NOT exceed 250kB.
     * @param Bool   $autoSplit Should the chapter be split if it exceeds the default split size? Default=FALSE, only used if $chapterData is a String.
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? See documentation for <code>processChapterExternalReferences</code> for explanation. Default is EPub::EXTERNAL_REF_IGNORE.
     * @param String $baseDir Default is "", meaning it is pointing to the document root. NOT used if $externalReferences is set to EPub::EXTERNAL_REF_IGNORE.
     * @return mixed $success FALSE if the addition failed, else the new NavPoint.
     */
    function addChapter($chapterName, $fileName, $chapterData = NULL, $autoSplit = FALSE, $externalReferences = EPub::EXTERNAL_REF_IGNORE, $baseDir = "") {
        if ($this->isFinalized) {
            return FALSE;
        }
        $fileName = Zip::getRelativePath($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);

        $chapter = $chapterData;
        if ($autoSplit && is_string($chapterData) && mb_strlen($chapterData) > $this->splitDefaultSize) {
            $splitter = new EPubChapterSplitter();

            $chapterArray = $splitter->splitChapter($chapterData);
            if (count($chapterArray) > 1) {
                $chapter = $chapterArray;
            }
        }

        if (!empty($chapter) && is_string($chapter)) {
            if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
                $htmlDirInfo = pathinfo($fileName);
                $htmlDir = preg_replace('#^[/\.]+#i', "", $htmlDirInfo["dirname"] . "/");                
                $this->processChapterExternalReferences($chapter, $externalReferences, $baseDir, $htmlDir);
            }

            if ($this->encodeHTML === TRUE) {
                $chapter = $this->encodeHtml($chapter);
            }
            
            $this->chapterCount++;
            $this->addFile($fileName, "chapter" . $this->chapterCount, $chapter, "application/xhtml+xml");
            $this->opf->addItemRef("chapter" . $this->chapterCount);

            $navPoint = new NavPoint($this->decodeHtmlEntities($chapterName), $fileName, "chapter" . $this->chapterCount);
            $this->ncx->addNavPoint($navPoint);
            $this->ncx->chapterList[$chapterName] = $navPoint;
        } else if (is_array($chapter)) {
            $fileNameParts = pathinfo($fileName);
            $extension = $fileNameParts['extension'];
            $name = $fileNameParts['filename'];
			
            $partCount = 0;
            $this->chapterCount++;

            $oneChapter = each($chapter);
            while ($oneChapter) {
                list($k, $v) = $oneChapter;
                if ($this->encodeHTML === TRUE) {
                    $v = $this->encodeHtml($v);
                }

                if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
                    $this->processChapterExternalReferences($v, $externalReferences, $baseDir);
                }
                $partCount++;
				$partName = $name . "_" . $partCount;
                $this->addFile($partName . "." . $extension, $partName, $v, "application/xhtml+xml");
                $this->opf->addItemRef($partName);

                $oneChapter = each($chapter);
            }
			$partName = $name . "_1." . $extension;
            $navPoint = new NavPoint($this->decodeHtmlEntities($chapterName), $partName, $partName);
            $this->ncx->addNavPoint($navPoint);

            $this->ncx->chapterList[$chapterName] = $navPoint;
        } else if (!isset($chapterData) && strpos($fileName, "#") > 0) {
            $this->chapterCount++;
            //$this->opf->addItemRef("chapter" . $this->chapterCount);

            $navPoint = new NavPoint($this->decodeHtmlEntities($chapterName), $fileName, "chapter" . $this->chapterCount);
            $this->ncx->addNavPoint($navPoint);
            $this->ncx->chapterList[$chapterName] = $navPoint;
		}
        return $navPoint;
    }

	/**
	 * Add one chapter level.
	 * 
	 * Subsequent chapters will be added to this level. 
	 */
	function subLevel($navTitle = NULL, $navId = NULL, $navClass = NULL, $isNavHidden = FALSE, $writingDirection = NULL) {
		return $this->ncx->subLevel($this->decodeHtmlEntities($navTitle), $navId, $navClass, $isNavHidden, $writingDirection);
	}

	/**
	 * Step back one chapter level.
	 * 
	 * Subsequent chapters will be added to this chapters parent level. 
	 */
	function backLevel() {
		$this->ncx->backLevel();
	}

	/**
	 * Step back to the root level.
	 * 
	 * Subsequent chapters will be added to the rooot NavMap. 
	 */
	function rootLevel() {
		$this->ncx->rootLevel();
	}
	
	/**
	 * Step back to the given level.
	 * Useful for returning to a previous level from deep within the structure. 
	 * Values below 2 will have the same effect as rootLevel()
	 * 
	 * @param int $newLevel
	 */
	function setCurrentLevel($newLevel) {
		$this->ncx->setCurrentLevel($newLevel);
	}
	
	/**
	 * Get current level count.
	 * The indentation of the current structure point.
	 * 
	 * @return current level count;
	 */
	function getCurrentLevel() {
		return $this->ncx->getCurrentLevel();
	}
	
    /**
     * Wrap ChapterContent with Head and Footer
     *
     * @param $content
     * @return string $content
     */
    private function wrapChapter($content) {
        return $this->htmlContentHeader . "\n" . $content . "\n" . $this->htmlContentFooter;
    }
    /**
     * Reference pages is usually one or two pages for items such as Table of Contents, reference lists, Author notes or Acknowledgements.
     * These do not show up in the regular navigation list.
     *
     * As they are supposed to be short.
     *
     * @param String $pageName Name of the chapter, will be use din the TOC
     * @param String $fileName Filename to use for the chapter, must be unique for the book.
     * @param String $pageData Page content in XHTML. File should NOT exceed 250kB.
     * @param String $reference Reference key
     * @param int    $externalReferences How to handle external references. See documentation for <code>processChapterExternalReferences</code> for explanation. Default is EPub::EXTERNAL_REF_IGNORE.
     * @param String $baseDir Default is "", meaning it is pointing to the document root. NOT used if $externalReferences is set to EPub::EXTERNAL_REF_IGNORE.
     * @return bool $success
     */
    function addReferencePage($pageName, $fileName, $pageData, $reference, $externalReferences = EPub::EXTERNAL_REF_IGNORE, $baseDir = "") {
        if ($this->isFinalized) {
            return FALSE;
        }
        $fileName = Zip::getRelativePath($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);

        if ($this->encodeHTML === TRUE) {
            $pageData = $this->encodeHtml($pageData);
        }

        if (!empty($pageData) && is_string($pageData)) {
            $this->wrapChapter($pageData);

            if ($externalReferences !== EPub::EXTERNAL_REF_IGNORE) {
                $htmlDirInfo = pathinfo($fileName);
                $htmlDir = preg_replace('#^[/\.]+#i', "", $htmlDirInfo["dirname"] . "/");
                $this->processChapterExternalReferences($pageData, $externalReferences, $baseDir, $htmlDir);
            }

            $this->addFile($fileName, "ref_" . $reference, $pageData, "application/xhtml+xml");
            $this->opf->addItemRef("ref_" . $reference, FALSE);
            $this->opf->addReference($reference, $pageName, $fileName);

            $this->ncx->referencesList[$reference] = $fileName;
            $this->ncx->referencesName[$reference] = $pageName;
            return TRUE;
        }
        return FALSE;
    }

	/**
     * Add custom metadata to the book. 
	 * 
	 * It is up to the builder to make sure there are no collisions. Metadata are just key value pairs.
     *
     * @param unknown_type $name
     * @param unknown_type $content
     */
	function addCustomMetadata($name, $content) {
		$this->opf->addMeta($name, $content);
	}

		/**
	 * Add DublinCore metadata to the book
	 * 
	 * Use the DublinCore constants included in EPub, ie DublinCore::DATE
	 * 
	 * @param type $dublinCore name
	 * @param type $value
	 */
    function addDublinCoreMetadata($dublinCore, $value) {
        if ($this->isFinalized) {
            return;
        }
		
		$this->opf->addDCMeta($dublinCore, $this->decodeHtmlEntities($value));
    }

    /**
     * Add a cover image to the book.
     * If the $imageData is not set, the function assumes the $fileName is the path to the image file.
     *
     * The styling and structure of the generated XHTML is heavily inspired by the XHTML generated by Calibre.
     *
     * @param String $fileName Filename to use for the image, must be unique for the book.
     * @param String $imageData Binary image data
     * @param String $mimetype Image mimetype, such as "image/jpeg" or "image/png".
     * @return bool $success
     */
    function setCoverImage($fileName, $imageData = NULL, $mimetype = NULL) {
        if ($this->isFinalized || $this->isCoverImageSet || array_key_exists("CoverPage.html", $this->fileList)) {
            return FALSE;
        }

        if ($imageData == NULL) {
            // assume $fileName is the valid file path.
            if (!file_exists($fileName)) {
                // Attempt to locate the file using the doc root.
                $rp = realpath($this->docRoot . "/" . $fileName);
	
               if ($rp !== FALSE) {
                    // only assign the docroot path if it actually exists there.
                    $fileName = $rp;
                }
            }
            $image = $this->getImage($fileName);
			$imageData = $image['image'];
            $mimetype = $image['mime'];
            $fileName = preg_replace("#\.[^\.]+$#", "." . $image['ext'], $fileName);
        }


        $path = pathinfo($fileName);
        $imgPath = "images/" . $path["basename"];

        if (empty($mimetype) && file_exists($fileName)) {
            list($width, $height, $type, $attr) = getimagesize($fileName);
            $mimetype = image_type_to_mime_type($type);
        }
        if (empty($mimetype)) {
            $ext = strtolower($path['extension']);
            if ($ext == "jpg") {
                $ext = "jpeg";
            }
            $mimetype = "image/" . $ext;
        }
		$coverPage = "";
		
		if ($this->isEPubVersion2()) {
			$coverPage = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
				. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
				. "  \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
				. "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\" xml:lang=\"en\">\n"
				. "\t<head>\n"
				. "\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>\n"
				. "\t\t<title>Cover Image</title>\n"
				. "\t\t<style type=\"text/css\" title=\"css\">\n"
				. "\t\t\t@page, body, div, img {\n"
				. "\t\t\t\tpadding: 0pt;\n"
				. "\t\t\t\tmargin:0pt;\n"
				. "\t\t\t}\n\t\t\tbody {\n"
				. "\t\t\t\ttext-align: center;\n"
				. "\t\t\t}\n"
				. "\t\t</style>\n"
				. "\t</head>\n"
				. "\t<body>\n"
				. "\t\t<div>\n"
				. "\t\t\t<img src=\"" . $imgPath . "\" alt=\"Cover image\" style=\"height: 100%\"/>\n"
				. "\t\t</div>\n"
				. "\t</body>\n"
				. "</html>\n";
		} else {
		    $coverPage = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
				. "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
				. "<head>"
				. "\t<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n"
				. "\t\t<title>Cover Image</title>\n"
				. "\t\t<style type=\"text/css\" title=\"css\">\n"
				. "\t\t\t@page, body, div, img {\n"
				. "\t\t\t\tpadding: 0pt;\n"
				. "\t\t\t\tmargin:0pt;\n"
				. "\t\t\t}\n\t\t\tbody {\n"
				. "\t\t\t\ttext-align: center;\n"
				. "\t\t\t}\n"
				. "\t\t</style>\n"
				. "\t</head>\n"
				. "\t<body>\n"
				. "\t\t<section epub:type=\"cover\">\n"
				. "\t\t\t<img src=\"" . $imgPath . "\" alt=\"Cover image\" style=\"height: 100%\"/>\n"
				. "\t\t</section>\n"
				. "\t</body>\n"
				. "</html>\n";
		}

        $this->addFile($imgPath, "CoverImage", $imageData, $mimetype);
		$this->addReferencePage("CoverPage", "CoverPage.xhtml", $coverPage, "cover");
        $this->isCoverImageSet = TRUE;
        return TRUE;
    }

    /**
     * Process external references from a HTML to the book. The chapter itself is not stored.
     * the HTML is scanned for &lt;link..., &lt;style..., and &lt;img tags.
     * Embedded CSS styles and links will also be processed.
     * Script tags are not processed, as scripting should be avoided in e-books.
     *
     * EPub keeps track of added files, and duplicate files referenced across multiple
     *  chapters, are only added once.
     *
     * If the $doc is a string, it is assumed to be the content of an HTML file,
     *  else is it assumes to be a DOMDocument.
     *
     * Basedir is the root dir the HTML is supposed to "live" in, used to resolve
     *  relative references such as <code>&lt;img src="../images/image.png"/&gt;</code>
     *
     * $externalReferences determines how the function will handle external references.
     *
     * @param mixed  &$doc (referenced)
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param String $baseDir Default is "", meaning it is pointing to the document root.
     * @param String $htmlDir The path to the parent HTML file's directory from the root of the archive.
     *
     * @return Bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    protected function processChapterExternalReferences(&$doc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "") {
        if ($this->isFinalized || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return FALSE;
        }

        $backPath = preg_replace('#[^/]+/#i', "../", $htmlDir);
        $isDocAString = is_string($doc);
        $xmlDoc = NULL;

        if ($isDocAString) {
            $xmlDoc = new DOMDocument();
            @$xmlDoc->loadHTML($doc);
        } else {
            $xmlDoc = $doc;
        }

        $this->processChapterStyles($xmlDoc, $externalReferences, $baseDir, $htmlDir);
        $this->processChapterLinks($xmlDoc, $externalReferences, $baseDir, $htmlDir, $backPath);
        $this->processChapterImages($xmlDoc, $externalReferences, $baseDir, $htmlDir, $backPath);

        if ($isDocAString) {
            //$html = $xmlDoc->saveXML();

            $head = $xmlDoc->getElementsByTagName("head");
            $body = $xmlDoc->getElementsByTagName("body");

            $xml = new DOMDocument('1.0', "utf-8");
            $xml->lookupPrefix("http://www.w3.org/1999/xhtml");
            $xml->preserveWhiteSpace = FALSE;
            $xml->formatOutput = TRUE;

            $xml2Doc = new DOMDocument('1.0', "utf-8");
            $xml2Doc->lookupPrefix("http://www.w3.org/1999/xhtml");
            $xml2Doc->loadXML("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n</html>\n");
            $html = $xml2Doc->getElementsByTagName("html")->item(0);
            $html->appendChild($xml2Doc->importNode($head->item(0), TRUE));
            $html->appendChild($xml2Doc->importNode($body->item(0), TRUE));

            // force pretty printing and correct formatting, should not be needed, but it is.
            $xml->loadXML($xml2Doc->saveXML());
            $doc = $xml->saveXML();
			
			if (!$this->isEPubVersion2()) {
				$doc = preg_replace('#^\s*<!DOCTYPE\ .+?>\s*#im', '', $doc);
			}
        }
        return TRUE;
    }

    /**
     * Process images referenced from an CSS file to the book.
     *
     * $externalReferences determins how the function will handle external references.
     *
     * @param String &$cssFile (referenced)
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param String $baseDir Default is "", meaning it is pointing to the document root.
     * @param String $cssDir The of the CSS file's directory from the root of the archive.
     *
     * @return Bool  FALSE if unsuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    protected function processCSSExternalReferences(&$cssFile, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $cssDir = "") {
        if ($this->isFinalized || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return FALSE;
        }

        $backPath = preg_replace('#[^/]+/#i', "../", $cssDir);
        $imgs = null;
        preg_match_all('#url\s*\([\'\"\s]*(.+?)[\'\"\s]*\)#im', $cssFile, $imgs, PREG_SET_ORDER);

        $itemCount = count($imgs);
        for ($idx = 0; $idx < $itemCount; $idx++) {
            $img = $imgs[$idx];
            if ($externalReferences === EPub::EXTERNAL_REF_REMOVE_IMAGES || $externalReferences === EPub::EXTERNAL_REF_REPLACE_IMAGES) {
                $cssFile = str_replace($img[0], "", $cssFile);
            } else {
                $source = $img[1];

                $pathData = pathinfo($source);
                $internalSrc = $pathData['basename'];
                $internalPath = "";
                $isSourceExternal = FALSE;

                if ($this->resolveImage($source, $internalPath, $internalSrc, $isSourceExternal, $baseDir, $cssDir, $backPath)) {
                    $cssFile = str_replace($img[0], "url('" . $backPath . $internalPath . "')", $cssFile);
                } else if ($isSourceExternal) {
                    $cssFile = str_replace($img[0], "", $cssFile); // External image is missing
                } // else do nothing, if the image is local, and missing, assume it's been generated.
            }
        }
        return TRUE;
    }

    /**
     * Process style tags in a DOMDocument. Styles will be passed as CSS files and reinserted into the document.
     *
     * @param DOMDocument &$xmlDoc (referenced)
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param String $baseDir  Default is "", meaning it is pointing to the document root.
     * @param String $htmlDir  The path to the parent HTML file's directory from the root of the archive.
     *
     * @return Bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    protected function processChapterStyles(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "") {
        if ($this->isFinalized || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return FALSE;
        }
        // process inlined CSS styles in style tags.
        $styles = $xmlDoc->getElementsByTagName("style");
        $styleCount = $styles->length;
        for ($styleIdx = 0; $styleIdx < $styleCount; $styleIdx++) {
            $style = $styles->item($styleIdx);

            $styleData = preg_replace('#[/\*\s]*\<\!\[CDATA\[[\s\*/]*#im', "", $style->nodeValue);
            $styleData = preg_replace('#[/\*\s]*\]\]\>[\s\*/]*#im', "", $styleData);

            $this->processCSSExternalReferences($styleData, $externalReferences, $baseDir, $htmlDir);
            $style->nodeValue  = "\n" . trim($styleData) . "\n";
        }
        return TRUE;
    }

    /**
     * Process link tags in a DOMDocument. Linked files will be loaded into the archive, and the link src will be rewritten to point to that location.
     * Link types text/css will be passed as CSS files.
     *
     * @param DOMDocument &$xmlDoc (referenced)
     * @param int    $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param String $baseDir  Default is "", meaning it is pointing to the document root.
     * @param String $htmlDir  The path to the parent HTML file's directory from the root of the archive.
     * @param String $backPath The path to get back to the root of the archive from $htmlDir.
     *
     * @return Bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    protected function processChapterLinks(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "", $backPath = "") {
        if ($this->isFinalized || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return FALSE;
        }
        // process link tags.
        $links = $xmlDoc->getElementsByTagName("link");
        $linkCount = $links->length;
        for ($linkIdx = 0; $linkIdx < $linkCount; $linkIdx++) {
            $link = $links->item($linkIdx);
            $source = $link->attributes->getNamedItem("href")->nodeValue;
            $sourceData = NULL;

            $pathData = pathinfo($source);
            $internalSrc = $pathData['basename'];

            if (preg_match('#^(http|ftp)s?://#i', $source) == 1) {
                $urlinfo = parse_url($source);

                if (strpos($urlinfo['path'], $baseDir."/") !== FALSE) {
                    $internalSrc = substr($urlinfo['path'], strpos($urlinfo['path'], $baseDir."/") + strlen($baseDir) + 1);
                }

                @$sourceData = getFileContents($source);
            } else if (strpos($source, "/") === 0) {
                @$sourceData = file_get_contents($this->docRoot . $source);
            } else {
                @$sourceData = file_get_contents($this->docRoot . $baseDir . "/" . $source);
            }

            if (!empty($sourceData)) {
                if (!array_key_exists($internalSrc, $this->fileList)) {
                    $mime = $link->attributes->getNamedItem("type")->nodeValue;
                    if (empty($mime)) {
                        $mime = "text/plain";
                    }
                    if ($mime == "text/css") {
                        $this->processCSSExternalReferences($sourceData, $externalReferences, $baseDir, $htmlDir);
                        $this->addCSSFile($internalSrc, $internalSrc, $sourceData, EPub::EXTERNAL_REF_IGNORE, $baseDir);
                        $link->setAttribute("href", $backPath . $internalSrc);
                    } else {
                        $this->addFile($internalSrc, $internalSrc, $sourceData, $mime);
                    }
                    $this->fileList[$internalSrc] = $source;
                } else {
                    $link->setAttribute("href", $backPath . $internalSrc);
                }
            } // else do nothing, if the link is local, and missing, assume it's been generated.
        }
        return TRUE;
    }

    /**
     * Process img tags in a DOMDocument.
     * $externalReferences will determine what will happen to these images, and the img src will be rewritten accordingly.
     *
     * @param DOMDocument &$xmlDoc             (referenced)
     * @param int          $externalReferences How to handle external references, EPub::EXTERNAL_REF_IGNORE, EPub::EXTERNAL_REF_ADD or EPub::EXTERNAL_REF_REMOVE_IMAGES? Default is EPub::EXTERNAL_REF_ADD.
     * @param String       $baseDir            Default is "", meaning it is pointing to the document root.
     * @param String       $htmlDir            The path to the parent HTML file's directory from the root of the archive.
     * @param String       $backPath           The path to get back to the root of the archive from $htmlDir.
     *
     * @return Bool  FALSE if uncuccessful (book is finalized or $externalReferences == EXTERNAL_REF_IGNORE).
     */
    protected function processChapterImages(&$xmlDoc, $externalReferences = EPub::EXTERNAL_REF_ADD, $baseDir = "", $htmlDir = "", $backPath = "") {
        if ($this->isFinalized || $externalReferences === EPub::EXTERNAL_REF_IGNORE) {
            return FALSE;
        }
        // process img tags.
        $postProcDomElememts = array();
        $images = $xmlDoc->getElementsByTagName("img");
        $itemCount = $images->length;
        for ($idx = 0; $idx < $itemCount; $idx++) {
            $img = $images->item($idx);
            if ($externalReferences === EPub::EXTERNAL_REF_REMOVE_IMAGES) {
                $postProcDomElememts[] = $img;
            } else if ($externalReferences === EPub::EXTERNAL_REF_REPLACE_IMAGES) {
                $altNode = $img->attributes->getNamedItem("alt");
				$alt = "image";
				if ($altNode !== NULL && strlen($altNode->nodeValue) > 0) {
					$alt = $altNode->nodeValue;
				}
                $postProcDomElememts[] = array($img, $this->createDomFragment($xmlDoc, "<em>[" . $alt . "]</em>"));
            } else {
                $source = $img->attributes->getNamedItem("src")->nodeValue;

                $parsedSource = parse_url($source);
                $internalSrc = $this->sanitizeFileName(urldecode(pathinfo($parsedSource['path'], PATHINFO_BASENAME)));
                $internalPath = "";
                $isSourceExternal = FALSE;

                if ($this->resolveImage($source, $internalPath, $internalSrc, $isSourceExternal, $baseDir, $htmlDir, $backPath)) {
                    $img->setAttribute("src", $backPath . $internalPath);
                } else if ($isSourceExternal) {
                    $postProcDomElememts[] = $img; // External image is missing
                } // else do nothing, if the image is local, and missing, assume it's been generated.
            }
        }

        foreach ($postProcDomElememts as $target) {
            if (is_array($target)) {
                $target[0]->parentNode->replaceChild($target[1], $target[0]);
            } else {
                $target->parentNode->removeChild($target);
            }
        }
        return TRUE;
    }

    /**
     * Resolve an image src and determine it's target location and add it to the book.
     *
     * @param String  $source Image Source link.
     * @param String &$internalPath (referenced) Return value, will be set to the target path and name in the book.
     * @param String &$internalSrc (referenced) Return value, will be set to the target name in the book.
     * @param String &$isSourceExternal (referenced) Return value, will be set to TRUE if the image originated from a full URL.
     * @param String  $baseDir  Default is "", meaning it is pointing to the document root.
     * @param String  $htmlDir  The path to the parent HTML file's directory from the root of the archive.
     * @param String  $backPath The path to get back to the root of the archive from $htmlDir.
     */
    protected function resolveImage($source, &$internalPath, &$internalSrc, &$isSourceExternal, $baseDir = "", $htmlDir = "", $backPath = "") {
        if ($this->isFinalized) {
            return FALSE;
        }
        $imageData  = NULL;

        if (preg_match('#^(http|ftp)s?://#i', $source) == 1) {
            $urlinfo = parse_url($source);

            if (strpos($urlinfo['path'], $baseDir."/") !== FALSE) {
                $internalSrc = $this->sanitizeFileName(urldecode(substr($urlinfo['path'], strpos($urlinfo['path'], $baseDir."/") + strlen($baseDir) + 1)));
            }
            $internalPath = $urlinfo["scheme"] . "/" . $urlinfo["host"] . "/" . pathinfo($urlinfo["path"], PATHINFO_DIRNAME);
            $isSourceExternal = TRUE;
            $imageData = $this->getImage($source);
        } else if (strpos($source, "/") === 0) {
            $internalPath = pathinfo($source, PATHINFO_DIRNAME);
            $imageData = $this->getImage($this->docRoot . $source);
        } else {
            $internalPath = $htmlDir . "/" . preg_replace('#^[/\.]+#', '', pathinfo($source, PATHINFO_DIRNAME));
            $imageData = $this->getImage($this->docRoot . $baseDir . "/" . $source);
        }
        if ($imageData !== FALSE) {
            $internalPath = Zip::getRelativePath("images/" . $internalPath . "/" . $internalSrc);
            if (!array_key_exists($internalPath, $this->fileList)) {
                $this->addFile($internalPath, "i_" . $internalSrc, $imageData['image'], $imageData['mime']);
                $this->fileList[$internalPath] = $source;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Resolve a media src and determine it's target location and add it to the book.
     *
     * @param String $source Source link.
     * @param String $internalPath (referenced) Return value, will be set to the target path and name in the book.
     * @param String $internalSrc (referenced) Return value, will be set to the target name in the book.
     * @param String $isSourceExternal (referenced) Return value, will be set to TRUE if the image originated from a full URL.
     * @param String $baseDir  Default is "", meaning it is pointing to the document root.
     * @param String $htmlDir  The path to the parent HTML file's directory from the root of the archive.
     * @param String $backPath The path to get back to the root of the archive from $htmlDir.
     */
    protected function resolveMedia($source, &$internalPath, &$internalSrc, &$isSourceExternal, $baseDir = "", $htmlDir = "", $backPath = "") {
        if ($this->isFinalized) {
            return FALSE;
        }
        $mediaPath  = NULL;

        if (preg_match('#^(http|ftp)s?://#i', $source) == 1) {
            $urlinfo = parse_url($source);

            if (strpos($urlinfo['path'], $baseDir."/") !== FALSE) {
                $internalSrc = substr($urlinfo['path'], strpos($urlinfo['path'], $baseDir."/") + strlen($baseDir) + 1);
            }
            $internalPath = $urlinfo["scheme"] . "/" . $urlinfo["host"] . "/" . pathinfo($urlinfo["path"], PATHINFO_DIRNAME);
            $isSourceExternal = TRUE;
            @$mediaPath = $source;
        } else if (strpos($source, "/") === 0) {
            $internalPath = pathinfo($source, PATHINFO_DIRNAME);
            @$mediaPath = $this->docRoot . $source;
        } else {
            $internalPath = $htmlDir . "/" . preg_replace('#^[/\.]+#', '', pathinfo($source, PATHINFO_DIRNAME));
            @$mediaPath = $this->docRoot . $baseDir . "/" . $source;
        }

        if ($mediaPath !== FALSE) {
            $mime = $this->getMime($source);
            $internalPath = Zip::getRelativePath("media/" . $internalPath . "/" . $internalSrc);
            if (!array_key_exists($internalPath, $this->fileList) &&
            $this->addLargeFile($internalPath, "m_" . $internalSrc, $mediaPath, $mime)) {
                $this->fileList[$internalPath] = $source;
            }
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get Book Chapter count.
     *
     * @access public
     * @return number of chapters
     */
    function getChapterCount() {
        return $this->chapterCount;
    }

    /**
     * Book title, mandatory.
     *
     * Used for the dc:title metadata parameter in the OPF file as well as the DocTitle attribute in the NCX file.
     *
     * @param string $title
     * @access public
     * @return bool $success
     */
    function setTitle($title) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->title = $title;
        return TRUE;
    }

    /**
     * Get Book title.
     *
     * @access public
     * @return $title
     * @Deprecated
     */
    function getTitle() {
        return $this->title;
    }

    /**
     * Book language, mandatory
     *
     * Use the RFC3066 Language codes, such as "en", "da", "fr" etc.
     * Defaults to "en".
     *
     * Used for the dc:language metadata parameter in the OPF file.
     *
     * @param string $language
     * @access public
     * @return bool $success
     */
    function setLanguage($language) {
        if ($this->isFinalized || mb_strlen($language) != 2) {
            return FALSE;
        }
        $this->language = $language;
        return TRUE;
    }

    /**
     * Get Book language.
     *
     * @access public
     * @return $language
     * @Deprecated
     */
    function getLanguage() {
        return $this->language;
    }

    /**
     * Unique book identifier, mandatory.
     * Use the URI, or ISBN if available.
     *
     * An unambiguous reference to the resource within a given context.
     *
     * Recommended best practice is to identify the resource by means of a
     *  string conforming to a formal identification system.
     *
     * Used for the dc:identifier metadata parameter in the OPF file, as well
     *  as dtb:uid in the NCX file.
     *
     * Identifier type should only be:
     *  EPub::IDENTIFIER_URI
     *  EPub::IDENTIFIER_ISBN
     *  EPub::IDENTIFIER_UUID
     *
     * @param string $identifier
     * @param string $identifierType
     * @access public
     * @return bool $success
     */
    function setIdentifier($identifier, $identifierType) {
        if ($this->isFinalized || ($identifierType !== EPub::IDENTIFIER_URI && $identifierType !== EPub::IDENTIFIER_ISBN && $identifierType !== EPub::IDENTIFIER_UUID)) {
            return FALSE;
        }
        $this->identifier = $identifier;
        $this->identifierType = $identifierType;
        return TRUE;
    }

    /**
     * Get Book identifier.
     *
     * @access public
     * @return $identifier
     * @Deprecated
     */
    function getIdentifier() {
        return $this->identifier;
    }

    /**
     * Get Book identifierType.
     *
     * @access public
     * @return $identifierType
     * @Deprecated
     */
    function getIdentifierType() {
        return $this->identifierType;
    }

    /**
     * Book description, optional.
     *
     * An account of the resource.
     *
     * Description may include but is not limited to: an abstract, a table of
     *  contents, a graphical representation, or a free-text account of the
     *  resource.
     *
     * Used for the dc:source metadata parameter in the OPF file
     *
     * @param string $description
     * @access public
     * @return bool $success
     */
    function setDescription($description) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->description = $description;
        return TRUE;
    }

    /**
     * Get Book description.
     *
     * @access public
     * @return $description
     * @Deprecated
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * Book author or creator, optional.
     * The $authorSortKey is basically how the name is to be sorted, usually
     *  it's "Lastname, First names" where the $author is the straight
     *  "Firstnames Lastname"
     *
     * An entity primarily responsible for making the resource.
     *
     * Examples of a Creator include a person, an organization, or a service.
     *  Typically, the name of a Creator should be used to indicate the entity.
     *
     * Used for the dc:creator metadata parameter in the OPF file and the
     *  docAuthor attribure in the NCX file.
     * The sort key is used for the opf:file-as attribute in dc:creator.
     *
     * @param string $author
     * @param string $authorSortKey
     * @access public
     * @return bool $success
     */
    function setAuthor($author, $authorSortKey) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->author = $author;
        $this->authorSortKey = $authorSortKey;
        return TRUE;
    }

    /**
     * Get Book author.
     *
     * @access public
     * @return $author
     * @Deprecated
     */
    function getAuthor() {
        return $this->author;
    }

    /**
     * Publisher Information, optional.
     *
     * An entity responsible for making the resource available.
     *
     * Examples of a Publisher include a person, an organization, or a service.
     *  Typically, the name of a Publisher should be used to indicate the entity.
     *
     * Used for the dc:publisher and dc:relation metadata parameters in the OPF file.
     *
     * @param string $publisherName
     * @param string $publisherURL
     * @access public
     * @return bool $success
     */
    function setPublisher($publisherName, $publisherURL) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->publisherName = $publisherName;
        $this->publisherURL = $publisherURL;
        return TRUE;
    }

    /**
     * Get Book publisherName.
     *
     * @access public
     * @return $publisherName
     * @Deprecated
     */
    function getPublisherName() {
        return $this->publisherName;
    }

    /**
     * Get Book publisherURL.
     *
     * @access public
     * @return $publisherURL
     * @Deprecated
     */
    function getPublisherURL() {
        return $this->publisherURL;
    }

    /**
     * Release date, optional. If left blank, the time of the finalization will
     *  be used.
     *
     * A point or period of time associated with an event in the lifecycle of
     *  the resource.
     *
     * Date may be used to express temporal information at any level of
     *  granularity.  Recommended best practice is to use an encoding scheme,
     *  such as the W3CDTF profile of ISO 8601 [W3CDTF].
     *
     * Used for the dc:date metadata parameter in the OPF file
     *
     * @param long $timestamp
     * @access public
     * @return bool $success
     */
    function setDate($timestamp) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->date = $timestamp;
        $this->opf->date = $timestamp;
        return TRUE;
    }

    /**
     * Get Book date.
     *
     * @access public
     * @return $date
     * @Deprecated
     */
    function getDate() {
        return $this->date;
    }

    /**
     * Book (copy)rights, optional.
     *
     * Information about rights held in and over the resource.
     *
     * Typically, rights information includes a statement about various
     *  property rights associated with the resource, including intellectual
     *  property rights.
     *
     * Used for the dc:rights metadata parameter in the OPF file
     *
     * @param string $rightsText
     * @access public
     * @return bool $success
     */
    function setRights($rightsText) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->rights = $rightsText;
        return TRUE;
    }

    /**
     * Get Book rights.
     *
     * @access public
     * @return $rights
     * @Deprecated
     */
    function getRights() {
        return $this->rights;
    }

    /**
     * Add book Subject.
     *
     * The topic of the resource.
     *
     * Typically, the subject will be represented using keywords, key phrases,
     *  or classification codes. Recommended best practice is to use a
     *  controlled vocabulary. To describe the spatial or temporal topic of the
     *  resource, use the Coverage element.
     *
     * @param String $subject
     */
    function setSubject($subject) {
        if ($this->isFinalized) {
            return;
        }
		$this->opf->addDCMeta(DublinCore::SUBJECT, $this->decodeHtmlEntities($subject));
    }

	/**
     * Book source URL, optional.
     *
     * A related resource from which the described resource is derived.
     *
     * The described resource may be derived from the related resource in whole
     *  or in part. Recommended best practice is to identify the related
     *  resource by means of a string conforming to a formal identification system.
     *
     * Used for the dc:source metadata parameter in the OPF file
     *
     * @param string $sourceURL
     * @access public
     * @return bool $success
     */
    function setSourceURL($sourceURL) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->sourceURL = $sourceURL;
        return TRUE;
    }

    /**
     * Get Book sourceURL.
     *
     * @access public
     * @return $sourceURL
     * @Deprecated
     */
    function getSourceURL() {
        return $this->sourceURL;
    }

    /**
     * Coverage, optional.
     *
     * The spatial or temporal topic of the resource, the spatial applicability
     *  of the resource, or the jurisdiction under which the resource is relevant.
     *
     * Spatial topic and spatial applicability may be a named place or a location
     *  specified by its geographic coordinates. Temporal topic may be a named
     *  period, date, or date range. A jurisdiction may be a named administrative
     *  entity or a geographic place to which the resource applies. Recommended
     *  best practice is to use a controlled vocabulary such as the Thesaurus of
     *  Geographic Names [TGN]. Where appropriate, named places or time periods
     *  can be used in preference to numeric identifiers such as sets of
     *  coordinates or date ranges.
     *
     * Used for the dc:coverage metadata parameter in the OPF file
     *
	 * Same as ->addDublinCoreMetadata(DublinCore::COVERAGE, $coverage);
	 * 
     * @param string $coverage
     * @access public
     * @return bool $success
     */
    function setCoverage($coverage) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->coverage = $coverage;
        return TRUE;
    }

    /**
     * Get Book coverage.
     *
     * @access public
     * @return $coverage
     * @Deprecated
     */
    function getCoverage() {
        return $this->coverage;
    }

    /**
     * Set book Relation.
     *
     * A related resource.
     *
     * Recommended best practice is to identify the related resource by means
     *  of a string conforming to a formal identification system.
     *
     * @param String $relation
     */
    function setRelation($relation) {
        if ($this->isFinalized) {
            return;
        }
        $this->relation = $relation;
    }

    /**
     * Get the book relation.
     *
     * @return String The relation.
     * @Deprecated
     */
    function getRelation() {
        return $this->relation;
    }

    /**
     * Set book Generator.
     *
     * The generator is a meta tag added to the ncx file, it is not visible
     *  from within the book, but is a kind of electronic watermark.
     *
     * @param String $generator
     */
    function setGenerator($generator) {
        if ($this->isFinalized) {
            return;
        }
        $this->generator = $generator;
    }

    /**
     * Get the book relation.
     *
     * @return String The generator identity string.
     * @Deprecated
     */
    function getGenerator() {
        return $this->generator;
    }

    /**
     * Set ePub date formate to the short yyyy-mm-dd form, for compliance with
     *  a bug in EpubCheck, prior to its version 1.1.
     *
     * The latest version of ePubCheck can be obtained here:
     *  http://code.google.com/p/epubcheck/
     *
     * @access public
     * @return bool $success
     */
    function setShortDateFormat() {
        if ($this->isFinalized) {
            return FALSE;
        }
        $this->dateformat = $this->dateformatShort;
        return TRUE;
    }

    /**
     * @Deprecated
     */
    function setIgnoreEmptyBuffer($ignoreEmptyBuffer = TRUE) {
        return TRUE;
    }

	/**
	 * Set the references title for the ePub 3 landmarks section
	 * 
	 * @param type $referencesTitle
	 */
	function setReferencesTitle($referencesTitle = "Guide") {
        if ($this->isFinalized) {
            return FALSE;
        }
		$this->ncx->referencesTitle = is_string($referencesTitle) ? trim($referencesTitle) : "Guide";
	}
	
    /**
     * Get Book status.
     *
     * @access public
     * @return boolean
     */
    function isFinalized() {
        return $this->isFinalized;
    }

    /**
     * Build the Table of Contents. This is not strictly necessary, as most eReaders will build it from the navigation structure in the .ncx file.
     *
     * @param String $cssFileName Include a link to this css file in the TOC html.
     * @param String $tocCSSClass The TOC is a <div>, if you need special formatting, you can add a css class for that div. Default is "toc".
     * @param String $title Title of the Table of contents. Default is "Table of Contents". Use this for ie. languages other than English.
     * @param boolean $references include reference pages in the TOC, using the $referencesOrder array to determine the order of the pages in the TOC. Default is TRUE.
     */
    function buildTOC($cssFileName = NULL, $tocCSSClass = "toc", $title = "Table of Contents", $references = TRUE) {
        if ($this->isFinalized) {
            return FALSE;
        }
        $cssFileName = preg_replace('#^[/\.]+#i', "", Zip::getRelativePath($cssFileName));

        if (empty($title)) {
            $title = "Table of Contents";
        }

		$tocData = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		
		if ($this->isEPubVersion2()) {
			$tocData .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
			. "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
			. "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
		    . "<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
		} else {
			$tocData .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\">\n"
		    . "<head>\n<meta http-equiv=\"Default-Style\" content=\"text/html; charset=utf-8\" />\n";
		}

        if (!empty($cssFileName)) {
            $tocData .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $cssFileName . "\" />\n";
        }
        $tocData .= "<title>Table of Contents</title>\n"
        . "</head>\n"
        . "<body>\n"
        . "<h3>" . $title . "</h3>\n<div";

        if (!empty($tocCSSClass)) {
            $tocData .= " class=\"" . $tocCSSClass . "\"";
        }
        $tocData .= ">\n";

        while (list($item, $descriptive) = each($this->referencesOrder)) {
            if ($item === "text") {
                while (list($chapterName, $navPoint) = each($this->ncx->chapterList)) {
					$fileName = $navPoint->getContentSrc();
					$level = $navPoint->getLevel() -2;
					$tocData .= "\t<p>" . str_repeat(" &#160;  &#160;  &#160;", $level) . "<a href=\"" . $fileName . "\">" . $chapterName . "</a></p>\n";
                }
            } else if ($references === TRUE) {
                if (array_key_exists($item, $this->ncx->referencesList)) {
                    $tocData .= "\t<p><a href=\"" . $this->ncx->referencesList[$item] . "\">" . $descriptive . "</a></p>\n";
                } else if ($item === "toc") {
                    $tocData .= "\t<p><a href=\"TOC.xhtml\">" . $title . "</a></p>\n";
                } else if ($item === "cover" && $this->isCoverImageSet) {
                    $tocData .= "\t<p><a href=\"CoverPage.xhtml\">Cover</a></p>\n";
                }
            }
        }
        $tocData .= "</div>\n</body>\n</html>\n";

        $this->addReferencePage($title, "TOC.xhtml", $tocData, Reference::TABLE_OF_CONTENTS);
    }

	function isEPubVersion2() {
		return $this->bookVersion === EPub::BOOK_VERSION_EPUB2;
	}
	
	function buildEPub3TOC($cssFileName = NULL, $title = "Table of Contents") {
		$this->ncx->referencesOrder = $this->referencesOrder;
		$this->ncx->setDocTitle($this->decodeHtmlEntities($this->title));
		return $this->ncx->finalizeEPub3($title, $cssFileName);
	}	
	
	function addEPub3TOC($fileName, $tocData) {
		if ($this->isEPubVersion2() || $this->isFinalized || array_key_exists($fileName, $this->fileList)) {
            return FALSE;
        }
        $fileName = Zip::getRelativePath($fileName);
        $fileName = preg_replace('#^[/\.]+#i', "", $fileName);

        $this->zip->addFile($tocData, $this->bookRoot.$fileName);

        $this->fileList[$fileName] = $fileName;
        $this->opf->addItem("toc", $fileName, "application/xhtml+xml", "nav");
        return TRUE;
	}	

	/**
     * Check for mandatory parameters and finalize the e-book.
     * Once finalized, the book is locked for further additions.
     *
     * @return bool $success
     */
    function finalize() {
        if ($this->isFinalized || $this->chapterCount == 0 || empty($this->title) || empty($this->language)) {
            return FALSE;
        }

        if (empty($this->identifier) || empty($this->identifierType)) {
            $this->setIdentifier($this->createUUID(4), EPub::IDENTIFIER_UUID);
        }

        if ($this->date == 0) {
            $this->date = time();
        }

        if (empty($this->sourceURL)) {
            $this->sourceURL = $this->getCurrentPageURL();
        }

        if (empty($this->publisherURL)) {
            $this->sourceURL = $this->getCurrentServerURL();
        }

        // Generate OPF data:
        $this->opf->setIdent("BookId");
        $this->opf->initialize($this->title, $this->language, $this->identifier, $this->identifierType);

        if (!empty($this->description)) {
            $this->opf->addDCMeta(DublinCore::DESCRIPTION, $this->decodeHtmlEntities($this->description));
        }

        if (!empty($this->publisherName)) {
            $this->opf->addDCMeta(DublinCore::PUBLISHER, $this->decodeHtmlEntities($this->publisherName));
        }

        if (!empty($this->publisherURL)) {
            $this->opf->addDCMeta(DublinCore::RELATION, $this->decodeHtmlEntities($this->publisherURL));
        }

        if (!empty($this->author)) {
            $this->opf->addCreator($this->decodeHtmlEntities($this->author), $this->decodeHtmlEntities($this->authorSortKey), MarcCode::AUTHOR);
        }

        $DCdate = new DublinCore(DublinCore::DATE, gmdate($this->dateformat, $this->date));
        $DCdate->addOpfAttr("event", "publication");
        $this->opf->metadata->addDublinCore($DCdate);

        if (!empty($this->rights)) {
            $this->opf->addDCMeta(DublinCore::RIGHTS, $this->decodeHtmlEntities($this->rights));
        }

        if (!empty($this->coverage)) {
            $this->opf->addDCMeta(DublinCore::COVERAGE, $this->decodeHtmlEntities($this->coverage));
        }

        if (!empty($this->sourceURL)) {
            $this->opf->addDCMeta(DublinCore::SOURCE, $this->sourceURL);
        }

        if (!empty($this->relation)) {
            $this->opf->addDCMeta(DublinCore::RELATION, $this->decodeHtmlEntities($this->relation));
        }

        if ($this->isCoverImageSet) {
            $this->opf->addMeta("cover", "CoverImage");
        }

        if ($this->EPubMark) {
            $this->opf->addMeta("generator", "EPub (" . self::VERSION . ") by A. Grandt, http://www.phpclasses.org/package/6115");
        }
        if (!empty($this->generator)) {
            $this->opf->addMeta("generator", $this->decodeHtmlEntities($this->generator));
        }

        reset($this->ncx->chapterList);
        list($firstChapterName, $firstChapterNavPoint) = each($this->ncx->chapterList);
		$firstChapterFileName = $firstChapterNavPoint->getContentSrc();

        $this->opf->addReference(Reference::TEXT, $this->decodeHtmlEntities($firstChapterName), $firstChapterFileName);

        $this->ncx->setUid($this->identifier);

        if ($this->EPubMark) {
            $this->ncx->addMetaEntry("dtb:generator", "EPub (" . self::VERSION . ") by A. Grandt, http://www.phpclasses.org/package/6115");
        }
        if (!empty($this->generator)) {
            $this->ncx->addMetaEntry("dtb:generator", $this->decodeHtmlEntities($this->generator));
        }
        $this->ncx->setDocTitle($this->decodeHtmlEntities($this->title));

        if (!empty($this->author)) {
            $this->ncx->setDocAuthor($this->decodeHtmlEntities($this->author));
        }
		
		if (!$this->isEPubVersion2()) {
			$this->addEPub3TOC("epub3toc.xhtml", $this->buildEPub3TOC());
		}
		
        $this->opf = $this->fixEncoding($this->opf->finalize());
        $ncxFinal = $this->fixEncoding($this->ncx->finalize());

        if (mb_detect_encoding($this->opf, 'UTF-8', true) === "UTF-8") {
            $this->zip->addFile($this->opf, $this->bookRoot."book.opf");
        } else {
            $this->zip->addFile(mb_convert_encoding($this->opf, "UTF-8"), $this->bookRoot."book.opf");
        }
        if (mb_detect_encoding($ncxFinal, 'UTF-8', true) === "UTF-8") {
            $this->zip->addFile($ncxFinal, $this->bookRoot."book.ncx");
        } else {
            $this->zip->addFile(mb_convert_encoding($ncxFinal, "UTF-8"), $this->bookRoot."book.ncx");
        }

        $this->opf = NULL;
        $this->ncx = NULL;

        $this->isFinalized = TRUE;
        return TRUE;
    }

    /**
     *
     * Ensure the encoded string is a valid UTF-8 string.
     *
     * Note, that a mb_detect_encoding on the returned string will still return ASCII if the entire string is comprized of characters in the 1-127 range.
     *
     * @link: http://snippetdb.com/php/convert-string-to-utf-8-for-mysql
     * @param String $in_str
     * @return converted string.
     */
    function fixEncoding($in_str) {
        if (mb_detect_encoding($in_str) == "UTF-8" && mb_check_encoding($in_str,"UTF-8")) {
            return $in_str;
        } else {
            return utf8_encode($in_str);
        }
    }

    /**
     * Return the finalized book.
     *
     * @return String with the book in binary form.
     */
    function getBook() {
        if (!$this->isFinalized) {
            $this->finalize();
        }

        return $this->zip->getZipData();
    }

    /**
     * Remove disallowed characters from string to get a nearly safe filename
     *
     * @param $fileName
     * @return mixed|string
     */
    function sanitizeFileName($fileName) {
        $fileName1 = str_replace($this->forbiddenCharacters, '', $fileName);
        $fileName2 = preg_replace('/[\s-]+/', '-', $fileName1);
        return trim($fileName2, '.-_');
    }

    /**
     * Save the ePub file to local disk.
     *
     * @param $fileName
     * @param $baseDir If empty baseDir is absolute to server path, if omitted it's relative to script path
     * @return The sent file name if successfull, FALSE if it failed.
     */
    function saveBook($fileName, $baseDir = '.') {

        // Make fileName safe
        $fileName = $this->sanitizeFileName($fileName);

        // Finalize book, if it's not done already
        if (!$this->isFinalized) {
            $this->finalize();
        }

		if (stripos(strrev($fileName), "bupe.") !== 0) {
            $fileName .= ".epub";
        }
		
        // Try to open file access
        $fh = fopen($baseDir.'/'.$fileName, "w");

        if ($fh) {
            fputs($fh, $this->getBook());
            fclose($fh);

            // if file is written return TRUE
            return $fileName;
        }

        // return FALSE by default
        return FALSE;
    }

    /**
     * Return the finalized book size.
     *
     * @return String
     */
    function getBookSize() {
        if (!$this->isFinalized) {
            $this->finalize();
        }

        return $this->zip->getArchiveSize();
    }

    /**
     * Send the book as a zip download
     *
     * Sending will fail if the output buffer is in use. You can override this limit by
     *  calling setIgnoreEmptyBuffer(TRUE), though the function will still fail if that
     *  buffer is not empty.
     *
     * @param String $fileName The name of the book without the .epub at the end.
     * @return The sent file name if successfull, FALSE if it failed.
     */
    function sendBook($fileName) {
        if (!$this->isFinalized) {
            $this->finalize();
        }

        if (stripos(strrev($fileName), "bupe.") !== 0) {
            $fileName .= ".epub";
        }

        if (TRUE === $this->zip->sendZip($fileName, "application/epub+zip")) {
			return $fileName;
		}
        return FALSE;
    }

    /**
     * Generates an UUID.
     *
     * Default version (4) will generate a random UUID, version 3 will URL based UUID.
     *
     * Added for convinience
     *
     * @param  int    $bookVersion UUID version to retrieve, See lib.uuid.manual.html for details.
     * @return string $url     The formatted uuid
     */
    function createUUID($bookVersion = 4, $url = NULL) {
        include_once("lib.uuid.php");
        return UUID::mint($bookVersion, $url, UUID::nsURL);
    }

    /**
     * Get the url of the current page.
     * Example use: Default Source URL
     *
     * $return Page URL as a string.
     */
    function getCurrentPageURL() {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://" . $_SERVER["SERVER_NAME"];
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= ":" . $_SERVER["SERVER_PORT"];
        }
        $pageURL .= $_SERVER["REQUEST_URI"];
        return $pageURL;
    }

    /**
     * Get the url of the server.
     * Example use: Default Publisher URL
     *
     * $return Server URL as a string.
     */
    function getCurrentServerURL() {
        $serverURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $serverURL .= "s";
        }
        $serverURL .= "://" . $_SERVER["SERVER_NAME"];
        if ($_SERVER["SERVER_PORT"] != "80") {
            $serverURL .= ":" . $_SERVER["SERVER_PORT"];
        }
        return $serverURL . '/';
    }

    /**
     * Try to determine the mimetype of the file path.
     *
     * @param String $source Path
     * @return String mimetype, or FALSE.
     */
    function getMime($source) {
        return $this->mimetypes[pathinfo($source, PATHINFO_EXTENSION)];
    }

    /**
     * Get an image from a file or url, return it resized if the image exceeds the $maxImageWidth or $maxImageHeight directives.
     *
     * The return value is an array.
     * ['width'] is the width of the image.
     * ['height'] is the height of the image.
     * ['mime'] is the mime type of the image. Resized images are always in jpeg format.
     * ['image'] is the image data.
     * ['ext'] is the extension of the image file.
     *
     * @param String $source path or url to file.
     * $return array
     */
    function getImage($source) {
        $width = -1;
        $height = -1;
        $mime = "application/octet-stream";
        $type = FALSE;
		$ext = "";


        $image = $this->getFileContents($source);

        if ($image !== FALSE && strlen($image) > 0) {
            $imageFile = imagecreatefromstring($image);
            if ($imageFile !== false) {
                $width = ImageSX($imageFile);
                $height = ImageSY($imageFile);
            }
            if ($this->isExifInstalled) {
                @$type = exif_imagetype($source);
                $mime = image_type_to_mime_type($type);
            }
            if ($mime === "application/octet-stream") {
                $mime = $this->image_file_type_from_binary($image);
            }
            if ($mime === "application/octet-stream") {
                $mime = $this->getMimeTypeFromUrl($source);
            }
        } else {
            return FALSE;
        }

        if ($width <= 0 || $height <= 0) {
            return FALSE;
        }

        $ratio = 1;

        if ($this->isGdInstalled) {
            if ($width > $this->maxImageWidth) {
                $ratio = $this->maxImageWidth/$width;
            }
            if ($height*$ratio > $this->maxImageHeight) {
                $ratio = $this->maxImageHeight/$height;
            }
            if ($ratio < 1 || empty($mime) || 
					!($mime == "image/jpeg" || $mime == "image/png" || 
					    ($this->isGifImagesEnabled && $mime == "image/gif"))) {
                $image_o = imagecreatefromstring($image);
                $image_p = imagecreatetruecolor($width*$ratio, $height*$ratio);
                imagecopyresampled($image_p, $image_o, 0, 0, 0, 0, ($width*$ratio), ($height*$ratio), $width, $height);
                ob_start();
                imagejpeg($image_p, NULL, 80);
                $image = ob_get_contents();
                ob_end_clean();
                imagedestroy($image_o);
                imagedestroy($image_p);
                $mime = "image/jpeg";
                $ext = "jpg";
            }
        }
		if ($ext === "") {
			static $mimeToExt = array (
				'image/jpeg' => 'jpg',
				'image/gif' => 'gif',
				'image/png' => 'png'
            );

			if (isset($mimeToExt[$mime])) {
				$ext = $mimeToExt[$mime];
			}
		}

        $rv = array();
        $rv['width'] = $width*$ratio;
        $rv['height'] = $height*$ratio;
        $rv['mime'] = $mime;
        $rv['image'] = $image;
        $rv['ext'] = $ext;

        return $rv;
    }

    /**
     * Get file contents, using curl if available, else file_get_contents
     *
     * @param type $source
     * @return boolean
     */
    function getFileContents($source) {
        $isExternal = preg_match('#^(http|ftp)s?://#i', $source) == 1;

        if ($isExternal && $this->isCurlInstalled) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            if ($info['http_code'] == 200 && $res != false) {
                return $res;
            }
        }

        if ($this->isFileGetContentsInstalled && (!$isExternal || $this->isFileGetContentsExtInstalled)) {
            @$data = file_get_contents($source);
            return $data;
        }
        return FALSE;
    }

    /**
    * get mime type from image data
    *
    * By fireweasel found on http://stackoverflow.com/questions/2207095/get-image-mimetype-from-resource-in-php-gd
    * @staticvar array $type
    * @param type $binary
    * @return string
    */
    function image_file_type_from_binary($binary) {
        $hits = 0;
        if (!preg_match(
                '/\A(?:(\xff\xd8\xff)|(GIF8[79]a)|(\x89PNG\x0d\x0a)|(BM)|(\x49\x49(?:\x2a\x00|\x00\x4a))|(FORM.{4}ILBM))/',
                $binary, $hits)) {
            return 'application/octet-stream';
        }
        static $type = array (
            1 => 'image/jpeg',
            2 => 'image/gif',
            3 => 'image/png',
            4 => 'image/x-windows-bmp',
            5 => 'image/tiff',
            6 => 'image/x-ilbm',
        );
        return $type[count($hits) - 1];
    }

    function getMimeTypeFromUrl($source) {
        $ext = FALSE;

        $srev = strrev($source);
        $pos = strpos($srev, "?");
        if ($pos !== FALSE) {
            $srev = substr($srev, $pos+1);
        }

        $pos = strpos($srev, ".");
        if ($pos !== FALSE) {
            $ext = strtolower(strrev(substr($srev, 0, $pos)));
        }

        if ($ext !== FALSE) {
            switch ($ext) {
                case "jpg":
                case "jpe":
                case "jpeg":
                    return 'image/jpeg';
                case "gif":
                    return 'image/gif';
                case "png":
                    return 'image/png';
                case "bmp":
                    return 'image/x-windows-bmp';
                case "tif":
                case "tiff":
                case "cpt":
                    return 'image/tiff';
                case "lbm":
                case "ilbm":
                    return 'image/x-ilbm';
            }
        }
        return "application/octet-stream";
    }

    /**
     *
     * Encode html code to use html entities, safeguarding it from potential character encoding peoblems
     * This function is a bit different from the vanilla htmlentities function in that it does not encode html tags.
     *
     * The regexp is taken from the PHP Manual discussion, it was written by user "busbyjon".
     * http://www.php.net/manual/en/function.htmlentities.php#90111
     *
     * @param String $string string to encode.
     */
    public function encodeHtml($string) {
        $string = strtr($string, $this->html_encoding_characters);

        //return preg_replace("/&amp;(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/", "&\\1", $string);
        //return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/", "&amp;", $string);
        return $string;
    }

    /**
     * Helper function to create a DOM fragment with given markup.
     *
     * @author Adam Schmalhofer
     *
     * @param DOMDocument $dom
     * @param String $markup
     * @return DOMNode fragment in a node.
     */
    protected function createDomFragment($dom, $markup) {
        $node = $dom->createDocumentFragment();
        $node->appendXML($markup);
        return $node;
    }

    /**
     * Retrieve an array of file names currently added to the book.
     * $key is the filename used in the book
     * $value is the original filename, will be the same as $key for most entries
     *
     * @return array file list
     */
    function getFileList() {
        return $this->fileList;
    }

    /**
     * Clean up a path
     * If the path starts with a "/", it is deemed absolute and any /../ in the beginning is stripped off.
     * The returned path will not end in a "/".
     *
     * @param String $relPath The path to clean up
     * @return String the clean path
     * @deprecated Redundant, please use Zip::getRelativePath($relPath) instead.
     */
    function relPath($relPath) {
        return Zip::getRelativePath($relPath);
    }

    /**
     * Set default chapter target size.
     * Default is 250000 bytes, and minimum is 10240 bytes.
     *
     * @param $size segment size in bytes
     * @return void
     */
    function setSplitSize($size) {
        $this->splitDefaultSize = (int)$size;
        if ($size < 10240) {
            $this->splitDefaultSize = 10240; // Making the file smaller than 10k is not a good idea.
        }
    }

    /**
     * Get the chapter target size.
     *
     * @return $size
     */
    function getSplitSize() {
        return $this->splitDefaultSize;
    }

    /**
     * Remove all non essential html tags and entities.
     *
     * @return $string with the stripped entities.
     */
    function decodeHtmlEntities($string) {
        global $htmlEntities;

        $string = preg_replace('~\s*<br\s*/*\s*>\s*~i', "\n", $string);
        $string = preg_replace('~\s*</(p|div)\s*>\s*~i', "\n\n", $string);
        $string = preg_replace('~<[^>]*>~', '', $string);

        $string = strtr($string, $htmlEntities);

        $string = str_replace('&', '&amp;', $string);
        $string = str_replace('&amp;amp;', '&amp;', $string);
        $string = preg_replace('~&amp;(#x*[a-fA-F0-9]+;)~', '&\1', $string);
        $string = str_replace('<', '&lt;', $string);
        $string = str_replace('>', '&gt;', $string);

        return $string;
}

    /**
     * Simply remove all HTML tags, brute force and no finesse.
     *
     * @param $string html
     * @return String
     */
     function html2text($string) {
        return preg_replace('~<[^>]*>~', '', $string);
    }
	
	function getLog() {
        return $this->log->getLog();
    }
}
?>