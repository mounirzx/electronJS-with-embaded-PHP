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
 * An implementation of TypeResolver that complete the relationships between the
 * elements of a superstructure on the base of the elements ID.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Metamodel_TypeResolverById extends PHP_UML_Metamodel_TypeResolver
{
    public function resolve(PHP_UML_Metamodel_Package &$ns, array $default)
    {
        $this->topPackage  = $ns;
        $this->defaultRepo = $default;
        
        $this->resolvePackage($ns);
    }
    
    /**
     * Recursively replaces all the "named types" by a proper "reference" to a
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
        if (empty($element)) {
            $targetElement = PHP_UML_Metamodel_Helper::searchTypeIntoPackage($this->topPackage, 'mixed');
        } else {
            $targetElement = PHP_UML_Metamodel_Helper::findTypeById($this->topPackage, $element);
        }
        
        if ($targetElement === false)
            $targetElement = $this->resolveTypeByUri($element);
        
        if ($targetElement === false) {
            self::resolutionWarning($element, $context);
        } else {
            $element = $targetElement;
        }
    }
    
    /**
     * Resolve a URI type reference by grasping a type name in the URI itself
     * (eg. href="http://schema.omg.org/spec/UML/2.1/uml.xml#Integer") 
     * 
     * @param string $uri URI to resolve
     */
    private function resolveTypeByUri($uri)
    {
        if (self::isNsUri($uri)) {
            foreach ($this->defaultRepo as $itemPkg) {
                foreach ($itemPkg->ownedType as $item) {
                    if (stripos($uri, $item->name) !== false) {
                        return $item; 
                    }
                }
            }
        }
        return false;
    }
    
    private static function isNsUri($text)
    {
        return (strncasecmp($text, 'http://', 7) == 0);
    }
}
?>
