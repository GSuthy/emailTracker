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
            if (isset($this->request['data']['routerSelect'])) {
                $recipient = $this->request['data']['recipient'];
                $recipient_contains = ($this->request['data']['recipientSearchType'] == "contains") ? true : false;
                $sender = $this->request['data']['sender'];
                $sender_contains = ($this->request['data']['senderSearchType'] == "contains") ? true : false;
                $startDttm = $this->request['data']['start_date'];
                $endDttm = $this->request['data']['end_date'];
                $maxCount = 30;
                $offset = 0;

                $routerResults = $this->Routers->getTable($recipient, $recipient_contains,
                    $sender, $sender_contains,
                    $startDttm, $endDttm, $maxCount, $offset);

                $this->set('numRouterResultsLeft', $routerResults['count'] - count($routerResults['results']));
                $this->set('routerResults', $routerResults['results']);
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
        $json = json_encode($results);

        $this->set('moreResults', $json);
        $this->layout = null;
        $this->render('canitresults');
    }

    public function routersresults($recipient = null, $recipient_contains = null, $sender = null, $sender_contains = null, $startDttm = null, $endDttm = null, $maxResults = null, $offset = null) {
        $results = $this->Routers->getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults, $offset);
        $json = json_encode($results);
        $this->set('moreResults', $json);
        $this->layout = null;
        $this->render('routersresults');
    }

    public function canitlogs($queue_id = null, $reporting_host = null) {
        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'settings');

        $queue_id = $_REQUEST['queue_id'];
        $reporting_host = $_REQUEST["reporting_host"];
        $logs = CanItClient::getLogs($queue_id, $reporting_host);
        $json = json_encode($logs);

        $this->set('logs', $json);
        $this->layout = null;
        $this->render('canitlogs');
    }

    public function unauthorized() {}
}
