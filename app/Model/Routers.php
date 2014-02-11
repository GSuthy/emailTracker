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
        if (!is_null($sender)) {
            if (empty($sender)) {
                $sender = null;
            } else if ($sender_contains) {
                $sender = "%" . $sender . "%";
            }
        }

        if (!is_null($recipient)) {
            if (empty($recipient)) {
                $recipient = null;
            } else if ($recipient_contains) {
                $recipient = "%" . $recipient . "%";
            }
        }

        $to_and_from = false;
        if (!is_null($sender) && is_null($recipient)) {

        } else if (is_null($sender) && !is_null($recipient)) {

        } else if (!is_null($sender) && !is_null($recipient)) {
            $to_and_from = true;
        } else {

        }


    }
}
