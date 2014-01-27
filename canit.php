<?php
	require_once("canit-api-client.php");
	require_once("settings.php");
	require_once("exchange.php");

//	$show_table = false;
	$search_params = array();
	$queue_id_clicked;
	$reporting_host_clicked;
	/*if(isset($_POST['searching'])){
		$show_table = true;
		$search_params = $_POST;
	} */
	
	//date_default_timezone_set(date_default_timezone_get());
	date_default_timezone_set('America/Denver');
/*?><!--



<?php*/

class CanitClient {


    private function canitError($errorMessage){
        unset($errorReturn);
        $errorReturn["error"] = $errorMessage;
        return $errorReturn;
    }

    public function getCanitResults ($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults){
        /*if (!is_null($sender)) {
            if ($sender_contains) {
                $sender = "%" . $sender . "%";
            }
        } else if ($sender === "") {
            $sender = null;
        }

        if (!is_null($recipient)) {
            if ($recipient_contains) {
                $recipient = "%" . $recipient . "%";
            }
        } else if ($recipient === "") {
            $recipient = null;
        }*/

        global $credentials;
        $canit_url = "https://gw3.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login($credentials['username'], $credentials['password']);
        $users = $api->do_get('realm/@@/users');
        $num_results = 2000;
        $search_string = 'log/search/0/'.$maxResults.'?sender='.$sender.'&recipients='.$recipient.'&subject='.$subject;

        if ($sender_contains)
        {
            $search_string = $search_string . '&rel_sender=contains';
        }
        if ($recipient_contains)
        {
            $search_string = $search_string . '&rel_recipients=contains';
        }
        if ($subject_contains)
        {
            $search_string = $search_string . '&rel_subject=contains';
        }

        echo $search_string . '<br>';
        $results = $api->do_get($search_string);
	    if (!$api->succeeded()) {
		    print "GET request failed: " . $api->get_last_error() . "\n";
	    } else {
//            echo "Yea canit made a call <br>";
           /* echo "<pre>";
            print_r ($results);
            echo "</pre>";*/
            return $results;
        }

    }







}


/*if ($show_table)
{


	
	$results = $api->do_get($search_string);
	if (!$api->succeeded()) {
		print "GET request failed: " . $api->get_last_error() . "\n";
	} else {
		$count = 1;
		$table_string = "<form method='POST'><table border='1'>";
		$table_string .= "<th></th><th>Incident ID</th><th>View Logs</th><th>Timestamp</th><th>Sender</th><th>Recipients</th><th>Subject</th><th>What</th><th>Score</th>";
		foreach ($results as $result)
		{
			$table_string .= "<tr>";
			$table_string .= "<td>$count</td>";
			
			$table_string .= "<td>";
			if ($result['incident_id'])
			{
				$table_string .= "$result[incident_id]"; 
			}
			$table_string .= "</td>";
		
			$table_string .= "<td><input type='submit' name='viewLogs' value='view'></td>";
			$table_string .= "<td>".date('m/d/Y -- h:i', $result['ts'])."</td>";
			$table_string .= "<td>$result[sender]</td>";
			
			$table_string .= "<td>";
			foreach ($result['recipients'] as $recipient)
			{
				$table_string .= "$recipient  ";
			}
			$table_string .= "</td>";
		
			$table_string .= "<td>";
			if ($result['incident_id']) {
				$table_string .= "<a href='https://emailfilter.byu.edu/canit/showincident.php?id=$result[incident_id]&s=$result[stream]&rlm=$result[realm]'>$result[subject]</a>";
			} else {
				$table_string .= "$result[subject]";
			}
			$table_string .= "</td>";
			$table_string .= "<td>$result[what]</td>";
			$table_string .= "<td>$result[score]</td>";
		
			$count++;
		}
		$table_string .= "</table></form>";
		
		$logs = $api->do_get('log/s08M9Mvt021490/gw10');
		
		if (!$api->succeeded()) {
			echo "GET request failed: " . $api->get_last_error() . "\n";
		} else {
			print "<br/>THIS LOG API CALL IS HARD-CODED<br/>";
			print "<pre>";
			print_r($logs);
			print "</pre>";
		}
		
		echo "<br/><br/>";
		echo $table_string;
	}
	
	$sender = $search_params['sender'];
	$sender_contains = ($search_params['senderSearchType'] == 'contains');
	
	$recipient = $search_params['recipient'];
	$recipient_contains = ($search_params['recipientSearchType'] == 'contains');
	
	$subject = $search_params['subject'];
	$subject_contains = ($search_params['subjectSearchType'] == 'contains');
	
	$startDttm = $search_params['start_date'];
	$endDttm = $search_params['end_date'];
	
	$exchangeResults = ExchangeClient::getExchangeResults($sender, $sender_contains, $recipient, $recipient_contains, $subject, $subject_contains, $startDttm, $endDttm, $num_results);
	if (isset($exchangeResults['error'])) {
		print "Exchange request failed: " . $exchangeResults['error'] . "\n";
	} else {
		$count = 1;
		$table_string = "<form method='POST'><table border='1'>";
		$table_string .= "<th></th><th>Event ID</th><th>Timestamp</th><th>Sender</th><th>Recipient</th><th>Subject</th><th>Client Hostname</th><th>Server Hostname</th>";
		foreach ($exchangeResults as $result)
		{
			$table_string .= "<tr>";
			$table_string .= "<td>$count</td>";
			
			$table_string .= "<td>";
			if ($result['event_id'])
			{
				$table_string .= "$result[event_id]"; 
			}
			$table_string .= "</td>";
		
			$table_string .= "<td>$result[date_time]</td>";
			$table_string .= "<td>$result[sender_address]</td>";
			
			$table_string .= "<td>$result[recipient_address]</td>";
		
			$table_string .= "<td>$result[message_subject]</td>";
			$table_string .= "<td>$result[client_hostname]</td>";
			$table_string .= "<td>$result[server_hostname]</td>";
		
			$count++;
		}
		$table_string .= "</table></form>";
		
		echo "<br/><br/>";
		echo $table_string;
	}
}*/

?>


