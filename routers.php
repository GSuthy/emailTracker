<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 1/21/14
 * Time: 11:06 AM
 */

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
            $query .= "(Message LIKE \"%from=<" . $sender . ">%\" OR Message LIKE \"%from=" . $sender . ", size%\") ";
        } else if (is_null($sender) && !is_null($recipient)) {
            $query .=
                "(Message LIKE \"%to=" . $recipient . ",%\" OR Message LIKE \"%to=<" . $recipient . ">%\") ";
        } else {
            $query .= "(Message LIKE \"%to=" . $recipient . ",%\" OR Message LIKE \"%to=<" . $recipient . ">%\") ";
            $to_and_from = true;
        }
        $query .= "AND ReceivedAt >= '" . $startDttm . "' AND ReceivedAt <= '" . $endDttm . "' ORDER by ReceivedAt DESC";


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
                $this->getFromID($con, $queue_id_array[$index++], $queue_id_array, $log_lines);
            }

            $message_from = preg_split("/from=[<]?|[>]?, size/", $log_lines[0]['Message']);
            $temp_sender = $message_from[1];

            $message_to = preg_split("/to=[<]?|[>]?, delay/", $log_lines[count($log_lines) - 1]['Message']);
            $temp_recipient = $message_to[1];

            $message_dsn = preg_split("/dsn=|, stat=/", $log_lines[count($log_lines) - 1]['Message']);
            $temp_dsn = $message_dsn[1];

            $temp_status = $temp_dsn;

            $temp_array['Date'] = $date;
            $temp_array['Time'] = $time;
            $temp_array['Sender'] = $temp_sender;
            $temp_array['Recipient'] = $temp_recipient;
            $temp_array['Subject'] = "";
            $temp_array['Status'] = $temp_status;
            $temp_array['Loglines'] = $log_lines;

            $push_results = false;
            if ($to_and_from) {
                if ($sender_contains) {
                    $sender_regex = str_replace("%", ".*", $sender);
                    if (preg_match("/" . $sender_regex . "/", $temp_sender)) {
                        $push_results = true;
                    }
                } else {
                    if ($sender === $temp_sender)
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

        return $return_array;
    }
}