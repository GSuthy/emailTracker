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

<div class="container">
    <table id="queueTable">
        <thead>
        <tr>
            <th>Server</th>
            <th>Active Queue</th>
            <th>Deferred Queue</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $description => $resultArray): ?>
            <tr class="sub-header">
                <td colspan="3"><?= $description ?></td>
            </tr>
            <?php
            $i = 0;
            foreach ($resultArray as $result): ?>
                <tr class="<?= $i++ % 2 == 0 ? 'even' : 'odd' ?>">
                    <td><?= $result['server'] ?></td>
                    <td><?= $result['active_queue'] ?></td>
                    <td><?= $result['deferred_queue'] ?></td>
                </tr>
            <?php endforeach; ?>
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