<?php
// +---------------------------------------------+
// | Military Battle System :: Broker Logs       |
// +---------------------------------------------+
// | All the games changable variables           |
// +---------------------------------------------+
// | Copyright Rodney Cheney 2011-2015           |
// +---------------------------------------------+

$conn = mysql_connect("localhost","extreemg_s1","Mq)r{i?=P0&^");   //"localhost","DATABASE USERNAME","DATABASE PASSWORD"
mysql_select_db("extreemg_s2") or die(mysql_error());
		
    $SECRET = "1196d32648cbdd232a172c7f285772d5";   ///this is you apps secret key get this from app info
    $snuid = $_REQUEST['uid'];      // this grabs the snuid from the url
    $currency = $_REQUEST['new'];   // this grabs amount of points to award user
    $total= $_REQUEST['total'];     // this grabs total points super rewards has ever sent user
    $offerwallID = $_REQUEST['oid'];// this grabs the offer walls offer ID
    $transactionID = $_REQUEST['id'];//this grabs the taransaction id from super rewards
    $sidverify = $_REQUEST['sig'];  // this grabs the hashed info for you to authenticate with
   
    // make  a hash of our own to verify authenicc transaction
    $sig = md5($_REQUEST['id'] . ':' . $_REQUEST['new'] . ':' . $_REQUEST['uid'] . ':' . $SECRET);
    
    //here we are gonna count the total points loged each time user has recived points
    $sql = "SELECT SUM(`points`) as `DeadWeight` FROM `sr_log` WHERE `userID`='$snuid'";

    if( !($result = mysql_query($sql)) ) 
        {
            die('Failed to query database. srlogs.');
        }
 
 $row = mysql_fetch_array($result);
 $DeadWeight = $row['DeadWeight']; 
 
    

// You may want to add other checks to ensure that the user exists in your system

//Check if hashed info is same as hashed info from superrewards if not do nothing.
if  ($sidverify == $sig)
    {
        //Insert Super Rewards transaction info into your database
        mysql_query("INSERT INTO sr_log SET points=$currency,total=$total,oid=$offerwallID,userID=$snuid, transID=$transactionID");
        //Check if the total amount of points awarded to users is less then or the same as total from super rewards
        //This will make sure you dont give more then you should.
        if  ($DeadWeight <= $total)
            {
                //If all is good Update the user with there points from the url request.
                mysql_query("UPDATE p_players SET gold_num=gold_num+$currency  WHERE id=$snuid");
            }
      echo "1";
    }else{
        echo "0";     
    }
    

  
?>