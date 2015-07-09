<?php
$this->start('script');
echo $this->Html->script(array(
    'queues'
));
$this->end();
$this->start('css');
echo $this->Html->css(array(
    'queues'
));
$this->end();
?>

<?php if (!$queues_authorized): ?>

    <div class='container-error''>
	<form class='error'>
        <div class='rowError'>
            <h1>Email Tracking &amp; Filtering</h1>
            <h2>You are not authorized to view this page.</h2>
            <h2>If you believe you have received this message in error, please contact the Office of Information Technology's help desk at 801-422-4000</h2>
        </div>
    </form>
	</div>

<?php else: ?>

<div class="container">
    <div class="tableHolder">
        <table id="queueTable">
            <thead>
            <tr>
                <th>Server</th>
                <th>Active Queue</th>
                <th>Deferred Queue</th>
            </tr>
            </thead>
            <tbody>
    

                <?php



        $working = array();
        $notWorking = array();
        $message = CanItClient::searchlog();

                foreach ($message as $check) {
            if ($check['message'] == "All mounted volumes have at least 10% free disk space and inodes") {
                if ($check['test_ok'] == 1){
                    // if ($check['hostname'] === "gw10.byu.edu") {
                    array_push($working, $check['hostname'] . " currently has more than 10$ free disk space " . "<br>");
                return $message;
                return $working;
            }
            else {
                    array_push($notWorking, $check['hostname'] . "currently is not working");
                }
            }
        }
       

                $i = 0;
                foreach ($working as $result): ?>
                    <tr class="<?= $i++ % 2 == 0 ? 'even' : 'odd' ?>">
                        <td><?= $result['hostname'] ?></td>
                        <td><?= $result['test_ok'] ?></td>
                        <td><?= $result['message'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">
                    <button id="refreshQueuesTable" type="button">Refresh</button>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>
