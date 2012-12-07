<?php
/*********************/
/*                   */
/*  Dezend for PHP5  */
/*         NWS       */
/*      Nulled.WS    */
/*                   */
/*********************/

class FriendsModel extends ModelBase
{

    public function SendInvitation( $post )
    {
        $PlayerCounter = intval( $this->provider->fetchScalar( "SELECT `id` FROM `p_players` WHERE `name` LIKE '%s';", array( $post['playerName'] ) ) );
        $count = $this->provider->fetchScalar( "SELECT COUNT(*) FROM `p_friends` WHERE \r\n\t\t(`playerid1` = '%s' AND `playerid2` = '%s') OR\r\n\t\t(`playerid2` = '%s' AND `playerid1` = '%s') ;", array( $post['playerId1'], $PlayerCounter, $post['playerId1'], $PlayerCounter ) );
        if ( $count == 0 && $PlayerCounter != 0 )
        {
            $this->provider->executeQuery( "INSERT INTO `p_friends` SET \r\n\t\t\t\t\t`playerid1` = '%s',\r\n\t\t\t\t\t`playername1` = '%s',\r\n\t\t\t\t\t`playerid2` = '%s',\r\n\t\t\t\t\t`playername2` = '%s',\r\n\t\t\t\t\t`accept` = '0',\r\n\t\t\t\t\t`date` = '%s'\r\n\t\t\t\t\t ;", array( $post['playerId1'], $post['myname'], $PlayerCounter, $post['playerName'], time( ) ) );
        }
    }

    public function ConfirmInvitation( $FriendID, $playerId )
    {
        $this->provider->executeQuery( "UPDATE `p_friends` SET \r\n\t\t\t`accept` = '1'\r\n\t\t\tWHERE `ID` = '%s' AND (`playerid1` = '%s' OR `playerid2` = '%s');", array( $FriendID, $playerId, $playerId ) );
    }

    public function DeleteFriend( $FriendID, $playerId )
    {
        $this->provider->executeQuery( "DELETE FROM `p_friends` WHERE `ID` = '%s' AND (`playerid1` = '%s' OR `playerid2` = '%s') ;", array( $FriendID, $playerId, $playerId ) );
    }

    public function GetFriends( $playerId, $pageIndex, $pageSize )
    {
        return $this->provider->fetchResultSet( "SELECT * FROM `p_friends` WHERE `playerid1` = '%s' OR `playerid2` = '%s' LIMIT %s,%s;", array( $playerId, $playerId, $pageIndex * $pageSize, $pageSize ) );
    }

    public function getFriendsCount( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT COUNT(*) FROM `p_friends` WHERE `playerid1` = '%s' OR `playerid2` = '%s';", array( $playerId, $playerId ) );
    }

}

?>
