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
 * Implementation of the class renderer
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Php
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Php_DocClass extends PHP_UML_Output_Php_DocClassifier
{
    protected function getTypeName()
    {
        return 'class';
    }

    protected function getStyleName()
    {
        return 'class';
    }

    protected function getPrefix()
    {
        return self::CLASS_PREFIX;
    }

    protected function getCurrentElement($i)
    {
        return $this->getContextPackage()->classes[$i];
    }

    protected function getNextElement($i)
    {
        return $i+1<count($this->getContextPackage()->classes) ? $this->getContextPackage()->classes[$i+1] : null;
    }

    protected function getPreviousElement($i)
    {
        return $i>0 ? $this->getContextPackage()->classes[$i-1] : null;
    }
}
?>
