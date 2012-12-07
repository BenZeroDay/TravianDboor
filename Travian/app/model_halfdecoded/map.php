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

class MapModel extends ModelBase
{

    public function getVillagesMatrix( $matrixStr )
    {
        return $this->provider->fetchResultSet( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.rel_x, v.rel_y, v.field_maps_id, \r\n\t\t\t\tv.image_num, v.tribe_id, v.player_id, v.alliance_id, \r\n\t\t\t\tv.player_name, v.village_name, v.alliance_name,\r\n\t\t\t\tv.people_count, v.is_oasis\r\n\t\t\tFROM\r\n\t\t\t\tp_villages v \r\n\t\t\tWHERE\r\n\t\t\t\tv.id IN (%s)", array(
            $matrixStr
        ) );
    }

    public function getContractsAllianceId( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
            $allianceId
        ) );
    }

}

?>
