<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi exslt exslt-set exslt-functions" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:exslt="http://exslt.org/common" xmlns:exslt-set="http://exslt.org/sets"
	xmlns:exslt-functions="http://exslt.org/functions">
	
	<!-- Called on the top container. Lists all elements -->
	<xsl:template name="index-all">

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" />
				<xsl:with-param name="title" select="concat(@name,' - Overview')"/>
			</xsl:call-template>
			<body>

				<div id="navigation">
					<div id="banner">
						<ul class="sections">
							<li><a href="{$fileOverviewSummary}" title="Summary of all packages">Overview</a></li>
							<li>Package</li>
							<li>Class</li>
							<li class="active">Index</li>
						</ul>
						<ul class="navigFrame">
							<a href="{concat($fileIndex,'?',$fileIndexAll)}" class="navigFrameElem" target="_top">FRAMES</a>
							<a href="{$fileIndexAll}" class="navigFrameElem" target="_top">NO FRAMES</a>
						</ul>
					</div>
				</div>

				<div id="content">
					<ul>
						<xsl:variable name="content">
							<xsl:call-template name="indexAllInnerContainer" mode="indexAll"/>
						</xsl:variable>
						<xsl:for-each select="exslt:node-set($content)/*">
							<xsl:sort select="text()"/>
							<li><a href="{@href}"><xsl:value-of select="text()"/></a>
							&#32;-&#32;
							<xsl:value-of select="comment/text()"/>
							<xsl:copy-of select="comment/a"/>
							</li>
						</xsl:for-each>
					</ul>
				</div>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="indexAllInnerContainer">
		<xsl:for-each select="packagedElement[@xmi:type='uml:Package' or @xmi:type='uml:Model']">

			<xsl:variable name="setEntities" select="(packagedElement|nestedClassifier)[@xmi:type='uml:Interface' or @xmi:type='uml:Class' or @xmi:type='uml:DataType']"/>
			<xsl:for-each select="$setEntities">
				<xsl:variable name="entityName" select="@name"/>
				<xsl:variable name="path">
					<xsl:call-template name="getPackageFilePath">
						<xsl:with-param name="context" select="."/>
					</xsl:call-template>
				</xsl:variable>
				<xsl:variable name="fileName">
					<xsl:value-of select="$path"/>
					<xsl:call-template name="getPrefix"><xsl:with-param name="context" select="."/></xsl:call-template>
					<xsl:value-of select="concat(@name, '.html')"/>
				</xsl:variable>
				<xsl:variable name="packageName">
					<xsl:call-template name="getPackageNamePart"/>
				</xsl:variable>
				<li>
					<xsl:attribute name="href">
						<xsl:value-of select="$fileName"/>
					</xsl:attribute>
					<comment>
						Class in
						<a href="{concat($path, $filePackageSummary)}"><xsl:value-of select="$packageName"/></a>
						<xsl:text>&#32;</xsl:text>
						<xsl:call-template name="titleComment"/>
					</comment>
					<xsl:value-of select="@name"/>
				</li>
				<xsl:for-each select="ownedAttribute">
					<li>
						<xsl:attribute name="href">
							<xsl:value-of select="concat($fileName, '#', @name)"/>
						</xsl:attribute>
						<comment>
							Variable in
							<xsl:value-of select="$packageName"/><a href="{$fileName}"><xsl:value-of select="$entityName"/></a>
							<xsl:text>&#32;</xsl:text>
							<xsl:call-template name="titleComment"/>
						</comment>
						<xsl:value-of select="@name"/>
					</li>
				</xsl:for-each>
				<xsl:for-each select="ownedOperation">
					<li>
						<xsl:variable name="parameters">
							<xsl:call-template name="htmlParametersBracket"/>
						</xsl:variable>
						<xsl:attribute name="href">
							<xsl:value-of select="concat($fileName, '#', @name, $parameters)"/>
						</xsl:attribute>
						<comment>
							Method in
							<xsl:value-of select="$packageName"/><a href="{$fileName}"><xsl:value-of select="$entityName"/></a>
							<xsl:text>&#32;</xsl:text>
							<xsl:call-template name="titleComment"/>
						</comment>
						<xsl:value-of select="@name"/>
					</li>
				</xsl:for-each>
			</xsl:for-each>

			
			<xsl:call-template name="indexAllInnerContainer"/>
		</xsl:for-each>
		<!-- procedural elements -->
			<xsl:variable name="entityName" select="@name"/>
			<xsl:variable name="path">
				<xsl:call-template name="getPackageFilePath">
					<xsl:with-param name="context" select="."/>
				</xsl:call-template>
			</xsl:variable>
			<xsl:variable name="fileName">
				<xsl:value-of select="$path"/>
				<xsl:choose>
					<xsl:when test="@xmi:type='uml:Model'">
						<xsl:value-of select="$fileOverviewSummary"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="concat(@name, '/', $filePackageSummary)"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:for-each select="ownedOperation">
				<li>
					<xsl:variable name="parameters">
						<xsl:call-template name="htmlParametersBracket"/>
					</xsl:variable>
					<xsl:attribute name="href">
						<xsl:value-of select="concat($fileName, '#', @name, $parameters)"/>
					</xsl:attribute>
					<comment>
						Method in
						<a href="{$fileName}"><xsl:value-of select="$entityName"/></a>
						<xsl:text>&#32;</xsl:text>
						<xsl:call-template name="titleComment"/>
					</comment>
					<xsl:value-of select="@name"/>
				</li>
				</xsl:for-each>
				<xsl:for-each select="ownedAttribute">
					<li>
						<xsl:attribute name="href">
							<xsl:value-of select="concat($fileName, '#', @name)"/>
						</xsl:attribute>
						<comment>
							Constant in
							<a href="{$fileName}"><xsl:value-of select="$entityName"/></a>
							<xsl:text>&#32;</xsl:text>
							<xsl:call-template name="titleComment"/>
						</comment>
						<xsl:value-of select="@name"/>
					</li>
				</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>