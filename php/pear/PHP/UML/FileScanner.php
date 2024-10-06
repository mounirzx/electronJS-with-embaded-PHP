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
 * @version  SVN: $Revision: 105 $
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2009-06-04 19:48:27 +0200 (jeu., 04 juin 2009) $
 */


/**
 * A superclass for scanning files and folders. It does nothing but browsing
 * recursively the file system tree, given a list of entry folders. At least
 * one folder must be provided.
 * It can be seen as an extension of RecursiveDirectoryIterator, upon which
 * it is based.
 * 
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * 
 */
abstract class PHP_UML_FileScanner
{

    /**
     * List of directories to scan
     *
     * @var array
     */
    protected $directories = array();
    
    /**
     * List of files to scan
     *
     * @var array
     */
    protected $files = array();
    
    /**
     * Allowed path-/file-names (possible wildcards are ? and *)
     * 
     * @var array
     */
    protected $matchPatterns = array('*.php');
    
    /**
     * Ignored directories (possible wildcards are ? and *)
     *
     * @var array();
     */
    protected $ignorePatterns = array();


    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * This function will be called every time the scanner meets
     * a new file (while it looks into the folders), as well as for
     * each file defined in the property $files.
     * It's up to the subclass to define the treatment to be done.
     *
     * @param mixed  $basedir  Directory path
     *
     * @param string $filename File name
     */
    abstract function tickFile($basedir, $filename);

    /**
     * Starts the scan
     *
     */
    public function scan()
    {
        // We parse the directories
        foreach ($this->directories as $pathItem) {
            $baseDir  = realpath($pathItem);
            $trailing = substr($baseDir, -1);

            if ($baseDir != false && is_dir($baseDir) && is_readable($baseDir)) {

                if ($trailing != '/' && $trailing != '\\')
                     $baseDir .= DIRECTORY_SEPARATOR;
                
                $objects = new RecursiveIteratorIterator(
                    new PHP_UML_FilePatternFilterIterator(
                        new RecursiveDirectoryIterator($baseDir), $this->ignorePatterns, $this->matchPatterns
                    )
                );

                $baseDirPos = strlen($baseDir);
                foreach ($objects as $ptr) {
                    $relativePath = substr($ptr->getPathname(), $baseDirPos);
                    $this->tickFile($baseDir, $relativePath);
                }
            }
            else
                $this->raiseUnknownFolderException($pathItem);
        }

        // We parse the files
        foreach ($this->files as $filenameItem) {
            $filenameItem = realpath($filenameItem);
            $baseDir      = dirname($filenameItem).DIRECTORY_SEPARATOR;
            $baseName     = basename($filenameItem);
            if ($filenameItem != false)
                $this->tickFile($baseDir, $baseName);
        }

    }

    /**
     * Can be overriden to treat unknown folder exception
     *
     * @param string $basedir Directory name
     */
    public function raiseUnknownFolderException($basedir)
    {
    }
    
    public function setFiles(array $files)
    {
        $this->files = $files;
    }
    
    public function setDirectories(array $directories)
    {
        $this->directories = $directories;
    }
    
    public function setMatchPatterns(array $patterns)
    {
        $this->matchPatterns = $patterns;
    }
    
    public function setIgnorePatterns(array $patterns)
    {
        $this->ignorePatterns = $patterns;
    }
}
?>
