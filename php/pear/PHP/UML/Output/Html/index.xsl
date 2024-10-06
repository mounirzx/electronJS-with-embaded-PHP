<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">
	
<xsl:template name="index">
	<xsl:param name="containerName" select="@name"/>
	<html>
		<xsl:call-template name="htmlStartPage"/>
		<xsl:call-template name="htmlHead">
			<xsl:with-param name="path" />
			<xsl:with-param name="title" select="concat($containerName, '&#32;API&#32;Documentation')"/>
		</xsl:call-template>

		<script type="text/javascript">
			<![CDATA[
				targetPage = "" + window.location.search;
				if (targetPage != "" && targetPage != "undefined")
					targetPage = targetPage.substring(1);
				function loadFrames() {
					if (targetPage != "" && targetPage != "undefined")
						top.classFrame.location = top.targetPage;
				}
		   ]]>
		</script>
		<frameset cols="20%,80%" title="" onLoad="top.loadFrames()">
			<frameset rows="30%,70%" title="" onLoad="top.loadFrames()">
				<frame src="overview-frame.html" name="packageListFrame" title="All Packages" />
				<frame src="allclasses-frame.html" name="packageFrame" title="All classes and interfaces (except non-static nested types)" />
			</frameset>
			<frame src="overview-summary.html" name="classFrame" title="Package, class and interface descriptions" scrolling="yes" />
			<noframes>
			<h2>Frame Alert</h2>
			<p>
			This document is designed to be viewed using the frames feature.
			If you see this message, you are using a non-frame-capable web client.<br/>
			Link to<a HREF="overview-summary.html">Non-frame version.</a>
			</p>
			</noframes>
		</frameset>
	</html>
</xsl:template>

</xsl:stylesheet>
