<?php $controller = strtolower($this->params['controller']); ?>
<div class="menu-row">
    <div class="menu-column menu-grid_12 noSelect">
        <ul>
            <li class='navItem notCurrentPage'>
                <?= $this->Html->link('Log Search', array('controller' => 'search', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'search' ? ' selected' : ''))); ?>
            </li>
            <li class='navItem currentPage currentQueues'>
                <?= $this->Html->link('Queues', array('controller' => 'rest', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'rest' ? ' selected' : ''))) ?>
            </li>
        </ul>
    </div>
</div>