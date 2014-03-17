<?php
App::uses('AppController', 'Controller', 'Routers');

class SearchController extends AppController {
    public $uses = array('Routers');

	public function index() {
        App::import('Vendor', 'CanItAPI/CanItClient');
		App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
		App::import('Vendor', 'ExchangeAPI/ExchangeClient');
		App::import('Vendor', 'settings');

        if ($this->request->is('post')) {

            $recipient = $this->request['data']['recipient'];
            $recipient_contains = ($this->request['data']['recipientSearchType'] == "contains") ? true : false;
            $sender = $this->request['data']['sender'];
            $sender_contains = ($this->request['data']['senderSearchType'] == "contains") ? true : false;
            $subject = $this->request['data']['subject'];
            $subject_contains = ($this->request['data']['subjectSearchType'] == "contains") ? true : false;
            $startDttm = $this->request['data']['start_date'];
            $endDttm = $this->request['data']['end_date'];
            $maxResults = 30;
            $offset = 0;

            if (isset($this->request['data']['canitSelect'])) {
                $scoreThresholds = CanItClient::getThresholds();
                $this->set('scoreThresholds', $scoreThresholds);
                $canitResults = CanItClient::getCanitResults($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults, $offset);;
                $this->set('canitResults', $canitResults);
            }
            if (isset($this->request['data']['routerSelect'])) {
                $routerResults = $this->Routers->getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults, $offset);
                $this->set('numRouterResultsLeft', $routerResults['count']);
                $this->set('routerResults', $routerResults['results']);
            }
            if (isset($this->request['data']['exchangeSelect'])) {

            }
        }
    }

    public function canitresults($recipient = null, $recipient_contains = null, $sender = null, $sender_contains = null, $subject = null,
                                 $subject_contains = null, $startDttm = null, $endDttm = null, $maxResults = null, $offset = null) {
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

    public function routersresults($recipient = null, $recipient_contains = null, $sender = null, $sender_contains = null, $startDttm = null, $endDttm = null, $maxResults = null, $offset = null) {
        $recipient = $_REQUEST['recipient'];
        $recipient_contains = $_REQUEST["recipient_contains"];
        $sender = $_REQUEST['sender'];
        $sender_contains = $_REQUEST["sender_contains"];
        $startDttm = $_REQUEST['start_date'];
        $endDttm = $_REQUEST['end_date'];
        $maxResults = $_REQUEST['max_results'];
        $offset = $_REQUEST['offset'];

        $results = $this->Routers->getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

    public function canitlogs($queue_id = null, $reporting_host = null) {
        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'settings');

        $queue_id = $_REQUEST['queue_id'];
        $reporting_host = $_REQUEST["reporting_host"];
        $logs = CanItClient::getLogs($queue_id, $reporting_host);

        return new CakeResponse(array('body' => json_encode($logs), 'type' => 'json'));
    }

    public function routerslogs($messageId = null, $nextId = null) {
        $messageId = $_REQUEST['message_id'];
        $nextId = $_REQUEST['next_id'];

        $allLogs = array();

        array_push($allLogs, $this->recursivePreviousLink($messageId));

        $currentLog = $this->Routers->getCurrentLog($messageId);
        array_push($allLogs, $currentLog);

        $currentNextId = $nextId;
        while (!is_null($currentNextId)) {
            $nextLink = $this->Routers->getNextLink($currentNextId);

            $temp_id = null;
            foreach ($nextLink as $array) {
                array_push($allLogs, $array);
                if (!is_null($array['Routers']['next_id'])) {
                    $temp_id = $array['Routers']['next_id'];
                }
            }
            $currentNextId = $temp_id;
        }

        return new CakeResponse(array('body' => json_encode($allLogs), 'type' => 'json'));
    }

    private function recursivePreviousLink($currentMessageId) {
        $returnLinks = array();
        $usedIDs = array();

        $previousLinks = $this->Routers->getPreviousLink($currentMessageId);
        foreach ($previousLinks as $links) {
            foreach ($links as $link) {
                if ($link['Routers']['message_id'] != null && !in_array($link['Routers']['message_id'], $usedIDs)) {
                    array_push($usedIDs, $link['Routers']['message_id']);
                    $tempArray = $this->recursivePreviousLink($link['Routers']['message_id']);
                    foreach ($tempArray as $result) {
                        array_push($returnLinks, $result);
                    }
                }
            }
        }
        array_push($returnLinks, $previousLinks);

        return $returnLinks;
    }

    public function unauthorized() {}
}
