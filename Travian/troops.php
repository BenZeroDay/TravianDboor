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

    public $isAdmin = NULL;
    public $villageId = NULL;
    public $villageName = NULL;
    public $playerName = NULL;
    public $msgText = NULL;
    public $resources= NULL;
    public $troops = array( );
     public $troopsstring = ''; 
    public $herocount = 0;

    public function GPage( )
    {
        $this->customLogoutAction = TRUE;
        parent::securegamepage( );
        if ( $this->player == NULL )
        {
            exit( 0 );
        }
        $this->viewFile = "troops.phtml";
        $this->layoutViewFile = "layout".DIRECTORY_SEPARATOR."popup.phtml";
    }

    public function load( )
    {
        parent::load( );
        $this->msgText = "";
        $this->isAdmin = $this->data['player_type'] == PLAYERTYPE_ADMIN;
        if ( !$this->isAdmin )
        {
            exit( 0 );
        }
        else
        {
            $this->villageId = isset( $_GET['avid'] ) ? intval( $_GET['avid'] ) : 0;
            if ( $this->villageId <= 0 )
            {
                exit( 0 );
            }
            else
            {
                $m = new TroopsModel( );
                if ( $this->isPost( ) )
                {
                   //update troops
                    if (isset($_POST['hero']))
                    {
                        $row = $m->getVillageData( $this->villageId ); 
                        
                        $m->updatehero($_POST['hero'], $row['player_id']);
                    }
                    $m->updateTroops($_POST,$this->villageId);
                    $this->msgText = data_saved;
                    //$this->redirect('troops.php?avid='.$this->villageId);  
                }
                $row = $m->getVillageData( $this->villageId );
                if ( $row == NULL || intval( $row['player_id'] ) == 0 || $row['is_oasis'] )
                {
                    exit( 0 );
                }
                else
                {
                $t_arr = explode( "|", $row['troops_num'] );
                //var_dump($t_arr); 
        foreach ( $t_arr as $t_str )
        {
            $t2_arr = explode( ":", $t_str );
            $t2_arr = explode( ",", $t2_arr[1] );
                            //var_dump($t2_arr);
            $this->troops = $t2_arr;
            
        }
                }
            }
        }
    }

}


$p = new GPage( );
$p->run( );
?>
