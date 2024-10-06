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
 * @version  SVN: $Revision: 97 $
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2009-01-04 21:57:08 +0100 (dim., 04 janv. 2009) $
 */

/**
 * A class for generating unique IDs, in two possible ways.
 * 
 * Almost every XMI element contains a UID. This class can generate both globally
 * unique IDs, and deterministic unique IDs (ie. the IDs are the same every time the
 * parser is run again). We need such deterministic IDs because of the PHP_UML test
 * suite, which would report errors otherwise (because of different IDs !)
 * You select which one by setting the boolean property $determinisic to true/false
 * 
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link     http://pear.php.net/package/PHP_UML
 */
class PHP_UML_SimpleUID
{
    const PREFIX = 'PHP_UML_'; 
    
    private static $counter = 0;

    /**
     * If true, the IDs contained in the XMI code will be very deterministic.
     * This is useful for running the PHP_UML test suite. Most of the time, you will
     * want to have more unique IDs, so leave that property to false (the generation
     * will then rely on uniqid()).
     *
     * @var boolean
     */
    public static $deterministic = false;
    
    /**
     * Accessor for getting the UID
     *
     * @return string
     */
    static function getUID()
    {
        if (self::$deterministic)
            return self::PREFIX.self::$counter++;
        else
            return uniqid();
    }
    
    /**
     * Reset the deterministic generator
     *
     */
    static function reset()
    {
        self::$counter = 0;
    }
}
?>
