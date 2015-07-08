
<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

class SearchLogsObject {
       public function SearchLogsObject($hostname) {
        $this->hostname = $hostname;
    }

    public function hostName() {
        return $this->hostname;
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
            
            // echo($temp->hostName() . "<br>");
            array_push($searchLogObjects, $temp);
        }

        $message = CanItClient::searchlog();
        foreach ($message as $temp) {
            if ($temp['hostname'] === "gw10.byu.edu") {
            echo ($temp['hostname'] . "<br>" . "is working");
        }
    }


        echo "<pre>";
        print_r($searchLogs);
        echo "</pre>";
        exit();


    }
}
?>
