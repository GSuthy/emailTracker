<?php
App::uses('AppController', 'Controller', 'Routers');

class SearchController extends AppController {
    public $uses = array('Routers');

	public function index() {
        App::import('Vendor', 'CanItAPI/CanItClient');
		App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
		App::import('Vendor', 'RouterAPI/RouterClient');
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
                $maxCount = 20;

                $routerResults = $this->Routers->getTable($recipient, $recipient_contains,
                    $sender, $sender_contains,
                    $startDttm, $endDttm, $maxCount);
                $this->set('routerResults', $routerResults);
            }
        }
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
