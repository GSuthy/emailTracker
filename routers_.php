<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 1/13/14
 * Time: 3:30 PM
 */

ini_set('max_execution_time', 0);

$con = mysqli_connect("sienna.byu.edu", "oit#greplogadmin", "1t5T00lTime", "syslog");

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

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    $queue_id;

    $to = "parker@taco.byu.edu";
//    $to = "parker_bradshaw@byu.edu";
//    $to = "stevenc4@taco.byu.edu";
//    $from = "parker@taco.byu.edu";
    $from = "p-bradshaw";
    $beginDate = date('Y-m-d\TH:i:s', mktime(date('H'), date('i'), 0, date('m'), date('d') - 10, date('Y')));
    $endDate = date('Y-m-d\TH:i:s', mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y')));

    echo $beginDate . " " . $endDate . "<br/>";

//    $results = mysqli_query($con, "select * from systemevents where message like '%to=%" . $to . "%' and receivedat between '" . $beginDate . "' and '" . $endDate . "'");
    $results = mysqli_query($con, "select * from systemevents where message like '%from=%" . $from . "%' and receivedat between '" . $beginDate . "' and '" . $endDate . "' order by receivedat desc");


    foreach ($results as $result) {
        $parameters = preg_split("/[:,]?\s+/", $result['Message']);

        $queue_id = $parameters[1];
        echo "<b>Queue ID: $queue_id</b><br/>";

        $queue_id_array = array($queue_id);

        $index = 0;
        $continue = true;
        while ($index < count($queue_id_array)) {
            getFromID($queue_id_array[$index++]);
        }

        /*$results_2 = mysqli_query($con, "select * from systemevents where message like '%" . $queue_id . "%'");
        $to_dst = array();
        $to_array = array();
        $from_array = "";
        foreach ($results_2 as $result_2) {
            echo htmlspecialchars($result_2['Message']) . "<br>";
            $matches;

            preg_match("/to=<.+>,\sdelay/", $result_2['Message'], $matches);
            if (count($matches) > 0) {
                array_push($to_dst, substr($matches[0], 4, strlen($matches[0]) - 12));

                $secondary_id;
                preg_match("/stat=Sent\s\(.+\sMessage accepted/", $result_2['Message'], $secondary_id);
                if (count($secondary_id) > 0) {
                    array_push($queue_id_array, substr($secondary_id[0], 11, strlen($secondary_id[0]) - 28));
                }
            }

            preg_match("/to=[^<]+,\sdelay/", $result_2['Message'], $matches);
            if (count($matches) > 0) {
                array_push($to_array, substr($matches[0], 3, strlen($matches[0]) - 10));
                $secondary_id;
                preg_match("/stat=Sent(\s.+\sMessage accepted/", $result_2['Message'], $secondary_id);
                echo $result_2['Message'];
                if (count($secondary_id) > 0) {
                    echo "Success";
                }
            }

            preg_match("/from=<.+>,\ssize/", $result_2['Message'], $matches);
            if (count($matches) > 0) {
                $from_array = substr($matches[0], 6, strlen($matches[0]) - 13);
            }
        }
        echo "<br/>";
//        echo "To: <i>$to_dst</i><br/>";
        foreach ($to_dst as $toAddr) {
            if (!is_null($toAddr))
                echo "To: <i>$toAddr</i><br/>";
        }
        foreach ($to_array as $toAddr) {
            if (!is_null($toAddr))
            echo "To: <i>$toAddr</i><br/>";
        }
        echo "From: <i>$from_array</i><br/>";
        echo "<br/><br/>";
        echo "<pre>";
        print_r($queue_id_array);
        echo "</pre>";*/
    }



}

mysqli_close($con);