<?php
/**
 * PHP_UML
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 175 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-15 17:07:58 +0200 (jeu., 15 sept. 2011) $
 */

/**
 * Wrapper for an XMI document
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_XmiDocument
{
    /**
     * The XMI code of the XMI document
     * @var string
     */
    private $xmi;
    
    /**
     * Constructor
     * 
     * @param string $code XMI code as string 
     */
    public function __construct($code = '')
    {
        $this->xmi = $code;
    }
    
    /**
     * Return the code XMI as a string
     */
    public function dump()
    {
        return $this->xmi;
    }
    
    /** 
     * Load XMI from a file
     * 
     * @param string $filepath Filepath
     */
    public function load($filepath)
    {
        if (file_exists($filepath)) {
            $this->xmi = file_get_contents($filepath);
        } else {
            throw new PHP_UML_Exception('Could not open '.$filepath);
        }

        $xmlDom = new DomDocument;
        $xmlDom->loadXML($this->xmi);
        if (self::isVersion1($xmlDom)) {
            $this->xmi = PHP_UML_Output_ExporterXSL::convertTo2($this->xmi);
        }
    }
    
    /**
     * Return true if the document is in XMI version 1.x
     * 
     * @param DOMDocument $d DOM document
     * 
     * @return boolean
     */
    static function isVersion1(DOMDocument $d)
    {
        $version = $d->getElementsByTagName('XMI')->item(0)->getAttribute('xmi.version');
        if ($version=='')
            $version = $d->getElementsByTagName('XMI')->item(0)->getAttribute('xmi:version');
        return ($version<2);
    }
}
?>
