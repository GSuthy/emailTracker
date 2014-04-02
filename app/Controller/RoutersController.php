<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 4/2/14
 * Time: 3:28 PM
 */

App::uses('AppController', 'Controller');

class RoutersController extends AppController {

    public $uses = 'Routers';

    public function routersResults() {
        session_write_close();
        $recipient = $this->request->data("recipient");
        $recipient_contains = $this->request->data("recipient_contains");
        $sender = $this->request->data("sender");
        $sender_contains = $this->request->data("sender_contains");
        $startDttm = $this->request->data("start_date");
        $endDttm = $this->request->data("end_date");
        $maxResults = $this->request->data("max_results");
        $offset = $this->request->data("offset");

        $results = $this->Routers->getTable($recipient, $recipient_contains, $sender, $sender_contains, $startDttm, $endDttm, $maxResults, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

    public function routersLogs() {
        session_write_close();
        $messageId = $_REQUEST['message_id'];
        $nextId = $_REQUEST['next_id'];

        $allLogs = array();

        array_push($allLogs, $this->recursivePreviousLink($messageId));

        $currentLog = $this->Routers->getCurrentLog($messageId);
        array_push($allLogs, $currentLog);

        $currentNextId = $nextId;
        while (!is_null($currentNextId)) {
            $nextLink = $this->Routers->getNextLink($currentNextId);

            $temp_id = null;
            foreach ($nextLink as $array) {
                array_push($allLogs, $array);
                if (!is_null($array['Routers']['next_id'])) {
                    $temp_id = $array['Routers']['next_id'];
                }
            }
            $currentNextId = $temp_id;
        }

        return new CakeResponse(array('body' => json_encode($allLogs), 'type' => 'json'));
    }

    private function recursivePreviousLink($currentMessageId) {
        $returnLinks = array();
        $usedIDs = array();

        $previousLinks = $this->Routers->getPreviousLink($currentMessageId);
        foreach ($previousLinks as $links) {
            foreach ($links as $link) {
                if ($link['Routers']['message_id'] != null && !in_array($link['Routers']['message_id'], $usedIDs)) {
                    array_push($usedIDs, $link['Routers']['message_id']);
                    $tempArray = $this->recursivePreviousLink($link['Routers']['message_id']);
                    foreach ($tempArray as $result) {
                        array_push($returnLinks, $result);
                    }
                }
            }
        }
        array_push($returnLinks, $previousLinks);

        return $returnLinks;
    }
} 