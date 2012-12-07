<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class GPage extends gamepage
{

    public $playerStatus = -1;
    public $uid = NULL;
    public $uname = NULL;

    public function GPage( )
    {
        parent::gamepage( );
        $this->viewFile = "activate.phtml";
        $this->contentCssClass = "activate";
    }

    public function load( )
    {
        parent::load( );
        $m = new ActivateModel( );
        if ( isset( $_GET['id'] ) )
        {
            $this->playerStatus = $m->doActivation( $_GET['id'] ) ? 1 : 2;
        }
        else if ( isset( $_GET['uid'] ) )
        {
            $this->uid = intval( $_GET['uid'] );
            $row = $m->getPlayerData( $this->uid );
            if ( $row == NULL )
            {
                $this->uid = 0 - 1;
            }
            else
            {
                $this->uname = $row['name'];
                if ( $this->isPost( ) && isset( $_POST['pw'] ) && md5( $_POST['pw'] ) == $row['pwd'] )
                {
                    $mj = new QueueJobModel( );
                    $mj->deletePlayer( $this->uid );
                    $this->playerStatus = 3;
                }
            }
        }
        $m->dispose( );
    }

}

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."activate.php" );
$p = new GPage( );
$p->run( );
?>
