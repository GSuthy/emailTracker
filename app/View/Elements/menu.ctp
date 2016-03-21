<?php $controller = strtolower($this->params['controller']); ?>
<div class="menu-row">
    <div class="menu-column menu-grid_12 noSelect">
        <ul>
            <?php if($authorized): ?>
            <li class='navItem notCurrentPage'>
                <?= $this->Html->link('Log Search', array('controller' => 'search', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'search' ? ' selected' : ''))); ?>
            </li>
            <?php endif; if($queues_authorized) : ?>
           <li class='navItem notCurrentPage'>
                <?= $this->Html->link('Queues', array('controller' => 'queues', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'queues' ? ' selected' : ''))); ?>
            </li>
            <?php endif; if($queues_authorized) : ?>
            <li class='navItem notCurrentPage'>
                <?= $this->Html->link('Health', array('controller' => 'health', 'action' => 'index'),
                    array('class' => 'navItem transition' . ($controller == 'health' ? ' selected' : ''))); ?>
            </li>

            <?php endif; ?>
          <?php endif; if($queues_authorized) : ?>
          <li class='navItem notCurrentPage'>
              <?= $this->Html->link('Phishing', array('controller' => 'phishing', 'action' => 'index'),
                  array('class' => 'navItem transition' . ($controller == 'phishing' ? ' selected' : ''))); ?>
          </li>

          <?php endif; ?>
        </ul>
    </div>
</div>
