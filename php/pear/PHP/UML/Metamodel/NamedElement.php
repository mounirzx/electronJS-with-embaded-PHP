<?php
/**
 * PHP_UML (MOF-like metamodel of language PHP)
 *
 * PHP version 5
 *
 * This is a PHP metamodel, inspired by the MOF Metamodel.
 * It defines the various elements of the PHP language that have been parsed
 * by the parser, and that we want to reverse-engineer.
 * 
 * For more information on language metamodels, see :
 * - The OMG website, and its MOF standard
 * - Ceejay, a Java/C++ Code Generation Metamodel for ULF-Ware (M. Piefel)
 * 
 * @category   PHP
 * @package    PHP_UML
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 138 $
 * @link       http://pear.php.net/package/PHP_UML
 * @link       http://www.omg.org/mof/
 * @since      $Date: 2009-12-13 04:23:11 +0100 (dim., 13 déc. 2009) $
 */

/**
 * The NamedElement class
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Metamodel_NamedElement
{
    /**
     * Unique identifier
     *
     * @var string
     */
    public $id;
    
    /**
     * Name
     *
     * @var string
     */
    public $name;
    
    /**
     * Reference to a "documention" stereotype
     *
     * @var PHP_UML_Metamodel_Stereotype
     */
    public $description;
}
?>
