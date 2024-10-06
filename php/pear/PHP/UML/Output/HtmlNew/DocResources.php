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
 * Rendering of the resources
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage HtmlNew
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_HtmlNew_DocResources extends PHP_UML_Output_HtmlNew_DocElement
{
    public function render($p)
    {
        $dir     = $this->getContextPackage()->dir;
        $baseSrc = dirname(__FILE__).DIRECTORY_SEPARATOR;
        
        $index     = $this->getTemplate(self::INDEX_FILENAME.'.'.self::FILE_EXT);
        $modelName = $p->name;
        $index     = str_replace('#MODELNAME', $modelName, $index);
        file_put_contents($dir.self::INDEX_FILENAME.'.'.self::FILE_EXT, $index);
        
        $src = dirname(__FILE__).DIRECTORY_SEPARATOR.self::TEMPLATES_DIRNAME.DIRECTORY_SEPARATOR.self::HELP_FILENAME.'.'.self::FILE_EXT;
        copy($src, $dir.self::HELP_FILENAME.'.'.self::FILE_EXT);
        
        $src  = $baseSrc.self::RESOURCES_DIRNAME;
        $dest = $dir.self::RESOURCES_DIRNAME;
        if (!file_exists($dest)) {
            mkdir($dest);
        }
        if (file_exists($src)) {
            $iterator = new DirectoryIterator($src);
            foreach ($iterator as $file) {
                if ($file->isFile())
                    copy($file->getPathname(), $dest.DIRECTORY_SEPARATOR.$file->getFilename());
            }
        }
    }
}
?>
