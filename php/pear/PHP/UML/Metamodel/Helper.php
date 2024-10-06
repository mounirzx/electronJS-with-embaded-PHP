<?php
/**
 * PHP_UML (PHP/MOF program elements classes)
 *
 * PHP version 5
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version    SVN: $Revision: 175 $
 * @link       http://pear.php.net/package/PHP_UML
 * @link       http://www.omg.org/mof/
 * @since      $Date: 2011-09-15 17:07:58 +0200 (jeu., 15 sept. 2011) $
 *
 */

/**
 * Helper class to deal with Metamodel elements.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Metamodel_Helper
{
    const META_INTERFACE = 'PHP_UML_Metamodel_Interface';
    const META_CLASS     = 'PHP_UML_Metamodel_Class';
    const META_DATATYPE  = 'PHP_UML_Metamodel_Datatype';
    const META_OPERATION = 'PHP_UML_Metamodel_Operation';
    const META_PROPERTY  = 'PHP_UML_Metamodel_Property';

    const PHP_PROFILE_NAME        = 'php';
    const PHP_STEREOTYPE_DOCBLOCK = 'docblock';
    

    /**
     * Deletes all empty packages
     * 
     * @param PHP_UML_Metamodel_Package &$ns Top package
     */
    static public function deleteEmptyPackages(PHP_UML_Metamodel_Package &$ns)
    {
        if (!is_null($ns->nestedPackage)) {
            foreach ($ns->nestedPackage as $key => &$pkg) {
                if (self::isEmpty($pkg)) {
                    unset($ns->nestedPackage[$key]);
                } else {
                    if (self::deleteEmptyPackages($pkg)) {
                        unset($ns->nestedPackage[$key]);
                    }
                }
            }
            if (self::isEmpty($ns)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a package is empty
     * 
     * @param PHP_UML_Metamodel_Package $p Top package
     */
    static private function isEmpty(PHP_UML_Metamodel_Package $p)
    {
        return empty($p->nestedPackage) && empty($p->ownedType) && empty($p->ownedOperation) && empty($p->ownedAttribute);
    }
    
    /**
     * Searches in a given package for a typed element (likely, a class)
     *
     * @param PHP_UML_Metamodel_Package $ns    A package element
     * @param string                    $value A name
     *
     * @return mixed Either FALSE if not found, or the element
     */
    public static function searchTypeIntoPackage(PHP_UML_Metamodel_Package $ns, $value)
    {
        foreach ($ns->ownedType as $key => &$o) {
            if (strcasecmp($o->name, $value)==0) {
                return $o;
            }
        }
        return false;
    }

    /**
     * Searches if an operation already exists in a given pkg
     *
     * @param PHP_UML_Metamodel_Package $ns    A package element
     * @param string                    $value A name
     *
     * @return mixed Either FALSE if not found, or the element
     */
    static public function searchOperationIntoPackage(PHP_UML_Metamodel_Package $ns, $value)
    {
        foreach ($ns->ownedOperation as $key => &$o) {
            if (strcasecmp($o->name, $value)==0) {
                return $o;
            }
        }
        return false;
    }

    /**
     * Searches if an attribute already exists in a given pkg
     *
     * @param PHP_UML_Metamodel_Package $ns    A package element
     * @param string                    $value A name
     *
     * @return mixed Either FALSE if not found, or the element
     */
    public static function searchAttributeIntoPackage(PHP_UML_Metamodel_Package $ns, $value)
    {
        foreach ($ns->ownedAttribute as $key => &$o) {
            if (strcasecmp($o->name, $value)==0) {
                return $o;
            }
        }
        return false;
    }
    
    /**
     * Retrieves a particular tag in a given stereotype
     *
     * @param PHP_UML_Metamodel_Stereotype $s       The stereotype
     * @param string                       $tagName The tag name (eg "description")
     * 
     * @return PHP_UML_Metamodel_Tag
     */
    public static function getStereotypeTag(PHP_UML_Metamodel_Stereotype $s, $tagName)
    {
        if (!empty($s)) {
            foreach ($s->ownedAttribute as $tag) {
                if ($tag->name == $tagName)
                    return $tag;
            }
        }
        return null;
    }
    
    /**
     * Searches recursively in a given package for an element, knowing its ID
     * 
     * @param PHP_UML_Metamodel_Package $np    A package element (context)
     * @param string                    $value An ID
     *
     * @return mixed Either FALSE if not found, or the position in the stack
     */
    public static function findTypeById(PHP_UML_Metamodel_Package $np, $value)
    {
        if ($np->id == $value)
            return $np;
        
        foreach ($np->ownedType as $item) {
            if ($item->id == $value)
                return $item;
        }
        foreach ($np->ownedAttribute as $item) {
            if ($item->id == $value)
                return $item;
        }
        foreach ($np->ownedOperation as $item) {
            if ($item->id == $value)
                return $item;
        }
        foreach ($np->nestedPackage as $item) {
            $ret = self::findTypeById($item, $value);
            if (!($ret === false))
                return $ret;
        }
        return false;
    }

    /**
     * Searches recursively in a given package for a package, knowing its name
     * Works with position numbers, not variable references.
     *
     * @param PHP_UML_Metamodel_Package $np    A package element (context)
     * @param string                    $value A package name (to find)
     *
     * @return mixed Either FALSE if not found, or the position in the stack
    */
    public static function findSubpackageByName(PHP_UML_Metamodel_Package $np, $value)
    {
        foreach ($np->nestedPackage as $pkg) {
            if (strcasecmp($pkg->name, $value)==0) {
                return $pkg;
            }
        }
        return false;
    }
    
    /**
     * Splits a package path into its first/last element, and the rest
     * Allows for the two different versions of package delimiter
     * 
     * @param string $path      A path to split
     * @param bool   $modeFirst If true, splits into 1st and the rest
     *                          If false, splits into last and the rest
     * @param string $alt       Alternate separator (eg, /, or ::)
     *
     * @return array Results array
     */
    static public function getPackagePathParts($path, $modeFirst = true, $alt=PHP_UML_Input_PHP_Parser::T_NS_SEPARATOR2)
    {
        $first = '';
        $last  = '';
        if ($modeFirst) {
            $pos1 = strpos($path, PHP_UML_Input_PHP_Parser::T_NS_SEPARATOR);
            $pos2 = strpos($path, $alt);
            if ($pos1!==false && ($pos1<$pos2 || $pos2===false)) {
                $pos = $pos1;
                $len = strlen(PHP_UML_Input_PHP_Parser::T_NS_SEPARATOR);
            } else {
                $pos = $pos2;
                $len = strlen($alt);
            }
        } else {
            $pos1 = strrpos($path, PHP_UML_Input_PHP_Parser::T_NS_SEPARATOR);
            $pos2 = strrpos($path, $alt);
            if ($pos1!==false && ($pos1>$pos2 || $pos2===false)) {
                $pos = $pos1;
                $len = strlen(PHP_UML_Input_PHP_Parser::T_NS_SEPARATOR);
            } else {
                $pos = $pos2;
                $len = strlen($alt);
            }
        }
        if ($pos===false)
            $first = $path;
        else  {
            $first = substr($path, 0, $pos);
            $last  = substr($path, $pos+$len);
        }
        return array($pos, $first, $last);
    }
}
?>
