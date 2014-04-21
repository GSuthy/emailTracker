<?php
$show_table = false;
if($_SERVER['REQUEST_METHOD'] == "POST") {
	$show_table = true;
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Email Tracking and Filtering</title>

	<meta name="format-detection" content="telephone=no">

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

	<?php
	echo $this->Html->css(array(
//        'tooltips',
        'cupertino/jquery-ui-1.10.4.custom.min',
        'global',
		'mobile',
		'datepicker',
        'animations'
	));

	echo $this->Html->script(array(
        'jquery-ui-1.10.4.custom.min',
		'scripts'
	));
	?>

	<!-- these are for fonts -->
	<link href='//fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900,100italic,400italic,300italic' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=PT+Serif:400,700' rel='stylesheet' type='text/css'>
	<link href='//fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet' type='text/css'>

</head>
<body>

<div id="banner">
	<header id="main-header">
		<div id="header-top" class="wrapper">
			<div id="logo">
				<a href="http://www.byu.edu/" class="byu">
					<?php echo $this->Html->image('byu-logo.gif', array()); ?>
				</a>
			</div>
			<div id="button-container">
				<?php echo $this->Html->link('Logout', array(
						'controller' => 'Users',
						'action' => 'logout'), array('class' => 'button')
				); ?>
			</div>
		</div>
	</header>
</div>

<?php

if (!$authorized) {
	echo "<div class='container-error''>";
	echo "<form class='error'>";
	echo "<div class='rowError'>";
	echo "<h1>Email Tracking &amp; Filtering</h1>";
	echo "<h2>You are not authorized to view this page.";
	echo "<h2>If you believe you have received this message in error, please contact...";
	echo "</div>";
	echo "</form>";
	echo "</div>";
	die();
}

?>

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
					<input class="recipient-input" type="text" <?php if ($show_table) echo "value='" . $_POST['recipient'] . "'"; ?> name="recipient">
				</span>

				<span class="sender">
					<label>Sender:</label>
					<input class="sender-input" type="text" <?php if ($show_table) echo "value='" . $_POST['sender'] . "'"; ?> name="sender">
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
			<div  <?php if ($show_table && isset($_POST['canitSelect']) && isset($_POST['routerSelect'])) echo "class='server-arrow on'"; else echo "class='server-arrow'";?>></div>
			<div <?php if ($show_table && isset($_POST['routerSelect'])) echo "class='box-selector on'"; else echo "class='box-selector'"?> id="routers">
				<h4>Routers</h4><p>(Alias Routing)</p>
				<div class="status-indicator"></div>
			</div>
			<div <?php if ($show_table && isset($_POST['routerSelect']) && isset($_POST['exchangeSelect'])) echo "class='server-arrow on'"; else echo "class='server-arrow'";?>></div>
			<div <?php if ($show_table && isset($_POST['exchangeSelect'])) echo "class='box-selector on'"; else echo "class='box-selector'"?> id="Exchange">
				<h4>Exchange</h4><p>(Mail Delivery)</p>
				<div class="status-indicator"></div>
			</div>
			<!-- Checkboxes for Server Selection -->
			<input type="checkbox" name="canitSelect" value="CanIt" <?php if (($show_table && isset($_POST['canitSelect'])) || !$show_table) echo "checked='checked'"; ?>>
			<input type="checkbox" name="routerSelect" value="Routers" <?php if ($show_table && isset($_POST['routerSelect'])) echo "checked='checked'"; ?>>
			<input type="checkbox" name="exchangeSelect" value="Exchange" <?php if ($show_table && isset($_POST['exchangeSelect'])) echo "checked='checked'"; ?>>
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
			<input class="subject-input" type="text" <?php if ($show_table) echo "value='" . $_POST['subject'] . "'"; ?> name="subject">
		</div>
	</div>
    <div class="row">
        <div class="column-no-border grid_6 message">Logs may be 15 to 30 minutes behind.</div>
        <div class="column-no-border grid_6 form-button"><input class="form-button" type="submit" name="search" value="Search" /></div>
    </div>
</form>
<!-- End Search Box -->

<div id="tabs">
<ul>
	<?php if (!empty($_POST['canitSelect'])): ?>
		<li><a href="#canit-results">CanIt</a></li>
	<?php endif;
	if (!empty($_POST['routerSelect'])): ?>
		<li><a href="#routers-results">Routers</a></li>
	<?php endif;
	if (!empty($_POST['exchangeSelect'])): ?>
		<li><a href="#exchange-results">Exchange</a></li>
	<?php endif; ?>
</ul>

<?php if (!empty($_POST['canitSelect'])): ?>
	<div id="canit-results">
		<?php
		$warning_level_spam_score = $scoreThresholds['hold_threshold'];
		$auto_reject_spam_score = $scoreThresholds['auto_reject'];

		$warningDiv = "<div id=\"warningDiv\" hidden>".$warning_level_spam_score."</div>";
		$rejectDiv = "<div id=\"rejectDiv\" hidden>".$auto_reject_spam_score."</div>";
		echo $warningDiv;
		echo $rejectDiv;

		$canit_table_string = "<table class='results canit'>" .
			"<tbody>" .
			"<th>Date</th>" .
			"<th>Time</th>" .
			"<th>Sender</th>" .
			"<th>Recipients</th>" .
			"<th>Subject</th>" .
			"<th>Stream</th>" .
			"<th>Status</th>" .
			"<th>Score</th>" .
			"<th class='hidden'>Queue ID</th>" .
			"<th class='hidden'>Reporting Host</th>" .
			"<th class='hidden'>Realm</th>" .
			"<th class='hidden'>Incident ID</th>" .
			"</tr>";
		$canit_table_string .= "</tbody></table>";
		$canit_table_string .= "<a class='results loading-more canit'>Loading Results</a>";
		$canit_table_string .= "<br/>";
		echo $canit_table_string;
		?>
	</div>
<?php endif; ?>
<?php if (!empty($_POST['routerSelect'])) : ?>
	<div id="routers-results" class="hidden">
		<?php

		$router_table_string = "<table class='results routers'>" .
			"<tbody>" .
			"<th>Date</th>" .
			"<th>Time</th>" .
			"<th>Sender</th>" .
			"<th>Recipients</th>" .
			"<th>Status</th>" .
			"<th class='hidden'>Current ID</th>" .
			"<th class='hidden'>Next ID</th>" .
			"</tr>";
		$router_table_string .= "</tbody></table>";
		$router_table_string .= "<a class='results loading-more routers'>Loading Results</a>";
		$router_table_string .= "<br/>";
		echo $router_table_string;
		?>
	</div>
<?php endif; ?>
<?php if(!empty($_POST['exchangeSelect'])) : ?>
<div id="exchange-results" class="hidden">
	<?php

	$exchange_table_string = "\n<table class='results exchange'>\n" .
		"<tbody>" .
		"<th>Date</th>" .
		"<th>Time</th>" .
		"<th>Sender</th>" .
		"<th>Recipient</th>" .
		"<th>Subject</th>" .
		"<th class='hidden'>Message ID</th>" .
		"</tr>\n";
	$exchange_table_string .= "</tbody></table>";
	$exchange_table_string .= ("<a class='results loading-more exchange'>Loading Results</a>");
	$exchange_table_string .= "<br/>";

	echo $exchange_table_string;
	?>
</div>
<?php endif; ?>

<!-- Start Results Table -->
<br/>
<?php
if ($show_table) {
	$start_date_error = "";
	$startDttm = $endDttm = "";

	$recipient = strtolower($_POST['recipient']);
	$sender = strtolower($_POST['sender']);
	$subject = strtolower($_POST['subject']);

	if (empty($_POST['start_date']) || (!empty($_POST['start_date']) && $_POST['start_date'] == "")) {
		$start_date_error = "Start date required";
	} else {
		$date = $_POST['start_date'];
		$startDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) . "-" . substr($date, 3, 2) . "T00:00:00.000";
	}

	if (empty($_POST['end_date'])) {
		$endDttm = date('Y') . "-" . date('m') . "-" . date('d') . "T" . date('H') . ":" . date('m') . ":" . date('i') . "000";
		$endDttm = date('Y') . "-" . date('m') . "-" . date('d') . "T" . date('H') . ":" . date('m') . ":" . date('i') . "000";
	} else {
		$date = $_POST['end_date'];
		$endDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) .  "-" . substr($date, 3, 2) . "T23:59:59.999";
		$endDttm = substr($date, 6, 4) . "-" . substr($date, 0, 2) .  "-" . substr($date, 3, 2) . "T23:59:59.999";
	}

	$paramsTable = "<table id=\"paramsTable\" style=\"display: none\"><tr>".
		"<td>".$recipient."</td>".
		"<td>".$sender."</td>".
		"<td>".$subject."</td>".
		"<td>".$startDttm."</td>".
		"<td>".$endDttm."</td>".
		"</tr></table>";
	echo $paramsTable;

	$max_results = 30;

	$hasErrors = (!empty($recip_sender_error) || !empty($start_date_error));
}
?>

<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', 'UA-47977201-1', 'byu.edu');
	ga('send', 'pageview');
</script>

</body>

</html>