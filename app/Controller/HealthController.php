
<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

class HealthController extends AppController {

    public $uses = "GatewayQueues";

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('add');
    }

    public function index() {

        App::import('Vendor', 'CanItAPI/CanItClient');
        App::import('Vendor', 'CanItAPIClient', array('file' => 'CanItAPI/canit-api-client.php'));
        App::import('Vendor', 'ExchangeAPI/ExchangeClient');
        App::import('Vendor', 'settings');

        echo "<pre>";
        print_r(CanItClient::searchLog());
        echo "</pre>";
        exit();
    }

    private function getTable() {
        $descriptions = $this->getDescriptions();
        $results = array();
        foreach ($descriptions as $description) {
            $params = array(
                'conditions' => array(
                    'description' => $description
                ),
                'fields' => array('server', 'active_queue', 'deferred_queue'),
            );
            $routers = $this->GatewayQueues->find('all', $params);
            $routers = Set::classicExtract($routers, '{n}.GatewayQueues');
            $results[$description] = $routers;
        }
        return $results;
    }

    private function getDescriptions() {
        $params = array(
            'fields' => 'description',
            'group' => 'description'
        );
        $descriptions = $this->GatewayQueues->find('all', $params);
        $descriptions = Set::classicExtract($descriptions, '{n}.GatewayQueues.description');
        return $descriptions;
    }

    public function add() {
        $data = $this->request->input('json_decode');
        if ($data == null) {
            $data = $this->request->data;
        } else {
            $data = (array)$data;
        }

        $valid = true;
        $valid &= array_key_exists('server', $data) && $data['server'] != "";
        $valid &= array_key_exists('active_queue', $data) && is_numeric($data['active_queue']);
        $valid &= array_key_exists('deferred_queue', $data) && is_numeric($data['deferred_queue']);

        $response = new CakeResponse();
        if ($valid) {
            $server = $data['server'];
            $active_queue = $data['active_queue'];
            $deferred_queue = $data['deferred_queue'];

            $this->GatewayQueues->id = $server;
            $success = $this->GatewayQueues->updateAll(
                array('active_queue' => $active_queue, 'deferred_queue' => $deferred_queue),
                array('server' => $server)
            );

            if ($success) {
                $response->statusCode(201);
            } else {
                $response->statusCode(500);
                $response->body(json_encode(array('message' => 'There was an error inserting the data into the database'), JSON_PRETTY_PRINT));
            }
        } else {
            $response->statusCode(400);
            $response->body(json_encode(array('message' => 'The data was transmitted in an invalid format'), JSON_PRETTY_PRINT));
        }

        return $response;
    }
} 
?>

