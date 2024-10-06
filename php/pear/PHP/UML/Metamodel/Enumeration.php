<?php
/**
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 136 $
 * @link       http://pear.php.net/package/PHP_UML
 * @since      $Date: 2009-12-10 01:35:58 +0100 (jeu., 10 déc. 2009) $
 *
 */

/**
 * Meta-Enumeration
 * Enumerates some basic PHP classifiers.
 *
 */
class PHP_UML_Metamodel_Enumeration
{
    /**
     * PHP datatypes
     *
     * @var array
     */
    static public $datatypes = array('mixed', 'array', 'string', 'int', 'integer',
        'bool', 'boolean', 'float', 'void', 'null', 'object', 'resource');

    static public $interfaces = array('Iterator', 'Countable');
    
    static public $classes = array('Exception');
    
    /**
     * Main file types. Used as stereotypes for qualifying the artifacts.
     *
     * @var array
     */
    static public $filetype = array('PHP File');
}
?>
