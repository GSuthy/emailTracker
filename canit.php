<?php
session_start();

require_once("canit-api-client.php");
require_once("settings.php");

$show_table = false;
$show_logs = false;
$search_params = array();
$id_host = array();
$queue_id_clicked;
$reporting_host_clicked;

if ($_POST) {
    print "<pre>";
    print_r($_POST);
    print "</pre>";
}

if(isset($_POST['submit'])){
    $show_table = true;
    $search_params = $_POST;
} else if (isset($_POST['viewLogs'])) {
    $show_logs = true;
    $show_table = true;
    $id_host = $_POST;
    $search_params = $_SESSION['table'];
}

date_default_timezone_set('America/Denver');
?>

<!DOCTYPE html>
<html>
<body>
<b>Search Parameters</b>
<form method="POST">
    <table>
        <tr>
            <td>Start Date: </td>
            <td><input type="datetime-local" id="start_date" name="start_date" value="<?php echo date('Y-m-d\TH:i', mktime(date('H'), date('i'), 0, date('m'), date('d')-1, date('Y'))); ?>"/></td>
        </tr>
        <!--TODO: Make the date time persist after doing POST-->
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

if ($show_logs) {
    echo "<br/><br/>SHOWING LOGS<br/>";
    $logs = $api->do_get('log/'.$id_host['queue_id'].'/'.$id_host['reporting_host']);

    if (!$api->succeeded()) {
        echo "GET request failed: " . $api->get_last_error() . "\n";
    } else {
        echo "<br/>LOG API CALL<br/>";
        echo "$id_host[queue_id] $id_host[reporting_host]";
        echo "<pre>";
        //print_r($logs[0]['loglines']);
        foreach ($logs[0]['loglines'] as $log) {
            echo $log . "<br/>";
        }
        echo "</pre>";
    }
}

function aasort(&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

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
        $table_string = "<table border='1'>";
        $table_string .= "<th></th><th>Incident ID</th><th>View Logs</th><th>Timestamp</th><th>Sender</th><th>Recipients</th><th>Subject</th><th>What</th><th>Score</th>";

        //usort($results, function ($a, $b) { return $a['ts'] - $b['ts']; });
        aasort($results, "ts");

        foreach ($results as $result)
        {
            $start_date = $search_params['start_date'];
            $end_date = $search_params['end_date'];
            $result_date = $result['ts'];
            $result_date = date('Y-m-d\TH:i', $result_date);

            if ($start_date <= $result_date && $result_date <= $end_date) {
                $table_string .= "<tr>";
                $table_string .= "<td>$count</td>";

                $table_string .= "<td>";
                if ($result['incident_id'])
                {
                    $table_string .= "$result[incident_id]";
                }
                $table_string .= "</td>";

                $table_string .= "<form method='POST'>";
                $table_string .= "<td>";
                $table_string .= "<input type='text' name='queue_id' value='$result[queue_id]' hidden='true'>";
                $table_string .= "<input type='text' name='reporting_host' value='$result[reporting_host]' hidden='true'>";
                $table_string .= "<input type='submit' name='viewLogs' value='view'>";
                $table_string .= "</td>";
                $table_string .= "</form>";

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
        }
        $table_string .= "</table></form>";

        echo "<br/><br/>";
        echo $table_string;
    }
    $_SESSION['table'] = $search_params;
}

?>


