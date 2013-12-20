<?php

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

//TODO: sanitize variables/check for SQL injection
$sender = $input["sender"];
$recipient = $input["recipient"];
$startDttm = date_create($input["startDttm"]); //TODO: timezones?
$endDttm = date_create($input["endDttm"]);

if(!$sender && !$recipient) {
	echo "Must specify a sender or recipient"; //TODO: better failure message
}

if(!$startDttm) {
	echo "Must specify startDttm"; //TODO: better failure message
}

if(!$endDttm) {
	$endDttm = date_create();
}

$con = mysqli_connect("Sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "syslog");
if (mysqli_connect_errno())
{
	echo "Failed to connect to database: " . mysqli_connect_error(); //TODO: better failure message
	return;
}


$query = 
	"SELECT * " .
	"FROM systemevents " .
	"WHERE " .
	"Message LIKE \"%" . $sender . "%\" " . 
	"AND ReceivedAt BETWEEN \"" . date_format($startDttm, "Y-m-d H:i:s") . "\" AND \"" . date_format($endDttm, "Y-m-d H:i:s") . "\" ";
	
	//echo $query . "\n";

$result = mysqli_query($con, $query);

if(!$result) {
	echo "Query failed: " . mysqli_error($con); //TODO: better failure message
	return;
}

while($row = mysqli_fetch_array($result)) {

	echo $row["Message"] . "\n";
	//echo json_encode($row) . "\n";
	continue;

	$message = $row["Message"];
	$message = ltrim($message);	
	
	$uniqueID = substr($message, 0, 14);
	if($uniqueID[4] == "-")
	{
		continue;
	}
	
	$linkQuery = "SELECT * " . 
				"FROM systemevents " . 
				"WHERE " . 
				"Message LIKE \" " . $uniqueID . "%\" " .
				"OR Message LIKE \"%: " . $uniqueID . "%\" ";
				
	$linkResult = mysqli_query($con, $linkQuery);
	
	while($linkRow = mysqli_fetch_array($linkResult))
	{
		$linkMessage = $linkRow["Message"];
		echo $linkMessage;
		
		echo "\n";
	}
	
	//echo $message;
	
	echo "\n";
}

mysqli_close($con);

?>