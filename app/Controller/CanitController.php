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
        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'settings');

        $recipient = $_REQUEST['recipient'];
        $recipient_contains = $_REQUEST["recipient_contains"];
        $sender = $_REQUEST['sender'];
        $sender_contains = $_REQUEST["sender_contains"];
        $subject = $_REQUEST['subject'];
        $subject_contains = $_REQUEST["subject_contains"];
        $startDttm = $_REQUEST['start_date'];
        $endDttm = $_REQUEST['end_date'];
        $maxResults = $_REQUEST['max_results'];
        $offset = $_REQUEST['offset'];

        $results = CanItClient::getCanitResults($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

    public function canitLogs() {
        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'settings');

        $queue_id = $_REQUEST['queue_id'];
        $reporting_host = $_REQUEST["reporting_host"];
        $logs = CanItClient::getLogs($queue_id, $reporting_host);

        return new CakeResponse(array('body' => json_encode($logs), 'type' => 'json'));
    }
} 