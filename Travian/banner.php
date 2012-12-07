<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

class GPage extends securegamepage
{

    public function GPage( )
    {
        parent::securegamepage( );
    }

    public function load( )
    {
        parent::load( );
        if ( isset( $_GET['url'] ) && !empty( $_GET['url'] ) )
        {
            $advID = base64_decode( mysql_real_escape_string( trim( $_GET['url'] ) ) );
            if ( $advID != "" )
            {
                $m = new AdvertisingModel( );
                $url = $m->GoToBanner( $advID );
                $m->dispose( );
                $this->redirect( $url );
            }
        }
    }

}

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."advertising.php" );
$p = new GPage( );
$p->run( );
?>
