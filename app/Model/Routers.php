<?php
App::uses('AppModel', 'Model');

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'routers';
    public $useTable = 'emailevents';
	public $primaryKey = 'id';

    public function getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults) {

        if (!is_null($sender)) {
            if ($sender_contains) {
                $sender = "%" . $sender . "%";
            }
        }

        if (!is_null($recipient)) {
            if ($recipient_contains) {
                $recipient = "%" . $recipient . "%";
            }
        }

        $start_date = date_create_from_format('m/d/Y H:i:s', $startDttm . " 00:00:00");
        $end_date = date_create_from_format('m/d/Y H:i:s', $endDttm . " 00:00:00");
        $end_date->add(new DateInterval('P1D'));

        $options = array();
        $options['joins'] = array(
            array('table' => 'emailevents',
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
        $conditions['Routers.sender_receiver LIKE'] = $sender;
        $conditions['Table2.sender_receiver LIKE'] = $recipient;
        $conditions['Routers.received_at BETWEEN ? AND ?'] = array($start_date->format("Y-m-d H:i:s"), $end_date->format("Y-m-d H:i:s"));

        $options['conditions'] = $conditions;
        $options['limit'] = $maxResults;
        $options['fields'] = array('Routers.message_id', 'Routers.received_at', 'Routers.sender_receiver', 'Table2.sender_receiver', 'Table2.stat');

        $temp_results = $this->find('all', $options);

        $results = array();
        if (!empty($temp_results)) {
            $results = $this->formatResults($temp_results);
        }

        return $results;
    }

    private function formatResults($temp_results) {
        $results = array();
        foreach ($temp_results as $temp_result) {
            $datetime = date_create_from_format('Y-m-d H:i:s', $temp_result['Routers']['received_at']);

            $result = array();
            $result['Date'] = $datetime->format('m/d/Y');
            $result['Time'] = $datetime->format('H:i');
            $result['Sender'] = preg_replace("/<|>/", "", $temp_result['Routers']['sender_receiver']);
            $result['Recipients'] = explode(",", preg_replace("/<|>/", "", $temp_result['Table2']['sender_receiver']));
            $result['Status'] = $temp_result['Table2']['stat'];

            array_push($results, $result);
        }
        return $results;
    }
}
