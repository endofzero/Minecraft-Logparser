<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><title>MineCraft Logs</title>
<link rel="stylesheet" type="text/css" href="default.css" />
</head><body>

<?php
/* Include Files *********************/
require("../../include/dbconnect.php");
/*************************************/

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
            trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
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

$fluffArray = array();
// Load fluff file into array
$fluffArray=file("fluff.txt");

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

//echo preg_match("/craft server version/",trim($row["Text"]));

	//Server stop and start
	if (preg_match("/Starting minecraft server version/",trim($row["Text"]))>0)
	{
	echo "<div class='serverStart'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
	if ($serverStart==1){
		$diff=get_time_difference($startDate,$prevDate);
		echo "<div class='serverUptimeBad'>Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']." - NO SHUTDOWN LOGGED</div>";
	}
	$startDate=$row["Date"];
	$serverStart = 1;		
	}
	elseif (preg_match("/Stopping server/",trim($row["Text"]))>0)
	{

		echo "<div class='serverStop' style='background-color:black;color:white;'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		$endDate=$row["Date"];
		$diff=get_time_difference($startDate,$endDate);
		echo "<div class='serverUptime'> Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</div>";
		$serverStart=0;
//Chat
	}elseif (strcspn($row["Text"],"><")=="0"){
	echo "<div style='background-color:black;color:cyan;'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Console command
	}
	elseif (preg_match("/CONSOLE/",trim($row["Text"]))>0)
	{
//User console command
		if (strcspn($row["Text"],"[]")=="0"){
	echo "<div style='background-color:black;color:cyan;;font-weight:bold;'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//System console
		}else{
	echo "<div style='background-color:black;color:green;'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		}
//Severe error
	}
	elseif (preg_match("/SEVERE/",trim($row["Class"]))>0)
	{
		echo "<div class='severeError'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Warning error
	}
	elseif (preg_match("/WARNING/",trim($row["Class"]))>0)
	{
		echo "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
//Hey0 Command logging - logging=1
	}
	elseif (preg_match("/WARNING/",trim($row["Class"]))>0)
	{
		echo "<div class='warningError'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
//User Login 
	}
	elseif (preg_match("/logged in/",trim($row["Text"]))>0)
	{
		echo "<div class='userLogin'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//User Logout
	}
	elseif (preg_match("/lost connection/",trim($row["Text"]))>0)
	{
		echo "<div class='userLogout'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Default Print
	}else{
		$fluffTest = htmlspecialchars(trim($row["Text"]));
//		echo "<div>Log  Check : ".str_pad($logCount,3).": ". $row["Fluff"] ." ". $fluffTest ."</div>";
//		echo "</br>";
		$pattern = "/".trim($row["Text"])."/";
//		echo $pattern."</br>".trim($row["Text"])."</br>";
		$fluffMatch = 0;
		$fluffCount = 0;
		foreach($fluffArray as $value)
		{
//		echo "<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fluff Check: ".strcasecmp($fluffTest,$value)." _ ".levenshtein($fluffTest,$value)." :: ".str_pad($fluffCount,3).": ". md5(strtoupper(trim($value))) ." ". htmlspecialchars($value)."</div>";
//		echo "1: ".str_pad($fluffCount,3).": ". md5(strtoupper(trim($row["Text"]))) ." ". htmlspecialchars($row["Text"]) . "</br>2: ".str_pad($fluffCount,3).": ". md5(strtoupper(trim($value))) ." ". htmlspecialchars($value) . "</br>";
			if (preg_match($pattern,trim($row["Text"]))>0)
//			if (trim($row["Fluff"])==md5(strtoupper(trim(stripcslashes($value)))))
//			if (levenshtein($fluffTest,$value) <= 10)
			{
//			echo " ==MATCH FOUND==</br>";
//			echo "<h3>1: ". md5(strtoupper(trim($row["Text"]))) ." ".  $row["Text"] . "</br>2: " . md5(strtoupper(trim($value))) ." ".  $value . "</h3></br>";
			$fluffMatch=1;
			}
//else{echo "</br>";}	
//			if (stripcslashes(trim($row["Text"]))==trim($value))
//			{
		//	echo "X";
//			echo "<h3>1: ". md5(strtoupper(trim($row["Text"]))) ." ".  $row["Text"] . "</br>2: " . md5(strtoupper(trim($value))) ." ".  $value . "</h3></br>";
//			$fluffMatch=1;
//			}	
		$fluffCount++;
		}

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
}

function injectLogs(){

$year = 2010;
$testArray = array();

// Load log file into array
$testArray=file("/var/www/minecraft/logs/master-log.log");

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
