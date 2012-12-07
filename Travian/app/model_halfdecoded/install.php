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

require_once( MODEL_PATH."register.php" );
require_once( MODEL_PATH."queue.php" );
class SetupModel extends ModelBase
{

    public function processSetup( $map_size, $adminEmail )
    {
        $this->_createTables( );
        $this->_createMap( $map_size );
        if ( $this->_createAdminPlayer( $map_size, $adminEmail ) )
        {
            $raiseTime = 10 / $GLOBALS['GameMetadata']['game_speed'];
            $raiseTime *= 2592000;
            $raiseTime = intval( $raiseTime );
            if ( $raiseTime < 2592000 )
            {
                $raiseTime = 2592000;
            }
            $queueModel = new QueueModel( );
            $queueModel->addTask( new QueueTask( QS_TATAR_RAISE, 0, $raiseTime ) );
            GameLicense::set( WebHelper::getdomain( ) );
        }
    }

    public function _createTables( )
    {
        $this->provider->executeBatchQuery( "\r\nDROP TABLE IF EXISTS `g_settings`;\r\nDROP TABLE IF EXISTS `g_summary`;\r\nDROP TABLE IF EXISTS `p_alliances`;\r\nDROP TABLE IF EXISTS `p_merchants`;\r\nDROP TABLE IF EXISTS `p_msgs`;\r\nDROP TABLE IF EXISTS `p_players`;\r\nDROP TABLE IF EXISTS `p_queue`;\r\nDROP TABLE IF EXISTS `p_rpts`;\r\nDROP TABLE IF EXISTS `p_villages`;\r\nDROP TABLE IF EXISTS `g_chat`;\r\nDROP TABLE IF EXISTS `g_comment`;\r\nDROP TABLE IF EXISTS `g_profile`;\r\nDROP TABLE IF EXISTS `p_friends`;\r\nDROP TABLE IF EXISTS `privatechat`;\r\n\r\nCREATE TABLE `privatechat` (\r\n  `id` int(10) unsigned NOT NULL auto_increment,\r\n  `from` varchar(255) character set utf8 NOT NULL default '',\r\n  `from_img` varchar(200) NOT NULL default 'nophoto.gif',\r\n  `from_id` int(11) NOT NULL default '0',\r\n  `to` varchar(255) character set utf8 NOT NULL default '',\r\n  `to_img` varchar(200) NOT NULL default 'nophoto.gif',\r\n  `to_id` int(11) NOT NULL default '0',\r\n  `message` text character set utf8 NOT NULL,\r\n  `sent` datetime NOT NULL default '0000-00-00 00:00:00',\r\n  `recd` int(10) unsigned NOT NULL default '0',\r\n  PRIMARY KEY  (`id`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;\r\n\r\nCREATE TABLE `p_profile` (\r\n  `ID` int(11) NOT NULL AUTO_INCREMENT,\r\n  `userid` int(11) DEFAULT '0',\r\n  `message` text,\r\n  `date` varchar(250) DEFAULT NULL,\r\n  `image` VARCHAR(250) NOT NULL DEFAULT '',\r\n  `url` VARCHAR(250) NOT NULL DEFAULT '',\r\n  `youtube` VARCHAR(250) NOT NULL DEFAULT '',\r\n  PRIMARY KEY (`ID`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_comment` (\r\n  `ID` int(11) NOT NULL AUTO_INCREMENT,\r\n  `username` varchar(100) DEFAULT NULL,\r\n  `userid` int(11) DEFAULT '0',\r\n  `to_userid` int(11) DEFAULT '0',\r\n  `topicid` int(11) DEFAULT '0',\r\n  `date` varchar(30) DEFAULT NULL,\r\n  `comment` varchar(250) DEFAULT NULL,\r\n  PRIMARY KEY (`ID`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_friends` (\r\n  `ID` int(11) NOT NULL AUTO_INCREMENT,\r\n  `playerid1` int(11) DEFAULT '0',\r\n  `playername1` varchar(70) DEFAULT NULL,\r\n  `playerid2` int(11) DEFAULT '0',\r\n  `playername2` varchar(70) DEFAULT NULL,\r\n  `date` varchar(30) DEFAULT NULL,\r\n  `accept` int(11) DEFAULT '0',\r\n  PRIMARY KEY (`ID`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `g_words` (\r\n  `ID` int(11) NOT NULL AUTO_INCREMENT,\r\n  `word` varchar(200) DEFAULT NULL,\r\n  PRIMARY KEY (`ID`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `g_banner` (\r\n  `ID` int(11) NOT NULL AUTO_INCREMENT,\r\n  `name` varchar(200) DEFAULT NULL,\r\n  `url` varchar(200) DEFAULT NULL,\r\n  `cat` int(11) DEFAULT '1',\r\n  `image` varchar(200) DEFAULT NULL,\r\n  `type` enum('image','flash') DEFAULT 'image',\r\n  `date` varchar(30) DEFAULT NULL,\r\n  `visit` int(11) DEFAULT '0',\r\n  `view` int(11) DEFAULT '0',\r\n  PRIMARY KEY (`ID`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `g_chat` (\r\n  `ID` int(11) NOT NULL AUTO_INCREMENT,\r\n  `username` varchar(100) DEFAULT NULL,\r\n  `date` varchar(30) DEFAULT NULL,\r\n  `userid` int(11) DEFAULT NULL,\r\n  `text` varchar(250) DEFAULT NULL,\r\n  PRIMARY KEY (`ID`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `g_settings` (\r\n  `start_date` datetime DEFAULT NULL,\r\n  `license_key` varchar(50) DEFAULT NULL,\r\n  `game_over` tinyint(1) DEFAULT '0',\r\n  `game_transient_stopped` tinyint(1) DEFAULT '0',\r\n  `cur_week` smallint(6) DEFAULT '0',\r\n  `win_pid` bigint(20) DEFAULT '0',\r\n  `qlocked_date` datetime DEFAULT NULL,\r\n  `qlocked` tinyint(1) DEFAULT '0'\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `g_summary` (\r\n  `players_count` bigint(20) DEFAULT '0',\r\n  `active_players_count` bigint(20) DEFAULT '0',\r\n  `Dboor_players_count` bigint(20) DEFAULT '0',\r\n  `Arab_players_count` bigint(20) DEFAULT '0',\r\n  `Roman_players_count` bigint(20) DEFAULT '0',\r\n  `Teutonic_players_count` bigint(20) DEFAULT '0',\r\n  `Gallic_players_count` bigint(20) DEFAULT '0',\r\n  `news_text` text,\r\n  `gnews_text` text\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_alliances` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `name` varchar(255) NOT NULL DEFAULT '',\r\n  `name2` varchar(255) DEFAULT NULL,\r\n  `creator_player_id` bigint(20) DEFAULT NULL,\r\n  `rating` int(11) DEFAULT NULL,\r\n  `creation_date` datetime DEFAULT NULL,\r\n  `contracts_alliance_id` text,\r\n  `player_count` tinyint(4) DEFAULT NULL,\r\n  `max_player_count` tinyint(4) DEFAULT '1',\r\n  `players_ids` text,\r\n  `invites_player_ids` text,\r\n  `description1` text,\r\n  `description2` text,\r\n  `medals` varchar(300) DEFAULT NULL,\r\n  `attack_points` bigint(20) DEFAULT '0',\r\n  `defense_points` bigint(20) DEFAULT '0',\r\n  `week_attack_points` bigint(20) DEFAULT '0',\r\n  `week_defense_points` bigint(20) DEFAULT '0',\r\n  `week_dev_points` bigint(20) DEFAULT '0',\r\n  `week_thief_points` bigint(20) DEFAULT '0',\r\n  PRIMARY KEY (`id`),\r\n  KEY `NewIndex1` (`name`),\r\n  KEY `NewIndex2` (`rating`),\r\n  KEY `NewIndex3` (`attack_points`),\r\n  KEY `NewIndex4` (`defense_points`),\r\n  KEY `NewIndex5` (`week_attack_points`),\r\n  KEY `NewIndex6` (`week_defense_points`),\r\n  KEY `NewIndex7` (`week_dev_points`),\r\n  KEY `NewIndex8` (`week_thief_points`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_players` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `tribe_id` tinyint(4) DEFAULT NULL,\r\n  `alliance_id` bigint(20) DEFAULT NULL,\r\n  `alliance_name` varchar(255) DEFAULT NULL,\r\n  `alliance_roles` text,\r\n  `invites_alliance_ids` text,\r\n  `name` varchar(255) DEFAULT NULL,\r\n  `pwd` varchar(255) DEFAULT NULL,\r\n  `email` varchar(50) DEFAULT NULL,\r\n  `is_active` tinyint(1) DEFAULT '0',\r\n  `is_blocked` tinyint(1) DEFAULT '0',\r\n  `player_type` tinyint(4) DEFAULT '0',\r\n  `active_plus_account` tinyint(1) DEFAULT '0',\r\n  `activation_code` varchar(255) DEFAULT NULL,\r\n  `last_login_date` datetime DEFAULT NULL,\r\n  `last_ip` varchar(255) DEFAULT NULL,\r\n  `birth_date` date DEFAULT NULL,\r\n  `gender` tinyint(1) NOT NULL DEFAULT '0',\r\n  `description1` text,\r\n  `description2` text,\r\n  `house_name` varchar(255) DEFAULT NULL,\r\n  `registration_date` datetime DEFAULT NULL,\r\n  `gold_num` int(11) DEFAULT '0',\r\n  `agent_for_players` varchar(255) DEFAULT NULL,\r\n  `my_agent_players` varchar(255) DEFAULT NULL,\r\n  `custom_links` text,\r\n  `medals` varchar(300) DEFAULT NULL,\r\n  `total_people_count` bigint(20) DEFAULT '2',\r\n  `selected_village_id` bigint(20) DEFAULT NULL,\r\n  `villages_count` tinyint(4) DEFAULT '1',\r\n  `villages_id` text,\r\n  `villages_data` text,\r\n  `friend_players` text,\r\n  `notes` text,\r\n  `hero_troop_id` tinyint(4) DEFAULT NULL,\r\n  `hero_level` tinyint(4) DEFAULT '0',\r\n  `hero_points` bigint(20) DEFAULT '0',\r\n  `hero_name` varchar(300) DEFAULT NULL,\r\n  `hero_in_village_id` bigint(20) DEFAULT NULL,\r\n  `attack_points` bigint(20) DEFAULT '0',\r\n  `defense_points` bigint(20) DEFAULT '0',\r\n  `week_attack_points` bigint(20) DEFAULT '0',\r\n  `week_defense_points` bigint(20) DEFAULT '0',\r\n  `week_dev_points` bigint(20) DEFAULT '0',\r\n  `week_thief_points` bigint(20) DEFAULT '0',\r\n  `new_report_count` smallint(6) DEFAULT '0',\r\n  `new_mail_count` smallint(6) DEFAULT '0',\r\n  `guide_quiz` varchar(50) DEFAULT NULL,\r\n  `new_gnews` tinyint(1) DEFAULT '0',\r\n  `create_nvil` tinyint(4) DEFAULT '0',\r\n  `snid` bigint(11) NOT NULL DEFAULT '0',\r\n  `avatar`  varchar(255) NULL DEFAULT 'http://www.wartatar.com/assets/default/img/q/l6.jpg',\r\n  PRIMARY KEY (`id`),\r\n  UNIQUE KEY `NewIndex1` (`name`),\r\n  UNIQUE KEY `NewIndex2` (`activation_code`),\r\n  UNIQUE KEY `NewIndex4` (`email`),\r\n  KEY `NewIndex3` (`attack_points`),\r\n  KEY `NewIndex6` (`defense_points`),\r\n  KEY `NewIndex5` (`last_login_date`),\r\n  KEY `NewIndex7` (`week_attack_points`),\r\n  KEY `NewIndex8` (`week_defense_points`),\r\n  KEY `NewIndex9` (`week_dev_points`),\r\n  KEY `NewIndex10` (`week_thief_points`),\r\n  KEY `NewIndex11` (`snid`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_villages` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `rel_x` smallint(6) DEFAULT NULL,\r\n  `rel_y` smallint(6) DEFAULT NULL,\r\n  `field_maps_id` tinyint(4) DEFAULT NULL,\r\n  `image_num` tinyint(4) DEFAULT NULL,\r\n  `rand_num` int(11) DEFAULT NULL,\r\n  `parent_id` bigint(20) DEFAULT NULL,\r\n  `tribe_id` tinyint(4) DEFAULT NULL,\r\n  `player_id` bigint(20) DEFAULT NULL,\r\n  `alliance_id` bigint(20) DEFAULT NULL,\r\n  `player_name` varchar(300) DEFAULT NULL,\r\n  `village_name` varchar(255) DEFAULT NULL,\r\n  `alliance_name` varchar(300) DEFAULT NULL,\r\n  `is_capital` tinyint(1) DEFAULT '0',\r\n  `is_special_village` tinyint(1) DEFAULT '0',\r\n  `is_oasis` tinyint(1) DEFAULT NULL,\r\n  `people_count` int(11) DEFAULT '2',\r\n  `crop_consumption` int(11) DEFAULT '2',\r\n  `time_consume_percent` float DEFAULT '100',\r\n  `offer_merchants_count` tinyint(4) DEFAULT '0',\r\n  `resources` varchar(300) DEFAULT NULL,\r\n  `cp` varchar(300) DEFAULT NULL,\r\n  `buildings` varchar(300) DEFAULT NULL,\r\n  `troops_training` varchar(200) DEFAULT NULL,\r\n  `troops_num` text,\r\n  `troops_out_num` text,\r\n  `troops_intrap_num` text,\r\n  `troops_out_intrap_num` text,\r\n  `troops_trapped_num` int(11) DEFAULT '0',\r\n  `allegiance_percent` int(11) DEFAULT '100',\r\n  `child_villages_id` text,\r\n  `village_oases_id` text,\r\n  `creation_date` datetime DEFAULT NULL,\r\n  `update_key` varchar(5) DEFAULT NULL,\r\n  `last_update_date` datetime DEFAULT NULL,\r\n  PRIMARY KEY (`id`),\r\n  KEY `NewIndex2` (`player_id`),\r\n  KEY `rand_num` (`rand_num`),\r\n  KEY `field_maps_id` (`field_maps_id`),\r\n  KEY `NewIndex3` (`is_special_village`),\r\n  KEY `NewIndex4` (`is_oasis`),\r\n  KEY `NewIndex5` (`people_count`),\r\n  KEY `NewIndex1` (`village_name`),\r\n  KEY `NewIndex6` (`player_id`,`is_oasis`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_queue` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `player_id` bigint(20) NOT NULL DEFAULT '0',\r\n  `village_id` bigint(20) DEFAULT NULL,\r\n  `to_player_id` bigint(20) DEFAULT NULL,\r\n  `to_village_id` bigint(20) DEFAULT NULL,\r\n  `proc_type` tinyint(4) DEFAULT NULL,\r\n  `building_id` bigint(20) DEFAULT NULL,\r\n  `proc_params` text,\r\n  `threads` int(11) DEFAULT '1',\r\n  `end_date` datetime DEFAULT NULL,\r\n  `execution_time` bigint(20) DEFAULT NULL,\r\n  PRIMARY KEY (`id`),\r\n  KEY `NewIndex1` (`player_id`),\r\n  KEY `NewIndex2` (`village_id`),\r\n  KEY `NewIndex3` (`to_player_id`),\r\n  KEY `NewIndex4` (`to_village_id`),\r\n  KEY `NewIndex5` (`end_date`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_msgs` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `from_player_id` bigint(20) DEFAULT NULL,\r\n  `to_player_id` bigint(20) DEFAULT NULL,\r\n  `from_player_name` varchar(300) DEFAULT NULL,\r\n  `to_player_name` varchar(300) DEFAULT NULL,\r\n  `msg_title` varchar(255) DEFAULT NULL,\r\n  `msg_body` text,\r\n  `creation_date` datetime DEFAULT NULL,\r\n  `is_readed` tinyint(1) DEFAULT '0',\r\n  `delete_status` tinyint(2) DEFAULT '0',\r\n  PRIMARY KEY (`id`),\r\n  KEY `NewIndex1` (`from_player_id`),\r\n  KEY `NewIndex2` (`to_player_id`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_merchants` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `player_id` bigint(20) DEFAULT NULL,\r\n  `player_name` varchar(255) DEFAULT NULL,\r\n  `village_id` bigint(20) DEFAULT NULL,\r\n  `village_x` smallint(6) DEFAULT NULL,\r\n  `village_y` smallint(6) DEFAULT NULL,\r\n  `offer` varchar(300) DEFAULT NULL,\r\n  `merchants_num` tinyint(4) DEFAULT NULL,\r\n  `merchants_speed` tinyint(4) DEFAULT NULL,\r\n  `alliance_only` tinyint(1) DEFAULT NULL,\r\n  `max_time` tinyint(4) DEFAULT NULL,\r\n  PRIMARY KEY (`id`),\r\n  KEY `NewIndex1` (`player_id`),\r\n  KEY `village_x` (`village_x`),\r\n  KEY `village_y` (`village_y`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\nCREATE TABLE `p_rpts` (\r\n  `id` bigint(20) NOT NULL AUTO_INCREMENT,\r\n  `from_player_id` bigint(20) DEFAULT NULL,\r\n  `from_player_name` varchar(300) DEFAULT NULL,\r\n  `from_village_id` bigint(20) DEFAULT NULL,\r\n  `from_village_name` varchar(300) DEFAULT NULL,\r\n  `to_player_id` bigint(20) DEFAULT NULL,\r\n  `to_player_name` varchar(300) DEFAULT NULL,\r\n  `to_village_id` bigint(20) DEFAULT NULL,\r\n  `to_village_name` varchar(300) DEFAULT NULL,\r\n  `rpt_body` text,\r\n  `creation_date` datetime DEFAULT NULL,\r\n  `read_status` tinyint(2) DEFAULT '0',\r\n  `delete_status` tinyint(2) DEFAULT '0',\r\n  `rpt_cat` tinyint(4) DEFAULT NULL,\r\n  `rpt_result` tinyint(4) DEFAULT '0',\r\n  PRIMARY KEY (`id`),\r\n  KEY `NewIndex1` (`from_player_id`),\r\n  KEY `NewIndex2` (`to_player_id`),\r\n  KEY `NewIndex3` (`rpt_cat`)\r\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\r\n\r\n\r\nINSERT INTO `g_settings`(`start_date`,`license_key`) VALUES (NOW(),NULL);\r\nINSERT INTO `g_summary`(`players_count`,`active_players_count`,`Arab_players_count`,`Roman_players_count`,`Teutonic_players_count`,`Gallic_players_count`,`news_text`) VALUES ( '0','0','0','0','0','0',NULL);" );
    }

    public function _createMap( $map_size )
    {
        $maphalf_size = floor( $map_size / 2 );
        $oasis_troop_ids = array( );
        foreach ( $GLOBALS['GameMetadata']['troops'] as $k => $v )
        {
            if ( $v['for_tribe_id'] == 4 )
            {
                $oasis_troop_ids[] = $k;
            }
        }
        $i = 0;
        while ( $i < $map_size )
        {
            $queryBatch = array( );
            $j = 0;
            while ( $j < $map_size )
            {
                $rel_x = $maphalf_size < $i ? $i - $map_size : $i;
                $rel_y = $maphalf_size < $j ? $j - $map_size : $j;
                $troops_num = "";
                $field_maps_id = 0;
                $rand_num = "NULL";
                $creation_date = "NULL";
                if ( $rel_x == 0 && $rel_y == 0 )
                {
                    $r = 1;
                }
                else
                {
                    $r_arr = array(
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        0,
                        1,
                        mt_rand( 0, 1 ),
                        mt_rand( 0, 1 ),
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        0,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        1,
                        mt_rand( 0, 1 )
                    );
                    $r = $r_arr[mt_rand( 0, 48 )];
                }
                if ( $r == 1 )
                {
                    $image_num = mt_rand( 0, 9 );
                    $is_oasis = 0;
                    $tribe_id = 0;
                    if ( $rel_x == 0 && $rel_y == 0 )
                    {
                        $field_maps_id = 3;
                    }
                    else
                    {
                        $fr_arr = array(
                            3,
                            mt_rand( 1, 12 ),
                            3,
                            mt_rand( 1, 4 ),
                            mt_rand( 1, 5 ),
                            3,
                            mt_rand( 1, 12 ),
                            3,
                            mt_rand( 7, 11 ),
                            mt_rand( 7, 12 ),
                            3,
                            3,
                            mt_rand( 1, 12 )
                        );
                        $field_maps_id = $fr_arr[mt_rand( 0, 12 )];
                    }
                    if ( $field_maps_id == 3 )
                    {
                        $pr_arr = array(
                            0,
                            1,
                            0,
                            0,
                            mt_rand( 0, 1 )
                        );
                        $pr = $pr_arr[mt_rand( 0, 4 )];
                        $rand_num = $pr == 1 ? abs( $rel_x ) + abs( $rel_y ) : 310;
                    }
                }
                else
                {
                    $image_num = mt_rand( 1, 12 );
                    $is_oasis = 1;
                    $tribe_id = 4;
                    $creation_date = "NOW()";
                    $troops_num = $oasis_troop_ids[mt_rand( 0, 2 )]." ".mt_rand( 1, 5 );
                    $troops_num .= ",".$oasis_troop_ids[mt_rand( 3, 5 )]." ".mt_rand( 2, 6 );
                    $troops_num .= ",".$oasis_troop_ids[mt_rand( 6, 8 )]." ".mt_rand( 3, 7 );
                    if ( mt_rand( 0, 1 ) == 1 )
                    {
                        $troops_num .= ",".$oasis_troop_ids[9]." ".mt_rand( 2, 8 );
                    }
                    $troops_num = "-1:".$troops_num;
                }
                $queryBatch[] = "(".$rel_x.",".$rel_y.",".$image_num.",".$rand_num.",".$field_maps_id.",".$tribe_id.",".$is_oasis.",'".$troops_num."',".$creation_date.")";
                ++$j;
            }
            $this->provider->executeQuery( "INSERT INTO p_villages (rel_x,rel_y,image_num,rand_num,field_maps_id,tribe_id,is_oasis,troops_num,creation_date) VALUES".implode( ",", $queryBatch ) );
            unset( $queryBatch );
            $queryBatch = NULL;
            ++$i;
        }
    }

    public function _createAdminPlayer( $map_size, $adminEmail )
    {
        $m = new RegisterModel( );
        $adminName = $GLOBALS['AppConfig']['system']['adminName'];
        $result = $m->createNewPlayer( $adminName, $adminEmail, $GLOBALS['AppConfig']['system']['adminPassword'], 6, 0, $adminName, $map_size, PLAYERTYPE_ADMIN );
        if ( $result['hasErrors'] )
        {
            return FALSE;
        }
        $m->dispose( );
        return TRUE;
    }

}

?>
