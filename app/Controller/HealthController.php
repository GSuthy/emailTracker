
<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

class SearchLogsObject {
       public function SearchLogsObject($message) {
        $this->message = $message;
    }

    public function messageM() {
        return $this->message;
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
            $temp = new SearchLogsObject($log["message"]);
            
            // echo($temp->hostName() . "<br>");
            array_push($searchLogObjects, $temp);
        }
        $working = array();
        $message = CanItClient::searchlog();
        foreach ($message as $temp) {
            if ($temp['message'] == "All mounted volumes have at least 10% free disk space and inodes"){
                // && $temp['test_ok'] == 1 && $temp['hostname'] == "gw10.byu.edu" || "gw5.byu.edu" || "gw3.byu.edu") {
            array_push($array, $temp['hostname']);
            }
           
        }
        echo "<pre>";
        print_r($searchLogs);
        echo "</pre>";
       

$array = array();      
array_push($array, "item", "another item");
var_dump($array);

exit();
    }
}
?>
