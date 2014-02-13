<?php
App::uses('AppModel', 'Model');

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'routers';
    public $useTable = 'systemevents';
	public $primaryKey = 'ID';

    public function test() {
        $datasource = $this->getDataSource();
        var_dump($datasource);
        die();
    }

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

        $to_and_from = false;
        $conditions = array();
        if (!is_null($sender) && is_null($recipient)) {
            $conditions['OR'] = array(
                                        "Message LIKE" => "%from=<" . $sender . ">, size%",
                                        "Message LIKE" => "%from=" . $sender . ", size%"
                                     );
        } else if (!is_null($recipient)) {
            $conditions['OR'] = array(
                                        "Message LIKE" => "%to=" . $recipient . ",%delay%",
                                        "Message LIKE" => "%to=<" . $recipient . ">,%delay%"
                                     );
        } else {
            $condition['OR'] = array(
                                        "Message LIKE" => "%to=%",
                                        "Message LIKE" => "%from=%"
                                    );
        }
//        $conditions['ReceivedAt BETWEEN ? AND ?'] = array($startDttm, $endDttm);

        $results = $this->find('all', array('conditions' => $conditions, 'order' => 'ReceivedAt DESC', 'limit' => 20, 'fields' => array('Message', 'ReceivedAt')));
        return $results;
    }
}
