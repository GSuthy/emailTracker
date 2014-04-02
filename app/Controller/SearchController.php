<?php
App::uses('AppController', 'Controller', 'Routers');

class SearchController extends AppController {
    public $uses = array('Routers', 'Exchange');

	public function index() {
        App::import('Vendor', 'CanItAPI/CanItClient');
		App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
		App::import('Vendor', 'ExchangeAPI/ExchangeClient');
		App::import('Vendor', 'settings');

        $easterEggOn = Configure::read('easterEggOn');

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
                $canitResults = CanItClient::getCanitResults($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults, $offset);
                $this->set('canitResults', $canitResults);
            }
        }
    }

    public function unauthorized() {}
}
