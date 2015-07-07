<?php

App::uses('AppModel', 'Model');

class SearchLogObject extends AppModel {
    private $stream;

    public function SearchLogObject($stream) {
        $this->stream = $stream;
    }

    public function getStream() {
        return $this->stream;
    }

}

?>
