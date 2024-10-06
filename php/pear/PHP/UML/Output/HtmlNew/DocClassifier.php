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
 * @version  SVN: $Revision: 169 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-12 01:28:43 +0200 (lun., 12 sept. 2011) $
 */

/**
 * Implementation of the HTML renderer for a classifier (class, interface, datatype)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage HtmlNew
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_HtmlNew_DocClassifier extends PHP_UML_Output_HtmlNew_DocElement
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

        $nav = $this->getNavigationBlock($i);

        $tit = $this->getTitleBlock($i);

        $this->ignoredTag = array('var');    // we ignore all 'var' docblocks
        
        $str  = $this->getDescriptionBlock($p);
        $str .= $this->getPropertyBlock($p);
        $str .= $this->getFunctionBlock($p);

        $str = $this->replaceInTpl($str, $nav, $tit, $p->name);

        $this->save($this->getPrefix().$p->name, $str);
    }

    /**
     * Return the HTML code for the navigation bar
     * 
     * @param int $i Index of the current element in the the array containing all
     *               the elements of the current package
     *
     * @return string A list of HTML LI
     */
    private function getNavigationBlock($i)
    {
        $prev = $this->getPreviousElement($i);
        $next = $this->getNextElement($i);
        $type = $this->getTypeName();

        $str = $this->getCommonLinks();
        if (!empty($prev))
            $str .= '<li><a href="'.$this->getPrefix().$prev->name.'.'.self::FILE_EXT.'" class="left">Prev '.$type.'</a></li>';
        $str .= $this->getNavigParentPackage();
        if (!empty($next))
            $str .= '<li><a href="'.$this->getPrefix().$next->name.'.'.self::FILE_EXT.'" class="right">Next '.$type.'</a></li>';

        return $str;
    }

    private function getTitleBlock($i)
    {
        $elem = $this->getCurrentElement($i);
        $str  = '<h1 class="'.$this->getStyleName();
        if ($elem->isAbstract)
            $str .= ' abstract';
        return $str.'">'.$elem->name.'</h1>';
    }

    private function getDescriptionBlock(PHP_UML_Metamodel_Classifier $p)
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
        if ($p->isAbstract)
            $typeName = 'abstract '.$typeName;
        if ($p->isReadOnly)
            $typeName = 'final '.$typeName;

        $str = '<div class="description"><p>'.ucfirst($typeName).
            ' <span class="title">'.$this->getQualifiedName($p).'</span>';
        $css = 'link2';
        if (!empty($p->superClass[0])) {
            $str .= ' extends ';
            if (is_object($p->superClass[0]))
                $str .= $this->getLinkTo($p->superClass[0], $css);
            else
                $str .= $this->displayUnresolved($p->superClass[0]);
        }
        if (isset($p->implements)) {    
            $nbImpl = count($p->implements);
            if ($nbImpl>0) {
                $str .= ' implements ';
                for ($i=0; $i<$nbImpl; $i++) {
                    if (!empty($p->implements[$i])) {
                        if (is_object($p->implements[$i]))
                            $str .= $this->getLinkTo($p->implements[$i], $css);
                        else
                            $str .= $this->displayUnresolved($p->implements[$i]);
                        if ($i<$nbImpl-1)
                            $str .= ', ';
                    }
                }
            }
        }
        $str .= '</p>';

        $str .= '<ul class="description">';

        if (!is_null($p->description)) {
            $str .= $this->getTagsAsList($p->description);
        }

        if ($nbAllImplemented>0) {
            $str .= '<li>All implemented interfaces: ';
            foreach ($allImplemented as $ai) {
                $str .= $this->getLinkTo($ai, $css).', ';
            }
            $str  = substr($str, 0, -2);
            $str .= '</li>';
        }

        if ($nbAllInheriting>0) {
            $str .= '<li>All subclasses: ';
            foreach ($allInheriting as $ai) {
                $str .= $this->getLinkTo($ai, $css).', ';
            }
            $str  = substr($str, 0, -2);
            $str .= '</li>';
        }

        if ($nbAllImplementing>0) {
            $str .= '<li>All implementing classes: ';
            foreach ($allImplementing as $ai) {
                $str .= $this->getLinkTo($ai, $css).', ';
            }
            $str  = substr($str, 0, -2);
            $str .= '</li>';
        }

        $str .= $this->getFileInfo($p);

        $str .= '</ul></div>';

        if ($nbAllInherited>0) {
            $str .= '<dl class="inheritTree">';
            $sum  = -15;
            $img  = '<img src="'.$this->getContextPackage()->rpt.self::RESOURCES_DIRNAME.'/inherit.gif" alt="extended by "/>';
            foreach ($allInherited as $ai) {
                $str     .= '<dd style="padding-left:'.$sum.'px">';
                $fullName = $this->getLinkTo($ai, $css);
                if ($sum>0)
                    $str .= $img;
                $sum += 15 + 2*(strlen(strstr($fullName, '>'))-5);
                $str .= $fullName.'</dd>';
            }
            $str .= '<dd style="padding-left:'.$sum.'px" class="current">'.$img;
            $str .= $this->getAbsPath($p->package, self::T_NAMESPACE).$p->name.'</dd>';
            $str .= '</dl>';
        }
        return $str;
    }
}
?>
