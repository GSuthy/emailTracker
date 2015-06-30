<?php
$this->start('script');
echo $this->Html->script(array(
    '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js',
    'jquery-ui-1.10.4.custom.min',
//    'jquery-ui-1.10.3.custom.min',
    'scripts'
));
$this->end();
$this->start('css');
echo $this->Html->css(array(
    'cupertino/jquery-ui-1.10.4.custom.min',
    'datepicker'
));
$this->end();
?>

<?php if (!$authorized): ?>

    <div class='container-error''>
	<form class='error'>
        <div class='rowError'>
            <h1>Email Tracking &amp; Filtering</h1>
            <h2>You are not authorized to view this page.</h2>
            <h2>Perhaps CAS Authentication is not working correctly</h2>
            <h2>If you believe you have received this message in error, please contact the Office of Information Technology's help desk at 801-422-4000</h2>
        </div>
	</div>

<?php else:
$show_table = false;
if($_SERVER['REQUEST_METHOD'] == "POST") {
    $show_table = true;
}
?>

<div class="container">

    <!-- Start Search box -->
    <form method="POST" class="search">
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
                ?>

                <div id="warningDiv" hidden><?= $warning_level_spam_score ?></div>
                <div id="rejectDiv" hidden><?= $auto_reject_spam_score ?></div>

                <table class='results canit'>
                    <tbody>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Sender</th>
                        <th>Recipients</th>
                        <th>Subject</th>
                        <th>Stream</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th class='hidden'>Queue ID</th>
                        <th class='hidden'>Reporting Host</th>
                        <th class='hidden'>Realm</th>
                        <th class='hidden'>Incident ID</th>
                    </tr>
                    </tbody>
                </table>
                <a class='results loading-more canit'>Loading Results</a>
                <br/>
            </div>
        <?php endif; ?>
        <?php if (!empty($_POST['routerSelect'])) : ?>
            <div id="routers-results" class="hidden">
                <table class='results routers'>
                    <tbody>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Sender</th>
                    <th>Recipients</th>
                    <th>Status</th>
                    <th class='hidden'>Current ID</th>
                    <th class='hidden'>Next ID</th>
                    </tr>
                    </tbody>
                </table>
                <a class='results loading-more routers'>Loading Results</a>
                <br/>
            </div>
        <?php endif; ?>
        <?php if(!empty($_POST['exchangeSelect'])) : ?>
            <div id="exchange-results" class="hidden">
                <table class="results exchange">
                    <tbody>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Sender</th>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th class="hidden">Message ID</th>
                    </tr>
                    </tbody>
                </table>
                <a class="results loading-more exchange">Loading Results</a>
                <br/>
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
            ?>

            <table id="paramsTable" style="display: none">
                <tr>
                    <td><?= $recipient ?></td>
                    <td><?= $sender ?></td>
                    <td><?= $subject ?></td>
                    <td><?= $startDttm ?></td>
                    <td><?= $endDttm ?></td>
                </tr>
            </table>

            <?php
            $max_results = 30;
            $hasErrors = (!empty($recip_sender_error) || !empty($start_date_error));
        }
        endif;
        ?>
    </div>
</div>
