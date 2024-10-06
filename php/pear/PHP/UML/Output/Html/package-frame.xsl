<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">

	<xsl:template name="package-frame">
		<xsl:param name="relPathTop"/>
		<xsl:param name="classSet"/>
		<xsl:param name="interfaceSet"/>
		<xsl:param name="ownedOperationSet"/>
		<xsl:param name="ownedAttributeSet"/>
		<xsl:param name="packageNamePart"/>

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" select="$relPathTop"/>
				<xsl:with-param name="title" select="concat($packageNamePart, @name)"/>
			</xsl:call-template>
			<body>

				<div id="contentFrame">
					<h1>
						<a href="package-summary.html" target="classFrame">
							<xsl:value-of select="concat($packageNamePart, @name)" />
						</a>
					</h1>
					<xsl:if test="count($classSet) &gt; 0">
						<h3>Classes</h3>
						<ul>
							<xsl:for-each select="$classSet">
								<xsl:sort select="@name" data-type="text"/>
								<li>
									<a href="{concat($fileprefixClass, @name,'.html')}" target="classFrame"><xsl:value-of select="@name"/></a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:if>
					<xsl:if test="count($interfaceSet) &gt; 0">
						<h3>Interfaces</h3>
						<ul>
							<xsl:for-each select="$interfaceSet">
								<xsl:sort select="@name" data-type="text"/>
								<li class="interfaceElement">
									<a href="{concat($fileprefixInterface, @name, '.html')}" target="classFrame"><xsl:value-of select="@name"/></a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:if>
					<xsl:if test="count($ownedOperationSet) &gt; 0">
						<h3>Functions</h3>
						<ul>
							<xsl:for-each select="$ownedOperationSet">
								<xsl:sort select="@name" data-type="text"/>
								<xsl:variable name="parameters">
									<xsl:call-template name="htmlParametersBracket">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
									</xsl:call-template>
								</xsl:variable>
								<li>
									<a href="{concat('package-summary.html#', @name, $parameters)}" target="classFrame"><xsl:value-of select="@name"/></a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:if>
					<xsl:if test="count($ownedAttributeSet) &gt; 0">
						<h3>Constants</h3>
						<ul>
							<xsl:for-each select="$ownedAttributeSet">
								<xsl:sort select="@name" data-type="text"/>
								<li>
									<a href="{concat('package-summary.html#', @name)}" target="classFrame"><xsl:value-of select="@name"/></a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:if>
				</div>

			</body>
		</html>

	</xsl:template>
</xsl:stylesheet>
