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

class VillageModel extends ModelBase
{

    public function getLatestReports( $playerId, $villageId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.id,\r\n\t\t\t\tr.rpt_result,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d') mdate,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%H:%%i') mtime,\r\n\t\t\t\tFALSE isAttack\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE\r\n\t\t\t\tr.to_player_id=%s\r\n\t\t\t\tAND r.to_village_id=%s\r\n\t\t\t\tAND (r.rpt_cat=3 OR (r.rpt_cat=4 AND r.rpt_result!=100))\r\n\t\t\tORDER BY r.creation_date DESC\r\n\t\t\tLIMIT 5", array(
            $playerId,
            $villageId
        ) );
    }

    public function getLatestReports2( $fromPlayerIds, $playerId, $villageId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.id,\r\n\t\t\t\tr.rpt_result,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d') mdate,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%H:%%i') mtime,\r\n\t\t\t\tTRUE isAttack\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE\r\n\t\t\t\tr.to_player_id=%s\r\n\t\t\t\tAND r.to_village_id=%s\r\n\t\t\t\tAND r.from_player_id IN (%s)\r\n\t\t\t\tAND (r.rpt_cat=3 OR r.rpt_cat=4)\r\n\t\t\tORDER BY r.creation_date DESC\r\n\t\t\tLIMIT 5", array(
            $playerId,
            $villageId,
            $fromPlayerIds
        ) );
    }

    public function getAlliancePlayersId( $alliance_id )
    {
        return $this->provider->fetchScalar( "SELECT a.players_ids FROM p_alliances a WHERE a.id=%s", array(
            $alliance_id
        ) );
    }

    public function getPlayType( $player_id )
    {
        return $this->provider->fetchScalar( "SELECT p.player_type FROM p_players p WHERE p.id=%s", array(
            $player_id
        ) );
    }

    public function getMapItemData( $villageId )
    {
        return $this->provider->fetchRow( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.rel_x, v.rel_y, v.field_maps_id, v.is_capital,\r\n\t\t\t\tv.image_num, v.tribe_id, v.player_id, v.alliance_id, v.parent_id, \r\n\t\t\t\tv.player_name, v.village_name, v.alliance_name,\r\n\t\t\t\tv.people_count, v.is_oasis, v.troops_num,\r\n\t\t\t\tv.allegiance_percent,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.creation_date)) elapsedTimeInSeconds\r\n\t\t\tFROM\r\n\t\t\t\tp_villages v \r\n\t\t\tWHERE\r\n\t\t\t\tv.id=%s", array(
            $villageId
        ) );
    }

    public function getVillageName( $villageId )
    {
        return $this->provider->fetchScalar( "SELECT v.village_name FROM p_villages v WHERE v.id=%s", array(
            $villageId
        ) );
    }

}

?>
