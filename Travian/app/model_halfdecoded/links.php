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

class LinksModel extends ModelBase
{

    public function changePlayerLinks( $playerId, $links )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.custom_links='%s' WHERE p.id=%s", array(
            $links,
            $playerId
        ) );
    }

}

?>
