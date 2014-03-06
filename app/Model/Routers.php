<?php
App::uses('AppModel', 'Model');

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'routers';
    public $useTable = 'emailevents';
	public $primaryKey = 'id';

    public function getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults, $offset) {
        $this->formatInput($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm);

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
        $conditions['AND'] = array(
            array('OR' => array(
                array('Routers.sender_receiver LIKE' => $sender),
                array('Routers.sender_receiver LIKE' => '<'.$sender.'>')
            )),
            array('OR' => array(
                array('Table2.sender_receiver LIKE' => $recipient),
                array('Table2.sender_receiver LIKE' => '<'.$recipient.'>')
            ))
        );
        $conditions['Routers.received_at BETWEEN ? AND ?'] = array($startDttm->format("Y-m-d H:i:s"), $endDttm->format("Y-m-d H:i:s"));

        $options['conditions'] = $conditions;
        $options['fields'] = array('Routers.message_id', 'Routers.received_at', 'Routers.sender_receiver', 'Table2.sender_receiver', 'Table2.stat');
        $options['offset'] = $offset;

        $count = $this->find('count', $options);

        $options['limit'] = $maxResults;
        $temp_results = $this->find('all', $options);

        $results = array(
            'count' => $count,
            'results' => array()
        );

        if (!empty($temp_results)) {
            $results['results'] = $this->formatOutput($temp_results);
            $results['count'] -= ($offset + count($results['results']));
        }

        return $results;
    }

    private function formatInput(&$recipient, $recipient_contains, &$sender, $sender_contains, &$startDttm, &$endDttm) {
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

            array_push($results, $result);
        }
        return $results;
    }
}

