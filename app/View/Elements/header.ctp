<div id="banner">
    <header id="main-header" class="noSelect">
        <div id="header-top" class="wrapper">
            <div id="logo">
                <a href="http://www.byu.edu/" class="byu">
                    <?php echo $this->Html->image('byu.gif', array()); ?>
                    <a class="appName">&nbsp;| Email Tracking & Filtering</a>
                </a>
                <div id="username-welcome">
                    <?= $this->Html->link('Log Out', array('controller' => 'users', 'action' => 'logout'), array('class' => 'header-button')) ?>
                    <div id='welcome'>
                        Welcome, <?=  $authUser['name'] ?>
                        <input type="text" id="stream" value="<?= $authUser['netId'] ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </header>
</div>