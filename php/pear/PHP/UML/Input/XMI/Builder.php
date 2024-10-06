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
 * @version  SVN: $Revision: 178 $
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2011-09-19 03:08:06 +0200 (lun., 19 sept. 2011) $
 */

/**
 * Builder class to build a Superstructure from XMI 2.1.2
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage XMI
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * 
 */
class PHP_UML_Input_XMI_Builder
{
    /**
     * Current XPath object
     * 
     * @var DOMXPath
     */
    protected $xpath;
    
    /**
     * Package to build
     * 
     * @var PHP_UML_Metamodel_Package
     */
    protected $package;
    
    /**
     * Reference to a Superstructure. Needed when comments have to be added
     * through Tags and Stereotypes.
     * 
     * @var PHP_UML_Metamodel_Superstructure
     */
    protected $model;
    
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }
    
    public function setModel(PHP_UML_Metamodel_Superstructure $model)
    {
        $this->model   = $model;
        $this->package = &$model->packages;
    }
    
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * Retrieve an implementation of TypeResolver
     *
     * @return PHP_UML_Metamodel_TypeResolver
     */
    public static function getResolver()
    {
        return new PHP_UML_Metamodel_TypeResolverById();
    }
    
    /**
     * Set the initial package to build
     * 
     * @param PHP_UML_Metamodel_Package $package Starting package
     */
    public function setPackage(PHP_UML_Metamodel_Package $package)
    {
        $this->package = $package;
    }
    
    /**
     * Return the current package
     * 
     * @return PHP_UML_Metamodel_Package
     */
    public function getPackage()
    {
        return $this->package;
    }
    
    /**
     * Build the package from an XMI document, provided as a DOM document
     * 
     * @param DOMDocument $doc DOM document to use as source for the build
     */
    public function buildDom(DOMDocument $doc)
    {
        $this->xpath = new DOMXPath($doc);

        $this->xpath->registerNamespace('php', 'http://pear.php.net/package/PHP_UML');

        // to avoid error "Undefined namespace prefix" when the ns declaration
        // is in the node uml:Model, we have to query like this:
        $entries = $this->xpath->query('//*[local-name()="Model"]');
        
        foreach ($entries as $entry) {
            $this->package = $this->addPackage($entry);
        }
    }

    
    protected function addPackage(DOMElement $package, PHP_UML_Metamodel_Package &$nestingPackage = null)
    {
        $p       = new PHP_UML_Metamodel_Package;
        $p->id   = $package->getAttribute('xmi:id');
        $p->name = $package->getAttribute('name');
        
        $p->nestedPackage = array();

        if (!is_null($nestingPackage))
            $nestingPackage->nestedPackage[] = $p;
        $p->nestingPackage = $nestingPackage;
        
        $classifiers = $this->xpath->query('packagedElement[@xmi:type="uml:Class" or @xmi:type="uml:AssociationClass"]', $package);
        $this->addClasses($classifiers, $p);
        
        $classifiers = $this->xpath->query('packagedElement[@xmi:type="uml:Interface"]', $package);
        $this->addInterfaces($classifiers, $p);
        
        $classifiers = $this->xpath->query('packagedElement[@xmi:type="uml:DataType"]', $package);
        $this->addDatatypes($classifiers, $p);
        
        $this->addComment($package, $p);
        
        $packages = $this->xpath->query('packagedElement[@xmi:type="uml:Package"]', $package);
        foreach ($packages as $item) {
            $this->addPackage($item, $p);
        }
        
        return $p;
    }
    
    protected function addClasses(DOMNodeList $entries, PHP_UML_Metamodel_Package $package)
    {
        foreach ($entries as $entry) {
            $this->addClass($entry, $package);
        }
    }
    
    protected function addInterfaces(DOMNodeList $entries, PHP_UML_Metamodel_Package $package)
    {
        foreach ($entries as $entry) {
            $this->addInterface($entry, $package);
        }
    }
    
    protected function addDatatypes(DOMNodeList $element, PHP_UML_Metamodel_Package $package)
    {
        foreach ($element as $item) {
            $this->addDatatype($item, $package);
        }
    }

    protected function addDatatype(DOMElement $element, PHP_UML_Metamodel_Package $package)
    {
        $c       = new PHP_UML_Metamodel_Datatype();
        $c->name = $element->getAttribute('name');
        $c->id   = $element->getAttribute('xmi:id');
           
        $c->package           = $package;
        $package->ownedType[] = $c;
    }
    
    protected function addClass(DOMElement $element, PHP_UML_Metamodel_Package $package)
    {
        $c       = new PHP_UML_Metamodel_Class();
        $c->name = $element->getAttribute('name');
        $c->id   = $element->getAttribute('xmi:id');
        
        $parameters = $this->xpath->query('//packagedElement[@xmi:type="uml:Realization" and @client="'.$c->id.'"]');
        foreach ($parameters as $item) {
            $tgId = $item->getAttribute('realizingClassifier');
            if (empty($tgId))
                $tgId = $item->getAttribute('supplier');
            if (!empty($tgId))
                $c->implements[] = $tgId;
        }
        
        $this->addClassifierFeatures($element, $c);
        
        $this->addProperties($element, $c);
        $this->addOperations($element, $c);
        $this->addComment($element, $c);
        
        $c->package           = $package;
        $package->ownedType[] = $c;
    }
    
    protected function addComment(DOMElement $element, PHP_UML_Metamodel_NamedElement $c)
    {
        $description = '';
        
        $parameters = $this->xpath->query('ownedComment', $element);
        foreach ($parameters as $item) {
            $description = trim($item->getAttribute('body').$item->nodeValue);
        }
        if ($description=='') {
            $parameters = $this->xpath->query('//packagedComment[@annotatedElement="'.$c->id.'"]|//*[@xmi:type="uml:Comment" and @annotatedElement="'.$c->id.'"]');
            foreach ($parameters as $item) {
                $description = trim($item->getAttribute('body').$item->nodeValue);
            }
        }
        $parameters = $this->xpath->query('//php:docblock[@base_Element="'.$c->id.'"]');
        $docblocks  = array();
        foreach ($parameters as $item) {
            foreach ($item->childNodes as $item) {
                $docblock = array();
                $nodeName = $item->nodeName;
                
                $pos         = strpos($nodeName, ':');
                $docblock[]  = ($pos===false ? $nodeName : substr($nodeName, $pos+1));
                $docblock[]  = $docblock[0];    // see how tags were initially set in Parser
                $docblock[]  = $item->nodeValue;    
                $docblocks[] = $docblock;
            }
        }
        if ((!empty($description)) || count($docblocks)>0) {
            $this->model->addDocTags($c, $description, $docblocks);
        }
    }
    
    protected function addInterface(DOMElement $element, PHP_UML_Metamodel_Package $package)
    {
        $c       = new PHP_UML_Metamodel_Interface();
        $c->name = $element->getAttribute('name');
        $c->id   = $element->getAttribute('xmi:id');
        
        $this->addClassifierFeatures($element, $c);
        
        $this->addProperties($element, $c);
        $this->addOperations($element, $c);
        $this->addComment($element, $c);
        
        $c->package           = $package;
        $package->ownedType[] = $c;
    }
    
    protected function addProperties(DOMElement $element, PHP_UML_Metamodel_Classifier $class)
    {
        $attributes = $this->xpath->query('ownedAttribute', $element);
        foreach ($attributes as $item) {
            $this->addProperty($item, $class);
        }
    }

    protected function addOperations(DOMElement $element, PHP_UML_Metamodel_Classifier $class)
    {
        $operations = $this->xpath->query('ownedOperation', $element);
        foreach ($operations as $item) {
            $this->addOperation($item, $class);
        }
    }
    
    protected function addOperation(DOMElement $element, PHP_UML_Metamodel_Classifier $class)
    {
        $a = new PHP_UML_Metamodel_Operation;
        
        $a->name = $element->getAttribute('name');
        $a->id   = $element->getAttribute('xmi:id');
           
        $parameters = $this->xpath->query('ownedParameter', $element);
        foreach ($parameters as $item) {
            $this->addParameter($item, $a);
        }
        
        $a->isInstantiable = !($element->getAttribute('isStatic') == 'true');
        $a->isReadOnly     = ($element->getAttribute('isReadOnly') == 'true');
        $a->visibility     = $element->getAttribute('visibility');
        
        $this->addComment($element, $a);
        
        $a->class                = $class;
        $class->ownedOperation[] = $a;
    }
    
    protected function addParameter(DOMElement $element, PHP_UML_Metamodel_Operation $o)
    {
        $a = new PHP_UML_Metamodel_Parameter();
        
        $a->name      = $element->getAttribute('name');
        $a->id        = $element->getAttribute('xmi:id');
        $a->direction = $element->getAttribute('direction');
        
        $this->addDefaultValueAndType($element, $a);
        
        $o->ownedParameter[] = $a;
        $a->operation        = $o;
    }
    
    protected function addProperty(DOMElement $element, PHP_UML_Metamodel_Class $class)
    {
        $a = new PHP_UML_Metamodel_Property();
        
        $a->name = $element->getAttribute('name');
        if ($a->name=='')
            return;
        $a->id = $element->getAttribute('xmi:id');
        
        $this->addDefaultValueAndType($element, $a);
        
        $a->isInstantiable = !($element->getAttribute('isStatic') == 'true');
        $a->isReadOnly     = ($element->getAttribute('isReadOnly') == 'true');
        $a->visibility     = $element->getAttribute('visibility');
        
        $this->addComment($element, $a);
        
        $a->class                = $class;
        $class->ownedAttribute[] = $a;
    }
    
    protected function addClassifierFeatures(DOMElement $element, PHP_UML_Metamodel_Classifier &$c)
    {
        $parameters = $this->xpath->query('generalization|*[@type="uml:Generalization"]', $element);
        foreach ($parameters as $item) {
            $c->superClass[] = $item->getAttribute('general');
        }
        
        $c->isAbstract = ($element->getAttribute('isAbstract')=='true');        
        $c->isReadOnly = ($element->getAttribute('isReadOnly')=='true');
    }
    
    protected function addDefaultValueAndType($element, PHP_UML_Metamodel_TypedElement $a)
    {
        $type = $this->xpath->query('type', $element);
        
        if ($type->length>0) {
            $a->type = $type->item(0)->getAttribute('xmi:idref');
            if ($a->type=='')
                $a->type = $type->item(0)->getAttribute('href');
        }
        $dv = $this->xpath->query('defaultValue', $element);
        if ($dv->length>0) {
            $v = $dv->item(0)->getAttribute('value');
            if ($v=='') {
                $v = $dv->item(0)->nodeValue;
            }
            $a->default = $v;
        }
    }
}
?>
