<?php
/**
 * PHP_UML (PHP_UML_Output_Eclipse_AbstractBuilder)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 176 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-19 00:03:11 +0200 (lun., 19 sept. 2011) $
 */

/**
 * Extended abstract class to generate a UML model that can be imported into Eclipse
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Eclipse
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_Eclipse_AbstractBuilder extends PHP_UML_Output_Xmi_AbstractBuilder
{
    /**
     * Factory method. Retrieves a proper implementation class,
     * matching the XMI version.
     *
     * @param float $version XMI version
     * 
     * @return PHP_UML_Output_Xmi_Builder An XMI builder object 
     */
    static function getInstance($version)
    {
        if ($version < 2)
            return new PHP_UML_Output_Xmi_BuilderImpl1($xmlEncoding);
        else
            return new PHP_UML_Output_Eclipse_BuilderImpl2($xmlEncoding);
    }
}
?>
