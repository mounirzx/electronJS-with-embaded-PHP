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
 * @version  SVN: $Revision: 176 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-19 00:03:11 +0200 (lun., 19 sept. 2011) $
 */

/**
 * General class for an renderer in the HtmlNew implementation
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage HtmlNew
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_HtmlNew_DocElement extends PHP_UML_Output_ApiRenderer
{
    const FILE_EXT = 'htm';
    
    const RESOURCES_DIRNAME = '$resources';
    const HELP_FILENAME     = 'help';
    const INDEX_FILENAME    = 'index';
    const INDEXALL_FILENAME = 'index-all';
    const MENU_FILENAME     = 'menu';
    const JS_MAIN_NAME      = 'MainList';
    const TEMPLATES_DIRNAME = 'templates';

    /**
     * Constructor
     *
     * @param PHP_UML_Output_ExporterAPI $exporter Reference to an exporter
     */
    public function __construct(PHP_UML_Output_ExporterAPI $exporter)
    {
        parent::__construct($exporter);
        $this->mainTpl = $this->getTemplate('main.htm');
    }
    
    protected function getDescription(PHP_UML_Metamodel_Stereotype $s, $annotatedElement='')
    {
        $tag = PHP_UML_Metamodel_Helper::getStereotypeTag($s, 'description');
        if (!is_null($tag))
            return nl2br(htmlspecialchars($tag->value));
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
        $n      = count($operation->ownedParameter);
        $pieces = array();
        for ($i=0; $i<$n; $i++) {
            $parameter = $operation->ownedParameter[$i];
            if (substr($parameter->direction, 0, 2)=='in') {
                $str = '';
                if ($withType && isset($parameter->type)) {
                    if (is_object($parameter->type))
                        $str .= $this->getLinkTo($parameter->type).' ';
                    else
                        $str .= $this->displayUnresolved($parameter->type);
                }
                if ($parameter->direction=='inout') {
                    $str .= '&#38;';
                }
                $str .= $parameter->name;
                $str .= $this->getDefaultValue($parameter);
                $pieces[] = $str;
            }
        }
        return '('.implode(',', $pieces).')';
    }

    protected function getDefaultValue(PHP_UML_Metamodel_TypedElement $obj)
    {
        if ($obj->default!='')
            return '<span class="defVal"> = '.htmlentities($obj->default, ENT_QUOTES).'</span>';
        else
            return '';
    }

    /**
     * Renders a HTML hyperlink towards a given element
     * (since datatypes don't own to a "package",  we suppose they are located in
     * the top package)
     * 
     * @param PHP_UML_Metamodel_Classifier $t        The element
     * @param string                       $cssStyle CSS style to use
     * 
     * @return string
     */
    protected function getLinkTo(PHP_UML_Metamodel_Classifier $t, $cssStyle='link')
    {
        $loc = '';
        $ns  = '';
        if (isset($t->package)) {
            $loc = $this->getAbsPath($t->package);
            $ns  = $this->getAbsPath($t->package, self::T_NAMESPACE);
        }
        return '<a href="'.$this->getContextPackage()->rpt.$loc.self::getObjPrefix($t).$t->name.'.'.
            self::FILE_EXT.'" class="'.$cssStyle.'">'.$ns.$t->name.'</a>';
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
        return '<span class="link">'.$type.'</span> ';
    }
    

    /**
     * Renders the properties of a given stereotype as an HTML list (LI tags).
     * Docblocks in $ignoredTag are not shown, as well as "return" tag with only a type
     * 
     * @param PHP_UML_Metamodel_Stereotype $s A stereotype
     * 
     * @return string
     */
    protected function getTagsAsList(PHP_UML_Metamodel_Stereotype $s)
    {
        $str = '';
        foreach ($s->ownedAttribute as $tag) {
            if (!(in_array($tag->name, $this->ignoredTag) || ($tag->name=='return' && strpos($tag->value, ' ')===false))) {
                if ($tag->name!='description') {
                    $str .= '<li class="smaller">';
                    $str .= '@'.$tag->name.' ';
                } else {
                    $str .= '<li>';
                }
                if (strlen($tag->value)>0)
                    $str .= nl2br(htmlspecialchars($tag->value));
                $str .= '</li>';
            }
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

        $str  = '<h2>Properties</h2>';
        $str .= '<ul class="summary">';
        foreach ($p->ownedAttribute as $o) {
            $str .= '<li class="Collapsed" id="'.$this->generatePropertyId($o).'">';
            $str .= '<a href="javascript:void(0);" class="'.
                $this->getPropertyStyle($o->visibility).'" target="main">'.
                $o->name.'</a>';

            $str .= '<ul class="description"><li>';
            $str .= ucfirst($o->visibility).' ';
            if (!$o->isInstantiable)
                $str .= 'static ';
            if ($o->isReadOnly)
                $str .= 'const ';
            if (is_object($o->type))
                $str .= $this->getLinkTo($o->type).' ';
            else
                $str .= $this->displayUnresolved($o->type);
            $str .= '<span class="smallTitle">'.$o->name.'</span>'.$this->getDefaultValue($o).'</li>';
            if (!is_null($o->description)) {
                $str .= $this->getTagsAsList($o->description);
            }
            $str .= $this->getFileInfo($o);
            $str .= '</ul>';
     
            $str .= '</li>';
        }
        $str .= '</ul>';
        return $str;
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

        $str  = '<h2>Functions</h2>';
        $str .= '<ul class="summary">';
        foreach ($p->ownedOperation as $o) {
            $fullName = $this->getParameterList($o, true);

            $str .= '<li class="Collapsed" id="'.$this->generateFunctionId($o).'">';
            $str .= '<a href="javascript:void(0);" class="'.$this->getFunctionStyle($o->visibility);
            if ($o->isAbstract)
                $str .= ' abstract';
            $str .= '" target="main">'.$o->name.'</a>'.$fullName;
            
            $str .= '<ul class="description"><li>';
            $str .= ucfirst($o->visibility).' ';
            if (!$o->isInstantiable)
                $str .= 'static ';
            if ($o->isAbstract)
                $str .= 'abstract ';
            $return = $this->getReturnParam($o);
            if (!empty($return)) {
                if (is_object($return->type))
                    $str .= $this->getLinkTo($return->type).' ';
                else
                    $str .= $this->displayUnresolved($return->type);
            }
            $str .= '<span class="smallTitle">'.$o->name.'</span>'.$fullName.'</li>';

            if (!is_null($o->description)) {
                $str .= $this->getTagsAsList($o->description);
            }
            foreach ($this->getAllImplemented($p) as $ai) {
                foreach ($ai->ownedOperation as $aiO) {
                    if ($aiO->name == $o->name && !empty($aiO->description)) {
                        $txt = $this->getDescription($aiO->description, $aiO->id);
                        if ($txt!='')
                            $str .= '<li>'.$txt.'<br/><span class="note">(copied from interface '.$this->getLinkTo($ai).')</span></li>';
                    }
                }
            }
            foreach ($this->getAllInherited($p) as $ai) {
                foreach ($ai->ownedOperation as $aiO) {
                    if ($aiO->name == $o->name && !empty($aiO->description)) {
                        $txt = $this->getDescription($aiO->description, $aiO->id);
                        if ($txt!='')
                            $str .= '<li>'.$txt.'<br/><span class="note">(copied from class '.$this->getLinkTo($ai).')</span></li>';
                    }
                }
            }
            $str .= $this->getFileInfo($o);
            $str .= '</ul>';

            $str .= '</li>';
        }
        $str .= '</ul>';
        return $str;
    }

    /**
     * Returns the HTML for the link "Package" in the navigation bar
     * 
     * @param string $rel A prefix to add to the hyperlink (eg: ../)
     * 
     * @return string
     */
    protected function getNavigParentPackage($rel='')
    {
        return '<li><a href="'.$rel.self::PACKAGE_FILENAME.'.'.self::FILE_EXT.'" class="top">Package</a></li>';
    }

    /**
     * Returns the HTML code for the common items of the navigation bar
     * 
     * @return string
     */
    protected function getCommonLinks()
    {
        return '<li><a href="javascript:toggler.toggleAll(\''.self::JS_MAIN_NAME.'\', \'btnToggle\')" class="expandAllBtn" id="btnToggle">Expand all</a></li>'.
            '<li><a href="'.$this->getContextPackage()->rpt.self::HELP_FILENAME.'.'.self::FILE_EXT.'" class="helpBtn">Help</a></li>'.
            '<li><a href="'.$this->getContextPackage()->rpt.self::INDEXALL_FILENAME.'.'.self::FILE_EXT.'" class="indexAllBtn">Index</a></li>';
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
            return '<li>File: '.$this->getAbsPath($p->file->package).$p->file->name.'</li>';
        else
            return '';
    }

    protected function getPropertyStyle($visibility)
    {
        return 'property-'.substr($visibility, 0, 3);
    }
    
    protected function getFunctionStyle($visibility)
    {
        return 'method-'.substr($visibility, 0, 3);
    }
    
    /**
     * Replace the template's placeholders with their value
     *
     * @param string $main Main HTML content (generated by PHP_UML)
     * @param string $nav  Navigation HTML content (navig bar)
     * @param string $tit  Title content
     * @param string $name Element name
     *
     * @return string
     */
    protected function replaceInTpl($main, $nav, $tit, $name)
    {
        $str = str_replace('#NAVIG', $nav, $this->mainTpl);
        $str = str_replace('#TITLE', $tit, $str);
        $str = str_replace('#DETAIL', $main, $str);
        $str = str_replace('#RELPATHTOP', $this->getContextPackage()->rpt, $str);
        $str = str_replace('#NAME', $this->getTypeName().' '.$name, $str);
        $str = str_replace('#CURDATE', date("M j, Y, G:i:s O"), $str);
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
