<?php

App::import('Vendor', 'ExchangeAPI/ExchangeClient');

class ExchangeController extends AppController {
    
	
	public function getAdditionalLogs() {            
            $maxResults = $this->request->data("max_results");
            
            $messageId = html_entity_decode($this->request->data("message_id"));
            
            $returnVal = ExchangeClient::getAdditionalLogs($messageId, $maxResults);
                        
            return new CakeResponse(array('body' => json_encode($returnVal), 'type' => 'json'));
            
        }
	
}

