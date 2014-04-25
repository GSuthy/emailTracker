<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 3/17/14
 * Time: 5:48 PM
 */

/**
 * Class Exchange
 * This model class is used to interface with the exchange table referenced in the database.php file.
 * Class methods are called from ExchangeController.php
 */

class Exchange extends AppModel {
    public $name = 'Exchange';
    public $useDbConfig = 'exchange';
    public $useTable = 'logmain';
    public $primaryKey = 'id';

    /**
     * This method is used to make a general database query for Microsoft Exchange logging tables.
     *
     * @param $recipient    The recipient being searched on.  This is a 'contains' search.
     * @param $sender       The sender being searched on.  This is a 'contains' search.
     * @param $subject      The subject being searched on.  This is a 'contains' search.
     * @param $startDttm    The start datetime for the search.
     * @param $endDttm      The end datetime for the search.
     * @param $limit        The number of results to return.
     * @param $offset       Starts the results from the 'offset' index.
     * @return array        Returns an array of query results.
     */

    public function getTable($recipient, $sender, $subject, $startDttm, $endDttm, $limit, $offset) {
        if(empty($sender)) {
            $sender = null;
        } else {
            $sender = "%" . $sender . "%";
        }

        if(empty($recipient)) {
            $recipient = null;
        } else {
            $recipient = "%" . $recipient . "%";
        }

        if(empty($subject)) {
            $subject = null;
        } else {
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
        $conditions['Exchange.date_time >='] = date_format($startDttm, "Y-m-d H:i:s");
        $conditions['Exchange.date_time <='] = date_format($endDttm, "Y-m-d H:i:s");
        if(!is_null($sender)) {
            $conditions['Exchange.sender_address LIKE'] = $sender;
        }
        if(!is_null($recipient)) {
            $conditions['Recipients.recipient_address LIKE'] = $recipient;
        }
        if(!is_null($subject)) {
            $conditions['Exchange.message_subject LIKE'] = $subject;
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

    /**
     * This method returns additional information corresponding to a particular log
     *
     * @param $message_id               The ID by which the log will be fetched.
     * @param $limit                    The limit.
     * @param null $sender              The name of the sender address.
     * @param null $subject             The subject line of the email.
     * @param int $utcMilliseconds      The time of the log in milliseconds.
     * @return array                    Returns an array of additional information corresponding to the selected log.
     */
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
            $conditions['Exchange.date_time >='] = date_format($startDttm, "Y-m-d H:i:s");
            $conditions['Exchange.date_time <='] = date_format($endDttm, "Y-m-d H:i:s");

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

    /**
     * @param $temp_results     The array resulting from the query produced in getTable.
     * @return array            Returns the same information, but the array is formatted in a way that is simpler to manage in the JavaScript portion of the code.
     */
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


    /**
     * @param $temp_results     The array resulting from the query produced in getLogs.
     * @return array            Returns the same information, but the array is formatted in a way that is simpler to manage in the Javascript portion of the code.
     */
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