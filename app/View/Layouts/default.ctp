<!DOCTYPE html>
<html>
<head>

    <meta name="format-detection" content="telephone=no">

    <?php
    echo $this->Html->charset();
    echo $this->Html->meta('icon');

    echo $this->fetch('meta');

    echo $this->Html->css(array(
        '//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600',
        'global',
        'animations',
        'header',
        'menu',
        'footer'
    ));

    echo $this->Html->script(array(
        'jquery-2.1.1.min'
    ));

    echo $this->fetch('css');
    echo $this->fetch('script');
    ?>

    <!-- these are for fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900,100italic,400italic,300italic' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=PT+Serif:400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet' type='text/css'>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

    <title>Email Tracking & Filtering</title>

    <?= $this->element('google-analytics'); ?>

</head>
<body>
<div id="popup-overlay"></div>
<div id="content">
    <?= $this->element('header') ?>
    <?= $this->element('menu') ?>
    <?php echo $this->fetch('content'); ?>
</div>
<?= $this->element('footer') ?>

</body>
</html>