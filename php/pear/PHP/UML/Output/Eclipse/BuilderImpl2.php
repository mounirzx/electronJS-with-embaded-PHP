<?php
/**
 * PHP_UML (PHP_UML_Output_Eclipse_BuilderImpl2)
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
 * Implementation class to create XMI in version 2, adapted for importation into Eclipse
 * 
 * See the interface PHP_UML_Output_Eclipse_Builder for the comments.
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 * @see        PHP_UML_Output_Xmi_Builder
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Eclipse_BuilderImpl2 extends PHP_UML_Output_Xmi_BuilderImpl2
{
    const XMI_VERSION = '2.1';
    const UML_VERSION = '2.1.2';

    const DEFAULT_CLASSIFIER_ATT = ' visibility="public" ';

    public function getXmiHeaderOpen()
    {
        return '';
    }

    public function getModelOpen(PHP_UML_Metamodel_Package $model)
    {
        return '<uml:Model xmi:type="uml:Model" name="'.$model->name.'"
            xmi:version="2.1" xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
            xmlns:uml="http://www.eclipse.org/uml2/2.1.0/UML"
            xmi:id="'.$model->id.'" '.self::DEFAULT_CLASSIFIER_ATT.'>';
    }
    
    public function getXmiHeaderClose()
    {
        return '';
    }
    
    public function getRealizations(PHP_UML_Metamodel_Class $client)
    {
        $str = '';
        foreach ($client->implements as &$rclass) {
            if (is_object($rclass)) {
                $str .= '<packagedElement xmi:type="uml:Realization" '.
                'xmi:id="'.self::getUID().'" '.
                'client="'.$client->id.'" '.
                'supplier="'.$rclass->id.'" />';
            }
        }
        return $str;
    }
}
?>
