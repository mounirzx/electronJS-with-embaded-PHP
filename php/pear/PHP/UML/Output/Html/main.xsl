<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<!-- The starting template -->
	<xsl:include href="documentation-generator.xsl"/>

	<!-- Some common templates for documentation generation -->
	<xsl:include href="documentation-common.xsl"/>

	<!-- The user templates -->
	<xsl:include href="classifier.xsl"/>
	<xsl:include href="package-summary.xsl"/>
	<xsl:include href="overview-summary.xsl"/>
	<xsl:include href="overview-frame.xsl"/>
	<xsl:include href="allclasses-frame.xsl"/>
	<xsl:include href="package-frame.xsl"/>
	<xsl:include href="index.xsl"/>
	<xsl:include href="index-all.xsl"/>

</xsl:stylesheet>