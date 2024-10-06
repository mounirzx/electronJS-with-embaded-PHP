<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  
	xmlns:uml="http://schema.omg.org/spec/UML/2.1" xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:UML="http://www.omg.org/spec/UML/1.4" exclude-result-prefixes="uml UML">

	<xsl:include href="common.xsl"/>

	<xsl:key name="association1" match="UML:Association|UML:AssociationClass" use="UML:Association.connection/UML:AssociationEnd[1]/UML:AssociationEnd.participant/*/@xmi.idref" />
	<xsl:key name="association2" match="UML:Association|UML:AssociationClass" use="UML:Association.connection/UML:AssociationEnd[2]/UML:AssociationEnd.participant/*/@xmi.idref" />
	<xsl:key name="abstraction" match="UML:Abstraction" use="@xmi.id"/>
	
	<xsl:key name="generalization" match="UML:Generalization[UML:Generalization.parent/UML:Class]" use="UML:Generalization.child/UML:Class/@xmi.idref" /><!-- argo/rationale -->
	<xsl:key name="generalization2" match="UML:Generalization[not(UML:Generalization.parent/UML:Class)]" use="@child" /> <!--- umbrello-->
	
	<xsl:key name="getTaggedValue" match="UML:TaggedValue[@tag='documentation']" use="@modelElement"/>
	
	<xsl:param name="appName"/>
	<xsl:param name="targetVersion" select="string('2.1')"/>

	<xsl:output encoding="iso-8859-1" method="xml" indent="yes" />

	<!-- XMI -->
	<xsl:template match="/">
		<xmi:XMI xmi:version="2.1" xmlns:uml="http://schema.omg.org/spec/UML/2.1" xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">
			<xsl:apply-templates select="XMI" />
		</xmi:XMI>
	</xsl:template>

	<xsl:template match="XMI">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="XMI.documentation">
		<xmi:Documentation>
			<xsl:attribute name="exporter"><xsl:value-of select="XMI.exporter"/><xsl:value-of select="concat('Conversion to XMI ',$targetVersion,' by ',$appName)"/></xsl:attribute>
			<xsl:attribute name="exporterVersion"><xsl:value-of select="XMI.exporterVersion"/></xsl:attribute>
		</xmi:Documentation>
	</xsl:template>

	<xsl:template match="XMI.content">
		<xsl:apply-templates select="UML:Model"/>
	</xsl:template>

	<xsl:template match="XMI.extension">
		<xmi:Extension>
			<xsl:if test="@xmi.extender!=''"><xsl:attribute name="extender"><xsl:value-of select="@xmi.extender"/></xsl:attribute></xsl:if>
			<xsl:copy-of select="*"/>
		</xmi:Extension>
	</xsl:template>
	


	<!-- CONTAINERS -->
	<xsl:template match="UML:Model">
		<uml:Model xmi:type="uml:Model" name="{@name}" xmi:id="{@xmi.id}">
			<xsl:if test="@visibility!=''"><xsl:attribute name="visibility"><xsl:value-of select="@visibility"/></xsl:attribute></xsl:if>
			<xsl:call-template name="namespace-ownedElement"/>
		</uml:Model>
	</xsl:template>
	
	<xsl:template match="UML:Package">
		<packagedElement xmi:type="uml:Package" xmi:id="{@xmi.id}" name="{@name}">
			<xsl:call-template name="namespace-ownedElement"/>
		</packagedElement>
	</xsl:template>
	
	<xsl:template name="namespace-ownedElement">
		<xsl:for-each select="UML:Namespace.ownedElement">
			<xsl:apply-templates select="UML:Model"/>
			<xsl:apply-templates select="UML:Package"/>
			<xsl:apply-templates select="UML:UseCase"/>
			<xsl:apply-templates select="UML:Actor"/>
	
			<xsl:apply-templates select="UML:Class[@name!='']"/>
			<xsl:apply-templates select="UML:Association"/>
			<xsl:apply-templates select="UML:AssociationClass"/>
			
			<xsl:apply-templates select="UML:Interface"/>
			<xsl:apply-templates select="UML:Dependency"/>
	
			<xsl:apply-templates select="UML:Stereotype"/>
			
			<xsl:apply-templates select="UML:Abstraction"/>
			<xsl:apply-templates select="UML:Artifact"/>
	
			<xsl:apply-templates select="UML:DataType"/>
			
			<xsl:apply-templates select="XMI.extension"/>
			
			<!--xsl:call-template name="realization"/-->
		</xsl:for-each>
		<xsl:call-template name="comment"/>
		<xsl:apply-templates select="XMI.extension"/>
	</xsl:template>



	<!-- ELEMENTS -->

	<xsl:template match="UML:Actor">
		<packagedElement xmi:type="uml:Actor" name="{@name}" xmi:id="{@xmi.id}" visibility="{@visibility}" isAbstract="{@isAbstract}"/>
	</xsl:template>
	
	<xsl:template match="UML:UseCase">
		<packagedElement xmi:type="uml:UseCase" name="{@name}" xmi:id="{@xmi.id}" visibility="{@visibility}" isAbstract="{@isAbstract}"/>
	</xsl:template>
	
	<xsl:template match="UML:Class">
		<xsl:call-template name="class"/>
	</xsl:template>
	
	<xsl:template name="class">
		<xsl:variable name="hic" select="@xmi.id"/>
		<packagedElement xmi:type="uml:Class" name="{@name}" xmi:id="{$hic}" visibility="{@visibility}" isAbstract="{@isAbstract}">

			<xsl:for-each select="key('generalization',$hic)">
				<generalization xmi:type="uml:Generalization" xmi:id="{@xmi.id}" general="{UML:Generalization.parent/UML:Class/@xmi.idref}"/>
			</xsl:for-each>
			<xsl:for-each select="key('generalization2',$hic)">
				<generalization xmi:type="uml:Generalization" xmi:id="{@xmi.id}" general="{@parent}"/>
			</xsl:for-each>

			<xsl:for-each select="key('association2',$hic)/UML:Association.connection/UML:AssociationEnd[1]">
				<ownedAttribute xmi:type="uml:Property" name="" association="{../../@xmi.id}" aggregation="{@aggregation}" xmi:id="{concat($hic,'_',../../@xmi.id)}">
					<type xmi:idref="{UML:AssociationEnd.participant/*/@xmi.idref}" /> 
					<lowerValue xmi:type="uml:LiteralString">
		 		 		<xsl:attribute name="value"><xsl:call-template name="convInfinite"><xsl:with-param name="value" select="UML:AssociationEnd.multiplicity/UML:Multiplicity/UML:Multiplicity.range/UML:MultiplicityRange/@lower"/></xsl:call-template></xsl:attribute>
		 		 	</lowerValue>
		  		 <upperValue xmi:type="uml:LiteralString">
		 		 		<xsl:attribute name="value"><xsl:call-template name="convInfinite"><xsl:with-param name="value" select="UML:AssociationEnd.multiplicity/UML:Multiplicity/UML:Multiplicity.range/UML:MultiplicityRange/@upper"/></xsl:call-template></xsl:attribute>
		 		 	</upperValue>
			 	</ownedAttribute>
			</xsl:for-each>

			<xsl:for-each select="key('association1',$hic)/UML:Association.connection/UML:AssociationEnd[2]" >
				<ownedAttribute xmi:type="uml:Property" name="" association="{../../@xmi.id}" aggregation="{@aggregation}" xmi:id="{concat($hic,'_',../../@xmi.id)}">
					<type xmi:idref="{UML:AssociationEnd.participant/*/@xmi.idref}" /> 
		 		 	<lowerValue xmi:type="uml:LiteralString">
		 		 		<xsl:attribute name="value"><xsl:call-template name="convInfinite"><xsl:with-param name="value" select="UML:AssociationEnd.multiplicity/UML:Multiplicity/UML:Multiplicity.range/UML:MultiplicityRange/@lower"/></xsl:call-template></xsl:attribute>
		 		 	</lowerValue>
		  		 <upperValue xmi:type="uml:LiteralString">
		 		 		<xsl:attribute name="value"><xsl:call-template name="convInfinite"><xsl:with-param name="value" select="UML:AssociationEnd.multiplicity/UML:Multiplicity/UML:Multiplicity.range/UML:MultiplicityRange/@upper"/></xsl:call-template></xsl:attribute>
		 		 	</upperValue>
			 	</ownedAttribute>
			</xsl:for-each>

			<xsl:apply-templates select="UML:Classifier.feature/UML:Attribute"/>
			<xsl:apply-templates select="UML:Classifier.feature/UML:Operation"/>

			<xsl:call-template name="comment"/>
		</packagedElement>
	</xsl:template>
	
	<xsl:template match="UML:Attribute">
		<ownedAttribute xmi:type="uml:Property" name="{@name}" xmi:id="{@xmi.id}" visibility="{@visibility}" >
			<xsl:if test="@isAbstract"><xsl:attribute name="isAbstract">true</xsl:attribute></xsl:if>
			<xsl:if test="@isStatic or @ownerScope='classifier'"><xsl:attribute name="isStatic">true</xsl:attribute></xsl:if>
			<xsl:if test="@isReadOnly or @changeability='frozen'"><xsl:attribute name="isReadOnly">true</xsl:attribute></xsl:if>
			<type xmi:idref="{(UML:StructuralFeature.type/UML:DataType/@xmi.idref)|@type}" />

			<xsl:if test="UML:Attribute.initialValue">
				<xsl:for-each select="UML:Attribute.initialValue">
  				<defaultValue xmi:type="uml:LiteralString" xmi:id="{UML:Expression/@xmi.id}" value="{UML:Expression/@body}" />
  			</xsl:for-each>
  		</xsl:if>
 			<xsl:if test="@initialValue!=''">	<!-- umbrello-->
			  <defaultValue xmi:type="uml:LiteralString" xmi:id="{concat(@xmi.id,'dv')}" value="{@initialValue}"/>
			 </xsl:if>
 
			<xsl:call-template name="comment"/>
	    <xsl:apply-templates select="XMI.extension"/>
		</ownedAttribute>
	</xsl:template>
	
	<xsl:template match="UML:Operation">
		<ownedOperation xmi:type="uml:Operation" name="{@name}" xmi:id="{@xmi.id}" visibility="{@visibility}" isAbstract="{@isAbstract}">
			<xsl:if test="@isStatic or @ownerScope='classifier'"><xsl:attribute name="isStatic">true</xsl:attribute></xsl:if>
			<xsl:if test="@isRoot"><xsl:attribute name="isRoot">true</xsl:attribute></xsl:if>
			<xsl:for-each select="UML:BehavioralFeature.parameter/UML:Parameter">
				<ownedParameter name="{@name}" xmi:id="{@xmi.id}" direction="{@kind}">
			  	<type xmi:idref="{(UML:Parameter.type/UML:DataType/@xmi.idref)|@type}" /> 
			  	<xsl:for-each select="UML:Parameter.defaultValue">
			  		<defaultValue xmi:type="uml:LiteralString" xmi:id="{UML:Expression/@xmi.id}" value="{UML:Expression/@body}"/>
			  	</xsl:for-each>
			  	<xsl:if test="@value!=''">	<!-- umbrello-->
			  		<defaultValue xmi:type="uml:LiteralString" xmi:id="{concat(@xmi.id,'dv')}" value="{@value}"/>
			  	</xsl:if>
			  	<xsl:call-template name="comment"/>
			  </ownedParameter>
			</xsl:for-each>
			<xsl:call-template name="comment"/>
	    <xsl:apply-templates select="XMI.extension"/>
		</ownedOperation>
	</xsl:template>

	<xsl:template match="UML:DataType">
		<packagedElement xmi:type="uml:DataType" name="{@name}" xmi:id="{@xmi.id}"/>
	</xsl:template>
	
	<xsl:template match="UML:Interface">
		<packagedElement xmi:type="uml:Interface" name="{@name}" xmi:id="{@xmi.id}" visibility="{@visibility}" isAbstract="{@isAbstract}">
			<xsl:apply-templates select="UML:Classifier.feature/UML:Operation"/>
			<xsl:call-template name="comment"/>
	    <xsl:apply-templates select="XMI.extension"/>
		</packagedElement>
	</xsl:template>


	<xsl:template match="UML:Artifact">
		<packagedElement xmi:type="uml:Artifact" xmi:id="{@xmi.id}" name="{@name}" stereotype="{UML:ModelElement.stereotype/UML:Stereotype/@xmi.idref}"/> 
	</xsl:template>


	<xsl:template match="UML:Stereotype">
		<packagedElement xmi:type="uml:Stereotype" xmi:id="{@xmi.id}" name="{@name}" isAbstract="{@isAbstract}"/>
	</xsl:template>
	
	<!-- STRUCTURAL ELEMENTS -->
	
	<xsl:template match="UML:Dependency">
		<xsl:choose>
			<xsl:when test="UML:Dependency.client">
				<packagedElement xmi:type="uml:Dependency" xmi:id="{@xmi.id}" client="{UML:Dependency.client/UML:Class/@xmi.idref}" supplier="{UML:Dependency.supplier/UML:Class/@xmi.idref}">
					<xsl:if test="@name!=''">
						<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
					</xsl:if>
					<xsl:apply-templates select="UML:Classifier.feature/UML:Operation"/>
				</packagedElement>
			</xsl:when>
			<xsl:when test="@client!=''">
				<packagedElement xmi:type="uml:Dependency" xmi:id="{@xmi.id}" client="{@client}" supplier="{@supplier}" visibility="{@visibility}">
					<xsl:if test="@name!=''">
						<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
					</xsl:if>
				</packagedElement>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="UML:Association">
		<xsl:call-template name="association"/>
	</xsl:template>
	
	<xsl:template match="UML:AssociationClass">
		<xsl:call-template name="class"/>
		<xsl:call-template name="association"><xsl:with-param name="type">AC</xsl:with-param></xsl:call-template>
	</xsl:template>
	
	<xsl:template match="UML:Generalization"/>

	<xsl:template match="UML:Abstraction">
		<xsl:choose>
			<xsl:when test="@client!=''">
				<packagedElement xmi:type="uml:Realization" xmi:id="{@xmi.id}" client="{@client}" supplier="{@supplier}" realizingClassifier="{@supplier}" />
			</xsl:when>
			<xsl:when test="UML:Dependency.supplier">
				<xsl:variable name="supplier" select="UML:Dependency.supplier/*/@xmi.idref"/>
				<packagedElement xmi:type="uml:Realization" xmi:id="{@xmi.id}" client="{UML:Dependency.client/UML:Class/@xmi.idref}" supplier="{$supplier}" realizingClassifier="{$supplier}" />
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="convInfinite">
		<xsl:param name="value"/>
		<xsl:choose>
			<xsl:when test="$value='-1'">*</xsl:when>
			<xsl:otherwise><xsl:value-of select="$value"/></xsl:otherwise>
		</xsl:choose>
	</xsl:template>	

	<xsl:template name="association">
		<xsl:param name="type" select="''" />
		<packagedElement xmi:type="uml:Association" xmi:id="{concat($type,@xmi.id)}">
			<xsl:choose>
				<xsl:when test="$type!='AC'">
					<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="name"></xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:variable name="class_far1" select="UML:Association.connection/UML:AssociationEnd[1]/UML:AssociationEnd.participant/*/@xmi.idref"/>
			<memberEnd xmi:idref="{concat($class_far1,'_',@xmi.id)}" />
			<xsl:variable name="class_far2" select="UML:Association.connection/UML:AssociationEnd[2]/UML:AssociationEnd.participant/*/@xmi.idref"/>
			<memberEnd xmi:idref="{concat($class_far2,'_',@xmi.id)}" />
		</packagedElement>
		<xsl:if test="$type='AC'">
			<packagedElement xmi:type="uml:AssociationClass" xmi:id="{concat('AAC',@xmi.id)}">
				<memberEnd xmi:idref="{concat($type,@xmi.id)}"/>
				<memberEnd xmi:idref="{@xmi.id}"/>
			</packagedElement>
		</xsl:if>
	</xsl:template>


	
	<!-- COMMENTS -->

	<xsl:template match="UML:Comment">
		<xsl:call-template name="comment-body">
			<xsl:with-param name="id" select="@xmi.id"/>
			<xsl:with-param name="text" select="@body"/>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template match="UML:ModelElement.taggedValue/UML:TaggedValue">
		<xsl:call-template name="comment-body">
			<xsl:with-param name="id" select="@xmi.id"/>
			<xsl:with-param name="text" select="UML:TaggedValue.dataValue/text()"/>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template name="comment">
		<xsl:apply-templates select="UML:Comment"/>
		<xsl:apply-templates select="UML:ModelElement.taggedValue/UML:TaggedValue"/><!-- argo -->
		<xsl:for-each select="key('getTaggedValue', @xmi.id)"> <!-- rationale -->
			<xsl:call-template name="comment-body">
				<xsl:with-param name="id" select="concat(@xmi.id,'comment')"/>
				<xsl:with-param name="text" select="concat(@value, UML:TaggedValue.value/text())"/>
			</xsl:call-template>
		</xsl:for-each>
		<xsl:call-template name="comment-body"><!-- umbrello-->
			<xsl:with-param name="id" select="concat(@xmi.id,'comment')"/>
			<xsl:with-param name="text" select="@comment"/>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template name="comment-body">
		<xsl:param name="id" value="id"/>
		<xsl:param name="text" value="text"/>
		<xsl:if test="$text!=''">
			<ownedComment xmi:type="uml:Comment" xmi:id="{$id}">
				<body><xsl:value-of select="$text"/></body>
			</ownedComment>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>