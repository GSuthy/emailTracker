<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 1/13/14
 * Time: 3:30 PM
 */

ini_set('max_execution_time', 0);

$con = mysqli_connect("sienna.byu.edu", "oit#greplogadmin", "1t5T00lTime", "syslog");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    $queue_id;

    $to = "parker@taco.byu.edu";
    $from = "";

    $results = mysqli_query($con, "select * from systemevents where message like '%to=<" . $to . ">%'");

    foreach ($results as $result) {
        $parameters = preg_split("/[:,]?\s+/", $result['Message']);

        $queue_id = $parameters[1];
        echo "<b>Queue ID: $queue_id</b><br/>";

        $queue_id_array = array($queue_id);
//        print_r($queue_id_array);

        $results_2 = mysqli_query($con, "select * from systemevents where message like '%" . $queue_id . "%'");
        $to_dst = "";
        $to_array = array();
        $from_array = "";
        foreach ($results_2 as $result_2) {
            echo htmlspecialchars($result_2['Message']) . "<br>";
            $matches;
            preg_match("/to=<.+>,\sdelay/", $result_2['Message'], $matches);
            if (count($matches) > 0) {
                $to_dst = substr($matches[0], 4, strlen($matches[0]) - 12);
            }
            preg_match("/to=[^<]+,\sdelay/", $result_2['Message'], $matches);
            if (count($matches) > 0) {
                array_push($to_array, substr($matches[0], 3, strlen($matches[0]) - 14));
            }
            preg_match("/from=<.+>,\ssize/", $result_2['Message'], $matches);
            if (count($matches) > 0) {
                $from_array = substr($matches[0], 6, strlen($matches[0]) - 13);
            }
        }
        echo "<br/>";
        echo "To: <i>$to_dst</i><br/>";
        foreach ($to_array as $toAddr) {
            if (!is_null($toAddr))
            echo "To: <i>$toAddr</i><br/>";
        }
        echo "From: <i>$from_array</i><br/>";
        echo "<br/><br/>";
    }
}

mysqli_close($con);