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

    public static function getCanitResults ($recipient, $recipient_contains, $sender, $sender_contains, $subject, $subject_contains, $startDttm, $endDttm, $maxResults, $offset) {
        $canit_url = "https://emailfilter.byu.edu/canit/api/2.0";
        $api = new CanItAPIClient($canit_url);
        $success = $api->login(settings::$credentials['username'], settings::$credentials['password']);

        $startDttm = new DateTime($startDttm);
        $start_date = $startDttm->format('Y-m-d');

        $endDttm = new DateTime($endDttm);
        $end_date = $endDttm->format('Y-m-d');

        $start_date = urlencode($start_date);
        $end_date = urlencode($end_date);

        $recipient = urlencode($recipient);
        $sender = urlencode($sender);
        $subject = urlencode($subject);

        $search_string = 'log/search/'.$offset.'/'.$maxResults.'?sender='.$sender.'&recipients='.$recipient.'&subject='.$subject.'&start_date='.$start_date.'&end_date='.$end_date;

        if (!$startDttm) {
            return CanItClient::canitError("Must specify a start date");
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

        $results = $api->do_get($search_string);

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