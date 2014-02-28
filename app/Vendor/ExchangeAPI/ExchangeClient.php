<?php

class ExchangeClient {

	private static function exchangeError($errorMessage) {
		unset($errorReturn);
		$errorReturn["error"] = $errorMessage;
		return $errorReturn;
	}

	public static function getExchangeResults($sender, $sender_contains, $recipient, $recipient_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults) {	
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
			$subject = "%" . $subject . "%";
		}
		
		$startDttm = date_create($startDttm);
		$endDttm = date_create($endDttm);

		if(!$startDttm) {
			return ExchangeClient::exchangeError("Must specify startDttm"); //TODO: better failure message;
		}

		if(!$endDttm) {
			$endDttm = date_create();
		}
		
		if(is_null($maxResults) || !is_numeric($maxResults)) {
			$maxResults = 20;
		}
		
		$query = "SELECT MIN(logmain.date_time) as date_time, logmain.sender_address, logmain.message_subject, logmain.message_id, messagerecipients.recipient_address \n";
		$query .= "FROM logmain INNER JOIN messagerecipients ON logmain.id=messagerecipients.log_id \n";
		$query .= "WHERE (logmain.date_time BETWEEN \"" . date_format($startDttm, "Y-m-d H:i:s") . "\" AND \"" . date_format($endDttm, "Y-m-d H:i:s") . "\") \n";
		if(!is_null($sender)) {
			$query .= "AND (logmain.sender_address LIKE \"" . $sender . "\") \n";
		}
		if(!is_null($recipient)) {
			$query .= "AND (messagerecipients.recipient_address LIKE \"" . $recipient . "\") \n";
		}
		if(!is_null($subject)) {
			$query .= "AND (logmain.message_subject LIKE \"" . $subject . "\") \n";
		}
                $query .= "AND !(messagerecipients.recipient_address LIKE \"%@ad.byu.edu\") \n";
		
		$query .= "GROUP BY logmain.message_id \n";
		$query .= "ORDER BY date_time \n";
                $query .= "LIMIT " . $maxResults;
		
                //echo $query . "<br>";
					
		$con = mysqli_connect("sienna.byu.edu", "oit#greplog", "HiddyH0Neighbor", "exchange", "3306");
		if (mysqli_connect_errno())
		{
			return ExchangeClient::exchangeError("Failed to connect to database: " . mysqli_connect_error()); //TODO: better failure message
		}
		
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
	
	public static function getAdditionalLogs($message_id, $maxResults) {	
		if(is_null($message_id)) {
			return ExchangeClient::exchangeError("Must specify message_id"); //TODO: better failure message;
		}
		
		if(is_null($maxResults) || !is_numeric($maxResults)) {
			$maxResults = 20;
		}              
		
		$query = "SELECT logmain.date_time, logmain.client_hostname, logmain.server_hostname, logmain.event_id, logmain.sender_address, logmain.message_subject, logmain.message_id, messagerecipients.recipient_address \n";
		$query .= "FROM logmain INNER JOIN messagerecipients ON logmain.id = messagerecipients.log_id \n";
                $query .= "WHERE \"" . $message_id . "\" = logmain.message_id \n";
                $query .= "ORDER BY date_time \n";
                $query .= "LIMIT " . $maxResults;
                
                //return ExchangeClient::exchangeError("Query: " . $query);
					
		$con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "exchange");
		if (mysqli_connect_errno())
		{
			return ExchangeClient::exchangeError("Failed to connect to database: " . mysqli_connect_error()); //TODO: better failure message
		}
		
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