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
 * @version  SVN: $Revision: 139 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2009-12-13 21:48:54 +0100 (dim., 13 déc. 2009) $
 */

/**
 * Implementation of the HTML renderer for a classifier (class, interface, datatype)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Php
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_Php_DocClassifier extends PHP_UML_Output_Php_DocElement
{
    /**
     * Return the type name of the classifier
     * 
     * @return string
     */
    abstract protected function getTypeName();
    
    /**
     * Return the CSS style name of the classifier
     * 
     * @return string
     */
    abstract protected function getStyleName();
    
    /**
     * Return the prefix (filename scheme)
     * 
     * @return string
     */
    abstract protected function getPrefix();
    
    /**
     * Return the current object
     * 
     * @param int $i Current index in all the elements of the package
     * 
     * @return PHP_UML_Metamodel_Classifier
     */
    abstract protected function getCurrentElement($i);
    
    /**
     * Return the next object
     * 
     * @param int $i Current index in all the elements of the package
     * 
     * @return PHP_UML_Metamodel_Classifier
     */
    abstract protected function getNextElement($i);
    
    /**
     * Return the previous object
     * 
     * @param int $i Previous index in all the elements of the package
     * 
     * @return PHP_UML_Metamodel_Classifier
     */
    abstract protected function getPreviousElement($i);

    /**
     * Generates and saves the HTML code for a classifier
     * 
     * @param int $i Index of the element (to be found in the Context object)
     */
    public function render($i)
    {
        $p = $this->getCurrentElement($i);

        $nsDeclaration = '';
        $ns            = '';
        if (isset($p->package)) {
            $ns = substr($this->getAbsPath($p->package, self::T_NAMESPACE), 0, -1);
            if (!empty($ns))
                $nsDeclaration = 'namespace '.$ns.';'.$this->getNl();
        }
        
        $header = $this->exporter->getDocblocks() ? '/**'.$this->getNl().
            ' * @author '.PHP_UML_Output_Exporter::APP_NAME.$this->getNl().
            ' * @since '.date("M j, Y, G:i:s O").$this->getNl().
            (empty($ns) ? '' : ' * @package '.$ns.$this->getNl()).
            ' */' : '';
                
        $str  = $this->getMainBlock($p);
        $str .= $this->getNl().'{'.$this->getNl();
        $str .= $this->getPropertyBlock($p);
        $str .= $this->getNl();
        $str .= $this->getFunctionBlock($p);
        $str .= '}';

        $str = $this->replaceInTpl($str, $header, $nsDeclaration, $p->name);

        $this->save($p->name, $str);
    }

    private function getMainBlock(PHP_UML_Metamodel_Classifier $p)
    {
        $allInherited   = $this->getAllInherited($p);
        $nbAllInherited = count($allInherited);

        $allImplemented   = $this->getAllImplemented($p);
        $nbAllImplemented = count($allImplemented);
 
        $allInheriting   = $this->getAllInheriting($p);
        $nbAllInheriting = count($allInheriting);

        $allImplementing   = $this->getAllImplementing($p);
        $nbAllImplementing = count($allImplementing);

        $typeName = $this->getTypeName();
        if ($p instanceof PHP_UML_Metamodel_Class) {
            if ($p->isAbstract)
                $typeName = 'abstract '.$typeName;
            if ($p->isReadOnly)
                $typeName = 'final '.$typeName;
        }
        $str = '';
        
        if (!is_null($p->description)) {
            $str .= $this->getTagsAsList($p->description);
        }
        
        $str .= $typeName.' '.$p->name.' ';

        if (!empty($p->superClass[0])) {
            $str .= 'extends ';
            if (is_object($p->superClass[0]))
                $str .= $this->getLinkTo($p->superClass[0]);
            else
                $str .= $this->displayUnresolved($p->superClass[0]);
            $str .= ' ';
        }
        if (isset($p->implements)) {
            $nbImpl = count($p->implements);
            if ($nbImpl>0) {
                $str .= 'implements ';
                for ($i=0; $i<$nbImpl; $i++) {
                    if (!empty($p->implements[$i])) {
                        if (is_object($p->implements[$i]))
                            $str .= $this->getLinkTo($p->implements[$i]);
                        else
                            $str .= $this->displayUnresolved($p->implements[$i]);
                        if ($i<$nbImpl-1)
                            $str .= ', ';
                    }
                }
            }
        }

        return $str;
    }
}
?>
