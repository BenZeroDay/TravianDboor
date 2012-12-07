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
class GPage
{

    public function load( )
    {
        $AppConfig = $GLOBALS['AppConfig'];
        if ( $this->isPost( ) )
        {
            $usedPackage = NULL;
            foreach ( $AppConfig['plus']['packages'] as $package )
            {
                if ( $package['cost'] == $_POST['amount'] )
                {
                    $usedPackage = $package;
                }
            }
            if ( isset( $_POST['merchant_id'] ) )
            {
                $merchant_id = $AppConfig['plus']['payments']['cashu']['merchant_id'];
            }
            else
            {
                $merchant_id = $AppConfig['plus']['payments']['onecard']['merchant_id'];
                $key = $merchant_id.$_POST['OneCard_TransID'].$_POST['OneCard_Amount'].$_POST['OneCard_Currency'].$_POST['OneCard_RTime'].$payment['plus']['payments']['cashu']['testKey'].$_POST['OneCard_Code'];
                $token = md5( $key );
                if ( $usedPackage != NULL && $_POST['OneCard_Code'] == "00" && $_POST['OneCard_RHashKey'] == $token )
                {
                    $playerId = base64_decode( $_POST['OneCard_Field1'] );
                    $goldNumber = $usedPackage['gold'];
                    $m = new PaymentModel( );
                    $m->incrementPlayerGold( $playerId, $goldNumber );
                    $m->dispose( );
                    echo "<h2 style=\"color:#00ff00;\">success</h2>";
                }
                else
                {
                    echo "<h2 style=\"color:#ff0000;\">failed</h2>";
                }
                $p = new GPage( );
                $p->run( );
                return;
            }
            $usedPayment = NULL;
            foreach ( $AppConfig['plus']['payments'] as $payment )
            {
                if ( $payment['merchant_id'] == $merchant_id )
                {
                    $usedPayment = $payment;
                }
            }
            if ( !isset( $_GET[$usedPayment['returnKey']] ) )
            {
                return;
            }
            if ( $usedPackage != NULL && $usedPayment != NULL && $_POST['token'] == md5( sprintf( "%s:%s:%s:%s", $merchant_id, $_POST['amount'], strtolower( $_POST['currency'] ), $_POST['test_mode'] ? $usedPayment['testKey'] : $usedPayment['key'] ) ) )
            {
                $playerId = base64_decode( $_POST['session_id'] );
                $goldNumber = $usedPackage['gold'];
                $m = new PaymentModel( );
                $m->incrementPlayerGold( $playerId, $goldNumber );
                $m->dispose( );
                echo "<h2 style=\"color:#00ff00;\">success</h2>";
            }
            else
            {
                echo "<h2 style=\"color:#ff0000;\">failed</h2>";
            }
        }
    }

}


$p = new GPage( );
$p->run( );
?>
