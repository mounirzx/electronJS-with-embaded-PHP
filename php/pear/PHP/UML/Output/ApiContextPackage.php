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
 * @version  SVN: $Revision: 136 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2009-12-10 01:35:58 +0100 (jeu., 10 déc. 2009) $
 */

/**
 * This class is a contextual wrapper around a "current" package being
 * processed. It is used during the traversal of all the packages of
 * a model, especially to store temporarily a complete view about all
 * the relationships between the contained types.
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_ApiContextPackage
{
    /**
     * Filepath to the current package
     * @var string
     */
    public $dir = '';
    
    /**
     * Relative filepath to go to the top package
     * @var string
     */
    public $rpt = '';
    
    /**
     * Classes of the package
     * @var array
     */
    public $classes;
    
    /**
     * Interfaces of the package
     * @var array
     */
    public $interfaces;
    
    /**
     * Datatpyes of the package
     * @var array
     */
    public $datatypes;

    public $allImplemented = array();
    public $allImplementing = array();
    public $allInherited = array();
    public $allInheriting = array();
}
?>
