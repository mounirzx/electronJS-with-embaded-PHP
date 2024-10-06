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
 * Implementation of the HTML renderer for the treeview menu (on the left panel)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage HtmlNew
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_HtmlNew_DocMenu extends PHP_UML_Output_HtmlNew_DocElement
{
    /**
     * Generates and saves the HTML menu
     * 
     * @param PHP_UML_Metamodel_Package $p Starting package
     */
    public function render($p)
    {
        $tpl = $this->getTemplate(self::MENU_FILENAME.'.'.self::FILE_EXT);
        
        $str  = '<ul class="TreeView" id="MainList">';
        $str .= $this->getContent($p);
        $str .= '</ul>';

        $str = str_replace('#MENU', $str, $tpl);

        $this->save(self::MENU_FILENAME, $str);
    }

    /**
     * Returns the HTML content for a whole model
     * 
     * @param PHP_UML_Metamodel_Package $p Starting package
     * 
     * @return string
     */
    private function getContent(PHP_UML_Metamodel_Package $p)
    {
        return $this->getPackage($p);
    }

    /**
     * Returns the HTML content for a given package
     * 
     * @param PHP_UML_Metamodel_Package $p     Package
     * @param string                    $path  Relative path to the current package
     * @param int                       $level Recursion level
     * 
     * @return string
     */
    private function getPackage(PHP_UML_Metamodel_Package $p, $path='', $level=0)
    {
        $pt    = $level>0 ? 'package' : 'model';
        $empty = (count($p->nestedPackage)==0 && count($p->ownedType)==0 && count($p->ownedOperation)==0 && count($p->ownedAttribute)==0);
        $str   = ($level==0 ? '<li class="Expanded">' : ($empty ? '<li>' : '<li class="Collapsed">'));
        $str  .= '<a href="'.$path.self::PACKAGE_FILENAME.'.'.self::FILE_EXT.'" class="'.$pt.'" target="main">'.$p->name.'</a>';
        if (!$empty) {
            $str .= '<ul>';
            foreach ($p->nestedPackage as $np) {
                $npDir = $path.$np->name;
                $str  .= $this->getPackage($np, $npDir.'/', $level+1);
            }
            foreach ($p->ownedType as $c) {
                $str .= $this->getClassifierItem($c, $path, $level);
            }
            foreach ($p->ownedAttribute as $a) {
                $str .= $this->getPackagePropertyItem($a, $path);
            }
            foreach ($p->ownedOperation as $o) {
                $str .= $this->getPackageOperationItem($o, $path);
            }

            $str .= '</ul>';
        }
        $str .= '</li>';
        return $str;
    }
    

    protected function getPackagePropertyItem(PHP_UML_Metamodel_Property $p, $path)
    {
        return '<li>'.
            '<a href="'.$path.self::PACKAGE_FILENAME.'.'.self::FILE_EXT.'#'.$this->generatePropertyId($p).
            '" class="property" target="main">'.$p->name.'</a>'.
            '</li>';
    }

    protected function getPackageOperationItem(PHP_UML_Metamodel_Operation $o, $path)
    {
        return '<li>'.
          '<a href="'.$path.self::PACKAGE_FILENAME.'.'.self::FILE_EXT.'#'.$this->generateFunctionId($o).
          '" class="method" target="main">'.$o->name.$this->getParameterList($o).'</a>'.
          '</li>';
    }

    protected function getClassifierItem(PHP_UML_Metamodel_Classifier $c, $path, $level)
    {
        $str = '';
        if ($level>0 || !in_array($c->name, $this->hiddenClassifiers)) {
            $str .= '<li>'.
                '<a href="'.$path.self::getObjPrefix($c).$c->name.'.'.self::FILE_EXT.
                '" class="'.self::getObjStyle($c);
            if ($c->isAbstract)
                $str .= ' abstract';
            $str .= '" target="main">'.$c->name.'</a></li>';
        }
        return $str;
    }
}
?>
