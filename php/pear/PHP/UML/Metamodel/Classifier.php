<?php
/**
 * PHP_UML (MOF-like metamodel of language PHP)
 *
 * PHP version 5
 * 
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 138 $
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.omg.org/mof/
 * @since    $Date: 2009-12-13 04:23:11 +0100 (dim., 13 déc. 2009) $
 *
 */

/**
 * Metaclass for classifier
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 */
abstract class PHP_UML_Metamodel_Classifier extends PHP_UML_Metamodel_Type
{
    public $superClass = array();
    public $ownedOperation = array();
    public $file;
    public $package;
    public $isAbstract;
    public $isReadOnly;
}
?>
