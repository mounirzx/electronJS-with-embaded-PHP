<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi exslt exslt-set exslt-functions" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:exslt="http://exslt.org/common" xmlns:exslt-set="http://exslt.org/sets"
	xmlns:exslt-functions="http://exslt.org/functions">

	<!-- Common templates required by the documentation exporter -->

	<!-- Returns the generalized elements of the current context (through <element> tags)-->
	<xsl:template name="generalization">
		<xsl:variable name="parent" select="key('getElementById', @general)"/>
		<element>
			<xsl:attribute name="href">
				<xsl:call-template name="getPackageFilePath">
					<xsl:with-param name="context" select="$parent"/>
				</xsl:call-template>
				<xsl:call-template name="getPrefix">
					<xsl:with-param name="context" select="$parent"/>
				</xsl:call-template>
				<xsl:value-of select="concat($parent/@name,'.html')"/>
			</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:value-of select="$parent/@xmi:id"/>
			</xsl:attribute>
			<xsl:call-template name="getPackageNamePart">
				<xsl:with-param name="context" select="$parent"/>
			</xsl:call-template>
			<xsl:value-of select="$parent/@name"/>
		</element>
		<xsl:for-each select="$parent/generalization">
			<xsl:call-template name="generalization"/>
		</xsl:for-each>
	</xsl:template>


	<xsl:template name="htmlHead">
		<xsl:param name="path"/>
		<xsl:param name="title"/>
		<xsl:comment><xsl:value-of select="concat('Generated&#32;by&#32;',$appName,'&#32;on&#32;',$genDate)"/></xsl:comment>
		<head>
			<title><xsl:value-of select="$title"/></title>
			<link rel="stylesheet" type="text/css" media="all" href="{concat($path,'style.css')}" />
		</head>
	</xsl:template>


	<xsl:template name="htmlInherit">
		<xsl:param name="relPathTop" />
		<img src="{concat($relPathTop, 'inherit.gif')}" alt="extended by "/>
	</xsl:template>


	<!-- Displays the inheritance-tree of the current class -->
	<xsl:template name="htmlInheritedClassTree">
		<xsl:param name="path"/>
		<xsl:param name="relPathTop" />

		<xsl:variable name="nbElement" select="count(exslt:node-set($path)/*)"/>
		<xsl:if test="$nbElement &gt; 1">	<!-- no display if the class does not inherit from anything -->
			<dl class="classTree">
				<xsl:for-each select="exslt:node-set($path)/*">
					<xsl:sort select="position()" data-type="number" order="descending"/>
					<dd>
						<xsl:choose>
							<xsl:when test="position() &gt; 1">
								<xsl:if test="position() = last()">
									<xsl:attribute name="class">currentElem</xsl:attribute>
								</xsl:if>
								<xsl:attribute name="style"><xsl:value-of select="concat('padding-left:',(position()-1)*30,'px')"/></xsl:attribute>
								<xsl:call-template name="htmlInherit">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
								</xsl:call-template>
							</xsl:when>
						</xsl:choose>
						<xsl:choose>
							<xsl:when test="position() &lt; $nbElement">
								<a href="{concat($relPathTop, @href)}"><xsl:value-of select="text()"/></a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="text()"/>
							</xsl:otherwise>
						</xsl:choose>
					</dd>
				</xsl:for-each>
			</dl>
		</xsl:if>
	</xsl:template>


	<xsl:template name="htmlStartPage">
		<xsl:attribute name="xmlns">http://www.w3.org/1999/xhtml</xsl:attribute>
		<xsl:attribute name="xml:lang">en</xsl:attribute>
		<xsl:attribute name="lang">en</xsl:attribute>
	</xsl:template>
	
	
	<xsl:template name="htmlTableSummary">
		<xsl:param name="relPathTop"/>
		<xsl:param name="set" />
		<xsl:param name="filePrefix" />
		<table border="1" class="tableSummary">
			<xsl:for-each select="$set">
				<tr>
					<td><strong><a href="{concat($filePrefix, @name, '.html')}"><xsl:value-of select="@name"/></a></strong></td>
					<td><xsl:call-template name="titleComment"/></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>


	<!-- Displays the parameters of a function as a bracketed list (<ownedParameter...) -->
	<xsl:template name="htmlParametersBracket">
		<xsl:param name="relPathTop" />
		<xsl:text>(</xsl:text>
		<xsl:for-each select="ownedParameter[@direction!='return' or not(@direction)]">
			<xsl:call-template name="htmlType">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
				<xsl:with-param name="context" select="."/>
			</xsl:call-template>
			<xsl:if test="@direction='inout'">&#38;</xsl:if>
			<xsl:value-of select="@name" />
			<xsl:if test="defaultValue">
				<xsl:value-of select="concat('=', defaultValue/@value)"/>
			</xsl:if>
			<xsl:if test="position() &lt; last()">
				<xsl:text>,&#160;</xsl:text>
			</xsl:if>
		</xsl:for-each>
		<xsl:text>)</xsl:text>
	</xsl:template>


	<!-- Displays a type as a hyperlink (<type xmi:idref="..."/>) -->
	<xsl:template name="htmlType">
		<xsl:param name="relPathTop" />
		<xsl:param name="context" />
		<xsl:variable name="idref" select="$context/type/@xmi:idref | $context/@type"/>
		<xsl:if test="$idref!=''">
			<xsl:variable name="typeElement" select="key('getElementById', $idref)"/>
			<xsl:call-template name="htmlLinkToElement">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
				<xsl:with-param name="context" select="$typeElement"/>
			</xsl:call-template>
			<xsl:text> </xsl:text>
		</xsl:if>
	</xsl:template>


	<!-- Displays the description/docblocks about a given element -->
	<xsl:template name="htmlDescription">
		<xsl:param name="baseElement"/>
		<xsl:variable name="docblocks" select="key('getMetadata', $baseElement)/*[local-name()!='param' and local-name()!='return' and local-name()!='access' and local-name()!='var']"/>
		
		<xsl:for-each select="ownedComment">
			<xsl:value-of select="@body"/>
			<xsl:call-template name="br-replace">
				<xsl:with-param name="str" select="body/text()"/>
			</xsl:call-template>
			<div>
				<xsl:for-each select="$docblocks">
					<xsl:call-template name="htmlDocblock"/>
				</xsl:for-each>
			</div>
		</xsl:for-each>
	</xsl:template>


	<!-- Displays the description/params. Used for method parameters detailing -->
	<xsl:template name="htmlDescriptionParam">
		<xsl:param name="baseElement"/>
		<xsl:variable name="docblocks" select="key('getMetadata', $baseElement)/*[local-name()='param' or local-name()='return']"/>
		
		<xsl:for-each select="ownedComment">
			<p>
				<xsl:value-of select="@body"/>
				<xsl:call-template name="br-replace">
					<xsl:with-param name="str" select="body/text()"/>
				</xsl:call-template>
			</p>
		</xsl:for-each>
	</xsl:template>


	<!-- Displays a docblock element (in general) -->
	<xsl:template name="htmlDocblock">
		<b>
		<xsl:call-template name="toUpperCase">
			<xsl:with-param name="str" select="substring(local-name(),1,1)"/>
		</xsl:call-template>
		<xsl:value-of select="concat(substring(local-name(),2), ':&#32;')"/></b>
		<xsl:value-of select="text()"/>
		<xsl:copy-of select="*"/><br/>
	</xsl:template>


	<!-- Displays a docblock param -->
	<xsl:template name="htmlDocblockParam">
		<xsl:value-of select="substring-after(substring-after(text(), ' '), ' ')"/>
		<xsl:copy-of select="*"/>
	</xsl:template>


	<!-- Replace breaklines by <br/> -->
	<xsl:template name="br-replace">
	  <xsl:param name="str"/>
	  <xsl:choose>
			<xsl:when test="contains($str,$cr)">
				<xsl:value-of select="substring-before($str,$cr)"/>
				<br/>
				<xsl:call-template name="br-replace">
					<xsl:with-param name="str" select="substring-after($str,$cr)"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$str"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>


	<xsl:template name="htmlLinkToElement">
		<xsl:param name="relPathTop" />
		<xsl:param name="context" />
		<xsl:param name="style" select="string('linkType')"/>
		<xsl:variable name="path">
				<xsl:call-template name="getPackageFilePath">
					<xsl:with-param name="context" select="$context"/>
				</xsl:call-template>
				<xsl:call-template name="getPrefix">
					<xsl:with-param name="context" select="$context"/>
				</xsl:call-template>
			</xsl:variable>
			<a href="{concat($relPathTop,$path,$context/@name,'.html')}" class="{$style}"><xsl:value-of select="$context/@name"/></a>
	</xsl:template>

</xsl:stylesheet>