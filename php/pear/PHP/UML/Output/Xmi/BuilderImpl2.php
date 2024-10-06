<?php
/**
 * PHP_UML (PHP_UML_Output_Xmi_BuilderImpl2)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 174 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-15 03:17:32 +0200 (jeu., 15 sept. 2011) $
 */

/**
 * Implementation class to create XMI in version 2
 * 
 * See the interface PHP_UML_Output_Xmi_Builder for the comments.
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 * @see        PHP_UML_Output_Xmi_Builder
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Xmi_BuilderImpl2 extends PHP_UML_Output_Xmi_AbstractBuilder
{
    const XMI_VERSION = '2.1';
    const UML_VERSION = '2.1.2';

    const DEFAULT_CLASSIFIER_ATT = ' visibility="public" isAbstract="false" ';
    
    /**
     * PHP_UML UML Profile (TODO) 
     * @var string
     */
    public $profile = '';

    public function getXmiHeaderOpen()
    {
        return '<xmi:XMI xmi:version="'.self::XMI_VERSION.'" 
            xmlns:uml="http://schema.omg.org/spec/UML/'.self::UML_VERSION.'"
            xmlns:xmi="http://schema.omg.org/spec/XMI/'.self::XMI_VERSION.'"
            xmlns:php="http://schemas/phpdoc/'.self::getUID().'">
                <xmi:Documentation exporter="'.self::EXPORTER_NAME.'"/>';
    }

    public function getModelOpen(PHP_UML_Metamodel_Package $model)
    {
        return '<uml:Model xmi:type="uml:Model" name="'.$model->name.'"
            xmi:id="'.$model->id.'" '.self::DEFAULT_CLASSIFIER_ATT.'>';
    }

    public function getModelClose()
    {
        return '</uml:Model>';
    }
    
    public function getXmiHeaderClose()
    {
        return '</xmi:XMI>';
    }
    
    public function getPackageOpen(PHP_UML_Metamodel_Package $package)
    { 
        $str = '<packagedElement xmi:type="uml:Package" xmi:id="'.$package->id.
            '" name="'.$package->name.'">';
        if (isset($package->description)) {
            $str .= $this->getComment($package->description, $package->id);
        }
        return $str;
    }
    
    public function getPackageClose()
    {
        return '</packagedElement>';
    }
    
    public function getNamespaceOpen()
    {
        return '';
    }
    
    public function getNamespaceClose()
    {
        return '';
    }
    
    public function getDatatype(PHP_UML_Metamodel_Datatype $type)
    {
        $str = '<packagedElement xmi:type="uml:DataType"'.
            ' xmi:id="'.$type->id.'"'.
            ' name="'.$type->name.'">';
        if (isset($type->description))
            $str .= $this->getComment($type->description, $type->id);
        return $str.'</packagedElement>';
    }
    
    public function getSubsystemOpen(PHP_UML_Metamodel_Package $package)
    {
        return '<packagedElement xmi:type="uml:Component" xmi:id="'.
            $package->id.'" name="'.$package->name.
            '" '.self::DEFAULT_CLASSIFIER_ATT.'>';
    }
    
    public function getSubsystemClose()
    {
        return '</packagedElement>';
    }

    public function getClass(PHP_UML_Metamodel_Class $class)
    {
        $strRealization = '';

        $str = '<packagedElement xmi:type="uml:Class" name="'.$class->name.'" xmi:id="'.
            $class->id.'" visibility="package"
            isAbstract="'.($class->isAbstract?'true':'false').'">';

        $str .= $this->getGeneralizations($class);

        $strRealization .= $this->getRealizations($class);

        foreach ($class->ownedAttribute as &$property)
            $str .= $this->getProperty($property);

        foreach ($class->ownedOperation as &$operation)
            $str .= $this->getOperation($operation);

        if (isset($class->description))
            $str .= $this->getComment($class->description, $class->id);

        $str .= '</packagedElement>';

        return $str.$strRealization;
    }
 
    public function getInterface(PHP_UML_Metamodel_Interface $interface)
    {
        $str = '<packagedElement xmi:type="uml:Interface" '.
            ' name="'.$interface->name.'" xmi:id="'.$interface->id.'"'.
            ' visibility="package" isAbstract="true">';

        foreach ($interface->ownedOperation as &$operation)
            $str .= $this->getOperation($operation);

        $str .= $this->getGeneralizations($interface);

        if (isset($interface->description))
            $str .= $this->getComment($interface->description, $interface->id);

        $str .= '</packagedElement>';
        return $str;
    }

    public function getRealizations(PHP_UML_Metamodel_Class $client)
    {
        $str = '';
        foreach ($client->implements as &$rclass) {
            if (is_object($rclass)) {
                $str .= '<packagedElement xmi:type="uml:Realization" '.
                'xmi:id="'.self::getUID().'" '.
                'client="'.$client->id.'" '.
                'supplier="'.$rclass->id.'" '.
                'realizingClassifier="'.$rclass->id.'"/>';
            }
        }
        return $str;
    }

    public function getGeneralizations(PHP_UML_Metamodel_Type $client)
    {
        $str = '';
        foreach ($client->superClass as &$gclass) {
            if (is_object($gclass)) {
                $str .= '<generalization xmi:type="uml:Generalization" '.
                    'xmi:id="'.self::getUID().'" '.
                    'general="'.$gclass->id.'"/> ';
            }
        }
        return $str;
    }

    public function getProperty(PHP_UML_Metamodel_Property $property)
    {
        $str = '<ownedAttribute xmi:type="uml:Property"'.
            ' name="'.$property->name.'"'.
            ' xmi:id="'.$property->id.'"'.
            ' visibility="'.$property->visibility.'" ';
 
        if (!$property->isInstantiable)
            $str .= ' isStatic="true"';
        if ($property->isReadOnly)
            $str .= ' isReadOnly="true" ';

        $str .= '>'.$this->getParameterType($property);
        
        if (isset($property->description))
            $str .= $this->getComment($property->description, $property->id);
 
        $str .= '</ownedAttribute>';
        return $str;
    }

    public function getOperation(PHP_UML_Metamodel_Operation $operation)
    {
        $str = '<ownedOperation xmi:id="'.$operation->id.'" 
            name="'.$operation->name.'" visibility="'.$operation->visibility.'" ';

        if (!$operation->isInstantiable)
            $str .= ' isStatic="true"';
        if ($operation->isAbstract)
            $str .= ' isAbstract="true"';

        $str .= '>';

        foreach ($operation->ownedParameter as &$parameter) {
            $str .= $this->getParameter($parameter);
        }

        if (isset($operation->description))
            $str .= $this->getComment($operation->description, $operation->id);

        $str .= '</ownedOperation>';

        return $str;
    }
        
    public function getParameter(PHP_UML_Metamodel_Parameter $parameter)
    {   
        return '<ownedParameter xmi:id="'.$parameter->id.'" '.
            'name="'.$parameter->name.'" direction="'.$parameter->direction.'">'.
            $this->getParameterType($parameter).
            '</ownedParameter>';
    }
    
    public function getParameterType(PHP_UML_Metamodel_TypedElement $parameter)
    {
        $str = '';
        $id  = self::getUID();

        if (isset($parameter->type->id))
            $str = '<type xmi:idref="'.$parameter->type->id.'"/>';

        if ($parameter->default!='')
            $str .= '<defaultValue xmi:type="uml:LiteralString" xmi:id="'.$id.'"'.
                ' value="'.htmlspecialchars($parameter->default, ENT_QUOTES).'" />';

        return $str;
    }
    
    public function getArtifact(PHP_UML_Metamodel_Artifact $file, &$mf = array())
    {
        $id   = $file->id;
        $file = htmlspecialchars($file->name);
        $name = basename($file);
        $str  = '
            <packagedElement xmi:type="uml:Artifact"'.
            ' xmi:id="'.$id.'"'.
            ' name="'.$name.'" '.
            ' stereotype="'.self::getUID('stereotype_'.self::PHP_FILE).'" '.
            self::DEFAULT_CLASSIFIER_ATT.' >';

        foreach ($mf as $class) {
            if ($class instanceof PHP_UML_Metamodel_Classifier) {
                $id_supplier = $class->id;
                $str        .= self::getManifestation($id, $id_supplier, 'source');
            }
        }

        $str .= '</packagedElement>';
        return $str;
    }
    
    public function getComponentOpen(PHP_UML_Metamodel_Package $package, array $provided, array $required)
    {       
        $str = '
            <packagedElement xmi:type="uml:Component"'.
            ' xmi:id="'.$package->id.'"'.
            ' name="'.$package->name.'" '.
            self::DEFAULT_CLASSIFIER_ATT.' >';

        foreach ($provided as $c) {
            switch (get_class($c)) {
            case 'PHP_UML_Metamodel_Interface':
            case 'PHP_UML_Metamodel_Class':
                $id          = self::getUID('CV_provided_'.$c->name);
                $id_provided = $c->id;
                $str        .= '<provided xmi:id="'.$id.'" xmi:idref="'.$id_provided.
                    '" name="'.$c->name.'"/>';
            }
        }
        foreach ($required as $c) {
            $id          = self::getUID('CV_required_'.$c->name);
            $id_provided = $c->id;
            $str        .= '<required xmi:id="'.$id.'" xmi:idref="'.$id_provided.'" name="'.$c->name.'"/>';
        }
        return $str;
    }

    public function getComponentClose()
    {
        return '</packagedElement>';
    }

    /**
     * Formates a Profile adapted to PHP_UML.
     *
     * TODO. Experimental.
     *
     * @return string XMI Code
     */
    public function getProfile()
    {
        return file_get_contents('../Metamodel/phpdoc.profile.xmi').
                '<profileApplication xmi:type="uml:ProfileApplication" xmi:id="'.self::getUID().'">'.
            '<appliedProfile xmi:idref="PHP_UML_phpdoc_1"/>'.
            '</profileApplication>';
    }

    public function getComment(PHP_UML_Metamodel_Stereotype $s, $annotatedElement='')
    {
        $tag = PHP_UML_Metamodel_Helper::getStereotypeTag($s, 'description');
        if(!is_null($tag))
            return '<ownedComment xmi:type="uml:Comment"
                xmi:id="'.self::getUID().'" annotatedElement="'.$annotatedElement.
                '"><body>'.htmlspecialchars($tag->value).'</body></ownedComment>';
        else
            return '';
    }

    public function getStereotypes()
    {
        return '';
    }
    
    /**
     * Gets all the elements contained in a stereotype
     * Note: the property "documentation" is not discarded (we will have
     * it as an "ownedComent" tag, instead; see getComment())
     *
     * @param PHP_UML_Metamodel_Stereotype $s Stereotype
     *
     * @return string
     */
    public function getStereotypeInstance(PHP_UML_Metamodel_Stereotype $s)
    {
        $str = '';
        foreach ($s->ownedAttribute as $tag) {
            if($tag->value!='' && $tag->name!='description')
                $str .= $this->getMetadata($tag);
        }

        if ($str!='')
            return '<'.$s->profile.':'.$s->name.
                ' base_Element="'.$s->element->id.'">'.
                $str.'</'.$s->profile.':'.$s->name.'>';
        else
            return '';
    }
    
    public function getMetadata(PHP_UML_Metamodel_Tag $tag)
    {
        return '<'.$tag->name.'>'.htmlspecialchars($tag->value).'</'.$tag->name.'>'; 
    }
 
    /**
     * Generates a manifestation element (= the link between a class and the
     * artifact where the class is defined)
     *
     * @param string $client   Name of the client
     * @param string $supplier Name of the supplier
     * @param string $name     Name of the relation
     * 
     * @return string XMI code
     */
    public function getManifestation($client, $supplier, $name)
    {
        return '<manifestation xmi:type="uml:Manifestation" '.
            'xmi:id="'.self::getUID().'" '.
            'client="'.$client.'" supplier="'.$supplier.'" '.
            'utilizedElement="'.$supplier.'" name="'.$name.'"/>'; 
    }
    
}
?>
