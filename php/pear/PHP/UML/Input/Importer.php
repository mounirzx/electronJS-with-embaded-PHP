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
 * Defines a way to import external data, and to get the result as a UML model
 * (as a superstructure) 
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
interface PHP_UML_Input_Importer
{
    /**
     * Get the resulting, final model
     * 
     * @return PHP_UML_Metamodel_Superstructure
     */
    public function getModel();
    
    /**
     * Import data
     * 
     */
    public function import();
}
?>
