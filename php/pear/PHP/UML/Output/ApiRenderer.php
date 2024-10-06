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
 * @version  SVN: $Revision: -1 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: $
 */

/**
 * Top class for the API "renderers" used by the ExporterAPI implementations
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_ApiRenderer
{
    const PACKAGE_FILENAME = 'package-summary';
    const CLASS_PREFIX     = 'class-';
    const INTERFACE_PREFIX = 'interface-';
    const DATATYPE_PREFIX  = 'datatype-';
        
    const META_INTERFACE = PHP_UML_Metamodel_Superstructure::META_INTERFACE;
    const META_CLASS     = PHP_UML_Metamodel_Superstructure::META_CLASS;
    const META_DATATYPE  = PHP_UML_Metamodel_Superstructure::META_DATATYPE;
    const META_OPERATION = PHP_UML_Metamodel_Superstructure::META_OPERATION;
    const META_PROPERTY  = PHP_UML_Metamodel_Superstructure::META_PROPERTY;

    const T_NAMESPACE = '\\';

    /**
     * Main template (we pre-store it to avoid reading repeatedly) 
     * @var string
     */
    protected $mainTpl;

    /**
     * Classes that won't appear directly in the main API lists
     * for readability (even though each one has its own detail page)
     * @var array
     */
    protected $hiddenClasses = array();

    /**
     * Hidden interfaces
     * @var array
     */
    protected $hiddenInterfaces = array();

    /**
     * Hidden classifiers
     * @var array
     */
    protected $hiddenClassifiers = array();

    /**
     * These docblocks will not be displayed in the docblocks list 
     * @var array
     */
    protected $ignoredTag = array();

    /**
     * Reference to the Exporter object (giving access to the ApiContextPackage)
     * @var PHP_UML_Output_ExporterAPI
     */
    protected $exporter;

    /**
     * Render globally a package
     * 
     * @param PHP_UML_Metamodel_Package $p Starting package (model)
     */
    abstract function render($p);

    /**
     * Constructor for an ApiRenderer.
     * 
     * @param PHP_UML_Output_ExporterAPI $exporter Reference to an exporter
     */
    public function __construct(PHP_UML_Output_ExporterAPI $exporter)
    {
        $this->hiddenClasses     = array_merge($this->hiddenClasses, PHP_UML_Metamodel_Enumeration::$classes);
        $this->hiddenInterfaces  = array_merge($this->hiddenInterfaces, PHP_UML_Metamodel_Enumeration::$interfaces);
        $this->hiddenClassifiers = array_merge($this->hiddenClassifiers, $this->hiddenClasses);
        $this->hiddenClassifiers = array_merge($this->hiddenClassifiers, $this->hiddenInterfaces);
        $this->hiddenClassifiers = array_merge($this->hiddenClassifiers, PHP_UML_Metamodel_Enumeration::$datatypes);

        $this->exporter = $exporter;
    }

    abstract protected function getDescription(PHP_UML_Metamodel_Stereotype $s, $annotatedElement='');

    /**
     * Renders the operation's parameters
     * 
     * @param PHP_UML_Metamodel_Operation $operation The operation
     * @param bool                        $withType  If true, adds an hyperlink
     * 
     * @return string
     */
    abstract protected function getParameterList(PHP_UML_Metamodel_Operation $operation, $withType = false);
    

    /**
     * Renders a default value
     * 
     * @param PHP_UML_Metamodel_TypedElement $obj A property or a parameter
     * 
     * @return string
     */
    abstract protected function getDefaultValue(PHP_UML_Metamodel_TypedElement $obj);

    /**
     * Returns the content of a template file
     * 
     * @param string $str Template filename
     *     
     * @return string The content of the template
     */
    protected function getTemplate($str)
    {
        $baseSrc = $this->getTemplateDirectory();
        return file_get_contents($baseSrc.DIRECTORY_SEPARATOR.$str);
    }
    
    /**
     * Return the current ApiContextPackage object
     * 
     * @return PHP_UML_Output_ApiContextPackage
     */
    protected function getContextPackage()
    {
        return $this->exporter->getContextPackage();
    }
    
    static protected function getObjPrefix(PHP_UML_Metamodel_NamedElement $x)
    {
        switch (get_class($x)) {
        case self::META_INTERFACE:
            return self::INTERFACE_PREFIX;
        case self::META_DATATYPE:
            return self::DATATYPE_PREFIX;
        case self::META_OPERATION:
            return self::PACKAGE_FILENAME;
        case self::META_PROPERTY:
            return self::PACKAGE_FILENAME;
        }
        return self::CLASS_PREFIX;
    }

    static protected function getObjStyle(PHP_UML_Metamodel_NamedElement $x)
    {
        switch (get_class($x)) {
        case self::META_INTERFACE:
            return 'interface';
        case self::META_DATATYPE:
            return 'datatype';
        case self::META_OPERATION:
            return 'method';
        case self::META_PROPERTY:
            return 'property';
        }
        return 'class';
    }

    /**
     * Renders a link to an element
     * (since datatypes don't own to a "package",  we suppose they are located in
     * the top package)
     * 
     * @param PHP_UML_Metamodel_Classifier $t        The element
     * @param string                       $cssStyle CSS style to use
     * 
     * @return string
     */
    //abstract protected function getLinkTo(PHP_UML_Metamodel_Classifier $t, $cssStyle='link');

    /**
     * Returns the complete namespace for an element
     * 
     * @param PHP_UML_Metamodel_Classifier $t The classifier
     * 
     * @return string
     */
    protected function getQualifiedName(PHP_UML_Metamodel_Classifier $t)
    {
        $ns = isset($t->package) ? $this->getAbsPath($t->package, self::T_NAMESPACE) : '';
        return $ns.$t->name;
    }

    /**
     * Renders an unresolved type
     * 
     * @param string $type Type, provided as a string
     * 
     * @return string
     */
    abstract protected function displayUnresolved($type);
    
    /**
     * Returns the path from the top package to a given package
     * 
     * @param PHP_UML_Metamodel_Package $p         The package
     * @param string                    $delimiter Delimiter
     * 
     * @return string
     */
    protected function getAbsPath(PHP_UML_Metamodel_Package $p, $delimiter='/')
    {
        if (!empty($p->nestingPackage)) {
            return $this->getAbsPath($p->nestingPackage, $delimiter).$p->name.$delimiter;
        }
        else
            return '';
    }

    /**
     * Returns the return parameter of a function
     * 
     * @param PHP_UML_Metamodel_Operation $operation The function
     * 
     * @return PHP_UML_Metamodel_Parameter The parameter
     */
    protected function getReturnParam(PHP_UML_Metamodel_Operation $operation)
    {
        foreach ($operation->ownedParameter as $p) {
            if ($p->direction=='return') {
                return $p;
            }
        }
        return null;
    }

    /**
     * Renders the properties of a given stereotype
     * 
     * @param PHP_UML_Metamodel_Stereotype $s A stereotype
     * 
     * @return string
     */
    abstract protected function getTagsAsList(PHP_UML_Metamodel_Stereotype $s);

    /**
     * Returns the array containing all the extended classes of a classifier
     * This array must have been previously set in the Context object
     * 
     * @param PHP_UML_Metamodel_NamedElement $c A classifier or a package
     * 
     * @return array
     */
    protected function getAllInherited(PHP_UML_Metamodel_NamedElement $c)
    {
        return array_key_exists($c->id, $this->getContextPackage()->allInherited) ? $this->getContextPackage()->allInherited[$c->id] : array();
    }

    /**
     * Returns the array containing all the classes that extends a classifier
     * 
     * @param PHP_UML_Metamodel_NamedElement $c A classifier or a package
     * 
     * @return array
     */
    protected function getAllInheriting(PHP_UML_Metamodel_NamedElement $c)
    {
        return array_key_exists($c->id, $this->getContextPackage()->allInheriting) ? $this->getContextPackage()->allInheriting[$c->id] : array();
    }

    /**
     * Returns the array containing all the interfaces of a classifier
     * 
     * @param PHP_UML_Metamodel_NamedElement $c A classifier or a package
     * 
     * @return array
     */
    protected function getAllImplemented(PHP_UML_Metamodel_NamedElement $c)
    {
        return array_key_exists($c->id, $this->getContextPackage()->allImplemented) ? $this->getContextPackage()->allImplemented[$c->id] : array();
    }

    /**
     * Returns the array containing all the classes that implement a given interface
     * 
     * @param PHP_UML_Metamodel_NamedElement $c A classifier or a package
     * 
     * @return array
     */
    protected function getAllImplementing(PHP_UML_Metamodel_NamedElement $c)
    {
        return array_key_exists($c->id, $this->getContextPackage()->allImplementing) ? $this->getContextPackage()->allImplementing[$c->id] : array();
    }

    /**
     * Saves a string in a file (in the folder referenced in the context object)
     * 
     * @param string $elementName Filename (without the extension)
     * @param string $str         Content
     */
    abstract protected function save($elementName, $str);

    /**
     * Renders the block "Properties" of a package or a class
     * 
     * @param PHP_UML_Metamodel_NamedElement $p A classifier/a package
     * 
     * @return string
     */
    abstract protected function getPropertyBlock(PHP_UML_Metamodel_NamedElement $p);

    /**
     * Returns an ID for a property
     * 
     * @param PHP_UML_Metamodel_Property $o Element
     * 
     * @return string
     */
    protected function generatePropertyId(PHP_UML_Metamodel_Property $o)
    {
        return str_replace('$', '', $o->name);
    }
    
    /**
     * Returns an ID to identify a function
     * 
     * @param PHP_UML_Metamodel_Operation $o Element
     * 
     * @return string
     */
    protected function generateFunctionId(PHP_UML_Metamodel_Operation $o)
    {
        return 'f'.$o->id;
    }
    
    /**
     * Renders the block "Function" of a package or a classifier
     * 
     * @param PHP_UML_Metamodel_NamedElement $p A classifier or a package
     * 
     * @return string
     */
    abstract protected function getFunctionBlock(PHP_UML_Metamodel_NamedElement $p);

   
    /**
     * Renders the "File" information tag
     * 
     * @param PHP_UML_Metamodel_NamedElement $p An element
     * 
     * @return string
     */
    abstract protected function getFileInfo(PHP_UML_Metamodel_NamedElement $p);
    
    /**
     * Replace the template's placeholders with their value
     *  
     * @param string $main Main content (generated by PHP_UML)
     * @param string $nav  Navigation content (navig bar)
     * @param string $tit  Title content
     * @param string $name Element name
     * 
     * @return string
     */
    abstract protected function replaceInTpl($main, $nav, $tit, $name);
}
?>
