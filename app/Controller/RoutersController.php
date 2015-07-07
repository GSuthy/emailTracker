<?php
/**
 * Created by PhpStorm.
 * User: stevenc4
 * Date: 4/2/14
 * Time: 3:28 PM
 */

App::uses('AppController', 'Controller');

/**
 * Class RoutersController
 * Receives AJAX calls and returns table information.
 */

class RoutersController extends AppController {

    public $uses = 'Routers';

    /**
     * Receives data from an AJAX request and calls the Routers model class to get information to display in the Routers Results table.
     *
     * @return CakeResponse     Contains an array of data to be displayed in the Routers Results table.
     */
    public function routersResults() {
        session_write_close();
        $recipient = $this->request->data("recipient");
        $sender = $this->request->data("sender");
        $subject = $this->request->data("subject");
        $startDttm = $this->request->data("start_date");
        $endDttm = $this->request->data("end_date");
        $maxResults = $this->request->data("max_results");
        $offset = $this->request->data("offset");

        $results = $this->Routers->getTable($recipient, $sender, $subject, $startDttm, $endDttm, $maxResults, $offset);

        return new CakeResponse(array('body' => json_encode($results), 'type' => 'json'));
    }

    /**
     * Receives data from an AJAX request and calls the Routers model class to get information to display when a row in the Routers Results table is clicked.
     *
     * @return CakeResponse     Contains an array of data to be displayed below the clicked row in the Routers Results table.
     */
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

    /**
     * Called from routersLogs.  Gets the previous logs according to the message ID.  Formats the resulting arrays into one.
     *
     * @param $currentMessageId     String containing the message ID of the log line whose preceding log line is to be fetched.
     * @return array                Array containing all the logs that come sequentially before the given message ID.
     */
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
