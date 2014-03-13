<?php

App::import('Vendor', 'ExchangeAPI/ExchangeClient');

class ExchangeController extends AppController {
    
	public function viewMoreResults() {
        $sender = $this->request->data("sender");
        $sender_contains = $this->request->data("sender_contains");
        $recipient = $this->request->data("recipient");
        $recipient_contains = $this->request->data("recipient_contains");
        $subject = $this->request->data("subject");
        $subject_contains = $this->request->data("subject_contains");
        $start_date = $this->request->data("start_date");
        $end_date = $this->request->data("end_date");
        $max_results = $this->request->data("max_results");
        $offset = $this->request->data("offset");

        $returnVal = ExchangeClient::getExchangeResults($sender, $sender_contains, $recipient,
                                                        $recipient_contains, $subject, $subject_contains,
                                                        $start_date, $end_date, $max_results, $offset);

        return new CakeResponse(array('body' => json_encode($returnVal), 'type' => 'json'));
    }

    public function getAdditionalLogs() {
        $maxResults = $this->request->data("max_results");

        $messageId = html_entity_decode($this->request->data("message_id"));
        $messageId = preg_replace('/<\/.*>/',"",$messageId);

        if(!empty($messageId)) {
            $returnVal = ExchangeClient::getAdditionalLogs($messageId, $maxResults);
        } else {
            //these 3 vars are only used if messageId is empty
            $utcMilliseconds = $this->request->data("utc_milliseconds");
            $sender = $this->request->data("sender_address");
            $subject = $this->request->data("message_subject");

            $returnVal = ExchangeClient::getAdditionalLogs($messageId, $maxResults, $sender, $subject, $utcMilliseconds);
        }

        return new CakeResponse(array('body' => json_encode($returnVal), 'type' => 'json'));
    }

}

