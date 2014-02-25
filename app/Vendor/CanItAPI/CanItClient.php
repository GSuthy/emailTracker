<?php
	/*require_once("canit-api-client.php");
	require_once("settings.php");
	require_once("ExchangeClient.php");*/

	date_default_timezone_set('America/Denver');

class CanItClient {


    private static function canitError($errorMessage){
        unset($errorReturn);
        $errorReturn["error"] = $errorMessage;
        return $errorReturn;
    }

    public static function getCanitResults ($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults){
//        global $credentials;
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);
        $users = $api->do_get('realm/@@/users');
//        $maxResults = 2000;
        $start_date = substr($startDttm, 0, 10);
        $end_date = substr($endDttm, 0, 10);
        $search_string = 'log/search/0/'.$maxResults.'?sender='.$sender.'&recipients='.$recipient.'&subject='.$subject.'&start_date='.$start_date.'&end_date='.$end_date;

        if (!$startDttm) {
            return CanItClient::canitError("Must specify a start date"); //TODO: better fail message
        }

        if ($sender_contains)
        {
            $search_string = $search_string . '&rel_sender=contains';
        }
        if ($recipient_contains)
        {
            $search_string = $search_string . '&rel_recipients=contains';
        }
        if ($subject_contains)
        {
            $search_string = $search_string . '&rel_subject=contains';
        }

//        echo $search_string . '<br>';
        $results = $api->do_get($search_string);

        $results_temp = array();
        foreach ($results as &$result) {
            //5, 18
            $result['auto_reject'] = $api->do_get('realm/' . $result['realm'] . '/stream/' . $result['stream'] .  '/setting/AutoReject')['value'];
            $result['hold_threshold'] = $api->do_get('realm/' . $result['realm'] . '/stream/' . $result['stream'] . '/setting/HoldThreshold')['value'];
        }

	    if (!$api->succeeded()) {
		    print "GET request failed: " . $api->get_last_error() . "\n";
            return null;
	    } else {
            return $results;
        }
    }

    public static function getLogs($queue_id, $reporting_host) {
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);
        $users = $api->do_get('realm/@@/users');
        $search_string = 'log/' . $queue_id . '/' . $reporting_host;

        $results = $api->do_get($search_string);

        if (!$api->succeeded()) {
            print "GET request failed: " . $api->get_last_error() . "\n";
            return null;
        } else {
            return $results[0]['loglines'];
        }
    }
}