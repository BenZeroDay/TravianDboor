<?php 

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );

class GPage extends securegamepage
{

   
    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "changes.phtml";
        $this->contentCssClass = "player";
    }

    public function load( )
    {
        parent::load( );
        
    }

    

    

}


$p = new GPage( );
$p->run( );
?>
