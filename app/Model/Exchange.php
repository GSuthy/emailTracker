<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 3/17/14
 * Time: 5:48 PM
 */

class Exchange extends AppModel {
    public $name = 'Exchange';
    public $useDbConfig = 'exchange';
    public $useTable = 'logmain';
    public $primaryKey = 'id';

    public function getTable($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $limit, $offset) {
        if(empty($sender)) {
            $sender = null;
        } else if($sender_contains) {
            $sender = "%" . $sender . "%";
        }

        if(empty($recipient)) {
            $recipient = null;
        } else if($recipient_contains) {
            $recipient = "%" . $recipient . "%";
        }

        if(empty($subject)) {
            $subject = null;
        } else if($subject_contains) {
            $subject = "%" . $subject . "%";
        }

        $startDttm = date_create($startDttm);
        $endDttm = date_create($endDttm);

        if(!$endDttm) {
            $endDttm = date_create();
        }
        $endDttm->add(new DateInterval('P1D'));

        if(is_null($limit) || !is_numeric($limit)) {
            $limit = 30;
        }

        if(is_null($offset) || !is_numeric($offset)) {
            $offset = 0;
        }

        $fields = array(
            'MIN(Exchange.date_time) as date_time',
            'Exchange.sender_address',
            'Exchange.message_subject',
            'Exchange.message_id',
            'Recipients.recipient_address'
        );

        $joins = array (
            array(
                'table' => 'messagerecipients',
                'alias' => 'Recipients',
                'type' => 'INNER',
                'conditions' => 'Exchange.id = Recipients.log_id'
            )
        );

        $conditions = array();
        $conditions['Exchange.date_time BETWEEN ? AND ?'] = array(date_format($startDttm, "Y-m-d H:i:s"), date_format($endDttm, "Y-m-d H:i:s"));
        if(!is_null($sender)) {
            $conditions['Exchange.sender_address LIKE'] = $sender;
        }
        if(!is_null($recipient)) {
            $conditions['Recipients.recipient_address LIKE'] = $recipient;
        }
        if(!is_null($subject)) {
            $conditions['Exchange.message_subject'] = $subject;
        }
        $conditions['Recipients.recipient_address NOT LIKE'] = '%@ad.byu.edu';

        $options = array(
            'conditions' => $conditions,
            'joins' => $joins,
            'fields' => $fields,
            'group' => 'Exchange.message_id',
            'order' => 'Exchange.date_time DESC',
            'offset' => $offset,
            'limit' => $limit
        );

        $temp_results = $this->find('all', $options);
        $results = $this->formatTableOutput($temp_results);
        return $results;
    }

    public function getLogs($message_id, $limit, $sender = NULL, $subject = NULL, $utcMilliseconds = 0) {
        if(is_null($limit) || !is_numeric($limit)) {
            $limit = 20;
        }

        $time = date_create("@" .(($utcMilliseconds / 1000) - (7 * 60 * 60)));

        $startDttm = clone $time;
        $startDttm->sub(new DateInterval("PT1M"));
        $endDttm = clone $time;
        $endDttm->add(new DateInterval("PT1M"));


        $fields = array(
            'Exchange.date_time',
            'Exchange.client_hostname',
            'Exchange.server_hostname',
            'Exchange.event_id',
            'Exchange.sender_address',
            'Exchange.message_subject',
            'Exchange.message_id',
            'Recipients.recipient_address'
        );

        $joins = array (
            array(
                'table' => 'messagerecipients',
                'alias' => 'Recipients',
                'type' => 'INNER',
                'conditions' => 'Exchange.id = Recipients.log_id'
            )
        );

        $conditions = array();
        if (empty($message_id)) {
            $conditions['Exchange.message_id is'] = 'null';
            $conditions['Exchange.date_time BETWEEN ? AND ?'] = array(date_format($startDttm, "Y-m-d H:i:s"), date_format($endDttm, "Y-m-d H:i:s"));

            if(empty($sender)) {
                $conditions['Exchange.sender_address is'] = 'null';
            } else {
                $conditions['Exchange.sender_address'] = $sender;
            }

            if(empty($subject)) {
                $conditions['Exchange.message_subject is'] = 'null';
            } else {
                $conditions['Exchange.message_subject'] = $subject;
            }
        } else {
            $conditions['Exchange.message_id'] = $message_id;
        }

        $options = array(
            'conditions' => $conditions,
            'joins' => $joins,
            'fields' => $fields,
            'order' => array('Exchange.date_time'),
            'limit' => $limit
        );

        $temp_results = $this->find('all', $options);
        $results = $this->formatLogOutput($temp_results);
        return $results;
    }

    private function formatTableOutput($temp_results) {
        $results = array();
        foreach ($temp_results as $temp_result) {
            $datetime = date_create_from_format('Y-m-d H:i:s', $temp_result[0]['date_time']);

            $result = array();
            $result['Date'] = $datetime->format('m/d/Y');
            $result['Time'] = $datetime->format('H:i:s');
            $result['Sender'] = $temp_result['Exchange']['sender_address'];
            $result['Recipient'] = $temp_result['Recipients']['recipient_address'];
            $result['Subject'] = $temp_result['Exchange']['message_subject'];
            $result['ID'] = $temp_result['Exchange']['message_id'];

            array_push($results, $result);
        }
        return $results;
    }

    private function formatLogOutput($temp_results) {
        $results = array();
        foreach ($temp_results as $temp_result) {
            $result = array();
            $result['date_time'] = $temp_result['Exchange']['date_time'];
            $result['event_id'] = $temp_result['Exchange']['event_id'];
            $result['recipient_address'] = $temp_result['Recipients']['recipient_address'];
            $result['client_hostname'] = $temp_result['Exchange']['client_hostname'];
            $result['server_hostname'] = $temp_result['Exchange']['server_hostname'];

            array_push($results, $result);
        }
        return $results;
    }
} 