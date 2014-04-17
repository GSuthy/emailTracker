<?php
App::uses('AppModel', 'Model');

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'routers';
    public $useTable = 'emailevents';
	public $primaryKey = 'id';

    public function getTable($recipient, $recipient_contains, $sender, $sender_contains, $subject, $startDttm, $endDttm, $maxResults, $offset) {
        $this->formatInput($recipient, $recipient_contains, $sender, $sender_contains, $subject, $startDttm, $endDttm);

        $options = array();
        $options['joins'] = array(
            array(
                'table' => 'emailevents',
                'alias' => 'Table2',
                'type' => 'INNER',
                'conditions' => array(
                    'Routers.message_id = Table2.message_id',
                    'Routers.is_type_from' => 1,
                    'Table2.is_type_from' => 0
                )
            )
        );

        $conditions = array();
        $and_array = array();

        if (!is_null($sender)) {
            if ($sender_contains) {
                $sender_or = array('OR' => array(
                    array('Routers.sender_receiver LIKE' => $sender),
                    array('Routers.sender_receiver LIKE' => '<'.$sender.'>')
                ));
            } else {
                $sender_or = array('OR' => array(
                    array('Routers.sender_receiver =' => $sender),
                    array('Routers.sender_receiver =' => '<'.$sender.'>')
                ));
            }
            array_push($and_array, $sender_or);
        }

        if (!is_null($recipient)) {
            if ($recipient_contains) {
                $recipient_or = array('OR' => array(
                    array('Table2.sender_receiver LIKE' => $recipient),
                    array('Table2.sender_receiver LIKE' => '<'.$recipient.'>')
                ));
            } else {
                $recipient_or = array('OR' => array(
                    array('Table2.sender_receiver =' => $recipient),
                    array('Table2.sender_receiver =' => '<'.$recipient.'>')
                ));
            }
            array_push($and_array, $recipient_or);
        }

        if (!is_null($sender) && !is_null($recipient)) {
            $conditions['AND'] = $and_array;
        } else if (!is_null($sender)) {
            $conditions['OR'] = $sender_or;
        } else if (!is_null($recipient)) {
            $conditions['OR'] = $recipient_or;
        }

        $conditions['Routers.received_at >='] = $startDttm->format("Y-m-d H:i:s");
        $conditions['Routers.received_at <='] = $endDttm->format("Y-m-d H:i:s");

        $options['conditions'] = $conditions;
        $options['order'] = 'Routers.received_at DESC';
        $options['fields'] = array('Routers.message_id', 'Routers.received_at', 'Routers.sender_receiver', 'Table2.next_id', 'Table2.sender_receiver', 'Table2.stat');
        $options['offset'] = $offset;
        $options['limit'] = $maxResults;

        if (is_null($subject) || !is_null($sender) || !is_null($recipient)) {
            $temp_results = $this->find('all', $options);
            $results = $this->formatOutput($temp_results);
        } else {
            $results = array();
        }
        return $results;
    }

    public function getPreviousLink($message_id) {
        $conditions = array();
        $conditions['next_id'] = $message_id;

        $resultsTemp = $this->find('all', array('conditions' => $conditions));
        $results = array();
        foreach ($resultsTemp as $resultTemp) {
            array_push($results, $this->getCurrentLog($resultTemp['Routers']['message_id']));
        }

        return $results;
    }

    public function getCurrentLog($message_id) {
        $conditions = array();
        $conditions['message_id'] = $message_id;

        $results = $this->find('all', array('conditions' => $conditions));

        return $results;
    }

    public function getNextLink($next_id) {
        $conditions = array();
        $conditions['message_id'] = $next_id;

        $results = $this->find('all', array('conditions' => $conditions));

        return $results;
    }

    private function formatInput(&$recipient, $recipient_contains, &$sender, $sender_contains, &$subject, &$startDttm, &$endDttm) {
        if (empty($sender)) {
            $sender = null;
        } else if($sender_contains) {
            $sender = "%" . $sender . "%";
        }

        if (empty($recipient)) {
            $recipient = null;
        } else if ($recipient_contains) {
            $recipient = "%" . $recipient . "%";
        }

        if (empty($subject)) {
            $subject = null;
        }

        $startDttm = (date_create_from_format('m/d/Y H:i:s', $startDttm . " 00:00:00") ?
                      date_create_from_format('m/d/Y H:i:s', $startDttm . " 00:00:00") :
                      date_create_from_format('Y-m-d H:i:s.u', str_replace('T', ' ', $startDttm)));
        $endDttm = (date_create_from_format('m/d/Y H:i:s', $endDttm . " 00:00:00") ?
                    date_create_from_format('m/d/Y H:i:s', $endDttm . " 00:00:00") :
                    date_create_from_format('Y-m-d H:i:s.u', str_replace('T', ' ', $endDttm)));
        $endDttm->add(new DateInterval('P1D'));
    }

    private function formatOutput($temp_results) {
        $results = array();
        foreach ($temp_results as $temp_result) {
            $datetime = date_create_from_format('Y-m-d H:i:s', $temp_result['Routers']['received_at']);

            $result = array();
            $result['Date'] = $datetime->format('m/d/Y');
            $result['Time'] = $datetime->format('H:i');
            $result['Sender'] = preg_replace("/<|>/", "", $temp_result['Routers']['sender_receiver']);
            $result['Recipients'] = explode(",", preg_replace("/<|>/", "", $temp_result['Table2']['sender_receiver']));
            $result['Status'] = $temp_result['Table2']['stat'];
            $result['Message_ID'] = $temp_result['Routers']['message_id'];
            $result['Next_ID'] = $temp_result['Table2']['next_id'];

            array_push($results, $result);
        }
        return $results;
    }
}

