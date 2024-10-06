<?php
/**
 * PHP_UML
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
 * An implementation of TypeResolver that completes the relationships between the
 * elements of a superstructure by using the elements name.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Metamodel_TypeResolverByName extends PHP_UML_Metamodel_TypeResolver
{
    public function resolve(PHP_UML_Metamodel_Package &$ns, array $default)
    {
        $this->topPackage  = $ns;
        $this->defaultRepo = $default;
        
        $this->resolvePackage($ns);
    }
    
    /**
     * Recursively replaces all the "named types" by a proper reference to a
     * typed element. This impacts:
     * - the extended classes and implemented classes (through their
     * EMOF-"superClass" and "implements" relations)
     * - the function parameters (through their EMOF-"type" attribute)
     * - the properties in classes (through their EMOF-"type" attribute)
     * 
     * @param PHP_UML_Metamodel_Package &$ns Package to resolve the elements of
     */
    private function resolvePackage(PHP_UML_Metamodel_Package &$ns)
    {
        if (!is_null($ns->nestedPackage)) {
            foreach ($ns->nestedPackage as $key => &$pkg) {
                $this->resolvePackage($pkg);
            }
        }
        if (!is_null($ns->ownedType))
        foreach ($ns->ownedType as &$elt) {
            if (isset($elt->superClass) && !is_null($elt->superClass)) { 
                foreach ($elt->superClass as &$className) {
                    $this->resolveType($ns, $className, $elt);
                }
            }
            if (isset($elt->implements) && !is_null($elt->implements)) { 
                foreach ($elt->implements as &$className) {
                    $this->resolveType($ns, $className, $elt);
                }
            }
            if (isset($elt->ownedOperation)) {
                foreach ($elt->ownedOperation as &$function) {
                    foreach ($function->ownedParameter as &$parameter) {
                        $this->resolveType($ns, $parameter->type, $elt); 
                    }
                }
            }
            if (isset($elt->ownedAttribute)) {
                foreach ($elt->ownedAttribute as &$attribute) { 
                    $this->resolveType($ns, $attribute->type, $elt);
                }
            }
        }
        if (isset($ns->ownedOperation)) {
            foreach ($ns->ownedOperation as &$function) {
                foreach ($function->ownedParameter as &$parameter) {
                    $this->resolveType($ns, $parameter->type, $function); 
                }
            }
        }
        if (isset($ns->ownedAttribute)) {
            foreach ($ns->ownedAttribute as &$attribute) { 
                $this->resolveType($ns, $attribute->type, $attribute);
            }
        }
    }

    /**
     * Does the type resolution for a given element in a given package
     *
     * @param PHP_UML_Metamodel_Package $pkg      The nesting package
     * @param string                    &$element The element to resolve, provided as a name
     * @param PHP_UML_Metamodel_Type    $context  The context (the nesting class/interface, which 
     *                                            is the only element to know the nesting file)
     */
    private function resolveType(PHP_UML_Metamodel_Package $pkg, &$element, PHP_UML_Metamodel_NamedElement $context)
    {
        // Is there a ns separator (\) in it ?
        list($pos, $first, $last) = PHP_UML_Metamodel_Helper::getPackagePathParts($element, false);
        if (!($pos===false)) {
            $tmpPkg = $this->getPackageByPath($first);
            if ($tmpPkg===false) {
                self::resolutionWarning($element, $context);
                $element = null;
            } else {
                // Do we know that type?
                $_o = PHP_UML_Metamodel_Helper::searchTypeIntoPackage($tmpPkg, $last);
                if (!($_o===false)) {
                    $element = $_o;
                } else {
                    self::resolutionWarning($element, $context);
                    //$element = null;
                }
            }
        } else {

            // Is it in the current package?
            $_o = PHP_UML_Metamodel_Helper::searchTypeIntoPackage($pkg, $element);
            if (!($_o===false)) {
                $element = $_o;
            } else {
                // Is it in one of the "default" packages?
                $found = false;
                foreach ($this->defaultRepo as $itemPkg) {
                    $_o = PHP_UML_Metamodel_Helper::searchTypeIntoPackage($itemPkg, $element);
                    if (!($_o===false)) {
                        $element = $_o;
                        $found   = true;
                        break;
                    }
                }
                if (!$found) {
                    self::resolutionWarning($element, $context);
                    //$element = null;
                }
            }
        }
    }
}
?>
