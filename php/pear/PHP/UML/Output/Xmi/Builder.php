<?php
/**
 * PHP_UML (interface PHP_UML_Output_Xmi_Builder)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 138 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2009-12-13 04:23:11 +0100 (dim., 13 déc. 2009) $
 */

/**
 * Interface for the XMI generation
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 */
interface PHP_UML_Output_Xmi_Builder
{

    /**
     * Retrievesthe XMI header
     *
     * @return string XMI code
     */
    function getXmiHeaderOpen();
    
    /**
     * Closing tag for the XMI header
     *
     * @return string XMI code
     */
    function getXmiHeaderClose();
    
    /**
     * Retrieves the opening tag for a model
     * 
     * @param string $model Root package
     * 
     * @return string XMI code
     */
    function getModelOpen(PHP_UML_Metamodel_Package $model);

    /**
     * Retrieves the closing tag of a model
     * 
     * @return string XMI code
     */
    function getModelClose();
    
    /**
     * Retrieves the opening tag for a package
     * 
     * @param PHP_UML_Metamodel_Package $package Package to insert
     * 
     * @return string XMI code
     */
    function getPackageOpen(PHP_UML_Metamodel_Package $package);
    
    /**
     * Retrieves the closing tag of a package
     *
     * @return string XMI code
     */
    function getPackageClose();

    /**
     * Retrieves the opening namespace tag (XMI 1 only)
     *
     * @return string XMI code
     */
    function getNamespaceOpen();

    /**
     * Retrieves the closing namespace tag (XMI 1 only)
     *
     * @return string XMI code
     */
    function getNamespaceClose();
    
    /**
     * Retrieves the opening tag for a sub-system (XMI 1), or a component (XMI 2)
     *
     * @param PHP_UML_Metamodel_Package $package Package to code as a subsystem
     * 
     * @return string XMI code
     */
    public function getSubsystemOpen(PHP_UML_Metamodel_Package $package);

    /**
     * Retrieves the closing tag for a subsystem / component
     * 
     * @return string XMI code
     */
    function getSubsystemClose();
    
    /**
     * Retrieves the necessary stereotypes
     *
     * @return string XMI code
     */
    function getStereotypes();
    
    /**
     * Retrieves the XMI code for a given stereotype
     *
     * @param PHP_UML_Metamodel_Stereotype $s Stereotype
     */
    function getStereotypeInstance(PHP_UML_Metamodel_Stereotype $s);
    
    /**
     * Retrieves the XMI declarations of the main PHP types
     * 
     * @param PHP_UML_Datatype $type Datatype
     * 
     * @return string XMI code
     */
    function getDatatype(PHP_UML_Metamodel_Datatype $type);
    
    /**
     * Retrieves the XMI code for a class
     * 
     * @param PHP_UML_Metamodel_Class $class Class
     * 
     * @return string XMI code
     */
    function getClass(PHP_UML_Metamodel_Class $class);     
    
    /**
     * Retrieves the XMI code for an interface
     * 
     * @param PHP_UML_Metamodel_Interface $interface Class
     * 
     * @return string XMI code
     */
    function getInterface(PHP_UML_Metamodel_Interface $interface);
    
    /**
     * Retrieves the XMI code for an operation
     *
     * @param PHP_UML_Metamodel_Operation $operation Operation
     * 
     * @return string XMI code
     */
    function getOperation(PHP_UML_Metamodel_Operation $operation);

    /**
     * Retrieves the XMI code for the paramater of an operation
     * 
     * @param PHP_UML_Metamodel_Parameter $parameter Parameter
     * 
     * @return string XMI code
     */
    function getParameter(PHP_UML_Metamodel_Parameter $parameter);
    
    /**
     * Retrieves the XMI code for an artifact
     * 
     * @param PHP_UML_Metamodel_Artifact $file File to add as an artifact
     * @param array                      &$mf  Manifested elements
     *                                         (array of PHP_UML_Metamodel_Class)
     * 
     * @return string XMI code
     */
    function getArtifact(PHP_UML_Metamodel_Artifact $file, &$mf = array());
    
    /**
     * Retrieves the XMI code for a component
     *
     * @param PHP_UML_Metamodel_Package $package  Package to add as a component
     * @param array                     $provided Set of providing classes
     * @param array                     $required Set of required classes (TODO)
     *
     * @return string XMI code
     */
    function getComponentOpen(PHP_UML_Metamodel_Package $package, array $provided, array $required);
    
    /**
     * Retieves the closing tag for a component
     *
     * @return string XMI code
     */
    function getComponentClose();
    
    /**
     * Retrieves the XMI code for a classifier (datatype, class, interface)
     * Required by getParameter(). Adds the default value (if any)
     *
     * @param PHP_UML_Metamodel_TypedElement $parameter Element (datatype, class..)
     * 
     * @return string XMI code           
     */
    function getParameterType(PHP_UML_Metamodel_TypedElement $parameter);
    
    /**
     * Retrieves the XMI code for all the realization (= interface implementations)
     * of a given element
     *
     * @param PHP_UML_Metamodel_Class $client Child element
     * 
     * @return string XMI code
     */
    function getRealizations(PHP_UML_Metamodel_Class $client);
    
    /**
     * Retrieves the XMI code for all the inherited classes of a given element
     *
     * @param PHP_UML_Metamodel_Type $client Child element
     * 
     * @return mixed In XMI 1.x, this returns an array, because XMI 1 needs two
     * declarations: the child element must be defined as "generalizable", in
     * addition to the generalization relationship. In XMI 2, only the relationship
     * is necessary, which is returned as a string.
     */
    function getGeneralizations(PHP_UML_Metamodel_Type $client);
    
    /**
     * Adds a description/comment to a model element
     * The description is given through a stereotype instance
     *
     * @param PHP_UML_Metamodel_Stereotype $s                A stereotype object
     * @param string                       $annotatedElement The commented element
     * 
     * @return string XMI code
     */
    function getComment(PHP_UML_Metamodel_Stereotype $s, $annotatedElement='');
    
    /**
     * Retrieves the XMI code for a UML2 profile.
     *
     * @return string XMI code
     */
    function getProfile();
    
    /**
     * Retrieves the metadata related to a given tag
     *
     * @param PHP_UML_Metamodel_Tag $tag Tag
     * 
     * @return string XMI code
     */
    public function getMetadata(PHP_UML_Metamodel_Tag $tag);
}

?>
