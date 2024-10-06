<?php
/**
 * PHP_UML (PHP_UML_Output_Xmi_BuilderImpl1)
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
 * Implementation class to create XMI in version 1
 *
 * See the interface PHP_UML_Output_Xmi_Builder for the comments
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 * @see        PHP_UML_Output_Xmi_Builder
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Xmi_BuilderImpl1 extends PHP_UML_Output_Xmi_AbstractBuilder
{
    const XMI_VERSION = '1.2';
    const UML_VERSION = '1.4';

    const DEFAULT_CLASSIFIER_ATT = ' visibility="public" isAbstract="false" 
        isSpecification="false" isRoot="false" isLeaf="false" ';

    public function getXmiHeaderOpen()
    {
        return '<XMI xmi.version="'.self::XMI_VERSION.'"
            xmlns:UML="http://www.omg.org/spec/UML/1.4">
            <XMI.header>
                <XMI.documentation>
                    <XMI.exporter>'.self::EXPORTER_NAME.'</XMI.exporter>
                </XMI.documentation>
                <XMI.metamodel XMI.name="UML" XMI.version="'.self::XMI_VERSION.'" />
            </XMI.header>
            <XMI.content>';
    }
    
    public function getXmiHeaderClose()
    {
        return '</XMI.content></XMI>';
    }
    
    public function getModelOpen(PHP_UML_Metamodel_Package $model)
    {
        return '<UML:Model name="'.$model->name.'" xmi.id="'.$model->id.'" '.
            self::DEFAULT_CLASSIFIER_ATT.'>';         
    }

    public function getStereotypes()
    {
        $str = '';
        foreach (self::$allStereotypes as $item)
            $str .= '<UML:Stereotype xmi.id="'.self::getUID('stereotype_'.$item).'"
                name="'.$item.'" '.self::DEFAULT_CLASSIFIER_ATT.' />';
        
        $str .= '<UML:Stereotype xmi.id="'.self::getUID('stereotype_realize').'"
            name="realize" '.self::DEFAULT_CLASSIFIER_ATT.'>
            <UML:Stereotype.baseClass>Abstraction</UML:Stereotype.baseClass>
            </UML:Stereotype>';
        
        $str .= $this->getTagDefinition('documentation');
        return $str;
    }
    
    public function getStereotypeInstance(PHP_UML_Metamodel_Stereotype $s)
    {
        return '';
    }
    
    public function getMetadata(PHP_UML_Metamodel_Tag $tag)
    {
        return '<'.$tag->name.'>'.$tag->value.'</'.$tag->name.'>'; 
    }
    
    public function getModelClose()
    {
        return '</UML:Model>';
    }
    
    public function getPackageOpen(PHP_UML_Metamodel_Package $package)
    {
        $str = '<UML:Package xmi.id="'.$package->id.'" name="'.$package->name.'">';
        if (isset($package->description)) {
            $str .= $this->getComment($package->description);
        }
        return $str;
    }

    public function getNamespaceOpen()
    {
        return '<UML:Namespace.ownedElement>';
    }
    
    public function getPackageClose()
    {
        return '</UML:Package>';
    }

    public function getNamespaceClose()
    {
        return '</UML:Namespace.ownedElement>';
    }
    
    public function getSubsystemOpen(PHP_UML_Metamodel_Package $package)
    {
        return '<UML:Subsystem name="'.$package->name.'" xmi.id="'.
            $package->id.'" isInstantiable="false"><UML:Namespace.ownedElement>';
    }

    public function getSubsystemClose()
    {
        return '</UML:Namespace.ownedElement></UML:Subsystem>';
    }
    
    public function getDatatype(PHP_UML_Metamodel_Datatype $type)
    {
        $str = '<UML:DataType xmi.id="'.$type->id.'"'.
            ' name="'.$type->name.'" visibility="public" isRoot="false" '.
            ' isLeaf="false" isAbstract="false">';
        if (isset($class->description))
            $str .= $this->getComment($class->description);
        return $str.'</UML:DataType>';
    }

    public function getClass(PHP_UML_Metamodel_Class $class)
    { 
        $str = '<UML:Class name="'.$class->name.'" xmi.id="'.
            $class->id.'" visibility="package"
            isAbstract="'.($class->isAbstract?'true':'false').'">';

        list($generalizable, $generalization) = $this->getGeneralizations($class);

        $str .= $generalizable;
        $str .= '<UML:Classifier.feature>';

        foreach ($class->ownedAttribute as &$property) {
            $str .= $this->getProperty($property);
        }

        foreach ($class->ownedOperation as &$operation) {
            $str .= $this->getOperation($operation);
        }

        $str .= '</UML:Classifier.feature>';
        
        if (isset($class->description))
            $str .= $this->getComment($class->description);
        
        $str .= '</UML:Class>';

        return $str.$generalization.$this->getRealizations($class);
    }

    public function getInterface(PHP_UML_Metamodel_Interface $interface)
    {
        $str = '<UML:Interface name="'.$interface->name.'"'.
            ' xmi.id="'.$interface->id.'"'.
            ' visibility="package" isAbstract="true">';

        list($generalizable, $generalization) = $this->getGeneralizations($interface);

        $str .= $generalizable;
        $str .= '<UML:Classifier.feature>';
 
        foreach ($interface->ownedOperation as &$operation)
            $str .= $this->getOperation($operation);

        $str .= '</UML:Classifier.feature>';

        if (isset($interface->description))
            $str .= $this->getComment($interface->description);

        $str .= '</UML:Interface>';
        
        return $str.$generalization;
    }

    public function getGeneralizations(PHP_UML_Metamodel_Type $client)
    {
        $str = '';

        $generalizable  = '';
        $generalization = '';
 
        foreach ($client->superClass as &$gclass) {
            if (is_object($gclass)) {
                $id = self::getUID();

                $generalizable .= '<UML:GeneralizableElement.generalization>
                    <UML:Generalization xmi.idref="'.$id.'"/>
                    </UML:GeneralizableElement.generalization>';

                $generalization .= '<UML:Generalization xmi.id="'.$id.'">
                    <UML:Generalization.child><UML:Class xmi.idref="'.
                    $client->id.
                    '" /></UML:Generalization.child>
                    <UML:Generalization.parent><UML:Class xmi.idref="'.
                    $gclass->id.'"/>
                    </UML:Generalization.parent></UML:Generalization>';
            }
        }
        return array($generalizable, $generalization);
    }

    public function getRealizations(PHP_UML_Metamodel_Class $client)
    {
        $str = '';
        foreach ($client->implements as &$rclass) {
            if (is_object($rclass)) {
                $str .= '<UML:Abstraction '.
                    'xmi.id="'.self::getUID().'" isSpecification="false">'.
                    '<UML:ModelElement.stereotype><UML:Stereotype xmi.idref="'.
                    self::getUID('stereotype_realize').'"/>'.
                    '</UML:ModelElement.stereotype>'.
                    '<UML:Dependency.client><UML:Class xmi.idref="'.
                    $client->id.
                    '"/></UML:Dependency.client>'.
                    '<UML:Dependency.supplier><UML:Interface xmi.idref="'.
                    $rclass->id.'"/>'.
                    '</UML:Dependency.supplier></UML:Abstraction>';
            }
        }
        return $str;
    }

    public function getProperty(PHP_UML_Metamodel_Property $property)
    {
        $str = '<UML:Attribute xmi.id="'.$property->id.'"'.
            ' name="'.$property->name.'" '.
            ' visibility="'.$property->visibility.'" ';

        if (!$property->isInstantiable) {
            $str .= ' isStatic="true" ownerScope="classifier"';
        } else {
            $str .= ' ownerScope="instance"';
        }

        if ($property->isReadOnly)
            $str .= ' changeability="frozen" isReadOnly="true" ';

        $str .= '>';
        $str .= self::getStructuralFeatureType($property);

        if (isset($property->description))
            $str .= $this->getComment($property->description);
        
        $str .= '</UML:Attribute>';
        return $str;
    }
    
    public function getOperation(PHP_UML_Metamodel_Operation $operation)
    {
        $str = '<UML:Operation xmi.id="'.$operation->id.'"'.
            ' name="'.$operation->name.'"'.
            ' visibility="'.$operation->visibility.'"';
        if (!$operation->isInstantiable)
            $str .= ' isStatic="true"';
        if ($operation->isAbstract)
            $str .= ' isAbstract="true"';

        $str .= ' isQuery="false" concurrency="sequential">'.
            '<UML:BehavioralFeature.parameter>';

        foreach ($operation->ownedParameter as &$parameter) {
            $str .= $this->getParameter($parameter);
        }

        $str .= '</UML:BehavioralFeature.parameter>';

        if (isset($operation->description))
            $str .= $this->getComment($operation->description);

        $str .= '</UML:Operation>';

        return $str;
    }

    public function getParameter(PHP_UML_Metamodel_Parameter $parameter)
    {
        return '<UML:Parameter xmi.id="'.$parameter->id.'" '.
            ' name="'.$parameter->name.'"'.
            ' kind="'.$parameter->direction.'">'.
            $this->getParameterType($parameter).
            '</UML:Parameter>';
    }
 
    public function getParameterType(PHP_UML_Metamodel_TypedElement $parameter)
    {
        $str = '';
        $id  = self::getUID();
        // Exception to MOF : a PHP class can have the name of a datatype

        if (isset($parameter->type->id)) {
            $str .= '<UML:Parameter.type>'.
                '<UML:DataType xmi.idref="'.$parameter->type->id.
                '"/></UML:Parameter.type>';
        }

        if ($parameter->default!='') {
            $str .= '<UML:Parameter.defaultValue>'.
                '<UML:Expression xmi.id="'.$id.'"'.
                ' body="'.htmlspecialchars($parameter->default, ENT_QUOTES).'" />'.
                '</UML:Parameter.defaultValue>';
        }
        return $str;
    }

    public function getArtifact(PHP_UML_Metamodel_Artifact $file, &$mf = array())
    {
        return '<UML:Artifact '.
            ' xmi.id="'.$file->id.'"'.
            ' name="'.basename(htmlspecialchars($file->name)).'">'.
            '<UML:ModelElement.stereotype>'.
            '<UML:Stereotype xmi.idref="'.self::getUID('stereotype_'.self::PHP_FILE).'"/>'.
            '</UML:ModelElement.stereotype>'.
            '</UML:Artifact>';
    }

    public function getComponentOpen(PHP_UML_Metamodel_Package $package, array $provided, array $required)
    {
        return self::getSubsystemOpen($package);
    }

    public function getComponentClose()
    {
        return self::getSubsystemClose();
    }

    public function getComment(PHP_UML_Metamodel_Stereotype $s, $annotatedElement='')
    {
        $tag = PHP_UML_Metamodel_Helper::getStereotypeTag($s, 'description');
        if(!is_null($tag))
            return $this->getTaggedValue($tag->value, self::getUID('Tag_documentation'));
        else
            return '';
    }
    
    public function getTaggedValue($value, $tag)
    {
        return '<UML:ModelElement.taggedValue>'.
            '<UML:TaggedValue xmi.id="'.self::getUID().'" visibility="public">'.
            '<UML:TaggedValue.dataValue>'.htmlspecialchars($value).'</UML:TaggedValue.dataValue>'.
            '<UML:TaggedValue.type>'.
                '<UML:TagDefinition xmi.idref="'.$tag.'"/>'.
            '</UML:TaggedValue.type>'.
            '</UML:TaggedValue>'.
            '</UML:ModelElement.taggedValue>';
    }
    
    public function getTagDefinition($name)
    {
        return '<UML:TagDefinition xmi.id="'.self::getUID('Tag_'.$name).'" '.
            'name="'.$name.'" isSpecification="false" tagType="string">
            <UML:TagDefinition.multiplicity>
            <UML:Multiplicity xmi.id="'.self::getUID('TagMultiplicity_'.$name).'">
              <UML:Multiplicity.range>
                <UML:MultiplicityRange xmi.id="'.self::getUID('TagMultiRange_'.$name).'"
                  lower="0" upper="1"/>
              </UML:Multiplicity.range>
            </UML:Multiplicity>
          </UML:TagDefinition.multiplicity>
        </UML:TagDefinition>';
    }
    
    public function getProfile()
    {
    }
    
    /**
     * Get the structural type of an element (XMI 1.x)
     *
     * @param PHP_UML_TypedElement $element Element
     * 
     * @return string XMI code
     */
    static protected function getStructuralFeatureType(PHP_UML_Metamodel_TypedElement $element)
    {
        $str = '';
        $id  = self::getUID();
        if (!is_object($element->type))
            return '';

        if (get_class($element->type)=='PHP_UML_Metamodel_Class') {

            $str .= '<UML:StructuralFeature.type>'.
                '<UML:DataType xmi.idref="'.$element->type->id.
                '"/></UML:StructuralFeature.type>';

        } elseif (get_class($element->type)=='PHP_UML_Metamodel_Datatype') {

            $str .= '<UML:StructuralFeature.type>'.
                '<UML:DataType xmi.idref="'.$element->type->id.
                '"/></UML:StructuralFeature.type>';
        }

        if ($element->default!='') {
            $str .= '<UML:Attribute.initialValue>'.
                '<UML:Expression xmi.id="'.$id.'"'.
                ' body="'.htmlspecialchars($element->default, ENT_QUOTES).'" />'.
                '</UML:Attribute.initialValue>';
        }
        return $str;
    }
}
?>
