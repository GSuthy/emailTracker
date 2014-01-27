<?php

class ExchangeClient {

	private static function exchangeError($errorMessage) {
		unset($errorReturn);
		$errorReturn["error"] = $errorMessage;
		return $errorReturn;
	}

	public static function getExchangeResults($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults) {
		if(empty($sender)) {
			$sender = null;
		} else if($sender_contains) {
			$sender = "%" . $sender . "%";
		}
		
		if(empty($recipient)) {
			$recipient = null;
		} else if($recipient_contains) {
			$recipient = "%" . $recipient . "%";
		}
		
		if(empty($subject)) {
			$subject = null;
		} else if($subject_contains) {
			$subject = "%" . $recipient . "%";
		}
		
		$startDttm = date_create($startDttm); //TODO: timezones?
		$endDttm = date_create($endDttm);

		if(!$startDttm) {
			return ExchangeClient::exchangeError("Must specify startDttm"); //TODO: better failure message;
		}

		if(!$endDttm) {
			$endDttm = date_create();
		}
		
		$query = "SELECT logmain.date_time, logmain.client_hostname, logmain.server_hostname, logmain.event_id, logmain.sender_address, logmain.message_subject, logmain.internal_message_id, messagerecipients.recipient_address ";
		$query .= "FROM logmain ";
		$query .= "INNER JOIN messagerecipients ON logmain.id=messagerecipients.log_id ";
		$query .= "WHERE (logmain.date_time BETWEEN \"" . date_format($startDttm, "Y-m-d H:i:s") . "\" AND \"" . date_format($endDttm, "Y-m-d H:i:s") . "\") ";
		if(!is_null($sender)) {
			$query .= "AND (logmain.sender_address LIKE \"" . $sender . "\") ";
		}
		if(!is_null($recipient)) {
			$query .= "AND (messagerecipients.recipient_address LIKE \"" . $recipient . "\") ";
		}
		if(!is_null($subject)) {
			$query .= "AND (logmain.message_subject LIKE \"" . $subject . "\") ";
		}
		
		$query .= "ORDER BY logmain.internal_message_id, logmain.date_time, logmain.id ";
		$query .= "LIMIT " . $maxResults;
		
		echo $query . "<br>";
		
		set_time_limit(60); //TODO: FIND ANOTHER SOLUTION TO THE TIMEOUT ISSUE
			
		$con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "exchange");
		if (mysqli_connect_errno())
		{
			return ExchangeClient::exchangeError("Failed to connect to database: " . mysqli_connect_error()); //TODO: better failure message
		}
		
		echo "connected " . date("Y-m-d H:i:s") . "<br>";

		$result = mysqli_query($con, $query);
		
		echo "finished " . date("Y-m-d H:i:s") . "<br>";

		if(!$result) {
			return ExchangeClient::exchangeError("Query failed: " . mysqli_error($con)); //TODO: better failure message;
		}

		$rowNum = 0;
		$returnValue = array();

		while($row = mysqli_fetch_array($result)) {			
			$returnValue[$rowNum] = $row;
			$rowNum++;
		}
		mysqli_close($con);
		echo date("Y-m-d H:i:s") . "\n";
		
		return $returnValue;
	}
}

?>