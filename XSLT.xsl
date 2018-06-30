<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    exclude-result-prefixes="xs"
    version="2.0">
    <xsl:output method="xml" indent="yes"/>
    
    <xsl:template match="/">
        <em>
            <liste-pays>
            <xsl:apply-templates select="mondial/country"/>
            </liste-pays>
            <liste-espace-maritime>
                <xsl:apply-templates select="mondial/sea"/>
            </liste-espace-maritime>
        </em>
    </xsl:template>
    
    <xsl:template match="mondial/country">
        <pays>
            <xsl:attribute name="id-p" select="@car_code"/>
            <xsl:attribute name="nom-p" select="name"/>
            <xsl:attribute name="superficie" select="@area"/>
            <xsl:attribute name="nbhab" select="population[@year = max(../population/@year)]"/>
            <xsl:apply-templates select="/mondial/river[source/@country eq current()/@car_code and ./to/@watertype eq 'sea']"/>
        </pays>
    </xsl:template>
    
    <xsl:template match="/mondial/river">
        <fleuve>
            <xsl:attribute name="id-f" select="@id"/>
            <xsl:attribute name="nom-f" select="name"/>
            <xsl:attribute name="longueur" select="length"/>
            <xsl:attribute name="se-jette" select="to/@water"/>
            <xsl:apply-templates select="current()/located"/>
        </fleuve>
    </xsl:template>

    <xsl:template match="/mondial/river/located">
        <parcourt>
            <xsl:attribute name="id-pays" select="@country"/>
            <xsl:choose>
                <xsl:when test = "string-length(../../@country) &lt; 3">
                    <xsl:attribute name="distance" select="../length"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="distance" select="'inconnue'"/>
                </xsl:otherwise>
            </xsl:choose>
        </parcourt>
    </xsl:template>
    
    <xsl:template match ="/mondial/sea">
        <espace-maritime>
            <xsl:attribute name="id-e" select="@id"/>
            <xsl:attribute name="nom-e" select="name"/>    
            <xsl:attribute name="type" select = "'inconnu'"/>
            <xsl:apply-templates select ="id(@country)" mode="espaceMaritime"/>
        </espace-maritime>     
    </xsl:template>
    
    <xsl:template match="country" mode="espaceMaritime">
        <cotoie>
            <xsl:attribute name="id-p" select ="./@car_code"/>
        </cotoie>
    </xsl:template>  
</xsl:stylesheet>