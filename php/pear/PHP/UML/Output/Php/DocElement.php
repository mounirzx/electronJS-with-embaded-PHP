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
 * @version  SVN: $Revision$
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date$
 */

/**
 * General class for an renderer in the PHP implementation
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Php
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_Php_DocElement extends PHP_UML_Output_ApiRenderer
{
    const FILE_EXT          = 'php';
    const TEMPLATES_DIRNAME = 'templates';

    /**
     * Constructor
     *
     * @param PHP_UML_Output_ExporterAPI $exporter Reference to an exporter
     */
    public function __construct(PHP_UML_Output_ExporterAPI $exporter)
    {
        parent::__construct($exporter);
        $this->mainTpl = $this->getTemplate('main.php');
    }
    
    protected function getDescription(PHP_UML_Metamodel_Stereotype $s, $annotatedElement='')
    {
        $tag = PHP_UML_Metamodel_Helper::getStereotypeTag($s, 'description');
        if (!is_null($tag))
            return $tag->value;
        else
            return '';
    }

    /**
     * Renders the operation's parameters, as a comma-sep list, between brackets
     * 
     * @param PHP_UML_Metamodel_Operation $operation The operation
     * @param bool                        $withType  If true, adds an hyperlink
     * 
     * @return string
     */
    protected function getParameterList(PHP_UML_Metamodel_Operation $operation, $withType = false)
    {
        $str = '(';
        $n   = count($operation->ownedParameter);
        for ($i=0; $i<$n; $i++) {
            $parameter = $operation->ownedParameter[$i];
            if (substr($parameter->direction, 0, 2)=='in') {
                if ($withType && isset($parameter->type) && !($parameter->type instanceof PHP_UML_Metamodel_Datatype)) {
                    if (is_object($parameter->type))
                        $str .= $this->getLinkTo($parameter->type).' ';
                    else if (strcasecmp($parameter->type, 'array')==0)
                        $str .= $this->displayUnresolved($parameter->type).' ';
                }
                if ($parameter->direction=='inout') {
                    $str .= '&';
                }
                if ($parameter->name[0] != '$')
                    $str .= '$';
                $str .= $parameter->name;
                $str .= $this->getDefaultValue($parameter);
                if ($i<($n-1))
                    $str .= ', ';
            }
        }
        $str .= ')';
        return $str;
    }

    protected function getDefaultValue(PHP_UML_Metamodel_TypedElement $obj)
    {
        if ($obj->default!='')
            return '='.$obj->default;
        else
            return '';
    }

    /**
     * Renders a link towards a given element
     * (since datatypes don't own to a "package",  we suppose they are located in
     * the top package)
     * 
     * @param PHP_UML_Metamodel_Classifier $t        The element
     * @param string                       $cssStyle CSS style to use
     * 
     * @return string
     */
    protected function getLinkTo(PHP_UML_Metamodel_Classifier $t, $hideDatatype=true)
    {
        if ($hideDatatype && ($t instanceof PHP_UML_Metamodel_Datatype))
            return '';
        
        $ns = $t instanceof PHP_UML_Metamodel_Datatype ? '' : self::T_NAMESPACE;
        if (isset($t->package)) {
            $ns .= $this->getAbsPath($t->package, self::T_NAMESPACE);
        }
        return $ns.$t->name;
    }

    /**
     * Renders an unresolved type as an HTML span
     * 
     * @param string $type Type, provided as a string
     * 
     * @return string
     */
    protected function displayUnresolved($type)
    {
        return $type;
    }
    

    protected function getTagsAsList(PHP_UML_Metamodel_Stereotype $s)
    {
        return $this->getDocblocks($s, 0);
    }
    
    /**
     * Renders the properties of a given stereotype.
     * Docblocks in $ignoredTag are not shown.
     * 
     * @param PHP_UML_Metamodel_Stereotype $s        A stereotype
     * @param int                          $nbSpacer Number of spacers to add
     * 
     * @return string
     */
    protected function getDocblocks(PHP_UML_Metamodel_Stereotype $s, $nbSpacer = 0)
    {
        $str    = '';
        $spacer = str_repeat(chr(9), $nbSpacer);
        foreach ($s->ownedAttribute as $tag) {
            if (!(in_array($tag->name, $this->ignoredTag))) {
                $str .= $spacer;
                if ($tag->name!='description') {
                    $str .= ' * @'.$tag->name.' ';
                } else {
                    $str .= ' * ';
                }
                if (strlen($tag->value)>0)
                    $str .= str_replace($this->getNl(), $this->getNl().$spacer.' * ', $tag->value);
                if ($tag->name=='description') {
                    $str .= $this->getNl().$spacer.' *';
                }
                $str .= $this->getNl();
            }
        }
        if ($str != '') {
            $str = $spacer.'/**'.$this->getNl().$str.$spacer.' */'.$this->getNl();
        }
        return $str;
    }


    /**
     * Renders the block "Properties" of a package or a class as HTML
     * 
     * @param PHP_UML_Metamodel_NamedElement $p A classifier/a package
     * 
     * @return string
     */
    protected function getPropertyBlock(PHP_UML_Metamodel_NamedElement $p)
    {
        if (empty($p->ownedAttribute))
            return '';

        $str    = '';
        $spacer = chr(9);
        foreach ($p->ownedAttribute as $o) {
                           
            if (!is_null($o->description) && $this->exporter->getDocblocks()) {
                // we add var/return docblocks if they are missing
                $this->addVarDocblock($o);
                $str .= $this->getDocblocks($o->description, 1);
            }
            
            $str .= $spacer;
            if ($o->isReadOnly)
                $str .= 'const ';
            else {
                $str .= $o->visibility.' ';
                if (!$o->isInstantiable)
                    $str .= 'static ';
            }
               
            // type display;
            /*if (is_object($o->type))
                $str .= $this->getLinkTo($o->type).' ';
            else
                $str .= $this->displayUnresolved($o->type);*/
            if ((!empty($o->name)) && ($o->name[0]!='$' && !$o->isReadOnly))
                $str .= '$';
            
            $str .= $o->name.''.$this->getDefaultValue($o).';';

            $str .= $this->getNl().$this->getNl();
        }
        $str .= '';
        return $str;
    }
    
    private function addVarDocblock(PHP_UML_Metamodel_Property $o)
    {
        $found = false;
        foreach ($o->description->ownedAttribute as $tag) {
            if ($tag->name=='var') {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $st       = new PHP_UML_Metamodel_Stereotype();
            $st->name = 'var';
            if (is_object($o->type))
                $st->value = $this->getLinkTo($o->type, false);
            else
                $st->value = $this->displayUnresolved($o->type);
            $o->description->ownedAttribute[] = $st;
        }
    }
    
    private function addReturnDocblock(PHP_UML_Metamodel_Operation $o)
    {
        $found = false;
        foreach ($o->description->ownedAttribute as $tag) {
            if ($tag->name=='return') {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $st       = new PHP_UML_Metamodel_Stereotype();
            $st->name = 'return';
            foreach ($o->ownedParameter as $parameter) {
                if ($parameter->direction != 'in') {
                    if (is_object($parameter->type))
                        $st->value .= $this->getLinkTo($parameter->type, false).' ';
                    else
                        $st->value .= $this->displayUnresolved($parameter->type);
                }
            }
            $o->description->ownedAttribute[] = $st;
        }
    }

    
    /**
     * Renders the block "Function" of a package or a classifier as HTML
     * 
     * @param PHP_UML_Metamodel_NamedElement $p A classifier or a package
     * 
     * @return string
     */
    protected function getFunctionBlock(PHP_UML_Metamodel_NamedElement $p)
    {
        if (empty($p->ownedOperation))
            return'';

        $str    = '';
        $spacer = chr(9);
        
        foreach ($p->ownedOperation as $o) {

            if (!is_null($o->description) && $this->exporter->getDocblocks()) {
                $this->addReturnDocblock($o);
                $str .= $this->getDocblocks($o->description, 1);
            }
            
            $str .= $spacer.($o->visibility).' ';
            if (!$o->isInstantiable)
                $str .= 'static ';
            if ($o->isAbstract)
                $str .= 'abstract ';
            
            $str .= 'function '.$o->name;
            
            /*type hint
            $return = $this->getReturnParam($o);
            if (is_object($return->type))
                $str .= $this->getLinkTo($return->type).' ';
            else
                $str .= $this->displayUnresolved($return->type);*/
            $str .= $this->getParameterList($o, true);

            if ($o->isAbstract || $p instanceof PHP_UML_Metamodel_Interface)
                $str .= ';'.$this->getNl().$this->getNl();
            else
                $str .= $this->getNl().$spacer.'{'.$this->getNl().
                    $spacer.'}'.$this->getNl().$this->getNl();
        }
        $str .= '';
        return $str;
    }
    
    /**
     * Returns the HTML code for the "File" information tag
     * 
     * @param PHP_UML_Metamodel_NamedElement $p An element
     * 
     * @return string
     */
    protected function getFileInfo(PHP_UML_Metamodel_NamedElement $p)
    {
        if (!empty($p->file->package))
            return ''.$this->getAbsPath($p->file->package).$p->file->name.'';
        else
            return '';
    }
    
    protected function getNl()
    {
        return PHP_EOL;
    }
    
    /**
     * Replace the template's placeholders with their value
     *
     * @param string $main   Main HTML content (generated by PHP_UML)
     * @param string $header Navigation HTML content (navig bar)
     * @param string $ns     Title content
     * @param string $name   Element name
     *
     * @return string
     */
    protected function replaceInTpl($main, $header, $ns, $name)
    {
        $str = str_replace('#HEADER', $header, $this->mainTpl);
        $str = str_replace('#NAMESPACE', $ns, $str);
        $str = str_replace('#DETAIL', $main, $str);
        $str = str_replace('#NAME', $this->getTypeName().' '.$name, $str);
        return $str;
    }
    
    protected function getTemplateDirectory()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.self::TEMPLATES_DIRNAME;
    }
    
    protected function save($elementName, $str)
    {
        $fic = $this->getContextPackage()->dir.$elementName.'.'.self::FILE_EXT;
        file_put_contents($fic, $str);
    }
}
?>
