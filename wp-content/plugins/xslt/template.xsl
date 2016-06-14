<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:msxsl="urn:schemas-microsoft-com:xslt" version="1.0">
<xsl:output method="html" encoding="utf-8"/>
<xsl:variable name="title" select="/rss/channel/title"/>
<xsl:variable name="feedUrl" select="/rss/channel/atom10:link[@rel='self']/@href" xmlns:atom10="http://www.w3.org/2005/Atom"/>
<xsl:variable name="link" select="/rss/channel/link"/>
<xsl:variable name="feedUrlEncoded" select="/rss/channel/encoded"/>
<xsl:template match="/">
<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html></xsl:text>
<xsl:element name="html">
<head>
	<title><xsl:value-of select="$title"/></title>
	<link href="{$link}/wp-content/plugins/xslt/template.css" rel="stylesheet" type="text/css" media="all"/>
	<link rel="alternate" type="application/rss+xml" title="{$title}" href="{$feedUrl}"/>
	<script><![CDATA[
function pop(url){
	var k=window.open(url,'','left='+(screen.width/2-300)+',top='+(screen.height/2-300)+',width=600,height=600,personalbar=0,toolbar=0,scrollbars=1,resizable=1');
	if(k===null||!k){
		alert('The share dialog was blocked by your popup blocker. Please disable your popup blocker.');
		return;
	}
	k.focus();
}
	]]></script>
</head>
<body>
	<div id="header">
		<h1 class="title"><a href="{normalize-space($link)}" title="Return to {$title}"><xsl:value-of select="$title"/></a></h1>
		<h2 class="subtitle"><xsl:value-of select="/rss/channel/description"/></h2>
		
		<div class="share">
			<a href="http://cloud.feedly.com/#subscription%2Ffeed%2F{$feedUrl}" target="_blank">Feedly</a> 
			<a href="http://digg.com/reader/search/{$feedUrlEncoded}" target="_blank">Digg Reader</a>
			<a href="http://reader.aol.ca/#subscription/{$feedUrl}" target="_blank">Aol Reader</a>
			<a href="http://theoldreader.com/feeds/subscribe?url={$feedUrlEncoded}" target="_blank">The Old Reader</a>
		</div>
	</div>
	<div id="main">

		<xsl:for-each select="/rss/channel/item">
			<div class="post">
				<h3 class="title"><a href="{normalize-space(link)}"><xsl:value-of select="title"/></a></h3>
				
				<div class="tagline">
					<a href="{normalize-space(comments)}">
						<xsl:if test="count(child::slash:comments)=1">
							<xsl:value-of select="slash:comments"/> 
						</xsl:if>
					Comments</a>
					
					<xsl:if test="count(child::pubDate)=1">
						&#160;-&#160;  <xsl:value-of select="normalize-space(substring(pubDate,5,string-length(pubDate)-10))"/>
					</xsl:if>
				</div>
				
				<div class="content">
					<xsl:value-of select="description" disable-output-escaping="yes"/>					 
				</div>
				
				<div class="share">
					<a href="#" onclick="pop('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('{normalize-space(link)}'));return false;">Facebook</a>
					<a href="#" onclick="pop('https://twitter.com/intent/tweet?url='+encodeURIComponent('{normalize-space(link)}'));return false;">Twitter</a>
					<a href="#" onclick="pop('https://plus.google.com/share?url='+encodeURIComponent('{normalize-space(link)}'));return false;">Google+</a>
				</div>
			</div>
		</xsl:for-each>
	</div>
	<div id="footer">
		 <p>Powered by <a href="http://wordpress.org" rel="nofollow">WordPress</a> and <a href="https://wordpress.org/plugins/xslt/" rel="nofollow">Better RSS Feeds</a></p>
	</div>
</body>
</xsl:element>
</xsl:template>
</xsl:stylesheet>
