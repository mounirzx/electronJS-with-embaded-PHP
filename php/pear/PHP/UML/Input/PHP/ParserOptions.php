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
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2011-09-15 17:07:58 +0200 (jeu., 15 sept. 2011) $
 */

/**
 * Defines the settings and options selected for a parsing
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage PHP
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * 
 */
class PHP_UML_Input_PHP_ParserOptions
{
    public $keepDocblocks = true;
    
    public $keepDollar = true;
    
    public $skipInternal = true;
    
    public $onlyApi = false;
    
    public $strict = false;
}
?>
