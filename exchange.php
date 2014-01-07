<?php

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

//TODO: sanitize variables/check for SQL injection
$sender = isset($input["sender"]) ? $input["sender"] : "%";
$recipient = isset($input["recipient"]) ? $input["recipient"] : "%";
$startDttm = date_create(isset($input["startDttm"]) ? $input["startDttm"] : null); //TODO: timezones?
$endDttm = date_create(isset($input["endDttm"]) ? $input["endDttm"] : null);

if($sender == "%" && $recipient == "%") {
	echo "Must specify a sender or recipient"; //TODO: better failure message
	return;
}

if(!$startDttm) {
	echo "Must specify startDttm"; //TODO: better failure message
	return;
}

if(!$endDttm) {
	$endDttm = date_create();
}

$con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "exchange");
if (mysqli_connect_errno())
{
	echo "Failed to connect to database: " . mysqli_connect_error(); //TODO: better failure message
	return;
}


$query = 
	"SELECT * " .
	"FROM logmain " .
	"LEFT JOIN messagerecipients " .
	"ON logmain.id=messagerecipients.log_id " .
	"WHERE " . 
	"(logmain.sender_address LIKE \"" . $sender . "\") " .
	"AND (messagerecipients.recipient_address LIKE \"" . $recipient . "\") " .
	"AND (logmain.date_time BETWEEN \"" . date_format($startDttm, "Y-m-d H:i:s") . "\" AND \"" . date_format($endDttm, "Y-m-d H:i:s") . "\") " .
	"LIMIT 100 ";
	
	//echo $query . "\n";

$result = mysqli_query($con, $query);

if(!$result) {
	echo "Query failed: " . mysqli_error($con); //TODO: better failure message
	return;
}

while($row = mysqli_fetch_array($result)) {
	echo json_encode($row) . "\n";
	continue;
	
	echo "\n";
}

mysqli_close($con);

?>