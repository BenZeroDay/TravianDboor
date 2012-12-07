<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."payment.php" );
require_once( LIB_PATH."paypal.class.php" );
class GPage extends DefaultPage
{

    public function GPage( )
    {
       

    }
    public function load( )
    {
        $AppConfig = $GLOBALS['AppConfig'];
        $p = new paypal_class( );
        $m = new PaymentModel( );
        if ( !isset( $_GET['action'] ) || empty( $_GET['action'] ) )
        {
            $GLOBALS['_GET']['action'] = "process";
        }
        switch ( $_GET['action'] )
        {
        case "process" :
            return;
        case "success" :
            if ( $this->isPost( ) )
            {
                echo "<html><head><title>Success</title></head><body><h3>Thank you for your order.</h3>";
                $m->dispose( );
                echo "</body></html>";
            }
            break;
        case "cancel" :
            echo "<html><head><title>Canceled</title></head><body><h3>The order was canceled.</h3>";
            echo "</body></html>";
            break;
        case "ipn" :
            if ( $p->validate_ipn( ) )
            {
                break;
            }
            $subject = "Instant Payment Notification - Recieved Payment";
            $to = $AppConfig['system']['email'];
            $body = "An instant payment notification was successfully recieved\n";
            $body .= "from ".$p->ipn_data['payer_email']." on ".date( "m/d/Y" );
            $body .= " at ".date( "g:i A" )."\n\nDetails:\n";
            foreach ( $p->ipn_data as $key => $value )
            {
                $body .= "\n{$key}: {$value}";
            }
            @mail( $to, $subject, $body );
            $usedPackage = NULL;
            foreach ( $AppConfig['plus']['packages'] as $package )
            {
                if ( $package['cost'] == $p->ipn_data['payment_gross'] )
                {
                    $usedPackage = $package;
                }
            }
            $Player = base64_decode( $p->ipn_data['custom'] );
            $m = new PaymentModel( );
            $m->incrementPlayerGold( $Player, $usedPackage );
            $m->dispose( );
        }
    }

}


$p = new GPage( );
$p->run( );
?>
