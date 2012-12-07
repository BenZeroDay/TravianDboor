<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."troops.php" );
class GPage extends securegamepage
{

    var $giveid;

    public function GPage( )
    {
        $this->customLogoutAction = TRUE;
        parent::securegamepage( );
        if ( $this->player == NULL )
        {
            exit( 0 );
        }
        $this->viewFile = "plussr.phtml";
        $this->layoutViewFile = "layout".DIRECTORY_SEPARATOR."popup.phtml";
    }

    public function load( )
    {
        parent::load( );
        $this->msgText = "";
      	$this->giveid = $this->player->playerId;
    }

}


$p = new GPage( );
$p->run( );
?>
