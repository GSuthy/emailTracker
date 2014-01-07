<?php

function returnError($errorMessage) {
	unset($errorReturn);
	$errorReturn["error"] = $errorMessage;
	echo json_encode($errorReturn);
	return;
}


$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

//TODO: sanitize variables/check for SQL injection
$sender = isset($input["sender"]) ? $input["sender"] : "%";
$recipient = isset($input["recipient"]) ? $input["recipient"] : "%";
$startDttm = date_create(isset($input["startDttm"]) ? $input["startDttm"] : "notADate"); //TODO: timezones?
$endDttm = date_create(isset($input["endDttm"]) ? $input["endDttm"] : "notADate");

if($sender == "%" && $recipient == "%") {
	returnError("Must specify a sender or recipient"); //TODO: better failure message
	return;
}

if(!$startDttm) {
	returnError("Must specify startDttm"); //TODO: better failure message
	return;
}

if(!$endDttm) {
	$endDttm = date_create();
}

$con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "exchange");
if (mysqli_connect_errno())
{
	returnError("Failed to connect to database: " . mysqli_connect_error()); //TODO: better failure message
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
	"ORDER BY logmain.date_time " .
	"LIMIT 100 ";
	
	//echo $query . "\n";

$result = mysqli_query($con, $query);

if(!$result) {
	returnError("Query failed: " . mysqli_error($con)); //TODO: better failure message
	return;
}

$rowNum = 0;

while($row = mysqli_fetch_array($result)) {
	//this removes unnecessary fields from the return JSON
	//TODO: "12" is the number of fields in logmain + the number of fields in messagerecipients: we should probably find a way to calculate this instead of using a magic number
	unset($row["id"]);
	for($i = "0"; $i < "12"; $i++) {
		unset($row[$i]);
	}
	
	$returnValue[$rowNum] = $row;
	$rowNum++;
}

echo json_encode($returnValue);

mysqli_close($con);

?>