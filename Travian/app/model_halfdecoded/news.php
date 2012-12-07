<?php
/**
*
* @ This file is created by Decodeby.US
* @ deZender Public (PHP5 Decompiler)
*
* @	Version			:	1.0.0.0
* @	Author			:	Ps2Gamer & Cyko
* @	Release on		:	30.05.2011
* @	Official site	:	http://decodeby.us
*
*/

class NewsModel extends ModelBase
{

    public function getSiteNews( )
    {
        return $this->provider->fetchScalar( "SELECT g.news_text FROM g_summary g" );
    }

    public function setSiteNews( $news )
    {
        $this->provider->executeQuery( "UPDATE g_summary g SET g.news_text='%s'", array(
            $news
        ) );
    }

    public function getGlobalSiteNews( )
    {
        return $this->provider->fetchScalar( "SELECT g.gnews_text FROM g_summary g" );
    }

    public function setGlobalPlayerNews( $news )
    {
        $this->provider->executeQuery( "UPDATE g_summary g SET g.gnews_text='%s'", array(
            $news
        ) );
        $flag = trim( $news ) != "" ? 1 : 0;
        $this->provider->executeQuery( "UPDATE p_players p SET p.new_gnews=%s", array(
            $flag
        ) );
    }

}

?>
