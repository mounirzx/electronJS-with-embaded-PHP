<?php
/**
 * PHP Parser and UML/XMI generator. Reverse-engineering tool.
 *
 * A package to scan PHP files and directories, and get an UML/XMI representation
 * of the parsed classes/packages.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 137 $
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2009-12-12 01:06:40 +0100 (sam., 12 déc. 2009) $
 */

/**
 * Maintains a stack of warning messages.
 * 
 * Worth being checked after a parsing, especially if several classes
 * share common names, in the PHP files
 * 
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link     http://pear.php.net/package/PHP_UML
 */
class PHP_UML_Warning
{
    /**
     * The $stack to read.
     * @var array
     */
    static public $stack = array();
    
    /**
     * Adds a warning message to the pile
     *
     * @param string $message The warning message to add
     */
    static public function add($message)
    {
        self::$stack[] = $message;
    }
    
    /**
     * Clears the pile
     */
    static public function clear()
    {
        self::$stack = array();
    }
}
?>
