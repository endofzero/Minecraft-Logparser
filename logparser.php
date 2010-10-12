<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
/* Include Files *********************/
require("../../include/dbconnect.php");
/*************************************/

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
	Hash CHAR(32) NOT NULL )";

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

$queryLogs = "SELECT * from logs";
$result = mysql_query($queryLogs);
 $numRows=mysql_num_rows($result);
 if (mysql_num_rows($result) != 0)
 {
while($row = mysql_fetch_array($result)){
//Server stop and start
	if (strrpos(trim($row["Text"]),"rting minecraft server version")>="1"||strrpos(trim($row["Text"]),"topping server")>="1"){
	echo "<h2 style='background-color:black;color:white;'>".$row["Date"]." ". htmlspecialchars(trim($row["Text"]))."</h2></br>";
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
		$fluffTest = $row["Class"]." ".$row["Text"];
		echo "$fluffTest: ".array_search($fluffTest,$fluffArray)." - ".in_array($fluffTest,$fluffArray)."</br>";
		if (in_array($fluffTest,$fluffArray))
		  {
		  echo $row["Date"]." ". $row["Class"]." ".htmlspecialchars(trim($row["Text"]))."</br>";
		  }
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

// Iterate through array parsing the data.
foreach($testArray as $value){

// Generate hash for unique id in SQL - To prevent duplicate entries in SQL
$hash = md5(trim($value));

// If year isn't the first 4, then ignore the line (usually java errors [SEVERE] error is still caught
if (substr($value,0,4)==$year){

// Extract the Date
$dateTime= substr($value,0,19);
$lenDiff = strlen($value);

// Remove the date from the value
$value=substr($value,19,$lenDiff);

// Extract the class
$classMarker = stripos($value,']')+1;
$class = substr($value,0,$classMarker);

// Remove the class from the value
$value=substr($value,$classMarker,$lenDiff);

$value=trim($value);
//echo $dateTime." ".$class." ".htmlspecialchars(trim($value))." : $hash </br>";

mysql_select_db('minecraft') or die('Unable to select the Database');
	$sql = "INSERT INTO logs(Date, Class, Text, Hash) VALUES ('$dateTime', '$class', 
'".addslashes($value)."', 
'$hash')";

//echo $sql;
	// Execute query
	if (mysql_query($sql)){echo "X";
	}else{echo "</br>Error injecting data: " . mysql_error() . "('$dateTime', '$class', 
'".htmlspecialchars($value)."'</br>";}

}
}
//print_r($testArray);
//print_r(file("/var/www/minecraft/logs/master-log.log"));
}

?>
