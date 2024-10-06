<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">

	<!-- Called on the top container. Lists all the container elements, in the left frame -->
	<xsl:template name="overview-frame">
		<xsl:param name="containerSet" />

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" />
				<xsl:with-param name="title" select="concat(@name,' - Overview')"/>
			</xsl:call-template>
			<body>

				<div id="contentFrame">
					<a href="{$fileAllclassesFrame}" target="packageFrame">All Classes</a>
					
					<h3>Packages</h3>
					<ul>
						<xsl:for-each select="$containerSet">
							<li>
								<xsl:variable name="path">
									<xsl:call-template name="getPackageFilePath">
										<xsl:with-param name="context" select="."/>
									</xsl:call-template>
									<xsl:value-of select="@name"/>
								</xsl:variable>
								<a href="{concat('./', $path, '/', $filePackageFrame)}" target="packageFrame">
									<xsl:call-template name="getPackageNamePart"/>
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
