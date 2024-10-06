<?php
/**
 * Get the Compatibility info for a list of files into a directory
 * Output is produced by a custom renderer (html2).
 * Rather than write your own stylesheet, you may use the default one, and
 * change some colors on the fly.
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsedir_tohtml.php,v 1.2 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0b4 (2008-06-18)
 * @ignore
 */

require_once 'PHP/CompatInfo.php';
require_once 'PHP/CompatInfo/Renderer.php';
require_once 'PHP/CompatInfo/Renderer/Html.php';

/**
 * Custom html layout
 *
 * @ignore
 */
class PHP_CompatInfo_Renderer_Html2 extends PHP_CompatInfo_Renderer_Html
{
    /**
     * Html2 Renderer Class constructor
     *
     * @param object &$parser Instance of the parser (model of MVC pattern)
     * @param array  $conf    A hash containing any additional configuration
     *
     * @access public
     * @since  version 1.8.0b4 (2008-06-18)
     */
    function PHP_CompatInfo_Renderer_Html2(&$parser, $conf)
    {
        parent::__construct($parser, $conf);
        // use default stylesheet (pci.css)
        $this->setStyleSheet();  // Important: do not remove it
    }

    /**
     * Returns HTML code of parsing result
     *
     * @param object $obj instance of HTML_Table
     *
     * @access public
     * @return string
     */
    function toHtml($obj)
    {
        $styles = $this->getStyleSheet(3, array(&$this, '_getStyles'));

        $body = $obj->toHtml();

        $html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="author" content="Laurent Laville" />
<title>my PCI widget</title>
<link rel="stylesheet" type="text/css" href="yoursite.css" />
<link rel="stylesheet" type="text/css" href="$styles" />
</head>
<body>

<div id="header">
<h1>Laurent-Laville.org</h1>
</div>

<div id="footer">
</div>

<div id="contents">

<div class="outer">
<div class="inner">
$body
</div>
</div>

</div>

</body>
</html>
HTML;
        return $html;
    }

    /**
     * User callback to modify stylesheet object
     *
     * @param object $css Instance of HTML_CSS
     * @return string
     * @access private
     */
    function _getStyles($css)
    {
        $stylesheet = 'pciskin.css';
        $css->setStyle('.inner', 'height', '12em');
        $css->setStyle('.inner .even', 'background-color', '#449922');
        $css->setStyle('.inner .even', 'color', '#FFFFFF');
        $css->setStyle('.outer thead td', 'background-color', '#006600');
        $css->setStyle('.outer tfoot td', 'background-color', '#006600');
        $css->toFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . $stylesheet);
        return $stylesheet;
    }
}

/*
  Display parsing result of directory '$source' with a website integration
  look and feel.
 */
$driverType    = 'html2';
$driverOptions = array('args' => array('output-level' => 18));

$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);

$source = 'C:\wamp\tmp\Services_W3C_CSSValidator-0.1.0';
$compatInfo->parseData($source);
?>