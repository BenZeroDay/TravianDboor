<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends popuppage
{

    public $requestPaymentProvider = FALSE;
    public $providerType = "";
    public $package = NULL;
    public $payment = NULL;
    public $secureId = NULL;
    public $Domain = NULL;

    public function GPage( )
    {
        parent::popuppage( );
        $this->viewFile = "payment.phtml";
    }

    public function load( )
    {
        parent::load( );
        $this->Domain = webhelper::getbaseurl( );
        if ( isset( $_GET['p'], $_GET['pg'] ) )
        {
            $this->providerType = trim( $_GET['p'] );
            $this->packageIndex = trim( $_GET['pg'] );
            if ( isset( $this->appConfig['plus']['payments'][$this->providerType], $this->appConfig['plus']['packages'][$this->packageIndex] ) )
            {
                $this->title = sprintf( payment_loading." %s ...", $this->appConfig['plus']['payments'][$this->providerType]['name'] );
                $this->package = $this->appConfig['plus']['packages'][$this->packageIndex];
                $this->payment = $this->appConfig['plus']['payments'][$this->providerType];
                $this->requestPaymentProvider = isset( $_GET['c'] );
                if ( $this->requestPaymentProvider )
                {
                    $this->layoutViewFile = NULL;
                    $this->secureId = base64_encode( $this->player->playerId );
                }
            }
            else
            {
                echo "<script type=\"text/javascript\">self.close();</script>";
            }
        }
    }

}

$p = new GPage( );
$p->run( );
?>
