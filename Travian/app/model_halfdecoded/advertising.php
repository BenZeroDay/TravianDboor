<?php
/*********************/
/*                   */
/*  Dezend for PHP5  */
/*         NWS       */
/*      Nulled.WS    */
/*                   */
/*********************/

class AdvertisingModel extends ModelBase
{

    public function Advertising( $post, $type )
    {
        if ( $type == "add" )
        {
            $this->provider->executeQuery( "INSERT INTO `g_banner` SET \r\n\t\t\t`name` = '%s',\r\n\t\t\t`url` = '%s',\r\n\t\t\t`cat` = '%s',\r\n\t\t\t`image` = '%s',\r\n\t\t\t`type` = '%s',\r\n\t\t\t`date` = '%s',\r\n\t\t\t`visit` = '%s',\r\n\t\t\t`view` = '%s'\r\n\t\t\t ;", array( $post['name'], $post['url'], $post['cat'], $post['image'], $post['type'], time( ), 0, 0 ) );
        }
        else
        {
            if ( $type == "edit" )
            {
                $this->provider->executeQuery( "UPDATE `g_banner` SET \r\n\t\t\t`name` = '%s',\r\n\t\t\t`url` = '%s',\r\n\t\t\t`cat` = '%s',\r\n\t\t\t`image` = '%s',\r\n\t\t\t`type` = '%s'\r\n\t\t\tWHERE `ID` = '%s'\r\n\t\t\t ;", array( $post['name'], $post['url'], $post['cat'], $post['image'], $post['type'], $post['ID'] ) );
            }
        }
    }

    public function DeleteAdvertising( $advID )
    {
        $this->provider->executeQuery( "DELETE FROM `g_banner` WHERE `ID` = '%s' ;", array( $advID ) );
    }

    public function GetBanner( $place )
    {
        $row = $this->provider->fetchRow( "SELECT * FROM `g_banner` WHERE `cat` = '%s' ORDER BY RAND() LIMIT 1;", array( $place ) );
        $this->provider->executeQuery( "UPDATE `g_banner`SET `view` = `view`+1 WHERE `ID` = '%s' ;", array( $row['ID'] ) );
        return $row;
    }

    public function GoToBanner( $ID )
    {
        $row = $this->provider->fetchRow( "SELECT * FROM `g_banner` WHERE `ID` = '%s' LIMIT 1;", array( $ID ) );
        $this->provider->executeQuery( "UPDATE `g_banner`SET `visit` = `visit`+1 WHERE `ID` = '%s' ;", array( $row['ID'] ) );
        return $row['url'];
    }

    public function GetAdvertisings( $pageIndex, $pageSize )
    {
        return $this->provider->fetchResultSet( "SELECT * FROM `g_banner` LIMIT %s,%s;", array( $pageIndex * $pageSize, $pageSize ) );
    }

    public function getAdvertisingCount( )
    {
        return $this->provider->fetchScalar( "SELECT COUNT(*) FROM `g_banner` ;" );
    }

}

?>
