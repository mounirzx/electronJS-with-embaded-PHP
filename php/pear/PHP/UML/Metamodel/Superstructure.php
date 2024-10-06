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
 * A superstructure is a set composed of a UML model, a physical model,
 * and some stereotypes associated to the UML model.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Metamodel_Superstructure
{
    const META_INTERFACE = 'PHP_UML_Metamodel_Interface';
    const META_CLASS     = 'PHP_UML_Metamodel_Class';
    const META_DATATYPE  = 'PHP_UML_Metamodel_Datatype';
    const META_OPERATION = 'PHP_UML_Metamodel_Operation';
    const META_PROPERTY  = 'PHP_UML_Metamodel_Property';

    const PHP_PROFILE_NAME        = 'php';
    const PHP_STEREOTYPE_DOCBLOCK = 'docblock';
    
    /**
     * The root package for a logical UML model
     *
     * @var PHP_UML_Metamodel_Package
     */
    public $packages;

    /**
     * The root package for a deployment (physical) model
     *
     * @var PHP_UML_Metamodel_Package
     */    
    public $deploymentPackages;

    /**
     * Stack of all stereotypes
     * TODO: when real stereotypes will be implemented, deleting that array, and
     * reading the stereotypes from the $packages instead
     *
     * @var array
     */
    public $stereotypes = array();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Adds the internal PHP metatypes, metaclasses, metainterfaces...
     *
     */
    public function addInternalPhpTypes()
    {
        foreach (PHP_UML_Metamodel_Enumeration::$datatypes as $d) {
            $type       = new PHP_UML_Metamodel_Datatype;
            $type->id   = PHP_UML_SimpleUID::getUID();
            $type->name = $d;

            $this->addRootType($type, 'Internal PHP type.');
            
        }
        foreach (PHP_UML_Metamodel_Enumeration::$interfaces as $i) {
            $type       = new PHP_UML_Metamodel_Interface;
            $type->id   = PHP_UML_SimpleUID::getUID();
            $type->name = $i;

            $this->addRootType($type, 'Internal PHP interface.');
        }
        foreach (PHP_UML_Metamodel_Enumeration::$classes as $c) {
            $type       = new PHP_UML_Metamodel_Class;
            $type->id   = PHP_UML_SimpleUID::getUID();
            $type->name = $c;

            $this->addRootType($type, 'Internal PHP class.');
        }
    }
    
    private function addRootType(PHP_UML_Metamodel_NamedElement $type, $desc)
    {
        if (!PHP_UML_Metamodel_Helper::searchTypeIntoPackage($this->packages, $type->name)) {
            $this->packages->ownedType[] = $type;
            $this->addDocTags($type, $desc);
        }
    }
    
    /**
     * Creates a stereotype and the necessary Tag objects for a given element
     * 
     * @param PHP_UML_Metamodel_NamedElement &$element The element to document
     * @param string                         $desc     A textual description
     * @param array                          $docs     An array containing docblocks
     */
    public function addDocTags(PHP_UML_Metamodel_NamedElement &$element, $desc, array $docs = array())
    {
        $stereotype = $this->createStereotype($element, self::PHP_PROFILE_NAME, self::PHP_STEREOTYPE_DOCBLOCK);

        if ($desc != '') {
            $tag        = new PHP_UML_Metamodel_Tag;
            $tag->id    = PHP_UML_SimpleUID::getUID();;
            $tag->name  = 'description';
            $tag->value = $desc;

            $stereotype->ownedAttribute[] = $tag;
        }
        foreach ($docs as $doc) {
            $tag        = new PHP_UML_Metamodel_Tag;
            $tag->id    = PHP_UML_SimpleUID::getUID();;
            $tag->name  = $doc[1];
            $tag->value = trim(implode(' ', array_slice($doc, 2)));

            $stereotype->ownedAttribute[] = $tag;
        }
        $element->description = $stereotype;
    }
    
    /**
     * Recursively replaces all the "named types" by a proper "reference" to a
     * typed element. This impacts:
     * - the extended classes and implemented classes (through their
     * EMOF-"superClass" and "implements" relations)
     * - the function parameters (through their EMOF-"type" attribute)
     * - the properties in classes (through their EMOF-"type" attribute)
     * 
     * @param PHP_UML_Metamodel_Package &$ns    Package to resolve the elements of
     * @param array                     &$_oDef Default packages to look for
     *                                          orphaned elements
     */
    private function resolveReferences(PHP_UML_Metamodel_Package &$ns, array &$_oDef)
    {
        if (!is_null($ns->nestedPackage)) {
            foreach ($ns->nestedPackage as $key => &$pkg) {
                $this->resolveReferences($pkg, $_oDef);
            }
        }
        if (!is_null($ns->ownedType))
        foreach ($ns->ownedType as &$elt) {
            if (isset($elt->superClass) && !is_null($elt->superClass)) { 
                foreach ($elt->superClass as &$className) {
                    $this->resolveType($ns, $className, $_oDef, $elt);
                }
            }
            if (isset($elt->implements) && !is_null($elt->implements)) { 
                foreach ($elt->implements as &$className) {
                    $this->resolveType($ns, $className, $_oDef, $elt);
                }
            }
            if (isset($elt->ownedOperation)) {
                foreach ($elt->ownedOperation as &$function) {
                    foreach ($function->ownedParameter as &$parameter) {
                        $this->resolveType($ns, $parameter->type, $_oDef, $elt); 
                    }
                }
            }
            if (isset($elt->ownedAttribute)) {
                foreach ($elt->ownedAttribute as &$attribute) { 
                    $this->resolveType($ns, $attribute->type, $_oDef, $elt);
                }
            }
        }
        if (isset($ns->ownedOperation)) {
            foreach ($ns->ownedOperation as &$function) {
                foreach ($function->ownedParameter as &$parameter) {
                    $this->resolveType($ns, $parameter->type, $_oDef, $function); 
                }
            }
        }
        if (isset($ns->ownedAttribute)) {
            foreach ($ns->ownedAttribute as &$attribute) { 
                $this->resolveType($ns, $attribute->type, $_oDef, $attribute);
            }
        }
    }


    /**
     * Retrieves the stereotype (named $name) associated to the element $element
     * If not found, returns null.
     *
     * @param PHP_UML_Metamodel_NamedElement $element        Related object
     * @param string                         $profileName    Profile name
     * @param string                         $stereotypeName Stereotype name
     * 
     * @return PHP_UML_Metamodel_Stereotype
     */
    public function getStereotype(PHP_UML_Metamodel_NamedElement $element, $profileName, $stereotypeName)
    {
        foreach ($this->stereotypes->getIterator() as $s) {
            if ($s->element == $element && $s->name == $stereotypeName && $s->profile == $profileName) {
                return $s;
            }
        }
        return null;
    }

    /**
     * Creates a stereotype in a given profile, and binds it to a given element
     * Returns the stereotype that was created
     * 
     * @param PHP_UML_Metamodel_NamedElement &$element       The element
     * @param string                         $profileName    The profile name
     * @param string                         $stereotypeName The stereotype name
     * 
     * @return PHP_UML_Metamodel_Stereotype
     */
    public function createStereotype(PHP_UML_Metamodel_NamedElement &$element, $profileName, $stereotypeName)
    {
        $stereotype = new PHP_UML_Metamodel_Stereotype;

        $stereotype->profile = $profileName;
        $stereotype->name    = $stereotypeName;
        $stereotype->element = $element;
        $this->stereotypes[] = $stereotype;
        return $stereotype;
    }
        
    /**
     * Finalizes the main object structure that the Parser has built.
     * Launches the resolution of the references for all stacks from the root pkg
     * 
     * Every reference (a temporary string) is replaced by a PHP reference
     * to the corresponding type (that is, a class or a datatype)
     * Must be run once the model is complete (= once PHP parsing is done)
     * 
     * @param bool  $noEmptyPkg True to force removal of empty packages
     * @param array $defPkg     Array of PHP_UML_Metamodel_Package where to look into,
     *                          in order to resolve the orphaned elements.
     *                          By default, it will look in the root package. This is,
     *                          by the way, where the PHP datatypes are.
     */
    public function finalizeAll($noEmptyPkg = true, $defPkg = array())
    {
        if ($noEmptyPkg)
            PHP_UML_Metamodel_Helper::deleteEmptyPackages($this->packages);

        $resolver          = new PHP_UML_Metamodel_TypeResolverByName();
        $resolver->package = $this->packages;
        
        if (empty($defPkg))
            $defPkg = array($this->packages);
        else
            $defPkg[] = &$this->packages;
        
        $resolver->resolveReferences($this->packages, $defPkg);
    }
    
    /**
     * Initialize the structure before use (we just instantiate the top objects in
     * the logical and deployment models)
     * 
     * @param string $modelName Model name
     */
    public function initModel($modelName = 'default')
    {
        $this->packages       = new PHP_UML_Metamodel_Package;
        $this->packages->name = $modelName;
        $this->packages->id   = PHP_UML_SimpleUID::getUID();
        $this->addInternalPhpTypes();

        $this->deploymentPackages       = new PHP_UML_Metamodel_Package;
        $this->deploymentPackages->name = 'Deployment View';
        $this->deploymentPackages->id   = PHP_UML_SimpleUID::getUID();
    }
}
?>
