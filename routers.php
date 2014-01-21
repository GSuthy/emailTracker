<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 1/21/14
 * Time: 11:06 AM
 */

function getFromID($id_to_search) {
    $search_results = mysqli_query($GLOBALS['con'], "select * from systemevents where message like ' " . $id_to_search . "%'");
    foreach ($search_results as $result) {
        $secondary_id = array();
        preg_match("/stat=Sent\s\(.+\sMessage accepted/", $result['Message'], $secondary_id);
        echo "<pre>";
        echo htmlspecialchars($result['Message']);
        echo "</pre>";
        if (count($secondary_id) > 0) {
            $id_to_add = substr($secondary_id[0], 11, strlen($secondary_id[0]) - 28);
            if (in_array($id_to_add, $GLOBALS['queue_id_array']) != true) {
                array_push($GLOBALS['queue_id_array'], $id_to_add);
            }
        }
    }
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

//TODO: sanitize variables/check for SQL injection
$sender = $input["sender"];
$recipient = $input["recipient"];
$startDate = $input["startDate"];
$endDate = $input["endDate"];

$to_and_from = false;
if (!is_null($sender) && is_null($recipient)) {
    $query =
        "SELECT * " .
        "FROM systemevents " .
        "WHERE " .
        "Message LIKE \"%from=<" . $sender . ">%\" " .
        "AND ReceivedAt BETWEEN \"" . $startDate . "\" AND \"" . $endDate . "\" " .
        "ORDER by ReceivedAt DESC";
} else if (is_null($sender) && !$is_null($recipient)) {
    $query =
        "SELECT * " .
        "FROM systemevents " .
        "WHERE " .
        "Message LIKE \"%to=%" . $to . "%" .
        "AND ReceivedAt BETWEEN \"" . $startDate . "\" AND \"" . $endDate . "\" " .
        "ORDER by ReceivedAt DESC";
} else {
    $query =
        "SELECT * " .
        "FROM systemevents " .
        "WHERE " .
        "Message LIKE \"%to=%" . $to . "%" .
        "AND ReceivedAt BETWEEN \"" . $startDate . "\" AND \"" . $endDate . "\" " .
        "ORDER by ReceivedAt DESC";
    $to_and_from = true;
}


$con = mysqli_connect("Sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "syslog");
if (mysqli_connect_errno())
{
    echo "Failed to connect to database: " . mysqli_connect_error(); //TODO: better failure message
    return;
}

$result = mysqli_query($con, $query);

if(!$result) {
    echo "Query failed: " . mysqli_error($con); //TODO: better failure message
    return;
}

foreach ($results as $result) {
    $message = preg_split("/[:,]?\s+/", $result['Message']);
    $queue_id = $parameters[1];
    $queue_id_array = array($queue_id);

    $index = 0;
    while ($index < count($queue_id_array)) {
        getFromID($queue_id_array[$index++]);
    }
}

mysqli_close($con);