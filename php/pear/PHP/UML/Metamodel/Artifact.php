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
 * @version    SVN: $Revision: 136 $
 * @link       http://pear.php.net/package/PHP_UML
 * @since      $Date: 2009-12-10 01:35:58 +0100 (jeu., 10 déc. 2009) $
 */

/**
 * Meta-File
 *
 */
class PHP_UML_Metamodel_Artifact extends PHP_UML_Metamodel_NamedElement
{
    /**
     * Array of classes/interfaces declarations contained in that file
     *
     * @var array
     */
    public $manifested = array();
    
    public $package;
}
?>
