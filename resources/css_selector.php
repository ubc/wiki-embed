<?php
/**
 * The smallest and fastest CSS selectors tool for PHP DOMDocument
 * Basic selectors only (no CSS3 yet)
 * The more selectors processed - the slower it runs, so...
 */
class DOMCSS {
  /** @var array **/
  private static $c = array(
    /// Pre-processing:
    '/["\']/' => '',                                     // no quotes please
    '/\s*([\[\]>+,])\s*/' => '\1',                       // no WS around []>+,
    '/\s{2,}|\n/' => ' ',                                // no duplicate WS
    '/(?:^|,)\./' => '*.',                               // .class shorthand
    '/(?:^|,)#/' => '*#',                                // #id shorthand
    '/:(link|visited|active|hover|focus)/' => '.\1',     // not applicable
    '/\[(.*)]/e' => '"[".str_replace(".","`","\1")."]"', // dots inside [] to `
    /// CSS 2 XPath conversion:
    '/,/' => '|',                                               // E,F
    '/>/' => '/',                                               // E>F
    '/ /' => '//',                                              // E F
    '/\+/' => '/following-sibling::*[1]/self::',                // E+F
    '/([a-z]+)\:first\-child/' => '*[1]/self::\1',              // E:first-child
    '/\[([a-z][0-9_a-z]*)\]/' => '[@\1]',                       // E[attr]
    '/\[([a-z][0-9_a-z]*)=([^"\'\]]+)\]/' => '[@\1="\2"]',      // E[attr=v]
    '/\[([a-z][0-9_a-z]*)~=([^"\'\]]+?)\]/' =>                  // E[attr~=v]
      '[contains(concat(" ",@\1," "),concat(" ","\2", " "))]',   
    '/\[[a-z][0-9_a-z]*\|=([^"\'\]]+?)\]/' =>                   // E[attr|=v]
      '[@\1="\2" or starts-with(@\1,concat("\2","-"))]',         
    '/\.([a-z][0-9_a-z]*)/' =>                                  // E.class
      '[contains(concat(" ",@class," "),concat(" ","\1"," "))]',
    '/#([a-z][0-9_a-z]*)/' => '[@id="\1"]',                     // E#id
    '/`/' => '.'                                                // ` back to .
  );
  /** @var DOMDocument **/
  private $document;
  /** Creates new DOMCSS **/
  public function __construct($document) {
    $this->document = $document;
  }
  /**
   * Performs CSS query on DOMDocument
   * @param string $q
   * @return DOMNodeList
   */
  public function query($q) {
    $x = new DOMXPath($this->document);
    foreach (self::$c as $search => $replace)
      $q = preg_replace($search . 'i', $replace, $q);
    return $x->query("//$q");
  }
}
?>