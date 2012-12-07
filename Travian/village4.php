<?php

require( ".".DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."village4.php" ); 
class GPage extends securegamepage
{
    public $selectedTabIndex = NULL;
    public $troops = NULL; 
    
    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "village4.phtml";
        $this->contentCssClass = "statistics";
    }  
    
     public function load( )
    {
        parent::load( );
        $this->selectedTabIndex = isset( $_GET['t'] ) && is_numeric( $_GET['t'] ) && 0 <= intval( $_GET['t'] ) && intval( $_GET['t'] ) <= 5 ? intval( $_GET['t'] ) : 0;
           
        if ( $this->selectedTabIndex == 0 )
        {
        
        }
            
    }
    
    
    
     public function preRender( )
    {
        parent::prerender( );
        if ( 0 <= $this->selectedTabIndex )
        {
            $this->villagesLinkPostfix .= "&t=".$this->selectedTabIndex;
        }
    }
    
    
}
$p = new GPage( );
$p->run( );
?>
