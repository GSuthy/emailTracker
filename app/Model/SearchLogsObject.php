<?php


class SearchLogObject {
    private $stream;

    public function SearchLogObject($stream) {
        $this->stream = $stream;
    }

    public function getStream() {
        return $this->stream;
    }

}

?>
