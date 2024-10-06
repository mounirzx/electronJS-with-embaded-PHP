<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">

	<!-- Called on the top container. Lists all the "uml:Package" elements, in the left frame. -->
	<xsl:template name="allclasses">

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" />
				<xsl:with-param name="title" select="concat(@name,' - All Classes')"/>
			</xsl:call-template>
			<body>

				<div id="contentFrame">
					<h1>All Classes</h1>
					<ul>
						<xsl:for-each select="//packagedElement[@xmi:type='uml:Class' or @xmi:type='uml:Interface' or @xmi:type='uml:DataType']">
							<xsl:sort select="@name" data-type="text" case-order="lower-first"/>
							<xsl:variable name="prefix">
								<xsl:call-template name="getPackageFilePath">
									<xsl:with-param name="context" select="."/>
								</xsl:call-template>
								<xsl:call-template name="getPrefix">
									<xsl:with-param name="context" select="."/>
								</xsl:call-template>
							</xsl:variable>
							<li>
								<xsl:if test="@xmi:type='uml:Interface'">
									<xsl:attribute name="class">interfaceElement</xsl:attribute>
								</xsl:if>
								<a href="{concat('./', $prefix, @name, '.html')}" target="classFrame">
									<xsl:value-of select="@name"/>
								</a>
							</li>
						</xsl:for-each>
					</ul>
				</div>

			</body>
		</html>

	</xsl:template>

</xsl:stylesheet>
