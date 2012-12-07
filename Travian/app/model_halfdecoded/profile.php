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

class ProfileModel extends ModelBase
{

    public function getPlayerIdByName( $playerName )
    {
        return $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.name='%s'", array(
            $playerName
        ) );
    }

    public function getPlayerAgentForById( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.agent_for_players FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function getPlayerMyAgentById( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.my_agent_players FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function setMyAgents( $playerId, $playerName, $agents, $newAgentId )
    {
        $agentStr = "";
        foreach ( $agents as $agentId => $agentName )
        {
            if ( $agentStr != "" )
            {
                $agentStr .= ",";
            }
            $agentStr .= $agentId." ".$agentName;
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.my_agent_players='%s' WHERE p.id=%s", array(
            $agentStr,
            $playerId
        ) );
        $agentFor = $playerId." ".$playerName;
        $this->provider->executeQuery( "UPDATE p_players p SET p.agent_for_players=IF(ISNULL(p.agent_for_players) OR p.agent_for_players='', '%s', CONCAT_WS(',', p.agent_for_players, '%s')) WHERE p.id=%s", array(
            $agentFor,
            $agentFor,
            $newAgentId
        ) );
    }

    public function removeMyAgents( $playerId, $agents, $aid )
    {
        $agentStr = "";
        foreach ( $agents as $agentId => $agentName )
        {
            if ( $agentStr != "" )
            {
                $agentStr .= ",";
            }
            $agentStr .= $agentId." ".$agentName;
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.my_agent_players='%s' WHERE p.id=%s", array(
            $agentStr,
            $playerId
        ) );
        $agentForStr = $this->getPlayerAgentForById( $aid );
        $agentForPlayers = trim( $agentForStr ) == "" ? array( ) : explode( ",", $agentForStr );
        $i = 0;
        $c = sizeof( $agentForPlayers );
        while ( $i < $c )
        {
            $agent = $agentForPlayers[$i];
            $agentName = explode( " ", $agent );
            $agentId = explode( " ", $agent );
            list( $agentId, $agentName ) = $agentId;
            if ( $agentId == $playerId )
            {
                unset( $agentForPlayers[$i] );
            }
            ++$i;
        }
        $agentForStr = implode( ",", $agentForPlayers );
        $this->provider->executeQuery( "UPDATE p_players p SET p.agent_for_players='%s' WHERE p.id=%s", array(
            $agentForStr,
            $aid
        ) );
    }

    public function removeAgentsFor( $playerId, $agents, $aid )
    {
        $agentStr = "";
        foreach ( $agents as $agentId => $agentName )
        {
            if ( $agentStr != "" )
            {
                $agentStr .= ",";
            }
            $agentStr .= $agentId." ".$agentName;
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.agent_for_players='%s' WHERE p.id=%s", array(
            $agentStr,
            $playerId
        ) );
        $agentForStr = $this->getPlayerMyAgentById( $aid );
        $agentForPlayers = trim( $agentForStr ) == "" ? array( ) : explode( ",", $agentForStr );
        $i = 0;
        $c = sizeof( $agentForPlayers );
        while ( $i < $c )
        {
            $agent = $agentForPlayers[$i];
            $agentName = explode( " ", $agent );
            $agentId = explode( " ", $agent );
            list( $agentId, $agentName ) = $agentId;            
            if ( $agentId == $playerId )
            {
                unset( $agentForPlayers[$i] );
            }
            ++$i;
        }
        $agentForStr = implode( ",", $agentForPlayers );
        $this->provider->executeQuery( "UPDATE p_players p SET p.my_agent_players='%s' WHERE p.id=%s", array(
            $agentForStr,
            $aid
        ) );
    }

    public function editPlayerProfile( $playerId, $data )
    {
        $selected_village_id = $this->provider->fetchScalar( "SELECT p.selected_village_id FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
        $villages_data_arr = array( );
        $villages_id_arr = explode( "\n", $data['villages'] );
        $i = 0;
        $c = sizeof( $villages_id_arr );
        while ( $i < $c )
        {
            $vname = explode( " ", $villages_id_arr[$i], 4 );
            $y = explode( " ", $villages_id_arr[$i], 4 );
            $x = explode( " ", $villages_id_arr[$i], 4 );
            $vid = explode( " ", $villages_id_arr[$i], 4 );
            list( $vid, $x, $y, $vname ) = $vid;            
            if ( $vid == $selected_village_id )
            {
                $vname = $data['village_name'];
                $villages_id_arr[$i] = $vid." ".$x." ".$y." ".$vname;
            }
            $villages_data_arr[$vname][] = $villages_id_arr[$i];
            ++$i;
        }
        ksort( $villages_data_arr );
        $villages_data = "";
        foreach ( $villages_data_arr as $k => $v )
        {
            foreach ( $villages_data_arr[$k] as $v2 )
            {
                if ( $villages_data != "" )
                {
                    $villages_data .= "\n";
                }
                $villages_data .= $v2;
            }
        }
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.birth_date='%s',\r\n\t\t\t\tp.gender=%s,\r\n\t\t\t\tp.house_name='%s',\r\n\t\t\t\tp.description1='%s',\r\n\t\t\t\tp.description2='%s',\r\n\t\t\t\tp.villages_data='%s',\r\n\t\t\t\tp.avatar='%s'\r\n\t\t\tWHERE p.id=%s", array(
            $data['birthData'],
            $data['gender'],
            $data['house_name'],
            $data['description1'],
            $data['description2'],
            $villages_data,
            $data['avatar'],
            $playerId
        ) );
        $village_name = trim( $data['village_name'] );
        if ( $village_name != "" )
        {
            $this->provider->executeQuery( "UPDATE p_villages v SET v.village_name='%s' WHERE v.id=%s", array(
                $village_name,
                $selected_village_id
            ) );
        }
    }

    public function changePlayerPassword( $playerId, $newPassword )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.pwd='%s' WHERE p.id=%s", array(
            $newPassword,
            $playerId
        ) );
    }

    public function changePlayerEmail( $playerId, $newEmail )
    {
        if ( 0 < intval( $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE p.email='%s'", array(
            $newEmail
        ) ) ) )
        {
            return;
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.email='%s' WHERE p.id=%s", array(
            $newEmail,
            $playerId
        ) );
    }

    public function getPlayerRank( $playerId, $score )
    {
        return $this->provider->fetchScalar( "SELECT (\r\n\t\t\t\t(SELECT\r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_players p\r\n\t\t\t\tWHERE p.player_type!=%s AND (p.total_people_count*10+p.villages_count)>%s) \r\n\t\t\t\t+\r\n\t\t\t\t(SELECT \r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_players p\r\n\t\t\t\tWHERE p.player_type!=%s AND p.id<%s AND (p.total_people_count*10+p.villages_count)=%s)\r\n\t\t\t) + 1 rank", array(
            PLAYERTYPE_TATAR,
            $score,
            PLAYERTYPE_TATAR,
            $playerId,
            $score
        ) );
    }

    public function getWinnerPlayer( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT gs.win_pid FROM g_settings gs" ) );
        return $this->getPlayerDataById( $playerId );
    }

    public function getPlayerDataById( $playerId )
    {
        $protectionPeriod = intval( $GLOBALS['GameMetadata']['player_protection_period'] / $GLOBALS['GameMetadata']['game_speed'] );
        return $this->provider->fetchRow( "SELECT\r\n\t\t\t\tp.id,\r\n\t\t\t\tp.tribe_id,\r\n\t\t\t\tp.alliance_id,\r\n\t\t\t\tp.alliance_name,\r\n\t\t\t\tp.house_name, \r\n\t\t\t\tp.is_blocked,\r\n\t\t\t\tp.birth_date,\r\n\t\t\t\tp.gender,\r\n\t\t\t\tp.description1, p.description2,\r\n\t\t\t\tp.medals,\r\n\t\t\t\tp.total_people_count,\r\n\t\t\t\tp.villages_count,\r\n\t\t\t\tp.name,\r\n\t\t\t\tp.avatar,\r\n\t\t\t\tp.villages_id,\r\n\t\t\t\tDATE_FORMAT(registration_date, '%%Y/%%m/%%d %%H:%%i') registration_date,\r\n\t\t\t\tTIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW()) protection_remain,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW())) protection_remain_sec,\r\n\t\t\t\tDATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(birth_date)), '%%Y')+0 age\r\n\t\t\tFROM p_players p\r\n\t\t\tWHERE p.id=%s", array(
            $protectionPeriod,
            $protectionPeriod,
            $playerId
        ) );
    }

    public function getVillagesSummary( $villages_id )
    {
        return $this->provider->fetchResultSet( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.rel_x, v.rel_y,\r\n\t\t\t\tv.village_name,\r\n\t\t\t\tv.people_count,\r\n\t\t\t\tv.is_capital\r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id IN (%s)\r\n\t\t\tORDER BY v.people_count DESC", array(
            $villages_id
        ) );
    }

    public function resetGNewsFlag( $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.new_gnews=0 WHERE p.id=%s", array(
            $playerId
        ) );
    }

}

?>
