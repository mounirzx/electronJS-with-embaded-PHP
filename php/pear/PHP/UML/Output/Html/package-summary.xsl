<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi exslt exslt-set exslt-functions" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:exslt="http://exslt.org/common" xmlns:exslt-set="http://exslt.org/sets"
	xmlns:exslt-functions="http://exslt.org/functions">

	<xsl:template name="package-summary">
		<xsl:param name="relPathTop" />
		<xsl:param name="classSet"/>
		<xsl:param name="interfaceSet"/>
		<xsl:param name="ownedOperationSet" select="$ownedOperationSet"/>
		<xsl:param name="ownedAttributeSet" select="$ownedAttributeSet"/>
		<xsl:param name="packageNamePart"/>
		<xsl:param name="containerName"/>
		<xsl:variable name="packageFilePath">
			<xsl:call-template name="getPackageFilePath">
			<xsl:with-param name="context" select="."></xsl:with-param></xsl:call-template>
		</xsl:variable>
		<xsl:variable name="previousPreceding" select="(preceding::packagedElement|ancestor::packagedElement)[@xmi:type='uml:Package' or @xmi:type='uml:Model'][last()]"/>
		<xsl:variable name="nextFollowing" select="(descendant::packagedElement|following::packagedElement)[@xmi:type='uml:Package' or @xmi:type='uml:Model'][1]"/>

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" select="$relPathTop"/>
				<xsl:with-param name="title" select="concat($packageNamePart, @name)"/>
			</xsl:call-template>
			<body>

				<div id="navigation">
					<div id="banner">
						<ul class="sections">
							<li><a href="{$relPathTop}{$fileOverviewSummary}" title="Summary of all packages">Overview</a></li>
							<li class="active">Package</li>
							<li>Class</li>
							<li><a href="{$relPathTop}{$fileIndexAll}">Index</a></li>
						</ul>
						<ul class="navigFrame">
							<a href="{concat($relPathTop,$fileIndex,'?',$containerName,'/',$packageFilePath,$filePackageSummary)}" class="navigFrameElem" target="_top">FRAMES</a>
							<xsl:text>&#32;</xsl:text>
							<a href="{$filePackageSummary}" class="navigFrameElem" target="_top">NO FRAMES</a>
						</ul>
					</div>
					<ul class="siblingSections">
						<xsl:if test="count($previousPreceding) &gt; 0">
							<xsl:variable name="path">
								<xsl:call-template name="getPackageFilePath">
									<xsl:with-param name="context" select="$previousPreceding/@name"/>
								</xsl:call-template>
							</xsl:variable>
							<li class="last"><a href="{concat($relPathTop, $path, $filePackageSummary)}">Prev Package</a></li>
						</xsl:if>
						<xsl:if test="count($nextFollowing) &gt; 0">
							<xsl:variable name="path">
								<xsl:call-template name="getPackageFilePath">
									<xsl:with-param name="context" select="$nextFollowing/@name"/>
								</xsl:call-template>
							</xsl:variable>
							<li class="last"><a href="{concat($relPathTop, $path, $filePackageSummary)}">Next Package</a></li>
						</xsl:if>
					</ul>
				</div>

				<div id="content">
					<div class="classSummary">
						<h1 id="entityName">Package <xsl:value-of select="concat($packageNamePart, @name)" /></h1>
					</div>
					<p>
						<xsl:call-template name="htmlDescription">
						<xsl:with-param name="baseElement" select="@xmi:id"/>
						</xsl:call-template>
					</p>
					<hr/>

					<xsl:if test="count($interfaceSet) &gt; 0">
						<h2>Interface summary</h2>
						<xsl:call-template name="htmlTableSummary">
							<xsl:with-param name="relPathTop" select="$relPathTop"/>
							<xsl:with-param name="set" select="$interfaceSet"/>
							<xsl:with-param name="filePrefix" select="$fileprefixInterface"/>
						</xsl:call-template>
					</xsl:if>
					
					<xsl:if test="count($classSet) &gt; 0">
						<h2>Class summary</h2>
						<xsl:call-template name="htmlTableSummary">
							<xsl:with-param name="relPathTop" select="$relPathTop"/>
							<xsl:with-param name="set" select="$classSet"/>
							<xsl:with-param name="filePrefix" select="$fileprefixClass"/>
						</xsl:call-template>
					</xsl:if>

					<!-- added for the non-OO functions -->
					<xsl:if test="count($ownedAttributeSet) &gt; 0">
						<div id="fieldSummary">
							<h2>Field Summary</h2>
							<table border="1" class="tableSummary">
								<xsl:for-each select="$ownedAttributeSet">
									<xsl:call-template name="class-field-summary">
										<xsl:with-param name="relPathTop" select="$relPathTop"/>
									</xsl:call-template>
								</xsl:for-each>
							</table>
						</div>
					</xsl:if>
					<xsl:if test="count($ownedOperationSet) &gt; 0">
						<div id="methodSummary">
							<h2>Method Summary</h2>
							<table border="1" class="tableSummary">
								<xsl:for-each select="$ownedOperationSet">
									<xsl:call-template name="class-method-summary">
										<xsl:with-param name="relPathTop" select="$relPathTop"/>
									</xsl:call-template>
								</xsl:for-each>
							</table>
						</div>
					</xsl:if>
					<xsl:if test="count($ownedAttributeSet) &gt; 0">
						<div id="fieldDetail">
							<h2>Field Detail</h2>
							<xsl:for-each select="$ownedAttributeSet">
								<xsl:call-template name="class-field-detail">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
								</xsl:call-template>
							</xsl:for-each>
						</div>
					</xsl:if>
					<xsl:if test="count($ownedOperationSet) &gt; 0">
						<div id="methodDetail">
							<h2>Method Detail</h2>
							<xsl:for-each select="$ownedOperationSet">
								<xsl:call-template name="class-method-detail">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
									<xsl:with-param name="specifiedBy" select="no"/>
								</xsl:call-template>
							</xsl:for-each>
						</div>
					</xsl:if>

				</div>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>