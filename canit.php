<?php
	require_once("canit-api-client.php");
	require_once("settings.php");
	require_once("exchange.php");

	$show_table = false;
	$search_params = array();
	$queue_id_clicked;
	$reporting_host_clicked;
	if(isset($_POST['submit'])){
		$show_table = true;
		$search_params = $_POST;
	} 
	
	//date_default_timezone_set(date_default_timezone_get());
	date_default_timezone_set('America/Denver');
?>

<!DOCTYPE html>
<html>
<body>
	<b>Search Parameters</b>
	<form method="POST" action="<?php $_SERVER['PHP_SELF']; ?>">
		<table>
			<tr>
				<td>Start Date: </td>
				<td><input type="datetime-local" id="start_date" name="start_date" value="<?php echo date('Y-m-d\TH:i', mktime(date('H'), date('i'), 0, date('m'), date('d')-1, date('Y'))); ?>"/></td>
			</tr>
			<tr>
				<td>End Date: </td>
				<td><input type="datetime-local" id="end_date" name="end_date" value="<?php echo date('Y-m-d\TH:i'); ?>"/></td>
			</tr>
			<tr>
				<td>Net ID:</td>
				<td><input type="text" id="net_id" name="net_id" value="<?php if ($search_params) echo $search_params['net_id']; ?>"/></td>
			</tr>
			<tr>
				<td>Sender:</td>
				<td><input type="text" id="sender" name="sender" value="<?php if ($search_params) echo $search_params['sender']; ?>"/></td>
				<td>
					<select name="senderSearchType">
						<option name="senderContains">contains</option>
						<option name="senderIs" <?php if ($search_params && $search_params['senderSearchType'] == 'equals') echo "selected"; ?>>equals</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Recipient: </td>
				<td><input type="text" id="recipient" name="recipient" value="<?php if ($search_params) echo $search_params['recipient']; ?>"/>
				<td>
					<select name="recipientSearchType">
						<option name="recipientContains">contains</option>
						<option name="recipientIs" <?php if ($search_params && $search_params['recipientSearchType'] == 'equals') echo "selected"; ?>>equals</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Subject:</td>
				<td><input type="text" name="subject" value="<?php if ($search_params) echo $search_params['subject']; ?>"/></td>
				<td>
					<select name="subjectSearchType">
						<option name="subjectContains">contains</option>
						<option name="subjectIs" <?php if ($search_params && $search_params['subjectSearchType'] == 'equals') echo "selected"; ?>>equals</option>
					</select>
				</td>
			</tr>
		</table>
		<input type="submit" name="submit" value="Search"/>
	</form>
	<br/>
</body>
</html>

<?php
$canit_url = "https://emailfilter.byu.edu/canit/api/2.0";

$options = array(
	CURLOPT_SSL_VERIFYPEER => false
);

$api = new CanItAPIClient($canit_url);


$success = $api->login($credentials['username'], $credentials['password']);

$users = $api->do_get('realm/@@/users');

$num_results = 2000;

if ($show_table)
{
	$search_string = 'log/search/0/'.$num_results.'?stream='.$search_params['net_id'].'&sender='.$search_params['sender'].'&recipients='.$search_params['recipient'].'&subject='.$search_params['subject'];
	
	if ($search_params['senderSearchType'] == 'contains')
	{
		$search_string = $search_string . '&rel_sender=contains';
	}
	if ($search_params['recipientSearchType'] == 'contains')
	{
		$search_string = $search_string . '&rel_recipients=contains';
	}
	if ($search_params['subjectSearchType'] == 'contains')
	{
		$search_string = $search_string . '&rel_subject=contains';
	}
	
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
	

	//UNCOMMENT THE FOLLOWING TO ENABLE EXCHANGE SEARCH
	//it just spits out the data, I haven't formatted it into a table.
	//also, it runs really slow and I don't know why--running the queries in MySQL Workbench goes really fast
	/*
	$sender = $search_params['sender'];
	$sender_contains = ($search_params['senderSearchType'] == 'contains');
	
	$recipient = $search_params['recipient'];
	$recipient_contains = ($search_params['recipientSearchType'] == 'contains');
	
	$subject = $search_params['subject'];
	$subject_contains = ($search_params['subjectSearchType'] == 'contains');
	
	$startDttm = $search_params['start_date'];
	$endDttm = $search_params['end_date'];
	
	$exchangeResults = ExchangeClient::getExchangeResults($sender, $sender_contains, $recipient, $recipient_contains, $subject, $subject_contains, $startDttm, $endDttm);
	echo(json_encode($exchangeResults));
	*/
}

?>


