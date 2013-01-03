<?php



echo "?";

include( "./app/config.php" );

$db_connect = mysql_connect( $AppConfig['db']['host'], $AppConfig['db']['user'], $AppConfig['db']['password'] );

mysql_select_db( $AppConfig['db']['database'], $db_connect );

$q = "SELECT * FROM g_settings order by cur_week DESC LIMIT 0, 1";

$result = mysql_query( $q );

if ( mysql_num_rows( $result ) )

{

    $row = mysql_fetch_assoc( $result );

    $week = $row['cur_week'] + 1;

}

else

{

    $week = "1";

}

$result = mysql_query( "SELECT * FROM p_players WHERE id > 0 ORDER BY week_dev_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "1:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_players SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_dev_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_players WHERE id > 0 ORDER BY week_attack_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "2:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_players SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_attack_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_players WHERE id > 0 ORDER BY week_defense_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "3:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_players SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_defense_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_players WHERE id > 0 ORDER BY week_thief_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "4:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_players SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_thief_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

if ( $week == "1" )

{

    if ( !mysql_query( "UPDATE p_alliances SET medals='::'" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_alliances WHERE id > 0 ORDER BY week_dev_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "5:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_alliances SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_dev_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_alliances WHERE id > 0 ORDER BY week_attack_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "6:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_alliances SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_attack_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_alliances WHERE id > 0 ORDER BY week_defense_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "7:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_alliances SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_defense_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

$result = mysql_query( "SELECT * FROM p_alliances WHERE id > 0 ORDER BY week_thief_points DESC Limit 10" );

$i = 0;

while ( $row = mysql_fetch_array( $result ) )

{

    $i++;

    $medal = "8:{$i}:{$week}";

    $id = $row['id'];

    if ( !mysql_query( "UPDATE p_alliances SET medals=CONCAT_WS(',', medals, '{$medal}') WHERE id='{$id}' AND week_thief_points > 0" ) )

    {

        exit( mysql_error( ) );

    }

}

if ( !mysql_query( "UPDATE g_settings SET cur_week='{$week}'" ) )

{

    exit( mysql_error( ) );

}

if ( !mysql_query( "UPDATE p_players SET week_dev_points='0', week_attack_points='0', week_defense_points='0', week_thief_points='0'" ) )

{

    exit( mysql_error( ) );

}

if ( !mysql_query( "UPDATE p_alliances SET week_dev_points='0', week_attack_points='0', week_defense_points='0', week_thief_points='0'" ) )

{

    exit( mysql_error( ) );

}

echo "<p align=\"center\"><font color=\"blue\" size=\"5\"> עדכון הטופ 10 בוצע בהצחלה. </font></p>";

echo "<p align=\"center\"><font color=\"blue\" size=\"5\"> כל שחקנים טופ ה-10 קיבלו את המדליות המגיעיות להם </font></p>";

echo "<p align=\"center\"><font color=\"blue\" size=\"5\"> אנא, אל תרענן דף זה, במידה ותערנן שוב את הדף אתה תחלק פעם נוספת את המדליות! </font></p>";

echo "<p align=\"center\"><font color=\"blue\" size=\"5\"> המשך משחק מהנה </font></p>";

echo "<p align=\"center\">&nbsp;</p>";

echo "<p align=\"center\">&nbsp;</p>";

echo "<p align=\"center\">&nbsp;</p>";

echo "<p class=\"f16\" align=\"center\">\r\n<a href=\"village1.php\"><font size=\"4\" color=\"green\">\r\n<span style=\"text-decoration: none\">» חזרה לשרת</span></font></a></p>";

mysql_close( $db_connect );

echo " ";

?>
