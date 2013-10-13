<?php
/**
 * Extended DOM utilities.
 * @autor Luis Leiva
 */
class DOMUtil extends DOMDocument {

  /**
   * Creates an external script element.
   * @param  string  $url  script URL
   * @return string        HTML element: <script type="text/javascript" src="$url"></script>
   */
  public function createExternalScript($url)
  {
    $js = $this->createElement('script');
    $js->setAttribute('type', 'text/javascript');
    $js->setAttribute('src', $url);

    return $js;
  }

  /**
   * Creates an inline script element.
   * @param  string $cdata  javascript code (should be wrapped in a CDATA section)
   * @return string         HTML element: <script type="text/javascript">$cdata</script>
   */
  public function createInlineScript($cdata)
  {
    $js = $this->createElement('script', $cdata);
    $js->setAttribute('type', 'text/javascript');

    return $js;
  }

  /**
   * Creates an external stylesheet element.
   * @param  string  $url  stylesheet URL
   * @return string        HTML element: <link type="text/css" rel="stylesheet" href="$url" />
   */
  public function createExternalStyleSheet($url)
  {
    $css = $this->createElement('link');
    $css->setAttribute('type', 'text/css');
    $css->setAttribute('rel', 'stylesheet');
    $css->setAttribute('href', $url);

    return $css;
  }

  /**
   * Creates an inline stylesheet element.
   * @param  string  $styles CSS styles
   * @return string          HTML element: <style type="text/css">$styles</style>
   */
  public function createInlineStyleSheet($styles)
  {
    $css = $this->createElement('style', $styles);
    $css->setAttribute('type', 'text/css');

    return $css;
  }

  /**
   * Creates a DIV element.
   * @param   string  $id       DIV id
   * @param   string  $content  DIV content (plain text) (default: none)
   * @return  string            HTML element: <div id="$id">$content</div>
   */
  public function createDiv($id, $content = "")
  {
    $div = $this->createElement('div', $content);
    $div->setAttribute('id', $id);

    return $div;
  }

  /**
   * Checks if a javascript file exists in the DOM,
   * by comparing the provided $source with the script's "src" attribute.
   * @param   string  $source   JavaScript source attribute
   * @return  boolean           TRUE on succes or FALSE on failure
   */
  public function scriptExists($source)
  {
    $scripts = $this->getElementsByTagName("script");
    foreach ($scripts as $script) {
      $src = $script->getAttribute("src");
      if (strpos($src, $scriptSrc) !== false) {
        return true;
      }
    }
    return false;
  }
}
?>