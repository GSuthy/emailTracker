<?php
App::uses('AppModel', 'Model');

/**
 * Class Routers
 * This model class is used to interface with the routers table referenced in the database.php file.
 * Class methods are called from RoutersController.php
 */

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'syslog';
    public $useTable = 'emailevents';
	public $primaryKey = 'id';

    /**
     * This method is used to make a general database query for mail-routing logging tables.
     *
     * @param $recipient    String containing the recipient being searched on.  This is a 'contains' search.
     * @param $sender       String containing the sender being searched on.  This is a 'contains' search.
     * @param $subject      String containing the subject being searched on.  This is a 'contains' search.  This field is unused.
     * @param $startDttm    String containing the start datetime for the search.
     * @param $endDttm      String containing the end datetime for the search.
     * @param $maxResults   Integer denoting the number of results to return.
     * @param $offset       Integer denoting offset index for the search results.
     * @return array        Returns an array of query results.
     */
    public function getTable($recipient, $sender, $subject, $startDttm, $endDttm, $maxResults, $offset) {
        $this->formatInput($recipient, $sender, $subject, $startDttm, $endDttm);

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
            $sender_or = array('OR' => array(
                array('Routers.sender_receiver LIKE' => $sender),
                array('Routers.sender_receiver LIKE' => '<'.$sender.'>')
            ));
            array_push($and_array, $sender_or);
        }

        if (!is_null($recipient)) {
            $recipient_or = array('OR' => array(
                array('Table2.sender_receiver LIKE' => $recipient),
                array('Table2.sender_receiver LIKE' => '<'.$recipient.'>')
            ));
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

    /**
     * Returns the previous log line in the link by searching for lines whose 'next_id' field is equal to the given message_id.
     *
     * @param $message_id   String containing the message ID for the previous additional-information log.
     * @return array        Array including the log lines preceding the log line with the corresponding ID.
     */
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

    /**
     * Returns the current log line in the link by searching for lines whose 'message_id' field is equal to the given message_id.
     *
     * @param $message_id   String containing the message ID for the specified additional-information log.
     * @return array        Array including the logs lines corresponding to the ID.
     */
    public function getCurrentLog($message_id) {
        $conditions = array();
        $conditions['message_id'] = $message_id;

        $results = $this->find('all', array('conditions' => $conditions));

        return $results;
    }

    /**
     * Returns the next log line in the link by searching for lines whose 'message_id' field is equal to the given next_id.
     *
     * @param $next_id      String containing the message ID for the next additional-information log.
     * @return array        Array including the log lines succeeding the log line with the corresponding next ID.
     */
    public function getNextLink($next_id) {
        $conditions = array();
        $conditions['message_id'] = $next_id;

        $results = $this->find('all', array('conditions' => $conditions));

        return $results;
    }

    /**
     * Formats the fields for use in the MySQL query.
     *
     * @param $recipient    String containing the recipient address being searched on.
     * @param $sender       String containing the sender address being searched on.
     * @param $subject      String containing the subject being searched on.
     * @param $startDttm    String containing the start datetime for the query.
     * @param $endDttm      String containing the end datetime for the query.
     */
    private function formatInput(&$recipient, &$sender, &$subject, &$startDttm, &$endDttm) {
        if (empty($sender)) {
            $sender = null;
        } else {
            $sender = "%" . $sender . "%";
        }

        if (empty($recipient)) {
            $recipient = null;
        } else {
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
    }

    /**
     * @param $temp_results     Array resulting from the query produced in getTable.
     * @return array            Returns the same information, but the array is formatted in a way that is simpler to manage in the JavaScript portion of the code.
     */
    private function formatOutput($temp_results) {
        $results = array();
        foreach ($temp_results as $temp_result) {
            $datetime = date_create_from_format('Y-m-d H:i:s', $temp_result['Routers']['received_at']);
            $status = $temp_result['Table2']['stat'];
            $status = explode(" ", $status)[0];

            $result = array();
            $result['Date'] = $datetime->format('m/d/Y');
            $result['Time'] = $datetime->format('H:i');
            $result['Sender'] = preg_replace("/<|>/", "", $temp_result['Routers']['sender_receiver']);
            $result['Recipients'] = explode(",", preg_replace("/<|>/", "", $temp_result['Table2']['sender_receiver']));
            $result['Status'] = $status;
            $result['Message_ID'] = $temp_result['Routers']['message_id'];
            $result['Next_ID'] = $temp_result['Table2']['next_id'];

            array_push($results, $result);
        }
        return $results;
    }
}

