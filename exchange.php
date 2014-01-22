<?php

class ExchangeClient {

	private static function exchangeError($errorMessage) {
		unset($errorReturn);
		$errorReturn["error"] = $errorMessage;
		return $errorReturn;
	}

	public static function getExchangeResults($sender, $sender_contains, $recipient, $recipient_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults) {		
		if(!is_null($sender)) {
			if($sender_contains) {
				$sender = "%" . $sender . "%";
			}
		} else {
			$sender = "%";
		}
		
		if(!is_null($recipient)) {
			if($recipient_contains) {
				$recipient = "%" . $recipient . "%";
			}
		} else {
			$recipient = "%";
		}
		
		if(!is_null($subject)) {
			if($subject_contains) {
				$subject = "%" . $subject . "%";
			}
		} else {
			$subject = "%";
		}
		
		$startDttm = date_create($startDttm); //TODO: timezones?
		$endDttm = date_create($endDttm);

		if(!$startDttm) {
			return ExchangeClient::exchangeError("Must specify startDttm"); //TODO: better failure message;
		}

		if(!$endDttm) {
			$endDttm = date_create();
		}

		$con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "exchange");
		if (mysqli_connect_errno())
		{
			return ExchangeClient::exchangeError("Failed to connect to database: " . mysqli_connect_error()); //TODO: better failure message
		}

		$query = 
			"SELECT * " .
			"FROM logmain " .
			"LEFT JOIN messagerecipients " .
			"ON logmain.id=messagerecipients.log_id " .
			"WHERE " . 
			"(logmain.sender_address LIKE \"" . $sender . "\") " .
			"AND (logmain.message_subject LIKE \"" . $subject . "\") " .
			"AND (messagerecipients.recipient_address LIKE \"" . $recipient . "\") " .
			"AND (logmain.date_time BETWEEN \"" . date_format($startDttm, "Y-m-d H:i:s") . "\" AND \"" . date_format($endDttm, "Y-m-d H:i:s") . "\") " .
			"ORDER BY logmain.date_time " .
			"LIMIT " . $maxResults;

		$result = mysqli_query($con, $query);

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
		
		return $returnValue;
	}
}

?>