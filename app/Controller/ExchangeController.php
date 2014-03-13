<?php

App::import('Vendor', 'ExchangeAPI/ExchangeClient');

class ExchangeController extends AppController {
    
	public function viewMoreResults() {
        
    }

	public function getAdditionalLogs() {            
            $maxResults = $this->request->data("max_results");
            
            $messageId = html_entity_decode($this->request->data("message_id"));
            
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

