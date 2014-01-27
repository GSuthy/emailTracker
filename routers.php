<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 1/21/14
 * Time: 11:06 AM
 */

ini_set('max_execution_time', 300);

class RouterClient {

    private function routerError($errorMessage) {
        unset($errorReturn);
        $errorReturn["error"] = $errorMessage;
        return $errorReturn;
    }

    private function getFromID($con, $id_to_search, &$queue_id_array, &$log_lines) {
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

    public function getRouterResults($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults) {
        if (!is_null($sender)) {
            if ($sender === "") {
                $sender = null;
            } else if ($sender_contains) {
                $sender = "%" . $sender . "%";
            }
        }

        if (!is_null($recipient)) {
            if ($recipient === "") {
                $recipient = null;
            } else if ($recipient_contains) {
                $recipient = "%" . $recipient . "%";
            }
        }

        if (!$startDttm) {
            return RouterClient::routerError("Mus specify a startDttm"); //TODO: better fail message
        }

        if (!$endDttm) {
//            $endDttm = date('Y-m-d\TH:i', mktime(date('H'), date('i'), 0, date('m'), date('d')-1, date('Y')));
//            $endDttm = date('m') . "/" . date('d') . "/" . date('Y');
            $endDttm = date('Y') . "-" . date('m') . "-" . date('d') . "T" . date('H') . ":" . date('m') . ":" . date('i') . "000";
        }


        $to_and_from = false;
        $query = "SELECT * FROM systemevents WHERE ";
        if (!is_null($sender) && is_null($recipient)) {
            $query .= "(Message LIKE \"%from=<" . $sender . ">, size%\" OR Message LIKE \"%from=" . $sender . ", size%\") ";
        } else if (is_null($sender) && !is_null($recipient)) {
            $query .= "(Message LIKE \"%to=" . $recipient . ",%delay%\" OR Message LIKE \"%to=<" . $recipient . ">,%delay%\") ";
        } else {
            $query .= "(Message LIKE \"%to=" . $recipient . ",%delay%\" OR Message LIKE \"%to=<" . $recipient . ">,%delay%\") ";
            $to_and_from = true;
        }
        $query .= "AND ReceivedAt >= '" . $startDttm . "' AND ReceivedAt <= '" . $endDttm . "' ORDER by ReceivedAt DESC";

//        echo $query . "<br/>";

        $con = mysqli_connect("sienna.byu.edu:3306", "oit#greplog", "HiddyH0Neighbor", "syslog");
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

            /*echo "<br/>";
            foreach ($log_lines as $line) {
                echo htmlspecialchars($line['Message']) . "<br/>";
            }
            echo "<br/>";*/

            $message_from = preg_split("/.*from=[<]?|[>]?, size.*/", $log_lines[0]['Message']);
            $temp_sender = $message_from[1];

            /*echo "Sender: " . $temp_sender . "<br/>";

            echo htmlspecialchars($log_lines[0]['Message']) . "<br/>";*/

            $message_to = preg_split("/.*to=[<]?|>,<|[>]?, delay.*/", $log_lines[count($log_lines) - 1]['Message']);
            $temp_recipients = Array();
            foreach ($message_to as $temp_recip) {
                if ($temp_recip != "") {
//                    echo "Recipient: " . $temp_recip . "<br/>";
                    array_push($temp_recipients, $temp_recip);
                }
            }

//            echo htmlspecialchars($log_lines[count($log_lines) - 1]['Message']) . "<br/>";

            $message_dsn = preg_split("/dsn=|, stat=/", $log_lines[count($log_lines) - 1]['Message']);
            $temp_dsn = $message_dsn[1];

            $temp_status = $temp_dsn;

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