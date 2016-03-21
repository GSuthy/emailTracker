<?php

//we use the basic 'queues' css for the other pages
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

<?php if (!$phishing_authorized): ?>

    <div class='container-error'>
	<form class='error'>
        <div class='rowError'>
            <h1>Email Tracking &amp; Filtering</h1>
            <h2>You are not authorized to view this page.</h2>
            <h2>If you believe you have received this message in error, please contact the Office of Information Technologys help desk at 801-422-4000</h2>
        </div>
    </form>
	</div>


<?php else: ?>
  <div class='container-error'>
<form class='error'>
      <div class='rowError'>
          <h1>Phishing and Spam Email Removal</h1>
            <h3><FONT COLOR = "B90000">Please ensure that you are <b>very</b> careful when removing emails</font><h3>
      </div>
  </form>
</div>

<div class="container">

    <!-- Start Search box -->
    <form method="POST" class="search">
        <div class="row">
            <div class="column grid_12">

				<span class="recipient">
					<label>Subject:</label>
					<input class="recipient-input" type="text" <?php if ($show_table) echo "value='" . $_POST['recipient'] . "'"; ?> name="recipient">
				</span>

				<span class="sender">
					<label>Sender:</label>
					<input class="sender-input" type="text" <?php if ($show_table) echo "value='" . $_POST['sender'] . "'"; ?> name="sender">
				</span>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>
