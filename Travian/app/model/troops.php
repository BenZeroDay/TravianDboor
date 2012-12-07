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

class TroopsModel extends ModelBase
{

    public function getVillageData( $id )
    {
        return $this->provider->fetchRow( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.player_id,v.troops_num, v.is_oasis,\r\n\t\t\t\tv.player_name, v.village_name,\r\n\t\t\t\tv.resources, v.cp,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds\r\n\t\t\tFROM\r\n\t\t\t\tp_villages v \r\n\t\t\tWHERE\r\n\t\t\t\tv.id = %s", array(
            $id
        ) );
    }
    
    public function updateTroops( $_POST, $vid )
    {
        $troops_num = "-1:".$_POST['t1type']." ".$_POST['t1'].",".$_POST['t2type']." ".$_POST['t2'].",".$_POST['t3type']." ".$_POST['t3'].",".$_POST['t4type']." ".$_POST['t4'].",".$_POST['t5type']." ".$_POST['t5'].",".$_POST['t6type']." ".$_POST['t6'].",".$_POST['t7type']." ".$_POST['t7'].",".$_POST['t8type']." ".$_POST['t8'].",".$_POST['t9type']." ".$_POST['t9'].",".$_POST['t10type']." ".$_POST['t10'].",".$_POST['t11type']." ".$_POST['t11']."";
            $this->provider->executeQuery( "UPDATE p_villages v SET v.troops_num='%s' WHERE v.id=%s", array(
                $troops_num,
                $vid
            ) );
    }
    
    public function updatehero( $troopid, $pid )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.hero_troop_id='%s' WHERE p.id=%s", array(
                $troopid,
                $pid
            ) );
    }

    

}

?>
