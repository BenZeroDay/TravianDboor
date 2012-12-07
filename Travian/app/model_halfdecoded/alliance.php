<?php
/*********************/
/*                   */
/*  Dezend for PHP5  */
/*         NWS       */
/*      Nulled.WS    */
/*                   */
/*********************/

class AllianceModel extends ModelBase
{

    public function getAllianceData( $allianceId )
    {
        return $this->provider->fetchRow( "SELECT \r\n\t\t\t\ta.*,\r\n\t\t\t\t(a.rating*100+a.player_count) score\r\n\t\t\tFROM p_alliances a \r\n\t\t\tWHERE a.id=%s", array( $allianceId ) );
    }

    public function getAllianceDataFor( $playerId )
    {
        return $this->provider->fetchRow( "SELECT \r\n\t\t\t\tp.alliance_id,\r\n\t\t\t\tp.alliance_name\r\n\t\t\tFROM p_players p \r\n\t\t\tWHERE p.id=%s", array( $playerId ) );
    }

    public function getLatestReports( $playerIds, $type )
    {
        $expr = "";
        if ( $type == 1 )
        {
            $expr = sprintf( "r.from_player_id IN (%s)", $playerIds );
        }
        else if ( $type == 2 )
        {
            $expr = sprintf( "(r.to_player_id IN (%s) AND IF(r.rpt_cat=4, r.rpt_result!=100,1))", $playerIds );
        }
        else
        {
            $expr = sprintf( "(r.from_player_id IN (%s) OR (r.to_player_id IN (%s) AND IF(r.rpt_cat=4, r.rpt_result!=100,1)))", $playerIds, $playerIds );
        }
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.id,\r\n\t\t\t\tr.from_player_id,\r\n\t\t\t\tr.to_player_id,\r\n\t\t\t\tr.from_player_name,\r\n\t\t\t\tr.to_player_name,\r\n\t\t\t\tr.rpt_result,\r\n\t\t\t\tr.rpt_cat,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d %%H:%%i') mdate,\r\n\t\t\t\t(r.from_player_id IN(%s)) isAttack\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE\r\n\t\t\t\t(r.rpt_cat=3 OR r.rpt_cat=4)\r\n\t\t\t\tAND %s\r\n\t\t\tORDER BY r.creation_date DESC\r\n\t\t\tLIMIT 20", array( $playerIds, $expr ) );
    }

    public function getAlliancePlayers( $players_ids )
    {
        if ( trim( $players_ids ) == "" )
        {
            return NULL;
        }
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tp.id,\r\n\t\t\t\tp.name,\r\n\t\t\t\tp.total_people_count,\r\n\t\t\t\tp.alliance_roles,\r\n\t\t\t\tp.villages_count,\r\n\t\t\t\tfloor(TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date))/3600) lastLoginFromHours\r\n\t\t\tFROM p_players p \r\n\t\t\tWHERE p.id IN (%s)\r\n\t\t\tORDER BY p.total_people_count DESC, p.villages_count DESC", array( $players_ids ) );
    }

    public function getPlayerName( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.name FROM p_players p WHERE p.id=%s", array( $playerId ) );
    }

    public function getAllianceRank( $allianceId, $score )
    {
        return $this->provider->fetchScalar( "SELECT (\r\n\t\t\t\t(SELECT\r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_alliances a\r\n\t\t\t\tWHERE \r\n\t\t\t\t\t(a.rating*100+a.player_count)>%s)\r\n\t\t\t\t+\r\n\t\t\t\t(SELECT \r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_alliances a\r\n\t\t\t\tWHERE \r\n\t\t\t\t\t(a.rating*100+a.player_count)=%s\r\n\t\t\t\t\tAND a.id<%s)\r\n\t\t\t) + 1 rank", array( $score, $score, $allianceId ) );
    }

    public function editAllianceData( $allianceId, $data, $playersIds )
    {
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.name='%s', a.name2='%s', a.description1='%s', a.description2='%s' WHERE a.id=%s", array( $data['name'], $data['name2'], $data['description1'], $data['description2'], $allianceId ) );
        $this->provider->executeQuery( "UPDATE p_players p SET p.alliance_name='%s' WHERE p.id IN(%s)", array( $data['name'], $playersIds ) );
        $this->provider->executeQuery( "UPDATE p_villages v SET v.alliance_name='%s' WHERE v.player_id IN(%s)", array( $data['name'], $playersIds ) );
    }

    public function removeFromAlliance( $playerId, $allianceId, $playersIds, $playersCount )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.alliance_id=NULL, p.alliance_name=NULL, p.alliance_roles=NULL WHERE p.id=%s", array( $playerId ) );
        $this->provider->executeQuery( "UPDATE p_villages v SET v.alliance_id=NULL, v.alliance_name=NULL WHERE v.player_id=%s", array( $playerId ) );
        if ( trim( $playersIds ) != "" )
        {
            $playersIdsArr = explode( ",", $playersIds );
            $playersIds = "";
            $i = 0;
            $c = sizeof( $playersIdsArr );
            while ( $i < $c )
            {
                if ( $playersIdsArr[$i] == $playerId )
                {
                    continue;
                }
                if ( $playersIds != "" )
                {
                    $playersIds .= ",";
                }
                $playersIds .= $playersIdsArr[$i];
                ++$i;
            }
        }
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.player_count=a.player_count-1, a.players_ids='%s' WHERE a.id=%s", array( $playersIds, $allianceId ) );
        if ( $playersCount == 1 )
        {
            $this->provider->executeQuery( "DELETE FROM p_alliances WHERE id=%s", array( $allianceId ) );
        }
        return $playersIds;
    }

    public function getPlayerAllianceRole( $playerId )
    {
        return $this->provider->fetchRow( "SELECT p.name, p.alliance_roles FROM p_players p WHERE p.id=%s", array( $playerId ) );
    }

    public function setPlayerAllianceRole( $playerId, $roleName, $roleNumber )
    {
        return $this->provider->executeQuery( "UPDATE p_players p SET p.alliance_roles='%s' WHERE p.id=%s", array( $roleNumber." ".$roleName, $playerId ) );
    }

    public function getPlayerId( $playerName )
    {
        return $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.name='%s'", array( $playerName ) );
    }

    public function _getNewInvite( $invitesString, $removeId )
    {
        if ( $invitesString == "" )
        {
            return "";
        }
        $result = "";
        $arr = explode( "\n", $invitesString );
        foreach ( $arr as $invite )
        {
            $name = explode( " ", $invite, 2 );
            $id = explode( " ", $invite, 2 );
            list( $id, $name ) = $id; 
            if ( $id == $removeId )
            {
                continue;
            }
            if ( $result != "" )
            {
                $result .= "\n";
            }
            $result .= $id." ".$name;
        }
        return $result;
    }

    public function removeAllianceInvites( $playerId, $allianceId )
    {
        $pRow = $this->provider->fetchRow( "SELECT p.name, p.invites_alliance_ids FROM p_players p WHERE p.id=%s", array( $playerId ) );
        $aRow = $this->provider->fetchRow( "SELECT a.name, a.invites_player_ids FROM p_alliances a WHERE a.id=%s", array( $allianceId ) );
        $pInvitesStr = $this->_getNewInvite( trim( $pRow['invites_alliance_ids'] ), $allianceId );
        $aInvitesStr = $this->_getNewInvite( trim( $aRow['invites_player_ids'] ), $playerId );
        $this->provider->executeQuery( "UPDATE p_players p SET p.invites_alliance_ids='%s' WHERE p.id=%s", array( $pInvitesStr, $playerId ) );
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.invites_player_ids='%s' WHERE a.id=%s", array( $aInvitesStr, $allianceId ) );
    }

    public function addAllianceInvites( $playerId, $allianceId )
    {
        $pRow = $this->provider->fetchRow( "SELECT p.name, p.invites_alliance_ids FROM p_players p WHERE p.id=%s", array( $playerId ) );
        $aRow = $this->provider->fetchRow( "SELECT a.name, a.invites_player_ids FROM p_alliances a WHERE a.id=%s", array( $allianceId ) );
        $pInvitesStr = $pRow['invites_alliance_ids'];
        if ( $pInvitesStr != "" )
        {
            $pInvitesStr .= "\n";
        }
        $pInvitesStr .= $allianceId." ".$aRow['name'];
        $aInvitesStr = $aRow['invites_player_ids'];
        if ( $aInvitesStr != "" )
        {
            $aInvitesStr .= "\n";
        }
        $aInvitesStr .= $playerId." ".$pRow['name'];
        $this->provider->executeQuery( "UPDATE p_players p SET p.invites_alliance_ids='%s' WHERE p.id=%s", array( $pInvitesStr, $playerId ) );
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.invites_player_ids='%s' WHERE a.id=%s", array( $aInvitesStr, $allianceId ) );
    }

    public function removeAllianceContracts( $allianceId1, $allianceId2 )
    {
        $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array( $allianceId1 ) );
        $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array( $allianceId2 ) );
        $contracts1 = "";
        if ( trim( $contracts_alliance_id1 ) != "" )
        {
            $arr = explode( ",", $contracts_alliance_id1 );
            foreach ( $arr as $arrStr )
            {
                $aStatus = explode( " ", $arrStr );
                $aid = explode( " ", $arrStr );
                list( $aid, $aStatus ) = $aid;  
                if ( $aid == $allianceId2 )
                {
                    continue;
                }
                if ( $contracts1 != "" )
                {
                    $contracts1 .= ",";
                }
                $contracts1 .= $arrStr;
            }
        }
        $contracts2 = "";
        if ( trim( $contracts_alliance_id2 ) != "" )
        {
            $arr = explode( ",", $contracts_alliance_id2 );
            foreach ( $arr as $arrStr )
            {
                $aStatus = explode( " ", $arrStr );
                $aid = explode( " ", $arrStr );
                list( $aid, $aStatus ) = $aid; 
                if ( $aid == $allianceId1 )
                {
                    continue;
                }
                if ( $contracts2 != "" )
                {
                    $contracts2 .= ",";
                }
                $contracts2 .= $arrStr;
            }
        }
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array( $contracts1, $allianceId1 ) );
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array( $contracts2, $allianceId2 ) );
    }

    public function acceptAllianceContracts( $allianceId1, $allianceId2 )
    {
        $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array( $allianceId1 ) );
        $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array( $allianceId2 ) );
        $contracts1 = "";
        if ( trim( $contracts_alliance_id1 ) != "" )
        {
            $arr = explode( ",", $contracts_alliance_id1 );
            foreach ( $arr as $arrStr )
            {
                $aStatus = explode( " ", $arrStr );
                $aid = explode( " ", $arrStr );
                list( $aid, $aStatus ) = $aid;
                if ( $aid == $allianceId2 )
                {
                    $aStatus = 0;
                }
                if ( $contracts1 != "" )
                {
                    $contracts1 .= ",";
                }
                $contracts1 .= $aid." ".$aStatus;
            }
        }
        $contracts2 = "";
        if ( trim( $contracts_alliance_id2 ) != "" )
        {
            $arr = explode( ",", $contracts_alliance_id2 );
            foreach ( $arr as $arrStr )
            {
                $aStatus = explode( " ", $arrStr );
                $aid = explode( " ", $arrStr );
                list( $aid, $aStatus ) = $aid;
                if ( $aid == $allianceId1 )
                {
                    $aStatus = 0;
                }
                if ( $contracts2 != "" )
                {
                    $contracts2 .= ",";
                }
                $contracts2 .= $aid." ".$aStatus;
            }
        }
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array( $contracts1, $allianceId1 ) );
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array( $contracts2, $allianceId2 ) );
    }

    public function addAllianceContracts( $allianceId1, $allianceId2 )
    {
        $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array( $allianceId1 ) );
        $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array( $allianceId2 ) );
        $contracts1 = $contracts_alliance_id1;
        if ( $contracts1 != "" )
        {
            $contracts1 .= ",";
        }
        $contracts1 .= $allianceId2." 1";
        $contracts2 = $contracts_alliance_id2;
        if ( $contracts2 != "" )
        {
            $contracts2 .= ",";
        }
        $contracts2 .= $allianceId1." 2";
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array( $contracts1, $allianceId1 ) );
        $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array( $contracts2, $allianceId2 ) );
    }

    public function getAllianceId( $allianceName )
    {
        return $this->provider->fetchScalar( "SELECT a.id FROM p_alliances a WHERE a.name='%s'", array( $allianceName ) );
    }

    public function getAllianceName( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT a.name FROM p_alliances a WHERE a.id=%s", array( $allianceId ) );
    }

}

?>
