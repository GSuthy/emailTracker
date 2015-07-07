<?php

App::uses('AppModel', 'Model');

class SearchLogsObject extends AppModel {
    private $stream;

    public function SearchLogsObject($stream) {
        $this->stream = $stream;
    }

    public function getStream() {
        return $this->stream;
    }

}

?>
