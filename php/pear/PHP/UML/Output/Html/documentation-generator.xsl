<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi php exslt exslt-set exslt-functions" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:php="http://php.net/xsl"
	xmlns:exslt="http://exslt.org/common" xmlns:exslt-set="http://exslt.org/sets"
	xmlns:exslt-functions="http://exslt.org/functions">

	<xsl:variable name="packageDelimiter">\</xsl:variable>
	<xsl:variable name="fileprefixDatatype">datatype-</xsl:variable>
	<xsl:variable name="fileprefixClass">class-</xsl:variable>
	<xsl:variable name="fileprefixInterface">interface-</xsl:variable>
	<xsl:variable name="fileAllclassesFrame">allclasses-frame.html</xsl:variable>
	<xsl:variable name="fileOverviewFrame">overview-frame.html</xsl:variable>
	<xsl:variable name="fileOverviewSummary">overview-summary.html</xsl:variable>
	<xsl:variable name="filePackageFrame">package-frame.html</xsl:variable>
	<xsl:variable name="filePackageSummary">package-summary.html</xsl:variable>
	<xsl:variable name="fileIndex">index.html</xsl:variable>
	<xsl:variable name="fileIndexAll">index-all.html</xsl:variable>
	<xsl:variable name="cr" select="'&#xa;'"/>
	
	<xsl:include href="../common.xsl"/>

	<xsl:template name="top-container">
		<xsl:call-template name="inner-container">
			<xsl:with-param name="relPathTop" />
		</xsl:call-template>

		<xsl:variable name="containerSet" select="//packagedElement[@xmi:type='uml:Package' or @xmi:type='uml:Model']"/>

		<!-- Generation of the overview-summary file -->
		<xsl:variable name="contentOverviewSummary">
			<xsl:call-template name="overview-summary">
				<xsl:with-param name="containerSet" select="$containerSet" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $fileOverviewSummary, exslt:node-set($contentOverviewSummary)/*)" />
		
		<!-- Generation of the overview-frame file -->
		<xsl:variable name="contentOverviewFrame">
			<xsl:call-template name="overview-frame">
				<xsl:with-param name="containerSet" select="$containerSet" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $fileOverviewFrame, exslt:node-set($contentOverviewFrame)/*)" />

		<!-- Generation of the allclasses file -->
		<xsl:variable name="contentAllclasses">
			<xsl:call-template name="allclasses" />
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $fileAllclassesFrame, exslt:node-set($contentAllclasses)/*)" />

		<!-- Generation of the index file -->
		<xsl:variable name="contentIndex">
			<xsl:call-template name="index" />
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $fileIndex, exslt:node-set($contentIndex)/*)" />

		<!-- Generation of the index-all file -->
		<xsl:variable name="contentIndexAll">
			<xsl:call-template name="index-all" />
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $fileIndexAll, exslt:node-set($contentIndexAll)/*)" />
	</xsl:template>


	<!-- Tree walking of containers (uml package/model) -->
	<xsl:template name="inner-container">
		<xsl:param name="relPathTop" />	<!-- relative path to root -->

		<!-- Misc initializations -->
		<xsl:variable name="interfaceSet" select="packagedElement[@xmi:type='uml:Interface']" />
		<xsl:variable name="classSet" select="packagedElement[@xmi:type='uml:Class']"/>
		<xsl:variable name="ownedOperationSet" select="ownedOperation"/>
		<xsl:variable name="ownedAttributeSet" select="ownedAttribute"/>
		<xsl:variable name="packageNamePart">
			<xsl:call-template name="getPackageNamePart"/>
		</xsl:variable>

		<!-- Generation of the package-summary file -->
		<xsl:variable name="contentSummary">
			<xsl:call-template name="package-summary">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
				<xsl:with-param name="interfaceSet" select="$interfaceSet"/>
				<xsl:with-param name="classSet" select="$classSet"/>
				<xsl:with-param name="ownedAttributeSet" select="$ownedAttributeSet"/>
				<xsl:with-param name="ownedOperationSet" select="$ownedOperationSet"/>
				<xsl:with-param name="packageNamePart" select="$packageNamePart"/>
			</xsl:call-template>
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $filePackageSummary, exslt:node-set($contentSummary)/*)" />
	
		<!--  Generation of the package-frame files -->
		<xsl:variable name="contentFrame">
			<xsl:call-template name="package-frame">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
				<xsl:with-param name="interfaceSet" select="$interfaceSet"/>
				<xsl:with-param name="classSet" select="$classSet"/>
				<xsl:with-param name="ownedOperationSet" select="$ownedOperationSet"/>
				<xsl:with-param name="ownedAttributeSet" select="$ownedAttributeSet"/>
				<xsl:with-param name="packageNamePart" select="$packageNamePart"/>
			</xsl:call-template>
		</xsl:variable>
		<xsl:copy-of select="php:function($phpSaveToFile, $filePackageFrame, exslt:node-set($contentFrame)/*)" />
	
		<!--  Generation of the classifier files -->
		<xsl:call-template name="generateClassifierFiles">
			<xsl:with-param name="relPathTop" select="$relPathTop" />
			<xsl:with-param name="setEntities" select="(packagedElement|nestedClassifier)[@xmi:type='uml:Interface' or @xmi:type='uml:Class' or @xmi:type='uml:DataType']"/>
		</xsl:call-template>
		
		<!-- Recursion into the owned containers -->
		<xsl:for-each select="packagedElement[@xmi:type='uml:Package' or @xmi:type='uml:Model']">
			<!-- Creation of folders -->
			<xsl:copy-of select="php:function($phpCreateFolder, string(@name))" />
			<xsl:call-template name="inner-container">
				<xsl:with-param name="relPathTop" select="concat($relPathTop, '../')"/>
			</xsl:call-template>
			<xsl:copy-of select="php:functionString('chdir', '../')" />
		</xsl:for-each>
		
	</xsl:template>

	<!-- Classifier entities handler -->
	<xsl:template name="generateClassifierFiles">
		<xsl:param name="relPathTop"/>
		<xsl:param name="setEntities"/>

		<xsl:for-each select="$setEntities">
			<xsl:variable name="entity" select="substring-after(@xmi:type,':')"/>
			<xsl:variable name="xmiType" select="@xmi:type"/>
			<xsl:variable name="filePrefix">
				<xsl:call-template name="getPrefix"/>
			</xsl:variable>
			<xsl:variable name="content">
				<xsl:call-template name="classifier">
					<xsl:with-param name="relPathTop" select="$relPathTop" />
					<xsl:with-param name="entity" select="$entity"/>
					<xsl:with-param name="nestingPackageName">
						<xsl:call-template name="getNestingPackageName">
							<xsl:with-param name="context" select="."/>
						</xsl:call-template>
					</xsl:with-param>
					<xsl:with-param name="ownedAttributeSet" select="ownedAttribute"/>
					<xsl:with-param name="ownedOperationSet" select="ownedOperation"/>
					<xsl:with-param name="generalization" select="key('getElementById',generalization/@general)"/>
					<xsl:with-param name="implements" select="key('getElementById', key('getRealizations', current()/@xmi:id)/@supplier)"/>
					<xsl:with-param name="prevEntity" select="preceding-sibling::packagedElement[@xmi:type=$xmiType][1]/@name"/>
					<xsl:with-param name="nextEntity" select="following-sibling::packagedElement[@xmi:type=$xmiType][1]/@name"/>
					<xsl:with-param name="filePrefix" select="$filePrefix"/>
					<xsl:with-param name="relPathClass">
						<xsl:call-template name="getPackageFilePath">
							<xsl:with-param name="context" select="."/>
						</xsl:call-template>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:variable>
			<xsl:copy-of select="php:function($phpSaveToFile, string(concat($filePrefix, @name, '.html')), exslt:node-set($content)/*)" />

			<!-- nested entities ? -->
			<xsl:variable name="setNestedEntities" select="(packagedElement|nestedClassifier)[@xmi:type='uml:Interface' or @xmi:type='uml:Class' or @xmi:type='uml:DataType']"/>
			<xsl:if test="count($setNestedEntities) &gt; 0">
				<xsl:copy-of select="php:function($phpCreateFolder, string(@name))" />
				<xsl:call-template name="generateClassifierFiles">
					<xsl:with-param name="relPathTop" select="concat('../', $relPathTop)"/>
					<xsl:with-param name="setEntities" select="$setNestedEntities"/>
				</xsl:call-template>
				<xsl:copy-of select="php:functionString('chdir', '../')" />
			</xsl:if>
			
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="xmi:Documentation"/>
</xsl:stylesheet>
