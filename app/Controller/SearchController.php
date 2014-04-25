<?php
App::uses('AppController', 'Controller', 'Routers');

/**
 * Class SearchController
 * This controller class sets thresholds for the CanIt spam scores.
 */

class SearchController extends AppController {
    public $uses = array('Routers', 'Exchange');

    /**
     * Called on page load.
     * Calls CanItClient class' getThresholds method and sets the thresholds in the JavaScript portion of the code.
     */
    public function index() {
        App::import('Vendor', 'CanItAPI/CanItClient');
		App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
		App::import('Vendor', 'ExchangeAPI/ExchangeClient');
		App::import('Vendor', 'settings');

        $easterEggOn = Configure::read('easterEggOn');

        if ($this->request->is('post')) {
            if (isset($this->request['data']['canitSelect'])) {
                $scoreThresholds = CanItClient::getThresholds();
                $this->set('scoreThresholds', $scoreThresholds);
            }
        }
    }

    public function unauthorized() {}
}
