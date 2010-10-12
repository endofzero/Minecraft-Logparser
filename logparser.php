<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/* Include Files *********************/
require("../../include/dbconnect.php");
/*************************************/

/**
 * Function to calculate date or time difference.
 * 
 * Function to calculate date or time difference. Returns an array or
 * false on error.
 *
 * @author       J de Silva                             <giddomains@gmail.com>
 * @copyright    Copyright &copy; 2005, J de Silva
 * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
 * @param        string                                 $start
 * @param        string                                 $end
 * @return       array
 */
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
            trigger_error( "Ending date/time is earlier than the start 
date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING 
);
    }
    return( false );
}



function clearTable(){
     mysql_select_db("minecraft") or die("Unable to select Database");
     if (mysql_query("DELETE FROM logs")){echo "Database cleared";
     }else{echo "Error clearing database: " . mysql_error();}
}

function dropTable(){
     mysql_select_db("minecraft") or die("Unable to select Database");
     if (mysql_query("DROP TABLE logs")){echo "Table Dropped";
     }else{echo "Error Dropping Table: " . mysql_error();}
}
function createTable()
{
	mysql_select_db('minecraft') or die('Unable to select the Database');
	$sql = "CREATE TABLE logs(
	PRIMARY KEY(Hash),
	Date DATETIME,
	Class VARCHAR(20),
	Text VARCHAR(100),
	Hash CHAR(32) NOT NULL,
	Fluff CHAR(32))";
	// Execute query
	if (mysql_query($sql)){echo "Table created";
	}else{echo "Error creating Table: " . mysql_error();}
}

if ($_REQUEST["action"] == ""){
        $action="stats";
}else{
        $action=$_REQUEST["action"];}

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

function displayStats(){

?>
<html><head><title>MineCraft Logs</title></head><body style='background-color:black;color:white'>

<?php

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

$queryLogs = "SELECT * from logs";
$result = mysql_query($queryLogs);
 $numRows=mysql_num_rows($result);
if (mysql_num_rows($result) != 0)
{
while($row = mysql_fetch_array($result))
{
	//Server stop and start
	if (strrpos(trim($row["Text"]),"rting minecraft server version")>="1")
	{
	echo "<h2 style='background-color:black;color:white;'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</h2></br>";
	$startDate= $row["Date"];		
	}
	elseif (strrpos(trim($row["Text"]),"topping server")>="1")
	{
		echo "<h2 style='background-color:black;color:white;'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</h2></br>";
		$endDate=$row["Date"];
		$diff=get_time_difference($startDate,$endDate);
		echo "Server uptime:". $diff['days'] . ":" . $diff['hours'] . ":" . $diff['minutes'].":".$diff['seconds']."</br>";
//Chat
	}elseif (strcspn($row["Text"],"><")=="0"){
	echo "<div style='background-color:black;color:cyan;'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Console command
	}elseif (strrpos(trim($row["Text"]),"ONSOLE")>="1"){
//User console command
		if (strcspn($row["Text"],"[]")=="0"){
	echo "<div style='background-color:black;color:cyan;;font-weight:bold;'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//System console
		}else{
	echo "<div style='background-color:black;color:green;'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
		}
//Severe error
	}elseif (strrpos(trim($row["Class"]),"SEVERE")>="1"){
		echo "<div style='background-color:black;color:red;font-size:110%;font-weight:bold;'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Warning error
	}elseif (strrpos(trim($row["Class"]),"WARNING")>="1"){
		echo "<div style='background-color:black;color:orange;'>".$row["Date"]." ".$row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</div>";
//Hey0 Command logging - logging=1
	}elseif (strrpos(trim($row["Text"]),"ommand used by")>="1"){
		echo "<div style='background-color:black;color:orange;'>".$row["Date"]." ".$row["Class"]." ". htmlspecialchars(trim($row["Text"]))."</div>";
//Default Print
	}else{
		$fluffTest = htmlspecialchars(trim($row["Text"]));
		echo "Log  Check : ".str_pad($logCount,3).": ". $row["Fluff"] ." ". htmlspecialchars($fluffTest)." ".in_array($fluffTest,$fluffArray)."</br>";
//		if (in_array($fluffTest,$fluffArray))
//		echo "</br>";
		$fluffMatch = 0;
		$fluffCount = 0;
		foreach($fluffArray as $value)
		{
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fluff Check: ".strcasecmp($fluffTest,$value)." _ ".levenshtein($fluffTest,$value)." :: ".str_pad($fluffCount,3).": ". md5(strtoupper(trim($value))) ." ". htmlspecialchars($value) . "</br>";
//		echo "1: ".str_pad($fluffCount,3).": ". md5(strtoupper(trim($row["Text"]))) ." ". htmlspecialchars($row["Text"]) . "</br>2: ".str_pad($fluffCount,3).": ". md5(strtoupper(trim($value))) ." ". htmlspecialchars($value) . "</br>";
			if (levenshtein($fluffTest,$value) <= 10)
			{
		//	echo "X";
//			echo "<h3>1: ". md5(strtoupper(trim($row["Text"]))) ." ".  $row["Text"] . "</br>2: " . md5(strtoupper(trim($value))) ." ".  $value . "</h3></br>";
			$fluffMatch=1;
			}	
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
	}
}
}

?>
</body></html>
<?php
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
	$sql = "INSERT INTO logs(Date, Class, Text, Hash, Fluff) VALUES ('$dateTime', '$class', '".addslashes($value)."','$hash','$fluffhash')";

//echo $sql;
	// Execute query
	if (mysql_query($sql))
	{
//		echo "X";
		$injectCount++;
	}else{
		if (strrpos(trim(mysql_error()),"uplicate")>="1"){
//			echo "D";
			$dupeCount++;
		}else{
			echo "</br>Error: " . mysql_error() . "('$dateTime', '$class', '".htmlspecialchars($value)."'</br>";
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

//print_r($testArray);
//print_r(file("/var/www/minecraft/logs/master-log.log"));
}

?>
