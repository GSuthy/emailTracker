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
        <table id="healthTable">
            <thead>
            <tr>
                <th>Hostname</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
    

                <?php
             
                $table = HealthController::data1(); 
                // print_r($table);
                $i = 0;
                foreach ($table as $result): ?>
                    <tr class="<?= $i++ % 2 == 0 ? 'even' : 'odd' ?>">
                        <td><?= $result['hostname']?></td>
                        <td><?= $result['test_ok'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">
                    <button id="healthTable" type="button">Refresh</button>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>


