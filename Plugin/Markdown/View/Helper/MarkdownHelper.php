<?php
/**
 * Markdown Helper
 *
 * @package app.View.Helper
 */
class MarkdownHelper extends AppHelper {

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
    $text = mb_convert_encoding($text, "HTML-ENTITIES", "UTF-8");
		$text = str_replace("^\r", "<br />", $text);
		$text = $this->parseEndnotes($text);
		$text = $this->parser->transform($text);
		$text = utf8_encode($text);
		return $text;
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
        //$text = preg_replace('/'.$match.'/', sprintf(' <small class="note">[<a class="note-backref" id="note-backref-%1$d" href="#note-%1$d">%1$d</a>]</small>', $endnoteNumber), $text, 1);
        $text = str_replace($match, sprintf(' <small class="note">[<a class="note-backref" id="note-backref-%1$d" href="#note-%1$d">%1$d</a>]</small>', $endnoteNumber), $text);
        if ($index === 0) {
          $text .= "\r\r<section class=\"section-notes\"><ol>\n";
        }
        $text .= sprintf('<li>%2$s <a class="note" id="note-%1$d" href="#note-backref-%1$d">&#8617;</a></li>'."\n", $endnoteNumber, $endnote);
      }
    }
	  return $text.'</ol></section>';
	}
}
?>