<?php
    require_once("routers.php");
    require_once("canit-api-client.php");
    require_once("canit.php");
    require_once("settings.php");
    require_once("exchange.php");

    $show_table = false;
    if(isset($_POST['search'])) {
        $show_table = true;
    }
?>

<!DOCTYPE html>

<head>
    <title>
        Email Tracking and Filtering
    </title>

    <meta name="format-detection" content="telephone=no">


    <link type="text/css" rel="stylesheet" href="css/global.css" />
    <link type="text/css" rel="stylesheet" href="css/mobile.css" />
    <link type="text/css" rel="stylesheet" href="css/datepicker.css" />

    <link href='http://fonts.googleapis.com/
css?family=Roboto:400,100,300,500,700,900,100italic,400italic,300italic' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=PT+Serif:400,700' rel='stylesheet' type='text/css'>
    <link href='css/fonts.css' rel='stylesheet' type='text/css'>


    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="js/scripts.js"></script>

</head>
<body>

<div id="banner">

    <div class="row">
        <img src="img/byu-logo.gif" />

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
                            <option name="recipientContains" selected>contains</option>
                            <option name="recipientIs">equals</option>
                        </select>
					</span>
					<input type="text" value="" name="recipient">
				</span>

				<span class="sender">
					<label>Sender:</label>
					<span class="moz-select-wrap">
					<select name="senderSearchType">
                        <option name="senderContains" selected>contains</option>
                        <option name="senderIs">equals</option>
                    </select>
					</span>
					<input type="text" value="" name="sender">
				</span>
        </div>
    </div>
    <div class="row">

        <div class="column grid_6 server-select">
            <h3>Servers: <span>(click to toggle)</span></h3>
            <div class="box-selector on" id="canit">
                <h4>CanIt</h4><p>(Spam Filtering)</p>
                <div class="status-indicator"></div>
            </div>
            <div class="server-arrow"></div>
            <div class="box-selector on" id="routers">
                <h4>Routers</h4><p>(Alias Routing)</p>
                <div class="status-indicator"></div>
            </div>
            <div class="server-arrow"></div>
            <div class="box-selector on" id="Exchange">
                <h4>Exchange</h4><p>(Mail Delivery)</p>
                <div class="status-indicator"></div>
            </div>
            <!-- Checkboxes for Server Selection -->
            <input type="checkbox" name="server" value="Canit" checked="checked">
            <input type="checkbox" name="server" value="Routers" checked="checked">
            <input type="checkbox" name="server" value="Exchange" checked="checked">
        </div>

        <div class="column grid_6 date-and-time">
            <h3>Date Range:</h3>
            <p class="datepair" data-language="javascript">
                Start: <input type="text" id="datepickerStart" name="start_date">
                End: <input type="text" id="datepickerEnd" name="end_date">
            </p>
        </div>

        <div class="column grid_6 subject">
            <label>Subject:</label>
				<span class="moz-select-wrap">
					<select name="subjectSearchType">
                        <option name="subjectContains" selected>contains</option>
                        <option name="subjectIs">equals</option>
                    </select>
				</span>
            <input type="text" value="" name="subject">
        </div>



        <input type="submit" name="search" value="Search" />


    </div>
</form>
<!-- End Search Box -->

<!-- Start Results Table -->
<br/>
<?php
if ($show_table) {

    //Initialize canit connection
    $canitClient = new CanitClient();

    //Initialize router connection
    $routerClient = new RouterClient();

    $recipient = strtolower($_POST['recipient']);
    $recipientContains = ($_POST['recipientSearchType'] === "contains" ? true : false);
    $sender = strtolower($_POST['sender']);
    $senderContains = ($_POST['senderSearchType'] === "contains" ? true : false);
    $subject = ($_POST['subject']);
    $subjectContains = ($_POST['subjectSearchType'] === "contains" ? true : false);
    $date = $_POST['start_date'];
    $startDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) . "-" . substr($date, 3, 2) . "T00:00:00.000";
    $date = $_POST['end_date'];
    $endDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) .  "-" . substr($date, 3, 2) . "T23:59:59.999";
    $max_results = 100;


    $canitResults = $canitClient->getCanitResults($recipient, $recipientContains, $sender, $senderContains, $subject, $subjectContains, $startDttm, $endDttm, $max_results);
    $routerResults = $routerClient->getRouterResults($recipient, $recipientContains, $sender, $senderContains, $startDttm, $endDttm, $max_results);

    $canit_table_string = "<table class='results'>" .
        "<tbody>" .
        "<tr class='table-information'>" .
        "<td colspan='6'>Router Results</td>" .
        "<tr>" .
        "<th>Date</th>" .
        "<th>Time</th>" .
        "<th>Sender</th>" .
        "<th>Recipients</th>" .
        "<th>Subject</th>" .
        "<th>Status</th>" .
        "<th>Score</th>" .
        "</tr>";

        foreach($canitResults as $canit_row){
            $canit_table_string = $canit_table_string . "<tr>".
                "<td>" . date('m/d/Y -- h:i', $canit_row['ts'])."</td>" .
                "<td>" . date('m/d/Y -- h:i', $canit_row['ts'])."</td>" .
                "<td>" . $canit_row['sender'] . "</td>".
                "<td>" . $canit_row['recipients'][0]. "</td>" .
                "<td>" . $canit_row['subject'] . "</td>" .
                "<td>" . $canit_row['what'] . "</td>" .
                "<td>" . $canit_row['score'] ."</td>";
        }

        $canit_table_string = $canit_table_string ."</tbody>" .
        "</table>" .
        "<br/>";

    echo $canit_table_string;

    $router_table_string = "<table class='results'>" .
        "<tbody>" .
        "<tr class='table-information'>" .
        "<td colspan='6'>Router Results</td>" .
        "<tr>" .
        "<th>Date</th>" .
        "<th>Time</th>" .
        "<th>Sender</th>" .
        "<th>Recipients</th>" .
//        "<th>Subject</th>" .
        "<th>Status</th>" .
        "</tr>";

        foreach($routerResults as $row) {
             $router_table_string = $router_table_string . "<tr>" .
                "<td>" . $row['Date'] . "</td>" .
                "<td>" . $row['Time'] . "</td>" .
                "<td>" . $row['Sender'] . "</td>" .
                "<td>" . $row['Recipient'] . "</td>" .
//                "<td>" . $row['Subject'] . "</td>" .
                "<td>" . $row['Status'] . "</td>" .
                "</tr>";
        }

        $router_table_string = $router_table_string ."</tbody>" .
        "</table>" .
        "<br/>";

    echo $router_table_string;
	
	$exchangeResults = ExchangeClient::getExchangeResults($sender, $senderContains, $recipient, $recipientContains, $subject, $subjectContains, $startDttm, $endDttm, $max_results);
	
	$exchange_table_string = "<table class='results'>" .
        "<tbody>" .
        "<tr class='table-information'>" .
        "<td colspan='6'>Exchange Results</td>" .
        "<tr>" .
        "<th>Timestamp</th>" .
        "<th>Sender</th>" .
        "<th>Recipient</th>" .
        "<th>Subject</th>" .
        "</tr>";

        foreach($exchangeResults as $row) {
             $exchange_table_string = $exchange_table_string . "<tr>" .
                "<td>" . $row['date_time'] . "</td>" .
                "<td>" . $row['sender_address'] . "</td>" .
                "<td>" . $row['recipient_address'] . "</td>" .
                "<td>" . $row['message_subject'] . "</td>" .
                "</tr>";
        }

        $exchange_table_string = $exchange_table_string ."</tbody>" .
        "</table>" .
        "<br/>";

    echo $exchange_table_string;
	
	
}
?>
<table class="results">
    <tbody>
    <tr class="table-information">
        <td colspan="7">CanIt Results</td>
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Sender</th>
        <th>Recipients</th>
        <th>Subject</th>
        <th>Status</th>
        <th>Score</th>
    </tr>
    <tr>
        <td>01/12/2014</td>
        <td>08:00</td>
        <td>commserve@byu.edu</td>
        <td>parker@taco.byu.edu  </td>
        <td>Backup Job Summary Report - Daily Report</td>
        <td>Accepted</td>
        <td>1.35</td>
    </tr>

    <tr>
        <td>01/14/2014</td>
        <td>15:00</td>
        <td>trevor_harmon@byu.edu</td>
        <td>thetrevorharmon@gmail.com</td>
        <td>Picture of assignment</td>
        <td>Accepted</td>
        <td>4.35</td>
    </tr>

    <tr>
        <td>01/20/2014</td>
        <td>08:00</td>
        <td>marriotcenter@byu.edu</td>
        <td>ticketingoffice@byu.edu</td>
        <td>Report of ticket sales</td>
        <td>Accepted</td>
        <td>0.35</td>
    </tr>
    </tbody>
</table>
<br/>
<table class="results">
    <tbody>
    <tr class="table-information">
        <td colspan="7">Routers Results</td>
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Sender</th>
        <th>Recipients</th>
        <th>Subject</th>
        <th>Status</th>
        <th>Score</th>
    </tr>
    <tr>
        <td>01/12/2014</td>
        <td>08:00</td>
        <td>commserve@byu.edu</td>
        <td>parker@taco.byu.edu  </td>
        <td>Backup Job Summary Report - Daily Report</td>
        <td>Accepted</td>
        <td>1.35</td>
    </tr>

    <tr>
        <td>01/14/2014</td>
        <td>15:00</td>
        <td>trevor_harmon@byu.edu</td>
        <td>thetrevorharmon@gmail.com</td>
        <td>Picture of assignment</td>
        <td>Accepted</td>
        <td>4.35</td>
    </tr>

    <tr>
        <td>01/20/2014</td>
        <td>08:00</td>
        <td>marriotcenter@byu.edu</td>
        <td>ticketingoffice@byu.edu</td>
        <td>Report of ticket sales</td>
        <td>Accepted</td>
        <td>0.35</td>
    </tr>

    <tr>
        <td>01/20/2014</td>
        <td>08:00</td>
        <td>marriotcenter@byu.edu</td>
        <td>ticketingoffice@byu.edu</td>
        <td>Report of ticket sales</td>
        <td>Accepted</td>
        <td>0.35</td>
    </tr>

    <tr>
        <td>01/20/2014</td>
        <td>08:00</td>
        <td>marriotcenter@byu.edu</td>
        <td>ticketingoffice@byu.edu</td>
        <td>Report of ticket sales</td>
        <td>Accepted</td>
        <td>0.35</td>
    </tr>
    </tbody>
</table>


<!-- End Results Table -->
</div>

</div>

</body>
</html>