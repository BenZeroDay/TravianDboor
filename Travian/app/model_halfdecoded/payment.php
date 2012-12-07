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

class PaymentModel extends ModelBase
{

    public function incrementPlayerGold( $playerId, $goldNumber )
    {
        $this->provider->executeQuery( "UPDATE p_players p \r\n\t\t\tSET\r\n\t\t\t\tp.gold_num=gold_num+%s\r\n\t\t\tWHERE p.id=%s", array(
            $goldNumber,
            $playerId
        ) );
    }

}

?>
