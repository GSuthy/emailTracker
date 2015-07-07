
<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

class SearchLogsObject {
       public function SearchLogsObject($hostname, $status) {
        $this->hostname = $hostname;
        $this->status = $status;
    }

    public function hostName() {
        return $this->hostname;
    }
    public function statusTest() {
        return $this->statusTest;
    }

}


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
            $temp = new SearchLogsObject($log["hostname"]);
            echo($temp->hostName() . "<br>");
            array_push($searchLogObjects, $temp);
        }

        $searchLogObjects = array();
        foreach ($searchLogObjects as $status1) {
            $temp = new SearchLogsObject ($status1["stats"]);
            echo($temp->statusTest() . "<br>");
            array_push($searchLogObjects, $temp);
        }

        echo(count($searchLogObjects));


        echo "<pre>";
        print_r($searchLogs);
        echo "</pre>";
        exit();


    }
}
?>

