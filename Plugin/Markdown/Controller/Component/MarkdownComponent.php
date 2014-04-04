<?php
App::uses('Component', 'Controller');

/**
 * MarkdownComponent
 *
 * @package		app.Controller.Component
 */
class MarkdownComponent extends Component {

/**
 * Reads entire file into a string.
 *
 * @param $file Path to the file
 * @return read data or FALSE on failure
 */
	public function getFile($file) {
		if (!file_exists($file)) {
			throw new MissingMarkdownException(array('file' => $file));
		}

		return file_get_contents($file);
	}

/**
 * Convert markdown to html
 *
 * @param  string $text Text in markdown format
 * @return string
 */
	public function transform($text) {
		if (!isset($this->parser)) {
			if (!class_exists('Markdown_Parser')) {
				App::import('Vendor', 'Markdown.MarkdownExtra/markdown');
			}
			$this->parser = new Markdown_Parser();
		}
		$text = str_replace("^\r", "<br />", $text);
		$text = $this->parseEndnotes($text);
		return $this->parser->transform($text);
	}

/**
 * Convert endnotes shortcode to html endnotes
 *
 * @param  string $text Text in markdown shortcode
 * @return string
 */
	private function parseEndnotes($text) {
    preg_match_all('(\[\^](.*?)\])', $text, $matches);
    if (isset($matches[0]) && !empty($matches[0])) {
      foreach ($matches[0] as $index=>$match) {
        $endnote = trim($matches[1][$index]);
        $endnoteNumber = $index+1;
        $text = str_replace($match, sprintf(' <small class="note">[<a class="note-backref" id="note-backref-%1$d" href="#note-%1$d">%1$d</a>]</small>', $endnoteNumber), $text);
        if ($index === 0) {
          $text .= "\r\rNotes\n--------\n";
        }
        $text .= sprintf('%1$d. %2$s <a class="note" id="note-%1$d" href="#note-backref-%1$d">&#8617;</a>'."\n", $endnoteNumber, $endnote);
      }
    }
	  return $text;
	}
}
?>