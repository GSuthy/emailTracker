<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 4/2/14
 * Time: 3:27 PM
 */

App::uses('AppController', 'Controller');

class ExchangeController extends AppController {

    public $uses = 'Exchange';

    public function exchangeResults() {
        session_write_close();
        $sender = $this->request->data("sender");
        $sender_contains = $this->request->data("sender_contains");
        $recipient = $this->request->data("recipient");
        $recipient_contains = $this->request->data("recipient_contains");
        $subject = $this->request->data("subject");
        $subject_contains = $this->request->data("subject_contains");
        $start_date = $this->request->data("start_date");
        $end_date = $this->request->data("end_date");
        $max_results = $this->request->data("max_results");
        $offset = $this->request->data("offset");

        $results = $this->Exchange->getTable($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $start_date, $end_date, $max_results, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

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