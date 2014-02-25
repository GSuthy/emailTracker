<?php

App::import('Vendor', 'ExchangeAPI/ExchangeClient');

class ExchangeController extends AppController {
    
	
	public function getAdditionalLogs() {            
            $maxResults = $this->request->query("max_results");
            
            $internalMessageId = $this->request->query("internal_message_id");
            
            $utcMilliseconds = $this->request->query("utc_milliseconds");
            
            $returnVal = ExchangeClient::getAdditionalLogs($internalMessageId, $utcMilliseconds, $maxResults);
                        
            return new CakeResponse(array('body' => json_encode($returnVal), 'type' => 'json'));
            
        }
	
}

