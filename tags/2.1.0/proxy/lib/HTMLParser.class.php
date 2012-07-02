<?php

/**
 * HTML Parser class for 'proxifying' HTML pages - i.e. converting all links etc.
 */
class HTMLParser {
	function __construct($proxy) {
		$this->proxy = $proxy;
		$this->url = $proxy->url;
		$this->log = new Logger();
	}
  /**
	 * Adds mouse tracking script.
	 */
  function includeTracking($html) {
  	$dom = new DOMUtil();
    $dom->formatOutput = true;
    $dom->preserveWhiteSpace = false;
    // hide warnings when parsing non valid (X)HTML pages
    @$dom->loadHTML($html); 
    // create (smt) record script
    $aux = $dom->createExternalScript(SMT_AUX);
    $rec = $dom->createExternalScript(SMT_RECORD);
    
    $cdata_smt = '
//<![CDATA[
  try {
    smt2.record();
  } catch(err) {}
//]]>
';

    $smt = $dom->createInlineScript($cdata_smt);
    // parse
    $head = $dom->getElementsByTagName('head');
    foreach ($head as $h) {
        $h->appendChild($aux);
        $h->appendChild($rec);
        $h->appendChild($smt);
    }
    /*
    // custom warn DIV
    $div = createDiv($dom, "test", "[SMT] on!");
    foreach ($dom->getElementsByTagName('body') as $b) {
      $b->insertBefore($div, $b->firstChild);
    }
    */
    // render parsed page
    $page = $dom->saveHTML();
    
    return $page;
  }
  
	/**
	 * Parses an HTML document.
	 */
	function parseHtml($html) {
		$matches = array();
		
		if ($this->proxy->opts['strip_script'] === TRUE) {
			preg_match_all('/<script.*?>.*?<\/script>|on(?:load|click|mouseover|mouseout|change)=".*?"/si', $html, $matches);
			foreach($matches[0] as $match) {
				$html = str_replace($match, '', $html);
			}
		}
		
		// Try to match href="link" and src="link"
		$matches = array();
		preg_match_all('/(action|href|src)=["\']?(.*?)["\']?(?:[\n ]|\/?>)/si', $html, $matches);
		
		for ($i=0; $i < sizeof($matches[0]); $i++) {
			$orig = $matches[0][$i];
			$url = trim($matches[2][$i]);
			
			if ($url == '/search') {
				$this->log->debug(sprintf('URL converted from [%s] to [%s]', $url, $this->convertUrl($url)));
			}
			
			$new = str_replace($url, $this->convertUrl($url), $orig);
			
			$html = str_replace($orig, $new, $html);
		}
		
		$matches = array();
		
		// Adds hidden input to all GET forms
		preg_match_all('/<form (?![^>]*?method=[\'"]?post[\'"]).*?>/si', $html, $matches);
		
		foreach ($matches[0] as $match) {
			$m = array();
			preg_match('/action=[\'"]?(.*?)[\'"]?[ >]/', $match, $m);
			$url = $m[1];
			
			if (strpos($url, INDEX_FILE_NAME . '?' . URL_PARAM_NAME) !== FALSE) {
				$url = substr($url, strrpos($url, URL_PARAM_NAME . '=') + (strlen(URL_PARAM_NAME) + 1));
			}

			if ($url != '') {
				$new = $match . '<input type="hidden" name="' . URL_PARAM_NAME . '" value="' . $url . '" />';
				$html = str_replace($match, $new, $html);
			}
		}
		
		// Extract and parse all CSS.
		// It may be more effective to just run the entire HTML through the parseCss routine,
		// but for now extract each CSS element and parse it separately.
		$matches = array();
		preg_match_all('/<style.*?>(.*?)<\/style>/is', $html, $matches);
		
		foreach($matches[1] as $match) {
			$orig = $match;
			$css = $this->parseCss($orig);
			
			$html = str_replace($orig, $css, $html);
		}
		
		return $html;	
	}
	
	/**
	 * Parses css for url links.
	 */ 
	function parseCss($css) {
		$matches = array();
		
		preg_match_all('/url\([\'"]?(.*?)[\'"]?\)|@import (?!url)["\']?(.*?)[\'"]?;/i', $css, $matches);
		
		for ($i=0; $i < sizeof($matches[0]); $i++) {
			$orig = $matches[0][$i];
			$url = trim($matches[1][$i]);
			if (empty($url)) {
				$url = trim($matches[2][$i]);
			}
			
			$new = str_replace($url, $this->convertUrl($url), $orig);
			$css = str_replace($orig, $new, $css);
		}
		
		return $css;
	}
	
	/**
	 * Converts a URL to one which will be handled by the proxy.
	 */
	function convertUrl($url) {
		$new = '';
		$original = $url;
		
		// Ignore email / javascript links -- we cannot convert them
		if (substr($url, 0, 7) == 'mailto:' || substr($url, 0, 11) == 'javascript:') {
			return $url;
		}
		
		$base = $this->url;
		// Strip off query string
		if (strpos($base, '?') !== FALSE) {
			$base = substr($base, 0, strpos($base, '?'));
		}
		$protocol = 'http';
		
		if (strpos($base, '://') !== FALSE) {
			$protocol = substr($base, 0, strpos($base, '://'));
			$base = substr($base, strpos($base, '://') + 3);
		}
		
		if (substr($url, 0, 1) == '/') {
			// URL is relative to server root -- append to base URL
			
			// Strip off server path
			if (strpos($base, '/') !== FALSE) {
				$base = substr($base, 0, strpos($base, '/'));
			}
			
			$new = $protocol . '://' . $base . $url;
		}
		elseif (substr($url, 0, 4) == 'http') {
			// URL is an absolute URL
			$new = $url;
		}
		else {
			// URL is relative to current URL, so check whether URL is currently a directory.
			
			if (strpos($base, '/') === FALSE) { // URLs such as example.com
				$new = $base . '/' . $url;
			}
			elseif (substr($base, -1) == '/') { // directory, such as example.com/dir/
				$new = $base . $url;
			}
			else { // file, such as example.com/dir/file.html
				$base = substr($base, 0, strrpos($base, '/'));
				$new = $base . '/' . $url;
			}
			
			$new = $protocol . '://' . $new;
		}
		
		if ($new != $url) {
			//$this->log->debug(sprintf('Converted [%s] to [%s]', $original, $new));
		}
		
		// Decode HTML Entities as characters are sometimes sent to the browser encoded -- particularly &amp;s
		return $this->proxy->local_url . '?' . URL_PARAM_NAME . '=' . base64_encode(html_entity_decode($new));
	}
}

?>
