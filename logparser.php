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
//            $diff    =    $uts['start'] - $uts['end'];
//            if( $days=intval((floor($diff/86400))) )
//                $diff = $diff % 86400;
//            if( $hours=intval((floor($diff/3600))) )
//                $diff = $diff % 3600;
//            if( $minutes=intval((floor($diff/60))) )
//                $diff = $diff % 60;
//            $diff    =    intval( $diff );            
//            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
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
	global $displayFluff;
	$serverStats = array();
	$chat = array();
	$connects = array();
	
	$users = array();

	$fluffArray = array();
	// Load fluff file into array
	$fluffArray=file("fluff.txt");
	//Trim Array and quote for preg
	array_walk($fluffArray,"trimArray");
	
	mysql_select_db("minecraft") or die("Unable to select Database");
	
	//Get userlist into Array
	$queryUsers = "SELECT * from users";
	$result = mysql_query($queryUsers);
	$userList= array();
	if (mysql_num_rows($result) != 0)
 	{
		while($row = mysql_fetch_array($result))
		{
   		array_push($userList, trim($row['name'])."-".trim($row['groups']));
		}
	}
	array_walk($userList,"trimArray");
//	print_r($userList);

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
$serverStart = 0;
$prevDate = "";

$uptimeSeconds = 0;

$queryLogs = "SELECT * from logs order by Date";
$result = mysql_query($queryLogs);
 $numRows=mysql_num_rows($result);
if (mysql_num_rows($result) != 0)
{
while($row = mysql_fetch_array($result))
{

	//Server start
	if (preg_match("/Starting minecraft server version/",trim($row["Text"]))>0)
	{
	if ($serverStart==1){
		$diff=get_time_difference($startDate,$prevDate);
		$serverLog.= "<div class='logFont serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED <span class='timeStamp'>$startDate - $prevDate</span></div>";
		$fullLog.= "<div class='logFont serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED <span class='timeStamp'>$startDate - $prevDate</span></div>";
		$uptimeSeconds = $uptimeSeconds + (($diff['seconds']) + ($diff['minutes']*60) + (($diff['hours']*60)*60));
	}
	$serverLog.= "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$fullLog.= "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$startDate=$row["Date"];
	$serverStart = 1;		
	// Server Stop
	}elseif (preg_match("/Stopping server/",trim($row["Text"]))>0)
	{
		$endDate=$row["Date"];
		$diff=get_time_difference($startDate,$endDate);
		$serverLog.= "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
		$fullLog.= "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
		$serverLog.= "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$uptimeSeconds = $uptimeSeconds + (($diff['seconds']) + ($diff['minutes']*60) + (($diff['hours']*60)*60));
		$serverStart=0;
	//Chat
	}elseif (strcspn($row["Text"],"<")=="0"){
	$chatLog.= "<div class='userChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$fullLog.= "<div class='userChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";

	//Console command
	}elseif (preg_match("/CONSOLE|Connected players:/",trim($row["Text"]))>0)
	{
		//User console command
		if (strcspn($row["Text"],"[]")=="0"){
			$chatLog.= "<div class='consoleChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
			$fullLog.= "<div class='consoleChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		//System console
		}else{
		if (preg_match("/Giving(.*)some (.*)/",trim($row["Text"]),$matches)>0)
		{
//		print_r($matches);
		$matches[2]= array_search(trim($matches[2]),$itemList);
			$consoleLog .= "<div class='consoleMsg'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>";
			$fullLog .= "<div class='consoleMsg'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>";
		}else{
			$consoleLog.= "<div class='consoleMsg'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
			$fullLog.= "<div class='consoleMsg'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		}

		}
	//Severe error
	}elseif (preg_match("/SEVERE/",trim($row["Class"]))>0)
	{
		$errorLog.= "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	//Warning error
	}elseif (preg_match("/WARNING/",trim($row["Class"]))>0)
	{
		$errorLog.= "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
	//Hey0 Command logging - logging=1
	}elseif (preg_match("/Command used by|tried command|teleported to|Giving .* some|Spawn position changed|created a lighter/",trim($row["Text"]))>0)
	{
		if (preg_match("/Giving(.*)some (.*)/",trim($row["Text"]),$matches)>0)
		{
//		print_r($matches);
		$matches[2]= array_search(trim($matches[2]),$itemList);
		$hey0Log .= "<div class='heyLogging'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>";
		$fullLog .= "<div class='heyLogging'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>";
		}else{
		$hey0Log .= "<div class='heyLogging'>".$row["Date"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog .= "<div class='heyLogging'>".$row["Date"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
		}
	//User Login 
	}elseif (preg_match("/logged in/",trim($row["Text"]))>0)
	{
		foreach ($userList as $user)
		{
			$user=explode('-',$user);
			if (preg_match("/$user[0]/",trim($row["Text"])))
			{
//			echo "<span class='userLogin'> $user[0] </span> ";
			}
		}

		$row["Text"]= preg_replace ( "/\[(.*)\]/" , "" , trim($row["Text"]));

		$serverLog.= "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	//User Logout
	}elseif (preg_match("/lost connection|Disconnecting/",trim($row["Text"]))>0)
	{
		foreach ($userList as $user)
		{
			$user=explode('-',$user);
			if (preg_match("/$user[0]/",trim($row["Text"])))
			{
//			echo "<span class='userLogout'> $user[0] </span>";
			}
		}
		$row["Text"]= preg_replace ( "/\[(.*)\]/" , "" , trim($row["Text"]));
		$row["Text"]= preg_replace ( "/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/" , "*" , trim($row["Text"]));

		$serverLog.= "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	// World Start
	}elseif (preg_match("/Loading properties|Preparing level|Preparing start region|Done! For help|Saving chunks|Starting Minecraft server on/",trim($row["Text"]))>0)
	{
		$serverLog.= "<div class='worldStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='worldStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	// Runecraft
	}elseif (preg_match("/Runecraft|used a|enchanted a/",trim($row["Text"]))>0)
	{
		$runecraftLog.= "<div class='runecraft'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='runecraft'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	//Default Print
	}else{

		$pattern = "/".implode("|", $fluffArray)."/is";

			if (preg_match($pattern,trim($row["Text"]))>0)
			{
			$fluffMatch=1;
				if ($displayFluff==1)
				{
					$fullLog.= "<div class='fluff'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
				}
			}
		$fluffCount++;
		if ($fluffMatch==0)
		{
			$fullLog .= $row["Date"]." ". $row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</br>";
		}
	}
$logCount++;
$prevDate = $row["Date"];
//echo $prevDate."::";
}
}
//Calc Base values
$uptimeMin=$uptimeSeconds/60;
$uptimeHrs=$uptimeMin/60;
$uptimeDay=floor($uptimeHrs/24);
$uptimeWeek=floor($uptimeDay/7);

//Covert into remainders
$uptimeSeconds=str_pad(fmod($uptimeSeconds,60),2,"0",STR_PAD_LEFT);
$uptimeMin=str_pad(floor(fmod($uptimeMin,60)),2,"0",STR_PAD_LEFT);
$uptimeHrs=str_pad(floor(fmod($uptimeHrs,24)),2,"0",STR_PAD_LEFT);
$uptimeDay=str_pad(floor(fmod($uptimeDay,7)),2,"0",STR_PAD_LEFT);
$uptimeWeek=str_pad($uptimeWeek,2,"0",STR_PAD_LEFT);

//$uptimeSeconds=fmod($uptimeSeconds,60);
//$uptimeMin=floor(fmod($uptimeMin,60));
//$uptimeHrs=floor(fmod($uptimeHrs,24));

echo "<div id='uptimeDialog'><span id='uptimeWeek'>$uptimeWeek</span>:<span id='uptimeDay'>$uptimeDay</span>:<span id='uptimeHrs'>$uptimeHrs</span>:<span id='uptimeMin'>$uptimeMin</span>:<span id='uptimeSeconds'>$uptimeSeconds</span></div>";
//echo "<div id='uptime'>Total Server Uptime: <span id='uptimeDay'>$uptimeDay</span> days - $uptimeHrs hours, $uptimeMin minutes, $uptimeSeconds seconds</div>";
?>
<div id="accordion">
	<h3><a href="#">Image</a></h3>
	<div>
	<canvas id="grapher" width="800" height="250">
	This text is displayed if your browser does not support HTML5 Canvas.
	</canvas>
	</div>
	<h3><a href="#">Server</a></h3>
	<div>
			<?php echo $serverLog; ?>
	</div>
	<h3><a href="#">Error</a></h3>
	<div>
			<?php echo $errorLog; ?>
	</div>
	<h3><a href="#">Chat</a></h3>
	<div>
			<?php echo $chatLog; ?>
	</div>
	<h3><a href="#">Console</a></h3>
	<div>
			<?php echo $consoleLog; ?>
	</div>
	<h3><a href="#">Runecraft</a></h3>
	<div>
			<?php echo $runecraftLog; ?>
	</div>
	<h3><a href="#">hey0</a></h3>
	<div>
			<?php echo $hey0Log; ?>
	</div>
	<h3><a href="#">Full Logs</a></h3>
	<div>
			<?php echo $fullLog; ?>
	</div>
</div>
<?php
}
?>

<html><head><title>MineCraft Logs</title>

  <link type="text/css" href="css/dark-hive/jquery-ui-1.8.6.custom.css" rel="stylesheet" />	
  <link rel="stylesheet" type="text/css" href="css/default.css" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.js"></script>
<script type="text/javascript" src="js/main.js"></script>
</head><body>

<div id="legendDialog" title="Color Legend">
<div><span class="severeError">Severe Error</span></div>
<div><span class="WarningError">Warning Error</span></div>
<div><span class="serverStart">Server Start / Stop</span></div>
<div><span class="serverUptime">Server Uptime</span></div>
<div><span class="serverUptimeBad">Server Uptime - Bad</span></div>
<div><span class="worldStart">World Start</span></div>
<div><span class="heyLogging">hey0 Logging</span></div>
<div><span class="runecraft">Runecraft</span></div>
<div><span class="userLogin">User Login</span></div>
<div><span class="userLogout">User Logout</span></div>
<div><span class="userChat">User Chat</span></div>
<div><span class="consoleChat">Console Chat</span></div>
<div><span class="consoleMsg">Console Message</span></div>
</div>

<div id="statsDialog" title="Stats">
<div><span class="severeError">Uptime:</span><div id="uptimeOutput"></div></div>
</div>


<button id="legend">Legend</button>
<button id="stats">Stats</button>

<?php
displayStats();

?>
</body></html>
