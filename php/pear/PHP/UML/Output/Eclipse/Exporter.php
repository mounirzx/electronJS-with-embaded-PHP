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
 * This class generates a specific XMI file that can be imported into Eclipse
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Eclipse_Exporter extends PHP_UML_Output_Xmi_Exporter
{
    /**
     * Getter for the XMI factory. Overrides the parent.
     *
     */
    protected function getXmiBuilder()
    {
        $builder = PHP_UML_Output_Eclipse_AbstractBuilder::getInstance($this->xmiVersion);
        $builder->setEncoding($this->encoding);
        $builder->setLogicalView($this->logicalView);
        $builder->setComponentView($this->componentView);
        $builder->setDeploymentView($this->deploymentView);
        $builder->setStereotypes(false);
        
        return $builder;
    }
}
?>
