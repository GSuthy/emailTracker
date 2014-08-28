<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 7/22/14
 * Time: 1:06 PM
 */

App::uses('CanItModel', 'Model');

class Logs extends CanItModel {
    public $useDbConfig = 'canit';
    public $useTable = '';

    public function getAll($realm, $stream)
    {
        $results = $this->GET(array(
            'conditions' => array(
                'realm' => $realm,
                'stream' => $stream,
                'rules'
            )
        ));
        return $results;
    }
}