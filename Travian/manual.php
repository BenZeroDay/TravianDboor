<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" ); 
class GPage extends defaultpage
{

    public function GPage( )
    {
        parent::defaultpage( );
        $this->viewFile = "manual.phtml";
    }

}


$p = new GPage( );
$p->run( );
?>
