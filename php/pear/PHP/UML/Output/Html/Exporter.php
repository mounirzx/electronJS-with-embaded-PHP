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
 * @version  SVN: $Revision: 178 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-19 03:08:06 +0200 (lun., 19 sept. 2011) $
 */

/**
 * Implementation of an exporter to generate an HTML website (Javadoc look and
 * feel)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Html
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Html_Exporter extends PHP_UML_Output_ExporterXSL
{
    function getXslFilepath()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.self::STARTING_TPL;
    }
}
?>
