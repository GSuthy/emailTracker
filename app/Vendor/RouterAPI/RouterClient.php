<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 1/21/14
 * Time: 11:06 AM
 */

ini_set('max_execution_time', 300);

class RouterClient {

    //TODO: When querying "SELECT * FROM syslog.systemevents WHERE Message LIKE " s0%" AND Message NOT LIKE "%to=%" AND Message NOT LIKE "%from=%" ORDER by ReceivedAt DESC;" we get results that don't follow the to-from standard

    private static function routerError($errorMessage) {
        unset($errorReturn);
        $errorReturn["error"] = $errorMessage;
        return $errorReturn;
    }

    private static function getFromID($con, $id_to_search, &$queue_id_array, &$log_lines) {
        $search_results = mysqli_query($con, "select * from systemevents where message like ' " . $id_to_search . "%'");
        foreach ($search_results as $result) {
            $secondary_id = array();
            preg_match("/stat=Sent\s\(.+\sMessage accepted/", $result['Message'], $secondary_id);

            array_push($log_lines, $result);
            if (count($secondary_id) > 0) {
                $id_to_add = substr($secondary_id[0], 11, strlen($secondary_id[0]) - 28);
                if (in_array($id_to_add, $queue_id_array) != true) {
                    array_push($queue_id_array, $id_to_add);
                }
            }
        }
    }

    public static function getRouterResults($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults) {
        if (!is_null($sender)) {
            if (empty($sender)) {
                $sender = null;
            } else if ($sender_contains) {
                $sender = "%" . $sender . "%";
            }
        }

        if (!is_null($recipient)) {
            if (empty($recipient)) {
                $recipient = null;
            } else if ($recipient_contains) {
                $recipient = "%" . $recipient . "%";
            }
        }

        if (!$startDttm) {
            return RouterClient::routerError("Must specify a start date"); //TODO: better fail message
        }

        if (!$endDttm) {
            $endDttm = date('Y') . "-" . date('m') . "-" . date('d') . "T" . date('H') . ":" . date('m') . ":" . date('i') . "000";
        }


        $to_and_from = false;
        $query = "SELECT * FROM systemevents WHERE ";
        if (!is_null($sender) && is_null($recipient)) {
            $query .= "(Message LIKE \"%from=<" . $sender . ">, size%\" OR Message LIKE \"%from=" . $sender . ", size%\") ";
        } else if (is_null($sender) && !is_null($recipient)) {
            $query .= "(Message LIKE \"%to=" . $recipient . ",%delay%\" OR Message LIKE \"%to=<" . $recipient . ">,%delay%\") ";
        } else if (!is_null($sender) && !is_null($recipient)) {
            $query .= "(Message LIKE \"%to=" . $recipient . ",%delay%\" OR Message LIKE \"%to=<" . $recipient . ">,%delay%\") ";
            $to_and_from = true;
        } else {
            $query .= "(Message LIKE \"%to=%\" OR Message LIKE \"%from=%\") ";
        }
        $query .= "AND ReceivedAt >= '" . $startDttm . "' AND ReceivedAt <= '" . $endDttm . "' ORDER by ReceivedAt DESC LIMIT " . $maxResults;

//        $con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "syslog");
        $con = mysqli_connect("sienna.byu.edu", "oit#greplog", "HiddyH0Neighbor", "syslog", "3306");
        if (mysqli_connect_errno())
        {
            return RouterClient::routerError("Failed to connect to database: " . mysqli_connect_error()); //TODO: better fail message
        }

        $results = mysqli_query($con, $query);

        if(!$results) {
            return RouterClient::routerError("Query failed: " . mysqli_error($con)); //TODO: better failure message;
        }

        $return_array = array();
        foreach ($results as $result) {
            $temp_array = array();

            $timestamp = $result['ReceivedAt'];

            $date = substr($timestamp, 5, 2) . "/" .
                substr($timestamp, 8, 2) . "/" .
                substr($timestamp, 0, 4);

            $time = substr($timestamp, 11, 5);


            $message_split = preg_split("/[:,]?\s+/", $result['Message']);
            $queue_id = $message_split[1];
            $queue_id_array = array($queue_id);

            $index = 0;
            $log_lines = array();
            while ($index < count($queue_id_array)) {
                RouterClient::getFromID($con, $queue_id_array[$index++], $queue_id_array, $log_lines);
            }


            $temp_sender = "";
            foreach($log_lines as $line) {
                if (preg_match("/(.*from=[<]?)|([>]?,\s.*)/", $line['Message'])) {
                    $message_from = preg_split("/(.*from=[<]?)|([>]?,\s.*)/", $line['Message']);
                    $temp_sender = $message_from[1];
                    break;
                } else {
                    echo htmlspecialchars($line['Message']) . "<br/>";
                }
            }

            $temp_recipients = Array();

            if (preg_match("/(.*to=[<]?)|(>,<)|([>]?,\s.*)/", $log_lines[count($log_lines) - 1]['Message'])) {
                $message_to = preg_split("/(.*to=[<]?)|(>,<)|([>]?,\s.*)/", $log_lines[count($log_lines) - 1]['Message']);
                foreach ($message_to as $temp_recip) {
                    if ($temp_recip != "") {
                        array_push($temp_recipients, $temp_recip);
                    } /*else {
                        array_push($temp_recipients, "");*/
                    }
                }
            }

//            echo htmlspecialchars($log_lines[count($log_lines) - 1]['Message']) . "<br/>";

            $message_dsn = preg_split("/dsn=|, stat=/", $log_lines[count($log_lines) - 1]['Message']);
            $temp_dsn = $message_dsn[1];

            $temp_status = ($temp_dsn === "2.0.0" ? "Sent" : "Error: check logs");

            $temp_array['Date'] = $date;
            $temp_array['Time'] = $time;
            $temp_array['Sender'] = $temp_sender;
            $temp_array['Recipients'] = $temp_recipients;
            $temp_array['Subject'] = "";
            $temp_array['Status'] = $temp_status;
            $temp_array['Loglines'] = $log_lines;

            $push_results = false;
            if ($to_and_from) {
                if ($sender_contains) {
                    $sender_regex = str_replace("%", ".*", $sender);
                    if (preg_match("/" . $sender_regex . "/", strtolower($temp_sender))) {
                        $push_results = true;
                    }
                } else {
                    if (strtolower($sender) === strtolower($temp_sender))
                    {
                        $push_results = true;
                    }
                }
            } else {
                $push_results = true;
            }

            if ($push_results) {
                array_push($return_array, $temp_array);
            }
        }
        mysqli_close($con);

        /*echo "<pre>";
        print_r($return_array);
        echo "</pre>";*/

        return $return_array;
    }
}