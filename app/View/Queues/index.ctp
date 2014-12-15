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
        <?php foreach ($results as $result): ?>
            <tr>
                <td><?= $result['GatewayQueues']['server'] ?></td>
                <td><?= $result['GatewayQueues']['active_queue'] ?></td>
                <td><?= $result['GatewayQueues']['deferred_queue'] ?></td>
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