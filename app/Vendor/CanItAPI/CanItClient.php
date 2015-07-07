<?php
	/*require_once("canit-api-client.php");
	require_once("settings.php");
	require_once("ExchangeClient.php");*/

	date_default_timezone_set('America/Denver');

/**
 * Class CanItClient
 * This class is used to interface with CanIt's API for obtaining information from the CanIt servers.
 * Class methods are called from CanitController.php
 */

class CanItClient {

    /**
     * This method is used to set the thresholds that determin the color of the CanIt Score <span> in the CanIt Results table.
     *
     * @return array|null   returns an array containing the upper and lower bounds of the warning levels for CanIt scores.
     */
    public static function getThresholds() {
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);

        $autoRejectArray = $api->do_get('realm/base/stream/default/setting/AutoReject');
        $auto_reject = $autoRejectArray['value'];
        $holdThresholdArray = $api->do_get('realm/base/stream/default/setting/HoldThreshold');
        $hold_threshold = $holdThresholdArray['value'];
        $results = array('auto_reject' => $auto_reject, 'hold_threshold' => $hold_threshold);

        if (!$api->succeeded()) {
            print "GET request failed: " . $api->get_last_error() . "\n";
            return null;
        } else {
            return $results;
        }
    }

    /**
     * This method is used to make an API call to get log lines from CanIt's server.
     *
     * @param $recipients   String containing the recipient being searched on.  This is a 'contains' search.
     * @param $sender       String containing the sender being searched on.  This is a 'contains' search.
     * @param $subject      String containing the subject being searched on.  This is a 'contains' search.  This field is unused.
     * @param $startDttm    String containing the start datetime for the search.
     * @param $endDttm      String containing the end datetime for the search.
     * @param $maxResults   Integer denoting the number of results to return.
     * @param $offset       Integer denoting offset index for the search results.
     * @return NULL         Returns an array of API call results.
     */
    public static function getCanitResults ($recipients, $sender, $subject, $startDttm, $endDttm, $maxResults, $offset) {
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);

        $startDttm = new DateTime($startDttm);
        $start_date = $startDttm->format('Y-m-d');

        $endDttm = new DateTime($endDttm);
        $end_date = $endDttm->format('Y-m-d');

        $start_date = urlencode($start_date);
        $end_date = urlencode($end_date);

        $recipients = urlencode($recipients);
        $sender = urlencode($sender);
        $subject = urlencode($subject);

        $search_string =  'log/search/'.$offset.'/'.$maxResults.'?start_date='.$start_date.'&end_date='.$end_date;

        if (!empty($sender)) {
            $search_string .= '&sender='.$sender.'&rel_sender=contains';
        }

        if (!empty($recipients)) {
            $search_string .= '&recipients='.$recipients.'&rel_recipients=contains';
        }

        if (!empty($subject)) {
            $search_string .= '&subject='.$subject.'&rel_subject=contains';
        }

        $results = $api->do_get($search_string);

	    if (!$api->succeeded()) {
		    print "GET request failed: " . $api->get_last_error() . "\n";
            return null;
	    } else {
            return $results;
        }
    }

    /**
     * @param $queue_id         String containing the queue ID corresponding to the additional logs being pulled.
     * @param $reporting_host   String containing the reporting host, used to get the logs corresponding to the selected row in the table.
     * @return null             Returns an array containing a additional log information corresponding to the selected table row.
     */
    public static function getLogs($queue_id, $reporting_host) {
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);
        $search_string = 'log/' . $queue_id . '/' . $reporting_host;

        $results = $api->do_get($search_string);

        if (!$api->succeeded()) {
            print "GET request failed: " . $api->get_last_error() . "\n";
            return null;
        } else {
            return $results[0]['loglines'];
        }
    }


    public static function searchLog() {
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);
        $results = $api->do_get("/realm/byu-edu/stream/default/incidents/status/3");

        if (!$api->succeeded()) {
            print "GET request failed: " . $api->get_last_error() . "\n";
            return null;
        } else {
            return $results;
        }


    }




}
