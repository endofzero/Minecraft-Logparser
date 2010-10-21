#!/usr/bin/php
<?php

/* Include Files *********************/
require("/var/include/dbconnect.php");
/*************************************/

//Configuration

$masterLogPath="/var/www/minecraft/logs/master-log.log";
$injectLogPath="/var/www/minecraft/inject.log";
$logDatabase="minecraft";

if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', 
'-?'))) {
?>

This is a command line PHP script with one option.

  Usage:
  <?php echo $argv[0]; ?> <option>

  <option> can be some word you would like
  to print out. With the --help, -help, -h,
  or -? options, you can get this help.

<?php
} else {
    echo $argv[1]."\n";
}

function clearTable()
{
global $logDatabase;
	mysql_select_db($logDatabase) or die("Unable to select Database");
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
global $logDatabase;
	mysql_select_db($logDatabase) or die("Unable to select Database");
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
global $logDatabase;
	mysql_select_db($logDatabase) or die('Unable to select the Database');
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
}

function injectLogs(){

global $masterLogPath, $injectLogPath, $logDatabase;

$year = 2010;
$testArray = array();

// Load log file into array
if (!file_exists($masterLogPath))
{
	trigger_error( "No File found to parse: $masterLogPath", E_USER_WARNING );
	RETURN;
}
else
{
	$testArray=file($masterLogPath);
}

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

mysql_select_db($logDatabase) or die('Unable to select the Database');
	$sql = "INSERT INTO logs(Date, Class, Text, Hash) VALUES ('$dateTime', '$class', 
'".addslashes($value)."','$hash')";

//echo $sql;
	// Execute query
	if (mysql_query($sql))
	{
		$outStr = $dateTime ." ".$class." ".$value." ".$hash."\n";
		$output = file_put_contents($injectLogPath,$outStr,FILE_APPEND);
		$injectCount++;
	}else{
		if (preg_match("/Duplicate/",trim(mysql_error()))>0)
		{
			$dupeCount++;
		}else{
			$errorList .= "Error: " . mysql_error() . "('$dateTime', '$class', 
'".htmlspecialchars($value)."'</br>";
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
if ($errorCount>0)
{
	echo "Errors Returned:</br>";
	echo $errorList;
}
else
{
	// Zero out the file
	$file = fopen($masterLogPath,"r+");
	if (filesize($masterLogPath)!=0)
	{
		if (!ftruncate($file,0))
		{
			echo ("Error clearing $masterLogPath");
  		}
		else
  		{
  		echo ("Cleared Master Log");
  		}
  	}
}

fclose($file);

} // Inject Logs


?>
