<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi exslt exslt-set exslt-functions" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:exslt="http://exslt.org/common" xmlns:exslt-set="http://exslt.org/sets"
	xmlns:exslt-functions="http://exslt.org/functions">

	<!-- Called on every class/interface. One file per class/interface -->
	<xsl:template name="classifier">
		<xsl:param name="relPathTop"/>
		<xsl:param name="entity"/>
		<xsl:param name="nestingPackageName"/>
		<xsl:param name="ownedAttributeSet"/>
		<xsl:param name="ownedOperationSet" />
		<xsl:param name="generalization"/>
		<xsl:param name="implements"/>
		<xsl:param name="prevEntity"/>
		<xsl:param name="nextEntity"/>
		<xsl:param name="filePrefix"/>
		<xsl:param name="relPathClass"/>

		<xsl:variable name="path">
			<element>
				<xsl:attribute name="id"><xsl:value-of select="@xmi:id"/></xsl:attribute>
				<xsl:call-template name="getPackageNamePart"/>
				<xsl:value-of select="@name"/>
			</element>
			<xsl:for-each select="generalization">
				<xsl:call-template name="generalization"></xsl:call-template>
			</xsl:for-each>
		</xsl:variable>
		<xsl:variable name="allImplementedClasses" select="key('getElementById', key('getRealizations', exslt:node-set($path)/*/@id)/@supplier)"/>
		<xsl:variable name="allImplementingClasses" select="key('getElementById',key('getRealizingClasses', @xmi:id)/@client)"/>
		<xsl:variable name="allSubClasses" select="key('getSubclasses', @xmi:id)"/>
		<xsl:variable name="artifact" select="key('getElementById',key('getManifestation', @xmi:id)/@client)"/>
		<xsl:variable name="lcEntityName" select="translate($entity, 'CDI', 'cdi')"/>

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" select="$relPathTop"/>
				<xsl:with-param name="title" select="concat($nestingPackageName, $packageDelimiter, @name)"/>
			</xsl:call-template>
			<body>

				<div id="navigation">
					<div id="banner">
						<ul class="sections">
							<li><a href="{$relPathTop}{$fileOverviewSummary}" title="Summary of all packages">Overview</a></li>
							<li><a href="{$filePackageSummary}" title="Summary of {$nestingPackageName}">Package</a></li>
							<li class="active"><xsl:value-of select="$entity"/></li>
							<li><a href="{$relPathTop}{$fileIndexAll}">Index</a></li>
						</ul>
						<ul class="navigFrame">
						<a href="{concat($relPathTop,$fileIndex,'?',$relPathClass,$filePrefix,@name,'.html')}" class="navigFrameElem" target="_top">FRAMES</a>
						<xsl:text>&#32;</xsl:text>
						<a href="{concat($filePrefix,@name,'.html')}" class="navigFrameElem" target="_top">NO FRAMES</a>
						</ul>
					</div>
					<ul class="siblingSections">
						<xsl:if test="$prevEntity!=''">
							<li><a href="{concat($filePrefix,$prevEntity,'.html')}"><xsl:value-of select="concat('Prev ', $entity)" /></a></li>
						</xsl:if>
						<xsl:if test="$nextEntity!=''">
							<li class="last"><a href="{concat($filePrefix,$nextEntity,'.html')}"><xsl:value-of select="concat('Next ', $entity)" /></a></li>
						</xsl:if>
					</ul>
				</div>

				<div id="content">
					<div class="classSummary">
						<!-- Class Name -->
						<h3 id="entityPackage"><xsl:value-of select="$nestingPackageName"/></h3>
						<h1 id="entityName"><xsl:value-of select="concat($entity, '&#160;', @name)" /></h1>
						
						<!-- Class tree -->
						<xsl:call-template name="htmlInheritedClassTree">
							<xsl:with-param name="path" select="$path"/>
							<xsl:with-param name="relPathTop" select="$relPathTop"/>
						</xsl:call-template>

						<!--  All Implemented Classes -->
						<xsl:if test="count($allImplementedClasses) &gt; 0">
							<h3 class="titleSmallList">All Implemented Interfaces:</h3>
							<div class="smallList">
							<xsl:for-each select="$allImplementedClasses">
								<xsl:call-template name="htmlLinkToElement">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
									<xsl:with-param name="context" select="."/>
									<xsl:with-param name="style" select="string('linkSimple')"/>
								</xsl:call-template>
								<xsl:if test="position() &lt; last()"><xsl:text>, </xsl:text></xsl:if>
							</xsl:for-each>
							</div>
						</xsl:if>

						<!--  All Implemented Classes -->
						<xsl:if test="count($allSubClasses) &gt; 0">
							<h3 class="titleSmallList">Direct Known Subclasses:</h3>
							<div class="smallList">
							<xsl:for-each select="$allSubClasses">
								<xsl:call-template name="htmlLinkToElement">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
									<xsl:with-param name="context" select="."/>
									<xsl:with-param name="style" select="string('linkSimple')"/>
								</xsl:call-template>
								<xsl:if test="position() &lt; last()"><xsl:text>, </xsl:text></xsl:if>
							</xsl:for-each>
							</div>
						</xsl:if>

						<!--  All Implementing Classes -->
						<xsl:if test="count($allImplementingClasses) &gt; 0">
							<h3 class="titleSmallList">All Known Implementing Classes:</h3>
							<div class="smallList">
							<xsl:for-each select="$allImplementingClasses">
								<xsl:call-template name="htmlLinkToElement">
									<xsl:with-param name="relPathTop" select="$relPathTop"/>
									<xsl:with-param name="context" select="."/>
									<xsl:with-param name="style" select="string('linkSimple')"/>
								</xsl:call-template>
								<xsl:if test="position() &lt; last()"><xsl:text>, </xsl:text></xsl:if>
							</xsl:for-each>
							</div>
						</xsl:if>

						<hr/>

						<!-- Class details -->
						<xsl:text>public&#160;</xsl:text>
						<xsl:if test="@isAbstract='true' and $entity!='Interface'">
							<xsl:text>abstract&#160;</xsl:text>
						</xsl:if>
						<xsl:value-of select="concat($lcEntityName, '&#160;')"/>
						<strong><xsl:value-of select="@name" /></strong><br/>
						<xsl:if test="count($generalization) &gt; 0">
							extends <xsl:value-of select="$generalization/@name" /><br/>
						</xsl:if>
						<xsl:if test="count($implements) &gt; 0">
							implements
							<xsl:for-each	select="$implements">
								<xsl:value-of select="@name" />
								<xsl:if test="position() &lt; last()">,&#160;</xsl:if>
							</xsl:for-each>
						</xsl:if>

						<p>
							<xsl:call-template name="htmlDescription">
								<xsl:with-param name="baseElement" select="@xmi:id"/>
							</xsl:call-template>
							<xsl:if test="count($artifact) &gt; 0">
								<b>File: </b>
								<xsl:for-each select="exslt:node-set($artifact)/ancestor::*[@xmi:type='uml:Package']">
									<xsl:if test="position() &gt; 1"><xsl:value-of select="concat('/',@name)"/></xsl:if>
								</xsl:for-each>
								<xsl:value-of select="concat('/', $artifact/@name)"/>
							</xsl:if>
						</p>
						<hr/>
					</div>

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
									<xsl:with-param name="specifiedBy" select="exslt:node-set($allImplementedClasses)/ownedOperation[@name=current()/@name]"/>
								</xsl:call-template>
							</xsl:for-each>
						</div>
					</xsl:if>

					<xsl:variable name="nestedClassSet" select="nestedClassifier[@xmi:type='uml:Class']"/>
					<xsl:if test="count($nestedClassSet) &gt; 0">
						<div>
							<h2>Nested Classes</h2>
							<xsl:call-template name="htmlTableSummary">
								<xsl:with-param name="relPathTop" select="$relPathTop"/>
								<xsl:with-param name="set" select="$nestedClassSet"/>
								<xsl:with-param name="filePrefix" select="string(concat(@name, '/', $fileprefixClass))"/>
							</xsl:call-template>
						</div>
					</xsl:if>

				</div>
			</body>
		</html>
	</xsl:template>


	<xsl:template name="class-field-summary">
		<xsl:param name="relPathTop"/>
		<tr>
			<td align="right" width="1%" valign="top">
				<span class="code">
					<xsl:choose>
						<xsl:when test="@isReadOnly='true'">const</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="@visibility='private'">private</xsl:when>
								<xsl:when test="@visibility='protected'">protected</xsl:when>
							</xsl:choose>
							<xsl:if test="@isStatic='true'">&#160;static</xsl:if>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:if test="count(type) &gt; 0">
						<xsl:text>&#160;</xsl:text>
						<xsl:call-template name="htmlType">
							<xsl:with-param name="relPathTop" select="$relPathTop"/>
							<xsl:with-param name="context" select="."/>
						</xsl:call-template>
					</xsl:if>
				</span>
			</td>
			<td valign="top">
				<span class="code">
					<a href="#{@name}" class="linkSummary"><xsl:value-of select="@name" /></a>
				</span>
			</td>
		</tr>
	</xsl:template>

	<!--  Method summary -->
	<xsl:template name="class-method-summary">
		<xsl:param name="relPathTop"/>
		<tr>
			<td align="right" width="1%" valign="top">
				<span class="code">
					<xsl:if test="@isReadOnly='true'">final</xsl:if>
					<xsl:choose>
						<xsl:when test="@visibility='private'">&#160;private</xsl:when>
						<xsl:when test="@visibility='protected'">&#160;protected</xsl:when>
					</xsl:choose>
					<xsl:if test="@isAbstract='true'">&#160;abstract</xsl:if>
					<xsl:if test="@isStatic='true'">&#160;static</xsl:if>
					<xsl:choose>
						<xsl:when test="ownedParameter[@direction='return']">
							<xsl:text>&#160;</xsl:text>
							<xsl:call-template name="htmlType">
								<xsl:with-param name="relPathTop" select="$relPathTop"/>
								<xsl:with-param name="context" select="ownedParameter[@direction='return']"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</span>
			</td>
			<td valign="top">
				<span class="code">
					<xsl:variable name="parameters">
						<xsl:call-template name="htmlParametersBracket">
							<xsl:with-param name="relPathTop" select="$relPathTop"/>
						</xsl:call-template>
					</xsl:variable>
					<a href="#{concat(@name,$parameters)}" class="linkSummary"><xsl:value-of select="@name"/></a>
					<xsl:copy-of select="$parameters"/>
				</span>
			</td>
		</tr>
	</xsl:template>

	<!-- Field Detail -->
	<xsl:template name="class-field-detail">
		<xsl:param name="relPathTop"/>
		<div class="detail" id="{@name}">
			<h4><xsl:value-of select="@name" /></h4>
			<div class="detailContent">
				<span class="code">
					<xsl:choose>
						<xsl:when test="@isReadOnly='true'">const</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="@visibility='private'">private</xsl:when>
								<xsl:when test="@visibility='protected'">protected</xsl:when>
								<xsl:otherwise>public</xsl:otherwise>
							</xsl:choose>
							<xsl:if test="@isStatic='true'">&#160;static</xsl:if>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:if test="count(type) &gt; 0">
						<xsl:text>&#160;</xsl:text>
						<xsl:call-template name="htmlType">
							<xsl:with-param name="relPathTop" select="$relPathTop"/>
							<xsl:with-param name="context" select="."/>
						</xsl:call-template>
					</xsl:if>
					<b><xsl:value-of select="concat(' ', @name)" /></b>
					<xsl:if test="defaultValue">
						<xsl:value-of select="concat(' = ', defaultValue/@value)" />
					</xsl:if>
				</span>
				<p>
					<xsl:call-template name="htmlDescription">
						<xsl:with-param name="baseElement" select="@xmi:id"/>
					</xsl:call-template>
				</p>
			</div>
		</div>
		<hr/>
	</xsl:template>

	<!-- Method Detail -->
	<xsl:template name="class-method-detail">
		<xsl:param name="relPathTop"/>
		<xsl:param name="specifiedBy"/>
		<xsl:variable name="artifact" select="key('getElementById',key('getManifestation', @xmi:id)/@client)"/>
		
		<xsl:variable name="parameters">
			<xsl:call-template name="htmlParametersBracket">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
			</xsl:call-template>
		</xsl:variable>
		<div class="detail" id="{concat(@name,$parameters)}">
			<h4><xsl:value-of select="@name"/></h4>
			<div class="detailContent">
				<span class="code">
					<xsl:choose>
						<xsl:when test="@visibility='private'">private</xsl:when>
						<xsl:when test="@visibility='protected'">protected</xsl:when>
						<xsl:otherwise>public</xsl:otherwise>
					</xsl:choose>
					<xsl:if test="@isAbstract='true'">&#160;abstract</xsl:if>
					<xsl:if test="@isStatic='true'">&#160;static</xsl:if>
					<xsl:choose>
						<xsl:when test="ownedParameter[@direction='return']">
							<xsl:text>&#160;</xsl:text>
							<xsl:call-template name="htmlType">
								<xsl:with-param name="relPathTop" select="$relPathTop"/>
								<xsl:with-param name="context" select="ownedParameter[@direction='return']"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
					<b><xsl:value-of select="concat(' ', @name)" /></b>
					<xsl:copy-of select="$parameters"/>
				</span>
				
				<!-- Description and tags -->
				<p>
					<xsl:call-template name="htmlDescriptionParam">
						<xsl:with-param name="baseElement" select="@xmi:id"/>
					</xsl:call-template>
					<xsl:if test="count($artifact) &gt; 0">
						<b>File: </b>
						<xsl:for-each select="exslt:node-set($artifact)/ancestor::*[@xmi:type='uml:Package']">
							<xsl:if test="position() &gt; 1"><xsl:value-of select="concat('/',@name)"/></xsl:if>
						</xsl:for-each>
						<xsl:value-of select="concat('/', $artifact/@name)"/>
					</xsl:if>
				</p>
				
				<!-- Specified By -->
				<xsl:if test="count($specifiedBy) &gt; 0">
					
					<xsl:for-each select="exslt:node-set($specifiedBy)/ownedComment">
						<h3 class="titleSmallList">Description Copied From interface:</h3>
							<div class="smallList">
								<xsl:value-of select="@body"/>
								<xsl:call-template name="br-replace">
									<xsl:with-param name="str" select="body/text()"/>
								</xsl:call-template>
							</div>
					</xsl:for-each>

					<h3 class="titleSmallList">Specified By</h3>
					<div class="smallList">
						<xsl:for-each select="$specifiedBy">
							<xsl:variable name="classParent" select="parent::*"/>
							<xsl:variable name="path">
								<xsl:call-template name="getPackageFilePath">
									<xsl:with-param name="context" select="$classParent"/>
								</xsl:call-template>
								<xsl:call-template name="getPrefix">
									<xsl:with-param name="context" select="$classParent"/>
								</xsl:call-template>
							</xsl:variable>
							<span class="code">
								<a href="{concat($relPathTop,$path,$classParent/@name,'.html#',@name,$parameters)}" class="$linkType">
									<xsl:value-of select="@name"/>
								</a>
							</span>
								<xsl:text> in </xsl:text>
								<span class="code">
									<xsl:call-template name="htmlLinkToElement">
										<xsl:with-param name="relPathTop" select="$relPathTop"/>
										<xsl:with-param name="context" select="$classParent"/>
										<xsl:with-param name="style" select="string('linkSimple')"/>
									</xsl:call-template>
								</span>
						</xsl:for-each>
					</div>
				</xsl:if>
				
				<xsl:variable name="params" select="ownedParameter[not(@direction) or @direction!='return']"/>
				<xsl:if test="count($params) &gt; 0">
					<h3 class="titleSmallList">Parameters</h3>
					<div class="smallList">
						<xsl:variable name="id" select="@xmi:id"/>
						<xsl:for-each select="$params">
							<xsl:variable name="cnt" select="position()"/>
							<span class="code"><xsl:value-of select="@name"/></span>
							<xsl:text> - </xsl:text>
							<xsl:for-each select="key('getMetadata', $id)/*[local-name()='param'][$cnt]">
								<xsl:call-template name="htmlDocblockParam"/>
							</xsl:for-each>
							<br/>
						</xsl:for-each>
					</div>
				</xsl:if>
			</div>
		</div>
	</xsl:template>

	
</xsl:stylesheet>
