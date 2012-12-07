<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."register.php" );

class GPage extends gamepage
{

    public $err = array
    (
        0 => "",
        1 => "",
        2 => "",
        3 => ""
    );
    public $success = NULL;
    public $SNdata = NULL;
    public $UserID = 0;

    public function GPage( )
    {
        parent::gamepage( );
        $this->viewFile = "register.phtml";
        $this->contentCssClass = "signup";
    }

    public function load( )
    {
        parent::load( );
        $this->SNdata = 0;
        $this->success = FALSE;
        $ip=$_SERVER['REMOTE_ADDR'];
        if ( $this->isPost( ) )
        {
            if ( $this->globalModel->isGameOver( ) )
            {
                $this->redirect( "over.php" );
            }
            else
            {
                $name = trim( $_POST['name'] );
                $email = trim( $_POST['email'] );
                $pwd = trim( $_POST['pwd'] );
                $this->err[0] = strlen( $name ) < 3 ? register_player_txt_notless3 : "";
                if ( $this->err[0] == "" )
                {
                    $this->err[0] = preg_match( "/[:,\\. \\n\\r\\t\\s]+/", $name ) ? register_player_txt_invalidchar : "";
                }
                if ( $name == "[ally]" || $name == "admin" || $name == "administrator" || $name == "مدير" || $name == "تتار" || $name == "التتار" || $name == "دعم" || $name == "الدعم" || $name == $this->appConfig['system']['adminName'] || $name == tatar_tribe_player )
                {
                    $this->err[0] = register_player_txt_reserved;
                }
                $this->err[1] = !preg_match( "/^[^@]+@[a-zA-Z0-9._-]+\\.[a-zA-Z]+\$/", $email ) ? register_player_txt_invalidemail : "";
                $this->err[2] = strlen( $pwd ) < 4 ? register_player_txt_notless4 : "";
                $this->err[3] = !isset( $_POST['tid'] ) || $_POST['tid'] != 1 && $_POST['tid'] != 2 && $_POST['tid'] != 3 && $_POST['tid'] != 6 && $_POST['tid'] != 7 ? "<li>".register_player_txt_choosetribe."</li>" : "";
                $this->err[3] .= !isset( $_POST['kid'] ) || !is_numeric( $_POST['kid'] ) || $_POST['kid'] < 0 || 4 < $_POST['kid'] ? "<li>".register_player_txt_choosestart."</li>" : "";
                if ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) || 0 < strlen( $this->err[2] ) || 0 < strlen( $this->err[3] ) )
                {
                    return;
                }
                $m = new RegisterModel( );
                
                $this->err[0] = $m->isPlayerNameExists( $name ) ? register_player_txt_usedname : "";
                $this->err[1] = $m->isPlayerEmailExists( $email ) ? register_player_txt_usedemail : "";
                $this->err[1] = $m->isPlayerIpExists( $ip) ? "Canot Register IP address already in use" : "";
                if ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) )
                {
                    $m->dispose( );
                }
                else
                {
                  $ip=$_SERVER['REMOTE_ADDR'];
                    $villageName = new_village_name_prefix." ".$name;
                    #removed 'PLAYERTYPE_NORMAL' right after map_size thing. Dunno why it doesnt make him player_type = 1 but owke.
                    $result = $m->createNewPlayer( $name, $email, $pwd, $_POST['tid'], $_POST['kid'], $villageName, $this->setupMetadata['map_size'], 1, 1, $this->SNdata ,$ip );
                    if ( $result['hasErrors'] )
                    {
                        $this->err[3] = register_player_txt_fullserver;
                        $m->dispose( );
                    }
                    else
                    {
                        $m->dispose( );
                        $link = WebHelper::getbaseurl( )."activate.php?id=".$result['activationCode'];
                        $to = $email;
                        $from = $this->appConfig['system']['email'];
                        $subject = register_player_txt_regmail_sub;
                        $message = sprintf( register_player_txt_regmail_body, $name, $name, $pwd, $link, $link );
                        WebHelper::sendmail( $to, $from, $subject, $message );
                        $this->success = TRUE;
                    }
                }
            }
        }
    }

}


$p = new GPage( );
$p->run( );
?>
