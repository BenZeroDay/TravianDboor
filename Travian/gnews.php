<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."news.php" );
class GPage extends securegamepage
{

    public $saved = NULL;
    public $siteNews = NULL;

    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "gnews.phtml";
        $this->contentCssClass = "messages";
    }

    public function load( )
    {
        parent::load( );
        if ( $this->data['player_type'] != PLAYERTYPE_ADMIN )
        {
            exit( 0 );
        }
        else
        {
            $m = new NewsModel( );
            $this->saved = FALSE;
            if ( $this->isPost( ) && isset( $_POST['news'] ) )
            {
                $this->siteNews = $_POST['news'];
                $this->saved = TRUE;
                $m->setGlobalPlayerNews( $this->siteNews );
            }
            else
            {
                $this->siteNews = $m->getGlobalSiteNews( );
            }
            $m->dispose( );
        }
    }

}


$p = new GPage( );
$p->run( );
?>
