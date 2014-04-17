<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 4/2/14
 * Time: 3:27 PM
 */

App::uses('AppController', 'Controller');

class CanitController extends AppController {

    public function canitResults() {
        session_write_close();
        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'settings');

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

        $results = CanItClient::getCanitResults($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $start_date, $end_date, $max_results, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

    public function canitLogs() {
        session_write_close();
        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'settings');

        $queue_id = $_REQUEST['queue_id'];
        $reporting_host = $_REQUEST["reporting_host"];
        $logs = CanItClient::getLogs($queue_id, $reporting_host);

        return new CakeResponse(array('body' => json_encode($logs), 'type' => 'json'));
    }
} 