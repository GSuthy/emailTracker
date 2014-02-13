<?php
App::uses('AppController', 'Controller', 'Routers');

class RoutersController extends AppController {
    public $name = 'Routers';
    public $uses = array('Routers');

    public function index() {
        $this->Routers->test();
    }

    public function getTable() {

    }
}