<?php
App::uses('AppModel', 'Model');

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'routers';
    public $useTable = 'systemevents';
	public $primaryKey = 'ID';

    public function getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults) {
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

        $start_date = date_create_from_format('m/d/Y H:i:s', $startDttm . " 00:00:00");
        $end_date = date_create_from_format('m/d/Y H:i:s', $endDttm . " 00:00:00");
        $end_date->add(new DateInterval('P1D'));

        $to_and_from = false;
        $conditions = array();
        if (!is_null($sender) && is_null($recipient)) {
            $conditions['OR'] = array(
                                        array("Message LIKE" => "%from=<" . $sender . ">, size%"),
                                        array("Message LIKE" => "%from=" . $sender . ", size%")
                                     );
        } else if (!is_null($recipient)) {
            $conditions['OR'] = array(
                                        array("Message LIKE" => "%to=" . $recipient . ",%delay%"),
                                        array("Message LIKE" => "%to=<" . $recipient . ">,%delay%")
                                     );
            if (!is_null($sender)) {
                $to_and_from = true;
            }
        } else {
            $condition['OR'] = array(
                                        array("Message LIKE" => "%to=%"),
                                        array("Message LIKE" => "%from=%")
                                    );
        }
        $conditions['ReceivedAt BETWEEN ? AND ?'] = array($start_date->format('Y-m-d\TH:i:s'), $end_date->format('Y-m-d\TH:i:s'));

        $temp_results = $this->find('all', array('conditions' => $conditions, 'order' => 'ReceivedAt DESC', 'limit' => $maxResults, 'fields' => array('Message', 'ReceivedAt')));

        $results = array();
        if (!empty($temp_results)) {
            $results = $this->formatResults($temp_results, $to_and_from, $sender_contains, $sender);
        }

        return $results;
    }

    private function formatResults($temp_results, $to_and_from, $sender_contains, $sender_search) {
        $results = array();
        foreach ($temp_results as $temp_result) {
            $datetime = date_create_from_format('Y-m-d H:i:s', $temp_result['Routers']['ReceivedAt']);

            $message_split = preg_split("/[:,]?\s+/", $temp_result['Routers']['Message']);
            $queue_id = $message_split[1];
            $queue_id_array = array($queue_id);

            $index = 0;
            $log_lines = array();
            while ($index < count($queue_id_array)) {
                $this->getFromID($queue_id_array[$index++], $queue_id_array, $log_lines);
            }

            $sender = "";
            foreach($log_lines as $line) {
                if (preg_match("/(.*from=[<]?)|([>]?,\s.*)/", $line['Routers']['Message'])) {
                    $message_from = preg_split("/(.*from=[<]?)|([>]?,\s.*)/", $line['Routers']['Message']);
                    $sender = $message_from[1];
                    break;
                } else {
                    echo htmlspecialchars($line['Message']) . "<br/>";
                }
            }

            $recipients = Array();
            if (preg_match("/(.*to=[<]?)|(>,<)|([>]?,\s.*)/", $log_lines[count($log_lines) - 1]['Routers']['Message'])) {
                $message_to = preg_split("/(.*to=[<]?)|(>,<)|([>]?,\s.*)/", $log_lines[count($log_lines) - 1]['Routers']['Message']);
                foreach ($message_to as $temp_recip) {
                    if ($temp_recip != "") {
                        array_push($recipients, $temp_recip);
                    }
                }
            } else {
                array_push($recipients, "");
            }

            if (preg_match("/dsn=|, stat=/", $log_lines[count($log_lines) - 1]['Routers']['Message'])) {
                $message_dsn = preg_split("/dsn=|, stat=/", $log_lines[count($log_lines) - 1]['Routers']['Message']);
                $temp_dsn = $message_dsn[1];
            } else {
                $temp_dsn = "";
            }

            $status = ($temp_dsn === "2.0.0" ? "Sent" : "Error: check logs");

            $result = array();
            $result['Date'] = $datetime->format('m/d/Y');
            $result['Time'] = $datetime->format('H:i');
            $result['Sender'] = $sender;
            $result['Recipients'] = $recipients;
            $result['Subject'] = "";
            $result['Status'] = $status;
            $result['Loglines'] = $log_lines;

            $push_results = false;
            if ($to_and_from) {
                if ($sender_contains) {
                    $sender_regex = str_replace("%", ".*", $sender_search);
                    if (preg_match("/" . $sender_regex . "/", strtolower($sender))) {
                        $push_results = true;
                    }
                } else {
                    if (strtolower($sender_search) === strtolower($sender))
                    {
                        $push_results = true;
                    }
                }
            } else {
                $push_results = true;
            }

            if ($push_results) {
                array_push($results, $result);
            }

        }
        return $results;
    }

    private function getFromID($id_to_search, &$queue_id_array, &$log_lines) {
        $conditions = array("Message LIKE" => " " . $id_to_search . "%");
        $search_results = $this->find('all', array('conditions' => $conditions));

        foreach ($search_results as $result) {
            $secondary_id = array();
            preg_match("/stat=Sent\s\(.+\sMessage accepted/", $result['Routers']['Message'], $secondary_id);

            array_push($log_lines, $result);
            if (count($secondary_id) > 0) {
                $id_to_add = substr($secondary_id[0], 11, strlen($secondary_id[0]) - 28);
                if (in_array($id_to_add, $queue_id_array) != true) {
                    array_push($queue_id_array, $id_to_add);
                }
            }
        }
    }

    public function getLogs() {

    }
}
