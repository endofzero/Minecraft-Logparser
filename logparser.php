<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>MineCraft Logs</title>
<link rel="stylesheet" type="text/css" href="default.css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.js"></script>
</head><body>
	<script>
	$(function() {
		$( "#accordion" )
			.accordion({
			collapsible: true,
			autoHeight: false,
			active: false,
			})
			.sortable({
				axis: "y",
				handle: "h3",
				stop: function() {
					stop = true;
				}
			});
	});

	</script>

<?php
/* Include Files *********************/
require("../../include/dbconnect.php");
/*************************************/

//Configuration

$masterLogPath="/var/www/minecraft/logs/master-log.log";

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
            $diff    =    $uts['start'] - $uts['end'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );            
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
//            trigger_error( "Ending date/time is earlier than the start date/time $start : $end", E_USER_WARNING );
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

function clearTable()
{
	mysql_select_db("minecraft") or die("Unable to select Database");
	if (mysql_query("DELETE FROM logs"))
	{
		echo "Database cleared";
	}
	else
	{
		echo "Error clearing database: " . mysql_error();
	}
}

function dropTable()
{
	mysql_select_db("minecraft") or die("Unable to select Database");
	if (mysql_query("DROP TABLE logs"))
	{
		echo "Table Dropped";
	}
	else
	{
		echo "Error Dropping Table: " . mysql_error();
	}
}
function createTable()
{
	mysql_select_db('minecraft') or die('Unable to select the Database');
	$sql = "CREATE TABLE logs(
	PRIMARY KEY(Hash),
	Date DATETIME,
	Class VARCHAR(20),
	Text VARCHAR(100),
	Hash CHAR(32) NOT NULL)";
	// Execute query
	if (mysql_query($sql))
	{
		echo "Table created";
	}
	else
	{
		echo "Error creating Table: " . mysql_error();
	}

//	CREATE TABLE fluff( PRIMARY KEY (Hash),hash CHAR(32) NOT NULL,text CHAR(100));
}

function displayStats()
{
	$serverStats = array();
	$chat = array();
	$connects = array();

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
        		array_push($userList, trim($row['name']).":".trim($row['groups']));
		}
	}

$logCount = 0;
$serverStart = 0;
$prevDate = "";

$queryLogs = "SELECT * from logs";
$result = mysql_query($queryLogs);
 $numRows=mysql_num_rows($result);
if (mysql_num_rows($result) != 0)
{
while($row = mysql_fetch_array($result))
{

	//Server stop and start
	if (preg_match("/Starting minecraft server version/",trim($row["Text"]))>0)
	{
	if ($serverStart==1){
		$diff=get_time_difference($startDate,$prevDate);
		$serverLog.= "<div class='serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED</div>";
		$fullLog.= "<div class='serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED</div>";
	}
	$serverLog.= "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$fullLog.= "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$startDate=$row["Date"];
	$serverStart = 1;		
	}
	elseif (preg_match("/Stopping server/",trim($row["Text"]))>0)
	{

		$endDate=$row["Date"];
		$diff=get_time_difference($startDate,$endDate);
		$serverLog.= "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
		$fullLog.= "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
		$serverLog.= "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='serverStop'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$serverStart=0;
//Chat
	}elseif (strcspn($row["Text"],"><")=="0"){
	$chatLog.= "<div class='userChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	$fullLog.= "<div class='userChat'>>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	//Console command
	}elseif (preg_match("/CONSOLE|Connected players:/",trim($row["Text"]))>0)
	{
		//User console command
		if (strcspn($row["Text"],"[]")=="0"){
			$chatLog.= "<div class='consoleChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
			$fullLog.= "<div class='consoleChat'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		//System console
		}else{
			$consoleLog.= "<div class='consoleMsg'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
			$fullLog.= "<div class='consoleMsg'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		}
//Severe error
	}
	elseif (preg_match("/SEVERE/",trim($row["Class"]))>0)
	{
		$errorLog.= "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Warning error
	}
	elseif (preg_match("/WARNING/",trim($row["Class"]))>0)
	{
		$errorLog.= "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
//Hey0 Command logging - logging=1
	}
	elseif (preg_match("/Command used by|tried command|teleported to|Giving .* some|Spawn position changed|created a lighter/",trim($row["Text"]))>0)
	{
		$hey0Log .= "<div class='heyLogging'>".$row["Date"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog .= "<div class='heyLogging'>".$row["Date"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
//User Login 
	}
	elseif (preg_match("/logged in/",trim($row["Text"]))>0)
	{
		$serverLog.= "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//User Logout
	}
	elseif (preg_match("/lost connection|Disconnecting/",trim($row["Text"]))>0)
	{
		$serverLog.= "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Default Print
	}elseif (preg_match("/Loading properties|Preparing level|Preparing start region|Done! For help|Saving chunks|Starting Minecraft server on/",trim($row["Text"]))>0)
	{
		$serverLog.= "<div class='worldStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='worldStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	}elseif (preg_match("/Runecraft|used a|enchanted a/",trim($row["Text"]))>0)
	{
		$runecraftLog.= "<div class='runecraft'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$fullLog.= "<div class='runecraft'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	
	}else{

		$pattern = "/".implode("|", $fluffArray)."/is";

			if (preg_match($pattern,trim($row["Text"]))>0)
			{
			$fluffMatch=1;
			$fullLog.= "<div class='fluff'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
			}
		$fluffCount++;
		if ($fluffMatch==0)
		{
//		echo "1: ". md5(strtoupper(trim($row["Text"]))) ." ". htmlspecialchars($row["Text"]) . "</br>2: " . md5(strtoupper(trim($value))) ." ". htmlspecialchars($value) . "</br>";
			echo $row["Date"]." ". $row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</br>";
}
$logCount++;
$prevDate = $row["Date"];
//echo $prevDate."::";
	}
}
}
?>
<div id="accordion">
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

function injectLogs(){

global $masterLogPath;

$year = 2010;
$testArray = array();

// Load log file into array
$testArray=file($masterLogPath);
$injectCount=0;
$dupeCount=0;
$errorCount=0;

// Iterate through array parsing the data.
foreach($testArray as $value){

// Generate hash for unique id in SQL - To prevent duplicate entries in SQL
$hash = md5(strtoupper(trim($value)));

// If year isn't the first 4, then ignore the line (usually java errors [SEVERE] error is still caught
if (substr($value,0,4)==$year){

// Extract the Date
$dateTime= substr($value,0,19);
$lenDiff = strlen($value);

// Remove the date from the value
$value=substr($value,19,$lenDiff);

// Extract the class
$classMarker = stripos($value,']')+1;
$class = trim(substr($value,0,$classMarker));

// Remove the class from the value
$value=substr($value,$classMarker,$lenDiff);

$value=trim($value);
$fluffhash=md5(strtoupper(trim($value)));
//echo $dateTime." ".$class." ".htmlspecialchars(trim($value))." : $hash </br>";

mysql_select_db('minecraft') or die('Unable to select the Database');
	$sql = "INSERT INTO logs(Date, Class, Text, Hash) VALUES ('$dateTime', '$class', '".addslashes($value)."','$hash')";

//echo $sql;
	// Execute query
	if (mysql_query($sql))
	{
		$injectCount++;
	}else{
		if (preg_match("/Duplicate/",trim(mysql_error()))>0)
		{
			$dupeCount++;
		}else{
			$errorList .= "Error: " . mysql_error() . "('$dateTime', '$class', '".htmlspecialchars($value)."'</br>";
			$errorCount++;
		}
	}
}
}

echo "</br>";
echo "<h2>Injection Counts</h2>";
echo "Log Lines: " . count($testArray)."</br>";
echo "Injections: " . $injectCount."</br>";
echo "Duplicates: " . $dupeCount."</br>";
echo "Errors: " . $errorCount."</br>";
echo "Errors Returned:</br>";
echo $errorList;

//print_r($testArray);
//print_r(file("/var/www/minecraft/logs/master-log.log"));
}

if ($_REQUEST["action"] == ""){$action="stats";}else{$action=$_REQUEST["action"];}

if($action=="stats"){
        displayStats();
    }
else if($action=="inject"){
        injectLogs();
    }
else if($action=="createDB"){
        createDB();
    }
else if($action=="createTable"){
        createTable();
    }
else if($action=="dropTable"){
        dropTable();
    }
else if($action=="clearTable"){
        clearTable();
}else{
         displayStats();
}

?>
</body></html>
