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

class NotesModel extends ModelBase
{

    public function changePlayerNotes( $playerId, $notes )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.notes='%s' WHERE p.id=%s", array(
            $notes,
            $playerId
        ) );
    }

}

?>
