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
 * The PHP parser.
 * It stores all the program elements of a PHP file in
 * a PHP_UML_Metamodel_Superstructure object.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage PHP
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Input_PHP_Parser
{
    const T_NS_SEPARATOR  = '\\';
    const T_NS_SEPARATOR2 = '::';    // for backward compat
        
    /**
     * Constructor
     *
     * @param PHP_UML_Metamodel_Superstructure &$struct   An empty instance of metamodel (superstructure)
     * @param bool                             $docblocks Set to true to interpret docblocks (@package, types)
     * @param bool                             $dollar    Set to true to keep the symbol $ in variable names
     * @param bool                             $internal  Set to true to skip elements tagged with @internal
     * @param bool                             $onlyApi   Set to true to include only the elements tagged with @api
     * @param bool                             $pureObj   Set to true to discard procedural code
     */
    abstract public function __construct(PHP_UML_Metamodel_Superstructure &$struct, $docblocks = true, $dollar = true, $internal = true, $onlyApi = false, $pureObj = false);


    /**
     * Parse a PHP file
     * 
     * @param string $fileBase Full path, or base directory
     * @param string $filePath Pathfile (relative to $fileBase)
     */
    abstract public function parse($fileBase, $filePath);

}
?>
