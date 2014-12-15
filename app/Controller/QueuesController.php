<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 12/11/14
 * Time: 4:10 PM
 */

App::uses('AppController', 'Controller');

class QueuesController extends AppController {

    public $uses = "GatewayQueues";

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('add');
    }

    public function index() {
        $results = $this->GatewayQueues->find('all');
        $this->set('results', $results);
    }

    public function getTable() {
        $results = $this->GatewayQueues->find('all');
        $response = new CakeResponse();
        $response->statusCode(200);
        $response->body(json_encode(array('results' => $results), JSON_PRETTY_PRINT));
        $response->type('json');
        return $response;
    }

    public function add() {
        $data = $this->request->data;

        $valid = true;
        $valid &= array_key_exists('server', $data) && $data['server'] != "";
        $valid &= array_key_exists('active_queue', $data) && is_numeric($data['active_queue']);
        $valid &= array_key_exists('deferred_queue', $data) && is_numeric($data['deferred_queue']);

        $response = new CakeResponse();
        if ($valid) {
            $server = $this->request->data['server'];
            $active_queue = $this->request->data['active_queue'];
            $deferred_queue = $this->request->data['deferred_queue'];

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