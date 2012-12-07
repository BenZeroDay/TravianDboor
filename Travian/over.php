<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."profile.php" );
class GPage extends gamepage
{

    public $playerData = NULL;

    public function GPage( )
    {
        parent::gamepage( );
        $this->viewFile = "over.phtml";
        $this->contentCssClass = "messages";
    }

    public function load( )
    {
        parent::load( );
        if ( !$this->globalModel->isGameOver( ) )
        {
            exit( 0 );
        }
        else
        {
            $m = new ProfileModel( );
            $this->playerData = $m->getWinnerPlayer( );
            $m->dispose( );
        }
    }

}


$p = new GPage( );
$p->run( );
?>
