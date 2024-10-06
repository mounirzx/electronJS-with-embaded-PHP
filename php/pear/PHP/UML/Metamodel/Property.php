<?php
/**
 * PHP_UML (MOF-like metamodel of language PHP)
 *
 * PHP version 5
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 133 $
 * @link       http://pear.php.net/package/PHP_UML
 * @link       http://www.omg.org/mof/
 * @since      $Date: 2009-11-15 23:46:39 +0100 (dim., 15 nov. 2009) $
 *
 */

class PHP_UML_Metamodel_Property extends PHP_UML_Metamodel_TypedElement
{
    public $isReadOnly;
    public $isInstantiable;
    public $visibility;
    public $default;
    public $class;
    public $package;
    public $file;
}
?>
