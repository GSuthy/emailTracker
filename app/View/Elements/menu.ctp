<?php $controller = strtolower($this->params['controller']); ?>
<div class="menu-row">
    <div class="menu-column menu-grid_12 noSelect">
        <ul>
            <li class='navItem notCurrentPage'>
                <?= $this->Html->link('Log Search', array('controller' => 'search', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'search' ? ' selected' : ''))); ?>
            </li>
            <li class='navItem currentPage currentQueues'>
                <?= $this->Html->link('Queues', array('controller' => 'queues', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'queues' ? ' selected' : ''))) ?>
            </li>
        </ul>
    </div>
</div>