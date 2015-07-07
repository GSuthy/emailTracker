
<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

class HealthController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('add');
    }

    public function index() {

        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'ExchangeAPI/ExchangeClient');
        App::import('Vendor', 'settings');
        $searchLogs = CanItClient::searchlog(); 

        $searchLogObjects = array();
        foreach($searchLogs as $log) {
            $temp = new SearchLogObject($log["stream"]);
            print_r("<h1>$temp</h1>");
        }

        echo "<pre>";
        print_r($searchLogs);
        echo "</pre>";
        exit();


    }
}
?>

