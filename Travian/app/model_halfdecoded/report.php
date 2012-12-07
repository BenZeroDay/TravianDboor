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

class ReportModel extends ModelBase
{

    public $maxReportBoxSize = 25;

    public function getPlayerAllianceId( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.alliance_id FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function getReportListCount( $playerId, $cat )
    {
        $expr = $cat == 0 ? "" : " AND r.rpt_cat=".$cat;
        return $this->provider->fetchScalar( "SELECT COUNT(*) \r\n\t\t\tFROM p_rpts r \r\n\t\t\tWHERE \r\n\t\t\t\t( (r.to_player_id=%s AND r.delete_status!=1) OR (r.from_player_id=%s AND r.delete_status!=2) )%s", array(
            $playerId,
            $playerId,
            $expr
        ) );
    }

    public function getReportList( $playerId, $cat, $pageIndex, $pageSize )
    {
        $expr = $cat == 0 ? "" : " AND r.rpt_cat=".$cat;
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.id,\r\n\t\t\t\tr.to_player_id,\r\n\t\t\t\tr.from_player_id,\r\n\t\t\t\tr.from_village_name,\r\n\t\t\t\tr.to_village_name,\r\n\t\t\t\tr.rpt_cat,\r\n\t\t\t\tr.rpt_result,\r\n\t\t\t\tIF(r.to_player_id=%s, r.read_status=1 OR r.read_status=3, r.read_status=2 OR r.read_status=3) is_readed,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d %%H:%%i') mdate\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE \r\n\t\t\t\t( (r.to_player_id=%s AND r.delete_status!=1) OR (r.from_player_id=%s AND r.delete_status!=2) )%s\r\n\t\t\tORDER BY r.creation_date DESC\r\n\t\t\tLIMIT %s,%s", array(
            $playerId,
            $playerId,
            $playerId,
            $expr,
            $pageIndex * $pageSize,
            $pageSize
        ) );
    }

    public function deleteReport( $playerId, $reportId )
    {
        $result = $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.to_player_id,\r\n\t\t\t\tr.from_player_id,\r\n\t\t\t\tr.read_status,\r\n\t\t\t\tr.delete_status\r\n\t\t\tFROM p_rpts r \r\n\t\t\tWHERE \r\n\t\t\t\tr.id=%s AND (r.from_player_id=%s OR r.to_player_id=%s)", array(
            $reportId,
            $playerId,
            $playerId
        ) );
        if ( !$result->next( ) )
        {
            return FALSE;
        }
        $deleteStatus = $result->row['delete_status'];
        $toPlayerId = $result->row['to_player_id'];
        $fromPlayerId = $result->row['from_player_id'];
        $readStatus = $result->row['read_status'];
        $result->free( );
        if ( $deleteStatus != 0 || $fromPlayerId == $toPlayerId )
        {
            $this->provider->executeQuery( "DELETE FROM p_rpts\r\n\t\t\t\tWHERE\r\n\t\t\t\t\tid=%s AND (from_player_id=%s OR to_player_id=%s)", array(
                $reportId,
                $playerId,
                $playerId
            ) );
        }
        else
        {
            $this->provider->executeQuery( "UPDATE p_rpts r\r\n\t\t\t\tSET\r\n\t\t\t\t\tr.delete_status=%s\r\n\t\t\t\tWHERE\r\n\t\t\t\t\tr.id=%s AND (r.from_player_id=%s OR r.to_player_id=%s)", array(
                $toPlayerId == $playerId ? 1 : 2,
                $reportId,
                $playerId,
                $playerId
            ) );
        }
        if ( $toPlayerId == $playerId )
        {
            if ( $readStatus == 0 || $readStatus == 2 )
            {
                $this->markReportAsReaded( $playerId, $toPlayerId, $reportId, $readStatus );
                return TRUE;
            }
        }
        else
        {
            if ( $readStatus == 0 || $readStatus == 1 )
            {
                $this->markReportAsReaded( $playerId, $toPlayerId, $reportId, $readStatus );
                return TRUE;
            }
        }
        return FALSE;
    }

    public function markReportAsReaded( $playerId, $rtoPlayerId, $reportId, $read_status )
    {
        $newReadStatus = ( $playerId == $rtoPlayerId ? 1 : 2 ) + $read_status;
        $this->provider->executeQuery( "UPDATE p_rpts r SET r.read_status=%s WHERE r.id=%s", array(
            $newReadStatus,
            $reportId
        ) );
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.new_report_count=IF(p.new_report_count-1<0, 0, p.new_report_count-1)\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
            $playerId
        ) );
    }

    public function getReport( $reportId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.from_player_id,\r\n\t\t\t\tr.to_player_id,\r\n\t\t\t\tr.from_village_id,\r\n\t\t\t\tr.to_village_id,\r\n\t\t\t\tr.from_player_name,\r\n\t\t\t\tr.to_player_name,\r\n\t\t\t\tr.from_village_name,\r\n\t\t\t\tr.to_village_name,\r\n\t\t\t\tr.rpt_body,\r\n\t\t\t\tr.rpt_cat,\r\n\t\t\t\tr.read_status,\r\n\t\t\t\tr.delete_status,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d') mdate,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%H:%%i:%%s') mtime\r\n\t\t\tFROM p_rpts r \r\n\t\t\tWHERE \r\n\t\t\t\tr.id=%s", array(
            $reportId
        ) );
    }

    public function getPlayerName( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.name FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function getVillageName( $villageId )
    {
        return $this->provider->fetchScalar( "SELECT v.village_name FROM p_villages v WHERE v.id=%s", array(
            $villageId
        ) );
    }

    public function createReport( $fromPlayerId, $toPlayerId, $fromVillageId, $toVillageId, $reportCategory, $reportResult, $body, $timeInSeconds )
    {
        $fromPlayerId = intval( $fromPlayerId );
        $toPlayerId = intval( $toPlayerId );
        $fromVillageId = intval( $fromVillageId );
        $toVillageId = intval( $toVillageId );
        $fromPlayerName = $this->getPlayerName( $fromPlayerId );
        $toPlayerName = $this->getPlayerName( $toPlayerId );
        $fromVillageName = $this->getVillageName( $fromVillageId );
        $toVillageName = $this->getVillageName( $toVillageId );
        $this->provider->executeQuery( "INSERT p_rpts\r\n\t\t\tSET\r\n\t\t\t\tfrom_player_id=%s,\r\n\t\t\t\tfrom_player_name='%s',\r\n\t\t\t\tto_player_id=%s,\r\n\t\t\t\tto_player_name='%s',\r\n\t\t\t\tfrom_village_id=%s,\r\n\t\t\t\tfrom_village_name='%s',\r\n\t\t\t\tto_village_id=%s,\r\n\t\t\t\tto_village_name='%s',\r\n\t\t\t\trpt_cat=%s,\r\n\t\t\t\trpt_result=%s,\r\n\t\t\t\trpt_body='%s',\r\n\t\t\t\tcreation_date=DATE_ADD(NOW(), INTERVAL %s SECOND),\r\n\t\t\t\tread_status=0,\r\n\t\t\t\tdelete_status=0", array(
            $fromPlayerId,
            $fromPlayerName,
            $toPlayerId,
            $toPlayerName,
            $fromVillageId,
            $fromVillageName,
            $toVillageId,
            $toVillageName,
            $reportCategory,
            $reportResult,
            $body,
            $timeInSeconds
        ) );
        $reportId = intval( $this->provider->fetchScalar( "SELECT LAST_INSERT_ID() FROM p_rpts" ) );
        $this->provider->executeQuery( "UPDATE p_players p SET p.new_report_count=p.new_report_count+1 WHERE p.id=%s", array(
            $fromPlayerId
        ) );
        if ( $fromPlayerId != $toPlayerId )
        {
            $this->provider->executeQuery( "UPDATE p_players p SET p.new_report_count=p.new_report_count+1 WHERE p.id=%s", array(
                $toPlayerId
            ) );
        }
        while ( 0 < ( $rid = $this->provider->fetchScalar( "SELECT MIN(r.id) id FROM p_rpts r WHERE r.delete_status!=1 AND r.to_player_id=%s GROUP BY r.from_player_id HAVING COUNT(*)>%s", array(
            $toPlayerId,
            $this->maxReportBoxSize
        ) ) ) )
        {
            $this->deleteReport( $toPlayerId, $rid );
        }
        while ( $fromPlayerId != $toPlayerId && 0 < ( $rid = $this->provider->fetchScalar( "SELECT MIN(r.id) id FROM p_rpts r WHERE r.delete_status!=2 AND r.from_player_id=%s GROUP BY r.from_player_id HAVING COUNT(*)>%s", array(
            $fromPlayerId,
            $this->maxReportBoxSize
        ) ) ) )
        {
            $this->deleteReport( $fromPlayerId, $rid );
        }
        return $reportId;
    }

    public function syncReports( $playerId )
    {
        $newCount = intval( $this->provider->fetchScalar( "SELECT\r\n\t\t\t\tCOUNT(*)\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE \r\n\t\t\t\t((r.to_player_id=%s AND r.delete_status!=1) OR (r.from_player_id=%s AND r.delete_status!=2))\r\n\t\t\t\tAND\r\n\t\t\t\t(IF(r.to_player_id=%s, r.read_status=1 OR r.read_status=3, r.read_status=2 OR r.read_status=3) = FALSE)", array(
            $playerId,
            $playerId,
            $playerId
        ) ) );
        if ( $newCount < 0 )
        {
            $newCount = 0;
        }
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.new_report_count=%s\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
            $newCount,
            $playerId
        ) );
        return $newCount;
    }

}

?>
