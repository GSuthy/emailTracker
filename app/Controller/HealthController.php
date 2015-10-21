
<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

// class SearchLogsObject {
//        public function SearchLogsObject($message) {
//         $this->message = $message;
//     }

//     public function messageM() {
//         return $this->message;
//     }

// }
  
 
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
        /* $searchLogs = CanItClient::searchlog(); 

        $searchLogObjects = array();
         foreach($searchLogs as $log) {
            $temp = new SearchLogsObject($log["message"]);
            
            echo($temp->hostName() . "<br>");
           array_push($searchLogObjects, $temp);
        }*/

    }
        /*Inlcuding the lines below only if you are trying to add a table that will include the databases that
        are in a correct state
       public static function data1() {
        $working = array();

        $message = CanItClient::searchlog();
             foreach ($message as $check) {
             if ($check['message'] == "All mounted volumes have at least 10% free disk space and inodes") {
                 if ($check['test_ok'] == 1) {            
                     // if ($check['hostname'] === "gw10.byu.edu") {
                     array_push($working, $check);
                 }      
                 }                        
             }
             asort($working);
            return $working;
        } */
   

       public static function error() {

        $notWorking = array();
        $message = CanItClient::searchlog();

            foreach ($message as $check) {
                if ($check['message'] == "All mounted volumes have at least 10% free disk space and inodes") {
                    if ($check['test_ok'] == 0) {
            
                    // if ($check['hostname'] === "gw10.byu.edu") {
                  array_push($notWorking, $check);
                    }  
                                       
                }
           
            
            }
          return $notWorking;
        }

        public function copytocluster () {
          $clusterCheck = array();
          $CanItSearch = CanItClient::searchlog();
          
          foreach ($CanItSearch as $checking){
            if ($checking['test_name'] == "CopyToCluster") {
              array_push($clusterCheck, $checking);

            }
          }

        
        return $clusterCheck;
  }     


}


?>
