<?php
    $show_table = false;
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $show_table = true;
    }
?>

<!DOCTYPE html>
<html>

<head>
    <title>
        Email Tracking and Filtering
    </title>

    <meta name="format-detection" content="telephone=no">


    <link type="text/css" rel="stylesheet" href="css/global.css" />
    <link type="text/css" rel="stylesheet" href="css/mobile.css" />
    <link type="text/css" rel="stylesheet" href="css/datepicker.css" />


    <!-- these are for fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900,100italic,400italic,300italic' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=PT+Serif:400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet' type='text/css'>

    <script src="//code.jquery.com/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="js/scripts.js"></script>

</head>
<body>

<div id="banner">

    <div class="row">
        <?php echo $this->Html->image('byu-logo.gif', array()); ?>
    </div>

</div>

<div class="container">

<!-- Start Search box -->
<form method="POST" class="search">
    <div class="row">
        <h1>Email Tracking &amp; Filtering</h1>
    </div>


    <div class="row">
        <div class="column grid_12">

				<span class="recipient">
					<label>Recipient:</label>
					<span class="moz-select-wrap">
						<select name="recipientSearchType">
                            <option <?php if (($show_table && $_POST['recipientSearchType'] === "contains") || !$show_table) echo "selected"; ?>>contains</option>
                            <option <?php if ($show_table && $_POST['recipientSearchType'] === "equals") echo "selected"; ?>>equals</option>
                        </select>
					</span>
					<input type="text" <?php if ($show_table) echo "value='" . $_POST['recipient'] . "'"; ?> name="recipient">
				</span>

				<span class="sender">
					<label>Sender:</label>
					<span class="moz-select-wrap">
					<select name="senderSearchType">
                        <option <?php if (($show_table && $_POST['senderSearchType'] === "contains") || !$show_table) echo "selected"; ?>>contains</option>
                        <option <?php if ($show_table && $_POST['senderSearchType'] === "equals") echo "selected"; ?>>equals</option>
                    </select>
					</span>
					<input type="text" <?php if ($show_table) echo "value='" . $_POST['sender'] . "'"; ?> name="sender">
				</span>
        </div>
    </div>
    <div class="row">

        <div class="column grid_6 server-select">
            <h3>Servers: <span>(click to toggle)</span></h3>
            <div <?php if (($show_table && isset($_POST['canitSelect'])) || !$show_table) echo "class='box-selector on'"; else echo "class='box-selector'"?> id="canit">
                <h4>CanIt</h4><p>(Spam Filtering)</p>
                <div class="status-indicator"></div>
            </div>
            <div <?php if (($show_table && isset($_POST['canitSelect']) && isset($_POST['routerSelect'])) || !$show_table) echo "class='server-arrow on'"; else echo "class='server-arrow'"?>></div>
            <div <?php if (($show_table && isset($_POST['routerSelect'])) || !$show_table) echo "class='box-selector on'"; else echo "class='box-selector'"?> id="routers">
                <h4>Routers</h4><p>(Alias Routing)</p>
                <div class="status-indicator"></div>
            </div>
            <div <?php if (($show_table && isset($_POST['routerSelect']) && isset($_POST['exchangeSelect'])) || !$show_table) echo "class='server-arrow on'"; else echo "class='server-arrow'"?>></div>
            <div <?php if (($show_table && isset($_POST['exchangeSelect'])) || !$show_table) echo "class='box-selector on'"; else echo "class='box-selector'"?> id="Exchange">
                <h4>Exchange</h4><p>(Mail Delivery)</p>
                <div class="status-indicator"></div>
            </div>
            <!-- Checkboxes for Server Selection -->
            <input type="checkbox" name="canitSelect" value="CanIt" <?php if (($show_table && isset($_POST['canitSelect'])) || !$show_table) echo "checked='checked'"; ?>>
            <input type="checkbox" name="routerSelect" value="Routers" <?php if (($show_table && isset($_POST['routerSelect'])) || !$show_table) echo "checked='checked'"; ?>>
            <input type="checkbox" name="exchangeSelect" value="Exchange" <?php if (($show_table && isset($_POST['exchangeSelect'])) || !$show_table) echo "checked='checked'"; ?>>
        </div>

        <div class="column grid_6 date-and-time">
            <h3>Date Range:</h3>
            <p class="datepair" data-language="javascript">
                Start: <input type="text" id="datepickerStart" name="start_date" value="<?php if ($show_table) echo $_POST['start_date']; else echo date('m/d/Y') ?>">
                End: <input type="text" id="datepickerEnd" name="end_date" value="<?php if ($show_table) echo $_POST['end_date']; else echo date('m/d/Y') ?>">
            </p>
        </div>

        <div class="column grid_6 subject">
            <label>Subject:</label>
				<span class="moz-select-wrap">
					<select name="subjectSearchType">
                        <option <?php if (($show_table && $_POST['subjectSearchType'] === "contains") || !$show_table) echo "selected"; ?>>contains</option>
                        <option <?php if ($show_table && $_POST['subjectSearchType'] === "equals") echo "selected"; ?>>equals</option>
                    </select>
				</span>
            <input type="text" <?php if ($show_table) echo "value='" . $_POST['subject'] . "'"; ?> name="subject">
        </div>



        <input type="submit" name="search" value="Search" />


    </div>
</form>
<!-- End Search Box -->

<!-- Start Results Table -->
<br/>
<?php
if ($show_table) {
    $start_date_error = "";
    $startDttm = $endDttm = "";

    $recipient = strtolower($_POST['recipient']);
    $recipientContains = ($_POST['recipientSearchType'] === "contains" ? true : false);
    $sender = strtolower($_POST['sender']);
    $senderContains = ($_POST['senderSearchType'] === "contains" ? true : false);
    $subject = strtolower($_POST['subject']);
    $subjectContains = ($_POST['subjectSearchType'] === "contains" ? true : false);

    if (empty($_POST['start_date'])) {
        $start_date_error = "Start date required";
    } else {
        $date = $_POST['start_date'];
        $startDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) . "-" . substr($date, 3, 2) . "T00:00:00.000";
    }

    if (empty($_POST['end_date'])) {
        $endDttm = date('Y') . "-" . date('m') . "-" . date('d') . "T" . date('H') . ":" . date('m') . ":" . date('i') . "000";
    } else {
        $date = $_POST['end_date'];
        $endDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) .  "-" . substr($date, 3, 2) . "T23:59:59.999";
    }

    $max_results = 20;
    $warning_level_spam_score = 5;
    $maximum_spam_score = 30;

    $hasErrors = (!empty($recip_sender_error) || !empty($start_date_error));

    /*
     *              Prints the CanIt table if the checkbox was selected
     */

    if (!$hasErrors) {
        if (isset($_POST['canitSelect']) && $_POST['canitSelect'] == true) {

            $canitResults = CanitClient::getCanitResults($recipient, $recipientContains, $sender, $senderContains, $subject, $subjectContains, $startDttm, $endDttm, $max_results);

            $canit_table_string = "<table class='results canit'>" .
                "<tbody>" .
                "<tr class='table-information'>" .
                "<td colspan='6'>CanIt Results</td>" .
                "<tr>" .
                "<th>Date</th>" .
                "<th>Time</th>" .
                "<th>Sender</th>" .
                "<th>Recipients</th>" .
                "<th>Subject</th>" .
                "<th>Status</th>" .
                "<th>Score</th>" .
                "</tr>";

            $is_even = true;
            foreach($canitResults as $canit_row){
                $canit_table_string = $canit_table_string . "<tr class='" . ($is_even ? "even-row" : "odd-row") . "'>".
                    "<td>" . date('m/d/Y', $canit_row['ts']) . "</td>" .
                    "<td>" . date('h:i', $canit_row['ts']) . "</td>" .
                    "<td><span class='canit-sender'>" . $canit_row['sender'] . "</span></td>".
                    "<td><span class='canit-recipients'>";
                foreach ($canit_row['recipients'] as $recip) {
                    $canit_table_string .= $recip . "<br/>";
                }
                $canit_spam_score_string = "";
                $canit_spam_score = $canit_row['score'];
                if (empty($canit_spam_score)){ $canit_spam_score_string = "spam-score-empty"; }
                else if ($canit_spam_score < $warning_level_spam_score){ $canit_spam_score_string = "spam-score-good"; }
                else if ($canit_spam_score > $maximum_spam_score){ $canit_spam_score_string = "spam-score-quarantined"; }
                else { $canit_spam_score_string = "spam-score-warning"; }

                $canit_table_string .= "</span></td>" .
                    "<td>" . $canit_row['subject'] . "</td>" .
                    "<td>" . $canit_row['what'] . "</td>" .
                    "<td><span class=\"" . $canit_spam_score_string . "\">" . $canit_row['score'] . "</span></td>";
                $is_even = !$is_even;
            }

            $canit_table_string = $canit_table_string ."</tbody>" .
                "</table>" .
                "<br/>";

            echo $canit_table_string;
        }

        /*
         *              Prints the Router table if the checkbox was selected
         */

        if (isset($_POST['routerSelect']) && $_POST['routerSelect'] == true) {

            $routerResults = RouterClient::getRouterResults($recipient, $recipientContains, $sender, $senderContains, $startDttm, $endDttm, $max_results);

            $router_table_string = "<table class='results routers'>" .
                "<tbody>" .
                "<tr class='table-information'>" .
                "<td colspan='6'>Router Results</td>" .
                "<tr>" .
                "<th>Date</th>" .
                "<th>Time</th>" .
                "<th>Sender</th>" .
                "<th>Recipients</th>" .
                "<th>Status</th>" .
                "</tr>";

            $is_even = true;
            foreach($routerResults as $row) {
                $router_table_string = $router_table_string . "<tr class='" . ($is_even ? "even-row" : "odd-row") . "'>" .
                    "<td>" . $row['Date'] . "</td>" .
                    "<td>" . $row['Time'] . "</td>" .
                    "<td>" . $row['Sender'] . "</td>" .
                    "<td>";
                foreach ($row['Recipients'] as $recip) {
                    $router_table_string .= $recip . "<br/>";
                }
                $router_table_string .= "</td>" .
                    "<td>" . $row['Status'] . "</td>" .
                    "</tr>";
                $is_even = !$is_even;
            }

            $router_table_string = $router_table_string ."</tbody>" .
                "</table>" .
                "<br/>";

            echo $router_table_string;
        }

        /*
         *	Prints the Exchange table if the checkbox was selected
         */

        if (isset($_POST['exchangeSelect']) && $_POST['exchangeSelect'] == true) {

            $exchangeResults = ExchangeClient::getExchangeResults($sender, $senderContains, $recipient, $recipientContains, $subject, $subjectContains, $startDttm, $endDttm, $max_results);

            $exchange_table_string = "\n<table class='results exchange'>\n" .
                "<tbody>" .
                "<tr class='table-information'>" .
                "<td colspan='6'>Exchange Results</td>" .
                "<tr>" .
                "<th>Date</th>" .
                "<th>Time</th>" .
                "<th>Sender</th>" .
                "<th>Subject</th>" .
		"<th>Message ID</th>" .
                "</tr>\n";

            $is_even = true;
            foreach($exchangeResults as $row) {
                $exchange_table_string = $exchange_table_string . "<tr class='" . ($is_even ? "even-row" : "odd-row") . " exchange'>" .
                    "<td>" . date('m/d/Y', strtotime($row['date_time'])) . "</td>" .
                    "<td>" . date('H:i:s', strtotime($row['date_time'])) . "</td>" .
                    "<td>" . $row['sender_address'] . "</td>" .
                    "<td>" . $row['message_subject'] . "</td>" .
					"<td>" . $row['internal_message_id'] . "</td>" .
                    "</tr>\n";
                $is_even = !$is_even;
            }

            $exchange_table_string = $exchange_table_string . "</tbody>\n" .
                "</table>\n" .
                "<br/>";

            echo $exchange_table_string;
        }
    }
}
?>

</div>

<div id="canitOverlay" style="" class="rowOverlay">
    <span class="external-link-wrap">
    <a class="view-logs">View Logs</a>
    <a href="#">Open in CanIt</a>
    </span>
</div>
    
<div id="nonCanitOverlay" style="" class="rowOverlay">
    <span class="external-link-wrap">
    <a class="view-logs">View Logs</a>
    </span>
</div>

</body>
</html>