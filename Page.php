<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997 - 2003 The PHP Group                              |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Adam Daniel <adaniel1@eesus.jnj.com>                        |
// |          Klaus Guenther <klaus@capitalfocus.org>                     |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'PEAR.php';
require_once 'HTML/Common.php';
// HTML/Page/Doctypes.php is required in _getDoctype()

/**
 * Base class for XHTML pages
 *
 * This class handles the details for creating a properly constructed XHTML page.
 * Page caching, stylesheets, client side script, and Meta tags can be
 * managed using this class.
 * 
 * The body may be a string, object, or array of objects or strings. Objects with
 * toHtml() and toString() methods are supported.
 * 
 *
 * XHTML Examples:
 * ---------------
 *
 * Simplest example:
 * -----------------
 * <code>
 * // the default doctype is XHTML 1.0 Transitional
 * // All doctypes and defaults are set in HTML/Page/Doctypes.php
 * $p = new HTML_Page();
 *
 * //add some content
 * $p->addBodyContent("<p>some text</p>");
 *
 * // print to browser
 * $p->display();
 * </code>
 * 
 * Complex XHTML example:
 * ----------------------
 * <code>
 * // The initializing code can also be in in the form of an HTML
 * // attr="value" string.
 * // Possible attributes are: charset, lineend, tab, doctype, language and cache
 * 
 * $p = new HTML_Page(array (
 *
 *                          // Sets the charset encoding
 *                          'charset'  => 'utf-8',
 *
 *                          // Sets the line end character
 *                          // unix (\n) is default
 *                          'lineend'  => 'unix',
 *
 *                          // Sets the tab string for autoindent
 *                          // tab (\t) is default
 *                          'tab'  => '  ',
 *
 *                          // This is where you define the doctype
 *                          'doctype'  => "XHTML 1.0 Strict",
 *
 *                          // Global page language setting
 *                          'language' => 'en',
 *
 *                          // If cache is set to true, the browser may
 *                          // cache the output. Else 
 *                          'cache'    => 'false'
 *                          ));
 *
 * // Here we go
 *
 * // Set the page title
 * $p->setTitle("My page");
 * 
 * // Add optional meta data
 * $p->addMetaData("author", "My Name");
 * 
 * // Put something into the body
 * $p->addBodyContent = "<p>some text</p>";
 *
 * // If at some point you want to clear the page content
 * // and output an error message, you can easily do that
 * // See the source for {@link toHtml} and {@link _getDoctype}
 * // for more details
 * if ($error) {
 *     $p->setTitle("Error!");
 *     $p->setBodyContent("<p>oops, we have an error: $error</p>");
 *     $p->display();
 *     die;
 * } // end error handling
 *
 * // print to browser
 * $p->display();
 * </code>
 * 
 * Simple XHTML declaration example:
 * <code>
 * $p = new HTML_Page();
 * // An XHTML compliant page (with title) is automatically generated
 *
 * // This overrides the XHTML 1.0 Transitional default
 * $p->setDoctype('xhtml');
 * 
 * // Put some content in here
 * $p->addBodyContent("<p>some text</p>");
 *
 * // print to browser
 * $p->display();
 * </code>
 * 
 *
 * HTML examples:
 * --------------
 *
 * HTML 4.01 example:
 * ------------------
 * <code>
 * $p = new HTML_Page('doctype="HTML 4.01 Strict"');
 * $p->addBodyContent = "<p>some text</p>";
 * $p->display();
 * </code>
 * 
 * nuke doctype declaration:
 * -------------------------
 * <code>
 * $p = new HTML_Page('doctype="none"');
 * $p->addBodyContent = "<p>some text</p>";
 * $p->display();
 * </code>
 * 
 * @author       Adam Daniel <adaniel1@eesus.jnj.com>
 * @author       Klaus Guenther <klaus@capitalfocus.org>
 * @version      2.0.0b1
 * @since        PHP 4.0.3pl1
 */
class HTML_Page extends HTML_Common {
    
    /**
     * Contains the content of the &lt;body&gt; tag.
     * 
     * @var     array
     * @access  private
     */
    var $_body = array();
    
    /**
     * Controls caching of the page
     * 
     * @var     bool
     * @access  private
     */
    var $_cache = false;
    
    /**
     * Contains the character encoding string
     * 
     * @var     string
     * @access  private
     */
    var $_charset = 'utf-8';
    
    /**
     * Contains the !DOCTYPE definition
     * 
     * @var array
     * @access private
     */
    var $_doctype = array('type'=>'xhtml','version'=>'1.0','variant'=>'transitional');
    
    /**
     * Contains the page language setting
     * 
     * @var     string
     * @access  private
     */
    var $_language = 'en';
    
    /**
     * Array of meta tags
     * 
     * @var     array
     * @access  private
     */
    var $_metaTags = array( 'standard' => array ( 'Generator' => 'PEAR HTML_Page' ) );
    
    /**
     * Array of linked scripts
     * 
     * @var  array
     * @access   private
     */
    var $_scripts = array();
    
    /**
     * Array of linked scripts
     * 
     * @var     array
     * @access  private
     */
    var $_simple = false;
    
    /**
     * Array of included style declarations
     * 
     * @var     array
     * @access  private
     */
    var $_style = array();
    
    /**
     * Array of linked style sheets
     * 
     * @var     array
     * @access  private
     */
    var $_styleSheets = array();
    
    /**
     * HTML page title
     * 
     * @var     string
     * @access  private
     */
    var $_title = '';
    
    /**
     * Class constructor
     * Possible attributes are:
     * - general options:
     *     - "lineend" => "unix|win|mac" (Sets line ending style; defaults to unix.)
     *     - "tab" => string (Sets line ending style; defaults to \t.)
     *     - "cache"   => "false|true"
     *     - "charset" => charset string (Sets charset encoding; defaults to utf-8)
     * - XHTML specific:
     *     - "doctype"  => mixed (Sets XHTML doctype; defaults to XHTML 1.0 Transitional.)
     *     - "language" => two letter language designation. (Defines global document language; defaults to "en".)
     * 
     * @param   mixed   $attributes     Associative array of table tag attributes
     *                                  or HTML attributes name="value" pairs
     * @access  public
     */
    function HTML_Page($attributes = "")
    {
        $commonVersion = 1.7;
        if (HTML_Common::apiVersion() < $commonVersion) {
            return PEAR::raiseError("HTML_Page version " . $this->apiVersion() . " requires " .
                "HTML_Common version $commonVersion or greater.", 0, PEAR_ERROR_TRIGGER);
        }
        
        if ($attributes) {
            $attributes = $this->_parseAttributes($attributes);
        }
        
        if (isset($attributes['lineend'])) {
            $this->setLineEnd($attributes['lineend']);
        }
        
        if (isset($attributes['charset'])) {
            $this->setCharset($attributes['charset']);
        }
        
        if (isset($attributes['doctype'])){
            if ($attributes['doctype'] == 'none') {
                $this->_simple = true;
            } elseif ($attributes['doctype']) {
                $this->setDoctype($attributes['doctype']);
            }
        }
        
        if (isset($attributes['language'])) {
            $this->setLang($attributes['language']);
        }
        
        if (isset($attributes['cache'])) {
            $this->setCache($attributes['cache']);
        }
        
    }
    
    /**
     * Generates the HTML string for the &lt;body&lt; tag
     * 
     * @access  private
     * @return  string
     */
    function _generateBody()
    {
        
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();
        
        // If body attributes exist, add them to the body tag.
        // Depreciated because of CSS
        $strAttr = $this->_getAttrString($this->_attributes);
        
        if ($strAttr) {
            $strHtml = "<body $strAttr>" . $lnEnd;
        } else {
            $strHtml = '<body>' . $lnEnd;
        }
        
        // Allow for mixed content in the body array
        // Iterate through the array and process each element
        foreach ($this->_body as $element) {
            if (is_object($element)) {
                if (is_subclass_of($element, "html_common")) {
                    $element->setTab($tab);
                    $element->setTabOffset(1);
                    $element->setLineEnd($lnEnd);
                }
                if (method_exists($element, "toHtml")) {
                    $strHtml .= $element->toHtml() . $lnEnd;
                } elseif (method_exists($element, "toString")) {
                    $strHtml .= $element->toString() . $lnEnd;
                }
            } elseif (is_array($element)) {
                foreach ($element as $level2) {
                    if (is_subclass_of($level2, "html_common")) {
                        $level2->setTabOffset(1);
                        $level2->setTab($tab);
                        $level2->setLineEnd($lnEnd);
                    }
                    if (is_object($level2)) {
                        if (method_exists($level2, "toHtml")) {
                            $strHtml .= $level2->toHtml() . $lnEnd;
                        } elseif (method_exists($level2, "toString")) {
                            $strHtml .= $level2->toString() . $lnEnd;
                        }
                    } else {
                        $strHtml .= $tab . $level2 . $lnEnd;
                    }
                }
            } else {
                $strHtml .= $tab . $element . $lnEnd;
            }
        }
        
        // Close tag
        $strHtml .= '</body>' . $lnEnd;
        
        // Let's roll!
        return $strHtml;
    } // end func _generateHead
    
    /**
     * Generates the HTML string for the &lt;head&lt; tag
     * 
     * @return string
     * @access private
     */
    function _generateHead()
    {
        
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();
        
        $strHtml  = '<head>' . $lnEnd;
        $strHtml .= $tab . '<title>' . $this->getTitle() . '</title>' . $lnEnd;
        
        // Generate META tags
        foreach ($this->_metaTags as $type => $tag) {
            foreach ($tag as $name => $content) {
                if ($type == 'http-equiv') {
                    $strHtml .= $tab . "<meta http-equiv=\"$name\" content=\"$content\" />" . $lnEnd;
                } elseif ($type == 'standard') {
                    $strHtml .= $tab . "<meta name=\"$name\" content=\"$content\" />" . $lnEnd;
                }
            }
        }
        
        // Generate stylesheet links
        for($intCounter=0; $intCounter<count($this->_styleSheets); $intCounter++) {
            $strStyleSheet = $this->_styleSheets[$intCounter];
            $strHtml .= $tab . "<link rel=\"stylesheet\" href=\"$strStyleSheet\" type=\"text/css\" />" . $lnEnd;
        }
        
        // Generate stylesheet declarations
        foreach ($this->_style as $type => $content) {
            $strHtml .= $tab . '<style type="' . $type . '">' . $lnEnd;
            $strHtml .= $tab . $tab . '<!--' . $lnEnd;
            if (is_object($content)) {
                
                // first let's propagate line endings and tabs for other HTML_Common-based objects
                if (is_subclass_of($content, "html_common")) {
                    $content->setTab($tab);
                    $content->setTabOffset(3);
                    $content->setLineEnd($lnEnd);
                }
                
                // now let's get a string from the object
                if (method_exists($content, "toString")) {
                    $strHtml .= $content->toString() . $lnEnd;
                } else {
                    return PEAR::raiseError('Error: Body content object does not support  method toString().',
                    0,PEAR_ERROR_TRIGGER);
                }
                
            } else {
                $strHtml .= $content . $lnEnd;
            }
            $strHtml .= $tab . $spacer . "-->" . $lnEnd;
            $strHtml .= $tab . "</style>" . $lnEnd;
        }
        
        // Generate script file links
        for($intCounter=0; $intCounter<count($this->_scripts); $intCounter++) {
            $strType = $this->_scripts[$intCounter]["type"];
            $strSrc = $this->_scripts[$intCounter]["src"];
            $strHtml .= $tab . "<script type=\"$strType\" src=\"$strSrc\"></script>" . $lnEnd;
        }
        
        // Close tag
        $strHtml .=  '</head>' . $lnEnd;
        
        // Let's roll!
        return $strHtml;
    } // end func _generateHead
    
    /**
     * Returns the doctype declaration
     *
     * @return string
     * @access private
     */
    function _getDoctype()
    {
        require('HTML/Page/Doctypes.php');
        
        $type = $this->_doctype['type'];
        $version = $this->_doctype['version'];
        $variant = $this->_doctype['variant'];
        
        $strDoctype = '';
        
        if ($variant != '') {
            if (isset($doctype[$type][$version][$variant][0])) {
                foreach ( $doctype[$type][$version][$variant] as $string) {
                    $strDoctype .= $string.$this->_getLineEnd();
                }
            }
        } elseif ($version != '') {
            if (isset($doctype[$type][$version][0])) {
                foreach ( $doctype[$type][$version] as $string) {
                    $strDoctype .= $string.$this->_getLineEnd();
                }
            } else {
                if (isset($default[$type][$version][0])) {
                    $this->_doctype = $this->_parseDoctypeString($default[$type][$version][0]);
                    $strDoctype = $this->_getDoctype();
                }
            }
        } elseif ($type != '') {
            if (isset($default[$type][0])){
                $this->_doctype = $this->_parseDoctypeString($default[$type][0]);
                $strDoctype = $this->_getDoctype();
            }
        } else {
            $this->_doctype = $this->_parseDoctypeString($default['default'][0]);
            $strDoctype = $this->_getDoctype();
        }
        
        if ($strDoctype) {
            return $strDoctype;
        } else {
            return PEAR::raiseError('Error: "'.$this->getDoctypeString().'" is an unsupported or illegal document type.',
                                    0,PEAR_ERROR_TRIGGER);
        }
        
    } // end func _getDoctype
    
    /**
     * Parses a doctype declaration like "XHTML 1.0 Strict" to an array
     *
     * @param   string  $string     The string to be parsed
     * @return string
     * @access private
     */
    function _parseDoctypeString($string)
    {
        $split = explode(' ',strtolower($string));
        $elements = count($split);
        
        $array = array('type'=>$split[0],'version'=>$split[1],'variant'=>$split[2]);
        
        return $array;
    } // end func _parseDoctypeString
    
    /**
     * Sets the content of the &lt;body&gt; tag. If content already exists,
     * the new content is appended.
     * If you wish to overwrite whatever is in the body, use {@link setBody};
     * {@link unsetBody} completely empties the body without inserting new content.
     * It is possible to add objects, strings or an array of strings and/or objects
     * Objects must have a toString method.
     * 
     * @param mixed $content New &lt;body&gt; tag content.
     * @access public
     */
    function addBodyContent($content)
    {
        $this->_body[] =& $content;
    } // end addBodyContent
    
    /**
     * Adds a linked script to the page
     * 
     * @param    string  $url        URL to the linked style sheet
     * @param    string  $type       Type of script. Defaults to 'text/javascript'
     * @access   public
     */
    function addScript($url, $type="text/javascript")
    {
        $this->_scripts[] = array("type"=>$type, "src"=>$url);
    } // end func addScript
    
    /**
     * Adds a linked stylesheet to the page
     * 
     * @param    string  $url    URL to the linked style sheet
     * @access   public
     * @return   void
     */
    function addStyleSheet($url)
    {
        $this->_styleSheets[] = $url;
    } // end func addStyleSheet
    
    /**
     * Adds a stylesheet declaration to the page.
     * Content can be a string or an object with a toString method.
     * Defaults to text/css.
     * 
     * @access   public
     * @param    string  $type      Type of stylesheet (e.g., text/css)
     * @param    mixed  &$content   Style declarations
     * @return   void
     */
    function addStyleDeclaration(&$content, $type = 'text/css')
    {
        $this->_style[$type] =& $content;
    } // end func addStyleDeclaration
    
    /**
     * Returns the current API version
     * 
     * @access   public
     * @returns  double
     */
    function apiVersion()
    {
        return 2.0;
    } // end func apiVersion
    
    /**
     * Defines if the document should be cached by the browser. Defaults to false.
     * 
     * @param string $cache Options are currently 'true' or 'false'. Defaults to 'false'.
     * @access public
     */
    function getCharset()
    {
        return $this->_charset;
    } // end setCache
    
    /**
     * Returns the document type string
     *
     * @access private
     * @return string
     */
    function getDoctypeString()
    {
        $strDoctype = strtoupper($this->_doctype['type']);
        $strDoctype .= ' '.ucfirst(strtolower($this->_doctype['version']));
        if ($this->_doctype['variant']) {
            $strDoctype .= ' ' . ucfirst(strtolower($this->_doctype['variant']));
        }
        return trim($strDoctype);
    } // end func getDoctypeString
    
    /**
     * Returns the document language.
     * 
     * @return string
     * @access public
     */
    function getLang ()
    {
        return $this->_language;
    } // end func getLang
    
    /**
     * Return the title of the page.
     * 
     * @returns  string
     * @access   public
     */
    function getTitle()
    {
        if (!$this->_title){
            if ($this->_simple) {
                return 'New Page';
            } else {
                return 'New '. $this->getDoctypeString() . ' Compliant Page';
            }
        } else {
            return $this->_title;
        }
    } // end func getTitle
    
    /**
     * Sets the content of the &lt;body&gt; tag. If content exists, it is overwritten.
     * If you wish to use a "safe" version, use {@link addBodyContent}
     * Objects must have a toString method.
     * 
     * @param mixed &$content New &lt;body&gt; tag content. May be an object.
     * @access public
     */
    function setBody(&$content)
    {
        $this->unsetBody();
        $this->_body[] =& $content;
    } // end setBody
    
    /**
     * Unsets the content of the &lt;body&gt; tag.
     * 
     * @access public
     */
    function unsetBody()
    {
        $this->_body = '';
    } // end unsetBody
        
    /**
     * Defines if the document should be cached by the browser. Defaults to false.
     * 
     * @param string $cache Options are currently 'true' or 'false'. Defaults to 'false'.
     * @access public
     */
    function setCache($cache = 'false')
    {
        if ($cache == 'true'){
            $this->_cache = true;
        } else {
            $this->_cache = false;
        }
    } // end setCache
    
    /**
     * Defines if the document should be cached by the browser. Defaults to false.
     * 
     * @param string $cache Options are currently 'true' or 'false'. Defaults to 'false'.
     * @access public
     */
    function setCharset($type = 'utf-8')
    {
        $this->_charset = $type;
    } // end setCache
    
    /**
     * Sets or alters the XHTML !DOCTYPE declaration. Can be set to "strict",
     * "transitional" or "frameset". Defaults to "transitional". This must come
     * _after_ declaring the character encoding with {@link setCharset} or directly
     * when the class is initiated {@link HTML_Page}.
     * 
     * @param mixed $type   String containing a document type. Defaults to "XHTML 1.0 Transitional"
     * @access public
     */
    function setDoctype($type = "XHTML 1.0 Transitional")
    {
        $this->_doctype = $this->_parseDoctypeString($type);
    } // end func setDoctype
    
    /**
     * Sets the global document language declaration. Default is English.
     * 
     * @access public
     * @param string $lang Two-letter language designation.
     */
    function setLang($lang = "en")
    {
        $this->_language = strtolower($lang);
    } // end setLang
    
    /**
     * Sets or alters a meta tag.
     * 
     * @param string  $name     Value of name or http-equiv tag
     * @param string  $content  Value of the content tag
     * @param bool    $http_equiv     META type "http-equiv" defaults to NULL
     * @return void
     * @access public
     */
    function setMetaData($name, $content, $http_equiv = false)
    {
        if ($http_equiv == true) {
            $this->_metaTags['http-equiv'][$name] = $content;
        } else {
            $this->_metaTags['standard'][$name] = $content;
        }
    } // end func setMetaData
    
    /**
     * Easily sets or alters a refresh meta tag. 
     * If no $url is passed, "self" is presupposed, and the appropriate URL
     * will be automatically generated.
     * 
     * @param string  $time    Time till refresh (in seconds)
     * @param string  $url     Absolute URL or "self"
     * @param bool    $https   If $url = self, this allows for the https protocol defaults to NULL
     * @return void
     * @access public
     */
    function setMetaRefresh($time, $url = 'self', $https = false)
    {
        if ($url == 'self') {
            if ($https) { 
                $protocol = 'https://';
            } else {
                $protocol = 'http://';
            }
            $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        $this->setMetaData("Refresh", "$time; url=$url", true);
    } // end func setMetaRefresh
    
    /**
     * Sets the title of the page
     * 
     * @param    string    $title
     * @access   public
     */
    function setTitle($title)
    {
        $this->_title = $title;
    } // end func setTitle
    
    /**
     * Generates and returns the complete page as a string.
     * 
     * @return string
     * @access private
     */
    function toHTML()
    {
        
        // get line endings
        $lnEnd = $this->_getLineEnd();
        
        // get the doctype declaration
        $strDoctype = $this->_getDoctype();
        
        //
        if ($this->_simple) {
            $strHtml = '<html>' . $lnEnd;
        } elseif ($this->_doctype['type'] == 'xhtml') {
            $strHtml  = '<?xml version="1.0" encoding="' . $this->_charset . '"?>' . $lnEnd;
            $strHtml .= $strDoctype . $lnEnd;
            $strHtml .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$this->_language.'">';
        } else {
            $strHtml  = $strDoctype . $lnEnd;
            $strHtml .= '<html>' . $lnEnd;
        }
        $strHtml .= $this->_generateHead();
        $strHtml .= $this->_generateBody();
        $strHtml .= '</html>';
        return $strHtml;
    } // end func toHtml

    /**
     * Outputs the HTML content to the screen.
     * 
     * @access    public
     */
    function display()
    {
        if(! $this->_cache) {
            header("Expires: Tue, 1 Jan 1980 12:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }
        
        // set character encoding
        header("Content-Type: text/html; charset=" . $this->_charset);
        
        $strHtml = $this->toHTML();
        print $strHtml;
    } // end func display
    
}
?>