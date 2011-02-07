<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/* Include Files *********************/
require("/var/include/dbconnect.php");
/*************************************/

//Configuration
$parserSettings=parse_ini_file("parser.settings");
$masterLogPath=$parserSettings["masterLogPath"];
$injectLogPath=$parserSettings["injectLogPath"];
$displayFluff=$parserSettings["displayFluff"];
$maxLines=$parserSettings["maxLines"];
$hideIP=$parserSettings["hideIP"];
$logDirection=$parserSettings["logDirection"];

$logTableName=$parserSettings["logTableName"];
$logDatabase=$parserSettings["logDatabase"];


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
	global $displayFluff, $maxLines, $logTableName, $logDatabase, $hideIP;
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

	$userStats = array ();

	$users = array();

	$fluffArray = array();
	// Load fluff file into array
	$fluffArray=file("fluff.txt");
	//Trim Array and quote for preg
	array_walk($fluffArray,"trimArray");

	mysql_select_db($logDatabase) or die("Unable to select Database: $logDatabase");

	//Get userlist into Array
	$queryUsers = "SELECT * from users";
	$result = mysql_query($queryUsers);
	$userList= array();
	$idCnt = 0;
	if (mysql_num_rows($result) != 0)
 	{
		while($row = mysql_fetch_array($result))
		{
   		array_push($userList, $idCnt.":".trim($row['name'])."-".trim($row['groups']));
		$idCnt++;
		}
	}
//	array_walk($userList,"trimArray");
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
//	print_r($userList);

$logCount = 0;
$serverStart = -1;
$prevDate = "";
$prevUserDate = "";
$uptimeSeconds = 0;

$firstDate = 0;
$lastDate = 0;

//$queryLogs = "SELECT * from logs where Date >= DATE_SUB(NOW(),INTERVAL 1 DAY) order by Date";
$queryLogs = "SELECT * from $logTableName order by Date";
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

		array_push($masterOutput, "<div class='logFont serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED <span class='timeStamp'>$startDate - $prevDate</span></div>");
		array_push($serverOutput, "<div class='logFont serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED <span class='timeStamp'>$startDate - $prevDate</span></div>");
		$uptimeSeconds = $uptimeSeconds + (($diff['seconds']) + ($diff['minutes']*60) + (($diff['hours']*60)*60));
//		echo date("U",strtotime($prevDate))." 0</br>";
		array_push($serverStats, "0:".date("U",strtotime($prevDate)));
	}
	$serverLog.= "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	array_push($masterOutput, "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	array_push($serverOutput, "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	$fullLog.= "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$startDate=$row["Date"];
//	echo date("U",strtotime($row["Date"]))." 1</br>";
	array_push($serverStats, "1:".date("U",strtotime($row["Date"])));
	$serverStart = 1;
// Server Stop
	}elseif (preg_match("/Stopping server/",trim($row["Text"]))>0)
	{
		$endDate=$row["Date"];
		$diff=get_time_difference($startDate,$endDate);
		$serverLog.= "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
	array_push($masterOutput, "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>");
	array_push($serverOutput, "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>");
		$fullLog.= "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
		$serverLog.= "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	array_push($masterOutput, "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	array_push($serverOutput, "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		$fullLog.= "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$uptimeSeconds = $uptimeSeconds + (($diff['seconds']) + ($diff['minutes']*60) + (($diff['hours']*60)*60));
//		echo date("U",strtotime($row["Date"]))." 0</br>";
		array_push($serverStats, "0:".date("U",strtotime($row["Date"])));
		$serverStart=0;

//Disconnections (haven't logged in yet)

	}elseif (preg_match("/Disconnecting/",trim($row["Text"]))>0)
	{
		array_push($masterOutput, "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
        	array_push($serverOutput, "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");

//Chat
	}elseif (strcspn($row["Text"],"<")=="0"){
		array_push($masterOutput, "<div class='userChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($chatOutput, "<div class='userChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($userChat, date("U",strtotime($row["Date"])));

//Console command
	}elseif (preg_match("/CONSOLE|Connected players:/",trim($row["Text"]))>0)
	{
//User console command
		if (strcspn($row["Text"],"[]")=="0"){
			array_push($masterOutput, "<div class='consoleChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
			array_push($chatOutput, "<div class='consoleChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
			array_push($consoleChat, date("U",strtotime($row["Date"])));
//System console
		}else{
		array_push($consoleMsg, date("U",strtotime($row["Date"])));
		if (preg_match("/Giving(.*)some (.*)/",trim($row["Text"]),$matches)>0)
		{
//		print_r($matches);
		$matches[2]= array_search(trim($matches[2]),$itemList);
			array_push($masterOutput, "<div class='consoleMsg'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>");
			array_push($consoleOutput, "<div class='consoleMsg'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>");
		}else{
			array_push($masterOutput, "<div class='consoleMsg'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
			array_push($consoleOutput, "<div class='consoleMsg'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		}

		}
//Severe error
	}elseif (preg_match("/SEVERE/",trim($row["Class"]))>0)
	{
		array_push($masterOutput, "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($errorOutput, "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($severeErrors, date("U",strtotime($row["Date"])));
//Warning error
	}elseif (preg_match("/WARNING/",trim($row["Class"]))>0)
	{
		array_push($masterOutput, "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($errorOutput, "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($warningErrors, date("U",strtotime($row["Date"])));

//Bukkit Command logging
	}elseif (preg_match("/issued server command/",trim($row["Text"]))>0)
	{
		array_push($heyLogging, date("U",strtotime($row["Date"])));
		if (preg_match("/Giving(.*)some (.*)/",trim($row["Text"]),$matches)>0)
		{
//		print_r($matches);
		$matches[2]= array_search(trim($matches[2]),$itemList);
		array_push($masterOutput, "<div class='heyLogging'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>");
		array_push($heyOutput, "<div class='heyLogging'>".$row["Date"]." Giving $matches[1] some <span class='itemName'>$matches[2]</span></div>");
		}else{
		array_push($masterOutput, "<div class='heyLogging'>".$row["Date"]." ".htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($heyOutput, "<div class='heyLogging'>".$row["Date"]." ".htmlspecialchars(trim($row["Text"]))."</div>");
		}
//User Login 
	}elseif (preg_match("/logged in/",trim($row["Text"]))>0)
	{
		foreach ($userList as $user)
		{
			$user=explode('-',$user);
			$user=explode(':',$user[0]);
//	print_r($user);
			if (preg_match("/$user[1]/",trim($row["Text"])))
			{
//			echo "<span class='userLogin'> $user[0] </span> ";
				array_push($userStats, "$user[0]:1:".date("U",strtotime($row["Date"])));
			}
		}

		if ($hideIP!=0){
			$row["Text"]= preg_replace ( "/\[(.*)\]/" , "" , trim($row["Text"]));
		}
	array_push($masterOutput, "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	array_push($serverOutput, "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
//User Logout
	}elseif (preg_match("/lost connection/",trim($row["Text"]))>0)
	{
		foreach ($userList as $user)
		{
			$user=explode('-',$user);
			$user=explode(':',$user[0]);
			if (preg_match("/$user[1]/",trim($row["Text"])))
			{
//			echo "<span class='userLogout'> $user[0] </span>";
				array_push($userStats, "$user[0]:0:".date("U",strtotime($row["Date"])));
			}
		}
		if ($hideIP!=0){
			$row["Text"]= preg_replace ( "/\[(.*)\]/" , "" , trim($row["Text"]));
		$row["Text"]= preg_replace ( "/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/" , "*" , trim($row["Text"]));
		}
//	array_push($userStats, "0:".date("U",strtotime($prevDate)));
	array_push($masterOutput, "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	array_push($serverOutput, "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
// World Start
	}elseif (preg_match("/Loading properties|Preparing level|Preparing start region|Done! For help|Saving chunks|Starting Minecraft server on|your current classpath is/",trim($row["Text"]))>0)
	{
	array_push($masterOutput, "<div class='worldStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	array_push($serverOutput, "<div class='worldStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
// Worldedit
	}elseif (preg_match("/WorldEdit/",trim($row["Text"]))>0)
	{
	array_push($masterOutput, "<div class='runecraft'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
	array_push($runecraftOutput, "<div class='runecraft'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		array_push($runecraft, date("U",strtotime($row["Date"])));
	//Default Print
	}else{
		array_push($masterOutput, "<div class='fluff'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
		$pattern = "/".implode("|", $fluffArray)."/is";
//		echo $pattern;
			if (preg_match($pattern,trim($row["Text"]))>0)
			{
			$fluffMatch=1;
				if ($displayFluff==1)
				{
					array_push($masterOutput, "<div class='fluff'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>");
				}
			}
		$fluffCount++;
		if ($fluffMatch==0)
		{
			//array_push($masterOutput, $row["Date"]." ". $row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</br>");
		}
	}
$logCount++;
$prevDate = $row["Date"];

if ($firstDate==0){
$firstDate = date("U",strtotime($row["Date"]));
//echo date("U",strtotime($row["Date"]))."-".$row["Date"]."</br>";
}

if (date("U",strtotime($row["Date"]))>= $firstDate)
{
$lastDate = date("U",strtotime($row["Date"]));
}
//print_r($userStats);
//echo $row["Date"]."</br>";
//echo $prevDate."::";
}
}
$diff = $lastDate - $firstDate;
//echo $firstDate." : ".$lastDate." - ".$diff;

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

//Build Text

$i=0;
foreach (array_reverse($masterOutput) as $value)
{if ($i<=$maxLines){$masterText.=$value;}$i++;}

$i=0;
foreach (array_reverse($heyOutput) as $value)
{if ($i<=$maxLines){$hey0Text.=$value;}$i++;}

$i=0;
foreach (array_reverse($runecraftOutput) as $value)
{if ($i<=$maxLines){$runecraftText.=$value;}$i++;}

$i=0;
foreach (array_reverse($chatOutput) as $value)
{if ($i<=$maxLines){$chatText.=$value;}$i++;}

$i=0;
foreach (array_reverse($consoleOutput) as $value)
{if ($i<=$maxLines){$consoleText.=$value;}$i++;}

$i=0;
foreach (array_reverse($errorOutput) as $value)
{if ($i<=$maxLines){$errorText.=$value;}$i++;}

$i=0;
foreach (array_reverse($serverOutput) as $value)
{if ($i<=$maxLines){$serverText.=$value;}$i++;}



$secDiff = $lastDate - $firstDate;
$userCount = count($userList);
echo "<div style='display:none;' id='unixMin'>$firstDate</div>";
echo "<div style='display:none;' id='unixMax'>$lastDate</div>";
echo "<div style='display:none;' id='userCount'>$userCount</div>";

//echo $firstDate." - ".$lastDate." : ".$secDiff." ". (($serverStats[1] - $serverStats[0])/$secDiff)."</br>";

echo "<div style='display:none;' id='serverStatsArray'>";
foreach ($serverStats as $value)
{
echo "<span class='serverStartItem'>$value</span>";
}
echo "</div>";

echo "<div style='display:none;' id='userNameArray'>";
foreach ($userList as $value)
{
$value=explode('-',$value);

echo "<span class='userNameItem'>$value[0]</span>";
}
echo "</div>";

echo "<div style='display:none;' id='userStatsArray'>";
foreach ($userStats as $value)
{
echo "<span class='userStatsItem'>$value</span>";
}
echo "</div>";

echo "<div style='display:none;' id='severeErrorArray'>";
foreach ($severeErrors as $value)
{
echo "<span class='severeErrorItem'>$value</span>";
}
echo "</div>";

echo "<div style='display:none;' id='warningErrorArray'>";
foreach ($warningErrors as $value)
{
echo "<span class='warningErrorItem'>$value</span>";
}
echo "</div>";

echo "<div style='display:none;' id='userChatArray'>";
foreach ($userChat as $value)
{
echo "<span class='userChatItem'>$value</span>";
}

echo "</div>";

echo "<div style='display:none;' id='consoleChatArray'>";
foreach ($consoleChat as $value)
{
echo "<span class='consoleChatItem'>$value</span>";
}

echo "</div>";

echo "<div style='display:none;' id='consoleMsgArray'>";
foreach ($consoleMsg as $value)
{
echo "<span class='consoleMsgItem'>$value</span>";
}
echo "</div>";

echo "<div style='display:none;' id='hey0Array'>";
foreach ($heyLogging as $value)
{
echo "<span class='hey0Item'>$value</span>";
}
echo "</div>";

echo "<div style='display:none;' id='runecraftArray'>";
foreach ($runecraft as $value)
{
echo "<span class='runecraftItem'>$value</span>";
}
echo "</div>";

//print_r($serverStats);

echo "<div style='display:none;' id='uptimeDialog'><span id='uptimeWeek'>$uptimeWeek</span>:<span id='uptimeDay'>$uptimeDay</span>:<span id='uptimeHrs'>$uptimeHrs</span>:<span id='uptimeMin'>$uptimeMin</span>:<span id='uptimeSeconds'>$uptimeSeconds</span></div>";
//echo "<div id='uptime'>Total Server Uptime: <span id='uptimeDay'>$uptimeDay</span> days - $uptimeHrs hours, $uptimeMin minutes, $uptimeSeconds seconds</div>";
?>

<button id="stats">Stats</button>
<button id="graphOpt">Graph</button>
<button id="legend">Log</button>
<p>
	<label for="amount">Range:</label>
	<span id="amount" style="border:0; color:#f6931f; font-weight:bold; width:70px;" />
</p>

<div id="slider-range" style="width:1010px;"></div></br>
	<div><canvas id="grapher" width="1010" height="250">
	This text is displayed if your browser does not support HTML5 Canvas.
	</canvas></div>

<div id="accordion">
	<h3><a href="#">Server</a></h3>
	<div>
			<?php echo $serverText; ?>
	</div>
	<h3><a href="#">Error</a></h3>
	<div>
			<?php echo $errorText; ?>
	</div>
	<h3><a href="#">Chat</a></h3>
	<div>
			<?php echo $chatText; ?>
	</div>
	<h3><a href="#">Console</a></h3>
	<div>
			<?php echo $consoleText; ?>
	</div>
	<h3><a href="#">WorldEdit</a></h3>
	<div>
			<?php echo $runecraftText; ?>
	</div>
	<h3><a href="#">Bukkit</a></h3>
	<div>
			<?php echo $hey0Text; ?>
	</div>
	<h3><a href="#">Full Logs</a></h3>
	<div>
			<?php echo $masterText; ?>
	</div>

</div>

<div style='display:none' id="legendDialog" title="Color Legend">
<div><span class="serverStart">Server Start / Stop</span></div>
<div><span class="serverUptime">Server Uptime</span></div>
<div><span class="serverUptimeBad">Server Uptime - Bad</span></div>
<div><span class="worldStart">World Start</span></div>
<div><span class="severeError">Severe Error</span></div>
<div><span class="WarningError">Warning Error</span></div>
<div><span class="heyLogging">Bukkit Logging</span></div>
<div><span class="runecraft">WorldEdit</span></div>
<div><span class="userLogin">User Login</span></div>
<div><span class="userLogout">User Logout</span></div>
<div><span class="userChat">User Chat</span></div>
<div><span class="consoleChat">Console Chat</span></div>
<div><span class="consoleMsg">Console Message</span></div>
</div>

<div style='display:none' id="graphDialog" title="Graph Options">
<table>
<tr><td><span class="serverStart2">Server Start/Stop</span></td><td><span id="serverStartGraph"></span></td><td><span id="serverStartLog"></span></td></tr>
<tr><td><span class="severeError">Severe Error</span></td><td><span id="severeErrorGraph"></span></td><td><span id="severeErrorLog"></span></td></tr>
<tr><td><span class="WarningError">Warning Error</span></td><td><span id="warningErrorGraph"></span></td><td><span id="warningErrorLog"></span></td></tr>
<tr><td><span class="heyLogging">Bukkit Logging</span></td><td><span id="heyLoggingGraph"></span></td><td><span id="heyLoggingLog"></span></td></tr>
<tr><td><span class="runecraft">WorldEdit</span></td><td><span id="runecraftGraph"></span></td><td><span id="runecraftLog"></span></td></tr>
<tr><td><span class="userLogin">User Login</span></td><td><span id="userLoginGraph"></span></td><td><span id="userLoginLog"></span></td></tr>
<tr><td><span class="userLogout">User Logout</span></td><td><span id="userLogoutGraph"></span></td><td><span id="userLogoutLog"></span></td></tr>
<tr><td><span class="userChat">User Chat</span></td><td><span id="userChatGraph"></span></td><td><span id="userChatLog"></span></td></tr>
<tr><td><span class="consoleChat">Console Chat</span></td><td><span id="consoleChatGraph"></span></td><td><span id="consoleChatLog"></span></td></tr>
<tr><td><span class="consoleMsg">Console Message</span></td><td><span id="consoleMsgGraph"></span></td><td><span id="consoleMsgLog"></span></td></tr>
</table>
</div>

<div id="statsDialog" title="Stats">
<div><span class="severeError">Uptime:</span><div id="uptimeOutput"></div></div>
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

<?php
displayStats();

?>
</body></html>
