<?php
/*********************/
/*                   */
/*  Dezend for PHP5  */
/*         NWS       */
/*      Nulled.WS    */
/*                   */
/*********************/

class GlobalModel extends ModelBase
{

    public function getSiteNews( )
    {
        return $this->provider->fetchScalar( "SELECT gs.news_text FROM g_summary gs" );
    }

    public function setSelectedVillage( $playerId, $villageId )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.selected_village_id=%s WHERE  p.id=%s", array( $villageId, $playerId ) );
    }

    public function hasVillage( $playerId, $villageId )
    {
        return intval( $this->provider->fetchScalar( "SELECT v.player_id FROM p_villages v WHERE v.id=%s", array( $villageId ) ) ) == $playerId;
    }

    public function getVillageData( $playerId )
    {
        $GameMetadata = $GLOBALS['GameMetadata'];
        $protectionPeriod = intval( $GameMetadata['player_protection_period'] / $GameMetadata['game_speed'] );
        $sessionTimeoutInSeconds = $GameMetadata['session_timeout'] * 60;
        $data = $this->provider->fetchRow( "SELECT\r\n\t\t\t\tp.alliance_id,\r\n\t\t\t\tp.alliance_name,\r\n\t\t\t\tp.alliance_roles,\r\n\t\t\t\tp.house_name,\r\n\t\t\t\tp.avatar,\r\n\t\t\t\tp.birth_date,\r\n\t\t\t\tp.gender,\r\n\t\t\t\tp.description1, p.description2,\r\n\t\t\t\tp.agent_for_players, p.my_agent_players, \r\n\t\t\t\tp.medals,\r\n\t\t\t\tp.total_people_count,\r\n\t\t\t\tp.villages_count,\r\n\t\t\t\tp.player_type,\r\n\t\t\t\tp.active_plus_account,\r\n\t\t\t\tp.name,\r\n\t\t\t\tp.pwd,\r\n\t\t\t\tp.email,\r\n\t\t\t\tp.custom_links,\r\n\t\t\t\tp.new_report_count, p.new_mail_count,\r\n\t\t\t\tp.selected_village_id, p.villages_id, p.villages_data,\r\n\t\t\t\tp.friend_players,\r\n\t\t\t\tp.gold_num,\r\n\t\t\t\tp.notes,\r\n\t\t\t\tp.week_attack_points,\r\n\t\t\t\tp.week_defense_points,\r\n\t\t\t\tp.week_dev_points,\r\n\t\t\t\tp.week_thief_points,\r\n\t\t\t\tp.hero_troop_id, p.hero_level, p.hero_points, p.hero_name, p.hero_in_village_id,\r\n\t\t\t\tp.invites_alliance_ids,\r\n\t\t\t\tp.guide_quiz,\r\n\t\t\t\tp.new_gnews,\r\n\t\t\t\tp.create_nvil,\r\n\t\t\t\tDATE_FORMAT(p.registration_date, '%%Y/%%m/%%d %%H:%%i') registration_date,\r\n\t\t\t\tTIMEDIFF(DATE_ADD(p.registration_date, INTERVAL %s SECOND), NOW()) protection_remain,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(DATE_ADD(p.registration_date, INTERVAL %s SECOND), NOW())) protection_remain_sec,\r\n\t\t\t\tDATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(p.birth_date)), '%%Y')+0 age,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) last_login_sec\r\n\t\t\tFROM p_players p\r\n\t\t\tWHERE p.id=%s", array( $protectionPeriod, $protectionPeriod, $playerId ) );
        if ( $data == NULL )
        {
            return NULL;
        }
        if ( $sessionTimeoutInSeconds <= $data['last_login_sec'] )
        {
            $this->provider->executeQuery( "UPDATE p_players p SET p.last_login_date=NOW() WHERE p.id=%s", array( $playerId ) );
        }
        $data2 = $this->provider->fetchRow( "SELECT\r\n\t\t\t\tv.rel_x, v.rel_y,\r\n\t\t\t\tv.parent_id, v.tribe_id,\r\n\t\t\t\tv.field_maps_id,\r\n\t\t\t\tv.village_name,\r\n\t\t\t\tv.is_capital, v.is_special_village,\r\n\t\t\t\tv.people_count,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tv.time_consume_percent,\r\n\t\t\t\tv.resources, v.buildings, v.cp,\r\n\t\t\t\tv.troops_training, v.troops_num,\r\n\t\t\t\tv.troops_trapped_num, v.troops_intrap_num, v.troops_out_num, v.troops_out_intrap_num, \r\n\t\t\t\tv.allegiance_percent,\r\n\t\t\t\tv.child_villages_id, v.village_oases_id,\r\n\t\t\t\tv.offer_merchants_count,\r\n\t\t\t\tv.update_key,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds\r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array( $data['selected_village_id'] ) );
        if ( $data2 == NULL )
        {
            return NULL;
        }
        foreach ( $data2 as $k => $v )
        {
            $data[$k] = $v;
        }
        unset( $data2 );
        $row = $this->provider->fetchRow( "SELECT g.game_over, g.game_transient_stopped FROM g_settings g" );
        $data['gameStatus'] = intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1;
        return $data;
    }

    public function isGameOver( )
    {
        return intval( $this->provider->fetchScalar( "SELECT g.game_over FROM g_settings g" ) ) == 1;
    }

    public function resetNewVillageFlag( $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.create_nvil=0 WHERE p.id=%s", array( $playerId ) );
    }

}

?>
