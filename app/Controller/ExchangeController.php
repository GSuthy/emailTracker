<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 4/2/14
 * Time: 3:27 PM
 */

App::uses('AppController', 'Controller');

/**
 * Class ExchangeController
 * Receives AJAX calls and returns table information.
 */

class ExchangeController extends AppController {

    public $uses = 'Exchange';

    /**
     * Receives data from an AJAX request and calls the Exchange model class to get information to display in the Exchange Results table.
     *
     * @return CakeResponse     Contains an array of data to be displayed in the Exchange Results table.
     */
    public function exchangeResults() {
        session_write_close();
        $sender = $this->request->data("sender");
        $recipient = $this->request->data("recipient");
        $subject = $this->request->data("subject");
        $start_date = $this->request->data("start_date");
        $end_date = $this->request->data("end_date");
        $max_results = $this->request->data("max_results");
        $offset = $this->request->data("offset");

        $results = $this->Exchange->getTable($recipient, $sender, $subject, $start_date, $end_date, $max_results, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

    /**
     * Receives data from an AJAX request and calls the Exchange model class to get information to display when a row in the Exchange Results table is clicked.
     *
     * @return CakeResponse     Contains an array of data to be displayed below the clicked row in the Exchange Results table.
     */
    public function exchangeLogs() {
        session_write_close();
        $maxResults = $_REQUEST['max_results'];

        $messageId = html_entity_decode($_REQUEST['message_id']);
        $messageId = preg_replace('/<\/.*>/',"",$messageId);

        if(!empty($messageId)) {
            $allLogs = $this->Exchange->getLogs($messageId, $maxResults);
        } else {
            //these 3 vars are only used if messageId is empty
            $utcMilliseconds = $_REQUEST['utc_milliseconds'];
            $sender = $_REQUEST['sender_address'];
            $subject = $_REQUEST['message_subject'];

            $allLogs = $this->Exchange->getLogs($messageId, $maxResults, $sender, $subject, $utcMilliseconds);
        }

        return new CakeResponse(array('body' => json_encode($allLogs), 'type' => 'json'));
    }
} 