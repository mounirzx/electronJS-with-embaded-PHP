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
 * A TypeResolver is a class designed to correct a Metamodel_Package whose
 * relationships between elements are not realized by direct references (nut
 * by means of a qualified name, or an ID...)
 * Such a tool is particularly needed when building a UML model, as during the
 * iteration process, some of the referenced elements may have not yet been met.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Metamodel_TypeResolver
{
    /**
     * A reference of the top package of the model to resolve
     *
     * @var PHP_UML_Metamodel_Package
     */
    protected $topPackage;
    
    /**
    * List of default packages where to look for orphaned elements
    *
    * @var array
    */
    protected $defaultRepo = array();
    
    /**
     * Recursively replaces all the "named types" by a proper "reference" to a
     * typed element. This impacts:
     * - the extended classes and implemented classes (through their
     * EMOF-"superClass" and "implements" relations)
     * - the function parameters (through their EMOF-"type" attribute)
     * - the properties in classes (through their EMOF-"type" attribute)
     * 
     * @param PHP_UML_Metamodel_Package &$ns     Package to resolve the elements of
     * @param array                     $default Default packages where to look for
     *                                           orphaned elements
     */
    abstract public function resolve(PHP_UML_Metamodel_Package &$ns, array $default);

    
    /**
     * Resolution error. Might later be isolated in a specific class.
     * 
     * @param string                         $element Element
     * @param PHP_UML_Metamodel_NamedElement $na      NamedElement
     */
    static protected function resolutionWarning($element, $na)
    {
        PHP_UML_Warning::add('Could not resolve '.$element.
            (empty($na->file) ? '' : ' in '.$na->file->name));
    }
 
    
    /**
     * Retrieve the PHP_UML_Metamodel_Package object related to a package path
     * (ie, to a qualified name, like A\B\C). 
     * Relies on the model->$packages, when references are still named
     * (= before their resolution)
     * 
     * @param string $path The path to find
     * 
     * @return PHP_UML_Metamodel_Package The package to find. Null if not found.
     */
    protected function getPackageByPath($path)
    {
        $pkg = $this->topPackage;
        do {
            list($pos, $first, $path) = PHP_UML_Metamodel_Helper::getPackagePathParts($path);
            if ($first!='')
                $pkg = PHP_UML_Metamodel_Helper::findSubpackageByName($pkg, $first);
            if ($pkg===false)
                return false;
        } while (!($pos===false));
        return $pkg;
    }
}
?>
