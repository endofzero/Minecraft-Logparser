<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/* Include Files *********************/
require("/var/include/dbconnect.php");
/*************************************/

//Configuration
$parserSettings=parse_ini_file("parser.settings");
$masterLogPath=$parserSettings['masterLogPath'];
$injectLogPath=$parserSettings['injectLogPath'];
$displayFluff=$parserSettings['displayFluff'];
$maxLines=$parserSettings['maxLines'];

function get_time_difference( $start, $end )
{
    $uts['start']      =    strtotime( $start );
    $uts['end']        =    strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );            
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }
        else
        {
            trigger_error( "Ending date/time is earlier than the start date/time $start : $end", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}

function trimArray(&$value,$key)
{
$value=trim($value);
$value=preg_quote($value,'/');
}

function displayStats()
{
	global $displayFluff, $maxLines;
	$serverStats = array();
	$severeErrors = array();
	$warningErrors = array();
	$consoleMsg = array();
	$consoleChat = array();
	$heyLogging = array();
	$runecraft = array();
	$userChat = array();
	$connects = array();

	$masterOutput = array();
	$heyOutput = array();
	$runecraftOutput = array();
	$chatOutput = array();
	$consoleOutput = array();
	$errorOutput = array();
	$serverOutput = array();
		
	$users = array();

	$fluffArray = array();
	// Load fluff file into array
	$fluffArray=file("fluff.txt");
	//Trim Array and quote for preg
	array_walk($fluffArray,"trimArray");
	
	mysql_select_db("minecraft") or die("Unable to select Database");
	
	//Get userlist into Array
	$queryUsers = "SELECT distinct player, count(*) as count from stats group by player";
	$result = mysql_query($queryUsers);
	$userList= array();
	if (mysql_num_rows($result) != 0)
 	{
		while($row = mysql_fetch_array($result))
		{
   		array_push($userList, trim($row['player'])."-".trim($row['count']));
		}
	}
	array_walk($userList,"trimArray");
	print_r($userList);

	//Get item list into Array
	$queryUsers = "SELECT * from items order by itemid";
	$result = mysql_query($queryUsers);
	$itemList= array();
	if (mysql_num_rows($result) != 0)
 	{
		while($row = mysql_fetch_array($result))
		{
		$itemList[trim($row['name'])] = trim($row['itemid']);
//   		array_push($itemList, trim($row['itemid'])."-".trim($row['name']));
		}
	}
	array_walk($itemList,"trimArray");
//	print_r($itemList);

$logCount = 0;
$serverStart = -1;
$prevDate = "";
$uptimeSeconds = 0;

$firstDate = 0;
$lastDate = 0;

$query = "SELECT * from stats order by player,category,stat,value";
$result = mysql_query($query);
$numRows=mysql_num_rows($result);
if (mysql_num_rows($result) != 0)
{
while($row = mysql_fetch_array($result))
{

array_push($masterOutput, "<tr><td>".$row["player"]."</td><td>". 
htmlspecialchars(trim($row["category"]))."</td><td>".$row["stat"]."</td><td>".$row["value"]."</td></tr>");

$logCount++;

}
}
?>

<table><tr><th>Player</th><th>Achievement</th><th>Count</th><th>Last Received</th></tr>
<?php
//Build Text
foreach (array_reverse($masterOutput) as $value)
{$masterText.=$value;}
echo $masterText;

?>
</table>


<?php
}
?>

<html><head><title>MineCraft Logs</title>

  <link type="text/css" href="css/dark-hive/jquery-ui-1.8.6.custom.css" rel="stylesheet" />	
  <link rel="stylesheet" type="text/css" href="css/default.css" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.js"></script>
</head><body>

<?php
displayStats();

?>
</body></html>
