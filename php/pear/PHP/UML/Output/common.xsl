<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">
	
	<!-- General templates used by the exporting system -->

	<xsl:key name="getElementById" match="packagedElement" use="@xmi:id"/>
	<xsl:key name="getRealizations" match="packagedElement[@xmi:type='uml:Realization']" use="@client"/>
	<xsl:key name="getRealizingClasses" match="packagedElement[@xmi:type='uml:Realization']" use="@supplier"/>
	<xsl:key name="getMetadata" match="/*[1]/*[name()='php:docblock']" use="@base_Element"/>
	<xsl:key name="getSubclasses" match="packagedElement" use="generalization/@general"/>
	<xsl:key name="getManifestation" match="manifestation" use="@supplier"/>
	
	<xsl:param name="phpCreateFolder"/>
	<xsl:param name="phpSaveToFile"/>
	<xsl:param name="appName" />
	<xsl:param name="genDate" />

	<!-- Starting templates -->
	<xsl:template match="/">
		<xsl:for-each select="descendant::*">
			<xsl:choose>
				<xsl:when test="name()='uml:Model'">
					<xsl:call-template name="top-container"/>
				</xsl:when>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>

	<!-- Each implementation (documentation or code) must provide a "top-container" template -->
	<xsl:template match="uml:Model">
		<xsl:call-template name="top-container"/>
	</xsl:template>


	<!-- Library -->

	<!-- Returns the package part of the context element (eg: PHP.UML) -->
	<xsl:template name="getPackageNamePart">
		<xsl:param name="context" select="."/>
		<xsl:for-each select="$context/ancestor::packagedElement">
			<xsl:value-of select="concat(@name, $packageDelimiter)"/>
		</xsl:for-each>
	</xsl:template>
	
	<!-- Returns the filepath that leads to the context element, from the top container -->
	<xsl:template name="getPackageFilePath">
		<xsl:param name="context" />
		<xsl:for-each select="$context/ancestor::packagedElement">
			<xsl:value-of select="concat(@name, '/')"/>
		</xsl:for-each>
	</xsl:template>
	
	<!-- Same as getPackageNamePart(), without the trailing delimiter -->
	<xsl:template name="getNestingPackageName">
		<xsl:param name="context" />
		<xsl:variable name="packageNamePart">
			<xsl:call-template name="getPackageNamePart">
				<xsl:with-param name="context" select="$context" />
			</xsl:call-template>
		</xsl:variable>
		<xsl:value-of select="substring($packageNamePart, 1, string-length($packageNamePart)-string-length($packageDelimiter))"/>
	</xsl:template>
	
	<xsl:template name="getPrefix">
		<xsl:param name="context" select="."/>
		<xsl:choose>
			<xsl:when test="$context/@xmi:type='uml:Interface'">								
				<xsl:value-of select="$fileprefixInterface"/>
			</xsl:when>
			<xsl:when test="$context/@xmi:type='uml:DataType'">
				<xsl:value-of select="$fileprefixDatatype"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$fileprefixClass"/>
			</xsl:otherwise>							
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="titleComment">
		<xsl:value-of select="substring-before(concat(ownedComment/body/text()|ownedComment/@body, $cr), $cr)"/>
	</xsl:template>

	<xsl:template name="toLowerCase">
		<xsl:param name="str"/>
		<xsl:value-of select="translate($str, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')"/>
	</xsl:template>

	<xsl:template name="toUpperCase">
		<xsl:param name="str"/>
		<xsl:value-of select="translate($str, 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
	</xsl:template>

</xsl:stylesheet>