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

class NewVillageBattleModel extends BattleModel
{

    public function handleCreateNewVillage( $taskRow, $toVillageRow, $fromVillageRow, $cropConsumption )
    {
        $GameMetadata = $GLOBALS['GameMetadata'];
        $SetupMetadata = $GLOBALS['SetupMetadata'];
        if ( intval( $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.id=%s", array(
            intval( $fromVillageRow['player_id'] )
        ) ) ) == 0 )
        {
            return FALSE;
        }
        $villageName = new_village_name;
        $update_key = substr( md5( $fromVillageRow['player_id'].$fromVillageRow['tribe_id'].$toVillageRow['id'].$fromVillageRow['player_name'].$villageName ), 2, 5 );
        $field_map_id = $toVillageRow['field_maps_id'];
        $buildings = "";
        foreach ( $SetupMetadata['field_maps'][$field_map_id] as $v )
        {
            if ( $buildings != "" )
            {
                $buildings .= ",";
            }
            $buildings .= sprintf( "%s 0 0", $v );
        }
        $k = 19;
        while ( $k <= 40 )
        {
            $buildings .= $k == 26 ? ",15 1 0" : ",0 0 0";
            ++$k;
        }
        $resources = "";
        $farr = explode( "-", $SetupMetadata['field_maps_summary'][$field_map_id] );
        $i = 1;
        $_c = sizeof( $farr );
        while ( $i <= $_c )
        {
            if ( $resources != "" )
            {
                $resources .= ",";
            }
            $resources .= sprintf( "%s 1300 1500 1500 %s 0", $i, $farr[$i - 1] * 2 * $GameMetadata['game_speed'] );
            ++$i;
        }
        $troops_training = "";
        $troops_num = "";
        foreach ( $GameMetadata['troops'] as $k => $v )
        {
            if ( $v['for_tribe_id'] == 0 - 1 || $v['for_tribe_id'] == $fromVillageRow['tribe_id'] )
            {
                if ( $troops_training != "" )
                {
                    $troops_training .= ",";
                }
                $researching_done = $v['research_time_consume'] == 0 ? 1 : 0;
                $troops_training .= $k." ".$researching_done." 0 0";
                if ( $troops_num != "" )
                {
                    $troops_num .= ",";
                }
                $troops_num .= $k." 0";
            }
        }
        $troops_num = "-1:".$troops_num;
        $this->provider->executeQuery( "UPDATE p_villages v\r\n\t\t\tSET\r\n\t\t\t\tv.parent_id=%s,\r\n\t\t\t\tv.tribe_id=%s,\r\n\t\t\t\tv.player_id=%s,\r\n\t\t\t\tv.alliance_id=%s,\r\n\t\t\t\tv.player_name='%s',\r\n\t\t\t\tv.village_name='%s',\r\n\t\t\t\tv.alliance_name='%s',\r\n\t\t\t\tv.is_capital=0,\r\n\t\t\t\tv.buildings='%s',\r\n\t\t\t\tv.resources='%s',\r\n\t\t\t\tv.cp='0 2',\r\n\t\t\t\tv.troops_training='%s',\r\n\t\t\t\tv.troops_num='%s',\r\n\t\t\t\tv.update_key='%s',\r\n\t\t\t\tv.creation_date=NOW(),\r\n\t\t\t\tv.last_update_date=NOW()\r\n\t\t\tWHERE v.id=%s", array(
            intval( $fromVillageRow['id'] ),
            intval( $fromVillageRow['tribe_id'] ),
            intval( $fromVillageRow['player_id'] ),
            0 < intval( $fromVillageRow['alliance_id'] ) ? intval( $fromVillageRow['alliance_id'] ) : "NULL",
            $fromVillageRow['player_name'],
            $villageName,
            $fromVillageRow['alliance_name'],
            $buildings,
            $resources,
            $troops_training,
            $troops_num,
            $update_key,
            intval( $toVillageRow['id'] )
        ) );
        $child_villages_id = trim( $fromVillageRow['child_villages_id'] );
        if ( $child_villages_id != "" )
        {
            $child_villages_id .= ",";
        }
        $child_villages_id .= $toVillageRow['id'];
        $this->provider->executeQuery( "UPDATE p_villages v\r\n\t\t\tSET\r\n\t\t\t\tv.crop_consumption=v.crop_consumption-%s,\r\n\t\t\t\tv.child_villages_id='%s'\r\n\t\t\tWHERE v.id=%s", array(
            $cropConsumption,
            $child_villages_id,
            intval( $fromVillageRow['id'] )
        ) );
        $prow = $this->provider->fetchRow( "SELECT p.villages_id, p.villages_data FROM p_players p WHERE p.id=%s", array(
            intval( $fromVillageRow['player_id'] )
        ) );
        $villages_id = trim( $prow['villages_id'] );
        if ( $villages_id != "" )
        {
            $villages_id .= ",";
        }
        $villages_id .= $toVillageRow['id'];
        $villages_data = trim( $prow['villages_data'] );
        if ( $villages_data != "" )
        {
            $villages_data .= "\n";
        }
        $villages_data .= $toVillageRow['id']." ".$toVillageRow['rel_x']." ".$toVillageRow['rel_y']." ".$villageName;
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.total_people_count=p.total_people_count+2,\r\n\t\t\t\tp.villages_count=p.villages_count+1,\r\n\t\t\t\tp.selected_village_id=%s,\r\n\t\t\t\tp.villages_id='%s',\r\n\t\t\t\tp.villages_data='%s',\r\n\t\t\t\tp.create_nvil=1\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
            intval( $toVillageRow['id'] ),
            $villages_id,
            $villages_data,
            intval( $fromVillageRow['player_id'] )
        ) );
        return FALSE;
    }

}

?>
