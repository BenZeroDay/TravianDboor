<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."alliance.php" );
class GPage extends securegamepage
{

    public $allianceData = NULL;
    public $playerName = NULL;
    public $playerRoles = NULL;

    public function GPage( )
    {
        $this->customLogoutAction = TRUE;
        parent::securegamepage( );
        if ( $this->player == NULL )
        {
            exit( 0 );
        }
        $this->viewFile = "alliancerole.phtml";
        $this->layoutViewFile = "layout".DIRECTORY_SEPARATOR."popup.phtml";
        $this->checkForGlobalMessage = FALSE;
    }

    public function load( )
    {
        parent::load( );
        $allianceId = intval( $this->data['alliance_id'] );
        if ( $allianceId == 0 || !$this->hasAllianceSetRoles( ) || !isset( $_GET['uid'] ) )
        {
            exit( 0 );
        }
        else
        {
            $uid = intval( $_GET['uid'] );
            $m = new AllianceModel( );
            $this->allianceData = $m->getAllianceData( $allianceId );
            if ( !$this->isMemberOfAlliance( $uid ) )
            {
                exit( 0 );
            }
            else
            {
                if ( $this->isPost( ) )
                {
                    $roleName = isset( $_POST['a_titel'] ) ? $_POST['a_titel'] : "";
                    if ( trim( $roleName ) == "" )
                    {
                        $roleName = ".";
                    }
                    $roleNumber = 0;
                    if ( isset( $_POST['e1'] ) )
                    {
                        $roleNumber |= ALLIANCE_ROLE_SETROLES;
                    }
                    if ( isset( $_POST['e2'] ) )
                    {
                        $roleNumber |= ALLIANCE_ROLE_REMOVEPLAYER;
                    }
                    if ( isset( $_POST['e3'] ) )
                    {
                        $roleNumber |= ALLIANCE_ROLE_EDITNAMES;
                    }
                    if ( isset( $_POST['e4'] ) )
                    {
                        $roleNumber |= ALLIANCE_ROLE_EDITCONTRACTS;
                    }
                    if ( isset( $_POST['e5'] ) )
                    {
                        $roleNumber |= ALLIANCE_ROLE_SENDMESSAGE;
                    }
                    if ( isset( $_POST['e6'] ) )
                    {
                        $roleNumber |= ALLIANCE_ROLE_INVITEPLAYERS;
                    }
                    $m->setPlayerAllianceRole( $uid, $roleName, $roleNumber );
                }
                $row = $m->getPlayerAllianceRole( $uid );
                if ( $row == NULL )
                {
                    exit( 0 );
                }
                else
                {
                    $this->playerName = $row['name'];
                    $alliance_roles = trim( $row['alliance_roles'] );
                    if ( $alliance_roles == "" )
                    {
                        $this->playerRoles = array( "name" => "", "roles" => 0 );
                    }
                    else
                    {
                        list( $rolesNumber, $roleName ) = explode( " ", $alliance_roles, 2 );                        
                        $this->playerRoles = array(
                            "name" => $roleName == "." ? "" : $roleName,
                            "roles" => $rolesNumber
                        );
                    }
                    $m->dispose( );
                }
            }
        }
    }

    public function isMemberOfAlliance( $playerId )
    {
        $players_ids = trim( $this->allianceData['players_ids'] );
        if ( $players_ids == "" )
        {
            return FALSE;
        }
        $arr = explode( ",", $players_ids );
        foreach ( $arr as $pid )
        {
            if ( !( $pid == $playerId ) )
            {
                continue;
            }
            return TRUE;
        }
        return FALSE;
    }

    public function _hasAllianceRole( $role )
    {
        $alliance_roles = trim( $this->data['alliance_roles'] );
        if ( $alliance_roles == "" )
        {
            return FALSE;
        }
        list( $roleNumber, $roleName ) = explode( " ", $alliance_roles, 2 );        
        return $roleNumber & $role;
    }

    public function hasAllianceSetRoles( )
    {
        return $this->_hasAllianceRole( ALLIANCE_ROLE_SETROLES );
    }

    public function getAllianceRoleCheckValue( $role )
    {
        return $this->playerRoles['roles'] & $role ? "value=\"1\" checked=\"checked\"" : "value=\"0\"";
    }

}

$p = new GPage( );
$p->run( );
?>
