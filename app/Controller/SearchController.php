<?php

class SearchController extends AppController {
	
	public function index() {
        if (!in_array("EAMP", explode(',', $this->Auth->user()['memberOf']))) {
            return $this->redirect('unauthorized');
        }

        App::import('Vendor', 'CanItAPI/CanItClient');
		App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
		App::import('Vendor', 'RouterAPI/RouterClient');
		App::import('Vendor', 'ExchangeAPI/ExchangeClient');
		App::import('Vendor', 'settings');
    }

    public function canitlogs($queue_id = null, $reporting_host = null) {
        /*if (!in_array("EAMP", explode(',', $this->Auth->user()['memberOf']))) {
            return $this->redirect(array('controller' => 'Errors', 'action' => 'unauthorized'));
        }*/

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