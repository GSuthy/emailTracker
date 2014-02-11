<?php
App::uses('AppModel', 'Model');

class Routers extends AppModel {
    public $name = 'Routers';
    public $useDbConfig = 'routers';
    public $useTable = 'systemevents';
	public $primaryKey = 'ID';

    public function test() {
        $datasource = $this->getDataSource();
        var_dump($datasource);
        die();
    }

    public function getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults) {
        if ($recipient_contains) {

        }
    }
}
