#!/usr/bin/php
<?php

/* Include Files *********************/
require("/var/include/dbconnect.php");
/*************************************/

//Configuration
$parserSettings=parse_ini_file("parser.settings");
$masterLogPath=$parserSettings['masterLogPath'];
$injectLogPath=$parserSettings['injectLogPath'];

$logDatabase=$parserSettings['logDatabase'];
$logTableName=$parserSettings['logTableName'];

if ($argc != 2 || in_array($argv[1], array('--help', '-help', '-h', 
'-?'))) {
?>

This is a command line PHP script with one option.

  Usage:
  <?php echo $argv[0]; ?> <option>

	Current Commands:
  inject : Will inject the logs into mysql based
           on the settings in parser.settings

  clearTable ZzC13arzZ : Will clear the table 
           of all data, you must enter in the 
	   confirm code.

  createTable : Creates the table in SQL 
           
  With the --help, -help, -h,
  or -? options, you can get this help.

<?php
} else {
	switch ($argv[1])
	{
	case "inject":
		injectLogs();
		break;
	case "clearTable":
		injectLogs();
		break;
	case "createTable":
		injectLogs();
		break;
	default:
?>

This is a command line PHP script with one option.

  Usage:
  <?php echo $argv[0]; ?> <option>

	Current Commands:
  inject : Will inject the logs into mysql based
           on the settings in parser.settings

  With the --help, -help, -h,
  or -? options, you can get this help.

<?php
}

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
'".htmlspecialchars($value)."'\n";
			$errorCount++;
		}
	}
}
}

echo "\nInjection Counts\n";
echo "Log Lines: " . count($testArray)."\n";
echo "Injections: " . $injectCount."\n";
echo "Duplicates: " . $dupeCount."\n";
echo "Errors: " . $errorCount."\n";
if ($errorCount>0)
{
	echo "Errors Returned:\n";
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
			echo ("Error clearing $masterLogPath\n");
  		}
		else
  		{
  		echo ("Cleared Master Log\n");
  		}
  	}
}

fclose($file);

} // Inject Logs


?>
